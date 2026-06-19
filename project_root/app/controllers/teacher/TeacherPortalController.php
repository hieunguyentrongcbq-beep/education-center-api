<?php
namespace App\Controllers\Teacher;

use Core\WebController;
use App\Models\ScheduleService;
use Core\Database;

class TeacherPortalController extends WebController {
    private function getTeacherId(): ?int {
        $userId = (int)($_SESSION['user']['id'] ?? 0);
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id FROM teachers WHERE user_id=:uid");
        $stmt->execute(['uid' => $userId]);
        $row = $stmt->fetch();
        return $row ? (int)$row['id'] : null;
    }

    public function dashboard() {
        $teacherId = $this->getTeacherId();
        $service = new ScheduleService();
        $schedule = $teacherId ? $service->getTeacherSchedule($teacherId) : [];
        $this->render('teacher/dashboard', [
            'title' => 'Dashboard Giáo viên',
            'portal' => 'teacher',
            'schedule' => array_slice($schedule, 0, 10),
            'teacherId' => $teacherId,
        ]);
    }

    public function schedule() {
        $teacherId = $this->getTeacherId();
        $week = ScheduleService::normalizeWeekStart($_GET['week'] ?? null);
        $service = new ScheduleService();
        $items = $teacherId ? $service->getTeacherSchedule($teacherId, $week) : [];
        $attRecords = $items ? $service->getAttendanceForSessions($items) : [];
        $attLookup = $service->buildAttendanceLookup($attRecords);
        $items = $service->attachAttendanceToSessions($items, $attLookup);
        $byDay = $service->groupSchedulesByDay($items);
        $this->render('teacher/schedule', [
            'title' => 'Lịch dạy',
            'portal' => 'teacher',
            'items' => $items,
            'byDay' => $byDay,
            'week' => $week,
        ]);
    }
}
