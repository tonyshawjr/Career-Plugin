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
        
        // Dashboard routing now handled by CareersPageHandler
        
        // Add dashboard shortcode
        add_shortcode('careers_form', array($this, 'job_form_shortcode'));
    }
    
    
    // Old routing methods removed - now handled by CareersPageHandler
    
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
        <div class="careers-dashboard-container">
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
                        <div class="status-number"><?php echo esc_html($application_stats['by_status']['pending'] ?? 0); ?></div>
                        <div class="status-label">Pending</div>
                    </div>
                    <div class="status-card">
                        <div class="status-number"><?php echo esc_html($application_stats['by_status']['reviewed'] ?? 0); ?></div>
                        <div class="status-label">Reviewed</div>
                    </div>
                    <div class="status-card">
                        <div class="status-number"><?php echo esc_html($application_stats['by_status']['interviewing'] ?? 0); ?></div>
                        <div class="status-label">Interviewing</div>
                    </div>
                    <div class="status-card">
                        <div class="status-number"><?php echo esc_html($application_stats['by_status']['hired'] ?? 0); ?></div>
                        <div class="status-label">Hired</div>
                    </div>
                    <div class="status-card">
                        <div class="status-number"><?php echo esc_html($application_stats['by_status']['rejected'] ?? 0); ?></div>
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
        <div class="careers-dashboard-container">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Create New Job Position</h1>
                <p class="dashboard-subtitle">Add a new position to your careers system</p>
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
        <div class="careers-dashboard-container">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Edit Job Position</h1>
                <p class="dashboard-subtitle">Update your position details</p>
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
        
        /* Filter Section */
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
            color: white !important;
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
            gap: 1rem;
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
        
        /* Pagination */
        .careers-dashboard-container .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
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
        
        <div class="careers-dashboard-container">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Manage Job Positions</h1>
                <div class="header-actions">
                    <a href="<?php echo home_url('/dashboard/positions/create'); ?>" class="create-button">
                        Create New Position
                    </a>
                    <a href="<?php echo home_url('/dashboard/locations'); ?>" class="create-button secondary">
                        Manage Locations
                    </a>
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
                            <a href="<?php echo home_url('/dashboard/positions'); ?>" class="filter-button clear-filters">Clear</a>
                        </div>
                    </div>
                </form>
            </div>
            
            <?php if (empty($positions)): ?>
                <div class="empty-state">
                    <h3><?php echo $search_query || $status_filter || $job_type_filter || $location_filter ? 'No positions found' : 'No positions yet'; ?></h3>
                    <p><?php echo $search_query || $status_filter || $job_type_filter || $location_filter ? 'Try adjusting your filters.' : 'Create your first job position to get started.'; ?></p>
                    <?php if (!$search_query && !$status_filter && !$job_type_filter && !$location_filter): ?>
                        <a href="<?php echo home_url('/dashboard/positions/create'); ?>" class="create-button">
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
                
                <!-- Positions Grid -->
                <div class="positions-grid">
                    <?php foreach ($positions as $position): ?>
                        <div class="position-card">
                            <input type="checkbox" class="position-checkbox" value="<?php echo esc_attr($position->id); ?>">
                            <div class="position-info">
                                <div class="position-info-item">
                                    <div class="position-info-label">Job Title</div>
                                    <h3 class="position-name"><?php echo esc_html($position->position_name); ?></h3>
                                    <div class="position-location"><?php echo esc_html($position->location); ?></div>
                                </div>
                                <div class="position-info-item">
                                    <div class="position-info-label">Status</div>
                                    <span class="position-status <?php echo esc_attr($position->status); ?>">
                                        <?php echo esc_html(ucfirst($position->status)); ?>
                                    </span>
                                </div>
                                <div class="position-info-item">
                                    <div class="position-info-label">Job Type</div>
                                    <?php if (!empty($position->job_type)): ?>
                                        <span class="position-type"><?php echo esc_html($position->job_type); ?></span>
                                    <?php else: ?>
                                        <span class="position-type">Not specified</span>
                                    <?php endif; ?>
                                </div>
                                <div class="position-info-item">
                                    <div class="position-info-label">Posted Date</div>
                                    <div class="position-date">
                                        <?php echo esc_html(date('M j, Y', strtotime($position->created_at))); ?>
                                    </div>
                                </div>
                                <div class="position-info-item">
                                    <div class="position-info-label">Applications</div>
                                    <div class="position-apps">
                                        <?php echo esc_html(CareersApplicationDB::get_applications_count_by_job($position->id)); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="position-actions">
                                <a href="<?php echo home_url('/dashboard/positions/edit/' . esc_attr($position->id)); ?>" 
                                   class="action-button primary">Edit</a>
                                <a href="<?php echo home_url('/dashboard/positions/applications/' . esc_attr($position->id)); ?>" 
                                   class="action-button">Applications</a>
                                <a href="<?php echo home_url('/open-positions/' . esc_attr($position->id)); ?>" 
                                   class="action-button" target="_blank">View</a>
                                <button class="action-button danger delete-position" 
                                        data-id="<?php echo esc_attr($position->id); ?>">Delete</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
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
            $('#select-all').on('change', function() {
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
        
        <div class="careers-dashboard-container">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Manage Locations</h1>
                <div class="header-actions">
                    <a href="<?php echo home_url('/dashboard'); ?>" class="dashboard-action-btn">
                        Back to Dashboard
                    </a>
                    <a href="<?php echo home_url('/dashboard/jobs'); ?>" class="dashboard-action-btn">
                        Manage Jobs
                    </a>
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
                    <div class="locations-grid">
                        <?php foreach ($locations_by_state as $state => $cities): ?>
                            <div class="state-card">
                                <div class="state-header">
                                    <h4 class="state-title"><?php echo esc_html($state); ?></h4>
                                    <div class="state-count"><?php echo count($cities); ?> <?php echo count($cities) === 1 ? 'city' : 'cities'; ?></div>
                                </div>
                                <div class="cities-grid">
                                    <?php foreach ($cities as $location): ?>
                                        <div class="city-item">
                                            <span class="city-name"><?php echo esc_html($location->city); ?></span>
                                            <button class="delete-location" data-id="<?php echo esc_attr($location->id); ?>">
                                                Delete
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
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
}