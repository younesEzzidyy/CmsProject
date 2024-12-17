<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

spl_autoload_register(
	function( $class )
	{
		if( strpos( $class, 'seraph_accel\\CloudFlareHooksEx' ) === 0 )
			@include_once( __DIR__ . '/cache_ext_CloudFlareHooksEx.php' );
	}
);

function _CacheExt_SockDoRequest( $addr, $method, $url, $headers = null, $port = null )
{

	$urlComps = Net::UrlParse( $url );
	if( !$urlComps )
		return( Gen::E_INVALIDARG );

	$urlComps[ 'scheme' ] = 'http';
	$urlComps[ 'host' ] = $addr;

	if( $port !== null )
		$urlComps[ 'port' ] = $port;
	else
		unset( $urlComps[ 'port' ] );

	return( Wp::RemoteRequest( $method, Net::UrlDeParse( $urlComps, 0, array( PHP_URL_USER, PHP_URL_PASS, PHP_URL_QUERY, PHP_URL_FRAGMENT ) ), array( 'headers' => $headers, 'sslverify' => false ) ) );
}

function _CacheExt_GetResponseResString( $requestRes, $body = false )
{
	if( is_int( $requestRes ) )
		return( 'Error code: ' . $requestRes );

	if( is_wp_error( $requestRes ) )
	{
		if( $requestRes -> get_error_data( 'url' ) )
			$url = $requestRes -> get_error_data( 'url' );
		return( 'Error: ' . $requestRes -> get_error_message() );
	}

	return( 'HTTP ' . ( isset( $requestRes[ 'method' ] ) ? ( $requestRes[ 'method' ] . ' ' ) : '' ) . wp_remote_retrieve_response_code( $requestRes ) . ' ' . ( string )(isset($requestRes[ 'url' ])?$requestRes[ 'url' ]:null) . ', sent headers: ' . @json_encode( ( array )(isset($requestRes[ 'headers_sent' ])?$requestRes[ 'headers_sent' ]:null) ) . ', response headers: ' . @json_encode( ( array )Net::GetHeadersFromWpRemoteGet( $requestRes ) ) . ', response body: ' . substr( wp_remote_retrieve_body( $requestRes ), 0, 5000 ) );
}

function _CacheExt_Nginx_GetUrlKey( $url, $method = 'GET' )
{
	$urlComps = Net::UrlParse( $url );
	return( md5( Gen::GetArrField( $urlComps, array( 'scheme' ), '' ) . $method . Gen::GetArrField( $urlComps, array( 'host' ), '' ) . Gen::GetArrField( $urlComps, array( 'path' ), '' ) ) );
}

function _CacheExt_Nginx_GetCacheFiles( $keys, $dir, $levels = '1:2' )
{
	$caches = array();
	$levels = explode( ':', $levels );
	foreach( ( array )$keys as $key )
	{
		$path = array();
		$path[] = $dir;
		$offset = 0;

		foreach( $levels as $l )
		{
			$offset = $offset + $l;
			$path[] = substr( $key, 0 - $offset, $l );
		}

		$path[] = $key;
		$caches[] = join( '/', $path );
	}

	return( $caches );
}

function _CacheExt_Nginx_ClearAll( $dir )
{
	Gen::DelDir( $dir, false );
}

function CacheExt_ClearOnExtRequest( $url = null )
{
	$sett = Plugin::SettGet();

	if( isset( $_SERVER[ 'HTTP_X_LSCACHE' ] ) || @preg_match( '@litespeed@i', (isset($_SERVER[ 'SERVER_SOFTWARE' ])?$_SERVER[ 'SERVER_SOFTWARE' ]:'') ) )
	{
		$logInfo = '';
		if( !headers_sent() )
		{
			$hdr = 'X-LiteSpeed-Purge: public,';

				$hdr .= '*';

			if( is_string( $hdr ) )
			{

				header( $hdr );
				$logInfo .= 'Executed, sent \'' . $hdr . '\' header';
			}
		}
		else
			$logInfo .= 'Skipped, headers already sent';

		if( (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'srvClr' ])?$sett[ 'logScope' ][ 'srvClr' ]:null) )
			LogWrite( 'LiteSpeed: ' . $logInfo, Ui::MsgInfo, 'Server/cloud cache update' );
	}
}

