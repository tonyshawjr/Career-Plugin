<?php
/**
 * Careers Dashboard Handler
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CareersDashboard {
    
    public function __construct() {
        add_action('wp_ajax_careers_dashboard_action', array($this, 'handle_dashboard_action'));
        
        // Handle frontend dashboard routing
        add_action('wp', array($this, 'handle_dashboard_routing'));
        
        // Add dashboard shortcode
        add_shortcode('careers_dashboard', array($this, 'dashboard_shortcode'));
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
            // Redirect to login
            wp_redirect(wp_login_url(home_url('/dashboard')));
            exit;
        }
        
        // Get dashboard type based on user role
        $dashboard_type = CareersUserRoles::get_user_dashboard_type();
        
        if ($dashboard_type === 'guest') {
            // User doesn't have career system access
            wp_redirect(home_url());
            exit;
        }
        
        // Get the dashboard page and action from query vars
        global $wp;
        $dashboard_page = get_query_var('careers_dashboard', 'main');
        $action = get_query_var('careers_action', '');
        $id = get_query_var('careers_id', '');
        
        // Debug output - remove this after testing
        if (current_user_can('administrator')) {
            echo "<!-- DEBUG: Page: $dashboard_page, Action: $action, ID: $id, Request: " . $wp->request . " -->";
            error_log("Dashboard Debug - Page: $dashboard_page, Action: $action, ID: $id, Request: " . $wp->request);
        }
        
        // Route to appropriate dashboard view
        $this->route_dashboard_view($dashboard_type, $dashboard_page, $action, $id);
        exit;
    }
    
    /**
     * Route dashboard views based on page and action
     */
    private function route_dashboard_view($dashboard_type, $page, $action, $id) {
        get_header();
        
        // Admin routing
        if ($dashboard_type === 'admin') {
            switch ($page) {
                case 'jobs':
                    if ($action === 'create') {
                        $this->render_job_creation_form();
                    } elseif ($action === 'edit' && $id) {
                        $this->render_job_edit_form($id);
                    } else {
                        $this->render_job_management();
                    }
                    break;
                
                case 'applications':
                    if ($id) {
                        $this->render_job_applications($id);
                    } else {
                        $this->render_application_management();
                    }
                    break;
                
                case 'analytics':
                    $this->render_analytics_dashboard();
                    break;
                
                default:
                    $this->render_admin_dashboard();
                    break;
            }
        } else {
            // Applicant routing
            switch ($page) {
                case 'profile':
                    $this->render_profile_management();
                    break;
                
                default:
                    $this->render_applicant_dashboard();
                    break;
            }
        }
        
        get_footer();
    }
    
    /**
     * Render dashboard page content
     */
    private function render_dashboard_page($dashboard_type) {
        echo '<div class="careers-dashboard-container">';
        echo '<div class="container">';
        
        if ($dashboard_type === 'admin') {
            $this->render_admin_dashboard();
        } else {
            $this->render_applicant_dashboard();
        }
        
        echo '</div>';
        echo '</div>';
    }
    
    /**
     * Dashboard shortcode
     */
    public function dashboard_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<p>Please <a href="' . wp_login_url() . '">log in</a> to view your dashboard.</p>';
        }
        
        $dashboard_type = CareersUserRoles::get_user_dashboard_type();
        
        if ($dashboard_type === 'guest') {
            return '<p>You do not have access to the careers dashboard.</p>';
        }
        
        ob_start();
        
        if ($dashboard_type === 'admin') {
            $this->render_admin_dashboard();
        } else {
            $this->render_applicant_dashboard();
        }
        
        return ob_get_clean();
    }
    
    /**
     * Render admin dashboard - Clean, professional design
     */
    private function render_admin_dashboard() {
        $current_user = wp_get_current_user();
        $stats = CareersApplicationDB::get_stats();
        
        // Get all jobs for the table
        $jobs = get_posts([
            'post_type' => 'career_job',
            'post_status' => ['publish', 'draft'],
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        
        // Calculate job applications counts
        global $wpdb;
        $job_application_counts = [];
        foreach ($jobs as $job) {
            $count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}careers_applications WHERE job_id = %d",
                $job->ID
            ));
            $job_application_counts[$job->ID] = $count;
        }
        
        $total_applications = array_sum($job_application_counts);
        $avg_applications_per_job = count($jobs) > 0 ? round($total_applications / count($jobs), 1) : 0;
        $active_jobs = count(array_filter($jobs, function($job) { return $job->post_status === 'publish'; }));
        
        ?>
        <div class="dashboard-container">
            <div class="dashboard-inner">
                <!-- Header Section -->
                <div class="dashboard-header">
                    <h1 class="dashboard-title">Admin Dashboard</h1>
                    <p class="dashboard-subtitle">
                        Manage jobs, applications, and system operations
                    </p>
                </div>

                <!-- Stats Overview -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                    <!-- Total Jobs -->
                    <div class="card">
                        <div style="display: flex; align-items: center;">
                            <div style="width: 3rem; height: 3rem; background: #dbeafe; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                                <svg style="width: 1.5rem; height: 1.5rem; color: #3b82f6;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <div style="font-size: 2rem; font-weight: 700; color: #111827; line-height: 1;"><?php echo count($jobs); ?></div>
                                <div style="font-size: 0.875rem; color: #6b7280; margin-top: 0.25rem;">Total Jobs</div>
                            </div>
                        </div>
                    </div>

                    <!-- Active Jobs -->
                    <div class="card">
                        <div style="display: flex; align-items: center;">
                            <div style="width: 3rem; height: 3rem; background: #d1fae5; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                                <svg style="width: 1.5rem; height: 1.5rem; color: #10b981;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <div style="font-size: 2rem; font-weight: 700; color: #111827; line-height: 1;"><?php echo $active_jobs; ?></div>
                                <div style="font-size: 0.875rem; color: #6b7280; margin-top: 0.25rem;">Active Jobs</div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Applications -->
                    <div class="card">
                        <div style="display: flex; align-items: center;">
                            <div style="width: 3rem; height: 3rem; background: #fef3c7; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                                <svg style="width: 1.5rem; height: 1.5rem; color: #f59e0b;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <div style="font-size: 2rem; font-weight: 700; color: #111827; line-height: 1;"><?php echo $total_applications; ?></div>
                                <div style="font-size: 0.875rem; color: #6b7280; margin-top: 0.25rem;">Total Applications</div>
                            </div>
                        </div>
                    </div>

                    <!-- Average Applications per Job -->
                    <div class="card">
                        <div style="display: flex; align-items: center;">
                            <div style="width: 3rem; height: 3rem; background: rgba(191, 30, 45, 0.1); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                                <svg style="width: 1.5rem; height: 1.5rem; color: #BF1E2D;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <div>
                                <div style="font-size: 2rem; font-weight: 700; color: #111827; line-height: 1;"><?php echo $avg_applications_per_job; ?></div>
                                <div style="font-size: 0.875rem; color: #6b7280; margin-top: 0.25rem;">Avg per Job</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabbed Interface -->
                <div class="dashboard-tabs">
                    <div class="dashboard-tab-list">
                        <button class="dashboard-tab-trigger active" id="jobs-tab" data-tab="jobs">
                            Job Management
                        </button>
                        <button class="dashboard-tab-trigger" id="applications-tab" data-tab="applications">
                            Applications
                        </button>
                    </div>
                </div>

                <!-- Tab Content -->
                <div id="jobs-content" class="tab-content active">
                    <!-- Job Management Card -->
                    <div class="dashboard-card">
                        <div class="dashboard-card-header">
                            <h2 class="dashboard-card-title">Job Management</h2>
                            <p class="dashboard-card-description">Create, edit, and manage job postings</p>
                        </div>
                        <div class="dashboard-card-content">
                            <?php if (!empty($jobs)): ?>
                            <div class="table-wrapper">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Job Title</th>
                                            <th>Location</th>
                                            <th>Status</th>
                                            <th>Applications</th>
                                            <th>Posted</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($jobs as $job): ?>
                                            <?php
                                            $location = get_post_meta($job->ID, '_job_location', true);
                                            $job_type = get_post_meta($job->ID, '_job_type', true);
                                            $application_count = $job_application_counts[$job->ID] ?? 0;
                                            $post_date = date('M j, Y', strtotime($job->post_date));
                                            $status = $job->post_status === 'publish' ? 'Active' : 'Draft';
                                            $status_color = $job->post_status === 'publish' ? '#10b981' : '#f59e0b';
                                            ?>
                                            <tr>
                                                <td>
                                                    <div>
                                                        <div class="table-job-title"><?php echo esc_html($job->post_title); ?></div>
                                                        <div class="table-job-type"><?php echo esc_html($job_type); ?></div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="table-location"><?php echo esc_html($location); ?></div>
                                                </td>
                                                <td>
                                                    <span class="table-status-badge <?php echo $status === 'Active' ? 'table-status-active' : 'table-status-draft'; ?>">
                                                        <?php echo $status; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="table-applications">
                                                        <?php echo $application_count; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="table-date"><?php echo esc_html($post_date); ?></div>
                                                </td>
                                                <td>
                                                    <div class="table-actions">
                                                        <a href="<?php echo home_url('/dashboard/jobs/edit/' . $job->ID); ?>" 
                                                           class="table-action-btn"
                                                           title="Edit Job">
                                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"></path>
                                                            </svg>
                                                        </a>
                                                        <a href="<?php echo home_url('/job/' . $job->post_name . '/'); ?>" 
                                                           target="_blank"
                                                           class="table-action-btn"
                                                           title="View Job">
                                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"></path>
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path>
                                                            </svg>
                                                        </a>
                                                        <a href="<?php echo home_url('/dashboard/jobs/applications/' . $job->ID); ?>" 
                                                           class="table-action-btn"
                                                           title="View Applicants">
                                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"></path>
                                                            </svg>
                                                        </a>
                                                        <button onclick="deleteJob(<?php echo $job->ID; ?>)" 
                                                                class="table-action-btn delete"
                                                                title="Delete Job">
                                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                        <div class="card table-empty-state">
                            <svg class="table-empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2h8z"></path>
                            </svg>
                            <h3 style="font-size: 1.25rem; font-weight: 600; color: #111827; margin: 0 0 0.5rem 0;">No jobs yet</h3>
                            <p style="color: #6b7280; margin: 0 0 1.5rem 0;">Create your first job posting to get started.</p>
                            <button onclick="window.location.href='<?php echo home_url('/dashboard/jobs/create'); ?>'" 
                                    class="btn-primary">
                                <svg style="width: 1rem; height: 1rem; margin-right: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Create Your First Job
                            </button>
                        </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Applications Content -->
                <div id="applications-content" class="tab-content" style="display: none;">
                    <!-- Application Management Card -->
                    <div class="dashboard-card">
                        <div class="dashboard-card-header">
                            <h2 class="dashboard-card-title">Application Management</h2>
                            <p class="dashboard-card-description">Review applications and update their status</p>
                        </div>
                        <div class="dashboard-card-content">
                            <div class="table-empty-state">
                                <svg class="table-empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <h3 style="font-size: 1.25rem; font-weight: 600; color: #111827; margin: 0 0 0.5rem 0;">Applications Coming Soon</h3>
                                <p style="color: #6b7280; margin: 0;">Application management features will be available here.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Modern tab switching functionality
            $('.dashboard-tab-trigger').on('click', function() {
                // Remove active class from all tabs
                $('.dashboard-tab-trigger').removeClass('active');
                $('.tab-content').hide();
                
                // Add active class to clicked tab
                $(this).addClass('active');
                
                // Show corresponding content
                const tabType = $(this).data('tab');
                $('#' + tabType + '-content').show();
            });
        });

        function deleteJob(jobId) {
            if (confirm('Are you sure you want to delete this job? This action cannot be undone.')) {
                jQuery.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'delete_career_job',
                        job_id: jobId,
                        nonce: '<?php echo wp_create_nonce('delete_job_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Error deleting job: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('Error deleting job. Please try again.');
                    }
                });
            }
        }
        </script>
        <?php
    }
    
    /**
     * Render applicant dashboard
     */
    private function render_applicant_dashboard() {
        $current_user = wp_get_current_user();
        $user_applications = $this->get_user_applications(get_current_user_id());
        
        ?>
        <div class="careers-applicant-dashboard">
            <div class="dashboard-header">
                <h1>My Career Dashboard</h1>
                <p>Welcome back, <?php echo esc_html($current_user->display_name); ?>!</p>
            </div>
            
            <div class="dashboard-stats">
                <div class="stat-cards">
                    <div class="stat-card">
                        <h3><?php echo count($user_applications); ?></h3>
                        <p>Applications Submitted</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $this->count_applications_by_status($user_applications, 'pending'); ?></h3>
                        <p>Under Review</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $this->count_applications_by_status($user_applications, 'interviewing'); ?></h3>
                        <p>Interviews Scheduled</p>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-actions">
                <div class="action-buttons">
                    <a href="<?php echo home_url('/open-positions'); ?>" class="btn btn-primary">
                        <span class="icon">🔍</span> Browse Jobs
                    </a>
                    <a href="<?php echo home_url('/dashboard/profile'); ?>" class="btn btn-secondary">
                        <span class="icon">👤</span> Update Profile
                    </a>
                </div>
            </div>
            
            <div class="dashboard-content">
                <div class="dashboard-section full-width">
                    <h2>My Applications</h2>
                    <?php $this->render_user_applications($user_applications); ?>
                </div>
            </div>
            
            <div class="dashboard-tips">
                <h2>Career Tips</h2>
                <div class="tips-grid">
                    <div class="tip-item">
                        <h3>🎯 Application Tips</h3>
                        <p>Tailor your resume for each position and highlight relevant certifications.</p>
                    </div>
                    <div class="tip-item">
                        <h3>📞 Interview Prep</h3>
                        <p>Research common medical imaging interview questions and practice your responses.</p>
                    </div>
                    <div class="tip-item">
                        <h3>🏥 Industry Insights</h3>
                        <p>Stay updated on mobile imaging technology and healthcare industry trends.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .careers-applicant-dashboard {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .dashboard-header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .dashboard-header h1 {
            color: #c8102e;
            margin-bottom: 10px;
        }
        
        .stat-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 4px solid #c8102e;
        }
        
        .stat-card h3 {
            font-size: 2.5em;
            margin: 0 0 10px 0;
            color: #c8102e;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #c8102e;
            color: white;
        }
        
        .btn-primary:hover {
            background: #a00d25;
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #545b62;
            color: white;
        }
        
        .dashboard-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .dashboard-section.full-width {
            width: 100%;
        }
        
        .dashboard-section h2 {
            color: #c8102e;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .tips-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .tip-item {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .tip-item h3 {
            color: #c8102e;
            margin-bottom: 10px;
        }
        
        .applications-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .applications-table th,
        .applications-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .applications-table th {
            background: #f8f9fa;
            font-weight: bold;
            color: #c8102e;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-reviewing { background: #d1ecf1; color: #0c5460; }
        .status-interviewing { background: #e2e3e5; color: #383d41; }
        .status-hired { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        
        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .applications-table {
                font-size: 0.9em;
            }
        }
        </style>
        <?php
    }
    
    /**
     * Handle dashboard AJAX actions
     */
    public function handle_dashboard_action() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'careers_nonce')) {
            wp_die(__('Security check failed.', 'careers-manager'));
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(__('You must be logged in.', 'careers-manager'));
        }
        
        $action = sanitize_text_field($_POST['dashboard_action']);
        
        switch ($action) {
            case 'withdraw_application':
                $this->withdraw_application();
                break;
            default:
                wp_send_json_error(__('Invalid action.', 'careers-manager'));
        }
    }
    
    /**
     * Withdraw application
     */
    private function withdraw_application() {
        $application_id = intval($_POST['application_id']);
        $user_id = get_current_user_id();
        
        // Get application and verify ownership
        $application = CareersApplicationDB::get_application($application_id);
        
        if (!$application || $application->user_id != $user_id) {
            wp_send_json_error(__('Application not found.', 'careers-manager'));
        }
        
        // Only allow withdrawal of pending applications
        if ($application->status !== 'pending') {
            wp_send_json_error(__('You can only withdraw pending applications.', 'careers-manager'));
        }
        
        // Update status to cancelled
        $result = CareersApplicationDB::update_status($application_id, 'rejected');
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        wp_send_json_success(__('Application withdrawn successfully.', 'careers-manager'));
    }
    
    /**
     * Get user dashboard data
     */
    public static function get_dashboard_data($user_id) {
        $applications = CareersApplicationDB::get_user_applications($user_id);
        
        $stats = array(
            'total_applications' => count($applications),
            'pending' => 0,
            'reviewed' => 0,
            'interviewing' => 0,
            'hired' => 0,
            'rejected' => 0
        );
        
        foreach ($applications as $application) {
            if (isset($stats[$application->status])) {
                $stats[$application->status]++;
            }
        }
        
        return array(
            'applications' => $applications,
            'stats' => $stats
        );
    }
    
    /**
     * Render dashboard stats
     */
    public static function render_dashboard_stats($stats) {
        ob_start();
        ?>
        <div class="careers-dashboard-stats">
            <div class="careers-stat-card">
                <div class="careers-stat-number"><?php echo intval($stats['total_applications']); ?></div>
                <div class="careers-stat-label"><?php _e('Total Applications', 'careers-manager'); ?></div>
            </div>
            
            <div class="careers-stat-card">
                <div class="careers-stat-number"><?php echo intval($stats['pending']); ?></div>
                <div class="careers-stat-label"><?php _e('Pending', 'careers-manager'); ?></div>
            </div>
            
            <div class="careers-stat-card">
                <div class="careers-stat-number"><?php echo intval($stats['interviewing']); ?></div>
                <div class="careers-stat-label"><?php _e('Interviewing', 'careers-manager'); ?></div>
            </div>
            
            <div class="careers-stat-card">
                <div class="careers-stat-number"><?php echo intval($stats['hired']); ?></div>
                <div class="careers-stat-label"><?php _e('Hired', 'careers-manager'); ?></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render application timeline
     */
    public static function render_application_timeline($application) {
        $statuses = array(
            'pending' => __('Application Submitted', 'careers-manager'),
            'reviewed' => __('Application Reviewed', 'careers-manager'),
            'interviewing' => __('Interview Stage', 'careers-manager'),
            'hired' => __('Hired', 'careers-manager'),
            'rejected' => __('Application Closed', 'careers-manager')
        );
        
        ob_start();
        ?>
        <div class="careers-application-timeline">
            <?php foreach ($statuses as $status => $label): ?>
                <div class="careers-timeline-item <?php echo ($application->status === $status) ? 'active' : ''; ?> <?php echo (array_search($status, array_keys($statuses)) < array_search($application->status, array_keys($statuses))) ? 'completed' : ''; ?>">
                    <div class="careers-timeline-marker"></div>
                    <div class="careers-timeline-content">
                        <div class="careers-timeline-label"><?php echo esc_html($label); ?></div>
                        <?php if ($application->status === $status): ?>
                            <div class="careers-timeline-date"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($application->updated_at))); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Get user applications
     */
    private function get_user_applications($user_id) {
        return CareersApplicationDB::get_applications(array(
            'user_id' => $user_id,
            'limit' => 50
        ));
    }
    
    /**
     * Count applications by status
     */
    private function count_applications_by_status($applications, $status) {
        $count = 0;
        foreach ($applications as $application) {
            if ($application->status === $status) {
                $count++;
            }
        }
        return $count;
    }
    
    /**
     * Render user applications table
     */
    private function render_user_applications($applications) {
        if (empty($applications)) {
            echo '<p>You haven\'t submitted any applications yet. <a href="' . home_url('/open-positions') . '">Browse open positions</a> to get started!</p>';
            return;
        }
        
        echo '<table class="applications-table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Position</th>';
        echo '<th>Status</th>';
        echo '<th>Applied Date</th>';
        echo '<th>Last Updated</th>';
        echo '<th>Actions</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        foreach ($applications as $application) {
            $status_class = 'status-' . str_replace(' ', '-', strtolower($application->status));
            $status_label = ucfirst(str_replace('_', ' ', $application->status));
            
            echo '<tr>';
            echo '<td><strong>' . esc_html($application->job_title) . '</strong></td>';
            echo '<td><span class="status-badge ' . esc_attr($status_class) . '">' . esc_html($status_label) . '</span></td>';
            echo '<td>' . esc_html(date_i18n(get_option('date_format'), strtotime($application->submitted_at))) . '</td>';
            echo '<td>' . esc_html(date_i18n(get_option('date_format'), strtotime($application->updated_at))) . '</td>';
            echo '<td>';
            
            if ($application->status === 'pending') {
                echo '<button class="btn-link withdraw-application" data-application-id="' . esc_attr($application->id) . '">Withdraw</button>';
            } else {
                echo '<span class="text-muted">—</span>';
            }
            
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
    }
    
    /**
     * Render recent applications widget
     */
    private function render_recent_applications_widget() {
        $recent_applications = CareersApplicationDB::get_applications(array('limit' => 5));
        
        if (empty($recent_applications)) {
            echo '<p>No recent applications.</p>';
            return;
        }
        
        echo '<div class="recent-applications-list">';
        foreach ($recent_applications as $application) {
            $status_class = 'status-' . str_replace(' ', '-', strtolower($application->status));
            echo '<div class="recent-application-item">';
            echo '<div class="application-info">';
            echo '<strong>' . esc_html($application->job_title) . '</strong>';
            echo '<small>' . esc_html($application->display_name) . ' • ' . esc_html(date_i18n('M j', strtotime($application->submitted_at))) . '</small>';
            echo '</div>';
            echo '<span class="status-badge ' . esc_attr($status_class) . '">' . esc_html(ucfirst($application->status)) . '</span>';
            echo '</div>';
        }
        echo '</div>';
    }
    
    /**
     * Render job performance widget
     */
    private function render_job_performance_widget($jobs_data) {
        if (empty($jobs_data)) {
            echo '<p>No job performance data available.</p>';
            return;
        }
        
        echo '<div class="job-performance-list">';
        foreach ($jobs_data as $job) {
            echo '<div class="job-performance-item">';
            echo '<div class="job-info">';
            echo '<strong>' . esc_html($job->post_title) . '</strong>';
            echo '<small>' . esc_html($job->count) . ' applications</small>';
            echo '</div>';
            echo '<div class="job-actions">';
            echo '<a href="' . home_url('/dashboard/jobs/' . $job->ID . '/edit') . '" class="action-link">Edit</a>';
            echo '<a href="' . home_url('/dashboard/jobs/' . $job->ID . '/applicants') . '" class="action-link">View Applicants</a>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    }
    
    /**
     * Render job creation form
     */
    private function render_job_creation_form() {
        ?>
        <div class="bg-gray-50 min-h-screen">
            <div class="container mx-auto px-4 py-12 sm:px-6 lg:px-8">
                <!-- Page Header -->
                <div class="mb-8">
                    <nav class="mb-4">
                        <ol class="flex space-x-2 text-sm text-gray-600">
                            <li>
                                <a href="<?php echo home_url('/dashboard'); ?>" class="hover:text-brand-red transition">Dashboard</a>
                            </li>
                            <li>/</li>
                            <li>
                                <a href="<?php echo home_url('/dashboard/jobs'); ?>" class="hover:text-brand-red transition">Jobs</a>
                            </li>
                            <li>/</li>
                            <li class="text-gray-900 font-medium">Create New Job</li>
                        </ol>
                    </nav>
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">Create New Job</h1>
                    <p class="text-lg text-gray-600">
                        Add a new position to your career listings.
                    </p>
                </div>

                <!-- Job Creation Form -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sm:p-8">
                    <form id="job-creation-form" method="post" action="<?php echo admin_url('admin-ajax.php'); ?>">
                        <?php wp_nonce_field('create_job_nonce', 'job_nonce'); ?>
                        <input type="hidden" name="action" value="create_career_job">
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Basic Information -->
                            <div class="lg:col-span-2">
                                <h2 class="text-xl font-semibold text-gray-900 mb-4">Basic Information</h2>
                            </div>
                            
                            <div>
                                <label for="job_title" class="block text-sm font-medium text-gray-700 mb-1">Job Title</label>
                                <input type="text" id="job_title" name="job_title" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-red focus:border-brand-red">
                            </div>
                            
                            <div>
                                <label for="job_location" class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                                <select id="job_location" name="job_location" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-red focus:border-brand-red">
                                    <option value="">Select Location</option>
                                    <optgroup label="Arizona">
                                        <option value="Phoenix">Phoenix</option>
                                        <option value="Tucson">Tucson</option>
                                        <option value="Mesa">Mesa</option>
                                        <option value="Scottsdale">Scottsdale</option>
                                    </optgroup>
                                    <optgroup label="North Carolina">
                                        <option value="Raleigh">Raleigh</option>
                                        <option value="Fayetteville">Fayetteville</option>
                                        <option value="Charlotte">Charlotte</option>
                                        <option value="Asheville">Asheville</option>
                                        <option value="Hickory">Hickory</option>
                                        <option value="Winston Salem">Winston Salem</option>
                                        <option value="Greensboro">Greensboro</option>
                                        <option value="Jacksonville">Jacksonville</option>
                                        <option value="New Bern">New Bern</option>
                                    </optgroup>
                                    <optgroup label="Texas">
                                        <option value="Dallas">Dallas</option>
                                        <option value="Houston">Houston</option>
                                        <option value="Austin">Austin</option>
                                        <option value="San Antonio">San Antonio</option>
                                        <option value="Corpus Christi">Corpus Christi</option>
                                        <option value="McAllen">McAllen</option>
                                        <option value="Lufkin">Lufkin</option>
                                        <option value="Nacogdoches">Nacogdoches</option>
                                    </optgroup>
                                    <optgroup label="Virginia">
                                        <option value="Roanoke">Roanoke</option>
                                    </optgroup>
                                    <optgroup label="Kentucky">
                                        <option value="Louisville">Louisville</option>
                                        <option value="Lexington">Lexington</option>
                                        <option value="Bowling Green">Bowling Green</option>
                                    </optgroup>
                                    <optgroup label="Georgia">
                                        <option value="Atlanta">Atlanta</option>
                                        <option value="Augusta">Augusta</option>
                                        <option value="Columbus">Columbus</option>
                                        <option value="Savannah">Savannah</option>
                                    </optgroup>
                                </select>
                            </div>
                            
                            <div>
                                <label for="job_type" class="block text-sm font-medium text-gray-700 mb-1">Job Type</label>
                                <select id="job_type" name="job_type" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-red focus:border-brand-red">
                                    <option value="">Select Job Type</option>
                                    <option value="Full-Time">Full-Time</option>
                                    <option value="Part-Time">Part-Time</option>
                                    <option value="Contract">Contract</option>
                                    <option value="Per Diem">Per Diem</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="job_modality" class="block text-sm font-medium text-gray-700 mb-1">Modality</label>
                                <select id="job_modality" name="job_modality" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-red focus:border-brand-red">
                                    <option value="">Select Modality</option>
                                    <option value="X-Ray">X-Ray</option>
                                    <option value="Ultrasound">Ultrasound</option>
                                    <option value="General">General</option>
                                </select>
                            </div>
                            
                            <div class="lg:col-span-2">
                                <label for="job_summary" class="block text-sm font-medium text-gray-700 mb-1">Job Summary</label>
                                <textarea id="job_summary" name="job_summary" rows="3" required
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-red focus:border-brand-red"
                                          placeholder="Brief summary of the position..."></textarea>
                            </div>
                            
                            <div class="lg:col-span-2">
                                <label for="job_description" class="block text-sm font-medium text-gray-700 mb-1">Job Description</label>
                                <textarea id="job_description" name="job_description" rows="6" required
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-red focus:border-brand-red"
                                          placeholder="Detailed description of the position, duties, and work environment..."></textarea>
                            </div>
                            
                            <!-- Requirements -->
                            <div class="lg:col-span-2 mt-6">
                                <h2 class="text-xl font-semibold text-gray-900 mb-4">Requirements & Qualifications</h2>
                            </div>
                            
                            <div class="lg:col-span-2">
                                <label for="job_requirements" class="block text-sm font-medium text-gray-700 mb-1">Requirements</label>
                                <textarea id="job_requirements" name="job_requirements" rows="4" required
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-red focus:border-brand-red"
                                          placeholder="List job requirements (one per line)"></textarea>
                                <p class="mt-1 text-sm text-gray-500">Enter each requirement on a new line</p>
                            </div>
                            
                            <div class="lg:col-span-2">
                                <label for="job_responsibilities" class="block text-sm font-medium text-gray-700 mb-1">Responsibilities</label>
                                <textarea id="job_responsibilities" name="job_responsibilities" rows="4" required
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-red focus:border-brand-red"
                                          placeholder="List job responsibilities (one per line)"></textarea>
                                <p class="mt-1 text-sm text-gray-500">Enter each responsibility on a new line</p>
                            </div>
                            
                            <div class="lg:col-span-2">
                                <label for="job_equipment" class="block text-sm font-medium text-gray-700 mb-1">Equipment Used</label>
                                <textarea id="job_equipment" name="job_equipment" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-red focus:border-brand-red"
                                          placeholder="List equipment used (one per line)"></textarea>
                                <p class="mt-1 text-sm text-gray-500">Enter each piece of equipment on a new line</p>
                            </div>
                            
                            <!-- Additional Information -->
                            <div class="lg:col-span-2 mt-6">
                                <h2 class="text-xl font-semibold text-gray-900 mb-4">Additional Information</h2>
                            </div>
                            
                            <div class="lg:col-span-2">
                                <label for="job_benefits" class="block text-sm font-medium text-gray-700 mb-1">Benefits</label>
                                <textarea id="job_benefits" name="job_benefits" rows="4"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-red focus:border-brand-red"
                                          placeholder="List job benefits (one per line)"></textarea>
                                <p class="mt-1 text-sm text-gray-500">Enter each benefit on a new line</p>
                            </div>
                            
                            <div class="lg:col-span-2">
                                <label for="state_licensing" class="block text-sm font-medium text-gray-700 mb-1">State Licensing Information</label>
                                <textarea id="state_licensing" name="state_licensing" rows="2"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-red focus:border-brand-red"
                                          placeholder="State-specific licensing requirements..."></textarea>
                            </div>
                            
                            <div>
                                <label for="job_status" class="block text-sm font-medium text-gray-700 mb-1">Job Status</label>
                                <select id="job_status" name="job_status" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-red focus:border-brand-red">
                                    <option value="publish">Active (Published)</option>
                                    <option value="draft">Draft</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Form Actions -->
                        <div class="mt-8 flex items-center justify-between pt-6 border-t border-gray-200">
                            <a href="<?php echo home_url('/dashboard/jobs'); ?>" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-red transition">
                                Cancel
                            </a>
                            <div class="flex space-x-3">
                                <button type="submit" name="save_as_draft" value="1"
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-red transition">
                                    Save as Draft
                                </button>
                                <button type="submit" name="publish_job" value="1"
                                        class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-brand-red hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-red transition">
                                    Publish Job
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#job-creation-form').on('submit', function(e) {
                e.preventDefault();
                
                var formData = new FormData(this);
                var submitButton = $(this).find('button[type="submit"]:focus');
                
                // Show loading state
                submitButton.prop('disabled', true).text('Creating...');
                
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            alert(response.data.message);
                            // Redirect to jobs page
                            window.location.href = response.data.redirect_url;
                        } else {
                            alert('Error: ' + response.data);
                            submitButton.prop('disabled', false).text(submitButton.hasClass('btn-primary') ? 'Publish Job' : 'Save as Draft');
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Error creating job. Please try again.');
                        submitButton.prop('disabled', false).text(submitButton.hasClass('btn-primary') ? 'Publish Job' : 'Save as Draft');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Render job edit form
     */
    private function render_job_edit_form($job_id) {
        // Get the job to edit
        $job = get_post($job_id);
        
        if (!$job || $job->post_type !== 'career_job') {
            echo '<div class="dashboard-container">
                    <div class="dashboard-inner">
                        <div class="dashboard-card">
                            <div class="dashboard-card-content with-padding">
                                <p style="color: #dc2626;">Job not found.</p>
                                <a href="' . home_url('/dashboard/jobs') . '" class="btn btn-primary">Back to Jobs</a>
                            </div>
                        </div>
                    </div>
                  </div>';
            return;
        }
        
        // Get current job meta - using same field names as creation form
        $job_title = $job->post_title;
        $job_description = $job->post_content;
        $job_summary = get_post_meta($job_id, 'job_summary', true);
        $job_requirements = get_post_meta($job_id, 'job_requirements', true);
        $job_responsibilities = get_post_meta($job_id, 'job_responsibilities', true);
        $job_benefits = get_post_meta($job_id, 'job_benefits', true);
        $job_equipment = get_post_meta($job_id, 'job_equipment', true);
        $location = get_post_meta($job_id, 'job_location', true);
        $job_type = get_post_meta($job_id, 'job_type', true);
        $job_modality = get_post_meta($job_id, 'job_modality', true);
        $salary_min = get_post_meta($job_id, 'salary_min', true);
        $salary_max = get_post_meta($job_id, 'salary_max', true);
        $experience_level = get_post_meta($job_id, 'experience_level', true);
        $state_licensing = get_post_meta($job_id, 'state_licensing', true);
        $application_deadline = get_post_meta($job_id, 'application_deadline', true);
        ?>
        
        <div class="bg-gray-50 min-h-screen">
            <div class="container mx-auto px-4 py-12 sm:px-6 lg:px-8">
                <!-- Page Header -->
                <div class="mb-8">
                    <nav class="mb-4">
                        <ol class="flex space-x-2 text-sm text-gray-600">
                            <li>
                                <a href="<?php echo home_url('/dashboard'); ?>" class="hover:text-brand-red transition">Dashboard</a>
                            </li>
                            <li>/</li>
                            <li>
                                <a href="<?php echo home_url('/dashboard/jobs'); ?>" class="hover:text-brand-red transition">Jobs</a>
                            </li>
                            <li>/</li>
                            <li class="text-gray-900 font-medium">Edit Job</li>
                        </ol>
                    </nav>
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">Edit Job: <?php echo esc_html($job_title); ?></h1>
                    <p class="text-lg text-gray-600">
                        Update the job posting details below.
                    </p>
                </div>

                <!-- Job Edit Form -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sm:p-8">
                    <form id="job-edit-form" method="post" action="<?php echo admin_url('admin-ajax.php'); ?>">
                        <?php wp_nonce_field('update_job_nonce', 'job_nonce'); ?>
                        <input type="hidden" name="action" value="update_career_job">
                        <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Basic Information -->
                            <div class="lg:col-span-2">
                                <h2 class="text-xl font-semibold text-gray-900 mb-4">Basic Information</h2>
                            </div>
                            
                            <div>
                                <label for="job_title" class="block text-sm font-medium text-gray-700 mb-1">Job Title</label>
                                <input type="text" id="job_title" name="job_title" value="<?php echo esc_attr($job_title); ?>" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-red focus:border-brand-red">
                            </div>
                            
                            <div>
                                <label for="job_location" class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                                <select id="job_location" name="job_location" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-red focus:border-brand-red">
                                    <option value="">Select Location</option>
                                    <optgroup label="Arizona">
                                        <option value="Phoenix" <?php selected($location, 'Phoenix'); ?>>Phoenix</option>
                                        <option value="Tucson" <?php selected($location, 'Tucson'); ?>>Tucson</option>
                                        <option value="Mesa" <?php selected($location, 'Mesa'); ?>>Mesa</option>
                                        <option value="Scottsdale" <?php selected($location, 'Scottsdale'); ?>>Scottsdale</option>
                                    </optgroup>
                                    <optgroup label="North Carolina">
                                        <option value="Raleigh" <?php selected($location, 'Raleigh'); ?>>Raleigh</option>
                                        <option value="Fayetteville" <?php selected($location, 'Fayetteville'); ?>>Fayetteville</option>
                                        <option value="Charlotte" <?php selected($location, 'Charlotte'); ?>>Charlotte</option>
                                        <option value="Asheville" <?php selected($location, 'Asheville'); ?>>Asheville</option>
                                        <option value="Hickory" <?php selected($location, 'Hickory'); ?>>Hickory</option>
                                        <option value="Winston Salem" <?php selected($location, 'Winston Salem'); ?>>Winston Salem</option>
                                        <option value="Greensboro" <?php selected($location, 'Greensboro'); ?>>Greensboro</option>
                                        <option value="Jacksonville" <?php selected($location, 'Jacksonville'); ?>>Jacksonville</option>
                                        <option value="New Bern" <?php selected($location, 'New Bern'); ?>>New Bern</option>
                                    </optgroup>
                                    <optgroup label="Texas">
                                        <option value="Dallas" <?php selected($location, 'Dallas'); ?>>Dallas</option>
                                        <option value="Houston" <?php selected($location, 'Houston'); ?>>Houston</option>
                                        <option value="Austin" <?php selected($location, 'Austin'); ?>>Austin</option>
                                        <option value="San Antonio" <?php selected($location, 'San Antonio'); ?>>San Antonio</option>
                                        <option value="Corpus Christi" <?php selected($location, 'Corpus Christi'); ?>>Corpus Christi</option>
                                        <option value="McAllen" <?php selected($location, 'McAllen'); ?>>McAllen</option>
                                        <option value="Lufkin" <?php selected($location, 'Lufkin'); ?>>Lufkin</option>
                                        <option value="Nacogdoches" <?php selected($location, 'Nacogdoches'); ?>>Nacogdoches</option>
                                    </optgroup>
                                    <optgroup label="Virginia">
                                        <option value="Roanoke" <?php selected($location, 'Roanoke'); ?>>Roanoke</option>
                                    </optgroup>
                                    <optgroup label="Kentucky">
                                        <option value="Louisville" <?php selected($location, 'Louisville'); ?>>Louisville</option>
                                        <option value="Lexington" <?php selected($location, 'Lexington'); ?>>Lexington</option>
                                        <option value="Bowling Green" <?php selected($location, 'Bowling Green'); ?>>Bowling Green</option>
                                    </optgroup>
                                    <optgroup label="Georgia">
                                        <option value="Atlanta" <?php selected($location, 'Atlanta'); ?>>Atlanta</option>
                                        <option value="Augusta" <?php selected($location, 'Augusta'); ?>>Augusta</option>
                                        <option value="Columbus" <?php selected($location, 'Columbus'); ?>>Columbus</option>
                                        <option value="Savannah" <?php selected($location, 'Savannah'); ?>>Savannah</option>
                                    </optgroup>
                                </select>
                            </div>
                            
                            <div>
                                <label for="job_type" class="block text-sm font-medium text-gray-700 mb-1">Job Type</label>
                                <select id="job_type" name="job_type" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-red focus:border-brand-red">
                                    <option value="">Select Job Type</option>
                                    <option value="Full-Time" <?php selected($job_type, 'Full-Time'); ?>>Full-Time</option>
                                    <option value="Part-Time" <?php selected($job_type, 'Part-Time'); ?>>Part-Time</option>
                                    <option value="Contract" <?php selected($job_type, 'Contract'); ?>>Contract</option>
                                    <option value="Per Diem" <?php selected($job_type, 'Per Diem'); ?>>Per Diem</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="job_modality" class="block text-sm font-medium text-gray-700 mb-1">Modality</label>
                                <select id="job_modality" name="job_modality" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-red focus:border-brand-red">
                                    <option value="">Select Modality</option>
                                    <option value="X-Ray" <?php selected($job_modality, 'X-Ray'); ?>>X-Ray</option>
                                    <option value="Ultrasound" <?php selected($job_modality, 'Ultrasound'); ?>>Ultrasound</option>
                                    <option value="General" <?php selected($job_modality, 'General'); ?>>General</option>
                                </select>
                            </div>
                            
                            <div class="lg:col-span-2">
                                <label for="job_summary" class="block text-sm font-medium text-gray-700 mb-1">Job Summary</label>
                                <textarea id="job_summary" name="job_summary" rows="3" required
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-red focus:border-brand-red"
                                          placeholder="Brief summary of the position..."><?php echo esc_textarea($job_summary); ?></textarea>
                            </div>
                            
                            <div class="lg:col-span-2">
                                <label for="job_description" class="block text-sm font-medium text-gray-700 mb-1">Job Description</label>
                                <textarea id="job_description" name="job_description" rows="6" required
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-red focus:border-brand-red"
                                          placeholder="Detailed description of the position, duties, and work environment..."><?php echo esc_textarea($job_description); ?></textarea>
                            </div>
                            
                            <!-- Requirements -->
                            <div class="lg:col-span-2 mt-6">
                                <h2 class="text-xl font-semibold text-gray-900 mb-4">Requirements & Qualifications</h2>
                            </div>
                            
                            <div class="lg:col-span-2">
                                <label for="job_requirements" class="block text-sm font-medium text-gray-700 mb-1">Requirements</label>
                                <textarea id="job_requirements" name="job_requirements" rows="4" required
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-red focus:border-brand-red"
                                          placeholder="List job requirements (one per line)"><?php echo esc_textarea($job_requirements); ?></textarea>
                                <p class="mt-1 text-sm text-gray-500">Enter each requirement on a new line</p>
                            </div>
                            
                            <div class="lg:col-span-2">
                                <label for="job_responsibilities" class="block text-sm font-medium text-gray-700 mb-1">Responsibilities</label>
                                <textarea id="job_responsibilities" name="job_responsibilities" rows="4" required
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-red focus:border-brand-red"
                                          placeholder="List job responsibilities (one per line)"><?php echo esc_textarea($job_responsibilities); ?></textarea>
                                <p class="mt-1 text-sm text-gray-500">Enter each responsibility on a new line</p>
                            </div>
                            
                            <div class="lg:col-span-2">
                                <label for="job_equipment" class="block text-sm font-medium text-gray-700 mb-1">Equipment Used</label>
                                <textarea id="job_equipment" name="job_equipment" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-red focus:border-brand-red"
                                          placeholder="List equipment used (one per line)"><?php echo esc_textarea($job_equipment); ?></textarea>
                                <p class="mt-1 text-sm text-gray-500">Enter each piece of equipment on a new line</p>
                            </div>
                            
                            <!-- Additional Information -->
                            <div class="lg:col-span-2 mt-6">
                                <h2 class="text-xl font-semibold text-gray-900 mb-4">Additional Information</h2>
                            </div>
                            
                            <div class="lg:col-span-2">
                                <label for="job_benefits" class="block text-sm font-medium text-gray-700 mb-1">Benefits</label>
                                <textarea id="job_benefits" name="job_benefits" rows="4"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-red focus:border-brand-red"
                                          placeholder="List job benefits (one per line)"><?php echo esc_textarea($job_benefits); ?></textarea>
                                <p class="mt-1 text-sm text-gray-500">Enter each benefit on a new line</p>
                            </div>
                            
                            <div class="lg:col-span-2">
                                <label for="state_licensing" class="block text-sm font-medium text-gray-700 mb-1">State Licensing Information</label>
                                <textarea id="state_licensing" name="state_licensing" rows="2"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-red focus:border-brand-red"
                                          placeholder="State-specific licensing requirements..."><?php echo esc_textarea($state_licensing); ?></textarea>
                            </div>
                            
                            <div>
                                <label for="job_status" class="block text-sm font-medium text-gray-700 mb-1">Job Status</label>
                                <select id="job_status" name="job_status" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-brand-red focus:border-brand-red">
                                    <option value="publish" <?php selected($job->post_status, 'publish'); ?>>Active (Published)</option>
                                    <option value="draft" <?php selected($job->post_status, 'draft'); ?>>Draft</option>
                                </select>
                        </div>
                        
                        <!-- Form Actions -->
                        <div class="flex flex-col sm:flex-row gap-4 pt-6 mt-6 border-t border-gray-200">
                            <button type="submit" id="update-job-btn"
                                    class="inline-flex items-center justify-center px-6 py-3 bg-brand-red text-white font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-red transition">
                                <span class="btn-text">Update Job</span>
                                <span class="btn-loading hidden">Updating...</span>
                            </button>
                            <a href="<?php echo home_url('/dashboard/jobs'); ?>" 
                               class="inline-flex items-center justify-center px-6 py-3 bg-gray-500 text-white font-medium rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition">
                                Cancel
                            </a>
                        </div>
                                
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                                    <div>
                                        <label for="location" style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Location</label>
                                        <input type="text" id="location" name="location" value="<?php echo esc_attr($location); ?>" 
                                               style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;" required>
                                    </div>
                                    
                                    <div>
                                        <label for="job_type" style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Employment Type</label>
                                        <select id="job_type" name="job_type" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;" required>
                                            <option value="">Select Type</option>
                                            <option value="full-time" <?php selected($job_type, 'full-time'); ?>>Full-time</option>
                                            <option value="part-time" <?php selected($job_type, 'part-time'); ?>>Part-time</option>
                                            <option value="contract" <?php selected($job_type, 'contract'); ?>>Contract</option>
                                            <option value="temporary" <?php selected($job_type, 'temporary'); ?>>Temporary</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label for="experience_level" style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Experience Level</label>
                                        <select id="experience_level" name="experience_level" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                                            <option value="">Select Level</option>
                                            <option value="entry" <?php selected($experience_level, 'entry'); ?>>Entry Level</option>
                                            <option value="mid" <?php selected($experience_level, 'mid'); ?>>Mid Level</option>
                                            <option value="senior" <?php selected($experience_level, 'senior'); ?>>Senior Level</option>
                                        </select>
                                    </div>
    }
    
    /**
     * Render job management interface
     */
    private function render_job_management() {
                                    
                                    <div>
                                        <label for="application_deadline" style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Application Deadline</label>
                                        <input type="date" id="application_deadline" name="application_deadline" value="<?php echo esc_attr($application_deadline); ?>" 
                                               style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
                                    </div>
                                </div>
                                
                                <div style="display: flex; gap: 1rem; justify-content: flex-end; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                                    <a href="<?php echo home_url('/dashboard/jobs'); ?>" 
                                       style="padding: 0.75rem 1.5rem; border: 1px solid #d1d5db; border-radius: 0.375rem; text-decoration: none; color: #374151; background: white;">
                                        Cancel
                                    </a>
                                    <button type="submit" 
                                            style="padding: 0.75rem 1.5rem; background: #BF1E2D; color: white; border: none; border-radius: 0.375rem; cursor: pointer;">
                                        Update Job
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#edit-job-form').on('submit', function(e) {
                e.preventDefault();
                
                var formData = new FormData(this);
                formData.append('action', 'update_career_job');
                
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            alert('Job updated successfully!');
                            window.location.href = '<?php echo home_url('/dashboard/jobs'); ?>';
                        } else {
                            alert('Error: ' + (response.data || 'Failed to update job'));
                        }
                    },
                    error: function() {
                        alert('An error occurred. Please try again.');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Render job management interface
     */
    private function render_job_management() {
        $jobs = get_posts([
            'post_type' => 'career_job',
            'post_status' => ['publish', 'draft'],
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        
        ?>
        <div class="bg-gray-50 min-h-screen">
            <div class="container mx-auto px-4 py-12 sm:px-6 lg:px-8">
                <!-- Page Header -->
                <div class="mb-8">
                    <nav class="mb-4">
                        <ol class="flex space-x-2 text-sm text-gray-600">
                            <li>
                                <a href="<?php echo home_url('/dashboard'); ?>" class="hover:text-brand-red transition">Dashboard</a>
                            </li>
                            <li>/</li>
                            <li class="text-gray-900 font-medium">Manage Jobs</li>
                        </ol>
                    </nav>
                    <div class="flex justify-between items-center">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 mb-4">Manage Jobs</h1>
                            <p class="text-lg text-gray-600">
                                Create, edit, and manage your job postings.
                            </p>
                        </div>
                        <a href="<?php echo home_url('/dashboard/jobs/create'); ?>" 
                           class="inline-flex items-center rounded-md bg-brand-red px-6 py-3 text-base font-medium text-white shadow-sm hover:bg-red-700 transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Create New Job
                        </a>
                    </div>
                </div>

                <!-- Jobs Grid -->
                <?php if (empty($jobs)): ?>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
                        <div class="w-12 h-12 mx-auto mb-4 bg-gray-50 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No jobs yet</h3>
                        <p class="text-gray-600 mb-4">Get started by creating your first job posting.</p>
                        <a href="<?php echo home_url('/dashboard/jobs/create'); ?>" 
                           class="inline-flex items-center rounded-md bg-brand-red px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700 transition">
                            Create Your First Job
                        </a>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                        <?php foreach ($jobs as $job): ?>
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                                <div class="p-6">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                                <?php echo esc_html($job->post_title); ?>
                                            </h3>
                                            <div class="flex items-center text-gray-600 text-sm">
                                                <svg class="h-4 w-4 text-brand-red mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                <span><?php echo esc_html(get_post_meta($job->ID, '_job_location', true)); ?></span>
                                            </div>
                                        </div>
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium <?php echo $job->post_status === 'publish' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                            <?php echo $job->post_status === 'publish' ? 'Published' : 'Draft'; ?>
                                        </span>
                                    </div>
                                    
                                    <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                                        <?php echo esc_html(wp_trim_words(get_post_meta($job->ID, '_job_summary', true), 15, '...')); ?>
                                    </p>
                                    
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-500">
                                            Created: <?php echo esc_html(date_i18n('M j, Y', strtotime($job->post_date))); ?>
                                        </span>
                                        <div class="flex space-x-2">
                                            <a href="<?php echo home_url('/dashboard/jobs/' . $job->ID . '/edit'); ?>" 
                                               class="text-brand-red hover:text-red-700 font-medium transition">
                                                Edit
                                            </a>
                                            <a href="<?php echo get_permalink($job->ID); ?>" 
                                               class="text-gray-600 hover:text-gray-800 font-medium transition" 
                                               target="_blank">
                                                View
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render application management interface
     */
    private function render_application_management() {
        ?>
        <div class="bg-gray-50 min-h-screen">
            <div class="container mx-auto px-4 py-12 sm:px-6 lg:px-8">
                <div class="mb-8">
                    <nav class="mb-4">
                        <ol class="flex space-x-2 text-sm text-gray-600">
                            <li>
                                <a href="<?php echo home_url('/dashboard'); ?>" class="hover:text-brand-red transition">Dashboard</a>
                            </li>
                            <li>/</li>
                            <li class="text-gray-900 font-medium">Applications</li>
                        </ol>
                    </nav>
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">Application Management</h1>
                    <p class="text-lg text-gray-600">
                        Review and manage job applications.
                    </p>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <p class="text-gray-600">Application management interface will be implemented here.</p>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render job-specific applications
     */
    private function render_job_applications($job_id) {
        // Get the job
        $job = get_post($job_id);
        
        if (!$job || $job->post_type !== 'career_job') {
            echo '<div class="dashboard-container">
                    <div class="dashboard-inner">
                        <div class="dashboard-card">
                            <div class="dashboard-card-content with-padding">
                                <p style="color: #dc2626;">Job not found.</p>
                                <a href="' . home_url('/dashboard/jobs') . '" class="btn btn-primary">Back to Jobs</a>
                            </div>
                        </div>
                    </div>
                  </div>';
            return;
        }
        
        // Get applications for this job
        global $wpdb;
        $applications = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}careers_applications WHERE job_id = %d ORDER BY created_at DESC",
            $job_id
        ));
        ?>
        
        <div class="dashboard-container">
            <div class="dashboard-inner">
                <div class="dashboard-header">
                    <h1 class="dashboard-title">Applications for: <?php echo esc_html($job->post_title); ?></h1>
                    <p class="dashboard-subtitle">Review and manage applications for this position</p>
                </div>
                
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <h2 class="dashboard-card-title">Applications (<?php echo count($applications); ?>)</h2>
                        <p class="dashboard-card-description">
                            <a href="<?php echo home_url('/dashboard/jobs'); ?>" style="color: #BF1E2D; text-decoration: none;">← Back to Jobs</a>
                        </p>
                    </div>
                    <div class="dashboard-card-content">
                        <?php if (!empty($applications)): ?>
                        <div class="table-wrapper">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Applicant</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Applied</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($applications as $application): ?>
                                        <?php
                                        $applied_date = date('M j, Y', strtotime($application->created_at));
                                        $status_color = '';
                                        switch($application->status) {
                                            case 'pending':
                                                $status_color = 'table-status-draft';
                                                break;
                                            case 'reviewed':
                                                $status_color = 'table-status-active';
                                                break;
                                            case 'rejected':
                                                $status_color = 'background-color: #fef2f2; color: #dc2626;';
                                                break;
                                        }
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="table-job-title"><?php echo esc_html($application->first_name . ' ' . $application->last_name); ?></div>
                                            </td>
                                            <td>
                                                <div class="table-location"><?php echo esc_html($application->email); ?></div>
                                            </td>
                                            <td>
                                                <div class="table-location"><?php echo esc_html($application->phone ?: 'N/A'); ?></div>
                                            </td>
                                            <td>
                                                <div class="table-date"><?php echo esc_html($applied_date); ?></div>
                                            </td>
                                            <td>
                                                <span class="table-status-badge <?php echo esc_attr($status_color); ?>" style="<?php if($application->status === 'rejected') echo $status_color; ?>">
                                                    <?php echo esc_html(ucfirst($application->status)); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="table-actions">
                                                    <button class="table-action-btn" 
                                                            onclick="viewApplication(<?php echo $application->id; ?>)"
                                                            title="View Application">
                                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"></path>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path>
                                                        </svg>
                                                    </button>
                                                    <?php if ($application->status === 'pending'): ?>
                                                    <button class="table-action-btn" 
                                                            onclick="updateApplicationStatus(<?php echo $application->id; ?>, 'reviewed')"
                                                            title="Mark as Reviewed">
                                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path>
                                                        </svg>
                                                    </button>
                                                    <?php endif; ?>
                                                    <button class="table-action-btn delete" 
                                                            onclick="updateApplicationStatus(<?php echo $application->id; ?>, 'rejected')"
                                                            title="Reject Application">
                                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18 18 6M6 6l12 12"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="table-empty-state">
                            <svg class="table-empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 715.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <h3 style="font-size: 1.25rem; font-weight: 600; color: #111827; margin: 0 0 0.5rem 0;">No Applications Yet</h3>
                            <p style="color: #6b7280; margin: 0;">This job hasn't received any applications yet.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <script type="text/javascript">
        function viewApplication(applicationId) {
            // Implementation for viewing application details
            alert('View application functionality coming soon!');
        }
        
        function updateApplicationStatus(applicationId, status) {
            if (!confirm('Are you sure you want to update this application status?')) {
                return;
            }
            
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'careers_admin_action',
                    admin_action: 'update_status',
                    application_id: applicationId,
                    status: status,
                    nonce: '<?php echo wp_create_nonce('careers_admin_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + (response.data || 'Failed to update status'));
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        }
        </script>
        <?php
    }
    
    /**
     * Render analytics dashboard
     */
    private function render_analytics_dashboard() {
        ?>
        <div class="bg-gray-50 min-h-screen">
            <div class="container mx-auto px-4 py-12 sm:px-6 lg:px-8">
                <div class="mb-8">
                    <nav class="mb-4">
                        <ol class="flex space-x-2 text-sm text-gray-600">
                            <li>
                                <a href="<?php echo home_url('/dashboard'); ?>" class="hover:text-brand-red transition">Dashboard</a>
                            </li>
                            <li>/</li>
                            <li class="text-gray-900 font-medium">Analytics</li>
                        </ol>
                    </nav>
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">Analytics</h1>
                    <p class="text-lg text-gray-600">
                        Track performance and insights.
                    </p>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <p class="text-gray-600">Analytics dashboard will be implemented here.</p>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render profile management for applicants
     */
    private function render_profile_management() {
        ?>
        <div class="bg-gray-50 min-h-screen">
            <div class="container mx-auto px-4 py-12 sm:px-6 lg:px-8">
                <div class="mb-8">
                    <nav class="mb-4">
                        <ol class="flex space-x-2 text-sm text-gray-600">
                            <li>
                                <a href="<?php echo home_url('/dashboard'); ?>" class="hover:text-brand-red transition">Dashboard</a>
                            </li>
                            <li>/</li>
                            <li class="text-gray-900 font-medium">Profile</li>
                        </ol>
                    </nav>
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">My Profile</h1>
                    <p class="text-lg text-gray-600">
                        Update your profile information.
                    </p>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <p class="text-gray-600">Profile management will be implemented here.</p>
                </div>
            </div>
        </div>
        <?php
    }
} 