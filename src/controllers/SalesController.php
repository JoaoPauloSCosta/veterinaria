<?php
require_once __DIR__ . '/../middlewares/auth.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../helpers/stock.php';
require_once __DIR__ . '/../models/SalesModel.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/ClientModel.php';

class SalesController {
    public static function pos(): void {
        require_login();
        require_role(['admin','financeiro','recepcao']);
        $clients = ClientModel::listAll();
        $products = ProductModel::listAll();
        render('sales/pos', compact('clients','products'));
    }

    public static function checkout(): void {
        require_login();
        require_role(['admin','financeiro','recepcao']);
        csrf_validate();
        $clientId = (int)($_POST['client_id'] ?? 0);
        $method = sanitize_string($_POST['method'] ?? 'dinheiro');
        $itemsRaw = $_POST['items'] ?? [];
        if ($clientId <= 0 || empty($itemsRaw)) {
            $flash_error = 'Cliente e itens são obrigatórios';
            render('sales/pos', compact('flash_error'));
            return;
        }
        $uid = (int)($_SESSION['user']['id'] ?? 0);
        $invoiceId = SalesModel::createInvoice($clientId, $uid);
        foreach ($itemsRaw as $it) {
            $pid = (int)($it['product_id'] ?? 0);
            $qty = (int)($it['quantity'] ?? 0);
            if ($pid > 0 && $qty > 0) {
                $prod = ProductModel::find($pid);
                if (!$prod) continue;
                $price = (float)$prod['price'];
                SalesModel::addItem($invoiceId, $pid, $qty, $price);
                if ((int)$prod['is_service'] === 0) {
                    stock_adjust($pid, $qty, 'saida', 'Venda #'.$invoiceId, $uid);
                }
            }
        }
        $full = SalesModel::get($invoiceId);
        SalesModel::pay($invoiceId, $method, (float)$full['invoice']['total']);
        audit_log($uid, 'sale_checkout', 'invoices', $invoiceId, json_encode($full['invoice']));
        render('sales/receipt', $full);
    }
}
