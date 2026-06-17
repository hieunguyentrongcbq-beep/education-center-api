<?php
/**
 * SEED FAKE DATA — EduCenter Full Test Data
 * Phủ đủ mọi tính năng: lịch học, lịch dạy, thanh toán, điểm danh,
 * bảng lương, khảo sát, bài nộp, nghỉ phép / học bù, thông báo.
 *
 * Chạy: php seed_fake.php
 */

$config = require __DIR__ . '/project_root/config/database.php';
$port   = $config['port'] ?? '3306';

/* ───── helper ─────────────────────────────────────────────────── */
function generateSessionDates(string $start, string $end, array $weekdays): array
{
    $dates = [];
    $d     = new DateTime($start);
    $endDt = new DateTime($end);
    while ($d <= $endDt) {
        if (in_array((int)$d->format('N'), $weekdays, true)) {   // N: 1=Mon … 7=Sun
            $dates[] = $d->format('Y-m-d');
        }
        $d->modify('+1 day');
    }
    return $dates;
}

function rowExists(PDO $pdo, string $table, array $where): bool
{
    $clauses = [];
    $vals    = [];
    foreach ($where as $col => $val) {
        if ($val === null) {
            $clauses[] = "`$col` IS NULL";
        } else {
            $clauses[] = "`$col` = ?";
            $vals[]    = $val;
        }
    }
    $sql  = "SELECT COUNT(*) FROM `$table` WHERE " . implode(' AND ', $clauses);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($vals);
    return (int)$stmt->fetchColumn() > 0;
}

