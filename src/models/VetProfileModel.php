<?php
require_once __DIR__ . '/../helpers/db.php';

class VetProfileModel {
    /**
     * Garante que a tabela vet_profiles existe com estrutura completa
     * Cria tabela e tenta adicionar foreign key se não existir
     */
    public static function ensureSchema(): void {
        $pdo = DB::getConnection();
        // Perfil básico do veterinário (1:1 com users)
        // Primeiro cria a tabela sem FK para evitar falhas caso o engine/índice da tabela users impeça o FK.
        $pdo->exec('CREATE TABLE IF NOT EXISTS vet_profiles (
            user_id INT PRIMARY KEY,
            mobile_phone VARCHAR(20) NULL,
            landline_phone VARCHAR(20) NULL,
            professional_email VARCHAR(255) NULL,
            crmv VARCHAR(30) NULL,
            crmv_uf CHAR(2) NULL,
            employment_type ENUM("CLT","PJ") NULL,
            admission_date DATE NULL,
            salary DECIMAL(12,2) NULL,
            workload_hours TINYINT NULL,
            notes TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_crmv_uf (crmv, crmv_uf)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        // Tentar adicionar a FK (ignorar erro caso não seja possível)
        try {
            $pdo->exec('ALTER TABLE vet_profiles ADD CONSTRAINT fk_vp_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
        } catch (Throwable $e) {
            error_log('vet_profiles: não foi possível adicionar FK (ignorar) - ' . $e->getMessage());
        }
    }

    /**
     * Verifica se a tabela vet_profiles existe no banco de dados
     * Retorna true se existir, false caso contrário
     */
    private static function tableExists(PDO $pdo): bool {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE 'vet_profiles'");
            return (bool)($stmt->fetch() ?: false);
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Insere ou atualiza perfil completo de veterinário
     * Normaliza dados e garante que a tabela existe antes da operação
     */
    public static function upsert(int $userId, array $data): void {
        $pdo = DB::getConnection();
        // Garantir tabela existente
        self::ensureSchema();
        if (!self::tableExists($pdo)) { return; }
        // Normalizar UF
        $crmvUf = isset($data['crmv_uf']) ? strtoupper(trim((string)$data['crmv_uf'])) : null;
        $sql = 'REPLACE INTO vet_profiles (user_id, mobile_phone, landline_phone, professional_email, crmv, crmv_uf, employment_type, admission_date, salary, workload_hours, notes)
                VALUES (:user_id, :mobile_phone, :landline_phone, :professional_email, :crmv, :crmv_uf, :employment_type, :admission_date, :salary, :workload_hours, :notes)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':mobile_phone' => ($data['mobile_phone'] ?? null) ?: null,
            ':landline_phone' => ($data['landline_phone'] ?? null) ?: null,
            ':professional_email' => ($data['professional_email'] ?? null) ?: null,
            ':crmv' => ($data['crmv'] ?? null) ?: null,
            ':crmv_uf' => $crmvUf ?: null,
            ':employment_type' => ($data['employment_type'] ?? null) ?: null,
            ':admission_date' => ($data['admission_date'] ?? null) ?: null,
            ':salary' => ($data['salary'] ?? null) !== null ? (float)$data['salary'] : null,
            ':workload_hours' => ($data['workload_hours'] ?? null) !== null ? (int)$data['workload_hours'] : null,
            ':notes' => ($data['notes'] ?? null) ?: null,
        ]);
    }

    /**
     * Busca perfil de veterinário por ID do usuário
     * Retorna array com dados do perfil ou null se não encontrado
     */
    public static function findByUserId(int $userId): ?array {
        $pdo = DB::getConnection();
        // Evitar falha caso a tabela ainda não exista
        if (!self::tableExists($pdo)) { return null; }
        $stmt = $pdo->prepare('SELECT * FROM vet_profiles WHERE user_id = :uid');
        $stmt->execute([':uid' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}