<?php
$isEdit = !empty($plan);
$p = $plan ?? [];
$statusVal = $old('status', $p['status'] ?? 'DRAFT');
$statusOptions = [
    'DRAFT' => 'Nháp',
    'APPROVED' => 'Đã duyệt',
    'CANCELLED' => 'Đã hủy',
];
$courseId = (int)$old('course_id', $p['course_id'] ?? 0);
$semesterId = (int)$old('semester_id', $p['semester_id'] ?? 0);
?>
<div class="card">
    <h2><?= $isEdit ? 'Sửa kế hoạch mở lớp' : 'Thêm kế hoạch mở lớp' ?></h2>
    <form method="POST" action="<?= $url($isEdit ? 'admin/class-plans/' . (int)$p['id'] . '/update' : 'admin/class-plans/store') ?>">
        <div class="form-row">
            <div class="form-group">
                <label>Khóa học *</label>
                <select name="course_id" required>
                    <option value="">-- Chọn khóa học --</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= $courseId === (int)$c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['course_code'] . ' — ' . $c['course_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Học kỳ *</label>
                <select name="semester_id" required>
                    <option value="">-- Chọn học kỳ --</option>
                    <?php foreach ($semesters as $s): ?>
                        <option value="<?= (int)$s['id'] ?>" <?= $semesterId === (int)$s['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['semester_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Số lớp dự kiến *</label>
                <input type="number" name="planned_class_count" min="1" max="99" required
                       value="<?= htmlspecialchars($old('planned_class_count', $p['planned_class_count'] ?? '1')) ?>">
            </div>
            <div class="form-group">
                <label>Sĩ số mục tiêu / lớp *</label>
                <input type="number" name="target_student_count" min="1" max="200" required
                       value="<?= htmlspecialchars($old('target_student_count', $p['target_student_count'] ?? '20')) ?>">
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
        <p class="form-hint">Đặt trạng thái <strong>Đã duyệt</strong> khi sẵn sàng mở lớp — có nút shortcut tạo lớp từ kế hoạch.</p>
        <div class="d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Lưu thay đổi' : 'Tạo kế hoạch' ?></button>
            <a href="<?= $url('admin/class-plans') ?>" class="btn btn-light">Hủy</a>
        </div>
    </form>
</div>
