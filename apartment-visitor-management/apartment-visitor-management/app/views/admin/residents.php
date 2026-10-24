<?php $pageTitle = 'Residents'; require __DIR__ . '/../partials/header.php'; require __DIR__ . '/../partials/navbar.php'; ?>
<main class="page">
    <h1>Manage Residents</h1>
    <p class="page-sub">Resident accounts, each linked to a flat.</p>

    <?php if ($success = flash_get('success')): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
    <?php if ($error = flash_get('error')): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <div class="card">
        <div class="card-head"><h2>Create a resident account</h2></div>
        <form method="POST" action="/index.php?route=admin/residents/store" class="form-grid">
            <?= Csrf::field() ?>
            <label>Full name
                <input type="text" name="name" value="<?= old($old ?? [], 'name') ?>" required>
                <?php if (!empty($errors['name'])): ?><span class="field-error"><?= e($errors['name']) ?></span><?php endif; ?>
            </label>
            <label>Email
                <input type="email" name="email" value="<?= old($old ?? [], 'email') ?>" required>
                <?php if (!empty($errors['email'])): ?><span class="field-error"><?= e($errors['email']) ?></span><?php endif; ?>
            </label>
            <label>Phone
                <input type="text" name="phone" value="<?= old($old ?? [], 'phone') ?>" required>
                <?php if (!empty($errors['phone'])): ?><span class="field-error"><?= e($errors['phone']) ?></span><?php endif; ?>
            </label>
            <label>Flat
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
            <label>Password
                <input type="password" name="password" required>
                <?php if (!empty($errors['password'])): ?><span class="field-error"><?= e($errors['password']) ?></span><?php endif; ?>
            </label>
            <div class="form-actions">
                <button type="submit" class="btn btn-accent">Create resident</button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-head"><h2>All residents (<?= count($residents) ?>)</h2></div>
        <?php if (empty($residents)): ?>
            <p class="empty-state">No residents yet.</p>
        <?php else: ?>
        <div class="table-scroll">
        <table class="data-table">
            <tr><th>Name</th><th>Flat</th><th>Email</th><th>Phone</th><th>Status</th><th>Actions</th></tr>
            <?php foreach ($residents as $r): ?>
                <tr>
                    <td><?= e($r['name']) ?></td>
                    <td><?= e($r['block'] . '-' . $r['number']) ?></td>
                    <td><?= e($r['email']) ?></td>
                    <td><?= e($r['phone']) ?></td>
                    <td><span class="badge badge-<?= e($r['status']) ?>"><?= e($r['status']) ?></span></td>
                    <td class="row-actions">
                        <form method="POST" action="/index.php?route=admin/residents/status" class="inline-form">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="user_id" value="<?= (int)$r['user_id'] ?>">
                            <input type="hidden" name="status" value="<?= $r['status'] === 'active' ? 'inactive' : 'active' ?>">
                            <button type="submit" class="btn btn-ghost btn-sm">
                                <?= $r['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                            </button>
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
