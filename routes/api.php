<?php
/** @var \Core\Router $router */
$router->get('/api/produtos/pesquisar', ['ApiController', 'pesquisarProdutos']);
$router->get('/api/clientes/pesquisar', ['ApiController', 'pesquisarClientes']);
$router->get('/api/estoque/alertas',    ['ApiController', 'alertasEstoque']);
$router->get('/api/dashboard/resumo',   ['ApiController', 'resumoDashboard']);
$router->get('/api/estoque/alertas', ['VendaController', 'alertasStock']);
