<?php

namespace App\Controllers;

use App\Models\Lote;
use App\Middleware\AuthMiddleware;
use Core\View;

class LoteController
{
    private Lote $model;

    public function __construct()
    {
        AuthMiddleware::requirePerfil('admin', 'diretor', 'farmaceutico');
        $this->model = new Lote();
    }

    // ================================================================
    // POST /lotes/{id}/promocao — colocar UM lote específico em promoção
    // ================================================================
    public function ativarPromocao(string $id): void
    {
        $this->verificarCsrf();

        $lote = $this->model->comProduto((int)$id);
        if (!$lote) { $this->voltarComErro('Lote não encontrado.'); return; }

        $preco  = (float) str_replace(',', '.', $_POST['preco_promocional'] ?? '0');
        $motivo = trim($_POST['promocao_motivo'] ?? '') ?: null;

        if ($preco <= 0) {
            $this->voltarComErro('Indique um preço promocional válido.', $id);
            return;
        }
        if ($preco >= (float)$lote['preco_venda']) {
            $this->voltarComErro('O preço promocional deve ser inferior ao preço normal (' .
                number_format($lote['preco_venda'], 2, ',', '.') . ' MZN).', $id);
            return;
        }

        $this->model->ativarPromocao((int)$id, $preco, $motivo, (int)$_SESSION['usuario_id']);

        $_SESSION['flash_sucesso'] = "Lote «{$lote['numero_lote']}» de «{$lote['produto_nome']}» colocado em promoção por " .
            number_format($preco, 2, ',', '.') . ' MZN.';
        $this->voltar($id);
    }

    // ================================================================
    // POST /lotes/{id}/promocao/cancelar
    // ================================================================
    public function cancelarPromocao(string $id): void
    {
        $this->verificarCsrf();

        $lote = $this->model->comProduto((int)$id);
        if (!$lote) { $this->voltarComErro('Lote não encontrado.'); return; }

        $this->model->cancelarPromocao((int)$id);
        $_SESSION['flash_sucesso'] = "Promoção do lote «{$lote['numero_lote']}» cancelada.";
        $this->voltar($id);
    }

    // ================================================================
    // POST /lotes/{id}/devolucao — devolver quantidade ao fornecedor,
    // sempre amarrado a este lote específico
    // ================================================================
    public function devolucao(string $id): void
    {
        $this->verificarCsrf();

        $lote = $this->model->comProduto((int)$id);
        if (!$lote) { $this->voltarComErro('Lote não encontrado.'); return; }

        $qtd     = (int)($_POST['quantidade'] ?? 0);
        $motivo  = $_POST['motivo'] ?? 'validade';
        $obs     = trim($_POST['observacoes'] ?? '') ?: null;

        if ($qtd <= 0 || $qtd > (int)$lote['quantidade']) {
            $this->voltarComErro("Quantidade inválida. Disponível no lote: {$lote['quantidade']}.", $id);
            return;
        }

        try {
            $devolucaoId = $this->model->registarDevolucao([
                'lote_id'     => (int)$id,
                'quantidade'  => $qtd,
                'motivo'      => $motivo,
                'observacoes' => $obs,
                'usuario_id'  => (int)$_SESSION['usuario_id'],
            ]);

            $_SESSION['flash_sucesso'] = "Devolução registada: {$qtd} unidade(s) do lote «{$lote['numero_lote']}». " .
                'Pode imprimir o comprovativo.';
            $_SESSION['devolucao_pdf_id'] = $devolucaoId;
        } catch (\Throwable $e) {
            $_SESSION['flash_erro'] = 'Erro ao registar devolução: ' . $e->getMessage();
        }

        $this->voltar($id);
    }

    // ================================================================
    // GET /devolucoes/{id}/pdf — comprovativo de devolução ao fornecedor
    // ================================================================
    public function devolucaoPdf(string $id): void
    {
        $devolucao = $this->model->devolucaoCompleta((int)$id);
        if (!$devolucao) {
            http_response_code(404);
            require __DIR__ . '/../../app/Views/errors/404.php';
            return;
        }

        $appUrl = $_ENV['APP_URL'] ?? '';
        extract([
            'devolucao' => $devolucao,
            'appUrl'    => $appUrl,
        ]);
        require __DIR__ . '/../../app/Views/lotes/devolucao_pdf.php';
        exit;
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------
    private function voltar(string $loteId): void
    {
        $destino = $_POST['voltar_para'] ?? (($_ENV['APP_URL'] ?? '') . '/relatorios/lotes-a-vencer');
        header('Location: ' . $destino);
        exit;
    }

    private function voltarComErro(string $msg, ?string $loteId = null): void
    {
        $_SESSION['flash_erro'] = $msg;
        $this->voltar((string)$loteId);
    }

    private function verificarCsrf(): void
    {
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['_token'] ?? '')) {
            http_response_code(403); exit('Token inválido.');
        }
    }
}
