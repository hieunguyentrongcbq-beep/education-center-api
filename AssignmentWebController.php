<?php

namespace App\Controllers\Admin;



use Core\WebController;

use App\Models\assigning;

use App\Models\classmodel;

use App\Models\instructormodel;

use App\Models\ScheduleService;

use Core\Database;



class AssignmentWebController extends WebController {

    public function index() {

        $model = new assigning();

        $classId = isset($_GET['class_id']) ? (int)$_GET['class_id'] : null;

        $assignments = $model->getAllAssignments($classId);

        $classes = (new classmodel())->getAllClasses(1, 100);

        $db = Database::getInstance()->getConnection();

        $teachers = $db->query("SELECT t.id, t.teacher_code, u.full_name FROM teachers t JOIN users u ON t.user_id=u.id")->fetchAll();

        $students = [];
        $selectedClass = null;
        $classTeachingDays = [];

        if ($classId) {
            $stuStmt = $db->prepare("
                SELECT s.id, s.student_code, u.full_name
                FROM students s
                JOIN users u ON s.user_id = u.id
                JOIN enrollments e ON e.student_id = s.id AND e.class_id = :cid
                WHERE e.payment_status = 'PAID' AND e.status = 'ACTIVE'
                ORDER BY s.student_code
            ");
            $stuStmt->execute(['cid' => $classId]);
            $students = $stuStmt->fetchAll();

            $classStmt = $db->prepare("
                SELECT c.id, c.class_code, co.course_name, co.day_primary, co.day_secondary
                FROM classes c
                JOIN courses co ON c.course_id = co.id
                WHERE c.id = :id
            ");
            $classStmt->execute(['id' => $classId]);
            $selectedClass = $classStmt->fetch() ?: null;
            $classTeachingDays = ScheduleService::classTeachingDays($selectedClass);
        }

        $this->render('admin/assignments/index', [

            'title' => 'Phân công giáo viên',

            'portal' => 'admin',

            'assignments' => $assignments,

            'classes' => $classes,

            'teachers' => $teachers,

            'students' => $students,

            'classId' => $classId,

            'selectedClass' => $selectedClass,

            'classTeachingDays' => $classTeachingDays,

            'weekDayLabels' => ScheduleService::weekDayLabels(),

            'weekDayOrder' => ScheduleService::weekDayOrder(),

        ]);

    }



    public function edit($params) {

        $id = (int)($params['id'] ?? 0);

        $model = new assigning();

        $assignment = $model->getAssignmentById($id);

        if (!$assignment) {

            $this->flash('error', 'Không tìm thấy phân công');

            $this->redirect('admin/assignments');

        }



        $db = Database::getInstance()->getConnection();

        $teachers = $db->query("SELECT t.id, t.teacher_code, u.full_name FROM teachers t JOIN users u ON t.user_id=u.id")->fetchAll();

        $students = $db->prepare("

            SELECT s.id, s.student_code, u.full_name

            FROM students s

            JOIN users u ON s.user_id = u.id

            JOIN enrollments e ON e.student_id = s.id AND e.class_id = :cid
                AND e.payment_status = 'PAID' AND e.status = 'ACTIVE'

        ");

        $students->execute(['cid' => (int)$assignment['class_id']]);

        $students = $students->fetchAll();

        $classStmt = $db->prepare("
            SELECT c.id, c.class_code, co.course_name, co.day_primary, co.day_secondary
            FROM classes c
            JOIN courses co ON c.course_id = co.id
            WHERE c.id = :id
        ");
        $classStmt->execute(['id' => (int)$assignment['class_id']]);
        $selectedClass = $classStmt->fetch() ?: null;

        $this->render('admin/assignments/form', [

            'title' => 'Sửa phân công GV',

            'portal' => 'admin',

            'assignment' => $assignment,

            'teachers' => $teachers,

            'students' => $students,

            'classTeachingDays' => ScheduleService::classTeachingDays($selectedClass),

            'weekDayLabels' => ScheduleService::weekDayLabels(),

            'weekDayOrder' => ScheduleService::weekDayOrder(),

        ]);

    }



    public function store() {

        $this->requirePost();

        $model = new assigning();

        $adminId = (int)($_SESSION['user']['id'] ?? 0);

        $result = $model->assignTeacher($_POST, $adminId);

        if (isset($result['error'])) {

            $this->flash('error', $result['error']);

        } else {

            $this->notifyTeacherAssigned((int)$_POST['teacher_id']);
            $this->audit('CREATE_ASSIGNMENT', 'teacher_assignments', $result['id'] ?? null);

            $this->flash('success', 'Đã phân công giáo viên');

        }

        $this->redirect('admin/assignments?class_id=' . (int)($_POST['class_id'] ?? 0));

    }



    public function update($params) {

        $this->requirePost();

        $id = (int)($params['id'] ?? 0);

        $model = new assigning();

        $result = $model->updateAssignment($id, $_POST);

        if (isset($result['error'])) {

            $this->flash('error', $result['error']);

            $this->redirect('admin/assignments/' . $id . '/edit');

        }



        if (!empty($result['teacher_id']) && (int)$result['teacher_id'] !== (int)($result['previous_teacher_id'] ?? 0)) {

            $this->notifyTeacherAssigned((int)$result['teacher_id'], true);

        }



        $this->audit('UPDATE_ASSIGNMENT', 'teacher_assignments', $id);

        $this->flash('success', 'Đã cập nhật phân công');

        $this->redirect('admin/assignments?class_id=' . (int)($result['class_id'] ?? 0));

    }



    public function delete($params) {

        $this->requirePost();

        $id = (int)($params['id'] ?? 0);

        $classId = (int)($_POST['class_id'] ?? 0);

        $result = (new assigning())->deleteAssignment($id);

        if (isset($result['error'])) {

            $this->flash('error', $result['error']);

        } else {
            $this->audit('DELETE_ASSIGNMENT', 'teacher_assignments', $id);
            $this->flash('success', 'Đã xóa phân công');

        }

        $redirect = $classId ? 'admin/assignments?class_id=' . $classId : 'admin/assignments';

        $this->redirect($redirect);

    }



    public function teachingSchedule() {
        $teacherId = isset($_GET['teacher_id']) && $_GET['teacher_id'] !== ''
            ? (int)$_GET['teacher_id'] : null;
        $classId = isset($_GET['class_id']) && $_GET['class_id'] !== ''
            ? (int)$_GET['class_id'] : null;
        $dayOfWeek = isset($_GET['day_of_week']) && $_GET['day_of_week'] !== ''
            ? (int)$_GET['day_of_week'] : null;

        $model = new assigning();
        $rows = $model->listTeachingSchedule($teacherId, $classId, $dayOfWeek);

        $db = Database::getInstance()->getConnection();
        $teachers = $db->query("
            SELECT t.id, t.teacher_code, u.full_name
            FROM teachers t
            JOIN users u ON t.user_id = u.id
            WHERE t.status = 'ACTIVE'
            ORDER BY t.teacher_code
        ")->fetchAll();
        $classes = (new classmodel())->getAllClasses(1, 200);

        $this->render('admin/assignments/teaching_schedule', [
            'title' => 'Lịch dạy giáo viên',
            'portal' => 'admin',
            'rows' => $rows,
            'teachers' => $teachers,
            'classes' => $classes,
            'teacherId' => $teacherId,
            'classId' => $classId,
            'dayOfWeek' => $dayOfWeek,
            'weekDayLabels' => ScheduleService::weekDayLabels(),
            'weekDayOrder' => ScheduleService::weekDayOrder(),
        ]);
    }

    private function notifyTeacherAssigned(int $teacherId, bool $updated = false): void {

        $db = Database::getInstance()->getConnection();

        $tv = $db->prepare("SELECT u.id FROM teachers t JOIN users u ON t.user_id=u.id WHERE t.id=:id");

        $tv->execute(['id' => $teacherId]);

        if ($u = $tv->fetch()) {

            $title = $updated ? 'Phân công được cập nhật' : 'Phân công mới';

            $content = $updated ? 'Phân công dạy của bạn đã được Admin cập nhật.' : 'Bạn được phân công dạy thêm.';

            (new instructormodel())->createNotification($u['id'], $title, $content);

        }

    }

}


