<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://remedyone.com
 * @since      1.0.0
 *
 * @package    rone_woo_store_mode
 * @subpackage rone_woo_store_mode/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    rone_woo_store_mode
 * @subpackage rone_woo_store_mode/public
 * @author     Simon Hunter <simon@remedyone.come>
 */
class rone_woo_store_mode_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	public $options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->options = rone_get_options();

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/rone-woo-store-mode-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/rone-woo-store-mode-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Display Store Close Notice on Checkout Page
	 * @since    1.0.0
	 */
	public function checkout( $content ) {
		
		$options = $this->options;
		
		$checkoutpage = get_option('woocommerce_checkout_page_id');
		
		if( is_page( $checkoutpage ) && ! rone_is_store_open() ){
			$content = do_shortcode( $options['Rone_Woo_store_checkout_content'] );
		}

		return $content;
	}

	public function shortcode( $atts, $content = null ){

		$options = $this->options;

		if( ! isset( $options['modes'] ) ) {
			return; 
		}

		$modes = array();

		foreach ($options['modes'] as $key => $value) {
			if( $value['status'] != 1 ) {
				continue;
			}
			$modes[] = $key;
		}

		// Return if all modes are deactive 
		if( empty( $modes ) ) {
			return; 
		}

		$session = $this->save_session_mode();
		if( ! $session ) {
			$session = $this->get_saved_session_mode();
		}
		if( ! $session ) {
			$session = rone_get_current_mode();
		}

		$store_status = rone_get_current_mode();

		// Start Building Shortcode HTML Output 
		$html = "<ul class='store-modes'>";
		foreach( $modes as $mode ) {
			$url = rone_get_mode_url( $mode );
			$count = $this->count( $mode );
			$count = ( $count ) ? "($count)" : "";
			$active = ( $session == $mode ) ? 'class="active-mode"' : '';
			$status = ( $store_status == $mode ) ? 'Open' : 'Closed';
			$html .= "<li $active><a href='$url'>".ucfirst( $mode )." $count $status</a></li>";
		}
		$html .= "</ul>";

		return $html; 

	}

	public function count( $mode ) {
		$term = get_term_by( 'slug', $mode, 'rone-mode' );
		return $term->count;
	}

	public function save_session_mode() {
		
		if( ! isset( $_GET['rone_change_mode'] ) ) {
			return;
		}

		$session = new WC_Session_Handler();

		$session->set( 'rone-store-mode', $_GET['mode'] );

		return $session->get('rone-store-mode' );

	}

	public function get_saved_session_mode() {
		$session = new WC_Session_Handler();
		return $session->get('rone-store-mode' );
	}

	public function rone_mode_after_shop_loop_item() {
		echo get_the_term_list( get_the_ID(), 'rone-mode' );
	}

	public function current_mode_products( $query ) {
		
		if( ! isset( $query->query['post_type'] ) ) {
			return;
		}
		if( $query->query['post_type'] != 'product' ) {
			return; 
		}

		$session_mode = $this->get_saved_session_mode();

		if( isset( $_GET['mode'] ) && ! empty( $_GET['mode'] ) ) {
			$session_mode = $_GET['mode'];
		} elseif( ! $session_mode ) {
			$session_mode = rone_get_current_mode();
		}

		if( $this->is_mode_active( $session_mode ) != 1 ) {
			return; 
		}

		if( ! is_admin() && $query->is_main_query() ) {
			$query->set('tax_query', array( array(
				'taxonomy' => 'rone-mode',
				'field' => 'slug', 
				'terms' => $session_mode
			) ) );
		}
	}

	public function is_mode_active( $mode ) {
		
		$active = false;
		if( ! isset( $this->options['modes'][$mode] ) ) {
			wp_safe_redirect( home_url() );
			exit;
		}

		if( $this->options['modes'][$mode]['status'] == 1 ) {
			$active = true;
		}

		return $active; 
	}

	public function filter() {

		echo do_shortcode( '[wsm-store-mode-change]' );

		if( ! rone_is_store_open() ) {
			echo "<p>";
				_e( 'Store is closed for checkout.', 'rone-woo-store-mode' );
			echo "</p>";
		}

		$mode = $this->get_saved_session_mode();

		if( isset( $_GET['mode'] )  && ! empty( $_GET['mode'] ) ) {
			$mode = $_GET['mode'];
		}

		if( $mode ) {
			echo "<p>Currently displaying products from ".ucfirst( $mode ).".</p>";
		}

		if( ! $mode && rone_get_current_mode() ) {
			echo "<p>Currently displaying products from ".ucfirst( rone_get_current_mode() ).".</p>";
		}

	}

}