<?php
$role = Auth::role();
$navItems = [];
if ($role === 'super_admin') {
    $navItems = [
        'admin/dashboard'  => 'Dashboard',
        'admin/flats'      => 'Flats',
        'admin/residents'  => 'Residents',
        'admin/guards'     => 'Guards',
        'admin/visitor-log' => 'Visitor Log & Reports',
    ];
} elseif ($role === 'guard') {
    $navItems = [
        'guard/dashboard' => 'Dashboard',
        'guard/checkin'   => 'Check In',
    ];
} elseif ($role === 'resident') {
    $navItems = [
        'resident/dashboard'   => 'Dashboard',
        'resident/preapprove'  => 'Pre-Approve Visitor',
        'resident/history'     => 'My Visitor History',
    ];
}
$currentRoute = $_GET['route'] ?? '';
?>
<nav class="navbar">
    <div class="navbar-brand">
        <span class="brand-mark">&#128737;</span> GateWatch
    </div>
    <ul class="navbar-links">
        <?php foreach ($navItems as $route => $label): ?>
            <li>
                <a href="/index.php?route=<?= e($route) ?>"
                   class="<?= $currentRoute === $route ? 'active' : '' ?>">
                    <?= e($label) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
    <div class="navbar-user">
        <span><?= e(Auth::name()) ?></span>
        <span class="user-role"><?= e(str_replace('_', ' ', $role)) ?></span>
        <form method="POST" action="/index.php?route=logout" class="inline-form">
            <?= Csrf::field() ?>
            <button type="submit" class="btn btn-ghost btn-sm">Logout</button>
        </form>
    </div>
</nav>