function CacheExt_Clear( $url = null )
{
	$sett = Plugin::SettGet();
	$hostname = gethostname();

	if( ( defined( 'O2SWITCH_VARNISH_PURGE_KEY' ) ) )
	{
		$requestRes = Gen::E_INVALIDARG;
		if( $url )
		{
            if( $urlComps = Net::UrlParse( $url ) )
			{
				$urlComps[ 'scheme' ] = 'https';
				$urlPurge = Net::UrlDeParse( $urlComps, 0, array( PHP_URL_QUERY, PHP_URL_FRAGMENT ) );
				if( isset( $urlComps[ 'query' ] ) )
					$requestRes = _CacheExt_SockDoRequest( $_SERVER[ 'SERVER_ADDR' ], 'PURGE', $urlPurge, array( 'X-Purge-Regex' => '.*', 'X-VC-Purge-Key' => @constant( 'O2SWITCH_VARNISH_PURGE_KEY' ) ) );
				else
					$requestRes = _CacheExt_SockDoRequest( $_SERVER[ 'SERVER_ADDR' ], 'PURGE', $urlPurge, array( 'X-Purge-Method' => 'default', 'X-VC-Purge-Key' => @constant( 'O2SWITCH_VARNISH_PURGE_KEY' ) ) );
			}
		}
		else
			$requestRes = _CacheExt_SockDoRequest( $_SERVER[ 'SERVER_ADDR' ], 'PURGE', Wp::GetSiteRootUrl(), array( 'X-Purge-Regex' => '.*', 'X-VC-Purge-Key' => @constant( 'O2SWITCH_VARNISH_PURGE_KEY' ) ) );

		if( (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'srvClr' ])?$sett[ 'logScope' ][ 'srvClr' ]:null) )
			LogWrite( 'O2Switch: ' . _CacheExt_GetResponseResString( $requestRes ), Ui::MsgInfo, 'Server/cloud cache update' );
	}

	if( $dir = trim( Gen::GetArrField( $sett, array( 'cache', 'nginx', 'fastCgiDir' ), '' ) ) )
	{
		$logInfo = '';

		if( $url )
		{
			foreach( _CacheExt_Nginx_GetCacheFiles( _CacheExt_Nginx_GetUrlKey( $url ), $dir, Gen::GetArrField( $sett, array( 'cache', 'nginx', 'fastCgiLevels' ), '' ) ) as $cache )
			{
				if( !@is_file( $cache ) )
					continue;

				@unlink( $cache );

				if( $logInfo )
					$logInfo .= ', ';
				$logInfo .= '\'' . $cache . '\'';
			}

			$logInfo = 'File(s) for URL \'' . $url . '\' deleted: ' . $logInfo;
		}
		else
		{
			_CacheExt_Nginx_ClearAll( $dir );
			$logInfo = 'Directory \'' . $dir . '\' cleared';
		}

		if( (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'srvClr' ])?$sett[ 'logScope' ][ 'srvClr' ]:null) )
			LogWrite( 'Nginx: ' . $logInfo, Ui::MsgInfo, 'Server/cloud cache update' );
	}

	else if( Gen::DoesFuncExist( '\\NginxChampuru::get_instance' ) && Gen::DoesFuncExist( '\\NginxChampuru::get_cache_dir' ) && Gen::DoesFuncExist( '\\NginxChampuru::get_cache_key' ) && Gen::DoesFuncExist( '\\NginxChampuru::get_cache' ) )
	{
		$logInfo = '';

		if( $instance = \NginxChampuru::get_instance() )
		{
			if( $url )
			{
				add_filter( 'nginxchampuru_get_reverse_proxy_key', 'seraph_accel\\_CacheExt_Nginx_GetUrlKey', 99999 );

				foreach( ( array )$instance -> get_cache( $instance -> get_cache_key( $url ), $url ) as $cache )
				{
					if( !@is_file( $cache ) )
						continue;

					@unlink( $cache );

					if( $logInfo )
						$logInfo .= ', ';
					$logInfo .= '\'' . $cache . '\'';
				}

				$logInfo = 'NginxChampuru: File(s) for URL \'' . $url . '\' deleted: ' . $logInfo;
			}
			else if( $dir = $instance -> get_cache_dir() )
			{
				_CacheExt_Nginx_ClearAll( $dir );
				$logInfo = 'NginxChampuru: Directory \'' . $dir . '\' cleared';
			}
			else
				$logInfo = 'NginxChampuru: void';
		}
		else
			$logInfo = 'NginxChampuru: no instance';

		if( (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'srvClr' ])?$sett[ 'logScope' ][ 'srvClr' ]:null) )
			LogWrite( 'Nginx: ' . $logInfo, Ui::MsgInfo, 'Server/cloud cache update' );
	}

	else if( Gen::DoesFuncExist( '\\NginxCache::purge_zone_once' ) )
	{

		$logInfo = '';

		if( $dir = trim( get_option( 'nginx_cache_path' ) ) )
		{
			if( $url )
			{
				foreach( _CacheExt_Nginx_GetCacheFiles( _CacheExt_Nginx_GetUrlKey( $url ), $dir ) as $cache )
				{
					if( !@is_file( $cache ) )
						continue;

					@unlink( $cache );

					if( $logInfo )
						$logInfo .= ', ';
					$logInfo .= '\'' . $cache . '\'';
				}

				$logInfo = 'NginxCache: File(s) for URL \'' . $url . '\' deleted: ' . $logInfo;
			}
			else
			{
				_CacheExt_Nginx_ClearAll( $dir );
				$logInfo = 'NginxCache: Directory \'' . $dir . '\' cleared';
			}
		}
		else
			$logInfo = 'NginxCache: no dir';

		if( (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'srvClr' ])?$sett[ 'logScope' ][ 'srvClr' ]:null) )
			LogWrite( 'Nginx: ' . $logInfo, Ui::MsgInfo, 'Server/cloud cache update' );
	}

	else if( Gen::DoesFuncExist( '\\Purger::purge_all' ) && Gen::DoesFuncExist( '\\Purger::purge_url' ) )
	{
		$logInfo = '';

		global $nginx_purger;
		if( $nginx_purger )
		{
			if( $url )
			{
				$nginx_purger -> purge_url( $url );
				$logInfo = 'Purger: URL \'' . $url . '\' purged';
			}
			else
			{
				$nginx_purger -> purge_all();
				$logInfo = 'Purger: Purged all';
			}
		}
		else
			$logInfo = 'Purger: no instance';

		if( (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'srvClr' ])?$sett[ 'logScope' ][ 'srvClr' ]:null) )
			LogWrite( 'Nginx: ' . $logInfo, Ui::MsgInfo, 'Server/cloud cache update' );
	}

	if( Gen::DoesFuncExist( '\\CF\\WordPress\\Hooks::purgeCacheEverything' ) )
	{
		$logInfo = '';

		if( $url )
		{
			( new CloudFlareHooksEx() ) -> purgeUrl( $url );
			$logInfo = 'URL \'' . $url . '\' purged';
		}
		else
		{
			( new CloudFlareHooksEx() ) -> purgeCacheEverything();
			$logInfo = 'Purged all';
		}

		if( (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'srvClr' ])?$sett[ 'logScope' ][ 'srvClr' ]:null) )
			LogWrite( 'CloudFlare: ' . $logInfo, Ui::MsgInfo, 'Server/cloud cache update' );
	}

	if( Gen::DoesFuncExist( '\\WPNCEasyWP\\Http\\Varnish\\VarnishCache::boot' ) )
	{
		$logInfo = '';

		if( $varnish = \WPNCEasyWP\Http\Varnish\VarnishCache::boot() )
		{

			    $varnish -> clearAll();
			$logInfo = 'Varnish cache cleared';
		}

		wp_cache_flush();

		if( $logInfo )
			$logInfo .= ', ';
		$logInfo .= 'WP cache flushed';

		if( (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'srvClr' ])?$sett[ 'logScope' ][ 'srvClr' ]:null) )
			LogWrite( 'EasyWP: ' . $logInfo, Ui::MsgInfo, 'Server/cloud cache update' );
	}

	if( ( isset( $_SERVER[ 'cw_allowed_ip' ] ) || @preg_match( '@/home/.*?cloudways.*@', __DIR__ ) ) )
	{
		if( !$url )
			$url = Wp::GetSiteRootUrl( '.*' );

		$urlComps = Net::UrlParse( $url );
		$requestRes = $urlComps ? _CacheExt_SockDoRequest( '127.0.0.1', 'PURGE', $url, array( 'Host' => $urlComps[ 'host' ], 'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36' ), 8080 ) : Gen::E_INVALIDARG;

		if( (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'srvClr' ])?$sett[ 'logScope' ][ 'srvClr' ]:null) )
			LogWrite( 'CloudWays: ' . _CacheExt_GetResponseResString( $requestRes ), Ui::MsgInfo, 'Server/cloud cache update' );
	}

	if( ( @preg_match( '@^dp-.+@', $hostname ) ) )
	{
		$requestRes = Gen::E_INVALIDARG;
		if( $url )
		{
            if( $urlComps = Net::UrlParse( $url ) )
			{
				$urlComps[ 'scheme' ] = 'https';
				$urlPurge = Net::UrlDeParse( $urlComps, 0, array( PHP_URL_QUERY, PHP_URL_FRAGMENT ) );
				if( isset( $urlComps[ 'query' ] ) )
					$requestRes = _CacheExt_SockDoRequest( $_SERVER[ 'SERVER_ADDR' ], 'PURGE', $urlPurge . '.*', array( 'x-purge-method' => 'regex' ) );
				else
					$requestRes = _CacheExt_SockDoRequest( $_SERVER[ 'SERVER_ADDR' ], 'PURGE', $urlPurge, array( 'x-purge-method' => 'default' ) );
			}
		}
		else
			$requestRes = _CacheExt_SockDoRequest( $_SERVER[ 'SERVER_ADDR' ], 'PURGE', Wp::GetSiteRootUrl( '.*' ), array( 'x-purge-method' => 'regex' ) );

		if( (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'srvClr' ])?$sett[ 'logScope' ][ 'srvClr' ]:null) )
			LogWrite( 'DreamHost: ' . _CacheExt_GetResponseResString( $requestRes ), Ui::MsgInfo, 'Server/cloud cache update' );
	}

	if( Gen::DoesFuncExist( '\\WPaaS\\Plugin::vip' ) )
	{
		$urlPurge = $url;
		if( !$urlPurge )
			$urlPurge = Wp::GetSiteRootUrl();
		$urlPurge = preg_replace( '@^https://@', 'http://', $urlPurge );

		update_option( 'gd_system_last_cache_flush', time() );
		$requestRes = _CacheExt_SockDoRequest( \WPaaS\Plugin::vip(), 'BAN', $urlPurge );

		if( (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'srvClr' ])?$sett[ 'logScope' ][ 'srvClr' ]:null) )
			LogWrite( 'GoDaddy: ' . _CacheExt_GetResponseResString( $requestRes ), Ui::MsgInfo, 'Server/cloud cache update' );
	}

	if( ( defined( 'KINSTAMU_VERSION' ) ) )
	{
		$requestRes = Gen::E_INVALIDARG;

		if( $url )
		{
            if( $urlComps = Net::UrlParse( $url ) )
				$requestRes = Wp::RemoteGet( Net::UrlDeParse( $urlComps, 0, array( PHP_URL_PATH, PHP_URL_QUERY, PHP_URL_FRAGMENT ) ) . '/kinsta-clear-cache' . ( isset( $urlComps[ 'path' ] ) ? $urlComps[ 'path' ] : '/' ), array( 'timeout' => 5, 'sslverify' => false ) );

		}
		else
			$requestRes = Wp::RemoteGet( 'https://localhost/kinsta-clear-cache-all', array( 'timeout' => 5, 'sslverify' => false ) );

		if( (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'srvClr' ])?$sett[ 'logScope' ][ 'srvClr' ]:null) )
			LogWrite( 'Kinsta: ' . _CacheExt_GetResponseResString( $requestRes ), Ui::MsgInfo, 'Server/cloud cache update' );
	}

	if( Gen::DoesFuncExist( '\\PagelyCachePurge::purgePath' ) )
	{
		$logInfo = '';

		if( $url )
		{
			$logInfo = 'URL \'' . $url . '\' invalid';
            if( $urlComps = Net::UrlParse( $url ) )
				if( isset( $urlComps[ 'path' ] ) )
				{
					( new \PagelyCachePurge() ) -> purgePath( $urlComps[ 'path' ] . '(.*)' );
					$logInfo = 'URL \'' . $url . '\' purged';
				}
		}
		else
		{
			( new \PagelyCachePurge() ) -> purgeAll();
			$logInfo = 'Purged all';
		}

		if( (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'srvClr' ])?$sett[ 'logScope' ][ 'srvClr' ]:null) )
			LogWrite( 'Pagely: ' . $logInfo, Ui::MsgInfo, 'Server/cloud cache update' );
	}

	if( ( isset( $_SERVER[ 'PRESSABLE_PROXIED_REQUEST' ] ) || strpos( $hostname, 'atomicsites.net' ) !== false ) )
	{
		$logInfo = '';

		if( $url )
		{
			global $batcache;

			if( $batcache )
			{
				$urlComps = Net::UrlParse( $url, Net::URLPARSE_F_QUERY );
				if( $urlComps && isset( $urlComps[ 'host' ] ) )
				{
					if( isset( $batcache -> ignored_query_args ) )
						foreach( $batcache -> ignored_query_args as $arg )
							unset( $urlComps[ 'query' ][ $arg ] );
					ksort( $urlComps[ 'query' ] );

					$keys = array(
						'host' => (isset($urlComps[ 'host' ])?$urlComps[ 'host' ]:''),
						'method' => 'GET',
						'path' => (isset($urlComps[ 'path' ])?$urlComps[ 'path' ]:''),
						'query' => (isset($urlComps[ 'query' ])?$urlComps[ 'query' ]:''),
						'extra' => array()
					);

					if( isset( $batcache -> origin ) )
						$keys[ 'origin' ] = $batcache -> origin;

					if( (isset($urlComps[ 'scheme' ])?$urlComps[ 'scheme' ]:'') == 'https' )
						$keys[ 'ssl' ] = true;

					wp_cache_init();
					$batcache -> configure_groups();

					foreach( array( 'mobile', 'tablet', 'desktop' ) as $deviceType )
					{
						$keys[ 'extra' ] = array( $deviceType );
						wp_cache_delete( md5( serialize( $keys ) ), $batcache -> group );
					}

					$logInfo = 'URL \'' . $url . '\' purged';
				}
				else
					$logInfo = 'URL \'' . $url . '\' invalid';
			}
			else
				$logInfo = 'No instance';
		}
		else
		{
			wp_cache_flush();
			$logInfo = 'Purged all';
		}

		if( (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'srvClr' ])?$sett[ 'logScope' ][ 'srvClr' ]:null) )
			LogWrite( 'Pressable: ' . $logInfo, Ui::MsgInfo, 'Server/cloud cache update' );
	}

	if( Gen::DoesFuncExist( '\\CDN_Clear_Cache_Api::cache_api_call' ) )
	{
		$logInfo = '';

		if( $url )
		{
            if( $urlComps = Net::UrlParse( $url ) )
			{
				\CDN_Clear_Cache_Api::cache_api_call( array( Net::UrlDeParse( $urlComps, 0, array(), array( PHP_URL_PATH, PHP_URL_QUERY ) ) ), 'purge' );
				$logInfo = 'URL \'' . $url . '\' purged';
			}
			else
				$logInfo = 'URL \'' . $url . '\' invalid';
		}
		else if( Gen::DoesFuncExist( 'CDN_Clear_Cache_Hooks::purge_cache' ) )
		{
			\CDN_Clear_Cache_Hooks::purge_cache();
			$logInfo = 'Purged all';
		}
		else
			$logInfo = 'Invalid state';

		if( (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'srvClr' ])?$sett[ 'logScope' ][ 'srvClr' ]:null) )
			LogWrite( 'RocketNet: ' . $logInfo, Ui::MsgInfo, 'Server/cloud cache update' );
	}

	if( ( strpos( (isset($_SERVER[ 'WARPDRIVE_API' ])?$_SERVER[ 'WARPDRIVE_API' ]:''), '//api.savvii.services' ) !== false ) )
	{
		$requestRes = Gen::E_INVALIDARG;

		if( $url )
		{
            if( $urlComps = Net::UrlParse( $url ) )
				$requestRes = Wp::RemoteRequest( 'PURGE', Net::UrlDeParse( $urlComps ) . 'purge', array( 'sslverify' => false, 'headers' => array( 'X-PURGE-HOST' => (isset($urlComps[ 'host' ])?$urlComps[ 'host' ]:null), 'X-PURGE-PATH-REGEX' => (isset($urlComps[ 'path' ])?$urlComps[ 'path' ]:'') . '.*' ) ) );
		}
		else
		{
            if( $urlComps = Net::UrlParse( Wp::GetSiteRootUrl() ) )
				$requestRes = Wp::RemoteRequest( 'PURGE', Net::UrlDeParse( $urlComps ) . 'purge', array( 'sslverify' => false, 'headers' => array( 'X-PURGE-HOST' => (isset($urlComps[ 'host' ])?$urlComps[ 'host' ]:null) ) ) );
		}

		if( (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'srvClr' ])?$sett[ 'logScope' ][ 'srvClr' ]:null) )
			LogWrite( 'Savvii: ' . _CacheExt_GetResponseResString( $requestRes ), Ui::MsgInfo, 'Server/cloud cache update' );
	}

	if( ( defined( 'SiteGround_Optimizer\\VERSION' ) || strpos( $hostname, 'siteground.eu' ) !== false ) )
	{
		if( function_exists( 'sg_cachepress_purge_cache' ) )
		{
			$logInfo = '';

			if( Gen::DoesFuncExist( '\\SiteGround_Optimizer\\Supercacher\\Supercacher::get_instance' ) && Gen::DoesFuncExist( '\\SiteGround_Optimizer\\Supercacher\\Supercacher::purge_cache_request' ) && Gen::DoesFuncExist( '\\SiteGround_Optimizer\\Supercacher\\Supercacher::purge_cache' ) )
			{
				if( $url )
					\SiteGround_Optimizer\Supercacher\Supercacher::get_instance() -> purge_cache_request( $url, false );
				else
					\SiteGround_Optimizer\Supercacher\Supercacher::purge_cache();
			}

			if( $url )
			{
				sg_cachepress_purge_cache( $url );
				$logInfo = 'URL \'' . $url . '\' purged';
			}
			else if( function_exists( 'sg_cachepress_purge_everything' ) )
			{
				sg_cachepress_purge_everything();
				$logInfo = 'Purged all';
			}
			else
				$logInfo = 'Invalid state';

			wp_cache_flush();

			if( Gen::DoesFuncExist( '\\SiteGround_Optimizer\\Supercacher\\Supercacher::delete_assets' ) )
				\SiteGround_Optimizer\Supercacher\Supercacher::delete_assets();

			if( (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'srvClr' ])?$sett[ 'logScope' ][ 'srvClr' ]:null) )
				LogWrite( 'SiteGround: ' . $logInfo, Ui::MsgInfo, 'Server/cloud cache update' );
		}
		else
		{
			$requestRes = Gen::E_INVALIDARG;

			if( $url )
			{
				if( $urlComps = Net::UrlParse( $url ) )
				{
					$urlComps[ 'scheme' ] = 'http';
					$urlPurge = Net::UrlDeParse( $urlComps, 0, array( PHP_URL_QUERY, PHP_URL_FRAGMENT ) );
					if( isset( $urlComps[ 'query' ] ) )
						$urlPurge .= '(.*)';
					$requestRes = _CacheExt_SockDoRequest( '127.0.0.1', 'PURGE', $urlPurge );
				}
			}
			else
				$requestRes = _CacheExt_SockDoRequest( '127.0.0.1', 'PURGE', Wp::GetSiteRootUrl( '/(.*)' ) );

			if( (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'srvClr' ])?$sett[ 'logScope' ][ 'srvClr' ]:null) )
				LogWrite( 'SiteGround: ' . _CacheExt_GetResponseResString( $requestRes ), Ui::MsgInfo, 'Server/cloud cache update' );
		}
	}

	if( ( isset( $_SERVER[ 'HTTP_X_ZXCS_VHOST' ] ) && ( strpos( $hostname, 'zxcs' ) !== false ) ) )
	{
		$urlPurge = $url;
		if( !$urlPurge )
			$urlPurge = Wp::GetSiteRootUrl() . '?purgeAll';

		$requestRes = Wp::RemoteRequest( 'PURGE', $urlPurge, array( 'sslverify' => false, 'headers' => array( 'X-Purge-ZXCS' => 'true', 'host-ZXCS' => (isset($_SERVER[ 'HTTP_HOST' ])?$_SERVER[ 'HTTP_HOST' ]:'') ) ) );

		if( (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'srvClr' ])?$sett[ 'logScope' ][ 'srvClr' ]:null) )
			LogWrite( 'Vimexx: ' . _CacheExt_GetResponseResString( $requestRes ), Ui::MsgInfo, 'Server/cloud cache update' );
	}

	if( Gen::DoesFuncExist( '\\WpeCommon::purge_varnish_cache' ) )
	{
		$logInfo = '';

		try
		{
			if( $url )
			{
				$ctx = new AnyObj();
				$ctx -> urlComps = Net::UrlParse( $url );

				if( $ctx -> urlComps )
				{
					$ctx -> cb =
						function( $ctx, $paths )
						{
							if( count( $paths ) == 1 && $paths[ 0 ] == '.*' )
								$paths = array( Net::UrlDeParse( $ctx -> urlComps, 0, array(), array( PHP_URL_PATH, PHP_URL_QUERY ) ) );
							return( $paths );
						};

					add_filter( 'wpe_purge_varnish_cache_paths', array( $ctx, 'cb' ) );
					\WpeCommon::purge_varnish_cache();
					remove_filter( 'wpe_purge_varnish_cache_paths', array( $ctx, 'cb' ) );

					$logInfo = 'URL \'' . $url . '\' Varnish cache purged';
				}
				else
					$logInfo = 'URL \'' . $url . '\' invalid';
			}
			else
			{
                \WpeCommon::purge_varnish_cache();

				$logInfo = 'Purged all Varnish cache';
			}
        }
		catch( \Exception $e )
		{
			$logInfo = ( string )$e;
		}

		if( (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'srvClr' ])?$sett[ 'logScope' ][ 'srvClr' ]:null) )
			LogWrite( 'WPEngine: ' . $logInfo, Ui::MsgInfo, 'Server/cloud cache update' );
	}

	global $wpaas_cache_class;
	if( ( Gen::DoesFuncExist( '\\WPaaS\\Cache_V2::ban' ) && $wpaas_cache_class ) )
	{
		$logInfo = '';

		if( $url )
		{
			$wpaas_cache_class -> purge( array( $url ) );
			$wpaas_cache_class -> flush_cdn();

			$logInfo = 'URL \'' . $url . '\' purged';
		}
		else
		{
			$wpaas_cache_class -> ban();
			$wpaas_cache_class -> flush_transients();
			$wpaas_cache_class -> flush_ob();

			$logInfo = 'Purged all';
		}

		if( (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'srvClr' ])?$sett[ 'logScope' ][ 'srvClr' ]:null) )
			LogWrite( 'WPAAS: ' . $logInfo, Ui::MsgInfo, 'Server/cloud cache update' );
	}

	if( ( defined( 'WPCOMSH_VERSION' ) ) )
	{
		wp_cache_flush();

		if( (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'srvClr' ])?$sett[ 'logScope' ][ 'srvClr' ]:null) )
			LogWrite( 'WordPress.Com: Flushed', Ui::MsgInfo, 'Server/cloud cache update' );
	}

	if( Gen::DoesFuncExist( '\\Tenweb_Manager\\Helper::clear_cache' ) )
	{
		\Tenweb_Manager\Helper::clear_cache();

		if( (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'srvClr' ])?$sett[ 'logScope' ][ 'srvClr' ]:null) )
			LogWrite( '10web (Tenweb): Cleared', Ui::MsgInfo, 'Server/cloud cache update' );
	}

	if( ( defined( 'CLP_VARNISH_VERSION' ) ) )
	{
		if( !Gen::DoesFuncExist( 'ClpVarnishCacheManager::purge_url' ) )
			@include_once( __DIR__ . '/../clp-varnish-cache/class.varnish-cache-manager.php' );

		$logInfo = '';

		if( $url )
		{
			if( Gen::DoesFuncExist( 'ClpVarnishCacheManager::purge_url' ) )
			{
				try
				{
					$clp_varnish_cache_manager = new \ClpVarnishCacheManager();
					$clp_varnish_cache_manager -> purge_url( $url );

					$logInfo = 'URL \'' . $url . '\' purged';
				}
				catch( \Exception $e )
				{
					$logInfo = $ex -> getMessage();
				}
				unset( $clp_varnish_cache_manager );
			}
			else
				$logInfo = 'Invalid state';
		}
		else
		{
			if( Gen::DoesFuncExist( 'ClpVarnishCacheManager::purge_host' ) && Gen::DoesFuncExist( 'ClpVarnishCacheManager::purge_tag' ) )
			{
				try
				{
					$clp_varnish_cache_manager = new \ClpVarnishCacheManager();

					{
						$host = Wp::SanitizeText( (isset($_SERVER[ 'HTTP_HOST' ])?$_SERVER[ 'HTTP_HOST' ]:null) );
						if( !empty( $host ) )
							$clp_varnish_cache_manager -> purge_host( $host );
						unset( $host );
					}

					{
						$cache_tag_prefix = $clp_varnish_cache_manager -> get_cache_tag_prefix();
						if( !empty( $cache_tag_prefix ) )
							$clp_varnish_cache_manager -> purge_tag( $cache_tag_prefix );
						unset( $cache_tag_prefix );
					}

					$logInfo = 'Purged all';
				}
				catch( \Exception $e )
				{
					$logInfo = $ex -> getMessage();
				}
				unset( $clp_varnish_cache_manager );
			}
			else
				$logInfo = 'Invalid state';
		}

		if( (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'srvClr' ])?$sett[ 'logScope' ][ 'srvClr' ]:null) )
			LogWrite( 'CloudPanel: ' . $logInfo, Ui::MsgInfo, 'Server/cloud cache update' );
	}

	if( ( Gen::DoesFuncExist( 'Endurance_Page_Cache::purge_all' ) ) )
	{
		$logInfo = '';

		global $epc;

		if( $url )
		{
			if( Gen::DoesFuncExist( 'Endurance_Page_Cache::purge_single' ) )
			{
				try
				{
					$epc -> purge_single( $url );

					$logInfo = 'URL \'' . $url . '\' purged';
				}
				catch( \Exception $e )
				{
					$logInfo = $ex -> getMessage();
				}
				unset( $clp_varnish_cache_manager );
			}
			else
				$logInfo = 'Invalid state';
		}
		else
		{
			try
			{
				$epc -> purge_all();

				$logInfo = 'Purged all';
			}
			catch( \Exception $e )
			{
				$logInfo = $ex -> getMessage();
			}
			unset( $clp_varnish_cache_manager );
		}

		if( (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'srvClr' ])?$sett[ 'logScope' ][ 'srvClr' ]:null) )
			LogWrite( 'Endurance Page Cache: ' . $logInfo, Ui::MsgInfo, 'Server/cloud cache update' );
	}

	if( Gen::GetArrField( $sett, array( 'cache', 'sucuri', 'apiKey' ), '' ) )
	{

		if( $url )
		{
			$url = Net::UrlParse( $url );
			$url = Gen::GetArrField( $url, array( 'path' ), '' );
		}

		$requestRes = Wp::RemoteRequest( 'POST', 'https://waf.sucuri.net/api?v2', array( 'body' => 'k=' . Gen::GetArrField( $sett, array( 'cache', 'sucuri', 'apiKey' ), '' ) . '&s=' . Gen::GetArrField( $sett, array( 'cache', 'sucuri', 'apiSecret' ), '' ) . '&a=clear_cache' . ( $url ? ( '&file=' . rawurlencode( $url ) ) : '' ), 'headers' => array( 'Content-Type' => 'application/x-www-form-urlencoded' ), 'sslverify' => false ) );
		if( (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'srvClr' ])?$sett[ 'logScope' ][ 'srvClr' ]:null) )
			LogWrite( 'Sucuri: ' . _CacheExt_GetResponseResString( $requestRes ), Ui::MsgInfo, 'Server/cloud cache update' );
	}

	if( ( Gen::DoesFuncExist( 'pantheon_clear_edge_all' ) ) )
	{
		$logInfo = '';

		if( $url )
		{
			if( Gen::DoesFuncExist( 'pantheon_clear_edge_paths' ) )
			{
				try
				{
					$url = Net::UrlParse( $url );
					$url = Gen::GetArrField( $url, array( 'path' ), '' );
					pantheon_clear_edge_paths( array( $url ) );

					$logInfo = 'URL \'' . $url . '\' purged';
				}
				catch( \Exception $e )
				{
					$logInfo = $ex -> getMessage();
				}
			}
			else
				$logInfo = 'Invalid state';
		}
		else
		{
			try
			{
				pantheon_clear_edge_all();

				$logInfo = 'Purged all';
			}
			catch( \Exception $e )
			{
				$logInfo = $ex -> getMessage();
			}
		}

		if( (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'srvClr' ])?$sett[ 'logScope' ][ 'srvClr' ]:null) )
			LogWrite( 'Pantheon Cache: ' . $logInfo, Ui::MsgInfo, 'Server/cloud cache update' );
	}
}

