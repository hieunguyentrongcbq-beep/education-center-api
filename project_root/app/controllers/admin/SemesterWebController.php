<?php
namespace App\Controllers\Admin;

use Core\WebController;
use App\Models\classmodel;

class SemesterWebController extends WebController {
    private $model;

    public function __construct() {
        $this->model = new classmodel();
    }

    public function index() {
        $this->render('admin/semesters/index', [
            'title' => 'Học kỳ',
            'portal' => 'admin',
            'semesters' => $this->model->listSemestersAdmin(),
        ]);
    }

    public function create() {
        $this->render('admin/semesters/form', [
            'title' => 'Thêm học kỳ',
            'portal' => 'admin',
            'semester' => null,
        ]);
    }

    public function store() {
        $this->requirePost();
        $result = $this->model->createSemester($_POST);
        if (isset($result['error'])) {
            $this->setOld($_POST);
            $this->flash('error', $result['error']);
            $this->redirect('admin/semesters/create');
        }
        $this->audit('CREATE_SEMESTER', 'semesters', $result['id'] ?? null);
        $this->flash('success', 'Đã tạo học kỳ');
        $this->redirect('admin/semesters');
    }

    public function edit($params) {
        $id = (int)($params['id'] ?? 0);
        $semester = $this->model->getSemesterById($id);
        if (!$semester) {
            $this->flash('error', 'Không tìm thấy học kỳ');
            $this->redirect('admin/semesters');
        }
        $this->render('admin/semesters/form', [
            'title' => 'Sửa học kỳ',
            'portal' => 'admin',
            'semester' => $semester,
        ]);
    }

    public function update($params) {
        $this->requirePost();
        $id = (int)($params['id'] ?? 0);
        $result = $this->model->updateSemester($id, $_POST);
        if (isset($result['error'])) {
            $this->setOld($_POST);
            $this->flash('error', $result['error']);
            $this->redirect('admin/semesters/' . $id . '/edit');
        }
        $this->audit('UPDATE_SEMESTER', 'semesters', $id);
        $this->flash('success', 'Đã cập nhật học kỳ');
        $this->redirect('admin/semesters');
    }

    public function delete($params) {
        $this->requirePost();
        $id = (int)($params['id'] ?? 0);
        $result = $this->model->deleteSemester($id);
        if (isset($result['error'])) {
            $this->flash('error', $result['error']);
        } else {
            $this->audit('DELETE_SEMESTER', 'semesters', $id);
            $this->flash('success', 'Đã xóa học kỳ');
        }
        $this->redirect('admin/semesters');
    }
}