/* ───── main ────────────────────────────────────────────────────── */
try {
    $dsn = "mysql:host={$config['host']};port={$port};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    echo "=== SEED FAKE DATA ===\n";
    echo "Ngày chạy: " . date('Y-m-d H:i:s') . "\n\n";

    // ══════════════════════════════════════════════════
    // 1. ROLES (idempotent)
    // ══════════════════════════════════════════════════
    $pdo->exec("INSERT IGNORE INTO roles (role_name, description) VALUES
        ('ADMIN',   'Quản trị viên'),
        ('TEACHER', 'Giáo viên'),
        ('STUDENT', 'Học viên')");

    $roleIds = [];
    foreach ($pdo->query("SELECT id, role_name FROM roles")->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $roleIds[$r['role_name']] = (int)$r['id'];
    }
    echo "[1] Roles: OK (ADMIN={$roleIds['ADMIN']}, TEACHER={$roleIds['TEACHER']}, STUDENT={$roleIds['STUDENT']})\n";

    // ══════════════════════════════════════════════════
    // 2. USERS
    // ══════════════════════════════════════════════════
    $usersRaw = [
        //  full_name                   email                    pass          phone          role
        ['Nguyễn Quản Trị',       'admin@edu.vn',          'admin123',   '0901000001', 'ADMIN'],
        ['Trần Minh Tuấn',         'tuan.gv@edu.vn',        'teacher123', '0902000001', 'TEACHER'],
        ['Lê Thị Hoa',             'hoa.gv@edu.vn',         'teacher123', '0902000002', 'TEACHER'],
        ['Nguyễn Minh Đức',        'duc.gv@edu.vn',         'teacher123', '0902000003', 'TEACHER'],
        ['Phạm Văn An',            'an.hv@edu.vn',          'student123', '0903000001', 'STUDENT'],
        ['Nguyễn Thị Bình',        'binh.hv@edu.vn',        'student123', '0903000002', 'STUDENT'],
        ['Hoàng Minh Cường',       'cuong.hv@edu.vn',       'student123', '0903000003', 'STUDENT'],
        ['Trần Thị Mai',           'mai.hv@edu.vn',         'student123', '0903000004', 'STUDENT'],
        ['Lê Văn Khoa',            'khoa.hv@edu.vn',        'student123', '0903000005', 'STUDENT'],
        ['Phạm Thị Linh',          'linh.hv@edu.vn',        'student123', '0903000006', 'STUDENT'],
        ['Võ Văn Hùng',            'hung.hv@edu.vn',        'student123', '0903000007', 'STUDENT'],
        ['Đặng Thị Thu',           'thu.hv@edu.vn',         'student123', '0903000008', 'STUDENT'],
    ];

    $uids = [];   // email → id
    foreach ($usersRaw as $u) {
        $stmt = $pdo->prepare(
            "INSERT IGNORE INTO users (full_name, email, password_hash, phone, status)
             VALUES (?, ?, ?, ?, 'ACTIVE')"
        );
        $stmt->execute([$u[0], $u[1], password_hash($u[2], PASSWORD_BCRYPT), $u[3]]);
        $id = (int)($pdo->lastInsertId() ?: $pdo->query("SELECT id FROM users WHERE email=" . $pdo->quote($u[1]))->fetchColumn());
        $uids[$u[1]] = $id;
        // user_roles
        $rid = $roleIds[$u[4]] ?? null;
        if ($id && $rid) {
            $pdo->exec("INSERT IGNORE INTO user_roles (user_id, role_id) VALUES ($id, $rid)");
        }
    }
    echo "[2] Users (" . count($uids) . "): OK\n";

    // ══════════════════════════════════════════════════
    // 3. TEACHERS
    // ══════════════════════════════════════════════════
    $teachersRaw = [
        ['tuan.gv@edu.vn', 'GV001', 'Lập trình Web & JavaScript',      '2023-01-15', 'FULL_TIME', 40],
        ['hoa.gv@edu.vn',  'GV002', 'Cơ sở dữ liệu & PHP Backend',     '2023-03-01', 'FULL_TIME', 40],
        ['duc.gv@edu.vn',  'GV003', 'Lập trình Python & Data Science',  '2024-01-10', 'VISITING',  20],
    ];
    foreach ($teachersRaw as $t) {
        $uid = $uids[$t[0]];
        $pdo->exec("INSERT IGNORE INTO teachers (user_id, teacher_code, specialization, hire_date, teacher_type, standard_hours, status)
            VALUES ($uid, '{$t[1]}', " . $pdo->quote($t[2]) . ", '{$t[3]}', '{$t[4]}', {$t[5]}, 'ACTIVE')");
    }
    $tvIds = [];
    foreach ($pdo->query("SELECT id, teacher_code FROM teachers")->fetchAll(PDO::FETCH_ASSOC) as $t) {
        $tvIds[$t['teacher_code']] = (int)$t['id'];
    }
    $tv1 = $tvIds['GV001']; $tv2 = $tvIds['GV002']; $tv3 = $tvIds['GV003'];
    echo "[3] Teachers: GV001=$tv1, GV002=$tv2, GV003=$tv3\n";

    // ══════════════════════════════════════════════════
    // 4. STUDENTS
    // ══════════════════════════════════════════════════
    $studentsRaw = [
        ['an.hv@edu.vn',    'HV001', '2002-05-10', '0904000001'],
        ['binh.hv@edu.vn',  'HV002', '2001-11-20', '0904000002'],
        ['cuong.hv@edu.vn', 'HV003', '2000-07-03', '0904000003'],
        ['mai.hv@edu.vn',   'HV004', '2003-02-14', '0904000004'],
        ['khoa.hv@edu.vn',  'HV005', '2002-08-22', '0904000005'],
        ['linh.hv@edu.vn',  'HV006', '2001-12-05', '0904000006'],
        ['hung.hv@edu.vn',  'HV007', '2003-04-17', '0904000007'],
        ['thu.hv@edu.vn',   'HV008', '2002-09-30', '0904000008'],
    ];
    foreach ($studentsRaw as $s) {
        $uid = $uids[$s[0]];
        $pdo->exec("INSERT IGNORE INTO students (user_id, student_code, date_of_birth, parent_phone, status)
            VALUES ($uid, '{$s[1]}', '{$s[2]}', '{$s[3]}', 'ACTIVE')");
    }
    $svIds = [];
    foreach ($pdo->query("SELECT id, student_code FROM students")->fetchAll(PDO::FETCH_ASSOC) as $s) {
        $svIds[$s['student_code']] = (int)$s['id'];
    }
    echo "[4] Students (" . count($svIds) . "): " . implode(', ', array_map(fn($k,$v)=>"$k=$v", array_keys($svIds), $svIds)) . "\n";

    // ══════════════════════════════════════════════════
    // 5. CLASSROOMS
    // ══════════════════════════════════════════════════
    $pdo->exec("INSERT IGNORE INTO classrooms (room_name, capacity, location, status) VALUES
        ('P101', 30, 'Tầng 1',               'ACTIVE'),
        ('P201', 25, 'Tầng 2',               'ACTIVE'),
        ('P301', 20, 'Tầng 3',               'ACTIVE'),
        ('P401', 35, 'Tầng 4 – Lab máy tính','ACTIVE')");
    $rmIds = [];
    foreach ($pdo->query("SELECT id, room_name FROM classrooms")->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $rmIds[$r['room_name']] = (int)$r['id'];
    }
    echo "[5] Classrooms: " . implode(', ', array_map(fn($k,$v)=>"$k=$v", array_keys($rmIds), $rmIds)) . "\n";

    // ══════════════════════════════════════════════════
    // 6. SEMESTERS
    // ══════════════════════════════════════════════════
    $pdo->exec("INSERT IGNORE INTO semesters (semester_name, start_date, end_date, status) VALUES
        ('Học kỳ 1 – 2026', '2026-01-01', '2026-06-30', 'ONGOING'),
        ('Học kỳ 2 – 2026', '2026-07-01', '2026-12-31', 'UPCOMING')");
    $semIds = [];
    foreach ($pdo->query("SELECT id, semester_name FROM semesters")->fetchAll(PDO::FETCH_ASSOC) as $s) {
        $semIds[$s['semester_name']] = (int)$s['id'];
    }
    // fallback nếu tên khác do migrate cũ
    $sem1 = $pdo->query("SELECT id FROM semesters WHERE start_date='2026-01-01' LIMIT 1")->fetchColumn() ?: array_values($semIds)[0];
    $sem2 = $pdo->query("SELECT id FROM semesters WHERE start_date='2026-07-01' LIMIT 1")->fetchColumn() ?: array_values($semIds)[1];
    $sem1 = (int)$sem1; $sem2 = (int)$sem2;
    echo "[6] Semesters: sem1=$sem1, sem2=$sem2\n";

    // ══════════════════════════════════════════════════
    // 7. COURSES
    // ══════════════════════════════════════════════════
    $pdo->exec("INSERT IGNORE INTO courses
        (course_code, course_name, description, duration_weeks, total_sessions,
         day_primary, day_secondary, default_start_time, default_end_time, tuition_fee, status)
        VALUES
        ('JS101',  'Lập trình JavaScript',    'Khóa học JS từ cơ bản đến nâng cao', 10, 20, 1, 4, '18:00:00','20:00:00', 3500000, 'ACTIVE'),
        ('PHP201', 'Lập trình PHP & MySQL',   'Backend development với PHP',         10, 20, 1, 4, '18:00:00','20:00:00', 3000000, 'ACTIVE'),
        ('DB301',  'Cơ sở dữ liệu nâng cao', 'SQL, tối ưu truy vấn, thiết kế DB',   8,  16, 2, 5, '08:00:00','10:00:00', 2500000, 'ACTIVE'),
        ('PY501',  'Lập trình Python',        'Python từ cơ bản đến nâng cao',      10, 20, 1, 4, '18:00:00','20:00:00', 3200000, 'ACTIVE')");
    $crsIds = [];
    foreach ($pdo->query("SELECT id, course_code FROM courses")->fetchAll(PDO::FETCH_ASSOC) as $c) {
        $crsIds[$c['course_code']] = (int)$c['id'];
    }
    echo "[7] Courses: " . implode(', ', array_map(fn($k,$v)=>"$k=$v", array_keys($crsIds), $crsIds)) . "\n";

    // ══════════════════════════════════════════════════
    // 8. CLASS PLANS
    // ══════════════════════════════════════════════════
    $plans = [
        [$crsIds['JS101'],  $sem1, 2, 25, 'APPROVED'],
        [$crsIds['PHP201'], $sem1, 1, 20, 'APPROVED'],
        [$crsIds['DB301'],  $sem1, 1, 15, 'APPROVED'],
        [$crsIds['PY501'],  $sem2, 2, 25, 'APPROVED'],
        [$crsIds['JS101'],  $sem2, 2, 25, 'DRAFT'],
    ];
    $adminUid = $uids['admin@edu.vn'];
    foreach ($plans as $p) {
        if (!rowExists($pdo, 'class_plans', ['course_id' => $p[0], 'semester_id' => $p[1]])) {
            $pdo->exec("INSERT INTO class_plans (course_id, semester_id, planned_class_count, target_student_count, status, created_by)
                VALUES ({$p[0]}, {$p[1]}, {$p[2]}, {$p[3]}, '{$p[4]}', $adminUid)");
        }
    }
    echo "[8] Class plans: OK\n";

    // ══════════════════════════════════════════════════
    // 9. CLASSES
    //   Ongoing (sem1): JS101-B, PHP201-A, DB301-A
    //   Upcoming (sem2): PY501-A, JS101-A  (đã có trong migrate, thêm nếu chưa có)
    // ══════════════════════════════════════════════════
    $classesRaw = [
        // code        crs_code  tv_code  room   sem   maxStu  startDate     endDate       status
        ['JS101-B',  'JS101',  'GV001', 'P101', $sem1, 25, '2026-04-14', '2026-06-19', 'ONGOING'],
        ['PHP201-A', 'PHP201', 'GV002', 'P201', $sem1, 20, '2026-04-14', '2026-06-19', 'ONGOING'],
        ['DB301-A',  'DB301',  'GV001', 'P301', $sem1, 15, '2026-05-12', '2026-06-19', 'ONGOING'],
        ['PY501-A',  'PY501',  'GV001', 'P101', $sem2, 25, '2026-07-06', '2026-09-13', 'UPCOMING'],
        ['PY501-B',  'PY501',  'GV003', 'P401', $sem2, 30, '2026-07-06', '2026-09-13', 'UPCOMING'],
        ['JS101-A',  'JS101',  'GV002', 'P201', $sem2, 25, '2026-07-07', '2026-09-14', 'UPCOMING'],
        ['PHP201-B', 'PHP201', 'GV002', 'P301', $sem2, 20, '2026-07-07', '2026-09-14', 'UPCOMING'],
    ];
    foreach ($classesRaw as $cl) {
        [$code, $crsCode, $tvCode, $roomName, $semId, $maxS, $sdt, $edt, $st] = $cl;
        $cid = $crsIds[$crsCode];
        $tid = $tvIds[$tvCode];
        $rid = $rmIds[$roomName];
        $pdo->exec("INSERT IGNORE INTO classes
            (class_code, course_id, teacher_id, classroom_id, semester_id, max_students, start_date, end_date, status)
            VALUES ('$code', $cid, $tid, $rid, $semId, $maxS, '$sdt', '$edt', '$st')");
    }
    $clIds = [];
    foreach ($pdo->query("SELECT id, class_code FROM classes")->fetchAll(PDO::FETCH_ASSOC) as $c) {
        $clIds[$c['class_code']] = (int)$c['id'];
    }
    echo "[9] Classes (" . count($clIds) . "): " . implode(', ', array_map(fn($k,$v)=>"$k=$v", array_keys($clIds), $clIds)) . "\n";

    // ══════════════════════════════════════════════════
    // 10. TEACHER ASSIGNMENTS
    // ══════════════════════════════════════════════════
    $assignRaw = [
        // [teacher_code, class_code, day_of_week]
        ['GV001', 'JS101-B',  1], ['GV001', 'JS101-B',  4],
        ['GV002', 'PHP201-A', 1], ['GV002', 'PHP201-A', 4],
        ['GV001', 'DB301-A',  2], ['GV001', 'DB301-A',  5],
        ['GV001', 'PY501-A',  1], ['GV001', 'PY501-A',  4],
        ['GV003', 'PY501-B',  1], ['GV003', 'PY501-B',  4],
        ['GV002', 'JS101-A',  2], ['GV002', 'JS101-A',  5],
        ['GV002', 'PHP201-B', 2], ['GV002', 'PHP201-B', 5],
    ];
    $assignCount = 0;
    foreach ($assignRaw as [$tvCode, $clCode, $dow]) {
        $tid = $tvIds[$tvCode] ?? null;
        $cid = $clIds[$clCode]  ?? null;
        if (!$tid || !$cid) continue;
        if (!rowExists($pdo, 'teacher_assignments', ['teacher_id' => $tid, 'class_id' => $cid, 'day_of_week' => $dow])) {
            $pdo->exec("INSERT INTO teacher_assignments
                (teacher_id, class_id, student_id, day_of_week, scenario_name, assignment_status)
                VALUES ($tid, $cid, NULL, $dow, 'FINAL', 'CONFIRMED')");
            $assignCount++;
        }
    }
    echo "[10] Teacher assignments: $assignCount new\n";

    // ══════════════════════════════════════════════════
    // 11. SCHEDULES  (specific_date cho từng buổi)
    //   day_of_week: 1=Mon 2=Tue 3=Wed 4=Thu 5=Fri 6=Sat 7=Sun (ISO-8601 N)
    // ══════════════════════════════════════════════════
    $scheduleSpec = [
        // [class_code, [weekdays], start, end, startTime, endTime, examDaysBeforeEnd]
        ['JS101-B',  [1,4], '2026-04-14', '2026-06-19', '18:00:00', '20:00:00', 3],
        ['PHP201-A', [1,4], '2026-04-14', '2026-06-19', '18:00:00', '20:00:00', 3],
        ['DB301-A',  [2,5], '2026-05-12', '2026-06-19', '08:00:00', '10:00:00', 4],
        // Upcoming: seed a few weeks so lịch sắp tới hiện ngay
        ['PY501-A',  [1,4], '2026-07-06', '2026-09-13', '18:00:00', '20:00:00', 3],
        ['PY501-B',  [1,4], '2026-07-06', '2026-09-13', '08:00:00', '10:00:00', 3],
        ['JS101-A',  [2,5], '2026-07-07', '2026-09-14', '18:00:00', '20:00:00', 3],
        ['PHP201-B', [2,5], '2026-07-07', '2026-09-14', '08:00:00', '10:00:00', 3],
    ];
    $schedInserted = 0;
    foreach ($scheduleSpec as [$clCode, $wdays, $sdt, $edt, $stTime, $etTime, $examBefore]) {
        $cid = $clIds[$clCode] ?? null;
        if (!$cid) continue;
        $dates = generateSessionDates($sdt, $edt, $wdays);
        foreach ($dates as $date) {
            if (rowExists($pdo, 'schedules', ['class_id' => $cid, 'specific_date' => $date, 'schedule_type' => 'REGULAR'])) continue;
            $dow = (int)(new DateTime($date))->format('N');
            $pdo->exec("INSERT INTO schedules (class_id, day_of_week, specific_date, start_time, end_time, schedule_type)
                VALUES ($cid, $dow, '$date', '$stTime', '$etTime', 'REGULAR')");
            $schedInserted++;
        }
        // Buổi thi cuối kỳ (EXAM)
        $examDate = date('Y-m-d', strtotime("$edt -$examBefore days"));
        if (!rowExists($pdo, 'schedules', ['class_id' => $cid, 'specific_date' => $examDate, 'schedule_type' => 'EXAM'])) {
            $dow = (int)(new DateTime($examDate))->format('N');
            $pdo->exec("INSERT INTO schedules (class_id, day_of_week, specific_date, start_time, end_time, schedule_type, exam_label, exam_supervisor)
                VALUES ($cid, $dow, '$examDate', '$stTime', '$etTime', 'EXAM', 'Kiểm tra cuối kỳ', 'Nguyễn Quản Trị')");
            $schedInserted++;
        }
    }
    echo "[11] Schedules: $schedInserted new records\n";

    // ══════════════════════════════════════════════════
    // 12. ENROLLMENTS
    // ══════════════════════════════════════════════════
    //  Ongoing:   HV001-5 đã đóng tiền, HV006-7 chưa đóng
    //  Upcoming:  HV002,HV003,HV004,HV005 đăng ký PY501-A / JS101-A
    $enrollRaw = [
        // [sv_code, cl_code, payment_status, enroll_date]
        ['HV001', 'JS101-B',  'PAID',   '2026-04-10'],
        ['HV002', 'JS101-B',  'PAID',   '2026-04-10'],
        ['HV004', 'JS101-B',  'PAID',   '2026-04-11'],
        ['HV005', 'JS101-B',  'PAID',   '2026-04-12'],
        ['HV008', 'JS101-B',  'UNPAID', '2026-04-13'],

        ['HV003', 'PHP201-A', 'PAID',   '2026-04-10'],
        ['HV004', 'PHP201-A', 'PAID',   '2026-04-11'],
        ['HV006', 'PHP201-A', 'UNPAID', '2026-04-12'],
        ['HV007', 'PHP201-A', 'UNPAID', '2026-04-12'],

        ['HV001', 'DB301-A',  'PAID',   '2026-05-08'],
        ['HV003', 'DB301-A',  'PAID',   '2026-05-09'],
        ['HV005', 'DB301-A',  'PAID',   '2026-05-08'],

        // Upcoming sem2
        ['HV001', 'PY501-A',  'UNPAID', '2026-06-01'],
        ['HV002', 'PY501-A',  'UNPAID', '2026-06-01'],
        ['HV004', 'PY501-A',  'PAID',   '2026-06-02'],
        ['HV005', 'PY501-A',  'PAID',   '2026-06-02'],
        ['HV006', 'PY501-A',  'UNPAID', '2026-06-03'],

        ['HV003', 'JS101-A',  'UNPAID', '2026-06-01'],
        ['HV007', 'JS101-A',  'UNPAID', '2026-06-02'],
        ['HV008', 'JS101-A',  'PAID',   '2026-06-02'],

        ['HV002', 'PY501-B',  'PAID',   '2026-06-04'],
        ['HV003', 'PY501-B',  'PAID',   '2026-06-04'],
    ];
    $enrollCount = 0;
    foreach ($enrollRaw as [$svCode, $clCode, $ps, $edate]) {
        $sid = $svIds[$svCode] ?? null;
        $cid = $clIds[$clCode]  ?? null;
        if (!$sid || !$cid) continue;
        if (!rowExists($pdo, 'enrollments', ['student_id' => $sid, 'class_id' => $cid])) {
            $pdo->exec("INSERT INTO enrollments (student_id, class_id, enrollment_date, payment_status, status)
                VALUES ($sid, $cid, '$edate', '$ps', 'ACTIVE')");
            $enrollCount++;
        }
    }
    echo "[12] Enrollments: $enrollCount new\n";

    // ══════════════════════════════════════════════════
    // 13. TUITION PAYMENTS
    // ══════════════════════════════════════════════════
    // Lấy học phí theo course_code
    $fees = [];
    foreach ($pdo->query("SELECT co.course_code, cl.class_code, co.tuition_fee
        FROM courses co JOIN classes cl ON cl.course_id = co.id")->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $fees[$r['class_code']] = (float)$r['tuition_fee'];
    }

    $paidPay = [
        // [sv_code, cl_code, date, method]
        ['HV001', 'JS101-B',  '2026-04-10', 'CASH'],
        ['HV002', 'JS101-B',  '2026-04-11', 'BANK_TRANSFER'],
        ['HV004', 'JS101-B',  '2026-04-12', 'CASH'],
        ['HV005', 'JS101-B',  '2026-04-13', 'MOMO'],
        ['HV003', 'PHP201-A', '2026-04-10', 'BANK_TRANSFER'],
        ['HV004', 'PHP201-A', '2026-04-12', 'CASH'],
        ['HV001', 'DB301-A',  '2026-05-08', 'CASH'],
        ['HV003', 'DB301-A',  '2026-05-09', 'MOMO'],
        ['HV005', 'DB301-A',  '2026-05-08', 'BANK_TRANSFER'],
        ['HV004', 'PY501-A',  '2026-06-02', 'MOMO'],
        ['HV005', 'PY501-A',  '2026-06-02', 'BANK_TRANSFER'],
        ['HV008', 'JS101-A',  '2026-06-03', 'CASH'],
        ['HV002', 'PY501-B',  '2026-06-04', 'CASH'],
        ['HV003', 'PY501-B',  '2026-06-04', 'MOMO'],
    ];
    $unpaidPay = [
        ['HV006', 'PHP201-A'],
        ['HV007', 'PHP201-A'],
        ['HV008', 'JS101-B'],
        ['HV001', 'PY501-A'],
        ['HV002', 'PY501-A'],
        ['HV006', 'PY501-A'],
        ['HV003', 'JS101-A'],
        ['HV007', 'JS101-A'],
    ];

    $payCount = 0;
    foreach ($paidPay as [$svCode, $clCode, $pdate, $method]) {
        $sid  = $svIds[$svCode] ?? null;
        $cid  = $clIds[$clCode]  ?? null;
        $amt  = $fees[$clCode]   ?? 0;
        if (!$sid || !$cid || !$amt) continue;
        if (!rowExists($pdo, 'tuition_payments', ['student_id' => $sid, 'class_id' => $cid, 'payment_status' => 'COMPLETED'])) {
            $pdo->exec("INSERT INTO tuition_payments (student_id, class_id, amount, payment_date, payment_method, payment_status)
                VALUES ($sid, $cid, $amt, '$pdate', '$method', 'COMPLETED')");
            $payCount++;
        }
    }
    foreach ($unpaidPay as [$svCode, $clCode]) {
        $sid = $svIds[$svCode] ?? null;
        $cid = $clIds[$clCode]  ?? null;
        $amt = $fees[$clCode]   ?? 0;
        if (!$sid || !$cid || !$amt) continue;
        if (!rowExists($pdo, 'tuition_payments', ['student_id' => $sid, 'class_id' => $cid, 'payment_status' => 'UNPAID'])) {
            $pdo->exec("INSERT INTO tuition_payments (student_id, class_id, amount, payment_date, payment_status)
                VALUES ($sid, $cid, $amt, CURDATE(), 'UNPAID')");
            $payCount++;
        }
    }
    echo "[13] Tuition payments: $payCount new\n";

    // ══════════════════════════════════════════════════
    // 14. ATTENDANCE  (chỉ các buổi đã qua)
    // ══════════════════════════════════════════════════
    // sv đã enroll và đã đóng tiền (PAID)
    $attendMap = [
        'JS101-B'  => ['HV001','HV002','HV004','HV005'],
        'PHP201-A' => ['HV003','HV004'],
        'DB301-A'  => ['HV001','HV003','HV005'],
    ];
    // weighted random attendance status
    $statusPool = array_merge(
        array_fill(0, 12, 'PRESENT'),
        array_fill(0, 2,  'LATE'),
        array_fill(0, 1,  'ABSENT'),
        array_fill(0, 1,  'EXCUSED')
    );

    $pastSched = $pdo->query(
        "SELECT s.id, s.class_id, s.specific_date, cl.class_code
         FROM schedules s
         JOIN classes cl ON s.class_id = cl.id
         WHERE s.specific_date < CURDATE()
           AND cl.class_code IN ('JS101-B','PHP201-A','DB301-A')
           AND s.schedule_type = 'REGULAR'
         ORDER BY s.class_id, s.specific_date"
    )->fetchAll(PDO::FETCH_ASSOC);

    $attCount = 0;
    foreach ($pastSched as $sch) {
        $svList = $attendMap[$sch['class_code']] ?? [];
        foreach ($svList as $svCode) {
            $sid = $svIds[$svCode] ?? null;
            if (!$sid) continue;
            if (rowExists($pdo, 'attendance', ['class_id' => (int)$sch['class_id'], 'student_id' => $sid, 'attendance_date' => $sch['specific_date']])) continue;
            $st     = $statusPool[array_rand($statusPool)];
            $tinh   = ($st === 'PRESENT' || $st === 'LATE') ? 1 : 0;
            $pdo->exec("INSERT INTO attendance (class_id, student_id, attendance_date, attendance_status, tinh_luong)
                VALUES ({$sch['class_id']}, $sid, '{$sch['specific_date']}', '$st', $tinh)");
            $attCount++;
        }
    }
    echo "[14] Attendance: $attCount records\n";

    // ══════════════════════════════════════════════════
    // 15. PAYROLLS
    // ══════════════════════════════════════════════════
    $payrolls = [
        // [tv_code, month, hours, amount, status]
        ['GV001', '2026-04', 32.0, 4800000, 'PAID'],
        ['GV001', '2026-05', 40.0, 6000000, 'PAID'],
        ['GV001', '2026-06', 20.0, 3000000, 'PENDING'],
        ['GV002', '2026-04', 28.0, 4200000, 'PAID'],
        ['GV002', '2026-05', 36.0, 5400000, 'PAID'],
        ['GV002', '2026-06', 16.0, 2400000, 'PENDING'],
        ['GV003', '2026-05', 12.0, 1800000, 'PAID'],
        ['GV003', '2026-06',  8.0, 1200000, 'PENDING'],
    ];
    $prCount = 0;
    foreach ($payrolls as [$tvCode, $month, $hrs, $amt, $st]) {
        $tid = $tvIds[$tvCode] ?? null;
        if (!$tid) continue;
        if (!rowExists($pdo, 'payrolls', ['teacher_id' => $tid, 'month' => $month])) {
            $pdo->exec("INSERT INTO payrolls (teacher_id, month, teaching_hours, salary_amount, payment_status)
                VALUES ($tid, '$month', $hrs, $amt, '$st')");
            $prCount++;
        }
    }
    echo "[15] Payrolls: $prCount new\n";

    // ══════════════════════════════════════════════════
    // 16. LEAVE REQUESTS (nghỉ + học bù)
    // ══════════════════════════════════════════════════
    $d2  = date('Y-m-d', strtotime('+2 days'));
    $d3  = date('Y-m-d', strtotime('+3 days'));
    $d8  = date('Y-m-d', strtotime('+8 days'));
    $dm3 = date('Y-m-d', strtotime('-3 days'));

    $leaveRaw = [
        // [type, req_id_key, class_code, date, reason, req_type, status]
        ['TEACHER', $tv2,              'PHP201-A', $d3,  'Bận họp chuyên môn, xin nghỉ và dạy bù sau.',     'MAKEUP', 'PENDING'],
        ['TEACHER', $tv1,              'JS101-B',  $d8,  'Công tác ngoài tỉnh, xin dạy bù ngày khác.',       'MAKEUP', 'APPROVED'],
        ['STUDENT', $svIds['HV001'],   'JS101-B',  $d2,  'Bị ốm, không thể đến lớp.',                        'LEAVE',  'PENDING'],
        ['STUDENT', $svIds['HV002'],   'JS101-B',  $dm3, 'Gia đình có việc bận đột xuất.',                    'LEAVE',  'APPROVED'],
        ['STUDENT', $svIds['HV004'],   'PHP201-A', $d3,  'Xin phép nghỉ học do bận thi môn khác.',           'LEAVE',  'PENDING'],
    ];
    $lvCount = 0;
    foreach ($leaveRaw as [$rType, $rId, $clCode, $rDate, $reason, $reqType, $st]) {
        $cid = $clIds[$clCode] ?? null;
        if (!$rId || !$cid) continue;
        if (!rowExists($pdo, 'leave_requests', ['requester_type' => $rType, 'requester_id' => $rId, 'request_date' => $rDate])) {
            $pdo->exec("INSERT INTO leave_requests (requester_type, requester_id, class_id, request_date, reason, request_type, status)
                VALUES ('$rType', $rId, $cid, '$rDate', " . $pdo->quote($reason) . ", '$reqType', '$st')");
            $lvCount++;
        }
    }
    echo "[16] Leave requests: $lvCount new\n";

    // ══════════════════════════════════════════════════
    // 17. SUBMISSIONS & GRADES
    // ══════════════════════════════════════════════════
    $subRaw = [
        // [sv_code, cl_code, type, status]
        ['HV001', 'JS101-B',  'ASSIGNMENT', 'GRADED'],
        ['HV002', 'JS101-B',  'ASSIGNMENT', 'GRADED'],
        ['HV004', 'JS101-B',  'ASSIGNMENT', 'PENDING'],
        ['HV005', 'JS101-B',  'ASSIGNMENT', 'GRADED'],
        ['HV001', 'JS101-B',  'MIDTERM',    'GRADED'],
        ['HV002', 'JS101-B',  'MIDTERM',    'GRADED'],
        ['HV003', 'PHP201-A', 'ASSIGNMENT', 'GRADED'],
        ['HV004', 'PHP201-A', 'ASSIGNMENT', 'GRADED'],
        ['HV003', 'PHP201-A', 'MIDTERM',    'GRADED'],
        ['HV001', 'DB301-A',  'ASSIGNMENT', 'GRADED'],
        ['HV005', 'DB301-A',  'ASSIGNMENT', 'PENDING'],
    ];
    $gradeComments = [
        'Bài làm tốt, trình bày rõ ràng.',
        'Cần bổ sung thêm phần lý thuyết.',
        'Xuất sắc, đề nghị làm thêm bài nâng cao.',
        'Có tiến bộ, tiếp tục cố gắng.',
        'Hoàn thành đầy đủ các yêu cầu.',
    ];
    $subCount = 0; $gradeCount = 0;
    foreach ($subRaw as [$svCode, $clCode, $type, $st]) {
        $sid = $svIds[$svCode] ?? null;
        $cid = $clIds[$clCode]  ?? null;
        if (!$sid || !$cid) continue;
        if (rowExists($pdo, 'submissions', ['student_id' => $sid, 'class_id' => $cid, 'type' => $type])) continue;
        $pdo->exec("INSERT INTO submissions (student_id, class_id, type, status)
            VALUES ($sid, $cid, '$type', '$st')");
        $subId = (int)$pdo->lastInsertId();
        $subCount++;
        if ($st === 'GRADED') {
            $score   = round(rand(60, 98) + rand(0,9)/10, 1);
            $comment = $gradeComments[array_rand($gradeComments)];
            $pdo->exec("INSERT INTO grades (submission_id, teacher_id, score, comment)
                VALUES ($subId, $tv1, $score, " . $pdo->quote($comment) . ")");
            $gradeCount++;
        }
    }
    echo "[17] Submissions: $subCount, Grades: $gradeCount\n";

    // ══════════════════════════════════════════════════
    // 18. STUDENT EVALUATIONS
    // ══════════════════════════════════════════════════
    $evalRaw = [
        // [sv_code, cl_code, tv_code, score, level, retake, comment]
        ['HV001', 'JS101-B',  'GV001', 8.5, 'GIOI',       0, 'Học viên chăm chỉ, nắm vững kiến thức. Đề nghị tham gia nhóm nâng cao.'],
        ['HV002', 'JS101-B',  'GV001', 7.2, 'KHA',        0, 'Tiến bộ tốt, cần luyện thêm bài tập thực hành.'],
        ['HV004', 'JS101-B',  'GV001', 5.4, 'TRUNG_BINH', 1, 'Cần cố gắng hơn, dự kiến phải thi lại.'],
        ['HV005', 'JS101-B',  'GV001', 7.8, 'KHA',        0, 'Hoàn thành tốt, cần thêm kinh nghiệm thực tế.'],
        ['HV003', 'PHP201-A', 'GV002', 9.2, 'GIOI',       0, 'Xuất sắc, lập trình backend rất chắc. Nên học thêm framework Laravel.'],
        ['HV004', 'PHP201-A', 'GV002', 6.8, 'KHA',        0, 'Hoàn thành tốt các bài tập cơ bản.'],
        ['HV001', 'DB301-A',  'GV001', 8.0, 'GIOI',       0, 'Nắm vững SQL, tối ưu query tốt.'],
        ['HV003', 'DB301-A',  'GV001', 7.5, 'KHA',        0, 'Tốt ở lý thuyết, cần thực hành thêm.'],
        ['HV005', 'DB301-A',  'GV001', 6.2, 'KHA',        0, 'Đạt yêu cầu, tiếp tục ôn tập.'],
    ];
    $evCount = 0;
    foreach ($evalRaw as [$svCode, $clCode, $tvCode, $score, $level, $retake, $comment]) {
        $sid = $svIds[$svCode] ?? null;
        $cid = $clIds[$clCode]  ?? null;
        $tid = $tvIds[$tvCode]  ?? null;
        if (!$sid || !$cid) continue;
        if (!rowExists($pdo, 'student_evaluations', ['student_id' => $sid, 'class_id' => $cid])) {
            $pdo->exec("INSERT INTO student_evaluations (student_id, class_id, teacher_id, avg_score, level, retake_needed, teacher_comment)
                VALUES ($sid, $cid, $tid, $score, '$level', $retake, " . $pdo->quote($comment) . ")");
            $evCount++;
        }
    }
    echo "[18] Student evaluations: $evCount new\n";

    // ══════════════════════════════════════════════════
    // 19. SURVEYS & RESPONSES
    // ══════════════════════════════════════════════════
    $surveyData = [
        ['JS101-B',  'Khảo sát chất lượng giảng dạy – JS101-B tháng 6/2026'],
        ['PHP201-A', 'Khảo sát cuối kỳ – PHP201-A'],
    ];
    $suvIds = [];
    foreach ($surveyData as [$clCode, $title]) {
        $cid = $clIds[$clCode] ?? null;
        if (!$cid) continue;
        $existId = $pdo->query("SELECT id FROM surveys WHERE class_id=$cid LIMIT 1")->fetchColumn();
        if (!$existId) {
            $pdo->exec("INSERT INTO surveys (class_id, title) VALUES ($cid, " . $pdo->quote($title) . ")");
            $existId = $pdo->lastInsertId();
        }
        $suvIds[$clCode] = (int)$existId;
    }
    $surveyResponses = [
        // [survey_class, sv_code, tv_code, rating, comment]
        ['JS101-B',  'HV001', 'GV001', 5, 'Giáo viên nhiệt tình, dễ hiểu. Rất hài lòng!'],
        ['JS101-B',  'HV002', 'GV001', 4, 'Bài giảng hay, mong có thêm ví dụ thực tế.'],
        ['JS101-B',  'HV004', 'GV001', 3, 'Tốc độ giảng hơi nhanh, khó theo kịp.'],
        ['JS101-B',  'HV005', 'GV001', 5, 'Rất tốt! Hiểu rõ JavaScript hơn nhiều.'],
        ['PHP201-A', 'HV003', 'GV002', 5, 'Cô Hoa dạy rất kỹ, code ví dụ thực tế.'],
        ['PHP201-A', 'HV004', 'GV002', 4, 'Nội dung tốt, nên có thêm bài tập nhóm.'],
    ];
    $suvRespCount = 0;
    foreach ($surveyResponses as [$clCode, $svCode, $tvCode, $rating, $comment]) {
        $suvId = $suvIds[$clCode] ?? null;
        $sid   = $svIds[$svCode]  ?? null;
        $tid   = $tvIds[$tvCode]  ?? null;
        if (!$suvId || !$sid || !$tid) continue;
        if (!rowExists($pdo, 'survey_responses', ['survey_id' => $suvId, 'student_id' => $sid])) {
            $pdo->exec("INSERT INTO survey_responses (survey_id, student_id, teacher_id, rating, comment)
                VALUES ($suvId, $sid, $tid, $rating, " . $pdo->quote($comment) . ")");
            $suvRespCount++;
        }
    }
    echo "[19] Surveys: " . count($suvIds) . ", responses: $suvRespCount new\n";

    // ══════════════════════════════════════════════════
    // 20. NOTIFICATIONS
    // ══════════════════════════════════════════════════
    $today = date('d/m/Y');
    $d7    = date('d/m/Y', strtotime('+7 days'));
    $notifs = [
        // [receiver_email, title, content, is_read]
        // Admin
        ['admin@edu.vn', 'Yêu cầu học bù mới',
         "GV Lê Thị Hoa gửi yêu cầu học bù ngày $d7 (PHP201-A). Vui lòng xem xét duyệt.", 0],
        ['admin@edu.vn', '3 học viên chưa đóng học phí',
         'PHP201-A: HV006 (Linh), HV007 (Hùng) và JS101-B: HV008 (Thu) chưa đóng học phí. Cần xử lý.', 0],
        ['admin@edu.vn', 'Bảng lương tháng 06/2026 chờ duyệt',
         'Có 3 bảng lương tháng 06/2026 (GV001, GV002, GV003) đang ở trạng thái PENDING.', 0],
        ['admin@edu.vn', 'Lớp PY501-A sắp khai giảng',
         'Lớp PY501-A dự kiến khai giảng 06/07/2026. Hiện có 5 học viên đăng ký (2 đã đóng tiền).', 1],

        // GV001 - Trần Minh Tuấn
        ['tuan.gv@edu.vn', 'Lịch dạy hôm nay',
         "Bạn có buổi dạy JS101-B hôm nay ($today) lúc 18:00–20:00 tại P101.", 0],
        ['tuan.gv@edu.vn', 'Bảng lương 05/2026 đã thanh toán',
         'Bảng lương tháng 05/2026 đã được xử lý. Số tiền: 6,000,000 VND. Vui lòng kiểm tra.', 1],
        ['tuan.gv@edu.vn', 'Yêu cầu học bù được duyệt',
         "Yêu cầu học bù ngày $d8 của bạn (JS101-B) đã được Admin phê duyệt.", 1],
        ['tuan.gv@edu.vn', 'Nhắc nhở: điểm danh lớp DB301-A',
         'Lớp DB301-A còn 3 buổi học. Vui lòng điểm danh đúng hạn sau mỗi buổi.', 0],

        // GV002 - Lê Thị Hoa
        ['hoa.gv@edu.vn', 'Lịch dạy hôm nay',
         "Bạn có buổi dạy PHP201-A hôm nay ($today) lúc 18:00–20:00 tại P201.", 0],
        ['hoa.gv@edu.vn', 'Học viên chưa đóng học phí',
         'Lớp PHP201-A có 2 học viên (HV006 Linh, HV007 Hùng) chưa đóng học phí.', 0],
        ['hoa.gv@edu.vn', 'Bảng lương 05/2026 đã thanh toán',
         'Bảng lương tháng 05/2026: 5,400,000 VND đã được thanh toán.', 1],

        // GV003 - Nguyễn Minh Đức
        ['duc.gv@edu.vn', 'Chào mừng gia nhập EduCenter!',
         'Tài khoản của bạn đã được kích hoạt. Lớp PY501-B sẽ khai giảng 06/07/2026.', 1],
        ['duc.gv@edu.vn', 'Bảng lương 05/2026',
         'Bảng lương tháng 05/2026 đã được thanh toán. Số tiền: 1,800,000 VND.', 1],

        // HV001 - Phạm Văn An
        ['an.hv@edu.vn', 'Lịch học hôm nay',
         "JS101-B: buổi học hôm nay ($today) lúc 18:00–20:00 tại P101. Đừng quên bài tập!", 0],
        ['an.hv@edu.vn', 'Xác nhận đăng ký PY501-A',
         'Bạn đã đăng ký lớp PY501-A (khai giảng 06/07). Học phí 3,200,000 VND chưa đóng.', 0],
        ['an.hv@edu.vn', 'Đã thanh toán – JS101-B',
         'Học phí lớp JS101-B (3,500,000 VND) đã được xác nhận. Cảm ơn bạn!', 1],

        // HV002 - Nguyễn Thị Bình
        ['binh.hv@edu.vn', 'Lịch học hôm nay',
         "JS101-B: buổi học hôm nay ($today) lúc 18:00–20:00 tại P101.", 0],
        ['binh.hv@edu.vn', 'Nhắc nhở học phí PY501-A',
         'Học phí lớp PY501-A (3,200,000 VND) chưa thanh toán. Hạn đóng: 30/06/2026.', 0],

        // HV003 - Hoàng Minh Cường
        ['cuong.hv@edu.vn', 'Nhắc nhở học phí JS101-A',
         'Học phí lớp JS101-A (3,500,000 VND) chưa thanh toán. Hạn đóng: 30/06/2026.', 0],
        ['cuong.hv@edu.vn', 'Kết quả học tập PHP201-A',
         'Điểm trung bình của bạn: 9.2 – Loại GIỎI! Xuất sắc!', 1],

        // HV006, HV007
        ['linh.hv@edu.vn', 'Nhắc nhở học phí PHP201-A',
         'Học phí lớp PHP201-A (3,000,000 VND) chưa thanh toán. Hạn đóng: 30/06/2026.', 0],
        ['hung.hv@edu.vn', 'Nhắc nhở học phí PHP201-A',
         'Học phí lớp PHP201-A (3,000,000 VND) chưa thanh toán. Hạn đóng: 30/06/2026.', 0],
    ];
    $notifCount = 0;
    foreach ($notifs as [$email, $title, $content, $isRead]) {
        $rid = $uids[$email] ?? null;
        if (!$rid) continue;
        // Insert mọi lần (thông báo không có unique constraint)
        $pdo->exec("INSERT INTO notifications (title, content, receiver_id, is_read)
            VALUES (" . $pdo->quote($title) . ", " . $pdo->quote($content) . ", $rid, $isRead)");
        $notifCount++;
    }
    echo "[20] Notifications: $notifCount inserted\n";

    // ══════════════════════════════════════════════════
    // SUMMARY
    // ══════════════════════════════════════════════════
    $todayStr     = date('d/m/Y');
    $d8Str        = date('d/m/Y', strtotime('+8 days'));

    echo "\n";
    echo str_repeat('═', 60) . "\n";
    echo "  SEED FAKE DATA HOÀN THÀNH!\n";
    echo str_repeat('═', 60) . "\n";
    echo "\n";
    echo "┌─ ĐĂNG NHẬP TEST ──────────────────────────────────────\n";
    echo "│ ADMIN   : admin@edu.vn          / admin123\n";
    echo "│ GV001   : tuan.gv@edu.vn        / teacher123\n";
    echo "│           → Dạy JS101-B (18h T2+T5), DB301-A (8h T3+T6)\n";
    echo "│ GV002   : hoa.gv@edu.vn         / teacher123\n";
    echo "│           → Dạy PHP201-A (18h T2+T5)\n";
    echo "│ GV003   : duc.gv@edu.vn         / teacher123\n";
    echo "│           → Dạy PY501-B (sắp tới 06/07)\n";
    echo "├─ SINH VIÊN ───────────────────────────────────────────\n";
    echo "│ HV001   : an.hv@edu.vn          / student123\n";
    echo "│           → JS101-B ✓ PAID, DB301-A ✓ PAID, PY501-A ✗ UNPAID\n";
    echo "│ HV002   : binh.hv@edu.vn        / student123\n";
    echo "│           → JS101-B ✓ PAID, PY501-A ✗ UNPAID, PY501-B ✓ PAID\n";
    echo "│ HV003   : cuong.hv@edu.vn       / student123\n";
    echo "│           → PHP201-A ✓ PAID, DB301-A ✓ PAID, JS101-A ✗ UNPAID, PY501-B ✓ PAID\n";
    echo "│ HV004   : mai.hv@edu.vn         / student123\n";
    echo "│           → JS101-B ✓ PAID, PHP201-A ✓ PAID, PY501-A ✓ PAID\n";
    echo "│ HV005   : khoa.hv@edu.vn        / student123\n";
    echo "│           → JS101-B ✓ PAID, DB301-A ✓ PAID, PY501-A ✓ PAID\n";
    echo "│ HV006   : linh.hv@edu.vn        / student123  → PHP201-A ✗ UNPAID\n";
    echo "│ HV007   : hung.hv@edu.vn        / student123  → PHP201-A ✗ UNPAID\n";
    echo "│ HV008   : thu.hv@edu.vn         / student123  → JS101-B ✗ UNPAID\n";
    echo "├─ LỊCH SẮP TỚI (sau $todayStr) ─────────────────────\n";
    echo "│ JS101-B  (GV001): T5 " . date('d/m', strtotime('2026-06-12')) . ", T2 " . date('d/m', strtotime('2026-06-16')) . ", T5 " . date('d/m', strtotime('2026-06-19')) . " – 18:00–20:00 P101\n";
    echo "│ PHP201-A (GV002): T5 " . date('d/m', strtotime('2026-06-12')) . ", T2 " . date('d/m', strtotime('2026-06-16')) . ", T5 " . date('d/m', strtotime('2026-06-19')) . " – 18:00–20:00 P201\n";
    echo "│ DB301-A  (GV001): T6 " . date('d/m', strtotime('2026-06-12')) . ", T3 " . date('d/m', strtotime('2026-06-16')) . ", T6 " . date('d/m', strtotime('2026-06-19')) . " – 08:00–10:00 P301\n";
    echo "│ PY501-A  (GV001): khai giảng 06/07 (Thứ 2+Thứ 5)\n";
    echo "│ JS101-A  (GV002): khai giảng 07/07 (Thứ 3+Thứ 6)\n";
    echo "│ PY501-B  (GV003): khai giảng 06/07 (Thứ 2+Thứ 5)\n";
    echo "├─ DEMO CHỜ ADMIN XỬ LÝ ───────────────────────────────\n";
    echo "│ • Thanh toán UNPAID: HV006, HV007 (PHP201-A), HV008 (JS101-B)\n";
    echo "│ • Bảng lương PENDING: GV001, GV002, GV003 tháng 06/2026\n";
    echo "│ • Nghỉ/học bù PENDING: GV002 (PHP201-A), HV001 & HV004 (nghỉ)\n";
    echo "│ • Khảo sát mới: JS101-B, PHP201-A\n";
    echo str_repeat('═', 60) . "\n";

} catch (Exception $e) {
    echo "\n[ERROR] " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
