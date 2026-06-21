<?php
namespace App\Models;

use Core\Database;
use PDO;

class instructormodel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // --- TEACHER LOGIC ---
    public function getAllTeachers($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $stmt = $this->db->prepare("
            SELECT t.*, u.full_name, u.email, u.phone 
            FROM teachers t
            JOIN users u ON t.user_id = u.id
            ORDER BY t.id DESC 
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findTeacherByCode($teacherCode) {
        $stmt = $this->db->prepare("SELECT * FROM teachers WHERE teacher_code = :teacher_code");
        $stmt->execute(['teacher_code' => $teacherCode]);
        return $stmt->fetch();
    }

    public function getTeacherById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT t.*, u.full_name, u.email, u.phone
            FROM teachers t
            JOIN users u ON t.user_id = u.id
            WHERE t.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getTeacherIdByUserId(int $userId): ?int {
        $stmt = $this->db->prepare("SELECT id FROM teachers WHERE user_id = :uid LIMIT 1");
        $stmt->execute(['uid' => $userId]);
        $row = $stmt->fetch();
        return $row ? (int)$row['id'] : null;
    }

    private function isValidDateString($date) {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    private function validatePhoneField($value, $label) {
        $value = trim((string)$value);
        if ($value === '') {
            return null;
        }
        $normalized = preg_replace('/[\s\-]/', '', $value);
        if (!preg_match('/^(\+?84|0)[0-9]{8,10}$/', $normalized)) {
            return ['error' => $label . ' không hợp lệ (VD: 0912345678)'];
        }
        return null;
    }

    /**
     * @return array|null Lỗi ['error' => '...'] hoặc null nếu hợp lệ
     */
    private function validateTeacherData($data, $forUpdate = false, $teacherId = null) {
        $name = trim($data['full_name'] ?? '');
        if ($name === '') {
            return ['error' => 'Họ tên là bắt buộc'];
        }
        if (mb_strlen($name) < 2) {
            return ['error' => 'Họ tên tối thiểu 2 ký tự'];
        }
        if (mb_strlen($name) > 200) {
            return ['error' => 'Họ tên tối đa 200 ký tự'];
        }

        $email = trim($data['email'] ?? '');
        if ($email === '') {
            return ['error' => 'Email là bắt buộc'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['error' => 'Email không hợp lệ'];
        }
        if (mb_strlen($email) > 255) {
            return ['error' => 'Email tối đa 255 ký tự'];
        }

        if (!$forUpdate) {
            $code = trim($data['teacher_code'] ?? '');
            if ($code === '') {
                return ['error' => 'Mã giáo viên là bắt buộc'];
            }
            if (!preg_match('/^[A-Za-z0-9_-]{2,30}$/', $code)) {
                return ['error' => 'Mã giáo viên chỉ gồm chữ, số, gạch ngang (2–30 ký tự)'];
            }
        }

        $password = trim($data['password'] ?? '');
        if ($password !== '' && strlen($password) < 6) {
            return ['error' => 'Mật khẩu tối thiểu 6 ký tự'];
        }

        $spec = trim($data['specialization'] ?? '');
        if ($spec === '') {
            return ['error' => 'Chuyên môn là bắt buộc'];
        }
        if (mb_strlen($spec) < 2) {
            return ['error' => 'Chuyên môn tối thiểu 2 ký tự'];
        }
        if (mb_strlen($spec) > 200) {
            return ['error' => 'Chuyên môn tối đa 200 ký tự'];
        }

        $phoneErr = $this->validatePhoneField($data['phone'] ?? '', 'Số điện thoại');
        if ($phoneErr) return $phoneErr;

        $hireDate = trim($data['hire_date'] ?? '');
        if ($hireDate !== '') {
            if (!$this->isValidDateString($hireDate)) {
                return ['error' => 'Ngày vào không hợp lệ'];
            }
            if (strtotime($hireDate) > strtotime('today')) {
                return ['error' => 'Ngày vào không thể ở tương lai'];
            }
        }

        $teacherType = $data['teacher_type'] ?? 'FULL_TIME';
        if (!in_array($teacherType, ['FULL_TIME', 'VISITING'], true)) {
            return ['error' => 'Loại giáo viên không hợp lệ'];
        }

        $standardHoursRaw = $data['standard_hours'] ?? '';
        if ($teacherType === 'FULL_TIME') {
            if ($standardHoursRaw === '' || !is_numeric($standardHoursRaw)) {
                return ['error' => 'Giờ chuẩn/tháng là bắt buộc với GV cơ hữu'];
            }
            $standardHours = (float)$standardHoursRaw;
            if ($standardHours <= 0 || $standardHours > 200) {
                return ['error' => 'Giờ chuẩn/tháng phải từ 1 đến 200'];
            }
        }

        $status = $data['status'] ?? 'ACTIVE';
        if (!in_array($status, ['ACTIVE', 'INACTIVE'], true)) {
            return ['error' => 'Trạng thái không hợp lệ'];
        }

        if ($forUpdate && $teacherId && $status === 'INACTIVE') {
            $assignStmt = $this->db->prepare("
                SELECT COUNT(*) FROM teacher_assignments
                WHERE teacher_id=:tid AND assignment_status='CONFIRMED'
            ");
            $assignStmt->execute(['tid' => $teacherId]);
            if ((int)$assignStmt->fetchColumn() > 0) {
                return ['error' => 'Không thể chuyển INACTIVE: giáo viên còn phân công đang hoạt động'];
            }
            $classStmt = $this->db->prepare("SELECT COUNT(*) FROM classes WHERE teacher_id=:tid");
            $classStmt->execute(['tid' => $teacherId]);
            if ((int)$classStmt->fetchColumn() > 0) {
                return ['error' => 'Không thể chuyển INACTIVE: giáo viên đang được gán trực tiếp vào lớp'];
            }
        }

        return null;
    }

    public function createTeacher($data) {
        $validationError = $this->validateTeacherData($data, false);
        if ($validationError) {
            return $validationError;
        }

        $teacherCode = trim($data['teacher_code']);
        $email = trim($data['email']);

        if ($this->findTeacherByCode($teacherCode)) {
            return ['error' => 'Mã giáo viên đã tồn tại'];
        }

        $chkEmail = $this->db->prepare("SELECT id FROM users WHERE email=:email");
        $chkEmail->execute(['email' => $email]);
        if ($chkEmail->fetch()) {
            return ['error' => 'Email đã tồn tại'];
        }

        $this->db->beginTransaction();
        try {
            $rawPassword = trim($data['password'] ?? '');
            $password = password_hash($rawPassword !== '' ? $rawPassword : 'giaovien123', PASSWORD_BCRYPT);
            $phone = trim($data['phone'] ?? '');
            $hireDate = trim($data['hire_date'] ?? '');

            $userStmt = $this->db->prepare("
                INSERT INTO users (full_name, email, password_hash, phone, status)
                VALUES (:full_name, :email, :pass, :phone, 'ACTIVE')
            ");
            $userStmt->execute([
                'full_name' => trim($data['full_name']),
                'email'     => $email,
                'pass'      => $password,
                'phone'     => $phone !== '' ? $phone : null,
            ]);
            $userId = $this->db->lastInsertId();

            // Assign teacher role
            $roleStmt = $this->db->prepare("SELECT id FROM roles WHERE role_name='TEACHER' LIMIT 1");
            $roleStmt->execute();
            $role = $roleStmt->fetch();
            if ($role) {
                $this->db->prepare("INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (:uid, :rid)")
                    ->execute(['uid' => $userId, 'rid' => $role['id']]);
            }

            $teacherType = $data['teacher_type'] ?? 'FULL_TIME';
            $standardHours = $teacherType === 'FULL_TIME'
                ? (float)($data['standard_hours'] ?? 40)
                : 0;

            $teacherStmt = $this->db->prepare("
                INSERT INTO teachers (user_id, teacher_code, specialization, hire_date, teacher_type, standard_hours, status)
                VALUES (:user_id, :teacher_code, :specialization, :hire_date, :teacher_type, :standard_hours, :status)
            ");
            $teacherStmt->execute([
                'user_id'        => $userId,
                'teacher_code'   => $teacherCode,
                'specialization' => trim($data['specialization']),
                'hire_date'      => $hireDate !== '' ? $hireDate : date('Y-m-d'),
                'teacher_type'   => $teacherType,
                'standard_hours' => $standardHours,
                'status'         => $data['status'] ?? 'ACTIVE',
            ]);

            $this->db->commit();
            return ['success' => true, 'id' => $this->db->lastInsertId()];
        } catch (\PDOException $e) {
            $this->db->rollBack();
            return ['error' => 'Lỗi tạo giáo viên: ' . $e->getMessage()];
        }
    }

    public function updateTeacher($id, $data) {
        $stmt = $this->db->prepare("SELECT t.id, t.user_id FROM teachers t WHERE t.id = :id");
        $stmt->execute(['id' => $id]);
        $teacher = $stmt->fetch();
        if (!$teacher) {
            return ['error' => 'Không tìm thấy giáo viên'];
        }

        $validationError = $this->validateTeacherData($data, true, (int)$id);
        if ($validationError) {
            return $validationError;
        }

        $email = trim($data['email']);
        $chkEmail = $this->db->prepare("SELECT id FROM users WHERE email=:email AND id != :uid");
        $chkEmail->execute(['email' => $email, 'uid' => $teacher['user_id']]);
        if ($chkEmail->fetch()) {
            return ['error' => 'Email đã được sử dụng bởi tài khoản khác'];
        }

        $phone = trim($data['phone'] ?? '');
        $hireDate = trim($data['hire_date'] ?? '');
        $newPassword = trim($data['password'] ?? '');
        $userStatus = ($data['status'] ?? 'ACTIVE') === 'ACTIVE' ? 'ACTIVE' : 'INACTIVE';

        try {
            $this->db->beginTransaction();

            $userSql = "
                UPDATE users SET full_name=:full_name, email=:email, phone=:phone, status=:ustatus
            ";
            $userParams = [
                'full_name' => trim($data['full_name']),
                'email'     => $email,
                'phone'     => $phone !== '' ? $phone : null,
                'ustatus'   => $userStatus,
                'uid'       => $teacher['user_id'],
            ];
            if ($newPassword !== '') {
                $userSql .= ", password_hash=:pass";
                $userParams['pass'] = password_hash($newPassword, PASSWORD_BCRYPT);
            }
            $userSql .= " WHERE id=:uid";
            $this->db->prepare($userSql)->execute($userParams);

            $teacherType = $data['teacher_type'] ?? 'FULL_TIME';
            $standardHours = $teacherType === 'FULL_TIME'
                ? (float)($data['standard_hours'] ?? 40)
                : 0;

            $this->db->prepare("
                UPDATE teachers SET specialization=:specialization, hire_date=:hire_date,
                    teacher_type=:teacher_type, standard_hours=:standard_hours, status=:status
                WHERE id=:id
            ")->execute([
                'specialization' => trim($data['specialization']),
                'hire_date'      => $hireDate !== '' ? $hireDate : null,
                'teacher_type'   => $teacherType,
                'standard_hours' => $standardHours,
                'status'         => $data['status'] ?? 'ACTIVE',
                'id'             => $id,
            ]);

            $this->db->commit();
            return ['success' => true];
        } catch (\PDOException $e) {
            $this->db->rollBack();
            return ['error' => 'Không thể cập nhật giáo viên'];
        }
    }

    public function deleteTeacher($id) {
        $stmt = $this->db->prepare("SELECT id FROM teachers WHERE id=:id");
        $stmt->execute(['id' => $id]);
        if (!$stmt->fetch()) return ['error' => 'Không tìm thấy giáo viên'];

        // Check if teacher is assigned to any class
        $stmt2 = $this->db->prepare("SELECT COUNT(*) FROM classes WHERE teacher_id=:id");
        $stmt2->execute(['id' => $id]);
        if ($stmt2->fetchColumn() > 0)
            return ['error' => 'Không thể xóa: giáo viên đang được gán vào lớp học. Hãy gỡ khỏi tất cả các lớp trước.'];

        // Check if teacher has any assignments
        $stmt3 = $this->db->prepare("SELECT COUNT(*) FROM teacher_assignments WHERE teacher_id=:id");
        $stmt3->execute(['id' => $id]);
        if ($stmt3->fetchColumn() > 0)
            return ['error' => 'Không thể xóa: giáo viên còn phân công chưa xóa. Hãy xóa các phân công trước.'];

        try {
            $ok = $this->db->prepare("DELETE FROM teachers WHERE id=:id")->execute(['id' => $id]);
            return $ok ? ['success' => true] : ['error' => 'Xóa thất bại'];
        } catch (\PDOException $e) {
            return ['error' => 'Không thể xóa do ràng buộc dữ liệu: ' . $e->getMessage()];
        }
    }

    // --- NOTIFICATION LOGIC ---
    public function getMyNotifications($userId) {
        $stmt = $this->db->prepare("SELECT * FROM notifications WHERE receiver_id = :user_id ORDER BY created_at DESC LIMIT 50");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function countUnreadNotifications($userId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM notifications WHERE receiver_id = :user_id AND is_read = 0");
        $stmt->execute(['user_id' => (int)$userId]);
        return (int)$stmt->fetchColumn();
    }

    public function markAllNotificationsAsRead($userId) {
        $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE receiver_id = :uid AND is_read = 0");
        return $stmt->execute(['uid' => (int)$userId]);
    }

    private function scheduleTypeLabel(string $type): string {
        $map = [
            'REGULAR' => 'Học thường',
            'EXAM'    => 'Thi',
            'MAKEUP'  => 'Học bù',
            'EXTRA'   => 'Bổ sung',
        ];
        return $map[$type] ?? $type;
    }

    /** @return array{title: string, content: string, student_id: ?int} */
    private function buildScheduleNotifyPayload(int $classId, array $data, string $action): array {
        $classStmt = $this->db->prepare('SELECT class_code FROM classes WHERE id = :id');
        $classStmt->execute(['id' => $classId]);
        $classCode = $classStmt->fetch()['class_code'] ?? 'lớp';

        $type = $data['schedule_type'] ?? 'REGULAR';
        $typeLabel = $this->scheduleTypeLabel($type);
        $days = ['Chủ nhật', 'Thứ hai', 'Thứ ba', 'Thứ tư', 'Thứ năm', 'Thứ sáu', 'Thứ bảy'];
        $dow = (int)($data['day_of_week'] ?? 0);
        $dayLabel = $days[$dow] ?? '';
        $timeLabel = substr($data['start_time'] ?? '', 0, 5) . '-' . substr($data['end_time'] ?? '', 0, 5);

        $detail = "Lớp {$classCode}";
        if (!empty($data['specific_date']) && $type !== 'REGULAR') {
            $detail .= ', ngày ' . date('d/m/Y', strtotime($data['specific_date']));
        } elseif ($dayLabel) {
            $detail .= ", {$dayLabel}";
        }
        $detail .= ", {$timeLabel} ({$typeLabel})";

        $studentId = !empty($data['student_id']) ? (int)$data['student_id'] : null;
        if ($studentId) {
            $nameStmt = $this->db->prepare("
                SELECT u.full_name FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = :id
            ");
            $nameStmt->execute(['id' => $studentId]);
            $studentName = $nameStmt->fetchColumn();
            if ($studentName) {
                $detail .= " — HV: {$studentName}";
            }
        }

        if (!empty($data['exam_label'])) {
            $detail .= ' — ' . $data['exam_label'];
        }

        $titles = [
            'add'    => "Lịch {$typeLabel} mới",
            'update' => "Lịch {$typeLabel} đã cập nhật",
            'delete' => "Lịch {$typeLabel} đã hủy",
        ];
        $verbs = [
            'add'    => 'thêm',
            'update' => 'cập nhật',
            'delete' => 'hủy',
        ];

        return [
            'title'      => $titles[$action] ?? "Lịch {$typeLabel}",
            'content'    => 'Admin đã ' . ($verbs[$action] ?? $action) . ' lịch: ' . $detail . '.',
            'student_id' => $studentId,
        ];
    }

    public function notifyStudentById(int $studentId, string $title, string $content): void {
        $stmt = $this->db->prepare("
            SELECT u.id FROM students s JOIN users u ON s.user_id = u.id WHERE s.id = :id
        ");
        $stmt->execute(['id' => $studentId]);
        if ($row = $stmt->fetch()) {
            $this->createNotification((int)$row['id'], $title, $content);
        }
    }

    /**
     * Thông báo GV: phân công CONFIRMED, GV mặc định lớp, và GV phân công HV (nếu có).
     */
    public function notifyTeachersOfClass($classId, $title, $content, $studentId = null) {
        $receiverIds = [];

        $assignStmt = $this->db->prepare("
            SELECT DISTINCT u.id FROM teacher_assignments ta
            JOIN teachers t ON ta.teacher_id = t.id
            JOIN users u ON t.user_id = u.id
            WHERE ta.class_id = :cid AND ta.assignment_status = 'CONFIRMED'
        ");
        $assignStmt->execute(['cid' => (int)$classId]);
        foreach ($assignStmt->fetchAll() as $row) {
            $receiverIds[(int)$row['id']] = true;
        }

        if ($studentId) {
            $studentTeacherStmt = $this->db->prepare("
                SELECT DISTINCT u.id FROM teacher_assignments ta
                JOIN teachers t ON ta.teacher_id = t.id
                JOIN users u ON t.user_id = u.id
                WHERE ta.class_id = :cid AND ta.student_id = :sid AND ta.assignment_status = 'CONFIRMED'
            ");
            $studentTeacherStmt->execute(['cid' => (int)$classId, 'sid' => (int)$studentId]);
            foreach ($studentTeacherStmt->fetchAll() as $row) {
                $receiverIds[(int)$row['id']] = true;
            }
        }

        $defaultStmt = $this->db->prepare("
            SELECT u.id FROM classes c
            JOIN teachers t ON c.teacher_id = t.id
            JOIN users u ON t.user_id = u.id
            WHERE c.id = :cid AND c.teacher_id IS NOT NULL
        ");
        $defaultStmt->execute(['cid' => (int)$classId]);
        if ($row = $defaultStmt->fetch()) {
            $receiverIds[(int)$row['id']] = true;
        }

        foreach (array_keys($receiverIds) as $userId) {
            $this->createNotification($userId, $title, $content);
        }
    }

    public function notifyClassScheduleChange(int $classId, array $data, bool $updated = false): void {
        $payload = $this->buildScheduleNotifyPayload($classId, $data, $updated ? 'update' : 'add');
        $studentId = $payload['student_id'];

        if ($studentId) {
            $this->notifyStudentById($studentId, $payload['title'], $payload['content']);
        } else {
            $this->notifyPaidStudentsOfClass($classId, $payload['title'], $payload['content']);
        }
        $this->notifyTeachersOfClass($classId, $payload['title'], $payload['content'], $studentId);
    }

    public function notifyClassScheduleDeleted(int $classId, array $schedule): void {
        $payload = $this->buildScheduleNotifyPayload($classId, $schedule, 'delete');
        $studentId = $payload['student_id'];

        if ($studentId) {
            $this->notifyStudentById($studentId, $payload['title'], $payload['content']);
        } else {
            $this->notifyPaidStudentsOfClass($classId, $payload['title'], $payload['content']);
        }
        $this->notifyTeachersOfClass($classId, $payload['title'], $payload['content'], $studentId);
    }

    public function notifyPaidStudentsOfClass($classId, $title, $content) {
        $stmt = $this->db->prepare("
            SELECT u.id FROM enrollments e
            JOIN students st ON e.student_id = st.id
            JOIN users u ON st.user_id = u.id
            WHERE e.class_id = :cid AND e.payment_status = 'PAID'
        ");
        $stmt->execute(['cid' => (int)$classId]);
        foreach ($stmt->fetchAll() as $row) {
            $this->createNotification($row['id'], $title, $content);
        }
    }

    public function createNotification($receiverId, $title, $content) {
        $stmt = $this->db->prepare("INSERT INTO notifications (title, content, receiver_id) VALUES (:title, :content, :receiver_id)");
        $result = $stmt->execute(['title' => $title, 'content' => $content, 'receiver_id' => $receiverId]);
        
        // --- THƯỞNG ĐIỂM: GỬI EMAIL THÔNG BÁO ---
        if ($result) {
            $userStmt = $this->db->prepare("SELECT email FROM users WHERE id = :id");
            $userStmt->execute(['id' => $receiverId]);
            $user = $userStmt->fetch();
            
            if ($user && !empty($user['email'])) {
                $to = $user['email'];
                $subject = "Thông báo mới: " . $title;
                $headers = "From: no-reply@educationcenter.com\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                
                // Giả lập gửi mail (Trong môi trường thật cần cấu hình SMTP Server trên server)
                @mail($to, $subject, $content, $headers);
            }
        }
        return $result;
    }

    public function markNotificationAsRead($notificationId, $userId) {
        $stmt = $this->db->prepare("UPDATE notifications SET is_read = TRUE WHERE id = :id AND receiver_id = :uid");
        return $stmt->execute(['id' => $notificationId, 'uid' => $userId]);
    }

    // --- REPORTING LOGIC ---
    public function getTeacherTeachingHours($month, $scenarioName = 'FINAL') {
        $stmt = $this->db->prepare("
            SELECT t.teacher_code, u.full_name, t.teacher_type, t.standard_hours,
                   COUNT(s.id) as total_sessions,
                   COALESCE(SUM(TIME_TO_SEC(TIMEDIFF(s.end_time, s.start_time)) / 3600), 0) as actual_hours
            FROM teachers t
            JOIN users u ON t.user_id = u.id
            LEFT JOIN teacher_assignments ta ON ta.teacher_id = t.id AND ta.scenario_name = :scenario_name AND ta.assignment_status = 'CONFIRMED'
            LEFT JOIN classes c ON ta.class_id = c.id
            LEFT JOIN schedules s ON c.id = s.class_id
            GROUP BY t.id, u.full_name, t.teacher_code, t.teacher_type, t.standard_hours
        ");
        $stmt->execute(['scenario_name' => $scenarioName]);
        $results = $stmt->fetchAll();

        foreach ($results as &$row) {
            $actual = (float)$row['actual_hours'];
            $standard = (float)$row['standard_hours'];
            
            if ($row['teacher_type'] === 'VISITING') {
                $row['workload_status'] = 'N/A (Visiting)';
                $row['hours_diff'] = 0;
            } else {
                $diff = $actual - $standard;
                $row['hours_diff'] = $diff;
                if ($diff < 0) $row['workload_status'] = 'THIẾU CHUẨN';
                else if ($diff == 0) $row['workload_status'] = 'ĐẠT CHUẨN';
                else $row['workload_status'] = 'VƯỢT CHUẨN';
            }
        }
        return $results;
    }

    public function compareScenarios() {
        $stmt = $this->db->prepare("
            SELECT ta.scenario_name,
                   COUNT(DISTINCT ta.class_id) as total_assigned_classes,
                   COUNT(DISTINCT ta.teacher_id) as total_teachers_used,
                   SUM(CASE WHEN t.teacher_type = 'VISITING' THEN 1 ELSE 0 END) as visiting_teacher_assignments,
                   (SELECT COALESCE(SUM(TIME_TO_SEC(TIMEDIFF(s.end_time, s.start_time)) / 3600), 0)
                    FROM teacher_assignments ta2 
                    JOIN teachers t2 ON ta2.teacher_id = t2.id
                    JOIN classes c2 ON ta2.class_id = c2.id
                    JOIN schedules s ON c2.id = s.class_id
                    WHERE ta2.scenario_name = ta.scenario_name AND t2.teacher_type = 'VISITING'
                   ) as total_visiting_hours
            FROM teacher_assignments ta
            JOIN teachers t ON ta.teacher_id = t.id
            GROUP BY ta.scenario_name
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // --- PAYROLL LOGIC ---
    public function listPayrollsAdmin($teacherId = null, $month = null, $paymentStatus = null) {
        $sql = "
            SELECT p.*, t.teacher_code, u.full_name, t.teacher_type, t.standard_hours
            FROM payrolls p
            JOIN teachers t ON p.teacher_id = t.id
            JOIN users u ON t.user_id = u.id
            WHERE 1=1
        ";
        $params = [];
        if ($teacherId) {
            $sql .= ' AND p.teacher_id = :teacher_id';
            $params['teacher_id'] = (int)$teacherId;
        }
        if ($month) {
            $sql .= ' AND p.month = :month';
            $params['month'] = $month;
        }
        if ($paymentStatus) {
            $sql .= ' AND p.payment_status = :payment_status';
            $params['payment_status'] = $paymentStatus;
        }
        $sql .= ' ORDER BY p.month DESC, u.full_name ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getPayrollById($id) {
        $stmt = $this->db->prepare("
            SELECT p.*, t.teacher_code, u.full_name, t.teacher_type, t.standard_hours
            FROM payrolls p
            JOIN teachers t ON p.teacher_id = t.id
            JOIN users u ON t.user_id = u.id
            WHERE p.id = :id
        ");
        $stmt->execute(['id' => (int)$id]);
        return $stmt->fetch() ?: null;
    }

    private function validatePayrollMonth($month) {
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            return ['error' => 'Tháng phải có định dạng YYYY-MM'];
        }
        $parts = explode('-', $month);
        if ((int)$parts[1] < 1 || (int)$parts[1] > 12) {
            return ['error' => 'Tháng không hợp lệ'];
        }
        return null;
    }

    private function payrollDuplicateExists($teacherId, $month, $excludeId = null) {
        $sql = 'SELECT id FROM payrolls WHERE teacher_id = :tid AND month = :month';
        $params = ['tid' => (int)$teacherId, 'month' => $month];
        if ($excludeId) {
            $sql .= ' AND id != :id';
            $params['id'] = (int)$excludeId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool)$stmt->fetch();
    }

    private function validatePayrollData($data, $forUpdate = false, $payrollId = null) {
        $teacherId = (int)($data['teacher_id'] ?? 0);
        if (!$forUpdate && !$teacherId) {
            return ['error' => 'Vui lòng chọn giáo viên'];
        }
        if ($teacherId) {
            $chk = $this->db->prepare('SELECT id FROM teachers WHERE id = :id');
            $chk->execute(['id' => $teacherId]);
            if (!$chk->fetch()) {
                return ['error' => 'Giáo viên không tồn tại'];
            }
        }

        $month = trim($data['month'] ?? '');
        if ($month === '') {
            return ['error' => 'Tháng lương là bắt buộc'];
        }
        $monthErr = $this->validatePayrollMonth($month);
        if ($monthErr) {
            return $monthErr;
        }

        $hours = isset($data['teaching_hours']) ? (float)$data['teaching_hours'] : -1;
        if ($hours < 0) {
            return ['error' => 'Số giờ dạy không hợp lệ'];
        }

        $salary = isset($data['salary_amount']) ? (float)$data['salary_amount'] : -1;
        if ($salary < 0) {
            return ['error' => 'Số tiền lương không hợp lệ'];
        }

        $status = $data['payment_status'] ?? 'PENDING';
        if (!in_array($status, ['PENDING', 'PAID'], true)) {
            return ['error' => 'Trạng thái thanh toán không hợp lệ'];
        }

        $tid = $teacherId ?: null;
        if ($forUpdate && $payrollId) {
            $existing = $this->getPayrollById($payrollId);
            if (!$existing) {
                return ['error' => 'Không tìm thấy bảng lương'];
            }
            $tid = (int)($data['teacher_id'] ?? $existing['teacher_id']);
            $month = $data['month'] ?? $existing['month'];
        }
        if ($tid && $this->payrollDuplicateExists($tid, $month, $forUpdate ? $payrollId : null)) {
            return ['error' => 'Giáo viên đã có bảng lương tháng này'];
        }

        return null;
    }

    public function createPayroll($data) {
        $err = $this->validatePayrollData($data);
        if ($err) {
            return $err;
        }

        $stmt = $this->db->prepare("
            INSERT INTO payrolls (teacher_id, month, teaching_hours, salary_amount, payment_status)
            VALUES (:teacher_id, :month, :teaching_hours, :salary_amount, :payment_status)
        ");
        $ok = $stmt->execute([
            'teacher_id'      => (int)$data['teacher_id'],
            'month'           => trim($data['month']),
            'teaching_hours'  => round((float)$data['teaching_hours'], 1),
            'salary_amount'   => round((float)$data['salary_amount'], 2),
            'payment_status'  => $data['payment_status'] ?? 'PENDING',
        ]);

        return $ok ? ['success' => true, 'id' => (int)$this->db->lastInsertId()] : ['error' => 'Không thể tạo bảng lương'];
    }

    public function updatePayroll($id, $data) {
        $id = (int)$id;
        if (!$this->getPayrollById($id)) {
            return ['error' => 'Không tìm thấy bảng lương'];
        }

        $err = $this->validatePayrollData($data, true, $id);
        if ($err) {
            return $err;
        }

        $existing = $this->getPayrollById($id);
        $stmt = $this->db->prepare("
            UPDATE payrolls
            SET teacher_id = :teacher_id,
                month = :month,
                teaching_hours = :teaching_hours,
                salary_amount = :salary_amount,
                payment_status = :payment_status
            WHERE id = :id
        ");
        $ok = $stmt->execute([
            'teacher_id'      => (int)($data['teacher_id'] ?? $existing['teacher_id']),
            'month'           => trim($data['month'] ?? $existing['month']),
            'teaching_hours'  => round((float)($data['teaching_hours'] ?? $existing['teaching_hours']), 1),
            'salary_amount'   => round((float)($data['salary_amount'] ?? $existing['salary_amount']), 2),
            'payment_status'  => $data['payment_status'] ?? $existing['payment_status'],
            'id'              => $id,
        ]);

        return $ok ? ['success' => true] : ['error' => 'Không thể cập nhật bảng lương'];
    }

    public function deletePayroll($id) {
        $id = (int)$id;
        if (!$this->getPayrollById($id)) {
            return ['error' => 'Không tìm thấy bảng lương'];
        }
        $ok = $this->db->prepare('DELETE FROM payrolls WHERE id = :id')->execute(['id' => $id]);
        return $ok ? ['success' => true] : ['error' => 'Không thể xóa bảng lương'];
    }

    public function markPayrollPaid($id) {
        $id = (int)$id;
        if (!$this->getPayrollById($id)) {
            return ['error' => 'Không tìm thấy bảng lương'];
        }
        $ok = $this->db->prepare("UPDATE payrolls SET payment_status = 'PAID' WHERE id = :id")
            ->execute(['id' => $id]);
        return $ok ? ['success' => true] : ['error' => 'Không thể đánh dấu đã trả'];
    }

    /**
     * Tính tổng giờ dạy từ lịch phân công (FINAL, CONFIRMED) trong tháng YYYY-MM.
     */
    public function computeTeachingHoursForMonth($teacherId, $month) {
        $teacherId = (int)$teacherId;
        $monthErr = $this->validatePayrollMonth($month);
        if ($monthErr) {
            return $monthErr;
        }

        $monthStart = $month . '-01';
        $monthEnd = date('Y-m-t', strtotime($monthStart));

        $stmt = $this->db->prepare("
            SELECT DISTINCT s.id, s.class_id, s.student_id, s.day_of_week, s.specific_date,
                   s.start_time, s.end_time, s.schedule_type, c.start_date, c.end_date
            FROM schedules s
            JOIN classes c ON s.class_id = c.id
            JOIN teacher_assignments ta ON ta.class_id = c.id AND ta.teacher_id = :tid
            WHERE ta.assignment_status = 'CONFIRMED'
              AND ta.scenario_name = 'FINAL'
              AND c.start_date <= :month_end
              AND c.end_date >= :month_start
        ");
        $stmt->execute([
            'tid' => $teacherId,
            'month_start' => $monthStart,
            'month_end' => $monthEnd,
        ]);
        $items = $stmt->fetchAll();
        if (!$items) {
            return ['hours' => 0.0];
        }

        $scheduleService = new ScheduleService();
        $weekStart = ScheduleService::normalizeWeekStart($monthStart);

        $totalMinutes = 0;
        $seen = [];
        while ($weekStart <= $monthEnd) {
            $expanded = $scheduleService->expandSchedulesForWeek($items, $weekStart);
            foreach ($expanded as $row) {
                $sessionDate = $row['session_date'] ?? null;
                if (!$sessionDate || $sessionDate < $monthStart || $sessionDate > $monthEnd) {
                    continue;
                }
                $key = ($row['class_id'] ?? '') . '|' . $sessionDate . '|' . ($row['start_time'] ?? '') . '|' . ($row['schedule_type'] ?? '');
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                $start = strtotime($sessionDate . ' ' . ($row['start_time'] ?? '00:00:00'));
                $end = strtotime($sessionDate . ' ' . ($row['end_time'] ?? '00:00:00'));
                if ($end > $start) {
                    $totalMinutes += ($end - $start) / 60;
                }
            }
            $weekStart = date('Y-m-d', strtotime($weekStart . ' +7 days'));
        }

        return ['hours' => round($totalMinutes / 60, 1)];
    }

    public function listPayrollMonthOptions() {
        $stmt = $this->db->query("
            SELECT DISTINCT month FROM payrolls
            UNION
            SELECT DATE_FORMAT(CURDATE(), '%Y-%m')
            ORDER BY month DESC
        ");
        return array_column($stmt->fetchAll(), 'month');
    }
}
