<?php
require_once 'src/helpers/db.php';

echo "Estrutura da tabela clients:\n";

try {
    $pdo = DB::getConnection();
    $stmt = $pdo->query('DESCRIBE clients');
    $columns = $stmt->fetchAll();
    
    foreach ($columns as $col) {
        echo $col['Field'] . ': ' . $col['Type'] . ' ' . $col['Null'] . ' ' . $col['Key'] . ' ' . $col['Default'] . ' ' . $col['Extra'] . "\n";
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>