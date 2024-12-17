<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

class Img
{

	const WEBP_QUALITY_DEF		= 80;

	const PNG_COMPRESSION_DEF	= -1;
	const PNG_COMPRESSION_NO	= 0;
	const PNG_COMPRESSION_LOW	= 1;
	const PNG_COMPRESSION_HIGH	= 9;
	const PNG_QUALITY_DEF		= 100;
	const PNG_SPEED_LOW			= 1;
	const PNG_SPEED_DEF			= 6;
	const PNG_SPEED_HIGH		= 11;

	const AVIF_QUALITY_DEF		= 52;
	const AVIF_SPEED_DEF		= 6;

	static function SetConvertExtToolFile( $name, $file = null )
	{
		if( $file !== null )
			self::$_toolPaths[ $name ] = $file;
		else
			unset( self::$_toolPaths[ $name ] );
	}

	static function GetInfoFromFile( $file, $ext = false )
	{
		$data = @file_get_contents( $file );
		return( $data !== false ? Img::GetInfoFromData( $data, $ext ) : null );
	}

	static function IsMimeRaster( $mime )
	{
		return( $mime != 'image/svg+xml' );
	}

	static function ExtractSizeFromSvgTag( $ndRoot, &$info )
	{
		foreach( array( 'cx' => 'width', 'cy' => 'height' ) as $sz => $prop )
		{
			$v = HtmlNd::GetAttrVal( $ndRoot, $prop );

			if( is_string( $v ) && Gen::StrEndsWith( $v, 'px' ) )
				$info[ $sz ] = ( int )substr( $v, 0, -2 );
			else if( is_numeric( $v ) )
				$info[ $sz ] = ( int )$v;
			else
				$info[ $sz ] = null;

			$info[ $prop ] = $v;
		}

		$viewBox = HtmlNd::GetAttrVal( $ndRoot, 'viewBox' );
		if( $viewBox === null )
			$viewBox = HtmlNd::GetAttrVal( $ndRoot, 'viewbox' );

		$info[ 'viewBox' ] = $viewBox;

		if( $info[ 'cx' ] === null && $info[ 'cy' ] === null )
		{

			if( $viewBox !== null && preg_match( '@^\\s*([\\d\\.]+)\\s+([\\d\\.]+)\\s+([\\d\\.]+)\\s+([\\d\\.]+)\\s*$@', $viewBox, $m ) )
			{
				$info[ 'cx' ] = ( float )$m[ 3 ];
				$info[ 'cy' ] = ( float )$m[ 4 ];
			}
		}
	}

