<?php
namespace App\Controllers;

use App\Models\Caixa;
use App\Middleware\AuthMiddleware;
use Core\View;

class CaixaController
{
    private Caixa $model;

    public function __construct()
    {
        AuthMiddleware::check();
        $this->model = new Caixa();
    }

    // ================================================================
    // GET /caixa — Dashboard da caixa
    // ================================================================
    public function index(): void
    {
        $caixaAberta  = $this->model->aberta();
        $historico    = $this->model->historico(1, 10);
        $stats        = $this->model->estatisticasHoje();

        $resumo = null;
        $movimentos = [];
        $pagamentos = [];
        if ($caixaAberta) {
            $resumo     = $this->model->resumoMovimentos($caixaAberta['id']);
            $movimentos = $this->model->movimentos($caixaAberta['id'], 15);
            $pagamentos = $this->model->resumoPagamentos($caixaAberta['id']);
        }

        View::render('caixa.caixa_index', [
            'titulo'       => 'Caixa',
            'activePage'   => 'caixa',
            'breadcrumb'   => ['Caixa' => null],
            'caixaAberta'  => $caixaAberta,
            'historico'    => $historico,
            'stats'        => $stats,
            'resumo'       => $resumo,
            'movimentos'   => $movimentos,
            'pagamentos'   => $pagamentos,
            'csrf_token'   => $_SESSION['csrf_token'] ?? '',
            'flash_sucesso' => $_SESSION['flash_sucesso'] ?? null,
            'flash_erro'    => $_SESSION['flash_erro']    ?? null,
        ]);
        unset($_SESSION['flash_sucesso'], $_SESSION['flash_erro']);
    }

    // ================================================================
    // POST /caixa/abrir
    // ================================================================
    public function abrir(): void
    {
        $this->verificarCsrf();

        // Verificar se já há caixa aberta
        $jaAberta = $this->model->aberta();
        if ($jaAberta) {
            $_SESSION['flash_erro'] = 'Já existe uma caixa aberta por ' . ($jaAberta['usuario_nome'] ?? 'outro utilizador') . '.';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/caixa');
            exit;
        }

        $saldoInicial = max(0, (float) str_replace(',', '.', $_POST['saldo_inicial'] ?? '0'));
        $obs          = trim($_POST['observacoes'] ?? '') ?: null;

        $id = $this->model->abrir((int) $_SESSION['usuario_id'], $saldoInicial, $obs);


        $_SESSION['flash_sucesso'] = 'Caixa aberta com fundo de MT ' . number_format($saldoInicial, 2, ',', '.') . '.';
        header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/caixa');
        exit;
    }

    // ================================================================
    // POST /caixa/fechar
    // ================================================================
    public function fechar(): void
    {
        AuthMiddleware::check();
        $this->verificarCsrf();

        $caixaAberta = $this->model->aberta();
        if (!$caixaAberta) {
            $_SESSION['flash_erro'] = 'Não há caixa aberta para fechar.';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/caixa');
            exit;
        }

        $saldoFinal = max(0, (float) str_replace(',', '.', $_POST['saldo_final'] ?? '0'));
        $obs        = trim($_POST['observacoes'] ?? '') ?: null;

        $this->model->fechar($caixaAberta['id'], $saldoFinal, $obs);

        $saldoEsperado = $caixaAberta['saldo_inicial'] + $caixaAberta['total_entradas'] - $caixaAberta['total_saidas'];
        $diferenca     = $saldoFinal - $saldoEsperado;
        $msg = 'Caixa fechada. Saldo final: MT ' . number_format($saldoFinal, 2, ',', '.');
        if ($diferenca != 0) {
            $msg .= ' | Diferença: MT ' . number_format(abs($diferenca), 2, ',', '.') . ($diferenca > 0 ? ' (sobra)' : ' (falta)');
        }
        $msg .= ' — Clique em "Relatório" para imprimir o fecho.';

        $_SESSION['flash_sucesso'] = $msg;
        header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/caixa/' . $caixaAberta['id'] . '?relatorio=1');
        exit;
    }

