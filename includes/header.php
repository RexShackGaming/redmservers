<?php require_once __DIR__ . '/auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <a href="<?= APP_URL ?>" class="logo">
                <span class="logo-icon">RDR</span>
                <span class="logo-text">RedM<span class="logo-accent">Servers</span></span>
            </a>
            <nav class="main-nav">
                <a href="<?= APP_URL ?>/?page=servers" class="nav-link">Browse Servers</a>
                <?php if (isLoggedIn()): ?>
                    <a href="<?= APP_URL ?>/?page=dashboard" class="nav-link">Dashboard</a>
                    <a href="<?= APP_URL ?>/?page=tickets" class="nav-link">Support</a>
                    <a href="<?= APP_URL ?>/?page=server-add" class="btn btn-sm btn-primary">List Server</a>
                    <div class="nav-user">
                        <span class="nav-username"><?= e($_SESSION['username']) ?></span>
                        <?php if (isAdmin()): ?>
                            <a href="<?= APP_URL ?>/admin/" class="nav-link nav-admin">Admin</a>
                        <?php endif; ?>
                        <a href="<?= APP_URL ?>/logout.php" class="nav-link">Logout</a>
                    </div>
                <?php else: ?>
                    <a href="<?= APP_URL ?>/?page=login" class="nav-link">Login</a>
                    <a href="<?= APP_URL ?>/?page=signup" class="btn btn-sm btn-primary">Sign Up</a>
                <?php endif; ?>
            </nav>
            <button class="mobile-toggle" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </header>
    <main class="site-main">
