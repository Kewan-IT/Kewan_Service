<?php

namespace App\Controllers;

use App\Models\Cliente;
use App\Middleware\AuthMiddleware;
use Core\View;

class ClienteController
{
    private Cliente $model;

    public function __construct()
    {
        AuthMiddleware::check();
        $this->model = new Cliente();
    }

    // ================================================================
    // GET /clientes
    // ================================================================
    public function index(): void
    {
        $pesquisa = trim($_GET['q']      ?? '');
        $status   = $_GET['status']       ?? '';
        $page     = max(1, (int)($_GET['page'] ?? 1));

        $paginacao = $this->model->listar($pesquisa, $status, $page, 20);
        $stats     = $this->model->estatisticas();

        View::render('clientes.index', [
            'titulo'     => 'Clientes',
            'activePage' => 'clientes',
            'breadcrumb' => ['Clientes' => null],
            'paginacao'  => $paginacao,
            'stats'      => $stats,
            'pesquisa'   => $pesquisa,
            'status'     => $status,
            'flash_sucesso' => $_SESSION['flash_sucesso'] ?? null,
            'flash_erro'    => $_SESSION['flash_erro']    ?? null,
        ]);
        unset($_SESSION['flash_sucesso'], $_SESSION['flash_erro']);
    }

    // ================================================================
    // GET /clientes/novo
    // ================================================================
    public function create(): void
    {
        View::render('clientes.form', [
            'titulo'    => 'Novo Cliente',
            'activePage'=> 'clientes',
            'breadcrumb'=> ['Clientes' => '/clientes', 'Novo' => null],
            'cliente'   => [],
            'erros'     => [],
            'modo'      => 'criar',
        ]);
    }

    // ================================================================
    // POST /clientes/novo
    // ================================================================
    public function store(): void
    {
        $this->verificarCsrf();
        [$dados, $erros] = $this->validar($_POST);

        if (!empty($dados['nuit']) && $this->model->nuitExiste($dados['nuit'])) {
            $erros['nuit'] = 'Este NUIT já está registado.';
        }

        if ($erros) {
            View::render('clientes.form', [
                'titulo'    => 'Novo Cliente',
                'activePage'=> 'clientes',
                'breadcrumb'=> ['Clientes' => '/clientes', 'Novo' => null],
                'cliente'   => $dados,
                'erros'     => $erros,
                'modo'      => 'criar',
            ]);
            return;
        }

        $id = $this->model->insert($dados);
        $_SESSION['flash_sucesso'] = 'Cliente registado com sucesso.';
        header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/clientes/' . $id);
        exit;
    }

    // ================================================================
    // GET /clientes/{id}
    // ================================================================
    public function show(string $id): void
    {
        $cliente = $this->model->findComHistorico((int) $id);
        if (!$cliente) { $this->notFound(); return; }

        $vendas   = $this->model->vendas((int) $id, 15);
        $favoritos = $this->model->produtosFavoritos((int) $id, 5);

        View::render('clientes.show', [
            'titulo'     => $cliente['nome'],
            'activePage' => 'clientes',
            'breadcrumb' => ['Clientes' => '/clientes', $cliente['nome'] => null],
            'cliente'    => $cliente,
            'vendas'     => $vendas,
            'favoritos'  => $favoritos,
            'flash_sucesso' => $_SESSION['flash_sucesso'] ?? null,
            'flash_erro'    => $_SESSION['flash_erro']    ?? null,
        ]);
        unset($_SESSION['flash_sucesso'], $_SESSION['flash_erro']);
    }

    // ================================================================
    // GET /clientes/{id}/editar
    // ================================================================
    public function edit(string $id): void
    {
        $cliente = $this->model->findById((int) $id);
        if (!$cliente) { $this->notFound(); return; }

        View::render('clientes.form', [
            'titulo'    => 'Editar — ' . $cliente['nome'],
            'activePage'=> 'clientes',
            'breadcrumb'=> ['Clientes' => '/clientes', $cliente['nome'] => '/clientes/' . $id, 'Editar' => null],
            'cliente'   => $cliente,
            'erros'     => [],
            'modo'      => 'editar',
        ]);
    }




