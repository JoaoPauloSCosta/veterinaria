<?php
require_once __DIR__ . '/../helpers/db.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/SettingsModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';

/**
 * Serviço de alerta de estoque crítico
 * - Cria notificações para admins
 * - Envia e-mails aos admins (se habilitado)
 */
class StockAlertService {
    /**
     * Dispara alerta quando houver produtos abaixo do mínimo.
     * Coleta lista completa dos itens críticos e notifica administradores.
     */
    public static function dispatch(): void {
        try {
            $low = ProductModel::lowStock();
            if (empty($low)) { return; }

            $settings = SettingsModel::all();
            $notifyEmail = !empty($settings['notify_email']);
            $notifySystem = !empty($settings['notify_system']);

            // Buscar admins ativos
            $pdo = DB::getConnection();
            $stmt = $pdo->query("SELECT id, name, email FROM users WHERE role='admin' AND is_active=1");
            $admins = $stmt->fetchAll();
            if (empty($admins)) { return; }

            // Montar resumo e tabela para mensagem
            $count = count($low);
            $title = "Estoque crítico: {$count} produto(s) abaixo do mínimo";
            $plainLines = [];
            $htmlRows = '';
            foreach ($low as $p) {
                $name = $p['name'] ?? ('ID ' . ($p['id'] ?? '?'));
                $sq = (int)($p['stock_quantity'] ?? 0);
                $min = (int)($p['min_stock_level'] ?? 0);
                $plainLines[] = "$name — atual: $sq, mínimo: $min";
                $link = (APP_URL ?? '') . "/products?restock_id=" . (int)$p['id'] . "#restock";
                $htmlRows .= "<tr><td>" . htmlspecialchars($name) . "</td><td style='text-align:center;'>$sq</td><td style='text-align:center;'>$min</td><td><a href='" . htmlspecialchars($link) . "' style='color:#0d6efd;'>Repor</a></td></tr>";
            }
            $plainBody = $title . "\n\n" . implode("\n", $plainLines) . "\n\nAcesse: " . ((APP_URL ?? '') . '/products');
            $htmlBody = "<p style='font-family:Arial,sans-serif;margin:0 0 8px;'>$title</p>"
                . "<table cellpadding='6' cellspacing='0' style='border-collapse:collapse;font-family:Arial,sans-serif;font-size:14px;'>"
                . "<thead><tr style='background:#f8f9fa;'><th style='text-align:left;'>Produto</th><th>Atual</th><th>Mínimo</th><th>Ação</th></tr></thead>"
                . "<tbody>" . $htmlRows . "</tbody></table>"
                . "<p style='font-family:Arial,sans-serif;margin-top:12px;'>Abra o painel: <a href='" . htmlspecialchars((APP_URL ?? '') . '/products') . "'>Estoque & Produtos</a></p>";

            // Notificação no sistema
            if ($notifySystem) {
                foreach ($admins as $a) {
                    NotificationModel::create((int)$a['id'], 'Estoque crítico', $plainBody, 'stock');
                }
            }

            // E-mail (se habilitado)
            if ($notifyEmail) {
                require_once __DIR__ . '/../helpers/mailer.php';
                $subject = 'Alerta de Estoque Crítico';
                $toList = [];
                foreach ($admins as $a) {
                    $email = trim((string)($a['email'] ?? ''));
                    if ($email !== '') { $toList[] = $email; }
                }
                if (!empty($toList)) {
                    send_email($toList, $subject, $htmlBody, $plainBody);
                }
            }
        } catch (Throwable $e) {
            error_log('StockAlertService dispatch failed: ' . $e->getMessage());
        }
    }
}