<?php
require_once __DIR__ . '/../helpers/db.php';

class PetModel {
    /**
     * Lista todos os pets com informações do cliente proprietário
     * Retorna array ordenado por nome do cliente e depois por nome do pet
     */
    public static function listAllWithClient(): array {
        $pdo = DB::getConnection();
        $sql = 'SELECT p.id, p.name, p.client_id, c.name AS client_name FROM pets p JOIN clients c ON c.id = p.client_id ORDER BY c.name, p.name';
        return $pdo->query($sql)->fetchAll();
    }
    
    /**
     * Lista todos os pets de um cliente específico
     * Retorna array com pets ordenados por nome
     */
    public static function listByClient(int $clientId): array {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM pets WHERE client_id = :cid ORDER BY name');
        $stmt->execute([':cid' => $clientId]);
        return $stmt->fetchAll();
    }

    /**
     * Lista pets com paginação e busca opcional por nome do pet ou cliente
     * Retorna array com registros paginados e total de registros encontrados
     */
    public static function paginate(string $q = '', int $limit = 20, int $offset = 0): array {
        $pdo = DB::getConnection();
        $where = '';
        $params = [];
        if ($q !== '') {
            $where = 'WHERE p.name LIKE :q1 OR c.name LIKE :q2';
            $params[':q1'] = "%$q%";
            $params[':q2'] = "%$q%";
        }
        $limitI = max(1, (int)$limit);
        $offsetI = max(0, (int)$offset);
        $sql = "SELECT SQL_CALC_FOUND_ROWS p.*, c.name AS client_name
                FROM pets p
                JOIN clients c ON c.id = p.client_id
                $where
                ORDER BY p.created_at DESC
                LIMIT $limitI OFFSET $offsetI";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $k=>$v) { $stmt->bindValue($k, $v, PDO::PARAM_STR); }
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $total = (int)$pdo->query('SELECT FOUND_ROWS()')->fetchColumn();
        return [$rows, $total];
    }

    /**
     * Busca pet por ID
     * Retorna array com dados do pet ou null se não encontrado
     */
    public static function find(int $id): ?array {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM pets WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Cria novo pet com os dados fornecidos
     * Retorna o ID do pet criado
     */
    public static function create(array $data): int {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('INSERT INTO pets (client_id, name, species, breed, birth_date, gender, color, notes) VALUES (:client_id, :name, :species, :breed, :birth_date, :gender, :color, :notes)');
        $stmt->execute([
            ':client_id' => $data['client_id'],
            ':name' => $data['name'],
            ':species' => $data['species'] ?: null,
            ':breed' => $data['breed'] ?: null,
            ':birth_date' => $data['birth_date'] ?: null,
            ':gender' => $data['gender'] ?: null,
            ':color' => $data['color'] ?: null,
            ':notes' => $data['notes'] ?: null,
        ]);
        return (int)$pdo->lastInsertId();
    }

    /**
     * Atualiza dados de um pet existente
     * Retorna true se a operação foi bem-sucedida
     */
    public static function update(int $id, array $data): bool {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('UPDATE pets SET client_id=:client_id, name=:name, species=:species, breed=:breed, birth_date=:birth_date, gender=:gender, color=:color, notes=:notes WHERE id=:id');
        return $stmt->execute([
            ':id' => $id,
            ':client_id' => $data['client_id'],
            ':name' => $data['name'],
            ':species' => $data['species'] ?: null,
            ':breed' => $data['breed'] ?: null,
            ':birth_date' => $data['birth_date'] ?: null,
            ':gender' => $data['gender'] ?: null,
            ':color' => $data['color'] ?: null,
            ':notes' => $data['notes'] ?: null,
        ]);
    }

    /**
     * Remove pet do sistema por ID
     * Retorna true se a operação foi bem-sucedida
     */
    public static function delete(int $id): bool {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('DELETE FROM pets WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }
}
