<?php $pageTitle = 'Dashboard'; require __DIR__ . '/../partials/header.php'; require __DIR__ . '/../partials/navbar.php'; ?>
<main class="page">
    <div class="card-head">
        <h1>My Dashboard</h1>
        <a class="btn btn-accent" href="/index.php?route=resident/preapprove">Pre-approve a visitor</a>
    </div>

    <?php if ($success = flash_get('success')): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
    <?php if ($error = flash_get('error')): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <div class="card">
        <div class="card-head"><h2>Currently visiting my flat</h2></div>
        <?php if (empty($visiting)): ?>
            <p class="empty-state">No one is visiting right now.</p>
        <?php else: ?>
            <?php foreach ($visiting as $row): ?>
                <div class="inside-row">
                    <div class="inside-row-main">
                        <span><?= e($row['visitor_name']) ?><?php if (!empty($row['purpose'])): ?> &middot; <?= e($row['purpose']) ?><?php endif; ?></span>
                        <span class="muted">In since <?= format_time($row['checked_in_at']) ?></span>
                    </div>
                    <span class="badge badge-inside">Inside</span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-head"><h2>Upcoming pre-approvals</h2></div>
        <?php if (empty($upcoming)): ?>
            <p class="empty-state">No upcoming pre-approved visitors. <a href="/index.php?route=resident/preapprove">Create one &rarr;</a></p>
        <?php else: ?>
        <div class="table-scroll">
        <table class="data-table">
            <tr><th>Visitor</th><th>Window</th><th>Code</th><th>Actions</th></tr>
            <?php foreach ($upcoming as $p): ?>
                <tr>
                    <td><?= e($p['visitor_name']) ?></td>
                    <td><?= format_datetime($p['expected_start']) ?> &ndash; <?= format_datetime($p['expected_end']) ?></td>
                    <td><span class="code-value-inline"><?= e($p['code']) ?></span></td>
                    <td>
                        <form method="POST" action="/index.php?route=resident/preapprove/cancel" class="inline-form"
                              onsubmit="return confirm('Cancel this gate pass?');">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        </div>
        <?php endif; ?>
    </div>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
