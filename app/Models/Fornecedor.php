<?php

namespace App\Models;

use Core\BaseModel;
use PDO;

class Fornecedor extends BaseModel
{
    protected string $table = 'fornecedores';

    public function listar(string $q = '', string $status = '', int $page = 1, int $perPage = 20): array
    {
        $where  = ['1=1'];
        $params = [];

        if ($q !== '') {
            $where[]      = '(nome LIKE :q1 OR nuit LIKE :q2 OR telefone LIKE :q3 OR cidade LIKE :q4)';
            $params['q1'] = "%$q%";
            $params['q2'] = "%$q%";
            $params['q3'] = "%$q%";
            $params['q4'] = "%$q%";
        }
        if ($status === 'activo')   { $where[] = 'ativo = 1'; }
        if ($status === 'inactivo') { $where[] = 'ativo = 0'; }

        $whereStr = implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;

        $total = $this->db->prepare("SELECT COUNT(*) FROM fornecedores WHERE $whereStr");
        $total->execute($params);

        $stmt = $this->db->prepare("
            SELECT * FROM fornecedores
            WHERE $whereStr
            ORDER BY nome ASC
            LIMIT $perPage OFFSET $offset
        ");
        $stmt->execute($params);

        return [
            'data'         => $stmt->fetchAll(),
            'total'        => (int)$total->fetchColumn(),
            'current_page' => $page,
            'last_page'    => max(1, (int)ceil($total->fetchColumn() / $perPage)),
            'per_page'     => $perPage,
        ];
    }

    public function estatisticas(): array
    {
        return $this->db->query("
            SELECT
                COUNT(*) AS total,
                SUM(ativo = 1) AS activos,
                SUM(ativo = 0) AS inactivos,
                SUM(criado_em >= DATE_SUB(NOW(), INTERVAL 30 DAY)) AS novos_mes
            FROM fornecedores
        ")->fetch();
    }

    public function nuitExiste(string $nuit, int $excluirId = 0): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM fornecedores WHERE nuit = :nuit AND id != :id LIMIT 1");
        $stmt->execute(['nuit' => $nuit, 'id' => $excluirId]);
        return (bool)$stmt->fetchColumn();
    }

    public function compras(int $id, int $limite = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT id, numero_compra, data_pedido, status, total
            FROM compras
            WHERE fornecedor_id = :id
            ORDER BY id DESC
            LIMIT $limite
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll();
    }
}
