<?php
namespace App\Controllers;

use App\Models\Venda;
use App\Models\Produto;
use App\Models\Cliente;
use App\Middleware\AuthMiddleware;
use Core\Database;
use Core\View;

class VendaController
{
    private Venda   $venda;
    private Produto $produto;
    private Cliente $cliente;

    public function __construct()
    {
        AuthMiddleware::check();
        $this->venda   = new Venda();
        $this->produto = new Produto();
        $this->cliente = new Cliente();
    }

    // ────────────────────────────────────────────────────────
    // GET /vendas
    // ────────────────────────────────────────────────────────
    public function index(): void
    {
        $f = [
            'busca'          => trim($_GET['busca']           ?? ''),
            'status'         => $_GET['status']               ?? '',
            'forma_pagamento'=> $_GET['forma_pagamento']      ?? '',
            'data_inicio'    => $_GET['data_inicio']          ?? date('Y-m-01'),
            'data_fim'       => $_GET['data_fim']             ?? date('Y-m-d'),
        ];

        View::render('vendas.index', [
            'titulo'       => 'Vendas',
            'activePage'   => 'vendas',
            'breadcrumb'   => ['Vendas' => null],
            'vendas'       => $this->venda->listar($f),
            'resumo'       => $this->venda->resumoHoje(),
            'filtros'      => $f,
            'flash_sucesso'=> $_SESSION['flash_sucesso'] ?? null,
            'flash_erro'   => $_SESSION['flash_erro']    ?? null,
        ]);
        unset($_SESSION['flash_sucesso'], $_SESSION['flash_erro']);
    }

    // ────────────────────────────────────────────────────────
    // GET /vendas/nova
    // Suporta: ?q=  ?qc=  ?cliente_id=  ?limpar=1
    // ────────────────────────────────────────────────────────
    public function create(): void
    {
        $appUrl = $_ENV['APP_URL'] ?? '';

        // Limpar carrinho da sessão
        if (isset($_GET['limpar'])) {
            unset($_SESSION['carrinho_balcao']);
            header('Location: ' . $appUrl . '/vendas/nova');
            exit;
        }

        $q         = trim($_GET['q']          ?? '');
        $qc        = trim($_GET['qc']         ?? '');
        $clienteId = (int)($_GET['cliente_id'] ?? 0);

        // ── Pesquisa de produtos (server-side) ───────────────
        $produtos = [];
        if ($q !== '') {
            $stmt = Database::getInstance()->prepare("
                SELECT p.id, p.nome, p.codigo_barras, p.principio_ativo,
                       p.preco_venda, p.estoque_actual, p.unidade_medida,
                       p.requer_receita, p.controlado,
                       c.nome AS categoria_nome
                FROM produtos p
                JOIN categorias c ON c.id = p.categoria_id
                WHERE p.ativo = 1
                  AND (
                        p.nome            LIKE :q
                     OR p.codigo_barras   = :exacto
                     OR p.principio_ativo LIKE :q
                  )
                ORDER BY
                    CASE WHEN p.codigo_barras = :exacto THEN 0 ELSE 1 END,
                    p.nome ASC
                LIMIT 20
            ");
            $stmt->execute(['q' => "%$q%", 'exacto' => $q]);
            $produtos = $stmt->fetchAll();
        }

        // ── Pesquisa de clientes (server-side) ───────────────
        $clientes = [];
        if ($qc !== '') {
            $stmt = Database::getInstance()->prepare("
                SELECT id, nome, telefone, nuit
                FROM clientes
                WHERE ativo = 1
                  AND (nome LIKE :qc OR telefone LIKE :qc OR nuit LIKE :qc)
                ORDER BY nome ASC
                LIMIT 10
            ");
            $stmt->execute(['qc' => "%$qc%"]);
            $clientes = $stmt->fetchAll();
        }

        // ── Cliente pré-seleccionado ─────────────────────────
        $cp = null;
        if ($clienteId > 0) {
            $stmt = Database::getInstance()->prepare(
                "SELECT id, nome, telefone, nuit FROM clientes WHERE id = :id LIMIT 1"
            );
            $stmt->execute(['id' => $clienteId]);
            $cp = $stmt->fetch() ?: null;
        }

        // ── Carrinho guardado na sessão ──────────────────────
        $carrinhoSessao = $_SESSION['carrinho_balcao'] ?? [];

        View::render('vendas.nova', [
            'titulo'         => 'Nova Venda',
            'activePage'     => 'venda-nova',
            'breadcrumb'     => [
                'Vendas'     => $appUrl . '/vendas',
                'Nova Venda' => null,
            ],
            'q'              => $q,
            'qc'             => $qc,
            'produtos'       => $produtos,
            'clientes'       => $clientes,
            'cp'             => $cp,
            'carrinhoSessao' => $carrinhoSessao,
            'csrf_token'     => $_SESSION['csrf_token'] ?? '',
            'flash_sucesso'  => $_SESSION['flash_sucesso'] ?? null,
            'flash_erro'     => $_SESSION['flash_erro']    ?? null,
        ]);
        unset($_SESSION['flash_sucesso'], $_SESSION['flash_erro']);
    }

    // ────────────────────────────────────────────────────────
    // POST /vendas/nova/carrinho
    // Guarda o carrinho JS na sessão PHP (chamado por fetch silencioso)
    // ────────────────────────────────────────────────────────
    public function salvarCarrinho(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $body  = json_decode(file_get_contents('php://input'), true);
        $token = $body['csrf'] ?? '';

        if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'erro' => 'Token inválido']);
            exit;
        }

