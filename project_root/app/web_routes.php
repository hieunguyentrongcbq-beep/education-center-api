<?php

use App\Middleware\SessionAuth;

use App\Controllers\WebAuthController;

use App\Controllers\Admin\AdminController;

use App\Controllers\Admin\CourseWebController;

use App\Controllers\Admin\ClassWebController;

use App\Controllers\Admin\ClassroomWebController;

use App\Controllers\Admin\SemesterWebController;

use App\Controllers\Admin\ClassPlanWebController;

use App\Controllers\Admin\StudentWebController;

use App\Controllers\Admin\TeacherWebController;

use App\Controllers\Admin\PaymentWebController;

use App\Controllers\Admin\EnrollmentWebController;

use App\Controllers\Admin\PayrollWebController;

use App\Controllers\Admin\ScheduleWebController;

use App\Controllers\Admin\AssignmentWebController;

use App\Controllers\Admin\AdminPhase2Controller;

use App\Controllers\Teacher\TeacherPortalController;

use App\Controllers\Teacher\TeacherPhase2Controller;

use App\Controllers\Student\StudentPortalController;

use App\Controllers\Student\StudentPhase2Controller;

use App\Controllers\NotificationWebController;

use App\Controllers\PortalApiController;



$guest = [SessionAuth::class, 'guest'];

$auth = [SessionAuth::class, 'handle'];

$admin = SessionAuth::requireRole(['ADMIN']);

$teacher = SessionAuth::requireRole(['TEACHER']);

$student = SessionAuth::requireRole(['STUDENT']);



$router->get('/', function() {
    if (!empty($_SESSION['user']['role'])) {
        $path = \App\Middleware\SessionAuth::roleDashboardPath($_SESSION['user']['role']);
        header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/web.php/' . $path);
        exit;
    }
    header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/web.php/login');
    exit;
});

$router->get('/login', [WebAuthController::class, 'showLogin'], [$guest]);

$router->post('/login', [WebAuthController::class, 'login'], [$guest]);

$router->get('/logout', [WebAuthController::class, 'logout'], [$auth]);



// Admin Phase 1

$router->get('/admin/dashboard', [AdminController::class, 'dashboard'], [$auth, $admin]);

$router->get('/admin/revenue/export', [AdminController::class, 'exportRevenue'], [$auth, $admin]);

$router->get('/admin/courses', [CourseWebController::class, 'index'], [$auth, $admin]);

$router->get('/admin/courses/create', [CourseWebController::class, 'create'], [$auth, $admin]);

$router->post('/admin/courses/store', [CourseWebController::class, 'store'], [$auth, $admin]);

$router->get('/admin/courses/{id}/edit', [CourseWebController::class, 'edit'], [$auth, $admin]);

$router->post('/admin/courses/{id}/update', [CourseWebController::class, 'update'], [$auth, $admin]);

$router->post('/admin/courses/{id}/delete', [CourseWebController::class, 'delete'], [$auth, $admin]);

$router->get('/admin/classes', [ClassWebController::class, 'index'], [$auth, $admin]);

$router->get('/admin/classes/create', [ClassWebController::class, 'create'], [$auth, $admin]);

$router->post('/admin/classes/store', [ClassWebController::class, 'store'], [$auth, $admin]);

$router->get('/admin/classes/{id}/edit', [ClassWebController::class, 'edit'], [$auth, $admin]);

$router->post('/admin/classes/{id}/update', [ClassWebController::class, 'update'], [$auth, $admin]);

$router->post('/admin/classes/{id}/delete', [ClassWebController::class, 'delete'], [$auth, $admin]);

$router->get('/admin/classrooms', [ClassroomWebController::class, 'index'], [$auth, $admin]);

$router->get('/admin/classrooms/create', [ClassroomWebController::class, 'create'], [$auth, $admin]);

$router->post('/admin/classrooms/store', [ClassroomWebController::class, 'store'], [$auth, $admin]);

$router->get('/admin/classrooms/{id}/edit', [ClassroomWebController::class, 'edit'], [$auth, $admin]);

