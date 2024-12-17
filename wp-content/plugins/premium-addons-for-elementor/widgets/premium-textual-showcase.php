<?php
/**
 * Premium Textual Showcase.
 */

namespace PremiumAddons\Widgets;

// Elementor Classes.
use Elementor\Utils;
use Elementor\Repeater;
use Elementor\Widget_Base;
use Elementor\Icons_Manager;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;

// PremiumAddons Classes.
use PremiumAddons\Includes\Helper_Functions;
use PremiumAddons\Admin\Includes\Admin_Helper;
use PremiumAddons\Includes\Premium_Template_Tags;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // If this file is called directly, abort.
}

/**
 * Class Premium_Textual_Showcase.
 */
class Premium_Textual_Showcase extends Widget_Base {

	/**
	 * Template Instance
	 *
	 * @var template_instance
	 */
	protected $template_instance;

	/**
	 * Check Icon Draw Option.
	 *
	 * @since 4.9.26
	 * @access public
	 */
	public function check_icon_draw() {
		$is_enabled = Admin_Helper::check_svg_draw( 'premium-textual-showcase' );
		return $is_enabled;
	}

	/**
	 * Get Elementor Helper Instance.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function getTemplateInstance() {
		$this->template_instance = Premium_Template_Tags::getInstance();

		return $this->template_instance;
	}

	/**
	 * Retrieve Widget Name.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function get_name() {
		return 'premium-textual-showcase';
	}

	/**
	 * Retrieve Widget Title.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function get_title() {
		return __( 'Textual Showcase', 'premium-addons-for-elementor' );
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
		return 'pa-showcase';
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
				'premium-addons',
			)
		);
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
	public function get_keywords() {
		return array( 'pa', 'premium', 'premium textual showcase', 'textual', 'showcase', 'image' );
	}

    protected function is_dynamic_content():bool {
        return false;
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
	 * Register Tiktok Feed controls.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function register_controls() {

		$this->add_general_controls();

		$this->add_content_style_controls();
		$this->add_item_container_style_controls();
	}

	/**
	 * Add general controls.
	 *
	 * @access private
	 */
	private function add_general_controls() {

		$papro_activated = apply_filters( 'papro_activated', false );

		$this->start_controls_section(
			'sect_gen_controls',
			array(
				'label' => __( 'Content', 'premium-addons-for-elementor' ),
			)
		);

		$draw_icon = $this->check_icon_draw();

		$svg_draw_conds = array(
			'relation' => 'or',
			'terms'    => array(
				array(
					'name'  => 'item_type',
					'value' => 'svg',
				),
				array(
					'terms' => array(
						array(
							'name'  => 'item_type',
							'value' => 'icon',
						),
						array(
							'name'     => 'icon[library]',
							'operator' => '!==',
							'value'    => 'svg',
						),
					),
				),
			),
		);

		$svg_draw_conds_hov = array(
			'relation' => 'or',
			'terms'    => array(
				array(
					'name'  => 'item_type_hov',
					'value' => 'svg',
				),
				array(
					'terms' => array(
						array(
							'name'  => 'item_type_hov',
							'value' => 'icon',
						),
						array(
							'name'     => 'icon_hov[library]',
							'operator' => '!==',
							'value'    => 'svg',
						),
					),
				),
			),
		);

		$repeater = new Repeater();

		$repeater->start_controls_tabs( 'item_tabs' );

		$repeater->start_controls_tab(
			'normal_state',
			array(
				'label' => __( 'Element', 'premium-addons-for-elementor' ),
			)
		);

		$repeater->add_control(
			'item_type',
			array(
				'label'       => __( 'Type', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'render_type' => 'template',
				'options'     => array(
					'icon'   => __( 'Icon', 'premium-addons-for-elementor' ),
					'text'   => __( 'Text', 'premium-addons-for-elementor' ),
					'image'  => __( 'Image', 'premium-addons-for-elementor' ),
					'lottie' => __( 'Lottie', 'premium-addons-for-elementor' ),
					'svg'    => apply_filters( 'pa_pro_label', __( 'SVG Code (Pro)', 'premium-addons-for-elementor' ) ),
				),
				'default'     => 'text',
			)
		);

		$repeater->add_control(
			'item_txt',
			array(
				'label'       => __( 'Text', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'label_block' => true,
				'condition'   => array(
					'item_type' => 'text',
				),
			)
		);

		$repeater->add_control(
			'item_txt_tag',
			array(
				'label'       => __( 'HTML Tag', 'premium-addons-for-elementor' ),
				'description' => __( 'Select an HTML tag for the text.', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'span',
				'options'     => array(
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
					'p'    => 'p',
				),
				'label_block' => true,
				'condition'   => array(
					'item_type' => 'text',
				),
			)
		);

		$repeater->add_control(
			'content_image',
			array(
				'label'       => __( 'Choose Image', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::MEDIA,
				'label_block' => true,
				'default'     => array(
					'url' => Utils::get_placeholder_image_src(),
				),
				'dynamic'     => array( 'active' => true ),
				'condition'   => array(
					'item_type' => 'image',
				),
			)
		);

		$repeater->add_group_control(
			Group_Control_Image_Size::get_type(),
			array(
				'name'      => 'thumbnail',
				'default'   => 'full',
				'condition' => array(
					'item_type' => 'image',
				),
			)
		);

		$repeater->add_responsive_control(
			'item_img_fit',
			array(
				'label'     => __( 'Image Fit', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					''        => __( 'Default', 'premium-addons-for-elementor' ),
					'cover'   => __( 'Cover', 'premium-addons-for-elementor' ),
					'fill'    => __( 'Fill', 'premium-addons-for-elementor' ),
					'contain' => __( 'Contain', 'premium-addons-for-elementor' ),
				),
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__main-item' => 'object-fit:{{VALUE}};',
				),
				'condition' => array(
					'item_type' => 'image',
				),
			)
		);

		$repeater->add_control(
			'icon',
			array(
				'label'       => __( 'Choose Icon', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::ICONS,
				'skin'        => 'inline',
				'default'     => array(
					'value'   => 'fas fa-star',
					'library' => 'fa-solid',
				),
				'label_block' => false,
				'condition'   => array(
					'item_type' => 'icon',
				),
			)
		);

		if ( $papro_activated ) {

			do_action( 'pa_showcase_svg', $repeater );

		}

		$repeater->add_control(
			'draw_svg',
			array(
				'label'       => __( 'Draw Icon', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SWITCHER,
                'description' => __( 'Enable this option to make the icon drawable. See ', 'premium-addons-for-elementor' ) . '<a href="https://www.youtube.com/watch?v=ZLr0bRe0RAY" target="_blank">tutorial</a>',
				'classes'     => $draw_icon ? '' : 'editor-pa-control-disabled',
				'description' => __( 'Use this option to draw your Font Awesome/SVG Icons.', 'premium-addons-for-elementor' ),
				'conditions'  => $svg_draw_conds,
			)
		);

		if ( $draw_icon ) {

			$repeater->add_control(
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
					'conditions' => array(
						'terms' => array(
							$svg_draw_conds,
						),
					),
					'selectors'  => array(
						'{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__main-item.premium-drawable-icon *' => 'stroke-width: {{SIZE}};',
					),
				)
			);

			$repeater->add_control(
				'svg_sync',
				array(
					'label'     => __( 'Draw All Paths Together', 'premium-addons-for-elementor' ),
					'type'      => Controls_Manager::SWITCHER,
					'condition' => array(
						'draw_svg'  => 'yes',
						'item_type' => 'svg',
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
					'conditions'  => array(
						'terms' => array(
							array(
								'name'  => 'draw_svg',
								'value' => 'yes',
							),
							$svg_draw_conds,
						),
					),
				)
			);

			$repeater->add_control(
				'stroke_colors',
				array(
					'label'      => __( 'Stroke Color', 'premium-addons-for-elementor' ),
					'type'       => Controls_Manager::COLOR,
					'conditions' => array(
						'terms' => array(
							$svg_draw_conds,
						),
					),
					'selectors'  => array(
						'{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__main-item.premium-drawable-icon *' => 'stroke: {{VALUE}};',
					),
				)
			);

			$repeater->add_control(
				'svg_color',
				array(
					'label'      => __( 'After Draw Fill Color', 'premium-addons-for-elementor' ),
					'type'       => Controls_Manager::COLOR,
					'global'     => false,
					'conditions' => array(
						'terms' => array(
							array(
								'name'  => 'draw_svg',
								'value' => 'yes',
							),
							$svg_draw_conds,
						),
					),
				)
			);
		} else {
			Helper_Functions::get_draw_svg_notice(
				$repeater,
				'textual',
				$svg_draw_conds,
				'',
				'conditions'
			);
		}

		$repeater->add_control(
			'lottie_url',
			array(
				'label'       => __( 'Animation JSON URL', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'description' => 'Get JSON code URL from <a href="https://lottiefiles.com/" target="_blank">here</a>',
				'label_block' => true,
				'condition'   => array(
					'item_type' => 'lottie',
				),
			)
		);

		$lottie_cond = array(
			'relation' => 'or',
			'terms'    => array(
				array(
					'name'  => 'item_type',
					'value' => 'lottie',
				),
				array(
					'terms' => array(
						array(
							'name'  => 'draw_svg',
							'value' => 'yes',
						),
						$svg_draw_conds,
					),
				),
			),
		);

		$repeater->add_control(
			'lottie_loop',
			array(
				'label'        => __( 'Loop', 'premium-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'true',
				'default'      => 'true',
				'conditions'   => $lottie_cond,
			)
		);

		$repeater->add_control(
			'lottie_reverse',
			array(
				'label'        => __( 'Reverse', 'premium-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'true',
				'conditions'   => $lottie_cond,
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
					'conditions'  => array(
						'terms' => array(
							array(
								'name'  => 'draw_svg',
								'value' => 'yes',
							),
							array(
								'name'     => 'lottie_reverse',
								'operator' => '!==',
								'value'    => 'true',
							),
							$svg_draw_conds,
						),
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
					'conditions'  => array(
						'terms' => array(
							array(
								'name'  => 'draw_svg',
								'value' => 'yes',
							),
							array(
								'name'  => 'lottie_reverse',
								'value' => 'true',
							),
							$svg_draw_conds,
						),
					),
				)
			);

			$repeater->add_control(
				'svg_yoyo',
				array(
					'label'      => __( 'Yoyo Effect', 'premium-addons-for-elementor' ),
					'type'       => Controls_Manager::SWITCHER,
					'conditions' => array(
						'terms' => array(
							array(
								'name'  => 'draw_svg',
								'value' => 'yes',
							),
							array(
								'name'  => 'lottie_loop',
								'value' => 'true',
							),
							$svg_draw_conds,
						),
					),
				)
			);
		}

		$repeater->add_control(
			'style',
			array(
				'label'     => esc_html__( 'Element Style', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$repeater->add_responsive_control(
			'item_txt_align',
			array(
				'label'     => __( 'Text Alignment', 'premium-addons-for-elementor' ),
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
					'{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__main-item.pa-txt-sc__item-text'    => 'text-align: {{VALUE}}',
				),
				'condition' => array(
					'item_type' => 'text',
				),
			)
		);

		$repeater->add_responsive_control(
			'content_size',
			array(
				'label'       => __( 'Size', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 'px', 'em', '%' ),
				'label_block' => true,
				'range'       => array(
					'px' => array(
						'min' => 0,
						'max' => 1000,
					),
				),
				'default'     => array(
					'size' => 50,
					'unit' => 'px',
				),
				'selectors'   => array(
					'{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__main-item i'  => 'font-size: {{SIZE}}{{UNIT}}; line-height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__main-item svg' => 'width: {{SIZE}}{{UNIT}} !important; height: {{SIZE}}{{UNIT}} !important',
				),
				'condition'   => array(
					'item_type' => array( 'icon', 'svg' ),
				),
			)
		);

		$repeater->add_control(
			'item_icon_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__main-item i' => 'color: {{VALUE}};',
					'{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__main-item svg, {{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__main-item svg *' => 'fill: {{VALUE}};',
				),
				'condition' => array(
					'item_type' => array( 'icon', 'svg' ),
				),
			)
		);

		$repeater->add_control(
			'item_text_color',
			array(
				'label'     => __( 'Text Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__item-text.pa-txt-sc__main-item'  => 'color: {{VALUE}};',
				),
				'condition' => array(
					'item_type' => 'text',
				),
			)
		);

		$repeater->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'item_text_typo',
				'selector'  => '{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__item-text.pa-txt-sc__main-item',
				'condition' => array(
					'item_type' => 'text',
				),
			)
		);

		$repeater->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'      => 'item_text_shadow',
				'selector'  => '{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__item-text.pa-txt-sc__main-item',
				'condition' => array(
					'item_type' => 'text',
				),
			)
		);

		$repeater->add_responsive_control(
			'item_img_width',
			array(
				'label'      => __( 'Width', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 1,
						'max' => 1000,
					),
				),
				'default'    => array(
					'size' => 100,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__main-item' => 'width: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__item-img.pa-txt-sc__main-item' => 'max-width: {{SIZE}}{{UNIT}}',
				),
				'condition'  => array(
					'item_type' => array( 'image', 'lottie' ),
				),
			)
		);

		$repeater->add_responsive_control(
			'item_img_height',
			array(
				'label'      => __( 'Height', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 1,
						'max' => 1000,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__main-item' => 'height: {{SIZE}}{{UNIT}}',
				),
				'condition'  => array(
					'item_type' => array( 'image', 'lottie' ),
				),
			)
		);

		$repeater->add_responsive_control(
			'opacity',
			array(
				'label'     => __( 'Opacity', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1,
						'step' => 0.1,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__main-item' => 'opacity: {{SIZE}};',
				),
			)
		);

		$repeater->add_control(
			'stroke_sw',
			array(
				'label'     => __( 'Stroke', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => array(
					'clipped_bg!' => 'yes',
					'item_type'   => 'text',
				),
			)
		);

		$repeater->add_control(
			'ts_stroke_text_color',
			array(
				'label'     => __( 'Stroke Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__main-item' => '-webkit-text-stroke-color: {{VALUE}};',
				),
				'condition' => array(
					'clipped_bg!' => 'yes',
					'item_type'   => 'text',
					'stroke_sw'   => 'yes',
				),
			)
		);

		$repeater->add_responsive_control(
			'stroke_width',
			array(
				'label'     => __( 'Stroke Fill Width', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 1,
					'unit' => 'px',
				),
				'selectors' => array(
					'{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__main-item' => '-webkit-text-stroke-width: {{SIZE}}px',
				),
				'condition' => array(
					'clipped_bg!' => 'yes',
					'item_type'   => 'text',
					'stroke_sw'   => 'yes',
				),
			)
		);

		$repeater->add_control(
			'zindex',
			array(
				'label'     => __( 'Z-Index', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::NUMBER,
				'selectors' => array(
					'{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__main-item' => 'z-index: {{VALUE}}',
				),
			)
		);

		$repeater->add_control(
			'hide_on_hov',
			array(
				'label'     => __( 'Hide on Hover', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'separator' => 'before',
			)
		);

		$repeater->end_controls_tab();

		$repeater->start_controls_tab(
			'hover_state',
			array(
				'label' => __( 'Hover Element', 'premium-addons-for-elementor' ),
			)
		);

		$repeater->add_control(
			'item_type_hov',
			array(
				'label'       => __( 'Type', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'render_type' => 'template',
				'options'     => array(
					'none'   => __( 'None', 'premium-addons-for-elementor' ),
					'icon'   => __( 'Icon', 'premium-addons-for-elementor' ),
					'text'   => __( 'Text', 'premium-addons-for-elementor' ),
					'image'  => __( 'Image', 'premium-addons-for-elementor' ),
					'lottie' => __( 'Lottie', 'premium-addons-for-elementor' ),
					'svg'    => apply_filters( 'pa_pro_label', __( 'SVG Code (Pro)', 'premium-addons-for-elementor' ) ),
				),
				'default'     => 'none',
			)
		);

		$repeater->add_control(
			'item_txt_hov',
			array(
				'label'       => __( 'Text', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'label_block' => true,
				'condition'   => array(
					'item_type_hov' => 'text',
				),
			)
		);

		$repeater->add_control(
			'item_txt_tag_hov',
			array(
				'label'       => __( 'HTML Tag', 'premium-addons-for-elementor' ),
				'description' => __( 'Select an HTML tag for the text.', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'span',
				'options'     => array(
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
					'p'    => 'p',
				),
				'label_block' => true,
				'condition'   => array(
					'item_type_hov' => 'text',
				),
			)
		);

		$repeater->add_control(
			'content_image_hov',
			array(
				'label'       => __( 'Choose Image', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::MEDIA,
				'label_block' => true,
				'default'     => array(
					'url' => Utils::get_placeholder_image_src(),
				),
				'dynamic'     => array( 'active' => true ),
				'condition'   => array(
					'item_type_hov' => 'image',
				),
			)
		);

		$repeater->add_group_control(
			Group_Control_Image_Size::get_type(),
			array(
				'name'      => 'thumbnail_hov',
				'default'   => 'full',
				'condition' => array(
					'item_type_hov' => 'image',
				),
			)
		);

		$repeater->add_responsive_control(
			'item_img_fit_hov',
			array(
				'label'     => __( 'Image Fit', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					''        => __( 'Default', 'premium-addons-for-elementor' ),
					'cover'   => __( 'Cover', 'premium-addons-for-elementor' ),
					'fill'    => __( 'Fill', 'premium-addons-for-elementor' ),
					'contain' => __( 'Contain', 'premium-addons-for-elementor' ),
				),
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__hov-item' => 'object-fit:{{VALUE}};',
				),
				'condition' => array(
					'item_type_hov' => 'image',
				),
			)
		);

		$repeater->add_control(
			'icon_hov',
			array(
				'label'       => __( 'Choose Icon', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::ICONS,
				'skin'        => 'inline',
				'default'     => array(
					'value'   => 'fas fa-star',
					'library' => 'fa-solid',
				),
				'label_block' => false,
				'condition'   => array(
					'item_type_hov' => 'icon',
				),
			)
		);

		if ( $papro_activated ) {

			do_action( 'pa_showcase_svg_hover', $repeater );

		}

		$repeater->add_control(
			'draw_svg_hov',
			array(
				'label'       => __( 'Draw Icon', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SWITCHER,
                'description' => __( 'Enable this option to make the icon drawable. See ', 'premium-addons-for-elementor' ) . '<a href="https://www.youtube.com/watch?v=ZLr0bRe0RAY" target="_blank">tutorial</a>',
				'classes'     => $draw_icon ? '' : 'editor-pa-control-disabled',
				'description' => __( 'Use this option to draw your Font Awesome/SVG Icons.', 'premium-addons-for-elementor' ),
				'conditions'  => $svg_draw_conds_hov,
			)
		);

		if ( $draw_icon ) {

			$repeater->add_control(
				'path_width_hov',
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
					'conditions' => array(
						'terms' => array(
							$svg_draw_conds_hov,
						),
					),
					'selectors'  => array(
						'{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__hov-item.premium-drawable-icon *' => 'stroke-width: {{SIZE}};',
					),
				)
			);

			$repeater->add_control(
				'svg_sync_hov',
				array(
					'label'      => __( 'Draw All Paths Together', 'premium-addons-for-elementor' ),
					'type'       => Controls_Manager::SWITCHER,
                    'condition' => array(
						'draw_svg_hov'  => 'yes',
						'item_type_hov' => 'svg',
					),
				)
			);

			$repeater->add_control(
				'frames_hov',
				array(
					'label'       => __( 'Speed', 'premium-addons-for-elementor' ),
					'type'        => Controls_Manager::NUMBER,
					'description' => __( 'Larger value means longer animation duration.', 'premium-addons-for-elementor' ),
					'default'     => 5,
					'min'         => 1,
					'max'         => 100,
					'conditions'  => array(
						'terms' => array(
							array(
								'name'  => 'draw_svg_hov',
								'value' => 'yes',
							),
							$svg_draw_conds_hov,
						),
					),
				)
			);

			$repeater->add_control(
				'stroke_colors_hov',
				array(
					'label'      => __( 'Stroke Color', 'premium-addons-for-elementor' ),
					'type'       => Controls_Manager::COLOR,
					'conditions' => array(
						'terms' => array(
							$svg_draw_conds,
						),
					),
					'selectors'  => array(
						'{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__hov-item.premium-drawable-icon *' => 'stroke: {{VALUE}};',
					),
				)
			);

			$repeater->add_control(
				'svg_color_hov',
				array(
					'label'      => __( 'After Draw Fill Color', 'premium-addons-for-elementor' ),
					'type'       => Controls_Manager::COLOR,
					'global'     => false,
					'conditions' => array(
						'terms' => array(
							array(
								'name'  => 'draw_svg_hov',
								'value' => 'yes',
							),
							$svg_draw_conds_hov,
						),
					),
				)
			);
		} else {
			Helper_Functions::get_draw_svg_notice(
				$repeater,
				'textual',
				$svg_draw_conds_hov,
				'',
				'conditions'
			);
		}

		$repeater->add_control(
			'lottie_url_hov',
			array(
				'label'       => __( 'Animation JSON URL', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'description' => 'Get JSON code URL from <a href="https://lottiefiles.com/" target="_blank">here</a>',
				'label_block' => true,
				'condition'   => array(
					'item_type' => 'lottie',
				),
			)
		);

		$lottie_cond_hov = array(
			'relation' => 'or',
			'terms'    => array(
				array(
					'name'  => 'item_type_hov',
					'value' => 'lottie',
				),
				array(
					'terms' => array(
						array(
							'name'  => 'draw_svg_hov',
							'value' => 'yes',
						),
						$svg_draw_conds_hov,
					),
				),
			),
		);

		$repeater->add_control(
			'lottie_loop_hov',
			array(
				'label'        => __( 'Loop', 'premium-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'true',
				'default'      => 'true',
				'conditions'   => $lottie_cond_hov,
			)
		);

		$repeater->add_control(
			'lottie_reverse_hov',
			array(
				'label'        => __( 'Reverse', 'premium-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'true',
				'conditions'   => $lottie_cond_hov,
			)
		);

		if ( $draw_icon ) {
			$repeater->add_control(
				'start_point_hov',
				array(
					'label'       => __( 'Start Point (%)', 'premium-addons-for-elementor' ),
					'type'        => Controls_Manager::SLIDER,
					'description' => __( 'Set the point that the SVG should start from.', 'premium-addons-for-elementor' ),
					'default'     => array(
						'unit' => '%',
						'size' => 0,
					),
					'conditions'  => array(
						'terms' => array(
							array(
								'name'  => 'draw_svg_hov',
								'value' => 'yes',
							),
							array(
								'name'     => 'lottie_reverse_hov',
								'operator' => '!==',
								'value'    => 'true',
							),
							$svg_draw_conds_hov,
						),
					),
				)
			);

			$repeater->add_control(
				'end_point_hov',
				array(
					'label'       => __( 'End Point (%)', 'premium-addons-for-elementor' ),
					'type'        => Controls_Manager::SLIDER,
					'description' => __( 'Set the point that the SVG should end at.', 'premium-addons-for-elementor' ),
					'default'     => array(
						'unit' => '%',
						'size' => 0,
					),
					'conditions'  => array(
						'terms' => array(
							array(
								'name'  => 'draw_svg_hov',
								'value' => 'yes',
							),
							array(
								'name'  => 'lottie_reverse_hov',
								'value' => 'true',
							),
							$svg_draw_conds_hov,
						),
					),
				)
			);

			$repeater->add_control(
				'svg_yoyo_hov',
				array(
					'label'      => __( 'Yoyo Effect', 'premium-addons-for-elementor' ),
					'type'       => Controls_Manager::SWITCHER,
					'conditions' => array(
						'terms' => array(
							array(
								'name'  => 'draw_svg_hov',
								'value' => 'yes',
							),
							array(
								'name'  => 'lottie_loop_hov',
								'value' => 'true',
							),
							$svg_draw_conds_hov,
						),
					),
				)
			);
		}

		$repeater->add_control(
			'style_hov',
			array(
				'label'     => esc_html__( 'Element Style', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$repeater->add_responsive_control(
			'item_txt_align_hov',
			array(
				'label'     => __( 'Text Alignment', 'premium-addons-for-elementor' ),
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
					'{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__item-text.pa-txt-sc__hov-item'    => 'justify-content: {{VALUE}}',
				),
				'condition' => array(
					'item_type_hov' => 'text',
				),
			)
		);

		$repeater->add_responsive_control(
			'content_size_hov',
			array(
				'label'       => __( 'Size', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 'px', 'em', '%' ),
				'label_block' => true,
				'range'       => array(
					'px' => array(
						'min' => 0,
						'max' => 1000,
					),
				),
				'default'     => array(
					'size' => 50,
					'unit' => 'px',
				),
				'selectors'   => array(
					'{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__hov-item i'  => 'font-size: {{SIZE}}{{UNIT}}; line-height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__hov-item svg' => 'width: {{SIZE}}{{UNIT}} !important; height: {{SIZE}}{{UNIT}} !important',
				),
				'condition'   => array(
					'item_type_hov' => array( 'icon', 'svg' ),
				),
			)
		);

		$repeater->add_control(
			'item_icon_color_hov',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__hov-item i' => 'color: {{VALUE}};',
					'{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__hov-item svg, {{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__hov-item svg *' => 'fill: {{VALUE}};',
				),
				'condition' => array(
					'item_type_hov' => array( 'icon', 'svg' ),
				),
			)
		);

		$repeater->add_control(
			'item_text_color_hov',
			array(
				'label'     => __( 'Text Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__item-text.pa-txt-sc__hov-item'  => 'color: {{VALUE}};',
				),
				'condition' => array(
					'item_type_hov' => 'text',
				),
			)
		);

		$repeater->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'item_text_typo_hov',
				'selector'  => '{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__item-text.pa-txt-sc__hov-item',
				'condition' => array(
					'item_type_hov' => 'text',
				),
			)
		);

		$repeater->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'      => 'item_text_shadow_hov',
				'selector'  => '{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__item-text.pa-txt-sc__hov-item',
				'condition' => array(
					'item_type_hov' => 'text',
				),
			)
		);

		$repeater->add_responsive_control(
			'item_img_width_hov',
			array(
				'label'      => __( 'Width', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 1,
						'max' => 1000,
					),
				),
				'default'    => array(
					'size' => 100,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__hov-item' => 'width: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__item-img.pa-txt-sc__hov-item' => 'max-width: {{SIZE}}{{UNIT}}',
				),
				'condition'  => array(
					'item_type_hov' => array( 'image', 'lottie' ),
				),
			)
		);

		$repeater->add_responsive_control(
			'item_img_height_hov',
			array(
				'label'      => __( 'Height', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 1,
						'max' => 1000,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__hov-item' => 'height: {{SIZE}}{{UNIT}}',
				),
				'condition'  => array(
					'item_type_hov' => array( 'image', 'lottie' ),
				),
			)
		);

		$repeater->add_responsive_control(
			'opacity_hov',
			array(
				'label'     => __( 'Opacity', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1,
						'step' => 0.1,
					),
				),
				'default'   => array(
					'size' => 1,
					'unit' => 'px',
				),
				'selectors' => array(
					'{{WRAPPER}} {{CURRENT_ITEM}}:hover .pa-txt-sc__hov-item' => 'opacity: {{SIZE}};',
				),
			)
		);

		$repeater->add_control(
			'stroke_sw_hov',
			array(
				'label'     => __( 'Stroke', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => array(
					'clipped_bg_hov!' => 'yes',
					'item_type_hov'   => 'text',
				),
			)
		);

		$repeater->add_control(
			'ts_stroke_text_color_hov',
			array(
				'label'     => __( 'Stroke Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__hov-item' => '-webkit-text-stroke-color: {{VALUE}};',
				),
				'condition' => array(
					'clipped_bg_hov!' => 'yes',
					'item_type_hov'   => 'text',
					'stroke_sw_hov'   => 'yes',
				),
			)
		);

		$repeater->add_responsive_control(
			'stroke_width_hov',
			array(
				'label'     => __( 'Stroke Fill Width', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => array(
					'size' => 1,
					'unit' => 'px',
				),
				'selectors' => array(
					'{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__hov-item' => '-webkit-text-stroke-width: {{SIZE}}px',
				),
				'condition' => array(
					'clipped_bg_hov!' => 'yes',
					'item_type_hov'   => 'text',
					'stroke_sw_hov'   => 'yes',
				),
			)
		);

		$repeater->add_control(
			'zindex_hov',
			array(
				'label'     => __( 'Z-Index', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::NUMBER,
				'selectors' => array(
					'{{WRAPPER}} {{CURRENT_ITEM}}' => 'z-index: {{VALUE}}',
				),
			)
		);

		$repeater->add_control(
			'transition',
			array(
				'label'      => __( 'Transition (sec)', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 's' ),
				'default'    => array(
					'unit' => 's',
					'size' => 0,
				),
				'range'      => array(
					's' => array(
						'min'  => 0,
						'max'  => 50,
						'step' => 0.1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__hov-item, {{WRAPPER}} {{CURRENT_ITEM}} .pa-txt-sc__main-item' => 'transition-duration: {{SIZE}}s',
				),
			)
		);

		$repeater->end_controls_tab();

		$repeater->end_controls_tabs();

		$repeater->add_control(
			'additional_heading',
			array(
				'label'     => esc_html__( 'Additional Options', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$repeater->add_control(
			'link_switcher',
			array(
				'label' => __( 'Link', 'premium-addons-for-elementor' ),
				'type'  => Controls_Manager::SWITCHER,
			)
		);

		$repeater->add_control(
			'link_type',
			array(
				'label'       => __( 'Link Type', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'url'  => __( 'URL', 'premium-addons-for-elementor' ),
					'link' => __( 'Existing Page', 'premium-addons-for-elementor' ),
				),
				'default'     => 'url',
				'label_block' => true,
				'condition'   => array(
					'link_switcher' => 'yes',
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
				'condition'   => array(
					'link_type'     => 'url',
					'link_switcher' => 'yes',
				),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'link_existing',
			array(
				'label'       => __( 'Existing Page', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT2,
				'options'     => $this->getTemplateInstance()->get_all_posts(),
				'multiple'    => false,
				'label_block' => true,
				'condition'   => array(
					'link_type'     => 'link',
					'link_switcher' => 'yes',
				),
			)
		);

		$repeater->add_control(
			'effect',
			array(
				'label'       => __( 'Effects', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'render_type' => 'template',
				'separator'   => 'before',
				'options'     => array(
					'none'                => __( 'None', 'premium-addons-for-elementor' ),
					'hvr-pulse-grow'      => __( 'Pulse', 'premium-addons-for-elementor' ),
					'rotate'              => __( 'Rotate', 'premium-addons-for-elementor' ),
					'hvr-buzz'            => apply_filters( 'pa_pro_label', __( 'Buzz (Pro)', 'premium-addons-for-elementor' ) ),
					'grow'                => apply_filters( 'pa_pro_label', __( 'Grow (Pro)', 'premium-addons-for-elementor' ) ),
					'd-rotate'            => apply_filters( 'pa_pro_label', __( '3D Rotate (Pro)', 'premium-addons-for-elementor' ) ),
					'hvr-float-shadow'    => apply_filters( 'pa_pro_label', __( 'Drop Shadow (Pro)', 'premium-addons-for-elementor' ) ),
					'hvr-wobble-vertical' => apply_filters( 'pa_pro_label', __( 'Wobble Vertical (Pro)', 'premium-addons-for-elementor' ) ),
				),
				'default'     => 'none',
				'condition'   => array(
					'item_type!' => 'text',
				),
			)
		);

		$repeater->add_control(
			'txt_effect',
			array(
				'label'       => __( 'Highlight Effects', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'render_type' => 'template',
				'separator'   => 'before',
				'options'     => array(
					'none'             => __( 'None', 'premium-addons-for-elementor' ),
					'strikethrough'    => __( 'Strikethrough', 'premium-addons-for-elementor' ),
					'underline'        => __( 'Underline', 'premium-addons-for-elementor' ),
					'min-mask'         => apply_filters( 'pa_pro_label', __( 'Minimal Mask (Pro)', 'premium-addons-for-elementor' ) ),
					'circle'           => apply_filters( 'pa_pro_label', __( 'Circle (Pro)', 'premium-addons-for-elementor' ) ),
					'curly'            => apply_filters( 'pa_pro_label', __( 'Curly (Pro)', 'premium-addons-for-elementor' ) ),
					'h-underline'      => apply_filters( 'pa_pro_label', __( 'Hand-drawn Underline (Pro)', 'premium-addons-for-elementor' ) ),
					'outline'          => apply_filters( 'pa_pro_label', __( 'Outline (Pro)', 'premium-addons-for-elementor' ) ),
					'double-underline' => apply_filters( 'pa_pro_label', __( 'Double Underline (Pro)', 'premium-addons-for-elementor' ) ),
					'underline-zigzag' => apply_filters( 'pa_pro_label', __( 'Underline Zigzag (Pro)', 'premium-addons-for-elementor' ) ),
					'diagonal'         => apply_filters( 'pa_pro_label', __( 'Diagonal (Pro)', 'premium-addons-for-elementor' ) ),
					'x'                => apply_filters( 'pa_pro_label', 'X (Pro)' ),
				),
				'default'     => 'none',
				'condition'   => array(
					'item_type' => 'text',
				),
			)
		);

		$repeater->add_control(
			'min_mask_notice',
			array(
				'raw'             => __( 'Please note that Minimal Mask Effect works only on Text Elements ', 'premium-addons-for-elementor' ),
				'type'            => Controls_Manager::RAW_HTML,
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
				'condition'       => array(
					'enable_background_overlay' => 'yes',
				),
			)
		);

		$repeater->add_control(
			'effect_color',
			array(
				'label'       => __( 'Color', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::COLOR,
				'render_type' => 'template',
				'selectors'   => array(
					'{{WRAPPER}} {{CURRENT_ITEM}} svg.outline-svg.outline' => 'stroke: {{VALUE}};',
					'{{WRAPPER}} {{CURRENT_ITEM}} .premium-mask-span::after, {{WRAPPER}} {{CURRENT_ITEM}}.underline::after'   => 'background-color: {{VALUE}};',
				),
				'condition'   => array(
					'item_type' => 'text',
				),
			)
		);

		$repeater->add_control(
			'mask_dir',
			array(
				'label'       => __( 'Direction', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'tr',
				'render_type' => 'template',
				'options'     => array(
					'tr' => __( 'To Right', 'premium-addons-for-elementor' ),
					'tl' => __( 'To Left', 'premium-addons-for-elementor' ),
					'tt' => __( 'To Top', 'premium-addons-for-elementor' ),
					'tb' => __( 'To Bottom', 'premium-addons-for-elementor' ),
				),
				'condition'   => array(
					'txt_effect' => 'min-mask',
					'item_type'  => 'text',
				),
			)
		);

		$repeater->add_responsive_control(
			'line_stroke_width',
			array(
				'label'       => __( 'Line Thickness', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 'px' ),
				'label_block' => true,
				'selectors'   => array(
					'{{WRAPPER}} {{CURRENT_ITEM}} svg.outline-svg'   => 'stroke-width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} {{CURRENT_ITEM}}.pa-txt-sc__effect-underline::after'   => 'height: {{SIZE}}{{UNIT}};',
				),
				'condition'   => array(
					'item_type' => 'text',
				),
			)
		);

		$repeater->add_responsive_control(
			'anim_speed',
			array(
				'label'       => __( 'Animation Speed', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 's' ),
				'render_type' => 'template',
				'range'       => array(
					'px' => array(
						'min'  => 0,
						'max'  => 50,
						'step' => 0.1,
					),
				),
				'label_block' => true,
				'selectors'   => array(
					'{{WRAPPER}} {{CURRENT_ITEM}} svg.outline-svg path, {{WRAPPER}} {{CURRENT_ITEM}}.pa-txt-sc__effect-hvr-pulse-grow.hvr-pulse-grow,
					{{WRAPPER}} {{CURRENT_ITEM}}.pa-txt-sc__effect-hvr-buzz.hvr-buzz'   => 'animation-duration: {{SIZE}}s;',
					'{{WRAPPER}} {{CURRENT_ITEM}}.pa-txt-sc__effect-underline::after,
					 {{WRAPPER}} {{CURRENT_ITEM}}.pa-txt-sc__effect-grow, {{WRAPPER}} {{CURRENT_ITEM}}.pa-txt-sc__effect-rotate,
					 {{WRAPPER}} {{CURRENT_ITEM}}.pa-txt-sc__effect-d-rotate, {{WRAPPER}} {{CURRENT_ITEM}}.pa-txt-sc__effect-hvr-wobble-vertical.hvr-wobble-vertical,
					 {{WRAPPER}} {{CURRENT_ITEM}}.pa-txt-sc__effect-hvr-float-shadow, {{WRAPPER}} {{CURRENT_ITEM}}.pa-txt-sc__effect-hvr-float-shadow::after'   => 'transition: {{SIZE}}s;',
				),
				'conditions'  => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'terms' => array(
								array(
									'name'  => 'item_type',
									'value' => 'text',
								),
								array(
									'name'     => 'txt_effect',
									'operator' => '!in',
									'value'    => array( 'none', 'min-mask' ),
								),
							),
						),
						array(
							'terms' => array(
								array(
									'name'     => 'item_type',
									'operator' => '!==',
									'value'    => 'text',
								),
								array(
									'name'     => 'effect',
									'operator' => '!==',
									'value'    => 'none',
								),
							),
						),
					),
				),
			)
		);

		$repeater->add_control(
			'con_style_heading',
			array(
				'label'     => esc_html__( 'Item Container Style', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$repeater->add_responsive_control(
			'item_width',
			array(
				'label'       => __( 'Item Width', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 'px', 'vw', 'custom' ),
				'range'       => array(
					'px' => array(
						'min' => 0,
						'max' => 1000,
					),
				),
				'label_block' => true,
				'selectors'   => array(
					'{{WRAPPER}} {{CURRENT_ITEM}}' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$repeater->add_responsive_control(
			'rotate',
			array(
				'label'      => __( 'Rotate (deg)', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'deg' ),
				'default'    => array(
					'unit' => 'deg',
					'size' => 0,
				),
				'range'      => array(
					'deg' => array(
						'min' => -180,
						'max' => 180,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} {{CURRENT_ITEM}}' => 'transform: rotate({{SIZE}}deg)',
				),
				'condition'  => array(
					'effect!' => array( 'rotate', 'd-rotate', 'hvr-float-shadow' ),
				),
			)
		);

		$repeater->add_control(
			'clipped_bg',
			array(
				'label'      => apply_filters( 'pa_pro_label', __( 'Clipped Background (Pro)', 'premium-addons-for-elementor' ) ),
				'type'       => Controls_Manager::SWITCHER,
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'  => 'item_type',
							'value' => 'text',
						),
						array(
							'name'  => 'item_type_hov',
							'value' => 'text',
						),
					),
				),
			)
		);

		$repeater->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'item_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} {{CURRENT_ITEM}}.pa-txt-sc__item-container:not(.pa-clipped-bg), {{WRAPPER}} {{CURRENT_ITEM}}.pa-clipped-bg span',
			)
		);

		$repeater->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'item_border',
				'selector' => '{{WRAPPER}} {{CURRENT_ITEM}}.pa-txt-sc__item-container',
			)
		);

		$repeater->add_control(
			'item_border_rad',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} {{CURRENT_ITEM}}.pa-txt-sc__item-container' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$repeater->add_responsive_control(
			'item_margin',
			array(
				'label'      => __( 'Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} {{CURRENT_ITEM}}.pa-txt-sc__item-container' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$repeater->add_responsive_control(
			'item_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} {{CURRENT_ITEM}}.pa-txt-sc__item-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'content',
			array(
				'label'         => __( 'Items', 'premium-addons-for-elementor' ),
				'type'          => Controls_Manager::REPEATER,
				'default'       => array(
					array(
						'item_type' => 'text',
						'item_txt'  => 'Premium',
					),
					array(
						'item_type'     => 'image',
						'content_image' => Utils::get_placeholder_image_src(),
					),
					array(
						'item_type' => 'text',
						'item_txt'  => 'Addons',
					),
					array(
						'item_type'     => 'image',
						'content_image' => Utils::get_placeholder_image_src(),
					),
				),
				'fields'        => $repeater->get_controls(),
				'title_field'   => '<# if ( "icon" === item_type ) { #> {{{ elementor.helpers.renderIcon( this, icon, {}, "i", "panel" ) }}}<#} else if( "text" === item_type ) { #> {{item_txt}} <# } else if( "image" === item_type) {#> <img class="editor-pa-img" src="{{content_image.url}}"> <# } else if ("svg" === item_type) { #> {{ "SVG Code" }} <# } else { #> {{ "Lottie" }} <# }#>',
				'prevent_empty' => false,
			)
		);

		$this->add_responsive_control(
			'cont_col_gap',
			array(
				'label'      => __( 'Horizontal Spacing', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'separator'  => 'before',
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-txt-sc__outer-container' => 'column-gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'cont_row_gap',
			array(
				'label'      => __( 'Vertical Spacing', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-txt-sc__outer-container' => 'row-gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'content_align',
			array(
				'label'     => __( 'Alignment', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => __( 'Start', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-order-start',
					),
					'center'     => array(
						'title' => __( 'Center', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-h-align-center',
					),
					'flex-end'   => array(
						'title' => __( 'End', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-order-end',
					),
				),
				'default'   => 'center',
				'selectors' => array(
					'{{WRAPPER}} .pa-txt-sc__outer-container' => 'justify-content: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'trigger',
			array(
				'label'       => __( 'Trigger Animation on', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'render_type' => 'template',
				'separator'   => 'before',
				'description' => __( 'Note that the following animations are always triggered on viewport: <b>Grow, Minimal Mask</b>', 'premium-addons-for-elementor' ),
				'options'     => array(
					'viewport' => __( 'Viewport', 'premium-addons-for-elementor' ),
					'hover'    => apply_filters( 'pa_pro_label', __( 'Hover (Pro)', 'premium-addons-for-elementor' ) ),
				),
				'default'     => 'viewport',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Add content style.
	 *
	 * @access private
	 */
	private function add_content_style_controls() {

		$this->start_controls_section(
			'content_style_sec',
			array(
				'label' => __( 'Item Content', 'premium-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'txt_heading',
			array(
				'label' => __( 'Text', 'premium-addons-for-elementor' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		/**Text Style */
		$this->add_control(
			'text_color',
			array(
				'label'     => __( 'Text Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-txt-sc__item-text' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'text_typo',
				'selector' => '{{WRAPPER}} .pa-txt-sc__item-text',
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'text_shadow',
				'selector' => '{{WRAPPER}} .pa-txt-sc__item-text',
			)
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			array(
				'name'      => 'css_filters',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .pa-txt-sc__item-container:not(.has-text)',
			)
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			array(
				'name'     => 'hover_css_filters',
				'label'    => __( 'Hover CSS Filters', 'premium-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .pa-txt-sc__item-container:not(.has-text):hover',
			)
		);

		$this->add_control(
			'transition',
			array(
				'label'      => __( 'Transition (sec)', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 's' ),
				'range'      => array(
					's' => array(
						'min'  => 0,
						'max'  => 3,
						'step' => 0.1,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .pa-txt-sc__item-container:not(.has-text)' => 'transition-duration: {{SIZE}}s',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Add item container style controls.
	 *
	 * @access private
	 */
	private function add_item_container_style_controls() {

		$this->start_controls_section(
			'cont_style_sec',
			array(
				'label' => __( 'Item Container', 'premium-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'cont_shadow',
				'selector' => '{{WRAPPER}} .pa-txt-sc__item-container',
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'cont_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .pa-txt-sc__item-container',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'cont_border',
				'selector' => '{{WRAPPER}} .pa-txt-sc__item-container',
			)
		);

		$this->add_control(
			'cont_border_rad',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-txt-sc__item-container' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'cont_margin',
			array(
				'label'      => __( 'Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-txt-sc__item-container' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'cont_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-txt-sc__item-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render title widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function render() {

		$widget_id = $this->get_id();

		$settings = $this->get_settings_for_display();

		$papro_activated = apply_filters( 'papro_activated', false );

		if ( ! $papro_activated || version_compare( PREMIUM_PRO_ADDONS_VERSION, '2.9.10', '<' ) ) {

			if ( 'hover' === $settings['trigger'] ) {

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

		$content = $settings['content'];

		$this->add_render_attribute( 'container', 'class', 'pa-txt-sc__outer-container pa-trigger-on-' . $settings['trigger'] );

		?>
		<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'container' ) ); ?>>
			<?php
			foreach ( $content as $index => $item ) {

				if ( ! $papro_activated || version_compare( PREMIUM_PRO_ADDONS_VERSION, '2.9.10', '<' ) ) {

					if ( 'svg' === $item['item_type'] ||
						( 'text' === $item['item_type'] && 'yes' === $item['clipped_bg'] ) ||
						( 'text' === $item['item_type'] && ! in_array( $item['txt_effect'], array( 'none', 'strikethrough', 'underline' ), true ) ) ||
						( 'text' !== $item['item_type'] && ! in_array( $item['effect'], array( 'none', 'hvr-pulse-grow', 'rotate' ), true ) )
					) {

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

				$has_hover_elem   = 'none' !== $item['item_type_hov'];
				$has_link         = 'yes' === $item['link_switcher'];
				$hide_on_hov      = 'yes' === $item['hide_on_hov'] ? 'pa-txt-sc__hov-hide' : '';
				$has_hov_svg_draw = in_array( $item['item_type_hov'], array( 'icon', 'svg' ), true ) && 'yes' === $item['draw_svg_hov'];

				$effect         = 'text' === $item['item_type'] ? $item['txt_effect'] : $item['effect'];
				$has_txt        = ( 'text' === $item['item_type'] || 'text' === $item['item_type_hov'] ) ? 'has-text' : '';
				$clipped_bg     = ( 'text' === $item['item_type'] || 'text' === $item['item_type_hov'] ) && 'yes' === $item['clipped_bg'];
				$has_clipped_bg = $clipped_bg ? 'pa-clipped-bg' : '';

				if ( $has_link ) {

					$link_type = $item['link_type'];
					$link_url  = 'url' === $link_type ? $item['link'] : get_permalink( $item['link_existing'] );

					$this->add_render_attribute( 'link' . $item['_id'], 'class', 'pa-txt-sc__link' );

					if ( 'url' === $link_type ) {
						$this->add_link_attributes( 'link' . $item['_id'], $link_url );
					} else {
						$this->add_render_attribute( 'link' . $item['_id'], 'href', $link_url );
					}
				}

				$this->add_render_attribute(
					'item-container' . $item['_id'],
					'class',
					array(
						'pa-txt-sc__item-container',
						'elementor-repeater-item-' . $item['_id'],
						'pa-txt-sc__effect-' . $effect,
						$hide_on_hov,
						$has_clipped_bg,
						$has_txt,
					)
				);

				?>
					<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'item-container' . $item['_id'] ) ); ?>>
						<?php

							$this->render_item_elements( $index, $item );

						if ( $has_hover_elem ) {
							$this->render_item_elements( $index, $item, '_hov' );
						}

						if ( $has_link ) {
							?>
									<a <?php echo wp_kses_post( $this->get_render_attribute_string( 'link' . $item['_id'] ) ); ?>></a>
								<?php
						}
						?>
					</div>
				<?php
			}
			?>
		</div>
		<?php
	}

	/**
	 * Render Item Element.
	 *
	 * @access private
	 * @since
	 *
	 * @param string $index  repeater item index.
	 * @param array  $item  repeater item settings.
	 * @param string $elem_type  normal or hover element.
	 */
	private function render_item_elements( $index, $item, $elem_type = '' ) {

		$settings   = $this->get_settings_for_display();
		$type       = $item[ 'item_type' . $elem_type ];
		$draw_svg   = false;
		$item_cls   = empty( $elem_type ) ? 'pa-txt-sc__main-item' : 'pa-txt-sc__hov-item';
		$item_style = empty( $elem_type ) ? '' : 'visibility:hidden;';
		$draw_icon  = $this->check_icon_draw();

		$this->add_render_attribute(
			'item-content-' . $item['_id'] . $elem_type,
			array(
				'class' => $item_cls,
				'style' => $item_style,
			)
		);

		if ( in_array( $type, array( 'icon', 'svg' ), true ) ) {

			$draw_svg = $draw_icon && 'yes' === $item[ 'draw_svg' . $elem_type ];

			$this->add_render_attribute( 'item-content-' . $item['_id'] . $elem_type, 'class', 'premium-drawable-icon pa-txt-sc__item-' . $type );

			if ( 'icon' === $type ) {
				$icon = $item[ 'icon' . $elem_type ];

				if ( ! empty( $icon ) ) {
					$this->add_render_attribute(
						'item-content-icon' . $item['_id'] . $elem_type,
						array(
							'class'       => $icon['value'],
							'aria-hidden' => 'true',
						)
					);
				}
			}

			if ( $draw_svg ) {
				$hov_drawer_cls = ! empty( $elem_type ) ? ' premium-drawer-hover' : '';
				$this->add_render_attribute(
					'item-content-' . $item['_id'] . $elem_type,
					array(
						'class'            => 'premium-svg-drawer',
						'data-svg-reverse' => $item[ 'lottie_reverse' . $elem_type ],
						'data-svg-loop'    => $item[ 'lottie_loop' . $elem_type ],
						'data-svg-sync'    => $item[ 'svg_sync' . $elem_type ],
						'data-svg-hover'   => ! empty( $elem_type ), // always play the hover element on hover.
						'data-svg-fill'    => $item[ 'svg_color' . $elem_type ],
						'data-svg-frames'  => $item[ 'frames' . $elem_type ],
						'data-svg-yoyo'    => $item[ 'svg_yoyo' . $elem_type ],
						'data-svg-point'   => $item[ 'lottie_reverse' . $elem_type ] ? $item[ 'end_point' . $elem_type ]['size'] : $item[ 'start_point' . $elem_type ]['size'],
					)
				);

			} else {
				$this->add_render_attribute( 'item-content-' . $item['_id'] . $elem_type, 'class', 'premium-svg-nodraw' );
			}
		}

		switch ( $type ) {

			case 'icon':
				$this->render_item_icon( $item, $draw_svg, $elem_type );
				break;

			case 'text':
				$this->render_item_txt( $item, $elem_type );
				break;

			case 'image':
				$this->render_item_image( $item, $settings, $elem_type );
				break;

			case 'lottie':
				$this->render_item_lottie( $item, $elem_type );
				break;

			default:
				$this->render_item_svg( $item, $index, $elem_type );
				break;
		}
	}

	/**
	 * Render Item Icon.
	 *
	 * @access private
	 * @since
	 *
	 * @param array   $item  repeater item settings.
	 * @param boolean $svg_draw  true if svg draw is enabled.
	 * @param string  $elem_type  element type.
	 */
	private function render_item_icon( $item, $svg_draw, $elem_type ) {
		$item_hov_class = empty( $elem_type ) ? 'pa-txt-sc__main-item' : 'pa-txt-sc__hov-item';
		$item_hov_style = empty( $elem_type ) ? '' : 'visibility:hidden;';

		if ( $svg_draw ) {
			?>
			<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'item-content-' . $item['_id'] . $elem_type ) ); ?>>
				<i <?php echo wp_kses_post( $this->get_render_attribute_string( 'item-content-icon' . $item['_id'] . $elem_type ) ); ?>></i>
			</div>
			<?php
		} else {
			?>
				<div class="premium-svg-nodraw premium-drawable-icon pa-txt-sc__item-icon <?php echo esc_attr( $item_hov_class ); ?>">
				<?php
					Icons_Manager::render_icon(
						$item[ 'icon' . $elem_type ],
						array(
							'aria-hidden' => 'true',
						)
					);
				?>
			</div>
			<?php
		}
	}

	/**
	 * Render Item SVG.
	 *
	 * @access private
	 * @since
	 *
	 * @param array  $item  repeater item settings.
	 * @param string $index  item index.
	 * @param string $elem_type  element type.
	 */
	private function render_item_svg( $item, $index, $elem_type ) {
		?>
		<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'item-content-' . $item['_id'] . $elem_type ) ); ?>>
			<?php $this->print_unescaped_setting( 'custom_svg' . $elem_type, 'content', $index ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
		<?php
	}

	/**
	 * Render Item Text.
	 *
	 * @access private
	 * @since
	 *
	 * @param array  $item  repeater item settings.
	 * @param string $elem_type  element type.
	 */
	private function render_item_txt( $item, $elem_type ) {

		$effect = $item['txt_effect'];

		$txt_tag = Helper_Functions::validate_html_tag( $item[ 'item_txt_tag' . $elem_type ] );

		$min_mask_cls = empty( $elem_type ) && 'min-mask' === $effect ? 'premium-mask-' . $item['mask_dir'] : '';

		if ( empty( $elem_type ) && ! in_array( $effect, array( 'none', 'min-mask', 'underline' ), true ) ) {
			echo $this->get_effect_svg( $effect );
		}

		$this->add_render_attribute( 'item-content-' . $item['_id'] . $elem_type, 'class', 'pa-txt-sc__item-text ' . $min_mask_cls );

		?>
			<<?php echo wp_kses_post( $txt_tag . ' ' . $this->get_render_attribute_string( 'item-content-' . $item['_id'] . $elem_type ) ); ?>> <?php echo esc_html( $item[ 'item_txt' . $elem_type ] ); ?></<?php echo wp_kses_post( $txt_tag ); ?>>
		<?php
	}

	private function get_effect_svg( $effect ) {

		$effects_svg = apply_filters(
			'pa_showcase_highlights',
			array(
				'strikethrough' => '<svg class="outline-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 150" preserveAspectRatio="none"><path d="M3,75h493.5"></path></svg>',
			)
		);

		return $effects_svg[ $effect ];
	}
	/**
	 * Render Item Image.
	 *
	 * @access private
	 * @since
	 *
	 * @param array  $item  repeater item settings.
	 * @param array  $settings  widget settings.
	 * @param string $elem_type  element type.
	 */
	private function render_item_image( $item, $settings, $elem_type ) {

		$image_src  = $item[ 'content_image' . $elem_type ]['url'];
		$image_id   = attachment_url_to_postid( $image_src );
		$item_cls   = empty( $elem_type ) ? ' pa-txt-sc__main-item' : ' pa-txt-sc__hov-item';
		$item_style = empty( $elem_type ) ? '' : 'visibility:hidden;';

		if ( $image_id && ! empty( $image_src ) ) {
			$image_html = wp_get_attachment_image(
				$image_id,
				$item[ 'thumbnail' . $elem_type . '_size' ],
				'',
				array(
					'class'      => 'pa-txt-sc__item-img' . $item_cls,
					'visibility' => 'hidden',
				)
			);
		} else {
			$image_html = '<img src="' . $image_src . '" class="pa-txt-sc__item-img' . $item_cls . '">'; // render elementor's placeholders.
		}

		echo wp_kses_post( $image_html );
	}

	/**
	 * Render Item Lottie.
	 *
	 * @access private
	 * @since
	 *
	 * @param array  $item  repeater item settings.
	 * @param string $elem_type  element type.
	 */
	private function render_item_lottie( $item, $elem_type ) {

		$this->add_render_attribute(
			'item-content-' . $item['_id'] . $elem_type,
			array(
				'class'               => array(
					'pa-txt-sc__item-lottie',
					'premium-lottie-animation',
				),
				'data-lottie-url'     => $item[ 'lottie_url' . $elem_type ],
				'data-lottie-loop'    => $item[ 'lottie_loop' . $elem_type ],
				'data-lottie-reverse' => $item[ 'lottie_reverse' . $elem_type ],
			)
		);
		?>
		<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'item-content-' . $item['_id'] . $elem_type ) ); ?>></div>
		<?php
	}
}
