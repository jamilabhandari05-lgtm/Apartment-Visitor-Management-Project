<?php $pageTitle = 'Guards'; require __DIR__ . '/../partials/header.php'; require __DIR__ . '/../partials/navbar.php'; ?>
<main class="page">
    <h1>Manage Security Guard Accounts</h1>
    <p class="page-sub">Guards who can log visitors at the gate.</p>

    <?php if ($success = flash_get('success')): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
    <?php if ($error = flash_get('error')): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <div class="card">
        <div class="card-head"><h2>Create a guard account</h2></div>
        <form method="POST" action="/index.php?route=admin/guards/store" class="form-grid">
            <?= Csrf::field() ?>
            <label>Full name
                <input type="text" name="name" value="<?= old($old ?? [], 'name') ?>" required>
                <?php if (!empty($errors['name'])): ?><span class="field-error"><?= e($errors['name']) ?></span><?php endif; ?>
            </label>
            <label>Email
                <input type="email" name="email" value="<?= old($old ?? [], 'email') ?>" required>
                <?php if (!empty($errors['email'])): ?><span class="field-error"><?= e($errors['email']) ?></span><?php endif; ?>
            </label>
            <label>Password
                <input type="password" name="password" required>
                <?php if (!empty($errors['password'])): ?><span class="field-error"><?= e($errors['password']) ?></span><?php endif; ?>
            </label>
            <div class="form-actions">
                <button type="submit" class="btn btn-accent">Create guard</button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-head"><h2>All guards (<?= count($guards) ?>)</h2></div>
        <?php if (empty($guards)): ?>
            <p class="empty-state">No guards yet.</p>
        <?php else: ?>
        <div class="table-scroll">
        <table class="data-table">
            <tr><th>Name</th><th>Email</th><th>Status</th><th>Actions</th></tr>
            <?php foreach ($guards as $g): ?>
                <tr>
                    <td><?= e($g['name']) ?></td>
                    <td><?= e($g['email']) ?></td>
                    <td><span class="badge badge-<?= e($g['status']) ?>"><?= e($g['status']) ?></span></td>
                    <td class="row-actions">
                        <form method="POST" action="/index.php?route=admin/guards/status" class="inline-form">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="user_id" value="<?= (int)$g['id'] ?>">
                            <input type="hidden" name="status" value="<?= $g['status'] === 'active' ? 'inactive' : 'active' ?>">
                            <button type="submit" class="btn btn-ghost btn-sm">
                                <?= $g['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
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
