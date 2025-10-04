<?php
require_once __DIR__ . '/../helpers/db.php';

class VetSpecialtyModel {
    private static function detectColumns(PDO $pdo): array {
        try {
            $cols = [];
            foreach ($pdo->query('DESCRIBE veterinarian_specialties') as $row) { $cols[] = $row['Field']; }
            $vet = null; $spec = null;
            foreach ([
                'veterinarian_profile_id','vet_id','user_id','veterinarian_id','veterinario_id','id_veterinario','id_vet','id_user'
            ] as $cand) { if (in_array($cand, $cols, true)) { $vet = $cand; break; } }
            foreach ([
                'specialty_id','specialty','especialidade_id','id_especialidade','id_specialty','spec_id'
            ] as $cand) { if (in_array($cand, $cols, true)) { $spec = $cand; break; } }
            if (!$vet || !$spec) { throw new RuntimeException('Cols not detected'); }
            
            return ['vet'=>$vet,'spec'=>$spec];
        } catch (Throwable $e) {
            // Default fallback
            return ['vet'=>'vet_id','spec'=>'specialty_id'];
        }
    }

    private static function detectSpecialtyNameCol(PDO $pdo): string {
        try {
            $cols = [];
            foreach ($pdo->query('DESCRIBE specialties') as $row) { $cols[] = $row['Field']; }
            foreach (['name','title','nome','descricao','description','titulo'] as $cand) { 
                if (in_array($cand, $cols, true)) { 
                    return $cand; 
                } 
            }
        } catch (Throwable $e) {
            // Fallback to default
        }
        return 'name';
    }
    public static function listIdsByVet(int $vetId): array {
        $pdo = DB::getConnection();
        try {
            $cols = self::detectColumns($pdo);
            $sql = "SELECT `{$cols['spec']}` AS sid FROM veterinarian_specialties WHERE `{$cols['vet']}` = :vid";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':vid'=>$vetId]);
            $result = array_map(static fn($r) => (int)$r['sid'], $stmt->fetchAll());
            
            return $result;
        } catch (Throwable $e) {
            return [];
        }
    }

    public static function listNamesByVet(int $vetId): array {
        $pdo = DB::getConnection();
        try {
            $cols = self::detectColumns($pdo);
            $nameCol = self::detectSpecialtyNameCol($pdo);
            $sql = "SELECT s.`$nameCol` AS sname FROM veterinarian_specialties vs JOIN specialties s ON s.id = vs.`{$cols['spec']}` WHERE vs.`{$cols['vet']}` = :vid ORDER BY s.`$nameCol`";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':vid'=>$vetId]);
            $result = array_map(static fn($r) => (string)$r['sname'], $stmt->fetchAll());
            
            return $result;
        } catch (Throwable $e) {
            return [];
        }
    }

    public static function replaceForVet(int $vetId, array $specialtyIds): void {
        $pdo = DB::getConnection();
        try {
            $cols = self::detectColumns($pdo);
            
            $pdo->beginTransaction();
            $del = $pdo->prepare("DELETE FROM veterinarian_specialties WHERE `{$cols['vet']}` = :vid");
            $del->execute([':vid'=>$vetId]);
            
            if ($specialtyIds) {
                $sqlIns = "INSERT INTO veterinarian_specialties (`{$cols['vet']}`, `{$cols['spec']}`) VALUES (:vid, :sid)";
                $ins = $pdo->prepare($sqlIns);
                foreach ($specialtyIds as $sid) {
                    $ins->execute([':vid'=>$vetId, ':sid'=>(int)$sid]);
                }
            }
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
        }
    }
}