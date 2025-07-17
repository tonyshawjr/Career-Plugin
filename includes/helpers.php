<?php
/**
 * Helper functions for Careers Manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get job modalities for taxonomy
 */
function careers_get_modalities() {
    return array(
        'remote' => __('Remote', 'careers-manager'),
        'hybrid' => __('Hybrid', 'careers-manager'),
        'onsite' => __('On-site', 'careers-manager'),
    );
}

/**
 * Get certifications for taxonomy
 */
function careers_get_certifications() {
    return array(
        'cpr' => __('CPR Certified', 'careers-manager'),
        'first_aid' => __('First Aid', 'careers-manager'),
        'nursing' => __('Nursing License', 'careers-manager'),
        'medical_assistant' => __('Medical Assistant', 'careers-manager'),
        'other' => __('Other', 'careers-manager'),
    );
}

/**
 * Get careers dashboard URL
 * Helper function to get proper URLs for dashboard pages
 */
if (!function_exists('careers_get_dashboard_url')) {
    function careers_get_dashboard_url($path = '', $args = array()) {
        if (class_exists('CareersSettings')) {
            return CareersSettings::get_dashboard_url($path, $args);
        }
        // Fallback to old URL structure
        $url = home_url('/dashboard');
        if (!empty($path)) {
            $url .= '/' . ltrim($path, '/');
        }
        if (!empty($args)) {
            $url = add_query_arg($args, $url);
        }
        return $url;
    }
}

/**
 * Get US states for dropdown
 */
function careers_get_states() {
    return array(
        'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas', 'CA' => 'California',
        'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware', 'FL' => 'Florida', 'GA' => 'Georgia',
        'HI' => 'Hawaii', 'ID' => 'Idaho', 'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa',
        'KS' => 'Kansas', 'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland',
        'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi', 'MO' => 'Missouri',
        'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada', 'NH' => 'New Hampshire', 'NJ' => 'New Jersey',
        'NM' => 'New Mexico', 'NY' => 'New York', 'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio',
        'OK' => 'Oklahoma', 'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina',
        'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah', 'VT' => 'Vermont',
        'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia', 'WI' => 'Wisconsin', 'WY' => 'Wyoming'
    );
}

/**
 * Get employment types
 */
function careers_get_employment_types() {
    return array(
        'full_time' => __('Full Time', 'careers-manager'),
        'part_time' => __('Part Time', 'careers-manager'),
        'contract' => __('Contract', 'careers-manager'),
        'temporary' => __('Temporary', 'careers-manager'),
    );
}

/**
 * Get experience levels
 */
function careers_get_experience_levels() {
    return array(
        'entry' => __('Entry Level', 'careers-manager'),
        'junior' => __('Junior (1-3 years)', 'careers-manager'),
        'mid' => __('Mid Level (3-5 years)', 'careers-manager'),
        'senior' => __('Senior (5+ years)', 'careers-manager'),
        'executive' => __('Executive', 'careers-manager'),
    );
}

/**
 * Get application statuses
 */
function careers_get_application_statuses() {
    return array(
        'new' => __('New', 'careers-manager'),
        'under_review' => __('Under Review', 'careers-manager'),
        'contacted' => __('Contacted', 'careers-manager'),
        'interview' => __('Interview', 'careers-manager'),
        'hired' => __('Hired', 'careers-manager'),
        'rejected' => __('Rejected', 'careers-manager'),
    );
}

/**
 * Format salary range
 */
function careers_format_salary($min, $max) {
    if (empty($min) && empty($max)) {
        return __('Salary not specified', 'careers-manager');
    }
    
    $min_formatted = $min ? '$' . number_format($min) : '';
    $max_formatted = $max ? '$' . number_format($max) : '';
    
    if ($min && $max) {
        return $min_formatted . ' - ' . $max_formatted;
    } elseif ($min) {
        return $min_formatted . '+';
    } else {
        return __('Up to ', 'careers-manager') . $max_formatted;
    }
}

/**
 * Check if user can apply for jobs
 */
function careers_user_can_apply() {
    return is_user_logged_in();
}

/**
 * Get template part
 */
function careers_get_template_part($template, $args = array()) {
    if (!empty($args)) {
        extract($args);
    }
    
    $template_path = CAREERS_PLUGIN_PATH . 'templates/' . $template . '.php';
    
    if (file_exists($template_path)) {
        include $template_path;
    }
}

/**
 * Validate file upload
 */
function careers_validate_file_upload($file) {
    $allowed_types = array('pdf', 'doc', 'docx');
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return new WP_Error('upload_error', __('File upload failed.', 'careers-manager'));
    }
    
    if ($file['size'] > $max_size) {
        return new WP_Error('file_too_large', __('File is too large. Maximum size is 5MB.', 'careers-manager'));
    }
    
    $file_type = wp_check_filetype($file['name']);
    if (!in_array($file_type['ext'], $allowed_types)) {
        return new WP_Error('invalid_file_type', __('Invalid file type. Only PDF, DOC, and DOCX files are allowed.', 'careers-manager'));
    }
    
    return true;
}

/**
 * Secure file upload for resumes
 */
function careers_upload_resume($file, $user_id, $job_id) {
    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }
    
    // Validate file
    $validation = careers_validate_file_upload($file);
    if (is_wp_error($validation)) {
        return $validation;
    }
    
    // Setup upload overrides
    $upload_overrides = array(
        'test_form' => false,
        'unique_filename_callback' => function($dir, $name, $ext) use ($user_id, $job_id) {
            return 'resume_' . $user_id . '_' . $job_id . '_' . time() . $ext;
        }
    );
    
    // Handle the upload
    $uploaded = wp_handle_upload($file, $upload_overrides);
    
    if (isset($uploaded['error'])) {
        return new WP_Error('upload_failed', $uploaded['error']);
    }
    
    return $uploaded;
}

/**
 * Get secure file URL
 */
function careers_get_secure_file_url($file_path, $user_id) {
    // Only allow access to file owner or admin
    if (!current_user_can('manage_options') && get_current_user_id() != $user_id) {
        return false;
    }
    
    return $file_path;
}

/**
 * Get career job permalink
 */
function careers_get_job_permalink($job_id) {
    $post = get_post($job_id);
    if (!$post || $post->post_type !== 'career_job') {
        return false;
    }
    
    // For now, let's use a simple approach that we know works
    // Link to the job detail page using the post ID
    return home_url('/open-positions/?job_id=' . $job_id);
} 