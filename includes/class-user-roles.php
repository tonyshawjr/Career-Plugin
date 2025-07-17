<?php
/**
 * User Role Management for Careers Plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CareersUserRoles {
    
    public function __construct() {
        // Hook into user registration to set default role
        add_action('user_register', array($this, 'set_default_role_on_registration'));
        
        // Add role capabilities
        add_action('init', array($this, 'add_role_capabilities'));
        
        // Handle role switching for existing users
        add_action('wp_ajax_careers_change_user_role', array($this, 'change_user_role'));
        
        // Hide admin bar for career_admin role
        add_action('after_setup_theme', array($this, 'hide_admin_bar_for_recruiters'));
        
        // Prevent wp-admin access for career_admin role
        add_action('admin_init', array($this, 'restrict_admin_access'));
        
        // Redirect after login for career_admin role
        add_filter('login_redirect', array($this, 'redirect_after_login'), 10, 3);
    }
    
    /**
     * Set default role for new user registrations through careers system
     */
    public function set_default_role_on_registration($user_id) {
        // Check if this registration came from the careers system
        if (isset($_POST['careers_registration']) && $_POST['careers_registration'] === '1') {
            $user = get_user_by('id', $user_id);
            $user->set_role('applicant');
        }
    }
    
    /**
     * Add additional capabilities to roles
     */
    public function add_role_capabilities() {
        // Get the roles
        $applicant_role = get_role('applicant');
        $career_admin_role = get_role('career_admin');
        $admin_role = get_role('administrator');
        
        // Add capabilities to applicant role
        if ($applicant_role) {
            $applicant_capabilities = array(
                'apply_for_jobs',
                'view_own_applications',
                'edit_own_profile',
                'upload_resume',
                'view_job_listings'
            );
            
            foreach ($applicant_capabilities as $cap) {
                $applicant_role->add_cap($cap);
            }
        }
        
        // Add capabilities to career admin role
        if ($career_admin_role) {
            $career_admin_capabilities = array(
                'manage_jobs',
                'create_jobs',
                'edit_jobs',
                'delete_jobs',
                'view_all_applications',
                'edit_application_status',
                'send_applicant_emails',
                'view_career_analytics',
                'export_applications',
                'manage_applicants'
            );
            
            foreach ($career_admin_capabilities as $cap) {
                $career_admin_role->add_cap($cap);
            }
        }
        
        // Ensure administrators have all career capabilities
        if ($admin_role) {
            $all_career_capabilities = array(
                'apply_for_jobs',
                'view_own_applications',
                'edit_own_profile',
                'upload_resume',
                'view_job_listings',
                'manage_jobs',
                'create_jobs',
                'edit_jobs',
                'delete_jobs',
                'view_all_applications',
                'edit_application_status',
                'send_applicant_emails',
                'view_career_analytics',
                'export_applications',
                'manage_applicants'
            );
            
            foreach ($all_career_capabilities as $cap) {
                $admin_role->add_cap($cap);
            }
        }
    }
    
    /**
     * Check if current user has specific career capability
     */
    public static function current_user_can_manage_careers() {
        return current_user_can('manage_jobs') || current_user_can('administrator');
    }
    
    /**
     * Check if current user is an applicant
     */
    public static function current_user_is_applicant() {
        $user = wp_get_current_user();
        return in_array('applicant', $user->roles);
    }
    
    /**
     * Check if current user is a career admin
     */
    public static function current_user_is_career_admin() {
        $user = wp_get_current_user();
        return in_array('career_admin', $user->roles) || in_array('administrator', $user->roles);
    }
    
    /**
     * Get user's dashboard type based on role
     */
    public static function get_user_dashboard_type() {
        if (self::current_user_is_career_admin()) {
            return 'admin';
        } elseif (self::current_user_is_applicant()) {
            return 'applicant';
        }
        return 'guest';
    }
    
    /**
     * Change user role via AJAX (for administrators only)
     */
    public function change_user_role() {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'], 'careers_nonce') || !current_user_can('manage_options')) {
            wp_die(__('Security check failed.', 'careers-manager'));
        }
        
        $user_id = intval($_POST['user_id']);
        $new_role = sanitize_text_field($_POST['new_role']);
        
        // Validate role
        $allowed_roles = array('applicant', 'career_admin', 'administrator');
        if (!in_array($new_role, $allowed_roles)) {
            wp_send_json_error(__('Invalid role specified.', 'careers-manager'));
        }
        
        $user = get_user_by('id', $user_id);
        if (!$user) {
            wp_send_json_error(__('User not found.', 'careers-manager'));
        }
        
        // Change the role
        $user->set_role($new_role);
        
        wp_send_json_success(__('User role updated successfully.', 'careers-manager'));
    }
    
    /**
     * Create a career admin user
     */
    public static function create_career_admin($email, $password, $first_name = '', $last_name = '') {
        $username = sanitize_user($email);
        
        $user_id = wp_create_user($username, $password, $email);
        
        if (is_wp_error($user_id)) {
            return $user_id;
        }
        
        // Set role to career_admin
        $user = get_user_by('id', $user_id);
        $user->set_role('career_admin');
        
        // Set additional user meta
        if ($first_name) {
            update_user_meta($user_id, 'first_name', sanitize_text_field($first_name));
        }
        if ($last_name) {
            update_user_meta($user_id, 'last_name', sanitize_text_field($last_name));
        }
        
        return $user_id;
    }
    
    /**
     * Get all users by career role
     */
    public static function get_users_by_career_role($role = 'applicant') {
        return get_users(array(
            'role' => $role,
            'orderby' => 'registered',
            'order' => 'DESC'
        ));
    }
    
    /**
     * Hide admin bar for career_admin users
     */
    public function hide_admin_bar_for_recruiters() {
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            if (in_array('career_admin', (array) $user->roles)) {
                show_admin_bar(false);
            }
        }
    }
    
    /**
     * Restrict wp-admin access for career_admin users
     */
    public function restrict_admin_access() {
        if (!defined('DOING_AJAX') || !DOING_AJAX) {
            $user = wp_get_current_user();
            if (in_array('career_admin', (array) $user->roles) && !in_array('administrator', (array) $user->roles)) {
                // Redirect to careers dashboard
                wp_redirect(CareersSettings::get_page_url('dashboard'));
                exit;
            }
        }
    }
    
    /**
     * Redirect career_admin users to dashboard after login
     */
    public function redirect_after_login($redirect_to, $request, $user) {
        if (isset($user->roles) && is_array($user->roles)) {
            if (in_array('career_admin', $user->roles) && !in_array('administrator', $user->roles)) {
                return CareersSettings::get_page_url('dashboard');
            }
        }
        return $redirect_to;
    }
    
    /**
     * Create career roles on plugin activation
     */
    public function create_roles() {
        // Create applicant role
        if (!get_role('applicant')) {
            add_role('applicant', __('Job Applicant', 'careers-manager'), array(
                'read' => true,
                'edit_posts' => false,
                'delete_posts' => false,
                'apply_for_jobs' => true,
                'view_own_applications' => true,
                'edit_own_profile' => true,
            ));
        }
        
        // Create career_admin role (Recruiter)
        if (!get_role('career_admin')) {
            add_role('career_admin', __('Recruiter', 'careers-manager'), array(
                'read' => true,
                'edit_posts' => false,
                'delete_posts' => false,
                'manage_jobs' => true,
                'view_all_applications' => true,
                'edit_application_status' => true,
                'send_applicant_emails' => true,
                'view_career_analytics' => true,
                'export_applications' => true,
            ));
        }
    }
    
    /**
     * Remove career roles on plugin deactivation
     */
    public static function remove_career_roles() {
        remove_role('applicant');
        remove_role('career_admin');
    }
}