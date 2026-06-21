<?php
namespace Core;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $_ENV['DB_HOST'], $_ENV['DB_PORT'], $_ENV['DB_NAME']
            );
            try {
                self::$instance = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);

                // Usa o fuso horário do próprio servidor (SYSTEM) para que
                // NOW()/CURDATE() no MySQL coincidam com a hora da máquina,
                // a menos que um fuso específico tenha sido definido em .env
                try {
                    $tz = $_ENV['APP_TIMEZONE'] ?? 'SYSTEM';
                    self::$instance->exec("SET time_zone = " . self::$instance->quote($tz));
                } catch (PDOException $e) {
                    // Se o MySQL não tiver as tabelas de fuso horário carregadas,
                    // mantém o fuso horário por omissão do servidor sem interromper a aplicação.
                }
            } catch (PDOException $e) {
                error_log('DB Error: ' . $e->getMessage());
                die(json_encode(['erro' => 'Falha na ligação ao banco de dados.']));
            }
        }
        return self::$instance;
    }
}