   public function pesquisarAjax(): void
{
    AuthMiddleware::check();

    header('Content-Type: application/json; charset=utf-8');

    $q = trim($_GET['q'] ?? '');

    if (strlen($q) < 1) {
        echo json_encode([]);
        exit;
    }

    try {

        $db = (new \Core\Database)->getInstance();

        $stmt = $db->prepare("
            SELECT
                id,
                nome,
                telefone,
                nuit
            FROM clientes
            WHERE nome LIKE :q
            ORDER BY nome ASC
            LIMIT 20
        ");

        $stmt->execute([
            ':q' => "%{$q}%"
        ]);

        echo json_encode(
            $stmt->fetchAll(PDO::FETCH_ASSOC),
            JSON_UNESCAPED_UNICODE
        );

        exit;

    } catch (\Throwable $e) {

        http_response_code(500);

        echo json_encode([
            'erro' => $e->getMessage()
        ]);

        exit;
    }
}

    // ================================================================
    // POST /clientes/{id}/editar
    // ================================================================
    public function update(string $id): void
    {
        $this->verificarCsrf();

        $cliente = $this->model->findById((int) $id);
        if (!$cliente) { $this->notFound(); return; }

        [$dados, $erros] = $this->validar($_POST, (int) $id);

        if (!empty($dados['nuit']) && $this->model->nuitExiste($dados['nuit'], (int) $id)) {
            $erros['nuit'] = 'Este NUIT já está registado.';
        }

        if ($erros) {
            View::render('clientes.form', [
                'titulo'    => 'Editar Cliente',
                'activePage'=> 'clientes',
                'breadcrumb'=> ['Clientes' => '/clientes', 'Editar' => null],
                'cliente'   => array_merge($cliente, $dados),
                'erros'     => $erros,
                'modo'      => 'editar',
            ]);
            return;
        }

        $this->model->update((int) $id, $dados);
        $_SESSION['flash_sucesso'] = 'Cliente actualizado com sucesso.';
        header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/clientes/' . $id);
        exit;
    }

    // ================================================================
    // GET /api/clientes/pesquisar?q=…  — AJAX para balcão de vendas
    // ================================================================
    public function pesquisar(): void
    {
        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 2) { View::json(['clientes' => []]); return; }
        View::json(['clientes' => $this->model->pesquisarParaVenda($q, 8)]);
    }

    // ----------------------------------------------------------------
    private function validar(array $post, int $excluirId = 0): array
    {
        $dados = [
            'nome'            => trim($post['nome']            ?? ''),
            'nuit'            => trim($post['nuit']            ?? '') ?: null,
            'bi'              => trim($post['bi']              ?? '') ?: null,
            'telefone'        => trim($post['telefone']        ?? '') ?: null,
            'email'           => trim($post['email']           ?? '') ?: null,
            'endereco'        => trim($post['endereco']        ?? '') ?: null,
            'data_nascimento' => $post['data_nascimento']       ?? null ?: null,
            'sexo'            => $post['sexo']                  ?? null ?: null,
            'observacoes'     => trim($post['observacoes']     ?? '') ?: null,
            'ativo'           => isset($post['ativo']) ? 1 : 0,
        ];

        $erros = [];
        if (strlen($dados['nome']) < 2) $erros['nome'] = 'Nome obrigatório (mínimo 2 caracteres).';
        if ($dados['email'] && !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            $erros['email'] = 'Email inválido.';
        }

        return [$dados, $erros];
    }

    private function verificarCsrf(): void
    {
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['_token'] ?? '')) {
            http_response_code(403); exit('Token inválido.');
        }
    }

    private function notFound(): void
    {
        http_response_code(404);
        require __DIR__ . '/../../app/Views/errors/404.php';
    }
}
