<?php
/**
 * Uninstall script for Careers Manager plugin
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Delete all career_job posts
$posts = get_posts(array(
    'post_type' => 'career_job',
    'numberposts' => -1,
    'post_status' => 'any'
));

foreach ($posts as $post) {
    wp_delete_post($post->ID, true);
}

// Drop custom table
$table_name = $wpdb->prefix . 'careers_applications';
$wpdb->query("DROP TABLE IF EXISTS $table_name");

// Delete options
delete_option('careers_manager_version');
delete_option('careers_manager_settings');

// Delete user meta
$wpdb->delete($wpdb->usermeta, array('meta_key' => 'careers_applicant_data'));

// Delete transients
delete_transient('careers_job_count');
delete_transient('careers_application_stats');

// Clear any cached data
wp_cache_flush(); 