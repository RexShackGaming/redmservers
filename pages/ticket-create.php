<?php
requireLogin();
$pageTitle = 'New Support Ticket';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        flash('error', 'Invalid request.');
    } else {
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (strlen($subject) < 3) {
            flash('error', 'Subject must be at least 3 characters.');
        } elseif (strlen($message) < 10) {
            flash('error', 'Message must be at least 10 characters.');
        } else {
            $stmt = db()->prepare('INSERT INTO tickets (user_id, subject) VALUES (?, ?)');
            $stmt->execute([$_SESSION['user_id'], $subject]);
            $ticketId = db()->lastInsertId();

            $stmt = db()->prepare('INSERT INTO ticket_messages (ticket_id, user_id, message) VALUES (?, ?, ?)');
            $stmt->execute([$ticketId, $_SESSION['user_id'], $message]);

            flash('success', 'Ticket created successfully.');
            redirect(APP_URL . '/?page=ticket-view&id=' . $ticketId);
        }
    }
}
?>

<section class="section">
    <div class="container" style="max-width:600px">
        <h1 class="section-title" style="margin-bottom:24px">New Support Ticket</h1>

        <?= flashHtml('error', 'alert alert-error') ?>
        <?= flashHtml('success', 'alert alert-success flash-auto-dismiss') ?>

        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px">
            <form method="post">
                <?= csrfField() ?>
                <div class="form-group">
                    <label class="form-label" for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" class="form-input" required maxlength="255" placeholder="Brief description of your issue" value="<?= e($_POST['subject'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="message">Message</label>
                    <textarea id="message" name="message" class="form-textarea" required rows="6" placeholder="Describe your issue in detail..."><?= e($_POST['message'] ?? '') ?></textarea>
                </div>
                <div style="display:flex;gap:12px;margin-top:24px">
                    <button type="submit" class="btn btn-primary btn-lg">Submit Ticket</button>
                    <a href="<?= APP_URL ?>/?page=tickets" class="btn btn-outline btn-lg">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</section>
