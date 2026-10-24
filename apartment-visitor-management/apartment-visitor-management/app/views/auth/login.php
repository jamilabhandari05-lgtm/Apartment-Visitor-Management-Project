<?php $pageTitle = 'Login'; require __DIR__ . '/../partials/header.php'; ?>
<main class="auth-page">
    <div class="auth-card">
        <div class="auth-brand">
            <span class="brand-mark">&#128737;</span>
            <h1>GateWatch</h1>
            <p class="auth-sub">Apartment Visitor Management</p>
        </div>

        <?php if (!empty($errors['login'])): ?>
            <div class="alert alert-error"><?= e($errors['login']) ?></div>
        <?php endif; ?>
        <?php if ($success = flash_get('success')): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="/index.php?route=login" class="auth-form" novalidate>
            <?= Csrf::field() ?>
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= old($old ?? [], 'email') ?>" required autofocus>
            <?php if (!empty($errors['email'])): ?><span class="field-error"><?= e($errors['email']) ?></span><?php endif; ?>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <?php if (!empty($errors['password'])): ?><span class="field-error"><?= e($errors['password']) ?></span><?php endif; ?>

            <button type="submit" class="btn btn-accent btn-block">Log in</button>
        </form>

        <details class="demo-creds">
            <summary>Demo credentials</summary>
            <table>
                <tr><th>Role</th><th>Email</th><th>Password</th></tr>
                <tr><td>Super Admin</td><td>admin@greenwood.test</td><td>Admin@123</td></tr>
                <tr><td>Security Guard</td><td>guard1@greenwood.test</td><td>Guard@123</td></tr>
                <tr><td>Resident</td><td>resident1@greenwood.test</td><td>Resident@123</td></tr>
            </table>
        </details>
    </div>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
