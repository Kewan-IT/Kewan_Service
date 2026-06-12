<?php

namespace App\Controllers;

use App\Models\Produto;
use App\Models\Categoria;
use App\Middleware\AuthMiddleware;
use App\Services\UploadService;
use Core\View;

class ProdutoController
{
    private Produto   $model;
    private Categoria $catModel;
    private UploadService $upload;

    public function __construct()
    {
        AuthMiddleware::check();
        $this->model    = new Produto();
        $this->catModel = new Categoria();
        $this->upload   = new UploadService();
    }

    // ================================================================
    // GET /produtos
    // ================================================================
    public function index(): void
    {
        $pesquisa  = trim($_GET['q']       ?? '');
        $categoria = (int)($_GET['cat']    ?? 0);
        $filtro    = $_GET['filtro']        ?? '';
        $page      = max(1, (int)($_GET['page'] ?? 1));

        $paginacao  = $this->model->listar($pesquisa, $categoria, $filtro, $page, 24);
        $categorias = $this->catModel->arvore();
        $stats      = $this->model->estatisticas();

        View::render('produtos.index', [
            'titulo'     => 'Produtos',
            'activePage' => 'produtos',
            'breadcrumb' => ['Produtos' => null],
            'paginacao'  => $paginacao,
            'categorias' => $categorias,
            'stats'      => $stats,
            'pesquisa'   => $pesquisa,
            'categoria'  => $categoria,
            'filtro'     => $filtro,
            'flash_sucesso' => $_SESSION['flash_sucesso'] ?? null,
            'flash_erro'    => $_SESSION['flash_erro']    ?? null,
        ]);
        unset($_SESSION['flash_sucesso'], $_SESSION['flash_erro']);
    }

    // ================================================================
    // GET /produtos/novo
    // ================================================================
    public function create(): void
    {
        AuthMiddleware::requirePerfil('admin', 'farmaceutico');

        View::render('produtos.form', [
            'titulo'     => 'Novo Produto',
            'activePage' => 'produtos',
            'breadcrumb' => ['Produtos' => '/produtos', 'Novo' => null],
            'categorias' => $this->catModel->arvore(),
            'fornecedores' => $this->fornecedores(),
            'produto'    => [],
            'erros'      => [],
            'modo'       => 'criar',
        ]);
    }

    // ================================================================
    // POST /produtos/novo
    // ================================================================
    public function store(): void
    {
        AuthMiddleware::requirePerfil('admin', 'farmaceutico');
        $this->verificarCsrf();

        [$dados, $erros] = $this->validar($_POST);

        if (!empty($dados['codigo_barras']) && $this->model->codigoBarrasExiste($dados['codigo_barras'])) {
            $erros['codigo_barras'] = 'Este código de barras já existe.';
        }

        if ($erros) {
            $this->renderForm('criar', $dados, $erros);
            return;
        }

        // Upload imagem
        if (!empty($_FILES['imagem']['name'])) {
            try {
                $dados['imagem_url'] = $this->uploadImagem($_FILES['imagem'], $dados['nome']);
            } catch (\RuntimeException $e) {
                $erros['imagem'] = $e->getMessage();
                $this->renderForm('criar', $dados, $erros);
                return;
            }
        }

        $id = $this->model->insert($dados);

        // Lote inicial se fornecido
        if (!empty($_POST['lote_numero']) && !empty($_POST['lote_validade']) && (int)$_POST['lote_quantidade'] > 0) {
            $this->model->adicionarLote([
                'produto_id'   => $id,
                'numero_lote'  => trim($_POST['lote_numero']),
                'quantidade'   => (int)$_POST['lote_quantidade'],
                'validade'     => $_POST['lote_validade'],
                'data_entrada' => date('Y-m-d'),
                'observacoes'  => trim($_POST['lote_obs'] ?? '') ?: null,
            ]);
        }

        $_SESSION['flash_sucesso'] = 'Produto registado com sucesso.';
        header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/produtos/' . $id);
        exit;
    }

    // ================================================================
    // GET /produtos/{id}
    // ================================================================
    public function show(string $id): void
    {
        $produto = $this->model->findComDetalhes((int) $id);
        if (!$produto) { $this->notFound(); return; }

        $lotes      = $this->model->lotes((int) $id);
        $movimentos = $this->model->movimentos((int) $id, 20);

        View::render('produtos.show', [
            'titulo'     => $produto['nome'],
            'activePage' => 'produtos',
            'breadcrumb' => ['Produtos' => '/produtos', $produto['nome'] => null],
            'produto'    => $produto,
            'lotes'      => $lotes,
            'movimentos' => $movimentos,
            'flash_sucesso' => $_SESSION['flash_sucesso'] ?? null,
            'flash_erro'    => $_SESSION['flash_erro']    ?? null,
        ]);
        unset($_SESSION['flash_sucesso'], $_SESSION['flash_erro']);
    }

