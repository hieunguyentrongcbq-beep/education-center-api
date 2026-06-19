<div class="card">
    <h2>Gửi yêu cầu</h2>
    <form method="POST" action="<?= $url('teacher/leave/submit') ?>">
        <div class="form-row">
            <div class="form-group">
                <label>Loại</label>
                <select name="request_type">
                    <option value="LEAVE">Xin nghỉ</option>
                    <option value="MAKEUP">Xin học bù</option>
                </select>
            </div>
            <div class="form-group">
                <label>Lớp</label>
                <select name="class_id" required>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['class_code']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Ngày</label>
                <input type="date" name="request_date" required value="<?= date('Y-m-d') ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Lý do</label>
            <textarea name="reason" rows="3" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Gửi Admin duyệt</button>
    </form>
</div>
<div class="card">
    <h2>Yêu cầu của tôi</h2>
    <table>
        <thead><tr><th>Loại</th><th>Lớp</th><th>Ngày</th><th>TT</th><th>Lý do</th></tr></thead>
        <tbody>
        <?php foreach ($requests as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['request_type'] ?? 'LEAVE') ?></td>
                <td><?= htmlspecialchars($r['class_code'] ?? '-') ?></td>
                <td><?= date('d/m/Y', strtotime($r['request_date'])) ?></td>
                <td><?= htmlspecialchars($r['status']) ?></td>
                <td><?= htmlspecialchars($r['reason']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
