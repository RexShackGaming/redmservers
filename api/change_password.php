<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(APP_URL);
}

if (!verifyCsrf()) {
    flash('error', 'Invalid request.');
    redirect(APP_URL . '/?page=dashboard');
}

$current   = $_POST['current_password'] ?? '';
$newPass   = $_POST['new_password'] ?? '';
$confirm   = $_POST['confirm_password'] ?? '';

$stmt = db()->prepare('SELECT password_hash FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || !password_verify($current, $user['password_hash'])) {
    flash('error', 'Current password is incorrect.');
    redirect(APP_URL . '/?page=dashboard');
}

if (strlen($newPass) < 6) {
    flash('error', 'New password must be at least 6 characters.');
    redirect(APP_URL . '/?page=dashboard');
}

if ($newPass !== $confirm) {
    flash('error', 'Passwords do not match.');
    redirect(APP_URL . '/?page=dashboard');
}

$hash = password_hash($newPass, PASSWORD_DEFAULT);
$stmt = db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
$stmt->execute([$hash, $_SESSION['user_id']]);

flash('success', 'Password changed successfully.');
redirect(APP_URL . '/?page=dashboard');
