<?php
$p = $payment;
$methodLabels = [
    'CASH' => 'Tiền mặt',
    'BANK_TRANSFER' => 'Chuyển khoản',
    'CARD' => 'Thẻ',
];
?>
<div class="card">
    <h2>Sửa thanh toán</h2>
    <p class="text-muted small mb-3">
        Học viên: <strong><?= htmlspecialchars($p['full_name'] . ' (' . $p['student_code'] . ')') ?></strong>
        — Lớp: <strong><?= htmlspecialchars($p['class_code'] ?? '-') ?></strong>
        (<?= htmlspecialchars($p['course_name'] ?? '') ?>)
    </p>

    <form method="POST" action="<?= $url('admin/payments/' . (int)$p['id'] . '/update') ?>">
        <div class="form-row">
            <div class="form-group">
                <label>Số tiền (đ) *</label>
                <input type="number" name="amount" min="1" step="1000" required
                       value="<?= htmlspecialchars((string)(float)$p['amount']) ?>">
            </div>
            <div class="form-group">
                <label>Phương thức *</label>
                <select name="payment_method" required>
                    <?php foreach ($methodLabels as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($p['payment_method'] ?? 'CASH') === $val ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Ngày thanh toán</label>
                <input type="date" name="payment_date"
                       value="<?= htmlspecialchars($p['payment_date'] ?? date('Y-m-d')) ?>">
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
            <a href="<?= $url('admin/payments') ?>" class="btn btn-light">Hủy</a>
        </div>
    </form>
</div>
