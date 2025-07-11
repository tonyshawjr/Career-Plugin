<?php
/**
 * Careers Email Handler
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CareersEmails {
    
    public function __construct() {
        add_action('careers_application_submitted', array($this, 'send_application_confirmation'), 10, 1);
        add_action('careers_application_status_changed', array($this, 'send_status_update'), 10, 2);
    }
    
    /**
     * Send application confirmation email to applicant
     */
    public static function send_application_confirmation($application_id) {
        $application = CareersApplicationDB::get_application($application_id);
        
        if (!$application) {
            return false;
        }
        
        $user = get_user_by('id', $application->user_id);
        $job = get_post($application->job_id);
        
        if (!$user || !$job) {
            return false;
        }
        
        $subject = sprintf(__('Application Confirmation - %s', 'careers-manager'), $job->post_title);
        
        $template_data = array(
            'user' => $user,
            'job' => $job,
            'application' => $application,
            'site_name' => get_bloginfo('name'),
            'site_url' => home_url(),
            'dashboard_url' => home_url('/dashboard/')
        );
        
        $message = self::get_email_template('application-confirmation', $template_data);
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        return wp_mail($user->user_email, $subject, $message, $headers);
    }
    
    /**
     * Send admin notification email
     */
    public static function send_admin_notification($application_id) {
        $application = CareersApplicationDB::get_application($application_id);
        
        if (!$application) {
            return false;
        }
        
        $user = get_user_by('id', $application->user_id);
        $job = get_post($application->job_id);
        
        if (!$user || !$job) {
            return false;
        }
        
        $admin_email = get_option('admin_email');
        $subject = sprintf(__('New Job Application - %s', 'careers-manager'), $job->post_title);
        
        $message = sprintf(
            __('A new job application has been submitted.

Job: %s
Applicant: %s (%s)
Date: %s

View Application: %s

Best regards,
%s', 'careers-manager'),
            $job->post_title,
            $user->display_name,
            $user->user_email,
            date_i18n(get_option('date_format'), strtotime($application->submitted_at)),
            admin_url('admin.php?page=careers-applications'),
            get_bloginfo('name')
        );
        
        return wp_mail($admin_email, $subject, $message);
    }
    
    /**
     * Send status update email to applicant
     */
    public static function send_status_update($application_id, $new_status) {
        $application = CareersApplicationDB::get_application($application_id);
        
        if (!$application) {
            return false;
        }
        
        $user = get_user_by('id', $application->user_id);
        $job = get_post($application->job_id);
        
        if (!$user || !$job) {
            return false;
        }
        
        $statuses = careers_get_application_statuses();
        $status_label = isset($statuses[$new_status]) ? $statuses[$new_status] : $new_status;
        
        $subject = sprintf(__('Application Update - %s', 'careers-manager'), $job->post_title);
        
        $template_data = array(
            'user' => $user,
            'job' => $job,
            'application' => $application,
            'new_status' => $new_status,
            'status_label' => $status_label,
            'site_name' => get_bloginfo('name'),
            'site_url' => home_url(),
            'dashboard_url' => home_url('/dashboard/')
        );
        
        $message = self::get_email_template('status-update', $template_data);
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        return wp_mail($user->user_email, $subject, $message, $headers);
    }
    
    /**
     * Get email template
     */
    private static function get_email_template($template_name, $data = array()) {
        $template_path = CAREERS_PLUGIN_PATH . 'templates/emails/' . $template_name . '.php';
        
        if (file_exists($template_path)) {
            extract($data);
            ob_start();
            include $template_path;
            return ob_get_clean();
        }
        
        // Fallback to basic template
        return self::get_basic_email_template($template_name, $data);
    }
    
    /**
     * Get basic email template as fallback
     */
    private static function get_basic_email_template($template_name, $data) {
        switch ($template_name) {
            case 'application-confirmation':
                return sprintf(
                    __('Hi %s,

Thank you for applying for the position of %s at %s.

We have received your application and will review it shortly. You can track the status of your application in your dashboard: %s

Best regards,
%s Team', 'careers-manager'),
                    $data['user']->first_name,
                    $data['job']->post_title,
                    $data['site_name'],
                    $data['dashboard_url'],
                    $data['site_name']
                );
                
            case 'status-update':
                return sprintf(
                    __('Hi %s,

Your application for %s has been updated.

New Status: %s

You can view more details in your dashboard: %s

Best regards,
%s Team', 'careers-manager'),
                    $data['user']->first_name,
                    $data['job']->post_title,
                    $data['status_label'],
                    $data['dashboard_url'],
                    $data['site_name']
                );
                
            default:
                return __('Thank you for your interest.', 'careers-manager');
        }
    }
    
    /**
     * Send custom email
     */
    public static function send_custom_email($to, $subject, $message, $template_data = array()) {
        if (empty($to) || empty($subject) || empty($message)) {
            return false;
        }
        
        // Replace template variables
        $message = self::replace_template_variables($message, $template_data);
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        return wp_mail($to, $subject, $message, $headers);
    }
    
    /**
     * Replace template variables in message
     */
    private static function replace_template_variables($message, $data) {
        $variables = array(
            '{site_name}' => get_bloginfo('name'),
            '{site_url}' => home_url(),
            '{dashboard_url}' => home_url('/dashboard/'),
            '{jobs_url}' => home_url('/jobs/'),
        );
        
        // Add data variables
        if (isset($data['user'])) {
            $variables['{user_name}'] = $data['user']->display_name;
            $variables['{first_name}'] = $data['user']->first_name;
            $variables['{last_name}'] = $data['user']->last_name;
            $variables['{user_email}'] = $data['user']->user_email;
        }
        
        if (isset($data['job'])) {
            $variables['{job_title}'] = $data['job']->post_title;
            $variables['{job_url}'] = get_permalink($data['job']->ID);
        }
        
        if (isset($data['application'])) {
            $variables['{application_date}'] = date_i18n(get_option('date_format'), strtotime($data['application']->submitted_at));
            $variables['{application_status}'] = $data['application']->status;
        }
        
        return str_replace(array_keys($variables), array_values($variables), $message);
    }
    
    /**
     * Get email header HTML
     */
    private static function get_email_header() {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>' . get_bloginfo('name') . '</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .email-container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .email-header { background: #f8f9fa; padding: 20px; text-align: center; border-bottom: 2px solid #007cba; }
                .email-content { padding: 30px 20px; }
                .email-footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
                .button { display: inline-block; padding: 12px 24px; background: #007cba; color: white; text-decoration: none; border-radius: 4px; }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="email-header">
                    <h1>' . get_bloginfo('name') . '</h1>
                </div>
                <div class="email-content">';
    }
    
    /**
     * Get email footer HTML
     */
    private static function get_email_footer() {
        return '
                </div>
                <div class="email-footer">
                    <p>&copy; ' . date('Y') . ' ' . get_bloginfo('name') . '. All rights reserved.</p>
                    <p><a href="' . home_url() . '">' . home_url() . '</a></p>
                </div>
            </div>
        </body>
        </html>';
    }
} 