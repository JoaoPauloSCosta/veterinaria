<?php
require_once __DIR__ . '/../middlewares/auth.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../helpers/errors.php';
require_once __DIR__ . '/../models/ClientModel.php';

class ClientsController {
    /**
     * Lista clientes com filtros e paginação; aplica controle de acesso
     * Renderiza a view com mensagens de sucesso quando aplicável
     */
    public static function index(): void {
        require_login();
        require_role(['admin','recepcao','financeiro','veterinario']);
        $q = sanitize_string($_GET['q'] ?? '');
        $success = sanitize_string($_GET['success'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $flash_success = '';
        if ($success === 'created') { $flash_success = 'Cliente cadastrado com sucesso.'; }
        if ($success === 'deleted') { $flash_success = 'Cliente excluido'; }
        
        [$clients, $total] = ClientModel::paginate($q, $limit, $offset);
        $totalPages = ceil($total / $limit);
        
        render('clients/index', compact('clients', 'q', 'total', 'flash_success', 'page', 'totalPages', 'limit'));
    }

    /**
     * Valida e cria novo cliente a partir dos dados do POST
     * Em caso de erro, mostra a listagem com mensagem; registra auditoria
     */
    public static function create(): void {
        require_login();
        require_role(['admin','recepcao']);
        csrf_validate();
        $data = [
            'name' => sanitize_string($_POST['name'] ?? ''),
            'cpf_cnpj' => sanitize_string($_POST['cpf_cnpj'] ?? ''),
            'email' => sanitize_string($_POST['email'] ?? ''),
            'phone' => sanitize_string($_POST['phone'] ?? ''),
            'landline_phone' => sanitize_string($_POST['landline_phone'] ?? ''),
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
            header('Location: ' . APP_URL . '/clients?success=created');
        } catch (Throwable $e) {
            $q = '';
            [$clients, $total] = ClientModel::paginate('', 50, 0);
            $flash_error = friendly_pdo_message($e, 'cliente');
            render('clients/index', compact('clients','q','total','flash_error'));
        }
    }

    /**
     * Atualiza dados do cliente com validação e controle de acesso
     * Em caso de erro, renderiza tela de edição com mensagem; registra auditoria
     */
    public static function edit(int $id): void {
        require_login();
        require_role(['admin','recepcao']);
        csrf_validate();
        $data = [
            'name' => sanitize_string($_POST['name'] ?? ''),
            'cpf_cnpj' => sanitize_string($_POST['cpf_cnpj'] ?? ''),
            'email' => sanitize_string($_POST['email'] ?? ''),
            'phone' => sanitize_string($_POST['phone'] ?? ''),
            'landline_phone' => sanitize_string($_POST['landline_phone'] ?? ''),
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

    /**
     * Exclui um cliente; exige perfil admin e proteção CSRF
     * Registra auditoria e redireciona com mensagem de sucesso
     */
    public static function delete(int $id): void {
        require_login();
        require_role(['admin']);
        csrf_validate();
        ClientModel::delete($id);
        audit_log($_SESSION['user']['id'] ?? null, 'client_delete', 'clients', $id);
        header('Location: ' . APP_URL . '/clients?success=deleted');
    }

    /**
     * Importa clientes de arquivo CSV com cabeçalho predefinido
     * Sanitiza e valida dados; ignora duplicidades; registra contagem em auditoria
     */
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
                'landline_phone' => sanitize_string($data['landline_phone'] ?? ''),
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

    /**
     * Exporta clientes para CSV para download com cabeçalho
     * Escreve linhas paginadas e registra ação em auditoria
     */
    public static function exportCsv(): void {
        require_login();
        require_role(['admin','recepcao','financeiro']);
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="clients_export.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['id','name','cpf_cnpj','email','phone','landline_phone','address'], ';');
        [$rows, $total] = ClientModel::paginate('', 100000, 0);
        foreach ($rows as $c) {
            fputcsv($out, [$c['id'],$c['name'],$c['cpf_cnpj'],$c['email'],$c['phone'],$c['landline_phone'],$c['address']], ';');
        }
        fclose($out);
        audit_log($_SESSION['user']['id'] ?? null, 'client_export_csv', 'clients');
    }
}
