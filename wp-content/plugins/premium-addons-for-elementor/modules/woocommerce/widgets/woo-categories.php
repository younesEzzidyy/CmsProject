<?php
/**
 * Premium Woo Categories.
 */

namespace PremiumAddons\Modules\Woocommerce\Widgets;

// Elementor Classes.
use Elementor\Widget_Base;
use Elementor\Group_Control_Image_Size;
use Elementor\Repeater;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;

use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

// Premium Addons Classes.
use PremiumAddons\Includes\Helper_Functions;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // If this file is called directly, abort.
}

/**
 * Class Woo_Categories
 */
class Woo_Categories extends Widget_Base {

	private $category_index = 0;

	/**
	 * Template Instance
	 *
	 * @var template_instance
	 */
	protected $template_instance;

	/**
	 * Retrieve Widget Name.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function get_name() {
		return 'premium-woo-categories';
	}

	/**
	 * Retrieve Widget Title.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function get_title() {
		return __( 'Woo Categories', 'premium-addons-for-elementor' );
	}

	/**
	 * Retrieve Widget Dependent CSS.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array CSS script handles.
	 */
	public function get_style_depends() {
		return array(
			'pa-slick',
			'woocommerce-general',
			'premium-addons',
		);
	}

	/**
	 * Retrieve Widget Keywords.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget keywords.
	 */
	public function get_keywords() {
		return array( 'pa', 'premium', 'posts', 'grid', 'item', 'loop', 'woocommerce', 'listing' );
	}

	/**
	 * Retrieve Widget Dependent JS.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array JS script handles.
	 */
	public function get_script_depends() {
		return array(
			'isotope-js',
			'pa-slick',
			'imagesloaded',
			'premium-woo-cats',
		);
	}

	/**
	 * Retrieve Widget Icon.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string widget icon.
	 */
	public function get_icon() {
		return 'pa-woo-cats';
	}

	/**
	 * Retrieve Widget Categories.
	 *
	 * @since 1.5.1
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return array( 'premium-elements' );
	}

	/**
	 * Retrieve Widget Support URL.
	 *
	 * @access public
	 *
	 * @return string support URL.
	 */
	public function get_custom_help_url() {
		return 'https://premiumaddons.com/support/';
	}

