<?php
require_once 'src/helpers/db.php';

echo "<h2>Correção da Tabela veterinarian_specialties - V3 (Final)</h2>";

try {
    $pdo = DB::getConnection();
    
    echo "<h3>1. Problema identificado:</h3>";
    echo "- vet_profiles.user_id: int(11)<br>";
    echo "- veterinarian_specialties.user_id: int(10) unsigned<br>";
    echo "- Tipos incompatíveis impedem a foreign key<br><br>";
    
    echo "<h3>2. Corrigindo tipo de dados:</h3>";
    try {
        $pdo->exec("ALTER TABLE veterinarian_specialties MODIFY user_id INT(11) NOT NULL");
        echo "✓ Tipo de user_id alterado para INT(11)<br>";
    } catch (Exception $e) {
        echo "⚠ Erro ao alterar tipo: " . $e->getMessage() . "<br>";
    }
    
    echo "<h3>3. Adicionando foreign key:</h3>";
    try {
        $pdo->exec("ALTER TABLE veterinarian_specialties ADD CONSTRAINT fk_vs_vet_profile FOREIGN KEY (user_id) REFERENCES vet_profiles(user_id) ON DELETE CASCADE ON UPDATE CASCADE");
        echo "✓ Foreign key adicionada com sucesso!<br>";
    } catch (Exception $e) {
        echo "⚠ Erro ao adicionar FK: " . $e->getMessage() . "<br>";
    }
    
    echo "<h3>4. Verificando estrutura final:</h3>";
    $stmt = $pdo->query("DESCRIBE veterinarian_specialties");
    $columns = $stmt->fetchAll();
    foreach ($columns as $col) {
        echo "- {$col['Field']}: {$col['Type']} {$col['Null']} {$col['Key']} {$col['Default']} {$col['Extra']}<br>";
    }
    
    echo "<h3>5. Verificando foreign keys:</h3>";
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
    
    echo "<h3>6. Testando VetSpecialtyModel::replaceForVet:</h3>";
    require_once 'src/models/VetSpecialtyModel.php';
    
    try {
        VetSpecialtyModel::replaceForVet(7, [1, 2]);
        echo "✓ VetSpecialtyModel::replaceForVet(7, [1, 2]) executado com sucesso<br>";
        
        // Verificar resultado
        $stmt = $pdo->prepare("SELECT * FROM veterinarian_specialties WHERE user_id = 7");
        $stmt->execute();
        $results = $stmt->fetchAll();
        echo "Associações criadas para user_id 7: " . count($results) . "<br>";
        foreach ($results as $row) {
            echo "- user_id: {$row['user_id']}, specialty_id: {$row['specialty_id']}<br>";
        }
        
    } catch (Exception $e) {
        echo "✗ Erro no teste: " . $e->getMessage() . "<br>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}
?>