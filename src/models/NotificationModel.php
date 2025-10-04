<?php
require_once __DIR__ . '/../helpers/db.php';

class NotificationModel {
    
    /**
     * Cria nova notificação para um usuário específico
     * Retorna o ID da notificação criada
     */
    public static function create(int $userId, string $title, string $message, string $type = 'system'): int {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, title, message, type, is_read, created_at) 
            VALUES (:user_id, :title, :message, :type, 0, NOW())
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':title' => $title,
            ':message' => $message,
            ':type' => $type
        ]);
        return (int)$pdo->lastInsertId();
    }
    
    /**
     * Busca notificações não lidas de um usuário com limite opcional
     * Retorna array ordenado por data de criação decrescente
     */
    public static function getUnreadByUserId(int $userId, int $limit = 10): array {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("
            SELECT id, title, message, type, created_at 
            FROM notifications 
            WHERE user_id = :user_id AND is_read = 0 
            ORDER BY created_at DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Conta total de notificações não lidas de um usuário
     * Retorna número inteiro com a quantidade
     */
    public static function countUnreadByUserId(int $userId): int {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM notifications 
            WHERE user_id = :user_id AND is_read = 0
        ");
        $stmt->execute([':user_id' => $userId]);
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * Marca notificação específica como lida para um usuário
     * Retorna true se a operação foi bem-sucedida
     */
    public static function markAsRead(int $notificationId, int $userId): bool {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("
            UPDATE notifications 
            SET is_read = 1 
            WHERE id = :id AND user_id = :user_id
        ");
        return $stmt->execute([
            ':id' => $notificationId,
            ':user_id' => $userId
        ]);
    }
    
    /**
     * Marca todas as notificações não lidas de um usuário como lidas
     * Retorna true se a operação foi bem-sucedida
     */
    public static function markAllAsRead(int $userId): bool {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("
            UPDATE notifications 
            SET is_read = 1 
            WHERE user_id = :user_id AND is_read = 0
        ");
        return $stmt->execute([':user_id' => $userId]);
    }
    
    /**
     * Cria notificações automáticas para nova consulta agendada
     * Envia para usuários admin e recepção ativos no sistema
     */
    public static function createAppointmentNotification(int $appointmentId, string $clientName, string $petName, string $appointmentDate): void {
        $pdo = DB::getConnection();
        
        // Buscar usuários que devem receber notificação (admin e recepção)
        $stmt = $pdo->prepare("
            SELECT id FROM users 
            WHERE role IN ('admin', 'recepcao') AND is_active = 1
        ");
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        $title = "Nova Consulta Agendada";
        $message = "Nova consulta agendada para {$petName} (cliente: {$clientName}) em {$appointmentDate}";
        
        foreach ($users as $user) {
            self::create($user['id'], $title, $message, 'appointment');
        }
    }
    
    /**
     * Busca notificações não lidas por tipo de usuário (role)
     * Retorna array com dados da notificação e nome do usuário
     */
    public static function getNotificationsByRole(string $role, int $limit = 10): array {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("
            SELECT n.id, n.title, n.message, n.type, n.created_at, u.name as user_name
            FROM notifications n
            JOIN users u ON n.user_id = u.id
            WHERE u.role = :role AND n.is_read = 0
            ORDER BY n.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':role', $role, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}