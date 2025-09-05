<?php
/**
 * Email Template: Welcome Email for New Users
 * Sent to users after successful registration
 */

function generate_welcome_email($user_data) {
    $current_lang = get_current_language();
    
    $subject = $current_lang === 'ar' 
        ? 'مرحباً بك في العمراني للعقارات'
        : 'Welcome to Alamrani Real Estate';
    
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="<?php echo $current_lang; ?>" dir="<?php echo $current_lang === 'ar' ? 'rtl' : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($subject); ?></title>
        <style>
            body {
                font-family: <?php echo $current_lang === 'ar' ? 'Cairo, Tahoma' : 'Arial, Helvetica'; ?>, sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
                direction: <?php echo $current_lang === 'ar' ? 'rtl' : 'ltr'; ?>;
            }
            .header {
                background: linear-gradient(135deg, #4FC3F7, #0B6E79);
                color: white;
                padding: 40px 20px;
                text-align: center;
                border-radius: 10px 10px 0 0;
            }
            .header h1 {
                margin: 0 0 10px 0;
                font-size: 28px;
            }
            .header p {
                margin: 0;
                font-size: 16px;
                opacity: 0.9;
            }
            .content {
                background: white;
                padding: 40px 30px;
                border: 1px solid #ddd;
                border-top: none;
            }
            .welcome-message {
                text-align: center;
                margin-bottom: 30px;
            }
            .welcome-message h2 {
                color: #0B6E79;
                margin-bottom: 15px;
            }
            .features-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                margin: 30px 0;
            }
            .feature-card {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 8px;
                text-align: center;
                border-left: 4px solid #4FC3F7;
            }
            .ar .feature-card {
                border-left: none;
                border-right: 4px solid #4FC3F7;
            }
            .feature-icon {
                width: 48px;
                height: 48px;
                background: #4FC3F7;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 15px auto;
                color: white;
                font-size: 20px;
            }
            .cta-section {
                background: linear-gradient(135deg, #e3f2fd, #f3e5f5);
                padding: 30px;
                border-radius: 10px;
                text-align: center;
                margin: 30px 0;
            }
            .cta-button {
                display: inline-block;
                background: #4FC3F7;
                color: white;
                padding: 15px 30px;
                text-decoration: none;
                border-radius: 8px;
                font-weight: bold;
                font-size: 16px;
                margin: 10px;
                transition: background 0.3s;
            }
            .cta-button:hover {
                background: #0B6E79;
            }
            .cta-button.secondary {
                background: transparent;
                color: #4FC3F7;
                border: 2px solid #4FC3F7;
            }
            .cta-button.secondary:hover {
                background: #4FC3F7;
                color: white;
            }
            .footer {
                background: #f8f9fa;
                padding: 30px 20px;
                text-align: center;
                color: #666;
                border-radius: 0 0 10px 10px;
            }
            .social-links {
                margin: 20px 0;
            }
            .social-links a {
                display: inline-block;
                margin: 0 10px;
                color: #4FC3F7;
                text-decoration: none;
            }
            .contact-info {
                background: white;
                border: 1px solid #e9ecef;
                border-radius: 8px;
                padding: 20px;
                margin: 20px 0;
            }
            .contact-info h4 {
                color: #0B6E79;
                margin-bottom: 15px;
                text-align: center;
            }
            .contact-item {
                display: flex;
                align-items: center;
                margin: 10px 0;
                justify-content: <?php echo $current_lang === 'ar' ? 'flex-end' : 'flex-start'; ?>;
            }
            .contact-icon {
                width: 20px;
                height: 20px;
                margin-<?php echo $current_lang === 'ar' ? 'left' : 'right'; ?>: 10px;
                color: #4FC3F7;
            }
            @media (max-width: 600px) {
                body {
                    padding: 10px;
                }
                .header, .content, .footer {
                    padding: 20px 15px;
                }
                .features-grid {
                    grid-template-columns: 1fr;
                }
                .cta-button {
                    display: block;
                    margin: 10px 0;
                }
            }
        </style>
    </head>
    <body class="<?php echo $current_lang; ?>">
        <div class="email-container">
            <div class="header">
                <h1><?php echo SITE_NAME; ?></h1>
                <p><?php echo $current_lang === 'ar' ? 'شريكك الموثوق في العقارات' : 'Your Trusted Real Estate Partner'; ?></p>
            </div>
            
            <div class="content">
                <div class="welcome-message">
                    <h2><?php echo $current_lang === 'ar' ? 'مرحباً ' . htmlspecialchars($user_data['full_name']) . '!' : 'Welcome ' . htmlspecialchars($user_data['full_name']) . '!'; ?></h2>
                    <p><?php echo $current_lang === 'ar' 
                        ? 'نحن سعداء جداً بانضمامك إلى مجتمع العمراني للعقارات. الآن يمكنك الاستفادة من جميع خدماتنا المميزة.'
                        : 'We are thrilled to welcome you to the Alamrani Real Estate community. You can now access all our premium services.'; ?></p>
                </div>
                
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">🏠</div>
                        <h4><?php echo $current_lang === 'ar' ? 'تصفح العقارات' : 'Browse Properties'; ?></h4>
                        <p><?php echo $current_lang === 'ar' 
                            ? 'اكتشف مئات العقارات المتميزة في جميع أنحاء اليمن'
                            : 'Discover hundreds of premium properties throughout Yemen'; ?></p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">❤️</div>
                        <h4><?php echo $current_lang === 'ar' ? 'المفضلة' : 'Favorites'; ?></h4>
                        <p><?php echo $current_lang === 'ar' 
                            ? 'احفظ العقارات المفضلة لديك وتابعها بسهولة'
                            : 'Save your favorite properties and track them easily'; ?></p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">📞</div>
                        <h4><?php echo $current_lang === 'ar' ? 'تواصل مباشر' : 'Direct Contact'; ?></h4>
                        <p><?php echo $current_lang === 'ar' 
                            ? 'تواصل مباشرة مع الوكلاء واحجز معاينات العقارات'
                            : 'Contact agents directly and schedule property viewings'; ?></p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">🔔</div>
                        <h4><?php echo $current_lang === 'ar' ? 'تنبيهات ذكية' : 'Smart Alerts'; ?></h4>
                        <p><?php echo $current_lang === 'ar' 
                            ? 'احصل على تنبيهات فورية عند توفر عقارات تناسب اهتماماتك'
                            : 'Get instant alerts when properties matching your interests become available'; ?></p>
                    </div>
                </div>
                
                <div class="cta-section">
                    <h3><?php echo $current_lang === 'ar' ? 'ابدأ رحلتك العقارية الآن' : 'Start Your Real Estate Journey Now'; ?></h3>
                    <p><?php echo $current_lang === 'ar' 
                        ? 'استكشف مجموعتنا الواسعة من العقارات المميزة أو تواصل مع فريق الخبراء لدينا'
                        : 'Explore our extensive collection of premium properties or connect with our expert team'; ?></p>
                    
                    <div style="margin: 20px 0;">
                        <a href="<?php echo SITE_URL; ?>/properties.php" class="cta-button">
                            <?php echo $current_lang === 'ar' ? 'تصفح العقارات' : 'Browse Properties'; ?>
                        </a>
                        <a href="<?php echo SITE_URL; ?>/agents.php" class="cta-button secondary">
                            <?php echo $current_lang === 'ar' ? 'تواصل مع الوكلاء' : 'Contact Agents'; ?>
                        </a>
                    </div>
                </div>
                
                <div class="contact-info">
                    <h4><?php echo $current_lang === 'ar' ? 'معلومات التواصل' : 'Contact Information'; ?></h4>
                    
                    <div class="contact-item">
                        <div class="contact-icon">📍</div>
                        <span><?php echo $current_lang === 'ar' ? 'شارع الستين، صنعاء، اليمن' : 'Sixty Street, Sana\'a, Yemen'; ?></span>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">📞</div>
                        <span>+967-1-234567</span>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">✉️</div>
                        <span><?php echo ADMIN_EMAIL; ?></span>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">🕒</div>
                        <span><?php echo $current_lang === 'ar' ? 'السبت - الخميس: 8:00 ص - 6:00 م' : 'Saturday - Thursday: 8:00 AM - 6:00 PM'; ?></span>
                    </div>
                </div>
                
                <div style="background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center;">
                    <h4 style="color: #2e7d32; margin-bottom: 10px;">
                        <?php echo $current_lang === 'ar' ? '💡 نصيحة مفيدة' : '💡 Helpful Tip'; ?>
                    </h4>
                    <p style="margin: 0; color: #2e7d32;">
                        <?php echo $current_lang === 'ar' 
                            ? 'استخدم خاصية البحث المتقدم لتضييق نتائج البحث وفقاً لاحتياجاتك المحددة مثل السعر والموقع وعدد الغرف.'
                            : 'Use the advanced search feature to narrow down results based on your specific needs like price, location, and number of rooms.'; ?>
                    </p>
                </div>
            </div>
            
            <div class="footer">
                <div class="social-links">
                    <a href="https://facebook.com/alamrani-realestate">Facebook</a>
                    <a href="https://instagram.com/alamrani-realestate">Instagram</a>
                    <a href="https://twitter.com/alamrani_re">Twitter</a>
                </div>
                
                <p>
                    <?php echo $current_lang === 'ar' ? 'تم إرسال هذه الرسالة إلى' : 'This email was sent to'; ?> 
                    <strong><?php echo htmlspecialchars($user_data['email']); ?></strong>
                </p>
                
                <p style="font-size: 12px; color: #999; margin-top: 20px;">
                    <?php echo $current_lang === 'ar' 
                        ? 'إذا لم تقم بإنشاء هذا الحساب، يرجى تجاهل هذه الرسالة أو التواصل معنا.'
                        : 'If you did not create this account, please ignore this email or contact us.'; ?>
                </p>
                
                <p style="margin-top: 20px;">
                    <small>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. <?php echo $current_lang === 'ar' ? 'جميع الحقوق محفوظة.' : 'All rights reserved.'; ?></small>
                </p>
            </div>
        </div>
    </body>
    </html>
    <?php
    
    $html_content = ob_get_clean();
    
    return [
        'subject' => $subject,
        'html' => $html_content,
        'text' => strip_tags($html_content)
    ];
}

/**
 * Send welcome email to new user
 */
function send_welcome_email($user_id) {
    global $db;
    
    // Get user data
    $stmt = $db->prepare("SELECT full_name, email FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch();
    
    if (!$user_data) {
        throw new Exception('User not found');
    }
    
    $email_content = generate_welcome_email($user_data);
    
    // Send email
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . SMTP_FROM_NAME . ' <' . SMTP_FROM_EMAIL . '>',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    $success = mail(
        $user_data['email'],
        $email_content['subject'],
        $email_content['html'],
        implode("\r\n", $headers)
    );
    
    if (!$success) {
        throw new Exception('Failed to send welcome email');
    }
    
    return true;
}
?>