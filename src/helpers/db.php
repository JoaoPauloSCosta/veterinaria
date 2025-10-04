<?php
require_once __DIR__ . '/../../config.php';

class DB {
    private static ?PDO $instance = null;

    private function __construct() {}

    /**
     * Retorna conexão PDO singleton com configurações seguras
     * Trata erros conforme ambiente e força modos de erro e fetch
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                if (APP_ENV === 'development') {
                    die('Erro de conexão: ' . htmlspecialchars($e->getMessage()));
                }
                error_log('DB connection failed');
                http_response_code(500);
                exit('Erro interno');
            }
        }
        return self::$instance;
    }
}
