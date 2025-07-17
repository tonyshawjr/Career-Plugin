<?php
/**
 * Careers Dashboard Handler - New Custom Table Version
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CareersDashboard {
    
    public function __construct() {
        add_action('wp_ajax_careers_position_action', array($this, 'handle_position_action'));
        add_action('wp_ajax_careers_location_action', array($this, 'handle_location_action'));
        add_action('wp_ajax_careers_bulk_position_action', array($this, 'handle_bulk_position_action'));
        
        // Application management AJAX handlers
        add_action('wp_ajax_careers_update_application_status', array($this, 'handle_update_application_status'));
        add_action('wp_ajax_careers_add_application_note', array($this, 'handle_add_application_note'));
        add_action('wp_ajax_careers_delete_application', array($this, 'handle_delete_application'));
        add_action('wp_ajax_careers_delete_all_applications', array($this, 'handle_delete_all_applications'));
        
        // Dashboard routing now handled by CareersPageHandler
        
        // Add dashboard shortcode
        add_shortcode('careers_form', array($this, 'job_form_shortcode'));
    }
    
    
    // Old routing methods removed - now handled by CareersPageHandler
    
    /**
     * Render unified dashboard navigation
     * Used across all dashboard pages for consistent UX
     */
    public function render_dashboard_navigation($current_page = 'dashboard') {
        $nav_items = array(
            'dashboard' => array(
                'label' => 'Dashboard', 
                'url' => CareersSettings::get_page_url('dashboard'),
                'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z'
            ),
            'manage_jobs' => array(
                'label' => 'Manage Positions', 
                'url' => CareersSettings::get_page_url('manage_jobs'),
                'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
            ),
            'create_job' => array(
                'label' => 'Create Position', 
                'url' => CareersSettings::get_page_url('create_job'),
                'icon' => 'M12 4v16m8-8H4'
            ),
            'applications' => array(
                'label' => 'Applications', 
                'url' => CareersSettings::get_page_url('applications'),
                'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'
            ),
            'locations' => array(
                'label' => 'Manage Locations', 
                'url' => CareersSettings::get_page_url('locations'),
                'icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z'
            ),
            'profile' => array(
                'label' => 'Profile', 
                'url' => CareersSettings::get_page_url('profile'),
                'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'
            )
        );
        
        ?>
        <div class="careers-dashboard-header">
            <div class="dashboard-header-container">
                <!-- Brand/Logo Area -->
                <div class="dashboard-brand">
                    <h1 class="brand-title">Career Portal</h1>
                </div>
                
                <!-- Desktop Navigation -->
                <nav class="dashboard-nav desktop-nav">
                    <ul class="nav-list">
                        <?php foreach ($nav_items as $page_key => $nav_item): ?>
                            <li class="nav-item">
                                <a href="<?php echo esc_url($nav_item['url']); ?>" 
                                   class="nav-link <?php echo $current_page === $page_key ? 'active' : ''; ?>"
                                   <?php echo $current_page === $page_key ? 'aria-current="page"' : ''; ?>>
                                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="<?php echo esc_attr($nav_item['icon']); ?>"/>
                                    </svg>
                                    <span class="nav-label"><?php echo esc_html($nav_item['label']); ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
                
                <!-- Mobile Menu Button -->
                <button class="mobile-menu-toggle" aria-label="Toggle navigation">
                    <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 12h18M3 6h18M3 18h18"/>
                    </svg>
                </button>
            </div>
            
            <!-- Mobile Navigation -->
            <nav class="dashboard-nav mobile-nav">
                <ul class="mobile-nav-list">
                    <?php foreach ($nav_items as $page_key => $nav_item): ?>
                        <li class="mobile-nav-item">
                            <a href="<?php echo esc_url($nav_item['url']); ?>" 
                               class="mobile-nav-link <?php echo $current_page === $page_key ? 'active' : ''; ?>"
                               <?php echo $current_page === $page_key ? 'aria-current="page"' : ''; ?>>
                                <svg class="mobile-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="<?php echo esc_attr($nav_item['icon']); ?>"/>
                                </svg>
                                <span class="mobile-nav-label"><?php echo esc_html($nav_item['label']); ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>
        
        <style>
        /* Dashboard Navigation Styles - Tailwind Inspired */
        .careers-dashboard-header {
            background: var(--background);
            border-bottom: 1px solid var(--border);
            margin-bottom: var(--space-8);
            position: sticky;
            top: 0;
            z-index: 50;
        }
        
        .dashboard-header-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 var(--space-4);
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 4rem; /* 64px */
        }
        
        .dashboard-brand .brand-title {
            font-size: var(--text-xl);
            font-weight: 600;
            color: var(--foreground);
            margin: 0;
        }
        
        /* Desktop Navigation */
        .desktop-nav {
            display: none;
        }
        
        @media (min-width: 768px) {
            .desktop-nav {
                display: block;
            }
        }
        
        .nav-list {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            margin: 0;
            padding: 0;
            list-style: none;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            padding: var(--space-3) var(--space-4);
            color: var(--muted);
            text-decoration: none;
            border-radius: var(--radius-md);
            font-weight: 500;
            font-size: var(--text-sm);
            transition: all 0.15s ease;
        }
        
        .nav-link:hover {
            color: var(--foreground);
            background-color: var(--secondary);
            text-decoration: none;
        }
        
        .nav-link.active {
            color: var(--brand-red);
            background-color: rgba(191, 30, 45, 0.1);
        }
        
        .nav-icon {
            width: 1.25rem;
            height: 1.25rem;
        }
        
        /* Mobile Menu Button */
        .mobile-menu-toggle {
            display: block;
            background: none;
            border: none;
            color: var(--foreground);
            padding: var(--space-2);
            border-radius: var(--radius);
            cursor: pointer;
        }
        
        @media (min-width: 768px) {
            .mobile-menu-toggle {
                display: none;
            }
        }
        
        .menu-icon {
            width: 1.5rem;
            height: 1.5rem;
        }
        
        /* Mobile Navigation */
        .mobile-nav {
            display: none;
            border-top: 1px solid var(--border);
            padding: var(--space-4);
        }
        
        .mobile-nav.open {
            display: block;
        }
        
        .mobile-nav-list {
            display: flex;
            flex-direction: column;
            gap: var(--space-1);
            margin: 0;
            padding: 0;
            list-style: none;
        }
        
        .mobile-nav-link {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            padding: var(--space-3) var(--space-4);
            color: var(--muted);
            text-decoration: none;
            border-radius: var(--radius-md);
            font-weight: 500;
            transition: all 0.15s ease;
        }
        
        .mobile-nav-link:hover {
            color: var(--foreground);
            background-color: var(--secondary);
            text-decoration: none;
        }
        
        .mobile-nav-link.active {
            color: var(--brand-red);
            background-color: rgba(191, 30, 45, 0.1);
        }
        
        .mobile-nav-icon {
            width: 1.25rem;
            height: 1.25rem;
        }
        
                 /* Hide mobile nav on larger screens */
         @media (min-width: 768px) {
             .mobile-nav {
                 display: none !important;
             }
         }
         
         /* Action Icon Styles */
         .action-icon {
             display: inline-flex;
             align-items: center;
             justify-content: center;
             width: 1.75rem;
             height: 1.75rem;
             border-radius: 0.375rem;
             transition: all 0.15s ease;
             text-decoration: none;
             border: none;
             background: transparent;
             cursor: pointer;
             position: relative;
         }
         
         .action-icon svg {
             display: block !important;
             width: 0.875rem !important;
             height: 0.875rem !important;
             stroke-width: 2 !important;
             stroke: currentColor !important;
             fill: none !important;
         }
         
         /* Edit Icon - Primary Blue */
         .edit-icon {
             color: #3b82f6;
             background-color: rgba(59, 130, 246, 0.1);
         }
         
         .edit-icon:hover {
             color: #2563eb;
             background-color: rgba(37, 99, 235, 0.15);
             text-decoration: none;
         }
         
         /* Applications Icon - Neutral Gray */
         .applications-icon {
             color: #6b7280;
             background-color: rgba(107, 114, 128, 0.1);
         }
         
         .applications-icon:hover {
             color: #374151;
             background-color: rgba(55, 65, 81, 0.15);
             text-decoration: none;
         }
         
         /* View Icon - Neutral Gray */
         .view-icon {
             color: #6b7280;
             background-color: rgba(107, 114, 128, 0.1);
         }
         
         .view-icon:hover {
             color: #374151;
             background-color: rgba(55, 65, 81, 0.15);
             text-decoration: none;
         }
         
         /* Delete Icon - Danger Red */
         .delete-icon {
            color: white !important;
             background-color: #dc2626 !important;
         }
         
         .delete-icon:hover {
            color: white !important;
             background-color: #b91c1c !important;
         }
         
         .delete-icon svg {
             display: block !important;
             width: 0.875rem !important;
             height: 0.875rem !important;
             stroke: white !important;
             fill: none !important;
             stroke-width: 2 !important;
         }
         
                   /* Action groups styling */
          .job-actions, .position-actions {
              display: flex;
              gap: 0.25rem;
              align-items: center;
              justify-content: flex-start;
          }
          
          /* Modern Jobs Table Design */
          .jobs-table-container {
              margin-top: var(--space-6);
              border: 1px solid var(--border);
              border-radius: var(--radius-lg);
            overflow: hidden;
              background: var(--background);
          }
          
          .jobs-table-header {
              display: grid;
              grid-template-columns: 2.2fr 1.6fr 0.7fr 0.9fr 1fr 1.2fr;
              gap: 1rem;
              background: var(--gray-50);
              padding: 1rem;
              border-bottom: 1px solid var(--border);
          }
          
          /* Manage Jobs Table with Checkbox Column */
          .jobs-table-header:has(.select-col) {
              grid-template-columns: 0.3fr 2.4fr 1.6fr 0.9fr 0.9fr 1fr 0.6fr 1.2fr;
          }
          
          .table-header-cell {
              font-size: var(--text-sm);
              font-weight: 600;
              color: var(--gray-700);
              text-transform: uppercase;
              letter-spacing: 0.05em;
          }
          
          .jobs-list {
              background: var(--background);
          }
          
          .job-row {
              display: grid;
              grid-template-columns: 2.2fr 1.6fr 0.7fr 0.9fr 1fr 1.2fr;
              gap: 1rem;
              padding: 0.75rem 1rem;
              transition: background-color 0.15s ease;
              align-items: center;
              border-bottom: 1px solid var(--border);
          }
          
          /* Manage Jobs Table Rows with Checkbox Column */
          .job-row:has(.select-col) {
              grid-template-columns: 0.3fr 2.4fr 1.6fr 0.9fr 0.9fr 1fr 0.6fr 1.2fr;
          }
          
          .job-row:last-child {
              border-bottom: none;
          }
          
          .job-row:hover {
              background-color: var(--gray-50);
          }
          
          .job-cell {
              display: flex;
              align-items: center;
          }
          
          .job-title {
              font-size: var(--text-base);
              font-weight: 600;
              color: var(--foreground);
              margin: 0;
              line-height: 1.4;
          }
          
          .location-info {
              display: flex;
              align-items: center;
              gap: var(--space-1);
              color: var(--muted);
              font-size: var(--text-sm);
          }
          
          .location-icon {
              width: 0.875rem;
              height: 0.875rem;
              flex-shrink: 0;
          }
          
          .applications-count {
              font-size: var(--text-base);
              font-weight: 600;
              color: var(--foreground);
              background: var(--gray-100);
              padding: var(--space-1) var(--space-2);
              border-radius: var(--radius);
              min-width: 2rem;
              text-align: center;
          }
          
          .employment-badge {
              background: var(--gray-100);
              color: var(--gray-700);
              padding: var(--space-1) var(--space-3);
              border-radius: var(--radius-full);
              font-size: var(--text-xs);
              font-weight: 500;
              white-space: nowrap;
          }
          
          .employment-badge.not-specified {
              background: var(--gray-50);
              color: var(--gray-500);
          }
          
          .posted-date {
              color: var(--muted);
              font-size: var(--text-sm);
          }
          
          /* Status Badge Styling */
          .status-badge {
              background: var(--gray-100);
              color: var(--gray-700);
              padding: var(--space-1) var(--space-3);
              border-radius: var(--radius-full);
              font-size: var(--text-xs);
              font-weight: 500;
              text-transform: uppercase;
              letter-spacing: 0.05em;
          }
          
          .status-badge.published {
              background: #d1fae5;
              color: #065f46;
          }
          
          .status-badge.draft {
              background: #fef3c7;
              color: #92400e;
          }
          
          /* Checkbox Column Styling */
          .select-col {
              display: flex;
              justify-content: center;
              align-items: center;
              padding: 0;
          }
          
          .select-col input[type="checkbox"] {
              margin: 0;
              width: 16px;
              height: 16px;
          }
          
          /* Locations Table Specific Styling */
          .locations-table-header {
              grid-template-columns: 1fr 1fr 0.8fr;
          }
          
          .job-row:has(.state-col) {
              grid-template-columns: 1fr 1fr 0.8fr;
          }
          
          .state-name, .city-name {
              font-weight: 500;
              color: var(--foreground);
          }
          
          /* Typography Overrides for Consistent Table Styling */
          .jobs-table-container .job-title,
          .jobs-table-container h3.job-title,
          .jobs-table-container .position-name {
              font-size: var(--text-base) !important;
              font-weight: 600 !important;
              color: var(--foreground) !important;
              margin: 0 !important;
            line-height: 1.4 !important;
              text-transform: none !important;
              letter-spacing: normal !important;
          }
          
          .jobs-table-container .table-header-cell {
              font-size: var(--text-sm) !important;
              font-weight: 600 !important;
              color: var(--gray-700) !important;
              text-transform: uppercase !important;
              letter-spacing: 0.05em !important;
          }
          
          .jobs-table-container .location-info {
              font-size: var(--text-sm) !important;
              color: var(--muted) !important;
              font-weight: normal !important;
          }
          
          .jobs-table-container .applications-count {
              font-size: var(--text-base) !important;
              font-weight: 600 !important;
          }
          
          .jobs-table-container .posted-date {
              font-size: var(--text-sm) !important;
              color: var(--muted) !important;
              font-weight: normal !important;
          }
          
          .jobs-table-container .employment-badge {
              font-size: var(--text-xs) !important;
              font-weight: 500 !important;
          }
          
          .jobs-table-container .status-badge {
              font-size: var(--text-xs) !important;
            font-weight: 500 !important;
              text-transform: uppercase !important;
              letter-spacing: 0.05em !important;
          }
          
          .jobs-table-container .state-name,
          .jobs-table-container .city-name {
              font-size: var(--text-base) !important;
              font-weight: 500 !important;
              color: var(--foreground) !important;
          }
          
          /* Override any legacy position info styling */
          .jobs-table-container .position-info-label,
          .jobs-table-container .position-info-item,
          .jobs-table-container .position-location,
          .jobs-table-container .position-date,
          .jobs-table-container .position-apps,
          .jobs-table-container .position-type {
              font-size: var(--text-sm) !important;
              font-weight: normal !important;
              color: var(--muted) !important;
            margin: 0 !important;
          }
          
          /* Column specific styling */
          .job-title-col {
              min-width: 200px;
          }
          
          .actions-col {
            display: flex;
              align-items: center;
              justify-content: flex-start;
          }
          
          .location-col {
              min-width: 150px;
          }
          
          .applications-col {
              justify-content: center;
          }
          
          .employment-col {
              min-width: 120px;
          }
          
          .actions-col, .location-actions-col {
              justify-content: center;
          }
          
          /* Metrics Grid */
          .metrics-grid {
            display: grid;
              grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
              gap: var(--space-4);
              margin-bottom: var(--space-8);
          }
          
          .metric-card {
              background: var(--background);
              border: 1px solid var(--border);
              border-radius: var(--radius-lg);
              padding: var(--space-6);
            text-align: center;
        }
          
          .metric-number {
              font-size: var(--text-3xl) !important;
              font-weight: 700 !important;
              color: var(--foreground) !important;
              margin-bottom: var(--space-2) !important;
          }
          
          .metric-label {
              color: var(--muted) !important;
              font-size: var(--text-sm) !important;
              font-weight: 500 !important;
          }
          
          .section-title {
              font-size: var(--text-xl) !important;
              font-weight: 600 !important;
              color: var(--foreground) !important;
              margin: 0 0 var(--space-4) 0 !important;
          }
          
          /* Mobile Responsive */
          @media (max-width: 768px) {
              .jobs-table-container {
                  border: none;
                  border-radius: 0;
                  background: transparent;
              }
              
              .jobs-table-header {
                  display: none;
              }
              
              .jobs-list {
                  gap: var(--space-3);
              }
              
              .job-row {
                  display: block;
                  background: var(--background);
                  border: 1px solid var(--border);
                  border-radius: var(--radius-lg);
                  margin-bottom: var(--space-3);
                  padding: var(--space-4);
              }
              
              .job-row:hover {
                  background: var(--background);
              }
              
              .job-cell {
                  display: block;
                  margin-bottom: var(--space-3);
              }
              
              .job-cell:last-child {
                  margin-bottom: 0;
              }
              
              .job-title-col {
                  margin-bottom: var(--space-2);
              }
              
              .job-title {
                  font-size: var(--text-base) !important;
                  font-weight: 600 !important;
                  margin-bottom: var(--space-1);
              }
              
              .location-col::before,
              .applications-col::before,
              .employment-col::before,
              .posted-col::before,
              .status-col::before,
              .state-col::before,
              .city-col::before {
                  content: attr(data-label);
                  font-size: var(--text-xs);
                  font-weight: 600;
                  color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
                  display: block;
                  margin-bottom: var(--space-1);
              }
              
              .select-col {
                  display: none;
              }
              
              .metrics-grid {
                  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                  gap: var(--space-3);
              }
          }
         
         /* Tooltip styles - using title attribute for now */
         .action-icon:hover::after {
             content: attr(title);
             position: absolute;
             bottom: 100%;
             left: 50%;
             transform: translateX(-50%);
             background: var(--gray-900);
             color: white;
             padding: var(--space-1) var(--space-2);
             border-radius: var(--radius-sm);
             font-size: var(--text-xs);
             white-space: nowrap;
             z-index: 100;
             margin-bottom: var(--space-1);
         }
         
         .action-icon:hover::before {
             content: '';
             position: absolute;
             bottom: 100%;
             left: 50%;
             transform: translateX(-50%);
             border: 4px solid transparent;
             border-top-color: var(--gray-900);
             z-index: 100;
         }

         /* Page Header Styles */
         .careers-dashboard-container {
             max-width: 1280px;
             margin: 0 auto;
             padding: 0 var(--space-4);
         }
         
         .dashboard-content {
             padding-bottom: var(--space-8);
         }
         
         .page-header {
             margin-bottom: 3rem;
             padding-bottom: 2rem;
             border-bottom: 1px solid #e5e7eb;
         }
         
         .page-header-content {
             display: flex;
             justify-content: space-between;
             align-items: flex-start;
             gap: 2rem;
         }
         
         .page-header-text {
             flex: 1;
         }
         
         .page-title {
             font-size: 2.25rem !important;
             font-weight: 600 !important;
             color: #111827 !important;
             margin: 0 0 0.75rem 0 !important;
             line-height: 1.25 !important;
             font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
         }
         
         .page-subtitle {
             font-size: 1.125rem !important;
             color: #6b7280 !important;
             margin: 0 !important;
             line-height: 1.5 !important;
             font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
         }
         
         .page-actions {
             display: flex;
            gap: 1rem;
             flex-wrap: wrap;
             align-items: flex-start;
         }
         
         .create-position-btn {
             display: inline-flex !important;
             align-items: center !important;
             gap: 0.5rem !important;
             background: #111827 !important;
             color: white !important;
             padding: 0.75rem 1.5rem !important;
             border: none !important;
             border-radius: 0.5rem !important;
             font-size: 0.875rem !important;
             font-weight: 500 !important;
             text-decoration: none !important;
             transition: background-color 0.15s ease !important;
             font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
             white-space: nowrap !important;
         }
         
         .create-position-btn:hover {
             background: #374151 !important;
             color: white !important;
             text-decoration: none !important;
         }
         
         .create-position-btn svg {
             width: 1rem !important;
             height: 1rem !important;
             stroke: currentColor !important;
             fill: none !important;
         }
         
                 /* Dashboard Content Bottom Spacing */
        .jobs-content {
            margin-bottom: 4rem;
        }
        
        /* Create/Edit Job Form Bottom Spacing */
        #careers-position-form {
            margin-bottom: 4rem;
        }
         
         /* Applications Table Specific Styling */
         .applications-table-header {
             grid-template-columns: 2fr 1.8fr 1.2fr 1.5fr 1.3fr 1fr 1.2fr 1.5fr;
         }
         
         .applications-list .job-row {
             grid-template-columns: 2fr 1.8fr 1.2fr 1.5fr 1.3fr 1fr 1.2fr 1.5fr;
         }
         
         .applicant-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
         
         .applicant-name {
             font-weight: 600;
             color: #111827;
             font-size: 0.875rem;
         }
         
         .applicant-email {
            font-size: 0.75rem;
             color: #6b7280;
         }
         
         .position-name {
            font-weight: 500;
             color: #111827;
             font-size: 0.875rem;
         }
         
         .general-application {
             font-style: italic;
             color: #6b7280;
             font-size: 0.875rem;
         }
         
         .modality-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
             border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
            background: #f3f4f6;
             color: #374151;
         }
         
         .status-dropdown-container {
             display: flex;
             align-items: center;
         }
         
         .status-dropdown {
             background: white !important;
             border: 1px solid #d1d5db !important;
             border-radius: 0.375rem !important;
             padding: 0.375rem 0.75rem !important;
             font-size: 0.75rem !important;
             font-weight: 500 !important;
             color: #374151 !important;
             cursor: pointer !important;
             transition: border-color 0.15s ease !important;
             font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
         }
         
         .status-dropdown:focus {
             outline: none !important;
             border-color: #6366f1 !important;
             box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1) !important;
         }
         
         .applied-date {
             font-size: 0.875rem;
             color: #6b7280;
         }
         
         .documents-list {
             display: flex;
             align-items: center;
             gap: 0.5rem;
         }
         
         .document-link {
             display: inline-flex;
             align-items: center;
             justify-content: center;
             width: 1.75rem;
             height: 1.75rem;
             border-radius: 0.375rem;
             background: #f3f4f6;
             color: #374151;
             text-decoration: none;
             transition: all 0.15s ease;
         }
         
         .document-link:hover {
             background: #e5e7eb;
             color: #111827;
             text-decoration: none;
         }
         
         .document-link svg {
             width: 1rem;
             height: 1rem;
             stroke: currentColor;
             fill: none;
         }
         
         .resume-link {
             background: #dbeafe;
             color: #2563eb;
         }
         
         .resume-link:hover {
             background: #bfdbfe;
             color: #1d4ed8;
         }
         
         .cover-letter-link {
             background: #d1fae5;
            color: #059669;
        }
         
         .cover-letter-link:hover {
             background: #a7f3d0;
             color: #047857;
         }
         
         .no-documents {
             font-size: 0.75rem;
             color: #9ca3af;
             font-style: italic;
         }
         
         .notes-icon {
             background-color: rgba(107, 114, 128, 0.1);
             color: #6b7280;
         }
         
         .notes-icon:hover {
             background-color: rgba(55, 65, 81, 0.15);
             color: #374151;
         }
         
         .delete-all-btn {
             display: inline-flex !important;
             align-items: center !important;
             gap: 0.5rem !important;
             background: #dc2626 !important;
             color: white !important;
             padding: 0.75rem 1.5rem !important;
             border: none !important;
             border-radius: 0.5rem !important;
            font-size: 0.875rem !important;
             font-weight: 500 !important;
            text-decoration: none !important;
             transition: background-color 0.15s ease !important;
             font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
             cursor: pointer !important;
         }
         
         .delete-all-btn:hover {
             background: #b91c1c !important;
             color: white !important;
            text-decoration: none !important;
        }
         
         .delete-all-btn svg {
             width: 1rem !important;
             height: 1rem !important;
             stroke: currentColor !important;
             fill: none !important;
         }
         
         @media (max-width: 768px) {
             .careers-dashboard-container {
                 padding: 0 1rem;
             }
             
             .dashboard-content {
                 padding-bottom: 1.5rem;
             }
             
             .page-header {
            margin-bottom: 2rem;
                 padding-bottom: 1.5rem;
             }
             
             .page-header-content {
                 flex-direction: column;
                 gap: 1.5rem;
             }
             
             .page-title {
                 font-size: 1.875rem !important;
             }
             
             .page-subtitle {
                 font-size: 1rem !important;
             }
             
             .page-actions {
                 width: 100%;
             }
             
             .create-position-btn {
                 justify-content: center !important;
                 width: 100% !important;
             }
             
             .careers-dashboard-container .filters-grid {
                 grid-template-columns: 1fr !important;
                 gap: 1rem !important;
             }
             
             .careers-dashboard-container .filters-section {
                 padding: 1rem !important;
             }
             
             /* Applications Table Mobile Styling */
             .applications-table-header {
                 display: none !important;
             }
             
             .applications-list .job-row {
                 display: block !important;
                 padding: 1rem !important;
                 margin-bottom: 1rem !important;
                 border: 1px solid #e5e7eb !important;
                 border-radius: 0.5rem !important;
                 background: white !important;
             }
             
             .applications-list .job-cell {
                 display: flex !important;
                 justify-content: space-between !important;
                 align-items: center !important;
                 padding: 0.5rem 0 !important;
                 border-bottom: 1px solid #f3f4f6 !important;
             }
             
             .applications-list .job-cell:last-child {
                 border-bottom: none !important;
             }
             
             .applications-list .job-cell:before {
                 content: attr(data-label) ": ";
                 font-weight: 600 !important;
                 color: #374151 !important;
                 font-size: 0.875rem !important;
                 min-width: 90px !important;
             }
             
             .applications-list .applicant-col:before {
                 content: "Applicant: ";
             }
             
             .applications-list .position-col:before {
                 content: "Position: ";
             }
             
             .applications-list .modality-col:before {
                 content: "Job Type: ";
             }
             
             .applications-list .location-col:before {
                 content: "Location: ";
             }
             
             .applications-list .status-col:before {
                 content: "Status: ";
             }
             
             .applications-list .applied-col:before {
                 content: "Applied: ";
             }
             
             .applications-list .documents-col:before {
                 content: "Documents: ";
             }
             
             .applications-list .actions-col:before {
                 content: "Actions: ";
             }
             
             .applicant-info {
                 align-items: flex-end !important;
                 text-align: right !important;
             }
             
            .job-actions {
                 justify-content: flex-end !important;
            }
        }
        
        /* Profile Page Styles */
        .careers-dashboard-container .profile-content {
            margin-bottom: 4rem;
        }
        
        .careers-dashboard-container .profile-form {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 2rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .careers-dashboard-container .form-section {
            margin-bottom: 2rem;
        }
        
        .careers-dashboard-container .form-section:last-child {
            margin-bottom: 0;
        }
        
        .careers-dashboard-container .section-title {
            font-size: 1.25rem !important;
            font-weight: 600 !important;
            color: #111 !important;
            margin: 0 0 1rem 0 !important;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .careers-dashboard-container .form-row {
            margin-bottom: 1.5rem;
        }
        
        .careers-dashboard-container .form-row label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
            font-size: 0.875rem;
        }
        
        .careers-dashboard-container .form-row input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 1rem;
            transition: border-color 0.2s;
            box-sizing: border-box;
        }
        
        .careers-dashboard-container .form-row input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .careers-dashboard-container .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .careers-dashboard-container .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e5e7eb;
        }
        
        .careers-dashboard-container .alert {
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1.5rem;
        }
        
        .careers-dashboard-container .alert-success {
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }
        
        .careers-dashboard-container .alert-error {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        
        .careers-dashboard-container .password-note {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .careers-dashboard-container .form-grid {
                grid-template-columns: 1fr;
            }
        }
        </style>
        
        <script>
        // Mobile menu toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.mobile-menu-toggle');
            const mobileNav = document.querySelector('.mobile-nav');
            
            if (menuToggle && mobileNav) {
                menuToggle.addEventListener('click', function() {
                    mobileNav.classList.toggle('open');
                });
            }
        });
        </script>
        <?php
    }
    
    /**
     * Render main dashboard
     */
    public function render_main_dashboard() {
        $stats = CareersPositionsDB::get_stats();
        $application_stats = CareersApplicationDB::get_stats();
        
        // Calculate additional metrics
        $total_jobs = $stats['total'] ?? 0;
        $total_applications = $application_stats['total'] ?? 0;
        $avg_applications_per_job = $total_jobs > 0 ? round($total_applications / $total_jobs, 1) : 0;
        
        // Get active jobs for the table
        $active_jobs = CareersPositionsDB::get_positions(array('status' => 'published', 'limit' => 10));
        
        ?>
        <?php $this->render_dashboard_navigation('dashboard'); ?>
        
        <div class="careers-dashboard-container">
            <div class="dashboard-content">
                <div class="page-header">
                    <div class="page-header-content">
                        <div class="page-header-text">
                            <h1 class="page-title">Dashboard Overview</h1>
                            <p class="page-subtitle">Monitor your careers system performance</p>
                        </div>
                    </div>
                </div>
            
            <!-- Removed dashboard tabs since applications is now a separate page -->
            
            <!-- Main Jobs Content -->
            <div class="jobs-content">
                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-number"><?php echo esc_html($total_jobs); ?></div>
                        <div class="metric-label">Total Jobs</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-number"><?php echo esc_html($total_applications); ?></div>
                        <div class="metric-label">Total Applications</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-number"><?php echo esc_html($avg_applications_per_job); ?></div>
                        <div class="metric-label">Avg Applications/Job</div>
                    </div>
                </div>
                
                <div class="jobs-section">
                    <h3 class="section-title">Active Jobs</h3>
                    <?php if (empty($active_jobs)): ?>
                        <div class="empty-state">
                            <h3>No active jobs</h3>
                            <p>Create your first job posting to get started.</p>
                        </div>
                    <?php else: ?>
                        <!-- Jobs Table -->
                        <div class="jobs-table-container">
                            <!-- Table Header -->
                            <div class="jobs-table-header">
                                <div class="table-header-cell job-title-col">Job Title</div>
                                <div class="table-header-cell location-col">Location</div>
                                <div class="table-header-cell applications-col">Apps</div>
                                <div class="table-header-cell employment-col">Type</div>
                                <div class="table-header-cell posted-col">Posted</div>
                                <div class="table-header-cell actions-col">Actions</div>
                            </div>
                            
                            <!-- Jobs List -->
                            <div class="jobs-list">
                            <?php foreach ($active_jobs as $job): ?>
                                <div class="job-row">
                                    <div class="job-cell job-title-col">
                                            <h4 class="job-title"><?php echo esc_html($job->position_name); ?></h4>
                                        </div>
                                    <div class="job-cell location-col" data-label="Location">
                                        <div class="location-info">
                                            <svg class="location-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                                <circle cx="12" cy="10" r="3"/>
                                            </svg>
                                            <?php echo esc_html($job->location); ?>
                                        </div>
                                        </div>
                                    <div class="job-cell applications-col" data-label="Applications">
                                        <span class="applications-count">
                                            <?php echo esc_html(CareersApplicationDB::get_applications_count_by_job($job->id)); ?>
                                        </span>
                                    </div>
                                    <div class="job-cell employment-col" data-label="Employment Type">
                                            <?php if (!empty($job->job_type)): ?>
                                                <?php 
                                                $type_class = strtolower(str_replace([' ', '-'], '-', $job->job_type));
                                                ?>
                                            <span class="employment-badge <?php echo esc_attr($type_class); ?>">
                                                <?php echo esc_html($job->job_type); ?>
                                            </span>
                                            <?php else: ?>
                                            <span class="employment-badge not-specified">Not specified</span>
                                            <?php endif; ?>
                                        </div>
                                    <div class="job-cell posted-col" data-label="Posted Date">
                                        <span class="posted-date"><?php echo esc_html(date('M j, Y', strtotime($job->created_at))); ?></span>
                                    </div>
                                    <div class="job-actions">
                                            <a href="<?php echo CareersSettings::get_page_url('edit_job', array('id' => $job->id)); ?>" 
                                               class="action-icon edit-icon" 
                                               title="Edit Position"
                                               aria-label="Edit <?php echo esc_attr($job->position_name); ?>">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                                    <path d="m18.5 2.5 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                                </svg>
                                            </a>
                                            <a href="<?php echo CareersSettings::get_page_url('applications', array('job_id' => $job->id)); ?>" 
                                               class="action-icon applications-icon" 
                                               title="View Applications"
                                               aria-label="View applications for <?php echo esc_attr($job->position_name); ?>">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                                    <circle cx="9" cy="7" r="4"/>
                                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                                </svg>
                                            </a>
                                            <a href="<?php echo esc_url(careers_get_job_permalink($job->id)); ?>" 
                                               class="action-icon view-icon" 
                                               title="View Job Posting" 
                                               target="_blank"
                                               aria-label="View job posting for <?php echo esc_attr($job->position_name); ?>">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                    <circle cx="12" cy="12" r="3"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
                    </div>
        <?php
    }
    
    /**
     * Render position creation form
     */
    public function render_position_creation_form() {
        $locations = CareersPositionsDB::get_locations();
        
        // Group locations by state for better organization
        $locations_by_state = array();
        foreach ($locations as $location) {
            $locations_by_state[$location->state][] = $location;
        }
        
        // Sort states alphabetically
        ksort($locations_by_state);
        
        // Sort cities within each state alphabetically
        foreach ($locations_by_state as $state => $cities) {
            usort($locations_by_state[$state], function($a, $b) {
                return strcmp($a->city, $b->city);
            });
        }
        
        ?>
        <?php $this->render_dashboard_navigation('create_job'); ?>
        
        <div class="careers-dashboard-container">
            <div class="dashboard-content">
                <div class="page-header">
                    <div class="page-header-content">
                        <div class="page-header-text">
                            <h1 class="page-title">Create New Position</h1>
                            <p class="page-subtitle">Add a new position to your careers system</p>
                        </div>
                    </div>
                </div>
            
            <form id="careers-position-form" method="post" action="">
                <?php wp_nonce_field('careers_position_action', 'careers_nonce'); ?>
                <input type="hidden" name="operation" value="create">
                
                <div class="form-grid">
                    <div class="form-row">
                        <label for="position_name">Position Name *</label>
                        <input type="text" id="position_name" name="position_name" required 
                               placeholder="e.g. Mobile X-Ray Technician">
                    </div>
                    
                    <div class="form-row">
                        <label for="location">Location *</label>
                        <select id="location" name="location" required>
                            <option value="">Select Location</option>
                            <?php foreach ($locations_by_state as $state => $cities): ?>
                                <optgroup label="<?php echo esc_attr($state); ?>">
                                    <?php foreach ($cities as $location): ?>
                                        <option value="<?php echo esc_attr($location->display_name); ?>">
                                            <?php echo esc_html($location->city); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-row">
                        <label for="job_type">Job Type</label>
                        <select id="job_type" name="job_type">
                            <option value="">Select Job Type</option>
                            <option value="Full-time">Full-time</option>
                            <option value="Part-time">Part-time</option>
                            <option value="Contract">Contract</option>
                            <option value="Per Diem">Per Diem</option>
                            <option value="Travel">Travel</option>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <label for="salary_range">Salary Range</label>
                        <input type="text" id="salary_range" name="salary_range" 
                               placeholder="e.g. $50,000 - $70,000 annually">
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-row">
                        <label for="schedule_type">Schedule</label>
                        <select id="schedule_type" name="schedule_type">
                            <option value="">Select Schedule</option>
                            <option value="Monday-Friday">Monday-Friday</option>
                            <option value="Weekends">Weekends</option>
                            <option value="Flexible">Flexible</option>
                            <option value="On-call">On-call</option>
                            <option value="Rotating">Rotating</option>
                            <option value="Night Shift">Night Shift</option>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <label for="experience_level">Experience Level</label>
                        <select id="experience_level" name="experience_level">
                            <option value="">Select Experience Level</option>
                            <option value="Entry Level">Entry Level</option>
                            <option value="1-2 years">1-2 years</option>
                            <option value="3-5 years">3-5 years</option>
                            <option value="5+ years">5+ years</option>
                            <option value="Senior Level">Senior Level</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <label for="certification_required">Certification Required</label>
                    <input type="text" id="certification_required" name="certification_required" 
                           placeholder="e.g. ARRT, State License, CPR">
                    <small>List required certifications, separated by commas</small>
                </div>
                
                <div class="form-row">
                    <label for="position_overview">Position Overview</label>
                    <textarea id="position_overview" name="position_overview" rows="4" 
                              placeholder="Brief overview of the position and what the role entails..."></textarea>
                </div>
                
                <div class="form-row">
                    <label for="responsibilities">Responsibilities</label>
                    <textarea id="responsibilities" name="responsibilities" rows="6" 
                              placeholder="List one responsibility per line..."></textarea>
                    <small>Enter one responsibility per line. These will be displayed as a bulleted list.</small>
                </div>
                
                <div class="form-row">
                    <label for="requirements">Requirements</label>
                    <textarea id="requirements" name="requirements" rows="6" 
                              placeholder="List one requirement per line..."></textarea>
                    <small>Enter one requirement per line. These will be displayed as a bulleted list.</small>
                </div>
                
                <div class="form-grid">
                    <div class="form-row">
                        <label for="equipment">Equipment Used</label>
                        <textarea id="equipment" name="equipment" rows="4" 
                                  placeholder="List equipment/tools used, one per line..."></textarea>
                        <small>Enter one piece of equipment per line.</small>
                    </div>
                    
                    <div class="form-row">
                        <label for="benefits">Benefits</label>
                        <textarea id="benefits" name="benefits" rows="4" 
                                  placeholder="List benefits, one per line..."></textarea>
                        <small>Enter one benefit per line. These will be displayed as a bulleted list.</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <label for="license_info">State License Info</label>
                    <textarea id="license_info" name="license_info" rows="3" 
                              placeholder="Any licensing requirements or information..."></textarea>
                    <small>HTML tags are allowed for formatting.</small>
                </div>
                
                <div class="form-row">
                    <div class="checkbox-row">
                        <input type="checkbox" id="has_vehicle" name="has_vehicle" value="1">
                        <label for="has_vehicle">Company Vehicle Provided</label>
                    </div>
                </div>
                
                <div class="form-row" id="vehicle_description_row" style="display: none;">
                    <label for="vehicle_description">Vehicle Description</label>
                    <input type="text" id="vehicle_description" name="vehicle_description" 
                           placeholder="Describe the company vehicle...">
                </div>
                
                <div class="form-row">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="published">Published</option>
                        <option value="draft">Draft</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="button button-primary">Create Position</button>
                    <a href="<?php echo CareersSettings::get_page_url('manage_jobs'); ?>" class="button">Cancel</a>
                </div>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Toggle vehicle description field
            $('#has_vehicle').change(function() {
                if ($(this).is(':checked')) {
                    $('#vehicle_description_row').show();
                } else {
                    $('#vehicle_description_row').hide();
                    $('#vehicle_description').val('');
                }
            });
            
            // Handle form submission
            $('#careers-position-form').on('submit', function(e) {
                e.preventDefault();
                
                var formData = $(this).serialize();
                formData += '&action=careers_position_action';
                
                $.ajax({
                    url: careers_ajax.ajax_url,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            alert('Position created successfully!');
                            window.location.href = '<?php echo CareersSettings::get_page_url('manage_jobs'); ?>';
                        } else {
                            alert('Error: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('Error submitting form. Please try again.');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Render position edit form
     */
    public function render_position_edit_form($id) {
        $position = CareersPositionsDB::get_position($id);
        $locations = CareersPositionsDB::get_locations();
        
        // Group locations by state for better organization
        $locations_by_state = array();
        foreach ($locations as $location) {
            $locations_by_state[$location->state][] = $location;
        }
        
        // Sort states alphabetically
        ksort($locations_by_state);
        
        // Sort cities within each state alphabetically
        foreach ($locations_by_state as $state => $cities) {
            usort($locations_by_state[$state], function($a, $b) {
                return strcmp($a->city, $b->city);
            });
        }
        
        if (!$position) {
            echo '<div class="error">Position not found.</div>';
            return;
        }
        
        ?>
        <style>
        .careers-dashboard-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 2rem 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            color: #333;
            font-size: 16px !important;
        }
        .careers-dashboard-container * {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        /* High specificity CSS overrides to fix font and underline issues */
        .careers-dashboard-container,
        .careers-dashboard-container * {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            font-size: 16px !important;
            line-height: 1.5 !important;
        }
        
        /* Remove all underlines and text decorations with maximum specificity */
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
        .careers-dashboard-container .create-button:focus,
        .careers-dashboard-container .button,
        .careers-dashboard-container .button:link,
        .careers-dashboard-container .button:visited,
        .careers-dashboard-container .button:hover,
        .careers-dashboard-container .button:active,
        .careers-dashboard-container .button:focus {
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
        
        .careers-dashboard-container h1,
        .careers-dashboard-container h2,
        .careers-dashboard-container h3,
        .careers-dashboard-container h4,
        .careers-dashboard-container h5,
        .careers-dashboard-container h6 {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            font-weight: 500 !important;
        }
        .careers-dashboard-container p,
        .careers-dashboard-container span,
        .careers-dashboard-container div {
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
        }
        .careers-dashboard-container .dashboard-subtitle {
            color: #666 !important;
            margin: 0 !important;
            font-size: 1rem !important;
        }
        
        .careers-dashboard-container .form-row {
            margin-bottom: 2rem;
        }
        .careers-dashboard-container .form-row label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #111;
            font-size: 1rem;
        }
        .careers-dashboard-container .form-row input, 
        .careers-dashboard-container .form-row textarea, 
        .careers-dashboard-container .form-row select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            box-sizing: border-box;
            transition: border-color 0.2s ease;
            background: #fff;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        .careers-dashboard-container .form-row input:focus, 
        .careers-dashboard-container .form-row textarea:focus, 
        .careers-dashboard-container .form-row select:focus {
            outline: none;
            border-color: #333;
            box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.05);
        }
        .careers-dashboard-container .form-row small {
            display: block;
            margin-top: 0.25rem;
            color: #666;
            font-size: 0.875rem;
        }
        .careers-dashboard-container .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .careers-dashboard-container .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #eee;
        }
        .careers-dashboard-container .button {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem !important;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none !important;
            text-align: center;
            transition: background 0.2s ease;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        .careers-dashboard-container .button-primary {
            background: #000 !important;
            color: white !important;
        }
        .careers-dashboard-container .button-primary:hover {
            background: #333 !important;
            color: white !important;
            text-decoration: none !important;
        }
        .careers-dashboard-container .button:not(.button-primary) {
            background: #f5f5f5 !important;
            color: #333 !important;
            border: 1px solid #ddd !important;
        }
        .careers-dashboard-container .button:not(.button-primary):hover {
            background: #e8e8e8 !important;
            color: #333 !important;
            text-decoration: none !important;
        }
        .careers-dashboard-container .checkbox-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .careers-dashboard-container .checkbox-row input[type="checkbox"] {
            width: auto;
            margin: 0;
        }
        @media (max-width: 768px) {
            .careers-dashboard-container .form-grid {
                grid-template-columns: 1fr;
            }
        }
        </style>
        
        <?php $this->render_dashboard_navigation('manage_jobs'); ?>
        
        <div class="careers-dashboard-container">
            <div class="dashboard-content">
                <div class="page-header">
                    <div class="page-header-content">
                        <div class="page-header-text">
                            <h1 class="page-title">Edit Position</h1>
                            <p class="page-subtitle">Update your position details</p>
                        </div>
                    </div>
                </div>
            
            <form id="careers-position-edit-form" method="post" action="">
                <?php wp_nonce_field('careers_position_action', 'careers_nonce'); ?>
                <input type="hidden" name="operation" value="update">
                <input type="hidden" name="position_id" value="<?php echo esc_attr($position->id); ?>">
                
                <div class="form-grid">
                    <div class="form-row">
                        <label for="position_name">Position Name *</label>
                        <input type="text" id="position_name" name="position_name" required 
                               value="<?php echo esc_attr($position->position_name); ?>"
                               placeholder="e.g. Mobile X-Ray Technician">
                    </div>
                    
                    <div class="form-row">
                        <label for="location">Location *</label>
                        <select id="location" name="location" required>
                            <option value="">Select Location</option>
                            <?php foreach ($locations_by_state as $state => $cities): ?>
                                <optgroup label="<?php echo esc_attr($state); ?>">
                                    <?php foreach ($cities as $location): ?>
                                        <option value="<?php echo esc_attr($location->display_name); ?>"
                                                <?php selected($position->location, $location->display_name); ?>>
                                            <?php echo esc_html($location->city); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-row">
                        <label for="job_type">Job Type</label>
                        <select id="job_type" name="job_type">
                            <option value="">Select Job Type</option>
                            <option value="Full-time" <?php selected($position->job_type, 'Full-time'); ?>>Full-time</option>
                            <option value="Part-time" <?php selected($position->job_type, 'Part-time'); ?>>Part-time</option>
                            <option value="Contract" <?php selected($position->job_type, 'Contract'); ?>>Contract</option>
                            <option value="Per Diem" <?php selected($position->job_type, 'Per Diem'); ?>>Per Diem</option>
                            <option value="Travel" <?php selected($position->job_type, 'Travel'); ?>>Travel</option>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <label for="salary_range">Salary Range</label>
                        <input type="text" id="salary_range" name="salary_range" 
                               value="<?php echo esc_attr($position->salary_range); ?>"
                               placeholder="e.g. $50,000 - $70,000 annually">
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-row">
                        <label for="schedule_type">Schedule</label>
                        <select id="schedule_type" name="schedule_type">
                            <option value="">Select Schedule</option>
                            <option value="Monday-Friday" <?php selected($position->schedule_type, 'Monday-Friday'); ?>>Monday-Friday</option>
                            <option value="Weekends" <?php selected($position->schedule_type, 'Weekends'); ?>>Weekends</option>
                            <option value="Flexible" <?php selected($position->schedule_type, 'Flexible'); ?>>Flexible</option>
                            <option value="On-call" <?php selected($position->schedule_type, 'On-call'); ?>>On-call</option>
                            <option value="Rotating" <?php selected($position->schedule_type, 'Rotating'); ?>>Rotating</option>
                            <option value="Night Shift" <?php selected($position->schedule_type, 'Night Shift'); ?>>Night Shift</option>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <label for="experience_level">Experience Level</label>
                        <select id="experience_level" name="experience_level">
                            <option value="">Select Experience Level</option>
                            <option value="Entry Level" <?php selected($position->experience_level, 'Entry Level'); ?>>Entry Level</option>
                            <option value="1-2 years" <?php selected($position->experience_level, '1-2 years'); ?>>1-2 years</option>
                            <option value="3-5 years" <?php selected($position->experience_level, '3-5 years'); ?>>3-5 years</option>
                            <option value="5+ years" <?php selected($position->experience_level, '5+ years'); ?>>5+ years</option>
                            <option value="Senior Level" <?php selected($position->experience_level, 'Senior Level'); ?>>Senior Level</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <label for="certification_required">Certification Required</label>
                    <input type="text" id="certification_required" name="certification_required" 
                           value="<?php echo esc_attr($position->certification_required); ?>"
                           placeholder="e.g. ARRT, State License, CPR">
                    <small>List required certifications, separated by commas</small>
                </div>
                
                <div class="form-row">
                    <label for="position_overview">Position Overview</label>
                    <textarea id="position_overview" name="position_overview" rows="4" 
                              placeholder="Brief overview of the position and what the role entails..."><?php echo esc_textarea($position->position_overview); ?></textarea>
                </div>
                
                <div class="form-row">
                    <label for="responsibilities">Responsibilities</label>
                    <textarea id="responsibilities" name="responsibilities" rows="6" 
                              placeholder="List one responsibility per line..."><?php echo esc_textarea($position->responsibilities); ?></textarea>
                    <small>Enter one responsibility per line. These will be displayed as a bulleted list.</small>
                </div>
                
                <div class="form-row">
                    <label for="requirements">Requirements</label>
                    <textarea id="requirements" name="requirements" rows="6" 
                              placeholder="List one requirement per line..."><?php echo esc_textarea($position->requirements); ?></textarea>
                    <small>Enter one requirement per line. These will be displayed as a bulleted list.</small>
                </div>
                
                <div class="form-grid">
                    <div class="form-row">
                        <label for="equipment">Equipment Used</label>
                        <textarea id="equipment" name="equipment" rows="4" 
                                  placeholder="List equipment/tools used, one per line..."><?php echo esc_textarea($position->equipment); ?></textarea>
                        <small>Enter one piece of equipment per line.</small>
                    </div>
                    
                    <div class="form-row">
                        <label for="benefits">Benefits</label>
                        <textarea id="benefits" name="benefits" rows="4" 
                                  placeholder="List benefits, one per line..."><?php echo esc_textarea($position->benefits); ?></textarea>
                        <small>Enter one benefit per line. These will be displayed as a bulleted list.</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <label for="license_info">State License Info</label>
                    <textarea id="license_info" name="license_info" rows="3" 
                              placeholder="Any licensing requirements or information..."><?php echo esc_textarea($position->license_info); ?></textarea>
                    <small>HTML tags are allowed for formatting.</small>
                </div>
                
                <div class="form-row">
                    <div class="checkbox-row">
                        <input type="checkbox" id="has_vehicle" name="has_vehicle" value="1"
                               <?php checked($position->has_vehicle, 1); ?>>
                        <label for="has_vehicle">Company Vehicle Provided</label>
                    </div>
                </div>
                
                <div class="form-row" id="vehicle_description_row" 
                     style="<?php echo $position->has_vehicle ? '' : 'display: none;'; ?>">
                    <label for="vehicle_description">Vehicle Description</label>
                    <input type="text" id="vehicle_description" name="vehicle_description" 
                           value="<?php echo esc_attr($position->vehicle_description); ?>"
                           placeholder="Describe the company vehicle...">
                </div>
                
                <div class="form-row">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="published" <?php selected($position->status, 'published'); ?>>Published</option>
                        <option value="draft" <?php selected($position->status, 'draft'); ?>>Draft</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="button button-primary">Update Position</button>
                    <a href="<?php echo CareersSettings::get_page_url('manage_jobs'); ?>" class="button">Cancel</a>
                </div>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Toggle vehicle description field
            $('#has_vehicle').change(function() {
                if ($(this).is(':checked')) {
                    $('#vehicle_description_row').show();
                } else {
                    $('#vehicle_description_row').hide();
                    $('#vehicle_description').val('');
                }
            });
            
            // Handle form submission
            $('#careers-position-edit-form').on('submit', function(e) {
                e.preventDefault();
                
                var formData = $(this).serialize();
                formData += '&action=careers_position_action';
                
                $.ajax({
                    url: careers_ajax.ajax_url,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            alert('Position updated successfully!');
                            window.location.href = '<?php echo CareersSettings::get_page_url('manage_jobs'); ?>';
                        } else {
                            alert('Error: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('Error submitting form. Please try again.');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Render position management - Enhanced with filtering, search, and bulk actions
     */
    public function render_position_management() {
        // Get filter parameters
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        $job_type_filter = isset($_GET['job_type']) ? sanitize_text_field($_GET['job_type']) : '';
        $location_filter = isset($_GET['location']) ? sanitize_text_field($_GET['location']) : '';
        $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page = 20;
        
        // Build query arguments
        $args = array(
            'limit' => $per_page,
            'offset' => ($page - 1) * $per_page,
            'orderby' => 'created_at',
            'order' => 'DESC'
        );
        
        if (!empty($status_filter)) {
            $args['status'] = $status_filter;
        }
        
        if (!empty($search_query)) {
            $args['search'] = $search_query;
        }
        
        if (!empty($job_type_filter)) {
            $args['job_type'] = $job_type_filter;
        }
        
        if (!empty($location_filter)) {
            $args['location'] = $location_filter;
        }
        
        // Get positions and total count
        $positions = CareersPositionsDB::get_positions($args);
        $total_args = $args;
        unset($total_args['limit'], $total_args['offset']);
        $total_positions = CareersPositionsDB::get_positions_count($total_args);
        
        // Get filter options
        $all_locations = CareersPositionsDB::get_locations();
        $job_types = array('Full-Time', 'Part-Time', 'Contract', 'Per-Diem', 'Travel');
        
        // Group locations by state for better organization
        $locations_by_state = array();
        foreach ($all_locations as $location) {
            $locations_by_state[$location->state][] = $location;
        }
        
        // Sort states alphabetically
        ksort($locations_by_state);
        
        // Sort cities within each state alphabetically
        foreach ($locations_by_state as $state => $cities) {
            usort($locations_by_state[$state], function($a, $b) {
                return strcmp($a->city, $b->city);
            });
        }
        
        // Pagination calculations
        $total_pages = ceil($total_positions / $per_page);
        
        ?>
        <style>
        .careers-dashboard-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 2rem 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            color: #333;
            font-size: 16px !important;
        }
        .careers-dashboard-container * {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        /* High specificity CSS overrides to fix font and underline issues */
        .careers-dashboard-container,
        .careers-dashboard-container * {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            font-size: 16px !important;
            line-height: 1.5 !important;
        }
        
        /* Remove all underlines and text decorations with maximum specificity */
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
        .careers-dashboard-container .create-button:focus,
        .careers-dashboard-container .button,
        .careers-dashboard-container .button:link,
        .careers-dashboard-container .button:visited,
        .careers-dashboard-container .button:hover,
        .careers-dashboard-container .button:active,
        .careers-dashboard-container .button:focus {
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
        
        .careers-dashboard-container h1,
        .careers-dashboard-container h2,
        .careers-dashboard-container h3,
        .careers-dashboard-container h4,
        .careers-dashboard-container h5,
        .careers-dashboard-container h6 {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            font-weight: 500 !important;
        }
        .careers-dashboard-container p,
        .careers-dashboard-container span,
        .careers-dashboard-container div {
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
        }
        .careers-dashboard-container .dashboard-subtitle {
            color: #666 !important;
            margin: 0 !important;
            font-size: 1rem !important;
        }
        
        .careers-dashboard-container .form-row {
            margin-bottom: 2rem;
        }
        .careers-dashboard-container .form-row label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #111;
            font-size: 1rem;
        }
        .careers-dashboard-container .form-row input, 
        .careers-dashboard-container .form-row textarea, 
        .careers-dashboard-container .form-row select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            box-sizing: border-box;
            transition: border-color 0.2s ease;
            background: #fff;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        .careers-dashboard-container .form-row input:focus, 
        .careers-dashboard-container .form-row textarea:focus, 
        .careers-dashboard-container .form-row select:focus {
            outline: none;
            border-color: #333;
            box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.05);
        }
        .careers-dashboard-container .form-row small {
            display: block;
            margin-top: 0.25rem;
            color: #666;
            font-size: 0.875rem;
        }
        .careers-dashboard-container .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .careers-dashboard-container .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #eee;
        }
        .careers-dashboard-container .button {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem !important;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none !important;
            text-align: center;
            transition: background 0.2s ease;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        .careers-dashboard-container .button-primary {
            background: #000 !important;
            color: white !important;
        }
        .careers-dashboard-container .button-primary:hover {
            background: #333 !important;
            color: white !important;
            text-decoration: none !important;
        }
        .careers-dashboard-container .button:not(.button-primary) {
            background: #f5f5f5 !important;
            color: #333 !important;
            border: 1px solid #ddd !important;
        }
        .careers-dashboard-container .button:not(.button-primary):hover {
            background: #e8e8e8 !important;
            color: #333 !important;
            text-decoration: none !important;
        }
        .careers-dashboard-container .checkbox-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .careers-dashboard-container .checkbox-row input[type="checkbox"] {
            width: auto;
            margin: 0;
        }
        
        /* Position Management Specific Styles */
        .careers-dashboard-container .header-actions {
            display: flex;
            gap: 1rem;
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
            cursor: pointer !important;
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
        
        /* Filter Section - Clean Modern Design */
        .careers-dashboard-container .filters-section {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }
        .careers-dashboard-container .filters-grid {
            display: grid;
            grid-template-columns: 2.5fr 1.2fr 1.2fr 1.5fr auto;
            gap: 1.25rem;
            align-items: end;
        }
        .careers-dashboard-container .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .careers-dashboard-container .filter-group label {
            font-size: 0.875rem !important;
            font-weight: 500 !important;
            color: #374151 !important;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        .careers-dashboard-container .filter-group input,
        .careers-dashboard-container .filter-group select {
            padding: 0.75rem !important;
            border: 1px solid #d1d5db !important;
            border-radius: 0.375rem !important;
            font-size: 0.875rem !important;
            background: white !important;
            transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        .careers-dashboard-container .filter-group input:focus,
        .careers-dashboard-container .filter-group select:focus {
            outline: none !important;
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1) !important;
        }
        .careers-dashboard-container .filter-button {
            background: #111827 !important;
            color: white !important;
            padding: 0.75rem 1.5rem !important;
            border: none !important;
            border-radius: 0.375rem !important;
            cursor: pointer !important;
            font-size: 0.875rem !important;
            font-weight: 500 !important;
            height: fit-content !important;
            text-decoration: none !important;
            transition: background-color 0.15s ease !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        .careers-dashboard-container .filter-button:hover {
            background: #374151 !important;
            color: white !important;
            text-decoration: none !important;
        }
        .careers-dashboard-container .filter-button.clear-filters {
            background: #f9fafb !important;
            color: #6b7280 !important;
            border: 1px solid #d1d5db !important;
        }
        .careers-dashboard-container .filter-button.clear-filters:hover {
            background: #f3f4f6 !important;
            color: #374151 !important;
        }
        .careers-dashboard-container .clear-filters {
            background: transparent !important;
            color: #666 !important;
            border: 1px solid #ddd !important;
            margin-left: 0.5rem;
        }
        .careers-dashboard-container .clear-filters:hover {
            background: #f5f5f5 !important;
            color: #333 !important;
            text-decoration: none !important;
        }
        
        /* Bulk Actions */
        .careers-dashboard-container .bulk-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            flex-wrap: wrap;
        }
        .careers-dashboard-container .bulk-select-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .careers-dashboard-container .bulk-select-group input[type="checkbox"] {
            margin: 0;
        }
        .careers-dashboard-container .bulk-select-group label {
            margin: 0;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .careers-dashboard-container .bulk-actions-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .careers-dashboard-container .bulk-actions select {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.875rem;
            min-width: 120px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        .careers-dashboard-container .bulk-apply-btn {
            background: #000 !important;
            color: white !important;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.875rem;
            white-space: nowrap;
            text-decoration: none !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        .careers-dashboard-container .bulk-apply-btn:hover {
            background: #333 !important;
            color: white !important;
            text-decoration: none !important;
        }
        .careers-dashboard-container .bulk-apply-btn:disabled {
            background: #ccc !important;
            cursor: not-allowed;
        }
        
        /* Results Info */
        .careers-dashboard-container .results-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            color: #666;
            font-size: 0.875rem;
        }
        
        /* Position Cards */
        .careers-dashboard-container .positions-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        /* Manage Positions Page Bottom Spacing */
        .careers-dashboard-container .jobs-table-container {
            margin-bottom: 4rem;
        }
        .careers-dashboard-container .position-card {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 4px;
            padding: 1.5rem;
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 1rem;
            align-items: center;
        }
        .careers-dashboard-container .position-checkbox {
            margin: 0;
        }
        .careers-dashboard-container .position-info {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 1fr;
            gap: 1.5rem;
            align-items: start;
        }
        .careers-dashboard-container .position-info-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        .careers-dashboard-container .position-info-label {
            font-size: 0.75rem;
            font-weight: 500;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.25rem;
        }
        .careers-dashboard-container .position-name {
            font-size: 1.125rem;
            font-weight: 500;
            color: #111;
            margin: 0 0 0.25rem 0;
        }
        .careers-dashboard-container .position-location {
            color: #666;
            font-size: 0.9rem;
        }
        .careers-dashboard-container .position-status {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .careers-dashboard-container .position-status.published {
            background: #d1fae5;
            color: #065f46;
        }
        .careers-dashboard-container .position-status.draft {
            background: #fef3c7;
            color: #92400e;
        }
        .careers-dashboard-container .position-date {
            color: #666;
            font-size: 0.9rem;
        }
        .careers-dashboard-container .position-apps {
            font-weight: 500;
            color: #059669;
        }
        .careers-dashboard-container .position-actions {
            display: flex;
            gap: 0.5rem;
        }
        .careers-dashboard-container .action-button {
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none !important;
            display: inline-block;
            transition: all 0.2s ease;
            background: #fff;
            color: #333;
            cursor: pointer;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        .careers-dashboard-container .action-button:hover {
            background: #f5f5f5;
            color: #333;
            text-decoration: none !important;
        }
        .careers-dashboard-container .action-button.primary {
            background: #000;
            color: white;
            border-color: #000;
        }
        .careers-dashboard-container .action-button.primary:hover {
            background: #333;
            color: white;
            text-decoration: none !important;
        }
        .careers-dashboard-container .action-button.danger {
            background: #dc2626;
            color: white;
            border-color: #dc2626;
        }
        .careers-dashboard-container .action-button.danger:hover {
            background: #b91c1c;
            color: white;
            text-decoration: none !important;
        }

        
        /* Empty State */
        .careers-dashboard-container .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #666;
        }
        .careers-dashboard-container .empty-state h3 {
            font-size: 1.25rem;
            font-weight: 500;
            color: #111;
            margin: 0 0 0.5rem 0;
        }
        .careers-dashboard-container .empty-state p {
            margin: 0 0 2rem 0;
        }
        
        @media (max-width: 1024px) {
            .careers-dashboard-container .filters-grid {
                grid-template-columns: 1fr 1fr;
                gap: 1rem;
            }
            .careers-dashboard-container .position-card {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            .careers-dashboard-container .position-info {
                display: block;
            }
            .careers-dashboard-container .position-info-item {
                margin-bottom: 1rem;
                padding-bottom: 0.75rem;
                border-bottom: 1px solid #f0f0f0;
            }
            .careers-dashboard-container .position-info-item:last-child {
                border-bottom: none;
                margin-bottom: 0;
            }
        }
        @media (max-width: 768px) {
            .careers-dashboard-container .form-grid {
                grid-template-columns: 1fr;
            }
            .careers-dashboard-container .form-actions {
                flex-direction: column;
            }
            .careers-dashboard-container .header-actions {
                flex-direction: column;
                gap: 0.5rem;
            }
            .careers-dashboard-container .filters-grid {
                grid-template-columns: 1fr;
            }
            .careers-dashboard-container .bulk-actions {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            .careers-dashboard-container .bulk-actions-group {
                width: 100%;
                justify-content: space-between;
            }
            .careers-dashboard-container .results-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }
        </style>
        
        <?php $this->render_dashboard_navigation('manage_jobs'); ?>
        
        <div class="careers-dashboard-container">
            <div class="dashboard-content">
                <div class="page-header">
                    <div class="page-header-content">
                        <div class="page-header-text">
                            <h1 class="page-title">Manage Positions</h1>
                            <p class="page-subtitle">Create, edit, and manage job positions</p>
                        </div>
                        <div class="page-actions">
                            <a href="<?php echo CareersSettings::get_page_url('create_job'); ?>" class="create-position-btn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 4v16m8-8H4"/>
                                </svg>
                                Create Position
                            </a>
                        </div>
                </div>
            </div>
            
            <!-- Filters Section -->
            <div class="filters-section">
                <form method="GET" action="" id="filters-form">
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label for="search">Search Jobs</label>
                            <input type="text" id="search" name="search" value="<?php echo esc_attr($search_query); ?>" 
                                   placeholder="Search by job title...">
                        </div>
                        <div class="filter-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="published" <?php selected($status_filter, 'published'); ?>>Published</option>
                                <option value="draft" <?php selected($status_filter, 'draft'); ?>>Draft</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="job_type">Job Type</label>
                            <select id="job_type" name="job_type">
                                <option value="">All Types</option>
                                <?php foreach ($job_types as $type): ?>
                                    <option value="<?php echo esc_attr($type); ?>" <?php selected($job_type_filter, $type); ?>>
                                        <?php echo esc_html($type); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="location">Location</label>
                            <select id="location" name="location">
                                <option value="">All Locations</option>
                                <?php foreach ($locations_by_state as $state => $cities): ?>
                                    <optgroup label="<?php echo esc_attr($state); ?>">
                                        <?php foreach ($cities as $location): ?>
                                            <option value="<?php echo esc_attr($location->city . ', ' . $location->state); ?>" <?php selected($location_filter, $location->city . ', ' . $location->state); ?>>
                                                <?php echo esc_html($location->city); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <button type="submit" class="filter-button">Filter</button>
                            <a href="<?php echo CareersSettings::get_page_url('manage_jobs'); ?>" class="filter-button clear-filters">Clear</a>
                        </div>
                    </div>
                </form>
            </div>
            
            <?php if (empty($positions)): ?>
                <div class="empty-state">
                    <h3><?php echo $search_query || $status_filter || $job_type_filter || $location_filter ? 'No positions found' : 'No positions yet'; ?></h3>
                    <p><?php echo $search_query || $status_filter || $job_type_filter || $location_filter ? 'Try adjusting your filters.' : 'Create your first job position to get started.'; ?></p>
                    <?php if (!$search_query && !$status_filter && !$job_type_filter && !$location_filter): ?>
                        <a href="<?php echo CareersSettings::get_page_url('create_job'); ?>" class="create-button">
                            Create Your First Position
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Bulk Actions -->
                <div class="bulk-actions">
                    <div class="bulk-select-group">
                        <input type="checkbox" id="select-all" class="position-checkbox">
                        <label for="select-all">Select All</label>
                    </div>
                    <div class="bulk-actions-group">
                        <select id="bulk-action">
                            <option value="">Bulk Actions</option>
                            <option value="publish">Publish</option>
                            <option value="draft">Move to Draft</option>
                            <option value="delete">Delete</option>
                        </select>
                        <button type="button" class="bulk-apply-btn" id="apply-bulk-action" disabled>Apply</button>
                    </div>
                </div>
                
                <!-- Results Info -->
                <div class="results-info">
                    <span>Showing <?php echo count($positions); ?> of <?php echo $total_positions; ?> positions</span>
                    <span>Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                </div>
                
                <!-- Positions Table -->
                <div class="jobs-table-container">
                    <!-- Table Header -->
                    <div class="jobs-table-header">
                        <div class="table-header-cell select-col">
                            <input type="checkbox" id="select-all-positions" class="position-checkbox">
                        </div>
                        <div class="table-header-cell job-title-col">Job Title</div>
                        <div class="table-header-cell location-col">Location</div>
                        <div class="table-header-cell status-col">Status</div>
                        <div class="table-header-cell employment-col">Type</div>
                        <div class="table-header-cell posted-col">Posted</div>
                        <div class="table-header-cell applications-col">Apps</div>
                        <div class="table-header-cell actions-col">Actions</div>
                    </div>
                    
                    <!-- Positions List -->
                    <div class="jobs-list">
                    <?php foreach ($positions as $position): ?>
                            <div class="job-row">
                                <div class="job-cell select-col">
                            <input type="checkbox" class="position-checkbox" value="<?php echo esc_attr($position->id); ?>">
                                </div>
                                <div class="job-cell job-title-col">
                                    <h3 class="job-title"><?php echo esc_html($position->position_name); ?></h3>
                                </div>
                                <div class="job-cell location-col" data-label="Location">
                                    <div class="location-info">
                                        <svg class="location-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                            <circle cx="12" cy="10" r="3"/>
                                        </svg>
                                        <?php echo esc_html($position->location); ?>
                                    </div>
                                </div>
                                <div class="job-cell status-col" data-label="Status">
                                    <span class="status-badge <?php echo esc_attr($position->status); ?>">
                                        <?php echo esc_html(ucfirst($position->status)); ?>
                                    </span>
                                </div>
                                <div class="job-cell employment-col" data-label="Employment Type">
                                    <?php if (!empty($position->job_type)): ?>
                                        <span class="employment-badge"><?php echo esc_html($position->job_type); ?></span>
                                    <?php else: ?>
                                        <span class="employment-badge not-specified">Not specified</span>
                                    <?php endif; ?>
                                </div>
                                <div class="job-cell posted-col" data-label="Posted Date">
                                    <span class="posted-date"><?php echo esc_html(date('M j, Y', strtotime($position->created_at))); ?></span>
                                    </div>
                                <div class="job-cell applications-col" data-label="Applications">
                                    <span class="applications-count">
                                        <?php echo esc_html(CareersApplicationDB::get_applications_count_by_job($position->id)); ?>
                                    </span>
                                    </div>
                                    <div class="job-actions">
                                        <a href="<?php echo CareersSettings::get_page_url('edit_job', array('id' => $position->id)); ?>" 
                                           class="action-icon edit-icon" 
                                           title="Edit Position"
                                           aria-label="Edit <?php echo esc_attr($position->position_name); ?>">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                                <path d="m18.5 2.5 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                            </svg>
                                        </a>
                                        <a href="<?php echo CareersSettings::get_page_url('applications', array('job_id' => $position->id)); ?>" 
                                           class="action-icon applications-icon" 
                                           title="View Applications"
                                           aria-label="View applications for <?php echo esc_attr($position->position_name); ?>">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                                <circle cx="9" cy="7" r="4"/>
                                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                            </svg>
                                        </a>
                                        <a href="<?php echo esc_url(careers_get_job_permalink($position->id)); ?>" 
                                           class="action-icon view-icon" 
                                           title="View Job Posting" 
                                           target="_blank"
                                           aria-label="View job posting for <?php echo esc_attr($position->position_name); ?>">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                        </a>
                                        <a href="#" class="action-icon delete-icon delete-position" 
                                           data-id="<?php echo esc_attr($position->id); ?>"
                                           title="Delete Position"
                                           aria-label="Delete <?php echo esc_attr($position->position_name); ?>">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M3 6h18"/>
                                                <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                                                <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                            </svg>
                                        </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="<?php echo esc_url(add_query_arg('paged', $page - 1)); ?>">&laquo; Previous</a>
                        <?php else: ?>
                            <span class="disabled">&laquo; Previous</span>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="<?php echo esc_url(add_query_arg('paged', $i)); ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="<?php echo esc_url(add_query_arg('paged', $page + 1)); ?>">Next &raquo;</a>
                        <?php else: ?>
                            <span class="disabled">Next &raquo;</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Select all checkbox functionality
            $('#select-all, #select-all-positions').on('change', function() {
                $('.position-checkbox').not(this).prop('checked', this.checked);
                toggleBulkActions();
            });
            
            // Individual checkbox functionality
            $('.position-checkbox').on('change', function() {
                if (!this.checked) {
                    $('#select-all').prop('checked', false);
                }
                toggleBulkActions();
            });
            
            // Toggle bulk actions button
            function toggleBulkActions() {
                var checkedCount = $('.position-checkbox:checked').not('#select-all').length;
                $('#apply-bulk-action').prop('disabled', checkedCount === 0);
            }
            
            // Bulk actions handler
            $('#apply-bulk-action').on('click', function() {
                var action = $('#bulk-action').val();
                var selectedIds = [];
                
                $('.position-checkbox:checked').not('#select-all').each(function() {
                    selectedIds.push($(this).val());
                });
                
                if (!action || selectedIds.length === 0) {
                    alert('Please select an action and at least one position.');
                    return;
                }
                
                if (action === 'delete' && !confirm('Are you sure you want to delete the selected positions?')) {
                    return;
                }
                
                $.ajax({
                    url: careers_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'careers_bulk_position_action',
                        bulk_action: action,
                        position_ids: selectedIds,
                        nonce: careers_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('Error performing bulk action. Please try again.');
                    }
                });
            });
            
            // Delete position handler
            $('.delete-position').on('click', function(e) {
                e.preventDefault();
                
                if (!confirm('Are you sure you want to delete this position?')) {
                    return;
                }
                
                var positionId = $(this).data('id');
                var card = $(this).closest('.position-card');
                
                $.ajax({
                    url: careers_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'careers_position_action',
                        position_action: 'delete',
                        position_id: positionId,
                        nonce: careers_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            card.fadeOut();
                        } else {
                            alert('Error: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('Error deleting position. Please try again.');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Render location management
     */
    public function render_location_management() {
        $locations_by_state = CareersPositionsDB::get_locations_by_state();
        
        ?>
        <style>
        .careers-dashboard-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 2rem 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            color: #333;
            font-size: 16px !important;
        }
        .careers-dashboard-container * {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        /* High specificity CSS overrides to fix font and underline issues */
        .careers-dashboard-container,
        .careers-dashboard-container * {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            font-size: 16px !important;
            line-height: 1.5 !important;
        }
        
        /* Remove all underlines and text decorations with maximum specificity */
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
        .careers-dashboard-container .create-button:focus,
        .careers-dashboard-container .button,
        .careers-dashboard-container .button:link,
        .careers-dashboard-container .button:visited,
        .careers-dashboard-container .button:hover,
        .careers-dashboard-container .button:active,
        .careers-dashboard-container .button:focus {
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
        
        .careers-dashboard-container h1,
        .careers-dashboard-container h2,
        .careers-dashboard-container h3,
        .careers-dashboard-container h4,
        .careers-dashboard-container h5,
        .careers-dashboard-container h6 {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            font-weight: 500 !important;
        }
        .careers-dashboard-container p,
        .careers-dashboard-container span,
        .careers-dashboard-container div {
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
        }
        .careers-dashboard-container .dashboard-subtitle {
            color: #666 !important;
            margin: 0 !important;
            font-size: 1rem !important;
        }
        
        .careers-dashboard-container .form-row {
            margin-bottom: 2rem;
        }
        .careers-dashboard-container .form-row label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #111;
            font-size: 1rem;
        }
        .careers-dashboard-container .form-row input, 
        .careers-dashboard-container .form-row textarea, 
        .careers-dashboard-container .form-row select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            box-sizing: border-box;
            transition: border-color 0.2s ease;
            background: #fff;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        .careers-dashboard-container .form-row input:focus, 
        .careers-dashboard-container .form-row textarea:focus, 
        .careers-dashboard-container .form-row select:focus {
            outline: none;
            border-color: #333;
            box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.05);
        }
        .careers-dashboard-container .form-row small {
            display: block;
            margin-top: 0.25rem;
            color: #666;
            font-size: 0.875rem;
        }
        .careers-dashboard-container .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .careers-dashboard-container .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #eee;
        }
        .careers-dashboard-container .button {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem !important;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none !important;
            text-align: center;
            transition: background 0.2s ease;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        .careers-dashboard-container .button-primary {
            background: #000 !important;
            color: white !important;
        }
        .careers-dashboard-container .button-primary:hover {
            background: #333 !important;
            color: white !important;
            text-decoration: none !important;
        }
        .careers-dashboard-container .button:not(.button-primary) {
            background: #f5f5f5 !important;
            color: #333 !important;
            border: 1px solid #ddd !important;
        }
        .careers-dashboard-container .button:not(.button-primary):hover {
            background: #e8e8e8 !important;
            color: #333 !important;
            text-decoration: none !important;
        }
        .careers-dashboard-container .checkbox-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .careers-dashboard-container .checkbox-row input[type="checkbox"] {
            width: auto;
            margin: 0;
        }
        
        /* Location Management Specific Styles */
        .careers-dashboard-container .header-actions {
            display: flex;
            gap: 1rem;
        }
        .careers-dashboard-container .dashboard-action-btn {
            background: #f5f5f5 !important;
            color: #333 !important;
            padding: 0.75rem 1.5rem !important;
            border: 1px solid #ddd !important;
            border-radius: 4px !important;
            font-size: 1rem !important;
            font-weight: 500 !important;
            text-decoration: none !important;
            display: inline-block !important;
            transition: background 0.2s ease !important;
            cursor: pointer !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        .careers-dashboard-container .dashboard-action-btn:hover {
            background: #e8e8e8 !important;
            color: #333 !important;
            text-decoration: none !important;
        }
        .careers-dashboard-container .location-form-card {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 4px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .careers-dashboard-container .location-form-card h3 {
            font-size: 1.25rem !important;
            font-weight: 500 !important;
            color: #111;
            margin: 0 0 1.5rem 0 !important;
        }
        .careers-dashboard-container .location-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 1rem;
            align-items: end;
        }
        .careers-dashboard-container .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .careers-dashboard-container .form-group label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #333;
        }
        .careers-dashboard-container .form-group input {
            padding: 0.75rem 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            box-sizing: border-box;
            transition: border-color 0.2s ease;
            background: #fff;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        .careers-dashboard-container .form-group input:focus {
            outline: none;
            border-color: #333;
            box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.05);
        }
        .careers-dashboard-container .add-location-btn {
            background: #000 !important;
            color: white !important;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem !important;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s ease;
            height: fit-content;
            text-decoration: none !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        .careers-dashboard-container .add-location-btn:hover {
            background: #333 !important;
            color: white !important;
            text-decoration: none !important;
        }
        .careers-dashboard-container .locations-grid {
            display: grid;
            gap: 1rem;
        }
        .careers-dashboard-container .state-card {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 4px;
            overflow: hidden;
        }
        .careers-dashboard-container .state-header {
            background: #f8f9fa;
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
        }
        .careers-dashboard-container .state-title {
            font-size: 1.125rem !important;
            font-weight: 500 !important;
            color: #111;
            margin: 0 !important;
        }
        .careers-dashboard-container .state-count {
            font-size: 0.875rem;
            color: #666;
            margin-top: 0.25rem;
        }
        .careers-dashboard-container .cities-grid {
            display: grid;
            gap: 0;
        }
        .careers-dashboard-container .city-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.2s ease;
        }
        .careers-dashboard-container .city-item:last-child {
            border-bottom: none;
        }
        .careers-dashboard-container .city-item:hover {
            background: #f8f9fa;
        }
        .careers-dashboard-container .city-name {
            font-size: 0.875rem;
            color: #333;
            font-weight: 500;
        }
        .careers-dashboard-container .delete-location {
            background: #dc2626 !important;
            color: white !important;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 500;
            transition: background 0.2s ease;
            text-decoration: none !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        .careers-dashboard-container .delete-location:hover {
            background: #b91c1c !important;
            color: white !important;
            text-decoration: none !important;
        }
        .careers-dashboard-container .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #666;
            background: #fff;
            border: 1px solid #eee;
            border-radius: 4px;
        }
        .careers-dashboard-container .empty-state h3 {
            font-size: 1.25rem !important;
            font-weight: 500 !important;
            color: #111;
            margin: 0 0 0.5rem 0 !important;
        }
        .careers-dashboard-container .empty-state p {
            margin: 0 0 2rem 0 !important;
            font-size: 0.875rem;
        }
        .careers-dashboard-container .create-first-btn {
            background: #000 !important;
            color: white !important;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem !important;
            font-weight: 500;
            text-decoration: none !important;
            display: inline-block;
            transition: background 0.2s ease;
            cursor: pointer;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        .careers-dashboard-container .create-first-btn:hover {
            background: #333 !important;
            color: white !important;
            text-decoration: none !important;
        }
        .careers-dashboard-container .locations-section {
            margin-top: 2rem;
        }
        .careers-dashboard-container .section-title {
            font-size: 1.25rem !important;
            font-weight: 500 !important;
            color: #111;
            margin: 0 0 1.5rem 0 !important;
        }
        .careers-dashboard-container .locations-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        /* Locations Page Bottom Spacing */
        .careers-dashboard-container .locations-section {
            margin-bottom: 4rem;
        }
        .careers-dashboard-container .stat-card {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 4px;
            padding: 1.5rem;
            text-align: center;
        }
        .careers-dashboard-container .stat-number {
            font-size: 2rem !important;
            font-weight: 500 !important;
            color: #111;
            margin: 0 0 0.5rem 0 !important;
        }
        .careers-dashboard-container .stat-label {
            font-size: 0.875rem;
            color: #666;
            margin: 0 !important;
        }
        
        @media (max-width: 1024px) {
            .careers-dashboard-container .location-form-grid {
                grid-template-columns: 1fr 1fr;
                gap: 1rem;
            }
            .careers-dashboard-container .add-location-btn {
                grid-column: 1 / -1;
                justify-self: start;
            }
        }
        @media (max-width: 768px) {
            .careers-dashboard-container .form-grid {
                grid-template-columns: 1fr;
            }
            .careers-dashboard-container .header-actions {
                flex-direction: column;
                gap: 0.5rem;
                width: 100%;
            }
            .careers-dashboard-container .dashboard-action-btn {
                text-align: center;
            }
            .careers-dashboard-container .location-form-grid {
                grid-template-columns: 1fr;
            }
            .careers-dashboard-container .locations-stats {
                grid-template-columns: 1fr;
            }
            .careers-dashboard-container .city-item {
                padding: 0.75rem 1rem;
            }
        }
        </style>
        
        <?php $this->render_dashboard_navigation('locations'); ?>
        
        <div class="careers-dashboard-container">
            <div class="dashboard-content">
                <div class="page-header">
                    <div class="page-header-content">
                        <div class="page-header-text">
                            <h1 class="page-title">Manage Locations</h1>
                            <p class="page-subtitle">Add and manage job locations</p>
                        </div>
                    </div>
                </div>
            
            <?php if (!empty($locations_by_state)): ?>
                <div class="locations-stats">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count($locations_by_state); ?></div>
                        <div class="stat-label">States</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">
                            <?php 
                            $total_cities = 0;
                            foreach ($locations_by_state as $cities) {
                                $total_cities += count($cities);
                            }
                            echo $total_cities;
                            ?>
                        </div>
                        <div class="stat-label">Cities</div>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="location-form-card">
                <h3>Add New Location</h3>
                <form id="add-location-form">
                    <?php wp_nonce_field('careers_location_action', 'careers_nonce'); ?>
                    <div class="location-form-grid">
                        <div class="form-group">
                            <label for="location_state">State *</label>
                            <input type="text" id="location_state" name="location_state" 
                                   placeholder="e.g. Texas" required>
                        </div>
                        <div class="form-group">
                            <label for="location_city">City *</label>
                            <input type="text" id="location_city" name="location_city" 
                                   placeholder="e.g. Dallas" required>
                        </div>
                        <div>
                            <button type="submit" class="button button-primary">Add Location</button>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="locations-section">
                <h3 class="section-title">Existing Locations</h3>
                
                <?php if (empty($locations_by_state)): ?>
                    <div class="empty-state">
                        <h3>No locations added yet</h3>
                        <p>Add your first location above to get started with job postings.</p>
                    </div>
                <?php else: ?>
                    <!-- Locations Table -->
                    <div class="jobs-table-container">
                        <!-- Table Header -->
                        <div class="jobs-table-header locations-table-header">
                            <div class="table-header-cell state-col">State</div>
                            <div class="table-header-cell city-col">City</div>
                            <div class="table-header-cell location-actions-col">Actions</div>
                                </div>
                        
                        <!-- Locations List -->
                        <div class="jobs-list">
                            <?php foreach ($locations_by_state as $state => $cities): ?>
                                    <?php foreach ($cities as $location): ?>
                                    <div class="job-row">
                                        <div class="job-cell state-col" data-label="State">
                                            <span class="state-name"><?php echo esc_html($state); ?></span>
                                        </div>
                                        <div class="job-cell city-col" data-label="City">
                                            <span class="city-name"><?php echo esc_html($location->city); ?></span>
                                        </div>
                                        <div class="job-cell location-actions-col">
                                            <div class="job-actions">
                                                <button class="action-icon delete-icon delete-location" 
                                                        data-id="<?php echo esc_attr($location->id); ?>"
                                                        title="Delete Location"
                                                        aria-label="Delete <?php echo esc_attr($location->city); ?>, <?php echo esc_attr($state); ?>">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M3 6h18"/>
                                                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                                                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                                    </svg>
                                            </button>
                                        </div>
                                </div>
                            </div>
                                <?php endforeach; ?>
                        <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Add location
            $('#add-location-form').on('submit', function(e) {
                e.preventDefault();
                
                var state = $('#location_state').val().trim();
                var city = $('#location_city').val().trim();
                
                if (!state || !city) {
                    alert('Please enter both state and city.');
                    return;
                }
                
                $.ajax({
                    url: careers_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'careers_location_action',
                        location_action: 'add',
                        location_state: state,
                        location_city: city,
                        nonce: careers_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('Error adding location. Please try again.');
                    }
                });
            });
            
            // Delete location
            $(document).on('click', '.delete-location', function() {
                if (!confirm('Are you sure you want to delete this location?')) {
                    return;
                }
                
                var locationId = $(this).data('id');
                var cityItem = $(this).closest('.city-item');
                
                $.ajax({
                    url: careers_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'careers_location_action',
                        location_action: 'delete',
                        location_id: locationId,
                        nonce: careers_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            cityItem.fadeOut(300, function() {
                                location.reload(); // Reload to update state counts
                            });
                        } else {
                            alert('Error: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('Error deleting location. Please try again.');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Handle AJAX position actions
     */
    public function handle_position_action() {
        // Debug: log what we're receiving
        error_log('Position action called. POST data: ' . print_r($_POST, true));
        
        // Verify nonce - check both possible nonce keys
        $nonce = $_POST['careers_nonce'] ?? $_POST['nonce'] ?? '';
        if (!wp_verify_nonce($nonce, 'careers_position_action') && !wp_verify_nonce($nonce, 'careers_nonce')) {
            error_log('Nonce verification failed. Nonce: ' . $nonce);
            wp_die('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options') && !current_user_can('career_admin')) {
            wp_die('Permission denied');
        }
        
        // Check for operation (from forms) or position_action (from AJAX delete)
        $operation = sanitize_text_field($_POST['operation'] ?? $_POST['position_action'] ?? '');
        
        switch ($operation) {
            case 'create':
                $this->handle_create_position();
                break;
                
            case 'update':
                $this->handle_update_position();
                break;
                
            case 'delete':
                $this->handle_delete_position();
                break;
        }
        
        wp_die();
    }
    
    /**
     * Handle create position
     */
    private function handle_create_position() {
        $data = array(
            'position_name' => sanitize_text_field($_POST['position_name']),
            'location' => sanitize_text_field($_POST['location']),
            'job_type' => sanitize_text_field($_POST['job_type']),
            'salary_range' => sanitize_text_field($_POST['salary_range']),
            'schedule_type' => sanitize_text_field($_POST['schedule_type']),
            'experience_level' => sanitize_text_field($_POST['experience_level']),
            'certification_required' => sanitize_text_field($_POST['certification_required']),
            'position_overview' => wp_kses_post($_POST['position_overview']),
            'responsibilities' => sanitize_textarea_field($_POST['responsibilities']),
            'requirements' => sanitize_textarea_field($_POST['requirements']),
            'equipment' => sanitize_textarea_field($_POST['equipment']),
            'benefits' => sanitize_textarea_field($_POST['benefits']),
            'license_info' => wp_kses_post($_POST['license_info']),
            'has_vehicle' => isset($_POST['has_vehicle']) ? 1 : 0,
            'vehicle_description' => sanitize_textarea_field($_POST['vehicle_description']),
            'status' => sanitize_text_field($_POST['status'])
        );
        
        $result = CareersPositionsDB::insert_position($data);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success('Position created successfully');
        }
    }
    
    /**
     * Handle update position
     */
    private function handle_update_position() {
        $position_id = intval($_POST['position_id']);
        
        $data = array(
            'position_name' => sanitize_text_field($_POST['position_name']),
            'location' => sanitize_text_field($_POST['location']),
            'job_type' => sanitize_text_field($_POST['job_type']),
            'salary_range' => sanitize_text_field($_POST['salary_range']),
            'schedule_type' => sanitize_text_field($_POST['schedule_type']),
            'experience_level' => sanitize_text_field($_POST['experience_level']),
            'certification_required' => sanitize_text_field($_POST['certification_required']),
            'position_overview' => wp_kses_post($_POST['position_overview']),
            'responsibilities' => sanitize_textarea_field($_POST['responsibilities']),
            'requirements' => sanitize_textarea_field($_POST['requirements']),
            'equipment' => sanitize_textarea_field($_POST['equipment']),
            'benefits' => sanitize_textarea_field($_POST['benefits']),
            'license_info' => wp_kses_post($_POST['license_info']),
            'has_vehicle' => isset($_POST['has_vehicle']) ? 1 : 0,
            'vehicle_description' => sanitize_textarea_field($_POST['vehicle_description']),
            'status' => sanitize_text_field($_POST['status'])
        );
        
        $result = CareersPositionsDB::update_position($position_id, $data);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success('Position updated successfully');
        }
    }
    
    /**
     * Handle delete position
     */
    private function handle_delete_position() {
        $position_id = intval($_POST['position_id']);
        
        $result = CareersPositionsDB::delete_position($position_id);
        
        if ($result) {
            wp_send_json_success('Position deleted successfully');
        } else {
            wp_send_json_error('Failed to delete position');
        }
    }
    
    /**
     * Handle AJAX location actions
     */
    public function handle_location_action() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['careers_nonce'], 'careers_location_action')) {
            wp_die('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options') && !current_user_can('career_admin')) {
            wp_die('Permission denied');
        }
        
        $location_action = sanitize_text_field($_POST['location_action']);
        
        switch ($location_action) {
            case 'add':
                $state = sanitize_text_field($_POST['location_state']);
                $city = sanitize_text_field($_POST['location_city']);
                $result = CareersPositionsDB::insert_location($state, $city);
                
                if (is_wp_error($result)) {
                    wp_send_json_error($result->get_error_message());
                } else {
                    wp_send_json_success('Location added successfully');
                }
                break;
                
            case 'delete':
                $location_id = intval($_POST['location_id']);
                $result = CareersPositionsDB::delete_location($location_id);
                
                if ($result) {
                    wp_send_json_success('Location deleted successfully');
                } else {
                    wp_send_json_error('Failed to delete location');
                }
                break;
        }
        
        wp_die();
    }
    
    /**
     * Handle bulk position actions
     */
    public function handle_bulk_position_action() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'careers_nonce')) {
            wp_send_json_error('Invalid nonce');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $action = sanitize_text_field($_POST['bulk_action']);
        $position_ids = array_map('intval', $_POST['position_ids']);
        
        if (empty($action) || empty($position_ids)) {
            wp_send_json_error('Missing action or position IDs');
        }
        
        switch ($action) {
            case 'publish':
                $result = CareersPositionsDB::bulk_update_status($position_ids, 'published');
                if ($result !== false) {
                    wp_send_json_success('Positions published successfully');
                } else {
                    wp_send_json_error('Failed to publish positions');
                }
                break;
                
            case 'draft':
                $result = CareersPositionsDB::bulk_update_status($position_ids, 'draft');
                if ($result !== false) {
                    wp_send_json_success('Positions moved to draft successfully');
                } else {
                    wp_send_json_error('Failed to move positions to draft');
                }
                break;
                
            case 'delete':
                $result = CareersPositionsDB::bulk_delete_positions($position_ids);
                if ($result !== false) {
                    wp_send_json_success('Positions deleted successfully');
                } else {
                    wp_send_json_error('Failed to delete positions');
                }
                break;
                
            default:
                wp_send_json_error('Invalid bulk action');
        }
        
        wp_die();
    }
    
    /**
     * Job form shortcode for admin use
     */
    public function job_form_shortcode($atts) {
        // Check if user has permissions
        if (!current_user_can('manage_options') && !current_user_can('career_admin')) {
            return '<p>You do not have permission to access this form.</p>';
        }
        
        // Get edit ID if provided
        $edit_id = get_query_var('careers_id', '');
        
        ob_start();
        
        if ($edit_id) {
            $this->render_position_edit_form($edit_id);
        } else {
            $this->render_position_creation_form();
        }
        
        return ob_get_clean();
    }
    
    /**
     * Render applications management
     */
    public function render_applications_management() {
        // Get filter parameters
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        $job_filter = isset($_GET['job_id']) ? intval($_GET['job_id']) : '';
        $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page = 20;
        
        // Build query arguments
        $args = array(
            'limit' => $per_page,
            'offset' => ($page - 1) * $per_page,
            'orderby' => 'submitted_at',
            'order' => 'DESC'
        );
        
        if (!empty($status_filter)) {
            $args['status'] = $status_filter;
        }
        
        if (!empty($job_filter)) {
            $args['job_id'] = $job_filter;
        }
        
        // Get applications and total count
        $applications = CareersApplicationDB::get_applications($args);
        $total_args = $args;
        unset($total_args['limit'], $total_args['offset']);
        $total_applications = $this->get_applications_count($total_args);
        
        // Get available jobs for filtering
        $available_jobs = CareersPositionsDB::get_positions(array('status' => 'published', 'limit' => -1));
        
        // Get application statistics
        $stats = CareersApplicationDB::get_stats();
        
        // Status pipeline definition
        $status_pipeline = array(
            'new' => array('label' => 'New', 'color' => '#3b82f6'),
            'under_review' => array('label' => 'Under Review', 'color' => '#f59e0b'),
            'contacted' => array('label' => 'Contacted', 'color' => '#8b5cf6'),
            'interview' => array('label' => 'Interview', 'color' => '#06b6d4'),
            'hired' => array('label' => 'Hired', 'color' => '#10b981'),
            'rejected' => array('label' => 'Rejected', 'color' => '#ef4444')
        );
        
        // Pagination calculations
        $total_pages = ceil($total_applications / $per_page);
        
        ?>
        <style>
        .careers-dashboard-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 2rem 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            color: #333;
            font-size: 16px !important;
        }
        .careers-dashboard-container * {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        /* High specificity CSS overrides to fix font and underline issues */
        .careers-dashboard-container,
        .careers-dashboard-container * {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            font-size: 16px !important;
            line-height: 1.5 !important;
        }
        
        /* Remove all underlines and text decorations with maximum specificity */
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
        .careers-dashboard-container button:focus {
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
        
        .careers-dashboard-container h1,
        .careers-dashboard-container h2,
        .careers-dashboard-container h3,
        .careers-dashboard-container h4,
        .careers-dashboard-container h5,
        .careers-dashboard-container h6 {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            font-weight: 500 !important;
        }
        .careers-dashboard-container p,
        .careers-dashboard-container span,
        .careers-dashboard-container div {
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
        }
        .careers-dashboard-container .dashboard-subtitle {
            color: #666 !important;
            margin: 0 !important;
            font-size: 1rem !important;
        }
        .careers-dashboard-container .header-actions {
            margin-top: 1rem;
        }
        .careers-dashboard-container .delete-all-btn {
            background: #dc3545 !important;
            color: white !important;
            border: none !important;
            padding: 0.75rem 1.5rem !important;
            border-radius: 6px !important;
            font-size: 0.875rem !important;
            cursor: pointer !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            text-decoration: none !important;
            transition: all 0.2s ease !important;
        }
        .careers-dashboard-container .delete-all-btn:hover {
            background: #c82333 !important;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(220, 53, 69, 0.2);
        }
        
        /* Applications Stats */
        .careers-dashboard-container .applications-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        /* Applications Page without pagination bottom spacing */
        .careers-dashboard-container .empty-state {
            margin-bottom: 4rem;
        }
        .careers-dashboard-container .stat-card {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
        }
        .careers-dashboard-container .stat-number {
            font-size: 2rem !important;
            font-weight: 600 !important;
            margin: 0 0 0.5rem 0 !important;
        }
        .careers-dashboard-container .stat-label {
            font-size: 0.875rem !important;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .careers-dashboard-container .stat-card.new .stat-number { color: #3b82f6; }
        .careers-dashboard-container .stat-card.under-review .stat-number { color: #f59e0b; }
        .careers-dashboard-container .stat-card.contacted .stat-number { color: #8b5cf6; }
        .careers-dashboard-container .stat-card.interview .stat-number { color: #06b6d4; }
        .careers-dashboard-container .stat-card.hired .stat-number { color: #10b981; }
        .careers-dashboard-container .stat-card.rejected .stat-number { color: #ef4444; }
        
        /* Filter Section */
        .careers-dashboard-container .filters-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        .careers-dashboard-container .filters-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 1rem;
            align-items: end;
        }
        .careers-dashboard-container .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .careers-dashboard-container .filter-group label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #555;
        }
        .careers-dashboard-container .filter-group input,
        .careers-dashboard-container .filter-group select {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.875rem;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        .careers-dashboard-container .filter-button {
            background: #000 !important;
            color: white !important;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.875rem;
            height: fit-content;
            text-decoration: none !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        .careers-dashboard-container .filter-button:hover {
            background: #333 !important;
            text-decoration: none !important;
        }
        .careers-dashboard-container .clear-filters {
            background: transparent !important;
            color: #666 !important;
            border: 1px solid #ddd !important;
            margin-left: 0.5rem;
        }
        .careers-dashboard-container .clear-filters:hover {
            background: #f5f5f5 !important;
            color: #333 !important;
        }
        
        /* Applications List */
        .careers-dashboard-container .applications-grid {
            display: grid;
            gap: 1rem;
        }
        .careers-dashboard-container .application-card {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 1.5rem;
            transition: all 0.2s ease;
        }
        .careers-dashboard-container .application-card:hover {
            border-color: #ddd;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .careers-dashboard-container .application-header {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 1rem;
            align-items: start;
            margin-bottom: 1rem;
        }
        .careers-dashboard-container .applicant-info h3 {
            font-size: 1.125rem !important;
            font-weight: 600 !important;
            margin: 0 0 0.25rem 0 !important;
            color: #111;
        }
        .careers-dashboard-container .applicant-email {
            color: #666;
            font-size: 0.875rem;
        }
        .careers-dashboard-container .application-meta {
            font-size: 0.875rem;
            color: #666;
            margin-top: 0.5rem;
        }
        
        .careers-dashboard-container .application-details {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 1.5rem;
            margin: 1rem 0;
        }
        .careers-dashboard-container .detail-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        .careers-dashboard-container .detail-label {
            font-size: 0.75rem;
            font-weight: 500;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .careers-dashboard-container .detail-value {
            font-size: 0.875rem;
            color: #333;
        }
        .careers-dashboard-container .job-title {
            font-weight: 500;
            color: #111;
        }
        .careers-dashboard-container .general-application {
            color: #f59e0b;
            font-style: italic;
        }
        
        /* Status Pipeline */
        .careers-dashboard-container .status-pipeline {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin: 1rem 0;
        }
        .careers-dashboard-container .status-btn {
            padding: 0.375rem 0.75rem;
            border: 1px solid #ddd;
            border-radius: 20px;
            font-size: 0.75rem !important;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            background: #fff;
            color: #666;
            text-decoration: none !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        .careers-dashboard-container .status-btn:hover {
            border-color: #999;
            text-decoration: none !important;
        }
        .careers-dashboard-container .status-btn.active {
            color: white !important;
            border-color: transparent;
        }
        .careers-dashboard-container .status-btn.new.active { background: #3b82f6 !important; }
        .careers-dashboard-container .status-btn.under-review.active { background: #f59e0b !important; }
        .careers-dashboard-container .status-btn.contacted.active { background: #8b5cf6 !important; }
        .careers-dashboard-container .status-btn.interview.active { background: #06b6d4 !important; }
        .careers-dashboard-container .status-btn.hired.active { background: #10b981 !important; }
        .careers-dashboard-container .status-btn.rejected.active { background: #ef4444 !important; }
        
        /* Notes Section */
        .careers-dashboard-container .notes-section {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #f0f0f0;
        }
        .careers-dashboard-container .notes-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        .careers-dashboard-container .notes-title {
            font-size: 0.875rem !important;
            font-weight: 600 !important;
            color: #333;
            margin: 0 !important;
        }
        .careers-dashboard-container .add-note-btn {
            font-size: 0.75rem !important;
            padding: 0.25rem 0.5rem;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        .careers-dashboard-container .add-note-btn:hover {
            background: #e9ecef;
            text-decoration: none !important;
        }
        
        .careers-dashboard-container .notes-list {
            display: grid;
            gap: 0.5rem;
        }
        .careers-dashboard-container .note-item {
            background: #f8f9fa;
            padding: 0.75rem;
            border-radius: 4px;
            font-size: 0.875rem;
        }
        .careers-dashboard-container .note-meta {
            font-size: 0.75rem;
            color: #666;
            margin-top: 0.25rem;
        }
        .careers-dashboard-container .note-form {
            margin-top: 0.5rem;
            display: none;
        }
        .careers-dashboard-container .note-form.active {
            display: block;
        }
        .careers-dashboard-container .note-textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.875rem;
            resize: vertical;
            min-height: 60px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        .careers-dashboard-container .note-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        .careers-dashboard-container .note-save-btn,
        .careers-dashboard-container .note-cancel-btn {
            padding: 0.25rem 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.75rem !important;
            cursor: pointer;
            text-decoration: none !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        .careers-dashboard-container .note-save-btn {
            background: #000;
            color: white;
            border-color: #000;
        }
        .careers-dashboard-container .note-save-btn:hover {
            background: #333;
            text-decoration: none !important;
        }
        .careers-dashboard-container .note-cancel-btn {
            background: #f8f9fa;
            color: #666;
        }
        .careers-dashboard-container .note-cancel-btn:hover {
            background: #e9ecef;
            text-decoration: none !important;
        }
        
        /* Action Buttons */
        .careers-dashboard-container .application-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        .careers-dashboard-container .action-btn {
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.875rem !important;
            font-weight: 500;
            text-decoration: none !important;
            display: inline-block;
            transition: all 0.2s ease;
            background: #fff;
            color: #333;
            cursor: pointer;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        .careers-dashboard-container .action-btn:hover {
            background: #f5f5f5;
            text-decoration: none !important;
        }
        .careers-dashboard-container .action-btn.primary {
            background: #000;
            color: white;
            border-color: #000;
        }
        .careers-dashboard-container .action-btn.primary:hover {
            background: #333;
            text-decoration: none !important;
        }
        
        /* Pagination */
        .careers-dashboard-container .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
            margin-bottom: 4rem;
            padding: 1rem;
        }
        .careers-dashboard-container .pagination a,
        .careers-dashboard-container .pagination span {
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none !important;
            color: #333;
            font-size: 0.875rem;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        .careers-dashboard-container .pagination a:hover {
            background: #f5f5f5;
            text-decoration: none !important;
        }
        .careers-dashboard-container .pagination .current {
            background: #000;
            color: white;
            border-color: #000;
        }
        .careers-dashboard-container .pagination .disabled {
            color: #ccc;
            cursor: not-allowed;
        }
        
        /* Empty State */
        .careers-dashboard-container .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #666;
        }
        .careers-dashboard-container .empty-state h3 {
            font-size: 1.25rem !important;
            font-weight: 500 !important;
            color: #111;
            margin: 0 0 0.5rem 0 !important;
        }
        .careers-dashboard-container .empty-state p {
            margin: 0 0 2rem 0 !important;
        }
        
        /* Applications Page Bottom Spacing - Target applications page specifically */
        .careers-dashboard-container .applications-stats {
            margin-bottom: 2rem;
        }
        .careers-dashboard-container .filters-section {
            margin-bottom: 2rem;
        }
        .careers-dashboard-container .empty-state {
            margin-bottom: 4rem !important;
        }
        .careers-dashboard-container .jobs-table-container:last-child {
            margin-bottom: 4rem !important;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .careers-dashboard-container .filters-grid {
                grid-template-columns: 1fr;
            }
            .careers-dashboard-container .application-header {
                grid-template-columns: 1fr;
            }
            .careers-dashboard-container .application-details {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            .careers-dashboard-container .status-pipeline {
                justify-content: center;
            }
            .careers-dashboard-container .application-actions {
                flex-direction: column;
            }
        }
        </style>
        
        <?php $this->render_dashboard_navigation('applications'); ?>
        
        <div class="careers-dashboard-container">
            <div class="dashboard-content">
                <div class="page-header">
                    <div class="page-header-content">
                        <div class="page-header-text">
                            <h1 class="page-title">Applications</h1>
                            <p class="page-subtitle">Manage job applications and track applicant progress</p>
                        </div>
                        <div class="page-actions">
                            <button id="delete-all-applications" class="delete-all-btn" type="button">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 6h18"/>
                                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                </svg>
                                Delete All Applications
                            </button>
                        </div>
                    </div>
                </div>
            
            <!-- Applications Statistics -->
            <div class="applications-stats">
                <div class="stat-card new">
                    <div class="stat-number"><?php echo esc_html($stats['by_status']['new'] ?? 0); ?></div>
                    <div class="stat-label">New</div>
                </div>
                <div class="stat-card under-review">
                    <div class="stat-number"><?php echo esc_html($stats['by_status']['under_review'] ?? 0); ?></div>
                    <div class="stat-label">Under Review</div>
                </div>
                <div class="stat-card contacted">
                    <div class="stat-number"><?php echo esc_html($stats['by_status']['contacted'] ?? 0); ?></div>
                    <div class="stat-label">Contacted</div>
                </div>
                <div class="stat-card interview">
                    <div class="stat-number"><?php echo esc_html($stats['by_status']['interview'] ?? 0); ?></div>
                    <div class="stat-label">Interview</div>
                </div>
                <div class="stat-card hired">
                    <div class="stat-number"><?php echo esc_html($stats['by_status']['hired'] ?? 0); ?></div>
                    <div class="stat-label">Hired</div>
                </div>
                <div class="stat-card rejected">
                    <div class="stat-number"><?php echo esc_html($stats['by_status']['rejected'] ?? 0); ?></div>
                    <div class="stat-label">Rejected</div>
                </div>
            </div>
            
            <!-- Filters Section -->
            <div class="filters-section">
                <form method="GET" action="" id="applications-filters-form">
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label for="search">Search Applicants</label>
                            <input type="text" id="search" name="search" value="<?php echo esc_attr($search_query); ?>" 
                                   placeholder="Search by name or email...">
                        </div>
                        <div class="filter-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="">All Statuses</option>
                                <?php foreach ($status_pipeline as $key => $status): ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($status_filter, $key); ?>>
                                        <?php echo esc_html($status['label']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="job_id">Job Position</label>
                            <select id="job_id" name="job_id">
                                <option value="">All Positions</option>
                                <option value="0" <?php selected($job_filter, 0); ?>>General Applications</option>
                                <?php foreach ($available_jobs as $job): ?>
                                    <option value="<?php echo esc_attr($job->id); ?>" <?php selected($job_filter, $job->id); ?>>
                                        <?php echo esc_html($job->position_name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <button type="submit" class="filter-button">Filter</button>
                            <a href="<?php echo remove_query_arg(array('status', 'search', 'job_id', 'paged')); ?>" class="filter-button clear-filters">Clear</a>
                        </div>
                    </div>
                </form>
            </div>
            
            <?php if (empty($applications)): ?>
                <div class="empty-state">
                    <h3><?php echo $status_filter || $search_query || $job_filter ? 'No applications found' : 'No applications yet'; ?></h3>
                    <p><?php echo $status_filter || $search_query || $job_filter ? 'Try adjusting your filters.' : 'Applications will appear here once candidates start applying.'; ?></p>
                </div>
            <?php else: ?>
                <!-- Results Info -->
                <div class="results-info">
                    <span>Showing <?php echo count($applications); ?> of <?php echo $total_applications; ?> applications</span>
                    <span>Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                </div>
                <!-- Applications Table -->
                <div class="jobs-table-container">
                    <!-- Table Header -->
                    <div class="jobs-table-header applications-table-header">
                        <div class="table-header-cell applicant-col">Applicant</div>
                        <div class="table-header-cell position-col">Position</div>
                        <div class="table-header-cell modality-col">Job Type</div>
                        <div class="table-header-cell location-col">Location</div>
                        <div class="table-header-cell status-col">Status</div>
                        <div class="table-header-cell applied-col">Applied</div>
                        <div class="table-header-cell documents-col">Documents</div>
                        <div class="table-header-cell actions-col">Actions</div>
                    </div>
                    
                    <!-- Applications List -->
                    <div class="jobs-list applications-list">
                        <?php foreach ($applications as $application): ?>
                            <?php 
                            $user = get_user_by('id', $application->user_id);
                            $position = null;
                            $modality = 'General';
                            $location = 'Various';
                            
                            if (!empty($application->job_id) && $application->job_id > 0) {
                                $position = CareersPositionsDB::get_position($application->job_id);
                                if ($position) {
                                    $modality = !empty($position->job_type) ? $position->job_type : 'Not specified';
                                    $location = $position->location;
                                }
                            }
                            
                            $meta = !empty($application->meta) ? json_decode($application->meta, true) : array();
                            
                            // Get applicant's actual name from metadata
                            $applicant_name = 'Unknown Applicant';
                            if (!empty($meta['first_name']) || !empty($meta['last_name'])) {
                                $first_name = !empty($meta['first_name']) ? $meta['first_name'] : '';
                                $last_name = !empty($meta['last_name']) ? $meta['last_name'] : '';
                                $applicant_name = trim($first_name . ' ' . $last_name);
                            } elseif ($user) {
                                $applicant_name = $user->display_name;
                            }
                            
                            // Get applicant's email from metadata
                            $applicant_email = 'No email';
                            if (!empty($meta['email'])) {
                                $applicant_email = $meta['email'];
                            } elseif ($user) {
                                $applicant_email = $user->user_email;
                            }
                            ?>
                            <div class="job-row application-row" data-application-id="<?php echo esc_attr($application->id); ?>">
                                <div class="job-cell applicant-col">
                                    <div class="applicant-info">
                                        <div class="applicant-name"><?php echo esc_html($applicant_name); ?></div>
                                        <div class="applicant-email"><?php echo esc_html($applicant_email); ?></div>
                                    </div>
                                </div>
                                <div class="job-cell position-col" data-label="Position">
                                    <?php if ($position): ?>
                                        <span class="position-name"><?php echo esc_html($position->position_name); ?></span>
                                    <?php else: ?>
                                        <span class="general-application">General Application</span>
                                    <?php endif; ?>
                                </div>
                                <div class="job-cell modality-col" data-label="Job Type">
                                    <span class="modality-badge"><?php echo esc_html($modality); ?></span>
                                </div>
                                <div class="job-cell location-col" data-label="Location">
                                    <div class="location-info">
                                        <svg class="location-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                            <circle cx="12" cy="10" r="3"/>
                                        </svg>
                                        <?php echo esc_html($location); ?>
                                    </div>
                                </div>
                                <div class="job-cell status-col" data-label="Status">
                                    <div class="status-dropdown-container">
                                        <select class="status-dropdown" data-application-id="<?php echo esc_attr($application->id); ?>">
                                            <?php foreach ($status_pipeline as $status_key => $status_info): ?>
                                                <option value="<?php echo esc_attr($status_key); ?>" 
                                                        <?php selected($application->status, $status_key); ?>>
                                                    <?php echo esc_html($status_info['label']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="job-cell applied-col" data-label="Applied">
                                    <span class="applied-date"><?php echo esc_html(date('M j, Y', strtotime($application->submitted_at))); ?></span>
                                </div>
                                <div class="job-cell documents-col" data-label="Documents">
                                    <div class="documents-list">
                                        <?php if (!empty($application->resume_url)): ?>
                                            <a href="<?php echo esc_url($application->resume_url); ?>" target="_blank" 
                                               class="document-link resume-link" 
                                               title="View Resume">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                                    <polyline points="14,2 14,8 20,8"/>
                                                    <line x1="16" y1="13" x2="8" y2="13"/>
                                                    <line x1="16" y1="17" x2="8" y2="17"/>
                                                    <polyline points="10,9 9,9 8,9"/>
                                                </svg>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($application->cover_letter_url)): ?>
                                            <a href="<?php echo esc_url($application->cover_letter_url); ?>" target="_blank" 
                                               class="document-link cover-letter-link" 
                                               title="View Cover Letter">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                                    <polyline points="22,6 12,13 2,6"/>
                                                </svg>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (empty($application->resume_url) && empty($application->cover_letter_url)): ?>
                                            <span class="no-documents">No documents</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="job-cell actions-col">
                                    <div class="job-actions">
                                        <a href="<?php echo CareersSettings::get_page_url('application_view', array('id' => $application->id)); ?>" 
                                           class="action-icon view-icon" 
                                           title="View Application Details"
                                           aria-label="View application details for <?php echo esc_attr($applicant_name); ?>">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                        </a>
                                        <a href="#" class="action-icon notes-icon notes-toggle" 
                                           data-application-id="<?php echo esc_attr($application->id); ?>"
                                           title="View/Add Notes"
                                           aria-label="View notes for <?php echo esc_attr($applicant_name); ?>">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M3 3v18l6-6h12V3H3z"/>
                                            </svg>
                                        </a>
                                        <a href="#" class="action-icon delete-icon delete-application" 
                                           data-id="<?php echo esc_attr($application->id); ?>"
                                           title="Delete Application"
                                           aria-label="Delete application from <?php echo esc_attr($applicant_name); ?>">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M3 6h18"/>
                                                <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                                                <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="<?php echo esc_url(add_query_arg('paged', $page - 1)); ?>">&laquo; Previous</a>
                        <?php else: ?>
                            <span class="disabled">&laquo; Previous</span>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="<?php echo esc_url(add_query_arg('paged', $i)); ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="<?php echo esc_url(add_query_arg('paged', $page + 1)); ?>">Next &raquo;</a>
                        <?php else: ?>
                            <span class="disabled">Next &raquo;</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Status dropdown change handler
            $('.status-dropdown').on('change', function() {
                var $dropdown = $(this);
                var applicationId = $dropdown.data('application-id');
                var newStatus = $dropdown.val();
                
                $.ajax({
                    url: careers_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'careers_update_application_status',
                        application_id: applicationId,
                        status: newStatus,
                        nonce: careers_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            $('<div style="position: fixed; top: 20px; right: 20px; background: #10b981; color: white; padding: 12px 20px; border-radius: 6px; z-index: 9999;">Status updated successfully!</div>')
                                .appendTo('body')
                                .delay(2000)
                                .fadeOut();
                        } else {
                            alert('Error updating status: ' + response.data);
                            // Reset dropdown to previous value
                            $dropdown.val($dropdown.data('original-value'));
                        }
                    },
                    error: function() {
                        alert('Error updating status. Please try again.');
                    }
                });
            });
            
            // Store original status values for resetting on error
            $('.status-dropdown').each(function() {
                $(this).data('original-value', $(this).val());
            });
            
            // Notes toggle handler
            $('.notes-toggle').on('click', function(e) {
                e.preventDefault();
                var applicationId = $(this).data('application-id');
                // TODO: Implement notes modal/panel
                alert('Notes functionality coming soon! Application ID: ' + applicationId);
            });
            
            // Delete application handler
            $('.delete-application').on('click', function(e) {
                e.preventDefault();
                
                if (!confirm('Are you sure you want to delete this application?')) {
                    return;
                }
                
                var applicationId = $(this).data('id');
                var $row = $(this).closest('.application-row');
                
                $.ajax({
                    url: careers_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'careers_delete_application',
                        application_id: applicationId,
                        nonce: careers_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $row.fadeOut(300, function() {
                                $(this).remove();
                                // If no more rows, reload page to show empty state
                                if ($('.application-row').length === 0) {
                                    location.reload();
                                }
                            });
                        } else {
                            alert('Error: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('Error deleting application. Please try again.');
                    }
                });
            });
            
            // Delete all applications handler
            $('#delete-all-applications').on('click', function() {
                if (!confirm('Are you sure you want to delete ALL applications? This action cannot be undone!')) {
                    return;
                }
                
                if (!confirm('This will permanently delete all application data including notes and files. Are you absolutely sure?')) {
                    return;
                }
                
                var $btn = $(this);
                $btn.prop('disabled', true).text('Deleting...');
                
                $.ajax({
                    url: careers_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'careers_delete_all_applications',
                        nonce: careers_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('All applications have been deleted successfully.');
                            location.reload(); // Refresh the page to show empty state
                        } else {
                            alert('Error deleting applications: ' + response.data);
                            $btn.prop('disabled', false).text('Delete All Applications');
                        }
                    },
                    error: function() {
                        alert('Error deleting applications. Please try again.');
                        $btn.prop('disabled', false).text('Delete All Applications');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Get application notes
     */
    private function get_application_notes($application_id) {
        return CareersApplicationDB::get_application_notes($application_id);
    }
    
    /**
     * Get applications count with filters
     */
    private function get_applications_count($args) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'careers_applications';
        
        $where_conditions = array();
        $where_values = array();
        
        if (!empty($args['status'])) {
            $where_conditions[] = "status = %s";
            $where_values[] = $args['status'];
        }
        
        if (!empty($args['job_id'])) {
            $where_conditions[] = "job_id = %d";
            $where_values[] = $args['job_id'];
        }
        
        $where_clause = '';
        if (!empty($where_conditions)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        }
        
        $query = "SELECT COUNT(*) FROM $table_name $where_clause";
        
        if (!empty($where_values)) {
            return $wpdb->get_var($wpdb->prepare($query, $where_values));
        } else {
            return $wpdb->get_var($query);
        }
    }
    
    /**
     * Handle application status update AJAX request
     */
    public function handle_update_application_status() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'careers_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options') && !current_user_can('career_admin')) {
            wp_send_json_error('Permission denied');
        }
        
        $application_id = intval($_POST['application_id']);
        $new_status = sanitize_text_field($_POST['status']);
        
        if (empty($application_id) || empty($new_status)) {
            wp_send_json_error('Missing application ID or status');
        }
        
        $result = CareersApplicationDB::update_status($application_id, $new_status);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        // Add a status change note
        $status_labels = array(
            'new' => 'New',
            'under_review' => 'Under Review',
            'contacted' => 'Contacted',
            'interview' => 'Interview',
            'hired' => 'Hired',
            'rejected' => 'Rejected'
        );
        
        $note_content = 'Status changed to: ' . ($status_labels[$new_status] ?? $new_status);
        CareersApplicationDB::add_note($application_id, get_current_user_id(), $note_content);
        
        wp_send_json_success('Status updated successfully');
    }
    
    /**
     * Handle add application note AJAX request
     */
    public function handle_add_application_note() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'careers_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options') && !current_user_can('career_admin')) {
            wp_send_json_error('Permission denied');
        }
        
        $application_id = intval($_POST['application_id']);
        $note_content = sanitize_textarea_field($_POST['note_content']);
        
        if (empty($application_id) || empty($note_content)) {
            wp_send_json_error('Missing application ID or note content');
        }
        
        $result = CareersApplicationDB::add_note($application_id, get_current_user_id(), $note_content);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        wp_send_json_success('Note added successfully');
    }
    
    /**
     * Handle delete single application AJAX request
     */
    public function handle_delete_application() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'careers_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options') && !current_user_can('career_admin')) {
            wp_send_json_error('Permission denied');
        }
        
        $application_id = intval($_POST['application_id']);
        if (!$application_id) {
            wp_send_json_error('Invalid application ID');
        }
        
        // Verify application exists
        $application = CareersApplicationDB::get_application($application_id);
        if (!$application) {
            wp_send_json_error('Application not found');
        }
        
        // Delete the application
        $result = CareersApplicationDB::delete_application($application_id);
        
        if (!$result) {
            wp_send_json_error('Failed to delete application');
        }
        
        // Log the deletion for audit purposes
        error_log('Careers: Application ID ' . $application_id . ' deleted by user ID ' . get_current_user_id());
        
        wp_send_json_success('Application deleted successfully');
    }
    
    /**
     * Handle delete all applications AJAX request
     */
    public function handle_delete_all_applications() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'careers_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        // Check permissions - only admins can delete all applications
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied - administrator access required');
        }
        
        global $wpdb;
        
        // Delete all application notes first (to maintain referential integrity)
        $notes_table = $wpdb->prefix . 'careers_application_notes';
        $notes_deleted = $wpdb->query("DELETE FROM $notes_table");
        
        // Delete all applications
        $applications_table = $wpdb->prefix . 'careers_applications';
        $apps_deleted = $wpdb->query("DELETE FROM $applications_table");
        
        if ($apps_deleted === false || $notes_deleted === false) {
            wp_send_json_error('Failed to delete all applications');
        }
        
        // Log the deletion for audit purposes
        error_log("Careers: All applications deleted by user " . get_current_user_id() . " (" . wp_get_current_user()->user_login . ")");
        
        wp_send_json_success(array(
            'message' => 'All applications deleted successfully',
            'applications_deleted' => $apps_deleted,
            'notes_deleted' => $notes_deleted
        ));
    }
    
    /**
     * Render profile management
     */
    public function render_profile_management() {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return '<div class="careers-dashboard-error">You must be logged in to view your profile.</div>';
        }
        
        $current_user = wp_get_current_user();
        $success_message = '';
        $error_message = '';
        
        // Handle form submission
        if (isset($_POST['update_profile']) && wp_verify_nonce($_POST['careers_nonce'], 'careers_nonce')) {
            $first_name = sanitize_text_field($_POST['first_name']);
            $last_name = sanitize_text_field($_POST['last_name']);
            $email = sanitize_email($_POST['email']);
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            $update_data = array('ID' => $current_user->ID);
            $update_meta = array();
            
            // Validate and update basic info
            if (!empty($first_name)) {
                $update_meta['first_name'] = $first_name;
            }
            if (!empty($last_name)) {
                $update_meta['last_name'] = $last_name;
            }
            
            // Validate email
            if (!empty($email) && $email !== $current_user->user_email) {
                if (!is_email($email)) {
                    $error_message = 'Please enter a valid email address.';
                } elseif (email_exists($email)) {
                    $error_message = 'This email address is already in use.';
                } else {
                    $update_data['user_email'] = $email;
                }
            }
            
            // Handle password change
            if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
                if (empty($current_password)) {
                    $error_message = 'Please enter your current password.';
                } elseif (!wp_check_password($current_password, $current_user->user_pass, $current_user->ID)) {
                    $error_message = 'Current password is incorrect.';
                } elseif (empty($new_password)) {
                    $error_message = 'Please enter a new password.';
                } elseif ($new_password !== $confirm_password) {
                    $error_message = 'New passwords do not match.';
                } elseif (strlen($new_password) < 8) {
                    $error_message = 'Password must be at least 8 characters long.';
                } else {
                    $update_data['user_pass'] = $new_password;
                }
            }
            
            // Update user if no errors
            if (empty($error_message)) {
                $result = wp_update_user($update_data);
                
                if (is_wp_error($result)) {
                    $error_message = $result->get_error_message();
                } else {
                    // Update meta fields
                    foreach ($update_meta as $key => $value) {
                        update_user_meta($current_user->ID, $key, $value);
                    }
                    
                    $success_message = 'Profile updated successfully.';
                    
                    // Refresh user data
                    $current_user = wp_get_current_user();
                }
            }
        }
        
        // Get current user meta
        $first_name = get_user_meta($current_user->ID, 'first_name', true);
        $last_name = get_user_meta($current_user->ID, 'last_name', true);
        
        ?>
        <style>
        .careers-dashboard-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 2rem 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            color: #333;
            font-size: 16px !important;
        }
        .careers-dashboard-container * {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        /* High specificity CSS overrides to fix font and underline issues */
        .careers-dashboard-container,
        .careers-dashboard-container * {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            font-size: 16px !important;
            line-height: 1.5 !important;
        }
        
        /* Remove all underlines and text decorations with maximum specificity */
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
        .careers-dashboard-container .create-button:focus,
        .careers-dashboard-container .button,
        .careers-dashboard-container .button:link,
        .careers-dashboard-container .button:visited,
        .careers-dashboard-container .button:hover,
        .careers-dashboard-container .button:active,
        .careers-dashboard-container .button:focus {
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
        
        .careers-dashboard-container h1,
        .careers-dashboard-container h2,
        .careers-dashboard-container h3,
        .careers-dashboard-container h4,
        .careers-dashboard-container h5,
        .careers-dashboard-container h6 {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            font-weight: 500 !important;
        }
        .careers-dashboard-container p,
        .careers-dashboard-container span,
        .careers-dashboard-container div {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        
        /* Page Layout */
        .careers-dashboard-container .dashboard-content {
            padding-bottom: 1.5rem;
        }
        
        .careers-dashboard-container .page-header {
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .careers-dashboard-container .page-header-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 2rem;
        }
        
        .careers-dashboard-container .page-header-text {
            flex: 1;
        }
        
        .careers-dashboard-container .page-title {
            font-size: 2.25rem !important;
            font-weight: 600 !important;
            color: #111827 !important;
            margin: 0 0 0.75rem 0 !important;
            line-height: 1.25 !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        
        .careers-dashboard-container .page-subtitle {
            font-size: 1.125rem !important;
            color: #6b7280 !important;
            margin: 0 !important;
            line-height: 1.5 !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        
        /* Profile Form Styles */
        .careers-dashboard-container .profile-content {
            margin-bottom: 4rem;
        }
        
        .careers-dashboard-container .profile-form {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 2rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .careers-dashboard-container .form-section {
            margin-bottom: 2rem;
        }
        
        .careers-dashboard-container .form-section:last-child {
            margin-bottom: 0;
        }
        
        .careers-dashboard-container .section-title {
            font-size: 1.25rem !important;
            font-weight: 600 !important;
            color: #111 !important;
            margin: 0 0 1rem 0 !important;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .careers-dashboard-container .form-row {
            margin-bottom: 2rem;
        }
        
        .careers-dashboard-container .form-row label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #111;
            font-size: 1rem;
        }
        
        .careers-dashboard-container .form-row input, 
        .careers-dashboard-container .form-row textarea, 
        .careers-dashboard-container .form-row select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            box-sizing: border-box;
            transition: border-color 0.2s ease;
            background: #fff;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        
        .careers-dashboard-container .form-row input:focus, 
        .careers-dashboard-container .form-row textarea:focus, 
        .careers-dashboard-container .form-row select:focus {
            outline: none;
            border-color: #333;
            box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.05);
        }
        
        .careers-dashboard-container .form-row small {
            display: block;
            margin-top: 0.25rem;
            color: #666;
            font-size: 0.875rem;
        }
        
        .careers-dashboard-container .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .careers-dashboard-container .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #eee;
        }
        
        .careers-dashboard-container .alert {
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1.5rem;
        }
        
        .careers-dashboard-container .alert-success {
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }
        
        .careers-dashboard-container .alert-error {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        
        .careers-dashboard-container .password-note {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.5rem;
        }
        
        .careers-dashboard-container .button {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem !important;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none !important;
            text-align: center;
            transition: background 0.2s ease;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        .careers-dashboard-container .button-primary {
            background: #000 !important;
            color: white !important;
        }
        .careers-dashboard-container .button-primary:hover {
            background: #333 !important;
            color: white !important;
            text-decoration: none !important;
        }
        
        @media (max-width: 1024px) {
            .careers-dashboard-container .form-grid {
                grid-template-columns: 1fr;
            }
            .careers-dashboard-container .page-header-content {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
        
        @media (max-width: 768px) {
            .careers-dashboard-container .page-title {
                font-size: 1.875rem !important;
            }
        }
        </style>
        
        <?php $this->render_dashboard_navigation('profile'); ?>
        
        <div class="careers-dashboard-container">
            <div class="dashboard-content">
                <div class="page-header">
                    <div class="page-header-content">
                        <div class="page-header-text">
                            <h1 class="page-title">Profile Settings</h1>
                            <p class="page-subtitle">Update your personal information and password</p>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success"><?php echo esc_html($success_message); ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-error"><?php echo esc_html($error_message); ?></div>
                <?php endif; ?>
                
                <div class="profile-content">
                    <form method="post" class="profile-form">
                        <?php wp_nonce_field('careers_nonce', 'careers_nonce'); ?>
                        
                        <!-- Personal Information -->
                        <div class="form-section">
                            <h2 class="section-title">Personal Information</h2>
                            
                            <div class="form-grid">
                                <div class="form-row">
                                    <label for="first_name">First Name</label>
                                    <input type="text" id="first_name" name="first_name" value="<?php echo esc_attr($first_name); ?>">
                                </div>
                                
                                <div class="form-row">
                                    <label for="last_name">Last Name</label>
                                    <input type="text" id="last_name" name="last_name" value="<?php echo esc_attr($last_name); ?>">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" value="<?php echo esc_attr($current_user->user_email); ?>">
                            </div>
                        </div>
                        
                        <!-- Change Password -->
                        <div class="form-section">
                            <h2 class="section-title">Change Password</h2>
                            <p class="password-note">Leave blank to keep your current password</p>
                            
                            <div class="form-row">
                                <label for="current_password">Current Password</label>
                                <input type="password" id="current_password" name="current_password">
                            </div>
                            
                            <div class="form-grid">
                                <div class="form-row">
                                    <label for="new_password">New Password</label>
                                    <input type="password" id="new_password" name="new_password">
                                </div>
                                
                                <div class="form-row">
                                    <label for="confirm_password">Confirm New Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="update_profile" class="button button-primary">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
}