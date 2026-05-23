<?php
namespace App\Models;

use Core\BaseModel;

class Venda extends BaseModel
{
    protected string $table = 'vendas';

    // ── Número sequencial VD-2025-00001 ─────────────────────
    public function gerarNumero(): string
    {
        $ano    = date('Y');
        $stmt   = $this->db->prepare(
            "SELECT numero_venda FROM vendas
             WHERE numero_venda LIKE :p ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute(['p' => "VD-$ano-%"]);
        $ultimo = $stmt->fetchColumn();
        $seq    = $ultimo ? ((int) substr($ultimo, -5)) + 1 : 1;
        return "VD-$ano-" . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    // ── Criar venda em transacção ────────────────────────────
    public function criar(array $cabecalho, array $itens): int
    {
        $this->db->beginTransaction();
        try {
            // Calcular totais
            $subtotal = 0.0;
            foreach ($itens as &$item) {
                $item['subtotal'] = round(
                    ($item['preco_unitario'] * $item['quantidade']) - ($item['desconto_item'] ?? 0),
                    2
                );
                $subtotal += $item['subtotal'];
            }
            unset($item);

            $desconto = (float)($cabecalho['desconto'] ?? 0);
            $total    = max(0.0, round($subtotal - $desconto, 2));

            // Inserir cabeçalho
            $vendaId = $this->insert(array_merge($cabecalho, [
                'numero_venda' => $this->gerarNumero(),
                'subtotal'     => $subtotal,
                'total'        => $total,
                'status'       => 'concluida',
            ]));

            // Inserir itens — os triggers da BD actualizam o stock
            $stmt = $this->db->prepare("
                INSERT INTO itens_venda
                    (venda_id, produto_id, lote_id, quantidade,
                     preco_unitario, desconto_item, subtotal)
                VALUES
                    (:venda_id, :produto_id, :lote_id, :quantidade,
                     :preco_unitario, :desconto_item, :subtotal)
            ");
            foreach ($itens as $item) {
                $stmt->execute([
                    'venda_id'      => $vendaId,
                    'produto_id'    => (int)$item['produto_id'],
                    'lote_id'       => $item['lote_id'] ?: null,
                    'quantidade'    => (int)$item['quantidade'],
                    'preco_unitario'=> (float)$item['preco_unitario'],
                    'desconto_item' => (float)($item['desconto_item'] ?? 0),
                    'subtotal'      => (float)$item['subtotal'],
                ]);
            }

            // Registar no caixa aberto (se existir)
            $stmtCaixa = $this->db->prepare(
                "SELECT id FROM caixa WHERE usuario_id = :uid
                 AND status = 'aberto' ORDER BY aberto_em DESC LIMIT 1"
            );
            $stmtCaixa->execute(['uid' => $cabecalho['usuario_id']]);
            $caixaId = $stmtCaixa->fetchColumn();

            if ($caixaId) {
                $numVenda = $this->db->query(
                    "SELECT numero_venda FROM vendas WHERE id = $vendaId"
                )->fetchColumn();

                $this->db->prepare("
                    INSERT INTO movimentos_caixa
                        (caixa_id, venda_id, tipo, valor, descricao, usuario_id)
                    VALUES (:cid, :vid, 'venda', :val, :desc, :uid)
                ")->execute([
                    'cid'  => $caixaId,
                    'vid'  => $vendaId,
                    'val'  => $total,
                    'desc' => "Venda $numVenda",
                    'uid'  => $cabecalho['usuario_id'],
                ]);

                $this->db->prepare(
                    "UPDATE caixa SET total_vendas = total_vendas + :v WHERE id = :id"
                )->execute(['v' => $total, 'id' => $caixaId]);
            }

            $this->db->commit();
            return $vendaId;

        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ── Detalhe completo com itens ───────────────────────────
    public function findCompleto(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT v.*,
                   c.nome     AS cliente_nome,
                   c.nuit     AS cliente_nuit,
                   c.telefone AS cliente_telefone,
                   u.nome     AS usuario_nome
            FROM vendas v
            LEFT JOIN clientes c ON c.id = v.cliente_id
            JOIN  usuarios  u    ON u.id = v.usuario_id
            WHERE v.id = :id LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $venda = $stmt->fetch();
        if (!$venda) return null;

        $stmt = $this->db->prepare("
            SELECT iv.*,
                   p.nome           AS produto_nome,
                   p.unidade_medida AS unidade,
                   l.numero_lote
            FROM itens_venda iv
            JOIN  produtos p   ON p.id = iv.produto_id
            LEFT JOIN lotes l  ON l.id = iv.lote_id
            WHERE iv.venda_id = :id
            ORDER BY iv.id ASC
        ");
        $stmt->execute(['id' => $id]);
        $venda['itens'] = $stmt->fetchAll();

        return $venda;
    }

    // ── Listagem com filtros ─────────────────────────────────
    public function listar(array $f = []): array
    {
        $sql    = "
            SELECT v.*, c.nome AS cliente_nome, u.nome AS usuario_nome,
                   (SELECT COUNT(*) FROM itens_venda WHERE venda_id = v.id) AS total_itens
            FROM vendas v
            LEFT JOIN clientes c ON c.id = v.cliente_id
            JOIN  usuarios  u    ON u.id = v.usuario_id
            WHERE 1=1
        ";
        $p = [];

        if (!empty($f['busca'])) {
            $sql .= " AND (v.numero_venda LIKE :bq OR c.nome LIKE :bq)";
            $p['bq'] = '%' . $f['busca'] . '%';
        }
        if (!empty($f['status']))          { $sql .= " AND v.status = :st";              $p['st'] = $f['status']; }
        if (!empty($f['forma_pagamento'])) { $sql .= " AND v.forma_pagamento = :fp";     $p['fp'] = $f['forma_pagamento']; }
        if (!empty($f['data_inicio']))     { $sql .= " AND DATE(v.criado_em) >= :di";    $p['di'] = $f['data_inicio']; }
        if (!empty($f['data_fim']))        { $sql .= " AND DATE(v.criado_em) <= :df";    $p['df'] = $f['data_fim']; }

        $sql .= " ORDER BY v.criado_em DESC";
        if (!empty($f['limite'])) $sql .= " LIMIT " . (int)$f['limite'];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($p);
        return $stmt->fetchAll();
    }

    // ── Cancelar ─────────────────────────────────────────────
    public function cancelar(int $id, string $motivo = ''): bool
    {
        $stmt = $this->db->prepare("
            UPDATE vendas
            SET status = 'cancelada',
                observacoes = CONCAT(COALESCE(observacoes, ''), :m)
            WHERE id = :id AND status = 'concluida'
        ");
        return $stmt->execute([
            'id' => $id,
            'm'  => $motivo ? "\n[Cancelamento: $motivo]" : '',
        ]);
    }

    // ── Resumo do dia ────────────────────────────────────────
    public function resumoHoje(): array
    {
        $stmt = $this->db->query("
            SELECT
                COUNT(*)                                AS total_vendas,
                COALESCE(SUM(total),   0)               AS valor_total,
                COALESCE(AVG(total),   0)               AS ticket_medio,
                SUM(forma_pagamento = 'dinheiro')        AS qtd_dinheiro,
                SUM(forma_pagamento = 'mpesa')           AS qtd_mpesa,
                SUM(forma_pagamento = 'emola')           AS qtd_emola,
                SUM(status = 'cancelada')                AS canceladas
            FROM vendas
            WHERE DATE(criado_em) = CURDATE()
        ");
        return $stmt->fetch();
    }
}
