<?php
/**
 * Careers Admin Interface
 * Frontend Dashboard Only - No WP-Admin Interface
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CareersAdmin {
    
    public function __construct() {
        // Add AJAX handlers for frontend job management
        add_action('wp_ajax_create_career_job', array($this, 'handle_job_creation'));
        add_action('wp_ajax_update_career_job', array($this, 'handle_job_update'));
        add_action('wp_ajax_delete_career_job', array($this, 'handle_job_deletion'));
        
        // Keep AJAX handler for any remaining backend needs
        add_action('wp_ajax_careers_admin_action', array($this, 'handle_admin_action'));
        
        // Add custom user roles
        add_action('init', array($this, 'add_custom_user_roles'));
        
        // Prevent non-admin users from accessing wp-admin
        add_action('admin_init', array($this, 'prevent_wp_admin_access'));
        
        // Redirect after login based on user role
        add_filter('login_redirect', array($this, 'custom_login_redirect'), 10, 3);
        
        // Force role creation immediately to ensure it happens
        $this->add_custom_user_roles();
    }
    
    /**
     * Add custom user roles for careers system
     */
    public function add_custom_user_roles() {
        // Only add roles if they don't exist
        if (!get_role('applicant')) {
            add_role('applicant', __('Job Applicant', 'careers-manager'), array(
                'read' => true,
                'edit_posts' => false,
                'delete_posts' => false,
                // Custom capabilities
                'apply_for_jobs' => true,
                'view_own_applications' => true,
                'edit_own_profile' => true,
            ));
        }
        
        // Check if career_admin role exists and update it if needed
        $career_admin_role = get_role('career_admin');
        if (!$career_admin_role) {
            // Role doesn't exist, create it
            add_role('career_admin', __('Recruiter', 'careers-manager'), array(
                'read' => true,
                'edit_posts' => false,
                'delete_posts' => false,
                // Custom capabilities for career management
                'manage_jobs' => true,
                'view_all_applications' => true,
                'edit_application_status' => true,
                'send_applicant_emails' => true,
                'view_career_analytics' => true,
                'export_applications' => true,
            ));
        } else {
            // Role exists, update the display name by removing and re-adding
            global $wp_roles;
            if (isset($wp_roles->roles['career_admin'])) {
                $wp_roles->roles['career_admin']['name'] = __('Recruiter', 'careers-manager');
                update_option($wp_roles->role_key, $wp_roles->roles);
            }
        }
    }
    
    /**
     * Prevent non-admin users from accessing wp-admin
     */
    public function prevent_wp_admin_access() {
        $user = wp_get_current_user();
        
        // Allow access for actual administrators
        if (in_array('administrator', $user->roles)) {
            return;
        }
        
        // Allow AJAX requests
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return;
        }
        
        // Redirect career system users to frontend dashboard
        if (in_array('applicant', $user->roles) || in_array('career_admin', $user->roles)) {
            wp_redirect(CareersSettings::get_page_url('dashboard'));
            exit;
        }
    }
    
    /**
     * Custom login redirect based on user role
     */
    public function custom_login_redirect($redirect_to, $request, $user) {
        // Check if user object exists and has roles
        if (isset($user->roles) && is_array($user->roles)) {
            // Redirect career system users to frontend dashboard
            if (in_array('applicant', $user->roles) || in_array('career_admin', $user->roles)) {
                return CareersSettings::get_page_url('dashboard');
            }
        }
        
        return $redirect_to;
    }
    
    /**
     * Handle admin AJAX actions
     */
    public function handle_admin_action() {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'], 'careers_nonce') || !current_user_can('manage_options')) {
            wp_die(__('Security check failed.', 'careers-manager'));
        }
        
        $action = sanitize_text_field($_POST['admin_action']);
        
        switch ($action) {
            case 'update_status':
                $this->update_application_status();
                break;
            case 'view_application':
                $this->view_application_details();
                break;
            default:
                wp_send_json_error(__('Invalid action.', 'careers-manager'));
        }
    }
    
    /**
     * Update application status
     */
    private function update_application_status() {
        $application_id = intval($_POST['application_id']);
        $new_status = sanitize_text_field($_POST['new_status']);
        
        $result = CareersApplicationDB::update_status($application_id, $new_status);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        wp_send_json_success(__('Status updated successfully.', 'careers-manager'));
    }
    
    /**
     * Get application details for modal
     */
    private function view_application_details() {
        $application_id = intval($_POST['application_id']);
        $application = CareersApplicationDB::get_application($application_id);
        
        if (!$application) {
            wp_send_json_error(__('Application not found.', 'careers-manager'));
        }
        
        $job = get_post($application->job_id);
        $user = get_user_by('id', $application->user_id);
        
        ob_start();
        ?>
        <h2><?php _e('Application Details', 'careers-manager'); ?></h2>
        
        <div class="careers-application-details">
            <div class="careers-detail-section">
                <h3><?php _e('Job Information', 'careers-manager'); ?></h3>
                <p><strong><?php _e('Job Title:', 'careers-manager'); ?></strong> <?php echo esc_html($job->post_title); ?></p>
                <p><strong><?php _e('Applied:', 'careers-manager'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($application->submitted_at))); ?></p>
            </div>
            
            <div class="careers-detail-section">
                <h3><?php _e('Applicant Information', 'careers-manager'); ?></h3>
                <?php if ($application->meta): ?>
                    <?php foreach ($application->meta as $key => $value): ?>
                        <?php if (!empty($value)): ?>
                            <p><strong><?php echo esc_html(ucwords(str_replace('_', ' ', $key))); ?>:</strong> <?php echo esc_html($value); ?></p>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="careers-detail-section">
                <h3><?php _e('Documents', 'careers-manager'); ?></h3>
                <?php if (!empty($application->resume_url)): ?>
                    <p><a href="<?php echo esc_url($application->resume_url); ?>" target="_blank" class="button"><?php _e('View Resume', 'careers-manager'); ?></a></p>
                <?php endif; ?>
                
                <?php if (!empty($application->cover_letter_url)): ?>
                    <p><a href="<?php echo esc_url($application->cover_letter_url); ?>" target="_blank" class="button"><?php _e('View Cover Letter', 'careers-manager'); ?></a></p>
                <?php endif; ?>
            </div>
        </div>
        <?php
        
        wp_send_json_success(ob_get_clean());
    }
    
    /**
     * Handle frontend job creation
     */
    public function handle_job_creation() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['job_nonce'], 'create_job_nonce')) {
            wp_send_json_error(__('Security check failed.', 'careers-manager'));
        }
        
        // Check if user is logged in and has permission
        if (!is_user_logged_in()) {
            wp_send_json_error(__('You must be logged in.', 'careers-manager'));
        }
        
        $user = wp_get_current_user();
        if (!in_array('career_admin', $user->roles) && !in_array('administrator', $user->roles)) {
            wp_send_json_error(__('You do not have permission to create jobs.', 'careers-manager'));
        }
        
        // Sanitize input data
        $title = sanitize_text_field($_POST['job_title']);
        $location = sanitize_text_field($_POST['job_location']);
        $type = sanitize_text_field($_POST['job_type']);
        $modality = sanitize_text_field($_POST['job_modality']);
        $summary = sanitize_textarea_field($_POST['job_summary']);
        $description = sanitize_textarea_field($_POST['job_description']);
        $requirements = sanitize_textarea_field($_POST['job_requirements']);
        $responsibilities = sanitize_textarea_field($_POST['job_responsibilities']);
        $equipment = sanitize_textarea_field($_POST['job_equipment']);
        $benefits = sanitize_textarea_field($_POST['job_benefits']);
        $state_licensing = sanitize_textarea_field($_POST['state_licensing']);
        $status = sanitize_text_field($_POST['job_status']);
        
        // Validate required fields
        if (empty($title) || empty($location) || empty($type) || empty($modality) || empty($summary) || empty($description) || empty($requirements) || empty($responsibilities)) {
            wp_send_json_error(__('Please fill in all required fields.', 'careers-manager'));
        }
        
        // Determine post status based on button clicked
        $post_status = 'draft';
        if (isset($_POST['publish_job']) && $_POST['publish_job'] == '1') {
            $post_status = 'publish';
        }
        
        // Create job post
        $post_data = array(
            'post_title' => $title,
            'post_content' => $description,
            'post_status' => $post_status,
            'post_type' => 'career_job',
            'post_author' => get_current_user_id()
        );
        
        $job_id = wp_insert_post($post_data);
        
        if (is_wp_error($job_id)) {
            wp_send_json_error(__('Failed to create job posting.', 'careers-manager'));
        }
        
        // Save meta fields
        update_post_meta($job_id, '_job_location', $location);
        update_post_meta($job_id, '_job_type', $type);
        update_post_meta($job_id, '_job_modality', $modality);
        update_post_meta($job_id, '_job_summary', $summary);
        update_post_meta($job_id, '_job_requirements', $requirements);
        update_post_meta($job_id, '_job_responsibilities', $responsibilities);
        update_post_meta($job_id, '_job_equipment', $equipment);
        update_post_meta($job_id, '_job_benefits', $benefits);
        update_post_meta($job_id, '_state_licensing', $state_licensing);
        
        // Set job tags based on modality and type
        $tags = array($modality, $type);
        if ($modality === 'X-Ray') {
            $tags[] = 'ARRT';
        } elseif ($modality === 'Ultrasound') {
            $tags[] = 'ARDMS';
        }
        
        // Create terms in job taxonomy if they don't exist
        $job_tags_taxonomy = 'career_job_tag';
        foreach ($tags as $tag) {
            $term = term_exists($tag, $job_tags_taxonomy);
            if (!$term) {
                wp_insert_term($tag, $job_tags_taxonomy);
            }
        }
        
        // Set the terms
        wp_set_object_terms($job_id, $tags, $job_tags_taxonomy);
        
        // Send success response
        wp_send_json_success(array(
            'message' => __('Job created successfully!', 'careers-manager'),
            'redirect_url' => CareersSettings::get_page_url('manage_jobs'),
            'job_id' => $job_id
        ));
    }
    
    /**
     * Handle job update via AJAX
     */
    public function handle_job_update() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['edit_job_nonce'], 'edit_career_job')) {
            wp_send_json_error(__('Security check failed.', 'careers-manager'));
            return;
        }
        
        // Check user permissions
        if (!current_user_can('edit_posts') && !in_array('career_admin', wp_get_current_user()->roles)) {
            wp_send_json_error(__('You do not have permission to edit jobs.', 'careers-manager'));
            return;
        }
        
        // Get job ID
        $job_id = intval($_POST['job_id']);
        if (!$job_id) {
            wp_send_json_error(__('Invalid job ID.', 'careers-manager'));
            return;
        }
        
        // Verify job exists and user has permission to edit it
        $job = get_post($job_id);
        if (!$job || $job->post_type !== 'career_job') {
            wp_send_json_error(__('Job not found.', 'careers-manager'));
            return;
        }
        
        // Sanitize input data
        $job_title = sanitize_text_field($_POST['job_title']);
        $job_description = wp_kses_post($_POST['job_description']);
        $location = sanitize_text_field($_POST['location']);
        $job_type = sanitize_text_field($_POST['job_type']);
        $experience_level = sanitize_text_field($_POST['experience_level']);
        $salary_range = sanitize_text_field($_POST['salary_range']);
        $application_deadline = sanitize_text_field($_POST['application_deadline']);
        
        // Validate required fields
        if (empty($job_title) || empty($job_description) || empty($location) || empty($job_type)) {
            wp_send_json_error(__('Please fill in all required fields.', 'careers-manager'));
            return;
        }
        
        // Update the job post
        $job_data = array(
            'ID' => $job_id,
            'post_title' => $job_title,
            'post_content' => $job_description,
            'post_status' => 'publish',
            'post_type' => 'career_job'
        );
        
        $updated_job_id = wp_update_post($job_data);
        
        if (is_wp_error($updated_job_id)) {
            wp_send_json_error(__('Failed to update job: ' . $updated_job_id->get_error_message(), 'careers-manager'));
            return;
        }
        
        // Update meta fields
        update_post_meta($job_id, '_job_location', $location);
        update_post_meta($job_id, '_job_type', $job_type);
        update_post_meta($job_id, '_experience_level', $experience_level);
        update_post_meta($job_id, '_salary_range', $salary_range);
        update_post_meta($job_id, '_application_deadline', $application_deadline);
        
        // Send success response
        wp_send_json_success(array(
            'message' => __('Job updated successfully!', 'careers-manager'),
            'redirect_url' => CareersSettings::get_page_url('manage_jobs')
        ));
    }
    
    /**
     * Handle job deletion via AJAX
     */
    public function handle_job_deletion() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'delete_job_nonce')) {
            wp_send_json_error(__('Security check failed.', 'careers-manager'));
            return;
        }
        
        // Check user permissions
        if (!current_user_can('delete_posts') && !in_array('career_admin', wp_get_current_user()->roles)) {
            wp_send_json_error(__('You do not have permission to delete jobs.', 'careers-manager'));
            return;
        }
        
        // Get job ID
        $job_id = intval($_POST['job_id']);
        if (!$job_id) {
            wp_send_json_error(__('Invalid job ID.', 'careers-manager'));
            return;
        }
        
        // Verify job exists
        $job = get_post($job_id);
        if (!$job || $job->post_type !== 'career_job') {
            wp_send_json_error(__('Job not found.', 'careers-manager'));
            return;
        }
        
        // Delete the job post
        $deleted = wp_delete_post($job_id, true); // true = force delete (skip trash)
        
        if (!$deleted) {
            wp_send_json_error(__('Failed to delete job.', 'careers-manager'));
            return;
        }
        
        // Send success response
        wp_send_json_success(array(
            'message' => __('Job deleted successfully!', 'careers-manager')
        ));
    }
} 