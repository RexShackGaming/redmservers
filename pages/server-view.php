<?php
$serverId = (int)($_GET['id'] ?? 0);
if ($serverId <= 0) redirect(APP_URL . '/?page=servers');

$stmt = db()->prepare('
    SELECT s.*, c.name as category_name, c.slug as category_slug, u.username, u.id as owner_id
    FROM servers s
    LEFT JOIN categories c ON s.category_id = c.id
    LEFT JOIN users u ON s.user_id = u.id
    WHERE s.id = ?
');
$stmt->execute([$serverId]);
$server = $stmt->fetch();

if (!$server) redirect(APP_URL . '/?page=servers');

incrementViews($serverId);
$pageTitle = e($server['name']) . ' - RedM Server';

$voteCount = getVoteCount($serverId);
$userVote = null;
if (isLoggedIn()) {
    $userVote = getUserVote($serverId, $_SESSION['user_id']);
}

$screenshots = db()->prepare('SELECT * FROM server_screenshots WHERE server_id = ? ORDER BY sort_order ASC');
$screenshots->execute([$serverId]);
$screenshots = $screenshots->fetchAll();

$joinUrl = 'https://cfx.re/join/' . e($server['join_code']);
?>

<section class="server-detail">
    <div class="container">
        <?php if ($server['banner_image']): ?>
            <img src="<?= APP_URL ?>/uploads/banners/<?= e($server['banner_image']) ?>" alt="<?= e($server['name']) ?>" class="server-detail-banner" id="server-banner-<?= $serverId ?>">
        <?php else: ?>
            <div class="server-detail-banner" style="display:flex;align-items:center;justify-content:center;font-size:48px;color:var(--text-muted);background:linear-gradient(135deg,var(--bg-secondary),#1a1040)" id="server-banner-<?= $serverId ?>">🎮</div>
        <?php endif; ?>

        <div class="server-detail-header">
            <div>
                <h1 class="server-detail-title"><?= e($server['name']) ?></h1>
                <div class="server-meta" style="margin-top:8px">
                    <?php if ($server['category_name']): ?>
                        <span class="tag tag-category"><?= e($server['category_name']) ?></span>
                    <?php endif; ?>
                    <span class="server-meta-item">👤 Listed by <?= e($server['username']) ?></span>
                    <span class="server-meta-item">🕐 <?= timeAgo($server['created_at']) ?></span>
                </div>
            </div>
            <div style="display:flex;gap:12px;align-items:center">
                <button class="vote-btn <?= $userVote ? 'voted' : '' ?>" data-server-id="<?= $serverId ?>">
                    ▲ <span class="vote-count" data-server-id="<?= $serverId ?>"><?= $voteCount ?></span>
                </button>
            </div>
        </div>

        <div class="server-detail-body">
            <div class="server-detail-content">
                <h3 style="margin-bottom:12px">About This Server</h3>
                <div style="color:var(--text-secondary);line-height:1.8;white-space:pre-line"><?= nl2br(e($server['description'])) ?></div>

                <?php if (!empty($server['tags'])): ?>
                    <div class="server-tags" style="margin-top:16px">
                        <?php foreach (explode(',', $server['tags']) as $tag): ?>
                            <span class="tag"><?= e(trim($tag)) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($screenshots)): ?>
                    <h3 style="margin-top:24px;margin-bottom:12px">Screenshots</h3>
                    <div class="screenshots-grid">
                        <?php foreach ($screenshots as $ss): ?>
                            <div class="screenshot-thumb">
                                <img src="<?= APP_URL ?>/uploads/screenshots/<?= e($ss['image_path']) ?>" alt="Screenshot">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="server-detail-sidebar">
                <div class="sidebar-card server-status-card" data-server-id="<?= $serverId ?>" data-join-code="<?= e($server['join_code']) ?>">
                    <h3>Server Status</h3>
                    <div class="status-loading" style="text-align:center;padding:16px;color:var(--text-muted)">Checking...</div>
                    <div class="status-result" style="display:none">
                        <div class="status-badge-row">
                            <span class="status-indicator" id="status-dot"></span>
                            <span id="status-text" style="font-weight:600"></span>
                        </div>
                        <div class="sidebar-stat">
                            <span class="sidebar-stat-label">Players</span>
                            <span class="sidebar-stat-value" id="status-players">-</span>
                        </div>
                        <div class="sidebar-stat">
                            <span class="sidebar-stat-label">Whitelist</span>
                            <span class="sidebar-stat-value"><i id="status-allowlisted"></i></span>
                        </div>
                    </div>
                    <div class="status-error" style="display:none;text-align:center;padding:16px;color:var(--text-muted)">Could not reach server</div>
                </div>

                <div class="sidebar-card">
                    <h3>Join This Server</h3>
                    <div class="copy-ip" data-ip="<?= $joinUrl ?>" style="cursor:pointer">
                        <div class="cfx-join-link">
                            cfx.re/join/<strong><?= e($server['join_code']) ?></strong>
                            <span class="copy-tooltip" style="margin-left:8px">Copied!</span>
                        </div>
                        <div style="font-size:12px;color:var(--text-muted);margin-top:4px">Click to copy link</div>
                    </div>
                    <a href="<?= $joinUrl ?>" target="_blank" rel="noopener" class="btn btn-primary btn-block" style="margin-top:12px">Join Server</a>
                </div>

                <?php if (!empty($server['discord_invite'])): ?>
                <div class="sidebar-card">
                    <h3>Community</h3>
                    <a href="<?= e($server['discord_invite']) ?>" target="_blank" rel="noopener" class="btn btn-outline btn-block" style="display:flex;align-items:center;justify-content:center;gap:8px">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/></svg>
                        Join Discord
                    </a>
                </div>
                <?php endif; ?>

                <div class="sidebar-card">
                    <h3>Info</h3>
                    <div class="sidebar-stat">
                        <span class="sidebar-stat-label">Max Players</span>
                        <span class="sidebar-stat-value"><?= number_format($server['player_count_max']) ?></span>
                    </div>
                    <div class="sidebar-stat">
                        <span class="sidebar-stat-label">Votes</span>
                        <span class="sidebar-stat-value"><?= number_format($voteCount) ?></span>
                    </div>
                    <div class="sidebar-stat">
                        <span class="sidebar-stat-label">Views</span>
                        <span class="sidebar-stat-value"><?= number_format($server['views']) ?></span>
                    </div>
                    <?php if ($server['category_name']): ?>
                    <div class="sidebar-stat">
                        <span class="sidebar-stat-label">Category</span>
                        <span class="sidebar-stat-value"><?= e($server['category_name']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="sidebar-stat">
                        <span class="sidebar-stat-label">Listed</span>
                        <span class="sidebar-stat-value"><?= timeAgo($server['created_at']) ?></span>
                    </div>
                </div>

                <?php if (isLoggedIn() && ($_SESSION['user_id'] == $server['owner_id'] || isAdmin())): ?>
                <div class="sidebar-card">
                    <h3>Manage</h3>
                    <a href="<?= APP_URL ?>/?page=server-edit&id=<?= $serverId ?>" class="btn btn-outline btn-block" style="margin-bottom:8px">Edit Server</a>
                    <form method="post" action="<?= APP_URL ?>/api/delete_server.php" class="delete-confirm">
                        <?= csrfField() ?>
                        <input type="hidden" name="server_id" value="<?= $serverId ?>">
                        <button type="submit" class="btn btn-danger btn-block">Delete Server</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<div class="lightbox">
    <img src="" alt="Screenshot">
</div>
