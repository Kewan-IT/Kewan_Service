<?php
namespace App\Controllers;

use App\Models\Venda;
use App\Models\Produto;
use App\Models\Cliente;
use App\Middleware\AuthMiddleware;
use Core\View;

class VendaController
{
    private Venda   $model;
    private Produto $produtoModel;
    private Cliente $clienteModel;

    public function __construct()
    {
        AuthMiddleware::check();
        $this->model        = new Venda();
        $this->produtoModel = new Produto();
        $this->clienteModel = new Cliente();
    }

    // ================================================================
    // GET /vendas
    // ================================================================
    public function index(): void
    {
        $filtros = [
            'busca'           => trim($_GET['busca']           ?? ''),
            'status'          => $_GET['status']               ?? '',
            'forma_pagamento' => $_GET['forma_pagamento']      ?? '',
            'data_inicio'     => $_GET['data_inicio']          ?? date('Y-m-01'),
            'data_fim'        => $_GET['data_fim']             ?? date('Y-m-d'),
        ];

        $vendas     = $this->model->listar($filtros);
        $resumo     = $this->model->resumoDia();
        $pagamentos = $this->model->resumoPagamentosDia();

        // Alertas de lotes para o banner no topo
        $alertas30  = $this->model->alertasLotesAVencer(30);
        $vencidos   = $this->model->lotesVencidos();

        View::render('vendas.vendas_index', [
            'titulo'        => 'Vendas',
            'activePage'    => 'vendas',
            'breadcrumb'    => ['Vendas' => null],
            'vendas'        => $vendas,
            'resumo'        => $resumo,
            'pagamentos'    => $pagamentos,
            'filtros'       => $filtros,
            'alertas30'     => $alertas30,
            'vencidos'      => $vencidos,
            'flash_sucesso' => $_SESSION['flash_sucesso'] ?? null,
            'flash_erro'    => $_SESSION['flash_erro']    ?? null,
        ]);
        unset($_SESSION['flash_sucesso'], $_SESSION['flash_erro']);
    }

