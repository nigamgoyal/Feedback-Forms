<?php
// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Delete the plugin folder.
$plugin_folder = plugin_dir_path( __FILE__ );

if ( is_dir( $plugin_folder ) ) {
    // Recursively delete the directory.
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator( $plugin_folder, RecursiveDirectoryIterator::SKIP_DOTS ),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ( $files as $fileinfo ) {
        $fileinfo->isDir() ? rmdir( $fileinfo->getRealPath() ) : unlink( $fileinfo->getRealPath() );
    }
    rmdir( $plugin_folder );

    global $wpdb;
    $table_names = array(
        $wpdb->prefix . 'feedback',
        $wpdb->prefix . 'feedback_growth_track',
        $wpdb->prefix . 'feedback_1_to_1_meeting',
        $wpdb->prefix . 'feedback_1_to_1_meeting_template',
        $wpdb->prefix . 'feedback_1_to_1_meeting_agenda',
        $wpdb->prefix . 'feedback_1_to_1_meeting_actionitem'
    );

    // Drop tables
    foreach ($table_names as $table_name) {

        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) === $table_name )
        // Delete table from database
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }
}

