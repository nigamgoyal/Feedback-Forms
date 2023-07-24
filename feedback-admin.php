<?php 
/*
 * Plugin Name:       Feedback Forms
 * Description:      A powerful WordPress plugin for feedback, communication, and growth in organizations. It enables employees to easily give and receive feedback, request feedback from colleagues, and schedule one-on-one meetings. Enhances collaboration, productivity, and career development.
 * Plugin URI:      https://wordpress.org/plugins/feedback-forms/
 * Version:           1.0.2
 * Author: Growbiz Solutions
 * Author URI: https://github.com/
 * License: GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: feedback-forms
 */

if ( ! defined( 'ABSPATH' ) ) {
    die();
}

if ( !function_exists( 'add_action' ) ) {
    die();
} 

// Register the uninstall hook.
register_uninstall_hook( __FILE__, 'feedback_reviews_uninstall' );

// Require feedback-db.php file that contains database related functions
require_once(plugin_dir_path(__FILE__) . 'feedback-db.php');

// Register the function to execute when the plugin is activated
register_activation_hook( __FILE__, "create_feedback_table" );
register_activation_hook( __FILE__, "create_growthTrack_table" );
register_activation_hook( __FILE__, "create_1to1meeting_template_table" );
register_activation_hook( __FILE__, "create_1to1meeting_table" );
register_activation_hook( __FILE__, "create_1to1meeting_agenda_table" );
register_activation_hook( __FILE__, "create_1to1meeting_actionItem_table" );

// to add employee role in wordpress usersrole
function add_employee_role() {
    $employee_role = get_role( 'employee' );
    if ( ! $employee_role ) {
        add_role( 'employee', 'Employee', array(
            'read' => true // True allows that capability
        ));
        
    }
}
add_action( 'init', 'add_employee_role' );

/**
 * Check if the current user has the "administrator" or "employee" role.
 *
 * @return bool True if the user has either role, false otherwise.
 */
function getUserRole() {
    // Check if the function wp_get_current_user() exists
    if (!function_exists('wp_get_current_user')) {
        require_once(ABSPATH . "wp-includes/pluggable.php");
    }

    // Get the current user's roles
    $current_user = wp_get_current_user();
    $user_roles = $current_user->roles;

    // Check if the user has the "administrator" or "employee" role
    if (in_array('administrator', $user_roles) || in_array('employee', $user_roles)) {
        return true;
    }

    // Return false if the user doesn't have either role
    return false;
}

if (getUserRole()) {
    require_once('functions.php');
function custom_plugins_admin_styles() {
    if(isset( $_GET['page'] ) && ( $_GET['page'] === 'feedback' || isset( $_GET['page'] ) && $_GET['page'] === 'view-feedback' || isset( $_GET['page'] ) && $_GET['page'] === 'growth-track-admin' || isset( $_GET['page'] ) && $_GET['page'] === 'growth-track' || isset( $_GET['page'] ) && $_GET['page'] === 'employees_1to1_meeting' )){
        wp_enqueue_style( 'feedbackpluginpage-css', plugins_url( 'assets/css/style.css', __FILE__ ), array(), '1.0.0', 'all' );
        wp_enqueue_style( 'bootstrappage-css', plugins_url( 'assets/css/bootstrap.min.css', __FILE__ ), array(), '5.2.3', 'all' );
        wp_enqueue_script( 'bootstrappage-js', plugins_url( 'assets/js/bootstrap.bundle.min.js', __FILE__ ), array( 'jquery' ), '5.2.3', true );
        wp_enqueue_script( 'feedbackpage-plugin', plugins_url( 'assets/js/feedback.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );
    }
}
add_action( 'admin_enqueue_scripts', 'custom_plugins_admin_styles' );



        function feedback_admin_menu() {
            add_menu_page(
                'Feedback',
                'Feedback',
                'read',
                'feedback',
                'employee_feedback_page',
                'dashicons-feedback',
                6,
            );
        }
        add_action( 'admin_menu', 'feedback_admin_menu' );
    
// Add sub-menu page for feedback page
        function feedback_submenues() {
            add_submenu_page(
                'feedback',
                'View Feedback',
                'View Feedback',
                'read',
                'view-feedback',
                'view_feedback_callback',
            );
            add_submenu_page(
                'feedback',
                'Growth Track',
                'Growth Track',
                'read',
                'growth-track',
                'growth_track_callback',
            );
            add_submenu_page(
                'feedback',
                'Growth Track Admin',
                'Growth Track Admin',
                'manage_options',
                'growth-track-admin',
                'growth_track_admin_callback',
            );
            add_submenu_page(
                'feedback',
                '1:1',
                '1:1',
                'read',
                'employees_1to1_meeting',
                'employees_1_to_1_callback',
            );
        }
        add_action('admin_menu', 'feedback_submenues');

    require_once(plugin_dir_path( __FILE__ ).'growth-track-admin.php');
    require_once(plugin_dir_path( __FILE__ ).'employees_1to1_meeting.php');
    require_once(plugin_dir_path( __FILE__ ).'growth-track.php');
    require_once(plugin_dir_path( __FILE__ ).'feedback.php');
    require_once(plugin_dir_path( __FILE__ ).'view-feedback.php');

    // Define uninstall function.
    function feedback_reviews_uninstall() {

    // Include the uninstall script.
    require_once( plugin_dir_path( __FILE__ ) . 'uninstall.php' );

    }

}
