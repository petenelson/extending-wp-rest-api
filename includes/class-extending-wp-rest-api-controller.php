<?php

if ( ! class_exists( 'Extending_WP_REST_API_Controller' ) ) {


	class Extending_WP_REST_API_Controller {


		public function rest_api_init( ) {

			$this->register_routes();

			$this->add_revision_count_to_posts();

			add_filter( 'rest_prepare_post', array( $this, 'add_featured_image_link' ), 10, 3 );

		}


		public function plugins_loaded() {

			// enqueue WP_API_Settings script
			// TODO move this to an admin class
			add_action( 'wp_print_scripts', function() {
				if ( is_admin() ) {
					wp_enqueue_script( 'wp-api' );
				}
			} );

			// custom authenication handling
			add_filter( 'determine_current_user', array( $this, 'determine_current_user') );

			// restrict access to the media endpoint
			add_action( 'init', function() {

				// _add_extra_api_post_type_arguments() in the WP REST API sets this to true
				// we'll turn it off for unauthenticated requests
				// ideally, the GET request would have a filterable permissions check

				global $wp_post_types;
				$wp_post_types['attachment']->show_in_rest = is_user_logged_in(); // other checks could be added here


			}, 20 );

			add_filter( 'rest_url_prefix', function( $endpoint ) {

				// if you're changing the endpoint, you'll also need to call flush_rewrite_rules
				// be sure to cache the custom endpoint and only flush the rules if it is changed
				//flush_rewrite_rules();
				//$endpoint = 'wpaustin-json';

				return $endpoint;
			} );

			add_filter( 'rest_pre_serve_request', array( $this, 'multiformat_rest_pre_serve_request' ), 10, 4 );

		}



		public function register_routes() {

			$namespace = 'api-extend'; // base endpoint for our custom API

			// creating a new route for our hello world exaple
			register_rest_route( $namespace, '/hello-world', array(
				'methods'             => array( WP_REST_Server::METHOD_GET, WP_REST_Server::METHOD_POST, ),
				'callback'            => array( $this, 'get_hello_world' ),
				'args'                => array(
					'my-number'           => array(
						'default'           => 0,
						'sanitize_callback' => 'absint',
						),
					'format'          => array(
						'sanitize_callback' => 'sanitize_key',
						)
					),
			) );


			// creating a new route for our custom authentication exaple
			register_rest_route( $namespace, '/whoami', array(
				'methods'              => array( WP_REST_Server::READABLE ),
				'callback'             => array( $this, 'get_whoami' ),
				'permission_callback'  => 'is_user_logged_in',
				'args'                 => array(
					'my-number'           => array(
						'default'           => 0,
						'sanitize_callback' => 'absint',
						),
					),
			) );


			// creating a new route for our custom authentication exaple
			register_rest_route( 'wp/v2', '/cron', array(
				'methods'              => array( WP_REST_Server::READABLE ),
				'callback'             => array( $this, 'get_crons' ),
			) );


			register_rest_route( $namespace, '/itsec-lockout', array(
				'methods'              => array( WP_REST_Server::READABLE ),
				'callback'             => array( $this, 'get_itsec_lockouts' ),
			) );

			register_rest_route( $namespace, '/itsec-lockout/(?P<id>[\d]+)', array(
				'methods'              => array( WP_REST_Server::READABLE ),
				'callback'             => array( $this, 'get_itsec_lockouts' ),
				'args'                 => array(
					'id'           => array(
						'default'           => 0,
						'sanitize_callback' => 'absint',
						),
					),
			) );

		}


		public function get_hello_world( WP_REST_Request $request ) {

			$response = new stdClass();
			$response->hello      = 'world';
			$response->time       = current_time( 'mysql' );
			$response->my_number  = $request['my-number'];

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
			return absint( count( wp_get_post_revisions( $post->ID ) ) );
		}


		public function add_featured_image_link( $result, $post, $request ) {

			if ( has_post_thumbnail( $post->ID ) ) {
				$featured_image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );

				$result->add_link( 'featured_image',
					$featured_image[0],
					array(
						'width' => absint( $featured_image[1] ),
						'height' => absint( $featured_image[2] )
						)
					);
			}

			return $result;

		}


		public function get_whoami( WP_REST_Request $request ) {

			$response = new stdClass();
			$response->current_user = null;

			// runs the determine_current_user filter
			$user = wp_get_current_user();
			if ( ! empty( $user ) ) {
				$response->current_user                = new stdClass();
				$response->current_user->ID            = $user->ID;
				$response->current_user->login         = $user->user_login;
				$response->current_user->email         = $user->user_email;
				$response->current_user->capabilities  = $user->allcaps;
			}

			return rest_ensure_response( $response );
		}


		public function determine_current_user( $user_id ) {

			if (
				stripos( $_SERVER['REQUEST_URI'], '/api-extend/whoami' ) > 0 && // make sure this is only for our whoami demo

				'helloworld' === $_REQUEST['api-key'] && // only for a specific API key

				! empty( $_REQUEST['login'] ) // verify login was passed

				) {

				$user = get_user_by( 'login', $_REQUEST['login'] );
				if ( ! empty( $user ) ) {
					return $user->ID;
				}

			}

			return $user_id;
		}


		public function get_crons( WP_REST_Request $request ) {
			$response = new stdClass();
			$response->cron_jobs  = _get_cron_array();
			$response->schedules  = wp_get_schedules();
			return rest_ensure_response( $response );
		}


		public function get_itsec_lockouts( WP_REST_Request $request ) {

			// itsec_lockouts
			global $wpdb;

			$sql = "select * from {$wpdb->prefix}itsec_lockouts where 1";

			if ( ! empty( $request['id'] ) ) {
				$sql .= $wpdb->prepare( ' and lockout_id = %d' , $request['id'] );
			}

			$response = new stdClass();
			$response->lockouts = $wpdb->get_results( $sql );

			return rest_ensure_response( $response );

		}


		public function multiformat_rest_pre_serve_request( $served, $result, $request, $server ) {

			if ( '/api-extend/hello-world' === $request->get_route() ) {

				// this coud also be accomplished with an Accepts header
				switch ( $request['format'] ) {

					case 'text':
						// if you needed a CSV, this is where you'd do it
						header( 'Content-Type: text/plain; charset=' . get_option( 'blog_charset' ) );
						echo $result->data->hello . ' ';
						echo $result->data->time;
						$served = true; // tells the WP-API that we sent the response already
						break;

					case 'xml': // I guess if you really need to
						header( 'Content-Type: application/xml; charset=' . get_option( 'blog_charset' )  );

						$xmlDoc = new DOMDocument();
						$response = $xmlDoc->appendChild( $xmlDoc->createElement( 'Response' ) );
						$response->appendChild( $xmlDoc->createElement( 'Hello', $result->data->hello ) );
						$response->appendChild( $xmlDoc->createElement( 'Time', $result->data->time ) );

						echo $xmlDoc->saveXML();
						$served = true;
						break;

				}
			}


			return $served;
		}

	}


}
