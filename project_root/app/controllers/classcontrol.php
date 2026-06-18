<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\classmodel;
use App\Models\instructormodel;
use App\Models\ScheduleService;

class classcontrol extends Controller {
    private $classModel;

    public function __construct() {
        $this->classModel = new classmodel();
    }

    // --- STUDENTS ---
    public function indexStudent() {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 200;
        $all   = isset($_GET['all']) && $_GET['all'] === '1';
        $this->json(['data' => $this->classModel->getAllStudents($limit, $all)]);
    }

    public function storeStudent() {
        $data = $this->getJsonBody();
        if (!$data) $this->json(['error' => 'Invalid request'], 400);
        $result = $this->classModel->createStudent($data);
        if (isset($result['error'])) $this->json(['error' => $result['error']], 400);
        $this->json(['message' => 'Student created successfully', 'data' => $result], 201);
    }

    public function updateStudent($params) {
        $id = (int)($params['id'] ?? 0);
        $data = $this->getJsonBody();
        if (!$id || !$data) $this->json(['error' => 'Invalid request'], 400);
        $result = $this->classModel->updateStudent($id, $data);
        if (isset($result['error'])) $this->json(['error' => $result['error']], 400);
        $this->json(['message' => 'Student updated successfully']);
    }

    public function deleteStudent($params) {
        $id = (int)($params['id'] ?? 0);
        if (!$id) $this->json(['error' => 'Invalid ID'], 400);
        $result = $this->classModel->deleteStudent($id);
        if (isset($result['error'])) $this->json(['error' => $result['error']], 400);
        $this->json(['message' => 'Student deleted successfully']);
    }

    /**
     * GET /students/{id}/schedule?week=YYYY-MM-DD&class_id=
     * Lịch học theo tuần — chỉ lớp đã đăng ký + đã thanh toán (ACTIVE).
     */
    public function studentSchedule($params) {
        $studentId = (int)($params['id'] ?? 0);
        if (!$studentId) {
            $this->json(['error' => 'Invalid student id'], 400);
        }

        $student = $this->classModel->getStudentById($studentId);
        if (!$student) {
            $this->json(['error' => 'Student not found'], 404);
        }

        if (!$this->canViewStudentSchedule($studentId)) {
            $this->json(['error' => 'Forbidden: you cannot view this student schedule'], 403);
        }

        $weekStart = ScheduleService::normalizeWeekStart($_GET['week'] ?? null);
        $weekEnd = date('Y-m-d', strtotime($weekStart . ' +6 days'));
        $classId = !empty($_GET['class_id']) ? (int)$_GET['class_id'] : null;

        $service = new ScheduleService();
        $items = $service->getStudentScheduleForWeek($studentId, $weekStart, $classId);
        $attRecords = $service->getAttendanceForSessions($items, $studentId);
        $attLookup = $service->buildAttendanceLookup($attRecords);
        $items = $service->attachAttendanceToSessions($items, $attLookup, $studentId);

        $this->json([
            'data' => $items,
            'by_day' => $service->groupSchedulesByDay($items),
            'week_start' => $weekStart,
            'week_end' => $weekEnd,
            'student' => [
                'id' => $studentId,
                'student_code' => $student['student_code'],
                'full_name' => $student['full_name'],
            ],
            'class_id' => $classId,
        ]);
    }

    private function canViewStudentSchedule(int $studentId): bool {
        $user = $GLOBALS['user'] ?? null;
        if (!$user) {
            return false;
        }

        $roles = $user['roles'] ?? [];
        $managerRoles = ['ADMIN', 'SUPER_ADMIN', 'CENTER_MANAGER', 'ACADEMIC_STAFF', 'ACCOUNTANT'];
        foreach ($managerRoles as $role) {
            if (in_array($role, $roles, true)) {
                return true;
            }
        }

        if (in_array('STUDENT', $roles, true)) {
            $ownId = $this->classModel->getStudentIdByUserId((int)$user['id']);
            return $ownId !== null && $ownId === $studentId;
        }

        return false;
    }

    // --- CLASSROOMS ---
    public function indexClassroom() {
        $this->json(['data' => $this->classModel->getAllClassrooms()]);
    }

    // --- SEMESTERS ---
    public function indexSemester() {
        $this->json(['data' => $this->classModel->getAllSemesters()]);
    }

