<?php
/**
 * Premium Woo CTA.
 */

namespace PremiumAddons\Modules\Woocommerce\Widgets;

// Elementor Classes.
use Elementor\Widget_Base;
use Elementor\Repeater;
use Elementor\Icons_Manager;
use Elementor\Utils;
use Elementor\Control_Media;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;


use PremiumAddons\Admin\Includes\Admin_Helper;
use PremiumAddons\Includes\Helper_Functions;
use PremiumAddons\Includes\Premium_Template_Tags;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // If this file is called directly, abort.
}

/**
 * Class Woo_CTA
 */
class Woo_CTA extends Widget_Base {

	/**
	 * Template Instance
	 *
	 * @var template_instance
	 */
	protected $template_instance;

	/**
	 * Check Icon Draw Option.
	 *
	 * @since 2.8.4
	 * @access public
	 */
	public function check_icon_draw() {

		$is_enabled = Admin_Helper::check_svg_draw( 'woo-cta' );
		return $is_enabled;
	}


	/**
	 * Get Elementor Helper Instance.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function getTemplateInstance() {
		return $this->template_instance = Premium_Template_Tags::getInstance();
	}


	/**
	 * Retrieve Widget Name.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function get_name() {
		return 'premium-woo-cta';
	}


		/**
		 * Retrieve Widget Title.
		 *
		 * @since 1.0.0
		 * @access public
		 */
	public function get_title() {
		return __( 'Woo CTA', 'premium-addons-for-elementor' );
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
		return array( 'pa', 'premium woo cta', 'product', 'woocommerce', 'cart', 'wishlist', 'compare' );
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
			'elementor-icons',
			'woocommerce-general',
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
		$draw_scripts = $this->check_icon_draw() ? array(
			'pa-fontawesome-all',
			'pa-tweenmax',
			'pa-motionpath',
		) : array();

		return array_merge(
			$draw_scripts,
			array(
				'lottie-js',
				'premium-woo-cta',
			)
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
		return 'pa-woo-cta';
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
	 * Register Woo CTA controls.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {

		$draw_icon = $this->check_icon_draw();

		$this->start_controls_section(
			'section_content',
			array(
				'label' => __( 'General', 'premium-addons-for-elementor' ),

			)
		);

		$this->add_control(
			'button_action',
			array(
				'label'   => __( 'Button Type', 'premium-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'add_to_cart',
				'options' => array(
					'add_to_cart'     => __( 'Add to Cart', 'premium-addons-for-elementor' ),
					'add_to_wishlist' => apply_filters( 'pa_pro_label', __( 'Add to Wishlist (Pro)', 'premium-addons-for-elementor' ) ),
					'add_to_compare'  => apply_filters( 'pa_pro_label', __( 'Add to Compare (Pro)', 'premium-addons-for-elementor' ) ),
				),
			)
		);

		do_action( 'pa_woo_cta_notice_controls', $this );

		$this->add_control(
			'use_current_product',
			array(
				'label' => __( 'Use Current Product', 'premium-addons-for-elementor' ),
				'type'  => Controls_Manager::SWITCHER,
			)
		);

		$this->add_control(
			'current_product_notice',
			array(
				'raw'             => __( 'This option is used with Elementor single product page template.', 'premium-addons-for-elementor' ),
				'type'            => Controls_Manager::RAW_HTML,
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
				'condition'       => array(
					'use_current_product' => 'yes',
				),
			)
		);

		$products = Premium_Template_Tags::get_default_posts_list( 'product' );

		$this->add_control(
			'product_id',
			array(
				'label'     => __( 'Select Product', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => $products,
				'condition' => array(
					'use_current_product!' => 'yes',
				),
			)
		);

		$this->add_control(
			'show_quantity',
			array(
				'label'     => __( 'Show Quantity Field', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => array(
					'button_action' => array( 'add_to_cart' ),
				),

			)
		);

		$this->add_control(
			'redirect_to_cart',
			array(
				'label'     => __( 'Automatically Redirect to Cart Page', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => array(
					'button_action' => array( 'add_to_cart' ),
				),
			)
		);

		$this->add_control(
			'product_unavailable_message',
			array(
				'label'       => __( 'Product Unavailable Message', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Out of Stock', 'premium-addons-for-elementor' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'connect_to_pa_mc',
			array(
				'label'       => __( 'Automatically Open The Mini Cart List', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SWITCHER,
				'description' => __( 'Use this option to open the Premium Mini Cart when a product is added.', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'connected_mc_id',
			array(
				'label'       => __( 'Widget ID', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'description' => __( 'Add your Premium Mini Cart widget custom ID ( EX: #my-custom-id )', 'premium-addons-for-elementor' ),
				'condition'   => array(
					'connect_to_pa_mc' => array( 'yes' ),
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_button',
			array(
				'label' => __( 'Button', 'premium-addons-for-elementor' ),

			)
		);

		$this->add_control(
			'cart_button_text',
			array(
				'label'       => __( 'Text', 'premium-addons-for-elementor' ),
				'default'     => __( 'Add to Cart', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'label_block' => true,
				'condition'   => array(
					'button_action' => array( 'add_to_cart' ),
				),

			)
		);

		do_action( 'pa_woo_cta_text_controls', $this );

		$common_conditions = array(
			'icon_switcher' => 'yes',
		);

		$this->add_control(
			'icon_switcher',
			array(
				'label'     => __( 'Icon', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'icon_type',
			array(
				'label'     => __( 'Icon Type', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					'icon'   => __( 'Icon', 'premium-addons-for-elementor' ),
					'image'  => __( 'Image', 'premium-addons-for-elementor' ),
					'lottie' => __( 'Lottie Animation', 'premium-addons-for-elementor' ),
					'svg'    => __( 'SVG Code', 'premium-addons-for-elementor' ),
				),
				'default'   => 'icon',
				'condition' => $common_conditions,
			)
		);

		$this->add_control(
			'icon',
			array(
				'label'      => __( 'Icon', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::ICONS,
				'default'    => array(
					'value'   => 'fa fa-shopping-cart',
					'library' => 'solid',
				),
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'terms' => array(
								array(
									'name'  => 'icon_switcher',
									'value' => 'yes',
								),
								array(
									'name'  => 'icon_type',
									'value' => 'icon',
								),
							),
						),

					),
				),
			)
		);

		$this->add_control(
			'custom_svg',
			array(
				'label'       => __( 'SVG Code', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXTAREA,
				'description' => 'You can use these sites to create SVGs: <a href="https://danmarshall.github.io/google-font-to-svg-path/" target="_blank">Google Fonts</a> and <a href="https://boxy-svg.com/" target="_blank">Boxy SVG</a>',
				'conditions'  => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'terms' => array(
								array(
									'name'  => 'icon_switcher',
									'value' => 'yes',
								),
								array(
									'name'  => 'icon_type',
									'value' => 'svg',
								),
							),
						),

					),
				),
			)
		);

		$draw_icon_conditions = array(
			'terms' => array(
				array(
					'name'     => 'icon[library]',
					'operator' => '!==',
					'value'    => 'svg',
				),
				array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'terms' => array(
								array(
									'name'  => 'icon_switcher',
									'value' => 'yes',
								),
								array(
									'relation' => 'or',
									'terms'    => array(
										array(
											'name'  => 'icon_type',
											'value' => 'icon',
										),
										array(
											'name'  => 'icon_type',
											'value' => 'svg',
										),
									),
								),
							),
						),
					),
				),
			),

		);

		$this->add_control(
			'draw_svg',
			array(
				'label'      => __( 'Draw Icon', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SWITCHER,
                'description' => __( 'Enable this option to make the icon drawable. See ', 'premium-addons-for-elementor' ) . '<a href="https://www.youtube.com/watch?v=ZLr0bRe0RAY" target="_blank">tutorial</a>',
				'classes'    => $draw_icon ? '' : 'editor-pa-control-disabled',
				'conditions' => $draw_icon_conditions,
			)
		);

		$draw_icon_conditions['terms'] = array_merge(
			$draw_icon_conditions['terms'],
			array(
				array(
					'name'  => 'draw_svg',
					'value' => 'yes',
				),
			)
		);

		if ( $draw_icon ) {

			$this->add_control(
				'path_width',
				array(
					'label'      => __( 'Path Thickness', 'premium-addons-for-elementor' ),
					'type'       => Controls_Manager::SLIDER,
					'range'      => array(
						'px' => array(
							'min'  => 0,
							'max'  => 50,
							'step' => 0.1,
						),
					),
					'conditions' => $draw_icon_conditions,
					'selectors'  => array(
						'{{WRAPPER}} .premium-woo-cta-button svg:not(.premium-btn-svg) *' => 'stroke-width: {{SIZE}}',
					),
				)
			);

			$this->add_control(
				'svg_sync',
				array(
					'label'      => __( 'Draw All Paths Together', 'premium-addons-for-elementor' ),
					'type'       => Controls_Manager::SWITCHER,
					'conditions' => $draw_icon_conditions,
				)
			);

			$this->add_control(
				'svg_loop',
				array(
					'label'        => __( 'Loop', 'premium-addons-for-elementor' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => 'true',
					'conditions'   => $draw_icon_conditions,
				)
			);

			$this->add_control(
				'frames',
				array(
					'label'       => __( 'Speed', 'premium-addons-for-elementor' ),
					'type'        => Controls_Manager::NUMBER,
					'description' => __( 'Larger value means longer animation duration.', 'premium-addons-for-elementor' ),
					'default'     => 5,
					'min'         => 1,
					'max'         => 100,
					'conditions'  => $draw_icon_conditions,
				)
			);

			$this->add_control(
				'svg_reverse',
				array(
					'label'        => __( 'Reverse', 'premium-addons-for-elementor' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'conditions'   => $draw_icon_conditions,
				)
			);

			$this->add_control(
				'svg_hover',
				array(
					'label'        => __( 'Only Play on Hover', 'premium-addons-for-elementor' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'conditions'   => $draw_icon_conditions,
				)
			);

			$this->add_control(
				'svg_yoyo',
				array(
					'label'      => __( 'Yoyo Effect', 'premium-addons-for-elementor' ),
					'type'       => Controls_Manager::SWITCHER,
					'conditions' => $draw_icon_conditions,
				)
			);

		} elseif ( method_exists( 'PremiumAddons\Includes\Helper_Functions', 'get_draw_svg_notice' ) ) {

			Helper_Functions::get_draw_svg_notice(
				$this,
				'cta',
				array_merge(
					$common_conditions,
					array(
						'icon_type'      => array( 'icon', 'svg' ),
						'icon[library]!' => 'svg',
					)
				)
			);
		}

		$this->add_control(
			'custom_image',
			array(
				'label'      => __( 'Select Image', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::MEDIA,
				'dynamic'    => array( 'active' => true ),
				'default'    => array(
					'url' => Utils::get_placeholder_image_src(),
				),
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'terms' => array(
								array(
									'name'  => 'icon_switcher',
									'value' => 'yes',
								),
								array(
									'name'  => 'icon_type',
									'value' => 'image',
								),
							),
						),
					),
				),
			)
		);

		$this->add_control(
			'lottie_source',
			array(
				'label'      => __( 'Source', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SELECT,
				'options'    => array(
					'url'  => __( 'External URL', 'premium-addons-for-elementor' ),
					'file' => __( 'Media File', 'premium-addons-for-elementor' ),
				),
				'default'    => 'url',
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'terms' => array(
								array(
									'name'  => 'icon_switcher',
									'value' => 'yes',
								),
								array(
									'name'  => 'icon_type',
									'value' => 'lottie',
								),
							),
						),
					),
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
				'conditions'  => array(
					'terms' => array(
						array(
							'name'  => 'lottie_source',
							'value' => 'url',
						),
						array(
							'relation' => 'or',
							'terms'    => array(
								array(
									'terms' => array(
										array(
											'name'  => 'icon_switcher',
											'value' => 'yes',
										),
										array(
											'name'  => 'icon_type',
											'value' => 'lottie',
										),
									),
								),
							),
						),
					),
				),
			)
		);

		$this->add_control(
			'lottie_file',
			array(
				'label'      => __( 'Upload JSON File', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::MEDIA,
				'media_type' => 'application/json',
				'conditions' => array(
					'terms' => array(
						array(
							'name'  => 'lottie_source',
							'value' => 'file',
						),
						array(
							'relation' => 'or',
							'terms'    => array(
								array(
									'terms' => array(
										array(
											'name'  => 'icon_switcher',
											'value' => 'yes',
										),
										array(
											'name'  => 'icon_type',
											'value' => 'lottie',
										),
									),
								),
							),
						),
					),
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
				'conditions'   => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'terms' => array(
								array(
									'name'  => 'icon_switcher',
									'value' => 'yes',
								),
								array(
									'name'  => 'icon_type',
									'value' => 'lottie',
								),
							),
						),
					),
				),
			)
		);

		$this->add_control(
			'lottie_reverse',
			array(
				'label'        => __( 'Reverse', 'premium-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'true',
				'conditions'   => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'terms' => array(
								array(
									'name'  => 'icon_switcher',
									'value' => 'yes',
								),
								array(
									'name'  => 'icon_type',
									'value' => 'lottie',
								),
							),
						),
					),
				),
			)
		);

		$this->add_control(
			'lottie_hover',
			array(
				'label'        => __( 'Only Play on Hover', 'premium-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'true',
				'conditions'   => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'terms' => array(
								array(
									'name'  => 'icon_switcher',
									'value' => 'yes',
								),
								array(
									'name'  => 'icon_type',
									'value' => 'lottie',
								),
							),
						),
					),
				),
			)
		);

		$this->add_responsive_control(
			'icon_position',
			array(
				'label'     => __( 'Icon Position', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'2'  => array(
						'title' => __( 'Before', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-order-start',
					),
					'-1' => array(
						'title' => __( 'After', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-order-end',
					),
				),
				'default'   => '2',
				'toggle'    => false,
				'selectors' => array(
					'{{WRAPPER}} .premium-button-text-icon-wrapper' => 'order: {{VALUE}}',
				),
				'condition' => array(
					'icon_switcher' => 'yes',
				),
			)
		);

		$this->add_control(
			'icon_size',
			array(
				'label'      => __( 'Icon Size', 'premium-addons-pro' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'default'    => array(
					'size' => 30,
				),
				'selectors'  => array(
					'{{WRAPPER}} .premium-woo-cta-button i' => 'font-size: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .premium-woo-cta-button svg' => 'width: {{SIZE}}{{UNIT}} !important; height: {{SIZE}}{{UNIT}} !important',
					'{{WRAPPER}} .premium-woo-cta-button img' => 'width: {{SIZE}}{{UNIT}};',
				),
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'terms' => array(
								array(
									'name'  => 'icon_switcher',
									'value' => 'yes',
								),
							),
						),
					),
				),
			)
		);

		$this->add_responsive_control(
			'icon_spacing',
			array(
				'label'     => __( 'Icon Spacing', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 16,
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-woo-cta-button' => 'column-gap: {{SIZE}}px;',
				),
				'condition' => $common_conditions,
			)
		);

		$this->add_control(
			'button_size',
			array(
				'label'       => __( 'Size', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'lg',
				'options'     => array(
					'sm'    => __( 'Small', 'premium-addons-for-elementor' ),
					'md'    => __( 'Medium', 'premium-addons-for-elementor' ),
					'lg'    => __( 'Large', 'premium-addons-for-elementor' ),
					'block' => __( 'Block', 'premium-addons-for-elementor' ),
				),
				'label_block' => true,
				'separator'   => 'before',
			)
		);

		$this->add_responsive_control(
			'button_align',
			array(
				'label'     => __( 'Alignment', 'premium-addons-for-elementor' ),
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
				'selectors' => array(
					'{{WRAPPER}} .premium-woo-btn-container' => 'justify-content: {{VALUE}}',
				),
				'default'   => 'center',
				'toggle'    => false,
			)
		);

		Helper_Functions::add_btn_hover_controls( $this, array() );

		$this->end_controls_section();

		// button style section.
		$this->start_controls_section(
			'section_style',
			array(
				'label' => __( 'Button', 'premium-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => 'button_typo',
					'global'   => array(
						'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
					),
					'selector' => '{{WRAPPER}} .premium-woo-btn-text',
				)
			);

			$this->add_control(
				'svg_color',
				array(
					'label'      => __( 'After Draw Fill Color', 'premium-addons-pro' ),
					'type'       => Controls_Manager::COLOR,
					'global'     => false,
					'separator'  => 'after',
					'conditions' => array(
						'terms' => array(
							array(
								'name'  => 'draw_svg',
								'value' => 'yes',
							),
							array(
								'relation' => 'or',
								'terms'    => array(
									array(
										'terms' => array(
											array(
												'name'  => 'icon_switcher',
												'value' => 'yes',
											),
											array(
												'name'  => 'icon_type',
												'value' => 'icon',
											),
										),
									),
								),
							),
						),
					),
				)
			);

			$this->start_controls_tabs( 'woo_cta_button_style_tabs' );

			$this->start_controls_tab(
				'woo_cta_button_style_normal',
				array(
					'label' => __( 'Normal', 'premium-addons-for-elementor' ),
				)
			);

			$this->add_control(
				'woo_button_text_color',
				array(
					'label'     => __( 'Text Color', 'premium-addons-for-elementor' ),
					'type'      => Controls_Manager::COLOR,
					'global'    => array(
						'default' => Global_Colors::COLOR_SECONDARY,
					),
					'selectors' => array(
						'{{WRAPPER}} .premium-woo-cta-button .premium-woo-btn-text'  => 'color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'button_icon_color',
				array(
					'label'      => __( 'Icon Color', 'premium-addons-pro' ),
					'type'       => Controls_Manager::COLOR,
					'global'     => array(
						'default' => Global_Colors::COLOR_SECONDARY,
					),
					'selectors'  => array(
						'{{WRAPPER}} .premium-woo-cta-button i' => 'color: {{VALUE}};',
						'{{WRAPPER}} .premium-woo-btn-icon *' => 'fill: {{VALUE}}',
					),
					'conditions' => array(
						'relation' => 'or',
						'terms'    => array(
							array(
								'terms' => array(
									array(
										'name'  => 'icon_switcher',
										'value' => 'yes',
									),
									array(
										'name'  => 'icon_type',
										'value' => 'icon',
									),
								),
							),
						),
					),
				)
			);

		if ( $draw_icon ) {
			$this->add_control(
				'stroke_color',
				array(
					'label'      => __( 'Stroke Color', 'premium-addons-pro' ),
					'type'       => Controls_Manager::COLOR,
					'global'     => array(
						'default' => Global_Colors::COLOR_ACCENT,
					),
					'selectors'  => array(
						'{{WRAPPER}} .premium-woo-btn-icon *' => 'stroke: {{VALUE}};',
					),
					'conditions' => array(
						'relation' => 'or',
						'terms'    => array(
							array(
								'terms' => array(
									array(
										'name'  => 'icon_switcher',
										'value' => 'yes',
									),
									array(
										'name'  => 'icon_type',
										'value' => 'icon',
									),
									array(
										'name'  => 'icon_type',
										'value' => 'svg',
									),
								),
							),
						),
					),
				)
			);

		}

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'           => 'woo_cta_button_background',
				'types'          => array( 'classic', 'gradient' ),
				'fields_options' => array(
					'color' => array(
						'global' => array(
							'default' => Global_Colors::COLOR_PRIMARY,
						),
					),
				),
				'selector'       => '{{WRAPPER}} .premium-woo-cta-button, {{WRAPPER}} .premium-button-style2-shutinhor:before , {{WRAPPER}} .premium-button-style2-shutinver:before , {{WRAPPER}} .premium-button-style5-radialin:before , {{WRAPPER}} .premium-button-style5-rectin:before',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'woo_cta_button_border',
				'selector' => '{{WRAPPER}} .premium-woo-cta-button',
			)
		);

		$this->add_control(
			'woo_box_button_radius',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%' ),
				'default'    => array(
					'size' => 0,
				),
				'selectors'  => array(
					'{{WRAPPER}} .premium-woo-cta-button' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'label'    => __( 'Shadow', 'premium-addons-for-elementor' ),
				'name'     => 'woo_cta_button_box_shadow',
				'selector' => '{{WRAPPER}} .premium-woo-cta-button',
			)
		);

		$this->add_responsive_control(
			'woo_cta_button_margin',
			array(
				'label'      => __( 'Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-woo-btn-container' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'woo_cta_button_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-woo-cta-button, {{WRAPPER}} .premium-button-line6::after' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'woo_cta_button_style_hover',
			array(
				'label' => __( 'Hover', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'woo_button_text_hover_color',
			array(
				'label'     => __( 'Text Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_PRIMARY,
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-woo-cta-button:hover .premium-woo-btn-text , {{WRAPPER}} .premium-button-line6::after'  => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'button_icon_color_hover',
			array(
				'label'      => __( 'Icon Color', 'premium-addons-pro' ),
				'type'       => Controls_Manager::COLOR,
				'global'     => array(
					'default' => Global_Colors::COLOR_PRIMARY,
				),
				'selectors'  => array(
					'{{WRAPPER}} .premium-woo-cta-button:hover i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .premium-woo-cta-button:hover .premium-woo-btn-icon *' => 'fill: {{VALUE}}',
				),
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'terms' => array(
								array(
									'name'  => 'icon_switcher',
									'value' => 'yes',
								),
								array(
									'name'  => 'icon_type',
									'value' => 'icon',
								),
							),
						),
					),
				),
			)
		);

		$this->add_control(
			'underline_color',
			array(
				'label'     => __( 'Line Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_SECONDARY,
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-btn-svg' => 'stroke: {{VALUE}};',
					'{{WRAPPER}} .premium-button-line2::before,  {{WRAPPER}} .premium-button-line4::before, {{WRAPPER}} .premium-button-line5::before, {{WRAPPER}} .premium-button-line5::after, {{WRAPPER}} .premium-button-line6::before, {{WRAPPER}} .premium-button-line7::before' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'premium_button_hover_effect' => 'style8',
				),
			)
		);

		$this->add_control(
			'first_layer_hover',
			array(
				'label'     => __( 'Layer #1 Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_SECONDARY,
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-button-style7 .premium-button-text-icon-wrapper:before' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'premium_button_hover_effect' => 'style7',

				),
			)
		);

		$this->add_control(
			'second_layer_hover',
			array(
				'label'     => __( 'Layer #2 Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_TEXT,
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-button-style7 .premium-button-text-icon-wrapper:after' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'premium_button_hover_effect' => 'style7',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'           => 'woo_cta_button_background_hover',
				'types'          => array( 'classic', 'gradient' ),
				'fields_options' => array(
					'color' => array(
						'global' => array(
							'default' => Global_Colors::COLOR_TEXT,
						),
					),
				),
				'selector'       => '{{WRAPPER}} .premium-button-none:hover, {{WRAPPER}} .premium-button-style8:hover, {{WRAPPER}} .premium-button-style1:before, {{WRAPPER}} .premium-button-style2-shutouthor:before, {{WRAPPER}} .premium-button-style2-shutoutver:before, {{WRAPPER}} .premium-button-style2-shutinhor, {{WRAPPER}} .premium-button-style2-shutinver, {{WRAPPER}} .premium-button-style2-dshutinhor:before, {{WRAPPER}} .premium-button-style2-dshutinver:before, {{WRAPPER}} .premium-button-style2-scshutouthor:before, {{WRAPPER}} .premium-button-style2-scshutoutver:before, {{WRAPPER}} .premium-button-style5-radialin, {{WRAPPER}} .premium-button-style5-radialout:before, {{WRAPPER}} .premium-button-style5-rectin, {{WRAPPER}} .premium-button-style5-rectout:before, {{WRAPPER}} .premium-button-style6-bg, {{WRAPPER}} .premium-button-style6:before',
				'condition'      => array(
					'premium_button_hover_effect!' => 'style7',
				),
			),
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'woo_cta_button_border_hover',
				'selector' => '{{WRAPPER}} .premium-woo-cta-button:hover',
			)
		);

		$this->add_control(
			'woo_cta_button_border_radius_hover',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-woo-cta-button:hover' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'label'    => __( 'Shadow', 'premium-addons-for-elementor' ),
				'name'     => 'woo_cta_button_shadow_hover',
				'selector' => '{{WRAPPER}} .premium-woo-cta-button:hover',
			)
		);

		$this->add_responsive_control(
			'woo_cta_button_margin_hover',
			array(
				'label'      => __( 'Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-woo-cta-button:hover' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'woo_cta_button_padding_hover',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-woo-cta-button:hover' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// Quantity style section.
		$this->start_controls_section(
			'quantity_style',
			array(
				'label'     => __( ' Quantity style', 'premium-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'button_action' => array( 'add_to_cart' ),
					'show_quantity' => 'yes',

				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'quantity_typography',
				'label'    => __( 'Typography', 'premium-addons-for-elementor' ),
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				),
				'selector' => '{{WRAPPER}} .product-quantity , {{WRAPPER}} .grouped_product_qty',
			)
		);

		$this->add_control(
			'product_qunatity_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_SECONDARY,
				),
				'selectors' => array(
					'{{WRAPPER}} .product-quantity , {{WRAPPER}} .grouped_product_qty'   => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'quantity_spacing',
			array(
				'label'     => __( 'Quantity Spacing', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 10,
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-woo-btn-container' => 'gap: {{SIZE}}px;',
				),
			)
		);

		$this->start_controls_tabs( 'woo_cta_quantity_style_tabs' );

		$this->start_controls_tab(
			'woo_cta_quantity_style_normal',
			array(
				'label' => __( 'Normal', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'quantity_input_background_color',
			array(
				'label'     => __( 'Input Background Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .product-quantity , {{WRAPPER}} .grouped_product_qty'  => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'quantity_icons_background_color',
			array(
				'label'     => __( 'Plus And Minus Background Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .quantity-button' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'quantity_border_color',
			array(
				'label'     => __( 'Border Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .product-quantity , {{WRAPPER}} .grouped_product_qty , {{WRAPPER}} .quantity-button' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'woo_cta_quantity_style_hover',
			array(
				'label' => __( 'Hover', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'quantity_input_background_color_hover',
			array(
				'label'     => __( 'Input Background Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .product-quantity:hover , {{WRAPPER}} .grouped_product_qty:hover'  => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'quantity_icons_background_color_hover',
			array(
				'label'     => __( 'Plus And Minus Background Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .quantity-button:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'quantity_border_color_hover',
			array(
				'label'     => __( 'Border Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .product-quantity:hover , {{WRAPPER}} .grouped_product_qty:hover , {{WRAPPER}} .quantity-button:hover' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs( 'woo_cta_quantity_style_tabs' );

		$this->end_controls_section();

		// product message unavilabe style section.
		$this->start_controls_section(
			'product_message',
			array(
				'label' => __( 'Product Message', 'premium-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'product_message_color',
			array(
				'label'     => __( 'Text Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_PRIMARY,
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-unavailable-message , {{WRAPPER}} .view-cart-button , {{WRAPPER}} .pro-wish , {{WRAPPER}} .premium-cta-message-box '   => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'mesaage_typography',
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				),
				'selector' => '{{WRAPPER}} .premium-unavailable-message , {{WRAPPER}} .view-cart-button  , {{WRAPPER}} .pro-wish , {{WRAPPER}} .premium-cta-message-box ',
			)
		);

		$this->add_responsive_control(
			'message_margin',
			array(
				'label'      => __( 'Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'default'    => array(
					'size' => 10,
				),
				'separator'  => 'before',
				'selectors'  => array(
					'{{WRAPPER}} .premium-unavailable-message , {{WRAPPER}} .view-cart-button  , {{WRAPPER}} .pro-wish , {{WRAPPER}} .premium-cta-message-box ' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->end_controls_section();

		// variation style section.
		$this->start_controls_section(
			'section_variation_styles',
			array(
				'label' => __( 'Groupred/Variations Table Product', 'premium-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// table varation style.
		$this->add_control(
			'table_style',
			array(
				'label' => __( 'Variations Table Style', 'premium-addons-for-elementor' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->start_controls_tabs( 'woo_cta_varation_table_style_tabs' );

		$this->start_controls_tab(
			'variation_table_style_normal_tab',
			array(
				'label' => __( 'Normal', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'variation_table_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .premium-variations , {{WRAPPER}} .premium-grouped-product',
			)
		);

		$this->add_responsive_control(
			'variation_table_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-variations , {{WRAPPER}} .premium-grouped-product' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'variation_table_border',
				'selector' => '{{WRAPPER}} .premium-variations , {{WRAPPER}} .premium-grouped-product',
			)
		);

		$this->add_control(
			'variation_table_border_radius',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-variations , {{WRAPPER}} .premium-grouped-product' => 'border-radius: {{SIZE}}{{UNIT}};',

				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'label'    => __( 'Shadow', 'premium-addons-for-elementor' ),
				'name'     => 'variation_table_shadow',
				'selector' => '{{WRAPPER}} .premium-variations , {{WRAPPER}} .premium-grouped-product',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'variation_table_style_hover_tab',
			array(
				'label' => __( 'Hover', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'variation_table_background_hover',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .premium-variations:hover  {{WRAPPER}} .premium-grouped-product:hover',
			)
		);

		$this->add_responsive_control(
			'variation_table_padding_hover',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-variations , {{WRAPPER}} .premium-grouped-product' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'variation_table_border_hover',
				'selector' => '{{WRAPPER}} .premium-variations:hover  , {{WRAPPER}} .premium-grouped-product:hover',
			)
		);

		$this->add_control(
			'variation_table_border_radius_hover',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-variations:hover  , {{WRAPPER}} .premium-grouped-product:hover' => 'border-radius: {{SIZE}}{{UNIT}};',

				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'label'    => __( 'Shadow', 'premium-addons-for-elementor' ),
				'name'     => 'variation_table_shadow_hover',
				'selector' => '{{WRAPPER}} .premium-variations:hover ,  {{WRAPPER}} .premium-grouped-product:hover',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs( 'woo_cta_varation_table_style_tabs' );

		$this->add_control(
			'attribute_label',
			array(
				'label' => __( 'Attribute Name', 'premium-addons-for-elementor' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'attribute_typography',
				'label'    => __( 'Label Typography', 'premium-addons-for-elementor' ),
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				),
				'selector' => '{{WRAPPER}} .premium-variations th label , {{WRAPPER}} .premium-grouped-product th ,  {{WRAPPER}} .premium-grouped-product td',
			)
		);

		$this->start_controls_tabs( 'attribute_varation_style_tabs' );

		$this->start_controls_tab(
			'variation_attribute_style_normal_tab',
			array(
				'label' => __( 'Normal', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'attribute_label_color',
			array(
				'label'     => __( 'Label Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#333333',
				'selectors' => array(
					'{{WRAPPER}} .premium-variations th label , {{WRAPPER}} .premium-grouped-product th' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'attribute_label_odd_bg_color',
			array(
				'label'     => __( 'Odd Background Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#F2F2F2',
				'selectors' => array(
					'{{WRAPPER}} .premium-variations tr:nth-child(odd) th , {{WRAPPER}} .premium-grouped-product th:nth-child(odd)' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'attribute_label_even_bg_color',
			array(
				'label'     => __( 'Even Background Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array(
					'{{WRAPPER}} .premium-variations tr:nth-child(even) th , {{WRAPPER}} .premium-grouped-product tr th:nth-child(even) ' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'attribute_label_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-variations th  , {{WRAPPER}} .premium-variations td' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .premium-grouped-product th  , {{WRAPPER}} .premium-grouped-product td' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'variation_attribute_style_normal_tab_hover',
			array(
				'label' => __( 'Hover', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'attribute_label_color_hover',
			array(
				'label'     => __( 'Label Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#333333',
				'selectors' => array(
					'{{WRAPPER}} .premium-variations th label:hover , {{WRAPPER}} .premium-grouped-product th:hover  ' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'attribute_label_odd_bg_color_hover',
			array(
				'label'     => __( 'Odd Background Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array(
					'{{WRAPPER}} .premium-variations tr:nth-child(odd) th:hover , {{WRAPPER}} .premium-grouped-product tr th:nth-child(odd):hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'attribute_label_even_bg_color_hover',
			array(
				'label'     => __( 'Even Background Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#F2F2F2',
				'selectors' => array(
					'{{WRAPPER}} .premium-variations tr:nth-child(even) th:hover , {{WRAPPER}} .premium-grouped-product tr th:nth-child(even):hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'attribute_label_padding_hover',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-variations th:hover  , {{WRAPPER}} .premium-variations td:hover' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .premium-grouped-product th:hover  , {{WRAPPER}} .premium-grouped-product td:hover' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs( 'attribute_varation_style_tabs' );

		$this->add_control(
			'woo_content_table_style',
			array(
				'label' => __( 'Content Table Style', 'premium-addons-for-elementor' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'content_grouped_table',
				'label'    => __( 'Content Typography', 'premium-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .premium-grouped-product td',
			)
		);

		$this->add_control(
			'content_table_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#787878',
				'selectors' => array(
					'{{WRAPPER}} .premium-grouped-product td' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'variation_td_odd_content_bg_color',
			array(
				'label'     => __( 'Odd Background Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#F2F2F2',
				'selectors' => array(
					'{{WRAPPER}} .premium-variations tr:nth-child(odd) td , {{WRAPPER}} .premium-grouped-product tr:nth-child(odd) td ' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'variation_td_even_content_bg_color',
			array(
				'label'     => __( 'Even Background Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array(
					'{{WRAPPER}} .premium-variations tr:nth-child(even) td, {{WRAPPER}} .premium-grouped-product tr:nth-child(even) td' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'select_style',
			array(
				'label' => __( 'Select Style', 'premium-addons-for-elementor' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'variations_select',
				'selector' => '{{WRAPPER}} .premium-variations .product-attribute',
			)
		);

		$this->add_control(
			'variation_select_border_radius',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%' ),
				'default'    => array(
					'size' => 0,
				),
				'selectors'  => array(
					'{{WRAPPER}} .premium-variations .product-attribute' => 'border-radius: {{SIZE}}{{UNIT}};',

				),
			)
		);

		$this->end_controls_section();
	}


	/**
	 * Render Get Woocommerce Product Variations
	 *
	 * @since 4.10.36
	 * @access private
	 *
	 *  @param number $product_id   product id.
	 */
	private function get_woocommerce_product_attributes_for_add_to_cart( $product_id ) {
		$product = wc_get_product( $product_id );

		if ( ! $product || ! $product->is_type( 'variable' ) ) {
			return array();
		}

		$attributes      = $product->get_attributes();
		$attribute_terms = array();

		foreach ( $attributes as $attribute_name => $attribute ) {
			if ( $attribute->is_taxonomy() ) {
				$taxonomy = $attribute->get_taxonomy();
				$terms    = get_terms(
					array(
						'taxonomy'   => $taxonomy,
						'hide_empty' => false,
					)
				);

				if ( ! is_wp_error( $terms ) ) {
					$attribute_terms[ $taxonomy ] = $terms;
				}
			} else {
				$attribute_terms[ $attribute_name ] = $attribute->get_options();
			}
		}

		return $attribute_terms;
	}



	/**
	 * Render Image Icon
	 *
	 * @since 4.10.36
	 * @access private
	 */
	private function render_image_icon() {

		$settings = $this->get_settings_for_display();

		$icon_type = $settings['icon_type'];

		$alt = Control_Media::get_image_alt( $settings['custom_image'] );

		$this->add_render_attribute(
			'icon_img',
			array(
				'src' => $settings['custom_image']['url'],
				'alt' => $alt,
			)
		);

		if ( 'image' === $icon_type ) {
			$this->add_render_attribute( 'icon_img', 'class', 'premium-woo-btn-icon' );
		}
		?>

		<img <?php echo wp_kses_post( $this->get_render_attribute_string( 'icon_img' ) ); ?>>

		<?php
	}

	/**
	 * Render Woo CTA widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {

		$settings = $this->get_settings_for_display();

		$papro_activated = apply_filters( 'papro_activated', false );

		if ( ! $papro_activated || version_compare( PREMIUM_PRO_ADDONS_VERSION, '2.9.20', '<' ) ) {

			if ( 'add_to_cart' !== $settings['button_action'] ) {

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

		$id = $this->get_id();

		$button_action = $settings['button_action'];

		$button_size = 'premium-btn-' . $settings['button_size'];

		if ( ! empty( $settings['product_id'] ) ) {
			$product_id = $settings['product_id'];
		} elseif ( wp_doing_ajax() && ! empty( $settings['product_id'] ) ) {
			$product_id = (int) Utils::_unstable_get_super_global_value( $_POST, 'post_id' );
		} else {
			$product_id = get_queried_object_id();
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return;
		}

		$this->add_render_attribute(
			'woo_cta_wrapper',
			array(
				'class' => array(
					'pa-woo-cta-widget',
					'premium-wrapper-woo-cta',
				),
				'id'    => 'pa-woo-cta-widget-' . $id,
			)
		);

		$connected_to_mc = 'yes' === $settings['connect_to_pa_mc'];

		if ( $connected_to_mc ) {
			$this->add_render_attribute( 'woo_cta_wrapper', 'data-pa-mc-id', $settings['connected_mc_id'] );
		}

		$product_type = $product->get_type();

		if ( 'grouped' === $product_type ) {
			$this->add_render_attribute( 'woo_cta_wrapper', 'class', 'pa-grouped' );
		} elseif ( 'external' === $product_type ) {
			$this->add_render_attribute( 'woo_cta_wrapper', 'class', 'pa-external' );

			// Handle external/affiliate product.
			$external_url = $product->get_product_url();
			$button_text  = $product->get_button_text();

		} elseif ( 'variable' === $product_type ) {
			$attributes = $this->get_woocommerce_product_attributes_for_add_to_cart( $product_id );
		}

		$product_unavailable_message = $settings['product_unavailable_message'];

		$show_quantity = $settings['show_quantity'];

		$effect_class = Helper_Functions::get_button_class( $settings );

		$this->add_render_attribute( 'button', 'class', $effect_class );

		$this->add_render_attribute(
			'button',
			array(
				'class'             => array(
					'premium-woo-cta-button',
					$effect_class,
					$button_size,
				),
				'id'                => 'woo-cta-btn-' . $id,
				'data-product-id'   => $product_id,
				'data-actions'      => $button_action,
				'data-product-type' => $product->get_type(),
				'data-external-url' => 'external' === $product_type ? esc_url( $external_url ) : '',
			)
		);

		$in_wishlist    = false;
		$in_mc_wishlist = false;

		if ( 'add_to_cart' === $button_action ) {

			$redirect_to_cart = $settings['redirect_to_cart'];

			$this->add_render_attribute( 'button', 'data-redirect-to-cart', $redirect_to_cart );

		} elseif ( 'add_to_wishlist' === $button_action ) {

			$product_added_wishlist = $settings['wishlist_message'];
			$wishlist_remove_text   = $settings['wishlist_remove_text'];
			$wishlist_button_text   = $settings['wishlist_button_text'];
			$wishlist_icon          = $settings['icon_switcher'];

			// For YITH Wishlist.
			if ( function_exists( 'YITH_WCWL' ) ) {
				$in_wishlist = YITH_WCWL()->is_product_in_wishlist( $product_id );
			} elseif ( class_exists( 'WLFMC' ) ) {
				$in_mc_wishlist = class_exists( 'WLFMC' ) ? WLFMC()->is_product_in_wishlist( $product_id ) : false;
			}

			if ( $in_wishlist || $in_mc_wishlist ) {
				$this->add_render_attribute( 'button', 'class', 'premium-woo-icon-hidden' );
			}

			$this->add_render_attribute(
				'button',
				array(
					'data-product-added'         => $product_added_wishlist,
					' data-wishlist-remove-text' => $wishlist_remove_text,
					'data-wishlist-button-text'  => $wishlist_button_text,
					'data-icon-visible'          => $wishlist_icon,
				)
			);

		} elseif ( 'add_to_compare' === $button_action ) {
			$in_compare          = false;
			$in_yith_compare     = false;
			$compare_remove_text = $settings['compare_remove_text'];
			$compare_button_text = $settings['compare_button_text'];

			// For Ever Compare.
			if ( class_exists( 'Ever_Compare' ) ) {
				$cookie_name = 'ever_compare_compare_list';
				$products    = isset( $_COOKIE[ $cookie_name ] ) ? json_decode( sanitize_text_field( wp_unslash( $_COOKIE[ $cookie_name ] ) ) ) : array();
				$in_compare  = in_array( strval( $product_id ), $products, true );
			} elseif ( class_exists( 'YITH_Woocompare_Frontend' ) ) {
				// For YITH Compare.
				$yith_cookie_name = 'yith_woocompare_list';
				$yith_products    = isset( $_COOKIE[ $yith_cookie_name ] ) ? json_decode( stripslashes( sanitize_text_field( wp_unslash( $_COOKIE[ $yith_cookie_name ] ) ) ) ) : array();
				$in_yith_compare  = in_array( intval( $product_id ), $yith_products, true );
			}

			$this->add_render_attribute(
				'button',
				array(
					' data-compare-remove-text' => $compare_remove_text,
					'data-compare-button-text'  => $compare_button_text,
				)
			);

		}

		if ( 'yes' === $settings['icon_switcher'] ) {
			$icon_type = $settings['icon_type'];

			if ( ( 'yes' === $settings['draw_svg'] && 'icon' === $icon_type ) || 'svg' === $icon_type ) {
				$this->add_render_attribute( 'icon', 'class', 'premium-woo-btn-icon' );
			}

			if ( 'yes' === $settings['draw_svg'] ) {

				$this->add_render_attribute(
					'container',
					'class',
					array(
						'elementor-invisible',
						'premium-drawer-hover',
					)
				);

				if ( 'icon' === $icon_type ) {
					$this->add_render_attribute( 'icon', 'class', $settings['icon']['value'] );
				}

				$this->add_render_attribute(
					'icon',
					array(
						'class'            => 'premium-svg-drawer',
						'data-svg-reverse' => $settings['svg_reverse'],
						'data-svg-loop'    => $settings['svg_loop'],
						'data-svg-sync'    => $settings['svg_sync'],
						'data-svg-hover'   => $settings['svg_hover'],
						'data-svg-fill'    => $settings['svg_color'],
						'data-svg-frames'  => $settings['frames'],
						'data-svg-yoyo'    => $settings['svg_yoyo'],
						'data-svg-point'   => 0,
					)
				);

			} else {
				$this->add_render_attribute( 'icon', 'class', 'premium-svg-nodraw' );
			}

			if ( 'lottie' === $icon_type ) {
				$handle = 'lottie_icon';
				$this->add_render_attribute(
					$handle,
					array(
						'class'               => array(
							'premium-modal-trigger-animation',
							'premium-lottie-animation',
							'premium-woo-btn-icon',
						),
						'data-lottie-url'     => 'url' === $settings['lottie_source'] ? $settings['lottie_url'] : $settings['lottie_file']['url'],
						'data-lottie-loop'    => $settings['lottie_loop'],
						'data-lottie-reverse' => $settings['lottie_reverse'],
						'data-lottie-hover'   => $settings['lottie_hover'],
					)
				);
			}
		}
		?>

		<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'woo_cta_wrapper' ) ); ?> >
			<?php
			if ( ! $product || ! $product->is_in_stock() ) {
				// Display message if the product is not available.
				?>
				<?php if ( ! empty( $product_unavailable_message ) ) { ?>
				<div class="premium-unavailable-message"><?php echo wp_kses_post( $product_unavailable_message ); ?></div>
					<?php
				}
			} else {
				?>
				<?php if ( 'add_to_cart' === $button_action ) { ?>
					<?php
					if ( 'grouped' === $product_type ) {
						$child_products = $product->get_children()
						?>
						<table class='premium-grouped-product'>
							<tr><th>Product</th><th>Price</th><th>Quantity</th></tr>
							<?php
							foreach ( $child_products as $child_id ) {
								$child_product = wc_get_product( $child_id )
								?>
								<tr>
									<td><?php echo wp_kses_post( $child_product->get_name() ); ?></td>
									<td><?php echo wp_kses_post( wc_price( $child_product->get_price() ) ); ?></td>
									<td>
									<div class='pa-qty-wrapper'>
										<div class="quantity-grouped-wrapper">
											<input type="number" class="grouped_product_qty" name="<?php echo wp_kses_post( $child_id ); ?>" value="0" min="0">
											<div class="add-to-cart-icons-quantity-wrapper">
												<i type="button" class="fas fa-plus quantity-button g-plus"></i>
												<i type="button" class="fas fa-minus quantity-button g-minus"></i>
											</div>
										</div>
									</div>
									</td>
								</tr>
							<?php } ?>
						</table>
				<?php } ?>
					<?php if ( 'variable' === $product_type ) { ?>
						<?php if ( ! empty( $attributes ) ) : ?>
					<table class="premium-variations" cellspacing="0" role="presentation">
						<tbody>
							<?php foreach ( $attributes as $attribute_name => $terms ) : ?>
								<tr>
									<th class="label">
										<label for="<?php echo wp_kses_post( $attribute_name ); ?>">
											<?php echo wp_kses_post( wc_attribute_label( $attribute_name ) ); ?>
										</label>
									</th>
									<td class="value">
										<select id="<?php echo wp_kses_post( $attribute_name ); ?>" class="product-attribute" name="attribute_<?php echo wp_kses_post( $attribute_name ); ?>" data-attribute_name="attribute_<?php echo wp_kses_post( $attribute_name ); ?>" data-show_option_none="yes">
											<option value="">Choose an option</option>
												<?php foreach ( $terms as $term ) : ?>
											<option value="<?php echo wp_kses_post( is_object( $term ) ? $term->slug : $term ); ?>" class="attached enabled">
													<?php echo wp_kses_post( is_object( $term ) ? $term->name : $term ); ?>
											</option>
												<?php endforeach; ?>
										</select>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			<?php } ?>
			<?php } ?>

			<div class='premium-woo-btn-container'>
				<?php if ( $show_quantity ) : ?>
				<div class="quantity-input-wrapper">
					<input type="number" class="product-quantity"  value="1" min="1">
						<div class="add-to-cart-icons-quantity-wrapper">
							<i type="button" class="fas fa-plus quantity-button plus"></i>
							<i type="button" class="fas fa-minus quantity-button minus"></i>
						</div>
				</div>
				<?php endif; ?>
			<button <?php echo wp_kses_post( $this->get_render_attribute_string( 'button' ) ); ?> >
				<?php
				if ( 'yes' === $settings['icon_switcher'] ) :

					if ( 'icon' === $icon_type ) :

						if ( 'yes' !== $settings['draw_svg'] ) :
							Icons_Manager::render_icon(
								$settings['icon'],
								array(
									'class'       => array( 'premium-woo-btn-icon', 'premium-svg-nodraw' ),
									'aria-hidden' => 'true',
								)
							);
							?>
							<?php else : ?>
							<i <?php echo wp_kses_post( $this->get_render_attribute_string( 'icon' ) ); ?>></i>
								<?php
						endif;

					elseif ( 'image' === $icon_type ) :

						$this->render_image_icon();

					elseif ( 'lottie' === $icon_type ) :
						?>

						<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'lottie_icon' ) ); ?>></div>

					<?php else : ?>
						<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'icon' ) ); ?>>
							<?php $this->print_unescaped_setting( 'custom_svg' ); ?>
						</div>
						<?php
					endif;
						endif;
				?>
				<?php if ( ! empty( $settings['cart_button_text'] || $settings['wishlist_button_text'] || $settings['compare_button_text'] ) ) : ?>
					<div class="premium-button-text-icon-wrapper">
						<span class="premium-woo-btn-text">
							<?php
							if ( 'external' === $product_type ) {
								echo wp_kses_post( $button_text );
							} elseif ( 'add_to_cart' === $button_action ) {
									echo wp_kses_post( $settings['cart_button_text'] );
							} elseif ( 'add_to_wishlist' === $button_action ) {
								if ( $in_wishlist || $in_mc_wishlist ) {
									echo wp_kses_post( $settings['wishlist_remove_text'] );
								} else {
									echo wp_kses_post( $settings['wishlist_button_text'] );
								}
							} elseif ( $in_compare || $in_yith_compare ) {
								echo wp_kses_post( $settings['compare_remove_text'] );
							} else {
								echo wp_kses_post( $settings['compare_button_text'] );
							}
							?>

						</span>
					</div>
				<?php endif; ?>
				<?php if ( 'style6' === $settings['premium_button_hover_effect'] && 'yes' === $settings['mouse_detect'] ) : ?>
					<span class="premium-button-style6-bg"></span>
				<?php endif; ?>

				<?php if ( 'style8' === $settings['premium_button_hover_effect'] ) : ?>
					<?php echo Helper_Functions::get_btn_svgs( $settings['underline_style'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endif; ?>

			</button>
				<div class="premium-woo-cta__spinner"></div>

			</div>
			<?php } ?>

		</div>

		<?php
	}
}




