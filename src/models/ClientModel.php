<?php
require_once __DIR__ . '/../helpers/db.php';

class ClientModel {
    /**
     * Lista todos os clientes ordenados por nome
     * Retorna array com ID e nome dos clientes
     */
    public static function listAll(): array {
        $pdo = DB::getConnection();
        return $pdo->query('SELECT id, name FROM clients ORDER BY name')->fetchAll();
    }
    
    /**
     * Lista clientes com paginação e busca opcional por nome, email ou CPF/CNPJ
     * Retorna array com registros paginados e total de registros encontrados
     */
    public static function paginate(string $q = '', int $limit = 20, int $offset = 0): array {
        $pdo = DB::getConnection();
        $where = '';
        $params = [];
        if ($q !== '') {
            // Usar placeholders distintos para evitar HY093 com PDO emulado desativado
            $where = 'WHERE name LIKE :q1 OR email LIKE :q2 OR cpf_cnpj LIKE :q3';
            $params[':q1'] = "%$q%";
            $params[':q2'] = "%$q%";
            $params[':q3'] = "%$q%";
        }
        // Evitar bind em LIMIT/OFFSET quando ATTR_EMULATE_PREPARES = false
        $limitI = max(1, (int)$limit);
        $offsetI = max(0, (int)$offset);
        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM clients $where ORDER BY created_at DESC LIMIT $limitI OFFSET $offsetI";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $k=>$v) { $stmt->bindValue($k, $v, PDO::PARAM_STR); }
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $total = (int)$pdo->query('SELECT FOUND_ROWS()')->fetchColumn();
        return [$rows, $total];
    }

    /**
     * Busca cliente por ID
     * Retorna array com dados do cliente ou null se não encontrado
     */
    public static function find(int $id): ?array {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM clients WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Cria novo cliente com os dados fornecidos
     * Retorna o ID do cliente criado
     */
    public static function create(array $data): int {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('INSERT INTO clients (name, cpf_cnpj, email, phone, landline_phone, address) VALUES (:name, :cpf_cnpj, :email, :phone, :landline_phone, :address)');
        $stmt->execute([
            ':name' => $data['name'],
            ':cpf_cnpj' => $data['cpf_cnpj'] ?: null,
            ':email' => $data['email'] ?: null,
            ':phone' => $data['phone'] ?: null,
            ':landline_phone' => $data['landline_phone'] ?: null,
            ':address' => $data['address'] ?: null,
        ]);
        return (int)$pdo->lastInsertId();
    }

    /**
     * Atualiza dados de um cliente existente
     * Retorna true se a operação foi bem-sucedida
     */
    public static function update(int $id, array $data): bool {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('UPDATE clients SET name=:name, cpf_cnpj=:cpf_cnpj, email=:email, phone=:phone, landline_phone=:landline_phone, address=:address WHERE id=:id');
        return $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':cpf_cnpj' => $data['cpf_cnpj'] ?: null,
            ':email' => $data['email'] ?: null,
            ':phone' => $data['phone'] ?: null,
            ':landline_phone' => $data['landline_phone'] ?: null,
            ':address' => $data['address'] ?: null,
        ]);
    }

    /**
     * Remove cliente do sistema por ID
     * Retorna true se a operação foi bem-sucedida
     */
    public static function delete(int $id): bool {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('DELETE FROM clients WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }
}
