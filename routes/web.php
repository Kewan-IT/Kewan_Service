<?php
/** @var \Core\Router $router */

// ── Autenticação ─────────────────────────────────────────────────
$router->get('/auth/login',           ['AuthController', 'showLogin']);
$router->post('/auth/login',          ['AuthController', 'login']);
$router->get('/auth/logout',          ['AuthController', 'logout']);
$router->get('/auth/recuperar',       ['AuthController', 'showRecuperar']);
$router->post('/auth/recuperar',      ['AuthController', 'recuperar']);

// ── Dashboard ────────────────────────────────────────────────────
$router->get('/',                     ['DashboardController', 'index']);
$router->get('/dashboard',            ['DashboardController', 'index'], ['middleware' => 'auth']);

// ── Funcionários ───────────────────────────────────────────────────
// Apenas admin tem acesso
$router->get('/funcionarios',                   ['FuncionarioController', 'index'], ['funcionalidade' => 'funcionarios']);
$router->get('/funcionarios/novo',              ['FuncionarioController', 'create'], ['funcionalidade' => 'funcionarios']);
$router->post('/funcionarios/novo',             ['FuncionarioController', 'store'], ['funcionalidade' => 'funcionarios']);
$router->get('/funcionarios/{id}',              ['FuncionarioController', 'show'], ['funcionalidade' => 'funcionarios']);
$router->get('/funcionarios/{id}/editar',       ['FuncionarioController', 'edit'], ['funcionalidade' => 'funcionarios']);
$router->post('/funcionarios/{id}/editar',      ['FuncionarioController', 'update'], ['funcionalidade' => 'funcionarios']);
$router->post('/funcionarios/{id}/credenciais', ['FuncionarioController', 'atribuirCredenciais'], ['funcionalidade' => 'funcionarios']);

// ── Produtos ────────────────────────────────────────────────────
// Apenas admin tem acesso
$router->get('/produtos',              ['ProdutoController', 'index'], ['funcionalidade' => 'produtos']);
$router->get('/produtos/novo',         ['ProdutoController', 'create'], ['funcionalidade' => 'produtos']);
$router->post('/produtos/novo',        ['ProdutoController', 'store'], ['funcionalidade' => 'produtos']);
$router->get('/produtos/{id}',         ['ProdutoController', 'show'], ['funcionalidade' => 'produtos']);
$router->get('/produtos/{id}/editar',  ['ProdutoController', 'edit'], ['funcionalidade' => 'produtos']);
$router->post('/produtos/{id}/editar', ['ProdutoController', 'update'], ['funcionalidade' => 'produtos']);
$router->post('/produtos/{id}/lote',   ['ProdutoController', 'adicionarLote'], ['funcionalidade' => 'produtos']);

// ── Clientes ────────────────────────────────────────────────────
// Apenas admin tem acesso
$router->get('/clientes',              ['ClienteController', 'index'], ['funcionalidade' => 'clientes']);
$router->get('/clientes/novo',         ['ClienteController', 'create'], ['funcionalidade' => 'clientes']);
$router->post('/clientes/novo',        ['ClienteController', 'store'], ['funcionalidade' => 'clientes']);
$router->get('/clientes/{id}',         ['ClienteController', 'show'], ['funcionalidade' => 'clientes']);
$router->get('/clientes/{id}/editar',  ['ClienteController', 'edit'], ['funcionalidade' => 'clientes']);
$router->post('/clientes/{id}/editar', ['ClienteController', 'update'], ['funcionalidade' => 'clientes']);

// ── Vendas ──────────────────────────────────────────────────────
// Caixa, Técnico, Farmacêutico e Admin têm acesso
$router->post('/vendas/nova/carrinho', ['VendaController', 'salvarCarrinho'], ['funcionalidade' => 'vendas']);
$router->get('/vendas/nova',           ['VendaController', 'create'], ['funcionalidade' => 'vendas']);
$router->post('/vendas/nova',          ['VendaController', 'store'], ['funcionalidade' => 'vendas']);
$router->get('/vendas',                ['VendaController', 'index'], ['funcionalidade' => 'vendas']);
$router->get('/vendas/{id}',           ['VendaController', 'show'], ['funcionalidade' => 'vendas']);
$router->get('/vendas/{id}/detalhe',   ['VendaController', 'show'], ['funcionalidade' => 'vendas']);
$router->get('/vendas/{id}/talao',     ['VendaController', 'talao'], ['funcionalidade' => 'vendas']);
$router->post('/vendas/{id}/cancelar', ['VendaController', 'cancelar'], ['funcionalidade' => 'vendas']);

// ── Compras ─────────────────────────────────────────────────────
// Apenas admin tem acesso
$router->get('/compras',               ['CompraController', 'index'], ['funcionalidade' => 'compras']);
$router->get('/compras/nova',          ['CompraController', 'create'], ['funcionalidade' => 'compras']);
$router->post('/compras/nova',         ['CompraController', 'store'], ['funcionalidade' => 'compras']);
$router->get('/compras/{id}/pdf',      ['CompraController', 'pdf'], ['funcionalidade' => 'compras']);
$router->get('/compras/{id}',          ['CompraController', 'show'], ['funcionalidade' => 'compras']);
$router->post('/compras/{id}/receber', ['CompraController', 'receberMercadoria'], ['funcionalidade' => 'compras']);
$router->post('/compras/{id}/cancelar',['CompraController', 'cancelar'], ['funcionalidade' => 'compras']);

