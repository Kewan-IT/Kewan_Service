<?php

namespace App\Controllers;

use App\Models\Funcionario;
use App\Models\Cargo;
use App\Models\Usuario;
use App\Middleware\AuthMiddleware;
use App\Services\UploadService;
use Core\View;

class FuncionarioController
{
    private Funcionario   $model;
    private Cargo         $cargoModel;
    private Usuario       $usuarioModel;
    private UploadService $upload;

    public function __construct()
    {
        AuthMiddleware::apenasAdmin();
        $this->model        = new Funcionario();
        $this->cargoModel   = new Cargo();
        $this->usuarioModel = new Usuario();
        $this->upload       = new UploadService();
    }

    // ================================================================
    // GET /funcionarios  — listagem com pesquisa e paginação
    // ================================================================
    public function index(): void
    {
        AuthMiddleware::requirePerfil('admin', 'farmaceutico');

        $pesquisa  = trim($_GET['q']       ?? '');
        $status    = $_GET['status']        ?? '';
        $cargo_id  = (int)($_GET['cargo']   ?? 0);
        $page      = max(1, (int)($_GET['page'] ?? 1));

        $paginacao = $this->model->listar($pesquisa, $status, $cargo_id, $page, 20);
        $cargos    = $this->cargoModel->listarActivos();
        $stats     = $this->model->estatisticas();

        View::render('funcionarios.index', [
            'titulo'     => 'Funcionários',
            'activePage' => 'funcionarios',
            'breadcrumb' => ['Funcionários' => null],
            'paginacao'  => $paginacao,
            'cargos'     => $cargos,
            'stats'      => $stats,
            'pesquisa'   => $pesquisa,
            'status'     => $status,
            'cargo_id'   => $cargo_id,
            'flash_sucesso' => $_SESSION['flash_sucesso'] ?? null,
            'flash_erro'    => $_SESSION['flash_erro']    ?? null,
        ]);
        unset($_SESSION['flash_sucesso'], $_SESSION['flash_erro']);
    }

    // ================================================================
    // GET /funcionarios/novo
    // ================================================================
    public function create(): void
    {
        AuthMiddleware::requirePerfil('admin');

        View::render('funcionarios.form', [
            'titulo'        => 'Novo Funcionário',
            'activePage'    => 'funcionarios',
            'breadcrumb'    => ['Funcionários' => '/funcionarios', 'Novo' => null],
            'cargos'        => $this->cargoModel->listarActivos(),
            'funcionario'   => [],
            'erros'         => [],
            'numero_sugerido' => $this->model->proximoNumero(),
            'modo'          => 'criar',
        ]);
    }

    // ================================================================
    // POST /funcionarios/novo
    // ================================================================
    public function store(): void
    {
        AuthMiddleware::requirePerfil('admin');
        $this->verificarCsrf();

        [$dados, $erros] = $this->validar($_POST);

        // Verificar unicidade do BI
        if (empty($erros['bi_numero']) && $this->model->biExiste($dados['bi_numero'])) {
            $erros['bi_numero'] = 'Este número de BI já está registado.';
        }

        if ($erros) {
            View::render('funcionarios.form', [
                'titulo'      => 'Novo Funcionário',
                'activePage'  => 'funcionarios',
                'breadcrumb'  => ['Funcionários' => '/funcionarios', 'Novo' => null],
                'cargos'      => $this->cargoModel->listarActivos(),
                'funcionario' => $dados,
                'erros'       => $erros,
                'numero_sugerido' => $dados['numero_funcionario'] ?? $this->model->proximoNumero(),
                'modo'        => 'criar',
            ]);
            return;
        }

        $dados['criado_por'] = $_SESSION['usuario_id'];

        // Upload foto
        if (!empty($_FILES['foto']['name'])) {
            try {
                $dados['foto_url']  = $this->upload->uploadFoto($_FILES['foto'], $dados['numero_funcionario']);
                $dados['foto_mime'] = $_FILES['foto']['type'];
            } catch (\RuntimeException $e) {
                $erros['foto'] = $e->getMessage();
            }
        }

        // Upload documento de identificação
        if (!empty($_FILES['doc_identificacao']['name'])) {
            try {
                $url = $this->upload->uploadDocumento($_FILES['doc_identificacao'], $dados['numero_funcionario'], 'bi');
                $dados['doc_identificacao_url']  = $url;
                $dados['doc_identificacao_nome'] = $_FILES['doc_identificacao']['name'];
                $dados['doc_identificacao_mime'] = $_FILES['doc_identificacao']['type'];
            } catch (\RuntimeException $e) {
                $erros['doc_identificacao'] = $e->getMessage();
            }
        }

        // Upload documento complementar
        if (!empty($_FILES['doc_complementar']['name'])) {
            try {
                $url = $this->upload->uploadDocumento($_FILES['doc_complementar'], $dados['numero_funcionario'], 'complementar');
                $dados['doc_complementar_url']  = $url;
                $dados['doc_complementar_nome'] = $_FILES['doc_complementar']['name'];
                $dados['doc_complementar_mime'] = $_FILES['doc_complementar']['type'];
            } catch (\RuntimeException $e) {
                $erros['doc_complementar'] = $e->getMessage();
            }
        }

        if ($erros) {
            View::render('funcionarios.form', [
                'titulo'      => 'Novo Funcionário',
                'activePage'  => 'funcionarios',
                'breadcrumb'  => ['Funcionários' => '/funcionarios', 'Novo' => null],
                'cargos'      => $this->cargoModel->listarActivos(),
                'funcionario' => $dados,
                'erros'       => $erros,
                'numero_sugerido' => $dados['numero_funcionario'],
                'modo'        => 'criar',
            ]);
            return;
        }

        $id = $this->model->insert($dados);

        $_SESSION['flash_sucesso'] = 'Funcionário registado com sucesso. Contrato de trabalho gerado automaticamente — pode imprimir ou guardar como PDF.';
        header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/funcionarios/' . $id . '?contrato=1');
        exit;
    }

