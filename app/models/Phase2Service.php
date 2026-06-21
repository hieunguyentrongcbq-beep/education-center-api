<?php
namespace App\Models;

use Core\Database;
use PDO;

class Phase2Service {
    private $db;
    private $notify;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->notify = new instructormodel();
    }

    // --- LEAVE / MAKEUP ---
    public function getStudentEnrolledClasses($studentId) {
        $stmt = $this->db->prepare("
            SELECT c.id, c.class_code, co.course_name
            FROM enrollments e
            JOIN classes c ON e.class_id = c.id
            JOIN courses co ON c.course_id = co.id
            WHERE e.student_id = :sid AND e.payment_status = 'PAID' AND e.status = 'ACTIVE'
            ORDER BY c.class_code
        ");
        $stmt->execute(['sid' => (int)$studentId]);
        return $stmt->fetchAll();
    }

    public function isStudentEnrolledInClass($studentId, $classId) {
        $stmt = $this->db->prepare("
            SELECT id FROM enrollments
            WHERE student_id = :sid AND class_id = :cid
              AND payment_status = 'PAID' AND status = 'ACTIVE'
            LIMIT 1
        ");
        $stmt->execute(['sid' => (int)$studentId, 'cid' => (int)$classId]);
        return (bool)$stmt->fetch();
    }

    public function createLeaveRequest($type, $requesterType, $requesterId, $classId, $date, $reason) {
        $type = ($type === 'MAKEUP') ? 'MAKEUP' : 'LEAVE';
        $reason = trim((string)$reason);
        $classId = (int)$classId;

        if (!$classId) {
            return ['error' => 'Vui lòng chọn lớp'];
        }
        if (empty($date)) {
            return ['error' => 'Ngày là bắt buộc'];
        }
        if ($reason === '') {
            return ['error' => 'Lý do là bắt buộc'];
        }

        if ($requesterType === 'TEACHER') {
            $assign = new assigning();
            if (!$assign->isTeacherAssignedToClass((int)$requesterId, $classId)) {
                return ['error' => 'Bạn không được phân công lớp này'];
            }
        } elseif ($requesterType === 'STUDENT') {
            if (!$this->isStudentEnrolledInClass((int)$requesterId, $classId)) {
                return ['error' => 'Bạn chưa ghi danh hoặc chưa thanh toán lớp này'];
            }
        }

        $stmt = $this->db->prepare("
            INSERT INTO leave_requests (requester_type, requester_id, class_id, request_date, reason, request_type, status)
            VALUES (:rt, :rid, :cid, :dt, :rs, :tp, 'PENDING')
        ");
        $ok = $stmt->execute([
            'rt' => $requesterType, 'rid' => $requesterId, 'cid' => $classId,
            'dt' => $date, 'rs' => $reason, 'tp' => $type,
        ]);
        if ($ok) {
            $this->notifyAdmins("Yêu cầu $type mới", "Có yêu cầu $type cần duyệt (ngày $date).");
            AuditLog::write($_SESSION['user']['id'] ?? null, 'CREATE_LEAVE', 'leave_requests', (int)$this->db->lastInsertId());
        }
        return $ok ? ['success' => true] : ['error' => 'Không thể tạo yêu cầu'];
    }

    public function listMyLeaveRequests($requesterType, $requesterId) {
        $stmt = $this->db->prepare("
            SELECT lr.*, c.class_code FROM leave_requests lr
            LEFT JOIN classes c ON lr.class_id=c.id
            WHERE lr.requester_type=:rt AND lr.requester_id=:rid ORDER BY lr.id DESC
        ");
        $stmt->execute(['rt' => $requesterType, 'rid' => $requesterId]);
        return $stmt->fetchAll();
    }

    public function listLeaveRequests($status = null, $type = null) {
        $sql = "SELECT lr.*, c.class_code,
                CASE lr.requester_type
                    WHEN 'TEACHER' THEN (SELECT u.full_name FROM teachers t JOIN users u ON t.user_id=u.id WHERE t.id=lr.requester_id)
                    ELSE (SELECT u.full_name FROM students s JOIN users u ON s.user_id=u.id WHERE s.id=lr.requester_id)
                END AS requester_name
            FROM leave_requests lr LEFT JOIN classes c ON lr.class_id=c.id WHERE 1=1";
        $params = [];
        if ($status) { $sql .= " AND lr.status=:st"; $params['st'] = $status; }
        if ($type) { $sql .= " AND lr.request_type=:tp"; $params['tp'] = $type; }
        $sql .= " ORDER BY lr.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function reviewLeaveRequest($id, $status, $adminUserId) {
        $reqStmt = $this->db->prepare("SELECT * FROM leave_requests WHERE id=:id AND status='PENDING'");
        $reqStmt->execute(['id' => $id]);
        $row = $reqStmt->fetch();
        if (!$row) {
            return ['error' => 'Yêu cầu không tồn tại hoặc đã được xử lý'];
        }

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                UPDATE leave_requests SET status=:st, reviewed_by=:rb WHERE id=:id AND status='PENDING'
            ");
            $stmt->execute(['st' => $status, 'rb' => $adminUserId, 'id' => $id]);
            if (!$stmt->rowCount()) {
                $this->db->rollBack();
                return ['error' => 'Không thể duyệt yêu cầu'];
            }

            $scheduleMsg = '';
            if ($status === 'APPROVED' && ($row['request_type'] ?? 'LEAVE') === 'MAKEUP') {
                $schedResult = $this->createMakeupScheduleFromLeave($row);
                if (isset($schedResult['error'])) {
                    $this->db->rollBack();
                    return $schedResult;
                }
                $scheduleMsg = '. Đã tạo buổi học bù ngày ' . date('d/m/Y', strtotime($row['request_date']));
            }

            $this->db->commit();
            AuditLog::write($adminUserId, 'REVIEW_' . $status, 'leave_requests', $id);
            $this->notifyLeaveReviewed($row, $status, $scheduleMsg);
            return ['success' => true, 'message' => $scheduleMsg];
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['error' => 'Lỗi xử lý: ' . $e->getMessage()];
        }
    }

    /**
     * Tạo lịch MAKEUP khi admin duyệt yêu cầu học bù.
     */
    private function createMakeupScheduleFromLeave(array $leaveRow): array {
        if (empty($leaveRow['class_id']) || empty($leaveRow['request_date'])) {
            return ['error' => 'Thiếu thông tin lớp hoặc ngày học bù'];
        }

        $classId = (int)$leaveRow['class_id'];
        $requestDate = $leaveRow['request_date'];
        $dayOfWeek = (int)date('w', strtotime($requestDate));

        $classStmt = $this->db->prepare("
            SELECT c.class_code, co.default_start_time, co.default_end_time
            FROM classes c
            JOIN courses co ON c.course_id = co.id
            WHERE c.id = :id
        ");
        $classStmt->execute(['id' => $classId]);
        $class = $classStmt->fetch();
        if (!$class) {
            return ['error' => 'Lớp học không tồn tại'];
        }

        $startTime = $class['default_start_time'] ?? '18:00:00';
        $endTime = $class['default_end_time'] ?? '20:00:00';

        if ($leaveRow['requester_type'] === 'TEACHER') {
            $teacherId = (int)$leaveRow['requester_id'];
            $scheduleService = new ScheduleService();
            $conflicts = $scheduleService->checkTeacherConflict(
                $teacherId, $dayOfWeek, $startTime, $endTime, null, $classId
            );
            if ($conflicts) {
                return ['error' => 'Giáo viên trùng lịch vào ngày học bù này'];
            }
        }

        $studentId = ($leaveRow['requester_type'] === 'STUDENT')
            ? (int)$leaveRow['requester_id']
            : null;

        $classModel = new classmodel();
        $result = $classModel->createSchedule([
            'class_id'      => $classId,
            'student_id'    => $studentId,
            'day_of_week'   => $dayOfWeek,
            'specific_date' => $requestDate,
            'start_time'    => $startTime,
            'end_time'      => $endTime,
            'schedule_type' => 'MAKEUP',
        ]);

        if (isset($result['error'])) {
            return $result;
        }

        AuditLog::write(
            $_SESSION['user']['id'] ?? null,
            'CREATE_MAKEUP_SCHEDULE',
            'schedules',
            (int)$result['id']
        );

        $this->notify->notifyClassScheduleChange($classId, [
            'schedule_type' => 'MAKEUP',
            'specific_date' => $requestDate,
            'day_of_week'   => $dayOfWeek,
            'start_time'    => $startTime,
            'end_time'      => $endTime,
            'student_id'    => $studentId,
        ], false);

        return ['success' => true, 'schedule_id' => $result['id']];
    }

    private function notifyLeaveReviewed(array $row, string $status, string $scheduleMsg = ''): void {
        $title = 'Yêu cầu ' . ($row['request_type'] ?? 'LEAVE');
        $content = 'Trạng thái: ' . $status . $scheduleMsg;

        if ($row['requester_type'] === 'TEACHER') {
            $u = $this->db->prepare("SELECT u.id FROM teachers t JOIN users u ON t.user_id=u.id WHERE t.id=:id");
            $u->execute(['id' => $row['requester_id']]);
            if ($usr = $u->fetch()) {
                $this->notify->createNotification($usr['id'], $title, $content);
            }
        } elseif ($row['requester_type'] === 'STUDENT') {
            $u = $this->db->prepare("SELECT u.id FROM students s JOIN users u ON s.user_id=u.id WHERE s.id=:id");
            $u->execute(['id' => $row['requester_id']]);
            if ($usr = $u->fetch()) {
                $this->notify->createNotification($usr['id'], $title, $content);
            }
        }
    }

    private function notifyAdmins($title, $content) {
        $admins = $this->db->query("
            SELECT u.id FROM users u
            JOIN user_roles ur ON u.id=ur.user_id
            JOIN roles r ON ur.role_id=r.id
            WHERE r.role_name='ADMIN'
        ")->fetchAll();
        foreach ($admins as $a) {
            $this->notify->createNotification($a['id'], $title, $content);
        }
    }

    // --- SUBMISSIONS ---
    private function validateSubmissionData($studentId, $classId, $type, $filePath) {
        $studentId = (int)$studentId;
        $classId = (int)$classId;

        if (!$studentId || !$classId) {
            return ['error' => 'Thiếu lớp hoặc học viên'];
        }
        if (!$filePath) {
            return ['error' => 'Thiếu file nộp'];
        }

        $allowedTypes = ['ASSIGNMENT', 'MIDTERM', 'FINAL'];
        if (!in_array($type, $allowedTypes, true)) {
            return ['error' => 'Loại bài nộp không hợp lệ'];
        }

        if (!$this->isStudentEnrolledInClass($studentId, $classId)) {
            return ['error' => 'Bạn chưa ghi danh hoặc chưa thanh toán lớp này'];
        }

        return null;
    }

    public function saveSubmission($studentId, $classId, $type, $filePath) {
        $err = $this->validateSubmissionData($studentId, $classId, $type, $filePath);
        if ($err) {
            return $err;
        }

        $exist = $this->db->prepare("
            SELECT id FROM submissions
            WHERE student_id = :sid AND class_id = :cid AND type = :tp
            LIMIT 1
        ");
        $exist->execute(['sid' => $studentId, 'cid' => $classId, 'tp' => $type]);
        $existing = $exist->fetch();

        if ($existing) {
            $subId = (int)$existing['id'];
            $ok = $this->db->prepare("
                UPDATE submissions
                SET file_path = :fp, status = 'PENDING', submitted_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ")->execute(['fp' => $filePath, 'id' => $subId]);
            if ($ok) {
                $this->db->prepare("DELETE FROM grades WHERE submission_id = :id")->execute(['id' => $subId]);
                AuditLog::write($_SESSION['user']['id'] ?? null, 'UPDATE_SUBMISSION', 'submissions', $subId);
                $this->notifyTeachersOfClass($classId, 'Bài nộp mới', "Học viên đã nộp lại bài ($type).");
            }
            return $ok ? ['success' => true, 'id' => $subId, 'updated' => true] : ['error' => 'Lưu thất bại'];
        }

        $stmt = $this->db->prepare("
            INSERT INTO submissions (student_id, class_id, type, file_path, status)
            VALUES (:sid, :cid, :tp, :fp, 'PENDING')
        ");
        $ok = $stmt->execute(['sid' => $studentId, 'cid' => $classId, 'tp' => $type, 'fp' => $filePath]);
        if ($ok) {
            $subId = (int)$this->db->lastInsertId();
            AuditLog::write($_SESSION['user']['id'] ?? null, 'UPLOAD_SUBMISSION', 'submissions', $subId);
            $this->notifyTeachersOfClass($classId, 'Bài nộp mới', "Học viên đã nộp bài ($type).");
        }
        return $ok ? ['success' => true, 'id' => $this->db->lastInsertId()] : ['error' => 'Lưu thất bại'];
    }

    public function getSubmissionsByStudent($studentId) {
        $stmt = $this->db->prepare("
            SELECT sub.*, c.class_code, co.course_name, g.score, g.comment, g.graded_at
            FROM submissions sub
            JOIN classes c ON sub.class_id=c.id
            JOIN courses co ON c.course_id=co.id
            LEFT JOIN grades g ON g.submission_id=sub.id
            WHERE sub.student_id=:sid ORDER BY sub.id DESC
        ");
        $stmt->execute(['sid' => $studentId]);
        return $stmt->fetchAll();
    }

    public function getSubmissionForStudent(int $submissionId, int $studentId): ?array {
        $stmt = $this->db->prepare("
            SELECT sub.*, c.class_code, co.course_name
            FROM submissions sub
            JOIN classes c ON sub.class_id = c.id
            JOIN courses co ON c.course_id = co.id
            WHERE sub.id = :id AND sub.student_id = :sid
        ");
        $stmt->execute(['id' => $submissionId, 'sid' => $studentId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function submissionDownloadFilename(array $submission): string {
        $type = $submission['type'] ?? 'ASSIGNMENT';
        if ($type === 'MIDTERM') {
            $typeSlug = 'giua-ki';
        } elseif ($type === 'FINAL') {
            $typeSlug = 'cuoi-ki';
        } else {
            $typeSlug = 'bai-tap';
        }
        $class = preg_replace('/[^\w\-]+/', '_', $submission['class_code'] ?? 'lop');
        $date = !empty($submission['submitted_at'])
            ? date('Ymd', strtotime($submission['submitted_at']))
            : date('Ymd');
        return "{$class}_{$typeSlug}_{$date}.pdf";
    }

    public function getSubmissionsForTeacher($teacherId, $classId = null) {
        $sql = "
            SELECT sub.*, s.student_code, u.full_name, c.class_code, g.score, g.comment, g.id AS grade_id
            FROM submissions sub
            JOIN students s ON sub.student_id = s.id
            JOIN users u ON s.user_id = u.id
            JOIN classes c ON sub.class_id = c.id
            LEFT JOIN grades g ON g.submission_id = sub.id
            WHERE EXISTS (
                SELECT 1 FROM teacher_assignments ta
                WHERE ta.class_id = sub.class_id
                  AND ta.student_id = sub.student_id
                  AND ta.teacher_id = :tid
                  AND ta.assignment_status = 'CONFIRMED'
            )
              AND sub.id = (
                SELECT MAX(s2.id) FROM submissions s2
                WHERE s2.student_id = sub.student_id
                  AND s2.class_id = sub.class_id
                  AND s2.type = sub.type
              )
        ";
        $params = ['tid' => $teacherId];
        if ($classId) {
            $sql .= ' AND sub.class_id = :cid';
            $params['cid'] = $classId;
        }
        $sql .= ' ORDER BY sub.id DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function gradeSubmission($submissionId, $teacherId, $score, $comment) {
        $assign = new assigning();
        $sub = $assign->canTeacherGradeSubmission((int)$teacherId, (int)$submissionId);
        if (!$sub) {
            return ['error' => 'Không có quyền chấm bài lớp này'];
        }

        $score = (float)$score;
        if ($score < 0 || $score > 10) {
            return ['error' => 'Điểm phải từ 0 đến 10'];
        }

        $studentId = (int)$sub['student_id'];
        $classId = (int)$sub['class_id'];

        $exist = $this->db->prepare("SELECT id FROM grades WHERE submission_id=:sid");
        $exist->execute(['sid' => $submissionId]);
        if ($row = $exist->fetch()) {
            $ok = $this->db->prepare("UPDATE grades SET score=:sc, comment=:cm, teacher_id=:tid, graded_at=NOW() WHERE id=:id")
                ->execute(['sc' => $score, 'cm' => $comment, 'tid' => $teacherId, 'id' => $row['id']]);
        } else {
            $ok = $this->db->prepare("
                INSERT INTO grades (submission_id, student_id, class_id, teacher_id, score, comment)
                VALUES (:sid, :stuid, :cid, :tid, :sc, :cm)
            ")->execute([
                'sid' => $submissionId,
                'stuid' => $studentId,
                'cid' => $classId,
                'tid' => $teacherId,
                'sc' => $score,
                'cm' => $comment,
            ]);
        }
        if ($ok) {
            $this->db->prepare("UPDATE submissions SET status='GRADED' WHERE id=:id")->execute(['id' => $submissionId]);
            $sub = $this->db->prepare("SELECT student_id, class_id FROM submissions WHERE id=:id");
            $sub->execute(['id' => $submissionId]);
            $s = $sub->fetch();
            if ($s) {
                $this->updateStudentEvaluation((int)$s['student_id'], (int)$s['class_id'], $teacherId);
                $stu = $this->db->prepare("SELECT u.id FROM students st JOIN users u ON st.user_id=u.id WHERE st.id=:id");
                $stu->execute(['id' => $s['student_id']]);
                if ($u = $stu->fetch()) {
                    $this->notify->createNotification($u['id'], 'Bài đã được chấm', "Điểm: $score/10. $comment");
                }
            }
            AuditLog::write($_SESSION['user']['id'] ?? null, 'GRADE', 'submissions', $submissionId);
        }
        return $ok ? ['success' => true] : ['error' => 'Chấm điểm thất bại'];
    }

    private function notifyTeachersOfClass($classId, $title, $content) {
        $tvs = $this->db->prepare("SELECT u.id FROM teacher_assignments ta JOIN teachers t ON ta.teacher_id=t.id JOIN users u ON t.user_id=u.id WHERE ta.class_id=:cid");
        $tvs->execute(['cid' => $classId]);
        foreach ($tvs->fetchAll() as $t) {
            $this->notify->createNotification($t['id'], $title, $content);
        }
    }

    // --- EVALUATIONS ---
    public function updateStudentEvaluation($studentId, $classId, $teacherId) {
        $graded = $this->db->prepare("
            SELECT COUNT(g.id) AS cnt, AVG(g.score) AS avg_score
            FROM grades g
            JOIN submissions sub ON g.submission_id = sub.id
            WHERE sub.student_id = :sid AND sub.class_id = :cid
        ");
        $graded->execute(['sid' => $studentId, 'cid' => $classId]);
        $row = $graded->fetch();

        $exist = $this->db->prepare("SELECT id FROM student_evaluations WHERE student_id=:sid AND class_id=:cid");
        $exist->execute(['sid' => $studentId, 'cid' => $classId]);
        $existing = $exist->fetch();

        if (!(int)($row['cnt'] ?? 0)) {
            if (!$existing) {
                $this->db->prepare("
                    INSERT INTO student_evaluations (student_id, class_id, teacher_id, avg_score, level, retake_needed)
                    VALUES (:sid, :cid, :tid, 0, 'TRUNG_BINH', 0)
                ")->execute(['sid' => $studentId, 'cid' => $classId, 'tid' => $teacherId]);
            }
            return;
        }

        $avgScore = (float)$row['avg_score'];
        $level = $this->scoreToLevel($avgScore);
        $retake = ($avgScore < 5) ? 1 : 0;

        if ($existing) {
            $this->db->prepare("
                UPDATE student_evaluations
                SET avg_score = :av, level = :lv, retake_needed = :rt, teacher_id = :tid, updated_at = NOW()
                WHERE id = :id
            ")->execute(['av' => $avgScore, 'lv' => $level, 'rt' => $retake, 'tid' => $teacherId, 'id' => $existing['id']]);
        } else {
            $this->db->prepare("
                INSERT INTO student_evaluations (student_id, class_id, teacher_id, avg_score, level, retake_needed)
                VALUES (:sid, :cid, :tid, :av, :lv, :rt)
            ")->execute(['sid' => $studentId, 'cid' => $classId, 'tid' => $teacherId, 'av' => $avgScore, 'lv' => $level, 'rt' => $retake]);
        }
    }

    /** Tất cả HV được GV phân công trong lớp (kể cả chưa có student_evaluations). */
    public function getClassEvaluationsForTeacher($classId, $teacherId) {
        $stmt = $this->db->prepare("
            SELECT DISTINCT s.id AS student_id, s.student_code, u.full_name,
                   ev.id AS evaluation_id, ev.avg_score, ev.level, ev.retake_needed, ev.teacher_comment,
                   (SELECT COUNT(g.id) FROM grades g
                    JOIN submissions sub ON g.submission_id = sub.id
                    WHERE sub.student_id = s.id AND sub.class_id = :cid2) AS graded_count
            FROM teacher_assignments ta
            JOIN students s ON ta.student_id = s.id
            JOIN users u ON s.user_id = u.id
            JOIN enrollments e ON e.student_id = s.id AND e.class_id = ta.class_id
                AND e.payment_status = 'PAID' AND e.status = 'ACTIVE'
            LEFT JOIN student_evaluations ev ON ev.student_id = s.id AND ev.class_id = ta.class_id
            WHERE ta.class_id = :cid AND ta.teacher_id = :tid AND ta.assignment_status = 'CONFIRMED'
            ORDER BY s.student_code
        ");
        $stmt->execute(['cid' => (int)$classId, 'cid2' => (int)$classId, 'tid' => (int)$teacherId]);
        return $stmt->fetchAll();
    }

    public function setEvaluationComment($studentId, $classId, $teacherId, $comment, $retake = null) {
        $assign = new assigning();
        if (!$assign->isTeacherAssignedToStudent((int)$teacherId, (int)$classId, (int)$studentId)) {
            return ['error' => 'Bạn không được phân công học viên trong lớp này'];
        }

        $this->updateStudentEvaluation($studentId, $classId, $teacherId);
        $params = ['cm' => $comment, 'tid' => $teacherId, 'sid' => $studentId, 'cid' => $classId];
        $sql = "UPDATE student_evaluations SET teacher_comment=:cm, teacher_id=:tid";
        if ($retake !== null) { $sql .= ", retake_needed=:rt"; $params['rt'] = $retake ? 1 : 0; }
        $sql .= " WHERE student_id=:sid AND class_id=:cid";
        $this->db->prepare($sql)->execute($params);
        return ['success' => true];
    }

    private function scoreToLevel($score) {
        if ($score >= 8) return 'GIOI';
        if ($score >= 6.5) return 'KHA';
        if ($score >= 5) return 'TRUNG_BINH';
        return 'KEM';
    }

    public function levelLabel($level) {
        $map = ['GIOI' => 'Giỏi', 'KHA' => 'Khá', 'TRUNG_BINH' => 'Trung bình', 'KEM' => 'Kém'];
        return $map[$level] ?? $level;
    }

    public function getEvaluation($studentId, $classId = null) {
        $sql = "SELECT ev.*, c.class_code, co.course_name, u.full_name AS teacher_name
            FROM student_evaluations ev
            JOIN classes c ON ev.class_id=c.id JOIN courses co ON c.course_id=co.id
            LEFT JOIN teachers t ON ev.teacher_id=t.id LEFT JOIN users u ON t.user_id=u.id
            WHERE ev.student_id=:sid";
        $params = ['sid' => $studentId];
        if ($classId) { $sql .= " AND ev.class_id=:cid"; $params['cid'] = $classId; }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $classId ? $stmt->fetch() : $stmt->fetchAll();
    }

    public function getClassScoreComparison($classId) {
        $stmt = $this->db->prepare("
            SELECT ev.*, s.student_code, u.full_name
            FROM student_evaluations ev
            JOIN students s ON ev.student_id=s.id
            JOIN users u ON s.user_id=u.id
            WHERE ev.class_id=:cid
            ORDER BY ev.avg_score DESC
        ");
        $stmt->execute(['cid' => $classId]);
        $rows = $stmt->fetchAll();
        $rank = 1;
        foreach ($rows as &$r) { $r['rank_pos'] = $rank++; }
        return $rows;
    }

    // --- SURVEYS ---
    public function getOrCreateSurvey($classId) {
        $stmt = $this->db->prepare("SELECT * FROM surveys WHERE class_id=:cid LIMIT 1");
        $stmt->execute(['cid' => $classId]);
        if ($s = $stmt->fetch()) return $s;
        $this->db->prepare("INSERT INTO surveys (class_id, title) VALUES (:cid, 'Khảo sát giáo viên')")->execute(['cid' => $classId]);
        return ['id' => $this->db->lastInsertId(), 'class_id' => $classId, 'title' => 'Khảo sát giáo viên'];
    }

    public function submitSurvey($surveyId, $studentId, $teacherId, $rating, $comment) {
        $exist = $this->db->prepare("SELECT id FROM survey_responses WHERE survey_id=:sv AND student_id=:sid");
        $exist->execute(['sv' => $surveyId, 'sid' => $studentId]);
        if ($exist->fetch()) return ['error' => 'Bạn đã khảo sát rồi'];
        $ok = $this->db->prepare("INSERT INTO survey_responses (survey_id, student_id, teacher_id, rating, comment) VALUES (:sv,:sid,:tid,:rt,:cm)")
            ->execute(['sv' => $surveyId, 'sid' => $studentId, 'tid' => $teacherId, 'rt' => $rating, 'cm' => $comment]);
        return $ok ? ['success' => true] : ['error' => 'Gửi khảo sát thất bại'];
    }

    public function getSurveyableClasses($studentId) {
        $stmt = $this->db->prepare("
            SELECT c.id, c.class_code, co.course_name, c.end_date, c.status,
                   ta.teacher_id, u.full_name AS teacher_name,
                   (SELECT sr.id FROM survey_responses sr JOIN surveys sv ON sr.survey_id=sv.id WHERE sv.class_id=c.id AND sr.student_id=:sid2 LIMIT 1) AS responded
            FROM enrollments e
            JOIN classes c ON e.class_id=c.id
            JOIN courses co ON c.course_id=co.id
            LEFT JOIN teacher_assignments ta ON ta.class_id=c.id AND ta.student_id=:sid3
            LEFT JOIN teachers t ON ta.teacher_id=t.id
            LEFT JOIN users u ON t.user_id=u.id
            WHERE e.student_id=:sid AND e.payment_status='PAID'
              AND (c.status='COMPLETED' OR c.end_date <= CURDATE())
        ");
        $stmt->execute(['sid' => $studentId, 'sid2' => $studentId, 'sid3' => $studentId]);
        return $stmt->fetchAll();
    }

    // --- ATTENDANCE (teacher classes) ---
    public function getTeacherClasses($teacherId) {
        $stmt = $this->db->prepare("
            SELECT DISTINCT c.id, c.class_code, co.course_name
            FROM teacher_assignments ta
            JOIN classes c ON ta.class_id=c.id
            JOIN courses co ON c.course_id=co.id
            WHERE ta.teacher_id=:tid AND ta.assignment_status='CONFIRMED'
        ");
        $stmt->execute(['tid' => $teacherId]);
        return $stmt->fetchAll();
    }

    public function getStudentsInClassForTeacher($classId, $teacherId) {
        $stmt = $this->db->prepare("
            SELECT s.id, s.student_code, u.full_name
            FROM enrollments e
            JOIN students s ON e.student_id=s.id
            JOIN users u ON s.user_id=u.id
            JOIN teacher_assignments ta ON ta.class_id=e.class_id AND ta.student_id=s.id AND ta.teacher_id=:tid
            WHERE e.class_id=:cid AND e.payment_status='PAID' AND e.status='ACTIVE'
        ");
        $stmt->execute(['cid' => $classId, 'tid' => $teacherId]);
        return $stmt->fetchAll();
    }

    public function getAttendanceReport($classId = null, $date = null) {
        $sql = "SELECT a.*, s.student_code, u.full_name, c.class_code,
                t.teacher_code, tu.full_name AS teacher_name
            FROM attendance a
            JOIN students s ON a.student_id=s.id
            JOIN users u ON s.user_id=u.id
            JOIN classes c ON a.class_id=c.id
            LEFT JOIN teacher_assignments ta ON ta.class_id=c.id AND ta.student_id=s.id
            LEFT JOIN teachers t ON ta.teacher_id=t.id
            LEFT JOIN users tu ON t.user_id=tu.id
            WHERE 1=1";
        $params = [];
        if ($classId) { $sql .= " AND a.class_id=:cid"; $params['cid'] = $classId; }
        if ($date) { $sql .= " AND a.attendance_date=:dt"; $params['dt'] = $date; }
        $sql .= " ORDER BY a.attendance_date DESC, c.class_code";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getStudentAttendanceStatus($studentId, $classId = null) {
        $sql = "SELECT a.*, c.class_code FROM attendance a JOIN classes c ON a.class_id=c.id WHERE a.student_id=:sid";
        $params = ['sid' => $studentId];
        if ($classId) { $sql .= " AND a.class_id=:cid"; $params['cid'] = $classId; }
        $sql .= " ORDER BY a.attendance_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getTeacherStats() {
        return $this->db->query("
            SELECT t.teacher_code, u.full_name,
                (SELECT COUNT(*) FROM teacher_assignments ta WHERE ta.teacher_id=t.id) AS assignments,
                (SELECT COUNT(*) FROM attendance a JOIN teacher_assignments ta ON a.class_id=ta.class_id AND a.student_id=ta.student_id WHERE ta.teacher_id=t.id AND a.tinh_luong=1) AS paid_sessions,
                (SELECT AVG(sr.rating) FROM survey_responses sr WHERE sr.teacher_id=t.id) AS avg_survey
            FROM teachers t JOIN users u ON t.user_id=u.id WHERE t.status='ACTIVE'
        ")->fetchAll();
    }

    public function getStudentStats() {
        return $this->db->query("
            SELECT s.student_code, u.full_name,
                (SELECT COUNT(*) FROM enrollments e WHERE e.student_id=s.id AND e.payment_status='PAID') AS classes_joined,
                (SELECT AVG(ev.avg_score) FROM student_evaluations ev WHERE ev.student_id=s.id) AS avg_score,
                (SELECT COUNT(*) FROM attendance a WHERE a.student_id=s.id AND a.attendance_status='PRESENT') AS present_count
            FROM students s JOIN users u ON s.user_id=u.id WHERE s.status='ACTIVE'
        ")->fetchAll();
    }

    public function getAuditLogs(array $filters = [], int $limit = 200) {
        $sql = "
            SELECT al.*, u.full_name, u.email
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['action'])) {
            $sql .= ' AND al.action = :action';
            $params['action'] = $filters['action'];
        }
        if (!empty($filters['entity_name'])) {
            $sql .= ' AND al.entity_name = :entity_name';
            $params['entity_name'] = $filters['entity_name'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= ' AND DATE(al.created_at) >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= ' AND DATE(al.created_at) <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }

        $sql .= ' ORDER BY al.id DESC LIMIT :lim';
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAuditLogFilterOptions(): array {
        $actions = $this->db->query("
            SELECT DISTINCT action FROM audit_logs
            WHERE action IS NOT NULL AND action != ''
            ORDER BY action
        ")->fetchAll(PDO::FETCH_COLUMN);

        $entities = $this->db->query("
            SELECT DISTINCT entity_name FROM audit_logs
            WHERE entity_name IS NOT NULL AND entity_name != ''
            ORDER BY entity_name
        ")->fetchAll(PDO::FETCH_COLUMN);

        return ['actions' => $actions, 'entities' => $entities];
    }
}
