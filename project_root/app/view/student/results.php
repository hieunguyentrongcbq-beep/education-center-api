<div class="card">
    <h2>Điểm & Nhận xét sau khóa học</h2>
    <?php if (empty($evaluations)): ?>
        <p class="empty">Chưa có đánh giá. GV sẽ chấm bài và đánh giá sau khi bạn nộp bài.</p>
    <?php else: ?>
    <table>
        <thead><tr><th>Lớp</th><th>Khóa học</th><th>ĐTB</th><th>Xếp loại</th><th>Học lại</th><th>GV</th><th>Nhận xét</th></tr></thead>
        <tbody>
        <?php foreach ($evaluations as $ev): ?>
            <tr>
                <td><?= htmlspecialchars($ev['class_code']) ?></td>
                <td><?= htmlspecialchars($ev['course_name']) ?></td>
                <td><?= number_format((float)$ev['avg_score'], 1) ?>/10</td>
                <td><strong><?= $p2->levelLabel($ev['level']) ?></strong></td>
                <td><?= $ev['retake_needed'] ? 'Cần học lại' : 'Đạt' ?></td>
                <td><?= htmlspecialchars($ev['teacher_name'] ?? '-') ?></td>
                <td><?= htmlspecialchars($ev['teacher_comment'] ?? '-') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
