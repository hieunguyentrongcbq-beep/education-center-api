<?php

$sc = $schedule;

$days = [
    1 => 'Thứ hai',
    2 => 'Thứ ba',
    3 => 'Thứ tư',
    4 => 'Thứ năm',
    5 => 'Thứ sáu',
    6 => 'Thứ bảy',
    0 => 'Chủ nhật',
];

$startVal = substr($sc['start_time'] ?? '18:00:00', 0, 5);

$endVal = substr($sc['end_time'] ?? '20:00:00', 0, 5);

$specificVal = !empty($sc['specific_date']) ? $sc['specific_date'] : '';

?>

<div class="card">

    <h2>Sửa lịch</h2>

    <p class="text-muted small mb-3">

        Lớp: <strong><?= htmlspecialchars($sc['class_code']) ?></strong>

        — <?= htmlspecialchars($sc['course_name'] ?? '') ?>

    </p>



    <form method="POST" action="<?= $url('admin/schedules/' . (int)$sc['id'] . '/update') ?>">

        <input type="hidden" name="class_id" value="<?= (int)$sc['class_id'] ?>">

        <div class="form-row">

            <div class="form-group">

                <label>Loại</label>

                <select name="schedule_type">

                    <?php foreach (['REGULAR' => 'Thường', 'EXAM' => 'Thi', 'MAKEUP' => 'Học bù', 'EXTRA' => 'Bổ sung'] as $val => $label): ?>

                        <option value="<?= $val ?>" <?= ($sc['schedule_type'] ?? 'REGULAR') === $val ? 'selected' : '' ?>>

                            <?= htmlspecialchars($label) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <div class="form-group">

                <label>Học viên (REGULAR bắt buộc)</label>

                <select name="student_id">

                    <option value="">-- Không / cả lớp --</option>

                    <?php foreach ($students as $s): ?>

                        <option value="<?= $s['id'] ?>" <?= (int)($sc['student_id'] ?? 0) === (int)$s['id'] ? 'selected' : '' ?>>

                            <?= htmlspecialchars($s['student_code'] . ' - ' . $s['full_name']) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

        </div>

        <div class="form-row">

            <div class="form-group">

                <label>Thứ</label>

                <select name="day_of_week" required>

                    <?php foreach ($days as $num => $label): ?>

                        <option value="<?= $num ?>" <?= (int)$sc['day_of_week'] === $num ? 'selected' : '' ?>>

                            <?= htmlspecialchars($label) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <div class="form-group">

                <label>Ngày cụ thể</label>

                <input type="date" name="specific_date" value="<?= htmlspecialchars($specificVal) ?>">

            </div>

            <div class="form-group">

                <label>Bắt đầu *</label>

                <input type="time" name="start_time" value="<?= htmlspecialchars($startVal) ?>" required>

            </div>

            <div class="form-group">

                <label>Kết thúc *</label>

                <input type="time" name="end_time" value="<?= htmlspecialchars($endVal) ?>" required>

            </div>

        </div>

        <div class="d-flex flex-wrap gap-2">

            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>

            <a href="<?= $url('admin/schedules?class_id=' . (int)$sc['class_id']) ?>" class="btn btn-light">Hủy</a>

        </div>

        <p class="form-hint">Thi / Học bù bắt buộc có ngày. REGULAR có thể gắn ngày từng buổi. GV và HV sẽ nhận thông báo khi lưu.</p>

    </form>

</div>


