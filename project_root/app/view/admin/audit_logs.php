<?php
$exportQuery = http_build_query(array_filter([
    'action' => $filterAction ?? '',
    'entity_name' => $filterEntity ?? '',
    'date_from' => $filterDateFrom ?? '',
    'date_to' => $filterDateTo ?? '',
]));
$hasFilter = ($filterAction ?? '') || ($filterEntity ?? '') || ($filterDateFrom ?? '') || ($filterDateTo ?? '');
?>
<div class="card">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h2 class="mb-0">Nhật ký hệ thống (Audit Logs)</h2>
        <a href="<?= $url('admin/audit-logs/export' . ($exportQuery ? '?' . $exportQuery : '')) ?>"
           class="btn btn-outline-primary btn-sm">
            <i class="bi bi-download me-1"></i> Export CSV
        </a>
    </div>

    <form method="GET" action="<?= $url('admin/audit-logs') ?>" class="mb-4">
        <div class="form-row align-items-end">
            <div class="form-group">
                <label>Hành động</label>
                <select name="action">
                    <option value="">Tất cả</option>
                    <?php foreach ($actionOptions as $act): ?>
                        <option value="<?= htmlspecialchars($act) ?>" <?= ($filterAction ?? '') === $act ? 'selected' : '' ?>>
                            <?= htmlspecialchars($act) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Entity</label>
                <select name="entity_name">
                    <option value="">Tất cả</option>
                    <?php foreach ($entityOptions as $ent): ?>
                        <option value="<?= htmlspecialchars($ent) ?>" <?= ($filterEntity ?? '') === $ent ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ent) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Từ ngày</label>
                <input type="date" name="date_from" value="<?= htmlspecialchars($filterDateFrom ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Đến ngày</label>
                <input type="date" name="date_to" value="<?= htmlspecialchars($filterDateTo ?? '') ?>">
            </div>
            <div class="form-group d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary btn-sm">Lọc</button>
                <?php if ($hasFilter): ?>
                    <a href="<?= $url('admin/audit-logs') ?>" class="btn btn-light btn-sm">Xóa bộ lọc</a>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <?php if (empty($logs)): ?>
        <p class="empty mb-0">Không có nhật ký<?= $hasFilter ? ' phù hợp bộ lọc' : '' ?>.</p>
    <?php else: ?>
    <div class="table-responsive-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Thời gian</th>
                    <th>User</th>
                    <th>Hành động</th>
                    <th>Entity</th>
                    <th>Entity ID</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= (int)$log['id'] ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></td>
                    <td><?= htmlspecialchars($log['full_name'] ?? 'System') ?></td>
                    <td><code><?= htmlspecialchars($log['action']) ?></code></td>
                    <td><?= htmlspecialchars($log['entity_name']) ?></td>
                    <td><?= $log['entity_id'] !== null ? (int)$log['entity_id'] : '-' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="form-hint mt-2 mb-0">Hiển thị tối đa 200 bản ghi. Export CSV lấy tối đa 5.000 bản ghi theo bộ lọc hiện tại.</p>
    <?php endif; ?>
</div>
