<?php
require_once __DIR__ . '/../helpers/db.php';

class ClientModel {
    public static function listAll(): array {
        $pdo = DB::getConnection();
        return $pdo->query('SELECT id, name FROM clients ORDER BY name')->fetchAll();
    }
    public static function paginate(string $q = '', int $limit = 20, int $offset = 0): array {
        $pdo = DB::getConnection();
        $where = '';
        $params = [];
        if ($q !== '') {
            $where = 'WHERE name LIKE :q OR email LIKE :q OR cpf_cnpj LIKE :q';
            $params[':q'] = "%$q%";
        }
        $stmt = $pdo->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM clients $where ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        foreach ($params as $k=>$v) { $stmt->bindValue($k, $v, PDO::PARAM_STR); }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $total = (int)$pdo->query('SELECT FOUND_ROWS()')->fetchColumn();
        return [$rows, $total];
    }

    public static function find(int $id): ?array {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM clients WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('INSERT INTO clients (name, cpf_cnpj, email, phone, address) VALUES (:name, :cpf_cnpj, :email, :phone, :address)');
        $stmt->execute([
            ':name' => $data['name'],
            ':cpf_cnpj' => $data['cpf_cnpj'] ?: null,
            ':email' => $data['email'] ?: null,
            ':phone' => $data['phone'] ?: null,
            ':address' => $data['address'] ?: null,
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('UPDATE clients SET name=:name, cpf_cnpj=:cpf_cnpj, email=:email, phone=:phone, address=:address WHERE id=:id');
        return $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':cpf_cnpj' => $data['cpf_cnpj'] ?: null,
            ':email' => $data['email'] ?: null,
            ':phone' => $data['phone'] ?: null,
            ':address' => $data['address'] ?: null,
        ]);
    }

    public static function delete(int $id): bool {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('DELETE FROM clients WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }
}
