<?php
require_once __DIR__ . '/../helpers/db.php';

class SalesModel {
    public static function createInvoice(int $clientId, int $userId): int {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('INSERT INTO invoices (client_id, user_id, total, status) VALUES (:cid,:uid,0,"pendente")');
        $stmt->execute([':cid'=>$clientId, ':uid'=>$userId]);
        return (int)$pdo->lastInsertId();
    }

    public static function addItem(int $invoiceId, int $productId, int $qty, float $unitPrice): void {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('INSERT INTO invoice_items (invoice_id, product_id, quantity, unit_price, subtotal) VALUES (:iid,:pid,:q,:p,:s)');
        $stmt->execute([':iid'=>$invoiceId, ':pid'=>$productId, ':q'=>$qty, ':p'=>$unitPrice, ':s'=>$unitPrice*$qty]);
        $pdo->prepare('UPDATE invoices SET total = (SELECT COALESCE(SUM(subtotal),0) FROM invoice_items WHERE invoice_id = :iid) WHERE id = :iid')->execute([':iid'=>$invoiceId]);
    }

    public static function pay(int $invoiceId, string $method, float $amount): void {
        $pdo = DB::getConnection();
        $pdo->beginTransaction();
        try {
            $pdo->prepare('INSERT INTO payments (invoice_id, method, amount) VALUES (:iid,:m,:a)')->execute([':iid'=>$invoiceId, ':m'=>$method, ':a'=>$amount]);
            $pdo->prepare("UPDATE invoices SET status='paga' WHERE id=:iid")->execute([':iid'=>$invoiceId]);
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function get(int $invoiceId): ?array {
        $pdo = DB::getConnection();
        $inv = $pdo->prepare('SELECT * FROM invoices WHERE id=:id');
        $inv->execute([':id'=>$invoiceId]);
        $invoice = $inv->fetch();
        if (!$invoice) return null;
        $it = $pdo->prepare('SELECT ii.*, p.name FROM invoice_items ii JOIN products p ON p.id=ii.product_id WHERE ii.invoice_id=:id');
        $it->execute([':id'=>$invoiceId]);
        $items = $it->fetchAll();
        return ['invoice'=>$invoice, 'items'=>$items];
    }
}
