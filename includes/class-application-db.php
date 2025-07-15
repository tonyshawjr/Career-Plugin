<?php
/**
 * Careers Application Database Handler
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CareersApplicationDB {
    
    private static $table_name = 'careers_applications';
    
    public function __construct() {
        // Hook into WordPress initialization
        add_action('init', array($this, 'maybe_create_table'));
    }
    
    /**
     * Create the applications table
     */
    public static function create_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            user_id int(11) NOT NULL,
            job_id int(11) NOT NULL,
            resume_url text,
            cover_letter_url text,
            status enum('pending','reviewed','interviewing','hired','rejected') DEFAULT 'pending',
            meta longtext,
            submitted_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY job_id (job_id),
            KEY status (status),
            KEY submitted_at (submitted_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Store table version
        update_option('careers_applications_table_version', '1.0');
    }
    
    /**
     * Maybe create table if it doesn't exist
     */
    public function maybe_create_table() {
        $installed_version = get_option('careers_applications_table_version');
        if ($installed_version != '1.0') {
            self::create_table();
        }
    }
    
    /**
     * Insert a new application
     */
    public static function insert_application($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        $defaults = array(
            'user_id' => 0,
            'job_id' => 0,
            'resume_url' => '',
            'cover_letter_url' => '',
            'status' => 'pending',
            'meta' => '',
            'submitted_at' => current_time('mysql')
        );
        
        $data = wp_parse_args($data, $defaults);
        
        // Validate required fields
        if (empty($data['user_id']) || empty($data['job_id'])) {
            return new WP_Error('missing_data', __('User ID and Job ID are required.', 'careers-manager'));
        }
        
        // Check if user already applied for this job
        $existing = self::get_application_by_user_job($data['user_id'], $data['job_id']);
        if ($existing) {
            return new WP_Error('already_applied', __('You have already applied for this job.', 'careers-manager'));
        }
        
        // Serialize meta data if it's an array
        if (is_array($data['meta'])) {
            $data['meta'] = maybe_serialize($data['meta']);
        }
        
        $result = $wpdb->insert(
            $table_name,
            $data,
            array('%d', '%d', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($result === false) {
            return new WP_Error('insert_failed', __('Failed to submit application.', 'careers-manager'));
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Get application by ID
     */
    public static function get_application($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        $application = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $id
        ));
        
        if ($application && $application->meta) {
            $application->meta = maybe_unserialize($application->meta);
        }
        
        return $application;
    }
    
    /**
     * Get application by user and job
     */
    public static function get_application_by_user_job($user_id, $job_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        $application = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d AND job_id = %d",
            $user_id,
            $job_id
        ));
        
        if ($application && $application->meta) {
            $application->meta = maybe_unserialize($application->meta);
        }
        
        return $application;
    }
    
    /**
     * Get applications by user
     */
    public static function get_user_applications($user_id, $limit = 20, $offset = 0) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        $applications = $wpdb->get_results($wpdb->prepare(
            "SELECT a.*, p.post_title as job_title 
             FROM $table_name a 
             LEFT JOIN {$wpdb->posts} p ON a.job_id = p.ID 
             WHERE a.user_id = %d 
             ORDER BY a.submitted_at DESC 
             LIMIT %d OFFSET %d",
            $user_id,
            $limit,
            $offset
        ));
        
        foreach ($applications as $application) {
            if ($application->meta) {
                $application->meta = maybe_unserialize($application->meta);
            }
        }
        
        return $applications;
    }
    
    /**
     * Get applications by job
     */
    public static function get_job_applications($job_id, $status = '', $limit = 20, $offset = 0) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        $where = $wpdb->prepare("WHERE a.job_id = %d", $job_id);
        
        if (!empty($status)) {
            $where .= $wpdb->prepare(" AND a.status = %s", $status);
        }
        
        $applications = $wpdb->get_results($wpdb->prepare(
            "SELECT a.*, u.display_name, u.user_email 
             FROM $table_name a 
             LEFT JOIN {$wpdb->users} u ON a.user_id = u.ID 
             $where 
             ORDER BY a.submitted_at DESC 
             LIMIT %d OFFSET %d",
            $limit,
            $offset
        ));
        
        foreach ($applications as $application) {
            if ($application->meta) {
                $application->meta = maybe_unserialize($application->meta);
            }
        }
        
        return $applications;
    }
    
    /**
     * Get all applications with filters
     */
    public static function get_applications($args = array()) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        $defaults = array(
            'status' => '',
            'job_id' => '',
            'user_id' => '',
            'limit' => 20,
            'offset' => 0,
            'orderby' => 'submitted_at',
            'order' => 'DESC'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where_conditions = array();
        $where_values = array();
        
        if (!empty($args['status'])) {
            $where_conditions[] = "a.status = %s";
            $where_values[] = $args['status'];
        }
        
        if (!empty($args['job_id'])) {
            $where_conditions[] = "a.job_id = %d";
            $where_values[] = $args['job_id'];
        }
        
        if (!empty($args['user_id'])) {
            $where_conditions[] = "a.user_id = %d";
            $where_values[] = $args['user_id'];
        }
        
        $where_clause = '';
        if (!empty($where_conditions)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        }
        
        $orderby = sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']);
        
        $query = "SELECT a.*, p.post_title as job_title, u.display_name, u.user_email 
                  FROM $table_name a 
                  LEFT JOIN {$wpdb->posts} p ON a.job_id = p.ID 
                  LEFT JOIN {$wpdb->users} u ON a.user_id = u.ID 
                  $where_clause 
                  ORDER BY $orderby 
                  LIMIT %d OFFSET %d";
        
        $where_values[] = $args['limit'];
        $where_values[] = $args['offset'];
        
        $applications = $wpdb->get_results($wpdb->prepare($query, $where_values));
        
        foreach ($applications as $application) {
            if ($application->meta) {
                $application->meta = maybe_unserialize($application->meta);
            }
        }
        
        return $applications;
    }
    
    /**
     * Update application status
     */
    public static function update_status($id, $status) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        $valid_statuses = array('pending', 'reviewed', 'interviewing', 'hired', 'rejected');
        
        if (!in_array($status, $valid_statuses)) {
            return new WP_Error('invalid_status', __('Invalid status.', 'careers-manager'));
        }
        
        $result = $wpdb->update(
            $table_name,
            array('status' => $status, 'updated_at' => current_time('mysql')),
            array('id' => $id),
            array('%s', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('update_failed', __('Failed to update application status.', 'careers-manager'));
        }
        
        return true;
    }
    
    /**
     * Delete application
     */
    public static function delete_application($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        // Get application data first for cleanup
        $application = self::get_application($id);
        
        if ($application) {
            // Delete associated files
            if (!empty($application->resume_url)) {
                $upload_dir = wp_upload_dir();
                $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $application->resume_url);
                if (file_exists($file_path)) {
                    wp_delete_file($file_path);
                }
            }
            
            if (!empty($application->cover_letter_url)) {
                $upload_dir = wp_upload_dir();
                $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $application->cover_letter_url);
                if (file_exists($file_path)) {
                    wp_delete_file($file_path);
                }
            }
        }
        
        $result = $wpdb->delete(
            $table_name,
            array('id' => $id),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Get application statistics
     */
    public static function get_stats() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        $stats = array();
        
        // Total applications
        $stats['total'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        
        // Applications by status
        $status_counts = $wpdb->get_results(
            "SELECT status, COUNT(*) as count FROM $table_name GROUP BY status"
        );
        
        foreach ($status_counts as $row) {
            $stats['by_status'][$row->status] = $row->count;
        }
        
        // Applications by job
        $job_counts = $wpdb->get_results(
            "SELECT a.job_id, p.post_title, COUNT(*) as count 
             FROM $table_name a 
             LEFT JOIN {$wpdb->posts} p ON a.job_id = p.ID 
             GROUP BY a.job_id 
             ORDER BY count DESC 
             LIMIT 10"
        );
        
        $stats['by_job'] = $job_counts;
        
        // Recent applications (last 30 days)
        $stats['recent'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM $table_name 
             WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
        );
        
        return $stats;
    }
    
    /**
     * Get application count for a specific job
     */
    public static function get_applications_count_by_job($job_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE job_id = %d",
            $job_id
        ));
        
        return intval($count);
    }
} 