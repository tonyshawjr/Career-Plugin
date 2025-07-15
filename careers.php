<?php
/**
 * Plugin Name: Careers Manager
 * Plugin URI: https://wordpress.org/plugins/careers-manager
 * Description: A comprehensive careers management plugin for job listings, applications, user dashboards, and admin tools. Compatible with Elementor via shortcodes.
 * Version: 1.0.0
 * Author: Careers Manager Team
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: careers-manager
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CAREERS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CAREERS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CAREERS_PLUGIN_VERSION', '1.0.1');

/**
 * Main Careers Manager class
 */
class CareersManager {
    
    /**
     * Instance of this class
     */
    private static $instance = null;
    
    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Load includes
        $this->load_includes();
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
        // Load text domain
        load_plugin_textdomain('careers-manager', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Initialize components with error handling
        try {
            if (class_exists('CareersPositionsDB')) {
                new CareersPositionsDB();
            }
            
            if (class_exists('CareersApplicationDB')) {
                new CareersApplicationDB();
            }
            
            if (class_exists('CareersUserRoles')) {
                new CareersUserRoles();
            }
            
            if (class_exists('CareersShortcodes')) {
                new CareersShortcodes();
            }
            
            if (class_exists('CareersAuth')) {
                new CareersAuth();
            }
            
            if (class_exists('CareersDashboard')) {
                new CareersDashboard();
            }
            
            if (class_exists('CareersAdmin')) {
                new CareersAdmin();
            }
            
            if (class_exists('CareersEmails')) {
                new CareersEmails();
            }
            
            // Elementor widgets are initialized in their own class file
            
        } catch (Exception $e) {
            error_log('Careers Plugin Initialization Error: ' . $e->getMessage());
        }
        
        // Enqueue assets
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Add custom rewrite rules
        add_action('init', array($this, 'add_rewrite_rules'), 10);
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('template_redirect', array($this, 'handle_position_redirect'));
    }
    
    /**
     * Load includes
     */
    private function load_includes() {
        $includes = array(
            'includes/helpers.php',
            'includes/class-positions-db.php',
            'includes/class-application-db.php',
            'includes/class-user-roles.php',
            'includes/class-shortcodes.php',
            'includes/class-auth.php',
            'includes/class-dashboard.php',
            'includes/class-admin.php',
            'includes/class-emails.php',
            'includes/class-elementor-widgets.php'
        );
        
        foreach ($includes as $file) {
            $file_path = CAREERS_PLUGIN_PATH . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            } else {
                error_log('Careers Plugin: Missing file ' . $file_path);
            }
        }
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        wp_enqueue_style(
            'careers-frontend',
            CAREERS_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            CAREERS_PLUGIN_VERSION
        );
        
        wp_enqueue_script(
            'careers-frontend',
            CAREERS_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            CAREERS_PLUGIN_VERSION,
            true
        );
        
