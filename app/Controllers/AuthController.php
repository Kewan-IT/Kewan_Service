<?php
namespace App\Controllers;

use App\Models\Usuario;
use App\Middleware\AuthMiddleware;
use Core\View;

class AuthController
{
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    // ----------------------------------------------------------------
    // GET /auth/login
    // ----------------------------------------------------------------
    public function showLogin(): void
    {
        AuthMiddleware::guest();

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        View::render('auth.login', [
            'titulo' => 'Login',
            'erro'   => $_SESSION['erro_login'] ?? null,
        ], 'auth');

        unset($_SESSION['erro_login']);
    }

    // ----------------------------------------------------------------
    // POST /auth/login
    // ----------------------------------------------------------------
    public function login(): void
    {
        AuthMiddleware::guest();
        $this->verificarCsrf();

        $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '');
        $senha = $_POST['senha'] ?? '';

        if (empty($email) || empty($senha)) {
            $this->redirectLogin('Preencha o email e a senha.');
            return;
        }

        $usuario = $this->usuarioModel->findByEmail($email);

        if (!$usuario) {
            $this->redirectLogin('Credenciais inválidas. Verifique o email e a senha.');
            return;
        }

        if (!$usuario['ativo']) {
            $this->redirectLogin('A sua conta está desactivada. Contacte o administrador.');
            return;
        }

        // Conta bloqueada temporariamente
        if (!empty($usuario['bloqueado_ate']) && strtotime($usuario['bloqueado_ate']) > time()) {
            $minutos = ceil((strtotime($usuario['bloqueado_ate']) - time()) / 60);
            $this->redirectLogin("Conta bloqueada. Tente novamente em {$minutos} minuto(s).");
            return;
        }

        // Verificar senha
        if (!password_verify($senha, $usuario['senha_hash'])) {
            // Incrementar tentativas
            $tentativas = (int)$usuario['tentativas_login'] + 1;
            $bloquear   = null;

            if ($tentativas >= 5) {
                $bloquear   = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                $tentativas = 0;
            }

            $this->usuarioModel->update($usuario['id'], [
                'tentativas_login' => $tentativas,
                'bloqueado_ate'    => $bloquear,
            ]);

            $restantes = max(0, 5 - $tentativas);
            $msg = $bloquear
                ? 'Conta bloqueada por 15 minutos após 5 tentativas falhadas.'
                : "Credenciais inválidas. Restam {$restantes} tentativa(s).";

            $this->redirectLogin($msg);
            return;
        }

        // Login bem sucedido — resetar tentativas
        $this->usuarioModel->update($usuario['id'], [
            'tentativas_login' => 0,
            'bloqueado_ate'    => null,
            'ultimo_login'     => date('Y-m-d H:i:s'),
        ]);

        session_regenerate_id(true);

        $_SESSION['usuario_id']   = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_email']= $usuario['email'];
        $_SESSION['perfil']       = $usuario['perfil'];
        $_SESSION['foto_url']     = $usuario['foto_url'] ?? null;
        $_SESSION['csrf_token']   = bin2hex(random_bytes(32));

        // ── Redireccionamento por perfil ──────────────────────────
        // Admin e Director → Dashboard
        // Caixa, Técnico, Farmacêutico → Nova Venda
        $perfil = $usuario['perfil'];
        if (in_array($perfil, AuthMiddleware::PERFIS_ADMIN, true)) {
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/dashboard');
        } else {
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/vendas/nova');
        }
        exit;
    }

    // ----------------------------------------------------------------
    // GET /auth/logout
    // ----------------------------------------------------------------
    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/auth/login');
        exit;
    }

    // ----------------------------------------------------------------
    // GET /auth/recuperar
    // ----------------------------------------------------------------
    public function showRecuperar(): void
    {
        AuthMiddleware::guest();
        View::render('auth.recuperar', [
            'titulo'  => 'Recuperar Senha',
            'sucesso' => $_SESSION['recuperar_sucesso'] ?? null,
            'erro'    => $_SESSION['recuperar_erro']    ?? null,
        ], 'auth');
        unset($_SESSION['recuperar_sucesso'], $_SESSION['recuperar_erro']);
    }

    // ----------------------------------------------------------------
    // POST /auth/recuperar
    // ----------------------------------------------------------------
    public function recuperar(): void
    {
        AuthMiddleware::guest();
        $this->verificarCsrf();

        $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['recuperar_erro'] = 'Introduza um endereço de email válido.';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/auth/recuperar');
            exit;
        }

        $usuario = $this->usuarioModel->findByEmail($email);

        if ($usuario && $usuario['ativo']) {
            $token  = bin2hex(random_bytes(32));
            $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $this->usuarioModel->update($usuario['id'], [
                'token_reset'     => password_hash($token, PASSWORD_BCRYPT),
                'token_expira_em' => $expira,
            ]);
        }

        $_SESSION['recuperar_sucesso'] = 'Se o email estiver registado, receberá as instruções em breve.';
        header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/auth/recuperar');
        exit;
    }

    // ----------------------------------------------------------------
    private function redirectLogin(string $erro): void
    {
        $_SESSION['erro_login'] = $erro;
        header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/auth/login');
        exit;
    }

    private function verificarCsrf(): void
    {
        $tokenPost   = $_POST['csrf_token']  ?? '';
        $tokenSessao = $_SESSION['csrf_token'] ?? '';

        if (empty($tokenPost) || !hash_equals($tokenSessao, $tokenPost)) {
            http_response_code(419);
            die('Token de segurança inválido. Recarregue a página e tente novamente.');
        }
    }
}
