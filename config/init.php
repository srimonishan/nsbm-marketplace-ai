<?php
/**
 * GreenLink Market - Bootstrap/Initialization
 * Include this file at the top of every page
 */

// Load configuration before starting the session so its cookie settings apply.
require_once __DIR__ . '/app.php';

if (session_status() === PHP_SESSION_NONE) {
    $requestPath = (string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    $portalHeader = strtolower(trim((string) ($_SERVER['HTTP_X_GREENLINK_PORTAL'] ?? '')));
    $usesAdminSession = str_contains($requestPath, '/admin/') || $portalHeader === 'admin';
    session_name($usesAdminSession ? 'GREENLINK_ADMIN_SESSION' : 'GREENLINK_USER_SESSION');
    session_start();
}

require_once __DIR__ . '/database.php';

// Load models
require_once APP_ROOT . '/models/Product.php';
require_once APP_ROOT . '/models/Category.php';
require_once APP_ROOT . '/models/User.php';
require_once APP_ROOT . '/models/Order.php';
require_once APP_ROOT . '/models/Review.php';
require_once APP_ROOT . '/models/Notification.php';

// Create logs directory if not exists
if (!is_dir(APP_ROOT . '/logs')) {
    mkdir(APP_ROOT . '/logs', 0755, true);
}

// Create uploads directory if not exists
if (!is_dir(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}
