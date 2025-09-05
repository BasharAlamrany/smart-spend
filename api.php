<?php
/**
 * Alamrani Real Estate - REST API Endpoints
 * Handles all API requests with proper authentication, validation, and error handling
 */

require_once 'config.php';

// Set JSON response headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Parse the request
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];
$path = str_replace(dirname($script_name), '', $request_uri);
$path = trim($path, '/');
$path = explode('?', $path)[0]; // Remove query string

// Remove 'api.php' from path if present
$path = str_replace('api.php', '', $path);
$path = trim($path, '/');

$method = $_SERVER['REQUEST_METHOD'];
$segments = explode('/', $path);

// Initialize response structure
$response = [
    'success' => false,
    'data' => null,
    'error' => null,
    'meta' => [
        'timestamp' => date('c'),
        'version' => '1.0'
    ]
];

// Database connection
try {
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    $response['error'] = [
        'code' => 'DATABASE_ERROR',
        'message' => 'Database connection failed'
    ];
    http_response_code(500);
    echo json_encode($response);
    exit;
}

// Helper functions
function success_response($data = null, $meta = []) {
    global $response;
    $response['success'] = true;
    $response['data'] = $data;
    $response['meta'] = array_merge($response['meta'], $meta);
    return $response;
}

function error_response($code, $message, $status_code = 400) {
    global $response;
    $response['error'] = [
        'code' => $code,
        'message' => $message
    ];
    http_response_code($status_code);
    return $response;
}

function validate_required_fields($data, $required_fields) {
    $missing = [];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            $missing[] = $field;
        }
    }
    return $missing;
}

function sanitize_property_data($data) {
    return [
        'title' => sanitize_input($data['title'] ?? ''),
        'title_ar' => sanitize_input($data['title_ar'] ?? ''),
        'description' => sanitize_input($data['description'] ?? ''),
        'description_ar' => sanitize_input($data['description_ar'] ?? ''),
        'price' => floatval($data['price'] ?? 0),
        'currency' => sanitize_input($data['currency'] ?? 'YER'),
        'type' => sanitize_input($data['type'] ?? ''),
        'property_type' => sanitize_input($data['property_type'] ?? ''),
        'rooms' => intval($data['rooms'] ?? 0),
        'bathrooms' => intval($data['bathrooms'] ?? 0),
        'area_sqm' => floatval($data['area_sqm'] ?? 0),
        'city' => sanitize_input($data['city'] ?? ''),
        'address' => sanitize_input($data['address'] ?? ''),
        'address_ar' => sanitize_input($data['address_ar'] ?? ''),
        'latitude' => floatval($data['latitude'] ?? 0),
        'longitude' => floatval($data['longitude'] ?? 0),
        'agent_id' => intval($data['agent_id'] ?? 0)
    ];
}

function is_authenticated() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['admin_id']);
}

function get_current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

// Rate limiting (simple implementation)
function check_rate_limit($identifier, $max_requests = 100, $time_window = 3600) {
    // Simple file-based rate limiting
    $rate_limit_file = sys_get_temp_dir() . '/alamrani_rate_limit_' . md5($identifier);
    $current_time = time();
    
    if (file_exists($rate_limit_file)) {
        $data = json_decode(file_get_contents($rate_limit_file), true);
        if ($data && $current_time - $data['start_time'] < $time_window) {
            if ($data['requests'] >= $max_requests) {
                return false;
            }
            $data['requests']++;
        } else {
            $data = ['start_time' => $current_time, 'requests' => 1];
        }
    } else {
        $data = ['start_time' => $current_time, 'requests' => 1];
    }
    
    file_put_contents($rate_limit_file, json_encode($data));
    return true;
}

// Apply rate limiting
$client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!check_rate_limit($client_ip)) {
    echo json_encode(error_response('RATE_LIMIT_EXCEEDED', 'Too many requests', 429));
    exit;
}

