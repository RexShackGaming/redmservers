<?php
requireLogin();
$pageTitle = 'Support Ticket';

$ticketId = (int)($_GET['id'] ?? 0);
if ($ticketId <= 0) redirect(APP_URL . '/?page=tickets');

$stmt = db()->prepare('SELECT * FROM tickets WHERE id = ? AND (user_id = ? OR ? IN (SELECT id FROM users WHERE role = ?))');
$stmt->execute([$ticketId, $_SESSION['user_id'], $_SESSION['user_id'], 'admin']);
$ticket = $stmt->fetch();

if (!$ticket) redirect(APP_URL . '/?page=tickets');

$stmt = db()->prepare('
    SELECT tm.*, u.username, u.role
    FROM ticket_messages tm
    JOIN users u ON tm.user_id = u.id
    WHERE tm.ticket_id = ?
    ORDER BY tm.created_at ASC
');
$stmt->execute([$ticketId]);
$messages = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        flash('error', 'Invalid request.');
    } else {
        $message = trim($_POST['message'] ?? '');

        if (strlen($message) < 1) {
            flash('error', 'Message cannot be empty.');
        } else {
            $stmt = db()->prepare('INSERT INTO ticket_messages (ticket_id, user_id, message) VALUES (?, ?, ?)');
            $stmt->execute([$ticketId, $_SESSION['user_id'], $message]);
            redirect(APP_URL . '/?page=ticket-view&id=' . $ticketId);
        }
    }
}
?>

<section class="section">
    <div class="container" style="max-width:700px">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:24px">
            <div>
                <a href="<?= APP_URL ?>/?page=tickets" style="font-size:13px;color:var(--text-muted);display:block;margin-bottom:8px">← Back to Tickets</a>
                <h1 class="section-title" style="margin-bottom:4px"><?= e($ticket['subject']) ?></h1>
                <span class="tag" style="<?= $ticket['status'] === 'open' ? 'background:rgba(40,167,69,.15);color:#28a745' : 'background:rgba(108,117,125,.15);color:var(--text-muted)' ?>"><?= e($ticket['status']) ?></span>
            </div>
            <?php if ($ticket['status'] === 'open' && (isAdmin() || $_SESSION['user_id'] == $ticket['user_id'])): ?>
                <form method="post" action="<?= APP_URL ?>/api/close_ticket.php" style="display:inline">
                    <?= csrfField() ?>
                    <input type="hidden" name="ticket_id" value="<?= $ticketId ?>">
                    <button type="submit" class="btn btn-outline btn-sm">Close Ticket</button>
                </form>
            <?php endif; ?>
        </div>

        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;margin-bottom:20px">
            <?php if (empty($messages)): ?>
                <div style="padding:40px;text-align:center;color:var(--text-muted)">No messages.</div>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <div style="padding:16px 20px;border-bottom:1px solid var(--border)">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                            <strong style="font-size:14px;color:<?= $msg['role'] === 'admin' ? 'var(--color-primary)' : 'var(--text-primary)' ?>">
                                <?= e($msg['username']) ?>
                                <?php if ($msg['role'] === 'admin'): ?>
                                    <span style="font-size:11px;color:var(--text-muted);font-weight:400">(Staff)</span>
                                <?php endif; ?>
                            </strong>
                            <span style="font-size:12px;color:var(--text-muted)"><?= timeAgo($msg['created_at']) ?></span>
                        </div>
                        <div style="font-size:14px;color:var(--text-secondary);line-height:1.7;white-space:pre-line"><?= e($msg['message']) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($ticket['status'] === 'open'): ?>
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:20px">
                <form method="post">
                    <?= csrfField() ?>
                    <div class="form-group">
                        <label class="form-label" for="message">Reply</label>
                        <textarea id="message" name="message" class="form-textarea" required rows="4" placeholder="Type your reply..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Reply</button>
                </form>
            </div>
        <?php else: ?>
            <div style="text-align:center;padding:20px;color:var(--text-muted);background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg)">
                This ticket is closed.
            </div>
        <?php endif; ?>
    </div>
</section>
