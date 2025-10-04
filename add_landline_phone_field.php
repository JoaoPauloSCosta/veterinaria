<?php
require_once 'src/helpers/db.php';

echo "Adicionando campo landline_phone na tabela clients...\n";

try {
    $pdo = DB::getConnection();
    
    // Verificar se o campo já existe
    $stmt = $pdo->query("SHOW COLUMNS FROM clients LIKE 'landline_phone'");
    $exists = $stmt->fetch();
    
    if ($exists) {
        echo "Campo landline_phone já existe na tabela clients.\n";
    } else {
        // Adicionar o campo landline_phone após o campo phone
        $pdo->exec("ALTER TABLE clients ADD COLUMN landline_phone VARCHAR(20) NULL AFTER phone");
        echo "Campo landline_phone adicionado com sucesso na tabela clients.\n";
    }
    
    // Verificar a estrutura atualizada
    echo "\nEstrutura atualizada da tabela clients:\n";
    $stmt = $pdo->query('DESCRIBE clients');
    $columns = $stmt->fetchAll();
    
    foreach ($columns as $col) {
        echo $col['Field'] . ': ' . $col['Type'] . ' ' . $col['Null'] . ' ' . $col['Key'] . ' ' . $col['Default'] . ' ' . $col['Extra'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>