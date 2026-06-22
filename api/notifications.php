<?php
/**
 * Customer Notifications API
 */

require_once __DIR__ . '/../config/init.php';

header('Content-Type: application/json');

if (!isLoggedIn() || isAdmin()) {
    jsonResponse(['success' => false, 'message' => 'Authentication required'], 401);
}

$notificationModel = new Notification();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $notifications = $notificationModel->getForUser((int) $_SESSION['user_id']);
    jsonResponse([
        'success' => true,
        'data' => $notifications,
        'unread_count' => $notificationModel->getUnreadCount((int) $_SESSION['user_id'])
    ]);
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    if (!empty($data['all'])) {
        $notificationModel->markAllRead((int) $_SESSION['user_id']);
    } elseif (!empty($data['id'])) {
        $notificationModel->markRead((int) $data['id'], (int) $_SESSION['user_id']);
    } else {
        jsonResponse(['success' => false, 'message' => 'Notification id is required'], 400);
    }

    jsonResponse([
        'success' => true,
        'unread_count' => $notificationModel->getUnreadCount((int) $_SESSION['user_id'])
    ]);
}

jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
