<?php
$filter = $_GET['filter'] ?? 'pending';
$pageNum = max(1, (int)($_GET['page'] ?? 1));

$where = $filter === 'all' ? '' : 'WHERE s.is_approved = ' . ($filter === 'pending' ? '0' : '1');
$order = $filter === 'pending' ? 's.created_at ASC' : 's.created_at DESC';

$countStmt = db()->query("SELECT COUNT(*) FROM servers s $where");
$totalItems = (int)$countStmt->fetchColumn();
$pagination = paginate($totalItems, 20, $pageNum);

$stmt = db()->query("
    SELECT s.*, u.username, c.name as category_name
    FROM servers s
    LEFT JOIN users u ON s.user_id = u.id
    LEFT JOIN categories c ON s.category_id = c.id
    $where
    ORDER BY $order
    LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}
");
$servers = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'approve' && $id > 0) {
        db()->prepare('UPDATE servers SET is_approved = 1 WHERE id = ?')->execute([$id]);
        flash('success', 'Server approved.');
    } elseif ($action === 'unapprove' && $id > 0) {
        db()->prepare('UPDATE servers SET is_approved = 0 WHERE id = ?')->execute([$id]);
        flash('success', 'Server unapproved.');
    } elseif ($action === 'feature' && $id > 0) {
        db()->prepare('UPDATE servers SET is_featured = 1 WHERE id = ?')->execute([$id]);
        flash('success', 'Server featured.');
    } elseif ($action === 'unfeature' && $id > 0) {
        db()->prepare('UPDATE servers SET is_featured = 0 WHERE id = ?')->execute([$id]);
        flash('success', 'Server unfeatured.');
    } elseif ($action === 'delete' && $id > 0) {
        $stmt = db()->prepare('SELECT banner_image FROM servers WHERE id = ?');
        $stmt->execute([$id]);
        $s = $stmt->fetch();
        if ($s && $s['banner_image'] && file_exists(BANNER_DIR . $s['banner_image'])) {
            unlink(BANNER_DIR . $s['banner_image']);
        }
        db()->prepare('DELETE FROM servers WHERE id = ?')->execute([$id]);
        flash('success', 'Server deleted.');
    }
    redirect(APP_URL . '/admin/?section=servers&filter=' . $filter);
}

require_once __DIR__ . '/../../includes/functions.php';
?>
<div class="admin-topbar">
    <h1 style="font-size:24px;font-weight:700">Manage Servers</h1>
</div>

<?= flashHtml('success', 'alert alert-success flash-auto-dismiss') ?>

<div style="display:flex;gap:8px;margin-bottom:20px">
    <a href="<?= APP_URL ?>/admin/?section=servers&filter=pending" class="btn <?= $filter === 'pending' ? 'btn-primary' : 'btn-outline' ?> btn-sm">Pending (<?= db()->query('SELECT COUNT(*) FROM servers WHERE is_approved=0')->fetchColumn() ?>)</a>
    <a href="<?= APP_URL ?>/admin/?section=servers&filter=approved" class="btn <?= $filter === 'approved' ? 'btn-primary' : 'btn-outline' ?> btn-sm">Approved</a>
    <a href="<?= APP_URL ?>/admin/?section=servers&filter=all" class="btn <?= $filter === 'all' ? 'btn-primary' : 'btn-outline' ?> btn-sm">All</a>
</div>

<div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Server</th>
                <th>Owner</th>
                <th>IP:Port</th>
                <th>Category</th>
                <th>Status</th>
                <th>Featured</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($servers)): ?>
                <tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:32px">No servers found.</td></tr>
            <?php else: ?>
                <?php foreach ($servers as $s): ?>
                    <tr>
                        <td><?= $s['id'] ?></td>
                        <td><a href="<?= APP_URL ?>/?page=server-view&id=<?= $s['id'] ?>" style="font-weight:600" target="_blank"><?= e($s['name']) ?></a></td>
                        <td><?= e($s['username']) ?></td>
                        <td style="font-family:monospace">cfx.re/join/<?= e($s['join_code']) ?></td>
                        <td><?= e($s['category_name'] ?? '-') ?></td>
                        <td>
                            <span class="status-badge <?= $s['is_approved'] ? 'status-approved' : 'status-pending' ?>">
                                <?= $s['is_approved'] ? 'Approved' : 'Pending' ?>
                            </span>
                        </td>
                        <td><?= $s['is_featured'] ? '★' : '-' ?></td>
                        <td>
                            <div class="admin-actions">
                                <?php if (!$s['is_approved']): ?>
                                    <form method="post" style="display:inline">
                                        <input type="hidden" name="action" value="approve">
                                        <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                        <button class="btn btn-success btn-sm">Approve</button>
                                    </form>
                                <?php else: ?>
                                    <form method="post" style="display:inline">
                                        <input type="hidden" name="action" value="unapprove">
                                        <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                        <button class="btn btn-outline btn-sm">Unapprove</button>
                                    </form>
                                <?php endif; ?>
                                <?php if (!$s['is_featured']): ?>
                                    <form method="post" style="display:inline">
                                        <input type="hidden" name="action" value="feature">
                                        <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                        <button class="btn btn-secondary btn-sm">Feature</button>
                                    </form>
                                <?php else: ?>
                                    <form method="post" style="display:inline">
                                        <input type="hidden" name="action" value="unfeature">
                                        <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                        <button class="btn btn-outline btn-sm">Unfeature</button>
                                    </form>
                                <?php endif; ?>
                                <form method="post" style="display:inline" class="delete-confirm">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                    <button class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
