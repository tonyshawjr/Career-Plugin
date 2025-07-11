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
define('CAREERS_PLUGIN_VERSION', '1.0.0');

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
        
        // Initialize components
        if (class_exists('CareerJobCPT')) {
            new CareerJobCPT();
        }
        
        if (class_exists('CareersApplicationDB')) {
            new CareersApplicationDB();
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
        
        // Enqueue assets
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Template handling
        add_filter('single_template', array($this, 'single_template'));
        add_filter('archive_template', array($this, 'archive_template'));
        
        // Ensure proper permalinks
        add_filter('post_type_link', array($this, 'career_job_permalink'), 10, 2);
        
        // Add custom rewrite rules
        add_action('init', array($this, 'add_rewrite_rules'), 20);
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('template_redirect', array($this, 'handle_job_redirect'));
    }
    
    /**
     * Load includes
     */
    private function load_includes() {
        require_once CAREERS_PLUGIN_PATH . 'includes/helpers.php';
        require_once CAREERS_PLUGIN_PATH . 'includes/class-job-cpt.php';
        require_once CAREERS_PLUGIN_PATH . 'includes/class-application-db.php';
        require_once CAREERS_PLUGIN_PATH . 'includes/class-shortcodes.php';
        require_once CAREERS_PLUGIN_PATH . 'includes/class-auth.php';
        require_once CAREERS_PLUGIN_PATH . 'includes/class-dashboard.php';
        require_once CAREERS_PLUGIN_PATH . 'includes/class-admin.php';
        require_once CAREERS_PLUGIN_PATH . 'includes/class-emails.php';
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
        // Load includes first to ensure classes are available
        $this->load_includes();
        
        // Register post type to ensure rewrite rules are created
        if (class_exists('CareerJobCPT')) {
            $cpt = new CareerJobCPT();
            $cpt->register_post_type();
            $cpt->register_taxonomies();
        }
        
        // Create database table
        if (class_exists('CareersApplicationDB')) {
            CareersApplicationDB::create_table();
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Load single template for career_job posts
     */
    public function single_template($template) {
        if (is_singular('career_job')) {
            $plugin_template = CAREERS_PLUGIN_PATH . 'templates/single-career_job.php';
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
        return $template;
    }
    
    /**
     * Load archive template for career_job posts
     */
    public function archive_template($template) {
        if (is_post_type_archive('career_job')) {
            $plugin_template = CAREERS_PLUGIN_PATH . 'templates/archive-career_job.php';
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
        return $template;
    }
    
    /**
     * Filter career job permalinks to ensure proper URL structure
     */
    public function career_job_permalink($permalink, $post) {
        if ($post->post_type !== 'career_job') {
            return $permalink;
        }
        
        // If using plain permalinks, don't modify
        if (empty(get_option('permalink_structure'))) {
            return $permalink;
        }
        
        // Ensure we're using the correct structure
        return home_url('/job/' . $post->post_name . '/');
    }
    
    /**
     * Add custom rewrite rules for job posts
     */
    public function add_rewrite_rules() {
        // Add rewrite rule for /job/job-name/
        add_rewrite_rule(
            '^job/([^/]+)/?$',
            'index.php?career_job=$matches[1]',
            'top'
        );
        
        // Alternative rule using post name
        add_rewrite_rule(
            '^job/([^/]+)/?$',
            'index.php?post_type=career_job&name=$matches[1]',
            'top'
        );
    }
    
    /**
     * Add custom query vars
     */
    public function add_query_vars($vars) {
        $vars[] = 'career_job';
        return $vars;
    }
    
    /**
     * Handle job redirect
     */
    public function handle_job_redirect() {
        // Check if we're on a job URL
        if (preg_match('/^\/job\/([^\/]+)\/?$/', $_SERVER['REQUEST_URI'], $matches)) {
            $job_slug = $matches[1];
            
            // Find the job by slug
            $args = array(
                'name' => $job_slug,
                'post_type' => 'career_job',
                'post_status' => 'publish',
                'posts_per_page' => 1
            );
            
            $jobs = get_posts($args);
            
            if (!empty($jobs)) {
                global $wp_query, $post;
                
                // Set up the query
                $post = $jobs[0];
                $wp_query->post = $post;
                $wp_query->posts = array($post);
                $wp_query->queried_object = $post;
                $wp_query->queried_object_id = $post->ID;
                $wp_query->found_posts = 1;
                $wp_query->post_count = 1;
                $wp_query->is_single = true;
                $wp_query->is_singular = true;
                $wp_query->is_404 = false;
                
                // Setup post data
                setup_postdata($post);
                
                // Load the template
                $template = $this->single_template('');
                if ($template) {
                    include($template);
                    exit;
                }
            }
        }
    }
}

// Initialize the plugin
CareersManager::get_instance(); 