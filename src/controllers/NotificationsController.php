<?php
require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../middlewares/auth.php';

class NotificationsController {
    /**
     * Retorna notificações não lidas do usuário autenticado em JSON
     * Inclui contagem de não lidas e trata erros com HTTP 500
     */
    public function unread(): void {
        require_login();
        
        header('Content-Type: application/json');
        
        try {
            $userId = $_SESSION['user']['id'];
            $notifications = NotificationModel::getUnreadByUserId($userId);
            $count = NotificationModel::countUnreadByUserId($userId);
            
            echo json_encode([
                'success' => true,
                'notifications' => $notifications,
                'count' => $count
            ]);
        } catch (Exception $e) {
            error_log('Erro ao carregar notificações: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ]);
        }
    }
    
    /**
     * Marca uma notificação como lida via POST com payload JSON
     * Valida método e dados; retorna JSON com sucesso ou mensagem de erro
     */
    public function markRead(): void {
        require_login();
        
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $notificationId = $input['id'] ?? null;
        
        if (!$notificationId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID da notificação é obrigatório']);
            return;
        }
        
        $userId = $_SESSION['user']['id'];
        $success = NotificationModel::markAsRead($notificationId, $userId);
        
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Notificação marcada como lida' : 'Erro ao marcar notificação'
        ]);
    }
    
    /**
     * Marca todas as notificações do usuário como lidas
     * Exige POST; retorna JSON com quantidade afetada
     */
    public function markAllRead(): void {
        require_login();
        
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
            return;
        }
        
        $userId = $_SESSION['user']['id'];
        $count = NotificationModel::markAllAsRead($userId);
        
        echo json_encode([
            'success' => true,
            'count' => $count,
            'message' => "Todas as notificações foram marcadas como lidas"
        ]);
    }
}