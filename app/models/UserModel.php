<?php
namespace App\Models;

use Core\Database;

class UserModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ==========================================
    // --- REPOSITORY LOGIC (Database Access) ---
    // ==========================================
    
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public function getUserRoles($userId) {
        $stmt = $this->db->prepare("
            SELECT r.role_name 
            FROM roles r 
            JOIN user_roles ur ON r.id = ur.role_id 
            WHERE ur.user_id = :user_id
        ");
        $stmt->execute(['user_id' => $userId]);
        $roles = [];
        while ($row = $stmt->fetch()) {
            $roles[] = $row['role_name'];
        }
        return $roles;
    }

    // ==========================================
    // --- SERVICE LOGIC (Business Rules) -------
    // ==========================================

    private static $rolePriority = [
        'ADMIN' => 1, 'SUPER_ADMIN' => 1, 'CENTER_MANAGER' => 1, 'ACCOUNTANT' => 1, 'ACADEMIC_STAFF' => 1,
        'TEACHER' => 2, 'STUDENT' => 3,
    ];

    public function getPrimaryRole(int $userId): string {
        $roles = $this->getUserRoles($userId);
        $best = 'STUDENT';
        $bestPri = 99;
        foreach ($roles as $r) {
            $mapped = $this->mapRole($r);
            $pri = self::$rolePriority[$mapped] ?? self::$rolePriority[$r] ?? 50;
            if ($pri < $bestPri) {
                $bestPri = $pri;
                $best = $mapped;
            }
        }
        return $best;
    }

    private function mapRole(string $role): string {
        $map = [
            'SUPER_ADMIN' => 'ADMIN', 'CENTER_MANAGER' => 'ADMIN',
            'ACCOUNTANT' => 'ADMIN', 'ACADEMIC_STAFF' => 'ADMIN',
        ];
        return $map[$role] ?? $role;
    }

    public function loginWeb(string $email, string $password): array {
        $user = $this->findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ['error' => 'Email hoặc mật khẩu không đúng'];
        }
        if ($user['status'] !== 'ACTIVE') {
            return ['error' => 'Tài khoản đã bị khóa'];
        }
        $role = $this->getPrimaryRole((int)$user['id']);
        return [
            'id' => (int)$user['id'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'role' => $role,
        ];
    }

    public function login($email, $password) {
        $user = $this->findByEmail($email);

        if (!$user) {
            return ['error' => 'Invalid email or password'];
        }

        // Kiểm tra mật khẩu (giả định dùng bcrypt)
        if (!password_verify($password, $user['password_hash'])) {
            return ['error' => 'Invalid email or password'];
        }

        if ($user['status'] !== 'ACTIVE') {
            return ['error' => 'Account is inactive'];
        }

        $roles = $this->getUserRoles($user['id']);

        $payload = [
            'id' => $user['id'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'roles' => $roles
        ];

        $token = self::encodeJWT($payload);

        return [
            'token' => $token,
            'user' => $payload
        ];
    }

    // ==========================================
    // --- UTILS LOGIC (JWT Generation) ---------
    // ==========================================
    
    private static $jwtSecret = 'super_secret_key_education_center_123';

    public static function encodeJWT($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload['exp'] = time() + (60 * 60 * 24); // Hết hạn sau 1 ngày
        $payloadJson = json_encode($payload);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payloadJson));

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$jwtSecret, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function decodeJWT($jwt) {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) return false;

        list($header, $payload, $signature) = $parts;

        $validSignature = hash_hmac('sha256', $header . "." . $payload, self::$jwtSecret, true);
        $validBase64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($validSignature));

        if ($signature === $validBase64UrlSignature) {
            $payloadData = json_decode(base64_decode($payload), true);
            if (isset($payloadData['exp']) && $payloadData['exp'] < time()) {
                return false;
            }
            return $payloadData;
        }
        return false;
    }
}
