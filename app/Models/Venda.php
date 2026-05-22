<?php

namespace App\Models;

use Core\BaseModel;
use PDO;

class Venda extends BaseModel
{
    protected string $table = 'vendas';

    // ----------------------------------------------------------------
    // Gerar número sequencial VD-2025-00001
    // ----------------------------------------------------------------
    public function gerarNumero(): string
    {
        $ano    = date('Y');
        $prefixo = $_ENV['PREFIXO_VENDA'] ?? 'VD';
        $stmt   = $this->db->prepare("
            SELECT numero_venda FROM vendas
            WHERE numero_venda LIKE :p
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute(['p' => "$prefixo-$ano-%"]);
        $ultimo = $stmt->fetchColumn();
        $seq    = $ultimo ? (int)substr($ultimo, -5) + 1 : 1;
        return sprintf("%s-%s-%05d", $prefixo, $ano, $seq);
    }

    // ----------------------------------------------------------------
    // Criar venda completa com itens (transação)
    // ----------------------------------------------------------------
    public function criar(array $cabecalho, array $itens): int
    {
        $this->db->beginTransaction();
        try {
            $cabecalho['numero_venda'] = $this->gerarNumero();
            $cabecalho['usuario_id']   = $cabecalho['usuario_id'] ?? 0;

            // Inserir cabeçalho
            $vendaId = $this->insert($cabecalho);

            // Inserir itens
            $stmtItem = $this->db->prepare("
                INSERT INTO itens_venda
                    (venda_id, produto_id, lote_id, quantidade, preco_unitario, desconto_item, subtotal)
                VALUES
                    (:venda_id, :produto_id, :lote_id, :quantidade, :preco_unitario, :desconto_item, :subtotal)
            ");

            foreach ($itens as $item) {
                $stmtItem->execute([
                    'venda_id'      => $vendaId,
                    'produto_id'    => $item['produto_id'],
                    'lote_id'       => $item['lote_id'] ?? null,
                    'quantidade'    => $item['quantidade'],
                    'preco_unitario'=> $item['preco_unitario'],
                    'desconto_item' => $item['desconto_item'] ?? 0,
                    'subtotal'      => $item['subtotal'],
                ]);
            }

            // Registar movimento de caixa se houver caixa aberto
            $caixaAberto = $this->db->query("
                SELECT id FROM caixa WHERE status = 'aberto' ORDER BY id DESC LIMIT 1
            ")->fetchColumn();

            if ($caixaAberto) {
                $stmt = $this->db->prepare("
                    INSERT INTO movimentos_caixa
                        (caixa_id, venda_id, tipo, valor, descricao, usuario_id)
                    VALUES
                        (:caixa_id, :venda_id, 'venda', :valor, :descricao, :usuario_id)
                ");
                $stmt->execute([
                    'caixa_id'   => $caixaAberto,
                    'venda_id'   => $vendaId,
                    'valor'      => $cabecalho['total'],
                    'descricao'  => 'Venda ' . $cabecalho['numero_venda'],
                    'usuario_id' => $cabecalho['usuario_id'],
                ]);

                // Actualizar totais do caixa
                $this->db->prepare("
                    UPDATE caixa
                    SET total_vendas = total_vendas + :total
                    WHERE id = :id
                ")->execute(['total' => $cabecalho['total'], 'id' => $caixaAberto]);
            }

            $this->db->commit();
            return $vendaId;

        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ----------------------------------------------------------------
    // Detalhe completo da venda com itens
    // ----------------------------------------------------------------
    public function findCompleto(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT v.*,
                   c.nome   AS cliente_nome,
                   c.nuit   AS cliente_nuit,
                   c.telefone AS cliente_telefone,
                   u.nome   AS usuario_nome
            FROM vendas v
            LEFT JOIN clientes c ON c.id = v.cliente_id
            JOIN usuarios u      ON u.id = v.usuario_id
            WHERE v.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $venda = $stmt->fetch();
        if (!$venda) return null;

        // Itens da venda
        $stmt = $this->db->prepare("
            SELECT iv.*,
                   p.nome           AS produto_nome,
                   p.unidade_medida,
                   p.codigo_barras,
                   l.numero_lote
            FROM itens_venda iv
            JOIN produtos p         ON p.id  = iv.produto_id
            LEFT JOIN lotes l       ON l.id  = iv.lote_id
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
        $sql = "
            SELECT v.*,
                   c.nome AS cliente_nome,
                   u.nome AS usuario_nome,
                   COUNT(iv.id) AS total_itens
            FROM vendas v
            LEFT JOIN clientes c  ON c.id = v.cliente_id
            JOIN usuarios u       ON u.id = v.usuario_id
            LEFT JOIN itens_venda iv ON iv.venda_id = v.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND DATE(v.criado_em) >= :data_inicio";
            $params['data_inicio'] = $filtros['data_inicio'];
        }
        if (!empty($filtros['data_fim'])) {
            $sql .= " AND DATE(v.criado_em) <= :data_fim";
            $params['data_fim'] = $filtros['data_fim'];
        }
        if (!empty($filtros['status'])) {
            $sql .= " AND v.status = :status";
            $params['status'] = $filtros['status'];
        }
        if (!empty($filtros['forma_pagamento'])) {
            $sql .= " AND v.forma_pagamento = :forma_pagamento";
            $params['forma_pagamento'] = $filtros['forma_pagamento'];
        }
        if (!empty($filtros['busca'])) {
            $sql .= " AND (v.numero_venda LIKE :busca OR c.nome LIKE :busca)";
            $params['busca'] = '%' . $filtros['busca'] . '%';
        }

        $sql .= " GROUP BY v.id ORDER BY v.criado_em DESC";

        if (!empty($filtros['limite'])) {
            $sql .= " LIMIT " . (int)$filtros['limite'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------------
    // Cancelar venda (trigger repõe o stock automaticamente)
    // ----------------------------------------------------------------
    public function cancelar(int $id, string $motivo = ''): bool
    {
        $stmt = $this->db->prepare("
            UPDATE vendas
            SET status = 'cancelada',
                observacoes = CONCAT(COALESCE(observacoes,''), '\nCANCELADA: ', :motivo)
            WHERE id = :id AND status = 'concluida'
        ");
        return $stmt->execute(['id' => $id, 'motivo' => $motivo]);
    }

    // ----------------------------------------------------------------
    // Resumo do dia para o dashboard
    // ----------------------------------------------------------------
    public function resumoDia(): array
    {
        $stmt = $this->db->query("
            SELECT
                COUNT(*)              AS total_vendas,
                COALESCE(SUM(total), 0) AS valor_total,
                COALESCE(SUM(desconto), 0) AS descontos,
                COALESCE(AVG(total), 0)  AS ticket_medio,
                SUM(status = 'cancelada') AS canceladas
            FROM vendas
            WHERE DATE(criado_em) = CURDATE()
              AND status != 'cancelada'
        ");
        return $stmt->fetch();
    }

    // ----------------------------------------------------------------
    // Resumo por forma de pagamento (hoje)
    // ----------------------------------------------------------------
    public function resumoPagamentosDia(): array
    {
        $stmt = $this->db->query("
            SELECT forma_pagamento,
                   COUNT(*)       AS total_vendas,
                   SUM(total)     AS valor_total
            FROM vendas
            WHERE DATE(criado_em) = CURDATE()
              AND status = 'concluida'
            GROUP BY forma_pagamento
            ORDER BY valor_total DESC
        ");
        return $stmt->fetchAll();
    }
}