	static function GetInfoFromData( $data, $ext = false )
	{
		$infoEx = $ext ? array() : null;

		if( gettype( $data ) == 'string' && @preg_match( '@<svg[>\\s]@i', $data ) && @preg_match( '@</svg>@i', $data ) )
		{
			$info = array( 'mime' => 'image/svg+xml', 'cx' => null, 'cy' => null );

			$doc = new \DOMDocument();
			if( !@$doc -> loadXML( $data, LIBXML_COMPACT | LIBXML_NOERROR | LIBXML_NONET | LIBXML_NOWARNING | LIBXML_PARSEHUGE ) )
				return( $info );

			$ndRoot = HtmlNd::FindByTag( $doc, 'svg' );
			if( !$ndRoot )
				return( $info );

			Img::ExtractSizeFromSvgTag( $ndRoot, $info );
			return( $info );
		}

		if( !function_exists( 'getimagesizefromstring' ) )
			return( null );

		$info = @getimagesizefromstring( $data, $infoEx );
		if( !$info )
			return( null );

		$info = array( 'cx' => $info[ 0 ], 'cy' => $info[ 1 ], 'mime' => $info[ 'mime' ] );

		if( !$ext )
			return( $info );

		switch( $info[ 'mime' ] )
		{
		case 'image/gif':
			$info[ 'dpiX' ] = 96;
			$info[ 'dpiY' ] = 96;
			break;

		case 'image/png':

			$buf = array();

			$x = 0;
			$y = 0;
			$units = 0;

			for( $i = 0; $i < strlen( $data ); $i++ )
			{
				array_push( $buf, ord( $data[ $i ] ) );
				if( count( $buf ) > 13 )
					array_shift( $buf );
				if( count( $buf ) < 13 )
					continue;

				if( $buf[ 0 ] == ord( 'p' ) &&
					$buf[ 1 ] == ord( 'H' ) &&
					$buf[ 2 ] == ord( 'Y' ) &&
					$buf[ 3 ] == ord( 's' ) )
				{
					$x = ( $buf[ 4 ] << 24 ) + ( $buf[ 5 ] << 16 ) + ( $buf[ 6 ] << 8 ) + $buf[ 7 ];
					$y = ( $buf[ 8 ] << 24 ) + ( $buf[ 9 ] << 16 ) + ( $buf[ 10 ] << 8 ) + $buf[ 11 ];
					$units = $buf[ 12 ];
					break;
				}
			}

			switch( $units )
			{
			case 0:
				$info[ 'dpiX' ] = 96;
				$info[ 'dpiY' ] = intval( round( $info[ 'dpiX' ] * ( $x && $y ? ( $x / $y ) : 1 ) ) );
				break;

			case 1:
				$info[ 'dpiX' ] = intval( round( $x * 0.0254 ) );
				$info[ 'dpiY' ] = intval( round( $y * 0.0254 ) );
				break;
			}

			break;

		case 'image/tiff':
			break;

		case 'image/x-ms-bmp':
		case 'image/bmp':

			$data = substr( $data, 14 + 24, 8 );
			$info[ 'dpiX' ] = intval( @hexdec( @bin2hex( strrev( substr( $data, 0, 4 ) ) ) ) * 0.0254 );
			$info[ 'dpiY' ] = intval( @hexdec( @bin2hex( strrev( substr( $data, 4, 4 ) ) ) ) * 0.0254 );
			break;

		case 'image/jpeg':

			if( !(isset($infoEx[ 'APP0' ])?$infoEx[ 'APP0' ]:null) )
				$infoEx[ 'APP0' ] = substr( $data, 6, 20 - 6 );

			$data = @bin2hex( substr( $infoEx[ 'APP0' ], 8, 4 ) );

			$x = @hexdec( substr( $data, 0, 4 ) );
			$y = @hexdec( substr( $data, 4, 4 ) );
			$units = @hexdec( @bin2hex( substr( $infoEx[ 'APP0' ], 7, 1 ) ) );

			switch( $units )
			{
			case 0:
				$info[ 'dpiX' ] = 96;
				$info[ 'dpiY' ] = intval( round( $info[ 'dpiX' ] * $x / $y ) );
				break;

			case 1:
				$info[ 'dpiX' ] = $x;
				$info[ 'dpiY' ] = $y;
				break;

			case 2:
				$info[ 'dpiX' ] = intval( round( $x * 2.54 ) );
				$info[ 'dpiY' ] = intval( round( $y * 2.54 ) );
				break;
			}

			break;
		}

		return( $info );
	}

	static function GetData( $h, $mimeType, $prms = null, $file = null )
	{
		Img::GetDataEx( $res, $h, $mimeType, $prms, $file );
		return( $res );
	}

