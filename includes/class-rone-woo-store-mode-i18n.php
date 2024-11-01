<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://remedyone.com
 * @since      1.0.0
 *
 * @package    rone_woo_store_mode
 * @subpackage rone_woo_store_mode/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    rone_woo_store_mode
 * @subpackage rone_woo_store_mode/includes
 * @author     Simon Hunter <simon@remedyone.come>
 */
class rone_woo_store_mode_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'rone-woo-store-mode',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
