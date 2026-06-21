<?php

namespace App\Controllers\Admin;



use Core\WebController;

use App\Models\classmodel;

use App\Models\instructormodel;

use Core\Database;



class ScheduleWebController extends WebController {

    private $model;



    public function __construct() {

        $this->model = new classmodel();

    }



    public function index() {

        $classId = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;

        $schedules = $classId ? $this->model->getSchedulesByClass($classId) : [];

        $classes = $this->model->getAllClasses(1, 100);

        $students = $this->model->getAllStudents(200);

        $this->render('admin/schedules/index', [

            'title' => 'Lịch học / Thi / Học bù',

            'portal' => 'admin',

            'schedules' => $schedules,

            'classes' => $classes,

            'students' => $students,

            'classId' => $classId,

        ]);

    }



    public function edit($params) {

        $id = (int)($params['id'] ?? 0);

        $schedule = $this->model->getScheduleById($id);

        if (!$schedule) {

            $this->flash('error', 'Không tìm thấy lịch');

            $this->redirect('admin/schedules');

        }



        $db = Database::getInstance()->getConnection();

        $students = $db->prepare("

            SELECT s.id, s.student_code, u.full_name

            FROM students s

            JOIN users u ON s.user_id = u.id

            JOIN enrollments e ON e.student_id = s.id AND e.class_id = :cid AND e.payment_status = 'PAID'

        ");

        $students->execute(['cid' => (int)$schedule['class_id']]);

        $students = $students->fetchAll();



        $this->render('admin/schedules/form', [

            'title' => 'Sửa lịch',

            'portal' => 'admin',

            'schedule' => $schedule,

            'students' => $students,

        ]);

    }



    public function store() {

        $this->requirePost();

        $result = $this->model->createSchedule($_POST);

        if (isset($result['error'])) {

            $this->setOld($_POST);

            $this->flash('error', $result['error']);

        } else {

            $classId = (int)$_POST['class_id'];

            (new instructormodel())->notifyClassScheduleChange($classId, $_POST, !empty($result['updated']));
            $this->audit(
                !empty($result['updated']) ? 'UPDATE_SCHEDULE' : 'CREATE_SCHEDULE',
                'schedules',
                $result['id'] ?? null
            );

            $this->flash('success', !empty($result['updated']) ? 'Đã cập nhật lịch' : 'Đã tạo lịch');

        }

        $this->redirect('admin/schedules?class_id=' . (int)($_POST['class_id'] ?? 0));

    }



    public function update($params) {

        $this->requirePost();

        $id = (int)($params['id'] ?? 0);

        $result = $this->model->updateSchedule($id, $_POST);

        if (isset($result['error'])) {

            $this->setOld($_POST);

            $this->flash('error', $result['error']);

            $this->redirect('admin/schedules/' . $id . '/edit');

        }



        $classId = (int)($result['class_id'] ?? $_POST['class_id'] ?? 0);

        (new instructormodel())->notifyClassScheduleChange($classId, $_POST, true);
        $this->audit('UPDATE_SCHEDULE', 'schedules', $id);

        $this->flash('success', 'Đã cập nhật lịch');

        $this->redirect('admin/schedules?class_id=' . $classId);

    }



    public function delete($params) {

        $this->requirePost();

        $id = (int)($params['id'] ?? 0);

        $classId = (int)($_POST['class_id'] ?? 0);

        $schedule = $this->model->getScheduleById($id);

        $result = $this->model->deleteSchedule($id);

        if (isset($result['error'])) {

            $this->flash('error', $result['error']);

        } else {
            if ($schedule) {
                (new instructormodel())->notifyClassScheduleDeleted(
                    (int)$schedule['class_id'],
                    $schedule
                );
            }
            $this->audit('DELETE_SCHEDULE', 'schedules', $id);
            $this->flash('success', 'Đã xóa lịch');

        }

        $this->redirect('admin/schedules?class_id=' . $classId);

    }

}


