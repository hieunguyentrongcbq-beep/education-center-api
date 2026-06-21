<?php
$isEdit = !empty($course);
$c = $course ?? [];
$totalSessions = (int)$old('total_sessions', $c['total_sessions'] ?? 20);
$weeksHint = (int)ceil($totalSessions / 2);
$dayPrimary = (int)$old('day_primary', $c['day_primary'] ?? 1);
$daySecondary = (int)$old('day_secondary', $c['day_secondary'] ?? 4);
$statusVal = $old('status', $c['status'] ?? 'ACTIVE');
$weekDayOrder = [1, 2, 3, 4, 5, 6, 0];
$weekDayOptions = [
    1 => 'Thứ hai',
    2 => 'Thứ ba',
    3 => 'Thứ tư',
    4 => 'Thứ năm',
    5 => 'Thứ sáu',
    6 => 'Thứ bảy',
    0 => 'Chủ nhật',
];
?>
<div class="card">
    <form method="POST" action="<?= $url($isEdit ? 'admin/courses/'.$c['id'].'/update' : 'admin/courses/store') ?>">
        <div class="form-row">
            <div class="form-group">
                <label>Mã khóa học *</label>
                <input name="course_code" value="<?= htmlspecialchars($old('course_code', $c['course_code'] ?? '')) ?>" <?= $isEdit ? 'readonly' : 'required' ?> maxlength="30" pattern="[A-Za-z0-9_-]{2,30}" title="2–30 ký tự: chữ, số, gạch ngang">
            </div>
            <div class="form-group">
                <label>Tên khóa học *</label>
                <input name="course_name" value="<?= htmlspecialchars($old('course_name', $c['course_name'] ?? '')) ?>" required minlength="3" maxlength="200">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Số buổi học *</label>
                <input type="number" name="total_sessions" min="2" max="200" step="2" value="<?= htmlspecialchars($totalSessions) ?>" required>
                <div class="form-hint">2 buổi/tuần → <?= $weeksHint ?> tuần (phải là số chẵn)</div>
            </div>
            <div class="form-group">
                <label>Học phí (VNĐ) *</label>
                <input type="number" name="tuition_fee" min="1" step="1" value="<?= htmlspecialchars($old('tuition_fee', $c['tuition_fee'] ?? '')) ?>" required>
                <div class="form-hint">Nhập số nguyên dương (VD: 3500000, 2750000).</div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Ngày học chính 1 *</label>
                <select name="day_primary" id="day-primary" required>
                    <?php foreach ($weekDayOrder as $v): ?>
                        <option value="<?= $v ?>" <?= $dayPrimary === $v ? 'selected' : '' ?>><?= htmlspecialchars($weekDayOptions[$v]) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Ngày học chính 2 *</label>
                <select name="day_secondary" id="day-secondary" required>
                    <?php foreach ($weekDayOrder as $v): ?>
                        <option value="<?= $v ?>" <?= $daySecondary === $v ? 'selected' : '' ?>><?= htmlspecialchars($weekDayOptions[$v]) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="form-hint" id="day-duplicate-hint" style="display:none;color:#dc2626">
                    Hai ngày học trong tuần phải khác nhau.
                </div>
            </div>
        </div>
        <p class="form-hint mb-3">Khóa học 2 buổi/tuần — chọn hai thứ khác nhau (vd: Thứ 2 và Thứ 5).</p>
        <div class="form-group">
            <label>Mô tả</label>
            <textarea name="description" rows="3" maxlength="5000"><?= htmlspecialchars($old('description', $c['description'] ?? '')) ?></textarea>
        </div>
        <div class="form-group">
            <label>Trạng thái</label>
            <select name="status">
                <option value="ACTIVE" <?= $statusVal==='ACTIVE'?'selected':'' ?>>ACTIVE</option>
                <option value="INACTIVE" <?= $statusVal==='INACTIVE'?'selected':'' ?>>INACTIVE</option>
            </select>
        </div>
        <?php if ($isEdit && !empty($endPreview)): ?>
            <p class="form-hint">Ví dụ: bắt đầu 01/07/2026 + <?= (int)($weeks??10) ?> tuần → kết thúc ~ <?= date('d/m/Y', strtotime($endPreview)) ?></p>
        <?php endif; ?>
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Cập nhật' : 'Tạo mới' ?></button>
        <a href="<?= $url('admin/courses') ?>" class="btn btn-secondary">Hủy</a>
    </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var primary = document.getElementById('day-primary');
    var secondary = document.getElementById('day-secondary');
    var hint = document.getElementById('day-duplicate-hint');
    var form = primary && primary.closest('form');
    if (!primary || !secondary || !form) return;

    function syncDayOptions() {
        var p = primary.value;
        var s = secondary.value;
        Array.prototype.forEach.call(secondary.options, function (opt) {
            opt.disabled = opt.value === p;
        });
        Array.prototype.forEach.call(primary.options, function (opt) {
            opt.disabled = opt.value === s;
        });
        if (s === p) {
            var fallback = Array.prototype.find.call(secondary.options, function (opt) {
                return !opt.disabled && opt.value !== p;
            });
            if (fallback) secondary.value = fallback.value;
        }
        var dup = primary.value === secondary.value;
        if (hint) hint.style.display = dup ? 'block' : 'none';
    }

    primary.addEventListener('change', syncDayOptions);
    secondary.addEventListener('change', syncDayOptions);
    form.addEventListener('submit', function (e) {
        if (primary.value === secondary.value) {
            e.preventDefault();
            if (hint) hint.style.display = 'block';
            secondary.focus();
        }
    });
    syncDayOptions();
});
</script>
