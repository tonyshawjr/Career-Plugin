<?php
/**
 * Careers Settings Page
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CareersSettings {
    
    private $options;
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Add settings menu
     */
    public function add_settings_menu() {
        add_options_page(
            __('Careers Settings', 'careers-manager'),
            __('Careers', 'careers-manager'),
            'manage_options',
            'careers-settings',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting(
            'careers_settings_group',
            'careers_page_settings'
        );
        
        add_settings_section(
            'careers_page_mapping',
            __('Page Assignments', 'careers-manager'),
            array($this, 'page_mapping_section_callback'),
            'careers-settings'
        );
        
        // Dashboard page
        add_settings_field(
            'dashboard_page',
            __('Dashboard Page', 'careers-manager'),
            array($this, 'page_dropdown_callback'),
            'careers-settings',
            'careers_page_mapping',
            array('id' => 'dashboard_page', 'description' => 'Select the page for the main dashboard')
        );
        
        // Manage Jobs page
        add_settings_field(
            'manage_jobs_page',
            __('Manage Jobs Page', 'careers-manager'),
            array($this, 'page_dropdown_callback'),
            'careers-settings',
            'careers_page_mapping',
            array('id' => 'manage_jobs_page', 'description' => 'Select the page for managing all jobs')
        );
        
        // Create Job page
        add_settings_field(
            'create_job_page',
            __('Create Job Page', 'careers-manager'),
            array($this, 'page_dropdown_callback'),
            'careers-settings',
            'careers_page_mapping',
            array('id' => 'create_job_page', 'description' => 'Select the page for creating new jobs')
        );
        
        // Edit Job page
        add_settings_field(
            'edit_job_page',
            __('Edit Job Page', 'careers-manager'),
            array($this, 'page_dropdown_callback'),
            'careers-settings',
            'careers_page_mapping',
            array('id' => 'edit_job_page', 'description' => 'Select the page for editing jobs')
        );
        
        // Locations page
        add_settings_field(
            'locations_page',
            __('Locations Page', 'careers-manager'),
            array($this, 'page_dropdown_callback'),
            'careers-settings',
            'careers_page_mapping',
            array('id' => 'locations_page', 'description' => 'Select the page for managing locations')
        );
        
        // Applications List page
        add_settings_field(
            'applications_page',
            __('Applications Page', 'careers-manager'),
            array($this, 'page_dropdown_callback'),
            'careers-settings',
            'careers_page_mapping',
            array('id' => 'applications_page', 'description' => 'Select the page for viewing all applications')
        );
        
        // Application View page
        add_settings_field(
            'application_view_page',
            __('Application View Page', 'careers-manager'),
            array($this, 'page_dropdown_callback'),
            'careers-settings',
            'careers_page_mapping',
            array('id' => 'application_view_page', 'description' => 'Select the page for viewing individual applications')
        );
        
        // Job Detail page (public-facing)
        add_settings_field(
            'job_detail_page',
            __('Job Detail Page', 'careers-manager'),
            array($this, 'page_dropdown_callback'),
            'careers-settings',
            'careers_page_mapping',
            array('id' => 'job_detail_page', 'description' => 'Select the page for public job postings (where visitors view and apply for jobs)')
        );
        
        // Apply page
        add_settings_field(
            'apply_page',
            __('Apply Page', 'careers-manager'),
            array($this, 'page_dropdown_callback'),
            'careers-settings',
            'careers_page_mapping',
            array('id' => 'apply_page', 'description' => 'Select the page where users submit job applications')
        );
    }
    
    /**
     * Page mapping section callback
     */
    public function page_mapping_section_callback() {
        echo '<p>' . __('Select which pages should display each careers function. Make sure to create these pages first.', 'careers-manager') . '</p>';
    }
    
    /**
     * Page dropdown callback
     */
    public function page_dropdown_callback($args) {
        $this->options = get_option('careers_page_settings');
        $pages = get_pages();
        $selected = isset($this->options[$args['id']]) ? $this->options[$args['id']] : '';
        
        ?>
        <select name="careers_page_settings[<?php echo esc_attr($args['id']); ?>]">
            <option value=""><?php _e('— Select —', 'careers-manager'); ?></option>
            <?php foreach ($pages as $page): ?>
                <option value="<?php echo esc_attr($page->ID); ?>" <?php selected($selected, $page->ID); ?>>
                    <?php echo esc_html($page->post_title); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($args['description'])): ?>
            <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php endif; ?>
        <?php
    }
    
    /**
     * Settings page display
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('careers_settings_group');
                do_settings_sections('careers-settings');
                submit_button();
                ?>
            </form>
            
            <div class="careers-settings-info">
                <h2><?php _e('Setup Instructions', 'careers-manager'); ?></h2>
                <ol>
                    <li><?php _e('Create pages for each function (Dashboard, Manage Jobs, etc.)', 'careers-manager'); ?></li>
                    <li><?php _e('Select each page in the dropdowns above', 'careers-manager'); ?></li>
                    <li><?php _e('The plugin will automatically display the appropriate content on each page', 'careers-manager'); ?></li>
                    <li><?php _e('No shortcodes needed - content is injected automatically', 'careers-manager'); ?></li>
                </ol>
                
                <h3><?php _e('Recommended Page Structure:', 'careers-manager'); ?></h3>
                <ul>
                    <li>Dashboard (Parent Page)</li>
                    <li>— Manage Jobs (Child of Dashboard)</li>
                    <li>— Create Job (Child of Dashboard)</li>
                    <li>— Edit Job (Child of Dashboard)</li>
                    <li>— Locations (Child of Dashboard)</li>
                    <li>— Applications (Child of Dashboard)</li>
                    <li>— View Application (Child of Applications)</li>
                    <li>Open Positions (Standalone - for public job listings)</li>
                    <li>Apply (Standalone - for job application forms)</li>
                </ul>
            </div>
        </div>
        <?php
    }
    
    /**
     * Get a page ID by function name
     */
    public static function get_page_id($function) {
        $options = get_option('careers_page_settings');
        $map = array(
            'dashboard' => 'dashboard_page',
            'manage_jobs' => 'manage_jobs_page',
            'create_job' => 'create_job_page',
            'edit_job' => 'edit_job_page',
            'locations' => 'locations_page',
            'applications' => 'applications_page',
            'application_view' => 'application_view_page',
            'job_detail' => 'job_detail_page',
            'apply' => 'apply_page'
        );
        
        $key = isset($map[$function]) ? $map[$function] : '';
        return isset($options[$key]) ? intval($options[$key]) : 0;
    }
    
    /**
     * Get page URL for a function
     */
    public static function get_page_url($function, $args = array()) {
        $page_id = self::get_page_id($function);
        if (!$page_id) {
            return home_url();
        }
        
        $url = get_permalink($page_id);
        if (!empty($args)) {
            $url = add_query_arg($args, $url);
        }
        
        return $url;
    }
    
    /**
     * Get dashboard URL with proper page mapping
     */
    public static function get_dashboard_url($path = '', $args = array()) {
        // Parse the path to determine which page to use
        $path_parts = explode('/', trim($path, '/'));
        
        if (empty($path_parts[0])) {
            return self::get_page_url('dashboard', $args);
        }
        
        // Map old paths to new functions
        switch ($path_parts[0]) {
            case 'jobs':
                if (isset($path_parts[1])) {
                    switch ($path_parts[1]) {
                        case 'create':
                            return self::get_page_url('create_job', $args);
                        case 'edit':
                            if (isset($path_parts[2])) {
                                $args['id'] = $path_parts[2];
                            }
                            return self::get_page_url('edit_job', $args);
                        case 'applications':
                            if (isset($path_parts[2])) {
                                $args['job_id'] = $path_parts[2];
                            }
                            return self::get_page_url('applications', $args);
                        default:
                            return self::get_page_url('manage_jobs', $args);
                    }
                } else {
                    return self::get_page_url('manage_jobs', $args);
                }
                break;
                
            case 'positions':
                // Positions is an alias for jobs
                if (isset($path_parts[1])) {
                    switch ($path_parts[1]) {
                        case 'create':
                            return self::get_page_url('create_job', $args);
                        case 'edit':
                            if (isset($path_parts[2])) {
                                $args['id'] = $path_parts[2];
                            }
                            return self::get_page_url('edit_job', $args);
                        case 'applications':
                            if (isset($path_parts[2])) {
                                $args['job_id'] = $path_parts[2];
                            }
                            return self::get_page_url('applications', $args);
                        default:
                            return self::get_page_url('manage_jobs', $args);
                    }
                } else {
                    return self::get_page_url('manage_jobs', $args);
                }
                break;
                
            case 'locations':
                return self::get_page_url('locations', $args);
                
            case 'applications':
                if (isset($path_parts[1]) && isset($path_parts[2])) {
                    $args['id'] = $path_parts[2];
                    return self::get_page_url('application_view', $args);
                }
                return self::get_page_url('applications', $args);
                
            default:
                return self::get_page_url('dashboard', $args);
        }
    }
}