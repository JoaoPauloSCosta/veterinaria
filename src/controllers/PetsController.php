<?php
require_once __DIR__ . '/../middlewares/auth.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../helpers/format.php';
require_once __DIR__ . '/../models/PetModel.php';
require_once __DIR__ . '/../models/ClientModel.php';

class PetsController {
    /**
     * Lista pets com filtros e paginação; controla acesso por perfis
     * Carrega clientes para o formulário e renderiza a view
     */
    public static function index(): void {
        require_login();
        require_role(['admin','recepcao','veterinario']);
        $q = sanitize_string($_GET['q'] ?? '');
        // Mensagens via sessão para manter URL limpa
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $flash_success = $_SESSION['pet_success'] ?? '';
        if ($flash_success !== '') { unset($_SESSION['pet_success']); }
        
        [$pets, $total] = PetModel::paginate($q, $limit, $offset);
        $totalPages = ceil($total / $limit);
        $clients = ClientModel::listAll();
        
        render('pets/index', compact('pets','q','total','clients','flash_success', 'page', 'totalPages', 'limit'));
    }

    /**
     * Valida e cria novo pet a partir dos dados do POST
     * Converte data BR para ISO quando necessário; registra auditoria
     */
    public static function create(): void {
        require_login();
        require_role(['admin','recepcao','veterinario']);
        csrf_validate();
        $data = [
            'client_id' => (int)($_POST['client_id'] ?? 0),
            'name' => sanitize_string($_POST['name'] ?? ''),
            'species' => sanitize_string($_POST['species'] ?? ''),
            'breed' => sanitize_string($_POST['breed'] ?? ''),
            'birth_date' => sanitize_string($_POST['birth_date'] ?? ''),
            'gender' => sanitize_string($_POST['gender'] ?? ''),
            'color' => sanitize_string($_POST['color'] ?? ''),
            'notes' => sanitize_string($_POST['notes'] ?? ''),
        ];
        // Convert BR date to ISO if needed
        if (!empty($data['birth_date'])) {
            if (preg_match('#^\d{2}/\d{2}/\d{4}$#', $data['birth_date'])) {
                $iso = br_to_iso_date($data['birth_date']);
                $data['birth_date'] = $iso ?? '';
            }
        }
        $errors = [];
        if ($data['client_id'] <= 0) $errors[] = 'Cliente é obrigatório.';
        if ($data['name'] === '') $errors[] = 'Nome do pet é obrigatório.';
        if ($data['birth_date'] && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['birth_date'])) $errors[] = 'Data de nascimento inválida (use dd/mm/aaaa).';
        if ($errors) {
            [$pets, $total] = PetModel::paginate('', 50, 0);
            $flash_error = implode(' ', $errors);
            render('pets/index', compact('pets','flash_error'));
            return;
        }
        $id = PetModel::create($data);
        audit_log($_SESSION['user']['id'] ?? null, 'pet_create', 'pets', $id, json_encode($data));
        $_SESSION['pet_success'] = 'Pet cadastrado com sucesso.';
        header('Location: ' . APP_URL . '/pets');
    }

    /**
        * Atualiza dados do pet com validação e controle de acesso
        * Em caso de erro, renderiza tela de edição com mensagem; registra auditoria
        */
    public static function edit(int $id): void {
        require_login();
        require_role(['admin','recepcao','veterinario']);
        csrf_validate();
        $data = [
            'client_id' => (int)($_POST['client_id'] ?? 0),
            'name' => sanitize_string($_POST['name'] ?? ''),
            'species' => sanitize_string($_POST['species'] ?? ''),
            'breed' => sanitize_string($_POST['breed'] ?? ''),
            'birth_date' => sanitize_string($_POST['birth_date'] ?? ''),
            'gender' => sanitize_string($_POST['gender'] ?? ''),
            'color' => sanitize_string($_POST['color'] ?? ''),
            'notes' => sanitize_string($_POST['notes'] ?? ''),
        ];
        $errors = [];
        if ($data['client_id'] <= 0) $errors[] = 'Cliente é obrigatório.';
        if ($data['name'] === '') $errors[] = 'Nome do pet é obrigatório.';
        if ($data['birth_date'] && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['birth_date'])) $errors[] = 'Data de nascimento inválida (use YYYY-MM-DD).';
        if ($errors) {
            $pet = PetModel::find($id);
            $flash_error = implode(' ', $errors);
            render('pets/edit', compact('pet','flash_error'));
            return;
        }
        PetModel::update($id, $data);
        audit_log($_SESSION['user']['id'] ?? null, 'pet_update', 'pets', $id, json_encode($data));
        $_SESSION['pet_success'] = 'Pet atualizado com sucesso.';
        header('Location: ' . APP_URL . '/pets');
    }

    /**
     * Exclui um pet; exige perfil admin e proteção CSRF
     * Registra auditoria e redireciona com mensagem de sucesso
     */
    public static function delete(int $id): void {
        require_login();
        require_role(['admin']);
        csrf_validate();
        PetModel::delete($id);
        audit_log($_SESSION['user']['id'] ?? null, 'pet_delete', 'pets', $id);
        $_SESSION['pet_success'] = 'Pet excluído com sucesso.';
        header('Location: ' . APP_URL . '/pets');
    }
}
