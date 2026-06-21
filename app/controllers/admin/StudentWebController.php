<?php
namespace App\Controllers\Admin;

use Core\WebController;
use App\Models\classmodel;

class StudentWebController extends WebController {
    private $model;

    public function __construct() {
        $this->model = new classmodel();
    }

    public function index() {
        $students = $this->model->getAllStudents(200, true);
        $this->render('admin/students/index', ['title' => 'Học viên', 'portal' => 'admin', 'students' => $students]);
    }

    public function create() {
        $this->render('admin/students/form', ['title' => 'Thêm học viên', 'portal' => 'admin', 'student' => null]);
    }

    public function store() {
        $this->requirePost();
        $result = $this->model->createStudent($_POST);
        if (isset($result['error'])) {
            $this->setOld($_POST);
            $this->flash('error', $result['error']);
            $this->redirect('admin/students/create');
        }
        $this->audit('CREATE_STUDENT', 'students', $result['student_id'] ?? null);
        $this->flash('success', 'Đã tạo học viên');
        $this->redirect('admin/students');
    }

    public function edit($params) {
        $id = (int)($params['id'] ?? 0);
        $students = $this->model->getAllStudents(200, true);
        $student = null;
        foreach ($students as $s) {
            if ((int)$s['id'] === $id) { $student = $s; break; }
        }
        if (!$student) { $this->flash('error', 'Không tìm thấy'); $this->redirect('admin/students'); }
        $this->render('admin/students/form', ['title' => 'Sửa học viên', 'portal' => 'admin', 'student' => $student]);
    }

    public function update($params) {
        $this->requirePost();
        $id = (int)($params['id'] ?? 0);
        $result = $this->model->updateStudent($id, $_POST);
        if (isset($result['error'])) {
            $this->setOld($_POST);
            $this->flash('error', $result['error']);
            $this->redirect("admin/students/$id/edit");
        }
        $this->audit('UPDATE_STUDENT', 'students', $id);
        $this->flash('success', 'Đã cập nhật học viên');
        $this->redirect('admin/students');
    }

    public function delete($params) {
        $this->requirePost();
        $id = (int)($params['id'] ?? 0);
        $result = $this->model->deleteStudent($id);
        if (!isset($result['error'])) {
            $this->audit('DELETE_STUDENT', 'students', $id);
        }
        $this->flash(isset($result['error']) ? 'error' : 'success', $result['error'] ?? 'Đã xóa');
        $this->redirect('admin/students');
    }
}
