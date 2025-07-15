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
        
        // Handle frontend dashboard routing
        add_action('wp', array($this, 'handle_dashboard_routing'));
        
        // Add dashboard shortcode
        add_shortcode('careers_form', array($this, 'job_form_shortcode'));
    }
    
    /**
     * Handle dashboard routing for /dashboard URL
     */
    public function handle_dashboard_routing() {
        global $wp;
        
        // Check if we're on any dashboard page
        if (strpos($wp->request, 'dashboard') === 0 || 
            (isset($wp->query_vars['careers_dashboard']) && !empty($wp->query_vars['careers_dashboard']))) {
            $this->load_dashboard_template();
        }
    }
    
    /**
     * Load appropriate dashboard template based on user role
     */
    private function load_dashboard_template() {
        if (!is_user_logged_in()) {
            wp_redirect(wp_login_url(home_url('/dashboard')));
            exit;
        }
        
        // Check if user has admin permissions
        if (!current_user_can('manage_options') && !current_user_can('career_admin')) {
            wp_redirect(home_url());
            exit;
        }
        
        // Get the dashboard page and action from query vars
        $dashboard_page = get_query_var('careers_dashboard', 'main');
        $action = get_query_var('careers_action', '');
        $id = get_query_var('careers_id', '');
        
        // Route to appropriate dashboard view
        $this->route_dashboard_view($dashboard_page, $action, $id);
        exit;
    }
    
    /**
     * Route dashboard views based on page and action
     */
    private function route_dashboard_view($page, $action, $id) {
        get_header();
        
        wp_enqueue_style('careers-dashboard', CAREERS_PLUGIN_URL . 'assets/css/frontend.css', array(), CAREERS_PLUGIN_VERSION);
        wp_enqueue_script('jquery');
        
        echo '<div class="careers-dashboard-container">';
        
        switch ($page) {
            case 'jobs':
            case 'positions':
                if ($action === 'create') {
                    $this->render_position_creation_form();
                } elseif ($action === 'edit' && $id) {
                    $this->render_position_edit_form($id);
                } else {
                    $this->render_position_management();
                }
                break;
            
            case 'locations':
                $this->render_location_management();
                break;
            
            default:
                $this->render_main_dashboard();
                break;
        }
        
        echo '</div>';
        
        get_footer();
    }
    
    /**
     * Render main dashboard
     */
    private function render_main_dashboard() {
        $stats = CareersPositionsDB::get_stats();
        $application_stats = CareersApplicationDB::get_stats();
        
        // Calculate additional metrics
        $total_jobs = $stats['total'] ?? 0;
        $total_applications = $application_stats['total'] ?? 0;
        $avg_applications_per_job = $total_jobs > 0 ? round($total_applications / $total_jobs, 1) : 0;
        
        // Get active jobs for the table
        $active_jobs = CareersPositionsDB::get_positions(array('status' => 'published', 'limit' => 10));
        
        ?>
        <style>
        .dashboard-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 2rem 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #333;
        }
        .dashboard-header {
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #eee;
        }
        .dashboard-title {
            font-size: 2.5rem;
            font-weight: 500;
            margin: 0 0 0.5rem 0;
            line-height: 1.2;
            color: #111;
        }
        .dashboard-subtitle {
            color: #666;
            margin: 0;
            font-size: 1rem;
        }
        .dashboard-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
            background: #f5f5f5;
            padding: 0.25rem;
            border-radius: 4px;
            width: fit-content;
        }
        .dashboard-tab {
            padding: 0.75rem 1.5rem;
            background: transparent;
            border: none;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
            color: #666;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .dashboard-tab.active {
            background: #fff;
            color: #111;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 3rem;
        }
        .metric-card {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 4px;
            padding: 1.5rem;
            text-align: center;
        }
        .metric-number {
            font-size: 2.5rem;
            font-weight: 500;
            color: #111;
            margin: 0 0 0.5rem 0;
        }
        .metric-label {
            font-size: 0.875rem;
            color: #666;
            margin: 0;
        }
        .applicant-status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
            margin-bottom: 3rem;
        }
        .status-card {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 4px;
            padding: 1rem;
            text-align: center;
        }
        .status-number {
            font-size: 1.5rem;
            font-weight: 500;
            color: #111;
            margin: 0 0 0.25rem 0;
        }
        .status-label {
            font-size: 0.75rem;
            color: #666;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .jobs-section {
            margin-top: 3rem;
        }
        .section-title {
            font-size: 1.25rem;
            font-weight: 500;
            color: #111;
            margin: 0 0 1.5rem 0;
        }
        .jobs-grid {
            display: grid;
            gap: 1rem;
        }
        .job-card {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 4px;
            padding: 1.5rem;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 1rem;
            align-items: center;
        }
        .job-info {
            display: grid;
            grid-template-columns: 2fr 1.5fr 1fr 1.5fr 1fr;
            gap: 1.5rem;
            align-items: start;
        }
        .job-info-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        .job-info-label {
            font-size: 0.75rem;
            font-weight: 500;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.25rem;
        }
        .job-title {
            font-size: 1.125rem;
            font-weight: 500;
            color: #111;
            margin: 0;
        }
        .job-location {
            color: #666;
            font-size: 0.9rem;
        }
        .app-count {
            font-weight: 500;
            color: #059669;
            background: #d1fae5;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            text-align: center;
            min-width: 2rem;
            display: inline-block;
        }
        .employment-type {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: #f3f4f6;
            color: #666;
        }
        .employment-type.full-time {
            background: #dbeafe;
            color: #1d4ed8;
        }
        .employment-type.part-time {
            background: #fef3c7;
            color: #d97706;
        }
        .employment-type.contract {
            background: #f3e8ff;
            color: #7c3aed;
        }
        .employment-type.per-diem {
            background: #ecfdf5;
            color: #059669;
        }
        .employment-type.travel {
            background: #fce7f3;
            color: #be185d;
        }
        .posted-date {
            color: #666;
            font-size: 0.9rem;
        }
        .job-actions {
            display: flex;
            gap: 0.5rem;
        }
        .action-btn {
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s ease;
            background: #fff;
            color: #333;
            cursor: pointer;
        }
        .action-btn:hover {
            background: #f5f5f5;
            color: #333;
        }
        .action-btn.primary {
            background: #000;
            color: white;
            border-color: #000;
        }
        .action-btn.primary:hover {
            background: #333;
            color: white;
        }
        .action-btn.danger {
            background: #dc2626;
            color: white;
            border-color: #dc2626;
        }
        .action-btn.danger:hover {
            background: #b91c1c;
            color: white;
        }
        .dashboard-actions {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        .dashboard-action-btn {
            background: #000;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: background 0.2s ease;
        }
        .dashboard-action-btn:hover {
            background: #333;
            color: white;
        }
        .dashboard-action-btn.secondary {
            background: #f5f5f5;
            color: #333;
            border: 1px solid #ddd;
        }
        .dashboard-action-btn.secondary:hover {
            background: #e8e8e8;
            color: #333;
        }
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
        .empty-state h3 {
            font-size: 1.125rem;
            font-weight: 500;
            color: #111;
            margin: 0 0 0.5rem 0;
        }
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem;
            }
            .dashboard-title {
                font-size: 2rem;
            }
            .metrics-grid {
                grid-template-columns: 1fr;
            }
            .applicant-status-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .dashboard-actions {
                flex-direction: column;
                gap: 0.5rem;
            }
            .dashboard-action-btn {
                text-align: center;
            }
            .job-card {
                padding: 1rem;
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            .job-info {
                display: block;
            }
            .job-info-item {
                margin-bottom: 1rem;
                padding-bottom: 0.75rem;
                border-bottom: 1px solid #f0f0f0;
            }
            .job-info-item:last-child {
                border-bottom: none;
                margin-bottom: 0;
            }
            .job-info-label {
                font-size: 0.75rem;
                margin-bottom: 0.5rem;
            }
            .job-title {
                font-size: 1.25rem;
                line-height: 1.3;
            }
            .job-location {
                font-size: 0.95rem;
            }
            .app-count {
                font-size: 0.875rem;
                padding: 0.375rem 0.75rem;
            }
            .employment-type {
                font-size: 0.875rem;
                padding: 0.375rem 0.75rem;
            }
            .posted-date {
                font-size: 0.95rem;
            }
            .job-actions {
                justify-content: stretch;
                gap: 0.5rem;
            }
            .action-btn {
                flex: 1;
                text-align: center;
                padding: 0.75rem 0.5rem;
                font-size: 0.875rem;
            }
        }
        </style>
        
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Dashboard</h1>
                <p class="dashboard-subtitle">Overview of your careers system</p>
            </div>
            
            <div class="dashboard-tabs">
                <button class="dashboard-tab active" data-tab="jobs">Jobs</button>
                <button class="dashboard-tab" data-tab="applicants">Applicants</button>
            </div>
            
            <!-- Jobs Tab -->
            <div id="jobs-tab" class="tab-content active">
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
                    <div class="dashboard-actions">
                        <a href="<?php echo home_url('/dashboard/jobs/create'); ?>" class="dashboard-action-btn">Create New Job</a>
                        <a href="<?php echo home_url('/dashboard/jobs'); ?>" class="dashboard-action-btn secondary">Manage All Jobs</a>
                        <a href="<?php echo home_url('/dashboard/locations'); ?>" class="dashboard-action-btn secondary">Manage Locations</a>
                    </div>
                    
                    <h3 class="section-title">Active Jobs</h3>
                    <?php if (empty($active_jobs)): ?>
                        <div class="empty-state">
                            <h3>No active jobs</h3>
                            <p>Create your first job posting to get started.</p>
                        </div>
                    <?php else: ?>
                        <div class="jobs-grid">
                            <?php foreach ($active_jobs as $job): ?>
                                <div class="job-card">
                                    <div class="job-info">
                                        <div class="job-info-item">
                                            <div class="job-info-label">Job Title</div>
                                            <h4 class="job-title"><?php echo esc_html($job->position_name); ?></h4>
                                        </div>
                                        <div class="job-info-item">
                                            <div class="job-info-label">Location</div>
                                            <div class="job-location"><?php echo esc_html($job->location); ?></div>
                                        </div>
                                        <div class="job-info-item">
                                            <div class="job-info-label">Applications</div>
                                            <?php 
                                            $app_count = CareersApplicationDB::get_applications_count_by_job($job->id);
                                            ?>
                                            <span class="app-count"><?php echo esc_html($app_count); ?></span>
                                        </div>
                                        <div class="job-info-item">
                                            <div class="job-info-label">Employment Type</div>
                                            <?php if (!empty($job->job_type)): ?>
                                                <?php 
                                                $type_class = strtolower(str_replace([' ', '-'], '-', $job->job_type));
                                                ?>
                                                <span class="employment-type <?php echo esc_attr($type_class); ?>"><?php echo esc_html($job->job_type); ?></span>
                                            <?php else: ?>
                                                <span class="employment-type">Not specified</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="job-info-item">
                                            <div class="job-info-label">Posted Date</div>
                                            <div class="posted-date"><?php echo esc_html(date('M j, Y', strtotime($job->created_at))); ?></div>
                                        </div>
                                    </div>
                                    <div class="job-actions">
                                        <a href="<?php echo home_url('/dashboard/jobs/edit/' . $job->id); ?>" class="action-btn primary">Edit</a>
                                        <a href="<?php echo home_url('/dashboard/jobs/applications/' . $job->id); ?>" class="action-btn">Applications</a>
                                        <a href="<?php echo home_url('/open-positions/' . $job->id); ?>" class="action-btn" target="_blank">View</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Applicants Tab -->
            <div id="applicants-tab" class="tab-content">
                <div class="applicant-status-grid">
                    <div class="status-card">
                        <div class="status-number">7</div>
                        <div class="status-label">New</div>
                    </div>
                    <div class="status-card">
                        <div class="status-number">10</div>
                        <div class="status-label">Under Review</div>
                    </div>
                    <div class="status-card">
                        <div class="status-number">0</div>
                        <div class="status-label">Contacted</div>
                    </div>
                    <div class="status-card">
                        <div class="status-number">2</div>
                        <div class="status-label">Interview</div>
                    </div>
                    <div class="status-card">
                        <div class="status-number">5</div>
                        <div class="status-label">Hired</div>
                    </div>
                    <div class="status-card">
                        <div class="status-number">4</div>
                        <div class="status-label">Rejected</div>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('.dashboard-tab').on('click', function() {
                var tabId = $(this).data('tab');
                
                // Update tab buttons
                $('.dashboard-tab').removeClass('active');
                $(this).addClass('active');
                
                // Update tab content
                $('.tab-content').removeClass('active');
                $('#' + tabId + '-tab').addClass('active');
            });
        });
        </script>
        <?php
    }
    
    /**
     * Render position creation form
     */
    private function render_position_creation_form() {
        $locations = CareersPositionsDB::get_locations();
        
        ?>
        <style>
        .careers-position-form {
            max-width: 1280px;
            margin: 0 auto;
            padding: 2rem 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #333;
        }
        .careers-position-form h1 {
            font-size: 2.5rem;
            font-weight: 500;
            margin: 0 0 3rem 0;
            line-height: 1.2;
            color: #111;
            padding-bottom: 2rem;
            border-bottom: 1px solid #eee;
        }
        .form-row {
            margin-bottom: 2rem;
        }
        .form-row label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #111;
            font-size: 1rem;
        }
        .form-row input, .form-row textarea, .form-row select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            box-sizing: border-box;
            transition: border-color 0.2s ease;
            background: #fff;
        }
        .form-row input:focus, .form-row textarea:focus, .form-row select:focus {
            outline: none;
            border-color: #333;
            box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.05);
        }
        .form-row small {
            display: block;
            margin-top: 0.25rem;
            color: #666;
            font-size: 0.875rem;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #eee;
        }
        .button {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: background 0.2s ease;
        }
        .button-primary {
            background: #000;
            color: white;
        }
        .button-primary:hover {
            background: #333;
        }
        .button:not(.button-primary) {
            background: #f5f5f5;
            color: #333;
            border: 1px solid #ddd;
        }
        .button:not(.button-primary):hover {
            background: #e8e8e8;
        }
        .checkbox-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .checkbox-row input[type="checkbox"] {
            width: auto;
            margin: 0;
        }
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            .form-actions {
                flex-direction: column;
            }
        }
        </style>
        
        <div class="careers-position-form">
            <h1>Create New Job Position</h1>
            
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
                            <?php foreach ($locations as $location): ?>
                                <option value="<?php echo esc_attr($location->display_name); ?>">
                                    <?php echo esc_html($location->display_name); ?>
                                </option>
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
                    <a href="<?php echo home_url('/dashboard/positions'); ?>" class="button">Cancel</a>
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
                            window.location.href = '/dashboard/positions';
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
    private function render_position_edit_form($id) {
        $position = CareersPositionsDB::get_position($id);
        $locations = CareersPositionsDB::get_locations();
        
        if (!$position) {
            echo '<div class="error">Position not found.</div>';
            return;
        }
        
        ?>
        <style>
        .careers-position-form {
            max-width: 1280px;
            margin: 0 auto;
            padding: 2rem 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #333;
        }
        .careers-position-form h1 {
            font-size: 2.5rem;
            font-weight: 500;
            margin: 0 0 3rem 0;
            line-height: 1.2;
            color: #111;
            padding-bottom: 2rem;
            border-bottom: 1px solid #eee;
        }
        .form-row {
            margin-bottom: 2rem;
        }
        .form-row label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #111;
            font-size: 1rem;
        }
        .form-row input, .form-row textarea, .form-row select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            box-sizing: border-box;
            transition: border-color 0.2s ease;
            background: #fff;
        }
        .form-row input:focus, .form-row textarea:focus, .form-row select:focus {
            outline: none;
            border-color: #333;
            box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.05);
        }
        .form-row small {
            display: block;
            margin-top: 0.25rem;
            color: #666;
            font-size: 0.875rem;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #eee;
        }
        .button {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: background 0.2s ease;
        }
        .button-primary {
            background: #000;
            color: white;
        }
        .button-primary:hover {
            background: #333;
        }
        .button:not(.button-primary) {
            background: #f5f5f5;
            color: #333;
            border: 1px solid #ddd;
        }
        .button:not(.button-primary):hover {
            background: #e8e8e8;
        }
        .checkbox-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
        </style>
        <div class="careers-position-form">
            <h1>Edit Job Position</h1>
            
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
                            <?php foreach ($locations as $location): ?>
                                <option value="<?php echo esc_attr($location->display_name); ?>"
                                        <?php selected($position->location, $location->display_name); ?>>
                                    <?php echo esc_html($location->display_name); ?>
                                </option>
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
                    <a href="<?php echo home_url('/dashboard/positions'); ?>" class="button">Cancel</a>
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
                            window.location.href = '/dashboard/positions';
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
     * Render position management
     */
    private function render_position_management() {
        $positions = CareersPositionsDB::get_positions(array('limit' => 50));
        
        ?>
        <style>
        .careers-position-management {
            max-width: 1280px;
            margin: 0 auto;
            padding: 2rem 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #333;
        }
        .management-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #eee;
        }
        .management-header h1 {
            font-size: 2.5rem;
            font-weight: 500;
            margin: 0;
            line-height: 1.2;
            color: #111;
        }
        .header-actions {
            display: flex;
            gap: 1rem;
        }
        .create-button {
            background: #000;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: background 0.2s ease;
        }
        .create-button:hover {
            background: #333;
            color: white;
        }
        .create-button.secondary {
            background: #f5f5f5;
            color: #333;
            border: 1px solid #ddd;
        }
        .create-button.secondary:hover {
            background: #e8e8e8;
            color: #333;
        }
        .positions-grid {
            display: grid;
            gap: 1rem;
        }
        .position-card {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 4px;
            padding: 1.5rem;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 1rem;
            align-items: center;
        }
        .position-info {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 1rem;
            align-items: center;
        }
        .position-name {
            font-size: 1.125rem;
            font-weight: 500;
            color: #111;
            margin: 0;
        }
        .position-location {
            color: #666;
            font-size: 0.9rem;
        }
        .position-status {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .position-status.published {
            background: #d1fae5;
            color: #065f46;
        }
        .position-status.draft {
            background: #fef3c7;
            color: #92400e;
        }
        .position-date {
            color: #666;
            font-size: 0.9rem;
        }
        .position-actions {
            display: flex;
            gap: 0.5rem;
        }
        .action-button {
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s ease;
            background: #fff;
            color: #333;
            cursor: pointer;
        }
        .action-button:hover {
            background: #f5f5f5;
            color: #333;
        }
        .action-button.primary {
            background: #000;
            color: white;
            border-color: #000;
        }
        .action-button.primary:hover {
            background: #333;
            color: white;
        }
        .action-button.danger {
            background: #dc2626;
            color: white;
            border-color: #dc2626;
        }
        .action-button.danger:hover {
            background: #b91c1c;
            color: white;
        }
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #666;
        }
        .empty-state h3 {
            font-size: 1.25rem;
            font-weight: 500;
            color: #111;
            margin: 0 0 0.5rem 0;
        }
        .empty-state p {
            margin: 0 0 2rem 0;
        }
        @media (max-width: 1024px) {
            .position-info {
                grid-template-columns: 1fr;
                gap: 0.5rem;
            }
            .position-card {
                grid-template-columns: 1fr;
            }
            .position-actions {
                justify-content: flex-start;
            }
        }
        @media (max-width: 768px) {
            .management-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            .header-actions {
                flex-direction: column;
                gap: 0.5rem;
                width: 100%;
            }
            .create-button {
                text-align: center;
            }
            .position-actions {
                flex-wrap: wrap;
            }
        }
        </style>
        <div class="careers-position-management">
            <div class="management-header">
                <h1>Manage Job Positions</h1>
                <div class="header-actions">
                    <a href="<?php echo home_url('/dashboard/positions/create'); ?>" class="create-button">
                        ➕ Create New Position
                    </a>
                    <a href="<?php echo home_url('/dashboard/locations'); ?>" class="create-button secondary">
                        📍 Manage Locations
                    </a>
                </div>
            </div>
            
            <?php if (empty($positions)): ?>
                <div class="empty-state">
                    <h3>No positions yet</h3>
                    <p>Create your first job position to get started.</p>
                    <a href="<?php echo home_url('/dashboard/positions/create'); ?>" class="create-button">
                        Create Your First Position
                    </a>
                </div>
            <?php else: ?>
                <div class="positions-grid">
                    <?php foreach ($positions as $position): ?>
                        <div class="position-card">
                            <div class="position-info">
                                <div>
                                    <h3 class="position-name"><?php echo esc_html($position->position_name); ?></h3>
                                    <div class="position-location">📍 <?php echo esc_html($position->location); ?></div>
                                </div>
                                <div>
                                    <span class="position-status <?php echo esc_attr($position->status); ?>">
                                        <?php echo esc_html(ucfirst($position->status)); ?>
                                    </span>
                                </div>
                                <div class="position-date">
                                    <?php echo esc_html(date('M j, Y', strtotime($position->created_at))); ?>
                                </div>
                            </div>
                            <div class="position-actions">
                                <a href="<?php echo home_url('/dashboard/positions/edit/' . esc_attr($position->id)); ?>" 
                                   class="action-button primary">Edit</a>
                                <a href="<?php echo home_url('/open-positions/' . esc_attr($position->id)); ?>" 
                                   class="action-button" target="_blank">View</a>
                                <button class="action-button danger delete-position" 
                                        data-id="<?php echo esc_attr($position->id); ?>">Delete</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('.delete-position').on('click', function(e) {
                e.preventDefault();
                
                if (!confirm('Are you sure you want to delete this position?')) {
                    return;
                }
                
                var positionId = $(this).data('id');
                var row = $(this).closest('tr');
                
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
                            row.fadeOut();
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
    private function render_location_management() {
        $locations_by_state = CareersPositionsDB::get_locations_by_state();
        
        ?>
        <style>
        .careers-location-management {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        .careers-location-management h1 {
            color: #2c3e50;
            margin-bottom: 30px;
            text-align: center;
        }
        .location-form {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 40px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .location-form h3 {
            margin-top: 0;
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .location-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 15px;
            align-items: end;
        }
        .location-form input, .location-form select {
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 6px;
            font-size: 14px;
        }
        .location-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #2c3e50;
        }
        .locations-by-state {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .state-group {
            border-bottom: 1px solid #e1e5e9;
        }
        .state-group:last-child {
            border-bottom: none;
        }
        .state-header {
            background: #3498db;
            color: white;
            padding: 15px 20px;
            font-weight: 600;
            font-size: 16px;
        }
        .cities-list {
            padding: 0;
            margin: 0;
            list-style: none;
        }
        .city-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 20px;
            border-bottom: 1px solid #f1f1f1;
        }
        .city-item:last-child {
            border-bottom: none;
        }
        .city-item:hover {
            background: #f8f9fa;
        }
        .city-name {
            font-size: 14px;
            color: #2c3e50;
        }
        .delete-location {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: background 0.3s ease;
        }
        .delete-location:hover {
            background: #c0392b;
        }
        .no-locations {
            text-align: center;
            padding: 40px 20px;
            color: #666;
            font-style: italic;
        }
        @media (max-width: 768px) {
            .location-form-grid {
                grid-template-columns: 1fr;
            }
        }
        </style>
        
        <div class="careers-location-management">
            <h1>Manage Locations</h1>
            
            <div class="location-form">
                <h3>Add New Location</h3>
                <form id="add-location-form">
                    <?php wp_nonce_field('careers_location_action', 'careers_nonce'); ?>
                    <div class="location-form-grid">
                        <div>
                            <label for="location_state">State *</label>
                            <input type="text" id="location_state" name="location_state" 
                                   placeholder="e.g. Texas" required>
                        </div>
                        <div>
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
            
            <div class="locations-by-state">
                <h3 style="padding: 20px; margin: 0; background: #ecf0f1; color: #2c3e50;">Existing Locations</h3>
                
                <?php if (empty($locations_by_state)): ?>
                    <div class="no-locations">
                        No locations added yet. Add your first location above.
                    </div>
                <?php else: ?>
                    <?php foreach ($locations_by_state as $state => $cities): ?>
                        <div class="state-group">
                            <div class="state-header">
                                <?php echo esc_html($state); ?> (<?php echo count($cities); ?> cities)
                            </div>
                            <ul class="cities-list">
                                <?php foreach ($cities as $location): ?>
                                    <li class="city-item">
                                        <span class="city-name"><?php echo esc_html($location->city); ?></span>
                                        <button class="delete-location" data-id="<?php echo esc_attr($location->id); ?>">
                                            Delete
                                        </button>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
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
}