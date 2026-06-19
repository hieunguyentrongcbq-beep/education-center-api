<div class="actions" style="margin-bottom:16px">
    <a href="<?= $url('admin/students/create') ?>" class="btn btn-primary">+ Thêm học viên</a>
</div>
<div class="card">
    <table>
        <thead><tr><th>Mã HV</th><th>Họ tên</th><th>Email</th><th>Điện thoại</th><th>TT</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($students as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['student_code']) ?></td>
                <td><?= htmlspecialchars($s['full_name']) ?></td>
                <td><?= htmlspecialchars($s['email']) ?></td>
                <td><?= htmlspecialchars($s['phone'] ?? '') ?></td>
                <td><?= htmlspecialchars($s['status']) ?></td>
                <td class="actions">
                    <a href="<?= $url('admin/students/'.$s['id'].'/edit') ?>" class="btn btn-sm btn-secondary">Sửa</a>
                    <form method="POST" action="<?= $url('admin/students/'.$s['id'].'/delete') ?>" style="display:inline" onsubmit="return confirm('Xóa HV?')">
                        <button class="btn btn-sm btn-danger">Xóa</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
