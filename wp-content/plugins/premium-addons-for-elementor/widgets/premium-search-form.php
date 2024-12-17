<?php
/**
 * Premium Search Form.
 */

namespace PremiumAddons\Widgets;

// Elementor Classes.
use Elementor\Plugin;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;

// PremiumAddons Classes.
use PremiumAddons\Includes\Premium_Template_Tags as Blog_Helper;
use PremiumAddons\Includes\Helper_Functions;
use PremiumAddons\Includes\Controls\Premium_Post_Filter;
use PremiumAddons\Includes\Controls\Premium_Tax_Filter;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // If this file is called directly, abort.
}

/**
 * Class Premium_Search_Form
 */
class Premium_Search_Form extends Widget_Base {


	/**
	 * Retrieve Widget Name.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function get_name() {
		return 'premium-search-form';
	}

	/**
	 * Retrieve Widget Title.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function get_title() {
		return __( 'Search Form', 'premium-addons-for-elementor' );
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
		return 'pa-search-form';
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
		return array( 'pa', 'premium', 'premium search form', 'ajax' );
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
		return array(
			'pa-slick',
			'premium-addons',
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
	 * Register Search Form controls.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {  // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore

		$papro_activated = apply_filters( 'papro_activated', false );

		$this->start_controls_section(
			'query_section',
			array(
				'label' => __( 'Query', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'query_type',
			array(
				'label'       => __( 'Query Type', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'post'     => __( 'Post Type (AJAX)', 'premium-addons-for-elementor' ),
					'elements' => apply_filters( 'pa_pro_label', __( 'Elements On Page (Pro)', 'premium-addons-for-elementor' ) ),
				),
				'default'     => 'post',
				'label_block' => true,
			)
		);

		$this->add_control(
			'selector',
			array(
				'label'       => __( 'CSS Selector', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'description' => __( 'Add the CSS selector of the parent container that contains the queried elements.', 'premium-addons-for-elementor' ),
				'label_block' => true,
				'condition'   => array(
					'query_type' => 'elements',
				),
			)
		);

        $this->add_control(
			'fadeout_selector',
			array(
				'label'       => __( 'Elements to Fade Out Selector', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'description' => __( 'Add the CSS selector of the elements to fade out.', 'premium-addons-for-elementor' ),
				'label_block' => true,
				'condition'   => array(
					'query_type' => 'elements',
				),
			)
		);

        $this->add_control(
			'highlighted_text_color',
			array(
				'label'     => __( 'Highlighted Text Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'.pa-highlighted-text-{{ID}}' => 'background-color: {{VALUE}};',
				),
                'condition'   => array(
					'query_type' => 'elements',
				),
			)
		);

		if ( ! $papro_activated ) {

			$get_pro = Helper_Functions::get_campaign_link( 'https://premiumaddons.com/pro', 'editor-page', 'wp-editor', 'get-pro' );

			$this->add_control(
				'query_notice',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => __( 'This option is available in Premium Addons Pro. ', 'premium-addons-for-elementor' ) . '<a href="' . esc_url( $get_pro ) . '" target="_blank">' . __( 'Upgrade now!', 'premium-addons-for-elementor' ) . '</a>',
					'content_classes' => 'papro-upgrade-notice',
					'condition'       => array(
						'query_type' => 'elements',
					),
				)
			);

		}

		$post_types = Blog_Helper::get_posts_types();

		$this->add_control(
			'custom_search_query',
			array(
				'label'     => __( 'Custom Search Query', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => array(
					'query_type' => 'post',
				),
			)
		);

		$this->add_control(
			'type_select',
			array(
				'label'     => __( 'Post Type Select', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => array(
					'query_type'           => 'post',
					'custom_search_query!' => 'yes',
				),
			)
		);

		$this->add_control(
			'show_posts_number',
			array(
				'label'     => __( 'Show Posts Number', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => array(
					'query_type'           => 'post',
					'type_select'          => 'yes',
					'custom_search_query!' => 'yes',
				),
			)
		);

		$this->add_control(
			'post_types_excluded',
			array(
				'label'       => __( 'Excluded Post Types', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple'    => true,
				'options'     => $post_types,
				'default'     => array(),
				'condition'   => array(
					'query_type'           => 'post',
					'type_select'          => 'yes',
					'custom_search_query!' => 'yes',
				),
			)
		);

		$this->add_control(
			'select_field_position',
			array(
				'label'        => __( 'Select Field Position', 'premium-addons-for-elementor' ),
				'label_block'  => true,
				'type'         => Controls_Manager::SELECT,
				'options'      => array(
					'after'  => __( 'After Field', 'premium-addons-for-elementor' ),
					'before' => __( 'Before Field', 'premium-addons-for-elementor' ),
				),
				'default'      => 'before',
				'prefix_class' => 'premium-search__select-',
				'condition'    => array(
					'query_type'           => 'post',
					'type_select'          => 'yes',
					'custom_search_query!' => 'yes',
				),
			)
		);

		$this->add_control(
			'post_type_filter',
			array(
				'label'       => __( 'Source', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'label_block' => true,
				'options'     => $post_types,
				'default'     => 'post',
				'condition'   => array(
					'custom_search_query' => 'yes',
					'query_type'          => 'post',
				),
			)
		);

		foreach ( $post_types as $key => $type ) {

			// Get all the taxanomies associated with the selected post type.
			$taxonomy = Blog_Helper::get_taxnomies( $key );

			if ( ! empty( $taxonomy ) ) {

				// Get all taxonomy values under the taxonomy.
				foreach ( $taxonomy as $index => $tax ) {

					$terms = get_terms( array(
                        'taxonomy'  => $index,
                        'hide_empty' => false,
                    ));

					$related_tax = array();

					if ( ! empty( $terms ) ) {

						foreach ( $terms as $t_index => $t_obj ) {

							$related_tax[ $t_obj->slug ] = $t_obj->name;
						}

						// Add filter rule for the each taxonomy.
						$this->add_control(
							$index . '_' . $key . '_filter_rule',
							array(
								/* translators: %s Taxnomy Label */
								'label'       => sprintf( __( '%s Filter Rule', 'premium-addons-for-elementor' ), $tax->label ),
								'type'        => Controls_Manager::SELECT,
								'default'     => 'IN',
								'label_block' => true,
								'options'     => array(
									/* translators: %s: Taxnomy Label */
									'IN'     => sprintf( __( 'Match %s', 'premium-addons-for-elementor' ), $tax->label ),
									/* translators: %s: Taxnomy Label */
									'NOT IN' => sprintf( __( 'Exclude %s', 'premium-addons-for-elementor' ), $tax->label ),
								),
								'condition'   => array(
									'query_type'          => 'post',
									'custom_search_query' => 'yes',
									'post_type_filter'    => $key,
								),
							)
						);

						// Add select control for each taxonomy.
						$this->add_control(
							'tax_' . $index . '_' . $key . '_filter',
							array(
								/* translators: %s Taxnomy Label */
								'label'       => sprintf( __( '%s Filter', 'premium-addons-for-elementor' ), $tax->label ),
								'type'        => Controls_Manager::SELECT2,
								'default'     => '',
								'multiple'    => true,
								'label_block' => true,
								'options'     => $related_tax,
								'condition'   => array(
									'query_type'          => 'post',
									'custom_search_query' => 'yes',
									'post_type_filter'    => $key,
								),
								'separator'   => 'after',
							)
						);

					}
				}
			}
		}

		$this->add_control(
			'author_filter_rule',
			array(
				'label'       => __( 'Filter By Author Rule', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'author__in'     => __( 'Match Authors', 'premium-addons-for-elementor' ),
					'author__not_in' => __( 'Exclude Authors', 'premium-addons-for-elementor' ),
				),
				'default'     => 'author__in',
				'separator'   => 'before',
				'label_block' => true,
				'condition'   => array(
					'query_type'          => 'post',
					'custom_search_query' => 'yes',
				),
			)
		);

		$this->add_control(
			'premium_blog_users',
			array(
				'label'       => __( 'Authors', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT2,
				'options'     => Blog_Helper::get_authors(),
				'label_block' => true,
				'multiple'    => true,
				'condition'   => array(
					'query_type'          => 'post',
					'custom_search_query' => 'yes',
				),
			)
		);

		$this->add_control(
			'posts_filter_rule',
			array(
				'label'       => __( 'Filter By Post Rule', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'post__in'     => __( 'Match Post', 'premium-addons-for-elementor' ),
					'post__not_in' => __( 'Exclude Post', 'premium-addons-for-elementor' ),
				),
				'default'     => 'post__not_in',
				'separator'   => 'before',
				'label_block' => true,
				'condition'   => array(
					'query_type'          => 'post',
					'custom_search_query' => 'yes',
				),
			)
		);

		$this->add_control(
			'premium_blog_posts_exclude',
			array(
				'label'       => __( 'Posts', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple'    => true,
				'options'     => Blog_Helper::get_default_posts_list( 'post' ),
				'condition'   => array(
					'post_type_filter'    => 'post',
					'query_type'          => 'post',
					'custom_search_query' => 'yes',
				),
			)
		);

		$this->add_control(
			'custom_posts_filter',
			array(
				'label'              => __( 'Posts', 'premium-addons-for-elementor' ),
				'type'               => Premium_Post_Filter::TYPE,
				'render_type'        => 'template',
				'label_block'        => true,
				'multiple'           => true,
				'frontend_available' => true,
				'condition'          => array(
					'post_type_filter!'   => 'post',
					'query_type'          => 'post',
					'custom_search_query' => 'yes',
				),

			)
		);

		$this->add_control(
			'premium_blog_number_of_posts',
			array(
				'label'       => __( 'Posts Per Page', 'premium-addons-for-elementor' ),
				'description' => __( 'Set the number of per page', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 1,
				'default'     => 4,
				'condition'   => array(
					'query_type' => 'post',
				),
			)
		);

		$this->add_control(
			'show_results_number',
			array(
				'label'     => __( 'Show Results Number', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => array(
					'query_type' => 'post',
				),
			)
		);

		$this->add_control(
			'results_number_text',
			array(
				'label'       => __( 'Results Number Text', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'Results: {{number}}',
				'description' => __( 'This helps to control number of results string. {{number}} will be repalced with the number of results', 'premium-addons-for-elementor' ),
				'condition'   => array(
					'query_type'          => 'post',
					'show_results_number' => 'yes',
				),
			)
		);

		$this->add_control(
			'premium_blog_order_by',
			array(
				'label'       => __( 'Order By', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'label_block' => true,
				'options'     => array(
					'none'          => __( 'None', 'premium-addons-for-elementor' ),
					'ID'            => __( 'ID', 'premium-addons-for-elementor' ),
					'author'        => __( 'Author', 'premium-addons-for-elementor' ),
					'title'         => __( 'Title', 'premium-addons-for-elementor' ),
					'name'          => __( 'Name', 'premium-addons-for-elementor' ),
					'date'          => __( 'Date', 'premium-addons-for-elementor' ),
					'modified'      => __( 'Last Modified', 'premium-addons-for-elementor' ),
					'rand'          => __( 'Random', 'premium-addons-for-elementor' ),
					'menu_order'    => __( 'Menu Order', 'premium-addons-for-elementor' ),
					'comment_count' => __( 'Number of Comments', 'premium-addons-for-elementor' ),
				),
				'default'     => 'date',
				'condition'   => array(
					'query_type' => 'post',
				),
			)
		);

		$this->add_control(
			'premium_blog_order',
			array(
				'label'       => __( 'Order', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'label_block' => true,
				'options'     => array(
					'DESC' => __( 'Descending', 'premium-addons-for-elementor' ),
					'ASC'  => __( 'Ascending', 'premium-addons-for-elementor' ),
				),
				'default'     => 'DESC',
				'condition'   => array(
					'query_type' => 'post',
				),
			)
		);

		$this->add_control(
			'empty_query_text',
			array(
				'label'       => __( 'Empty Query Text', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'condition'   => array(
					'query_type' => 'post',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'search_section',
			array(
				'label' => __( 'Search Field', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_responsive_control(
			'search_width',
			array(
				'label'      => __( 'Width', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%', 'custom' ),
				'range'      => array(
					'px' => array(
						'min' => 1,
						'max' => 500,
					),
					'em' => array(
						'min' => 1,
						'max' => 30,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__input-wrap'    => 'width: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_responsive_control(
			'search_height',
			array(
				'label'      => __( 'Height', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'custom' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__input-wrap'    => 'height: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_control(
			'show_label',
			array(
				'label'      => __( 'Show Label', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SWITCHER,
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'     => 'type_select',
							'operator' => '!==',
							'value'    => 'yes',
						),
						array(
							'terms' => array(
								array(
									'name'  => 'type_select',
									'value' => 'yes',
								),
								array(
									'name'  => 'select_field_position',
									'value' => 'after',
								),
							),
						),
					),
				),
			)
		);

		$this->add_control(
			'label_text',
			array(
				'label'       => __( 'Label Text', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Search Field', 'premium-addons-for-elementor' ),
				'label_block' => true,
				'conditions'  => array(
					'terms' => array(
						array(
							'name'  => 'show_label',
							'value' => 'yes',
						),
						array(
							'relation' => 'or',
							'terms'    => array(
								array(
									'name'     => 'type_select',
									'operator' => '!==',
									'value'    => 'yes',
								),
								array(
									'terms' => array(
										array(
											'name'  => 'type_select',
											'value' => 'yes',
										),
										array(
											'name'  => 'select_field_position',
											'value' => 'after',
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
			'placeholder_text',
			array(
				'label'       => __( 'Placeholder Text', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Looking for?', 'premium-addons-for-elementor' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'field_effects',
			array(
				'label'        => __( 'Fields Focus Effect', 'premium-addons-for-elementor' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => '',
				'options'      => array(
					''               => __( 'None', 'premium-addons-for-elementor' ),
					'label'          => apply_filters( 'pa_pro_label', __( 'Label Position (Pro)', 'premium-addons-for-elementor' ) ),
					'label-letter'   => apply_filters( 'pa_pro_label', __( 'Label Letter Spacing (Pro)', 'premium-addons-for-elementor' ) ),
					'label-pos-back' => apply_filters( 'pa_pro_label', __( 'Label Position + Background (Pro)', 'premium-addons-for-elementor' ) ),
					'css-filters'    => apply_filters( 'pa_pro_label', __( 'Label CSS Filters (Pro)', 'premium-addons-for-elementor' ) ),
				),
				'prefix_class' => 'premium-search-anim-',
				'label_block'  => true,
				'conditions'   => array(
					'terms' => array(
						array(
							'name'  => 'show_label',
							'value' => 'yes',
						),
						array(
							'relation' => 'or',
							'terms'    => array(
								array(
									'name'     => 'type_select',
									'operator' => '!==',
									'value'    => 'yes',
								),
								array(
									'terms' => array(
										array(
											'name'  => 'type_select',
											'value' => 'yes',
										),
										array(
											'name'  => 'select_field_position',
											'value' => 'after',
										),
									),
								),
							),
						),
					),
				),
			)
		);

		if ( $papro_activated ) {
			do_action( 'pa_search_effects_options', $this );
		}

		$this->end_controls_section();

		$this->start_controls_section(
			'search_button_section',
			array(
				'label' => __( 'Search Button', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'search_button',
			array(
				'label' => __( 'Search Button', 'premium-addons-for-elementor' ),
				'type'  => Controls_Manager::SWITCHER,
			)
		);

		$this->add_responsive_control(
			'button_width',
			array(
				'label'      => __( 'Width', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%', 'custom' ),
				'range'      => array(
					'px' => array(
						'min' => 1,
						'max' => 500,
					),
					'em' => array(
						'min' => 1,
						'max' => 30,
					),
				),
				'condition'  => array(
					'search_button' => 'yes',
				),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__btn-wrap'    => 'width: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_control(
			'button_action',
			array(
				'label'       => __( 'Action', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'onpage'   => __( 'On Page Search', 'premium-addons-for-elementor' ),
					'redirect' => __( 'Go to Search Page', 'premium-addons-for-elementor' ),
				),
				'default'     => 'onpage',
				'label_block' => true,
				'condition'   => array(
					'search_button' => 'yes',
				),
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'       => __( 'Text', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Find', 'premium-addons-for-elementor' ),
				'label_block' => true,
				'condition'   => array(
					'search_button' => 'yes',
				),
			)
		);

		$this->add_control(
			'search_page_link',
			array(
				'label'       => __( 'Search Page Link', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'condition'   => array(
					'search_button' => 'yes',
					'button_action' => 'redirect',
				),
			)
		);

		$this->add_control(
			'search_icon',
			array(
				'label'     => __( 'Search Icon', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => array(
					'search_button' => 'yes',
				),
			)
		);

		$this->add_control(
			'search_icon_select',
			array(
				'label'       => __( 'Select Icon', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::ICONS,
				'default'     => array(
					'value'   => 'fas fa-search',
					'library' => 'fa-solid',
				),
				'recommended' => array(
					'fa-solid' => array(
						'search',
						'search-dollar',
						'search-location',
						'search-plus',
					),
				),
				// 'exclude_inline_options' => array( 'svg' ),
				'condition'   => array(
					'search_button' => 'yes',
					'search_icon'   => 'yes',
				),
			)
		);

		$this->add_control(
			'icon_position',
			array(
				'label'     => __( 'Icon Position', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'row'         => array(
						'title' => __( 'Left', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-arrow-left',
					),
					'row-reverse' => array(
						'title' => __( 'Right', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-arrow-right',
					),
				),
				'default'   => 'row-reverse',
				'toggle'    => false,
				'condition' => array(
					'search_button' => 'yes',
					'search_icon'   => 'yes',
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-search__btn' => 'flex-direction:{{VALUE}}',
				),
			)
		);

		$this->add_responsive_control(
			'icon_size',
			array(
				'label'     => __( 'Icon Size', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'condition' => array(
					'search_button' => 'yes',
					'search_icon'   => 'yes',
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-search__btn svg' => 'width: {{SIZE}}px !important; height: {{SIZE}}px !important',
                    '{{WRAPPER}} .premium-search__btn i' => 'font-size: {{SIZE}}px',
				),
			)
		);

		$this->add_responsive_control(
			'icon_spacing',
			array(
				'label'      => __( 'Icon Spacing', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__btn' => 'column-gap: {{SIZE}}{{UNIT}};',
				),
				'default'    => array(
					'size' => 10,
					'unit' => 'px',
				),
				'condition'  => array(
					'search_button' => 'yes',
					'search_icon'   => 'yes',
				),
			)
		);

		$this->add_control(
			'button_position',
			array(
				'label'       => __( 'Button Position', 'premium-addons-for-elementor' ),
				'label_block' => true,
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'row-reverse' => __( 'Before', 'premium-addons-for-elementor' ),
					'row'         => __( 'After', 'premium-addons-for-elementor' ),
				),
				'default'     => 'row',
				'selectors'   => array(
					'{{WRAPPER}} .premium-search__input-btn-wrap' => 'flex-direction: {{VALUE}}',
				),
				'condition'   => array(
					'search_button' => 'yes',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'display_options_section',
			array(
				'label'     => __( 'Display Options', 'premium-addons-for-elementor' ),
				'condition' => array(
					'query_type' => 'post',
				),
			)
		);

		$this->add_control(
			'hide_on_click',
			array(
				'label'     => __( 'Hide Results on Click Outside', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => array(
					'query_type' => 'post',
				),
			)
		);

		$this->add_control(
			'skin',
			array(
				'label'       => __( 'Skin', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'classic' => __( 'Classic', 'premium-addons-for-elementor' ),
					'side'    => __( 'On Side', 'premium-addons-for-elementor' ),
					'banner'  => __( 'Banner', 'premium-addons-for-elementor' ),
				),
				'default'     => 'classic',
				'label_block' => true,
			)
		);

		$this->add_control(
			'image_notice',
			array(
				'raw'             => __( 'You need to set image width for this skin from style tab -> Thumbnail', 'premium-addons-for-elementor' ),
				'type'            => Controls_Manager::RAW_HTML,
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
				'condition'       => array(
					'skin' => 'side',
				),
			)
		);

		$this->add_responsive_control(
			'columns_number',
			array(
				'label'          => __( 'Number of Columns', 'premium-addons-for-elementor' ),
				'type'           => Controls_Manager::SELECT,
				'options'        => array(
					'100%'   => __( '1 Column', 'premium-addons-for-elementor' ),
					'50%'    => __( '2 Columns', 'premium-addons-for-elementor' ),
					'33.33%' => __( '3 Columns', 'premium-addons-for-elementor' ),
					'25%'    => __( '4 Columns', 'premium-addons-for-elementor' ),
					'20%'    => __( '5 Columns', 'premium-addons-for-elementor' ),
					'16.66%' => __( '6 Columns', 'premium-addons-for-elementor' ),
				),
				'default'        => '50%',
				'tablet_default' => '50%',
				'mobile_default' => '100%',
				'render_type'    => 'template',
				'label_block'    => true,
				'selectors'      => array(
					'{{WRAPPER}} .premium-search__post-wrap' => 'width: {{VALUE}}',
					'{{WRAPPER}}' => '--pa-search-carousel-slides: {{VALUE}}',
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
						'max' => 50,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-search__posts-wrap' => 'margin-left: calc( -{{SIZE}}{{UNIT}}/2 ); margin-right: calc( -{{SIZE}}{{UNIT}}/2 );',
					'{{WRAPPER}} .premium-search__post-wrap' => 'padding-right: calc( {{SIZE}}{{UNIT}}/2 ); padding-left: calc( {{SIZE}}{{UNIT}}/2 )',
				),
			)
		);

		$this->add_responsive_control(
			'row_spacing',
			array(
				'label'      => __( 'Rows Spacing', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 1,
						'max' => 200,
					),
				),
				'default'    => array(
					'size' => 10,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__post-wrap' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_control(
			'show_post_thumbnail',
			array(
				'label'   => __( 'Show Thumbnail', 'premium-addons-for-elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'premium_blog_title_tag',
			array(
				'label'       => __( 'Title HTML Tag', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'h2',
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
			)
		);

		$this->add_control(
			'show_excerpt',
			array(
				'label'   => __( 'Show Excerpt', 'premium-addons-for-elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'excerpt_length',
			array(
				'label'     => __( 'Excerpt Length', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 22,
				'condition' => array(
					'show_excerpt' => 'yes',
				),
			)
		);

        $this->add_control(
			'excerpt_length_apply',
			array(
				'label'        => __( 'Apply On Posts With Excerpt Set', 'premium-addons-for-elementor' ),
                'description'        => __( 'Enable this option if you want the excerpt length to be also applied on posts with the Excerpt field set.', 'premium-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'condition'    => array(
					'show_excerpt' => 'yes',
				),
			)
		);

		$this->add_control(
			'excerpt_type',
			array(
				'label'       => __( 'Excerpt Type', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'dots' => __( 'Dots', 'premium-addons-for-elementor' ),
					'link' => __( 'Read More', 'premium-addons-for-elementor' ),
				),
				'default'     => 'dots',
				'label_block' => true,
				'condition'   => array(
					'show_excerpt' => 'yes',
				),
			)
		);

		$this->add_control(
			'excerpt_text',
			array(
				'label'     => __( 'Read More Text', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Read More »', 'premium-addons-for-elementor' ),
				'condition' => array(
					'show_excerpt' => 'yes',
					'excerpt_type' => 'link',
				),
			)
		);

		$this->add_control(
			'link_box',
			array(
				'label'        => __( 'Link Whole Box', 'premium-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'prefix_class' => 'premium-search__whole-link-',
				'render_type'  => 'template',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'new_tab',
			array(
				'label'   => __( 'Open Links in New Tab', 'premium-addons-for-elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'remove_button',
			array(
				'label'     => __( 'Remove Keyword Button', 'premium-addons-for-elementor' ),
				'condition' => array(
					'carousel!' => 'yes',
				),
			)
		);

		$this->add_control(
			'remove_button_switcher',
			array(
				'label' => __( 'Enable Remove Button', 'premium-addons-for-elementor' ),
				'type'  => Controls_Manager::SWITCHER,
			)
		);

		if ( ! $papro_activated ) {

			$get_pro = Helper_Functions::get_campaign_link( 'https://premiumaddons.com/pro', 'editor-page', 'wp-editor', 'get-pro' );

			$this->add_control(
				'keyword_remove_notice',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => __( 'This option is available in Premium Addons Pro. ', 'premium-addons-for-elementor' ) . '<a href="' . esc_url( $get_pro ) . '" target="_blank">' . __( 'Upgrade now!', 'premium-addons-for-elementor' ) . '</a>',
					'content_classes' => 'papro-upgrade-notice',
					'condition'       => array(
						'remove_button_switcher' => 'yes',
					),
				)
			);

		}

		$this->end_controls_section();

		$this->start_controls_section(
			'pagination_section',
			array(
				'label'     => __( 'Pagination', 'premium-addons-for-elementor' ),
				'condition' => array(
					'query_type' => 'post',
					'carousel!'  => 'yes',
				),
			)
		);

		$this->add_control(
			'premium_blog_paging',
			array(
				'label' => __( 'Enable Pagination', 'premium-addons-for-elementor' ),
				'type'  => Controls_Manager::SWITCHER,
			)
		);

		$this->add_control(
			'max_pages',
			array(
				'label'     => __( 'Page Limit', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 5,
				'condition' => array(
					'premium_blog_paging' => 'yes',
				),
			)
		);

		$this->add_control(
			'pagination_strings',
			array(
				'label'     => __( 'Enable Pagination Next/Prev Strings', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => array(
					'premium_blog_paging' => 'yes',
				),
			)
		);

		$this->add_control(
			'premium_blog_prev_text',
			array(
				'label'     => __( 'Previous Page String', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Previous', 'premium-addons-for-elementor' ),
				'condition' => array(
					'premium_blog_paging' => 'yes',
					'pagination_strings'  => 'yes',
				),
			)
		);

		$this->add_control(
			'premium_blog_next_text',
			array(
				'label'     => __( 'Next Page String', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Next', 'premium-addons-for-elementor' ),
				'condition' => array(
					'premium_blog_paging' => 'yes',
					'pagination_strings'  => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'pagination_align',
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
				'default'   => 'right',
				'toggle'    => false,
				'condition' => array(
					'premium_blog_paging' => 'yes',
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-search-form__pagination-container' => 'text-align: {{VALUE}}',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'premium_blog_carousel_settings',
			array(
				'label'     => __( 'Carousel', 'premium-addons-for-elementor' ),
				'condition' => array(
					'query_type'           => 'post',
					'premium_blog_paging!' => 'yes',
				),
			)
		);

		$this->add_control(
			'carousel',
			array(
				'label' => __( 'Enable Carousel', 'premium-addons-for-elementor' ),
				'type'  => Controls_Manager::SWITCHER,
			)
		);

		$this->add_control(
			'carousel_fade',
			array(
				'label'     => __( 'Fade', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => array(
					'carousel'       => 'yes',
					'columns_number' => '100%',
				),
			)
		);

		$this->add_control(
			'slides_to_scroll',
			array(
				'label'     => __( 'Slides To Scroll', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::NUMBER,
				'condition' => array(
					'carousel' => 'yes',
				),
			)
		);

		$this->add_control(
			'rows',
			array(
				'label'       => __( 'Rows', 'premium-addons-for-elementor' ),
				'description' => __( '  How many rows should each slide have.', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 1,
				'condition'   => array(
					'carousel' => 'yes',
				),
			)
		);

		$this->add_control(
			'carousel_speed',
			array(
				'label'       => __( 'Transition Speed (ms)', 'premium-addons-for-elementor' ),
				'description' => __( 'Set the speed of the carousel animation in milliseconds (ms)', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 300,
				'render_type' => 'template',
				'selectors'   => array(
					'{{WRAPPER}} .premium-blog-wrap .slick-slide' => 'transition: all {{VALUE}}ms !important',
				),
				'condition'   => array(
					'carousel' => 'yes',
				),
			)
		);

		$this->add_control(
			'carousel_center',
			array(
				'label'     => __( 'Center Mode', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => array(
					'carousel' => 'yes',
				),
			)
		);

		$this->add_control(
			'carousel_spacing',
			array(
				'label'       => __( 'Slides\' Spacing', 'premium-addons-for-elementor' ),
				'description' => __( 'Set a spacing value in pixels (px)', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => '15',
				'condition'   => array(
					'carousel'        => 'yes',
					'carousel_center' => 'yes',
				),
			)
		);

		$this->add_control(
			'carousel_dots',
			array(
				'label'     => __( 'Navigation Dots', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => array(
					'carousel' => 'yes',
				),
			)
		);

		$this->add_control(
			'carousel_arrows',
			array(
				'label'     => __( 'Navigation Arrows', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => array(
					'carousel' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'arrows_vposition',
			array(
				'label'        => __( 'Vertical Position', 'premium-addons-for-elementor' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => array(
					'top'    => array(
						'title' => __( 'Top', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-arrow-up',
					),
					'middle' => array(
						'title' => __( 'Center', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-text-align-justify',
					),
					'bottom' => array(
						'title' => __( 'Bottom', 'premium-addons-for-elementor' ),
						'icon'  => 'eicon-arrow-down',
					),
				),
				'default'      => 'middle',
				'toggle'       => false,
				'prefix_class' => 'premium-search__arrow-',
				'condition'    => array(
					'carousel'        => 'yes',
					'carousel_arrows' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'carousel_prev_arrow_pos',
			array(
				'label'      => __( 'Previous Arrow Position', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%' ),
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
				'condition'  => array(
					'carousel'        => 'yes',
					'carousel_arrows' => 'yes',
				),
				'selectors'  => array(
					// '{{WRAPPER}} .premium-blog-wrap a.carousel-arrow.carousel-next' => 'right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .premium-search__query-wrap a.carousel-arrow.carousel-prev' => 'left: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'carousel_next_arrow_pos',
			array(
				'label'      => __( 'Previous Arrow Position', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%' ),
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
				'condition'  => array(
					'carousel'        => 'yes',
					'carousel_arrows' => 'yes',
				),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__query-wrap a.carousel-arrow.carousel-next' => 'right: {{SIZE}}{{UNIT}};',
					// '{{WRAPPER}} .premium-blog-wrap a.carousel-arrow.carousel-prev' => 'left: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'label_style',
			array(
				'label'     => __( 'Label', 'premium-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_label' => 'yes',
				),
			)
		);

		$this->add_control(
			'label_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-search__label' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'label_typo',
				'selector' => '{{WRAPPER}} .premium-search__label',
			)
		);

		$this->add_control(
			'label_background_color',
			array(
				'label'     => __( 'Background Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-search__label' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'label_border',
				'selector' => '{{WRAPPER}} .premium-search__label',
			)
		);

		$this->add_control(
			'label_border_radius',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__label' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'label_text_shadow',
				'selector' => '{{WRAPPER}} .premium-search__label',
			)
		);

		$this->add_responsive_control(
			'label_margin',
			array(
				'label'      => __( 'Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__label-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->add_responsive_control(
			'label_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->end_controls_section();

		if ( $papro_activated ) {
			do_action( 'pa_search_select_field_style', $this );
		}

		$this->start_controls_section(
			'input_field_style',
			array(
				'label' => __( 'Search Field', 'premium-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'input_typo',
				'selector' => '{{WRAPPER}} .premium-search__input',
			)
		);

		$this->start_controls_tabs( 'input_style_tabs' );

		$this->start_controls_tab(
			'input_style_normal',
			array(
				'label' => __( 'Normal', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'input_color',
			array(
				'label'     => __( 'Text Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-search__input, {{WRAPPER}} .premium-search__input::placeholder' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'input_background_color',
			array(
				'label'     => __( 'Background Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-search__input' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'input_border',
				'selector' => '{{WRAPPER}} .premium-search__input',
			)
		);

		$this->add_control(
			'input_border_radius',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'input_box_shadow',
				'selector' => '{{WRAPPER}} .premium-search__input',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'input_style_focus',
			array(
				'label' => __( 'Focus', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'input_background_color_focus',
			array(
				'label'     => __( 'Background Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-search__input:focus'  => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'input_border_focus',
				'selector' => '{{WRAPPER}} .premium-search__input:focus',
			)
		);

		$this->add_control(
			'read_more_border_radius_focus',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__input:focus'  => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'input_box_shadow_focus',
				'selector' => '{{WRAPPER}} .premium-search__input:focus',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		if ( $papro_activated ) {
			do_action( 'pa_search_keyword_button_style', $this );
		}

		$this->start_controls_section(
			'search_button_style',
			array(
				'label'     => __( 'Search Button', 'premium-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'search_button' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'btn_typo',
				'selector' => '{{WRAPPER}} .premium-search__btn',
			)
		);

		$this->start_controls_tabs( 'btn_style_tabs' );

		$this->start_controls_tab(
			'btn_style_normal',
			array(
				'label' => __( 'Normal', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'btn_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-search__btn'     => 'color: {{VALUE}};',
					'{{WRAPPER}} .premium-search__btn svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'btn_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .premium-search__btn',
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'btn_text_shadow',
				'selector' => '{{WRAPPER}} .premium-search__btn',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'btn_border',
				'selector' => '{{WRAPPER}} .premium-search__btn',
			)
		);

		$this->add_control(
			'btn_border_radius',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'btn_box_shadow',
				'selector' => '{{WRAPPER}} .premium-search__btn',
			)
		);

		$this->add_responsive_control(
			'btn_margin',
			array(
				'label'      => __( 'Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__btn-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'btn_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'btn_style_hover',
			array(
				'label' => __( 'Hover', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'btn_hover_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-search__btn:hover'  => 'color: {{VALUE}};',
					'{{WRAPPER}} .premium-search__btn:hover svg'  => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'btn_background_hover',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .premium-search__btn:hover',
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'btn_text_shadow_hover',
				'selector' => '{{WRAPPER}} .premium-search__btn:hover',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'btn_border_hover',
				'selector' => '{{WRAPPER}} .premium-search__btn:hover',
			)
		);

		$this->add_control(
			'btn_border_radius_hover',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__btn:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'btn_shadow_hover',
				'selector' => '{{WRAPPER}} .premium-search__btn:hover',
			)
		);

		$this->add_responsive_control(
			'btn_margin_hover',
			array(
				'label'      => __( 'Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__btn-wrap:hover' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'btn_padding_hover',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__btn:hover' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'thumbnail_style_section',
			array(
				'label'     => __( 'Thumbnail', 'premium-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'query_type'          => 'post',
					'show_post_thumbnail' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'thumbnail_width',
			array(
				'label'      => __( 'Width', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'custom' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 200,
					),
					'em' => array(
						'min' => 1,
						'max' => 20,
					),
				),
				'condition'  => array(
					'show_post_thumbnail' => 'yes',
					'skin'                => 'side',
				),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__thumbnail img' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'thumbnail_height',
			array(
				'label'      => __( 'Height', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'custom' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 600,
					),
					'em' => array(
						'min' => 1,
						'max' => 60,
					),
				),
				'default'    => array(
					'size' => 300,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__thumbnail img' => 'height: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'show_post_thumbnail' => 'yes',
				),
			)
		);

		$this->add_control(
			'image_effect',
			array(
				'label'        => __( 'Hover Effect', 'premium-addons-for-elementor' ),
				'type'         => Controls_Manager::SELECT,
				'description'  => __( 'Choose a hover effect for the image', 'premium-addons-for-elementor' ),
				'options'      => array(
					'none'    => __( 'None', 'premium-addons-for-elementor' ),
					'zoomin'  => __( 'Zoom In', 'premium-addons-for-elementor' ),
					'zoomout' => __( 'Zoom Out', 'premium-addons-for-elementor' ),
					'scale'   => __( 'Scale', 'premium-addons-for-elementor' ),
					'gray'    => __( 'Grayscale', 'premium-addons-for-elementor' ),
					'blur'    => __( 'Blur', 'premium-addons-for-elementor' ),
					'bright'  => __( 'Bright', 'premium-addons-for-elementor' ),
					'sepia'   => __( 'Sepia', 'premium-addons-for-elementor' ),
					'trans'   => __( 'Translate', 'premium-addons-for-elementor' ),
				),
				'prefix_class' => 'premium-search__effect-',
				'default'      => 'zoomin',
				'label_block'  => true,
			)
		);

		$this->add_control(
			'overlay_color_normal',
			array(
				'label'     => __( 'Overlay Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-search__overlay' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'overlay_color',
			array(
				'label'     => __( 'Hover Overlay Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-search__container:not(.premium-search__skin-banner) .premium-search__thumbnail-wrap:hover .premium-search__overlay' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .premium-search__container.premium-search__skin-banner .premium-search__post-wrap:hover .premium-search__overlay' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'thumbnail_border',
				'selector' => '{{WRAPPER}} .premium-search__thumbnail',
			)
		);

		$this->add_control(
			'thumbnail_border_radius',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__thumbnail' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'title_style_section',
			array(
				'label'     => __( 'Title', 'premium-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'query_type' => 'post',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typo',
				'global'   => array(
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				),
				'selector' => '{{WRAPPER}} .premium-search__post-title, {{WRAPPER}} .premium-search__post-title a',
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_SECONDARY,
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-search__post-title a'  => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'title_hover_color',
			array(
				'label'     => __( 'Hover Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-search__post-title:hover a, {{WRAPPER}}.premium-search__whole-link-yes .premium-search__post-wrap:hover .premium-search__post-title a'  => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'title_margin',
			array(
				'label'      => __( 'Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__post-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'excerpt_style_section',
			array(
				'label'     => __( 'Excerpt', 'premium-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'query_type'   => 'post',
					'show_excerpt' => 'yes',
				),
			)
		);

		$this->add_control(
			'excerpt_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_TEXT,
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-search__excerpt-wrap'  => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'excerpt_typo',
				'selector' => '{{WRAPPER}} .premium-search__excerpt-wrap',
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'excerpt_text_shadow',
				'selector' => '{{WRAPPER}} .premium-search__post-excerpt',
			)
		);

		$this->add_responsive_control(
			'excerpt_margin',
			array(
				'label'      => __( 'Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__excerpt-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'post_container_style',
			array(
				'label'     => __( 'Post Box', 'premium-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'query_type' => 'post',
					'skin!'      => 'banner',
				),
			)
		);

		$this->start_controls_tabs( 'post_box_style_tabs' );

		$this->start_controls_tab(
			'post_box_normal',
			array(
				'label' => __( 'Normal', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'post_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .premium-search__post-inner',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'post_border',
				'selector' => '{{WRAPPER}} .premium-search__post-inner',
			)
		);

		$this->add_control(
			'post_border_radius',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__post-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'post_box_shadow',
				'selector' => '{{WRAPPER}} .premium-search__post-inner',
			)
		);

		$this->add_responsive_control(
			'post_box_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__post-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'post_box_hover',
			array(
				'label' => __( 'Hover', 'premium-addons-for-elementor' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'post_background_hover',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .premium-search__post-inner:hover',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'post_border_hover',
				'selector' => '{{WRAPPER}} .premium-search__post-inner:hover',
			)
		);

		$this->add_control(
			'post_border_radius_hover',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__post-inner:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'post_box_shadow_hover',
				'selector' => '{{WRAPPER}} .premium-search__post-inner:hover',
			)
		);

		$this->add_responsive_control(
			'post_box_padding_hover',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__post-inner:hover' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'content_style_section',
			array(
				'label'     => __( 'Content Box', 'premium-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'query_type' => 'post',
					'skin'       => 'banner',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'content_background_color',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .premium-search__post-content',
			)
		);

		$this->add_responsive_control(
			'content_margin',
			array(
				'label'      => __( 'Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__post-content' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'content_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__post-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'readmore_style_section',
			array(
				'label'     => __( 'Read More', 'premium-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'query_type'   => 'post',
					'show_excerpt' => 'yes',
					'excerpt_type' => 'link',
				),
			)
		);

		$this->add_control(
			'readmore_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-search-excerpt-link'  => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'readmore_hover_color',
			array(
				'label'     => __( 'Hover Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-search-excerpt-link:hover'  => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'readmore_typo',
				'selector' => '{{WRAPPER}} .premium-search-excerpt-link',
			)
		);

		$this->add_responsive_control(
			'readmore_spacing',
			array(
				'label'     => __( 'Spacing', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'selectors' => array(
					'{{WRAPPER}} .premium-search-excerpt-link-wrap'  => 'margin-top: {{SIZE}}px',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'results_number_style_section',
			array(
				'label'     => __( 'Results Number', 'premium-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'query_type'          => 'post',
					'show_results_number' => 'yes',
				),
			)
		);

		$this->add_control(
			'results_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_TEXT,
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-search__results-number span'  => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'results_typo',
				'selector' => '{{WRAPPER}} .premium-search__results-number span',
			)
		);

		$this->add_responsive_control(
			'results_spacing',
			array(
				'label'      => __( 'Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__results-number' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'posts_container_style',
			array(
				'label'     => __( 'Posts Container', 'premium-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'query_type' => 'post',
				),
			)
		);

		$this->add_responsive_control(
			'posts_container_max_height',
			array(
				'label'       => __( 'Maximum Height', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 'px', 'em', 'vh', 'custom' ),
				'range'       => array(
					'px' => array(
						'min' => 50,
						'max' => 600,
					),
					'em' => array(
						'min' => 1,
						'max' => 50,
					),
				),
				'label_block' => true,
				'selectors'   => array(
					'{{WRAPPER}} .premium-search__posts-wrap'  => 'max-height: {{SIZE}}{{UNIT}};',
				),
				'condition'   => array(
					'query_type' => 'post',
					'carousel!'  => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'posts_container_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .premium-search__query-wrap',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'posts_container_border',
				'selector' => '{{WRAPPER}} .premium-search__query-wrap',
			)
		);

		$this->add_control(
			'posts_container_border_radius',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__query-wrap' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'posts_container_shadow',
				'selector' => '{{WRAPPER}} .premium-search__query-wrap',
			)
		);

		$this->add_responsive_control(
			'posts_container_margin',
			array(
				'label'      => __( 'Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__query-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->add_responsive_control(
			'posts_container_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__query-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'pagination_style_section',
			array(
				'label'     => __( 'Pagination', 'premium-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'query_type'          => 'post',
					'premium_blog_paging' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'pagination_typo',
				'selector' => '{{WRAPPER}} .premium-search-form__pagination-container > .page-numbers',
			)
		);

		$this->start_controls_tabs( 'pagination_style_tabs' );

		$this->start_controls_tab(
			'pagination_nomral_tab',
			array(
				'label' => __( 'Normal', 'premium-addons-for-elementor' ),

			)
		);

		$this->add_control(
			'pagination_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-search-form__pagination-container .page-numbers' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'pagination_back_color',
			array(
				'label'     => __( 'Background Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-search-form__pagination-container .page-numbers' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'navigation_border',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .premium-search-form__pagination-container .page-numbers',
			)
		);

		$this->add_control(
			'navigation_border_radius',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search-form__pagination-container .page-numbers' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'navigation_adv_radius!' => 'yes',
				),
			)
		);

		$this->add_control(
			'navigation_adv_radius',
			array(
				'label'       => __( 'Advanced Border Radius', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SWITCHER,
				'description' => __( 'Apply custom radius values. Get the radius value from ', 'premium-addons-for-elementor' ) . '<a href="https://9elements.github.io/fancy-border-radius/" target="_blank">here</a>' . __('. See ', 'premium-addons-for-elementor') . '<a href="https://www.youtube.com/watch?v=S0BJazLHV-M" target="_blank">tutorial</a>',
			)
		);

		$this->add_control(
			'navigation_adv_radius_value',
			array(
				'label'     => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'dynamic'   => array( 'active' => true ),
				'selectors' => array(
					'{{WRAPPER}} .premium-search-form__pagination-container .page-numbers' => 'border-radius: {{VALUE}};',
				),
				'condition' => array(
					'navigation_adv_radius' => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'pagination_hover_tab',
			array(
				'label' => __( 'Hover', 'premium-addons-for-elementor' ),

			)
		);

		$this->add_control(
			'pagination_hover_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-search-form__pagination-container .page-numbers:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'pagination_back_hover_color',
			array(
				'label'     => __( 'Background Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-search-form__pagination-container .page-numbers:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'hover_navigation_border',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .premium-search-form__pagination-container .page-numbers:hover',
			)
		);

		$this->add_control(
			'hover_navigation_border_radius',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search-form__pagination-container .page-numbers:hover' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'hover_navigation_adv_radius!' => 'yes',
				),
			)
		);

		$this->add_control(
			'hover_navigation_adv_radius',
			array(
				'label'       => __( 'Advanced Border Radius', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SWITCHER,
				'description' => __( 'Apply custom radius values. Get the radius value from ', 'premium-addons-for-elementor' ) . '<a href="https://9elements.github.io/fancy-border-radius/" target="_blank">here</a>' . __('. See ', 'premium-addons-for-elementor') . '<a href="https://www.youtube.com/watch?v=S0BJazLHV-M" target="_blank">tutorial</a>',
			)
		);

		$this->add_control(
			'hover_navigation_adv_radius_value',
			array(
				'label'     => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'dynamic'   => array( 'active' => true ),
				'selectors' => array(
					'{{WRAPPER}} .premium-search-form__pagination-container .page-numbers:hover' => 'border-radius: {{VALUE}};',
				),
				'condition' => array(
					'hover_navigation_adv_radius' => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'pagination_active_tab',
			array(
				'label' => __( 'Active', 'premium-addons-for-elementor' ),

			)
		);

		$this->add_control(
			'pagination_active_color',
			array(
				'label'     => __( 'Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-search-form__pagination-container span.current' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'pagination_back_active_color',
			array(
				'label'     => __( 'Background Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-search-form__pagination-container span.current' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'active_navigation_border',
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .premium-search-form__pagination-container span.current',
			)
		);

		$this->add_control(
			'active_navigation_border_radius',
			array(
				'label'      => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search-form__pagination-container span.current' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'active_navigation_adv_radius!' => 'yes',
				),
			)
		);

		$this->add_control(
			'active_navigation_adv_radius',
			array(
				'label'       => __( 'Advanced Border Radius', 'premium-addons-for-elementor' ),
				'type'        => Controls_Manager::SWITCHER,
				'description' => __( 'Apply custom radius values. Get the radius value from ', 'premium-addons-for-elementor' ) . '<a href="https://9elements.github.io/fancy-border-radius/" target="_blank">here</a>' . __('. See ', 'premium-addons-for-elementor') . '<a href="https://www.youtube.com/watch?v=S0BJazLHV-M" target="_blank">tutorial</a>',
			)
		);

		$this->add_control(
			'active_navigation_adv_radius_value',
			array(
				'label'     => __( 'Border Radius', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'dynamic'   => array( 'active' => true ),
				'selectors' => array(
					'{{WRAPPER}} .premium-search-form__pagination-container span.current' => 'border-radius: {{VALUE}};',
				),
				'condition' => array(
					'active_navigation_adv_radius' => 'yes',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'pagination_margin',
			array(
				'label'      => __( 'Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'separator'  => 'before',
				'selectors'  => array(
					'{{WRAPPER}} .premium-search-form__pagination-container .page-numbers' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'pagination_padding',
			array(
				'label'      => __( 'Padding', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search-form__pagination-container .page-numbers' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'pagination_overlay_color',
			array(
				'label'     => __( 'Overlay Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array(
					'{{WRAPPER}} .premium-search__query-wrap .premium-loading-feed' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'pagination_spinner_color',
			array(
				'label'     => __( 'Spinner Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-search__query-wrap .premium-loader' => 'border-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'pagination_spinner_fill_color',
			array(
				'label'     => __( 'Fill Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-search__query-wrap .premium-loader' => 'border-top-color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'carousel_dots_style',
			array(
				'label'     => __( 'Carousel Dots', 'premium-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'query_type' => 'post',
					'carousel'   => 'yes',
				),
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
					'{{WRAPPER}} ul.slick-dots li' => 'color: {{VALUE}}',
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
					'{{WRAPPER}} ul.slick-dots li.slick-active' => 'color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'carousel_arrows_style',
			array(
				'label'     => __( 'Carousel Arrows', 'premium-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'query_type' => 'post',
					'carousel'   => 'yes',
				),
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
					'{{WRAPPER}} .premium-search__query-wrap .slick-arrow' => 'color: {{VALUE}};',
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
					'{{WRAPPER}} .premium-search__query-wrap .slick-arrow i' => 'font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'carousel_arrow_background',
			array(
				'label'     => __( 'Background Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array(
					'default' => Global_Colors::COLOR_SECONDARY,
				),
				'selectors' => array(
					'{{WRAPPER}} .premium-search__query-wrap .slick-arrow' => 'background-color: {{VALUE}};',
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
					'{{WRAPPER}} .premium-search__query-wrap .slick-arrow' => 'border-radius: {{SIZE}}{{UNIT}};',
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
					'{{WRAPPER}} .premium-search__query-wrap .slick-arrow' => 'padding: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'carousel_arrow_margin',
			array(
				'label'      => __( 'Margin', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__query-wrap .slick-arrow' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'spinner_style',
			array(
				'label' => __( 'Spinner', 'premium-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'spinner_size',
			array(
				'label'      => __( 'Size', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__spinner .premium-loader' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_control(
			'spinner_color',
			array(
				'label'     => __( 'Spinner Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-search__spinner .premium-loader' => 'border-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'spinner_fill_color',
			array(
				'label'     => __( 'Fill Color', 'premium-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .premium-search__spinner .premium-loader' => 'border-top-color: {{VALUE}}',
				),
			)
		);

		$this->add_responsive_control(
			'spinner_offset',
			array(
				'label'      => __( 'Offset', 'premium-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .premium-search__spinner' => 'right: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render Search Form widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {

		$settings = $this->get_settings_for_display();

		$papro_activated = apply_filters( 'papro_activated', false );

		if ( ! $papro_activated || version_compare( PREMIUM_PRO_ADDONS_VERSION, '2.9.15', '<' ) ) {

			if ( 'elements' === $settings['query_type'] || 'yes' === $settings['remove_button_switcher'] || 'yes' === $settings['type_select'] || ( '' !== $settings['field_effects'] && 'yes' === $settings['show_label'] ) ) {

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
			'search_input',
			array(
				'id'          => 'premium-search__input-' . $this->get_id(),
				'type'        => 'text',
				'class'       => 'premium-search__input',
				'value'       => '',
				'placeholder' => esc_attr( $settings['placeholder_text'] ),
			)
		);

		$search_settings = array(
			'query'        => $settings['query_type'],
			'buttonAction' => $settings['button_action'],
		);

		if ( 'redirect' === $settings['button_action'] ) {
			$search_settings['search_link'] = esc_url( $settings['search_page_link'] );
		}

		if ( 'elements' === $settings['query_type'] ) {
			$search_settings['target'] = esc_attr( $settings['selector'] );
            $search_settings['fadeout_target'] = esc_attr( $settings['fadeout_selector'] );
		} else {

			$search_settings['hideOnClick'] = 'yes' === $settings['hide_on_click'];
			// Add page ID to be used later to get posts by AJAX.
			$page_id = '';
			if ( null !== Plugin::$instance->documents->get_current() ) {
				$page_id = Plugin::$instance->documents->get_current()->get_main_id();
			}
			$this->add_render_attribute( 'container', 'data-page', $page_id );

			if ( 'yes' === $settings['carousel'] ) {

				$search_settings['carousel']       = true;
				$search_settings['slidesToScroll'] = $settings['slides_to_scroll'];
				$search_settings['rows']           = $settings['rows'];
				$search_settings['spacing']        = $settings['carousel_spacing'];
				$search_settings['arrows']         = 'yes' === $settings['carousel_arrows'];
				$search_settings['fade']           = 'yes' === $settings['carousel_fade'];
				$search_settings['center']         = 'yes' === $settings['carousel_center'];
				$search_settings['dots']           = 'yes' === $settings['carousel_dots'];
				$search_settings['speed']          = '' !== $settings['carousel_speed'] ? $settings['carousel_speed'] : 300;

			}
		}

		$this->add_render_attribute(
			'container',
			array(
				'class'         => array(
					'premium-search__container',
					'premium-search__skin-' . $settings['skin'],
				),
				'data-settings' => wp_json_encode( $search_settings ),
			)
		);

		?>

			<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'container' ) ); ?>>

				<?php if ( 'yes' === $settings['show_label'] ) : ?>
					<div class="premium-search__label-wrap">
						<label for="<?php echo esc_attr( 'premium-search__input-' . $this->get_id() ); ?>" class="premium-search__label"><?php echo wp_kses_post( $settings['label_text'] ); ?></label>
					</div>
				<?php endif; ?>
				<div class="premium-search__input-btn-wrap">

					<?php
					if ( 'yes' === $settings['type_select'] ) :
						$post_types = Blog_Helper::get_posts_types();
						?>
						<div class="premium-search__type-filter">
							<select class="premium-search__type-select">

								<option value="any"><?php echo __( 'All Posts', 'premium-addons-for-elementor' ); ?></option>
								<?php
								foreach ( $post_types as $id => $label ) :
									$count = wp_count_posts( $id )->publish;

									if ( $count < 1 ) {
										continue;
									}
									?>
									<?php
									if ( ! in_array( $id, $settings['post_types_excluded'] ) ) :
										if ( 'yes' === $settings['show_posts_number'] ) {
											$label = $label . ' (' . $count . ')';
										}
										?>
										<option value="<?php echo esc_attr( $id ); ?>"><?php echo wp_kses_post( $label ); ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
							</select>
						</div>
					<?php endif; ?>

					<div class="premium-search__input-wrap">
						<input <?php echo wp_kses_post( $this->get_render_attribute_string( 'search_input' ) ); ?>>

						<div class="premium-search__spinner"></div>

						<?php if ( 'yes' === $settings['remove_button_switcher'] ) : ?>
							<div class="premium-search__remove-wrap premium-addons__v-hidden">
								<i class="premium-search__remove-icon fa fa-close"></i>
							</div>
						<?php endif; ?>
					</div>

					<?php if ( 'yes' === $settings['search_button'] ) : ?>

						<div class="premium-search__btn-wrap">
							<button type="button" class="premium-search__btn" value="" data-page-url="">

								<?php if ( 'yes' === $settings['search_icon'] ) : ?>

									<?php
										Icons_Manager::render_icon(
											$settings['search_icon_select'],
											array(
												'aria-hidden' => 'true',
											)
										);
									?>

								<?php endif; ?>

								<?php if ( ! empty( $settings['button_text'] ) ) : ?>
									<span class="premium-search__btn-text">
										<?php echo wp_kses_post( $settings['button_text'] ); ?>
									</span>
								<?php endif; ?>

							</button>
						</div>

					<?php endif; ?>

				</div>

				<?php if ( 'post' === $settings['query_type'] ) : ?>
					<div class="premium-search__query-wrap query-hidden"></div>
				<?php endif; ?>

			</div>

		<?php
	}
}
