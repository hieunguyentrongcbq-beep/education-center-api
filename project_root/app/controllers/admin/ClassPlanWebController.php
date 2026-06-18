<?php

namespace App\Controllers\Admin;

use Core\WebController;
use App\Models\classmodel;
use Core\Database;

class ClassPlanWebController extends WebController {
    private $model;

    public function __construct() {
        $this->model = new classmodel();
    }

    private function formData(): array {
        $db = Database::getInstance()->getConnection();
        return [
            'courses' => $db->query("SELECT id, course_code, course_name FROM courses WHERE status='ACTIVE' ORDER BY course_code")->fetchAll(),
            'semesters' => $this->model->getAllSemesters(),
        ];
    }

    public function index() {
        $this->render('admin/class_plans/index', [
            'title' => 'Kế hoạch mở lớp',
            'portal' => 'admin',
            'plans' => $this->model->listClassPlansAdmin(),
        ]);
    }

    public function create() {
        $this->render('admin/class_plans/form', array_merge(
            ['title' => 'Thêm kế hoạch mở lớp', 'portal' => 'admin', 'plan' => null],
            $this->formData()
        ));
    }

    public function store() {
        $this->requirePost();
        $adminId = (int)($_SESSION['user']['id'] ?? 0);
        $result = $this->model->createClassPlan($_POST, $adminId ?: null);
        if (isset($result['error'])) {
            $this->setOld($_POST);
            $this->flash('error', $result['error']);
            $this->redirect('admin/class-plans/create');
        }
        $this->audit('CREATE_CLASS_PLAN', 'class_plans', $result['id'] ?? null);
        $this->flash('success', 'Đã tạo kế hoạch mở lớp');
        $this->redirect('admin/class-plans');
    }

    public function edit($params) {
        $id = (int)($params['id'] ?? 0);
        $plan = $this->model->getClassPlanById($id);
        if (!$plan) {
            $this->flash('error', 'Không tìm thấy kế hoạch');
            $this->redirect('admin/class-plans');
        }
        $this->render('admin/class_plans/form', array_merge(
            ['title' => 'Sửa kế hoạch mở lớp', 'portal' => 'admin', 'plan' => $plan],
            $this->formData()
        ));
    }

    public function update($params) {
        $this->requirePost();
        $id = (int)($params['id'] ?? 0);
        $result = $this->model->updateClassPlan($id, $_POST);
        if (isset($result['error'])) {
            $this->setOld($_POST);
            $this->flash('error', $result['error']);
            $this->redirect('admin/class-plans/' . $id . '/edit');
        }
        $this->audit('UPDATE_CLASS_PLAN', 'class_plans', $id);
        $this->flash('success', 'Đã cập nhật kế hoạch');
        $this->redirect('admin/class-plans');
    }

    public function delete($params) {
        $this->requirePost();
        $id = (int)($params['id'] ?? 0);
        $result = $this->model->deleteClassPlan($id);
        if (isset($result['error'])) {
            $this->flash('error', $result['error']);
        } else {
            $this->audit('DELETE_CLASS_PLAN', 'class_plans', $id);
            $this->flash('success', 'Đã xóa kế hoạch');
        }
        $this->redirect('admin/class-plans');
    }
}
