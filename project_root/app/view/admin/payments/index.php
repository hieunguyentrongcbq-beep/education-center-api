<?php
$pendingTotal = 0;
$pendingConflicts = 0;
$pendingFull = 0;
foreach ($pending as $p) {
    $pendingTotal += (float)($p['tuition_fee'] ?? 0);
    if (!empty($p['has_schedule_conflict'])) {
        $pendingConflicts++;
    }
    if ((int)($p['seats_left'] ?? 0) <= 0) {
        $pendingFull++;
    }
}
?>
<div class="page-actions actions d-flex flex-wrap gap-2 justify-content-between align-items-center">
    <a href="<?= $url('admin/payments/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Xác nhận thanh toán mới
    </a>
    <?php if ($pendingCount > 0): ?>
    <a href="#pending-payments" class="btn btn-outline-warning">
        <i class="bi bi-hourglass-split me-1"></i><?= (int)$pendingCount ?> chờ duyệt
    </a>
    <?php endif; ?>
</div>

<div class="card" id="pending-payments">
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-hourglass-split me-2 text-warning"></i>
                Chờ xác nhận thanh toán
                <?php if ($pendingCount > 0): ?>
                    <span class="badge bg-warning text-dark ms-1"><?= (int)$pendingCount ?></span>
                <?php endif; ?>
            </h2>
            <p class="form-hint mb-0">Học viên đã ghi danh (UNPAID). Duyệt → PAID + tạo lịch học + phân công GV.</p>
        </div>
    </div>

    <?php if ($pendingCount > 0): ?>
    <div class="pending-summary stats mb-4">
        <div class="stat-box stat-box-sm">
            <div class="num"><?= (int)$pendingCount ?></div>
            <div class="stat-label">Hồ sơ chờ</div>
        </div>
        <div class="stat-box stat-box-sm">
            <div class="num"><?= number_format($pendingTotal / 1000000, 1, ',', '.') ?>M</div>
            <div class="stat-label">Tổng học phí (đ)</div>
        </div>
        <div class="stat-box stat-box-sm <?= $pendingConflicts ? 'stat-box-danger' : '' ?>">
            <div class="num"><?= (int)$pendingConflicts ?></div>
            <div class="stat-label">Trùng lịch</div>
        </div>
        <div class="stat-box stat-box-sm <?= $pendingFull ? 'stat-box-danger' : '' ?>">
            <div class="num"><?= (int)$pendingFull ?></div>
            <div class="stat-label">Lớp đầy</div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (empty($pending)): ?>
        <p class="empty mb-0">Không có thanh toán chờ duyệt.</p>
    <?php else: ?>
    <div class="table-responsive-wrap">
        <table class="pending-payments-table">
            <thead>
                <tr>
                    <th>Học viên</th>
                    <th>Lớp / Khóa học</th>
                    <th>Học phí</th>
                    <th>Ngày ĐK</th>
                    <th>Chỗ trống</th>
                    <th>Cảnh báo</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($pending as $p):
                $isFull = (int)($p['seats_left'] ?? 0) <= 0;
                $hasConflict = !empty($p['has_schedule_conflict']);
                $blocked = $isFull || $hasConflict;
                $rowClass = $blocked ? 'pending-row-blocked' : 'pending-row-ok';
            ?>
                <tr class="<?= $rowClass ?>">
                    <td>
                        <strong><?= htmlspecialchars($p['full_name']) ?></strong><br>
                        <small class="text-muted"><?= htmlspecialchars($p['student_code']) ?></small>
                        <?php if (!empty($p['email'])): ?>
                            <br><small class="text-muted"><?= htmlspecialchars($p['email']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="fw-semibold"><?= htmlspecialchars($p['class_code']) ?></span>
                        <span class="badge bg-light text-dark ms-1"><?= htmlspecialchars($p['class_status'] ?? '') ?></span><br>
                        <small class="text-muted"><?= htmlspecialchars($p['course_name']) ?></small>
                    </td>
                    <td class="fw-semibold text-primary"><?= number_format((float)$p['tuition_fee'], 0, ',', '.') ?> đ</td>
                    <td><?= $p['enrollment_date'] ? date('d/m/Y', strtotime($p['enrollment_date'])) : '-' ?></td>
                    <td>
                        <?php if ($isFull): ?>
                            <span class="badge bg-danger">Lớp đầy</span>
                        <?php else: ?>
                            <span class="badge bg-success"><?= (int)$p['seats_left'] ?> chỗ</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($hasConflict): ?>
                            <span class="badge bg-danger" title="Trùng lịch với lớp khác">Trùng lịch</span>
                        <?php else: ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle">OK</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions text-end">
                        <div class="d-inline-flex flex-wrap gap-1 justify-content-end">
                            <?php if (!$blocked): ?>
                            <button type="button" class="btn btn-sm btn-primary js-quick-approve"
                                    data-student-id="<?= (int)$p['student_id'] ?>"
                                    data-class-id="<?= (int)$p['class_id'] ?>"
                                    data-label="<?= htmlspecialchars($p['full_name'] . ' — ' . $p['class_code'], ENT_QUOTES) ?>"
                                    data-fee="<?= number_format((float)$p['tuition_fee'], 0, ',', '.') ?>">
                                <i class="bi bi-lightning-charge"></i> Duyệt nhanh
                            </button>
                            <?php endif; ?>
                            <a href="<?= $url('admin/payments/create?student_id='.(int)$p['student_id'].'&class_id='.(int)$p['class_id']) ?>"
                               class="btn btn-sm btn-outline-secondary" title="Form đầy đủ (chọn GV)">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php if (!empty($pending)): ?>
<div class="modal fade" id="quickApproveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= $url('admin/payments/confirm') ?>">
                <input type="hidden" name="return_to" value="admin/payments#pending-payments">
                <input type="hidden" name="student_id" id="qa-student-id" value="">
                <input type="hidden" name="class_id" id="qa-class-id" value="">
                <div class="modal-header">
                    <h5 class="modal-title">Duyệt thanh toán nhanh</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3" id="qa-summary"></p>
                    <div class="form-group">
                        <label>Phương thức thanh toán</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="CASH">Tiền mặt</option>
                            <option value="BANK_TRANSFER">Chuyển khoản</option>
                            <option value="CARD">Thẻ</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label>Giáo viên (tùy chọn)</label>
                        <select name="teacher_id" class="form-select">
                            <option value="">Tự động — GV mặc định lớp</option>
                            <?php foreach ($teachers as $t): ?>
                                <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['teacher_code'].' — '.$t['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <p class="form-hint mt-2 mb-0">Sau duyệt: enrollment PAID, tạo lịch 2 buổi/tuần, phân công GV.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2-circle me-1"></i> Xác nhận thanh toán
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modalEl = document.getElementById('quickApproveModal');
    if (!modalEl || typeof bootstrap === 'undefined') return;
    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    document.querySelectorAll('.js-quick-approve').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('qa-student-id').value = btn.getAttribute('data-student-id') || '';
            document.getElementById('qa-class-id').value = btn.getAttribute('data-class-id') || '';
            var label = btn.getAttribute('data-label') || '';
            var fee = btn.getAttribute('data-fee') || '';
            document.getElementById('qa-summary').textContent = label + (fee ? ' — ' + fee + ' đ' : '');
            modal.show();
        });
    });
    if (window.location.hash === '#pending-payments') {
        var anchor = document.getElementById('pending-payments');
        if (anchor) anchor.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
});
</script>
<?php endif; ?>

