<?php
/**
 * Email Template: Property Inquiry Notification
 * Sent to agents when a new inquiry is received
 */

function generate_inquiry_email($inquiry_data, $property_data, $agent_data) {
    $current_lang = get_current_language();
    
    $subject = $current_lang === 'ar' 
        ? 'استفسار جديد عن العقار: ' . $property_data['title_ar']
        : 'New Property Inquiry: ' . $property_data['title'];
    
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
                padding: 30px 20px;
                text-align: center;
                border-radius: 10px 10px 0 0;
            }
            .header h1 {
                margin: 0;
                font-size: 24px;
            }
            .content {
                background: white;
                padding: 30px 20px;
                border: 1px solid #ddd;
                border-top: none;
            }
            .property-info {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 8px;
                margin: 20px 0;
                border-left: 4px solid #4FC3F7;
            }
            .ar .property-info {
                border-left: none;
                border-right: 4px solid #4FC3F7;
            }
            .inquiry-details {
                background: #fff;
                border: 1px solid #e9ecef;
                border-radius: 8px;
                padding: 20px;
                margin: 20px 0;
            }
            .detail-row {
                display: flex;
                justify-content: space-between;
                padding: 10px 0;
                border-bottom: 1px solid #f0f0f0;
            }
            .detail-row:last-child {
                border-bottom: none;
            }
            .detail-label {
                font-weight: bold;
                color: #0B6E79;
                flex: 0 0 40%;
            }
            .detail-value {
                flex: 1;
                text-align: <?php echo $current_lang === 'ar' ? 'left' : 'right'; ?>;
            }
            .ar .detail-value {
                text-align: right;
            }
            .cta-button {
                display: inline-block;
                background: #4FC3F7;
                color: white;
                padding: 12px 24px;
                text-decoration: none;
                border-radius: 6px;
                margin: 20px 0;
                font-weight: bold;
            }
            .footer {
                background: #f8f9fa;
                padding: 20px;
                text-align: center;
                color: #666;
                font-size: 14px;
                border-radius: 0 0 10px 10px;
            }
            .property-image {
                width: 100%;
                max-width: 300px;
                height: 200px;
                object-fit: cover;
                border-radius: 8px;
                margin: 15px 0;
            }
            @media (max-width: 600px) {
                body {
                    padding: 10px;
                }
                .header, .content, .footer {
                    padding: 20px 15px;
                }
            }
        </style>
    </head>
    <body class="<?php echo $current_lang; ?>">
        <div class="email-container">
            <div class="header">
                <h1><?php echo SITE_NAME; ?></h1>
                <p><?php echo $current_lang === 'ar' ? 'استفسار عقاري جديد' : 'New Property Inquiry'; ?></p>
            </div>
            
            <div class="content">
                <h2><?php echo $current_lang === 'ar' ? 'مرحباً ' . htmlspecialchars($agent_data['name']) : 'Hello ' . htmlspecialchars($agent_data['name']); ?>,</h2>
                
                <p><?php echo $current_lang === 'ar' ? 'لقد تم استلام استفسار جديد عن أحد العقارات الخاصة بك:' : 'You have received a new inquiry about one of your properties:'; ?></p>
                
                <div class="property-info">
                    <h3><?php echo $current_lang === 'ar' ? 'تفاصيل العقار' : 'Property Details'; ?></h3>
                    
                    <?php if (!empty($property_data['main_image'])): ?>
                    <img src="<?php echo SITE_URL . '/' . $property_data['main_image']; ?>" alt="Property Image" class="property-image">
                    <?php endif; ?>
                    
                    <div class="detail-row">
                        <span class="detail-label"><?php echo $current_lang === 'ar' ? 'العنوان:' : 'Title:'; ?></span>
                        <span class="detail-value"><?php echo htmlspecialchars($current_lang === 'ar' ? $property_data['title_ar'] : $property_data['title']); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label"><?php echo $current_lang === 'ar' ? 'الموقع:' : 'Location:'; ?></span>
                        <span class="detail-value"><?php echo htmlspecialchars($property_data['city']); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label"><?php echo $current_lang === 'ar' ? 'السعر:' : 'Price:'; ?></span>
                        <span class="detail-value"><?php echo format_price($property_data['price'], $property_data['currency']); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label"><?php echo $current_lang === 'ar' ? 'النوع:' : 'Type:'; ?></span>
                        <span class="detail-value"><?php echo ucfirst($property_data['property_type']); ?> - <?php echo ucfirst($property_data['type']); ?></span>
                    </div>
                </div>
                
                <div class="inquiry-details">
                    <h3><?php echo $current_lang === 'ar' ? 'تفاصيل المستفسر' : 'Inquirer Details'; ?></h3>
                    
                    <div class="detail-row">
                        <span class="detail-label"><?php echo $current_lang === 'ar' ? 'الاسم:' : 'Name:'; ?></span>
                        <span class="detail-value"><?php echo htmlspecialchars($inquiry_data['name']); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label"><?php echo $current_lang === 'ar' ? 'البريد الإلكتروني:' : 'Email:'; ?></span>
                        <span class="detail-value"><a href="mailto:<?php echo htmlspecialchars($inquiry_data['email']); ?>"><?php echo htmlspecialchars($inquiry_data['email']); ?></a></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label"><?php echo $current_lang === 'ar' ? 'رقم الهاتف:' : 'Phone:'; ?></span>
                        <span class="detail-value"><a href="tel:<?php echo htmlspecialchars($inquiry_data['phone']); ?>"><?php echo htmlspecialchars($inquiry_data['phone']); ?></a></span>
                    </div>
                    
                    <?php if (!empty($inquiry_data['preferred_viewing_date'])): ?>
                    <div class="detail-row">
                        <span class="detail-label"><?php echo $current_lang === 'ar' ? 'تاريخ المعاينة المفضل:' : 'Preferred Viewing Date:'; ?></span>
                        <span class="detail-value"><?php echo date('Y-m-d', strtotime($inquiry_data['preferred_viewing_date'])); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($inquiry_data['preferred_contact_time'])): ?>
                    <div class="detail-row">
                        <span class="detail-label"><?php echo $current_lang === 'ar' ? 'وقت التواصل المفضل:' : 'Preferred Contact Time:'; ?></span>
                        <span class="detail-value"><?php echo htmlspecialchars($inquiry_data['preferred_contact_time']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($inquiry_data['message'])): ?>
                    <div class="detail-row">
                        <span class="detail-label"><?php echo $current_lang === 'ar' ? 'الرسالة:' : 'Message:'; ?></span>
                    </div>
                    <div style="margin-top: 10px; padding: 15px; background: #f8f9fa; border-radius: 6px; font-style: italic;">
                        "<?php echo nl2br(htmlspecialchars($inquiry_data['message'])); ?>"
                    </div>
                    <?php endif; ?>
                    
                    <div class="detail-row">
                        <span class="detail-label"><?php echo $current_lang === 'ar' ? 'تاريخ الاستفسار:' : 'Inquiry Date:'; ?></span>
                        <span class="detail-value"><?php echo date('Y-m-d H:i', strtotime($inquiry_data['created_at'])); ?></span>
                    </div>
                </div>
                
                <div style="text-align: center; margin: 30px 0;">
                    <a href="<?php echo SITE_URL; ?>/admin.php?page=inquiries" class="cta-button">
                        <?php echo $current_lang === 'ar' ? 'إدارة الاستفسارات' : 'Manage Inquiries'; ?>
                    </a>
                </div>
                
                <div style="background: #e3f2fd; padding: 15px; border-radius: 6px; margin: 20px 0;">
                    <p style="margin: 0; font-size: 14px; color: #1565c0;">
                        <strong><?php echo $current_lang === 'ar' ? 'نصيحة:' : 'Tip:'; ?></strong>
                        <?php echo $current_lang === 'ar' 
                            ? 'يُنصح بالرد على الاستفسارات في أقرب وقت ممكن لزيادة فرص إتمام الصفقة.' 
                            : 'It\'s recommended to respond to inquiries as soon as possible to increase the chances of closing the deal.'; ?>
                    </p>
                </div>
            </div>
            
            <div class="footer">
                <p>
                    <?php echo $current_lang === 'ar' ? 'هذه رسالة تلقائية من نظام' : 'This is an automated message from'; ?> 
                    <strong><?php echo SITE_NAME; ?></strong>
                </p>
                <p>
                    <?php echo $current_lang === 'ar' ? 'للاستفسارات:' : 'For inquiries:'; ?> 
                    <a href="mailto:<?php echo ADMIN_EMAIL; ?>"><?php echo ADMIN_EMAIL; ?></a>
                </p>
                <p style="font-size: 12px; color: #999; margin-top: 15px;">
                    <?php echo $current_lang === 'ar' 
                        ? 'إذا لم تعد ترغب في تلقي هذه الرسائل، يرجى التواصل مع الإدارة.' 
                        : 'If you no longer wish to receive these emails, please contact the administration.'; ?>
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
        'text' => strip_tags($html_content) // Simple text version
    ];
}

