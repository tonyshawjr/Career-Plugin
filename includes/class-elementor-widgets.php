<?php
/**
 * Careers Elementor Widgets Handler
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CareersElementorWidgets {
    
    public function __construct() {
        add_action('elementor/widgets/register', array($this, 'register_widgets'));
        add_action('elementor/elements/categories_registered', array($this, 'add_widget_categories'));
    }
    
    /**
     * Register custom Elementor widgets
     */
    public function register_widgets($widgets_manager) {
        // Check if Elementor is active
        if (!did_action('elementor/loaded')) {
            return;
        }
        
        // Include widget files
        require_once plugin_dir_path(__FILE__) . 'elementor-widgets/login-logout-widget.php';
        
        // Register widgets
        $widgets_manager->register(new \Careers_Login_Logout_Widget());
    }
    
    /**
     * Add custom widget categories
     */
    public function add_widget_categories($elements_manager) {
        $elements_manager->add_category(
            'careers',
            [
                'title' => esc_html__('Careers Manager', 'careers-manager'),
                'icon' => 'eicon-posts-ticker',
            ]
        );
    }
}

// Initialize only if Elementor is active
if (did_action('elementor/loaded')) {
    new CareersElementorWidgets();
}