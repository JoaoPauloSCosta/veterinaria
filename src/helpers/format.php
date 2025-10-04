<?php
/**
 * Formata data para padrão brasileiro dd/mm/YYYY
 * Retorna string original em caso de falha
 */
function br_date($value): string {
    if (empty($value)) return '';
    try {
        $dt = new DateTime((string)$value);
        return $dt->format('d/m/Y');
    } catch (Throwable $e) {
        return (string)$value;
    }
}

/**
 * Converte data BR (dd/mm/YYYY) para ISO (YYYY-MM-DD)
 * Retorna null se formato inválido
 */
function br_to_iso_date(?string $value): ?string {
    $v = trim((string)$value);
    if ($v === '') return null;
    // Expect dd/mm/YYYY
    if (!preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $v, $m)) return null;
    [$all,$d,$mth,$y] = $m;
    return sprintf('%04d-%02d-%02d', (int)$y, (int)$mth, (int)$d);
}

/**
 * Formata hora para HH:MM
 * Retorna string original em caso de falha
 */
function br_time($value): string {
    if (empty($value)) return '';
    try {
        $dt = new DateTime((string)$value);
        return $dt->format('H:i');
    } catch (Throwable $e) {
        return (string)$value;
    }
}

/**
 * Formata data e hora para dd/mm/YYYY HH:MM
 * Retorna string original em caso de falha
 */
function br_datetime($value): string {
    if (empty($value)) return '';
    try {
        $dt = new DateTime((string)$value);
        return $dt->format('d/m/Y H:i');
    } catch (Throwable $e) {
        return (string)$value;
    }
}
