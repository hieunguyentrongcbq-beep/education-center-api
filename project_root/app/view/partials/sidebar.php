<?php
$menus = [
    'admin' => [
        ['url' => 'admin/dashboard', 'label' => 'Dashboard', 'icon' => 'speedometer2'],
        ['url' => 'admin/notifications', 'label' => 'Thông báo', 'icon' => 'bell', 'badge' => true],
        ['url' => 'admin/courses', 'label' => 'Khóa học', 'icon' => 'book'],
        ['url' => 'admin/classes', 'label' => 'Lớp học', 'icon' => 'collection'],
        ['url' => 'admin/classrooms', 'label' => 'Phòng học', 'icon' => 'door-open'],
        ['url' => 'admin/semesters', 'label' => 'Học kỳ', 'icon' => 'calendar-range'],
        ['url' => 'admin/class-plans', 'label' => 'Kế hoạch mở lớp', 'icon' => 'clipboard2-pulse'],
        ['url' => 'admin/students', 'label' => 'Học viên', 'icon' => 'person-badge'],
        ['url' => 'admin/teachers', 'label' => 'Giáo viên', 'icon' => 'person-workspace'],
        ['url' => 'admin/enrollments', 'label' => 'Ghi danh', 'icon' => 'person-plus'],
        ['url' => 'admin/payments', 'label' => 'Thanh toán', 'icon' => 'credit-card', 'pending_badge' => true],
        ['url' => 'admin/payrolls', 'label' => 'Lương GV', 'icon' => 'cash-stack'],
        ['url' => 'admin/schedules', 'label' => 'Lịch học / Thi', 'icon' => 'calendar-week'],
        ['url' => 'admin/assignments', 'label' => 'Phân công GV', 'icon' => 'clipboard-check'],
        ['url' => 'admin/teaching-schedule', 'label' => 'Lịch dạy GV', 'icon' => 'calendar3'],
        ['url' => 'admin/leave-requests', 'label' => 'Duyệt nghỉ / Học bù', 'icon' => 'envelope-paper'],
        ['url' => 'admin/attendance-report', 'label' => 'Điểm danh / Chấm công', 'icon' => 'clipboard-data'],
        ['url' => 'admin/reports', 'label' => 'Thống kê HV & GV', 'icon' => 'bar-chart-line'],
        ['url' => 'admin/audit-logs', 'label' => 'Nhật ký hệ thống', 'icon' => 'journal-text'],
    ],
    'teacher' => [
        ['url' => 'teacher/dashboard', 'label' => 'Dashboard', 'icon' => 'speedometer2'],
        ['url' => 'teacher/notifications', 'label' => 'Thông báo', 'icon' => 'bell', 'badge' => true],
        ['url' => 'teacher/schedule', 'label' => 'Lịch dạy', 'icon' => 'calendar3'],
        ['url' => 'teacher/attendance', 'label' => 'Điểm danh', 'icon' => 'check2-square'],
        ['url' => 'teacher/leave', 'label' => 'Xin nghỉ / Học bù', 'icon' => 'calendar-x'],
        ['url' => 'teacher/grading', 'label' => 'Chấm bài & Đánh giá', 'icon' => 'file-earmark-pdf'],
    ],
    'student' => [
        ['url' => 'student/dashboard', 'label' => 'Dashboard', 'icon' => 'speedometer2'],
        ['url' => 'student/notifications', 'label' => 'Thông báo', 'icon' => 'bell', 'badge' => true],
        ['url' => 'student/schedule', 'label' => 'Lịch học', 'icon' => 'calendar-event'],
        ['url' => 'student/attendance', 'label' => 'Điểm danh', 'icon' => 'check2-circle'],
        ['url' => 'student/leave', 'label' => 'Xin nghỉ / Học bù', 'icon' => 'calendar-x'],
        ['url' => 'student/submissions', 'label' => 'Nộp bài PDF', 'icon' => 'cloud-upload'],
        ['url' => 'student/results', 'label' => 'Điểm & Nhận xét', 'icon' => 'trophy'],
        ['url' => 'student/compare', 'label' => 'So sánh điểm', 'icon' => 'graph-up-arrow'],
        ['url' => 'student/survey', 'label' => 'Khảo sát GV', 'icon' => 'star'],
    ],
];

$portalLabels = [
    'admin' => 'Quản trị',
    'teacher' => 'Giáo viên',
    'student' => 'Học viên',
];

$portal = $portal ?? 'admin';
$items = $menus[$portal] ?? [];
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);

$unread = (int)($unreadNotifications ?? 0);
$pendingPay = (int)($pendingPaymentsCount ?? 0);

$renderNav = function($items, $currentPath, $url, $unread, $pendingPay) {
    foreach ($items as $item):
        $active = strpos($currentPath, $item['url']) !== false ? 'active' : '';
        $showNotifBadge = !empty($item['badge']) && $unread > 0;
        $showPayBadge = !empty($item['pending_badge']) && $pendingPay > 0;
        $payUrl = !empty($item['pending_badge']) && $pendingPay > 0
            ? $item['url'] . '#pending-payments'
            : $item['url'];
    ?>
        <a href="<?= $url($payUrl) ?>" class="nav-link <?= $active ?>">
            <i class="bi bi-<?= $item['icon'] ?>"></i>
            <span><?= htmlspecialchars($item['label']) ?></span>
            <?php if ($showNotifBadge): ?>
                <span class="nav-badge"><?= $unread > 99 ? '99+' : $unread ?></span>
            <?php endif; ?>
            <?php if ($showPayBadge): ?>
                <span class="nav-badge nav-badge-warning"><?= $pendingPay > 99 ? '99+' : $pendingPay ?></span>
            <?php endif; ?>
        </a>
    <?php endforeach;
};
?>

<aside class="sidebar d-none d-lg-flex flex-column">
    <div class="sidebar-brand">
        <i class="bi bi-mortarboard-fill"></i>
        <div>
            <strong>EduCenter</strong>
            <small><?= htmlspecialchars($portalLabels[$portal] ?? '') ?></small>
        </div>
    </div>
    <nav class="sidebar-nav flex-grow-1">
        <?php $renderNav($items, $currentPath, $url, $unread, $pendingPay); ?>
    </nav>
    <div class="sidebar-footer">
        <div class="sidebar-avatar"><i class="bi bi-person-circle"></i></div>
        <div class="sidebar-user-info">
            <strong><?= htmlspecialchars($user['full_name'] ?? '') ?></strong>
            <span><?= htmlspecialchars($user['role'] ?? '') ?></span>
        </div>
        <a href="<?= $url('logout') ?>" class="btn btn-sm btn-outline-light w-100 mt-2">
            <i class="bi bi-box-arrow-right me-1"></i> Đăng xuất
        </a>
    </div>
</aside>

<div class="offcanvas offcanvas-start sidebar-offcanvas" tabindex="-1" id="sidebarOffcanvas">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title"><i class="bi bi-mortarboard-fill me-2"></i>EduCenter</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0 d-flex flex-column">
        <nav class="sidebar-nav">
            <?php $renderNav($items, $currentPath, $url, $unread, $pendingPay); ?>
        </nav>
        <div class="sidebar-footer m-3">
            <div class="text-white-50 small mb-2"><?= htmlspecialchars($user['full_name'] ?? '') ?></div>
            <a href="<?= $url('logout') ?>" class="btn btn-sm btn-outline-light w-100">Đăng xuất</a>
        </div>
    </div>
</div>
