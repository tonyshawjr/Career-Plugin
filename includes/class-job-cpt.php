<?php
/**
 * Career Job Custom Post Type
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CareerJobCPT {
    
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action('init', array($this, 'register_taxonomies'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'));
        add_filter('manage_career_job_posts_columns', array($this, 'admin_columns'));
        add_action('manage_career_job_posts_custom_column', array($this, 'admin_column_content'), 10, 2);
    }
    
    /**
     * Register the career_job post type
     */
    public function register_post_type() {
        $labels = array(
            'name' => __('Career Jobs', 'careers-manager'),
            'singular_name' => __('Career Job', 'careers-manager'),
            'menu_name' => __('Career Jobs', 'careers-manager'),
            'add_new' => __('Add New Job', 'careers-manager'),
            'add_new_item' => __('Add New Career Job', 'careers-manager'),
            'edit_item' => __('Edit Career Job', 'careers-manager'),
            'new_item' => __('New Career Job', 'careers-manager'),
            'view_item' => __('View Career Job', 'careers-manager'),
            'search_items' => __('Search Career Jobs', 'careers-manager'),
            'not_found' => __('No career jobs found', 'careers-manager'),
            'not_found_in_trash' => __('No career jobs found in Trash', 'careers-manager'),
        );
        
        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => false, // We'll add it to our custom admin menu
            'query_var' => true,
            'rewrite' => array(
                'slug' => 'job',
                'with_front' => false
            ),
            'capability_type' => 'post',
            'has_archive' => false, // Disable archive since we're using a page with shortcode
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'editor', 'thumbnail'),
            'show_in_rest' => true,
        );
        
        register_post_type('career_job', $args);
    }
    
    /**
     * Register taxonomies
     */
    public function register_taxonomies() {
        // Modality taxonomy
        register_taxonomy('job_modality', 'career_job', array(
            'hierarchical' => false,
            'labels' => array(
                'name' => __('Job Modalities', 'careers-manager'),
                'singular_name' => __('Job Modality', 'careers-manager'),
            ),
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'job-modality'),
            'show_in_rest' => true,
        ));
        
        // Certification taxonomy
        register_taxonomy('job_certification', 'career_job', array(
            'hierarchical' => false,
            'labels' => array(
                'name' => __('Job Certifications', 'careers-manager'),
                'singular_name' => __('Job Certification', 'careers-manager'),
            ),
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'job-certification'),
            'show_in_rest' => true,
        ));
        
        // State taxonomy
        register_taxonomy('job_state', 'career_job', array(
            'hierarchical' => false,
            'labels' => array(
                'name' => __('Job States', 'careers-manager'),
                'singular_name' => __('Job State', 'careers-manager'),
            ),
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'job-state'),
            'show_in_rest' => true,
        ));
    }
    
    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'career_job_details',
            __('Job Details', 'careers-manager'),
            array($this, 'job_details_meta_box'),
            'career_job',
            'normal',
            'high'
        );
        
        add_meta_box(
            'career_job_additional',
            __('Additional Information', 'careers-manager'),
            array($this, 'job_additional_meta_box'),
            'career_job',
            'normal',
            'default'
        );
    }
    
    /**
     * Job details meta box
     */
    public function job_details_meta_box($post) {
        wp_nonce_field('career_job_meta_box', 'career_job_meta_box_nonce');
        
        $location = get_post_meta($post->ID, '_career_location', true);
        $employment_type = get_post_meta($post->ID, '_career_employment_type', true);
        $salary_min = get_post_meta($post->ID, '_career_salary_min', true);
        $salary_max = get_post_meta($post->ID, '_career_salary_max', true);
        $experience_level = get_post_meta($post->ID, '_career_experience_level', true);
        $benefits = get_post_meta($post->ID, '_career_benefits', true);
        
        // Additional fields to match original React app structure
        $summary = get_post_meta($post->ID, '_career_summary', true);
        $responsibilities = get_post_meta($post->ID, '_career_responsibilities', true);
        $requirements = get_post_meta($post->ID, '_career_requirements', true);
        $equipment = get_post_meta($post->ID, '_career_equipment', true);
        
        ?>
        <table class="form-table">
            <tr>
                <th><label for="career_location"><?php _e('Location (City)', 'careers-manager'); ?></label></th>
                <td><input type="text" id="career_location" name="career_location" value="<?php echo esc_attr($location); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="career_employment_type"><?php _e('Employment Type', 'careers-manager'); ?></label></th>
                <td>
                    <select id="career_employment_type" name="career_employment_type">
                        <option value=""><?php _e('Select Employment Type', 'careers-manager'); ?></option>
                        <?php foreach (careers_get_employment_types() as $key => $label): ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($employment_type, $key); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="career_salary_min"><?php _e('Salary Range', 'careers-manager'); ?></label></th>
                <td>
                    <input type="number" id="career_salary_min" name="career_salary_min" value="<?php echo esc_attr($salary_min); ?>" placeholder="<?php _e('Minimum', 'careers-manager'); ?>" class="small-text" />
                    <span> - </span>
                    <input type="number" id="career_salary_max" name="career_salary_max" value="<?php echo esc_attr($salary_max); ?>" placeholder="<?php _e('Maximum', 'careers-manager'); ?>" class="small-text" />
                </td>
            </tr>
            <tr>
                <th><label for="career_experience_level"><?php _e('Experience Level', 'careers-manager'); ?></label></th>
                <td>
                    <select id="career_experience_level" name="career_experience_level">
                        <option value=""><?php _e('Select Experience Level', 'careers-manager'); ?></option>
                        <?php foreach (careers_get_experience_levels() as $key => $label): ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($experience_level, $key); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="career_summary"><?php _e('Job Summary', 'careers-manager'); ?></label></th>
                <td><textarea id="career_summary" name="career_summary" rows="3" cols="50" class="large-text" placeholder="Brief summary for job cards"><?php echo esc_textarea($summary); ?></textarea></td>
            </tr>
            <tr>
                <th><label for="career_responsibilities"><?php _e('Responsibilities', 'careers-manager'); ?></label></th>
                <td><textarea id="career_responsibilities" name="career_responsibilities" rows="6" cols="50" class="large-text" placeholder="One responsibility per line"><?php echo esc_textarea($responsibilities); ?></textarea></td>
            </tr>
            <tr>
                <th><label for="career_requirements"><?php _e('Requirements', 'careers-manager'); ?></label></th>
                <td><textarea id="career_requirements" name="career_requirements" rows="6" cols="50" class="large-text" placeholder="One requirement per line"><?php echo esc_textarea($requirements); ?></textarea></td>
            </tr>
            <tr>
                <th><label for="career_equipment"><?php _e('Equipment Used', 'careers-manager'); ?></label></th>
                <td><textarea id="career_equipment" name="career_equipment" rows="3" cols="50" class="large-text" placeholder="One piece of equipment per line"><?php echo esc_textarea($equipment); ?></textarea></td>
            </tr>
            <tr>
                <th><label for="career_benefits"><?php _e('Benefits', 'careers-manager'); ?></label></th>
                <td><textarea id="career_benefits" name="career_benefits" rows="4" cols="50" class="large-text" placeholder="One benefit per line"><?php echo esc_textarea($benefits); ?></textarea></td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Additional information meta box
     */
    public function job_additional_meta_box($post) {
        $equipment_vehicle = get_post_meta($post->ID, '_career_equipment_vehicle', true);
        $licensing = get_post_meta($post->ID, '_career_licensing', true);
        $day_in_life = get_post_meta($post->ID, '_career_day_in_life', true);
        
        ?>
        <table class="form-table">
            <tr>
                <th><label for="career_equipment_vehicle"><?php _e('Equipment & Vehicle Info', 'careers-manager'); ?></label></th>
                <td><textarea id="career_equipment_vehicle" name="career_equipment_vehicle" rows="4" cols="50" class="large-text"><?php echo esc_textarea($equipment_vehicle); ?></textarea></td>
            </tr>
            <tr>
                <th><label for="career_licensing"><?php _e('Licensing Info', 'careers-manager'); ?></label></th>
                <td><textarea id="career_licensing" name="career_licensing" rows="4" cols="50" class="large-text"><?php echo esc_textarea($licensing); ?></textarea></td>
            </tr>
            <tr>
                <th><label for="career_day_in_life"><?php _e('A Day in the Life', 'careers-manager'); ?></label></th>
                <td><textarea id="career_day_in_life" name="career_day_in_life" rows="6" cols="50" class="large-text"><?php echo esc_textarea($day_in_life); ?></textarea></td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Save meta box data
     */
    public function save_meta_boxes($post_id) {
        if (!isset($_POST['career_job_meta_box_nonce'])) {
            return;
        }
        
        if (!wp_verify_nonce($_POST['career_job_meta_box_nonce'], 'career_job_meta_box')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        $fields = array(
            'career_location',
            'career_employment_type',
            'career_salary_min',
            'career_salary_max',
            'career_experience_level',
            'career_benefits',
            'career_equipment_vehicle',
            'career_licensing',
            'career_day_in_life',
            'career_summary',
            'career_responsibilities',
            'career_requirements',
            'career_equipment'
        );
        
        // Text areas that need textarea sanitization
        $textarea_fields = array('career_benefits', 'career_equipment_vehicle', 'career_licensing', 'career_day_in_life', 'career_summary', 'career_responsibilities', 'career_requirements', 'career_equipment');
        
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                $value = in_array($field, $textarea_fields) ? 
                    sanitize_textarea_field($_POST[$field]) : 
                    sanitize_text_field($_POST[$field]);
                update_post_meta($post_id, '_' . $field, $value);
            }
        }
    }
    
    /**
     * Add custom columns to admin list
     */
    public function admin_columns($columns) {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['location'] = __('Location', 'careers-manager');
        $new_columns['employment_type'] = __('Employment Type', 'careers-manager');
        $new_columns['salary'] = __('Salary Range', 'careers-manager');
        $new_columns['applications'] = __('Applications', 'careers-manager');
        $new_columns['date'] = $columns['date'];
        
        return $new_columns;
    }
    
    /**
     * Admin column content
     */
    public function admin_column_content($column, $post_id) {
        global $wpdb;
        
        switch ($column) {
            case 'location':
                echo esc_html(get_post_meta($post_id, '_career_location', true));
                break;
                
            case 'employment_type':
                $type = get_post_meta($post_id, '_career_employment_type', true);
                $types = careers_get_employment_types();
                echo isset($types[$type]) ? esc_html($types[$type]) : '';
                break;
                
            case 'salary':
                $min = get_post_meta($post_id, '_career_salary_min', true);
                $max = get_post_meta($post_id, '_career_salary_max', true);
                echo careers_format_salary($min, $max);
                break;
                
            case 'applications':
                $table_name = $wpdb->prefix . 'careers_applications';
                $count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $table_name WHERE job_id = %d",
                    $post_id
                ));
                echo intval($count);
                break;
        }
    }
} 