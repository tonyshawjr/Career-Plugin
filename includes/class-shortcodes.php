<?php
/**
 * Careers Shortcodes Handler - New Custom Table Version
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CareersShortcodes {
    
    public function __construct() {
        add_shortcode('careers_list', array($this, 'careers_list_shortcode'));
        add_shortcode('careers_position_detail', array($this, 'careers_position_detail_shortcode'));
        add_shortcode('careers_form', array($this, 'careers_form_shortcode'));
        
        // Handle application submission
        add_action('wp_ajax_careers_submit_application', array($this, 'handle_application_submission'));
        add_action('wp_ajax_nopriv_careers_submit_application', array($this, 'handle_application_submission'));
    }
    
    /**
     * Careers list shortcode [careers_list]
     */
    public function careers_list_shortcode($atts) {
        $atts = shortcode_atts(array(
            'location' => '',
            'modality' => '',
            'certification' => '',
            'limit' => 20,
            'show_filter' => 'true'
        ), $atts);
        
        // Get filters from URL if present
        $location_filter = !empty($_GET['location']) ? sanitize_text_field($_GET['location']) : $atts['location'];
        $modality_filter = !empty($_GET['modality']) ? sanitize_text_field($_GET['modality']) : $atts['modality'];
        $certification_filter = !empty($_GET['certification']) ? sanitize_text_field($_GET['certification']) : $atts['certification'];
        
        // Get positions
        $args = array(
            'status' => 'published',
            'limit' => intval($atts['limit'])
        );
        
        if (!empty($location_filter)) {
            $args['location'] = $location_filter;
        }
        
        if (!empty($modality_filter)) {
            $args['job_type'] = $modality_filter;
        }
        
        if (!empty($certification_filter)) {
            $args['search'] = $certification_filter; // Search for certification in job content
        }
        
        $positions = CareersPositionsDB::get_positions($args);
        
        // Get filter options
        $all_locations = CareersPositionsDB::get_locations();
        $locations_by_state = array();
        foreach ($all_locations as $location) {
            $locations_by_state[$location->state][] = $location;
        }
        ksort($locations_by_state);
        foreach ($locations_by_state as $state => $cities) {
            usort($locations_by_state[$state], function($a, $b) {
                return strcmp($a->city, $b->city);
            });
        }
        
        $modalities = array('Full-Time', 'Part-Time', 'Contract', 'Per-Diem', 'Travel');
        $certifications = array('ARRT', 'ARDMS', 'X-Ray', 'Ultrasound', 'MRI', 'CT', 'Mammography');
        
        ob_start();
        ?>
        <div class="open-positions-page">
            <div class="page-header">
                <h1>Open Positions</h1>
                <p>Browse our current opportunities at National Mobile X-Ray. Filter by modality, location, and certification to find the perfect fit for your career.</p>
            </div>
            
            <div class="positions-layout">
                <?php if ($atts['show_filter'] === 'true'): ?>
                    <div class="filters-sidebar">
                        <div class="filters-header">
                            <h3>Filters</h3>
                            <a href="<?php echo esc_url(CareersSettings::get_page_url('open_positions')); ?>" class="clear-filters">Clear all filters</a>
                        </div>
                        
                        <form method="get" action="" class="filters-form">
                            <div class="filter-group">
                                <h4>Modality</h4>
                                <select name="modality" onchange="this.form.submit()">
                                    <option value="">All Modalities</option>
                                    <?php foreach ($modalities as $modality): ?>
                                        <option value="<?php echo esc_attr($modality); ?>" <?php selected($modality_filter, $modality); ?>>
                                            <?php echo esc_html($modality); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <h4>Location</h4>
                                <select name="location" onchange="this.form.submit()">
                                    <option value="">All Locations</option>
                                    <?php foreach ($locations_by_state as $state => $cities): ?>
                                        <optgroup label="<?php echo esc_attr($state); ?>">
                                            <?php foreach ($cities as $location): ?>
                                                <option value="<?php echo esc_attr($location->city . ', ' . $location->state); ?>" <?php selected($location_filter, $location->city . ', ' . $location->state); ?>>
                                                    <?php echo esc_html($location->city); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <h4>Certification</h4>
                                <select name="certification" onchange="this.form.submit()">
                                    <option value="">All Certifications</option>
                                    <?php foreach ($certifications as $cert): ?>
                                        <option value="<?php echo esc_attr($cert); ?>" <?php selected($certification_filter, $cert); ?>>
                                            <?php echo esc_html($cert); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
                
                <div class="positions-content">
                    <div class="positions-grid">
                        <?php if (empty($positions)): ?>
                            <div class="no-positions">
                                <h3>No positions available</h3>
                                <p>Try adjusting your filters or check back later for new opportunities.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($positions as $position): ?>
                                <div class="position-card">
                                    <div class="position-header">
                                        <h3 class="position-title">
                                            <a href="<?php echo esc_url(careers_get_job_permalink($position->id)); ?>">
                                                <?php echo esc_html($position->position_name); ?>
                                            </a>
                                        </h3>
                                        <div class="position-badges">
                                            <?php if (!empty($position->job_type)): ?>
                                                <span class="badge badge-<?php echo esc_attr(strtolower(str_replace([' ', '-'], '-', $position->job_type))); ?>">
                                                    <?php echo esc_html($position->job_type); ?>
                                                </span>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($position->certification_required)): ?>
                                                <?php 
                                                $certs = explode(',', $position->certification_required);
                                                foreach ($certs as $cert): 
                                                    $cert = trim($cert);
                                                    if (!empty($cert)):
                                                ?>
                                                    <span class="badge badge-cert"><?php echo esc_html($cert); ?></span>
                                                <?php 
                                                    endif;
                                                endforeach; 
                                                ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="position-location">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 2C8.13 2 5 5.13 5 9C5 14.25 12 22 12 22S19 14.25 19 9C19 5.13 15.87 2 12 2ZM12 11.5C10.62 11.5 9.5 10.38 9.5 9S10.62 6.5 12 6.5 14.5 7.62 14.5 9 13.38 11.5 12 11.5Z" fill="#666"/>
                                        </svg>
                                        <?php echo esc_html($position->location); ?>
                                    </div>
                                    
                                    <?php if (!empty($position->position_overview)): ?>
                                        <div class="position-description">
                                            <?php 
                                            $excerpt = wp_trim_words($position->position_overview, 20, '...');
                                            echo wp_kses_post($excerpt);
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="position-meta">
                                        <div class="position-date">
                                            Posted: <?php echo esc_html(date('M j, Y', strtotime($position->created_at))); ?>
                                        </div>
                                    </div>
                                    
                                    <div class="position-actions">
                                        <a href="<?php echo esc_url(careers_get_job_permalink($position->id)); ?>" 
                                           class="view-details-btn">View Details</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .open-positions-page {
            max-width: 1280px;
            margin: 0 auto;
            padding: 2rem 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #333;
        }
        
        .page-header {
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #eee;
        }
        
        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 500;
            margin: 0 0 0.5rem 0;
            line-height: 1.2;
            color: #111;
        }
        
        .page-header p {
            color: #666;
            margin: 0;
            font-size: 1rem;
            line-height: 1.6;
        }
        
        .positions-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 2rem;
        }
        
        .filters-sidebar {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 4px;
            padding: 1.5rem;
            height: fit-content;
            position: sticky;
            top: 2rem;
        }
        
        .filters-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        
        .filters-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 500;
            color: #111;
        }
        
        .clear-filters {
            color: #666;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .clear-filters:hover {
            color: #333;
        }
        
        .filter-group {
            margin-bottom: 1.5rem;
        }
        
        .filter-group h4 {
            margin: 0 0 0.5rem 0;
            font-size: 0.875rem;
            font-weight: 500;
            color: #111;
        }
        
        .filter-group select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            color: #333;
            background: #fff;
            transition: border-color 0.2s ease;
        }
        
        .filter-group select:focus {
            outline: none;
            border-color: #333;
            box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.05);
        }
        
        .positions-content {
            min-width: 0;
        }
        
        .positions-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        
        .position-card {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 4px;
            padding: 1.5rem;
            transition: all 0.2s ease;
        }
        
        .position-header {
            margin-bottom: 1rem;
        }
        
        .position-title {
            margin: 0 0 0.75rem 0;
            font-size: 1.125rem;
            font-weight: 500;
            line-height: 1.3;
            color: #111;
        }
        
        .position-title a {
            color: #111;
            text-decoration: none;
        }
        
        .position-title a:hover {
            color: #333;
        }
        
        .position-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-radius: 12px;
            white-space: nowrap;
        }
        
        .badge-full-time {
            background: #dbeafe;
            color: #1d4ed8;
        }
        
        .badge-part-time {
            background: #fef3c7;
            color: #d97706;
        }
        
        .badge-contract {
            background: #f3e8ff;
            color: #7c3aed;
        }
        
        .badge-per-diem {
            background: #ecfdf5;
            color: #059669;
        }
        
        .badge-travel {
            background: #fce7f3;
            color: #be185d;
        }
        
        .badge-cert {
            background: #fff9c4;
            color: #f9a825;
        }
        
        .position-location {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: #666;
        }
        
        .position-description {
            margin-bottom: 1rem;
            line-height: 1.6;
            color: #555;
        }
        
        .position-meta {
            margin-bottom: 1rem;
        }
        
        .position-date {
            font-size: 0.875rem;
            color: #666;
        }
        
        .view-details-btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: #fff;
            color: #333;
            text-decoration: none;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .view-details-btn:hover {
            background: #f5f5f5;
            color: #333;
        }
        
        .no-positions {
            grid-column: 1 / -1;
            text-align: center;
            padding: 4rem 2rem;
            color: #666;
        }
        
        .no-positions h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1.25rem;
            font-weight: 500;
            color: #111;
        }
        
        .no-positions p {
            margin: 0;
        }
        
        @media (max-width: 1024px) {
            .positions-layout {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .filters-sidebar {
                position: static;
                margin-bottom: 1rem;
            }
            
            .positions-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .open-positions-page {
                padding: 1rem;
            }
            
            .page-header h1 {
                font-size: 2rem;
            }
            
            .positions-grid {
                grid-template-columns: 1fr;
            }
        }
        </style>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Position detail shortcode [careers_position_detail]
     */
    public function careers_position_detail_shortcode($atts) {
        // Get position ID from URL or shortcode attribute
        $position_id = get_query_var('careers_position_id');
        
        if (empty($position_id) && isset($atts['id'])) {
            $position_id = intval($atts['id']);
        }
        
        if (empty($position_id)) {
            return '<p>Position not found.</p>';
        }
        
        $position = CareersPositionsDB::get_position($position_id);
        
        if (!$position || $position->status !== 'published') {
            return '<p>Position not found or no longer available.</p>';
        }
        
        ob_start();
        ?>
        <div class="position-detail-page">
            <!-- Header Section -->
            <div class="position-hero">
                <div class="hero-content">
                    <h1 class="position-title"><?php echo esc_html($position->position_name); ?></h1>
                    <div class="position-location">
                        📍 <?php echo esc_html($position->location); ?>
                    </div>
                </div>
                <div class="hero-actions">
                    <a href="<?php echo home_url('/apply/' . $position_id); ?>" class="apply-btn">Apply Now</a>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="position-content-grid">
                <!-- Left Column -->
                <div class="position-main-content">
                    
                    <?php if (!empty($position->position_overview)): ?>
                    <section class="content-section">
                        <h2>Position Overview</h2>
                        <div class="content-text">
                            <?php echo wp_kses_post(wpautop($position->position_overview)); ?>
                        </div>
                    </section>
                    <?php endif; ?>
                    
                    <?php if (!empty($position->responsibilities)): ?>
                    <section class="content-section">
                        <h2>Responsibilities</h2>
                        <div class="content-text">
                            <?php echo CareersPositionsDB::format_list_field($position->responsibilities); ?>
                        </div>
                    </section>
                    <?php endif; ?>
                    
                    <?php if (!empty($position->requirements)): ?>
                    <section class="content-section">
                        <h2>Requirements</h2>
                        <div class="content-text">
                            <?php echo CareersPositionsDB::format_list_field($position->requirements); ?>
                        </div>
                    </section>
                    <?php endif; ?>
                    
                    <?php if (!empty($position->equipment)): ?>
                    <section class="content-section">
                        <h2>Equipment Used</h2>
                        <div class="content-text">
                            <?php echo CareersPositionsDB::format_list_field($position->equipment); ?>
                        </div>
                    </section>
                    <?php endif; ?>
                    
                    <?php if (!empty($position->license_info)): ?>
                    <section class="content-section highlighted-section">
                        <h2>State Licensing Information</h2>
                        <div class="content-text">
                            <?php echo wp_kses_post(wpautop($position->license_info)); ?>
                        </div>
                    </section>
                    <?php endif; ?>
                    
                    <?php if ($position->has_vehicle && !empty($position->vehicle_description)): ?>
                    <section class="content-section">
                        <h2>Company Vehicle</h2>
                        <div class="content-text">
                            <p><?php echo esc_html($position->vehicle_description); ?></p>
                        </div>
                    </section>
                    <?php endif; ?>
                    
                </div>
                
                <!-- Right Sidebar -->
                <div class="position-sidebar">
                    
                    <!-- Quick Info Box -->
                    <div class="info-box">
                        <h3>Quick Info</h3>
                        <div class="info-items">
                            <?php if (!empty($position->job_type)): ?>
                            <div class="info-item">
                                <span class="info-icon">💼</span>
                                <div>
                                    <div class="info-label">Job Type</div>
                                    <div class="info-value"><?php echo esc_html($position->job_type); ?></div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($position->salary_range)): ?>
                            <div class="info-item">
                                <span class="info-icon">💰</span>
                                <div>
                                    <div class="info-label">Salary Range</div>
                                    <div class="info-value"><?php echo esc_html($position->salary_range); ?></div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($position->schedule_type)): ?>
                            <div class="info-item">
                                <span class="info-icon">⏰</span>
                                <div>
                                    <div class="info-label">Schedule</div>
                                    <div class="info-value"><?php echo esc_html($position->schedule_type); ?></div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($position->experience_level)): ?>
                            <div class="info-item">
                                <span class="info-icon">⭐</span>
                                <div>
                                    <div class="info-label">Experience Level</div>
                                    <div class="info-value"><?php echo esc_html($position->experience_level); ?></div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($position->certification_required)): ?>
                            <div class="info-item">
                                <span class="info-icon">🏆</span>
                                <div>
                                    <div class="info-label">Certification</div>
                                    <div class="info-value"><?php echo esc_html($position->certification_required); ?></div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($position->benefits)): ?>
                    <!-- Top-Tier Benefits -->
                    <div class="info-box">
                        <h3>Top-Tier Benefits</h3>
                        <div class="benefits-list">
                            <?php echo CareersPositionsDB::format_list_field($position->benefits); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Call to Action -->
                    <div class="info-box cta-box">
                        <h3>Take the First Step</h3>
                        <p>Submit your application today and join our team of mobile diagnostic professionals.</p>
                        <a href="<?php echo home_url('/apply/' . $position_id); ?>" class="apply-btn-full">Apply Now →</a>
                    </div>
                    
                </div>
            </div>
            
            <!-- Apply Button Section -->
            <div class="bottom-apply-section">
                <div class="apply-container">
                    <h3>Ready to Apply?</h3>
                    <p>Take the next step in your career journey.</p>
                    <a href="<?php echo home_url('/apply/' . $position_id); ?>" class="apply-btn-large">Apply for this Position →</a>
                </div>
            </div>
        </div>
        
        <style>
        .position-detail-page {
            width: 1280px;
            margin: 0 auto;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #333;
        }
        
        .position-hero {
            padding: 3rem 0;
            margin-bottom: 3rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 2rem;
        }
        
        .position-title {
            font-size: 2.5rem;
            font-weight: 500;
            margin: 0 0 0.5rem 0;
            line-height: 1.2;
            color: #111;
        }
        
        .position-location {
            font-size: 1.1rem;
            color: #666;
        }
        
        .apply-btn, .apply-btn-full {
            background: #000;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: background 0.2s ease;
        }
        
        .apply-btn:hover, .apply-btn-full:hover {
            background: #333;
            color: white;
            text-decoration: none;
        }
        
        .apply-btn-full {
            width: 100%;
            text-align: center;
        }
        
        .position-content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 3rem;
            margin-bottom: 3rem;
        }
        
        .content-section {
            margin-bottom: 2.5rem;
        }
        
        .content-section h2 {
            font-size: 1.25rem;
            font-weight: 500;
            color: #111;
            margin-bottom: 1rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 0.5rem;
        }
        
        .content-text {
            color: #555;
            line-height: 1.6;
        }
        
        .content-text ul {
            list-style: none;
            padding: 0;
        }
        
        .content-text li {
            padding: 0.25rem 0;
            padding-left: 1rem;
            position: relative;
        }
        
        .content-text li:before {
            content: "•";
            color: #333;
            position: absolute;
            left: 0;
        }
        
        .highlighted-section {
            background: #f9f9f9;
            padding: 1.5rem;
            border-radius: 4px;
            border-left: 3px solid #333;
        }
        
        .info-box {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 4px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .info-box h3 {
            font-size: 1.1rem;
            font-weight: 500;
            color: #111;
            margin: 0 0 1rem 0;
        }
        
        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f5f5f5;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-icon {
            font-size: 1rem;
            flex-shrink: 0;
            opacity: 0.7;
        }
        
        .info-label {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 0.15rem;
        }
        
        .info-value {
            font-weight: 400;
            color: #111;
        }
        
        .benefits-list ul {
            list-style: none;
            padding: 0;
        }
        
        .benefits-list li {
            padding: 0.25rem 0;
            color: #555;
        }
        
        .benefits-list li:before {
            content: "✓";
            color: #333;
            margin-right: 0.5rem;
        }
        
        .cta-box {
            background: #111;
            color: white;
            text-align: center;
        }
        
        .cta-box h3 {
            color: white;
            font-weight: 500;
        }
        
        .cta-box p {
            margin-bottom: 1.5rem;
            opacity: 0.8;
        }
        
        .bottom-apply-section {
            background: #f9f9f9;
            padding: 2.5rem 2rem;
            border-radius: 4px;
            margin-top: 3rem;
            text-align: center;
        }
        
        .apply-container h3 {
            font-size: 1.5rem;
            font-weight: 500;
            color: #111;
            margin-bottom: 0.5rem;
        }
        
        .apply-container p {
            color: #666;
            margin-bottom: 2rem;
        }
        
        .apply-btn-large {
            background: #000;
            color: white;
            padding: 1rem 2rem;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            display: inline-block;
            transition: background 0.2s ease;
        }
        
        .apply-btn-large:hover {
            background: #333;
            text-decoration: none;
        }
        
        @media (max-width: 768px) {
            .position-content-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .position-hero {
                padding: 2rem 0;
                text-align: center;
            }
            
            .position-title {
                font-size: 2rem;
            }
        }
        </style>
        
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Application page shortcode
     */
    public function careers_application_page_shortcode($atts) {
        $position_id = intval($atts['position_id']);
        
        if (empty($position_id)) {
            return '<p>Invalid position.</p>';
        }
        
        $position = CareersPositionsDB::get_position($position_id);
        
        if (!$position || $position->status !== 'published') {
            return '<p>Position not found or no longer available.</p>';
        }
        
        ob_start();
        ?>
        <div class="application-page">
            <!-- Header Section -->
            <div class="application-header">
                <div class="breadcrumb">
                    <a href="<?php echo esc_url(careers_get_job_permalink($position_id)); ?>">← Back to Job Details</a>
                </div>
                <h1>Apply for <?php echo esc_html($position->position_name); ?></h1>
                <div class="position-location">
                    📍 <?php echo esc_html($position->location); ?>
                </div>
                <p class="application-intro">
                    Ready to take the next step in your career? Complete the application below and we'll be in touch soon.
                </p>
            </div>

            <!-- Application Form -->
            <div class="application-form-container">
                <?php echo $this->careers_form_shortcode(array('position_id' => $position_id)); ?>
            </div>
        </div>
        
        <style>
        .application-page {
            max-width: 800px;
            margin: 0 auto;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #333;
        }
        
        .application-header {
            padding: 2rem 0 3rem 0;
            text-align: center;
            border-bottom: 1px solid #eee;
            margin-bottom: 3rem;
        }
        
        .breadcrumb {
            margin-bottom: 2rem;
            text-align: left;
        }
        
        .breadcrumb a {
            color: #666;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .breadcrumb a:hover {
            color: #000;
        }
        
        .application-header h1 {
            font-size: 2rem;
            font-weight: 500;
            margin: 0 0 0.5rem 0;
            color: #111;
        }
        
        .application-header .position-location {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 1.5rem;
        }
        
        .application-intro {
            color: #555;
            font-size: 1rem;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.5;
        }
        
        .application-form-container {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 4px;
            padding: 2rem;
        }
        
        @media (max-width: 768px) {
            .application-page {
                margin: 0 1rem;
            }
            
            .application-header {
                padding: 1.5rem 0 2rem 0;
            }
            
            .application-header h1 {
                font-size: 1.5rem;
            }
            
            .application-form-container {
                padding: 1.5rem;
            }
        }
        </style>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Render application form
     */
    private function render_application_form($position_id) {
        $user = wp_get_current_user();
        
        // Check if user already applied
        $existing_application = CareersApplicationDB::get_application_by_user_job($user->ID, $position_id);
        
        if ($existing_application) {
            return '<div class="application-notice">You have already applied for this position.</div>';
        }
        
        ob_start();
        ?>
        <form id="application-form" enctype="multipart/form-data">
            <?php wp_nonce_field('careers_application_submit', 'careers_nonce'); ?>
            <input type="hidden" name="position_id" value="<?php echo esc_attr($position_id); ?>">
            
            <div class="form-row">
                <label for="resume">Resume (PDF, DOC, DOCX) *</label>
                <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx" required>
            </div>
            
            <div class="form-row">
                <label for="cover_letter">Cover Letter (PDF, DOC, DOCX)</label>
                <input type="file" id="cover_letter" name="cover_letter" accept=".pdf,.doc,.docx">
            </div>
            
            <div class="form-row">
                <label for="additional_info">Additional Information</label>
                <textarea id="additional_info" name="additional_info" rows="4" 
                          placeholder="Any additional information you'd like to share..."></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="submit-button">Submit Application</button>
            </div>
        </form>
        
        <style>
        #application-form {
            background: #f8f9fa;
            padding: 24px;
            border-radius: 8px;
            margin-top: 20px;
            text-align: left;
        }
        
        .form-row {
            margin-bottom: 20px;
        }
        
        .form-row label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-row input, .form-row textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .submit-button {
            background: #27ae60;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        
        .submit-button:hover {
            background: #219a52;
        }
        
        .application-notice {
            background: #d4edda;
            color: #155724;
            padding: 16px;
            border-radius: 6px;
            border: 1px solid #c3e6cb;
            margin-top: 20px;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('#application-form').on('submit', function(e) {
                e.preventDefault();
                
                var formData = new FormData(this);
                formData.append('action', 'careers_submit_application');
                
                $.ajax({
                    url: careers_ajax.ajax_url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            alert('Application submitted successfully!');
                            $('#apply-form').html('<div class="application-notice">Thank you! Your application has been submitted.</div>');
                        } else {
                            alert('Error: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('Error submitting application. Please try again.');
                    }
                });
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Admin form shortcode [careers_form]
     */
    public function careers_form_shortcode($atts) {
        // Check if user has permissions
        if (!current_user_can('manage_options') && !current_user_can('career_admin')) {
            return '<p>You do not have permission to access this form.</p>';
        }
        
        // This will be handled by the dashboard class
        if (class_exists('CareersDashboard')) {
            $dashboard = new CareersDashboard();
            return $dashboard->job_form_shortcode($atts);
        }
        
        return '<p>Dashboard not available.</p>';
    }
    
    /**
     * Handle application submission
     */
    public function handle_application_submission() {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to apply.');
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['careers_nonce'], 'careers_application_submit')) {
            wp_send_json_error('Security check failed.');
        }
        
        $user_id = get_current_user_id();
        $position_id = intval($_POST['position_id']);
        $additional_info = sanitize_textarea_field($_POST['additional_info']);
        
        // Validate position exists
        $position = CareersPositionsDB::get_position($position_id);
        if (!$position) {
            wp_send_json_error('Position not found.');
        }
        
        // Check if user already applied
        $existing = CareersApplicationDB::get_application_by_user_job($user_id, $position_id);
        if ($existing) {
            wp_send_json_error('You have already applied for this position.');
        }
        
        // Handle file uploads
        $resume_url = '';
        $cover_letter_url = '';
        
        if (!empty($_FILES['resume']['name'])) {
            $resume_upload = $this->handle_file_upload('resume', array('pdf', 'doc', 'docx'));
            if (is_wp_error($resume_upload)) {
                wp_send_json_error($resume_upload->get_error_message());
            }
            $resume_url = $resume_upload;
        } else {
            wp_send_json_error('Resume is required.');
        }
        
        if (!empty($_FILES['cover_letter']['name'])) {
            $cover_letter_upload = $this->handle_file_upload('cover_letter', array('pdf', 'doc', 'docx'));
            if (is_wp_error($cover_letter_upload)) {
                wp_send_json_error($cover_letter_upload->get_error_message());
            }
            $cover_letter_url = $cover_letter_upload;
        }
        
        // Prepare application data
        $application_data = array(
            'user_id' => $user_id,
            'job_id' => $position_id,
            'resume_url' => $resume_url,
            'cover_letter_url' => $cover_letter_url,
            'status' => 'pending',
            'meta' => maybe_serialize(array(
                'additional_info' => $additional_info,
                'applied_from' => 'frontend'
            ))
        );
        
        // Insert application
        $result = CareersApplicationDB::insert_application($application_data);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        wp_send_json_success('Application submitted successfully!');
    }
    
    /**
     * Handle file upload
     */
    private function handle_file_upload($field_name, $allowed_extensions = array()) {
        if (!isset($_FILES[$field_name]) || $_FILES[$field_name]['error'] !== UPLOAD_ERR_OK) {
            return new WP_Error('upload_error', 'File upload failed.');
        }
        
        $file = $_FILES[$field_name];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Check file extension
        if (!empty($allowed_extensions) && !in_array($file_extension, $allowed_extensions)) {
            return new WP_Error('invalid_file_type', 'Invalid file type. Allowed: ' . implode(', ', $allowed_extensions));
        }
        
        // Check file size (10MB limit)
        if ($file['size'] > 10 * 1024 * 1024) {
            return new WP_Error('file_too_large', 'File size must be less than 10MB.');
        }
        
        // Create uploads directory
        $upload_dir = wp_upload_dir();
        $careers_dir = $upload_dir['basedir'] . '/careers-applications';
        
        if (!file_exists($careers_dir)) {
            wp_mkdir_p($careers_dir);
        }
        
        // Generate unique filename
        $filename = time() . '_' . sanitize_file_name($file['name']);
        $file_path = $careers_dir . '/' . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            return $upload_dir['baseurl'] . '/careers-applications/' . $filename;
        }
        
        return new WP_Error('upload_failed', 'Failed to save uploaded file.');
    }
}