    // --- COURSE ---
    public function indexCourse() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $courses = $this->classModel->getAllCourses($page, $limit);
        $this->json(['data' => $courses, 'page' => $page, 'limit' => $limit]);
    }

    public function storeCourse() {
        $body = $this->getJsonBody();
        if (!$body) $this->json(['error' => 'Invalid JSON body'], 400);

        $result = $this->classModel->createCourse($body);
        if (isset($result['error'])) $this->json(['error' => $result['error']], 400);

        $this->json(['message' => 'Course created successfully', 'data' => $result], 201);
    }

    public function updateCourse($params) {
        $id = (int)($params['id'] ?? 0);
        $body = $this->getJsonBody();
        if (!$id || !$body) $this->json(['error' => 'Invalid request'], 400);

        $result = $this->classModel->updateCourse($id, $body);
        if (isset($result['error'])) $this->json(['error' => $result['error']], 400);
        $this->json(['message' => 'Course updated successfully']);
    }

    // --- CLASS ---
    public function indexClass() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $classes = $this->classModel->getAllClasses($page, $limit);
        $this->json(['data' => $classes, 'page' => $page, 'limit' => $limit]);
    }

    public function storeClass() {
        $body = $this->getJsonBody();
        if (!$body) $this->json(['error' => 'Invalid JSON body'], 400);

        $result = $this->classModel->createClass($body);
        if (isset($result['error'])) $this->json(['error' => $result['error']], 400);

        $this->json(['message' => 'Class created successfully', 'data' => $result], 201);
    }

    public function updateClass($params) {
        $id = (int)($params['id'] ?? 0);
        $body = $this->getJsonBody();
        if (!$id || !$body) $this->json(['error' => 'Invalid request'], 400);

        $result = $this->classModel->updateClass($id, $body);
        if (isset($result['error'])) $this->json(['error' => $result['error']], 400);
        $this->json(['message' => 'Class updated successfully']);
    }

    // --- SCHEDULE ---
    public function indexSchedule() {
        $classId   = isset($_GET['class_id'])   ? (int)$_GET['class_id']   : 0;
        $studentId = isset($_GET['student_id']) ? (int)$_GET['student_id'] : null;
        // Allow fetching all schedules for a student across all classes
        if (!$classId && $studentId) {
            $schedules = $this->classModel->getAllSchedulesByStudent($studentId);
            $this->json(['data' => $schedules]);
            return;
        }
        if (!$classId) { $this->json(['error' => 'class_id is required'], 400); return; }
        $schedules = $this->classModel->getSchedulesByClass($classId, $studentId);
        $this->json(['data' => $schedules]);
    }

    public function storeSchedule() {
        $body = $this->getJsonBody();
        if (!$body) $this->json(['error' => 'Invalid JSON body'], 400);

        $result = $this->classModel->createSchedule($body);
        if (isset($result['error'])) $this->json(['error' => $result['error'], 'details' => $result['details'] ?? null], 400);

        $classId = (int)($body['class_id'] ?? 0);
        if ($classId) {
            (new instructormodel())->notifyClassScheduleChange($classId, $body, !empty($result['updated']));
        }

        $this->json(['message' => 'Schedule created successfully', 'data' => $result], 201);
    }

    // --- ENROLLMENT ---
    public function indexEnrollment() {
        $classId = isset($_GET['class_id']) ? (int)$_GET['class_id'] : null;
        $enrollments = $this->classModel->getAllEnrollments($classId);
        $this->json(['data' => $enrollments]);
    }

    public function storeEnrollment() {
        $body = $this->getJsonBody();
        if (!$body) $this->json(['error' => 'Invalid JSON body'], 400);

        $result = $this->classModel->createEnrollment($body);
        if (isset($result['error'])) $this->json(['error' => $result['error']], 400);

        $this->json(['message' => 'Student enrolled successfully', 'data' => $result], 201);
    }

    public function updateEnrollment($params) {
        $id = (int)($params['id'] ?? 0);
        $body = $this->getJsonBody();
        if (!$id || !$body) $this->json(['error' => 'Invalid request'], 400);

        $result = $this->classModel->updateEnrollment($id, $body);
        if (isset($result['error'])) $this->json(['error' => $result['error']], 400);
        $this->json(['message' => 'Enrollment updated successfully']);
    }

    // --- ATTENDANCE ---
    public function indexAttendance() {
        $classId   = isset($_GET['class_id'])   ? (int)$_GET['class_id']   : null;
        $studentId = isset($_GET['student_id']) ? (int)$_GET['student_id'] : null;
        $date      = isset($_GET['date'])       ? $_GET['date']             : null;

        if (!$classId) { $this->json(['error' => 'class_id is required'], 400); return; }

        if ($studentId) {
            $attendance = $this->classModel->getAttendanceByStudentAndClass($classId, $studentId);
        } else {
            $attendance = $this->classModel->getAttendanceByClassAndDate($classId, $date ?? date('Y-m-d'));
        }
        $this->json(['data' => $attendance]);
    }

    public function storeAttendance() {
        $body = $this->getJsonBody();
        if (!$body) $this->json(['error' => 'Invalid JSON body'], 400);

        $result = $this->classModel->markAttendance($body);
        if (isset($result['error'])) $this->json(['error' => $result['error']], 400);

        $this->json(['message' => 'Attendance marked successfully', 'data' => $result], 201);
    }

    // --- TUITION ---
    public function indexTuition() {
        $studentId = isset($_GET['student_id']) ? (int)$_GET['student_id'] : null;
        $tuitions = $this->classModel->getAllTuitions($studentId);
        $this->json(['data' => $tuitions]);
    }

    public function payTuition() {
        $body = $this->getJsonBody();
        if (empty($body['payment_id']) || empty($body['payment_method'])) {
            $this->json(['error' => 'payment_id and payment_method are required'], 400);
        }

        $result = $this->classModel->payTuition($body['payment_id'], $body['payment_method']);
        if (isset($result['error'])) $this->json(['error' => $result['error']], 400);

        $this->json(['message' => 'Payment processed successfully', 'data' => $result]);
    }

    public function deleteTuition($params) {
        $id = (int)($params['id'] ?? 0);
        if (!$id) $this->json(['error' => 'Invalid ID'], 400);
        $result = $this->classModel->deleteTuition($id);
        if (isset($result['error'])) $this->json(['error' => $result['error']], 400);
        $this->json(['message' => 'Tuition record deleted successfully']);
    }

    // --- DELETE ---
    public function deleteCourse($params) {
        $id = (int)($params['id'] ?? 0);
        if (!$id) $this->json(['error' => 'Invalid ID'], 400);
        $result = $this->classModel->deleteCourse($id);
        if (isset($result['error'])) $this->json(['error' => $result['error']], 400);
        $this->json(['message' => 'Course deleted successfully']);
    }

    public function deleteClass($params) {
        $id = (int)($params['id'] ?? 0);
        if (!$id) $this->json(['error' => 'Invalid ID'], 400);
        $result = $this->classModel->deleteClass($id);
        if (isset($result['error'])) $this->json(['error' => $result['error']], 400);
        $this->json(['message' => 'Class deleted successfully']);
    }

    public function deleteSchedule($params) {
        $id = (int)($params['id'] ?? 0);
        if (!$id) $this->json(['error' => 'Invalid ID'], 400);
        $result = $this->classModel->deleteSchedule($id);
        if (isset($result['error'])) $this->json(['error' => $result['error']], 400);
        $this->json(['message' => 'Schedule deleted successfully']);
    }

    public function deleteEnrollment($params) {
        $id = (int)($params['id'] ?? 0);
        if (!$id) $this->json(['error' => 'Invalid ID'], 400);
        $result = $this->classModel->deleteEnrollment($id);
        if (isset($result['error'])) $this->json(['error' => $result['error']], 400);
        $this->json(['message' => 'Enrollment deleted successfully']);
    }

    public function deleteAttendance($params) {
        $id = (int)($params['id'] ?? 0);
        if (!$id) $this->json(['error' => 'Invalid ID'], 400);
        $result = $this->classModel->deleteAttendance($id);
        if (isset($result['error'])) $this->json(['error' => $result['error']], 400);
        $this->json(['message' => 'Attendance deleted successfully']);
    }

    // --- REPORTS ---
    public function reportRevenue() {
        $revenue = $this->classModel->getRevenueByMonth();
        $this->json(['data' => $revenue]);
    }

    // --- THƯỞNG ĐIỂM: EXCEL / CSV EXPORT ---
    public function exportRevenueCSV() {
        $revenue = $this->classModel->getRevenueByMonth();
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=doanh_thu_thang.csv');
        
        $output = fopen('php://output', 'w');
        // Add UTF-8 BOM for Excel compatibility
        fputs($output, "\xEF\xBB\xBF");
        
        fputcsv($output, ['Tháng', 'Tổng Doanh Thu (VNĐ)']);
        foreach ($revenue as $row) {
            fputcsv($output, [$row['month'], $row['total_revenue']]);
        }
        fclose($output);
        exit();
    }

    public function reportStudents() {
        $students = $this->classModel->getStudentCount();
        $fillRate = $this->classModel->getClassFillRate();
        $this->json([
            'student_status_count' => $students,
            'class_fill_rates' => $fillRate
        ]);
    }
}
