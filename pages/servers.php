<?php
$pageTitle = 'Browse Servers';

$selectedCategory = $_GET['category'] ?? '';
$search = trim($_GET['search'] ?? '');
$sort = $_GET['sort'] ?? 'newest';
$pageNum = max(1, (int)($_GET['page'] ?? 1));

$where = 'WHERE s.is_approved = 1';
$params = [];

if ($selectedCategory !== '') {
    $where .= ' AND c.slug = ?';
    $params[] = $selectedCategory;
}
if ($search !== '') {
    $where .= ' AND (s.name LIKE ? OR s.description LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

$orderMatch = match ($sort) {
    'votes' => 'vote_count DESC, s.created_at DESC',
    'popular' => 's.views DESC',
    default => 's.created_at DESC',
};

$countStmt = db()->prepare("SELECT COUNT(*) FROM servers s LEFT JOIN categories c ON s.category_id = c.id $where");
$countStmt->execute($params);
$totalItems = (int)$countStmt->fetchColumn();

$pagination = paginate($totalItems, ITEMS_PER_PAGE, $pageNum);

$stmt = db()->prepare("
    SELECT s.*, c.name as category_name, c.slug as category_slug, u.username,
           (SELECT COALESCE(SUM(vote), 0) FROM server_votes WHERE server_id = s.id) as vote_count
    FROM servers s
    LEFT JOIN categories c ON s.category_id = c.id
    LEFT JOIN users u ON s.user_id = u.id
    $where
    ORDER BY s.is_featured DESC, $orderMatch
    LIMIT ? OFFSET ?
");
$allParams = array_merge($params, [$pagination['per_page'], $pagination['offset']]);
$stmt->execute($allParams);
$servers = $stmt->fetchAll();

$categories = getCategories();
$baseUrl = APP_URL . '/?page=servers';
if ($selectedCategory !== '') $baseUrl .= '&category=' . urlencode($selectedCategory);
if ($search !== '') $baseUrl .= '&search=' . urlencode($search);
if ($sort !== 'newest') $baseUrl .= '&sort=' . urlencode($sort);
?>

<section class="section">
    <div class="container">
        <div class="section-header">
            <div>
                <h2 class="section-title">Browse Servers</h2>
                <p class="section-subtitle"><?= number_format($totalItems) ?> server<?= $totalItems !== 1 ? 's' : '' ?> found</p>
            </div>
        </div>

        <form method="get" class="filter-bar">
            <input type="hidden" name="page" value="servers">
            <div class="search-box">
                <input type="text" name="search" placeholder="Search servers..." value="<?= e($search) ?>">
            </div>
            <select name="category" class="filter-select" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= e($cat['slug']) ?>" <?= $selectedCategory === $cat['slug'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="sort" class="filter-select" onchange="this.form.submit()">
                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
                <option value="votes" <?= $sort === 'votes' ? 'selected' : '' ?>>Most Voted</option>
                <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Most Popular</option>
            </select>
        </form>

        <?php if (empty($servers)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">🖥️</div>
                <h3>No servers found</h3>
                <p>No servers match your search criteria.</p>
            </div>
        <?php else: ?>
            <div class="server-grid">
                <?php foreach ($servers as $server): ?>
                    <?php include __DIR__ . '/partials/server_card.php'; ?>
                <?php endforeach; ?>
            </div>
            <?= renderPagination($pagination, APP_URL . '/?page=servers' . ($selectedCategory !== '' ? '&category=' . urlencode($selectedCategory) : '') . ($search !== '' ? '&search=' . urlencode($search) : '') . ($sort !== 'newest' ? '&sort=' . urlencode($sort) : '')) ?>
        <?php endif; ?>
    </div>
</section>
