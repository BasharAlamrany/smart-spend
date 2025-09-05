-- Alamrani Real Estate Database Schema
-- MySQL 8.0+ compatible with utf8mb4 support

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Database: alamrani_realestate
CREATE DATABASE IF NOT EXISTS `alamrani_realestate` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `alamrani_realestate`;

-- Table structure for table `admins`
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('super_admin','admin') DEFAULT 'admin',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `agents`
CREATE TABLE `agents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `bio_ar` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `license_number` varchar(50) DEFAULT NULL,
  `experience_years` int(11) DEFAULT 0,
  `specialization` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `users`
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` timestamp NULL DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_verification` (`verification_token`),
  KEY `idx_reset` (`reset_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `properties`
CREATE TABLE `properties` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `title_ar` varchar(200) NOT NULL,
  `slug` varchar(250) NOT NULL,
  `description` text DEFAULT NULL,
  `description_ar` text DEFAULT NULL,
  `price` decimal(15,2) NOT NULL,
  `currency` varchar(5) DEFAULT 'YER',
  `type` enum('sale','rent') NOT NULL,
  `property_type` enum('apartment','villa','house','office','shop','land','warehouse') NOT NULL,
  `rooms` int(11) DEFAULT NULL,
  `bathrooms` int(11) DEFAULT NULL,
  `area_sqm` decimal(10,2) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `address_ar` varchar(255) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `agent_id` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `views_count` int(11) DEFAULT 0,
  `year_built` int(11) DEFAULT NULL,
  `parking_spaces` int(11) DEFAULT NULL,
  `has_garden` tinyint(1) DEFAULT 0,
  `has_pool` tinyint(1) DEFAULT 0,
  `has_elevator` tinyint(1) DEFAULT 0,
  `furnished` enum('furnished','semi_furnished','unfurnished') DEFAULT 'unfurnished',
  `floor_number` int(11) DEFAULT NULL,
  `total_floors` int(11) DEFAULT NULL,
  `meta_title` varchar(200) DEFAULT NULL,
  `meta_description` varchar(300) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `agent_id` (`agent_id`),
  KEY `idx_active` (`is_active`),
  KEY `idx_featured` (`is_featured`),
  KEY `idx_type` (`type`),
  KEY `idx_property_type` (`property_type`),
  KEY `idx_city` (`city`),
  KEY `idx_price` (`price`),
  KEY `idx_rooms` (`rooms`),
  KEY `idx_area` (`area_sqm`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `properties_ibfk_1` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `property_images`
CREATE TABLE `property_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `property_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `alt_text` varchar(200) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_main` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `property_id` (`property_id`),
  KEY `idx_order` (`display_order`),
  KEY `idx_main` (`is_main`),
  CONSTRAINT `property_images_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `favorites`
CREATE TABLE `favorites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_property` (`user_id`,`property_id`),
  KEY `property_id` (`property_id`),
  CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `inquiries`
CREATE TABLE `inquiries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `property_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `message` text DEFAULT NULL,
  `preferred_contact_time` varchar(50) DEFAULT NULL,
  `preferred_viewing_date` date DEFAULT NULL,
  `status` enum('new','contacted','scheduled','closed') DEFAULT 'new',
  `agent_notes` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `property_id` (`property_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `inquiries_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inquiries_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `visits`
CREATE TABLE `visits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `property_id` int(11) DEFAULT NULL,
  `page_type` varchar(50) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `referrer` varchar(500) DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `property_id` (`property_id`),
  KEY `idx_page_type` (`page_type`),
  KEY `idx_created` (`created_at`),
  KEY `idx_session` (`session_id`),
  CONSTRAINT `visits_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `settings`
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','boolean','json') DEFAULT 'text',
  `description` varchar(255) DEFAULT NULL,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data

-- Insert admin user (password: admin123)
INSERT INTO `admins` (`username`, `email`, `password`, `full_name`, `role`) VALUES
('admin', 'admin@alamrani-realestate.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'مدير النظام', 'super_admin');

-- Insert sample agents
INSERT INTO `agents` (`name`, `email`, `phone`, `whatsapp`, `bio`, `bio_ar`, `license_number`, `experience_years`, `specialization`) VALUES
('أحمد العمراني', 'ahmed@alamrani-realestate.com', '+967-1-234567', '+967-777-123456', 'Experienced real estate agent specializing in residential properties in Sana\'a.', 'وكيل عقارات خبير متخصص في العقارات السكنية في صنعاء.', 'RE-2023-001', 8, 'Residential Properties'),
('فاطمة الحداد', 'fatima@alamrani-realestate.com', '+967-1-345678', '+967-777-234567', 'Commercial real estate specialist with focus on office buildings and retail spaces.', 'متخصصة في العقارات التجارية مع التركيز على المباني المكتبية والمساحات التجارية.', 'RE-2023-002', 6, 'Commercial Properties');

-- Insert sample users (password: user123)
INSERT INTO `users` (`email`, `password`, `full_name`, `phone`, `is_verified`) VALUES
('user1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'محمد أحمد', '+967-777-111222', 1),
('user2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'سارة علي', '+967-777-333444', 1);

-- Insert sample properties
INSERT INTO `properties` (`title`, `title_ar`, `slug`, `description`, `description_ar`, `price`, `currency`, `type`, `property_type`, `rooms`, `bathrooms`, `area_sqm`, `city`, `address`, `address_ar`, `latitude`, `longitude`, `agent_id`, `is_featured`, `year_built`, `parking_spaces`, `has_garden`, `furnished`) VALUES
('Luxury Apartment in Sixty Street', 'شقة فاخرة في شارع الستين', 'luxury-apartment-sixty-street', 'Beautiful 3-bedroom apartment with modern amenities and city view. Located in the heart of Sana\'a with easy access to shopping centers and restaurants.', 'شقة جميلة من 3 غرف نوم مع وسائل الراحة الحديثة وإطلالة على المدينة. تقع في قلب صنعاء مع سهولة الوصول إلى مراكز التسوق والمطاعم.', 2500000.00, 'YER', 'sale', 'apartment', 3, 2, 120.00, 'Sana\'a', 'Sixty Street, Building 12', 'شارع الستين، مبنى 12', 15.36940000, 44.19100000, 1, 1, 2020, 1, 0, 'semi_furnished'),

('Modern Villa in Hadda District', 'فيلا حديثة في حي حدة', 'modern-villa-hadda-district', 'Spacious 5-bedroom villa with private garden and swimming pool. Perfect for large families seeking luxury and comfort.', 'فيلا واسعة من 5 غرف نوم مع حديقة خاصة ومسبح. مثالية للعائلات الكبيرة التي تسعى للرفاهية والراحة.', 8500000.00, 'YER', 'sale', 'villa', 5, 4, 350.00, 'Sana\'a', 'Hadda District, Villa 45', 'حي حدة، فيلا 45', 15.37500000, 44.20500000, 1, 1, 2019, 3, 1, 'furnished'),

('Office Space in Commercial Center', 'مساحة مكتبية في المركز التجاري', 'office-space-commercial-center', 'Prime office location in the business district. Ideal for companies and professional services. Includes parking and 24/7 security.', 'موقع مكتبي ممتاز في الحي التجاري. مثالي للشركات والخدمات المهنية. يتضمن موقف سيارات وأمن على مدار الساعة.', 150000.00, 'YER', 'rent', 'office', 0, 2, 80.00, 'Sana\'a', 'Commercial Center, Floor 3', 'المركز التجاري، الطابق الثالث', 15.36500000, 44.19500000, 2, 0, 2021, 2, 0, 'unfurnished'),

('Cozy House in Old City', 'منزل مريح في المدينة القديمة', 'cozy-house-old-city', 'Traditional Yemeni house with authentic architecture. Recently renovated while preserving historical character.', 'منزل يمني تقليدي بهندسة معمارية أصيلة. تم تجديده مؤخراً مع المحافظة على الطابع التاريخي.', 1800000.00, 'YER', 'sale', 'house', 4, 2, 200.00, 'Sana\'a', 'Old City, Al-Qasimi Street', 'المدينة القديمة، شارع القاسمي', 15.35000000, 44.21000000, 1, 0, 1950, 0, 1, 'unfurnished'),

('Retail Shop in Busy Market', 'محل تجاري في السوق المزدحم', 'retail-shop-busy-market', 'Well-located retail space in high-traffic area. Perfect for various business types. Ground floor with street access.', 'مساحة تجارية في موقع ممتاز في منطقة مرور عالي. مثالية لأنواع مختلفة من الأعمال. الطابق الأرضي مع دخول من الشارع.', 80000.00, 'YER', 'rent', 'shop', 0, 1, 45.00, 'Sana\'a', 'Al-Thawra Street, Shop 15', 'شارع الثورة، محل 15', 15.36000000, 44.20000000, 2, 0, 2018, 0, 0, 'unfurnished'),

('Luxury Penthouse with Panoramic View', 'بنتهاوس فاخر مع إطلالة بانورامية', 'luxury-penthouse-panoramic-view', 'Exclusive penthouse apartment with 360-degree city views. Features premium finishes, private terrace, and modern amenities.', 'شقة بنتهاوس حصرية مع إطلالة 360 درجة على المدينة. تتميز بتشطيبات فاخرة وشرفة خاصة ووسائل راحة حديثة.', 4200000.00, 'YER', 'sale', 'apartment', 4, 3, 180.00, 'Sana\'a', 'Tower Heights, Top Floor', 'أبراج المرتفعات، الطابق العلوي', 15.37200000, 44.19800000, 1, 1, 2022, 2, 1, 'furnished'),

('Family Apartment for Rent', 'شقة عائلية للإيجار', 'family-apartment-rent', 'Comfortable family apartment in quiet neighborhood. Close to schools and healthcare facilities. Well-maintained building with elevator.', 'شقة عائلية مريحة في حي هادئ. قريبة من المدارس والمرافق الصحية. مبنى جيد الصيانة مع مصعد.', 45000.00, 'YER', 'rent', 'apartment', 3, 2, 110.00, 'Sana\'a', 'Al-Sabeen District, Building 8', 'حي السبعين، مبنى 8', 15.36700000, 44.18900000, 2, 0, 2017, 1, 0, 'semi_furnished');

-- Insert property images
INSERT INTO `property_images` (`property_id`, `image_path`, `alt_text`, `display_order`, `is_main`) VALUES
(1, 'uploads/properties/apt1_main.jpg', 'شقة فاخرة في شارع الستين - المنظر الرئيسي', 1, 1),
(1, 'uploads/properties/apt1_living.jpg', 'شقة فاخرة في شارع الستين - غرفة المعيشة', 2, 0),
(1, 'uploads/properties/apt1_kitchen.jpg', 'شقة فاخرة في شارع الستين - المطبخ', 3, 0),
(2, 'uploads/properties/villa1_main.jpg', 'فيلا حديثة في حي حدة - المنظر الرئيسي', 1, 1),
(2, 'uploads/properties/villa1_garden.jpg', 'فيلا حديثة في حي حدة - الحديقة', 2, 0),
(2, 'uploads/properties/villa1_pool.jpg', 'فيلا حديثة في حي حدة - المسبح', 3, 0),
(3, 'uploads/properties/office1_main.jpg', 'مساحة مكتبية في المركز التجاري - المنظر الرئيسي', 1, 1),
(4, 'uploads/properties/house1_main.jpg', 'منزل مريح في المدينة القديمة - المنظر الرئيسي', 1, 1),
(5, 'uploads/properties/shop1_main.jpg', 'محل تجاري في السوق المزدحم - المنظر الرئيسي', 1, 1),
(6, 'uploads/properties/penthouse1_main.jpg', 'بنتهاوس فاخر مع إطلالة بانورامية - المنظر الرئيسي', 1, 1),
(7, 'uploads/properties/apt2_main.jpg', 'شقة عائلية للإيجار - المنظر الرئيسي', 1, 1);

-- Insert sample favorites
INSERT INTO `favorites` (`user_id`, `property_id`) VALUES
(1, 1),
(1, 2),
(2, 1),
(2, 6);

-- Insert sample inquiries
INSERT INTO `inquiries` (`property_id`, `user_id`, `name`, `email`, `phone`, `message`, `preferred_viewing_date`, `status`) VALUES
(1, 1, 'محمد أحمد', 'user1@example.com', '+967-777-111222', 'مهتم بمشاهدة الشقة. متى يمكنني الحضور؟', '2024-01-15', 'new'),
(2, 2, 'سارة علي', 'user2@example.com', '+967-777-333444', 'أريد معرفة المزيد عن الفيلا وإمكانية التفاوض في السعر.', '2024-01-20', 'contacted'),
(6, NULL, 'خالد محمد', 'khalid@example.com', '+967-777-555666', 'هل البنتهاوس متاح للمعاينة هذا الأسبوع؟', '2024-01-18', 'new');

-- Insert system settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('site_maintenance', '0', 'boolean', 'Site maintenance mode'),
('contact_email', 'contact@alamrani-realestate.com', 'text', 'Main contact email'),
('contact_phone', '+967-1-234567', 'text', 'Main contact phone'),
('contact_whatsapp', '+967-777-123456', 'text', 'WhatsApp contact number'),
('social_facebook', 'https://facebook.com/alamrani-realestate', 'text', 'Facebook page URL'),
('social_instagram', 'https://instagram.com/alamrani-realestate', 'text', 'Instagram profile URL'),
('social_twitter', 'https://twitter.com/alamrani_re', 'text', 'Twitter profile URL'),
('company_address', 'شارع الستين، صنعاء، الجمهورية اليمنية', 'text', 'Company address'),
('company_address_en', 'Sixty Street, Sana\'a, Republic of Yemen', 'text', 'Company address in English'),
('max_property_images', '10', 'number', 'Maximum images per property'),
('featured_properties_count', '6', 'number', 'Number of featured properties to show on homepage');

COMMIT;