    // ================================================================
    // GET /produtos/{id}/editar
    // ================================================================
    public function edit(string $id): void
    {
        AuthMiddleware::requirePerfil('admin', 'farmaceutico');

        $produto = $this->model->findComDetalhes((int) $id);
        if (!$produto) { $this->notFound(); return; }

        View::render('produtos.form', [
            'titulo'      => 'Editar — ' . $produto['nome'],
            'activePage'  => 'produtos',
            'breadcrumb'  => ['Produtos' => '/produtos', $produto['nome'] => '/produtos/' . $id, 'Editar' => null],
            'categorias'  => $this->catModel->arvore(),
            'fornecedores'=> $this->fornecedores(),
            'produto'     => $produto,
            'erros'       => [],
            'modo'        => 'editar',
        ]);
    }

    // ================================================================
    // POST /produtos/{id}/editar
    // ================================================================
    public function update(string $id): void
    {
        AuthMiddleware::requirePerfil('admin', 'farmaceutico');
        $this->verificarCsrf();

        $produto = $this->model->findById((int) $id);
        if (!$produto) { $this->notFound(); return; }

        [$dados, $erros] = $this->validar($_POST, (int) $id);

        if (!empty($dados['codigo_barras']) && $this->model->codigoBarrasExiste($dados['codigo_barras'], (int) $id)) {
            $erros['codigo_barras'] = 'Este código de barras já existe.';
        }

        if ($erros) {
            $this->renderForm('editar', array_merge($produto, $dados), $erros, (int) $id);
            return;
        }

        // Upload nova imagem
        if (!empty($_FILES['imagem']['name'])) {
            try {
                $dados['imagem_url'] = $this->uploadImagem($_FILES['imagem'], $dados['nome']);
            } catch (\RuntimeException $e) {
                $erros['imagem'] = $e->getMessage();
                $this->renderForm('editar', array_merge($produto, $dados), $erros, (int) $id);
                return;
            }
        }

        $this->model->update((int) $id, $dados);

        $_SESSION['flash_sucesso'] = 'Produto actualizado com sucesso.';
        header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/produtos/' . $id);
        exit;
    }

    // ================================================================
    // POST /produtos/{id}/lote  — adicionar lote via ficha do produto
    // ================================================================
    public function adicionarLote(string $id): void
    {
        AuthMiddleware::requirePerfil('admin', 'farmaceutico');
        $this->verificarCsrf();

        $produto = $this->model->findById((int) $id);
        if (!$produto) { $this->notFound(); return; }

        $numero   = trim($_POST['numero_lote']  ?? '');
        $qty      = (int)($_POST['quantidade']  ?? 0);
        $validade = $_POST['validade']           ?? '';
        $obs      = trim($_POST['observacoes']   ?? '') ?: null;

        if (empty($numero) || $qty <= 0 || empty($validade)) {
            $_SESSION['flash_erro'] = 'Preencha todos os campos do lote.';
        } else {
            $this->model->adicionarLote([
                'produto_id'   => (int) $id,
                'numero_lote'  => $numero,
                'quantidade'   => $qty,
                'validade'     => $validade,
                'data_entrada' => date('Y-m-d'),
                'observacoes'  => $obs,
            ]);
            $_SESSION['flash_sucesso'] = "Lote «$numero» adicionado ($qty unidades).";
        }

        header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/produtos/' . $id);
        exit;
    }

