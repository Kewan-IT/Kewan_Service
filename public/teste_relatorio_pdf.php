<?php
// Coloque em public/teste_relatorio_pdf.php e aceda no browser

require_once dirname(__DIR__) . '/vendor/autoload.php';
$dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

session_name($_ENV['SESSION_NAME'] ?? 'kewanfarma');
session_start();

echo "<h2>Diagnóstico — Relatório PDF</h2>";

// 1. Controller existe?
$ctrl = dirname(__DIR__) . '/app/Controllers/RelatorioController.php';
echo "<h3>1. RelatorioController.php</h3>";
if (file_exists($ctrl)) {
    $src = file_get_contents($ctrl);
    echo "✅ Existe<br>";
    echo "vendasPdf: " . (strpos($src, 'vendasPdf') !== false ? '✅ método existe' : '❌ método NÃO existe') . "<br>";
} else {
    echo "❌ NÃO encontrado<br>";
}

// 2. View PDF existe?
$view = dirname(__DIR__) . '/app/Views/relatorios/vendas_pdf.php';
echo "<h3>2. vendas_pdf.php</h3>";
echo file_exists($view) ? "✅ Existe<br>" : "❌ NÃO encontrada em: $view<br>";

// 3. Rota registada?
$routes = file_get_contents(dirname(__DIR__) . '/routes/web.php');
echo "<h3>3. Rota /relatorios/vendas/pdf</h3>";
echo (strpos($routes, 'vendasPdf') !== false && strpos($routes, '/relatorios/vendas/pdf') !== false)
    ? "✅ Rota registada<br>"
    : "❌ Rota NÃO registada — adicione ao routes/web.php<br>";

// 4. Testar a query directamente
echo "<h3>4. Query de vendas</h3>";
try {
    require_once dirname(__DIR__) . '/core/Database.php';
    $db = \Core\Database::getInstance();

    $stmt = $db->prepare("
        SELECT COUNT(*) AS total, COALESCE(SUM(total),0) AS valor
        FROM vendas v
        WHERE DATE(v.criado_em) BETWEEN :ini AND :fim
    ");
    $stmt->execute(['ini' => date('Y-m-01'), 'fim' => date('Y-m-d')]);
    $row = $stmt->fetch();
    echo "✅ Query OK — {$row['total']} vendas, {$row['valor']} MZN<br>";
} catch (\Throwable $e) {
    echo "❌ Erro: <strong>" . $e->getMessage() . "</strong><br>";
    echo "Em: " . $e->getFile() . ":" . $e->getLine() . "<br>";
}

// 5. Erro PHP (últimos logs)
echo "<h3>5. Erro PHP</h3>";
$logFile = ini_get('error_log');
if ($logFile && file_exists($logFile)) {
    $lines = array_slice(file($logFile), -15);
    echo "<pre style='font-size:11px;background:#f8f8f8;padding:10px'>";
    foreach ($lines as $line) {
        if (stripos($line, 'relat') !== false || stripos($line, 'error') !== false) {
            echo htmlspecialchars($line);
        }
    }
    echo "</pre>";
} else {
    echo "Log não encontrado em: $logFile<br>";
    echo "Verifique manualmente: <code>tail -20 /var/log/apache2/error.log</code><br>";
}
