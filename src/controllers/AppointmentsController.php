<?php
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../models/ClientModel.php';
require_once __DIR__ . '/../models/PetModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../middlewares/auth.php';
require_once __DIR__ . '/../helpers/format.php';
require_once __DIR__ . '/../helpers/pagination.php';

class AppointmentsController {
    /**
     * Exibe a página principal de agendamentos com filtros e paginação
     * Carrega lista de agendamentos, veterinários e pets para o formulário
     */
    public function index(): void {
        require_login();
        
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $fromDate = $_GET['from'] ?? '';
        $toDate = $_GET['to'] ?? '';
        $vetId = !empty($_GET['vet']) ? (int)$_GET['vet'] : null;
        $orderBy = $_GET['order'] ?? 'DESC'; // DESC por padrão (mais recentes primeiro)
        
        $result = AppointmentModel::paginate($fromDate, $toDate, $vetId, $limit, $offset, $orderBy);
        $appointments = $result['items'] ?? [];
        $total = $result['total'] ?? 0;
        $totalPages = ceil($total / $limit);
        
        $veterinarians = UserModel::listByRole('veterinario');
        
        render('appointments/index', [
            'appointments' => $appointments,
            'items' => $appointments, // Adicionando items para compatibilidade com a view
            'pets' => PetModel::listAllWithClient(), // Adicionando pets para o formulário
            'veterinarians' => $veterinarians,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'from' => $fromDate,
            'to' => $toDate,
            'vet' => $vetId ?? '',
            'order' => $orderBy,
            'vets' => $veterinarians,
            'filters' => [
                'from' => $fromDate,
                'to' => $toDate,
                'vet' => $vetId ?? '',
                'order' => $orderBy
            ]
        ]);
    }

    /**
     * Cria novo agendamento com validação de conflitos de horário
     * Gera notificações automáticas e suporta requisições AJAX
     */
    public function create(): void {
        require_login();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $petId = (int)($_POST['pet_id'] ?? 0);
            $vetId = (int)($_POST['vet_id'] ?? 0);
            $startTime = $_POST['start_time'] ?? '';
            $endTime = $_POST['end_time'] ?? '';
            $room = $_POST['room'] ?? '';
            $notes = $_POST['notes'] ?? '';
            
            if ($petId <= 0 || $vetId <= 0 || empty($startTime) || empty($endTime)) {
                $_SESSION['error'] = 'Todos os campos obrigatórios devem ser preenchidos.';
                header('Location: ' . APP_URL . '/agenda');
                exit;
            }
            
            // Verificar conflito de horário para o veterinário
            if (AppointmentModel::hasConflict($vetId, $room, $startTime, $endTime)) {
                // Buscar informações do veterinário para a mensagem de erro
                $pdo = DB::getConnection();
                $stmt = $pdo->prepare("SELECT name FROM users WHERE id = :vet_id");
                $stmt->execute([':vet_id' => $vetId]);
                $vetInfo = $stmt->fetch();
                $vetName = $vetInfo ? $vetInfo['name'] : 'Veterinário';
                
                $startFormatted = date('d/m/Y H:i', strtotime($startTime));
                $endFormatted = date('d/m/Y H:i', strtotime($endTime));
                
                // Retornar erro em formato JSON para ser capturado pelo JavaScript
                if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'error' => "Conflito de horário detectado! O veterinário {$vetName} já possui um agendamento no período de {$startFormatted} às {$endFormatted}."
                    ]);
                    exit;
                }
                
                $_SESSION['error'] = "Conflito de horário detectado! O veterinário {$vetName} já possui um agendamento no período de {$startFormatted} às {$endFormatted}.";
                header('Location: ' . APP_URL . '/agenda');
                exit;
            }
            
            $data = [
                'pet_id' => $petId,
                'vet_id' => $vetId,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'room' => $room,
                'status' => 'agendada',
                'notes' => $notes,
                'created_by' => $_SESSION['user_id'] ?? 1
            ];
            
            try {
                $appointmentId = AppointmentModel::create($data);
                
                // Buscar informações do pet e cliente para a notificação
                $pdo = DB::getConnection();
                $stmt = $pdo->prepare("
                    SELECT p.name as pet_name, c.name as client_name 
                    FROM pets p 
                    JOIN clients c ON c.id = p.client_id 
                    WHERE p.id = :pet_id
                ");
                $stmt->execute([':pet_id' => $petId]);
                $petInfo = $stmt->fetch();
                
                // Criar notificação para o novo agendamento
                if ($petInfo) {
                    $appointmentDate = date('d/m/Y H:i', strtotime($startTime));
                    NotificationModel::createAppointmentNotification(
                        $appointmentId,
                        $petInfo['client_name'],
                        $petInfo['pet_name'],
                        $appointmentDate
                    );
                }
                
                $_SESSION['success'] = 'Agendamento criado com sucesso!';
                
                // Retornar sucesso em formato JSON para ser capturado pelo JavaScript
                if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'message' => 'Agendamento criado com sucesso!'
                    ]);
                    exit;
                }
                
                header('Location: ' . APP_URL . '/agenda');
                exit;
            } catch (Exception $e) {
                $errorMessage = 'Erro ao criar agendamento: ' . $e->getMessage();
                $_SESSION['error'] = $errorMessage;
                
                // Retornar erro em formato JSON para ser capturado pelo JavaScript
                if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'error' => $errorMessage
                    ]);
                    exit;
                }
                
                header('Location: ' . APP_URL . '/agenda');
                exit;
            }
        }
        
        // Se não for POST, redirecionar para a página principal
        header('Location: ' . APP_URL . '/agenda');
        exit;
    }

    /**
     * Cancela agendamento existente com validações de status
     * Suporta requisições AJAX e retorna mensagens apropriadas
     */
    public function cancel(int $id = null): void {
        require_login();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Se o ID não foi passado como parâmetro, tentar pegar do POST
            if ($id === null) {
                $id = (int)($_POST['id'] ?? 0);
            }
            
            if ($id > 0) {
                // Verificar se o agendamento existe e seu status atual
                $appointment = AppointmentModel::findById($id);
                
                if (!$appointment) {
                    $message = 'Agendamento não encontrado.';
                    $_SESSION['error'] = $message;
                    
                    // Se for uma requisição AJAX, retornar JSON
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => false,
                            'error' => $message
                        ]);
                        exit;
                    }
                } elseif ($appointment['status'] === 'cancelada') {
                    $message = 'Agendamento já cancelado';
                    $_SESSION['error'] = $message;
                    
                    // Se for uma requisição AJAX, retornar JSON
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => false,
                            'error' => $message
                        ]);
                        exit;
                    }
                } elseif (AppointmentModel::cancel($id)) {
                    $message = 'Agendamento cancelado com sucesso!';
                    $_SESSION['success'] = $message;
                    
                    // Se for uma requisição AJAX, retornar JSON
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => true,
                            'message' => $message
                        ]);
                        exit;
                    }
                } else {
                    $message = 'Erro ao cancelar agendamento.';
                    $_SESSION['error'] = $message;
                    
                    // Se for uma requisição AJAX, retornar JSON
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => false,
                            'error' => $message
                        ]);
                        exit;
                    }
                }
            } else {
                $message = 'ID do agendamento inválido.';
                $_SESSION['error'] = $message;
                
                // Se for uma requisição AJAX, retornar JSON
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'error' => $message
                    ]);
                    exit;
                }
            }
        }
        
        header('Location: ' . APP_URL . '/agenda');
        exit;
    }
}
