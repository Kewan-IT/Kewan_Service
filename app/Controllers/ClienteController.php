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

    public function index(): void
    {
        $pesquisa = trim($_GET['q']      ?? '');
        $status   = $_GET['status']       ?? '';
        $tipo     = $_GET['tipo']         ?? '';
        $page     = max(1, (int)($_GET['page'] ?? 1));

        $paginacao = $this->model->listar($pesquisa, $status, $tipo, $page, 20);
        $stats     = $this->model->estatisticas();

        View::render('clientes.index', [
            'titulo'     => 'Clientes',
            'activePage' => 'clientes',
            'breadcrumb' => ['Clientes' => null],
            'paginacao'  => $paginacao,
            'stats'      => $stats,
            'pesquisa'   => $pesquisa,
            'status'     => $status,
            'tipo'       => $tipo,
            'flash_sucesso' => $_SESSION['flash_sucesso'] ?? null,
            'flash_erro'    => $_SESSION['flash_erro']    ?? null,
        ]);
        unset($_SESSION['flash_sucesso'], $_SESSION['flash_erro']);
    }

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

    public function show(string $id): void
    {
        $cliente = $this->model->findComHistorico((int) $id);
        if (!$cliente) { $this->notFound(); return; }

        $vendas    = $this->model->vendas((int) $id, 15);
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
        if (strlen($q) < 1) { echo json_encode([]); exit; }

        try {
            $db = (new \Core\Database)->getInstance();
            $stmt = $db->prepare("
                SELECT id, nome, telefone, nuit, tipo_cliente
                FROM clientes
                WHERE nome LIKE :q
                ORDER BY nome ASC
                LIMIT 20
            ");
            $stmt->execute([':q' => "%{$q}%"]);
            echo json_encode($stmt->fetchAll(\PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
            exit;
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['erro' => $e->getMessage()]);
            exit;
        }
    }

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

    public function pesquisar(): void
    {
        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 2) { View::json(['clientes' => []]); return; }
        View::json(['clientes' => $this->model->pesquisarParaVenda($q, 8)]);
    }

    private function validar(array $post, int $excluirId = 0): array
    {
        $tipo = in_array($post['tipo_cliente'] ?? '', ['singular','instituicao'])
              ? $post['tipo_cliente']
              : 'singular';

        $dados = [
            'tipo_cliente'    => $tipo,
            'nome'            => trim($post['nome']            ?? ''),
            'nuit'            => trim($post['nuit']            ?? '') ?: null,
            'bi'              => trim($post['bi']              ?? '') ?: null,
            'telefone'        => trim($post['telefone']        ?? '') ?: null,
            'telefone2'       => trim($post['telefone2']       ?? '') ?: null,
            'email'           => trim($post['email']           ?? '') ?: null,
            'endereco'        => trim($post['endereco']        ?? '') ?: null,
            'observacoes'     => trim($post['observacoes']     ?? '') ?: null,
            'ativo'           => isset($post['ativo']) ? 1 : 0,
            // singular
            'data_nascimento' => $post['data_nascimento'] ?? null ?: null,
            'sexo'            => $post['sexo']            ?? null ?: null,
            // instituição
            'nome_comercial'  => trim($post['nome_comercial']  ?? '') ?: null,
            'sector'          => trim($post['sector']          ?? '') ?: null,
            'pessoa_contacto' => trim($post['pessoa_contacto'] ?? '') ?: null,
        ];

        $erros = [];
        if (strlen($dados['nome']) < 2) {
            $erros['nome'] = $tipo === 'instituicao'
                ? 'Nome/Razão Social obrigatório (mínimo 2 caracteres).'
                : 'Nome obrigatório (mínimo 2 caracteres).';
        }
        if ($dados['email'] && !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            $erros['email'] = 'Email inválido.';
        }
        // NUIT obrigatório para instituição
        if ($tipo === 'instituicao' && empty($dados['nuit'])) {
            $erros['nuit'] = 'NUIT obrigatório para instituições.';
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
