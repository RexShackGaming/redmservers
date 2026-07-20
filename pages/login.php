<?php
$pageTitle = 'Login';

if (isLoggedIn()) redirect(APP_URL . '/?page=dashboard');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        flash('error', 'Invalid request.');
    } else {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (loginUser($email, $password)) {
            flash('success', 'Welcome back, ' . $_SESSION['username'] . '!');
            redirect(APP_URL . '/?page=dashboard');
        } else {
            flash('error', 'Invalid email or password.');
        }
    }
}
?>

<section class="auth-container">
    <div class="auth-box">
        <h1 class="auth-title">Login</h1>
        <p class="auth-subtitle">Welcome back! Sign in to manage your servers</p>

        <?= flashHtml('error', 'alert alert-error') ?>
        <?= flashHtml('success', 'alert alert-success flash-auto-dismiss') ?>

        <form method="post">
            <?= csrfField() ?>
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-input" required value="<?= e($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-input" required>
                <div style="margin-top:6px;font-size:13px">
                    <a href="<?= APP_URL ?>/?page=forgot-password" style="color:var(--color-primary);">Forgot password?</a>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg">Login</button>
        </form>

        <div class="auth-footer">
            Don't have an account? <a href="<?= APP_URL ?>/?page=signup">Sign Up</a>
        </div>
    </div>
</section>
