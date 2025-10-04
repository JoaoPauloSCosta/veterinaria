<?php
require_once 'src/helpers/db.php';

echo "<h2>Debug dos Novos Veterinários</h2>";

try {
    $pdo = DB::getConnection();
    
    // 1. Verificar todos os usuários veterinários
    echo "<h3>1. Usuários com role 'veterinarian':</h3>";
    $stmt = $pdo->query("SELECT id, name, email, role FROM users WHERE role = 'veterinarian' ORDER BY id");
    $users = $stmt->fetchAll();
    foreach ($users as $user) {
        echo "<pre>";
        print_r($user);
        echo "</pre>";
    }
    
    // 2. Verificar todos os perfis de veterinários
    echo "<h3>2. Perfis de veterinários:</h3>";
    $stmt = $pdo->query("SELECT * FROM vet_profiles ORDER BY user_id");
    $profiles = $stmt->fetchAll();
    foreach ($profiles as $profile) {
        echo "<pre>";
        print_r($profile);
        echo "</pre>";
    }
    
    // 3. Verificar todas as associações de especialidades
    echo "<h3>3. Associações veterinário-especialidade:</h3>";
    $stmt = $pdo->query("SELECT * FROM veterinarian_specialties ORDER BY veterinarian_profile_id");
    $associations = $stmt->fetchAll();
    foreach ($associations as $assoc) {
        echo "<pre>";
        print_r($assoc);
        echo "</pre>";
    }
    
    // 4. Verificar dados completos com JOIN
    echo "<h3>4. Dados completos (usuários + perfis + especialidades):</h3>";
    $stmt = $pdo->query("
        SELECT 
            u.id as user_id,
            u.name as user_name,
            u.email,
            vp.user_id as profile_id,
            vp.user_id as profile_user_id,
            vs.veterinarian_profile_id,
            vs.specialty_id,
            s.name as specialty_name
         FROM users u
         LEFT JOIN vet_profiles vp ON u.id = vp.user_id
         LEFT JOIN veterinarian_specialties vs ON vp.user_id = vs.veterinarian_profile_id
         LEFT JOIN specialties s ON vs.specialty_id = s.id
         WHERE u.role = 'veterinarian'
         ORDER BY u.id, s.name
    ");
    $complete = $stmt->fetchAll();
    foreach ($complete as $row) {
        echo "<pre>";
        print_r($row);
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}
?>