<?php
/**
 * Premium Fancy Text.
 */

namespace PremiumAddons\Widgets;

// Elementor Classes.
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Background;

// PremiumAddons Classes.
use PremiumAddons\Includes\Helper_Functions;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // If this file is called directly, abort.
}

/**
 * Class Premium_Fancytext
 */
class Premium_Fancytext extends Widget_Base {


	/**
	 * Retrieve Widget Name.
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function get_name() {
		return 'premium-addon-fancy-text';
	}

	/**
	 * Retrieve Widget Title.
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function get_title() {
		return __( 'Animated Text', 'premium-addons-for-elementor' );
	}

	/**
	 * Retrieve Widget Icon.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return string widget icon.
	 */
	public function get_icon() {
		return 'pa-fancy-text';
	}

	/**
	 * Retrieve Widget Dependent CSS.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return array CSS style handles.
	 */
	public function get_style_depends() {
		return array(
			'premium-addons',
		);
	}

	/**
	 * Retrieve Widget Dependent JS.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return array JS script handles.
	 */
	public function get_script_depends() {
		return array(
			'pa-typed',
			'pa-vticker',
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
		return array( 'pa', 'premium', 'premium animated text', 'fancy', 'typing', 'headline', 'heading', 'animation' );
	}

	protected function is_dynamic_content(): bool {
		return false;
	}

	/**
	 * Retrieve Widget Categories.
	 *
	 * @since  1.5.1
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return array( 'premium-elements' );
	}

	/**
	 * Widget preview refresh button.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function is_reload_preview_required() {
		return true;
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
	 * Register Testimonials controls.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function register_controls() { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore

		$this->start_controls_section(
			'general_section',
			array(
				'label' => __( 'General', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'style',
			array(
				'label'        => __( 'Text Style', 'premium-addons-for-elementor' ),
				'type'         => Controls_Manager::SELECT,
				'prefix_class' => 'premium-atext__',
				'options'      => array(
					'switch'    => __( 'Switched', 'premium-addons-for-elementor' ),
					'highlight' => __( 'Highlighted', 'premium-addons-for-elementor' ),
				),
				'default'      => 'switch',
				'render_type'  => 'template',
			)
		);

		$this->add_control(
			'premium_fancy_prefix_text',
			array(
				'label'       => __( 'Before Text', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'default'     => __( 'This is', 'premium-addons-for-elementor' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'text',
			array(
				'label'       => __( 'Highlighted Text', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'separator'   => 'before',
				'default'     => __( 'animated', 'premium-addons-for-elementor' ),
				'label_block' => true,
				'condition'   => array(
					'style' => 'highlight',
				),
			)
		);

		$repeater = new REPEATER();

		$repeater->add_control(
			'premium_text_strings_text_field',
			array(
				'label'       => __( 'Fancy String', 'premium-addons-for-elementor' ),
				'dynamic'     => array( 'active' => true ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
			)
		);

		$this->add_control(
			'premium_fancy_text_strings',
			array(
				'label'       => __( 'Animated Text', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::REPEATER,
				'default'     => array(
					array(
						'premium_text_strings_text_field' => __( 'Designer', 'premium-addons-for-elementor' ),
					),
					array(
						'premium_text_strings_text_field' => __( 'Developer', 'premium-addons-for-elementor' ),
					),
					array(
						'premium_text_strings_text_field' => __( 'Awesome', 'premium-addons-for-elementor' ),
					),
				),
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ premium_text_strings_text_field }}}',
				'condition'   => array(
					'style' => 'switch',
				),
			)
		);

		$this->add_control(
			'premium_fancy_suffix_text',
			array(
				'label'       => __( 'After Text', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'default'     => __( 'Text', 'premium-addons-for-elementor' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'link',
			array(
				'label'     => __( 'Link', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::URL,
				'dynamic'   => array(
					'active' => true,
				),
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'display',
			array(
				'label'       => __( 'Display', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'inline' => __( 'Inline', 'premium-addons-for-elementor' ),
					'block'  => __( 'Block', 'premium-addons-for-elementor' ),
				),
				'default'     => 'inline',
				'selectors'   => array(
					'{{WRAPPER}} .premium-prefix-text, {{WRAPPER}} .premium-suffix-text' => 'display: {{VALUE}}',
				),
				'label_block' => true,
			)
		);

		$this->add_responsive_control(
			'premium_fancy_text_align',
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
				'default'   => 'center',
				'toggle'    => false,
				'selectors' => array(
					'{{WRAPPER}} .premium-atext__headline' => 'text-align: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'text_tag',
			array(
				'label'       => __( 'HTML Tag', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'h3',
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
				'separator'   => 'after',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'switched_settings',
			array(
				'label'     => __( 'Switched Settings', 'premium-addons-for-elementor' ),
				'condition' => array(
					'style' => 'switch',
				),
			)
		);

		$this->add_control(
			'trigger',
			array(
				'label'   => __( 'Trigger On', 'premium-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'page_load' => __( 'Page Load', 'premium-addons-for-elementor' ),
					'viewport'  => __( 'Visible on Viewport', 'premium-addons-for-elementor' ),
				),
				'default' => 'page_load',
			)
		);

		$this->add_control(
			'premium_fancy_text_effect',
			array(
				'label'       => __( 'Effect', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'typing'  => __( 'Typing', 'premium-addons-for-elementor' ),
					'clip'    => apply_filters( 'pa_pro_label', __( 'Clip (Pro)', 'premium-addons-for-elementor' ) ),
					'slide'   => __( 'Slide Up', 'premium-addons-for-elementor' ),
					'zoomout' => __( 'Zoom Out', 'premium-addons-for-elementor' ),
					'rotate'  => __( 'Rotate', 'premium-addons-for-elementor' ),
					'custom'  => __( 'Custom', 'premium-addons-for-elementor' ),
				),
				'default'     => 'typing',
				'render_type' => 'template',
				'label_block' => true,
			)
		);

		$this->add_control(
			'custom_animation',
			array(
				'label'       => __( 'Animations', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::ANIMATION,
				'render_type' => 'template',
				'default'     => 'fadeIn',
				'condition'   => array(
					'premium_fancy_text_effect' => 'custom',
				),
			)
		);

		$this->add_control(
			'premium_fancy_text_type_speed',
			array(
				'label'       => __( 'Type Speed', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 30,
				'description' => __( 'Set typing effect speed in milliseconds.', 'premium-addons-for-elementor' ),
				'condition'   => array(
					'premium_fancy_text_effect' => 'typing',
				),
			)
		);

		$this->add_control(
			'premium_fancy_text_zoom_speed',
			array(
				'label'       => __( 'Animation Speed', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::NUMBER,
				'render_type' => 'template',
				'description' => __( 'Set animation speed in milliseconds. Default value is 1000', 'premium-addons-for-elementor' ),
				'condition'   => array(
					'premium_fancy_text_effect!' => array( 'typing', 'slide' ),
				),
				'selectors'   => array(
					'{{WRAPPER}} .premium-atext__wrapper:not(.premium-atext__typing):not(.premium-atext__slide) .premium-fancy-list-items'   => 'animation-duration: {{VALUE}}ms',
				),
			)
		);

		$this->add_control(
			'premium_fancy_text_zoom_delay',
			array(
				'label'       => __( 'Animation Delay', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::NUMBER,
				'description' => __( 'Set animation delay in milliseconds. Default value is 2500', 'premium-addons-for-elementor' ),
				'condition'   => array(
					'premium_fancy_text_effect!' => array( 'typing', 'slide' ),
				),
			)
		);

		$this->add_control(
			'loop_count',
			array(
				'label'     => __( 'Loop Count', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::NUMBER,
				'condition' => array(
					'premium_fancy_text_effect!' => array( 'typing', 'slide' ),
				),
			)
		);

		$this->add_control(
			'premium_fancy_text_back_speed',
			array(
				'label'       => __( 'Back Speed', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 30,
				'description' => __( 'Set a speed for backspace effect in milliseconds.', 'premium-addons-for-elementor' ),
				'condition'   => array(
					'premium_fancy_text_effect' => 'typing',
				),
			)
		);

		$this->add_control(
			'premium_fancy_text_start_delay',
			array(
				'label'       => __( 'Start Delay', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 30,
				'description' => __( 'If you set it on 5000 milliseconds, the first word/string will appear after 5 seconds.', 'premium-addons-for-elementor' ),
				'condition'   => array(
					'premium_fancy_text_effect' => 'typing',
				),
			)
		);

		$this->add_control(
			'premium_fancy_text_back_delay',
			array(
				'label'       => __( 'Back Delay', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 30,
				'description' => __( 'If you set it on 5000 milliseconds, the word/string will remain visible for 5 seconds before backspace effect.', 'premium-addons-for-elementor' ),
				'condition'   => array(
					'premium_fancy_text_effect' => 'typing',
				),
			)
		);

		$this->add_control(
			'premium_fancy_text_type_loop',
			array(
				'label'     => __( 'Loop', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => array(
					'premium_fancy_text_effect' => 'typing',
				),
			)
		);

		$this->add_control(
			'premium_fancy_text_show_cursor',
			array(
				'label'     => __( 'Show Cursor', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => array(
					'premium_fancy_text_effect' => 'typing',
				),
			)
		);

		$this->add_control(
			'premium_slide_up_speed',
			array(
				'label'       => __( 'Animation Speed', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 200,
				'description' => __( 'Set a duration value in milliseconds for slide up effect.', 'premium-addons-for-elementor' ),
				'condition'   => array(
					'premium_fancy_text_effect' => 'slide',
				),
			)
		);

		$this->add_control(
			'premium_slide_up_pause_time',
			array(
				'label'       => __( 'Pause Time', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 3000,
				'description' => __( 'How long should the word/string stay visible? Set a value in milliseconds.', 'premium-addons-for-elementor' ),
				'condition'   => array(
					'premium_fancy_text_effect' => 'slide',
				),
			)
		);

		$this->add_control(
			'premium_slide_up_shown_items',
			array(
				'label'       => __( 'Show Items', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 1,
				'description' => __( 'How many items should be visible at a time?', 'premium-addons-for-elementor' ),
				'condition'   => array(
					'premium_fancy_text_effect' => 'slide',
				),
			)
		);

		$this->add_control(
			'premium_slide_up_hover_pause',
			array(
				'label'        => __( 'Pause on Hover', 'premium-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'prefix_class' => 'premium-atext__paused-',
				'render_type'  => 'template',
				'condition'    => array(
					'premium_fancy_text_effect' => 'slide',
				),
			)
		);

		$this->add_control(
			'loading_bar',
			array(
				'label'        => __( 'Loading Bar', 'premium-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'prefix_class' => 'premium-atext__loading-',
				'render_type'  => 'template',
				'condition'    => array(
					'premium_fancy_text_effect!' => 'typing',
				),
			)
		);

		$this->add_responsive_control(
			'premium_fancy_slide_align',
			array(
				'label'     => __( 'Animated Text Alignment', 'premium-addons-for-elementor' ),
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
					'{{WRAPPER}} .premium-fancy-list-items' => 'text-align: {{VALUE}}',
				),
				'condition' => array(
					'premium_fancy_text_effect' => 'slide',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'highlighted_settings',
			array(
				'label'     => __( 'Highlighted Settings', 'premium-addons-for-elementor' ),
				'condition' => array(
					'style' => 'highlight',
				),
			)
		);

		$this->add_control(
			'highlight_effect',
			array(
				'label'       => __( 'Effect', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'shadow'    => __( 'Animated Shadow', 'premium-addons-for-elementor' ),
					'pattern'   => __( 'Animated Pattern', 'premium-addons-for-elementor' ),
					'fill'      => __( 'Animated Fill', 'premium-addons-for-elementor' ),
					'tilt'      => __( 'Tilt', 'premium-addons-for-elementor' ),
					'flip'      => apply_filters( 'pa_pro_label', __( 'Flip (Pro)', 'premium-addons-for-elementor' ) ),
					'wave'      => apply_filters( 'pa_pro_label', __( 'Wave (Pro)', 'premium-addons-for-elementor' ) ),
					'pop'       => apply_filters( 'pa_pro_label', __( 'Pop (Pro)', 'premium-addons-for-elementor' ) ),
					'reveal'    => apply_filters( 'pa_pro_label', __( 'Reveal (Pro)', 'premium-addons-for-elementor' ) ),
					'lines'     => apply_filters( 'pa_pro_label', __( 'Moving Lines (Pro)', 'premium-addons-for-elementor' ) ),
					'underline' => apply_filters( 'pa_pro_label', __( 'Color Highlight (Pro)', 'premium-addons-for-elementor' ) ),
					'shape'     => apply_filters( 'pa_pro_label', __( 'Draw Shape (Pro)', 'premium-addons-for-elementor' ) ),
				),
				'default'     => 'shadow',
				'label_block' => true,
			)
		);

		$this->add_control(
			'pattern_notice',
			array(
				'raw'             => __( 'This effect works only with one word highlighted text.', 'premium-addons-for-elementor' ),
				'type'            => Controls_Manager::RAW_HTML,
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
				'condition'       => array(
					'highlight_effect' => 'pattern',
				),
			)
		);

		do_action( 'pa_atext_highlight_controls', $this );

		$this->add_control(
			'highlighted_hover_pause',
			array(
				'label'        => __( 'Pause on Hover', 'premium-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'prefix_class' => 'premium-atext__paused-',
				'render_type'  => 'template',
				'condition'    => array(
					'highlight_effect!' => array( 'underline', 'shape' ),
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_pa_docs',
			array(
				'label' => __( 'Help & Docs', 'premium-addons-for-elementor' ),
			)
		);

		$docs = array(
			'https://premiumaddons.com/docs/elementor-animated-text-widget-tutorial/' => __( 'Getting started »', 'premium-addons-for-elementor' ),
			'https://www.youtube.com/watch?v=Q5aRlJdXFw0' => __( 'Check the video tutorial »', 'premium-addons-for-elementor' ),
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
			'animated_text_style_section',
			array(
				'label' => __( 'Animated Text', 'premium-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'fancy_text_typography',
				'label'    => __( 'Headline Typography', 'premium-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .premium-atext__headline, {{WRAPPER}} .premium-atext__text svg g > text',
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				),
			)
		);

		$this->add_control(
			'premium_fancy_text_color',
			array(
				'label'      => __( 'Color', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::COLOR,
				'global'     => array(
					'default' => Global_Colors::COLOR_PRIMARY,
				),
				'selectors'  => array(
					'{{WRAPPER}} .premium-atext__text' => 'color: {{VALUE}};',
					'{{WRAPPER}} .premium-fancy-svg-text .premium-fancy-list-items, {{WRAPPER}} .text' => 'fill : {{VALUE}};',
				),
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'  => 'style',
							'value' => 'switch',
						),
						array(
							'terms' => array(
								array(
									'name'  => 'style',
									'value' => 'highlight',
								),
								array(
									'name'     => 'highlight_effect',
									'operator' => '!==',
									'value'    => 'fill',
								),
								array(
									'name'     => 'highlight_effect',
									'operator' => '!==',
									'value'    => 'reveal',
								),
							),
						),
					),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'fill_background',
				'types'     => array( 'classic', 'gradient' ),
				'condition' => array(
					'style'            => 'highlight',
					'highlight_effect' => 'fill',
				),
				'selector'  => '{{WRAPPER}} .premium-atext__text',
			)
		);

		$this->add_control(
			'highlight_color',
			array(
				'label'     => __( 'Highlight Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-atext__text::after' => 'background-color: {{VALUE}}',
				),
				'condition' => array(
					'style'            => 'highlight',
					'highlight_effect' => 'underline',
				),
			)
		);

		$this->add_control(
			'shadow_first_color',
			array(
				'label'     => __( 'First Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_PRIMARY,
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-atext__text' => '--pa-atext-fc: {{VALUE}}',
				),
				'condition' => array(
					'style'            => 'highlight',
					'highlight_effect' => array( 'shadow', 'pattern', 'lines' ),
				),
			)
		);

		$this->add_control(
			'shadow_second_color',
			array(
				'label'     => __( 'Second Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_SECONDARY,
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-atext__text, {{WRAPPER}} .premium-atext__pattern .premium-atext__text::after' => '--pa-atext-sc: {{VALUE}}',
				),
				'condition' => array(
					'style'            => 'highlight',
					'highlight_effect' => array( 'shadow', 'pattern', 'lines' ),
				),
			)
		);

		$this->add_control(
			'shadow_third_color',
			array(
				'label'     => __( 'Third Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_TEXT,
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-atext__text' => '--pa-atext-tc: {{VALUE}}',
				),
				'condition' => array(
					'style'            => 'highlight',
					'highlight_effect' => array( 'shadow', 'lines' ),
				),
			)
		);

		$this->add_control(
			'shadow_fourth_color',
			array(
				'label'     => __( 'Fourth Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_ACCENT,
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-atext__text' => '--pa-atext-foc: {{VALUE}}',
				),
				'condition' => array(
					'style'            => 'highlight',
					'highlight_effect' => array( 'shadow', 'lines' ),
				),
			)
		);

		$this->add_control(
			'shadow_fifth_color',
			array(
				'label'     => __( 'Fifth Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-atext__text' => '--pa-atext-fic: {{VALUE}}',
				),
				'condition' => array(
					'style'            => 'highlight',
					'highlight_effect' => 'lines',
				),
			)
		);

		$this->add_control(
			'premium_fancy_text_background_color',
			array(
				'label'      => __( 'Background Color', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::COLOR,
				'selectors'  => array(
					'{{WRAPPER}} .premium-atext__text' => 'background-color: {{VALUE}}',
				),
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'  => 'style',
							'value' => 'switch',
						),
						array(
							'terms' => array(
								array(
									'name'  => 'style',
									'value' => 'highlight',
								),
								array(
									'name'     => 'highlight_effect',
									'operator' => '!==',
									'value'    => 'fill',
								),
							),
						),
					),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'       => 'text_shadow',
				'selector'   => '{{WRAPPER}} .premium-atext__text',
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'  => 'style',
							'value' => 'switch',
						),
						array(
							'terms' => array(
								array(
									'name'  => 'style',
									'value' => 'highlight',
								),
								array(
									'name'     => 'highlight_effect',
									'operator' => '!==',
									'value'    => 'shadow',
								),
								array(
									'name'     => 'highlight_effect',
									'operator' => '!==',
									'value'    => 'lines',
								),
							),
						),
					),
				),
			)
		);

		$this->add_control(
			'stroke_width',
			array(
				'label'     => __( 'Stroke Width', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'selectors' => array(
					'{{WRAPPER}} .premium-atext__text' => '-webkit-text-stroke-width: {{SIZE}}px',
					'{{WRAPPER}} .text'                => 'stroke-width: {{SIZE}}',
				),
			)
		);

		$this->add_control(
			'stroke_text_color',
			array(
				'label'      => __( 'Stroke Color', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::COLOR,
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'  => 'style',
							'value' => 'switch',
						),
						array(
							'terms' => array(
								array(
									'name'  => 'style',
									'value' => 'highlight',
								),
								array(
									'name'     => 'highlight_effect',
									'operator' => '!==',
									'value'    => 'lines',
								),
							),
						),
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .premium-atext__text' => '-webkit-text-stroke-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'animation_speed',
			array(
				'label'     => __( 'Animation Speed (sec)', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 15,
						'step' => 0.1,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-atext__text, {{WRAPPER}} .premium-atext__text::after, {{WRAPPER}} .premium-atext__letter, {{WRAPPER}} .text' => 'animation-duration: {{SIZE}}s',
					'{{WRAPPER}} .premium-atext__shape svg path' => '--pa-animation-duration: {{SIZE}}s',
				),
				'condition' => array(
					'style'             => 'highlight',
					'highlight_effect!' => 'underline',
				),
			)
		);

		$this->add_control(
			'animation_delay',
			array(
				'label'     => __( 'Animation Delay (sec)', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					's' => array(
						'min' => 0,
						'max' => 15,
					),
				),
				'condition' => array(
					'style'            => 'highlight',
					'highlight_effect' => 'shape',
				),
			)
		);

		$this->add_responsive_control(
			'text_margin',
			array(
				'label'      => __( 'Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-atext__text' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		do_action( 'pa_atext_shape_style', $this );

		$this->start_controls_section(
			'premium_fancy_cursor_text_style_tab',
			array(
				'label'     => __( 'Cursor', 'premium-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'style'                     => 'switch',
					// 'premium_fancy_text_cursor_text!' => '',
					'premium_fancy_text_effect' => 'typing',
				),
			)
		);

		$this->add_control(
			'premium_fancy_text_cursor_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_PRIMARY,
				),
				'selectors' => array(
					'{{WRAPPER}} .typed-cursor' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'fancy_text_cursor_typography',
				'selector' => '{{WRAPPER}} .typed-cursor',
			)
		);

		$this->add_control(
			'premium_fancy_text_cursor_background',
			array(
				'label'     => __( 'Background Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .typed-cursor' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'premium_prefix_suffix_style_tab',
			array(
				'label' => __( 'Before & After Text', 'premium-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'premium_prefix_text_color',
			array(
				'label'     => __( 'Before Text Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_SECONDARY,
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-prefix-text' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'premium_suffix_text_color',
			array(
				'label'     => __( 'After Text Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_SECONDARY,
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-suffix-text' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'prefix_suffix_typography',
				'selector' => '{{WRAPPER}} .premium-prefix-text, {{WRAPPER}} .premium-suffix-text',
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				),
				'exclude'  => array( 'font_size' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'loading_bar_style',
			array(
				'label'     => __( 'Loading Bar', 'premium-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'style'                      => 'switch',
					'loading_bar'                => 'yes',
					'premium_fancy_text_effect!' => 'typing',
				),
			)
		);

		$this->add_control(
			'loading_bar_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_PRIMARY,
				),
				'selectors' => array(
					'{{WRAPPER}}.premium-atext__loading-yes .premium-loading-bar' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'loading_bar_height',
			array(
				'label'     => __( 'Height', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 10,
					),
				),
				'selectors' => array(
					'{{WRAPPER}}.premium-atext__loading-yes .premium-loading-bar' => 'height: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render Fancy Text widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function render() {

		$settings = $this->get_settings_for_display();

		$papro_activated = apply_filters( 'papro_activated', false );

		if ( ! $papro_activated || version_compare( PREMIUM_PRO_ADDONS_VERSION, '2.9.18', '<' ) ) {

			if ( ( 'switch' === $settings['style'] && 'clip' === $settings['premium_fancy_text_effect'] ) || ( 'highlight' === $settings['style'] && ! in_array( $settings['highlight_effect'], array( 'shadow', 'pattern', 'fill', 'tilt' ), true ) ) ) {

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

		$effect = $settings['premium_fancy_text_effect'];

		$title_tag = Helper_Functions::validate_html_tag( $settings['text_tag'] );

		$this->add_render_attribute( 'wrapper', 'class', 'premium-atext__wrapper' );

		if ( 'switch' === $settings['style'] ) {

			$this->add_render_attribute( 'wrapper', 'data-start-effect', $settings['trigger'] );

			$loading_bar = 'yes' === $settings['loading_bar'];

			$pause = '';

			if ( 'typing' === $effect ) {

				$show_cursor = ( ! empty( $settings['premium_fancy_text_show_cursor'] ) ) ? true : false;

				// $cursor_text = addslashes( $settings['premium_fancy_text_cursor_text'] );

				$loop = ! empty( $settings['premium_fancy_text_type_loop'] ) ? true : false;

				$strings = array();

				foreach ( $settings['premium_fancy_text_strings'] as $item ) {
					if ( ! empty( $item['premium_text_strings_text_field'] ) ) {
						array_push( $strings, str_replace( '\'', '&#39;', $item['premium_text_strings_text_field'] ) );
					}
				}

				// $cursor_text    = html_entity_decode( $cursor_text );
				$atext_settings = array(
					'effect'     => $effect,
					'strings'    => $strings,
					'typeSpeed'  => $settings['premium_fancy_text_type_speed'],
					'backSpeed'  => $settings['premium_fancy_text_back_speed'],
					'startDelay' => $settings['premium_fancy_text_start_delay'],
					'backDelay'  => $settings['premium_fancy_text_back_delay'],
					'showCursor' => $show_cursor,
					'loop'       => $loop,
				);

			} elseif ( 'slide' === $effect ) {

				$this->add_render_attribute( 'prefix', 'class', 'premium-atext__span-align' );
				$this->add_render_attribute( 'suffix', 'class', 'premium-atext__span-align' );

				$mouse_pause    = 'yes' === $settings['premium_slide_up_hover_pause'] ? true : false;
				$pause          = $mouse_pause ? 'pause' : '';
				$atext_settings = array(
					'effect'     => $effect,
					'speed'      => $settings['premium_slide_up_speed'],
					'showItems'  => $settings['premium_slide_up_shown_items'],
					'pause'      => $settings['premium_slide_up_pause_time'],
					'mousePause' => $mouse_pause,
				);
			} else {

				$atext_settings = array(
					'effect' => $effect,
					'delay'  => $settings['premium_fancy_text_zoom_delay'],
					'count'  => $settings['loop_count'],
				);

				if ( 'custom' === $effect ) {
					$atext_settings['animation'] = $settings['custom_animation'];
				} elseif ( 'clip' === $effect ) {
					$atext_settings['speed'] = $settings['premium_fancy_text_zoom_speed'];
				}
			}

			$atext_settings['loading'] = $loading_bar;
			$atext_settings['style']   = $settings['style'];

			$this->add_render_attribute(
				'wrapper',
				array(
					'class'         => array(
						'premium-atext__' . $effect,
						$pause,
					),
					'data-settings' => wp_json_encode( $atext_settings ),
				)
			);

		} else {

			$this->add_render_attribute( 'wrapper', 'class', 'premium-atext' );

			$effect = $settings['highlight_effect'];

			$atext_settings = array(
				'effect' => $effect,
				'style'  => $settings['style'],
			);

			if ( 'shape' === $effect ) {
				$atext_settings['delay']    = $settings['animation_delay']['size'];
				$atext_settings['duration'] = $settings['animation_speed']['size'];
			}

			$this->add_render_attribute(
				'wrapper',
				array(
					'class'         => array(
						'premium-atext__' . $effect,
					),
					'data-settings' => wp_json_encode( $atext_settings ),
				)
			);

		}

		if ( ! empty( $settings['link']['url'] ) ) {
			$this->add_link_attributes( 'url', $settings['link'] );
		}

		?>
		<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'wrapper' ) ); ?>>

			<<?php echo wp_kses_post( $title_tag ); ?> class="premium-atext__headline">
			<?php if ( ! empty( $settings['premium_fancy_prefix_text'] ) ) : ?>
				<span class="premium-prefix-text">
					<span <?php echo wp_kses_post( $this->get_render_attribute_string( 'prefix' ) ); ?>><?php echo wp_kses( ( $settings['premium_fancy_prefix_text'] ), true ); ?></span>
				</span>
			<?php endif; ?>

			<?php
			if ( 'highlight' === $settings['style'] ) :
				$this->render_highlight_text();
			else :
				$this->render_switch_text();
			endif;
			?>

			<?php if ( ! empty( $settings['premium_fancy_suffix_text'] ) ) : ?>
				<span class="premium-suffix-text">
					<span <?php echo wp_kses_post( $this->get_render_attribute_string( 'suffix' ) ); ?>><?php echo wp_kses( ( $settings['premium_fancy_suffix_text'] ), true ); ?></span>
				</span>
			<?php endif; ?>

			<?php if ( ! empty( $settings['link']['url'] ) ) : ?>
				<a <?php $this->print_render_attribute_string( 'url' ); ?>></a>
			<?php endif; ?>

			</<?php echo wp_kses_post( $title_tag ); ?>>
		</div>
		<?php
	}

	/**
	 * Render Highlight Text
	 *
	 * @since 4.10.34
	 * @access protected
	 */
	protected function render_switch_text() {

		$settings = $this->get_settings_for_display();

		$effect = $settings['premium_fancy_text_effect'];

		if ( 'typing' === $effect ) :
			?>
			<span class="premium-atext__text"></span>
		<?php else : ?>
			<div class="premium-atext__text" style='display: inline-block; text-align: center'>
				<ul class="premium-atext__items-wrapper">
			<?php
			foreach ( $settings['premium_fancy_text_strings'] as $index => $item ) :
				if ( ! empty( $item['premium_text_strings_text_field'] ) ) :
					$this->add_render_attribute( 'text_' . $item['_id'], 'class', 'premium-fancy-list-items' );

					if ( ( 'typing' !== $effect && 'slide' !== $effect ) && 0 !== $index ) {
						$this->add_render_attribute( 'text_' . $item['_id'], 'class', 'premium-fancy-item-hidden' );
					} else {
						$this->add_render_attribute( 'text_' . $item['_id'], 'class', 'premium-fancy-item-visible' );
					}

					?>
						<li <?php echo wp_kses_post( $this->get_render_attribute_string( 'text_' . $item['_id'] ) ); ?>>
							<?php echo wp_kses_post( $item['premium_text_strings_text_field'] ); ?>
						</li>
					<?php
				endif;
			endforeach;
			?>
				</ul>
			</div>
			<?php
		endif;
	}

	/**
	 * Render Switch Text
	 *
	 * @since 4.10.34
	 * @access protected
	 */
	protected function render_highlight_text() {

		$settings = $this->get_settings_for_display();

		$this->add_render_attribute(
			'text',
			array(
				'class'     => 'premium-atext__text',
				'data-text' => $settings['text'],
			)
		);

		if ( 'reveal' === $settings['highlight_effect'] ) {

			$image_url = PREMIUM_ADDONS_URL . 'assets/frontend/images/reveal_background.jpg';
			$this->add_render_attribute( 'text', 'style', "background-image: url('$image_url')" );

		}

		?>

			<?php if ( 'lines' !== $settings['highlight_effect'] ) : ?>
				<span <?php echo wp_kses_post( $this->get_render_attribute_string( 'text' ) ); ?>>
					<?php echo wp_kses_post( $settings['text'] ); ?>
					<?php if ( 'shape' === $settings['highlight_effect'] ) : ?>
						<?php $this->render_draw_shape(); ?>
					<?php endif; ?>
				</span>

			<?php else : ?>
				<svg class="premium-atext__text">
					<!-- Symbol -->
					<symbol id="s-text">
						<text text-anchor="middle" x="50%" y="50%" dy=".35em">
							<?php echo wp_kses_post( $settings['text'] ); ?>
						</text>
					</symbol>

					<!-- Duplicate symbols -->
					<use xlink:href="#s-text" class="text"></use>
					<use xlink:href="#s-text" class="text"></use>
					<use xlink:href="#s-text" class="text"></use>
					<use xlink:href="#s-text" class="text"></use>
					<use xlink:href="#s-text" class="text"></use>

				</svg>
			<?php endif; ?>

		<?php
	}

	/**
	 * Render Draw shape
	 *
	 * @since 4.10.34
	 * @access protected
	 */
	protected function render_draw_shape() {

		$settings = $this->get_settings_for_display();

		$shape = $settings['shape'];

		$shapes_array = array(
			'circle'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 150" preserveAspectRatio="none"><path d="M325,18C228.7-8.3,118.5,8.3,78,21C22.4,38.4,4.6,54.6,5.6,77.6c1.4,32.4,52.2,54,142.6,63.7 c66.2,7.1,212.2,7.5,273.5-8.3c64.4-16.6,104.3-57.6,33.8-98.2C386.7-4.9,179.4-1.4,126.3,20.7"></path></svg>',

			'wavy'      => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 150" preserveAspectRatio="none"><path d="M3,146.1c17.1-8.8,33.5-17.8,51.4-17.8c15.6,0,17.1,18.1,30.2,18.1c22.9,0,36-18.6,53.9-18.6 c17.1,0,21.3,18.5,37.5,18.5c21.3,0,31.8-18.6,49-18.6c22.1,0,18.8,18.8,36.8,18.8c18.8,0,37.5-18.6,49-18.6c20.4,0,17.1,19,36.8,19 c22.9,0,36.8-20.6,54.7-18.6c17.7,1.4,7.1,19.5,33.5,18.8c17.1,0,47.2-6.5,61.1-15.6"></path></svg>',

			'underline' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 150" preserveAspectRatio="none"><path d="M7.7,145.6C109,125,299.9,116.2,401,121.3c42.1,2.2,87.6,11.8,87.3,25.7"></path></svg>',

			'double'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 150" preserveAspectRatio="none"><path d="M5,125.4c30.5-3.8,137.9-7.6,177.3-7.6c117.2,0,252.2,4.7,312.7,7.6"></path><path d="M26.9,143.8c55.1-6.1,126-6.3,162.2-6.1c46.5,0.2,203.9,3.2,268.9,6.4"></path></svg>',

			'zigzag'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 150" preserveAspectRatio="none"><path d="M9.3,127.3c49.3-3,150.7-7.6,199.7-7.4c121.9,0.4,189.9,0.4,282.3,7.2C380.1,129.6,181.2,130.6,70,139 c82.6-2.9,254.2-1,335.9,1.3c-56,1.4-137.2-0.3-197.1,9"></path></svg>',

			'strike'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 150" preserveAspectRatio="none"><path d="M3,75h493.5"></path></svg>',

			'cross'     => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 150" preserveAspectRatio="none"><path d="M497.4,23.9C301.6,40,155.9,80.6,4,144.4"></path><path d="M14.1,27.6c204.5,20.3,393.8,74,467.3,111.7"></path></svg>',

		);

		echo $shapes_array[ $shape ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
