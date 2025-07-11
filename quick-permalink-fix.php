<?php
/**
 * Quick Permalink Fix
 * This sets pretty permalinks if they're not already set
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('You must be an administrator to run this script.');
}

echo "<h2>Quick Permalink Fix for Careers Manager</h2>";

// Check current permalink structure
$current_structure = get_option('permalink_structure');
echo "<p><strong>Current permalink structure:</strong> " . ($current_structure ?: 'Plain (?p=123)') . "</p>";

// If using plain permalinks, update to post name
if (empty($current_structure)) {
    echo "<p>🔧 Updating to pretty permalinks...</p>";
    
    // Set permalink structure to /%postname%/
    update_option('permalink_structure', '/%postname%/');
    
    // Flush rewrite rules
    flush_rewrite_rules(true);
    
    echo "<p>✅ <strong>Permalinks updated to: /%postname%/</strong></p>";
    echo "<p>Job posts will now use URLs like: /job/job-title/</p>";
} else {
    echo "<p>✅ Pretty permalinks are already enabled!</p>";
    
    // Still flush rules to ensure job post type is registered
    flush_rewrite_rules(true);
    echo "<p>✅ Rewrite rules refreshed</p>";
}

// Test the result
$jobs = get_posts(array(
    'post_type' => 'career_job',
    'posts_per_page' => 3,
    'post_status' => 'publish'
));

if (!empty($jobs)) {
    echo "<h3>Job URLs after fix:</h3>";
    echo "<ul>";
    foreach ($jobs as $job) {
        $url = get_permalink($job->ID);
        echo "<li><a href='" . esc_url($url) . "' target='_blank'>" . esc_html($job->post_title) . "</a><br>";
        echo "<code>" . esc_html($url) . "</code></li>";
    }
    echo "</ul>";
}

echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 20px 0;'>";
echo "<h4>✅ Permalinks Fixed!</h4>";
echo "<p>Your job listings should now work correctly. The 'View Details' button will link to URLs like <code>/job/job-title/</code></p>";
echo "<p><a href='" . home_url('/open-positions/') . "' style='display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 3px;'>Test Job Listings Page</a></p>";
echo "</div>";

echo "<p><a href='" . admin_url() . "'>← Back to WordPress Admin</a></p>";