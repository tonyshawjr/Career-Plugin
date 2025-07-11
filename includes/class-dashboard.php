<?php
/**
 * Careers Dashboard Handler
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CareersDashboard {
    
    public function __construct() {
        add_action('wp_ajax_careers_dashboard_action', array($this, 'handle_dashboard_action'));
    }
    
    /**
     * Handle dashboard AJAX actions
     */
    public function handle_dashboard_action() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'careers_nonce')) {
            wp_die(__('Security check failed.', 'careers-manager'));
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(__('You must be logged in.', 'careers-manager'));
        }
        
        $action = sanitize_text_field($_POST['dashboard_action']);
        
        switch ($action) {
            case 'withdraw_application':
                $this->withdraw_application();
                break;
            default:
                wp_send_json_error(__('Invalid action.', 'careers-manager'));
        }
    }
    
    /**
     * Withdraw application
     */
    private function withdraw_application() {
        $application_id = intval($_POST['application_id']);
        $user_id = get_current_user_id();
        
        // Get application and verify ownership
        $application = CareersApplicationDB::get_application($application_id);
        
        if (!$application || $application->user_id != $user_id) {
            wp_send_json_error(__('Application not found.', 'careers-manager'));
        }
        
        // Only allow withdrawal of pending applications
        if ($application->status !== 'pending') {
            wp_send_json_error(__('You can only withdraw pending applications.', 'careers-manager'));
        }
        
        // Update status to cancelled
        $result = CareersApplicationDB::update_status($application_id, 'rejected');
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        wp_send_json_success(__('Application withdrawn successfully.', 'careers-manager'));
    }
    
    /**
     * Get user dashboard data
     */
    public static function get_dashboard_data($user_id) {
        $applications = CareersApplicationDB::get_user_applications($user_id);
        
        $stats = array(
            'total_applications' => count($applications),
            'pending' => 0,
            'reviewed' => 0,
            'interviewing' => 0,
            'hired' => 0,
            'rejected' => 0
        );
        
        foreach ($applications as $application) {
            if (isset($stats[$application->status])) {
                $stats[$application->status]++;
            }
        }
        
        return array(
            'applications' => $applications,
            'stats' => $stats
        );
    }
    
    /**
     * Render dashboard stats
     */
    public static function render_dashboard_stats($stats) {
        ob_start();
        ?>
        <div class="careers-dashboard-stats">
            <div class="careers-stat-card">
                <div class="careers-stat-number"><?php echo intval($stats['total_applications']); ?></div>
                <div class="careers-stat-label"><?php _e('Total Applications', 'careers-manager'); ?></div>
            </div>
            
            <div class="careers-stat-card">
                <div class="careers-stat-number"><?php echo intval($stats['pending']); ?></div>
                <div class="careers-stat-label"><?php _e('Pending', 'careers-manager'); ?></div>
            </div>
            
            <div class="careers-stat-card">
                <div class="careers-stat-number"><?php echo intval($stats['interviewing']); ?></div>
                <div class="careers-stat-label"><?php _e('Interviewing', 'careers-manager'); ?></div>
            </div>
            
            <div class="careers-stat-card">
                <div class="careers-stat-number"><?php echo intval($stats['hired']); ?></div>
                <div class="careers-stat-label"><?php _e('Hired', 'careers-manager'); ?></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render application timeline
     */
    public static function render_application_timeline($application) {
        $statuses = array(
            'pending' => __('Application Submitted', 'careers-manager'),
            'reviewed' => __('Application Reviewed', 'careers-manager'),
            'interviewing' => __('Interview Stage', 'careers-manager'),
            'hired' => __('Hired', 'careers-manager'),
            'rejected' => __('Application Closed', 'careers-manager')
        );
        
        ob_start();
        ?>
        <div class="careers-application-timeline">
            <?php foreach ($statuses as $status => $label): ?>
                <div class="careers-timeline-item <?php echo ($application->status === $status) ? 'active' : ''; ?> <?php echo (array_search($status, array_keys($statuses)) < array_search($application->status, array_keys($statuses))) ? 'completed' : ''; ?>">
                    <div class="careers-timeline-marker"></div>
                    <div class="careers-timeline-content">
                        <div class="careers-timeline-label"><?php echo esc_html($label); ?></div>
                        <?php if ($application->status === $status): ?>
                            <div class="careers-timeline-date"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($application->updated_at))); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
} 