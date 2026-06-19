<?php
namespace App\Controllers\Admin;

use Core\WebController;
use App\Models\Phase2Service;
use App\Models\classmodel;

class AdminPhase2Controller extends WebController {
    public function leaveRequests() {
        $p2 = new Phase2Service();
        $status = !empty($_GET['status']) ? $_GET['status'] : null;
        $type = !empty($_GET['type']) ? $_GET['type'] : null;
        $this->render('admin/leave_requests', [
            'title' => 'Duyệt xin nghỉ / Học bù', 'portal' => 'admin',
            'requests' => $p2->listLeaveRequests($status, $type),
            'filterStatus' => $status,
            'filterType' => $type,
        ]);
    }

    public function reviewLeave() {
        $this->requirePost();
        $p2 = new Phase2Service();
        $adminId = (int)($_SESSION['user']['id'] ?? 0);
        $status = $_POST['action'] === 'approve' ? 'APPROVED' : 'REJECTED';
        $r = $p2->reviewLeaveRequest((int)$_POST['id'], $status, $adminId);
        $msg = $r['error'] ?? ('Đã ' . ($status === 'APPROVED' ? 'duyệt' : 'từ chối') . ($r['message'] ?? ''));
        $this->flash(isset($r['error']) ? 'error' : 'success', $msg);
        $qs = [];
        if (!empty($_POST['filter_status'])) {
            $qs['status'] = $_POST['filter_status'];
        }
        if (!empty($_POST['filter_type'])) {
            $qs['type'] = $_POST['filter_type'];
        }
        $redirect = 'admin/leave-requests' . ($qs ? '?' . http_build_query($qs) : '');
        $this->redirect($redirect);
    }

    public function attendanceReport() {
        $p2 = new Phase2Service();
        $classId = isset($_GET['class_id']) ? (int)$_GET['class_id'] : null;
        $date = $_GET['date'] ?? null;
        $classes = (new classmodel())->getAllClasses(1, 100);
        $this->render('admin/attendance_report', [
            'title' => 'Báo cáo điểm danh / Chấm công', 'portal' => 'admin',
            'records' => $p2->getAttendanceReport($classId, $date),
            'classes' => $classes, 'classId' => $classId, 'date' => $date,
        ]);
    }

    public function reports() {
        $p2 = new Phase2Service();
        $this->render('admin/reports', [
            'title' => 'Thống kê HV & GV', 'portal' => 'admin',
            'teacherStats' => $p2->getTeacherStats(),
            'studentStats' => $p2->getStudentStats(),
        ]);
    }

    public function auditLogs() {
        $p2 = new Phase2Service();
        $filters = $this->auditLogFiltersFromRequest();
        $options = $p2->getAuditLogFilterOptions();
        $this->render('admin/audit_logs', [
            'title' => 'Nhật ký hệ thống',
            'portal' => 'admin',
            'logs' => $p2->getAuditLogs($filters),
            'filterAction' => $filters['action'] ?? '',
            'filterEntity' => $filters['entity_name'] ?? '',
            'filterDateFrom' => $filters['date_from'] ?? '',
            'filterDateTo' => $filters['date_to'] ?? '',
            'actionOptions' => $options['actions'],
            'entityOptions' => $options['entities'],
        ]);
    }

    public function exportAuditLogs() {
        $p2 = new Phase2Service();
        $filters = $this->auditLogFiltersFromRequest();
        $logs = $p2->getAuditLogs($filters, 5000);
        $rows = [];
        foreach ($logs as $log) {
            $rows[] = [
                $log['id'],
                $log['created_at'],
                $log['full_name'] ?? 'System',
                $log['email'] ?? '',
                $log['action'],
                $log['entity_name'],
                $log['entity_id'],
            ];
        }
        $this->sendCsv(
            'audit_logs_' . date('Y-m-d_His') . '.csv',
            ['ID', 'Thời gian', 'User', 'Email', 'Hành động', 'Entity', 'Entity ID'],
            $rows
        );
    }

    public function exportTeacherStats() {
        $p2 = new Phase2Service();
        $rows = [];
        foreach ($p2->getTeacherStats() as $t) {
            $rows[] = [
                $t['teacher_code'],
                $t['full_name'],
                $t['assignments'],
                $t['paid_sessions'],
                $t['avg_survey'] !== null ? round((float)$t['avg_survey'], 2) : '',
            ];
        }
        $this->sendCsv(
            'thong_ke_giao_vien_' . date('Y-m-d') . '.csv',
            ['Mã GV', 'Họ tên', 'Phân công', 'Buổi chấm công', 'ĐTB khảo sát'],
            $rows
        );
    }

    public function exportStudentStats() {
        $p2 = new Phase2Service();
        $rows = [];
        foreach ($p2->getStudentStats() as $s) {
            $rows[] = [
                $s['student_code'],
                $s['full_name'],
                $s['classes_joined'],
                $s['avg_score'] !== null ? round((float)$s['avg_score'], 2) : '',
                $s['present_count'],
            ];
        }
        $this->sendCsv(
            'thong_ke_hoc_vien_' . date('Y-m-d') . '.csv',
            ['Mã HV', 'Họ tên', 'Số lớp', 'ĐTB điểm', 'Buổi có mặt'],
            $rows
        );
    }

    private function auditLogFiltersFromRequest(): array {
        $filters = [];
        if (!empty($_GET['action'])) {
            $filters['action'] = trim($_GET['action']);
        }
        if (!empty($_GET['entity_name'])) {
            $filters['entity_name'] = trim($_GET['entity_name']);
        }
        if (!empty($_GET['date_from'])) {
            $filters['date_from'] = $_GET['date_from'];
        }
        if (!empty($_GET['date_to'])) {
            $filters['date_to'] = $_GET['date_to'];
        }
        return $filters;
    }
}
