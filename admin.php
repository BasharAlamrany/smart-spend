<?php
/**
 * Alamrani Real Estate - Admin Panel
 * Complete administration interface for managing properties, agents, users, and inquiries
 */

require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle admin login
        $username = sanitize_input($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if ($username && $password) {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT id, username, password, full_name, role FROM admins WHERE username = ? AND is_active = 1");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name'] = $admin['full_name'];
                $_SESSION['admin_role'] = $admin['role'];
                
                // Update last login
                $update_stmt = $db->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
                $update_stmt->execute([$admin['id']]);
                
                redirect('admin.php');
            } else {
                $login_error = 'Invalid username or password';
            }
        } else {
            $login_error = 'Please enter both username and password';
        }
    }
    
    // Show login form
    include 'admin-login.php';
    exit;
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    redirect('admin.php');
}

$lang_data = load_language();
$current_lang = get_current_language();
$db = Database::getInstance()->getConnection();

// Handle AJAX requests
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['ajax']) {
        case 'stats':
            echo json_encode(get_admin_stats());
            break;
            
        case 'delete_property':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
                echo json_encode(delete_property($_POST['id']));
            }
            break;
            
        case 'toggle_featured':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
                echo json_encode(toggle_property_featured($_POST['id']));
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'create_property':
                $result = create_property($_POST);
                if ($result['success']) {
                    $success = 'Property created successfully';
                } else {
                    $error = $result['message'];
                }
                break;
                
            case 'update_property':
                $result = update_property($_POST['id'], $_POST);
                if ($result['success']) {
                    $success = 'Property updated successfully';
                } else {
                    $error = $result['message'];
                }
                break;
                
            case 'create_agent':
                $result = create_agent($_POST);
                if ($result['success']) {
                    $success = 'Agent created successfully';
                } else {
                    $error = $result['message'];
                }
                break;
                
            case 'update_inquiry_status':
                $result = update_inquiry_status($_POST['id'], $_POST['status'], $_POST['notes'] ?? '');
                if ($result['success']) {
                    $success = 'Inquiry status updated';
                } else {
                    $error = $result['message'];
                }
                break;
        }
    }
}

// Get current page
$page = $_GET['page'] ?? 'dashboard';
$allowed_pages = ['dashboard', 'properties', 'agents', 'users', 'inquiries', 'settings'];
if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard';
}

// Get admin statistics
function get_admin_stats() {
    global $db;
    
    $stats = [];
    
    $queries = [
        'total_properties' => "SELECT COUNT(*) FROM properties WHERE is_active = 1",
        'featured_properties' => "SELECT COUNT(*) FROM properties WHERE is_active = 1 AND is_featured = 1",
        'total_agents' => "SELECT COUNT(*) FROM agents WHERE is_active = 1",
        'total_users' => "SELECT COUNT(*) FROM users",
        'total_inquiries' => "SELECT COUNT(*) FROM inquiries",
        'new_inquiries' => "SELECT COUNT(*) FROM inquiries WHERE status = 'new'",
        'monthly_visits' => "SELECT COUNT(*) FROM visits WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
        'monthly_inquiries' => "SELECT COUNT(*) FROM inquiries WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
    ];
    
    foreach ($queries as $key => $query) {
        try {
            $stmt = $db->query($query);
            $stats[$key] = intval($stmt->fetchColumn());
        } catch (PDOException $e) {
            $stats[$key] = 0;
        }
    }
    
    return $stats;
}

$stats = get_admin_stats();

