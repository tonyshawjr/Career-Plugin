<?php
/**
 * Single Career Job Template - Modern Design
 */

get_header();

while (have_posts()) : the_post();
    $job_id = get_the_ID();
    
    // Get all job meta data
    $location = get_post_meta($job_id, '_job_location', true);
    $job_type = get_post_meta($job_id, '_job_type', true);
    $salary_range = get_post_meta($job_id, '_salary_range', true);
    $experience_level = get_post_meta($job_id, '_experience_level', true);
    $application_deadline = get_post_meta($job_id, '_application_deadline', true);
    
    // Additional meta fields for the comprehensive layout
    $department = get_post_meta($job_id, '_department', true) ?: 'Healthcare Services';
    $schedule = get_post_meta($job_id, '_schedule', true) ?: 'Full-time';
    $travel_required = get_post_meta($job_id, '_travel_required', true) ?: '50-75%';
    
    // Get job tags - safely handle potential errors
    $job_tags = array();
    if (taxonomy_exists('job_tag')) {
        $job_tags = wp_get_post_terms($job_id, 'job_tag', array('fields' => 'names'));
        if (is_wp_error($job_tags) || !is_array($job_tags)) {
            $job_tags = array();
        }
    }
    
    // Determine hero image based on job type - with fallback
    $hero_image = 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&h=600'; // Medical equipment default
    if (stripos(get_the_title(), 'ultrasound') !== false) {
        $hero_image = 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&h=600'; // Ultrasound
    } elseif (stripos(get_the_title(), 'ekg') !== false || stripos(get_the_title(), 'ecg') !== false) {
        $hero_image = 'https://images.unsplash.com/photo-1559757175-0eb30cd8c063?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&h=600'; // EKG
    }
    
    // Check if user already applied
    $user_applied = false;
    if (is_user_logged_in()) {
        global $wpdb;
        $application = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}careers_applications WHERE user_id = %d AND job_id = %d",
            get_current_user_id(),
            $job_id
        ));
        $user_applied = !empty($application);
    }
?>

<!-- Hero Section -->
<div class="relative h-64 md:h-80 bg-cover bg-center" style="background-image: url('<?php echo esc_url($hero_image); ?>');">
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    <div class="relative container mx-auto px-4 h-full flex items-center justify-center text-center">
        <div>
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-2"><?php the_title(); ?></h1>
            <p class="text-xl text-white opacity-90">
                <svg class="inline-block w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <?php echo esc_html($location ?: 'Remote'); ?>
            </p>
        </div>
    </div>
</div>

<!-- Breadcrumbs -->
<div class="bg-gray-50 py-3 border-b">
    <div class="container mx-auto px-4">
        <nav class="text-sm">
            <ol class="flex items-center space-x-2 text-gray-600">
                <li><a href="<?php echo home_url(); ?>" class="hover:text-brand-red transition">Home</a></li>
                <li>/</li>
                <li><a href="<?php echo home_url('/open-positions/'); ?>" class="hover:text-brand-red transition">Jobs</a></li>
                <li>/</li>
                <li class="text-gray-900 font-medium"><?php the_title(); ?></li>
            </ol>
        </nav>
    </div>
</div>