	static function GetDataEx( &$res, $h, $mimeType, $prms = null, $file = null )
	{
		if( !$h )
			return( Gen::E_INVALIDARG );

		$fn = null;

		$fileIsTmp = false;
		if( !$file )
		{
			$dirTmp = Gen::GetTempDir();
			$file = @tempnam( $dirTmp, '' );
			if( !$file )
			{
				Gen::LastErrDsc_Set( LocId::Pack( 'TmpFileCreateErr_%1$s', 'Common', array( $dirTmp ) ) );
				return( Gen::E_FAIL );
			}
			unset( $dirTmp );

			$fileIsTmp = true;
		}

		switch( $mimeType )
		{
		case 'image/gif':
			$fn = 'imagegif';
			$args = array( $h, $file );
			break;

		case 'image/jpeg':
			$fn = 'imagejpeg';
			$args = array( $h, $file, Gen::GetArrField( $prms, array( 'q' ), -1 ) );
			break;

		case 'image/png':

			if( function_exists( 'imageistruecolor' ) && !@imageistruecolor( $h ) )
				@imagetruecolortopalette( $h, false, @imagecolorstotal( $h ) );

			$fn = 'imagepng';
			$args = array( $h, $file, Gen::GetArrField( $prms, array( 'c' ), Img::PNG_COMPRESSION_DEF ) );
			break;

		case 'image/webp':
			if( !function_exists( 'imageistruecolor' ) )
				return( Gen::E_UNSUPPORTED );

			if( !@imageistruecolor( $h ) )
			{

				if( !@imagepalettetotruecolor( $h ) )
				{
					Gen::LastErrDsc_Set( 'imagepalettetotruecolor: fail' );
					return( Gen::E_FAIL );
				}

			}

			$fn = 'imagewebp';
			$args = array( $h, $file, Gen::GetArrField( $prms, array( 'q' ), Img::WEBP_QUALITY_DEF ) );
			break;

		case 'image/avif':
			if( !function_exists( 'imageistruecolor' ) )
				return( Gen::E_UNSUPPORTED );

			if( !@imageistruecolor( $h ) && !@imagepalettetotruecolor( $h ) )
			{
				Gen::LastErrDsc_Set( 'imagepalettetotruecolor: fail' );
				return( Gen::E_FAIL );
			}

			$fn = 'imageavif';
			$args = array( $h, $file, Gen::GetArrField( $prms, array( 'q' ), Img::AVIF_QUALITY_DEF ), Gen::GetArrField( $prms, array( 's' ), Img::AVIF_SPEED_DEF ) );
			break;
		}

		if( !$fn )
		{
			if( $fileIsTmp )
				@unlink( $file );
			return( Gen::E_UNSUPPORTED );
		}

		if( !function_exists( $fn ) )
			return( Gen::E_UNSUPPORTED );

		$res = Gen::CallFunc( $fn, $args );
		if( !$fileIsTmp )
		{
			if( $res )
			{
				$hr = self::_GetDataEx_PostProcess( $mimeType, $prms, $file );
				if( $hr != Gen::S_OK )
					$res = false;
				return( $hr );
			}

			Gen::LastErrDsc_Set( $fn . ': fail' );
			return( Gen::E_FAIL );
		}

		$hr = Gen::S_OK;
		if( !$res )
		{

			{
				Gen::LastErrDsc_Set( $fn . ': fail' );
				$hr = Gen::E_FAIL;
			}
		}
		else
		{
			$hr = self::_GetDataEx_PostProcess( $mimeType, $prms, $file );
			if( $hr == Gen::S_OK )
			{
				$res = @file_get_contents( $file );
				if( $res === false )
				{
					Gen::LastErrDsc_Set( Gen::GetLocPackFileReadErr( $file ) );
					$hr = Gen::E_FAIL;
				}
			}
		}

		@unlink( $file );
		return( $hr );
	}

	static private function _GetDataEx_PostProcess( $mimeType, $prms, $file )
	{
		if( $mimeType == 'image/png' )
		{
			$quality = Gen::GetArrField( $prms, array( 'q' ), Img::PNG_QUALITY_DEF );
			if( $quality < 1 )
				$quality = 1;
			else if( $quality > 100 )
				$quality = 100;

			if( $quality < 100 )
			{
				$speed = Gen::GetArrField( $prms, array( 's' ), Img::PNG_SPEED_DEF );
				if( $speed < 1 )
					$speed = 1;
				else if( $speed > 11 )
					$speed = 11;

				$mdl = ''; call_user_func_array( self::$_toolPaths[ 'pngquant' ], array( &$mdl ) );
				if( @file_exists( $mdl ) )
				{
					if( !function_exists( 'proc_open' ) || !function_exists( 'proc_close' ) )
						return( Gen::S_OK );

					$fileNew = Gen::GetFileName( $file, true, true ) . '.O.' . Gen::GetFileExt( $file );

					$cmdline = Gen::ExecEscArg( $mdl ) . ' --quality 0-' . $quality . ' --speed ' . $speed . ' --force --output ' . Gen::ExecEscArg( $fileNew ) . ' ' . Gen::ExecEscArg( $file );

					$hProc = @proc_open( $cmdline, array( 2 => array( 'pipe', 'w' ) ), $pipes, null, null, array( 'bypass_shell' => true ) );
					if( $hProc )
					{
						$output = @stream_get_contents( (isset($pipes[ 2 ])?$pipes[ 2 ]:null) );
						@fclose( (isset($pipes[ 2 ])?$pipes[ 2 ]:null) );
						$rescode = @proc_close( $hProc );

						if( $rescode == 0 )
						{
							$size = @filesize( $fileNew );
							if( $size !== false && $size < ( int )@filesize( $file ) )
							{
								if( !@rename( $fileNew, $file ) )
									@unlink( $fileNew );
							}
							else
								@unlink( $fileNew );
						}
						else
							@unlink( $fileNew );
					}
				}
			}
		}

		return( Gen::S_OK );
	}

