<?php
/**
 * Smoke test — chạy sau migrate.php (K10)
 * Usage: php scripts/smoke_test.php [base_url]
 */
$base = rtrim($argv[1] ?? 'http://localhost/educationcenterapi/project_root/public', '/');
$apiBase = $base . '/index.php';
$webBase = $base . '/web.php';

$passed = 0;
$failed = 0;

function check(string $label, bool $ok, string $detail = ''): void {
    global $passed, $failed;
    if ($ok) {
        $passed++;
        echo "  [OK]   $label" . ($detail ? " — $detail" : '') . "\n";
    } else {
        $failed++;
        echo "  [FAIL] $label" . ($detail ? " — $detail" : '') . "\n";
    }
}

function httpRequest(string $method, string $url, ?string $body = null, array $headers = [], ?string $cookieFile = null): array {
    $ch = curl_init($url);
    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => $headers,
    ];
    if ($body !== null) {
        $opts[CURLOPT_POSTFIELDS] = $body;
    }
    if ($cookieFile) {
        $opts[CURLOPT_COOKIEJAR] = $cookieFile;
        $opts[CURLOPT_COOKIEFILE] = $cookieFile;
    }
    curl_setopt_array($ch, $opts);
    $response = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    return ['code' => $code, 'body' => $response, 'error' => $err];
}

echo "=== EduCenter Smoke Test ===\n";
echo "API:  $apiBase\n";
echo "Web:  $webBase\n\n";

// --- API health ---
$r = httpRequest('GET', $apiBase . '/');
$apiUp = $r['code'] === 200 && strpos((string)$r['body'], 'Welcome') !== false;
check('API root', $apiUp, 'HTTP ' . $r['code']);

// --- API login admin ---
$loginBody = json_encode(['email' => 'admin@edu.vn', 'password' => 'admin123']);
$r = httpRequest('POST', $apiBase . '/auth/login', $loginBody, ['Content-Type: application/json']);
$loginData = json_decode((string)$r['body'], true);
$token = $loginData['data']['token'] ?? $loginData['token'] ?? $loginData['access_token'] ?? null;
check('API login admin', $r['code'] === 200 && !empty($token), $token ? 'JWT ok' : substr((string)$r['body'], 0, 120));

$authHeaders = $token ? ['Authorization: Bearer ' . $token, 'Content-Type: application/json'] : [];

// --- API CRUD read ---
foreach (['/courses', '/classes', '/students', '/teachers'] as $path) {
    $r = httpRequest('GET', $apiBase . $path, null, $authHeaders);
    $data = json_decode((string)$r['body'], true);
    $hasData = $r['code'] === 200 && isset($data['data']) && is_array($data['data']);
    check('API GET ' . $path, $hasData, $hasData ? count($data['data']) . ' rows' : 'HTTP ' . $r['code']);
}

// --- Teacher schedule API ---
$r = httpRequest('GET', $apiBase . '/teachers/1/schedule?week=' . date('Y-m-d'), null, $authHeaders);
$sched = json_decode((string)$r['body'], true);
check('API GET /teachers/1/schedule', $r['code'] === 200 && isset($sched['data']), isset($sched['week_start']) ? 'week ' . $sched['week_start'] : 'HTTP ' . $r['code']);

// --- Student schedule API ---
$r = httpRequest('GET', $apiBase . '/students/1/schedule?week=' . date('Y-m-d'), null, $authHeaders);
$ssched = json_decode((string)$r['body'], true);
check('API GET /students/1/schedule', $r['code'] === 200 && isset($ssched['data']), isset($ssched['student']['student_code']) ? $ssched['student']['student_code'] : 'HTTP ' . $r['code']);

// --- Portal session login (admin) ---
$cookieFile = sys_get_temp_dir() . '/educenter_smoke_' . getmypid() . '.txt';
@unlink($cookieFile);
$r = httpRequest('GET', $webBase . '/login', null, [], $cookieFile);
check('Portal GET /login', $r['code'] === 200 && strpos((string)$r['body'], 'Đăng nhập') !== false, 'HTTP ' . $r['code']);

$post = http_build_query(['email' => 'admin@edu.vn', 'password' => 'admin123']);
$r = httpRequest('POST', $webBase . '/login', $post, ['Content-Type: application/x-www-form-urlencoded'], $cookieFile);
$r2 = httpRequest('GET', $webBase . '/admin/dashboard', null, [], $cookieFile);
check('Portal admin login + dashboard', $r2['code'] === 200 && strpos((string)$r2['body'], 'Dashboard') !== false, 'HTTP ' . $r2['code']);

// --- Portal teacher ---
@unlink($cookieFile);
$r = httpRequest('POST', $webBase . '/login', http_build_query(['email' => 'tuan.gv@edu.vn', 'password' => 'teacher123']), ['Content-Type: application/x-www-form-urlencoded'], $cookieFile);
$r2 = httpRequest('GET', $webBase . '/teacher/schedule', null, [], $cookieFile);
check('Portal teacher schedule', $r2['code'] === 200 && strpos((string)$r2['body'], 'Lịch dạy') !== false, 'HTTP ' . $r2['code']);

// --- Portal student ---
@unlink($cookieFile);
$r = httpRequest('POST', $webBase . '/login', http_build_query(['email' => 'an.hv@edu.vn', 'password' => 'student123']), ['Content-Type: application/x-www-form-urlencoded'], $cookieFile);
$r2 = httpRequest('GET', $webBase . '/student/schedule', null, [], $cookieFile);
$studentBody = (string)$r2['body'];
check(
    'Portal student schedule',
    $r2['code'] === 200 && (strpos($studentBody, 'Lịch học') !== false || strpos($studentBody, 'portal-student') !== false),
    'HTTP ' . $r2['code']
);

// --- Portal API (session) ---
@unlink($cookieFile);
httpRequest('POST', $webBase . '/login', http_build_query(['email' => 'admin@edu.vn', 'password' => 'admin123']), ['Content-Type: application/x-www-form-urlencoded'], $cookieFile);
$r = httpRequest('GET', $webBase . '/api/portal/classrooms', null, ['Accept: application/json'], $cookieFile);
$rooms = json_decode((string)$r['body'], true);
check('Portal AJAX /api/portal/classrooms', $r['code'] === 200 && isset($rooms['data']), is_array($rooms['data'] ?? null) ? count($rooms['data']) . ' rooms' : 'HTTP ' . $r['code']);

@unlink($cookieFile);

echo "\n========================================\n";
echo "Passed: $passed | Failed: $failed\n";
echo $failed === 0 ? "SMOKE TEST PASSED\n" : "SMOKE TEST HAD FAILURES\n";
exit($failed > 0 ? 1 : 0);
