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
        
        ?>
        <style>
        body {
            background-color: #f9fafb;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .admin-dashboard-container {
            background-color: #f9fafb;
            min-height: 100vh;
            padding: 2.5rem 1rem;
        }
        .dashboard-inner {
            max-width: 1280px;
            margin: 0 auto;
        }
        .dashboard-header {
            margin-bottom: 2rem;
        }
        .dashboard-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: #111827;
            margin: 0 0 0.5rem 0;
        }
        .dashboard-subtitle {
            color: #6b7280;
            margin: 0;
        }
        .dashboard-tabs {
            max-width: 28rem;
            margin-bottom: 2rem;
        }
        .dashboard-tab-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.5rem;
            background-color: #f3f4f6;
            padding: 0.25rem;
            border-radius: 0.5rem;
        }
        .dashboard-tab-trigger {
            padding: 0.5rem 1rem;
            font-weight: 500;
            font-size: 0.875rem;
            color: #6b7280;
            background-color: transparent;
            border: none;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
        }
        .dashboard-tab-trigger.active {
            background-color: white;
            color: #111827;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        .dashboard-card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }
        .dashboard-card-header {
            padding: 1.5rem;
            border-bottom: 1px solid #f3f4f6;
        }
        .dashboard-card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #111827;
            margin: 0 0 0.25rem 0;
        }
        .dashboard-card-description {
            font-size: 0.875rem;
            color: #6b7280;
            margin: 0;
        }
        .dashboard-card-content {
            padding: 1.5rem;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .job-stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
            text-align: center;
        }
        .stat-icon {
            width: 3rem;
            height: 3rem;
            margin: 0 auto 1rem;
            background: #3b82f6;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }
        .stat-icon.applications {
            background: #10b981;
        }
        .stat-icon.avg {
            background: #f59e0b;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #111827;
            margin: 0;
        }
        .stat-label {
            font-size: 0.875rem;
            color: #6b7280;
            margin: 0.5rem 0 0 0;
        }
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        .btn-primary {
            background: #dc2626;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.375rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: background-color 0.2s ease;
        }
        .btn-primary:hover {
            background: #b91c1c;
            color: white;
        }
        .btn-secondary {
            background: white;
            color: #374151;
            padding: 0.75rem 1.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
        }
        .btn-secondary:hover {
            background: #f9fafb;
            color: #374151;
        }
        @media (max-width: 640px) {
            .admin-dashboard-container {
                padding: 1.5rem 0.75rem;
            }
            .dashboard-title {
                font-size: 1.5rem;
            }
            .job-stats-grid {
                grid-template-columns: 1fr;
            }
            .action-buttons {
                flex-direction: column;
            }
        }
        </style>
        
        <div class="admin-dashboard-container">
            <div class="dashboard-inner">
                <div class="dashboard-header">
                    <h1 class="dashboard-title">Admin Dashboard</h1>
                    <p class="dashboard-subtitle">Manage jobs, applications, and system operations</p>
                </div>
                
                <div class="dashboard-tabs">
                    <div class="dashboard-tab-list">
                        <button class="dashboard-tab-trigger active" data-tab="jobs">Job Management</button>
                        <button class="dashboard-tab-trigger" data-tab="applications">Applications</button>
                    </div>
                </div>
                
                <!-- Job Management Tab -->
                <div id="jobs-tab" class="tab-content active">
                    <div class="dashboard-card">
                        <div class="dashboard-card-header">
                            <h3 class="dashboard-card-title">Job Management</h3>
                            <p class="dashboard-card-description">Create, edit, and manage job postings</p>
                        </div>
                        <div class="dashboard-card-content">
                            <div class="job-stats-grid">
                                <div class="stat-card">
                                    <div class="stat-icon">📋</div>
                                    <div class="stat-number"><?php echo esc_html($stats['total']); ?></div>
                                    <p class="stat-label">Total Jobs</p>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-icon applications">✅</div>
                                    <div class="stat-number"><?php echo esc_html($stats['published']); ?></div>
                                    <p class="stat-label">Published</p>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-icon avg">📝</div>
                                    <div class="stat-number"><?php echo esc_html($stats['draft']); ?></div>
                                    <p class="stat-label">Draft</p>
                                </div>
                            </div>
                            
                            <div class="action-buttons">
                                <a href="<?php echo home_url('/dashboard/positions/create'); ?>" class="btn-primary">
                                    ➕ Add New Job
                                </a>
                                <a href="<?php echo home_url('/dashboard/positions'); ?>" class="btn-secondary">
                                    📋 Manage Jobs
                                </a>
                                <a href="<?php echo home_url('/dashboard/locations'); ?>" class="btn-secondary">
                                    📍 Manage Locations
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Applications Tab -->
                <div id="applications-tab" class="tab-content">
                    <div class="dashboard-card">
                        <div class="dashboard-card-header">
                            <h3 class="dashboard-card-title">Application Management</h3>
                            <p class="dashboard-card-description">Review applications and update their status</p>
                        </div>
                        <div class="dashboard-card-content">
                            <div class="job-stats-grid">
                                <div class="stat-card">
                                    <div class="stat-icon">👥</div>
                                    <div class="stat-number"><?php echo esc_html($application_stats['total']); ?></div>
                                    <p class="stat-label">Total Applications</p>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-icon applications">⏳</div>
                                    <div class="stat-number"><?php echo esc_html(isset($application_stats['by_status']['pending']) ? $application_stats['by_status']['pending'] : 0); ?></div>
                                    <p class="stat-label">Pending Review</p>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-icon avg">📈</div>
                                    <div class="stat-number"><?php echo esc_html($application_stats['recent']); ?></div>
                                    <p class="stat-label">This Month</p>
                                </div>
                            </div>
                            
                            <div class="action-buttons">
                                <a href="<?php echo home_url('/dashboard/applications'); ?>" class="btn-primary">
                                    👁️ Review Applications
                                </a>
                                <a href="<?php echo home_url('/dashboard/applications?status=pending'); ?>" class="btn-secondary">
                                    ⏳ Pending Applications
                                </a>
                                <a href="<?php echo home_url('/dashboard/reports'); ?>" class="btn-secondary">
                                    📊 View Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('.dashboard-tab-trigger').on('click', function() {
                var tabId = $(this).data('tab');
                
                // Update tab buttons
                $('.dashboard-tab-trigger').removeClass('active');
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
            max-width: 900px;
            margin: 20px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .careers-position-form h1 {
            color: #2c3e50;
            margin-bottom: 30px;
            text-align: center;
            font-size: 28px;
        }
        .form-row {
            margin-bottom: 25px;
        }
        .form-row label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 14px;
        }
        .form-row input, .form-row textarea, .form-row select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }
        .form-row input:focus, .form-row textarea:focus, .form-row select:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        .form-row small {
            display: block;
            margin-top: 5px;
            color: #666;
            font-size: 12px;
            font-style: italic;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e1e5e9;
        }
        .button {
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
        }
        .button-primary {
            background: #3498db;
            color: white;
        }
        .button-primary:hover {
            background: #2980b9;
            transform: translateY(-1px);
        }
        .button:not(.button-primary) {
            background: #95a5a6;
            color: white;
        }
        .button:not(.button-primary):hover {
            background: #7f8c8d;
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
        <div class="careers-position-form">
            <h1>Edit Job Position</h1>
            
            <form id="careers-position-edit-form" method="post" action="">
                <?php wp_nonce_field('careers_position_action', 'careers_nonce'); ?>
                <input type="hidden" name="operation" value="update">
                <input type="hidden" name="position_id" value="<?php echo esc_attr($position->id); ?>">
                
                <div class="form-row">
                    <label for="position_name">Position Name *</label>
                    <input type="text" id="position_name" name="position_name" 
                           value="<?php echo esc_attr($position->position_name); ?>" required>
                </div>
                
                <div class="form-row">
                    <label for="location">Location *</label>
                    <select id="location" name="location" required>
                        <option value="">Select Location</option>
                        <?php foreach ($locations as $location): ?>
                            <option value="<?php echo esc_attr($location->name); ?>"
                                    <?php selected($position->location, $location->name); ?>>
                                <?php echo esc_html($location->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <label for="position_overview">Position Overview</label>
                    <textarea id="position_overview" name="position_overview" rows="4"><?php echo esc_textarea($position->position_overview); ?></textarea>
                </div>
                
                <div class="form-row">
                    <label for="responsibilities">Responsibilities</label>
                    <textarea id="responsibilities" name="responsibilities" rows="6"><?php echo esc_textarea($position->responsibilities); ?></textarea>
                    <small>Enter one responsibility per line. These will be displayed as a bulleted list.</small>
                </div>
                
                <div class="form-row">
                    <label for="requirements">Requirements</label>
                    <textarea id="requirements" name="requirements" rows="6"><?php echo esc_textarea($position->requirements); ?></textarea>
                    <small>Enter one requirement per line. These will be displayed as a bulleted list.</small>
                </div>
                
                <div class="form-row">
                    <label for="equipment">Equipment Used</label>
                    <textarea id="equipment" name="equipment" rows="4"><?php echo esc_textarea($position->equipment); ?></textarea>
                    <small>Enter one piece of equipment per line. These will be displayed as a bulleted list.</small>
                </div>
                
                <div class="form-row">
                    <label for="license_info">State License Info</label>
                    <textarea id="license_info" name="license_info" rows="3"><?php echo esc_textarea($position->license_info); ?></textarea>
                    <small>HTML tags are allowed for formatting.</small>
                </div>
                
                <div class="form-row">
                    <label>
                        <input type="checkbox" id="has_vehicle" name="has_vehicle" value="1"
                               <?php checked($position->has_vehicle, 1); ?>>
                        Company Vehicle Provided
                    </label>
                </div>
                
                <div class="form-row" id="vehicle_description_row" 
                     style="<?php echo $position->has_vehicle ? '' : 'display: none;'; ?>">
                    <label for="vehicle_description">Vehicle Description</label>
                    <input type="text" id="vehicle_description" name="vehicle_description" 
                           value="<?php echo esc_attr($position->vehicle_description); ?>">
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
        <div class="careers-position-management">
            <h1>Manage Job Positions</h1>
            
            <div class="management-actions">
                <a href="<?php echo home_url('/dashboard/positions/create'); ?>" class="button button-primary">Create New Position</a>
            </div>
            
            <table class="positions-table">
                <thead>
                    <tr>
                        <th>Position Name</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($positions)): ?>
                        <tr>
                            <td colspan="5">No positions found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($positions as $position): ?>
                            <tr>
                                <td><?php echo esc_html($position->position_name); ?></td>
                                <td><?php echo esc_html($position->location); ?></td>
                                <td>
                                    <span class="status-<?php echo esc_attr($position->status); ?>">
                                        <?php echo esc_html(ucfirst($position->status)); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html(date('M j, Y', strtotime($position->created_at))); ?></td>
                                <td>
                                    <a href="<?php echo home_url('/dashboard/positions/edit/' . esc_attr($position->id)); ?>" 
                                       class="button button-small">Edit</a>
                                    <a href="<?php echo home_url('/open-positions/' . esc_attr($position->id)); ?>" 
                                       class="button button-small" target="_blank">View</a>
                                    <button class="button button-small delete-position" 
                                            data-id="<?php echo esc_attr($position->id); ?>">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
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