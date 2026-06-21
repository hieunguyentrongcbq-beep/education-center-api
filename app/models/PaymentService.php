<?php
namespace App\Models;

use Core\Database;
use PDO;

class PaymentService {
    private $db;
    private $scheduleService;
    private $instructorModel;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->scheduleService = new ScheduleService();
        $this->instructorModel = new instructormodel();
    }

    public function listPending(int $limit = 100): array {
        $stmt = $this->db->prepare("
            SELECT e.id AS enrollment_id,
                   s.id AS student_id, s.student_code, u.full_name, u.email,
                   c.id AS class_id, c.class_code, c.max_students, c.status AS class_status,
                   co.course_name, co.tuition_fee,
                   tp.id AS payment_id,
                   e.enrollment_date, e.payment_status, e.status AS enrollment_status
            FROM enrollments e
            JOIN students s ON e.student_id = s.id
            JOIN users u ON s.user_id = u.id
            JOIN classes c ON e.class_id = c.id
            JOIN courses co ON c.course_id = co.id
            LEFT JOIN tuition_payments tp ON tp.student_id = e.student_id
                AND tp.class_id = e.class_id AND tp.payment_status = 'UNPAID'
            WHERE e.payment_status = 'UNPAID'
              AND e.status IN ('ACTIVE')
              AND s.status = 'ACTIVE'
              AND c.status IN ('UPCOMING', 'ONGOING')
            ORDER BY e.enrollment_date DESC, e.id DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $conflicts = $this->scheduleService->checkStudentScheduleConflict(
                (int)$row['student_id'],
                (int)$row['class_id']
            );
            $row['has_schedule_conflict'] = !empty($conflicts);
            $row['schedule_conflict_message'] = $conflicts
                ? ScheduleService::conflictClassMessage($conflicts, 'học')
                : '';
            $cnt = $this->db->prepare("
                SELECT COUNT(*) FROM enrollments
                WHERE class_id=:cid AND payment_status='PAID' AND status='ACTIVE'
            ");
            $cnt->execute(['cid' => $row['class_id']]);
            $row['paid_count'] = (int)$cnt->fetchColumn();
            $row['seats_left'] = max(0, (int)$row['max_students'] - $row['paid_count']);
        }
        unset($row);

        return $rows;
    }

    public function countPending(): int {
        $stmt = $this->db->query("
            SELECT COUNT(*) FROM enrollments e
            JOIN students s ON e.student_id = s.id
            JOIN classes c ON e.class_id = c.id
            WHERE e.payment_status = 'UNPAID' AND e.status = 'ACTIVE'
              AND s.status = 'ACTIVE' AND c.status IN ('UPCOMING', 'ONGOING')
        ");
        return (int)$stmt->fetchColumn();
    }

    public function listCompleted(int $limit = 50): array {
        $stmt = $this->db->prepare("
            SELECT tp.*, s.student_code, u.full_name, c.class_code, co.course_name,
                   e.id AS enrollment_id
            FROM tuition_payments tp
            JOIN students s ON tp.student_id = s.id
            JOIN users u ON s.user_id = u.id
            LEFT JOIN classes c ON tp.class_id = c.id
            LEFT JOIN courses co ON c.course_id = co.id
            LEFT JOIN enrollments e ON e.student_id = tp.student_id AND e.class_id = tp.class_id
            WHERE tp.payment_status IN ('COMPLETED', 'REFUNDED')
            ORDER BY tp.id DESC LIMIT :lim
        ");
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getPaymentById(int $paymentId): ?array {
        $stmt = $this->db->prepare("
            SELECT tp.*, s.student_code, u.full_name, u.id AS user_id,
                   c.class_code, co.course_name,
                   e.id AS enrollment_id, e.payment_status AS enrollment_payment_status
            FROM tuition_payments tp
            JOIN students s ON tp.student_id = s.id
            JOIN users u ON s.user_id = u.id
            LEFT JOIN classes c ON tp.class_id = c.id
            LEFT JOIN courses co ON c.course_id = co.id
            LEFT JOIN enrollments e ON e.student_id = tp.student_id AND e.class_id = tp.class_id
            WHERE tp.id = :id
        ");
        $stmt->execute(['id' => $paymentId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function updatePayment(int $paymentId, array $data): array {
        $payment = $this->getPaymentById($paymentId);
        if (!$payment) {
            return ['error' => 'Không tìm thấy thanh toán'];
        }
        if ($payment['payment_status'] !== 'COMPLETED') {
            return ['error' => 'Chỉ sửa được thanh toán đã hoàn tất'];
        }

        $amount = isset($data['amount']) ? (float)$data['amount'] : (float)$payment['amount'];
        $method = trim($data['payment_method'] ?? $payment['payment_method'] ?? 'CASH');
        $date = $data['payment_date'] ?? $payment['payment_date'];

        if ($amount <= 0) {
            return ['error' => 'Số tiền phải lớn hơn 0'];
        }
        if ($amount > 999999999.99) {
            return ['error' => 'Số tiền vượt giới hạn cho phép'];
        }

        $allowedMethods = ['CASH', 'BANK_TRANSFER', 'CARD'];
        if (!in_array($method, $allowedMethods, true)) {
            return ['error' => 'Phương thức thanh toán không hợp lệ'];
        }
        if ($date && !strtotime((string)$date)) {
            return ['error' => 'Ngày thanh toán không hợp lệ'];
        }

        $ok = $this->db->prepare("
            UPDATE tuition_payments
            SET amount = :amount, payment_method = :method, payment_date = :pdate
            WHERE id = :id
        ")->execute([
            'amount' => $amount,
            'method' => $method,
            'pdate'  => $date ?: null,
            'id'     => $paymentId,
        ]);

        return $ok ? ['success' => true] : ['error' => 'Không thể cập nhật thanh toán'];
    }

    public function refundPayment(int $paymentId, int $adminUserId, string $reason = ''): array {
        $payment = $this->getPaymentById($paymentId);
        if (!$payment) {
            return ['error' => 'Không tìm thấy thanh toán'];
        }
        if ($payment['payment_status'] !== 'COMPLETED') {
            return ['error' => 'Chỉ hoàn tiền được thanh toán đã hoàn tất'];
        }

        $studentId = (int)$payment['student_id'];
        $classId = (int)($payment['class_id'] ?? 0);

        $this->db->beginTransaction();
        try {
            $this->db->prepare("
                UPDATE tuition_payments SET payment_status = 'REFUNDED' WHERE id = :id
            ")->execute(['id' => $paymentId]);

            if ($classId && $studentId) {
                $this->db->prepare("
                    UPDATE enrollments SET payment_status = 'REFUNDED'
                    WHERE student_id = :sid AND class_id = :cid
                ")->execute(['sid' => $studentId, 'cid' => $classId]);
            }

            if (!empty($payment['user_id'])) {
                $classCode = $payment['class_code'] ?? 'lớp học';
                $content = "Thanh toán lớp {$classCode} đã được hoàn tiền.";
                if ($reason !== '') {
                    $content .= ' Lý do: ' . $reason;
                }
                $this->instructorModel->createNotification(
                    (int)$payment['user_id'],
                    'Hoàn tiền học phí',
                    $content
                );
            }

            AuditLog::write($adminUserId, 'REFUND_PAYMENT', 'tuition_payments', $paymentId);

            $this->db->commit();
            return ['success' => true];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['error' => 'Lỗi hoàn tiền: ' . $e->getMessage()];
        }
    }

    public function confirmPayment(array $data, int $adminUserId): array {
        $studentId = (int)($data['student_id'] ?? 0);
        $classId = (int)($data['class_id'] ?? 0);
        $method = trim($data['payment_method'] ?? 'CASH');
        if (!$studentId || !$classId) {
            return ['error' => 'Thiếu học viên hoặc lớp'];
        }
        if (!in_array($method, ['CASH', 'BANK_TRANSFER', 'CARD'], true)) {
            return ['error' => 'Phương thức thanh toán không hợp lệ'];
        }

        $stuCheck = $this->db->prepare('SELECT id FROM students WHERE id = :id');
        $stuCheck->execute(['id' => $studentId]);
        if (!$stuCheck->fetch()) {
            return ['error' => 'Học viên không tồn tại'];
        }

        $classStmt = $this->db->prepare("
            SELECT c.*, co.tuition_fee, co.day_primary, co.day_secondary,
                   (SELECT COUNT(*) FROM enrollments WHERE class_id=c.id AND status='ACTIVE' AND payment_status='PAID') AS enrolled
            FROM classes c JOIN courses co ON c.course_id = co.id WHERE c.id=:id
        ");
        $classStmt->execute(['id' => $classId]);
        $class = $classStmt->fetch();
        if (!$class) return ['error' => 'Lớp không tồn tại'];
        if ($class['enrolled'] >= $class['max_students']) return ['error' => 'Lớp đã đầy'];

        $teacherId = !empty($data['teacher_id']) ? (int)$data['teacher_id'] : null;
        if (!$teacherId && !empty($class['teacher_id'])) {
            $teacherId = (int)$class['teacher_id'];
        }

        $existPaid = $this->db->prepare("
            SELECT id FROM enrollments WHERE student_id=:sid AND class_id=:cid AND payment_status='PAID'
        ");
        $existPaid->execute(['sid' => $studentId, 'cid' => $classId]);
        if ($existPaid->fetch()) return ['error' => 'Học viên đã thanh toán lớp này'];

        $conflicts = $this->scheduleService->checkStudentScheduleConflict($studentId, $classId);
        if ($conflicts) {
            return [
                'error' => ScheduleService::conflictClassMessage($conflicts, 'học'),
                'details' => $conflicts,
            ];
        }

        $this->db->beginTransaction();
        try {
            $amount = $class['tuition_fee'];

            $tpCheck = $this->db->prepare("SELECT id FROM tuition_payments WHERE student_id=:sid AND class_id=:cid LIMIT 1");
            $tpCheck->execute(['sid' => $studentId, 'cid' => $classId]);
            $tpRow = $tpCheck->fetch();
            if ($tpRow) {
                $this->db->prepare("
                    UPDATE tuition_payments SET payment_status='COMPLETED', payment_method=:m, payment_date=CURDATE(), amount=:a
                    WHERE id=:id
                ")->execute(['m' => $method, 'a' => $amount, 'id' => $tpRow['id']]);
            } else {
                $this->db->prepare("
                    INSERT INTO tuition_payments (student_id, class_id, amount, payment_date, payment_method, payment_status)
                    VALUES (:sid, :cid, :a, CURDATE(), :m, 'COMPLETED')
                ")->execute(['sid' => $studentId, 'cid' => $classId, 'a' => $amount, 'm' => $method]);
            }

            $enCheck = $this->db->prepare("SELECT id FROM enrollments WHERE student_id=:sid AND class_id=:cid");
            $enCheck->execute(['sid' => $studentId, 'cid' => $classId]);
            if ($enRow = $enCheck->fetch()) {
                $this->db->prepare("UPDATE enrollments SET payment_status='PAID', status='ACTIVE' WHERE id=:id")
                    ->execute(['id' => $enRow['id']]);
            } else {
                $this->db->prepare("
                    INSERT INTO enrollments (student_id, class_id, enrollment_date, payment_status, status)
                    VALUES (:sid, :cid, CURDATE(), 'PAID', 'ACTIVE')
                ")->execute(['sid' => $studentId, 'cid' => $classId]);
            }

            $schedResult = $this->scheduleService->generateRegularSchedule($studentId, $classId);
            if (isset($schedResult['error'])) {
                $this->db->rollBack();
                return $schedResult;
            }

            if ($teacherId) {
                $assignResult = $this->assignTeacherForClassDays(
                    $teacherId, $classId, $studentId, $class, $adminUserId
                );
                if (isset($assignResult['error'])) {
                    $this->db->rollBack();
                    return $assignResult;
                }
            }

            $stuUser = $this->db->prepare("SELECT u.id, u.full_name FROM students s JOIN users u ON s.user_id=u.id WHERE s.id=:id");
            $stuUser->execute(['id' => $studentId]);
            $stu = $stuUser->fetch();
            if ($stu) {
                $this->instructorModel->createNotification(
                    $stu['id'],
                    'Thanh toán thành công',
                    "Bạn đã được xếp vào lớp {$class['class_code']}. Lịch học đã được tạo."
                );
            }

            if ($teacherId) {
                $tvUser = $this->db->prepare("SELECT u.id FROM teachers t JOIN users u ON t.user_id=u.id WHERE t.id=:id");
                $tvUser->execute(['id' => $teacherId]);
                if ($tv = $tvUser->fetch()) {
                    $this->instructorModel->createNotification(
                        $tv['id'],
                        'Phân công dạy mới',
                        "Bạn được phân công dạy học viên tại lớp {$class['class_code']}."
                    );
                }
            }

            $this->db->commit();
            return [
                'success' => true,
                'end_date' => $schedResult['end_date'] ?? null,
                'teacher_assigned' => (bool)$teacherId,
            ];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['error' => 'Lỗi xử lý: ' . $e->getMessage()];
        }
    }

    /**
     * Phân công GV cho HV theo tất cả thứ học của khóa (day_primary + day_secondary).
     */
    private function assignTeacherForClassDays(
        int $teacherId,
        int $classId,
        int $studentId,
        array $class,
        int $adminUserId
    ): array {
        $days = [];
        foreach (['day_primary', 'day_secondary'] as $key) {
            if (!isset($class[$key]) || $class[$key] === '' || $class[$key] === null) {
                continue;
            }
            $dow = (int)$class[$key];
            if ($dow >= 0 && $dow <= 6) {
                $days[$dow] = $dow;
            }
        }
        if (!$days) {
            $days = [(int)($class['day_primary'] ?? 1)];
        }

        $assigning = new assigning();
        foreach ($days as $dow) {
            $result = $assigning->assignTeacher([
                'teacher_id'  => $teacherId,
                'class_id'    => $classId,
                'student_id'  => $studentId,
                'day_of_week' => $dow,
            ], $adminUserId);
            if (isset($result['error'])) {
                return $result;
            }
        }

        return ['success' => true, 'days' => array_values($days)];
    }
}