    // ================================================================
    // GET /caixa/{id}/relatorio — Relatório detalhado de fecho
    // ================================================================
    public function relatorio(string $id): void
    {
        $sessao = $this->model->findCompleto((int) $id);
        if (!$sessao) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.php';
            return;
        }

        $movimentos  = $this->model->movimentos((int) $id, 500);
        $resumo      = $this->model->resumoMovimentos((int) $id);
        $pagamentos  = $this->model->resumoPagamentos((int) $id);
        $dadosRel    = $this->model->dadosRelatorio((int) $id);
        $config      = (new \App\Models\Configuracao())->getAllWithDefaults();

        extract([
            's'          => $sessao,
            'movimentos' => $movimentos,
            'resumo'     => $resumo,
            'pagamentos' => $pagamentos,
            'config'     => $config,
            'appUrl'     => $_ENV['APP_URL'] ?? '',
        ] + $dadosRel);

        require __DIR__ . '/../../app/Views/caixa/relatorio_fecho_pdf.php';
        exit;
    }

    // ================================================================
    // POST /caixa/movimento — sangria, suprimento, entrada, saída
    // ================================================================
    public function movimento(): void
    {
        $this->verificarCsrf();

        $caixaAberta = $this->model->aberta();
        if (!$caixaAberta) {
            $_SESSION['flash_erro'] = 'Não há caixa aberta.';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/caixa');
            exit;
        }

        $tipo      = $_POST['tipo']        ?? '';
        $valor     = max(0, (float) str_replace(',', '.', $_POST['valor'] ?? '0'));
        $descricao = trim($_POST['descricao'] ?? '');
        $tiposValidos = ['sangria', 'suprimento', 'entrada', 'saida'];

        if (!in_array($tipo, $tiposValidos)) {
            $_SESSION['flash_erro'] = 'Tipo de movimento inválido.';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/caixa');
            exit;
        }
        if ($valor <= 0) {
            $_SESSION['flash_erro'] = 'O valor deve ser maior que zero.';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/caixa');
            exit;
        }
        if (empty($descricao)) {
            $_SESSION['flash_erro'] = 'A descrição é obrigatória.';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/caixa');
            exit;
        }

        $this->model->adicionarMovimento(
            $caixaAberta['id'], $tipo, $valor, $descricao, (int) $_SESSION['usuario_id']
        );

        $nomes = ['sangria' => 'Sangria', 'suprimento' => 'Suprimento', 'entrada' => 'Entrada', 'saida' => 'Saída'];
        $_SESSION['flash_sucesso'] = ($nomes[$tipo] ?? $tipo) . ' de MT ' . number_format($valor, 2, ',', '.') . ' registada.';
        header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/caixa');
        exit;
    }

    // ================================================================
    // GET /caixa/{id} — Detalhe de uma sessão
    // ================================================================
    public function show(string $id): void
    {
        $sessao = $this->model->findCompleto((int) $id);
        if (!$sessao) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.php';
            return;
        }

        $movimentos = $this->model->movimentos((int) $id);
        $resumo     = $this->model->resumoMovimentos((int) $id);
        $pagamentos = $this->model->resumoPagamentos((int) $id);

        View::render('caixa.caixa_detalhe', [
            'titulo'      => 'Sessão de Caixa #' . $id,
            'activePage'  => 'caixa',
            'breadcrumb'  => ['Caixa' => ($_ENV['APP_URL'] ?? '') . '/caixa', 'Sessão #' . $id => null],
            'sessao'      => $sessao,
            'movimentos'  => $movimentos,
            'resumo'      => $resumo,
            'pagamentos'  => $pagamentos,
            'csrf_token'  => $_SESSION['csrf_token'] ?? '',
        ]);
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
