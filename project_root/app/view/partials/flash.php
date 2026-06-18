<?php if (!empty($flash)): ?>
    <?php foreach ($flash as $type => $message):
        $bsType = $type === 'error' ? 'danger' : ($type === 'success' ? 'success' : 'info');
        $icon = $type === 'error' ? 'exclamation-triangle-fill' : ($type === 'success' ? 'check-circle-fill' : 'info-circle-fill');
    ?>
        <div class="alert alert-<?= $bsType ?> alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-<?= $icon ?> me-2"></i><?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
