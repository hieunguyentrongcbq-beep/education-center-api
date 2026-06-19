<?php
namespace App\Controllers\Student;

use Core\WebController;
use App\Models\Phase2Service;
use Core\Database;

class StudentPhase2Controller extends WebController {
    private function studentId() {
        $uid = (int)($_SESSION['user']['id'] ?? 0);
        $db = Database::getInstance()->getConnection();
        $s = $db->prepare("SELECT id FROM students WHERE user_id=:u");
        $s->execute(['u' => $uid]);
        $r = $s->fetch();
        return $r ? (int)$r['id'] : null;
    }

    public function attendance() {
        $sid = $this->studentId();
        $p2 = new Phase2Service();
        $records = $sid ? $p2->getStudentAttendanceStatus($sid) : [];
        $this->render('student/attendance', [
            'title' => 'Điểm danh của tôi', 'portal' => 'student', 'records' => $records,
        ]);
    }

    public function submissions() {
        $sid = $this->studentId();
        $p2 = new Phase2Service();
        $db = Database::getInstance()->getConnection();
        $classes = [];
        if ($sid) {
            $stmt = $db->prepare("
                SELECT c.id, c.class_code, co.course_name FROM enrollments e
                JOIN classes c ON e.class_id=c.id JOIN courses co ON c.course_id=co.id
                WHERE e.student_id=:sid AND e.payment_status='PAID'
            ");
            $stmt->execute(['sid' => $sid]);
            $classes = $stmt->fetchAll();
        }
        $this->render('student/submissions', [
            'title' => 'Nộp bài tập', 'portal' => 'student',
            'classes' => $classes,
            'submissions' => $sid ? $p2->getSubmissionsByStudent($sid) : [],
        ]);
    }

    public function upload() {
        $this->requirePost();
        $sid = $this->studentId();
        $path = $this->uploadPdf('file');
        if (!$path) {
            $this->flash('error', 'Chỉ chấp nhận file PDF tối đa 5MB');
            $this->redirect('student/submissions');
        }
        $p2 = new Phase2Service();
        $r = $p2->saveSubmission($sid, (int)$_POST['class_id'], $_POST['type'] ?? 'ASSIGNMENT', $path);
        $this->flash(isset($r['error']) ? 'error' : 'success', $r['error'] ?? 'Đã nộp bài');
        $this->redirect('student/submissions');
    }

    public function downloadSubmission($params) {
        $sid = $this->studentId();
        $id = (int)($params['id'] ?? 0);
        if (!$sid || !$id) {
            $this->flash('error', 'Không tìm thấy bài nộp');
            $this->redirect('student/submissions');
        }

        $p2 = new Phase2Service();
        $sub = $p2->getSubmissionForStudent($id, $sid);
        if (!$sub || empty($sub['file_path'])) {
            $this->flash('error', 'File PDF không tồn tại hoặc bạn không có quyền tải');
            $this->redirect('student/submissions');
        }

        $this->audit('DOWNLOAD_SUBMISSION', 'submissions', $id);
        $this->sendUploadedPdf($sub['file_path'], Phase2Service::submissionDownloadFilename($sub));
    }

    public function results() {
        $sid = $this->studentId();
        $p2 = new Phase2Service();
        $evaluations = $sid ? $p2->getEvaluation($sid) : [];
        $this->render('student/results', [
            'title' => 'Điểm & Nhận xét', 'portal' => 'student',
            'evaluations' => $evaluations, 'p2' => $p2,
        ]);
    }

    public function compare() {
        $sid = $this->studentId();
        $p2 = new Phase2Service();
        $classId = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
        $db = Database::getInstance()->getConnection();
        $classes = [];
        if ($sid) {
            $stmt = $db->prepare("SELECT c.id, c.class_code FROM enrollments e JOIN classes c ON e.class_id=c.id WHERE e.student_id=:sid AND e.payment_status='PAID'");
            $stmt->execute(['sid' => $sid]);
            $classes = $stmt->fetchAll();
            if (!$classId && $classes) $classId = (int)$classes[0]['id'];
        }
        $comparison = ($classId) ? $p2->getClassScoreComparison($classId) : [];
        $myRank = null;
        foreach ($comparison as $row) {
            if ((int)$row['student_id'] === $sid) { $myRank = $row; break; }
        }
        $this->render('student/compare', [
            'title' => 'So sánh điểm', 'portal' => 'student',
            'classes' => $classes, 'classId' => $classId,
            'comparison' => $comparison, 'myRank' => $myRank, 'p2' => $p2,
        ]);
    }

    public function leave() {
        $sid = $this->studentId();
        $p2 = new Phase2Service();
        $this->render('student/leave', [
            'title' => 'Xin nghỉ / Học bù',
            'portal' => 'student',
            'classes' => $sid ? $p2->getStudentEnrolledClasses($sid) : [],
            'requests' => $sid ? $p2->listMyLeaveRequests('STUDENT', $sid) : [],
        ]);
    }

    public function submitLeave() {
        $this->requirePost();
        $sid = $this->studentId();
        if (!$sid) {
            $this->flash('error', 'Không tìm thấy học viên');
            $this->redirect('student/leave');
        }
        $p2 = new Phase2Service();
        $type = ($_POST['request_type'] ?? '') === 'MAKEUP' ? 'MAKEUP' : 'LEAVE';
        $r = $p2->createLeaveRequest(
            $type,
            'STUDENT',
            $sid,
            (int)($_POST['class_id'] ?? 0),
            $_POST['request_date'] ?? '',
            $_POST['reason'] ?? ''
        );
        $this->flash(isset($r['error']) ? 'error' : 'success', $r['error'] ?? 'Đã gửi yêu cầu — Admin sẽ duyệt');
        $this->redirect('student/leave');
    }

    public function survey() {
        $sid = $this->studentId();
        $p2 = new Phase2Service();
        $classes = $sid ? $p2->getSurveyableClasses($sid) : [];
        $this->render('student/survey', [
            'title' => 'Khảo sát giáo viên', 'portal' => 'student', 'classes' => $classes,
        ]);
    }

    public function submitSurvey() {
        $this->requirePost();
        $sid = $this->studentId();
        $p2 = new Phase2Service();
        $classId = (int)$_POST['class_id'];
        $survey = $p2->getOrCreateSurvey($classId);
        $r = $p2->submitSurvey((int)$survey['id'], $sid, (int)$_POST['teacher_id'], (int)$_POST['rating'], $_POST['comment'] ?? '');
        $this->flash(isset($r['error']) ? 'error' : 'success', $r['error'] ?? 'Cảm ơn bạn đã khảo sát');
        $this->redirect('student/survey');
    }
}
