<?php
require_once __DIR__ . '/../helpers/db.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../middlewares/auth.php';

class PasswordController {
    public static function requestReset(): void {
        csrf_validate();
        $email = trim($_POST['email'] ?? '');
        if ($email === '') { header('Location: ' . APP_URL . '/login'); return; }
        $pdo = DB::getConnection();
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + PASSWORD_RESET_EXPIRY);
        $stmt = $pdo->prepare('INSERT INTO password_resets (email, token, expires_at) VALUES (:email,:token,:expires)');
        $stmt->execute([':email'=>$email, ':token'=>$token, ':expires'=>$expires]);
        audit_log(null, 'password_reset_request', null, null, 'email='.$email);
        $link = APP_URL . '/password/new?token=' . urlencode($token);
        render('auth/reset_link', compact('link'));
    }

    public static function showNew(): void {
        $token = trim($_GET['token'] ?? '');
        render('auth/reset_new', compact('token'));
    }

    public static function setNew(): void {
        csrf_validate();
        $token = trim($_POST['token'] ?? '');
        $password = $_POST['password'] ?? '';
        if (strlen($password) < 6) { $error='Senha muito curta.'; render('auth/reset_new', compact('token','error')); return; }
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('SELECT email FROM password_resets WHERE token=:t AND expires_at >= NOW() LIMIT 1');
        $stmt->execute([':t'=>$token]);
        $email = $stmt->fetchColumn();
        if (!$email) { $error='Token inválido ou expirado.'; render('auth/reset_new', compact('token','error')); return; }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $upd = $pdo->prepare('UPDATE users SET password_hash=:h WHERE email=:e');
        $upd->execute([':h'=>$hash, ':e'=>$email]);
        $pdo->prepare('DELETE FROM password_resets WHERE token=:t')->execute([':t'=>$token]);
        audit_log(null, 'password_reset_success', null, null, 'email='.$email);
        header('Location: ' . APP_URL . '/login');
    }
}
