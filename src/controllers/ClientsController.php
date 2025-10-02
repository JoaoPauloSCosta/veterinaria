<?php
require_once __DIR__ . '/../middlewares/auth.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../helpers/errors.php';
require_once __DIR__ . '/../models/ClientModel.php';

class ClientsController {
    public static function index(): void {
        require_login();
        require_role(['admin','recepcao','financeiro','veterinario']);
        $q = sanitize_string($_GET['q'] ?? '');
        [$clients, $total] = ClientModel::paginate($q, 50, 0);
        render('clients/index', compact('clients', 'q', 'total'));
    }

    public static function create(): void {
        require_login();
        require_role(['admin','recepcao']);
        csrf_validate();
        $data = [
            'name' => sanitize_string($_POST['name'] ?? ''),
            'cpf_cnpj' => sanitize_string($_POST['cpf_cnpj'] ?? ''),
            'email' => sanitize_string($_POST['email'] ?? ''),
            'phone' => sanitize_string($_POST['phone'] ?? ''),
            'address' => sanitize_string($_POST['address'] ?? ''),
        ];
        $errors = [];
        if ($data['name'] === '') $errors[] = 'Nome é obrigatório.';
        if (!validate_email($data['email'])) $errors[] = 'E-mail inválido.';
        if (!validate_cpf_cnpj($data['cpf_cnpj'])) $errors[] = 'CPF/CNPJ inválido.';
        if ($errors) {
            $q = '';
            [$clients, $total] = ClientModel::paginate('', 50, 0);
            $flash_error = implode(' ', $errors);
            render('clients/index', compact('clients','q','total','flash_error'));
            return;
        }
        try {
            $id = ClientModel::create($data);
            audit_log($_SESSION['user']['id'] ?? null, 'client_create', 'clients', $id, json_encode($data));
            header('Location: ' . APP_URL . '/clients');
        } catch (Throwable $e) {
            $q = '';
            [$clients, $total] = ClientModel::paginate('', 50, 0);
            $flash_error = friendly_pdo_message($e, 'cliente');
            render('clients/index', compact('clients','q','total','flash_error'));
        }
    }

    public static function edit(int $id): void {
        require_login();
        require_role(['admin','recepcao']);
        csrf_validate();
        $data = [
            'name' => sanitize_string($_POST['name'] ?? ''),
            'cpf_cnpj' => sanitize_string($_POST['cpf_cnpj'] ?? ''),
            'email' => sanitize_string($_POST['email'] ?? ''),
            'phone' => sanitize_string($_POST['phone'] ?? ''),
            'address' => sanitize_string($_POST['address'] ?? ''),
        ];
        $errors = [];
        if ($data['name'] === '') $errors[] = 'Nome é obrigatório.';
        if (!validate_email($data['email'])) $errors[] = 'E-mail inválido.';
        if (!validate_cpf_cnpj($data['cpf_cnpj'])) $errors[] = 'CPF/CNPJ inválido.';
        if ($errors) {
            $client = ClientModel::find($id);
            $flash_error = implode(' ', $errors);
            render('clients/edit', compact('client','flash_error'));
            return;
        }
        try {
            ClientModel::update($id, $data);
            audit_log($_SESSION['user']['id'] ?? null, 'client_update', 'clients', $id, json_encode($data));
            header('Location: ' . APP_URL . '/clients');
        } catch (Throwable $e) {
            $client = ClientModel::find($id);
            $flash_error = friendly_pdo_message($e, 'cliente');
            render('clients/edit', compact('client','flash_error'));
        }
    }

    public static function delete(int $id): void {
        require_login();
        require_role(['admin']);
        csrf_validate();
        ClientModel::delete($id);
        audit_log($_SESSION['user']['id'] ?? null, 'client_delete', 'clients', $id);
        header('Location: ' . APP_URL . '/clients');
    }

    public static function importCsv(): void {
        require_login();
        require_role(['admin']);
        csrf_validate();
        if (!isset($_FILES['csv']) || $_FILES['csv']['error'] !== UPLOAD_ERR_OK) {
            header('Location: ' . APP_URL . '/clients');
            return;
        }
        $tmp = $_FILES['csv']['tmp_name'];
        $h = fopen($tmp, 'r');
        if (!$h) { header('Location: ' . APP_URL . '/clients'); return; }
        // Espera cabeçalho: name,cpf_cnpj,email,phone,address
        $header = fgetcsv($h, 0, ';');
        $count = 0;
        while (($row = fgetcsv($h, 0, ';')) !== false) {
            $data = array_combine($header, $row);
            if (!$data) continue;
            $payload = [
                'name' => sanitize_string($data['name'] ?? ''),
                'cpf_cnpj' => sanitize_string($data['cpf_cnpj'] ?? ''),
                'email' => sanitize_string($data['email'] ?? ''),
                'phone' => sanitize_string($data['phone'] ?? ''),
                'address' => sanitize_string($data['address'] ?? ''),
            ];
            if ($payload['name'] === '' || !validate_email($payload['email']) || !validate_cpf_cnpj($payload['cpf_cnpj'])) continue;
            try {
                $id = ClientModel::create($payload);
                $count++;
            } catch (Throwable $e) {
                // ignora duplicidades de CPF/E-mail durante importação
                continue;
            }
        }
        fclose($h);
        audit_log($_SESSION['user']['id'] ?? null, 'client_import_csv', 'clients', null, 'count='.$count);
        header('Location: ' . APP_URL . '/clients');
    }

    public static function exportCsv(): void {
        require_login();
        require_role(['admin','recepcao','financeiro']);
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="clients_export.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['id','name','cpf_cnpj','email','phone','address'], ';');
        [$rows, $total] = ClientModel::paginate('', 100000, 0);
        foreach ($rows as $c) {
            fputcsv($out, [$c['id'],$c['name'],$c['cpf_cnpj'],$c['email'],$c['phone'],$c['address']], ';');
        }
        fclose($out);
        audit_log($_SESSION['user']['id'] ?? null, 'client_export_csv', 'clients');
    }
}