// Helper functions for CRUD operations
function create_property($data) {
    global $db;
    
    try {
        $stmt = $db->prepare("
            INSERT INTO properties (title, title_ar, slug, description, description_ar, price, currency, type, property_type, 
                                  rooms, bathrooms, area_sqm, city, address, address_ar, latitude, longitude, agent_id, 
                                  is_featured, year_built, parking_spaces, has_garden, has_pool, has_elevator, furnished)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $slug = generate_slug($data['title']);
        
        $stmt->execute([
            sanitize_input($data['title']),
            sanitize_input($data['title_ar']),
            $slug,
            sanitize_input($data['description'] ?? ''),
            sanitize_input($data['description_ar'] ?? ''),
            floatval($data['price']),
            sanitize_input($data['currency'] ?? 'YER'),
            sanitize_input($data['type']),
            sanitize_input($data['property_type']),
            intval($data['rooms'] ?? 0),
            intval($data['bathrooms'] ?? 0),
            floatval($data['area_sqm'] ?? 0),
            sanitize_input($data['city']),
            sanitize_input($data['address']),
            sanitize_input($data['address_ar']),
            floatval($data['latitude'] ?? 0),
            floatval($data['longitude'] ?? 0),
            intval($data['agent_id']),
            isset($data['is_featured']) ? 1 : 0,
            intval($data['year_built'] ?? 0),
            intval($data['parking_spaces'] ?? 0),
            isset($data['has_garden']) ? 1 : 0,
            isset($data['has_pool']) ? 1 : 0,
            isset($data['has_elevator']) ? 1 : 0,
            sanitize_input($data['furnished'] ?? 'unfurnished')
        ]);
        
        return ['success' => true, 'id' => $db->lastInsertId()];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

function delete_property($property_id) {
    global $db;
    
    try {
        $stmt = $db->prepare("UPDATE properties SET is_active = 0 WHERE id = ?");
        $stmt->execute([$property_id]);
        
        return ['success' => true, 'message' => 'Property deleted successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

function toggle_property_featured($property_id) {
    global $db;
    
    try {
        $stmt = $db->prepare("UPDATE properties SET is_featured = NOT is_featured WHERE id = ?");
        $stmt->execute([$property_id]);
        
        return ['success' => true, 'message' => 'Featured status updated'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

function update_inquiry_status($inquiry_id, $status, $notes) {
    global $db;
    
    try {
        $stmt = $db->prepare("UPDATE inquiries SET status = ?, agent_notes = ? WHERE id = ?");
        $stmt->execute([sanitize_input($status), sanitize_input($notes), $inquiry_id]);
        
        return ['success' => true, 'message' => 'Inquiry updated successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>" dir="<?php echo $current_lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang_data['admin_dashboard']; ?> - <?php echo SITE_NAME; ?></title>
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/admin.css">
    <?php if ($current_lang === 'ar'): ?>
    <link rel="stylesheet" href="css/rtl.css">
    <?php endif; ?>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-body <?php echo $current_lang; ?>">
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="admin-header-content">
            <div class="admin-logo">
                <h2><?php echo $lang_data['admin_dashboard']; ?></h2>
            </div>
            
            <div class="admin-header-actions">
                <div class="admin-user-info">
                    <span><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                    <small>(<?php echo htmlspecialchars($_SESSION['admin_role']); ?>)</small>
                </div>
                
                <div class="language-switcher">
                    <select id="languageSelect" onchange="changeLanguage(this.value)">
                        <option value="ar" <?php echo $current_lang === 'ar' ? 'selected' : ''; ?>>العربية</option>
                        <option value="en" <?php echo $current_lang === 'en' ? 'selected' : ''; ?>>English</option>
                    </select>
                </div>
                
                <a href="index.php" class="btn btn-outline btn-sm" target="_blank">
                    <?php echo $current_lang === 'ar' ? 'عرض الموقع' : 'View Site'; ?>
                </a>
                
                <a href="admin.php?action=logout" class="btn btn-secondary btn-sm">
                    <?php echo $lang_data['nav_logout']; ?>
                </a>
            </div>
        </div>
    </header>

    <div class="admin-layout">
        <!-- Admin Sidebar -->
        <aside class="admin-sidebar">
            <nav class="admin-nav">
                <ul>
                    <li class="<?php echo $page === 'dashboard' ? 'active' : ''; ?>">
                        <a href="admin.php?page=dashboard">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                            </svg>
                            <?php echo $current_lang === 'ar' ? 'الرئيسية' : 'Dashboard'; ?>
                        </a>
                    </li>
                    
                    <li class="<?php echo $page === 'properties' ? 'active' : ''; ?>">
                        <a href="admin.php?page=properties">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                <polyline points="9,22 9,12 15,12 15,22"></polyline>
                            </svg>
                            <?php echo $lang_data['admin_properties']; ?>
                        </a>
                    </li>
                    
                    <li class="<?php echo $page === 'agents' ? 'active' : ''; ?>">
                        <a href="admin.php?page=agents">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <?php echo $lang_data['admin_agents']; ?>
                        </a>
                    </li>
                    
                    <li class="<?php echo $page === 'users' ? 'active' : ''; ?>">
                        <a href="admin.php?page=users">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            <?php echo $lang_data['admin_users']; ?>
                        </a>
                    </li>
                    
                    <li class="<?php echo $page === 'inquiries' ? 'active' : ''; ?>">
                        <a href="admin.php?page=inquiries">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            </svg>
                            <?php echo $lang_data['admin_inquiries']; ?>
                            <?php if ($stats['new_inquiries'] > 0): ?>
                            <span class="badge"><?php echo $stats['new_inquiries']; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    
                    <li class="<?php echo $page === 'settings' ? 'active' : ''; ?>">
                        <a href="admin.php?page=settings">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="3"></circle>
                                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1 1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                            </svg>
                            <?php echo $lang_data['admin_settings']; ?>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <?php
            // Include the appropriate page content
            switch ($page) {
                case 'dashboard':
                    include 'admin-pages/dashboard.php';
                    break;
                case 'properties':
                    include 'admin-pages/properties.php';
                    break;
                case 'agents':
                    include 'admin-pages/agents.php';
                    break;
                case 'users':
                    include 'admin-pages/users.php';
                    break;
                case 'inquiries':
                    include 'admin-pages/inquiries.php';
                    break;
                case 'settings':
                    include 'admin-pages/settings.php';
                    break;
                default:
                    echo '<h1>Page not found</h1>';
            }
            ?>
        </main>
    </div>

    <!-- Scripts -->
    <script src="js/app.js"></script>
    <script src="js/admin.js"></script>
</body>
</html>

<?php
// Admin Dashboard Content
if ($page === 'dashboard'):
?>
<div class="admin-dashboard">
    <div class="dashboard-header">
        <h1><?php echo $current_lang === 'ar' ? 'لوحة التحكم' : 'Dashboard'; ?></h1>
        <p><?php echo $current_lang === 'ar' ? 'مرحباً بك في لوحة إدارة العمراني للعقارات' : 'Welcome to Alamrani Real Estate Admin Panel'; ?></p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo number_format($stats['total_properties']); ?></div>
                <div class="stat-label"><?php echo $lang_data['stats_total_properties']; ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"></polygon>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo number_format($stats['featured_properties']); ?></div>
                <div class="stat-label"><?php echo $lang_data['stats_featured_properties']; ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo number_format($stats['total_agents']); ?></div>
                <div class="stat-label"><?php echo $lang_data['stats_total_agents']; ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo number_format($stats['total_users']); ?></div>
                <div class="stat-label"><?php echo $lang_data['stats_total_users']; ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo number_format($stats['total_inquiries']); ?></div>
                <div class="stat-label"><?php echo $lang_data['stats_total_inquiries']; ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo number_format($stats['monthly_visits']); ?></div>
                <div class="stat-label"><?php echo $lang_data['stats_monthly_visits']; ?></div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="dashboard-section">
        <h2><?php echo $current_lang === 'ar' ? 'النشاط الأخير' : 'Recent Activity'; ?></h2>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3><?php echo $current_lang === 'ar' ? 'أحدث الاستفسارات' : 'Recent Inquiries'; ?></h3>
                    </div>
                    <div class="card-body">
                        <?php
                        $recent_inquiries_stmt = $db->prepare("
                            SELECT i.*, p.title as property_title 
                            FROM inquiries i 
                            LEFT JOIN properties p ON i.property_id = p.id 
                            ORDER BY i.created_at DESC 
                            LIMIT 5
                        ");
                        $recent_inquiries_stmt->execute();
                        $recent_inquiries = $recent_inquiries_stmt->fetchAll();
                        
                        if ($recent_inquiries):
                        ?>
                        <div class="activity-list">
                            <?php foreach ($recent_inquiries as $inquiry): ?>
                            <div class="activity-item">
                                <div class="activity-content">
                                    <strong><?php echo htmlspecialchars($inquiry['name']); ?></strong>
                                    <p><?php echo $current_lang === 'ar' ? 'استفسار عن' : 'Inquiry about'; ?>: <?php echo htmlspecialchars($inquiry['property_title']); ?></p>
                                    <small><?php echo date('Y-m-d H:i', strtotime($inquiry['created_at'])); ?></small>
                                </div>
                                <div class="activity-status">
                                    <span class="badge badge-<?php echo $inquiry['status']; ?>"><?php echo ucfirst($inquiry['status']); ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <p><?php echo $current_lang === 'ar' ? 'لا توجد استفسارات حديثة' : 'No recent inquiries'; ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3><?php echo $current_lang === 'ar' ? 'أحدث العقارات' : 'Recent Properties'; ?></h3>
                    </div>
                    <div class="card-body">
                        <?php
                        $recent_properties_stmt = $db->prepare("
                            SELECT title, title_ar, created_at, is_featured 
                            FROM properties 
                            WHERE is_active = 1 
                            ORDER BY created_at DESC 
                            LIMIT 5
                        ");
                        $recent_properties_stmt->execute();
                        $recent_properties = $recent_properties_stmt->fetchAll();
                        
                        if ($recent_properties):
                        ?>
                        <div class="activity-list">
                            <?php foreach ($recent_properties as $property): ?>
                            <div class="activity-item">
                                <div class="activity-content">
                                    <strong><?php echo htmlspecialchars($current_lang === 'ar' ? $property['title_ar'] : $property['title']); ?></strong>
                                    <small><?php echo date('Y-m-d H:i', strtotime($property['created_at'])); ?></small>
                                </div>
                                <?php if ($property['is_featured']): ?>
                                <div class="activity-status">
                                    <span class="badge badge-featured"><?php echo $current_lang === 'ar' ? 'مميز' : 'Featured'; ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <p><?php echo $current_lang === 'ar' ? 'لا توجد عقارات حديثة' : 'No recent properties'; ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
// Admin Login Form (separate file would be admin-login.php)
function render_admin_login($error = null) {
    global $lang_data, $current_lang;
    ?>
    <!DOCTYPE html>
    <html lang="<?php echo $current_lang; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login - <?php echo SITE_NAME; ?></title>
        <link rel="stylesheet" href="css/styles.css">
        <link rel="stylesheet" href="css/admin.css">
    </head>
    <body class="login-body">
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <h1><?php echo $current_lang === 'ar' ? 'تسجيل دخول الإدارة' : 'Admin Login'; ?></h1>
                    <p><?php echo SITE_NAME; ?></p>
                </div>
                
                <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" class="login-form">
                    <div class="form-group">
                        <label for="username"><?php echo $current_lang === 'ar' ? 'اسم المستخدم' : 'Username'; ?></label>
                        <input type="text" id="username" name="username" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password"><?php echo $current_lang === 'ar' ? 'كلمة المرور' : 'Password'; ?></label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        <?php echo $current_lang === 'ar' ? 'تسجيل الدخول' : 'Login'; ?>
                    </button>
                </form>
                
                <div class="login-footer">
                    <a href="index.php"><?php echo $current_lang === 'ar' ? 'العودة للموقع' : 'Back to Website'; ?></a>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>