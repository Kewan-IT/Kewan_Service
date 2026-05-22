<?php

namespace App\Models;

use Core\BaseModel;

class Categoria extends BaseModel
{
    protected string $table = 'categorias';

    // ----------------------------------------------------------------
    // Todas as categorias com contagem de produtos e info do pai
    // ----------------------------------------------------------------
    public function listarComContagem(): array
    {
        $stmt = $this->db->query("
            SELECT
                c.*,
                cp.nome AS pai_nome,
                COUNT(p.id) AS total_produtos
            FROM categorias c
            LEFT JOIN categorias cp ON cp.id = c.categoria_pai_id
            LEFT JOIN produtos p    ON p.categoria_id = c.id AND p.ativo = 1
            GROUP BY c.id
            ORDER BY COALESCE(cp.nome, c.nome), c.nome
        ");
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------------
    // Categorias pai (sem pai) activas — para selects
    // ----------------------------------------------------------------
    public function categoriasPai(): array
    {
        $stmt = $this->db->query("
            SELECT * FROM categorias
            WHERE categoria_pai_id IS NULL AND ativo = 1
            ORDER BY nome
        ");
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------------
    // Subcategorias de um pai — para selects encadeados
    // ----------------------------------------------------------------
    public function subcategorias(int $paiId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM categorias
            WHERE categoria_pai_id = :pai AND ativo = 1
            ORDER BY nome
        ");
        $stmt->execute(['pai' => $paiId]);
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------------
    // Árvore completa para selects (pai > filho)
    // ----------------------------------------------------------------
    public function arvore(): array
    {
        $stmt = $this->db->query("
            SELECT c.*, cp.nome AS pai_nome
            FROM categorias c
            LEFT JOIN categorias cp ON cp.id = c.categoria_pai_id
            WHERE c.ativo = 1
            ORDER BY COALESCE(cp.nome, c.nome), c.nome
        ");
        return $stmt->fetchAll();
    }
}
