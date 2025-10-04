<?php
require_once 'src/helpers/db.php';

echo "<h2>Debug Detalhado do VetSpecialtyModel::replaceForVet</h2>";

try {
    $pdo = DB::getConnection();
    
    // Simular o método replaceForVet com logs detalhados
    $vetId = 7;
    $specialtyIds = [1, 2];
    
    echo "<h3>Simulando VetSpecialtyModel::replaceForVet($vetId, [1, 2])</h3>";
    
    // 1. Detectar colunas
    echo "<h4>1. Detectando colunas:</h4>";
    $cols = [];
    try {
        $columns = [];
        foreach ($pdo->query('DESCRIBE veterinarian_specialties') as $row) { 
            $columns[] = $row['Field']; 
        }
        echo "Colunas encontradas: " . implode(', ', $columns) . "<br>";
        
        // Detectar coluna do veterinário
        foreach (['veterinarian_profile_id','vet_id','veterinarian_id','user_id'] as $cand) { 
            if (in_array($cand, $columns, true)) { 
                $cols['vet'] = $cand;
                echo "Coluna do veterinário detectada: {$cand}<br>";
                break; 
            } 
        }
        
        // Detectar coluna da especialidade
        foreach (['specialty_id','spec_id','speciality_id'] as $cand) { 
            if (in_array($cand, $columns, true)) { 
                $cols['spec'] = $cand;
                echo "Coluna da especialidade detectada: {$cand}<br>";
                break; 
            } 
        }
        
        if (!isset($cols['vet']) || !isset($cols['spec'])) {
            echo "❌ ERRO: Não foi possível detectar as colunas necessárias<br>";
            echo "cols['vet']: " . ($cols['vet'] ?? 'NÃO DETECTADO') . "<br>";
            echo "cols['spec']: " . ($cols['spec'] ?? 'NÃO DETECTADO') . "<br>";
            exit;
        }
        
    } catch (Throwable $e) {
        echo "❌ ERRO na detecção de colunas: " . $e->getMessage() . "<br>";
        exit;
    }
    
    // 2. Iniciar transação
    echo "<h4>2. Iniciando transação</h4>";
    $pdo->beginTransaction();
    
    // 3. Deletar associações existentes
    echo "<h4>3. Deletando associações existentes</h4>";
    $sqlDel = "DELETE FROM veterinarian_specialties WHERE `{$cols['vet']}` = :vid";
    echo "SQL DELETE: $sqlDel<br>";
    echo "Parâmetro :vid = $vetId<br>";
    
    $del = $pdo->prepare($sqlDel);
    $delResult = $del->execute([':vid'=>$vetId]);
    echo "Resultado DELETE: " . ($delResult ? 'SUCCESS' : 'FAILED') . "<br>";
    echo "Linhas afetadas: " . $del->rowCount() . "<br>";
    
    // 4. Inserir novas associações
    echo "<h4>4. Inserindo novas associações</h4>";
    if ($specialtyIds) {
        $sqlIns = "INSERT INTO veterinarian_specialties (`{$cols['vet']}`, `{$cols['spec']}`) VALUES (:vid, :sid)";
        echo "SQL INSERT: $sqlIns<br>";
        
        $ins = $pdo->prepare($sqlIns);
        foreach ($specialtyIds as $sid) {
            echo "Inserindo: veterinarian_profile_id=$vetId, specialty_id=$sid<br>";
            $insResult = $ins->execute([':vid'=>$vetId, ':sid'=>(int)$sid]);
            echo "Resultado: " . ($insResult ? 'SUCCESS' : 'FAILED') . "<br>";
            if (!$insResult) {
                $errorInfo = $ins->errorInfo();
                echo "Erro SQL: " . implode(' - ', $errorInfo) . "<br>";
            }
        }
    } else {
        echo "Nenhuma especialidade para inserir<br>";
    }
    
    // 5. Commit
    echo "<h4>5. Fazendo commit</h4>";
    $pdo->commit();
    echo "Transação commitada com sucesso<br>";
    
    // 6. Verificar resultado final
    echo "<h4>6. Verificando resultado final</h4>";
    $stmt = $pdo->prepare("SELECT * FROM veterinarian_specialties WHERE `{$cols['vet']}` = :vid");
    $stmt->execute([':vid'=>$vetId]);
    $final = $stmt->fetchAll();
    echo "Associações encontradas para ID $vetId: " . count($final) . "<br>";
    foreach ($final as $row) {
        echo "- {$cols['vet']}: {$row[$cols['vet']]}, {$cols['spec']}: {$row[$cols['spec']]}<br>";
    }
    
} catch (Throwable $e) {
    if ($pdo->inTransaction()) { 
        $pdo->rollBack(); 
        echo "❌ Transação revertida devido ao erro<br>";
    }
    echo "❌ ERRO GERAL: " . $e->getMessage() . "<br>";
    echo "Arquivo: " . $e->getFile() . "<br>";
    echo "Linha: " . $e->getLine() . "<br>";
}
?>