$router->post('/admin/classrooms/{id}/update', [ClassroomWebController::class, 'update'], [$auth, $admin]);

$router->post('/admin/classrooms/{id}/delete', [ClassroomWebController::class, 'delete'], [$auth, $admin]);

$router->get('/admin/semesters', [SemesterWebController::class, 'index'], [$auth, $admin]);

$router->get('/admin/semesters/create', [SemesterWebController::class, 'create'], [$auth, $admin]);

$router->post('/admin/semesters/store', [SemesterWebController::class, 'store'], [$auth, $admin]);

$router->get('/admin/semesters/{id}/edit', [SemesterWebController::class, 'edit'], [$auth, $admin]);

$router->post('/admin/semesters/{id}/update', [SemesterWebController::class, 'update'], [$auth, $admin]);

$router->post('/admin/semesters/{id}/delete', [SemesterWebController::class, 'delete'], [$auth, $admin]);

$router->get('/admin/class-plans', [ClassPlanWebController::class, 'index'], [$auth, $admin]);

$router->get('/admin/class-plans/create', [ClassPlanWebController::class, 'create'], [$auth, $admin]);

$router->post('/admin/class-plans/store', [ClassPlanWebController::class, 'store'], [$auth, $admin]);

$router->get('/admin/class-plans/{id}/edit', [ClassPlanWebController::class, 'edit'], [$auth, $admin]);

$router->post('/admin/class-plans/{id}/update', [ClassPlanWebController::class, 'update'], [$auth, $admin]);

$router->post('/admin/class-plans/{id}/delete', [ClassPlanWebController::class, 'delete'], [$auth, $admin]);

$router->get('/admin/students', [StudentWebController::class, 'index'], [$auth, $admin]);

$router->get('/admin/students/create', [StudentWebController::class, 'create'], [$auth, $admin]);

$router->post('/admin/students/store', [StudentWebController::class, 'store'], [$auth, $admin]);

$router->get('/admin/students/{id}/edit', [StudentWebController::class, 'edit'], [$auth, $admin]);

$router->post('/admin/students/{id}/update', [StudentWebController::class, 'update'], [$auth, $admin]);

$router->post('/admin/students/{id}/delete', [StudentWebController::class, 'delete'], [$auth, $admin]);

$router->get('/admin/teachers', [TeacherWebController::class, 'index'], [$auth, $admin]);

$router->get('/admin/teachers/create', [TeacherWebController::class, 'create'], [$auth, $admin]);

$router->post('/admin/teachers/store', [TeacherWebController::class, 'store'], [$auth, $admin]);

$router->get('/admin/teachers/{id}/edit', [TeacherWebController::class, 'edit'], [$auth, $admin]);

$router->post('/admin/teachers/{id}/update', [TeacherWebController::class, 'update'], [$auth, $admin]);

$router->post('/admin/teachers/{id}/delete', [TeacherWebController::class, 'delete'], [$auth, $admin]);

$router->get('/admin/enrollments', [EnrollmentWebController::class, 'index'], [$auth, $admin]);

$router->get('/admin/enrollments/create', [EnrollmentWebController::class, 'create'], [$auth, $admin]);

$router->post('/admin/enrollments/store', [EnrollmentWebController::class, 'store'], [$auth, $admin]);

$router->get('/admin/enrollments/{id}/edit', [EnrollmentWebController::class, 'edit'], [$auth, $admin]);

$router->post('/admin/enrollments/{id}/update', [EnrollmentWebController::class, 'update'], [$auth, $admin]);

$router->post('/admin/enrollments/{id}/delete', [EnrollmentWebController::class, 'delete'], [$auth, $admin]);

$router->get('/admin/payrolls', [PayrollWebController::class, 'index'], [$auth, $admin]);

$router->get('/admin/payrolls/create', [PayrollWebController::class, 'create'], [$auth, $admin]);

$router->post('/admin/payrolls/store', [PayrollWebController::class, 'store'], [$auth, $admin]);

