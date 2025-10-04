<?php
require_once 'src/helpers/db.php';

echo "<h2>Correção da Tabela veterinarian_specialties - V2</h2>";

try {
    $pdo = DB::getConnection();
    
    echo "<h3>1. Verificando dados existentes:</h3>";
    $stmt = $pdo->query("SELECT * FROM veterinarian_specialties");
    $existing = $stmt->fetchAll();
    echo "Registros existentes: " . count($existing) . "<br>";
    foreach ($existing as $row) {
        echo "- user_id: {$row['user_id']}, specialty_id: {$row['specialty_id']}<br>";
    }
    
    echo "<h3>2. Verificando se user_ids existem em vet_profiles:</h3>";
    foreach ($existing as $row) {
        $stmt = $pdo->prepare("SELECT user_id FROM vet_profiles WHERE user_id = ?");
        $stmt->execute([$row['user_id']]);
        $exists = $stmt->fetch();
        if ($exists) {
            echo "✓ user_id {$row['user_id']} existe em vet_profiles<br>";
        } else {
            echo "✗ user_id {$row['user_id']} NÃO existe em vet_profiles<br>";
        }
    }
    
    echo "<h3>3. Limpando dados órfãos:</h3>";
    $stmt = $pdo->exec("
        DELETE vs FROM veterinarian_specialties vs 
        LEFT JOIN vet_profiles vp ON vs.user_id = vp.user_id 
        WHERE vp.user_id IS NULL
    ");
    echo "Registros órfãos removidos: $stmt<br>";
    
    echo "<h3>4. Tentando adicionar foreign key novamente:</h3>";
    try {
        $pdo->exec("ALTER TABLE veterinarian_specialties ADD CONSTRAINT fk_vs_vet_profile FOREIGN KEY (user_id) REFERENCES vet_profiles(user_id) ON DELETE CASCADE ON UPDATE CASCADE");
        echo "✓ Foreign key adicionada com sucesso!<br>";
    } catch (Exception $e) {
        echo "⚠ Erro ao adicionar FK: " . $e->getMessage() . "<br>";
        
        // Verificar se há problema de tipo de dados
        echo "<h4>Verificando tipos de dados:</h4>";
        $stmt = $pdo->query("DESCRIBE vet_profiles");
        $vpCols = $stmt->fetchAll();
        foreach ($vpCols as $col) {
            if ($col['Field'] === 'user_id') {
                echo "vet_profiles.user_id: {$col['Type']}<br>";
            }
        }
        
        $stmt = $pdo->query("DESCRIBE veterinarian_specialties");
        $vsCols = $stmt->fetchAll();
        foreach ($vsCols as $col) {
            if ($col['Field'] === 'user_id') {
                echo "veterinarian_specialties.user_id: {$col['Type']}<br>";
            }
        }
    }
    
    echo "<h3>5. Testando inserção:</h3>";
    try {
        // Testar com user_id 7 que sabemos que existe
        $pdo->exec("INSERT IGNORE INTO veterinarian_specialties (user_id, specialty_id) VALUES (7, 1)");
        echo "✓ Inserção teste bem-sucedida<br>";
    } catch (Exception $e) {
        echo "✗ Erro na inserção teste: " . $e->getMessage() . "<br>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}
?>