<?php
require_once __DIR__ . '/../helpers/db.php';

class SpecialtyModel {
    /**
     * Lista todas as especialidades disponíveis ordenadas por nome
     * Detecta automaticamente a coluna de nome na tabela e retorna array padronizado
     */
    public static function listAll(): array {
        $pdo = DB::getConnection();
        try {
            // Detectar coluna de nome dinamicamente e sempre alias como 'name'
            $nameCol = 'name';
            try {
                $cols = [];
                foreach ($pdo->query('DESCRIBE specialties') as $row) { $cols[] = $row['Field']; }
                foreach (['name','title','nome','descricao','description','titulo'] as $cand) {
                    if (in_array($cand, $cols, true)) { $nameCol = $cand; break; }
                }
            } catch (Throwable $e) {}
            $stmt = $pdo->query("SELECT id, `$nameCol` AS name FROM specialties ORDER BY `$nameCol`");
            return $stmt->fetchAll();
        } catch (Throwable $e) {
            return [];
        }
    }
}