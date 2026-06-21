<?php

namespace App\Controllers\Teacher;



use Core\WebController;

use App\Models\classmodel;

use App\Models\Phase2Service;

use App\Models\instructormodel;

use App\Models\assigning;

use Core\Database;



class TeacherPhase2Controller extends WebController {

    private function teacherId() {

        $uid = (int)($_SESSION['user']['id'] ?? 0);

        $db = Database::getInstance()->getConnection();

        $s = $db->prepare("SELECT id FROM teachers WHERE user_id=:u");

        $s->execute(['u' => $uid]);

        $r = $s->fetch();

        return $r ? (int)$r['id'] : null;

    }



    /** Chặn truy cập lớp nếu GV không được phân công (T13). */

    private function assertTeacherClassAccess(?int $teacherId, int $classId, string $redirectPath): void {

        if (!$teacherId) {

            $this->flash('error', 'Không tìm thấy tài khoản giáo viên');

            $this->redirect($redirectPath);

        }

        if (!$classId) {

            return;

        }

        $assign = new assigning();

        if (!$assign->isTeacherAssignedToClass($teacherId, $classId)) {

            $this->flash('error', 'Bạn không được phân công lớp này');

            $this->redirect($redirectPath);

        }

    }



    public function attendance() {

        $tid = $this->teacherId();

        $p2 = new Phase2Service();

        $classId = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;

        $date = $_GET['date'] ?? date('Y-m-d');



        if ($classId && $tid) {

            $assign = new assigning();

            if (!$assign->isTeacherAssignedToClass($tid, $classId)) {

                $this->flash('error', 'Bạn không được phân công lớp này');

                $classId = 0;

            }

        }



        $classes = $tid ? $p2->getTeacherClasses($tid) : [];

        $students = ($tid && $classId) ? $p2->getStudentsInClassForTeacher($classId, $tid) : [];

        $classModel = new classmodel();

        $marked = $classId ? $classModel->getAttendanceByClassAndDate($classId, $date) : [];

        $markedMap = [];

        foreach ($marked as $m) {

            $markedMap[$m['student_id']] = $m;

        }



        $this->render('teacher/attendance', [

            'title' => 'Điểm danh', 'portal' => 'teacher',

            'classes' => $classes, 'classId' => $classId, 'date' => $date,

            'students' => $students, 'markedMap' => $markedMap,

        ]);

    }



    public function markAttendance() {

        $this->requirePost();

        $tid = $this->teacherId();

        $classId = (int)($_POST['class_id'] ?? 0);

        $date = $_POST['date'] ?? date('Y-m-d');

        $this->assertTeacherClassAccess($tid, $classId, 'teacher/attendance');



        $assign = new assigning();

        $classModel = new classmodel();

        $notify = new instructormodel();

        $adminNotified = false;



        foreach ($_POST['status'] ?? [] as $studentId => $status) {

            $studentId = (int)$studentId;

            if (!$assign->isTeacherAssignedToStudent($tid, $classId, $studentId)) {

                $this->flash('error', 'Không có quyền điểm danh học viên này');

                $this->redirect('teacher/attendance?class_id=' . $classId . '&date=' . urlencode($date));

            }



            $teacherPresent = isset($_POST['teacher_present']) ? 1 : 0;

            $markResult = $classModel->markAttendance([

                'class_id' => $classId,

                'student_id' => $studentId,

                'attendance_date' => $date,

                'attendance_status' => $status,

                'teacher_present' => $teacherPresent,

                'note' => $_POST['note'][$studentId] ?? '',

            ]);

            if (isset($markResult['error'])) {

                $this->flash('error', $markResult['error']);

                $this->redirect('teacher/attendance?class_id=' . $classId . '&date=' . urlencode($date));

            }

            if ($teacherPresent && $status === 'PRESENT' && !$adminNotified) {

                $db = Database::getInstance()->getConnection();

                $admins = $db->query("SELECT u.id FROM users u JOIN user_roles ur ON u.id=ur.user_id JOIN roles r ON ur.role_id=r.id WHERE r.role_name='ADMIN'")->fetchAll();

                foreach ($admins as $a) {

                    $notify->createNotification($a['id'], 'Chấm công GV', 'GV và HV đủ mặt lớp ' . $classId . ' ngày ' . $date);

                }

                $adminNotified = true;

            }

        }

        \App\Models\AuditLog::write($_SESSION['user']['id'] ?? null, 'MARK_ATTENDANCE', 'attendance', $classId);

        $this->flash('success', 'Đã lưu điểm danh');

        $this->redirect('teacher/attendance?class_id=' . $classId . '&date=' . urlencode($date));

    }



