<?php
$pageTitle = 'Reset Password';

if (isLoggedIn()) redirect(APP_URL . '/?page=dashboard');

$token = $_GET['token'] ?? '';
$tokenError = '';
$done = false;

if (empty($token)) {
    $tokenError = 'Missing reset token.';
} else {
    $stmt = db()->prepare("SELECT pr.id, pr.user_id, u.email, u.username
        FROM password_resets pr
        JOIN users u ON pr.user_id = u.id
        WHERE pr.token = ? AND pr.used = 0 AND pr.expires_at > NOW()
        LIMIT 1");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();

    if (!$reset) {
        $tokenError = 'Invalid or expired reset token.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($tokenError)) {
    if (!verifyCsrf()) {
        flash('error', 'Invalid request.');
    } else {
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm'] ?? '';

        if (strlen($password) < 6) {
            flash('error', 'Password must be at least 6 characters.');
        } elseif ($password !== $confirm) {
            flash('error', 'Passwords do not match.');
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
            $stmt->execute([$hash, $reset['user_id']]);

            $stmt = db()->prepare('UPDATE password_resets SET used = 1 WHERE id = ?');
            $stmt->execute([$reset['id']]);

            flash('success', 'Password reset successfully. You can now log in.');
            $done = true;
        }
    }
}
?>

<section class="auth-container">
    <div class="auth-box">
        <h1 class="auth-title">Reset Password</h1>
        <p class="auth-subtitle">Choose a new password for your account</p>

        <?= flashHtml('error', 'alert alert-error') ?>
        <?= flashHtml('success', 'alert alert-success flash-auto-dismiss') ?>

        <?php if ($tokenError): ?>
            <div class="alert alert-error"><?= e($tokenError) ?></div>
            <div class="auth-footer">
                <a href="<?= APP_URL ?>/?page=forgot-password">Request a new reset link</a>
            </div>
        <?php elseif ($done): ?>
            <div class="auth-footer">
                <a href="<?= APP_URL ?>/?page=login" class="btn btn-primary btn-block btn-lg">Log In</a>
            </div>
        <?php else: ?>
            <form method="post">
                <?= csrfField() ?>
                <input type="hidden" name="token" value="<?= e($token) ?>">
                <div class="form-group">
                    <label class="form-label" for="password">New Password</label>
                    <input type="password" id="password" name="password" class="form-input" required minlength="6">
                </div>
                <div class="form-group">
                    <label class="form-label" for="confirm">Confirm Password</label>
                    <input type="password" id="confirm" name="confirm" class="form-input" required minlength="6">
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">Reset Password</button>
            </form>

            <div class="auth-footer">
                <a href="<?= APP_URL ?>/?page=login">Back to Login</a>
            </div>
        <?php endif; ?>
    </div>
</section>
