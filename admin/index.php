<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$page = $_GET['section'] ?? 'dashboard';

$validSections = ['dashboard', 'servers', 'users', 'categories', 'tickets'];
if (!in_array($page, $validSections)) $page = 'dashboard';

$sectionFile = __DIR__ . '/sections/' . $page . '.php';
if (!file_exists($sectionFile)) $sectionFile = __DIR__ . '/sections/dashboard.php';

$pendingCount = db()->query('SELECT COUNT(*) FROM servers WHERE is_approved = 0')->fetchColumn();
$approvedCount = db()->query('SELECT COUNT(*) FROM servers WHERE is_approved = 1')->fetchColumn();
$totalUsers = db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalServers = db()->query('SELECT COUNT(*) FROM servers')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .admin-layout{display:flex;min-height:100vh}
        .admin-sidebar{width:240px;background:var(--bg-secondary);border-right:1px solid var(--border);padding:20px 0;position:sticky;top:0;height:100vh;overflow-y:auto;flex-shrink:0}
        .admin-sidebar-logo{padding:0 20px 20px;border-bottom:1px solid var(--border);margin-bottom:16px}
        .admin-nav a{display:flex;align-items:center;gap:10px;padding:10px 20px;color:var(--text-secondary);font-size:14px;font-weight:500;transition:var(--transition)}
        .admin-nav a:hover,.admin-nav a.active{background:rgba(233,69,96,.08);color:var(--accent);border-right:3px solid var(--accent)}
        .admin-content{flex:1;padding:32px;overflow-x:auto}
        .admin-topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px}
        .admin-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:32px}
        .admin-card{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:20px}
        .admin-card-value{font-size:28px;font-weight:800}
        .admin-card-label{font-size:13px;color:var(--text-secondary)}
        .admin-table{width:100%;border-collapse:collapse}
        .admin-table th,.admin-table td{padding:12px 16px;text-align:left;border-bottom:1px solid var(--border);font-size:14px}
        .admin-table th{color:var(--text-secondary);font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:.5px}
        .admin-table tr:hover{background:rgba(255,255,255,.02)}
        .admin-actions{display:flex;gap:6px}
        @media(max-width:768px){.admin-layout{flex-direction:column}.admin-sidebar{width:100%;height:auto;position:static}.admin-content{padding:16px}}
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="admin-sidebar-logo">
                <a href="<?= APP_URL ?>" class="logo-text" style="font-size:16px">RedM<span class="logo-accent">Servers</span></a>
                <div style="font-size:12px;color:var(--text-muted);margin-top:4px">Admin Panel</div>
            </div>
            <nav class="admin-nav">
                <a href="<?= APP_URL ?>/admin/" class="<?= $page === 'dashboard' ? 'active' : '' ?>">📊 Dashboard</a>
                <a href="<?= APP_URL ?>/admin/?section=servers" class="<?= $page === 'servers' ? 'active' : '' ?>">🖥️ Servers <?= $pendingCount > 0 ? '<span class="tag" style="font-size:11px;margin-left:auto">' . $pendingCount . '</span>' : '' ?></a>
                <a href="<?= APP_URL ?>/admin/?section=users" class="<?= $page === 'users' ? 'active' : '' ?>">👤 Users</a>
                <a href="<?= APP_URL ?>/admin/?section=categories" class="<?= $page === 'categories' ? 'active' : '' ?>">📁 Categories</a>
                <a href="<?= APP_URL ?>/admin/?section=tickets" class="<?= $page === 'tickets' ? 'active' : '' ?>">🎫 Tickets</a>
            </nav>
            <div style="padding:20px;border-top:1px solid var(--border);margin-top:auto">
                <a href="<?= APP_URL ?>/?page=dashboard" style="font-size:13px">← Back to Site</a>
            </div>
        </aside>
        <div class="admin-content">
            <?php include $sectionFile; ?>
        </div>
    </div>
</body>
</html>
