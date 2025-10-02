<?php
require_once __DIR__ . '/../middlewares/auth.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../helpers/stock.php';
require_once __DIR__ . '/../models/ProductModel.php';

class ProductsController {
    public static function index(): void {
        require_login();
        require_role(['admin','recepcao','veterinario','financeiro']);
        $q = sanitize_string($_GET['q'] ?? '');
        [$products, $total] = ProductModel::paginate($q, 100, 0);
        $low = ProductModel::lowStock();
        render('products/index', compact('products','q','total','low'));
    }

    public static function create(): void {
        require_login();
        require_role(['admin']);
        csrf_validate();
        $data = [
            'name' => sanitize_string($_POST['name'] ?? ''),
            'description' => sanitize_string($_POST['description'] ?? ''),
            'price' => (float)($_POST['price'] ?? 0),
            'stock_quantity' => (int)($_POST['stock_quantity'] ?? 0),
            'min_stock_level' => (int)($_POST['min_stock_level'] ?? 0),
            'is_service' => !empty($_POST['is_service']),
        ];
        if ($data['name'] === '' || $data['price'] < 0) {
            $flash_error = 'Nome e preço são obrigatórios.';
            [$products, $total] = ProductModel::paginate('', 100, 0);
            $low = ProductModel::lowStock();
            render('products/index', compact('products','low','flash_error'));
            return;
        }
        $id = ProductModel::create($data);
        audit_log($_SESSION['user']['id'] ?? null, 'product_create', 'products', $id, json_encode($data));
        header('Location: ' . APP_URL . '/products');
    }

    public static function edit(int $id): void {
        require_login();
        require_role(['admin']);
        csrf_validate();
        $data = [
            'name' => sanitize_string($_POST['name'] ?? ''),
            'description' => sanitize_string($_POST['description'] ?? ''),
            'price' => (float)($_POST['price'] ?? 0),
            'min_stock_level' => (int)($_POST['min_stock_level'] ?? 0),
            'is_service' => !empty($_POST['is_service']),
        ];
        ProductModel::update($id, $data);
        audit_log($_SESSION['user']['id'] ?? null, 'product_update', 'products', $id, json_encode($data));
        header('Location: ' . APP_URL . '/products');
    }

    public static function delete(int $id): void {
        require_login();
        require_role(['admin']);
        csrf_validate();
        ProductModel::delete($id);
        audit_log($_SESSION['user']['id'] ?? null, 'product_delete', 'products', $id);
        header('Location: ' . APP_URL . '/products');
    }

    public static function stockEntry(): void {
        require_login();
        require_role(['admin','financeiro']);
        csrf_validate();
        $pid = (int)($_POST['product_id'] ?? 0);
        $qty = (int)($_POST['quantity'] ?? 0);
        if ($pid > 0 && $qty > 0) {
            stock_adjust($pid, $qty, 'entrada', 'Entrada manual', (int)$_SESSION['user']['id']);
            audit_log($_SESSION['user']['id'] ?? null, 'stock_entry', 'products', $pid, 'qty='.$qty);
        }
        header('Location: ' . APP_URL . '/products');
    }

    public static function stockExit(): void {
        require_login();
        require_role(['admin','financeiro']);
        csrf_validate();
        $pid = (int)($_POST['product_id'] ?? 0);
        $qty = (int)($_POST['quantity'] ?? 0);
        if ($pid > 0 && $qty > 0) {
            stock_adjust($pid, $qty, 'saida', 'Saída manual', (int)$_SESSION['user']['id']);
            audit_log($_SESSION['user']['id'] ?? null, 'stock_exit', 'products', $pid, 'qty='.$qty);
        }
        header('Location: ' . APP_URL . '/products');
    }
}
