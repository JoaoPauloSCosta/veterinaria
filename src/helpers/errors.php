<?php
/**
 * Retorna mensagem amigável para erros PDO e violações comuns
 * Detecta chaves únicas como CPF/CNPJ e e-mail para feedback claro
 */
function friendly_pdo_message(Throwable $e, string $entity = 'registro'): string {
    // Default safe message
    $msg = 'Operação não pôde ser concluída. Verifique os dados e tente novamente.';
    if ($e instanceof PDOException) {
        $sqlstate = $e->getCode();
        $text = strtolower($e->getMessage() ?? '');
        if ($sqlstate === '23000') { // Integrity constraint violation
            if (str_contains($text, 'cpf') || str_contains($text, 'cpf_cnpj')) {
                return 'CPF/CNPJ já cadastrado.';
            }
            if (str_contains($text, 'email')) {
                return 'E-mail já cadastrado.';
            }
            if (str_contains($text, 'unique') || str_contains($text, 'duplicate entry')) {
                return ucfirst($entity) . ' já cadastrado.';
            }
        }
    }
    return $msg;
}
