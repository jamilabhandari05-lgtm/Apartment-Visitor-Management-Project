<?php $pageTitle = 'Dashboard'; require __DIR__ . '/../partials/header.php'; require __DIR__ . '/../partials/navbar.php'; ?>
<main class="page">
    <h1>Society Dashboard</h1>
    <p class="page-sub">Live overview of Greenwood Society's gate activity.</p>

    <?php if ($success = flash_get('success')): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
    <?php if ($error = flash_get('error')): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <div class="stat-grid">
        <div class="stat-tile"><span class="stat-value"><?= $stats['flats'] ?></span><span class="stat-label">Flats</span></div>
        <div class="stat-tile"><span class="stat-value"><?= $stats['residents'] ?></span><span class="stat-label">Residents</span></div>
        <div class="stat-tile"><span class="stat-value"><?= $stats['guards'] ?></span><span class="stat-label">Active Guards</span></div>
        <div class="stat-tile"><span class="stat-value"><?= $stats['inside'] ?></span><span class="stat-label">Inside Now</span></div>
        <div class="stat-tile"><span class="stat-value"><?= $stats['today'] ?></span><span class="stat-label">Today's Entries</span></div>
        <div class="stat-tile stat-alert"><span class="stat-value"><?= $stats['overstay'] ?></span><span class="stat-label">Overstaying</span></div>
    </div>

    <div class="card">
        <div class="card-head">
            <h2>Currently inside</h2>
            <a class="btn btn-ghost btn-sm" href="/index.php?route=admin/visitor-log">Full visitor log &rarr;</a>
        </div>
        <?php if (empty($insideNow)): ?>
            <p class="empty-state">No visitors on the premises right now.</p>
        <?php else: ?>
            <?php foreach ($insideNow as $row): $over = VisitorLog::isOverstaying($row); ?>
                <div class="inside-row">
                    <div class="inside-row-main">
                        <span class="inside-row-flat"><?= e($row['block'] . '-' . $row['number']) ?></span>
                        <span><?= e($row['visitor_name']) ?><?php if (!empty($row['purpose'])): ?> &middot; <?= e($row['purpose']) ?><?php endif; ?></span>
                        <span class="muted">In since <?= format_time($row['checked_in_at']) ?></span>
                    </div>
                    <div>
                        <?php if ($over): ?><span class="badge badge-overstay">Overstaying</span><?php else: ?><span class="badge badge-inside">Inside</span><?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
