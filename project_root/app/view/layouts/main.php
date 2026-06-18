<?php
$portalKey = $portal ?? 'admin';
$assetBase = dirname($baseUrl);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'EduCenter') ?> — EduCenter</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $assetBase ?>/assets/css/app.css">
</head>
<body class="portal-<?= htmlspecialchars($portalKey) ?>">
<div class="app-shell">
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="app-main">
        <header class="app-topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light btn-icon d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-label="Menu">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <div>
                    <h1 class="page-title mb-0"><?= htmlspecialchars($title ?? '') ?></h1>
                    <p class="page-subtitle mb-0">EduCenter Portal</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <?php
                $unreadTop = (int)($unreadNotifications ?? 0);
                $notifPath = strtolower($user['role'] ?? 'admin') . '/notifications';
                ?>
                <a href="<?= $url($notifPath) ?>" class="btn btn-light btn-icon position-relative topbar-bell" title="Thông báo">
                    <i class="bi bi-bell"></i>
                    <?php if ($unreadTop > 0): ?>
                        <span class="topbar-bell-badge"><?= $unreadTop > 99 ? '99+' : $unreadTop ?></span>
                    <?php endif; ?>
                </a>
                <span class="badge portal-badge d-none d-sm-inline-flex"><?= htmlspecialchars($user['role'] ?? '') ?></span>
                <span class="topbar-user d-none d-md-inline"><?= htmlspecialchars($user['full_name'] ?? '') ?></span>
            </div>
        </header>

        <main class="app-content">
            <?php require __DIR__ . '/../partials/flash.php'; ?>
            <?= $content ?>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!empty($user)): ?>
<script>window.__PORTAL_API_BASE__ = <?= json_encode($baseUrl, JSON_UNESCAPED_UNICODE) ?>;</script>
<script src="<?= $assetBase ?>/assets/js/portal-api.js"></script>
<?php
    $pageScripts = $pageScripts ?? [];
    foreach ($pageScripts as $script):
?>
<script src="<?= $assetBase ?>/assets/js/<?= htmlspecialchars($script) ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
