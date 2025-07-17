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
    private static $notes_table_name = 'careers_application_notes';
    
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
        $notes_table_name = $wpdb->prefix . self::$notes_table_name;
        
        error_log('Careers Debug: Creating tables - Applications: ' . $table_name . ', Notes: ' . $notes_table_name);
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Applications table with updated status enum
        $sql = "CREATE TABLE $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            user_id int(11) NOT NULL,
            job_id int(11) NOT NULL DEFAULT 0,
            resume_url text,
            cover_letter_url text,
            status enum('new','under_review','contacted','interview','hired','rejected') DEFAULT 'new',
            meta longtext,
            submitted_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY job_id (job_id),
            KEY status (status),
            KEY submitted_at (submitted_at)
        ) $charset_collate;";
        
        // Application notes table
        $notes_sql = "CREATE TABLE $notes_table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            application_id int(11) NOT NULL,
            user_id int(11) NOT NULL,
            content text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY application_id (application_id),
            KEY user_id (user_id),
            KEY created_at (created_at),
            FOREIGN KEY (application_id) REFERENCES $table_name(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        error_log('Careers Debug: Applications table SQL: ' . $sql);
        error_log('Careers Debug: Notes table SQL: ' . $notes_sql);
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        $result1 = dbDelta($sql);
        $result2 = dbDelta($notes_sql);
        
        error_log('Careers Debug: dbDelta result for applications table: ' . print_r($result1, true));
        error_log('Careers Debug: dbDelta result for notes table: ' . print_r($result2, true));
        
        // Check if tables exist after creation
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
        $notes_table_exists = $wpdb->get_var("SHOW TABLES LIKE '$notes_table_name'");
        
        error_log('Careers Debug: Applications table exists after creation: ' . ($table_exists ? 'YES' : 'NO'));
        error_log('Careers Debug: Notes table exists after creation: ' . ($notes_table_exists ? 'YES' : 'NO'));
        
        // Store table version
        update_option('careers_applications_table_version', '2.0');
        error_log('Careers Debug: Table version updated to 2.0');
    }
    
    /**
     * Maybe create table if it doesn't exist
     */
    public function maybe_create_table() {
        $installed_version = get_option('careers_applications_table_version');
        if ($installed_version != '2.0') {
            self::create_table();
        }
    }
    
    /**
     * Insert a new application
     */
    public static function insert_application($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        // Force table creation if it doesn't exist
        self::create_table();
        
        // Debug: Log the table name and data being inserted
        error_log('Careers Debug: Inserting application into table: ' . $table_name);
        error_log('Careers Debug: Application data: ' . print_r($data, true));
        
        $defaults = array(
            'user_id' => 0,
            'job_id' => 0,
            'resume_url' => '',
            'cover_letter_url' => '',
            'status' => 'new',
            'meta' => '',
            'submitted_at' => current_time('mysql')
        );
        
        $data = wp_parse_args($data, $defaults);
        
        // Validate required fields
        if (empty($data['user_id'])) {
            error_log('Careers Debug: Missing user_id');
            return new WP_Error('missing_data', __('User ID is required.', 'careers-manager'));
        }
        
        // For general applications, job_id can be 0
        if (empty($data['job_id'])) {
            $data['job_id'] = 0;
        }
        
        // Check if user already applied for this specific job (not for general applications)
        if ($data['job_id'] > 0) {
            $existing = self::get_application_by_user_job($data['user_id'], $data['job_id']);
            if ($existing) {
                error_log('Careers Debug: User already applied for this job');
                return new WP_Error('already_applied', __('You have already applied for this job.', 'careers-manager'));
            }
        }
        
        // Serialize meta data if it's an array
        if (is_array($data['meta'])) {
            $data['meta'] = maybe_serialize($data['meta']);
        }
        
        // Debug: Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
        error_log('Careers Debug: Table exists check: ' . ($table_exists ? 'YES' : 'NO'));
        
        $result = $wpdb->insert(
            $table_name,
            $data,
            array('%d', '%d', '%s', '%s', '%s', '%s', '%s')
        );
        
        // Debug: Log the result and any error
        error_log('Careers Debug: Insert result: ' . ($result === false ? 'FALSE' : $result));
        if ($wpdb->last_error) {
            error_log('Careers Debug: MySQL Error: ' . $wpdb->last_error);
        }
        
        if ($result === false) {
            return new WP_Error('insert_failed', __('Failed to submit application.', 'careers-manager'));
        }
        
        $insert_id = $wpdb->insert_id;
        error_log('Careers Debug: Insert ID: ' . $insert_id);
        
        return $insert_id;
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
        
        $valid_statuses = array('new', 'under_review', 'contacted', 'interview', 'hired', 'rejected');
        
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
     * Add a note to an application
     */
    public static function add_note($application_id, $user_id, $content) {
        global $wpdb;
        
        $notes_table = $wpdb->prefix . self::$notes_table_name;
        
        if (empty($content)) {
            return new WP_Error('empty_content', __('Note content cannot be empty.', 'careers-manager'));
        }
        
        $result = $wpdb->insert(
            $notes_table,
            array(
                'application_id' => $application_id,
                'user_id' => $user_id,
                'content' => sanitize_textarea_field($content),
                'created_at' => current_time('mysql')
            ),
            array('%d', '%d', '%s', '%s')
        );
        
        if ($result === false) {
            return new WP_Error('insert_failed', __('Failed to add note.', 'careers-manager'));
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Get notes for an application
     */
    public static function get_application_notes($application_id, $limit = 0) {
        global $wpdb;
        
        $notes_table = $wpdb->prefix . self::$notes_table_name;
        
        $limit_clause = '';
        if ($limit > 0) {
            $limit_clause = $wpdb->prepare(' LIMIT %d', $limit);
        }
        
        $notes = $wpdb->get_results($wpdb->prepare(
            "SELECT n.*, u.display_name, u.user_email 
             FROM $notes_table n 
             LEFT JOIN {$wpdb->users} u ON n.user_id = u.ID 
             WHERE n.application_id = %d 
             ORDER BY n.created_at DESC" . $limit_clause,
            $application_id
        ));
        
        return $notes;
    }
    
    /**
     * Delete a note
     */
    public static function delete_note($note_id, $user_id = null) {
        global $wpdb;
        
        $notes_table = $wpdb->prefix . self::$notes_table_name;
        
        $where = array('id' => $note_id);
        $where_format = array('%d');
        
        // If user_id is provided, only allow deletion of own notes (unless admin)
        if ($user_id && !current_user_can('manage_options')) {
            $where['user_id'] = $user_id;
            $where_format[] = '%d';
        }
        
        $result = $wpdb->delete(
            $notes_table,
            $where,
            $where_format
        );
        
        return $result !== false;
    }
    
    /**
     * Get note by ID
     */
    public static function get_note($note_id) {
        global $wpdb;
        
        $notes_table = $wpdb->prefix . self::$notes_table_name;
        
        $note = $wpdb->get_row($wpdb->prepare(
            "SELECT n.*, u.display_name, u.user_email 
             FROM $notes_table n 
             LEFT JOIN {$wpdb->users} u ON n.user_id = u.ID 
             WHERE n.id = %d",
            $note_id
        ));
        
        return $note;
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
        
        // Debug: Log what we're doing
        error_log('Careers Debug: Getting stats from table: ' . $table_name);
        
        $stats = array();
        
        // Total applications
        $stats['total'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        error_log('Careers Debug: Total applications: ' . $stats['total']);
        
        // Applications by status
        $status_counts = $wpdb->get_results(
            "SELECT status, COUNT(*) as count FROM $table_name GROUP BY status"
        );
        
        error_log('Careers Debug: Status counts: ' . print_r($status_counts, true));
        
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
        
        error_log('Careers Debug: Final stats: ' . print_r($stats, true));
        
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

    /**
     * Debug method to test database operations
     */
    public static function debug_test_database() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        error_log('=== Careers Debug Test ===');
        error_log('Table name: ' . $table_name);
        
        // Force table creation
        self::create_table();
        
        // Test a simple insert
        $test_data = array(
            'user_id' => 1,
            'job_id' => 1,
            'resume_url' => 'test.pdf',
            'cover_letter_url' => '',
            'status' => 'new',
            'meta' => 'test application',
            'submitted_at' => current_time('mysql')
        );
        
        error_log('Test data: ' . print_r($test_data, true));
        
        $result = $wpdb->insert(
            $table_name,
            $test_data,
            array('%d', '%d', '%s', '%s', '%s', '%s', '%s')
        );
        
        error_log('Test insert result: ' . ($result === false ? 'FALSE' : $result));
        error_log('Test insert ID: ' . $wpdb->insert_id);
        if ($wpdb->last_error) {
            error_log('Test insert error: ' . $wpdb->last_error);
        }
        
        // Check total count
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        error_log('Total rows in table: ' . $count);
        
        error_log('=== End Debug Test ===');
        
        return $count;
    }
} 