<?php
 
namespace App\Controllers;
 
use App\Models\Compra;
use App\Models\Produto;
use App\Models\Categoria;
use App\Models\Configuracao;
use App\Middleware\AuthMiddleware;
use Core\View;
 
class CompraController
{
    private Compra  $model;
    private Produto $produtoModel;
    private Categoria $categoriaModel;
 
    public function __construct()
    {
        AuthMiddleware::check();
        $this->model          = new Compra();
        $this->produtoModel   = new Produto();
        $this->categoriaModel = new Categoria();
    }
 
    // ================================================================
    // GET /compras — Listagem
    // ================================================================
    public function index(): void
    {
        $q      = trim($_GET['q']       ?? '');
        $status = $_GET['status']        ?? '';
        $page   = max(1, (int)($_GET['page'] ?? 1));
 
        $paginacao = $this->model->listar($q, $status, $page, 20);
        $stats     = $this->model->estatisticas();
 
        View::render('compras.compras_index', [
            'titulo'     => 'Compras',
            'activePage' => 'compras',
            'breadcrumb' => ['Compras' => null],
            'paginacao'  => $paginacao,
            'stats'      => $stats,
            'q'          => $q,
            'status'     => $status,
            'flash_sucesso' => $_SESSION['flash_sucesso'] ?? null,
            'flash_erro'    => $_SESSION['flash_erro']    ?? null,
        ]);
        unset($_SESSION['flash_sucesso'], $_SESSION['flash_erro']);
    }
 
    // ================================================================
    // GET /compras/nova — Formulário nova compra
    // ================================================================
    public function create(): void
    {
        AuthMiddleware::requirePerfil('admin', 'farmaceutico');
 
        View::render('compras.compras_nova', [
            'titulo'       => 'Nova Compra',
            'activePage'   => 'compras',
            'breadcrumb'   => ['Compras' => '/compras', 'Nova' => null],
            'fornecedores' => $this->model->fornecedores(),
            'categorias'   => $this->categoriaModel->arvore(),
            'csrf_token'   => $this->csrfGerar(),
        ]);
    }

    // ================================================================
    // POST /compras/nova — Guardar compra
    // ================================================================
    public function store(): void
    {
        AuthMiddleware::requirePerfil('admin', 'farmaceutico');
        $this->csrfVerificar();
 
        $itensJson = $_POST['itens_json'] ?? '[]';
        $itens     = json_decode($itensJson, true) ?: [];
 
        if (empty($itens)) {
            $_SESSION['flash_erro'] = 'Adicione pelo menos um produto à compra.';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/compras/nova');
            exit;
        }
 
        $cabecalho = [
            'fornecedor_id'  => (int)($_POST['fornecedor_id'] ?? 0),
            'numero_fatura'  => trim($_POST['numero_fatura']  ?? '') ?: null,
            'data_pedido'    => $_POST['data_pedido']  ?? date('Y-m-d'),
            'data_entrega'   => $_POST['data_entrega'] ?? null ?: null,
            'desconto'       => (float)str_replace(',', '.', $_POST['desconto'] ?? '0'),
            'status'         => $_POST['status']       ?? 'rascunho',
            'observacoes'    => trim($_POST['observacoes'] ?? '') ?: null,
        ];
 
        if (!$cabecalho['fornecedor_id']) {
            $_SESSION['flash_erro'] = 'Seleccione um fornecedor.';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/compras/nova');
            exit;
        }
 
        try {
            $id = $this->model->criar($cabecalho, $itens);
            $_SESSION['flash_sucesso'] = 'Compra registada com sucesso.';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/compras/' . $id);
        } catch (\Throwable $e) {
            $_SESSION['flash_erro'] = 'Erro ao guardar a compra: ' . $e->getMessage();
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/compras/nova');
        }
        exit;
    }
 
