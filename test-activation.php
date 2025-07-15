<?php
/**
 * Test activation to find fatal errors
 */

// Only run if accessed directly
if ($_SERVER['REQUEST_METHOD'] === 'GET' && basename($_SERVER['PHP_SELF']) === 'test-activation.php') {
    echo '<h1>Testing Plugin Activation</h1>';
    
    // Include WordPress
    require_once '../../../wp-config.php';
    
    echo '<h2>Testing File Includes</h2>';
    
    // Test each include file one by one
    $includes = [
        'helpers.php',
        'class-positions-db.php', 
        'class-application-db.php',
        'class-user-roles.php',
        'class-shortcodes-new.php',
        'class-auth.php',
        'class-dashboard-new.php',
        'class-admin.php',
        'class-emails.php',
        'class-elementor-widgets.php'
    ];
    
    foreach ($includes as $file) {
        $file_path = __DIR__ . '/includes/' . $file;
        echo '<p>Testing ' . $file . ': ';
        
        if (!file_exists($file_path)) {
            echo '❌ FILE NOT FOUND</p>';
            continue;
        }
        
        try {
            require_once $file_path;
            echo '✅ OK</p>';
        } catch (Error $e) {
            echo '❌ FATAL ERROR: ' . $e->getMessage() . '</p>';
        } catch (Exception $e) {
            echo '❌ EXCEPTION: ' . $e->getMessage() . '</p>';
        }
    }
    
    echo '<h2>Testing Class Instantiation</h2>';
    
    $classes = [
        'CareersPositionsDB',
        'CareersApplicationDB', 
        'CareersUserRoles',
        'CareersShortcodes',
        'CareersAuth',
        'CareersDashboard',
        'CareersAdmin',
        'CareersEmails',
        'CareersElementorWidgets'
    ];
    
    foreach ($classes as $class) {
        echo '<p>Testing ' . $class . ': ';
        
        if (!class_exists($class)) {
            echo '❌ CLASS NOT FOUND</p>';
            continue;
        }
        
        try {
            if ($class === 'CareersElementorWidgets') {
                // Skip this one for now as it has special initialization
                echo '⏭️ SKIPPED (special initialization)</p>';
                continue;
            }
            
            new $class();
            echo '✅ OK</p>';
        } catch (Error $e) {
            echo '❌ FATAL ERROR: ' . $e->getMessage() . '</p>';
        } catch (Exception $e) {
            echo '❌ EXCEPTION: ' . $e->getMessage() . '</p>';
        }
    }
    
    echo '<h2>Testing Database Operations</h2>';
    
    try {
        if (class_exists('CareersPositionsDB')) {
            echo '<p>Testing positions table creation: ';
            CareersPositionsDB::create_tables();
            echo '✅ OK</p>';
        }
        
        if (class_exists('CareersApplicationDB')) {
            echo '<p>Testing applications table creation: ';
            CareersApplicationDB::create_table();
            echo '✅ OK</p>';
        }
        
    } catch (Error $e) {
        echo '❌ FATAL ERROR: ' . $e->getMessage() . '</p>';
    } catch (Exception $e) {
        echo '❌ EXCEPTION: ' . $e->getMessage() . '</p>';
    }
    
    exit;
}
?>