<div class="card">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h2 class="mb-0"><i class="bi bi-receipt me-2 text-primary"></i>Lịch sử thanh toán</h2>
        <a href="<?= $url('admin/payments/export') ?>" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-download me-1"></i> Export CSV
        </a>
    </div>
    <?php if (empty($completed)): ?>
        <p class="empty mb-0">Chưa có thanh toán hoàn tất</p>
    <?php else: ?>
    <div class="table-responsive-wrap">
        <table>
            <thead><tr><th>Học viên</th><th>Lớp</th><th>Số tiền</th><th>Phương thức</th><th>Ngày</th><th>TT</th><th></th></tr></thead>
            <tbody>
            <?php
            $methodLabels = ['CASH' => 'Tiền mặt', 'BANK_TRANSFER' => 'Chuyển khoản', 'CARD' => 'Thẻ'];
            foreach ($completed as $p):
                $status = $p['payment_status'] ?? 'COMPLETED';
                $isRefunded = $status === 'REFUNDED';
            ?>
                <tr class="<?= $isRefunded ? 'table-secondary' : '' ?>">
                    <td><?= htmlspecialchars($p['full_name'].' ('.$p['student_code'].')') ?></td>
                    <td><?= htmlspecialchars($p['class_code'] ?? '-') ?></td>
                    <td class="fw-semibold <?= $isRefunded ? 'text-muted text-decoration-line-through' : 'text-primary' ?>">
                        <?= number_format((float)$p['amount'], 0, ',', '.') ?> đ
                    </td>
                    <td><?= htmlspecialchars($methodLabels[$p['payment_method'] ?? ''] ?? ($p['payment_method'] ?? '-')) ?></td>
                    <td><?= $p['payment_date'] ? date('d/m/Y', strtotime($p['payment_date'])) : '-' ?></td>
                    <td>
                        <?php if ($isRefunded): ?>
                            <span class="badge bg-secondary">Hoàn tiền</span>
                        <?php else: ?>
                            <span class="badge bg-success">Đã thu</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions d-flex flex-wrap gap-1">
                        <?php if (!$isRefunded): ?>
                            <a href="<?= $url('admin/payments/' . (int)$p['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
                            <button type="button" class="btn btn-sm btn-outline-warning"
                                    data-bs-toggle="modal" data-bs-target="#refundModal<?= (int)$p['id'] ?>">
                                Hoàn tiền
                            </button>
                            <div class="modal fade" id="refundModal<?= (int)$p['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="<?= $url('admin/payments/' . (int)$p['id'] . '/refund') ?>">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Xác nhận hoàn tiền</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Hoàn tiền cho <strong><?= htmlspecialchars($p['full_name']) ?></strong>
                                                    — lớp <strong><?= htmlspecialchars($p['class_code'] ?? '-') ?></strong>
                                                    (<strong><?= number_format((float)$p['amount'], 0, ',', '.') ?> đ</strong>).</p>
                                                <div class="form-group mb-0">
                                                    <label>Lý do (tùy chọn)</label>
                                                    <textarea name="reason" rows="2" class="form-control" placeholder="VD: Học viên chuyển lớp..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                                                <button type="submit" class="btn btn-warning">Xác nhận hoàn tiền</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <span class="text-muted small">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
