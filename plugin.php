<?php
/*
Plugin Name: Extending the WP REST API
*/

if ( ! defined( 'ABSPATH' ) ) die( 'restricted access' );

require_once plugin_dir_path( __FILE__ ) .'includes/class-extending-wp-rest-api.php';

$extending = new Extending_WP_REST_API();
add_action( 'plugins_loaded', array( $extending, 'plugins_loaded') );
