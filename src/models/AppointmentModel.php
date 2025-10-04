<?php
require_once __DIR__ . '/../helpers/db.php';

class AppointmentModel {
    /**
     * Lista agendamentos com filtros opcionais por data e veterinário
     * Retorna array com dados do agendamento, pet e veterinário
     */
    public static function list(string $fromDate = '', string $toDate = '', ?int $vetId = null): array {
        $pdo = DB::getConnection();
        $where = [];
        $params = [];
        if ($fromDate !== '') { $where[] = 'start_time >= :from'; $params[':from'] = $fromDate.' 00:00:00'; }
        if ($toDate !== '') { $where[] = 'end_time <= :to'; $params[':to'] = $toDate.' 23:59:59'; }
        if ($vetId) { $where[] = 'vet_id = :vet'; $params[':vet'] = $vetId; }
        $w = $where ? ('WHERE '.implode(' AND ', $where)) : '';
        $sql = "SELECT a.*, p.name AS pet_name, u.name AS vet_name FROM appointments a JOIN pets p ON p.id=a.pet_id JOIN users u ON u.id=a.vet_id $w ORDER BY a.start_time";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Verifica se existe conflito de horário para veterinário ou sala
     * Considera agendamentos ativos (agendada/confirmada) no mesmo período
     */
    public static function hasConflict(int $vetId, string $room, string $start, string $end, ?int $ignoreId = null): bool {
        $pdo = DB::getConnection();
        $sql = "SELECT COUNT(*) FROM appointments WHERE status IN ('agendada','confirmada') AND ((start_time < :end AND end_time > :start)) AND (vet_id = :vet OR (room IS NOT NULL AND room = :room))";
        $params = [':start'=>$start, ':end'=>$end, ':vet'=>$vetId, ':room'=>$room];
        if ($ignoreId) { $sql .= ' AND id <> :id'; $params[':id'] = $ignoreId; }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Cria novo agendamento com os dados fornecidos
     * Retorna o ID do agendamento criado
     */
    public static function create(array $data): int {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('INSERT INTO appointments (pet_id, vet_id, start_time, end_time, room, status, notes, created_by) VALUES (:pet_id,:vet_id,:start_time,:end_time,:room,:status,:notes,:created_by)');
        $stmt->execute([
            ':pet_id'=>$data['pet_id'], ':vet_id'=>$data['vet_id'], ':start_time'=>$data['start_time'], ':end_time'=>$data['end_time'], ':room'=>$data['room'], ':status'=>$data['status'], ':notes'=>$data['notes'], ':created_by'=>$data['created_by']
        ]);
        return (int)$pdo->lastInsertId();
    }

    /**
     * Atualiza horários de início e fim de um agendamento
     * Opcionalmente atualiza a sala se fornecida
     */
    public static function updateTimes(int $id, string $start, string $end, ?string $room = null): bool {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('UPDATE appointments SET start_time=:start, end_time=:end, room=COALESCE(:room, room) WHERE id=:id');
        return $stmt->execute([':start'=>$start, ':end'=>$end, ':room'=>$room, ':id'=>$id]);
    }

    /**
     * Busca agendamento por ID
     * Retorna array com dados do agendamento ou null se não encontrado
     */
    public static function findById(int $id): ?array {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM appointments WHERE id=:id");
        $stmt->execute([':id'=>$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Cancela agendamento alterando status para 'cancelada'
     * Retorna true se operação foi bem-sucedida
     */
    public static function cancel(int $id): bool {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("UPDATE appointments SET status='cancelada' WHERE id=:id");
        return $stmt->execute([':id'=>$id]);
    }

    /**
     * Lista agendamentos com paginação e filtros opcionais
     * Retorna array com items paginados e total de registros
     */
    public static function paginate(string $fromDate = '', string $toDate = '', ?int $vetId = null, int $limit = 10, int $offset = 0, string $orderBy = 'DESC'): array {
        $pdo = DB::getConnection();
        $where = [];
        $params = [];
        if ($fromDate !== '') { $where[] = 'start_time >= :from'; $params[':from'] = $fromDate.' 00:00:00'; }
        if ($toDate !== '') { $where[] = 'end_time <= :to'; $params[':to'] = $toDate.' 23:59:59'; }
        if ($vetId) { $where[] = 'vet_id = :vet'; $params[':vet'] = $vetId; }
        $w = $where ? ('WHERE '.implode(' AND ', $where)) : '';
        
        // Validar ordenação (apenas DESC ou ASC)
        $orderBy = strtoupper($orderBy) === 'ASC' ? 'ASC' : 'DESC';
        
        // Query com paginação
        $sql = "SELECT SQL_CALC_FOUND_ROWS a.*, p.name AS pet_name, u.name AS vet_name 
                FROM appointments a 
                JOIN pets p ON p.id=a.pet_id 
                JOIN users u ON u.id=a.vet_id 
                $w 
                ORDER BY a.start_time $orderBy 
                LIMIT :limit OFFSET :offset";
        
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
        
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            if (in_array($key, [':limit', ':offset'])) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        $stmt->execute();
        $items = $stmt->fetchAll();
        
        // Total de registros
        $totalStmt = $pdo->query('SELECT FOUND_ROWS()');
        $total = (int)$totalStmt->fetchColumn();
        
        return ['items' => $items, 'total' => $total];
    }
}
