<?php

namespace App\Controllers\Admin;



use Core\WebController;

use App\Models\PaymentService;

use App\Models\ScheduleService;

use App\Models\classmodel;

use Core\Database;



class PaymentWebController extends WebController {

    public function index() {

        $service = new PaymentService();
        $db = Database::getInstance()->getConnection();
        $teachers = $db->query("SELECT t.id, t.teacher_code, u.full_name FROM teachers t JOIN users u ON t.user_id=u.id WHERE t.status='ACTIVE' ORDER BY t.teacher_code")->fetchAll();

        $this->render('admin/payments/index', [

            'title' => 'Thanh toán học phí',

            'portal' => 'admin',

            'pending' => $service->listPending(),

            'pendingCount' => $service->countPending(),

            'completed' => $service->listCompleted(),

            'teachers' => $teachers,

        ]);

    }



    public function create() {

        $db = Database::getInstance()->getConnection();

        $students = (new classmodel())->getAllStudents(200);

        $classes = (new classmodel())->getAllClasses(1, 100);

        $teachers = $db->query("SELECT t.id, t.teacher_code, u.full_name FROM teachers t JOIN users u ON t.user_id=u.id WHERE t.status='ACTIVE'")->fetchAll();

        $prefillStudentId = (int)($_GET['student_id'] ?? 0);

        $prefillClassId = (int)($_GET['class_id'] ?? 0);

        $this->render('admin/payments/form', [

            'title' => 'Xác nhận thanh toán',

            'portal' => 'admin',

            'students' => $students,

            'classes' => $classes,

            'teachers' => $teachers,

            'prefillStudentId' => $prefillStudentId,

            'prefillClassId' => $prefillClassId,

        ]);

    }



    public function edit($params) {

        $id = (int)($params['id'] ?? 0);

        $payment = (new PaymentService())->getPaymentById($id);

        if (!$payment) {

            $this->flash('error', 'Không tìm thấy thanh toán');

            $this->redirect('admin/payments');

        }

        if ($payment['payment_status'] !== 'COMPLETED') {

            $this->flash('error', 'Chỉ sửa được thanh toán đã hoàn tất');

            $this->redirect('admin/payments');

        }



        $this->render('admin/payments/edit', [

            'title' => 'Sửa thanh toán',

            'portal' => 'admin',

            'payment' => $payment,

        ]);

    }



    public function confirm() {

        $this->requirePost();

        $service = new PaymentService();

        $adminId = (int)($_SESSION['user']['id'] ?? 0);

        $result = $service->confirmPayment($_POST, $adminId);

        if (isset($result['error'])) {

            $msg = $result['error'];

            if (!empty($result['details']) && is_array($result['details'])) {
                $kind = str_contains($msg, 'Giáo viên') ? 'dạy' : 'học';
                $msg = ScheduleService::conflictClassMessage($result['details'], $kind);
            }

            $this->setOld($_POST);

            $this->flash('error', $msg);

            $returnTo = trim($_POST['return_to'] ?? '');
            if ($returnTo !== '' && strpos($returnTo, 'admin/payments') === 0) {
                $this->redirect($returnTo);
            }
            $this->redirect('admin/payments/create');

        }

        $msg = 'Thanh toán thành công. Lịch học đã được tạo.';
        if (!empty($result['end_date'])) {
            $msg .= ' Kết thúc: ' . $result['end_date'];
        }
        if (!empty($result['teacher_assigned'])) {
            $msg .= ' Đã phân công GV (thứ chính + thứ phụ).';
        }
        $this->audit('CONFIRM_PAYMENT', 'enrollments', (int)($_POST['student_id'] ?? 0));

        $this->flash('success', $msg);

        $returnTo = trim($_POST['return_to'] ?? 'admin/payments');
        $this->redirect($returnTo !== '' ? $returnTo : 'admin/payments');

    }



    public function update($params) {

        $this->requirePost();

        $id = (int)($params['id'] ?? 0);

        $service = new PaymentService();

        $result = $service->updatePayment($id, $_POST);

        if (isset($result['error'])) {

            $this->flash('error', $result['error']);

            $this->redirect('admin/payments/' . $id . '/edit');

        }

        $this->audit('UPDATE_PAYMENT', 'tuition_payments', $id);

        $this->flash('success', 'Đã cập nhật thanh toán');

        $this->redirect('admin/payments');

    }



    public function refund($params) {

        $this->requirePost();

        $id = (int)($params['id'] ?? 0);

        $adminId = (int)($_SESSION['user']['id'] ?? 0);

        $reason = trim($_POST['reason'] ?? '');

        $result = (new PaymentService())->refundPayment($id, $adminId, $reason);

        if (isset($result['error'])) {

            $this->flash('error', $result['error']);

        } else {

            $this->flash('success', 'Đã hoàn tiền. Enrollment chuyển sang REFUNDED (lịch học giữ nguyên).');

        }

        $this->redirect('admin/payments');

    }

    public function exportCsv() {
        $payments = (new PaymentService())->listCompleted(5000);
        $methodLabels = [
            'CASH' => 'Tiền mặt',
            'BANK_TRANSFER' => 'Chuyển khoản',
            'CARD' => 'Thẻ',
        ];
        $rows = [];
        foreach ($payments as $p) {
            $rows[] = [
                $p['id'],
                $p['student_code'],
                $p['full_name'],
                $p['class_code'] ?? '',
                $p['course_name'] ?? '',
                $p['amount'],
                $methodLabels[$p['payment_method'] ?? ''] ?? ($p['payment_method'] ?? ''),
                $p['payment_date'] ?? '',
                $p['payment_status'] ?? '',
            ];
        }
        $this->sendCsv(
            'lich_su_thanh_toan_' . date('Y-m-d') . '.csv',
            ['ID', 'Mã HV', 'Họ tên', 'Lớp', 'Khóa học', 'Số tiền', 'Phương thức', 'Ngày', 'Trạng thái'],
            $rows
        );
    }

}


