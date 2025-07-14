<?php
/**
 * Quick flush permalinks test
 */

// Load WordPress
require_once('../../../wp-load.php');

if (!current_user_can('manage_options')) {
    die('Admin access required');
}

echo "<h2>Dashboard Routing Test</h2>";

// Add the rewrite rules manually
echo "<p>Adding rewrite rules...</p>";

// Dashboard main page
add_rewrite_rule(
    '^dashboard/?$',
    'index.php?careers_dashboard=main',
    'top'
);

// Dashboard with one parameter
add_rewrite_rule(
    '^dashboard/([^/]+)/?$',
    'index.php?careers_dashboard=$matches[1]',
    'top'
);

// Dashboard with action
add_rewrite_rule(
    '^dashboard/([^/]+)/([^/]+)/?$',
    'index.php?careers_dashboard=$matches[1]&careers_action=$matches[2]',
    'top'
);

// Dashboard with action and ID
add_rewrite_rule(
    '^dashboard/([^/]+)/([^/]+)/([^/]+)/?$',
    'index.php?careers_dashboard=$matches[1]&careers_action=$matches[2]&careers_id=$matches[3]',
    'top'
);

echo "<p>✅ Rewrite rules added</p>";

// Flush
flush_rewrite_rules();
echo "<p>✅ Permalinks flushed!</p>";

echo "<h3>Test URLs:</h3>";
echo "<ul>";
echo "<li><a href='" . home_url('/dashboard') . "'>Dashboard Home</a></li>";
echo "<li><a href='" . home_url('/dashboard/jobs') . "'>Jobs Page</a></li>";
echo "<li><a href='" . home_url('/dashboard/jobs/edit/55') . "'>Edit Job (Test)</a></li>";
echo "</ul>";

echo "<p><a href='" . home_url('/dashboard') . "'>Test Dashboard Now</a></p>";