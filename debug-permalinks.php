<?php
/**
 * Debug permalinks for Careers Manager
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('You must be an administrator to run this script.');
}

echo "<h2>Careers Manager Permalink Debug</h2>";

// Get permalink structure
$permalink_structure = get_option('permalink_structure');
echo "<p><strong>Permalink Structure:</strong> " . ($permalink_structure ?: 'Default (Plain)') . "</p>";

// Check if post type is registered
$post_type = get_post_type_object('career_job');
if ($post_type) {
    echo "<h3>Post Type Settings:</h3>";
    echo "<ul>";
    echo "<li><strong>Name:</strong> " . $post_type->name . "</li>";
    echo "<li><strong>Public:</strong> " . ($post_type->public ? 'Yes' : 'No') . "</li>";
    echo "<li><strong>Rewrite Slug:</strong> " . (isset($post_type->rewrite['slug']) ? $post_type->rewrite['slug'] : 'None') . "</li>";
    echo "<li><strong>Has Archive:</strong> " . ($post_type->has_archive ? $post_type->has_archive : 'No') . "</li>";
    echo "</ul>";
} else {
    echo "<p style='color: red;'>❌ career_job post type not found!</p>";
}

// Get sample jobs
$jobs = get_posts(array(
    'post_type' => 'career_job',
    'posts_per_page' => 5,
    'post_status' => 'publish'
));

if (!empty($jobs)) {
    echo "<h3>Current Job URLs:</h3>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th style='padding: 5px;'>Job Title</th><th style='padding: 5px;'>Current URL</th><th style='padding: 5px;'>Expected URL</th></tr>";
    
    foreach ($jobs as $job) {
        $current_url = get_permalink($job->ID);
        $expected_url = home_url('/job/' . $job->post_name . '/');
        
        echo "<tr>";
        echo "<td style='padding: 5px;'>" . esc_html($job->post_title) . "</td>";
        echo "<td style='padding: 5px;'><a href='" . esc_url($current_url) . "' target='_blank'>" . esc_url($current_url) . "</a></td>";
        echo "<td style='padding: 5px;'>" . esc_url($expected_url) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No published jobs found.</p>";
}

// Check rewrite rules
global $wp_rewrite;
echo "<h3>Rewrite Rules for career_job:</h3>";
echo "<pre style='background: #f0f0f0; padding: 10px; overflow: auto;'>";
$rules = $wp_rewrite->wp_rewrite_rules();
$career_rules = array_filter($rules, function($rule) {
    return strpos($rule, 'career_job') !== false;
});
print_r($career_rules);
echo "</pre>";

echo "<h3>Actions to Fix:</h3>";
echo "<ol>";
echo "<li><strong>Flush Permalinks:</strong> Go to Settings → Permalinks and click 'Save Changes'</li>";
echo "<li><strong>Or Deactivate/Reactivate Plugin:</strong> Go to Plugins → Deactivate and Activate 'Careers Manager'</li>";
echo "<li><strong>Or Run Flush Script:</strong> <a href='flush-permalinks.php'>Click here to flush permalinks</a></li>";
echo "</ol>";

// Check for page conflicts
$page = get_page_by_path('open-positions');
if ($page) {
    echo "<h3>Page Conflict Check:</h3>";
    echo "<p>✅ Found page '/open-positions/' (ID: " . $page->ID . ") - This is good, your shortcode page exists.</p>";
    echo "<p>The post type now uses '/job/' to avoid conflicts.</p>";
}