<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Start Class
class WPCDI_Install_Demos {

	/**
	 * Start things up
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_page' ), 999 );
	}

	/**
	 * Add sub menu page for the custom CSS input
	 *
	 */
	public function add_page() {

		add_submenu_page(
			'themes.php',
			esc_html__( 'WPCake Demos', 'wpcake-demo-importer' ),
			esc_html__( 'WPCake Demos', 'wpcake-demo-importer' ),
			'manage_options',
			'wpcdi-panel-install-demos',
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Settings page output
	 *
	 */
	public function create_admin_page() {

		?>

		<div class="wpcdi-demo-wrap wrap">

			<h2><?php esc_html_e( 'WPCake - Install a demo site', 'wpcake-demo-importer' ); ?></h2>

			<div class="theme-browser rendered">

				<?php
				// Vars
				$demos = WPCDI_Demos::get_demos_data();
				$categories = WPCDI_Demos::get_demo_all_categories( $demos ); ?>

				<?php if ( ! empty( $categories ) ) : ?>
					<div class="wpcdi-header-bar">
						<nav class="wpcdi-navigation">
							<ul>
								<li class="active"><a href="#all" class="wpcdi-navigation-link"><?php esc_html_e( 'All', 'wpcake-demo-importer' ); ?></a></li>
								<?php foreach ( $categories as $key => $name ) : ?>
									<li><a href="#<?php echo esc_attr( $key ); ?>" class="wpcdi-navigation-link"><?php echo esc_html( $name ); ?></a></li>
								<?php endforeach; ?>
							</ul>
						</nav>
						<div clas="wpcdi-search">
							<input type="text" class="wpcdi-search-input" name="wpcdi-search" value="" placeholder="<?php esc_html_e( 'Search demos...', 'wpcake-demo-importer' ); ?>">
						</div>
					</div>
				<?php endif; ?>

				<div class="themes wp-clearfix">

					<?php
					// Loop through all demos
					foreach ( $demos as $demo => $key ) {

						// Vars
						$item_categories = WPCDI_Demos::get_demo_item_categories( $key );	?>

						<div class="theme-wrap" data-categories="<?php echo esc_attr( $item_categories ); ?>" data-name="<?php echo esc_attr( strtolower( $demo ) ); ?>">

							<div class="theme wpcdi-open-popup" data-demo-id="<?php echo esc_attr( $demo ); ?>">

								<div class="theme-screenshot">
									<img src="<?php echo esc_url( WPCDI_URL ); ?>assets/images/<?php echo esc_attr( strtolower( $demo ) ); ?>.jpg" />

									<div class="demo-import-loader preview-all preview-all-<?php echo esc_attr( $demo ); ?>"></div>

									<div class="demo-import-loader preview-icon preview-<?php echo esc_attr( $demo ); ?>"><i class="custom-loader"></i></div>
								</div>

								<div class="theme-id-container">

									<h2 class="theme-name" id="<?php echo esc_attr( $demo ); ?>"><span><?php echo ucwords( $key['title'] ); ?></span></h2>

									<div class="theme-actions">
										<a class="button button-primary" href="https://wpcakedemos.com/<?php echo esc_attr( strtolower( $demo ) ); ?>/" target="_blank"><?php _e( 'Live Preview', 'wpcake-demo-importer' ); ?></a>
									</div>

								</div>

								<?php $demo_cost = ($key['is_free']) ? __('Free', 'wpcake-demo-importer') : __('Premium', 'wpcake-demo-importer'); ?>

								<div class="demo-class-container <?php echo esc_attr(strtolower( $demo_cost )); ?>">
									<span><?php echo esc_html($demo_cost);?></span>
								</div>

							</div>

						</div>

					<?php } ?>

				</div>

			</div>

		</div>

	<?php }
}
new WPCDI_Install_Demos();
