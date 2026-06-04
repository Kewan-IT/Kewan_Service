<?php

namespace App\Controllers;

use App\Models\Produto;
use App\Models\Cliente;
use App\Models\Venda;
use App\Middleware\AuthMiddleware;
use Core\View;

class ApiController
{
    public function __construct()
    {
        AuthMiddleware::check();
    }

    // GET /api/produtos/pesquisar?q=…
    public function pesquisarProdutos(): void
    {
        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 2) {
            View::json([]);
            return;
        }
        $model    = new Produto();
        $produtos = $model->pesquisarParaVenda($q, 12);
        View::json($produtos);
    }

    // POST /api/produtos/inline
    public function criarProdutoCompra(): void
    {
        $this->verificarCsrf();

        $payload = json_decode(file_get_contents('php://input') ?: '[]', true);
        if (!is_array($payload)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Payload inválido.']);
            exit;
        }

        $produtoModel = new Produto();
        $nome = trim((string)($payload['nome'] ?? ''));
        $categoriaId = (int)($payload['categoria_id'] ?? 0);
        $codigoBarras = trim((string)($payload['codigo_barras'] ?? '')) ?: null;
        $unidadeMedida = trim((string)($payload['unidade_medida'] ?? 'unidade')) ?: 'unidade';
        $precoCompra = (float) str_replace(',', '.', (string)($payload['preco_compra'] ?? '0'));
        $precoVenda = (float) str_replace(',', '.', (string)($payload['preco_venda'] ?? '0'));
        $estoqueMin = max(0, (int)($payload['estoque_min'] ?? 5));

        $erros = [];
        if (mb_strlen($nome) < 2) {
            $erros['nome'] = 'Nome obrigatório (mínimo 2 caracteres).';
        }
        if ($categoriaId <= 0) {
            $erros['categoria_id'] = 'Selecione uma categoria.';
        }
        if ($precoVenda <= 0) {
            $erros['preco_venda'] = 'Preço de venda deve ser maior que zero.';
        }
        if ($precoCompra < 0) {
            $erros['preco_compra'] = 'Preço de compra inválido.';
        }
        if ($codigoBarras !== null && $produtoModel->codigoBarrasExiste($codigoBarras)) {
            $erros['codigo_barras'] = 'Este código de barras já existe.';
        }

        if ($erros) {
            http_response_code(422);
            echo json_encode(['erro' => 'Dados inválidos.', 'errors' => $erros]);
            exit;
        }

        $produtoId = $produtoModel->insert([
            'nome'           => $nome,
            'codigo_barras'  => $codigoBarras,
            'principio_ativo'=> trim((string)($payload['principio_ativo'] ?? '')) ?: null,
            'descricao'      => trim((string)($payload['descricao'] ?? '')) ?: null,
            'categoria_id'   => $categoriaId,
            'fornecedor_id'  => null,
            'unidade_medida' => $unidadeMedida,
            'preco_compra'   => $precoCompra,
            'preco_venda'    => $precoVenda,
            'estoque_actual' => 0,
            'estoque_min'    => $estoqueMin,
            'requer_receita' => 0,
            'controlado'     => 0,
            'ativo'          => 1,
            'imagem_url'     => null,
        ]);

        $produto = $produtoModel->findById($produtoId);
        View::json(['success' => true, 'produto' => $produto]);
    }

    // GET /api/clientes/pesquisar?q=…
    public function pesquisarClientes(): void
    {
        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 2) {
            View::json([]);
            return;
        }
        $db   = \Core\Database::getInstance();
        $stmt = $db->prepare("
            SELECT id, nome, telefone, nuit
            FROM clientes
            WHERE ativo = 1
              AND (nome LIKE :q1 OR telefone LIKE :q2 OR nuit LIKE :q3)
            ORDER BY nome LIMIT 10
        ");
        $stmt->execute([
            'q1' => '%' . $q . '%',
            'q2' => '%' . $q . '%',
            'q3' => '%' . $q . '%',
        ]);
        View::json($stmt->fetchAll());
    }

    private function verificarCsrf(): void
    {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            echo json_encode(['erro' => 'Token inválido.']);
            exit;
        }
    }

    // GET /api/produtos/{id}/lotes
    // Devolve lotes válidos (não vencidos, quantidade > 0) do produto
    // ordenados por FEFO — usado pelo balcão para mostrar info do lote
    public function lotesDosProduto(string $id): void
    {
        $produtoId = (int)$id;
        $db = \Core\Database::getInstance();

        $stmt = $db->prepare("
            SELECT l.id, l.numero_lote, l.validade, l.quantidade,
                   DATEDIFF(l.validade, CURDATE()) AS dias_para_vencer,
                   CASE
                     WHEN DATEDIFF(l.validade, CURDATE()) <= 30 THEN 'critico'
                     WHEN DATEDIFF(l.validade, CURDATE()) <= 60 THEN 'atencao'
                     WHEN DATEDIFF(l.validade, CURDATE()) <= 90 THEN 'aviso'
                     ELSE 'ok'
                   END AS status_validade
            FROM lotes l
            WHERE l.produto_id = :pid
              AND l.quantidade > 0
              AND l.validade >= CURDATE()
            ORDER BY l.validade ASC
        ");
        $stmt->execute(['pid' => $produtoId]);
        View::json($stmt->fetchAll());
    }

    // GET /api/lotes/alertas?dias=30
    // Lotes a vencer nos próximos N dias
    public function alertasLotes(): void
    {
        $dias  = max(1, (int)($_GET['dias'] ?? 30));
        $model = new Venda();
        $data  = [
            'a_vencer' => $model->alertasLotesAVencer($dias),
            'vencidos' => $model->lotesVencidos(),
            'dias'     => $dias,
        ];
        View::json($data);
    }

    // GET /api/estoque/alertas
    public function alertasEstoque(): void
    {
        $model = new Produto();
        View::json($model->alertas());
    }

    // GET /api/dashboard/resumo
    public function resumoDashboard(): void
    {
        $db = \Core\Database::getInstance();

        $vendas_hoje = $db->query("
            SELECT COUNT(*) as total, COALESCE(SUM(total),0) as valor
            FROM vendas
            WHERE DATE(criado_em) = CURDATE() AND status = 'concluida'
        ")->fetch();

        $stock_baixo = $db->query("
            SELECT COUNT(*) as total FROM produtos
            WHERE ativo = 1 AND estoque_actual <= estoque_min
        ")->fetchColumn();

        // Alertas de lotes
        $lotes_criticos = $db->query("
            SELECT COUNT(*) FROM lotes
            WHERE quantidade > 0
              AND validade >= CURDATE()
              AND validade <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        ")->fetchColumn();

        $lotes_vencidos = $db->query("
            SELECT COUNT(*) FROM lotes
            WHERE quantidade > 0 AND validade < CURDATE()
        ")->fetchColumn();

        View::json([
            'vendas_hoje'    => $vendas_hoje,
            'stock_baixo'    => $stock_baixo,
            'lotes_criticos' => (int)$lotes_criticos,
            'lotes_vencidos' => (int)$lotes_vencidos,
        ]);
    }

    public function alertasStock(): void
    {
        $this->alertasEstoque();
    }
}
