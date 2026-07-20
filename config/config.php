<?php
define('APP_NAME', 'RedM Servers');
define('APP_URL', 'https://redm.gamer.gd');
define('APP_VERSION', '1.0.0');

define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/');
define('BANNER_DIR', UPLOAD_DIR . 'banners/');
define('SCREENSHOT_DIR', UPLOAD_DIR . 'screenshots/');
define('BANNER_MAX_SIZE', 2 * 1024 * 1024);
define('SCREENSHOT_MAX_SIZE', 5 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('MAX_SCREENSHOTS', 6);

define('ITEMS_PER_PAGE', 12);
define('SITE_EMAIL', 'rexshackuk@gmail.com');