	static function CreateFromData( $data )
	{
		Img::CreateFromDataEx( $res, $data );
		return( $res );
	}

	static function CreateFromDataEx( &$h, $data )
	{
		if( !$data )
			return( Gen::E_INVALIDARG );

		if( !function_exists( 'imagecreatefromstring' ) )
			return( Gen::E_UNSUPPORTED );

		$h = @imagecreatefromstring( $data );
		if( $h === false )
			return( Gen::E_FAIL );

		if( function_exists( 'imagealphablending' ) && function_exists( 'imagesavealpha' ) )
		{
			@imagealphablending( $h, false );
			@imagesavealpha( $h, true );
		}

		return( Gen::S_OK );
	}

	static function CreateCopyResample( $h, $sizeDst, $rcSrc = null, $rcDst = null, $bgClr = null )
	{
		if( !$sizeDst[ 'cx' ] || !$sizeDst[ 'cy' ] )
			return( null );

		$hNew = @imagecreatetruecolor( $sizeDst[ 'cx' ], $sizeDst[ 'cy' ] );
		if( $hNew === false )
			return( null );

		if( !$rcDst )
			$rcDst = array( 'x' => 0, 'y' => 0, 'cx' => $sizeDst[ 'cx' ], 'cy' => $sizeDst[ 'cy' ] );

		@imagesavealpha( $hNew, true );

		$hClr = @imagecolorallocatealpha( $hNew, 0, 0, 0, 127 );
		@imagefill( $hNew, 0, 0, $hClr );
		@imagecolordeallocate( $hNew, $hClr );

		if( $bgClr !== null )
		{
			$hClr = @imagecolorallocate( $hNew, ( $bgClr >> 16 ) & 0xFF, ( $bgClr >> 8 ) & 0xFF, $bgClr & 0xFF );
			@imagefilledrectangle( $hNew, 0, 0, $sizeDst[ 'cx' ] - 1, $sizeDst[ 'cy' ] - 1, $hClr );
			@imagecolordeallocate( $hNew, $hClr );
		}

		if( function_exists( 'imageantialias' ) )
			@imageantialias( $hNew, true );

		if( !@imagecopyresampled( $hNew, $h,
			$rcDst[ 'x' ], $rcDst[ 'y' ],
			$rcSrc[ 'x' ], $rcSrc[ 'y' ],
			$rcDst[ 'cx' ], $rcDst[ 'cy' ],
			$rcSrc[ 'cx' ], $rcSrc[ 'cy' ] ) )
		{
			@imagedestroy( $hNew );
			return( null );
		}

		return( $hNew );
	}

	static function IsDataPngAnimated( $data )
	{

		$pos = strpos( $data, 'IDAT' );
		return( $pos !== false && strpos( substr( $data, 0, $pos ), 'acTL' ) !== false );
	}

	static function IsDataGifAnimated( $data )
	{

		for( $n = 0, $i = 0; $n < 2; $n++ )
		{
			if( !preg_match( '@\x00\x21\xF9\x04.{4}\x00(?:\x2C|\x21)@s', $data, $m, PREG_OFFSET_CAPTURE, $i ) )
				break;
			$i = $m[ 0 ][ 1 ] + strlen( $m[ 0 ][ 0 ] );
		}

		return( $n > 1 );
	}

	static function ConvertData( $data, $mimeType, $prms = null, $file = null )
	{
		Img::ConvertDataEx( $res, $data, $mimeType, $prms, $file );
		return( $res );
	}

