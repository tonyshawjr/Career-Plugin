<?php
/**
 * Careers Shortcodes Handler
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CareersShortcodes {
    
    public function __construct() {
        // Register shortcodes immediately instead of waiting for init
        $this->register_shortcodes();
        
        // Register AJAX handlers
        add_action('wp_ajax_careers_submit_application', array($this, 'handle_application_submission'));
        add_action('wp_ajax_nopriv_careers_submit_application', array($this, 'handle_application_submission'));
        add_action('wp_ajax_careers_admin_load_job', array($this, 'handle_admin_load_job'));
        add_action('wp_ajax_careers_admin_save_job', array($this, 'handle_admin_save_job'));
        add_action('wp_ajax_careers_admin_load_application', array($this, 'handle_admin_load_application'));
        add_action('wp_ajax_careers_admin_update_status', array($this, 'handle_admin_update_status'));
    }
    
    /**
     * Register all shortcodes
     */
    public function register_shortcodes() {
        add_shortcode('careers_job_listings', array($this, 'job_listings_shortcode'));
        add_shortcode('careers_apply_form', array($this, 'apply_form_shortcode'));
        add_shortcode('careers_dashboard', array($this, 'dashboard_shortcode'));
        add_shortcode('careers_auth_form', array($this, 'auth_form_shortcode'));
        add_shortcode('careers_admin_dashboard', array($this, 'admin_dashboard_shortcode'));
        add_shortcode('careers_debug', array($this, 'debug_shortcode'));
        add_shortcode('careers_header_nav', array($this, 'header_nav_shortcode'));
    }
    
    /**
     * Header navigation shortcode [careers_header_nav]
     */
    public function header_nav_shortcode($atts) {
        $atts = shortcode_atts(array(
            'redirect_after_login' => home_url('/dashboard/'),
            'redirect_after_logout' => home_url('/'),
        ), $atts, 'careers_header_nav');
        
        ob_start();
        
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $dashboard_url = home_url('/dashboard/');
            $logout_url = wp_logout_url($atts['redirect_after_logout']);
            
            // Determine user role for dashboard link text
            $dashboard_text = 'Dashboard';
            if (in_array('career_admin', $current_user->roles)) {
                $dashboard_text = 'Admin Dashboard';
            } elseif (in_array('applicant', $current_user->roles)) {
                $dashboard_text = 'My Dashboard';
            }
            
            ?>
            <div class="careers-header-nav">
                <a href="<?php echo esc_url($dashboard_url); ?>" class="careers-dashboard-link"><?php echo esc_html($dashboard_text); ?></a>
                <a href="<?php echo esc_url($logout_url); ?>" class="elementor-button elementor-button-link elementor-size-sm careers-logout-btn">
                    <span class="elementor-button-content-wrapper">
                        <span class="elementor-button-text">Logout</span>
                    </span>
                </a>
            </div>
            <?php
        } else {
            $login_url = wp_login_url($atts['redirect_after_login']);
            ?>
            <div class="careers-header-nav">
                <a href="<?php echo esc_url($login_url); ?>" class="elementor-button elementor-button-link elementor-size-sm careers-login-btn">
                    <span class="elementor-button-content-wrapper">
                        <span class="elementor-button-text">Sign In</span>
                    </span>
                </a>
            </div>
            <?php
        }
        
        return ob_get_clean();
    }
    
    /**
     * Job listings shortcode [careers_job_listings]
     */
    public function job_listings_shortcode($atts) {
        $atts = shortcode_atts(array(
            'limit' => 10,
            'show_filters' => 'true',
            'show_search' => 'false',
            'columns' => 2,
            'modality' => '',
            'certification' => '',
            'state' => '',
            'employment_type' => '',
        ), $atts, 'careers_job_listings');
        
        ob_start();
        
        // Check if we're viewing a single job
        $job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;
        if ($job_id) {
            // Show single job detail
            $job = get_post($job_id);
            if ($job && $job->post_type === 'career_job') {
                $this->display_single_job($job);
                return ob_get_clean();
            }
        }
        
        // Get filter values from URL
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        $filter_modality = isset($_GET['modality']) ? sanitize_text_field($_GET['modality']) : $atts['modality'];
        $filter_certification = isset($_GET['certification']) ? sanitize_text_field($_GET['certification']) : $atts['certification'];
        $filter_state = isset($_GET['state']) ? sanitize_text_field($_GET['state']) : $atts['state'];
        $filter_employment_type = isset($_GET['employment_type']) ? sanitize_text_field($_GET['employment_type']) : $atts['employment_type'];
        
        ?>
        <div class="careers-job-listings-container">
            <div class="careers-header">
                <h1 class="careers-main-title">Open Positions</h1>
                <p class="careers-main-description">Browse our current opportunities at National Mobile X-Ray. Filter by modality, location, and certification to find the perfect fit for your career.</p>
            </div>
            
            <div class="careers-content-wrapper">
                <?php if ($atts['show_filters'] === 'true'): ?>
                <div class="careers-filters-sidebar">
                    <form method="get" class="careers-filter-form">
                        <div class="careers-filters-header">
                            <h3>Filters</h3>
                            <a href="<?php echo esc_url(remove_query_arg(array('modality', 'certification', 'state', 'employment_type', 'search'))); ?>" class="careers-clear-filters">Clear all filters</a>
                        </div>
                        
                        <div class="careers-filter-group">
                            <label>Modality</label>
                            <select name="modality" class="careers-filter-select">
                                <option value="">All Modalities</option>
                                <?php foreach (careers_get_modalities() as $key => $label): ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($filter_modality, $key); ?>><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="careers-filter-group">
                            <label>Location</label>
                            <select name="state" class="careers-filter-select">
                                <option value="">All Locations</option>
                                <?php foreach (careers_get_states() as $key => $label): ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($filter_state, $key); ?>><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="careers-filter-group">
                            <label>Certification</label>
                            <select name="certification" class="careers-filter-select">
                                <option value="">All Certifications</option>
                                <?php foreach (careers_get_certifications() as $key => $label): ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($filter_certification, $key); ?>><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <button type="submit" class="careers-filter-apply-btn" style="display: none;">Apply Filters</button>
                    </form>
                </div>
                <?php endif; ?>
                
                <div class="careers-jobs-content">
                    <div class="careers-jobs-grid careers-columns-<?php echo esc_attr($atts['columns']); ?>">
                        <?php
                        // Build query args
                        $args = array(
                            'post_type' => 'career_job',
                            'post_status' => 'publish',
                            'posts_per_page' => intval($atts['limit']),
                            'meta_query' => array(),
                            'tax_query' => array(),
                        );
                        
                        // Add search
                        if (!empty($search)) {
                            $args['s'] = $search;
                        }
                        
                        // Add taxonomy filters
                        if (!empty($filter_modality)) {
                            $args['tax_query'][] = array(
                                'taxonomy' => 'job_modality',
                                'field' => 'slug',
                                'terms' => $filter_modality,
                            );
                        }
                        
                        if (!empty($filter_certification)) {
                            $args['tax_query'][] = array(
                                'taxonomy' => 'job_certification',
                                'field' => 'slug',
                                'terms' => $filter_certification,
                            );
                        }
                        
                        if (!empty($filter_state)) {
                            $args['tax_query'][] = array(
                                'taxonomy' => 'job_state',
                                'field' => 'slug',
                                'terms' => $filter_state,
                            );
                        }
                        
                        // Add meta filters
                        if (!empty($filter_employment_type)) {
                            $args['meta_query'][] = array(
                                'key' => '_job_type',
                                'value' => $filter_employment_type,
                                'compare' => '='
                            );
                        }
                        
                        $jobs_query = new WP_Query($args);
                        
                        if ($jobs_query->have_posts()):
                            while ($jobs_query->have_posts()): $jobs_query->the_post();
                                $job_id = get_the_ID();
                                $location = get_post_meta($job_id, '_job_location', true);
                                $employment_type = get_post_meta($job_id, '_job_type', true);
                                $salary_min = get_post_meta($job_id, '_salary_min', true);
                                $salary_max = get_post_meta($job_id, '_salary_max', true);
                                $experience_level = get_post_meta($job_id, '_experience_level', true);
                                $posted_date = get_the_date();
                                $summary = get_post_meta($job_id, '_job_summary', true);
                                
                                // Get certifications and modalities
                                $certifications = wp_get_post_terms($job_id, 'job_certification');
                                $modalities = wp_get_post_terms($job_id, 'job_modality');
                                
                                ?>
                                <div class="careers-job-card-new">
                                    <!-- Header Section with Left and Right Layout -->
                                    <div class="careers-card-header">
                                        <div class="careers-card-left">
                                            <h3 class="careers-job-title-new">
                                                <?php the_title(); ?>
                                            </h3>
                                            
                                            <?php if (!empty($location)): ?>
                                                <div class="careers-job-location-new">
                                                    <svg class="careers-location-icon" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    <span class="careers-location-tag"><?php echo esc_html($location); ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="careers-card-right">
                                            <?php if (!empty($employment_type)): 
                                                $types = careers_get_employment_types();
                                                if (isset($types[$employment_type])):
                                            ?>
                                                <span class="careers-job-type-tag"><?php echo esc_html($types[$employment_type]); ?></span>
                                            <?php endif; endif; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Body Section -->
                                    <div class="careers-job-description-new">
                                        <?php 
                                        if (!empty($summary)) {
                                            echo esc_html($summary);
                                        } else {
                                            echo wp_trim_words(get_the_excerpt(), 30);
                                        }
                                        ?>
                                    </div>
                                    
                                    <!-- Footer Section -->
                                    <div class="careers-card-footer">
                                        <div class="careers-posted-date">
                                            Posted: <?php echo esc_html($posted_date); ?>
                                        </div>
                                        <a href="<?php echo esc_url(careers_get_job_permalink($job_id)); ?>" class="careers-view-details-btn">View Details</a>
                                    </div>
                                </div>
                                <?php
                            endwhile;
                            wp_reset_postdata();
                        else:
                        ?>
                            <div class="careers-no-jobs">
                                <p><?php _e('No jobs found matching your criteria.', 'careers-manager'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Auto-submit filters when changed
            $('.careers-filter-select').on('change', function() {
                $(this).closest('form').submit();
            });
        });
        </script>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Application form shortcode [careers_apply_form]
     */
    public function apply_form_shortcode($atts) {
        $atts = shortcode_atts(array(
            'job_id' => '',
        ), $atts, 'careers_apply_form');
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return '<div class="careers-auth-required"><p>' . __('Please log in to apply for jobs.', 'careers-manager') . '</p><a href="' . esc_url(home_url('/auth/')) . '" class="careers-btn careers-btn-primary">' . __('Login / Register', 'careers-manager') . '</a></div>';
        }
        
        // Get job ID from URL parameter if not provided in shortcode
        if (empty($atts['job_id']) && isset($_GET['job_id'])) {
            $atts['job_id'] = intval($_GET['job_id']);
        }
        
        if (empty($atts['job_id'])) {
            return '<div class="careers-error"><p>' . __('No job specified.', 'careers-manager') . '</p></div>';
        }
        
        $job = get_post($atts['job_id']);
        if (!$job || $job->post_type !== 'career_job') {
            return '<div class="careers-error"><p>' . __('Invalid job.', 'careers-manager') . '</p></div>';
        }
        
        // Check if user already applied
        $existing_application = CareersApplicationDB::get_application_by_user_job(get_current_user_id(), $atts['job_id']);
        if ($existing_application) {
            return '<div class="careers-already-applied"><p>' . __('You have already applied for this job.', 'careers-manager') . '</p><a href="' . esc_url(home_url('/dashboard/')) . '" class="careers-btn careers-btn-secondary">' . __('View Dashboard', 'careers-manager') . '</a></div>';
        }
        
        $current_user = wp_get_current_user();
        
        ob_start();
        ?>
        <div class="careers-apply-form-container">
            <div class="careers-job-info">
                <h3><?php _e('Applying for:', 'careers-manager'); ?> <?php echo esc_html($job->post_title); ?></h3>
            </div>
            
            <form id="careers-application-form" class="careers-application-form" enctype="multipart/form-data">
                <?php wp_nonce_field('careers_nonce', 'nonce'); ?>
                <input type="hidden" name="job_id" value="<?php echo esc_attr($atts['job_id']); ?>" />
                
                <div class="careers-form-step careers-step-1">
                    <h4><?php _e('Personal Information', 'careers-manager'); ?></h4>
                    
                    <div class="careers-form-row">
                        <div class="careers-form-group careers-half">
                            <label for="first_name"><?php _e('First Name', 'careers-manager'); ?> *</label>
                            <input type="text" id="first_name" name="first_name" value="<?php echo esc_attr($current_user->first_name); ?>" required />
                        </div>
                        
                        <div class="careers-form-group careers-half">
                            <label for="last_name"><?php _e('Last Name', 'careers-manager'); ?> *</label>
                            <input type="text" id="last_name" name="last_name" value="<?php echo esc_attr($current_user->last_name); ?>" required />
                        </div>
                    </div>
                    
                    <div class="careers-form-row">
                        <div class="careers-form-group careers-half">
                            <label for="email"><?php _e('Email', 'careers-manager'); ?> *</label>
                            <input type="email" id="email" name="email" value="<?php echo esc_attr($current_user->user_email); ?>" required />
                        </div>
                        
                        <div class="careers-form-group careers-half">
                            <label for="phone"><?php _e('Phone', 'careers-manager'); ?> *</label>
                            <input type="tel" id="phone" name="phone" required />
                        </div>
                    </div>
                    
                    <div class="careers-form-row">
                        <div class="careers-form-group careers-half">
                            <label for="city"><?php _e('City', 'careers-manager'); ?> *</label>
                            <input type="text" id="city" name="city" required />
                        </div>
                        
                        <div class="careers-form-group careers-half">
                            <label for="state"><?php _e('State', 'careers-manager'); ?> *</label>
                            <select id="state" name="state" required>
                                <option value=""><?php _e('Select State', 'careers-manager'); ?></option>
                                <?php foreach (careers_get_states() as $key => $label): ?>
                                    <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="careers-form-step careers-step-2">
                    <h4><?php _e('Position & Background', 'careers-manager'); ?></h4>
                    
                    <div class="careers-form-group">
                        <label for="role_interested"><?php _e('Role you are interested in', 'careers-manager'); ?> *</label>
                        <input type="text" id="role_interested" name="role_interested" value="<?php echo esc_attr($job->post_title); ?>" required />
                    </div>
                    
                    <div class="careers-form-group">
                        <label for="how_heard"><?php _e('How did you hear about us?', 'careers-manager'); ?></label>
                        <select id="how_heard" name="how_heard">
                            <option value=""><?php _e('Please select', 'careers-manager'); ?></option>
                            <option value="website"><?php _e('Company Website', 'careers-manager'); ?></option>
                            <option value="job_board"><?php _e('Job Board', 'careers-manager'); ?></option>
                            <option value="social_media"><?php _e('Social Media', 'careers-manager'); ?></option>
                            <option value="referral"><?php _e('Employee Referral', 'careers-manager'); ?></option>
                            <option value="other"><?php _e('Other', 'careers-manager'); ?></option>
                        </select>
                    </div>
                    
                    <div class="careers-form-group">
                        <label><?php _e('Are you a new graduate?', 'careers-manager'); ?></label>
                        <div class="careers-radio-group">
                            <label><input type="radio" name="new_graduate" value="yes" /> <?php _e('Yes', 'careers-manager'); ?></label>
                            <label><input type="radio" name="new_graduate" value="no" /> <?php _e('No', 'careers-manager'); ?></label>
                        </div>
                    </div>
                    
                    <div class="careers-form-group">
                        <label><?php _e('Do you have certifications?', 'careers-manager'); ?></label>
                        <div class="careers-radio-group">
                            <label><input type="radio" name="has_certifications" value="yes" /> <?php _e('Yes', 'careers-manager'); ?></label>
                            <label><input type="radio" name="has_certifications" value="no" /> <?php _e('No', 'careers-manager'); ?></label>
                        </div>
                    </div>
                    
                    <div class="careers-form-group">
                        <label><?php _e('Are you willing to relocate?', 'careers-manager'); ?></label>
                        <div class="careers-radio-group">
                            <label><input type="radio" name="willing_relocate" value="yes" /> <?php _e('Yes', 'careers-manager'); ?></label>
                            <label><input type="radio" name="willing_relocate" value="no" /> <?php _e('No', 'careers-manager'); ?></label>
                        </div>
                    </div>
                    
                    <div class="careers-form-group">
                        <label><?php _e('Are you willing to travel?', 'careers-manager'); ?></label>
                        <div class="careers-radio-group">
                            <label><input type="radio" name="willing_travel" value="yes" /> <?php _e('Yes', 'careers-manager'); ?></label>
                            <label><input type="radio" name="willing_travel" value="no" /> <?php _e('No', 'careers-manager'); ?></label>
                        </div>
                    </div>
                </div>
                
                <div class="careers-form-step careers-step-3">
                    <h4><?php _e('Documents', 'careers-manager'); ?></h4>
                    
                    <div class="careers-form-group">
                        <label for="resume"><?php _e('Resume', 'careers-manager'); ?> *</label>
                        <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx" required />
                        <small><?php _e('Accepted formats: PDF, DOC, DOCX (Max 5MB)', 'careers-manager'); ?></small>
                    </div>
                    
                    <div class="careers-form-group">
                        <label for="cover_letter"><?php _e('Cover Letter (Optional)', 'careers-manager'); ?></label>
                        <input type="file" id="cover_letter" name="cover_letter" accept=".pdf,.doc,.docx" />
                        <small><?php _e('Accepted formats: PDF, DOC, DOCX (Max 5MB)', 'careers-manager'); ?></small>
                    </div>
                </div>
                
                <div class="careers-form-actions">
                    <button type="submit" class="careers-btn careers-btn-primary careers-submit-application">
                        <?php _e('Submit Application', 'careers-manager'); ?>
                    </button>
                </div>
            </form>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Dashboard shortcode [careers_dashboard]
     */
    public function dashboard_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<div class="careers-auth-required"><p>' . __('Please log in to view your dashboard.', 'careers-manager') . '</p><a href="' . esc_url(home_url('/auth/')) . '" class="careers-btn careers-btn-primary">' . __('Login / Register', 'careers-manager') . '</a></div>';
        }
        
        $current_user = wp_get_current_user();
        $applications = CareersApplicationDB::get_user_applications(get_current_user_id());
        
        ob_start();
        ?>
        <div class="careers-dashboard-container">
            <!-- Profile Section -->
            <div class="careers-profile-section">
                <h2><?php _e('My Profile', 'careers-manager'); ?></h2>
                <p class="subtitle"><?php _e('Your account information', 'careers-manager'); ?></p>
                
                <div class="careers-profile-info">
                    <div class="careers-profile-field">
                        <label><?php _e('Name', 'careers-manager'); ?></label>
                        <div class="value"><?php echo esc_html($current_user->display_name ?: 'Not provided'); ?></div>
                        <input type="text" value="<?php echo esc_attr($current_user->display_name); ?>" style="display: none;">
                    </div>
                    
                    <div class="careers-profile-field">
                        <label><?php _e('Email', 'careers-manager'); ?></label>
                        <div class="value"><?php echo esc_html($current_user->user_email); ?></div>
                        <input type="email" value="<?php echo esc_attr($current_user->user_email); ?>" style="display: none;">
                    </div>
                </div>
                
                <div class="careers-profile-actions">
                    <button class="careers-btn careers-btn-primary" id="browse-jobs">
                        <?php _e('Browse Jobs', 'careers-manager'); ?>
                    </button>
                    <button class="careers-btn careers-btn-secondary" id="sign-out">
                        <?php _e('Sign Out', 'careers-manager'); ?>
                    </button>
                </div>
            </div>
            
            <!-- Applications Section -->
            <div class="careers-applications-section">
                <h2><?php _e('My Applications', 'careers-manager'); ?></h2>
                <p class="subtitle"><?php _e('Track the status of your job applications', 'careers-manager'); ?></p>
                
                <?php if (!empty($applications)): ?>
                    <?php foreach ($applications as $application): ?>
                        <?php
                        $job = get_post($application->job_id);
                        $location = get_post_meta($application->job_id, '_career_location', true);
                        $employment_type = get_post_meta($application->job_id, '_career_employment_type', true);
                        $employment_types = careers_get_employment_types();
                        $statuses = careers_get_application_statuses();
                        ?>
                        <div class="careers-application-item">
                            <div class="careers-application-header">
                                <h3 class="careers-application-title"><?php echo esc_html($job->post_title); ?></h3>
                                <div class="careers-application-date">
                                    📅 <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($application->submitted_at))); ?>
                                </div>
                            </div>
                            
                            <div class="careers-application-meta">
                                <?php if ($location): ?>
                                    <div class="careers-application-location">
                                        📍 <?php echo esc_html($location); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($employment_type && isset($employment_types[$employment_type])): ?>
                                    <div class="careers-application-type">
                                        💼 <?php echo esc_html($employment_types[$employment_type]); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="careers-application-status">
                                <span class="careers-status careers-status-<?php echo esc_attr($application->status); ?>">
                                    <?php echo esc_html($statuses[$application->status] ?? ucfirst($application->status)); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="careers-no-applications">
                        <h3><?php _e('You haven\'t applied to any positions yet.', 'careers-manager'); ?></h3>
                        <p><?php _e('Start your career journey today by exploring our available positions.', 'careers-manager'); ?></p>
                        <a href="#" class="careers-browse-btn" id="browse-available-positions"><?php _e('Browse Available Positions', 'careers-manager'); ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Sign out handler
            $('#sign-out').on('click', function(e) {
                e.preventDefault();
                if (confirm('<?php _e('Are you sure you want to sign out?', 'careers-manager'); ?>')) {
                    window.location.href = '<?php echo esc_url(wp_logout_url()); ?>';
                }
            });
            
            // Browse jobs handlers - you can customize these URLs
            $('#browse-jobs, #browse-available-positions').on('click', function(e) {
                e.preventDefault();
                // Redirect to jobs page - customize this URL as needed
                window.location.href = '<?php echo esc_url(home_url('/jobs/')); ?>';
            });
        });
        </script>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Auth form shortcode [careers_auth_form]
     */
    public function auth_form_shortcode($atts) {
        if (is_user_logged_in()) {
            return '<div class="careers-already-logged-in"><p>' . __('You are already logged in.', 'careers-manager') . '</p><a href="' . esc_url(home_url('/dashboard/')) . '" class="careers-btn careers-btn-primary">' . __('Go to Dashboard', 'careers-manager') . '</a></div>';
        }
        
        $atts = shortcode_atts(array(
            'default_form' => 'login',
            'redirect' => '',
        ), $atts, 'careers_auth_form');
        
        ob_start();
        ?>
        <div class="careers-auth-container">
            <div class="careers-auth-tabs">
                <button class="careers-auth-tab careers-login-tab active" data-tab="login"><?php _e('Login', 'careers-manager'); ?></button>
                <button class="careers-auth-tab careers-register-tab" data-tab="register"><?php _e('Register', 'careers-manager'); ?></button>
            </div>
            
            <div class="careers-auth-forms">
                <div id="careers-login-form-container" class="careers-auth-form-container active">
                    <?php echo CareersAuth::get_login_form(array('redirect' => $atts['redirect'])); ?>
                </div>
                
                <div id="careers-register-form-container" class="careers-auth-form-container">
                    <?php if (get_option('users_can_register')): ?>
                        <?php echo CareersAuth::get_register_form(array('redirect' => $atts['redirect'])); ?>
                    <?php else: ?>
                        <div class="careers-registration-disabled">
                            <p><?php _e('User registration is currently disabled.', 'careers-manager'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Handle application submission via AJAX
     */
    public function handle_application_submission() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'careers_nonce')) {
            wp_die(__('Security check failed.', 'careers-manager'));
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(__('You must be logged in to apply.', 'careers-manager'));
        }
        
        $job_id = intval($_POST['job_id']);
        $user_id = get_current_user_id();
        
        // Validate job
        $job = get_post($job_id);
        if (!$job || $job->post_type !== 'career_job') {
            wp_send_json_error(__('Invalid job.', 'careers-manager'));
        }
        
        // Check if already applied
        $existing = CareersApplicationDB::get_application_by_user_job($user_id, $job_id);
        if ($existing) {
            wp_send_json_error(__('You have already applied for this job.', 'careers-manager'));
        }
        
        // Handle file uploads
        $resume_url = '';
        $cover_letter_url = '';
        
        if (!empty($_FILES['resume'])) {
            $resume_upload = careers_upload_resume($_FILES['resume'], $user_id, $job_id);
            if (is_wp_error($resume_upload)) {
                wp_send_json_error($resume_upload->get_error_message());
            }
            $resume_url = $resume_upload['url'];
        }
        
        if (!empty($_FILES['cover_letter'])) {
            $cover_letter_upload = careers_upload_resume($_FILES['cover_letter'], $user_id, $job_id);
            if (is_wp_error($cover_letter_upload)) {
                wp_send_json_error($cover_letter_upload->get_error_message());
            }
            $cover_letter_url = $cover_letter_upload['url'];
        }
        
        // Prepare application meta
        $meta = array(
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name']),
            'email' => sanitize_email($_POST['email']),
            'phone' => sanitize_text_field($_POST['phone']),
            'city' => sanitize_text_field($_POST['city']),
            'state' => sanitize_text_field($_POST['state']),
            'role_interested' => sanitize_text_field($_POST['role_interested']),
            'how_heard' => sanitize_text_field($_POST['how_heard']),
            'new_graduate' => sanitize_text_field($_POST['new_graduate']),
            'has_certifications' => sanitize_text_field($_POST['has_certifications']),
            'willing_relocate' => sanitize_text_field($_POST['willing_relocate']),
            'willing_travel' => sanitize_text_field($_POST['willing_travel']),
        );
        
        // Insert application
        $application_data = array(
            'user_id' => $user_id,
            'job_id' => $job_id,
            'resume_url' => $resume_url,
            'cover_letter_url' => $cover_letter_url,
            'meta' => $meta,
            'status' => 'pending'
        );
        
        $application_id = CareersApplicationDB::insert_application($application_data);
        
        if (is_wp_error($application_id)) {
            wp_send_json_error($application_id->get_error_message());
        }
        
        // Send confirmation emails
        if (class_exists('CareersEmails')) {
            CareersEmails::send_application_confirmation($application_id);
            CareersEmails::send_admin_notification($application_id);
        }
        
        wp_send_json_success(array(
            'message' => __('Your application has been submitted successfully!', 'careers-manager'),
            'redirect_url' => home_url('/dashboard/')
        ));
    }
    
    /**
     * Admin dashboard shortcode [careers_admin_dashboard]
     */
    public function admin_dashboard_shortcode($atts) {
        $atts = shortcode_atts(array(
            'view' => 'dashboard', // dashboard, jobs, applications, job-applicants
            'job_id' => 0
        ), $atts);
        
        // Check if user is logged in and has admin capabilities
        if (!is_user_logged_in()) {
            return '<div class="careers-error">' . __('Please log in to access the admin dashboard.', 'careers-manager') . '</div>';
        }
        
        $user = wp_get_current_user();
        if (!current_user_can('manage_options') && !in_array('administrator', $user->roles)) {
            return '<div class="careers-error">' . __('You do not have permission to access the admin dashboard.', 'careers-manager') . '</div>';
        }
        
        $view = isset($_GET['view']) ? sanitize_text_field($_GET['view']) : $atts['view'];
        $job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : intval($atts['job_id']);
        
        ob_start();
        
        echo '<div class="careers-admin-dashboard">';
        
        // Navigation tabs
        $this->render_admin_navigation($view);
        
        // Content based on view
        switch ($view) {
            case 'jobs':
                $this->render_admin_jobs_view();
                break;
            case 'applications':
                $this->render_admin_applications_view();
                break;
            case 'job-applicants':
                $this->render_job_applicants_view($job_id);
                break;
            default:
                $this->render_admin_dashboard_view();
                break;
        }
        
        echo '</div>';
        
        // Add modals
        $this->render_admin_modals();
        
        return ob_get_clean();
    }
    
    /**
     * Render admin navigation
     */
    private function render_admin_navigation($current_view) {
        $tabs = array(
            'dashboard' => __('Dashboard', 'careers-manager'),
            'jobs' => __('Job Management', 'careers-manager'),
            'applications' => __('Applications', 'careers-manager')
        );
        
        echo '<div class="careers-admin-nav">';
        foreach ($tabs as $view => $label) {
            $active = ($current_view === $view) ? 'active' : '';
            $url = add_query_arg('view', $view);
            echo '<a href="' . esc_url($url) . '" class="careers-nav-tab ' . $active . '">' . esc_html($label) . '</a>';
        }
        echo '</div>';
    }
    
    /**
     * Render admin dashboard overview
     */
    private function render_admin_dashboard_view() {
        $stats = CareersApplicationDB::get_stats();
        ?>
        <div class="careers-admin-content">
            <div class="careers-admin-header">
                <h1><?php _e('Admin Dashboard', 'careers-manager'); ?></h1>
                <p><?php _e('Manage jobs, applications, and system operations', 'careers-manager'); ?></p>
            </div>
            
            <div class="careers-admin-stats">
                <div class="careers-stat-boxes">
                    <div class="careers-stat-box">
                        <div class="stat-number"><?php echo intval($stats['total']); ?></div>
                        <div class="stat-label"><?php _e('Total Applications', 'careers-manager'); ?></div>
                    </div>
                    <div class="careers-stat-box">
                        <div class="stat-number"><?php echo isset($stats['by_status']['pending']) ? intval($stats['by_status']['pending']) : 0; ?></div>
                        <div class="stat-label"><?php _e('Pending', 'careers-manager'); ?></div>
                    </div>
                    <div class="careers-stat-box">
                        <div class="stat-number"><?php echo intval($stats['recent']); ?></div>
                        <div class="stat-label"><?php _e('Applications (30 days)', 'careers-manager'); ?></div>
                    </div>
                    <div class="careers-stat-box">
                        <div class="stat-number"><?php echo count($stats['by_job']); ?></div>
                        <div class="stat-label"><?php _e('Active Jobs', 'careers-manager'); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="careers-admin-sections">
                <div class="careers-admin-section">
                    <h2><?php _e('Quick Actions', 'careers-manager'); ?></h2>
                    <div class="careers-quick-actions">
                        <a href="<?php echo esc_url(add_query_arg('view', 'jobs')); ?>" class="careers-action-card">
                            <h3><?php _e('Job Management', 'careers-manager'); ?></h3>
                            <p><?php _e('Create, edit, and manage job postings', 'careers-manager'); ?></p>
                        </a>
                        <a href="<?php echo esc_url(add_query_arg('view', 'applications')); ?>" class="careers-action-card">
                            <h3><?php _e('Applications', 'careers-manager'); ?></h3>
                            <p><?php _e('Review and manage job applications', 'careers-manager'); ?></p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render admin jobs view
     */
    private function render_admin_jobs_view() {
        $jobs = get_posts(array(
            'post_type' => 'career_job',
            'post_status' => array('publish', 'draft'),
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        global $wpdb;
        ?>
        <div class="careers-admin-content">
            <div class="careers-admin-header">
                <h1><?php _e('Job Management', 'careers-manager'); ?></h1>
                <p><?php _e('Create, edit, and manage job postings and track applications', 'careers-manager'); ?></p>
            </div>
            
            <div class="careers-admin-actions">
                <button class="careers-btn careers-btn-primary" id="add-new-job">
                    + <?php _e('Add New Job', 'careers-manager'); ?>
                </button>
            </div>
            
            <div class="careers-admin-stats">
                <div class="careers-stat-boxes">
                    <?php
                    $total_jobs = count($jobs);
                    $total_applications = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}careers_applications");
                    $avg_applications = $total_jobs > 0 ? round($total_applications / $total_jobs, 1) : 0;
                    ?>
                    <div class="careers-stat-box">
                        <div class="stat-number"><?php echo $total_jobs; ?></div>
                        <div class="stat-label"><?php _e('Total Jobs', 'careers-manager'); ?></div>
                    </div>
                    <div class="careers-stat-box">
                        <div class="stat-number"><?php echo $total_applications; ?></div>
                        <div class="stat-label"><?php _e('Total Applications', 'careers-manager'); ?></div>
                    </div>
                    <div class="careers-stat-box">
                        <div class="stat-number"><?php echo $avg_applications; ?></div>
                        <div class="stat-label"><?php _e('Avg Applications/Job', 'careers-manager'); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="careers-jobs-table">
                <div class="careers-table-header">
                    <div class="table-cell"><?php _e('Job Title', 'careers-manager'); ?></div>
                    <div class="table-cell"><?php _e('Location', 'careers-manager'); ?></div>
                    <div class="table-cell"><?php _e('Applications', 'careers-manager'); ?></div>
                    <div class="table-cell"><?php _e('Employment Type', 'careers-manager'); ?></div>
                    <div class="table-cell"><?php _e('Posted Date', 'careers-manager'); ?></div>
                    <div class="table-cell"><?php _e('Actions', 'careers-manager'); ?></div>
                </div>
                
                <?php if (!empty($jobs)): ?>
                    <?php foreach ($jobs as $job): ?>
                        <?php
                        $application_count = $wpdb->get_var($wpdb->prepare(
                            "SELECT COUNT(*) FROM {$wpdb->prefix}careers_applications WHERE job_id = %d",
                            $job->ID
                        ));
                        $location = get_post_meta($job->ID, '_career_location', true);
                        $employment_type = get_post_meta($job->ID, '_career_employment_type', true);
                        $employment_types = careers_get_employment_types();
                        ?>
                        <div class="careers-table-row">
                            <div class="table-cell">
                                <strong><?php echo esc_html($job->post_title); ?></strong>
                            </div>
                            <div class="table-cell"><?php echo esc_html($location); ?></div>
                            <div class="table-cell">
                                <a href="<?php echo esc_url(add_query_arg(array('view' => 'job-applicants', 'job_id' => $job->ID))); ?>" class="careers-applicants-link">
                                    <?php echo intval($application_count); ?> <?php _e('applicants', 'careers-manager'); ?>
                                </a>
                            </div>
                            <div class="table-cell"><?php echo isset($employment_types[$employment_type]) ? esc_html($employment_types[$employment_type]) : ''; ?></div>
                            <div class="table-cell"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($job->post_date))); ?></div>
                            <div class="table-cell">
                                <button class="careers-btn careers-btn-small edit-job" data-job-id="<?php echo esc_attr($job->ID); ?>">
                                    <?php _e('Edit', 'careers-manager'); ?>
                                </button>
                                <a href="<?php echo esc_url(get_permalink($job->ID)); ?>" class="careers-btn careers-btn-small" target="_blank">
                                    <?php _e('View', 'careers-manager'); ?>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="careers-table-row">
                        <div class="table-cell" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                            <?php _e('No jobs found. Create your first job to get started.', 'careers-manager'); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render admin applications view
     */
    private function render_admin_applications_view() {
        $current_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $current_job = isset($_GET['job_id']) ? intval($_GET['job_id']) : '';
        
        $applications = CareersApplicationDB::get_applications(array(
            'status' => $current_status,
            'job_id' => $current_job,
            'limit' => 50
        ));
        
        $statuses = careers_get_application_statuses();
        ?>
        <div class="careers-admin-content">
            <div class="careers-admin-header">
                <h1><?php _e('Applications', 'careers-manager'); ?></h1>
                <p><?php _e('Review and manage job applications', 'careers-manager'); ?></p>
            </div>
            
            <div class="careers-applications-filters">
                <select id="filter-status">
                    <option value=""><?php _e('All Statuses', 'careers-manager'); ?></option>
                    <?php foreach ($statuses as $key => $label): ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php selected($current_status, $key); ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
                
                <select id="filter-job">
                    <option value=""><?php _e('All Jobs', 'careers-manager'); ?></option>
                    <?php
                    $jobs = get_posts(array('post_type' => 'career_job', 'posts_per_page' => -1));
                    foreach ($jobs as $job):
                    ?>
                        <option value="<?php echo esc_attr($job->ID); ?>" <?php selected($current_job, $job->ID); ?>><?php echo esc_html($job->post_title); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="careers-applications-table">
                <div class="careers-table-header">
                    <div class="table-cell"><?php _e('Applicant', 'careers-manager'); ?></div>
                    <div class="table-cell"><?php _e('Job', 'careers-manager'); ?></div>
                    <div class="table-cell"><?php _e('Status', 'careers-manager'); ?></div>
                    <div class="table-cell"><?php _e('Applied', 'careers-manager'); ?></div>
                    <div class="table-cell"><?php _e('Actions', 'careers-manager'); ?></div>
                </div>
                
                <?php if (!empty($applications)): ?>
                    <?php foreach ($applications as $application): ?>
                        <div class="careers-table-row">
                            <div class="table-cell">
                                <strong><?php echo esc_html($application->display_name); ?></strong><br>
                                <small><?php echo esc_html($application->user_email); ?></small>
                            </div>
                            <div class="table-cell"><?php echo esc_html($application->job_title); ?></div>
                            <div class="table-cell">
                                <select class="careers-status-select" data-application-id="<?php echo esc_attr($application->id); ?>">
                                    <?php foreach ($statuses as $key => $label): ?>
                                        <option value="<?php echo esc_attr($key); ?>" <?php selected($application->status, $key); ?>><?php echo esc_html($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="table-cell"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($application->submitted_at))); ?></div>
                            <div class="table-cell">
                                <button class="careers-btn careers-btn-small view-application" data-application-id="<?php echo esc_attr($application->id); ?>">
                                    <?php _e('Review', 'careers-manager'); ?>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="careers-table-row">
                        <div class="table-cell" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                            <?php _e('No applications found.', 'careers-manager'); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render job applicants view
     */
    private function render_job_applicants_view($job_id) {
        if (!$job_id) {
            echo '<div class="careers-error">' . __('No job specified.', 'careers-manager') . '</div>';
            return;
        }
        
        $job = get_post($job_id);
        if (!$job || $job->post_type !== 'career_job') {
            echo '<div class="careers-error">' . __('Job not found.', 'careers-manager') . '</div>';
            return;
        }
        
        $applications = CareersApplicationDB::get_applications(array(
            'job_id' => $job_id,
            'limit' => 100
        ));
        
        $statuses = careers_get_application_statuses();
        ?>
        <div class="careers-admin-content">
            <div class="careers-admin-header">
                <a href="<?php echo esc_url(add_query_arg('view', 'jobs')); ?>" class="careers-back-link">
                    ← <?php _e('Back to Admin Dashboard', 'careers-manager'); ?>
                </a>
                <h1><?php _e('Job Applicants', 'careers-manager'); ?></h1>
                <p><?php printf(__('Managing applicants for: %s - %s', 'careers-manager'), esc_html($job->post_title), esc_html(get_post_meta($job_id, '_career_location', true))); ?></p>
                <div class="careers-applicant-count"><?php echo count($applications); ?> <?php _e('Applicants', 'careers-manager'); ?></div>
            </div>
            
            <div class="careers-applicant-list">
                <div class="careers-table-header">
                    <div class="table-cell"><?php _e('Applicant', 'careers-manager'); ?></div>
                    <div class="table-cell"><?php _e('Contact', 'careers-manager'); ?></div>
                    <div class="table-cell"><?php _e('Applied Date', 'careers-manager'); ?></div>
                    <div class="table-cell"><?php _e('Status', 'careers-manager'); ?></div>
                    <div class="table-cell"><?php _e('Documents', 'careers-manager'); ?></div>
                    <div class="table-cell"><?php _e('Actions', 'careers-manager'); ?></div>
                </div>
                
                <?php if (!empty($applications)): ?>
                    <?php foreach ($applications as $application): ?>
                        <div class="careers-table-row">
                            <div class="table-cell">
                                <strong><?php echo esc_html($application->display_name); ?></strong>
                            </div>
                            <div class="table-cell">
                                <div><?php echo esc_html($application->user_email); ?></div>
                                <?php
                                $meta = json_decode($application->meta, true);
                                if (isset($meta['phone'])) {
                                    echo '<div><small>' . esc_html($meta['phone']) . '</small></div>';
                                }
                                ?>
                            </div>
                            <div class="table-cell"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($application->submitted_at))); ?></div>
                            <div class="table-cell">
                                <span class="careers-status careers-status-<?php echo esc_attr($application->status); ?>">
                                    <?php echo esc_html($statuses[$application->status] ?? $application->status); ?>
                                </span>
                            </div>
                            <div class="table-cell">
                                <?php if ($application->resume_url): ?>
                                    <a href="<?php echo esc_url($application->resume_url); ?>" target="_blank" class="careers-document-link">
                                        📄 <?php _e('Resume', 'careers-manager'); ?>
                                    </a>
                                <?php endif; ?>
                                <?php if ($application->cover_letter_url): ?>
                                    <a href="<?php echo esc_url($application->cover_letter_url); ?>" target="_blank" class="careers-document-link">
                                        📄 <?php _e('Cover Letter', 'careers-manager'); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div class="table-cell">
                                <button class="careers-btn careers-btn-small view-application" data-application-id="<?php echo esc_attr($application->id); ?>">
                                    <?php _e('Review', 'careers-manager'); ?>
                                </button>
                                <button class="careers-btn careers-btn-small">
                                    <?php _e('Interview', 'careers-manager'); ?>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="careers-table-row">
                        <div class="table-cell" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                            <?php _e('No applicants yet for this position.', 'careers-manager'); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render admin modals
     */
    private function render_admin_modals() {
        ?>
        <!-- Job Edit Modal -->
        <div id="careers-job-modal" class="careers-modal" style="display: none;">
            <div class="careers-modal-content">
                <div class="careers-modal-header">
                    <h2 id="job-modal-title"><?php _e('Edit Job', 'careers-manager'); ?></h2>
                    <button class="careers-modal-close">&times;</button>
                </div>
                <div class="careers-modal-body">
                    <form id="careers-job-form">
                        <input type="hidden" id="job-id" name="job_id" value="">
                        
                        <div class="careers-form-group">
                            <label for="job-title"><?php _e('Job Title', 'careers-manager'); ?></label>
                            <input type="text" id="job-title" name="job_title" required>
                        </div>
                        
                        <div class="careers-form-group">
                            <label for="job-location"><?php _e('Location', 'careers-manager'); ?></label>
                            <input type="text" id="job-location" name="job_location">
                        </div>
                        
                        <div class="careers-form-group">
                            <label for="job-description"><?php _e('Description', 'careers-manager'); ?></label>
                            <textarea id="job-description" name="job_description" rows="6"></textarea>
                        </div>
                        
                        <div class="careers-form-row">
                            <div class="careers-form-group">
                                <label for="job-employment-type"><?php _e('Employment Type', 'careers-manager'); ?></label>
                                <select id="job-employment-type" name="job_employment_type">
                                    <option value=""><?php _e('Select Type', 'careers-manager'); ?></option>
                                    <?php foreach (careers_get_employment_types() as $key => $label): ?>
                                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="careers-form-group">
                                <label for="job-experience-level"><?php _e('Experience Level', 'careers-manager'); ?></label>
                                <select id="job-experience-level" name="job_experience_level">
                                    <option value=""><?php _e('Select Level', 'careers-manager'); ?></option>
                                    <?php foreach (careers_get_experience_levels() as $key => $label): ?>
                                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="careers-form-group">
                            <label for="job-salary-range"><?php _e('Salary Range', 'careers-manager'); ?></label>
                            <div class="careers-salary-inputs">
                                <input type="number" id="job-salary-min" name="job_salary_min" placeholder="<?php _e('Min', 'careers-manager'); ?>">
                                <span>-</span>
                                <input type="number" id="job-salary-max" name="job_salary_max" placeholder="<?php _e('Max', 'careers-manager'); ?>">
                            </div>
                        </div>
                        
                        <div class="careers-form-group">
                            <label for="job-benefits"><?php _e('Benefits (one per line)', 'careers-manager'); ?></label>
                            <textarea id="job-benefits" name="job_benefits" rows="4"></textarea>
                        </div>
                        
                        <div class="careers-form-group">
                            <label for="job-status"><?php _e('Status', 'careers-manager'); ?></label>
                            <select id="job-status" name="job_status">
                                <option value="draft"><?php _e('Draft', 'careers-manager'); ?></option>
                                <option value="publish"><?php _e('Published', 'careers-manager'); ?></option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="careers-modal-footer">
                    <button type="button" class="careers-btn careers-btn-secondary" id="cancel-job"><?php _e('Cancel', 'careers-manager'); ?></button>
                    <button type="button" class="careers-btn careers-btn-primary" id="save-job"><?php _e('Update Job', 'careers-manager'); ?></button>
                </div>
            </div>
        </div>
        
        <!-- Application View Modal -->
        <div id="careers-application-modal" class="careers-modal" style="display: none;">
            <div class="careers-modal-content">
                <div class="careers-modal-header">
                    <h2><?php _e('Application Details', 'careers-manager'); ?></h2>
                    <button class="careers-modal-close">&times;</button>
                </div>
                <div class="careers-modal-body" id="application-details">
                    <!-- Application details will be loaded here -->
                </div>
                <div class="careers-modal-footer">
                    <button type="button" class="careers-btn careers-btn-secondary careers-modal-close"><?php _e('Close', 'careers-manager'); ?></button>
                </div>
            </div>
                 </div>
         <?php
     }

    /**
     * Handle admin load job AJAX request
     */
    public function handle_admin_load_job() {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'], 'careers_nonce') || !current_user_can('manage_options')) {
            wp_send_json_error(__('Security check failed.', 'careers-manager'));
        }
        
        $job_id = intval($_POST['job_id']);
        
        if ($job_id === 0) {
            // New job
            wp_send_json_success(array(
                'job_id' => 0,
                'title' => '',
                'content' => '',
                'location' => '',
                'employment_type' => '',
                'experience_level' => '',
                'salary_min' => '',
                'salary_max' => '',
                'benefits' => '',
                'status' => 'draft'
            ));
        }
        
        $job = get_post($job_id);
        if (!$job || $job->post_type !== 'career_job') {
            wp_send_json_error(__('Job not found.', 'careers-manager'));
        }
        
        $job_data = array(
            'job_id' => $job_id,
            'title' => $job->post_title,
            'content' => $job->post_content,
            'location' => get_post_meta($job_id, '_career_location', true),
            'employment_type' => get_post_meta($job_id, '_career_employment_type', true),
            'experience_level' => get_post_meta($job_id, '_career_experience_level', true),
            'salary_min' => get_post_meta($job_id, '_career_salary_min', true),
            'salary_max' => get_post_meta($job_id, '_career_salary_max', true),
            'benefits' => get_post_meta($job_id, '_career_benefits', true),
            'status' => $job->post_status
        );
        
        wp_send_json_success($job_data);
    }
    
    /**
     * Handle admin save job AJAX request
     */
    public function handle_admin_save_job() {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'], 'careers_nonce') || !current_user_can('manage_options')) {
            wp_send_json_error(__('Security check failed.', 'careers-manager'));
        }
        
        $job_id = intval($_POST['job_id']);
        $title = sanitize_text_field($_POST['job_title']);
        $content = wp_kses_post($_POST['job_description']);
        $status = sanitize_text_field($_POST['job_status']);
        
        if (empty($title)) {
            wp_send_json_error(__('Job title is required.', 'careers-manager'));
        }
        
        $post_data = array(
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => $status,
            'post_type' => 'career_job'
        );
        
        if ($job_id > 0) {
            $post_data['ID'] = $job_id;
            $result = wp_update_post($post_data);
        } else {
            $result = wp_insert_post($post_data);
            $job_id = $result;
        }
        
        if (is_wp_error($result)) {
            wp_send_json_error(__('Error saving job.', 'careers-manager'));
        }
        
        // Save meta fields
        $meta_fields = array(
            'job_location' => '_career_location',
            'job_employment_type' => '_career_employment_type',
            'job_experience_level' => '_career_experience_level',
            'job_salary_min' => '_career_salary_min',
            'job_salary_max' => '_career_salary_max',
            'job_benefits' => '_career_benefits'
        );
        
        foreach ($meta_fields as $field => $meta_key) {
            if (isset($_POST[$field])) {
                update_post_meta($job_id, $meta_key, sanitize_text_field($_POST[$field]));
            }
        }
        
        wp_send_json_success(array(
            'job_id' => $job_id,
            'message' => __('Job saved successfully.', 'careers-manager')
        ));
    }
    
    /**
     * Handle admin load application AJAX request
     */
    public function handle_admin_load_application() {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'], 'careers_nonce') || !current_user_can('manage_options')) {
            wp_send_json_error(__('Security check failed.', 'careers-manager'));
        }
        
        $application_id = intval($_POST['application_id']);
        $application = CareersApplicationDB::get_application($application_id);
        
        if (!$application) {
            wp_send_json_error(__('Application not found.', 'careers-manager'));
        }
        
        $user = get_user_by('id', $application->user_id);
        $job = get_post($application->job_id);
        $meta = json_decode($application->meta, true);
        
        $html = '<div class="careers-application-details">';
        $html .= '<h3>' . sprintf(__('Application for: %s', 'careers-manager'), esc_html($job->post_title)) . '</h3>';
        
        $html .= '<div class="careers-applicant-info">';
        $html .= '<h4>' . __('Applicant Information', 'careers-manager') . '</h4>';
        $html .= '<p><strong>' . __('Name:', 'careers-manager') . '</strong> ' . esc_html($user->display_name) . '</p>';
        $html .= '<p><strong>' . __('Email:', 'careers-manager') . '</strong> ' . esc_html($user->user_email) . '</p>';
        
        if (isset($meta['phone'])) {
            $html .= '<p><strong>' . __('Phone:', 'careers-manager') . '</strong> ' . esc_html($meta['phone']) . '</p>';
        }
        if (isset($meta['city']) && isset($meta['state'])) {
            $html .= '<p><strong>' . __('Location:', 'careers-manager') . '</strong> ' . esc_html($meta['city']) . ', ' . esc_html($meta['state']) . '</p>';
        }
        $html .= '</div>';
        
        if (!empty($meta)) {
            $html .= '<div class="careers-application-meta">';
            $html .= '<h4>' . __('Application Details', 'careers-manager') . '</h4>';
            
            foreach ($meta as $key => $value) {
                if (!in_array($key, ['phone', 'city', 'state']) && !empty($value)) {
                    $label = ucwords(str_replace('_', ' ', $key));
                    $html .= '<p><strong>' . esc_html($label) . ':</strong> ' . esc_html($value) . '</p>';
                }
            }
            $html .= '</div>';
        }
        
        $html .= '<div class="careers-application-documents">';
        $html .= '<h4>' . __('Documents', 'careers-manager') . '</h4>';
        if ($application->resume_url) {
            $html .= '<p><a href="' . esc_url($application->resume_url) . '" target="_blank" class="careers-document-link">📄 ' . __('View Resume', 'careers-manager') . '</a></p>';
        }
        if ($application->cover_letter_url) {
            $html .= '<p><a href="' . esc_url($application->cover_letter_url) . '" target="_blank" class="careers-document-link">📄 ' . __('View Cover Letter', 'careers-manager') . '</a></p>';
        }
        $html .= '</div>';
        
        $html .= '<div class="careers-application-status">';
        $html .= '<h4>' . __('Status Management', 'careers-manager') . '</h4>';
        $html .= '<select id="application-status-select" data-application-id="' . esc_attr($application_id) . '">';
        
        $statuses = careers_get_application_statuses();
        foreach ($statuses as $key => $label) {
            $selected = ($application->status === $key) ? 'selected' : '';
            $html .= '<option value="' . esc_attr($key) . '" ' . $selected . '>' . esc_html($label) . '</option>';
        }
        $html .= '</select>';
        $html .= '<button type="button" class="careers-btn careers-btn-primary" id="update-application-status">' . __('Update Status', 'careers-manager') . '</button>';
        $html .= '</div>';
        
        $html .= '<div class="careers-application-dates">';
        $html .= '<p><strong>' . __('Applied:', 'careers-manager') . '</strong> ' . esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($application->submitted_at))) . '</p>';
        $html .= '<p><strong>' . __('Last Updated:', 'careers-manager') . '</strong> ' . esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($application->updated_at))) . '</p>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        wp_send_json_success(array('html' => $html));
    }
    
    /**
     * Handle admin update status AJAX request
     */
    public function handle_admin_update_status() {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'], 'careers_nonce') || !current_user_can('manage_options')) {
            wp_send_json_error(__('Security check failed.', 'careers-manager'));
        }
        
        $application_id = intval($_POST['application_id']);
        $new_status = sanitize_text_field($_POST['status']);
        
        $valid_statuses = array_keys(careers_get_application_statuses());
        if (!in_array($new_status, $valid_statuses)) {
            wp_send_json_error(__('Invalid status.', 'careers-manager'));
        }
        
        $result = CareersApplicationDB::update_application_status($application_id, $new_status);
        
        if ($result) {
            // Send status update email
            CareersEmails::send_status_update($application_id, $new_status);
            
            wp_send_json_success(array(
                'message' => __('Status updated successfully.', 'careers-manager')
            ));
        } else {
            wp_send_json_error(__('Error updating status.', 'careers-manager'));
        }
    }

    /**
     * Debug shortcode [careers_debug]
     */
    public function debug_shortcode($atts) {
        ob_start();
        
        echo '<div style="background: #f0f0f0; border: 1px solid #ddd; padding: 15px; margin: 20px 0; font-family: monospace;">';
        echo '<h3>🔍 Careers Manager Plugin Debug Check</h3>';
        
        // Check if plugin class exists
        if (class_exists('CareersManager')) {
            echo '<p style="color: green;">✅ CareersManager class exists</p>';
        } else {
            echo '<p style="color: red;">❌ CareersManager class NOT found</p>';
        }
        
        // Check if shortcodes class exists
        if (class_exists('CareersShortcodes')) {
            echo '<p style="color: green;">✅ CareersShortcodes class exists</p>';
        } else {
            echo '<p style="color: red;">❌ CareersShortcodes class NOT found</p>';
        }
        
        // Check if shortcodes are registered
        global $shortcode_tags;
        $careers_shortcodes = array(
            'careers_job_listings',
            'careers_apply_form', 
            'careers_dashboard',
            'careers_auth_form',
            'careers_admin_dashboard',
            'careers_debug'
        );
        
        echo '<h4>Shortcode Registration Status:</h4>';
        $all_registered = true;
        foreach ($careers_shortcodes as $shortcode) {
            if (isset($shortcode_tags[$shortcode])) {
                echo '<p style="color: green;">✅ [' . $shortcode . '] is registered</p>';
            } else {
                echo '<p style="color: red;">❌ [' . $shortcode . '] is NOT registered</p>';
                $all_registered = false;
            }
        }
        
        // Check if custom post type exists
        if (post_type_exists('career_job')) {
            echo '<p style="color: green;">✅ career_job post type exists</p>';
        } else {
            echo '<p style="color: red;">❌ career_job post type NOT found</p>';
        }
        
        // Check if plugin functions exist
        if (function_exists('careers_get_application_statuses')) {
            echo '<p style="color: green;">✅ Helper functions loaded</p>';
        } else {
            echo '<p style="color: red;">❌ Helper functions NOT loaded</p>';
        }
        
        // Check user permissions
        if (current_user_can('manage_options')) {
            echo '<p style="color: green;">✅ You have admin permissions</p>';
        } else {
            echo '<p style="color: orange;">⚠️ You do not have admin permissions (needed for admin dashboard)</p>';
        }
        
        // WordPress environment check
        echo '<h4>WordPress Environment:</h4>';
        echo '<p><strong>WordPress Version:</strong> ' . get_bloginfo('version') . '</p>';
        echo '<p><strong>PHP Version:</strong> ' . PHP_VERSION . '</p>';
        echo '<p><strong>Active Theme:</strong> ' . wp_get_theme()->get('Name') . '</p>';
        
        // Plugin status
        if (function_exists('is_plugin_active')) {
            if (is_plugin_active('careers/careers.php')) {
                echo '<p style="color: green;">✅ Plugin is ACTIVE</p>';
            } else {
                echo '<p style="color: red;">❌ Plugin is NOT ACTIVE</p>';
            }
        }
        
        echo '</div>';
        
        // Show solution steps
        echo '<div style="background: #e7f3ff; border: 1px solid #bee5eb; padding: 15px; margin: 20px 0;">';
        echo '<h4>🛠️ How to Fix Common Issues:</h4>';
        
        if (!$all_registered) {
            echo '<div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; margin: 10px 0;">';
            echo '<h5>⚠️ Shortcodes Not Registered - Try These Steps:</h5>';
            echo '<ol>';
            echo '<li>Go to <strong>WordPress Admin → Plugins</strong></li>';
            echo '<li>Find <strong>"Careers Manager"</strong></li>';
            echo '<li>Click <strong>"Deactivate"</strong> then <strong>"Activate"</strong></li>';
            echo '<li>Refresh this page and check again</li>';
            echo '</ol>';
            echo '</div>';
        }
        
        echo '<ol>';
        echo '<li><strong>Plugin Not Active:</strong> Go to WordPress Admin → Plugins → Find "Careers Manager" → Click "Activate"</li>';
        echo '<li><strong>Permission Issues:</strong> Make sure you are logged in as an Administrator</li>';
        echo '<li><strong>Cache Issues:</strong> Clear any caching plugins or browser cache</li>';
        echo '<li><strong>Theme Conflicts:</strong> Try switching to Twenty Twenty-Four theme temporarily</li>';
        echo '<li><strong>PHP Errors:</strong> Check your site\'s error log for any PHP errors</li>';
        echo '</ol>';
        
        echo '<h5>Quick Test:</h5>';
        echo '<p>If everything above shows green checkmarks, try using <code>[careers_admin_dashboard]</code> again.</p>';
        
        echo '<p style="color: #666; font-size: 12px;"><strong>Note:</strong> Remove this [careers_debug] shortcode after troubleshooting is complete.</p>';
        echo '</div>';
        
        return ob_get_clean();
    }
    
    /**
     * Display single job details - Matching NMXR Design
     */
    private function display_single_job($job) {
        $job_id = $job->ID;
        $location = get_post_meta($job_id, '_career_location', true);
        $employment_type = get_post_meta($job_id, '_career_employment_type', true);
        $salary_min = get_post_meta($job_id, '_career_salary_min', true);
        $salary_max = get_post_meta($job_id, '_career_salary_max', true);
        $experience_level = get_post_meta($job_id, '_career_experience_level', true);
        $posted_date = get_the_date('', $job);
        
        // New fields to match React app structure
        $summary = get_post_meta($job_id, '_career_summary', true);
        $responsibilities = get_post_meta($job_id, '_career_responsibilities', true);
        $requirements = get_post_meta($job_id, '_career_requirements', true);
        $equipment = get_post_meta($job_id, '_career_equipment', true);
        $benefits = get_post_meta($job_id, '_career_benefits', true);
        $equipment_vehicle = get_post_meta($job_id, '_career_equipment_vehicle', true);
        $licensing = get_post_meta($job_id, '_career_licensing', true);
        
        ?>
        <!-- Main Content Area -->
        <div class="careers-job-detail-container">
            <div class="careers-job-main">
                <!-- Job Header -->
                <div class="careers-job-detail-header">
                    <h1 class="careers-job-detail-title"><?php echo esc_html($job->post_title); ?></h1>
                    <div class="careers-job-badges">
                        <span class="careers-location-badge">📍 <?php echo esc_html($location ?: 'Texas'); ?></span>
                        <span class="careers-type-badge">XRT</span>
                        <span class="careers-employment-badge">Full Time</span>
                    </div>
                </div>
                
                <!-- Position Overview -->
                <div class="careers-section">
                    <h2>Position Overview</h2>
                    <div class="careers-job-description">
                        <?php echo wpautop($job->post_content); ?>
                    </div>
                </div>
                
                <!-- Responsibilities -->
                <div class="careers-section">
                    <h2>Responsibilities</h2>
                    <ul class="careers-responsibility-list">
                        <?php if (!empty($responsibilities)): ?>
                            <?php foreach (array_filter(explode("\n", $responsibilities)) as $responsibility): ?>
                                <li><?php echo esc_html(trim($responsibility)); ?></li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>Perform diagnostic imaging examinations with portable X-ray equipment</li>
                            <li>Travel to nursing homes and other healthcare facilities</li>
                            <li>Ensure proper patient positioning and image quality</li>
                            <li>Maintain detailed patient records and documentation</li>
                            <li>Collaborate with healthcare providers regarding examination results</li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <!-- Requirements -->
                <div class="careers-section">
                    <h2>Requirements</h2>
                    <ul class="careers-requirement-list">
                        <?php if (!empty($requirements)): ?>
                            <?php foreach (array_filter(explode("\n", $requirements)) as $requirement): ?>
                                <li><?php echo esc_html(trim($requirement)); ?></li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>Valid ARRT certification</li>
                            <li>Texas state radiologic technologist license</li>
                            <li>Valid driver's license with clean driving record</li>
                            <li>Ability to lift and move equipment weighing up to 50 pounds</li>
                            <li>Excellent patient care and communication skills</li>
                            <li>1+ years of experience preferred (new graduates welcome to apply)</li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <!-- Equipment Used -->
                <div class="careers-section">
                    <h2>Equipment Used</h2>
                    <?php if (!empty($equipment)): ?>
                        <ul class="careers-equipment-list">
                            <?php foreach (array_filter(explode("\n", $equipment)) as $equipment_item): ?>
                                <li><?php echo esc_html(trim($equipment_item)); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>DR-130 Portable X-Ray Machine</p>
                    <?php endif; ?>
                </div>
                
                <!-- State Licensing Info -->
                <div class="careers-licensing-box">
                    <h3>State Licensing Information</h3>
                    <?php if (!empty($licensing)): ?>
                        <p><?php echo esc_html($licensing); ?></p>
                    <?php else: ?>
                        <p>Texas requires Radiologic Technologists to hold both ARRT certification and state licensure through the Texas Medical Board (TMB).</p>
                    <?php endif; ?>
                </div>
                
                <!-- Company Vehicle Section -->
                <?php if (!empty($equipment_vehicle)): ?>
                <div class="careers-section">
                    <h2>Company Vehicle</h2>
                    <div class="careers-vehicle-image">
                        <div class="careers-placeholder-image">📷</div>
                    </div>
                    <div class="careers-vehicle-description">
                        <?php echo wpautop(esc_html($equipment_vehicle)); ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="careers-section">
                    <h2>Company Vehicle</h2>
                    <div class="careers-vehicle-image">
                        <div class="careers-placeholder-image">📷</div>
                    </div>
                    <p>As a mobile technologist, you'll drive our custom 2025 Subaru Forester with safety upgrades and specialized equipment for diagnostic services.</p>
                    
                    <div class="careers-vehicle-features">
                        <div class="careers-feature-row">
                            <div class="careers-feature-item">
                                <h4>Winch System</h4>
                                <p>Reduces physical strain when loading equipment</p>
                            </div>
                            <div class="careers-feature-item">
                                <h4>Inverter System</h4>
                                <p>For on-the-go equipment charging</p>
                            </div>
                        </div>
                        <div class="careers-feature-row">
                            <div class="careers-feature-item">
                                <h4>GPS & Camera System</h4>
                                <p>Enhanced security and navigation</p>
                            </div>
                            <div class="careers-feature-item">
                                <h4>Custom Layout</h4>
                                <p>Optimized for technologist workflow</p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Location Information -->
                <div class="careers-section">
                    <h2>Location Information</h2>
                    <div class="careers-location-image">
                        <div class="careers-placeholder-image">📷</div>
                    </div>
                    <div class="careers-location-info">
                        <span class="careers-location-badge">📍 Texas</span>
                        <p>This position covers facilities in the Texas area. Technologists typically service a region within 50-75 miles of their home base.</p>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="careers-job-sidebar">
                <!-- Quick Info -->
                <div class="careers-sidebar-section">
                    <h3>Quick Info</h3>
                    <div class="careers-info-item">
                        <span class="careers-info-label">Job Type</span>
                        <span class="careers-info-value">Full Time</span>
                    </div>
                    <div class="careers-info-item">
                        <span class="careers-info-label">Salary Range</span>
                        <span class="careers-info-value">Competitive, based on experience</span>
                    </div>
                    <div class="careers-info-item">
                        <span class="careers-info-label">Schedule</span>
                        <span class="careers-info-value">Flexible, with on-call options</span>
                    </div>
                    <div class="careers-info-item">
                        <span class="careers-info-label">Experience Level</span>
                        <span class="careers-info-value">Entry-level/Experienced</span>
                    </div>
                    <div class="careers-info-item">
                        <span class="careers-info-label">Certifications</span>
                        <span class="careers-info-value">Required (see job details)</span>
                    </div>
                </div>
                
                <!-- Top-Tier Benefits -->
                <div class="careers-sidebar-section">
                    <h3>Top-Tier Benefits</h3>
                    <ul class="careers-benefits-list">
                        <?php if (!empty($benefits)): ?>
                            <?php foreach (array_filter(explode("\n", $benefits)) as $benefit): ?>
                                <li>
                                    <svg class="careers-checkmark" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <?php echo esc_html(trim($benefit)); ?>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>
                                <svg class="careers-checkmark" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Competitive salary
                            </li>
                            <li>
                                <svg class="careers-checkmark" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Flexible scheduling
                            </li>
                            <li>
                                <svg class="careers-checkmark" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Health insurance options
                            </li>
                            <li>
                                <svg class="careers-checkmark" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                401(k) retirement plan
                            </li>
                            <li>
                                <svg class="careers-checkmark" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Continuing education support
                            </li>
                            <li>
                                <svg class="careers-checkmark" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Mileage reimbursement
                            </li>
                            <li>
                                <svg class="careers-checkmark" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Career advancement opportunities
                            </li>
                        <?php endif; ?>
                    </ul>
                    <p class="careers-benefits-note">Whether you're a new grad or a seasoned technologist, we provide the tools, training, and flexibility to help you thrive.</p>
                </div>
                
                <!-- A Typical Day -->
                <div class="careers-sidebar-section">
                    <h3>A Typical Day</h3>
                    <ul class="careers-day-list">
                        <?php 
                        $day_in_life_items = !empty($day_in_life) ? array_filter(explode("\n", $day_in_life)) : array();
                        if (!empty($day_in_life_items)): 
                        ?>
                            <?php foreach ($day_in_life_items as $index => $day_item): ?>
                                <li>
                                    <div class="careers-day-number"><?php echo $index + 1; ?></div>
                                    <span><?php echo esc_html(trim($day_item)); ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>
                                <div class="careers-day-number">1</div>
                                <span>Drive company vehicle to assigned facilities</span>
                            </li>
                            <li>
                                <div class="careers-day-number">2</div>
                                <span>Unload and operate portable imaging equipment</span>
                            </li>
                            <li>
                                <div class="careers-day-number">3</div>
                                <span>Capture and transmit studies wirelessly</span>
                            </li>
                            <li>
                                <div class="careers-day-number">4</div>
                                <span>Repeat service multiple sites during shift</span>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <p class="careers-day-note">Approximately 60-70% of your day will be in transit between facilities, with the remaining time dedicated to setup and exam procedures, all while building bonds with your workflows.</p>
                </div>
                
                <!-- Take the First Step -->
                <div class="careers-sidebar-section careers-apply-section">
                    <h3>Take the First Step</h3>
                    <p>Submit your application today and join our team of mobile diagnostic professionals.</p>
                    <a href="<?php echo esc_url(home_url('/apply/?job_id=' . $job_id)); ?>" class="careers-apply-now-btn">Apply Now →</a>
                    <p class="careers-apply-note">Have questions not-list on this job?<br><a href="<?php echo esc_url(home_url('/contact')); ?>">Share this position</a></p>
                </div>
            </div>
        </div>
        <?php
    }
} 