<div class="actions" style="margin-bottom:16px">
    <a href="<?= $url('admin/classes/create') ?>" class="btn btn-primary">+ Mở lớp học</a>
</div>
<div class="card">
    <table>
        <thead><tr><th>Mã lớp</th><th>Khóa học</th><th>Bắt đầu</th><th>Kết thúc</th><th>SV tối đa</th><th>TT</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($classes as $cl): ?>
            <tr>
                <td><?= htmlspecialchars($cl['class_code']) ?></td>
                <td><?= htmlspecialchars($cl['course_name'] ?? '') ?></td>
                <td><?= date('d/m/Y', strtotime($cl['start_date'])) ?></td>
                <td><?= date('d/m/Y', strtotime($cl['end_date'])) ?></td>
                <td><?= (int)$cl['max_students'] ?></td>
                <td><?= htmlspecialchars($cl['status']) ?></td>
                <td class="actions">
                    <a href="<?= $url('admin/classes/'.$cl['id'].'/edit') ?>" class="btn btn-sm btn-secondary">Sửa</a>
                    <form method="POST" action="<?= $url('admin/classes/'.$cl['id'].'/delete') ?>" style="display:inline" onsubmit="return confirm('Xóa lớp?')">
                        <button class="btn btn-sm btn-danger">Xóa</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
