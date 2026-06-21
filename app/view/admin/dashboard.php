<?php
$revenueChart = array_reverse($revenue);
$revenueLabels = array_column($revenueChart, 'month');
$revenueValues = array_map(function ($r) {
    return (float)$r['total_revenue'];
}, $revenueChart);

$statusLabels = [
    'ACTIVE' => 'Đang học',
    'INACTIVE' => 'Ngưng học',
    'GRADUATED' => 'Đã tốt nghiệp',
];
$studentChartLabels = [];
$studentChartValues = [];
foreach ($students as $s) {
    $studentChartLabels[] = $statusLabels[$s['status']] ?? $s['status'];
    $studentChartValues[] = (int)$s['count'];
}

$fillChartLabels = [];
$fillChartValues = [];
foreach ($fillRate as $f) {
    $fillChartLabels[] = $f['class_code'];
    $fillChartValues[] = $f['max_students'] > 0
        ? (int)round($f['current_students'] / $f['max_students'] * 100)
        : 0;
}

$totalStudents = array_sum($studentChartValues);
$totalRevenue = array_sum($revenueValues);
$pendingPayCount = (int)($pendingPaymentsCount ?? 0);
?>
<?php if ($pendingPayCount > 0): ?>
<div class="card border-warning mb-4 pending-dashboard-alert">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="h5 mb-1 text-warning-emphasis">
                <i class="bi bi-hourglass-split me-2"></i><?= $pendingPayCount ?> thanh toán chờ duyệt
            </h2>
            <p class="text-muted mb-0 small">Học viên đã ghi danh nhưng chưa thanh toán — cần admin xác nhận để vào lớp.</p>
        </div>
        <a href="<?= $url('admin/payments#pending-payments') ?>" class="btn btn-warning">
            <i class="bi bi-credit-card me-1"></i> Xử lý ngay
        </a>
    </div>
    <?php if (!empty($pendingPayments)): ?>
    <div class="table-responsive-wrap mt-3">
        <table class="table table-sm mb-0">
            <thead><tr><th>Học viên</th><th>Lớp</th><th>Học phí</th><th></th></tr></thead>
            <tbody>
            <?php foreach (array_slice($pendingPayments, 0, 5) as $pp): ?>
                <tr>
                    <td><?= htmlspecialchars($pp['full_name']) ?> <small class="text-muted">(<?= htmlspecialchars($pp['student_code']) ?>)</small></td>
                    <td><?= htmlspecialchars($pp['class_code']) ?></td>
                    <td><?= number_format((float)$pp['tuition_fee'], 0, ',', '.') ?> đ</td>
                    <td class="text-end">
                        <a href="<?= $url('admin/payments/create?student_id='.(int)$pp['student_id'].'&class_id='.(int)$pp['class_id']) ?>" class="btn btn-sm btn-outline-primary">Duyệt</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>
<div class="stats">
    <div class="stat-box">
        <i class="bi bi-currency-dollar stat-icon"></i>
        <div class="num"><?= number_format($totalRevenue / 1000000, 1, ',', '.') ?>M</div>
        <div class="stat-label">Tổng doanh thu (đ)</div>
    </div>
    <div class="stat-box">
        <i class="bi bi-people stat-icon"></i>
        <div class="num"><?= $totalStudents ?></div>
        <div class="stat-label">Tổng học viên</div>
    </div>
    <div class="stat-box">
        <i class="bi bi-collection stat-icon"></i>
        <div class="num"><?= count($fillRate) ?></div>
        <div class="stat-label">Lớp đang mở</div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                <h2 class="mb-0"><i class="bi bi-bar-chart-line me-2 text-primary"></i>Doanh thu theo tháng</h2>
                <a href="<?= $url('admin/revenue/export') ?>" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-download me-1"></i> Export CSV
                </a>
            </div>
            <?php if (empty($revenueChart)): ?>
                <p class="empty mb-0">Chưa có dữ liệu doanh thu</p>
            <?php else: ?>
                <div class="chart-wrap chart-wrap-lg">
                    <canvas id="revenueChart"></canvas>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <h2><i class="bi bi-pie-chart me-2 text-primary"></i>Học viên theo trạng thái</h2>
            <?php if ($totalStudents === 0): ?>
                <p class="empty mb-0">Chưa có học viên</p>
            <?php else: ?>
                <div class="chart-wrap">
                    <canvas id="studentChart"></canvas>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card mt-4">
    <h2><i class="bi bi-graph-up me-2 text-primary"></i>Tỉ lệ lấp đầy lớp (%)</h2>
    <?php if (empty($fillRate)): ?>
        <p class="empty mb-0">Chưa có lớp đang mở</p>
    <?php else: ?>
        <div class="chart-wrap chart-wrap-bar" style="height: <?= max(220, min(420, count($fillRate) * 48)) ?>px">
            <canvas id="fillRateChart"></canvas>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const primary = getComputedStyle(document.documentElement).getPropertyValue('--ec-primary').trim() || '#4f46e5';
    const primarySoft = getComputedStyle(document.documentElement).getPropertyValue('--ec-primary-soft').trim() || '#eef2ff';
    const palette = ['#4f46e5', '#059669', '#f59e0b', '#ef4444', '#7c3aed', '#0ea5e9'];

    Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
    Chart.defaults.color = '#64748b';

    const revenueLabels = <?= json_encode($revenueLabels, JSON_UNESCAPED_UNICODE) ?>;
    const revenueValues = <?= json_encode($revenueValues) ?>;
    if (revenueLabels.length && document.getElementById('revenueChart')) {
        new Chart(document.getElementById('revenueChart'), {
            type: 'bar',
            data: {
                labels: revenueLabels,
                datasets: [{
                    label: 'Doanh thu (đ)',
                    data: revenueValues,
                    backgroundColor: primary + 'cc',
                    borderColor: primary,
                    borderWidth: 1,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                return new Intl.NumberFormat('vi-VN').format(ctx.raw) + ' đ';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (v) {
                                return new Intl.NumberFormat('vi-VN', { notation: 'compact' }).format(v);
                            }
                        }
                    }
                }
            }
        });
    }

    const studentLabels = <?= json_encode($studentChartLabels, JSON_UNESCAPED_UNICODE) ?>;
    const studentValues = <?= json_encode($studentChartValues) ?>;
    if (studentValues.length && document.getElementById('studentChart')) {
        new Chart(document.getElementById('studentChart'), {
            type: 'doughnut',
            data: {
                labels: studentLabels,
                datasets: [{
                    data: studentValues,
                    backgroundColor: palette.slice(0, studentLabels.length),
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    const fillLabels = <?= json_encode($fillChartLabels, JSON_UNESCAPED_UNICODE) ?>;
    const fillValues = <?= json_encode($fillChartValues) ?>;
    if (fillLabels.length && document.getElementById('fillRateChart')) {
        new Chart(document.getElementById('fillRateChart'), {
            type: 'bar',
            data: {
                labels: fillLabels,
                datasets: [{
                    label: 'Tỉ lệ lấp đầy (%)',
                    data: fillValues,
                    backgroundColor: fillValues.map(function (v) {
                        return v >= 80 ? '#059669cc' : (v >= 50 ? '#f59e0bcc' : primary + '99');
                    }),
                    borderRadius: 6,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        beginAtZero: true,
                        max: 100,
                        ticks: { callback: function (v) { return v + '%'; } }
                    }
                }
            }
        });
    }
})();
</script>
