<?php
/**
 * Flush permalinks for Careers Manager
 * Run this file to update permalink structure
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('You must be an administrator to run this script.');
}

echo "<h2>Flushing Permalinks for Careers Manager</h2>";

// Register the post type with new rewrite rules
if (class_exists('CareerJobCPT')) {
    $cpt = new CareerJobCPT();
    $cpt->register_post_type();
    $cpt->register_taxonomies();
    echo "<p>✅ Re-registered career_job post type with new rewrite rules</p>";
} else {
    echo "<p>❌ CareerJobCPT class not found</p>";
}

// Add dashboard rewrite rules before flushing
if (class_exists('CareersManager')) {
    $manager = CareersManager::get_instance();
    $manager->add_rewrite_rules();
    echo "<p>✅ Added dashboard rewrite rules</p>";
}

// Flush rewrite rules
flush_rewrite_rules();
echo "<p>✅ Permalinks flushed successfully!</p>";

// Show current permalink structure
echo "<h3>Current Settings:</h3>";
echo "<ul>";
echo "<li>Job Listings Page: /open-positions/ (using shortcode)</li>";
echo "<li>Single Job URL: /job/[job-name]/</li>";
echo "</ul>";

// Test URLs
$jobs = get_posts(array(
    'post_type' => 'career_job',
    'posts_per_page' => 5,
    'post_status' => 'publish'
));

if (!empty($jobs)) {
    echo "<h3>Sample Job URLs:</h3>";
    echo "<ul>";
    foreach ($jobs as $job) {
        $url = get_permalink($job->ID);
        echo "<li><a href='" . esc_url($url) . "' target='_blank'>" . esc_html($job->post_title) . "</a> - " . esc_url($url) . "</li>";
    }
    echo "</ul>";
}

echo "<p><strong>Note:</strong> You can also flush permalinks by going to Settings → Permalinks in WordPress admin and clicking 'Save Changes'.</p>";
echo "<p><a href='" . admin_url() . "'>Return to WordPress Admin</a></p>";