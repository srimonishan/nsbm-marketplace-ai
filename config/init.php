<?php
/**
 * NSBM Marketplace AI - Bootstrap/Initialization
 * Include this file at the top of every page
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load configuration
require_once __DIR__ . '/app.php';
require_once __DIR__ . '/database.php';

// Load models
require_once APP_ROOT . '/models/Product.php';
require_once APP_ROOT . '/models/Category.php';
require_once APP_ROOT . '/models/User.php';
require_once APP_ROOT . '/models/Order.php';
require_once APP_ROOT . '/models/Review.php';

// Create logs directory if not exists
if (!is_dir(APP_ROOT . '/logs')) {
    mkdir(APP_ROOT . '/logs', 0755, true);
}

// Create uploads directory if not exists
if (!is_dir(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}