        $carrinho = $body['carrinho'] ?? [];

        // Validar estrutura mínima de cada item
        $carrinho = array_filter($carrinho, fn($i) =>
            isset($i['id'], $i['qty'], $i['preco']) && (int)$i['qty'] > 0
        );

        $_SESSION['carrinho_balcao'] = array_values($carrinho);
        echo json_encode(['ok' => true, 'n' => count($carrinho)]);
        exit;
    }

    // ────────────────────────────────────────────────────────
    // POST /vendas/nova
    // Recebe o formulário oculto e cria a venda
    // ────────────────────────────────────────────────────────
    public function store(): void
    {
        $this->csrf();

        $appUrl    = $_ENV['APP_URL'] ?? '';
        $itensJson = $_POST['itens_json'] ?? '[]';
        $itens     = json_decode($itensJson, true);

        if (!is_array($itens) || empty($itens)) {
            $_SESSION['flash_erro'] = 'O carrinho está vazio. Adicione produtos antes de finalizar.';
            header('Location: ' . $appUrl . '/vendas/nova');
            exit;
        }

        // Validar cada item contra a BD (segurança + preço actualizado)
        $itensValidos = [];
        foreach ($itens as $raw) {
            $prod = $this->produto->findById((int)($raw['produto_id'] ?? $raw['id'] ?? 0));
            if (!$prod || !$prod['ativo']) continue;

            $qty      = max(1, (int)($raw['quantidade'] ?? $raw['qty'] ?? 1));
            $descItem = max(0.0, (float)($raw['desconto_item'] ?? 0));

            if ($prod['estoque_actual'] < $qty) {
                $_SESSION['flash_erro'] =
                    "Stock insuficiente para \"{$prod['nome']}\". Disponível: {$prod['estoque_actual']} unidade(s).";
                header('Location: ' . $appUrl . '/vendas/nova');
                exit;
            }

            $itensValidos[] = [
                'produto_id'    => $prod['id'],
                'lote_id'       => !empty($raw['lote_id']) ? (int)$raw['lote_id'] : null,
                'quantidade'    => $qty,
                'preco_unitario'=> (float)$prod['preco_venda'],
                'desconto_item' => $descItem,
            ];
        }

        if (empty($itensValidos)) {
            $_SESSION['flash_erro'] = 'Nenhum produto válido encontrado. Tente novamente.';
            header('Location: ' . $appUrl . '/vendas/nova');
            exit;
        }

        $cabecalho = [
            'usuario_id'      => (int)$_SESSION['usuario_id'],
            'cliente_id'      => !empty($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : null,
            'forma_pagamento' => $_POST['forma_pagamento'] ?? 'dinheiro',
            'desconto'        => max(0.0, (float)($_POST['desconto']    ?? $_POST['desconto_geral'] ?? 0)),
            'valor_pago'      => max(0.0, (float)($_POST['valor_pago'] ?? 0)),
            'observacoes'     => trim($_POST['observacoes'] ?? '') ?: null,
        ];

        try {
            $id = $this->venda->criar($cabecalho, $itensValidos);

            // Limpar carrinho da sessão após venda concluída
            unset($_SESSION['carrinho_balcao']);

            $_SESSION['flash_sucesso'] = 'Venda registada com sucesso!';
            header('Location: ' . $appUrl . '/vendas/' . $id . '/detalhe');
        } catch (\Throwable $e) {
            error_log('[VendaController::store] ' . $e->getMessage());
            $_SESSION['flash_erro'] = 'Erro ao registar a venda: ' . $e->getMessage();
            header('Location: ' . $appUrl . '/vendas/nova');
        }
        exit;
    }

    // ────────────────────────────────────────────────────────
    // GET /vendas/{id}/detalhe
    // ────────────────────────────────────────────────────────
    public function show(string $id): void
    {
        $venda = $this->venda->findCompleto((int)$id);
        if (!$venda) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.php';
            return;
        }

        View::render('vendas.detalhe', [
            'titulo'       => 'Venda ' . $venda['numero_venda'],
            'activePage'   => 'vendas',
            'breadcrumb'   => [
                'Vendas'               => ($_ENV['APP_URL'] ?? '') . '/vendas',
                $venda['numero_venda'] => null,
            ],
            'venda'        => $venda,
            'csrf_token'   => $_SESSION['csrf_token'] ?? '',
            'flash_sucesso'=> $_SESSION['flash_sucesso'] ?? null,
            'flash_erro'   => $_SESSION['flash_erro']    ?? null,
        ]);
        unset($_SESSION['flash_sucesso'], $_SESSION['flash_erro']);
    }

    // ────────────────────────────────────────────────────────
    // GET /vendas/{id}/talao  — Standalone, sem layout base
    // ────────────────────────────────────────────────────────
    public function talao(string $id): void
    {
        $venda = $this->venda->findCompleto((int)$id);
        if (!$venda) {
            http_response_code(404);
            echo '<p>Venda não encontrada.</p>';
            exit;
        }

        $config = [];
        try {
            $rows = Database::getInstance()
                ->query("SELECT chave, valor FROM configuracoes")
                ->fetchAll();
            foreach ($rows as $r) $config[$r['chave']] = $r['valor'];
        } catch (\Throwable $e) { /* usa defaults */ }

        // Renderizar sem layout — require directo do ficheiro de talão
        $viewPath = dirname(__DIR__) . '/Views/vendas/talao.php';
        extract(['venda' => $venda, 'config' => $config]);
        require $viewPath;
        exit;
    }

    // ────────────────────────────────────────────────────────
    // POST /vendas/{id}/cancelar
    // ────────────────────────────────────────────────────────
    public function cancelar(string $id): void
    {
        AuthMiddleware::requirePerfil('admin', 'farmaceutico');
        $this->csrf();

        $appUrl = $_ENV['APP_URL'] ?? '';
        $motivo = trim($_POST['motivo'] ?? '');
        $ok     = $this->venda->cancelar((int)$id, $motivo);

        $_SESSION[$ok ? 'flash_sucesso' : 'flash_erro'] = $ok
            ? 'Venda cancelada com sucesso. Stock reposto automaticamente.'
            : 'Não foi possível cancelar. A venda pode já estar cancelada.';

        header('Location: ' . $appUrl . '/vendas/' . $id . '/detalhe');
        exit;
    }

    // ────────────────────────────────────────────────────────
    // GET /api/estoque/alertas  — JSON para o header
    // ────────────────────────────────────────────────────────
    public function alertasStock(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $stmt = Database::getInstance()->query(
                "SELECT COUNT(*) AS total FROM produtos WHERE ativo=1 AND estoque_actual < estoque_min"
            );
            echo json_encode($stmt->fetch(\PDO::FETCH_ASSOC));
        } catch (\Throwable $e) {
            echo json_encode(['total' => 0]);
        }
        exit;
    }

    // ────────────────────────────────────────────────────────
    private function csrf(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(419);
            die('Token de segurança inválido. Recarregue a página e tente novamente.');
        }
    }
}
