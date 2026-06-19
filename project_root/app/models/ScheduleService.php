<?php
namespace App\Models;

use Core\Database;
use PDO;

class ScheduleService {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public static function calcEndDate(string $startDate, int $totalSessions): string {
        $weeks = (int)ceil($totalSessions / 2);
        $dt = new \DateTime($startDate);
        $dt->modify('+' . ($weeks * 7) . ' days');
        return $dt->format('Y-m-d');
    }

    public static function calcDurationWeeks(int $totalSessions): int {
        return (int)ceil($totalSessions / 2);
    }

    /** Nhãn thứ trong tuần (0=CN, 1=T2 … 6=T7 — theo lịch hệ thống). */
    public static function weekDayLabels(): array {
        return [
            0 => 'Chủ nhật',
            1 => 'Thứ hai',
            2 => 'Thứ ba',
            3 => 'Thứ tư',
            4 => 'Thứ năm',
            5 => 'Thứ sáu',
            6 => 'Thứ bảy',
        ];
    }

    /** Thứ tự hiển thị dropdown: Thứ 2 → Chủ nhật. */
    public static function weekDayOrder(): array {
        return [1, 2, 3, 4, 5, 6, 0];
    }

    /** Các thứ học của lớp/khóa: day_primary + day_secondary. */
    public static function classTeachingDays(?array $classOrCourseRow): array {
        if (!$classOrCourseRow) {
            return [];
        }
        $days = [];
        foreach (['day_primary', 'day_secondary'] as $key) {
            if (!isset($classOrCourseRow[$key]) || $classOrCourseRow[$key] === '' || $classOrCourseRow[$key] === null) {
                continue;
            }
            $dow = (int)$classOrCourseRow[$key];
            $days[$dow] = $dow;
        }
        ksort($days);
        return array_values($days);
    }

    /** Rút gọn danh sách trùng lịch thành tên lớp (không trả JSON chi tiết). */
    public static function conflictClassMessage(array $conflicts, string $kind = 'học'): string {
        $codes = array_values(array_unique(array_filter(array_column($conflicts, 'class_code'))));
        if (!$codes) {
            return 'Trùng lịch ' . $kind . ' với lớp khác';
        }
        if (count($codes) === 1) {
            return 'Trùng lịch ' . $kind . ' với lớp ' . $codes[0];
        }
        return 'Trùng lịch ' . $kind . ' với các lớp ' . implode(', ', $codes);
    }

