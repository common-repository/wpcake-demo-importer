<?php

/**
 * Return if direct access.
 */
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define class if not exist.
 */
if ( ! class_exists( 'WPCDI_Demos' ) ) {

	/**
	 * The WPCDI_Demos class.
	 */
	class WPCDI_Demos {

		/**
		 * [__construct description]
		 */
		public function __construct() {

			// Return if not admin screen and customize preview screen.
			if ( ! is_admin() || is_customize_preview() ) {
				return;
			}

			if ( version_compare( PHP_VERSION, '5.4', '>=' ) ) {
				require_once( WPCDI_PATH .'/inc/importers/class-helpers.php' );
				require_once( WPCDI_PATH .'/inc/class-install-demos.php' );
			}

			add_action( 'admin_init', array( $this, 'init' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
			add_filter( 'upload_mimes', array( $this, 'allow_xml_uploads' ) );
			add_action( 'admin_footer', array( $this, 'popup' ) );
			add_filter( 'plugin_action_links_' . WPCDI_BASENAME, array( $this, 'plugin_action_links' ), 10, 4 );

		}

		/**
		 * Ajax callbacks.
		 * @return [type] [description]
		 */
		public function init() {

			// try to set no limit to execution time.
			set_time_limit( 0 );

			// Demos popup ajax
			add_action( 'wp_ajax_wpcdi_ajax_get_demo_data', array( $this, 'ajax_demo_data' ) );
			add_action( 'wp_ajax_wpcdi_ajax_required_plugins_activate', array( $this, 'ajax_required_plugins_activate' ) );

			// Get data to import
			add_action( 'wp_ajax_wpcdi_ajax_get_import_data', array( $this, 'ajax_get_import_data' ) );

			// Import XML file
			add_action( 'wp_ajax_wpcdi_ajax_import_xml', array( $this, 'ajax_import_xml' ) );

			// Import customizer settings
			add_action( 'wp_ajax_wpcdi_ajax_import_theme_settings', array( $this, 'ajax_import_theme_settings' ) );

			// Import widgets
			add_action( 'wp_ajax_wpcdi_ajax_import_widgets', array( $this, 'ajax_import_widgets' ) );

			// After import
			add_action( 'wp_ajax_wpcdi_after_import', array( $this, 'ajax_after_import' ) );

		}

		/**
		 * Load scripts on install demo page only
		 *
		 */
		public static function scripts() {

			global $pagenow;

			if ( 'themes.php' == $pagenow && isset( $_GET['page'] )  && 'wpcdi-panel-install-demos' == $_GET['page'] ) {

				// CSS
				wp_enqueue_style( 'wpcdi-demos-style', WPCDI_URL . 'assets/css/demos.css', array(), WPCDI_VERSION, 'all' );

				// JS
				wp_enqueue_script( 'wpcdi-demos-js', WPCDI_URL . 'assets/js/demos.js', array( 'jquery', 'wp-util', 'updates' ), WPCDI_VERSION, true );

				wp_localize_script( 'wpcdi-demos-js', 'wpcdiDemos', array(
					'ajaxurl' 					=> admin_url( 'admin-ajax.php' ),
					'demo_data_nonce' 			=> wp_create_nonce( 'get-demo-data' ),
					'wpcdi_import_data_nonce' 	=> wp_create_nonce( 'wpcdi_import_data_nonce' ),
					'content_importing_error' 	=> esc_html__( 'There was a problem during the importing process resulting in the following error from your server:', 'wpcake-demo-importer' ),
					'button_activating' 		=> esc_html__( 'Activating', 'wpcake-demo-importer' ) . '&hellip;',
					'button_active' 			=> esc_html__( 'Active', 'wpcake-demo-importer' ),
				) );

			}

		}

		/**
		 * Allows xml uploads so we can import from server
		 *
		 */
		public function allow_xml_uploads( $mimes ) {
			$mimes = array_merge( $mimes, array(
				'xml' 	=> 'application/xml'
			) );
			return $mimes;
		}

		/**
		 * Available demos - Get demos data to add them in the Demo Import
		 *
		 */
		public static function get_demos_data() {

			// Demos url
			$url = 'http://www.wpcakedemos.com/demo_data/';


			$data = array(

				'Gymfit' => array(
					'title'							=> __('Gym Fitness', 'wpcake-demo-importer'),
					'categories'        => array( 'Business', 'WooCommerce', 'Free' ),
					'xml_file'     		=> $url . 'gymfit/content.xml',
					'theme_settings' 	=> $url . 'gymfit/customizer.dat',
					'widgets_file'  	=> $url . 'gymfit/widgets.wie',
					'front_is'			=> 'page', // 'page' or 'posts'
					'is_shop'			=> 'yes', // 'yes' or 'no'
					'is_free'			=> true, // true or false
					'required_plugins'  => array(
						'free' => array(
							array(
								'slug'  	=> 'elementor',
								'init'  	=> 'elementor/elementor.php',
								'name'  	=> 'Elementor',
							),
							array(
								'slug'  	=> 'post-grid-elementor-addon',
								'init'  	=> 'post-grid-elementor-addon/post-grid-elementor-addon.php',
								'name'  	=> 'Elementor Post Grid Addon',
							),
							array(
								'slug'  	=> 'woocommerce',
								'init'  	=> 'woocommerce/woocommerce.php',
								'name'  	=> 'WooCommerce',
							),
						),
					),
				),

				'Business' => array(
					'title'							=> __('City Business', 'wpcake-demo-importer'),
					'categories'        => array( 'Business', 'Free' ),
					'xml_file'     		=> $url . 'business/business.xml',
					'theme_settings' 	=> $url . 'business/business.dat',
					'widgets_file'  	=> $url . 'business/business.wie',
					'front_is'			=> 'page', // 'page' or 'posts'
					'is_shop'			=> 'yes', // 'yes' or 'no'
					'is_free'			=> true, // true or false
					'required_plugins'  => array(
						'free' => array(
							array(
								'slug'  	=> 'elementor',
								'init'  	=> 'elementor/elementor.php',
								'name'  	=> 'Elementor',
							),
							array(
								'slug'  	=> 'post-grid-elementor-addon',
								'init'  	=> 'post-grid-elementor-addon/post-grid-elementor-addon.php',
								'name'  	=> 'Elementor Post Grid Addon',
							),
						),
					),
				),

				'Wedding' => array(
					'title'							=> __('Wedding Invite', 'wpcake-demo-importer'),
					'categories'        => array( 'Blog', 'Free' ),
					'xml_file'     		=> $url . 'wedding/wedding.xml',
					'theme_settings' 	=> $url . 'wedding/wedding.dat',
					'widgets_file'  	=> $url . 'wedding/wedding.wie',
					'front_is'			=> 'page', // 'page' or 'posts'
					'is_shop'			=> 'yes', // 'yes' or 'no'
					'is_free'			=> true, // true or false
					'required_plugins'  => array(
						'free' => array(
							array(
								'slug'  	=> 'elementor',
								'init'  	=> 'elementor/elementor.php',
								'name'  	=> 'Elementor',
							),
							array(
								'slug'  	=> 'post-grid-elementor-addon',
								'init'  	=> 'post-grid-elementor-addon/post-grid-elementor-addon.php',
								'name'  	=> 'Elementor Post Grid Addon',
							),
						),
					),
				),

				'Barbershop' => array(
					'title'							=> __('Barbershop', 'wpcake-demo-importer'),
					'categories'        => array( 'Business', 'Blog', 'Free' ),
					'xml_file'     		=> $url . 'barbershop/barbershop.xml',
					'theme_settings' 	=> $url . 'barbershop/barbershop.dat',
					'widgets_file'  	=> $url . 'barbershop/barbershop.wie',
					'front_is'			=> 'page', // 'page' or 'posts'
					'is_shop'			=> 'yes', // 'yes' or 'no'
					'is_free'			=> true, // true or false
					'required_plugins'  => array(
						'free' => array(
							array(
								'slug'  	=> 'elementor',
								'init'  	=> 'elementor/elementor.php',
								'name'  	=> 'Elementor',
							),
							array(
								'slug'  	=> 'post-grid-elementor-addon',
								'init'  	=> 'post-grid-elementor-addon/post-grid-elementor-addon.php',
								'name'  	=> 'Elementor Post Grid Addon',
							),
						),
					),
				),

				'Webstudio' => array(
					'title'							=> __('Design Studio', 'wpcake-demo-importer'),
					'categories'        => array( 'Business', 'Free' ),
					'xml_file'     		=> $url . 'webstudio/webstudio.xml',
					'theme_settings' 	=> $url . 'webstudio/webstudio.dat',
					'widgets_file'  	=> $url . 'webstudio/webstudio.wie',
					'front_is'			=> 'page', // 'page' or 'posts'
					'is_shop'			=> 'yes', // 'yes' or 'no'
					'is_free'			=> true, // true or false
					'required_plugins'  => array(
						'free' => array(
							array(
								'slug'  	=> 'elementor',
								'init'  	=> 'elementor/elementor.php',
								'name'  	=> 'Elementor',
							),
							array(
								'slug'  	=> 'post-grid-elementor-addon',
								'init'  	=> 'post-grid-elementor-addon/post-grid-elementor-addon.php',
								'name'  	=> 'Elementor Post Grid Addon',
							),
						),
					),
				),

				'Fashionstore' => array(
					'title'							=> __('Fashion Store', 'wpcake-demo-importer'),
					'categories'        => array( 'Business', 'WooCommerce', 'Free' ),
					'xml_file'     		=> $url . 'fashionstore/fashionstore.xml',
					'theme_settings' 	=> $url . 'fashionstore/fashionstore.dat',
					'widgets_file'  	=> $url . 'fashionstore/fashionstore.wie',
					'front_is'			=> 'page', // 'page' or 'posts'
					'is_shop'			=> 'yes', // 'yes' or 'no'
					'is_free'			=> true, // true or false
					'required_plugins'  => array(
						'free' => array(
							array(
								'slug'  	=> 'elementor',
								'init'  	=> 'elementor/elementor.php',
								'name'  	=> 'Elementor',
							),
							array(
								'slug'  	=> 'post-grid-elementor-addon',
								'init'  	=> 'post-grid-elementor-addon/post-grid-elementor-addon.php',
								'name'  	=> 'Elementor Post Grid Addon',
							),
							array(
								'slug'  	=> 'woocommerce',
								'init'  	=> 'woocommerce/woocommerce.php',
								'name'  	=> 'WooCommerce',
							),
						),
					),
				),

				'Restaurant' => array(
					'title'							=> __('Local Restaurant', 'wpcake-demo-importer'),
					'categories'        => array( 'Business', 'Free' ),
					'xml_file'     		=> $url . 'restaurant/restaurant.xml',
					'theme_settings' 	=> $url . 'restaurant/restaurant.dat',
					'widgets_file'  	=> $url . 'restaurant/restaurant.wie',
					'front_is'			=> 'page', // 'page' or 'posts'
					'is_shop'			=> 'yes', // 'yes' or 'no'
					'is_free'			=> true, // true or false
					'required_plugins'  => array(
						'free' => array(
							array(
								'slug'  	=> 'elementor',
								'init'  	=> 'elementor/elementor.php',
								'name'  	=> 'Elementor',
							),
							array(
								'slug'  	=> 'post-grid-elementor-addon',
								'init'  	=> 'post-grid-elementor-addon/post-grid-elementor-addon.php',
								'name'  	=> 'Elementor Post Grid Addon',
							),
							array(
								'slug'  	=> 'contact-form-7',
								'init'  	=> 'contact-form-7/wp-contact-form-7.php',
								'name'  	=> 'Contact Form 7',
							),
						),
					),
				),

				'Techrepair' => array(
					'title'							=> __('Tech Repair', 'wpcake-demo-importer'),
					'categories'        => array( 'Business', 'Free' ),
					'xml_file'     		=> $url . 'techrepair/techrepair.xml',
					'theme_settings' 	=> $url . 'techrepair/techrepair.dat',
					'widgets_file'  	=> $url . 'techrepair/techrepair.wie',
					'front_is'			=> 'page', // 'page' or 'posts'
					'is_shop'			=> 'yes', // 'yes' or 'no'
					'is_free'			=> true, // true or false
					'required_plugins'  => array(
						'free' => array(
							array(
								'slug'  	=> 'elementor',
								'init'  	=> 'elementor/elementor.php',
								'name'  	=> 'Elementor',
							),
							array(
								'slug'  	=> 'post-grid-elementor-addon',
								'init'  	=> 'post-grid-elementor-addon/post-grid-elementor-addon.php',
								'name'  	=> 'Elementor Post Grid Addon',
							),
						),
					),
				),

				'College' => array(
					'title'							=> __('College', 'wpcake-demo-importer'),
					'categories'        => array( 'Business', 'Free' ),
					'xml_file'     		=> $url . 'college/college.xml',
					'theme_settings' 	=> $url . 'college/college.dat',
					'widgets_file'  	=> $url . 'college/college.wie',
					'front_is'			=> 'page', // 'page' or 'posts'
					'is_shop'			=> 'yes', // 'yes' or 'no'
					'is_free'			=> true, // true or false
					'required_plugins'  => array(
						'free' => array(
							array(
								'slug'  	=> 'elementor',
								'init'  	=> 'elementor/elementor.php',
								'name'  	=> 'Elementor',
							),
							array(
								'slug'  	=> 'post-grid-elementor-addon',
								'init'  	=> 'post-grid-elementor-addon/post-grid-elementor-addon.php',
								'name'  	=> 'Elementor Post Grid Addon',
							),
							array(
								'slug'  	=> 'contact-form-7',
								'init'  	=> 'contact-form-7/wp-contact-form-7.php',
								'name'  	=> 'Contact Form 7',
							),
						),
					),
				),

				'Contentmarketer' => array(
					'title'							=> __('Content Marketer', 'wpcake-demo-importer'),
					'categories'        => array( 'Business', 'Free' ),
					'xml_file'     		=> $url . 'contentmarketer/contentmarketer.xml',
					'theme_settings' 	=> $url . 'contentmarketer/contentmarketer.dat',
					'widgets_file'  	=> $url . 'contentmarketer/contentmarketer.wie',
					'front_is'			=> 'page', // 'page' or 'posts'
					'is_shop'			=> 'yes', // 'yes' or 'no'
					'is_free'			=> true, // true or false
					'required_plugins'  => array(
						'free' => array(
							array(
								'slug'  	=> 'elementor',
								'init'  	=> 'elementor/elementor.php',
								'name'  	=> 'Elementor',
							),
							array(
								'slug'  	=> 'post-grid-elementor-addon',
								'init'  	=> 'post-grid-elementor-addon/post-grid-elementor-addon.php',
								'name'  	=> 'Elementor Post Grid Addon',
							),
							array(
								'slug'  	=> 'contact-form-7',
								'init'  	=> 'contact-form-7/wp-contact-form-7.php',
								'name'  	=> 'Contact Form 7',
							),
						),
					),
				),

				'Academy' => array(
					'title'							=> __('Academy', 'wpcake-demo-importer'),
					'categories'        => array( 'Business', 'Free' ),
					'xml_file'     		=> $url . 'academy/academy.xml',
					'theme_settings' 	=> $url . 'academy/academy.dat',
					'widgets_file'  	=> $url . 'academy/academy.wie',
					'front_is'			=> 'page', // 'page' or 'posts'
					'is_shop'			=> 'yes', // 'yes' or 'no'
					'is_free'			=> true, // true or false
					'required_plugins'  => array(
						'free' => array(
							array(
								'slug'  	=> 'elementor',
								'init'  	=> 'elementor/elementor.php',
								'name'  	=> 'Elementor',
							),
							array(
								'slug'  	=> 'post-grid-elementor-addon',
								'init'  	=> 'post-grid-elementor-addon/post-grid-elementor-addon.php',
								'name'  	=> 'Elementor Post Grid Addon',
							),
							array(
								'slug'  	=> 'contact-form-7',
								'init'  	=> 'contact-form-7/wp-contact-form-7.php',
								'name'  	=> 'Contact Form 7',
							),
						),
					),
				),

			);

			/*
			* Possible plugins
			*
			*
				array(
					'slug'  	=> 'woocommerce',
					'init'  	=> 'woocommerce/woocommerce.php',
					'name'  	=> 'WooCommerce',
				),

				array(
					'slug'  	=> 'megamenu',
					'init'  	=> 'megamenu/megamenu.php',
					'name'  	=> 'Max Mega Menu',
				),

				array(
					'slug'  	=> 'yith-woocommerce-quick-view',
					'init'  	=> 'yith-woocommerce-quick-view/init.php',
					'name'  	=> 'WooCommerce Quick View',
				),

				array(
					'slug'  	=> 'ti-woocommerce-wishlist',
					'init'  	=> 'ti-woocommerce-wishlist/ti-woocommerce-wishlist.php',
					'name'  	=> 'WooCommerce Wishlist',
				),

				array(
					'slug'  	=> 'woocommerce-pdf-invoices-packing-slips',
					'init'  	=> 'woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php',
					'name'  	=> 'PDF Invoices & Packing Slips',
				),

				array(
					'slug'  	=> 'bbpress',
					'init'  	=> 'bbpress/bbpress.php',
					'name'  	=> 'bbPress',
				),

				array(
					'slug'  	=> 'contact-form-7',
					'init'  	=> 'contact-form-7/wp-contact-form-7.php',
					'name'  	=> 'Contact Form 7',
				),

				array(
					'slug'  	=> 'elementor',
					'init'  	=> 'elementor/elementor.php',
					'name'  	=> 'Elementor',
				),
				array(
					'slug'  	=> 'post-grid-elementor-addon',
					'init'  	=> 'post-grid-elementor-addon/post-grid-elementor-addon.php',
					'name'  	=> 'Elementor Post Grid Addon',
				),


			 */

			// Return
			return apply_filters( 'wpcdi_demos_data', $data );

		}

		/**
		 * Get the category list of all categories used in the predefined demo imports array.
		 *
		 */
		public static function get_demo_all_categories( $demo_imports ) {
			$categories = array();

			foreach ( $demo_imports as $item ) {
				if ( ! empty( $item['categories'] ) && is_array( $item['categories'] ) ) {
					foreach ( $item['categories'] as $category ) {
						$categories[ sanitize_key( $category ) ] = $category;
					}
				}
			}

			if ( empty( $categories ) ) {
				return false;
			}

			return $categories;
		}

		/**
		 * Return the concatenated string of demo import item categories.
		 * These should be separated by comma and sanitized properly.
		 *
		 */
		public static function get_demo_item_categories( $item ) {
			$sanitized_categories = array();

			if ( isset( $item['categories'] ) ) {
				foreach ( $item['categories'] as $category ) {
					$sanitized_categories[] = sanitize_key( $category );
				}
			}

			if ( ! empty( $sanitized_categories ) ) {
				return implode( ',', $sanitized_categories );
			}

			return false;
		}

		/**
		 * Demos popup
		 *
		 */
		public static function popup() {
			global $pagenow;

			// Display on the demos pages
			if ( 'themes.php' == $pagenow && isset( $_GET['page'] ) && 'wpcdi-panel-install-demos' == $_GET['page'] ) { ?>

				<div id="wpcdi-demo-popup-wrap">
					<div class="wpcdi-demo-popup-container">
						<div class="wpcdi-demo-popup-content-wrap">
							<div class="wpcdi-demo-popup-content-inner">
								<a href="#" class="wpcdi-demo-popup-close">×</a>
								<div id="wpcdi-demo-popup-content"></div>
							</div>
						</div>
					</div>
					<div class="wpcdi-demo-popup-overlay"></div>
				</div>

			<?php
			}
		}

		/**
		 * Demos popup ajax.
		 *
		 */
		public static function ajax_demo_data() {

			if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $_GET['demo_data_nonce'], 'get-demo-data' ) ) {
				die( 'This action was stopped for security purposes.' );
			}

			// Database reset url
			if ( is_plugin_active( 'wordpress-database-reset/wp-reset.php' ) ) {
				$plugin_link 	= admin_url( 'tools.php?page=database-reset' );
			} else {
				$plugin_link 	= admin_url( 'plugin-install.php?s=WordPress+Database+Reset&tab=search' );
			}

			// Get all demos
			$demos = self::get_demos_data();

			// Get selected demo
			$demo = isset( $_GET['demo_name'] ) ? sanitize_text_field( wp_unslash( $_GET['demo_name'] ) ) : '';

			// Get required plugins
			$plugins = $demos[$demo][ 'required_plugins' ];

			$demotitle = $demos[$demo][ 'title' ];
			// Get free plugins
			$free = $plugins[ 'free' ];
			?>

			<div id="wpcdi-demo-plugins">

				<h2 class="title"><?php echo sprintf( esc_html__( 'Import the %1$s demo', 'wpcake-demo-importer' ), esc_attr( $demotitle ) ); ?></h2>

				<div class="wpcdi-popup-text">

					<p><?php echo
						sprintf(
							esc_html__( 'Importing demo data allow you to quickly edit everything instead of creating content from scratch. It is recommended uploading sample data on a fresh WordPress install to prevent conflicts with your current content. You can use this plugin to reset your site if needed: %1$sWordpress Database Reset%2$s.', 'wpcake-demo-importer' ),
							'<a href="'. esc_url( $plugin_link ) .'" target="_blank">',
							'</a>'
						); ?></p>

					<div class="wpcdi-required-plugins-wrap">
						<h3><?php esc_html_e( 'Required Plugins', 'wpcake-demo-importer' ); ?></h3>
						<p><?php esc_html_e( 'For your site to look exactly like this demo, the plugins below need to be activated.', 'wpcake-demo-importer' ); ?></p>
						<div class="wpcdi-required-plugins oe-plugin-installer">
							<?php
							self::required_plugins( $free, 'free' );
							?>
						</div>
					</div>

				</div>

				<a class="wpcdi-button wpcdi-plugins-next" href="#"><?php esc_html_e( 'Go to the next step', 'wpcake-demo-importer' ); ?></a>

			</div>

			<form method="post" id="wpcdi-demo-import-form">

				<input id="wpcdi_import_demo" type="hidden" name="wpcdi_import_demo" value="<?php echo esc_attr( $demo ); ?>" />

				<div class="wpcdi-demo-import-form-types">

					<h2 class="title"><?php esc_html_e( 'Select what you want to import:', 'wpcake-demo-importer' ); ?></h2>

					<ul class="wpcdi-popup-text">
						<li>
							<label for="wpcdi_import_xml">
								<input id="wpcdi_import_xml" type="checkbox" name="wpcdi_import_xml" checked="checked" />
								<strong><?php esc_html_e( 'Import XML Data', 'wpcake-demo-importer' ); ?></strong> (<?php esc_html_e( 'pages, posts, images, menus, etc...', 'wpcake-demo-importer' ); ?>)
							</label>
						</li>

						<li>
							<label for="wpcdi_theme_settings">
								<input id="wpcdi_theme_settings" type="checkbox" name="wpcdi_theme_settings" checked="checked" />
								<strong><?php esc_html_e( 'Import Customizer Settings', 'wpcake-demo-importer' ); ?></strong>
							</label>
						</li>

						<li>
							<label for="wpcdi_import_widgets">
								<input id="wpcdi_import_widgets" type="checkbox" name="wpcdi_import_widgets" checked="checked" />
								<strong><?php esc_html_e( 'Import Widgets', 'wpcake-demo-importer' ); ?></strong>
							</label>
						</li>

					</ul>

				</div>

				<?php wp_nonce_field( 'wpcdi_import_demo_data_nonce', 'wpcdi_import_demo_data_nonce' ); ?>
				<input type="submit" name="submit" class="wpcdi-button wpcdi-import" value="<?php esc_html_e( 'Install this demo', 'wpcake-demo-importer' ); ?>"  />

			</form>

			<div class="wpcdi-loader">
				<h2 class="title"><?php esc_html_e( 'The import process could take some time, please be patient', 'wpcake-demo-importer' ); ?></h2>
				<div class="wpcdi-import-status wpcdi-popup-text"></div>
			</div>

			<div class="wpcdi-last">

				<svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52"><circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"></circle><path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"></path></svg>

				<h3><?php esc_html_e( 'Demo Imported!', 'wpcake-demo-importer' ); ?></h3>

				<a href="<?php echo esc_url( get_home_url() ); ?>" target="_blank"><?php esc_html_e( 'See the result', 'wpcake-demo-importer' ); ?></a>
			</div>

			<?php
			die();
		}

		/**
		 * Required plugins.
		 *
		 */
		public function required_plugins( $plugins, $return ) {

			foreach( $plugins as $key => $plugin ) {

				$api = array(
					'slug' 	=> isset( $plugin['slug'] ) ? $plugin['slug'] : '',
					'init' 	=> isset( $plugin['init'] ) ? $plugin['init'] : '',
					'name' 	=> isset( $plugin['name'] ) ? $plugin['name'] : '',
				);

				if ( ! is_wp_error( $api ) ) { // confirm error free

					// Installed but Inactive.
					if( file_exists( WP_PLUGIN_DIR . '/' . $plugin['init'] ) && is_plugin_inactive( $plugin['init'] ) ) {

						$button_classes = 'button activate-now button-primary';
						$button_text 	= esc_html__( 'Activate', 'wpcake-demo-importer' );

					// Not Installed.
					} elseif( ! file_exists( WP_PLUGIN_DIR . '/' . $plugin['init'] ) ) {

						$button_classes = 'button install-now';
						$button_text 	= esc_html__( 'Install Now', 'wpcake-demo-importer' );

					// Active.
					} else {
						$button_classes = 'button disabled';
						$button_text 	= esc_html__( 'Activated', 'wpcake-demo-importer' );
					} ?>

					<div class="wpcdi-plugin wpcdi-clr wpcdi-plugin-<?php echo esc_attr( $api['slug'] ); ?>" data-slug="<?php echo esc_attr( $api['slug'] ); ?>" data-init="<?php echo esc_attr( $api['init'] ); ?>">

						<h2><?php echo esc_html( $api['name'] ); ?></h2>

						<button class="<?php echo esc_attr( $button_classes ); ?>" data-init="<?php echo esc_attr( $api['init'] ); ?>" data-slug="<?php echo esc_attr( $api['slug'] ); ?>" data-name="<?php echo esc_attr( $api['name'] ); ?>"><?php echo esc_html( $button_text ); ?></button>

					</div>

				<?php
				}
			}

		}

		/**
		 * Required plugins activate
		 *
		 */
		public function ajax_required_plugins_activate() {

			if( ! current_user_can( 'install_plugins' ) || ! isset( $_POST['init'] ) || ! $_POST['init'] ) {
				wp_send_json_error(
					array(
						'success' => false,
						'message' => __( 'No plugin specified', 'wpcake-demo-importer' ),
					)
				);
			}

			$plugin_init = ( isset( $_POST['init'] ) ) ? sanitize_text_field( wp_unslash( $_POST['init'] ) ) : '';
			$activate 	 = activate_plugin( $plugin_init, '', false, true );

			if ( is_wp_error( $activate ) ) {
				wp_send_json_error(
					array(
						'success' => false,
						'message' => $activate->get_error_message(),
					)
				);
			}

			wp_send_json_success(
				array(
					'success' => true,
					'message' => __( 'Plugin Successfully Activated', 'wpcake-demo-importer' ),
				)
			);

		}

		/**
		 * Returns an array containing all the importable content
		 *
		 */
		public function ajax_get_import_data() {

			if( ! current_user_can( 'manage_options' ) ) {
				die( 'This action was stopped for security purposes.' );
			}

			check_ajax_referer( 'wpcdi_import_data_nonce', 'security' );

			echo json_encode(

				array(

					array(
						'input_name' 	=> 'wpcdi_import_xml',
						'action' 		=> 'wpcdi_ajax_import_xml',
						'method' 		=> 'ajax_import_xml',
						'loader' 		=> esc_html__( 'Importing XML Data', 'wpcake-demo-importer' )
					),

					array(
						'input_name' 	=> 'wpcdi_theme_settings',
						'action' 		=> 'wpcdi_ajax_import_theme_settings',
						'method' 		=> 'ajax_import_theme_settings',
						'loader' 		=> esc_html__( 'Importing Customizer Settings', 'wpcake-demo-importer' )
					),

					array(
						'input_name' 	=> 'wpcdi_import_widgets',
						'action' 		=> 'wpcdi_ajax_import_widgets',
						'method' 		=> 'ajax_import_widgets',
						'loader' 		=> esc_html__( 'Importing Widgets', 'wpcake-demo-importer' )
					),
				)
			);

			die();
		}

		/**
		 * Import XML file
		 *
		 */
		public function ajax_import_xml() {

			if ( ! current_user_can('manage_options') || ! wp_verify_nonce( $_POST['wpcdi_import_demo_data_nonce'], 'wpcdi_import_demo_data_nonce' ) ) {
				die( 'This action was stopped for security purposes.' );
			}

			// Get the selected demo
			$demo_type 			= isset( $_POST['wpcdi_import_demo'] ) ? sanitize_text_field( wp_unslash( $_POST['wpcdi_import_demo'] ) ) : '';

			// Get demos data
			$demo 				= WPCDI_Demos::get_demos_data()[ $demo_type ];

			// Content file
			$xml_file 			= isset( $demo['xml_file'] ) ? $demo['xml_file'] : '';

			// Import Posts, Pages, Images, Menus.
			$result = $this->process_xml( $xml_file );

			if ( is_wp_error( $result ) ) {
				echo json_encode( $result->errors );
			} else {
				echo 'successful import';
			}

			die();
		}

		/**
		 * Import XML data
		 *
		 */
		public function process_xml( $file ) {

			$response = WPCDI_Demos_Helpers::get_remote( $file );

			// No sample data found
			if ( $response === false ) {
				return new WP_Error( 'xml_import_error', __( 'Can not retrieve sample data xml file. The server may be down at the moment please try again later. If you still have issues contact the theme developer for assistance.', 'wpcake-demo-importer' ) );
			}

			// Write sample data content to temp xml file
			$temp_xml = WPCDI_PATH .'inc/importers/temp.xml';
			file_put_contents( $temp_xml, $response );

			// Set temp xml to attachment url for use
			$attachment_url = $temp_xml;

			// If file exists lets import it
			if ( file_exists( $attachment_url ) ) {
				$this->import_xml( $attachment_url );
			} else {
				// Import file can't be imported - we should die here since this is core for most people.
				return new WP_Error( 'xml_import_error', __( 'The xml import file could not be accessed. Please try again or contact the theme developer.', 'wpcake-demo-importer' ) );
			}

		}

		/**
		 * Import XML file
		 *
		 */
		private function import_xml( $file ) {

			// Make sure importers constant is defined
			if ( ! defined( 'WP_LOAD_IMPORTERS' ) ) {
				define( 'WP_LOAD_IMPORTERS', true );
			}

			// Import file location
			$import_file = ABSPATH . 'wp-admin/includes/import.php';

			// Include import file
			if ( ! file_exists( $import_file ) ) {
				return;
			}

			// Include import file
			require_once( $import_file );

			// Define error var
			$importer_error = false;

			if ( ! class_exists( 'WP_Importer' ) ) {
				$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';

				if ( file_exists( $class_wp_importer ) ) {
					require_once $class_wp_importer;
				} else {
					$importer_error = __( 'Can not retrieve class-wp-importer.php', 'wpcake-demo-importer' );
				}
			}

			if ( ! class_exists( 'WP_Import' ) ) {
				$class_wp_import = WPCDI_PATH . 'inc/importers/class-wordpress-importer.php';

				if ( file_exists( $class_wp_import ) ) {
					require_once $class_wp_import;
				} else {
					$importer_error = __( 'Can not retrieve wordpress-importer.php', 'wpcake-demo-importer' );
				}
			}

			// Display error
			if ( $importer_error ) {
				return new WP_Error( 'xml_import_error', $importer_error );
			} else {

				// No error, lets import things...
				if ( ! is_file( $file ) ) {
					$importer_error = __( 'Sample data file appears corrupt or can not be accessed.', 'wpcake-demo-importer' );
					return new WP_Error( 'xml_import_error', $importer_error );
				} else {
					$importer = new WP_Import();
					$importer->fetch_attachments = true;
					add_filter( 'upload_mimes', array( $this, 'allow_svg_mime_types' ) );
					$importer->import( $file );

					// Clear sample data content from temp xml file
					$temp_xml = WPCDI_PATH .'inc/importers/temp.xml';
					file_put_contents( $temp_xml, '' );
				}
			}
		}

		/**
		 * [allow_svg_mime_types description]
		 * @return [type] [description]
		 */
		public function allow_svg_mime_types( $mimes ) {
			$mimes['svg'] = 'image/svg+xml';
 			return $mimes;
		}

		/**
		 * Import customizer settings
		 *
		 */
		public function ajax_import_theme_settings() {

			if( ! current_user_can('manage_options') || ! wp_verify_nonce( $_POST['wpcdi_import_demo_data_nonce'], 'wpcdi_import_demo_data_nonce' ) ) {
				die( 'This action was stopped for security purposes.' );
			}

			// Include settings importer
			include WPCDI_PATH . 'inc/importers/class-settings-importer.php';

			// Get the selected demo
			$demo_type 			= isset( $_POST['wpcdi_import_demo'] ) ? sanitize_text_field( wp_unslash( $_POST['wpcdi_import_demo'] ) ) : '';

			// Get demos data
			$demo 				= WPCDI_Demos::get_demos_data()[ $demo_type ];

			// Settings file
			$theme_settings 	= isset( $demo['theme_settings'] ) ? $demo['theme_settings'] : '';

			// Import settings.
			$settings_importer = new WPCDI_Settings_Importer();
			$result = $settings_importer->process_import_file( $theme_settings );

			if ( is_wp_error( $result ) ) {
				echo json_encode( $result->errors );
			} else {
				echo 'successful import';
			}

			die();
		}

		/**
		 * Import widgets
		 *
		 */
		public function ajax_import_widgets() {

			if( ! current_user_can('manage_options') || ! wp_verify_nonce( $_POST['wpcdi_import_demo_data_nonce'], 'wpcdi_import_demo_data_nonce' ) ) {
				die( 'This action was stopped for security purposes.' );
			}

			// Include widget importer
			include WPCDI_PATH . 'inc/importers/class-widget-importer.php';

			// Get the selected demo
			$demo_type 			= isset( $_POST['wpcdi_import_demo'] ) ? sanitize_text_field( wp_unslash( $_POST['wpcdi_import_demo'] ) ) : '';

			// Get demos data
			$demo 				= WPCDI_Demos::get_demos_data()[ $demo_type ];

			// Widgets file
			$widgets_file 		= isset( $demo['widgets_file'] ) ? $demo['widgets_file'] : '';

			// Import settings.
			$widgets_importer = new WPCDI_Widget_Importer();
			$result = $widgets_importer->process_import_file( $widgets_file );

			if ( is_wp_error( $result ) ) {
				echo json_encode( $result->errors );
			} else {
				echo 'successful import';
			}

			die();
		}

		/**
		 * After import
		 *
		 */
		public function ajax_after_import() {

			if ( ! current_user_can('manage_options') || ! wp_verify_nonce( $_POST['wpcdi_import_demo_data_nonce'], 'wpcdi_import_demo_data_nonce' ) ) {
				die( 'This action was stopped for security purposes.' );
			}

			// If XML file is imported
			if ( $_POST['wpcdi_import_is_xml'] === 'true' ) {

				// Get the selected demo
				$demo_type = isset( $_POST['wpcdi_import_demo'] ) ? sanitize_text_field( wp_unslash( $_POST['wpcdi_import_demo'] ) ) : '';

				// Get demos data
				$demo = WPCDI_Demos::get_demos_data()[ $demo_type ];

				// front_is 'page' handle
				if( isset( $demo['front_is'] ) && $demo['front_is'] == 'page' ) {

					$home_page = get_page_by_title( 'Home' );
					$blog_page = get_page_by_title( 'Blog' );

					update_option( 'show_on_front', 'page' );

					if ( is_object( $home_page ) ) {
						update_option( 'page_on_front', $home_page->ID );
					}

					if ( is_object( $blog_page ) ) {
						update_option( 'page_for_posts', $blog_page->ID );
					}

				}

				// is_shop 'yes' handle
				if( class_exists( 'WooCommerce' ) && isset( $demo['is_shop'] ) && $demo['is_shop'] == 'yes' ) {

					$woopages = array(
						'woocommerce_shop_page_id' 				=> 'shop',
						'woocommerce_cart_page_id' 				=> 'cart',
						'woocommerce_checkout_page_id' 			=> 'checkout',
						'woocommerce_myaccount_page_id' 		=> 'my-account',
					);

					foreach( $woopages as $woo_page_name => $woo_page_slug ) {

						$woopage = get_page_by_path( $woo_page_slug );
						if( isset( $woopage ) && $woopage->ID ) {
							update_option( $woo_page_name, $woopage->ID );
						}
					}

					// We no longer need to install pages
					delete_option( '_wc_needs_pages' );
					delete_transient( '_wc_activation_redirect' );

					// Make sure TI WooCommerce Wishlist Plugin is in action
					if( defined( 'TINVWL_URL' ) ) {

						// find and set wishlist page
						$wishlist_page = get_page_by_path( 'wishlist' );
						if( isset( $wishlist_page ) && $wishlist_page->ID ) {
							// set the page
							update_option( 'tinvwl-page', array( 'wishlist' => $wishlist_page->ID ) );
						}

					}

				}

				// Set imported menus to registered theme locations
				$locations 	= get_theme_mod( 'nav_menu_locations' );
				$menus 		= wp_get_nav_menus();

				if( $menus ) {
					foreach( $menus as $menu ) {
						if( $menu->name == 'main' ) {
							//set main menu to primary menu location
							$locations['primary-menu'] = $menu->term_id;
						} elseif( $menu->name == 'topbar' ) {
							//set topbar to topbar menu location
							$locations['topbar'] = $menu->term_id;
						}
					}
				}

				set_theme_mod( 'nav_menu_locations', $locations );

				// Enable Elementor FA 4 Support
				update_option( 'elementor_load_fa4_shim', 'yes' );

			}

			die();
		}

		/**
		 * [plugin_action_links description]
		 * @param  [type] $actions [description]
		 * @return [type]          [description]
		 */
		public function plugin_action_links( $actions ) {
			$custom_link = array(
				'configure' => sprintf( '<a href="%s">%s</a>', admin_url() . 'themes.php?page=wpcdi-panel-install-demos', __( 'Open Demos', 'wpcake-demo-importer' ) ),
				);
			return array_merge( $custom_link, $actions );
		}
	}
}
new WPCDI_Demos();
