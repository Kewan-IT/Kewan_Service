<?php
/**
 * Script de Importação - 200 Produtos Farmacêuticos
 * Executa a inserção dos produtos no banco de dados
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuração do banco de dados
$host = 'localhost';
$user = 'root';
$password = 'root';
$database = 'kewanfarma';

try {
    // Conectar ao banco de dados
    $pdo = new PDO(
        "mysql:host=$host;charset=utf8mb4",
        $user,
        $password
    );
    
    echo "✓ Conectado ao MySQL\n";
    
    // Seleccionar a base de dados
    $pdo->exec("USE $database");
    echo "✓ Base de dados '$database' selecionada\n\n";
    
    // Ler o arquivo SQL
    $sqlFile = __DIR__ . '/../database/seeds_produtos_200.sql';
    
    if (!file_exists($sqlFile)) {
        die("❌ Arquivo não encontrado: $sqlFile\n");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Dividir em statements
    $statements = array_filter(
        array_map('trim', preg_split('/;/', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', trim($stmt));
        }
    );
    
    echo "📊 Total de declarações SQL: " . count($statements) . "\n\n";
    
    // Executar cada statement
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $index => $statement) {
        try {
            // Adicionar ponto e vírgula se necessário
            $statement = trim($statement);
            if (!str_ends_with($statement, ';')) {
                $statement .= ';';
            }
            
            $pdo->exec($statement);
            $successCount++;
            
            // Mostrar progresso a cada 5 statements
            if (($index + 1) % 5 == 0) {
                echo "✓ " . ($index + 1) . " statements executados\n";
            }
        } catch (Exception $e) {
            $errorCount++;
            echo "❌ Erro no statement " . ($index + 1) . ": " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "✓ IMPORTAÇÃO CONCLUÍDA COM SUCESSO!\n";
    echo str_repeat("=", 60) . "\n";
    echo "Statements executados com sucesso: $successCount\n";
    echo "Erros encontrados: $errorCount\n\n";
    
    // Verificar quantidade de produtos inseridos
    $result = $pdo->query("SELECT COUNT(*) as total FROM produtos");
    $row = $result->fetch(PDO::FETCH_ASSOC);
    
    echo "📦 Total de produtos na base: " . $row['total'] . "\n";
    
    // Verificar categorias
    $result = $pdo->query("SELECT COUNT(*) as total FROM categorias");
    $row = $result->fetch(PDO::FETCH_ASSOC);
    
    echo "📂 Total de categorias: " . $row['total'] . "\n";
    
    // Verificar fornecedores
    $result = $pdo->query("SELECT COUNT(*) as total FROM fornecedores");
    $row = $result->fetch(PDO::FETCH_ASSOC);
    
    echo "🏭 Total de fornecedores: " . $row['total'] . "\n";
    
    echo "\n✅ Dados de exemplo inseridos com sucesso!\n";
    
} catch (Exception $e) {
    die("❌ Erro na conexão: " . $e->getMessage() . "\n");
}
?>
