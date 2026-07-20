<?php
requireLogin();
$pageTitle = 'My Support Tickets';

$stmt = db()->prepare('SELECT * FROM tickets WHERE user_id = ? ORDER BY updated_at DESC');
$stmt->execute([$_SESSION['user_id']]);
$tickets = $stmt->fetchAll();
?>

<section class="section">
    <div class="container" style="max-width:800px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
            <div>
                <h1 class="section-title" style="margin-bottom:4px">Support Tickets</h1>
                <p style="color:var(--text-secondary);font-size:14px">Get help with your servers or account</p>
            </div>
            <a href="<?= APP_URL ?>/?page=ticket-create" class="btn btn-primary">+ New Ticket</a>
        </div>

        <?php if (empty($tickets)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">🎫</div>
                <h3>No tickets yet</h3>
                <p>Create a ticket and our team will get back to you.</p>
                <a href="<?= APP_URL ?>/?page=ticket-create" class="btn btn-primary" style="margin-top:12px">Create Ticket</a>
            </div>
        <?php else: ?>
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden">
                <?php foreach ($tickets as $i => $ticket): ?>
                    <a href="<?= APP_URL ?>/?page=ticket-view&id=<?= $ticket['id'] ?>" style="display:flex;align-items:center;gap:16px;padding:16px 20px;border-bottom:<?= $i < count($tickets) - 1 ? '1px solid var(--border)' : 'none' ?>;transition:var(--transition);text-decoration:none">
                        <div style="flex:1">
                            <div style="font-weight:600;color:var(--text-primary);margin-bottom:4px"><?= e($ticket['subject']) ?></div>
                            <div style="font-size:13px;color:var(--text-muted)"><?= timeAgo($ticket['created_at']) ?></div>
                        </div>
                        <span class="tag" style="<?= $ticket['status'] === 'open' ? 'background:rgba(40,167,69,.15);color:#28a745' : 'background:rgba(108,117,125,.15);color:var(--text-muted)' ?>"><?= e($ticket['status']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