	/**
	 * Register Woo Products controls.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'general_section',
			array(
				'label' => __( 'General', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'layout',
			array(
				'label'              => __( 'Layout', 'premium-addons-for-elementor' ),
				'type'               => Controls_Manager::SELECT,
				'options'            => array(
					'masonry'  => __( 'Masonry', 'premium-addons-for-elementor' ),
					'even'     => __( 'Even', 'premium-addons-for-elementor' ),
					'list'     => __( 'List', 'premium-addons-for-elementor' ),
					'carousel' => __( 'Carousel', 'premium-addons-for-elementor' ),
				),
				'default'            => 'masonry',
				'render_type'        => 'template',
				'prefix_class'       => 'premium-woo-cats__',
				'frontend_available' => true,
			)
		);

		$this->add_responsive_control(
			'columns',
			array(
				'label'              => __( 'Category Per Row', 'premium-addons-for-elementor' ),
				'type'               => Controls_Manager::SELECT,
				'options'            => array(
					'100%'    => __( '1 Column', 'premium-addons-for-elementor' ),
					'50%'     => __( '2 Columns', 'premium-addons-for-elementor' ),
					'33.33%'  => __( '3 Columns', 'premium-addons-for-elementor' ),
					'25%'     => __( '4 Columns', 'premium-addons-for-elementor' ),
					'20%'     => __( '5 Columns', 'premium-addons-for-elementor' ),
					'16.667%' => __( '6 Columns', 'premium-addons-for-elementor' ),
				),
				'default'            => '33.33%',
				'tablet_default'     => '50%',
				'mobile_default'     => '100%',
				'render_type'        => 'template',
				'selectors'          => array(
					'{{WRAPPER}} .premium-woo-cats__list-wrap li.product-category' => 'width: {{VALUE}}',
				),
				'frontend_available' => true,
				'condition'          => array(
					'layout!' => 'list',
				),
			)
		);

        $this->add_group_control(
			Group_Control_Image_Size::get_type(),
			array(
				'name'      => 'thumbnail_image',
				'default'   => 'full',
			)
		);

		$this->add_responsive_control(
			'img_width',
			array(
				'label'      => __( 'Image Width', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 500,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .premium-woo-cats__category .premium-woo-cats__img-wrap' => 'width: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'layout' => 'list',
				),
			)
		);

		$this->add_responsive_control(
			'img_height',
			array(
				'label'      => __( 'Image Height', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 500,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .premium-woo-cats__category img' => 'height: {{SIZE}}{{UNIT}};',
					// '{{WRAPPER}}.premium-woo-cats__list .premium-woo-cats__category' => 'height: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'layout!' => 'masonry',
				),
			)
		);

		$this->add_responsive_control(
			'rows_spacing',
			array(
				'label'     => __( 'Rows Spacing', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-woo-cats__list-wrap li.product' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
				'condition' => array(
					'layout!' => 'carousel',
				),
			)
		);

		$this->add_responsive_control(
			'columns_spacing',
			array(
				'label'     => __( 'Columns Spacing', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 10,
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-woo-cats__list-wrap li.product-category' => 'padding-right: calc( {{SIZE}}{{UNIT}}/2 ); padding-left: calc( {{SIZE}}{{UNIT}}/2 );',
					'{{WRAPPER}} .premium-woo-cats__list-wrap ul.products' => 'margin-left: calc( -{{SIZE}}{{UNIT}}/2 ); margin-right: calc( -{{SIZE}}{{UNIT}}/2 );',
				),
				'condition' => array(
					'columns!' => '100%',
					'layout!'  => 'list',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'query_section',
			array(
				'label' => __( 'Query', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'categories_number',
			array(
				'label'   => __( 'Number of Categories', 'uael' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => '3',
			)
		);

		$this->add_control(
			'category_filter_rule',
			array(
				'label'   => __( 'Category Filter Rule', 'premium-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'all',
				'options' => array(
					'all'     => __( 'Show All', 'premium-addons-for-elementor' ),
					'top'     => __( 'Show Top Level', 'premium-addons-for-elementor' ),
					'include' => __( 'Match Categories', 'premium-addons-for-elementor' ),
					'exclude' => __( 'Exclude Categories', 'premium-addons-for-elementor' ),
				),
			)
		);

		$this->add_control(
			'category_filter',
			array(
				'label'       => __( 'Category Filter', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => Helper_Functions::get_woo_categories( 'id' ),
				'condition'   => array(
					'category_filter_rule' => array( 'include', 'exclude' ),
				),
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'   => __( 'Order by', 'premium-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'name',
				'options' => array(
					'name'       => __( 'Name', 'premium-addons-for-elementor' ),
					'slug'       => __( 'Slug', 'premium-addons-for-elementor' ),
                    'date'       => __( 'Date', 'premium-addons-for-elementor' ),
					'desc'       => __( 'Description', 'premium-addons-for-elementor' ),
					'count'      => __( 'Count', 'premium-addons-for-elementor' ),
					'menu_order' => __( 'Menu Order', 'premium-addons-for-elementor' ),
				),
			)
		);

		$this->add_control(
			'order',
			array(
				'label'   => __( 'Order', 'premium-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'desc',
				'options' => array(
					'desc' => __( 'Descending', 'premium-addons-for-elementor' ),
					'asc'  => __( 'Ascending', 'premium-addons-for-elementor' ),
				),
			)
		);

		$this->add_control(
			'hide_empty',
			array(
				'label'        => __( 'Hide Empty Categories', 'premium-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'true',
				'return_value' => 'true',
			)
		);

		$this->add_control(
			'empty_query_text',
			array(
				'label'       => __( 'Empty Query Text', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'display_options_section',
			array(
				'label' => __( 'Display Options', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'content_position',
			array(
				'label'        => __( 'Content Position', 'premium-addons-for-elementor' ),
				'type'         => Controls_Manager::SELECT,
				'options'      => array(
					'above' => __( 'Above Image', 'premium-addons-for-elementor' ),
					'below' => __( 'Below Image', 'premium-addons-for-elementor' ),
					'next'  => __( 'Beside Image', 'premium-addons-for-elementor' ),
				),
				'render_type'  => 'template',
				'prefix_class' => 'premium-woo-cats__content-',
				'default'      => 'below',
			)
		);

		$this->add_control(
			'show_count',
			array(
				'label'   => __( 'Show Products Count', 'premium-addons-for-elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'count_position',
			array(
				'label'     => __( 'Products Count Position', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					'line' => __( 'Separate Line', 'premium-addons-for-elementor' ),
					'next' => __( 'Beside Title', 'premium-addons-for-elementor' ),
				),
				'default'   => 'line',
				'condition' => array(
					'show_count' => 'yes',
				),
			)
		);

		$this->add_control(
			'show_desc',
			array(
				'label' => __( 'Show Category Description', 'premium-addons-for-elementor' ),
				'type'  => Controls_Manager::SWITCHER,
			)
		);

		$this->add_control(
			'title_tag',
			array(
				'label'   => __( 'Title HTML Tag', 'premium-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'h1' => __( 'H1', 'premium-addons-for-elementor' ),
					'h2' => __( 'H2', 'premium-addons-for-elementor' ),
					'h3' => __( 'H3', 'premium-addons-for-elementor' ),
					'h4' => __( 'H4', 'premium-addons-for-elementor' ),
					'h5' => __( 'H5', 'premium-addons-for-elementor' ),
					'h6' => __( 'H6', 'premium-addons-for-elementor' ),
				),
				'default' => 'h3',
			)
		);

		$this->add_responsive_control(
			'content_align',
			array(
				'label'     => __( 'Content Alignment', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'   => array(
						'title' => __( 'Left', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => __( 'Right', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'   => 'center',
				'toggle'    => false,
				'selectors' => array(
					'{{WRAPPER}} .premium-woo-cats__content-wrap' => 'text-align: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'new_tab',
			array(
				'label'   => __( 'Links in New Tab', 'premium-addons-for-elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->end_controls_section();

		do_action( 'pa_woo_cats_carousel', $this );

		$this->start_controls_section(
			'image_style_section',
			array(
				'label' => __( 'Image', 'premium-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'overlay_color',
			array(
				'label'     => __( 'Overlay Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-woo-cats__img-overlay:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'hover_style',
			array(
				'label'        => __( 'Image Hover Style', 'premium-addons-for-elementor' ),
				'type'         => Controls_Manager::SELECT,
				'options'      => array(
					''        => __( 'None', 'premium-addons-for-elementor' ),
					'zoomin'  => __( 'Zoom In', 'premium-addons-for-elementor' ),
					'zoomout' => __( 'Zoom Out', 'premium-addons-for-elementor' ),
					'scale'   => __( 'Scale', 'premium-addons-for-elementor' ),
					'gray'    => __( 'Grayscale', 'premium-addons-for-elementor' ),
					'bright'  => __( 'Bright', 'premium-addons-for-elementor' ),
					'sepia'   => __( 'Sepia', 'premium-addons-for-elementor' ),
					'trans'   => __( 'Translate', 'premium-addons-for-elementor' ),
					'custom'  => __( 'Custom', 'premium-addons-for-elementor' ),
				),
				'prefix_class' => 'premium-woo-cats__img-',
				'default'      => 'zoomin',
			)
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			array(
				'name'      => 'hover_css_filters',
				'selector'  => '{{WRAPPER}} li:hover .premium-woo-cats__category img',
				'condition' => array(
					'hover_style' => 'custom',
				),
			)
		);

		$this->add_responsive_control(
			'img_fit',
			array(
				'label'     => __( 'Image Fit', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'cover',
				'options'   => array(
					'fill'    => __( 'Fill', 'premium-addons-for-elementor' ),
					'cover'   => __( 'Cover', 'premium-addons-for-elementor' ),
					'contain' => __( 'Contain', 'premium-addons-for-elementor' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-woo-cats__category img' => 'object-fit: {{VALUE}};',
				),
			)
		);

		$this->start_controls_tabs( 'image_style_tabs' );

		$this->start_controls_tab(
			'image_style_tab_normal',
			array(
				'label' => __( 'Normal', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'image_border',
				'selector' => '{{WRAPPER}} .premium-woo-cats__img-wrap',
			)
		);

		$this->add_control(
			'image_border_radius',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-woo-cats__img-wrap'  => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'image_style_tab_hover',
			array(
				'label' => __( 'Hover', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'image_hover_border',
				'selector' => '{{WRAPPER}} li:hover .premium-woo-cats__img-wrap',
			)
		);

		$this->add_control(
			'image_hover_border_radius',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} li:hover .premium-woo-cats__img-wrap'  => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

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
				'selectors' => array(
					'{{WRAPPER}} .woocommerce-loop-category__title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'title_hover_color',
			array(
				'label'     => __( 'Hover Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woocommerce-loop-category__title:hover' => 'color: {{VALUE}};',
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
				'selector' => '{{WRAPPER}} .woocommerce-loop-category__title',
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'title_text_shadow',
				'selector' => '{{WRAPPER}} .woocommerce-loop-category__title',
			)
		);

		$this->add_responsive_control(
			'title_spacing',
			array(
				'label'      => __( 'Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .woocommerce-loop-category__title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_count_style',
			array(
				'label'     => __( 'Products Count', 'premium-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_count' => 'yes',
				),
			)
		);

		$this->add_control(
			'count_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-woo-cats__count' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'count_hover_color',
			array(
				'label'     => __( 'Hover Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-woo-cats__count:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'count_typography',
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				),
				'selector' => '{{WRAPPER}} .premium-woo-cats__count',
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'count_text_shadow',
				'selector' => '{{WRAPPER}} .premium-woo-cats__count',
			)
		);

		$this->add_responsive_control(
			'count_spacing',
			array(
				'label'      => __( 'Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-woo-cats__count' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_desc_style',
			array(
				'label'     => __( 'Description', 'premium-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_desc' => 'yes',
				),
			)
		);

		$this->add_control(
			'desc_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-woo-cats__desc' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'desc_hover_color',
			array(
				'label'     => __( 'Hover Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-woo-cats__desc:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'desc_typography',
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				),
				'selector' => '{{WRAPPER}} .premium-woo-cats__desc',
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'desc_text_shadow',
				'selector' => '{{WRAPPER}} .premium-woo-cats__desc',
			)
		);

		$this->add_responsive_control(
			'desc_spacing',
			array(
				'label'      => __( 'Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-woo-cats__desc' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		do_action( 'pa_woo_cats_carousel_style', $this );

		$this->start_controls_section(
			'section_content_style',
			array(
				'label' => __( 'Content Container', 'premium-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'content_width',
			array(
				'label'     => __( 'Width (%)', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 100,
					'unit' => '%',
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-woo-cats__content-wrap' => 'width: {{SIZE}}%',
				),
			)
		);

		$this->add_control(
			'individual_style',
			array(
				'label' => __( 'Individual Style', 'premium-addons-for-elementor' ),
				'type'  => Controls_Manager::SWITCHER,
			)
		);

		do_action( 'pa_woo_cats_individual_style', $this );

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'content_background',
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '{{WRAPPER}} .premium-woo-cats__content-wrap',
				'condition' => array(
					'individual_style!' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'content_border',
				'selector'  => '{{WRAPPER}} .premium-woo-cats__content-wrap',
				'condition' => array(
					'individual_style!' => 'yes',
				),
			)
		);

		$this->add_control(
			'content_border_radius',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-woo-cats__content-wrap'  => 'border-radius: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'individual_style!' => 'yes',
				),
			)
		);

		$this->add_control(
			'content_margin',
			array(
				'label'      => __( 'Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'separator'  => 'before',
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-woo-cats__content-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'content_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-woo-cats__content-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'list_item_style',
			array(
				'label' => __( 'Category Container', 'premium-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'list_item_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} li.product-category',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'list_item_border',
				'selector' => '{{WRAPPER}} li.product-category',
			)
		);

		$this->add_control(
			'list_item_border_radius',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} li.product-category' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'list_item_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} li.product-category' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render Woo Categories widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {

		$settings = $this->get_settings();

        $papro_activated = apply_filters( 'papro_activated', false );

		if ( ! $papro_activated || version_compare( PREMIUM_PRO_ADDONS_VERSION, '2.9.19', '<' ) ) {

			if ( 'yes' === $settings['individual_style'] || 'carousel' === $settings['layout'] ) {

				?>
				<div class="premium-error-notice">
					<?php
						$message = __( 'This option is available in <b>Premium Addons Pro</b>.', 'premium-addons-for-elementor' );
						echo wp_kses_post( $message );
					?>
				</div>
				<?php
				return false;

			}
		}

		$this->add_render_attribute( 'categories_wrap', 'class', 'premium-woo-cats__container' );

		if ( 'carousel' === $settings['layout'] ) {
			$this->add_render_attribute( 'categories_wrap', 'class', 'premium-addons__v-hidden' );
		}

		$this->add_render_attribute( 'list_wrap', 'class', 'premium-woo-cats__list-wrap' );

		?>

		<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'categories_wrap' ) ); ?>>

			<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'list_wrap' ) ); ?>>

				<?php echo $this->render_product_categories(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

			</div>

		</div>

		<?php
	}

	protected function render_product_categories() {

		$settings = $this->get_settings();

		$args = array(
            'taxonomy'  => 'product_cat',
			'hide_empty' => $settings['hide_empty'],
			'orderby'    => $settings['orderby'],
			'order'      => $settings['order'],
			'number'     => $settings['categories_number'],
			'pad_counts' => true,
		);

		if ( 'top' === $settings['category_filter_rule'] ) {
			$atts['parent'] = 0;
		} elseif ( 'include' === $settings['category_filter_rule'] && is_array( $settings['category_filter'] ) ) {
			$include_ids = array_filter( array_map( 'trim', $settings['category_filter'] ) );

			$args['include'] = $include_ids;

		} elseif ( 'exclude' === $settings['category_filter_rule'] && is_array( $settings['category_filter'] ) ) {
			$exclude_ids = array_filter( array_map( 'trim', $settings['category_filter'] ) );

			$args['exclude'] = $exclude_ids;
		}

		$categories = get_terms( $args );

		if ( empty( $categories ) ) {

			$query_notice = $settings['empty_query_text'];

			Helper_Functions::render_empty_query_message( $query_notice );

			return;
		}

		/* Category Link */
		remove_action( 'woocommerce_before_subcategory', 'woocommerce_template_loop_category_link_open', 10 );
		add_action( 'woocommerce_before_subcategory', array( $this, 'render_woo_category_link' ), 10 );