    // ================================================================
    // GET /funcionarios/{id}/contrato — Contrato Individual de Trabalho
    // gerado com base na Lei do Trabalho de Moçambique (Lei n.º 23/2007,
    // de 1 de Agosto) para o sector privado
    // ================================================================
    public function contrato(string $id): void
    {
        AuthMiddleware::requirePerfil('admin', 'farmaceutico');

        $funcionario = $this->model->findComDetalhes((int) $id);
        if (!$funcionario) {
            http_response_code(404);
            require __DIR__ . '/../../app/Views/errors/404.php';
            return;
        }

        $config = (new \App\Models\Configuracao())->getAllWithDefaults();

        extract([
            'f'      => $funcionario,
            'config' => $config,
            'appUrl' => $_ENV['APP_URL'] ?? '',
        ]);
        require __DIR__ . '/../../app/Views/funcionarios/contrato_pdf.php';
        exit;
    }

    // ================================================================
    // GET /funcionarios/{id}  — ficha do funcionário
    // ================================================================
    public function show(string $id): void
    {
        AuthMiddleware::requirePerfil('admin', 'farmaceutico');

        $funcionario = $this->model->findComDetalhes((int) $id);
        if (!$funcionario) {
            http_response_code(404);
            require __DIR__ . '/../../app/Views/errors/404.php';
            return;
        }

        $documentos = $this->model->documentos((int) $id);
        $historico  = $this->model->historicoCredenciais((int) $id);

        View::render('funcionarios.show', [
            'titulo'      => $funcionario['nome_completo'],
            'activePage'  => 'funcionarios',
            'breadcrumb'  => ['Funcionários' => '/funcionarios', $funcionario['nome_completo'] => null],
            'funcionario' => $funcionario,
            'documentos'  => $documentos,
            'historico'   => $historico,
            'flash_sucesso' => $_SESSION['flash_sucesso'] ?? null,
            'flash_erro'    => $_SESSION['flash_erro']    ?? null,
            'abrir_contrato' => !empty($_GET['contrato']),
        ]);
        unset($_SESSION['flash_sucesso'], $_SESSION['flash_erro']);
    }

    // ================================================================
    // GET /funcionarios/{id}/editar
    // ================================================================
    public function edit(string $id): void
    {
        AuthMiddleware::requirePerfil('admin');

        $funcionario = $this->model->findComDetalhes((int) $id);
        if (!$funcionario) {
            http_response_code(404);
            require __DIR__ . '/../../app/Views/errors/404.php';
            return;
        }

        View::render('funcionarios.form', [
            'titulo'      => 'Editar — ' . $funcionario['nome_completo'],
            'activePage'  => 'funcionarios',
            'breadcrumb'  => [
                'Funcionários'              => '/funcionarios',
                $funcionario['nome_completo'] => '/funcionarios/' . $id,
                'Editar'                    => null,
            ],
            'cargos'        => $this->cargoModel->listarActivos(),
            'funcionario'   => $funcionario,
            'erros'         => [],
            'numero_sugerido' => $funcionario['numero_funcionario'],
            'modo'          => 'editar',
        ]);
    }

