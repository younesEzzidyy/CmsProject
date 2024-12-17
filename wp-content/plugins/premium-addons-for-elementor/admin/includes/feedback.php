<?php
/**
 * PA Admin Bar
 */

namespace PremiumAddons\Admin\Includes;

use PremiumAddons\Includes\Helper_Functions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Feedback
 */
class Feedback {

	/**
	 * Class instance
	 *
	 * @var instance
	 */
	private static $instance = null;

	/**
	 * Constructor for the class
	 */
	private function __construct() {

		add_action( 'admin_footer-plugins.php', array( $this, 'create_popup' ) );
		add_action( 'wp_ajax_pa_handle_feedback_action', array( $this, 'send' ) );
	}


	public static function send() {

		$response = array( 'success' => false );

		$data = $_POST['data'];

		if ( isset( $data['feedback'] ) ) {
			$reason      = $data['feedback'];
			$suggestions = isset( $data['suggestions'] ) ? $data['suggestions'] : null;
			$anonymous   = isset( $data['anonymous'] ) ? ! ! $data['anonymous'] : false;

		}

		if ( ! is_string( $reason ) || empty( $reason ) ) {
			return false;
		}

		$wordpress = self::collect_wordpress_data( true );

		$wordpress['deactivated_plugin']['uninstall_reason']  = $reason;
		$wordpress['deactivated_plugin']['uninstall_details'] = '';

		if ( ! empty( $suggestions ) ) {
			$wordpress['deactivated_plugin']['uninstall_details'] .= $suggestions;
		}

		if ( ! $anonymous ) {
			$wordpress['deactivated_plugin']['uninstall_details'] .= ( empty( $wordpress['deactivated_plugin']['uninstall_details'] ) ? '' : PHP_EOL . PHP_EOL ) . 'Domain: ' . self::get_site_domain();
		}

		$body = array(
			'user'      => self::collect_user_data( $anonymous ),
			'wordpress' => $wordpress,
		);

		$api_url = 'https://feedbackpa.leap13.com/wp-json/feedback/v2/add';

		$response = wp_safe_remote_request(
			$api_url,
			array(
				'headers'     => array(
					'Content-Type' => 'application/json',
				),
				'body'        => wp_json_encode( $body ),
				'timeout'     => 20,
				'method'      => 'POST',
				'httpversion' => '1.1',
			)
		);

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( 'REQUEST ERR' );

		}

		if ( ! isset( $response['response'] ) || ! is_array( $response['response'] ) ) {
			wp_send_json_error( 'REQUEST UNKNOWN' );

		}

		if ( ! isset( $response['body'] ) ) {
			wp_send_json_error( 'REQUEST PAYLOAD EMPTY' );

		}