		/* Category Wrapper */
		add_action( 'woocommerce_before_subcategory', array( $this, 'render_woo_category_wrap_start' ), 15 );
		add_action( 'woocommerce_after_subcategory', array( $this, 'render_woo_category_wrap_end' ), 8 );

		/* Category Title */
		remove_action( 'woocommerce_shop_loop_subcategory_title', 'woocommerce_template_loop_category_title', 10 );
		add_action( 'woocommerce_shop_loop_subcategory_title', array( $this, 'render_woo_category_title' ), 10 );

		ob_start();

		wc_set_loop_prop( 'columns', '' );

		woocommerce_product_loop_start();

		foreach ( $categories as $category ) {

            $settings['thumbnail_image'] = array(
                'id' => get_term_meta($category->term_id, 'thumbnail_id', true)
            );

            $thumbnail_html = Group_Control_Image_Size::get_attachment_image_html( $settings, 'thumbnail_image' );

			include PREMIUM_ADDONS_PATH . 'modules/woocommerce/templates/product-category.php';

			$this->category_index = $this->category_index + 1;
		}

		woocommerce_product_loop_end();

		woocommerce_reset_loop();

		$html_content = ob_get_clean();

		/* Category Link */
		add_action( 'woocommerce_before_subcategory', 'woocommerce_template_loop_category_link_open', 10 );
		remove_action( 'woocommerce_before_subcategory', array( $this, 'render_woo_category_link' ), 10 );