try {
    // Route handling
    switch ($segments[0]) {
        case 'properties':
            handle_properties_endpoint($method, $segments);
            break;
            
        case 'agents':
            handle_agents_endpoint($method, $segments);
            break;
            
        case 'inquiries':
            handle_inquiries_endpoint($method, $segments);
            break;
            
        case 'favorites':
            handle_favorites_endpoint($method, $segments);
            break;
            
        case 'auth':
            handle_auth_endpoint($method, $segments);
            break;
            
        case 'search':
            handle_search_endpoint($method, $segments);
            break;
            
        case 'contact':
            handle_contact_endpoint($method, $segments);
            break;
            
        case 'newsletter':
            handle_newsletter_endpoint($method, $segments);
            break;
            
        case 'admin':
            handle_admin_endpoint($method, $segments);
            break;
            
        default:
            echo json_encode(error_response('ENDPOINT_NOT_FOUND', 'API endpoint not found', 404));
            exit;
    }
} catch (Exception $e) {
    if (DEBUG_MODE) {
        echo json_encode(error_response('INTERNAL_ERROR', $e->getMessage(), 500));
    } else {
        echo json_encode(error_response('INTERNAL_ERROR', 'An internal error occurred', 500));
    }
    exit;
}

// Properties endpoint handler
function handle_properties_endpoint($method, $segments) {
    global $db;
    
    switch ($method) {
        case 'GET':
            if (isset($segments[1]) && is_numeric($segments[1])) {
                // Get single property
                get_property_by_id($segments[1]);
            } else {
                // Get properties list with filters
                get_properties_list();
            }
            break;
            
        case 'POST':
            if (!is_admin()) {
                echo json_encode(error_response('UNAUTHORIZED', 'Admin access required', 401));
                return;
            }
            create_property();
            break;
            
        case 'PUT':
            if (!is_admin()) {
                echo json_encode(error_response('UNAUTHORIZED', 'Admin access required', 401));
                return;
            }
            if (isset($segments[1]) && is_numeric($segments[1])) {
                update_property($segments[1]);
            } else {
                echo json_encode(error_response('INVALID_REQUEST', 'Property ID required'));
            }
            break;
            
        case 'DELETE':
            if (!is_admin()) {
                echo json_encode(error_response('UNAUTHORIZED', 'Admin access required', 401));
                return;
            }
            if (isset($segments[1]) && is_numeric($segments[1])) {
                delete_property($segments[1]);
            } else {
                echo json_encode(error_response('INVALID_REQUEST', 'Property ID required'));
            }
            break;
            
        default:
            echo json_encode(error_response('METHOD_NOT_ALLOWED', 'Method not allowed', 405));
    }
}

