<?php
$statusFilter = $_GET['status'] ?? 'open';
$allowedFilters = ['open', 'closed', 'all'];
if (!in_array($statusFilter, $allowedFilters)) $statusFilter = 'open';

$where = $statusFilter !== 'all' ? 'WHERE t.status = ?' : '';
$params = $statusFilter !== 'all' ? [$statusFilter] : [];

$stmt = db()->prepare("
    SELECT t.*, u.username,
           (SELECT COUNT(*) FROM ticket_messages WHERE ticket_id = t.id) as msg_count
    FROM tickets t
    JOIN users u ON t.user_id = u.id
    $where
    ORDER BY t.updated_at DESC
");
$stmt->execute($params);
$tickets = $stmt->fetchAll();
?>

<div class="admin-topbar">
    <h2>Support Tickets</h2>
    <div style="display:flex;gap:8px">
        <a href="?section=tickets&status=open" class="btn btn-sm <?= $statusFilter === 'open' ? 'btn-primary' : 'btn-outline' ?>">Open</a>
        <a href="?section=tickets&status=closed" class="btn btn-sm <?= $statusFilter === 'closed' ? 'btn-primary' : 'btn-outline' ?>">Closed</a>
        <a href="?section=tickets&status=all" class="btn btn-sm <?= $statusFilter === 'all' ? 'btn-primary' : 'btn-outline' ?>">All</a>
    </div>
</div>

<?php if (empty($tickets)): ?>
    <div class="empty-state" style="margin-top:40px">
        <div class="empty-state-icon">🎫</div>
        <h3>No tickets</h3>
        <p>No <?= $statusFilter ?> tickets found.</p>
    </div>
<?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Subject</th>
                <th>User</th>
                <th>Messages</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tickets as $t): ?>
                <tr>
                    <td><a href="<?= APP_URL ?>/?page=ticket-view&id=<?= $t['id'] ?>" style="font-weight:600;color:var(--text-primary)"><?= e($t['subject']) ?></a></td>
                    <td><?= e($t['username']) ?></td>
                    <td><?= $t['msg_count'] ?></td>
                    <td>
                        <span class="tag" style="<?= $t['status'] === 'open' ? 'background:rgba(40,167,69,.15);color:#28a745' : 'background:rgba(108,117,125,.15);color:var(--text-muted)' ?>">
                            <?= e($t['status']) ?>
                        </span>
                    </td>
                    <td style="font-size:13px;color:var(--text-muted)"><?= timeAgo($t['created_at']) ?></td>
                    <td>
                        <div class="admin-actions">
                            <a href="<?= APP_URL ?>/?page=ticket-view&id=<?= $t['id'] ?>" class="btn btn-outline btn-sm">View</a>
                            <?php if ($t['status'] === 'open'): ?>
                                <form method="post" action="<?= APP_URL ?>/api/close_ticket.php" style="display:inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Close</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
