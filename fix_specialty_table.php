<?php
require_once 'src/helpers/db.php';

echo "<h2>Correção da Tabela veterinarian_specialties</h2>";

try {
    $pdo = DB::getConnection();
    
    echo "<h3>1. Situação atual:</h3>";
    echo "- Código usa: vet_profiles (user_id como PK)<br>";
    echo "- veterinarian_specialties referencia: veterinarian_profiles (id como PK)<br>";
    echo "- Isso causa o erro de foreign key constraint<br><br>";
    
    echo "<h3>2. Removendo foreign key atual:</h3>";
    try {
        $pdo->exec("ALTER TABLE veterinarian_specialties DROP FOREIGN KEY fk_vs_profile");
        echo "✓ Foreign key fk_vs_profile removida<br>";
    } catch (Exception $e) {
        echo "⚠ Erro ao remover FK (pode não existir): " . $e->getMessage() . "<br>";
    }
    
    echo "<h3>3. Renomeando coluna para user_id:</h3>";
    try {
        $pdo->exec("ALTER TABLE veterinarian_specialties CHANGE veterinarian_profile_id user_id INT(10) UNSIGNED NOT NULL");
        echo "✓ Coluna renomeada de veterinarian_profile_id para user_id<br>";
    } catch (Exception $e) {
        echo "⚠ Erro ao renomear coluna: " . $e->getMessage() . "<br>";
    }
    
    echo "<h3>4. Adicionando nova foreign key para vet_profiles:</h3>";
    try {
        $pdo->exec("ALTER TABLE veterinarian_specialties ADD CONSTRAINT fk_vs_vet_profile FOREIGN KEY (user_id) REFERENCES vet_profiles(user_id) ON DELETE CASCADE ON UPDATE CASCADE");
        echo "✓ Nova foreign key adicionada: user_id -> vet_profiles(user_id)<br>";
    } catch (Exception $e) {
        echo "⚠ Erro ao adicionar nova FK: " . $e->getMessage() . "<br>";
    }
    
    echo "<h3>5. Verificando estrutura final:</h3>";
    $stmt = $pdo->query("DESCRIBE veterinarian_specialties");
    $columns = $stmt->fetchAll();
    foreach ($columns as $col) {
        echo "- {$col['Field']}: {$col['Type']} {$col['Null']} {$col['Key']} {$col['Default']} {$col['Extra']}<br>";
    }
    
    echo "<h3>6. Verificando foreign keys:</h3>";
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
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}
?>