$router->get('/admin/payrolls/{id}/edit', [PayrollWebController::class, 'edit'], [$auth, $admin]);

$router->post('/admin/payrolls/{id}/update', [PayrollWebController::class, 'update'], [$auth, $admin]);

$router->post('/admin/payrolls/{id}/delete', [PayrollWebController::class, 'delete'], [$auth, $admin]);

$router->post('/admin/payrolls/{id}/mark-paid', [PayrollWebController::class, 'markPaid'], [$auth, $admin]);

$router->get('/admin/payments', [PaymentWebController::class, 'index'], [$auth, $admin]);

$router->get('/admin/payments/export', [PaymentWebController::class, 'exportCsv'], [$auth, $admin]);

$router->get('/admin/payments/create', [PaymentWebController::class, 'create'], [$auth, $admin]);

$router->post('/admin/payments/confirm', [PaymentWebController::class, 'confirm'], [$auth, $admin]);

$router->get('/admin/payments/{id}/edit', [PaymentWebController::class, 'edit'], [$auth, $admin]);

$router->post('/admin/payments/{id}/update', [PaymentWebController::class, 'update'], [$auth, $admin]);

$router->post('/admin/payments/{id}/refund', [PaymentWebController::class, 'refund'], [$auth, $admin]);

$router->get('/admin/schedules', [ScheduleWebController::class, 'index'], [$auth, $admin]);

$router->post('/admin/schedules/store', [ScheduleWebController::class, 'store'], [$auth, $admin]);

$router->get('/admin/schedules/{id}/edit', [ScheduleWebController::class, 'edit'], [$auth, $admin]);

$router->post('/admin/schedules/{id}/update', [ScheduleWebController::class, 'update'], [$auth, $admin]);

$router->post('/admin/schedules/{id}/delete', [ScheduleWebController::class, 'delete'], [$auth, $admin]);

$router->get('/admin/assignments', [AssignmentWebController::class, 'index'], [$auth, $admin]);

$router->post('/admin/assignments/store', [AssignmentWebController::class, 'store'], [$auth, $admin]);

$router->get('/admin/assignments/{id}/edit', [AssignmentWebController::class, 'edit'], [$auth, $admin]);

$router->post('/admin/assignments/{id}/update', [AssignmentWebController::class, 'update'], [$auth, $admin]);

$router->post('/admin/assignments/{id}/delete', [AssignmentWebController::class, 'delete'], [$auth, $admin]);

$router->get('/admin/teaching-schedule', [AssignmentWebController::class, 'teachingSchedule'], [$auth, $admin]);



// Admin Phase 2

$router->get('/admin/leave-requests', [AdminPhase2Controller::class, 'leaveRequests'], [$auth, $admin]);

$router->post('/admin/leave-requests/review', [AdminPhase2Controller::class, 'reviewLeave'], [$auth, $admin]);

$router->get('/admin/attendance-report', [AdminPhase2Controller::class, 'attendanceReport'], [$auth, $admin]);

$router->get('/admin/reports', [AdminPhase2Controller::class, 'reports'], [$auth, $admin]);

$router->get('/admin/reports/teachers/export', [AdminPhase2Controller::class, 'exportTeacherStats'], [$auth, $admin]);

$router->get('/admin/reports/students/export', [AdminPhase2Controller::class, 'exportStudentStats'], [$auth, $admin]);

$router->get('/admin/audit-logs', [AdminPhase2Controller::class, 'auditLogs'], [$auth, $admin]);

$router->get('/admin/audit-logs/export', [AdminPhase2Controller::class, 'exportAuditLogs'], [$auth, $admin]);

$router->get('/admin/notifications', [NotificationWebController::class, 'index'], [$auth, $admin]);

$router->post('/admin/notifications/{id}/read', [NotificationWebController::class, 'markRead'], [$auth, $admin]);

$router->post('/admin/notifications/read-all', [NotificationWebController::class, 'markAllRead'], [$auth, $admin]);



// Teacher

