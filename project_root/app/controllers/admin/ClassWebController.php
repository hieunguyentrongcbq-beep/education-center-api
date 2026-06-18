<?php
namespace App\Controllers\Admin;

use Core\WebController;
use App\Models\classmodel;
use App\Models\ScheduleService;
use Core\Database;

class ClassWebController extends WebController {
    private $model;

    public function __construct() {
        $this->model = new classmodel();
    }

    private function formData(): array {
        $db = Database::getInstance()->getConnection();
        return [
            'courses' => $db->query("SELECT id, course_code, course_name, total_sessions FROM courses WHERE status='ACTIVE'")->fetchAll(),
            'semesters' => $this->model->getAllSemesters(),
            'classrooms' => $this->model->getAllClassrooms(),
            'teachers' => $db->query("SELECT t.id, t.teacher_code, u.full_name FROM teachers t JOIN users u ON t.user_id=u.id WHERE t.status='ACTIVE'")->fetchAll(),
        ];
    }

    public function index() {
        $classes = $this->model->getAllClasses(1, 100);
        $this->render('admin/classes/index', ['title' => 'Lớp học', 'portal' => 'admin', 'classes' => $classes]);
    }

    public function create() {
        $prefill = null;
        if (!empty($_GET['course_id']) || !empty($_GET['semester_id']) || !empty($_GET['max_students'])) {
            $prefill = [
                'course_id'    => (int)($_GET['course_id'] ?? 0) ?: null,
                'semester_id'  => (int)($_GET['semester_id'] ?? 0) ?: null,
                'max_students' => (int)($_GET['max_students'] ?? 0) ?: null,
            ];
        }
        $this->render('admin/classes/form', array_merge(
            ['title' => 'Mở lớp học', 'portal' => 'admin', 'class' => $prefill],
            $this->formData()
        ));
    }

    public function store() {
        $this->requirePost();
        $data = $_POST;
        if (empty($data['end_date']) && !empty($data['start_date']) && !empty($data['course_id'])) {
            $db = Database::getInstance()->getConnection();
            $c = $db->prepare("SELECT total_sessions FROM courses WHERE id=:id");
            $c->execute(['id' => $data['course_id']]);
            $course = $c->fetch();
            $data['end_date'] = ScheduleService::calcEndDate($data['start_date'], (int)($course['total_sessions'] ?? 20));
        }
        $result = $this->model->createClass($data);
        if (isset($result['error'])) {
            $this->setOld($data);
            $this->flash('error', $result['error']);
            $this->redirect('admin/classes/create');
        }
        $this->audit('CREATE_CLASS', 'classes', $result['id'] ?? null);
        $this->flash('success', 'Đã tạo lớp học. Kết thúc: ' . ($data['end_date'] ?? ''));
        $this->redirect('admin/classes');
    }

    public function edit($params) {
        $id = (int)($params['id'] ?? 0);
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM classes WHERE id=:id");
        $stmt->execute(['id' => $id]);
        $class = $stmt->fetch();
        if (!$class) { $this->flash('error', 'Không tìm thấy'); $this->redirect('admin/classes'); }
        $this->render('admin/classes/form', array_merge(
            ['title' => 'Sửa lớp học', 'portal' => 'admin', 'class' => $class],
            $this->formData()
        ));
    }

    public function update($params) {
        $this->requirePost();
        $id = (int)($params['id'] ?? 0);
        $result = $this->model->updateClass($id, $_POST);
        if (isset($result['error'])) {
            $this->setOld($_POST);
            $this->flash('error', $result['error']);
            $this->redirect("admin/classes/$id/edit");
        }
        $this->audit('UPDATE_CLASS', 'classes', $id);
        $this->flash('success', 'Đã cập nhật lớp học');
        $this->redirect('admin/classes');
    }

    public function delete($params) {
        $this->requirePost();
        $id = (int)($params['id'] ?? 0);
        $result = $this->model->deleteClass($id);
        if (!isset($result['error'])) {
            $this->audit('DELETE_CLASS', 'classes', $id);
        }
        $this->flash(isset($result['error']) ? 'error' : 'success', $result['error'] ?? 'Đã xóa lớp');
        $this->redirect('admin/classes');
    }
}
