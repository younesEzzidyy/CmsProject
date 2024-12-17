<?php
/**
 * Class: CTA_Module
 * Name: Woocommerce CTA
 * Slug: premium-woocommerce-cta
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
 * Class CTA Module.
 */
class CTA_Module extends Module_Base {

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
		return 'woocommerce-cta';
	}

	/**
	 * Constructor.
	 *
	 * @since 4.7.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'wp_ajax_handle_pa_woo_cta_actions', array( $this, 'handle_pa_woo_cta_actions' ) );
		add_action( 'wp_ajax_nopriv_handle_pa_woo_cta_actions', array( $this, 'handle_pa_woo_cta_actions' ) );
	}

	/**
	 * Find matching product variation by product id and attribute.
	 *
	 * @access public
	 * @since 4.10.45
	 *
	 * @param number $product_id   product id.
	 * @param array  $attributes   attributes.
	 */
	public function find_matching_product_variation_id( $product_id, $attributes ) {

		return ( new \WC_Product_Data_Store_CPT() )->find_matching_product_variation(
			new \WC_Product( $product_id ),
			$attributes
		);
	}

	/**
	 * Handle Actions for button in Woo CTA.
	 *
	 * @access public
	 * @since 4.10.45
	 */
	public function handle_pa_woo_cta_actions() {

		check_ajax_referer( 'pa-woo-cta-nonce', 'security' );

		$product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;

		if ( ! $product_id ) {
			wp_send_json_error( 'Product ID is missing.' );
			return;
		}

		$ajax_action = isset( $_POST['ajaxAction'] ) ? sanitize_text_field( wp_unslash( $_POST['ajaxAction'] ) ) : '';
		$quantity    = isset( $_POST['quantity'] ) ? intval( $_POST['quantity'] ) : 1;
		$attributes  = isset( $_POST['attributes'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['attributes'] ) ) : array();

		if ( 'custom_add_to_cart' === $ajax_action ) {
			// Handle Add to Cart logic.
			$this->custom_add_to_cart( $product_id );
		} elseif ( 'custom_add_to_wishlist' === $ajax_action ) {
			// Handle Add to Wishlist logic.
			$this->custom_add_to_wishlist( $product_id );
		} else {
			$this->custom_add_to_compare( $product_id );
		}
	}

	/**
	 * Custom Add To Cart for button in Woo CTA.
	 *
	 * @param number $product_id   product id.
	 * @access public
	 * @since 4.10.45
	 */
	public function custom_add_to_cart( $product_id ) {

		check_ajax_referer( 'pa-woo-cta-nonce', 'security' );

		if ( ! isset( $_POST['product_id'] ) ) {
			wp_send_json_error( array( 'message' => 'Product ID is missing.' ) );
			return;
		}

		$product_status = get_post_status( $product_id );
		$product        = wc_get_product( $product_id );

		if ( ! $product ) {
			wp_send_json_error( array( 'message' => 'Invalid product ID.' ) );
			return;
		}

		if ( 'publish' !== $product_status ) {
			wp_send_json_error( array( 'message' => 'Product is not available.' ) );
			return;
		}

		if ( $product->is_type( 'grouped' ) ) {
			// Handle grouped product.
			$quantities = isset( $_POST['grouped_product_qty'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['grouped_product_qty'] ) ) : array();

			if ( empty( $quantities ) ) {
				wp_send_json_error( array( 'message' => 'No quantities specified for grouped products.' ) );
				return;
			}

			foreach ( $quantities as $child_id => $quantity ) {
				$child_id = intval( $child_id );
				$quantity = intval( $quantity );

				if ( $quantity > 0 ) {
					$child_product = wc_get_product( $child_id );

					if ( $child_product && $child_product->is_in_stock() ) {
						$stock_quantity = $child_product->get_stock_quantity();

						if ( ( $quantity < $stock_quantity ) || ( null === $stock_quantity ) ) {
							WC()->cart->add_to_cart( $child_id, $quantity );
						} else {
							wp_send_json_error( array( 'qty_message' => 'The maximum available quantity for ' . $child_product->get_name() . ' is ' . $stock_quantity . '.' ) );
						}
					} else {
						wp_send_json_error( array( 'message' => 'Product ' . $child_product->get_name() . ' is out of stock.' ) );
						return;
					}
				}
			}

			wp_send_json_success(
				array(
					'cart_url' => wc_get_cart_url(),
					'message'  => 'Grouped products successfully added to the cart.',
				)
			);

		} elseif ( $product->is_type( 'variable' ) ) {
			// Handle variable product.
			$quantity     = isset( $_POST['quantity'] ) ? intval( $_POST['quantity'] ) : 1;
			$attributes   = isset( $_POST['attributes'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['attributes'] ) ) : array();
			$variation_id = $this->find_matching_product_variation_id( $product_id, $attributes );

			if ( $variation_id ) {
				$variation = wc_get_product( $variation_id );
				if ( ! $variation->is_in_stock() ) {
					wp_send_json_error( array( 'message' => 'Selected variation is out of stock.' ) );
					return;
				}

				$stock_quantity = $variation->get_stock_quantity();

				if ( ( $quantity < $stock_quantity ) || ( null === $stock_quantity ) ) {

					$added = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $attributes );
					if ( $added ) {
						wp_send_json_success(
							array(
								'cart_url' => wc_get_cart_url(),
								'message'  => 'Product successfully added to the cart.',
							)
						);
					} else {
						wp_send_json_error( array( 'message' => 'Failed to add product to cart.' ) );
					}
				} else {
					wp_send_json_error( array( 'qty_message' => 'The maximum available quantity is ' . $stock_quantity . '.' ) );
				}
			} else {
				wp_send_json_error( array( 'message' => 'No matching variation found.' ) );
			}
		} else {
			// Handle simple product.
			$quantity = isset( $_POST['quantity'] ) ? intval( $_POST['quantity'] ) : 1;

			// Check stock quantity for simple product.
			$stock_quantity = $product->get_stock_quantity();

			// Check if the stock quantity is set or is unlimited.
			if ( ( $quantity < $stock_quantity ) || ( null === $stock_quantity ) ) {
				$added = WC()->cart->add_to_cart( $product_id, $quantity );
				if ( $added ) {
					wp_send_json_success(
						array(
							'cart_url' => wc_get_cart_url(),
							'message'  => 'Product successfully added to the cart.',
						)
					);
				} else {
					wp_send_json_error( array( 'message' => 'Failed to add product to cart.' ) );
				}
			} else {
				wp_send_json_error( 'The maximum available quantity is ' . $stock_quantity . '.' );
				return;
			}
		}
	}

	/**
	 * Handel Custom Add To Wishlist According to installed Plugin.
	 *
	 * @param number $product_id   product id.
	 * @access public
	 * @since 4.10.45
	 */
	public function custom_add_to_wishlist( $product_id ) {
		// Check if YITH WooCommerce Wishlist is active.
		if ( class_exists( 'YITH_WCWL' ) ) {
			$this->handle_yith_wishlist( $product_id );

		} elseif ( class_exists( 'WLFMC' ) ) { // Check if MC WooCommerce Wishlist is active.
			$this->handle_mc_wishlist( $product_id );
		} else {
			wp_send_json_error( 'No supported wishlist plugin activated.' );
		}
	}

	/**
	 * Custom Add To Wishlist Using Yith WooCommerce Wishlist Plugin.
	 *
	 * @param number $product_id   product id.
	 * @access public
	 * @since 4.10.45
	 */
	public function handle_yith_wishlist( $product_id ) {

		check_ajax_referer( 'pa-woo-cta-nonce', 'security' );

		// Check if YITH WooCommerce Wishlist is active.
		if ( ! function_exists( 'YITH_WCWL' ) ) {
			wp_send_json_error( 'YITH WooCommerce Wishlist plugin not activated.' );
			return;
		}

		$user_id = get_current_user_id();
		try {
			// Check if the product is already in the wishlist.
			$product_in_wishlist = YITH_WCWL()->is_product_in_wishlist( $product_id );

			if ( $product_in_wishlist ) {
				// Remove the product from the wishlist.
				YITH_WCWL()->remove( array( 'remove_from_wishlist' => $product_id ) );

				$message     = apply_filters( 'yith_wcwl_product_removed_text', 'Product removed from wishlist.' );
				$in_wishlist = false;
				wp_send_json_success(
					array(
						'message'     => $message,
						'in_wishlist' => $in_wishlist,
					)
				);
			} else {
				// Add the product to the wishlist.
				YITH_WCWL()->add( array( 'add_to_wishlist' => $product_id ) );

				$message     = apply_filters( 'yith_wcwl_wishlist_message', get_option( 'yith_wcwl_product_added_text', 'Product added to wishlist.' ) );
				$in_wishlist = true;
					wp_send_json_success(
						array(
							'message'     => $message,
							'in_wishlist' => $in_wishlist,
						)
					);
			}
		} catch ( YITH_WCWL_Exception $e ) {
				wp_send_json_error( array( 'message' => $e->getMessage() ) );
		} catch ( Exception $e ) {
				wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}

	/**
	 * Custom Add To Wishlist Using MC Wishlist Pluign.
	 *
	 * @param number $product_id   product id.
	 * @access public
	 * @since 4.10.45
	 */
	public function handle_mc_wishlist( $product_id ) {

		check_ajax_referer( 'pa-woo-cta-nonce', 'security' );

		if ( ! class_exists( 'WLFMC' ) ) {
			wp_send_json_error( __( 'MC WooCommerce Wishlist class not found.', 'premium-addons-for-elementor' ) );
			return;
		}

		$user_id = get_current_user_id();

		try {
			$wishlist            = WLFMC();
			$product_in_wishlist = $wishlist->is_product_in_wishlist( $product_id );

			if ( $product_in_wishlist ) {
				// Attempt to remove the product from the wishlist.
				$result      = $wishlist->remove( array( 'remove_from_wishlist' => $product_id ) );
				$in_wishlist = false;

				if ( $result ) {
					wp_send_json_success(
						array(
							'message'      => __( 'Product removed from MC Wishlist.', 'premium-addons-for-elementor' ),
							'in_wishlist'  => $in_wishlist,
							'wishlist_url' => $wishlist->get_wishlist_url(),
						)
					);
				} else {
					wp_send_json_error( __( 'Failed to remove product from MC Wishlist.', 'premium-addons-for-elementor' ) );
				}
			} else {
				// If the product is not in the wishlist, add it.
				$result      = $wishlist->add( array( 'add_to_wishlist' => $product_id ) );
				$in_wishlist = true;

				if ( $result ) {
					wp_send_json_success(
						array(
							'message'      => __( 'Product added to MC Wishlist.', 'premium-addons-for-elementor' ),
							'in_wishlist'  => $in_wishlist,
							'wishlist_url' => $wishlist->get_wishlist_url(),
						)
					);
				} else {
					wp_send_json_error( __( 'Failed to add product to MC Wishlist.', 'premium-addons-for-elementor' ) );
				}
			}
		} catch ( WLFMC_Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}


	/**
	 * Handel Custom Add To Compare According to installed Plugin.
	 *
	 * @param number $product_id   product id.
	 * @access public
	 * @since 4.10.45
	 */
	public function custom_add_to_compare( $product_id ) {
		// Check if Yith WooCommerce compare is active.
		if ( class_exists( 'YITH_Woocompare' ) ) {
			$this->handle_yith_compare( $product_id );

		} elseif ( class_exists( 'Ever_Compare' ) ) { // Check if Ever compare is active.
			$this->handle_ever_compare( $product_id );

		} else {
				wp_send_json_error( 'No supported wishlist plugin activated.' );
		}
	}

	/**
	 * Custom Add To Compare Using Ever Compare plugin.
	 *
	 * @param number $product_id   product id.
	 * @access public
	 * @since 4.10.45
	 */
	public function handle_ever_compare( $product_id ) {

		check_ajax_referer( 'pa-woo-cta-nonce', 'security' );

		// Ensure the Ever Compare plugin is active.
		if ( ! class_exists( 'Ever_Compare' ) ) {
			wp_send_json_error( __( 'Ever Compare plugin not activated or class not found.', 'premium-addons-for-elementor' ) );
			return;
		} else {

			// Define the cookie name.
			$cookie_name = 'ever_compare_compare_list';

			// Retrieve the existing list of compared products from the cookie.
			$products = isset( $_COOKIE[ $cookie_name ] ) ? json_decode( sanitize_text_field( wp_unslash( $_COOKIE[ $cookie_name ] ) ) ) : array();

			// Check if the product is in the compare list.
			$product_in_compare = in_array( strval( $product_id ), $products, true );

			// Perform the add or remove operation.
			if ( $product_in_compare ) {
				// Remove the product from the list.
				$products = array_filter(
					$products,
					function ( $id ) use ( $product_id ) {
						return strval( $product_id ) !== $id;
					}
				);

				// Update the cookie.
				setcookie( $cookie_name, wp_json_encode( $products ), time() + 3600 * 24 * 30, COOKIEPATH, COOKIE_DOMAIN, false, true ); // 30 days expiration
				$_COOKIE[ $cookie_name ] = wp_json_encode( $products );

				$in_compare = false;
				$message    = __( 'Product removed from compare list.', 'premium-addons-for-elementor' );
			} else {
				// Add the product to the list.
				$products[] = strval( $product_id );

				// Update the cookie.
				setcookie( $cookie_name, wp_json_encode( $products ), time() + 3600 * 24 * 30, COOKIEPATH, COOKIE_DOMAIN, false, true ); // 30 days expiration
				$_COOKIE[ $cookie_name ] = wp_json_encode( $products );

				$in_compare = true;
				$message    = __( 'Product added to compare list.', 'premium-addons-for-elementor' );
				// URL for viewing the compare list
				// $compare_url = home_url('/compare');.

			}

			// Return success response.
			wp_send_json_success(
				array(
					'message'    => $message,
					'in_compare' => $in_compare,
				)
			);

		}
	}

	/**
	 * Custom Add To Compare Using Yith WooCommerce Compare Plugin.
	 *
	 * @param number $product_id   product id.
	 * @access public
	 * @since 4.10.45
	 */
	public function handle_yith_compare( $product_id ) {

		check_ajax_referer( 'pa-woo-cta-nonce', 'security' );

		// Include the YITH WooCommerce Compare frontend class file.
		$frontend_class_file = WP_PLUGIN_DIR . '/yith-woocommerce-compare/includes/class.yith-woocompare-frontend.php';

		if ( ! file_exists( $frontend_class_file ) ) {
			wp_send_json_error( array( 'message' => __( 'Comparison class file not found.', 'premium-addons-for-elementor' ) ) );
			return;
		}
			require_once $frontend_class_file;

		if ( ! class_exists( 'YITH_Woocompare_Frontend' ) ) {
			wp_send_json_error( array( 'message' => __( 'YITH WooCommerce Compare plugin is not active.', 'premium-addons-for-elementor' ) ) );
			return;
		}

			// Retrieve the current comparison list from cookies.
			$cookie_name   = 'yith_woocompare_list';
			$products_list = isset( $_COOKIE[ $cookie_name ] ) ? json_decode( stripslashes( sanitize_text_field( wp_unslash( $_COOKIE[ $cookie_name ] ) ) ) ) : array();

			// Check if the product is already in the comparison list.
			$is_in_compare = in_array( $product_id, $products_list, true );

		if ( $is_in_compare ) {
			// Remove the product from the comparison list.
			foreach ( $products_list as $key => $id ) {
				if ( $id === $product_id ) {
					unset( $products_list[ $key ] );
				}
			}
			$in_compare = false;
			$message    = __( 'Product removed from comparison list.', 'premium-addons-for-elementor' );

		} else {
			// Add the product to the comparison list.
			$products_list[] = $product_id;
			$in_compare      = true;
			$message         = __( 'Product added to comparison list.', 'premium-addons-for-elementor' );
		}

			// Update the cookie with the new comparison list.
			setcookie( $cookie_name, wp_json_encode( array_values( $products_list ) ), 0, COOKIEPATH, COOKIE_DOMAIN, false, false );

			wp_send_json_success(
				array(
					'message'    => $message,
					'in_compare' => $in_compare,
				)
			);
	}
}
