<?php
$isEdit = !empty($payroll);
$p = $payroll ?? [];
$teacherIdVal = (int)$old('teacher_id', $p['teacher_id'] ?? $prefillTeacherId ?? 0);
$monthVal = $old('month', $p['month'] ?? $prefillMonth ?? date('Y-m'));
$hoursDefault = (!empty($_GET['compute_hours']) && $suggestedHours !== null)
    ? $suggestedHours
    : ($p['teaching_hours'] ?? ($suggestedHours ?? '0'));
$hoursVal = $old('teaching_hours', $hoursDefault);
$salaryVal = $old('salary_amount', $p['salary_amount'] ?? '0');
$statusVal = $old('payment_status', $p['payment_status'] ?? 'PENDING');

?>
<div class="card">
    <h2><?= $isEdit ? 'Sửa bảng lương' : 'Tạo bảng lương' ?></h2>

    <?php if ($suggestedHours !== null): ?>
        <div class="alert alert-info py-2">
            Giờ dạy tính từ lịch phân công (FINAL): <strong><?= number_format((float)$suggestedHours, 1, ',', '.') ?> giờ</strong>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= $url($isEdit ? 'admin/payrolls/' . (int)$p['id'] . '/update' : 'admin/payrolls/store') ?>" id="payroll-form">
        <div class="form-row">
            <div class="form-group">
                <label>Giáo viên *</label>
                <select name="teacher_id" required id="teacher_id" <?= $isEdit ? '' : '' ?>>
                    <option value="">-- Chọn giáo viên --</option>
                    <?php foreach ($teachers as $t): ?>
                        <option value="<?= (int)$t['id'] ?>" <?= $teacherIdVal === (int)$t['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['teacher_code'] . ' - ' . $t['full_name']) ?>
                            (<?= ($t['teacher_type'] ?? '') === 'VISITING' ? 'Mời giảng' : 'Cơ hữu' ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Tháng lương *</label>
                <input type="month" name="month" required id="month" value="<?= htmlspecialchars($monthVal) ?>">
            </div>
        </div>

        <div class="form-row align-items-end">
            <div class="form-group">
                <label>Số giờ dạy</label>
                <input type="number" name="teaching_hours" step="0.1" min="0" required
                       value="<?= htmlspecialchars((string)$hoursVal) ?>">
            </div>
            <div class="form-group">
                <label>&nbsp;</label>
                <a href="#" class="btn btn-outline-secondary btn-sm d-block" id="compute-hours-btn">
                    <i class="bi bi-calculator"></i> Tính giờ từ lịch
                </a>
            </div>
            <div class="form-group">
                <label>Số tiền lương (VNĐ)</label>
                <input type="number" name="salary_amount" step="1000" min="0" required
                       value="<?= htmlspecialchars((string)$salaryVal) ?>">
            </div>
            <div class="form-group">
                <label>Trạng thái</label>
                <select name="payment_status">
                    <option value="PENDING" <?= $statusVal === 'PENDING' ? 'selected' : '' ?>>Chưa trả</option>
                    <option value="PAID" <?= $statusVal === 'PAID' ? 'selected' : '' ?>>Đã trả</option>
                </select>
            </div>
        </div>

        <p class="form-hint">Giờ dạy lấy từ lịch lớp + phân công GV (scenario FINAL). Số tiền do admin nhập.</p>

        <div class="d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Lưu' : 'Tạo bảng lương' ?></button>
            <a href="<?= $url('admin/payrolls') ?>" class="btn btn-light">Hủy</a>
        </div>
    </form>
</div>
<script>
(function () {
    var btn = document.getElementById('compute-hours-btn');
    if (!btn) return;
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        var tid = document.getElementById('teacher_id');
        var month = document.getElementById('month');
        if (!tid || !month || !tid.value || !month.value) {
            alert('Chọn giáo viên và tháng trước khi tính giờ.');
            return;
        }
        var base = <?= json_encode($isEdit ? $url('admin/payrolls/' . (int)$p['id'] . '/edit') : $url('admin/payrolls/create')) ?>;
        window.location = base + '?compute_hours=1&teacher_id=' + encodeURIComponent(tid.value) + '&month=' + encodeURIComponent(month.value);
    });
})();
</script>
