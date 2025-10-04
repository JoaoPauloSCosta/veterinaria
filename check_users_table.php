<?php
require_once 'src/helpers/db.php';

echo "<h2>Verificação da Estrutura da Tabela Users</h2>";

try {
    $pdo = DB::getConnection();
    
    // 1. Verificar estrutura da tabela users
    echo "<h3>1. Estrutura da tabela users:</h3>";
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll();
    foreach ($columns as $col) {
        echo "- {$col['Field']}: {$col['Type']} {$col['Null']} {$col['Key']} {$col['Default']} {$col['Extra']}<br>";
    }
    
    // 2. Verificar se a tabela notifications já existe
    echo "<h3>2. Verificando se a tabela notifications existe:</h3>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'notifications'");
    if ($stmt->fetch()) {
        echo "✓ Tabela notifications já existe<br>";
        
        // Mostrar estrutura atual
        echo "<h4>Estrutura atual da notifications:</h4>";
        $stmt = $pdo->query("DESCRIBE notifications");
        $columns = $stmt->fetchAll();
        foreach ($columns as $col) {
            echo "- {$col['Field']}: {$col['Type']} {$col['Null']} {$col['Key']} {$col['Default']} {$col['Extra']}<br>";
        }
    } else {
        echo "✗ Tabela notifications NÃO existe<br>";
    }
    
    // 3. Verificar todas as tabelas do banco
    echo "<h3>3. Todas as tabelas do banco:</h3>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll();
    foreach ($tables as $table) {
        $tableName = array_values($table)[0];
        echo "- $tableName<br>";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>