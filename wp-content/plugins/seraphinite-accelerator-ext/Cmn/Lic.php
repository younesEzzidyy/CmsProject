<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

class Lic
{
	const API_VER										= 1;

	const DefFeature									= 'full';

	const S_LICMGR_EP_ALREADY_ACTIVE					= 0x00071B8C;
	const S_LICMGR_EP_ALREADY_INACTIVE					= 0x00071B8D;
	const S_LICMGR_LIC_INACTIVE							= 0x00071B8E;
	const S_LICMGR_LIC_EXPIRED							= 0x00071B90;
	const E_LICMGR_LIC_INVALID							= 0x80071B8F;
	const E_LICMGR_LIC_BLOCKED							= 0x80070510;
	const E_LICMGR_UNIT_INVALID							= 0x8007007B;
	const E_LICMGR_EP_ACTIVATION_LIMIT_REACHED			= 0x80070573;
	const E_LICMGR_EP_NOT_ACTIVE						= 0x80071B8D;
	const E_LICMGR_FEATURE_NOT_ACTIVE					= 0x80040112;

	static function GetKeyDisplayText( $key )
	{
		if( !is_string( $key ) )
			return( '' );

		$n = strlen( $key );

		$div_block_size = 5;
		if( $n <= 5 )
			$div_block_size = 2;
		else if( $n <= 10 )
			$div_block_size = 4;

		$div_middle_size = $n % $div_block_size;
		$div_middle_pos = $div_middle_size ? ( intval( $n / $div_block_size ) / 2 * $div_block_size ) : 1000;

		$key_disp = '';
		for( $i = 0, $isep = 0; $i < $n; $i++, $isep++ )
		{
			if( $i == $div_middle_pos )
				$isep = 0;
			else if( $i == $div_middle_pos + $div_middle_size )
				$isep = 0;
			else if( $isep >= $div_block_size )
				$isep = 0;

			if( !$isep && $key_disp )
				$key_disp .= '-';
			$key_disp .= $key[ $i ];
		}

		return( $key_disp );
	}

	static function GetKeyIdFromText( $keyText )
	{
		return( strtoupper( str_replace( array( '-', ' ', '.', '_' ), '', trim( $keyText ) ) ) );
	}
}

