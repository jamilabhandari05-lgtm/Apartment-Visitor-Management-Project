<?php

require_once __DIR__ . '/../models/User.php';

class AuthController
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            redirect(Auth::dashboardRoute(Auth::role()));
        }
        $errors = [];
        $old = [];
        require __DIR__ . '/../views/auth/login.php';
    }

    public function login(): void
    {
        Csrf::requireValid();

        $email = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        $v = new Validator();
        $v->required($_POST, 'email', 'Email')->email($_POST, 'email', 'Email')
          ->required($_POST, 'password', 'Password');

        if ($v->fails()) {
            $errors = $v->errors();
            $old = $_POST;
            require __DIR__ . '/../views/auth/login.php';
            return;
        }

        if (!Auth::attempt($email, $password)) {
            $errors = ['login' => 'Invalid email or password, or the account is inactive.'];
            $old = $_POST;
            require __DIR__ . '/../views/auth/login.php';
            return;
        }

        redirect(Auth::dashboardRoute(Auth::role()));
    }

    public function logout(): void
    {
        Csrf::requireValid();
        Auth::logout();
        redirect('login');
    }
}
