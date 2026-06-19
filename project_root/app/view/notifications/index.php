<?php
$basePath = $portal . '/notifications';
?>
<div class="card">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <h2 class="mb-1">Hộp thư thông báo</h2>
            <p class="text-muted mb-0 small notif-summary">
                <?php if ($unreadCount > 0): ?>
                    Bạn có <strong><?= (int)$unreadCount ?></strong> thông báo chưa đọc.
                <?php else: ?>
                    Không có thông báo mới.
                <?php endif; ?>
            </p>
        </div>
        <?php if ($unreadCount > 0): ?>
        <button type="button" class="btn btn-outline-primary btn-sm js-mark-all-read">
            <i class="bi bi-check2-all me-1"></i> Đánh dấu tất cả đã đọc
        </button>
        <?php endif; ?>
    </div>

    <?php if (empty($notifications)): ?>
        <div class="empty-state text-center py-5">
            <i class="bi bi-bell-slash fs-1 text-muted"></i>
            <p class="text-muted mt-3 mb-0">Chưa có thông báo nào.</p>
        </div>
    <?php else: ?>
        <div class="notif-list">
            <?php foreach ($notifications as $n):
                $isRead = !empty($n['is_read']);
                $createdAt = !empty($n['created_at']) ? date('d/m/Y H:i', strtotime($n['created_at'])) : '-';
            ?>
            <div class="notif-item <?= $isRead ? '' : 'unread' ?>">
                <div class="notif-item-body">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
                        <strong class="notif-title"><?= htmlspecialchars($n['title']) ?></strong>
                        <small class="text-muted"><?= $createdAt ?></small>
                    </div>
                    <p class="notif-content mb-0"><?= nl2br(htmlspecialchars($n['content'] ?? '')) ?></p>
                </div>
                <?php if (!$isRead): ?>
                <div class="notif-item-action">
                    <button type="button" class="btn btn-sm btn-light js-mark-read" data-id="<?= (int)$n['id'] ?>" title="Đánh dấu đã đọc">
                        <i class="bi bi-check2"></i>
                    </button>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
