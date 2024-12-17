<?php
/**
 * Premium Mini Cart.
 */

namespace PremiumAddons\Modules\Woocommerce\Widgets;

// Elementor Classes.
use Elementor\Utils;
use Elementor\Widget_Base;
use Elementor\Icons_Manager;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
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
 * Class Mini_Cart
 */
class Mini_Cart extends Widget_Base {

	/**
	 * Retrieve Widget Name.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function get_name() {
		return 'premium-mini-cart';
	}

	/**
	 * Retrieve Widget Title.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function get_title() {
		return __( 'Mini Cart', 'premium-addons-for-elementor' );
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
		return array( 'pa', 'premium', 'mini cart', 'cart', 'woocommerce' );
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
			'font-awesome-5-all',
			'premium-addons',
		);
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
			'wc-cart-fragments',
			'premium-mini-cart',
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
		return 'pa-mini-cart';
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
	 * Register Mini Cart Controls.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {
		$this->register_content_tab_controls();
		$this->register_style_tab_controls();
	}

	private function register_content_tab_controls() {
		$this->add_trigger_ctrls();
		$this->add_mini_cart_ctrls();
		$this->add_help_docs_tab();
	}

	private function register_style_tab_controls() {
		$this->add_trigger_style();
		$this->add_header_style();
		$this->add_items_style();
		$this->add_cart_containers_style();
		$this->add_footer_style();
		$this->add_loader_style();
	}

	private function add_trigger_ctrls() {

		$this->start_controls_section(
			'trigger_section',
			array(
				'label' => __( 'Trigger', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'cart_txt',
			array(
				'label'       => __( 'Text', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'dynamic'     => array( 'active' => true ),
			)
		);

		$this->add_control(
			'subtotal',
			array(
				'label'   => __( 'Subtotal', 'premium-addons-for-elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'icon_type',
			array(
				'label'   => __( 'Icon Type', 'premium-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'icon'      => __( 'Icon', 'premium-addons-for-elementor' ),
					'image'     => __( 'Image', 'premium-addons-for-elementor' ),
					'animation' => __( 'Lottie Animation', 'premium-addons-for-elementor' ),
					'svg'       => __( 'SVG Code', 'premium-addons-for-elementor' ),
				),
				'default' => 'icon',
			)
		);

		$this->add_control(
			'default_icons',
			array(
				'label'     => __( 'Select an Icon', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					'basket'       => __( 'Basket (Filled)', 'premium-addons-for-elementor' ),
					'basket-thin'  => __( 'Basket (Outlined)', 'premium-addons-for-elementor' ),
					'cart'         => __( 'Cart (Filled)', 'premium-addons-for-elementor' ),
					'cart-outline' => __( 'Cart (Outlined)', 'premium-addons-for-elementor' ),
					'custom'       => __( 'Custom', 'premium-addons-for-elementor' ),
				),
				'default'   => 'basket-thin',
				'condition' => array(
					'icon_type' => 'icon',
				),
			)
		);

		$this->add_control(
			'icon',
			array(
				'label'                  => __( 'Select an Icon', 'premium-addons-for-elementor' ),
				'type'                   => Controls_Manager::ICONS,
				'label_block'            => false,
				'default'                => array(
					'value'   => 'fas fa-shopping-cart',
					'library' => 'fa-solid',
				),
				'exclude_inline_options' => 'none',
				'skin'                   => 'inline',
				'condition'              => array(
					'icon_type'     => 'icon',
					'default_icons' => 'custom',
				),
			)
		);

		$this->add_control(
			'image',
			array(
				'label'     => __( 'Upload Image', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::MEDIA,
				'default'   => array(
					'url' => Utils::get_placeholder_image_src(),
				),
				'condition' => array(
					'icon_type' => 'image',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			array(
				'name'      => 'thumbnail',
				'default'   => 'woocommerce_gallery_thumbnail',
				'condition' => array(
					'icon_type' => 'image',
				),
			)
		);

		$this->add_control(
			'custom_svg',
			array(
				'label'       => __( 'SVG Code', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXTAREA,
				'description' => 'You can use these sites to create SVGs: <a href="https://danmarshall.github.io/google-font-to-svg-path/" target="_blank">Google Fonts</a> and <a href="https://boxy-svg.com/" target="_blank">Boxy SVG</a>',
				'condition'   => array(
					'icon_type' => 'svg',
				),
			)
		);

		$this->add_control(
			'lottie_url',
			array(
				'label'       => __( 'Animation JSON URL', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'description' => 'Get JSON code URL from <a href="https://lottiefiles.com/" target="_blank">here</a>',
				'label_block' => true,
				'condition'   => array(
					'icon_type' => 'animation',
				),
			)
		);

		$this->add_control(
			'lottie_loop',
			array(
				'label'        => __( 'Loop', 'premium-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'true',
				'default'      => 'true',
				'condition'    => array(
					'icon_type' => 'animation',
				),
			)
		);

		$this->add_control(
			'lottie_reverse',
			array(
				'label'        => __( 'Reverse', 'premium-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'true',
				'condition'    => array(
					'icon_type' => 'animation',
				),
			)
		);

		$this->add_responsive_control(
			'icon_size',
			array(
				'label'     => __( 'Icon Size', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 500,
						'step' => 1,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__icon-wrapper i' => 'font-size: {{SIZE}}px',
					'{{WRAPPER}} .pa-woo-mc__icon-wrapper svg, .pa-woo-mc__icon-wrapper .premium-lottie-animation' => 'width: {{SIZE}}px; height: {{SIZE}}px',
				),
				'condition' => array(
					'icon_type' => array( 'animation', 'svg', 'icon' ),
				),
			)
		);

		$this->add_responsive_control(
			'stroke_width',
			array(
				'label'     => __( 'Stroke Width', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__icon-wrapper i, {{WRAPPER}} .pa-woo-mc__icon-wrapper svg' => 'stroke-width: {{SIZE}}PX',
				),
				'condition' => array(
					'icon_type' => array( 'svg', 'icon' ),
				),
			)
		);

		$this->add_control(
			'badge_switcher',
			array(
				'label'     => __( 'Cart Items Count', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'premium-addons-for-elementor' ),
				'label_off' => esc_html__( 'Hide', 'premium-addons-for-elementor' ),
				'separator' => 'before',
				'default'   => 'yes',
			)
		);

		$this->add_control(
			'badge_hide_switcher',
			array(
				'label'     => __( 'When cart is empty', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'premium-addons-for-elementor' ),
				'label_off' => esc_html__( 'Hide', 'premium-addons-for-elementor' ),
				'default'   => 'yes',
				'condition' => array(
					'badge_switcher' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'badge_width',
			array(
				'label'     => __( 'Size (PX)', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__badge' => 'width: {{SIZE}}px; height: {{SIZE}}px;',
				),
				'condition' => array(
					'badge_switcher' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'badge_h_pos',
			array(
				'label'     => __( 'Horizontal Position (%)', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__badge' => 'left: {{SIZE}}%',
				),
				'condition' => array(
					'badge_switcher' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'badge_v_pos',
			array(
				'label'     => __( 'Vertical Position (%)', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__badge' => 'top: {{SIZE}}%',
				),
				'condition' => array(
					'badge_switcher' => 'yes',
				),
			)
		);

		$this->add_control(
			'display_options_heading',
			array(
				'label'     => esc_html__( 'Display Options', 'premium-addons-for-elementor' ),
				'separator' => 'before',
				'type'      => Controls_Manager::HEADING,
			)
		);

		$this->add_responsive_control(
			'display',
			array(
				'label'     => __( 'Display', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'row'    => array(
						'title' => __( 'Inline', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-ellipsis-h',
					),
					'column' => array(
						'title' => __( 'Block', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-ellipsis-v',
					),
				),
				'default'   => 'row',
				'toggle'    => false,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__inner-container' => 'flex-direction: {{VALUE}}',
				),
			)
		);

		$this->add_responsive_control(
			'alignment',
			array(
				'label'     => __( 'Position', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => __( 'Left', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-h-align-left',
					),
					'center'     => array(
						'title' => __( 'Center', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-h-align-center',
					),
					'flex-end'   => array(
						'title' => __( 'Right', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-h-align-right',
					),
				),
				'default'   => 'center',
				'toggle'    => false,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__outer-container' => 'justify-content: {{VALUE}}',
				),
				'condition' => array(
					'float_trigger!' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'icon_alignment',
			array(
				'label'     => __( 'Icon Alignment', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => __( 'Left', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-h-align-left',
					),
					'center'     => array(
						'title' => __( 'Center', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-h-align-center',
					),
					'flex-end'   => array(
						'title' => __( 'Right', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-h-align-right',
					),
				),
				'default'   => 'center',
				'toggle'    => false,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__icon-wrapper' => 'justify-content: {{VALUE}}',
				),
				'condition' => array(
					'display' => 'column',
				),
			)
		);

		$this->add_responsive_control(
			'text_ver_alignment',
			array(
				'label'     => __( 'Text Alignment', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => __( 'Left', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-v-align-top',
					),
					'center'     => array(
						'title' => __( 'Center', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-v-align-middle',
					),
					'flex-end'   => array(
						'title' => __( 'Right', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-v-align-bottom',
					),
				),
				'default'   => 'center',
				'toggle'    => false,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__text-wrapper' => 'align-self: {{VALUE}}',
				),
				'condition' => array(
					'display' => 'row',
				),
			)
		);

		$this->add_responsive_control(
			'icon_order',
			array(
				'label'     => __( 'Icon Order', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'toggle'    => false,
				'options'   => array(
					'0' => array(
						'title' => __( 'First', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-order-start',
					),
					'2' => array(
						'title' => __( 'Last', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-order-end',
					),
				),
				'default'   => '2',
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__icon-wrapper' => 'order: {{VALUE}}',
				),
			)
		);

		$this->add_responsive_control(
			'cart_txt_display',
			array(
				'label'     => __( 'Text Display', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'row ; align-items: center;' => array(
						'title' => __( 'Inline', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-ellipsis-h',
					),
					'column'                     => array(
						'title' => __( 'Block', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-ellipsis-v',
					),
				),
				'default'   => 'row ; align-items: center;',
				'toggle'    => false,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__text-wrapper' => 'flex-direction: {{VALUE}}',
				),
				'condition' => array(
					'subtotal'  => 'yes',
					'cart_txt!' => '',
				),
			)
		);

		$this->add_responsive_control(
			'cart_txt_order',
			array(
				'label'     => __( 'Subtotal Order', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'toggle'    => false,
				'options'   => array(
					'0' => array(
						'title' => __( 'First', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-order-start',
					),
					'2' => array(
						'title' => __( 'Last', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-order-end',
					),
				),
				'default'   => '2',
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__subtotal' => 'order: {{VALUE}}',
				),
				'condition' => array(
					'subtotal'         => 'yes',
					'cart_txt!'        => '',
					'cart_txt_display' => 'column',
				),
			)
		);

		$this->add_responsive_control(
			'cart_txt_alignment',
			array(
				'label'     => __( 'Cart Text Alignment', 'premium-addons-for-elementor' ),
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
					'{{WRAPPER}} .pa-woo-mc__text-wrapper' => 'text-align: {{VALUE}}',
				),
				'condition' => array(
					'subtotal'         => 'yes',
					'cart_txt!'        => '',
					'cart_txt_display' => 'column',
				),
			)
		);

		$this->add_responsive_control(
			'spacing',
			array(
				'label'      => __( 'Cart Spacing', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'separator'  => 'before',
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__inner-container' => 'gap: {{SIZE}}{{UNIT}}',
				),
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'  => 'subtotal',
							'value' => 'yes',
						),
						array(
							'name'     => 'cart_txt',
							'operator' => '!=',
							'value'    => '',
						),
					),
				),
			)
		);

		$this->add_responsive_control(
			'txt_spacing',
			array(
				'label'      => __( 'Cart Text Spacing', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__text-wrapper' => 'gap: {{SIZE}}{{UNIT}}',
				),
				'condition'  => array(
					'subtotal'  => 'yes',
					'cart_txt!' => '',
				),
			)
		);

		$this->add_control(
			'float_trigger',
			array(
				'label'        => __( 'Float', 'premium-addons-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'render_type'  => 'template',
				'separator'    => 'before',
				'prefix_class' => 'premium-mc-float-',
			)
		);

		$this->add_responsive_control(
			'float_hpos',
			array(
				'label'        => __( 'Horizontal Position', 'premium-addons-pro' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => array(
					'left'   => array(
						'title' => __( 'Left', 'premium-addons-pro' ),
						'icon'  => 'eicon-h-align-left',
					),
					'right'  => array(
						'title' => __( 'Right', 'premium-addons-pro' ),
						'icon'  => 'eicon-h-align-right',
					),
					'custom' => array(
						'title' => __( 'Custom', 'premium-addons-pro' ),
						'icon'  => 'eicon-cog',
					),
				),
				'prefix_class' => 'premium-mc-float-',
				'default'      => 'left',
				'condition'    => array(
					'float_trigger' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'float_custom_hpos',
			array(
				'label'     => __( 'Horizontal Offset (%)', 'premium-addons-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__inner-container' => 'left: {{SIZE}}%',
				),
				'condition' => array(
					'float_trigger' => 'yes',
					'float_hpos'    => 'custom',
				),
			)
		);

		$this->add_responsive_control(
			'float_vpos',
			array(
				'label'        => __( 'Vertical Position', 'premium-addons-pro' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => array(
					'top'    => array(
						'title' => __( 'Top', 'premium-addons-pro' ),
						'icon'  => 'eicon-arrow-up',
					),
					'middle' => array(
						'title' => __( 'Middle', 'premium-addons-pro' ),
						'icon'  => 'eicon-v-align-middle',
					),
					'bottom' => array(
						'title' => __( 'Bottom', 'premium-addons-pro' ),
						'icon'  => 'eicon-arrow-down',
					),
					'custom' => array(
						'title' => __( 'Custom', 'premium-addons-pro' ),
						'icon'  => 'eicon-cog',
					),
				),
				'prefix_class' => 'premium-mc-float-',
				'default'      => 'middle',
				'condition'    => array(
					'float_trigger' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'float_custom_vpos',
			array(
				'label'     => __( 'Vertical Offset (%)', 'premium-addons-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__inner-container' => 'top: {{SIZE}}%',
				),
				'condition' => array(
					'float_trigger' => 'yes',
					'float_vpos'    => 'custom',
				),
			)
		);

		$this->add_control(
			'behaviour',
			array(
				'label'     => __( 'Trigger Behaviour', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'separator' => 'before',
				'options'   => array(
					'toggle' => __( 'Opens Mini Cart List', 'premium-addons-for-elementor' ),
					'url'    => apply_filters( 'pa_pro_label', __( 'Redirect To Cart Page (Pro)', 'premium-addons-for-elementor' ) ),

				),
				'default'   => 'toggle',
			)
		);

		$this->add_control(
			'cart_link',
			array(
				'label'     => __( 'URL', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::URL,
				'dynamic'   => array( 'active' => true ),
				'default'   => array(
					'url' => get_permalink( wc_get_page_id( 'cart' ) ),
				),
				'condition' => array(
					'behaviour' => 'url',
				),
			)
		);

		$this->add_control(
			'woo_cta_connect',
			array(
				'label'       => __( 'Connect To Premium Woo CTA', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SWITCHER,
				'description' => __( 'Use this option to open the cart menu everytime a product is added to cart using Premium Woo CTA widget.', 'premium-addons-for-elementor' ),
				'condition'   => array(
					'behaviour' => 'url',
				),
			)
		);

		$this->end_controls_section();
	}

	private function add_mini_cart_ctrls() {

		$slide_connected_conds = array(
			'relation' => 'or',
			'terms'    => array(
				array(
					'terms' => array(
						array(
							'name'  => 'behaviour',
							'value' => 'toggle',
						),
						array(
							'name'  => 'float_trigger',
							'value' => 'yes',
						),
					),
				),
				array(
					'terms' => array(
						array(
							'name'  => 'behaviour',
							'value' => 'toggle',
						),
						array(
							'name'  => 'cart_type',
							'value' => 'slide',
						),
					),
				),
				array(
					'terms' => array(
						array(
							'name'  => 'behaviour',
							'value' => 'url',
						),
						array(
							'name'  => 'woo_cta_connect',
							'value' => 'yes',
						),
					),
				),
			),
		);

		$this->start_controls_section(
			'mini_cart_sec',
			array(
				'label'      => __( 'Mini Cart', 'premium-addons-for-elementor' ),
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'  => 'behaviour',
							'value' => 'toggle',
						),
						array(
							'terms' => array(
								array(
									'name'  => 'behaviour',
									'value' => 'url',
								),
								array(
									'name'  => 'woo_cta_connect',
									'value' => 'yes',
								),
							),
						),
					),
				),
			)
		);

		// This will only show if the behavior is to toggle.
		$this->add_control(
			'cart_type',
			array(
				'label'       => __( 'Type', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'separator'   => 'before',
				'render_type' => 'template',
				'options'     => array(
					'slide' => __( 'Slide Menu', 'premium-addons-for-elementor' ),
					'menu'  => apply_filters( 'pa_pro_label', __( 'Mini Window (Pro)', 'premium-addons-for-elementor' ) ),
				),
				'default'     => 'slide',
				'condition'   => array(
					'behaviour'      => 'toggle',
					'float_trigger!' => 'yes',
				),
			)
		);

		do_action( 'pa_woo_mini_cart_window_controls', $this );

		$this->add_responsive_control(
			'cart_dir',
			array(
				'label'              => __( 'Direction', 'premium-addons-for-elementor' ),
				'frontend_available' => true,
				'type'               => Controls_Manager::CHOOSE,
				'toggle'             => false,
				'options'            => array(
					'left'  => array(
						'title' => __( 'left', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-order-start',
					),
					'right' => array(
						'title' => __( 'Right', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-order-end',
					),
				),
				'default'            => 'right',
				'selectors'          => array(
					'{{WRAPPER}} .pa-woo-mc__content-wrapper' => '{{VALUE}}: 0',
				),
				'conditions'         => $slide_connected_conds,
			)
		);

		$this->add_control(
			'slide_effects',
			array(
				'label'      => __( 'Transition Effect', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SELECT,
				'options'    => array(
					'overlay' => __( 'Overlay', 'premium-addons-for-elementor' ),
				),
				'default'    => 'overlay',
				'conditions' => $slide_connected_conds,
			)
		);

		$this->add_control(
			'content_layout',
			array(
				'label'        => __( 'Layout', 'premium-addons-for-elementor' ),
				'type'         => Controls_Manager::SELECT,
				'prefix_class' => 'pa-show-',
				// 'render_type' => 'template',
				'options'      => array(
					'layout-1' => __( 'Layout 1', 'premium-addons-for-elementor' ),
					'layout-2' => apply_filters( 'pa_pro_label', __( 'Layout 2 (Pro)', 'premium-addons-for-elementor' ) ),
				),
				'default'      => 'layout-1',
			)
		);

		$this->add_responsive_control(
			'menu_width',
			array(
				'label'      => __( 'Width', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vw', '%', 'custom' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1000,
						'step' => 1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__content-wrapper' => 'width: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_responsive_control(
			'menu_height',
			array(
				'label'      => __( 'Height', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh', 'custom' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 500,
						'step' => 1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__items-wrapper' => 'height: {{SIZE}}{{UNIT}}',
				),
				'condition'  => array(
					'cart_type' => 'menu',
				),
			)
		);

		$this->add_control(
			'slide_overlay',
			array(
				'label'      => __( 'Overlay', 'premium-addons-pro' ),
				'type'       => Controls_Manager::SWITCHER,
				'default'    => 'yes',
				'conditions' => $slide_connected_conds,
			)
		);

		$this->add_control(
			'overlay_color',
			array(
				'label'      => __( 'Overlay Color', 'premium-addons-pro' ),
				'type'       => Controls_Manager::COLOR,
				'default'    => 'rgba(0,0,0,0.5)',
				'selectors'  => array(
					'.pa-woo-mc__overlay-{{ID}}' => 'background-color: {{VALUE}}',
				),
				'conditions' => array(
					'terms' => array(
						array(
							'name'  => 'slide_overlay',
							'value' => 'yes',
						),
						$slide_connected_conds,
					),
				),
			)
		);

		$this->add_control(
			'close_on_outside',
			array(
				'label'      => esc_html__( 'Close On Click Outside Content', 'premium-addons-pro' ),
				'type'       => Controls_Manager::SWITCHER,
				'default'    => 'yes',
				'separator'  => 'before',
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'  => 'cart_type',
							'value' => 'slide',
						),
						array(
							'terms' => array(
								array(
									'name'  => 'cart_type',
									'value' => 'menu',
								),
								array(
									'name'  => 'trigger',
									'value' => 'click',
								),
							),
						),
					),
				),
				// this should show up if
				// slide
				// mini window => click
				// test it with float
			)
		);

		$this->add_control(
			'cart_header_heading',
			array(
				'label'     => esc_html__( 'Header', 'premium-addons-for-elementor' ),
				'separator' => 'before',
				'type'      => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'cart_header',
			array(
				'label'        => __( 'Title', 'premium-addons-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'prefix_class' => 'premium-mc-title-',
				'render_type'  => 'template',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'cart_title',
			array(
				'label'       => __( 'Title', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'Your Cart',
				'label_block' => true,
				'dynamic'     => array( 'active' => true ),
				'condition'   => array(
					'cart_header' => 'yes',
				),
			)
		);

		$this->add_control(
			'close_icon',
			array(
				'label'                  => __( 'Select an Icon', 'premium-addons-for-elementor' ),
				'type'                   => Controls_Manager::ICONS,
				'label_block'            => false,
				'exclude_inline_options' => 'none',
				'default'                => array(
					'value'   => 'fas fa-times',
					'library' => 'fa-solid',
				),
				'skin'                   => 'inline',
				'conditions'             => $slide_connected_conds,
			)
		);

		$this->add_control(
			'close_icon_order',
			array(
				'label'      => __( 'Icon Position', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::CHOOSE,
				'toggle'     => false,
				'options'    => array(
					'2' => array(
						'title' => __( 'left', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-order-start',
					),
					'0' => array(
						'title' => __( 'Right', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-order-end',
					),
				),
				'default'    => '0',
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__cart-title' => 'order: {{VALUE}}',
				),
				'conditions' => array(
					'terms' => array(
						array(
							'name'     => 'cart_title',
							'operator' => '!==',
							'value'    => '',
						),
						array(
							'name'  => 'cart_header',
							'value' => 'yes',
						),
						$slide_connected_conds,
					),
				),
			)
		);

		$this->add_control(
			'icon_pos',
			array(
				'label'      => __( 'Icon Position', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::CHOOSE,
				'toggle'     => false,
				'options'    => array(
					'margin-right' => array(
						'title' => __( 'left', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-order-start',
					),
					'margin-left'  => array(
						'title' => __( 'Right', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-order-end',
					),
				),
				'default'    => 'margin-left',
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__close-button' => '{{VALUE}}: auto',
				),
				'conditions' => array(
					'terms' => array(
						array(
							'relation' => 'or',
							'terms'    => array(
								array(
									'name'     => 'cart_header',
									'operator' => '!==',
									'value'    => 'yes',
								),
								array(
									'terms' => array(
										array(
											'name'  => 'cart_title',
											'value' => '',
										),
										array(
											'name'  => 'cart_header',
											'value' => 'yes',
										),
									),
								),
							),
						),
						$slide_connected_conds,
					),
				),
			)
		);

		$this->add_responsive_control(
			'close_icon_size',
			array(
				'label'      => __( 'Icon Size', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__close-button i' => 'font-size: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .pa-woo-mc__close-button svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}',
				),
				'conditions' => $slide_connected_conds,
			)
		);

		$this->add_responsive_control(
			'title_pos',
			array(
				'label'     => __( 'Alignment', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => __( 'Left', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-h-align-left',
					),
					'center'     => array(
						'title' => __( 'Center', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-h-align-center',
					),
					'flex-end'   => array(
						'title' => __( 'Right', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-h-align-right',
					),
				),
				'default'   => 'center',
				'toggle'    => false,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__cart-header' => 'justify-content: {{VALUE}}',
				),
				'condition' => array(
					'cart_header'    => 'yes',
					'float_trigger!' => 'yes',
					'cart_type'      => 'menu',
				),
			)
		);

		$this->add_control(
			'cart_content_heading',
			array(
				'label'     => esc_html__( 'Products', 'premium-addons-for-elementor' ),
				'separator' => 'before',
				'type'      => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'thumb_heading',
			array(
				'label'     => esc_html__( 'Thumbnail', 'premium-addons-for-elementor' ),
				'separator' => 'before',
				'type'      => Controls_Manager::HEADING,
			)
		);

		$this->add_responsive_control(
			'item_thumb',
			array(
				'label'      => __( 'Image Size', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 1000,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__product-thumbnail'  => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'thumb_fit',
			array(
				'label'     => __( 'Image Fit', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					''        => __( 'Default', 'premium-addons-pro' ),
					'fill'    => __( 'Fill', 'premium-addons-pro' ),
					'cover'   => __( 'Cover', 'premium-addons-pro' ),
					'contain' => __( 'Contain', 'premium-addons-pro' ),
				),
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__product-thumbnail img' => 'object-fit: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'thumb_info_spacing',
			array(
				'label'      => __( 'Spacing', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 500,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__item-wrapper' => 'column-gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'product_info_heading',
			array(
				'label'     => esc_html__( 'Product Info', 'premium-addons-for-elementor' ),
				'separator' => 'before',
				'type'      => Controls_Manager::HEADING,
			)
		);

		$this->add_responsive_control(
			'v_align',
			array(
				'label'     => __( 'Vertical Alignment', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'start' => array(
						'title' => __( 'Top', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-align-start-v',
					),
					'center'     => array(
						'title' => __( 'Center', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-align-center-v',
					),
					'end'   => array(
						'title' => __( 'Bottom', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-align-end-v',
					),
					'stretch'                        => array(
						'title' => __( 'stretch', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-align-stretch-v',
					),
				),
				'selectors_dictionary' => array(
					'start'  => 'align-self: flex-start',
					'center' => 'align-self: center',
					'end'   => 'align-self: flex-end',
					'stretch'   => 'stretch'
				),
				'default'   => 'center',
				'toggle'    => false,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__product-data' => '{{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'product_info_spacing',
			array(
				'label'      => __( 'Spacing', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__product-data' => 'gap: {{SIZE}}{{UNIT}}',
				),
				'condition'  => array(
					'v_align!' => '',
				),
			)
		);

		$this->add_control(
			'remove_icon',
			array(
				'label'        => __( 'Remove Item', 'premium-addons-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'separator'    => 'before',
				'default'      => 'yes',
				'prefix_class' => 'pa-woo-mc__remove-icon-',
			)
		);

		$this->add_responsive_control(
			'remove_type',
			array(
				'label'        => __( 'Type', 'premium-addons-for-elementor' ),
				'type'         => Controls_Manager::CHOOSE,
				'prefix_class' => 'pa-show-trash-',
				'options'      => array(
					'text' => array(
						'title' => __( 'Text', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-pencil',
					),
					'icon' => array(
						'title' => __( 'Icon', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-trash-o',
					),
				),
				'default'      => 'icon',
				'toggle'       => false,
				'condition'    => array(
					'remove_icon' => 'yes',
				),
			)
		);

		$this->add_control(
			'remove_txt',
			array(
				'label'       => __( 'Text', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'render_type' => 'template',
				'default'     => 'Remove',
				'label_block' => true,
				'dynamic'     => array( 'active' => true ),
				'condition'   => array(
					'remove_icon' => 'yes',
					'remove_type' => 'text',
				),
			)
		);

		$this->add_responsive_control(
			'remove_icon_size',
			array(
				'label'      => __( 'Icon Size', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__remove-item' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}',
				),
				'condition'  => array(
					'remove_icon' => 'yes',
					'remove_type' => 'icon',
				),
			)
		);

		$this->add_responsive_control(
			'remove_icon_align',
			array(
				'label'       => __( 'Alignment', 'premium-addons-for-elementor' ),
				'description' => __( 'This option works better when the product title is displayed on more than one line.', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::CHOOSE,
				'options'     => array(
					'flex-start' => array(
						'title' => __( 'Top', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-v-align-top',
					),
					'center'     => array(
						'title' => __( 'Center', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-v-align-middle',
					),
					'flex-end'   => array(
						'title' => __( 'Bottom', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-v-align-bottom',
					),
				),
				'default'     => 'flex-start',
				'toggle'      => false,
				'selectors'   => array(
					'{{WRAPPER}} .pa-woo-mc__remove-item' => 'align-self: {{VALUE}};',
				),
				'condition'   => array(
					'remove_icon'    => 'yes',
					'content_layout' => 'layout-1',
				),
			)
		);

		$this->add_responsive_control(
			'qty_input_width',
			array(
				'label'      => __( 'Quantity Width', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'separator'  => 'before',
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__input' => 'width: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_control(
			'qty_controls',
			array(
				'label'        => __( 'Quantity Controls', 'premium-addons-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'separator'    => 'before',
				'default'      => 'yes',
				'prefix_class' => 'pa-woo-mc__qty-btn-',
			)
		);

		$this->add_responsive_control(
			'qty_controls_size',
			array(
				'label'      => __( 'Controls Size', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__qty-btn' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}',
				),
				'condition'  => array(
					'qty_controls' => 'yes',
				),
			)
		);

		$this->add_control(
			'separator',
			array(
				'label'        => __( 'Items Divider', 'premium-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'prefix_class' => 'pa-mc-separator-',
				'default'      => 'yes',
				'separator'    => 'before',
			)
		);

		$this->add_control(
			'divider_style',
			array(
				'label'     => __( 'Style', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					'solid'  => __( 'Solid', 'premium-addons-for-elementor' ),
					'double' => __( 'Double', 'premium-addons-for-elementor' ),
					'dotted' => __( 'Dotted', 'premium-addons-for-elementor' ),
					'dashed' => __( 'Dashed', 'premium-addons-for-elementor' ),
				),
				'default'   => 'solid',
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__item-divider' => 'border-style: {{VALUE}};',
				),
				'condition' => array(
					'separator' => 'yes',
				),

			)
		);

		$this->add_control(
			'pa_txt_color_divider_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__item-divider' => 'border-color: {{VALUE}}',
				),
				'condition' => array(
					'cart_txt!' => '',
				),
				'condition' => array(
					'separator' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'divider_height',
			array(
				'label'     => __( 'Thickness', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__item-divider' => 'border-top-width: {{SIZE}}px;',
				),
				'condition' => array(
					'separator' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'divider_hor_spacing',
			array(
				'label'      => __( 'Divider Horizontal Spacing', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}}.pa-mc-separator-yes .pa-woo-mc__item-divider'  => 'margin-left: {{SIZE}}{{UNIT}}; margin-right: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'separator' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'items_spacing',
			array(
				'label'      => __( 'Items Spacing', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}}:not(.pa-mc-separator-yes) .pa-woo-mc__items-wrapper'  => 'row-gap: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.pa-mc-separator-yes .pa-woo-mc__item-divider'  => 'margin-top: {{SIZE}}{{UNIT}}; margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'cart_footer_heading',
			array(
				'label'     => esc_html__( 'Footer', 'premium-addons-for-elementor' ),
				'separator' => 'before',
				'type'      => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'footer_subtotal',
			array(
				'label'   => __( 'Subtotal', 'premium-addons-pro' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'subtotal_txt',
			array(
				'label'       => __( 'Text', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'render_type' => 'template',
				'default'     => 'Subtotal {{count}} items',
				'description' => __( 'Use this option to add a text of your choice, and use the {{count}} placeholder to add your items\' count.', 'premium-addons-for-elementor' ),
				'label_block' => true,
				'dynamic'     => array( 'active' => true ),
				'condition'   => array(
					'footer_subtotal' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'footer_subtotal_pos',
			array(
				'label'     => __( 'Position', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => __( 'Left', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-h-align-left',
					),
					'center'     => array(
						'title' => __( 'Center', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-h-align-center',
					),
					'flex-end'   => array(
						'title' => __( 'Right', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-h-align-right',
					),
				),
				'default'   => 'flex-end',
				'toggle'    => false,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__cart-subtotal' => 'justify-content: {{VALUE}}',
				),
				'condition' => array(
					'footer_subtotal' => 'yes',
					'subtotal_txt'    => '',
				),
			)
		);

		$this->add_control(
			'footer_subtotal_order',
			array(
				'label'     => __( 'Order', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'0' => array(
						'title' => __( 'Defaul', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-order-start',
					),
					'2' => array(
						'title' => __( 'Reverse', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-order-end',
					),
				),
				'default'   => '0',
				'toggle'    => false,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__subtotal-heading' => 'order: {{VALUE}}',
				),
				'condition' => array(
					'footer_subtotal' => 'yes',
					'subtotal_txt!'   => '',
				),
			)
		);

		$this->add_control(
			'view_cart',
			array(
				'label'     => __( 'View Cart', 'premium-addons-pro' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'separator' => 'before',
			)
		);

		$this->add_control(
			'checkout',
			array(
				'label'   => __( 'Checkout', 'premium-addons-pro' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_responsive_control(
			'cart_btn_display',
			array(
				'label'      => __( 'Display', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::CHOOSE,
				'options'    => array(
					'nowrap' => array(
						'title' => __( 'Inline', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-ellipsis-h',
					),
					'wrap'   => array(
						'title' => __( 'Block', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-ellipsis-v',
					),
				),
				'default'    => 'wrap',
				'toggle'     => false,
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__cart-buttons' => 'flex-wrap: {{VALUE}}',
				),
				'conditions' => array(
					'terms' => array(
						array(
							'name'  => 'checkout',
							'value' => 'yes',
						),
						array(
							'name'  => 'view_cart',
							'value' => 'yes',
						),
					),
				),
			)
		);

		$this->add_control(
			'cart_btn_order',
			array(
				'label'      => __( 'Order', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::CHOOSE,
				'options'    => array(
					'0' => array(
						'title' => __( 'Defaul', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-order-start',
					),
					'2' => array(
						'title' => __( 'Reverse', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-order-end',
					),
				),
				'default'    => '0',
				'toggle'     => false,
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__view-cart' => 'order: {{VALUE}}',
				),
				'conditions' => array(
					'terms' => array(
						array(
							'name'  => 'checkout',
							'value' => 'yes',
						),
						array(
							'name'  => 'view_cart',
							'value' => 'yes',
						),
					),
				),
			)
		);

		$this->add_responsive_control(
			'cart_btn_spacing',
			array(
				'label'      => __( 'Spacing', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__cart-buttons' => 'gap: {{SIZE}}{{UNIT}}',
				),
				'conditions' => array(
					'terms' => array(
						array(
							'name'  => 'checkout',
							'value' => 'yes',
						),
						array(
							'name'  => 'view_cart',
							'value' => 'yes',
						),
					),
				),
			)
		);

		$this->end_controls_section();
	}

	private function add_help_docs_tab() {

		$this->start_controls_section(
			'section_pa_docs',
			array(
				'label' => __( 'Help & Docs', 'premium-addons-for-elementor' ),
			)
		);

		$doc1_url = Helper_Functions::get_campaign_link( 'https://premiumaddons.com/docs/elementor-woocommerce-mini-cart-widget-tutorial/', 'editor-page', 'wp-editor', 'get-support' );

		$this->add_control(
			'doc_1',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => sprintf( '<a href="%s" target="_blank">%s</a>', $doc1_url, __( 'Gettings started »', 'premium-addons-for-elementor' ) ),
				'content_classes' => 'editor-pa-doc',
			)
		);

		$this->end_controls_section();
	}

	private function add_trigger_style() {

		$this->start_controls_section(
			'trigger_style',
			array(
				'label' => __( 'Trigger', 'premium-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'cart_txt_typo',
				'label'     => esc_html__( 'Text Typography', 'premium-addons-for-elementor' ),
				'selector'  => '{{WRAPPER}} .pa-woo-mc__text',
				'condition' => array(
					'cart_txt!' => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'subtotal_typo',
				'label'     => esc_html__( 'Subtotal Typography', 'premium-addons-for-elementor' ),
				'selector'  => '{{WRAPPER}} .pa-woo-mc__subtotal',
				'condition' => array(
					'subtotal' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'badge_typo',
				'label'     => esc_html__( 'Count Typography', 'premium-addons-for-elementor' ),
				'selector'  => '{{WRAPPER}} .pa-woo-mc__badge',
				'condition' => array(
					'badge_switcher' => 'yes',
				),
			)
		);

		$this->add_control(
			'badge_rad',
			array(
				'label'      => __( 'Count Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__badge' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'badge_switcher' => 'yes',
				),
			)
		);

		$this->start_controls_tabs(
			'trigger_style_tabs'
		);

		$this->start_controls_tab(
			'triggle_style_normal',
			array(
				'label' => esc_html__( 'Normal', 'remium-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'pa_txt_color',
			array(
				'label'     => __( 'Cart Text Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__text' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'cart_txt!' => '',
				),
			)
		);

		$this->add_control(
			'pa_txt_color_subtotal',
			array(
				'label'     => __( 'Subtotal Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__subtotal' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'subtotal' => 'yes',
				),
			)
		);

		$this->add_control(
			'icon_color',
			array(
				'label'     => __( 'Icon Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__icon-wrapper i' => 'color: {{VALUE}}',
					'{{WRAPPER}} .pa-woo-mc__icon-wrapper svg, {{WRAPPER}} .pa-woo-mc__icon-wrapper svg *' => 'fill: {{VALUE}};',
				),
				'condition' => array(
					'icon_type' => array( 'svg', 'icon' ),
				),
			)
		);

		$this->add_control(
			'stroke_color',
			array(
				'label'     => __( 'Icon Stroke Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__icon-wrapper i, {{WRAPPER}} .pa-woo-mc__icon-wrapper svg' => 'stroke: {{VALUE}}',
				),
				'condition' => array(
					'icon_type' => array( 'svg', 'icon' ),
				),
			)
		);

		$this->add_control(
			'badge_heading',
			array(
				'label'     => esc_html__( 'Count Badge', 'premium-addons-for-elementor' ),
				'separator' => 'before',
				'type'      => Controls_Manager::HEADING,
				'condition' => array(
					'badge_switcher' => 'yes',
				),
			)
		);

		$this->add_control(
			'pa_btn_color_badge',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__badge' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'badge_switcher' => 'yes',
				),
			)
		);

		$this->add_control(
			'pa_btn_bg_badge',
			array(
				'label'     => __( 'Background Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__badge' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'badge_switcher' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'pa_border_color_badge',
				'selector'  => '{{WRAPPER}} .pa-woo-mc__badge',
				'condition' => array(
					'badge_switcher' => 'yes',
				),
			)
		);

		$this->add_control(
			'cont_heading',
			array(
				'label'     => esc_html__( 'Container', 'premium-addons-for-elementor' ),
				'separator' => 'before',
				'type'      => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'pa_btn_bg',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .pa-woo-mc__inner-container',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'pa_border_color_cont',
				'selector' => '{{WRAPPER}} .pa-woo-mc__inner-container',
			)
		);

		$this->add_responsive_control(
			'cont_border_rad',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__inner-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'cont_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__inner-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'triggle_style_hov',
			array(
				'label' => esc_html__( 'Hover', 'remium-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'pa_txt_color_hov',
			array(
				'label'     => __( 'Cart Text Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__link:hover .pa-woo-mc__text,
					{{WRAPPER}} .pa-woo-mc__inner-container:hover .pa-woo-mc__text' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'cart_txt!' => '',
				),
			)
		);

		$this->add_control(
			'pa_txt_color_subtotal_hov',
			array(
				'label'     => __( 'Subtotal Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__link:hover .pa-woo-mc__subtotal,
					 {{WRAPPER}} .pa-woo-mc__inner-container:hover .pa-woo-mc__subtotal' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'subtotal' => 'yes',
				),
			)
		);

		$this->add_control(
			'icon_color_hov',
			array(
				'label'     => __( 'Icon Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__link:hover .pa-woo-mc__icon-wrapper i,
					{{WRAPPER}} .pa-woo-mc__inner-container:hover .pa-woo-mc__icon-wrapper i' => 'color: {{VALUE}}',
					'{{WRAPPER}} .pa-woo-mc__link:hover .pa-woo-mc__icon-wrapper svg,
					{{WRAPPER}} .pa-woo-mc__inner-container:hover .pa-woo-mc__icon-wrapper svg,
					{{WRAPPER}} .pa-woo-mc__inner-container:hover .pa-woo-mc__icon-wrapper svg *' => 'fill: {{VALUE}};',
				),
				'condition' => array(
					'icon_type' => array( 'svg', 'icon' ),
				),
			)
		);

		$this->add_control(
			'stroke_color_hov',
			array(
				'label'     => __( 'Icon Stroke Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__link:hover .pa-woo-mc__icon-wrapper i,
					 {{WRAPPER}} .pa-woo-mc__link:hover .pa-woo-mc__icon-wrapper svg,
					 {{WRAPPER}} .pa-woo-mc__inner-container:hover .pa-woo-mc__icon-wrapper i,
					 {{WRAPPER}} .pa-woo-mc__inner-container:hover .pa-woo-mc__icon-wrapper svg' => 'stroke: {{VALUE}}',
				),
				'condition' => array(
					'icon_type' => array( 'svg', 'icon' ),
				),
			)
		);

		$this->add_control(
			'badge_heading_hov',
			array(
				'label'     => esc_html__( 'Count Badge', 'premium-addons-for-elementor' ),
				'separator' => 'before',
				'type'      => Controls_Manager::HEADING,
				'condition' => array(
					'badge_switcher' => 'yes',
				),
			)
		);

		$this->add_control(
			'pa_btn_color_badge_hov',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__link:hover .pa-woo-mc__badge,
					{{WRAPPER}} .pa-woo-mc__inner-container:hover .pa-woo-mc__badge' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'badge_switcher' => 'yes',
				),
			)
		);

		$this->add_control(
			'pa_btn_bg_hover_badge',
			array(
				'label'     => __( 'Background Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__link:hover .pa-woo-mc__badge,
					 {{WRAPPER}} .pa-woo-mc__inner-container:hover .pa-woo-mc__badge' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'badge_switcher' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'pa_border_color_badge_hov',
				'selector'  => '{{WRAPPER}} .pa-woo-mc__link:hover .pa-woo-mc__badge, {{WRAPPER}} .pa-woo-mc__inner-container:hover .pa-woo-mc__badge',
				'condition' => array(
					'badge_switcher' => 'yes',
				),
			)
		);

		$this->add_control(
			'cont_heading_hov',
			array(
				'label'     => esc_html__( 'Container', 'premium-addons-for-elementor' ),
				'separator' => 'before',
				'type'      => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'pa_btn_bg_hover',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .pa-woo-mc__inner-container:hover, {{WRAPPER}} .pa-woo-mc__link:hover .pa-woo-mc__inner-container',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'pa_border_color_cont_hov',
				'selector' => '{{WRAPPER}} .pa-woo-mc__inner-container:hover, {{WRAPPER}} .pa-woo-mc__link:hover .pa-woo-mc__inner-container',
			)
		);

		$this->add_responsive_control(
			'cont_border_rad_hov',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__inner-container:hover, {{WRAPPER}} .pa-woo-mc__link:hover .pa-woo-mc__inner-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'cont_padding_hov',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__inner-container:hover, {{WRAPPER}} .pa-woo-mc__link:hover .pa-woo-mc__inner-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function add_cart_containers_style() {

		$this->start_controls_section(
			'cart_conts_style_sec',
			array(
				'label'      => __( 'Cart Containers', 'premium-addons-for-elementor' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'  => 'behaviour',
							'value' => 'toggle',
						),
						array(
							'terms' => array(
								array(
									'name'  => 'behaviour',
									'value' => 'url',
								),
								array(
									'name'  => 'woo_cta_connect',
									'value' => 'yes',
								),
							),
						),
					),
				),
			)
		);

		$this->add_control(
			'items_style_heading',
			array(
				'label'     => esc_html__( 'Items', 'premium-addons-for-elementor' ),
				'separator' => 'before',
				'type'      => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'pa_ele_bg_item_cont',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .pa-woo-mc__items-wrapper',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'pa_border_color_item_cont',
				'selector' => '{{WRAPPER}} .pa-woo-mc__items-wrapper',
			)
		);

		$this->add_responsive_control(
			'item_cont_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__items-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'mc_cont_style_heading',
			array(
				'label'     => esc_html__( 'Mini Cart', 'premium-addons-for-elementor' ),
				'separator' => 'before',
				'type'      => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'pa_ele_bg_outer_cont',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .pa-woo-mc__content-wrapper',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'pa_border_color_outer_cont',
				'selector' => '{{WRAPPER}} .pa-woo-mc__content-wrapper',
			)
		);

		do_action( 'pa_woo_mini_cart_window_style_controls', $this );

		$this->add_responsive_control(
			'outer_cont_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__content-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	private function add_items_style() {

		$this->start_controls_section(
			'items_style_sec',
			array(
				'label'      => __( 'Cart Items', 'premium-addons-for-elementor' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'  => 'behaviour',
							'value' => 'toggle',
						),
						array(
							'terms' => array(
								array(
									'name'  => 'behaviour',
									'value' => 'url',
								),
								array(
									'name'  => 'woo_cta_connect',
									'value' => 'yes',
								),
							),
						),
					),
				),
			)
		);

		$this->add_control(
			'thumb_style_heading',
			array(
				'label'     => esc_html__( 'Thumbnail', 'premium-addons-for-elementor' ),
				'separator' => 'before',
				'type'      => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'img_shadow',
				'selector' => '{{WRAPPER}} .pa-woo-mc__product-thumbnail img',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'pa_border_color_img',
				'selector' => '{{WRAPPER}} .pa-woo-mc__product-thumbnail img',
			)
		);

		$this->add_responsive_control(
			'img_rad',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__product-thumbnail img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'title_style_heading',
			array(
				'label'     => esc_html__( 'Title', 'premium-addons-for-elementor' ),
				'separator' => 'before',
				'type'      => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'product_title_typo',
				'label'    => esc_html__( 'Text Typography', 'premium-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .pa-woo-mc__title',
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__title' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'title_color_hov',
			array(
				'label'     => __( 'Hover Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__title:hover' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'trash_style_heading',
			array(
				'label'     => esc_html__( 'Remove Icon', 'premium-addons-for-elementor' ),
				'separator' => 'before',
				'type'      => Controls_Manager::HEADING,
				'condition' => array(
					'remove_icon' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'remove_txt_typo',
				'label'     => esc_html__( 'Typography', 'premium-addons-for-elementor' ),
				'selector'  => '{{WRAPPER}} .pa-woo-mc__remove-item span',
				'condition' => array(
					'remove_icon' => 'yes',
				),
			)
		);

		$this->add_control(
			'pa_btn_color_remove',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__remove-item svg, {{WRAPPER}} .pa-woo-mc__remove-item svg *' => 'fill: {{VALUE}}',
					'{{WRAPPER}} .pa-woo-mc__remove-item span' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'remove_icon' => 'yes',
				),
			)
		);

		$this->add_control(
			'pa_btn_color_hov_remove',
			array(
				'label'     => __( 'Hover Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__remove-item:hover svg, {{WRAPPER}} .pa-woo-mc__remove-item:hover svg *' => 'fill: {{VALUE}}',
					'{{WRAPPER}} .pa-woo-mc__remove-item:hover span' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'remove_icon' => 'yes',
				),
			)
		);

		$this->add_control(
			'price_style_heading',
			array(
				'label'     => esc_html__( 'Price', 'premium-addons-for-elementor' ),
				'separator' => 'before',
				'type'      => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'price_typo',
				'label'    => esc_html__( 'Typography', 'premium-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .pa-woo-mc__item-price',
			)
		);

		$this->add_control(
			'pa_txt_color_price',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__item-price' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'qty_style_heading',
			array(
				'label'     => esc_html__( 'Quantity', 'premium-addons-for-elementor' ),
				'separator' => 'before',
				'type'      => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'qty_typo',
				'label'    => esc_html__( 'Typography', 'premium-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .pa-woo-mc__input[type="number"]',
			)
		);

		$this->add_control(
			'pa_txt_color_qty',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__input[type="number"]' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'qty_cta_style_heading',
			array(
				'label'     => esc_html__( '+/- Buttons', 'premium-addons-for-elementor' ),
				'separator' => 'before',
				'type'      => Controls_Manager::HEADING,
			)
		);

		$this->start_controls_tabs(
			'qty_cta_tabs',
		);

		$this->start_controls_tab(
			'qty_cta_normal',
			array(
				'label' => esc_html__( 'Normal', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'pa_btn_color_qta_cta',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__qty-btn, {{WRAPPER}} .pa-woo-mc__qty-btn *' => 'fill: {{VALUE}}',
				),
				'condition' => array(
					'qty_controls' => 'yes',
				),
			)
		);

		$this->add_control(
			'pa_btn_bg_qty_cta',
			array(
				'label'     => __( 'Background Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__qty-btn' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'qty_controls' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'qta_btn_shadow',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pa-woo-mc__qty-btn',
				'condition' => array(
					'qty_controls' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'pa_border_color_qta_cta',
				'selector'  => '{{WRAPPER}} .pa-woo-mc__qty-btn',
				'condition' => array(
					'qty_controls' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'qta_btn_rad',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__qty-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'qty_controls' => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'qta_btn_tab_hov',
			array(
				'label' => esc_html__( 'Hover', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'pa_btn_color_hov',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__qty-btn:hover' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'qty_controls' => 'yes',
				),
			)
		);

		$this->add_control(
			'pa_btn_color_qta_cta_hov',
			array(
				'label'     => __( 'Background Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__qty-btn:hover, {{WRAPPER}} .pa-woo-mc__qty-btn:hover *' => 'fill: {{VALUE}}',
				),
				'condition' => array(
					'qty_controls' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'qty_btn_shadow_hov',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pa-woo-mc__qty-btn:hover',
				'condition' => array(
					'qty_controls' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'pa_border_color_qty_cta_hov',
				'selector'  => '{{WRAPPER}} .pa-woo-mc__qty-btn:hover',
				'condition' => array(
					'qty_controls' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'qty_btn_rad_hov',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__qty-btn:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'qty_controls' => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'qty_btn_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'separator'  => 'before',
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__qty-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'qty_controls' => 'yes',
				),
			)
		);

		$this->add_control(
			'qty_cont_heading',
			array(
				'label'     => esc_html__( 'Quantity Container', 'premium-addons-for-elementor' ),
				'separator' => 'before',
				'type'      => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'pa_ele_bg_qty-cont',
			array(
				'label'     => __( 'Background Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__item-qty' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'qty_cont_shadow',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pa-woo-mc__item-qty',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'pa_border_color_qty_cont',
				'selector' => '{{WRAPPER}} .pa-woo-mc__item-qty',
			)
		);

		$this->add_responsive_control(
			'qty_cont_rad',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__item-qty' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'qty_cont_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__item-qty' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	private function add_header_style() {

		$this->start_controls_section(
			'header_style',
			array(
				'label'      => __( 'Cart Header', 'premium-addons-for-elementor' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'  => 'behaviour',
							'value' => 'toggle',
						),
						array(
							'terms' => array(
								array(
									'name'  => 'behaviour',
									'value' => 'url',
								),
								array(
									'name'  => 'woo_cta_connect',
									'value' => 'yes',
								),
							),
						),
					),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'cart_title_typo',
				'label'     => esc_html__( 'Title Typography', 'premium-addons-for-elementor' ),
				'selector'  => '{{WRAPPER}} .pa-woo-mc__cart-title',
				'condition' => array(
					'cart_header' => 'yes',
					'cart_title!' => '',
				),
			)
		);

		$this->add_control(
			'pa_heading_color',
			array(
				'label'     => __( 'Title Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__cart-title' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'cart_header' => 'yes',
					'cart_title!' => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'      => 'cart_title_shadow',
				'selector'  => '{{WRAPPER}} .pa-woo-mc__cart-title',
				'condition' => array(
					'cart_header' => 'yes',
					'cart_title!' => '',
				),
			)
		);

		$this->add_control(
			'cicon_heading',
			array(
				'label'     => esc_html__( 'Close Icon', 'premium-addons-for-elementor' ),
				'separator' => 'before',
				'type'      => Controls_Manager::HEADING,
			)
		);

		$this->start_controls_tabs(
			'cicon_tabs'
		);

		$this->start_controls_tab(
			'cicon_tab_normal',
			array(
				'label' => esc_html__( 'Normal', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'pa_btn_color_cicon',
			array(
				'label'     => __( 'Icon Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__close-button i' => 'color: {{VALUE}}',
					'{{WRAPPER}} .pa-woo-mc__close-button svg, {{WRAPPER}} .pa-woo-mc__close-button svg *' => 'fill: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'pa_btn_bg_cicon',
			array(
				'label'     => __( 'Background Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__close-button' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'cicon_shadow',
				'selector' => '{{WRAPPER}} .pa-woo-mc__close-button',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'pa_border_color_cicon',
				'selector' => '{{WRAPPER}} .pa-woo-mc__close-button',
			)
		);

		$this->add_responsive_control(
			'cicon_rad',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__close-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'cicon_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__close-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'cicon_tab_hov',
			array(
				'label' => esc_html__( 'Hover', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'pa_btn_color_cicon_hov',
			array(
				'label'     => __( 'Hover Icon Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__close-button:hover i' => 'color: {{VALUE}}',
					'{{WRAPPER}} .pa-woo-mc__close-button:hover svg, {{WRAPPER}} .pa-woo-mc__close-button:hover svg *' => 'fill: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'pa_btn_bg_hover_cicon',
			array(
				'label'     => __( 'Background Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__close-button:hover' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'cicon_shadow_hov',
				'selector' => '{{WRAPPER}} .pa-woo-mc__close-button:hover',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'pa_border_color_cicon_hov',
				'selector' => '{{WRAPPER}} .pa-woo-mc__close-button:hover',
			)
		);

		$this->add_responsive_control(
			'cicon_rad_hov',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__close-button:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'cicon_padding_hov',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__close-button:hover' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'header_cont_heading',
			array(
				'label'     => esc_html__( 'Container', 'premium-addons-for-elementor' ),
				'separator' => 'before',
				'type'      => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'pa_btn_bg_header',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .pa-woo-mc__cart-header',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'pa_border_color_header',
				'selector' => '{{WRAPPER}} .pa-woo-mc__cart-header',
			)
		);

		$this->add_responsive_control(
			'header_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__cart-header' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	private function add_footer_style() {

		$this->start_controls_section(
			'footer_style',
			array(
				'label'      => __( 'Cart Footer', 'premium-addons-for-elementor' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'conditions' => array(
					'terms' => array(
						array(
							'relation' => 'or',
							'terms'    => array(
								array(
									'name'  => 'behaviour',
									'value' => 'toggle',
								),
								array(
									'terms' => array(
										array(
											'name'  => 'behaviour',
											'value' => 'url',
										),
										array(
											'name'  => 'woo_cta_connect',
											'value' => 'yes',
										),
									),
								),
							),
						),
						array(
							'relation' => 'or',
							'terms'    => array(
								array(
									'name'  => 'footer_subtotal',
									'value' => 'yes',
								),
								array(
									'name'  => 'view_cart',
									'value' => 'yes',
								),
								array(
									'name'  => 'checkout',
									'value' => 'yes',
								),
							),
						),
					),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'footer_sub_text_typo',
				'label'     => esc_html__( 'Subtotal Text Typography', 'premium-addons-for-elementor' ),
				'selector'  => '{{WRAPPER}} .pa-woo-mc__subtotal-heading',
				'condition' => array(
					'footer_subtotal' => 'yes',
					'subtotal_txt!'   => '',
				),
			)
		);

		$this->add_control(
			'pa_txt_color_footer_heading',
			array(
				'label'     => __( 'Subtotal Text Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__subtotal-heading' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'footer_subtotal' => 'yes',
					'subtotal_txt!'   => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'footer_amount_typo',
				'label'     => esc_html__( 'Subtotal Amount Typography', 'premium-addons-for-elementor' ),
				'selector'  => '{{WRAPPER}} .pa-woo-mc__cart-footer .pa-woo-mc__subtotal',
				'condition' => array(
					'footer_subtotal' => 'yes',
				),
			)
		);

		$this->add_control(
			'pa_txt_color_footer_amount',
			array(
				'label'     => __( 'Subtotal Amount Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__cart-footer .pa-woo-mc__subtotal' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'footer_subtotal' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'pa_ele_bg',
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '{{WRAPPER}} .pa-woo-mc__cart-subtotal',
				'condition' => array(
					'view_cart' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'footer_sub_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'separator'  => 'before',
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__cart-subtotal' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'footer_btn_heading',
			array(
				'label'     => esc_html__( 'Cart CTA', 'premium-addons-for-elementor' ),
				'separator' => 'before',
				'type'      => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'       => 'footer_btn_typo',
				'selector'   => '{{WRAPPER}} .pa-woo-mc__btn-txt',
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'  => 'checkout',
							'value' => 'yes',
						),
						array(
							'name'  => 'view_cart',
							'value' => 'yes',
						),
					),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'       => 'footer_btn_shadow',
				'selector'   => '{{WRAPPER}} .pa-woo-mc__btn-txt',
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'  => 'checkout',
							'value' => 'yes',
						),
						array(
							'name'  => 'view_cart',
							'value' => 'yes',
						),
					),
				),
			)
		);

		$this->start_controls_tabs(
			'footer_btn_tabs',
		);

		$this->start_controls_tab(
			'footer_btn_normal',
			array(
				'label'      => esc_html__( 'Normal', 'premium-addons-for-elementor' ),
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'  => 'checkout',
							'value' => 'yes',
						),
						array(
							'name'  => 'view_cart',
							'value' => 'yes',
						),
					),
				),
			)
		);

		$this->add_control(
			'vcart_heading',
			array(
				'label'     => esc_html__( 'View Cart', 'premium-addons-for-elementor' ),
				'separator' => 'before',
				'type'      => Controls_Manager::HEADING,
				'condition' => array(
					'view_cart' => 'yes',
				),
			)
		);

		$this->add_control(
			'pa_btn_color_vcart',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__view-cart .pa-woo-mc__btn-txt' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'view_cart' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'pa_btn_bg_vcart',
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '{{WRAPPER}} .pa-woo-mc__view-cart',
				'condition' => array(
					'view_cart' => 'yes',
				),
			)
		);

		$this->add_control(
			'checkout_heading',
			array(
				'label'     => esc_html__( 'Checkout', 'premium-addons-for-elementor' ),
				'separator' => 'before',
				'type'      => Controls_Manager::HEADING,
				'condition' => array(
					'checkout' => 'yes',
				),
			)
		);

		$this->add_control(
			'pa_btn_color_checkout',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__checkout .pa-woo-mc__btn-txt' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'checkout' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'pa_btn_bg_checkout',
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '{{WRAPPER}} .pa-woo-mc__checkout',
				'condition' => array(
					'checkout' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'footer_btn_shadow',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pa-woo-mc__mc-btn',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'pa_border_color_footer_cta',
				'selector' => '{{WRAPPER}} .pa-woo-mc__mc-btn',
			)
		);

		$this->add_responsive_control(
			'footer_btn_rad',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__mc-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'footer_btn_tab_hov',
			array(
				'label'      => esc_html__( 'Hover', 'premium-addons-for-elementor' ),
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'  => 'checkout',
							'value' => 'yes',
						),
						array(
							'name'  => 'view_cart',
							'value' => 'yes',
						),
					),
				),
			)
		);

		$this->add_control(
			'vcart_heading_hov',
			array(
				'label'     => esc_html__( 'View Cart', 'premium-addons-for-elementor' ),
				'separator' => 'before',
				'type'      => Controls_Manager::HEADING,
				'condition' => array(
					'view_cart' => 'yes',
				),
			)
		);

		$this->add_control(
			'pa_btn_color_vcart_hov',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__view-cart:hover .pa-woo-mc__btn-txt' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'view_cart' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'pa_btn_bg_vcart_hov',
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '{{WRAPPER}} .pa-woo-mc__view-cart:hover',
				'condition' => array(
					'view_cart' => 'yes',
				),
			)
		);

		$this->add_control(
			'checkout_heading_hov',
			array(
				'label'     => esc_html__( 'Checkout', 'premium-addons-for-elementor' ),
				'separator' => 'before',
				'type'      => Controls_Manager::HEADING,
				'condition' => array(
					'checkout' => 'yes',
				),
			)
		);

		$this->add_control(
			'pa_btn_color_checkout_hov',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-woo-mc__checkout:hover .pa-woo-mc__btn-txt' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'checkout' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'pa_btn_bg_checkout_hov',
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '{{WRAPPER}} .pa-woo-mc__checkout:hover',
				'condition' => array(
					'checkout' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'footer_btn_shadow_hov',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pa-woo-mc__mc-btn:hover',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'pa_border_color_footer_cta_hov',
				'selector' => '{{WRAPPER}} .pa-woo-mc__mc-btn:hover',
			)
		);

		$this->add_responsive_control(
			'footer_btn_rad_hov',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__mc-btn:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'footer_btn_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'separator'  => 'before',
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__mc-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'  => 'checkout',
							'value' => 'yes',
						),
						array(
							'name'  => 'view_cart',
							'value' => 'yes',
						),
					),
				),
			)
		);

		$this->add_responsive_control(
			'footer_btn_cont',
			array(
				'label'      => __( 'container Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__cart-buttons' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'  => 'checkout',
							'value' => 'yes',
						),
						array(
							'name'  => 'view_cart',
							'value' => 'yes',
						),
					),
				),
			)
		);

		$this->add_control(
			'footer_cont_heading',
			array(
				'label'     => esc_html__( 'Container', 'premium-addons-for-elementor' ),
				'separator' => 'before',
				'type'      => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'pa_btn_bg_footer',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .pa-woo-mc__cart-footer',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'pa_border_color_footer',
				'selector' => '{{WRAPPER}} .pa-woo-mc__cart-footer',
			)
		);

		$this->add_responsive_control(
			'footer_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-woo-mc__cart-footer' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	private function add_loader_style() {

		$this->start_controls_section(
			'loader_style_sec',
			array(
				'label'      => __( 'Loader', 'premium-addons-for-elementor' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'  => 'behaviour',
							'value' => 'toggle',
						),
						array(
							'terms' => array(
								array(
									'name'  => 'behaviour',
									'value' => 'url',
								),
								array(
									'name'  => 'woo_cta_connect',
									'value' => 'yes',
								),
							),
						),
					),
				),
			)
		);

		$this->add_control(
			'loader_overlay_color',
			array(
				'label'     => __( 'Overlay Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array(
					'{{WRAPPER}} .premium-loading-feed' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'spinner_color',
			array(
				'label'     => __( 'Spinner Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-loader' => 'border-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'spinner_fill_color',
			array(
				'label'     => __( 'Fill Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-loader' => 'border-top-color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render Mini Cart widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {

		if ( ! wp_script_is( 'wc-cart-fragments' ) ) {
			wp_enqueue_script( 'wc-cart-fragments' );
		}

		$settings = $this->get_settings_for_display();

		$papro_activated = apply_filters( 'papro_activated', false );

		if ( ! $papro_activated || version_compare( PREMIUM_PRO_ADDONS_VERSION, '2.9.22', '<' ) ) {

			if ( 'url' === $settings['behaviour'] || 'menu' === $settings['cart_type'] || 'layout-2' === $settings['content_layout'] ) {

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

        update_option( 'pa_mc_layout', $settings['content_layout'] );

		$icon_type = $settings['icon_type'];
		$cart_txt  = $settings['cart_txt'];

		$subtotoal = 'yes' === $settings['subtotal'];

		$badge               = 'yes' === $settings['badge_switcher'];
		$hide_badge_if_empty = 'yes' === $settings['badge_hide_switcher'];
		$behaviour           = $settings['behaviour'];

		$is_connected     = 'url' === $behaviour && 'yes' === $settings['woo_cta_connect'];
		$float            = 'yes' === $settings['float_trigger'];
		$render_mini_cart = ( 'toggle' === $behaviour || $is_connected ) && ! is_cart() && ! is_checkout();

		$this->add_render_attribute( 'cart_outer_wrapper', 'class', 'pa-woo-mc__outer-container' );

		if ( $render_mini_cart ) {

			$cart_type = $is_connected || $float ? 'slide' : $settings['cart_type'];
			// we should also add the animation.
			$this->add_render_attribute(
				'cart_menu_content',
				'class',
				array(
					'pa-woo-mc__content-wrapper',
					'pa-woo-mc__content-wrapper-' . $this->get_id(),
					'premium-addons__v-hidden',
					'pa-flex-col',
					'pa-woo-mc__' . $cart_type,
				)
			);

			$cart_settings = array(
				'type'         => $cart_type,
				'behavior'     => $behaviour,
				'trigger'      => 'slide' === $cart_type ? 'click' : $settings['trigger'],
				'style'        => 'slide' === $cart_type ? $settings['slide_effects'] : '',
				'clickOutside' => 'yes' === $settings['close_on_outside'],
				'removeTxt'    => 'yes' === $settings['remove_icon'] && 'text' === $settings['remove_type'] ? $settings['remove_txt'] : false,
			);

			$this->add_render_attribute( 'cart_outer_wrapper', 'data-settings', json_encode( $cart_settings ) );
		}

		?>
			<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'cart_outer_wrapper' ) ); ?>>

				<div class="pa-woo-mc__inner-container">
					<div class="pa-woo-mc__icon-wrapper">
						<?php
						switch ( $icon_type ) {
							case 'icon':
								if ( 'none' === $settings['default_icons'] ) {
									$trigger_icon = '';
								} elseif ( 'custom' === $settings['default_icons'] ) {
									Icons_Manager::render_icon(
										$settings['icon'],
										array(
											'class'       => array( 'pa-woo-mc__icon' ),
											'aria-hidden' => 'true',
										)
									);
								} else {
									echo $this->getTriggerIcon( $settings['default_icons'] );
								}

								break;
							case 'image':
								if ( ! empty( $settings['image']['url'] ) ) {
									$image_html = Group_Control_Image_Size::get_attachment_image_html( $settings, 'thumbnail', 'image' );
								}

								echo wp_kses_post( $image_html );
							case 'animation':
								$this->add_render_attribute(
									'cart_lottie',
									array(
										'class'            => array(
											'pa-woo-mc__lotti-animation',
											'premium-lottie-animation',
										),
										'data-lottie-url'  => $settings['lottie_url'],
										'data-lottie-loop' => $settings['lottie_loop'],
										'data-lottie-reverse' => $settings['lottie_reverse'],
									)
								);
								?>
										<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'cart_lottie' ) ); ?>></div>
									<?php

								break;
							default:
								$this->print_unescaped_setting( 'custom_svg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								break;
						}

						if ( $badge ) {
							$count = WC()->cart ? WC()->cart->get_cart_contents_count() : '0';

							if ( $count || ( ! $count && ! $hide_badge_if_empty ) ) {
								?>
										<span class="pa-woo-mc__badge"><?php echo esc_html( $count ); ?></span>
									<?php
							}
						}
						?>
					</div>
					<?php if ( ! empty( $cart_txt ) || $subtotoal ) : ?>
					<div class="pa-woo-mc__text-wrapper">
						<?php if ( $subtotoal ) : ?>
						<span class="pa-woo-mc__subtotal"><?php echo wp_kses_data( WC()->cart ? WC()->cart->get_cart_total() : '' ); ?></span>
						<?php endif; ?>
						<?php if ( ! empty( $cart_txt ) ) : ?>
						<span class="pa-woo-mc__text"><?php echo esc_html( $cart_txt ); ?></span>
						<?php endif; ?>
					</div>
					<?php endif; ?>

					<?php
					if ( 'url' === $behaviour ) :
						$this->add_link_attributes( 'cart_link', $settings['cart_link'] );
						$this->add_render_attribute( 'cart_link', 'class', 'pa-woo-mc__link' );
						?>
						<a <?php $this->print_render_attribute_string( 'cart_link' ); ?>></a>
					<?php endif; ?>

					<?php
					if ( $render_mini_cart && 'menu' === $cart_type ) {
						$this->render_mini_cart_content( $settings, $cart_type );
					}
					?>
				</div>

			</div>
		<?php
		if ( $render_mini_cart && 'slide' === $cart_type ) {

			$this->add_render_attribute( 'cart_menu_content', 'class', array( 'pa-woo-mc__anim-' . $settings['slide_effects'], $settings['cart_dir'] ) );

			$this->render_mini_cart_content( $settings, $cart_type );

			if ( 'yes' === $settings['slide_overlay'] ) {
				$this->add_render_attribute(
					'overlay',
					array(
						'class' => array(
							'pa-woo-mc__overlay-' . $this->get_id(),
							'pa-woo-mc__overlay',
							'premium-addons__v-hidden',
						),
					)
				);
				?>
				<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'overlay' ) ); ?>></div>
								<?php
			}
		}
	}

	private function render_mini_cart_content( $settings, $cart_type ) {

		$render_header = 'slide' === $cart_type || ( 'menu' === $cart_type && 'yes' === $settings['cart_header'] );
		?>
			<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'cart_menu_content' ) ); ?>>
				<?php

				if ( $render_header ) {
					$this->render_cart_header( $settings, $cart_type );
				}
				?>
					<div class="pa-woo-mc__widget-shopping-outer-wrapper">
						<div class="widget_shopping_cart_content">
							<?php woocommerce_mini_cart(); ?>
						</div>
					</div>
					<?php
					$this->render_cart_footer( $settings );
					?>
			</div>

		<?php
	}

	private function render_cart_header( $settings, $cart_type ) {
		$title = $settings['cart_title'];
		?>
		<div class="pa-woo-mc__cart-header">
			<?php

			if ( ! empty( $title ) ) {
				?>
					<div class="pa-woo-mc__cart-title"> <?php echo esc_html( $title ); ?> </div>
				<?php
			}

			if ( 'slide' === $cart_type ) {
				?>
					<span class="pa-woo-mc__close-button">
					<?php
						Icons_Manager::render_icon(
							$settings['close_icon'],
							array(
								'class'       => array( 'pa-woo-mc__close-icon' ),
								'aria-hidden' => 'true',
							)
						);
					?>
					</span>
				<?php
			}
			?>
		</div>
		<?php
	}

	private function render_cart_footer( $settings ) {
		$subtotal  = 'yes' === $settings['footer_subtotal'];
		$checkout  = 'yes' === $settings['checkout'];
		$view_cart = 'yes' === $settings['view_cart'];

		if ( ! in_array( true, array( $subtotal, $checkout, $view_cart ), true ) ) {
			return;
		}

		if ( $subtotal ) {
			$cart_count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;

			$this->add_render_attribute(
				'cart_footer',
				array(
					'class'              => 'pa-woo-mc__cart-footer',
					'data-pa-footer-txt' => $settings['subtotal_txt'],
				)
			);

			$has_item_count = str_contains( $settings['subtotal_txt'], '{{count}}' );

			if ( $has_item_count ) {
				$subtotal_heading = ! empty( $settings['subtotal_txt'] ) ? '<span class="pa-woo-mc__cart-count">' . $cart_count . '</span>' : false;
			} else {
				$subtotal_heading = ! empty( $settings['subtotal_txt'] ) ? $settings['subtotal_txt'] : false;
			}
		}
		?>
		<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'cart_footer' ) ); ?>>
			<?php if ( $subtotal ) : ?>
			<div class="pa-woo-mc__cart-subtotal">
				<?php if ( $subtotal_heading ) : ?>
				<span class="pa-woo-mc__subtotal-heading"> <?php echo wp_kses_post( $subtotal_heading ); ?></span>
				<?php endif; ?>

				<span class="pa-woo-mc__subtotal"><?php echo wp_kses_data( WC()->cart ? WC()->cart->get_cart_total() : '' ); ?></span>
				</div>
			<?php endif; ?>
			<?php if ( $view_cart || $checkout ) : ?>
			<div class="pa-woo-mc__cart-buttons">
				<?php if ( $view_cart ) : ?>
				<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="pa-woo-mc__mc-btn pa-woo-mc__view-cart">
					<span class="pa-woo-mc__btn-txt"><?php echo esc_html__( 'View cart', 'woocommerce' ); // phpcs:ignore WordPress.WP.I18n ?></span>
				</a>
				<?php endif; ?>
				<?php if ( $checkout ) : ?>
				<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="pa-woo-mc__mc-btn pa-woo-mc__checkout">
					<span class="pa-woo-mc__btn-txt"><?php echo esc_html__( 'Checkout', 'woocommerce' ); // phpcs:ignore WordPress.WP.I18n ?></span>
				</a>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</div>

		<?php
	}

	private function getTriggerIcon( $icon ) {

		$icons = array(
			'basket'       => '<svg class="pa-woo-mc__icon" xmlns="http://www.w3.org/2000/svg" width="21.5" height="21.5" viewBox="0 0 21.5 21.5"><defs><style>.pa-basket{fill:#333;}</style></defs><path class="pa-basket" d="M17.99,4.33h-.4l-3.38-3.38c-.27-.27-.71-.27-.99,0-.27.27-.27.71,0,.99l2.39,2.39H5.89l2.39-2.39c.27-.27.27-.71,0-.99-.27-.27-.71-.27-.99,0l-3.37,3.38h-.4c-.9,0-2.77,0-2.77,2.56,0,.97.2,1.61.62,2.03.24.25.53.38.84.45.29.07.6.08.9.08h15.28c.31,0,.6-.02.88-.08.84-.2,1.48-.8,1.48-2.48,0-2.56-1.87-2.56-2.76-2.56Z"/><path class="pa-basket" d="M17.8,10.75H3.62c-.62,0-1.09.55-.99,1.16l.84,5.14c.28,1.72,1.03,3.7,4.36,3.7h5.61c3.37,0,3.97-1.69,4.33-3.58l1.01-5.23c.12-.62-.35-1.19-.98-1.19ZM9.36,17.2c0,.39-.31.7-.69.7s-.7-.31-.7-.7v-3.3c0-.38.31-.7.7-.7s.69.32.69.7v3.3ZM13.64,17.2c0,.39-.31.7-.7.7s-.7-.31-.7-.7v-3.3c0-.38.32-.7.7-.7s.7.32.7.7v3.3Z"/></svg>',
			'basket-thin'  => '<svg class="pa-woo-mc__icon" xmlns="http://www.w3.org/2000/svg" width="21.5" height="21.51" viewBox="0 0 21.5 21.51"><defs><style>.pa-basket{fill:#1a1a1a;}</style></defs><g id="Basket"><path class="pa-basket" d="M18.53,3.85h-.44L14.47.22c-.29-.29-.77-.29-1.06,0-.29.29-.29.77,0,1.06l2.56,2.57H5.53l2.56-2.57c.29-.29.29-.77,0-1.06-.29-.29-.77-.29-1.06,0l-3.62,3.63h-.44c-.96,0-2.97,0-2.97,2.75,0,1.04.21,1.73.67,2.18.26.26.57.41.9.48l1.35,8.25c.31,1.86,1.12,3.99,4.69,3.99h6.03c3.63,0,4.28-1.82,4.67-3.85l1.61-8.39c.33-.07.64-.21.91-.48.46-.45.67-1.14.67-2.18,0-2.75-2.01-2.75-2.97-2.75ZM16.83,17.37c-.33,1.73-.62,2.63-3.19,2.63h-6.03c-2.32,0-2.92-.96-3.21-2.73l-1.29-7.92h15.26l-1.54,8.02ZM19.78,7.72c-.14.14-.57.13-1.02.13H2.74c-.45,0-.88.01-1.02-.13-.06-.07-.22-.31-.22-1.12,0-1.13.28-1.25,1.47-1.25h15.56c1.19,0,1.47.12,1.47,1.25,0,.81-.16,1.05-.22,1.12Z"/><path class="pa-basket" d="M8.51,17.05c-.41,0-.75-.34-.75-.75v-3.55c0-.41.34-.75.75-.75s.75.34.75.75v3.55c0,.41-.34.75-.75.75Z"/><path class="pa-basket" d="M13.11,17.05c-.41,0-.75-.34-.75-.75v-3.55c0-.41.34-.75.75-.75s.75.34.75.75v3.55c0,.41-.34.75-.75.75Z"/></g></svg>',
			'cart'         => '<svg class="pa-woo-mc__icon" xmlns="http://www.w3.org/2000/svg" width="21.5" height="21.5" viewBox="0 0 21.5 21.5"><defs><style>.pa-cart-filled{fill:#333;}</style></defs><g id="Cart_Filled"><path class="pa-cart-filled" d="M15.6,21.38c.97,0,1.75-.78,1.75-1.75s-.78-1.75-1.75-1.75-1.75.78-1.75,1.75.78,1.75,1.75,1.75Z"/><path class="pa-cart-filled" d="M7.6,21.38c.97,0,1.75-.78,1.75-1.75s-.78-1.75-1.75-1.75-1.75.78-1.75,1.75.78,1.75,1.75,1.75Z"/><path class="pa-cart-filled" d="M4.19,2.82l-.2,2.45c-.04.47.33.86.8.86h15.31c.42,0,.77-.32.8-.74.13-1.77-1.22-3.21-2.99-3.21H5.62c-.1-.44-.3-.86-.61-1.21-.5-.53-1.2-.84-1.92-.84h-1.74C.94.12.6.47.6.88s.34.75.75.75h1.74c.31,0,.6.13.81.35.21.23.31.53.29.84Z"/><path class="pa-cart-filled" d="M19.86,7.62H4.52c-.42,0-.76.32-.8.73l-.36,4.35c-.14,1.71,1.2,3.17,2.91,3.17h11.12c1.5,0,2.82-1.23,2.93-2.73l.33-4.67c.04-.46-.32-.85-.79-.85Z"/></g></svg>',
			'cart-outline' => '<svg class="pa-woo-mc__icon" xmlns="http://www.w3.org/2000/svg" width="21.5" height="21.5" viewBox="0 0 21.5 21.5"><defs><style>.pa-cart-outline{fill:#333;}</style></defs><g id="Cart_Outline"><path class="pa-cart-outline" d="M20.33,3c-.67-.73-1.6-1.13-2.62-1.13H5.21c-.11-.38-.31-.73-.58-1.03-.5-.53-1.19-.84-1.91-.84H.98C.56,0,.23.34.23.75s.33.75.75.75h1.74c.3,0,.59.13.8.36.21.22.31.52.29.83l-.83,9.96c-.09.98.25,1.96.92,2.69.67.73,1.62,1.15,2.62,1.15h10.65c1.82,0,3.41-1.48,3.55-3.31l.54-7.5c.08-1.01-.25-1.96-.93-2.68ZM19.74,6H7.98c-.42,0-.75.34-.75.75s.33.75.75.75h11.65l-.4,5.57c-.08,1.04-1.03,1.92-2.06,1.92H6.52c-.59,0-1.12-.24-1.52-.66-.39-.43-.58-.98-.53-1.56l.06-.72h11.72c.41,0,.75-.34.75-.75s-.34-.75-.75-.75H4.66l.6-7.18h12.45c.59,0,1.14.23,1.53.65.38.41.57.96.53,1.56l-.03.42Z"/><path class="pa-cart-outline" d="M15.22,21.5c-1.1,0-2-.9-2-2s.9-2,2-2,2,.9,2,2-.9,2-2,2ZM15.22,19c-.28,0-.5.22-.5.5s.22.5.5.5.5-.22.5-.5-.22-.5-.5-.5Z"/><path class="pa-cart-outline" d="M7.23,21.5c-1.1,0-2-.9-2-2s.9-2,2-2,2,.9,2,2-.9,2-2,2ZM7.23,19c-.28,0-.5.22-.5.5s.22.5.5.5.5-.22.5-.5-.22-.5-.5-.5Z"/></g></svg>',
		);

		return $icons[ $icon ];
	}
}
