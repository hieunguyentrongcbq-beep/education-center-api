<?php
$weekDayLabels = $weekDayLabels ?? [];
$weekDayOrder = $weekDayOrder ?? [1, 2, 3, 4, 5, 6, 0];
$classTeachingDays = $classTeachingDays ?? [];
$currentDow = $assignment['day_of_week'];
if ($currentDow !== null && $currentDow !== '') {
    $currentDow = (string)(int)$currentDow;
} else {
    $currentDow = '';
}
$statuses = [
    'CONFIRMED' => 'Đã xác nhận',
    'PENDING'   => 'Chờ xác nhận',
    'CANCELLED' => 'Đã hủy',
];
?>
<div class="card">
    <h2>Sửa phân công giáo viên</h2>
    <p class="text-muted small mb-3">
        Lớp: <strong><?= htmlspecialchars($assignment['class_code']) ?></strong>
        — <?= htmlspecialchars($assignment['course_name'] ?? '') ?>
    </p>

    <form method="POST" action="<?= $url('admin/assignments/' . (int)$assignment['id'] . '/update') ?>">
        <div class="form-row">
            <div class="form-group">
                <label>Học viên (đã thanh toán) *</label>
                <select name="student_id" required>
                    <?php foreach ($students as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= (int)$assignment['student_id'] === (int)$s['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['student_code'] . ' - ' . $s['full_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Giáo viên *</label>
                <select name="teacher_id" required>
                    <?php foreach ($teachers as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= (int)$assignment['teacher_id'] === (int)$t['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['teacher_code'] . ' - ' . $t['full_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Thứ dạy</label>
                <select name="day_of_week">
                    <option value="" <?= $currentDow === '' ? 'selected' : '' ?>>— (mọi thứ)</option>
                    <?php foreach ($weekDayOrder as $dow):
                        $label = $weekDayLabels[$dow] ?? ('Thứ ' . $dow);
                        if (in_array($dow, $classTeachingDays, true)) {
                            $label .= ' ★';
                        }
                    ?>
                        <option value="<?= $dow ?>" <?= $currentDow === (string)$dow ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Trạng thái</label>
                <select name="assignment_status">
                    <?php foreach ($statuses as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($assignment['assignment_status'] ?? 'CONFIRMED') === $val ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
            <a href="<?= $url('admin/assignments?class_id=' . (int)$assignment['class_id']) ?>" class="btn btn-light">Hủy</a>
        </div>
        <p class="form-hint">★ = buổi học chính của khóa. Hệ thống chặn nếu GV trùng lịch (khi trạng thái Đã xác nhận).</p>
    </form>
</div>
