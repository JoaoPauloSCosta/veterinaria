<?php
require_once __DIR__ . '/../models/SettingsModel.php';

/**
 * Envia e-mail simples em HTML (com alternativa texto) para múltiplos destinatários.
 * Usa cabeçalhos padrão `mail()`; em produção pode ser substituído por SMTP.
 */
function send_email(array $to, string $subject, string $html, ?string $plain = null): bool {
    if (empty($to)) { return false; }
    $settings = SettingsModel::all();
    $fromEmail = trim($settings['from_email'] ?? 'no-reply@localhost');
    $fromName = trim($settings['company_name'] ?? 'Veterinária');

    $boundary = md5(uniqid((string)mt_rand(), true));
    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'From: ' . ($fromName ? (mb_encode_mimeheader($fromName) . " <{$fromEmail}>") : $fromEmail);
    $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';

    $plain = $plain ?: strip_tags($html);
    $body = '';
    $body .= "--{$boundary}\r\n";
    $body .= "Content-Type: text/plain; charset=utf-8\r\n\r\n";
    $body .= $plain . "\r\n";
    $body .= "--{$boundary}\r\n";
    $body .= "Content-Type: text/html; charset=utf-8\r\n\r\n";
    $body .= $html . "\r\n";
    $body .= "--{$boundary}--";

    $toHeader = implode(', ', array_map('trim', $to));
    try {
        return (bool)mail($toHeader, $subject, $body, implode("\r\n", $headers));
    } catch (Throwable $e) {
        error_log('send_email error: ' . $e->getMessage());
        return false;
    }
}