    // ================================================================
    // GET /api/produtos/pesquisar?q=…  — AJAX para balcão de vendas
    // ================================================================
    public function pesquisarAjax(): void
    {
        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 2) {
            View::json([]);
            return;
        }
        $produtos = $this->model->pesquisarParaVenda($q, 12);
        View::json($produtos);
    }

    // ================================================================
    // GET /api/estoque/alertas  — usado pelo header do layout
    // ================================================================
    public function alertasAjax(): void
    {
        View::json($this->model->alertas());
    }

    // ================================================================
    // GET /api/produtos/{id}/preco  — AJAX para obter preço do produto
    // ================================================================
    public function precoAjax(string $id): void
    {
        $produto = $this->model->findById((int) $id);
        if (!$produto) {
            View::json(['erro' => 'Produto não encontrado'], 404);
            return;
        }
        View::json([
            'id'          => $produto['id'],
            'preco_venda' => $produto['preco_venda'],
            'nome'        => $produto['nome'],
        ]);
    }

    // ----------------------------------------------------------------
    // Helpers privados
    // ----------------------------------------------------------------
    private function validar(array $post, int $excluirId = 0): array
    {
        $fator = (float)str_replace(',', '.', $post['fator_conversao'] ?? '1');
        if ($fator <= 0) $fator = 1;

        $dados = [
            'nome'             => trim($post['nome']            ?? ''),
            'codigo_barras'    => trim($post['codigo_barras']   ?? '') ?: null,
            'principio_ativo'  => trim($post['principio_ativo'] ?? '') ?: null,
            'descricao'        => trim($post['descricao']       ?? '') ?: null,
            'categoria_id'     => (int)($post['categoria_id']   ?? 0),
            'fornecedor_id'    => (int)($post['fornecedor_id']  ?? 0) ?: null,
            'unidade_medida'   => trim($post['unidade_medida']  ?? 'unidade'),
            'unidade_compra'   => trim($post['unidade_compra']  ?? 'caixa'),
            'unidade_venda'    => trim($post['unidade_venda']   ?? 'unidade'),
            'fator_conversao'  => $fator,
            'preco_compra'     => (float)str_replace(',', '.', $post['preco_compra'] ?? '0'),
            'preco_venda'      => (float)str_replace(',', '.', $post['preco_venda']  ?? '0'),
            'estoque_min'      => (int)($post['estoque_min']    ?? 5),
            'requer_receita'   => isset($post['requer_receita']) ? 1 : 0,
            'controlado'       => isset($post['controlado'])     ? 1 : 0,
            'ativo'            => isset($post['ativo'])          ? 1 : 0,
        ];

        $erros = [];
        if (strlen($dados['nome']) < 2)       $erros['nome']            = 'Nome obrigatório (mínimo 2 caracteres).';
        if ($dados['categoria_id'] === 0)     $erros['categoria_id']    = 'Seleccione a categoria.';
        if ($dados['preco_venda'] <= 0)       $erros['preco_venda']     = 'Preço de venda deve ser maior que zero.';
        if ($dados['preco_compra'] < 0)       $erros['preco_compra']    = 'Preço de compra inválido.';
        if ($dados['estoque_min'] < 0)        $erros['estoque_min']     = 'Stock mínimo inválido.';
        if ($dados['fator_conversao'] <= 0)   $erros['fator_conversao'] = 'Factor de conversão deve ser maior que zero.';

        return [$dados, $erros];
    }

    private function uploadImagem(array $file, string $nome): string
    {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 5 * 1024 * 1024;

        if ($file['error'] !== UPLOAD_ERR_OK)          throw new \RuntimeException('Erro no upload.');
        if ($file['size'] > $maxSize)                  throw new \RuntimeException('Imagem demasiado grande (máx. 5 MB).');
        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, $allowed, true))          throw new \RuntimeException('Formato inválido (JPG, PNG ou WebP).');

        $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
        $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($nome));
        $dest = dirname(__DIR__, 2) . '/public/uploads/produtos/' . $slug . '-' . uniqid() . '.' . strtolower($ext);
        if (!move_uploaded_file($file['tmp_name'], $dest)) throw new \RuntimeException('Falha ao guardar a imagem.');

        return 'produtos/' . basename($dest);
    }

    private function renderForm(string $modo, array $dados, array $erros, int $id = 0): void
    {
        View::render('produtos.form', [
            'titulo'      => $modo === 'criar' ? 'Novo Produto' : 'Editar Produto',
            'activePage'  => 'produtos',
            'breadcrumb'  => ['Produtos' => '/produtos', ($modo === 'criar' ? 'Novo' : 'Editar') => null],
            'categorias'  => $this->catModel->arvore(),
            'fornecedores'=> $this->fornecedores(),
            'produto'     => $dados,
            'erros'       => $erros,
            'modo'        => $modo,
        ]);
    }

    private function fornecedores(): array
    {
        return $this->db()->query("SELECT id, nome FROM fornecedores WHERE ativo = 1 ORDER BY nome")->fetchAll();
    }

    private function db(): \PDO
    {
        return \Core\Database::getInstance();
    }

    private function notFound(): void
    {
        http_response_code(404);
        require __DIR__ . '/../../app/Views/errors/404.php';
    }

    private function verificarCsrf(): void
    {
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['_token'] ?? '')) {
            http_response_code(403); exit('Token inválido.');
        }
    }
}
