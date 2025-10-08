<?php
require_once __DIR__ . '/../middlewares/auth.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../helpers/errors.php';
require_once __DIR__ . '/../helpers/stock.php';
require_once __DIR__ . '/../helpers/db.php';
require_once __DIR__ . '/../models/ProductModel.php';

class ProductsController {
    /**
     * Lista produtos/serviços com filtros e paginação
     * Mostra itens com baixo estoque e renderiza a view
     */
    public static function index(): void {
        require_login();
        require_role(['admin','recepcao','veterinario','financeiro']);
        $q = sanitize_string($_GET['q'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        [$products, $total] = ProductModel::paginate($q, $limit, $offset);
        $totalPages = ceil($total / $limit);
        $low = ProductModel::lowStock();
        $flash_error = $_SESSION['products_error'] ?? null; if ($flash_error) { unset($_SESSION['products_error']); }

        // Filtros do relatório de movimentações
        $mtype = sanitize_string($_GET['mtype'] ?? ''); // entrada|saida
        $mreason = sanitize_string($_GET['reason_code'] ?? '');
        $muserName = sanitize_string($_GET['user_name'] ?? '');
        $mfrom = sanitize_string($_GET['from'] ?? '');
        $mto = sanitize_string($_GET['to'] ?? '');
        $pdo = DB::getConnection();
        // Garantir que a tabela de movimentações tem os campos necessários
        ensure_stock_movements_schema($pdo);
        $where = [];
        $params = [];
        if (in_array($mtype, ['entrada','saida'], true)) { $where[] = 'sm.type = :type'; $params[':type'] = $mtype; }
        if ($mreason !== '') { $where[] = 'sm.reason_code = :reason'; $params[':reason'] = $mreason; }
        if ($muserName !== '') { $where[] = 'u.name LIKE :uname'; $params[':uname'] = '%'.$muserName.'%'; }
        if ($mfrom !== '') { $where[] = 'sm.created_at >= :from'; $params[':from'] = $mfrom . ' 00:00:00'; }
        if ($mto !== '') { $where[] = 'sm.created_at <= :to'; $params[':to'] = $mto . ' 23:59:59'; }
        $sql = 'SELECT sm.*, p.name AS product_name, u.name AS user_name FROM stock_movements sm JOIN products p ON p.id = sm.product_id LEFT JOIN users u ON u.id = sm.user_id';
        if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
        $sql .= ' ORDER BY sm.created_at DESC LIMIT 50';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $movements = $stmt->fetchAll();
        
        render('products/index', compact('products','q','total','low','flash_error','movements','mtype','mreason','muserName','mfrom','mto', 'page', 'totalPages', 'limit'));
    }

    /**
     * Cria novo produto/serviço com validação de campos
     * Em erro, exibe listagem com mensagem; registra auditoria
     */
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
        try {
            $id = ProductModel::create($data);
            audit_log($_SESSION['user']['id'] ?? null, 'product_create', 'products', $id, json_encode($data));
            header('Location: ' . APP_URL . '/products');
        } catch (Throwable $e) {
            [$products, $total] = ProductModel::paginate('', 100, 0);
            $low = ProductModel::lowStock();
            $flash_error = friendly_pdo_message($e, 'produto');
            render('products/index', compact('products','low','flash_error'));
        }
    }

    /**
     * Atualiza produto/serviço; exige perfil admin e CSRF
     * Em erro, retorna à listagem com mensagem; registra auditoria
     */
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
        try {
            ProductModel::update($id, $data);
            audit_log($_SESSION['user']['id'] ?? null, 'product_update', 'products', $id, json_encode($data));
            header('Location: ' . APP_URL . '/products');
        } catch (Throwable $e) {
            [$products, $total] = ProductModel::paginate('', 100, 0);
            $low = ProductModel::lowStock();
            $flash_error = friendly_pdo_message($e, 'produto');
            render('products/index', compact('products','low','flash_error'));
        }
    }

    /**
     * Exclui produto/serviço; exige perfil admin e CSRF
     * Registra auditoria e redireciona para listagem
     */
    public static function delete(int $id): void {
        require_login();
        require_role(['admin']);
        csrf_validate();
        ProductModel::delete($id);
        audit_log($_SESSION['user']['id'] ?? null, 'product_delete', 'products', $id);
        header('Location: ' . APP_URL . '/products');
    }

    /**
     * Lança entrada de estoque manual para um produto
     * Ajusta saldo e registra auditoria; exige perfis admin/financeiro
     */
    public static function stockEntry(): void {
        require_login();
        require_role(['admin','financeiro']);
        csrf_validate();
        $pid = (int)($_POST['product_id'] ?? 0);
        $qty = (int)($_POST['quantity'] ?? 0);
        $reasonCode = trim($_POST['reason_code'] ?? '');
        $notes = sanitize_string($_POST['notes'] ?? '');
        $batch = sanitize_string($_POST['batch'] ?? '');
        if ($pid <= 0 || $qty <= 0 || $reasonCode === '' || !validate_stock_reason('entrada', $reasonCode)) {
            $_SESSION['products_error'] = 'Movimentação inválida: selecione um motivo válido e informe quantidade.';
            header('Location: ' . APP_URL . '/products');
            return;
        }
        if (stock_adjust($pid, $qty, 'entrada', $reasonCode, (int)$_SESSION['user']['id'], $notes ?: null, $batch ?: null)) {
            audit_log($_SESSION['user']['id'] ?? null, 'stock_entry', 'products', $pid, 'qty='.$qty.';reason='.$reasonCode);
        } else {
            $_SESSION['products_error'] = 'Falha ao registrar a entrada de estoque.';
        }
        header('Location: ' . APP_URL . '/products');
    }

    /**
     * Lança saída de estoque manual para um produto
     * Ajusta saldo e registra auditoria; exige perfis admin/financeiro
     */
    public static function stockExit(): void {
        require_login();
        require_role(['admin','financeiro']);
        csrf_validate();
        $pid = (int)($_POST['product_id'] ?? 0);
        $qty = (int)($_POST['quantity'] ?? 0);
        $reasonCode = trim($_POST['reason_code'] ?? '');
        $notes = sanitize_string($_POST['notes'] ?? '');
        $batch = sanitize_string($_POST['batch'] ?? '');
        if ($pid <= 0 || $qty <= 0 || $reasonCode === '' || !validate_stock_reason('saida', $reasonCode)) {
            $_SESSION['products_error'] = 'Movimentação inválida: selecione um motivo válido e informe quantidade.';
            header('Location: ' . APP_URL . '/products');
            return;
        }
        if (stock_adjust($pid, $qty, 'saida', $reasonCode, (int)$_SESSION['user']['id'], $notes ?: null, $batch ?: null)) {
            audit_log($_SESSION['user']['id'] ?? null, 'stock_exit', 'products', $pid, 'qty='.$qty.';reason='.$reasonCode);
        } else {
            $_SESSION['products_error'] = 'Falha ao registrar a saída de estoque.';
        }
        header('Location: ' . APP_URL . '/products');
    }
}
