<?php
/**
 * GreenLink Market - Application Configuration
 */

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Lax');

// Application constants
define('APP_NAME', 'GreenLink Market');
define('APP_VERSION', '1.0.0');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost:8000');
define('APP_ROOT', dirname(__DIR__));

// Load local development secrets without requiring a third-party dotenv package.
// Production deployments should provide these values through the web server.
$localEnvFile = APP_ROOT . '/.env';
if (is_file($localEnvFile) && is_readable($localEnvFile)) {
    $localEnv = parse_ini_file($localEnvFile, false, INI_SCANNER_RAW) ?: [];
    foreach ($localEnv as $key => $value) {
        if (getenv($key) === false) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }
}

// Paths
define('UPLOAD_PATH', APP_ROOT . '/assets/uploads/');
define('UPLOAD_URL', rtrim(APP_URL, '/') . '/assets/uploads/');
define('MAX_PRODUCT_IMAGE_SIZE', 5 * 1024 * 1024);

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// Pagination
define('ITEMS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 20);

// AI Configuration
$geminiApiKeys = array_values(array_filter(array_map(
    'trim',
    explode(',', getenv('GEMINI_API_KEYS') ?: (getenv('GEMINI_API_KEY') ?: ''))
)));
define('GEMINI_API_KEYS', $geminiApiKeys);
define('GEMINI_API_KEY', $geminiApiKeys[0] ?? 'your-gemini-api-key-here');
define('GEMINI_MODEL', getenv('GEMINI_MODEL') ?: 'gemini-3.5-flash');
define('GEMINI_API_URL', getenv('GEMINI_API_URL') ?: 'https://generativelanguage.googleapis.com/v1beta/models');

// Currency
define('CURRENCY', 'LKR');
define('APP_CURRENCY_SYMBOL', 'Rs.');
define('TAX_RATE', 0.05);
define('SHIPPING_FEE', 350);
define('FREE_SHIPPING_THRESHOLD', 10000);

/**
 * Helper Functions
 */

function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function generateCSRFToken(): string {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function validateCSRFToken(string $token): bool {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

function redirect(string $url): void {
    header("Location: $url");
    exit;
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'customer'
    ];
}

function formatPrice(float $amount): string {
    return APP_CURRENCY_SYMBOL . ' ' . number_format($amount, 2);
}

function productImageUrl(?string $image): ?string {
    $image = trim((string) $image);
    if ($image === '') return null;

    if (filter_var($image, FILTER_VALIDATE_URL)) {
        $scheme = strtolower((string) parse_url($image, PHP_URL_SCHEME));
        return in_array($scheme, ['http', 'https'], true) ? $image : null;
    }

    $path = ltrim(str_replace('\\', '/', $image), '/');
    if ($path === '' || str_contains($path, '..')) return null;
    return rtrim(APP_URL, '/') . '/' . $path;
}

function generateOrderNumber(): string {
    return 'NSBM-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
}

function slugify(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text);
}

function jsonResponse($successOrData, string $message = '', $data = null, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    
    if (is_array($successOrData)) {
        // Legacy call: jsonResponse(['key' => 'value'])
        echo json_encode($successOrData);
    } else {
        // New call: jsonResponse(true/false, 'message', data)
        $response = ['success' => (bool)$successOrData, 'message' => $message];
        if ($data !== null) {
            $response['data'] = $data;
        }
        if (!$successOrData && $statusCode === 200) {
            http_response_code(400);
        }
        echo json_encode($response);
    }
    exit;
}

function getTimeAgo(string $datetime): string {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('M d, Y', $time);
}
