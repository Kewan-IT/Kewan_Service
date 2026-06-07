#!/usr/bin/env php
<?php

/**
 * Script de Backup Automático do KewanFarma
 *
 * A hora de execução é configurada pelo administrador em /backup (configurações).
 * Gere a linha cron com base na hora guardada na base de dados:
 *
 *   XX YY * * * /usr/bin/php /caminho/do/projeto/backup-automatico.php >> storage/logs/backup.log 2>&1
 *
 * Para editar o crontab manualmente: crontab -e
 */

date_default_timezone_set('Africa/Maputo');

// Carregar variáveis de ambiente
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value, '"\'');
        }
    }
}

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\BackupService;
use Core\Database;

try {
    // Ler a hora configurada pelo administrador
    $db   = Database::getInstance();
    $stmt = $db->prepare("SELECT valor FROM configuracoes WHERE chave = 'backup_hora_automatico' LIMIT 1");
    $stmt->execute();
    $horaConfigurada = $stmt->fetchColumn() ?: '19:30';

    echo "[" . date('Y-m-d H:i:s') . "] Hora configurada para backup automático: {$horaConfigurada}\n";

    $backupService = new BackupService();

    echo "[" . date('Y-m-d H:i:s') . "] Iniciando backup automático...\n";

    $resultado = $backupService->fazerBackup();

    if ($resultado['sucesso']) {
        echo "[" . date('Y-m-d H:i:s') . "] ✓ " . $resultado['mensagem'] . "\n";
        echo "[" . date('Y-m-d H:i:s') . "] Arquivo: " . $resultado['arquivo'] . "\n";
        echo "[" . date('Y-m-d H:i:s') . "] Tamanho: " . $resultado['tamanho'] . "\n";
    } else {
        echo "[" . date('Y-m-d H:i:s') . "] ✗ ERRO: " . $resultado['mensagem'] . "\n";
        exit(1);
    }

    // Limpar backups com mais de 30 dias
    echo "[" . date('Y-m-d H:i:s') . "] Limpando backups antigos...\n";
    $limpeza = $backupService->limparBackupsAntigos(30);

    if ($limpeza['sucesso']) {
        echo "[" . date('Y-m-d H:i:s') . "] ✓ " . $limpeza['mensagem'] . "\n";
    } else {
        echo "[" . date('Y-m-d H:i:s') . "] ✗ AVISO: " . $limpeza['mensagem'] . "\n";
    }

    echo "[" . date('Y-m-d H:i:s') . "] Backup automático concluído com sucesso!\n";
    exit(0);

} catch (\Throwable $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ✗ ERRO FATAL: " . $e->getMessage() . "\n";
    exit(1);
}
