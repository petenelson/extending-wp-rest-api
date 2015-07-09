<?php

if ( ! class_exists( 'Extending_WP_REST_API' ) ) {


	class Extending_WP_REST_API {

		public function plugins_loaded() {

			// example of admin-ajax got getting some posts
			add_action( 'wp_ajax_api-extend-posts', array( $this, 'ajax_get_posts'), 1 );
			add_action( 'wp_ajax_nopriv_api-extend-posts', array( $this, 'ajax_get_posts'), 1 );

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

	}



}