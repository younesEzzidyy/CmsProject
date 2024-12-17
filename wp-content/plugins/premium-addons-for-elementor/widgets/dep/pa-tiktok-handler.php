<?php
/**
 * Premium TikTok Feed Handler.
 */

use PremiumAddons\Includes\Helper_Functions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TIKTOK_API_URL', 'https://open.tiktokapis.com/v2/' );

/**
 * Get TikTok Data
 *
 * @param string $id         widget id.
 * @param array  $settings    widget settings.
 */
function get_tiktok_data( $id, $settings ) {

	$token = $settings['access_token'];

	$transient_name = sprintf( 'papro_tiktok_feed_%s_%s', $id, substr( $token, -8 ) );

	$response = get_transient( $transient_name );

	$is_edit_mode = \Elementor\Plugin::$instance->editor->is_edit_mode();

	if ( $is_edit_mode || false === $response ) {

		$filter_id = $settings['match_id'];

		$fields = '?fields=id,create_time,cover_image_url,share_url,video_description,duration,height,width,title,embed_html,embed_link,like_count,comment_count,share_count,view_count';

		$args = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $token,
				'Content-Type'  => 'application/json',
			),
		);

		if ( ! empty( $filter_id ) ) {

			$filters  = explode( ',', $filter_id );
			$endpoint = 'video/query/';

			$filters = array(
				'filters' => array(
					'video_ids' => $filters,
				),
			);

			$args['body'] = wp_json_encode( $filters );

		} else {
			$endpoint = 'video/list/';
			$limit    = $settings['no_of_posts'];

			if ( ! empty( $limit ) ) {
				$limit = array(
					'max_count' => $limit,
				);

				$args['body'] = wp_json_encode( $limit );
			}
		}

		$url = TIKTOK_API_URL . $endpoint . $fields;

		sleep( 2 );

		$response = wp_remote_post(
			$url,
			$args
		);

		if ( is_wp_error( $response ) ) {
			return;
		}

		$response = wp_remote_retrieve_body( $response );

		$response = json_decode( $response, true );

		if ( 'ok' !== $response['error']['code'] ) {
			?>
			<div class="premium-error-notice">
				<?php echo esc_html( __( 'Something went wrong: Code ', 'premium-addons-for-elementor' ) ) . $response['error']['code'] . ' => ' . $response['error']['message']; ?>
			</div>
			<?php
			return;
		}

		$transient = $settings['reload'];

		$expire_time = Helper_Functions::transient_expire( $transient );

		set_transient( $transient_name, $response, $expire_time );
	}

	$items = $response['data']['videos'];

	if ( empty( $filter_id ) ) {

		$detect = new \PA_Mobile_Detect();

		if ( $detect->isTablet() && ! empty( $settings['no_of_posts_tablet'] ) ) {
			$items = array_slice( $items, 0, $settings['no_of_posts_tablet'] );
		} elseif ( $detect->isMobile() && ! empty( $settings['no_of_posts_mobile'] ) ) {
			$items = array_slice( $items, 0, $settings['no_of_posts_mobile'] );
		}
	}

	return $items;
}

/**
 * Get Profile Data.
 *
 * @param string $id         widget id.
 * @param array  $settings    widget settings.
 */
function get_tiktok_profile_data( $id, $settings ) {

	$token = $settings['access_token'];

	$transient_name = sprintf( 'papro_tiktok_profile_%s_%s', $id, substr( $token, -8 ) );

	$response = get_transient( $transient_name );

	if ( false === $response ) {

		$url = TIKTOK_API_URL . 'user/info/?fields=avatar_url,display_name,bio_description,profile_deep_link,is_verified,follower_count,following_count,likes_count';

		sleep( 2 );

		$response = wp_remote_get(
			$url,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $token,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return 'error';
		}

		$response = wp_remote_retrieve_body( $response );

		$response = json_decode( $response, true );

		if ( 'ok' !== $response['error']['code'] ) {
			?>
			<div class="premium-error-notice">
				<?php echo esc_html( __( 'Something went wrong: Code ', 'premium-addons-for-elementor' ) ) . $response['error']['code'] . ' => ' . $response['error']['message']; ?>
			</div>
			<?php
			return 'error';
		}

		$transient = $settings['reload'];

		$expire_time = Helper_Functions::transient_expire( $transient );

		set_transient( $transient_name, $response, $expire_time );
	}

	return $response['data']['user'];
}

/**
 * Refresh TikTok Token.
 *
 * @param string $token old token.
 */
function refresh_tiktok_token( $token ) {

	$api_url = 'https://appfb.premiumaddons.com/wp-json/fbapp/v2/reftiktok/';

	$response = wp_remote_get(
		$api_url . $token,
		array(
			'timeout'   => 60,
			'sslverify' => true,
		)
	);

	$response = wp_remote_retrieve_body( $response );

	$response = json_decode( $response, true );

	set_transient( 'pa_tiktok_token_' . $token, $response, 23 * HOUR_IN_SECONDS );

	return $response;
}


