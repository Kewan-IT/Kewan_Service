<?php

namespace App\Models;

use Core\Database;
use PDO;

class Dashboard
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ================================================================
    // KPIs do dia
    // ================================================================
    public function kpisDia(): array
    {
        $vendas = $this->db->query("
            SELECT
                COUNT(*)                            AS total_vendas,
                COALESCE(SUM(total), 0)             AS valor_total,
                COALESCE(AVG(total), 0)             AS ticket_medio,
                COALESCE(SUM(desconto), 0)          AS total_descontos
            FROM vendas
            WHERE DATE(criado_em) = CURDATE() AND status = 'concluida'
        ")->fetch();

        $vendasOntem = $this->db->query("
            SELECT COALESCE(SUM(total), 0) AS valor_total, COUNT(*) AS total_vendas
            FROM vendas
            WHERE DATE(criado_em) = CURDATE() - INTERVAL 1 DAY AND status = 'concluida'
        ")->fetch();

        $produtos = $this->db->query("
            SELECT
                COUNT(*)                                       AS total,
                SUM(ativo = 1)                                 AS activos,
                SUM(estoque_actual <= estoque_min AND ativo=1) AS stock_baixo,
                SUM(estoque_actual = 0 AND ativo = 1)          AS sem_stock
            FROM produtos
        ")->fetch();

        $clientes = $this->db->query("
            SELECT COUNT(*) AS total, SUM(ativo=1) AS activos FROM clientes
        ")->fetch();

        $comprasMes = $this->db->query("
            SELECT COALESCE(SUM(total),0) AS valor
            FROM compras
            WHERE MONTH(data_pedido)=MONTH(CURDATE()) AND YEAR(data_pedido)=YEAR(CURDATE())
              AND status NOT IN ('cancelada','rascunho')
        ")->fetchColumn();

        $lotesCriticos = $this->db->query("
            SELECT COUNT(*) FROM lotes
            WHERE quantidade > 0 AND validade >= CURDATE()
              AND validade <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        ")->fetchColumn();

        $lotesVencidos = $this->db->query("
            SELECT COUNT(*) FROM lotes WHERE quantidade > 0 AND validade < CURDATE()
        ")->fetchColumn();

        // Variação percentual vs ontem
        $varVendas = $vendasOntem['valor_total'] > 0
            ? round((($vendas['valor_total'] - $vendasOntem['valor_total']) / $vendasOntem['valor_total']) * 100, 1)
            : 0;

        return [
            'vendas'         => $vendas,
            'vendas_ontem'   => $vendasOntem,
            'var_vendas'     => $varVendas,
            'produtos'       => $produtos,
            'clientes'       => $clientes,
            'compras_mes'    => (float)$comprasMes,
            'lotes_criticos' => (int)$lotesCriticos,
            'lotes_vencidos' => (int)$lotesVencidos,
        ];
    }

    // ================================================================
    // Vendas dos últimos 30 dias (para gráfico de linha)
    // ================================================================
    public function vendasUltimos30Dias(): array
    {
        $stmt = $this->db->query("
            SELECT
                DATE(criado_em)          AS dia,
                COUNT(*)                 AS total_vendas,
                COALESCE(SUM(total), 0)  AS valor_total
            FROM vendas
            WHERE criado_em >= CURDATE() - INTERVAL 29 DAY
              AND status = 'concluida'
            GROUP BY DATE(criado_em)
            ORDER BY dia ASC
        ");
        return $stmt->fetchAll();
    }

    // ================================================================
    // Vendas por hora do dia (hoje)
    // ================================================================
    public function vendasPorHoraHoje(): array
    {
        $stmt = $this->db->query("
            SELECT
                HOUR(criado_em)          AS hora,
                COUNT(*)                 AS total,
                COALESCE(SUM(total), 0)  AS valor
            FROM vendas
            WHERE DATE(criado_em) = CURDATE() AND status = 'concluida'
            GROUP BY HOUR(criado_em)
            ORDER BY hora
        ");
        return $stmt->fetchAll();
    }

    // ================================================================
    // Top 10 produtos mais vendidos no mês
    // ================================================================
    public function topProdutosMes(int $limit = 10): array
    {
        $stmt = $this->db->query("
            SELECT
                p.nome,
                p.unidade_venda,
                SUM(iv.quantidade)              AS qtd_vendida,
                SUM(iv.subtotal)                AS valor_total,
                COUNT(DISTINCT v.id)            AS num_vendas
            FROM itens_venda iv
            JOIN produtos p ON p.id = iv.produto_id
            JOIN vendas   v ON v.id = iv.venda_id
            WHERE MONTH(v.criado_em) = MONTH(CURDATE())
              AND YEAR(v.criado_em)  = YEAR(CURDATE())
              AND v.status = 'concluida'
            GROUP BY iv.produto_id, p.nome, p.unidade_venda
            ORDER BY qtd_vendida DESC
            LIMIT $limit
        ");
        return $stmt->fetchAll();
    }

    // ================================================================
    // Vendas por forma de pagamento (mês actual)
    // ================================================================
    public function vendasPorPagamento(): array
    {
        $stmt = $this->db->query("
            SELECT
                forma_pagamento,
                COUNT(*)                AS total_vendas,
                COALESCE(SUM(total), 0) AS valor_total
            FROM vendas
            WHERE MONTH(criado_em) = MONTH(CURDATE())
              AND YEAR(criado_em)  = YEAR(CURDATE())
              AND status = 'concluida'
            GROUP BY forma_pagamento
            ORDER BY valor_total DESC
        ");
        return $stmt->fetchAll();
    }

    // ================================================================
    // Vendas por categoria (mês)
    // ================================================================
    public function vendasPorCategoria(): array
    {
        $stmt = $this->db->query("
            SELECT
                c.nome                          AS categoria,
                SUM(iv.quantidade)              AS qtd,
                COALESCE(SUM(iv.subtotal), 0)   AS valor
            FROM itens_venda iv
            JOIN produtos  p ON p.id = iv.produto_id
            JOIN categorias c ON c.id = p.categoria_id
            JOIN vendas     v ON v.id = iv.venda_id
            WHERE MONTH(v.criado_em) = MONTH(CURDATE())
              AND YEAR(v.criado_em)  = YEAR(CURDATE())
              AND v.status = 'concluida'
            GROUP BY p.categoria_id, c.nome
            ORDER BY valor DESC
            LIMIT 8
        ");
        return $stmt->fetchAll();
    }

    // ================================================================
    // Melhor funcionário do mês (por valor de vendas)
    // ================================================================
    public function melhorFuncionarioMes(): ?array
    {
        $stmt = $this->db->query("
            SELECT
                f.id,
                f.nome_completo,
                f.foto_url,
                c.nome                          AS cargo,
                COUNT(v.id)                     AS total_vendas,
                COALESCE(SUM(v.total), 0)       AS valor_total,
                COALESCE(AVG(v.total), 0)       AS ticket_medio
            FROM vendas v
            JOIN usuarios    u ON u.id = v.usuario_id
            JOIN funcionarios f ON f.id = u.funcionario_id
            JOIN cargos      c ON c.id = f.cargo_id
            WHERE MONTH(v.criado_em) = MONTH(CURDATE())
              AND YEAR(v.criado_em)  = YEAR(CURDATE())
              AND v.status = 'concluida'
            GROUP BY f.id, f.nome_completo, f.foto_url, c.nome
            ORDER BY valor_total DESC
            LIMIT 1
        ");
        return $stmt->fetch() ?: null;
    }

    // ================================================================
    // Ranking de funcionários do mês
    // ================================================================
    public function rankingFuncionariosMes(int $limit = 5): array
    {
        $stmt = $this->db->query("
            SELECT
                f.id,
                f.nome_completo,
                f.foto_url,
                c.nome                          AS cargo,
                COUNT(v.id)                     AS total_vendas,
                COALESCE(SUM(v.total), 0)       AS valor_total
            FROM vendas v
            JOIN usuarios     u ON u.id = v.usuario_id
            JOIN funcionarios f ON f.id = u.funcionario_id
            JOIN cargos       c ON c.id = f.cargo_id
            WHERE MONTH(v.criado_em) = MONTH(CURDATE())
              AND YEAR(v.criado_em)  = YEAR(CURDATE())
              AND v.status = 'concluida'
            GROUP BY f.id, f.nome_completo, f.foto_url, c.nome
            ORDER BY valor_total DESC
            LIMIT $limit
        ");
        return $stmt->fetchAll();
    }

    // ================================================================
    // Aniversariantes do mês
    // ================================================================
    public function aniversariantesMes(): array
    {
        $stmt = $this->db->query("
            SELECT
                f.id,
                f.nome_completo,
                f.foto_url,
                f.data_nascimento,
                c.nome AS cargo,
                DAY(f.data_nascimento) AS dia_aniversario,
                TIMESTAMPDIFF(YEAR, f.data_nascimento, CURDATE()) + 1 AS idade_proxima,
                CASE
                    WHEN DAY(f.data_nascimento) = DAY(CURDATE()) THEN 'hoje'
                    WHEN DAY(f.data_nascimento) > DAY(CURDATE()) THEN 'proximo'
                    ELSE 'passado'
                END AS status_aniversario
            FROM funcionarios f
            JOIN cargos c ON c.id = f.cargo_id
            WHERE MONTH(f.data_nascimento) = MONTH(CURDATE())
              AND f.status = 'activo'
            ORDER BY DAY(f.data_nascimento) ASC
        ");
        return $stmt->fetchAll();
    }

    // ================================================================
    // Produtos com stock crítico
    // ================================================================
    public function produtosStockCritico(int $limit = 8): array
    {
        $stmt = $this->db->query("
            SELECT
                p.id, p.nome, p.estoque_actual, p.estoque_min,
                p.unidade_venda,
                c.nome AS categoria,
                ROUND((p.estoque_actual / NULLIF(p.estoque_min,0)) * 100) AS pct_stock
            FROM produtos p
            JOIN categorias c ON c.id = p.categoria_id
            WHERE p.ativo = 1
              AND p.estoque_actual <= p.estoque_min
            ORDER BY p.estoque_actual ASC
            LIMIT $limit
        ");
        return $stmt->fetchAll();
    }

    // ================================================================
    // Lotes a vencer em breve
    // ================================================================
    public function lotesAVencer(int $dias = 60, int $limit = 8): array
    {
        $stmt = $this->db->prepare("
            SELECT
                l.numero_lote,
                l.quantidade,
                l.validade,
                DATEDIFF(l.validade, CURDATE()) AS dias_restantes,
                p.nome AS produto_nome,
                p.unidade_venda
            FROM lotes l
            JOIN produtos p ON p.id = l.produto_id
            WHERE l.quantidade > 0
              AND l.validade >= CURDATE()
              AND l.validade <= DATE_ADD(CURDATE(), INTERVAL :dias DAY)
            ORDER BY l.validade ASC
            LIMIT :lim
        ");
        $stmt->bindValue(':dias', $dias, PDO::PARAM_INT);
        $stmt->bindValue(':lim',  $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ================================================================
    // Últimas vendas (para feed em tempo real)
    // ================================================================
    public function ultimasVendas(int $limit = 8): array
    {
        $stmt = $this->db->prepare("
            SELECT
                v.id, v.numero_venda, v.total, v.forma_pagamento,
                v.criado_em, v.status,
                u.nome AS funcionario,
                COALESCE(cl.nome, 'Balcão') AS cliente
            FROM vendas v
            JOIN usuarios u    ON u.id = v.usuario_id
            LEFT JOIN clientes cl ON cl.id = v.cliente_id
            ORDER BY v.criado_em DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ================================================================
    // Receita mensal dos últimos 6 meses (comparativo)
    // ================================================================
    public function receitaUltimosMeses(int $meses = 6): array
    {
        $stmt = $this->db->prepare("
            SELECT
                DATE_FORMAT(criado_em, '%Y-%m')    AS mes,
                DATE_FORMAT(criado_em, '%b/%Y')    AS mes_label,
                COUNT(*)                           AS total_vendas,
                COALESCE(SUM(total), 0)            AS valor_total
            FROM vendas
            WHERE criado_em >= DATE_SUB(DATE_FORMAT(NOW(),'%Y-%m-01'), INTERVAL :m MONTH)
              AND status = 'concluida'
            GROUP BY DATE_FORMAT(criado_em, '%Y-%m')
            ORDER BY mes ASC
        ");
        $stmt->bindValue(':m', $meses - 1, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ================================================================
    // Resumo de caixa hoje
    // ================================================================
    public function resumoCaixaHoje(): array
    {
        $stmt = $this->db->query("
            SELECT
                COALESCE(SUM(CASE WHEN tipo='entrada' THEN valor ELSE 0 END),0) AS total_entradas,
                COALESCE(SUM(CASE WHEN tipo='saida'   THEN valor ELSE 0 END),0) AS total_saidas,
                COUNT(*) AS total_movimentos
            FROM movimentos_caixa mc
            JOIN caixa cx ON cx.id = mc.caixa_id
            WHERE DATE(cx.aberto_em) = CURDATE()
        ");
        return $stmt->fetch();
    }

    // ================================================================
    // Compras vs Vendas (mês actual — para gauge de resultado)
    // ================================================================
    public function resultadoMes(): array
    {
        $vendas = (float)$this->db->query("
            SELECT COALESCE(SUM(total),0) FROM vendas
            WHERE MONTH(criado_em)=MONTH(CURDATE()) AND YEAR(criado_em)=YEAR(CURDATE())
              AND status='concluida'
        ")->fetchColumn();

        $compras = (float)$this->db->query("
            SELECT COALESCE(SUM(total),0) FROM compras
            WHERE MONTH(data_pedido)=MONTH(CURDATE()) AND YEAR(data_pedido)=YEAR(CURDATE())
              AND status NOT IN ('cancelada','rascunho')
        ")->fetchColumn();

        return [
            'vendas'    => $vendas,
            'compras'   => $compras,
            'resultado' => $vendas - $compras,
        ];
    }
}
