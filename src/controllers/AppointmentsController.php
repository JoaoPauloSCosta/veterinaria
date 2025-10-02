<?php
require_once __DIR__ . '/../middlewares/auth.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../models/PetModel.php';
require_once __DIR__ . '/../models/UserModel.php';

class AppointmentsController {
    public static function index(): void {
        require_login();
        require_role(['admin','recepcao','veterinario']);
        $from = sanitize_string($_GET['from'] ?? '');
        $to = sanitize_string($_GET['to'] ?? '');
        $vet = (int)($_GET['vet'] ?? 0) ?: null;
        $items = AppointmentModel::list($from, $to, $vet);
        $pets = PetModel::listAllWithClient();
        $vets = UserModel::listByRole('veterinario');
        render('appointments/index', compact('items','from','to','vet','pets','vets'));
    }

    public static function create(): void {
        require_login();
        require_role(['admin','recepcao']);
        csrf_validate();
        $data = [
            'pet_id' => (int)($_POST['pet_id'] ?? 0),
            'vet_id' => (int)($_POST['vet_id'] ?? 0),
            'start_time' => sanitize_string($_POST['start_time'] ?? ''),
            'end_time' => sanitize_string($_POST['end_time'] ?? ''),
            'room' => sanitize_string($_POST['room'] ?? ''),
            'status' => 'agendada',
            'notes' => sanitize_string($_POST['notes'] ?? ''),
            'created_by' => $_SESSION['user']['id'] ?? 0,
        ];
        $errors = [];
        if ($data['pet_id'] <= 0) $errors[] = 'Pet obrigatório';
        if ($data['vet_id'] <= 0) $errors[] = 'Veterinário obrigatório';
        if (!$data['start_time'] || !$data['end_time']) $errors[] = 'Horários obrigatórios';
        if (AppointmentModel::hasConflict($data['vet_id'], $data['room'], $data['start_time'], $data['end_time'])) {
            $errors[] = 'Já existe um agendamento para este veterinário neste horário. Escolha outra data ou hora.';
        }
        if ($errors) {
            $from = ''; $to = ''; $vet = null; $items = AppointmentModel::list('', '', null);
            $flash_error = implode(' ', $errors);
            render('appointments/index', compact('items','from','to','vet','flash_error'));
            return;
        }
        $id = AppointmentModel::create($data);
        audit_log($_SESSION['user']['id'] ?? null, 'appointment_create', 'appointments', $id, json_encode($data));
        header('Location: ' . APP_URL . '/agenda');
    }

    public static function move(int $id): void {
        require_login();
        require_role(['admin','recepcao']);
        csrf_validate();
        $start = sanitize_string($_POST['start_time'] ?? '');
        $end = sanitize_string($_POST['end_time'] ?? '');
        $room = sanitize_string($_POST['room'] ?? '');
        // Fetch appointment to check vet
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('SELECT vet_id FROM appointments WHERE id = :id');
        $stmt->execute([':id'=>$id]);
        $vetId = (int)$stmt->fetchColumn();
        if (AppointmentModel::hasConflict($vetId, $room, $start, $end, $id)) {
            $from = ''; $to = ''; $vet = null; $items = AppointmentModel::list('', '', null);
            $flash_error = 'Já existe um agendamento para este veterinário neste horário. Escolha outra data ou hora.';
            render('appointments/index', compact('items','from','to','vet','flash_error'));
            return;
        }
        AppointmentModel::updateTimes($id, $start, $end, $room);
        audit_log($_SESSION['user']['id'] ?? null, 'appointment_move', 'appointments', $id, json_encode(['start'=>$start,'end'=>$end,'room'=>$room]));
        header('Location: ' . APP_URL . '/agenda');
    }

    public static function cancel(int $id): void {
        require_login();
        require_role(['admin','recepcao','veterinario']);
        csrf_validate();
        // Check current status to avoid double cancel
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('SELECT status FROM appointments WHERE id = :id');
        $stmt->execute([':id'=>$id]);
        $status = $stmt->fetchColumn();
        if ($status === 'cancelada') {
            $from = ''; $to = ''; $vet = null; $items = AppointmentModel::list('', '', null);
            $flash_error = 'Agendamento já está cancelado.';
            render('appointments/index', compact('items','from','to','vet','flash_error'));
            return;
        }
        AppointmentModel::cancel($id);
        audit_log($_SESSION['user']['id'] ?? null, 'appointment_cancel', 'appointments', $id);
        header('Location: ' . APP_URL . '/agenda');
    }
}
