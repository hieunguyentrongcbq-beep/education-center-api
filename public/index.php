<?php
// Handle CORS if needed
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Display errors for development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Autoloader implementation (PSR-4 style)
spl_autoload_register(function ($class) {
    $base_dir = __DIR__ . '/../';
    
    // Convert namespace separators to directory separators
    $file = $base_dir . str_replace('\\', '/', $class) . '.php';
    
    // Map App to app/ and Core to core/
    $file = str_replace($base_dir . 'App/', $base_dir . 'app/', $file);
    $file = str_replace($base_dir . 'Core/', $base_dir . 'core/', $file);

    if (file_exists($file)) {
        require_once $file;
    }
});

use Core\Router;

$router = new Router();

// ==========================================
// ROUTES
// ==========================================

$router->get('/ping', function() {
    header('Content-Type: application/json');
    echo json_encode([
        "status" => "ok", 
        "message" => "Education Center API is running", 
        "timestamp" => date('Y-m-d H:i:s')
    ]);
});

// Load application routes
require_once __DIR__ . '/../app/routes.php';


// ==========================================
// DISPATCH
// ==========================================

$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Tìm phần đường dẫn thực sự nằm sau chữ "public" hoặc "public/index.php"
if (preg_match('/public(?:\/index\.php)?\/?(.*)$/', $request_uri, $matches)) {
    $url = '/' . $matches[1];
} else {
    $url = $request_uri;
}

if ($url === '/' || $url === '') {
    $url = '/';
}

$method = $_SERVER['REQUEST_METHOD'];
$router->dispatch($url, $method);



