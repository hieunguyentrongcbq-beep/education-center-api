<div class="card">
    <form method="GET" class="form-row">
        <div class="form-group">
            <label>Lớp</label>
            <select name="class_id" onchange="this.form.submit()">
                <option value="">Tất cả</option>
                <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($classId??0)===(int)$c['id']?'selected':'' ?>><?= htmlspecialchars($c['class_code']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Ngày</label>
            <input type="date" name="date" value="<?= htmlspecialchars($date ?? '') ?>" onchange="this.form.submit()">
        </div>
    </form>
    <h2>Báo cáo điểm danh & Chấm công GV</h2>
    <table>
        <thead><tr><th>Ngày</th><th>Lớp</th><th>HV</th><th>TT</th><th>GV chấm công</th><th>GV</th></tr></thead>
        <tbody>
        <?php foreach ($records as $r): ?>
            <tr>
                <td><?= date('d/m/Y', strtotime($r['attendance_date'])) ?></td>
                <td><?= htmlspecialchars($r['class_code']) ?></td>
                <td><?= htmlspecialchars($r['full_name']) ?></td>
                <td><?= htmlspecialchars($r['attendance_status']) ?></td>
                <td><?= $r['tinh_luong'] ? '✓ Có' : '-' ?></td>
                <td><?= htmlspecialchars($r['teacher_name'] ?? '-') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
