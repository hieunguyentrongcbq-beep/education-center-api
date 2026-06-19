<div class="card">
    <h2>Lịch sử điểm danh</h2>
    <?php if (empty($records)): ?>
        <p class="empty">Chưa có dữ liệu điểm danh. GV sẽ điểm danh sau mỗi buổi học.</p>
    <?php else: ?>
    <table>
        <thead><tr><th>Ngày</th><th>Lớp</th><th>Trạng thái</th><th>Ghi chú</th></tr></thead>
        <tbody>
        <?php foreach ($records as $r): ?>
            <tr>
                <td><?= date('d/m/Y', strtotime($r['attendance_date'])) ?></td>
                <td><?= htmlspecialchars($r['class_code']) ?></td>
                <td>
                    <?php
                    $badge = ['PRESENT'=>'✓ Có mặt','ABSENT'=>'✗ Vắng','LATE'=>'⏰ Muộn','EXCUSED'=>'📝 Có phép'];
                    echo $badge[$r['attendance_status']] ?? $r['attendance_status'];
                    ?>
                </td>
                <td><?= htmlspecialchars($r['note'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
