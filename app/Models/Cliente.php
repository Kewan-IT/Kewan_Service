<?php

namespace App\Models;

use Core\BaseModel;

class Cliente extends BaseModel
{
    protected string $table = 'clientes';

    public function listar(string $pesquisa = '', string $status = '', string $tipo = '', int $page = 1, int $perPage = 20): array
    {
        $where  = ['1=1'];
        $params = [];

        if ($pesquisa !== '') {
            $where[]        = '(c.nome LIKE :pesq OR c.telefone LIKE :pesq OR c.nuit LIKE :pesq OR c.bi LIKE :pesq OR c.email LIKE :pesq OR c.nome_comercial LIKE :pesq OR c.pessoa_contacto LIKE :pesq)';
            $params['pesq'] = '%' . $pesquisa . '%';
        }
        if ($status === 'activo')   { $where[] = 'c.ativo = 1'; }
        if ($status === 'inactivo') { $where[] = 'c.ativo = 0'; }
        if ($tipo === 'singular')    { $where[] = "(c.tipo_cliente = 'singular' OR c.tipo_cliente IS NULL)"; }
        if ($tipo === 'instituicao') { $where[] = "c.tipo_cliente = 'instituicao'"; }

        $whereStr = implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;

        $stmtCount = $this->db->prepare("SELECT COUNT(*) FROM clientes c WHERE $whereStr");
        $stmtCount->execute($params);
        $total = (int) $stmtCount->fetchColumn();

        $stmtData = $this->db->prepare("
            SELECT c.*,
                COUNT(v.id)      AS total_compras,
                SUM(v.total)     AS total_gasto,
                MAX(v.criado_em) AS ultima_compra
            FROM clientes c
            LEFT JOIN vendas v ON v.cliente_id = c.id AND v.status = 'concluida'
            WHERE $whereStr
            GROUP BY c.id
            ORDER BY c.nome
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

    public function findComHistorico(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT c.*,
                COUNT(v.id)      AS total_compras,
                SUM(v.total)     AS total_gasto,
                MAX(v.criado_em) AS ultima_compra
            FROM clientes c
            LEFT JOIN vendas v ON v.cliente_id = c.id AND v.status = 'concluida'
            WHERE c.id = :id
            GROUP BY c.id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function vendas(int $clienteId, int $limit = 20): array
    {
        $stmt = $this->db->prepare("
            SELECT v.id, v.numero_venda, v.total, v.forma_pagamento,
                v.status, v.criado_em, u.nome AS atendido_por,
                COUNT(iv.id) AS total_itens
            FROM vendas v
            LEFT JOIN usuarios u     ON u.id = v.usuario_id
            LEFT JOIN itens_venda iv ON iv.venda_id = v.id
            WHERE v.cliente_id = :id
            GROUP BY v.id
            ORDER BY v.criado_em DESC
            LIMIT $limit
        ");
        $stmt->execute(['id' => $clienteId]);
        return $stmt->fetchAll();
    }

    public function produtosFavoritos(int $clienteId, int $limit = 5): array
    {
        $stmt = $this->db->prepare("
            SELECT p.nome, SUM(iv.quantidade) AS total_qty, SUM(iv.subtotal) AS total_gasto
            FROM itens_venda iv
            JOIN vendas v   ON v.id = iv.venda_id AND v.cliente_id = :id AND v.status = 'concluida'
            JOIN produtos p ON p.id = iv.produto_id
            GROUP BY p.id
            ORDER BY total_qty DESC
            LIMIT $limit
        ");
        $stmt->execute(['id' => $clienteId]);
        return $stmt->fetchAll();
    }

    public function estatisticas(): array
    {
        $est = $this->db->query("
            SELECT
                COUNT(*)                                           AS total,
                SUM(ativo=1)                                       AS activos,
                SUM(ativo=0)                                       AS inactivos,
                SUM(tipo_cliente='singular'  OR tipo_cliente IS NULL) AS total_singular,
                SUM(tipo_cliente='instituicao')                    AS total_instituicao
            FROM clientes
        ")->fetch();
        $est['novos_mes'] = (int) $this->db->query("
            SELECT COUNT(*) FROM clientes
            WHERE MONTH(criado_em)=MONTH(CURDATE()) AND YEAR(criado_em)=YEAR(CURDATE())
        ")->fetchColumn();
        return $est;
    }

    public function pesquisarParaVenda(string $q, int $limit = 8): array
    {
        $stmt = $this->db->prepare("
            SELECT id, nome, telefone, nuit, bi, email, tipo_cliente
            FROM clientes
            WHERE ativo = 1
              AND (nome LIKE :q OR telefone LIKE :q OR nuit LIKE :q OR bi LIKE :q OR nome_comercial LIKE :q)
            ORDER BY nome LIMIT $limit
        ");
        $stmt->execute(['q' => '%' . $q . '%']);
        return $stmt->fetchAll();
    }

    public function nuitExiste(string $nuit, int $excluirId = 0): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM clientes WHERE nuit=:nuit AND id!=:id");
        $stmt->execute(['nuit' => $nuit, 'id' => $excluirId]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
