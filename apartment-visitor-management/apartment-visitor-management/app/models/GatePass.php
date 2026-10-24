<?php

require_once __DIR__ . '/../../core/Database.php';

class GatePass
{
    // Excludes visually ambiguous characters (0/O, 1/I/L).
    private const CODE_CHARS = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    private const CODE_LENGTH = 5;

    private static function generateCode(): string
    {
        $pdo = Database::connect();
        do {
            $code = '';
            for ($i = 0; $i < self::CODE_LENGTH; $i++) {
                $code .= self::CODE_CHARS[random_int(0, strlen(self::CODE_CHARS) - 1)];
            }
            $stmt = $pdo->prepare('SELECT id FROM gate_passes WHERE code = ?');
            $stmt->execute([$code]);
        } while ($stmt->fetch());

        return $code;
    }

    public static function create(
        int $residentId,
        string $visitorName,
        ?string $visitorPhone,
        ?string $purpose,
        string $expectedStart,
        string $expectedEnd
    ): array {
        $code = self::generateCode();
        $stmt = Database::connect()->prepare(
            'INSERT INTO gate_passes (resident_id, visitor_name, visitor_phone, purpose, expected_start, expected_end, code, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, "pending")'
        );
        $stmt->execute([$residentId, $visitorName, $visitorPhone, $purpose, $expectedStart, $expectedEnd, $code]);

        return [
            'id'   => (int)Database::connect()->lastInsertId(),
            'code' => $code,
        ];
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connect()->prepare('SELECT * FROM gate_passes WHERE id = ?');
        $stmt->execute([$id]);
        $pass = $stmt->fetch();
        return $pass ?: null;
    }

    public static function findByCode(string $code): ?array
    {
        $stmt = Database::connect()->prepare(
            'SELECT gp.*, r.flat_id, f.block, f.number, u.name AS resident_name, u.status AS resident_status
             FROM gate_passes gp
             JOIN residents r ON r.id = gp.resident_id
             JOIN flats f ON f.id = r.flat_id
             JOIN users u ON u.id = r.user_id
             WHERE gp.code = ?'
        );
        $stmt->execute([strtoupper(trim($code))]);
        $pass = $stmt->fetch();
        return $pass ?: null;
    }

    public static function byResident(int $residentId): array
    {
        $stmt = Database::connect()->prepare(
            'SELECT * FROM gate_passes WHERE resident_id = ? ORDER BY expected_start DESC'
        );
        $stmt->execute([$residentId]);
        return $stmt->fetchAll();
    }

    public static function cancel(int $id, int $residentId): bool
    {
        $stmt = Database::connect()->prepare(
            'UPDATE gate_passes SET status = "cancelled" WHERE id = ? AND resident_id = ? AND status = "pending"'
        );
        $stmt->execute([$id, $residentId]);
        return $stmt->rowCount() > 0;
    }

    public static function markUsed(int $id): void
    {
        $stmt = Database::connect()->prepare('UPDATE gate_passes SET status = "used" WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function isExpired(array $pass): bool
    {
        return $pass['status'] === 'pending' && strtotime($pass['expected_end']) < time();
    }
}