    public function leave() {

        $tid = $this->teacherId();

        $p2 = new Phase2Service();

        $this->render('teacher/leave', [

            'title' => 'Xin nghỉ / Học bù', 'portal' => 'teacher',

            'classes' => $tid ? $p2->getTeacherClasses($tid) : [],

            'requests' => $tid ? $p2->listMyLeaveRequests('TEACHER', $tid) : [],

        ]);

    }



    public function submitLeave() {

        $this->requirePost();

        $tid = $this->teacherId();

        if (!$tid) {

            $this->flash('error', 'Không tìm thấy GV');

            $this->redirect('teacher/leave');

        }

        $classId = (int)($_POST['class_id'] ?? 0);

        if ($classId) {

            $this->assertTeacherClassAccess($tid, $classId, 'teacher/leave');

        }

        $p2 = new Phase2Service();

        $type = $_POST['request_type'] === 'MAKEUP' ? 'MAKEUP' : 'LEAVE';

        $r = $p2->createLeaveRequest($type, 'TEACHER', $tid, $classId, $_POST['request_date'], $_POST['reason']);

        $this->flash(isset($r['error']) ? 'error' : 'success', $r['error'] ?? 'Đã gửi yêu cầu');

        $this->redirect('teacher/leave');

    }



    public function grading() {

        $tid = $this->teacherId();

        $p2 = new Phase2Service();

        $classId = isset($_GET['class_id']) ? (int)$_GET['class_id'] : null;



        if ($classId && $tid) {

            $assign = new assigning();

            if (!$assign->isTeacherAssignedToClass($tid, $classId)) {

                $this->flash('error', 'Bạn không được phân công lớp này');

                $classId = null;

            }

        }



        $submissions = $tid ? $p2->getSubmissionsForTeacher($tid, $classId) : [];

        $classes = $tid ? $p2->getTeacherClasses($tid) : [];



        $evaluations = ($classId && $tid) ? $p2->getClassEvaluationsForTeacher($classId, $tid) : [];



        $this->render('teacher/grading', [

            'title' => 'Chấm bài & Đánh giá', 'portal' => 'teacher',

            'submissions' => $submissions, 'classes' => $classes, 'classId' => $classId,

            'evaluations' => $evaluations, 'p2' => $p2,

        ]);

    }



    public function grade() {

        $this->requirePost();

        $tid = $this->teacherId();

        if (!$tid) {

            $this->flash('error', 'Không tìm thấy GV');

            $this->redirect('teacher/grading');

        }

        $submissionId = (int)($_POST['submission_id'] ?? 0);

        if (!$submissionId) {

            $this->flash('error', 'Thiếu bài nộp cần chấm');

            $this->redirect('teacher/grading');

        }

        $assign = new assigning();

        $sub = $assign->canTeacherGradeSubmission($tid, $submissionId);

        if (!$sub) {

            $this->flash('error', 'Không có quyền chấm bài lớp này');

            $this->redirect('teacher/grading');

        }

        $classId = (int)$sub['class_id'];

        $p2 = new Phase2Service();

        $r = $p2->gradeSubmission($submissionId, $tid, (float)$_POST['score'], $_POST['comment'] ?? '');

        $this->flash(isset($r['error']) ? 'error' : 'success', $r['error'] ?? 'Đã chấm điểm');

        $this->redirect('teacher/grading?class_id=' . $classId);

    }



    public function evaluate() {

        $this->requirePost();

        $tid = $this->teacherId();

        if (!$tid) {

            $this->flash('error', 'Không tìm thấy GV');

            $this->redirect('teacher/grading');

        }

        $classId = (int)($_POST['class_id'] ?? 0);

        $this->assertTeacherClassAccess($tid, $classId, 'teacher/grading');



        $p2 = new Phase2Service();

        $r = $p2->setEvaluationComment(

            (int)$_POST['student_id'], $classId, $tid,

            $_POST['teacher_comment'] ?? '',

            isset($_POST['retake_needed'])

        );

        $this->flash(isset($r['error']) ? 'error' : 'success', $r['error'] ?? 'Đã cập nhật đánh giá');

        $this->redirect('teacher/grading?class_id=' . $classId);

    }

}


