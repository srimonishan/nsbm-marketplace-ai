<?php
/**
 * Authentication API - GreenLink Market
 */

require_once __DIR__ . '/../config/init.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($action === 'check' && $method !== 'GET') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

if ($action !== 'check' && $method !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$data = $method === 'POST'
    ? (json_decode(file_get_contents('php://input'), true) ?: [])
    : [];
$userModel = new User();

switch ($action) {
    case 'login':
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $requestedRole = ($data['role'] ?? 'customer') === 'admin' ? 'admin' : 'customer';

        if (empty($email) || empty($password)) {
            jsonResponse(['success' => false, 'message' => 'Email and password are required'], 400);
        }

        $user = $userModel->authenticate($email, $password);
        if ($user) {
            if ($requestedRole === 'admin' && $user['role'] !== 'admin') {
                jsonResponse(['success' => false, 'message' => 'This account does not have admin access'], 403);
            }
            if ($requestedRole === 'customer' && $user['role'] === 'admin') {
                jsonResponse(['success' => false, 'message' => 'Please use the Admin Login page'], 403);
            }

            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            jsonResponse([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['first_name'] . ' ' . $user['last_name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Invalid email or password'], 401);
        }
        break;

    case 'register':
        $firstName = trim($data['first_name'] ?? '');
        $lastName = trim($data['last_name'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';

        // Validation
        if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
            jsonResponse(['success' => false, 'message' => 'All fields are required'], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['success' => false, 'message' => 'Invalid email format'], 400);
        }

        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            jsonResponse(['success' => false, 'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'], 400);
        }

        if ($password !== $confirmPassword) {
            jsonResponse(['success' => false, 'message' => 'Passwords do not match'], 400);
        }

        // Check if email exists
        if ($userModel->getByEmail($email)) {
            jsonResponse(['success' => false, 'message' => 'Email already registered'], 409);
        }

        try {
            $userId = $userModel->create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'password' => $password
            ]);

            session_regenerate_id(true);
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $firstName . ' ' . $lastName;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = 'customer';

            jsonResponse([
                'success' => true,
                'message' => 'Registration successful',
                'user' => [
                    'id' => $userId,
                    'name' => $firstName . ' ' . $lastName,
                    'email' => $email,
                    'role' => 'customer'
                ]
            ], 201);
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => 'Registration failed'], 500);
        }
        break;

    case 'logout':
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax'
            ]);
        }
        session_destroy();
        jsonResponse(['success' => true, 'message' => 'Logged out successfully']);
        break;

    case 'check':
        if (isLoggedIn()) {
            jsonResponse([
                'success' => true,
                'authenticated' => true,
                'user' => getCurrentUser()
            ]);
        } else {
            jsonResponse(['success' => true, 'authenticated' => false]);
        }
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
}
