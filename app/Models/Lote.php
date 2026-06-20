<?php
namespace App\Models;

use Core\BaseModel;

class Lote extends BaseModel
{
    protected string $table = 'lotes';

    // ----------------------------------------------------------------
    // Lote com dados do produto (para validações e PDF)
    // ----------------------------------------------------------------
    public function comProduto(int $loteId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT l.*, p.nome AS produto_nome, p.unidade_medida, p.preco_venda,
                   p.preco_compra, p.fornecedor_id, f.nome AS fornecedor_nome
            FROM lotes l
            JOIN produtos p          ON p.id = l.produto_id
            LEFT JOIN fornecedores f ON f.id = p.fornecedor_id
            WHERE l.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $loteId]);
        return $stmt->fetch() ?: null;
    }

    // ----------------------------------------------------------------
    // Activar promoção — preço definido manualmente pelo utilizador,
    // válido apenas para este lote específico
    // ----------------------------------------------------------------
    public function ativarPromocao(int $loteId, float $precoPromocional, ?string $motivo, int $usuarioId): bool
    {
        return $this->update($loteId, [
            'em_promocao'         => 1,
            'preco_promocional'   => $precoPromocional,
            'promocao_motivo'     => $motivo,
            'promocao_usuario_id' => $usuarioId,
            'promocao_criado_em'  => date('Y-m-d H:i:s'),
        ]);
    }

    // ----------------------------------------------------------------
    // Cancelar promoção do lote
    // ----------------------------------------------------------------
    public function cancelarPromocao(int $loteId): bool
    {
        return $this->update($loteId, [
            'em_promocao'         => 0,
            'preco_promocional'   => null,
            'promocao_motivo'     => null,
            'promocao_usuario_id' => null,
            'promocao_criado_em'  => null,
        ]);
    }

    // ----------------------------------------------------------------
    // Gerar número sequencial  ex: DEV-2025-00001
    // ----------------------------------------------------------------
    public function gerarNumeroDevolucao(): string
    {
        $ano  = date('Y');
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM devolucoes_fornecedor WHERE YEAR(criado_em) = :ano");
        $stmt->execute(['ano' => $ano]);
        $seq = (int) $stmt->fetchColumn() + 1;
        return sprintf('DEV-%s-%05d', $ano, $seq);
    }

    // ----------------------------------------------------------------
    // Registar devolução ao fornecedor de uma quantidade de um lote
    // específico: baixa a quantidade do lote, o stock do produto,
    // grava o movimento de stock e cria o registo de devolução.
    // ----------------------------------------------------------------
    public function registarDevolucao(array $dados): int
    {
        $this->db->beginTransaction();
        try {
            $lote = $this->comProduto((int)$dados['lote_id']);
            if (!$lote) {
                throw new \RuntimeException('Lote não encontrado.');
            }

            $qtd = (int)$dados['quantidade'];
            if ($qtd <= 0 || $qtd > (int)$lote['quantidade']) {
                throw new \RuntimeException('Quantidade de devolução inválida para este lote.');
            }

            $numero = $this->gerarNumeroDevolucao();

            // 1. Inserir registo de devolução
            $stmtDev = $this->db->prepare("
                INSERT INTO devolucoes_fornecedor
                    (numero_devolucao, lote_id, produto_id, fornecedor_id, numero_lote,
                     quantidade, motivo, observacoes, valor_unitario, usuario_id)
                VALUES
                    (:numero_devolucao, :lote_id, :produto_id, :fornecedor_id, :numero_lote,
                     :quantidade, :motivo, :observacoes, :valor_unitario, :usuario_id)
            ");
            $stmtDev->execute([
                'numero_devolucao' => $numero,
                'lote_id'          => $lote['id'],
                'produto_id'       => $lote['produto_id'],
                'fornecedor_id'    => $lote['fornecedor_id'],
                'numero_lote'      => $lote['numero_lote'],
                'quantidade'       => $qtd,
                'motivo'           => $dados['motivo'] ?? 'validade',
                'observacoes'      => $dados['observacoes'] ?? null,
                'valor_unitario'   => $lote['preco_compra'],
                'usuario_id'       => $dados['usuario_id'],
            ]);
            $devolucaoId = (int) $this->db->lastInsertId();

            // 2. Baixar a quantidade do lote
            $this->db->prepare("
                UPDATE lotes SET quantidade = quantidade - :qty
                WHERE id = :id AND quantidade >= :qty2
            ")->execute(['qty' => $qtd, 'qty2' => $qtd, 'id' => $lote['id']]);

            // 3. Baixar o stock global do produto
            $this->db->prepare("
                UPDATE produtos SET estoque_actual = estoque_actual - :qty
                WHERE id = :id AND estoque_actual >= :qty2
            ")->execute(['qty' => $qtd, 'qty2' => $qtd, 'id' => $lote['produto_id']]);

            // 4. Movimento de stock rastreável
            $this->db->prepare("
                INSERT INTO movimentos_stock
                    (produto_id, lote_id, tipo, quantidade, referencia, usuario_id, observacoes)
                VALUES
                    (:produto_id, :lote_id, 'saida', :quantidade, :referencia, :usuario_id, :observacoes)
            ")->execute([
                'produto_id'  => $lote['produto_id'],
                'lote_id'     => $lote['id'],
                'quantidade'  => $qtd,
                'referencia'  => $numero,
                'usuario_id'  => $dados['usuario_id'],
                'observacoes' => 'Devolução ao fornecedor — ' . $numero,
            ]);

            // 5. Se o lote esgotou, cancelar promoção residual
            $restante = (int)$lote['quantidade'] - $qtd;
            if ($restante <= 0) {
                $this->cancelarPromocao($lote['id']);
            }

            $this->db->commit();
            return $devolucaoId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ----------------------------------------------------------------
    // Devolução completa (para o PDF/recibo)
    // ----------------------------------------------------------------
    public function devolucaoCompleta(int $devolucaoId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT d.*, p.nome AS produto_nome, p.unidade_medida,
                   f.nome AS fornecedor_nome, f.telefone AS fornecedor_telefone,
                   u.nome AS usuario_nome
            FROM devolucoes_fornecedor d
            JOIN produtos p          ON p.id = d.produto_id
            LEFT JOIN fornecedores f ON f.id = d.fornecedor_id
            LEFT JOIN usuarios u     ON u.id = d.usuario_id
            WHERE d.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $devolucaoId]);
        return $stmt->fetch() ?: null;
    }
}
