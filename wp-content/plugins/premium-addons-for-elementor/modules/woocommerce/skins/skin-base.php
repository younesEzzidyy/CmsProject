<?php
/**
 * PA WooCommerce Skin Grid - Default.
 *
 * @package PA
 */

namespace PremiumAddons\Modules\Woocommerce\Skins;

use Elementor\Repeater;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Skin_Base as Elementor_Skin_Base;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

use PremiumAddons\Modules\Woocommerce\Widgets\Woo_Products;
use PremiumAddons\Includes\Helper_Functions;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // If this file is called directly, abort.
}

/**
 * Class Skin_Grid_Base
 *
 * @property Products $parent
 */
abstract class Skin_Base extends Elementor_Skin_Base {

	/**
	 * Register control actions.
	 *
	 * @since 4.7.0
	 * @access protected
	 */
	protected function _register_controls_actions() {
		// Product Rating Style.
		add_action( 'elementor/element/premium-woo-products/section_image_style/after_section_end', array( $this, 'register_product_rating_style' ) );
		// Product Price Style.
		add_action( 'elementor/element/premium-woo-products/section_image_style/after_section_end', array( $this, 'register_product_price_style' ) );

		add_action( 'elementor/element/premium-woo-products/section_image_style/after_section_end', array( $this, 'register_title_style_controls' ) );

		add_action( 'elementor/element/premium-woo-products/section_image_style/after_section_end', array( $this, 'register_cat_style_controls' ) );
		// Product Quick View Style.
		add_action( 'elementor/element/premium-woo-products/section_image_style/after_section_end', array( $this, 'register_quick_view_modal_style_controls' ), 20 );

		add_action( 'elementor/element/premium-woo-products/section_image_style/after_section_end', array( $this, 'register_quick_view_content_style_controls' ), 20 );

		add_action( 'elementor/element/premium-woo-products/section_image_style/after_section_end', array( $this, 'register_quick_view_slider_style_controls' ), 20 );
	}