	static function ConvertDataEx( &$res, $data, $mimeType, $prms = null, $file = null )
	{

		$type = substr( $mimeType, 6 );

		$hr = self::_ConvertDataEx_Gd( $res, $data, $mimeType, $type, $prms, $file );
		if( $hr != Gen::E_UNSUPPORTED )
		    return( $hr );

		$hr = self::_ConvertDataEx_Imagick( $res, $data, $type, $prms, $file );
		if( $hr != Gen::E_UNSUPPORTED )
		    return( $hr );

		if( $type == 'webp' && isset( self::$_toolPaths[ 'cwebp' ] ) )
		{

			$mdl = '';
			$hr = call_user_func_array( self::$_toolPaths[ 'cwebp' ], array( &$mdl ) );
			if( Gen::HrFail( $hr ) )
				return( $hr );

			if( @file_exists( $mdl ) )
				return( self::_ConvertDataEx_CWebp( $mdl, $res, $data, $prms, $file ) );
		}

		if( $type == 'avif' && isset( self::$_toolPaths[ 'avifenc' ] ) )
		{

			$mdl = '';
			$hr = call_user_func_array( self::$_toolPaths[ 'avifenc' ], array( &$mdl ) );
			if( Gen::HrFail( $hr ) )
				return( $hr );

			if( @file_exists( $mdl ) )
				return( self::_ConvertDataEx_AvifEnc( $mdl, $res, $data, $prms, $file ) );
		}

		return( Gen::E_UNSUPPORTED );
	}

	static private function _ConvertDataEx_Gd( &$res, $data, $mimeType, $type, $prms = null, $file = null )
	{
		if( !function_exists( 'image' . $type ) )
			return( Gen::E_UNSUPPORTED );

		$hr = Img::CreateFromDataEx( $img, $data );
		if( Gen::HrFail( $hr ) )
			return( $hr );

		$hr = Img::GetDataEx( $res, $img, $mimeType, $prms, $file );
		if( $img )
			@imagedestroy( $img );
		return( $hr );
	}

	static private function _ConvertDataEx_Imagick( &$res, $data, $type, $prms = null, $file = null )
	{
		if( !class_exists( 'Imagick' ) )
			return( Gen::E_UNSUPPORTED );

		if( !$data )
			return( Gen::E_INVALIDARG );

		$img = new \Imagick();

		try
		{
			if( !$img -> readImageBlob( $data ) )
			{
				Gen::LastErrDsc_Set( 'Imagick::readImageBlob: fail' );
				return( Gen::E_FAIL );
			}
		}
		catch( \Exception $e )
		{
			Gen::LastErrDsc_Set( 'Imagick::readImageBlob: ' . $e -> getMessage() );
			return( Gen::E_FAIL );
		}

		if( $type == 'avif' && ( !@method_exists( $img, 'getImageAlphaChannel' ) || $img -> getImageAlphaChannel()  ) )
		    return( Gen::E_UNSUPPORTED );

		try
		{
			if( !$img -> setCompressionQuality( Gen::GetArrField( $prms, array( 'q' ), 75 ) ) )
			{
				Gen::LastErrDsc_Set( 'Imagick::setCompressionQuality: fail' );
				return( Gen::E_FAIL );
			}
		}
		catch( \Exception $e )
		{
			Gen::LastErrDsc_Set( 'Imagick::setCompressionQuality: ' . $e -> getMessage() );
			return( Gen::E_FAIL );
		}

		try
		{
			if( !$img -> setImageFormat( $type ) )
				return( Gen::E_UNSUPPORTED );
		}
		catch( \Exception $e )
		{
			return( Gen::E_UNSUPPORTED );
		}

		if( $file )
		{
			try
			{
				if( !$img -> writeImage( $file ) )
				{
					Gen::LastErrDsc_Set( LocId::Pack( 'FileWriteErr_%1$s', 'Common', array( $file ) ) );
					return( Gen::E_FAIL );
				}
				$res = true;
			}
			catch( \Exception $e )
			{

				Gen::LastErrDsc_Set( 'Imagick::writeImage: ' . $e -> getMessage() );
				return( $e -> getCode() == 1 ? Gen::E_UNSUPPORTED : Gen::E_FAIL );
			}
		}
		else
		{
			try
			{
				$res = $img -> getImageBlob();
			}
			catch( \Exception $e )
			{

				Gen::LastErrDsc_Set( 'Imagick::getImageBlob: ' . $e -> getMessage() );
				return( $e -> getCode() == 1 ? Gen::E_UNSUPPORTED : Gen::E_FAIL );
			}
		}

		return( Gen::S_OK );
	}