<!-- Main Content -->
<div class="bg-gray-50 py-8">
    <div class="container mx-auto px-4">
        <!-- Job Header Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="lg:flex lg:items-start lg:justify-between">
                <div class="mb-4 lg:mb-0">
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3"><?php the_title(); ?></h2>
                    <div class="flex items-center text-gray-600 mb-3">
                        <svg class="w-5 h-5 mr-2 text-brand-red" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <?php echo esc_html($location ?: 'Location TBD'); ?>
                    </div>
                    <?php if (!empty($job_tags)): ?>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($job_tags as $tag): ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-50 text-brand-red">
                            <?php echo esc_html($tag); ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <?php if (is_user_logged_in()): ?>
                        <?php if ($user_applied): ?>
                            <span class="inline-flex items-center px-6 py-3 bg-green-100 text-green-800 rounded-lg font-medium">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Applied
                            </span>
                        <?php else: ?>
                            <a href="<?php echo esc_url(add_query_arg('job_id', $job_id, home_url('/apply/'))); ?>" 
                               class="inline-flex items-center px-6 py-3 bg-brand-red text-white rounded-lg font-medium hover:bg-red-700 transition">
                                Apply Now
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="<?php echo esc_url(home_url('/auth/')); ?>" 
                           class="inline-flex items-center px-6 py-3 bg-brand-red text-white rounded-lg font-medium hover:bg-red-700 transition">
                            Apply Now
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Main Grid Layout -->
        <div class="lg:grid lg:grid-cols-3 lg:gap-6">
            <!-- Main Content Column (2/3 width) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Position Overview Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Position Overview</h3>
                        
                        <div class="prose prose-gray max-w-none">
                            <?php the_content(); ?>
                        </div>

                        <!-- Responsibilities Section -->
                        <div class="mt-6">
                            <h4 class="text-lg font-semibold text-gray-900 mb-3">Key Responsibilities</h4>
                            <ul class="space-y-2">
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 mr-2 text-brand-red mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Perform mobile X-ray examinations at patient locations</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 mr-2 text-brand-red mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Maintain equipment and ensure proper functioning</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 mr-2 text-brand-red mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Document patient information and imaging results</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 mr-2 text-brand-red mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Provide compassionate patient care and communication</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Requirements Section -->
                        <div class="mt-6">
                            <h4 class="text-lg font-semibold text-gray-900 mb-3">Requirements</h4>
                            <ul class="space-y-2">
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 mr-2 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Valid state radiologic technologist license</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 mr-2 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>ARRT certification required</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 mr-2 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Valid driver's license and clean driving record</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Licensing Notice -->
                        <div class="mt-6 p-4 bg-red-50 border-l-4 border-brand-red">
                            <p class="text-sm text-gray-800">
                                <strong>State Licensing:</strong> Must maintain active licensing in states serviced. We assist with multi-state licensing requirements.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Company Vehicle Section -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Company Vehicle Provided</h3>
                        <div class="flex items-center mb-4">
                            <div class="h-12 mr-4 flex items-center">
                                <span class="text-2xl font-bold text-blue-600">SUBARU</span>
                            </div>
                            <p class="text-gray-600">Fully equipped Subaru Outback with all necessary equipment</p>
                        </div>
                        <img src="https://images.unsplash.com/photo-1449824913935-59a10b8d2000?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&h=400" alt="Subaru Outback" class="w-full h-56 object-cover rounded-lg mb-4">
                        
                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-semibold text-gray-900 mb-2">Vehicle Features</h4>
                                <ul class="text-sm text-gray-600 space-y-1">
                                    <li>• All-wheel drive for all weather conditions</li>
                                    <li>• Spacious cargo area for equipment</li>
                                    <li>• Fuel card provided</li>
                                    <li>• Regular maintenance covered</li>
                                </ul>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-semibold text-gray-900 mb-2">Equipment Included</h4>
                                <ul class="text-sm text-gray-600 space-y-1">
                                    <li>• State-of-the-art portable X-ray unit</li>
                                    <li>• Digital imaging system</li>
                                    <li>• Safety equipment</li>
                                    <li>• Communication devices</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Service Area</h3>
                        <img src="https://images.unsplash.com/photo-1524661135-423995f22d0b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&h=300" alt="Service Area" class="w-full h-48 object-cover rounded-lg mb-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 mr-2 text-brand-red mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <p class="text-gray-600">This position covers the <?php echo esc_html($location); ?> area, servicing nursing homes, assisted living facilities, and private residences within a 50-mile radius.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Column (1/3 width) -->
            <div class="lg:col-span-1 space-y-6 mt-6 lg:mt-0">
                <!-- Quick Info Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Quick Info</h3>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <div>
                                <p class="text-sm text-gray-500">Employment Type</p>
                                <p class="font-medium text-gray-900"><?php echo esc_html($job_type ?: 'Full-time'); ?></p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <p class="text-sm text-gray-500">Salary Range</p>
                                <p class="font-medium text-gray-900"><?php echo esc_html($salary_range ?: 'Competitive'); ?></p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <div>
                                <p class="text-sm text-gray-500">Experience Level</p>
                                <p class="font-medium text-gray-900"><?php echo esc_html(ucfirst($experience_level) ?: 'Entry to Mid Level'); ?></p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <div>
                                <p class="text-sm text-gray-500">Department</p>
                                <p class="font-medium text-gray-900"><?php echo esc_html($department); ?></p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <p class="text-sm text-gray-500">Schedule</p>
                                <p class="font-medium text-gray-900"><?php echo esc_html($schedule); ?></p>
                            </div>
                        </li>
                        <?php if ($application_deadline): ?>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <div>
                                <p class="text-sm text-gray-500">Application Deadline</p>
                                <p class="font-medium text-gray-900"><?php echo date('M j, Y', strtotime($application_deadline)); ?></p>
                            </div>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Benefits Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Benefits</h3>
                    <ul class="space-y-2">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">Competitive salary with performance bonuses</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">Comprehensive health insurance</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">401(k) with company match</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">Paid time off and holidays</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">Company vehicle and fuel card</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">Continuing education support</span>
                        </li>
                    </ul>
                    <p class="mt-4 text-sm text-gray-600 italic">Join a team that values your contribution to patient care!</p>
                </div>

                <!-- Daily Workflow Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">A Day in Your Life</h3>
                    <div class="space-y-3">
                        <div class="flex">
                            <div class="flex-shrink-0 w-8 h-8 bg-red-100 text-brand-red rounded-full flex items-center justify-center text-sm font-medium">1</div>
                            <p class="ml-3 text-sm text-gray-700">Start your day with route planning and equipment check</p>
                        </div>
                        <div class="flex">
                            <div class="flex-shrink-0 w-8 h-8 bg-red-100 text-brand-red rounded-full flex items-center justify-center text-sm font-medium">2</div>
                            <p class="ml-3 text-sm text-gray-700">Visit 4-6 facilities performing X-ray examinations</p>
                        </div>
                        <div class="flex">
                            <div class="flex-shrink-0 w-8 h-8 bg-red-100 text-brand-red rounded-full flex items-center justify-center text-sm font-medium">3</div>
                            <p class="ml-3 text-sm text-gray-700">Upload images and complete documentation</p>
                        </div>
                        <div class="flex">
                            <div class="flex-shrink-0 w-8 h-8 bg-red-100 text-brand-red rounded-full flex items-center justify-center text-sm font-medium">4</div>
                            <p class="ml-3 text-sm text-gray-700">End your day knowing you've made a difference</p>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t">
                        <p class="text-sm text-gray-600">
                            <span class="font-medium">Time allocation:</span><br>
                            70% Patient care • 20% Travel • 10% Documentation
                        </p>
                    </div>
                </div>

                <!-- Call-to-Action Card -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Ready to Join Our Team?</h3>
                    <p class="text-gray-600 mb-4">Take the next step in your healthcare career with National Mobile X-Ray.</p>
                    
                    <?php if (is_user_logged_in()): ?>
                        <?php if ($user_applied): ?>
                            <a href="<?php echo esc_url(home_url('/dashboard/')); ?>" 
                               class="block w-full text-center px-6 py-3 bg-gray-600 text-white rounded-lg font-medium">
                                View Application Status
                            </a>
                        <?php else: ?>
                            <a href="<?php echo esc_url(add_query_arg('job_id', $job_id, home_url('/apply/'))); ?>" 
                               class="block w-full text-center px-6 py-3 bg-brand-red text-white rounded-lg font-medium hover:bg-red-700 transition">
                                Apply Now
                                <svg class="inline-block w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="<?php echo esc_url(home_url('/auth/')); ?>" 
                           class="block w-full text-center px-6 py-3 bg-brand-red text-white rounded-lg font-medium hover:bg-red-700 transition">
                            Apply Now
                            <svg class="inline-block w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </a>
                    <?php endif; ?>
                    
                    <div class="mt-4 pt-4 border-t">
                        <p class="text-sm text-gray-600 mb-2">Share this opportunity:</p>
                        <button onclick="copyToClipboard('<?php echo get_permalink(); ?>')" 
                                class="text-sm text-brand-red hover:text-red-700 transition">
                            <svg class="inline-block w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            Copy Link
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Link copied to clipboard!');
    }, function(err) {
        console.error('Could not copy text: ', err);
    });
}
</script>

<?php endwhile; ?>

<?php
// Handle case where job doesn't exist
if (!have_posts()) {
    ?>
    <div class="bg-gray-50 py-16">
        <div class="container mx-auto px-4">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center max-w-lg mx-auto">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Job Not Found</h2>
                <p class="text-gray-600 mb-6">The job you're looking for doesn't exist or has been removed.</p>
                <a href="<?php echo home_url('/open-positions/'); ?>" 
                   class="inline-flex items-center px-6 py-3 bg-brand-red text-white rounded-lg font-medium hover:bg-red-700 transition">
                    Browse Open Positions
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>
    <?php
}

get_footer();
?>