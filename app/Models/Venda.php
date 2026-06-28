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
                    UPDATE caixa SET total_vendas = total_vendas + :total1, total_entradas = total_entradas + :total2 WHERE id = :id
                ")->execute(['total1' => $cabecalho['total'], 'total2' => $cabecalho['total'], 'id' => $caixaId]);
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
            $sql .= ' AND (v.numero_venda LIKE :busca1 OR c.nome LIKE :busca2)';
            $params['busca1'] = '%' . $filtros['busca'] . '%';
            $params['busca2'] = '%' . $filtros['busca'] . '%';
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
    // Cancelar venda (devolução TOTAL) — repõe stock de todos os itens
    // ----------------------------------------------------------------
    public function cancelar(int $id, string $motivo = '', int $usuarioId = 0): bool
    {
        $this->garantirTabelasDevolucoes();
        $this->db->beginTransaction();
        try {
            $venda = $this->db->prepare("SELECT * FROM vendas WHERE id = :id AND status = 'concluida' LIMIT 1");
            $venda->execute(['id' => $id]);
            $v = $venda->fetch();
            if (!$v) {
                $this->db->rollBack();
                return false;
            }

            $itens = $this->db->prepare("SELECT * FROM itens_venda WHERE venda_id = :id");
            $itens->execute(['id' => $id]);
            $rows = $itens->fetchAll();

            foreach ($rows as $item) {
                $qtyRestante = (int)$item['quantidade'] - (int)$item['qty_devolvida'];
                if ($qtyRestante <= 0) continue;

                $this->db->prepare("UPDATE produtos SET estoque_actual = estoque_actual + :qty WHERE id = :pid")
                         ->execute(['qty' => $qtyRestante, 'pid' => $item['produto_id']]);
                if ($item['lote_id']) {
                    $this->db->prepare("UPDATE lotes SET quantidade = quantidade + :qty WHERE id = :lid")
                             ->execute(['qty' => $qtyRestante, 'lid' => $item['lote_id']]);
                }
                // Marcar item como totalmente devolvido
                $this->db->prepare("UPDATE itens_venda SET qty_devolvida = quantidade WHERE id = :id")
                         ->execute(['id' => $item['id']]);
            }

            // Registar devolução total
            $this->db->prepare("
                INSERT INTO devolucoes (venda_id, usuario_id, tipo, motivo, valor_total)
                VALUES (:vid, :uid, 'total', :motivo, :total)
            ")->execute([
                'vid'    => $id,
                'uid'    => $usuarioId ?: ((int)($_SESSION['usuario_id'] ?? 0)),
                'motivo' => $motivo,
                'total'  => $v['total'],
            ]);

            // Ajustar caixa — lançar saída pelo valor total
            $caixaId = $this->db->query("SELECT id FROM caixa WHERE status = 'aberto' ORDER BY id DESC LIMIT 1")->fetchColumn();
            if ($caixaId) {
                $this->db->prepare("
                    INSERT INTO movimentos_caixa (caixa_id, venda_id, tipo, valor, descricao, usuario_id)
                    VALUES (:cid, :vid, 'devolucao', :val, :desc, :uid)
                ")->execute([
                    'cid'  => $caixaId,
                    'vid'  => $id,
                    'val'  => $v['total'],
                    'desc' => 'Devolução total — ' . $v['numero_venda'] . ': ' . $motivo,
                    'uid'  => $usuarioId ?: ((int)($_SESSION['usuario_id'] ?? 0)),
                ]);
                $this->db->prepare("
                    UPDATE caixa
                    SET total_vendas   = GREATEST(0, total_vendas - :t1),
                        total_entradas = GREATEST(0, total_entradas - :t2)
                    WHERE id = :id
                ")->execute(['t1' => $v['total'], 't2' => $v['total'], 'id' => $caixaId]);
            }

            $this->db->prepare("
                UPDATE vendas
                SET status = 'cancelada',
                    observacoes = CONCAT(COALESCE(observacoes,''), ' [CANCELADA: ', :motivo, ']')
                WHERE id = :id
            ")->execute(['id' => $id, 'motivo' => $motivo]);

            $this->db->commit();
            return true;

        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ----------------------------------------------------------------
    // Devolução PARCIAL — devolve apenas os itens/quantidades indicados
    // $itens = [ ['item_venda_id' => X, 'quantidade' => Y], ... ]
    // ----------------------------------------------------------------
    public function devolverParcial(int $vendaId, array $itens, string $motivo, int $usuarioId): int
    {
        if (empty($itens)) throw new \InvalidArgumentException('Nenhum item seleccionado.');

        $this->garantirTabelasDevolucoes();
        $this->db->beginTransaction();
        try {
            $venda = $this->db->prepare("SELECT * FROM vendas WHERE id = :id AND status = 'concluida' LIMIT 1");
            $venda->execute(['id' => $vendaId]);
            $v = $venda->fetch();
            if (!$v) throw new \RuntimeException('Venda não encontrada ou já cancelada.');

            $valorDevolucao = 0.0;

            // Criar registo de devolução
            $this->db->prepare("
                INSERT INTO devolucoes (venda_id, usuario_id, tipo, motivo, valor_total)
                VALUES (:vid, :uid, 'parcial', :motivo, 0)
            ")->execute(['vid' => $vendaId, 'uid' => $usuarioId, 'motivo' => $motivo]);
            $devId = (int)$this->db->lastInsertId();

            foreach ($itens as $it) {
                $itemId = (int)($it['item_venda_id'] ?? 0);
                $qtyDev = (int)($it['quantidade']    ?? 0);
                if ($itemId <= 0 || $qtyDev <= 0) continue;

                // Obter item original
                $stmt = $this->db->prepare("SELECT * FROM itens_venda WHERE id = :id AND venda_id = :vid LIMIT 1");
                $stmt->execute(['id' => $itemId, 'vid' => $vendaId]);
                $item = $stmt->fetch();
                if (!$item) continue;

                $qtyDisponivel = (int)$item['quantidade'] - (int)$item['qty_devolvida'];
                $qtyDev        = min($qtyDev, $qtyDisponivel); // nunca devolver mais do que comprou
                if ($qtyDev <= 0) continue;

                $subtotalDev = round((float)$item['preco_unitario'] * $qtyDev, 2);
                $valorDevolucao += $subtotalDev;

                // Inserir item de devolução
                $this->db->prepare("
                    INSERT INTO devolucao_itens
                        (devolucao_id, item_venda_id, produto_id, lote_id, quantidade, preco_unitario, subtotal)
                    VALUES (:did, :iid, :pid, :lid, :qty, :preco, :sub)
                ")->execute([
                    'did'   => $devId,
                    'iid'   => $itemId,
                    'pid'   => $item['produto_id'],
                    'lid'   => $item['lote_id'],
                    'qty'   => $qtyDev,
                    'preco' => $item['preco_unitario'],
                    'sub'   => $subtotalDev,
                ]);

                // Actualizar qty_devolvida no item original
                $this->db->prepare("
                    UPDATE itens_venda SET qty_devolvida = qty_devolvida + :qty WHERE id = :id
                ")->execute(['qty' => $qtyDev, 'id' => $itemId]);

                // Repor stock do produto
                $this->db->prepare("UPDATE produtos SET estoque_actual = estoque_actual + :qty WHERE id = :pid")
                         ->execute(['qty' => $qtyDev, 'pid' => $item['produto_id']]);

                // Repor stock do lote
                if ($item['lote_id']) {
                    $this->db->prepare("UPDATE lotes SET quantidade = quantidade + :qty WHERE id = :lid")
                             ->execute(['qty' => $qtyDev, 'lid' => $item['lote_id']]);
                }
            }

            // Actualizar valor total da devolução
            $this->db->prepare("UPDATE devolucoes SET valor_total = :val WHERE id = :id")
                     ->execute(['val' => $valorDevolucao, 'id' => $devId]);

            // Verificar se todos os itens foram devolvidos → mudar status para 'devolvida'
            $pendente = $this->db->prepare("
                SELECT COUNT(*) FROM itens_venda
                WHERE venda_id = :vid AND quantidade > qty_devolvida
            ");
            $pendente->execute(['vid' => $vendaId]);
            $temPendente = (int)$pendente->fetchColumn();

            $novoStatus = $temPendente === 0 ? 'cancelada' : 'devolvida';
            $this->db->prepare("
                UPDATE vendas
                SET status = :status,
                    observacoes = CONCAT(COALESCE(observacoes,''), ' [DEV.PARCIAL: ', :motivo, ']')
                WHERE id = :id
            ")->execute(['status' => $novoStatus, 'id' => $vendaId, 'motivo' => $motivo]);

            // Ajustar caixa
            $caixaId = $this->db->query("SELECT id FROM caixa WHERE status = 'aberto' ORDER BY id DESC LIMIT 1")->fetchColumn();
            if ($caixaId && $valorDevolucao > 0) {
                $this->db->prepare("
                    INSERT INTO movimentos_caixa (caixa_id, venda_id, tipo, valor, descricao, usuario_id)
                    VALUES (:cid, :vid, 'devolucao', :val, :desc, :uid)
                ")->execute([
                    'cid'  => $caixaId,
                    'vid'  => $vendaId,
                    'val'  => $valorDevolucao,
                    'desc' => 'Devolução parcial — ' . $v['numero_venda'] . ': ' . $motivo,
                    'uid'  => $usuarioId,
                ]);
                $this->db->prepare("
                    UPDATE caixa
                    SET total_vendas   = GREATEST(0, total_vendas - :t1),
                        total_entradas = GREATEST(0, total_entradas - :t2)
                    WHERE id = :id
                ")->execute(['t1' => $valorDevolucao, 't2' => $valorDevolucao, 'id' => $caixaId]);
            }

            $this->db->commit();
            return $devId;

        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ----------------------------------------------------------------
    // Garantir que as tabelas de devoluções existem (auto-migração)
    // ----------------------------------------------------------------
    private function garantirTabelasDevolucoes(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS devolucoes (
                id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                venda_id    INT UNSIGNED NOT NULL,
                usuario_id  INT UNSIGNED NOT NULL,
                tipo        ENUM('total','parcial') NOT NULL DEFAULT 'total',
                motivo      TEXT NOT NULL,
                valor_total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                criado_em   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_venda (venda_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS devolucao_itens (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                devolucao_id    INT UNSIGNED NOT NULL,
                item_venda_id   INT UNSIGNED NOT NULL,
                produto_id      INT UNSIGNED NOT NULL,
                lote_id         INT UNSIGNED NULL,
                quantidade      INT UNSIGNED NOT NULL,
                preco_unitario  DECIMAL(12,2) NOT NULL,
                subtotal        DECIMAL(12,2) NOT NULL,
                INDEX idx_dev (devolucao_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        // Adicionar coluna qty_devolvida se ainda nao existir
        try {
            $this->db->exec("ALTER TABLE itens_venda ADD COLUMN qty_devolvida INT UNSIGNED NOT NULL DEFAULT 0");
        } catch (\PDOException $e) {
            if (strpos($e->getMessage(), '1060') === false) throw $e;
        }
    }

    // ----------------------------------------------------------------
    // Historico de devolucoes de uma venda
    // ----------------------------------------------------------------
    public function devolucoes(int $vendaId): array
    {
        $this->garantirTabelasDevolucoes();
        $stmt = $this->db->prepare("
            SELECT d.*, u.nome AS usuario_nome
            FROM devolucoes d
            LEFT JOIN usuarios u ON u.id = d.usuario_id
            WHERE d.venda_id = :vid
            ORDER BY d.criado_em DESC
        ");
        $stmt->execute(['vid' => $vendaId]);
        $devs = $stmt->fetchAll();

        foreach ($devs as &$dev) {
            $si = $this->db->prepare("
                SELECT di.*, p.nome AS produto_nome, p.unidade_medida, l.numero_lote
                FROM devolucao_itens di
                JOIN produtos p    ON p.id = di.produto_id
                LEFT JOIN lotes l  ON l.id = di.lote_id
                WHERE di.devolucao_id = :did
            ");
            $si->execute(['did' => $dev['id']]);
            $dev['itens'] = $si->fetchAll();
        }
        return $devs;
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
