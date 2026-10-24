<?php

require_once __DIR__ . '/../../core/Database.php';

class User
{
    public static function find(int $id): ?array
    {
        $stmt = Database::connect()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::connect()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = 'SELECT id FROM users WHERE email = ?';
        $params = [$email];
        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $stmt = Database::connect()->prepare($sql);
        $stmt->execute($params);
        return (bool)$stmt->fetch();
    }

    public static function create(string $name, string $email, string $password, string $role): int
    {
        $stmt = Database::connect()->prepare(
            'INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, "active")'
        );
        $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
        return (int)Database::connect()->lastInsertId();
    }

    public static function allByRole(string $role): array
    {
        $stmt = Database::connect()->prepare('SELECT * FROM users WHERE role = ? ORDER BY name');
        $stmt->execute([$role]);
        return $stmt->fetchAll();
    }

    /**
     * Updates status, scoped to the expected role when one is given so a status
     * toggle on one admin screen (e.g. Residents) can't be pointed at a user_id
     * belonging to a different role (e.g. a guard or another admin).
     *
     * @return bool True if a matching row was updated.
     */
    public static function setStatus(int $id, string $status, ?string $role = null): bool
    {
        $sql = 'UPDATE users SET status = ? WHERE id = ?';
        $params = [$status, $id];
        if ($role !== null) {
            $sql .= ' AND role = ?';
            $params[] = $role;
        }
        $stmt = Database::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }
}
