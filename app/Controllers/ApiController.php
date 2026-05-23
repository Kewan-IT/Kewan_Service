<?php

namespace App\Controllers;

use App\Models\Produto;
use App\Models\Cliente;
use App\Middleware\AuthMiddleware;
use Core\View;

class ApiController
{
    public function __construct()
    {
        AuthMiddleware::check();
    }

    // GET /api/produtos/pesquisar?q=…
    public function pesquisarProdutos(): void
    {
        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 2) {
            View::json([]);
            return;
        }
        $model    = new Produto();
        $produtos = $model->pesquisarParaVenda($q, 12);
        View::json($produtos);
    }

    // GET /api/clientes/pesquisar?q=…
    public function pesquisarClientes(): void
    {
        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 2) {
            View::json([]);
            return;
        }
        $db   = \Core\Database::getInstance();
        $stmt = $db->prepare("
            SELECT id, nome, telefone, nuit
            FROM clientes
            WHERE ativo = 1
              AND (nome LIKE :q1 OR telefone LIKE :q2 OR nuit LIKE :q3)
            ORDER BY nome LIMIT 10
        ");
        $stmt->execute([
            'q1' => '%' . $q . '%',
            'q2' => '%' . $q . '%',
            'q3' => '%' . $q . '%',
        ]);
        View::json($stmt->fetchAll());
    }

    // GET /api/estoque/alertas
    public function alertasEstoque(): void
    {
        $model = new Produto();
        View::json($model->alertas());
    }

    // GET /api/dashboard/resumo
    public function resumoDashboard(): void
    {
        $db = \Core\Database::getInstance();

        $vendas_hoje = $db->query("
            SELECT COUNT(*) as total, COALESCE(SUM(total),0) as valor
            FROM vendas
            WHERE DATE(created_at) = CURDATE() AND status = 'concluida'
        ")->fetch();

        $stock_baixo = $db->query("
            SELECT COUNT(*) as total FROM produtos
            WHERE ativo = 1 AND estoque_actual <= estoque_min
        ")->fetchColumn();

        View::json([
            'vendas_hoje'  => $vendas_hoje,
            'stock_baixo'  => $stock_baixo,
        ]);
    }

    // GET /api/estoque/alertas (alias para VendaController::alertasStock)
    public function alertasStock(): void
    {
        $this->alertasEstoque();
    }
}
