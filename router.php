<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/src/helpers/db.php';
require_once __DIR__ . '/src/helpers/security.php';
require_once __DIR__ . '/src/helpers/format.php';
require_once __DIR__ . '/src/middlewares/auth.php';
require_once __DIR__ . '/src/controllers/ClientsController.php';
require_once __DIR__ . '/src/controllers/PetsController.php';
require_once __DIR__ . '/src/controllers/AppointmentsController.php';
require_once __DIR__ . '/src/controllers/ProductsController.php';
require_once __DIR__ . '/src/controllers/RecordsController.php';
require_once __DIR__ . '/src/controllers/SalesController.php';
require_once __DIR__ . '/src/controllers/UsersController.php';
require_once __DIR__ . '/src/controllers/SettingsController.php';
require_once __DIR__ . '/src/controllers/PasswordController.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = rtrim(parse_url(APP_URL, PHP_URL_PATH), '/');
$path = '/' . ltrim(str_replace($basePath, '', $uri), '/');
$method = $_SERVER['REQUEST_METHOD'];

// Normalizações para evitar 404 ao acessar a raiz
if ($path === '' || $path === false) {
    $path = '/';
}
if ($path === '/index.php') {
    $path = '/';
}

function render(string $view, array $data = []): void {
    extract($data);
    include __DIR__ . '/src/views/layouts/header.php';
    include __DIR__ . '/src/views/' . $view . '.php';
    include __DIR__ . '/src/views/layouts/footer.php';
}


switch (true) {
    case $path === '/login' && $method === 'GET':
        render('auth/login');
        break;
    case $path === '/login' && $method === 'POST':
        csrf_validate();
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        try {
            $pdo = DB::getConnection();
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email AND is_active = 1 LIMIT 1');
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();
            if ($user && password_verify($password, $user['password_hash'])) {
                start_session_safe();
                $_SESSION['user'] = [
                    'id' => (int)$user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                ];
                audit_log($user['id'], 'login_success');
                header('Location: ' . APP_URL . '/dashboard');
                exit;
            }
        } catch (Throwable $e) {
            error_log($e->getMessage());
        }
        audit_log(null, 'login_failed', null, null, 'email=' . $email);
        $error = 'Credenciais inválidas.';
        render('auth/login', compact('error'));
        break;
    case $path === '/logout':
        start_session_safe();
        $uid = $_SESSION['user']['id'] ?? null;
        session_destroy();
        audit_log($uid, 'logout');
        header('Location: ' . APP_URL . '/login');
        break;
    // Password reset
    case $path === '/password/reset' && $method === 'POST':
        PasswordController::requestReset();
        break;
    case $path === '/password/new' && $method === 'GET':
        PasswordController::showNew();
        break;
    case $path === '/password/new' && $method === 'POST':
        PasswordController::setNew();
        break;
    case $path === '/' || $path === '/dashboard':
        require_login();
        render('dashboard/index');
        break;
    // Agenda routes
    case $path === '/agenda' && $method === 'GET':
        AppointmentsController::index();
        break;
    case $path === '/agenda/create' && $method === 'POST':
        AppointmentsController::create();
        break;
    case preg_match('#^/agenda/(\d+)/move$#', $path, $m) === 1 && $method === 'POST':
        AppointmentsController::move((int)$m[1]);
        break;
    case preg_match('#^/agenda/(\d+)/cancel$#', $path, $m) === 1 && $method === 'POST':
        AppointmentsController::cancel((int)$m[1]);
        break;
    // Clients routes
    case $path === '/clients' && $method === 'GET':
        ClientsController::index();
        break;
    case $path === '/clients/create' && $method === 'POST':
        ClientsController::create();
        break;
    case preg_match('#^/clients/(\d+)/edit$#', $path, $m) === 1 && $method === 'POST':
        ClientsController::edit((int)$m[1]);
        break;
    case preg_match('#^/clients/(\d+)/delete$#', $path, $m) === 1 && $method === 'POST':
        ClientsController::delete((int)$m[1]);
        break;

    // Pets routes
    case $path === '/pets' && $method === 'GET':
        PetsController::index();
        break;
    case $path === '/pets/create' && $method === 'POST':
        PetsController::create();
        break;
    case preg_match('#^/pets/(\d+)/edit$#', $path, $m) === 1 && $method === 'POST':
        PetsController::edit((int)$m[1]);
        break;
    case preg_match('#^/pets/(\d+)/delete$#', $path, $m) === 1 && $method === 'POST':
        PetsController::delete((int)$m[1]);
        break;

    // Products & stock routes
    case $path === '/products' && $method === 'GET':
        ProductsController::index();
        break;
    case $path === '/products/create' && $method === 'POST':
        ProductsController::create();
        break;
    case preg_match('#^/products/(\d+)/edit$#', $path, $m) === 1 && $method === 'POST':
        ProductsController::edit((int)$m[1]);
        break;
    case preg_match('#^/products/(\d+)/delete$#', $path, $m) === 1 && $method === 'POST':
        ProductsController::delete((int)$m[1]);
        break;
    case $path === '/stock/entry' && $method === 'POST':
        ProductsController::stockEntry();
        break;
    case $path === '/stock/exit' && $method === 'POST':
        ProductsController::stockExit();
        break;

    // Records routes
    case preg_match('#^/records/(\d+)$#', $path, $m) === 1 && $method === 'GET':
        RecordsController::list((int)$m[1]);
        break;
    case preg_match('#^/records/(\d+)/create$#', $path, $m) === 1 && $method === 'POST':
        RecordsController::create((int)$m[1]);
        break;

    // Sales routes
    case $path === '/sales/pos' && $method === 'GET':
        SalesController::pos();
        break;
    case $path === '/sales/checkout' && $method === 'POST':
        SalesController::checkout();
        break;
    
    // Settings (Admin only)
    case $path === '/settings' && $method === 'GET':
        SettingsController::index();
        break;
    case $path === '/settings/save' && $method === 'POST':
        SettingsController::save();
        break;
    case $path === '/settings/reset' && $method === 'POST':
        SettingsController::reset();
        break;

    
    // Admin - Users routes
    case $path === '/users/new' && $method === 'GET':
        UsersController::new();
        break;
    case $path === '/users/new' && $method === 'POST':
        UsersController::create();
        break;
    // CSV import/export (clients)
    case $path === '/import/csv' && $method === 'POST':
        ClientsController::importCsv();
        break;
    case $path === '/export/csv' && $method === 'GET':
        ClientsController::exportCsv();
        break;
    default:
        http_response_code(404);
        echo 'Rota não encontrada.';
}
