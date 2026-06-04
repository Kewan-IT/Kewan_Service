<?php
namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use Core\Database;
use Core\View;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;

class DashboardController
{
    public function index(): void
    {
        AuthMiddleware::check();

        $db = Database::getInstance();
        $filtros = $this->filtrosDashboard();
        $funcionarios = $db->query("SELECT id, nome FROM usuarios WHERE ativo = 1 ORDER BY nome")->fetchAll();

        [$dataInicio, $dataFim] = $this->calcularPeriodo($filtros);

        $params = [
            'inicio' => $dataInicio . ' 00:00:00',
            'fim'    => $dataFim . ' 23:59:59',
        ];

        $where = ["v.status = 'concluida'", "v.criado_em >= :inicio", "v.criado_em <= :fim"];
        if (!empty($filtros['funcionario_id'])) {
            $where[] = 'v.usuario_id = :usuario_id';
            $params['usuario_id'] = (int) $filtros['funcionario_id'];
        }
        $whereStr = implode(' AND ', $where);

        $stmtVendasPeriodo = $db->prepare("SELECT COUNT(*) FROM vendas v WHERE $whereStr");
        $stmtVendasPeriodo->execute($params);

        $stmtReceitaPeriodo = $db->prepare("SELECT COALESCE(SUM(v.total), 0) FROM vendas v WHERE $whereStr");
        $stmtReceitaPeriodo->execute($params);

        $stmtVendasMes = $db->prepare("SELECT COUNT(*) FROM vendas WHERE status = 'concluida' AND MONTH(criado_em) = MONTH(CURDATE()) AND YEAR(criado_em) = YEAR(CURDATE())");
        $stmtVendasMes->execute();

        $stmtReceitaMes = $db->prepare("SELECT COALESCE(SUM(total), 0) FROM vendas WHERE status = 'concluida' AND MONTH(criado_em) = MONTH(CURDATE()) AND YEAR(criado_em) = YEAR(CURDATE())");
        $stmtReceitaMes->execute();

        $stats = [
            'vendas_periodo'   => (int) $stmtVendasPeriodo->fetchColumn(),
            'receita_periodo'  => (float) $stmtReceitaPeriodo->fetchColumn(),
            'vendas_mes'       => (int) $stmtVendasMes->fetchColumn(),
            'receita_mes'      => (float) $stmtReceitaMes->fetchColumn(),
            'produtos_ativos'  => (int) $db->query("SELECT COUNT(*) FROM produtos WHERE ativo = 1")->fetchColumn(),
            'stock_baixo'      => (int) $db->query("SELECT COUNT(*) FROM produtos WHERE ativo = 1 AND estoque_actual <= estoque_min")->fetchColumn(),
            'clientes_activos' => (int) $db->query("SELECT COUNT(*) FROM clientes WHERE ativo = 1")->fetchColumn(),
            'lotes_criticos'   => (int) $db->query("SELECT COUNT(*) FROM lotes WHERE quantidade > 0 AND validade >= CURDATE() AND validade <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetchColumn(),
            'lotes_vencidos'   => (int) $db->query("SELECT COUNT(*) FROM lotes WHERE quantidade > 0 AND validade < CURDATE()")->fetchColumn(),
            'receitas_pendentes' => (int) $db->query("SELECT COUNT(*) FROM receitas_medicas WHERE status = 'pendente'")->fetchColumn(),
        ];

        $aniversariantes_mes = $db->query("
            SELECT
                id,
                nome_completo,
                foto_url,
                data_nascimento,
                DAY(data_nascimento) AS dia_nascimento,
                MONTHNAME(data_nascimento) AS mes_nome
            FROM funcionarios
            WHERE status = 'activo'
              AND data_nascimento IS NOT NULL
              AND MONTH(data_nascimento) = MONTH(CURDATE())
            ORDER BY DAY(data_nascimento) ASC
        ")->fetchAll();

        $stmtUltimasVendas = $db->prepare("
            SELECT
                v.numero_venda,
                COALESCE(c.nome, 'Balcão') AS cliente_nome,
                v.total,
                v.forma_pagamento,
                v.status,
                v.criado_em
            FROM vendas v
            LEFT JOIN clientes c ON c.id = v.cliente_id
            WHERE $whereStr
            ORDER BY v.criado_em DESC
            LIMIT 5
        ");
        $stmtUltimasVendas->execute($params);
        $ultimas_vendas = $stmtUltimasVendas->fetchAll();

        $stock_baixo = $db->query("
            SELECT id, nome, estoque_actual, estoque_min, unidade_medida
            FROM produtos
            WHERE ativo = 1 AND estoque_actual <= estoque_min
            ORDER BY estoque_actual ASC, nome ASC
            LIMIT 5
        ")->fetchAll();

        $lotes_criticos = $db->query("
            SELECT
                l.id,
                p.nome AS produto_nome,
                l.numero_lote,
                l.validade,
                l.quantidade,
                p.unidade_medida,
                DATEDIFF(l.validade, CURDATE()) AS dias_para_vencer
            FROM lotes l
            JOIN produtos p ON p.id = l.produto_id
            WHERE l.quantidade > 0
              AND l.validade >= CURDATE()
              AND l.validade <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            ORDER BY l.validade ASC, p.nome ASC
            LIMIT 5
        ")->fetchAll();

        $stmtSeries = $db->prepare("
            SELECT DATE(v.criado_em) AS dia,
                   COUNT(*) AS total_vendas,
                   COALESCE(SUM(v.total), 0) AS valor_total
            FROM vendas v
            WHERE $whereStr
            GROUP BY DATE(v.criado_em)
            ORDER BY dia ASC
        ");
        $stmtSeries->execute($params);
        $series = $stmtSeries->fetchAll();

        $mapaSeries = [];
        foreach ($series as $serie) {
            $mapaSeries[$serie['dia']] = [
                'total_vendas' => (int) $serie['total_vendas'],
                'valor_total' => (float) $serie['valor_total'],
            ];
        }

        $grafico_vendas = ['labels' => [], 'qtds' => [], 'valores' => []];
        $inicio = new DateTimeImmutable($dataInicio);
        $fim = new DateTimeImmutable($dataFim);
        $intervalo = new DatePeriod($inicio, new DateInterval('P1D'), $fim->modify('+1 day'));

        foreach ($intervalo as $dia) {
            $chave = $dia->format('Y-m-d');
            $grafico_vendas['labels'][] = $dia->format('d/m');
            $grafico_vendas['qtds'][] = (int) ($mapaSeries[$chave]['total_vendas'] ?? 0);
            $grafico_vendas['valores'][] = (float) ($mapaSeries[$chave]['valor_total'] ?? 0);
        }

        $stmtPagamentos = $db->prepare("
            SELECT v.forma_pagamento,
                   COUNT(*) AS total,
                   COALESCE(SUM(v.total), 0) AS valor_total
            FROM vendas v
            WHERE $whereStr
            GROUP BY v.forma_pagamento
            ORDER BY total DESC
        ");
        $stmtPagamentos->execute($params);
        $pagamentos = $stmtPagamentos->fetchAll();

        $grafico_pagamento = ['labels' => [], 'valores' => []];
        foreach ($pagamentos as $pagamento) {
            $grafico_pagamento['labels'][] = ucwords(str_replace('_', ' ', $pagamento['forma_pagamento']));
            $grafico_pagamento['valores'][] = (float) $pagamento['valor_total'];
        }

        View::render('dashboard.index', [
            'titulo'               => 'Dashboard',
            'activePage'           => 'dashboard',
            'stats'                => $stats,
            'aniversariantes_mes'  => $aniversariantes_mes,
            'ultimas_vendas'       => $ultimas_vendas,
            'stock_baixo'          => $stock_baixo,
            'lotes_criticos'       => $lotes_criticos,
            'grafico_vendas'       => $grafico_vendas,
            'grafico_pagamento'    => $grafico_pagamento,
            'funcionarios'         => $funcionarios,
            'filtros'              => $filtros,
        ]);
    }

    public function resumoAjax(): void
    {
        AuthMiddleware::check();
        View::json(['total' => 0, 'status' => 'ok']);
    }

    private function filtrosDashboard(): array
    {
        return [
            'periodo'        => $_GET['periodo'] ?? 'mes',
            'data_inicio'    => $_GET['data_inicio'] ?? '',
            'data_fim'       => $_GET['data_fim'] ?? '',
            'funcionario_id' => $_GET['funcionario_id'] ?? '',
        ];
    }

    private function calcularPeriodo(array $filtros): array
    {
        $periodo = $filtros['periodo'] ?? 'mes';
        $dataInicio = $filtros['data_inicio'] ?? '';
        $dataFim = $filtros['data_fim'] ?? '';

        if ($periodo === 'custom') {
            if ($dataInicio === '' || $dataFim === '') {
                $dataInicio = date('Y-m-01');
                $dataFim = date('Y-m-d');
            }
            return [$dataInicio, $dataFim];
        }

        $hoje = new DateTimeImmutable('today');

        return match ($periodo) {
            'hoje' => [$hoje->format('Y-m-d'), $hoje->format('Y-m-d')],
            '7d' => [$hoje->modify('-6 days')->format('Y-m-d'), $hoje->format('Y-m-d')],
            '30d' => [$hoje->modify('-29 days')->format('Y-m-d'), $hoje->format('Y-m-d')],
            default => [date('Y-m-01'), date('Y-m-d')],
        };
    }
}