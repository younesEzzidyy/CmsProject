<?php
/**
 * Premium Mobile Menu.
 */

namespace PremiumAddons\Widgets;

// Elementor Classes.
use Elementor\Widget_Base;
use Elementor\Utils;
use Elementor\Icons_Manager;
use Elementor\Control_Media;
use Elementor\Repeater;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Background;

// PremiumAddons Classes.
use PremiumAddons\Admin\Includes\Admin_Helper;
use PremiumAddons\Includes\Helper_Functions;
use PremiumAddons\Includes\Premium_Template_Tags;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // If this file is called directly, abort.
}

/**
 * Class Premium_Mobile_Menu
 */
class Premium_Mobile_Menu extends Widget_Base {

	/**
	 * Check Icon Draw Option.
	 *
	 * @since 4.9.26
	 * @access public
	 */
	public function check_icon_draw() {
		$is_enabled = Admin_Helper::check_svg_draw( 'premium-mobile-menu' );
		return $is_enabled;
	}

	/**
	 * Template Instance
	 *
	 * @var template_instance
	 */
	protected $template_instance;

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
		return 'premium-mobile-menu';
	}

	/**
	 * Retrieve Widget Title.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function get_title() {
		return __( 'Mobile Menu', 'premium-addons-for-elementor' );
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
		return 'pa-mobile-menu';
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
		return array( 'pa', 'premium', 'premium mobile menu', 'nav', 'navigation', 'header' );
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
	 * Retrieve Widget Dependent CSS.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array CSS style handles.
	 */
	public function get_style_depends() {
		return array(
            'font-awesome-5-all',
			'pa-slick',
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
				'pa-slick',
				'premium-addons',
			)
		);
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
	 * Register Banner controls.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {  // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore

		$papro_activated = apply_filters( 'papro_activated', false );

		$draw_icon = $this->check_icon_draw();

		$this->start_controls_section(
			'menu_items_section',
			array(
				'label' => __( 'Menu Items', 'premium-addons-for-elementor' ),
			)
		);

		$repeater = new Repeater();

		$repeater->start_controls_tabs( 'item_controls_tabs' );

		$repeater->start_controls_tab(
			'item_controls_content_tab',
			array(
				'label' => __( 'Content', 'premium-addons-for-elementor' ),
			)
		);

		$repeater->add_control(
			'icon_type',
			array(
				'label'       => __( 'Icon Type', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'none'      => __( 'None', 'premium-addons-for-elementor' ),
					'icon'      => __( 'Icon', 'premium-addons-for-elementor' ),
					'image'     => __( 'Image', 'premium-addons-for-elementor' ),
					'animation' => __( 'Lottie Animation', 'premium-addons-for-elementor' ),
					'svg'       => __( 'SVG Code', 'premium-addons-for-elementor' ),
				),
				'default'     => 'icon',
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'icon',
			array(
				'label'       => __( 'Icon', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::ICONS,
				'default'     => array(
					'value'   => 'fas fa-star',
					'library' => 'fa-solid',
				),
				'condition'   => array(
					'icon_type' => 'icon',
				),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'custom_image',
			array(
				'label'     => __( 'Custom Image', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::MEDIA,
				'dynamic'   => array( 'active' => true ),
				'default'   => array(
					'url' => Utils::get_placeholder_image_src(),
				),
				'condition' => array(
					'icon_type' => 'image',
				),
			)
		);

		$repeater->add_control(
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

		$repeater->add_control(
			'lottie_source',
			array(
				'label'     => __( 'Source', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					'url'  => __( 'External URL', 'premium-addons-for-elementor' ),
					'file' => __( 'Media File', 'premium-addons-for-elementor' ),
				),
				'default'   => 'url',
				'condition' => array(
					'icon_type' => 'animation',
				),
			)
		);

		$repeater->add_control(
			'lottie_url',
			array(
				'label'       => __( 'Animation JSON URL', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'description' => 'Get JSON code URL from <a href="https://lottiefiles.com/" target="_blank">here</a>',
				'label_block' => true,
				'condition'   => array(
					'icon_type'     => 'animation',
					'lottie_source' => 'url',
				),
			)
		);

		$repeater->add_control(
			'lottie_file',
			array(
				'label'      => __( 'Upload JSON File', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::MEDIA,
				'media_type' => 'application/json',
				'condition'  => array(
					'icon_type'     => 'animation',
					'lottie_source' => 'file',
				),
			)
		);

		$animation_conditions = array(
			'relation' => 'or',
			'terms'    => array(
				array(
					'name'  => 'icon_type',
					'value' => 'animation',
				),
				array(
					'terms' => array(
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
						array(
							'name'  => 'draw_svg',
							'value' => 'yes',
						),
					),
				),
			),
		);

		$repeater->add_control(
			'draw_svg',
			array(
				'label'     => __( 'Draw Icon', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
                'description' => __( 'Enable this option to make the icon drawable. See ', 'premium-addons-for-elementor' ) . '<a href="https://www.youtube.com/watch?v=ZLr0bRe0RAY" target="_blank">tutorial</a>',
				'classes'   => $draw_icon ? '' : 'editor-pa-control-disabled',
				'condition' => array(
					'icon_type'      => array( 'icon', 'svg' ),
					'icon[library]!' => 'svg',
				),
			)
		);

		if ( $draw_icon ) {

			$repeater->add_control(
				'stroke_width',
				array(
					'label'     => __( 'Path Thickness', 'premium-addons-for-elementor' ),
					'type'      => Controls_Manager::SLIDER,
					'range'     => array(
						'px' => array(
							'min'  => 0,
							'max'  => 50,
							'step' => 0.1,
						),
					),
					'condition' => array(
						'icon_type' => array( 'icon', 'svg' ),
					),
					'selectors' => array(
						'{{WRAPPER}} {{CURRENT_ITEM}}.premium-mobile-menu__item:not(.lottie-item) svg *' => 'stroke-width: {{SIZE}}',
					),
				)
			);

			$repeater->add_control(
				'svg_sync',
				array(
					'label'     => __( 'Draw All Paths Together', 'premium-addons-for-elementor' ),
					'type'      => Controls_Manager::SWITCHER,
					'condition' => array(
						'icon_type' => 'svg',
						'draw_svg'  => 'yes',
					),
				)
			);

			$repeater->add_control(
				'frames',
				array(
					'label'       => __( 'Speed', 'premium-addons-for-elementor' ),
					'type'        => Controls_Manager::NUMBER,
					'description' => __( 'Larger value means longer animation duration.', 'premium-addons-for-elementor' ),
					'default'     => 5,
					'min'         => 1,
					'max'         => 100,
					'condition'   => array(
						'icon_type' => array( 'icon', 'svg' ),
						'draw_svg'  => 'yes',
					),
				)
			);

			$repeater->add_control(
				'svg_color',
				array(
					'label'     => __( 'After Draw Fill Color', 'premium-addons-for-elementor' ),
					'type'      => Controls_Manager::COLOR,
					'global'    => false,
					'separator' => 'after',
					'condition' => array(
						'icon_type' => array( 'icon', 'svg' ),
						'draw_svg'  => 'yes',
					),
				)
			);

		} else {

			Helper_Functions::get_draw_svg_notice(
				$repeater,
				'mobile',
				array(
					'icon_type'      => array( 'icon', 'svg' ),
					'icon[library]!' => 'svg',
				)
			);

		}

		$repeater->add_control(
			'lottie_loop',
			array(
				'label'        => __( 'Loop', 'premium-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'true',
				'default'      => 'true',
				'conditions'   => $animation_conditions,
			)
		);

		$repeater->add_control(
			'lottie_reverse',
			array(
				'label'        => __( 'Reverse', 'premium-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'true',
				'conditions'   => $animation_conditions,
			)
		);

		if ( $draw_icon ) {
			$repeater->add_control(
				'start_point',
				array(
					'label'       => __( 'Start Point (%)', 'premium-addons-for-elementor' ),
					'type'        => Controls_Manager::SLIDER,
					'description' => __( 'Set the point that the SVG should start from.', 'premium-addons-for-elementor' ),
					'default'     => array(
						'unit' => '%',
						'size' => 0,
					),
					'condition'   => array(
						'icon_type'       => array( 'icon', 'svg' ),
						'draw_svg'        => 'yes',
						'lottie_reverse!' => 'true',
					),
				)
			);

			$repeater->add_control(
				'end_point',
				array(
					'label'       => __( 'End Point (%)', 'premium-addons-for-elementor' ),
					'type'        => Controls_Manager::SLIDER,
					'description' => __( 'Set the point that the SVG should end at.', 'premium-addons-for-elementor' ),
					'default'     => array(
						'unit' => '%',
						'size' => 0,
					),
					'condition'   => array(
						'icon_type'      => array( 'icon', 'svg' ),
						'draw_svg'       => 'yes',
						'lottie_reverse' => 'true',
					),
				)
			);

			$repeater->add_control(
				'svg_hover',
				array(
					'label'        => __( 'Only Play on Hover', 'premium-addons-for-elementor' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'condition'    => array(
						'icon_type' => array( 'icon', 'svg' ),
						'draw_svg'  => 'yes',
					),
				)
			);

			$repeater->add_control(
				'svg_yoyo',
				array(
					'label'     => __( 'Yoyo Effect', 'premium-addons-for-elementor' ),
					'type'      => Controls_Manager::SWITCHER,
					'condition' => array(
						'icon_type'   => array( 'icon', 'svg' ),
						'draw_svg'    => 'yes',
						'lottie_loop' => 'true',
					),
				)
			);
		}

		$repeater->add_control(
			'menu_item_text_switcher',
			array(
				'label'     => __( 'Title', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'separator' => 'before',
				'default'   => 'yes',
			)
		);

		$repeater->add_control(
			'menu_item_text',
			array(
				'label'     => __( 'Item Title', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'condition' => array(
					'menu_item_text_switcher' => 'yes',
				),
			)
		);

		$repeater->add_control(
			'action',
			array(
				'label'       => __( 'Action', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'link'      => __( 'Link', 'premium-addons-for-elementor' ),
					'offcanvas' => apply_filters( 'pa_pro_label', __( 'Open Off-Canvas (Pro)', 'premium-addons-for-elementor' ) ),
				),
				'default'     => 'link',
				'separator'   => 'before',
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'link_select',
			array(
				'label'       => __( 'Link/URL', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'url'           => __( 'URL', 'premium-addons-for-elementor' ),
					'existing_page' => __( 'Existing Page', 'premium-addons-for-elementor' ),
				),
				'default'     => 'url',
				'label_block' => true,
				'condition'   => array(
					'action' => 'link',
				),
			)
		);

		$repeater->add_control(
			'link',
			array(
				'label'       => __( 'URL', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::URL,
				'dynamic'     => array( 'active' => true ),
				'placeholder' => 'https://premiumaddons.com/',
				'label_block' => true,
				'condition'   => array(
					'action'      => 'link',
					'link_select' => 'url',
				),
			)
		);

		$repeater->add_control(
			'existing_page',
			array(
				'label'       => __( 'Existing Page', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT2,
				'options'     => $this->getTemplateInstance()->get_all_posts(),
				'condition'   => array(
					'action'      => 'link',
					'link_select' => 'existing_page',
				),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'home_page',
			array(
				'label'     => __( 'Is Homepage', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => array(
					'action' => 'link',
				),
			)
		);

		$repeater->add_control(
			'offcanvas_id',
			array(
				'label'       => __( 'Off-Canvas ID', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'description' => __( 'Add the same ID you added in Off-canvas widget -> Content tab -> Trigger', 'premium-addons-for-elementor' ),
				'condition'   => array(
					'action' => 'offcanvas',
				),
			)
		);

		$repeater->add_control(
			'show_badge',
			array(
				'label'     => apply_filters( 'pa_pro_label', __( 'Badge (Pro)', 'premium-addons-for-elementor' ) ),
				'type'      => Controls_Manager::SWITCHER,
				'separator' => 'before',
			)
		);

		if ( ! $papro_activated ) {

			$get_pro = Helper_Functions::get_campaign_link( 'https://premiumaddons.com/pro', 'editor-page', 'wp-editor', 'get-pro' );

			$repeater->add_control(
				'papro_notice',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => __( 'This option is available in Premium Addons Pro. ', 'premium-addons-for-elementor' ) . '<a href="' . esc_url( $get_pro ) . '" target="_blank">' . __( 'Upgrade now!', 'premium-addons-for-elementor' ) . '</a>',
					'content_classes' => 'papro-upgrade-notice',
					'conditions'      => array(
						'relation' => 'or',
						'terms'    => array(
							array(
								'name'  => 'show_badge',
								'value' => 'yes',
							),
							array(
								'name'  => 'action',
								'value' => 'offcanvas',
							),
						),
					),
				)
			);

		} else {
			do_action( 'pa_mobile_menu_badge_controls', $repeater );
		}

		$repeater->end_controls_tab();

		$repeater->start_controls_tab(
			'item_controls_style_tab',
			array(
				'label' => __( 'Style', 'premium-addons-for-elementor' ),
			)
		);

		$repeater->add_control(
			'items_min_height',
			array(
				'label'      => __( 'Minimum Height', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'custom' ),
				'selectors'  => array(
					'{{WRAPPER}} {{CURRENT_ITEM}} .premium-mobile-menu__link' => 'min-height: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$repeater->add_control(
			'menu_text_color',
			array(
				'label'     => __( 'Text Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} {{CURRENT_ITEM}} .premium-mobile-menu__text span' => 'color: {{VALUE}};',
				),
				'condition' => array(
					'menu_item_text_switcher' => 'yes',
					'menu_item_text!'         => '',
				),
			)
		);

		$repeater->add_control(
			'item_icon_color',
			array(
				'label'     => __( 'Icon Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} {{CURRENT_ITEM}} i' => 'color: {{VALUE}};',
					'{{WRAPPER}} {{CURRENT_ITEM}}.premium-mobile-menu__item:not(.lottie-item) svg, {{WRAPPER}} {{CURRENT_ITEM}}.premium-mobile-menu__item:not(.lottie-item) svg *' => 'fill: {{VALUE}};',
				),
				'condition' => array(
					'icon_type!' => array( 'none', 'image', 'animation' ),
				),
			)
		);

		$repeater->add_control(
			'item_icon_hover_color',
			array(
				'label'     => __( 'Icon Hover Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} {{CURRENT_ITEM}}.premium-mobile-menu__item .premium-mobile-menu__link:hover i' => 'color: {{VALUE}};',
					'{{WRAPPER}} {{CURRENT_ITEM}}.premium-mobile-menu__item:not(.lottie-item) .premium-mobile-menu__link:hover svg, {{WRAPPER}} {{CURRENT_ITEM}}.premium-mobile-menu__item:not(.lottie-item) .premium-mobile-menu__link:hover svg *' => 'fill: {{VALUE}};',
				),
				'condition' => array(
					'icon_type!' => array( 'none', 'image', 'animation' ),
				),
			)
		);

		$repeater->add_control(
			'item_icon_size',
			array(
				'label'      => __( 'Icon Size', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} {{CURRENT_ITEM}} i' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} {{CURRENT_ITEM}} svg, {{WRAPPER}} {{CURRENT_ITEM}} img' => 'width: {{SIZE}}{{UNIT}} !important; height: {{SIZE}}{{UNIT}} !important',
				),
				'condition'  => array(
					'icon_type!' => 'none',
				),
			)
		);

		if ( $draw_icon ) {
			$repeater->add_control(
				'item_stroke_color',
				array(
					'label'     => __( 'Stroke Color', 'premium-addons-for-elementor' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '#61CE70',
					'selectors' => array(
						'{{WRAPPER}} {{CURRENT_ITEM}}.premium-mobile-menu__item:not(.lottie-item) svg *' => 'stroke: {{VALUE}};',
					),
					'condition' => array(
						'icon_type' => array( 'icon', 'svg' ),
					),
				)
			);
		}

		$repeater->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'item_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} {{CURRENT_ITEM}} .premium-mobile-menu__item-inner',
			)
		);

		$repeater->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'item_border',
				'selector' => '{{WRAPPER}} {{CURRENT_ITEM}} .premium-mobile-menu__item-inner',
			)
		);

		$repeater->add_control(
			'item_border_radius',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} {{CURRENT_ITEM}} .premium-mobile-menu__item-inner' => 'border-radius: {{SIZE}}{{UNIT}} !important',
				),
			)
		);

		$repeater->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'item_box_shadow',
				'selector' => '{{WRAPPER}} {{CURRENT_ITEM}} .premium-mobile-menu__item-inner',
			)
		);

		do_action( 'pa_mobile_menu_badge_style', $repeater );

		$repeater->add_responsive_control(
			'item_margin',
			array(
				'label'      => __( 'Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} {{CURRENT_ITEM}} .premium-mobile-menu__item-inner' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$repeater->add_responsive_control(
			'single_item_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} {{CURRENT_ITEM}} .premium-mobile-menu__item-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$repeater->end_controls_tab();

		$repeater->end_controls_tabs();

		$this->add_control(
			'menu_items',
			array(
				'label'       => __( 'Add Menu Items', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array(
						'icon' => array(
							'value'   => 'fas fa-home',
							'library' => 'fa-solid',
						),
					),
					array(
						'icon' => array(
							'value'   => 'fas fa-search',
							'library' => 'fa-solid',
						),

					),
					array(
						'icon' => array(
							'value'   => 'fas fa-plus-circle',
							'library' => 'fa-solid',
						),
					),
					array(
						'icon' => array(
							'value'   => 'far fa-heart',
							'library' => 'fa-regular',
						),
					),
					array(
						'icon' => array(
							'value'   => 'fas fa-shopping-cart',
							'library' => 'fa-solid',
						),
					),
				),
				'title_field' => '{{{ menu_item_text }}}',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'display_options_section',
			array(
				'label' => __( 'Display Options', 'premium-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_responsive_control(
			'menu_direction',
			array(
				'label'        => __( 'Menu Direction', 'premium-addons-for-elementor' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => array(
					'row'    => array(
						'title' => esc_html__( 'Row - horizontal', 'elementor' ),
						'icon'  => 'eicon-arrow-right',
					),
					'column' => array(
						'title' => esc_html__( 'Column - vertical', 'elementor' ),
						'icon'  => 'eicon-arrow-down',
					),
				),
				'render_type'  => 'template',
				'prefix_class' => 'premium-mobile-menu__dir-',
				'default'      => 'row',
			)
		);

		$this->add_responsive_control(
			'menu_display',
			array(
				'label'       => __( 'Menu Position', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'relative' => __( 'Relative', 'premium-addons-for-elementor' ),
					'fixed'    => __( 'Fixed', 'premium-addons-for-elementor' ),
				),
				'default'     => 'relative',
				'selectors'   => array(
					'{{WRAPPER}} .premium-mobile-menu__wrap' => 'position: {{VALUE}}',
				),
				'label_block' => true,
			)
		);

		$this->add_responsive_control(
			'menu_hpos',
			array(
				'label'        => __( 'Horizontal Position', 'premium-addons-for-elementor' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => array(
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
					'custom' => array(
						'title' => __( 'Custom', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-cog',
					),
				),
				'prefix_class' => 'premium-mobile-menu__',
				'toggle'       => false,
				'default'      => 'left',
				'condition'    => array(
					'menu_display' => 'fixed',
				),
			)
		);

		$this->add_responsive_control(
			'menu_custom_hpos',
			array(
				'label'      => __( 'Horizontal Offset', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 800,
					),
				),
				'default'    => array(
					'size' => 0,
					'unit' => '%',
				),
				'selectors'  => array(
					'{{WRAPPER}} .premium-mobile-menu__wrap'    => 'left: {{SIZE}}{{UNIT}}',
				),
				'condition'  => array(
					'menu_display' => 'fixed',
					'menu_hpos'    => 'custom',
				),
			)
		);

		$this->add_responsive_control(
			'menu_vpos',
			array(
				'label'        => __( 'Vertical Position', 'premium-addons-for-elementor' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => array(
					'top'    => array(
						'title' => __( 'Top', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-arrow-up',
					),
					'bottom' => array(
						'title' => __( 'Bottom', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-arrow-down',
					),
					'custom' => array(
						'title' => __( 'Custom', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-cog',
					),
				),
				'prefix_class' => 'premium-mobile-menu__',
				'toggle'       => false,
				'default'      => 'bottom',
				'condition'    => array(
					'menu_display' => 'fixed',
				),
			)
		);

		$this->add_responsive_control(
			'menu_custom_vpos',
			array(
				'label'     => __( 'Vertical Offset (%)', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 0,
					'unit' => '%',
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-mobile-menu__wrap'    => 'bottom: {{SIZE}}{{UNIT}}',
				),
				'condition' => array(
					'menu_display' => 'fixed',
					'menu_vpos'    => 'custom',
				),
			)
		);

		$this->add_control(
			'zindex',
			array(
				'label'     => __( 'Z-index', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 99,
				'condition' => array(
					'menu_display' => 'fixed',
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-mobile-menu__wrap' => 'z-index: {{VALUE}}',
				),
			)
		);

		$this->add_responsive_control(
			'menu_width',
			array(
				'label'      => __( 'Menu Width', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'vw', 'custom' ),
				'separator'  => 'before',
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 1500,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .premium-mobile-menu__list' => 'width: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_responsive_control(
			'items_spacing',
			array(
				'label'      => __( 'Items Spacing', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'custom' ),
				'selectors'  => array(
					'{{WRAPPER}}.premium-mobile-menu__dir-row .premium-mobile-menu__list' => 'column-gap: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}}.premium-mobile-menu__dir-column .premium-mobile-menu__list' => 'row-gap: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_responsive_control(
			'menu_align',
			array(
				'label'     => __( 'Menu Alignment', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => __( 'Left', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center'     => array(
						'title' => __( 'Center', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-text-align-center',
					),
					'flex-end'   => array(
						'title' => __( 'Right', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'   => 'center',
				'selectors' => array(
					'{{WRAPPER}} .premium-mobile-menu__items-wrap' => 'justify-content: {{VALUE}};',
				),
				'condition' => array(
					'menu_display' => 'relative',
				),
			)
		);

		$this->add_responsive_control(
			'item_display',
			array(
				'label'       => __( 'Item Display', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'row'    => __( 'Inline', 'premium-addons-for-elementor' ),
					'column' => __( 'Block', 'premium-addons-for-elementor' ),
				),
				'default'     => 'column',
				'selectors'   => array(
					'{{WRAPPER}} .premium-mobile-menu__link' => 'flex-direction: {{VALUE}}',
				),
				'label_block' => true,
			)
		);

		$this->add_control(
			'active_item_indicator',
			array(
				'label'        => apply_filters( 'pa_pro_label', __( 'Active Item Indicator (Pro)', 'premium-addons-for-elementor' ) ),
				'type'         => Controls_Manager::SWITCHER,
				'separator'    => 'before',
				'render_type'  => $papro_activated ? 'ui' : 'template',
				'prefix_class' => 'premium-mobile-menu__indicator-',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'carousel_section',
			array(
				'label'     => apply_filters( 'pa_pro_label', __( 'Carousel (Pro)', 'premium-addons-for-elementor' ) ),
				'condition' => array(
					'menu_direction' => 'row',
				),
			)
		);

		$this->add_control(
			'carousel',
			array(
				'label'              => __( 'Enable Carousel', 'premium-addons-for-elementor' ),
				'type'               => Controls_Manager::SWITCHER,
				'frontend_available' => true,
			)
		);

		do_action( 'pa_mobile_menu_carousel_controls', $this );

		$this->end_controls_section();

        $this->start_controls_section(
			'section_pa_docs',
			array(
				'label' => __( 'Help & Docs', 'premium-addons-for-elementor' ),
			)
		);

		$docs = array(
			'https://premiumaddons.com/docs/elementor-mobile-menu-widget-tutorial/' => __( 'Getting started »', 'premium-addons-for-elementor' ),
			'https://www.youtube.com/watch?v=OPSlk9YoIGI' => __( 'Check the video tutorial »', 'premium-addons-for-elementor' ),
		);

		$doc_index = 1;
		foreach ( $docs as $url => $title ) {

			$doc_url = Helper_Functions::get_campaign_link( $url, 'editor-page', 'wp-editor', 'get-support' );

			$this->add_control(
				'doc_' . $doc_index,
				array(
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => sprintf( '<a href="%s" target="_blank">%s</a>', $doc_url, $title ),
					'content_classes' => 'editor-pa-doc',
				)
			);

			++$doc_index;

		}

		$this->end_controls_section();

		$this->start_controls_section(
			'item_style',
			array(
				'label' => __( 'Menu Item', 'premium-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'items_min_height',
			array(
				'label'      => __( 'Minimum Height', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'custom' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-mobile-menu__link' => 'min-height: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'item_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .premium-mobile-menu__item-inner',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'item_border',
				'selector' => '{{WRAPPER}} .premium-mobile-menu__item-inner',
			)
		);

		$this->add_control(
			'item_border_radius',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-mobile-menu__item-inner' => 'border-radius: {{SIZE}}{{UNIT}} !important',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'item_box_shadow',
				'selector' => '{{WRAPPER}} .premium-mobile-menu__item-inner',
			)
		);

		$this->add_responsive_control(
			'item_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-mobile-menu__item-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'item_icon_style',
			array(
				'label' => __( 'Item Icon', 'premium-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'item_icon_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-mobile-menu__item i' => 'color: {{VALUE}};',
					' {{WRAPPER}} .premium-mobile-menu__item:not(.lottie-item) svg, {{WRAPPER}} .premium-mobile-menu__item:not(.lottie-item) svg *' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'item_icon_hover_color',
			array(
				'label'     => __( 'Hover Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-mobile-menu__item .premium-mobile-menu__link:hover i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .premium-mobile-menu__item:not(.lottie-item) .premium-mobile-menu__link:hover svg, {{WRAPPER}} .premium-mobile-menu__item:not(.lottie-item) .premium-mobile-menu__link:hover svg *' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'item_icon_size',
			array(
				'label'      => __( 'Icon Size', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-mobile-menu__item i' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .premium-mobile-menu__item svg, {{WRAPPER}} .premium-mobile-menu__item img' => 'width: {{SIZE}}{{UNIT}} !important; height: {{SIZE}}{{UNIT}} !important',
				),
			)
		);

		if ( $draw_icon ) {
			$this->add_control(
				'item_stroke_color',
				array(
					'label'     => __( 'Stroke Color', 'premium-addons-for-elementor' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '#61CE70',
					'selectors' => array(
						'{{WRAPPER}} .premium-mobile-menu__item:not(.lottie-item) svg *' => 'stroke: {{VALUE}};',
					),
				)
			);
		}

		$this->add_responsive_control(
			'icon_margin',
			array(
				'label'      => __( 'Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-mobile-menu__icon-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'item_title_style',
			array(
				'label' => __( 'Item Title', 'premium-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'menu_text_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-mobile-menu__text span' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'menu_text_hover_color',
			array(
				'label'     => __( 'Hover Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-mobile-menu__link:hover .premium-mobile-menu__text span' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'menu_text_typography',
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				),
				'selector' => '{{WRAPPER}} .premium-mobile-menu__text span',
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'menu_text_shadow',
				'selector' => '{{WRAPPER}} .premium-mobile-menu__text span',
			)
		);

		$this->end_controls_section();

		do_action( 'pa_mobile_menu_badge_style_tab', $this );

		$this->start_controls_section(
			'active_item_style',
			array(
				'label' => __( 'Active Menu Item', 'premium-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'active_item_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .active-menu-item .premium-mobile-menu__item-inner',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'active_item_border',
				'selector' => '{{WRAPPER}} .active-menu-item .premium-mobile-menu__item-inner',
			)
		);

		$this->add_control(
			'active_item_border_radius',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .active-menu-item .premium-mobile-menu__item-inner' => 'border-radius: {{SIZE}}{{UNIT}} !important',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'active_item_box_shadow',
				'selector' => '{{WRAPPER}} .active-menu-item .premium-mobile-menu__item-inner',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'active_item_icon_style',
			array(
				'label' => __( 'Active Item Icon', 'premium-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'active_item_icon_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .active-menu-item.premium-mobile-menu__item i' => 'color: {{VALUE}};',
					' {{WRAPPER}} .active-menu-item.premium-mobile-menu__item:not(.lottie-item) svg, {{WRAPPER}} .active-menu-item.premium-mobile-menu__item:not(.lottie-item) svg *' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'active_item_icon_hover_color',
			array(
				'label'     => __( 'Hover Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .active-menu-item.premium-mobile-menu__item .premium-mobile-menu__link:hover i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .active-menu-item.premium-mobile-menu__item:not(.lottie-item) .premium-mobile-menu__link:hover svg, {{WRAPPER}} .active-menu-item.premium-mobile-menu__item:not(.lottie-item) .premium-mobile-menu__link:hover svg *' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'active_item_icon_size',
			array(
				'label'      => __( 'Icon Size', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .active-menu-item.premium-mobile-menu__item i' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .active-menu-item.premium-mobile-menu__item svg, {{WRAPPER}} .active-menu-item.premium-mobile-menu__item img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}',
				),
			)
		);

		if ( $draw_icon ) {
			$this->add_control(
				'active_item_stroke_color',
				array(
					'label'     => __( 'Stroke Color', 'premium-addons-for-elementor' ),
					'type'      => Controls_Manager::COLOR,
					'default'   => '#61CE70',
					'selectors' => array(
						'{{WRAPPER}} .active-menu-item.premium-mobile-menu__item:not(.lottie-item) svg *' => 'stroke: {{VALUE}};',
					),
				)
			);
		}

		$this->end_controls_section();

		$this->start_controls_section(
			'active_item_title_style',
			array(
				'label' => __( 'Active Item Title', 'premium-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'active_menu_text_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .active-menu-item .premium-mobile-menu__text span' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'active_menu_text_hover_color',
			array(
				'label'     => __( 'Hover Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .active-menu-item .premium-mobile-menu__link:hover .premium-mobile-menu__text span' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'active_menu_text_shadow',
				'selector' => '{{WRAPPER}} .active-menu-item .premium-mobile-menu__text span',
			)
		);

		$this->end_controls_section();

		do_action( 'pa_mobile_menu_indicator_carousel_style', $this );

		$this->start_controls_section(
			'items_container_style',
			array(
				'label' => __( 'Items Container', 'premium-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'items_container_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .premium-mobile-menu__list',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'items_container_border',
				'selector' => '{{WRAPPER}} .premium-mobile-menu__list',
			)
		);

		$this->add_control(
			'items_container_border_radius',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-mobile-menu__list' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'items_container_shadow',
				'selector' => '{{WRAPPER}} .premium-mobile-menu__list',
			)
		);

		$this->add_responsive_control(
			'items_container_margin',
			array(
				'label'      => __( 'Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-mobile-menu__list' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->add_responsive_control(
			'items_container_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-mobile-menu__list' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render Banner widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {

		$settings = $this->get_settings_for_display();

		$papro_activated = apply_filters( 'papro_activated', false );

		if ( ! $papro_activated || version_compare( PREMIUM_PRO_ADDONS_VERSION, '2.9.17', '<' ) ) {

			if ( 'yes' === $settings['carousel'] || 'yes' === $settings['active_item_indicator'] ) {

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

		$menu_items = $settings['menu_items'];

		if ( ! is_array( $menu_items ) ) {
			return;
		}

		$id = $this->get_id();

		$is_edit_mode = \Elementor\Plugin::$instance->editor->is_edit_mode();

        $current_page_url   = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

		$is_home = is_front_page();

		$this->add_render_attribute( 'wrap', 'class', 'premium-mobile-menu__wrap' );

		$carousel = 'yes' === $settings['carousel'] ? true : false;

		if ( $carousel ) {

			$this->add_render_attribute(
				'wrap',
				array(
					'data-rtl' => is_rtl(),
				)
			);

		}

		?>

			<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'wrap' ) ); ?>>
				<div class="premium-mobile-menu__items-wrap">

					<ul class="premium-mobile-menu__list">
						<?php
						foreach ( $menu_items as $index => $item ) {

							if ( ! $papro_activated || version_compare( PREMIUM_PRO_ADDONS_VERSION, '2.9.17', '<' ) ) {

								if ( 'offcanvas' === $item['action'] || 'yes' === $item['show_badge'] ) {

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

							$this->add_render_attribute(
								'menu-item-' . $index,
								array(
									'class' => array(
										'premium-mobile-menu__item',
										'elementor-repeater-item-' . $item['_id'],
									),
								)
							);

							$animation_key = 'icon_lottie_' . $index;

							if ( 'icon' === $item['icon_type'] || 'svg' === $item['icon_type'] ) {

								$this->add_render_attribute( $animation_key, 'class', 'premium-drawable-icon' );

								if ( 'yes' === $item['draw_svg'] ) {

									$this->add_render_attribute(
										$animation_key,
										array(
											'class' => array( 'premium-svg-drawer', 'elementor-invisible' ),
											'data-svg-reverse' => $item['lottie_reverse'],
											'data-svg-loop' => $item['lottie_loop'],
											'data-svg-hover' => $item['svg_hover'],
											'data-svg-sync' => $item['svg_sync'],
											'data-svg-fill' => $item['svg_color'],
											'data-svg-frames' => $item['frames'],
											'data-svg-yoyo' => $item['svg_yoyo'],
											'data-svg-point' => $item['lottie_reverse'] ? $item['end_point']['size'] : $item['start_point']['size'],
										)
									);

								} else {
									$this->add_render_attribute( $animation_key, 'class', 'premium-svg-nodraw' );
								}
							} elseif ( 'animation' === $item['icon_type'] ) {

								$this->add_render_attribute( 'menu-item-' . $index, 'class', 'lottie-item' );

								$this->add_render_attribute(
									$animation_key,
									array(
										'class'            => 'premium-lottie-animation',
										'data-lottie-url'  => 'url' === $item['lottie_source'] ? $item['lottie_url'] : $item['lottie_file']['url'],
										'data-lottie-loop' => $item['lottie_loop'],
										'data-lottie-reverse' => $item['lottie_reverse'],
									)
								);
							}

							$item_link = $this->get_repeater_setting_key( 'link', 'menu_items', $index );

							$this->add_render_attribute(
								$item_link,
								array(
									'class'      => 'premium-mobile-menu__link',
									'aria-label' => 'yes' === $item['menu_item_text_switcher'] ? $item['menu_item_text'] : '',
								)
							);

							if ( 'link' === $item['action'] ) {
								$link_url = ( 'url' === $item['link_select'] ) ? $item['link'] : get_permalink( $item['existing_page'] );

								if ( 'url' === $item['link_select'] ) {
									$this->add_link_attributes( $item_link, $link_url );

									$link = $link_url['url'];

                                    $segments    = explode( '/', $link );
                                    $target_link = end( $segments );

                                    $this->add_render_attribute( 'menu-item-' . $index, 'data-target', $target_link );

								} else {
									$this->add_render_attribute( $item_link, 'href', $link_url );

									$link = $link_url;
								}

								if ( $is_edit_mode && 2 === $index ) {
									$this->add_render_attribute( 'menu-item-' . $index, 'class', 'active-menu-item' );
								} elseif ( false !== strpos( $link, $current_page_url ) && ! $is_home && false === strpos( $link, '#' ) ) {
										$this->add_render_attribute( 'menu-item-' . $index, 'class', 'active-menu-item' );
								} elseif ( 'yes' === $item['home_page'] && $is_home ) {
									$this->add_render_attribute( 'menu-item-' . $index, 'class', 'active-menu-item' );
								}
							} else {
								$this->add_render_attribute( 'menu-item-' . $index, 'id', $item['offcanvas_id'] );
							}

							?>
							<li <?php echo wp_kses_post( $this->get_render_attribute_string( 'menu-item-' . $index ) ); ?> >
								<div class="premium-mobile-menu__item-inner">

									<a <?php echo wp_kses_post( $this->get_render_attribute_string( $item_link ) ); ?>>

										<?php if ( 'yes' === $item['show_badge'] && ! empty( $item['badge_title'] ) ) : ?>
											<div class="premium-mobile-menu__badge">
												<span><?php echo wp_kses_post( $item['badge_title'] ); ?></span>
											</div>
										<?php endif; ?>

										<?php if ( 'none' !== $item['icon_type'] ) : ?>
											<div class="premium-mobile-menu__icon-wrap">
												<?php

												if ( 'icon' === $item['icon_type'] ) {
													if ( 'yes' !== $item['draw_svg'] ) {
														echo '<div class="premium-drawable-icon">';
															Icons_Manager::render_icon(
																$item['icon'],
																array(
																	'class'       => array( 'premium-svg-nodraw' ),
																	'aria-hidden' => 'true',
																)
															);
														echo '</div>';
													} else {
														?>
															<div <?php echo wp_kses_post( $this->get_render_attribute_string( $animation_key ) ); ?>>
																<i class="<?php echo esc_attr( $item['icon']['value'] ); ?>"></i>
															</div>
															<?php
													}
												} elseif ( 'svg' === $item['icon_type'] ) {
													?>
														<div <?php echo wp_kses_post( $this->get_render_attribute_string( $animation_key ) ); ?>>
														<?php echo $this->print_unescaped_setting( 'custom_svg', 'menu_items', $index ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
														</div>
														<?php
												} elseif ( 'text' === $item['icon_type'] ) {
													?>
														<div class="premium-bullet-list-icon-text">
															<p <?php echo wp_kses_post( $this->get_render_attribute_string( $text_icon ) ); ?>>
															<?php echo wp_kses_post( $item['list_text_icon'] ); ?>
															</p>
														</div>
														<?php
												} elseif ( 'image' === $item['icon_type'] ) {
													if ( ! empty( $item['custom_image']['url'] ) ) {
														$alt = Control_Media::get_image_alt( $item['custom_image'] );
														echo '<img src="' . esc_url( $item['custom_image']['url'] ) . '" alt="' . esc_attr( $alt ) . '">';
													}
												} else {
													echo '<div ' . wp_kses_post( $this->get_render_attribute_string( $animation_key ) ) . '></div>';
												}

												?>
											</div>
										<?php endif; ?>

										<?php if ( 'yes' === $item['menu_item_text_switcher'] && ! empty( $item['menu_item_text'] ) ) : ?>
											<div class="premium-mobile-menu__text">
												<span><?php echo wp_kses_post( $item['menu_item_text'] ); ?></span>
											</div>
										<?php endif; ?>

									</a>

								</div>
							</li>

						<?php } ?>
					</ul>
				</div>
			</div>

		<?php
	}
}
