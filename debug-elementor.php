<?php
/**
 * Debug Script for Elementor Widget Registration
 */

// Only run if accessed directly
if ($_SERVER['REQUEST_METHOD'] === 'GET' && basename($_SERVER['PHP_SELF']) === 'debug-elementor.php') {
    // Include WordPress
    require_once '../../../wp-config.php';
    
    echo '<h1>Elementor Widget Debug</h1>';
    
    // Check if Elementor is active
    echo '<h2>Elementor Status</h2>';
    echo '<ul>';
    echo '<li>Elementor Plugin Active: ' . (class_exists('\Elementor\Plugin') ? '✅ YES' : '❌ NO') . '</li>';
    echo '<li>Elementor Loaded Action: ' . (did_action('elementor/loaded') ? '✅ YES' : '❌ NO') . '</li>';
    echo '<li>Elementor Version: ' . (defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : 'Not defined') . '</li>';
    echo '</ul>';
    
    // Check widget file
    echo '<h2>Widget Files</h2>';
    $widget_file = __DIR__ . '/includes/elementor-widgets/login-logout-widget.php';
    echo '<ul>';
    echo '<li>Widget File Exists: ' . (file_exists($widget_file) ? '✅ YES' : '❌ NO') . '</li>';
    echo '<li>Widget File Path: ' . $widget_file . '</li>';
    echo '</ul>';
    
    // Test widget class loading
    if (file_exists($widget_file)) {
        require_once $widget_file;
        echo '<li>Widget Class Exists: ' . (class_exists('Careers_Login_Logout_Widget') ? '✅ YES' : '❌ NO') . '</li>';
    }
    
    // Check handler class
    echo '<h2>Handler Class</h2>';
    $handler_file = __DIR__ . '/includes/class-elementor-widgets.php';
    echo '<ul>';
    echo '<li>Handler File Exists: ' . (file_exists($handler_file) ? '✅ YES' : '❌ NO') . '</li>';
    echo '<li>Handler Class Exists: ' . (class_exists('CareersElementorWidgets') ? '✅ LOADED' : '❌ NOT LOADED') . '</li>';
    echo '</ul>';
    
    // Test hooks
    echo '<h2>Hook Status</h2>';
    echo '<ul>';
    echo '<li>elementor/loaded hook fired: ' . (did_action('elementor/loaded') ? '✅ YES' : '❌ NO') . '</li>';
    echo '<li>elementor/widgets/register hook fired: ' . (did_action('elementor/widgets/register') ? '✅ YES' : '❌ NO') . '</li>';
    echo '<li>elementor/widgets/widgets_registered hook fired: ' . (did_action('elementor/widgets/widgets_registered') ? '✅ YES' : '❌ NO') . '</li>';
    echo '</ul>';
    
    // Check if we can get the widgets manager
    if (class_exists('\Elementor\Plugin')) {
        try {
            $widgets_manager = \Elementor\Plugin::instance()->widgets_manager;
            echo '<h2>Widgets Manager</h2>';
            echo '<ul>';
            echo '<li>Widgets Manager Available: ✅ YES</li>';
            
            // Get all registered widgets
            $widgets = $widgets_manager->get_widget_types();
            echo '<li>Total Registered Widgets: ' . count($widgets) . '</li>';
            
            // Check if our widget is registered
            $our_widget_found = false;
            foreach ($widgets as $widget_name => $widget_instance) {
                if ($widget_name === 'careers_login_logout') {
                    $our_widget_found = true;
                    break;
                }
            }
            echo '<li>Our Widget Registered: ' . ($our_widget_found ? '✅ YES' : '❌ NO') . '</li>';
            
            if (!$our_widget_found) {
                echo '<li>Registered Widget Names: ' . implode(', ', array_keys($widgets)) . '</li>';
            }
            
            echo '</ul>';
        } catch (Exception $e) {
            echo '<p>Error accessing widgets manager: ' . $e->getMessage() . '</p>';
        }
    }
    
    // Try to manually register the widget
    echo '<h2>Manual Registration Test</h2>';
    if (class_exists('\Elementor\Plugin') && file_exists($widget_file)) {
        try {
            require_once $widget_file;
            if (class_exists('Careers_Login_Logout_Widget')) {
                $widget = new Careers_Login_Logout_Widget();
                echo '<ul>';
                echo '<li>Widget Instance Created: ✅ YES</li>';
                echo '<li>Widget Name: ' . $widget->get_name() . '</li>';
                echo '<li>Widget Title: ' . $widget->get_title() . '</li>';
                echo '<li>Widget Categories: ' . implode(', ', $widget->get_categories()) . '</li>';
                echo '</ul>';
            }
        } catch (Exception $e) {
            echo '<p>Error creating widget instance: ' . $e->getMessage() . '</p>';
        }
    }
    
    exit;
}
?>