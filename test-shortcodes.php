<?php
/**
 * Test if Careers Manager shortcodes are registered
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if plugin is active
$is_active = is_plugin_active('careers/careers.php');

echo "<h2>Careers Manager Plugin Test</h2>";
echo "<p>Plugin Status: " . ($is_active ? '<span style="color:green;">ACTIVE</span>' : '<span style="color:red;">INACTIVE</span>') . "</p>";

// Check if classes exist
echo "<h3>Classes:</h3>";
echo "<ul>";
echo "<li>CareersManager: " . (class_exists('CareersManager') ? '✅' : '❌') . "</li>";
echo "<li>CareersShortcodes: " . (class_exists('CareersShortcodes') ? '✅' : '❌') . "</li>";
echo "</ul>";

// Check shortcodes
echo "<h3>Registered Shortcodes:</h3>";
global $shortcode_tags;
$careers_shortcodes = [
    'careers_job_listings',
    'careers_apply_form',
    'careers_dashboard',
    'careers_auth_form',
    'careers_admin_dashboard',
    'careers_debug'
];

echo "<ul>";
foreach ($careers_shortcodes as $shortcode) {
    $registered = isset($shortcode_tags[$shortcode]);
    echo "<li>[$shortcode]: " . ($registered ? '✅ Registered' : '❌ NOT Registered') . "</li>";
}
echo "</ul>";

// Test shortcode output
echo "<h3>Test Shortcode Output:</h3>";
echo "<p>Testing [careers_debug] shortcode:</p>";
echo "<div style='border: 1px solid #ccc; padding: 10px;'>";
echo do_shortcode('[careers_debug]');
echo "</div>";

echo "<hr>";
echo "<p><strong>Solution:</strong> If shortcodes are not registered, go to WordPress Admin → Plugins → Deactivate and Reactivate the Careers Manager plugin.</p>";