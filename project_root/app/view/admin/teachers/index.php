<div class="actions" style="margin-bottom:16px">
    <a href="<?= $url('admin/teachers/create') ?>" class="btn btn-primary">+ Thêm giáo viên</a>
</div>
<div class="card">
    <table>
        <thead><tr><th>Mã GV</th><th>Họ tên</th><th>Email</th><th>Điện thoại</th><th>Chuyên môn</th><th>Loại</th><th>TT</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($teachers as $t): ?>
            <tr>
                <td><?= htmlspecialchars($t['teacher_code']) ?></td>
                <td><?= htmlspecialchars($t['full_name']) ?></td>
                <td><?= htmlspecialchars($t['email']) ?></td>
                <td><?= htmlspecialchars($t['phone'] ?? '—') ?></td>
                <td><?= htmlspecialchars($t['specialization']) ?></td>
                <td><?= ($t['teacher_type'] ?? '') === 'VISITING' ? 'Mời giảng' : 'Cơ hữu' ?></td>
                <td><?= htmlspecialchars($t['status']) ?></td>
                <td class="actions">
                    <a href="<?= $url('admin/teachers/'.$t['id'].'/edit') ?>" class="btn btn-sm btn-secondary">Sửa</a>
                    <form method="POST" action="<?= $url('admin/teachers/'.$t['id'].'/delete') ?>" style="display:inline" onsubmit="return confirm('Xóa GV?')">
                        <button class="btn btn-sm btn-danger">Xóa</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
