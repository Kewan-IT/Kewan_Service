<?php
namespace App\Models;

use Core\BaseModel;

class Venda extends BaseModel
{
    protected string $table = 'vendas';

    // ----------------------------------------------------------------
    // Gerar número sequencial  ex: VD-2025-00001
    // ----------------------------------------------------------------
    public function gerarNumero(): string
    {
        $ano     = date('Y');
        $prefixo = $_ENV['PREFIXO_VENDA'] ?? 'VD';
        $stmt    = $this->db->prepare("
            SELECT COUNT(*) FROM vendas WHERE YEAR(criado_em) = :ano
        ");
        $stmt->execute(['ano' => $ano]);
        $seq = (int) $stmt->fetchColumn() + 1;
        return sprintf('%s-%s-%05d', $prefixo, $ano, $seq);
    }

    // ----------------------------------------------------------------
    // Criar venda completa com itens (transação)
    // ----------------------------------------------------------------
    public function criar(array $cabecalho, array $itens): int
    {
        $this->db->beginTransaction();
        try {
            $cabecalho['numero_venda'] = $this->gerarNumero();

            // Inserir cabeçalho
            $vendaId = $this->insert($cabecalho);

            // Inserir itens e actualizar stock
            $stmtItem = $this->db->prepare("
                INSERT INTO itens_venda
                    (venda_id, produto_id, lote_id, quantidade, preco_unitario, desconto_item, subtotal)
                VALUES
                    (:venda_id, :produto_id, :lote_id, :quantidade, :preco_unitario, :desconto_item, :subtotal)
            ");

            $stmtStock = $this->db->prepare("
                UPDATE produtos SET estoque_actual = estoque_actual - :qty
                WHERE id = :id AND estoque_actual >= :qty2
            ");

            foreach ($itens as $item) {
                $stmtItem->execute([
                    'venda_id'       => $vendaId,
                    'produto_id'     => $item['produto_id'],
                    'lote_id'        => $item['lote_id'] ?? null,
                    'quantidade'     => $item['quantidade'],
                    'preco_unitario' => $item['preco_unitario'],
                    'desconto_item'  => $item['desconto_item'] ?? 0,
                    'subtotal'       => $item['subtotal'],
                ]);

                // Baixar stock
                $stmtStock->execute([
                    'qty' => $item['quantidade'], 'qty2' => $item['quantidade'],
                    'id'  => $item['produto_id'],
                ]);
            }

            // Registar movimento de caixa se houver caixa aberto
            $caixaId = $this->db->query("
                SELECT id FROM caixa WHERE status = 'aberto' ORDER BY id DESC LIMIT 1
            ")->fetchColumn();

            if ($caixaId) {
                $this->db->prepare("
                    INSERT INTO movimentos_caixa
                        (caixa_id, venda_id, tipo, valor, descricao, usuario_id)
                    VALUES
                        (:caixa_id, :venda_id, 'venda', :valor, :desc, :uid)
                ")->execute([
                    'caixa_id' => $caixaId,
                    'venda_id' => $vendaId,
                    'valor'    => $cabecalho['total'],
                    'desc'     => 'Venda ' . $cabecalho['numero_venda'],
                    'uid'      => $cabecalho['usuario_id'],
                ]);

                $this->db->prepare("
                    UPDATE caixa SET total_vendas = total_vendas + :total WHERE id = :id
                ")->execute(['total' => $cabecalho['total'], 'id' => $caixaId]);
            }

            $this->db->commit();
            return $vendaId;

        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ----------------------------------------------------------------
    // Detalhe completo com itens
    // ----------------------------------------------------------------
    public function findCompleto(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT v.*,
                   c.nome      AS cliente_nome,
                   c.nuit      AS cliente_nuit,
                   c.telefone  AS cliente_telefone,
                   u.nome      AS usuario_nome
            FROM vendas v
            LEFT JOIN clientes c ON c.id = v.cliente_id
            LEFT JOIN usuarios u ON u.id = v.usuario_id
            WHERE v.id = :id LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $venda = $stmt->fetch();
        if (!$venda) return null;

        $stmt = $this->db->prepare("
            SELECT iv.*,
                   p.nome           AS produto_nome,
                   p.unidade_medida,
                   p.codigo_barras,
                   l.numero_lote
            FROM itens_venda iv
            JOIN produtos p       ON p.id = iv.produto_id
            LEFT JOIN lotes l     ON l.id = iv.lote_id
            WHERE iv.venda_id = :id
        ");
        $stmt->execute(['id' => $id]);
        $venda['itens'] = $stmt->fetchAll();

        return $venda;
    }

    // ----------------------------------------------------------------
    // Listagem com filtros
    // ----------------------------------------------------------------
    public function listar(array $filtros = []): array
    {
        $sql    = "
            SELECT v.*,
                   c.nome AS cliente_nome,
                   u.nome AS usuario_nome,
                   COUNT(iv.id) AS total_itens
            FROM vendas v
            LEFT JOIN clientes c     ON c.id = v.cliente_id
            LEFT JOIN usuarios u     ON u.id = v.usuario_id
            LEFT JOIN itens_venda iv ON iv.venda_id = v.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filtros['data_inicio'])) {
            $sql .= ' AND DATE(v.criado_em) >= :data_inicio';
            $params['data_inicio'] = $filtros['data_inicio'];
        }
        if (!empty($filtros['data_fim'])) {
            $sql .= ' AND DATE(v.criado_em) <= :data_fim';
            $params['data_fim'] = $filtros['data_fim'];
        }
        if (!empty($filtros['status'])) {
            $sql .= ' AND v.status = :status';
            $params['status'] = $filtros['status'];
        }
        if (!empty($filtros['forma_pagamento'])) {
            $sql .= ' AND v.forma_pagamento = :forma_pagamento';
            $params['forma_pagamento'] = $filtros['forma_pagamento'];
        }
        if (!empty($filtros['busca'])) {
            $sql .= ' AND (v.numero_venda LIKE :busca OR c.nome LIKE :busca)';
            $params['busca'] = '%' . $filtros['busca'] . '%';
        }

        $sql .= ' GROUP BY v.id ORDER BY v.criado_em DESC';

        if (!empty($filtros['limite'])) {
            $sql .= ' LIMIT ' . (int)$filtros['limite'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------------
    // Cancelar venda
    // ----------------------------------------------------------------
    public function cancelar(int $id, string $motivo = ''): bool
    {
        // Repor stock
        $itens = $this->db->prepare("SELECT produto_id, quantidade FROM itens_venda WHERE venda_id = :id");
        $itens->execute(['id' => $id]);
        foreach ($itens->fetchAll() as $item) {
            $this->db->prepare("UPDATE produtos SET estoque_actual = estoque_actual + :qty WHERE id = :id")
                     ->execute(['qty' => $item['quantidade'], 'qty2' => $item['quantidade'], 'id' => $item['produto_id']]);
        }

        $stmt = $this->db->prepare("
            UPDATE vendas SET status = 'cancelada',
                observacoes = CONCAT(COALESCE(observacoes,''), ' [CANCELADA: ', :motivo, ']')
            WHERE id = :id AND status = 'concluida'
        ");
        return $stmt->execute(['id' => $id, 'motivo' => $motivo]);
    }

    // ----------------------------------------------------------------
    // Resumo do dia
    // ----------------------------------------------------------------
    public function resumoDia(): array
    {
        $stmt = $this->db->query("
            SELECT
                COUNT(*)                   AS total_vendas,
                COALESCE(SUM(total),0)     AS valor_total,
                COALESCE(SUM(desconto),0)  AS descontos,
                COALESCE(AVG(total),0)     AS ticket_medio
            FROM vendas
            WHERE DATE(criado_em) = CURDATE() AND status = 'concluida'
        ");
        return $stmt->fetch();
    }

    // ----------------------------------------------------------------
    // Resumo por forma de pagamento hoje
    // ----------------------------------------------------------------
    public function resumoPagamentosDia(): array
    {
        $stmt = $this->db->query("
            SELECT forma_pagamento, COUNT(*) AS total_vendas, SUM(total) AS valor_total
            FROM vendas
            WHERE DATE(criado_em) = CURDATE() AND status = 'concluida'
            GROUP BY forma_pagamento ORDER BY valor_total DESC
        ");
        return $stmt->fetchAll();
    }
}