function get_properties_list() {
    global $db;
    
    // Get filter parameters
    $filters = [
        'keyword' => $_GET['keyword'] ?? '',
        'city' => $_GET['city'] ?? '',
        'type' => $_GET['type'] ?? '',
        'property_type' => $_GET['property_type'] ?? '',
        'min_price' => floatval($_GET['min_price'] ?? 0),
        'max_price' => floatval($_GET['max_price'] ?? 0),
        'rooms' => intval($_GET['rooms'] ?? 0),
        'min_area' => floatval($_GET['min_area'] ?? 0),
        'max_area' => floatval($_GET['max_area'] ?? 0),
        'agent_id' => intval($_GET['agent_id'] ?? 0),
        'featured' => isset($_GET['featured']) ? (bool)$_GET['featured'] : null
    ];
    
    // Pagination
    $page = max(1, intval($_GET['page'] ?? 1));
    $per_page = min(50, max(1, intval($_GET['per_page'] ?? PROPERTIES_PER_PAGE)));
    $offset = ($page - 1) * $per_page;
    
    // Sorting
    $sort_by = $_GET['sort_by'] ?? 'created_at';
    $sort_order = strtoupper($_GET['sort_order'] ?? 'DESC');
    
    $allowed_sort_fields = ['created_at', 'price', 'area_sqm', 'title'];
    if (!in_array($sort_by, $allowed_sort_fields)) {
        $sort_by = 'created_at';
    }
    
    if (!in_array($sort_order, ['ASC', 'DESC'])) {
        $sort_order = 'DESC';
    }
    
    // Build WHERE clause
    $where_conditions = ['p.is_active = 1'];
    $params = [];
    
    if (!empty($filters['keyword'])) {
        $where_conditions[] = '(p.title LIKE ? OR p.title_ar LIKE ? OR p.description LIKE ? OR p.description_ar LIKE ?)';
        $keyword = '%' . $filters['keyword'] . '%';
        $params = array_merge($params, [$keyword, $keyword, $keyword, $keyword]);
    }
    
    if (!empty($filters['city'])) {
        $where_conditions[] = 'p.city = ?';
        $params[] = $filters['city'];
    }
    
    if (!empty($filters['type'])) {
        $where_conditions[] = 'p.type = ?';
        $params[] = $filters['type'];
    }
    
    if (!empty($filters['property_type'])) {
        $where_conditions[] = 'p.property_type = ?';
        $params[] = $filters['property_type'];
    }
    
    if ($filters['min_price'] > 0) {
        $where_conditions[] = 'p.price >= ?';
        $params[] = $filters['min_price'];
    }
    
    if ($filters['max_price'] > 0) {
        $where_conditions[] = 'p.price <= ?';
        $params[] = $filters['max_price'];
    }
    
    if ($filters['rooms'] > 0) {
        $where_conditions[] = 'p.rooms >= ?';
        $params[] = $filters['rooms'];
    }
    
    if ($filters['min_area'] > 0) {
        $where_conditions[] = 'p.area_sqm >= ?';
        $params[] = $filters['min_area'];
    }
    
    if ($filters['max_area'] > 0) {
        $where_conditions[] = 'p.area_sqm <= ?';
        $params[] = $filters['max_area'];
    }
    
    if ($filters['agent_id'] > 0) {
        $where_conditions[] = 'p.agent_id = ?';
        $params[] = $filters['agent_id'];
    }
    
    if ($filters['featured'] !== null) {
        $where_conditions[] = 'p.is_featured = ?';
        $params[] = $filters['featured'] ? 1 : 0;
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Get total count
    $count_sql = "SELECT COUNT(*) FROM properties p WHERE $where_clause";
    $count_stmt = $db->prepare($count_sql);
    $count_stmt->execute($params);
    $total_count = $count_stmt->fetchColumn();
    
    // Get properties
    $sql = "
        SELECT p.*, a.name as agent_name, a.phone as agent_phone, a.email as agent_email,
               (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY display_order LIMIT 1) as main_image,
               (SELECT COUNT(*) FROM property_images WHERE property_id = p.id) as images_count
        FROM properties p 
        LEFT JOIN agents a ON p.agent_id = a.id 
        WHERE $where_clause
        ORDER BY p.$sort_by $sort_order
        LIMIT $per_page OFFSET $offset
    ";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $properties = $stmt->fetchAll();
    
    // Format properties
    $formatted_properties = array_map(function($property) {
        return [
            'id' => intval($property['id']),
            'title' => $property['title'],
            'title_ar' => $property['title_ar'],
            'slug' => $property['slug'],
            'price' => floatval($property['price']),
            'currency' => $property['currency'],
            'type' => $property['type'],
            'property_type' => $property['property_type'],
            'rooms' => $property['rooms'] ? intval($property['rooms']) : null,
            'bathrooms' => $property['bathrooms'] ? intval($property['bathrooms']) : null,
            'area_sqm' => $property['area_sqm'] ? floatval($property['area_sqm']) : null,
            'city' => $property['city'],
            'address' => $property['address'],
            'address_ar' => $property['address_ar'],
            'latitude' => $property['latitude'] ? floatval($property['latitude']) : null,
            'longitude' => $property['longitude'] ? floatval($property['longitude']) : null,
            'main_image' => $property['main_image'],
            'images_count' => intval($property['images_count']),
            'is_featured' => (bool)$property['is_featured'],
            'agent' => [
                'id' => intval($property['agent_id']),
                'name' => $property['agent_name'],
                'phone' => $property['agent_phone'],
                'email' => $property['agent_email']
            ],
            'created_at' => $property['created_at'],
            'updated_at' => $property['updated_at']
        ];
    }, $properties);
    
    $meta = [
        'total' => intval($total_count),
        'page' => $page,
        'per_page' => $per_page,
        'total_pages' => ceil($total_count / $per_page),
        'has_next' => $page < ceil($total_count / $per_page),
        'has_prev' => $page > 1
    ];
    
    echo json_encode(success_response($formatted_properties, $meta));
}

function get_property_by_id($property_id) {
    global $db;
    
    // Get property details
    $stmt = $db->prepare("
        SELECT p.*, a.name as agent_name, a.phone as agent_phone, a.email as agent_email, a.whatsapp as agent_whatsapp
        FROM properties p 
        LEFT JOIN agents a ON p.agent_id = a.id 
        WHERE p.id = ? AND p.is_active = 1
    ");
    $stmt->execute([$property_id]);
    $property = $stmt->fetch();
    
    if (!$property) {
        echo json_encode(error_response('PROPERTY_NOT_FOUND', 'Property not found', 404));
        return;
    }
    
    // Get property images
    $images_stmt = $db->prepare("
        SELECT image_path, alt_text, is_main 
        FROM property_images 
        WHERE property_id = ? 
        ORDER BY display_order
    ");
    $images_stmt->execute([$property_id]);
    $images = $images_stmt->fetchAll();
    
    // Increment view count
    $view_stmt = $db->prepare("UPDATE properties SET views_count = views_count + 1 WHERE id = ?");
    $view_stmt->execute([$property_id]);
    
    // Track visit
    $visit_stmt = $db->prepare("INSERT INTO visits (property_id, page_type, ip_address, user_agent, session_id) VALUES (?, ?, ?, ?, ?)");
    $visit_stmt->execute([$property_id, 'property_details', $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '', session_id()]);
    
    // Format response
    $formatted_property = [
        'id' => intval($property['id']),
        'title' => $property['title'],
        'title_ar' => $property['title_ar'],
        'slug' => $property['slug'],
        'description' => $property['description'],
        'description_ar' => $property['description_ar'],
        'price' => floatval($property['price']),
        'currency' => $property['currency'],
        'type' => $property['type'],
        'property_type' => $property['property_type'],
        'rooms' => $property['rooms'] ? intval($property['rooms']) : null,
        'bathrooms' => $property['bathrooms'] ? intval($property['bathrooms']) : null,
        'area_sqm' => $property['area_sqm'] ? floatval($property['area_sqm']) : null,
        'city' => $property['city'],
        'address' => $property['address'],
        'address_ar' => $property['address_ar'],
        'latitude' => $property['latitude'] ? floatval($property['latitude']) : null,
        'longitude' => $property['longitude'] ? floatval($property['longitude']) : null,
        'year_built' => $property['year_built'] ? intval($property['year_built']) : null,
        'parking_spaces' => $property['parking_spaces'] ? intval($property['parking_spaces']) : null,
        'has_garden' => (bool)$property['has_garden'],
        'has_pool' => (bool)$property['has_pool'],
        'has_elevator' => (bool)$property['has_elevator'],
        'furnished' => $property['furnished'],
        'floor_number' => $property['floor_number'] ? intval($property['floor_number']) : null,
        'total_floors' => $property['total_floors'] ? intval($property['total_floors']) : null,
        'is_featured' => (bool)$property['is_featured'],
        'views_count' => intval($property['views_count']) + 1,
        'images' => array_map(function($img) {
            return [
                'url' => $img['image_path'],
                'alt' => $img['alt_text'],
                'is_main' => (bool)$img['is_main']
            ];
        }, $images),
        'agent' => [
            'id' => intval($property['agent_id']),
            'name' => $property['agent_name'],
            'phone' => $property['agent_phone'],
            'email' => $property['agent_email'],
            'whatsapp' => $property['agent_whatsapp']
        ],
        'created_at' => $property['created_at'],
        'updated_at' => $property['updated_at']
    ];
    
    echo json_encode(success_response($formatted_property));
}

// Inquiries endpoint handler
function handle_inquiries_endpoint($method, $segments) {
    global $db;
    
    switch ($method) {
        case 'POST':
            create_inquiry();
            break;
            
        case 'GET':
            if (!is_authenticated() && !is_admin()) {
                echo json_encode(error_response('UNAUTHORIZED', 'Authentication required', 401));
                return;
            }
            get_inquiries();
            break;
            
        default:
            echo json_encode(error_response('METHOD_NOT_ALLOWED', 'Method not allowed', 405));
    }
}

function create_inquiry() {
    global $db;
    
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    
    $required_fields = ['property_id', 'name', 'email', 'phone'];
    $missing_fields = validate_required_fields($input, $required_fields);
    
    if (!empty($missing_fields)) {
        echo json_encode(error_response('VALIDATION_ERROR', 'Missing required fields: ' . implode(', ', $missing_fields)));
        return;
    }
    
    // Validate property exists
    $property_stmt = $db->prepare("SELECT id FROM properties WHERE id = ? AND is_active = 1");
    $property_stmt->execute([$input['property_id']]);
    if (!$property_stmt->fetch()) {
        echo json_encode(error_response('INVALID_PROPERTY', 'Property not found'));
        return;
    }
    
    // Validate email
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(error_response('INVALID_EMAIL', 'Invalid email address'));
        return;
    }
    
    // Insert inquiry
    $stmt = $db->prepare("
        INSERT INTO inquiries (property_id, user_id, name, email, phone, message, preferred_contact_time, preferred_viewing_date, ip_address, user_agent) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $user_id = get_current_user_id();
    $viewing_date = !empty($input['preferred_viewing_date']) ? $input['preferred_viewing_date'] : null;
    
    $stmt->execute([
        $input['property_id'],
        $user_id,
        sanitize_input($input['name']),
        sanitize_input($input['email']),
        sanitize_input($input['phone']),
        sanitize_input($input['message'] ?? ''),
        sanitize_input($input['preferred_contact_time'] ?? ''),
        $viewing_date,
        $_SERVER['REMOTE_ADDR'] ?? '',
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
    
    $inquiry_id = $db->lastInsertId();
    
    // Send notification email (optional)
    try {
        send_inquiry_notification($inquiry_id);
    } catch (Exception $e) {
        // Log error but don't fail the request
        error_log("Failed to send inquiry notification: " . $e->getMessage());
    }
    
    echo json_encode(success_response(['inquiry_id' => $inquiry_id], ['message' => 'Inquiry submitted successfully']));
}

// Contact endpoint handler
function handle_contact_endpoint($method, $segments) {
    if ($method !== 'POST') {
        echo json_encode(error_response('METHOD_NOT_ALLOWED', 'Method not allowed', 405));
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    
    $required_fields = ['name', 'email', 'subject', 'message'];
    $missing_fields = validate_required_fields($input, $required_fields);
    
    if (!empty($missing_fields)) {
        echo json_encode(error_response('VALIDATION_ERROR', 'Missing required fields: ' . implode(', ', $missing_fields)));
        return;
    }
    
    // Validate email
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(error_response('INVALID_EMAIL', 'Invalid email address'));
        return;
    }
    
    // Here you would typically send the contact email
    // For now, we'll just return success
    
    echo json_encode(success_response(null, ['message' => 'Contact message sent successfully']));
}

// Search suggestions endpoint
function handle_search_endpoint($method, $segments) {
    global $db;
    
    if ($method !== 'GET' || !isset($segments[1]) || $segments[1] !== 'suggestions') {
        echo json_encode(error_response('ENDPOINT_NOT_FOUND', 'Endpoint not found', 404));
        return;
    }
    
    $query = $_GET['q'] ?? '';
    if (strlen($query) < 2) {
        echo json_encode(success_response([]));
        return;
    }
    
    $search_term = '%' . $query . '%';
    
    $stmt = $db->prepare("
        SELECT DISTINCT title as suggestion, 'property' as type FROM properties 
        WHERE (title LIKE ? OR title_ar LIKE ?) AND is_active = 1
        UNION
        SELECT DISTINCT city as suggestion, 'city' as type FROM properties 
        WHERE city LIKE ? AND is_active = 1
        LIMIT 10
    ");
    
    $stmt->execute([$search_term, $search_term, $search_term]);
    $suggestions = $stmt->fetchAll();
    
    echo json_encode(success_response($suggestions));
}

// Helper function to send inquiry notification
function send_inquiry_notification($inquiry_id) {
    // This would typically send an email notification
    // For now, we'll just log it
    error_log("Inquiry notification for ID: $inquiry_id");
}

// Favorites endpoint (requires authentication)
function handle_favorites_endpoint($method, $segments) {
    if (!is_authenticated()) {
        echo json_encode(error_response('UNAUTHORIZED', 'Authentication required', 401));
        return;
    }
    
    switch ($method) {
        case 'GET':
            get_user_favorites();
            break;
            
        case 'POST':
            add_to_favorites();
            break;
            
        case 'DELETE':
            if (isset($segments[1]) && is_numeric($segments[1])) {
                remove_from_favorites($segments[1]);
            } else {
                echo json_encode(error_response('INVALID_REQUEST', 'Property ID required'));
            }
            break;
            
        default:
            echo json_encode(error_response('METHOD_NOT_ALLOWED', 'Method not allowed', 405));
    }
}

function get_user_favorites() {
    global $db;
    
    $user_id = get_current_user_id();
    
    $stmt = $db->prepare("
        SELECT p.*, 
               (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY display_order LIMIT 1) as main_image
        FROM properties p
        INNER JOIN favorites f ON p.id = f.property_id
        WHERE f.user_id = ? AND p.is_active = 1
        ORDER BY f.created_at DESC
    ");
    
    $stmt->execute([$user_id]);
    $properties = $stmt->fetchAll();
    
    echo json_encode(success_response($properties));
}

function add_to_favorites() {
    global $db;
    
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $property_id = $input['property_id'] ?? null;
    
    if (!$property_id) {
        echo json_encode(error_response('VALIDATION_ERROR', 'Property ID required'));
        return;
    }
    
    $user_id = get_current_user_id();
    
    // Check if already in favorites
    $check_stmt = $db->prepare("SELECT id FROM favorites WHERE user_id = ? AND property_id = ?");
    $check_stmt->execute([$user_id, $property_id]);
    
    if ($check_stmt->fetch()) {
        echo json_encode(error_response('ALREADY_EXISTS', 'Property already in favorites'));
        return;
    }
    
    // Add to favorites
    $stmt = $db->prepare("INSERT INTO favorites (user_id, property_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $property_id]);
    
    echo json_encode(success_response(null, ['message' => 'Added to favorites']));
}

function remove_from_favorites($property_id) {
    global $db;
    
    $user_id = get_current_user_id();
    
    $stmt = $db->prepare("DELETE FROM favorites WHERE user_id = ? AND property_id = ?");
    $stmt->execute([$user_id, $property_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(success_response(null, ['message' => 'Removed from favorites']));
    } else {
        echo json_encode(error_response('NOT_FOUND', 'Property not in favorites', 404));
    }
}

// Newsletter subscription
function handle_newsletter_endpoint($method, $segments) {
    if ($method !== 'POST') {
        echo json_encode(error_response('METHOD_NOT_ALLOWED', 'Method not allowed', 405));
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $email = $input['email'] ?? '';
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(error_response('INVALID_EMAIL', 'Invalid email address'));
        return;
    }
    
    // Here you would typically save to a newsletter subscribers table
    // For now, we'll just return success
    
    echo json_encode(success_response(null, ['message' => 'Subscribed to newsletter successfully']));
}

// Admin endpoints (require admin authentication)
function handle_admin_endpoint($method, $segments) {
    if (!is_admin()) {
        echo json_encode(error_response('UNAUTHORIZED', 'Admin access required', 401));
        return;
    }
    
    if (!isset($segments[1])) {
        echo json_encode(error_response('ENDPOINT_NOT_FOUND', 'Admin endpoint not specified', 404));
        return;
    }
    
    switch ($segments[1]) {
        case 'stats':
            get_admin_stats();
            break;
            
        case 'inquiries':
            get_admin_inquiries();
            break;
            
        default:
            echo json_encode(error_response('ENDPOINT_NOT_FOUND', 'Admin endpoint not found', 404));
    }
}

function get_admin_stats() {
    global $db;
    
    $stats_queries = [
        'total_properties' => "SELECT COUNT(*) FROM properties WHERE is_active = 1",
        'featured_properties' => "SELECT COUNT(*) FROM properties WHERE is_active = 1 AND is_featured = 1",
        'total_agents' => "SELECT COUNT(*) FROM agents WHERE is_active = 1",
        'total_users' => "SELECT COUNT(*) FROM users",
        'total_inquiries' => "SELECT COUNT(*) FROM inquiries",
        'monthly_inquiries' => "SELECT COUNT(*) FROM inquiries WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
        'monthly_visits' => "SELECT COUNT(*) FROM visits WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
    ];
    
    $stats = [];
    foreach ($stats_queries as $key => $query) {
        $stmt = $db->query($query);
        $stats[$key] = intval($stmt->fetchColumn());
    }
    
    echo json_encode(success_response($stats));
}

function get_admin_inquiries() {
    global $db;
    
    $page = max(1, intval($_GET['page'] ?? 1));
    $per_page = min(50, max(1, intval($_GET['per_page'] ?? 20)));
    $offset = ($page - 1) * $per_page;
    
    // Get total count
    $count_stmt = $db->query("SELECT COUNT(*) FROM inquiries");
    $total_count = $count_stmt->fetchColumn();
    
    // Get inquiries
    $stmt = $db->prepare("
        SELECT i.*, p.title as property_title, p.slug as property_slug
        FROM inquiries i
        LEFT JOIN properties p ON i.property_id = p.id
        ORDER BY i.created_at DESC
        LIMIT ? OFFSET ?
    ");
    
    $stmt->execute([$per_page, $offset]);
    $inquiries = $stmt->fetchAll();
    
    $meta = [
        'total' => intval($total_count),
        'page' => $page,
        'per_page' => $per_page,
        'total_pages' => ceil($total_count / $per_page)
    ];
    
    echo json_encode(success_response($inquiries, $meta));
}
?>