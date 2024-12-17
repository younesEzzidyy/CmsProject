<?php
/**
 * Class: Products_Module
 * Name: Woocommerce Products
 * Slug: premium-woocommerce-products
 * PA WooCommerce Module.
 *
 * @package PA
 */

namespace PremiumAddons\Modules\Woocommerce\Modules;

use Elementor\Plugin;
use PremiumAddons\Includes\Module_Base;
use PremiumAddons\Modules\Woocommerce\TemplateBlocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // If this file is called directly, abort.
}

/**
 * Class Product Module.
 */
class Products_Module extends Module_Base {


	/**
	 * Instance variable
	 *
	 * @var $instance.
	 */
	private static $instance = null;

	/**
	 * Instance
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
		return 'woocommerce-products';
	}

	/**
	 * Constructor.
	 *
	 * @since 4.7.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		// Trigger AJAX Hooks for pagination.
		add_action( 'wp_ajax_get_woo_products', array( $this, 'get_woo_products' ) );
		add_action( 'wp_ajax_nopriv_get_woo_products', array( $this, 'get_woo_products' ) );

		// Trigger AJAX Hooks for product view.
		add_action( 'wp_ajax_get_woo_product_qv', array( $this, 'get_woo_product_quick_view' ) );
		add_action( 'wp_ajax_nopriv_get_woo_product_qv', array( $this, 'get_woo_product_quick_view' ) );

		// Trigger AJAX Hooks for add to cart.
		add_action( 'wp_ajax_premium_woo_add_cart_product', array( $this, 'add_product_to_cart' ) );
		add_action( 'wp_ajax_nopriv_premium_woo_add_cart_product', array( $this, 'add_product_to_cart' ) );
	}

	/**
	 * Get Woo Products.
	 *
	 * @access public
	 * @since 4.7.0
	 */
	public function get_woo_products() {

		check_ajax_referer( 'pa-woo-products-nonce', 'nonce' );

		if ( ! isset( $_POST['pageID'] ) || ! isset( $_POST['elemID'] ) || ! isset( $_POST['skin'] ) ) {
			return;
		}

		$post_id   = sanitize_text_field( wp_unslash( $_POST['pageID'] ) );
		$widget_id = sanitize_text_field( wp_unslash( $_POST['elemID'] ) );
		$style_id  = sanitize_text_field( wp_unslash( $_POST['skin'] ) );

		$elementor = Plugin::$instance;
		$meta      = $elementor->documents->get( $post_id )->get_elements_data();

		$widget_data = $this->find_element_recursive( $meta, $widget_id );

		$data = array(
			'message'    => __( 'Saved', 'premium-addons-for-elementor' ),
			'ID'         => '',
			'skin_id'    => '',
			'html'       => '',
			'pagination' => '',
		);

		if ( null !== $widget_data ) {

			// Restore default values.
			$widget = $elementor->elements_manager->create_element_instance( $widget_data );

			// Return data and call your function according to your need for ajax call.
			// You will have access to settings variable as well as some widget functions.
			$skin = TemplateBlocks\Skin_Init::get_instance( $style_id );

			// Here you will just need posts based on ajax requst to attache in layout.
			$html = $skin->inner_render( $style_id, $widget, true );

			$pagination = $skin->page_render( $style_id, $widget );

			$data['ID']         = $widget->get_id();
			$data['skin_id']    = $widget->get_current_skin_id();
			$data['html']       = $html;
			$data['pagination'] = $pagination;
		}

		wp_send_json_success( $data );
	}

	/**
	 * Find Element Recursive.
	 *
	 * @access public
	 * @since 4.7.0
	 *
	 * @param array $elements  elements.
	 * @param int   $elem_id     element id.
	 *
	 * @return object|boolean
	 */
	public function find_element_recursive( $elements, $elem_id ) {

		foreach ( $elements as $element ) {
			if ( $elem_id === $element['id'] ) {
				return $element;
			}

			if ( ! empty( $element['elements'] ) ) {
				$element = $this->find_element_recursive( $element['elements'], $elem_id );

				if ( $element ) {
					return $element;
				}
			}
		}

		return false;
	}


