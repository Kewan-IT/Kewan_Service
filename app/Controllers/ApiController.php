<?php
namespace App\Controllers;

class ApiController
{
    private function db(): \PDO
    {
        return \Core\Database::getInstance();
    }

    private function json(mixed $data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public function pesquisarProdutos(): void
    {
        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 1) { $this->json([]); }
        try {
            $stmt = $this->db()->prepare("
                SELECT
                    p.id,
                    p.nome,
                    p.preco_venda,
                    p.estoque_actual,
                    COALESCE(p.requer_receita, 0) AS requer_receita,
                    COALESCE(p.controlado, 0)     AS controlado,
                    p.unidade_medida,
                    COALESCE(c.nome, 'Sem Categoria') AS categoria_nome
                FROM produtos p
                LEFT JOIN categorias c ON c.id = p.categoria_id
                WHERE p.ativo = 1
                  AND (p.nome LIKE :q OR p.codigo_barras LIKE :q OR p.principio_ativo LIKE :q)
                ORDER BY p.nome ASC
                LIMIT 20
            ");
            $stmt->execute([':q' => '%' . $q . '%']);
            $this->json($stmt->fetchAll(\PDO::FETCH_ASSOC));
        } catch (\Throwable $e) {
            error_log('API pesquisarProdutos erro: ' . $e->getMessage());
            $this->json([]);
        }
    }

    public function pesquisarClientes(): void
    {
        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 1) { $this->json([]); }
        try {
            $stmt = $this->db()->prepare("
                SELECT id, nome, telefone, nuit, bi, email
                FROM clientes
                WHERE ativo = 1
                  AND (nome LIKE :q OR telefone LIKE :q OR nuit LIKE :q OR bi LIKE :q)
                ORDER BY nome ASC
                LIMIT 10
            ");
            $stmt->execute([':q' => '%' . $q . '%']);
            $this->json($stmt->fetchAll(\PDO::FETCH_ASSOC));
        } catch (\Throwable $e) {
            error_log('API pesquisarClientes erro: ' . $e->getMessage());
            $this->json([]);
        }
    }

    public function alertasEstoque(): void
    {
        try {
            $db = $this->db();
            $stockBaixo = (int) $db->query("SELECT COUNT(*) FROM produtos WHERE ativo=1 AND estoque_actual < estoque_min")->fetchColumn();
            $aVencer    = (int) $db->query("SELECT COUNT(*) FROM lotes WHERE quantidade > 0 AND validade BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY)")->fetchColumn();
            $this->json(['stock_baixo' => $stockBaixo, 'a_vencer' => $aVencer, 'total' => $stockBaixo + $aVencer]);
        } catch (\Throwable $e) {
            $this->json(['stock_baixo' => 0, 'a_vencer' => 0, 'total' => 0]);
        }
    }

    public function resumoDashboard(): void
    {
        try {
            $row = $this->db()->query("
                SELECT COUNT(*) AS total, COALESCE(SUM(total), 0) AS valor
                FROM vendas WHERE DATE(criado_em) = CURDATE() AND status = 'concluida'
            ")->fetch(\PDO::FETCH_ASSOC);
            $this->json(['vendas_hoje' => $row]);
        } catch (\Throwable $e) {
            $this->json(['vendas_hoje' => ['total' => 0, 'valor' => 0]]);
        }
    }
}