		wp_send_json_success( ( $response['body'] ) );
	}

	/**
	 * Method generates Feedback popup
	 */
	public function create_popup() {

		$plugin_data = get_plugin_data( PREMIUM_ADDONS_FILE );

		?>
			<div class="pa-deactivation-popup hidden" data-type="wrapper" data-slug="<?php echo $plugin_data['TextDomain']; ?>">
				<div class="overlay">
					<div class="close"><i class="dashicons dashicons-no"></i></div>
					<div class="body">
						<section class="title-wrap">
							<div class="pa-img-wrap">
								<img src="<?php echo esc_url( PREMIUM_ADDONS_URL . 'admin/images/pa-logo-symbol.png' ); ?>">
							</div>
							<?php echo __( 'Sorry to see you go', 'premium-addons-for-elementor' ); ?>
						</section>
						<section class="messages-wrap">
							<p><?php echo __( 'Would you quickly give us your reason for doing so?', 'premium-addons-for-elementor' ); ?></p>
						</section>
						<section class="options-wrap">
							<label>
								<input type="radio" name="feedback" value="temp">
							<?php echo __( 'Temporary deactivation', 'premium-addons-for-elementor' ); ?>
							</label>
							<label>
								<input type="radio" name="feedback" value="setup">
							<?php echo __( 'Set up is too difficult', 'premium-addons-for-elementor' ); ?>
							</label>
							<label>
								<input type="radio" name="feedback" value="e-issues">
							<?php echo __( 'Causes issues with Elementor', 'premium-addons-for-elementor' ); ?>
							</label>
							<label>
								<input type="radio" name="feedback" value="documentation">
							<?php echo __( 'Lack of documentation', 'premium-addons-for-elementor' ); ?>
							</label>
							<label>
								<input type="radio" name="feedback" value="features">
							<?php echo __( 'Not the features I wanted', 'premium-addons-for-elementor' ); ?>
							</label>
							<label>
								<input type="radio" name="feedback" value="better-plugin">
							<?php echo __( 'Found a better plugin', 'premium-addons-for-elementor' ); ?>
							</label>
							<label>
								<input type="radio" name="feedback" value="incompatibility">
							<?php echo __( 'Incompatible with theme or plugin', 'premium-addons-for-elementor' ); ?>
							</label>
							<label>
								<input type="radio" name="feedback" value="maintenance">
							<?php echo __( 'Other', 'premium-addons-for-elementor' ); ?>
							</label>
						</section>
						<section class="messages-wrap hidden" data-feedback>
							<p class="hidden" data-feedback="setup"><?php echo __( 'What was the difficult part?', 'premium-addons-for-elementor' ); ?></p>
							<p class="hidden" data-feedback="e-issues"><?php echo __( 'What was the issue?', 'premium-addons-for-elementor' ); ?></p>
							<p class="hidden" data-feedback="documentation"><?php echo __( 'What can we describe more?', 'premium-addons-for-elementor' ); ?></p>
							<p class="hidden" data-feedback="features"><?php echo __( 'How could we improve?', 'premium-addons-for-elementor' ); ?></p>
							<p class="hidden" data-feedback="better-plugin"><?php echo __( 'Can you mention it?', 'premium-addons-for-elementor' ); ?></p>
							<p class="hidden" data-feedback="incompatibility"><?php echo __( 'With what plugin or theme is incompatible?', 'premium-addons-for-elementor' ); ?></p>
							<p class="hidden" data-feedback="maintenance"><?php echo __( 'Please specify:', 'premium-addons-for-elementor' ); ?></p>
						</section>
						<section class="options-wrap hidden" data-feedback>
							<label>
								<textarea name="suggestions" rows="2"></textarea>
							</label>
						</section>
						<section class="messages-wrap hidden" data-feedback>
							<?php if( ini_get( 'max_execution_time' ) < 300 ) :
								$link = Helper_Functions::get_campaign_link( 'https://premiumaddons.com/docs/fix-elementor-editor-panel-loading-issues/', 'plugins-page', 'wp-dash', 'deactivate-form' );
							?>
								<p class="options-wrap pa-info-notice">
									<?php echo __( 'Having Elementor editor not loading issue? Your website PHP limits might be the reason. Here\'s', 'premium-addons-for-elementor') .
											sprintf( '<a target="_blank" href="%s">%s</a>', $link, __(' how to increase the PHP limits', 'premium-addons-for-elementor') ); ?>
								</p>
							<?php endif; ?>
							<p><?php echo __( 'Would you like to share your e-mail with us so that we can write you back?', 'premium-addons-for-elementor' ); ?></p>
						</section>
						<section class="options-wrap hidden" data-feedback>
							<label>
								<input type="checkbox" name="anonymous" value="1">
							<?php echo __( 'No, I\'d like to stay anonymous', 'premium-addons-for-elementor' ); ?>
							</label>
						</section>

						<section class="buttons-wrap clearfix">
							<button class="pa-deactivate-btn" data-action="deactivation"><?php echo __( 'Deactivate', 'premium-addons-for-elementor' ); ?></button>
						</section>
					</div>

				</div>
			</div>
			<?php
	}

	private static function collect_wordpress_data( $detailed = true ) {

		$current_plugin = get_plugin_data( PREMIUM_ADDONS_FILE );

		// Plugin data
		$data = array(
			'deactivated_plugin' => array(
				'version' => $current_plugin['Version'],
				'memory'  => 'Memory: ' . size_format( wp_convert_hr_to_bytes( ini_get( 'memory_limit' ) ) ),
				'time'    => 'Time: ' . ini_get( 'max_execution_time' ),
				'install' => 'Activation: ' . get_option( 'pa_install_time' ),
				'deactivate' => 'Deactivation: ' . gmdate( 'j F, Y', time() )
			),
		);

		if ( $detailed ) {

            $data['extra'] = array(
                'locale'      => ( get_bloginfo( 'version' ) >= 4.7 ) ? get_user_locale() : get_locale(),
                'themes'      => self::get_themes(),
                'plugins'     => self::get_plugins(),
            );

		}

		return $data;
	}

	/**
	 * Get a list of installed plugins
	 */
	private static function get_plugins() {

		if ( ! function_exists( 'get_plugins' ) ) {
			include ABSPATH . '/wp-admin/includes/plugin.php';
		}

		// $plugins   = get_plugins();
		$option    = get_option( 'active_plugins', array() );
		$active    = array();

		// $installed = array();
		// foreach ( $plugins as $id => $info ) {
		// 	if ( in_array( $id, $active ) ) {
		// 		continue;
		// 	}

		// 	$id = explode( '/', $id );
		// 	$id = ucwords( str_replace( '-', ' ', $id[0] ) );

		// 	$installed[] = $id;
		// }

		foreach ( $option as $id ) {
			$id = explode( '/', $id );
			$id = ucwords( str_replace( '-', ' ', $id[0] ) );

			$active[] = $id;
		}

		return array(
			// 'installed' => $installed,
			'active'    => $active,
		);
	}

	/**
	 * Get current themes
	 *
	 * @return array
	 */
	private static function get_themes() {

		$theme = wp_get_theme();

		return array(
			// 'installed' => self::get_installed_themes(),
			'active'    => array(
				'name'    => $theme->get( 'Name' ),
			),
		);
	}

	/**
	 * Get an array of installed themes
	 *
	 * @return array
	 */
	private static function get_installed_themes() {
		$installed = wp_get_themes();
		$theme     = get_stylesheet();
		$data      = array();

		foreach ( $installed as $slug => $info ) {
			if ( $slug === $theme ) {
				continue;
			}

			$data[ $slug ] = array(
				'name'    => $info->get( 'Name' ),
			);
		}

		return $data;
	}

	/**
	 * Collect user data.
	 *
	 * @param bool $anonymous
	 *
	 * @return array
	 */
	private static function collect_user_data( $anonymous ) {
		$user = wp_get_current_user();

		$return = array(
			'email'      => '',
			'first_name' => '',
			'last_name'  => '',
			'domain'     => '',
		);

		if ( $user && ! $anonymous ) {
			$return['email']      = $user->user_email;
			$return['first_name'] = $user->first_name;
			$return['last_name']  = $user->last_name;
			$return['domain']     = self::get_site_domain();
		}

		return $return;
	}

	private static function get_site_domain() {
		return function_exists( 'parse_url' ) ? parse_url( get_home_url(), PHP_URL_HOST ) : false;
	}

	/**
	 * Creates and returns an instance of the class
	 *
	 * @since 3.20.9
	 * @access public
	 *
	 * @return object
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) ) {

			self::$instance = new self();

		}

		return self::$instance;
	}
}
