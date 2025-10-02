<?php
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../helpers/db.php';

function start_session_safe(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function require_login(): void {
    start_session_safe();
    if (empty($_SESSION['user'])) {
        header('Location: ' . APP_URL . '/login');
        exit;
    }
}

function require_role(array $roles): void {
    start_session_safe();
    $user = $_SESSION['user'] ?? null;
    if (!$user || !in_array($user['role'], $roles, true)) {
        http_response_code(403);
        exit('Acesso negado.');
    }
}
