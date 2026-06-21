<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

spl_autoload_register(function ($class) {
    $base_dir = __DIR__ . '/../';
    $file = $base_dir . str_replace('\\', '/', $class) . '.php';
    $file = str_replace($base_dir . 'App/', $base_dir . 'app/', $file);
    $file = str_replace($base_dir . 'Core/', $base_dir . 'core/', $file);
    if (file_exists($file)) {
        require_once $file;
    }
});

use Core\Router;

$router = new Router();
require_once __DIR__ . '/../app/web_routes.php';

$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (preg_match('/web(?:\.php)?\/?(.*)$/', $request_uri, $matches)) {
    $url = '/' . ($matches[1] ?? '');
} else {
    $url = $request_uri;
}

$url = rtrim($url, '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

$router->dispatch($url, $method);
