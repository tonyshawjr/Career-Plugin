<?php
/**
 * Test Script for Careers Plugin
 * Place this in the plugin directory and access via browser to test functionality
 */

// Only run if accessed directly
if ($_SERVER['REQUEST_METHOD'] === 'GET' && basename($_SERVER['PHP_SELF']) === 'test-plugin.php') {
    // Include WordPress
    require_once '../../../wp-config.php';
    
    echo '<h1>Careers Plugin Test</h1>';
    
    // Test database table creation
    echo '<h2>Testing Database Tables</h2>';
    
    global $wpdb;
    
    $positions_table = $wpdb->prefix . 'careers_positions';
    $locations_table = $wpdb->prefix . 'careers_locations';
    $applications_table = $wpdb->prefix . 'careers_applications';
    
    $positions_exists = $wpdb->get_var("SHOW TABLES LIKE '$positions_table'");
    $locations_exists = $wpdb->get_var("SHOW TABLES LIKE '$locations_table'");
    $applications_exists = $wpdb->get_var("SHOW TABLES LIKE '$applications_table'");
    
    echo '<ul>';
    echo '<li>Positions table: ' . ($positions_exists ? '✅ EXISTS' : '❌ MISSING') . '</li>';
    echo '<li>Locations table: ' . ($locations_exists ? '✅ EXISTS' : '❌ MISSING') . '</li>';
    echo '<li>Applications table: ' . ($applications_exists ? '✅ EXISTS' : '❌ MISSING') . '</li>';
    echo '</ul>';
    
    // Test class loading
    echo '<h2>Testing Class Loading</h2>';
    echo '<ul>';
    echo '<li>CareersPositionsDB: ' . (class_exists('CareersPositionsDB') ? '✅ LOADED' : '❌ NOT LOADED') . '</li>';
    echo '<li>CareersApplicationDB: ' . (class_exists('CareersApplicationDB') ? '✅ LOADED' : '❌ NOT LOADED') . '</li>';
    echo '<li>CareersShortcodes: ' . (class_exists('CareersShortcodes') ? '✅ LOADED' : '❌ NOT LOADED') . '</li>';
    echo '<li>CareersDashboard: ' . (class_exists('CareersDashboard') ? '✅ LOADED' : '❌ NOT LOADED') . '</li>';
    echo '</ul>';
    
    // Test shortcode registration
    echo '<h2>Testing Shortcodes</h2>';
    global $shortcode_tags;
    echo '<ul>';
    echo '<li>[careers_list]: ' . (isset($shortcode_tags['careers_list']) ? '✅ REGISTERED' : '❌ NOT REGISTERED') . '</li>';
    echo '<li>[careers_position_detail]: ' . (isset($shortcode_tags['careers_position_detail']) ? '✅ REGISTERED' : '❌ NOT REGISTERED') . '</li>';
    echo '<li>[careers_form]: ' . (isset($shortcode_tags['careers_form']) ? '✅ REGISTERED' : '❌ NOT REGISTERED') . '</li>';
    echo '</ul>';
    
    // Test data insertion
    if (class_exists('CareersPositionsDB')) {
        echo '<h2>Testing Data Operations</h2>';
        
        // Test location insertion
        $location_result = CareersPositionsDB::insert_location('Test Location');
        echo '<p>Location insertion: ' . (is_wp_error($location_result) ? '❌ FAILED: ' . $location_result->get_error_message() : '✅ SUCCESS') . '</p>';
        
        // Test position insertion
        $position_data = array(
            'position_name' => 'Test Position',
            'location' => 'Test Location',
            'position_overview' => 'This is a test position',
            'responsibilities' => "Responsibility 1\nResponsibility 2",
            'requirements' => "Requirement 1\nRequirement 2",
            'equipment' => "Equipment 1\nEquipment 2",
            'license_info' => 'Test license info',
            'has_vehicle' => 1,
            'vehicle_description' => 'Test vehicle',
            'status' => 'published'
        );
        
        $position_result = CareersPositionsDB::insert_position($position_data);
        echo '<p>Position insertion: ' . (is_wp_error($position_result) ? '❌ FAILED: ' . $position_result->get_error_message() : '✅ SUCCESS (ID: ' . $position_result . ')') . '</p>';
        
        // Test data retrieval
        $positions = CareersPositionsDB::get_positions(array('limit' => 5));
        echo '<p>Position retrieval: ' . (empty($positions) ? '❌ NO DATA' : '✅ SUCCESS (' . count($positions) . ' positions)') . '</p>';
        
        $locations = CareersPositionsDB::get_locations();
        echo '<p>Location retrieval: ' . (empty($locations) ? '❌ NO DATA' : '✅ SUCCESS (' . count($locations) . ' locations)') . '</p>';
    }
    
    // Test shortcode output
    if (function_exists('do_shortcode')) {
        echo '<h2>Testing Shortcode Output</h2>';
        
        echo '<h3>[careers_list] output:</h3>';
        echo '<div style="border: 1px solid #ccc; padding: 10px; background: #f9f9f9;">';
        echo do_shortcode('[careers_list]');
        echo '</div>';
        
        if (!empty($position_result) && !is_wp_error($position_result)) {
            echo '<h3>[careers_position_detail] output (for position ID ' . $position_result . '):</h3>';
            echo '<div style="border: 1px solid #ccc; padding: 10px; background: #f9f9f9;">';
            echo do_shortcode('[careers_position_detail id="' . $position_result . '"]');
            echo '</div>';
        }
    }
    
    echo '<h2>URLs to Test</h2>';
    echo '<ul>';
    echo '<li><a href="/dashboard">Dashboard</a></li>';
    echo '<li><a href="/dashboard/positions">Position Management</a></li>';
    echo '<li><a href="/dashboard/positions/create">Create Position</a></li>';
    echo '<li><a href="/dashboard/locations">Location Management</a></li>';
    echo '<li><a href="/open-positions">Job Listings</a></li>';
    if (!empty($position_result) && !is_wp_error($position_result)) {
        echo '<li><a href="/open-positions/' . $position_result . '">Job Detail</a></li>';
    }
    echo '</ul>';
    
    exit;
}
?>