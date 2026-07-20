<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$id       = (int)($_GET['id'] ?? 0);
$serverId = (int)($_GET['server_id'] ?? 0);

if ($id <= 0 || $serverId <= 0) redirect(APP_URL . '/?page=dashboard');

$stmt = db()->prepare('SELECT image_path FROM server_screenshots WHERE id = ? AND server_id = ?');
$stmt->execute([$id, $serverId]);
$ss = $stmt->fetch();

if ($ss) {
    $stmt2 = db()->prepare('SELECT user_id FROM servers WHERE id = ?');
    $stmt2->execute([$serverId]);
    $server = $stmt2->fetch();

    if ($server && $server['user_id'] == $_SESSION['user_id']) {
        $path = SCREENSHOT_DIR . $ss['image_path'];
        if (file_exists($path)) unlink($path);
        $stmt3 = db()->prepare('DELETE FROM server_screenshots WHERE id = ?');
        $stmt3->execute([$id]);
    }
}

flash('success', 'Screenshot deleted.');
redirect(APP_URL . '/?page=server-edit&id=' . $serverId);
