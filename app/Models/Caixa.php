<?php
namespace App\Models;

use Core\BaseModel;

class Caixa extends BaseModel
{
    protected string $table = 'caixa';

    // ----------------------------------------------------------------
    // Caixa actualmente aberta (qualquer utilizador)
    // ----------------------------------------------------------------
    public function aberta(): ?array
    {
        $stmt = $this->db->query("
            SELECT c.*, u.nome AS usuario_nome
            FROM caixa c
            JOIN usuarios u ON u.id = c.usuario_id
            WHERE c.status = 'aberto'
            ORDER BY c.aberto_em DESC
            LIMIT 1
        ");
        return $stmt->fetch() ?: null;
    }

    // ----------------------------------------------------------------
    // Caixa aberta pelo utilizador actual
    // ----------------------------------------------------------------
    public function abertaPorUtilizador(int $usuarioId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM caixa
            WHERE status = 'aberto' AND usuario_id = :uid
            ORDER BY aberto_em DESC LIMIT 1
        ");
        $stmt->execute(['uid' => $usuarioId]);
        return $stmt->fetch() ?: null;
    }

    // ----------------------------------------------------------------
    // Abrir caixa
    // ----------------------------------------------------------------
    public function abrir(int $usuarioId, float $saldoInicial, ?string $obs = null): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO caixa (usuario_id, saldo_inicial, status, observacoes)
            VALUES (:uid, :saldo, 'aberto', :obs)
        ");
        $stmt->execute(['uid' => $usuarioId, 'saldo' => $saldoInicial, 'obs' => $obs]);
        return (int) $this->db->lastInsertId();
    }

