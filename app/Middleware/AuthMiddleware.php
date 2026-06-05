<?php
namespace App\Middleware;

class AuthMiddleware {
    /**
     * Mapa de permissões por perfil
     * Define quais funcionalidades cada perfil pode acessar
     */
    private static array $permissoes = [
        'admin' => [
            'dashboard',
            'funcionarios',
            'produtos',
            'clientes',
            'vendas',
            'compras',
            'fornecedores',
            'caixa',
            'relatorios',
            'configuracoes',
            'backup'
        ],
        'farmaceutico' => [
            'caixa',
            'vendas',
            'backup'
        ],
        'caixa' => [
            'caixa',
            'vendas',
            'backup'
        ],
        'tecnico' => [
            'caixa',
            'vendas',
            'backup'
        ]
    ];

    /**
     * Verifica se o usuário está autenticado
     */
    public static function check(): void {
        if (empty($_SESSION['usuario_id'])) {
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/auth/login');
            exit;
        }
    }

    /**
     * Verifica se o usuário tem um dos perfis especificados
     */
    public static function requirePerfil(string ...$perfis): void {
        self::check();
        if (!in_array($_SESSION['perfil'] ?? '', $perfis, true)) {
            http_response_code(403);
            require __DIR__ . '/../Views/errors/403.php';
            exit;
        }
    }

    /**
     * Verifica se o usuário tem acesso a uma funcionalidade específica
     * 
     * Funcionalidades disponíveis:
     * - 'dashboard': Dashboard
     * - 'funcionarios': Gestão de Funcionários
     * - 'produtos': Gestão de Produtos
     * - 'clientes': Gestão de Clientes
     * - 'vendas': Registro e Consulta de Vendas
     * - 'compras': Gestão de Compras
     * - 'fornecedores': Gestão de Fornecedores
     * - 'caixa': Movimento do Caixa
     * - 'relatorios': Relatórios
     * - 'configuracoes': Configurações do Sistema
     * - 'backup': Backup Manual
     */
    public static function requireFuncionalidade(string ...$funcionalidades): void {
        self::check();

        $perfil = $_SESSION['perfil'] ?? '';
        $permissoesUsuario = self::$permissoes[$perfil] ?? [];

        // Verifica se o usuário tem acesso a pelo menos uma das funcionalidades
        $temAcesso = false;
        foreach ($funcionalidades as $func) {
            if (in_array($func, $permissoesUsuario, true)) {
                $temAcesso = true;
                break;
            }
        }

        if (!$temAcesso) {
            http_response_code(403);
            require __DIR__ . '/../Views/errors/403.php';
            exit;
        }
    }

    /**
     * Verifica acesso via AJAX/API
     */
    public static function checkAjax(): void {
        if (empty($_SESSION['usuario_id'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['erro' => 'Sessão expirada']);
            exit;
        }
    }

    /**
     * Verifica acesso a funcionalidade via AJAX/API
     */
    public static function checkAjaxFuncionalidade(string ...$funcionalidades): void {
        self::checkAjax();

        $perfil = $_SESSION['perfil'] ?? '';
        $permissoesUsuario = self::$permissoes[$perfil] ?? [];

        $temAcesso = false;
        foreach ($funcionalidades as $func) {
            if (in_array($func, $permissoesUsuario, true)) {
                $temAcesso = true;
                break;
            }
        }

        if (!$temAcesso) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['erro' => 'Acesso negado']);
            exit;
        }
    }

    /**
     * Redireciona se o usuário já está autenticado
     */
    public static function guest(): void {
        if (!empty($_SESSION['usuario_id'])) {
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/dashboard');
            exit;
        }
    }

    /**
     * Retorna o mapa de permissões (para fins de controle em views)
     */
    public static function getPermissoes(): array {
        return self::$permissoes;
    }

    /**
     * Verifica se o usuário atual tem acesso a uma funcionalidade
     */
    public static function temAcesso(string $funcionalidade): bool {
        $perfil = $_SESSION['perfil'] ?? '';
        $permissoesUsuario = self::$permissoes[$perfil] ?? [];
        return in_array($funcionalidade, $permissoesUsuario, true);
    }
}
