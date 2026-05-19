<?php
namespace Core;

class View {
    public static function render(string $view, array $data = [], string $layout = 'base'): void {
        extract($data);
        $viewPath   = dirname(__DIR__) . '/app/Views/' . str_replace('.', '/', $view) . '.php';
        $layoutPath = dirname(__DIR__) . '/app/Views/layouts/' . $layout . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View não encontrada: $view");
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        if ($layout && file_exists($layoutPath)) {
            require $layoutPath;
        } else {
            echo $content;
        }
    }

    public static function json(mixed $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
