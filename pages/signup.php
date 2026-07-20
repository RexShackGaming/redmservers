<?php
$pageTitle = 'Sign Up';

if (isLoggedIn()) redirect(APP_URL . '/?page=dashboard');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        $errors[] = 'Invalid request.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $result = registerUser($username, $email, $password);

        if ($result['success']) {
            loginUser($email, $password);
            flash('success', 'Welcome! Your account has been created.');
            redirect(APP_URL . '/?page=dashboard');
        } else {
            $errors = $result['errors'];
        }
    }
}
?>

<section class="auth-container">
    <div class="auth-box">
        <h1 class="auth-title">Create Account</h1>
        <p class="auth-subtitle">Join the community and list your RedM server</p>

        <?= flashHtml('error', 'alert alert-error') ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $err): ?>
                    <div><?= e($err) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <?= csrfField() ?>
            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input type="text" id="username" name="username" class="form-input" required minlength="3" maxlength="50" value="<?= e($_POST['username'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-input" required value="<?= e($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-input" required minlength="6">
                <div class="form-help">Minimum 6 characters</div>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg">Sign Up</button>
        </form>

        <div class="auth-footer">
            Already have an account? <a href="<?= APP_URL ?>/?page=login">Login</a>
        </div>
    </div>
</section>
