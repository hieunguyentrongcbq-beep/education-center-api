<?php
namespace App\Models;

use Core\Database;
use PDO;

class classmodel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ==========================================
    // --- COURSE LOGIC -------------------------
    // ==========================================
    public function getAllStudents($limit = 200, $all = false) {
        $where = $all ? '' : "WHERE s.status = 'ACTIVE'";
        $stmt = $this->db->prepare("
            SELECT s.id, s.student_code, s.status, s.date_of_birth, s.parent_phone,
                   u.full_name, u.email, u.phone
            FROM students s
            JOIN users u ON u.id = s.user_id
            $where
            ORDER BY s.id DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getStudentById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT s.id, s.student_code, s.status, s.user_id, s.date_of_birth, s.parent_phone,
                   u.full_name, u.email, u.phone
            FROM students s
            JOIN users u ON u.id = s.user_id
            WHERE s.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getStudentIdByUserId(int $userId): ?int {
        $stmt = $this->db->prepare("SELECT id FROM students WHERE user_id = :uid LIMIT 1");
        $stmt->execute(['uid' => $userId]);
        $row = $stmt->fetch();
        return $row ? (int)$row['id'] : null;
    }

    /**
     * @return array|null Lỗi ['error' => '...'] hoặc null nếu hợp lệ
     */
    private function validateStudentData($data, $forUpdate = false, $studentId = null) {
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

        if (!$forUpdate) {
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

            $code = trim($data['student_code'] ?? '');
            if ($code === '') {
                return ['error' => 'Mã học viên là bắt buộc'];
            }
            if (!preg_match('/^[A-Za-z0-9_-]{2,30}$/', $code)) {
                return ['error' => 'Mã học viên chỉ gồm chữ, số, gạch ngang (2–30 ký tự)'];
            }

            $password = $data['password'] ?? '';
            if ($password !== '' && strlen($password) < 6) {
                return ['error' => 'Mật khẩu tối thiểu 6 ký tự'];
            }
        }

        $phoneErr = $this->validatePhoneField($data['phone'] ?? '', 'Số điện thoại');
        if ($phoneErr) return $phoneErr;

        $parentErr = $this->validatePhoneField($data['parent_phone'] ?? '', 'SĐT phụ huynh');
        if ($parentErr) return $parentErr;

        $dob = trim($data['date_of_birth'] ?? '');
        if ($dob !== '') {
            if (!$this->isValidDateString($dob)) {
                return ['error' => 'Ngày sinh không hợp lệ'];
            }
            if (strtotime($dob) > strtotime('today')) {
                return ['error' => 'Ngày sinh không thể ở tương lai'];
            }
            $age = (int)date('Y') - (int)date('Y', strtotime($dob));
            if ($age > 100) {
                return ['error' => 'Ngày sinh không hợp lệ'];
            }
        }

        $status = $data['status'] ?? 'ACTIVE';
        if (!in_array($status, ['ACTIVE', 'INACTIVE', 'GRADUATED'], true)) {
            return ['error' => 'Trạng thái không hợp lệ'];
        }

        if ($forUpdate && $studentId && in_array($status, ['INACTIVE', 'GRADUATED'], true)) {
            $cntStmt = $this->db->prepare("
                SELECT COUNT(*) FROM enrollments
                WHERE student_id=:sid AND status='ACTIVE' AND payment_status='PAID'
            ");
            $cntStmt->execute(['sid' => $studentId]);
            $activePaid = (int)$cntStmt->fetchColumn();
            if ($activePaid > 0 && $status === 'INACTIVE') {
                return ['error' => 'Không thể chuyển INACTIVE: học viên đang học ' . $activePaid . ' lớp đã thanh toán'];
            }
        }

        return null;
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

    public function createStudent($data) {
        $validationError = $this->validateStudentData($data, false);
        if ($validationError) {
            return $validationError;
        }

        $email = trim($data['email']);
        $studentCode = trim($data['student_code']);

        $chk = $this->db->prepare("SELECT id FROM users WHERE email=:email");
        $chk->execute(['email' => $email]);
        if ($chk->fetch()) return ['error' => 'Email đã tồn tại'];

        $chk2 = $this->db->prepare("SELECT id FROM students WHERE student_code=:code");
        $chk2->execute(['code' => $studentCode]);
        if ($chk2->fetch()) return ['error' => 'Mã học viên đã tồn tại'];

        $this->db->beginTransaction();
        try {
            $rawPassword = trim($data['password'] ?? '');
            $password = password_hash($rawPassword !== '' ? $rawPassword : 'hocvien123', PASSWORD_BCRYPT);
            $phone = trim($data['phone'] ?? '');
            $parentPhone = trim($data['parent_phone'] ?? '');
            $dob = trim($data['date_of_birth'] ?? '');

            $stmt = $this->db->prepare("
                INSERT INTO users (full_name, email, password_hash, phone, status)
                VALUES (:full_name, :email, :pass, :phone, 'ACTIVE')
            ");
            $stmt->execute([
                'full_name' => trim($data['full_name']),
                'email'     => $email,
                'pass'      => $password,
                'phone'     => $phone !== '' ? $phone : null,
            ]);
            $userId = $this->db->lastInsertId();

            $stmt2 = $this->db->prepare("
                INSERT INTO students (user_id, student_code, date_of_birth, parent_phone, status)
                VALUES (:uid, :code, :dob, :pp, :status)
            ");
            $stmt2->execute([
                'uid'    => $userId,
                'code'   => $studentCode,
                'dob'    => $dob !== '' ? $dob : null,
                'pp'     => $parentPhone !== '' ? $parentPhone : null,
                'status' => $data['status'] ?? 'ACTIVE',
            ]);

            // Assign student role (role_id = 4 for student, adjust if different)
            $roleStmt = $this->db->prepare("SELECT id FROM roles WHERE role_name='STUDENT' LIMIT 1");
            $roleStmt->execute();
            $role = $roleStmt->fetch();
            if ($role) {
                $this->db->prepare("INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (:uid, :rid)")
                    ->execute(['uid' => $userId, 'rid' => $role['id']]);
            }

            $this->db->commit();
            return ['success' => true, 'student_id' => $this->db->lastInsertId()];
        } catch (\PDOException $e) {
            $this->db->rollBack();
            return ['error' => 'Lỗi tạo học viên: ' . $e->getMessage()];
        }
    }

    public function updateStudent($id, $data) {
        $stmt = $this->db->prepare("SELECT s.id, s.user_id FROM students s WHERE s.id=:id");
        $stmt->execute(['id' => $id]);
        $s = $stmt->fetch();
        if (!$s) {
            return ['error' => 'Không tìm thấy học viên'];
        }

        $validationError = $this->validateStudentData($data, true, (int)$id);
        if ($validationError) {
            return $validationError;
        }

        $phone = trim($data['phone'] ?? '');
        $parentPhone = trim($data['parent_phone'] ?? '');
        $dob = trim($data['date_of_birth'] ?? '');

        try {
            $this->db->prepare("
                UPDATE users SET full_name=:full_name, phone=:phone WHERE id=:uid
            ")->execute([
                'full_name' => trim($data['full_name']),
                'phone'     => $phone !== '' ? $phone : null,
                'uid'       => $s['user_id'],
            ]);

            $this->db->prepare("
                UPDATE students SET date_of_birth=:dob, parent_phone=:pp, status=:status WHERE id=:id
            ")->execute([
                'dob'    => $dob !== '' ? $dob : null,
                'pp'     => $parentPhone !== '' ? $parentPhone : null,
                'status' => $data['status'] ?? 'ACTIVE',
                'id'     => $id,
            ]);
            return ['success' => true];
        } catch (\PDOException $e) {
            return ['error' => 'Không thể cập nhật học viên'];
        }
    }

    public function deleteStudent($id) {
        $stmt = $this->db->prepare("SELECT id, user_id FROM students WHERE id=:id");
        $stmt->execute(['id' => $id]);
        $student = $stmt->fetch();
        if (!$student) return ['error' => 'Không tìm thấy học viên'];

        try {
            $this->db->beginTransaction();
            // Xóa điểm danh
            $this->db->prepare("DELETE FROM attendance WHERE student_id=:id")->execute(['id' => $id]);
            // Xóa học phí
            $this->db->prepare("DELETE FROM tuition_payments WHERE student_id=:id")->execute(['id' => $id]);
            // Xóa đăng ký lớp
            $this->db->prepare("DELETE FROM enrollments WHERE student_id=:id")->execute(['id' => $id]);
            // Xóa học viên (cascade xóa user)
            $this->db->prepare("DELETE FROM students WHERE id=:id")->execute(['id' => $id]);
            $this->db->commit();
            return ['success' => true];
        } catch (\PDOException $e) {
            $this->db->rollBack();
            return ['error' => 'Không thể xóa: ' . $e->getMessage()];
        }
    }

    public function deleteTuition($id) {
        $stmt = $this->db->prepare("SELECT id FROM tuition_payments WHERE id=:id");
        $stmt->execute(['id' => $id]);
        if (!$stmt->fetch()) return ['error' => 'Không tìm thấy bản ghi học phí'];

        try {
            $this->db->prepare("DELETE FROM tuition_payments WHERE id=:id")->execute(['id' => $id]);
            return ['success' => true];
        } catch (\PDOException $e) {
            return ['error' => 'Không thể xóa: ' . $e->getMessage()];
        }
    }

    public function getAllClassrooms() {
        $stmt = $this->db->query("SELECT * FROM classrooms WHERE status = 'ACTIVE' ORDER BY room_name");
        return $stmt->fetchAll();
    }

    public function listClassroomsAdmin() {
        $stmt = $this->db->query("
            SELECT c.*,
                   (SELECT COUNT(*) FROM classes cl WHERE cl.classroom_id = c.id) AS class_count
            FROM classrooms c
            ORDER BY c.room_name ASC
        ");
        return $stmt->fetchAll();
    }

    public function getClassroomById($id) {
        $stmt = $this->db->prepare("SELECT * FROM classrooms WHERE id = :id");
        $stmt->execute(['id' => (int)$id]);
        return $stmt->fetch() ?: null;
    }

    private function validateClassroomData($data, $excludeId = null) {
        $name = trim($data['room_name'] ?? '');
        if ($name === '') {
            return ['error' => 'Tên phòng là bắt buộc'];
        }
        if (mb_strlen($name) > 50) {
            return ['error' => 'Tên phòng tối đa 50 ký tự'];
        }

        $dupSql = 'SELECT id FROM classrooms WHERE room_name = :name';
        $dupParams = ['name' => $name];
        if ($excludeId) {
            $dupSql .= ' AND id != :id';
            $dupParams['id'] = (int)$excludeId;
        }
        $dup = $this->db->prepare($dupSql);
        $dup->execute($dupParams);
        if ($dup->fetch()) {
            return ['error' => 'Tên phòng đã tồn tại'];
        }

        if (!isset($data['capacity']) || $data['capacity'] === '') {
            return ['error' => 'Sức chứa là bắt buộc'];
        }
        $capacity = (int)$data['capacity'];
        if ($capacity < 1 || $capacity > 500) {
            return ['error' => 'Sức chứa phải từ 1 đến 500'];
        }

        $status = $data['status'] ?? 'ACTIVE';
        if (!in_array($status, ['ACTIVE', 'INACTIVE'], true)) {
            return ['error' => 'Trạng thái không hợp lệ'];
        }

        return null;
    }

    public function createClassroom($data) {
        $err = $this->validateClassroomData($data);
        if ($err) {
            return $err;
        }

        $stmt = $this->db->prepare("
            INSERT INTO classrooms (room_name, capacity, location, status)
            VALUES (:room_name, :capacity, :location, :status)
        ");
        $ok = $stmt->execute([
            'room_name' => trim($data['room_name']),
            'capacity'  => (int)$data['capacity'],
            'location'  => trim($data['location'] ?? '') ?: null,
            'status'    => $data['status'] ?? 'ACTIVE',
        ]);

        return $ok ? ['success' => true, 'id' => $this->db->lastInsertId()] : ['error' => 'Không thể tạo phòng học'];
    }

    public function updateClassroom($id, $data) {
        $id = (int)$id;
        if (!$this->getClassroomById($id)) {
            return ['error' => 'Không tìm thấy phòng học'];
        }

        $err = $this->validateClassroomData($data, $id);
        if ($err) {
            return $err;
        }

        $stmt = $this->db->prepare("
            UPDATE classrooms
            SET room_name = :room_name, capacity = :capacity, location = :location, status = :status
            WHERE id = :id
        ");
        $ok = $stmt->execute([
            'room_name' => trim($data['room_name']),
            'capacity'  => (int)$data['capacity'],
            'location'  => trim($data['location'] ?? '') ?: null,
            'status'    => $data['status'] ?? 'ACTIVE',
            'id'        => $id,
        ]);

        return $ok ? ['success' => true] : ['error' => 'Không thể cập nhật phòng học'];
    }

    public function deleteClassroom($id) {
        $id = (int)$id;
        if (!$this->getClassroomById($id)) {
            return ['error' => 'Không tìm thấy phòng học'];
        }

        $used = $this->db->prepare('SELECT COUNT(*) FROM classes WHERE classroom_id = :id');
        $used->execute(['id' => $id]);
        if ((int)$used->fetchColumn() > 0) {
            return ['error' => 'Không thể xóa: phòng đang được gán cho lớp học'];
        }

        $ok = $this->db->prepare('DELETE FROM classrooms WHERE id = :id')->execute(['id' => $id]);
        return $ok ? ['success' => true] : ['error' => 'Không thể xóa phòng học'];
    }

    public function getAllSemesters() {
        $stmt = $this->db->query("SELECT * FROM semesters ORDER BY start_date DESC");
        return $stmt->fetchAll();
    }

    public function listSemestersAdmin() {
        $stmt = $this->db->query("
            SELECT s.*,
                   (SELECT COUNT(*) FROM classes c WHERE c.semester_id = s.id) AS class_count
            FROM semesters s
            ORDER BY s.start_date DESC
        ");
        return $stmt->fetchAll();
    }

    public function getSemesterById($id) {
        $stmt = $this->db->prepare('SELECT * FROM semesters WHERE id = :id');
        $stmt->execute(['id' => (int)$id]);
        return $stmt->fetch() ?: null;
    }

    private function validateSemesterData($data) {
        $name = trim($data['semester_name'] ?? '');
        if ($name === '') {
            return ['error' => 'Tên học kỳ là bắt buộc'];
        }
        if (mb_strlen($name) > 100) {
            return ['error' => 'Tên học kỳ tối đa 100 ký tự'];
        }

        if (empty($data['start_date']) || empty($data['end_date'])) {
            return ['error' => 'Ngày bắt đầu và kết thúc là bắt buộc'];
        }
        if (!strtotime($data['start_date']) || !strtotime($data['end_date'])) {
            return ['error' => 'Ngày không hợp lệ'];
        }
        if ($data['end_date'] < $data['start_date']) {
            return ['error' => 'Ngày kết thúc phải sau ngày bắt đầu'];
        }

        $status = $data['status'] ?? 'UPCOMING';
        if (!in_array($status, ['UPCOMING', 'ONGOING', 'COMPLETED'], true)) {
            return ['error' => 'Trạng thái không hợp lệ'];
        }

        return null;
    }

    public function createSemester($data) {
        $err = $this->validateSemesterData($data);
        if ($err) {
            return $err;
        }

        $stmt = $this->db->prepare("
            INSERT INTO semesters (semester_name, start_date, end_date, status)
            VALUES (:semester_name, :start_date, :end_date, :status)
        ");
        $ok = $stmt->execute([
            'semester_name' => trim($data['semester_name']),
            'start_date'    => $data['start_date'],
            'end_date'      => $data['end_date'],
            'status'        => $data['status'] ?? 'UPCOMING',
        ]);

        return $ok ? ['success' => true, 'id' => $this->db->lastInsertId()] : ['error' => 'Không thể tạo học kỳ'];
    }

    public function updateSemester($id, $data) {
        $id = (int)$id;
        if (!$this->getSemesterById($id)) {
            return ['error' => 'Không tìm thấy học kỳ'];
        }

        $err = $this->validateSemesterData($data);
        if ($err) {
            return $err;
        }

        $stmt = $this->db->prepare("
            UPDATE semesters
            SET semester_name = :semester_name, start_date = :start_date,
                end_date = :end_date, status = :status
            WHERE id = :id
        ");
        $ok = $stmt->execute([
            'semester_name' => trim($data['semester_name']),
            'start_date'    => $data['start_date'],
            'end_date'      => $data['end_date'],
            'status'        => $data['status'] ?? 'UPCOMING',
            'id'            => $id,
        ]);

        return $ok ? ['success' => true] : ['error' => 'Không thể cập nhật học kỳ'];
    }

    public function deleteSemester($id) {
        $id = (int)$id;
        if (!$this->getSemesterById($id)) {
            return ['error' => 'Không tìm thấy học kỳ'];
        }

        $used = $this->db->prepare('SELECT COUNT(*) FROM classes WHERE semester_id = :id');
        $used->execute(['id' => $id]);
        if ((int)$used->fetchColumn() > 0) {
            return ['error' => 'Không thể xóa: học kỳ đang được gán cho lớp học'];
        }

        $plans = $this->db->prepare('SELECT COUNT(*) FROM class_plans WHERE semester_id = :id');
        $plans->execute(['id' => $id]);
        if ((int)$plans->fetchColumn() > 0) {
            return ['error' => 'Không thể xóa: học kỳ đang có kế hoạch mở lớp'];
        }

        $ok = $this->db->prepare('DELETE FROM semesters WHERE id = :id')->execute(['id' => $id]);
        return $ok ? ['success' => true] : ['error' => 'Không thể xóa học kỳ'];
    }

    public function listClassPlansAdmin() {
        $stmt = $this->db->query("
            SELECT cp.*,
                   co.course_code, co.course_name,
                   s.semester_name,
                   u.full_name AS created_by_name,
                   (SELECT COUNT(*) FROM classes c
                    WHERE c.course_id = cp.course_id AND c.semester_id = cp.semester_id) AS actual_class_count
            FROM class_plans cp
            JOIN courses co ON cp.course_id = co.id
            JOIN semesters s ON cp.semester_id = s.id
            LEFT JOIN users u ON cp.created_by = u.id
            ORDER BY s.start_date DESC, co.course_code ASC
        ");
        return $stmt->fetchAll();
    }

    public function getClassPlanById($id) {
        $stmt = $this->db->prepare('
            SELECT cp.*, co.course_code, co.course_name, s.semester_name
            FROM class_plans cp
            JOIN courses co ON cp.course_id = co.id
            JOIN semesters s ON cp.semester_id = s.id
            WHERE cp.id = :id
        ');
        $stmt->execute(['id' => (int)$id]);
        return $stmt->fetch() ?: null;
    }

    private function validateClassPlanData($data, ?int $excludeId = null) {
        $courseId = (int)($data['course_id'] ?? 0);
        $semesterId = (int)($data['semester_id'] ?? 0);
        if (!$courseId) {
            return ['error' => 'Vui lòng chọn khóa học'];
        }
        if (!$semesterId) {
            return ['error' => 'Vui lòng chọn học kỳ'];
        }

        $course = $this->db->prepare('SELECT id FROM courses WHERE id = :id');
        $course->execute(['id' => $courseId]);
        if (!$course->fetch()) {
            return ['error' => 'Khóa học không tồn tại'];
        }

        $semester = $this->db->prepare('SELECT id FROM semesters WHERE id = :id');
        $semester->execute(['id' => $semesterId]);
        if (!$semester->fetch()) {
            return ['error' => 'Học kỳ không tồn tại'];
        }

        $planned = (int)($data['planned_class_count'] ?? 0);
        $target = (int)($data['target_student_count'] ?? 0);
        if ($planned < 1) {
            return ['error' => 'Số lớp dự kiến phải >= 1'];
        }
        if ($target < 1) {
            return ['error' => 'Sĩ số mục tiêu / lớp phải >= 1'];
        }

        $status = $data['status'] ?? 'DRAFT';
        if (!in_array($status, ['DRAFT', 'APPROVED', 'CANCELLED'], true)) {
            return ['error' => 'Trạng thái không hợp lệ'];
        }

        $dupSql = 'SELECT id FROM class_plans WHERE course_id = :cid AND semester_id = :sid';
        $dupParams = ['cid' => $courseId, 'sid' => $semesterId];
        if ($excludeId) {
            $dupSql .= ' AND id != :ex';
            $dupParams['ex'] = $excludeId;
        }
        $dup = $this->db->prepare($dupSql);
        $dup->execute($dupParams);
        if ($dup->fetch()) {
            return ['error' => 'Đã có kế hoạch mở lớp cho khóa học này trong học kỳ đã chọn'];
        }

        return null;
    }

    public function createClassPlan($data, ?int $createdBy = null) {
        $err = $this->validateClassPlanData($data);
        if ($err) {
            return $err;
        }

        $stmt = $this->db->prepare("
            INSERT INTO class_plans (course_id, semester_id, planned_class_count, target_student_count, status, created_by)
            VALUES (:course_id, :semester_id, :planned_class_count, :target_student_count, :status, :created_by)
        ");
        $ok = $stmt->execute([
            'course_id'            => (int)$data['course_id'],
            'semester_id'          => (int)$data['semester_id'],
            'planned_class_count'  => (int)$data['planned_class_count'],
            'target_student_count' => (int)$data['target_student_count'],
            'status'               => $data['status'] ?? 'DRAFT',
            'created_by'           => $createdBy,
        ]);

        return $ok ? ['success' => true, 'id' => $this->db->lastInsertId()] : ['error' => 'Không thể tạo kế hoạch'];
    }

    public function updateClassPlan($id, $data) {
        $id = (int)$id;
        if (!$this->getClassPlanById($id)) {
            return ['error' => 'Không tìm thấy kế hoạch mở lớp'];
        }

        $err = $this->validateClassPlanData($data, $id);
        if ($err) {
            return $err;
        }

        $stmt = $this->db->prepare("
            UPDATE class_plans
            SET course_id = :course_id, semester_id = :semester_id,
                planned_class_count = :planned_class_count,
                target_student_count = :target_student_count,
                status = :status
            WHERE id = :id
        ");
        $ok = $stmt->execute([
            'course_id'            => (int)$data['course_id'],
            'semester_id'          => (int)$data['semester_id'],
            'planned_class_count'  => (int)$data['planned_class_count'],
            'target_student_count' => (int)$data['target_student_count'],
            'status'               => $data['status'] ?? 'DRAFT',
            'id'                   => $id,
        ]);

        return $ok ? ['success' => true] : ['error' => 'Không thể cập nhật kế hoạch'];
    }

    public function deleteClassPlan($id) {
        $id = (int)$id;
        if (!$this->getClassPlanById($id)) {
            return ['error' => 'Không tìm thấy kế hoạch mở lớp'];
        }

        $ok = $this->db->prepare('DELETE FROM class_plans WHERE id = :id')->execute(['id' => $id]);
        return $ok ? ['success' => true] : ['error' => 'Không thể xóa kế hoạch'];
    }

    public function getAllCourses($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $stmt = $this->db->prepare("SELECT * FROM courses ORDER BY id DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findCourseByCode($courseCode) {
        $stmt = $this->db->prepare("SELECT * FROM courses WHERE course_code = :course_code");
        $stmt->execute(['course_code' => $courseCode]);
        return $stmt->fetch();
    }

    /**
     * @return array|null Lỗi ['error' => '...'] hoặc null nếu hợp lệ
     */
    private function validateCourseData($data, $forUpdate = false) {
        if (!$forUpdate) {
            $code = trim($data['course_code'] ?? '');
            if ($code === '') {
                return ['error' => 'Mã khóa học là bắt buộc'];
            }
            if (!preg_match('/^[A-Za-z0-9_-]{2,30}$/', $code)) {
                return ['error' => 'Mã khóa học chỉ gồm chữ, số, gạch ngang (2–30 ký tự)'];
            }
        }

        $name = trim($data['course_name'] ?? '');
        if ($name === '') {
            return ['error' => 'Tên khóa học là bắt buộc'];
        }
        if (mb_strlen($name) < 3) {
            return ['error' => 'Tên khóa học tối thiểu 3 ký tự'];
        }
        if (mb_strlen($name) > 200) {
            return ['error' => 'Tên khóa học tối đa 200 ký tự'];
        }

        if (!isset($data['tuition_fee']) || $data['tuition_fee'] === '') {
            return ['error' => 'Học phí là bắt buộc'];
        }
        if (!is_numeric($data['tuition_fee']) || (float)$data['tuition_fee'] <= 0) {
            return ['error' => 'Học phí phải là số dương'];
        }
        if ((float)$data['tuition_fee'] > 999999999.99) {
            return ['error' => 'Học phí vượt quá giới hạn cho phép'];
        }

        if (!isset($data['total_sessions']) || $data['total_sessions'] === '') {
            return ['error' => 'Số buổi học là bắt buộc'];
        }
        $totalSessions = (int)$data['total_sessions'];
        if ($totalSessions < 2) {
            return ['error' => 'Số buổi học tối thiểu là 2'];
        }
        if ($totalSessions % 2 !== 0) {
            return ['error' => 'Số buổi học phải là số chẵn (2 buổi/tuần)'];
        }
        if ($totalSessions > 200) {
            return ['error' => 'Số buổi học tối đa 200'];
        }

        if (!isset($data['day_primary']) || $data['day_primary'] === '') {
            return ['error' => 'Ngày học chính 1 là bắt buộc'];
        }
        if (!isset($data['day_secondary']) || $data['day_secondary'] === '') {
            return ['error' => 'Ngày học chính 2 là bắt buộc'];
        }
        $dayPrimary = (int)$data['day_primary'];
        $daySecondary = (int)$data['day_secondary'];
        if ($dayPrimary < 0 || $dayPrimary > 6) {
            return ['error' => 'Ngày học chính 1 không hợp lệ (0=CN … 6=T7)'];
        }
        if ($daySecondary < 0 || $daySecondary > 6) {
            return ['error' => 'Ngày học chính 2 không hợp lệ (0=CN … 6=T7)'];
        }
        if ($dayPrimary === $daySecondary) {
            return ['error' => 'Hai ngày học trong tuần phải khác nhau'];
        }

        $status = $data['status'] ?? 'ACTIVE';
        if (!in_array($status, ['ACTIVE', 'INACTIVE'], true)) {
            return ['error' => 'Trạng thái không hợp lệ'];
        }

        $description = $data['description'] ?? '';
        if (mb_strlen($description) > 5000) {
            return ['error' => 'Mô tả tối đa 5000 ký tự'];
        }

        if (!$forUpdate) {
            $timeErr = $this->validateCourseTimeField($data['default_start_time'] ?? '18:00', 'Giờ bắt đầu');
            if ($timeErr) return $timeErr;
            $timeErr = $this->validateCourseTimeField($data['default_end_time'] ?? '20:00', 'Giờ kết thúc');
            if ($timeErr) return $timeErr;
            $start = $this->normalizeTimeValue($data['default_start_time'] ?? '18:00');
            $end = $this->normalizeTimeValue($data['default_end_time'] ?? '20:00');
            if (strtotime($start) >= strtotime($end)) {
                return ['error' => 'Giờ kết thúc phải sau giờ bắt đầu'];
            }
        }

        return null;
    }

    private function validateCourseTimeField($value, $label) {
        $normalized = $this->normalizeTimeValue($value);
        if ($normalized === null) {
            return ['error' => $label . ' không hợp lệ (định dạng HH:MM)'];
        }
        return null;
    }

    private function normalizeTimeValue($value) {
        $value = trim((string)$value);
        if ($value === '') {
            return null;
        }
        if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $value)) {
            $ts = strtotime($value);
            return $ts ? date('H:i:s', $ts) : null;
        }
        return null;
    }

    public function createCourse($data) {
        $validationError = $this->validateCourseData($data, false);
        if ($validationError) {
            return $validationError;
        }

        $courseCode = trim($data['course_code']);
        if ($this->findCourseByCode($courseCode)) {
            return ['error' => 'Mã khóa học đã tồn tại'];
        }

        $totalSessions = (int)$data['total_sessions'];
        $durationWeeks = (int)ceil($totalSessions / 2);

        $stmt = $this->db->prepare("
            INSERT INTO courses (course_code, course_name, description, duration_weeks, total_sessions,
                day_primary, day_secondary, default_start_time, default_end_time, tuition_fee, status) 
            VALUES (:course_code, :course_name, :description, :duration_weeks, :total_sessions,
                :day_primary, :day_secondary, :default_start_time, :default_end_time, :tuition_fee, :status)
        ");

        $success = $stmt->execute([
            'course_code' => $courseCode,
            'course_name' => trim($data['course_name']),
            'description' => trim($data['description'] ?? ''),
            'duration_weeks' => $durationWeeks,
            'total_sessions' => $totalSessions,
            'day_primary' => (int)$data['day_primary'],
            'day_secondary' => (int)$data['day_secondary'],
            'default_start_time' => $this->normalizeTimeValue($data['default_start_time'] ?? '18:00'),
            'default_end_time' => $this->normalizeTimeValue($data['default_end_time'] ?? '20:00'),
            'tuition_fee' => $data['tuition_fee'],
            'status' => $data['status'] ?? 'ACTIVE',
        ]);

        if ($success) return ['success' => true, 'id' => $this->db->lastInsertId()];
        return ['error' => 'Không thể tạo khóa học'];
    }

    public function updateCourse($id, $data) {
        $stmt = $this->db->prepare("SELECT id FROM courses WHERE id = :id");
        $stmt->execute(['id' => $id]);
        if (!$stmt->fetch()) {
            return ['error' => 'Không tìm thấy khóa học'];
        }

        $validationError = $this->validateCourseData($data, true);
        if ($validationError) {
            return $validationError;
        }

        $totalSessions = (int)$data['total_sessions'];
        $durationWeeks = (int)ceil($totalSessions / 2);
        $stmt = $this->db->prepare("
            UPDATE courses SET course_name=:course_name, description=:description,
            duration_weeks=:duration_weeks, total_sessions=:total_sessions,
            day_primary=:day_primary, day_secondary=:day_secondary,
            tuition_fee=:tuition_fee, status=:status
            WHERE id=:id
        ");
        $ok = $stmt->execute([
            'course_name'    => trim($data['course_name']),
            'description'    => trim($data['description'] ?? ''),
            'duration_weeks' => $durationWeeks,
            'total_sessions' => $totalSessions,
            'day_primary'    => (int)$data['day_primary'],
            'day_secondary'  => (int)$data['day_secondary'],
            'tuition_fee'    => $data['tuition_fee'],
            'status'         => $data['status'] ?? 'ACTIVE',
            'id'             => $id,
        ]);
        return $ok ? ['success' => true] : ['error' => 'Không thể cập nhật khóa học'];
    }

    public function deleteCourse($id) {
        $stmt = $this->db->prepare("SELECT id FROM courses WHERE id=:id");
        $stmt->execute(['id' => $id]);
        if (!$stmt->fetch()) return ['error' => 'Không tìm thấy khóa học'];

        $chk = $this->db->prepare("SELECT COUNT(*) FROM classes WHERE course_id=:id");
        $chk->execute(['id' => $id]);
        if ($chk->fetchColumn() > 0)
            return ['error' => 'Không thể xóa: khóa học đang có lớp học liên kết. Hãy xóa các lớp trước.'];

        try {
            $ok = $this->db->prepare("DELETE FROM courses WHERE id=:id")->execute(['id' => $id]);
            return $ok ? ['success' => true] : ['error' => 'Xóa thất bại'];
        } catch (\PDOException $e) {
            return ['error' => 'Không thể xóa do ràng buộc dữ liệu'];
        }
    }

    // ==========================================
    // --- CLASS LOGIC --------------------------
    // ==========================================
    public function getAllClasses($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $stmt = $this->db->prepare("
            SELECT c.*, cr.course_name, cr.duration_weeks, t.teacher_code, rm.room_name 
            FROM classes c
            LEFT JOIN courses cr ON c.course_id = cr.id
            LEFT JOIN teachers t ON c.teacher_id = t.id
            LEFT JOIN classrooms rm ON c.classroom_id = rm.id
            ORDER BY c.id DESC 
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findClassByCode($classCode) {
        $stmt = $this->db->prepare("SELECT * FROM classes WHERE class_code = :class_code");
        $stmt->execute(['class_code' => $classCode]);
        return $stmt->fetch();
    }

    private function isValidDateString($date) {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * @return array|null Lỗi ['error' => '...'] hoặc null nếu hợp lệ
     */
    private function validateClassData($data, $forUpdate = false, $classId = null) {
        if (!$forUpdate) {
            $code = trim($data['class_code'] ?? '');
            if ($code === '') {
                return ['error' => 'Mã lớp là bắt buộc'];
            }
            if (!preg_match('/^[A-Za-z0-9_-]{2,40}$/', $code)) {
                return ['error' => 'Mã lớp chỉ gồm chữ, số, gạch ngang (2–40 ký tự)'];
            }
        }

        if (!$forUpdate) {
            if (empty($data['course_id'])) {
                return ['error' => 'Khóa học là bắt buộc'];
            }
            $courseStmt = $this->db->prepare("SELECT id, status, total_sessions FROM courses WHERE id=:id");
            $courseStmt->execute(['id' => (int)$data['course_id']]);
            $course = $courseStmt->fetch();
            if (!$course) {
                return ['error' => 'Khóa học không tồn tại'];
            }
            if ($course['status'] !== 'ACTIVE') {
                return ['error' => 'Khóa học không còn hoạt động'];
            }
        }

        if (!isset($data['max_students']) || $data['max_students'] === '') {
            return ['error' => 'Sĩ số tối đa là bắt buộc'];
        }
        $maxStudents = (int)$data['max_students'];
        if ($maxStudents < 1) {
            return ['error' => 'Sĩ số tối đa tối thiểu là 1'];
        }
        if ($maxStudents > 500) {
            return ['error' => 'Sĩ số tối đa không được vượt quá 500'];
        }

        if ($forUpdate && $classId) {
            $cntStmt = $this->db->prepare("SELECT COUNT(*) FROM enrollments WHERE class_id=:cid AND status='ACTIVE'");
            $cntStmt->execute(['cid' => $classId]);
            $enrolled = (int)$cntStmt->fetchColumn();
            if ($maxStudents < $enrolled) {
                return ['error' => 'Sĩ số tối đa không được nhỏ hơn số học viên đang học (' . $enrolled . ')'];
            }
        }

        if (empty($data['start_date'])) {
            return ['error' => 'Ngày bắt đầu là bắt buộc'];
        }
        if (!$this->isValidDateString($data['start_date'])) {
            return ['error' => 'Ngày bắt đầu không hợp lệ'];
        }

        $endDate = $data['end_date'] ?? '';
        if ($forUpdate) {
            if ($endDate === '') {
                return ['error' => 'Ngày kết thúc là bắt buộc'];
            }
            if (!$this->isValidDateString($endDate)) {
                return ['error' => 'Ngày kết thúc không hợp lệ'];
            }
            if (strtotime($endDate) < strtotime($data['start_date'])) {
                return ['error' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu'];
            }
        } elseif ($endDate !== '') {
            if (!$this->isValidDateString($endDate)) {
                return ['error' => 'Ngày kết thúc không hợp lệ'];
            }
            if (strtotime($endDate) < strtotime($data['start_date'])) {
                return ['error' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu'];
            }
        }

        $status = $data['status'] ?? 'UPCOMING';
        if (!in_array($status, ['UPCOMING', 'ONGOING', 'COMPLETED', 'CANCELLED'], true)) {
            return ['error' => 'Trạng thái không hợp lệ'];
        }

        if (!empty($data['semester_id'])) {
            $s = $this->db->prepare("SELECT id FROM semesters WHERE id=:id");
            $s->execute(['id' => (int)$data['semester_id']]);
            if (!$s->fetch()) {
                return ['error' => 'Học kỳ không tồn tại'];
            }
        }

        if (!empty($data['classroom_id'])) {
            $r = $this->db->prepare("SELECT id FROM classrooms WHERE id=:id AND status='ACTIVE'");
            $r->execute(['id' => (int)$data['classroom_id']]);
            if (!$r->fetch()) {
                return ['error' => 'Phòng học không tồn tại hoặc không hoạt động'];
            }
        }

        if (!empty($data['teacher_id'])) {
            $t = $this->db->prepare("SELECT id FROM teachers WHERE id=:id AND status='ACTIVE'");
            $t->execute(['id' => (int)$data['teacher_id']]);
            if (!$t->fetch()) {
                return ['error' => 'Giáo viên không tồn tại hoặc không hoạt động'];
            }
        }

        return null;
    }

    public function createClass($data) {
        $validationError = $this->validateClassData($data, false);
        if ($validationError) {
            return $validationError;
        }

        $classCode = trim($data['class_code']);
        if ($this->findClassByCode($classCode)) {
            return ['error' => 'Mã lớp đã tồn tại'];
        }

        $courseStmt = $this->db->prepare("SELECT total_sessions FROM courses WHERE id=:id");
        $courseStmt->execute(['id' => (int)$data['course_id']]);
        $course = $courseStmt->fetch();
        $endDate = !empty($data['end_date'])
            ? $data['end_date']
            : ScheduleService::calcEndDate($data['start_date'], (int)($course['total_sessions'] ?? 20));

        $stmt = $this->db->prepare("
            INSERT INTO classes (class_code, course_id, teacher_id, classroom_id, semester_id, max_students, start_date, end_date, status) 
            VALUES (:class_code, :course_id, :teacher_id, :classroom_id, :semester_id, :max_students, :start_date, :end_date, :status)
        ");

        $success = $stmt->execute([
            'class_code' => $classCode,
            'course_id' => (int)$data['course_id'],
            'teacher_id' => !empty($data['teacher_id']) ? (int)$data['teacher_id'] : null,
            'classroom_id' => !empty($data['classroom_id']) ? (int)$data['classroom_id'] : null,
            'semester_id' => !empty($data['semester_id']) ? (int)$data['semester_id'] : null,
            'max_students' => (int)$data['max_students'],
            'start_date' => $data['start_date'],
            'end_date' => $endDate,
            'status' => $data['status'] ?? 'UPCOMING',
        ]);

        if ($success) return ['success' => true, 'id' => $this->db->lastInsertId()];
        return ['error' => 'Không thể tạo lớp học'];
    }

    public function updateClass($id, $data) {
        $stmt = $this->db->prepare("SELECT id FROM classes WHERE id = :id");
        $stmt->execute(['id' => $id]);
        if (!$stmt->fetch()) {
            return ['error' => 'Không tìm thấy lớp học'];
        }

        $validationError = $this->validateClassData($data, true, (int)$id);
        if ($validationError) {
            return $validationError;
        }

        $stmt = $this->db->prepare("
            UPDATE classes SET teacher_id=:teacher_id, classroom_id=:classroom_id,
            semester_id=:semester_id, max_students=:max_students,
            start_date=:start_date, end_date=:end_date, status=:status
            WHERE id=:id
        ");
        $ok = $stmt->execute([
            'teacher_id'   => !empty($data['teacher_id']) ? (int)$data['teacher_id'] : null,
            'classroom_id' => !empty($data['classroom_id']) ? (int)$data['classroom_id'] : null,
            'semester_id'  => !empty($data['semester_id']) ? (int)$data['semester_id'] : null,
            'max_students' => (int)$data['max_students'],
            'start_date'   => $data['start_date'],
            'end_date'     => $data['end_date'],
            'status'       => $data['status'] ?? 'UPCOMING',
            'id'           => $id,
        ]);
        return $ok ? ['success' => true] : ['error' => 'Không thể cập nhật lớp học'];
    }

    public function deleteClass($id) {
        $stmt = $this->db->prepare("SELECT id FROM classes WHERE id=:id");
        $stmt->execute(['id' => $id]);
        if (!$stmt->fetch()) return ['error' => 'Không tìm thấy lớp học'];

        try {
            $this->db->beginTransaction();
            // Cascade delete: attendance → teacher_assignments → schedules → tuition_payments → enrollments → class
            $this->db->prepare("DELETE FROM attendance WHERE class_id=:id")->execute(['id'=>$id]);
            $this->db->prepare("DELETE FROM teacher_assignments WHERE class_id=:id")->execute(['id'=>$id]);
            $this->db->prepare("DELETE FROM schedules WHERE class_id=:id")->execute(['id'=>$id]);
            $this->db->prepare("UPDATE tuition_payments SET class_id=NULL WHERE class_id=:id")->execute(['id'=>$id]);
            $this->db->prepare("DELETE FROM enrollments WHERE class_id=:id")->execute(['id'=>$id]);
            $this->db->prepare("DELETE FROM classes WHERE id=:id")->execute(['id'=>$id]);
            $this->db->commit();
            return ['success' => true];
        } catch (\PDOException $e) {
            $this->db->rollBack();
            return ['error' => 'Xóa thất bại: ' . $e->getMessage()];
        }
    }

    // ==========================================
    // --- SCHEDULE LOGIC -----------------------
    // ==========================================
    public function getAllSchedulesByStudent($studentId) {
        // Get all classes this student is enrolled & paid in, then return ALL schedules for those classes
        $stmt = $this->db->prepare("
            SELECT s.*,
                   cl.class_code,
                   cl.start_date  AS class_start_date,
                   cl.end_date    AS class_end_date,
                   co.course_name,
                   co.duration_weeks AS num_sessions
            FROM schedules s
            JOIN classes cl  ON s.class_id  = cl.id
            JOIN courses co  ON cl.course_id = co.id
            WHERE (
                (s.schedule_type = 'REGULAR' AND s.student_id = :sid1)
                OR
                (s.schedule_type IN ('EXAM','MAKEUP') AND s.class_id IN (
                    SELECT class_id FROM schedules WHERE student_id = :sid2 AND schedule_type = 'REGULAR'
                ))
            )
            ORDER BY s.class_id ASC, s.day_of_week ASC, s.start_time ASC
        ");
        $stmt->execute(['sid1' => $studentId, 'sid2' => $studentId]);
        return $stmt->fetchAll();
    }

    public function getSchedulesByClass($classId, $studentId = null) {
        $params = ['class_id' => $classId];
        if ($studentId) {
            // REGULAR sessions: only for this student; EXAM/MAKEUP: show all (no student filter)
            $where = "s.class_id = :class_id AND (s.schedule_type != 'REGULAR' OR s.student_id = :student_id)";
            $params['student_id'] = $studentId;
        } else {
            $where = 's.class_id = :class_id';
        }
        $stmt = $this->db->prepare("
            SELECT s.*,
                   cl.class_code,
                   cl.start_date  AS class_start_date,
                   cl.end_date    AS class_end_date,
                   co.course_name,
                   co.duration_weeks AS num_sessions,
                   t.teacher_code,
                   u.full_name  AS teacher_name,
                   su.full_name AS sv_full_name,
                   st.student_code AS sv_code
            FROM schedules s
            JOIN classes cl  ON s.class_id  = cl.id
            JOIN courses co  ON cl.course_id = co.id
            LEFT JOIN teachers t  ON cl.teacher_id = t.id
            LEFT JOIN users    u  ON t.user_id      = u.id
            LEFT JOIN students st ON s.student_id   = st.id
            LEFT JOIN users    su ON st.user_id      = su.id
            WHERE $where
            ORDER BY s.day_of_week ASC, s.start_time ASC, su.full_name ASC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function checkRoomConflict($classroomId, $semesterId, $dayOfWeek, $startTime, $endTime) {
        $sql = "
            SELECT s.*, c.class_code
            FROM schedules s
            JOIN classes c ON s.class_id = c.id
            WHERE c.classroom_id = :classroom_id 
              AND c.semester_id = :semester_id
              AND s.day_of_week = :day_of_week
              AND (
                  (s.start_time < :end_time AND s.end_time > :start_time)
              )
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'classroom_id' => $classroomId,
            'semester_id' => $semesterId,
            'day_of_week' => $dayOfWeek,
            'start_time' => $startTime,
            'end_time' => $endTime
        ]);
        return $stmt->fetchAll();
    }

    /** Trả về Y-m-d nếu có ngày cụ thể hợp lệ, ngược lại null. */
    private function normalizeScheduleSpecificDate(array $data): ?string {
        $raw = trim((string)($data['specific_date'] ?? ''));
        if ($raw === '' || !$this->isValidDateString($raw)) {
            return null;
        }
        return $raw;
    }

    private function validateScheduleData(array $data, ?int $classId = null) {
        $classId = $classId ?: (int)($data['class_id'] ?? 0);
        if (!$classId) {
            return ['error' => 'Thiếu lớp học'];
        }

        $classStmt = $this->db->prepare('SELECT id FROM classes WHERE id = :id');
        $classStmt->execute(['id' => $classId]);
        if (!$classStmt->fetch()) {
            return ['error' => 'Lớp học không tồn tại'];
        }

        if (!isset($data['day_of_week']) || $data['day_of_week'] === '') {
            return ['error' => 'Thiếu thứ trong tuần'];
        }
        $dow = (int)$data['day_of_week'];
        if ($dow < 0 || $dow > 6) {
            return ['error' => 'Thứ trong tuần không hợp lệ (0-6)'];
        }

        $startErr = $this->validateCourseTimeField($data['start_time'] ?? '', 'Giờ bắt đầu');
        if ($startErr) {
            return $startErr;
        }
        $endErr = $this->validateCourseTimeField($data['end_time'] ?? '', 'Giờ kết thúc');
        if ($endErr) {
            return $endErr;
        }
        $start = $this->normalizeTimeValue($data['start_time']);
        $end = $this->normalizeTimeValue($data['end_time']);
        if ($start >= $end) {
            return ['error' => 'Giờ kết thúc phải sau giờ bắt đầu'];
        }

        $schedType = $data['schedule_type'] ?? 'REGULAR';
        if (!in_array($schedType, ['REGULAR', 'EXAM', 'MAKEUP', 'EXTRA'], true)) {
            return ['error' => 'Loại lịch không hợp lệ'];
        }

        $studentId = isset($data['student_id']) && $data['student_id'] !== ''
            ? (int)$data['student_id']
            : null;

        if ($schedType === 'REGULAR' && !$studentId) {
            return ['error' => 'Lịch REGULAR cần chọn học viên'];
        }

        if (in_array($schedType, ['EXAM', 'MAKEUP', 'EXTRA'], true)) {
            if (!$this->normalizeScheduleSpecificDate($data)) {
                return ['error' => 'Thi / Học bù cần ngày cụ thể hợp lệ (YYYY-MM-DD)'];
            }
        } elseif (!empty($data['specific_date']) && !$this->normalizeScheduleSpecificDate($data)) {
            return ['error' => 'Ngày cụ thể không hợp lệ (YYYY-MM-DD)'];
        }

        if ($studentId) {
            $stuStmt = $this->db->prepare('SELECT id FROM students WHERE id = :id');
            $stuStmt->execute(['id' => $studentId]);
            if (!$stuStmt->fetch()) {
                return ['error' => 'Học viên không tồn tại'];
            }
            $enrStmt = $this->db->prepare("
                SELECT id FROM enrollments
                WHERE student_id = :sid AND class_id = :cid AND payment_status = 'PAID'
            ");
            $enrStmt->execute(['sid' => $studentId, 'cid' => $classId]);
            if (!$enrStmt->fetch()) {
                return ['error' => 'Học viên chưa ghi danh / thanh toán lớp này'];
            }
        }

        $examLabel = trim($data['exam_label'] ?? '');
        if ($examLabel !== '' && mb_strlen($examLabel) > 100) {
            return ['error' => 'Nhãn thi tối đa 100 ký tự'];
        }

        return null;
    }

    private function validateAttendanceData(array $data) {
        if (empty($data['class_id']) || empty($data['student_id']) || empty($data['attendance_date'])) {
            return ['error' => 'Thiếu lớp, học viên hoặc ngày điểm danh'];
        }

        if (!$this->isValidDateString($data['attendance_date'])) {
            return ['error' => 'Ngày điểm danh không hợp lệ'];
        }

        $status = $data['attendance_status'] ?? 'PRESENT';
        if (!in_array($status, ['PRESENT', 'ABSENT', 'LATE', 'EXCUSED'], true)) {
            return ['error' => 'Trạng thái điểm danh không hợp lệ'];
        }

        $note = (string)($data['note'] ?? '');
        if (mb_strlen($note) > 500) {
            return ['error' => 'Ghi chú tối đa 500 ký tự'];
        }

        $classStmt = $this->db->prepare('SELECT id FROM classes WHERE id = :id');
        $classStmt->execute(['id' => (int)$data['class_id']]);
        if (!$classStmt->fetch()) {
            return ['error' => 'Lớp học không tồn tại'];
        }

        $stuStmt = $this->db->prepare('SELECT id FROM students WHERE id = :id');
        $stuStmt->execute(['id' => (int)$data['student_id']]);
        if (!$stuStmt->fetch()) {
            return ['error' => 'Học viên không tồn tại'];
        }

        return null;
    }

    public function createSchedule($data) {
        $validationError = $this->validateScheduleData($data);
        if ($validationError) {
            return $validationError;
        }

        $data['start_time'] = $this->normalizeTimeValue($data['start_time']);
        $data['end_time'] = $this->normalizeTimeValue($data['end_time']);

        $studentId       = isset($data['student_id']) && $data['student_id'] !== '' ? (int)$data['student_id'] : null;
        $schedTypeChk    = $data['schedule_type'] ?? 'REGULAR';
        $specificDateChk = $this->normalizeScheduleSpecificDate($data);
        if ($specificDateChk) {
            $data['day_of_week'] = (int)date('w', strtotime($specificDateChk));
        }

        if ($schedTypeChk === 'REGULAR') {
            if ($specificDateChk) {
                $dupSql    = "SELECT id FROM schedules WHERE class_id=:cid AND schedule_type='REGULAR' AND specific_date=:sdate";
                $dupParams = ['cid' => $data['class_id'], 'sdate' => $specificDateChk];
                if ($studentId) {
                    $dupSql .= ' AND student_id=:sid';
                    $dupParams['sid'] = $studentId;
                } else {
                    $dupSql .= ' AND student_id IS NULL';
                }
            } else {
                $dupSql    = "SELECT id FROM schedules WHERE class_id=:cid AND day_of_week=:dow AND schedule_type='REGULAR' AND specific_date IS NULL";
                $dupParams = ['cid' => $data['class_id'], 'dow' => $data['day_of_week']];
                if ($studentId) {
                    $dupSql .= ' AND student_id=:sid';
                    $dupParams['sid'] = $studentId;
                } else {
                    $dupSql .= ' AND student_id IS NULL';
                }
            }
            $dup = $this->db->prepare($dupSql . ' LIMIT 1');
            $dup->execute($dupParams);
            if ($existing = $dup->fetch()) {
                $this->db->prepare("
                    UPDATE schedules
                    SET start_time=:st, end_time=:et, day_of_week=:dow, specific_date=:sdate
                    WHERE id=:id
                ")->execute([
                    'st' => $data['start_time'],
                    'et' => $data['end_time'],
                    'dow' => $data['day_of_week'],
                    'sdate' => $specificDateChk,
                    'id' => $existing['id'],
                ]);
                return ['success' => true, 'id' => $existing['id'], 'updated' => true];
            }
        } else {
            // For EXAM/MAKEUP: match on type + specific_date (each dated event is unique)
            $dupSql    = "SELECT id FROM schedules WHERE class_id=:cid AND day_of_week=:dow AND start_time=:st AND end_time=:et AND schedule_type=:stype";
            $dupParams = ['cid'=>$data['class_id'],'dow'=>$data['day_of_week'],'st'=>$data['start_time'],'et'=>$data['end_time'],'stype'=>$schedTypeChk];
            if ($studentId) { $dupSql .= ' AND student_id=:sid'; $dupParams['sid'] = $studentId; }
            if ($specificDateChk) { $dupSql .= ' AND specific_date=:sdate'; $dupParams['sdate'] = $specificDateChk; }
            else                  { $dupSql .= ' AND specific_date IS NULL'; }
            $dup = $this->db->prepare($dupSql . ' LIMIT 1');
            $dup->execute($dupParams);
            if ($existing = $dup->fetch()) {
                return ['success' => true, 'id' => $existing['id']];
            }
        }

        $studentId      = isset($data['student_id']) && $data['student_id'] !== '' ? (int)$data['student_id'] : null;
        $examLabel      = $data['exam_label']      ?? null;
        $examSupervisor = $data['exam_supervisor']  ?? null;
        $schedType      = $data['schedule_type']   ?? 'REGULAR';
        $specificDate   = $specificDateChk;
        $stmt = $this->db->prepare("
            INSERT INTO schedules (class_id, student_id, day_of_week, specific_date, start_time, end_time, schedule_type, exam_label, exam_supervisor)
            VALUES (:class_id, :student_id, :day_of_week, :specific_date, :start_time, :end_time, :schedule_type, :exam_label, :exam_supervisor)
        ");

        $success = $stmt->execute([
            'class_id'        => $data['class_id'],
            'student_id'      => $studentId,
            'day_of_week'     => $data['day_of_week'],
            'specific_date'   => $specificDate,
            'start_time'      => $data['start_time'],
            'end_time'        => $data['end_time'],
            'schedule_type'   => $schedType,
            'exam_label'      => $examLabel,
            'exam_supervisor' => $examSupervisor,
        ]);

        if ($success) return ['success' => true, 'id' => $this->db->lastInsertId()];
        return ['error' => 'Failed to create schedule'];
    }

    public function getScheduleById($id) {
        $stmt = $this->db->prepare("
            SELECT s.*,
                   cl.class_code, co.course_name,
                   st.student_code AS sv_code, su.full_name AS student_name
            FROM schedules s
            JOIN classes cl ON s.class_id = cl.id
            JOIN courses co ON cl.course_id = co.id
            LEFT JOIN students st ON s.student_id = st.id
            LEFT JOIN users su ON st.user_id = su.id
            WHERE s.id = :id
        ");
        $stmt->execute(['id' => (int)$id]);
        return $stmt->fetch() ?: null;
    }

    public function updateSchedule($id, $data) {
        $current = $this->getScheduleById($id);
        if (!$current) {
            return ['error' => 'Không tìm thấy lịch'];
        }

        $classId = (int)$current['class_id'];
        $validationError = $this->validateScheduleData($data, $classId);
        if ($validationError) {
            return $validationError;
        }

        $studentId = isset($data['student_id']) && $data['student_id'] !== ''
            ? (int)$data['student_id']
            : null;
        $schedType = $data['schedule_type'] ?? $current['schedule_type'] ?? 'REGULAR';
        $specificDate = $this->normalizeScheduleSpecificDate($data);
        $dow = $specificDate
            ? (int)date('w', strtotime($specificDate))
            : (int)$data['day_of_week'];
        $startTime = $this->normalizeTimeValue($data['start_time']);
        $endTime = $this->normalizeTimeValue($data['end_time']);

        if ($schedType === 'REGULAR') {
            if ($specificDate) {
                $dup = $this->db->prepare("
                    SELECT id FROM schedules
                    WHERE class_id = :cid AND schedule_type = 'REGULAR' AND specific_date = :sdate
                      AND ((student_id IS NULL AND :sid IS NULL) OR student_id = :sid2)
                      AND id != :id
                    LIMIT 1
                ");
                $dup->execute([
                    'cid' => $classId,
                    'sdate' => $specificDate,
                    'sid' => $studentId,
                    'sid2' => $studentId,
                    'id' => (int)$id,
                ]);
            } else {
                $dup = $this->db->prepare("
                    SELECT id FROM schedules
                    WHERE class_id = :cid AND day_of_week = :dow AND schedule_type = 'REGULAR'
                      AND specific_date IS NULL
                      AND ((student_id IS NULL AND :sid IS NULL) OR student_id = :sid2)
                      AND id != :id
                    LIMIT 1
                ");
                $dup->execute([
                    'cid' => $classId,
                    'dow' => $dow,
                    'sid' => $studentId,
                    'sid2' => $studentId,
                    'id' => (int)$id,
                ]);
            }
        } else {
            $dup = $this->db->prepare("
                SELECT id FROM schedules
                WHERE class_id = :cid AND schedule_type = :stype AND specific_date = :sdate
                  AND id != :id
                  AND ((student_id IS NULL AND :sid IS NULL) OR student_id = :sid2)
                LIMIT 1
            ");
            $dup->execute([
                'cid' => $classId,
                'stype' => $schedType,
                'sdate' => $specificDate,
                'id' => (int)$id,
                'sid' => $studentId,
                'sid2' => $studentId,
            ]);
        }
        if ($dup->fetch()) {
            return ['error' => 'Đã có lịch trùng cho học viên / ngày này'];
        }

        $examLabel = $data['exam_label'] ?? $current['exam_label'] ?? null;
        $examSupervisor = $data['exam_supervisor'] ?? $current['exam_supervisor'] ?? null;

        $ok = $this->db->prepare("
            UPDATE schedules
            SET student_id = :student_id, day_of_week = :day_of_week, specific_date = :specific_date,
                start_time = :start_time, end_time = :end_time, schedule_type = :schedule_type,
                exam_label = :exam_label, exam_supervisor = :exam_supervisor
            WHERE id = :id
        ")->execute([
            'student_id'      => $studentId,
            'day_of_week'     => $dow,
            'specific_date'   => $specificDate,
            'start_time'      => $startTime,
            'end_time'        => $endTime,
            'schedule_type'   => $schedType,
            'exam_label'      => $examLabel,
            'exam_supervisor' => $examSupervisor,
            'id'              => (int)$id,
        ]);

        if (!$ok) {
            return ['error' => 'Không thể cập nhật lịch'];
        }

        return ['success' => true, 'id' => (int)$id, 'updated' => true, 'class_id' => $classId];
    }

    public function deleteSchedule($id) {
        $stmt = $this->db->prepare("SELECT id FROM schedules WHERE id=:id");
        $stmt->execute(['id' => $id]);
        if (!$stmt->fetch()) return ['error' => 'Schedule not found'];
        $ok = $this->db->prepare("DELETE FROM schedules WHERE id=:id")->execute(['id' => $id]);
        return $ok ? ['success' => true] : ['error' => 'Failed to delete schedule'];
    }

    // ==========================================
    // --- ENROLLMENT LOGIC ---------------------
    // ==========================================
    public function getAllEnrollments($classId = null) {
        // Simple reliable query: use enrollment.payment_status as source of truth
        // tuition_payment linked by student_id + earliest unpaid or most recent
        $sql = "
            SELECT e.*,
                   s.student_code, u.full_name,
                   c.class_code, co.course_name,
                   co.tuition_fee AS tuition_amount,
                   tp.id          AS tuition_id,
                   tp.payment_date,
                   tp.payment_method
            FROM enrollments e
            JOIN students s  ON e.student_id = s.id
            JOIN users u     ON s.user_id    = u.id
            JOIN classes c   ON e.class_id   = c.id
            JOIN courses co  ON c.course_id  = co.id
            LEFT JOIN tuition_payments tp ON tp.student_id = e.student_id AND tp.class_id = e.class_id
        ";
        if ($classId) {
            $sql .= " WHERE e.class_id = :class_id ORDER BY e.id ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['class_id' => $classId]);
        } else {
            $sql .= " ORDER BY e.id ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        }
        return $stmt->fetchAll();
    }

    public function getEnrollmentById($id) {
        $stmt = $this->db->prepare("
            SELECT e.*,
                   s.student_code, u.full_name,
                   c.class_code, co.course_name,
                   co.tuition_fee AS tuition_amount,
                   tp.id AS tuition_id, tp.payment_date, tp.payment_method
            FROM enrollments e
            JOIN students s ON e.student_id = s.id
            JOIN users u ON s.user_id = u.id
            JOIN classes c ON e.class_id = c.id
            JOIN courses co ON c.course_id = co.id
            LEFT JOIN tuition_payments tp ON tp.student_id = e.student_id AND tp.class_id = e.class_id
            WHERE e.id = :id
        ");
        $stmt->execute(['id' => (int)$id]);
        return $stmt->fetch() ?: null;
    }

    public function createEnrollment($data) {
        if (empty($data['student_id']) || empty($data['class_id'])) {
            return ['error' => 'Missing required fields'];
        }

        $studentId = $data['student_id'];
        $classId = $data['class_id'];

        $stmtStudent = $this->db->prepare("SELECT id FROM students WHERE id = :id");
        $stmtStudent->execute(['id' => $studentId]);
        if (!$stmtStudent->fetch()) return ['error' => 'Student not found'];

        $stmtClass = $this->db->prepare("
            SELECT c.max_students, c.course_id, co.tuition_fee,
                   (SELECT COUNT(*) FROM enrollments WHERE class_id = :cid AND status = 'ACTIVE') as current_enrolled
            FROM classes c
            JOIN courses co ON c.course_id = co.id
            WHERE c.id = :id
        ");
        $stmtClass->execute(['cid' => $classId, 'id' => $classId]);
        $classData = $stmtClass->fetch();

        if (!$classData) return ['error' => 'Class not found'];

        if ($classData['current_enrolled'] >= $classData['max_students']) {
            return ['error' => 'Class is already full'];
        }

        $stmtExist = $this->db->prepare("SELECT id FROM enrollments WHERE student_id = :sid AND class_id = :cid");
        $stmtExist->execute(['sid' => $studentId, 'cid' => $classId]);
        if ($stmtExist->fetch()) return ['error' => 'Student already enrolled in this class'];

        try {
            $this->db->beginTransaction();

            $paymentStatus = in_array($data['payment_status'] ?? '', ['UNPAID','PAID','REFUNDED']) ? $data['payment_status'] : 'UNPAID';
            $enrollStatus  = in_array($data['status'] ?? '', ['ACTIVE','DROPPED','COMPLETED']) ? $data['status'] : 'ACTIVE';

            $stmtEnroll = $this->db->prepare("
                INSERT INTO enrollments (student_id, class_id, enrollment_date, payment_status, status) 
                VALUES (:sid, :cid, CURRENT_DATE, :ps, :st)
            ");
            $stmtEnroll->execute(['sid' => $studentId, 'cid' => $classId, 'ps' => $paymentStatus, 'st' => $enrollStatus]);

            $tuitionPayStatus = ($paymentStatus === 'PAID') ? 'COMPLETED' : 'UNPAID';
            $stmtTuition = $this->db->prepare("
                INSERT INTO tuition_payments (student_id, class_id, amount, payment_date, payment_status)
                VALUES (:sid, :cid, :amount, CURRENT_DATE, :ps)
            ");
            $stmtTuition->execute([
                'sid'    => $studentId,
                'cid'    => $classId,
                'amount' => $classData['tuition_fee'],
                'ps'     => $tuitionPayStatus,
            ]);

            $this->db->commit();
            return ['success' => true, 'id' => (int)$this->db->lastInsertId()];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['error' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function updateEnrollment($id, $data) {
        $stmt = $this->db->prepare("SELECT * FROM enrollments WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $enroll = $stmt->fetch();
        if (!$enroll) {
            return ['error' => 'Không tìm thấy ghi danh'];
        }

        if (isset($data['payment_status']) && !in_array($data['payment_status'], ['UNPAID', 'PAID', 'REFUNDED'], true)) {
            return ['error' => 'Trạng thái thanh toán không hợp lệ'];
        }
        if (isset($data['status']) && !in_array($data['status'], ['ACTIVE', 'DROPPED', 'COMPLETED'], true)) {
            return ['error' => 'Trạng thái học không hợp lệ'];
        }

        // Kiểm tra trùng nếu đổi student hoặc class
        if (!empty($data['student_id']) && !empty($data['class_id'])) {
            $chk = $this->db->prepare("SELECT id FROM enrollments WHERE student_id=:sid AND class_id=:cid AND id!=:id");
            $chk->execute(['sid' => $data['student_id'], 'cid' => $data['class_id'], 'id' => $id]);
            if ($chk->fetch()) return ['error' => 'Student already enrolled in this class'];
        }

        $newPayStatus = $data['payment_status'] ?? $enroll['payment_status'];
        $studentId    = $data['student_id'] ?? $enroll['student_id'];

        $stmt = $this->db->prepare("
            UPDATE enrollments
            SET student_id=COALESCE(:student_id, student_id),
                class_id=COALESCE(:class_id, class_id),
                payment_status=:payment_status,
                status=:status
            WHERE id=:id
        ");
        $ok = $stmt->execute([
            'student_id'     => $data['student_id']  ?? null,
            'class_id'       => $data['class_id']    ?? null,
            'payment_status' => $newPayStatus,
            'status'         => $data['status']      ?? 'ACTIVE',
            'id'             => $id,
        ]);
        if (!$ok) return ['error' => 'Failed to update enrollment'];

        // Sync tuition_payments cho đúng bản ghi của lớp này
        if ($newPayStatus === 'PAID') {
            $tuitionStatus = 'COMPLETED';
        } elseif ($newPayStatus === 'REFUNDED') {
            $tuitionStatus = 'REFUNDED';
        } else {
            $tuitionStatus = 'UNPAID';
        }

        // Lấy tuition_fee VÀ class_id của lớp học trong enrollment này
        $feeStmt = $this->db->prepare("
            SELECT c.tuition_fee, e.class_id
            FROM enrollments e
            JOIN classes cl ON e.class_id = cl.id
            JOIN courses c  ON cl.course_id = c.id
            WHERE e.id = :eid
        ");
        $feeStmt->execute(['eid' => $id]);
        $feeRow     = $feeStmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $tuitionFee = $feeRow['tuition_fee'] ?? null;
        $classIdRef = $feeRow['class_id']    ?? null;

        if ($tuitionFee !== null) {
            // Tìm bản ghi tuition_payment khớp chính xác student + class_id
            $existStmt = $this->db->prepare("
                SELECT id FROM tuition_payments
                WHERE student_id = :sid AND class_id = :cid
                ORDER BY id ASC LIMIT 1
            ");
            $existStmt->execute(['sid' => $studentId, 'cid' => $classIdRef]);
            $existPay = $existStmt->fetch(PDO::FETCH_ASSOC);

            $payMethod = $data['payment_method'] ?? null;
            if ($existPay) {
                $this->db->prepare("
                    UPDATE tuition_payments
                    SET payment_status = :ts,
                        class_id       = COALESCE(class_id, :cid),
                        payment_method = COALESCE(:method, payment_method),
                        payment_date   = CASE
                            WHEN :ts2 = 'COMPLETED' AND payment_date IS NULL THEN CURDATE()
                            WHEN :ts3 != 'COMPLETED' THEN NULL
                            ELSE payment_date
                        END
                    WHERE id = :pid
                ")->execute(['ts' => $tuitionStatus, 'ts2' => $tuitionStatus, 'ts3' => $tuitionStatus, 'method' => $payMethod, 'cid' => $classIdRef, 'pid' => $existPay['id']]);
            } elseif ($tuitionStatus === 'COMPLETED') {
                // Chưa có bản ghi — tạo mới
                $this->db->prepare("
                    INSERT INTO tuition_payments (student_id, class_id, amount, payment_date, payment_method, payment_status)
                    VALUES (:sid, :cid, :amount, CURDATE(), :method, 'COMPLETED')
                ")->execute(['sid' => $studentId, 'cid' => $classIdRef, 'amount' => $tuitionFee, 'method' => $payMethod ?? 'CASH']);
            }
        }

        return ['success' => true];
    }

    public function deleteEnrollment($id) {
        $stmt = $this->db->prepare("SELECT student_id, class_id FROM enrollments WHERE id=:id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            return ['error' => 'Không tìm thấy ghi danh'];
        }

        $this->db->prepare("
            DELETE FROM tuition_payments WHERE student_id = :sid AND class_id = :cid
        ")->execute(['sid' => $row['student_id'], 'cid' => $row['class_id']]);

        $ok = $this->db->prepare("DELETE FROM enrollments WHERE id=:id")->execute(['id' => $id]);
        return $ok ? ['success' => true] : ['error' => 'Không thể xóa ghi danh'];
    }

    // ==========================================
    // --- ATTENDANCE LOGIC ---------------------
    // ==========================================
    public function getAttendanceByStudentAndClass($classId, $studentId) {
        $stmt = $this->db->prepare("
            SELECT * FROM attendance
            WHERE class_id = :cid AND student_id = :sid
            ORDER BY attendance_date ASC
        ");
        $stmt->execute(['cid' => $classId, 'sid' => $studentId]);
        return $stmt->fetchAll();
    }

    public function getAttendanceByClassAndDate($classId, $date) {
        $stmt = $this->db->prepare("
            SELECT a.*, s.student_code, u.full_name
            FROM attendance a
            JOIN students s ON a.student_id = s.id
            JOIN users u ON s.user_id = u.id
            WHERE a.class_id = :class_id AND a.attendance_date = :date
        ");
        $stmt->execute(['class_id' => $classId, 'date' => $date]);
        return $stmt->fetchAll();
    }

    public function markAttendance($data) {
        $validationError = $this->validateAttendanceData($data);
        if ($validationError) {
            return $validationError;
        }

        $classId        = $data['class_id'];
        $studentId      = $data['student_id'];
        $date           = $data['attendance_date'];
        $status         = $data['attendance_status'] ?? 'PRESENT';
        $note           = $data['note'] ?? '';
        $teacherPresent = isset($data['teacher_present']) ? (int)$data['teacher_present'] : 0;

        $stmtExist = $this->db->prepare("SELECT id FROM attendance WHERE class_id=:cid AND student_id=:sid AND attendance_date=:date");
        $stmtExist->execute(['cid' => $classId, 'sid' => $studentId, 'date' => $date]);
        $exist = $stmtExist->fetch();

        if ($exist) {
            $success = $this->db->prepare("
                UPDATE attendance
                SET attendance_status=:status, tinh_luong=:tl, note=:note
                WHERE id=:id
            ")->execute(['status' => $status, 'tl' => $teacherPresent, 'note' => $note, 'id' => $exist['id']]);
        } else {
            $success = $this->db->prepare("
                INSERT INTO attendance (class_id, student_id, attendance_date, attendance_status, tinh_luong, note)
                VALUES (:cid, :sid, :date, :status, :tl, :note)
            ")->execute(['cid' => $classId, 'sid' => $studentId, 'date' => $date, 'status' => $status, 'tl' => $teacherPresent, 'note' => $note]);
        }

        if ($success) return ['success' => true];
        return ['error' => 'Failed to mark attendance'];
    }

    public function deleteAttendance($id) {
        $stmt = $this->db->prepare("SELECT id FROM attendance WHERE id=:id");
        $stmt->execute(['id' => $id]);
        if (!$stmt->fetch()) return ['error' => 'Attendance record not found'];
        $ok = $this->db->prepare("DELETE FROM attendance WHERE id=:id")->execute(['id' => $id]);
        return $ok ? ['success' => true] : ['error' => 'Failed to delete attendance'];
    }

    // ==========================================
    // --- TUITION LOGIC ------------------------
    // ==========================================
    public function getAllTuitions($studentId = null) {
        $sql = "SELECT tp.*, s.student_code, u.full_name 
                FROM tuition_payments tp
                JOIN students s ON tp.student_id = s.id
                JOIN users u ON s.user_id = u.id";
        if ($studentId) {
            $sql .= " WHERE tp.student_id = :sid";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['sid' => $studentId]);
        } else {
            $sql .= " ORDER BY tp.id DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        }
        return $stmt->fetchAll();
    }

    public function payTuition($paymentId, $method) {
        $stmt = $this->db->prepare("SELECT id, payment_status FROM tuition_payments WHERE id = :id");
        $stmt->execute(['id' => $paymentId]);
        $payment = $stmt->fetch();

        if (!$payment) return ['error' => 'Tuition payment not found'];
        if ($payment['payment_status'] === 'COMPLETED') return ['error' => 'Tuition is already paid'];

        $stmtUpdate = $this->db->prepare("
            UPDATE tuition_payments 
            SET payment_status = 'COMPLETED', payment_method = :method, payment_date = CURRENT_DATE
            WHERE id = :id
        ");
        $success = $stmtUpdate->execute(['method' => $method, 'id' => $paymentId]);

        if ($success) return ['success' => true];
        return ['error' => 'Failed to process payment'];
    }

    // ==========================================
    // --- REPORTING & ANALYTICS LOGIC ----------
    // ==========================================
    public function getRevenueByMonth() {
        // Group revenue by class START month (not payment date)
        // Only count payments that have a matching PAID enrollment
        $stmt = $this->db->prepare("
            SELECT
                DATE_FORMAT(cl.start_date, '%Y-%m') AS month,
                SUM(tp.amount) AS total_revenue
            FROM tuition_payments tp
            JOIN classes cl     ON tp.class_id   = cl.id
            JOIN enrollments e  ON e.student_id  = tp.student_id
                                AND e.class_id   = tp.class_id
                                AND e.payment_status IN ('PAID','COMPLETED')
            WHERE tp.payment_status = 'COMPLETED'
            GROUP BY month
            ORDER BY month DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getStudentCount() {
        $stmt = $this->db->prepare("
            SELECT status, COUNT(*) as count 
            FROM students 
            GROUP BY status
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getClassFillRate() {
        $stmt = $this->db->prepare("
            SELECT c.class_code, c.max_students, 
                   (SELECT COUNT(*) FROM enrollments WHERE class_id = c.id AND status = 'ACTIVE') as current_students
            FROM classes c
            WHERE c.status != 'COMPLETED'
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
