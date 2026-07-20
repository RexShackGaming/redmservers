<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'redirect' => APP_URL . '/?page=login']);
    exit;
}

$serverId = (int)($_POST['server_id'] ?? 0);
$userId   = $_SESSION['user_id'];

if ($serverId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid server']);
    exit;
}

$stmt = db()->prepare('SELECT id FROM servers WHERE id = ?');
$stmt->execute([$serverId]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Server not found']);
    exit;
}

$stmt = db()->prepare('SELECT id, vote FROM server_votes WHERE server_id = ? AND user_id = ?');
$stmt->execute([$serverId, $userId]);
$existing = $stmt->fetch();

if ($existing) {
    $stmt = db()->prepare('DELETE FROM server_votes WHERE id = ?');
    $stmt->execute([$existing['id']]);
    $userVoted = false;
} else {
    $stmt = db()->prepare('INSERT INTO server_votes (server_id, user_id, vote) VALUES (?, ?, 1)');
    $stmt->execute([$serverId, $userId]);
    $userVoted = true;
}

$voteCount = getVoteCount($serverId);

echo json_encode([
    'success'    => true,
    'votes'      => $voteCount,
    'user_voted' => $userVoted,
]);
