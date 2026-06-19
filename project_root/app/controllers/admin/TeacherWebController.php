<?php
namespace App\Controllers\Admin;

use Core\WebController;
use App\Models\instructormodel;
use Core\Database;

class TeacherWebController extends WebController {
    private $model;

    public function __construct() {
        $this->model = new instructormodel();
    }

    public function index() {
        $teachers = $this->model->getAllTeachers(1, 100);
        $this->render('admin/teachers/index', ['title' => 'Giáo viên', 'portal' => 'admin', 'teachers' => $teachers]);
    }

    public function create() {
        $this->render('admin/teachers/form', ['title' => 'Thêm giáo viên', 'portal' => 'admin', 'teacher' => null]);
    }

    public function store() {
        $this->requirePost();
        $result = $this->model->createTeacher($_POST);
        if (isset($result['error'])) {
            $this->setOld($_POST);
            $this->flash('error', $result['error']);
            $this->redirect('admin/teachers/create');
        }
        $this->audit('CREATE_TEACHER', 'teachers', $result['id'] ?? null);
        $this->flash('success', 'Đã tạo giáo viên');
        $this->redirect('admin/teachers');
    }

    public function edit($params) {
        $id = (int)($params['id'] ?? 0);
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT t.*, u.full_name, u.email, u.phone FROM teachers t JOIN users u ON t.user_id=u.id WHERE t.id=:id");
        $stmt->execute(['id' => $id]);
        $teacher = $stmt->fetch();
        if (!$teacher) { $this->flash('error', 'Không tìm thấy'); $this->redirect('admin/teachers'); }
        $this->render('admin/teachers/form', ['title' => 'Sửa giáo viên', 'portal' => 'admin', 'teacher' => $teacher]);
    }

    public function update($params) {
        $this->requirePost();
        $id = (int)($params['id'] ?? 0);
        $result = $this->model->updateTeacher($id, $_POST);
        if (isset($result['error'])) {
            $this->setOld($_POST);
            $this->flash('error', $result['error']);
            $this->redirect("admin/teachers/$id/edit");
        }
        $this->audit('UPDATE_TEACHER', 'teachers', $id);
        $this->flash('success', 'Đã cập nhật giáo viên');
        $this->redirect('admin/teachers');
    }

    public function delete($params) {
        $this->requirePost();
        $id = (int)($params['id'] ?? 0);
        $result = $this->model->deleteTeacher($id);
        if (!isset($result['error'])) {
            $this->audit('DELETE_TEACHER', 'teachers', $id);
        }
        $this->flash(isset($result['error']) ? 'error' : 'success', $result['error'] ?? 'Đã xóa');
        $this->redirect('admin/teachers');
    }
}
