<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use Core\View;
use Core\Database;
use PDO;

class RelatorioController
{
    private \PDO $db;

    public function __construct()
    {
        AuthMiddleware::check();
        $this->db = Database::getInstance();
    }

    // ================================================================
    // GET /relatorios — Página principal
    // ================================================================
    public function index(): void
    {
        View::render('relatorios.index', [
            'titulo'     => 'Relatórios',
            'activePage' => 'relatorios',
            'breadcrumb' => ['Relatórios' => null],
        ]);
    }

    // ================================================================
    // GET /relatorios/vendas
    // ================================================================
    public function vendas(): void
    {
        $filtros = $this->filtrosVendas();

        View::render('relatorios.vendas', [
            'titulo'     => 'Relatório de Vendas',
            'activePage' => 'relatorios',
            'breadcrumb' => ['Relatórios' => '/relatorios', 'Vendas' => null],
            'filtros'    => $filtros,
            'resumo'     => $this->resumoVendas($filtros),
            'vendas'     => $this->dadosVendas($filtros),
            'por_dia'    => $this->vendasPorDia($filtros),
            'por_pagamento' => $this->vendasPorPagamento($filtros),
            'por_funcionario' => $this->vendasPorFuncionario($filtros),
            'funcionarios'   => $this->todosFuncionarios(),
        ]);
    }

    // ================================================================
    // GET /relatorios/vendas/pdf
    // ================================================================
    public function vendasPdf(): void
    {
        $filtros = $this->filtrosVendas();
        $appUrl  = $_ENV['APP_URL'] ?? '';

        extract([
            'filtros'          => $filtros,
            'resumo'           => $this->resumoVendas($filtros),
            'vendas'           => $this->dadosVendas($filtros),
            'por_pagamento'    => $this->vendasPorPagamento($filtros),
            'por_funcionario'  => $this->vendasPorFuncionario($filtros),
            'appUrl'           => $appUrl,
        ]);

        require __DIR__ . '/../../app/Views/relatorios/vendas_pdf.php';
        exit;
    }

    // ================================================================
    // GET /relatorios/stock
    // ================================================================
    public function stock(): void
    {
        View::render('relatorios.stock', [
            'titulo'     => 'Relatório de Stock',
            'activePage' => 'relatorios',
            'breadcrumb' => ['Relatórios' => '/relatorios', 'Stock' => null],
        ]);
    }

    // ================================================================
    // GET /relatorios/lotes-a-vencer
    // ================================================================
    public function lotesAVencer(): void
    {
        View::render('relatorios.lotes_vencer', [
            'titulo'     => 'Lotes a Vencer',
            'activePage' => 'relatorios',
            'breadcrumb' => ['Relatórios' => '/relatorios', 'Lotes a Vencer' => null],
        ]);
    }

    // ================================================================
    // GET /relatorios/funcionarios
    // ================================================================
    public function funcionarios(): void
    {
        View::render('relatorios.funcionarios', [
            'titulo'     => 'Relatório de Funcionários',
            'activePage' => 'relatorios',
            'breadcrumb' => ['Relatórios' => '/relatorios', 'Funcionários' => null],
        ]);
    }

    // ----------------------------------------------------------------
    // Helpers — Vendas
    // ----------------------------------------------------------------
    private function filtrosVendas(): array
    {
        return [
            'data_inicio'      => $_GET['data_inicio']      ?? date('Y-m-01'),
            'data_fim'         => $_GET['data_fim']          ?? date('Y-m-d'),
            'funcionario_id'   => $_GET['funcionario_id']   ?? '',
            'forma_pagamento'  => $_GET['forma_pagamento']  ?? '',
            'status'           => $_GET['status']            ?? 'concluida',
        ];
    }

    private function resumoVendas(array $f): array
    {
        return $this->fetchResumoVendas($this->whereVendas($f), $this->paramsVendas($f));
    }