	/**
	 * Get Woo Products View.
	 *
	 * @access public
	 * @since 4.7.0
	 */
	public function get_woo_product_quick_view() {

		check_ajax_referer( 'pa-woo-qv-nonce', 'security' );

		if ( ! isset( $_REQUEST['product_id'] ) ) {
			die();
		}

		$post_id   = isset( $_POST['pageID'] ) ? sanitize_text_field( wp_unslash( $_POST['pageID'] ) ) : 0;
		$widget_id = isset( $_POST['elemID'] ) ? sanitize_text_field( wp_unslash( $_POST['elemID'] ) ) : 0;

		$elementor = Plugin::$instance;
		$meta      = $elementor->documents->get( $post_id )->get_elements_data();

		$widget_data = $this->find_element_recursive( $meta, $widget_id );

		if ( null === $widget_data ) {

			wp_send_json_error( 'Widget settings not found.' );

		}

		// Restore default values.
		$widget = $elementor->elements_manager->create_element_instance( $widget_data );

		$settings = $widget->get_settings();

		$this->quick_view_content_actions();

		$product_id = intval( $_REQUEST['product_id'] );

		// set the main wp query for the product.
		wp( 'p=' . $product_id . '&post_type=product' );

		ob_start();

		// load content template.
		include PREMIUM_ADDONS_PATH . 'modules/woocommerce/templates/quick-view-product.php';

		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		die();
	}

	/**
	 * Quick View Content Actions.
	 *
	 * @access public
	 * @since 4.7.0
	 */
	public function quick_view_content_actions() {
		// Image.
		add_action( 'premium_woo_qv_image', array( $this, 'product_quick_view_image_content' ), 20 );
		// Summary.
		add_action( 'premium_woo_quick_view_product', array( $this, 'product_quick_view_content' ), 10 );
	}

	/**
	 * Product Quick View Image Content.
	 * Include qv image.
	 *
	 * @access public
	 * @since 4.7.0
	 */
	public function product_quick_view_image_content() {

		include PREMIUM_ADDONS_PATH . 'modules/woocommerce/templates/quick-view-product-image.php';
	}

	/**
	 * Add Product To Cart.
	 * Adds product to cart.
	 *
	 * @access public
	 * @since 4.7.0
	 */
	public function add_product_to_cart() {

		check_ajax_referer( 'pa-woo-cta-nonce', 'nonce' );

		$product_id   = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : 0;
		$variation_id = isset( $_POST['variation_id'] ) ? sanitize_text_field( wp_unslash( $_POST['variation_id'] ) ) : 0;
		$quantity     = isset( $_POST['quantity'] ) ? sanitize_text_field( wp_unslash( $_POST['quantity'] ) ) : 0;

		if ( $variation_id ) {
			WC()->cart->add_to_cart( $product_id, $quantity, $variation_id );
		} else {
			WC()->cart->add_to_cart( $product_id, $quantity );
		}
		die();
	}

	/**
	 * Product Quick View Content.
	 * Gets product quick view content.
	 *
	 * @param array $settings   settings.
	 *
	 * @since 4.7.0
	 */
	public function product_quick_view_content( $settings ) {

		global $product;

		$post_id = $product->get_id();

		$single_structure = apply_filters(
			'premium_woo_qv_structure',
			array(
				'title',
				'ratings',
				'price',
				'short_desc',
				'add_cart',
				'meta',
			)
		);

		if ( is_array( $single_structure ) && ! empty( $single_structure ) ) {

			foreach ( $single_structure as $value ) {

				switch ( true ) {
					case 'title' === $value:
						echo '<a href="' . esc_url( apply_filters( 'premium_woo_product_title_link', get_the_permalink() ) ) . '" class="premium-woo-product__link">';
							woocommerce_template_loop_product_title();
						echo '</a>';
						break;
					case 'price' === $value:
						woocommerce_template_single_price();
						break;
					case 'ratings' === $value:
						woocommerce_template_loop_rating();
						break;
					case 'short_desc' === $value:
						echo '<div class="premium-woo-qv-desc">';
							woocommerce_template_single_excerpt();
						echo '</div>';
						break;
					case 'add_cart' === $value:
							$attributes = count( $product->get_attributes() ) > 0 ? 'data-variations="true"' : '';
							echo '<div class="premium-woo-atc-button" ' . esc_attr( $attributes ) . '>';
								woocommerce_template_single_add_to_cart();
							echo '</div>';
						break;
					case 'meta' === $value:
						$attributes = count( $product->get_attributes() ) > 0 ? 'data-variations="true"' : '';
						echo '<div class="premium-woo-qv-meta">';
							woocommerce_template_single_meta();
						echo '</div>';
						break;

					default:
						break;
				}
			}
		}
	}
}
