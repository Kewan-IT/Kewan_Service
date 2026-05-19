<?php
namespace Core;

class App {
    public static function run(): void {
        // Carrega variáveis de ambiente
        $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__, 1));
        $dotenv->load();

        // Configurações de sessão
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_strict_mode', 1);
        session_name($_ENV['SESSION_NAME'] ?? 'kewanfarma');
        session_start();

        // Fuso horário
        date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Africa/Maputo');

        // Carrega rotas e despacha
        $router = new Router();
        require dirname(__DIR__) . '/routes/web.php';
        require dirname(__DIR__) . '/routes/api.php';

        $url    = $_GET['url'] ?? '/';
        $method = $_SERVER['REQUEST_METHOD'];
        $router->dispatch('/' . trim($url, '/'), $method);
    }
}
