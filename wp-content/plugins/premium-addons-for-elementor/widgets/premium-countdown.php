<?php
/**
 * Premium Countdown.
 */

namespace PremiumAddons\Widgets;

// Elementor Classes.
use Elementor\Modules\DynamicTags\Module as TagsModule;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;

// PHP Classes.
use Datetime;
use DateTimeZone;

// PremiumAddons Classes.
use PremiumAddons\Includes\Helper_Functions;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Premium_Countdown
 */
class Premium_Countdown extends Widget_Base {

	/**
	 * Retrieve Widget Name.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function get_name() {
		return 'premium-countdown-timer';
	}

	/**
	 * Retrieve Widget Title.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function get_title() {
		return __( 'Countdown', 'premium-addons-for-elementor' );
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
		return 'pa-countdown';
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
	 * Retrieve Widget Dependent CSS.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array CSS style handles.
	 */
	public function get_style_depends() {
		return array(
			'pa-flipclock',
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
			'pa-countdown',
			'pa-flipclock',
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
		return array( 'pa', 'premium', 'premium countdown', 'counter', 'time', 'event' );
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
	 * Register Countdown controls.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore

		$papro_activated = apply_filters( 'papro_activated', false );

		$options = apply_filters(
			'pa_countdown_options',
			array(
				'styles'          => array(
					'default'  => __( 'Default', 'premium-addons-for-elementor' ),
					'featured' => __( 'Featured Unit', 'premium-addons-for-elementor' ),
					'circle'   => __( 'Circle (Pro)', 'premium-addons-for-elementor' ),
					'rotate'   => __( 'Rotate (Pro)', 'premium-addons-for-elementor' ),
					'flipping' => __( 'Flip (Pro)', 'premium-addons-for-elementor' ),
				),
				'style_condition' => array( 'circle', 'rotate', 'flipping' ),
			)
		);

		$this->start_controls_section(
			'countdown_section',
			array(
				'label' => __( 'Countdown', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'premium_countdown_type',
			array(
				'label'   => __( 'Type', 'premium-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'fixed'     => __( 'Fixed Timer', 'premium-addons-for-elementor' ),
					'evergreen' => __( 'Evergreen Timer', 'premium-addons-for-elementor' ),
				),
				'default' => 'fixed',
			)
		);

		$this->add_control(
			'premium_countdown_date_time',
			array(
				'label'          => __( 'Due Date', 'premium-addons-for-elementor' ),
				'description'    => __( 'Date format is (yyyy/mm/dd). Time format is (hh:mm:ss). Example: 2020-01-01 09:30.', 'premium-addons-for-elementor' ),
				'type'           => Controls_Manager::DATE_TIME,
				'picker_options' => array(
					'format' => 'Ym/d H:m:s',
				),
				'default'        => gmdate( 'Y/m/d H:m:s', strtotime( '+ 2 Day' ) ),
				'dynamic'        => array(
					'active' => true,
				),
				'condition'      => array(
					'premium_countdown_type' => 'fixed',
				),
			)
		);

		$this->add_control(
			'premium_countdown_eve_days',
			array(
				'label'       => __( 'Days', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => '0',
				'dynamic'     => array( 'active' => true ),
				'render_type' => 'template',
				'default'     => 2,
				'condition'   => array(
					'premium_countdown_type' => 'evergreen',
				),
			)
		);

		$this->add_control(
			'premium_countdown_eve_hours',
			array(
				'label'       => __( 'Hours', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => '0',
				'max'         => '23',
				'dynamic'     => array( 'active' => true ),
				'render_type' => 'template',
				'default'     => 3,
				'condition'   => array(
					'premium_countdown_type' => 'evergreen',
				),
			)
		);

		$this->add_control(
			'premium_countdown_eve_min',
			array(
				'label'       => __( 'Minutes', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => '0',
				'max'         => '59',
				'dynamic'     => array( 'active' => true ),
				'render_type' => 'template',
				'default'     => 0,
				'condition'   => array(
					'premium_countdown_type' => 'evergreen',
				),
			)
		);

		$this->add_control(
			'premium_countdown_eve_reset',
			array(
				'label'     => __( 'Reset', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => array(
					'premium_countdown_type' => 'evergreen',
				),
			)
		);

		$this->add_control(
			'premium_countdown_eve_reset_hours',
			array(
				'label'       => __( 'Hours', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => '0',
				'dynamic'     => array( 'active' => true ),
				'render_type' => 'template',
				'default'     => 24,
				'condition'   => array(
					'premium_countdown_type'      => 'evergreen',
					'premium_countdown_eve_reset' => 'yes',
				),
			)
		);

		$this->add_control(
			'premium_countdown_eve_reset_min',
			array(
				'label'       => __( 'Minutes', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => '0',
				'max'         => '59',
				'dynamic'     => array( 'active' => true ),
				'render_type' => 'template',
				'default'     => 0,
				'condition'   => array(
					'premium_countdown_type'      => 'evergreen',
					'premium_countdown_eve_reset' => 'yes',
				),
			)
		);

		$this->add_control(
			'timezone',
			array(
				'label'       => __( 'Timezone', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'description' => __( 'Select the timezone to calculate the time left.', 'premium-addons-for-elementor' ),
				'options'     => array(
					'wp-time'   => __( 'WordPress Default', 'premium-addons-for-elementor' ),
					'user-time' => __( 'User Local Time', 'premium-addons-for-elementor' ),
				),
				'default'     => 'wp-time',
			)
		);

		$this->add_control(
			'premium_countdown_units',
			array(
				'label'       => __( 'Time Units', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT2,
				'description' => __( 'Select the time units that you want to display in countdown timer.', 'premium-addons-for-elementor' ),
				'options'     => array(
					'Y' => __( 'Years', 'premium-addons-for-elementor' ),
					'O' => __( 'Months', 'premium-addons-for-elementor' ),
					'D' => __( 'Days', 'premium-addons-for-elementor' ),
					'H' => __( 'Hours', 'premium-addons-for-elementor' ),
					'M' => __( 'Minutes', 'premium-addons-for-elementor' ),
					'S' => __( 'Seconds', 'premium-addons-for-elementor' ),
				),
				'default'     => array( 'D', 'H', 'M', 'S' ),
				'multiple'    => true,
				'separator'   => 'after',
				'condition'   => array(
					'style!' => 'flipping',
				),
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
			'style',
			array(
				'label'   => __( 'Style', 'premium-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $options['styles'],
				'default' => 'default',
			)
		);

		$this->add_control(
			'flip_language',
			array(
				'label'     => __( 'Language', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					'ar' => __( 'Arabic', 'premium-addons-for-elementor' ),
					'da' => __( 'Danish', 'premium-addons-for-elementor' ),
					'de' => __( 'German', 'premium-addons-for-elementor' ),
					'en' => __( 'English', 'premium-addons-for-elementor' ),
					'es' => __( 'Spanish', 'premium-addons-for-elementor' ),
					'fi' => __( 'Finnish', 'premium-addons-for-elementor' ),
					'fr' => __( 'French', 'premium-addons-for-elementor' ),
					'it' => __( 'Italian', 'premium-addons-for-elementor' ),
					'nl' => __( 'Dutch', 'premium-addons-for-elementor' ),
					'pt' => __( 'Portuguese', 'premium-addons-for-elementor' ),
					'ru' => __( 'Russian', 'premium-addons-for-elementor' ),
					'sv' => __( 'Swedish', 'premium-addons-for-elementor' ),
				),
				'default'   => 'en',
				'condition' => array(
					'style' => 'flipping',
				),
			)
		);

		$this->add_control(
			'featured_unit',
			array(
				'label'     => __( 'Featured Time Unit', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					'0' => __( 'Years', 'premium-addons-for-elementor' ),
					'1' => __( 'Months', 'premium-addons-for-elementor' ),
					'3' => __( 'Days', 'premium-addons-for-elementor' ),
					'4' => __( 'Hours', 'premium-addons-for-elementor' ),
					'5' => __( 'Minutes', 'premium-addons-for-elementor' ),
					'6' => __( 'Seconds', 'premium-addons-for-elementor' ),
				),
				'default'   => '3',
				'condition' => array(
					'style' => 'featured',
				),
			)
		);

		$this->add_responsive_control(
			'featured_unit_size',
			array(
				'label'     => __( 'Featured Unit Width (PX)', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 1,
						'max' => 300,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .countdown-section:first-child .countdown-amount' => 'width: {{SIZE}}px',
				),
				'condition' => array(
					'style' => 'featured',
				),
			)
		);

		if ( ! $papro_activated ) {

			$get_pro = Helper_Functions::get_campaign_link( 'https://premiumaddons.com/pro', 'editor-page', 'wp-editor', 'get-pro' );

			$this->add_control(
				'notification_notice',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => __( 'This option is available in Premium Addons Pro. ', 'premium-addons-for-elementor' ) . '<a href="' . esc_url( $get_pro ) . '" target="_blank">' . __( 'Upgrade now!', 'premium-addons-for-elementor' ) . '</a>',
					'content_classes' => 'papro-upgrade-notice',
					'condition'       => array(
						'style' => $options['style_condition'],
					),
				)
			);

		}

		$this->add_responsive_control(
			'digit_size',
			array(
				'label'       => __( 'Digit Size (px)', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array(
					'px' => array(
						'min' => 1,
						'max' => 200,
					),
				),
				'default'     => array(
					'unit' => 'px',
					'size' => 60,
				),
				'render_type' => 'template',
				'selectors'   => array(
					'{{WRAPPER}} .countdown-amount' => 'width: {{SIZE}}px; height: {{SIZE}}px;',
					'{{WRAPPER}}.premium-countdown-block .countdown-period span' => 'width: {{SIZE}}px;',
					'{{WRAPPER}} .premium-countdown-flipping .flip' => 'width: {{SIZE}}px;',
				),
			)
		);

		$this->add_control(
			'unit_inside_circle',
			array(
				'label'        => __( 'Add Units Inside Digits', 'premium-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'prefix_class' => 'premium-countdown-uinside-',
				'render_type'  => 'template',
				'condition'    => array(
					'style!' => 'flipping',
				),
			)
		);

		$this->add_control(
			'unit_position',
			array(
				'label'        => __( 'Time Units Position', 'premium-addons-for-elementor' ),
				'type'         => Controls_Manager::SELECT,
				'options'      => array(
					'inline' => __( 'Inline', 'premium-addons-for-elementor' ),
					'block'  => __( 'Block', 'premium-addons-for-elementor' ),
				),
				'prefix_class' => 'premium-countdown-',
				'render_type'  => 'template',
				'default'      => 'block',
				'condition'    => array(
					'unit_inside_circle!' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'units_valignment',
			array(
				'label'     => __( 'Units Alignment', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => __( 'Top', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-arrow-up',
					),
					'center'     => array(
						'title' => __( 'Center', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-text-align-justify',
					),
					'flex-end'   => array(
						'title' => __( 'Bottom', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-arrow-down',
					),
				),
				'default'   => 'flex-end',
				'toggle'    => false,
				'condition' => array(
					'unit_position'       => 'inline',
					'unit_inside_circle!' => 'yes',
					'style!'              => 'circle',
				),
				'selectors' => array(
					'{{WRAPPER}}.premium-countdown-inline .countdown-section, {{WRAPPER}}.premium-countdown-inline .flip-unit-wrap' => 'align-items: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'premium_countdown_separator',
			array(
				'label'      => __( 'Digits Separator', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SWITCHER,
				'default'    => 'yes',
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'  => 'unit_position',
							'value' => 'block',
						),
						array(
							'name'  => 'unit_inside_circle',
							'value' => 'yes',
						),
					),
				),
			)
		);

		$this->add_control(
			'premium_countdown_separator_text',
			array(
				'label'      => __( 'Separator Shape', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SELECT,
				'options'    => array(
					':'      => __( 'Colon', 'premium-addons-for-elementor' ),
					'.'      => __( 'Dot', 'premium-addons-for-elementor' ),
					'custom' => __( 'Custom', 'premium-addons-for-elementor' ),
				),
				'default'    => ':',
				'conditions' => array(
					'terms' => array(
						array(
							'name'  => 'premium_countdown_separator',
							'value' => 'yes',
						),
						array(
							'relation' => 'or',
							'terms'    => array(
								array(
									'name'  => 'unit_position',
									'value' => 'block',
								),
								array(
									'name'  => 'unit_inside_circle',
									'value' => 'yes',
								),
							),
						),
					),
				),
			)
		);

		$this->add_control(
			'custom_separator',
			array(
				'label'      => __( 'Separator Text', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::TEXT,
				'conditions' => array(
					'terms' => array(
						array(
							'name'  => 'premium_countdown_separator',
							'value' => 'yes',
						),
						array(
							'name'  => 'premium_countdown_separator_text',
							'value' => 'custom',
						),
						array(
							'relation' => 'or',
							'terms'    => array(
								array(
									'name'  => 'unit_position',
									'value' => 'block',
								),
								array(
									'name'  => 'unit_inside_circle',
									'value' => 'yes',
								),
							),
						),
					),
				),
			)
		);

		$this->add_responsive_control(
			'premium_countdown_align',
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
				'toggle'    => false,
				'default'   => 'center',
				'selectors' => array(
					'{{WRAPPER}} .premium-countdown' => 'justify-content: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'expire_section',
			array(
				'label' => __( 'After Expire', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'expiration_type',
			array(
				'label'       => __( 'Choose Action', 'premium-addons-for-elementor' ),
				'label_block' => false,
				'type'        => Controls_Manager::SELECT,
				'description' => __( 'Choose whether if you want to set a message or a redirect link or leave it as digits', 'premium-addons-for-elementor' ),
				'options'     => array(
					'default' => __( 'Default', 'premium-addons-for-elementor' ),
					'text'    => __( 'Message', 'premium-addons-for-elementor' ),
					'restart' => __( 'Restart', 'premium-addons-for-elementor' ),
					'url'     => __( 'Redirection Link', 'premium-addons-for-elementor' ),
				),
				'default'     => 'text',
			)
		);

		$this->add_control(
			'change_digits',
			array(
				'label'       => __( 'Change Digits To', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'description' => __( 'Choose whether if you want to set a message or a redirect link or leave it as digits', 'premium-addons-for-elementor' ),
				'options'     => array(
					'default' => __( 'None', 'premium-addons-for-elementor' ),
					'dash'    => __( 'Dash', 'premium-addons-for-elementor' ),
					'done'    => __( 'D-O-N-E', 'premium-addons-for-elementor' ),
				),
				'default'     => 'default',
				'condition'   => array(
					'expiration_type' => 'default',
					'style!'          => 'flipping',
				),
			)
		);

		$this->add_control(
			'expiration_text',
			array(
				'label'     => __( 'Text', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::WYSIWYG,
				'dynamic'   => array( 'active' => true ),
				'default'   => __( 'Countdown Expired!', 'premium-addons-for-elementor' ),
				'condition' => array(
					'expiration_type' => 'text',
				),
			)
		);

		$this->add_control(
			'expiration_url',
			array(
				'label'       => __( 'URL', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array(
					'active'     => true,
					'categories' => array(
						TagsModule::POST_META_CATEGORY,
						TagsModule::URL_CATEGORY,
					),
				),
				'label_block' => true,
				'condition'   => array(
					'expiration_type' => 'url',
				),
			)
		);

		$this->add_control(
			'restart_days',
			array(
				'label'     => __( 'Days', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 1,
				'condition' => array(
					'expiration_type' => 'restart',
				),
			)
		);

		$this->add_control(
			'restart_hours',
			array(
				'label'     => __( 'Hours', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 2,
				'condition' => array(
					'expiration_type' => 'restart',
				),
			)
		);

		$this->add_control(
			'restart_minutes',
			array(
				'label'     => __( 'Minutes', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 55,
				'condition' => array(
					'expiration_type' => 'restart',
				),
			)
		);

		$this->add_control(
			'restart_notice',
			array(
				'raw'             => __( 'When the action is set to restart, all times will be calculated based on the server timezone.', 'premium-addons-for-elementor' ),
				'type'            => Controls_Manager::RAW_HTML,
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
				'condition'       => array(
					'expiration_type' => 'restart',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'premium_countdown_transaltion',
			array(
				'label'     => __( 'Strings Translation', 'premium-addons-for-elementor' ),
				'condition' => array(
					'style' => '',
				),
			)
		);

		$this->add_control(
			'premium_countdown_day_singular',
			array(
				'label'   => __( 'Day (Singular)', 'premium-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'dynamic' => array( 'active' => true ),
				'default' => 'Day',
			)
		);

		$this->add_control(
			'premium_countdown_day_plural',
			array(
				'label'   => __( 'Day (Plural)', 'premium-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'dynamic' => array( 'active' => true ),
				'default' => 'Days',
			)
		);

		$this->add_control(
			'premium_countdown_month_singular',
			array(
				'label'   => __( 'Month (Singular)', 'premium-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'dynamic' => array( 'active' => true ),
				'default' => 'Month',
			)
		);

		$this->add_control(
			'premium_countdown_month_plural',
			array(
				'label'   => __( 'Months (Plural)', 'premium-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'dynamic' => array( 'active' => true ),
				'default' => 'Months',
			)
		);

		$this->add_control(
			'premium_countdown_year_singular',
			array(
				'label'   => __( 'Year (Singular)', 'premium-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'dynamic' => array( 'active' => true ),
				'default' => 'Year',
			)
		);

		$this->add_control(
			'premium_countdown_year_plural',
			array(
				'label'   => __( 'Years (Plural)', 'premium-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'dynamic' => array( 'active' => true ),
				'default' => 'Years',
			)
		);

		$this->add_control(
			'premium_countdown_hour_singular',
			array(
				'label'   => __( 'Hour (Singular)', 'premium-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'dynamic' => array( 'active' => true ),
				'default' => 'Hour',
			)
		);

		$this->add_control(
			'premium_countdown_hour_plural',
			array(
				'label'   => __( 'Hours (Plural)', 'premium-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'dynamic' => array( 'active' => true ),
				'default' => 'Hours',
			)
		);

		$this->add_control(
			'premium_countdown_minute_singular',
			array(
				'label'   => __( 'Minute (Singular)', 'premium-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'dynamic' => array( 'active' => true ),
				'default' => 'Minute',
			)
		);

		$this->add_control(
			'premium_countdown_minute_plural',
			array(
				'label'   => __( 'Minutes (Plural)', 'premium-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'dynamic' => array( 'active' => true ),
				'default' => 'Minutes',
			)
		);

		$this->add_control(
			'premium_countdown_second_singular',
			array(
				'label'   => __( 'Second (Singular)', 'premium-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'dynamic' => array( 'active' => true ),
				'default' => 'Second',
			)
		);

		$this->add_control(
			'premium_countdown_second_plural',
			array(
				'label'   => __( 'Seconds (Plural)', 'premium-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'dynamic' => array( 'active' => true ),
				'default' => 'Seconds',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_pa_docs',
			array(
				'label' => __( 'Help & Docs', 'premium-addons-for-elementor' ),
			)
		);

		$doc1_url = Helper_Functions::get_campaign_link( 'https://premiumaddons.com/docs/countdown-widget-tutorial/', 'editor-page', 'wp-editor', 'get-support' );

		$this->add_control(
			'doc_1',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => sprintf( '<a href="%s" target="_blank">%s</a>', $doc1_url, __( 'Gettings started »', 'premium-addons-for-elementor' ) ),
				'content_classes' => 'editor-pa-doc',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'premium_countdown_typhography',
			array(
				'label' => __( 'Digits', 'premium-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'premium_countdown_digit_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_SECONDARY,
				),
				'selectors' => array(
					'{{WRAPPER}} .countdown-amount, {{WRAPPER}} .inn' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'circle_stroke_color',
			array(
				'label'     => __( 'Stroke Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .countdown-svg path' => 'stroke: {{VALUE}};',
				),
				'condition' => array(
					'style' => 'circle',
				),
			)
		);

		$this->add_responsive_control(
			'circle_stroke_width',
			array(
				'label'       => __( 'Stroke Width (px)', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array(
					'px' => array(
						'min' => 1,
						'max' => 15,
					),
				),
				'default'     => array(
					'unit' => 'px',
					'size' => 4,
				),
				'render_type' => 'template',
				'selectors'   => array(
					'{{WRAPPER}} .countdown-svg path' => 'stroke-width: {{SIZE}}px',
					'{{WRAPPER}}'                     => '--pa-countdown-stroke-width: {{SIZE}}',
				),
				'condition'   => array(
					'style' => 'circle',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'premium_countdown_digit_typo',
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				),
				'selector' => '{{WRAPPER}} .countdown-amount, {{WRAPPER}} .inn',
			)
		);

		$this->add_control(
			'premium_countdown_timer_digit_bg_color',
			array(
				'label'     => __( 'Background Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_PRIMARY,
				),
				'selectors' => array(
					'{{WRAPPER}} .countdown-amount, {{WRAPPER}} .inn' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'flip_separator_color',
			array(
				'label'     => __( 'Separator Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .flip-clock-wrapper div.up::after' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'style' => 'flipping',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'digits_shadow',
				'selector' => '{{WRAPPER}} .countdown-amount span, {{WRAPPER}} .inn',

			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'premium_countdown_units_shadow',
				'selector' => '{{WRAPPER}} .countdown-amount, {{WRAPPER}} .flip',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'premium_countdown_digits_border',
				'selector'  => '{{WRAPPER}} .countdown-amount, {{WRAPPER}} .premium-countdown-figure, {{WRAPPER}} .flip',
				'condition' => array(
					'style!' => 'circle',
				),
			)
		);

		$this->add_control(
			'premium_countdown_digit_border_radius',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .countdown-amount, {{WRAPPER}} .flip' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'style!'            => 'circle',
					'digit_adv_radius!' => 'yes',
				),
			)
		);

		$this->add_control(
			'digit_adv_radius',
			array(
				'label'       => __( 'Advanced Border Radius', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SWITCHER,
				'description' => __( 'Apply custom radius values. Get the radius value from ', 'premium-addons-for-elementor' ) . '<a href="https://9elements.github.io/fancy-border-radius/" target="_blank">here</a>' . __('. See ', 'premium-addons-for-elementor') . '<a href="https://www.youtube.com/watch?v=S0BJazLHV-M" target="_blank">tutorial</a>',
				'condition'   => array(
					'style!' => 'circle',
				),
			)
		);

		$this->add_control(
			'digit_adv_radius_value',
			array(
				'label'     => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'dynamic'   => array( 'active' => true ),
				'selectors' => array(
					'{{WRAPPER}} .countdown-amount, {{WRAPPER}} .flip' => 'border-radius: {{VALUE}};',
				),
				'condition' => array(
					'style!'           => 'circle',
					'digit_adv_radius' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'digits_margin',
			array(
				'label'      => __( 'Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .flip' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'style' => 'flipping',
				),
			)
		);

		$this->add_responsive_control(
			'digits_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .countdown-amount' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'style!' => 'flipping',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'premium_countdown_unit_style',
			array(
				'label' => __( 'Units', 'premium-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'premium_countdown_unit_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_SECONDARY,
				),
				'selectors' => array(
					'{{WRAPPER}} .countdown-period, {{WRAPPER}} .premium-countdown-label, {{WRAPPER}} .flip-clock-label' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'premium_countdown_unit_typo',
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_SECONDARY,
				),
				'selector' => '{{WRAPPER}} .countdown-period, {{WRAPPER}} .premium-countdown-label, {{WRAPPER}} .flip-clock-label',
			)
		);

		$this->add_control(
			'premium_countdown_unit_backcolor',
			array(
				'label'     => __( 'Background Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .countdown-period span, {{WRAPPER}} .premium-countdown-label, {{WRAPPER}} .flip-unit' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'units_shadow',
				'selector' => '{{WRAPPER}} .countdown-period span, {{WRAPPER}} .flip-clock-label',

			)
		);

		$this->add_responsive_control(
			'units_margin',
			array(
				'label'      => __( 'Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .countdown-period, {{WRAPPER}} .premium-countdown-label, {{WRAPPER}} .flip-unit' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->add_responsive_control(
			'units_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .countdown-period span, {{WRAPPER}} .premium-countdown-label, {{WRAPPER}} .flip-unit' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'featured_unit_style',
			array(
				'label'     => __( 'Featured Unit', 'premium-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'style' => 'featured',
				),
			)
		);

		$this->add_control(
			'featured_digit_color',
			array(
				'label'     => __( 'Digit Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .countdown-section:first-child .countdown-amount' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'featured_digit_typo',
				'label'    => __( 'Digit Typography', 'premium-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .countdown-section:first-child .countdown-amount',
			)
		);

		$this->add_control(
			'featured_unit_color',
			array(
				'label'     => __( 'Unit Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .countdown-section:first-child .countdown-period' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'featured_unit_typo',
				'label'    => __( 'Unit Typography', 'premium-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .countdown-section:first-child .countdown-period',
			)
		);

		$this->add_responsive_control(
			'featured_unit_spacing',
			array(
				'label'      => __( 'Spacing', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .countdown-section:first-child .countdown-period' => 'margin-top: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_responsive_control(
			'featured_digit_padding',
			array(
				'label'      => __( 'Digit Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .countdown-section:first-child .countdown-amount' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'premium_countdown_separator_style',
			array(
				'label'      => __( 'Separator', 'premium-addons-for-elementor' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'conditions' => array(
					'terms' => array(
						array(
							'name'  => 'premium_countdown_separator',
							'value' => 'yes',
						),
						array(
							'relation' => 'or',
							'terms'    => array(
								array(
									'name'  => 'unit_position',
									'value' => 'block',
								),
								array(
									'name'  => 'unit_inside_circle',
									'value' => 'yes',
								),
							),
						),
					),
				),
			)
		);

		$this->add_control(
			'premium_countdown_separator_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_SECONDARY,
				),
				'selectors' => array(
					'{{WRAPPER}} .countdown_separator' => 'color: {{VALUE}};',
					'{{WRAPPER}} .countdown-separator-circle' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'premium_countdown_separator_size',
			array(
				'label'     => __( 'Size', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 1,
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .countdown_separator' => 'font-size: {{SIZE}}px;',
					'{{WRAPPER}} .countdown-separator-circle' => 'width: {{SIZE}}px; height: {{SIZE}}px;',
				),
			)
		);

		$this->add_responsive_control(
			'separator_spacing',
			array(
				'label'      => __( 'Spacing', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .countdown_separator' => 'margin-right: {{SIZE}}{{UNIT}}; margin-left: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'boxes_style',
			array(
				'label' => __( 'Boxes', 'premium-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'boxes_bg_color',
			array(
				'label'     => __( 'Background Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .countdown-section, {{WRAPPER}} .flip-unit-wrap' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'boxes_shadow',
				'selector' => '{{WRAPPER}} .countdown-section, {{WRAPPER}} .flip-unit-wrap',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'boxes_border',
				'selector' => '{{WRAPPER}} .countdown-section, {{WRAPPER}} .flip-unit-wrap',
			)
		);

		$this->add_control(
			'boxes_border_radius',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .countdown-section, {{WRAPPER}} .flip-unit-wrap' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'boxes_adv_radius!' => 'yes',
				),
			)
		);

		$this->add_control(
			'boxes_adv_radius',
			array(
				'label'       => __( 'Advanced Border Radius', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SWITCHER,
				'description' => __( 'Apply custom radius values. Get the radius value from ', 'premium-addons-for-elementor' ) . '<a href="https://9elements.github.io/fancy-border-radius/" target="_blank">here</a>' . __('. See ', 'premium-addons-for-elementor') . '<a href="https://www.youtube.com/watch?v=S0BJazLHV-M" target="_blank">tutorial</a>',
			)
		);

		$this->add_control(
			'boxes_adv_radius_value',
			array(
				'label'     => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'dynamic'   => array( 'active' => true ),
				'selectors' => array(
					'{{WRAPPER}} .countdown-section, {{WRAPPER}} .flip-unit-wrap' => 'border-radius: {{VALUE}};',
				),
				'condition' => array(
					'boxes_adv_radius' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'boxes_margin',
			array(
				'label'      => __( 'Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .countdown-section, {{WRAPPER}} .flip-unit-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'boxes_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .countdown-section, {{WRAPPER}} .flip-unit-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'premium_countdown_exp_message',
			array(
				'label'     => __( 'Expiration Message', 'premium-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'expiration_type' => 'text',
				),
			)
		);

		$this->add_control(
			'premium_countdown_message_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_SECONDARY,
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-countdown-exp-message' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'premium_countdown_message_bg_color',
			array(
				'label'     => __( 'Background Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-countdown-exp-message' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'premium_countdown_message_typo',
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				),
				'selector' => '{{WRAPPER}} .premium-countdown-exp-message',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'premium_countdown_message_border',
				'selector' => '{{WRAPPER}} .premium-countdown-exp-message',
			)
		);

		$this->add_control(
			'premium_countdown_message_border_radius',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-countdown-exp-message' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'premium_countdown_message_shadow',
				'selector' => '{{WRAPPER}} .premium-countdown-exp-message',
			)
		);

		$this->add_responsive_control(
			'premium_countdown_message_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-countdown-exp-message' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'premium_countdown_message_margin',
			array(
				'label'      => __( 'Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-countdown-exp-message' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Handles Evergreen Counter
	 *
	 * @since 4.3.9
	 * @access protected
	 */
	protected function get_evergreen_time() {

		$settings = $this->get_settings_for_display();

		if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$http_x_headers = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );

			$_SERVER['REMOTE_ADDR'] = $http_x_headers[0];
		}

		$ip_address = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

		$ip_address = ( '::1' === $ip_address ) ? '127.0.0.1' : $ip_address;

		$id = $this->get_id();

		$eve_days  = empty( $settings['premium_countdown_eve_days'] ) ? 0 : $settings['premium_countdown_eve_days'] * 24 * 60 * 60;
		$eve_hours = empty( $settings['premium_countdown_eve_hours'] ) ? 0 : $settings['premium_countdown_eve_hours'] * 60 * 60;
		$eve_min   = empty( $settings['premium_countdown_eve_min'] ) ? 0 : $settings['premium_countdown_eve_min'] * 60;

		$eve_interval = $eve_days + $eve_hours + $eve_min;

		$counter_key = 'premium_countdown_evergreen_' . $id;

		$evergreen_user = 'premium_evergreen_user_' . $ip_address;

		add_option( $counter_key, array() );

		$local_data = get_option( $counter_key, 'Null' );

		$local_due_date = isset( $local_data[ $evergreen_user ]['due_date'] ) ? $local_data[ $evergreen_user ]['due_date'] : 'Null';

		$local_interval = isset( $local_data[ $evergreen_user ]['interval'] ) ? $local_data[ $evergreen_user ]['interval'] : 'Null';

		if ( 'Null' === $local_due_date && 'Null' === $local_interval ) {
			return $this->handle_evergreen_counter( $counter_key, $evergreen_user, $eve_interval );
		}

		if ( 'Null' !== $local_due_date && intval( $local_interval ) !== $eve_interval ) {
			return $this->handle_evergreen_counter( $counter_key, $evergreen_user, $eve_interval );
		}

		if ( strtotime( $local_due_date->format( 'Y-m-d H:i:s' ) ) > 0 && intval( $local_interval ) === $eve_interval ) {
			return $local_due_date;
		}
	}

	/**
	 * Set/update Evergreen user Local Data.
	 *
	 * @param string $counter_key  evergreen/widget key.
	 * @param string $evergreen_user  evergreen user Key.
	 * @param number $eve_interval evergreen interval.
	 *
	 * @since 4.3.9
	 * @access protected
	 *
	 * @return object $end_time
	 */
	protected function handle_evergreen_counter( $counter_key, $evergreen_user, $eve_interval ) {

		$end_time = new DateTime( 'GMT' );

		$end_time->setTime( $end_time->format( 'H' ) + 2, $end_time->format( 'i' ), $end_time->format( 's' ) + $eve_interval );

		$local_data = get_option( $counter_key, 'Null' );

		$local_data[ $evergreen_user ]['due_date'] = $end_time;
		$local_data[ $evergreen_user ]['interval'] = $eve_interval;

		update_option( $counter_key, $local_data );

		return $end_time;
	}


	/**
	 * Render Countdown widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {

		$settings = $this->get_settings_for_display();

		$papro_activated = apply_filters( 'papro_activated', false );

		if ( ! $papro_activated || version_compare( PREMIUM_PRO_ADDONS_VERSION, '2.9.14', '<' ) ) {

			if ( in_array( $settings['style'], array( 'circle', 'flipping', 'rotate' ), true ) ) {

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

		$timer_type = $settings['premium_countdown_type'];

		$reset = '';

		$is_edit_mode = \Elementor\Plugin::$instance->editor->is_edit_mode();

		if ( 'evergreen' === $timer_type && 'yes' === $settings['premium_countdown_eve_reset'] ) {

			$transient_name = 'premium_evergreen_reset_' . $id;

			if ( false === get_transient( $transient_name ) ) {

				delete_option( 'premium_countdown_evergreen_' . $id );

				$reset = true;

				$reset_hours = empty( $settings['premium_countdown_eve_reset_hours'] ) ? 0 : $settings['premium_countdown_eve_reset_hours'] * HOUR_IN_SECONDS;
				$reset_min   = empty( $settings['premium_countdown_eve_reset_min'] ) ? 0 : $settings['premium_countdown_eve_reset_min'] * MINUTE_IN_SECONDS;

				$expire_time = $reset_hours + $reset_min;

				if ( ! $is_edit_mode && 0 !== $expire_time ) {
					set_transient( $transient_name, 'DEFAULT', $expire_time );
				}
			}
		}

		$target_date = 'evergreen' === $timer_type ? $this->get_evergreen_time() : str_replace( '-', '/', $settings['premium_countdown_date_time'] );

		$event = $settings['expiration_type'];
		$text  = '';
		if ( 'url' === $event ) {
			$text = esc_url( $settings['expiration_url'] );
		} elseif ( 'restart' === $event ) {

			if ( $is_edit_mode ) {
				$last_target = $target_date;
			} else {
				$last_target = get_option( 'pa_countdown_target' . $id, false );
			}

			if ( ! $last_target ) {
				$last_target = $target_date;
			}

			$is_date_passed = strtotime( $last_target ) < time();

			if ( $is_date_passed ) {

				$current_time = new DateTime();

				$current_time->modify( '+' . $settings['restart_days'] . ' day' );
				$current_time->modify( '+' . $settings['restart_hours'] . ' hours' );
				$current_time->modify( '+' . $settings['restart_minutes'] . ' minutes' );

				$target_date = str_replace( '-', '/', $current_time->format( 'Y-m-d H:i:s' ) );

			} else {
				$target_date = $last_target;
			}

			update_option( 'pa_countdown_target' . $id, $target_date );
		}

		// Used to sync time with WordPress.
		$sent_time = '';
		if ( 'wp-time' === $settings['timezone'] ) {
			$sent_time = str_replace( '-', '/', current_time( 'mysql' ) );
		}

		if ( 'flipping' !== $settings['style'] ) {

			$formats = $settings['premium_countdown_units'];
			$format  = implode( '', $formats );

			// Singular labels set up.
			$y     = ! empty( $settings['premium_countdown_year_singular'] ) ? $settings['premium_countdown_year_singular'] : 'Year';
			$m     = ! empty( $settings['premium_countdown_month_singular'] ) ? $settings['premium_countdown_month_singular'] : 'Month';
			$w     = 'Week';
			$d     = ! empty( $settings['premium_countdown_day_singular'] ) ? $settings['premium_countdown_day_singular'] : 'Day';
			$h     = ! empty( $settings['premium_countdown_hour_singular'] ) ? $settings['premium_countdown_hour_singular'] : 'Hour';
			$mi    = ! empty( $settings['premium_countdown_minute_singular'] ) ? $settings['premium_countdown_minute_singular'] : 'Minute';
			$s     = ! empty( $settings['premium_countdown_second_singular'] ) ? $settings['premium_countdown_second_singular'] : 'Second';
			$label = $y . ',' . $m . ',' . $w . ',' . $d . ',' . $h . ',' . $mi . ',' . $s;

			// Plural labels set up.
			$ys      = ! empty( $settings['premium_countdown_year_plural'] ) ? $settings['premium_countdown_year_plural'] : 'Years';
			$ms      = ! empty( $settings['premium_countdown_month_plural'] ) ? $settings['premium_countdown_month_plural'] : 'Months';
			$ws      = 'Weeks';
			$ds      = ! empty( $settings['premium_countdown_day_plural'] ) ? $settings['premium_countdown_day_plural'] : 'Days';
			$hs      = ! empty( $settings['premium_countdown_hour_plural'] ) ? $settings['premium_countdown_hour_plural'] : 'Hours';
			$mis     = ! empty( $settings['premium_countdown_minute_plural'] ) ? $settings['premium_countdown_minute_plural'] : 'Minutes';
			$ss      = ! empty( $settings['premium_countdown_second_plural'] ) ? $settings['premium_countdown_second_plural'] : 'Seconds';
			$labels1 = $ys . ',' . $ms . ',' . $ws . ',' . $ds . ',' . $hs . ',' . $mis . ',' . $ss;

			$countdown_settings = array(
				// 'single'     => esc_html( $label ),
				// 'plural'     => esc_html( $labels1 ),
				'until'      => $target_date,
				'serverSync' => $sent_time,
				'format'     => $format,
				'event'      => $event,
				'changeTo'   => $settings['change_digits'],
				'text'       => $text,
				'separator'  => 'custom' !== $settings['premium_countdown_separator_text'] ? $settings['premium_countdown_separator_text'] : $settings['custom_separator'],
				'timerType'  => $timer_type,
				'unitsPos'   => $settings['unit_position'],
				'reset'      => $reset,
				'style'      => $settings['style'],
			);

			if ( 'featured' === $settings['style'] ) {
				$countdown_settings['featuredUnit'] = $settings['featured_unit'];
			}

			$this->add_render_attribute( 'inner_counter', 'class', 'premium-addons__v-hidden' );

		} else {

			$countdown_settings = array(
				'until'      => $target_date,
				'serverSync' => $sent_time,
				'event'      => $event,
				'text'       => $text,
				'timerType'  => $timer_type,
				'separator'  => 'custom' !== $settings['premium_countdown_separator_text'] ? $settings['premium_countdown_separator_text'] : $settings['custom_separator'],
				'reset'      => $reset,
				'style'      => $settings['style'],
				'lang'       => $settings['flip_language'],
			);

		}

		$this->add_render_attribute(
			'container',
			array(
				'class'         => array( 'premium-countdown', 'premium-countdown-separator-' . esc_attr( $settings['premium_countdown_separator'] ) ),
				'data-settings' => wp_json_encode( $countdown_settings ),
			)
		);

		$this->add_render_attribute(
			'inner_counter',
			array(
				'class' => array(
					'countdown',
					'premium-countdown-init',
					'premium-countdown-' . $settings['style'],
				),
			)
		);

		?>
		<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'container' ) ); ?>>
			<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'inner_counter' ) ); ?>></div>

			<?php if ( 'text' === $event ) : ?>

				<div class="premium-countdown-exp-message premium-addons__v-hidden">
					<?php $this->print_text_editor( $settings['expiration_text'] ); ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
