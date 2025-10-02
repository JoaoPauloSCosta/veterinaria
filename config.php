<?php
/**
 * Configuração do Sistema de Gestão Veterinária
 * 
 * IMPORTANTE: Não versionar este arquivo com credenciais reais.
 * Copie config.example.php para config.php e ajuste os valores.
 */

// Configurações do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'veterinaria_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configurações da Aplicação
define('APP_NAME', 'Sistema Veterinário');
define('APP_URL', 'http://localhost/veterinaria/public');
define('APP_ENV', 'development'); // development, production

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações de Sessão
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Mudar para 1 em HTTPS
ini_set('session.cookie_samesite', 'Strict');

// Configurações de Upload
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf']);
define('UPLOAD_ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf']);
define('UPLOAD_PATH', __DIR__ . '/uploads/');

// Configurações de Segurança
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_RESET_EXPIRY', 3600); // 1 hora em segundos

// Configurações de Exibição de Erros
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/logs/php-errors.log');
}

// Criar diretórios necessários se não existirem
$directories = [
    __DIR__ . '/uploads/',
    __DIR__ . '/uploads/pets/',
    __DIR__ . '/uploads/records/',
    __DIR__ . '/logs/',
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}
