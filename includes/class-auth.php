<?php
/**
 * Careers Authentication Handler
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CareersAuth {
    
    public function __construct() {
        add_action('wp_ajax_careers_login', array($this, 'handle_login'));
        add_action('wp_ajax_nopriv_careers_login', array($this, 'handle_login'));
        add_action('wp_ajax_careers_register', array($this, 'handle_register'));
        add_action('wp_ajax_nopriv_careers_register', array($this, 'handle_register'));
        add_action('init', array($this, 'handle_logout'));
    }
    
    /**
     * Handle user login via AJAX
     */
    public function handle_login() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'careers_nonce')) {
            wp_die(__('Security check failed.', 'careers-manager'));
        }
        
        $username = sanitize_text_field($_POST['username']);
        $password = $_POST['password'];
        $remember = isset($_POST['remember']) ? true : false;
        
        if (empty($username) || empty($password)) {
            wp_send_json_error(__('Please fill in all fields.', 'careers-manager'));
        }
        
        $credentials = array(
            'user_login' => $username,
            'user_password' => $password,
            'remember' => $remember
        );
        
        $user = wp_signon($credentials, false);
        
        if (is_wp_error($user)) {
            wp_send_json_error($user->get_error_message());
        }
        
        wp_send_json_success(array(
            'message' => __('Login successful! Redirecting...', 'careers-manager'),
            'redirect_url' => $this->get_redirect_url()
        ));
    }
    
    /**
     * Handle user registration via AJAX
     */
    public function handle_register() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'careers_nonce')) {
            wp_die(__('Security check failed.', 'careers-manager'));
        }
        
        // Check if registration is enabled
        if (!get_option('users_can_register')) {
            wp_send_json_error(__('User registration is disabled.', 'careers-manager'));
        }
        
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validation
        if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
            wp_send_json_error(__('Please fill in all fields.', 'careers-manager'));
        }
        
        if (!is_email($email)) {
            wp_send_json_error(__('Please enter a valid email address.', 'careers-manager'));
        }
        
        if ($password !== $confirm_password) {
            wp_send_json_error(__('Passwords do not match.', 'careers-manager'));
        }
        
        if (strlen($password) < 6) {
            wp_send_json_error(__('Password must be at least 6 characters long.', 'careers-manager'));
        }
        
        if (email_exists($email)) {
            wp_send_json_error(__('An account with this email already exists.', 'careers-manager'));
        }
        
        // Create username from email
        $username = $this->generate_username($email);
        
        if (username_exists($username)) {
            wp_send_json_error(__('Username already exists. Please try again.', 'careers-manager'));
        }
        
        // Create user
        $user_id = wp_create_user($username, $password, $email);
        
        if (is_wp_error($user_id)) {
            wp_send_json_error($user_id->get_error_message());
        }
        
        // Update user meta
        wp_update_user(array(
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'display_name' => $first_name . ' ' . $last_name,
            'role' => 'subscriber'
        ));
        
        // Auto-login the user
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);
        
        // Send welcome email
        $this->send_welcome_email($user_id);
        
        wp_send_json_success(array(
            'message' => __('Registration successful! Welcome!', 'careers-manager'),
            'redirect_url' => $this->get_redirect_url()
        ));
    }
    
    /**
     * Handle logout
     */
    public function handle_logout() {
        if (isset($_GET['careers_logout']) && wp_verify_nonce($_GET['_wpnonce'], 'careers_logout')) {
            wp_logout();
            wp_safe_redirect(remove_query_arg(array('careers_logout', '_wpnonce')));
            exit;
        }
    }
    
    /**
     * Generate unique username from email
     */
    private function generate_username($email) {
        $username = sanitize_user(substr($email, 0, strpos($email, '@')));
        
        // If username exists, append numbers
        $base_username = $username;
        $counter = 1;
        
        while (username_exists($username)) {
            $username = $base_username . $counter;
            $counter++;
        }
        
        return $username;
    }
    
    /**
     * Get redirect URL after login/registration
     */
    private function get_redirect_url() {
        // Check if there's a redirect parameter
        if (!empty($_POST['redirect_to'])) {
            return esc_url_raw($_POST['redirect_to']);
        }
        
        // Default to current page or home
        $redirect_url = wp_get_referer();
        if (!$redirect_url) {
            $redirect_url = home_url();
        }
        
        return $redirect_url;
    }
    
    /**
     * Send welcome email to new users
     */
    private function send_welcome_email($user_id) {
        $user = get_user_by('id', $user_id);
        
        if (!$user) {
            return;
        }
        
        $subject = sprintf(__('Welcome to %s', 'careers-manager'), get_bloginfo('name'));
        
        $message = sprintf(
            __('Hi %s,

Welcome to %s! Your account has been created successfully.

You can now apply for jobs and track your applications through your dashboard.

Login details:
Email: %s
You can login at: %s

Best regards,
%s Team', 'careers-manager'),
            $user->first_name,
            get_bloginfo('name'),
            $user->user_email,
            wp_login_url(),
            get_bloginfo('name')
        );
        
        wp_mail($user->user_email, $subject, $message);
    }
    
    /**
     * Check if user is logged in and redirect if not
     */
    public static function require_login($redirect_url = '') {
        if (!is_user_logged_in()) {
            if (empty($redirect_url)) {
                $redirect_url = home_url('/auth/');
            }
            
            wp_safe_redirect($redirect_url);
            exit;
        }
    }
    
    /**
     * Get login form HTML
     */
    public static function get_login_form($args = array()) {
        $defaults = array(
            'redirect' => '',
            'form_id' => 'careers-login-form',
            'label_username' => __('Email or Username', 'careers-manager'),
            'label_password' => __('Password', 'careers-manager'),
            'label_remember' => __('Remember Me', 'careers-manager'),
            'label_log_in' => __('Log In', 'careers-manager'),
            'remember' => true,
            'value_username' => '',
            'value_remember' => false,
        );
        
        $args = wp_parse_args($args, $defaults);
        
        ob_start();
        ?>
        <form id="<?php echo esc_attr($args['form_id']); ?>" class="careers-login-form" method="post">
            <?php wp_nonce_field('careers_nonce', 'nonce'); ?>
            
            <div class="careers-form-group">
                <label for="careers_username"><?php echo esc_html($args['label_username']); ?></label>
                <input type="text" id="careers_username" name="username" value="<?php echo esc_attr($args['value_username']); ?>" required />
            </div>
            
            <div class="careers-form-group">
                <label for="careers_password"><?php echo esc_html($args['label_password']); ?></label>
                <input type="password" id="careers_password" name="password" required />
            </div>
            
            <?php if ($args['remember']): ?>
            <div class="careers-form-group careers-checkbox">
                <label>
                    <input type="checkbox" name="remember" value="1" <?php checked($args['value_remember']); ?> />
                    <?php echo esc_html($args['label_remember']); ?>
                </label>
            </div>
            <?php endif; ?>
            
            <div class="careers-form-group">
                <input type="submit" value="<?php echo esc_attr($args['label_log_in']); ?>" class="careers-submit-btn" />
            </div>
            
            <?php if (!empty($args['redirect'])): ?>
            <input type="hidden" name="redirect_to" value="<?php echo esc_url($args['redirect']); ?>" />
            <?php endif; ?>
        </form>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Get registration form HTML
     */
    public static function get_register_form($args = array()) {
        $defaults = array(
            'redirect' => '',
            'form_id' => 'careers-register-form',
        );
        
        $args = wp_parse_args($args, $defaults);
        
        ob_start();
        ?>
        <form id="<?php echo esc_attr($args['form_id']); ?>" class="careers-register-form" method="post">
            <?php wp_nonce_field('careers_nonce', 'nonce'); ?>
            
            <div class="careers-form-row">
                <div class="careers-form-group careers-half">
                    <label for="careers_first_name"><?php _e('First Name', 'careers-manager'); ?></label>
                    <input type="text" id="careers_first_name" name="first_name" required />
                </div>
                
                <div class="careers-form-group careers-half">
                    <label for="careers_last_name"><?php _e('Last Name', 'careers-manager'); ?></label>
                    <input type="text" id="careers_last_name" name="last_name" required />
                </div>
            </div>
            
            <div class="careers-form-group">
                <label for="careers_email"><?php _e('Email Address', 'careers-manager'); ?></label>
                <input type="email" id="careers_email" name="email" required />
            </div>
            
            <div class="careers-form-row">
                <div class="careers-form-group careers-half">
                    <label for="careers_password"><?php _e('Password', 'careers-manager'); ?></label>
                    <input type="password" id="careers_password" name="password" required />
                </div>
                
                <div class="careers-form-group careers-half">
                    <label for="careers_confirm_password"><?php _e('Confirm Password', 'careers-manager'); ?></label>
                    <input type="password" id="careers_confirm_password" name="confirm_password" required />
                </div>
            </div>
            
            <div class="careers-form-group">
                <input type="submit" value="<?php _e('Create Account', 'careers-manager'); ?>" class="careers-submit-btn" />
            </div>
            
            <?php if (!empty($args['redirect'])): ?>
            <input type="hidden" name="redirect_to" value="<?php echo esc_url($args['redirect']); ?>" />
            <?php endif; ?>
        </form>
        <?php
        return ob_get_clean();
    }
} 