<?php
require_once __DIR__ . '/../middlewares/auth.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../helpers/stock.php';
require_once __DIR__ . '/../models/RecordModel.php';
require_once __DIR__ . '/../models/ProductModel.php';

class RecordsController {
    public static function list(int $petId): void {
        require_login();
        require_role(['admin','veterinario']);
        $items = RecordModel::listByPet($petId);
        render('records/index', compact('items','petId'));
    }

    public static function create(int $petId): void {
        require_login();
        require_role(['admin','veterinario']);
        csrf_validate();
        $data = [
            'pet_id' => $petId,
            'appointment_id' => (int)($_POST['appointment_id'] ?? 0) ?: null,
            'vet_id' => (int)($_SESSION['user']['id'] ?? 0),
            'record_date' => date('Y-m-d H:i:s'),
            'anamnesis' => sanitize_string($_POST['anamnesis'] ?? ''),
            'diagnosis' => sanitize_string($_POST['diagnosis'] ?? ''),
            'treatment' => sanitize_string($_POST['treatment'] ?? ''),
            'prescription' => sanitize_string($_POST['prescription'] ?? ''),
        ];
        $id = RecordModel::create($data);
        // Baixa automática de estoque para produtos usados
        $used = $_POST['used_products'] ?? [];
        foreach ($used as $entry) {
            $pid = (int)($entry['product_id'] ?? 0);
            $qty = (int)($entry['quantity'] ?? 0);
            if ($pid > 0 && $qty > 0) {
                $product = ProductModel::find($pid);
                if ($product && (int)$product['is_service'] === 0) {
                    stock_adjust($pid, $qty, 'saida', 'Uso em atendimento #'.$id, (int)$_SESSION['user']['id']);
                }
            }
        }
        audit_log($_SESSION['user']['id'] ?? null, 'record_create', 'records', $id);
        header('Location: ' . APP_URL . '/records/' . $petId);
    }
}
