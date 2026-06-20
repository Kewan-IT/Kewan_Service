<?php

namespace App\Models;

use Core\BaseModel;

class Produto extends BaseModel
{
    protected string $table = 'produtos';

    // ----------------------------------------------------------------
    // Listagem paginada com filtros
    // ----------------------------------------------------------------
    public function listar(
        string $pesquisa   = '',
        int    $categoria  = 0,
        string $filtro     = '',
        int    $page       = 1,
        int    $perPage    = 24
    ): array {
        $where  = ['p.ativo = 1'];
        $params = [];

        if ($pesquisa !== '') {
            $where[]        = '(p.nome LIKE :pesq OR p.codigo_barras LIKE :pesq OR p.principio_ativo LIKE :pesq)';
            $params['pesq'] = '%' . $pesquisa . '%';
        }
        if ($categoria > 0) {
            // inclui subcategorias
            $where[]             = '(p.categoria_id = :cat OR c.categoria_pai_id = :cat)';
            $params['cat']       = $categoria;
        }
        if ($filtro === 'stock_baixo') {
            $where[] = 'p.estoque_actual < p.estoque_min';
        } elseif ($filtro === 'sem_stock') {
            $where[] = 'p.estoque_actual = 0';
        } elseif ($filtro === 'receita') {
            $where[] = 'p.requer_receita = 1';
        } elseif ($filtro === 'controlado') {
            $where[] = 'p.controlado = 1';
        }

        $whereStr = implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;

        $stmtCount = $this->db->prepare("
            SELECT COUNT(*) FROM produtos p
            JOIN categorias c ON c.id = p.categoria_id
            WHERE $whereStr
        ");
        $stmtCount->execute($params);
        $total = (int) $stmtCount->fetchColumn();

        $stmtData = $this->db->prepare("
            SELECT
                p.*,
                c.nome AS categoria_nome,
                cp.nome AS categoria_pai_nome,
                f.nome AS fornecedor_nome,
                (p.estoque_actual < p.estoque_min) AS stock_baixo,
                (SELECT MIN(l.validade) FROM lotes l WHERE l.produto_id = p.id AND l.quantidade > 0) AS proxima_validade
            FROM produtos p
            JOIN categorias c       ON c.id = p.categoria_id
            LEFT JOIN categorias cp ON cp.id = c.categoria_pai_id
            LEFT JOIN fornecedores f ON f.id = p.fornecedor_id
            WHERE $whereStr
            ORDER BY p.nome
            LIMIT $perPage OFFSET $offset
        ");
        $stmtData->execute($params);

        return [
            'data'         => $stmtData->fetchAll(),
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => max(1, (int) ceil($total / $perPage)),
        ];
    }

    // ----------------------------------------------------------------
    // Ficha completa com lotes
    // ----------------------------------------------------------------
    public function findComDetalhes(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                p.*,
                c.nome            AS categoria_nome,
                cp.id             AS categoria_pai_id,
                cp.nome           AS categoria_pai_nome,
                f.nome            AS fornecedor_nome,
                f.telefone        AS fornecedor_telefone
            FROM produtos p
            JOIN categorias c       ON c.id = p.categoria_id
            LEFT JOIN categorias cp ON cp.id = c.categoria_pai_id
            LEFT JOIN fornecedores f ON f.id = p.fornecedor_id
            WHERE p.id = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    // ----------------------------------------------------------------
    // Lotes do produto
    // ----------------------------------------------------------------
    public function lotes(int $produtoId): array
    {
        $stmt = $this->db->prepare("
            SELECT *, DATEDIFF(validade, CURDATE()) AS dias_validade
            FROM lotes
            WHERE produto_id = :id
            ORDER BY validade ASC
        ");
        $stmt->execute(['id' => $produtoId]);
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------------
    // Histórico de movimentos de stock
    // ----------------------------------------------------------------
    public function movimentos(int $produtoId, int $limit = 30): array
    {
        $stmt = $this->db->prepare("
            SELECT em.*, u.nome AS usuario_nome, l.numero_lote
            FROM estoque_movimentos em
            LEFT JOIN usuarios u ON u.id = em.usuario_id
            LEFT JOIN lotes l    ON l.id = em.lote_id
            WHERE em.produto_id = :id
            ORDER BY em.criado_em DESC
            LIMIT $limit
        ");
        $stmt->execute(['id' => $produtoId]);
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------------
    // Pesquisa AJAX para o balcão de vendas
    // ----------------------------------------------------------------
    public function pesquisarParaVenda(string $q, int $limit = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT
                p.id, p.nome, p.codigo_barras, p.principio_ativo,
                p.preco_venda, p.estoque_actual, p.requer_receita,
                p.controlado, p.unidade_medida, p.imagem_url,
                p.unidade_compra, p.unidade_venda, p.fator_conversao,
                c.nome AS categoria,
                (SELECT MIN(l.validade) FROM lotes l WHERE l.produto_id = p.id AND l.quantidade > 0) AS proxima_validade,
                (SELECT l.id FROM lotes l WHERE l.produto_id = p.id AND l.quantidade > 0 ORDER BY l.validade ASC LIMIT 1) AS lote_id,
                (SELECT l.numero_lote FROM lotes l WHERE l.produto_id = p.id AND l.quantidade > 0 ORDER BY l.validade ASC LIMIT 1) AS numero_lote,
                (SELECT l.em_promocao FROM lotes l WHERE l.produto_id = p.id AND l.quantidade > 0 ORDER BY l.validade ASC LIMIT 1) AS lote_em_promocao,
                (SELECT l.preco_promocional FROM lotes l WHERE l.produto_id = p.id AND l.quantidade > 0 ORDER BY l.validade ASC LIMIT 1) AS lote_preco_promocional,
                (SELECT l.quantidade FROM lotes l WHERE l.produto_id = p.id AND l.quantidade > 0 ORDER BY l.validade ASC LIMIT 1) AS lote_quantidade
            FROM produtos p
            JOIN categorias c ON c.id = p.categoria_id
            WHERE p.ativo = 1
              AND p.estoque_actual > 0
              AND (p.nome LIKE :q1 OR p.codigo_barras LIKE :q2 OR p.principio_ativo LIKE :q3)
            ORDER BY p.nome
            LIMIT $limit
        ");
        $stmt->execute(['q1' => '%' . $q . '%', 'q2' => '%' . $q . '%', 'q3' => '%' . $q . '%']);
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------------
    // Adicionar lote — aplica fator_conversao automaticamente
    // Exemplo: comprar 10 caixas × 10 cartelas = 100 unidades em stock
    // ----------------------------------------------------------------
    public function adicionarLote(array $dados): int
    {
        // Obter factor de conversão do produto
        $produto = $this->findById($dados['produto_id']);
        $fator   = (float)($produto['fator_conversao'] ?? 1);
        if ($fator <= 0) $fator = 1;

        // Quantidade em unidade de compra (ex: 10 caixas)
        $qtdCompra  = (int)$dados['quantidade'];
        // Quantidade convertida em unidade de venda (ex: 100 cartelas)
        $qtdVenda   = (int)round($qtdCompra * $fator);

        $dados['quantidade_compra'] = $qtdCompra;  // guardar referência
        $dados['quantidade']        = $qtdVenda;   // stock em unidade de venda

        $stmt = $this->db->prepare("
            INSERT INTO lotes (produto_id, numero_lote, quantidade, validade, data_entrada, observacoes)
            VALUES (:produto_id, :numero_lote, :quantidade, :validade, :data_entrada, :observacoes)
            ON DUPLICATE KEY UPDATE
                quantidade = quantidade + VALUES(quantidade),
                validade   = VALUES(validade)
        ");
        $stmt->execute([
            'produto_id'   => $dados['produto_id'],
            'numero_lote'  => $dados['numero_lote'],
            'quantidade'   => $dados['quantidade'],
            'validade'     => $dados['validade'],
            'data_entrada' => $dados['data_entrada'],
            'observacoes'  => $dados['observacoes'] ?? null,
        ]);

        // Actualizar stock do produto (em unidade de venda)
        $this->db->prepare("
            UPDATE produtos SET estoque_actual = estoque_actual + :qty WHERE id = :id
        ")->execute(['qty' => $qtdVenda, 'id' => $dados['produto_id']]);

        $obs = ($dados['observacoes'] ?? null);
        if ($fator > 1) {
            $obs = trim("$obs | {$qtdCompra} {$produto['unidade_compra']} × {$fator} = {$qtdVenda} {$produto['unidade_venda']}");
        }

        // Registar movimento
        $this->registarMovimento($dados['produto_id'], 'entrada', $qtdVenda, null, $obs);

        return (int) $this->db->lastInsertId();
    }

    // ----------------------------------------------------------------
    // Registar movimento de stock
    // ----------------------------------------------------------------
    public function registarMovimento(int $produtoId, string $tipo, int $qty, ?int $loteId = null, ?string $obs = null): void
    {
        $p = $this->findById($produtoId);
        if (!$p) return;

        $anterior  = (int)$p['estoque_actual'];
        $posterior = $tipo === 'entrada'
            ? $anterior + $qty
            : $anterior - $qty;

        $this->db->prepare("
            INSERT INTO estoque_movimentos
                (produto_id, lote_id, tipo, quantidade, quantidade_anterior, quantidade_posterior, usuario_id, observacoes)
            VALUES
                (:produto_id, :lote_id, :tipo, :quantidade, :anterior, :posterior, :usuario_id, :obs)
        ")->execute([
            'produto_id' => $produtoId,
            'lote_id'    => $loteId,
            'tipo'       => $tipo,
            'quantidade' => $qty,
            'anterior'   => $anterior,
            'posterior'  => $posterior,
            'usuario_id' => $_SESSION['usuario_id'] ?? null,
            'obs'        => $obs,
        ]);
    }

    // ----------------------------------------------------------------
    // Estatísticas para o dashboard do módulo
    // ----------------------------------------------------------------
    public function estatisticas(): array
    {
        $stmt = $this->db->query("
            SELECT
                COUNT(*)                              AS total,
                SUM(ativo = 1)                        AS activos,
                SUM(estoque_actual < estoque_min AND ativo = 1) AS stock_baixo,
                SUM(estoque_actual = 0 AND ativo = 1) AS sem_stock,
                SUM(requer_receita = 1 AND ativo = 1) AS com_receita,
                SUM(controlado = 1 AND ativo = 1)     AS controlados
            FROM produtos
        ");
        $est = $stmt->fetch();

        // Lotes a vencer em 90 dias
        $stmt2 = $this->db->query("SELECT COUNT(*) FROM vw_lotes_a_vencer");
        $est['lotes_a_vencer'] = (int) $stmt2->fetchColumn();

        return $est;
    }

    // ----------------------------------------------------------------
    // Verificar código de barras único
    // ----------------------------------------------------------------
    public function codigoBarrasExiste(string $cod, int $excluirId = 0): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM produtos
            WHERE codigo_barras = :cod AND id != :id
        ");
        $stmt->execute(['cod' => $cod, 'id' => $excluirId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    // ----------------------------------------------------------------
    // Alertas para a API
    // ----------------------------------------------------------------
    public function alertas(): array
    {
        $stockBaixo = $this->db->query("SELECT COUNT(*) FROM vw_produtos_stock_baixo")->fetchColumn();
        $aVencer    = $this->db->query("SELECT COUNT(*) FROM vw_lotes_a_vencer")->fetchColumn();
        return [
            'stock_baixo' => (int) $stockBaixo,
            'a_vencer'    => (int) $aVencer,
            'total'       => (int) $stockBaixo + (int) $aVencer,
        ];
    }
}
