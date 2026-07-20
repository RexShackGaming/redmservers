<?php
function e(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function sanitize($input)
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

function verifyCsrf(): bool
{
    return isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token']);
}

function uploadImage(array $file, string $destination, int $maxSize, array $allowedTypes): ?string
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    if ($file['size'] > $maxSize) {
        return null;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowedTypes, true)) {
        return null;
    }

    $image = match ($mime) {
        'image/jpeg' => @imagecreatefromjpeg($file['tmp_name']),
        'image/png'  => @imagecreatefrompng($file['tmp_name']),
        'image/gif'  => @imagecreatefromgif($file['tmp_name']),
        'image/webp' => @imagecreatefromwebp($file['tmp_name']),
        default      => null,
    };

    if ($image === null) {
        return null;
    }

    $filename = uniqid('img_', true) . '.webp';
    $target   = $destination . $filename;

    imagepalettetotruecolor($image);
    imagealphablending($image, true);
    imagesavealpha($image, true);

    if (imagewebp($image, $target, 85)) {
        imagedestroy($image);
        return $filename;
    }

    imagedestroy($image);
    return null;
}

function paginate(int $totalItems, int $perPage, int $currentPage): array
{
    $totalPages = max(1, (int)ceil($totalItems / $perPage));
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;

    return [
        'total_items'  => $totalItems,
        'per_page'     => $perPage,
        'current_page' => $currentPage,
        'total_pages'  => $totalPages,
        'offset'       => $offset,
    ];
}

function renderPagination(array $pagination, string $baseUrl): string
{
    if ($pagination['total_pages'] <= 1) return '';

    $html = '<div class="pagination">';

    if ($pagination['current_page'] > 1) {
        $html .= '<a href="' . $baseUrl . '?page=' . ($pagination['current_page'] - 1) . '" class="page-btn">&laquo; Prev</a>';
    }

    for ($i = 1; $i <= $pagination['total_pages']; $i++) {
        $active = $i === $pagination['current_page'] ? ' active' : '';
        $html .= '<a href="' . $baseUrl . '?page=' . $i . '" class="page-btn' . $active . '">' . $i . '</a>';
    }

    if ($pagination['current_page'] < $pagination['total_pages']) {
        $html .= '<a href="' . $baseUrl . '?page=' . ($pagination['current_page'] + 1) . '" class="page-btn">Next &raquo;</a>';
    }

    $html .= '</div>';
    return $html;
}

function timeAgo(string $datetime): string
{
    $now  = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->diff($past);

    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}

function getVoteCount(int $serverId): int
{
    $stmt = db()->prepare('SELECT COALESCE(SUM(vote), 0) as total FROM server_votes WHERE server_id = ?');
    $stmt->execute([$serverId]);
    return (int)$stmt->fetchColumn();
}

function getUserVote(int $serverId, int $userId): ?int
{
    $stmt = db()->prepare('SELECT vote FROM server_votes WHERE server_id = ? AND user_id = ? LIMIT 1');
    $stmt->execute([$serverId, $userId]);
    $result = $stmt->fetch();
    return $result ? (int)$result['vote'] : null;
}

function incrementViews(int $serverId): void
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $stmt = db()->prepare('INSERT IGNORE INTO server_views (server_id, ip_address) VALUES (?, ?)');
    $stmt->execute([$serverId, $ip]);
    if ($stmt->rowCount() > 0) {
        $stmt = db()->prepare('UPDATE servers SET views = (SELECT COUNT(*) FROM server_views WHERE server_id = ?) WHERE id = ?');
        $stmt->execute([$serverId, $serverId]);
    }
}

function getCategories(): array
{
    $stmt = db()->query('SELECT * FROM categories ORDER BY sort_order ASC');
    return $stmt->fetchAll();
}

function getCategoryById(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM categories WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $result = $stmt->fetch();
    return $result ?: null;
}

function flash(string $key, string $message = ''): ?string
{
    if (!empty($message)) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    if (!empty($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

function flashHtml(string $key, string $class = 'alert'): string
{
    $msg = flash($key);
    if ($msg) {
        return '<div class="' . $class . '">' . e($msg) . '</div>';
    }
    return '';
}
