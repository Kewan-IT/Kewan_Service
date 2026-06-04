<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\Configuracao;
use App\Services\UploadService;
use App\Services\BackupService;
use Core\View;

class ConfiguracaoController
{
    private Configuracao $model;
    private UploadService $uploadService;
    private BackupService $backupService;

    public function __construct()
    {
        AuthMiddleware::requirePerfil('admin');
        $this->model = new Configuracao();
        $this->uploadService = new UploadService();
        $this->backupService = new BackupService();
    }

    public function index(): void
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $config = $this->defaults($this->model->getAll());
        $backups = $this->backupService->listarBackups();

        View::render('configuracoes.index', [
            'titulo' => 'Configurações',
            'activePage' => 'configuracoes',
            'breadcrumb' => ['Configurações' => null],
            'config' => $config,
            'backups' => $backups,
            'csrf_token' => $_SESSION['csrf_token'],
            'flash_sucesso' => $_SESSION['flash_sucesso'] ?? null,
            'flash_erro' => $_SESSION['flash_erro'] ?? null,
        ]);

        unset($_SESSION['flash_sucesso'], $_SESSION['flash_erro']);
    }

    public function update(): void
    {
        $this->verificarCsrf();

        try {
            $payload = [
                'nome_farmacia' => trim($_POST['nome_farmacia'] ?? ''),
                'nuit_farmacia' => trim($_POST['nuit_farmacia'] ?? ''),
                'endereco_farmacia' => trim($_POST['endereco_farmacia'] ?? ''),
                'telefone_farmacia' => trim($_POST['telefone_farmacia'] ?? ''),
                'email_farmacia' => trim($_POST['email_farmacia'] ?? ''),
                'moeda' => strtoupper(trim($_POST['moeda'] ?? 'MZN')),
                'iva_percentagem' => trim($_POST['iva_percentagem'] ?? '16'),
                'prefixo_venda' => strtoupper(trim($_POST['prefixo_venda'] ?? 'VD')),
                'prefixo_compra' => strtoupper(trim($_POST['prefixo_compra'] ?? 'CP')),
                'dias_alerta_validade' => trim($_POST['dias_alerta_validade'] ?? '90'),
            ];

            $logoPath = $this->processarLogo();
            if ($logoPath !== null) {
                $payload['logo_farmacia'] = $logoPath;
            }

            $this->model->salvar($payload);
            $_SESSION['flash_sucesso'] = 'Configurações guardadas com sucesso.';
        } catch (\Throwable $e) {
            error_log('Erro ao guardar configurações: ' . $e->getMessage());
            $_SESSION['flash_erro'] = 'Não foi possível guardar as configurações.';
        }

        header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/configuracoes');
        exit;
    }

    public function fazerBackup(): void
    {
        $this->verificarCsrf();

        $resultado = $this->backupService->fazerBackup();

        if ($resultado['sucesso']) {
            $_SESSION['flash_sucesso'] = $resultado['mensagem'];
        } else {
            $_SESSION['flash_erro'] = $resultado['mensagem'];
        }

        header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/configuracoes');
        exit;
    }

    public function deletarBackup(): void
    {
        $this->verificarCsrf();

        $arquivo = $_POST['arquivo'] ?? '';

        if (empty($arquivo)) {
            $_SESSION['flash_erro'] = 'Arquivo não especificado.';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/configuracoes');
            exit;
        }

        $resultado = $this->backupService->deletarBackup($arquivo);

        if ($resultado['sucesso']) {
            $_SESSION['flash_sucesso'] = $resultado['mensagem'];
        } else {
            $_SESSION['flash_erro'] = $resultado['mensagem'];
        }

        header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/configuracoes');
        exit;
    }

    public function downloadBackup(): void
    {
        $arquivo = $_GET['arquivo'] ?? '';

        if (empty($arquivo)) {
            http_response_code(404);
            exit('Arquivo não especificado.');
        }

        if (!$this->backupService->downloadBackup($arquivo)) {
            http_response_code(404);
            exit('Arquivo não encontrado.');
        }
    }

    private function processarLogo(): ?string
    {
        $arquivo = $_FILES['logo_farmacia'] ?? null;
        if (!is_array($arquivo) || ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($arquivo['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Falha ao enviar o logo da farmácia.');
        }

        return $this->uploadService->uploadLogo($arquivo);
    }

    private function defaults(array $config): array
    {
        $defaults = [
            'nome_farmacia' => 'KewanFarma',
            'nuit_farmacia' => '',
            'endereco_farmacia' => '',
            'telefone_farmacia' => '',
            'email_farmacia' => '',
            'logo_farmacia' => '',
            'moeda' => 'MZN',
            'iva_percentagem' => '16',
            'prefixo_venda' => 'VD',
            'prefixo_compra' => 'CP',
            'dias_alerta_validade' => '90',
        ];

        foreach ($defaults as $key => $value) {
            if (!array_key_exists($key, $config) || $config[$key] === null) {
                $config[$key] = $value;
            }
        }

        return $config;
    }

    private function verificarCsrf(): void
    {
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            exit('Token inválido.');
        }
    }
}