// ── Fornecedores ────────────────────────────────────────────────
// Apenas admin tem acesso
$router->get('/fornecedores',              ['FornecedorController', 'index'], ['funcionalidade' => 'fornecedores']);
$router->get('/fornecedores/novo',         ['FornecedorController', 'create'], ['funcionalidade' => 'fornecedores']);
$router->post('/fornecedores/novo',        ['FornecedorController', 'store'], ['funcionalidade' => 'fornecedores']);
$router->get('/fornecedores/{id}',         ['FornecedorController', 'show'], ['funcionalidade' => 'fornecedores']);
$router->get('/fornecedores/{id}/editar',  ['FornecedorController', 'edit'], ['funcionalidade' => 'fornecedores']);
$router->post('/fornecedores/{id}/editar', ['FornecedorController', 'update'], ['funcionalidade' => 'fornecedores']);
$router->post('/fornecedores/{id}/toggle', ['FornecedorController', 'toggle'], ['funcionalidade' => 'fornecedores']);

// ── Caixa ───────────────────────────────────────────────────────
// Caixa, Técnico, Farmacêutico e Admin têm acesso
$router->get('/caixa',               ['CaixaController', 'index'], ['funcionalidade' => 'caixa']);
$router->post('/caixa/abrir',        ['CaixaController', 'abrir'], ['funcionalidade' => 'caixa']);
$router->post('/caixa/fechar',       ['CaixaController', 'fechar'], ['funcionalidade' => 'caixa']);
$router->post('/caixa/movimento',    ['CaixaController', 'movimento'], ['funcionalidade' => 'caixa']);
$router->get('/caixa/{id}',          ['CaixaController', 'show'], ['funcionalidade' => 'caixa']);
// ── Relatórios ──────────────────────────────────────────────────
// Apenas admin tem acesso
$router->get('/relatorios',                ['RelatorioController', 'index'], ['funcionalidade' => 'relatorios']);
$router->get('/relatorios/vendas',         ['RelatorioController', 'vendas'], ['funcionalidade' => 'relatorios']);
$router->get('/relatorios/vendas/pdf',     ['RelatorioController', 'vendasPdf'], ['funcionalidade' => 'relatorios']);
$router->get('/relatorios/stock',          ['RelatorioController', 'stock'], ['funcionalidade' => 'relatorios']);
$router->get('/relatorios/stock/pdf',      ['RelatorioController', 'stockPdf'], ['funcionalidade' => 'relatorios']);
$router->get('/relatorios/lotes-a-vencer', ['RelatorioController', 'lotesAVencer'], ['funcionalidade' => 'relatorios']);
$router->get('/relatorios/funcionarios',   ['RelatorioController', 'funcionarios'], ['funcionalidade' => 'relatorios']);
$router->get('/relatorios/lotes-a-vencer/pdf', ['RelatorioController', 'lotesAVencerPdf'], ['funcionalidade' => 'relatorios']);
$router->get('/relatorios/funcionarios/pdf',   ['RelatorioController', 'funcionariosPdf'], ['funcionalidade' => 'relatorios']);

// ── Configurações ───────────────────────────────────────────────
$router->get('/configuracoes',             ['ConfiguracaoController', 'index'], ['funcionalidade' => 'configuracoes']);
$router->post('/configuracoes',            ['ConfiguracaoController', 'update'], ['funcionalidade' => 'configuracoes']);
// Backup manual é acessível para Caixa, Técnico, Farmacêutico e Admin
$router->post('/configuracoes/fazer-backup',    ['ConfiguracaoController', 'fazerBackup'], ['funcionalidade' => 'backup']);
$router->post('/configuracoes/deletar-backup',  ['ConfiguracaoController', 'deletarBackup'], ['funcionalidade' => 'backup']);
$router->get('/configuracoes/download-backup',  ['ConfiguracaoController', 'downloadBackup'], ['funcionalidade' => 'backup']);

// ── API AJAX ────────────────────────────────────────────────────
// Pesquisa de produtos e clientes — acessível para vendas
$router->get('/api/produtos/pesquisar',  ['ApiController', 'pesquisarProdutos'], ['funcionalidade' => 'vendas']);
$router->get('/api/clientes/pesquisar',  ['ApiController', 'pesquisarClientes'], ['funcionalidade' => 'vendas']);
// Alertas de estoque — apenas admin
$router->get('/api/estoque/alertas',     ['ApiController', 'alertasEstoque'], ['funcionalidade' => 'produtos']);
// Resumo do dashboard — acessível a todos os autenticados
$router->get('/api/dashboard/resumo',    ['ApiController', 'resumoDashboard']);