	static private function _ConvertDataEx_CWebp( $mdl, &$res, $data, $prms = null, $file = null )
	{
		$hr = Gen::S_OK;
		$dirTmp = Gen::GetTempDir();
		$fileTmpIn = @tempnam( $dirTmp, '' );
		if( !$fileTmpIn )
		{
			Gen::LastErrDsc_Set( LocId::Pack( 'TmpFileCreateErr_%1$s', 'Common', array( $dirTmp ) ) );
			return( Gen::E_FAIL );
		}
		unset( $dirTmp );

		$fileIsTmp = false;
		if( !$file )
		{
			$file = $fileTmpIn . '.webp';
			$fileIsTmp = true;
		}

		if( $mimeTypeSrc = Img::GetInfoFromData( $data ) )
		{
			$mimeTypeSrc = $mimeTypeSrc[ 'mime' ];
			if( !in_array( $mimeTypeSrc, array( 'image/png', 'image/jpeg' ) ) )
			{
				$hr = Img::CreateFromDataEx( $img, $data );
				if( Gen::HrFail( $hr ) )
				{
					@unlink( $fileTmpIn );
					return( $hr );
				}

				$data = null;

				$hr = Img::GetDataEx( $res, $img, 'image/png', null, $fileTmpIn );
				if( $img )
					@imagedestroy( $img );

				if( Gen::HrFail( $hr ) )
				{
					@unlink( $fileTmpIn );
					return( $hr );
				}
			}
		}

		if( $data !== null && @file_put_contents( $fileTmpIn, $data ) === false )
		{
			@unlink( $fileTmpIn );
			Gen::LastErrDsc_Set( LocId::Pack( 'FileWriteErr_%1$s', 'Common', array( $fileTmpIn ) ) );
			return( Gen::E_FAIL );
		}

		unset( $data );

		$quality = Gen::GetArrField( $prms, array( 'q' ), Img::WEBP_QUALITY_DEF );
		if( $quality > 100 )
			$quality = 100;
		else if( $quality < 0 )
			$quality = 0;

		$cmdline = Gen::ExecEscArg( $mdl ) . ' -q ' . $quality . ' ' . Gen::ExecEscArg( $fileTmpIn ) . ' -o ' . Gen::ExecEscArg( $file );

		if( !function_exists( 'proc_open' ) )
		{
			Gen::LastErrDsc_Set( LocId::Pack( 'FuncBlocked_%1$s', 'Common', array( 'proc_open' ) ) );
			return( Gen::E_ACCESS_DENIED );
		}

		if( !function_exists( 'proc_close' ) )
		{
			Gen::LastErrDsc_Set( LocId::Pack( 'FuncBlocked_%1$s', 'Common', array( 'proc_close' ) ) );
			return( Gen::E_ACCESS_DENIED );
		}

		$hProc = @proc_open( $cmdline, array( 2 => array( 'pipe', 'w' ) ), $pipes, null, null, array( 'bypass_shell' => true ) );
		if( $hProc )
		{
			$output = @stream_get_contents( (isset($pipes[ 2 ])?$pipes[ 2 ]:null) );
			@fclose( (isset($pipes[ 2 ])?$pipes[ 2 ]:null) );
			$rescode = @proc_close( $hProc );

			if( $rescode != 0 )
			{
				Gen::LastErrDsc_Set( LocId::Pack( 'ExecErrCode_%1$s%2$d%3$s', 'Common', array( $cmdline, $rescode, str_replace( "\n", '; ', trim( $output ) ) ) ) );
				$hr = Gen::E_ERRORINAPP;
			}
			else if( $fileIsTmp )
			{
				$res = @file_get_contents( $file );
				if( $res === false )
				{
					Gen::LastErrDsc_Set( Gen::GetLocPackFileReadErr( $file ) );
					$hr = Gen::E_FAIL;
				}
			}
			else
				$res = true;
		}
		else
		{
			Gen::LastErrDsc_Set( LocId::Pack( 'ExecErr_%1$s', 'Common', array( $cmdline ) ) );
			$hr = Gen::E_FAIL;
		}

		@unlink( $fileTmpIn );
		if( $fileIsTmp )
			@unlink( $file );
		return( $hr );
	}

