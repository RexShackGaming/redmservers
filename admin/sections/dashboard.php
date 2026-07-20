<?php
$stats = db()->query('SELECT
    (SELECT COUNT(*) FROM servers WHERE is_approved = 1) as approved,
    (SELECT COUNT(*) FROM servers WHERE is_approved = 0) as pending,
    (SELECT COUNT(*) FROM users) as users,
    (SELECT COUNT(*) FROM servers) as total
')->fetch();
?>
<div class="admin-topbar">
    <h1 style="font-size:24px;font-weight:700">Dashboard</h1>
    <span style="color:var(--text-secondary);font-size:14px">Welcome, <?= e($_SESSION['username']) ?></span>
</div>

<div class="admin-cards">
    <div class="admin-card">
        <div class="admin-card-value" style="color:var(--accent)"><?= $stats['total'] ?></div>
        <div class="admin-card-label">Total Servers</div>
    </div>
    <div class="admin-card">
        <div class="admin-card-value" style="color:var(--warning)"><?= $stats['pending'] ?></div>
        <div class="admin-card-label">Pending Approval</div>
    </div>
    <div class="admin-card">
        <div class="admin-card-value" style="color:var(--success)"><?= $stats['approved'] ?></div>
        <div class="admin-card-label">Approved Servers</div>
    </div>
    <div class="admin-card">
        <div class="admin-card-value" style="color:var(--info)"><?= $stats['users'] ?></div>
        <div class="admin-card-label">Registered Users</div>
    </div>
</div>

<?php if ((int)$stats['pending'] > 0): ?>
<div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:20px">
    <h3 style="margin-bottom:12px">Pending Servers</h3>
    <div class="alert alert-warning">There are <?= $stats['pending'] ?> server(s) waiting for approval. <a href="<?= APP_URL ?>/admin/?section=servers">Review now</a></div>
</div>
<?php endif; ?>
