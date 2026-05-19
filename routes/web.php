<?php
/** @var \Core\Router $router */

// Autenticação
$router->get('/auth/login',         ['AuthController',      'showLogin']);
$router->post('/auth/login',        ['AuthController',      'login']);
$router->get('/auth/logout',        ['AuthController',      'logout']);
$router->get('/auth/recuperar',     ['AuthController',      'showRecuperar']);
$router->post('/auth/recuperar',    ['AuthController',      'recuperar']);

// Dashboard
$router->get('/dashboard',          ['DashboardController', 'index']);
$router->get('/',                   ['DashboardController', 'index']);

// Funcionários
$router->get('/funcionarios',              ['FuncionarioController', 'index']);
$router->get('/funcionarios/novo',         ['FuncionarioController', 'create']);
$router->post('/funcionarios/novo',        ['FuncionarioController', 'store']);
$router->get('/funcionarios/{id}',         ['FuncionarioController', 'show']);
$router->get('/funcionarios/{id}/editar',  ['FuncionarioController', 'edit']);
$router->post('/funcionarios/{id}/editar', ['FuncionarioController', 'update']);
$router->post('/funcionarios/{id}/credenciais', ['FuncionarioController', 'atribuirCredenciais']);

// Produtos
$router->get('/produtos',              ['ProdutoController', 'index']);
$router->get('/produtos/novo',         ['ProdutoController', 'create']);
$router->post('/produtos/novo',        ['ProdutoController', 'store']);
$router->get('/produtos/{id}',         ['ProdutoController', 'show']);
$router->get('/produtos/{id}/editar',  ['ProdutoController', 'edit']);
$router->post('/produtos/{id}/editar', ['ProdutoController', 'update']);

// Clientes
$router->get('/clientes',              ['ClienteController', 'index']);
$router->get('/clientes/novo',         ['ClienteController', 'create']);
$router->post('/clientes/novo',        ['ClienteController', 'store']);
$router->get('/clientes/{id}',         ['ClienteController', 'show']);

// Vendas
$router->get('/vendas',              ['VendaController', 'index']);
$router->get('/vendas/nova',         ['VendaController', 'create']);
$router->post('/vendas/nova',        ['VendaController', 'store']);
$router->get('/vendas/{id}',         ['VendaController', 'show']);
$router->post('/vendas/{id}/cancelar', ['VendaController', 'cancelar']);

// Compras
$router->get('/compras',              ['CompraController', 'index']);
$router->get('/compras/nova',         ['CompraController', 'create']);
$router->post('/compras/nova',        ['CompraController', 'store']);
$router->get('/compras/{id}',         ['CompraController', 'show']);
$router->post('/compras/{id}/receber',['CompraController', 'receberMercadoria']);

// Caixa
$router->get('/caixa',                ['CaixaController', 'index']);
$router->post('/caixa/abrir',         ['CaixaController', 'abrir']);
$router->post('/caixa/fechar',        ['CaixaController', 'fechar']);
$router->post('/caixa/movimento',     ['CaixaController', 'registarMovimento']);

// Relatórios
$router->get('/relatorios',                 ['RelatorioController', 'index']);
$router->get('/relatorios/vendas',          ['RelatorioController', 'vendas']);
$router->get('/relatorios/stock',           ['RelatorioController', 'stock']);
$router->get('/relatorios/funcionarios',    ['RelatorioController', 'funcionarios']);
$router->get('/relatorios/lotes-a-vencer',  ['RelatorioController', 'lotesAVencer']);

// Configurações (só admin)
$router->get('/configuracoes',        ['ConfiguracaoController', 'index']);
$router->post('/configuracoes',       ['ConfiguracaoController', 'update']);
