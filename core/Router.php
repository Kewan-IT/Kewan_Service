<?php
namespace Core;

class Router {
    private array $routes = [];

    public function get(string $path, array $action): void {
        $this->routes['GET'][$path] = $action;
    }

    public function post(string $path, array $action): void {
        $this->routes['POST'][$path] = $action;
    }

    public function dispatch(string $url, string $method): void {
        $url    = rtrim($url, '/') ?: '/';
        $method = strtoupper($method);

        foreach ($this->routes[$method] ?? [] as $pattern => $action) {
            $regex = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern);
            if (preg_match('#^' . $regex . '$#', $url, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                [$controllerName, $methodName] = $action;
                $class = "App\\Controllers\\$controllerName";
                (new $class)->{$methodName}(...array_values($params));
                return;
            }
        }

        http_response_code(404);
        require __DIR__ . '/../app/Views/errors/404.php';
    }
}
