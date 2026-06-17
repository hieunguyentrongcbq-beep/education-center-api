<?php
namespace App\Middleware;

class SessionAuth {
    public static function handle(): bool {
        if (empty($_SESSION['user'])) {
            $_SESSION['flash']['error'] = 'Vui lòng đăng nhập';
            header('Location: ' . self::baseUrl() . '/login');
            exit;
        }
        return true;
    }

    public static function roleDashboardPath(?string $role): string {
        $key = strtolower(trim((string)$role));
        $map = [
            'admin'   => 'admin/dashboard',
            'teacher' => 'teacher/dashboard',
            'student' => 'student/dashboard',
        ];
        return $map[$key] ?? 'login';
    }

    public static function guest(): bool {
        if (!empty($_SESSION['user'])) {
            header('Location: ' . self::baseUrl() . '/' . self::roleDashboardPath($_SESSION['user']['role'] ?? ''));
            exit;
        }
        return true;
    }

    public static function requireRole(array $roles): callable {
        return function () use ($roles) {
            self::handle();
            $userRole = $_SESSION['user']['role'] ?? '';
            if (!in_array($userRole, $roles, true)) {
                http_response_code(403);
                $_SESSION['flash']['error'] = 'Bạn không có quyền truy cập trang này';
                header('Location: ' . self::baseUrl() . '/' . self::roleDashboardPath($userRole));
                exit;
            }
            return true;
        };
    }

    private static function baseUrl(): string {
        $script = $_SERVER['SCRIPT_NAME'] ?? '/web.php';
        return rtrim(dirname($script), '/\\') . '/web.php';
    }
}
