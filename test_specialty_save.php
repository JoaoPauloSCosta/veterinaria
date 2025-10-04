<?php
require_once 'src/helpers/db.php';
require_once 'src/models/VetSpecialtyModel.php';

echo "<h2>Teste de Salvamento de Especialidades</h2>";

try {
    $pdo = DB::getConnection();
    
    // Simular o que acontece quando criamos um veterinário
    echo "<h3>1. Testando VetSpecialtyModel::replaceForVet com ID 7 (usuário existente)</h3>";
    
    // Primeiro, vamos ver o que está na tabela antes
    echo "<h4>Antes do teste:</h4>";
    $stmt = $pdo->query("SELECT * FROM veterinarian_specialties WHERE veterinarian_profile_id = 7");
    $before = $stmt->fetchAll();
    echo "Associações para ID 7: " . count($before) . "<br>";
    foreach ($before as $row) {
        echo "- veterinarian_profile_id: {$row['veterinarian_profile_id']}, specialty_id: {$row['specialty_id']}<br>";
    }
    
    // Agora vamos testar o método replaceForVet
    echo "<h4>Executando VetSpecialtyModel::replaceForVet(7, [1, 2]):</h4>";
    VetSpecialtyModel::replaceForVet(7, [1, 2]);
    
    // Verificar o resultado
    echo "<h4>Depois do teste:</h4>";
    $stmt = $pdo->query("SELECT * FROM veterinarian_specialties WHERE veterinarian_profile_id = 7");
    $after = $stmt->fetchAll();
    echo "Associações para ID 7: " . count($after) . "<br>";
    foreach ($after as $row) {
        echo "- veterinarian_profile_id: {$row['veterinarian_profile_id']}, specialty_id: {$row['specialty_id']}<br>";
    }
    
    // Verificar se o perfil existe
    echo "<h3>2. Verificando se o perfil do usuário 7 existe:</h3>";
    $stmt = $pdo->prepare("SELECT * FROM vet_profiles WHERE user_id = 7");
    $stmt->execute();
    $profile = $stmt->fetch();
    if ($profile) {
        echo "✓ Perfil existe para user_id 7<br>";
        echo "- CRMV: " . ($profile['crmv'] ?? 'N/A') . "<br>";
        echo "- Employment: " . ($profile['employment_type'] ?? 'N/A') . "<br>";
    } else {
        echo "✗ Perfil NÃO existe para user_id 7<br>";
    }
    
    // Verificar todas as associações
    echo "<h3>3. Todas as associações na tabela:</h3>";
    $stmt = $pdo->query("SELECT * FROM veterinarian_specialties ORDER BY veterinarian_profile_id, specialty_id");
    $all = $stmt->fetchAll();
    foreach ($all as $row) {
        echo "- veterinarian_profile_id: {$row['veterinarian_profile_id']}, specialty_id: {$row['specialty_id']}<br>";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>