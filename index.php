<?php
/**
 * Alamrani Real Estate - Home Page
 * Main landing page with hero section, featured properties, and search functionality
 */

require_once 'config.php';
$lang_data = load_language();
$current_lang = get_current_language();
$db = Database::getInstance()->getConnection();

// Get featured properties
try {
    $stmt = $db->prepare("
        SELECT p.*, a.name as agent_name, a.phone as agent_phone,
               (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY display_order LIMIT 1) as main_image
        FROM properties p 
        LEFT JOIN agents a ON p.agent_id = a.id 
        WHERE p.is_active = 1 AND p.is_featured = 1 
        ORDER BY p.created_at DESC 
        LIMIT 6
    ");
    $stmt->execute();
    $featured_properties = $stmt->fetchAll();
} catch (PDOException $e) {
    $featured_properties = [];
}

// Get cities for search dropdown
try {
    $stmt = $db->prepare("SELECT DISTINCT city FROM properties WHERE is_active = 1 ORDER BY city");
    $stmt->execute();
    $cities = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $cities = [];
}

// Get statistics
try {
    $stats_stmt = $db->prepare("
        SELECT 
            (SELECT COUNT(*) FROM properties WHERE is_active = 1) as total_properties,
            (SELECT COUNT(*) FROM agents WHERE is_active = 1) as total_agents,
            (SELECT COUNT(*) FROM users) as total_users,
            (SELECT COUNT(*) FROM inquiries WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as monthly_inquiries
    ");
    $stats_stmt->execute();
    $stats = $stats_stmt->fetch();
} catch (PDOException $e) {
    $stats = ['total_properties' => 0, 'total_agents' => 0, 'total_users' => 0, 'monthly_inquiries' => 0];
}

// SEO Meta Data
$page_title = $lang_data['site_title'];
$page_description = $lang_data['site_description'];
$canonical_url = SITE_URL;

// Track visit
try {
    $stmt = $db->prepare("INSERT INTO visits (page_type, ip_address, user_agent, session_id) VALUES (?, ?, ?, ?)");
    $stmt->execute(['home', $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '', session_id()]);
} catch (PDOException $e) {
    // Ignore visit tracking errors
}
?>
<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>" dir="<?php echo $current_lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($lang_data['keywords']); ?>">
    <link rel="canonical" href="<?php echo $canonical_url; ?>">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta property="og:url" content="<?php echo $canonical_url; ?>">
    <meta property="og:type" content="website">
    <meta property="og:image" content="<?php echo SITE_URL; ?>/images/hero-bg.jpg">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta name="twitter:image" content="<?php echo SITE_URL; ?>/images/hero-bg.jpg">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="css/styles.css">
    <?php if ($current_lang === 'ar'): ?>
    <link rel="stylesheet" href="css/rtl.css">
    <?php endif; ?>
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="vendor/leaflet/leaflet.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="images/favicon.png">
</head>
<body class="<?php echo $current_lang; ?>" data-user-logged-in="<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>">
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="index.php">
                        <img src="images/logo.png" alt="<?php echo SITE_NAME; ?>">
                        <span><?php echo $current_lang === 'ar' ? SITE_NAME_AR : SITE_NAME; ?></span>
                    </a>
                </div>
                
                <nav class="nav-menu" id="navMenu">
                    <ul>
                        <li><a href="index.php" class="active"><?php echo $lang_data['nav_home']; ?></a></li>
                        <li><a href="properties.php"><?php echo $lang_data['nav_properties']; ?></a></li>
                        <li><a href="agents.php"><?php echo $lang_data['nav_agents']; ?></a></li>
                        <li><a href="about.php"><?php echo $lang_data['nav_about']; ?></a></li>
                        <li><a href="contact.php"><?php echo $lang_data['nav_contact']; ?></a></li>
                    </ul>
                </nav>
                
                <div class="header-actions">
                    <div class="language-switcher">
                        <select id="languageSelect" onchange="changeLanguage(this.value)">
                            <option value="ar" <?php echo $current_lang === 'ar' ? 'selected' : ''; ?>>العربية</option>
                            <option value="en" <?php echo $current_lang === 'en' ? 'selected' : ''; ?>>English</option>
                        </select>
                    </div>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="dashboard.php" class="btn btn-outline"><?php echo $lang_data['dashboard']; ?></a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline"><?php echo $lang_data['login']; ?></a>
                    <?php endif; ?>
                    
                    <button class="menu-toggle" id="menuToggle">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile Navigation -->
        <div class="mobile-nav" id="mobileNav">
            <ul>
                <li><a href="index.php" class="active"><?php echo $lang_data['nav_home']; ?></a></li>
                <li><a href="properties.php"><?php echo $lang_data['nav_properties']; ?></a></li>
                <li><a href="agents.php"><?php echo $lang_data['nav_agents']; ?></a></li>
                <li><a href="about.php"><?php echo $lang_data['nav_about']; ?></a></li>
                <li><a href="contact.php"><?php echo $lang_data['nav_contact']; ?></a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="dashboard.php"><?php echo $lang_data['dashboard']; ?></a></li>
                    <li><a href="logout.php"><?php echo $lang_data['nav_logout']; ?></a></li>
                <?php else: ?>
                    <li><a href="login.php"><?php echo $lang_data['nav_login']; ?></a></li>
                    <li><a href="register.php"><?php echo $lang_data['nav_register']; ?></a></li>
                <?php endif; ?>
            </ul>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1 class="fade-in"><?php echo $lang_data['hero_title']; ?></h1>
            <p class="fade-in"><?php echo $lang_data['hero_subtitle']; ?></p>
            <div class="hero-actions fade-in">
                <a href="properties.php" class="btn btn-primary btn-lg"><?php echo $lang_data['hero_cta']; ?></a>
                <a href="contact.php" class="btn btn-outline btn-lg"><?php echo $lang_data['hero_cta_secondary']; ?></a>
            </div>
        </div>
        
        <!-- Search Panel -->
        <div class="search-panel slide-up">
            <form id="searchForm" class="search-form" action="properties.php" method="GET">
                <div class="form-group">
                    <input type="text" name="keyword" id="searchInput" class="form-control" 
                           placeholder="<?php echo $lang_data['hero_search_placeholder']; ?>">
                    <div id="searchSuggestions" class="search-suggestions"></div>
                </div>
                
                <div class="form-group">
                    <select name="city" class="form-control form-select">
                        <option value=""><?php echo $lang_data['search_city']; ?></option>
                        <?php foreach ($cities as $city): ?>
                            <option value="<?php echo htmlspecialchars($city); ?>"><?php echo htmlspecialchars($city); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <select name="type" class="form-control form-select">
                        <option value=""><?php echo $lang_data['search_purpose']; ?></option>
                        <option value="sale"><?php echo $lang_data['purpose_sale']; ?></option>
                        <option value="rent"><?php echo $lang_data['purpose_rent']; ?></option>
                    </select>
                </div>
                
                <div class="form-group">
                    <select name="property_type" class="form-control form-select">
                        <option value=""><?php echo $lang_data['search_type']; ?></option>
                        <option value="apartment"><?php echo $lang_data['property_type_apartment']; ?></option>
                        <option value="villa"><?php echo $lang_data['property_type_villa']; ?></option>
                        <option value="house"><?php echo $lang_data['property_type_house']; ?></option>
                        <option value="office"><?php echo $lang_data['property_type_office']; ?></option>
                        <option value="shop"><?php echo $lang_data['property_type_shop']; ?></option>
                        <option value="land"><?php echo $lang_data['property_type_land']; ?></option>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">
                        <?php echo $lang_data['search_button']; ?>
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- Featured Properties Section -->
    <?php if (!empty($featured_properties)): ?>
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title"><?php echo $lang_data['section_featured']; ?></h2>
                <p class="section-subtitle">
                    <?php echo $current_lang === 'ar' ? 'اكتشف أفضل العقارات المختارة بعناية' : 'Discover our handpicked premium properties'; ?>
                </p>
            </div>
            
            <div class="properties-grid">
                <?php foreach ($featured_properties as $property): ?>
                <div class="property-card card">
                    <div class="property-image">
                        <img src="<?php echo $property['main_image'] ?: 'images/property-placeholder.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($current_lang === 'ar' ? $property['title_ar'] : $property['title']); ?>"
                             loading="lazy">
                        
                        <div class="property-badge">
                            <?php echo $property['type'] === 'sale' ? $lang_data['purpose_sale'] : $lang_data['purpose_rent']; ?>
                        </div>
                        
                        <div class="property-actions">
                            <button class="property-action favorite-btn" data-property-id="<?php echo $property['id']; ?>" 
                                    data-tooltip="<?php echo $lang_data['property_add_favorite']; ?>">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                </svg>
                            </button>
                            <button class="property-action quick-view-btn" data-property-id="<?php echo $property['id']; ?>"
                                    data-tooltip="<?php echo $current_lang === 'ar' ? 'معاينة سريعة' : 'Quick View'; ?>">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <div class="property-content">
                        <h3 class="property-title">
                            <a href="property-details.php?slug=<?php echo $property['slug']; ?>">
                                <?php echo htmlspecialchars($current_lang === 'ar' ? $property['title_ar'] : $property['title']); ?>
                            </a>
                        </h3>
                        
                        <div class="property-location">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <?php echo htmlspecialchars($property['city']); ?>
                        </div>
                        
                        <div class="property-features">
                            <?php if ($property['rooms']): ?>
                            <div class="property-feature">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                </svg>
                                <?php echo $property['rooms'] . ' ' . $lang_data['property_rooms']; ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($property['bathrooms']): ?>
                            <div class="property-feature">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"></path>
                                    <path d="M8 8v13h8V8"></path>
                                    <path d="M6 8h12"></path>
                                </svg>
                                <?php echo $property['bathrooms'] . ' ' . $lang_data['property_bathrooms']; ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($property['area_sqm']): ?>
                            <div class="property-feature">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14,2 14,8 20,8"></polyline>
                                </svg>
                                <?php echo number_format($property['area_sqm']) . ' ' . ($current_lang === 'ar' ? 'م²' : 'm²'); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="property-price">
                            <?php echo format_price($property['price'], $property['currency']); ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-8">
                <a href="properties.php" class="btn btn-primary btn-lg">
                    <?php echo $current_lang === 'ar' ? 'عرض جميع العقارات' : 'View All Properties'; ?>
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Services Section -->
    <section class="section bg-gray-50">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title"><?php echo $lang_data['section_services']; ?></h2>
                <p class="section-subtitle">
                    <?php echo $current_lang === 'ar' ? 'نقدم خدمات عقارية شاملة لتلبية جميع احتياجاتكم' : 'We provide comprehensive real estate services to meet all your needs'; ?>
                </p>
            </div>
            
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9,22 9,12 15,12 15,22"></polyline>
                        </svg>
                    </div>
                    <h3 class="service-title"><?php echo $lang_data['service_buy']; ?></h3>
                    <p class="service-description">
                        <?php echo $current_lang === 'ar' ? 'نساعدك في العثور على العقار المثالي للشراء بأفضل الأسعار' : 'We help you find the perfect property to buy at the best prices'; ?>
                    </p>
                </div>
                
                <div class="service-card">
                    <div class="service-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                        </svg>
                    </div>
                    <h3 class="service-title"><?php echo $lang_data['service_sell']; ?></h3>
                    <p class="service-description">
                        <?php echo $current_lang === 'ar' ? 'نساعدك في بيع عقارك بأفضل سعر في أقل وقت ممكن' : 'We help you sell your property at the best price in the shortest time'; ?>
                    </p>
                </div>
                
                <div class="service-card">
                    <div class="service-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </div>
                    <h3 class="service-title"><?php echo $lang_data['service_rent']; ?></h3>
                    <p class="service-description">
                        <?php echo $current_lang === 'ar' ? 'خدمات تأجير شاملة للعقارات السكنية والتجارية' : 'Comprehensive rental services for residential and commercial properties'; ?>
                    </p>
                </div>
                
                <div class="service-card">
                    <div class="service-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"></path>
                            <circle cx="12" cy="13" r="3"></circle>
                        </svg>
                    </div>
                    <h3 class="service-title"><?php echo $lang_data['service_manage']; ?></h3>
                    <p class="service-description">
                        <?php echo $current_lang === 'ar' ? 'إدارة احترافية للعقارات مع متابعة دورية وصيانة شاملة' : 'Professional property management with regular monitoring and comprehensive maintenance'; ?>
                    </p>
                </div>
                
                <div class="service-card">
                    <div class="service-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                        </svg>
                    </div>
                    <h3 class="service-title"><?php echo $lang_data['service_invest']; ?></h3>
                    <p class="service-description">
                        <?php echo $current_lang === 'ar' ? 'استشارات الاستثمار العقاري وتحليل الفرص الاستثمارية' : 'Real estate investment consulting and investment opportunity analysis'; ?>
                    </p>
                </div>
                
                <div class="service-card">
                    <div class="service-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 11H5a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2h-4m-4 0V9a2 2 0 0 1 4 0v2m-4 0h4"></path>
                        </svg>
                    </div>
                    <h3 class="service-title"><?php echo $lang_data['service_consult']; ?></h3>
                    <p class="service-description">
                        <?php echo $current_lang === 'ar' ? 'استشارات عقارية متخصصة من خبراء في السوق اليمني' : 'Specialized real estate consulting from experts in the Yemeni market'; ?>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="section">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-6">
                    <div class="text-center">
                        <div class="counter text-primary" data-count="<?php echo $stats['total_properties']; ?>">0</div>
                        <h4><?php echo $lang_data['stats_total_properties']; ?></h4>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="text-center">
                        <div class="counter text-primary" data-count="<?php echo $stats['total_agents']; ?>">0</div>
                        <h4><?php echo $lang_data['stats_total_agents']; ?></h4>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="text-center">
                        <div class="counter text-primary" data-count="<?php echo $stats['total_users']; ?>">0</div>
                        <h4><?php echo $lang_data['stats_total_users']; ?></h4>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="text-center">
                        <div class="counter text-primary" data-count="<?php echo $stats['monthly_inquiries']; ?>">0</div>
                        <h4><?php echo $current_lang === 'ar' ? 'استفسار شهري' : 'Monthly Inquiries'; ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><?php echo $lang_data['footer_about']; ?></h3>
                    <p><?php echo $lang_data['footer_about_text']; ?></p>
                    <div class="social-links">
                        <a href="#" class="social-link" aria-label="Facebook">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="#" class="social-link" aria-label="Instagram">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 6.62 5.367 11.987 11.988 11.987c6.62 0 11.987-5.367 11.987-11.987C24.014 5.367 18.637.001 12.017.001zM8.449 16.988c-1.297 0-2.448-.611-3.197-1.559-.748-.948-1.197-2.15-1.197-3.429 0-1.279.449-2.481 1.197-3.429.749-.948 1.9-1.559 3.197-1.559 1.296 0 2.447.611 3.196 1.559.748.948 1.197 2.15 1.197 3.429 0 1.279-.449 2.481-1.197 3.429-.749.948-1.9 1.559-3.196 1.559z"/>
                            </svg>
                        </a>
                        <a href="#" class="social-link" aria-label="Twitter">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                        </a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3><?php echo $lang_data['footer_quick_links']; ?></h3>
                    <ul>
                        <li><a href="properties.php"><?php echo $lang_data['nav_properties']; ?></a></li>
                        <li><a href="agents.php"><?php echo $lang_data['nav_agents']; ?></a></li>
                        <li><a href="about.php"><?php echo $lang_data['nav_about']; ?></a></li>
                        <li><a href="contact.php"><?php echo $lang_data['nav_contact']; ?></a></li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li><a href="dashboard.php"><?php echo $lang_data['dashboard']; ?></a></li>
                        <?php else: ?>
                            <li><a href="login.php"><?php echo $lang_data['login']; ?></a></li>
                            <li><a href="register.php"><?php echo $lang_data['register']; ?></a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3><?php echo $lang_data['footer_contact_info']; ?></h3>
                    <ul>
                        <li><?php echo $current_lang === 'ar' ? 'شارع الستين، صنعاء، اليمن' : 'Sixty Street, Sana\'a, Yemen'; ?></li>
                        <li>+967-1-234567</li>
                        <li>info@alamrani-realestate.com</li>
                        <li><?php echo $lang_data['contact_hours_value']; ?></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3><?php echo $lang_data['footer_newsletter']; ?></h3>
                    <p><?php echo $lang_data['footer_newsletter_text']; ?></p>
                    <form class="newsletter-form" action="api.php/newsletter" method="POST">
                        <div class="form-group">
                            <input type="email" name="email" class="form-control" 
                                   placeholder="<?php echo $current_lang === 'ar' ? 'البريد الإلكتروني' : 'Email Address'; ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">
                            <?php echo $lang_data['footer_subscribe']; ?>
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo $current_lang === 'ar' ? SITE_NAME_AR : SITE_NAME; ?>. <?php echo $lang_data['footer_rights']; ?></p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="vendor/leaflet/leaflet.js"></script>
    <script src="js/app.js"></script>
    
    <!-- JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "RealEstateAgent",
        "name": "<?php echo $current_lang === 'ar' ? SITE_NAME_AR : SITE_NAME; ?>",
        "url": "<?php echo SITE_URL; ?>",
        "logo": "<?php echo SITE_URL; ?>/images/logo.png",
        "image": "<?php echo SITE_URL; ?>/images/hero-bg.jpg",
        "description": "<?php echo htmlspecialchars($page_description); ?>",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "<?php echo $current_lang === 'ar' ? 'شارع الستين' : 'Sixty Street'; ?>",
            "addressLocality": "<?php echo $current_lang === 'ar' ? 'صنعاء' : 'Sana\'a'; ?>",
            "addressCountry": "<?php echo $current_lang === 'ar' ? 'اليمن' : 'Yemen'; ?>"
        },
        "telephone": "+967-1-234567",
        "email": "<?php echo ADMIN_EMAIL; ?>",
        "sameAs": [
            "https://facebook.com/alamrani-realestate",
            "https://instagram.com/alamrani-realestate",
            "https://twitter.com/alamrani_re"
        ]
    }
    </script>
</body>
</html>