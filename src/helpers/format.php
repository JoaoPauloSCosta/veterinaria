<?php
function br_date($value): string {
    if (empty($value)) return '';
    try {
        $dt = new DateTime((string)$value);
        return $dt->format('d/m/Y');
    } catch (Throwable $e) {
        return (string)$value;
    }
}

function br_to_iso_date(?string $value): ?string {
    $v = trim((string)$value);
    if ($v === '') return null;
    // Expect dd/mm/YYYY
    if (!preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $v, $m)) return null;
    [$all,$d,$mth,$y] = $m;
    return sprintf('%04d-%02d-%02d', (int)$y, (int)$mth, (int)$d);
}

function br_time($value): string {
    if (empty($value)) return '';
    try {
        $dt = new DateTime((string)$value);
        return $dt->format('H:i');
    } catch (Throwable $e) {
        return (string)$value;
    }
}

function br_datetime($value): string {
    if (empty($value)) return '';
    try {
        $dt = new DateTime((string)$value);
        return $dt->format('d/m/Y H:i');
    } catch (Throwable $e) {
        return (string)$value;
    }
}
