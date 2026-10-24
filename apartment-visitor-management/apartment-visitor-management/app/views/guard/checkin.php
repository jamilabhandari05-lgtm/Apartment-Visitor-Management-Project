<?php
$pageTitle = 'Check In';
$activeTab = isset($old['visitor_name']) ? 'walkin' : 'code';
require __DIR__ . '/../partials/header.php'; require __DIR__ . '/../partials/navbar.php';
?>
<main class="page">
    <h1>Check In a Visitor</h1>
    <p class="page-sub">Look up a gate-pass code, or log a walk-in directly against a flat.</p>

    <?php if ($success = flash_get('success')): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
    <?php if ($error = flash_get('error')): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <div class="tab-bar">
        <a href="#" class="tab-link <?= $activeTab === 'code' ? 'active' : '' ?>" data-tab-target="code">By gate-pass code</a>
        <a href="#" class="tab-link <?= $activeTab === 'walkin' ? 'active' : '' ?>" data-tab-target="walkin">Walk-in</a>
    </div>

    <div class="card <?= $activeTab === 'code' ? '' : 'hidden' ?>" data-tab-panel="code">
        <form method="POST" action="/index.php?route=guard/checkin/lookup" class="form-grid">
            <?= Csrf::field() ?>
            <label>Gate-pass code
                <input type="text" id="code" name="code" value="<?= old($old ?? [], 'code') ?>" maxlength="10" autofocus placeholder="e.g. K3F7Q" required>
                <?php if (!empty($errors['code'])): ?><span class="field-error"><?= e($errors['code']) ?></span><?php endif; ?>
            </label>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Look up code</button>
            </div>
        </form>

        <?php if (!empty($passPreview)): ?>
            <div class="card" style="background: var(--bg); margin-top:1rem;">
                <h2>Pre-approval details</h2>
                <p><strong>Visitor:</strong> <?= e($passPreview['visitor_name']) ?> <?php if (!empty($passPreview['visitor_phone'])): ?>(<?= e($passPreview['visitor_phone']) ?>)<?php endif; ?></p>
                <p><strong>Flat:</strong> <?= e($passPreview['block'] . '-' . $passPreview['number']) ?> &middot; <strong>Resident:</strong> <?= e($passPreview['resident_name']) ?></p>
                <p><strong>Purpose:</strong> <?= e($passPreview['purpose'] ?? '-') ?></p>
                <p><strong>Expected window:</strong> <?= format_datetime($passPreview['expected_start']) ?> &ndash; <?= format_datetime($passPreview['expected_end']) ?></p>
                <?php $residentInactive = ($passPreview['resident_status'] ?? 'active') !== 'active'; ?>
                <p><strong>Status:</strong>
                    <?php if ($residentInactive): ?>
                        <span class="badge badge-expired">Resident deactivated</span>
                    <?php elseif ($passPreview['status'] === 'pending' && !GatePass::isExpired($passPreview)): ?>
                        <span class="badge badge-pending">Ready to check in</span>
                    <?php elseif ($passPreview['status'] === 'pending'): ?>
                        <span class="badge badge-expired">Expired</span>
                    <?php else: ?>
                        <span class="badge badge-<?= e($passPreview['status']) ?>"><?= e($passPreview['status']) ?></span>
                    <?php endif; ?>
                </p>
                <?php if (!$residentInactive && $passPreview['status'] === 'pending' && !GatePass::isExpired($passPreview)): ?>
                    <form method="POST" action="/index.php?route=guard/checkin/code" class="inline-form">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="code" value="<?= e($passPreview['code']) ?>">
                        <button type="submit" class="btn btn-accent">Confirm check-in</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="card <?= $activeTab === 'walkin' ? '' : 'hidden' ?>" data-tab-panel="walkin">
        <form method="POST" action="/index.php?route=guard/checkin/walkin" class="form-grid">
            <?= Csrf::field() ?>
            <label>Flat being visited
                <select name="flat_id" required>
                    <option value="">Select a flat&hellip;</option>
                    <?php foreach ($flats as $flat): ?>
                        <option value="<?= (int)$flat['id'] ?>" <?= (($old['flat_id'] ?? '') == $flat['id']) ? 'selected' : '' ?>>
                            <?= e($flat['block'] . '-' . $flat['number']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['flat_id'])): ?><span class="field-error"><?= e($errors['flat_id']) ?></span><?php endif; ?>
            </label>
            <label>Visitor name
                <input type="text" name="visitor_name" value="<?= old($old ?? [], 'visitor_name') ?>" required>
                <?php if (!empty($errors['visitor_name'])): ?><span class="field-error"><?= e($errors['visitor_name']) ?></span><?php endif; ?>
            </label>
            <label>Visitor phone (optional)
                <input type="text" name="visitor_phone" value="<?= old($old ?? [], 'visitor_phone') ?>">
            </label>
            <label>Purpose (optional)
                <input type="text" name="purpose" value="<?= old($old ?? [], 'purpose') ?>" placeholder="Delivery, cab pickup, maintenance...">
            </label>
            <label>Note (optional)
                <input type="text" name="photo_note" value="<?= old($old ?? [], 'photo_note') ?>" placeholder="Vehicle number, description, ID checked...">
            </label>
            <div class="form-actions">
                <button type="submit" class="btn btn-accent">Check in walk-in</button>
            </div>
        </form>
    </div>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
