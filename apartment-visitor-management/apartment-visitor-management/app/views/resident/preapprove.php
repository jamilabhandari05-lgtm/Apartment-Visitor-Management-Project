<?php $pageTitle = 'Pre-Approve a Visitor'; require __DIR__ . '/../partials/header.php'; require __DIR__ . '/../partials/navbar.php'; ?>
<main class="page">
    <h1>Pre-Approve a Visitor</h1>
    <p class="page-sub">Share the gate-pass code with your visitor so the guard can check them in quickly.</p>

    <?php if (!empty($newCode)): ?>
        <div class="card">
            <div class="code-display">
                <div class="code-label">Gate-pass code</div>
                <div class="code-value"><?= e($newCode) ?></div>
            </div>
            <p>Share this code with your visitor. The guard will match it at the gate — it can only be used once.</p>
            <a class="btn btn-accent" href="/index.php?route=resident/preapprove">Create another</a>
            <a class="btn btn-ghost" href="/index.php?route=resident/dashboard">Back to dashboard</a>
        </div>
    <?php else: ?>
    <div class="card">
        <form method="POST" action="/index.php?route=resident/preapprove/store" class="form-grid">
            <?= Csrf::field() ?>
            <label>Visitor name
                <input type="text" name="visitor_name" value="<?= old($old ?? [], 'visitor_name') ?>" required>
                <?php if (!empty($errors['visitor_name'])): ?><span class="field-error"><?= e($errors['visitor_name']) ?></span><?php endif; ?>
            </label>
            <label>Visitor phone (optional)
                <input type="text" name="visitor_phone" value="<?= old($old ?? [], 'visitor_phone') ?>">
                <?php if (!empty($errors['visitor_phone'])): ?><span class="field-error"><?= e($errors['visitor_phone']) ?></span><?php endif; ?>
            </label>
            <label>Purpose (optional)
                <input type="text" name="purpose" value="<?= old($old ?? [], 'purpose') ?>" placeholder="Personal visit, family guest...">
            </label>
            <label>Expected arrival
                <input type="datetime-local" name="expected_start" value="<?= old($old ?? [], 'expected_start') ?>" required>
                <?php if (!empty($errors['expected_start'])): ?><span class="field-error"><?= e($errors['expected_start']) ?></span><?php endif; ?>
            </label>
            <label>Expected departure
                <input type="datetime-local" name="expected_end" value="<?= old($old ?? [], 'expected_end') ?>" required>
                <?php if (!empty($errors['expected_end'])): ?><span class="field-error"><?= e($errors['expected_end']) ?></span><?php endif; ?>
            </label>
            <div class="form-actions">
                <button type="submit" class="btn btn-accent">Generate gate pass</button>
            </div>
        </form>
    </div>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
