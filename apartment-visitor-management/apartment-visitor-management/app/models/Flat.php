<?php

require_once __DIR__ . '/../../core/Database.php';

class Flat
{
    public static function all(): array
    {
        $stmt = Database::connect()->query(
            'SELECT f.*,
                    (SELECT COUNT(*) FROM residents r WHERE r.flat_id = f.id) AS resident_count
             FROM flats f
             ORDER BY f.block, f.number'
        );
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connect()->prepare('SELECT * FROM flats WHERE id = ?');
        $stmt->execute([$id]);
        $flat = $stmt->fetch();
        return $flat ?: null;
    }

    public static function exists(string $block, string $number, ?int $excludeId = null): bool
    {
        $sql = 'SELECT id FROM flats WHERE block = ? AND number = ?';
        $params = [$block, $number];
        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $stmt = Database::connect()->prepare($sql);
        $stmt->execute($params);
        return (bool)$stmt->fetch();
    }

    public static function create(string $block, string $number, ?int $floor): int
    {
        $stmt = Database::connect()->prepare(
            'INSERT INTO flats (block, number, floor) VALUES (?, ?, ?)'
        );
        $stmt->execute([$block, $number, $floor]);
        return (int)Database::connect()->lastInsertId();
    }

    public static function update(int $id, string $block, string $number, ?int $floor): void
    {
        $stmt = Database::connect()->prepare(
            'UPDATE flats SET block = ?, number = ?, floor = ? WHERE id = ?'
        );
        $stmt->execute([$block, $number, $floor, $id]);
    }

    public static function residentCount(int $id): int
    {
        $stmt = Database::connect()->prepare('SELECT COUNT(*) AS c FROM residents WHERE flat_id = ?');
        $stmt->execute([$id]);
        return (int)$stmt->fetch()['c'];
    }

    public static function delete(int $id): bool
    {
        if (self::residentCount($id) > 0) {
            return false;
        }
        try {
            $stmt = Database::connect()->prepare('DELETE FROM flats WHERE id = ?');
            $stmt->execute([$id]);
            return true;
        } catch (PDOException $e) {
            // Flat still referenced by visitor_logs history -- keep the record intact.
            return false;
        }
    }
}
