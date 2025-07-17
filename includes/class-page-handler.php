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
        
        // Profile page
        if ($page_id == CareersSettings::get_page_id('profile')) {
            return $this->get_profile_content();
        }
        
        // Job Detail page (public-facing)
        if ($page_id == CareersSettings::get_page_id('job_detail')) {
            return $this->get_job_detail_content();
        }
        
        // Apply page
        if ($page_id == CareersSettings::get_page_id('apply')) {
            return $this->get_apply_content();
        }
        
        // Open Positions page
        if ($page_id == CareersSettings::get_page_id('open_positions')) {
            return $this->get_open_positions_content();
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
            CareersSettings::get_page_id('application_view'),
            CareersSettings::get_page_id('profile'),
            CareersSettings::get_page_id('job_detail'),
            CareersSettings::get_page_id('apply'),
            CareersSettings::get_page_id('open_positions')
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
            /* Management specific styles - flat design */
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
                border-radius: 4px;
                margin-bottom: 2rem;
                border: 1px solid #eee;
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
            
            /* Flat position card design matching main dashboard */
            .careers-dashboard-container .positions-grid {
                display: grid;
                gap: 1rem;
            }
            .careers-dashboard-container .position-card {
                background: white !important;
                border: 1px solid #eee !important;
                border-radius: 4px !important;
                padding: 1.5rem !important;
                display: grid !important;
                grid-template-columns: auto 1fr auto !important;
                gap: 1rem !important;
                align-items: center !important;
            }
            .careers-dashboard-container .position-info {
                display: grid !important;
                grid-template-columns: 2fr 1fr 1fr 1fr 1fr !important;
                gap: 1.5rem !important;
                align-items: start !important;
            }
            .careers-dashboard-container .position-info-item {
                display: flex !important;
                flex-direction: column !important;
                gap: 0.25rem !important;
            }
            .careers-dashboard-container .position-info-label {
                font-size: 0.75rem !important;
                font-weight: 500 !important;
                text-transform: uppercase !important;
                letter-spacing: 0.05em !important;
                color: #666 !important;
                margin-bottom: 0.25rem !important;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            }
            .careers-dashboard-container .position-name {
                font-size: 1.125rem !important;
                font-weight: 600 !important;
                color: #111 !important;
                margin: 0 !important;
                line-height: 1.3 !important;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            }
            .careers-dashboard-container .position-location,
            .careers-dashboard-container .position-date,
            .careers-dashboard-container .position-applications {
                font-size: 0.875rem !important;
                color: #374151 !important;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            }
            .careers-dashboard-container .position-status {
                display: inline-block !important;
                padding: 0.25rem 0.5rem !important;
                border-radius: 12px !important;
                font-size: 0.75rem !important;
                font-weight: 500 !important;
                text-transform: uppercase !important;
                letter-spacing: 0.05em !important;
                background: #f3f4f6 !important;
                color: #374151 !important;
            }
            .careers-dashboard-container .position-status.published {
                background: #d1fae5 !important;
                color: #065f46 !important;
            }
            .careers-dashboard-container .position-status.draft {
                background: #fef3c7 !important;
                color: #92400e !important;
            }
            .careers-dashboard-container .position-actions {
                display: flex !important;
                gap: 0.5rem !important;
                align-items: flex-start !important;
            }
            
            /* Mobile responsive for manage jobs */
            @media (max-width: 1024px) {
                .careers-dashboard-container .filters-grid {
                    grid-template-columns: 1fr !important;
                    gap: 1rem !important;
                }
                .careers-dashboard-container .position-card {
                    grid-template-columns: 1fr !important;
                    gap: 1rem !important;
                }
                .careers-dashboard-container .position-info {
                    display: block !important;
                }
                .careers-dashboard-container .position-info-item {
                    margin-bottom: 1rem !important;
                    padding-bottom: 0.75rem !important;
                    border-bottom: 1px solid #f0f0f0 !important;
                }
                .careers-dashboard-container .position-info-item:last-child {
                    margin-bottom: 0 !important;
                }
                .careers-dashboard-container .position-name {
                    font-size: 1.25rem !important;
                    line-height: 1.3 !important;
                }
                .careers-dashboard-container .position-status {
                    font-size: 0.875rem !important;
                    padding: 0.375rem 0.75rem !important;
                }
                .careers-dashboard-container .position-actions {
                    justify-content: flex-start !important;
                    flex-wrap: wrap !important;
                }
            }
            
            /* Dashboard tabs removed - applications now on separate page */
            
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
            .careers-dashboard-container .metric-card .metric-number {
                font-size: 2.5rem !important;
                font-weight: 600;
                color: #111827;
                margin: 0;
                line-height: 1;
            }
            .careers-dashboard-container .metric-card .metric-label {
                font-size: 14px !important;
                font-weight: 500;
                color: #6b7280;
                margin-top: 0.5rem;
                text-transform: uppercase;
                letter-spacing: 0.025em;
            }
            
            /* Jobs section */
            .careers-dashboard-container .jobs-section {
                margin-top: 2rem;
            }
            .careers-dashboard-container .section-title {
                font-size: 1.5rem !important;
                font-weight: 600;
                margin: 2rem 0 1.5rem 0;
                color: #111;
            }
            .careers-dashboard-container .jobs-grid {
                display: grid;
                gap: 1rem;
            }
            .careers-dashboard-container .job-card {
                background: white;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                padding: 1.5rem;
                display: grid;
                grid-template-columns: 1fr auto;
                gap: 1.5rem;
                align-items: center;
                transition: box-shadow 0.2s ease;
            }
            .careers-dashboard-container .job-card:hover {
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            }
            .careers-dashboard-container .job-info {
                display: grid;
                grid-template-columns: repeat(5, 1fr);
                gap: 1.5rem;
                align-items: start;
            }
            .careers-dashboard-container .job-info-item {
                display: flex;
                flex-direction: column;
                gap: 0.25rem;
            }
            .careers-dashboard-container .job-info-label {
                font-size: 12px !important;
                font-weight: 500;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: #6b7280;
                margin-bottom: 0.25rem;
            }
            .careers-dashboard-container .job-title {
                font-size: 1.125rem !important;
                font-weight: 600;
                color: #111;
                margin: 0;
                line-height: 1.3;
            }
            .careers-dashboard-container .job-location,
            .careers-dashboard-container .posted-date,
            .careers-dashboard-container .app-count {
                font-size: 0.875rem !important;
                color: #374151;
            }
            .careers-dashboard-container .employment-type {
                display: inline-block;
                padding: 0.25rem 0.5rem;
                border-radius: 12px;
                font-size: 0.75rem !important;
                font-weight: 500;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                background: #f3f4f6;
                color: #374151;
            }
            .careers-dashboard-container .employment-type.full-time {
                background: #dbeafe;
                color: #1e40af;
            }
            .careers-dashboard-container .employment-type.part-time {
                background: #fef3c7;
                color: #92400e;
            }
            .careers-dashboard-container .employment-type.contract {
                background: #e0e7ff;
                color: #4338ca;
            }
            .careers-dashboard-container .job-actions {
                display: flex;
                gap: 0.5rem;
                align-items: center;
            }
            
            /* Status cards for applicants */
            .careers-dashboard-container .applicant-status-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1.5rem;
                margin-bottom: 2rem;
            }
            .careers-dashboard-container .status-card {
                background: white;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                padding: 1.5rem;
                text-align: center;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }
            .careers-dashboard-container .status-card .status-number {
                font-size: 2.5rem !important;
                font-weight: 600;
                color: #111827;
                margin: 0;
                line-height: 1;
            }
            .careers-dashboard-container .status-card .status-label {
                font-size: 14px !important;
                font-weight: 500;
                color: #6b7280;
                margin-top: 0.5rem;
                text-transform: uppercase;
                letter-spacing: 0.025em;
            }
            
            /* Location management - flat design */
            .careers-dashboard-container .location-form {
                background: white;
                border: 1px solid #eee;
                border-radius: 4px;
                padding: 1.5rem;
                margin-bottom: 2rem;
            }
            .careers-dashboard-container .locations-list {
                background: white;
                border: 1px solid #eee;
                border-radius: 4px;
                padding: 1.5rem;
            }
            .careers-dashboard-container .locations-list h3 {
                font-size: 1.25rem !important;
                font-weight: 600;
                margin: 0 0 1rem 0;
                color: #111;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
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
                padding: 1rem;
                border-bottom: 1px solid #f0f0f0;
                background: #fff;
            }
            .careers-dashboard-container .location-item:last-child {
                border-bottom: none;
            }
            .careers-dashboard-container .location-item:hover {
                background: #f9fafb;
            }
            .careers-dashboard-container .location-name {
                font-size: 1rem !important;
                color: #374151;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
                font-weight: 500;
            }
            
            /* Mobile location layout */
            @media (max-width: 768px) {
                .careers-dashboard-container .location-item {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 1rem;
                }
                .careers-dashboard-container .location-item .action-btn {
                    align-self: flex-end;
                }
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
                .careers-dashboard-container .job-info {
                    grid-template-columns: 1fr;
                    gap: 1rem;
                }
                .careers-dashboard-container .job-card {
                    grid-template-columns: 1fr;
                }
                .careers-dashboard-container .job-actions {
                    justify-content: flex-start;
                    margin-top: 1rem;
                }
                /* Dashboard tabs removed - applications now on separate page */
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
        
        if (class_exists('CareersDashboard')) {
            $dashboard = new CareersDashboard();
            ob_start();
            $dashboard->render_applications_management();
            return ob_get_clean();
        }
        
        return '<div class="careers-dashboard-error">Applications component not found.</div>';
    }
    
    /**
     * Get job detail content
     */
    private function get_job_detail_content() {
        $job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;
        if (!$job_id) {
            return '<div class="careers-dashboard-error">No job ID provided.</div>';
        }
        
        // Get job details from database
        $job = CareersPositionsDB::get_position($job_id);
        if (!$job || $job->status !== 'published') {
            return '<div class="careers-dashboard-error">Job not found or not published.</div>';
        }
        
        // Use the existing shortcode system for job details
        if (class_exists('CareersShortcodes')) {
            $shortcodes = new CareersShortcodes();
            ob_start();
            echo $shortcodes->careers_position_detail_shortcode(array('id' => $job_id));
            return ob_get_clean();
        }
        
        return '<div class="careers-dashboard-error">Job detail component not found.</div>';
    }
    
    /**
     * Get open positions content
     */
    private function get_open_positions_content() {
        // Use the existing careers list shortcode
        if (class_exists('CareersShortcodes')) {
            $shortcodes = new CareersShortcodes();
            ob_start();
            echo $shortcodes->careers_list_shortcode(array());
            return ob_get_clean();
        }
        
        return '<div class="careers-dashboard-error">Open positions component not found.</div>';
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
        
        // Get application data
        $application = CareersApplicationDB::get_application($application_id);
        if (!$application) {
            return '<div class="careers-dashboard-error">Application not found.</div>';
        }
        
        // Get user data
        $user = get_user_by('id', $application->user_id);
        
        // Get position data if applicable
        $position = null;
        if (!empty($application->job_id) && $application->job_id > 0) {
            $position = CareersPositionsDB::get_position($application->job_id);
        }
        
        // Parse metadata
        $meta = !empty($application->meta) ? json_decode($application->meta, true) : array();
        
        // Get applicant's name and email from metadata
        $applicant_name = 'Unknown Applicant';
        if (!empty($meta['first_name']) || !empty($meta['last_name'])) {
            $first_name = !empty($meta['first_name']) ? $meta['first_name'] : '';
            $last_name = !empty($meta['last_name']) ? $meta['last_name'] : '';
            $applicant_name = trim($first_name . ' ' . $last_name);
        } elseif ($user) {
            $applicant_name = $user->display_name;
        }
        
        $applicant_email = !empty($meta['email']) ? $meta['email'] : ($user ? $user->user_email : 'No email');
        
        // Get notes
        global $wpdb;
        $notes_table = $wpdb->prefix . 'careers_application_notes';
        $notes = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $notes_table WHERE application_id = %d ORDER BY created_at DESC",
            $application_id
        ));
        
        // Status pipeline
        $status_pipeline = array(
            'new' => array('label' => 'New', 'color' => '#3b82f6'),
            'under_review' => array('label' => 'Under Review', 'color' => '#f59e0b'),
            'contacted' => array('label' => 'Contacted', 'color' => '#8b5cf6'),
            'interview' => array('label' => 'Interview', 'color' => '#06b6d4'),
            'hired' => array('label' => 'Hired', 'color' => '#10b981'),
            'rejected' => array('label' => 'Rejected', 'color' => '#ef4444')
        );
        
        ob_start();
        ?>
        <style>
        .careers-dashboard-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 2rem 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .dashboard-title {
            font-size: 2.5rem !important;
            font-weight: 600 !important;
            margin: 0 !important;
            color: #111 !important;
        }
        
        .dashboard-subtitle {
            color: #6b7280 !important;
            margin: 0.5rem 0 0 0 !important;
            font-size: 1rem !important;
        }
        
        .header-actions {
            display: flex;
            gap: 1rem;
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: #f3f4f6;
            color: #374151;
            text-decoration: none !important;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .back-button:hover {
            background: #e5e7eb;
            color: #111;
        }
        
        .application-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }
        
        .main-column {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }
        
        .sidebar-column {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }
        
        .info-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .card-title {
            font-size: 1.25rem !important;
            font-weight: 600 !important;
            color: #111 !important;
            margin: 0 !important;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .info-label {
            font-size: 0.875rem !important;
            color: #6b7280 !important;
            font-weight: 500 !important;
        }
        
        .info-value {
            font-size: 1rem !important;
            color: #111 !important;
            font-weight: 400 !important;
        }
        
        .status-section {
            background: #f9fafb;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .status-label {
            font-size: 0.875rem !important;
            color: #6b7280 !important;
            margin-bottom: 0.5rem !important;
        }
        
        .status-select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 1rem;
            background: white;
            cursor: pointer;
        }
        
        .document-links {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-top: 1rem;
        }
        
        .document-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            background: #eff6ff;
            color: #2563eb !important;
            text-decoration: none !important;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .document-link:hover {
            background: #dbeafe;
            color: #1d4ed8 !important;
        }
        
        .notes-section {
            margin-top: 1.5rem;
        }
        
        .notes-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .notes-title {
            font-size: 1.125rem !important;
            font-weight: 600 !important;
            color: #111 !important;
        }
        
        .add-note-button {
            padding: 0.5rem 1rem;
            background: #3b82f6;
            color: white !important;
            border: none;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .add-note-button:hover {
            background: #2563eb;
        }
        
        .note-form {
            background: #f9fafb;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .note-textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
        }
        
        .note-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.75rem;
            justify-content: flex-end;
        }
        
        .notes-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .note-item {
            background: #f9fafb;
            padding: 1rem;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
        }
        
        .note-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .note-author {
            font-weight: 500;
            color: #374151;
            font-size: 0.875rem;
        }
        
        .note-date {
            color: #6b7280;
            font-size: 0.75rem;
        }
        
        .note-content {
            color: #111;
            font-size: 0.875rem;
            line-height: 1.5;
            white-space: pre-wrap;
        }
        
        .empty-notes {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
            font-size: 0.875rem;
        }
        
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.375rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .badge-yes {
            background: #d1fae5;
            color: #065f46;
        }
        
        .badge-no {
            background: #fee2e2;
            color: #991b1b;
        }
        
        @media (max-width: 768px) {
            .application-content {
                grid-template-columns: 1fr;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
        </style>
        
        <div class="careers-dashboard-container">
            <div class="dashboard-header">
                <div>
                    <h1 class="dashboard-title">Application Details</h1>
                    <p class="dashboard-subtitle"><?php echo esc_html($applicant_name); ?> - <?php echo $position ? esc_html($position->position_name) : 'General Application'; ?></p>
                </div>
                <div class="header-actions">
                    <a href="<?php echo CareersSettings::get_page_url('applications'); ?>" class="back-button">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 12H5M5 12L12 19M5 12L12 5"/>
                        </svg>
                        Back to Applications
                    </a>
                </div>
            </div>
            
            <div class="application-content">
                <div class="main-column">
                    <!-- Applicant Information -->
                    <div class="info-card">
                        <div class="card-header">
                            <h2 class="card-title">Applicant Information</h2>
                        </div>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Full Name</span>
                                <span class="info-value"><?php echo esc_html($applicant_name); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Email</span>
                                <span class="info-value"><?php echo esc_html($applicant_email); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Phone</span>
                                <span class="info-value"><?php echo !empty($meta['phone']) ? esc_html($meta['phone']) : 'Not provided'; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Location</span>
                                <span class="info-value">
                                    <?php 
                                    $location = array();
                                    if (!empty($meta['current_city'])) $location[] = $meta['current_city'];
                                    if (!empty($meta['current_state'])) $location[] = $meta['current_state'];
                                    echo !empty($location) ? esc_html(implode(', ', $location)) : 'Not provided';
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Application Details -->
                    <div class="info-card">
                        <div class="card-header">
                            <h2 class="card-title">Application Details</h2>
                        </div>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Role Interested In</span>
                                <span class="info-value"><?php echo !empty($meta['role_interested']) ? esc_html($meta['role_interested']) : 'Not specified'; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">How They Heard About Us</span>
                                <span class="info-value"><?php echo !empty($meta['hear_about_us']) ? esc_html($meta['hear_about_us']) : 'Not specified'; ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">New Graduate</span>
                                <span class="badge <?php echo (!empty($meta['new_graduate']) && $meta['new_graduate'] === 'yes') ? 'badge-yes' : 'badge-no'; ?>">
                                    <?php echo (!empty($meta['new_graduate']) && $meta['new_graduate'] === 'yes') ? 'Yes' : 'No'; ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Has Certifications</span>
                                <span class="badge <?php echo (!empty($meta['has_certifications']) && $meta['has_certifications'] === 'yes') ? 'badge-yes' : 'badge-no'; ?>">
                                    <?php echo (!empty($meta['has_certifications']) && $meta['has_certifications'] === 'yes') ? 'Yes' : 'No'; ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Willing to Relocate</span>
                                <span class="badge <?php echo (!empty($meta['willing_relocate']) && $meta['willing_relocate'] === 'yes') ? 'badge-yes' : 'badge-no'; ?>">
                                    <?php echo (!empty($meta['willing_relocate']) && $meta['willing_relocate'] === 'yes') ? 'Yes' : 'No'; ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Willing to Travel</span>
                                <span class="badge <?php echo (!empty($meta['willing_travel']) && $meta['willing_travel'] === 'yes') ? 'badge-yes' : 'badge-no'; ?>">
                                    <?php echo (!empty($meta['willing_travel']) && $meta['willing_travel'] === 'yes') ? 'Yes' : 'No'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Notes -->
                    <div class="info-card">
                        <div class="notes-section">
                            <div class="notes-header">
                                <h3 class="notes-title">Internal Notes</h3>
                                <button class="add-note-button" id="add-note-toggle">Add Note</button>
                            </div>
                            
                            <div class="note-form" id="note-form" style="display: none;">
                                <textarea class="note-textarea" id="note-content" placeholder="Add a note about this application..."></textarea>
                                <div class="note-actions">
                                    <button class="button" id="cancel-note">Cancel</button>
                                    <button class="button button-primary" id="save-note" data-application-id="<?php echo esc_attr($application_id); ?>">Save Note</button>
                                </div>
                            </div>
                            
                            <div class="notes-list" id="notes-list">
                                <?php if (!empty($notes)): ?>
                                    <?php foreach ($notes as $note): ?>
                                        <?php 
                                        $note_author = get_user_by('id', $note->user_id);
                                        $author_name = 'Unknown';
                                        if ($note_author) {
                                            $first_name = get_user_meta($note->user_id, 'first_name', true);
                                            $last_name = get_user_meta($note->user_id, 'last_name', true);
                                            if ($first_name || $last_name) {
                                                $author_name = trim($first_name . ' ' . $last_name);
                                            } else {
                                                $author_name = $note_author->display_name;
                                            }
                                        }
                                        ?>
                                        <div class="note-item">
                                            <div class="note-header">
                                                <span class="note-author"><?php echo esc_html($author_name); ?></span>
                                                <span class="note-date"><?php echo esc_html(human_time_diff(strtotime($note->created_at), current_time('timestamp')) . ' ago'); ?></span>
                                            </div>
                                            <div class="note-content"><?php echo esc_html($note->content); ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="empty-notes">No notes yet. Add the first note about this application.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="sidebar-column">
                    <!-- Status & Actions -->
                    <div class="info-card">
                        <div class="card-header">
                            <h2 class="card-title">Status & Actions</h2>
                        </div>
                        
                        <div class="status-section">
                            <p class="status-label">Application Status</p>
                            <select class="status-select" id="application-status" data-application-id="<?php echo esc_attr($application_id); ?>">
                                <?php foreach ($status_pipeline as $status_key => $status_info): ?>
                                    <option value="<?php echo esc_attr($status_key); ?>" 
                                            <?php selected($application->status, $status_key); ?>>
                                        <?php echo esc_html($status_info['label']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Applied On</span>
                            <span class="info-value"><?php echo esc_html(date('F j, Y \a\t g:i A', strtotime($application->submitted_at))); ?></span>
                        </div>
                    </div>
                    
                    <!-- Documents -->
                    <div class="info-card">
                        <div class="card-header">
                            <h2 class="card-title">Documents</h2>
                        </div>
                        
                        <div class="document-links">
                            <?php if (!empty($application->resume_url)): ?>
                                <a href="<?php echo esc_url($application->resume_url); ?>" target="_blank" class="document-link">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                        <path d="M14 2v6h6"/>
                                        <path d="M16 13H8"/>
                                        <path d="M16 17H8"/>
                                        <path d="M10 9H8"/>
                                    </svg>
                                    View Resume
                                </a>
                            <?php else: ?>
                                <p style="color: #6b7280; font-size: 0.875rem;">No resume uploaded</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($position): ?>
                    <!-- Position Details -->
                    <div class="info-card">
                        <div class="card-header">
                            <h2 class="card-title">Position Applied For</h2>
                        </div>
                        
                        <div class="info-item" style="margin-bottom: 1rem;">
                            <span class="info-label">Position</span>
                            <span class="info-value"><?php echo esc_html($position->position_name); ?></span>
                        </div>
                        
                        <div class="info-item" style="margin-bottom: 1rem;">
                            <span class="info-label">Location</span>
                            <span class="info-value"><?php echo esc_html($position->location); ?></span>
                        </div>
                        
                        <div class="info-item" style="margin-bottom: 1rem;">
                            <span class="info-label">Job Type</span>
                            <span class="info-value"><?php echo esc_html($position->job_type); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Experience Level</span>
                            <span class="info-value"><?php echo !empty($position->experience_level) ? esc_html($position->experience_level) : 'Not specified'; ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Toggle note form
            $('#add-note-toggle').on('click', function() {
                $('#note-form').slideToggle();
                $('#note-content').focus();
            });
            
            $('#cancel-note').on('click', function() {
                $('#note-form').slideUp();
                $('#note-content').val('');
            });
            
            // Save note
            $('#save-note').on('click', function() {
                var $button = $(this);
                var applicationId = $button.data('application-id');
                var noteContent = $('#note-content').val().trim();
                
                if (!noteContent) {
                    alert('Please enter a note');
                    return;
                }
                
                $button.prop('disabled', true).text('Saving...');
                
                $.ajax({
                    url: careers_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'careers_add_application_note',
                        nonce: careers_ajax.nonce,
                        application_id: applicationId,
                        note_content: noteContent
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert(response.data || 'Failed to save note');
                            $button.prop('disabled', false).text('Save Note');
                        }
                    },
                    error: function() {
                        alert('Failed to save note');
                        $button.prop('disabled', false).text('Save Note');
                    }
                });
            });
            
            // Update status
            $('#application-status').on('change', function() {
                var $select = $(this);
                var applicationId = $select.data('application-id');
                var newStatus = $select.val();
                
                $.ajax({
                    url: careers_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'careers_update_application_status',
                        nonce: careers_ajax.nonce,
                        application_id: applicationId,
                        status: newStatus
                    },
                    success: function(response) {
                        if (!response.success) {
                            alert(response.data || 'Failed to update status');
                            location.reload();
                        }
                    },
                    error: function() {
                        alert('Failed to update status');
                        location.reload();
                    }
                });
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Get apply page content
     */
    private function get_apply_content() {
        $position_id = isset($_GET['position_id']) ? intval($_GET['position_id']) : 0;
        
        error_log('=== Careers Debug: Apply Page Handler ===');
        error_log('Careers Debug: GET parameters: ' . print_r($_GET, true));
        error_log('Careers Debug: Position ID from GET: ' . $position_id);
        
        // If no position_id or position_id = 0, treat as general application
        if ($position_id == 0) {
            error_log('Careers Debug: General application (position_id = 0)');
        } else {
            // Get position data to show in the application form
            $position = CareersPositionsDB::get_position($position_id);
            if (!$position || $position->status !== 'published') {
                error_log('Careers Debug: Position not found or not published for ID: ' . $position_id);
                return '<div class="careers-apply-error">Position not found or no longer available.</div>';
            }
            error_log('Careers Debug: Position found: ' . $position->position_name . ' (ID: ' . $position->id . ')');
        }
        
        error_log('Careers Debug: Calling application page shortcode with position_id: ' . $position_id);
        
        // Use the existing application page shortcode to render the application form
        if (class_exists('CareersShortcodes')) {
            $shortcodes = new CareersShortcodes();
            $result = $shortcodes->careers_application_page_shortcode(array('position_id' => $position_id));
            error_log('=== End Careers Debug: Apply Page Handler ===');
            return $result;
        }
        
        error_log('Careers Debug: CareersShortcodes class not found');
        error_log('=== End Careers Debug: Apply Page Handler ===');
        return '<div class="careers-apply-error">Application system not available.</div>';
    }
    
    /**
     * Render dashboard navigation
     */
    private function render_dashboard_navigation($current_page = 'profile') {
        if (class_exists('CareersDashboard')) {
            $dashboard = new CareersDashboard();
            $dashboard->render_dashboard_navigation($current_page);
        }
    }
    
    /**
     * Get profile content
     */
    private function get_profile_content() {
        // Check permissions
        if (!is_user_logged_in()) {
            return '<div class="careers-dashboard-error">You must be logged in to view your profile.</div>';
        }
        
        if (class_exists('CareersDashboard')) {
            $dashboard = new CareersDashboard();
            ob_start();
            $dashboard->render_profile_management();
            return ob_get_clean();
        }
        
        return '<div class="careers-dashboard-error">Profile component not found.</div>';
    }
    
}