    // ================================================================
    // GET /vendas/nova  — Balcão
    // ================================================================
    public function create(): void
    {
        // Verificar caixa aberta
        $caixaModel = new \App\Models\Caixa();
        if (!$caixaModel->aberta()) {
            $_SESSION['flash_erro'] = 'Caixa fechada. Abra a caixa antes de efectuar vendas.';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/caixa');
            exit;
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Alertas de lotes para o banner no balcão
        $alertas30 = $this->model->alertasLotesAVencer(30);
        $vencidos  = $this->model->lotesVencidos();

        View::render('vendas.vendas_nova', [
            'titulo'        => 'Nova Venda',
            'activePage'    => 'venda-nova',
            'breadcrumb'    => ['Vendas' => ($_ENV['APP_URL'] ?? '') . '/vendas', 'Nova Venda' => null],
            'csrf_token'    => $_SESSION['csrf_token'],
            'alertas30'     => $alertas30,
            'vencidos'      => $vencidos,
        ]);
    }

    // ================================================================
    // POST /vendas/nova — Processar com validação de lotes
    // ================================================================
    public function store(): void
    {
        // Verificar caixa aberta
        $caixaModel = new \App\Models\Caixa();
        if (!$caixaModel->aberta()) {
            $_SESSION['flash_erro'] = 'Caixa fechada. Abra a caixa antes de efectuar vendas.';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/caixa');
            exit;
        }
        $this->verificarCsrf();

        $itens = json_decode($_POST['itens_json'] ?? '[]', true);

        if (empty($itens)) {
            $_SESSION['flash_erro'] = 'A venda deve ter pelo menos um produto.';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/vendas/nova');
            exit;
        }

        $subtotal   = 0;
        $itensFinal = [];

        foreach ($itens as $item) {
            $produto = $this->produtoModel->findById((int)($item['produto_id'] ?? 0));
            if (!$produto) {
                $_SESSION['flash_erro'] = 'Produto inválido na lista.';
                header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/vendas/nova');
                exit;
            }

            $qty = max(1, (int)$item['quantidade']);

            // Verificar stock global
            if ($produto['estoque_actual'] < $qty) {
                $_SESSION['flash_erro'] = "Stock insuficiente para «{$produto['nome']}». Disponível: {$produto['estoque_actual']}.";
                header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/vendas/nova');
                exit;
            }

            // Verificar se há lotes válidos suficientes (FEFO check)
            $alocs = $this->model->selecionarLotesFEFO((int)$produto['id'], $qty);
            if (empty($alocs)) {
                // Pode haver stock mas tudo vencido
                $_SESSION['flash_erro'] = "Sem lotes válidos para «{$produto['nome']}». Verifique os lotes em stock.";
                header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/vendas/nova');
                exit;
            }

            $precoUnit    = (float)$produto['preco_venda'];
            $descontoItem = max(0, (float)($item['desconto_item'] ?? 0));
            $subItem      = max(0, ($precoUnit * $qty) - $descontoItem);

            $itensFinal[] = [
                'produto_id'     => (int)$produto['id'],
                'lote_id'        => null, // FEFO resolve internamente
                'quantidade'     => $qty,
                'preco_unitario' => $precoUnit,
                'desconto_item'  => $descontoItem,
                'subtotal'       => $subItem,
            ];
            $subtotal += $subItem;
        }

        $desconto       = max(0, (float)($_POST['desconto'] ?? 0));
        $total          = max(0, $subtotal - $desconto);
        $valorPago      = (float)($_POST['valor_pago'] ?? $total);
        $formaPagamento = $_POST['forma_pagamento'] ?? 'dinheiro';

        if ($formaPagamento === 'dinheiro' && $valorPago < $total) {
            $_SESSION['flash_erro'] = 'Valor recebido insuficiente.';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/vendas/nova');
            exit;
        }

        $cabecalho = [
            'cliente_id'      => (int)($_POST['cliente_id'] ?? 0) ?: null,
            'usuario_id'      => (int)$_SESSION['usuario_id'],
            'receita_id'      => null,
            'forma_pagamento' => $formaPagamento,
            'desconto'        => $desconto,
            'subtotal'        => $subtotal,
            'total'           => $total,
            'valor_pago'      => $valorPago,
            'status'          => 'concluida',
            'observacoes'     => trim($_POST['observacoes'] ?? '') ?: null,
        ];

        try {
            $vendaId = $this->model->criar($cabecalho, $itensFinal);
            $_SESSION['flash_sucesso'] = 'Venda registada com sucesso!';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/vendas/' . $vendaId . '/detalhe');
            exit;
        } catch (\Throwable $e) {
            error_log('Erro venda: ' . $e->getMessage());
            $_SESSION['flash_erro'] = 'Erro ao processar: ' . $e->getMessage();
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/vendas/nova');
            exit;
        }
    }

    // ================================================================
    // GET /vendas/{id}/detalhe
    // ================================================================
    public function show(string $id): void
    {
        $venda = $this->model->findCompleto((int)$id);
        if (!$venda) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.php';
            return;
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        View::render('vendas.vendas_detalhe', [
            'titulo'        => 'Venda ' . $venda['numero_venda'],
            'activePage'    => 'vendas',
            'breadcrumb'    => ['Vendas' => ($_ENV['APP_URL'] ?? '') . '/vendas', $venda['numero_venda'] => null],
            'venda'         => $venda,
            'csrf_token'    => $_SESSION['csrf_token'],
            'flash_sucesso' => $_SESSION['flash_sucesso'] ?? null,
            'flash_erro'    => $_SESSION['flash_erro']    ?? null,
        ]);
        unset($_SESSION['flash_sucesso'], $_SESSION['flash_erro']);
    }

    // ================================================================
    // GET /vendas/{id}/talao
    // ================================================================
    public function talao(string $id): void
    {
        $venda = $this->model->findCompleto((int)$id);
        if (!$venda) {
            http_response_code(404);
            return;
        }
        View::render('vendas.talao', ['venda' => $venda]);
    }

    // ================================================================
    // POST /vendas/{id}/cancelar
    // ================================================================
    public function cancelar(string $id): void
    {
        AuthMiddleware::requirePerfil('admin', 'farmaceutico');
        $this->verificarCsrf();

        $motivo = trim($_POST['motivo'] ?? 'Cancelamento solicitado');
        $ok     = $this->model->cancelar((int)$id, $motivo);

        $_SESSION[$ok ? 'flash_sucesso' : 'flash_erro'] = $ok
            ? 'Venda cancelada. Stock e lotes repostos automaticamente.'
            : 'Não foi possível cancelar esta venda.';

        header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/vendas/' . $id . '/detalhe');
        exit;
    }

    // ================================================================
    // POST /vendas/nova/carrinho — guardar carrinho na sessão
    // ================================================================
    public function salvarCarrinho(): void
    {
        $data = json_decode($_POST['cart_data'] ?? '[]', true);
        if (is_array($data)) {
            $_SESSION['carrinho_balcao'] = array_values($data);
        }
        header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/vendas/nova');
        exit;
    }

    // ----------------------------------------------------------------
    private function verificarCsrf(): void
    {
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            http_response_code(419);
            die('Token inválido. Recarregue a página.');
        }
    }
}
