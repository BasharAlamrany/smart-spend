<?php
/**
 * Alamrani Real Estate - Configuration File
 * Contains database settings, site configuration, and security keys
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'alamrani_realestate');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_URL', 'http://localhost/alamrani');
define('SITE_NAME', 'Alamrani Real Estate');
define('SITE_NAME_AR', 'العمراني للعقارات');
define('ADMIN_EMAIL', 'admin@alamrani-realestate.com');

// Security Configuration
define('CSRF_SECRET', 'your-random-csrf-secret-key-here');
define('SESSION_LIFETIME', 86400);
define('PASSWORD_MIN_LENGTH', 8);

// File Upload Configuration
define('UPLOAD_MAX_SIZE', 5242880);
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'webp']);
define('UPLOAD_PATH', __DIR__ . '/uploads/properties/');

// Email Configuration
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 1025);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_FROM_EMAIL', 'noreply@alamrani-realestate.com');
define('SMTP_FROM_NAME', 'Alamrani Real Estate');

// Map Configuration
define('GOOGLE_MAPS_API_KEY', '');
define('DEFAULT_MAP_CENTER_LAT', 15.3694);
define('DEFAULT_MAP_CENTER_LNG', 44.1910);
define('DEFAULT_MAP_ZOOM', 12);

// Pagination
define('PROPERTIES_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 20);

// Debug Mode
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Database Connection Class
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
}

// Utility Functions
function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function redirect($url, $code = 302) {
    header("Location: $url", true, $code);
    exit;
}

function get_current_language() {
    if (isset($_COOKIE['language']) && in_array($_COOKIE['language'], ['ar', 'en'])) {
        return $_COOKIE['language'];
    }
    return isset($_GET['lang']) && in_array($_GET['lang'], ['ar', 'en']) ? $_GET['lang'] : 'ar';
}

function load_language($lang = null) {
    if ($lang === null) {
        $lang = get_current_language();
    }
    
    $lang_file = __DIR__ . "/lang/{$lang}.php";
    if (file_exists($lang_file)) {
        return include $lang_file;
    }
    return include __DIR__ . "/lang/ar.php";
}

function format_price($price, $currency = 'YER') {
    return number_format($price) . ' ' . $currency;
}

function generate_slug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

// Start session with security settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.cookie_samesite', 'Strict');
session_start();

// Set language cookie if lang parameter is provided
if (isset($_GET['lang']) && in_array($_GET['lang'], ['ar', 'en'])) {
    setcookie('language', $_GET['lang'], time() + (86400 * 30), '/');
    $_COOKIE['language'] = $_GET['lang'];
}
?>