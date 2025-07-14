<?php
/**
 * Careers Login/Logout Elementor Widget
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Careers_Login_Logout_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'careers_login_logout';
    }

    public function get_title() {
        return esc_html__('Careers Login/Logout', 'careers-manager');
    }

    public function get_icon() {
        return 'eicon-lock-user';
    }

    public function get_categories() {
        return ['careers'];
    }

    public function get_keywords() {
        return ['login', 'logout', 'user', 'careers', 'auth'];
    }

    protected function register_controls() {
        
        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__('Content', 'careers-manager'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'login_text',
            [
                'label' => esc_html__('Login Button Text', 'careers-manager'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Sign In', 'careers-manager'),
                'placeholder' => esc_html__('Enter login text', 'careers-manager'),
            ]
        );

        $this->add_control(
            'logout_text',
            [
                'label' => esc_html__('Logout Button Text', 'careers-manager'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Logout', 'careers-manager'),
                'placeholder' => esc_html__('Enter logout text', 'careers-manager'),
            ]
        );

        $this->add_control(
            'dashboard_text',
            [
                'label' => esc_html__('Dashboard Link Text', 'careers-manager'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Dashboard', 'careers-manager'),
                'placeholder' => esc_html__('Enter dashboard text', 'careers-manager'),
            ]
        );

        $this->add_control(
            'show_dashboard_link',
            [
                'label' => esc_html__('Show Dashboard Link', 'careers-manager'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'careers-manager'),
                'label_off' => esc_html__('Hide', 'careers-manager'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'redirect_after_login',
            [
                'label' => esc_html__('Redirect After Login', 'careers-manager'),
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => esc_html__('https://your-site.com/dashboard', 'careers-manager'),
                'default' => [
                    'url' => home_url('/dashboard/'),
                ],
            ]
        );

        $this->add_control(
            'redirect_after_logout',
            [
                'label' => esc_html__('Redirect After Logout', 'careers-manager'),
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => esc_html__('https://your-site.com', 'careers-manager'),
                'default' => [
                    'url' => home_url('/'),
                ],
            ]
        );

        $this->end_controls_section();

        // Button Style Section
        $this->start_controls_section(
            'button_style_section',
            [
                'label' => esc_html__('Button Style', 'careers-manager'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'selector' => '{{WRAPPER}} .careers-auth-button',
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label' => esc_html__('Text Color', 'careers-manager'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .careers-auth-button' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_background_color',
            [
                'label' => esc_html__('Background Color', 'careers-manager'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .careers-auth-button' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'button_border',
                'selector' => '{{WRAPPER}} .careers-auth-button',
            ]
        );

        $this->add_control(
            'button_border_radius',
            [
                'label' => esc_html__('Border Radius', 'careers-manager'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .careers-auth-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_padding',
            [
                'label' => esc_html__('Padding', 'careers-manager'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .careers-auth-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Dashboard Link Style Section
        $this->start_controls_section(
            'dashboard_style_section',
            [
                'label' => esc_html__('Dashboard Link Style', 'careers-manager'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_dashboard_link' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'dashboard_typography',
                'selector' => '{{WRAPPER}} .careers-dashboard-link',
            ]
        );

        $this->add_control(
            'dashboard_text_color',
            [
                'label' => esc_html__('Text Color', 'careers-manager'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .careers-dashboard-link' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'dashboard_hover_color',
            [
                'label' => esc_html__('Hover Color', 'careers-manager'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .careers-dashboard-link:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'dashboard_margin',
            [
                'label' => esc_html__('Margin', 'careers-manager'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .careers-dashboard-link' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Layout Section
        $this->start_controls_section(
            'layout_section',
            [
                'label' => esc_html__('Layout', 'careers-manager'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'layout_direction',
            [
                'label' => esc_html__('Direction', 'careers-manager'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'row' => [
                        'title' => esc_html__('Row', 'careers-manager'),
                        'icon' => 'eicon-arrow-right',
                    ],
                    'column' => [
                        'title' => esc_html__('Column', 'careers-manager'),
                        'icon' => 'eicon-arrow-down',
                    ],
                ],
                'default' => 'row',
                'selectors' => [
                    '{{WRAPPER}} .careers-header-nav' => 'flex-direction: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'layout_gap',
            [
                'label' => esc_html__('Gap', 'careers-manager'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 16,
                ],
                'selectors' => [
                    '{{WRAPPER}} .careers-header-nav' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'layout_align',
            [
                'label' => esc_html__('Alignment', 'careers-manager'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'flex-start' => [
                        'title' => esc_html__('Left', 'careers-manager'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'careers-manager'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'flex-end' => [
                        'title' => esc_html__('Right', 'careers-manager'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'flex-start',
                'selectors' => [
                    '{{WRAPPER}} .careers-header-nav' => 'justify-content: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $login_redirect = !empty($settings['redirect_after_login']['url']) ? $settings['redirect_after_login']['url'] : home_url('/dashboard/');
        $logout_redirect = !empty($settings['redirect_after_logout']['url']) ? $settings['redirect_after_logout']['url'] : home_url('/');
        
        ?>
        <div class="careers-header-nav">
            <?php if (is_user_logged_in()): ?>
                <?php $current_user = wp_get_current_user(); ?>
                
                <?php if ($settings['show_dashboard_link'] === 'yes'): ?>
                    <?php
                    $dashboard_text = $settings['dashboard_text'];
                    if (in_array('career_admin', $current_user->roles)) {
                        $dashboard_text = 'Admin ' . $dashboard_text;
                    } elseif (in_array('applicant', $current_user->roles)) {
                        $dashboard_text = 'My ' . $dashboard_text;
                    }
                    ?>
                    <a href="<?php echo esc_url($login_redirect); ?>" class="careers-dashboard-link">
                        <?php echo esc_html($dashboard_text); ?>
                    </a>
                <?php endif; ?>
                
                <a href="<?php echo esc_url(wp_logout_url($logout_redirect)); ?>" class="careers-auth-button careers-logout-btn">
                    <?php echo esc_html($settings['logout_text']); ?>
                </a>
                
            <?php else: ?>
                
                <a href="<?php echo esc_url(wp_login_url($login_redirect)); ?>" class="careers-auth-button careers-login-btn">
                    <?php echo esc_html($settings['login_text']); ?>
                </a>
                
            <?php endif; ?>
        </div>
        <?php
    }

    protected function content_template() {
        ?>
        <#
        var loginRedirect = settings.redirect_after_login.url || '<?php echo home_url('/dashboard/'); ?>';
        var logoutRedirect = settings.redirect_after_logout.url || '<?php echo home_url('/'); ?>';
        #>
        <div class="careers-header-nav">
            <# if (settings.show_dashboard_link === 'yes') { #>
                <a href="{{ loginRedirect }}" class="careers-dashboard-link">
                    {{{ settings.dashboard_text }}}
                </a>
            <# } #>
            <a href="#" class="careers-auth-button">
                {{{ settings.login_text }}}
            </a>
        </div>
        <?php
    }
}