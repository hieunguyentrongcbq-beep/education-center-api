<?php
$statusLabels = ['PENDING' => 'Chưa trả', 'PAID' => 'Đã trả'];
$statusBadges = ['PENDING' => 'bg-warning text-dark', 'PAID' => 'bg-success'];
$typeLabels = ['FULL_TIME' => 'Cơ hữu', 'VISITING' => 'Mời giảng'];
?>
<div class="page-actions actions d-flex flex-wrap gap-2">
    <a href="<?= $url('admin/payrolls/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Tạo bảng lương
    </a>
</div>

<?php if (!empty($payrolls)): ?>
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card p-3 mb-0">
            <small class="text-muted">Chưa trả (theo bộ lọc)</small>
            <div class="fs-5 fw-semibold text-warning"><?= number_format($totalPending, 0, ',', '.') ?> đ</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-3 mb-0">
            <small class="text-muted">Đã trả (theo bộ lọc)</small>
            <div class="fs-5 fw-semibold text-success"><?= number_format($totalPaid, 0, ',', '.') ?> đ</div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <h2>Bảng lương giáo viên</h2>
    <p class="form-hint mb-3">Quản lý lương theo tháng. Có thể tính giờ dạy từ lịch phân công khi tạo/sửa.</p>

    <form method="GET" action="<?= $url('admin/payrolls') ?>" class="mb-4">
        <div class="form-row align-items-end">
            <div class="form-group">
                <label>Giáo viên</label>
                <select name="teacher_id" onchange="this.form.submit()">
                    <option value="">Tất cả</option>
                    <?php foreach ($teachers as $t): ?>
                        <option value="<?= (int)$t['id'] ?>" <?= ($teacherId ?? 0) === (int)$t['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['teacher_code'] . ' - ' . $t['full_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Tháng</label>
                <select name="month" onchange="this.form.submit()">
                    <option value="">Tất cả</option>
                    <?php foreach ($monthOptions as $m): ?>
                        <option value="<?= htmlspecialchars($m) ?>" <?= ($monthFilter ?? '') === $m ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Trạng thái</label>
                <select name="payment_status" onchange="this.form.submit()">
                    <option value="">Tất cả</option>
                    <?php foreach ($statusLabels as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($statusFilter ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <a href="<?= $url('admin/payrolls') ?>" class="btn btn-light btn-sm">Xóa lọc</a>
            </div>
        </div>
    </form>

    <?php if (empty($payrolls)): ?>
        <p class="empty mb-0">Chưa có bảng lương.</p>
    <?php else: ?>
    <div class="table-responsive-wrap">
        <table>
            <thead>
                <tr>
                    <th>GV</th>
                    <th>Loại</th>
                    <th>Tháng</th>
                    <th>Giờ dạy</th>
                    <th>Chuẩn</th>
                    <th>Lương</th>
                    <th>TT</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($payrolls as $p):
                $st = $p['payment_status'] ?? 'PENDING';
                $hours = (float)($p['teaching_hours'] ?? 0);
                $standard = (float)($p['standard_hours'] ?? 0);
            ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($p['full_name']) ?></strong><br>
                        <small class="text-muted"><?= htmlspecialchars($p['teacher_code']) ?></small>
                    </td>
                    <td><?= htmlspecialchars($typeLabels[$p['teacher_type'] ?? ''] ?? $p['teacher_type'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($p['month']) ?></td>
                    <td>
                        <?= number_format($hours, 1, ',', '.') ?>h
                        <?php if (($p['teacher_type'] ?? '') === 'FULL_TIME' && $standard > 0): ?>
                            <br><small class="text-muted <?= $hours < $standard ? 'text-danger' : 'text-success' ?>">
                                <?= $hours < $standard ? 'Thiếu' : ($hours > $standard ? 'Vượt' : 'Đạt') ?> chuẩn <?= number_format($standard, 0) ?>h
                            </small>
                        <?php endif; ?>
                    </td>
                    <td><?= ($p['teacher_type'] ?? '') === 'FULL_TIME' ? number_format($standard, 0) . 'h' : '—' ?></td>
                    <td class="fw-semibold"><?= number_format((float)$p['salary_amount'], 0, ',', '.') ?> đ</td>
                    <td>
                        <span class="badge <?= $statusBadges[$st] ?? 'bg-secondary' ?>">
                            <?= htmlspecialchars($statusLabels[$st] ?? $st) ?>
                        </span>
                    </td>
                    <td class="actions d-flex flex-wrap gap-1">
                        <a href="<?= $url('admin/payrolls/' . (int)$p['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
                        <?php if ($st === 'PENDING'): ?>
                        <form method="POST" action="<?= $url('admin/payrolls/' . (int)$p['id'] . '/mark-paid') ?>">
                            <input type="hidden" name="month" value="<?= htmlspecialchars($p['month']) ?>">
                            <button type="submit" class="btn btn-sm btn-success">Đã trả</button>
                        </form>
                        <?php endif; ?>
                        <form method="POST" action="<?= $url('admin/payrolls/' . (int)$p['id'] . '/delete') ?>"
                              onsubmit="return confirm('Xóa bảng lương tháng <?= htmlspecialchars($p['month']) ?>?')">
                            <input type="hidden" name="month" value="<?= htmlspecialchars($p['month']) ?>">
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
