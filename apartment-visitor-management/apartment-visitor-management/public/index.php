<?php

declare(strict_types=1);

// Let PHP's built-in dev server (`php -S`) serve real static files directly
// instead of routing every asset request through the front controller.
if (PHP_SAPI === 'cli-server') {
    $requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $assetPath = __DIR__ . $requestPath;
    if (!str_ends_with($requestPath, '.php') && is_file($assetPath)) {
        return false;
    }
}

date_default_timezone_set('Asia/Kolkata');
ini_set('display_errors', '1');
error_reporting(E_ALL);

session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
]);

require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Csrf.php';
require_once __DIR__ . '/../core/Validator.php';
require_once __DIR__ . '/../core/Router.php';

require_once __DIR__ . '/../app/models/VisitorLog.php';
require_once __DIR__ . '/../app/models/GatePass.php';

require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/AdminController.php';
require_once __DIR__ . '/../app/controllers/GuardController.php';
require_once __DIR__ . '/../app/controllers/ResidentController.php';

$router = new Router();

// ---- Auth ----
$router->get('login', fn() => (new AuthController())->showLogin());
$router->post('login', fn() => (new AuthController())->login());
$router->post('logout', fn() => (new AuthController())->logout());

// ---- Super Admin ----
$router->get('admin/dashboard', fn() => (new AdminController())->dashboard());
$router->get('admin/flats', fn() => (new AdminController())->flats());
$router->post('admin/flats/store', fn() => (new AdminController())->flatStore());
$router->post('admin/flats/update', fn() => (new AdminController())->flatUpdate());
$router->post('admin/flats/delete', fn() => (new AdminController())->flatDelete());
$router->get('admin/residents', fn() => (new AdminController())->residents());
$router->post('admin/residents/store', fn() => (new AdminController())->residentStore());
$router->post('admin/residents/status', fn() => (new AdminController())->residentStatus());
$router->get('admin/guards', fn() => (new AdminController())->guards());
$router->post('admin/guards/store', fn() => (new AdminController())->guardStore());
$router->post('admin/guards/status', fn() => (new AdminController())->guardStatus());
$router->get('admin/visitor-log', fn() => (new AdminController())->visitorLog());

// ---- Security Guard ----
$router->get('guard/dashboard', fn() => (new GuardController())->dashboard());
$router->get('guard/checkin', fn() => (new GuardController())->checkInForm());
$router->post('guard/checkin/lookup', fn() => (new GuardController())->lookupCode());
$router->post('guard/checkin/code', fn() => (new GuardController())->checkInByCode());
$router->post('guard/checkin/walkin', fn() => (new GuardController())->checkInWalkIn());
$router->post('guard/checkout', fn() => (new GuardController())->checkOut());

// ---- Resident ----
$router->get('resident/dashboard', fn() => (new ResidentController())->dashboard());
$router->get('resident/preapprove', fn() => (new ResidentController())->preapproveForm());
$router->post('resident/preapprove/store', fn() => (new ResidentController())->preapproveStore());
$router->post('resident/preapprove/cancel', fn() => (new ResidentController())->preapproveCancel());
$router->get('resident/history', fn() => (new ResidentController())->history());

$route = $_GET['route'] ?? '';

if ($route === '') {
    if (Auth::check()) {
        redirect(Auth::dashboardRoute(Auth::role()));
    }
    redirect('login');
}

$router->dispatch($_SERVER['REQUEST_METHOD'], $route);
