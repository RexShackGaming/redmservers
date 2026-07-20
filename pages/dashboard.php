<?php
$pageTitle = 'My Servers';

$stmt = db()->prepare('
    SELECT s.*, c.name as category_name,
           (SELECT COALESCE(SUM(vote), 0) FROM server_votes WHERE server_id = s.id) as vote_count
    FROM servers s
    LEFT JOIN categories c ON s.category_id = c.id
    WHERE s.user_id = ?
    ORDER BY s.created_at DESC
');
$stmt->execute([$_SESSION['user_id']]);
$myServers = $stmt->fetchAll();

$totalViews = 0;
$totalVotes = 0;
$totalServers = count($myServers);
$approvedCount = 0;
foreach ($myServers as $s) {
    $totalViews += $s['views'];
    $totalVotes += $s['vote_count'];
    if ($s['is_approved']) $approvedCount++;
}
?>

<section class="section">
    <div class="container">
        <?= flashHtml('success', 'alert alert-success flash-auto-dismiss') ?>
        <?= flashHtml('error', 'alert alert-error flash-auto-dismiss') ?>

        <div class="dashboard-header">
            <h1 class="section-title">My Servers</h1>
            <a href="<?= APP_URL ?>/?page=server-add" class="btn btn-primary">+ Add Server</a>
        </div>

        <div class="dashboard-stats">
            <div class="dash-stat">
                <div class="dash-stat-value"><?= $totalServers ?></div>
                <div class="dash-stat-label">Total Servers</div>
            </div>
            <div class="dash-stat">
                <div class="dash-stat-value"><?= $approvedCount ?></div>
                <div class="dash-stat-label">Approved</div>
            </div>
            <div class="dash-stat">
                <div class="dash-stat-value"><?= number_format($totalViews) ?></div>
                <div class="dash-stat-label">Total Views</div>
            </div>
            <div class="dash-stat">
                <div class="dash-stat-value"><?= number_format($totalVotes) ?></div>
                <div class="dash-stat-label">Total Votes</div>
            </div>
        </div>

        <?php if (empty($myServers)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">🖥️</div>
                <h3>No servers yet</h3>
                <p>Add your first RedM server to get started.</p>
                <a href="<?= APP_URL ?>/?page=server-add" class="btn btn-primary" style="margin-top:16px">+ Add Server</a>
            </div>
        <?php else: ?>
            <?php foreach ($myServers as $server): ?>
                <div class="server-list-item">
                    <?php if ($server['banner_image']): ?>
                        <img src="<?= APP_URL ?>/uploads/banners/<?= e($server['banner_image']) ?>" class="server-list-thumb" alt="">
                    <?php else: ?>
                        <div class="server-list-thumb" style="display:flex;align-items:center;justify-content:center;font-size:24px">🎮</div>
                    <?php endif; ?>
                    <div class="server-list-info">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
                            <a href="<?= APP_URL ?>/?page=server-view&id=<?= $server['id'] ?>" style="font-weight:700;font-size:16px;color:var(--text-primary)"><?= e($server['name']) ?></a>
                            <span class="status-badge <?= $server['is_approved'] ? 'status-approved' : 'status-pending' ?>">
                                <?= $server['is_approved'] ? 'Approved' : 'Pending' ?>
                            </span>
                            <?php if ($server['is_featured']): ?>
                                <span class="tag" style="font-size:11px">Featured</span>
                            <?php endif; ?>
                        </div>
                        <div class="server-meta">
                            <span class="server-meta-item">🔗 cfx.re/join/<?= e($server['join_code']) ?></span>
                            <span class="server-meta-item">▲ <?= $server['vote_count'] ?></span>
                            <span class="server-meta-item">👁 <?= number_format($server['views']) ?></span>
                            <?php if ($server['category_name']): ?>
                                <span class="server-meta-item"><?= e($server['category_name']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="server-list-actions">
                        <a href="<?= APP_URL ?>/?page=server-edit&id=<?= $server['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                        <form method="post" action="<?= APP_URL ?>/api/delete_server.php" class="delete-confirm" style="display:inline">
                            <?= csrfField() ?>
                            <input type="hidden" name="server_id" value="<?= $server['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div style="margin-top:40px;border-top:1px solid var(--border);padding-top:32px">
            <h2 style="font-size:18px;font-weight:700;margin-bottom:16px">Account Settings</h2>
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px;max-width:450px">
                <h3 style="font-size:15px;font-weight:600;margin-bottom:16px">Change Password</h3>
                <form method="post" action="<?= APP_URL ?>/api/change_password.php">
                    <?= csrfField() ?>
                    <div class="form-group">
                        <label class="form-label" for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-input" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-input" required minlength="6">
                    </div>
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </form>
            </div>
        </div>
    </div>
</section>
