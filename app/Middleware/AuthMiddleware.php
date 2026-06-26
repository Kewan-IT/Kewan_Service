<?php
namespace App\Middleware;

class AuthMiddleware
{
    // ----------------------------------------------------------------
    // Perfis com acesso TOTAL ao sistema
    // ----------------------------------------------------------------
    const PERFIS_ADMIN = ['admin', 'diretor'];

    // ----------------------------------------------------------------
    // Perfis com acesso RESTRITO (só caixa, vendas, backup)
    // ----------------------------------------------------------------
    const PERFIS_RESTRITOS = ['caixa', 'tecnico', 'farmaceutico'];

    // ----------------------------------------------------------------
    // Verificar se está autenticado
    // ----------------------------------------------------------------
    public static function check(): void
    {
        if (empty($_SESSION['usuario_id'])) {
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/auth/login');
            exit;
        }

        // Se tiver a flag de troca obrigatória, bloquear qualquer outra rota
        if (!empty($_SESSION['trocar_senha_obrigatorio'])) {
            $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
            $base = rtrim(parse_url($_ENV['APP_URL'] ?? '', PHP_URL_PATH) ?? '', '/');
            $rota = str_replace($base, '', $uri);
            // Permitir apenas a rota de troca e o logout
            if (!in_array(rtrim($rota, '/'), ['/auth/trocar-senha', '/auth/logout'], true)) {
                header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/auth/trocar-senha');
                exit;
            }
        }
    }

    // ----------------------------------------------------------------
    // Verificar perfil específico
    // ----------------------------------------------------------------
    public static function requirePerfil(string ...$perfis): void
    {
        self::check();
        if (!in_array($_SESSION['perfil'] ?? '', $perfis, true)) {
            http_response_code(403);
            require __DIR__ . '/../Views/errors/403.php';
            exit;
        }
    }

    // ----------------------------------------------------------------
    // Apenas admins e directores
    // ----------------------------------------------------------------
    public static function apenasAdmin(): void
    {
        self::check();
        if (!in_array($_SESSION['perfil'] ?? '', self::PERFIS_ADMIN, true)) {
            http_response_code(403);
            require __DIR__ . '/../Views/errors/403.php';
            exit;
        }
    }

    // ----------------------------------------------------------------
    // Verificar acesso a uma rota específica
    // Redireciona perfis restritos para /vendas/nova se tentarem
    // aceder a páginas proibidas
    // ----------------------------------------------------------------
    public static function verificarAcesso(string $pagina): void
    {
        self::check();

        $perfil = $_SESSION['perfil'] ?? '';

        // Admins e directores têm acesso a tudo
        if (in_array($perfil, self::PERFIS_ADMIN, true)) {
            return;
        }

        // Páginas permitidas para perfis restritos
        $permitidas = [
            'vendas', 'venda-nova', 'caixa', 'backup',
        ];

        if (!in_array($pagina, $permitidas, true)) {
            // Redirecionar para nova venda em vez de mostrar 403
            $_SESSION['flash_erro'] = 'Não tem permissão para aceder a esta secção.';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/vendas/nova');
            exit;
        }
    }

    // ----------------------------------------------------------------
    // Para rotas AJAX
    // ----------------------------------------------------------------
    public static function checkAjax(): void
    {
        if (empty($_SESSION['usuario_id'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['erro' => 'Sessão expirada']);
            exit;
        }
    }

    // ----------------------------------------------------------------
    // Redirecionar se já estiver autenticado
    // — Admin/Director → Dashboard
    // — Perfis restritos → Nova Venda
    // ----------------------------------------------------------------
    public static function guest(): void
    {
        if (!empty($_SESSION['usuario_id'])) {
            $perfil = $_SESSION['perfil'] ?? '';
            if (in_array($perfil, self::PERFIS_ADMIN, true)) {
                header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/dashboard');
            } else {
                header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/vendas/nova');
            }
            exit;
        }
    }

    // ----------------------------------------------------------------
    // Verificar se o perfil actual tem acesso total
    // ----------------------------------------------------------------
    public static function isAdmin(): bool
    {
        return in_array($_SESSION['perfil'] ?? '', self::PERFIS_ADMIN, true);
    }
}
