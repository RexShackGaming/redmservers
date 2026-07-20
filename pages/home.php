<?php
$pageTitle = 'Home - List Your RedM Server';

$stats = db()->query('SELECT
    (SELECT COUNT(*) FROM servers WHERE is_approved = 1) as total_servers,
    (SELECT COALESCE(SUM(player_count_max), 0) FROM servers WHERE is_approved = 1) as total_slots,
    (SELECT COUNT(*) FROM users) as total_users
')->fetch();

$featuredServers = db()->query('
    SELECT s.*, c.name as category_name, c.slug as category_slug, u.username,
           (SELECT COALESCE(SUM(vote), 0) FROM server_votes WHERE server_id = s.id) as vote_count
    FROM servers s
    LEFT JOIN categories c ON s.category_id = c.id
    LEFT JOIN users u ON s.user_id = u.id
    WHERE s.is_approved = 1 AND s.is_featured = 1
    ORDER BY s.created_at DESC LIMIT 6
')->fetchAll();

$latestServers = db()->query('
    SELECT s.*, c.name as category_name, u.username,
           (SELECT COALESCE(SUM(vote), 0) FROM server_votes WHERE server_id = s.id) as vote_count
    FROM servers s
    LEFT JOIN categories c ON s.category_id = c.id
    LEFT JOIN users u ON s.user_id = u.id
    WHERE s.is_approved = 1
    ORDER BY s.created_at DESC LIMIT 6
')->fetchAll();
?>

<section class="hero">
    <div class="container hero-content">
        <h1>List Your RedM Server</h1>
        <p>The community hub for RedM server owners. List your server, get discovered, and grow your player base.</p>
        <div class="hero-actions">
            <a href="<?= APP_URL ?>/?page=servers" class="btn btn-lg btn-outline">Browse Servers</a>
            <a href="<?= APP_URL ?>/?page=signup" class="btn btn-lg btn-primary">List Your Server</a>
        </div>
    </div>
</section>

<section class="stats-bar">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-value"><?= number_format($stats['total_servers']) ?></div>
                <div class="stat-label">Servers Listed</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?= number_format($stats['total_slots']) ?></div>
                <div class="stat-label">Total Player Slots</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?= number_format($stats['total_users']) ?></div>
                <div class="stat-label">Registered Users</div>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($featuredServers)): ?>
<section class="section">
    <div class="container">
        <div class="section-header">
            <div>
                <h2 class="section-title">Featured Servers</h2>
                <p class="section-subtitle">Hand-picked quality servers</p>
            </div>
        </div>
        <div class="server-grid">
            <?php foreach ($featuredServers as $server): ?>
                <?php include __DIR__ . '/partials/server_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section" style="background:var(--bg-secondary)">
    <div class="container">
        <div class="section-header">
            <div>
                <h2 class="section-title">How It Works</h2>
                <p class="section-subtitle">Get your server listed in 3 simple steps</p>
            </div>
        </div>
        <div class="how-grid">
            <div class="how-card">
                <div class="how-icon">1</div>
                <h3>Create Account</h3>
                <p>Sign up for a free account in seconds. No email verification required.</p>
            </div>
            <div class="how-card">
                <div class="how-icon">2</div>
                <h3>Add Your Server</h3>
                <p>Enter your server details, add a banner, screenshots, and description.</p>
            </div>
            <div class="how-card">
                <div class="how-icon">3</div>
                <h3>Get Discovered</h3>
                <p>Your server is listed for the community to find. Get votes and grow!</p>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($latestServers)): ?>
<section class="section">
    <div class="container">
        <div class="section-header">
            <div>
                <h2 class="section-title">Latest Servers</h2>
                <p class="section-subtitle">Recently added servers</p>
            </div>
            <a href="<?= APP_URL ?>/?page=servers" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="server-grid">
            <?php foreach ($latestServers as $server): ?>
                <?php include __DIR__ . '/partials/server_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
