<?php
requireLogin();
$pageTitle = 'Edit Server';
$categories = getCategories();
$errors = [];

$serverId = (int)($_GET['id'] ?? 0);
if ($serverId <= 0) redirect(APP_URL . '/?page=dashboard');

$stmt = db()->prepare('SELECT * FROM servers WHERE id = ?');
$stmt->execute([$serverId]);
$server = $stmt->fetch();

if (!$server || ($server['user_id'] != $_SESSION['user_id'] && !isAdmin())) {
    flash('error', 'Server not found.');
    redirect(APP_URL . '/?page=dashboard');
}

$screenshots = db()->prepare('SELECT * FROM server_screenshots WHERE server_id = ? ORDER BY sort_order ASC');
$screenshots->execute([$serverId]);
$screenshots = $screenshots->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        $errors[] = 'Invalid request.';
    } else {
        $name          = trim($_POST['name'] ?? '');
        $join_code     = trim($_POST['join_code'] ?? '');
        $description   = trim($_POST['description'] ?? '');
        $player_count  = (int)($_POST['player_count_max'] ?? 64);
        $category_id   = (int)($_POST['category_id'] ?? 0) ?: null;
        $tags          = trim($_POST['tags'] ?? '');
        $discord_invite = trim($_POST['discord_invite'] ?? '');

        if (strlen($name) < 3 || strlen($name) > 150) $errors[] = 'Server name must be 3-150 characters.';
        if (strlen($join_code) < 2 || strlen($join_code) > 50) $errors[] = 'Invalid join code.';
        if (strlen($description) < 10) $errors[] = 'Description must be at least 10 characters.';
        if ($player_count < 1 || $player_count > 1024) $errors[] = 'Player count must be between 1 and 1024.';

        $bannerName = $server['banner_image'];
        $bannerFile = $_FILES['banner_image'] ?? null;
        if ($bannerFile && $bannerFile['error'] === UPLOAD_ERR_OK) {
            $newBanner = uploadImage($bannerFile, BANNER_DIR, BANNER_MAX_SIZE, ALLOWED_IMAGE_TYPES);
            if ($newBanner) {
                if ($bannerName && file_exists(BANNER_DIR . $bannerName)) unlink(BANNER_DIR . $bannerName);
                $bannerName = $newBanner;
            } else {
                $errors[] = 'Banner must be a valid image (JPEG, PNG, GIF, WebP) under 2MB.';
            }
        }

        if (empty($errors)) {
            $stmt = db()->prepare('
                UPDATE servers SET name=?, join_code=?, description=?, banner_image=?,
                       player_count_max=?, category_id=?, tags=?, discord_invite=?, updated_at=NOW()
                WHERE id=?
            ');
            $stmt->execute([$name, $join_code, $description, $bannerName, $player_count, $category_id, $tags, $discord_invite, $serverId]);

            $uploads = $_FILES['screenshots'] ?? [];
            if (!empty($uploads['name'][0])) {
                $existingCount = count($screenshots ?? []);
                $count = min(count($uploads['name']), MAX_SCREENSHOTS - $existingCount);
                for ($i = 0; $i < $count; $i++) {
                    $file = [
                        'name'     => $uploads['name'][$i],
                        'type'     => $uploads['type'][$i],
                        'tmp_name' => $uploads['tmp_name'][$i],
                        'error'    => $uploads['error'][$i],
                        'size'     => $uploads['size'][$i],
                    ];
                    $ssName = uploadImage($file, SCREENSHOT_DIR, SCREENSHOT_MAX_SIZE, ALLOWED_IMAGE_TYPES);
                    if ($ssName) {
                        $ssStmt = db()->prepare('INSERT INTO server_screenshots (server_id, image_path, sort_order) VALUES (?, ?, ?)');
                        $ssStmt->execute([$serverId, $ssName, $existingCount + $i]);
                    }
                }
            }

            flash('success', 'Server updated successfully.');
            redirect(APP_URL . '/?page=dashboard');
        }
    }
}
?>

