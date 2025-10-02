<?php
require_once __DIR__ . '/../helpers/db.php';

class AppointmentModel {
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

    public static function hasConflict(int $vetId, string $room, string $start, string $end, ?int $ignoreId = null): bool {
        $pdo = DB::getConnection();
        $sql = "SELECT COUNT(*) FROM appointments WHERE status IN ('agendada','confirmada') AND ((start_time < :end AND end_time > :start)) AND (vet_id = :vet OR (room IS NOT NULL AND room = :room))";
        $params = [':start'=>$start, ':end'=>$end, ':vet'=>$vetId, ':room'=>$room];
        if ($ignoreId) { $sql .= ' AND id <> :id'; $params[':id'] = $ignoreId; }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    public static function create(array $data): int {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('INSERT INTO appointments (pet_id, vet_id, start_time, end_time, room, status, notes, created_by) VALUES (:pet_id,:vet_id,:start_time,:end_time,:room,:status,:notes,:created_by)');
        $stmt->execute([
            ':pet_id'=>$data['pet_id'], ':vet_id'=>$data['vet_id'], ':start_time'=>$data['start_time'], ':end_time'=>$data['end_time'], ':room'=>$data['room'], ':status'=>$data['status'], ':notes'=>$data['notes'], ':created_by'=>$data['created_by']
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function updateTimes(int $id, string $start, string $end, ?string $room = null): bool {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('UPDATE appointments SET start_time=:start, end_time=:end, room=COALESCE(:room, room) WHERE id=:id');
        return $stmt->execute([':start'=>$start, ':end'=>$end, ':room'=>$room, ':id'=>$id]);
    }

    public static function cancel(int $id): bool {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("UPDATE appointments SET status='cancelada' WHERE id=:id");
        return $stmt->execute([':id'=>$id]);
    }
}
