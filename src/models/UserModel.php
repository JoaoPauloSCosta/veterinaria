<?php
require_once __DIR__ . '/../helpers/db.php';

class UserModel {
    /**
     * Lista usuários ativos por tipo de função (role)
     * Retorna array com ID, nome, email e role ordenados por nome
     */
    public static function listByRole(string $role): array {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('SELECT id, name, email, role FROM users WHERE role = :role AND is_active = 1 ORDER BY name');
        $stmt->execute([':role'=>$role]);
        return $stmt->fetchAll();
    }

    /**
     * Lista usuários por role com paginação e busca opcional por nome ou email
     * Retorna array com registros paginados e total de registros encontrados
     */
    public static function paginateByRole(string $role, string $q = '', int $limit = 50, int $offset = 0): array {
        $pdo = DB::getConnection();
        $where = 'WHERE role = :role';
        $params = [':role' => $role];
        if ($q !== '') {
            $where .= ' AND (name LIKE :q OR email LIKE :q)';
            $params[':q'] = "%$q%";
        }
        $sql = "SELECT SQL_CALC_FOUND_ROWS id, name, email, role, is_active FROM users $where ORDER BY name LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $k=>$v) {
            $stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $total = (int)$pdo->query('SELECT FOUND_ROWS()')->fetchColumn();
        return [$rows, $total];
    }

    /**
     * Cria novo veterinário com os dados fornecidos
     * Retorna o ID do usuário veterinário criado
     */
    public static function createVet(array $data): int {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('INSERT INTO users (name, email, role, password_hash, is_active, created_at) VALUES (:name, :email, "veterinario", :hash, :active, NOW())');
        $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':hash' => $data['password_hash'],
            ':active' => !empty($data['is_active']) ? 1 : 0,
        ]);
        return (int)$pdo->lastInsertId();
    }

    /**
     * Atualiza dados de um veterinário existente, incluindo senha se fornecida
     * Retorna true se a operação foi bem-sucedida
     */
    public static function updateVet(int $id, array $data): bool {
        $pdo = DB::getConnection();
        if (!empty($data['password_hash'])) {
            $stmt = $pdo->prepare('UPDATE users SET name=:name, email=:email, is_active=:active, password_hash=:hash WHERE id=:id AND role="veterinario"');
            return $stmt->execute([
                ':id'=>$id, ':name'=>$data['name'], ':email'=>$data['email'], ':active'=>!empty($data['is_active']) ? 1 : 0, ':hash'=>$data['password_hash']
            ]);
        }
        $stmt = $pdo->prepare('UPDATE users SET name=:name, email=:email, is_active=:active WHERE id=:id AND role="veterinario"');
        return $stmt->execute([
            ':id'=>$id, ':name'=>$data['name'], ':email'=>$data['email'], ':active'=>!empty($data['is_active']) ? 1 : 0
        ]);
    }

    /**
     * Remove veterinário do sistema por ID
     * Retorna true se a operação foi bem-sucedida
     */
    public static function deleteVet(int $id): bool {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id AND role = "veterinario"');
        return $stmt->execute([':id'=>$id]);
    }
}
