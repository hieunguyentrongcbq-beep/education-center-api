<?php
/**
 * Migration — EduCenter Database Schema + Seed + ALTER sync
 */
$config = require __DIR__ . '/project_root/config/database.php';
$port = $config['port'] ?? '3306';

function addColumnIfMissing(PDO $pdo, string $table, string $column, string $definition): void {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    $stmt->execute([$table, $column]);
    if ((int)$stmt->fetchColumn() === 0) {
        $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
        echo "  ALTER $table.$column OK\n";
    }
}

try {
    $dsn = "mysql:host={$config['host']};port={$port};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "=== EduCenter Migration ===\n\n";

    $tables = [];

    $tables[] = "CREATE TABLE IF NOT EXISTS users (
        id            INT AUTO_INCREMENT PRIMARY KEY,
        full_name     VARCHAR(150) NOT NULL,
        email         VARCHAR(150) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        phone         VARCHAR(20),
        status        ENUM('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
        created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $tables[] = "CREATE TABLE IF NOT EXISTS roles (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        role_name   VARCHAR(60) UNIQUE NOT NULL,
        description TEXT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $tables[] = "CREATE TABLE IF NOT EXISTS user_roles (
        user_id INT NOT NULL,
        role_id INT NOT NULL,
        PRIMARY KEY (user_id, role_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $tables[] = "CREATE TABLE IF NOT EXISTS teachers (
        id             INT AUTO_INCREMENT PRIMARY KEY,
        user_id        INT NOT NULL,
        teacher_code   VARCHAR(30) UNIQUE NOT NULL,
        specialization VARCHAR(200) NOT NULL,
        hire_date      DATE,
        teacher_type   ENUM('FULL_TIME','VISITING') DEFAULT 'FULL_TIME',
        standard_hours DECIMAL(5,1) DEFAULT 40,
        status         ENUM('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
        FOREIGN KEY (user_id) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $tables[] = "CREATE TABLE IF NOT EXISTS students (
        id            INT AUTO_INCREMENT PRIMARY KEY,
        user_id       INT NOT NULL,
        student_code  VARCHAR(30) UNIQUE NOT NULL,
        date_of_birth DATE,
        parent_phone  VARCHAR(20),
        status        ENUM('ACTIVE','INACTIVE','GRADUATED') DEFAULT 'ACTIVE',
        FOREIGN KEY (user_id) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $tables[] = "CREATE TABLE IF NOT EXISTS courses (
        id                INT AUTO_INCREMENT PRIMARY KEY,
        course_code       VARCHAR(30) UNIQUE NOT NULL,
        course_name       VARCHAR(200) NOT NULL,
        description       TEXT,
        duration_weeks    INT NOT NULL DEFAULT 10,
        total_sessions    INT NOT NULL DEFAULT 20,
        day_primary       TINYINT NOT NULL DEFAULT 1,
        day_secondary     TINYINT NOT NULL DEFAULT 4,
        default_start_time TIME DEFAULT '18:00:00',
        default_end_time   TIME DEFAULT '20:00:00',
        tuition_fee       DECIMAL(12,2) NOT NULL,
        status            ENUM('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
        created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $tables[] = "CREATE TABLE IF NOT EXISTS classrooms (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        room_name  VARCHAR(50) UNIQUE NOT NULL,
        capacity   INT NOT NULL,
        location   VARCHAR(200),
        status     ENUM('ACTIVE','INACTIVE') DEFAULT 'ACTIVE'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $tables[] = "CREATE TABLE IF NOT EXISTS semesters (
        id            INT AUTO_INCREMENT PRIMARY KEY,
        semester_name VARCHAR(100) NOT NULL,
        start_date    DATE NOT NULL,
        end_date      DATE NOT NULL,
        status        ENUM('UPCOMING','ONGOING','COMPLETED') DEFAULT 'UPCOMING'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $tables[] = "CREATE TABLE IF NOT EXISTS class_plans (
        id                   INT AUTO_INCREMENT PRIMARY KEY,
        course_id            INT NOT NULL,
        semester_id          INT NOT NULL,
        planned_class_count  INT DEFAULT 1,
        target_student_count INT DEFAULT 20,
        status               ENUM('DRAFT','APPROVED','CANCELLED') DEFAULT 'DRAFT',
        created_by           INT,
        FOREIGN KEY (course_id)  REFERENCES courses(id),
        FOREIGN KEY (semester_id) REFERENCES semesters(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $tables[] = "CREATE TABLE IF NOT EXISTS classes (
        id           INT AUTO_INCREMENT PRIMARY KEY,
        class_code   VARCHAR(40) UNIQUE NOT NULL,
        course_id    INT NOT NULL,
        teacher_id   INT,
        classroom_id INT,
        semester_id  INT,
        max_students INT NOT NULL DEFAULT 30,
        start_date   DATE NOT NULL,
        end_date     DATE NOT NULL,
        status       ENUM('UPCOMING','ONGOING','COMPLETED','CANCELLED') DEFAULT 'UPCOMING',
        created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (course_id)    REFERENCES courses(id),
        FOREIGN KEY (teacher_id)   REFERENCES teachers(id),
        FOREIGN KEY (classroom_id) REFERENCES classrooms(id),
        FOREIGN KEY (semester_id)  REFERENCES semesters(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $tables[] = "CREATE TABLE IF NOT EXISTS schedules (
        id              INT AUTO_INCREMENT PRIMARY KEY,
        class_id        INT NOT NULL,
        student_id      INT NULL,
        day_of_week     TINYINT NOT NULL,
        specific_date   DATE NULL,
        start_time      TIME NOT NULL,
        end_time        TIME NOT NULL,
        schedule_type   ENUM('REGULAR','EXAM','MAKEUP','EXTRA') DEFAULT 'REGULAR',
        exam_label      VARCHAR(100) NULL,
        exam_supervisor VARCHAR(100) NULL,
        FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $tables[] = "CREATE TABLE IF NOT EXISTS enrollments (
        id              INT AUTO_INCREMENT PRIMARY KEY,
        student_id      INT NOT NULL,
        class_id        INT NOT NULL,
        enrollment_date DATE NOT NULL,
        payment_status  ENUM('UNPAID','PAID','REFUNDED') DEFAULT 'UNPAID',
        status          ENUM('ACTIVE','DROPPED','COMPLETED') DEFAULT 'ACTIVE',
        UNIQUE KEY uq_enroll (student_id, class_id),
        FOREIGN KEY (student_id) REFERENCES students(id),
        FOREIGN KEY (class_id)   REFERENCES classes(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $tables[] = "CREATE TABLE IF NOT EXISTS attendance (
        id                INT AUTO_INCREMENT PRIMARY KEY,
        class_id          INT NOT NULL,
        student_id        INT NOT NULL,
        attendance_date   DATE NOT NULL,
        attendance_status ENUM('PRESENT','ABSENT','LATE','EXCUSED') DEFAULT 'PRESENT',
        tinh_luong        TINYINT(1) DEFAULT 0,
        note              VARCHAR(300),
        FOREIGN KEY (class_id)   REFERENCES classes(id),
        FOREIGN KEY (student_id) REFERENCES students(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $tables[] = "CREATE TABLE IF NOT EXISTS teacher_assignments (
        id                INT AUTO_INCREMENT PRIMARY KEY,
        teacher_id        INT NOT NULL,
        class_id          INT NOT NULL,
        student_id        INT NULL,
        day_of_week       TINYINT NULL,
        scenario_name     VARCHAR(60) DEFAULT 'FINAL',
        assigned_by       INT,
        assigned_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        assignment_status ENUM('PENDING','CONFIRMED','CANCELLED') DEFAULT 'CONFIRMED',
        FOREIGN KEY (teacher_id) REFERENCES teachers(id),
        FOREIGN KEY (class_id)   REFERENCES classes(id),
        FOREIGN KEY (student_id) REFERENCES students(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $tables[] = "CREATE TABLE IF NOT EXISTS payrolls (
        id             INT AUTO_INCREMENT PRIMARY KEY,
        teacher_id     INT NOT NULL,
        month          VARCHAR(7) NOT NULL,
        teaching_hours DECIMAL(6,1) DEFAULT 0,
        salary_amount  DECIMAL(12,2) DEFAULT 0,
        payment_status ENUM('PENDING','PAID') DEFAULT 'PENDING',
        FOREIGN KEY (teacher_id) REFERENCES teachers(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $tables[] = "CREATE TABLE IF NOT EXISTS tuition_payments (
        id             INT AUTO_INCREMENT PRIMARY KEY,
        student_id     INT NOT NULL,
        class_id       INT NULL,
        amount         DECIMAL(12,2) NOT NULL,
        payment_date   DATE,
        payment_method VARCHAR(40),
        payment_status ENUM('UNPAID','COMPLETED','REFUNDED') DEFAULT 'UNPAID',
        created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id),
        FOREIGN KEY (class_id) REFERENCES classes(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $tables[] = "CREATE TABLE IF NOT EXISTS notifications (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        title       VARCHAR(200) NOT NULL,
        content     TEXT,
        receiver_id INT NOT NULL,
        is_read     TINYINT(1) DEFAULT 0,
        created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (receiver_id) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $tables[] = "CREATE TABLE IF NOT EXISTS audit_logs (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        user_id     INT,
        action      VARCHAR(60),
        entity_name VARCHAR(60),
        entity_id   INT,
        created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    // Phase 2 scaffold tables
    $tables[] = "CREATE TABLE IF NOT EXISTS submissions (
        id           INT AUTO_INCREMENT PRIMARY KEY,
        student_id   INT NOT NULL,
        class_id     INT NOT NULL,
        type         ENUM('ASSIGNMENT','MIDTERM','FINAL') DEFAULT 'ASSIGNMENT',
        file_path    VARCHAR(500),
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status       ENUM('PENDING','GRADED') DEFAULT 'PENDING',
        FOREIGN KEY (student_id) REFERENCES students(id),
        FOREIGN KEY (class_id) REFERENCES classes(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $tables[] = "CREATE TABLE IF NOT EXISTS grades (
        id            INT AUTO_INCREMENT PRIMARY KEY,
        submission_id INT NOT NULL,
        teacher_id    INT NOT NULL,
        score         DECIMAL(5,2),
        comment       TEXT,
        graded_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (submission_id) REFERENCES submissions(id),
        FOREIGN KEY (teacher_id) REFERENCES teachers(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $tables[] = "CREATE TABLE IF NOT EXISTS leave_requests (
        id             INT AUTO_INCREMENT PRIMARY KEY,
        requester_type ENUM('TEACHER','STUDENT') NOT NULL,
        requester_id   INT NOT NULL,
        class_id       INT,
        request_date   DATE NOT NULL,
        reason         TEXT,
        status         ENUM('PENDING','APPROVED','REJECTED') DEFAULT 'PENDING',
        reviewed_by    INT,
        created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (class_id) REFERENCES classes(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $tables[] = "CREATE TABLE IF NOT EXISTS surveys (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        class_id   INT NOT NULL,
        title      VARCHAR(200) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (class_id) REFERENCES classes(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $tables[] = "CREATE TABLE IF NOT EXISTS survey_responses (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        survey_id  INT NOT NULL,
        student_id INT NOT NULL,
        teacher_id INT NOT NULL,
        rating     TINYINT NOT NULL,
        comment    TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (survey_id) REFERENCES surveys(id),
        FOREIGN KEY (student_id) REFERENCES students(id),
        FOREIGN KEY (teacher_id) REFERENCES teachers(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    foreach ($tables as $i => $sql) {
        $pdo->exec($sql);
        echo "  Table " . ($i + 1) . "/" . count($tables) . " OK\n";
    }

    echo "\n=== ALTER sync (existing DB) ===\n";
    addColumnIfMissing($pdo, 'courses', 'total_sessions', "INT NOT NULL DEFAULT 20 AFTER duration_weeks");
    addColumnIfMissing($pdo, 'courses', 'day_primary', "TINYINT NOT NULL DEFAULT 1 AFTER total_sessions");
    addColumnIfMissing($pdo, 'courses', 'day_secondary', "TINYINT NOT NULL DEFAULT 4 AFTER day_primary");
    addColumnIfMissing($pdo, 'courses', 'default_start_time', "TIME DEFAULT '18:00:00' AFTER day_secondary");
    addColumnIfMissing($pdo, 'courses', 'default_end_time', "TIME DEFAULT '20:00:00' AFTER default_start_time");
    addColumnIfMissing($pdo, 'schedules', 'student_id', "INT NULL AFTER class_id");
    addColumnIfMissing($pdo, 'schedules', 'specific_date', "DATE NULL AFTER day_of_week");
    addColumnIfMissing($pdo, 'schedules', 'exam_label', "VARCHAR(100) NULL AFTER schedule_type");
    addColumnIfMissing($pdo, 'schedules', 'exam_supervisor', "VARCHAR(100) NULL AFTER exam_label");
    addColumnIfMissing($pdo, 'teacher_assignments', 'student_id', "INT NULL AFTER class_id");
    addColumnIfMissing($pdo, 'teacher_assignments', 'day_of_week', "TINYINT NULL AFTER student_id");
    addColumnIfMissing($pdo, 'tuition_payments', 'class_id', "INT NULL AFTER student_id");
    addColumnIfMissing($pdo, 'attendance', 'tinh_luong', "TINYINT(1) DEFAULT 0 AFTER attendance_status");
    addColumnIfMissing($pdo, 'leave_requests', 'request_type', "ENUM('LEAVE','MAKEUP') DEFAULT 'LEAVE' AFTER requester_id");

    $pdo->exec("CREATE TABLE IF NOT EXISTS student_evaluations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        class_id INT NOT NULL,
        teacher_id INT,
        avg_score DECIMAL(5,2) DEFAULT 0,
        level ENUM('GIOI','KHA','TRUNG_BINH','KEM') DEFAULT 'TRUNG_BINH',
        retake_needed TINYINT(1) DEFAULT 0,
        teacher_comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uq_stu_class (student_id, class_id),
        FOREIGN KEY (student_id) REFERENCES students(id),
        FOREIGN KEY (class_id) REFERENCES classes(id),
        FOREIGN KEY (teacher_id) REFERENCES teachers(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "  student_evaluations OK\n";

    echo "\n=== Seeding dữ liệu mẫu ===\n";

    $pdo->exec("INSERT IGNORE INTO roles (role_name, description) VALUES
        ('ADMIN',   'Quản trị viên'),
        ('TEACHER', 'Giáo viên'),
        ('STUDENT', 'Học viên')");
    echo "Roles (3): OK\n";

    // Map old roles to new if exist
    $oldToNew = ['SUPER_ADMIN'=>'ADMIN','CENTER_MANAGER'=>'ADMIN','ACADEMIC_STAFF'=>'ADMIN','ACCOUNTANT'=>'ADMIN'];
    foreach ($oldToNew as $old => $new) {
        $oldId = $pdo->query("SELECT id FROM roles WHERE role_name='$old'")->fetchColumn();
        $newId = $pdo->query("SELECT id FROM roles WHERE role_name='$new'")->fetchColumn();
        if ($oldId && $newId) {
            $pdo->exec("UPDATE IGNORE user_roles SET role_id=$newId WHERE role_id=$oldId");
        }
    }

    $users = [
        ['Nguyễn Quản Trị',   'admin@edu.vn',      'admin123',   '0901000001'],
        ['Trần Minh Tuấn',    'tuan.gv@edu.vn',    'teacher123', '0902000001'],
        ['Lê Thị Hoa',        'hoa.gv@edu.vn',     'teacher123', '0902000002'],
        ['Phạm Văn An',       'an.hv@edu.vn',      'student123', '0903000001'],
        ['Nguyễn Thị Bình',   'binh.hv@edu.vn',    'student123', '0903000002'],
        ['Hoàng Minh Cường',  'cuong.hv@edu.vn',   'student123', '0903000003'],
    ];
    $userIds = [];
    foreach ($users as $u) {
        $ph = password_hash($u[2], PASSWORD_BCRYPT);
        $s = $pdo->prepare("INSERT IGNORE INTO users (full_name, email, password_hash, phone, status) VALUES (?,?,?,?,'ACTIVE')");
        $s->execute([$u[0], $u[1], $ph, $u[3]]);
        $userIds[$u[1]] = $pdo->lastInsertId() ?: $pdo->query("SELECT id FROM users WHERE email='{$u[1]}'")->fetchColumn();
    }
    echo "Users: OK\n";

    $roleIds = [];
    foreach ($pdo->query("SELECT id, role_name FROM roles")->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $roleIds[$r['role_name']] = $r['id'];
    }

    $roleMap = [
        'admin@edu.vn'    => 'ADMIN',
        'tuan.gv@edu.vn'  => 'TEACHER',
        'hoa.gv@edu.vn'   => 'TEACHER',
        'an.hv@edu.vn'    => 'STUDENT',
        'binh.hv@edu.vn'  => 'STUDENT',
        'cuong.hv@edu.vn' => 'STUDENT',
    ];
    foreach ($roleMap as $email => $roleName) {
        $uid = $userIds[$email];
        $rid = $roleIds[$roleName] ?? null;
        if ($uid && $rid) {
            $pdo->exec("INSERT IGNORE INTO user_roles (user_id, role_id) VALUES ($uid, $rid)");
        }
    }
    echo "User roles: OK\n";

    $pdo->exec("INSERT IGNORE INTO teachers (user_id, teacher_code, specialization, hire_date, teacher_type, standard_hours, status) VALUES
        ({$userIds['tuan.gv@edu.vn']}, 'GV001', 'Lập trình Web & JavaScript', '2023-01-15', 'FULL_TIME', 40, 'ACTIVE'),
        ({$userIds['hoa.gv@edu.vn']},  'GV002', 'Cơ sở dữ liệu & PHP Backend', '2023-03-01', 'FULL_TIME', 40, 'ACTIVE')");
    echo "Teachers: OK\n";

    $pdo->exec("INSERT IGNORE INTO students (user_id, student_code, date_of_birth, status) VALUES
        ({$userIds['an.hv@edu.vn']},    'HV001', '2002-05-10', 'ACTIVE'),
        ({$userIds['binh.hv@edu.vn']},  'HV002', '2001-11-20', 'ACTIVE'),
        ({$userIds['cuong.hv@edu.vn']}, 'HV003', '2000-07-03', 'ACTIVE')");
    echo "Students: OK\n";

    $pdo->exec("INSERT IGNORE INTO classrooms (room_name, capacity, location, status) VALUES
        ('P101', 30, 'Tầng 1', 'ACTIVE'),
        ('P201', 25, 'Tầng 2', 'ACTIVE'),
        ('P301', 20, 'Tầng 3', 'ACTIVE')");
    echo "Classrooms: OK\n";

    $pdo->exec("INSERT IGNORE INTO semesters (semester_name, start_date, end_date, status) VALUES
        ('Học kỳ 1 - 2026', '2026-01-01', '2026-06-30', 'ONGOING'),
        ('Học kỳ 2 - 2026', '2026-07-01', '2026-12-31', 'UPCOMING')");
    echo "Semesters: OK\n";

    $pdo->exec("INSERT IGNORE INTO courses (course_code, course_name, description, duration_weeks, total_sessions, day_primary, day_secondary, tuition_fee, status) VALUES
        ('JS101',  'Lập trình JavaScript',    'Khóa học JS từ cơ bản đến nâng cao', 10, 20, 1, 4, 3500000, 'ACTIVE'),
        ('PHP201', 'Lập trình PHP & MySQL',   'Backend development với PHP',         10, 20, 1, 4, 3000000, 'ACTIVE'),
        ('DB301',  'Cơ sở dữ liệu nâng cao', 'SQL, tối ưu truy vấn, thiết kế DB',   8,  16, 2, 5, 2500000, 'ACTIVE'),
        ('PY501',  'Lập trình Python',        'Python từ cơ bản đến nâng cao',      10, 20, 1, 4, 3200000, 'ACTIVE')");
    echo "Courses: OK\n";

    $semId  = $pdo->query("SELECT id FROM semesters WHERE semester_name LIKE '%Kỳ 2%' OR semester_name LIKE '%2%' LIMIT 1")->fetchColumn();
    if (!$semId) $semId = 1;
    $rmId   = $pdo->query("SELECT id FROM classrooms WHERE room_name='P101'")->fetchColumn();
    $rmId2  = $pdo->query("SELECT id FROM classrooms WHERE room_name='P201'")->fetchColumn();
    $tvId1  = $pdo->query("SELECT id FROM teachers WHERE teacher_code='GV001'")->fetchColumn();
    $tvId2  = $pdo->query("SELECT id FROM teachers WHERE teacher_code='GV002'")->fetchColumn();
    $crsPy  = $pdo->query("SELECT id FROM courses WHERE course_code='PY501'")->fetchColumn() ?: 1;
    $crsJs  = $pdo->query("SELECT id FROM courses WHERE course_code='JS101'")->fetchColumn() ?: 1;

    $pdo->exec("INSERT IGNORE INTO classes (class_code, course_id, teacher_id, classroom_id, semester_id, max_students, start_date, end_date, status) VALUES
        ('PY501-A', $crsPy, $tvId1, $rmId, $semId, 25, '2026-07-01', '2026-09-09', 'UPCOMING'),
        ('JS101-A', $crsJs, $tvId2, $rmId2, $semId, 25, '2026-07-01', '2026-09-09', 'UPCOMING')");
    echo "Classes: OK\n";

    // Demo: thanh toán chờ duyệt (enrollment UNPAID + tuition_payments UNPAID)
    $hv2 = $pdo->query("SELECT id FROM students WHERE student_code='HV002'")->fetchColumn();
    $hv3 = $pdo->query("SELECT id FROM students WHERE student_code='HV003'")->fetchColumn();
    $classPy = $pdo->query("SELECT id FROM classes WHERE class_code='PY501-A'")->fetchColumn();
    $classJs = $pdo->query("SELECT id FROM classes WHERE class_code='JS101-A'")->fetchColumn();
    $feePy = $pdo->query("SELECT tuition_fee FROM courses WHERE id=" . (int)$crsPy)->fetchColumn();
    $feeJs = $pdo->query("SELECT tuition_fee FROM courses WHERE id=" . (int)$crsJs)->fetchColumn();

    $pendingSeeds = [
        ['student_id' => $hv2, 'class_id' => $classPy, 'amount' => $feePy, 'label' => 'Bình → PY501-A'],
        ['student_id' => $hv3, 'class_id' => $classJs, 'amount' => $feeJs, 'label' => 'Cường → JS101-A'],
    ];
    $pendingCount = 0;
    foreach ($pendingSeeds as $seed) {
        if (!$seed['student_id'] || !$seed['class_id']) continue;
        $sid = (int)$seed['student_id'];
        $cid = (int)$seed['class_id'];
        $amt = (float)$seed['amount'];

        $exists = $pdo->query("SELECT id FROM enrollments WHERE student_id=$sid AND class_id=$cid")->fetchColumn();
        if (!$exists) {
            $pdo->exec("
                INSERT INTO enrollments (student_id, class_id, enrollment_date, payment_status, status)
                VALUES ($sid, $cid, CURDATE(), 'UNPAID', 'ACTIVE')
            ");
        } else {
            $pdo->exec("
                UPDATE enrollments SET payment_status='UNPAID', status='ACTIVE'
                WHERE student_id=$sid AND class_id=$cid AND payment_status != 'PAID'
            ");
        }

        $tpExists = $pdo->query("
            SELECT id FROM tuition_payments
            WHERE student_id=$sid AND class_id=$cid AND payment_status='UNPAID' LIMIT 1
        ")->fetchColumn();
        if (!$tpExists) {
            $pdo->exec("
                INSERT INTO tuition_payments (student_id, class_id, amount, payment_date, payment_status)
                VALUES ($sid, $cid, $amt, CURDATE(), 'UNPAID')
            ");
        }
        $pendingCount++;
    }
    echo "Pending payments demo: $pendingCount enrollment(s) UNPAID (Admin → Thanh toán)\n";

    // Demo: yêu cầu học bù chờ duyệt (GV → Admin duyệt → tự tạo lịch MAKEUP)
    if ($tvId1 && $classPy) {
        $makeupDate = date('Y-m-d', strtotime('+7 days'));
        $pendingLeave = $pdo->query("
            SELECT id FROM leave_requests
            WHERE requester_type='TEACHER' AND requester_id=" . (int)$tvId1 . "
              AND request_type='MAKEUP' AND status='PENDING' LIMIT 1
        ")->fetchColumn();
        if (!$pendingLeave) {
            $reason = 'Demo: xin học bù buổi vắng do công tác';
            $pdo->exec("
                INSERT INTO leave_requests (requester_type, requester_id, class_id, request_date, reason, request_type, status)
                VALUES ('TEACHER', " . (int)$tvId1 . ", " . (int)$classPy . ", '$makeupDate', '$reason', 'MAKEUP', 'PENDING')
            ");
            echo "Leave/makeup demo: 1 yêu cầu MAKEUP PENDING (Admin → Duyệt nghỉ / Học bù)\n";
        }
    }

    echo "\n========================================\n";
    echo "Migration hoàn thành!\n";
    echo "Portal: http://localhost/educationcenterapi/project_root/\n";
    echo "Login:  admin@edu.vn / admin123\n";
    echo "        tuan.gv@edu.vn / teacher123\n";
    echo "        an.hv@edu.vn / student123\n";
    echo "Demo chờ thanh toán: binh.hv@edu.vn (PY501-A), cuong.hv@edu.vn (JS101-A)\n";
    echo "Demo học bù: Admin → Duyệt nghỉ/Học bù → duyệt MAKEUP của GV Tuấn\n";
    echo "========================================\n";

} catch (Exception $e) {
    echo "LỖI: " . $e->getMessage() . "\n";
}
