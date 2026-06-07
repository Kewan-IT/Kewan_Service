<?php

namespace App\Services;

class BackupService
{
    private string $backupDir;

    public function __construct()
    {
        $this->backupDir = __DIR__ . '/../../storage/backups';

        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    /**
     * Executa o backup do banco de dados via mysqldump.
     */
    public function fazerBackup(): array
    {
        $host     = $_ENV['DB_HOST'] ?? 'localhost';
        $port     = $_ENV['DB_PORT'] ?? '3306';
        $database = $_ENV['DB_NAME'] ?? '';
        $username = $_ENV['DB_USER'] ?? '';
        $password = $_ENV['DB_PASS'] ?? '';

        if (empty($database) || empty($username)) {
            return [
                'sucesso'  => false,
                'mensagem' => 'Configurações do banco de dados em falta no ficheiro .env (DB_NAME, DB_USER, DB_PASS).',
            ];
        }

        $timestamp = date('Y-m-d_H-i-s');
        $filename  = "backup_kewanfarma_{$timestamp}.sql";
        $filepath  = $this->backupDir . DIRECTORY_SEPARATOR . $filename;

        // Monta o comando mysqldump de forma segura
        $passwordArg = $password !== '' ? '-p' . escapeshellarg($password) : '';

        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s %s --single-transaction --routines --triggers %s > %s 2>&1',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            $passwordArg,
            escapeshellarg($database),
            escapeshellarg($filepath)
        );

        exec($command, $output, $exitCode);

        if ($exitCode !== 0 || !file_exists($filepath) || filesize($filepath) === 0) {
            @unlink($filepath);
            $detalhe = implode(' ', $output);
            return [
                'sucesso'  => false,
                'mensagem' => 'Falha ao criar o backup. Verifique se o mysqldump está instalado e as credenciais estão correctas.' . ($detalhe ? ' Detalhe: ' . $detalhe : ''),
            ];
        }

        return [
            'sucesso'  => true,
            'mensagem' => 'Backup criado com sucesso.',
            'arquivo'  => $filename,
            'tamanho'  => $this->formatarTamanho(filesize($filepath)),
            'caminho'  => $filepath,
        ];
    }

    /**
     * Verifica se já existe um backup feito hoje.
     */
    public function backupHojeExiste(): bool
    {
        $hoje      = date('Y-m-d');
        $ficheiros = glob($this->backupDir . DIRECTORY_SEPARATOR . "backup_kewanfarma_{$hoje}_*.sql");
        return !empty($ficheiros);
    }

    /**
     * Verifica se a hora configurada já passou hoje e o backup ainda não foi feito.
     * Retorna true se deve executar o backup agora.
     * Janela de 1 hora após a hora configurada para não perder caso o sistema
     * estivesse offline exactamente à hora marcada.
     */
    public function deveExecutarAgora(string $horaConfigurada): bool
    {
        if ($this->backupHojeExiste()) {
            return false;
        }

        [$hh, $mm] = explode(':', $horaConfigurada . ':00');
        $horaAlvo  = mktime((int)$hh, (int)$mm, 0);
        $agora     = time();

        return $agora >= $horaAlvo && $agora < ($horaAlvo + 3600);
    }

    /**
     * Lista todos os ficheiros de backup disponíveis.
     */
    public function listarBackups(): array
    {
        $ficheiros = glob($this->backupDir . DIRECTORY_SEPARATOR . '*.sql');
        if (!$ficheiros) {
            return [];
        }

        $lista = [];
        foreach ($ficheiros as $caminho) {
            $lista[] = [
                'nome'     => basename($caminho),
                'tamanho'  => $this->formatarTamanho(filesize($caminho)),
                'data'     => date('d/m/Y H:i:s', filemtime($caminho)),
                'timestamp'=> filemtime($caminho),
                'caminho'  => $caminho,
            ];
        }

        // Mais recente primeiro
        usort($lista, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        return $lista;
    }

    /**
     * Devolve o caminho absoluto de um ficheiro de backup pelo nome.
     * Retorna null se o ficheiro não existir ou o nome for inválido.
     */
    public function caminhoBackup(string $nome): ?string
    {
        // Segurança: impede path traversal
        if (strpos($nome, '..') !== false || strpos($nome, '/') !== false || strpos($nome, '\\') !== false) {
            return null;
        }

        $caminho = $this->backupDir . DIRECTORY_SEPARATOR . $nome;
        return file_exists($caminho) ? $caminho : null;
    }

    /**
     * Apaga um ficheiro de backup pelo nome.
     */
    public function apagarBackup(string $nome): bool
    {
        $caminho = $this->caminhoBackup($nome);
        if ($caminho === null) {
            return false;
        }
        return @unlink($caminho);
    }

    /**
     * Remove backups com mais de $dias dias.
     */
    public function limparBackupsAntigos(int $dias = 30): array
    {
        $limite    = time() - ($dias * 86400);
        $ficheiros = glob($this->backupDir . DIRECTORY_SEPARATOR . '*.sql');
        $removidos = 0;

        foreach ($ficheiros as $caminho) {
            if (filemtime($caminho) < $limite) {
                @unlink($caminho);
                $removidos++;
            }
        }

        return [
            'sucesso'  => true,
            'mensagem' => "Limpeza concluída. {$removidos} backup(s) antigo(s) removido(s).",
        ];
    }

    // ── Helpers ────────────────────────────────────────────────────

    private function formatarTamanho(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }
}