$router->get('/teacher/dashboard', [TeacherPortalController::class, 'dashboard'], [$auth, $teacher]);

$router->get('/teacher/schedule', [TeacherPortalController::class, 'schedule'], [$auth, $teacher]);

$router->get('/teacher/attendance', [TeacherPhase2Controller::class, 'attendance'], [$auth, $teacher]);

$router->post('/teacher/attendance/mark', [TeacherPhase2Controller::class, 'markAttendance'], [$auth, $teacher]);

$router->get('/teacher/leave', [TeacherPhase2Controller::class, 'leave'], [$auth, $teacher]);

$router->post('/teacher/leave/submit', [TeacherPhase2Controller::class, 'submitLeave'], [$auth, $teacher]);

$router->get('/teacher/grading', [TeacherPhase2Controller::class, 'grading'], [$auth, $teacher]);

$router->post('/teacher/grading/score', [TeacherPhase2Controller::class, 'grade'], [$auth, $teacher]);

$router->post('/teacher/grading/evaluate', [TeacherPhase2Controller::class, 'evaluate'], [$auth, $teacher]);

$router->get('/teacher/notifications', [NotificationWebController::class, 'index'], [$auth, $teacher]);

$router->post('/teacher/notifications/{id}/read', [NotificationWebController::class, 'markRead'], [$auth, $teacher]);

$router->post('/teacher/notifications/read-all', [NotificationWebController::class, 'markAllRead'], [$auth, $teacher]);



// Student

$router->get('/student/dashboard', [StudentPortalController::class, 'dashboard'], [$auth, $student]);

$router->get('/student/schedule', [StudentPortalController::class, 'schedule'], [$auth, $student]);

$router->get('/student/attendance', [StudentPhase2Controller::class, 'attendance'], [$auth, $student]);

$router->get('/student/leave', [StudentPhase2Controller::class, 'leave'], [$auth, $student]);

$router->post('/student/leave/submit', [StudentPhase2Controller::class, 'submitLeave'], [$auth, $student]);

$router->get('/student/submissions', [StudentPhase2Controller::class, 'submissions'], [$auth, $student]);

$router->post('/student/submissions/upload', [StudentPhase2Controller::class, 'upload'], [$auth, $student]);

$router->get('/student/submissions/{id}/download', [StudentPhase2Controller::class, 'downloadSubmission'], [$auth, $student]);

$router->get('/student/results', [StudentPhase2Controller::class, 'results'], [$auth, $student]);

$router->get('/student/compare', [StudentPhase2Controller::class, 'compare'], [$auth, $student]);

$router->get('/student/survey', [StudentPhase2Controller::class, 'survey'], [$auth, $student]);

$router->post('/student/survey/submit', [StudentPhase2Controller::class, 'submitSurvey'], [$auth, $student]);

$router->get('/student/notifications', [NotificationWebController::class, 'index'], [$auth, $student]);

$router->post('/student/notifications/{id}/read', [NotificationWebController::class, 'markRead'], [$auth, $student]);

$router->post('/student/notifications/read-all', [NotificationWebController::class, 'markAllRead'], [$auth, $student]);



// Portal AJAX API (K07 — session auth, JSON)

$router->get('/api/portal/classrooms', [PortalApiController::class, 'listClassrooms'], [$auth, $admin]);

$router->post('/api/portal/classrooms', [PortalApiController::class, 'createClassroom'], [$auth, $admin]);

$router->put('/api/portal/classrooms/{id}', [PortalApiController::class, 'updateClassroom'], [$auth, $admin]);

$router->delete('/api/portal/classrooms/{id}', [PortalApiController::class, 'deleteClassroom'], [$auth, $admin]);

$router->get('/api/portal/notifications/unread-count', [PortalApiController::class, 'unreadNotificationCount'], [$auth]);

$router->post('/api/portal/notifications/{id}/read', [PortalApiController::class, 'markNotificationRead'], [$auth]);

$router->post('/api/portal/notifications/read-all', [PortalApiController::class, 'markAllNotificationsRead'], [$auth]);