    // ================================================================
    // POST /funcionarios/{id}/editar
    // ================================================================
    public function update(string $id): void
    {
        AuthMiddleware::requirePerfil('admin');
        $this->verificarCsrf();

        $funcionario = $this->model->findById((int) $id);
        if (!$funcionario) {
            $_SESSION['flash_erro'] = 'Funcionário não encontrado.';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/funcionarios');
            exit;
        }

        [$dados, $erros] = $this->validar($_POST, (int) $id);

        if ($erros) {
            View::render('funcionarios.form', [
                'titulo'      => 'Editar — ' . ($dados['nome_completo'] ?? ''),
                'activePage'  => 'funcionarios',
                'breadcrumb'  => ['Funcionários' => '/funcionarios', 'Editar' => null],
                'cargos'      => $this->cargoModel->listarActivos(),
                'funcionario' => array_merge($funcionario, $dados),
                'erros'       => $erros,
                'numero_sugerido' => $funcionario['numero_funcionario'],
                'modo'        => 'editar',
            ]);
            return;
        }

        // Upload nova foto (se fornecida)
        if (!empty($_FILES['foto']['name'])) {
            try {
                $dados['foto_url']  = $this->upload->uploadFoto($_FILES['foto'], $funcionario['numero_funcionario']);
                $dados['foto_mime'] = $_FILES['foto']['type'];
            } catch (\RuntimeException $e) {
                $erros['foto'] = $e->getMessage();
            }
        }

        // Upload novo doc identificação
        if (!empty($_FILES['doc_identificacao']['name'])) {
            try {
                $url = $this->upload->uploadDocumento($_FILES['doc_identificacao'], $funcionario['numero_funcionario'], 'bi');
                $dados['doc_identificacao_url']  = $url;
                $dados['doc_identificacao_nome'] = $_FILES['doc_identificacao']['name'];
                $dados['doc_identificacao_mime'] = $_FILES['doc_identificacao']['type'];
            } catch (\RuntimeException $e) {
                $erros['doc_identificacao'] = $e->getMessage();
            }
        }

        $this->model->update((int) $id, $dados);

        $_SESSION['flash_sucesso'] = 'Dados actualizados com sucesso.';
        header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/funcionarios/' . $id);
        exit;
    }

    // ================================================================
    // POST /funcionarios/{id}/credenciais  — atribuir/actualizar acesso
    // ================================================================
    public function atribuirCredenciais(string $id): void
    {
        AuthMiddleware::requirePerfil('admin');
        $this->verificarCsrf();

        $funcionario = $this->model->findComDetalhes((int) $id);
        if (!$funcionario) {
            $_SESSION['flash_erro'] = 'Funcionário não encontrado.';
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/funcionarios');
            exit;
        }

        $email  = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '');
        $perfil = $_POST['perfil'] ?? '';
        $senha  = $_POST['senha']  ?? '';
        $perfisValidos = ['admin', 'farmaceutico', 'caixa', 'tecnico'];

