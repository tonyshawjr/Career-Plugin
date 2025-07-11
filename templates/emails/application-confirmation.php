<?php
/**
 * Application Confirmation Email Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
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
        .job-details { background: #f8f9fa; padding: 20px; border-radius: 4px; margin: 20px 0; }
        .highlight { color: #007cba; font-weight: bold; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1><?php echo esc_html($site_name); ?></h1>
            <h2 style="color: #007cba; margin: 10px 0;"><?php _e('Application Confirmation', 'careers-manager'); ?></h2>
        </div>
        
        <div class="email-content">
            <p><?php printf(__('Hi %s,', 'careers-manager'), esc_html($user->first_name)); ?></p>
            
            <p><?php printf(__('Thank you for applying for the position of <span class="highlight">%s</span> at %s.', 'careers-manager'), esc_html($job->post_title), esc_html($site_name)); ?></p>
            
            <div class="job-details">
                <h3><?php _e('Application Details', 'careers-manager'); ?></h3>
                <p><strong><?php _e('Position:', 'careers-manager'); ?></strong> <?php echo esc_html($job->post_title); ?></p>
                <p><strong><?php _e('Application Date:', 'careers-manager'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($application->submitted_at))); ?></p>
                <p><strong><?php _e('Status:', 'careers-manager'); ?></strong> <span class="highlight"><?php _e('Under Review', 'careers-manager'); ?></span></p>
            </div>
            
            <p><?php _e('We have received your application and will review it carefully. Our team will be in touch with you regarding the next steps in the process.', 'careers-manager'); ?></p>
            
            <p><?php _e('In the meantime, you can track the status of your application and view all your submitted applications in your dashboard.', 'careers-manager'); ?></p>
            
            <p style="text-align: center;">
                <a href="<?php echo esc_url($dashboard_url); ?>" class="button"><?php _e('View Dashboard', 'careers-manager'); ?></a>
            </p>
            
            <p><?php _e('If you have any questions about your application or our hiring process, please don\'t hesitate to contact us.', 'careers-manager'); ?></p>
            
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