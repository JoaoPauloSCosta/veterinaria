<?php
/**
 * Normaliza string removendo espaços nas extremidades
 * Converte null para string vazia
 */
function sanitize_string(?string $v): string { return trim((string)$v); }
/**
 * Converte valor para inteiro de forma segura
 * Útil para parâmetros numéricos de entrada
 */
function sanitize_int($v): int { return (int)$v; }

/**
 * Valida email; campo é opcional (null/vazio é aceito)
 * Usa filtro nativo do PHP
 */
function validate_email(?string $email): bool {
    if ($email === null || $email === '') return true; // opcional
    return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Valida CPF/CNPJ de forma básica pelo tamanho
 * Campo opcional; não calcula dígitos verificadores
 */
function validate_cpf_cnpj(?string $doc): bool {
    if ($doc === null || $doc === '') return true; // opcional
    $digits = preg_replace('/\D+/', '', $doc);
    // valida tamanho simples (não implementa dígitos verificadores completos aqui)
    return in_array(strlen($digits), [11, 14], true);
}
