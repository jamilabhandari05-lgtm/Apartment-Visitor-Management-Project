<?php

require_once __DIR__ . '/../models/Flat.php';
require_once __DIR__ . '/../models/GatePass.php';
require_once __DIR__ . '/../models/VisitorLog.php';

class GuardController
{
    public function dashboard(): void
    {
        Auth::requireRole('guard');

        $insideNow = VisitorLog::currentlyInside();
        $overstaying = array_filter($insideNow, fn($row) => VisitorLog::isOverstaying($row));

        require __DIR__ . '/../views/guard/dashboard.php';
    }

    public function checkInForm(): void
    {
        Auth::requireRole('guard');
        $flats = Flat::all();
        $errors = [];
        $old = [];
        $passPreview = null;
        require __DIR__ . '/../views/guard/checkin.php';
    }

    public function lookupCode(): void
    {
        Auth::requireRole('guard');
        Csrf::requireValid();

        $code = strtoupper(trim($_POST['code'] ?? ''));
        $flats = Flat::all();
        $errors = [];
        $old = $_POST;
        $passPreview = GatePass::findByCode($code);

        if (!$passPreview) {
            $errors['code'] = 'No gate pass found for that code.';
        }

        require __DIR__ . '/../views/guard/checkin.php';
    }

    public function checkInByCode(): void
    {
        Auth::requireRole('guard');
        Csrf::requireValid();

        $code = strtoupper(trim($_POST['code'] ?? ''));
        $result = VisitorLog::checkInByCode($code, Auth::id());

        if (!$result['ok']) {
            flash_set('error', $result['error']);
        } else {
            flash_set('success', 'Visitor checked in.');
        }

        redirect('guard/checkin');
    }

    public function checkInWalkIn(): void
    {
        Auth::requireRole('guard');
        Csrf::requireValid();

        $flatId = (int)($_POST['flat_id'] ?? 0);
        $visitorName = trim($_POST['visitor_name'] ?? '');
        $visitorPhone = trim($_POST['visitor_phone'] ?? '') ?: null;
        $purpose = trim($_POST['purpose'] ?? '') ?: null;
        $photoNote = trim($_POST['photo_note'] ?? '') ?: null;

        $v = new Validator();
        $v->required($_POST, 'flat_id', 'Flat')
          ->required($_POST, 'visitor_name', 'Visitor name')->maxLength($_POST, 'visitor_name', 100, 'Visitor name');

        $errors = $v->errors();
        if (!Flat::find($flatId)) {
            $errors['flat_id'] = 'Please choose a valid flat.';
        }

        if (!empty($errors)) {
            $old = $_POST;
            $flats = Flat::all();
            $passPreview = null;
            require __DIR__ . '/../views/guard/checkin.php';
            return;
        }

        VisitorLog::checkInWalkIn($flatId, $visitorName, $visitorPhone, $purpose, $photoNote, Auth::id());
        flash_set('success', "Walk-in visitor $visitorName checked in.");
        redirect('guard/checkin');
    }

    public function checkOut(): void
    {
        Auth::requireRole('guard');
        Csrf::requireValid();

        $logId = (int)($_POST['id'] ?? 0);
        if (VisitorLog::checkOut($logId, Auth::id())) {
            flash_set('success', 'Visitor checked out.');
        } else {
            flash_set('error', 'That visitor is already checked out.');
        }

        redirect('guard/dashboard');
    }
}