	/**
	 * Register Title Style Controls.
	 *
	 * @since 4.7.0
	 * @access public
	 */
	public function register_title_style_controls() {

		$this->start_controls_section(
			'section_title_style',
			array(
				'label' => __( 'Title', 'premium-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_PRIMARY,
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-woocommerce .woocommerce-loop-product__title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'title_hover_color',
			array(
				'label'     => __( 'Hover Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_PRIMARY,
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-woocommerce .woocommerce-loop-product__title:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				),
				'selector' => '{{WRAPPER}} .premium-woocommerce .woocommerce-loop-product__title',
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'title_text_shadow',
				'selector' => '{{WRAPPER}} .premium-woocommerce .woocommerce-loop-product__title',
			)
		);

		$this->add_responsive_control(
			'title_spacing',
			array(
				'label'      => __( 'Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-woocommerce .woocommerce-loop-product__title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Category Style Controls.
	 *
	 * @since 4.7.0
	 * @access public
	 */
	public function register_cat_style_controls() {

		$this->start_controls_section(
			'section_category_style',
			array(
				'label' => __( 'Category', 'premium-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'category_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_TEXT,
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-woocommerce .premium-woo-product-category' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'category_typography',
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				),
				'selector' => '{{WRAPPER}} .premium-woocommerce .premium-woo-product-category',
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'category_text_shadow',
				'selector' => '{{WRAPPER}} .premium-woocommerce .premium-woo-product-category',
			)
		);

		$this->add_responsive_control(
			'category_spacing',
			array(
				'label'      => __( 'Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-woocommerce .premium-woo-product-category' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Quick View Style Controls.
	 *
	 * @since 4.7.0
	 * @access public
	 */
	public function register_quick_view_modal_style_controls() {

		$this->start_controls_section(
			'quick_view_modal_style',
			array(
				'label' => __( 'Quick View Modal', 'premium-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'qv_width',
			array(
				'label'       => __( 'Width', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 'px', 'vw', '%', 'custom' ),
				'range'       => array(
					'px' => array(
						'min' => 50,
						'max' => 1500,
					),
					'em' => array(
						'min' => 1,
						'max' => 50,
					),
				),
				'label_block' => true,
				'selectors'   => array(
					'#premium-woo-quick-view-{{ID}} .premium-woo-lightbox-content'  => 'width: {{SIZE}}{{UNIT}} !important',
				),
			)
		);

		$this->add_control(
			'lightbox_overlay_color',
			array(
				'label'     => __( 'Overlay Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'#premium-woo-quick-view-{{ID}} .premium-woo-quick-view-back' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'qv_container_background',
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '#premium-woo-quick-view-{{ID}} #premium-woo-quick-view-modal .premium-woo-lightbox-content',
			)
		);

		$this->add_responsive_control(
			'lightbox_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'#premium-woo-quick-view-{{ID}} .premium-woo-lightbox-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'lightbox_border',
				'selector' => '#premium-woo-quick-view-{{ID}} .premium-woo-lightbox-content',
			)
		);

		$this->add_control(
			'lightbox_border_radius',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'#premium-woo-quick-view-{{ID}} .premium-woo-lightbox-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->add_control(
			'qv_close_heading',
			array(
				'label' => __( 'Close Icon', 'premium-addons-for-elementor' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'close_icon_color',
			array(
				'label'     => __( 'Icon Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'#premium-woo-quick-view-{{ID}} .premium-woo-quick-view-close' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'close_icon_color_hover',
			array(
				'label'     => __( 'Icon Hover Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'#premium-woo-quick-view-{{ID}} .premium-woo-quick-view-close:hover' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_responsive_control(
			'close_icon_size',
			array(
				'label'     => __( 'Icon Size', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 10,
						'max' => 50,
					),
				),
				'selectors' => array(
					'#premium-woo-quick-view-{{ID}} .premium-woo-quick-view-close' => 'font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'close_icon_backcolor',
			array(
				'label'     => __( 'Background Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'#premium-woo-quick-view-{{ID}} .premium-woo-quick-view-close' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'close_icon_border_radius',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'#premium-woo-quick-view-{{ID}} .premium-woo-quick-view-close' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'close_icon_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'#premium-woo-quick-view-{{ID}} .premium-woo-quick-view-close' => 'padding: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Quick View Style Controls.
	 *
	 * @since 4.7.0
	 * @access public
	 */
	public function register_quick_view_content_style_controls() {

		$this->start_controls_section(
			'quick_view_content_style',
			array(
				'label' => __( 'Quick View Content', 'premium-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

        $this->add_control(
			'qv_ribbon_heading',
			array(
				'label' => __( 'Sale Ribbon', 'premium-addons-for-elementor' ),
				'type'  => Controls_Manager::HEADING,
                'condition' => array(
					'qv_sale!' => 'yes',
				),
			)
		);

        $this->add_control(
			'qv_ribbon_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'#premium-woo-quick-view-{{ID}} .premium-qv-badge .corner' => 'color: {{VALUE}};',
				),
                'condition' => array(
					'qv_sale!' => 'yes',
				),
			)
		);

        $this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'qv_ribbon_typography',
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				),
				'selector' => '#premium-woo-quick-view-{{ID}} .premium-qv-badge .corner',
                'condition' => array(
					'qv_sale!' => 'yes',
				),
			)
		);

        $this->add_control(
			'qv_ribbon_backcolor',
			array(
				'label'     => __( 'Background Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'#premium-woo-quick-view-{{ID}} .premium-qv-badge .corner' => 'background-color: {{VALUE}};',
				),
                'condition' => array(
					'qv_sale!' => 'yes',
				),
			)
		);

		$this->add_control(
			'qv_name_heading',
			array(
				'label' => __( 'Product Name', 'premium-addons-for-elementor' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'qv_name_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_PRIMARY,
				),
				'selectors' => array(
					'#premium-woo-quick-view-{{ID}} .woocommerce-loop-product__title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'qv_name_hover_color',
			array(
				'label'     => __( 'Hover Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'#premium-woo-quick-view-{{ID}} .woocommerce-loop-product__title:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'qv_name_typography',
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				),
				'selector' => '#premium-woo-quick-view-{{ID}} .woocommerce-loop-product__title',
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'qv_name_text_shadow',
				'selector' => '#premium-woo-quick-view-{{ID}} .woocommerce-loop-product__title',
			)
		);

		$this->add_responsive_control(
			'qv_name_spacing',
			array(
				'label'      => __( 'Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'#premium-woo-quick-view-{{ID}} .woocommerce-loop-product__title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'qv_rating_heading',
			array(
				'label'     => __( 'Product Rating', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'qv_rating!' => 'yes',
				),
			)
		);

		$this->add_control(
			'qv_star_color',
			array(
				'label'     => __( 'Star Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'#premium-woo-quick-view-{{ID}} div.star-rating' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'qv_rating!' => 'yes',
				),
			)
		);

		$this->add_control(
			'qv_empty_star_color',
			array(
				'label'     => __( 'Empty Star Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'#premium-woo-quick-view-{{ID}} div.star-rating::before' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'qv_rating!' => 'yes',
				),
			)
		);

		$this->add_control(
			'qv_star_size',
			array(
				'label'     => __( 'Star Size', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'unit' => 'em',
				),
				'range'     => array(
					'em' => array(
						'min'  => 0,
						'max'  => 4,
						'step' => 0.1,
					),
				),
				'selectors' => array(
					'#premium-woo-quick-view-{{ID}} div.star-rating' => 'font-size: {{SIZE}}{{UNIT}}',
				),
				'condition' => array(
					'qv_rating!' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'qv_rating_spacing',
			array(
				'label'      => __( 'Bottom Spacing', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'em' => array(
						'min'  => 0,
						'max'  => 5,
						'step' => 0.1,
					),
				),
				'selectors'  => array(
					'#premium-woo-quick-view-{{ID}} div.star-rating' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				),
				'condition'  => array(
					'qv_rating!' => 'yes',
				),
			)
		);

		$this->add_control(
			'qv_price_heading',
			array(
				'label'     => __( 'Product Price', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'qv_price!' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'qv_price_spacing',
			array(
				'label'      => __( 'Bottom Spacing', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'em' => array(
						'min'  => 0,
						'max'  => 5,
						'step' => 0.1,
					),
				),
				'selectors'  => array(
					'#premium-woo-quick-view-{{ID}} .price' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				),
				'condition'  => array(
					'qv_price!' => 'yes',
				),
			)
		);

		$this->start_controls_tabs( 'qv_price_style_tabs' );

		$this->start_controls_tab(
			'qv_price_tab',
			array(
				'label'     => __( 'Price', 'premium-addons-for-elementor' ),
				'condition' => array(
					'qv_price!' => 'yes',
				),
			)
		);

		$this->add_control(
			'qv_price_color',
			array(
				'label'     => __( 'Price Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_TEXT,
				),
				'selectors' => array(
					'#premium-woo-quick-view-{{ID}} div.product p.price' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'qv_price!' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'qv_price_typography',
				'global'    => array(
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				),
				'selector'  => '#premium-woo-quick-view-{{ID}} div.product p.price, .premium-woo-quick-view-{{ID}} div.product p.price ins',
				'condition' => array(
					'qv_price!' => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'qv_slashed_price_tab',
			array(
				'label'     => __( 'Slashed', 'premium-addons-for-elementor' ),
				'condition' => array(
					'qv_price!' => 'yes',
				),
			)
		);

		$this->add_control(
			'qv_slashed_price_color',
			array(
				'label'     => __( 'Slashed Price Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_TEXT,
				),
				'selectors' => array(
					'#premium-woo-quick-view-{{ID}} .price del' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'qv_price!' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'qv_slashed_price_typography',
				'selector'  => '#premium-woo-quick-view-{{ID}} .price del',
				'exclude'  => array( 'word_spacing' ),
				'condition' => array(
					'qv_price!' => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

        $this->add_control(
			'qv_desc_heading',
			array(
				'label'     => __( 'Product Description', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
                'condition' => array(
					'qv_desc!' => 'yes',
				),
			)
		);

		$this->add_control(
			'qv_desc_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'#premium-woo-quick-view-{{ID}} .premium-woo-qv-desc' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'qv_desc!' => 'yes',
				),
			)
		);

        $this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'qv_desc_typography',
				'selector'  => '#premium-woo-quick-view-{{ID}} .premium-woo-qv-desc',
				'global'    => array(
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				),
				'condition' => array(
					'qv_desc!' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'qv_desc_margin',
			array(
				'label'      => __( 'Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'#premium-woo-quick-view-{{ID}} .premium-woo-qv-desc' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'qv_desc!' => 'yes',
				),
			)
		);

		$this->add_control(
			'qv_atc_heading',
			array(
				'label'     => __( 'Product CTA', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'qv_atc!' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'qv_cta_typography',
				'selector'  => '#premium-woo-quick-view-{{ID}} .premium-woo-atc-button button.button.alt',
				'global'    => array(
					'default' => Global_Typography::TYPOGRAPHY_ACCENT,
				),
				'condition' => array(
					'qv_atc!' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'qv_cta_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'#premium-woo-quick-view-{{ID}} .premium-woo-atc-button button.button.alt' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'qv_atc!' => 'yes',
				),
			)
		);

		$this->start_controls_tabs( 'qv_cta_style_tabs' );

		$this->start_controls_tab(
			'qv_cta_style_tab_normal',
			array(
				'label'     => __( 'Normal', 'premium-addons-for-elementor' ),
				'condition' => array(
					'qv_atc!' => 'yes',
				),
			)
		);

		$this->add_control(
			'qv_cta_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'#premium-woo-quick-view-{{ID}} .premium-woo-atc-button button.button.alt' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'qv_atc!' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'qv_cta_background',
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '#premium-woo-quick-view-{{ID}} .premium-woo-atc-button button.button.alt',
				'condition' => array(
					'qv_atc!' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'qv_cta_shadow',
				'selector'  => '#premium-woo-quick-view-{{ID}} .premium-woo-atc-button button.button.alt',
				'condition' => array(
					'qv_atc!' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'qv_cta_border',
				'selector'  => '#premium-woo-quick-view-{{ID}} .premium-woo-atc-button button.button.alt',
				'condition' => array(
					'qv_atc!' => 'yes',
				),
			)
		);

		$this->add_control(
			'qv_cta_radius',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'#premium-woo-quick-view-{{ID}} .premium-woo-atc-button button.button.alt' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
				'condition'  => array(
					'qv_atc!' => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'qv_cta_style_tab_hover',
			array(
				'label'     => __( 'Hover', 'premium-addons-for-elementor' ),
				'condition' => array(
					'qv_atc!' => 'yes',
				),
			)
		);

		$this->add_control(
			'qv_cta_color_hover',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'#premium-woo-quick-view-{{ID}} .premium-woo-atc-button button.button.alt:hover' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'qv_atc!' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'qv_cta_background_hover',
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '#premium-woo-quick-view-{{ID}} .premium-woo-atc-button button.button.alt:hover',
				'condition' => array(
					'qv_atc!' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'qv_cta_shadow_hover',
				'selector'  => '#premium-woo-quick-view-{{ID}} .premium-woo-atc-button button.button.alt:hover',
				'condition' => array(
					'qv_atc!' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'qv_cta_border_hover',
				'selector'  => '#premium-woo-quick-view-{{ID}} .premium-woo-atc-button button.button.alt:hover',
				'condition' => array(
					'qv_atc!' => 'yes',
				),
			)
		);

		$this->add_control(
			'qv_cta_radius_hover',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'#premium-woo-quick-view-{{ID}} .premium-woo-atc-button button.button.alt:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
				'condition'  => array(
					'qv_atc!' => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'qv_cart_heading',
			array(
				'label'     => __( 'View Cart Text', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
                'condition' => array(
					'qv_atc!' => 'yes',
				),
			)
		);

		$this->add_control(
			'qv_cart_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'#premium-woo-quick-view-{{ID}} .added_to_cart' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'qv_atc!' => 'yes',
				),
			)
		);

		$this->add_control(
			'qv_cart_color_hover',
			array(
				'label'     => __( 'Hover Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'#premium-woo-quick-view-{{ID}} .added_to_cart:hover' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'qv_atc!' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'cart_spacing',
			array(
				'label'      => __( 'Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'#premium-woo-quick-view-{{ID}} .added_to_cart' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'qv_atc!' => 'yes',
				),
			)
		);

        $this->add_control(
			'qv_meta_heading',
			array(
				'label' => __( 'Product Meta', 'premium-addons-for-elementor' ),
				'type'  => Controls_Manager::HEADING,
                'condition' => array(
					'qv_meta!' => 'yes',
				),
			)
		);

        $this->add_control(
			'qv_meta_name_color',
			array(
				'label'     => __( 'Meta Name Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'#premium-woo-quick-view-{{ID}} .product_meta > span' => 'color: {{VALUE}};',
				),
                'condition' => array(
					'qv_meta!' => 'yes',
				),
			)
		);

        $this->add_control(
			'qv_meta_value_color',
			array(
				'label'     => __( 'Meta Value Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'#premium-woo-quick-view-{{ID}} .product_meta > span span, .premium-woo-quick-view-{{ID}} .product_meta a' => 'color: {{VALUE}};',
				),
                'condition' => array(
					'qv_meta!' => 'yes',
				),
			)
		);

        $this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'qv_meta_typography',
				'selector' => '#premium-woo-quick-view-{{ID}} .product_meta > span',
                'condition' => array(
					'qv_meta!' => 'yes',
				),
			)
		);

        $this->add_responsive_control(
			'qv_meta_spacing',
			array(
				'label'      => __( 'Bottom Spacing', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'em' => array(
						'min'  => 0,
						'max'  => 5,
						'step' => 0.1,
					),
				),
				'selectors'  => array(
					'#premium-woo-quick-view-{{ID}} .product_meta > span' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				),
				'condition'  => array(
					'qv_meta!' => 'yes',
				),
			)
		);

		$this->add_control(
			'qv_quantity_heading',
			array(
				'label' => __( 'Quantity Field', 'premium-addons-for-elementor' ),
				'type'  => Controls_Manager::HEADING,
				'separator'=> 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'qv_quantitiy_border',
				'selector' => '#premium-woo-quick-view-{{ID}} div.quantity .qty'
			)
		);

		$this->add_control(
			'qv_quantitiy_border_radius',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'#premium-woo-quick-view-{{ID}} div.quantity .qty' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Quick View Slider Style Controls.
	 *
	 * @since 4.7.0
	 * @access public
	 */
	public function register_quick_view_slider_style_controls() {

		$this->start_controls_section(
			'quick_view_slider_style',
			array(
				'label' => __( 'Quick View Carousel', 'premium-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'qv_dots_heading',
			array(
				'label' => __( 'Dots', 'premium-addons-for-elementor' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'carousel_dot_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_SECONDARY,
				),
				'selectors' => array(
					'#premium-woo-quick-view-{{ID}} .flex-control-nav a' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'carousel_dot_active_color',
			array(
				'label'     => __( 'Active Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_PRIMARY,
				),
				'selectors' => array(
					'#premium-woo-quick-view-{{ID}} a.flex-active' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'qv_arrow_heading',
			array(
				'label' => __( 'Arrow', 'premium-addons-for-elementor' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'arrow_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_PRIMARY,
				),
				'selectors' => array(
					'#premium-woo-quick-view-{{ID}} .flex-direction-nav li a' => 'color: {{VALUE}} !important',
				),
			)
		);

		$this->add_responsive_control(
			'arrows_pos',
			array(
				'label'      => __( 'Position', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => -100,
						'max' => 100,
					),
					'em' => array(
						'min' => -10,
						'max' => 10,
					),
				),
				'selectors'  => array(
					'#premium-woo-quick-view-{{ID}} .flex-direction-nav li a.flex-prev' => 'left: {{SIZE}}{{UNIT}};',
					'#premium-woo-quick-view-{{ID}} .flex-direction-nav li a.flex-next' => 'right: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'carousel_arrow_size',
			array(
				'label'      => __( 'Size', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'#premium-woo-quick-view-{{ID}} .flex-direction-nav li a' => 'font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'carousel_arrow_background',
			array(
				'label'     => __( 'Background Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'#premium-woo-quick-view-{{ID}} .flex-direction-nav li a' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'carousel_border_radius',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'#premium-woo-quick-view-{{ID}} .flex-direction-nav li a' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'carousel_arrow_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'#premium-woo-quick-view-{{ID}} .flex-direction-nav li a' => 'padding: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Product Price Style Controls.
	 *
	 * @since 4.7.0
	 * @access public
	 */
	public function register_product_price_style() {

		$this->start_controls_section(
			'section_price_style',
			array(
				'label' => __( 'Price', 'premium-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'price_text_shadow',
				'selector' => '{{WRAPPER}} .premium-woocommerce li.product .price',
			)
		);

		$this->add_responsive_control(
			'price_spacing',
			array(
				'label'      => __( 'Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-woocommerce li.product .price' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs(
			'price_style_tabs'
		);

		$this->start_controls_tab(
			'price_tab',
			array(
				'label' => __( 'Price', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'price_color',
			array(
				'label'     => __( 'Price Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_TEXT,
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-woocommerce li.product .price' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'price_typography',
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				),
				'selector' => '{{WRAPPER}} .premium-woocommerce li.product .price',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'slashed_price_tab',
			array(
				'label' => __( 'Slashed', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'slashed_price_color',
			array(
				'label'     => __( 'Slashed Price Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_TEXT,
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-woocommerce li.product .price del' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'slashed_price_typography',
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				),
				'exclude'  => array( 'word_spacing' ),
				'selector' => '{{WRAPPER}} .premium-woocommerce li.product .price del',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Register Product Rating Style Controls.
	 *
	 * @since 4.7.0
	 * @access public
	 */
	public function register_product_rating_style() {

		$this->start_controls_section(
			'section_rating_style',
			array(
				'label' => __( 'Rating', 'premium-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'star_color',
			array(
				'label'     => __( 'Star Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-woocommerce li.product div.star-rating' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'empty_star_color',
			array(
				'label'     => __( 'Empty Star Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-woocommerce li.product div.star-rating::before' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'star_size',
			array(
				'label'     => __( 'Star Size', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'unit' => 'em',
				),
				'range'     => array(
					'em' => array(
						'min'  => 0,
						'max'  => 4,
						'step' => 0.1,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-woocommerce li.product .star-rating' => 'font-size: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_responsive_control(
			'rating_spacing',
			array(
				'label'      => __( 'Bottom Spacing', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'em' => array(
						'min'  => 0,
						'max'  => 5,
						'step' => 0.1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}}  .premium-woocommerce li.product .star-rating' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->end_controls_section();
	}
}
