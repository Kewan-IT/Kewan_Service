<?php
namespace App\Middleware;

class AuthMiddleware {
    public static function check(): void {
        if (empty($_SESSION['usuario_id'])) {
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/auth/login');
            exit;
        }
    }

    public static function requirePerfil(string ...$perfis): void {
        self::check();
        if (!in_array($_SESSION['perfil'] ?? '', $perfis, true)) {
            http_response_code(403);
            require __DIR__ . '/../Views/errors/403.php';
            exit;
        }
    }

    public static function guest(): void {
        if (!empty($_SESSION['usuario_id'])) {
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/dashboard');
            exit;
        }
    }
}
