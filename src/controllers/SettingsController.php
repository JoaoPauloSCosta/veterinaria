<?php
require_once __DIR__ . '/../middlewares/auth.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../models/SettingsModel.php';

class SettingsController {
    private static function ensureAdmin(): void {
        require_login();
        require_role(['admin']);
    }

    public static function index(): void {
        self::ensureAdmin();
        $settings = SettingsModel::all();
        render('settings/index', compact('settings'));
    }

    public static function save(): void {
        self::ensureAdmin();
        csrf_validate();

        // Collect inputs
        $kv = [];
        foreach ([
            // Gerais
            'clinic_name','clinic_address','clinic_phone','clinic_email','timezone','locale',
            // SMTP
            'smtp_host','smtp_port','smtp_user','smtp_pass','smtp_secure','smtp_from','smtp_from_name',
            // Integrações
            'webhook_url','api_key',
            // Estoque/Financeiro
            'min_stock_alert','finance_report_period','currency','tax_rate','payments_card','payments_boleto','payments_pix',
            // Segurança
            'password_min_length','password_exp_days',
            // Notificações
            'notify_email','notify_system','notify_appointment','notify_vaccine','notify_due'
        ] as $key) {
            $kv[$key] = sanitize_string($_POST[$key] ?? '');
        }

        // Logo upload (optional)
        if (!empty($_FILES['logo']['name'])) {
            $file = $_FILES['logo'];
            if ($file['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['png','jpg','jpeg','svg'])) {
                    $targetDir = __DIR__ . '/../../public/uploads';
                    if (!is_dir($targetDir)) @mkdir($targetDir, 0777, true);
                    $name = 'logo.' . $ext;
                    $dest = $targetDir . '/' . $name;
                    move_uploaded_file($file['tmp_name'], $dest);
                    $kv['logo_path'] = '/uploads/' . $name;
                }
            }
        }

        SettingsModel::setMany($kv);
        audit_log($_SESSION['user']['id'] ?? null, 'settings_save', 'settings', null, json_encode($kv));
        header('Location: ' . APP_URL . '/settings');
    }

    public static function reset(): void {
        self::ensureAdmin();
        csrf_validate();
        SettingsModel::resetAll();
        audit_log($_SESSION['user']['id'] ?? null, 'settings_reset', 'settings');
        header('Location: ' . APP_URL . '/settings');
    }
}
