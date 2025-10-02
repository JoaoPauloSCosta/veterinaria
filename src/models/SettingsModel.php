<?php
require_once __DIR__ . '/../helpers/db.php';

class SettingsModel {
    private static function ensureTable(PDO $pdo): void {
        // Prefer non-reserved column names; do not override existing schema
        $pdo->exec('CREATE TABLE IF NOT EXISTS settings (
            `skey` VARCHAR(100) PRIMARY KEY,
            `svalue` TEXT NULL,
            `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    }

    private static function detectColumns(PDO $pdo): array {
        // Returns ['k'=>'col_name_for_key','v'=>'col_name_for_value']
        $cols = [];
        foreach ($pdo->query('DESCRIBE settings') as $row) {
            $cols[] = $row['Field'];
        }
        $k = null; $v = null;
        foreach (['skey','key','name'] as $cand) { if (in_array($cand, $cols, true)) { $k = $cand; break; } }
        foreach (['svalue','value','val'] as $cand) { if (in_array($cand, $cols, true)) { $v = $cand; break; } }
        if (!$k || !$v) {
            // Fallback: attempt to add missing expected columns
            if (!$k) { $pdo->exec('ALTER TABLE settings ADD COLUMN `skey` VARCHAR(100)'); $k = 'skey'; }
            if (!$v) { $pdo->exec('ALTER TABLE settings ADD COLUMN `svalue` TEXT'); $v = 'svalue'; }
        }
        return ['k'=>$k,'v'=>$v];
    }

    public static function all(): array {
        $pdo = DB::getConnection();
        self::ensureTable($pdo);
        $cols = self::detectColumns($pdo);
        $stmt = $pdo->query("SELECT `{$cols['k']}` AS k, `{$cols['v']}` AS v FROM settings");
        $out = [];
        foreach ($stmt as $row) { $out[$row['k']] = $row['v']; }
        return $out;
    }

    public static function setMany(array $assoc): void {
        $pdo = DB::getConnection();
        self::ensureTable($pdo);
        $cols = self::detectColumns($pdo);
        $sql = "REPLACE INTO settings (`{$cols['k']}`,`{$cols['v']}`) VALUES (:k,:v)";
        $stmt = $pdo->prepare($sql);
        foreach ($assoc as $k=>$v) { $stmt->execute([':k'=>$k, ':v'=>$v]); }
    }

    public static function resetAll(): void {
        $pdo = DB::getConnection();
        self::ensureTable($pdo);
        $pdo->exec('TRUNCATE TABLE settings');
    }
}
