<?php $pageTitle = 'Flats'; require __DIR__ . '/../partials/header.php'; require __DIR__ . '/../partials/navbar.php'; ?>
<main class="page">
    <h1>Manage Flats</h1>
    <p class="page-sub">Blocks and units in the society.</p>

    <?php if ($success = flash_get('success')): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
    <?php if ($error = flash_get('error')): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <div class="card">
        <div class="card-head"><h2>Add a flat</h2></div>
        <form method="POST" action="/index.php?route=admin/flats/store" class="form-grid">
            <?= Csrf::field() ?>
            <label>Block
                <input type="text" name="block" value="<?= old($old ?? [], 'block') ?>" maxlength="10" required>
                <?php if (!empty($errors['block'])): ?><span class="field-error"><?= e($errors['block']) ?></span><?php endif; ?>
            </label>
            <label>Flat number
                <input type="text" name="number" value="<?= old($old ?? [], 'number') ?>" maxlength="10" required>
                <?php if (!empty($errors['number'])): ?><span class="field-error"><?= e($errors['number']) ?></span><?php endif; ?>
            </label>
            <label>Floor (optional)
                <input type="number" name="floor" value="<?= old($old ?? [], 'floor') ?>">
            </label>
            <div class="form-actions">
                <button type="submit" class="btn btn-accent">Add flat</button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-head"><h2>All flats (<?= count($flats) ?>)</h2></div>
        <?php if (empty($flats)): ?>
            <p class="empty-state">No flats added yet.</p>
        <?php else: ?>
        <div class="table-scroll">
        <table class="data-table">
            <tr><th>Block</th><th>Number</th><th>Floor</th><th>Residents</th><th>Actions</th></tr>
            <?php foreach ($flats as $flat): ?>
                <tr>
                    <td><?= e($flat['block']) ?></td>
                    <td><?= e($flat['number']) ?></td>
                    <td><?= e((string)($flat['floor'] ?? '-')) ?></td>
                    <td><?= (int)$flat['resident_count'] ?></td>
                    <td class="row-actions">
                        <details>
                            <summary class="btn btn-ghost btn-sm">Edit</summary>
                            <form method="POST" action="/index.php?route=admin/flats/update" class="form-grid" style="margin-top:0.6rem;">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="id" value="<?= (int)$flat['id'] ?>">
                                <label>Block <input type="text" name="block" value="<?= e($flat['block']) ?>" maxlength="10" required></label>
                                <label>Number <input type="text" name="number" value="<?= e($flat['number']) ?>" maxlength="10" required></label>
                                <label>Floor <input type="number" name="floor" value="<?= e((string)($flat['floor'] ?? '')) ?>"></label>
                                <button type="submit" class="btn btn-primary btn-sm">Save</button>
                            </form>
                        </details>
                        <form method="POST" action="/index.php?route=admin/flats/delete" class="inline-form"
                              onsubmit="return confirm('Remove this flat? Only possible if it has no residents.');">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="id" value="<?= (int)$flat['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
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
