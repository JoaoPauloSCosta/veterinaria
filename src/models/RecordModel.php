<?php
require_once __DIR__ . '/../helpers/db.php';

class RecordModel {
    public static function listByPet(int $petId): array {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('SELECT r.*, u.name AS vet_name FROM records r JOIN users u ON u.id=r.vet_id WHERE r.pet_id = :pid ORDER BY r.record_date DESC');
        $stmt->execute([':pid'=>$petId]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare('INSERT INTO records (pet_id, appointment_id, vet_id, record_date, anamnesis, diagnosis, treatment, prescription) VALUES (:pet_id,:appointment_id,:vet_id,:record_date,:anamnesis,:diagnosis,:treatment,:prescription)');
        $stmt->execute([
            ':pet_id'=>$data['pet_id'], ':appointment_id'=>$data['appointment_id'] ?: null, ':vet_id'=>$data['vet_id'], ':record_date'=>$data['record_date'], ':anamnesis'=>$data['anamnesis'], ':diagnosis'=>$data['diagnosis'], ':treatment'=>$data['treatment'], ':prescription'=>$data['prescription']
        ]);
        return (int)$pdo->lastInsertId();
    }
}
