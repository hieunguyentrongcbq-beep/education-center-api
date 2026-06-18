<?php
$statusBadges = [
    'DRAFT' => 'bg-secondary',
    'APPROVED' => 'bg-success',
    'CANCELLED' => 'bg-danger',
];
$statusLabels = [
    'DRAFT' => 'Nháp',
    'APPROVED' => 'Đã duyệt',
    'CANCELLED' => 'Đã hủy',
];
?>
<div class="page-actions actions">
    <a href="<?= $url('admin/class-plans/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Thêm kế hoạch
    </a>
    <a href="<?= $url('admin/classes/create') ?>" class="btn btn-outline-primary">
        <i class="bi bi-collection"></i> Mở lớp học
    </a>
</div>
<div class="card">
    <h2 class="h5 mb-2">Kế hoạch mở lớp theo học kỳ</h2>
    <p class="form-hint">Lập kế hoạch số lớp và sĩ số mục tiêu trước khi mở lớp thực tế. Mỗi khóa + học kỳ chỉ một kế hoạch.</p>
    <?php if (empty($plans)): ?>
        <p class="empty mb-0">Chưa có kế hoạch mở lớp.</p>
    <?php else: ?>
    <div class="table-responsive-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Khóa học</th>
                    <th>Học kỳ</th>
                    <th>Lớp KH</th>
                    <th>Đã mở</th>
                    <th>Sĩ số / lớp</th>
                    <th>TT</th>
                    <th>Người tạo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($plans as $p):
                $st = $p['status'] ?? 'DRAFT';
                $planned = (int)($p['planned_class_count'] ?? 0);
                $actual = (int)($p['actual_class_count'] ?? 0);
                $progressOk = $actual >= $planned;
            ?>
                <tr>
                    <td><?= (int)$p['id'] ?></td>
                    <td>
                        <span class="fw-semibold"><?= htmlspecialchars($p['course_code']) ?></span><br>
                        <small class="text-muted"><?= htmlspecialchars($p['course_name']) ?></small>
                    </td>
                    <td><?= htmlspecialchars($p['semester_name']) ?></td>
                    <td><?= $planned ?></td>
                    <td>
                        <span class="<?= $progressOk ? 'text-success' : '' ?> fw-semibold"><?= $actual ?></span>
                        <?php if ($planned > 0 && !$progressOk): ?>
                            <small class="text-muted">/ <?= $planned ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?= (int)($p['target_student_count'] ?? 0) ?> HV</td>
                    <td>
                        <span class="badge <?= $statusBadges[$st] ?? 'bg-secondary' ?>">
                            <?= htmlspecialchars($statusLabels[$st] ?? $st) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($p['created_by_name'] ?? '—') ?></td>
                    <td class="actions d-flex flex-wrap gap-1">
                        <?php if ($st === 'APPROVED'): ?>
                        <a href="<?= $url('admin/classes/create?course_id='.(int)$p['course_id'].'&semester_id='.(int)$p['semester_id'].'&max_students='.(int)$p['target_student_count']) ?>"
                           class="btn btn-sm btn-success" title="Mở lớp từ kế hoạch">
                            <i class="bi bi-plus-circle"></i>
                        </a>
                        <?php endif; ?>
                        <a href="<?= $url('admin/class-plans/' . (int)$p['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="<?= $url('admin/class-plans/' . (int)$p['id'] . '/delete') ?>"
                              onsubmit="return confirm('Xóa kế hoạch #<?= (int)$p['id'] ?>?')">
                            <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
