<?php

namespace App\Controllers;

use App\Models\Fornecedor;
use App\Middleware\AuthMiddleware;
use Core\View;

class FornecedorController
{
    private Fornecedor $model;

    public function __construct()
    {
        AuthMiddleware::apenasAdmin();
        $this->model = new Fornecedor();
    }

    // GET /fornecedores
    public function index(): void
    {
        $q      = trim($_GET['q']     ?? '');
        $status = $_GET['status']      ?? '';
        $page   = max(1, (int)($_GET['page'] ?? 1));

        $paginacao = $this->model->listar($q, $status, $page, 20);
        $stats     = $this->model->estatisticas();

        View::render('fornecedores.index', [
            'titulo'     => 'Fornecedores',
            'activePage' => 'fornecedores',
            'breadcrumb' => ['Fornecedores' => null],
            'paginacao'  => $paginacao,
            'stats'      => $stats,
            'q'          => $q,
            'status'     => $status,
            'flash_sucesso' => $_SESSION['flash_sucesso'] ?? null,
            'flash_erro'    => $_SESSION['flash_erro']    ?? null,
        ]);
        unset($_SESSION['flash_sucesso'], $_SESSION['flash_erro']);
    }

    // GET /fornecedores/novo
    public function create(): void
    {
        AuthMiddleware::requirePerfil('admin', 'farmaceutico');
        View::render('fornecedores.form', [
            'titulo'     => 'Novo Fornecedor',
            'activePage' => 'fornecedores',
            'breadcrumb' => ['Fornecedores' => '/fornecedores', 'Novo' => null],
            'fornecedor' => [],
            'erros'      => [],
            'modo'       => 'criar',
        ]);
    }

    // POST /fornecedores/novo
    public function store(): void
    {
        AuthMiddleware::requirePerfil('admin', 'farmaceutico');
        $this->csrfVerificar();

        [$dados, $erros] = $this->validar($_POST);

        if (!empty($dados['nuit']) && $this->model->nuitExiste($dados['nuit'])) {
            $erros['nuit'] = 'Este NUIT já está registado.';
        }

        if ($erros) {
            View::render('fornecedores.form', [
                'titulo'     => 'Novo Fornecedor',
                'activePage' => 'fornecedores',
                'breadcrumb' => ['Fornecedores' => '/fornecedores', 'Novo' => null],
                'fornecedor' => $dados,
                'erros'      => $erros,
                'modo'       => 'criar',
            ]);
            return;
        }

        $id = $this->model->insert($dados);
        $_SESSION['flash_sucesso'] = 'Fornecedor registado com sucesso.';
        header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/fornecedores/' . $id);
        exit;
    }

    // GET /fornecedores/{id}
    public function show(string $id): void
    {
        $fornecedor = $this->model->findById((int)$id);
        if (!$fornecedor) { $this->notFound(); return; }

        $compras = $this->model->compras((int)$id, 10);

        View::render('fornecedores.show', [
            'titulo'     => $fornecedor['nome'],
            'activePage' => 'fornecedores',
            'breadcrumb' => ['Fornecedores' => '/fornecedores', $fornecedor['nome'] => null],
            'fornecedor' => $fornecedor,
            'compras'    => $compras,
            'flash_sucesso' => $_SESSION['flash_sucesso'] ?? null,
            'flash_erro'    => $_SESSION['flash_erro']    ?? null,
        ]);
        unset($_SESSION['flash_sucesso'], $_SESSION['flash_erro']);
    }

    // GET /fornecedores/{id}/editar
    public function edit(string $id): void
    {
        AuthMiddleware::requirePerfil('admin', 'farmaceutico');
        $fornecedor = $this->model->findById((int)$id);
        if (!$fornecedor) { $this->notFound(); return; }

        View::render('fornecedores.form', [
            'titulo'     => 'Editar — ' . $fornecedor['nome'],
            'activePage' => 'fornecedores',
            'breadcrumb' => ['Fornecedores' => '/fornecedores', $fornecedor['nome'] => '/fornecedores/'.$id, 'Editar' => null],
            'fornecedor' => $fornecedor,
            'erros'      => [],
            'modo'       => 'editar',
        ]);
    }

    // POST /fornecedores/{id}/editar
    public function update(string $id): void
    {
        AuthMiddleware::requirePerfil('admin', 'farmaceutico');
        $this->csrfVerificar();

        $fornecedor = $this->model->findById((int)$id);
        if (!$fornecedor) { $this->notFound(); return; }

        [$dados, $erros] = $this->validar($_POST);

        if (!empty($dados['nuit']) && $this->model->nuitExiste($dados['nuit'], (int)$id)) {
            $erros['nuit'] = 'Este NUIT já está registado.';
        }

        if ($erros) {
            View::render('fornecedores.form', [
                'titulo'     => 'Editar Fornecedor',
                'activePage' => 'fornecedores',
                'breadcrumb' => ['Fornecedores' => '/fornecedores', 'Editar' => null],
                'fornecedor' => array_merge($fornecedor, $dados),
                'erros'      => $erros,
                'modo'       => 'editar',
            ]);
            return;
        }

        $this->model->update((int)$id, $dados);
        $_SESSION['flash_sucesso'] = 'Fornecedor actualizado com sucesso.';
        header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/fornecedores/' . $id);
        exit;
    }

    // POST /fornecedores/{id}/toggle
    public function toggle(string $id): void
    {
        AuthMiddleware::requirePerfil('admin');
        $f = $this->model->findById((int)$id);
        if (!$f) { $this->notFound(); return; }

        $this->model->update((int)$id, ['ativo' => $f['ativo'] ? 0 : 1]);
        $_SESSION['flash_sucesso'] = $f['ativo'] ? 'Fornecedor desactivado.' : 'Fornecedor activado.';
        header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/fornecedores/' . $id);
        exit;
    }

    // ----------------------------------------------------------------
    private function validar(array $post): array
    {
        $dados = [
            'nome'     => trim($post['nome']     ?? ''),
            'nuit'     => trim($post['nuit']     ?? '') ?: null,
            'telefone' => trim($post['telefone'] ?? '') ?: null,
            'email'    => trim($post['email']    ?? '') ?: null,
            'endereco' => trim($post['endereco'] ?? '') ?: null,
            'cidade'   => trim($post['cidade']   ?? '') ?: null,
            'pais'     => trim($post['pais']     ?? 'Moçambique') ?: 'Moçambique',
            'ativo'    => isset($post['ativo']) ? 1 : 0,
        ];

        $erros = [];
        if (strlen($dados['nome']) < 2) {
            $erros['nome'] = 'Nome obrigatório (mínimo 2 caracteres).';
        }
        if ($dados['email'] && !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            $erros['email'] = 'Email inválido.';
        }

        return [$dados, $erros];
    }

    private function csrfVerificar(): void
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
