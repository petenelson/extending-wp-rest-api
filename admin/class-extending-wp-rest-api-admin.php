<?php

if ( ! defined( 'ABSPATH' ) ) wp_die( 'restricted access' );

if ( ! class_exists( 'Extending_WP_REST_API_Admin' ) ) {


	function extending_wp_rest_api_setting_enabled( $setting ) {
		return apply_filters( 'extending-wp-rest-api-setting-is-enabled', false, 'extending-wp-rest-api-settings-general', $setting );
	}



	class Extending_WP_REST_API_Admin {

		private $settings_page                 = 'extending-wp-rest-api-settings';
		private $settings_key_general          = 'extending-wp-rest-api-settings-general';
		private $settings_key_hello_world      = 'extending-wp-rest-api-settings-hello-world';
		private $plugin_settings_tabs          = array();


		public function plugins_loaded() {
			// admin menus
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );

			add_filter( 'extending-wp-rest-api-setting-is-enabled', array( $this, 'setting_is_enabled' ), 1, 3 );
			add_filter( 'extending-wp-rest-api-setting-get', array( $this, 'setting_get' ), 1, 3 );
		}



		public function admin_init() {
			$this->register_general_settings();
			$this->register_hello_world();
		}


		private function register_general_settings() {
			$key = $this->settings_key_general;
			$this->plugin_settings_tabs[$key] = __( 'General', 'extending-wp-rest-api' );

			register_setting( $key, $key );

			$section = 'general';

			add_settings_section( $section, '', array( $this, 'section_header' ), $key );

			add_settings_field( 'add-revision-count', __( 'Add Revision Count to Posts', 'extending-wp-rest-api' ), array( $this, 'settings_yes_no' ), $key, $section,
				array( 'key' => $key, 'name' => 'add-revision-count', ) );

			add_settings_field( 'add-featured-image', __( 'Add Featured Image to Posts', 'extending-wp-rest-api' ), array( $this, 'settings_yes_no' ), $key, $section,
				array( 'key' => $key, 'name' => 'add-featured-image', ) );

			add_settings_field( 'determine-current-user', __( 'Determine Current User Demo', 'extending-wp-rest-api' ), array( $this, 'settings_yes_no' ), $key, $section,
				array( 'key' => $key, 'name' => 'determine-current-user', ) );

			add_settings_field( 'disallow-non-ssl', __( 'Disallow non-SSL', 'extending-wp-rest-api' ), array( $this, 'settings_yes_no' ), $key, $section,
				array( 'key' => $key, 'name' => 'disallow-non-ssl', ) );

			add_settings_field( 'force-ssl-endpoint', __( 'Force SSL Endpoint in Link Header', 'extending-wp-rest-api' ), array( $this, 'settings_yes_no' ), $key, $section,
				array( 'key' => $key, 'name' => 'force-ssl-endpoint', ) );

			add_settings_field( 'change-url-prefix', __( 'Change Endpoint Prefix (/awesome-api)', 'extending-wp-rest-api' ), array( $this, 'settings_yes_no' ), $key, $section,
				array( 'key' => $key, 'name' => 'change-url-prefix', ) );

			add_settings_field( 'restrict-media-endpoint', __( 'Hide Media Endpoint from Non-authenticated users', 'extending-wp-rest-api' ), array( $this, 'settings_yes_no' ), $key, $section,
				array( 'key' => $key, 'name' => 'restrict-media-endpoint', ) );

			add_settings_field( 'disable-media-endpoint', __( 'Disable Media Endpoint', 'extending-wp-rest-api' ), array( $this, 'settings_yes_no' ), $key, $section,
				array( 'key' => $key, 'name' => 'disable-media-endpoint', ) );

			add_settings_field( 'remove-wordpess-core', __( 'Remove WordPress Core', 'extending-wp-rest-api' ), array( $this, 'settings_yes_no' ), $key, $section,
				array( 'key' => $key, 'name' => 'remove-wordpress-core', ) );

		}

		private function register_hello_world() {
			$key = $this->settings_key_hello_world;
			$this->plugin_settings_tabs[$key] =  __( 'Hello World', 'extending-wp-rest-api' );

			register_setting( $key, $key );

			$section = 'hello-world';

			add_settings_section( $section, '', array( $this, 'section_header' ), $key );

		}


		public function setting_is_enabled( $enabled, $key, $setting ) {
			return '1' === $this->setting_get( '0', $key, $setting );
		}


		public function setting_get( $value, $key, $setting ) {

			$args = wp_parse_args( get_option( $key ),
				array(
					$setting => $value,
				)
			);

			return $args[$setting];
		}


		public function settings_input( $args ) {

			$args = wp_parse_args( $args,
				array(
					'name' => '',
					'key' => '',
					'maxlength' => 50,
					'size' => 30,
					'after' => '',
				)
			);


			$name = $args['name'];
			$key = $args['key'];
			$size = $args['size'];
			$maxlength = $args['maxlength'];

			$option = get_option( $key );
			$value = isset( $option[$name] ) ? esc_attr( $option[$name] ) : '';

			echo "<div><input id='{$name}' name='{$key}[{$name}]'  type='text' value='" . $value . "' size='{$size}' maxlength='{$maxlength}' /></div>";
			if ( !empty( $args['after'] ) )
				echo '<div>' . __( $args['after'], 'extending-wp-rest-api' ) . '</div>';

		}


		public function settings_textarea( $args ) {

			$args = wp_parse_args( $args,
				array(
					'name' => '',
					'key' => '',
					'rows' => 10,
					'cols' => 40,
					'after' => '',
				)
			);


			$name = $args['name'];
			$key = $args['key'];
			$rows = $args['rows'];
			$cols = $args['cols'];

			$option = get_option( $key );
			$value = isset( $option[$name] ) ? esc_attr( $option[$name] ) : '';

			echo "<div><textarea id='{$name}' name='{$key}[{$name}]' rows='{$rows}' cols='{$cols}'>" . $value . "</textarea></div>";
			if ( !empty( $args['after'] ) )
				echo '<div>' . $args['after'] . '</div>';

		}


		public function settings_yes_no( $args ) {

			$args = wp_parse_args( $args,
				array(
					'name' => '',
					'key' => '',
					'after' => '',
				)
			);

			$name = $args['name'];
			$key = $args['key'];

			$option = get_option( $key );
			$value = isset( $option[$name] ) ? esc_attr( $option[$name] ) : '';

			if ( empty( $value ) )
				$value = '0';

			echo '<div>';
			echo "<label><input id='{$name}_1' name='{$key}[{$name}]'  type='radio' value='1' " . ( '1' === $value ? " checked=\"checked\"" : "" ) . "/>" . __( 'Yes', 'extending-wp-rest-api' ) . "</label> ";
			echo "<label><input id='{$name}_0' name='{$key}[{$name}]'  type='radio' value='0' " . ( '0' === $value ? " checked=\"checked\"" : "" ) . "/>" . __( 'No', 'extending-wp-rest-api' ) . "</label> ";
			echo '</div>';

			if ( !empty( $args['after'] ) )
				echo '<div>' . __( $args['after'], 'extending-wp-rest-api' ) . '</div>';
		}


		public function admin_menu() {
			add_options_page( __( 'Extending REST API Settings', 'extending-wp-rest-api' ), __( 'Extending REST API', 'extending-wp-rest-api' ), 'manage_options', $this->settings_page, array( $this, 'options_page' ), 30 );
		}


		public function options_page() {

			$tab = $this->current_tab(); ?>
			<div class="wrap">
				<?php $this->plugin_options_tabs(); ?>
				<form method="post" action="options.php" class="options-form">
					<?php settings_fields( $tab ); ?>
					<?php do_settings_sections( $tab ); ?>
					<?php
						if ( $this->settings_key_help !== $tab ) {
							submit_button( __( 'Save Settings', 'extending-wp-rest-api' ), 'primary', 'submit', true );
						}
					?>
				</form>
			</div>
			<?php

			$settings_updated = filter_input( INPUT_GET, 'settings-updated', FILTER_SANITIZE_STRING );
			if ( ! empty( $settings_updated ) ) {
				flush_rewrite_rules( );
			}

		}


		private function current_tab() {
			$current_tab = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );
			return empty( $current_tab ) ? $this->settings_key_general : $current_tab;
		}


		private function plugin_options_tabs() {
			$current_tab = $this->current_tab();
			echo '<h2>' . __( 'Extending WP REST API Settings', 'extending-wp-rest-api' ) . '</h2><h2 class="nav-tab-wrapper">';
			foreach ( $this->plugin_settings_tabs as $tab_key => $tab_caption ) {
				$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
				echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->settings_page . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
			}
			echo '</h2>';
		}


		public function section_header( $args ) {

			switch ( $args['id'] ) {
				case 'hello-world';
					include_once 'admin-hello-world.php';
					wp_enqueue_script( 'extending-wp-resi-api', plugin_dir_url( __FILE__ ) . '/admin-hello-world.js', 'jquery', time(), true );

					// https://highlightjs.org/
					wp_enqueue_script( 'highlight-js', '//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.6/highlight.min.js' );
					wp_enqueue_style( 'highlight-js', '//cdnjs.cloudflare.com/ajax/libs/highlight.js/8.6/styles/default.min.css' );

					break;
			}

			if ( !empty( $output ) ) {
				echo '<p class="settings-section-header">' . $output . '</p>';
			}

		}


	}

}