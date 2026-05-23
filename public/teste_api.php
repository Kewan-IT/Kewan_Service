<?php
// Ficheiro de diagnóstico — coloque em public/teste_api.php
// Aceda: http://SEU-SITE/teste_api.php?q=nome_produto

// Carregar o ambiente
require_once dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Ligar à base de dados directamente
$host = $_ENV['DB_HOST'] ?? 'localhost';
$db   = $_ENV['DB_NAME'] ?? '';
$user = $_ENV['DB_USER'] ?? '';
$pass = $_ENV['DB_PASS'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die(json_encode(['erro' => 'DB: ' . $e->getMessage()]));
}

$q = trim($_GET['q'] ?? 'a');

// Teste 1: contar todos os produtos activos com stock
$total = $pdo->query("SELECT COUNT(*) FROM produtos WHERE ativo = 1 AND estoque_actual > 0")->fetchColumn();

// Teste 2: pesquisa real
$stmt = $pdo->prepare("
    SELECT p.id, p.nome, p.preco_venda, p.estoque_actual, p.requer_receita,
           c.nome AS categoria
    FROM produtos p
    JOIN categorias c ON c.id = p.categoria_id
    WHERE p.ativo = 1
      AND p.estoque_actual > 0
      AND (p.nome LIKE :q OR p.codigo_barras LIKE :q OR p.principio_ativo LIKE :q)
    ORDER BY p.nome
    LIMIT 10
");
$stmt->execute(['q' => '%' . $q . '%']);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode([
    'total_produtos_com_stock' => $total,
    'query'                    => $q,
    'resultados'               => count($produtos),
    'produtos'                 => $produtos,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
