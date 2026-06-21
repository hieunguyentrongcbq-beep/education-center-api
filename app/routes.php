<?php
use Core\Controller;
use App\Controllers\AuthController;
use App\Controllers\instructorcontrol;
use App\Controllers\classcontrol;

// Public routes
$router->get('/', function() {
    header('Content-Type: application/json');
    echo json_encode(["message" => "Welcome to Education Center API"]);
});

$router->post('/auth/login', [AuthController::class, 'login']);

// ==========================================
// --- PROTECTED ROUTES ---------------------
// ==========================================
$auth = [Controller::class, 'authMiddleware'];
$managerOrAdmin = Controller::roleMiddleware(['CENTER_MANAGER', 'SUPER_ADMIN']);
$academicStaffOrManager = Controller::roleMiddleware(['ACADEMIC_STAFF', 'CENTER_MANAGER', 'SUPER_ADMIN']);
$teacher = Controller::roleMiddleware(['TEACHER']);
$accountant = Controller::roleMiddleware(['ACCOUNTANT', 'SUPER_ADMIN']);

// --- classcontrol.php mapped routes ---
$router->get('/students',    [classcontrol::class, 'indexStudent'],  [$auth]);
$router->get('/students/{id}/schedule', [classcontrol::class, 'studentSchedule'], [$auth]);
$router->post('/students',   [classcontrol::class, 'storeStudent'],  [$auth, $managerOrAdmin]);
$router->put('/students/{id}', [classcontrol::class, 'updateStudent'], [$auth, $managerOrAdmin]);
$router->delete('/students/{id}', [classcontrol::class, 'deleteStudent'], [$auth, $managerOrAdmin]);
$router->get('/classrooms', [classcontrol::class, 'indexClassroom'], [$auth]);
$router->get('/semesters',  [classcontrol::class, 'indexSemester'],  [$auth]);

$router->get('/courses', [classcontrol::class, 'indexCourse'], [$auth]);
$router->post('/courses', [classcontrol::class, 'storeCourse'], [$auth, $managerOrAdmin]);
$router->put('/courses/{id}', [classcontrol::class, 'updateCourse'], [$auth, $managerOrAdmin]);
$router->delete('/courses/{id}', [classcontrol::class, 'deleteCourse'], [$auth, $managerOrAdmin]);

$router->get('/classes', [classcontrol::class, 'indexClass'], [$auth]);
$router->post('/classes', [classcontrol::class, 'storeClass'], [$auth, $managerOrAdmin]);
$router->put('/classes/{id}', [classcontrol::class, 'updateClass'], [$auth, $managerOrAdmin]);
$router->delete('/classes/{id}', [classcontrol::class, 'deleteClass'], [$auth, $managerOrAdmin]);

$router->get('/schedules', [classcontrol::class, 'indexSchedule'], [$auth]);
$router->post('/schedules', [classcontrol::class, 'storeSchedule'], [$auth, $managerOrAdmin]);
$router->delete('/schedules/{id}', [classcontrol::class, 'deleteSchedule'], [$auth, $managerOrAdmin]);

$router->get('/enrollments', [classcontrol::class, 'indexEnrollment'], [$auth]);
$router->post('/enrollments', [classcontrol::class, 'storeEnrollment'], [$auth, $academicStaffOrManager]);
$router->put('/enrollments/{id}', [classcontrol::class, 'updateEnrollment'], [$auth, $academicStaffOrManager]);
$router->delete('/enrollments/{id}', [classcontrol::class, 'deleteEnrollment'], [$auth, $academicStaffOrManager]);

$router->get('/attendance', [classcontrol::class, 'indexAttendance'], [$auth]);
$router->post('/attendance', [classcontrol::class, 'storeAttendance'], [$auth]);
$router->delete('/attendance/{id}', [classcontrol::class, 'deleteAttendance'], [$auth, $managerOrAdmin]);

$router->get('/tuitions', [classcontrol::class, 'indexTuition'], [$auth, $accountant]);
$router->post('/tuitions/pay', [classcontrol::class, 'payTuition'], [$auth, $accountant]);
$router->delete('/tuitions/{id}', [classcontrol::class, 'deleteTuition'], [$auth, $managerOrAdmin]);

// --- instructorcontrol.php mapped routes ---
$router->get('/teachers', [instructorcontrol::class, 'indexTeacher'], [$auth]);
$router->get('/teachers/{id}/schedule', [instructorcontrol::class, 'teacherSchedule'], [$auth]);
$router->post('/teachers', [instructorcontrol::class, 'storeTeacher'], [$auth, $managerOrAdmin]);
$router->put('/teachers/{id}', [instructorcontrol::class, 'updateTeacher'], [$auth, $managerOrAdmin]);
$router->delete('/teachers/{id}', [instructorcontrol::class, 'deleteTeacher'], [$auth, $managerOrAdmin]);

$router->get('/notifications', [instructorcontrol::class, 'indexNotification'], [$auth]);
$router->post('/notifications/read', [instructorcontrol::class, 'markNotificationRead'], [$auth]);

$router->get('/teacher-assignments', [instructorcontrol::class, 'indexAssignment'], [$auth]);
$router->post('/teacher-assignments', [instructorcontrol::class, 'storeAssignment'], [$auth, $managerOrAdmin]);
$router->put('/teacher-assignments/{id}', [instructorcontrol::class, 'updateAssignment'], [$auth, $managerOrAdmin]);
$router->delete('/teacher-assignments/{id}', [instructorcontrol::class, 'deleteAssignment'], [$auth, $managerOrAdmin]);

// --- REPORTS (Phase 5) ---
$router->get('/reports/revenue', [classcontrol::class, 'reportRevenue'], [$auth, $accountant]);
$router->get('/reports/revenue/export', [classcontrol::class, 'exportRevenueCSV'], [$auth, $accountant]);
$router->get('/reports/students', [classcontrol::class, 'reportStudents'], [$auth, $managerOrAdmin]);
$router->get('/reports/teacher-hours', [instructorcontrol::class, 'reportTeacherHours'], [$auth, $managerOrAdmin]);
$router->get('/reports/scenarios/compare', [instructorcontrol::class, 'reportScenarios'], [$auth, $managerOrAdmin]);

// Example Protected route
$router->get('/api/protected', function() {
    header('Content-Type: application/json');
    echo json_encode(["message" => "You have accessed a protected route", "user" => $GLOBALS['user']]);
}, [$auth]);

// Example Admin route
$router->get('/api/admin', function() {
    header('Content-Type: application/json');
    echo json_encode(["message" => "Welcome Admin", "user" => $GLOBALS['user']]);
}, [$auth, Controller::roleMiddleware(['SUPER_ADMIN'])]);

// Thêm Route mặc định cho trang chủ
$router->get('/', function() {
    header('Content-Type: application/json');
    echo json_encode(["message" => "Welcome to Education Center API"]);
});
