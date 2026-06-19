<?php
$weekDayOptions = [
    1 => 'Thứ hai',
    2 => 'Thứ ba',
    3 => 'Thứ tư',
    4 => 'Thứ năm',
    5 => 'Thứ sáu',
    6 => 'Thứ bảy',
    0 => 'Chủ nhật',
];
$dayShort = [0 => 'CN', 1 => 'T2', 2 => 'T3', 3 => 'T4', 4 => 'T5', 5 => 'T6', 6 => 'T7'];
$dayOfWeekVal = (int)$old('day_of_week', 1);
?>
<div class="card">
    <form method="GET" action="<?= $url('admin/schedules') ?>" style="margin-bottom:16px">
        <div class="form-group">
            <label>Chọn lớp</label>
            <select name="class_id" onchange="this.form.submit()">
                <option value="">-- Chọn lớp --</option>
                <?php foreach ($classes as $cl): ?>
                    <option value="<?= $cl['id'] ?>" <?= $classId===(int)$cl['id']?'selected':'' ?>><?= htmlspecialchars($cl['class_code']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <?php if ($classId): ?>
    <h2>Thêm lịch (Thi / Học bù)</h2>
    <form method="POST" action="<?= $url('admin/schedules/store') ?>">
        <input type="hidden" name="class_id" value="<?= $classId ?>">
        <div class="form-row">
            <div class="form-group">
                <label>Loại</label>
                <select name="schedule_type">
                    <option value="EXAM">Thi</option>
                    <option value="MAKEUP">Học bù</option>
                    <option value="REGULAR">Thường</option>
                </select>
            </div>
            <div class="form-group">
                <label>Học viên (REGULAR bắt buộc)</label>
                <select name="student_id">
                    <option value="">-- Tất cả / không --</option>
                    <?php foreach ($students as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['student_code'].' - '.$s['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Thứ</label>
                <select name="day_of_week" required>
                    <?php foreach ($weekDayOptions as $num => $label): ?>
                        <option value="<?= $num ?>" <?= $dayOfWeekVal === $num ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Ngày cụ thể</label><input type="date" name="specific_date" value="<?= htmlspecialchars($old('specific_date', '')) ?>"></div>
            <div class="form-group"><label>Bắt đầu</label><input type="time" name="start_time" value="18:00" required></div>
            <div class="form-group"><label>Kết thúc</label><input type="time" name="end_time" value="20:00" required></div>
        </div>
        <button type="submit" class="btn btn-primary">Thêm lịch</button>
        <p class="form-hint">Thi / Học bù: bắt buộc nhập ngày cụ thể. Thường (REGULAR): có thể nhập ngày từng buổi hoặc để trống (lặp theo thứ).</p>
    </form>

    <h2 style="margin-top:24px">Lịch hiện tại</h2>
    <?php if (empty($schedules)): ?><p class="empty">Chưa có lịch</p><?php else: ?>
    <table>
        <thead><tr><th>Thứ</th><th>Giờ</th><th>Loại</th><th>HV</th><th>Ngày cụ thể</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($schedules as $sc): ?>
            <tr>
                <td><?= htmlspecialchars($dayShort[(int)$sc['day_of_week']] ?? (string)$sc['day_of_week']) ?></td>
                <td><?= substr($sc['start_time'],0,5) ?> - <?= substr($sc['end_time'],0,5) ?></td>
                <td><?= htmlspecialchars($sc['schedule_type']) ?></td>
                <td><?= htmlspecialchars($sc['sv_code'] ?? $sc['student_code'] ?? '-') ?></td>
                <td><?= $sc['specific_date'] ? date('d/m/Y', strtotime($sc['specific_date'])) : '-' ?></td>
                <td class="d-flex flex-wrap gap-1">
                    <a href="<?= $url('admin/schedules/' . (int)$sc['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
                    <form method="POST" action="<?= $url('admin/schedules/'.$sc['id'].'/delete') ?>">
                        <input type="hidden" name="class_id" value="<?= $classId ?>">
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Xóa lịch này?')">Xóa</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
    <?php endif; ?>
</div>
