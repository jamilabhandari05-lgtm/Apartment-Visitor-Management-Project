<?php

require_once __DIR__ . '/Database.php';

class Auth
{
    public static function attempt(string $email, string $password): bool
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND status = "active" LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];

        // Residents act on their own resident_id/flat_id everywhere; resolve once at login.
        if ($user['role'] === 'resident') {
            $stmt = $pdo->prepare('SELECT id, flat_id FROM residents WHERE user_id = ? LIMIT 1');
            $stmt->execute([$user['id']]);
            $resident = $stmt->fetch();
            $_SESSION['resident_id'] = $resident['id'] ?? null;
            $_SESSION['flat_id'] = $resident['flat_id'] ?? null;
        }

        return true;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function check(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function role(): ?string
    {
        return $_SESSION['user_role'] ?? null;
    }

    public static function name(): ?string
    {
        return $_SESSION['user_name'] ?? null;
    }

    public static function residentId(): ?int
    {
        return $_SESSION['resident_id'] ?? null;
    }

    public static function flatId(): ?int
    {
        return $_SESSION['flat_id'] ?? null;
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            redirect('login');
        }
    }

    public static function requireRole(string ...$roles): void
    {
        self::requireLogin();
        if (!in_array(self::role(), $roles, true)) {
            http_response_code(403);
            require __DIR__ . '/../app/views/errors/404.php';
            exit;
        }
    }

    public static function dashboardRoute(string $role): string
    {
        return match ($role) {
            'super_admin' => 'admin/dashboard',
            'guard'       => 'guard/dashboard',
            'resident'    => 'resident/dashboard',
            default       => 'login',
        };
    }
}
