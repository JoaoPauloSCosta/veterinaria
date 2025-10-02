<?php
require_once __DIR__ . '/../helpers/db.php';

class ProductModel {
    public static function listAll(): array {
        $pdo = DB::getConnection();
        return $pdo->query('SELECT id, name, price, is_service FROM products ORDER BY name')->fetchAll();
    }
    public static function paginate(string $q = '', int $limit = 50, int $offset = 0): array {
        $pdo = DB::getConnection();
        $where = '';
        $params = [];
        if ($q !== '') { $where = 'WHERE name LIKE :q'; $params[':q'] = "%$q%"; }
        $stmt = $pdo->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM products $where ORDER BY name LIMIT :limit OFFSET :offset");
        foreach ($params as $k=>$v) { $stmt->bindValue($k, $v, PDO::PARAM_STR); }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
      
        $rows = $stmt->fetchAll();
        $total = (int)DB::getConnection()->query('SELECT FOUND_ROWS()')->fetchColumn();
        return [$rows, $total];
    }

    public static function find(int $id): ?array {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id');
        $stmt->execute([':id'=>$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('INSERT INTO products (name, description, price, stock_quantity, min_stock_level, is_service) VALUES (:name,:description,:price,:stock_quantity,:min_stock_level,:is_service)');
        $stmt->execute([
            ':name'=>$data['name'], ':description'=>$data['description'] ?: null, ':price'=>$data['price'], ':stock_quantity'=>$data['stock_quantity'] ?? 0, ':min_stock_level'=>$data['min_stock_level'] ?? 0, ':is_service'=>$data['is_service'] ? 1 : 0
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('UPDATE products SET name=:name, description=:description, price=:price, min_stock_level=:min_stock_level, is_service=:is_service WHERE id=:id');
        return $stmt->execute([
            ':id'=>$id, ':name'=>$data['name'], ':description'=>$data['description'] ?: null, ':price'=>$data['price'], ':min_stock_level'=>$data['min_stock_level'] ?? 0, ':is_service'=>$data['is_service'] ? 1 : 0
        ]);
    }

    public static function delete(int $id): bool {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('DELETE FROM products WHERE id = :id');
        return $stmt->execute([':id'=>$id]);
    }

    public static function lowStock(): array {
        $pdo = DB::getConnection();
        return $pdo->query('SELECT * FROM products WHERE stock_quantity <= min_stock_level AND is_service = 0 ORDER BY stock_quantity ASC')->fetchAll();
    }
}
