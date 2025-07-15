<?php
/**
 * Careers Positions Database Handler
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CareersPositionsDB {
    
    private static $table_name = 'careers_positions';
    private static $locations_table = 'careers_locations';
    
    public function __construct() {
        add_action('init', array($this, 'maybe_create_tables'));
    }
    
    /**
     * Create the positions and locations tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $positions_table = $wpdb->prefix . self::$table_name;
        $locations_table = $wpdb->prefix . self::$locations_table;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Create positions table
        $positions_sql = "CREATE TABLE $positions_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            position_name varchar(255) NOT NULL,
            location varchar(255) NOT NULL,
            job_type varchar(100),
            salary_range varchar(255),
            schedule_type varchar(100),
            experience_level varchar(100),
            certification_required varchar(255),
            position_overview text,
            responsibilities text,
            requirements text,
            equipment text,
            benefits text,
            license_info text,
            has_vehicle tinyint(1) DEFAULT 0,
            vehicle_description text,
            status enum('draft','published') DEFAULT 'published',
            created_by bigint(20),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY location (location),
            KEY job_type (job_type),
            KEY status (status),
            KEY created_by (created_by),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // Create locations table
        $locations_sql = "CREATE TABLE $locations_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            state varchar(100) NOT NULL,
            city varchar(100) NOT NULL,
            display_name varchar(255) NOT NULL,
            PRIMARY KEY (id),
            KEY state (state),
            KEY city (city),
            UNIQUE KEY state_city (state, city)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($positions_sql);
        dbDelta($locations_sql);
        
        // Insert default locations
        self::insert_default_locations();
        
        // Store table version
        update_option('careers_positions_table_version', '2.0');
    }
    
    /**
     * Insert default locations
     */
    private static function insert_default_locations() {
        $default_locations = array(
            array('state' => 'Texas', 'city' => 'Dallas'),
            array('state' => 'Texas', 'city' => 'Houston'),
            array('state' => 'Texas', 'city' => 'Austin'),
            array('state' => 'Florida', 'city' => 'Miami'),
            array('state' => 'Florida', 'city' => 'Tampa'),
            array('state' => 'California', 'city' => 'Los Angeles'),
            array('state' => 'California', 'city' => 'San Francisco'),
            array('state' => 'New York', 'city' => 'New York City'),
            array('state' => 'Georgia', 'city' => 'Atlanta'),
            array('state' => 'North Carolina', 'city' => 'Charlotte'),
            array('state' => 'Virginia', 'city' => 'Richmond'),
            array('state' => 'Arizona', 'city' => 'Phoenix'),
            array('state' => 'Nevada', 'city' => 'Las Vegas'),
            array('state' => 'Remote', 'city' => 'Remote')
        );
        
        foreach ($default_locations as $location) {
            self::insert_location($location['state'], $location['city']);
        }
    }
    
    /**
     * Maybe create tables if they don't exist
     */
    public function maybe_create_tables() {
        $installed_version = get_option('careers_positions_table_version');
        if ($installed_version != '2.0') {
            self::create_tables();
        }
    }
    
    /**
     * Insert a new position
     */
    public static function insert_position($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        $defaults = array(
            'position_name' => '',
            'location' => '',
            'job_type' => '',
            'salary_range' => '',
            'schedule_type' => '',
            'experience_level' => '',
            'certification_required' => '',
            'position_overview' => '',
            'responsibilities' => '',
            'requirements' => '',
            'equipment' => '',
            'benefits' => '',
            'license_info' => '',
            'has_vehicle' => 0,
            'vehicle_description' => '',
            'status' => 'published',
            'created_by' => get_current_user_id(),
            'created_at' => current_time('mysql')
        );
        
        $data = wp_parse_args($data, $defaults);
        
        // Validate required fields
        if (empty($data['position_name']) || empty($data['location'])) {
            return new WP_Error('missing_data', __('Position name and location are required.', 'careers-manager'));
        }
        
        // Sanitize data
        $data['position_name'] = sanitize_text_field($data['position_name']);
        $data['location'] = sanitize_text_field($data['location']);
        $data['job_type'] = sanitize_text_field($data['job_type']);
        $data['salary_range'] = sanitize_text_field($data['salary_range']);
        $data['schedule_type'] = sanitize_text_field($data['schedule_type']);
        $data['experience_level'] = sanitize_text_field($data['experience_level']);
        $data['certification_required'] = sanitize_text_field($data['certification_required']);
        $data['position_overview'] = wp_kses_post($data['position_overview']);
        $data['responsibilities'] = sanitize_textarea_field($data['responsibilities']);
        $data['requirements'] = sanitize_textarea_field($data['requirements']);
        $data['equipment'] = sanitize_textarea_field($data['equipment']);
        $data['benefits'] = sanitize_textarea_field($data['benefits']);
        $data['license_info'] = wp_kses_post($data['license_info']);
        $data['has_vehicle'] = (int) $data['has_vehicle'];
        $data['vehicle_description'] = sanitize_textarea_field($data['vehicle_description']);
        $data['status'] = in_array($data['status'], array('draft', 'published')) ? $data['status'] : 'published';
        
        $result = $wpdb->insert(
            $table_name,
            $data,
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%s')
        );
        
        if ($result === false) {
            return new WP_Error('insert_failed', __('Failed to create position.', 'careers-manager'));
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Update a position
     */
    public static function update_position($id, $data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        // Remove id and timestamps from data if present
        unset($data['id'], $data['created_at'], $data['created_by']);
        $data['updated_at'] = current_time('mysql');
        
        // Sanitize data
        if (isset($data['position_name'])) {
            $data['position_name'] = sanitize_text_field($data['position_name']);
        }
        if (isset($data['location'])) {
            $data['location'] = sanitize_text_field($data['location']);
        }
        if (isset($data['job_type'])) {
            $data['job_type'] = sanitize_text_field($data['job_type']);
        }
        if (isset($data['salary_range'])) {
            $data['salary_range'] = sanitize_text_field($data['salary_range']);
        }
        if (isset($data['schedule_type'])) {
            $data['schedule_type'] = sanitize_text_field($data['schedule_type']);
        }
        if (isset($data['experience_level'])) {
            $data['experience_level'] = sanitize_text_field($data['experience_level']);
        }
        if (isset($data['certification_required'])) {
            $data['certification_required'] = sanitize_text_field($data['certification_required']);
        }
        if (isset($data['position_overview'])) {
            $data['position_overview'] = wp_kses_post($data['position_overview']);
        }
        if (isset($data['responsibilities'])) {
            $data['responsibilities'] = sanitize_textarea_field($data['responsibilities']);
        }
        if (isset($data['requirements'])) {
            $data['requirements'] = sanitize_textarea_field($data['requirements']);
        }
        if (isset($data['equipment'])) {
            $data['equipment'] = sanitize_textarea_field($data['equipment']);
        }
        if (isset($data['benefits'])) {
            $data['benefits'] = sanitize_textarea_field($data['benefits']);
        }
        if (isset($data['license_info'])) {
            $data['license_info'] = wp_kses_post($data['license_info']);
        }
        if (isset($data['has_vehicle'])) {
            $data['has_vehicle'] = (int) $data['has_vehicle'];
        }
        if (isset($data['vehicle_description'])) {
            $data['vehicle_description'] = sanitize_textarea_field($data['vehicle_description']);
        }
        if (isset($data['status'])) {
            $data['status'] = in_array($data['status'], array('draft', 'published')) ? $data['status'] : 'published';
        }
        
        $result = $wpdb->update(
            $table_name,
            $data,
            array('id' => $id),
            null, // Let WordPress determine format based on data types
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('update_failed', __('Failed to update position.', 'careers-manager'));
        }
        
        return true;
    }
    
    /**
     * Get position by ID
     */
    public static function get_position($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $id
        ));
    }
    
    
    /**
     * Delete position
     */
    public static function delete_position($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        $result = $wpdb->delete(
            $table_name,
            array('id' => $id),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Get all locations
     */
    public static function get_locations() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$locations_table;
        
        return $wpdb->get_results("SELECT * FROM $table_name ORDER BY state ASC, city ASC");
    }
    
    /**
     * Insert location
     */
    public static function insert_location($state, $city) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$locations_table;
        
        if (empty($state) || empty($city)) {
            return new WP_Error('missing_data', __('State and city are required.', 'careers-manager'));
        }
        
        $state = sanitize_text_field($state);
        $city = sanitize_text_field($city);
        $display_name = $city . ', ' . $state;
        
        // Check if location already exists
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_name WHERE state = %s AND city = %s",
            $state, $city
        ));
        
        if ($existing) {
            return $existing; // Return existing ID
        }
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'state' => $state,
                'city' => $city,
                'display_name' => $display_name
            ),
            array('%s', '%s', '%s')
        );
        
        if ($result === false) {
            // Check if it's a duplicate key error
            if (strpos($wpdb->last_error, 'Duplicate entry') !== false) {
                return new WP_Error('duplicate_location', __('Location already exists.', 'careers-manager'));
            }
            return new WP_Error('insert_failed', __('Failed to add location.', 'careers-manager'));
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Get locations grouped by state
     */
    public static function get_locations_by_state() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$locations_table;
        
        $locations = $wpdb->get_results("SELECT * FROM $table_name ORDER BY state ASC, city ASC");
        
        $grouped = array();
        foreach ($locations as $location) {
            $grouped[$location->state][] = $location;
        }
        
        return $grouped;
    }
    
    /**
     * Delete location
     */
    public static function delete_location($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$locations_table;
        
        $result = $wpdb->delete(
            $table_name,
            array('id' => $id),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Get position statistics
     */
    public static function get_stats() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        $stats = array();
        
        // Total positions
        $stats['total'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        
        // Published positions
        $stats['published'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE status = %s",
            'published'
        ));
        
        // Draft positions
        $stats['draft'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE status = %s",
            'draft'
        ));
        
        // Positions by location
        $location_counts = $wpdb->get_results(
            "SELECT location, COUNT(*) as count FROM $table_name WHERE status = 'published' GROUP BY location ORDER BY count DESC"
        );
        
        $stats['by_location'] = $location_counts;
        
        // Recent positions (last 30 days)
        $stats['recent'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM $table_name WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
        );
        
        return $stats;
    }
    
    /**
     * Format list fields for display
     */
    public static function format_list_field($field_content) {
        if (empty($field_content)) {
            return '';
        }
        
        $items = array_filter(array_map('trim', explode("\n", $field_content)));
        
        if (empty($items)) {
            return '';
        }
        
        $html = '<ul>';
        foreach ($items as $item) {
            $html .= '<li>' . esc_html($item) . '</li>';
        }
        $html .= '</ul>';
        
        return $html;
    }
    
    /**
     * Get positions count with filtering support
     */
    public static function get_positions_count($args = array()) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        $defaults = array(
            'status' => '',
            'search' => '',
            'job_type' => '',
            'location' => ''
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where_conditions = array();
        $where_values = array();
        
        // Status filter
        if (!empty($args['status'])) {
            $where_conditions[] = "status = %s";
            $where_values[] = $args['status'];
        }
        
        // Search filter - search across multiple fields
        if (!empty($args['search'])) {
            $where_conditions[] = "(position_name LIKE %s OR position_overview LIKE %s OR responsibilities LIKE %s OR requirements LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }
        
        // Job type filter
        if (!empty($args['job_type'])) {
            $where_conditions[] = "job_type = %s";
            $where_values[] = $args['job_type'];
        }
        
        // Location filter
        if (!empty($args['location'])) {
            $where_conditions[] = "location = %s";
            $where_values[] = $args['location'];
        }
        
        $where_clause = '';
        if (!empty($where_conditions)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        }
        
        $query = "SELECT COUNT(*) FROM $table_name $where_clause";
        
        if (!empty($where_values)) {
            $query = $wpdb->prepare($query, $where_values);
        }
        
        return intval($wpdb->get_var($query));
    }
    
    /**
     * Enhanced get_positions method with search and filtering support
     */
    public static function get_positions($args = array()) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        $defaults = array(
            'status' => '',
            'search' => '',
            'job_type' => '',
            'location' => '',
            'limit' => 50,
            'offset' => 0,
            'orderby' => 'created_at',
            'order' => 'DESC'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where_conditions = array();
        $where_values = array();
        
        // Status filter
        if (!empty($args['status'])) {
            $where_conditions[] = "status = %s";
            $where_values[] = $args['status'];
        }
        
        // Search filter - search across multiple fields
        if (!empty($args['search'])) {
            $where_conditions[] = "(position_name LIKE %s OR position_overview LIKE %s OR responsibilities LIKE %s OR requirements LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }
        
        // Job type filter
        if (!empty($args['job_type'])) {
            $where_conditions[] = "job_type = %s";
            $where_values[] = $args['job_type'];
        }
        
        // Location filter
        if (!empty($args['location'])) {
            $where_conditions[] = "location = %s";
            $where_values[] = $args['location'];
        }
        
        $where_clause = '';
        if (!empty($where_conditions)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        }
        
        // Sanitize orderby and order
        $allowed_orderby = array('id', 'position_name', 'location', 'status', 'created_at');
        $orderby = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'created_at';
        $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';
        
        $query = "SELECT * FROM $table_name $where_clause ORDER BY $orderby $order LIMIT %d OFFSET %d";
        
        $where_values[] = $args['limit'];
        $where_values[] = $args['offset'];
        
        $query = $wpdb->prepare($query, $where_values);
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Bulk update positions status
     */
    public static function bulk_update_status($position_ids, $status) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        if (empty($position_ids) || !is_array($position_ids)) {
            return false;
        }
        
        $valid_statuses = array('published', 'draft');
        if (!in_array($status, $valid_statuses)) {
            return false;
        }
        
        $placeholders = implode(',', array_fill(0, count($position_ids), '%d'));
        
        $query = $wpdb->prepare(
            "UPDATE $table_name SET status = %s WHERE id IN ($placeholders)",
            array_merge(array($status), $position_ids)
        );
        
        return $wpdb->query($query);
    }
    
    /**
     * Bulk delete positions
     */
    public static function bulk_delete_positions($position_ids) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::$table_name;
        
        if (empty($position_ids) || !is_array($position_ids)) {
            return false;
        }
        
        $placeholders = implode(',', array_fill(0, count($position_ids), '%d'));
        
        $query = $wpdb->prepare(
            "DELETE FROM $table_name WHERE id IN ($placeholders)",
            $position_ids
        );
        
        return $wpdb->query($query);
    }
}