<?php
require_once __DIR__ . '/db.php';

/**
 * Ajusta estoque e registra movimento
 * Suporta entrada/saída e rollback em caso de erro
 */
function stock_adjust(int $productId, int $quantity, string $type, string $reason, int $userId): bool {
    // type: entrada | saida | ajuste
    $pdo = DB::getConnection();
    $pdo->beginTransaction();
    try {
        if ($type === 'entrada') {
            $stmt = $pdo->prepare('UPDATE products SET stock_quantity = stock_quantity + :q WHERE id = :id');
        } else { // saida ou ajuste negativo
            $stmt = $pdo->prepare('UPDATE products SET stock_quantity = stock_quantity - :q WHERE id = :id');
        }
        $stmt->execute([':q' => $quantity, ':id' => $productId]);

        $m = $pdo->prepare('INSERT INTO stock_movements (product_id, type, quantity, reason, user_id) VALUES (:pid,:type,:q,:reason,:uid)');
        $m->execute([':pid'=>$productId, ':type'=>$type, ':q'=>$quantity, ':reason'=>$reason, ':uid'=>$userId]);
        $pdo->commit();
        return true;
    } catch (Throwable $e) {
        $pdo->rollBack();
        error_log('Stock adjust failed: ' . $e->getMessage());
        return false;
    }
}
