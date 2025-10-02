<?php
function sanitize_string(?string $v): string { return trim((string)$v); }
function sanitize_int($v): int { return (int)$v; }

function validate_email(?string $email): bool {
    if ($email === null || $email === '') return true; // opcional
    return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validate_cpf_cnpj(?string $doc): bool {
    if ($doc === null || $doc === '') return true; // opcional
    $digits = preg_replace('/\D+/', '', $doc);
    // valida tamanho simples (não implementa dígitos verificadores completos aqui)
    return in_array(strlen($digits), [11, 14], true);
}
