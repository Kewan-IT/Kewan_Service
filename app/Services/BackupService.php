<?php

namespace App\Services;

class BackupService
{
    private string $dbHost;
    private string $dbPort;
    private string $dbUser;
    private string $dbPass;
    private string $dbName;
    private string $backupDir;

    public function __construct()
    {
        $this->dbHost = $_ENV['DB_HOST'] ?? 'localhost';
        $this->dbPort = $_ENV['DB_PORT'] ?? '3306';
        $this->dbUser = $_ENV['DB_USER'] ?? 'root';
        $this->dbPass = $_ENV['DB_PASS'] ?? '';
        $this->dbName = $_ENV['DB_NAME'] ?? 'kewanfarma';
        $this->backupDir = __DIR__ . '/../../storage/backups';

        // Garantir que o diretório existe
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    /**
     * Faz backup do banco de dados
     */
    public function fazerBackup(): array
    {
        try {
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "backup_kewanfarma_{$timestamp}.sql";
            $filepath = $this->backupDir . '/' . $filename;

            // Comando mysqldump
            $passwordOption = $this->dbPass !== '' ? '--password=' . escapeshellarg($this->dbPass) : '';
            $command = sprintf(
                'mysqldump --single-transaction --routines --triggers -h %s --port %s -u %s %s %s > %s 2>&1',
                escapeshellarg($this->dbHost),
                escapeshellarg($this->dbPort),
                escapeshellarg($this->dbUser),
                $passwordOption,
                escapeshellarg($this->dbName),
                escapeshellarg($filepath)
            );

            // Executar comando
            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                $outputMessage = !empty($output) ? implode('\n', $output) : 'Sem saída do comando.';
                throw new \RuntimeException('Falha ao executar mysqldump. Verifique se mysqldump está instalado e as credenciais estão corretas. Saída: ' . $outputMessage);
            }

            // Verificar se o arquivo foi criado e tem conteúdo
            if (!file_exists($filepath) || filesize($filepath) === 0) {
                throw new \RuntimeException('Arquivo de backup não foi criado ou está vazio.');
            }

            return [
                'sucesso' => true,
                'mensagem' => "Backup realizado com sucesso: {$filename}",
                'arquivo' => $filename,
                'tamanho' => $this->formatarTamanho(filesize($filepath)),
                'data' => date('Y-m-d H:i:s')
            ];
        } catch (\Throwable $e) {
            error_log('Erro ao fazer backup: ' . $e->getMessage());
            return [
                'sucesso' => false,
                'mensagem' => 'Erro ao fazer backup: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Lista todos os backups disponíveis
     */
    public function listarBackups(): array
    {
        $backups = [];
        
        if (!is_dir($this->backupDir)) {
            return $backups;
        }

        $files = array_reverse(glob($this->backupDir . '/backup_*.sql'));

        foreach ($files as $file) {
            $backups[] = [
                'arquivo' => basename($file),
                'tamanho' => $this->formatarTamanho(filesize($file)),
                'data' => date('Y-m-d H:i:s', filemtime($file)),
                'timestamp' => filemtime($file)
            ];
        }

        return $backups;
    }

    /**
     * Deleta um backup específico
     */
    public function deletarBackup(string $arquivo): array
    {
        try {
            // Validar nome do arquivo
            if (!preg_match('/^backup_kewanfarma_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql$/', $arquivo)) {
                throw new \RuntimeException('Nome de arquivo inválido.');
            }

            $filepath = $this->backupDir . '/' . $arquivo;

            if (!file_exists($filepath)) {
                throw new \RuntimeException('Arquivo não encontrado.');
            }

            unlink($filepath);

            return [
                'sucesso' => true,
                'mensagem' => "Backup deletado com sucesso: {$arquivo}"
            ];
        } catch (\Throwable $e) {
            error_log('Erro ao deletar backup: ' . $e->getMessage());
            return [
                'sucesso' => false,
                'mensagem' => 'Erro ao deletar backup: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Faz download de um backup
     */
    public function downloadBackup(string $arquivo): bool
    {
        try {
            // Validar nome do arquivo
            if (!preg_match('/^backup_kewanfarma_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql$/', $arquivo)) {
                throw new \RuntimeException('Nome de arquivo inválido.');
            }

            $filepath = $this->backupDir . '/' . $arquivo;

            if (!file_exists($filepath)) {
                throw new \RuntimeException('Arquivo não encontrado.');
            }

            header('Content-Type: application/sql');
            header('Content-Disposition: attachment; filename="' . $arquivo . '"');
            header('Content-Length: ' . filesize($filepath));

            readfile($filepath);
            return true;
        } catch (\Throwable $e) {
            error_log('Erro ao fazer download de backup: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Limpa backups antigos (mantém apenas os últimos N dias)
     */
    public function limparBackupsAntigos(int $dias = 30): array
    {
        try {
            $deletados = 0;
            $dataLimite = strtotime("-{$dias} days");

            if (!is_dir($this->backupDir)) {
                return ['sucesso' => true, 'deletados' => 0];
            }

            $files = glob($this->backupDir . '/backup_*.sql');

            foreach ($files as $file) {
                if (filemtime($file) < $dataLimite) {
                    unlink($file);
                    $deletados++;
                }
            }

            return [
                'sucesso' => true,
                'mensagem' => "Limpeza concluída: {$deletados} backup(s) antigo(s) deletado(s).",
                'deletados' => $deletados
            ];
        } catch (\Throwable $e) {
            error_log('Erro ao limpar backups antigos: ' . $e->getMessage());
            return [
                'sucesso' => false,
                'mensagem' => 'Erro ao limpar backups antigos: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Formata tamanho de arquivo em unidades legíveis
     */
    private function formatarTamanho(int $bytes): string
    {
        $unidades = ['B', 'KB', 'MB', 'GB'];
        $tamanho = $bytes;

        foreach ($unidades as $unidade) {
            if ($tamanho < 1024) {
                return round($tamanho, 2) . ' ' . $unidade;
            }
            $tamanho /= 1024;
        }

        return round($tamanho, 2) . ' TB';
    }
}