    // ----------------------------------------------------------------
    // Fechar caixa
    // ----------------------------------------------------------------
    public function fechar(int $id, float $saldoFinal, ?string $obs = null): bool
    {
        $stmt = $this->db->prepare("
            UPDATE caixa
            SET status     = 'fechado',
                saldo_final = :saldo,
                fechado_em  = NOW(),
                observacoes = CONCAT(COALESCE(observacoes,''), :obs)
            WHERE id = :id AND status = 'aberto'
        ");
        return $stmt->execute(['id' => $id, 'saldo' => $saldoFinal, 'obs' => $obs ? "\n[Fecho: $obs]" : '']);
    }

    // ----------------------------------------------------------------
    // Adicionar movimento manual
    // ----------------------------------------------------------------
    public function adicionarMovimento(int $caixaId, string $tipo, float $valor, string $descricao, int $usuarioId, ?int $vendaId = null): int
    {
        $this->db->beginTransaction();
        try {
            // Inserir movimento
            $stmt = $this->db->prepare("
                INSERT INTO movimentos_caixa (caixa_id, venda_id, tipo, valor, descricao, usuario_id)
                VALUES (:caixa_id, :venda_id, :tipo, :valor, :descricao, :usuario_id)
            ");
            $stmt->execute([
                'caixa_id'   => $caixaId,
                'venda_id'   => $vendaId,
                'tipo'       => $tipo,
                'valor'      => $valor,
                'descricao'  => $descricao,
                'usuario_id' => $usuarioId,
            ]);
            $id = (int) $this->db->lastInsertId();

            // Actualizar totais da caixa
            if (in_array($tipo, ['venda', 'entrada', 'suprimento', 'devolucao'])) {
                $this->db->prepare("UPDATE caixa SET total_entradas = total_entradas + :v WHERE id = :id")
                         ->execute(['v' => $valor, 'id' => $caixaId]);
                if ($tipo === 'venda') {
                    $this->db->prepare("UPDATE caixa SET total_vendas = total_vendas + :v WHERE id = :id")
                             ->execute(['v' => $valor, 'id' => $caixaId]);
                }
            } else {
                // saida, sangria
                $this->db->prepare("UPDATE caixa SET total_saidas = total_saidas + :v WHERE id = :id")
                         ->execute(['v' => $valor, 'id' => $caixaId]);
            }

            $this->db->commit();
            return $id;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ----------------------------------------------------------------
    // Movimentos de uma sessão
    // ----------------------------------------------------------------
    public function movimentos(int $caixaId, int $limit = 100): array
    {
        $stmt = $this->db->prepare("
            SELECT m.*, u.nome AS usuario_nome,
                   v.numero_venda
            FROM movimentos_caixa m
            LEFT JOIN usuarios u ON u.id = m.usuario_id
            LEFT JOIN vendas v   ON v.id = m.venda_id
            WHERE m.caixa_id = :id
            ORDER BY m.criado_em DESC
            LIMIT $limit
        ");
        $stmt->execute(['id' => $caixaId]);
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------------
    // Resumo por tipo de movimento
    // ----------------------------------------------------------------
    public function resumoMovimentos(int $caixaId): array
    {
        $stmt = $this->db->prepare("
            SELECT tipo, COUNT(*) AS total_mov, SUM(valor) AS total_valor
            FROM movimentos_caixa
            WHERE caixa_id = :id
            GROUP BY tipo
        ");
        $stmt->execute(['id' => $caixaId]);
        $rows = $stmt->fetchAll();
        $res  = [];
        foreach ($rows as $r) {
            $res[$r['tipo']] = $r;
        }
        return $res;
    }

    // ----------------------------------------------------------------
    // Resumo por forma de pagamento das vendas
    // ----------------------------------------------------------------
    public function resumoPagamentos(int $caixaId): array
    {
        $stmt = $this->db->prepare("
            SELECT v.forma_pagamento,
                   COUNT(v.id)   AS total_vendas,
                   SUM(v.total)  AS total_valor
            FROM vendas v
            JOIN movimentos_caixa m ON m.venda_id = v.id AND m.caixa_id = :id
            WHERE v.status = 'concluida'
            GROUP BY v.forma_pagamento
            ORDER BY total_valor DESC
        ");
        $stmt->execute(['id' => $caixaId]);
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------------
    // Histórico de sessões (paginado)
    // ----------------------------------------------------------------
    public function historico(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $total  = (int) $this->db->query("SELECT COUNT(*) FROM caixa")->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT c.*, u.nome AS usuario_nome,
                   (c.saldo_inicial + c.total_entradas - c.total_saidas) AS saldo_esperado
            FROM caixa c
            JOIN usuarios u ON u.id = c.usuario_id
            ORDER BY c.aberto_em DESC
            LIMIT $perPage OFFSET $offset
        ");
        $stmt->execute();

        return [
            'data'         => $stmt->fetchAll(),
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => max(1, (int) ceil($total / $perPage)),
        ];
    }

    // ----------------------------------------------------------------
    // Detalhe de uma sessão
    // ----------------------------------------------------------------
    public function findCompleto(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT c.*,
                   u.nome AS usuario_nome,
                   (c.saldo_inicial + c.total_entradas - c.total_saidas) AS saldo_esperado,
                   CASE WHEN c.saldo_final IS NOT NULL
                        THEN c.saldo_final - (c.saldo_inicial + c.total_entradas - c.total_saidas)
                        ELSE NULL END AS diferenca
            FROM caixa c
            JOIN usuarios u ON u.id = c.usuario_id
            WHERE c.id = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    // ----------------------------------------------------------------
    // Dados ricos para o relatório de fecho — vendas detalhadas,
    // top produtos, top operadores, timeline horária
    // ----------------------------------------------------------------
    public function dadosRelatorio(int $caixaId): array
    {
        // Vendas completas da sessão
        $stmtV = $this->db->prepare("
            SELECT v.id, v.numero_venda, v.total, v.subtotal, v.desconto,
                   v.forma_pagamento, v.valor_pago, v.troco, v.status,
                   v.criado_em,
                   u.nome AS operador,
                   COALESCE(c.nome, 'Balcão') AS cliente
            FROM vendas v
            JOIN movimentos_caixa mc ON mc.venda_id = v.id AND mc.caixa_id = :id
            JOIN usuarios u ON u.id = v.usuario_id
            LEFT JOIN clientes c ON c.id = v.cliente_id
            WHERE v.status = 'concluida'
            ORDER BY v.criado_em ASC
        ");
        $stmtV->execute(['id' => $caixaId]);
        $vendas = $stmtV->fetchAll();

        // Top 10 produtos vendidos na sessão
        $stmtP = $this->db->prepare("
            SELECT p.nome AS produto, p.unidade_medida,
                   SUM(iv.quantidade) AS qtd_total,
                   SUM(iv.subtotal)   AS valor_total
            FROM itens_venda iv
            JOIN vendas v     ON v.id = iv.venda_id
            JOIN produtos p   ON p.id = iv.produto_id
            JOIN movimentos_caixa mc ON mc.venda_id = v.id AND mc.caixa_id = :id
            WHERE v.status = 'concluida'
            GROUP BY iv.produto_id
            ORDER BY qtd_total DESC
            LIMIT 10
        ");
        $stmtP->execute(['id' => $caixaId]);
        $topProdutos = $stmtP->fetchAll();

        // Vendas por hora (timeline)
        $stmtH = $this->db->prepare("
            SELECT HOUR(v.criado_em) AS hora,
                   COUNT(v.id)       AS num_vendas,
                   SUM(v.total)      AS total_hora
            FROM vendas v
            JOIN movimentos_caixa mc ON mc.venda_id = v.id AND mc.caixa_id = :id
            WHERE v.status = 'concluida'
            GROUP BY HOUR(v.criado_em)
            ORDER BY hora
        ");
        $stmtH->execute(['id' => $caixaId]);
        $porHora = $stmtH->fetchAll();

        // Movimentos manuais (sangrias, suprimentos, entradas, saídas)
        $stmtM = $this->db->prepare("
            SELECT m.*, u.nome AS usuario_nome
            FROM movimentos_caixa m
            LEFT JOIN usuarios u ON u.id = m.usuario_id
            WHERE m.caixa_id = :id AND m.tipo != 'venda'
            ORDER BY m.criado_em ASC
        ");
        $stmtM->execute(['id' => $caixaId]);
        $manuais = $stmtM->fetchAll();

        // Vendas por operador
        $stmtO = $this->db->prepare("
            SELECT u.nome AS operador,
                   COUNT(v.id) AS num_vendas,
                   SUM(v.total) AS total,
                   SUM(v.desconto) AS total_descontos
            FROM vendas v
            JOIN movimentos_caixa mc ON mc.venda_id = v.id AND mc.caixa_id = :id
            JOIN usuarios u ON u.id = v.usuario_id
            WHERE v.status = 'concluida'
            GROUP BY v.usuario_id
            ORDER BY total DESC
        ");
        $stmtO->execute(['id' => $caixaId]);
        $porOperador = $stmtO->fetchAll();

        // Ticket médio, maior e menor venda
        $stmtStats = $this->db->prepare("
            SELECT COUNT(v.id) AS total_vendas,
                   COALESCE(AVG(v.total),0)  AS ticket_medio,
                   COALESCE(MAX(v.total),0)  AS maior_venda,
                   COALESCE(MIN(v.total),0)  AS menor_venda,
                   COALESCE(SUM(v.desconto),0) AS total_descontos
            FROM vendas v
            JOIN movimentos_caixa mc ON mc.venda_id = v.id AND mc.caixa_id = :id
            WHERE v.status = 'concluida'
        ");
        $stmtStats->execute(['id' => $caixaId]);
        $statsVendas = $stmtStats->fetch();

        return compact('vendas', 'topProdutos', 'porHora', 'manuais', 'porOperador', 'statsVendas');
    }

    // ----------------------------------------------------------------
    // Estatísticas do dashboard
    // ----------------------------------------------------------------
    public function estatisticasHoje(): array
    {
        $aberta = $this->aberta();
        $stmt   = $this->db->query("
            SELECT
                COUNT(*)                        AS total_sessoes,
                COALESCE(SUM(total_vendas),0)   AS total_vendas,
                COALESCE(SUM(total_entradas),0) AS total_entradas,
                COALESCE(SUM(total_saidas),0)   AS total_saidas
            FROM caixa
            WHERE DATE(aberto_em) = CURDATE()
        ");
        $est = $stmt->fetch();
        $est['caixa_aberta'] = $aberta;
        return $est;
    }
}
