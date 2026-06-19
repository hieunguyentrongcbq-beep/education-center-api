<div class="card">
    <form method="GET">
        <div class="form-group">
            <label>Chọn lớp</label>
            <select name="class_id" onchange="this.form.submit()">
                <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $classId===(int)$c['id']?'selected':'' ?>><?= htmlspecialchars($c['class_code']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
    <?php if ($myRank): ?>
        <p><strong>Vị trí của bạn:</strong> #<?= $myRank['rank_pos'] ?> — Điểm TB: <?= number_format((float)$myRank['avg_score'],1) ?> (<?= $p2->levelLabel($myRank['level']) ?>)</p>
    <?php endif; ?>
</div>
<div class="card">
    <h2>Bảng xếp hạng lớp</h2>
    <?php if (empty($comparison)): ?><p class="empty">Chưa có điểm để so sánh</p><?php else: ?>
    <table>
        <thead><tr><th>Hạng</th><th>Học viên</th><th>ĐTB</th><th>Xếp loại</th></tr></thead>
        <tbody>
        <?php foreach ($comparison as $row): ?>
            <tr style="<?= $myRank && (int)$row['student_id']===(int)$myRank['student_id'] ? 'background:#eff6ff;font-weight:600' : '' ?>">
                <td>#<?= $row['rank_pos'] ?></td>
                <td><?= htmlspecialchars($row['full_name']) ?></td>
                <td><?= number_format((float)$row['avg_score'], 1) ?></td>
                <td><?= $p2->levelLabel($row['level']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
