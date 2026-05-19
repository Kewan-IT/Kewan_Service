<?php
namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use Core\View;

class DashboardController
{
    public function index(): void
    {
        AuthMiddleware::check();
        View::render('dashboard.index', [
            'titulo'     => 'Dashboard',
            'activePage' => 'dashboard',
        ]);
    }

    public function resumoAjax(): void
    {
        AuthMiddleware::check();
        View::json(['total' => 0, 'status' => 'ok']);
    }
}