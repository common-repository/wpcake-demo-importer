<?php
/**
 * Plugin Name:	WPCake Demo Importer
 * Description:	Import starter sites that have been carefully designed for use with the Free WordPress WPCake theme.
 * Version:		1.0.3
 * Author:		WPCake
 * Author URI:	https://www.wpcake.com
 * License:		GPLv2 or later
 * License URI:	http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wpcake-demo-importer
 * Domain Path: /languages
 *
 */

// Exit if directly accessed files.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define constants.

define( 'WPCDI_VERSION' , '1.0.3' );

define( 'WPCDI_FILE', __FILE__ );
define( 'WPCDI_PATH', wp_normalize_path( plugin_dir_path( WPCDI_FILE ) ) );

define( 'WPCDI_URL', plugin_dir_url( WPCDI_FILE ) );

define( 'WPCDI_BASENAME', plugin_basename( WPCDI_FILE ) );
define( 'WPCDI_DIR_NAME', dirname( WPCDI_BASENAME ) );

// Make sure WPCake or it's child theme is active.
if( wp_get_theme()->Template == 'wpcake' ) {

	// Load text domain.
	add_action( 'init', 'wpcdi_load_plugin_textdomain' );
	function wpcdi_load_plugin_textdomain() {
		load_plugin_textdomain( 'wpcake-demo-importer', false, WPCDI_PATH . 'languages' );
	}

	// Load the init file.
	require( WPCDI_PATH . 'inc/init.php' );

}
