<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$serverId   = (int)($_POST['server_id'] ?? $_GET['id'] ?? 0);
$csrfToken  = $_POST['csrf_token'] ?? '';

if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
    flash('error', 'Invalid request.');
    redirect(APP_URL . '/?page=dashboard');
}

if ($serverId <= 0) {
    flash('error', 'Invalid server.');
    redirect(APP_URL . '/?page=dashboard');
}

$stmt = db()->prepare('SELECT id, banner_image FROM servers WHERE id = ?');
$stmt->execute([$serverId]);
$server = $stmt->fetch();

if (!$server || ($server['user_id'] != $_SESSION['user_id'] && !isAdmin())) {
    flash('error', 'Server not found.');
    redirect(APP_URL . '/?page=dashboard');
}

$stmt = db()->prepare('DELETE FROM servers WHERE id = ?');
$stmt->execute([$serverId]);

if ($server['banner_image'] && file_exists(BANNER_DIR . $server['banner_image'])) {
    unlink(BANNER_DIR . $server['banner_image']);
}

$stmt = db()->prepare('SELECT image_path FROM server_screenshots WHERE server_id = ?');
$stmt->execute([$serverId]);
$ssList = $stmt->fetchAll();
foreach ($ssList as $ss) {
    $path = SCREENSHOT_DIR . $ss['image_path'];
    if (file_exists($path)) unlink($path);
}

flash('success', 'Server deleted.');
redirect(APP_URL . '/?page=dashboard');
