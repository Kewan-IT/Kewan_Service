<?php
namespace Core;

class Router {
    private array $routes = [];

    public function get(string $path, array $action, array $options = []): void {
        $this->routes['GET'][$path] = [
            'action' => $action,
            'options' => $options
        ];
    }

    public function post(string $path, array $action, array $options = []): void {
        $this->routes['POST'][$path] = [
            'action' => $action,
            'options' => $options
        ];
    }

    public function dispatch(string $url, string $method): void {
        $url    = rtrim($url, '/') ?: '/';
        $method = strtoupper($method);

        foreach ($this->routes[$method] ?? [] as $pattern => $routeData) {
            $regex = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern);
            if (preg_match('#^' . $regex . '$#', $url, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                // Extrair dados da rota
                $action = $routeData['action'] ?? $routeData;
                $options = $routeData['options'] ?? [];

                // Verificar funcionalidade se especificada
                if (!empty($options['funcionalidade'])) {
                    \App\Middleware\AuthMiddleware::requireFuncionalidade($options['funcionalidade']);
                }

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
