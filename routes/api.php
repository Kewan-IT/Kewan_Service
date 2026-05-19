<?php
/** @var \Core\Router $router */

// Endpoints JSON para chamadas AJAX do frontend
$router->get('/api/produtos/pesquisar',    ['ProdutoController', 'pesquisarAjax']);
$router->get('/api/clientes/pesquisar',    ['ClienteController', 'pesquisarAjax']);
$router->get('/api/produtos/{id}/preco',   ['ProdutoController', 'precoAjax']);
$router->get('/api/estoque/alertas',       ['ProdutoController', 'alertasAjax']);
$router->get('/api/dashboard/resumo',      ['DashboardController','resumoAjax']);
