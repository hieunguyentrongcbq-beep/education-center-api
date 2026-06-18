<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\instructormodel;
use App\Models\assigning;
use App\Models\ScheduleService;

class instructorcontrol extends Controller {
    private $instructorModel;
    private $assigningModel;

    public function __construct() {
        $this->instructorModel = new instructormodel();
        $this->assigningModel = new assigning();
    }

    // --- TEACHER ---
    public function indexTeacher() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $teachers = $this->instructorModel->getAllTeachers($page, $limit);
        $this->json(['data' => $teachers, 'page' => $page, 'limit' => $limit]);
    }

    public function storeTeacher() {
        $body = $this->getJsonBody();
        if (!$body) $this->json(['error' => 'Invalid JSON body'], 400);

        $result = $this->instructorModel->createTeacher($body);
        if (isset($result['error'])) $this->json(['error' => $result['error']], 400);

        $this->json(['message' => 'Teacher created successfully', 'data' => $result], 201);
    }

    public function updateTeacher($params) {
        $id = (int)($params['id'] ?? 0);
        $body = $this->getJsonBody();
        if (!$id || !$body) $this->json(['error' => 'Invalid request'], 400);

        $result = $this->instructorModel->updateTeacher($id, $body);
        if (isset($result['error'])) $this->json(['error' => $result['error']], 400);
        $this->json(['message' => 'Teacher updated successfully']);
    }

    // --- NOTIFICATION ---
    public function indexNotification() {
        $user = $GLOBALS['user'];
        $notifications = $this->instructorModel->getMyNotifications($user['id']);
        $this->json(['data' => $notifications]);
    }

    public function markNotificationRead() {
        $body = $this->getJsonBody();
        if (empty($body['notification_id'])) $this->json(['error' => 'notification_id is required'], 400);

        $user = $GLOBALS['user'];
        $success = $this->instructorModel->markNotificationAsRead($body['notification_id'], $user['id']);

        if ($success) $this->json(['message' => 'Marked as read successfully']);
        $this->json(['error' => 'Failed to mark as read'], 400);
    }

    // --- ASSIGNING ---
    public function indexAssignment() {
        $classId   = isset($_GET['class_id'])   ? (int)$_GET['class_id']   : null;
        $studentId = isset($_GET['student_id']) ? (int)$_GET['student_id'] : null;
        $assignments = $this->assigningModel->getAllAssignments($classId, $studentId);
        $this->json(['data' => $assignments]);
    }

    public function storeAssignment() {
        $body = $this->getJsonBody();
        if (!$body) $this->json(['error' => 'Invalid JSON body'], 400);

        $user = $GLOBALS['user'];
        $assignedBy = $user['id'];

        $result = $this->assigningModel->assignTeacher($body, $assignedBy);
        if (isset($result['error'])) $this->json(['error' => $result['error'], 'details' => $result['details'] ?? null], 400);

        $this->json(['message' => 'Teacher assigned successfully', 'data' => $result], 201);
    }

    // --- DELETE ---
    public function deleteTeacher($params) {
        $id = (int)($params['id'] ?? 0);
        if (!$id) $this->json(['error' => 'Invalid ID'], 400);
        $result = $this->instructorModel->deleteTeacher($id);
        if (isset($result['error'])) $this->json(['error' => $result['error']], 400);
        $this->json(['message' => 'Teacher deleted successfully']);
    }

    /**
     * GET /teachers/{id}/schedule?week=YYYY-MM-DD
     * Lịch dạy theo tuần (REGULAR + EXAM/MAKEUP) — dùng ScheduleService giống portal GV.
     */
    public function teacherSchedule($params) {
        $teacherId = (int)($params['id'] ?? 0);
        if (!$teacherId) {
            $this->json(['error' => 'Invalid teacher id'], 400);
        }

        $teacher = $this->instructorModel->getTeacherById($teacherId);
        if (!$teacher) {
            $this->json(['error' => 'Teacher not found'], 404);
        }

        if (!$this->canViewTeacherSchedule($teacherId)) {
            $this->json(['error' => 'Forbidden: you cannot view this teacher schedule'], 403);
        }

        $weekStart = ScheduleService::normalizeWeekStart($_GET['week'] ?? null);
        $weekEnd = date('Y-m-d', strtotime($weekStart . ' +6 days'));

        $service = new ScheduleService();
        $items = $service->getTeacherSchedule($teacherId, $weekStart);

        $this->json([
            'data' => $items,
            'by_day' => $service->groupSchedulesByDay($items),
            'week_start' => $weekStart,
            'week_end' => $weekEnd,
            'teacher' => [
                'id' => $teacherId,
                'teacher_code' => $teacher['teacher_code'],
                'full_name' => $teacher['full_name'],
            ],
        ]);
    }

    private function canViewTeacherSchedule(int $teacherId): bool {
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

        if (in_array('TEACHER', $roles, true)) {
            $ownId = $this->instructorModel->getTeacherIdByUserId((int)$user['id']);
            return $ownId !== null && $ownId === $teacherId;
        }

        return false;
    }

    public function updateAssignment($params) {
        $id = (int)($params['id'] ?? 0);
        $body = $this->getJsonBody();
        if (!$id || !$body) $this->json(['error' => 'Invalid request'], 400);
        $result = $this->assigningModel->updateAssignment($id, $body);
        if (isset($result['error'])) $this->json(['error' => $result['error']], 400);
        $this->json(['message' => 'Assignment updated successfully']);
    }

    public function deleteAssignment($params) {
        $id = (int)($params['id'] ?? 0);
        if (!$id) $this->json(['error' => 'Invalid ID'], 400);
        $result = $this->assigningModel->deleteAssignment($id);
        if (isset($result['error'])) $this->json(['error' => $result['error']], 400);
        $this->json(['message' => 'Assignment deleted successfully']);
    }

    // --- REPORTS ---
    public function reportTeacherHours() {
        $month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
        $scenario = isset($_GET['scenario']) ? $_GET['scenario'] : 'FINAL';
        $hours = $this->instructorModel->getTeacherTeachingHours($month, $scenario);
        $this->json(['data' => $hours, 'month' => $month, 'scenario' => $scenario]);
    }

    public function reportScenarios() {
        $scenarios = $this->instructorModel->compareScenarios();
        $this->json(['data' => $scenarios]);
    }
}
