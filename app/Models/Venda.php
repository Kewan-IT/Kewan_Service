<?php
namespace App\Models;

use Core\BaseModel;
use PDO;

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
    // Lotes disponíveis para um produto (não vencidos, com stock > 0)
    // ordenados por FEFO (validade ASC)
    // ----------------------------------------------------------------
    public function lotesDisponiveisPorProduto(int $produtoId): array
    {
        $stmt = $this->db->prepare("
            SELECT id, numero_lote, quantidade, validade,
                   DATEDIFF(validade, CURDATE()) AS dias_para_vencer
            FROM lotes
            WHERE produto_id = :pid
              AND quantidade > 0
              AND validade >= CURDATE()
            ORDER BY validade ASC
        ");
        $stmt->execute(['pid' => $produtoId]);
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------------
    // Selecção automática FEFO: devolve lista [{lote_id, quantidade}]
    // que satisfaz a quantidade pedida, consumindo os lotes mais antigos
    // ----------------------------------------------------------------
    public function selecionarLotesFEFO(int $produtoId, int $qtdPedida): array
    {
        $lotes      = $this->lotesDisponiveisPorProduto($produtoId);
        $resultado  = [];
        $restante   = $qtdPedida;

        foreach ($lotes as $lote) {
            if ($restante <= 0) break;
            $usar       = min($lote['quantidade'], $restante);
            $resultado[] = ['lote_id' => (int)$lote['id'], 'quantidade' => $usar];
            $restante   -= $usar;
        }

        // Se ainda resta quantidade, o stock é insuficiente (não-vencido)
        if ($restante > 0) {
            return []; // sinaliza falha
        }

        return $resultado;
    }

    // ----------------------------------------------------------------
    // Criar venda completa com FEFO automático (transação)
    // ----------------------------------------------------------------
    public function criar(array $cabecalho, array $itens): int
    {
        $this->db->beginTransaction();
        try {
            $cabecalho['numero_venda'] = $this->gerarNumero();

            $vendaId = $this->insert($cabecalho);

            $stmtItem = $this->db->prepare("
                INSERT INTO itens_venda
                    (venda_id, produto_id, lote_id, quantidade, preco_unitario, desconto_item, subtotal)
                VALUES
                    (:venda_id, :produto_id, :lote_id, :quantidade, :preco_unitario, :desconto_item, :subtotal)
            ");

            $stmtLote = $this->db->prepare("
                UPDATE lotes
                SET quantidade = quantidade - :qty
                WHERE id = :lote_id AND quantidade >= :qty2
            ");

            $stmtStock = $this->db->prepare("
                UPDATE produtos SET estoque_actual = estoque_actual - :qty
                WHERE id = :id AND estoque_actual >= :qty2
            ");

            $stmtMov = $this->db->prepare("
                INSERT INTO movimentos_stock
                    (produto_id, lote_id, tipo, quantidade, referencia, usuario_id, observacoes)
                VALUES
                    (:produto_id, :lote_id, 'saida', :quantidade, :referencia, :usuario_id, :observacoes)
            ");

            foreach ($itens as $item) {
                $produtoId   = (int)$item['produto_id'];
                $qtdTotal    = (int)$item['quantidade'];
                $precoUnit   = (float)$item['preco_unitario'];
                $descontoIt  = (float)($item['desconto_item'] ?? 0);
                $subtotal    = (float)$item['subtotal'];

                // Resolver lotes via FEFO
                $loteId = $item['lote_id'] ?? null;

                if ($loteId) {
                    // Lote explicitamente passado (manual)
                    $alocacoes = [['lote_id' => (int)$loteId, 'quantidade' => $qtdTotal]];
                } else {
                    $alocacoes = $this->selecionarLotesFEFO($produtoId, $qtdTotal);
                }

                if (empty($alocacoes)) {
                    // Sem lotes válidos — insere sem lote (fallback)
                    $stmtItem->execute([
                        'venda_id'       => $vendaId,
                        'produto_id'     => $produtoId,
                        'lote_id'        => null,
                        'quantidade'     => $qtdTotal,
                        'preco_unitario' => $precoUnit,
                        'desconto_item'  => $descontoIt,
                        'subtotal'       => $subtotal,
                    ]);
                } else {
                    // Uma linha por alocação de lote (normalmente apenas 1)
                    $proporcaoTotal = $qtdTotal > 0 ? 1 : 1;
                    foreach ($alocacoes as $i => $aloc) {
                        $qLote   = $aloc['quantidade'];
                        $proporcao = $qLote / $qtdTotal;
                        $subLote   = ($i === array_key_last($alocacoes))
                            ? $subtotal - array_sum(array_column(array_slice($alocacoes, 0, -1), '_sub'))
                            : round($subtotal * $proporcao, 2);
                        $aloc['_sub'] = $subLote;

                        $stmtItem->execute([
                            'venda_id'       => $vendaId,
                            'produto_id'     => $produtoId,
                            'lote_id'        => $aloc['lote_id'],
                            'quantidade'     => $qLote,
                            'preco_unitario' => $precoUnit,
                            'desconto_item'  => round($descontoIt * $proporcao, 2),
                            'subtotal'       => $subLote,
                        ]);

                        // Baixar stock do lote
                        $ok = $stmtLote->execute([
                            'qty'     => $qLote,
                            'qty2'    => $qLote,
                            'lote_id' => $aloc['lote_id'],
                        ]);

                        // Movimento rastreável
                        $stmtMov->execute([
                            'produto_id'  => $produtoId,
                            'lote_id'     => $aloc['lote_id'],
                            'quantidade'  => $qLote,
                            'referencia'  => $cabecalho['numero_venda'],
                            'usuario_id'  => $cabecalho['usuario_id'],
                            'observacoes' => 'Venda ' . $cabecalho['numero_venda'],
                        ]);
                    }
                }

                // Baixar stock global do produto
                $stmtStock->execute([
                    'qty'  => $qtdTotal,
                    'qty2' => $qtdTotal,
                    'id'   => $produtoId,
                ]);
            }

            // Caixa
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
    // Alertas de lotes a vencer (30 / 60 / 90 dias)
    // ----------------------------------------------------------------
    public function alertasLotesAVencer(int $dias = 30): array
    {
        $stmt = $this->db->prepare("
            SELECT l.id, l.numero_lote, l.validade, l.quantidade,
                   p.id AS produto_id, p.nome AS produto_nome, p.unidade_medida,
                   DATEDIFF(l.validade, CURDATE()) AS dias_para_vencer
            FROM lotes l
            JOIN produtos p ON p.id = l.produto_id
            WHERE l.quantidade > 0
              AND l.validade >= CURDATE()
              AND l.validade <= DATE_ADD(CURDATE(), INTERVAL :dias DAY)
            ORDER BY l.validade ASC, p.nome ASC
        ");
        $stmt->execute(['dias' => $dias]);
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------------
    // Lotes vencidos com stock residual
    // ----------------------------------------------------------------
    public function lotesVencidos(): array
    {
        $stmt = $this->db->query("
            SELECT l.id, l.numero_lote, l.validade, l.quantidade,
                   p.nome AS produto_nome, p.unidade_medida,
                   DATEDIFF(CURDATE(), l.validade) AS dias_vencido
            FROM lotes l
            JOIN produtos p ON p.id = l.produto_id
            WHERE l.quantidade > 0
              AND l.validade < CURDATE()
            ORDER BY l.validade ASC
        ");
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------------
    // Detalhe completo com itens e lotes
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
                   l.numero_lote,
                   l.validade       AS lote_validade
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
    // Cancelar venda — repõe stock nos lotes
    // ----------------------------------------------------------------
    public function cancelar(int $id, string $motivo = ''): bool
    {
        $this->db->beginTransaction();
        try {
            $itens = $this->db->prepare("SELECT produto_id, lote_id, quantidade FROM itens_venda WHERE venda_id = :id");
            $itens->execute(['id' => $id]);
            foreach ($itens->fetchAll() as $item) {
                // Repor no produto
                $this->db->prepare("UPDATE produtos SET estoque_actual = estoque_actual + :qty WHERE id = :id")
                         ->execute(['qty' => $item['quantidade'], 'id' => $item['produto_id']]);
                // Repor no lote se existir
                if ($item['lote_id']) {
                    $this->db->prepare("UPDATE lotes SET quantidade = quantidade + :qty WHERE id = :id")
                             ->execute(['qty' => $item['quantidade'], 'id' => $item['lote_id']]);
                }
            }

            $stmt = $this->db->prepare("
                UPDATE vendas SET status = 'cancelada',
                    observacoes = CONCAT(COALESCE(observacoes,''), ' [CANCELADA: ', :motivo, ']')
                WHERE id = :id AND status = 'concluida'
            ");
            $ok = $stmt->execute(['id' => $id, 'motivo' => $motivo]);
            $this->db->commit();
            return $ok;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
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
