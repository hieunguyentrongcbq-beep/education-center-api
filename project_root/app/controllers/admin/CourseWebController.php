<?php
namespace App\Controllers\Admin;

use Core\WebController;
use App\Models\classmodel;
use App\Models\ScheduleService;
use Core\Database;

class CourseWebController extends WebController {
    private $model;

    public function __construct() {
        $this->model = new classmodel();
    }

    public function index() {
        $courses = $this->model->getAllCourses(1, 100);
        $this->render('admin/courses/index', ['title' => 'Khóa học', 'portal' => 'admin', 'courses' => $courses]);
    }

    public function create() {
        $this->render('admin/courses/form', ['title' => 'Thêm khóa học', 'portal' => 'admin', 'course' => null]);
    }

    public function store() {
        $this->requirePost();
        $data = $_POST;
        $result = $this->model->createCourse($data);
        if (isset($result['error'])) {
            $this->setOld($data);
            $this->flash('error', $result['error']);
            $this->redirect('admin/courses/create');
        }
        $this->audit('CREATE_COURSE', 'courses', $result['id'] ?? null);
        $this->flash('success', 'Đã tạo khóa học');
        $this->redirect('admin/courses');
    }

    public function edit($params) {
        $id = (int)($params['id'] ?? 0);
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM courses WHERE id=:id");
        $stmt->execute(['id' => $id]);
        $course = $stmt->fetch();
        if (!$course) { $this->flash('error', 'Không tìm thấy'); $this->redirect('admin/courses'); }
        $weeks = ScheduleService::calcDurationWeeks((int)$course['total_sessions']);
        $endPreview = ScheduleService::calcEndDate('2026-07-01', (int)$course['total_sessions']);
        $this->render('admin/courses/form', [
            'title' => 'Sửa khóa học', 'portal' => 'admin', 'course' => $course,
            'weeks' => $weeks, 'endPreview' => $endPreview,
        ]);
    }

    public function update($params) {
        $this->requirePost();
        $id = (int)($params['id'] ?? 0);
        $result = $this->model->updateCourse($id, $_POST);
        if (isset($result['error'])) {
            $this->setOld($_POST);
            $this->flash('error', $result['error']);
            $this->redirect("admin/courses/$id/edit");
        }
        $this->audit('UPDATE_COURSE', 'courses', $id);
        $this->flash('success', 'Đã cập nhật khóa học');
        $this->redirect('admin/courses');
    }

    public function delete($params) {
        $this->requirePost();
        $id = (int)($params['id'] ?? 0);
        $result = $this->model->deleteCourse($id);
        if (!isset($result['error'])) {
            $this->audit('DELETE_COURSE', 'courses', $id);
        }
        $this->flash(isset($result['error']) ? 'error' : 'success', $result['error'] ?? 'Đã xóa khóa học');
        $this->redirect('admin/courses');
    }
}
