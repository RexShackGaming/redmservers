<?php
$pageNum = max(1, (int)($_GET['page'] ?? 1));
$totalUsers = (int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
$pagination = paginate($totalUsers, 20, $pageNum);
$resetLink = '';
$resetUserId = 0;

$users = db()->query("
    SELECT u.*, (SELECT COUNT(*) FROM servers WHERE user_id = u.id) as server_count
    FROM users u
    ORDER BY u.created_at DESC
    LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'toggle_admin' && $id > 0 && $id !== $_SESSION['user_id']) {
        $stmt = db()->prepare('SELECT role FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $u = $stmt->fetch();
        if ($u) {
            $newRole = $u['role'] === 'admin' ? 'user' : 'admin';
            db()->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$newRole, $id]);
            flash('success', 'User role updated to ' . $newRole . '.');
        }
    } elseif ($action === 'delete' && $id > 0 && $id !== $_SESSION['user_id']) {
        db()->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
        flash('success', 'User deleted.');
    } elseif ($action === 'reset_password' && $id > 0) {
        $token = bin2hex(random_bytes(32));
        $stmt = db()->prepare('SELECT username FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $u = $stmt->fetch();
        if ($u) {
            db()->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))')
                ->execute([$id, $token]);
            $resetLink = APP_URL . '/?page=reset-password&token=' . urlencode($token);
            $resetUserId = $id;
        }
    }
    if ($action !== 'reset_password') {
        redirect(APP_URL . '/admin/?section=users');
    }
}
?>
<div class="admin-topbar">
    <h1 style="font-size:24px;font-weight:700">Manage Users</h1>
</div>

<?= flashHtml('success', 'alert alert-success flash-auto-dismiss') ?>

<div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Servers</th>
                <th>Joined</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td style="font-weight:600"><?= e($u['username']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td>
                        <span class="status-badge <?= $u['role'] === 'admin' ? 'status-approved' : '' ?>">
                            <?= e(ucfirst($u['role'])) ?>
                        </span>
                    </td>
                    <td><?= $u['server_count'] ?></td>
                    <td><?= timeAgo($u['created_at']) ?></td>
                    <td>
                        <div class="admin-actions">
                            <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                <form method="post" style="display:inline">
                                    <input type="hidden" name="action" value="reset_password">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <button class="btn btn-outline btn-sm">Reset PW</button>
                                </form>
                                <form method="post" style="display:inline">
                                    <input type="hidden" name="action" value="toggle_admin">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <button class="btn btn-outline btn-sm"><?= $u['role'] === 'admin' ? 'Remove Admin' : 'Make Admin' ?></button>
                                </form>
                                <form method="post" style="display:inline" class="delete-confirm">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <button class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            <?php else: ?>
                                <span style="color:var(--text-muted);font-size:13px">You</span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($resetLink) && $u['id'] === $resetUserId): ?>
                            <div style="margin-top:8px;padding:8px;background:var(--bg-secondary);border-radius:var(--radius);font-size:12px;word-break:break-all">
                                <strong>Reset link (share with user):</strong><br>
                                <a href="<?= e($resetLink) ?>"><?= e($resetLink) ?></a>
                                <button onclick="navigator.clipboard.writeText('<?= e($resetLink) ?>');this.textContent='Copied!';setTimeout(function(){this.textContent='Copy'}.bind(this),2000)" style="margin-top:4px;padding:2px 8px;font-size:11px;border:1px solid var(--border);border-radius:var(--radius);background:var(--bg-card);color:var(--text-secondary);cursor:pointer">Copy</button>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
