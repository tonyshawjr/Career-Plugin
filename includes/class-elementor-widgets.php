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
        
        // Also try the deprecated hook for older Elementor versions
        add_action('elementor/widgets/widgets_registered', array($this, 'register_widgets_deprecated'));
    }
    
    /**
     * Register custom Elementor widgets
     */
    public function register_widgets($widgets_manager) {
        // Include widget files
        require_once plugin_dir_path(__FILE__) . 'elementor-widgets/login-logout-widget.php';
        
        // Register widgets
        if (class_exists('Careers_Login_Logout_Widget')) {
            $widgets_manager->register(new \Careers_Login_Logout_Widget());
        }
    }
    
    /**
     * Register widgets for older Elementor versions
     */
    public function register_widgets_deprecated($widgets_manager) {
        $this->register_widgets($widgets_manager);
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

// Initialize when Elementor is loaded
add_action('elementor/loaded', function() {
    new CareersElementorWidgets();
});