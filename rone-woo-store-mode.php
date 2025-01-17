<?php

/**
 *
 * @link              http://remedyone.com
 * @since             1.0.0
 * @package           rone_woo_store_mode
 *
 * @wordpress-plugin
 * Plugin Name:       Woo Store Mode
 * Description:       Adds restaurant-style opening and closing hours functionality to Woocommerce stores.
 * Version:           1.0.1
 * Author:            RemedyOne
 * Author URI:        https://www.remedyone.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       rone-woo-store-mode
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'RWSM_PLUGIN_VERSION', '1.0.1' );

define( 'RWSM_PLUGIN_NAME', 'rone-woo-store-mode' );

/**
 * Activate Plugin 
 */
function activate_rone_woo_store_mode() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-rone-woo-store-mode-activator.php';
	rone_woo_store_mode_Activator::activate();
}

/**
 * Deactivation Hook 
 */
function deactivate_rone_woo_store_mode() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-rone-woo-store-mode-deactivator.php';
	rone_woo_store_mode_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_rone_woo_store_mode' );
register_deactivation_hook( __FILE__, 'deactivate_rone_woo_store_mode' );


if( ! class_exists( 'Rone_Woo_Store' ) ) {

	class Rone_Woo_Store {

		protected static $instance = null;

		public static function get_instance() {
			if( null == self::$instance ) {
				self::$instance = new self;
			} 
			return self::$instance;
		}

		/**	
		 * Constructor 
		 */ 
		protected function __construct() {

			if( ! class_exists( 'WooCommerce' ) ) {
				add_action('admin_notices', array( $this, 'fallback_notice' ) );
			} else {
				
				// Load Plugin Core 
				require plugin_dir_path( __FILE__ ) . 'includes/class-rone-woo-store-mode.php';

				// Execute Plugin 
				run_rone_woo_store_mode(); 
				
			}

		}

		public function fallback_notice() {
			echo '<div class="error">';
			echo '<p>' . __( 'Woo Store Mode: Needs the WooCommerce Plugin activated.', 'rone-woo-store-mode' ) . '</p>';
			echo '</div>';
		}
	}

}


/**
* Initialize the plugin.
*/
add_action( 'plugins_loaded', array( 'Rone_Woo_Store', 'get_instance' ) );


/**
 * Begins execution of the plugin only if WooCommerce is active.
 *
 * @since    1.0.0
 */
function run_rone_woo_store_mode() {
	$plugin = new rone_woo_store_mode();
	$plugin->run();
}