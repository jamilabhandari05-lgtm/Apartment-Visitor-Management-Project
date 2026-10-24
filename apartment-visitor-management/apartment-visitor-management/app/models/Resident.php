<?php

require_once __DIR__ . '/../../core/Database.php';

class Resident
{
    public static function create(int $userId, int $flatId, string $phone): int
    {
        $stmt = Database::connect()->prepare(
            'INSERT INTO residents (user_id, flat_id, phone) VALUES (?, ?, ?)'
        );
        $stmt->execute([$userId, $flatId, $phone]);
        return (int)Database::connect()->lastInsertId();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connect()->prepare(
            'SELECT r.*, u.name, u.email, u.status
             FROM residents r JOIN users u ON u.id = r.user_id
             WHERE r.id = ?'
        );
        $stmt->execute([$id]);
        $resident = $stmt->fetch();
        return $resident ?: null;
    }

    public static function findByUserId(int $userId): ?array
    {
        $stmt = Database::connect()->prepare('SELECT * FROM residents WHERE user_id = ?');
        $stmt->execute([$userId]);
        $resident = $stmt->fetch();
        return $resident ?: null;
    }

    public static function all(): array
    {
        $stmt = Database::connect()->query(
            'SELECT r.id, r.phone, r.created_at, u.id AS user_id, u.name, u.email, u.status,
                    f.id AS flat_id, f.block, f.number
             FROM residents r
             JOIN users u ON u.id = r.user_id
             JOIN flats f ON f.id = r.flat_id
             ORDER BY f.block, f.number'
        );
        return $stmt->fetchAll();
    }
}
