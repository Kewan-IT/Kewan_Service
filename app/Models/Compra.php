<?php
 
namespace App\Models;
 
use Core\BaseModel;
use PDO;
 
class Compra extends BaseModel
{
    protected string $table = 'compras';
 
    // ----------------------------------------------------------------
    // Gerar número sequencial CP-2025-00001
    // ----------------------------------------------------------------
    public function gerarNumero(): string
    {
        $ano     = date('Y');
        $prefixo = 'CP';
        $stmt    = $this->db->prepare("
            SELECT numero_compra FROM compras
            WHERE numero_compra LIKE :p
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute(['p' => "$prefixo-$ano-%"]);
        $ultimo = $stmt->fetchColumn();
        $seq    = $ultimo ? (int)substr($ultimo, -5) + 1 : 1;
        return sprintf("%s-%s-%05d", $prefixo, $ano, $seq);
    }
 
    // ----------------------------------------------------------------
    // Criar compra completa com itens (transação)
    // ----------------------------------------------------------------
    public function criar(array $cabecalho, array $itens): int
    {
        $this->db->beginTransaction();
        try {
            $cabecalho['numero_compra'] = $this->gerarNumero();
            $cabecalho['usuario_id']    = $_SESSION['usuario_id'] ?? 1;
 
            // Calcular totais
            $subtotal = array_sum(array_column($itens, 'subtotal'));
            $desconto = (float)($cabecalho['desconto'] ?? 0);
            $cabecalho['subtotal'] = $subtotal;
            $cabecalho['total']    = max(0, $subtotal - $desconto);
 
            $compraId = $this->insert($cabecalho);
 
            $stmtItem = $this->db->prepare("
                INSERT INTO itens_compra
                    (compra_id, produto_id, quantidade, preco_unitario, subtotal, numero_lote, validade_lote)
                VALUES
                    (:compra_id, :produto_id, :quantidade, :preco_unitario, :subtotal, :numero_lote, :validade_lote)
            ");
 
            foreach ($itens as $item) {
                $stmtItem->execute([
                    'compra_id'      => $compraId,
                    'produto_id'     => $item['produto_id'],
                    'quantidade'     => $item['quantidade'],
                    'preco_unitario' => $item['preco_unitario'],
                    'subtotal'       => $item['subtotal'],
                    'numero_lote'    => $item['numero_lote']    ?? null,
                    'validade_lote'  => $item['validade_lote']  ?? null,
                ]);
            }
 
            $this->db->commit();
            return $compraId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
 
    // ----------------------------------------------------------------
    // Receber mercadoria (parcial ou total) — actualiza stock
    // ----------------------------------------------------------------
    public function receberMercadoria(int $compraId, array $recebimentos): void
    {
        $this->db->beginTransaction();
        try {
            $stmtUpdate = $this->db->prepare("
                UPDATE itens_compra
                SET quantidade_recebida = quantidade_recebida + :qty
                WHERE id = :id AND compra_id = :compra_id
            ");
 
            $stmtLote = $this->db->prepare("
                INSERT INTO lotes (produto_id, numero_lote, quantidade, validade, data_entrada, observacoes)
                VALUES (:produto_id, :numero_lote, :quantidade, :validade, :data_entrada, :observacoes)
                ON DUPLICATE KEY UPDATE
                    quantidade = quantidade + VALUES(quantidade)
            ");
 
            $stmtStock = $this->db->prepare("
                UPDATE produtos SET estoque_actual = estoque_actual + :qty WHERE id = :id
            ");
 
            $stmtMov = $this->db->prepare("
                INSERT INTO movimentos_stock (produto_id, tipo, quantidade, referencia, usuario_id, observacoes)
                VALUES (:produto_id, 'entrada', :quantidade, :referencia, :usuario_id, :observacoes)
            ");
 
            $compra = $this->findById($compraId);

            foreach ($recebimentos as $r) {
                $qtdCompra = (int)$r['quantidade_receber'];
                if ($qtdCompra <= 0) continue;

                // Obter factor de conversão do produto
                $stmtProd = $this->db->prepare("SELECT fator_conversao, unidade_compra, unidade_venda FROM produtos WHERE id = :id");
                $stmtProd->execute(['id' => $r['produto_id']]);
                $prod  = $stmtProd->fetch();
                $fator = (float)($prod['fator_conversao'] ?? 1);
                if ($fator <= 0) $fator = 1;

                // Converter: ex. 10 caixas × 10 cartelas/caixa = 100 cartelas em stock
                $qtdVenda = (int)round($qtdCompra * $fator);

                $obsLote = 'Compra #' . $compraId;
                if ($fator > 1) {
                    $obsLote .= " | {$qtdCompra} {$prod['unidade_compra']} × {$fator} = {$qtdVenda} {$prod['unidade_venda']}";
                }

                // Actualizar quantidade recebida no item (em unidade de compra)
                $stmtUpdate->execute([
                    'qty'       => $qtdCompra,
                    'id'        => $r['item_id'],
                    'compra_id' => $compraId,
                ]);

                // Criar/actualizar lote com stock em unidade de venda
                $lote     = $r['numero_lote']   ?? ('LT-' . date('Ymd') . '-' . $r['produto_id']);
                $validade = $r['validade_lote']  ?? null;

                $stmtLote->execute([
                    'produto_id'   => $r['produto_id'],
                    'numero_lote'  => $lote,
                    'quantidade'   => $qtdVenda,
                    'validade'     => $validade,
                    'data_entrada' => date('Y-m-d'),
                    'observacoes'  => $obsLote,
                ]);

                // Actualizar stock do produto (em unidade de venda)
                $stmtStock->execute(['qty' => $qtdVenda, 'id' => $r['produto_id']]);

                // Registar movimento
                $stmtMov->execute([
                    'produto_id'  => $r['produto_id'],
                    'quantidade'  => $qtdVenda,
                    'referencia'  => $compra['numero_compra'] ?? "CP-$compraId",
                    'usuario_id'  => $_SESSION['usuario_id'] ?? 1,
                    'observacoes' => $obsLote,
                ]);
            }
 
            // Actualizar status da compra
            $this->actualizarStatus($compraId);
 
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
 
    // ----------------------------------------------------------------
    // Actualizar status automaticamente
    // ----------------------------------------------------------------
    private function actualizarStatus(int $compraId): void
    {
        $stmt = $this->db->prepare("
            SELECT
                SUM(quantidade) AS total_pedido,
                SUM(quantidade_recebida) AS total_recebido
            FROM itens_compra
            WHERE compra_id = :id
        ");
        $stmt->execute(['id' => $compraId]);
        $row = $stmt->fetch();
 
        $novoStatus = 'enviada';
        if ($row['total_recebido'] >= $row['total_pedido']) {
            $novoStatus = 'recebida';
        } elseif ($row['total_recebido'] > 0) {
            $novoStatus = 'parcialmente_recebida';
        }
 
        $this->db->prepare("UPDATE compras SET status = :s WHERE id = :id")
            ->execute(['s' => $novoStatus, 'id' => $compraId]);
    }
 
    // ----------------------------------------------------------------
    // Cancelar compra
    // ----------------------------------------------------------------
    public function cancelar(int $id): void
    {
        $this->db->prepare("UPDATE compras SET status = 'cancelada' WHERE id = :id AND status IN ('rascunho','enviada')")
            ->execute(['id' => $id]);
    }
 
    // ----------------------------------------------------------------
    // Listagem paginada com filtros
    // ----------------------------------------------------------------
    public function listar(string $q = '', string $status = '', int $page = 1, int $perPage = 20): array
    {
        $where  = ['1=1'];
        $params = [];
 
        if ($q !== '') {
            $where[]        = '(c.numero_compra LIKE :q1 OR f.nome LIKE :q2)';
            $params['q1']   = "%$q%";
            $params['q2']   = "%$q%";
        }
        if ($status !== '') {
            $where[]          = 'c.status = :status';
            $params['status'] = $status;
        }
 
        $whereStr = implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;
 
        $total = $this->db->prepare("
            SELECT COUNT(*) FROM compras c
            JOIN fornecedores f ON f.id = c.fornecedor_id
            WHERE $whereStr
        ");
        $total->execute($params);
        $totalRows = (int)$total->fetchColumn();
 
        $stmt = $this->db->prepare("
            SELECT c.*, f.nome AS fornecedor_nome
            FROM compras c
            JOIN fornecedores f ON f.id = c.fornecedor_id
            WHERE $whereStr
            ORDER BY c.id DESC
            LIMIT $perPage OFFSET $offset
        ");
        $stmt->execute($params);
 
        return [
            'data'         => $stmt->fetchAll(),
            'total'        => $totalRows,
            'current_page' => $page,
            'last_page'    => max(1, (int)ceil($totalRows / $perPage)),
            'per_page'     => $perPage,
        ];
    }
 
    // ----------------------------------------------------------------
    // Detalhe completo com itens
    // ----------------------------------------------------------------
    public function findComItens(int $id): ?array
    {
        $compra = $this->db->prepare("
            SELECT c.*, f.nome AS fornecedor_nome, f.telefone AS fornecedor_telefone,
                   f.email AS fornecedor_email,
                   u.nome AS usuario_nome
            FROM compras c
            JOIN fornecedores f ON f.id = c.fornecedor_id
            JOIN usuarios u     ON u.id  = c.usuario_id
            WHERE c.id = :id
        ");
        $compra->execute(['id' => $id]);
        $row = $compra->fetch();
        if (!$row) return null;
 
        $itens = $this->db->prepare("
            SELECT ic.*, p.nome AS produto_nome, p.unidade_medida
            FROM itens_compra ic
            JOIN produtos p ON p.id = ic.produto_id
            WHERE ic.compra_id = :id
            ORDER BY ic.id
        ");
        $itens->execute(['id' => $id]);
        $row['itens'] = $itens->fetchAll();
 
        return $row;
    }
 
    // ----------------------------------------------------------------
    // Estatísticas para o topo da listagem
    // ----------------------------------------------------------------
    public function estatisticas(): array
    {
        return $this->db->query("
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'rascunho'               THEN 1 ELSE 0 END) AS rascunhos,
                SUM(CASE WHEN status = 'enviada'                THEN 1 ELSE 0 END) AS enviadas,
                SUM(CASE WHEN status IN ('parcialmente_recebida','recebida') THEN 1 ELSE 0 END) AS recebidas,
                COALESCE(SUM(CASE WHEN MONTH(data_pedido) = MONTH(CURDATE()) AND YEAR(data_pedido) = YEAR(CURDATE()) THEN total ELSE 0 END),0) AS valor_mes
            FROM compras
            WHERE status != 'cancelada'
        ")->fetch();
    }
 
    // ----------------------------------------------------------------
    // Todos os fornecedores activos (para selects)
    // ----------------------------------------------------------------
    public function fornecedores(): array
    {
        return $this->db->query("SELECT id, nome, telefone, email FROM fornecedores WHERE ativo = 1 ORDER BY nome")
            ->fetchAll();
    }
}
 
