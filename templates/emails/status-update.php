<?php
/**
 * Status Update Email Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$status_messages = array(
    'reviewed' => __('Great news! Your application has been reviewed by our team and you\'ve moved to the next stage in our hiring process.', 'careers-manager'),
    'interviewing' => __('Congratulations! We\'d like to invite you for an interview. Our team will be in touch with you soon to schedule a time that works for you.', 'careers-manager'),
    'hired' => __('Congratulations! We\'re excited to offer you the position. Welcome to the team! Our HR department will contact you with next steps.', 'careers-manager'),
    'rejected' => __('Thank you for your interest in this position. While we were impressed with your qualifications, we\'ve decided to move forward with other candidates. We encourage you to apply for future opportunities.', 'careers-manager')
);

$status_message = isset($status_messages[$new_status]) ? $status_messages[$new_status] : __('Your application status has been updated.', 'careers-manager');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($site_name); ?></title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .email-container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .email-header { background: #f8f9fa; padding: 30px; text-align: center; border-bottom: 3px solid #007cba; }
        .email-content { padding: 30px 20px; }
        .email-footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .button { display: inline-block; padding: 12px 24px; background: #007cba; color: white; text-decoration: none; border-radius: 4px; margin: 10px 0; }
        .status-update { background: #f8f9fa; padding: 20px; border-radius: 4px; margin: 20px 0; text-align: center; }
        .status-badge { display: inline-block; padding: 8px 16px; border-radius: 20px; font-weight: bold; text-transform: uppercase; font-size: 12px; }
        .status-new { background: #ffc107; color: #856404; }
        .status-under_review { background: #17a2b8; color: white; }
        .status-contacted { background: #6f42c1; color: white; }
        .status-interview { background: #fd7e14; color: white; }
        .status-hired { background: #28a745; color: white; }
        .status-rejected { background: #dc3545; color: white; }
        .highlight { color: #007cba; font-weight: bold; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1><?php echo esc_html($site_name); ?></h1>
            <h2 style="color: #007cba; margin: 10px 0;"><?php _e('Application Update', 'careers-manager'); ?></h2>
        </div>
        
        <div class="email-content">
            <p><?php printf(__('Hi %s,', 'careers-manager'), esc_html($user->first_name)); ?></p>
            
            <p><?php printf(__('We have an update regarding your application for the position of <span class="highlight">%s</span>.', 'careers-manager'), esc_html($job->post_title)); ?></p>
            
            <div class="status-update">
                <h3><?php _e('Status Update', 'careers-manager'); ?></h3>
                <p><span class="status-badge status-<?php echo esc_attr($new_status); ?>"><?php echo esc_html($status_label); ?></span></p>
            </div>
            
            <p><?php echo $status_message; ?></p>
            
            <?php if ($new_status === 'interviewing'): ?>
                <p><strong><?php _e('Next Steps:', 'careers-manager'); ?></strong></p>
                <ul>
                    <li><?php _e('A member of our team will contact you within the next 2-3 business days', 'careers-manager'); ?></li>
                    <li><?php _e('Please have your calendar ready to schedule the interview', 'careers-manager'); ?></li>
                    <li><?php _e('Feel free to prepare any questions you might have about the role', 'careers-manager'); ?></li>
                </ul>
            <?php elseif ($new_status === 'hired'): ?>
                <p><strong><?php _e('Next Steps:', 'careers-manager'); ?></strong></p>
                <ul>
                    <li><?php _e('Our HR department will contact you within 24 hours', 'careers-manager'); ?></li>
                    <li><?php _e('Please be ready to discuss start date and compensation', 'careers-manager'); ?></li>
                    <li><?php _e('We\'ll send you all necessary onboarding documents', 'careers-manager'); ?></li>
                </ul>
            <?php endif; ?>
            
            <p><?php _e('You can always check the latest status of your application in your dashboard.', 'careers-manager'); ?></p>
            
            <p style="text-align: center;">
                <a href="<?php echo esc_url($dashboard_url); ?>" class="button"><?php _e('View Dashboard', 'careers-manager'); ?></a>
            </p>
            
            <?php if ($new_status !== 'rejected'): ?>
                <p><?php _e('If you have any questions about the next steps or the position, please don\'t hesitate to reach out to us.', 'careers-manager'); ?></p>
            <?php else: ?>
                <p><?php _e('We appreciate the time you invested in the application process and wish you the best in your job search.', 'careers-manager'); ?></p>
            <?php endif; ?>
            
            <p><?php _e('Best regards,', 'careers-manager'); ?><br>
            <?php printf(__('The %s Team', 'careers-manager'), esc_html($site_name)); ?></p>
        </div>
        
        <div class="email-footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo esc_html($site_name); ?>. <?php _e('All rights reserved.', 'careers-manager'); ?></p>
            <p><a href="<?php echo esc_url($site_url); ?>"><?php echo esc_url($site_url); ?></a></p>
        </div>
    </div>
</body>
</html> 