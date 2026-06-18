<?php $assetBase = dirname($baseUrl); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Đăng nhập') ?> — EduCenter</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $assetBase ?>/assets/css/app.css">
</head>
<body class="auth-body">
<div class="auth-wrapper">
    <div class="auth-hero d-none d-lg-flex">
        <div class="auth-hero-content">
            <div class="auth-logo"><i class="bi bi-mortarboard-fill"></i> EduCenter</div>
            <h2>Hệ thống quản lý trung tâm đào tạo</h2>
            <p>Lịch học, điểm danh, thanh toán và đánh giá — tất cả trong một nền tảng.</p>
            <ul class="auth-features">
                <li><i class="bi bi-check-circle-fill"></i> Quản trị khóa học & lớp</li>
                <li><i class="bi bi-check-circle-fill"></i> Portal giáo viên & học viên</li>
                <li><i class="bi bi-check-circle-fill"></i> Báo cáo & thống kê realtime</li>
            </ul>
        </div>
    </div>
    <div class="auth-panel">
        <div class="auth-card card shadow-lg border-0">
            <div class="card-body p-4 p-md-5">
                <?php require __DIR__ . '/../partials/flash.php'; ?>
                <?= $content ?>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
