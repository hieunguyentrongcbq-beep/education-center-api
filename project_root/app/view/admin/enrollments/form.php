<?php
$isEdit = !empty($enrollment);
$e = $enrollment ?? [];
$payStatus = $old('payment_status', $e['payment_status'] ?? 'UNPAID');
$enrollStatus = $old('status', $e['status'] ?? 'ACTIVE');
?>
<div class="card">
    <h2><?= $isEdit ? 'Sửa ghi danh' : 'Ghi danh học viên vào lớp' ?></h2>

    <?php if ($isEdit): ?>
        <p class="text-muted small mb-3">
            <strong><?= htmlspecialchars($e['full_name'] ?? '') ?></strong>
            (<?= htmlspecialchars($e['student_code'] ?? '') ?>)
            — Lớp <strong><?= htmlspecialchars($e['class_code'] ?? '') ?></strong>
        </p>
        <input type="hidden" name="class_id" value="<?= (int)$e['class_id'] ?>">
    <?php else: ?>
        <p class="form-hint mb-3">Tạo ghi danh UNPAID + bản ghi học phí. Chuyển sang PAID qua <strong>Thanh toán → Duyệt</strong> để tự tạo lịch.</p>
    <?php endif; ?>

    <form method="POST" action="<?= $url($isEdit ? 'admin/enrollments/' . (int)$e['id'] . '/update' : 'admin/enrollments/store') ?>">
        <?php if (!$isEdit): ?>
        <div class="form-row">
            <div class="form-group">
                <label>Học viên *</label>
                <select name="student_id" required>
                    <option value="">-- Chọn học viên --</option>
                    <?php foreach ($students as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= (int)$old('student_id', 0) === (int)$s['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['student_code'] . ' - ' . $s['full_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Lớp *</label>
                <select name="class_id" required>
                    <option value="">-- Chọn lớp --</option>
                    <?php foreach ($classes as $cl): ?>
                        <option value="<?= $cl['id'] ?>" <?= (int)$old('class_id', 0) === (int)$cl['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cl['class_code'] . ' - ' . ($cl['course_name'] ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <?php endif; ?>

        <div class="form-row">
            <div class="form-group">
                <label>Trạng thái thanh toán</label>
                <select name="payment_status">
                    <option value="UNPAID" <?= $payStatus === 'UNPAID' ? 'selected' : '' ?>>Chưa thanh toán</option>
                    <option value="PAID" <?= $payStatus === 'PAID' ? 'selected' : '' ?>>Đã thanh toán</option>
                    <option value="REFUNDED" <?= $payStatus === 'REFUNDED' ? 'selected' : '' ?>>Hoàn tiền</option>
                </select>
            </div>
            <div class="form-group">
                <label>Trạng thái học</label>
                <select name="status">
                    <option value="ACTIVE" <?= $enrollStatus === 'ACTIVE' ? 'selected' : '' ?>>Đang học</option>
                    <option value="DROPPED" <?= $enrollStatus === 'DROPPED' ? 'selected' : '' ?>>Bỏ học</option>
                    <option value="COMPLETED" <?= $enrollStatus === 'COMPLETED' ? 'selected' : '' ?>>Hoàn thành</option>
                </select>
            </div>
            <?php if ($isEdit): ?>
            <div class="form-group">
                <label>Phương thức TT (khi PAID)</label>
                <select name="payment_method">
                    <option value="">—</option>
                    <option value="CASH" <?= ($e['payment_method'] ?? '') === 'CASH' ? 'selected' : '' ?>>Tiền mặt</option>
                    <option value="BANK_TRANSFER" <?= ($e['payment_method'] ?? '') === 'BANK_TRANSFER' ? 'selected' : '' ?>>Chuyển khoản</option>
                    <option value="CARD" <?= ($e['payment_method'] ?? '') === 'CARD' ? 'selected' : '' ?>>Thẻ</option>
                </select>
            </div>
            <?php endif; ?>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Lưu' : 'Ghi danh' ?></button>
            <a href="<?= $url('admin/enrollments' . ($isEdit ? '?class_id=' . (int)$e['class_id'] : '')) ?>" class="btn btn-light">Hủy</a>
        </div>
    </form>
</div>
