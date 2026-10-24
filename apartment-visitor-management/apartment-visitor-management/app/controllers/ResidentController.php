<?php

require_once __DIR__ . '/../models/GatePass.php';
require_once __DIR__ . '/../models/VisitorLog.php';

class ResidentController
{
    public function dashboard(): void
    {
        Auth::requireRole('resident');

        $passes = GatePass::byResident(Auth::residentId());
        $upcoming = array_filter($passes, fn($p) => $p['status'] === 'pending' && !GatePass::isExpired($p));
        $visiting = array_filter(VisitorLog::byFlat(Auth::flatId()), fn($row) => empty($row['checked_out_at']));

        require __DIR__ . '/../views/resident/dashboard.php';
    }

    public function preapproveForm(): void
    {
        Auth::requireRole('resident');
        $errors = [];
        $old = [];
        $newCode = null;
        require __DIR__ . '/../views/resident/preapprove.php';
    }

    public function preapproveStore(): void
    {
        Auth::requireRole('resident');
        Csrf::requireValid();

        $visitorName = trim($_POST['visitor_name'] ?? '');
        $visitorPhone = trim($_POST['visitor_phone'] ?? '') ?: null;
        $purpose = trim($_POST['purpose'] ?? '') ?: null;
        $expectedStart = str_replace('T', ' ', trim($_POST['expected_start'] ?? ''));
        $expectedEnd = str_replace('T', ' ', trim($_POST['expected_end'] ?? ''));

        $v = new Validator();
        $v->required($_POST, 'visitor_name', 'Visitor name')->maxLength($_POST, 'visitor_name', 100, 'Visitor name')
          ->required($_POST, 'expected_start', 'Expected arrival')->datetime($_POST, 'expected_start', 'Expected arrival')
          ->required($_POST, 'expected_end', 'Expected departure')->datetime($_POST, 'expected_end', 'Expected departure');
        if (!empty($_POST['visitor_phone'])) {
            $v->phone($_POST, 'visitor_phone', 'Visitor phone');
        }

        $errors = $v->errors();
        if (empty($errors) && strtotime($expectedEnd) <= strtotime($expectedStart)) {
            $errors['expected_end'] = 'Expected departure must be after the arrival time.';
        }
        if (empty($errors) && strtotime($expectedStart) < time() - 300) {
            $errors['expected_start'] = 'Expected arrival cannot be in the past.';
        }

        if (!empty($errors)) {
            $old = $_POST;
            $newCode = null;
            require __DIR__ . '/../views/resident/preapprove.php';
            return;
        }

        $pass = GatePass::create(Auth::residentId(), $visitorName, $visitorPhone, $purpose, $expectedStart, $expectedEnd);

        $old = [];
        $newCode = $pass['code'];
        require __DIR__ . '/../views/resident/preapprove.php';
    }

    public function preapproveCancel(): void
    {
        Auth::requireRole('resident');
        Csrf::requireValid();

        $id = (int)($_POST['id'] ?? 0);
        if (GatePass::cancel($id, Auth::residentId())) {
            flash_set('success', 'Gate pass cancelled.');
        } else {
            flash_set('error', 'That gate pass could not be cancelled (already used or not yours).');
        }

        redirect('resident/dashboard');
    }

    public function history(): void
    {
        Auth::requireRole('resident');
        $logs = VisitorLog::byFlat(Auth::flatId());
        require __DIR__ . '/../views/resident/history.php';
    }
}
