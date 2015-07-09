<?php

if ( ! class_exists( 'Extending_WP_REST_API_Admin_Ajax' ) ) {


	class Extending_WP_REST_API_Admin_Ajax {

		public function plugins_loaded() {

			// example of admin-ajax got getting some posts
			add_action( 'wp_ajax_api-extend-posts', array( $this, 'ajax_get_posts'), 1 );
			add_action( 'wp_ajax_nopriv_api-extend-posts', array( $this, 'ajax_get_posts'), 1 );

			// custom data example
			add_action( 'wp_ajax_api-extend-hello-world', array( $this, 'ajax_hello_world' ) );
			add_action( 'wp_ajax_nopriv_api-extend-hello-world', array( $this, 'ajax_hello_world' ) );

		}


		public function ajax_get_posts() {
			global $post;

			$posts = array();
			$query = new WP_Query( array( 'post_type' => 'post' ) );

			while ( $query->have_posts() ) {
				$query->the_post();
				$posts[] = $post;
			}

			wp_reset_postdata();

			wp_send_json( $posts );

		}


		public function ajax_hello_world() {

			$response = new stdClass();
			$response->hello = 'world';
			$response->time = current_time( 'mysql' );

			wp_send_json( $response );

		}

	}



}