<?php
$pageTitle = 'Forgot Password';

if (isLoggedIn()) redirect(APP_URL . '/?page=dashboard');
?>

<section class="auth-container">
    <div class="auth-box">
        <h1 class="auth-title">Forgot Password</h1>
        <p class="auth-subtitle">Self-service password reset is disabled for security</p>

        <div style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px;text-align:center;margin-bottom:24px">
            <div style="font-size:48px;margin-bottom:12px">📧</div>
            <p style="color:var(--text-secondary);font-size:14px;line-height:1.7">
                Email the admin at <strong><a href="mailto:<?= e(SITE_EMAIL) ?>" style="color:var(--color-primary)"><?= e(SITE_EMAIL) ?></a></strong>
                and they will help you regain access to your account.
            </p>
        </div>

        <div class="auth-footer">
            <a href="<?= APP_URL ?>/?page=login">Back to Login</a>
        </div>
    </div>
</section>
