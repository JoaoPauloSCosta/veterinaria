<?php
require_once __DIR__ . '/../helpers/db.php';

class UserModel {
    public static function listByRole(string $role): array {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('SELECT id, name, email, role FROM users WHERE role = :role AND is_active = 1 ORDER BY name');
        $stmt->execute([':role'=>$role]);
        return $stmt->fetchAll();
    }
}
