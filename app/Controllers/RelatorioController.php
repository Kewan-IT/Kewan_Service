<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Models\Configuracao;
use Core\View;
use Core\Database;
use PDO;

class RelatorioController
{
    private \PDO $db;

    public function __construct()
    {
        AuthMiddleware::apenasAdmin();
        $this->db = Database::getInstance();
    }

    // ================================================================
    // GET /relatorios
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
            'titulo'          => 'Relatório de Vendas',
            'activePage'      => 'relatorios',
            'breadcrumb'      => ['Relatórios' => '/relatorios', 'Vendas' => null],
            'filtros'         => $filtros,
            'resumo'          => $this->fetchResumoVendas($this->whereVendas($filtros), $this->paramsVendas($filtros)),
            'vendas'          => $this->dadosVendas($filtros),
            'por_dia'         => $this->vendasPorDia($filtros),
            'por_pagamento'   => $this->vendasPorPagamento($filtros),
            'por_funcionario' => $this->vendasPorFuncionario($filtros),
            'funcionarios'    => $this->todosFuncionarios(),
        ]);
    }

    // ================================================================
    // GET /relatorios/vendas/pdf
    // ================================================================
    public function vendasPdf(): void
    {
        $filtros = $this->filtrosVendas();
        $appUrl  = $_ENV['APP_URL'] ?? '';
        $config  = (new Configuracao())->getAllWithDefaults();

        extract([
            'filtros'         => $filtros,
            'resumo'          => $this->fetchResumoVendas($this->whereVendas($filtros), $this->paramsVendas($filtros)),
            'vendas'          => $this->dadosVendas($filtros),
            'por_pagamento'   => $this->vendasPorPagamento($filtros),
            'por_funcionario' => $this->vendasPorFuncionario($filtros),
            'appUrl'          => $appUrl,
            'config'          => $config,
        ]);
        require __DIR__ . '/../../app/Views/relatorios/vendas_pdf.php';
        exit;
    }

    // ================================================================
    // GET /relatorios/stock
    // ================================================================
    public function stock(): void
    {
        $filtros = $this->filtrosStock();
        View::render('relatorios.stock', [
            'titulo'        => 'Relatório de Stock',
            'activePage'    => 'relatorios',
            'breadcrumb'    => ['Relatórios' => '/relatorios', 'Stock' => null],
            'filtros'       => $filtros,
            'resumo'        => $this->resumoStock(),
            'produtos'      => $this->dadosStock($filtros),
            'por_categoria' => $this->stockPorCategoria(),
            'categorias'    => $this->todasCategorias(),
        ]);
    }

    // ================================================================
    // GET /relatorios/stock/pdf
    // ================================================================
    public function stockPdf(): void
    {
        $filtros = $this->filtrosStock();
        $appUrl  = $_ENV['APP_URL'] ?? '';
        $config  = (new Configuracao())->getAllWithDefaults();

        extract([
            'filtros'  => $filtros,
            'resumo'   => $this->resumoStock(),
            'produtos' => $this->dadosStock($filtros),
            'appUrl'   => $appUrl,
            'config'   => $config,
        ]);
        require __DIR__ . '/../../app/Views/relatorios/stock_pdf.php';
        exit;
    }

    // ================================================================
    // GET /relatorios/lotes-a-vencer
    // ================================================================
    public function lotesAVencer(): void
    {
        $prazo  = (int)($_GET['prazo'] ?? 30);
        $tipo   = $_GET['tipo'] ?? 'proximos'; // proximos | vencidos | todos
        $cat    = $_GET['categoria_id'] ?? '';

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        View::render('relatorios.lotes_vencer', [
            'titulo'        => 'Lotes a Vencer',
            'activePage'    => 'relatorios',
            'breadcrumb'    => ['Relatórios' => '/relatorios', 'Lotes a Vencer' => null],
            'prazo'         => $prazo,
            'tipo'          => $tipo,
            'categoria'     => $cat,
            'resumo'        => $this->resumoLotes($prazo),
            'lotes'         => $this->dadosLotes($prazo, $tipo, $cat),
            'categorias'    => $this->todasCategorias(),
            'csrf_token'    => $_SESSION['csrf_token'],
            'flash_sucesso' => $_SESSION['flash_sucesso'] ?? null,
            'flash_erro'    => $_SESSION['flash_erro']    ?? null,
            'devolucao_pdf_id' => $_SESSION['devolucao_pdf_id'] ?? null,
        ]);
        unset($_SESSION['flash_sucesso'], $_SESSION['flash_erro'], $_SESSION['devolucao_pdf_id']);
    }

    // ================================================================
    // GET /relatorios/lotes-a-vencer/pdf
    // ================================================================
    public function lotesAVencerPdf(): void
    {
        $prazo  = (int)($_GET['prazo'] ?? 30);
        $tipo   = $_GET['tipo'] ?? 'proximos';
        $cat    = $_GET['categoria_id'] ?? '';
        $appUrl = $_ENV['APP_URL'] ?? '';
        $config = (new Configuracao())->getAllWithDefaults();

        extract([
            'prazo'     => $prazo,
            'tipo'      => $tipo,
            'resumo'    => $this->resumoLotes($prazo),
            'lotes'     => $this->dadosLotes($prazo, $tipo, $cat),
            'appUrl'    => $appUrl,
            'config'    => $config,
        ]);
        require __DIR__ . '/../../app/Views/relatorios/lotes_vencer_pdf.php';
        exit;
    }

    // ================================================================
    // GET /relatorios/funcionarios
    // ================================================================
    public function funcionarios(): void
    {
        $filtros = $this->filtrosFuncionarios();
        View::render('relatorios.funcionarios', [
            'titulo'       => 'Relatório de Funcionários',
            'activePage'   => 'relatorios',
            'breadcrumb'   => ['Relatórios' => '/relatorios', 'Funcionários' => null],
            'filtros'      => $filtros,
            'resumo'       => $this->resumoFuncionarios($filtros),
            'ranking'      => $this->rankingFuncionarios($filtros),
            'por_dia'      => $this->vendasPorDiaFuncionario($filtros),
            'funcionarios' => $this->todosFuncionarios(),
        ]);
    }

    // ================================================================
    // GET /relatorios/funcionarios/pdf
    // ================================================================
    public function funcionariosPdf(): void
    {
        $filtros = $this->filtrosFuncionarios();
        $appUrl  = $_ENV['APP_URL'] ?? '';
        $config  = (new Configuracao())->getAllWithDefaults();

        extract([
            'filtros'  => $filtros,
            'resumo'   => $this->resumoFuncionarios($filtros),
            'ranking'  => $this->rankingFuncionarios($filtros),
            'appUrl'   => $appUrl,
            'config'   => $config,
        ]);
        require __DIR__ . '/../../app/Views/relatorios/funcionarios_pdf.php';
        exit;
    }

    // ----------------------------------------------------------------
    // Helpers — Vendas
    // ----------------------------------------------------------------
    private function filtrosVendas(): array
    {
        return [
            'data_inicio'     => $_GET['data_inicio']     ?? date('Y-m-01'),
            'data_fim'        => $_GET['data_fim']         ?? date('Y-m-d'),
            'funcionario_id'  => $_GET['funcionario_id']  ?? '',
            'forma_pagamento' => $_GET['forma_pagamento'] ?? '',
            'status'          => $_GET['status']           ?? 'concluida',
        ];
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
        $stmt   = $this->db->prepare("
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
        $stmt   = $this->db->prepare("
            SELECT DATE(v.criado_em) AS dia, COUNT(*) AS total_vendas, SUM(v.total) AS valor_total
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
        $stmt   = $this->db->prepare("
            SELECT v.forma_pagamento, COUNT(*) AS total_vendas, SUM(v.total) AS valor_total
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
        $stmt   = $this->db->prepare("
            SELECT u.nome AS funcionario_nome, COUNT(v.id) AS total_vendas,
                   SUM(v.total) AS valor_total, AVG(v.total) AS ticket_medio
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
        $p = ['data_inicio' => $f['data_inicio'], 'data_fim' => $f['data_fim']];
        if (!empty($f['funcionario_id']))  $p['funcionario_id']  = $f['funcionario_id'];
        if (!empty($f['forma_pagamento'])) $p['forma_pagamento'] = $f['forma_pagamento'];
        if (!empty($f['status']))          $p['status']          = $f['status'];
        return $p;
    }

    // ----------------------------------------------------------------
    // Helpers — Stock
    // ----------------------------------------------------------------
    private function filtrosStock(): array
    {
        return [
            'categoria_id' => $_GET['categoria_id'] ?? '',
            'estado'       => $_GET['estado']       ?? '',
            'q'            => trim($_GET['q']       ?? ''),
        ];
    }

    private function resumoStock(): array
    {
        return $this->db->query("
            SELECT
                COUNT(*)                                          AS total_produtos,
                SUM(estoque_actual <= estoque_min AND estoque_actual > 0) AS stock_baixo,
                SUM(estoque_actual <= 0)                          AS esgotados,
                COALESCE(SUM(estoque_actual * preco_compra), 0)   AS valor_total
            FROM produtos WHERE ativo = 1
        ")->fetch() ?: [];
    }

    private function dadosStock(array $f): array
    {
        $where  = ['p.ativo = 1'];
        $params = [];
        if (!empty($f['q'])) {
            $where[]      = '(p.nome LIKE :q1 OR p.codigo_barras LIKE :q2)';
            $params['q1'] = '%'.$f['q'].'%';
            $params['q2'] = '%'.$f['q'].'%';
        }
        if (!empty($f['categoria_id'])) {
            $where[]       = 'p.categoria_id = :cat';
            $params['cat'] = $f['categoria_id'];
        }
        if ($f['estado'] === 'baixo')    $where[] = 'p.estoque_actual <= p.estoque_min AND p.estoque_actual > 0';
        if ($f['estado'] === 'esgotado') $where[] = 'p.estoque_actual <= 0';
        if ($f['estado'] === 'normal')   $where[] = 'p.estoque_actual > p.estoque_min';

        $stmt = $this->db->prepare("
            SELECT p.id, p.nome, p.codigo_barras, p.estoque_actual, p.estoque_min,
                   p.preco_compra, p.preco_venda, p.unidade_medida,
                   c.nome AS categoria_nome
            FROM produtos p
            JOIN categorias c ON c.id = p.categoria_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY p.estoque_actual ASC, p.nome ASC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function stockPorCategoria(): array
    {
        return $this->db->query("
            SELECT c.nome AS categoria_nome, COUNT(p.id) AS total_produtos,
                   SUM(p.estoque_actual) AS total_stock,
                   SUM(p.estoque_actual * p.preco_compra) AS valor_stock
            FROM produtos p
            JOIN categorias c ON c.id = p.categoria_id
            WHERE p.ativo = 1
            GROUP BY c.id, c.nome
            ORDER BY valor_stock DESC
        ")->fetchAll();
    }

    private function todasCategorias(): array
    {
        return $this->db->query("SELECT id, nome FROM categorias WHERE ativo = 1 ORDER BY nome")->fetchAll();
    }

    // ----------------------------------------------------------------
    // Helpers — Lotes a Vencer
    // ----------------------------------------------------------------
    private function resumoLotes(int $prazo): array
    {
        $stmt = $this->db->prepare("
            SELECT
                SUM(validade < CURDATE() AND quantidade > 0)                              AS vencidos,
                SUM(validade >= CURDATE() AND DATEDIFF(validade, CURDATE()) <= :p30 AND quantidade > 0) AS ate_30_dias,
                SUM(validade >= CURDATE() AND DATEDIFF(validade, CURDATE()) <= :p60 AND quantidade > 0) AS ate_60_dias,
                SUM(validade >= CURDATE() AND DATEDIFF(validade, CURDATE()) <= :p90 AND quantidade > 0) AS ate_90_dias,
                SUM(CASE WHEN validade < CURDATE() THEN quantidade ELSE 0 END)            AS qtd_vencida,
                SUM(CASE WHEN validade >= CURDATE() AND DATEDIFF(validade, CURDATE()) <= :p902 THEN quantidade ELSE 0 END) AS qtd_proxima
            FROM lotes
        ");
        $stmt->execute(['p30' => 30, 'p60' => 60, 'p90' => 90, 'p902' => 90]);
        return $stmt->fetch() ?: [];
    }

    private function dadosLotes(int $prazo, string $tipo, string $cat): array
    {
        $where  = ['l.quantidade >= 0'];
        $params = [];

        if ($tipo === 'vencidos') {
            $where[] = 'l.validade < CURDATE()';
            $where[] = 'l.quantidade > 0';
        } elseif ($tipo === 'proximos') {
            $where[]      = 'l.validade >= CURDATE()';
            $where[]      = 'DATEDIFF(l.validade, CURDATE()) <= :prazo';
            $where[]      = 'l.quantidade > 0';
            $params['prazo'] = $prazo;
        } else {
            // todos — vencidos + próximos dentro do prazo
            $where[]      = '(l.validade < CURDATE() OR DATEDIFF(l.validade, CURDATE()) <= :prazo)';
            $where[]      = 'l.quantidade > 0';
            $params['prazo'] = $prazo;
        }

        if (!empty($cat)) {
            $where[]      = 'p.categoria_id = :cat';
            $params['cat'] = $cat;
        }

        $stmt = $this->db->prepare("
            SELECT
                l.id, l.numero_lote, l.quantidade, l.validade, l.data_entrada,
                l.em_promocao, l.preco_promocional,
                DATEDIFF(l.validade, CURDATE()) AS dias_para_vencer,
                p.id AS produto_id, p.nome AS produto_nome,
                p.preco_venda, p.unidade_medida,
                c.nome AS categoria_nome,
                f.nome AS fornecedor_nome,
                (l.quantidade * p.preco_compra) AS valor_em_risco
            FROM lotes l
            JOIN produtos   p ON p.id = l.produto_id
            JOIN categorias c ON c.id = p.categoria_id
            LEFT JOIN fornecedores f ON f.id = p.fornecedor_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY l.validade ASC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------------
    // Helpers — Funcionários
    // ----------------------------------------------------------------
    private function filtrosFuncionarios(): array
    {
        return [
            'data_inicio'    => $_GET['data_inicio']    ?? date('Y-m-01'),
            'data_fim'       => $_GET['data_fim']        ?? date('Y-m-d'),
            'funcionario_id' => $_GET['funcionario_id'] ?? '',
            'perfil'         => $_GET['perfil']          ?? '',
        ];
    }

    private function resumoFuncionarios(array $f): array
    {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(DISTINCT v.usuario_id)   AS total_funcionarios,
                COUNT(v.id)                    AS total_vendas,
                COALESCE(SUM(v.total), 0)      AS valor_total,
                COALESCE(MAX(sub.ticket), 0)   AS melhor_ticket
            FROM vendas v
            LEFT JOIN (
                SELECT usuario_id, AVG(total) AS ticket
                FROM vendas
                WHERE status = 'concluida'
                  AND DATE(criado_em) BETWEEN :d1 AND :d2
                GROUP BY usuario_id
            ) sub ON sub.usuario_id = v.usuario_id
            WHERE v.status = 'concluida'
              AND DATE(v.criado_em) BETWEEN :d3 AND :d4
        ");
        $stmt->execute([
            'd1' => $f['data_inicio'], 'd2' => $f['data_fim'],
            'd3' => $f['data_inicio'], 'd4' => $f['data_fim'],
        ]);
        return $stmt->fetch() ?: [];
    }

    private function rankingFuncionarios(array $f): array
    {
        $where  = ["v.status = 'concluida'", "DATE(v.criado_em) BETWEEN :data_inicio AND :data_fim"];
        $params = ['data_inicio' => $f['data_inicio'], 'data_fim' => $f['data_fim']];

        if (!empty($f['funcionario_id'])) {
            $where[]                  = 'v.usuario_id = :uid';
            $params['uid']            = $f['funcionario_id'];
        }
        if (!empty($f['perfil'])) {
            $where[]                  = 'u.perfil = :perfil';
            $params['perfil']         = $f['perfil'];
        }

        $whereStr = implode(' AND ', $where);
        $stmt = $this->db->prepare("
            SELECT u.id, u.nome, u.email, u.perfil,
                   COUNT(v.id)                    AS total_vendas,
                   COALESCE(SUM(v.total), 0)      AS valor_total,
                   COALESCE(AVG(v.total), 0)      AS ticket_medio,
                   COALESCE(SUM(iv.quantidade),0) AS total_itens
            FROM vendas v
            JOIN usuarios u   ON u.id = v.usuario_id
            LEFT JOIN itens_venda iv ON iv.venda_id = v.id
            WHERE $whereStr
            GROUP BY u.id, u.nome, u.email, u.perfil
            ORDER BY valor_total DESC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function vendasPorDiaFuncionario(array $f): array
    {
        if (empty($f['funcionario_id'])) return [];

        $stmt = $this->db->prepare("
            SELECT DATE(criado_em) AS dia, COUNT(*) AS total_vendas, SUM(total) AS valor_total
            FROM vendas
            WHERE status = 'concluida'
              AND DATE(criado_em) BETWEEN :ini AND :fim
              AND usuario_id = :uid
            GROUP BY DATE(criado_em)
            ORDER BY dia ASC
        ");
        $stmt->execute([
            'ini' => $f['data_inicio'],
            'fim' => $f['data_fim'],
            'uid' => $f['funcionario_id'],
        ]);
        return $stmt->fetchAll();
    }
}
