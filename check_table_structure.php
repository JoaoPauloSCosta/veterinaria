<?php
require_once 'src/helpers/db.php';

echo "<h2>Verificação da Estrutura das Tabelas</h2>";

try {
    $pdo = DB::getConnection();
    
    // 1. Verificar estrutura da tabela veterinarian_specialties
    echo "<h3>1. Estrutura da tabela veterinarian_specialties:</h3>";
    $stmt = $pdo->query("DESCRIBE veterinarian_specialties");
    $columns = $stmt->fetchAll();
    foreach ($columns as $col) {
        echo "- {$col['Field']}: {$col['Type']} {$col['Null']} {$col['Key']} {$col['Default']} {$col['Extra']}<br>";
    }
    
    // 2. Verificar foreign keys da tabela veterinarian_specialties
    echo "<h3>2. Foreign Keys da tabela veterinarian_specialties:</h3>";
    $stmt = $pdo->query("
        SELECT 
            CONSTRAINT_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'veterinarian_specialties' 
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    $fks = $stmt->fetchAll();
    if ($fks) {
        foreach ($fks as $fk) {
            echo "- {$fk['CONSTRAINT_NAME']}: {$fk['COLUMN_NAME']} -> {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}<br>";
        }
    } else {
        echo "Nenhuma foreign key encontrada<br>";
    }
    
    // 3. Verificar se existe tabela veterinarian_profiles
    echo "<h3>3. Verificando tabela veterinarian_profiles:</h3>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'veterinarian_profiles'");
    if ($stmt->fetch()) {
        echo "✓ Tabela veterinarian_profiles existe<br>";
        
        echo "<h4>Estrutura da veterinarian_profiles:</h4>";
        $stmt = $pdo->query("DESCRIBE veterinarian_profiles");
        $columns = $stmt->fetchAll();
        foreach ($columns as $col) {
            echo "- {$col['Field']}: {$col['Type']} {$col['Null']} {$col['Key']} {$col['Default']} {$col['Extra']}<br>";
        }
        
        echo "<h4>Dados na veterinarian_profiles:</h4>";
        $stmt = $pdo->query("SELECT * FROM veterinarian_profiles ORDER BY id");
        $profiles = $stmt->fetchAll();
        foreach ($profiles as $profile) {
            echo "- ID: {$profile['id']}<br>";
        }
    } else {
        echo "✗ Tabela veterinarian_profiles NÃO existe<br>";
    }
    
    // 4. Verificar tabela vet_profiles
    echo "<h3>4. Verificando tabela vet_profiles:</h3>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'vet_profiles'");
    if ($stmt->fetch()) {
        echo "✓ Tabela vet_profiles existe<br>";
        
        echo "<h4>Estrutura da vet_profiles:</h4>";
        $stmt = $pdo->query("DESCRIBE vet_profiles");
        $columns = $stmt->fetchAll();
        foreach ($columns as $col) {
            echo "- {$col['Field']}: {$col['Type']} {$col['Null']} {$col['Key']} {$col['Default']} {$col['Extra']}<br>";
        }
        
        echo "<h4>Dados na vet_profiles:</h4>";
        $stmt = $pdo->query("SELECT user_id FROM vet_profiles ORDER BY user_id");
        $profiles = $stmt->fetchAll();
        foreach ($profiles as $profile) {
            echo "- user_id: {$profile['user_id']}<br>";
        }
    } else {
        echo "✗ Tabela vet_profiles NÃO existe<br>";
    }
    
    // 5. Verificar todas as tabelas do banco
    echo "<h3>5. Todas as tabelas do banco:</h3>";
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