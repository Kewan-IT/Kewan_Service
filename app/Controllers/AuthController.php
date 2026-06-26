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

        // ── Forçar troca de senha no primeiro login ──
        if (!empty($usuario['trocar_senha_proximo'])) {
            $_SESSION['trocar_senha_obrigatorio'] = true;
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/auth/trocar-senha');
            exit;
        }

        // ── Redireccionamento normal por perfil ──
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
    // GET /auth/trocar-senha — obrigatório no primeiro login
    // ----------------------------------------------------------------
    public function trocarSenhaForm(): void
    {
        if (empty($_SESSION['usuario_id']) || empty($_SESSION['trocar_senha_obrigatorio'])) {
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/auth/login');
            exit;
        }

        View::render('auth.trocar_senha_obrigatorio', [
            'titulo' => 'Definir nova senha',
            'nome'   => $_SESSION['usuario_nome'] ?? '',
            'erro'   => $_SESSION['trocar_senha_erro'] ?? null,
        ], 'auth');
        unset($_SESSION['trocar_senha_erro']);
    }

    // ----------------------------------------------------------------
    // POST /auth/trocar-senha
    // ----------------------------------------------------------------
    public function trocarSenha(): void
    {
        if (empty($_SESSION['usuario_id']) || empty($_SESSION['trocar_senha_obrigatorio'])) {
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/auth/login');
            exit;
        }

        $this->verificarCsrf();

        $atual     = $_POST['senha_atual']     ?? '';
        $nova      = $_POST['nova_senha']      ?? '';
        $confirmar = $_POST['confirmar_senha'] ?? '';

        $usuario = $this->usuarioModel->findById((int)$_SESSION['usuario_id']);

        if (!password_verify($atual, $usuario['senha_hash'])) {
            $_SESSION['trocar_senha_erro'] = 'A senha actual está incorrecta.';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/auth/trocar-senha');
            exit;
        }

        if (strlen($nova) < 8) {
            $_SESSION['trocar_senha_erro'] = 'A nova senha deve ter pelo menos 8 caracteres.';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/auth/trocar-senha');
            exit;
        }

        if ($nova !== $confirmar) {
            $_SESSION['trocar_senha_erro'] = 'As senhas não coincidem.';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/auth/trocar-senha');
            exit;
        }

        if (password_verify($nova, $usuario['senha_hash'])) {
            $_SESSION['trocar_senha_erro'] = 'A nova senha não pode ser igual à senha actual (temporária).';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/auth/trocar-senha');
            exit;
        }

        $this->usuarioModel->update((int)$_SESSION['usuario_id'], [
            'senha_hash' => password_hash($nova, PASSWORD_BCRYPT, ['cost' => 12]),
        ]);

        try {
            \Core\Database::getInstance()->prepare(
                "UPDATE usuarios SET trocar_senha_proximo = 0 WHERE id = :id"
            )->execute(['id' => (int)$_SESSION['usuario_id']]);
        } catch (\Throwable $e) {}

        unset($_SESSION['trocar_senha_obrigatorio']);

        $perfil = $_SESSION['perfil'] ?? '';
        if (in_array($perfil, \App\Middleware\AuthMiddleware::PERFIS_ADMIN, true)) {
            $_SESSION['flash_sucesso'] = 'Senha definida com sucesso! Bem-vindo(a) ao sistema.';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/dashboard');
        } else {
            $_SESSION['flash_sucesso'] = 'Senha definida com sucesso!';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/vendas/nova');
        }
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
    // ----------------------------------------------------------------
    // GET /auth/recuperar
    // ----------------------------------------------------------------
    public function recuperarForm(): void
    {
        AuthMiddleware::guest();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        View::render('auth.recuperar', [
            'titulo'  => 'Recuperar senha',
            'sucesso' => $_SESSION['recuperar_sucesso'] ?? null,
            'erro'    => $_SESSION['recuperar_erro']    ?? null,
            'senha_temp' => $_SESSION['senha_temp'] ?? null,
            'smtp_ok'    => (new \App\Services\MailService())->smtpConfigurado(),
        ]);
        unset($_SESSION['recuperar_sucesso'], $_SESSION['recuperar_erro'], $_SESSION['senha_temp']);
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
            $this->garantirColunaTrocarSenha(); // garante colunas antes de qualquer UPDATE
            $mailer = new \App\Services\MailService();

            if ($mailer->smtpConfigurado()) {
                // ── Fluxo normal: enviar link de reset por email ──
                $token  = bin2hex(random_bytes(32));
                $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $this->usuarioModel->update($usuario['id'], [
                    'token_reset'     => password_hash($token, PASSWORD_BCRYPT),
                    'token_expira_em' => $expira,
                ]);

                $link = ($_ENV['APP_URL'] ?? '') . '/auth/reset?token=' . urlencode($token) . '&email=' . urlencode($email);

                try {
                    $mailer->enviarRecuperacaoSenha($email, $usuario['nome'], $link);
                    $_SESSION['recuperar_sucesso'] = 'Email enviado! Verifique a sua caixa de entrada (e a pasta de spam). O link é válido por 1 hora.';
                } catch (\Throwable $e) {
                    error_log('Erro ao enviar email de recuperação: ' . $e->getMessage());
                    $_SESSION['recuperar_erro'] = 'Não foi possível enviar o email. Contacte o administrador do sistema.';
                }
            } else {
                // ── Fallback: SMTP não configurado — gerar senha temporária ──
                $novaSenha = $this->gerarSenhaTemporaria();
                $this->usuarioModel->update($usuario['id'], [
                    'senha_hash'          => password_hash($novaSenha, PASSWORD_BCRYPT),
                ]);

                // Garantir que a coluna existe antes de tentar escrever nela
                $this->garantirColunaTrocarSenha();
                try {
                    \Core\Database::getInstance()->prepare(
                        "UPDATE usuarios SET trocar_senha_proximo = 1 WHERE id = :id"
                    )->execute(['id' => $usuario['id']]);
                } catch (\Throwable $e) { /* coluna ainda não existe — ignora */ }

                $_SESSION['senha_temp'] = $novaSenha;
                $_SESSION['recuperar_sucesso'] = 'Senha temporária gerada. Copie-a abaixo, faça login e defina uma nova senha imediatamente.';
            }
        } else {
            // Mensagem genérica — não revelar se o email existe
            $_SESSION['recuperar_sucesso'] = 'Se o email estiver registado, receberá as instruções em breve.';
        }

        header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/auth/recuperar');
        exit;
    }

    // ----------------------------------------------------------------
    // GET /auth/reset — formulário para definir nova senha com token
    // ----------------------------------------------------------------
    public function resetForm(): void
    {
        AuthMiddleware::guest();

        $token = trim($_GET['token'] ?? '');
        $email = trim($_GET['email'] ?? '');

        if (!$token || !$email) {
            $_SESSION['recuperar_erro'] = 'Link inválido ou incompleto.';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/auth/recuperar');
            exit;
        }

        $usuario = $this->usuarioModel->findByEmail($email);

        if (!$usuario || !$usuario['ativo']
            || empty($usuario['token_reset'])
            || empty($usuario['token_expira_em'])
            || strtotime($usuario['token_expira_em']) < time()
            || !password_verify($token, $usuario['token_reset'])
        ) {
            $_SESSION['recuperar_erro'] = 'Este link de recuperação é inválido ou expirou. Solicite um novo.';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/auth/recuperar');
            exit;
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        View::render('auth.reset_senha', [
            'titulo' => 'Redefinir senha',
            'token'  => $token,
            'email'  => $email,
            'nome'   => $usuario['nome'],
            'erro'   => $_SESSION['reset_erro'] ?? null,
        ]);
        unset($_SESSION['reset_erro']);
    }

    // ----------------------------------------------------------------
    // POST /auth/reset — gravar nova senha
    // ----------------------------------------------------------------
    public function reset(): void
    {
        AuthMiddleware::guest();
        $this->verificarCsrf();

        $token     = trim($_POST['token'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $senha     = $_POST['nova_senha']     ?? '';
        $confirmar = $_POST['confirmar_senha'] ?? '';

        $usuario = $this->usuarioModel->findByEmail($email);

        if (!$usuario || !$usuario['ativo']
            || empty($usuario['token_reset'])
            || empty($usuario['token_expira_em'])
            || strtotime($usuario['token_expira_em']) < time()
            || !password_verify($token, $usuario['token_reset'])
        ) {
            $_SESSION['recuperar_erro'] = 'Link inválido ou expirado. Solicite um novo.';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/auth/recuperar');
            exit;
        }

        if (strlen($senha) < 8) {
            $_SESSION['reset_erro'] = 'A nova senha deve ter pelo menos 8 caracteres.';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/auth/reset?token=' . urlencode($token) . '&email=' . urlencode($email));
            exit;
        }

        if ($senha !== $confirmar) {
            $_SESSION['reset_erro'] = 'As senhas não coincidem.';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/auth/reset?token=' . urlencode($token) . '&email=' . urlencode($email));
            exit;
        }

        $this->usuarioModel->update($usuario['id'], [
            'senha_hash'           => password_hash($senha, PASSWORD_BCRYPT),
            'token_reset'          => null,
            'token_expira_em'      => null,
        ]);

        // Limpar flag trocar_senha_proximo se existir
        try {
            \Core\Database::getInstance()->prepare(
                "UPDATE usuarios SET trocar_senha_proximo = 0 WHERE id = :id"
            )->execute(['id' => $usuario['id']]);
        } catch (\Throwable $e) { /* coluna opcional — ignora */ }

        $_SESSION['login_sucesso'] = 'Senha redefinida com sucesso! Faça login com a nova senha.';
        header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/auth/login');
        exit;
    }

    // ----------------------------------------------------------------
    // Garante que as colunas de autenticação existem na tabela usuarios
    // (colunas adicionadas em migrações que podem não ter sido aplicadas)
    // ----------------------------------------------------------------
    private function garantirColunaTrocarSenha(): void
    {
        $db   = \Core\Database::getInstance();
        $cols = [];
        try {
            $rows = $db->query("SHOW COLUMNS FROM usuarios LIKE 'token_reset'")->fetchAll();
            if (empty($rows)) $cols[] = "ADD COLUMN token_reset VARCHAR(255) NULL DEFAULT NULL";
        } catch (\Throwable $e) {}
        try {
            $rows = $db->query("SHOW COLUMNS FROM usuarios LIKE 'token_expira_em'")->fetchAll();
            if (empty($rows)) $cols[] = "ADD COLUMN token_expira_em TIMESTAMP NULL DEFAULT NULL";
        } catch (\Throwable $e) {}
        try {
            $rows = $db->query("SHOW COLUMNS FROM usuarios LIKE 'trocar_senha_proximo'")->fetchAll();
            if (empty($rows)) $cols[] = "ADD COLUMN trocar_senha_proximo TINYINT(1) NOT NULL DEFAULT 0";
        } catch (\Throwable $e) {}

        if (!empty($cols)) {
            try {
                $db->exec("ALTER TABLE usuarios " . implode(', ', $cols));
            } catch (\Throwable $e) { /* ignora se outra requisição já criou */ }
        }
    }

    // ----------------------------------------------------------------
    private function gerarSenhaTemporaria(): string
    {
        // Apenas letras e números — evita problemas de cópia, codificação HTML e ambiguidade
        // visual (sem O/0, I/l/1). Comprimento 12 para compensar a menor entropia.
        $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $senha = '';
        for ($i = 0; $i < 12; $i++) {
            $senha .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $senha;
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
