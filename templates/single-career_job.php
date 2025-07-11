<?php
/**
 * Single Career Job Template
 */

get_header();

while (have_posts()) : the_post();
    $job_id = get_the_ID();
    $location = get_post_meta($job_id, '_career_location', true);
    $employment_type = get_post_meta($job_id, '_career_employment_type', true);
    $salary_min = get_post_meta($job_id, '_career_salary_min', true);
    $salary_max = get_post_meta($job_id, '_career_salary_max', true);
    $experience_level = get_post_meta($job_id, '_career_experience_level', true);
    $benefits = get_post_meta($job_id, '_career_benefits', true);
    $equipment_vehicle = get_post_meta($job_id, '_career_equipment_vehicle', true);
    $licensing = get_post_meta($job_id, '_career_licensing', true);
    $day_in_life = get_post_meta($job_id, '_career_day_in_life', true);
    
    // Check if user already applied
    $user_applied = false;
    if (is_user_logged_in()) {
        $application = CareersApplicationDB::get_application_by_user_job(get_current_user_id(), $job_id);
        $user_applied = !empty($application);
    }
?>

<div class="careers-single-job">
    <div class="careers-job-header">
        <div class="careers-container">
            <h1 class="careers-job-title"><?php the_title(); ?></h1>
            
            <div class="careers-job-meta">
                <?php if (!empty($location)): ?>
                    <span class="careers-meta-item">
                        <span class="careers-icon">📍</span> <?php echo esc_html($location); ?>
                    </span>
                <?php endif; ?>
                
                <?php if (!empty($employment_type)): 
                    $types = careers_get_employment_types();
                    if (isset($types[$employment_type])):
                ?>
                    <span class="careers-meta-item">
                        <span class="careers-icon">💼</span> <?php echo esc_html($types[$employment_type]); ?>
                    </span>
                <?php endif; endif; ?>
                
                <?php if (!empty($salary_min) || !empty($salary_max)): ?>
                    <span class="careers-meta-item">
                        <span class="careers-icon">💰</span> <?php echo careers_format_salary($salary_min, $salary_max); ?>
                    </span>
                <?php endif; ?>
                
                <?php if (!empty($experience_level)): 
                    $levels = careers_get_experience_levels();
                    if (isset($levels[$experience_level])):
                ?>
                    <span class="careers-meta-item">
                        <span class="careers-icon">📊</span> <?php echo esc_html($levels[$experience_level]); ?>
                    </span>
                <?php endif; endif; ?>
            </div>
            
            <div class="careers-job-actions">
                <?php if (is_user_logged_in()): ?>
                    <?php if ($user_applied): ?>
                        <span class="careers-applied-badge">✓ You've Applied</span>
                        <a href="<?php echo esc_url(home_url('/dashboard/')); ?>" class="careers-btn careers-btn-secondary">View Dashboard</a>
                    <?php else: ?>
                        <a href="<?php echo esc_url(add_query_arg('job_id', $job_id, home_url('/apply/'))); ?>" class="careers-btn careers-btn-primary careers-btn-large">Apply for This Position</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="<?php echo esc_url(home_url('/auth/')); ?>" class="careers-btn careers-btn-primary careers-btn-large">Login to Apply</a>
                <?php endif; ?>
                
                <a href="<?php echo esc_url(home_url('/open-positions/')); ?>" class="careers-btn careers-btn-secondary">← Back to All Jobs</a>
            </div>
        </div>
    </div>
    
    <div class="careers-job-content">
        <div class="careers-container">
            <div class="careers-job-main">
                <div class="careers-job-description">
                    <h2>Job Description</h2>
                    <div class="careers-content">
                        <?php the_content(); ?>
                    </div>
                </div>
                
                <?php if (!empty($benefits)): ?>
                <div class="careers-job-section">
                    <h3>Benefits</h3>
                    <div class="careers-benefits">
                        <?php
                        $benefits_list = explode("\n", $benefits);
                        echo '<ul>';
                        foreach ($benefits_list as $benefit) {
                            $benefit = trim($benefit);
                            if (!empty($benefit)) {
                                echo '<li>' . esc_html($benefit) . '</li>';
                            }
                        }
                        echo '</ul>';
                        ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($day_in_life)): ?>
                <div class="careers-job-section">
                    <h3>A Day in the Life</h3>
                    <p><?php echo nl2br(esc_html($day_in_life)); ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($equipment_vehicle)): ?>
                <div class="careers-job-section">
                    <h3>Equipment & Vehicle Requirements</h3>
                    <p><?php echo nl2br(esc_html($equipment_vehicle)); ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($licensing)): ?>
                <div class="careers-job-section">
                    <h3>Licensing Requirements</h3>
                    <p><?php echo nl2br(esc_html($licensing)); ?></p>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="careers-job-sidebar">
                <div class="careers-apply-card">
                    <h3>Ready to Apply?</h3>
                    <p>Join our team and make a difference!</p>
                    
                    <?php if (is_user_logged_in()): ?>
                        <?php if ($user_applied): ?>
                            <p class="careers-applied-message">You applied on <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($application->submitted_at))); ?></p>
                            <a href="<?php echo esc_url(home_url('/dashboard/')); ?>" class="careers-btn careers-btn-secondary careers-btn-block">View Application Status</a>
                        <?php else: ?>
                            <a href="<?php echo esc_url(add_query_arg('job_id', $job_id, home_url('/apply/'))); ?>" class="careers-btn careers-btn-primary careers-btn-block">Apply Now</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="<?php echo esc_url(home_url('/auth/')); ?>" class="careers-btn careers-btn-primary careers-btn-block">Login to Apply</a>
                        <p class="careers-register-text">Don't have an account? <a href="<?php echo esc_url(home_url('/auth/')); ?>">Register here</a></p>
                    <?php endif; ?>
                </div>
                
                <div class="careers-share-card">
                    <h3>Share This Job</h3>
                    <div class="careers-share-buttons">
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode(get_permalink()); ?>" target="_blank" class="careers-share-btn">LinkedIn</a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode(get_the_title()); ?>" target="_blank" class="careers-share-btn">Twitter</a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>" target="_blank" class="careers-share-btn">Facebook</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php endwhile; ?>

<?php get_footer(); ?>