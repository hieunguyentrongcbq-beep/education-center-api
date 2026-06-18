<?php
$isEdit = !empty($course);
$c = $course ?? [];
$totalSessions = (int)$old('total_sessions', $c['total_sessions'] ?? 20);
$weeksHint = (int)ceil($totalSessions / 2);
$dayPrimary = (int)$old('day_primary', $c['day_primary'] ?? 1);
$daySecondary = (int)$old('day_secondary', $c['day_secondary'] ?? 4);
$statusVal = $old('status', $c['status'] ?? 'ACTIVE');
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
                <input type="number" name="tuition_fee" min="1" step="1000" value="<?= htmlspecialchars($old('tuition_fee', $c['tuition_fee'] ?? '')) ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Ngày học chính 1 (0=CN, 1=T2...) *</label>
                <select name="day_primary" required>
                    <?php foreach ([1=>'Thứ hai',4=>'Thứ năm',2=>'Thứ ba',5=>'Thứ sáu'] as $v=>$l): ?>
                        <option value="<?= $v ?>" <?= $dayPrimary===$v?'selected':'' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Ngày học chính 2 *</label>
                <select name="day_secondary" required>
                    <?php foreach ([4=>'Thứ năm',1=>'Thứ hai',3=>'Thứ tư',6=>'Thứ bảy'] as $v=>$l): ?>
                        <option value="<?= $v ?>" <?= $daySecondary===$v?'selected':'' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
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
