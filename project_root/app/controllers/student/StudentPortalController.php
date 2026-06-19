<?php
namespace App\Controllers\Student;

use Core\WebController;
use App\Models\ScheduleService;
use Core\Database;

class StudentPortalController extends WebController {
    private function getStudentId(): ?int {
        $userId = (int)($_SESSION['user']['id'] ?? 0);
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id FROM students WHERE user_id=:uid");
        $stmt->execute(['uid' => $userId]);
        $row = $stmt->fetch();
        return $row ? (int)$row['id'] : null;
    }

    public function dashboard() {
        $studentId = $this->getStudentId();
        $service = new ScheduleService();
        $schedule = $studentId ? $service->getStudentScheduleForWeek($studentId) : [];
        $this->render('student/dashboard', [
            'title' => 'Dashboard Học viên',
            'portal' => 'student',
            'schedule' => array_slice($schedule, 0, 8),
            'studentId' => $studentId,
        ]);
    }

    public function schedule() {
        $studentId = $this->getStudentId();
        $week = ScheduleService::normalizeWeekStart($_GET['week'] ?? null);
        $service = new ScheduleService();
        $items = $studentId ? $service->getStudentScheduleForWeek($studentId, $week) : [];
        $p2 = new \App\Models\Phase2Service();
        $attendance = $studentId ? $p2->getStudentAttendanceStatus($studentId) : [];
        $attLookup = $service->buildAttendanceLookup($attendance);
        $items = $service->attachAttendanceToSessions($items, $attLookup, $studentId);
        $attMap = [];
        foreach ($attendance as $a) {
            $date = ScheduleService::normalizeDateOnly($a['attendance_date'] ?? null);
            if ($date) {
                $attMap[$a['class_id'] . '_' . $date] = $a['attendance_status'];
            }
        }
        $byDay = $service->groupSchedulesByDay($items);
        $this->render('student/schedule', [
            'title' => 'Lịch học',
            'portal' => 'student',
            'items' => $items,
            'byDay' => $byDay,
            'week' => $week,
            'attendance' => $attendance,
            'attMap' => $attMap,
        ]);
    }
}
