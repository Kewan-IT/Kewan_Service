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
$router->get('/dashboard',            ['DashboardController', 'index']);

// ── Funcionários ─────────────────────────────────────────────────
$router->get('/funcionarios',                   ['FuncionarioController', 'index']);
$router->get('/funcionarios/novo',              ['FuncionarioController', 'create']);
$router->post('/funcionarios/novo',             ['FuncionarioController', 'store']);
$router->get('/funcionarios/{id}',              ['FuncionarioController', 'show']);
$router->get('/funcionarios/{id}/editar',       ['FuncionarioController', 'edit']);
$router->post('/funcionarios/{id}/editar',      ['FuncionarioController', 'update']);
$router->post('/funcionarios/{id}/credenciais', ['FuncionarioController', 'atribuirCredenciais']);

// ── Produtos ─────────────────────────────────────────────────────
$router->get('/produtos',              ['ProdutoController', 'index']);
$router->get('/produtos/novo',         ['ProdutoController', 'create']);
$router->post('/produtos/novo',        ['ProdutoController', 'store']);
$router->get('/produtos/{id}',         ['ProdutoController', 'show']);
$router->get('/produtos/{id}/editar',  ['ProdutoController', 'edit']);
$router->post('/produtos/{id}/editar', ['ProdutoController', 'update']);
$router->post('/produtos/{id}/lote',   ['ProdutoController', 'adicionarLote']);

// ── Clientes ─────────────────────────────────────────────────────
$router->get('/clientes',              ['ClienteController', 'index']);
$router->get('/clientes/novo',         ['ClienteController', 'create']);
$router->post('/clientes/novo',        ['ClienteController', 'store']);
$router->get('/clientes/{id}',         ['ClienteController', 'show']);
$router->get('/clientes/{id}/editar',  ['ClienteController', 'edit']);
$router->post('/clientes/{id}/editar', ['ClienteController', 'update']);

// ── Vendas ───────────────────────────────────────────────────────
$router->post('/vendas/nova/carrinho', ['VendaController', 'salvarCarrinho']);
$router->get('/vendas/nova',           ['VendaController', 'create']);
$router->post('/vendas/nova',          ['VendaController', 'store']);
$router->get('/vendas',                ['VendaController', 'index']);
$router->get('/vendas/{id}',           ['VendaController', 'show']);
$router->get('/vendas/{id}/detalhe',   ['VendaController', 'show']);
$router->get('/vendas/{id}/talao',     ['VendaController', 'talao']);
$router->post('/vendas/{id}/cancelar', ['VendaController', 'cancelar']);

// ── Compras ──────────────────────────────────────────────────────
$router->get('/compras',               ['CompraController', 'index']);
$router->get('/compras/nova',          ['CompraController', 'create']);
$router->post('/compras/nova',         ['CompraController', 'store']);
$router->get('/compras/{id}/pdf',      ['CompraController', 'pdf']);
$router->get('/compras/{id}',          ['CompraController', 'show']);
$router->post('/compras/{id}/receber', ['CompraController', 'receberMercadoria']);
$router->post('/compras/{id}/cancelar',['CompraController', 'cancelar']);

// ── Fornecedores ─────────────────────────────────────────────────
$router->get('/fornecedores',              ['FornecedorController', 'index']);
$router->get('/fornecedores/novo',         ['FornecedorController', 'create']);
$router->post('/fornecedores/novo',        ['FornecedorController', 'store']);
$router->get('/fornecedores/{id}',         ['FornecedorController', 'show']);
$router->get('/fornecedores/{id}/editar',  ['FornecedorController', 'edit']);
$router->post('/fornecedores/{id}/editar', ['FornecedorController', 'update']);
$router->post('/fornecedores/{id}/toggle', ['FornecedorController', 'toggle']);

// ── Caixa ────────────────────────────────────────────────────────
$router->get('/caixa',               ['CaixaController', 'index']);
$router->post('/caixa/abrir',        ['CaixaController', 'abrir']);
$router->post('/caixa/fechar',       ['CaixaController', 'fechar']);
$router->post('/caixa/movimento',    ['CaixaController', 'movimento']);
$router->get('/caixa/{id}',          ['CaixaController', 'show']);

// ── Relatórios ───────────────────────────────────────────────────
$router->get('/relatorios',                ['RelatorioController', 'index']);
$router->get('/relatorios/vendas',         ['RelatorioController', 'vendas']);
$router->get('/relatorios/vendas/pdf',     ['RelatorioController', 'vendasPdf']);
$router->get('/relatorios/stock',          ['RelatorioController', 'stock']);
$router->get('/relatorios/stock/pdf',      ['RelatorioController', 'stockPdf']);
$router->get('/relatorios/lotes-a-vencer', ['RelatorioController', 'lotesAVencer']);
$router->get('/relatorios/funcionarios',   ['RelatorioController', 'funcionarios']);
$router->get('/relatorios/lotes-a-vencer/pdf', ['RelatorioController', 'lotesAVencerPdf']);
$router->get('/relatorios/funcionarios/pdf',   ['RelatorioController', 'funcionariosPdf']);
$router->get('/relatorios/stock/pdf',          ['RelatorioController', 'stockPdf']);

// ── Configurações ────────────────────────────────────────────────
$router->get('/configuracoes',             ['ConfiguracaoController', 'index']);
$router->post('/configuracoes',            ['ConfiguracaoController', 'update']);
$router->post('/configuracoes/fazer-backup',    ['ConfiguracaoController', 'fazerBackup']);
$router->post('/configuracoes/deletar-backup',  ['ConfiguracaoController', 'deletarBackup']);
$router->get('/configuracoes/download-backup',  ['ConfiguracaoController', 'downloadBackup']);

// ── API AJAX ─────────────────────────────────────────────────────
$router->get('/api/produtos/pesquisar',  ['ApiController', 'pesquisarProdutos']);
$router->get('/api/clientes/pesquisar',  ['ApiController', 'pesquisarClientes']);
$router->get('/api/estoque/alertas',     ['ApiController', 'alertasEstoque']);
$router->get('/api/dashboard/resumo',    ['ApiController', 'resumoDashboard']);