<section class="section">
    <div class="container" style="max-width:700px">
        <h1 class="section-title" style="margin-bottom:24px">Edit Server</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $err): ?>
                    <div><?= e($err) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!$server['is_approved']): ?>
            <div class="alert alert-warning">This server is pending approval and is not yet visible to the public.</div>
        <?php endif; ?>

        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px">
            <form method="post" enctype="multipart/form-data">
                <?= csrfField() ?>

                <div class="form-group">
                    <label class="form-label" for="name">Server Name *</label>
                    <input type="text" id="name" name="name" class="form-input" required maxlength="150" value="<?= e($_POST['name'] ?? $server['name']) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="join_code">CFX Join Code *</label>
                    <input type="text" id="join_code" name="join_code" class="form-input" required placeholder="e.g. 6jgov4" value="<?= e($_POST['join_code'] ?? $server['join_code']) ?>">
                    <div class="form-help">The code from your cfx.re/join link. Players join via cfx.re/join/<?= e($server['join_code']) ?></div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Description *</label>
                    <textarea id="description" name="description" class="form-textarea" required minlength="10"><?= e($_POST['description'] ?? $server['description']) ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="player_count_max">Max Players *</label>
                        <input type="number" id="player_count_max" name="player_count_max" class="form-input" required min="1" max="1024" value="<?= e($_POST['player_count_max'] ?? $server['player_count_max']) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="category_id">Category</label>
                        <select id="category_id" name="category_id" class="form-select">
                            <option value="0">-- Select --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ((int)($_POST['category_id'] ?? $server['category_id'] ?? 0) === (int)$cat['id']) ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="tags">Tags</label>
                    <input type="text" id="tags" name="tags" class="form-input" placeholder="e.g. roleplay, economy, friendly" value="<?= e($_POST['tags'] ?? $server['tags']) ?>">
                    <div class="form-help">Comma-separated tags to help players find your server.</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="discord_invite">Discord Invite (optional)</label>
                    <input type="text" id="discord_invite" name="discord_invite" class="form-input" placeholder="https://discord.gg/your-server" value="<?= e($_POST['discord_invite'] ?? $server['discord_invite'] ?? '') ?>">
                    <div class="form-help">Link to your Discord community so players can connect with you.</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Current Banner</label>
                    <?php if ($server['banner_image']): ?>
                        <img src="<?= APP_URL ?>/uploads/banners/<?= e($server['banner_image']) ?>" style="max-height:150px;border-radius:var(--radius);margin-bottom:8px">
                    <?php else: ?>
                        <p style="color:var(--text-muted);font-size:14px">No banner uploaded.</p>
                    <?php endif; ?>
                    <input type="file" name="banner_image" class="form-input" accept="image/jpeg,image/png,image/gif,image/webp" style="margin-top:4px">
                </div>

                <?php if (!empty($screenshots)): ?>
                    <div class="form-group">
                        <label class="form-label">Current Screenshots</label>
                        <div class="screenshots-grid">
                            <?php foreach ($screenshots as $ss): ?>
                                <div style="position:relative">
                                    <img src="<?= APP_URL ?>/uploads/screenshots/<?= e($ss['image_path']) ?>" style="width:100%;aspect-ratio:16/9;object-fit:cover;border-radius:var(--radius)">
                                    <a href="<?= APP_URL ?>/api/delete_screenshot.php?id=<?= $ss['id'] ?>&server_id=<?= $serverId ?>" class="btn btn-danger btn-sm" style="position:absolute;top:4px;right:4px;padding:2px 8px;font-size:11px" onclick="return confirm('Delete screenshot?')">✕</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label">Add More Screenshots</label>
                    <input type="file" name="screenshots[]" class="form-input" multiple accept="image/jpeg,image/png,image/gif,image/webp">
                    <div class="form-help">Up to <?= MAX_SCREENSHOTS ?> total. Max 5MB each.</div>
                </div>

                <div style="display:flex;gap:12px;margin-top:24px">
                    <button type="submit" class="btn btn-primary btn-lg">Save Changes</button>
                    <a href="<?= APP_URL ?>/?page=dashboard" class="btn btn-outline btn-lg">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</section>
