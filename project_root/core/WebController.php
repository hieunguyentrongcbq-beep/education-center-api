<?php
namespace Core;

class WebController {
    protected function baseUrl(): string {
        $script = $_SERVER['SCRIPT_NAME'] ?? '/web.php';
        return rtrim(dirname($script), '/\\') . '/web.php';
    }

    protected function url(string $path = ''): string {
        $base = $this->baseUrl();
        $path = ltrim($path, '/');
        return $path ? $base . '/' . $path : $base;
    }

    protected function redirect(string $path): void {
        header('Location: ' . $this->url($path));
        exit;
    }

    protected function flash(string $type, string $message): void {
        $_SESSION['flash'][$type] = $message;
    }

    protected function getFlash(): array {
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flash;
    }

    protected function old(string $key, $default = '') {
        return $_SESSION['old'][$key] ?? $default;
    }

    protected function setOld(array $data): void {
        $_SESSION['old'] = $data;
    }

    protected function clearOld(): void {
        unset($_SESSION['old']);
    }

    protected function validate(array $rules, array $input): array {
        $errors = [];
        foreach ($rules as $field => $ruleStr) {
            $value = $input[$field] ?? '';
            $rulesList = explode('|', $ruleStr);
            foreach ($rulesList as $rule) {
                if ($rule === 'required' && ($value === '' || $value === null)) {
                    $errors[$field] = "Trường $field là bắt buộc";
                    break;
                }
                if (strpos($rule, 'min:') === 0 && strlen((string)$value) < (int)substr($rule, 4)) {
                    $errors[$field] = "Trường $field quá ngắn";
                    break;
                }
                if ($rule === 'email' && $value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = 'Email không hợp lệ';
                    break;
                }
                if ($rule === 'numeric' && $value !== '' && !is_numeric($value)) {
                    $errors[$field] = "Trường $field phải là số";
                    break;
                }
            }
        }
        return $errors;
    }

    protected function render(string $view, array $data = [], string $layout = 'main'): void {
        $data['flash'] = $this->getFlash();
        $data['user'] = $_SESSION['user'] ?? null;
        if (!empty($_SESSION['user']['id']) && !isset($data['unreadNotifications'])) {
            $data['unreadNotifications'] = (new \App\Models\instructormodel())->countUnreadNotifications($_SESSION['user']['id']);
        }
        if (!isset($data['pendingPaymentsCount']) && strtolower($_SESSION['user']['role'] ?? '') === 'admin') {
            $data['pendingPaymentsCount'] = (new \App\Models\PaymentService())->countPending();
        }
        $data['baseUrl'] = $this->baseUrl();
        $self = $this;
        $data['url'] = function($path = '') use ($self) { return $self->url($path); };
        $data['old'] = function($key, $default = '') use ($self) { return $self->old($key, $default); };

        extract($data);
        ob_start();
        $viewFile = __DIR__ . "/../app/view/{$view}.php";
        if (!file_exists($viewFile)) {
            die("View not found: {$view}");
        }
        require $viewFile;
        $content = ob_get_clean();

        $layoutFile = __DIR__ . "/../app/view/layouts/{$layout}.php";
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
        exit;
    }

    protected function requirePost(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die('Method not allowed');
        }
    }

    protected function requireMethod(array $methods): void {
        if (!in_array($_SERVER['REQUEST_METHOD'], $methods, true)) {
            $this->json(['error' => 'Method not allowed'], 405);
        }
    }

    protected function json($data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** @param array<int, array<int, scalar|null>> $rows */
    protected function sendCsv(string $filename, array $headers, array $rows): void {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($out, $headers);
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }
        fclose($out);
        exit;
    }

    protected function inputJson(): array {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        return array_merge($_GET, $_POST);
    }

    protected function input(): array {
        return array_merge($_GET, $_POST);
    }

    protected static function dayName(int $dow): string {
        $days = ['Chủ nhật', 'Thứ hai', 'Thứ ba', 'Thứ tư', 'Thứ năm', 'Thứ sáu', 'Thứ bảy'];
        return $days[$dow] ?? (string)$dow;
    }

    protected static function formatDate(?string $date): string {
        if (!$date) return '-';
        $ts = strtotime($date);
        return $ts ? date('d/m/Y', $ts) : $date;
    }

    protected static function formatMoney($amount): string {
        return number_format((float)$amount, 0, ',', '.') . ' đ';
    }

    protected function audit(string $action, string $entityName, $entityId = null): void {
        \App\Models\AuditLog::write(
            $_SESSION['user']['id'] ?? null,
            $action,
            $entityName,
            $entityId !== null && $entityId !== '' ? (int)$entityId : null
        );
    }

    protected function uploadPdf($fieldName, $subdir = 'submissions'): ?string {
        if (empty($_FILES[$fieldName]['tmp_name'])) return null;
        $file = $_FILES[$fieldName];
        if ($file['error'] !== UPLOAD_ERR_OK) return null;
        if ($file['size'] > 5 * 1024 * 1024) return null;
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') return null;

        $dir = __DIR__ . '/../public/uploads/' . $subdir;
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $name = uniqid('pdf_', true) . '.pdf';
        $dest = $dir . '/' . $name;
        if (!move_uploaded_file($file['tmp_name'], $dest)) return null;
        return 'uploads/' . $subdir . '/' . $name;
    }

    /** Gửi file PDF đã upload (chống path traversal). */
    protected function sendUploadedPdf(string $relativePath, string $downloadName): void {
        $relativePath = str_replace('\\', '/', $relativePath);
        if (strpos($relativePath, '..') !== false || !preg_match('#^uploads/[a-zA-Z0-9_./-]+\.pdf$#', $relativePath)) {
            http_response_code(404);
            exit('File not found');
        }

        $uploadsRoot = realpath(__DIR__ . '/../public/uploads');
        $fullPath = realpath(__DIR__ . '/../public/' . $relativePath);
        if (!$uploadsRoot || !$fullPath || strpos($fullPath, $uploadsRoot) !== 0 || !is_file($fullPath)) {
            http_response_code(404);
            exit('File not found');
        }

        $safeName = preg_replace('/[^\w.\-]+/u', '_', $downloadName) ?: 'submission.pdf';
        if (substr(strtolower($safeName), -4) !== '.pdf') {
            $safeName .= '.pdf';
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $safeName . '"');
        header('Content-Length: ' . (string)filesize($fullPath));
        header('Cache-Control: private, no-cache');
        readfile($fullPath);
        exit;
    }
}
