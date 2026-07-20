<?php
$categories = getCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $slug = strtolower(trim($_POST['slug'] ?? ''));
        $icon = trim($_POST['icon'] ?? '');

        if (strlen($name) < 2) {
            flash('error', 'Category name must be at least 2 characters.');
        } else {
            if ($slug === '') $slug = preg_replace('/[^a-z0-9-]/', '', strtolower(str_replace(' ', '-', $name)));
            $stmt = db()->prepare('INSERT INTO categories (name, slug, icon, sort_order) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $slug, $icon, (int)($_POST['sort_order'] ?? 0)]);
            flash('success', 'Category added.');
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            db()->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
            flash('success', 'Category deleted.');
        }
    } elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? '');
        if ($id > 0 && strlen($name) >= 2) {
            db()->prepare('UPDATE categories SET name = ?, icon = ?, sort_order = ? WHERE id = ?')
                ->execute([$name, $icon, (int)($_POST['sort_order'] ?? 0), $id]);
            flash('success', 'Category updated.');
        }
    }
    redirect(APP_URL . '/admin/?section=categories');
}
?>
<div class="admin-topbar">
    <h1 style="font-size:24px;font-weight:700">Manage Categories</h1>
</div>

<?= flashHtml('success', 'alert alert-success flash-auto-dismiss') ?>
<?= flashHtml('error', 'alert alert-error flash-auto-dismiss') ?>

<div style="display:grid;grid-template-columns:1fr 320px;gap:24px">
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Icon</th>
                    <th>Order</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:24px">No categories.</td></tr>
                <?php else: ?>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><?= $cat['id'] ?></td>
                            <td style="font-weight:600"><?= e($cat['name']) ?></td>
                            <td style="font-family:monospace"><?= e($cat['slug']) ?></td>
                            <td><?= e($cat['icon'] ?? '-') ?></td>
                            <td><?= $cat['sort_order'] ?></td>
                            <td>
                                <div class="admin-actions">
                                    <form method="post" class="delete-confirm" style="display:inline">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $cat['id'] ?>">
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

    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:20px;height:fit-content">
        <h3 style="margin-bottom:16px">Add Category</h3>
        <form method="post">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label class="form-label">Name *</label>
                <input type="text" name="name" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" class="form-input" placeholder="auto-generated if empty">
            </div>
            <div class="form-group">
                <label class="form-label">Icon</label>
                <input type="text" name="icon" class="form-input" placeholder="e.g. groups, swords">
            </div>
            <div class="form-group">
                <label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" class="form-input" value="0">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Add Category</button>
        </form>
    </div>
</div>
