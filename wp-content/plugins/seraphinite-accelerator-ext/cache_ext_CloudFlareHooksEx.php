<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

class CloudFlareHooksEx extends \CF\WordPress\Hooks
{
	public function purgeUrl( $url )
	{
		if( !( $host = Gen::GetArrField( Net::UrlParse( $url ), array( 'host' ) ) ) )
			return;

		$bFound = false;
		foreach( Gen::GetArrField( $this -> integrationAPI -> getDomainList(), array( '' ), array() ) as $domain )
		{
			if( Gen::StrEndsWith( $host, $domain ) )
			{
				$bFound = true;
				break;
			}
		}
		if( !$bFound )
			return;

        if( !( $zoneTag = $this -> api -> getZoneTag( $domain ) ) )
            return;

		$files = array( $url );

		foreach( array( 'mobile', 'tablet' ) as $devType )
			$files[] = json_decode( json_encode( array( 'url' => $url, 'headers' => array( 'CF-Device-Type' => $devType ) ), JSON_FORCE_OBJECT ) );

		$this -> api -> zonePurgeFiles( $zoneTag, $files );
	}
}