    // ================================================================
    // GET /compras/{id} — Detalhe
    // ================================================================
    public function show(string $id): void
    {
        $compra = $this->model->findComItens((int)$id);
        if (!$compra) { $this->notFound(); return; }
 
        View::render('compras.compras_detalhe', [
            'titulo'     => 'Compra ' . $compra['numero_compra'],
            'activePage' => 'compras',
            'breadcrumb' => ['Compras' => '/compras', $compra['numero_compra'] => null],
            'compra'     => $compra,
            'csrf_token' => $this->csrfGerar(),
            'flash_sucesso' => $_SESSION['flash_sucesso'] ?? null,
            'flash_erro'    => $_SESSION['flash_erro']    ?? null,
        ]);
        unset($_SESSION['flash_sucesso'], $_SESSION['flash_erro']);
    }
 
    // ================================================================
    // POST /compras/{id}/receber — Receber mercadoria
    // ================================================================
    public function receberMercadoria(string $id): void
    {
        AuthMiddleware::requirePerfil('admin', 'farmaceutico');
        $this->csrfVerificar();
 
        $compra = $this->model->findComItens((int)$id);
        if (!$compra) { $this->notFound(); return; }
 
        if (!in_array($compra['status'], ['enviada', 'parcialmente_recebida', 'rascunho'])) {
            $_SESSION['flash_erro'] = 'Esta compra não pode ser recebida no estado actual.';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/compras/' . $id);
            exit;
        }
 
        // Montar array de recebimentos a partir do POST
        $recebimentos = [];
        foreach ($_POST['receber'] ?? [] as $itemId => $qty) {
            $qty = (int)$qty;
            if ($qty <= 0) continue;
 
            // Encontrar o item na compra
            $item = null;
            foreach ($compra['itens'] as $i) {
                if ((int)$i['id'] === (int)$itemId) { $item = $i; break; }
            }
            if (!$item) continue;
 
            $maxReceber = $item['quantidade'] - $item['quantidade_recebida'];
            $recebimentos[] = [
                'item_id'        => $itemId,
                'produto_id'     => $item['produto_id'],
                'quantidade_receber' => min($qty, $maxReceber),
                'numero_lote'    => $_POST['lote'][$itemId]    ?? $item['numero_lote']    ?? null,
                'validade_lote'  => $_POST['validade'][$itemId] ?? $item['validade_lote'] ?? null,
            ];
        }
 
        if (empty($recebimentos)) {
            $_SESSION['flash_erro'] = 'Indique pelo menos uma quantidade a receber.';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/compras/' . $id);
            exit;
        }
 
        try {
            $this->model->receberMercadoria((int)$id, $recebimentos);
            $_SESSION['flash_sucesso'] = 'Mercadoria recebida e stock actualizado com sucesso.';
        } catch (\Throwable $e) {
            $_SESSION['flash_erro'] = 'Erro ao receber mercadoria: ' . $e->getMessage();
        }
 
        header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/compras/' . $id);
        exit;
    }
 
    // ================================================================
    // POST /compras/{id}/cancelar
    // ================================================================
    public function cancelar(string $id): void
    {
        AuthMiddleware::requirePerfil('admin');
        $this->csrfVerificar();
 
        try {
            $this->model->cancelar((int)$id);
            $_SESSION['flash_sucesso'] = 'Compra cancelada.';
        } catch (\Throwable $e) {
            $_SESSION['flash_erro'] = 'Não foi possível cancelar: ' . $e->getMessage();
        }
 
        header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/compras/' . $id);
        exit;
    }

    // ================================================================
// GET /compras/{id}/pdf — Gerar PDF da encomenda
// ================================================================
public function pdf(string $id): void
{
    $compra = $this->model->findComItens((int)$id);
    if (!$compra) { $this->notFound(); return; }

    $appUrl = $_ENV['APP_URL'] ?? '';
    $config = (new Configuracao())->getAllWithDefaults();
    extract(['compra' => $compra, 'appUrl' => $appUrl, 'config' => $config]);
    require __DIR__ . '/../../app/Views/compras/compras_pdf.php';
    exit;
}
 
    // ----------------------------------------------------------------
    private function notFound(): void
    {
        http_response_code(404);
        require __DIR__ . '/../../app/Views/errors/404.php';
    }
 
    private function csrfGerar(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
 
    private function csrfVerificar(): void
    {
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            http_response_code(403); exit('Token inválido.');
        }
    }
}
 
