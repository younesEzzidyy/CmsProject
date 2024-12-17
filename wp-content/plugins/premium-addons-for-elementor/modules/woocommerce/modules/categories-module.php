<?php
/**
 * Class: Categories_Module
 * Name: Woocommerce Categories
 * Slug: premium-woocommerce-categories
 * PA WooCommerce Module.
 *
 * @package PA
 */

namespace PremiumAddons\Modules\Woocommerce\Modules;

use PremiumAddons\Includes\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // If this file is called directly, abort.
}

/**
 * Class Categories Module.
 */
class Categories_Module extends Module_Base {

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
		return 'woocommerce-categories';
	}
}
