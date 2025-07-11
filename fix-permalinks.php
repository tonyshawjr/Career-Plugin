<?php
/**
 * Fix permalinks for Careers Manager
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('You must be an administrator to run this script.');
}

echo "<h2>Fixing Careers Manager Permalinks</h2>";

// Check current permalink structure
$permalink_structure = get_option('permalink_structure');
echo "<h3>Current Status:</h3>";
echo "<p><strong>Permalink Structure:</strong> " . ($permalink_structure ?: 'Default (Plain)') . "</p>";

if (empty($permalink_structure)) {
    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0;'>";
    echo "<h4>⚠️ Plain Permalinks Detected!</h4>";
    echo "<p>Your site is using plain permalinks (?p=123). Pretty permalinks are required for the job URLs to work properly.</p>";
    echo "<p><strong>To fix:</strong> Go to <a href='" . admin_url('options-permalink.php') . "'>Settings → Permalinks</a> and choose any option except 'Plain'.</p>";
    echo "</div>";
}

// Force re-register the post type
if (class_exists('CareerJobCPT')) {
    $cpt = new CareerJobCPT();
    $cpt->register_post_type();
    $cpt->register_taxonomies();
    echo "<p>✅ Re-registered career_job post type</p>";
}

// Force flush rewrite rules
global $wp_rewrite;
$wp_rewrite->init();
$wp_rewrite->flush_rules(true);
echo "<p>✅ Flushed rewrite rules (hard flush)</p>";

// Update the rewrite rules in the database
update_option('rewrite_rules', '');
echo "<p>✅ Cleared rewrite rules cache</p>";

// Test a job URL
$jobs = get_posts(array(
    'post_type' => 'career_job',
    'posts_per_page' => 1,
    'post_status' => 'publish'
));

if (!empty($jobs)) {
    $job = $jobs[0];
    echo "<h3>Testing Job URL:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th style='padding: 10px;'>Type</th><th style='padding: 10px;'>URL</th></tr>";
    
    // Get permalink
    $permalink = get_permalink($job->ID);
    echo "<tr><td style='padding: 10px;'>Current Permalink</td><td style='padding: 10px;'><code>" . esc_html($permalink) . "</code></td></tr>";
    
    // Get expected URL
    $expected = home_url('/job/' . $job->post_name . '/');
    echo "<tr><td style='padding: 10px;'>Expected URL</td><td style='padding: 10px;'><code>" . esc_html($expected) . "</code></td></tr>";
    
    // Check if using pretty permalinks
    if (strpos($permalink, '?p=') !== false) {
        echo "<tr><td colspan='2' style='padding: 10px; background: #ffcccc;'>❌ Still using plain permalinks!</td></tr>";
    } else {
        echo "<tr><td colspan='2' style='padding: 10px; background: #ccffcc;'>✅ Pretty permalinks active!</td></tr>";
    }
    
    echo "</table>";
}

echo "<h3>Next Steps:</h3>";
echo "<ol>";
if (empty($permalink_structure)) {
    echo "<li style='color: red;'><strong>REQUIRED:</strong> <a href='" . admin_url('options-permalink.php') . "'>Go to Settings → Permalinks</a> and select 'Post name' or another pretty permalink option.</li>";
}
echo "<li>After changing permalink settings, return to this page to verify.</li>";
echo "<li>Clear your browser cache and any caching plugins.</li>";
echo "</ol>";

echo "<p><a href='" . home_url('/open-positions/') . "' class='button'>View Job Listings Page</a> ";
echo "<a href='" . admin_url() . "' class='button'>Return to Admin</a></p>";

// Add CSS for buttons
echo "<style>
.button {
    display: inline-block;
    padding: 10px 20px;
    background: #0073aa;
    color: white;
    text-decoration: none;
    border-radius: 3px;
    margin: 5px;
}
.button:hover {
    background: #005a87;
}
</style>";