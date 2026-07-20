<?php
require_once __DIR__ . '/includes/auth.php';

$page = $_GET['page'] ?? 'home';

$validPages = [
    'home', 'servers', 'server-view', 'signup', 'login',
    'dashboard', 'server-add', 'server-edit',
    'forgot-password', 'reset-password',
    'tickets', 'ticket-create', 'ticket-view',
    'terms', 'privacy'
];

if (!in_array($page, $validPages)) {
    $page = 'home';
}

$filePath = __DIR__ . '/pages/' . $page . '.php';

if (!file_exists($filePath)) {
    $page = 'home';
    $filePath = __DIR__ . '/pages/home.php';
}

$pageTitle = '';
ob_start();
require $filePath;
$pageContent = ob_get_clean();

require __DIR__ . '/includes/header.php';
echo $pageContent;
require __DIR__ . '/includes/footer.php';
