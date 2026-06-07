<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\Configuracao;
use App\Services\BackupService;
use Core\View;

class BackupController
{
    private BackupService $backupService;
    private Configuracao  $configModel;

    public function __construct()
    {
        $this->backupService = new BackupService();
        $this->configModel   = new Configuracao();
    }

    // Aplica protecção admin em todas as acções excepto verificarAutomatico
    // que verifica a sessão manualmente para devolver JSON em vez de redirect.
    private function requireAdmin(): void
    {
        AuthMiddleware::apenasAdmin();
    }

    // ── GET /api/backup/verificar ──────────────────────────────────
    // Chamado silenciosamente pelo JS do layout. Verifica se é hora
    // de fazer o backup automático e executa-o se necessário.
    public function verificarAutomatico(): void
    {
        header('Content-Type: application/json');

        // Rejeita silenciosamente se não for admin (sem redirect)
        if (($_SESSION['perfil'] ?? '') !== 'admin') {
            echo json_encode(['executou' => false, 'motivo' => 'sem permissao']);
            exit;
        }

        $config           = $this->configModel->getAll();
        $horaConfigurada  = $config['backup_hora_automatico'] ?? '19:30';

        if (!$this->backupService->deveExecutarAgora($horaConfigurada)) {
            echo json_encode(['executou' => false, 'motivo' => 'fora da janela ou backup ja existe']);
            exit;
        }

        $resultado = $this->backupService->fazerBackup();

        echo json_encode([
            'executou' => $resultado['sucesso'],
            'mensagem' => $resultado['mensagem'],
            'arquivo'  => $resultado['arquivo']  ?? null,
            'tamanho'  => $resultado['tamanho']  ?? null,
        ]);
        exit;
    }

    // ── GET /backup ────────────────────────────────────────────────
    public function index(): void
    {
        $this->requireAdmin();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $config  = $this->configModel->getAll();
        $backups = $this->backupService->listarBackups();

        View::render('backup.index', [
            'titulo'            => 'Backup',
            'activePage'        => 'backup',
            'breadcrumb'        => ['Backup' => null],
            'backups'           => $backups,
            'backup_hora'       => $config['backup_hora_automatico'] ?? '19:30',
            'csrf_token'        => $_SESSION['csrf_token'],
            'flash_sucesso'     => $_SESSION['flash_sucesso'] ?? null,
            'flash_erro'        => $_SESSION['flash_erro']   ?? null,
        ]);

        unset($_SESSION['flash_sucesso'], $_SESSION['flash_erro']);
    }

    // ── POST /backup/fazer ─────────────────────────────────────────
    public function fazer(): void
    {
        $this->requireAdmin();
        $this->verificarCsrf();

        $resultado = $this->backupService->fazerBackup();

        if ($resultado['sucesso']) {
            $_SESSION['flash_sucesso'] = 'Backup criado com sucesso: ' . $resultado['arquivo'] . ' (' . $resultado['tamanho'] . ')';
        } else {
            $_SESSION['flash_erro'] = $resultado['mensagem'];
        }

        header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/backup');
        exit;
    }

    // ── GET /backup/descarregar?ficheiro=xxx ───────────────────────
    public function descarregar(): void
    {
        $this->requireAdmin();
        $nome    = $_GET['ficheiro'] ?? '';
        $caminho = $this->backupService->caminhoBackup($nome);

        if ($caminho === null) {
            $_SESSION['flash_erro'] = 'Ficheiro de backup não encontrado.';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/backup');
            exit;
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($caminho) . '"');
        header('Content-Length: ' . filesize($caminho));
        header('Cache-Control: must-revalidate');
        ob_clean();
        flush();
        readfile($caminho);
        exit;
    }

    // ── POST /backup/apagar ────────────────────────────────────────
    public function apagar(): void
    {
        $this->requireAdmin();
        $this->verificarCsrf();

        $nome = $_POST['ficheiro'] ?? '';
        if ($this->backupService->apagarBackup($nome)) {
            $_SESSION['flash_sucesso'] = 'Backup apagado com sucesso.';
        } else {
            $_SESSION['flash_erro'] = 'Não foi possível apagar o backup.';
        }

        header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/backup');
        exit;
    }

    // ── POST /backup/configurar-hora ───────────────────────────────
    public function configurarHora(): void
    {
        $this->requireAdmin();
        $this->verificarCsrf();

        $hora = trim($_POST['backup_hora_automatico'] ?? '');

        // Valida formato HH:MM
        if (!preg_match('/^\d{2}:\d{2}$/', $hora)) {
            $_SESSION['flash_erro'] = 'Hora inválida. Use o formato HH:MM (ex: 19:30).';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/backup');
            exit;
        }

        [$h, $m] = explode(':', $hora);
        if ((int)$h > 23 || (int)$m > 59) {
            $_SESSION['flash_erro'] = 'Hora inválida. Horas entre 00-23 e minutos entre 00-59.';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/backup');
            exit;
        }

        $this->configModel->salvar(['backup_hora_automatico' => $hora]);
        $_SESSION['flash_sucesso'] = "Hora do backup automático definida para as {$hora}. Actualize o cron do servidor para reflectir esta alteração.";

        header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/backup');
        exit;
    }

    // ── Helper ─────────────────────────────────────────────────────
    private function verificarCsrf(): void
    {
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            exit('Token inválido.');
        }
    }
}
