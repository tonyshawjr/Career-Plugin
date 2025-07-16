<?php
/**
 * Careers Page Handler - Injects content into designated pages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CareersPageHandler {
    
    public function __construct() {
        // Hook into the_content to inject our functionality
        add_filter('the_content', array($this, 'inject_page_content'), 20);
        
        // Enqueue assets only on our pages
        add_action('wp_enqueue_scripts', array($this, 'enqueue_page_assets'));
    }
    
    /**
     * Inject content into designated pages
     */
    public function inject_page_content($content) {
        // Only process on singular pages
        if (!is_singular('page')) {
            return $content;
        }
        
        $page_id = get_the_ID();
        
        // Dashboard page
        if ($page_id == CareersSettings::get_page_id('dashboard')) {
            return $this->get_dashboard_content();
        }
        
        // Manage Jobs page
        if ($page_id == CareersSettings::get_page_id('manage_jobs')) {
            return $this->get_manage_jobs_content();
        }
        
        // Create Job page
        if ($page_id == CareersSettings::get_page_id('create_job')) {
            return $this->get_create_job_content();
        }
        
        // Edit Job page
        if ($page_id == CareersSettings::get_page_id('edit_job')) {
            return $this->get_edit_job_content();
        }
        
        // Locations page
        if ($page_id == CareersSettings::get_page_id('locations')) {
            return $this->get_locations_content();
        }
        
        // Applications page
        if ($page_id == CareersSettings::get_page_id('applications')) {
            return $this->get_applications_content();
        }
        
        // Application View page
        if ($page_id == CareersSettings::get_page_id('application_view')) {
            return $this->get_application_view_content();
        }
        
        return $content;
    }
    
    /**
     * Enqueue assets only on our pages
     */
    public function enqueue_page_assets() {
        if (!is_singular('page')) {
            return;
        }
        
        $page_id = get_the_ID();
        $careers_pages = array(
            CareersSettings::get_page_id('dashboard'),
            CareersSettings::get_page_id('manage_jobs'),
            CareersSettings::get_page_id('create_job'),
            CareersSettings::get_page_id('edit_job'),
            CareersSettings::get_page_id('locations'),
            CareersSettings::get_page_id('applications'),
            CareersSettings::get_page_id('application_view')
        );
        
        if (in_array($page_id, $careers_pages)) {
            wp_enqueue_style(
                'careers-frontend',
                CAREERS_PLUGIN_URL . 'assets/css/frontend.css',
                array(),
                CAREERS_PLUGIN_VERSION
            );
            
            // Add inline CSS for dashboard styling
            $dashboard_css = '
            .careers-dashboard-container {
                max-width: 1280px;
                margin: 0 auto;
                padding: 2rem 0;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
                color: #333;
                font-size: 16px !important;
                line-height: 1.5 !important;
            }
            .careers-dashboard-container * {
                box-sizing: border-box;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            }
            .careers-dashboard-container h1, 
            .careers-dashboard-container h2, 
            .careers-dashboard-container h3, 
            .careers-dashboard-container h4, 
            .careers-dashboard-container h5, 
            .careers-dashboard-container h6 {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            }
            .careers-dashboard-container p {
                font-size: 16px !important;
                line-height: 1.5 !important;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            }
            .careers-dashboard-container label {
                font-size: 14px !important;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            }
            .careers-dashboard-container input,
            .careers-dashboard-container select,
            .careers-dashboard-container textarea {
                font-size: 16px !important;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            }
            /* High specificity overrides for underlines and fonts */
            .careers-dashboard-container a,
            .careers-dashboard-container a:link,
            .careers-dashboard-container a:visited,
            .careers-dashboard-container a:hover,
            .careers-dashboard-container a:active,
            .careers-dashboard-container a:focus,
            .careers-dashboard-container button,
            .careers-dashboard-container button:link,
            .careers-dashboard-container button:visited,
            .careers-dashboard-container button:hover,
            .careers-dashboard-container button:active,
            .careers-dashboard-container button:focus,
            .careers-dashboard-container .action-btn,
            .careers-dashboard-container .action-btn:link,
            .careers-dashboard-container .action-btn:visited,
            .careers-dashboard-container .action-btn:hover,
            .careers-dashboard-container .action-btn:active,
            .careers-dashboard-container .action-btn:focus,
            .careers-dashboard-container .dashboard-action-btn,
            .careers-dashboard-container .dashboard-action-btn:link,
            .careers-dashboard-container .dashboard-action-btn:visited,
            .careers-dashboard-container .dashboard-action-btn:hover,
            .careers-dashboard-container .dashboard-action-btn:active,
            .careers-dashboard-container .dashboard-action-btn:focus,
            .careers-dashboard-container .create-button,
            .careers-dashboard-container .create-button:link,
            .careers-dashboard-container .create-button:visited,
            .careers-dashboard-container .create-button:hover,
            .careers-dashboard-container .create-button:active,
            .careers-dashboard-container .create-button:focus {
                text-decoration: none !important;
                border-bottom: none !important;
                box-shadow: none !important;
                outline: none !important;
                text-underline-offset: unset !important;
                text-decoration-line: none !important;
                text-decoration-color: transparent !important;
                text-decoration-style: none !important;
                text-decoration-thickness: 0 !important;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            }
            .careers-dashboard-container .dashboard-header {
                margin-bottom: 3rem;
                padding-bottom: 2rem;
                border-bottom: 1px solid #eee;
            }
            .careers-dashboard-container .dashboard-title {
                font-size: 2.5rem !important;
                font-weight: 500 !important;
                margin: 0 0 0.5rem 0 !important;
                line-height: 1.2 !important;
                color: #111 !important;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            }
            .careers-dashboard-container .dashboard-subtitle {
                color: #666 !important;
                margin: 0 !important;
                font-size: 1rem !important;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            }
            .careers-dashboard-container .dashboard-action-btn {
                background: #000 !important;
                color: white !important;
                padding: 0.75rem 1.5rem !important;
                border: none !important;
                border-radius: 4px !important;
                font-size: 1rem !important;
                font-weight: 500 !important;
                text-decoration: none !important;
                display: inline-block !important;
                transition: background 0.2s ease !important;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            }
            .careers-dashboard-container .dashboard-action-btn:hover {
                background: #333 !important;
                color: white !important;
                text-decoration: none !important;
            }
            .careers-dashboard-container .dashboard-action-btn.secondary {
                background: #f5f5f5 !important;
                color: #333 !important;
                border: 1px solid #ddd !important;
            }
            .careers-dashboard-container .dashboard-action-btn.secondary:hover {
                background: #e8e8e8 !important;
                color: #333 !important;
            }
            .careers-dashboard-container .dashboard-actions {
                display: flex;
                gap: 1rem;
                margin-bottom: 2rem;
                flex-wrap: wrap;
            }
            .careers-dashboard-container .create-button {
                background: #000 !important;
                color: white !important;
                padding: 0.75rem 1.5rem !important;
                border: none !important;
                border-radius: 4px !important;
                font-size: 1rem !important;
                font-weight: 500 !important;
                text-decoration: none !important;
                display: inline-block !important;
                transition: background 0.2s ease !important;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            }
            .careers-dashboard-container .create-button:hover {
                background: #333 !important;
                color: white !important;
                text-decoration: none !important;
            }
            .careers-dashboard-container .create-button.secondary {
                background: #f5f5f5 !important;
                color: #333 !important;
                border: 1px solid #ddd !important;
            }
            .careers-dashboard-container .create-button.secondary:hover {
                background: #e8e8e8 !important;
                color: #333 !important;
                text-decoration: none !important;
            }
            .careers-dashboard-container .form-group {
                margin-bottom: 1.5rem;
            }
            .careers-dashboard-container .form-label {
                display: block;
                font-weight: 500;
                margin-bottom: 0.5rem;
                color: #111;
            }
            .careers-dashboard-container .form-input {
                width: 100%;
                padding: 0.75rem;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 1rem;
                font-family: inherit;
            }
            .careers-dashboard-container .form-input:focus {
                outline: none;
                border-color: #000;
                box-shadow: 0 0 0 2px rgba(0,0,0,0.1);
            }
            .careers-dashboard-container .form-select {
                width: 100%;
                padding: 0.75rem;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 1rem;
                font-family: inherit;
                background: white;
            }
            .careers-dashboard-container .form-select:focus {
                outline: none;
                border-color: #000;
                box-shadow: 0 0 0 2px rgba(0,0,0,0.1);
            }
            .careers-dashboard-container .form-row {
                margin-bottom: 1.5rem;
            }
            .careers-dashboard-container .form-row label {
                display: block;
                font-weight: 500;
                margin-bottom: 0.5rem;
                color: #111;
                font-size: 14px !important;
            }
            .careers-dashboard-container .form-row input,
            .careers-dashboard-container .form-row select,
            .careers-dashboard-container .form-row textarea {
                width: 100%;
                padding: 0.75rem;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 16px !important;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
                background: white;
            }
            .careers-dashboard-container .form-row textarea {
                min-height: 120px;
                resize: vertical;
            }
            .careers-dashboard-container .form-actions {
                display: flex;
                gap: 1rem;
                margin-top: 2rem;
            }
            
            /* Table styles */
            .careers-dashboard-container table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 1rem;
            }
            .careers-dashboard-container table th {
                text-align: left;
                font-weight: 500;
                color: #6b7280;
                font-size: 14px !important;
                padding: 0.75rem;
                border-bottom: 2px solid #e5e7eb;
                background: #f9fafb;
            }
            .careers-dashboard-container table td {
                padding: 0.75rem;
                border-bottom: 1px solid #e5e7eb;
                font-size: 14px !important;
                color: #374151;
            }
            .careers-dashboard-container table tr:hover {
                background: #f9fafb;
            }
            
            /* Status badges */
            .careers-dashboard-container .status-badge {
                display: inline-block;
                padding: 0.25rem 0.75rem;
                font-size: 12px !important;
                font-weight: 500;
                border-radius: 12px;
                text-transform: capitalize;
            }
            .careers-dashboard-container .status-badge.published {
                background: #d1fae5;
                color: #065f46;
            }
            .careers-dashboard-container .status-badge.draft {
                background: #fef3c7;
                color: #92400e;
            }
            
            /* Empty state */
            .careers-dashboard-container .empty-state {
                text-align: center;
                padding: 3rem;
                color: #6b7280;
            }
            .careers-dashboard-container .empty-state h3 {
                font-size: 1.25rem !important;
                font-weight: 500;
                color: #111827;
                margin: 0 0 1rem 0;
            }
            .careers-dashboard-container .empty-state p {
                font-size: 16px !important;
                margin: 0 0 1.5rem 0;
            }
            /* Management specific styles */
            .careers-dashboard-container .management-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 2rem;
                padding-bottom: 2rem;
                border-bottom: 1px solid #eee;
            }
            .careers-dashboard-container .management-header h1 {
                font-size: 2.5rem !important;
                font-weight: 500 !important;
                margin: 0 !important;
                line-height: 1.2 !important;
                color: #111 !important;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            }
            .careers-dashboard-container .header-actions {
                display: flex;
                gap: 1rem;
            }
            .careers-dashboard-container .filters-section {
                background: #f8f9fa;
                padding: 1.5rem;
                border-radius: 8px;
                margin-bottom: 2rem;
            }
            .careers-dashboard-container .filters-grid {
                display: grid;
                grid-template-columns: 2fr 1fr 1fr 1fr auto;
                gap: 1rem;
                align-items: end;
            }
            .careers-dashboard-container .filter-group {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
            }
            .careers-dashboard-container .filter-group label {
                font-weight: 500;
                font-size: 0.875rem;
                color: #374151;
            }
            .careers-dashboard-container .filter-button {
                background: #000 !important;
                color: white !important;
                padding: 0.75rem 1.5rem !important;
                border: none !important;
                border-radius: 4px !important;
                font-size: 0.875rem !important;
                font-weight: 500 !important;
                text-decoration: none !important;
                cursor: pointer !important;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            }
            .careers-dashboard-container .filter-button:hover {
                background: #333 !important;
                color: white !important;
                text-decoration: none !important;
            }
            .careers-dashboard-container .positions-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
                gap: 1.5rem;
                margin-bottom: 2rem;
            }
            .careers-dashboard-container .position-card {
                background: white;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                padding: 1.5rem;
                transition: box-shadow 0.2s ease;
            }
            .careers-dashboard-container .position-card:hover {
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            }
            .careers-dashboard-container .position-card h3 {
                font-size: 1.25rem !important;
                font-weight: 600;
                margin: 0 0 0.5rem 0;
                color: #111827;
            }
            .careers-dashboard-container .position-card p {
                font-size: 14px !important;
                color: #6b7280;
                margin: 0.25rem 0;
            }
            .careers-dashboard-container .position-header {
                display: flex;
                justify-content: space-between;
                align-items: start;
                margin-bottom: 1rem;
            }
            .careers-dashboard-container .position-meta {
                margin-bottom: 1rem;
            }
            .careers-dashboard-container .position-meta .meta-item {
                margin-bottom: 0.5rem;
                font-size: 14px !important;
                color: #6b7280;
            }
            .careers-dashboard-container .position-meta .meta-item strong {
                color: #374151;
            }
            .careers-dashboard-container .position-actions {
                display: flex;
                gap: 0.5rem;
                flex-wrap: wrap;
            }
            
            /* Metrics grid */
            .careers-dashboard-container .metrics-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 1.5rem;
                margin-bottom: 2rem;
            }
            .careers-dashboard-container .metric-card {
                background: white;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                padding: 1.5rem;
                text-align: center;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }
            .careers-dashboard-container .metric-card h3 {
                font-size: 14px !important;
                font-weight: 500;
                color: #6b7280;
                margin: 0 0 0.5rem 0;
                text-transform: uppercase;
                letter-spacing: 0.025em;
            }
            .careers-dashboard-container .metric-card .metric-value {
                font-size: 2.5rem !important;
                font-weight: 600;
                color: #111827;
                margin: 0;
                line-height: 1;
            }
            .careers-dashboard-container .metric-card .metric-change {
                font-size: 14px !important;
                margin-top: 0.5rem;
                color: #6b7280;
            }
            
            /* Location management */
            .careers-dashboard-container .location-form {
                background: white;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                padding: 1.5rem;
                margin-bottom: 2rem;
            }
            .careers-dashboard-container .locations-list {
                background: white;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                padding: 1.5rem;
            }
            .careers-dashboard-container .locations-list h3 {
                font-size: 1.25rem !important;
                font-weight: 600;
                margin: 0 0 1rem 0;
                color: #111827;
            }
            .careers-dashboard-container .location-items {
                list-style: none;
                padding: 0;
                margin: 0;
            }
            .careers-dashboard-container .location-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.75rem;
                border-bottom: 1px solid #e5e7eb;
            }
            .careers-dashboard-container .location-item:last-child {
                border-bottom: none;
            }
            .careers-dashboard-container .location-name {
                font-size: 16px !important;
                color: #374151;
            }
            .careers-dashboard-container .action-btn {
                padding: 0.5rem 1rem !important;
                border: 1px solid #ddd !important;
                border-radius: 4px !important;
                font-size: 0.875rem !important;
                font-weight: 500 !important;
                text-decoration: none !important;
                display: inline-block !important;
                transition: all 0.2s ease !important;
                background: #fff !important;
                color: #333 !important;
                cursor: pointer !important;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            }
            .careers-dashboard-container .action-btn:hover {
                background: #f5f5f5 !important;
                color: #333 !important;
                text-decoration: none !important;
            }
            .careers-dashboard-container .action-btn.primary {
                background: #000 !important;
                color: white !important;
                border-color: #000 !important;
            }
            .careers-dashboard-container .action-btn.primary:hover {
                background: #333 !important;
                color: white !important;
            }
            .careers-dashboard-container .action-btn.danger {
                background: #dc2626 !important;
                color: white !important;
                border-color: #dc2626 !important;
            }
            .careers-dashboard-container .action-btn.danger:hover {
                background: #b91c1c !important;
                color: white !important;
            }
            @media (max-width: 768px) {
                .careers-dashboard-container {
                    padding: 1rem;
                }
                .careers-dashboard-container .dashboard-title {
                    font-size: 2rem !important;
                }
                .careers-dashboard-container .management-header h1 {
                    font-size: 2rem !important;
                }
                .careers-dashboard-container .filters-grid {
                    grid-template-columns: 1fr;
                    gap: 1rem;
                }
                .careers-dashboard-container .positions-grid {
                    grid-template-columns: 1fr;
                }
            }
            ';
            
            wp_add_inline_style('careers-frontend', $dashboard_css);
            
            wp_enqueue_script(
                'careers-frontend',
                CAREERS_PLUGIN_URL . 'assets/js/frontend.js',
                array('jquery'),
                CAREERS_PLUGIN_VERSION,
                true
            );
            
            wp_localize_script('careers-frontend', 'careers_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('careers_nonce'),
            ));
        }
    }
    
    /**
     * Get dashboard content
     */
    private function get_dashboard_content() {
        // Check permissions
        if (!is_user_logged_in() || (!current_user_can('manage_options') && !current_user_can('career_admin'))) {
            return '<div class="careers-dashboard-error">You must be logged in and have permission to access the dashboard.</div>';
        }
        
        // Get the dashboard instance and call the render method
        if (class_exists('CareersDashboard')) {
            $dashboard = new CareersDashboard();
            ob_start();
            $dashboard->render_main_dashboard();
            return ob_get_clean();
        }
        
        return '<div class="careers-dashboard-error">Dashboard component not found.</div>';
    }
    
    /**
     * Get manage jobs content
     */
    private function get_manage_jobs_content() {
        // Check permissions
        if (!is_user_logged_in() || (!current_user_can('manage_options') && !current_user_can('career_admin'))) {
            return '<div class="careers-dashboard-error">You must be logged in and have permission to manage jobs.</div>';
        }
        
        if (class_exists('CareersDashboard')) {
            $dashboard = new CareersDashboard();
            ob_start();
            $dashboard->render_position_management();
            return ob_get_clean();
        }
        
        return '<div class="careers-dashboard-error">Jobs management component not found.</div>';
    }
    
    /**
     * Get create job content
     */
    private function get_create_job_content() {
        // Check permissions
        if (!is_user_logged_in() || (!current_user_can('manage_options') && !current_user_can('career_admin'))) {
            return '<div class="careers-dashboard-error">You must be logged in and have permission to create jobs.</div>';
        }
        
        if (class_exists('CareersDashboard')) {
            $dashboard = new CareersDashboard();
            ob_start();
            $dashboard->render_position_creation_form();
            return ob_get_clean();
        }
        
        return '<div class="careers-dashboard-error">Job creation component not found.</div>';
    }
    
    /**
     * Get edit job content
     */
    private function get_edit_job_content() {
        // Check permissions
        if (!is_user_logged_in() || (!current_user_can('manage_options') && !current_user_can('career_admin'))) {
            return '<div class="careers-dashboard-error">You must be logged in and have permission to edit jobs.</div>';
        }
        
        $job_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$job_id) {
            return '<div class="careers-dashboard-error">No job ID provided.</div>';
        }
        
        if (class_exists('CareersDashboard')) {
            $dashboard = new CareersDashboard();
            ob_start();
            $dashboard->render_position_edit_form($job_id);
            return ob_get_clean();
        }
        
        return '<div class="careers-dashboard-error">Job edit component not found.</div>';
    }
    
    /**
     * Get locations content
     */
    private function get_locations_content() {
        // Check permissions
        if (!is_user_logged_in() || (!current_user_can('manage_options') && !current_user_can('career_admin'))) {
            return '<div class="careers-dashboard-error">You must be logged in and have permission to manage locations.</div>';
        }
        
        if (class_exists('CareersDashboard')) {
            $dashboard = new CareersDashboard();
            ob_start();
            $dashboard->render_location_management();
            return ob_get_clean();
        }
        
        return '<div class="careers-dashboard-error">Locations component not found.</div>';
    }
    
    /**
     * Get applications content
     */
    private function get_applications_content() {
        // Check permissions
        if (!is_user_logged_in() || (!current_user_can('manage_options') && !current_user_can('career_admin'))) {
            return '<div class="careers-dashboard-error">You must be logged in and have permission to view applications.</div>';
        }
        
        $job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;
        
        if (class_exists('CareersDashboard')) {
            $dashboard = new CareersDashboard();
            ob_start();
            // This method will need to be created or adapted
            ?>
            <div class="careers-dashboard-container">
                <div class="dashboard-header">
                    <h1 class="dashboard-title">Applications</h1>
                    <p class="dashboard-subtitle">Manage job applications</p>
                </div>
                
                <div class="applications-content">
                    <?php if ($job_id): ?>
                        <p>Viewing applications for job ID: <?php echo esc_html($job_id); ?></p>
                    <?php else: ?>
                        <p>Viewing all applications</p>
                    <?php endif; ?>
                    <!-- Applications list will go here -->
                    <div class="coming-soon-notice">
                        <p>Applications management functionality coming soon.</p>
                    </div>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }
        
        return '<div class="careers-dashboard-error">Applications component not found.</div>';
    }
    
    /**
     * Get application view content
     */
    private function get_application_view_content() {
        // Check permissions
        if (!is_user_logged_in() || (!current_user_can('manage_options') && !current_user_can('career_admin'))) {
            return '<div class="careers-dashboard-error">You must be logged in and have permission to view applications.</div>';
        }
        
        $application_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$application_id) {
            return '<div class="careers-dashboard-error">No application ID provided.</div>';
        }
        
        ob_start();
        ?>
        <div class="careers-dashboard-container">
            <div class="dashboard-header">
                <h1 class="dashboard-title">View Application</h1>
                <p class="dashboard-subtitle">Application details</p>
            </div>
            
            <div class="application-view-content">
                <p>Viewing application ID: <?php echo esc_html($application_id); ?></p>
                <!-- Application details will go here -->
                <div class="coming-soon-notice">
                    <p>Application view functionality coming soon.</p>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}