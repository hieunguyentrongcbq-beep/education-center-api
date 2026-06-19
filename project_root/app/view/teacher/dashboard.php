<div class="welcome-card">
    <h2><i class="bi bi-person-workspace me-2"></i>Xin chào, <?= htmlspecialchars($user['full_name'] ?? '') ?></h2>
    <p>Quản lý lịch dạy, điểm danh và chấm bài tập của bạn.</p>
    <a href="<?= $url('teacher/schedule') ?>" class="btn btn-light btn-sm">
        <i class="bi bi-calendar3 me-1"></i> Xem lịch dạy đầy đủ
    </a>
</div>

<div class="card">
    <h2><i class="bi bi-clock-history me-2 text-primary"></i>Buổi dạy sắp tới</h2>
    <?php if (empty($schedule)): ?>
        <p class="empty mb-0">Chưa có lịch dạy. Admin cần phân công bạn cho học viên.</p>
    <?php else: ?>
    <div class="table-responsive-wrap">
        <table>
            <thead><tr><th>Lớp</th><th>Khóa học</th><th>Thứ</th><th>Giờ</th><th>Học viên</th></tr></thead>
            <tbody>
            <?php $days=['CN','T2','T3','T4','T5','T6','T7']; foreach ($schedule as $item): ?>
                <tr>
                    <td><span class="badge bg-light text-dark"><?= htmlspecialchars($item['class_code']) ?></span></td>
                    <td><?= htmlspecialchars($item['course_name']) ?></td>
                    <td><?= $days[(int)$item['day_of_week']] ?? '' ?></td>
                    <td><i class="bi bi-clock text-muted me-1"></i><?= substr($item['start_time'],0,5) ?>–<?= substr($item['end_time'],0,5) ?></td>
                    <td><?= htmlspecialchars($item['student_name'] ?? '-') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
