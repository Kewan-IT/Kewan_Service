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

    // ----------------------------------------------------------------
    // GET /vendas — Listagem
    // ----------------------------------------------------------------
    public function index(): void
    {
        $filtros = [
            'busca'          => trim($_GET['busca']           ?? ''),
            'status'         => $_GET['status']               ?? '',
            'forma_pagamento'=> $_GET['forma_pagamento']      ?? '',
            'data_inicio'    => $_GET['data_inicio']          ?? date('Y-m-01'),
            'data_fim'       => $_GET['data_fim']             ?? date('Y-m-d'),
        ];

        $vendas   = $this->model->listar($filtros);
        $resumo   = $this->model->resumoDia();
        $pagamentos = $this->model->resumoPagamentosDia();

        View::render('vendas.index', [
            'titulo'      => 'Vendas',
            'activePage'  => 'vendas',
            'breadcrumb'  => ['Vendas' => null],
            'vendas'      => $vendas,
            'resumo'      => $resumo,
            'pagamentos'  => $pagamentos,
            'filtros'     => $filtros,
            'flash_sucesso'=> $_SESSION['flash_sucesso'] ?? null,
            'flash_erro'   => $_SESSION['flash_erro']    ?? null,
        ]);
        unset($_SESSION['flash_sucesso'], $_SESSION['flash_erro']);
    }

    // ----------------------------------------------------------------
    // GET /vendas/nova — Balcão de venda
    // ----------------------------------------------------------------
    public function create(): void
    {
        View::render('vendas.nova', [
            'titulo'     => 'Nova Venda',
            'activePage' => 'venda-nova',
            'breadcrumb' => ['Vendas' => $_ENV['APP_URL'] . '/vendas', 'Nova Venda' => null],
            'csrf_token' => $_SESSION['csrf_token'],
        ]);
    }

    // ----------------------------------------------------------------
    // POST /vendas/nova — Processar venda
    // ----------------------------------------------------------------
    public function store(): void
    {
        $this->verificarCsrf();

        // Itens enviados como JSON
        $itensJson = $_POST['itens_json'] ?? '[]';
        $itens = json_decode($itensJson, true);

        if (empty($itens)) {
            $_SESSION['flash_erro'] = 'A venda deve ter pelo menos um produto.';
            header('Location: ' . $_ENV['APP_URL'] . '/vendas/nova');
            exit;
        }

        // Validar e calcular totais
        $subtotal = 0;
        $itensFinal = [];

        foreach ($itens as $item) {
            $produto = $this->produtoModel->findById((int)($item['produto_id'] ?? 0));
            if (!$produto) {
                $_SESSION['flash_erro'] = 'Produto inválido na lista de itens.';
                header('Location: ' . $_ENV['APP_URL'] . '/vendas/nova');
                exit;
            }

            $qty          = (int)($item['quantidade'] ?? 1);
            $precoUnit    = (float)$produto['preco_venda'];
            $descontoItem = (float)($item['desconto_item'] ?? 0);
            $subItem      = ($precoUnit * $qty) - $descontoItem;

            $itensFinal[] = [
                'produto_id'    => $produto['id'],
                'lote_id'       => null, // lote automático pelo trigger
                'quantidade'    => $qty,
                'preco_unitario'=> $precoUnit,
                'desconto_item' => $descontoItem,
                'subtotal'      => max(0, $subItem),
            ];

            $subtotal += max(0, $subItem);
        }

        $descontoGeral = (float)($_POST['desconto'] ?? 0);
        $total         = max(0, $subtotal - $descontoGeral);
        $valorPago     = (float)($_POST['valor_pago'] ?? $total);
        $formaPagamento= $_POST['forma_pagamento'] ?? 'dinheiro';

        if ($valorPago < $total && $formaPagamento === 'dinheiro') {
            $_SESSION['flash_erro'] = 'Valor pago insuficiente para a forma de pagamento Dinheiro.';
            header('Location: ' . $_ENV['APP_URL'] . '/vendas/nova');
            exit;
        }

        $cabecalho = [
            'cliente_id'     => (int)($_POST['cliente_id'] ?? 0) ?: null,
            'usuario_id'     => (int)$_SESSION['usuario_id'],
            'receita_id'     => (int)($_POST['receita_id'] ?? 0) ?: null,
            'forma_pagamento'=> $formaPagamento,
            'desconto'       => $descontoGeral,
            'subtotal'       => $subtotal,
            'total'          => $total,
            'valor_pago'     => $valorPago,
            'status'         => 'concluida',
            'observacoes'    => trim($_POST['observacoes'] ?? '') ?: null,
        ];

        try {
            $vendaId = $this->model->criar($cabecalho, $itensFinal);
            $_SESSION['flash_sucesso'] = 'Venda registada com sucesso!';
            header('Location: ' . $_ENV['APP_URL'] . '/vendas/' . $vendaId);
            exit;
        } catch (\Throwable $e) {
            error_log('Erro ao criar venda: ' . $e->getMessage());
            $_SESSION['flash_erro'] = 'Erro ao processar a venda. Tente novamente.';
            header('Location: ' . $_ENV['APP_URL'] . '/vendas/nova');
            exit;
        }
    }

    // ----------------------------------------------------------------
    // GET /vendas/{id} — Detalhe e talão
    // ----------------------------------------------------------------
    public function show(string $id): void
    {
        $venda = $this->model->findCompleto((int)$id);
        if (!$venda) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.php';
            return;
        }

        View::render('vendas.detalhe', [
            'titulo'      => 'Venda ' . $venda['numero_venda'],
            'activePage'  => 'vendas',
            'breadcrumb'  => [
                'Vendas' => $_ENV['APP_URL'] . '/vendas',
                $venda['numero_venda'] => null,
            ],
            'venda'        => $venda,
            'flash_sucesso'=> $_SESSION['flash_sucesso'] ?? null,
            'flash_erro'   => $_SESSION['flash_erro']    ?? null,
            'csrf_token'   => $_SESSION['csrf_token'],
        ]);
        unset($_SESSION['flash_sucesso'], $_SESSION['flash_erro']);
    }

    // ----------------------------------------------------------------
    // POST /vendas/{id}/cancelar
    // ----------------------------------------------------------------
    public function cancelar(string $id): void
    {
        AuthMiddleware::requirePerfil('admin', 'farmaceutico');
        $this->verificarCsrf();

        $motivo = trim($_POST['motivo'] ?? 'Cancelamento solicitado');
        $ok     = $this->model->cancelar((int)$id, $motivo);

        if ($ok) {
            $_SESSION['flash_sucesso'] = 'Venda cancelada. Stock reposto automaticamente.';
        } else {
            $_SESSION['flash_erro'] = 'Não foi possível cancelar esta venda.';
        }

        header('Location: ' . $_ENV['APP_URL'] . '/vendas/' . $id);
        exit;
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------
    private function verificarCsrf(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(419);
            die('Token de segurança inválido. Recarregue a página.');
        }
    }
}
