<?php
require_once __DIR__ . '/../middlewares/auth.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../helpers/errors.php';
require_once __DIR__ . '/../helpers/db.php';

class UsersController {
    public static function new(): void {
        require_login();
        require_role(['admin']);
        render('users/create');
    }

    public static function create(): void {
        require_login();
        require_role(['admin']);
        csrf_validate();

        $name = sanitize_string($_POST['name'] ?? '');
        $email = sanitize_string($_POST['email'] ?? '');
        $role = sanitize_string($_POST['role'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $allowedRoles = ['admin','veterinario','recepcao','financeiro'];
        if ($name === '' || !validate_email($email) || !in_array($role, $allowedRoles, true) || strlen($password) < 6) {
            $error = 'Dados inválidos. Verifique os campos.';
            render('users/create', compact('error', 'name', 'email', 'role', 'isActive'));
            return;
        }

        $pdo = DB::getConnection();
        // Verificar email único
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email'=>$email]);
        if ($stmt->fetch()) {
            $error = 'Email já cadastrado.';
            render('users/create', compact('error', 'name', 'email', 'role', 'isActive'));
            return;
        }

        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (name, email, role, password_hash, is_active, created_at) VALUES (:name, :email, :role, :hash, :active, NOW())');
            $stmt->execute([
                ':name'=>$name,
                ':email'=>$email,
                ':role'=>$role,
                ':hash'=>$hash,
                ':active'=>$isActive,
            ]);
            audit_log($_SESSION['user']['id'] ?? null, 'user_create', 'users', (int)$pdo->lastInsertId());
            header('Location: ' . APP_URL . '/dashboard');
        } catch (Throwable $e) {
            $error = friendly_pdo_message($e, 'usuário');
            render('users/create', compact('error', 'name', 'email', 'role', 'isActive'));
        }
    }
}
