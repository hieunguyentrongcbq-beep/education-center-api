<?php
$weekDayLabels = $weekDayLabels ?? [];
$weekDayOrder = $weekDayOrder ?? [1, 2, 3, 4, 5, 6, 0];
$classTeachingDays = $classTeachingDays ?? [];
$classId = $classId ?? null;
?>
<div class="card">
    <h2>Phân công giáo viên (1 HV - 1 GV)</h2>

    <form method="GET" action="<?= $url('admin/assignments') ?>" class="form-row" style="margin-bottom:16px">
        <div class="form-group">
            <label>Chọn lớp để phân công</label>
            <select name="class_id" onchange="this.form.submit()" required>
                <option value="">-- Chọn lớp --</option>
                <?php foreach ($classes as $cl): ?>
                    <option value="<?= $cl['id'] ?>" <?= ($classId ?? 0) === (int)$cl['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cl['class_code'] . ' - ' . ($cl['course_name'] ?? '')) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <?php if ($classId && $selectedClass): ?>
    <p class="form-hint mb-3">
        Lớp <strong><?= htmlspecialchars($selectedClass['class_code']) ?></strong>
        (<?= htmlspecialchars($selectedClass['course_name'] ?? '') ?>)
        — học
        <?php
        $dayNames = array_map(fn($d) => $weekDayLabels[$d] ?? ('Thứ ' . $d), $classTeachingDays);
        echo htmlspecialchars(implode(' & ', $dayNames) ?: 'chưa cấu hình');
        ?>.
        Buổi học chính của khóa được đánh dấu <strong>★</strong> trong danh sách thứ dạy.
    </p>

    <form method="POST" action="<?= $url('admin/assignments/store') ?>">
        <input type="hidden" name="class_id" value="<?= (int)$classId ?>">
        <div class="form-row">
            <div class="form-group">
                <label>Học viên (đã thanh toán lớp này) *</label>
                <select name="student_id" required>
                    <?php if (empty($students)): ?>
                        <option value="">— Chưa có HV đã thanh toán —</option>
                    <?php else: ?>
                        <?php foreach ($students as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['student_code'] . ' - ' . $s['full_name']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Giáo viên *</label>
                <select name="teacher_id" required>
                    <?php foreach ($teachers as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['teacher_code'] . ' - ' . $t['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Thứ dạy *</label>
                <select name="day_of_week" required>
                    <option value="">— Chọn thứ —</option>
                    <?php foreach ($weekDayOrder as $dow):
                        $label = $weekDayLabels[$dow] ?? ('Thứ ' . $dow);
                        if (in_array($dow, $classTeachingDays, true)) {
                            $label .= ' ★';
                        }
                        $selected = in_array($dow, $classTeachingDays, true) && count($classTeachingDays) === 1;
                    ?>
                        <option value="<?= $dow ?>" <?= $selected ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary" <?= empty($students) ? 'disabled' : '' ?>>Phân công</button>
        <p class="form-hint">Hệ thống chặn nếu GV trùng lịch với buổi dạy khác.</p>
    </form>
    <?php elseif ($classId): ?>
        <p class="empty">Không tìm thấy thông tin lớp.</p>
    <?php else: ?>
        <p class="empty">Chọn lớp ở trên để xem học viên đã thanh toán và phân công giáo viên.</p>
    <?php endif; ?>

    <h2 style="margin-top:24px">Danh sách phân công<?= $classId ? ' — lớp đã chọn' : '' ?></h2>
    <table>
        <thead><tr><th>GV</th><th>HV</th><th>Lớp</th><th>Thứ</th><th>TT</th><th></th></tr></thead>
        <tbody>
        <?php if (empty($assignments)): ?>
            <tr><td colspan="6" class="empty">Chưa có phân công<?= $classId ? ' cho lớp này' : '' ?>.</td></tr>
        <?php endif; ?>
        <?php foreach ($assignments as $a):
            $dow = $a['day_of_week'];
            $dowLabel = ($dow === null || $dow === '') ? 'Mọi thứ' : ($weekDayLabels[(int)$dow] ?? $dow);
        ?>
            <tr>
                <td><?= htmlspecialchars($a['teacher_name'] . ' (' . $a['teacher_code'] . ')') ?></td>
                <td><?= htmlspecialchars($a['student_name'] ?? '-') ?></td>
                <td><?= htmlspecialchars($a['class_code']) ?></td>
                <td><?= htmlspecialchars($dowLabel) ?></td>
                <td><?= htmlspecialchars($a['assignment_status'] ?? 'CONFIRMED') ?></td>
                <td class="d-flex flex-wrap gap-1">
                    <a href="<?= $url('admin/assignments/' . (int)$a['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
                    <form method="POST" action="<?= $url('admin/assignments/' . $a['id'] . '/delete') ?>">
                        <input type="hidden" name="class_id" value="<?= (int)$a['class_id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Xóa phân công này?')">Xóa</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
