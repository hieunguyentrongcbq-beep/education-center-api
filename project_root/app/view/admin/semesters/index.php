<?php
$statusBadges = [
    'UPCOMING' => 'bg-info text-dark',
    'ONGOING' => 'bg-success',
    'COMPLETED' => 'bg-secondary',
];
$statusLabels = [
    'UPCOMING' => 'Sắp diễn ra',
    'ONGOING' => 'Đang diễn ra',
    'COMPLETED' => 'Đã kết thúc',
];
?>
<div class="page-actions actions">
    <a href="<?= $url('admin/semesters/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Thêm học kỳ
    </a>
</div>
<div class="card">
    <p class="form-hint">Học kỳ dùng khi mở lớp học (dropdown trong form lớp).</p>
    <?php if (empty($semesters)): ?>
        <p class="empty mb-0">Chưa có học kỳ.</p>
    <?php else: ?>
    <div class="table-responsive-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên học kỳ</th>
                    <th>Bắt đầu</th>
                    <th>Kết thúc</th>
                    <th>Lớp</th>
                    <th>TT</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($semesters as $s):
                $st = $s['status'] ?? 'UPCOMING';
            ?>
                <tr>
                    <td><?= (int)$s['id'] ?></td>
                    <td class="fw-semibold"><?= htmlspecialchars($s['semester_name']) ?></td>
                    <td><?= date('d/m/Y', strtotime($s['start_date'])) ?></td>
                    <td><?= date('d/m/Y', strtotime($s['end_date'])) ?></td>
                    <td><?= (int)($s['class_count'] ?? 0) ?></td>
                    <td>
                        <span class="badge <?= $statusBadges[$st] ?? 'bg-secondary' ?>">
                            <?= htmlspecialchars($statusLabels[$st] ?? $st) ?>
                        </span>
                    </td>
                    <td class="actions d-flex flex-wrap gap-1">
                        <a href="<?= $url('admin/semesters/' . (int)$s['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i> Sửa
                        </a>
                        <form method="POST" action="<?= $url('admin/semesters/' . (int)$s['id'] . '/delete') ?>"
                              onsubmit="return confirm('Xóa học kỳ <?= htmlspecialchars($s['semester_name']) ?>?')">
                            <button type="submit" class="btn btn-sm btn-danger"
                                <?= (int)($s['class_count'] ?? 0) > 0 ? 'disabled title="Học kỳ đang có lớp"' : '' ?>>
                                Xóa
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
