<?php
namespace App\Controllers\Admin;

use Core\WebController;
use App\Models\classmodel;
use App\Models\PaymentService;

class AdminController extends WebController {
    public function dashboard() {
        $model = new classmodel();
        $payService = new PaymentService();
        $revenue = $model->getRevenueByMonth();
        $students = $model->getStudentCount();
        $fillRate = $model->getClassFillRate();
        $pendingPayments = $payService->listPending(8);
        $this->render('admin/dashboard', [
            'title' => 'Dashboard',
            'portal' => 'admin',
            'revenue' => $revenue,
            'students' => $students,
            'fillRate' => $fillRate,
            'pendingPayments' => $pendingPayments,
            'pendingPaymentsCount' => $payService->countPending(),
        ]);
    }

    public function exportRevenue() {
        $revenue = (new classmodel())->getRevenueByMonth();
        $rows = [];
        foreach ($revenue as $row) {
            $rows[] = [$row['month'], $row['total_revenue']];
        }
        $this->sendCsv(
            'doanh_thu_thang_' . date('Y-m-d') . '.csv',
            ['Tháng', 'Tổng Doanh Thu (VNĐ)'],
            $rows
        );
    }
}
