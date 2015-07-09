<?php

if ( ! class_exists( 'Extending_WP_REST_API_Controller' ) ) {


	class Extending_WP_REST_API_Controller {


		public function rest_api_init( ) {

			$this->register_routes();

			$this->add_revision_count_to_posts();




		}


		public function register_routes() {

			// creating a new route for our hello world exaple
			register_rest_route( 'api-extend', '/hello-world', array(
				'methods'             => array( WP_REST_Server::READABLE ),
				'callback'            => array( $this, 'get_hello_world' ),
				'args'                => array(
					'my-number'           => array(
						'default'           => 0,
						'sanitize_callback' => 'absint',
						),
					),
			) );

		}


		public function get_hello_world( WP_REST_Request $request ) {

			$response = new stdClass();
			$response->hello = 'world';
			$response->time = current_time( 'mysql' );
			$response->my_number= $request['my-number'];

			return rest_ensure_response( $response );

		}


		public function add_revision_count_to_posts() {

			$schema = array(
				'type'        => 'integer',
				'description' => 'number of revisions',
				'context'     => array( 'view' ),
			);

			register_api_field( 'post', 'number_of_revisions', array(
				'schema'          => $schema,
				'get_callback'    => array( $this, 'get_number_of_revisions' ),
			) );

		}


		public function get_number_of_revisions( $post, $request ) {
			return absint( wp_get_post_revisions( $post->ID ) );
		}



	}


}