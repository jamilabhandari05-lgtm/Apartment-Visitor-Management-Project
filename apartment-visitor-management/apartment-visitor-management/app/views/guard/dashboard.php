<?php $pageTitle = 'Dashboard'; require __DIR__ . '/../partials/header.php'; require __DIR__ . '/../partials/navbar.php'; ?>
<main class="page">
    <div class="card-head">
        <h1>Gate Dashboard</h1>
        <a class="btn btn-accent" href="/index.php?route=guard/checkin">Check in a visitor</a>
    </div>
    <p class="page-sub">Everyone currently on the premises.</p>

    <?php if ($success = flash_get('success')): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
    <?php if ($error = flash_get('error')): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <div class="stat-grid">
        <div class="stat-tile"><span class="stat-value"><?= count($insideNow) ?></span><span class="stat-label">Inside Now</span></div>
        <div class="stat-tile stat-alert"><span class="stat-value"><?= count($overstaying) ?></span><span class="stat-label">Overstaying</span></div>
    </div>

    <div class="card">
        <div class="card-head"><h2>Currently inside</h2></div>
        <?php if (empty($insideNow)): ?>
            <p class="empty-state">No visitors on the premises right now.</p>
        <?php else: ?>
            <?php foreach ($insideNow as $row): $over = VisitorLog::isOverstaying($row); ?>
                <div class="inside-row">
                    <div class="inside-row-main">
                        <span class="inside-row-flat"><?= e($row['block'] . '-' . $row['number']) ?></span>
                        <span><?= e($row['visitor_name']) ?><?php if (!empty($row['purpose'])): ?> &middot; <?= e($row['purpose']) ?><?php endif; ?></span>
                        <span class="muted">In since <?= format_time($row['checked_in_at']) ?> &middot; <?= $row['gate_pass_id'] ? 'Pre-approved' : 'Walk-in' ?></span>
                    </div>
                    <div class="row-actions">
                        <?php if ($over): ?><span class="badge badge-overstay">Overstaying</span><?php endif; ?>
                        <form method="POST" action="/index.php?route=guard/checkout" class="inline-form">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                            <button type="submit" class="btn btn-primary btn-sm">Check out</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
