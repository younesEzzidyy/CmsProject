<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

require( __DIR__ . '/Lic.php' );

class LicCli
{
	const Act_Activate					= 'activate';
	const Act_Deactivate				= 'deactivate';
	const Act_Check						= 'check';

	static function Action( $srvUrl, $apiSecret, $action, $key, $unitId, $unitVer, $endpointId, $endpointName = NULL )
	{
		$res = array( 'hr' => Gen::E_FAIL, 'response' => NULL );

		$url = self::_GetRequestUrl( $srvUrl, $apiSecret, $action, $key, $unitId, $unitVer, $endpointId, $endpointName );

		$requestRes = wp_remote_get( $url, array( 'timeout' => 30 ) );

		$res[ 'hr' ] = Net::GetHrFromWpRemoteGet( $requestRes );
		if( $res[ 'hr' ] != Gen::S_OK )
			return( $res );

		$res[ 'response' ] = json_decode( wp_remote_retrieve_body( $requestRes ), true );
		if( !is_array( $res[ 'response' ] ) )
		{
			$res[ 'response' ] = NULL;
			$res[ 'hr' ] = Gen::E_DATACORRUPTED;
		}

		return( $res );
	}

	static function GetDataUrl( $srvUrl, $apiSecret, $key, $unitId, $unitVer, $endpointId, $feature, $item )
	{
		return( self::_GetRequestUrl( $srvUrl, $apiSecret, 'getdata', $key, $unitId, $unitVer, $endpointId, NULL, $feature, $item ) );
	}

	static function _GetRequestUrl( $srvUrl, $apiSecret, $action, $key, $unitId, $unitVer, $endpointId, $endpointName = NULL, $feature = NULL, $item = NULL )
	{
		$srvUrl .= '/v' . Lic::API_VER;

		$args = array( 'secret' => $apiSecret, 'key' => $key, 'unit' => $unitId, 'unit_ver' => $unitVer, 'endpoint' => $endpointId );
		if( $endpointName )
			$args[ 'endpoint_name' ] = $endpointName;
		if( $feature )
			$args[ 'feature' ] = $feature;

		$args = array( 'action' => $action, 'args' => @rawurlencode( @base64_encode( @json_encode( $args ) ) ) );
		if( $item )
			$args[ 'item' ] = $item;

		return( add_query_arg( $args, $srvUrl ) );
	}
}