        // Localize script for AJAX
        wp_localize_script('careers-frontend', 'careers_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('careers_nonce'),
        ));
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'careers') !== false || strpos($hook, 'career_job') !== false) {
            wp_enqueue_style(
                'careers-admin',
                CAREERS_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                CAREERS_PLUGIN_VERSION
            );
            
            wp_enqueue_script(
                'careers-admin',
                CAREERS_PLUGIN_URL . 'assets/js/admin.js',
                array('jquery'),
                CAREERS_PLUGIN_VERSION,
                true
            );
            
            // Localize script for AJAX
            wp_localize_script('careers-admin', 'careers_admin_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('careers_nonce'),
            ));
        }
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        try {
            // Load includes first to ensure classes are available
            $this->load_includes();
            
            // Create database tables
            if (class_exists('CareersPositionsDB')) {
                CareersPositionsDB::create_tables();
            }
            
            if (class_exists('CareersApplicationDB')) {
                CareersApplicationDB::create_table();
            }
            
            // Create user roles
            if (class_exists('CareersUserRoles')) {
                $user_roles = new CareersUserRoles();
                if (method_exists($user_roles, 'create_roles')) {
                    $user_roles->create_roles();
                }
            }
            
            // Add rewrite rules before flushing
            $this->add_rewrite_rules();
            
            // Flush rewrite rules
            flush_rewrite_rules();
            
        } catch (Exception $e) {
            // Log the error and deactivate
            error_log('Careers Plugin Activation Error: ' . $e->getMessage());
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die('Careers plugin activation failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    
    /**
     * Add custom rewrite rules for positions and dashboard
     */
    public function add_rewrite_rules() {
        // Add rewrite rule for /open-positions/123
        add_rewrite_rule(
            '^open-positions/([0-9]+)/?$',
            'index.php?careers_position_id=$matches[1]',
            'top'
        );
        
        // Add rewrite rule for /open-positions (listings page)
        add_rewrite_rule(
            '^open-positions/?$',
            'index.php?careers_positions=1',
            'top'
        );
        
        // Add rewrite rule for /apply/123 (application page)
        add_rewrite_rule(
            '^apply/([0-9]+)/?$',
            'index.php?careers_apply_id=$matches[1]',
            'top'
        );
        
        // Add rewrite rules for dashboard and sub-pages
        add_rewrite_rule(
            '^dashboard/?$',
            'index.php?careers_dashboard=main',
            'top'
        );
        
        add_rewrite_rule(
            '^dashboard/([^/]+)/?$',
            'index.php?careers_dashboard=$matches[1]',
            'top'
        );
        
        add_rewrite_rule(
            '^dashboard/([^/]+)/([^/]+)/?$',
            'index.php?careers_dashboard=$matches[1]&careers_action=$matches[2]',
            'top'
        );
        
        add_rewrite_rule(
            '^dashboard/([^/]+)/([^/]+)/([^/]+)/?$',
            'index.php?careers_dashboard=$matches[1]&careers_action=$matches[2]&careers_id=$matches[3]',
            'top'
        );
    }
    
    /**
     * Add custom query vars
     */
    public function add_query_vars($vars) {
        $vars[] = 'careers_position_id';
        $vars[] = 'careers_positions';
        $vars[] = 'careers_apply_id';
        $vars[] = 'careers_dashboard';
        $vars[] = 'careers_action';
        $vars[] = 'careers_id';
        return $vars;
    }
    
    /**
     * Handle position redirect
     */
    public function handle_position_redirect() {
        global $wp_query;
        
        // Check if we're on a position detail page
        $position_id = get_query_var('careers_position_id');
        if ($position_id) {
            $wp_query->is_404 = false;
            $wp_query->is_single = true;
            $wp_query->is_singular = true;
            
            $this->load_position_detail_template($position_id);
            return;
        }
        
        // Check if we're on an application page
        $apply_id = get_query_var('careers_apply_id');
        if ($apply_id) {
            $wp_query->is_404 = false;
            $wp_query->is_page = true;
            $wp_query->is_singular = true;
            
            $this->load_application_template($apply_id);
            return;
        }
        
        // Check if we're on the positions listing page
        $positions_page = get_query_var('careers_positions');
        if ($positions_page) {
            $wp_query->is_404 = false;
            $wp_query->is_page = true;
            $wp_query->is_singular = true;
            
            $this->load_positions_list_template();
            return;
        }
    }
    
    /**
     * Load positions list template
     */
    private function load_positions_list_template() {
        get_header();
        
        echo '<div class="careers-positions-page">';
        echo '<div class="container">';
        echo '<h1>Open Positions</h1>';
        
        // Load the shortcode class and display the list
        if (class_exists('CareersShortcodes')) {
            $shortcodes = new CareersShortcodes();
            echo $shortcodes->careers_list_shortcode(array());
        }
        
        echo '</div>';
        echo '</div>';
        
        get_footer();
        exit;
    }
    
    /**
     * Load position detail template
     */
    private function load_position_detail_template($position_id) {
        get_header();
        
        echo '<div class="careers-position-detail-page">';
        echo '<div class="container">';
        
        // Load the shortcode class and display the position detail
        if (class_exists('CareersShortcodes')) {
            $shortcodes = new CareersShortcodes();
            echo $shortcodes->careers_position_detail_shortcode(array('id' => $position_id));
        }
        
        echo '</div>';
        echo '</div>';
        
        get_footer();
        exit;
    }
    
    /**
     * Load application template
     */
    private function load_application_template($position_id) {
        // First verify the position exists
        if (class_exists('CareersPositionsDB')) {
            $position = CareersPositionsDB::get_position($position_id);
            if (!$position || $position->status !== 'published') {
                global $wp_query;
                $wp_query->set_404();
                status_header(404);
                get_template_part('404');
                exit;
            }
        }
        
        get_header();
        
        echo '<div class="careers-application-page">';
        echo '<div class="container">';
        
        // Load the shortcode class and display the application form
        if (class_exists('CareersShortcodes')) {
            $shortcodes = new CareersShortcodes();
            echo $shortcodes->careers_application_page_shortcode(array('position_id' => $position_id));
        }
        
        echo '</div>';
        echo '</div>';
        
        get_footer();
        exit;
    }
}

// Initialize the plugin
CareersManager::get_instance(); 