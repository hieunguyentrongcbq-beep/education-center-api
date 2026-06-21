<?php
$payLabels = ['UNPAID' => 'Chưa TT', 'PAID' => 'Đã TT', 'REFUNDED' => 'Hoàn tiền'];
$payBadges = ['UNPAID' => 'bg-warning text-dark', 'PAID' => 'bg-success', 'REFUNDED' => 'bg-secondary'];
$statusLabels = ['ACTIVE' => 'Đang học', 'DROPPED' => 'Bỏ học', 'COMPLETED' => 'Hoàn thành'];
?>
<div class="page-actions actions d-flex flex-wrap gap-2">
    <a href="<?= $url('admin/enrollments/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Ghi danh mới
    </a>
    <a href="<?= $url('admin/payments') ?>" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-credit-card"></i> Thanh toán / Duyệt
    </a>
</div>
<div class="card">
    <h2>Danh sách ghi danh</h2>
    <p class="form-hint mb-3">Ghi danh trực tiếp (UNPAID). Xác nhận thanh toán tại <strong>Thanh toán</strong> để vào lớp + tạo lịch tự động.</p>

    <form method="GET" action="<?= $url('admin/enrollments') ?>" class="mb-4">
        <div class="form-row align-items-end">
            <div class="form-group">
                <label>Lớp</label>
                <select name="class_id" onchange="this.form.submit()">
                    <option value="">Tất cả lớp</option>
                    <?php foreach ($classes as $cl): ?>
                        <option value="<?= $cl['id'] ?>" <?= ($classId ?? 0) === (int)$cl['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cl['class_code']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Thanh toán</label>
                <select name="payment_status" onchange="this.form.submit()">
                    <option value="">Tất cả</option>
                    <?php foreach ($payLabels as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($paymentFilter ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <a href="<?= $url('admin/enrollments') ?>" class="btn btn-light btn-sm">Xóa bộ lọc</a>
            </div>
        </div>
    </form>

    <?php if (empty($enrollments)): ?>
        <p class="empty mb-0">Không có ghi danh nào.</p>
    <?php else: ?>
    <div class="table-responsive-wrap">
        <table>
            <thead>
                <tr>
                    <th>HV</th>
                    <th>Lớp</th>
                    <th>Khóa</th>
                    <th>Ngày ĐK</th>
                    <th>TT thanh toán</th>
                    <th>TT học</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($enrollments as $e):
                $ps = $e['payment_status'] ?? 'UNPAID';
                $st = $e['status'] ?? 'ACTIVE';
            ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($e['full_name']) ?></strong><br>
                        <small class="text-muted"><?= htmlspecialchars($e['student_code']) ?></small>
                    </td>
                    <td><?= htmlspecialchars($e['class_code']) ?></td>
                    <td><?= htmlspecialchars($e['course_name']) ?></td>
                    <td><?= $e['enrollment_date'] ? date('d/m/Y', strtotime($e['enrollment_date'])) : '-' ?></td>
                    <td><span class="badge <?= $payBadges[$ps] ?? 'bg-secondary' ?>"><?= $payLabels[$ps] ?? $ps ?></span></td>
                    <td><?= htmlspecialchars($statusLabels[$st] ?? $st) ?></td>
                    <td class="actions d-flex flex-wrap gap-1">
                        <a href="<?= $url('admin/enrollments/' . (int)$e['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
                        <?php if ($ps === 'UNPAID'): ?>
                            <a href="<?= $url('admin/payments/create?student_id=' . (int)$e['student_id'] . '&class_id=' . (int)$e['class_id']) ?>"
                               class="btn btn-sm btn-primary">Duyệt TT</a>
                        <?php endif; ?>
                        <form method="POST" action="<?= $url('admin/enrollments/' . (int)$e['id'] . '/delete') ?>"
                              onsubmit="return confirm('Xóa ghi danh này?')">
                            <input type="hidden" name="class_id" value="<?= (int)$e['class_id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
