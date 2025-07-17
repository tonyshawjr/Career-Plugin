<?php
/**
 * Careers Shortcodes Handler - New Custom Table Version
 */
/* Test */
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
        
        // Handle comprehensive application form submission (non-AJAX)
        add_action('init', array($this, 'handle_comprehensive_application_submission'));
        add_action('template_redirect', array($this, 'handle_comprehensive_application_submission'));
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
                    <div class="filters-sidebar bg-white rounded-lg shadow-sm border border-gray-200 p-6 lg:sticky lg:top-6 h-fit">
                        <div class="filters-header flex justify-between items-center mb-6 pb-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 m-0">Filters</h3>
                            <a href="<?php echo esc_url(CareersSettings::get_page_url('open_positions')); ?>" 
                               class="clear-filters text-sm font-medium text-brand-red hover:text-red-700 transition-colors focus:outline-none focus:ring-2 focus:ring-brand-red focus:ring-offset-2 rounded-md px-2 py-1">
                                Clear all
                            </a>
                        </div>
                        
                        <form method="get" action="" class="filters-form space-y-6">
                            <div class="filter-group">
                                <label class="filter-label block text-sm font-medium text-gray-700 mb-2" for="modality-select">
                                    Modality
                                </label>
                                <div class="relative">
                                    <select id="modality-select" 
                                            name="modality" 
                                            onchange="this.form.submit()"
                                            class="filter-select w-full px-3 py-2.5 text-sm border border-gray-300 rounded-md bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-red focus:border-brand-red transition-colors appearance-none cursor-pointer">
                                        <option value="">All Modalities</option>
                                        <?php foreach ($modalities as $modality): ?>
                                            <option value="<?php echo esc_attr($modality); ?>" <?php selected($modality_filter, $modality); ?>>
                                                <?php echo esc_html($modality); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="select-arrow absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="filter-group">
                                <label class="filter-label block text-sm font-medium text-gray-700 mb-2" for="location-select">
                                    Location
                                </label>
                                <div class="relative">
                                    <select id="location-select" 
                                            name="location" 
                                            onchange="this.form.submit()"
                                            class="filter-select w-full px-3 py-2.5 text-sm border border-gray-300 rounded-md bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-red focus:border-brand-red transition-colors appearance-none cursor-pointer">
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
                                    <div class="select-arrow absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="filter-group">
                                <label class="filter-label block text-sm font-medium text-gray-700 mb-2" for="certification-select">
                                    Certification
                                </label>
                                <div class="relative">
                                    <select id="certification-select" 
                                            name="certification" 
                                            onchange="this.form.submit()"
                                            class="filter-select w-full px-3 py-2.5 text-sm border border-gray-300 rounded-md bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-red focus:border-brand-red transition-colors appearance-none cursor-pointer">
                                        <option value="">All Certifications</option>
                                        <?php foreach ($certifications as $cert): ?>
                                            <option value="<?php echo esc_attr($cert); ?>" <?php selected($certification_filter, $cert); ?>>
                                                <?php echo esc_html($cert); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="select-arrow absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
                
                <div class="positions-content">
                    <div class="positions-grid">
                        <?php if (empty($positions)): ?>
                            <div class="empty-state text-center py-16 px-6">
                                <div class="empty-state-icon w-16 h-16 mx-auto mb-6 text-gray-300">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6.5M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2M8 6v10a2 2 0 002 2h4a2 2 0 002-2V6"/>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No positions found</h3>
                                <p class="text-gray-600 mb-6 max-w-md mx-auto">Try adjusting your filters or check back later for new opportunities.</p>
                                <a href="<?php echo esc_url(CareersSettings::get_page_url('open_positions')); ?>" 
                                   class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 transition-colors">
                                    Clear Filters
                                </a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($positions as $position): ?>
                                <article class="position-card bg-white rounded-lg shadow-sm border border-gray-200 p-6 transition-shadow hover:shadow-md">
                                    <header class="position-card-header mb-4">
                                        <h3 class="position-title">
                                            <a href="<?php echo esc_url(careers_get_job_permalink($position->id)); ?>" 
                                               class="text-gray-900 hover:text-brand-red transition-colors">
                                                <?php echo esc_html($position->position_name); ?>
                                            </a>
                                        </h3>
                                        <div class="position-badges flex flex-wrap gap-2">
                                            <?php if (!empty($position->job_type)): ?>
                                                <span class="position-badge position-badge-<?php echo esc_attr(strtolower(str_replace([' ', '-'], '-', $position->job_type))); ?>">
                                                    <?php echo esc_html($position->job_type); ?>
                                                </span>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($position->certification_required)): ?>
                                                <?php 
                                                $certs = explode(',', $position->certification_required);
                                                foreach ($certs as $cert): 
                                                    $cert = trim($cert);
                                                    if (!empty($cert)):
                                                        $cert_slug = strtolower(str_replace([' ', '-'], '-', $cert));
                                                ?>
                                                    <span class="position-badge position-badge-cert position-badge-cert-<?php echo esc_attr($cert_slug); ?>">
                                                        <?php echo esc_html($cert); ?>
                                                    </span>
                                                <?php 
                                                    endif;
                                                endforeach; 
                                                ?>
                                            <?php endif; ?>
                                        </div>
                                    </header>
                                    
                                    <div class="position-meta mb-4">
                                        <div class="position-location flex items-center text-gray-600 text-sm mb-2">
                                            <svg class="w-4 h-4 mr-2 flex-shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12 2C8.13 2 5 5.13 5 9C5 14.25 12 22 12 22S19 14.25 19 9C19 5.13 15.87 2 12 2ZM12 11.5C10.62 11.5 9.5 10.38 9.5 9S10.62 6.5 12 6.5 14.5 7.62 14.5 9 13.38 11.5 12 11.5Z" fill="currentColor"/>
                                            </svg>
                                            <?php echo esc_html($position->location); ?>
                                        </div>
                                        <div class="position-date text-xs text-gray-500">
                                            Posted: <?php echo esc_html(date('M j, Y', strtotime($position->created_at))); ?>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($position->position_overview)): ?>
                                        <div class="position-description text-gray-600 text-sm leading-relaxed mb-4">
                                            <?php 
                                            $excerpt = wp_trim_words($position->position_overview, 20, '...');
                                            echo wp_kses_post($excerpt);
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <footer class="position-card-footer mt-auto">
                                        <a href="<?php echo esc_url(careers_get_job_permalink($position->id)); ?>"
                                           class="position-cta-btn inline-flex items-center justify-center w-full px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-md hover:bg-gray-800 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">
                                            View Details
                                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </a>
                                    </footer>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        /* Include badge system CSS */
        @import url('<?php echo plugins_url("assets/css/badge-system.css", dirname(__FILE__)); ?>');
        
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
        
        /* ===== MODERN FILTERS SIDEBAR - TAILWIND INSPIRED ===== */
        
        .filters-sidebar {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 1.5rem;
            height: fit-content;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            transition: box-shadow 0.2s ease;
        }
        
        /* Sticky positioning for desktop */
        @media (min-width: 1024px) {
            .filters-sidebar {
                position: sticky;
                top: 1.5rem;
            }
        }
        
        .filters-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .filters-header h3 {
            margin: 0;
            font-size: 1.125rem;
            font-weight: 600;
            color: #111827;
            line-height: 1.4;
        }
        
        .clear-filters {
            color: #BF1E2D;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            transition: all 0.15s ease;
            border: none;
            background: transparent;
        }
        
        .clear-filters:hover {
            color: #9f1c25;
            background-color: rgba(191, 30, 45, 0.05);
            text-decoration: none;
        }
        
        .clear-filters:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(191, 30, 45, 0.1);
        }
        
        .filters-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        
        .filter-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }
        
        .filter-select {
            width: 100%;
            padding: 0.625rem 0.75rem;
            padding-right: 2.5rem; /* Space for custom arrow */
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            color: #111827;
            background: #ffffff;
            transition: all 0.15s ease;
            appearance: none;
            cursor: pointer;
            line-height: 1.4;
        }
        
        .filter-select:hover {
            border-color: #9ca3af;
        }
        
        .filter-select:focus {
            outline: none;
            border-color: #BF1E2D;
            box-shadow: 0 0 0 3px rgba(191, 30, 45, 0.1);
        }
        
        .filter-select:disabled {
            background-color: #f9fafb;
            color: #6b7280;
            cursor: not-allowed;
        }
        
        /* Custom select arrow styling */
        .select-arrow {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            padding-right: 0.5rem;
            pointer-events: none;
        }
        
        .select-arrow svg {
            width: 1rem;
            height: 1rem;
            color: #6b7280;
            transition: color 0.15s ease;
        }
        
        .filter-select:focus + .select-arrow svg {
            color: #BF1E2D;
        }
        
        /* Optgroup styling */
        .filter-select optgroup {
            font-weight: 600;
            color: #374151;
            background-color: #f9fafb;
        }
        
        .filter-select option {
            padding: 0.5rem;
            color: #111827;
            background-color: #ffffff;
        }
        
        .filter-select option:hover {
            background-color: #f3f4f6;
        }
        
        .positions-content {
            min-width: 0;
        }
        
        .positions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.5rem;
        }
        
        .position-card {
            display: flex;
            flex-direction: column;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 1.5rem;
            transition: box-shadow 0.2s ease;
        }
        
        .position-card:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
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
        
        /* ===== MODERN BADGE SYSTEM - TAILWIND INSPIRED ===== */
        
        /* Base Badge Styles */
        .position-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem; /* py-1 px-3 */
            border-radius: 9999px; /* rounded-full */
            font-size: 0.75rem; /* text-xs */
            font-weight: 500; /* font-medium */
            line-height: 1.2;
            white-space: nowrap;
            transition: all 0.15s ease;
            border: 1px solid transparent;
            letter-spacing: 0.025em;
        }
        
        /* Job Type Badges - Color-Coded System with Improved Contrast */
        .position-badge-full-time {
            background-color: #dbeafe; /* blue-100 */
            color: #1e40af; /* blue-700 - improved contrast */
            border-color: #bfdbfe; /* blue-200 */
        }
        
        .position-badge-part-time {
            background-color: #fef3c7; /* amber-100 */
            color: #b45309; /* amber-700 - improved contrast */
            border-color: #fde68a; /* amber-200 */
        }
        
        .position-badge-contract {
            background-color: #f3e8ff; /* violet-100 */
            color: #6d28d9; /* violet-700 - improved contrast */
            border-color: #ddd6fe; /* violet-200 */
        }
        
        .position-badge-per-diem,
        .position-badge-perdiem {
            background-color: #d1fae5; /* emerald-100 */
            color: #047857; /* emerald-700 - improved contrast */
            border-color: #a7f3d0; /* emerald-200 */
        }
        
        .position-badge-travel {
            background-color: #fce7f3; /* pink-100 */
            color: #be185d; /* pink-700 - improved contrast */
            border-color: #fbcfe8; /* pink-200 */
        }
        
        /* Certification Badges - Distinct Visual Treatment */
        .position-badge-cert {
            background-color: #f8fafc; /* slate-50 */
            color: #475569; /* slate-600 - improved contrast */
            border-color: #e2e8f0; /* slate-200 */
            font-weight: 600; /* font-semibold for emphasis */
            position: relative;
        }
        
        .position-badge-cert::before {
            content: "🏆";
            margin-right: 0.25rem;
            font-size: 0.625rem;
            opacity: 0.8;
        }
        
        /* Hover Effects for Better Interaction */
        .position-badge:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        /* Enhanced Typography Hierarchy */
        .position-badge {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        
        /* Legacy badge support */
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
        
        /* Empty State Styles */
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 4rem 1.5rem;
            color: #6b7280;
        }
        
        .empty-state-icon {
            width: 4rem;
            height: 4rem;
            margin: 0 auto 1.5rem;
            color: #d1d5db;
        }
        
        .empty-state h3 {
            font-size: 1.125rem;
            font-weight: 500;
            color: #111827;
            margin-bottom: 0.5rem;
        }
        
        .empty-state p {
            color: #6b7280;
            margin-bottom: 1.5rem;
            max-width: 28rem;
            margin-left: auto;
            margin-right: auto;
        }
        
        .empty-state a {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            background-color: #f3f4f6;
            color: #374151;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.375rem;
            text-decoration: none;
            transition: background-color 0.2s ease;
        }
        
        .empty-state a:hover {
            background-color: #e5e7eb;
            color: #374151;
            text-decoration: none;
        }
        
        /* Tailwind-style Utility Classes for Position Cards */
        .bg-white { background-color: #ffffff; }
        .rounded-lg { border-radius: 0.5rem; }
        .shadow-sm { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }
        .border { border-width: 1px; }
        .border-gray-200 { border-color: #e5e7eb; }
        .p-6 { padding: 1.5rem; }
        .transition-shadow { transition-property: box-shadow; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); transition-duration: 150ms; }
        .hover\:shadow-md:hover { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
        
        .mb-4 { margin-bottom: 1rem; }
        .mb-3 { margin-bottom: 0.75rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-6 { margin-bottom: 1.5rem; }
        .mt-auto { margin-top: auto; }
        
        .text-xl { font-size: 1.25rem; line-height: 1.75rem; }
        .font-semibold { font-weight: 600; }
        .text-gray-900 { color: #111827; }
        .text-gray-600 { color: #4b5563; }
        .text-gray-500 { color: #6b7280; }
        .text-gray-700 { color: #374151; }
        .text-white { color: #ffffff; }
        .leading-tight { line-height: 1.25; }
        .leading-relaxed { line-height: 1.625; }
        
        .hover\:text-brand-red:hover { color: #BF1E2D; }
        .transition-colors { transition-property: color, background-color, border-color; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); transition-duration: 150ms; }
        
        .flex { display: flex; }
        .flex-wrap { flex-wrap: wrap; }
        .gap-2 { gap: 0.5rem; }
        .items-center { align-items: center; }
        .justify-center { justify-content: center; }
        
        .px-3 { padding-left: 0.75rem; padding-right: 0.75rem; }
        .py-1 { padding-top: 0.25rem; padding-bottom: 0.25rem; }
        .px-4 { padding-left: 1rem; padding-right: 1rem; }
        .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
        .rounded-full { border-radius: 9999px; }
        .rounded-md { border-radius: 0.375rem; }
        
        .text-xs { font-size: 0.75rem; line-height: 1rem; }
        .text-sm { font-size: 0.875rem; line-height: 1.25rem; }
        .font-medium { font-weight: 500; }
        
        .bg-gray-100 { background-color: #f3f4f6; }
        .bg-gray-900 { background-color: #111827; }
        
        .w-4 { width: 1rem; }
        .h-4 { height: 1rem; }
        .w-16 { width: 4rem; }
        .h-16 { height: 4rem; }
        .mr-2 { margin-right: 0.5rem; }
        .ml-2 { margin-left: 0.5rem; }
        .mx-auto { margin-left: auto; margin-right: auto; }
        .flex-shrink-0 { flex-shrink: 0; }
        
        .inline-flex { display: inline-flex; }
        .w-full { width: 100%; }
        .hover\:bg-gray-800:hover { background-color: #1f2937; }
        .hover\:bg-gray-200:hover { background-color: #e5e7eb; }
        
        .focus\:outline-none:focus { outline: 2px solid transparent; outline-offset: 2px; }
        .focus\:ring-2:focus { box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.05); }
        .focus\:ring-gray-900:focus { box-shadow: 0 0 0 3px rgba(17, 24, 39, 0.1); }
        .focus\:ring-offset-2:focus { box-shadow: 0 0 0 2px #ffffff, 0 0 0 4px rgba(17, 24, 39, 0.1); }
        
        .text-center { text-align: center; }
        .py-16 { padding-top: 4rem; padding-bottom: 4rem; }
        .px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
        .text-lg { font-size: 1.125rem; line-height: 1.75rem; }
        .max-w-md { max-width: 28rem; }
        
        /* Responsive Grid System - Mobile First */
        @media (min-width: 640px) {
            .positions-grid {
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            }
        }
        
        @media (min-width: 768px) {
            .positions-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (min-width: 1024px) {
            .positions-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (min-width: 1280px) {
            .positions-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        /* ===== RESPONSIVE BEHAVIOR FOR FILTERS SIDEBAR ===== */
        
        /* Layout adjustments for smaller screens */
        @media (max-width: 1023px) {
            .positions-layout {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .filters-sidebar {
                position: static !important;
                top: auto !important;
                margin-bottom: 1.5rem;
                width: 100%;
                max-width: none;
            }
            
            /* Adjust filter form layout for tablet */
            .filters-form {
                gap: 1.25rem;
            }
            
            .filter-group {
                margin-bottom: 0;
            }
        }
        
        /* Mobile-specific adjustments */
        @media (max-width: 767px) {
            .filters-sidebar {
                padding: 1.25rem;
                border-radius: 0.375rem;
            }
            
            .filters-header {
                margin-bottom: 1.25rem;
                padding-bottom: 0.75rem;
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }
            
            .filters-header h3 {
                font-size: 1rem;
            }
            
            .clear-filters {
                font-size: 0.813rem;
                padding: 0.375rem 0.75rem;
                align-self: flex-end;
            }
            
            .filters-form {
                gap: 1rem;
            }
            
            .filter-label {
                font-size: 0.813rem;
                margin-bottom: 0.375rem;
            }
            
            .filter-select {
                padding: 0.75rem;
                padding-right: 2.5rem;
                font-size: 0.875rem;
            }
            
            .select-arrow {
                padding-right: 0.75rem;
            }
        }
        
        /* Extra small screens */
        @media (max-width: 480px) {
            .filters-header {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }
            
            .clear-filters {
                align-self: auto;
            }
        }
        
        @media (max-width: 767px) {
            .open-positions-page {
                padding: 1rem;
            }
            
            .page-header {
                margin-bottom: 2rem;
                padding-bottom: 1.5rem;
            }
            
            .page-header h1 {
                font-size: 2rem;
            }
            
            .position-card {
                padding: 1.25rem;
            }
            
            .positions-grid {
                gap: 1rem;
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
        // Get position ID from multiple sources to handle different URL formats
        $position_id = 0;
        
        // Method 1: Old rewrite system (/open-positions/3)
        $position_id = get_query_var('careers_position_id');
        
        // Method 2: Settings-based system (?id=3)
        if (empty($position_id) && isset($_GET['id'])) {
            $position_id = intval($_GET['id']);
        }
        
        // Method 3: Shortcode attribute
        if (empty($position_id) && isset($atts['id'])) {
            $position_id = intval($atts['id']);
        }
        
        error_log('Careers Debug: Job detail page - Final position ID: ' . $position_id);
        error_log('Careers Debug: Job detail page - Query var careers_position_id: ' . get_query_var('careers_position_id'));
        error_log('Careers Debug: Job detail page - GET id: ' . (isset($_GET['id']) ? $_GET['id'] : 'none'));
        error_log('Careers Debug: Job detail page - Atts: ' . print_r($atts, true));
        
        if (empty($position_id)) {
            error_log('Careers Debug: Job detail page - No position ID, returning error');
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
                    <a href="<?php echo CareersSettings::get_page_url('apply', array('position_id' => $position_id)); ?>" class="apply-btn">Apply Now</a>
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
                        <a href="<?php echo CareersSettings::get_page_url('apply', array('position_id' => $position_id)); ?>" class="apply-btn-full">Apply Now →</a>
                    </div>
                    
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
        
        error_log('=== Careers Debug: Application Form Rendering ===');
        error_log('Careers Debug: Application page shortcode called with atts: ' . print_r($atts, true));
        error_log('Careers Debug: Position ID extracted: ' . $position_id);
        
        if (empty($position_id)) {
            error_log('Careers Debug: Position ID is empty - returning error');
            return '<p>Invalid position.</p>';
        }
        
        $position = CareersPositionsDB::get_position($position_id);
        
        if (!$position || $position->status !== 'published') {
            error_log('Careers Debug: Position not found or not published for ID: ' . $position_id);
            return '<p>Position not found or no longer available.</p>';
        }
        
        error_log('Careers Debug: Position found: ' . $position->position_name . ' (ID: ' . $position->id . ')');
        error_log('=== End Careers Debug: Application Form Rendering ===');
        
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
                <?php echo $this->render_comprehensive_application_form($position_id); ?>
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
        
        // Validate position exists (unless it's a general application with position_id = 0)
        if ($position_id > 0) {
            $position = CareersPositionsDB::get_position($position_id);
            if (!$position) {
                wp_send_json_error('Position not found.');
            }
            
            // Check if user already applied for this specific position
            $existing = CareersApplicationDB::get_application_by_user_job($user_id, $position_id);
            if ($existing) {
                wp_send_json_error('You have already applied for this position.');
            }
        } else {
            // For general applications, set position to null for validation
            $position = null;
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
            'status' => 'new',
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
    
    /**
     * Render comprehensive application form with all required fields
     */
    private function render_comprehensive_application_form($position_id) {
        $position = CareersPositionsDB::get_position($position_id);
        $user = wp_get_current_user();
        
        // Check for success message
        if (isset($_GET['application_submitted']) && $_GET['application_submitted'] == '1') {
            ob_start();
            ?>
            <div class="careers-dashboard-container">
                <div class="dashboard-header">
                    <h1 class="dashboard-title">Application Submitted Successfully!</h1>
                    <p class="dashboard-subtitle">Thank you for applying for <strong><?php echo esc_html($position->position_name); ?></strong></p>
                </div>
                <div class="success-message">
                    <p>Your application has been submitted successfully. We will review your application and get back to you soon.</p>
                    <div class="form-actions">
                        <a href="<?php echo CareersSettings::get_page_url('job_detail', array('id' => $position_id)); ?>" class="button">← Back to Job Details</a>
                    </div>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }
        
        // Check if user is logged in
        error_log('Careers Debug: Application form - User logged in status: ' . (is_user_logged_in() ? 'YES' : 'NO'));
        error_log('Careers Debug: Application form - Current user ID: ' . get_current_user_id());
        
        if (!is_user_logged_in()) {
            error_log('Careers Debug: Application form - Showing login required page');
            ob_start();
            ?>
            <div class="careers-dashboard-container">
                <div class="dashboard-header">
                    <h1 class="dashboard-title">Login Required</h1>
                    <p class="dashboard-subtitle">You need to log in to apply for <strong><?php echo esc_html($position->position_name); ?></strong></p>
                </div>
                <div class="login-notice">
                    <p><strong>Please log in to submit your application.</strong></p>
                    <p>If you don't have an account, please contact us and we'll create one for you.</p>
                    <div class="form-actions">
                        <a href="<?php echo wp_login_url(get_permalink()); ?>" class="button button-primary">Log In</a>
                        <a href="<?php echo CareersSettings::get_page_url('job_detail', array('id' => $position_id)); ?>" class="button">← Back to Job Details</a>
                    </div>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }
        
        // Check if user already applied
        $existing_application = CareersApplicationDB::get_application_by_user_job($user->ID, $position_id);
        if ($existing_application) {
            return '<div class="application-notice">You have already applied for this position.</div>';
        }
        
        // Get all states for dropdown
        $states = array(
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
        
        // How did you hear about us options
        $hear_about_options = array(
            'Indeed' => 'Indeed',
            'LinkedIn' => 'LinkedIn',
            'Company Website' => 'Company Website',
            'Job Fair' => 'Job Fair',
            'Referral' => 'Employee Referral',
            'Social Media' => 'Social Media',
            'Google Search' => 'Google Search',
            'Other' => 'Other'
        );
        
        ob_start();
        ?>
        <div class="careers-dashboard-container">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Apply Now</h1>
                <p class="dashboard-subtitle">You are applying for: <strong><?php echo esc_html($position->position_name); ?></strong> in <strong><?php echo esc_html($position->location); ?></strong></p>
            </div>
            
            <form id="comprehensive-application-form" method="post" enctype="multipart/form-data" action="">
                <?php wp_nonce_field('careers_application_submit', 'careers_nonce'); ?>
                <input type="hidden" name="position_id" value="<?php echo esc_attr($position_id); ?>">
                <input type="hidden" name="action" value="submit_comprehensive_application">
                
                <div class="application-form-row">
                    <div class="application-form-group half-width">
                        <label for="first_name">First Name <span class="required">*</span></label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo esc_attr($user->user_firstname); ?>" required>
                    </div>
                    <div class="application-form-group half-width">
                        <label for="last_name">Last Name <span class="required">*</span></label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo esc_attr($user->user_lastname); ?>" required>
                    </div>
                </div>
                
                <div class="application-form-row">
                    <div class="application-form-group">
                        <label for="email">Email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" value="<?php echo esc_attr($user->user_email); ?>" required>
                    </div>
                </div>
                
                <div class="application-form-row">
                    <div class="application-form-group">
                        <label for="phone">Phone Number <span class="required">*</span></label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                </div>
                
                <div class="application-form-row">
                    <div class="application-form-group half-width">
                        <label for="current_city">Current City <span class="required">*</span></label>
                        <input type="text" id="current_city" name="current_city" required>
                    </div>
                    <div class="application-form-group half-width">
                        <label for="current_state">Current State <span class="required">*</span></label>
                        <select id="current_state" name="current_state" required>
                            <option value="">Select a state</option>
                            <?php foreach ($states as $code => $name): ?>
                                <option value="<?php echo esc_attr($code); ?>"><?php echo esc_html($name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="application-form-row">
                    <div class="application-form-group">
                        <label for="role_interested">Role Interested In <span class="required">*</span></label>
                        <input type="text" id="role_interested" name="role_interested" value="<?php echo esc_attr($position->position_name); ?>" readonly>
                    </div>
                </div>
                
                <div class="application-form-row">
                    <div class="application-form-group">
                        <label for="hear_about_us">How did you hear about us? <span class="required">*</span></label>
                        <select id="hear_about_us" name="hear_about_us" required>
                            <option value="">Select an option</option>
                            <?php foreach ($hear_about_options as $value => $label): ?>
                                <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="application-form-row">
                    <div class="application-form-group">
                        <label>Are you a new graduate? <span class="required">*</span></label>
                        <div class="radio-group">
                            <label class="radio-label">
                                <input type="radio" name="new_graduate" value="yes" required> Yes
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="new_graduate" value="no" required> No
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="application-form-row">
                    <div class="application-form-group">
                        <label>Do you have certifications?</label>
                        <div class="radio-group">
                            <label class="radio-label">
                                <input type="radio" name="has_certifications" value="yes"> Yes
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="has_certifications" value="no"> No
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="application-form-row">
                    <div class="application-form-group">
                        <label>Are you willing to relocate? <span class="required">*</span></label>
                        <div class="radio-group">
                            <label class="radio-label">
                                <input type="radio" name="willing_relocate" value="yes" required> Yes
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="willing_relocate" value="no" required> No
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="application-form-row">
                    <div class="application-form-group">
                        <label>Are you willing to travel statewide? <span class="required">*</span></label>
                        <div class="radio-group">
                            <label class="radio-label">
                                <input type="radio" name="willing_travel" value="yes" required> Yes
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="willing_travel" value="no" required> No
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="application-form-row">
                    <div class="application-form-group">
                        <label for="resume">Resume <span class="required">*</span></label>
                        <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx" required>
                        <small>PDF, Word (.doc, .docx) files only</small>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="button button-primary">Submit Application</button>
                </div>
            </form>
        </div>
        
        <style>
        /* Use dashboard minimal styling - no custom form styling needed */
        .careers-dashboard-container .application-form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .careers-dashboard-container .application-form-group {
            flex: 1;
        }
        
        .careers-dashboard-container .application-form-group.half-width {
            flex: 0 0 calc(50% - 0.5rem);
        }
        
        .careers-dashboard-container .radio-group {
            display: flex;
            gap: 1.5rem;
            margin-top: 0.5rem;
        }
        
        .careers-dashboard-container .radio-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: normal;
            cursor: pointer;
        }
        
        .careers-dashboard-container .radio-label input[type="radio"] {
            margin: 0;
        }
        
        .careers-dashboard-container .required {
            color: #dc2626;
        }
        
        .careers-dashboard-container input[readonly] {
            background-color: #f9fafb;
            color: #6b7280;
        }
        
        .careers-dashboard-container small {
            display: block;
            color: #6b7280;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        @media (max-width: 768px) {
            .careers-dashboard-container .application-form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .careers-dashboard-container .application-form-group.half-width {
                flex: 1;
            }
            
            .careers-dashboard-container .radio-group {
                flex-direction: column;
                gap: 0.75rem;
            }
        }
        </style>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Handle comprehensive application form submission
     */
    public function handle_comprehensive_application_submission() {
        // Debug: Log all POST requests (disabled - working correctly)
        // if (!empty($_POST)) {
        //     error_log('Careers Debug: POST REQUEST DETECTED - Action: ' . (isset($_POST['action']) ? $_POST['action'] : 'none'));
        // }
        
        // Check if this is a comprehensive application submission
        if (!isset($_POST['action']) || $_POST['action'] !== 'submit_comprehensive_application') {
            return;
        }
        
        // Application submission working correctly - debug logs minimized
        error_log('Careers Debug: Application submission started for position: ' . (isset($_POST['position_id']) ? $_POST['position_id'] : 'unknown'));
        
        error_log('=== Careers Debug: Application Submission Flow ===');
        error_log('Careers Debug: Comprehensive application submission started');
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            error_log('Careers Debug: User not logged in - wp_die will be called');
            wp_die('You must be logged in to apply for positions.');
        }
        
        error_log('Careers Debug: User is logged in, continuing with submission...');
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['careers_nonce'], 'careers_application_submit')) {
            error_log('Careers Debug: Nonce verification failed');
            wp_die('Security check failed. Please try again.');
        }
        
        $user_id = get_current_user_id();
        $position_id = intval($_POST['position_id']);
        
        error_log('Careers Debug: User ID: ' . $user_id . ', Position ID from POST: ' . $position_id);
        
        // Validate position exists
        $position = CareersPositionsDB::get_position($position_id);
        if (!$position) {
            error_log('Careers Debug: Position not found: ' . $position_id);
            error_log('Careers Debug: Checking if position exists in careers_positions table...');
            
            // Let's check what positions actually exist
            global $wpdb;
            $table_name = $wpdb->prefix . 'careers_positions';
            $existing_positions = $wpdb->get_results("SELECT id, position_name FROM $table_name");
            error_log('Careers Debug: Existing positions: ' . print_r($existing_positions, true));
            
            wp_die('Position not found.');
        }
        
        error_log('Careers Debug: Position found: ' . $position->position_name . ' (ID: ' . $position->id . ')');
        
        // Check if user already applied for this position
        $existing = CareersApplicationDB::get_application_by_user_job($user_id, $position_id);
        if ($existing) {
            error_log('Careers Debug: User already applied for this position');
            wp_die('You have already applied for this position.');
        }
        
        // Handle resume upload
        $resume_url = '';
        error_log('Careers Debug: About to check resume upload...');
        error_log('Careers Debug: FILES array: ' . print_r($_FILES, true));
        
        if (!empty($_FILES['resume']['name'])) {
            error_log('Careers Debug: Resume file detected, processing upload...');
            $resume_upload = $this->handle_file_upload('resume', array('pdf', 'doc', 'docx'));
            if (is_wp_error($resume_upload)) {
                error_log('Careers Debug: Resume upload failed: ' . $resume_upload->get_error_message());
                wp_die('Resume upload failed: ' . $resume_upload->get_error_message());
            }
            $resume_url = $resume_upload;
            error_log('Careers Debug: Resume uploaded successfully: ' . $resume_url);
        } else {
            error_log('Careers Debug: No resume file uploaded - FILES check failed');
            wp_die('Resume is required.');
        }
        
        // Collect all form data into meta field
        $meta_data = array(
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name']),
            'email' => sanitize_email($_POST['email']),
            'phone' => sanitize_text_field($_POST['phone']),
            'current_city' => sanitize_text_field($_POST['current_city']),
            'current_state' => sanitize_text_field($_POST['current_state']),
            'role_interested' => sanitize_text_field($_POST['role_interested']),
            'hear_about_us' => sanitize_text_field($_POST['hear_about_us']),
            'new_graduate' => sanitize_text_field($_POST['new_graduate']),
            'has_certifications' => isset($_POST['has_certifications']) ? sanitize_text_field($_POST['has_certifications']) : '',
            'willing_relocate' => sanitize_text_field($_POST['willing_relocate']),
            'willing_travel' => sanitize_text_field($_POST['willing_travel'])
        );
        
        error_log('Careers Debug: Meta data collected: ' . print_r($meta_data, true));
        
        // Prepare application data
        $application_data = array(
            'user_id' => $user_id,
            'job_id' => $position_id,
            'resume_url' => $resume_url,
            'cover_letter_url' => '', // No cover letter in comprehensive form
            'status' => 'new',
            'meta' => json_encode($meta_data)
        );
        
        error_log('Careers Debug: Final application data being saved: ' . print_r($application_data, true));
        error_log('Careers Debug: CRITICAL - job_id being saved: ' . $application_data['job_id']);
        
        // Insert application
        $application_id = CareersApplicationDB::insert_application($application_data);
        
        if (is_wp_error($application_id)) {
            error_log('Careers Debug: Application insert failed: ' . $application_id->get_error_message());
            wp_die('Failed to submit application: ' . $application_id->get_error_message());
        }
        
        error_log('Careers Debug: Application inserted successfully with ID: ' . $application_id);
        
        // Verify what was actually saved in the database
        $saved_application = CareersApplicationDB::get_application($application_id);
        error_log('Careers Debug: Saved application data: ' . print_r($saved_application, true));
        error_log('Careers Debug: VERIFY - job_id actually saved: ' . $saved_application->job_id);
        
        // Send confirmation email if emails class exists
        if (class_exists('CareersEmails')) {
            do_action('careers_application_submitted', $application_id);
        }
        
        // Redirect to success page
        $redirect_url = add_query_arg('application_submitted', '1', CareersSettings::get_page_url('apply', array('position_id' => $position_id)));
        error_log('Careers Debug: Redirecting to: ' . $redirect_url);
        error_log('=== End Careers Debug: Application Submission Flow ===');
        wp_redirect($redirect_url);
        exit;
    }
}