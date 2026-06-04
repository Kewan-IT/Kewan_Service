#!/usr/bin/env php
<?php

/**
 * Script de Backup Automático do KewanFarma
 * 
 * Este script deve ser agendado via cron para executar diariamente às 19h30
 * 
 * Exemplo de configuração no crontab:
 * 30 19 * * * /usr/bin/php /caminho/do/backup-automatico.php
 * 
 * Para editar o crontab: crontab -e
 */

// Definir timezone
date_default_timezone_set('Africa/Maputo');

// Carregar arquivo de variáveis de ambiente
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

// Autoloader do Composer
require_once __DIR__ . '/vendor/autoload.php';

// Usar o serviço de backup
use App\Services\BackupService;

try {
    $backupService = new BackupService();
    
    echo "[" . date('Y-m-d H:i:s') . "] Iniciando backup automático...\n";
    
    // Fazer backup
    $resultado = $backupService->fazerBackup();
    
    if ($resultado['sucesso']) {
        echo "[" . date('Y-m-d H:i:s') . "] ✓ " . $resultado['mensagem'] . "\n";
        echo "[" . date('Y-m-d H:i:s') . "] Arquivo: " . $resultado['arquivo'] . "\n";
        echo "[" . date('Y-m-d H:i:s') . "] Tamanho: " . $resultado['tamanho'] . "\n";
    } else {
        echo "[" . date('Y-m-d H:i:s') . "] ✗ ERRO: " . $resultado['mensagem'] . "\n";
        exit(1);
    }
    
    // Limpar backups antigos (manter apenas 30 dias)
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
