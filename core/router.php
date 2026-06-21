<?php
namespace Core;

class Router {
    private $routes = [];

    public function add($method, $path, $action, $middlewares = []) {
        // Convert path with params like {id} to regex
        $pathRegex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[a-zA-Z0-9_-]+)', $path);
        $pathRegex = "#^" . $pathRegex . "$#";
        
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $pathRegex,
            'action' => $action,
            'middlewares' => $middlewares
        ];
    }

    public function get($path, $action, $middlewares = []) { $this->add('GET', $path, $action, $middlewares); }
    public function post($path, $action, $middlewares = []) { $this->add('POST', $path, $action, $middlewares); }
    public function put($path, $action, $middlewares = []) { $this->add('PUT', $path, $action, $middlewares); }
    public function delete($path, $action, $middlewares = []) { $this->add('DELETE', $path, $action, $middlewares); }

    public function dispatch($url, $method) {
        // Remove query string from url for matching
        $url = parse_url($url, PHP_URL_PATH);
        
        // Remove trailing slash if present (except root)
        if ($url !== '/') {
            $url = rtrim($url, '/');
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['path'], $url, $matches)) {
                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                    }
                }

                // Execute middlewares (if any)
                foreach ($route['middlewares'] as $middleware) {
                    if (is_callable($middleware)) {
                        // Handles: [ClassName, 'method'], Closure, 'function_name'
                        if (!call_user_func($middleware)) {
                            return;
                        }
                    } elseif (is_string($middleware)) {
                        // Handles: class name string — instantiate and call handle()
                        $middlewareInstance = new $middleware();
                        if (!$middlewareInstance->handle()) {
                            return;
                        }
                    }
                }

                // Execute controller action
                if (is_callable($route['action'])) {
                    call_user_func_array($route['action'], [$params]);
                } else if (is_array($route['action'])) {
                    $controllerName = $route['action'][0];
                    $methodName = $route['action'][1];
                    $controller = new $controllerName();
                    $controller->$methodName($params);
                } else if (is_string($route['action'])) {
                    list($controllerName, $methodName) = explode('@', $route['action']);
                    $controllerName = "App\\Controllers\\" . $controllerName;
                    $controller = new $controllerName();
                    $controller->$methodName($params);
                }
                return;
            }
        }

        // 404 Not Found
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Route not found', 'path' => $url]);
    }
}
