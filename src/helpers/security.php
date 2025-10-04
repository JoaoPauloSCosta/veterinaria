<?php
require_once __DIR__ . '/../../config.php';

/**
 * Escapa valores para HTML com segurança
 * Aceita arrays/objetos serializando para JSON
 */
function e($value): string {
    if ($value === null) {
        return '';
    }
    if (is_array($value) || is_object($value)) {
        $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Gera/retorna token CSRF armazenado em sessão
 * Inicia sessão se necessário
 */
function csrf_token(): string {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Retorna input HTML hidden com token CSRF
 * Compatível com forms POST
 */
function csrf_input(): string {
    return '<input type="hidden" name="' . e(CSRF_TOKEN_NAME) . '" value="' . e(csrf_token()) . '">';
}

/**
 * Valida token CSRF em requisições POST
 * Retorna 400 e encerra se inválido
 */
function csrf_validate(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $token = $_POST[CSRF_TOKEN_NAME] ?? '';
        if (!hash_equals($_SESSION[CSRF_TOKEN_NAME] ?? '', $token)) {
            http_response_code(400);
            exit('CSRF token inválido.');
        }
    }
}

/**
 * Valida upload: tamanho, MIME e extensão
 * Retorna [ok, novoNome, mime] ou [false, mensagem]
 */
function validate_upload(array $file, array $allowedMime = UPLOAD_ALLOWED_TYPES, array $allowedExt = UPLOAD_ALLOWED_EXTENSIONS): array {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [false, 'Erro no upload.'];
    }
    if ($file['size'] > UPLOAD_MAX_SIZE) {
        return [false, 'Arquivo excede o tamanho máximo.'];
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowedMime, true)) {
        return [false, 'Tipo de arquivo não permitido.'];
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        return [false, 'Extensão de arquivo não permitida.'];
    }
    $newName = bin2hex(random_bytes(16)) . '.' . $ext;
    return [true, $newName, $mime];
}

/**
 * Registra ação no log de auditoria com IP
 * Ignora erros silenciosamente para não quebrar fluxo
 */
function audit_log(?int $userId, string $action, ?string $entity = null, ?int $entityId = null, ?string $details = null): void {
    try {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('INSERT INTO audit_logs (user_id, action, entity, entity_id, details, ip_address) VALUES (:user_id, :action, :entity, :entity_id, :details, :ip)');
        $stmt->execute([
            ':user_id' => $userId,
            ':action' => $action,
            ':entity' => $entity,
            ':entity_id' => $entityId,
            ':details' => $details,
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    } catch (Throwable $e) {
        error_log('Audit log failed: ' . $e->getMessage());
    }
}
