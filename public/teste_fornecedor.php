<?php
// Coloque em public/teste_fornecedor.php e aceda no browser

require_once dirname(__DIR__) . '/vendor/autoload.php';
$dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

echo "<h2>Diagnóstico Fornecedores</h2>";

// Teste 1: Model existe?
$modelPath = dirname(__DIR__) . '/app/Models/Fornecedor.php';
echo "<h3>1. Fornecedor.php (Model)</h3>";
echo file_exists($modelPath) ? "✅ Existe<br>" : "❌ NÃO encontrado em: $modelPath<br>";

// Teste 2: Controller existe?
$ctrlPath = dirname(__DIR__) . '/app/Controllers/FornecedorController.php';
echo "<h3>2. FornecedorController.php</h3>";
echo file_exists($ctrlPath) ? "✅ Existe<br>" : "❌ NÃO encontrado em: $ctrlPath<br>";

// Teste 3: Views existem?
echo "<h3>3. Views</h3>";
$views = [
    'app/Views/fornecedores/index.php',
    'app/Views/fornecedores/form.php',
    'app/Views/fornecedores/show.php',
];
foreach ($views as $v) {
    $path = dirname(__DIR__) . '/' . $v;
    echo (file_exists($path) ? "✅" : "❌ NÃO encontrado:") . " $v<br>";
}

// Teste 4: Rotas têm Fornecedor?
echo "<h3>4. routes/web.php</h3>";
$routes = file_get_contents(dirname(__DIR__) . '/routes/web.php');
echo strpos($routes, 'FornecedorController') !== false
    ? "✅ Rotas de fornecedores registadas<br>"
    : "❌ Rotas NÃO encontradas — precisa adicionar ao routes/web.php<br>";

// Teste 5: Instanciar o model directamente
echo "<h3>5. Instanciar Model</h3>";
try {
    require_once dirname(__DIR__) . '/core/Database.php';
    require_once dirname(__DIR__) . '/app/Models/Fornecedor.php';
    $m = new \App\Models\Fornecedor();
    $stats = $m->estatisticas();
    echo "✅ Model OK — Total fornecedores: " . $stats['total'] . "<br>";
} catch (\Throwable $e) {
    echo "❌ Erro: <strong>" . $e->getMessage() . "</strong><br>";
    echo "Em: " . $e->getFile() . ":" . $e->getLine() . "<br>";
}

// Teste 6: View::render consegue encontrar a view?
echo "<h3>6. View::render path</h3>";
$viewFile = dirname(__DIR__) . '/app/Views/fornecedores/index.php';
echo file_exists($viewFile)
    ? "✅ View index.php encontrada<br>"
    : "❌ View NÃO encontrada — verifique o nome da pasta (deve ser 'fornecedores' em minúsculas)<br>";

// Mostrar conteúdo da pasta views
echo "<h3>7. Pasta app/Views/</h3>";
$viewsDir = dirname(__DIR__) . '/app/Views/';
foreach (scandir($viewsDir) as $d) {
    if ($d === '.' || $d === '..') continue;
    echo "📁 $d<br>";
}
