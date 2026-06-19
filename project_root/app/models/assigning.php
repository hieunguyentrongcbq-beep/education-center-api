<?php
namespace App\Models;

use Core\Database;
use PDO;

class assigning {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Lấy danh sách phân công — có thể lọc theo class_id và/hoặc student_id
    public function getAllAssignments($classId = null, $studentId = null) {
        $sql = "
            SELECT ta.*,
                   t.teacher_code, u.full_name AS teacher_name,
                   c.class_code, co.course_name, co.course_code,
                   s.student_code, su.full_name AS student_name
            FROM teacher_assignments ta
            JOIN teachers t  ON ta.teacher_id  = t.id
            JOIN users    u  ON t.user_id       = u.id
            JOIN classes  c  ON ta.class_id     = c.id
            JOIN courses  co ON c.course_id     = co.id
            LEFT JOIN students s  ON ta.student_id = s.id
            LEFT JOIN users   su  ON s.user_id     = su.id
        ";
        $wheres = [];
        $params = [];
        if ($classId)   { $wheres[] = 'ta.class_id = :class_id';     $params['class_id']   = $classId; }
        if ($studentId) { $wheres[] = 'ta.student_id = :student_id'; $params['student_id'] = $studentId; }
        if ($wheres) $sql .= ' WHERE ' . implode(' AND ', $wheres);
        $sql .= ' ORDER BY ta.id ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Phân công GV cho 1 học sinh cụ thể (mô hình 1:1)
    public function assignTeacher($data, $assignedByUserId) {
        if (empty($data['teacher_id']) || empty($data['class_id']) || empty($data['student_id'])) {
            return ['error' => 'Thiếu teacher_id, class_id hoặc student_id'];
        }

        $teacherId  = (int)$data['teacher_id'];
        $classId    = (int)$data['class_id'];
        $studentId  = (int)$data['student_id'];
        $dayOfWeek  = isset($data['day_of_week']) ? (int)$data['day_of_week'] : null;

        if (!$this->db->query("SELECT id FROM teachers WHERE id=$teacherId")->fetch())
            return ['error' => 'Giáo viên không tồn tại'];
        if (!$this->db->query("SELECT id FROM classes WHERE id=$classId")->fetch())
            return ['error' => 'Lớp không tồn tại'];
        if (!$this->db->query("SELECT id FROM students WHERE id=$studentId")->fetch())
            return ['error' => 'Học viên không tồn tại'];

        $enrolled = $this->db->prepare("SELECT id FROM enrollments WHERE student_id=:sid AND class_id=:cid AND payment_status='PAID'");
        $enrolled->execute(['sid'=>$studentId, 'cid'=>$classId]);
        if (!$enrolled->fetch()) return ['error' => 'Học viên chưa thanh toán / chưa vào lớp này'];

        $scheduleService = new ScheduleService();
        $dowCheck = $dayOfWeek;
        if ($dowCheck === null) {
            $classRow = $scheduleService->getClassWithCourse($classId);
            $dowCheck = (int)($classRow['day_primary'] ?? 1);
        }
        $times = $scheduleService->getScheduleTimesForClass($classId, $dowCheck)
            ?: ['start_time' => '18:00:00', 'end_time' => '20:00:00'];
        $conflicts = $scheduleService->checkTeacherConflict(
            $teacherId, $dowCheck, $times['start_time'], $times['end_time'], null, $classId
        );
        if ($conflicts) {
            return [
                'error' => ScheduleService::conflictClassMessage($conflicts, 'dạy'),
                'details' => $conflicts,
            ];
        }

        if ($dayOfWeek) {
            // Delete both: the specific-day record AND any global (no-day) record for same class+student
            // This prevents old global records from bleeding into other days
            $this->db->prepare("DELETE FROM teacher_assignments WHERE student_id=:sid AND class_id=:cid AND (day_of_week=:dow OR day_of_week IS NULL)")
                     ->execute(['sid'=>$studentId,'cid'=>$classId,'dow'=>$dayOfWeek]);
        } else {
            // Global assignment — delete existing global record only
            $this->db->prepare("DELETE FROM teacher_assignments WHERE student_id=:sid AND class_id=:cid AND day_of_week IS NULL")
                     ->execute(['sid'=>$studentId,'cid'=>$classId]);
        }

        $stmt = $this->db->prepare("
            INSERT INTO teacher_assignments (teacher_id, class_id, student_id, day_of_week, assigned_by, assignment_status)
            VALUES (:teacher_id, :class_id, :student_id, :day_of_week, :assigned_by, 'CONFIRMED')
        ");
        $ok = $stmt->execute([
            'teacher_id'  => $teacherId,
            'class_id'    => $classId,
            'student_id'  => $studentId,
            'day_of_week' => $dayOfWeek,
            'assigned_by' => $assignedByUserId,
        ]);
        return $ok ? ['success' => true, 'id' => $this->db->lastInsertId()] : ['error' => 'Không thể phân công'];
    }

    public function getAssignmentById($id) {
        $stmt = $this->db->prepare("
            SELECT ta.*,
                   t.teacher_code, u.full_name AS teacher_name,
                   c.class_code, co.course_name,
                   s.student_code, su.full_name AS student_name
            FROM teacher_assignments ta
            JOIN teachers t  ON ta.teacher_id  = t.id
            JOIN users    u  ON t.user_id       = u.id
            JOIN classes  c  ON ta.class_id     = c.id
            JOIN courses  co ON c.course_id     = co.id
            LEFT JOIN students s  ON ta.student_id = s.id
            LEFT JOIN users   su  ON s.user_id     = su.id
            WHERE ta.id = :id
        ");
        $stmt->execute(['id' => (int)$id]);
        return $stmt->fetch() ?: null;
    }

    // Cập nhật phân công GV (đổi GV / HV / thứ / trạng thái)
    public function updateAssignment($id, $data) {
        $id = (int)$id;
        $current = $this->getAssignmentById($id);
        if (!$current) return ['error' => 'Không tìm thấy phân công'];

        $classId   = (int)$current['class_id'];
        $teacherId = !empty($data['teacher_id']) ? (int)$data['teacher_id'] : (int)$current['teacher_id'];
        $studentId = !empty($data['student_id']) ? (int)$data['student_id'] : (int)$current['student_id'];

        if (array_key_exists('day_of_week', $data)) {
            $dayOfWeek = ($data['day_of_week'] === '' || $data['day_of_week'] === null)
                ? null
                : (int)$data['day_of_week'];
        } else {
            $dayOfWeek = $current['day_of_week'] !== null && $current['day_of_week'] !== ''
                ? (int)$current['day_of_week']
                : null;
        }

        $status = !empty($data['assignment_status']) ? $data['assignment_status'] : $current['assignment_status'];
        $allowedStatuses = ['PENDING', 'CONFIRMED', 'CANCELLED'];
        if (!in_array($status, $allowedStatuses, true)) {
            return ['error' => 'Trạng thái phân công không hợp lệ'];
        }

        if (!$this->db->query("SELECT id FROM teachers WHERE id=$teacherId")->fetch()) {
            return ['error' => 'Giáo viên không tồn tại'];
        }
        if (!$this->db->query("SELECT id FROM students WHERE id=$studentId")->fetch()) {
            return ['error' => 'Học viên không tồn tại'];
        }

        $enrolled = $this->db->prepare("SELECT id FROM enrollments WHERE student_id=:sid AND class_id=:cid AND payment_status='PAID'");
        $enrolled->execute(['sid' => $studentId, 'cid' => $classId]);
        if (!$enrolled->fetch()) {
            return ['error' => 'Học viên chưa thanh toán / chưa vào lớp này'];
        }

        if ($dayOfWeek === null) {
            $dup = $this->db->prepare("
                SELECT id FROM teacher_assignments
                WHERE class_id=:cid AND student_id=:sid AND day_of_week IS NULL AND id != :id
            ");
            $dup->execute(['cid' => $classId, 'sid' => $studentId, 'id' => $id]);
        } else {
            $dup = $this->db->prepare("
                SELECT id FROM teacher_assignments
                WHERE class_id=:cid AND student_id=:sid AND day_of_week=:dow AND id != :id
            ");
            $dup->execute(['cid' => $classId, 'sid' => $studentId, 'dow' => $dayOfWeek, 'id' => $id]);
        }
        if ($dup->fetch()) {
            return ['error' => 'Đã có phân công khác cho học viên vào thứ này'];
        }

        if ($status === 'CONFIRMED') {
            $scheduleService = new ScheduleService();
            $dowCheck = $dayOfWeek;
            if ($dowCheck === null) {
                $classRow = $scheduleService->getClassWithCourse($classId);
                $dowCheck = (int)($classRow['day_primary'] ?? 1);
            }
            $times = $scheduleService->getScheduleTimesForClass($classId, $dowCheck)
                ?: ['start_time' => '18:00:00', 'end_time' => '20:00:00'];
            $conflicts = $scheduleService->checkTeacherConflict(
                $teacherId, $dowCheck, $times['start_time'], $times['end_time'], $id, $classId
            );
            if ($conflicts) {
                return [
                    'error' => ScheduleService::conflictClassMessage($conflicts, 'dạy'),
                    'details' => $conflicts,
                ];
            }
        }

        $ok = $this->db->prepare("
            UPDATE teacher_assignments
            SET teacher_id=:teacher_id, student_id=:student_id,
                day_of_week=:day_of_week, assignment_status=:assignment_status
            WHERE id=:id
        ")->execute([
            'teacher_id'        => $teacherId,
            'student_id'        => $studentId,
            'day_of_week'       => $dayOfWeek,
            'assignment_status' => $status,
            'id'                => $id,
        ]);

        if (!$ok) return ['error' => 'Không thể cập nhật phân công'];

        return [
            'success' => true,
            'previous_teacher_id' => (int)$current['teacher_id'],
            'teacher_id'          => $teacherId,
            'class_id'            => $classId,
        ];
    }

    public function deleteAssignment($id) {
        $stmt = $this->db->prepare("SELECT id FROM teacher_assignments WHERE id=:id");
        $stmt->execute(['id' => $id]);
        if (!$stmt->fetch()) return ['error' => 'Assignment not found'];
        $ok = $this->db->prepare("DELETE FROM teacher_assignments WHERE id=:id")->execute(['id' => $id]);
        return $ok ? ['success' => true] : ['error' => 'Failed to delete assignment'];
    }

    /** GV có phân công CONFIRMED vào lớp (bất kỳ học viên nào). */
    public function isTeacherAssignedToClass($teacherId, $classId) {
        $stmt = $this->db->prepare("
            SELECT id FROM teacher_assignments
            WHERE teacher_id = :tid AND class_id = :cid AND assignment_status = 'CONFIRMED'
            LIMIT 1
        ");
        $stmt->execute(['tid' => (int)$teacherId, 'cid' => (int)$classId]);
        return (bool)$stmt->fetch();
    }

    /**
     * GV được chấm bài nộp này (phân công CONFIRMED đúng lớp + học viên).
     * @return array|null {id, class_id, student_id} hoặc null
     */
    public function canTeacherGradeSubmission($teacherId, $submissionId) {
        $stmt = $this->db->prepare("
            SELECT sub.id, sub.class_id, sub.student_id
            FROM submissions sub
            JOIN teacher_assignments ta ON ta.class_id = sub.class_id AND ta.student_id = sub.student_id
            WHERE sub.id = :subid AND ta.teacher_id = :tid AND ta.assignment_status = 'CONFIRMED'
            LIMIT 1
        ");
        $stmt->execute(['subid' => (int)$submissionId, 'tid' => (int)$teacherId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** GV được phân công dạy học viên cụ thể trong lớp. */
    public function isTeacherAssignedToStudent($teacherId, $classId, $studentId) {
        $stmt = $this->db->prepare("
            SELECT id FROM teacher_assignments
            WHERE teacher_id = :tid AND class_id = :cid AND student_id = :sid
              AND assignment_status = 'CONFIRMED'
            LIMIT 1
        ");
        $stmt->execute([
            'tid' => (int)$teacherId,
            'cid' => (int)$classId,
            'sid' => (int)$studentId,
        ]);
        return (bool)$stmt->fetch();
    }

    /**
     * Bảng lịch dạy: GV đang phân công CONFIRMED → lớp, thứ, giờ, học viên.
     */
    public function listTeachingSchedule(?int $teacherId = null, ?int $classId = null, ?int $dayOfWeek = null): array {
        $sql = "
            SELECT ta.id AS assignment_id, ta.teacher_id, ta.class_id, ta.student_id,
                   ta.day_of_week, ta.assignment_status,
                   t.teacher_code, u.full_name AS teacher_name,
                   c.class_code, c.status AS class_status, c.start_date, c.end_date,
                   co.course_name, co.course_code,
                   co.day_primary, co.day_secondary,
                   co.default_start_time, co.default_end_time,
                   cr.room_name,
                   s.student_code, su.full_name AS student_name
            FROM teacher_assignments ta
            JOIN teachers t ON ta.teacher_id = t.id
            JOIN users u ON t.user_id = u.id
            JOIN classes c ON ta.class_id = c.id
            JOIN courses co ON c.course_id = co.id
            LEFT JOIN classrooms cr ON c.classroom_id = cr.id
            LEFT JOIN students s ON ta.student_id = s.id
            LEFT JOIN users su ON s.user_id = su.id
            WHERE ta.assignment_status = 'CONFIRMED'
              AND c.status IN ('UPCOMING', 'ONGOING')
        ";
        $params = [];
        if ($teacherId) {
            $sql .= ' AND ta.teacher_id = :teacher_id';
            $params['teacher_id'] = $teacherId;
        }
        if ($classId) {
            $sql .= ' AND ta.class_id = :class_id';
            $params['class_id'] = $classId;
        }
        $sql .= ' ORDER BY u.full_name, c.class_code, ta.day_of_week, s.student_code';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $raw = $stmt->fetchAll();

        $scheduleService = new ScheduleService();
        $weekLabels = ScheduleService::weekDayLabels();
        $rows = [];

        foreach ($raw as $row) {
            $days = [];
            if ($row['day_of_week'] === null || $row['day_of_week'] === '') {
                $days = ScheduleService::classTeachingDays($row);
                if (!$days) {
                    $days = [null];
                }
            } else {
                $days = [(int)$row['day_of_week']];
            }

            foreach ($days as $dow) {
                if ($dayOfWeek !== null && $dow !== null && (int)$dow !== $dayOfWeek) {
                    continue;
                }
                if ($dayOfWeek !== null && $dow === null) {
                    continue;
                }

                $timeDow = $dow !== null ? (int)$dow : (int)($row['day_primary'] ?? 1);
                $times = $scheduleService->getScheduleTimesForClass((int)$row['class_id'], $timeDow);

                $item = $row;
                $item['display_day'] = $dow;
                $item['day_label'] = $dow === null
                    ? 'Mọi thứ'
                    : ($weekLabels[$dow] ?? ('Thứ ' . $dow));
                $item['start_time'] = $times['start_time'] ?? $row['default_start_time'] ?? '18:00:00';
                $item['end_time'] = $times['end_time'] ?? $row['default_end_time'] ?? '20:00:00';
                $item['student_label'] = $row['student_code']
                    ? $row['student_code'] . ' — ' . ($row['student_name'] ?? '')
                    : 'Cả lớp';
                $rows[] = $item;
            }
        }

        return $rows;
    }
}
