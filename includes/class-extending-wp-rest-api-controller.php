<?php

if ( ! class_exists( 'Extending_WP_REST_API_Controller' ) ) {


	class Extending_WP_REST_API_Controller {


		public function rest_api_init( ) {

			register_rest_route( 'api-extend', '/hello-world', array(
				'methods'             => array( WP_REST_Server::READABLE ),
				'callback'            => array( $this, 'get_hello_world' ),
				'args'                => array( ),
			) );


		}

		public function get_hello_world( WP_REST_Request $request ) {

			$response = new stdClass();
			$response->hello = 'world';
			$response->time = current_time( 'mysql' );

			return rest_ensure_response( $response );

		}




	}


}