    private function fetchResumoVendas(string $where, array $params): array
    {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*)                   AS total_vendas,
                COALESCE(SUM(total),   0)  AS valor_total,
                COALESCE(SUM(desconto),0)  AS total_descontos,
                COALESCE(AVG(total),   0)  AS ticket_medio,
                SUM(status='cancelada')    AS canceladas
            FROM vendas v
            LEFT JOIN usuarios u ON u.id = v.usuario_id
            WHERE $where
        ");
        $stmt->execute($params);
        return $stmt->fetch() ?: [];
    }

    private function dadosVendas(array $f): array
    {
        $where  = $this->whereVendas($f);
        $params = $this->paramsVendas($f);

        $stmt = $this->db->prepare("
            SELECT v.id, v.numero_venda, v.criado_em, v.forma_pagamento,
                   v.subtotal, v.desconto, v.total, v.status,
                   COALESCE(c.nome, 'Balcão') AS cliente_nome,
                   u.nome AS funcionario_nome,
                   (SELECT COUNT(*) FROM itens_venda iv WHERE iv.venda_id = v.id) AS total_itens
            FROM vendas v
            LEFT JOIN clientes  c ON c.id = v.cliente_id
            LEFT JOIN usuarios  u ON u.id = v.usuario_id
            WHERE $where
            ORDER BY v.criado_em DESC
            LIMIT 500
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function vendasPorDia(array $f): array
    {
        $where  = $this->whereVendas($f);
        $params = $this->paramsVendas($f);

        $stmt = $this->db->prepare("
            SELECT DATE(v.criado_em) AS dia,
                   COUNT(*)          AS total_vendas,
                   SUM(v.total)      AS valor_total
            FROM vendas v
            LEFT JOIN usuarios u ON u.id = v.usuario_id
            WHERE $where
            GROUP BY DATE(v.criado_em)
            ORDER BY dia ASC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function vendasPorPagamento(array $f): array
    {
        $where  = $this->whereVendas($f);
        $params = $this->paramsVendas($f);

        $stmt = $this->db->prepare("
            SELECT v.forma_pagamento,
                   COUNT(*)     AS total_vendas,
                   SUM(v.total) AS valor_total
            FROM vendas v
            LEFT JOIN usuarios u ON u.id = v.usuario_id
            WHERE $where
            GROUP BY v.forma_pagamento
            ORDER BY valor_total DESC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function vendasPorFuncionario(array $f): array
    {
        $where  = $this->whereVendas($f);
        $params = $this->paramsVendas($f);

        $stmt = $this->db->prepare("
            SELECT u.nome AS funcionario_nome,
                   COUNT(v.id)   AS total_vendas,
                   SUM(v.total)  AS valor_total,
                   AVG(v.total)  AS ticket_medio
            FROM vendas v
            LEFT JOIN usuarios u ON u.id = v.usuario_id
            WHERE $where
            GROUP BY v.usuario_id, u.nome
            ORDER BY valor_total DESC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function todosFuncionarios(): array
    {
        return $this->db->query("SELECT id, nome FROM usuarios WHERE ativo = 1 ORDER BY nome")->fetchAll();
    }

    private function whereVendas(array $f): string
    {
        $where = ["DATE(v.criado_em) BETWEEN :data_inicio AND :data_fim"];
        if (!empty($f['funcionario_id']))  $where[] = "v.usuario_id = :funcionario_id";
        if (!empty($f['forma_pagamento'])) $where[] = "v.forma_pagamento = :forma_pagamento";
        if (!empty($f['status']))          $where[] = "v.status = :status";
        return implode(' AND ', $where);
    }

    private function paramsVendas(array $f): array
    {
        $p = [
            'data_inicio' => $f['data_inicio'],
            'data_fim'    => $f['data_fim'],
        ];
        if (!empty($f['funcionario_id']))  $p['funcionario_id']  = $f['funcionario_id'];
        if (!empty($f['forma_pagamento'])) $p['forma_pagamento'] = $f['forma_pagamento'];
        if (!empty($f['status']))          $p['status']          = $f['status'];
        return $p;
    }
}
