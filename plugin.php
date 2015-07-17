<?php
/*
Plugin Name: Extending the WP REST API
*/

if ( ! defined( 'ABSPATH' ) ) die( 'restricted access' );

require_once plugin_dir_path( __FILE__ ) .'includes/class-extending-wp-rest-api-admin-ajax.php';
require_once plugin_dir_path( __FILE__ ) .'includes/class-extending-wp-rest-api-controller.php';


$admin_ajax = new Extending_WP_REST_API_Admin_Ajax();
add_action( 'plugins_loaded', array( $admin_ajax, 'plugins_loaded') );

// hook into the rest_api_init action so we can start registering routes
$api_controller = new Extending_WP_REST_API_Controller();
add_action( 'rest_api_init', array( $api_controller, 'rest_api_init') );
add_action( 'plugins_loaded', array( $api_controller, 'plugins_loaded') );