    public function getClassWithCourse(int $classId): ?array {
        $stmt = $this->db->prepare("
            SELECT c.*, co.total_sessions, co.day_primary, co.day_secondary,
                   co.default_start_time, co.default_end_time, co.course_name, co.course_code
            FROM classes c
            JOIN courses co ON c.course_id = co.id
            WHERE c.id = :id
        ");
        $stmt->execute(['id' => $classId]);
        return $stmt->fetch() ?: null;
    }

    public function generateRegularSchedule(int $studentId, int $classId): array {
        $class = $this->getClassWithCourse($classId);
        if (!$class) return ['error' => 'Lớp không tồn tại'];

        $days = [(int)$class['day_primary'], (int)$class['day_secondary']];
        $startTime = $class['default_start_time'] ?? '18:00:00';
        $endTime = $class['default_end_time'] ?? '20:00:00';

        foreach ($days as $dow) {
            $dup = $this->db->prepare("
                SELECT id FROM schedules
                WHERE class_id=:cid AND student_id=:sid AND day_of_week=:dow
                  AND schedule_type='REGULAR' AND specific_date IS NULL LIMIT 1
            ");
            $dup->execute(['cid' => $classId, 'sid' => $studentId, 'dow' => $dow]);
            if ($dup->fetch()) continue;

            $ins = $this->db->prepare("
                INSERT INTO schedules (class_id, student_id, day_of_week, start_time, end_time, schedule_type)
                VALUES (:cid, :sid, :dow, :st, :et, 'REGULAR')
            ");
            $ins->execute([
                'cid' => $classId, 'sid' => $studentId, 'dow' => $dow,
                'st' => $startTime, 'et' => $endTime,
            ]);
        }

        $endDate = self::calcEndDate($class['start_date'], (int)$class['total_sessions']);
        $this->db->prepare("UPDATE classes SET end_date=:ed WHERE id=:id")
            ->execute(['ed' => $endDate, 'id' => $classId]);

        return ['success' => true, 'end_date' => $endDate];
    }

    /**
     * Kiểm tra GV có trùng lịch dạy ở lớp khác cùng khung giờ không.
     * Cùng một lớp + cùng thứ = không tính trùng (1 GV dạy nhiều HV trong lớp).
     */
    public function checkTeacherConflict(
        int $teacherId,
        int $dayOfWeek,
        string $startTime,
        string $endTime,
        ?int $excludeAssignmentId = null,
        ?int $forClassId = null
    ): array {
        $sql = "
            SELECT ta.id, ta.class_id, c.class_code
            FROM teacher_assignments ta
            JOIN classes c ON ta.class_id = c.id
            WHERE ta.teacher_id = :tid
              AND ta.assignment_status = 'CONFIRMED'
              AND (ta.day_of_week IS NULL OR ta.day_of_week = :dow)
        ";
        $params = ['tid' => $teacherId, 'dow' => $dayOfWeek];
        if ($excludeAssignmentId) {
            $sql .= ' AND ta.id != :ex';
            $params['ex'] = $excludeAssignmentId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $conflicts = [];
        foreach ($stmt->fetchAll() as $row) {
            $otherClassId = (int)$row['class_id'];
            if ($forClassId !== null && $otherClassId === $forClassId) {
                continue;
            }
            $times = $this->getScheduleTimesForClass($otherClassId, $dayOfWeek);
            if (!$times) {
                continue;
            }
            if ($times['start_time'] < $endTime && $times['end_time'] > $startTime) {
                $conflicts[] = array_merge($row, [
                    'start_time' => $times['start_time'],
                    'end_time'   => $times['end_time'],
                    'day_of_week' => $dayOfWeek,
                ]);
            }
        }
        return $conflicts;
    }

    public function getScheduleTimesForClass(int $classId, int $dayOfWeek): ?array {
        $stmt = $this->db->prepare("
            SELECT s.start_time, s.end_time FROM schedules s
            WHERE s.class_id=:cid AND s.day_of_week=:dow AND s.schedule_type='REGULAR'
            LIMIT 1
        ");
        $stmt->execute(['cid' => $classId, 'dow' => $dayOfWeek]);
        $row = $stmt->fetch();
        if ($row) {
            return $row;
        }
        $class = $this->getClassWithCourse($classId);
        if (!$class) {
            return null;
        }
        if (!in_array($dayOfWeek, self::classTeachingDays($class), true)) {
            return null;
        }
        return [
            'start_time' => $class['default_start_time'] ?? '18:00:00',
            'end_time'   => $class['default_end_time'] ?? '20:00:00',
        ];
    }

    public static function normalizeWeekStart(?string $date = null): string {
        $base = $date ?: date('Y-m-d');
        return date('Y-m-d', strtotime('monday this week', strtotime($base)));
    }

    /**
     * REGULAR: một buổi/tuần theo day_of_week trong khoảng start_date–end_date của lớp.
     * EXAM/MAKEUP: chỉ khi specific_date nằm trong tuần đang xem.
     */
    public function expandSchedulesForWeek(array $items, string $weekStart): array {
        $weekStart = self::normalizeWeekStart($weekStart);
        $weekEnd = date('Y-m-d', strtotime($weekStart . ' +6 days'));
        $expanded = [];
        $seen = [];

        foreach ($items as $item) {
            $type = $item['schedule_type'] ?? 'REGULAR';
            $specificDate = !empty($item['specific_date']) ? $item['specific_date'] : null;

            if ($specificDate && in_array($type, ['REGULAR', 'EXAM', 'MAKEUP', 'EXTRA'], true)) {
                if ($specificDate < $weekStart || $specificDate > $weekEnd) {
                    continue;
                }
                $row = $item;
                $row['session_date'] = $specificDate;
                $row['day_of_week'] = (int)date('w', strtotime($specificDate));
                $key = $this->scheduleRowKey($row);
                if (!isset($seen[$key])) {
                    $seen[$key] = true;
                    $expanded[] = $row;
                }
                continue;
            }

            if ($type !== 'REGULAR') {
                continue;
            }

            $dow = (int)$item['day_of_week'];
            $classStart = $item['start_date'] ?? null;
            $classEnd = $item['end_date'] ?? null;

            for ($offset = 0; $offset < 7; $offset++) {
                $sessionDate = date('Y-m-d', strtotime($weekStart . " +{$offset} days"));
                if ((int)date('w', strtotime($sessionDate)) !== $dow) {
                    continue;
                }
                if ($classStart && $sessionDate < $classStart) {
                    continue;
                }
                if ($classEnd && $sessionDate > $classEnd) {
                    continue;
                }
                $row = $item;
                $row['session_date'] = $sessionDate;
                $row['day_of_week'] = $dow;
                $key = $this->scheduleRowKey($row);
                if (!isset($seen[$key])) {
                    $seen[$key] = true;
                    $expanded[] = $row;
                }
            }
        }

        usort($expanded, function ($a, $b) {
            $da = ($a['session_date'] ?? '') . ($a['start_time'] ?? '');
            $db = ($b['session_date'] ?? '') . ($b['start_time'] ?? '');
            return strcmp($da, $db);
        });

        return $expanded;
    }

    public function groupSchedulesByDay(array $items): array {
        $byDay = [];
        foreach ($items as $item) {
            $byDay[(int)$item['day_of_week']][] = $item;
        }
        return $byDay;
    }

    /** Chuẩn hóa DATE/DATETIME → Y-m-d (đồng bộ với session_date trên lịch). */
    public static function normalizeDateOnly(?string $date): ?string {
        if ($date === null || $date === '') {
            return null;
        }
        $ts = strtotime(substr((string)$date, 0, 10));
        return $ts ? date('Y-m-d', $ts) : null;
    }

    public static function attendanceLookupKey(int $classId, string $date, ?int $studentId = null): string {
        $d = self::normalizeDateOnly($date) ?? '';
        if ($studentId) {
            return $classId . '_' . $studentId . '_' . $d;
        }
        return $classId . '_' . $d;
    }

    /** Map attendance theo class_id (+ student_id) + ngày buổi học. */
    public function buildAttendanceLookup(array $records): array {
        $map = [];
        foreach ($records as $r) {
            $date = self::normalizeDateOnly($r['attendance_date'] ?? null);
            if (!$date) {
                continue;
            }
            $classId = (int)($r['class_id'] ?? 0);
            $studentId = (int)($r['student_id'] ?? 0);
            if (!$classId) {
                continue;
            }
            $map[self::attendanceLookupKey($classId, $date)] = $r;
            if ($studentId) {
                $map[self::attendanceLookupKey($classId, $date, $studentId)] = $r;
            }
        }
        return $map;
    }

    /** Lấy điểm danh khớp các buổi học trong tuần (theo session_date). */
    public function getAttendanceForSessions(array $sessionItems, ?int $studentId = null): array {
        $dates = [];
        $classIds = [];
        foreach ($sessionItems as $item) {
            $d = self::normalizeDateOnly($item['session_date'] ?? null);
            if ($d) {
                $dates[] = $d;
            }
            if (!empty($item['class_id'])) {
                $classIds[] = (int)$item['class_id'];
            }
        }
        if (empty($dates)) {
            return [];
        }

        $params = ['d1' => min($dates), 'd2' => max($dates)];
        $sql = 'SELECT * FROM attendance WHERE attendance_date BETWEEN :d1 AND :d2';
        if ($studentId) {
            $sql .= ' AND student_id = :sid';
            $params['sid'] = $studentId;
        } elseif (!empty($classIds)) {
            $classIds = array_values(array_unique($classIds));
            $parts = [];
            foreach ($classIds as $i => $cid) {
                $key = 'c' . $i;
                $parts[] = ':' . $key;
                $params[$key] = $cid;
            }
            $sql .= ' AND class_id IN (' . implode(',', $parts) . ')';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Gắn trạng thái điểm danh vào từng buổi theo class_id + session_date. */
    public function attachAttendanceToSessions(array $items, array $attendanceLookup, ?int $defaultStudentId = null): array {
        foreach ($items as &$item) {
            $sessionDate = self::normalizeDateOnly($item['session_date'] ?? null);
            if (!$sessionDate) {
                continue;
            }
            $classId = (int)($item['class_id'] ?? 0);
            $studentId = (int)($item['student_id'] ?? $item['schedule_student_id'] ?? $defaultStudentId ?? 0);
            $rec = null;
            if ($studentId) {
                $rec = $attendanceLookup[self::attendanceLookupKey($classId, $sessionDate, $studentId)] ?? null;
            }
            if (!$rec) {
                $rec = $attendanceLookup[self::attendanceLookupKey($classId, $sessionDate)] ?? null;
            }
            if ($rec) {
                $item['attendance_status'] = $rec['attendance_status'];
                $item['attendance_marked'] = true;
                $item['tinh_luong'] = (int)($rec['tinh_luong'] ?? 0);
                $item['attendance_date'] = self::normalizeDateOnly($rec['attendance_date']);
            } else {
                $item['attendance_marked'] = false;
            }
        }
        unset($item);
        return $items;
    }

    private function scheduleRowKey(array $row): string {
        return implode('|', [
            $row['class_id'] ?? '',
            $row['student_id'] ?? '',
            $row['session_date'] ?? '',
            $row['schedule_type'] ?? '',
            $row['start_time'] ?? '',
            $row['end_time'] ?? '',
        ]);
    }

    public function getTeacherSchedule(int $teacherId, ?string $weekStart = null): array {
        $weekStart = self::normalizeWeekStart($weekStart);
        $weekEnd = date('Y-m-d', strtotime($weekStart . ' +6 days'));

        $stmt = $this->db->prepare("
            SELECT ta.*, c.id AS class_id, c.class_code, c.start_date, c.end_date,
                   co.course_name, s.id AS schedule_id, s.day_of_week, s.start_time, s.end_time,
                   s.schedule_type, s.specific_date, s.student_id AS schedule_student_id,
                   st.student_code, u.full_name AS student_name
            FROM teacher_assignments ta
            JOIN classes c ON ta.class_id = c.id
            JOIN courses co ON c.course_id = co.id
            JOIN schedules s ON s.class_id = c.id
                AND (
                    (s.schedule_type IN ('EXAM','MAKEUP','EXTRA') AND (s.student_id IS NULL OR s.student_id = ta.student_id))
                    OR (s.schedule_type = 'REGULAR' AND s.student_id = ta.student_id)
                )
            LEFT JOIN students st ON ta.student_id = st.id
            LEFT JOIN users u ON st.user_id = u.id
            WHERE ta.teacher_id = :tid AND ta.assignment_status = 'CONFIRMED'
              AND c.start_date <= :we AND c.end_date >= :ws
            ORDER BY s.day_of_week, s.start_time
        ");
        $stmt->execute(['tid' => $teacherId, 'ws' => $weekStart, 'we' => $weekEnd]);
        return $this->expandSchedulesForWeek($stmt->fetchAll(), $weekStart);
    }

    public function checkStudentScheduleConflict(int $studentId, int $newClassId): array {
        $newClass = $this->getClassWithCourse($newClassId);
        if (!$newClass) return [];

        $newDays = [(int)$newClass['day_primary'], (int)$newClass['day_secondary']];
        $newStart = $newClass['default_start_time'] ?? '18:00:00';
        $newEnd = $newClass['default_end_time'] ?? '20:00:00';

        $stmt = $this->db->prepare("
            SELECT s.day_of_week, s.start_time, s.end_time, c.class_code
            FROM schedules s
            JOIN classes c ON s.class_id = c.id
            JOIN enrollments e ON e.class_id = c.id AND e.student_id = :sid AND e.payment_status = 'PAID'
            WHERE s.student_id = :sid2 AND s.schedule_type = 'REGULAR' AND s.class_id != :newcid
        ");
        $stmt->execute(['sid' => $studentId, 'sid2' => $studentId, 'newcid' => $newClassId]);
        $existing = $stmt->fetchAll();
        $conflicts = [];
        foreach ($existing as $ex) {
            if (!in_array((int)$ex['day_of_week'], $newDays, true)) continue;
            if ($ex['start_time'] < $newEnd && $ex['end_time'] > $newStart) {
                $conflicts[] = $ex;
            }
        }
        return $conflicts;
    }

    /** Các biến thể thứ (0=CN PHP w, 7=CN ISO-N) để khớp phân công & lịch. */
    private static function dayOfWeekVariants(int $dow): array {
        if ($dow === 0) {
            return [0, 7];
        }
        if ($dow === 7) {
            return [7, 0];
        }
        return [$dow];
    }

    /**
     * Tìm GV cho buổi học: ưu tiên phân công theo HV → phân công cả lớp → GV mặc định lớp.
     */
    public function resolveTeacherForSession(int $classId, int $studentId, int $dayOfWeek): ?array {
        $variants = self::dayOfWeekVariants($dayOfWeek);
        $inList = implode(',', array_fill(0, count($variants), '?'));

        $sql = "
            SELECT t.teacher_code, tu.full_name AS teacher_name
            FROM teacher_assignments ta
            JOIN teachers t ON ta.teacher_id = t.id
            JOIN users tu ON t.user_id = tu.id
            WHERE ta.class_id = ?
              AND ta.assignment_status = 'CONFIRMED'
              AND (ta.student_id = ? OR ta.student_id IS NULL)
              AND (ta.day_of_week IS NULL OR ta.day_of_week IN ($inList))
            ORDER BY (ta.student_id IS NOT NULL) DESC, ta.id DESC
            LIMIT 1
        ";
        $params = array_merge([$classId, $studentId], $variants);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        if ($row = $stmt->fetch()) {
            return $row;
        }

        $fallback = $this->db->prepare("
            SELECT t.teacher_code, tu.full_name AS teacher_name
            FROM classes c
            JOIN teachers t ON c.teacher_id = t.id
            JOIN users tu ON t.user_id = tu.id
            WHERE c.id = ? AND c.teacher_id IS NOT NULL
        ");
        $fallback->execute([$classId]);
        $row = $fallback->fetch();
        return $row ?: null;
    }

    public function attachTeachersToSessions(array $items, int $studentId): array {
        foreach ($items as &$item) {
            $sessionDow = (int)($item['day_of_week'] ?? 0);
            if (!empty($item['session_date'])) {
                $sessionDow = (int)date('w', strtotime($item['session_date']));
            }
            $teacher = $this->resolveTeacherForSession(
                (int)$item['class_id'],
                $studentId,
                $sessionDow
            );
            if ($teacher) {
                $item['teacher_name'] = $teacher['teacher_name'];
                $item['teacher_code'] = $teacher['teacher_code'] ?? null;
            }
        }
        unset($item);
        return $items;
    }

    public function getStudentScheduleForWeek(int $studentId, ?string $weekStart = null, ?int $classId = null): array {
        $weekStart = self::normalizeWeekStart($weekStart);

        $sql = "
            SELECT s.*, c.id AS class_id, c.class_code, c.start_date, c.end_date, co.course_name
            FROM schedules s
            JOIN classes c ON s.class_id = c.id
            JOIN courses co ON c.course_id = co.id
            JOIN enrollments e ON e.class_id = c.id AND e.student_id = :sid
                AND e.payment_status IN ('PAID','COMPLETED') AND e.status = 'ACTIVE'
            WHERE (
                (s.schedule_type = 'REGULAR' AND s.student_id = :sid2)
                OR (s.schedule_type IN ('EXAM','MAKEUP','EXTRA') AND (s.student_id IS NULL OR s.student_id = :sid3))
            )
              AND c.start_date <= DATE_ADD(:week_start, INTERVAL 6 DAY)
              AND c.end_date >= :week_end
        ";
        $params = [
            'sid' => $studentId,
            'sid2' => $studentId,
            'sid3' => $studentId,
            'week_start' => $weekStart,
            'week_end' => $weekStart,
        ];
        if ($classId) {
            $sql .= ' AND c.id = :class_id';
            $params['class_id'] = $classId;
        }
        $sql .= ' ORDER BY s.day_of_week, s.start_time';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $items = $this->expandSchedulesForWeek($stmt->fetchAll(), $weekStart);
        return $this->attachTeachersToSessions($items, $studentId);
    }
}
