<?php
/**
 * Careers Admin Interface
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CareersAdmin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_careers_admin_action', array($this, 'handle_admin_action'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __('Careers', 'careers-manager'),
            __('Careers', 'careers-manager'),
            'manage_options',
            'careers-manager',
            array($this, 'admin_dashboard_page'),
            'dashicons-businessman',
            25
        );
        
        // Jobs submenu
        add_submenu_page(
            'careers-manager',
            __('Jobs', 'careers-manager'),
            __('Jobs', 'careers-manager'),
            'manage_options',
            'careers-jobs',
            array($this, 'jobs_page')
        );
        
        // Applications submenu
        add_submenu_page(
            'careers-manager',
            __('Applications', 'careers-manager'),
            __('Applications', 'careers-manager'),
            'manage_options',
            'careers-applications',
            array($this, 'applications_page')
        );
        
        // Analytics submenu
        add_submenu_page(
            'careers-manager',
            __('Analytics', 'careers-manager'),
            __('Analytics', 'careers-manager'),
            'manage_options',
            'careers-analytics',
            array($this, 'analytics_page')
        );
    }
    
    /**
     * Admin dashboard page
     */
    public function admin_dashboard_page() {
        $stats = CareersApplicationDB::get_stats();
        ?>
        <div class="wrap">
            <h1><?php _e('Careers Dashboard', 'careers-manager'); ?></h1>
            
            <div class="careers-admin-stats">
                <div class="careers-stat-boxes">
                    <div class="careers-stat-box">
                        <h3><?php echo intval($stats['total']); ?></h3>
                        <p><?php _e('Total Applications', 'careers-manager'); ?></p>
                    </div>
                    
                    <div class="careers-stat-box">
                        <h3><?php echo isset($stats['by_status']['pending']) ? intval($stats['by_status']['pending']) : 0; ?></h3>
                        <p><?php _e('Pending Applications', 'careers-manager'); ?></p>
                    </div>
                    
                    <div class="careers-stat-box">
                        <h3><?php echo intval($stats['recent']); ?></h3>
                        <p><?php _e('Applications (30 days)', 'careers-manager'); ?></p>
                    </div>
                    
                    <div class="careers-stat-box">
                        <h3><?php echo count($stats['by_job']); ?></h3>
                        <p><?php _e('Active Jobs', 'careers-manager'); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="careers-admin-content">
                <div class="careers-admin-section">
                    <h2><?php _e('Recent Applications', 'careers-manager'); ?></h2>
                    <?php $this->render_recent_applications(); ?>
                </div>
                
                <div class="careers-admin-section">
                    <h2><?php _e('Popular Jobs', 'careers-manager'); ?></h2>
                    <?php $this->render_popular_jobs($stats['by_job']); ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Jobs management page
     */
    public function jobs_page() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
        $job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;
        
        if ($action === 'edit' && $job_id) {
            $this->edit_job_page($job_id);
            return;
        }
        
        if ($action === 'new') {
            $this->edit_job_page(0);
            return;
        }
        
        // List all jobs
        $jobs = get_posts(array(
            'post_type' => 'career_job',
            'post_status' => array('publish', 'draft'),
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php _e('Jobs', 'careers-manager'); ?></h1>
            <a href="<?php echo esc_url(add_query_arg('action', 'new')); ?>" class="page-title-action"><?php _e('Add New', 'careers-manager'); ?></a>
            
            <?php if (isset($_GET['message']) && $_GET['message'] === 'saved'): ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php _e('Job saved successfully.', 'careers-manager'); ?></p>
                </div>
            <?php endif; ?>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th scope="col"><?php _e('Title', 'careers-manager'); ?></th>
                        <th scope="col"><?php _e('Location', 'careers-manager'); ?></th>
                        <th scope="col"><?php _e('Type', 'careers-manager'); ?></th>
                        <th scope="col"><?php _e('Applications', 'careers-manager'); ?></th>
                        <th scope="col"><?php _e('Status', 'careers-manager'); ?></th>
                        <th scope="col"><?php _e('Date', 'careers-manager'); ?></th>
                        <th scope="col"><?php _e('Actions', 'careers-manager'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($jobs)): ?>
                        <?php foreach ($jobs as $job): ?>
                            <?php
                            global $wpdb;
                            $application_count = $wpdb->get_var($wpdb->prepare(
                                "SELECT COUNT(*) FROM {$wpdb->prefix}careers_applications WHERE job_id = %d",
                                $job->ID
                            ));
                            $location = get_post_meta($job->ID, '_career_location', true);
                            $employment_type = get_post_meta($job->ID, '_career_employment_type', true);
                            $employment_types = careers_get_employment_types();
                            ?>
                            <tr>
                                <td>
                                    <strong>
                                        <a href="<?php echo esc_url(add_query_arg(array('action' => 'edit', 'job_id' => $job->ID))); ?>">
                                            <?php echo esc_html($job->post_title); ?>
                                        </a>
                                    </strong>
                                </td>
                                <td><?php echo esc_html($location); ?></td>
                                <td><?php echo isset($employment_types[$employment_type]) ? esc_html($employment_types[$employment_type]) : ''; ?></td>
                                <td><?php echo intval($application_count); ?></td>
                                <td>
                                    <span class="careers-status careers-status-<?php echo esc_attr($job->post_status); ?>">
                                        <?php echo esc_html(ucfirst($job->post_status)); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($job->post_date))); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(array('action' => 'edit', 'job_id' => $job->ID))); ?>" class="button button-small"><?php _e('Edit', 'careers-manager'); ?></a>
                                    <a href="<?php echo esc_url(get_permalink($job->ID)); ?>" class="button button-small" target="_blank"><?php _e('View', 'careers-manager'); ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7"><?php _e('No jobs found.', 'careers-manager'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    /**
     * Edit job page
     */
    public function edit_job_page($job_id) {
        $job = null;
        if ($job_id) {
            $job = get_post($job_id);
            if (!$job || $job->post_type !== 'career_job') {
                wp_die(__('Job not found.', 'careers-manager'));
            }
        }
        
        // Handle form submission
        if (isset($_POST['save_job']) && wp_verify_nonce($_POST['careers_job_nonce'], 'careers_save_job')) {
            $this->save_job($job_id);
            return;
        }
        
        // Get existing data
        $title = $job_id ? $job->post_title : '';
        $content = $job_id ? $job->post_content : '';
        $status = $job_id ? $job->post_status : 'draft';
        $location = $job_id ? get_post_meta($job_id, '_career_location', true) : '';
        $employment_type = $job_id ? get_post_meta($job_id, '_career_employment_type', true) : '';
        $salary_min = $job_id ? get_post_meta($job_id, '_career_salary_min', true) : '';
        $salary_max = $job_id ? get_post_meta($job_id, '_career_salary_max', true) : '';
        $experience_level = $job_id ? get_post_meta($job_id, '_career_experience_level', true) : '';
        $benefits = $job_id ? get_post_meta($job_id, '_career_benefits', true) : '';
        $equipment_vehicle = $job_id ? get_post_meta($job_id, '_career_equipment_vehicle', true) : '';
        $licensing = $job_id ? get_post_meta($job_id, '_career_licensing', true) : '';
        $day_in_life = $job_id ? get_post_meta($job_id, '_career_day_in_life', true) : '';
        
        // New fields to match React app structure
        $summary = $job_id ? get_post_meta($job_id, '_career_summary', true) : '';
        $responsibilities = $job_id ? get_post_meta($job_id, '_career_responsibilities', true) : '';
        $requirements = $job_id ? get_post_meta($job_id, '_career_requirements', true) : '';
        $equipment = $job_id ? get_post_meta($job_id, '_career_equipment', true) : '';
        
        // Convert textarea fields to arrays for repeater functionality
        $benefits_array = !empty($benefits) ? array_filter(explode("\n", $benefits)) : array('');
        $day_in_life_array = !empty($day_in_life) ? array_filter(explode("\n", $day_in_life)) : array('');
        $responsibilities_array = !empty($responsibilities) ? array_filter(explode("\n", $responsibilities)) : array('');
        $requirements_array = !empty($requirements) ? array_filter(explode("\n", $requirements)) : array('');
        $equipment_array = !empty($equipment) ? array_filter(explode("\n", $equipment)) : array('');
        
        ?>
        <div class="wrap">
            <h1><?php echo $job_id ? __('Edit Job', 'careers-manager') : __('Add New Job', 'careers-manager'); ?></h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('careers_save_job', 'careers_job_nonce'); ?>
                <input type="hidden" name="save_job" value="1" />
                
                <table class="form-table">
                    <tr>
                        <th><label for="job_title"><?php _e('Job Title', 'careers-manager'); ?></label></th>
                        <td><input type="text" id="job_title" name="job_title" value="<?php echo esc_attr($title); ?>" class="regular-text" required /></td>
                    </tr>
                    <tr>
                        <th><label for="job_content"><?php _e('Job Description', 'careers-manager'); ?></label></th>
                        <td>
                            <?php
                            wp_editor($content, 'job_content', array(
                                'textarea_name' => 'job_content',
                                'textarea_rows' => 10,
                                'media_buttons' => true,
                                'teeny' => false,
                                'quicktags' => true
                            ));
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="job_location"><?php _e('Location (City)', 'careers-manager'); ?></label></th>
                        <td><input type="text" id="job_location" name="job_location" value="<?php echo esc_attr($location); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th><label for="job_employment_type"><?php _e('Employment Type', 'careers-manager'); ?></label></th>
                        <td>
                            <select id="job_employment_type" name="job_employment_type">
                                <option value=""><?php _e('Select Employment Type', 'careers-manager'); ?></option>
                                <?php foreach (careers_get_employment_types() as $key => $label): ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($employment_type, $key); ?>><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="job_salary_min"><?php _e('Minimum Salary', 'careers-manager'); ?></label></th>
                        <td><input type="number" id="job_salary_min" name="job_salary_min" value="<?php echo esc_attr($salary_min); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th><label for="job_salary_max"><?php _e('Maximum Salary', 'careers-manager'); ?></label></th>
                        <td><input type="number" id="job_salary_max" name="job_salary_max" value="<?php echo esc_attr($salary_max); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th><label for="job_experience_level"><?php _e('Experience Level', 'careers-manager'); ?></label></th>
                        <td>
                            <select id="job_experience_level" name="job_experience_level">
                                <option value=""><?php _e('Select Experience Level', 'careers-manager'); ?></option>
                                <?php foreach (careers_get_experience_levels() as $key => $label): ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($experience_level, $key); ?>><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="job_summary"><?php _e('Job Summary', 'careers-manager'); ?></label></th>
                        <td>
                            <textarea id="job_summary" name="job_summary" rows="3" cols="50" class="large-text" placeholder="Brief summary for job cards"><?php echo esc_textarea($summary); ?></textarea>
                            <p class="description"><?php _e('Brief description that appears on job cards (150-200 characters recommended)', 'careers-manager'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label><?php _e('Responsibilities', 'careers-manager'); ?></label></th>
                        <td>
                            <div id="responsibilities-repeater">
                                <?php foreach ($responsibilities_array as $index => $responsibility): ?>
                                    <div class="repeater-row" style="margin-bottom: 10px; display: flex; align-items: center;">
                                        <input type="text" name="job_responsibilities[]" value="<?php echo esc_attr(trim($responsibility)); ?>" class="large-text" placeholder="Enter responsibility..." style="margin-right: 10px;" />
                                        <button type="button" class="button remove-row" style="background: #dc3232; color: white;">Remove</button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" id="add-responsibility" class="button button-secondary">Add Responsibility</button>
                            <p class="description"><?php _e('Add the main job responsibilities. These will appear as bullet points.', 'careers-manager'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label><?php _e('Requirements', 'careers-manager'); ?></label></th>
                        <td>
                            <div id="requirements-repeater">
                                <?php foreach ($requirements_array as $index => $requirement): ?>
                                    <div class="repeater-row" style="margin-bottom: 10px; display: flex; align-items: center;">
                                        <input type="text" name="job_requirements[]" value="<?php echo esc_attr(trim($requirement)); ?>" class="large-text" placeholder="Enter requirement..." style="margin-right: 10px;" />
                                        <button type="button" class="button remove-row" style="background: #dc3232; color: white;">Remove</button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" id="add-requirement" class="button button-secondary">Add Requirement</button>
                            <p class="description"><?php _e('Add job requirements and qualifications. These will appear as bullet points.', 'careers-manager'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label><?php _e('Equipment Used', 'careers-manager'); ?></label></th>
                        <td>
                            <div id="equipment-repeater">
                                <?php foreach ($equipment_array as $index => $equipment_item): ?>
                                    <div class="repeater-row" style="margin-bottom: 10px; display: flex; align-items: center;">
                                        <input type="text" name="job_equipment[]" value="<?php echo esc_attr(trim($equipment_item)); ?>" class="large-text" placeholder="Enter equipment..." style="margin-right: 10px;" />
                                        <button type="button" class="button remove-row" style="background: #dc3232; color: white;">Remove</button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" id="add-equipment" class="button button-secondary">Add Equipment</button>
                            <p class="description"><?php _e('List the main equipment used for this position.', 'careers-manager'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label><?php _e('Benefits', 'careers-manager'); ?></label></th>
                        <td>
                            <div id="benefits-repeater">
                                <?php foreach ($benefits_array as $index => $benefit): ?>
                                    <div class="repeater-row" style="margin-bottom: 10px; display: flex; align-items: center;">
                                        <input type="text" name="job_benefits[]" value="<?php echo esc_attr(trim($benefit)); ?>" class="large-text" placeholder="Enter benefit..." style="margin-right: 10px;" />
                                        <button type="button" class="button remove-row" style="background: #dc3232; color: white;">Remove</button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" id="add-benefit" class="button button-secondary">Add Benefit</button>
                            <p class="description"><?php _e('Add job benefits. These will appear with green checkmarks.', 'careers-manager'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="job_equipment_vehicle"><?php _e('Equipment & Vehicle Info', 'careers-manager'); ?></label></th>
                        <td><textarea id="job_equipment_vehicle" name="job_equipment_vehicle" rows="4" cols="50" class="large-text"><?php echo esc_textarea($equipment_vehicle); ?></textarea></td>
                    </tr>
                    <tr>
                        <th><label for="job_licensing"><?php _e('Licensing Info', 'careers-manager'); ?></label></th>
                        <td><textarea id="job_licensing" name="job_licensing" rows="4" cols="50" class="large-text"><?php echo esc_textarea($licensing); ?></textarea></td>
                    </tr>
                    <tr>
                        <th><label><?php _e('A Typical Day', 'careers-manager'); ?></label></th>
                        <td>
                            <div id="day-in-life-repeater">
                                <?php foreach ($day_in_life_array as $index => $day_item): ?>
                                    <div class="repeater-row" style="margin-bottom: 10px; display: flex; align-items: center;">
                                        <span class="step-number" style="background: #f0f0f0; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; margin-right: 10px; font-weight: bold; color: #c8102e;"><?php echo $index + 1; ?></span>
                                        <input type="text" name="job_day_in_life[]" value="<?php echo esc_attr(trim($day_item)); ?>" class="large-text" placeholder="Enter typical day activity..." style="margin-right: 10px;" />
                                        <button type="button" class="button remove-row" style="background: #dc3232; color: white;">Remove</button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" id="add-day-item" class="button button-secondary">Add Step</button>
                            <p class="description"><?php _e('Add steps for a typical workday. These will appear with numbered red dots.', 'careers-manager'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="job_status"><?php _e('Status', 'careers-manager'); ?></label></th>
                        <td>
                            <select id="job_status" name="job_status">
                                <option value="draft" <?php selected($status, 'draft'); ?>><?php _e('Draft', 'careers-manager'); ?></option>
                                <option value="publish" <?php selected($status, 'publish'); ?>><?php _e('Published', 'careers-manager'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php echo $job_id ? __('Update Job', 'careers-manager') : __('Create Job', 'careers-manager'); ?>" />
                    <a href="<?php echo esc_url(admin_url('admin.php?page=careers-jobs')); ?>" class="button"><?php _e('Cancel', 'careers-manager'); ?></a>
                </p>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Repeater functionality
            function addRepeaterRow(containerId, fieldName, placeholder) {
                const container = $('#' + containerId);
                const newRow = $('<div class="repeater-row" style="margin-bottom: 10px; display: flex; align-items: center;"></div>');
                
                if (containerId === 'day-in-life-repeater') {
                    const stepNumber = container.find('.repeater-row').length + 1;
                    newRow.append('<span class="step-number" style="background: #f0f0f0; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; margin-right: 10px; font-weight: bold; color: #c8102e;">' + stepNumber + '</span>');
                }
                
                newRow.append('<input type="text" name="' + fieldName + '[]" value="" class="large-text" placeholder="' + placeholder + '" style="margin-right: 10px;" />');
                newRow.append('<button type="button" class="button remove-row" style="background: #dc3232; color: white;">Remove</button>');
                
                container.append(newRow);
                updateStepNumbers();
            }
            
            function updateStepNumbers() {
                $('#day-in-life-repeater .step-number').each(function(index) {
                    $(this).text(index + 1);
                });
            }
            
            // Add button handlers
            $('#add-responsibility').click(function() {
                addRepeaterRow('responsibilities-repeater', 'job_responsibilities', 'Enter responsibility...');
            });
            
            $('#add-requirement').click(function() {
                addRepeaterRow('requirements-repeater', 'job_requirements', 'Enter requirement...');
            });
            
            $('#add-equipment').click(function() {
                addRepeaterRow('equipment-repeater', 'job_equipment', 'Enter equipment...');
            });
            
            $('#add-benefit').click(function() {
                addRepeaterRow('benefits-repeater', 'job_benefits', 'Enter benefit...');
            });
            
            $('#add-day-item').click(function() {
                addRepeaterRow('day-in-life-repeater', 'job_day_in_life', 'Enter typical day activity...');
            });
            
            // Remove button handler
            $(document).on('click', '.remove-row', function() {
                $(this).closest('.repeater-row').remove();
                updateStepNumbers();
            });
            
            // Initialize step numbers
            updateStepNumbers();
        });
        </script>
        <?php
    }
    
    /**
     * Save job
     */
    private function save_job($job_id) {
        $title = sanitize_text_field($_POST['job_title']);
        $content = wp_kses_post($_POST['job_content']);
        $status = sanitize_text_field($_POST['job_status']);
        
        $post_data = array(
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => $status,
            'post_type' => 'career_job'
        );
        
        if ($job_id) {
            $post_data['ID'] = $job_id;
            $result = wp_update_post($post_data);
        } else {
            $result = wp_insert_post($post_data);
            $job_id = $result;
        }
        
        if (!is_wp_error($result)) {
            // Save simple meta fields
            $simple_meta_fields = array(
                'job_location' => '_career_location',
                'job_employment_type' => '_career_employment_type',
                'job_salary_min' => '_career_salary_min',
                'job_salary_max' => '_career_salary_max',
                'job_experience_level' => '_career_experience_level',
                'job_equipment_vehicle' => '_career_equipment_vehicle',
                'job_licensing' => '_career_licensing',
                'job_summary' => '_career_summary'
            );
            
            foreach ($simple_meta_fields as $field => $meta_key) {
                if (isset($_POST[$field])) {
                    update_post_meta($job_id, $meta_key, sanitize_text_field($_POST[$field]));
                }
            }
            
            // Save array fields (convert arrays to newline-separated strings)
            $array_meta_fields = array(
                'job_responsibilities' => '_career_responsibilities',
                'job_requirements' => '_career_requirements',
                'job_equipment' => '_career_equipment',
                'job_benefits' => '_career_benefits',
                'job_day_in_life' => '_career_day_in_life'
            );
            
            foreach ($array_meta_fields as $field => $meta_key) {
                if (isset($_POST[$field]) && is_array($_POST[$field])) {
                    $clean_array = array_filter(array_map('sanitize_text_field', $_POST[$field]));
                    $value = implode("\n", $clean_array);
                    update_post_meta($job_id, $meta_key, $value);
                }
            }
            
            // Redirect back to jobs list with success message
            wp_redirect(add_query_arg(array(
                'page' => 'careers-jobs',
                'message' => 'saved'
            ), admin_url('admin.php')));
            exit;
        } else {
            // Handle error
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>' . __('Error saving job.', 'careers-manager') . '</p></div>';
            });
        }
    }

    /**
     * Applications management page
     */
    public function applications_page() {
        $current_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $current_job = isset($_GET['job_id']) ? intval($_GET['job_id']) : '';
        
        $applications = CareersApplicationDB::get_applications(array(
            'status' => $current_status,
            'job_id' => $current_job,
            'limit' => 50
        ));
        
        $statuses = careers_get_application_statuses();
        ?>
        <div class="wrap">
            <h1><?php _e('Applications', 'careers-manager'); ?></h1>
            
            <div class="careers-applications-filters">
                <form method="get">
                    <input type="hidden" name="page" value="careers-applications" />
                    
                    <select name="status">
                        <option value=""><?php _e('All Statuses', 'careers-manager'); ?></option>
                        <?php foreach ($statuses as $key => $label): ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($current_status, $key); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select name="job_id">
                        <option value=""><?php _e('All Jobs', 'careers-manager'); ?></option>
                        <?php
                        $jobs = get_posts(array('post_type' => 'career_job', 'posts_per_page' => -1));
                        foreach ($jobs as $job):
                        ?>
                            <option value="<?php echo esc_attr($job->ID); ?>" <?php selected($current_job, $job->ID); ?>><?php echo esc_html($job->post_title); ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <input type="submit" class="button" value="<?php _e('Filter', 'careers-manager'); ?>" />
                </form>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Applicant', 'careers-manager'); ?></th>
                        <th><?php _e('Job', 'careers-manager'); ?></th>
                        <th><?php _e('Status', 'careers-manager'); ?></th>
                        <th><?php _e('Applied', 'careers-manager'); ?></th>
                        <th><?php _e('Actions', 'careers-manager'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($applications)): ?>
                        <?php foreach ($applications as $application): ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($application->display_name); ?></strong><br>
                                    <small><?php echo esc_html($application->user_email); ?></small>
                                </td>
                                <td><?php echo esc_html($application->job_title); ?></td>
                                <td>
                                    <select class="careers-status-select" data-application-id="<?php echo esc_attr($application->id); ?>">
                                        <?php foreach ($statuses as $key => $label): ?>
                                            <option value="<?php echo esc_attr($key); ?>" <?php selected($application->status, $key); ?>><?php echo esc_html($label); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($application->submitted_at))); ?></td>
                                <td>
                                    <a href="#" class="button careers-view-application" data-application-id="<?php echo esc_attr($application->id); ?>"><?php _e('View', 'careers-manager'); ?></a>
                                    <?php if (!empty($application->resume_url)): ?>
                                        <a href="<?php echo esc_url($application->resume_url); ?>" target="_blank" class="button"><?php _e('Resume', 'careers-manager'); ?></a>
                                    <?php endif; ?>
                                    <?php if (!empty($application->cover_letter_url)): ?>
                                        <a href="<?php echo esc_url($application->cover_letter_url); ?>" target="_blank" class="button"><?php _e('Cover Letter', 'careers-manager'); ?></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5"><?php _e('No applications found.', 'careers-manager'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Application Details Modal -->
        <div id="careers-application-modal" class="careers-modal" style="display: none;">
            <div class="careers-modal-content">
                <span class="careers-modal-close">&times;</span>
                <div id="careers-application-details"></div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Analytics page
     */
    public function analytics_page() {
        $stats = CareersApplicationDB::get_stats();
        ?>
        <div class="wrap">
            <h1><?php _e('Analytics', 'careers-manager'); ?></h1>
            
            <div class="careers-analytics-content">
                <div class="careers-chart-section">
                    <h2><?php _e('Application Status Distribution', 'careers-manager'); ?></h2>
                    <div class="careers-status-chart">
                        <?php if (!empty($stats['by_status'])): ?>
                            <?php foreach ($stats['by_status'] as $status => $count): 
                                $statuses = careers_get_application_statuses();
                                $label = isset($statuses[$status]) ? $statuses[$status] : $status;
                                $percentage = ($stats['total'] > 0) ? round(($count / $stats['total']) * 100, 1) : 0;
                            ?>
                                <div class="careers-status-item">
                                    <div class="careers-status-label"><?php echo esc_html($label); ?></div>
                                    <div class="careers-status-bar">
                                        <div class="careers-status-fill careers-status-<?php echo esc_attr($status); ?>" style="width: <?php echo esc_attr($percentage); ?>%"></div>
                                    </div>
                                    <div class="careers-status-count"><?php echo intval($count); ?> (<?php echo esc_html($percentage); ?>%)</div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="careers-chart-section">
                    <h2><?php _e('Applications by Job', 'careers-manager'); ?></h2>
                    <div class="careers-jobs-chart">
                        <?php if (!empty($stats['by_job'])): ?>
                            <?php foreach ($stats['by_job'] as $job): ?>
                                <div class="careers-job-item">
                                    <div class="careers-job-title"><?php echo esc_html($job->post_title); ?></div>
                                    <div class="careers-job-count"><?php echo intval($job->count); ?> <?php _e('applications', 'careers-manager'); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
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
     * Render recent applications
     */
    private function render_recent_applications() {
        $recent_applications = CareersApplicationDB::get_applications(array('limit' => 10));
        
        if (empty($recent_applications)) {
            echo '<p>' . __('No recent applications.', 'careers-manager') . '</p>';
            return;
        }
        
        echo '<table class="wp-list-table widefat">';
        echo '<thead><tr><th>' . __('Applicant', 'careers-manager') . '</th><th>' . __('Job', 'careers-manager') . '</th><th>' . __('Status', 'careers-manager') . '</th><th>' . __('Date', 'careers-manager') . '</th></tr></thead>';
        echo '<tbody>';
        
        foreach ($recent_applications as $application) {
            echo '<tr>';
            echo '<td>' . esc_html($application->display_name) . '</td>';
            echo '<td>' . esc_html($application->job_title) . '</td>';
            echo '<td><span class="careers-status careers-status-' . esc_attr($application->status) . '">' . esc_html($application->status) . '</span></td>';
            echo '<td>' . esc_html(date_i18n(get_option('date_format'), strtotime($application->submitted_at))) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    }
    
    /**
     * Render popular jobs
     */
    private function render_popular_jobs($jobs) {
        if (empty($jobs)) {
            echo '<p>' . __('No job data available.', 'careers-manager') . '</p>';
            return;
        }
        
        echo '<table class="wp-list-table widefat">';
        echo '<thead><tr><th>' . __('Job Title', 'careers-manager') . '</th><th>' . __('Applications', 'careers-manager') . '</th></tr></thead>';
        echo '<tbody>';
        
        foreach ($jobs as $job) {
            echo '<tr>';
            echo '<td>' . esc_html($job->post_title) . '</td>';
            echo '<td>' . intval($job->count) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    }
} 