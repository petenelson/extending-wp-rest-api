<?php

if ( ! class_exists( 'Extending_WP_REST_API_Controller' ) ) {


	class Extending_WP_REST_API_Controller {


		public function rest_api_init( ) {

			$this->register_routes();

			if ( extending_wp_rest_api_setting_enabled( 'add-revision-count' ) ) {
				$this->add_revision_count_to_posts();
			}
		}


		public function plugins_loaded() {

			// enqueue WP_API_Settings script
			add_action( 'wp_print_scripts', function() {
				wp_enqueue_script( 'wp-api' );
			} );

			if ( extending_wp_rest_api_setting_enabled( 'add-featured-image' ) ) {
				add_filter( 'rest_prepare_post', array( $this, 'add_featured_image_link' ), 10, 3 );
			}

			if ( extending_wp_rest_api_setting_enabled( 'disallow-non-ssl' ) ) {
				add_filter( 'rest_pre_dispatch', array( $this, 'disallow_non_ssl' ) );
			}

			if ( extending_wp_rest_api_setting_enabled( 'disable-media-endpoint' ) ) {
				add_filter( 'rest_pre_dispatch', array( $this, 'disable_media_endpoint' ), 10, 2 );
			}

			if ( extending_wp_rest_api_setting_enabled( 'restrict-media-endpoint' ) ) {
				add_filter( 'rest_pre_dispatch', array( $this, 'restrict_media_endpoint' ), 10, 3 );
			}

			if ( extending_wp_rest_api_setting_enabled( 'remove-wordpress-core' ) ) {
				add_filter( 'rest_endpoints', array( $this, 'remove_wordpress_core_endpoints' ), 10, 1 );
			}

			if ( extending_wp_rest_api_setting_enabled( 'determine-current-user' ) ) {
				add_filter( 'determine_current_user', array( $this, 'determine_current_user' ), 50 );
			}

			// You can ignore the extending_wp_rest_api_setting_enabled()
			// calls, it's just for the code demo.

			add_filter( 'rest_url_prefix', function( $endpoint ) {

				if ( extending_wp_rest_api_setting_enabled( 'change-url-prefix' ) ) {
					// if you're changing the endpoint, you'll also need to call flush_rewrite_rules
					// be sure to cache the custom endpoint and only flush the rules if it is changed
					$endpoint = 'awesome-api';
				}

				if ( $endpoint !== get_option( 'extend_api_endpoint' ) ) {
					flush_rewrite_rules();
					update_option( 'extend_api_endpoint', $endpoint );
				}

				return $endpoint;
			} );

			add_filter( 'rest_pre_serve_request', array( $this, 'multiformat_rest_pre_serve_request' ), 10, 4 );


			if ( extending_wp_rest_api_setting_enabled( 'force-ssl-endpoint' ) ) {
				add_filter( 'rest_url', array( $this, 'force_https_rest_url'), 10, 4 );
			}
		}



		public function register_routes() {

			$namespace = 'api-extend'; // base endpoint for our custom API

			// creating a new route for our hello world exaple
			register_rest_route( $namespace, '/v1/hello-world', array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_hello_world' ),
				'args'                => array(
					'format'          => array(
						'sanitize_callback' => 'sanitize_key',
						)
					),
			) );

			// creating a new route editable route for our hello world exaple
			// the sanitize and validate callback functions are passed the value, the request,
			// and the name of the parameter ( $value, $request, $key )
			register_rest_route( $namespace, '/v1/hello-world', array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_hello_world' ),
				'permission_callback' => array( $this, 'update_hello_world_permission_check' ),
				'args'                => array(
					'my_number'           => array(
						'required'          => true,
						'default'           => 10,
						'sanitize_callback' => 'absint',
						'validate_callback' => array( $this, 'number_is_greater_than_10' ),
						),
					)
			) );


			// creating a new route for our hello world exaple
			// versioning example
			register_rest_route( $namespace, '/v2/hello-world', array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_hello_world_v2' ),
				'args'                => array(
					'format'          => array(
						'required'          => false,
						'default'           => 'json',
						'sanitize_callback' => 'sanitize_key',
						)
					),
			) );


			// creating a new route editable route for our hello world exaple
			// the sanitize and validate callbacks are passed the value, the request, and the name of the parameter ( $value, $request, $key )
			register_rest_route( $namespace, '/v1/hello-world', array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_hello_world' ),
				'permission_callback' => array( $this, 'update_hello_world_permission_check' ),
			) );


			// creating a new route for our custom authentication exaple
			register_rest_route( $namespace, '/whoami', array(
				'methods'              => array( WP_REST_Server::READABLE ),
				'callback'             => array( $this, 'get_whoami' ),
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


			// sample for dynamically generating an image and returning it via the REST API
			register_rest_route( $namespace, '/billing-period-chart', array(
				'methods'              => array( WP_REST_Server::READABLE ),
				'callback'             => array( $this, 'get_chart' ),
				'args'                 => array(
					'start'               => array(
						'required'           => true,
						'sanitize_callback'  => array( $this, 'to_date_time' ),
						),
					'end'                 => array(
						'required'           => true,
						'sanitize_callback'  => array( $this, 'to_date_time' ),
						),
					'current'             => array(
						'required'           => true,
						'sanitize_callback'  => array( $this, 'to_date_time' ),
						),
					)
			) );

			register_rest_route( $namespace, '/remote-sizes', array(
				'methods'              => array( WP_REST_Server::READABLE ),
				'callback'             => array( $this, 'get_remote_sizes' ),
				)
			);

		}


		public function disallow_non_ssl( $response ) {

			if ( ! is_ssl() ) {
				$response = new WP_Error( 'rest_forbidden', __( "SSL is required to access the REST API." ), array( 'status' => 403 ) );
			}

			return $response;
		}

		public function restrict_media_endpoint( $response, $server, $request ) {

			// See if this is the media endpoint and the user is logged in.
			if ( false !== stripos( $request->get_route(), '/wp/v2/media' ) && ! is_user_logged_in() ) {
				$response = new WP_Error(
					'rest_forbidden',
					__( "Authentication is required to access the media endpoint." ),
					array( 'status' => 403 )
				);	
			}

			return $response;
		}


		public function force_https_rest_url( $url, $path, $blog_id, $scheme ) {
			return set_url_scheme( $url, 'https' ); // force the Link header to be https
		}


		public function disable_media_endpoint( $response, $request ) {

			if ( false !== stripos( $request->get_route(), 'wp/v2/media' ) ) {
				$response = new WP_Error( 'rest_forbidden', __( "Sorry, the media endpoint is temporarily disabled." ),
					array( 'status' => 403 ) );
			}

			return $response;
		}


		public function get_hello_world( WP_REST_Request $request ) {

			$response             = new stdClass();
			$response->hello      = 'world';
			$response->time       = current_time( 'mysql' );
			$response->my_number  = absint( get_option( '_extending_my_number' ) );

			$response->some_html  = '<strong>Hello</strong> <em>World</em>';


			return rest_ensure_response( $response );

		}


		public function get_hello_world_v2( WP_REST_Request $request ) {

			$response             = new stdClass();
			$response->hello      = 'This is the new and improved endpoint!';
			$response->my_number  = absint( get_option( '_extending_my_number' ) );

			return rest_ensure_response( $response );

		}


		public function update_hello_world_permission_check( WP_REST_Request $request ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				// can return false or a custom WP_Error
				return new WP_Error( 'rest_forbidden',
					sprintf( 'current user must have manage_options permissions', $value ), array( 'status' => 403 ) );
			} else {
				return true;
			}
		}


		public function number_is_greater_than_10( $value, $request, $key ) {
			if ( $value <= 10 ) {
				// can return false or a custom WP_Error
				return new WP_Error( 'rest_invalid_param',
					sprintf( '%s %d must be greater than 10', $key, $value ), array( 'status' => 400 ) );
			} else {
				return true;
			}
		}


		public function update_hello_world( WP_REST_Request $request ) {

			// because permissions, sanitation, and validation have already been taken care of,
			// we can start working with our data right away

			// update our example with whatever was passed by the user
			update_option( '_extending_my_number', $request['my_number'] );

			// return the updated object
			return $this->get_hello_world( $request );

		}


		public function delete_hello_world( WP_REST_Request $request ) {

			delete_option( '_extending_my_number' );

			// return the updated object
			return $this->get_hello_world( $request );

		}


		public function add_revision_count_to_posts() {

			$schema = array(
				'type'        => 'integer',
				'description' => 'number of revisions',
				'context'     => array( 'view' ),
			);

			register_rest_field( 'post', 'number_of_revisions', array(
				'schema'          => $schema,
				'get_callback'    => array( $this, 'get_number_of_revisions' ),
			) );
		}


		public function get_number_of_revisions( $post ) {
			return absint( count( wp_get_post_revisions( $post->ID ) ) );
		}


		public function add_featured_image_link( $result, $post, $request ) {

			if ( has_post_thumbnail( $post->ID ) ) {
				$featured_image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );

				if ( is_array( $featured_image ) && ! empty( $featured_image ) ) {
					$result->add_link( 'featured_image',
						$featured_image[0],
						array(
							'width' => absint( $featured_image[1] ),
							'height' => absint( $featured_image[2] )
							)
						);
				}
			}

			return $result;
		}


		public function get_whoami( WP_REST_Request $request ) {

			$response = new stdClass();
			$response->current_user = null;

			// Runs the determine_current_user filter.
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

			$uri = filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_STRING );
			$api_key = filter_input( INPUT_GET, 'api-key', FILTER_SANITIZE_STRING );
			$login = filter_input( INPUT_GET, 'login', FILTER_SANITIZE_STRING );

			// Make sure this is only for our whoami demo.
			// Only for a specific API key.
			// Verify login was passed.
			if ( false !== stripos( $uri, '/api-extend/whoami' ) && 'helloworld' === $api_key && ! empty( $login ) ) {

				// this request is allowed to impersonate anyone
				$user = get_user_by( 'login', $login );
				if ( ! empty( $user ) ) {
					$user_id = $user->ID;
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
			$response->lockouts = array();

			$lockouts = $wpdb->get_results( $sql );

			foreach ( $lockouts as $lockout ) {

				// add a link to the individual entry
				$lockout->_links = array( 'self' => array( 'href' => rest_url( '/api-extend/itsec-lockout/' . absint( $lockout->lockout_id ) ) ) );
				$response->lockouts[] = $lockout;

			}

			return rest_ensure_response( $response );

		}


		public function multiformat_rest_pre_serve_request( $served, $result, $request, $server ) {

			if ( in_array( $request->get_route(), array( '/api-extend/v1/hello-world', '/api-extend/v2/hello-world' ) ) ) {

				// this coud also be accomplished with an Accepts header
				switch ( $request['format'] ) {

					case 'text':
						// if you needed a CSV, this is where you'd do it
						header( 'Content-Type: text/plain; charset=' . get_option( 'blog_charset' ) );
						echo $result->data->hello . ' ';
						echo $result->data->time . ' ';
						echo $result->data->my_number;
						$served = true; // tells the WP-API that we sent the response already
						break;

					case 'xml': // I guess if you really need to
						header( 'Content-Type: application/xml; charset=' . get_option( 'blog_charset' )  );

						$xmlDoc = new DOMDocument();
						$response = $xmlDoc->appendChild( $xmlDoc->createElement( 'Response' ) );
						$response->appendChild( $xmlDoc->createElement( 'Hello', $result->data->hello ) );
						$response->appendChild( $xmlDoc->createElement( 'Time', $result->data->time ) );
						$response->appendChild( $xmlDoc->createElement( 'My_Number', $result->data->my_number ) );

						echo $xmlDoc->saveXML();
						$served = true;
						break;

				}

			}


			if ( '/api-extend/billing-period-chart' === $request->get_route() ) {

				// because we returned the image generator class to the API, we can access the
				// image it generated and return it to the browser
				header('Content-type: image/png');
				imagepng($result->data->im);
				imagedestroy($result->data->im);

				$served = true;

			}


			return $served;
		}


		public function to_date_time( $value ) {
			return new DateTime( $value );
		}


		public function get_chart( WP_REST_Request $request ) {

			// https://www.greenmountainenergy.com/api/?api-action=billing-period-chart&s=2014-11-13&e=2014-12-15&c=2014-11-25
			// http://local.baconipsum.dev/wp-json/api-extend/chart?start=2015-02-01&end=2015-04-25&current=2015-03-09

			require_once 'class-pn-date-progress-chart.php';

			$start = $request['start'];
			$end = $request['end'];
			$current = $request['current'];

			$dateChart = new pn_date_progress_chart();
			$dateChart->init();


			// vertical grey line to the left of the blue bar
			$dateChart->draw_line($dateChart->bar_start-1, 1, $dateChart->bar_start-1, $dateChart->bottom_line_y);

			// horizontal grey line to the bottom of the blue bar
			$dateChart->draw_line($dateChart->bar_start-1, $dateChart->bottom_line_y, $dateChart->bar_end, $dateChart->bottom_line_y);

			// start date tick
			$dateChart->draw_line($dateChart->bar_start-1, $dateChart->bottom_line_y, $dateChart->bar_start-1, $dateChart->bottom_line_y + $dateChart->tick_height);


			// end date tick
			$dateChart->draw_line($dateChart->bar_end, $dateChart->bottom_line_y, $dateChart->bar_end, $dateChart->bottom_line_y + $dateChart->tick_height);


			// draw start date
			$dateChart->draw_date($start, $dateChart->bar_start-1);

			// draw end date
			$dateChart->draw_date($end, $dateChart->bar_end);

			// figure out how far into the date range we are and draw a progress bar accordingly
			$total_days = date_diff($start, $end)->days;
			$days_into_range = date_diff($start, $current)->days;
			$percent_into_range = $days_into_range / $total_days;

			// draw ticks at 25%, 50%, 75%
			$percent_ticks_x = [];
			for ($i = .25; $i <= .75 ; $i += .25)
				$percent_ticks_x[] = $dateChart->bar_start + floor($dateChart->bar_max_width * $i);



			foreach ($percent_ticks_x as $p)
				$dateChart->draw_line($p, $dateChart->bottom_line_y, $p, $dateChart->bottom_line_y + $dateChart->tick_height);


			// create dates for 25%, 50% and 75%
			$percent_date = [];
			for ($i = .25; $i <= .75 ; $i += .25) {
				$percent_date[] = date_add(clone $start, new DateInterval('P' . floor($total_days * $i) . 'D'));
			}


			// draw dates at 25%, 50%, 75%
			for ($i=0; $i < 3; $i++)
				$dateChart->draw_date($percent_date[$i], $percent_ticks_x[$i]);

			$dateChart->draw_progress_bar($percent_into_range);

			return $dateChart;


		}


		/**
		 * Unsets all core WP endpoints registered by the WordPress REST API (via rest_endpoints filter)
		 * @param  array   $endpoints   registered endpoints
		 * @return array
		 */
		public function remove_wordpress_core_endpoints( $endpoints ) {

			foreach ( array_keys( $endpoints ) as $endpoint ) {
				if ( stripos( $endpoint, '/wp/v2' ) === 0 ) {
					unset( $endpoints[ $endpoint ] );
				}
			}

			return $endpoints;
		}

		public function get_remote_sizes() {

			// a basic example of logging results from API requests to other servers

			$args = array(
				'time'                  => current_time( 'mysql' ),
				'ip_address'            => filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_SANITIZE_STRING ),
				'route'                 => '/wp-json/dashboard-directory-size/v1/sizes',
				'source'                => 'petenelson.io REST API',
				'method'                => 'GET',
				'status'                => 0,
				'request'               => array(
					'body'                 => '',
					),
				'response'               => array(
					'body'                 => '',
					),
				'milliseconds'          => 0,
				);

			$remote_results = wp_remote_get( 'https://petenelson.io/wp-json/dashboard-directory-size/v1/sizes' );

			$args['response']['headers'] = wp_remote_retrieve_headers( $remote_results );
			$args['status'] = wp_remote_retrieve_response_code( $remote_results );

			$data =  json_decode( wp_remote_retrieve_body( $remote_results ) );

			$args['response']['body'] = $data;

			do_action( 'wp-rest-api-log-insert', $args );

			$data = wp_list_pluck( $data, 'path' );

			return rest_ensure_response( $data  );

		}

	}


}
