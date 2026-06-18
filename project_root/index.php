<?php
/**
 * Entry khi truy cập thư mục project_root/
 * → Đã đăng nhập: dashboard theo role
 * → Chưa đăng nhập: trang login portal
 */
session_start();

$base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$portal = $base . '/public/web.php';

if (!empty($_SESSION['user']['role'])) {
    $role = strtolower($_SESSION['user']['role']);
    $paths = [
        'admin'   => '/admin/dashboard',
        'teacher' => '/teacher/dashboard',
        'student' => '/student/dashboard',
    ];
    $path = $paths[$role] ?? '/login';
    header('Location: ' . $portal . $path);
    exit;
}

header('Location: ' . $portal . '/login');
exit;
