<?php
/*
Plugin Name: Extending the WP REST API
*/

if ( ! defined( 'ABSPATH' ) ) die( 'restricted access' );

require_once plugin_dir_path( __FILE__ ) .'includes/class-extending-wp-rest-api-admin-ajax.php';

$admin_ajax = new Extending_WP_REST_API_Admin_Ajax();
add_action( 'plugins_loaded', array( $admin_ajax, 'plugins_loaded') );
