<?php
$days = ['Chủ nhật','Thứ hai','Thứ ba','Thứ tư','Thứ năm','Thứ sáu','Thứ bảy'];
$weekLabel = date('d/m/Y', strtotime($week)) . ' — ' . date('d/m/Y', strtotime($week.' +6 days'));
$prevWeek = date('Y-m-d', strtotime($week . ' -7 days'));
$nextWeek = date('Y-m-d', strtotime($week . ' +7 days'));
$attLabels = ['PRESENT'=>'Có mặt','ABSENT'=>'Vắng','LATE'=>'Muộn','EXCUSED'=>'Có phép'];
$attendanceLink = function ($item) use ($url) {
    $classId = (int)($item['class_id'] ?? 0);
    $sessionDate = $item['session_date'] ?? '';
    if (!$classId || !$sessionDate) {
        return null;
    }
    return $url('teacher/attendance?class_id=' . $classId . '&date=' . urlencode($sessionDate));
};
$attBadge = function ($item) use ($attLabels) {
    if (empty($item['attendance_marked'])) {
        return '<span class="schedule-att-badge schedule-att-pending">Chưa ĐD</span>';
    }
    $st = $item['attendance_status'] ?? '';
    $cls = match ($st) {
        'PRESENT' => 'schedule-att-present',
        'ABSENT' => 'schedule-att-absent',
        'LATE' => 'schedule-att-late',
        'EXCUSED' => 'schedule-att-excused',
        default => 'schedule-att-pending',
    };
    $label = htmlspecialchars($attLabels[$st] ?? $st);
    return '<span class="schedule-att-badge ' . $cls . '">ĐD: ' . $label . '</span>';
};
?>
<div class="card">
    <h2>Lịch dạy — Tuần <?= $weekLabel ?></h2>
    <p class="form-hint">Điểm danh theo đúng ngày buổi học (<code>session_date</code>) — bấm link để mở form với ngày khớp lịch.</p>
    <form method="GET" style="margin-bottom:16px;display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <label>Tuần bắt đầu (Thứ 2): </label>
        <input type="date" name="week" value="<?= htmlspecialchars($week) ?>" onchange="this.form.submit()">
        <a href="<?= $url('teacher/schedule?week=' . $prevWeek) ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-chevron-left"></i> Trở về
        </a>
        <a href="<?= $url('teacher/schedule?week=' . $nextWeek) ?>" class="btn btn-sm btn-outline-secondary">
            Tiếp <i class="bi bi-chevron-right"></i>
        </a>
    </form>

    <?php if (empty($items)): ?>
        <p class="empty">Không có buổi dạy trong tuần này.</p>
    <?php else: ?>
    <div class="schedule-grid">
        <?php for ($d = 0; $d <= 6; $d++): ?>
        <div class="schedule-day">
            <h4><?= $days[$d] ?></h4>
            <?php if (!empty($byDay[$d])): foreach ($byDay[$d] as $item):
                $type = $item['schedule_type'] ?? 'REGULAR';
                $cls = in_array($type, ['EXAM','MAKEUP'], true) ? strtolower($type) : '';
            ?>
                <div class="schedule-item <?= $cls ?>">
                    <strong><?= substr($item['start_time'],0,5) ?>-<?= substr($item['end_time'],0,5) ?></strong>
                    <?php if (!empty($item['session_date'])): ?>
                        <br><small><?= date('d/m/Y', strtotime($item['session_date'])) ?></small>
                    <?php endif; ?>
                    <br><?= htmlspecialchars($item['class_code']) ?><br>
                    <?= htmlspecialchars($item['student_name'] ?? '') ?>
                    <?php if ($type !== 'REGULAR'): ?>
                        <br><small><?= htmlspecialchars($type) ?></small>
                    <?php endif; ?>
                    <br><?= $attBadge($item) ?>
                    <?php if ($link = $attendanceLink($item)): ?>
                        <br><a href="<?= $link ?>" class="btn btn-sm btn-outline-primary mt-1" title="Điểm danh buổi <?= htmlspecialchars($item['session_date'] ?? '') ?>">
                            <i class="bi bi-check2-square"></i> Điểm danh
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; else: ?>
                <span style="color:#cbd5e1;font-size:12px">—</span>
            <?php endif; ?>
        </div>
        <?php endfor; ?>
    </div>

    <table style="margin-top:20px">
        <thead><tr><th>Lớp</th><th>Khóa</th><th>Ngày buổi học</th><th>Thứ</th><th>Giờ</th><th>HV</th><th>Loại</th><th>Điểm danh</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['class_code']) ?></td>
                <td><?= htmlspecialchars($item['course_name']) ?></td>
                <td><?= !empty($item['session_date']) ? date('d/m/Y', strtotime($item['session_date'])) : '-' ?></td>
                <td><?= $days[(int)$item['day_of_week']] ?></td>
                <td><?= substr($item['start_time'],0,5) ?>-<?= substr($item['end_time'],0,5) ?></td>
                <td><?= htmlspecialchars($item['student_name'] ?? '-') ?></td>
                <td><?= htmlspecialchars($item['schedule_type']) ?></td>
                <td><?= $attBadge($item) ?></td>
                <td>
                    <?php if ($link = $attendanceLink($item)): ?>
                        <a href="<?= $link ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-check2-square"></i> Điểm danh
                        </a>
                    <?php else: ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
