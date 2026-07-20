<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$ticketId = (int)($_POST['ticket_id'] ?? 0);

if (!verifyCsrf() || $ticketId <= 0) {
    flash('error', 'Invalid request.');
    redirect(APP_URL . '/?page=tickets');
}

$stmt = db()->prepare('SELECT * FROM tickets WHERE id = ? AND (user_id = ? OR ? IN (SELECT id FROM users WHERE role = ?))');
$stmt->execute([$ticketId, $_SESSION['user_id'], $_SESSION['user_id'], 'admin']);
$ticket = $stmt->fetch();

if (!$ticket) {
    flash('error', 'Ticket not found.');
    redirect(APP_URL . '/?page=tickets');
}

$stmt = db()->prepare('UPDATE tickets SET status = ? WHERE id = ?');
$stmt->execute(['closed', $ticketId]);

flash('success', 'Ticket closed.');
redirect(APP_URL . '/?page=ticket-view&id=' . $ticketId);
