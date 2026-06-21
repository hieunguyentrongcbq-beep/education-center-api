<?php
namespace App\Controllers\Admin;

use Core\WebController;
use App\Models\instructormodel;

class PayrollWebController extends WebController {
    private $model;

    public function __construct() {
        $this->model = new instructormodel();
    }

    public function index() {
        $teacherId = !empty($_GET['teacher_id']) ? (int)$_GET['teacher_id'] : null;
        $month = !empty($_GET['month']) ? trim($_GET['month']) : null;
        $status = !empty($_GET['payment_status']) ? $_GET['payment_status'] : null;

        $payrolls = $this->model->listPayrollsAdmin($teacherId, $month, $status);
        $totalPending = 0;
        $totalPaid = 0;
        foreach ($payrolls as $p) {
            $amt = (float)($p['salary_amount'] ?? 0);
            if (($p['payment_status'] ?? '') === 'PAID') {
                $totalPaid += $amt;
            } else {
                $totalPending += $amt;
            }
        }

        $this->render('admin/payrolls/index', [
            'title' => 'Lương giáo viên',
            'portal' => 'admin',
            'payrolls' => $payrolls,
            'teachers' => $this->model->getAllTeachers(1, 200),
            'monthOptions' => $this->model->listPayrollMonthOptions(),
            'teacherId' => $teacherId,
            'monthFilter' => $month ?? '',
            'statusFilter' => $status ?? '',
            'totalPending' => $totalPending,
            'totalPaid' => $totalPaid,
        ]);
    }

    public function create() {
        $suggestedHours = null;
        $teacherId = !empty($_GET['teacher_id']) ? (int)$_GET['teacher_id'] : 0;
        $month = trim($_GET['month'] ?? date('Y-m'));
        if ($teacherId && $month && !empty($_GET['compute_hours'])) {
            $computed = $this->model->computeTeachingHoursForMonth($teacherId, $month);
            if (isset($computed['hours'])) {
                $suggestedHours = $computed['hours'];
            }
        }

        $this->render('admin/payrolls/form', [
            'title' => 'Tạo bảng lương',
            'portal' => 'admin',
            'payroll' => null,
            'teachers' => $this->model->getAllTeachers(1, 200),
            'suggestedHours' => $suggestedHours,
            'prefillTeacherId' => $teacherId,
            'prefillMonth' => $month,
        ]);
    }

    public function store() {
        $this->requirePost();
        $result = $this->model->createPayroll($_POST);
        if (isset($result['error'])) {
            $this->setOld($_POST);
            $this->flash('error', $result['error']);
            $this->redirect('admin/payrolls/create');
        }
        $this->audit('CREATE_PAYROLL', 'payrolls', $result['id'] ?? null);
        $this->flash('success', 'Đã tạo bảng lương');
        $this->redirect('admin/payrolls?month=' . urlencode(trim($_POST['month'] ?? '')));
    }

    public function edit($params) {
        $id = (int)($params['id'] ?? 0);
        $payroll = $this->model->getPayrollById($id);
        if (!$payroll) {
            $this->flash('error', 'Không tìm thấy bảng lương');
            $this->redirect('admin/payrolls');
        }

        $suggestedHours = null;
        if (!empty($_GET['compute_hours'])) {
            $computed = $this->model->computeTeachingHoursForMonth(
                (int)$payroll['teacher_id'],
                $payroll['month']
            );
            if (isset($computed['hours'])) {
                $suggestedHours = $computed['hours'];
            }
        }

        $this->render('admin/payrolls/form', [
            'title' => 'Sửa bảng lương',
            'portal' => 'admin',
            'payroll' => $payroll,
            'teachers' => $this->model->getAllTeachers(1, 200),
            'suggestedHours' => $suggestedHours,
            'prefillTeacherId' => 0,
            'prefillMonth' => '',
        ]);
    }

    public function update($params) {
        $this->requirePost();
        $id = (int)($params['id'] ?? 0);
        $result = $this->model->updatePayroll($id, $_POST);
        if (isset($result['error'])) {
            $this->setOld($_POST);
            $this->flash('error', $result['error']);
            $this->redirect('admin/payrolls/' . $id . '/edit');
        }
        $this->audit('UPDATE_PAYROLL', 'payrolls', $id);
        $this->flash('success', 'Đã cập nhật bảng lương');
        $this->redirect('admin/payrolls?month=' . urlencode(trim($_POST['month'] ?? '')));
    }

    public function delete($params) {
        $this->requirePost();
        $id = (int)($params['id'] ?? 0);
        $month = trim($_POST['month'] ?? '');
        $result = $this->model->deletePayroll($id);
        if (isset($result['error'])) {
            $this->flash('error', $result['error']);
        } else {
            $this->audit('DELETE_PAYROLL', 'payrolls', $id);
            $this->flash('success', 'Đã xóa bảng lương');
        }
        $redirect = $month ? 'admin/payrolls?month=' . urlencode($month) : 'admin/payrolls';
        $this->redirect($redirect);
    }

    public function markPaid($params) {
        $this->requirePost();
        $id = (int)($params['id'] ?? 0);
        $month = trim($_POST['month'] ?? '');
        $result = $this->model->markPayrollPaid($id);
        if (isset($result['error'])) {
            $this->flash('error', $result['error']);
        } else {
            $this->audit('PAY_PAYROLL', 'payrolls', $id);
            $this->flash('success', 'Đã đánh dấu đã trả lương');
        }
        $redirect = $month ? 'admin/payrolls?month=' . urlencode($month) : 'admin/payrolls';
        $this->redirect($redirect);
    }
}
