<?php
/**
 * Apply rewrite rules for job posts
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('You must be an administrator to run this script.');
}

echo "<h2>Applying Job Post Rewrite Rules</h2>";

// Trigger the plugin's rewrite rules
if (class_exists('CareersManager')) {
    $plugin = CareersManager::get_instance();
    
    // Add the rewrite rules
    $plugin->add_rewrite_rules();
    echo "<p>✅ Added custom rewrite rules for /job/job-name/ URLs</p>";
}

// Flush rewrite rules
flush_rewrite_rules(true);
echo "<p>✅ Flushed rewrite rules</p>";

// Test the rules
global $wp_rewrite;
$rules = $wp_rewrite->wp_rewrite_rules();

echo "<h3>Job-related Rewrite Rules:</h3>";
echo "<pre style='background: #f0f0f0; padding: 10px;'>";
foreach ($rules as $pattern => $rewrite) {
    if (strpos($pattern, 'job') !== false || strpos($rewrite, 'career_job') !== false) {
        echo htmlspecialchars($pattern) . ' => ' . htmlspecialchars($rewrite) . "\n";
    }
}
echo "</pre>";

// Test with actual jobs
$jobs = get_posts(array(
    'post_type' => 'career_job',
    'posts_per_page' => 3,
    'post_status' => 'publish'
));

if (!empty($jobs)) {
    echo "<h3>Test Job URLs:</h3>";
    echo "<ul>";
    foreach ($jobs as $job) {
        $url = home_url('/job/' . $job->post_name . '/');
        echo "<li>";
        echo "<strong>" . esc_html($job->post_title) . "</strong><br>";
        echo "URL: <a href='" . esc_url($url) . "' target='_blank'>" . esc_html($url) . "</a><br>";
        echo "Slug: " . esc_html($job->post_name);
        echo "</li>";
    }
    echo "</ul>";
}

echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 20px 0;'>";
echo "<h4>✅ Rewrite Rules Applied!</h4>";
echo "<p>The job URLs should now work correctly. Test by clicking on the job links above.</p>";
echo "</div>";

echo "<p><a href='" . home_url('/open-positions/') . "' style='display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 3px;'>Go to Job Listings</a></p>";