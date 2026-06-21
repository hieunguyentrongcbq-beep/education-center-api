<?php
$isEdit = !empty($semester);
$sem = $semester ?? [];
$statusVal = $old('status', $sem['status'] ?? 'UPCOMING');
$statusOptions = [
    'UPCOMING' => 'Sắp diễn ra',
    'ONGOING' => 'Đang diễn ra',
    'COMPLETED' => 'Đã kết thúc',
];
?>
<div class="card">
    <h2><?= $isEdit ? 'Sửa học kỳ' : 'Thêm học kỳ' ?></h2>
    <form method="POST" action="<?= $url($isEdit ? 'admin/semesters/' . (int)$sem['id'] . '/update' : 'admin/semesters/store') ?>">
        <div class="form-group">
            <label>Tên học kỳ *</label>
            <input name="semester_name" required maxlength="100"
                   value="<?= htmlspecialchars($old('semester_name', $sem['semester_name'] ?? '')) ?>"
                   placeholder="VD: Học kỳ 1 - 2026">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Ngày bắt đầu *</label>
                <input type="date" name="start_date" required
                       value="<?= htmlspecialchars($old('start_date', $sem['start_date'] ?? '')) ?>">
            </div>
            <div class="form-group">
                <label>Ngày kết thúc *</label>
                <input type="date" name="end_date" required
                       value="<?= htmlspecialchars($old('end_date', $sem['end_date'] ?? '')) ?>">
            </div>
            <div class="form-group">
                <label>Trạng thái</label>
                <select name="status">
                    <?php foreach ($statusOptions as $val => $label): ?>
                        <option value="<?= $val ?>" <?= $statusVal === $val ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Lưu thay đổi' : 'Tạo học kỳ' ?></button>
            <a href="<?= $url('admin/semesters') ?>" class="btn btn-light">Hủy</a>
        </div>
    </form>
</div>
