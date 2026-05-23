<?php
// Coloque em public/teste_rota.php e aceda:
// http://SEU-SITE/teste_rota.php

require_once dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Simular sessão autenticada para testar o controller
session_name($_ENV['SESSION_NAME'] ?? 'kewanfarma');
session_start();

echo "<h2>Diagnóstico de Rotas</h2>";

// Teste 1: O ficheiro ProdutoController existe e tem o método certo?
$controllerPath = dirname(__DIR__) . '/app/Controllers/ProdutoController.php';
echo "<h3>1. ProdutoController.php</h3>";
if (file_exists($controllerPath)) {
    $src = file_get_contents($controllerPath);
    echo "✅ Ficheiro existe<br>";
    echo "pesquisarAjax: " . (strpos($src, 'pesquisarAjax') !== false ? '✅ EXISTE' : '❌ NÃO EXISTE — ainda tem o método antigo!') . "<br>";
    echo "pesquisar (antigo): " . (preg_match('/public function pesquisar\b/', $src) ? '⚠️ ainda existe o método antigo' : '✅ removido') . "<br>";
    
    // Mostrar os métodos públicos
    preg_match_all('/public function (\w+)/', $src, $m);
    echo "Métodos: " . implode(', ', $m[1]) . "<br>";
} else {
    echo "❌ Ficheiro NÃO encontrado em: $controllerPath<br>";
}

// Teste 2: O ficheiro api.php existe?
$apiRoutesPath = dirname(__DIR__) . '/routes/api.php';
echo "<h3>2. routes/api.php</h3>";
if (file_exists($apiRoutesPath)) {
    echo "✅ Existe<br>";
    echo "<pre>" . htmlspecialchars(file_get_contents($apiRoutesPath)) . "</pre>";
} else {
    echo "❌ NÃO encontrado<br>";
}

// Teste 3: Tentar chamar directamente o método (sem routing)
echo "<h3>3. Chamada directa ao método pesquisarAjax</h3>";
try {
    require_once dirname(__DIR__) . '/core/Database.php';
    require_once dirname(__DIR__) . '/app/Models/Produto.php';
    
    $model = new \App\Models\Produto();
    $produtos = $model->pesquisarParaVenda('a', 5);
    echo "✅ Model funciona. Resultados: " . count($produtos) . "<br>";
    echo "<pre>" . json_encode($produtos[0] ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "</pre>";
} catch (Throwable $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
    echo "Em: " . $e->getFile() . ":" . $e->getLine() . "<br>";
}

// Teste 4: Simular o URL exato que o JS chama
echo "<h3>4. URL que o JavaScript chama</h3>";
$appUrl = $_ENV['APP_URL'] ?? '(vazio)';
echo "APP_URL = <strong>$appUrl</strong><br>";
echo "URL da pesquisa = <strong>{$appUrl}/api/produtos/pesquisar?q=a</strong><br>";
echo "<a href='{$appUrl}/api/produtos/pesquisar?q=a' target='_blank'>Clicar aqui para testar directamente →</a><br>";
