<?php
/**
 * Class: Mini_Cart_Module
 * Name: Woocommerce Mini Cart
 * PA WooCommerce Modules.
 *
 * @package PA
 */

namespace PremiumAddons\Modules\Woocommerce\Modules;

use PremiumAddons\Includes\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // If this file is called directly, abort.
}

/**
 * Class Mini Cart Module.
 */
class Mini_Cart_Module extends Module_Base {

	/**
	 * Instance variable
	 *
	 * @var $instance.
	 */
	private static $instance = null;

	/**
	 * Instance.
	 *
	 * @return object self::$instance
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get Module Name.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @return string Module name.
	 */
	public function get_name() {
		return 'woocommerce-mini-cart';
	}

	/**
	 * Constructor.
	 *
	 * @since 4.7.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		if ( ! defined( 'ELEMENTOR_PRO_VERSION' ) ) {
			add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'pa_maybe_init_cart' ) );
		}

		add_action( 'wp_ajax_pa_update_mc_qty', array( $this, 'pa_update_mc_qty' ) );
		add_action( 'wp_ajax_nopriv_pa_update_mc_qty', array( $this, 'pa_update_mc_qty' ) );

		add_action( 'wp_ajax_pa_delete_cart_item', array( $this, 'pa_delete_cart_item' ) );
		add_action( 'wp_ajax_nopriv_pa_delete_cart_item', array( $this, 'pa_delete_cart_item' ) );

		$enabled_keys = get_option( 'pa_save_settings', array() );

		$mc_custom_temp_enabled = isset( $enabled_keys['pa_mc_temp'] ) ? $enabled_keys['pa_mc_temp'] : false;

		if ( $mc_custom_temp_enabled ) {
			add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'pa_add_mini_cart_fragments' ) );
		}
	}

	public function pa_maybe_init_cart() {
		$has_cart = is_a( WC()->cart, 'WC_Cart' );

		if ( ! $has_cart ) {
			$session_class = apply_filters( 'woocommerce_session_handler', 'WC_Session_Handler' );
			WC()->session  = new $session_class();
			WC()->session->init();
			WC()->cart     = new \WC_Cart();
			WC()->customer = new \WC_Customer( get_current_user_id(), true );
		}
	}

	public function pa_add_mini_cart_fragments( $fragments ) {

		$product_count = WC()->cart->get_cart_contents_count();

		$fragments['.pa-woo-mc__outer-container .pa-woo-mc__badge']                             = '<span class="pa-woo-mc__badge">' . $product_count . '</span>';
		$fragments['.pa-woo-mc__cart-footer .pa-woo-mc__cart-count']                            = '<span class="pa-woo-mc__cart-count">' . $product_count . '</span>';
		$fragments['.pa-woo-mc__outer-container .pa-woo-mc__text-wrapper .pa-woo-mc__subtotal'] = '<span class="pa-woo-mc__subtotal">' . WC()->cart->get_cart_subtotal() . '</span>';
		$fragments['.pa-woo-mc__cart-footer .pa-woo-mc__subtotal']                              = '<span class="pa-woo-mc__subtotal">' . WC()->cart->get_cart_subtotal() . '</span>';

		return $fragments;
	}

	public function pa_update_mc_qty() {

		check_ajax_referer( 'pa-mini-cart-nonce', 'nonce' );

		if ( ! isset( $_POST['itemKey'] ) || ! isset( $_POST['quantity'] ) ) {
			return;
		}

		$item_key = sanitize_text_field( $_POST['itemKey'] );
		$quantity = absint( $_POST['quantity'] );

		if ( $quantity > 0 && WC()->cart->get_cart_item( $_POST['itemKey'] ) ) {
			WC()->cart->set_quantity( $_POST['itemKey'], $_POST['quantity'], true );
		}

		\WC_AJAX::get_refreshed_fragments();

		wp_send_json_success();
	}

	public function pa_delete_cart_item() {

		check_ajax_referer( 'pa-mini-cart-nonce', 'nonce' );

		if ( ! isset( $_POST['itemKey'] ) ) {
			return;
		}

		$item_key = sanitize_text_field( $_POST['itemKey'] );

		if ( WC()->cart->get_cart_item( $_POST['itemKey'] ) ) {
			WC()->cart->remove_cart_item( $_POST['itemKey'] );
		}

		\WC_AJAX::get_refreshed_fragments();

		wp_send_json_success();
	}
}