/**
 * Send inquiry notification email
 */
function send_inquiry_notification($inquiry_id) {
    global $db;
    
    // Get inquiry, property, and agent data
    $stmt = $db->prepare("
        SELECT i.*, p.*, a.name as agent_name, a.email as agent_email,
               (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY display_order LIMIT 1) as main_image
        FROM inquiries i
        JOIN properties p ON i.property_id = p.id
        JOIN agents a ON p.agent_id = a.id
        WHERE i.id = ?
    ");
    $stmt->execute([$inquiry_id]);
    $data = $stmt->fetch();
    
    if (!$data) {
        throw new Exception('Inquiry not found');
    }
    
    $inquiry_data = [
        'name' => $data['name'],
        'email' => $data['email'],
        'phone' => $data['phone'],
        'message' => $data['message'],
        'preferred_viewing_date' => $data['preferred_viewing_date'],
        'preferred_contact_time' => $data['preferred_contact_time'],
        'created_at' => $data['created_at']
    ];
    
    $property_data = [
        'title' => $data['title'],
        'title_ar' => $data['title_ar'],
        'city' => $data['city'],
        'price' => $data['price'],
        'currency' => $data['currency'],
        'property_type' => $data['property_type'],
        'type' => $data['type'],
        'main_image' => $data['main_image']
    ];
    
    $agent_data = [
        'name' => $data['agent_name'],
        'email' => $data['agent_email']
    ];
    
    $email_content = generate_inquiry_email($inquiry_data, $property_data, $agent_data);
    
    // Send email using PHP mail() or your preferred email service
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . SMTP_FROM_NAME . ' <' . SMTP_FROM_EMAIL . '>',
        'Reply-To: ' . $inquiry_data['email'],
        'X-Mailer: PHP/' . phpversion()
    ];
    
    $success = mail(
        $agent_data['email'],
        $email_content['subject'],
        $email_content['html'],
        implode("\r\n", $headers)
    );
    
    if (!$success) {
        throw new Exception('Failed to send email');
    }
    
    return true;
}
?>