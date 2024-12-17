<?php
/**
 * Post Category Condition Handler.
 */

namespace PremiumAddons\Includes\PA_Display_Conditions\Conditions;

// Elementor Classes.
use Elementor\Controls_Manager;

// PA Classes.
use PremiumAddons\Includes\Helper_Functions;
use PremiumAddons\Includes\Premium_Template_Tags;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * Class Post_Category
 */
class Post_Category extends Condition {

	/**
	 * Get Controls Options.
	 *
	 * @access public
	 * @since 4.7.0
	 *
	 * @return array|void  controls options
	 */
	public function get_control_options() {

		$categories = Premium_Template_Tags::get_all_categories();

		return array(
			'label'       => __( 'Value', 'premium-addons-for-elementor' ),
			'type'        => Controls_Manager::SELECT2,
			'default'     => array(),
			'label_block' => true,
			'options'     => $categories,
			'multiple'    => true,
			'condition'   => array(
				'pa_condition_key' => 'post_category',
			),
		);
	}

	/**
	 * Compare Condition Value.
	 *
	 * @access public
	 * @since 4.7.0
	 *
	 * @param array       $settings       element settings.
	 * @param string      $operator       condition operator.
	 * @param string      $value          condition value.
	 * @param string      $compare_val    compare value.
	 * @param string|bool $tz        time zone.
	 *
	 * @return bool|void
	 */
	public function compare_value( $settings, $operator, $value, $compare_val, $tz ) {

		$post_id = get_the_ID();

		if ( ! $post_id ) {
			return true;
		}

		$categories = get_the_category( $post_id );

		$cat_ids = array();

		foreach ( $categories as $index => $category ) {

			array_push( $cat_ids, $category->cat_ID );

		}

		$condition_result = ! empty( array_intersect( (array) $value, $cat_ids ) ) ? true : false;

		return Helper_Functions::get_final_result( $condition_result, $operator );
	}
}