/**
 * Refresh TikTok Token.
 *
 * @param string $token old token.
 */
function get_tiktok_videos_urls( $settings, $feed, $widget_id ) {

	$token = $settings['access_token'];

	$transient_name = sprintf( 'papro_tiktok_urls_%s_%s', substr( $token, -8 ), $widget_id );

	$response = get_transient( $transient_name );

	if ( false === $response ) {

        foreach( $feed as $index => $video ) {

            $video_url = get_video_url( $video['share_url'] );

            download_tiktok_video( $video_url, $video['id'] );

        }

		$transient = Helper_Functions::transient_expire( $settings['reload'] );

		set_transient( $transient_name, true, $transient );

	}

}

function get_video_url( $url ) {

    $url = strtok( $url, '?' );

    $content = get_video_content( $url );

    $check = explode('"playAddr":"', $content);

    $contentURL = explode("\"", $check[1])[0];
    $contentURL = escape_sequence_decode($contentURL);

    return $contentURL;

}

function escape_sequence_decode($str) {

    // [U+D800 - U+DBFF][U+DC00 - U+DFFF]|[U+0000 - U+FFFF]
    $regex = '/\\\u([dD][89abAB][\da-fA-F]{2})\\\u([dD][c-fC-F][\da-fA-F]{2})
              |\\\u([\da-fA-F]{4})/sx';

    return preg_replace_callback($regex, function ($matches) {

        if (isset($matches[3])) {
            $cp = hexdec($matches[3]);
        } else {
            $lead = hexdec($matches[1]);
            $trail = hexdec($matches[2]);

            // http://unicode.org/faq/utf_bom.html#utf16-4
            $cp = ($lead << 10) + $trail + 0x10000 - (0xD800 << 10) - 0xDC00;
        }

        // https://tools.ietf.org/html/rfc3629#section-3
        // Characters between U+D800 and U+DFFF are not allowed in UTF-8
        if ($cp > 0xD7FF && 0xE000 > $cp) {
            $cp = 0xFFFD;
        }

        // https://github.com/php/php-src/blob/php-5.6.4/ext/standard/html.c#L471
        // php_utf32_utf8(unsigned char *buf, unsigned k)

        if ($cp < 0x80) {
            return chr($cp);
        } else if ($cp < 0xA0) {
            return chr(0xC0 | $cp >> 6) . chr(0x80 | $cp & 0x3F);
        }

        return html_entity_decode('&#' . $cp . ';');
    }, $str);
}

function get_video_content($url, $geturl = false) {

    $ch = curl_init();

    $options = array(
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
        CURLOPT_ENCODING       => "utf-8",
        CURLOPT_AUTOREFERER    => false,
        CURLOPT_COOKIEJAR      => 'cookie.txt',
        CURLOPT_COOKIEFILE     => 'cookie.txt',
        CURLOPT_REFERER        => 'https://www.tiktok.com/',
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_MAXREDIRS      => 10,
    );

    curl_setopt_array($ch, $options);

    if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) {
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    }

    $data = curl_exec($ch);


    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($geturl === true) {
        return curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    }

    curl_close($ch);

    return strval($data);

}

function download_tiktok_video( $video_url, $video_id, $geturl = false ) {

    $tiktok_dir = set_url_scheme( wp_upload_dir()['basedir'] . '/tiktok-videos' );

    if ( ! file_exists( $tiktok_dir ) ) {
        wp_mkdir_p( set_url_scheme( wp_upload_dir()['basedir'] . '/tiktok-videos' ) );
    }

    $ch = curl_init();
    $headers = array(
        'Range: bytes=0-',
    );

    $options = array(
        CURLOPT_URL            => $video_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => false,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_FOLLOWLOCATION => true,
        CURLINFO_HEADER_OUT    => true,
        CURLOPT_USERAGENT => 'okhttp',
        CURLOPT_ENCODING       => "utf-8",
        CURLOPT_AUTOREFERER    => true,
        CURLOPT_COOKIEJAR      => 'cookie.txt',
        CURLOPT_COOKIEFILE     => 'cookie.txt',
        CURLOPT_REFERER        => 'https://www.tiktok.com/',
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_MAXREDIRS      => 10,
    );

    curl_setopt_array($ch, $options);
    if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) {
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    }

    $data = curl_exec($ch);

    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($geturl === true) {
        return curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    }

    curl_close($ch);

    $filename = $tiktok_dir . '/' . $video_id . ".mp4";

    $d = fopen($filename, "w");

    fwrite($d, $data);

    fclose($d);

    return $filename;

}