<?php

require_once __DIR__ . '/../models/Flat.php';
require_once __DIR__ . '/../models/Resident.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/VisitorLog.php';
require_once __DIR__ . '/../models/GatePass.php';

class AdminController
{
    public function dashboard(): void
    {
        Auth::requireRole('super_admin');

        $insideNow = VisitorLog::currentlyInside();
        $overstaying = array_filter($insideNow, fn($row) => VisitorLog::isOverstaying($row));
        $todayLog = VisitorLog::search(date('Y-m-d'), date('Y-m-d'), null);

        $pdo = Database::connect();
        $stats = [
            'flats'     => (int)$pdo->query('SELECT COUNT(*) AS c FROM flats')->fetch()['c'],
            'residents' => (int)$pdo->query('SELECT COUNT(*) AS c FROM residents')->fetch()['c'],
            'guards'    => (int)$pdo->query('SELECT COUNT(*) AS c FROM users WHERE role = "guard" AND status = "active"')->fetch()['c'],
            'inside'    => count($insideNow),
            'today'     => count($todayLog),
            'overstay'  => count($overstaying),
        ];

        require __DIR__ . '/../views/admin/dashboard.php';
    }

    public function flats(): void
    {
        Auth::requireRole('super_admin');
        $flats = Flat::all();
        $errors = [];
        $old = [];
        require __DIR__ . '/../views/admin/flats.php';
    }

    public function flatStore(): void
    {
        Auth::requireRole('super_admin');
        Csrf::requireValid();

        $block = strtoupper(trim($_POST['block'] ?? ''));
        $number = trim($_POST['number'] ?? '');
        $floor = trim($_POST['floor'] ?? '') === '' ? null : (int)$_POST['floor'];

        $v = new Validator();
        $v->required($_POST, 'block', 'Block')->maxLength($_POST, 'block', 10, 'Block')
          ->required($_POST, 'number', 'Flat number')->maxLength($_POST, 'number', 10, 'Flat number');

        $errors = $v->errors();
        if (empty($errors) && Flat::exists($block, $number)) {
            $errors['number'] = 'A flat with this block and number already exists.';
        }

        if (!empty($errors)) {
            $old = $_POST;
            $flats = Flat::all();
            require __DIR__ . '/../views/admin/flats.php';
            return;
        }

        Flat::create($block, $number, $floor);
        flash_set('success', "Flat $block-$number added.");
        redirect('admin/flats');
    }

    public function flatUpdate(): void
    {
        Auth::requireRole('super_admin');
        Csrf::requireValid();

        $id = (int)($_POST['id'] ?? 0);
        $block = strtoupper(trim($_POST['block'] ?? ''));
        $number = trim($_POST['number'] ?? '');
        $floor = trim($_POST['floor'] ?? '') === '' ? null : (int)$_POST['floor'];

        if ($block === '' || $number === '' || !Flat::find($id)) {
            flash_set('error', 'Invalid flat details.');
            redirect('admin/flats');
        }

        if (Flat::exists($block, $number, $id)) {
            flash_set('error', 'Another flat already uses that block and number.');
            redirect('admin/flats');
        }

        Flat::update($id, $block, $number, $floor);
        flash_set('success', 'Flat updated.');
        redirect('admin/flats');
    }

    public function flatDelete(): void
    {
        Auth::requireRole('super_admin');
        Csrf::requireValid();

        $id = (int)($_POST['id'] ?? 0);
        if (Flat::delete($id)) {
            flash_set('success', 'Flat removed.');
        } else {
            flash_set('error', 'This flat has residents or visitor history and cannot be removed.');
        }
        redirect('admin/flats');
    }

    public function residents(): void
    {
        Auth::requireRole('super_admin');
        $residents = Resident::all();
        $flats = Flat::all();
        $errors = [];
        $old = [];
        require __DIR__ . '/../views/admin/residents.php';
    }

    public function residentStore(): void
    {
        Auth::requireRole('super_admin');
        Csrf::requireValid();

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $flatId = (int)($_POST['flat_id'] ?? 0);
        $password = (string)($_POST['password'] ?? '');

        $v = new Validator();
        $v->required($_POST, 'name', 'Name')->maxLength($_POST, 'name', 100, 'Name')
          ->required($_POST, 'email', 'Email')->email($_POST, 'email', 'Email')
          ->required($_POST, 'phone', 'Phone')->phone($_POST, 'phone', 'Phone')
          ->required($_POST, 'password', 'Password')->minLength($_POST, 'password', 6, 'Password');

        $errors = $v->errors();
        if (User::emailExists($email)) {
            $errors['email'] = 'This email is already registered.';
        }
        if (!Flat::find($flatId)) {
            $errors['flat_id'] = 'Please choose a valid flat.';
        }

        if (!empty($errors)) {
            $old = $_POST;
            $residents = Resident::all();
            $flats = Flat::all();
            require __DIR__ . '/../views/admin/residents.php';
            return;
        }

        $userId = User::create($name, $email, $password, 'resident');
        Resident::create($userId, $flatId, $phone);

        flash_set('success', "Resident account created for $name.");
        redirect('admin/residents');
    }

    public function residentStatus(): void
    {
        Auth::requireRole('super_admin');
        Csrf::requireValid();

        $userId = (int)($_POST['user_id'] ?? 0);
        $status = $_POST['status'] === 'active' ? 'active' : 'inactive';

        if (User::setStatus($userId, $status, 'resident')) {
            flash_set('success', 'Resident status updated.');
        } else {
            flash_set('error', 'Resident not found.');
        }
        redirect('admin/residents');
    }

    public function guards(): void
    {
        Auth::requireRole('super_admin');
        $guards = User::allByRole('guard');
        $errors = [];
        $old = [];
        require __DIR__ . '/../views/admin/guards.php';
    }

    public function guardStore(): void
    {
        Auth::requireRole('super_admin');
        Csrf::requireValid();

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        $v = new Validator();
        $v->required($_POST, 'name', 'Name')->maxLength($_POST, 'name', 100, 'Name')
          ->required($_POST, 'email', 'Email')->email($_POST, 'email', 'Email')
          ->required($_POST, 'password', 'Password')->minLength($_POST, 'password', 6, 'Password');

        $errors = $v->errors();
        if (User::emailExists($email)) {
            $errors['email'] = 'This email is already registered.';
        }

        if (!empty($errors)) {
            $old = $_POST;
            $guards = User::allByRole('guard');
            require __DIR__ . '/../views/admin/guards.php';
            return;
        }

        User::create($name, $email, $password, 'guard');
        flash_set('success', "Guard account created for $name.");
        redirect('admin/guards');
    }

    public function guardStatus(): void
    {
        Auth::requireRole('super_admin');
        Csrf::requireValid();

        $userId = (int)($_POST['user_id'] ?? 0);
        $status = $_POST['status'] === 'active' ? 'active' : 'inactive';

        if (User::setStatus($userId, $status, 'guard')) {
            flash_set('success', 'Guard status updated.');
        } else {
            flash_set('error', 'Guard not found.');
        }
        redirect('admin/guards');
    }

    public function visitorLog(): void
    {
        Auth::requireRole('super_admin');

        $dateFrom = trim($_GET['date_from'] ?? '');
        $dateTo = trim($_GET['date_to'] ?? '');
        $flatId = (int)($_GET['flat_id'] ?? 0) ?: null;

        $logs = VisitorLog::search($dateFrom ?: null, $dateTo ?: null, $flatId);
        $flats = Flat::all();
        $frequentVisitors = VisitorLog::frequentVisitors();

        require __DIR__ . '/../views/admin/log.php';
    }
}
