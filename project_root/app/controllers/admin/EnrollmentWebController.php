<?php
namespace App\Controllers\Admin;

use Core\WebController;
use App\Models\classmodel;

class EnrollmentWebController extends WebController {
    private $model;

    public function __construct() {
        $this->model = new classmodel();
    }

    public function index() {
        $classId = isset($_GET['class_id']) ? (int)$_GET['class_id'] : null;
        $paymentFilter = !empty($_GET['payment_status']) ? $_GET['payment_status'] : null;

        $enrollments = $this->model->getAllEnrollments($classId ?: null);
        if ($paymentFilter) {
            $enrollments = array_values(array_filter($enrollments, function ($e) use ($paymentFilter) {
                return ($e['payment_status'] ?? '') === $paymentFilter;
            }));
        }

        $this->render('admin/enrollments/index', [
            'title' => 'Ghi danh lớp học',
            'portal' => 'admin',
            'enrollments' => $enrollments,
            'classes' => $this->model->getAllClasses(1, 200),
            'classId' => $classId,
            'paymentFilter' => $paymentFilter ?? '',
        ]);
    }

    public function create() {
        $this->render('admin/enrollments/form', [
            'title' => 'Ghi danh học viên',
            'portal' => 'admin',
            'enrollment' => null,
            'students' => $this->model->getAllStudents(300, true),
            'classes' => $this->model->getAllClasses(1, 200),
        ]);
    }

    public function store() {
        $this->requirePost();
        $result = $this->model->createEnrollment($_POST);
        if (isset($result['error'])) {
            $this->setOld($_POST);
            $this->flash('error', $result['error']);
            $this->redirect('admin/enrollments/create');
        }
        $this->audit('CREATE_ENROLLMENT', 'enrollments', $result['id'] ?? null);
        $this->flash('success', 'Đã ghi danh học viên. Dùng Thanh toán → Duyệt nếu cần tạo lịch tự động.');
        $this->redirect('admin/enrollments?class_id=' . (int)($_POST['class_id'] ?? 0));
    }

    public function edit($params) {
        $id = (int)($params['id'] ?? 0);
        $enrollment = $this->model->getEnrollmentById($id);
        if (!$enrollment) {
            $this->flash('error', 'Không tìm thấy ghi danh');
            $this->redirect('admin/enrollments');
        }
        $this->render('admin/enrollments/form', [
            'title' => 'Sửa ghi danh',
            'portal' => 'admin',
            'enrollment' => $enrollment,
            'students' => [],
            'classes' => [],
        ]);
    }

    public function update($params) {
        $this->requirePost();
        $id = (int)($params['id'] ?? 0);
        $result = $this->model->updateEnrollment($id, $_POST);
        if (isset($result['error'])) {
            $this->setOld($_POST);
            $this->flash('error', $result['error']);
            $this->redirect('admin/enrollments/' . $id . '/edit');
        }
        $this->audit('UPDATE_ENROLLMENT', 'enrollments', $id);
        $this->flash('success', 'Đã cập nhật ghi danh');
        $this->redirect('admin/enrollments?class_id=' . (int)($_POST['class_id'] ?? 0));
    }

    public function delete($params) {
        $this->requirePost();
        $id = (int)($params['id'] ?? 0);
        $classId = (int)($_POST['class_id'] ?? 0);
        $result = $this->model->deleteEnrollment($id);
        if (isset($result['error'])) {
            $this->flash('error', $result['error']);
        } else {
            $this->audit('DELETE_ENROLLMENT', 'enrollments', $id);
            $this->flash('success', 'Đã xóa ghi danh');
        }
        $redirect = $classId ? 'admin/enrollments?class_id=' . $classId : 'admin/enrollments';
        $this->redirect($redirect);
    }
}
