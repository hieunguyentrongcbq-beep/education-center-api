<?php

$days = ['Chủ nhật','Thứ hai','Thứ ba','Thứ tư','Thứ năm','Thứ sáu','Thứ bảy'];

$weekLabel = date('d/m/Y', strtotime($week)) . ' — ' . date('d/m/Y', strtotime($week.' +6 days'));
$prevWeek = date('Y-m-d', strtotime($week . ' -7 days'));
$nextWeek = date('Y-m-d', strtotime($week . ' +7 days'));

$attLabels = ['PRESENT'=>'Có mặt','ABSENT'=>'Vắng','LATE'=>'Muộn','EXCUSED'=>'Có phép'];

?>

<?php if (!empty($attendance)): ?>

<div class="card">

    <h2>Điểm danh gần đây</h2>

    <table>

        <thead><tr><th>Ngày</th><th>Lớp</th><th>Trạng thái</th></tr></thead>

        <tbody>

        <?php foreach (array_slice($attendance, 0, 5) as $a): ?>

            <tr>

                <td><?= date('d/m/Y', strtotime($a['attendance_date'])) ?></td>

                <td><?= htmlspecialchars($a['class_code']) ?></td>

                <td><?= htmlspecialchars($attLabels[$a['attendance_status']] ?? $a['attendance_status']) ?></td>

            </tr>

        <?php endforeach; ?>

        </tbody>

    </table>

    <a href="<?= $url('student/attendance') ?>">Xem tất cả →</a>

</div>

<?php endif; ?>

<div class="card">

    <h2>Lịch học — Tuần <?= $weekLabel ?></h2>

    <p class="form-hint">
        Chỉ hiển thị lớp bạn đã đăng ký, thanh toán và đang học (ACTIVE).
        <strong>Chưa ĐD</strong> = chưa điểm danh (giáo viên chưa chấm có mặt/vắng cho buổi đó).
    </p>

    <form method="GET" style="margin-bottom:16px;display:flex;gap:8px;align-items:center;flex-wrap:wrap">

        <label>Tuần bắt đầu (Thứ 2): </label>

        <input type="date" name="week" value="<?= htmlspecialchars($week) ?>" onchange="this.form.submit()">

        <a href="<?= $url('student/schedule?week=' . $prevWeek) ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-chevron-left"></i> Trở về
        </a>
        <a href="<?= $url('student/schedule?week=' . $nextWeek) ?>" class="btn btn-sm btn-outline-secondary">
            Tiếp <i class="bi bi-chevron-right"></i>
        </a>

    </form>



    <?php if (empty($items)): ?>

        <p class="empty">Không có buổi học trong tuần này. (Cần thanh toán trước khi có lịch)</p>

    <?php else: ?>

    <div class="schedule-grid">

        <?php for ($d = 0; $d <= 6; $d++): ?>

        <div class="schedule-day">

            <h4><?= $days[$d] ?></h4>

            <?php if (!empty($byDay[$d])): foreach ($byDay[$d] as $item):

                $type = $item['schedule_type'] ?? 'REGULAR';

                $cls = in_array($type, ['EXAM','MAKEUP'], true) ? strtolower($type) : '';

                $sessionDate = $item['session_date'] ?? '';

                $attKey = ($item['class_id'] ?? '') . '_' . $sessionDate;

                $attStatus = $item['attendance_status'] ?? ($sessionDate ? ($attMap[$attKey] ?? null) : null);

            ?>

                <div class="schedule-item <?= $cls ?>">

                    <strong><?= substr($item['start_time'],0,5) ?>-<?= substr($item['end_time'],0,5) ?></strong>

                    <?php if ($sessionDate): ?>

                        <br><small><?= date('d/m', strtotime($sessionDate)) ?></small>

                    <?php endif; ?>

                    <br><?= htmlspecialchars($item['class_code']) ?><br>

                    <?= htmlspecialchars($item['course_name']) ?><br>

                    GV: <?= htmlspecialchars(trim(($item['teacher_name'] ?? '') . (!empty($item['teacher_code']) ? ' (' . $item['teacher_code'] . ')' : '')) ?: 'Chưa phân công') ?>

                    <?php if ($type !== 'REGULAR'): ?>

                        <br><small><?= htmlspecialchars($type) ?></small>

                    <?php endif; ?>

                    <?php if ($attStatus): ?>
                        <br><span class="schedule-att-badge schedule-att-<?= strtolower($attStatus) ?>">ĐD: <?= htmlspecialchars($attLabels[$attStatus] ?? $attStatus) ?></span>
                    <?php elseif ($sessionDate): ?>
                        <br><span class="schedule-att-badge schedule-att-pending" title="Giáo viên chưa điểm danh buổi này">Chưa điểm danh</span>
                    <?php endif; ?>

                </div>

            <?php endforeach; else: ?>

                <span style="color:#cbd5e1;font-size:12px">—</span>

            <?php endif; ?>

        </div>

        <?php endfor; ?>

    </div>



    <table style="margin-top:20px">

        <thead><tr><th>Lớp</th><th>Khóa</th><th>Ngày</th><th>Thứ</th><th>Giờ</th><th>Giáo viên</th><th>Loại</th><th>Điểm danh</th></tr></thead>

        <tbody>

        <?php foreach ($items as $item):

            $sessionDate = $item['session_date'] ?? '';

            $attKey = ($item['class_id'] ?? '') . '_' . $sessionDate;

            $attStatus = $item['attendance_status'] ?? ($sessionDate ? ($attMap[$attKey] ?? null) : null);

        ?>

            <tr>

                <td><?= htmlspecialchars($item['class_code']) ?></td>

                <td><?= htmlspecialchars($item['course_name']) ?></td>

                <td><?= $sessionDate ? date('d/m/Y', strtotime($sessionDate)) : '-' ?></td>

                <td><?= $days[(int)$item['day_of_week']] ?? '-' ?></td>

                <td><?= substr($item['start_time'],0,5) ?>-<?= substr($item['end_time'],0,5) ?></td>

                <td><?= htmlspecialchars(trim(($item['teacher_name'] ?? '') . (!empty($item['teacher_code']) ? ' (' . $item['teacher_code'] . ')' : '')) ?: 'Chưa phân công') ?></td>

                <td><?= htmlspecialchars($item['schedule_type'] ?? 'REGULAR') ?></td>

                <td>
                    <?php if ($attStatus): ?>
                        <span class="schedule-att-badge schedule-att-<?= strtolower($attStatus) ?>"><?= htmlspecialchars($attLabels[$attStatus] ?? $attStatus) ?></span>
                    <?php elseif ($sessionDate): ?>
                        <span class="schedule-att-badge schedule-att-pending" title="Giáo viên chưa điểm danh buổi này">Chưa điểm danh</span>
                    <?php else: ?>—<?php endif; ?>
                </td>

            </tr>

        <?php endforeach; ?>

        </tbody>

    </table>

    <?php endif; ?>

</div>

