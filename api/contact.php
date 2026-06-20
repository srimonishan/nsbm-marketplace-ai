<?php
/**
 * Contact API - GreenLink Market
 */

require_once __DIR__ . '/../config/init.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $name = trim($data['name'] ?? '');
    $email = trim($data['email'] ?? '');
    $subject = trim($data['subject'] ?? '');
    $message = trim($data['message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        jsonResponse(['success' => false, 'message' => 'All fields are required'], 400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['success' => false, 'message' => 'Invalid email format'], 400);
    }

    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare(
            "INSERT INTO contact_messages (name, email, subject, message) VALUES (:name, :email, :subject, :message)"
        );
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':subject' => $subject,
            ':message' => $message
        ]);

        jsonResponse(['success' => true, 'message' => 'Message sent successfully! We will get back to you soon.'], 201);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'Failed to send message'], 500);
    }
} elseif ($method === 'GET' && isAdmin()) {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
    jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
} else {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}