        $erros = [];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))  $erros[] = 'Email inválido.';
        if (!in_array($perfil, $perfisValidos))           $erros[] = 'Perfil inválido.';
        if ($funcionario['usuario_id'] === null && strlen($senha) < 8) $erros[] = 'A senha deve ter pelo menos 8 caracteres.';

        if ($this->usuarioModel->emailExiste($email, (int)($funcionario['usuario_id'] ?? 0))) {
            $erros[] = 'Este email já está em uso por outro utilizador.';
        }

        if ($erros) {
            $_SESSION['flash_erro'] = implode(' ', $erros);
            header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/funcionarios/' . $id);
            exit;
        }

        if ($funcionario['usuario_id']) {
            // Actualizar credenciais existentes
            $upd = ['email' => $email, 'perfil' => $perfil];
            if (!empty($senha) && strlen($senha) >= 8) {
                $upd['senha_hash'] = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);
            }
            $this->usuarioModel->update((int) $funcionario['usuario_id'], $upd);
            $_SESSION['flash_sucesso'] = 'Credenciais actualizadas.';
        } else {
            // Criar novas credenciais
            $this->usuarioModel->criarCredenciais([
                'nome'           => $funcionario['nome_completo'],
                'email'          => $email,
                'perfil'         => $perfil,
                'senha'          => $senha,
                'funcionario_id' => (int) $id,
                'ativo'          => 1,
            ], (int) $_SESSION['usuario_id']);
            $_SESSION['flash_sucesso'] = 'Acesso ao sistema criado com sucesso.';
        }

        header('Location: ' . ($_ENV['APP_URL'] ?? '') . '/funcionarios/' . $id);
        exit;
    }

    // ================================================================
    // Validação dos dados do formulário
    // ================================================================
    private function validar(array $post, int $excluirId = 0): array
    {
        $dados = [
            'nome_completo'        => trim($post['nome_completo']       ?? ''),
            'data_nascimento'      => $post['data_nascimento']           ?? '',
            'sexo'                 => $post['sexo']                      ?? '',
            'estado_civil'         => $post['estado_civil']              ?? null,
            'nacionalidade'        => trim($post['nacionalidade']        ?? 'Moçambicana'),
            'naturalidade'         => trim($post['naturalidade']         ?? '') ?: null,
            'bi_numero'            => trim($post['bi_numero']            ?? ''),
            'bi_validade'          => $post['bi_validade']               ?? null,
            'nuit'                 => trim($post['nuit']                 ?? '') ?: null,
            'nrps'                 => trim($post['nrps']                 ?? '') ?: null,
            'telefone_principal'   => trim($post['telefone_principal']   ?? ''),
            'telefone_alternativo' => trim($post['telefone_alternativo'] ?? '') ?: null,
            'email_pessoal'        => trim($post['email_pessoal']        ?? '') ?: null,
            'endereco'             => trim($post['endereco']             ?? ''),
            'bairro'               => trim($post['bairro']              ?? '') ?: null,
            'cidade'               => trim($post['cidade']               ?? 'Quelimane'),
            'provincia'            => trim($post['provincia']            ?? 'Zambézia'),
            'emergencia_nome'      => trim($post['emergencia_nome']      ?? '') ?: null,
            'emergencia_parentesco'=> trim($post['emergencia_parentesco']?? '') ?: null,
            'emergencia_telefone'  => trim($post['emergencia_telefone']  ?? '') ?: null,
            'cargo_id'             => (int)($post['cargo_id']            ?? 0),
            'data_admissao'        => $post['data_admissao']             ?? '',
            'tipo_contrato'        => $post['tipo_contrato']             ?? 'efectivo',
            'salario'              => (float)($post['salario']           ?? 0),
            'numero_funcionario'   => strtoupper(trim($post['numero_funcionario'] ?? '')),
            'nivel_escolaridade'   => $post['nivel_escolaridade']        ?? null,
            'curso'                => trim($post['curso']                ?? '') ?: null,
            'instituicao'          => trim($post['instituicao']          ?? '') ?: null,
            'ano_conclusao'        => !empty($post['ano_conclusao']) ? (int)$post['ano_conclusao'] : null,
            'status'               => $post['status']                    ?? 'activo',
            'observacoes'          => trim($post['observacoes']          ?? '') ?: null,
        ];

        $erros = [];
        if (strlen($dados['nome_completo']) < 3)    $erros['nome_completo']      = 'Nome obrigatório (mínimo 3 caracteres).';
        if (empty($dados['data_nascimento']))        $erros['data_nascimento']    = 'Data de nascimento obrigatória.';
        if (!in_array($dados['sexo'], ['M','F','outro'])) $erros['sexo']         = 'Seleccione o sexo.';
        if (empty($dados['bi_numero']))              $erros['bi_numero']          = 'Número do BI obrigatório.';
        if (strlen($dados['telefone_principal']) < 8) $erros['telefone_principal'] = 'Telefone inválido.';
        if (empty($dados['endereco']))               $erros['endereco']           = 'Endereço obrigatório.';
        if ($dados['cargo_id'] === 0)                $erros['cargo_id']           = 'Seleccione o cargo.';
        if (empty($dados['data_admissao']))          $erros['data_admissao']      = 'Data de admissão obrigatória.';
        if ($dados['salario'] < 0)                   $erros['salario']            = 'Salário inválido.';
        if (empty($dados['numero_funcionario']))     $erros['numero_funcionario'] = 'Número de funcionário obrigatório.';

        // Validar email pessoal se preenchido
        if ($dados['email_pessoal'] && !filter_var($dados['email_pessoal'], FILTER_VALIDATE_EMAIL)) {
            $erros['email_pessoal'] = 'Email pessoal inválido.';
        }

        return [$dados, $erros];
    }

    // ----------------------------------------------------------------
    private function verificarCsrf(): void
    {
        $token = $_POST['_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            exit('Token de segurança inválido.');
        }
    }
}
