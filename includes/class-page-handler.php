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
            }
            .careers-dashboard-container * {
                box-sizing: border-box;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            }
            .careers-dashboard-container a {
                text-decoration: none !important;
            }
            .careers-dashboard-container a:hover {
                text-decoration: none !important;
            }
            .careers-dashboard-container button {
                text-decoration: none !important;
            }
            .careers-dashboard-container button:hover {
                text-decoration: none !important;
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