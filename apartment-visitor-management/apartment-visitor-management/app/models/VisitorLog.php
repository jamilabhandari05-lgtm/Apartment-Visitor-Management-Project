<?php

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/GatePass.php';

class VisitorLog
{
    // A walk-in with no pre-approval window is flagged overstaying past this many hours inside.
    public const WALKIN_MAX_STAY_HOURS = 4;

    /**
     * @return array{ok:bool, error?:string, id?:int}
     */
    public static function checkInByCode(string $code, int $guardUserId): array
    {
        $pass = GatePass::findByCode($code);
        if (!$pass) {
            return ['ok' => false, 'error' => 'No gate pass found for that code.'];
        }
        if ($pass['status'] === 'used') {
            return ['ok' => false, 'error' => 'This gate pass has already been used to check in a visitor.'];
        }
        if ($pass['status'] === 'cancelled') {
            return ['ok' => false, 'error' => 'This gate pass was cancelled by the resident.'];
        }
        if (($pass['resident_status'] ?? 'active') !== 'active') {
            return ['ok' => false, 'error' => 'The resident who issued this gate pass has been deactivated. This visitor cannot be checked in.'];
        }
        if (GatePass::isExpired($pass)) {
            return ['ok' => false, 'error' => 'This gate pass has expired. Log the visitor as a walk-in instead.'];
        }

        $pdo = Database::connect();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO visitor_logs (gate_pass_id, flat_id, visitor_name, visitor_phone, purpose, checked_in_at, checked_in_by)
                 VALUES (?, ?, ?, ?, ?, NOW(), ?)'
            );
            $stmt->execute([
                $pass['id'], $pass['flat_id'], $pass['visitor_name'], $pass['visitor_phone'], $pass['purpose'], $guardUserId,
            ]);
            $logId = (int)$pdo->lastInsertId();

            $stmt = $pdo->prepare('UPDATE gate_passes SET status = "used" WHERE id = ? AND status = "pending"');
            $stmt->execute([$pass['id']]);
            if ($stmt->rowCount() === 0) {
                // Raced with another guard checking the same code in concurrently.
                $pdo->rollBack();
                return ['ok' => false, 'error' => 'This gate pass was just used by another entry. Refresh and try again.'];
            }

            $pdo->commit();
            return ['ok' => true, 'id' => $logId];
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function checkInWalkIn(
        int $flatId,
        string $visitorName,
        ?string $visitorPhone,
        ?string $purpose,
        ?string $photoNote,
        int $guardUserId
    ): int {
        $stmt = Database::connect()->prepare(
            'INSERT INTO visitor_logs (flat_id, visitor_name, visitor_phone, purpose, photo_note, checked_in_at, checked_in_by)
             VALUES (?, ?, ?, ?, ?, NOW(), ?)'
        );
        $stmt->execute([$flatId, $visitorName, $visitorPhone, $purpose, $photoNote, $guardUserId]);
        return (int)Database::connect()->lastInsertId();
    }

    public static function checkOut(int $logId, int $guardUserId): bool
    {
        $stmt = Database::connect()->prepare(
            'UPDATE visitor_logs SET checked_out_at = NOW(), checked_out_by = ?
             WHERE id = ? AND checked_out_at IS NULL'
        );
        $stmt->execute([$guardUserId, $logId]);
        return $stmt->rowCount() > 0;
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connect()->prepare(self::baseSelect() . ' WHERE vl.id = ?');
        $stmt->execute([$id]);
        $log = $stmt->fetch();
        return $log ?: null;
    }

    public static function currentlyInside(): array
    {
        $stmt = Database::connect()->query(
            self::baseSelect() . ' WHERE vl.checked_out_at IS NULL ORDER BY vl.checked_in_at DESC'
        );
        return $stmt->fetchAll();
    }

    public static function byFlat(int $flatId): array
    {
        $stmt = Database::connect()->prepare(
            self::baseSelect() . ' WHERE vl.flat_id = ? ORDER BY vl.checked_in_at DESC'
        );
        $stmt->execute([$flatId]);
        return $stmt->fetchAll();
    }

    public static function search(?string $dateFrom, ?string $dateTo, ?int $flatId): array
    {
        $sql = self::baseSelect() . ' WHERE 1=1';
        $params = [];

        if ($dateFrom) {
            $sql .= ' AND vl.checked_in_at >= ?';
            $params[] = $dateFrom . ' 00:00:00';
        }
        if ($dateTo) {
            $sql .= ' AND vl.checked_in_at <= ?';
            $params[] = $dateTo . ' 23:59:59';
        }
        if ($flatId) {
            $sql .= ' AND vl.flat_id = ?';
            $params[] = $flatId;
        }
        $sql .= ' ORDER BY vl.checked_in_at DESC LIMIT 500';

        $stmt = Database::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function frequentVisitors(int $limit = 10): array
    {
        $stmt = Database::connect()->prepare(
            'SELECT visitor_name, visitor_phone, COUNT(*) AS visit_count, MAX(checked_in_at) AS last_visit
             FROM visitor_logs
             WHERE visitor_phone IS NOT NULL AND visitor_phone != ""
             GROUP BY visitor_name, visitor_phone
             HAVING COUNT(*) > 1
             ORDER BY visit_count DESC, last_visit DESC
             LIMIT ?'
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * The moment past which a still-checked-in visitor counts as overstaying:
     * their gate pass's approved window end, or (for a walk-in) check-in time
     * plus the default max stay. Computed on read, never stored.
     */
    public static function overstayThreshold(array $row): int
    {
        if (!empty($row['gate_pass_id']) && !empty($row['expected_end'])) {
            return strtotime($row['expected_end']);
        }
        return strtotime($row['checked_in_at']) + self::WALKIN_MAX_STAY_HOURS * 3600;
    }

    public static function isOverstaying(array $row): bool
    {
        return empty($row['checked_out_at']) && time() > self::overstayThreshold($row);
    }

    private static function baseSelect(): string
    {
        return 'SELECT vl.*, f.block, f.number, gp.expected_end, gp.expected_start,
                        cib.name AS checked_in_by_name, cob.name AS checked_out_by_name
                 FROM visitor_logs vl
                 JOIN flats f ON f.id = vl.flat_id
                 LEFT JOIN gate_passes gp ON gp.id = vl.gate_pass_id
                 JOIN users cib ON cib.id = vl.checked_in_by
                 LEFT JOIN users cob ON cob.id = vl.checked_out_by';
    }
}
