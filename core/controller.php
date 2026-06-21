<?php
namespace Core;

class Controller {
    /**
     * Return a JSON response
     */
    protected function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    /**
     * Render a view file
     */
    protected function view($view, $data = []) {
        extract($data);
        $viewFile = __DIR__ . "/../app/view/{$view}.php";
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("View not found: {$view}");
        }
    }

    /**
     * Get JSON body from the request
     */
    protected function getJsonBody() {
        $json = file_get_contents('php://input');
        return json_decode($json, true);
    }

    // ==========================================
    // --- MIDDLEWARES --------------------------
    // ==========================================

    public static function authMiddleware() {
        if (!function_exists('getallheaders')) {
            $headers = [];
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
        } else {
            $headers = getallheaders();
        }

        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        if ($authHeader === '' && !empty($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        }
        if ($authHeader === '' && !empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Token not provided']);
            return false;
        }

        $token = $matches[1];
        // Sử dụng UserModel để decode JWT
        $decoded = \App\Models\UserModel::decodeJWT($token);

        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Invalid or expired token']);
            return false;
        }

        $GLOBALS['user'] = $decoded;
        return true;
    }

    public static function roleMiddleware($roles = []) {
        // Trả về một closure để Router thực thi
        return function() use ($roles) {
            if (!isset($GLOBALS['user'])) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                return false;
            }

            $user = $GLOBALS['user'];
            $userRoles = isset($user['roles']) ? $user['roles'] : [];

            $hasRole = false;
            foreach ($roles as $role) {
                if (in_array($role, $userRoles)) {
                    $hasRole = true;
                    break;
                }
            }

            if (!$hasRole) {
                http_response_code(403);
                echo json_encode(['error' => 'Forbidden: You do not have permission to access this resource']);
                return false;
            }

            return true;
        };
    }
}