		/* Category Wrapper */
		remove_action( 'woocommerce_before_subcategory', array( $this, 'render_woo_category_wrap_start' ), 15 );
		remove_action( 'woocommerce_after_subcategory', array( $this, 'render_woo_category_wrap_end' ), 8 );

		/* Category Title */
		remove_action( 'woocommerce_shop_loop_subcategory_title', array( $this, 'render_woo_category_title' ), 10 );
		add_action( 'woocommerce_shop_loop_subcategory_title', 'woocommerce_template_loop_category_title', 10 );

		return $html_content;
	}

	/**
	 * Wrapper Start.
	 *
	 * @param object $category Category object.
	 */
	public function render_woo_category_link( $category ) {

		$link = get_term_link( $category, 'product_cat' );

		$settings = $this->get_settings();

		$target = 'yes' === $settings['new_tab'] ? '_blank' : '_self';

		echo '<a href="' . esc_url( $link ) . '" target="' . esc_attr( $target ) . '">';
	}

	/**
	 * Wrapper Start.
	 *
	 * @param object $category Category object.
	 */
	public function render_woo_category_wrap_start( $category ) {
		echo '<div class="premium-woo-cats__category">';
	}

	/**
	 * Wrapper End.
	 *
	 * @param object $category Category object.
	 */
	public function render_woo_category_wrap_end( $category ) {
		echo '</div>';
	}

	/**
	 * Show the subcategory title in the product loop.
	 *
	 * @param object $category Category object.
	 */
	public function render_woo_category_title( $category ) {

		$index = $this->category_index;

		$settings              = $this->get_settings();
		$one_product_string    = apply_filters( 'pa_woo_cat_product_string', __( 'Product', 'premium-addons-for-elementor' ) );
		$plural_product_string = apply_filters( 'pa_woo_cat_products_string', __( 'Products', 'premium-addons-for-elementor' ) );

		$text_tag = Helper_Functions::validate_html_tag( $settings['title_tag'] );

		$this->add_render_attribute( 'content_wrap' . $index, 'class', 'premium-woo-cats__content-wrap' );

		if ( 'yes' === $settings['individual_style'] ) {
			$cats_repeater = $settings['categories_repeater'];
			$class         = isset( $cats_repeater[ $index ] ) ? 'elementor-repeater-item-' . $cats_repeater[ $index ]['_id'] : '';

			$this->add_render_attribute( 'content_wrap' . $index, 'class', $class );
		}

		?>
			<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'content_wrap' . $index ) ); ?>>
				<<?php echo wp_kses_post( $text_tag ); ?> class="woocommerce-loop-category__title">
					<span><?php echo wp_kses_post( $category->name ); ?></span>
					<?php

					if ( 'yes' === $settings['show_count'] && 'next' === $settings['count_position'] && $category->count > 0 ) {
						$output = sprintf( '<sup class="premium-woo-cats__count">(%s)</sup>', $category->count );

						echo wp_kses_post( $output );
					}

					?>
				</<?php echo wp_kses_post( $text_tag ); ?>>

				<?php

				if ( 'yes' === $settings['show_count'] && 'line' === $settings['count_position'] && $category->count > 0 ) {
					$output = sprintf( // WPCS: XSS OK.
						/* translators: 1: number of products */
						_nx( '<p class="premium-woo-cats__count">%1$s %2$s</p>', '<p class="premium-woo-cats__count">%1$s %3$s</p>', $category->count, 'product categories', 'premium-addons-for-elementor' ), // phpcs:ignore WordPress.WP.I18n.MismatchedPlaceholders, WordPress.WP.I18n.NoHtmlWrappedStrings
						number_format_i18n( $category->count ),
						$one_product_string,
						$plural_product_string
					);

					echo wp_kses_post( $output );
				}

				?>

				<?php if ( ! empty( $category->description ) && 'yes' === $settings['show_desc'] ) : ?>
					<div class="premium-woo-cats__desc-wrap">
						<p class="premium-woo-cats__desc">
							<?php echo wp_kses_post( $category->description ); ?>
						</p>
					</div>
				<?php endif; ?>

			</div>
		<?php
	}
}
