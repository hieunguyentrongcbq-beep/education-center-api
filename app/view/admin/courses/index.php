<div class="page-actions actions">
    <a href="<?= $url('admin/courses/create') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Thêm khóa học</a>
</div>
<div class="card">
    <div class="table-responsive-wrap">
    <table>
        <thead>
            <tr>
                <th>ID</th><th>Mã</th><th>Tên</th><th>Số buổi</th><th>Tuần</th>
                <th>Thứ 1 / Thứ 2</th><th>Học phí</th><th>TT</th><th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($courses as $c): ?>
            <tr>
                <td><?= (int)$c['id'] ?></td>
                <td><?= htmlspecialchars($c['course_code']) ?></td>
                <td><?= htmlspecialchars($c['course_name']) ?></td>
                <td><?= (int)($c['total_sessions'] ?? $c['duration_weeks']*2) ?></td>
                <td><?= (int)$c['duration_weeks'] ?></td>
                <td><?= (int)($c['day_primary']??1) ?> / <?= (int)($c['day_secondary']??4) ?></td>
                <td><?= number_format((float)$c['tuition_fee'],0,',','.') ?> đ</td>
                <td><span class="badge <?= $c['status']==='ACTIVE'?'bg-success':'bg-secondary' ?>"><?= htmlspecialchars($c['status']) ?></span></td>
                <td class="actions">
                    <a href="<?= $url('admin/courses/'.$c['id'].'/edit') ?>" class="btn btn-sm btn-secondary"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="<?= $url('admin/courses/'.$c['id'].'/delete') ?>" style="display:inline" onsubmit="return confirm('Xóa khóa học?')">
                        <button class="btn btn-sm btn-danger">Xóa</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
