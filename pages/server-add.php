<?php
requireLogin();
$pageTitle = 'Add Server';
$categories = getCategories();
$errors = [];

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

        $bannerFile = $_FILES['banner_image'] ?? null;
        $bannerName = null;
        if ($bannerFile && $bannerFile['error'] === UPLOAD_ERR_OK) {
            $bannerName = uploadImage($bannerFile, BANNER_DIR, BANNER_MAX_SIZE, ALLOWED_IMAGE_TYPES);
            if (!$bannerName) $errors[] = 'Banner must be a valid image (JPEG, PNG, GIF, WebP) under 2MB.';
        }

        if (empty($errors)) {
            $stmt = db()->prepare('
                INSERT INTO servers (user_id, name, join_code, description, banner_image, player_count_max, category_id, tags, discord_invite)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $_SESSION['user_id'], $name, $join_code, $description,
                $bannerName, $player_count, $category_id, $tags, $discord_invite
            ]);
            $serverId = db()->lastInsertId();

            $screenshots = $_FILES['screenshots'] ?? [];
            if (!empty($screenshots['name'][0])) {
                $count = min(count($screenshots['name']), MAX_SCREENSHOTS);
                for ($i = 0; $i < $count; $i++) {
                    $file = [
                        'name'     => $screenshots['name'][$i],
                        'type'     => $screenshots['type'][$i],
                        'tmp_name' => $screenshots['tmp_name'][$i],
                        'error'    => $screenshots['error'][$i],
                        'size'     => $screenshots['size'][$i],
                    ];
                    $ssName = uploadImage($file, SCREENSHOT_DIR, SCREENSHOT_MAX_SIZE, ALLOWED_IMAGE_TYPES);
                    if ($ssName) {
                        $ssStmt = db()->prepare('INSERT INTO server_screenshots (server_id, image_path, sort_order) VALUES (?, ?, ?)');
                        $ssStmt->execute([$serverId, $ssName, $i]);
                    }
                }
            }

            flash('success', 'Server added! It will appear once approved by an admin.');
            redirect(APP_URL . '/?page=dashboard');
        }
    }
}
?>

<section class="section">
    <div class="container" style="max-width:700px">
        <h1 class="section-title" style="margin-bottom:24px">Add Server</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $err): ?>
                    <div><?= e($err) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:24px">
            <form method="post" enctype="multipart/form-data">
                <?= csrfField() ?>

                <div class="form-group">
                    <label class="form-label" for="name">Server Name *</label>
                    <input type="text" id="name" name="name" class="form-input" required maxlength="150" value="<?= e($_POST['name'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="join_code">CFX Join Code *</label>
                    <input type="text" id="join_code" name="join_code" class="form-input" required placeholder="e.g. 6jgov4" value="<?= e($_POST['join_code'] ?? '') ?>">
                    <div class="form-help">The code from your cfx.re/join link. Players will be able to join via cfx.re/join/<?= e($_POST['join_code'] ?? 'CODE') ?></div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Description *</label>
                    <textarea id="description" name="description" class="form-textarea" required minlength="10"><?= e($_POST['description'] ?? '') ?></textarea>
                    <div class="form-help">Tell players what makes your server special. Minimum 10 characters.</div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="player_count_max">Max Players *</label>
                        <input type="number" id="player_count_max" name="player_count_max" class="form-input" required min="1" max="1024" value="<?= e($_POST['player_count_max'] ?? '64') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="category_id">Category</label>
                        <select id="category_id" name="category_id" class="form-select">
                            <option value="0">-- Select --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ((int)($_POST['category_id'] ?? 0) === (int)$cat['id']) ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="tags">Tags</label>
                    <input type="text" id="tags" name="tags" class="form-input" placeholder="e.g. roleplay, economy, friendly" value="<?= e($_POST['tags'] ?? '') ?>">
                    <div class="form-help">Comma-separated tags to help players find your server.</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="discord_invite">Discord Invite (optional)</label>
                    <input type="text" id="discord_invite" name="discord_invite" class="form-input" placeholder="https://discord.gg/your-server" value="<?= e($_POST['discord_invite'] ?? '') ?>">
                    <div class="form-help">Link to your Discord community so players can connect with you.</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="banner_image">Banner Image (optional)</label>
                    <input type="file" id="banner_image" name="banner_image" class="form-input" accept="image/jpeg,image/png,image/gif,image/webp">
                    <div class="form-help">Recommended: 1200x400px. Max 2MB. JPEG, PNG, GIF, or WebP.</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="screenshots">Screenshots (optional)</label>
                    <input type="file" id="screenshots" name="screenshots[]" class="form-input" multiple accept="image/jpeg,image/png,image/gif,image/webp">
                    <div class="form-help">Up to <?= MAX_SCREENSHOTS ?> screenshots. Max 5MB each.</div>
                </div>

                <div style="display:flex;gap:12px;margin-top:24px">
                    <button type="submit" class="btn btn-primary btn-lg">Add Server</button>
                    <a href="<?= APP_URL ?>/?page=dashboard" class="btn btn-outline btn-lg">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</section>