	static private function _ConvertDataEx_AvifEnc( $mdl, &$res, $data, $prms = null, $file = null )
	{
		$hr = Gen::S_OK;
		$dirTmp = Gen::GetTempDir();
		$fileTmpIn = @tempnam( $dirTmp, '' );
		if( !$fileTmpIn )
		{
			Gen::LastErrDsc_Set( LocId::Pack( 'TmpFileCreateErr_%1$s', 'Common', array( $dirTmp ) ) );
			return( Gen::E_FAIL );
		}
		unset( $dirTmp );

		$fileIsTmp = false;
		if( !$file )
		{
			$file = $fileTmpIn . '.avif';
			$fileIsTmp = true;
		}

		if( $mimeTypeSrc = Img::GetInfoFromData( $data ) )
		{
			$mimeTypeSrc = $mimeTypeSrc[ 'mime' ];
			if( !in_array( $mimeTypeSrc, array( 'image/png', 'image/jpeg' ) ) )
			{
				$hr = Img::CreateFromDataEx( $img, $data );
				if( Gen::HrFail( $hr ) )
				{
					@unlink( $fileTmpIn );
					return( $hr );
				}

				$data = null;

				$hr = Img::GetDataEx( $res, $img, 'image/png', null, $fileTmpIn );
				if( $img )
					@imagedestroy( $img );

				if( Gen::HrFail( $hr ) )
				{
					@unlink( $fileTmpIn );
					return( $hr );
				}
			}
		}

		if( $data !== null && @file_put_contents( $fileTmpIn, $data ) === false )
		{
			@unlink( $fileTmpIn );
			Gen::LastErrDsc_Set( LocId::Pack( 'FileWriteErr_%1$s', 'Common', array( $fileTmpIn ) ) );
			return( Gen::E_FAIL );
		}

		unset( $data );

		$quality = Gen::GetArrField( $prms, array( 'q' ), Img::AVIF_QUALITY_DEF );
		if( $quality > 100 )
			$quality = 100;
		else if( $quality < 0 )
			$quality = 0;

		$speed = Gen::GetArrField( $prms, array( 's' ), Img::AVIF_SPEED_DEF );
		if( $speed > 10 )
			$speed = 10;
		else if( $speed < 0 )
			$speed = 0;

		$quality = ( int )round( ( 63.0 / 100.0 ) * ( float )( 100 - $quality ) );
		$cmdline = Gen::ExecEscArg( $mdl ) . ' --min ' . $quality . ' --max ' . $quality . ' --minalpha ' . $quality . ' --maxalpha ' . $quality . ' --speed ' . $speed . ' ' . Gen::ExecEscArg( $fileTmpIn ) . ' ' . Gen::ExecEscArg( $file );

		if( !function_exists( 'proc_open' ) )
		{
			Gen::LastErrDsc_Set( LocId::Pack( 'FuncBlocked_%1$s', 'Common', array( 'proc_open' ) ) );
			return( Gen::E_ACCESS_DENIED );
		}

		if( !function_exists( 'proc_close' ) )
		{
			Gen::LastErrDsc_Set( LocId::Pack( 'FuncBlocked_%1$s', 'Common', array( 'proc_close' ) ) );
			return( Gen::E_ACCESS_DENIED );
		}

		$hProc = @proc_open( $cmdline, array( 2 => array( 'pipe', 'w' ) ), $pipes, null, null, array( 'bypass_shell' => true ) );
		if( $hProc )
		{
			$output = @stream_get_contents( (isset($pipes[ 2 ])?$pipes[ 2 ]:null) );
			@fclose( (isset($pipes[ 2 ])?$pipes[ 2 ]:null) );
			$rescode = @proc_close( $hProc );

			if( $rescode != 0 )
			{
				Gen::LastErrDsc_Set( LocId::Pack( 'ExecErrCode_%1$s%2$d%3$s', 'Common', array( $cmdline, $rescode, str_replace( "\n", '; ', trim( $output ) ) ) ) );
				$hr = Gen::E_ERRORINAPP;
			}
			else if( $fileIsTmp )
			{
				$res = @file_get_contents( $file );
				if( $res === false )
				{
					Gen::LastErrDsc_Set( Gen::GetLocPackFileReadErr( $file ) );
					$hr = Gen::E_FAIL;
				}
			}
			else
				$res = true;
		}
		else
		{
			Gen::LastErrDsc_Set( LocId::Pack( 'ExecErr_%1$s', 'Common', array( $cmdline ) ) );
			$hr = Gen::E_FAIL;
		}

		@unlink( $fileTmpIn );
		if( $fileIsTmp )
			@unlink( $file );
		return( $hr );
	}

	static private $_toolPaths = null;
}

