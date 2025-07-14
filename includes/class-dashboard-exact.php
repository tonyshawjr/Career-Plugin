<?php
/**
 * Exact Dashboard Match
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function render_exact_admin_dashboard() {
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
    ?>
    <div style="background: #f8f9fa; min-height: 100vh; padding: 3rem 0;">
        <div class="container">
            <!-- Header -->
            <div style="margin-bottom: 2rem;">
                <h1 style="font-size: 2.25rem; font-weight: 700; color: #111827; margin: 0 0 0.5rem 0;">Admin Dashboard</h1>
                <p style="font-size: 1rem; color: #6b7280; margin: 0;">
                    Manage jobs, applications, and system operations
                </p>
            </div>

            <!-- Tab Navigation -->
            <div style="margin-bottom: 2rem;">
                <div style="display: flex; gap: 0.5rem;">
                    <button class="dashboard-tab active" id="job-management-tab" style="background: #BF1E2D; color: white; padding: 0.75rem 1.5rem; border-radius: 6px; border: none; font-weight: 500; cursor: pointer;">
                        Job Management
                    </button>
                    <button class="dashboard-tab" id="applications-tab" style="background: #f8f9fa; color: #6b7280; padding: 0.75rem 1.5rem; border-radius: 6px; border: 1px solid #e5e7eb; font-weight: 500; cursor: pointer;">
                        Applications
                    </button>
                </div>
            </div>

            <!-- Job Management Content -->
            <div id="job-management-content">
                <!-- Section Header -->
                <div style="margin-bottom: 2rem;">
                    <h2 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin: 0 0 0.25rem 0;">Job Management</h2>
                    <p style="font-size: 0.875rem; color: #6b7280; margin: 0;">Create, edit, and manage job postings</p>
                </div>

                <!-- Active Jobs Section -->
                <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 2rem;">
                    <!-- Section Header -->
                    <div style="padding: 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h3 style="font-size: 1.125rem; font-weight: 600; color: #111827; margin: 0 0 0.25rem 0;">Active Jobs</h3>
                            <p style="font-size: 0.875rem; color: #6b7280; margin: 0;">Manage your job postings and track applications</p>
                        </div>
                        <button onclick="window.location.href='<?php echo home_url('/dashboard/jobs/create'); ?>'" 
                                style="background: #BF1E2D; color: white; padding: 0.5rem 1rem; border-radius: 6px; border: none; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                            + Add New Job
                        </button>
                    </div>

                    <!-- Stats Cards -->
                    <div style="padding: 1.5rem; border-bottom: 1px solid #e5e7eb;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1.5rem;">
                            <!-- Total Jobs -->
                            <div style="text-align: center;">
                                <div style="width: 60px; height: 60px; background: #dbeafe; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.75rem;">
                                    <svg style="width: 30px; height: 30px; color: #3b82f6;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div style="font-size: 2rem; font-weight: 700; color: #111827; line-height: 1; margin-bottom: 0.25rem;"><?php echo count($jobs); ?></div>
                                <div style="font-size: 0.875rem; color: #6b7280;">Total Jobs</div>
                            </div>

                            <!-- Total Applications -->
                            <div style="text-align: center;">
                                <div style="width: 60px; height: 60px; background: #d1fae5; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.75rem;">
                                    <svg style="width: 30px; height: 30px; color: #10b981;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div style="font-size: 2rem; font-weight: 700; color: #111827; line-height: 1; margin-bottom: 0.25rem;"><?php echo $total_applications; ?></div>
                                <div style="font-size: 0.875rem; color: #6b7280;">Total Applications</div>
                            </div>

                            <!-- App Applications/Job -->
                            <div style="text-align: center;">
                                <div style="width: 60px; height: 60px; background: #fef3c7; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.75rem;">
                                    <svg style="width: 30px; height: 30px; color: #f59e0b;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 515.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 919.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <div style="font-size: 2rem; font-weight: 700; color: #111827; line-height: 1; margin-bottom: 0.25rem;"><?php echo $avg_applications_per_job; ?></div>
                                <div style="font-size: 0.875rem; color: #6b7280;">App Applications/Job</div>
                            </div>
                        </div>
                    </div>

                    <!-- Jobs Table -->
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 1px solid #e5e7eb;">
                                    <th style="text-align: left; padding: 0.75rem 1.5rem; font-size: 0.75rem; font-weight: 500; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Job Title</th>
                                    <th style="text-align: left; padding: 0.75rem 1.5rem; font-size: 0.75rem; font-weight: 500; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Location</th>
                                    <th style="text-align: left; padding: 0.75rem 1.5rem; font-size: 0.75rem; font-weight: 500; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Applications</th>
                                    <th style="text-align: left; padding: 0.75rem 1.5rem; font-size: 0.75rem; font-weight: 500; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Employment Type</th>
                                    <th style="text-align: left; padding: 0.75rem 1.5rem; font-size: 0.75rem; font-weight: 500; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Posted Date</th>
                                    <th style="text-align: left; padding: 0.75rem 1.5rem; font-size: 0.75rem; font-weight: 500; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($jobs)): ?>
                                    <?php foreach ($jobs as $job): ?>
                                        <?php
                                        $location = get_post_meta($job->ID, '_job_location', true);
                                        $job_type = get_post_meta($job->ID, '_job_type', true);
                                        $application_count = $job_application_counts[$job->ID] ?? 0;
                                        $post_date = date('n/j/Y', strtotime($job->post_date));
                                        ?>
                                        <tr style="border-bottom: 1px solid #f1f5f9;" onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='white'">
                                            <td style="padding: 1rem 1.5rem;">
                                                <div style="font-size: 0.875rem; font-weight: 500; color: #111827;"><?php echo esc_html($job->post_title); ?></div>
                                            </td>
                                            <td style="padding: 1rem 1.5rem;">
                                                <div style="font-size: 0.875rem; color: #6b7280;"><?php echo esc_html($location); ?></div>
                                            </td>
                                            <td style="padding: 1rem 1.5rem;">
                                                <div style="font-size: 0.875rem; color: #BF1E2D; font-weight: 500;">
                                                    <?php echo $application_count; ?> applicant<?php echo $application_count !== 1 ? 's' : ''; ?>
                                                </div>
                                            </td>
                                            <td style="padding: 1rem 1.5rem;">
                                                <div style="font-size: 0.875rem; color: #6b7280;"><?php echo esc_html($job_type); ?></div>
                                            </td>
                                            <td style="padding: 1rem 1.5rem;">
                                                <div style="font-size: 0.875rem; color: #6b7280;"><?php echo esc_html($post_date); ?></div>
                                            </td>
                                            <td style="padding: 1rem 1.5rem;">
                                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                    <a href="<?php echo home_url('/dashboard/jobs/' . $job->ID . '/edit'); ?>" 
                                                       style="color: #6b7280; padding: 0.25rem; text-decoration: none; transition: color 0.15s;"
                                                       onmouseover="this.style.color='#BF1E2D'"
                                                       onmouseout="this.style.color='#6b7280'"
                                                       title="Edit">
                                                        <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                    </a>
                                                    <a href="<?php echo get_permalink($job->ID); ?>" 
                                                       target="_blank"
                                                       style="color: #6b7280; padding: 0.25rem; text-decoration: none; transition: color 0.15s;"
                                                       onmouseover="this.style.color='#BF1E2D'"
                                                       onmouseout="this.style.color='#6b7280'"
                                                       title="View">
                                                        <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                        </svg>
                                                    </a>
                                                    <a href="<?php echo home_url('/dashboard/jobs/' . $job->ID . '/applications'); ?>" 
                                                       style="color: #6b7280; padding: 0.25rem; text-decoration: none; transition: color 0.15s;"
                                                       onmouseover="this.style.color='#BF1E2D'"
                                                       onmouseout="this.style.color='#6b7280'"
                                                       title="Applications">
                                                        <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 515.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 919.288 0M15 7a3 3 0 11-6 0 3 3 0 616 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                        </svg>
                                                    </a>
                                                    <button onclick="deleteJob(<?php echo $job->ID; ?>)" 
                                                            style="color: #6b7280; border: none; background: none; cursor: pointer; padding: 0.25rem; transition: color 0.15s;"
                                                            onmouseover="this.style.color='#ef4444'"
                                                            onmouseout="this.style.color='#6b7280'"
                                                            title="Delete">
                                                        <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1-1H8a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" style="padding: 3rem; text-align: center;">
                                            <div style="color: #6b7280;">
                                                <svg style="width: 4rem; height: 4rem; margin: 0 auto 1rem; color: #d1d5db;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                <h3 style="font-size: 1.25rem; font-weight: 600; color: #111827; margin: 0 0 0.5rem 0;">No jobs yet</h3>
                                                <p style="color: #6b7280; margin: 0 0 1.5rem 0;">Create your first job posting to get started.</p>
                                                <button onclick="window.location.href='<?php echo home_url('/dashboard/jobs/create'); ?>'" 
                                                        style="background: #BF1E2D; color: white; padding: 0.75rem 1.5rem; border-radius: 6px; border: none; font-weight: 500; cursor: pointer;">
                                                    Create Your First Job
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Applications Content -->
            <div id="applications-content" style="display: none;">
                <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 3rem; text-align: center;">
                    <svg style="width: 4rem; height: 4rem; margin: 0 auto 1.5rem; color: #d1d5db;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 712-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 style="font-size: 1.25rem; font-weight: 600; color: #111827; margin: 0 0 0.5rem 0;">Applications Coming Soon</h3>
                    <p style="color: #6b7280; margin: 0;">Application management features will be available here.</p>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Tab switching functionality
        $('#job-management-tab').on('click', function() {
            // Reset all tabs
            $('.dashboard-tab').css({
                'background': '#f8f9fa',
                'color': '#6b7280',
                'border': '1px solid #e5e7eb'
            });
            
            // Activate clicked tab
            $(this).css({
                'background': '#BF1E2D',
                'color': 'white',
                'border': 'none'
            });
            
            $('#job-management-content').show();
            $('#applications-content').hide();
        });

        $('#applications-tab').on('click', function() {
            // Reset all tabs
            $('.dashboard-tab').css({
                'background': '#f8f9fa',
                'color': '#6b7280',
                'border': '1px solid #e5e7eb'
            });
            
            // Activate clicked tab
            $(this).css({
                'background': '#BF1E2D',
                'color': 'white',
                'border': 'none'
            });
            
            $('#applications-content').show();
            $('#job-management-content').hide();
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