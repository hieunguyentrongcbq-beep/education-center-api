<div class="welcome-card">
    <h2><i class="bi bi-mortarboard me-2"></i>Xin chào, <?= htmlspecialchars($user['full_name'] ?? '') ?></h2>
    <p>Theo dõi lịch học, điểm danh và kết quả học tập của bạn.</p>
    <a href="<?= $url('student/schedule') ?>" class="btn btn-light btn-sm">
        <i class="bi bi-calendar-event me-1"></i> Xem lịch học đầy đủ
    </a>
</div>

<div class="card">
    <h2><i class="bi bi-calendar-week me-2 text-primary"></i>Lịch học của bạn</h2>
    <p class="form-hint mb-3"><i class="bi bi-info-circle me-1"></i>Chỉ hiển thị lớp đã thanh toán (PAID).</p>
    <?php if (empty($schedule)): ?>
        <p class="empty mb-0">Chưa có lịch học. Vui lòng liên hệ admin để thanh toán và xếp lớp.</p>
    <?php else: ?>
    <div class="table-responsive-wrap">
        <table>
            <thead><tr><th>Lớp</th><th>Khóa học</th><th>Thứ</th><th>Giờ</th><th>GV</th><th>Loại</th></tr></thead>
            <tbody>
            <?php $days=['CN','T2','T3','T4','T5','T6','T7']; foreach ($schedule as $item): ?>
                <?php
                $typeBadge = 'bg-light text-dark';
                if (($item['schedule_type'] ?? '') === 'EXAM') $typeBadge = 'bg-warning text-dark';
                if (($item['schedule_type'] ?? '') === 'MAKEUP') $typeBadge = 'bg-success';
                ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($item['class_code']) ?></td>
                    <td><?= htmlspecialchars($item['course_name']) ?></td>
                    <td><?= $days[(int)$item['day_of_week']] ?? '' ?></td>
                    <td><?= substr($item['start_time'],0,5) ?>–<?= substr($item['end_time'],0,5) ?></td>
                    <td><?= htmlspecialchars($item['teacher_name'] ?? '-') ?></td>
                    <td><span class="badge <?= $typeBadge ?>"><?= htmlspecialchars($item['schedule_type']) ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
