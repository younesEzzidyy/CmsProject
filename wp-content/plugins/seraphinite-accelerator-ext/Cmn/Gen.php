<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

class Gen
{
	const SEVERITY_SUCCESS							= 0;
	const SEVERITY_ERROR							= 1;

	const FACILITY_INTERNET							= 12;
	const FACILITY_HTTP								= 25;

	const S_OK										= 0x00000000;
	const S_FALSE									= 0x00000001;
	const S_FAIL									= 0x00004005;
	const S_NOTIMPL									= 0x00004001;
	const S_IO_PENDING								= 0x000703E5;
	const S_ALREADY_EXISTS							= 0x000700B7;
	const S_TIMEOUT									= 0x000705B4;
	const S_ABORTED									= 0x000704C7;

	const E_DISPATCH_9								= 0x80020009;
	const E_NOTIMPL									= 0x80004001;
	const E_FAIL									= 0x80004005;
	const E_UNSUPPORTED								= 0x80004021;
	const E_INVALIDARG								= 0x80070057;
	const E_INVALID_STATE							= 0x8007139F;
	const E_INTERNAL								= 0x8007054F;
	const E_DATACORRUPTED							= 0x80070570;
	const E_NOT_FOUND								= 0x80070490;
	const E_ACCESS_DENIED							= 0x80070005;
	const E_ABORTED									= 0x800704C7;
	const E_SYNTAX									= 0x800401E4;
	const E_ALREADY_EXISTS							= 0x800700B7;
	const E_TIMEOUT									= 0x800705B4;
	const E_BUSY									= 0x800700AA;
	const E_ERRORINAPP								= 0x800401F7;
	const E_CONTEXT_EXPIRED							= 0x8007078B;

	const SevInfo					= 0;
	const SevSucc					= 1;
	const SevWarn					= 2;
	const SevErr					= 3;

	static function HrCorrect( $hr )
	{
		if( $hr < 0 )
			$hr = 0xFFFFFFFF - ( -1 * $hr ) + 1;
		return( $hr );
	}

	static function IsEmpty( $v )
	{
		return( empty( $v ) );
	}

	static function HrMake( $sev, $fac, $code )
	{
		return( ( $sev << 31 ) | ( $fac << 16 ) | Gen::HrCode( $code ) );
	}

	static function HrCode( $hr )
	{
		return( ( ( $hr ) & 0xFFFF ) );
	}

	static function HrFacility( $hr )
	{
		return( ( ( ( $hr ) >> 16 ) & 0x1FFF ) );
	}

	static function HrSucc( $hr )
	{
		return( !( $hr & 0x80000000 ) );
	}

	static function HrFail( $hr )
	{
		return( !self::HrSucc( $hr ) );
	}

	static function HrSuccFromFail( $hr )
	{
		return( $hr & ~0x80000000 );
	}

	static function HrAccom( $hr, $hrOp )
	{
		if( $hrOp == Gen::S_FALSE )
			$hrOp = Gen::S_OK;

		if( $hr == Gen::S_FALSE )
			return( $hrOp );

		if( $hr == Gen::S_OK )
			return( Gen::HrSuccFromFail( $hrOp ) );

		if( Gen::HrSucc( $hr ) )
			return( $hr );

		if( Gen::HrFail( $hrOp ) )
			return( $hr );

		return( Gen::HrSuccFromFail( $hr ) );
	}

	static function GetArrField( $arr, $fieldPath, $defVal = null, $sep = '.', $bCaseIns = false, $bSafe = true )
	{
		if( !is_array( $fieldPath ) )
			$fieldPath = explode( $sep, $fieldPath );
		return( self::_GetArrField( $arr, $fieldPath, $defVal, $bCaseIns, $bSafe ) );
	}

	static private function _GetVarType( $v )
	{
		$t = gettype( $v );
		if( $t == 'double' )
			$t = 'integer';
		return( $t );
	}

	static private function _GetArrField( $v, array $fieldPath, $defVal = null, $bCaseIns = false, $bSafe = true )
	{
		if( !count( $fieldPath ) )
			return( $defVal );

		foreach( $fieldPath as $fld )
		{
			$isArr = is_array( $v ) ? true : ( is_object( $v ) ? false : null );
			if( $isArr === null )
				return( $defVal );

			if( $fld === '' )
				continue;

			$vNext = $isArr ? ( isset( $v[ $fld ] ) ? $v[ $fld ] : null ) : ( isset( $v -> { $fld } ) ? $v -> { $fld } : null );
			if( $vNext === null && !( $isArr ? isset( $v[ $fld ] ) : isset( $v -> { $fld } ) ) )
			{
				if( !$bCaseIns )
					return( $defVal );

				$fld = strtolower( $fld );

				$vNext = $isArr ? ( isset( $v[ $fld ] ) ? $v[ $fld ] : null ) : ( isset( $v -> { $fld } ) ? $v -> { $fld } : null );
				if( $vNext === null && !( $isArr ? isset( $v[ $fld ] ) : isset( $v -> { $fld } ) ) )
					return( $defVal );
			}

			$v = $vNext;
		}

		if( $bSafe && $defVal !== null && self::_GetVarType( $v ) != self::_GetVarType( $defVal ) )
			return( $defVal );
		return( $v );
	}

	static function SetArrField( &$arr, $fieldPath, $val = null, $sep = '.' )
	{
		if( !is_array( $fieldPath ) )
			$fieldPath = explode( $sep, $fieldPath );
		self::_SetArrField( $arr, $fieldPath, $val );
	}

	static function UnsetArrField( &$arr, $fieldPath, $sep = '.' )
	{
		if( !is_array( $fieldPath ) )
			$fieldPath = explode( $sep, $fieldPath );
		self::_SetArrField( $arr, $fieldPath, null, true );
	}

	static private function _SetArrField( &$arr, array $fieldPath, $val = null, $unset = false )
	{
		$fld = array_shift( $fieldPath );
		if( $fld === null )
			return;

		if( $fld === '' )
			return( self::_SetArrField( $arr, $fieldPath, $val, $unset ) );

		$isObj = is_object( $arr );

		if( !$fieldPath )
		{
			if( $unset )
			{
				if( $isObj )
					unset( $arr -> { $fld } );
				else
					unset( $arr[ $fld ] );
			}
			else if( $fld === '+' )
			{
				if( $isObj )
					return( false );
				$arr[] = $val;
			}
			else
			{
				if( $isObj )
					$arr -> { $fld } = $val;
				else
					$arr[ $fld ] = $val;
			}

			return( true );
		}

		$vNext = $isObj ? ( isset( $arr -> { $fld } ) ? $arr -> { $fld } : null ) : ( isset( $arr[ $fld ] ) ? $arr[ $fld ] : null );
		if( !is_array( $vNext ) && !is_object( $vNext ) )
		{
			if( $isObj )
				$arr -> { $fld } = $vNext ? array( $vNext ) : array();
			else
				$arr[ $fld ] = $vNext ? array( $vNext ) : array();
		}

		if( $isObj )
			return( self::_SetArrField( $arr -> { $fld }, $fieldPath, $val, $unset ) );
		return( self::_SetArrField( $arr[ $fld ], $fieldPath, $val, $unset ) );
	}

	static function ToUnixSlashes( $path )
	{
		return( str_replace( '\\', '/', $path ) );
	}

	static function DoesFuncExist( $name )
	{
		$classSep = strpos( $name, '::' );
		if( $classSep === false )
			return( function_exists( $name ) );

		return( method_exists( substr( $name, 0, $classSep ), substr( $name, $classSep + 2 ) ) );
	}

	static function CallFunc( $name, $args = array(), $def = null )
	{
		return( Gen::DoesFuncExist( $name ) ? call_user_func_array( $name, $args ) : $def );
	}

	static function CallFuncArraySafe( $name, $args )
	{
		$fct = new \ReflectionFunction( $name );
		$nReq = $fct -> getNumberOfRequiredParameters();

		$n = count( $args );
		if( $n != $nReq )
		{
			if( $n > $nReq )
				array_splice( $args, $n - 1, $n - $nReq, array() );
			else
			{
				while( $n < $nReq )
				{
					$args[] = null;
					$n = $n + 1;
				}
			}
		}

		return( call_user_func_array( $name, $args ) );
	}

	static function GetFuncFile( $name )
	{
		try
		{
			$fct = new \ReflectionFunction( $name );
		}
		catch( \Exception $e )
		{
			return( null );
		}

		return( $fct -> getFileName() );
	}

	static function Serialize( $v )
	{
		return( @serialize( $v ) );
	}

	static function Unserialize( $data, $defVal = null )
	{
		if( !is_serialized( $data ) )
			return( $defVal );

		$v = @unserialize( $data );
		if( $v === false )
			return( $defVal );

		return( $v );
	}

	const CRYPT_METHOD_OPENSSL					= 'os3';
	const CRYPT_METHOD_MCRYPT					= 'mc3';
	const CRYPT_METHOD_XOR						= 'x3';

	static private function _StrEncDecSalt( $scheme )
	{
		if( $scheme != 'own' )
			return( wp_salt( $scheme ) );

		static $cached_salts = array();
		if( isset( $cached_salts[ $scheme ] ) )
			return( $cached_salts[ $scheme ] );

		if( defined( 'SERAPH_SECRET_KEY' ) )
			$key = SERAPH_SECRET_KEY;
		else
			$key = get_option( 'seraph_secretKey' );
		if( !$key )
			update_option( 'seraph_secretKey', $key = wp_generate_password( 64, true, true ) );

		$salt = hash_hmac( 'md5', $scheme, $key );

		$cached_salts[ $scheme ] = $key . $salt;
		return( $cached_salts[ $scheme ] );
	}

	static function StrEncode( $str, $type = null )
	{
		if( empty( $str ) || !is_string( $str ) )
			return( '' );

		if( empty( $type ) )
		{
			if( false ) {}
			else if( function_exists( 'openssl_encrypt' ) )
				$type = self::CRYPT_METHOD_OPENSSL;
			else if( function_exists( 'mcrypt_encrypt' ) )
				$type = self::CRYPT_METHOD_MCRYPT;
			else
				$type = self::CRYPT_METHOD_XOR;
		}

		switch( $type )
		{
		case self::CRYPT_METHOD_OPENSSL:
			if( !function_exists( 'openssl_encrypt' ) || !function_exists( 'wp_salt' ) )
				return( '' );

			$key = openssl_digest( self::_StrEncDecSalt( 'own' ), 'SHA256', true );

			$ivSize = ( function_exists( 'openssl_cipher_iv_length' ) && function_exists( 'openssl_random_pseudo_bytes' ) ) ? openssl_cipher_iv_length( 'AES-256-CBC' ) : null;
			$iv = null;
			if( $ivSize )
				$iv = openssl_random_pseudo_bytes( $ivSize );

			$str = openssl_encrypt( $str, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv );
			if( $str === false )
				return( '' );

			if( $ivSize )
				$str = $iv . $str;
			break;

		case self::CRYPT_METHOD_MCRYPT:
			if( !function_exists( 'mcrypt_encrypt' ) || !function_exists( 'wp_salt' ) )
				return( '' );

			$key = md5( self::_StrEncDecSalt( 'own' ), true );
			$str = @call_user_func( 'mcrypt_encrypt', 'MCRYPT_RIJNDAEL_256', $key, $str, 'MCRYPT_MODE_ECB' );
			if( $str === false )
				return( '' );
			break;

		case self::CRYPT_METHOD_XOR:
			if( !function_exists( 'wp_salt' ) )
				return( '' );

			$str = self::XorData( $str, self::_StrEncDecSalt( 'own' ) );
			break;

		default:
			return( '' );
			break;
		}

		$str = $type . ':' . base64_encode( $str );
		return( $str );
	}

	static function StrDecode( $str )
	{
		if( empty( $str ) || !is_string( $str ) )
			return( '' );

		$type = substr( $str, 0, 4 );
		{
			$sep = strpos( $type, ':' );
			if( $sep === false )
				$type = self::CRYPT_METHOD_MCRYPT;
			else
			{
				$type = substr( $type, 0, $sep );
				$str = substr( $str, $sep + 1 );
			}
		}

		$str = base64_decode( $str );

		switch( $type )
		{
		case 'os':
		case 'os2':
		case self::CRYPT_METHOD_OPENSSL:
			if( !function_exists( 'openssl_decrypt' ) || !function_exists( 'wp_salt' ) )
				return( '' );

			$key = openssl_digest( self::_StrEncDecSalt( $type === 'os' ? 'auth' : ( $type === 'os2' ? 'perm_storage' : 'own' ) ), 'SHA256', true );

			$ivSize = ( function_exists( 'openssl_cipher_iv_length' ) && function_exists( 'openssl_random_pseudo_bytes' ) ) ? openssl_cipher_iv_length( 'AES-256-CBC' ) : null;
			$iv = null;
			if( $ivSize )
			{
				$iv = mb_substr( $str, 0, $ivSize, '8bit' );
				$str = mb_substr( $str, $ivSize, null, '8bit' );
			}

			$strD = openssl_decrypt( $str, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv );
			if( $strD === false )
				$str = '';
			else
				$str = $strD;
			break;

		case 'mc':
		case 'mc2':
		case self::CRYPT_METHOD_MCRYPT:
			if( !function_exists( 'mcrypt_decrypt' ) || !function_exists( 'wp_salt' ) )
				return( '' );

			$key = md5( self::_StrEncDecSalt( $type === 'mc' ? 'auth' : ( $type === 'mc2' ? 'perm_storage' : 'own' ) ), true );
			$str = @call_user_func( 'mcrypt_decrypt', 'MCRYPT_RIJNDAEL_256', $key, $str, 'MCRYPT_MODE_ECB' );
			if( $str === false )
				$str = '';
			break;

		case 'x':
		case 'x2':
		case self::CRYPT_METHOD_XOR:
			if( !function_exists( 'wp_salt' ) )
				return( '' );

			$str = self::XorData( $str, self::_StrEncDecSalt( $type === 'x' ? 'auth' : ( $type === 'x2' ? 'perm_storage' : 'own' ) ) );
			break;

		default:
			return( '' );
			break;
		}

		return( $str );
	}

	static function StrPrintf( $fmt, $args )
	{
		try
		{
			$res = vsprintf( $fmt, $args );
		}
		catch( \Throwable $ex )
		{
			$res = $fmt .  ': ' . $ex -> getMessage();
		}

		return( $res );
	}

	static function XorData( $data, $key )
	{
		$n = mb_strlen( $data, '8bit' );
		$nKey = mb_strlen( $key, '8bit' );

		if( !$nKey )
			return( null );

		$dataNew = '';
		for( $i = 0, $iKey = 0; $i < $n; $i++, $iKey++ )
		{
			if( $iKey == $nKey )
				$iKey = 0;
			$dataNew .= $data[ $i ] ^ $key[ $iKey ];
		}

		return( $dataNew );
	}

	static function HtAccess_IsSupported()
	{

		return( !!Gen::CallFunc( 'apache_get_version' ) || preg_match( '@apache@i', isset( $_SERVER[ 'SERVER_SOFTWARE' ] ) ? $_SERVER[ 'SERVER_SOFTWARE' ] : '' ) || preg_match( '@litespeed@i', isset( $_SERVER[ 'SERVER_SOFTWARE' ] ) ? $_SERVER[ 'SERVER_SOFTWARE' ] : '' ) );
	}

	static function HtAccess_GetBlock( $id )
	{
		$homePath = Wp::GetHomePath();
		$htaccessFile = $homePath . '.htaccess';

		$cont = @file_get_contents( $htaccessFile );
		if( !$cont )
			return( false );

		$start_marker = '# BEGIN ' . $id;
		$end_marker   = '# END ' . $id;

		$nStart = strpos( $cont, $start_marker );
		if( $nStart === false )
			return( '' );
		$nStart = strpos( $cont, "\n", $nStart + strlen( $start_marker ) );
		if( $nStart === false )
			return( '' );
		$nStart++;

		$nEnd = strpos( $cont, $end_marker, $nStart );
		if( $nEnd === false )
			return( '' );

		$cont = substr( $cont, $nStart, $nEnd - $nStart );

		$cont = array_map( 'rtrim', explode( "\n", $cont ) );
		for( $i = 0; $i < count( $cont ); $i++ )
		{
			if( substr( $cont[ $i ], 0, 1 ) != '#' )
				continue;

			array_splice( $cont, $i, 1 );
			$i--;
		}

		return( trim( implode( "\n", $cont ) ) );
	}

	static function HtAccess_SetBlock( $id, $content, $bakLim = null )
	{
		$homePath = Wp::GetHomePath();
		$htaccessFile = $homePath . '.htaccess';

		$bakSucc = true;
		if( $bakLim )
		{
			$bakFile = $homePath . $id . '-' . date( 'Y-m-d_His' ) . '.htaccess';
			if( !@copy( $htaccessFile, $bakFile ) )
				$bakSucc = false;

			$aPrev = @glob( $homePath . $id . '-*.htaccess' );
			if( count( $aPrev ) > $bakLim )
			{
				foreach( $aPrev as $i => $filePrev )
				{
					if( $i >= ( count( $aPrev ) - $bakLim ) )
						break;
					@unlink( $filePrev );
				}
			}
		}

		if( !function_exists( 'insert_with_markers' ) )
			require_once( ABSPATH . 'wp-admin/includes/misc.php' );

		return( insert_with_markers( $htaccessFile, $id, $content ) === false ? Gen::E_ACCESS_DENIED : ( $bakSucc ? Gen::S_OK : Gen::E_ACCESS_DENIED ) );
	}

	static function HtAccess_QuoteUri( $uri )
	{

		$uri = str_replace( '.', '\\.', $uri );
		$uri = str_replace( '?', '\\?', $uri );
		return( $uri );
	}

	static function GetFileExt( $filepath )
	{
		$sepPos = strrpos( $filepath, '.' );
		return( $sepPos !== false ? substr( $filepath, $sepPos + 1 ) : '' );
	}

	static function GetFileName( $filepath, $nameOnly = false, $withPath = false )
	{
		if( !$withPath )
		{
			$filepath = basename( $filepath );
			if( !$nameOnly )
				return( $filepath );
		}

		$sepPos = strrpos( $filepath, '.' );
		if( $sepPos !== false )
			$filepath = substr( $filepath, 0, $sepPos );

		return( $filepath );
	}

	static function GetFileDir( $filepath, $saveLastSep = false, $levels = 1 )
	{
		if( !$levels || gettype( $filepath ) !== 'string' )
			return( $filepath );

		$sepPos = 0;

		if( $levels > 0 )
		{
			for( ;; )
			{
				$sepPos = Gen::StrRPosArr( $filepath, array( '/', '\\' ), $sepPos );
				if( $sepPos === false || $sepPos === 0 )
					break;

				$levels--;
				if( !$levels )
					break;

				$sepPos = $sepPos - strlen( $filepath ) - 1;
			}
		}
		else
		{
			$levels *= -1;

			for( ;; )
			{
				$sepPos = Gen::StrPosArr( $filepath, array( '/', '\\' ), $sepPos );
				if( $sepPos === false )
					break;

				$levels--;
				if( !$levels )
					break;

				$sepPos++;
			}
		}

		if( $sepPos === false )
			return( '' );

		return( substr( $filepath, 0, $sepPos + ( $saveLastSep ? 1 : 0 ) ) );
	}

	static function GetNormalizedPath( $path )
	{
		$path = str_replace( '\\', '/', $path );

		$root = ( isset( $path[ 0 ] ) && $path[ 0 ] === '/' ) ? '/' : '';

		$segments = explode( '/', trim( $path, '/' ) );
		$ret = array();
		foreach( $segments as $segment )
		{
			if( ( $segment == '.' ) || strlen( $segment ) === 0 )
				continue;

			if( substr_count( $segment, '.' ) == strlen( $segment ) )
				array_pop( $ret );
			else
				array_push( $ret, $segment );
		}

		return( $root . implode( '/', $ret ) );
	}

	static function MakeDir( $path, $recursive = false, $mode = 0777 )
	{
		if( @file_exists( $path ) )
		    return( Gen::S_OK );
		if( @mkdir( $path, $mode, $recursive ) )
			return( Gen::S_OK );
		return( @is_dir( $path ) ? Gen::S_FALSE : Gen::E_FAIL );
	}

	static function DirEnum( $path, &$ctx, $cb, $recurse = false )
	{

		try
		{
			$iterator = $recurse ? new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $path, \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::SKIP_DOTS ), \RecursiveIteratorIterator::CHILD_FIRST, \RecursiveIteratorIterator::CATCH_GET_CHILD ) : new \IteratorIterator( new \DirectoryIterator( $path ) );
		}
		catch( \Exception $e )
		{
			return( null );
		}

		if( $recurse )
		{
			foreach( $iterator as $file )
				if( call_user_func_array( $cb, array( $file -> getPath(), $file -> getFilename(), &$ctx ) ) === false )
					return( false );
		}
		else
		{
			foreach( $iterator as $file )
				if( !$file -> isDot() && call_user_func_array( $cb, array( $file -> getPath(), $file -> getFilename(), &$ctx ) ) === false )
					return( false );
		}

		return( true );
	}

	static function CopyDir( $path, $pathNew )
	{
		if( Gen::HrFail( Gen::MakeDir( $pathNew, true ) ) )
			return( false );

		$ctx = array( 'pathNew' => $pathNew );
		if( Gen::DirEnum( $path, $ctx,
			function( $path, $file, &$ctx )
			{
				$path = $path . '/' . $file;
				$pathNew = $ctx[ 'pathNew' ] . '/' . $file;
				if( @is_dir( $path ) )
				{
					if( !Gen::CopyDir( $path, $pathNew ) )
						$ctx[ 'notAll' ] = true;
				}
				else if( !@copy( $path, $pathNew ) )
					$ctx[ 'notAll' ] = true;

				return( true );
			}
		) === null )
			return( false );

		return( !( isset( $ctx[ 'notAll' ] ) ? $ctx[ 'notAll' ] : false ) );
	}

	static function DelDir( $path, $selfToo = true )
	{
		$notAll = array();
		Gen::DirEnum( $path, $notAll,
			function( $path, $file, &$notAll )
			{
				$path = $path . '/' . $file;
				if( @is_dir( $path ) )
				{
					if( !Gen::DelDir( $path ) )
						$notAll = true;
				}
				else if( !@unlink( $path ) )
					$notAll = true;

				return( true );
			}
		);

		if( $notAll )
			return( false );

		return( $selfToo ? @rmdir( $path ) : true );
	}

	static function DirGetHash( $path, $inclCont = false )
	{
		$ctx = new AnyObj();
		$ctx -> hash = md5( '' );
		$ctx -> inclCont = $inclCont;
		$ctx -> cb =
			function( $ctx, $path, $file, &$dummy )
			{
				$path = $path . '/' . $file;

				$cont = '';
				if( !@is_dir( $path ) )
				{
					if( is_bool( $ctx -> inclCont ) ? $ctx -> inclCont : @call_user_func( $ctx -> inclCont, $path ) )
					{
						$cont = @file_get_contents( $path );
						if( $cont === false )
							return( false );
					}
					else
					{
						$cont = @filesize( $path );
						if( $cont === false )
							return( false );
						$cont = ( string )$cont;
					}
				}

				$cont = $ctx -> hash . $file . $cont;

				$ctx -> hash = md5( $cont );
				return( true );
			};

		if( !Gen::DirEnum( $path, $dummy, array( $ctx, 'cb' ), true ) )
			return( false );

		return( $ctx -> hash );
	}

	static private function _FileOpen( $filename, $mode, $use_include_path = false )
	{
		if( strpos( $mode, 'r' ) !== false && !@file_exists( $filename ) )
		    return( false );

		$h = @fopen( $filename, $mode, $use_include_path );

		return( $h );
	}

	static function FileOpenWithMakeDir( &$h, $filename, $mode, $use_include_path = false )
	{
		$h = self::_FileOpen( $filename, $mode, $use_include_path );
		if( $h )
			return( Gen::S_OK );

		$dir = @dirname( $filename );
		if( @file_exists( $dir ) )
		{
			if( !@is_writable( $dir ) )
				return( Gen::E_ACCESS_DENIED );

			if( !( strpos( 'waxc', $mode[ 0 ] ) !== false && @is_dir( $filename ) ) )
				return( Gen::E_FAIL );

			Gen::DelDir( $filename );
		}

		Gen::MakeDir( $dir, true );
		$h = self::_FileOpen( $filename, $mode, $use_include_path );
		return( $h ? Gen::S_OK : Gen::E_FAIL );
	}

	static function FileContentExclusive_Open( &$h, $filename, $wait = false, $mode = 'rb+', $use_include_path = false )
	{
		if( strpos( $mode, 'r' ) !== false )
		{
			$h = self::_FileOpen( $filename, $mode, $use_include_path );
			if( !$h )
				return( Gen::E_FAIL );
			$hr = Gen::S_OK;
		}
		else
		{
			$hr = Gen::FileOpenWithMakeDir( $h, $filename, $mode, $use_include_path );
			if( !$h )
				return( $hr );
		}

		if( !@flock( $h, ( $wait ? 0 : LOCK_NB ) | LOCK_EX ) )
		{
			@fclose( $h );
			$h = null;
			return( $wait ? Gen::E_FAIL : Gen::E_BUSY );
		}

		return( $hr );
	}

	static function FileContentExclusive_Close( $h )
	{
		@flock( $h, LOCK_UN );
		@fclose( $h );
	}

	static function FileContentExclusive_Get( $h, $failRes = false, $lenMax = null )
	{
		$data = '';
		while( !@feof( $h ) )
		{
			$n = 16384;
			if( $lenMax !== null )
			{
				if( $lenMax > $n )
					$lenMax -= $n;
				else
				{
					$n = $lenMax;
					$lenMax = 0;
				}
			}

			$buf = @fread( $h, $n );
			if( $buf === false )
				return( $failRes );

			$data .= $buf;

			if( $lenMax !== null && !$lenMax )
				break;
		}

		return( $data );
	}

	static function FileContentExclusive_Put( $h, $data )
	{
		if( @fseek( $h, 0 ) === -1 )
			return( false );

		if( @fwrite( $h, $data ) === false )
			return( false );

		if( !@ftruncate( $h, strlen( $data ) ) )
			return( false );

		return( true );
	}

	static function FileGetContentExclusive( $filename, $failRes = false, $wait = false, $lenMax = null, $mode = 'rb+', $use_include_path = false )
	{
		Gen::FileContentExclusive_Open( $h, $filename, $wait, $mode, $use_include_path );
		if( !$h )
			return( $failRes );

		$data = Gen::FileContentExclusive_Get( $h, $failRes, $lenMax );
		if( $data === false )
			$data = $failRes;

		Gen::FileContentExclusive_Close( $h );
		return( $data );
	}

	static function FilePutContentExclusive( $filename, $data, $wait = false, $mode = 'cb', $use_include_path = false )
	{
		$hr = Gen::FileContentExclusive_Open( $h, $filename, $wait, $mode, $use_include_path );
		if( Gen::HrFail( $hr ) )
			return( $hr );

		if( Gen::FileContentExclusive_Put( $h, $data ) === false )
		{
			Gen::FileContentExclusive_Close( $h );
			return( Gen::E_FAIL );
		}

		Gen::FileContentExclusive_Close( $h );
		return( Gen::S_OK );
	}

	static function FilePutContentWithMakeDir( $filename, $data, $mode = 'wb', $use_include_path = false )
	{
		$hr = Gen::FileOpenWithMakeDir( $h, $filename, $mode, $use_include_path );
		if( Gen::HrFail( $hr ) )
			return( $hr );

		if( @fwrite( $h, $data ) === false )
		{
			@fclose( $h );
			return( Gen::E_FAIL );
		}

		@fclose( $h );
		return( Gen::S_OK );
	}

	static function FileSize( $file )
	{
		return( @file_exists( $file ) ? @filesize( $file ) : false );
	}

	static function SetLastSlash( $filepath, $set = true, $slash = '/' )
	{
		$n = strlen( $filepath );
		if( !$n )
			return( '' );

		$sepPos = strrpos( $filepath, $slash );
		if( $sepPos === $n - 1 )
		{
			if( !$set )
				return( substr( $filepath, 0, $n - 1 ) );
		}
		else
		{
			if( $set )
				return( $filepath . $slash );
		}

		return( $filepath );
	}

	static function SetFirstSlash( $filepath, $set = true, $slash = '/' )
	{
		if( empty( $filepath ) )
			return( '' );

		$sepPos = strpos( $filepath, $slash );
		if( $sepPos === 0 )
		{
			if( !$set )
				return( substr( $filepath, 1 ) );
		}
		else
		{
			if( $set )
				return( $slash . $filepath );
		}

		return( $filepath );
	}

	static function StrReplaceWhileChanging( $search, $replace, $str )
	{
		for( ;; )
		{
			$nPrev = strlen( $str );
			$str = str_replace( $search, $replace, $str );
			if( $nPrev == strlen( $str ) )
				break;
		}

		return( $str );
	}

	static function StrPosArr( string $haystack, array $needles, $offset = 0 )
	{
		foreach( $needles as $needle )
		{
			$pos = strpos( $haystack, $needle, $offset );
			if( $pos !== false )
				return( $pos );
		}

		return( false );
	}

	static function StrRPosArr( string $haystack, array $needles, $offset = 0 )
	{
		foreach( $needles as $needle )
		{
			$pos = strrpos( $haystack, $needle, $offset );
			if( $pos !== false )
				return( $pos );
		}

		return( false );
	}

	static function StrStartsWith( string $haystack, $needle )
	{
		if( is_string( $needle ) )
		{
			if( function_exists( 'str_starts_with' ) )
				return( str_starts_with( $haystack, $needle ) );
			return( substr_compare( $haystack, $needle, 0, strlen( $needle ) ) === 0 );
		}

		foreach( $needle as $needleEl )
			if( Gen::StrStartsWith( $haystack, $needleEl ) )
				return( true );

		return( false );
	}

	static function StrEndsWith( string $haystack, string $needle )
	{
		if( is_string( $needle ) )
		{
			if( function_exists( 'str_ends_with' ) )
				return( str_ends_with( $haystack, $needle ) );
			return( substr_compare( $haystack, $needle, -strlen( $needle ), strlen( $needle ) ) === 0 );
		}

		foreach( $needle as $needleEl )
			if( Gen::StrEndsWith( $haystack, $needleEl ) )
				return( true );

		return( false );
	}

	static function StrReplace( $search, $replace, $subject )
	{
		if( !is_array( $subject ) )
			return( str_replace( $search, $replace, $subject ) );

		foreach( $subject as &$subjectEl )
			$subjectEl = Gen::StrReplace( $search, $replace, $subjectEl );

		return( $subject );
	}

	static function ArrCopy( $arr )
	{
		$arr = array_map(
			function( $arrEl )
			{
				if( is_array( $arrEl ) )
					$arrEl = self::ArrCopy( $arrEl );
				return( $arrEl );
			},
			$arr
		);

		return( $arr );
	}

	static function ArrSet( &$arr, $arrSrc )
	{
		if( !count( $arrSrc ) )
		{
			Gen::ArrGetByPos( $arr, 0, null, $k );
			if( is_int( $k ) )
				$arr = array();
			return;
		}

		$arrCleared = false;
		foreach( $arrSrc as $k => $vSrc )
		{
			if( !$arrCleared && is_int( $k ) )
			{
				$arr = array();
				$arrCleared = true;
			}

			$v = &$arr[ $k ];
			if( is_array( $vSrc ) )
				Gen::ArrSet( $v, $vSrc );
			else
				$v = $vSrc;
		}
	}

	static function ArrFlatten( $arr )
	{
		$res = array();

		foreach( $arr as $a )
		{
			if( !is_array( $a ) )
			{
				$res[] = $a;
				continue;
			}

			foreach( self::ArrFlatten( $a ) as $aSub )
				$res[] = $aSub;
		}

		return( $res );
	}

	static function ArrFromStr( $str, $sep, $cbItem = null, $cbArgs = null )
	{
		if( empty( $str ) )
			return( array() );

		$arr = explode( $sep, $str );

		if( !$cbItem )
			return( $arr );

		foreach( $arr as &$a )
			call_user_func_array( $cbItem, array( $cbArgs, &$a ) );

		return( $arr );
	}

	static function ArrGetByPos( $arr, $pos, $def = null, &$key = null )
	{
		if( !$arr )
			return( $def );

		foreach( $arr as $k => $v )
		{
			if( $pos == 0 )
			{
				$key = $k;
				return( $v );
			}
			$pos--;
		}

		return( $def );
	}

	static function ArrEqual( array $a1, array $a2 )
	{
		return( count( $a1 ) == count( $a2 ) && !array_diff( $a1, $a2 ) && !array_diff( $a2, $a1 ) );
	}

	static function ArrAdd( array &$array, array $array2 )
	{
		array_splice( $array, count( $array ), 0, $array2 );
	}

	static function ArrSplice( array &$array, int $offset, $length, $replacement = array(), $preserve_keys = true )
	{
		if( !$preserve_keys )
			return( array_splice( $array, $offset, $length, $replacement ) );

		$out = array_slice( $array, $offset, $length, true );
		$array = array_slice( $array, 0, $offset, true ) + $replacement + array_slice( $array, $offset + $length, null, true );
		return( $out );
	}

	static function ArrGetIntPtrIdx( array &$array )
	{
		$kCur = key( $array );
		if( $kCur === null )
			return( false );

		reset( $array );
		for( $i = 0; ( $k = key( $array ) ) !== null; $i++ )
		{
			if( $k === $kCur )
				return( $i );
			next( $array );
		}

		return( false );
	}

	static function ArrSetIntPtrToIdx( array &$array, $i )
	{
		reset( $array );
		for( ; $i > 0; $i-- )
			next( $array );
	}

	static function GetCurRequestTime( $serverArgs = null )
	{
		if( $serverArgs === null )
			$serverArgs = $_SERVER;
		return( isset( $serverArgs[ 'REQUEST_TIME' ] ) ? ( int )$serverArgs[ 'REQUEST_TIME' ] : null );
	}

	static function StripTagsContent( $text, $tags = '', $invert = false )
	{
		if( is_string( $tags ) )
		{
			preg_match_all( '/<(.+?)[\s]*\/?[\s]*>/si', trim( $tags ), $tags );
			$tags = array_unique( $tags[ 1 ] );
		}

		if( is_array( $tags ) && count( $tags ) > 0 )
		{
			if( $invert )
				return( preg_replace( '@<(' . implode( '|', $tags ) . ')\b.*?>.*?</\1>@si', '', $text ) );
			return( preg_replace( '@<(?!(?:' . implode( '|', $tags ) . ')\b)(\w+)\b.*?>.*?</\1>@si', '', $text ) );
		}

		if( $invert )
			return( $text );

		return( preg_replace( '@<(\w+)\b.*?>.*?</\1>@si', '', $text ) );
	}

	static function GetJsHtmlContent( $text )
	{
		$text = str_replace( 'script>', 'scrapt>', $text );
		$text = addslashes( $text );
		$text = str_replace( "\r", '\\r', $text );
		$text = str_replace( "\n", '\\n', $text );
		$text = str_replace( "\t", '\\t', $text );
		return( $text );
	}

	static function MinifyHtml( $html )
	{
		return( $html );

		$search = array(
			'/\>[^\S ]+/s',
			'/[^\S ]+\</s',
			'/(\s)+/s',
			'/<!--(.|\s)*?-->/'
		);

		$replace = array(
			'>',
			'<',
			'\\1',
			''
		);

		$html = preg_replace( $search, $replace, $html );
		return( $html );
	}

	static function FloatToStr( $v )
	{
		$v = trim( sprintf( '%f', $v ), '0' );
		return( trim( $v, '.' ) );
	}

	static function SanitizeId( $id, $vDef = '' )
	{
		if( gettype( $id ) != 'string' )
			return( $vDef );
		return( preg_replace( '@[^A-Za-z0-9_\\-:\\.%/\\\\]@', '', $id ) );
	}

	static function SanitizeTextData( $data )
	{
		if( gettype( $data ) != 'string' )
			return( '' );
		return( preg_replace( '@[^A-Za-z0-9_\\-:+=|/,\\.\\ ]@', '', $data ) );
	}

	static private function _GetRequestSessionsCloserForContinueBgWork()
	{

		if( PHP_VERSION_ID >= 70016 && function_exists( 'fastcgi_finish_request' ) )
			return( 'fastcgi_finish_request' );
		else if( function_exists( 'litespeed_finish_request' ) )
			return( 'litespeed_finish_request' );
		return( null );
	}

	static function IsRequestSessionsCanBeClosedForContinueBgWork()
	{
		return( self::_GetRequestSessionsCloserForContinueBgWork() !== null );
	}

	static function CloseCurRequestSessionForContinueBgWorkEx()
	{
		$fnName = self::_GetRequestSessionsCloserForContinueBgWork();
		return( $fnName !== null ? @call_user_func( $fnName ) : false );
	}

	static function CloseCurRequestSessionForContinueBgWork()
	{

		@ignore_user_abort( true );

		if( session_id() )
			session_write_close();

		for( $l = ob_get_level(); $l > 0; $l-- )
			ob_end_flush();
		flush();

		error_reporting( E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR );

		return( Gen::CloseCurRequestSessionForContinueBgWorkEx() );
	}

	static function FileAddLine( $file, $text )
	{
		$text .= "\r\n";

		Gen::FileOpenWithMakeDir( $stm, $file, 'a' );
		if( !$stm )
			return;

		@fwrite( $stm, $text );
		@fclose( $stm );
	}

	static function LogWrite( $file, $text, $severity = Gen::SevInfo, $category = null )
	{
		switch( $severity )
		{
		case Gen::SevSucc:	$severity = 'S'; break;
		case Gen::SevWarn:	$severity = 'W'; break;
		case Gen::SevErr:	$severity = 'E'; break;
		default:			$severity = 'I'; break;
		}

		static $requestId;
		if( $requestId === null )
			$requestId = Gen::MicroTimeStamp( (isset($_SERVER[ 'REQUEST_TIME_FLOAT' ])?$_SERVER[ 'REQUEST_TIME_FLOAT' ]:null) );

		{
			$fileHtaccess = Gen::GetFileDir( $file ) . '/.htaccess';
			if( !@file_exists( $htaccessFile ) )
				@file_put_contents( $fileHtaccess, 'Options -Indexes' );
		}

		if( Gen::FileSize( $file ) > ( 2 * 1024 * 1024 ) )
		{
			$filePrev = Gen::GetFileName( $file, true, true ) . '.' . sprintf( '%08X', time() ) . '.' . Gen::GetFileExt( $file );
			if( !@file_exists( $filePrev ) )
			{
				@rename( $file, $filePrev );

				$aPrev = @glob( Gen::GetFileName( $file, true, true ) . '.*.' . Gen::GetFileExt( $file ) );
				if( count( $aPrev ) > ( 50 - 1 ) )
				{
					foreach( $aPrev as $i => $filePrev )
					{
						if( $i >= ( count( $aPrev ) - ( 50 - 1 ) ) )
							break;
						@unlink( $filePrev );
					}
				}

				if( count( $aPrev ) )
					Gen::FileAddLine( $file, 'Previous: ' . Gen::GetFileName( $aPrev[ count( $aPrev ) - 1 ] ) . "\r\n" );
			}
		}

		Gen::FileAddLine( $file, gmdate( 'd M Y H:i:s', time() ) . " GMT\t" . '<' . $severity . '>' . "\t" . $requestId . ( is_string( $category ) ? ( "\t" . $category ) : '' ) . "\t" . $text );
	}

	static function LogClear( $file, $bHasSfx = false )
	{
		$fileCmn = Gen::GetFileName( $file, true, true );
		if( $bHasSfx )
			$fileCmn = Gen::GetFileName( $fileCmn, true, true );

		foreach( @glob( $fileCmn . '*.' . Gen::GetFileExt( $file ), GLOB_NOSORT ) as $filePrev )
			@unlink( $filePrev );

		@file_put_contents( $file, '' );
	}

	static function GetAlignNShift( $val, $size )
	{
		$n = $val % $size;
		return( $n ? $size - $n : 0 );
	}

	static function AlignN( $val, $size )
	{
		return( $val + Gen::GetAlignNShift( $val, $size ) );
	}

	static function AlignNLowShift( $val, $size )
	{
		return( - $val % $size );
	}

	static function AlignNLow( $val, $size )
	{
		return( $val + Gen::AlignNLowShift( $val, $size ) );
	}

	static function MicroTimeStamp( $time = null )
	{
		if( $time === null )
			$time = microtime( true );
		return( preg_replace( '@[^\\d]@', '', ( string )$time ) );
	}

	static function ExecGetMdlNames( $name, array &$info = array() )
	{

		$info[ 'archs' ] = array( function_exists( 'php_uname' ) ? strtolower( php_uname( 'm' ) ) : '' );
		$info[ 'os' ] = strtolower( PHP_OS );
		$ext = '';

		if( strstr( $info[ 'os' ], 'darwin' ) )
		{
			$info[ 'os' ] = 'darwin';
			$ext = 'bin';

			if( $info[ 'archs' ][ 0 ] == 'x86_64' )
			{
				$info[ 'archs' ][ 0 ] = 'x64';
				$info[ 'archs' ][] = 'x86';
			}
		}
		else if( strstr( $info[ 'os' ], 'linux' ) )
		{
			$info[ 'os' ] = 'linux';
			$ext = 'bin';

			if( $info[ 'archs' ][ 0 ] == 'x86_64' )
			{
				$info[ 'archs' ][ 0 ] = 'x64';
				$info[ 'archs' ][] = 'x86';
			}
		}
		else if( strstr( $info[ 'os' ], 'sunos' ) )
		{
			$info[ 'os' ] = 'sun';
			$ext = 'bin';

			if( $info[ 'archs' ][ 0 ] == 'x86_64' )
			{
				$info[ 'archs' ][ 0 ] = 'x64';
				$info[ 'archs' ][] = 'x86';
			}
		}
		else if( strstr( $info[ 'os' ], 'bsd' ) )
		{
			$info[ 'os' ] = 'bsd';
			$ext = 'bin';

			if( $info[ 'archs' ][ 0 ] == 'x86_64' )
			{
				$info[ 'archs' ][ 0 ] = 'x64';
				$info[ 'archs' ][] = 'x86';
			}
		}
		else if( strstr( $info[ 'os' ], 'win' ) )
		{
			$info[ 'os' ] = 'win';
			$ext = 'exe';

			if( $info[ 'archs' ][ 0 ] === 'amd64' )
			{
				$info[ 'archs' ][ 0 ] = 'x64';
				$info[ 'archs' ][] = 'x86';
			}
			else if( $info[ 'archs' ][ 0 ] === 'i586' || $info[ 'archs' ][ 0 ] === '' )
				$info[ 'archs' ][ 0 ] = 'x86';
		}

		$res = array();
		foreach( $info[ 'archs' ] as $arch )
			$res[] = $name . '.' . $info[ 'os' ] . '-' . $arch . '.' . $ext;
		return( $res );
	}

	static function ExecEscArg( $a )
	{

		return( function_exists( 'escapeshellarg' ) ? escapeshellarg( $a ) : ( "'" . str_replace( "'", "\\'", $a ) . "'" ) );
	}

	static function ExecMaskUrlArg( $v )
	{

		return( str_replace( '%', '^', $v ) );
	}

	static function ExecUnMaskUrlArg( $v )
	{

		return( str_replace( '^', '%', $v ) );
	}

	static function LastErrDsc_Set( $txt )
	{
		self::$_lastErrDsc = $txt;
	}

	static function LastErrDsc_Get()
	{
		return( self::$_lastErrDsc );
	}

	static function LastErrDsc_Is()
	{
		return( self::$_lastErrDsc !== null );
	}

	static function GetLocPackFileReadErr( $file )
	{
		return( LocId::Pack( @file_exists( $file ) ? 'FileReadErr_%1$s' : 'FileNotFound_%1$s', 'Common', array( $file ) ) );
	}

	static function SetTempDirFunc( $fn )
	{
		self::$_fnGetTmpDir = $fn;
	}

	static function GetTempDirEx()
	{
		if( function_exists( 'sys_get_temp_dir' ) )
			return( @sys_get_temp_dir() );
		return( '/tmp/' );
	}

	static function GetTempDir()
	{
		if( self::$_fnGetTmpDir )
			return( call_user_func( self::$_fnGetTmpDir ) );
		return( Gen::GetTempDirEx() );
	}

	static function GetCallStack( $options = DEBUG_BACKTRACE_PROVIDE_OBJECT, $limit = 0 )
	{
		$a = @debug_backtrace( ( int )$options, ( int )$limit );
		array_splice( $a, 0, 1 );

		$res = '';
		foreach( $a as $i => $info )
		{
			if( $res )
				$res .= "\n";

			$res .= '#' . $i . ' ';
			if( (isset($info[ 'file' ])?$info[ 'file' ]:null) )
				$res .= $info[ 'file' ];
			else
				$res .= '{}';
			if( (isset($info[ 'line' ])?$info[ 'line' ]:null) !== null )
				$res .= '(' . $info[ 'line' ] . ')';

			$res .= ': ';

			if( (isset($info[ 'class' ])?$info[ 'class' ]:null) )
				$res .= $info[ 'class' ];
			if( (isset($info[ 'type' ])?$info[ 'type' ]:null) )
				$res .= $info[ 'type' ];
			$res .= Gen::GetArrField( $info, array( 'function' ), '' ) . '(';

			foreach( Gen::GetArrField( $info, array( 'args' ), array() ) as $iArg => $arg )
			{
				if( $iArg )
					$res .= ', ';
				$res .= str_replace( array( '\\\\', '\\/' ), array( '\\', '/' ), @json_encode( $arg ) );
			}
			$res .= ')';
		}

		return( $res );
	}

	static function JsObjDecl2Json( $data )
	{
		$sQuote = ''; $c = '';
		for( $i = 0; $i < strlen( $data ); $i++ )
		{
			$cPrev = $c;
			$c = $data[ $i ];

			if( $c === '"' || $c === '\'' )
			{
				if( $sQuote === '' )
					$sQuote = $c;
				else if( $sQuote === $c && $cPrev !== '\\' )
					$sQuote = '';
				continue;
			}

			if( $sQuote !== '' && $c === ':' )
				$data[ $i ] = "\x01";
		}

		$data = str_replace( array( '\'' ), array( '"' ), preg_replace( '@([\\s\\,\\{])(\\w+):@', '$1"$2":', $data ) );
		$data = preg_replace( '@([\'"}])\\s*,\\s*}@', '$1}', $data );
		$data = str_replace( array( "\t", "\r", "\n" ), ' ', $data );
		$data = str_replace( "\x01", ':', $data );
		return( $data );
	}

	static function JsonGetEndPos( $posStart, $data )
	{
		$n = 0;
		for( $pos = $posStart; $pos < strlen( $data ); $pos++ )
		{
			if( $data[ $pos ] == '{' )
				$n++;
			else if( $data[ $pos ] == '}' )
			{
				$n--;
				if( !$n )
					break;
			}
		}

		return( $n ? null : ( $pos + 1 ) );
	}

	static function VarCmp( $v1, $v2 )
	{
		if( $v1 < $v2 )
			return( -1 );
		if( $v1 > $v2 )
			return( 1 );
		return( 0 );
	}

	static function VarExport( $v, $fmt = null, $level = 0 )
	{
		$fmt[ 'floatPrec' ] = Gen::GetArrField( $fmt, array( 'floatPrec' ), 15 );
		$fmt[ 'indent' ] = Gen::GetArrField( $fmt, array( 'indent' ), "\t" );
		$fmt[ 'elemSpace' ] = Gen::GetArrField( $fmt, array( 'elemSpace' ), "\n" );
		return( self::_VarExport( $v, $fmt, $level ) );
	}

	static private function _VarExport( $v, array $fmt, $level = 0 )
	{
		switch( gettype( $v ) )
		{
		case 'boolean':
			return( $v ? 'true' : 'false' );
		case 'integer':
			return( ( string )$v );
		case 'string':
			return( '\'' . str_replace( array( '\\', '\'' ), array( '\\\\', '\\\'' ), $v ) . '\'' );
		case 'double':
			return( preg_replace( '@([^\\.])0+$@', '${1}', sprintf( '%.' . ( string )$fmt[ 'floatPrec' ] . 'F', $v ) ) );

		case 'array':
			$res = 'array(' . $fmt[ 'elemSpace' ];
			foreach( $v as $k => $vI )
				$res .= str_repeat( $fmt[ 'indent' ], $level + 1 ) . self::_VarExport( $k, $fmt ) . ' => ' . self::_VarExport( $vI, $fmt, $level + 1 ) . ',' . $fmt[ 'elemSpace' ];
			$res .= str_repeat( $fmt[ 'indent' ], $level ) . ')';
			return( $res );

		case 'object':
			return( ( string )$v );
		}

		return( 'null' );
	}

	static function SliceExecTime( $procWorkInt, $procPauseInt, $abortCheckInt = 1, $cbIsAborted = null )
	{
		static $tmLastCoolingPause = 0.0;
		static $tmLastCheck = 0.0;
		static $resLastAbortCheck = false;

		$tmCur = microtime( true );

		if( ( float )$procWorkInt && ( float )$procPauseInt && ( $tmCur - $tmLastCoolingPause > ( float )$procWorkInt ) )
		{
			$wait = ( float )$procPauseInt;
			for( ;; )
			{
				if( $wait <= $abortCheckInt )
				{
					usleep( ( int )( 1000000 * $wait ) );
					break;
				}

				usleep( ( int )( 1000000 * $abortCheckInt ) );
				$wait -= $abortCheckInt;

				if( $cbIsAborted && call_user_func( $cbIsAborted ) )
					break;
			}

			$tmCur = microtime( true );
			$tmLastCoolingPause = $tmCur;
		}

		if( $tmCur - $tmLastCheck < $abortCheckInt )
			return( !$resLastAbortCheck );

		$tmLastCheck = $tmCur;
		if( !$resLastAbortCheck && $cbIsAborted )
			$resLastAbortCheck = call_user_func( $cbIsAborted );
		return( !$resLastAbortCheck );
	}

	static function GetNonce( $data, $key )
	{
		return( str_replace( array( '/', '+' ), '', rtrim( base64_encode( hash_hmac( 'md5', $data, $key, true ) ), '=' ) ) );
	}

	static function ParseProps( $props, $sep = ';', $sepVal = '=', $aDefs = null )
	{
		$a = array();
		foreach( explode( ( string )$sep, trim( ( string )$props, $sep ) ) as $p )
		{
			if( $sepVal === null )
			{
				$p = trim( $p );
				if( strlen( $p ) )
					$a[] = $p;
				continue;
			}

			$sepPos = strpos( $p, ( string )$sepVal );
			if( $sepPos !== false )
				$p = array( substr( $p, 0, $sepPos ), substr( $p, $sepPos + 1 ) );
			else
				$p = array( $p );

			$key = trim( $p[ 0 ] );

			$vDef = $aDefs ? (isset($aDefs[ $key ])?$aDefs[ $key ]:null) : null;
			if( isset( $p[ 1 ] ) )
			{
				$v = trim( $p[ 1 ] );

				if( $vDef !== null )
				{
					if( is_int( $vDef ) )
						$v = ( int )$v;
					else if( is_bool( $vDef ) )
						$v = ( bool )$v;
				}
			}
			else
				$v = $vDef !== null ? $vDef : '';

			$a[ $key ] = $v;
		}

		return( $a );
	}

	static function GarbageCollectorEnable( $enable )
	{
		if( $enable )
		{
			if( function_exists( 'gc_enable' ) )
				gc_enable();
		}
		else if( function_exists( 'gc_disable' ) )
			gc_disable();
	}

	static function SetTimeLimit( $seconds )
	{
		if( !function_exists( 'set_time_limit' ) )
			return( false );
		return( @set_time_limit( $seconds ) );
	}

	static private $_lastErrDsc = null;
	static private $_fnGetTmpDir = null;
}

class LocId
{
	static function Pack( $id, $comp = null, $args = null )
	{
		$text = "\x01" . $id;
		if( $comp )
			$text .= '.' . $comp;
		if( is_array( $args ) )
			$text .= ':' . @json_encode( array_map( function( $v ) { return( ( is_string( $v ) && substr( $v, 0, 1 ) != "\x01" ) ? preg_replace_callback( '@[\\%\\x80-\\xFF]@', function( $m ) { return( '%' . bin2hex( $m[ 0 ] ) ); }, $v ) : $v ); }, $args ) );
		return( $text );
	}

	static function UnPack( $v, $cbIdExpander )
	{
		$ctx = new AnyObj();
		$ctx -> cbIdExpander = $cbIdExpander;

		$ctx -> cb = function( $ctx, $v, $top = false )
		{
			if( is_array( $v ) )
				return( array_map( array( $ctx, 'cb' ), $v ) );

			if( !is_string( $v ) )
				return( $v );

			if( substr( $v, 0, 1 ) != "\x01" )
				return( $top ? $v : preg_replace_callback( '@\\%[0-9a-f]{2}@i', function( $m ) { return( hex2bin( substr( $m[ 0 ], 1 ) ) ); }, $v ) );

			$posArgs = strpos( $v, ':', 1 );
			if( $posArgs !== false )
			{
				$args = $ctx -> cb( @json_decode( substr( $v, $posArgs + 1 ), true ) );
				$v = substr( $v, 1, $posArgs - 1 );
			}
			else
			{
				$args = array();
				$v = substr( $v, 1 );
			}

			$posComp = strpos( $v, '.' );
			if( $posComp !== false )
			{
				$comp = substr( $v, $posComp + 1 );
				$v = substr( $v, 0, $posComp );
			}
			else
				$comp = null;

			return( vsprintf( call_user_func( $ctx -> cbIdExpander, $v, $comp ), $args ) );
		};

		return( $ctx -> cb( $v, true ) );
	}
}

class AnyObj extends \stdClass
{
	public function __construct( array $args = array() )
	{
		foreach( $args as $argId => $arg )
			$this -> { $argId } = $arg;
	}

	public function __call( $method, $args )
	{
		array_splice( $args, 0, 0, array( $this ) );
		return( call_user_func_array( $this -> { $method }, $args ) );
	}
}

class Lock
{
	function __construct( $id, $dir = null, $del = false )
	{
		if( $dir !== false )
		{
			if( $dir === null )
				$dir = Gen::GetTempDir();
			$this -> file = rtrim( $dir, '/\\' ) . '/';
		}
		else
			$this -> file = '';

		$this -> file .= $id;
		$this -> mode = $del ? 1 : 0;
	}

	function __destruct()
	{
		$this -> Release();
	}

	function Acquire( $wait = true, $mode = LOCK_EX  )
	{
		if( $this -> h )
			return( null );

		$this -> hr = Gen::FileOpenWithMakeDir( $this -> h, $this -> file, 'c' );
		if( !$this -> h )
			return( false );

		if( $wait === true )
		{
			if( @flock( $this -> h, $mode ) )
			{
				$this -> mode |= 2;
				return( true );
			}

			$this -> Release();
			$this -> hr = Gen::E_FAIL;
			return( false );
		}

		if( @flock( $this -> h, LOCK_NB | $mode ) )
		{
			$this -> mode |= 2;
			return( true );
		}

		$wait = ( float )$wait;

		while( $wait )
		{
			usleep( 250 * 1000 );

			if( @flock( $this -> h, LOCK_NB | $mode ) )
			{
				$this -> mode |= 2;
				return( true );
			}

			if( $wait > 0.250 )
				$wait -= 0.250;
			else
				$wait = 0.0;
		}

		$this -> Release();
		$this -> hr = Gen::E_BUSY;
		return( false );
	}

	function Release()
	{
		if( !$this -> h )
			return;

		if( $this -> mode & 2 )
		{
			$this -> mode &= ~2;
			@flock( $this -> h, LOCK_UN );

		}

		@fclose( $this -> h );
		$this -> h = null;

		if( $this -> mode & 1 )
			@unlink( $this -> file );
	}

	function GetFileName()
	{
		return( $this -> file );
	}

	function GetErrDescr()
	{
		$dir = Gen::GetFileDir( $this -> file );
		return( @is_writable( $dir ) ? LocId::Pack( 'FileModifyErr_%1$s', 'Common', array( $this -> file ) ) : LocId::Pack( 'DirWriteErr_%1$s', 'Common', array( $dir ) ) );
	}

	private $file;
	private $h;
	private $mode;
	private $hr;
}

class Bs
{
	static function Find( $nElemCount, $findValue, $cbValCmp, &$pnFoundIndex )
	{
		$pBs = new \stdClass();
		$pBs -> nCurIndex = null;
		$pBs -> nCurSize = null;

		$iCmpResult = -1;
		$bRetVal = false;

		$bSearch = self::_GetFirstIndex( $pBs, $nElemCount, $i );
		while( $bSearch )
		{
			$iCmpResult = call_user_func( $cbValCmp, $i, $findValue );
			if( is_string( $iCmpResult ) )
			{
				$bRetVal = $iCmpResult;
				break;
			}

			if( $iCmpResult == 0 )
			{
				$bRetVal = true;
				break;
			}
			else
				$iSrchWay = -$iCmpResult;

			$bSearch = self::_GetNextIndex( $pBs, $iSrchWay, $i );
		}

		if(	$nElemCount && $iCmpResult == -1 )
			$i++;

		$pnFoundIndex = $i;
		return( $bRetVal );
	}

	static private function _GetFirstIndex( $pBs, $nElemCount, &$pnCurIndex )
	{
		$fRetVal = false;

		$pBs -> nCurIndex = $nElemCount - ( int )( $nElemCount / 2 ) - 1;
		$pBs -> nCurSize = $nElemCount;

		if( $nElemCount )
			$fRetVal = true;
		else
			$pBs -> nCurIndex = 0;

		$pnCurIndex = $pBs -> nCurIndex;
		return( $fRetVal );
	}

	static private function _GetNextIndexEx( &$pnCurBlockSize, &$pnCurIndex, $iSign )
	{
		$fRetVal = true;

		$nCurIndex = $pnCurIndex;

		if( $iSign == 1 )
		{
			$pnCurBlockSize = ( int )( $pnCurBlockSize / 2 );
			$nCurIndex -= ( int )( $pnCurBlockSize / 2 ) - $pnCurBlockSize;
		}
		else
		{
			$pnCurBlockSize = ( int )( $pnCurBlockSize / 2 ) - ( 1 - ( $pnCurBlockSize % 2 ) );
			$nCurIndex -= ( int )( $pnCurBlockSize / 2 ) + 1;
		}

		if( $pnCurBlockSize )
			$pnCurIndex = $nCurIndex;
		else
			$fRetVal = false;

		return( $fRetVal );
	}

	static private function _GetNextIndex( $pBs, $iSign, &$pnCurIndex )
	{
		$fRetVal = self::_GetNextIndexEx( $pBs -> nCurSize, $pBs -> nCurIndex, $iSign );
		$pnCurIndex = $pBs -> nCurIndex;
		return( $fRetVal );
	}
}

class ArrayOnFiles implements \Iterator, \ArrayAccess, \Countable
{
	private $dir;
	private $options;
	private $iIterChunk;
	private $iChunk;
	private $aChunk;

	public function __construct( $dirFilesPattern , $options = null )
	{
		if( is_array( $dirFilesPattern ) )
		{
		    $options = (isset($dirFilesPattern[ 'options' ])?$dirFilesPattern[ 'options' ]:null);
		    $dirFilesPattern = (isset($dirFilesPattern[ 'dirFilesPattern' ])?$dirFilesPattern[ 'dirFilesPattern' ]:null);
		}

		$this -> dir = explode( '*', $dirFilesPattern );
		$this -> options = array_merge( array( 'countPerChunk' => 1000, 'countSep' => '@', 'compr' => 'gz', 'comprLev' => 1, 'keys' => true , 'cbSort' => null ), ( array )$options );
		$this -> iIterChunk = -2;
		$this -> iChunk = 0;
		$this -> aChunk = array();

		if( !isset( $this -> options[ 'countPerFirstChunk' ] ) )
			$this -> options[ 'countPerFirstChunk' ] = $this -> options[ 'countPerChunk' ];

		foreach( glob( $dirFilesPattern, GLOB_NOSORT ) as $file )
		{
			$chunk = new \stdClass();
			$chunk -> id = substr( $file, strlen( $this -> dir[ 0 ] ), strlen( $file ) - ( strlen( $this -> dir[ 0 ] ) + strlen( $this -> dir[ 1 ] ) ) );
			$chunk -> a = null;
			$chunk -> dirty = false;

			$idxAndCount = explode( $this -> options[ 'countSep' ], $chunk -> id );
			if( count( $idxAndCount ) != 2 )
				continue;

			$chunk -> idx = @intval( $idxAndCount[ 0 ], 36 );
			if( base_convert( ( string )$chunk -> idx, 10, 36 ) !== $idxAndCount[ 0 ] )
				continue;

			$chunk -> n = @intval( $idxAndCount[ 1 ], 36 );
			if( base_convert( ( string )$chunk -> n, 10, 36 ) !== $idxAndCount[ 1 ] )
				continue;

			$this -> aChunk[] = $chunk;
		}

		usort( $this -> aChunk, function( $chunk1, $chunk2 ) { return( Gen::VarCmp( $chunk1 -> idx, $chunk2 -> idx ) ); } );
    }

	#[\ReturnTypeWillChange]
	public function current()
	{
		if( !$this -> _InitCurIterChunk() )
			return( false );
		return( current( $this -> aChunk[ $this -> iIterChunk ] -> a ) );
	}

	#[\ReturnTypeWillChange]
	public function key()
	{
		if( !$this -> _InitCurIterChunk() )
			return( null );
		return( key( $this -> aChunk[ $this -> iIterChunk ] -> a ) );
	}

	#[\ReturnTypeWillChange]
	public function next()
	{
		$this -> getNext();
	}

	#[\ReturnTypeWillChange]
	public function prev()
	{
		$this -> getPrev();
	}

	#[\ReturnTypeWillChange]
	public function rewind()
	{
		$this -> reset();
	}

	#[\ReturnTypeWillChange]
	public function reset()
	{
		$this -> iIterChunk = -1;
		$this -> next();
		$this -> _UnloadUnusedChunks();
	}

	#[\ReturnTypeWillChange]
	public function end()
	{
		$this -> iIterChunk = count( $this -> aChunk );
		$this -> prev();
		$this -> _UnloadUnusedChunks();
	}

	#[\ReturnTypeWillChange]
	public function valid()
	{
		return( $this -> _InitCurIterChunk() );
	}

	#[\ReturnTypeWillChange]
	public function count()
	{
		$n = 0;
		foreach( $this -> aChunk as $chunk )
			$n += $chunk -> n;
		return( $n );
	}

	#[\ReturnTypeWillChange]
	public function offsetExists( $key )
	{
		$this -> _UnloadUnusedChunks();

		foreach( $this -> aChunk as $this -> iChunk => $chunk )
		{
			$this -> _ChunkLoad( $chunk );

			if( isset( $chunk -> a[ $key ] ) )
				return( true );

			if( $this -> iChunk != $this -> iIterChunk )
				$this -> _ChunkUnLoad( $chunk );
		}

		return( false );
	}

	#[\ReturnTypeWillChange]
	public function offsetGet( $key )
	{
		$this -> _UnloadUnusedChunks();

		foreach( $this -> aChunk as $this -> iChunk => $chunk )
		{
			$this -> _ChunkLoad( $chunk );

			if( isset( $chunk -> a[ $key ] ) )
				return( $chunk -> a[ $key ] );

			if( $this -> iChunk != $this -> iIterChunk )
				$this -> _ChunkUnLoad( $chunk );
		}

		return( null );
	}

	#[\ReturnTypeWillChange]
	public function offsetUnset( $key )
	{
		$this -> unsetItem( $key );
	}

	#[\ReturnTypeWillChange]
	public function offsetSet( $key, $value )
	{
		$this -> setItem( $key, $value );
	}

	public function getNext()
	{
		if( $this -> iIterChunk === -2 )
			$this -> iIterChunk = 0;

		$reset = false;
		if( $this -> iIterChunk < 0 )
		{
			$this -> iIterChunk = 0;
			$reset = true;
		}

		for( ; $this -> iIterChunk < count( $this -> aChunk ); $this -> iIterChunk++ )
		{
			$chunk = $this -> aChunk[ $this -> iIterChunk ];
			$this -> _ChunkLoad( $chunk );

			$next = ( $reset || key( $chunk -> a ) === null ) ? reset( $chunk -> a ) : next( $chunk -> a );
			if( $next !== false || key( $chunk -> a ) !== null )
				return( $next );

			if( $this -> iIterChunk != $this -> iChunk )
				$this -> _ChunkUnLoad( $chunk );

			$reset = true;
		}

		return( false );
	}

	public function getPrev()
	{
		if( $this -> iIterChunk === -2 )
			$this -> iIterChunk = 0;

		$reset = false;
		if( $this -> iIterChunk >= count( $this -> aChunk ) )
		{
			$this -> iIterChunk = count( $this -> aChunk ) - 1;
			$reset = true;
		}

		for( ; $this -> iIterChunk >= 0; $this -> iIterChunk-- )
		{
			$chunk = $this -> aChunk[ $this -> iIterChunk ];
			$this -> _ChunkLoad( $chunk );

			$prev = ( $reset || key( $chunk -> a ) === null ) ? end( $chunk -> a ) : prev( $chunk -> a );
			if( $prev !== false || key( $chunk -> a ) !== null )
				return( $prev );

			if( $this -> iIterChunk != $this -> iChunk )
				$this -> _ChunkUnLoad( $chunk );

			$reset = true;
		}

		return( false );
	}

	public function unsetItem( $key )
	{
		foreach( $this -> aChunk as $this -> iChunk => $chunk )
		{
			$this -> _ChunkLoad( $chunk );

			if( isset( $chunk -> a[ $key ] ) )
			{
				unset( $chunk -> a[ $key ] );
				$chunk -> n = count( $chunk -> a );
				$chunk -> dirty = true;

				return( $this -> _ChunksUpdate() );
			}

			if( $this -> iChunk != $this -> iIterChunk )
				$this -> _ChunkUnLoad( $chunk );
		}

		return( null );
	}

	public function setItem( $key, $value )
	{
		$this -> _UnloadUnusedChunks();

		if( !$this -> _setItem( $key, $value ) )
			return( false );

		$res = $this -> _ChunksUpdate();
		$this -> _UnloadUnusedChunks();
		return( $res );
	}

	public function setItems( array $a, $saveMem = false )
	{
		if( $saveMem )
			$this -> _UnloadUnusedChunks();

		foreach( $a as $key => $value )
			if( !$this -> _setItem( $key, $value, $saveMem ) )
				return( false );

		$res = $this -> _ChunksUpdate();
		$this -> _UnloadUnusedChunks();
		return( $res );
	}

	private function _setItem( $key, $value, $saveMem = true )
	{
		if( $this -> options[ 'keys' ] && $key !== null )
		{
			foreach( $this -> aChunk as $this -> iChunk => $chunk )
			{
				$this -> _ChunkLoad( $chunk );

				if( isset( $chunk -> a[ $key ] ) )
				{
					if( !$this -> options[ 'cbSort' ] || call_user_func( $this -> options[ 'cbSort' ], $chunk -> a[ $key ], $value ) === 0 )
					{
						$chunk -> a[ $key ] = $value;
						$chunk -> dirty = true;
						return( true );
					}

					unset( $chunk -> a[ $key ] );
					$chunk -> n = count( $chunk -> a );
					$chunk -> dirty = true;
					break;
				}

				if( $saveMem && $this -> iChunk != $this -> iIterChunk )
					$this -> _ChunkUnLoad( $chunk );
			}
		}

		if( $this -> options[ 'cbSort' ] )
		{

			for( $iTry = 0; $iTry < count( $this -> aChunk ) + 1; $iTry++ )
			{
				$res = Bs::Find( $this -> count(), array( $key => $value ), array( $this, '_cbBsFind' ), $iInsert );
				if( $res === 'e' )
					return( false );
				if( $res !== 'r' )
					break;
			}

			if( $iTry == count( $this -> aChunk ) + 1 )
				return( false );
		}
		else
			$iInsert = $this -> count();

		$this -> _IdxToChunkIdx( $iInsert, $this -> iChunk, $chunk, 1 );

		if( !$chunk )
		{
			$chunk = new \stdClass();
			$chunk -> id = null;
			$chunk -> idx = null;
			$chunk -> a = array();
			$chunk -> n = 0;

			$this -> aChunk[] = $chunk;
		}
		else
			$this -> _ChunkLoad( $chunk );

		if( $key === null )
			$key = 0;

		Gen::ArrSplice( $chunk -> a, $iInsert, 0, array( $key => $value ), $this -> options[ 'keys' ] );

		$chunk -> n = count( $chunk -> a );
		$chunk -> dirty = true;
		return( true );
	}

	function slice( $offset, $length = null )
	{
		$res = array();
		$offset = ( int )$offset;

		if( !$this -> _IdxToChunkIdx( $offset, $iChunk, $chunk ) )
			return( $res );

		for( ; $iChunk < count( $this -> aChunk ); $iChunk++ )
		{
			$chunk = $this -> aChunk[ $iChunk ];
			$this -> _ChunkLoad( $chunk );

			$n = $length === null ? $chunk -> n : $length;
			$a = array_slice( $chunk -> a, $offset, $n, true );
			$res += $a;

			if( $iChunk != $this -> iIterChunk && $iChunk != $this -> iChunk )
				$this -> _ChunkUnLoad( $chunk );

			if( $length !== null )
			{
				$length -= count( $a );
				if( $length <= 0 )
					break;
			}

			$offset = 0;
		}

		return( $res );
	}

	function splice( $offset = null, $length = null )
	{
		$res = array();
		$offset = ( int )$offset;

		if( !$this -> _IdxToChunkIdx( $offset, $iChunk, $chunk ) )
			return( $res );

		for( ; $iChunk < count( $this -> aChunk ); $iChunk++ )
		{
			$chunk = $this -> aChunk[ $iChunk ];
			$this -> _ChunkLoad( $chunk );

			$n = $length === null ? $chunk -> n : $length;
			$a = Gen::ArrSplice( $chunk -> a, $offset, $n, array(), $this -> options[ 'keys' ] );
			$res += $a;

			$chunk -> n = count( $chunk -> a );
			$chunk -> dirty = true;

			if( $iChunk != $this -> iIterChunk && $iChunk != $this -> iChunk )
				$this -> _ChunkUnLoad( $chunk );

			if( $length !== null )
			{
				$length -= count( $a );
				if( $length <= 0 )
					break;
			}

			$offset = 0;
		}

		$this -> _ChunksUpdate();
		$this -> _UnloadUnusedChunks();
		return( $res );
	}

	function clear()
	{
		foreach( $this -> aChunk as $chunk )
		{
			$chunk -> n = 0;
			$chunk -> a = null;
			$chunk -> dirty = true;
		}

		return( $this -> _ChunksUpdate() );
	}

	function dispose()
	{
		foreach( $this -> aChunk as $chunk )
		    $chunk -> a = null;
		$this -> aChunk = null;
		$this -> dir = null;
		$this -> options = null;
	}

	private function _IdxToChunkIdx( &$i, &$iChunk, &$chunk, $nCompensation = 0 )
	{
		foreach( $this -> aChunk as $iChunk => $chunk )
		{
			if( $i < $chunk -> n + $nCompensation )
				return( true );
			$i -= $chunk -> n;
		}

		return( false );
	}

	function _cbBsFind( $i, $itemFind )
	{
		$iChunkPrev = $this -> iChunk;
		$this -> _IdxToChunkIdx( $i, $this -> iChunk, $chunk );

		if( $iChunkPrev != $this -> iIterChunk && $iChunkPrev != $this -> iChunk )
			$this -> _ChunkUnLoad( $this -> aChunk[ $iChunkPrev ] );

		if( !$this -> _ChunkLoad( $chunk ) )
		{
			if( $this -> _ChunkUpdate( $chunk, $chunk -> idx ) === false )
				return( 'e' );
			return( 'r' );
		}

		$item = array_slice( $chunk -> a, $i, 1, true );

		$resCmp = call_user_func( $this -> options[ 'cbSort' ], current( $item ), current( $itemFind ) );
		if( $resCmp !== 0 )
			return( $resCmp );
		return( Gen::VarCmp( key( $item ), key( $itemFind ) ) );
	}

	private function _ChunkLoad( $chunk )
	{
		if( $chunk -> a !== null )
			return( true );

		if( $chunk -> id !== null )
		{
			$file = $this -> dir[ 0 ] . $chunk -> id . $this -> dir[ 1 ];

			$chunk -> a = @file_get_contents( $file );
			if( $this -> options[ 'compr' ] == 'gz' && is_string( $chunk -> a ) )
				$chunk -> a = @gzdecode( $chunk -> a );
			if( is_string( $chunk -> a ) )
				$chunk -> a = @unserialize( $chunk -> a );
		}

		if( !is_array( $chunk -> a ) )
			$chunk -> a = array();

		$nPrev = $chunk -> n;
		$chunk -> n = count( $chunk -> a );
		if( $nPrev == $chunk -> n )
			return( true );

		$chunk -> dirty = true;
		return( false );
	}

	private function _ChunkUnLoad( $chunk )
	{
		if( !$chunk -> dirty )
			$chunk -> a = null;
	}

	private function _InitCurIterChunk()
	{
		if( $this -> iIterChunk === -2 )
		{
			$this -> iIterChunk = -1;
			$this -> next();
		}

		if( $this -> iIterChunk < 0 || $this -> iIterChunk >= count( $this -> aChunk ) )
			return( false );

		$chunk = $this -> aChunk[ $this -> iIterChunk ];
		$this -> _ChunkLoad( $chunk );
		return( true );
	}

	private function _UnloadUnusedChunks()
	{
		foreach( $this -> aChunk as $iChunk => $chunk )
			if( $iChunk != $this -> iIterChunk && $iChunk != $this -> iChunk )
				$this -> _ChunkUnLoad( $chunk );
	}

	private function _ChunkUpdate( $chunk, $idxNew )
	{
		if( !$chunk -> dirty && $chunk -> idx === $idxNew )
			return( null );

		$filePrev = $chunk -> id !== null ? ( $this -> dir[ 0 ] . $chunk -> id . $this -> dir[ 1 ] ) : null;
		$file = $chunk -> n ? ( $this -> dir[ 0 ] . base_convert( ( string )$idxNew, 10, 36 ) . $this -> options[ 'countSep' ] . base_convert( ( string )$chunk -> n, 10, 36 ) . $this -> dir[ 1 ] ) : null;

		if( $file )
		{
			if( $filePrev && $filePrev != $file && !@rename( $filePrev, $file ) )
			{
				Gen::LastErrDsc_Set( LocId::Pack( 'FileRenameErr_%1$s%2$s', 'Common', array( $filePrev, $file ) ) );

				return( false );
			}

			$chunk -> id = substr( $file, strlen( $this -> dir[ 0 ] ), strlen( $file ) - ( strlen( $this -> dir[ 0 ] ) + strlen( $this -> dir[ 1 ] ) ) );
			$chunk -> idx = $idxNew;

			if( $chunk -> dirty && $chunk -> a !== null )
			{
				$fileTmp = $this -> dir[ 0 ] . '_' . $this -> dir[ 1 ] . '.tmp';

				{
					$data = @serialize( $chunk -> a );
					if( $this -> options[ 'compr' ] == 'gz' )
						$data = @gzencode( $data, $this -> options[ 'comprLev' ] );

					if( Gen::FilePutContentWithMakeDir( $fileTmp, ( string )$data ) != Gen::S_OK )
					{
						Gen::LastErrDsc_Set( LocId::Pack( 'FileWriteErr_%1$s', 'Common', array( $fileTmp ) ) );

						return( false );
					}

					unset( $data );
				}

				if( !@rename( $fileTmp, $file ) )
				{
					Gen::LastErrDsc_Set( LocId::Pack( 'FileRenameErr_%1$s%2$s', 'Common', array( $fileTmp, $file ) ) );

					return( false );
				}
			}
		}
		else
		{
			if( $filePrev && !@unlink( $filePrev ) && file_exists( $filePrev ) )
			{
				Gen::LastErrDsc_Set( LocId::Pack( 'FileDeleteErr_%1$s', 'Common', array( $filePrev ) ) );

				return( false );
			}

			$chunk -> id = null;
			$chunk -> idx = $idxNew;
		}

		$chunk -> dirty = false;
		return( true );
	}

	private function _ChunksUpdate()
	{
		$res = null;

		$nCurChunkMax = $this -> options[ 'countPerFirstChunk' ];
		for( $iChunk = 0; $iChunk < count( $this -> aChunk ); $iChunk++ )
		{
			$chunk = $this -> aChunk[ $iChunk ];

			if( !$chunk -> n )
			{
				$r = $this -> _ChunkUpdate( $chunk, $iChunk );
				if( $r === false )
					return( false );
				if( $r === true )
					$res = true;

				array_splice( $this -> aChunk, $iChunk, 1 );
				$iChunk--;

				if( $this -> iIterChunk >= $iChunk )
					$this -> iIterChunk --;

				continue;
			}

			if( $chunk -> n < $nCurChunkMax )
			{

				for( $iChunkNext = $iChunk + 1; $iChunkNext < count( $this -> aChunk ); $iChunkNext++ )
				{
					$chunkNext = $this -> aChunk[ $iChunkNext ];
					if( !$chunkNext -> n )
						continue;

					if( ( $chunk -> n + $chunkNext -> n ) > $nCurChunkMax )
						break;

					$this -> _ChunkLoad( $chunk );
					$this -> _ChunkLoad( $chunkNext );

					Gen::ArrSplice( $chunk -> a, count( $chunk -> a ), 0, $chunkNext -> a, $this -> options[ 'keys' ] );
					$chunk -> n = count( $chunk -> a );
					$chunk -> dirty = true;

					$chunkNext -> a = array();
					$chunkNext -> n = 0;
					$chunkNext -> dirty = true;
				}

			}
			else if( $chunk -> n > $nCurChunkMax )
			{
				$this -> _ChunkLoad( $chunk );
				$a = $chunk -> a;

				for( $chunkSplit = $chunk, $nChunkAdd = 0; ; $nChunkAdd++ )
				{
					$chunkSplit -> a = Gen::ArrSplice( $a, 0, $nCurChunkMax, array(), $this -> options[ 'keys' ] );
					$chunkSplit -> n = count( $chunkSplit -> a );
					$chunkSplit -> dirty = true;

					if( !count( $a ) )
						break;

					$chunkSplit = new \stdClass();
					$chunkSplit -> id = null;
					$chunkSplit -> idx = null;
					$chunkSplit -> a = null;
					$chunkSplit -> n = 0;
					array_splice( $this -> aChunk, $iChunk + $nChunkAdd + 1, 0, array( $chunkSplit ) );

					$nCurChunkMax = $this -> options[ 'countPerChunk' ];
				}

				unset( $a, $chunkSplit );

				for( $iChunkShift = count( $this -> aChunk ) - 1; $iChunkShift >= $iChunk + $nChunkAdd + 1; $iChunkShift-- )
				{
					$chunkShift = $this -> aChunk[ $iChunkShift ];
					if( $chunkShift -> idx !== null )
					{
						$r = $this -> _ChunkUpdate( $chunkShift, $chunkShift -> idx + $nChunkAdd );
						if( $r === false )
							return( false );
						if( $r === true )
							$res = true;
					}
				}

				if( $this -> iIterChunk > $iChunk )
					$this -> iIterChunk += $nChunkAdd;
			}

			$r = $this -> _ChunkUpdate( $chunk, $iChunk );
			if( $r === false )
				return( false );
			if( $r === true )
				$res = true;

			$nCurChunkMax = $this -> options[ 'countPerChunk' ];
		}

		return( $res );
	}

}

class DateTime
{

	const FMT_MINUTE					= 'i';
	const FMT_HOUR						= 'H';
	const FMT_WEEKDAY					= 'N';
	const FMT_DAY						= 'd';
	const FMT_WEEK						= 'W';
	const FMT_WEEK_USINGFIRSTDAY		= 'W+';
	const FMT_MONTH						= 'n';
	const FMT_YEAR						= 'o';

	const RFC2822						= "D, d M Y H:i:s O";

	static function GetFmtVals( $dt, $firstWeekDay = 1, $a = array( DateTime::FMT_YEAR, DateTime::FMT_MONTH, DateTime::FMT_WEEK, DateTime::FMT_DAY, DateTime::FMT_WEEKDAY, DateTime::FMT_HOUR, DateTime::FMT_MINUTE ) )
	{
		if( !$dt )
			return( array() );

		$fmt = $dt -> format( implode( "\n", $a ) );
		if( !$fmt )
			return( array() );

		$fmt = explode( "\n", $fmt );
		$res = array();
		foreach( $a as $i => $k )
			$res[ $k ] = ( int )$fmt[ $i ];

		if( isset( $res[ DateTime::FMT_WEEK ] ) )
		{

				$res[ DateTime::FMT_WEEK_USINGFIRSTDAY ] = $res[ DateTime::FMT_WEEK ];
		}

		return( $res );
	}
}

class DateTimeZone
{
	static function FromOffset( $offset = null  )
	{
		$offset = ( int )$offset;

		$prefix = 'GMT+';
		if( $offset < 0 )
		{
			$offset *= -1;
			$prefix = 'GMT-';
		}

		return( new \DateTimeZone( $prefix . sprintf( '%02d:%02d', $offset / ( 60 * 60 ), ( $offset % ( 60 * 60 ) ) / 60 ) ) );
	}
}

class DateInterval
{

	static function FromMinutes( $v )
	{
		return( \DateInterval::createFromDateString( ( string )$v . ' min' ) );
	}

	static function FromHours( $v )
	{
		return( \DateInterval::createFromDateString( ( string )$v . ' hour' ) );
	}

	static function FromDays( $v )
	{
		return( \DateInterval::createFromDateString( ( string )$v . ' day' ) );
	}

	static function FromWeeks( $v )
	{
		return( \DateInterval::createFromDateString( ( string )$v . ' weeks' ) );
	}

	static function FromMonths( $v )
	{
		return( \DateInterval::createFromDateString( ( string )$v . ' month' ) );
	}

	static function FromYears( $v )
	{
		return( \DateInterval::createFromDateString( ( string )$v . ' year' ) );
	}
}

class Lang
{
	static function GetLang2LocData()
	{
		$map = array(
			'ar'			=> array( 'ar', 'ary' ),
			'az'			=> array( 'az', 'azb' ),
			'be'			=> array( 'bel' ),
			'bg'			=> array( 'bg_BG' ),
			'bn'			=> array( 'bn_BD' ),
			'bs'			=> array( 'bs_BA' ),
			'cs'			=> array( 'cs_CZ' ),
			'da'			=> array( 'da_DK' ),
			'de'			=> array( 'de_DE_formal', 'de_CH', 'de_CH_informal', 'de_DE' ),
			'dz'			=> array( 'dzo' ),
			'en'			=> array( 'en_US', 'en_ZA', 'en_CA', 'en_AU', 'en_NZ', 'en_GB' ),
			'es'			=> array( 'es_ES' ),
			'es-MX'			=> array( 'es_MX', 'es_CL', 'es_GT', 'es_VE', 'es_CR', 'es_PE', 'es_AR', 'es_CO' ),
			'fa'			=> array( 'fa_IR' ),
			'fr'			=> array( 'fr_FR', 'fr_BE', 'fr_CA' ),
			'gl'			=> array( 'gl_ES' ),
			'he'			=> array( 'he_IL' ),
			'hi'			=> array( 'hi_IN' ),
			'hu'			=> array( 'hu_HU' ),
			'id'			=> array( 'id_ID' ),
			'is'			=> array( 'is_IS' ),
			'it'			=> array( 'it_IT' ),
			'jv'			=> array( 'jv_ID' ),
			'ka'			=> array( 'ka_GE' ),
			'ko'			=> array( 'ko_KR' ),
			'ku'			=> array( 'ckb' ),
			'lt'			=> array( 'lt_LT' ),
			'mk'			=> array( 'mk_MK' ),
			'ml'			=> array( 'ml_IN' ),
			'ms'			=> array( 'ms_MY' ),
			'my'			=> array( 'my_MM' ),
			'nb'			=> array( 'nb_NO' ),
			'ne'			=> array( 'ne_NP' ),
			'nl'			=> array( 'nl_BE', 'nl_NL', 'nl_NL_formal' ),
			'nn'			=> array( 'nn_NO' ),
			'oc'			=> array( 'oci' ),
			'pa'			=> array( 'pa_IN' ),
			'pl'			=> array( 'pl_PL' ),
			'pt'			=> array( 'pt_PT', 'pt_PT_ao90' ),
			'pt-BR'			=> array( 'pt_BR' ),
			'ro'			=> array( 'ro_RO' ),
			'ru'			=> array( 'ru_RU' ),
			'si'			=> array( 'si_LK' ),
			'sk'			=> array( 'sk_SK' ),
			'sl'			=> array( 'sl_SI' ),
			'sr'			=> array( 'sr_RS' ),
			'sv'			=> array( 'sv_SE' ),
			'ta'			=> array( 'ta_IN' ),
			'tr'			=> array( 'tr_TR' ),
			'tt'			=> array( 'tt_RU' ),
			'ty'			=> array( 'tah' ),
			'ug'			=> array( 'ug_CN' ),
			'uz'			=> array( 'uz_UZ' ),
			'zh'			=> array( 'zh_CN', 'zh_HK', 'zh_TW' ),
		);

		return( $map );
	}

	static function GetLangFromLocale( $locale )
	{
		if( empty( $locale ) )
			return( null );

		$data = self::GetLang2LocData();

		foreach( $data as $dataLang => $dataLocales )
			if( array_search( $locale, $dataLocales ) !== false )
				return( $dataLang );

		return( str_replace( '_', '-', $locale ) );
	}

	static function GetLocalesFromLang( $lang )
	{
		if( empty( $lang ) )
			return( array() );

		$data = self::GetLang2LocData();

		$dataLocales = isset( $data[ $lang ] ) ? $data[ $lang ] : null;
		if( !empty( $dataLocales ) )
			return( $dataLocales );

		return( array( str_replace( '-', '_', $lang ) ) );
	}
}

class Net
{
	const E_TIMEOUT									= 0x800C2EE2;

	const E_HTTP_STATUS_BEGIN						= 0x100;
	const E_HTTP_STATUS_END							= 0x400;

	static function GetHrFromResponseCode( $code, $soft = false )
	{
		return( Gen::HrMake( $code < ( $soft ? 500 : 400 ) ? Gen::SEVERITY_SUCCESS : Gen::SEVERITY_ERROR, Gen::FACILITY_HTTP, Net::E_HTTP_STATUS_BEGIN + $code ) );
	}

	static function GetResponseCodeFromHr( $hr )
	{
		if( Gen::HrFacility( $hr ) != Gen::FACILITY_HTTP )
			return( null );
		$hr = Gen::HrCode( $hr );
		if( $hr < Net::E_HTTP_STATUS_BEGIN || $hr > Net::E_HTTP_STATUS_END )
			return( null );
		return( $hr - Net::E_HTTP_STATUS_BEGIN );
	}

	static function GetHrFromWpRemoteGet( $requestRes, $soft = false, $smart = false )
	{
		if( !$requestRes )
			return( Gen::E_FAIL );

		if( !is_wp_error( $requestRes ) )
		{
			$httpStatus = wp_remote_retrieve_response_code( $requestRes );
			if( $httpStatus == 200 || $httpStatus === false )
				return( Gen::S_OK );

			$hr = Net::GetHrFromResponseCode( $httpStatus, $soft );
			if( $smart )
			{
				if( $httpStatus == 404 )
					$hr = Gen::E_NOT_FOUND;
			}

			return( $hr );
		}

		$errCode = $requestRes -> get_error_code();
		$errMsg = $requestRes -> get_error_message( $errCode );

		if( $errCode == 'http_request_failed' && strpos( $errMsg, 'cURL error 28:' ) !== false )
			return( Net::E_TIMEOUT );

		if( Gen::StrStartsWith( $errCode, 'seraph_accel:hr:0x' ) )
			return( 0xFFFFFFFF & intval( substr( $errCode, strlen( 'seraph_accel:hr:0x' ) ), 16 ) );

		return( Gen::E_FAIL );
	}

	static function GetHeadersFromWpRemoteGet( $requestRes )
	{
		$hdrs = wp_remote_retrieve_headers( $requestRes );
		if( is_a( $hdrs, 'Requests_Utility_CaseInsensitiveDictionary' ) )
			$hdrs = $hdrs -> getAll();

		return( is_array( $hdrs ) ? $hdrs : array() );
	}

	static function GetSiteAddrFromUrl( $url, $withScheme = false )
	{
		$siteUrlParts = @parse_url( $url );
		if( !is_array( $siteUrlParts ) )
			return( null );
		return( ( ( $withScheme && isset( $siteUrlParts[ 'scheme' ] ) ) ? ( $siteUrlParts[ 'scheme' ] . '://' ) : '' ) . ( isset( $siteUrlParts[ 'host' ] ) ? $siteUrlParts[ 'host' ] : '' ) . ( isset( $siteUrlParts[ 'port' ] ) ? ( ':' . $siteUrlParts[ 'port' ] ) : '' ) );
	}

	static function GetUrlWithoutProtoEx( $url, &$proto )
	{
		$pos = strpos( $url, '://' );
		if( $pos === false )
			return( $url );

		$proto = substr( $url, 0, $pos );
		return( substr( $url, $pos + 3 ) );
	}

	static function GetUrlWithoutProto( $url )
	{
		$proto = '';
		return( Net::GetUrlWithoutProtoEx( $url, $proto ) );
	}

	static function Url2Uri( $url, $siteUrlRelative = false )
	{
		if( !$siteUrlRelative )
		{
			$url = Net::GetUrlWithoutProto( $url );

			$pos = strpos( $url, '/' );
			if( $pos === false )
				return( '' );

			return( substr( $url, $pos ) );
		}

		$siteUrl = Net::GetUrlWithoutProto( Gen::SetLastSlash( $siteUrlRelative === 'home' ? Wp::GetSiteRootUrl( '', false ) : Wp::GetSiteWpRootUrl(), false ) );
		$url = Net::GetUrlWithoutProto( $url );

		if( strpos( $url, $siteUrl ) !== 0 )
			return( $url );
		return( substr( $url, strlen( $siteUrl ) ) );
	}

	static function GetRequestHost( $serverArgs = null )
	{
		if( $serverArgs === null )
			$serverArgs = $_SERVER;

		if( isset( $serverArgs[ 'HTTP_HOST' ] ) )
			return( $serverArgs[ 'HTTP_HOST' ] );

		$host = isset( $serverArgs[ 'SERVER_NAME' ] ) ? $serverArgs[ 'SERVER_NAME' ] : '';
		$port = isset( $serverArgs[ 'SERVER_PORT' ] ) ? $serverArgs[ 'SERVER_PORT' ] : null;
		if( $port && $port != '443' && $port != '80' )
			$host .= ':' . $port;

		return( $host );
	}

	static function GetRequestHeaders( $serverArgs = null, $bAssoc = true, $bNorm = false )
	{
		if( $serverArgs === null )
			$serverArgs = $_SERVER;

		$headers = array();
		foreach( $serverArgs as $key => $value )
		{
			if( strpos( $key, 'HTTP_' ) !== 0 )
				continue;

			$header = str_replace( ' ', '-', ucwords( str_replace( '_', ' ', strtolower( substr( $key, 5 ) ) ) ) );

			if( $bNorm )
			{
				if( $header == 'Accept-Language' )
					$value = Net::RequestHeader_Norm_AcceptLanguage( $value );
			}

			if( $bAssoc )
				$headers[ $header ] = $value;
			else
				$headers[] = $header . ': ' . $value;
		}

		return( $headers );
	}

	static function RequestHeader_Norm_AcceptLanguage( $v )
	{
		$aRes = array();

		foreach( explode( ',', $v ) as $vI )
			if( @preg_match( '@^([\\w\\-*]+)(?:\\s*;\\s*q\\s*=\\s*([\\d\\.]*))?@', trim( $vI ), $m ) )
				$aRes[] = array( $m[ 1 ], count( $m ) > 2 ? ( float )$m[ 2 ] : 1.0 );

		usort( $aRes, function( $a, $b ) { return( $a[ 1 ] < $b[ 1 ] ? 1 : ( $a[ 1 ] > $b[ 1 ] ? -1 : 0 ) ); } );
		return( implode( ',', array_map( function( $v ) { return( implode( ';q=', $v ) ); }, $aRes ) ) );
	}

	static function GetRequestIp( $serverArgs = null )
	{
		if( $serverArgs === null )
			$serverArgs = $_SERVER;

		if( isset( $serverArgs[ 'HTTP_X_REAL_IP' ] ) )
			return( Gen::SanitizeTextData( stripslashes( ( string )$serverArgs[ 'HTTP_X_REAL_IP' ] ) ) );

		if( isset( $serverArgs[ 'HTTP_X_FORWARDED_FOR' ] ) )
			return( trim( current( preg_split( '/,/', Gen::SanitizeTextData( stripslashes( ( string )$serverArgs[ 'HTTP_X_FORWARDED_FOR' ] ) ) ) ) ) );

		if( isset( $serverArgs[ 'REMOTE_ADDR' ] ) )
			return( Gen::SanitizeTextData( stripslashes( ( string )$serverArgs[ 'REMOTE_ADDR' ] ) ) );

		return( '' );
	}

	static function RemoveHeader( &$headers, $key )
	{
		unset( $headers[ $key ] );
		unset( $headers[ strtolower( $key ) ] );
	}

	static function UrlParseQuery( $query )
	{
		$args = array();
		@parse_str( $query, $args );
		return( $args );
	}

	static function UrlBuildQuery( $args )
	{
		if( !$args )
			return( '' );
		$res = http_build_query( $args, '', '&', PHP_QUERY_RFC3986 );
		$res = rtrim( $res, '=' );
		$res = str_replace( '=&', '&', $res );
		return( $res );
	}

	static function UrlExtractArgs( &$url )
	{
		$pos = strpos( $url, '?' );
		if( $pos === false )
			return( array() );

		$args = Net::UrlParseQuery( substr( $url, $pos + 1 ) );
		$url = substr( $url, 0, $pos );
		return( $args );
	}

	static function UrlAddArgs( $url, $args )
	{
		$args = Net::UrlBuildQuery( $args );
		if( $args )
			$url = $url . '?' . $args;
		return( $url );
	}

	const URLPARSE_F_QUERY					= 1;
	const URLPARSE_F_PATH_FIXFIRSTSLASH		= 2;
	const URLPARSE_F_PRESERVEEMPTIES		= 4;

	static function UrlParse( $url, $flags = 0 )
	{
		if( !$url )
			return( null );

		$url = preg_replace_callback( '%[^:/@?&=#]+%usD', function( $m ) { return( urlencode( $m[ 0 ] ) ); }, $url );

		if( (isset($url[ 0 ])?$url[ 0 ]:null) === ':' && (isset($url[ 1 ])?$url[ 1 ]:null) === '/' )
			$url = substr( $url, 1 );

		$urlComps = @parse_url( $url );
		if( !$urlComps )
			return( false );

		foreach( $urlComps as $k => &$v )
			if( is_string( $v ) )
				$v = urldecode( $v );
		unset( $k, $v );

		if( $flags & Net::URLPARSE_F_QUERY )
			$urlComps[ 'query' ] = Net::UrlParseQuery( isset( $urlComps[ 'query' ] ) ? $urlComps[ 'query' ] : null );

		if( ( $flags & Net::URLPARSE_F_PATH_FIXFIRSTSLASH ) && isset( $urlComps[ 'path' ] ) && strlen( $urlComps[ 'path' ] ) > 1 && $urlComps[ 'path' ][ 0 ] == '/' && $urlComps[ 'path' ][ 1 ] == '/' )
			$urlComps[ 'path' ] = '/' . ltrim( $urlComps[ 'path' ], '/' );

		if( $flags & Net::URLPARSE_F_PRESERVEEMPTIES )
		{
			if( !isset( $urlComps[ 'path' ] ) )
				$urlComps[ 'path' ] = '';
			if( !isset( $urlComps[ 'query' ] ) && strpos( $url, '?' ) !== false )
				$urlComps[ 'query' ] = '';
			if( !isset( $urlComps[ 'fragment' ] ) && strpos( $url, '#' ) !== false )
				$urlComps[ 'fragment' ] = '';
		}

		return( $urlComps );
	}

	static private function _UrlDeParse( $res, $resToStrCb, &$metas, array &$urlComps, $flags, array &$exclComps, array &$inclComps )
	{
		if( !$metas )
		{
			if( is_string( $res ) )
				return( $res );
			return( $resToStrCb ? @call_user_func( $resToStrCb, $res ) : ( string )$res );
		}

		$res = '';

		foreach( $metas as $meta )
		{
			$v = isset( $urlComps[ $meta[ 1 ] ] ) ? $urlComps[ $meta[ 1 ] ] : null;

			if( !$v )
			{
				if( $flags & Net::URLPARSE_F_PRESERVEEMPTIES )
				{
					if( $v === null )
						continue;
				}
				else if( $v !== '0' && $v !== 0 )
					continue;
			}

			if( !in_array( $meta[ 0 ], $exclComps ) && ( !$inclComps || in_array( $meta[ 0 ], $inclComps ) ) )
				$res .= $meta[ 2 ][ 0 ] . self::_UrlDeParse( $v, $meta[ 2 ][ 1 ], $meta[ 3 ], $urlComps, $flags, $exclComps, $inclComps ) . $meta[ 2 ][ 2 ];
		}

		return( $res );
	}

	static function UrlDeParse( $urlComps, $flags = 0, $exclComps = array(  ), $inclComps = array() )
	{
		if( !is_array( $urlComps ) )
			return( false );

		$metas = array(
			array( PHP_URL_SCHEME,		'scheme',	array( '', null, ':' ), array()	),
			array( PHP_URL_HOST,		'host',		array( '//', null, '' ), array(
				array( PHP_URL_USER,		'user',		array( '', null, '@' ), array(
					array( PHP_URL_USER,		'user',		array( '',	null, '' ), array()	),
					array( PHP_URL_PASS,		'pass',		array( ':',	null, '' ), array()	),
				) ),
				array( PHP_URL_HOST,		'host',		array( '',	null, '' ), array()	),
				array( PHP_URL_PORT,		'port',		array( ':',	null, '' ), array()	),
			) ),
			array( PHP_URL_PATH,		'path',		array( '',	null, '' ), array()	),
			array( PHP_URL_QUERY,		'query',	array( '?',	'seraph_accel\\Net::UrlBuildQuery', '' ), array()	),
			array( PHP_URL_FRAGMENT,	'fragment',	array( '#',	null, '' ), array()	),
		);

		return( self::_UrlDeParse( '', null, $metas, $urlComps, $flags, $exclComps, $inclComps ) );
	}

	static function SetCookie( $name, $value = '', $options = array() )
	{
		if( version_compare( PHP_VERSION, '7.3.0' ) >= 0 )
			return( setcookie( $name, $value, $options ) );

		return( setcookie( $name, $value,
			Gen::GetArrField( $options, array( 'expires' ), 0 ),
			Gen::GetArrField( $options, array( 'path' ), '' ),
			Gen::GetArrField( $options, array( 'domain' ), '' ),
			Gen::GetArrField( $options, array( 'secure' ), false ),
			Gen::GetArrField( $options, array( 'httponly' ), false )
		) );
	}

	static function GetQueryObjArg( $v )
	{

		return( @json_decode( @base64_decode( $v ), true ) );
	}

	static function CurRequestRemoveArgs( &$args, array $aArgRemove )
	{
		$requestUri = &$_SERVER[ 'REQUEST_URI' ];
		$requestUriArgs = Net::UrlExtractArgs( $requestUri );

		$redirect_query_string_args = Net::UrlParseQuery( (isset($_SERVER[ 'REDIRECT_QUERY_STRING' ])?$_SERVER[ 'REDIRECT_QUERY_STRING' ]:'') );
		$query_string_args = Net::UrlParseQuery( (isset($_SERVER[ 'QUERY_STRING' ])?$_SERVER[ 'QUERY_STRING' ]:'') );

		foreach( $aArgRemove as $argRemove )
		{
			unset( $args[ $argRemove ] );
			unset( $_GET[ $argRemove ] );
			unset( $_REQUEST[ $argRemove ] );
			unset( $requestUriArgs[ $argRemove ] );
			unset( $redirect_query_string_args[ $argRemove ] );
			unset( $query_string_args[ $argRemove ] );
		}

		$requestUri = Net::UrlAddArgs( $requestUri, $requestUriArgs );

		$_SERVER[ 'REDIRECT_QUERY_STRING' ] = Net::UrlBuildQuery( $redirect_query_string_args );
		$_SERVER[ 'QUERY_STRING' ] = Net::UrlBuildQuery( $query_string_args );
	}
}

class HtmlNd
{
	static function LoadXML( $str, $options = 0 )
	{
		if( !$str )
			return( null );

		$docTmp = new \DOMDocument();
		try
		{
			if( !@$docTmp -> loadXML( ( string )$str, $options ) )
				return( null );
		}
		catch( \Exception $e )
		{
			return( null );
		}

		return( $docTmp -> firstChild );
	}

	static function Parse( $str, $options = null, $encoding = 'UTF-8' )
	{
		if( $options === null )
			$options = LIBXML_NONET | LIBXML_NOBLANKS;

		if( empty( $str ) )
			return( null );

		if( $options & LIBXML_NOBLANKS )
		{
			$str = str_replace( "\r", '', $str );
			$str = str_replace( "\t", '', $str );
		}

		$doc = new \DOMDocument();
		if( !@$doc -> loadHTML( '<!DOCTYPE html><html><head><meta charset="' . $encoding . '"></head><body>' . $str . '</body></html>', $options | LIBXML_HTML_NOIMPLIED ) )
			return( null );

		$nd = HtmlNd::FindByTag( $doc, 'body' );

		if( $options & LIBXML_NOBLANKS )
			HtmlNd::CleanEmptyChildren( $nd );

		return( $nd );
	}

	static function ParseAndImportAll( $doc, $cont, $options = null, $encoding = 'UTF-8' )
	{
		$nd = HtmlNd::Parse( $cont, $options, $encoding );
		if( !$nd || !$nd -> firstChild )
			return( array() );
		$res = array();
		foreach( $nd -> childNodes as $ndChild )
			$res[] = $doc -> importNode( $ndChild, true );
		return( $res );
	}

	static function ParseAndImport( $doc, $cont, $options = null, $encoding = 'UTF-8' )
	{
		$nd = HtmlNd::Parse( $cont, $options, $encoding );
		if( !$nd || !$nd -> firstChild )
			return( null );
		return( $doc -> importNode( $nd -> firstChild, true ) );
	}

	static function IsNodeEmpty( $nd )
	{
		if( $nd -> nodeType != XML_TEXT_NODE )
			return( false );
		if( trim( $nd -> textContent ) != '' )
			return( false );

		$parent = $nd -> parentNode;
		if( !$parent )
			return( true );

		switch( $parent -> nodeName )
		{
		case 'strong':
		case 'em':
		case 'span':
			return( false );
		}

		return( true );
	}

	static function IsNodeTag( $nd, $tag )
	{
		return( $nd -> nodeType == XML_ELEMENT_NODE && $nd -> nodeName == $tag );
	}

	static function CleanEmptyChildren( $nd )
	{
		HtmlNd::CleanChildren( $nd, function( $nd, $data ) { return( HtmlNd::IsNodeEmpty( $nd ) ); } );
	}

	static function CleanChildren( $nd, $func = null, $data = null )
	{
		$children = $nd -> childNodes;
		if( !$children )
			return;

		for( $i = 0; $i < $children -> length; $i++ )
		{
			$child = $children -> item( $i );
			HtmlNd::CleanChildren( $child, $func, $data );

			if( !$func || $func( $child, $data ) )
			{
				$nd -> removeChild( $child );
				$i --;
			}
		}
	}

	static function DeParse( $nd, $includeSelf = true )
	{
		if( !$nd || !$nd -> ownerDocument )
			return( null );

		if( $nd -> nodeName == 'body' )
			$includeSelf = false;

		if( $includeSelf )
			return( $nd -> ownerDocument -> saveHTML( $nd ) );

		$children = $nd -> childNodes;
		if( !$children )
			return( '' );

		$res = '';
		for( $i = 0; $i < $children -> length; $i++ )
		{
			$child = $children -> item( $i );
			$res .= $nd -> ownerDocument -> saveHTML( $child );
		}

		return( $res );
	}

	static function FindBy( $nd, $func, $data = null, $recurse = true )
	{
		if( !$nd )
			return( null );

		if( $func( $nd, $data ) )
			return( $nd );

		if( $recurse === null )
			return( null );

		$children = $nd -> childNodes;
		if( !$children )
			return( null );

		for( $i = 0; $i < $children -> length; $i++ )
		{
			$ndRes = self::FindBy( $children -> item( $i ), $func, $data, $recurse ? true : null );
			if( $ndRes )
				return( $ndRes );
		}

		return( null );
	}

	static function FindUpBy( $nd, $func, $data = null )
	{
		if( !$nd )
			return( null );

		if( $func( $nd, $data ) )
			return( $nd );

		$parent = $nd -> parentNode;
		if( !$parent )
			return( null );

		return( HtmlNd::FindUpBy( $parent, $func, $data ) );
	}

	static function FindByTag( $nd, $tag, $recurse = true )
	{
		return( HtmlNd::FindBy( $nd, function( $nd, $tag ) { return( HtmlNd::IsNodeTag( $nd, $tag ) ); }, $tag, $recurse ) );
	}

	static function FindUpByTag( $nd, $tag )
	{
		return( HtmlNd::FindUpBy( $nd, function( $nd, $tag ) { return( HtmlNd::IsNodeTag( $nd, $tag ) ); }, $tag ) );
	}

	static function ChildrenAsArr( $children )
	{
		$res = array();
		HtmlNd::ChildrenAddToArr( $res, $children );
		return( $res );
	}

	static function ChildrenAddToArr( array &$res, $children, $notExistedOnly = false )
	{
		if( !$children )
			return;

		for( $i = 0; $i < $children -> length; $i++ )
		{
			$item = $children -> item( $i );
			if( !$notExistedOnly || !in_array( $item, $res, true ) )
				$res[] = $item;
		}
	}

	static function FirstOfChildren( $children )
	{
		if( !$children || !$children -> length )
			return( null );
		return( $children -> item( 0 ) );
	}

	static function ChildrenIter( $children )
	{
		if( !$children )
			return( $children );

		if( $children -> length !== 1 )
			return( $children );

		$iterSub = $children -> item( 0 );
		if( $iterSub instanceof \Iterator || $iterSub instanceof \IteratorAggregate )
			return( $iterSub );
		return( $children );
	}

	static function GetNodesByTag( &$res, $nd, $tag )
	{
		if( !$nd )
			return;

		if( $nd -> nodeName == $tag )
			$res[] = $nd;

		$children = $nd -> childNodes;
		if( !$children )
			return;

		for( $i = 0; $i < $children -> length; $i++ )
			self::GetNodesByTag( $res, $children -> item( $i ), $tag );
	}

	static function GetChildrenCount( $nd )
	{
		if( !$nd )
			return( 0 );

		$children = $nd -> childNodes;
		if( !$children )
			return( 0 );

		return( $children -> length );
	}

	static function GetChild( $nd, $i )
	{
		if( !$nd )
			return( null );

		$children = $nd -> childNodes;
		if( !$children )
			return( null );

		if( $i >= $children -> length )
			return( null );

		return( $children -> item( $i ) );
	}

	static function DoesContain( $ndWhere, $nd )
	{
		while( $nd )
		{
			if( $nd === $ndWhere )
				return( true );
			$nd = $nd -> parentNode;
		}

		return( false );
	}

	static function GetNextTreeChild( $ndTopParent, $ndPrev, $includeTopParent = false )
	{
		if( !$ndPrev )
			return( $includeTopParent ? $ndTopParent : $ndTopParent -> firstChild );

		$nd = $ndPrev -> firstChild;
		if( $nd )
			return( $nd );

		return( HtmlNd::GetNextTreeSibling( $ndPrev, $ndTopParent ) );
	}

	static function GetNextTreeSibling( $ndPrev, $ndTopParent = null )
	{
		if( !$ndPrev )
			return( null );

		if( !is_array( $ndTopParent ) )
			$ndTopParent = array( $ndTopParent );

		while( $ndPrev )
		{
			$nd = $ndPrev -> nextSibling;
			if( $nd )
				return( $nd );

			$nd = $ndPrev -> parentNode;
			if( in_array( $nd, $ndTopParent, true ) )
				break;

			$ndPrev = $nd;
		}

		return( null );
	}

	static function RemoveChild( $nd, $i )
	{
		if( !$nd )
			return( null );

		$children = $nd -> childNodes;
		if( !$children )
			return( null );

		if( $i >= $children -> length )
			return( null );

		$child = $children -> item( $i );
		$nd -> removeChild( $child );
		return( $child );
	}

	static function Remove( $nd )
	{
		if( is_array( $nd ) )
		{
			foreach( $nd as $ndI )
				HtmlNd::Remove( $ndI );
			return;
		}

		if( !$nd || !$nd -> parentNode )
			return;

		$nd -> parentNode -> removeChild( $nd );
	}

	static function InsertChild( $nd, $i, $ndChild )
	{
		if( !$nd )
			return( false );

		$ndChildBefore = self::GetChild( $nd, $i );
		if( $ndChildBefore )
			$nd -> insertBefore( $ndChild, $ndChildBefore );
		else
			$nd -> appendChild( $ndChild );

		return( true );
	}

	static function InsertBefore( $nd, $ndChild, $ndChildBefore )
	{
		if( !$nd )
			return( false );

		if( is_array( $ndChild ) )
		{
			foreach( $ndChild as $ndChildI )
				$nd -> insertBefore( $ndChildI, $ndChildBefore );
		}
		else
			$nd -> insertBefore( $ndChild, $ndChildBefore );

		return( true );
	}

	static function InsertAfter( $nd, $ndChild, $ndChildAfter, $bFirstIfNoChildAfter = false )
	{
		if( !$nd || !$ndChild )
			return( false );

		$nd -> insertBefore( $ndChild, $ndChildAfter ? $ndChildAfter -> nextSibling : ( $bFirstIfNoChildAfter ? $nd -> firstChild : null ) );
		return( true );
	}

	static function GetAttrNode( $nd, $name )
	{
		if( !$nd || !$nd -> attributes )
			return( null );
		return( $nd -> attributes -> getNamedItem( $name ) );
	}

	static function GetAttrVal( $nd, $name )
	{
		$attr = HtmlNd::GetAttrNode( $nd, $name );
		return( $attr ? $attr -> nodeValue : null );
	}

	static function SetAttrVal( $nd, $name, $val )
	{
		if( !$nd )
			return( false );

		$nd -> setAttribute( $name, $val );
		return( true );
	}

	static function SetValFromContent( $nd, $cont )
	{
		if( !$nd )
			return( false );

		$nd -> nodeValue = htmlspecialchars( $cont );
		if( !$cont || $nd -> nodeValue )
			return( $nd -> nodeValue );

		$cont = htmlentities( $cont, ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE );
		$cont = str_replace( "\x00", '', $cont );

		$nd -> nodeValue = $cont;
		return( $nd -> nodeValue );
	}

	static function GetAttr( $nd, $name, $def = null )
	{
		$v = ( string )$nd -> getAttribute( $name );
		return( strlen( $v ) ? $v : $def );
	}

	static function GetAttrClass( $nd )
	{
		return( $nd ? Ui::ParseClassAttr( $nd -> getAttribute( 'class' ) ) : array() );
	}

	static function AddRemoveAttrClass( $nd, $valClasses, $valClassesRemove = '' )
	{
		if( !$nd )
			return( false );

		$val = HtmlNd::GetAttrClass( $nd );

		if( !is_array( $valClasses ) )
			$valClasses = explode( ' ', @trim( $valClasses ) );

		if( !is_array( $valClassesRemove ) )
			$valClassesRemove = explode( ' ', @trim( $valClassesRemove ) );

		foreach( $valClasses as $valClass )
			if( strlen( ( string )$valClass ) && !in_array( $valClass, $val ) )
				$val[] = $valClass;

		foreach( $valClassesRemove as $valClassRemove )
			if( ( $i = array_search( $valClassRemove, $val ) ) !== false )
				unset( $val[ $i ] );

		$val = implode( ' ', $val );
		if( strlen( $val ) )
			$nd -> setAttribute( 'class', $val );
		else
			$nd -> removeAttribute( 'class' );
		return( true );
	}

	static function RenameAttr( $nd, $name, $nameNew )
	{
		if( !$nd )
			return( false );

		$val = $nd -> getAttribute( $name );
		if( !$val && !$nd -> hasAttribute( $name ) )
			return( false );

		$nd -> removeAttribute( $name );
		$nd -> setAttribute( $nameNew, $val );
		return( true );
	}

	static function HasAttrs( $nd, $excl = null )
	{
		if( !$nd )
			return( false );

		$attrs = isset( $nd -> attributes ) ? $nd -> attributes : null;
		if( !$attrs )
			return( false );

		if( !$excl )
			return( !!$attrs -> length );

		for( $i = 0; $i < $attrs -> length; $i++ )
		{
			$attr = $attrs -> item( $i );
			if( !in_array( $attr -> nodeName, $excl ) )
				return( true );
		}

		return( false );
	}

	static function CopyAllAttrs( $nd, $ndTo, $excl = null )
	{
		if( !$nd || $nd -> nodeType != XML_ELEMENT_NODE )
			return( false );

		$attrs = $nd -> attributes;
		if( !$attrs )
			return( true );

		if( $attrs )
		{
			for( $i = 0; $i < $attrs -> length; $i++ )
			{
				$attr = $attrs -> item( $i );
				if( !$excl || !in_array( $attr -> nodeName, $excl ) )
					$ndTo -> setAttribute( $attr -> nodeName, $attr -> nodeValue );
			}
		}

		return( true );
	}

	static function ClearAllAttrs( $nd )
	{
		if( !$nd || $nd -> nodeType != XML_ELEMENT_NODE )
			return( false );

		$attrs = $nd -> attributes;
		if( !$attrs )
			return( true );

		for( $i = $attrs -> length; $i > 0; $i-- )
		{
			$attr = $attrs -> item( $i - 1 );
			$nd -> removeAttribute( $attr -> nodeName );
		}

		return( true );
	}

	static function GetText( $nd )
	{
		if( !$nd )
			return( '' );
		return( $nd -> nodeType == XML_TEXT_NODE ? $nd -> textContent : '' );
	}

	static function GetTag( $nd )
	{
		if( !$nd )
			return( '' );
		return( $nd -> nodeName );
	}

	static function SetTag( $nd, $name, $preserveAttrs = true )
	{
		if( !$nd )
			return( $nd );

		if( $nd -> nodeName == $name )
			return( $nd );

		$ndNew = $nd -> ownerDocument -> createElement( $name );
		if( $nd -> parentNode )
			$nd -> parentNode -> replaceChild( $ndNew, $nd );

		if( $preserveAttrs )
			self::CopyAllAttrs( $nd, $ndNew, is_array( $preserveAttrs ) ? $preserveAttrs : null );

		self::MoveChildren( $ndNew, $nd );
		return( $ndNew );
	}

	static function MoveChildren( $nd, $ndFrom, $ndChildBefore = null )
	{
		if( !$nd )
			return;

		$children = $ndFrom -> childNodes;
		if( !$children )
			return;

		for( ; $children -> length; )
		{
			$ndChild = $children -> item( 0 );
			$ndFrom -> removeChild( $ndChild );

			if( $ndChildBefore )
				$nd -> insertBefore( $ndChild, $ndChildBefore );
			else
				$nd -> appendChild( $ndChild );
		}
	}

	static function GetNextTypeSibling( $nd )
	{
		if( !$nd )
			return( null );

		$nodeType = $nd -> nodeType;
		while( $nd = $nd -> nextSibling )
			if( $nd -> nodeType === $nodeType )
				break;

		return( $nd );
	}

	static function GetOuterSize( $nd )
	{
		if( !$nd )
			return( 0 );

		$res = strlen( $nd -> textContent );
		if( $nd -> nodeType !== XML_ELEMENT_NODE )
			return( $res );

		$res += 2 * strlen( $nd -> nodeName ) + 5;
		foreach( $nd -> attributes as $attr )
			$res += 4 + strlen( $attr -> nodeName ) + strlen( $attr -> nodeValue );

		return( $res );
	}

	static function CreateTag( $doc, $tag, $attrs = null, $aChildren = null )
	{
		$nd = $doc -> createElement( $tag );

		if( $attrs )
			foreach( $attrs as $attr => $attrVal )
			{
				if( $attr === 'disabled' )
				{
					if( $attrVal !== true && $attrVal !== '' )
						continue;
					$attrVal = '';
				}

				if( is_array( $attrVal ) )
				{
					if( $attr == 'style' )
						$attrVal = Ui::GetStyleAttr( $attrVal );
					else
					{
						$res = '';
						$first = true;
						foreach( $attrVal as $attrValItem )
						{
							if( empty( $attrValItem ) )
								continue;

							if( !$first )
								$res .= ' ';
							$res .= $attrValItem;

							$first = false;
						}

						$attrVal = $res;
						unset( $res );
					}
				}

				if( $attrVal !== null )
					$nd -> setAttribute( $attr, $attrVal );
			}

		if( $aChildren )
			foreach( $aChildren as $child )
				$nd -> appendChild( $child );

		return( $nd );
	}

	static function GetNextElementSibling( $nd )
	{
		if( !$nd )
			return( null );

		for( ;; )
		{
			$nd = $nd -> nextSibling;
			if( !$nd )
				break;

			if( $nd -> nodeType == XML_ELEMENT_NODE )
				return( $nd );
		}

		return( null );
	}

	static function GetPreviousElementSibling( $nd )
	{
		if( !$nd )
			return( null );

		for( ;; )
		{
			$nd = $nd -> previousSibling;
			if( !$nd )
				break;

			if( $nd -> nodeType == XML_ELEMENT_NODE )
				return( $nd );
		}

		return( null );
	}

	static function GetFirstElement( $nd )
	{
		if( !$nd )
			return( null );

		$nd = $nd -> firstChild;
		if( !$nd )
			return( null );
		return( ( $nd -> nodeType == XML_ELEMENT_NODE ) ? $nd : HtmlNd::GetNextElementSibling( $nd ) );
	}

	static function GetLastElement( $nd )
	{
		if( !$nd )
			return( null );

		$nd = $nd -> lastChild;
		if( !$nd )
			return( null );
		return( ( $nd -> nodeType == XML_ELEMENT_NODE ) ? $nd : HtmlNd::GetPreviousElementSibling( $nd ) );
	}

	static function Dump( $nd )
	{
		if( !$nd )
			return( null );

		$children = isset( $nd -> childNodes ) ? $nd -> childNodes : null;
		$attrs = isset( $nd -> attributes ) ? $nd -> attributes : null;

		$res = array();

		switch( $nd -> nodeType )
		{
		case XML_DOCUMENT_TYPE_NODE:	$res[ 'type' ] = 'DOCUMENT_TYPE'; break;
		case XML_ELEMENT_NODE:			$res[ 'type' ] = 'ELEMENT'; break;
		case XML_COMMENT_NODE:			$res[ 'type' ] = 'COMMENT'; break;
		case XML_TEXT_NODE:				$res[ 'type' ] = 'TEXT'; break;
		case XML_CDATA_SECTION_NODE:	$res[ 'type' ] = 'CDATA_SECTION'; break;

		default:						$res[ 'type' ] = $nd -> nodeType; break;
		}

		if( $nd -> nodeName )
			$res[ 'name' ] = $nd -> nodeName;

		if( $attrs && $attrs -> length )
		{
			$res[ 'attrs' ] = array();
			for( $i = 0; $i < $attrs -> length; $i++ )
			{
				$attr = $attrs -> item( $i );
				$res[ 'attrs' ][] = array( 'name' => $attr -> nodeName, 'value' => $attr -> nodeValue );
			}
		}

		if( !$children || !$children -> length )
		{
			$res[ 'content' ] = $nd -> textContent;
			return( $res );
		}

		$res[ 'children' ] = array();
		for( $i = 0; $i < $children -> length; $i++ )
			$res[ 'children' ][] = self::Dump( $children -> item( $i ) );

		return( $res );
	}
}

if( defined( 'T_ELEMENT' ) )
	exit( -1 );

const T_ELEMENT			= 10001;

class Php
{
	const TI_ID					= 0;
	const TI_CONTENT			= 1;
	const TI_LINENUM			= 2;

	const T_OPEN_TAG_CONTENT	= '<?php';

	static function Token_GetIdName( $id )
	{
		if( $id == T_ELEMENT )
			return( 'T_ELEMENT' );
		return( token_name( $id ) );
	}

	static function Token_GetContent( $token, $id = null )
	{
		if( !$token )
			return( null );

		if( $id !== null && $token[ Php::TI_ID ] != $id )
			return( null );

		return( $token[ Php::TI_CONTENT ] );
	}

	static function Token_GetEncapsedStrVal( $str )
	{
		return( substr( $str, 1, -1 ) );
	}

	static function Token_IdMatch( $token, $id )
	{
		if( is_array( $id ) )
		{
			foreach( $id as $idItem )
				if( self::Token_IdMatch( $token, $idItem ) )
					return( true );

			return( false );
		}

		if( is_string( $id ) )
		{
			if( is_array( $token ) )
				return( false );

			if( $id != $token )
				return( false );

			return( true );
		}

		if( !is_array( $token ) )
			return( false );

		if( $token[ Php::TI_ID ] != $id )
			return( false );

		return( true );
	}

	static function Tokens_GetSpaceIds()
	{
		$ids = Php::Tokens_GetCommentIds();
		$ids[] = T_WHITESPACE;

		return( $ids );
	}

	static function Tokens_GetCommentIds()
	{
		$ids = array( T_COMMENT );
		if( defined( 'T_ML_COMMENT' ) )
			$ids[] = T_ML_COMMENT;
		if( defined( 'T_DOC_COMMENT' ) )
			$ids[] = T_DOC_COMMENT;

		return( $ids );
	}

	static function Tokens_Normalize( &$tokens, $preserveLineNums = false )
	{
		$tokenLineNum = 0;
		for( $i = 0; $i < count( $tokens ); $i++ )
		{
			$token = &$tokens[ $i ];

			if( !is_array( $token ) )
			{
				$tokenNew = array( Php::TI_ID => T_ELEMENT, Php::TI_CONTENT => $token );
				if( $preserveLineNums )
					$tokenNew[ Php::TI_LINENUM ] = $tokenLineNum;

				$tokens[ $i ] = $tokenNew;
			}
			else
			{
				$tokenLineNum = $token[ Php::TI_LINENUM ];
				if( !$preserveLineNums )
					unset( $token[ Php::TI_LINENUM ] );
			}

			if( $token[ Php::TI_ID ] == T_INLINE_HTML )
				continue;

			if( $token[ Php::TI_ID ] == T_WHITESPACE )
				continue;

			$tokenVal_Ls = '';
			$tokenVal = null;
			$tokenVal_Rs = '';
			{
				$tokenValT = ltrim( $token[ Php::TI_CONTENT ] );
				$tokenVal_Ls = substr( $token[ Php::TI_CONTENT ], 0, strlen( $token[ Php::TI_CONTENT ] ) - strlen( $tokenValT ) );
				$tokenVal = rtrim( $tokenValT );
				$tokenVal_Rs = substr( $tokenValT, strlen( $tokenVal ), strlen( $tokenValT ) );
			}

			$token[ Php::TI_CONTENT ] = $tokenVal;

			if( $tokenVal_Ls )
			{
				$tokenNew = array( Php::TI_ID => T_WHITESPACE, Php::TI_CONTENT => $tokenVal_Ls );
				if( $preserveLineNums )
					$tokenNew[ Php::TI_LINENUM ] = $tokenLineNum;

				Php::Tokens_Insert( $tokens, $i, array( $tokenNew ) );
				$i++;
			}

			if( $tokenVal_Rs )
			{
				$tokenNew = array( Php::TI_ID => T_WHITESPACE, Php::TI_CONTENT => $tokenVal_Rs );
				if( $preserveLineNums )
					$tokenNew[ Php::TI_LINENUM ] = $tokenLineNum;

				Php::Tokens_Insert( $tokens, $i + 1, array( $tokenNew ) );
				$i++;
			}
		}
	}

	static function Tokens_Find( &$tokens, $id, $content = null, $pos = 0, $length = 0 )
	{
		$n = count( $tokens );
		if( $length > 0 )
		{
			$nNew = $pos + $length;
			if( $nNew <= $n )
				$n = $nNew;
		}

		if( !is_array( $id ) )
			$id = array( 'i' => array( $id ) );

		if( $content !== null && !is_array( $content ) )
			$content = array( $content );

		for( ; $pos < $n; $pos++ )
		{
			$token = $tokens[ $pos ];

			{
				$match = true;
				$idsList = isset( $id[ 'e' ] ) ? $id[ 'e' ] : null;
				if( !$idsList )
				{
					$idsList = isset( $id[ 'i' ] ) ? $id[ 'i' ] : null;
					$match = false;
				}

				if( is_array( $idsList ) && Php::Token_IdMatch( $token, $idsList ) )
					$match = !$match;

				if( !$match )
					continue;
			}

			if( $content !== null )
			{
				$contentFound = false;
				foreach( $content as $contentItem )
				{
					if( $token[ Php::TI_CONTENT ] == $contentItem )
					{
						$contentFound = true;
						break;
					}
				}

				if( !$contentFound )
					continue;
			}

			return( $pos );
		}

		return( false );
	}

	static function Tokens_Insert( &$tokens, $pos, $a )
	{
		$n = count( $tokens );
		array_splice( $tokens, $pos > $n ? $n : $pos, 0, $a );
	}

	static function Tokens_GetFromContent( $str, $preserveLineNums = false )
	{
		$tokens = @token_get_all( $str );
		Php::Tokens_Normalize( $tokens, $preserveLineNums );
		return( $tokens );
	}

	static function Tokens_GetContent( $tokens )
	{
		$res = '';

		for( $i = 0, $n = count( $tokens ); $i < $n; $i++ )
		{
			$token = $tokens[ $i ];
			$res .= is_array( $token ) ? $token[ Php::TI_CONTENT ] : $token;
		}

		return( $res );
	}

	static function Tokens_CallArgs_GetSingleArg( $callArgs, $idx, &$argTokenPos = null )
	{
		$arg = isset( $callArgs[ $idx ] ) ? $callArgs[ $idx ] : null;
		if( $arg === null )
			return( null );

		return( count( $arg ) == 1 ? Gen::ArrGetByPos( $arg, 0, null, $argTokenPos ) : null );
	}

	static function Tokens_GetCallArgs( $tokens, &$pos, $preserveSpaces = false )
	{
		$spacesIds = Php::Tokens_GetSpaceIds();

		$pos = Php::Tokens_Find( $tokens, array( 'e' => $spacesIds ), null, $pos );
		if( $pos === false )
			return( false );

		if( $tokens[ $pos ] != array( Php::TI_ID => T_ELEMENT, Php::TI_CONTENT => '(' ) )
			return( false );

		$pos++;

		$res = array();

		$bracketsLevel = 1;
		$argIdx = 0;

		for( $n = count( $tokens ); $pos < $n; $pos++ )
		{
			$token = $tokens[ $pos ];

			if( $token == array( Php::TI_ID => T_ELEMENT, Php::TI_CONTENT => ')' ) )
			{
				$bracketsLevel--;
				if( $bracketsLevel == 0 )
					break;
			}

			if( $bracketsLevel == 1 && $token == array( Php::TI_ID => T_ELEMENT, Php::TI_CONTENT => ',' ) )
			{
				$argIdx++;
				continue;
			}

			if( $token == array( Php::TI_ID => T_ELEMENT, Php::TI_CONTENT => '(' ) )
				$bracketsLevel++;

			if( !$preserveSpaces && self::Token_IdMatch( $token, $spacesIds ) )
				continue;

			$res[ $argIdx ][ $pos ] = $token;
		}

		return( $res );
	}

	static function File_SetDefineVal( $file, $name, $val )
	{
		if( !file_exists( $file ) )
			return( Gen::E_NOT_FOUND );

		if( !is_writable( $file ) )
			return( Gen::E_ACCESS_DENIED );

		$fileContent = file_get_contents( $file );
		if( !$fileContent )
			return( Gen::E_ACCESS_DENIED );

		if( !Php::Content_SetDefineVal( $fileContent, $name, $val ) )
			return( Gen::S_FALSE );

		if( !is_integer( file_put_contents( $file, $fileContent, LOCK_EX ) ) )
			return( Gen::E_FAIL );

		return( Gen::S_OK );
	}

	static function Content_SetDefineVal( &$fileContent, $name, $val )
	{
		$tokens = Php::Tokens_GetFromContent( $fileContent );
		if( !Php::Tokens_SetDefineVal( $tokens, $name, $val ) )
			return( false );

		$fileContent = Php::Tokens_GetContent( $tokens );
		return( true );
	}

	static function Tokens_SetDefineVal( &$tokens, $name, $val )
	{

		$firstInsertPos = Php::Tokens_Find( $tokens, T_OPEN_TAG );
		if( $firstInsertPos === false )
		{
			$tokensInsert = array();
			$tokensInsert[] = array( Php::TI_ID => T_OPEN_TAG, Php::TI_CONTENT => Php::T_OPEN_TAG_CONTENT );
			$tokensInsert[] = array( Php::TI_ID => T_WHITESPACE, Php::TI_CONTENT => PHP_EOL . PHP_EOL );

			Php::Tokens_Insert( $tokens, count( $tokens ), $tokensInsert );

			$firstInsertPos = count( $tokens );
		}
		else
		{
			$firstInsertPos++;

			$firstInsertPos = Php::Tokens_Find( $tokens, array( 'e' => array( T_WHITESPACE ) ), null, $firstInsertPos );
			if( $firstInsertPos === false )
				$firstInsertPos = count( $tokens );
		}

		$defineValPos = false;
		for( $i = $firstInsertPos; ; )
		{
			$i = Php::Tokens_Find( $tokens, T_STRING, 'define', $i );
			if( $i === false )
				break;
			$i++;

			$callArgs = Php::Tokens_GetCallArgs( $tokens, $i );
			if( empty( $callArgs ) || count( $callArgs ) != 2 )
				continue;

			if( Php::Token_GetEncapsedStrVal( Php::Token_GetContent( Php::Tokens_CallArgs_GetSingleArg( $callArgs, 0 ), T_CONSTANT_ENCAPSED_STRING ) ) != $name )
				continue;

			Php::Tokens_CallArgs_GetSingleArg( $callArgs, 1, $defineValPos );
			break;
		}

		$changed = false;

		if( $defineValPos === false )
		{
			$tokensInsert = array();
			$tokensInsert[] = array( Php::TI_ID => T_STRING, Php::TI_CONTENT => 'define' );
			$tokensInsert[] = array( Php::TI_ID => T_ELEMENT, Php::TI_CONTENT => '(' );
			$tokensInsert[] = array( Php::TI_ID => T_CONSTANT_ENCAPSED_STRING, Php::TI_CONTENT => '\'' . $name . '\'' );
			$tokensInsert[] = array( Php::TI_ID => T_ELEMENT, Php::TI_CONTENT => ',' );
			$tokensInsert[] = array( Php::TI_ID => T_WHITESPACE, Php::TI_CONTENT => ' ' );

			{
				$defineValPos = count( $tokensInsert );
				$tokensInsert[] = array( Php::TI_ID => T_CONSTANT_ENCAPSED_STRING, Php::TI_CONTENT => '\'\'' );
			}

			$tokensInsert[] = array( Php::TI_ID => T_ELEMENT, Php::TI_CONTENT => ')' );
			$tokensInsert[] = array( Php::TI_ID => T_ELEMENT, Php::TI_CONTENT => ';' );
			$tokensInsert[] = array( Php::TI_ID => T_WHITESPACE, Php::TI_CONTENT => PHP_EOL . PHP_EOL );

			Php::Tokens_Insert( $tokens, $firstInsertPos, $tokensInsert );
			$defineValPos += $firstInsertPos;

			$changed = true;
		}

		{

			$tokenValNew = null;
			switch( gettype( $val ) )
			{
			case 'string':
				$token = $tokens[ $defineValPos ];

				$cQuote = null;
				if( $token[ Php::TI_ID ] == T_CONSTANT_ENCAPSED_STRING )
					$cQuote = substr( $token[ Php::TI_CONTENT ], 0, 1 );

				if( empty( $cQuote ) )
					$cQuote = '\'';

				$tokenValNew = array( Php::TI_ID => T_CONSTANT_ENCAPSED_STRING, Php::TI_CONTENT => $cQuote . $val . $cQuote );
				break;

			case 'boolean':
				$tokenValNew = array( Php::TI_ID => T_STRING, Php::TI_CONTENT => $val ? 'true' : 'false' );
				break;

			case 'integer':
				$tokenValNew = array( Php::TI_ID => T_LNUMBER, Php::TI_CONTENT => '' . $val );
				break;

			case 'double':
			    $tokenValNew = array( Php::TI_ID => T_DNUMBER, Php::TI_CONTENT => '' . $val );
			    break;

			default:
				return( false );
				break;
			}

			if( $tokens[ $defineValPos ] != $tokenValNew )
			{
				$tokens[ $defineValPos ] = $tokenValNew;
				$changed = true;
			}
		}

		return( true );
	}
}

class WpFakePostContainer
{
	public function __construct( $post )
	{
		$this -> post = $post;
		wp_cache_add( $this -> post -> ID, $post, 'posts' );
	}

	function __destruct()
	{
		wp_cache_delete( $this -> post -> ID, 'posts' );
	}

	public $post;
}

class Wp
{
	static function GetKsesSanitizeCtx( $context = '' )
	{
		if( is_array( $context ) )
			return( $context );

		static $g_aScope = array();

		if( !isset( $g_aScope[ $context ] ) )
		{
			switch( $context )
			{
			case 'admin':
				$g_aScope[ $context ] = array_replace_recursive( wp_kses_allowed_html( 'post' ),
					array(
						'input' => array(
							'type' => true,
							'value' => true,
							'onclick' => true,
							'class' => true,
							'style' => true,
						),

						'div' => array(
						    'class' => true,
						    'style' => true,
						    'id' => true,
							'data-*' => true,
						),
					)
				);

				add_filter( 'safe_style_css', function( $aRule ) { return( array_merge( $aRule, array( 'display' ) ) ); }, 10 );
				break;

			case 'script':
				$g_aScope[ $context ] = array_replace_recursive( array(),
					array(
						'script' => array(
							'type' => true,
						),
					)
				);
				break;
			}
		}

		return( (isset($g_aScope[ $context ])?$g_aScope[ $context ]:null) );
	}

	static function SanitizeId( $id )
	{
		return( Gen::SanitizeId( sanitize_text_field( $id ) ) );

	}

	static function SanitizeTextData( $data )
	{
		return( Gen::SanitizeTextData( sanitize_text_field( $data ) ) );
	}

	static function SanitizeText( $text )
	{
		if( gettype( $text ) != 'string' )
			return( '' );
		return( sanitize_text_field( $text ) );
	}

	static function SanitizeMultilineText( $text )
	{
		if( gettype( $text ) != 'string' )
			return( '' );
		return( sanitize_textarea_field( $text ) );
	}

	static function SanitizeHtml( $html )
	{
		if( gettype( $html ) != 'string' )
			return( '' );
		return( wp_kses_post( $html ) );
	}

	static function SanitizeCss( $data )
	{
		if( gettype( $data ) != 'string' )
			return( '' );
		return( html_entity_decode( str_replace( array( '<style>', '</style>' ), array( '', '' ), wp_kses_post( '<style>' . htmlspecialchars( $data ) . '</style>' ) ) ) );
	}

	static function SanitizeXPath( $query )
	{
		if( gettype( $query ) != 'string' )
			return( '' );
		return( str_replace( array( '{{{LT}}}', '{{{GT}}}' ), array( '<', '>' ), sanitize_text_field( str_replace( array( '<', '>' ), array( '{{{LT}}}', '{{{GT}}}' ), $query ) ) ) );
	}

	static function SanitizeUrl( $url )
	{
		if( gettype( $url ) != 'string' )
			return( '' );
		return( esc_url_raw( $url ) );
	}

	static function safe_html_x( $text, $context, $domain )
	{
		return( Wp::SanitizeHtml( _x( $text, $context, $domain ) ) );
	}

	static function GetSiteRootUrl( $path = '', $base = true )
	{
		if( !$base )
			return( home_url( $path ) );

		$obj = new AnyObj();
		$obj -> vOpt = '';
		$obj -> url = '';
		$obj -> cb1 = function( $obj, $vOpt ) { return( $obj -> vOpt = $vOpt ); };
		$obj -> cb2 = function( $obj, $vOpt ) { return( $obj -> vOpt ); };
		$obj -> cb = function( $obj, $url ) { return( $obj -> url = $url ); };

		if( $base === 'base' )
		{
			add_filter( 'option_home', array( $obj, 'cb1' ), -99999, 1 );
			add_filter( 'option_home', array( $obj, 'cb2' ), 99999, 1 );
		}

		add_filter( 'home_url', array( $obj, 'cb' ), -99999, 1 );
		home_url( $path );
		remove_filter( 'home_url', array( $obj, 'cb' ), -99999 );

		if( $base === 'base' )
		{
			remove_filter( 'option_home', array( $obj, 'cb2' ), 99999 );
			remove_filter( 'option_home', array( $obj, 'cb1' ), -99999 );
		}

		return( $obj -> url );
	}

	static function GetSiteWpRootUrl( $path = '', $blog_id = null, $base = false )
	{

		if( !$base )
			return( get_site_url( $blog_id, $path ) );

		$obj = new AnyObj();
		$obj -> vOpt = '';
		$obj -> url = '';
		$obj -> cb1 = function( $obj, $vOpt ) { return( $obj -> vOpt = $vOpt ); };
		$obj -> cb2 = function( $obj, $vOpt ) { return( $obj -> vOpt ); };
		$obj -> cb = function( $obj, $url ) { return( $obj -> url = $url ); };

		add_filter( 'option_siteurl', array( $obj, 'cb1' ), -99999, 1 );
		add_filter( 'option_siteurl', array( $obj, 'cb2' ), 99999, 1 );

		add_filter( 'site_url', array( $obj, 'cb' ), -99999, 1 );
		get_site_url( $blog_id, $path );
		remove_filter( 'site_url', array( $obj, 'cb' ), -99999 );

		remove_filter( 'option_siteurl', array( $obj, 'cb2' ), 99999 );
		remove_filter( 'option_siteurl', array( $obj, 'cb1' ), -99999 );

		return( $obj -> url );
	}

	static function GetSiteId( $mode = null )
	{
		if( $mode === 'OLD' )
			$siteUrl = Wp::GetSiteWpRootUrl();
		else
			$siteUrl = Wp::GetSiteRootUrl( '', 'base' );

		$siteUrlParts = @parse_url( $siteUrl );
		if( !is_array( $siteUrlParts ) )
			return( '' );

		$res = $siteUrlParts[ 'host' ];

		if( isset( $siteUrlParts[ 'port' ] ) )
			$res .= '_' . $siteUrlParts[ 'port' ];

		if( isset( $siteUrlParts[ 'path' ] ) )
			$res .= '_' . str_replace( array( '/', '\\' ), '_', $siteUrlParts[ 'path' ] );

		return( md5( $res ) );
	}

	static function GetSiteDisplayName()
	{
		$siteUrlParts = @parse_url( Wp::GetSiteRootUrl() );
		if( !is_array( $siteUrlParts ) )
			return( '' );

		$res = $siteUrlParts[ 'host' ];

		if( isset( $siteUrlParts[ 'port' ] ) )
			$res .= ':' . $siteUrlParts[ 'port' ];

		if( isset( $siteUrlParts[ 'path' ] ) )
			$res .= $siteUrlParts[ 'path' ];

		return( $res );
	}

	static function GetOptionsUrl( $page = null )
	{
		$url = admin_url( 'options-general.php' );
		if( $page )
			$url = add_query_arg( array( 'page' => $page ), $url );
		return( $url );
	}

	static function GetTempDir()
	{
		if( defined( 'WP_TEMP_DIR' ) )
		{

			$dir = trailingslashit( WP_TEMP_DIR );
			if( @is_dir( $dir ) && wp_is_writable( $dir ) )
				return( $dir );
			return( Gen::GetTempDirEx() );
		}

		return( get_temp_dir() );
	}

	static function GetTempFile( &$dirTmp = null )
	{
		if( $dirTmp === null )
			$dirTmp = Wp::GetTempDir();
		return( @tempnam( $dirTmp, substr( 'accel', 0, 3 ) ) );
	}

	static function GetConfigFilePath()
	{

		$dir = ABSPATH;
		$path = $dir . 'wp-config.php';
		if( @file_exists( $path ) )
			return( $path );

		$dir = dirname( ABSPATH ) . '/';
		$path = $dir . 'wp-config.php';
		if( @file_exists( $path ) && !@file_exists( $dir . 'wp-settings.php' ) )
			return( $path );

		return( null );
	}

	static private function _RemoteGet_Ctx( &$url, &$args )
	{
		if( $args === null )
			$args = array();

		if( (isset($args[ 'local' ])?$args[ 'local' ]:null) )
		{
			$aUrl = Net::UrlParse( $url, Net::URLPARSE_F_PRESERVEEMPTIES );
			if( $aUrl )
			{
				$args[ 'sslverify' ] = false;
				$args[ 'headers' ][ 'Host' ] = $aUrl[ 'host' ];
				$aUrl[ 'host' ] = '127.0.0.1';

				$url = Net::UrlDeParse( $aUrl );
			}
		}

		$obj = new AnyObj();

		$obj -> _cbRequestBefore =
			function( $obj, $url, $p1, $p2, $p3, &$options )
			{
				if( $options && isset( $options[ 'timeout' ] ) && $options[ 'timeout' ] )
					$options[ 'connect_timeout' ] = $options[ 'timeout' ] - 1;
			};

		$obj -> _cbRequestsBeforeParse =
			function( $obj, &$response, $url, $headers, $data, $type, $options )
			{
				$obj -> method = $type;

				$obj -> headers_sent = ( array )$headers;
				if( isset( $options[ 'useragent' ] ) )
					$obj -> headers_sent[ 'User-Agent' ] = $options[ 'useragent' ];
			};

		$obj -> setHooks =
			function( $obj, $enable )
			{
				if( $enable )
				{
					add_action( 'requests-requests.before_request', array( $obj, '_cbRequestBefore' ), 10, 5 );
					add_action( 'requests-requests.before_parse', array( $obj, '_cbRequestsBeforeParse' ), 10, 6 );
				}
				else
				{
					remove_action( 'requests-requests.before_parse', array( $obj, '_cbRequestsBeforeParse' ), 10 );
					remove_action( 'requests-requests.before_request', array( $obj, '_cbRequestBefore' ), 10 );
				}
			};

		$obj -> adjustRes =
			function( $obj, $url, $res )
			{
				if( !$res )
					return( $res );

				if( is_wp_error( $res ) )
				{
					$res -> add_data( $url, 'url' );
					return( $res );
				}

				$res[ 'url' ] = $url;
				if( isset( $obj -> method ) )
					$res[ 'method' ] = $obj -> method;
				if( isset( $obj -> headers_sent ) )
					$res[ 'headers_sent' ] = $obj -> headers_sent;

				return( $res );
			};

		return( $obj );
	}

	static function RemoteGet( $url, $args = null )
	{
		$obj = self::_RemoteGet_Ctx( $url, $args );

		$obj -> setHooks( true );
		$res = wp_remote_get( $url, $args );
		$obj -> setHooks( false );

		return( $obj -> adjustRes( $url, $res ) );
	}

	static function RemotePost( $url, $args = null )
	{
		$obj = self::_RemoteGet_Ctx( $url, $args );

		$obj -> setHooks( true );
		$res = wp_remote_post( $url, $args );
		$obj -> setHooks( false );

		return( $obj -> adjustRes( $url, $res ) );
	}

	static function RemoteRequest( $method, $url, $args = null )
	{
		if( $method === 'GET' )
			return( Wp::RemoteGet( $url, $args ) );
		if( $method === 'POST' )
			return( Wp::RemotePost( $url, $args ) );

		$obj = self::_RemoteGet_Ctx( $url, $args );

		$args[ 'method' ] = $method;

		$obj -> setHooks( true );
		$res = wp_remote_request( $url, $args );
		$obj -> setHooks( false );

		return( $obj -> adjustRes( $url, $res ) );
	}

	static function GetAbsPathFromUri( $uri )
	{
		return( Gen::SetLastSlash( ABSPATH ) . Gen::SetFirstSlash( $uri, false ) );
	}

	static function GetLocString( $id, $number = null, $domain = 'default' )
	{
		$ctx = null;
		if( is_array( $id ) )
		{
			$ctx = $id[ 1 ];
			$id = $id[ 0 ];
		}

		if( $number !== null )
			return( $ctx ? _nx( $id, $id, $number, $ctx, $domain ) : _n( $id, $id, $number, $domain ) );
		return( $ctx ? _x( $id, $ctx, $domain ) : __( $id, $domain ) );
	}

	static function GetPostIdBySlug( $slug, $post_type = 'post', $status = 'publish', $lang = null )
	{
		$posts = get_posts( array( 'name' => $slug, 'post_type' => $post_type, 'post_status' => $status ? $status : 'any', 'numberposts' => 1 ) );
		if( empty( $posts ) )
			return( null );
		return( apply_filters( 'translate_object_id', $posts[ 0 ] -> ID, $post_type, true, $lang ) );
	}

	static function GetPostIdByPath( $path, $post_type = 'post', $lang = null )
	{
		$post = get_page_by_path( $path, OBJECT, $post_type );
		if( !$post )
			return( null );
		return( apply_filters( 'translate_object_id', $post -> ID, $post -> post_type, true, $lang ) );
	}

	static function GetPostPreviewLink( $post = null, $query_args = null, $preview_link = null )
	{
		if( !is_array( $query_args ) )
			$query_args = array();
		if( !is_string( $preview_link ) )
			$preview_link = '';

		if( Gen::DoesFuncExist( 'get_preview_post_link' ) )
			return( get_preview_post_link( $post, $query_args, $preview_link ) );

		$post = get_post( $post );
		if( !$post )
			return( null );

		$post_type_object = get_post_type_object( $post -> post_type );
		if( is_post_type_viewable( $post_type_object ) )
		{
			if( empty( $preview_link ) )
				$preview_link = set_url_scheme( get_permalink( $post ) );

			$query_args[ 'preview' ] = 'true';
			$preview_link = add_query_arg( $query_args, $preview_link );
		}

		return( apply_filters( 'preview_post_link', $preview_link, $post ) );
	}

	static function GetTransientOptionId( $transient )
	{
		return( '_transient_' . $transient );
	}

	static function CreateFakePostContainer( $postType = 'post' )
	{
		$post = new \WP_Post( ( object )array( 'ID' => 0x7FFFFFFF ) );
		$post -> post_type = $postType;
		$post -> post_status = 'auto-draft';
		$post -> post_title = '';

		return( new WpFakePostContainer( $post ) );
	}

	private static function _GetAvailableTaxonomyTerms( $idTaxonomy )
	{
		$terms = get_terms( $idTaxonomy, array( 'hide_empty' => false ) );
		if( empty( $terms ) || is_wp_error( $terms ) )
			return( null );

		$termsNew = array();
		foreach( $terms as $postCat )
		{
			$postCatNew = array( 'slug' => $postCat -> slug, 'name' => $postCat -> name, 'descr' => $postCat -> description, 'parent' => $postCat -> parent );
			$termsNew[ $postCat -> term_id ] = $postCatNew;
		}

		return( $termsNew );
	}

	static function GetAvailableTaxonomyTerms( $idTaxonomy )
	{
		$filters = Wp::RemoveLangFilters();
		$terms = self::_GetAvailableTaxonomyTerms( $idTaxonomy );
		Wp::AddFilters( $filters );

		if( $terms && Wp::IsLangsActive() )
			foreach( $terms as $id => &$info )
				$info[ 'lang' ] = Wp::GetElemLang( $id, $idTaxonomy );

		return( $terms );
	}

	static private function _GetPostsTaxonomiesByClass_Filter( $taxonomy, $filters, $filterPostType, $filterHasRewriteSlug )
	{

		if( $filterPostType && !in_array( $filterPostType, Gen::GetArrField( $taxonomy, array( 'object_type' ), array() ) ) )
			return( false );
		if( $filterHasRewriteSlug && !Gen::GetArrField( $taxonomy, array( 'rewrite', 'slug' ) ) )
			return( false );

		foreach( $filters as $filterId => $filterVal )
			if( ( isset( $taxonomy -> { $filterId } ) ? $taxonomy -> { $filterId } : null ) != $filterVal )
				return( false );

		return( true );
	}

	const POST_TAXONOMY_CLASS_CATEGORIES					= 'categories';
	const POST_TAXONOMY_CLASS_TAGS							= 'tags';

	static function GetPostsTaxonomiesByClass( $classId, array $filters = array( 'show_ui' => true ) )
	{
		$taxonomies = get_taxonomies( NULL, 'objects' );
		if( !is_array( $taxonomies ) )
			return( array() );

		$res = array();

		if( $filterPostType = isset( $filters[ 'postType' ] ) ? $filters[ 'postType' ] : null )
			unset( $filters[ 'postType' ] );
		if( $filterHasRewriteSlug = isset( $filters[ 'hasRewriteSlug' ] ) ? $filters[ 'hasRewriteSlug' ] : null )
			unset( $filters[ 'hasRewriteSlug' ] );

		$taxonomyMetaCbName = 'post_' . $classId . '_meta_box';

		foreach( $taxonomies as $taxonomyId => $taxonomy )
		{
			if( $taxonomy -> meta_box_cb != $taxonomyMetaCbName || !self::_GetPostsTaxonomiesByClass_Filter( $taxonomy, $filters, $filterPostType, $filterHasRewriteSlug ) )
				continue;

			foreach( Gen::GetArrField( $taxonomy -> object_type, array( '' ), array() ) as $taxonomyPostType )
				$res[ $taxonomyPostType ][] = $taxonomyId;
		}

		return( $res );
	}

	static function GetPostsAvailableTaxonomies( $type, $postTypes = NULL )
	{
		$mapPostTypeToTaxonomy = Wp::GetPostsTaxonomiesByClass( $type );

		$cats = array();

		if( empty( $postTypes ) )
			$postTypes = get_post_types();

		$filters = Wp::RemoveLangFilters();

		foreach( $postTypes as $postType )
		{
			$postTaxonomy = Gen::GetArrField( $mapPostTypeToTaxonomy, array( $postType, 0 ) );
			if( empty( $postTaxonomy ) )
				continue;

			$postCatsNew = self::_GetAvailableTaxonomyTerms( $postTaxonomy );
			if( $postCatsNew !== null )
				$cats[ $postType ] = $postCatsNew;
		}

		Wp::AddFilters( $filters );

		if( Wp::IsLangsActive() )
			foreach( $cats as $postType => &$type )
			{
				$postTaxonomy = Gen::GetArrField( $mapPostTypeToTaxonomy, array( $postType, 0 ) );
				foreach( $type as $id => &$info )
					$info[ 'lang' ] = Wp::GetElemLang( $id, $postTaxonomy );
			}

		return( $cats );
	}

	static function UpdatePostTypeTaxonomies( $terms, $type, $postType, $lang = null )
	{
		$postTaxonomy = Gen::GetArrField( Wp::GetPostsTaxonomiesByClass( $type ), array( $postType, 0 ) );
		if( !$postTaxonomy )
			return( null );

		$res = array();

		$filters = Wp::RemoveLangFilters();

		$langDef = Wp::GetDefLang();

		foreach( $terms as $term )
		{
			$term = trim( $term );
			if( !strlen( $term ) )
				continue;

			$termInfo = null;
			{
				$termInsRes = wp_insert_term( $term, $postTaxonomy );
				if( is_wp_error( $termInsRes ) )
				{
					$termIdExisted = intval( $termInsRes -> get_error_data( 'term_exists' ) );
					if( $termIdExisted > 0 )
					{
						if( $lang )
						{
							$termExistedLang = Wp::GetElemLang( $termIdExisted, $postTaxonomy );
							if( $termExistedLang != $lang )
							{
								if( $langDef != $termExistedLang )
								{
									$termInfo = get_term( $termIdExisted, $postTaxonomy, ARRAY_A );
									if( !is_wp_error( $termInfo ) )
										wp_update_term( $termIdExisted, $postTaxonomy, array( 'slug' => $termInfo[ 'slug' ] . ' ' . $termExistedLang ) );
								}

								$slug = $term . ( $langDef != $lang ? ( ' ' . $lang ) : '' );
								$termInfo = get_term_by( 'slug', $slug, $postTaxonomy, ARRAY_A );
								if( !$termInfo )
								{
									$termInsRes = wp_insert_term( $term, $postTaxonomy, array( 'slug' => $slug ) );
									if( !is_wp_error( $termInsRes ) )
										$termInfo = get_term( intval( $termInsRes[ 'term_id' ] ), $postTaxonomy, ARRAY_A );
								}
							}
							else
								$termInfo = get_term( $termIdExisted, $postTaxonomy, ARRAY_A );
						}
						else
							$termInfo = get_term( $termIdExisted, $postTaxonomy, ARRAY_A );
					}
				}
				else
					$termInfo = get_term( intval( $termInsRes[ 'term_id' ] ), $postTaxonomy, ARRAY_A );
			}

			if( !$termInfo || is_wp_error( $termInfo ) )
				continue;

			$res[ $termInfo[ 'term_id' ] ] = array( 'name' => $termInfo[ 'name' ], 'slug' => $termInfo[ 'slug' ] );

			if( $lang )
				Wp::SetElemLang( $termInfo[ 'term_id' ], $lang, $postTaxonomy );
		}

		Wp::AddFilters( $filters );

		return( $res );
	}

	static function FindTermByField( array $terms, string $field, $fieldValue, $fieldRet = '', $fieldRetDef = null )
	{
		foreach( $terms as $term )
			if( $term -> $field == $fieldValue )
				return( empty( $fieldRet ) ? $term : $term -> $fieldRet );

		return( empty( $fieldRet ) ? null : $fieldRetDef );
	}

	static function GetSupportsPostsTypes( $supportsList )
	{
		$res = array();

		$postTypes = get_post_types();
		foreach( $postTypes as $postType )
		{
			$supportsResCount = 0;
			foreach( $supportsList as $supportsItem )
				if( post_type_supports( $postType, $supportsItem ) )
					$supportsResCount++;

			if( $supportsResCount < count( $supportsList ) )
				continue;

			$res[] = $postType;
		}

		return( $res );
	}

	static function GetAvailableThumbnails()
	{
		global $_wp_additional_image_sizes;

		$thumbnail_sizes = array();

		$sizeNames = apply_filters( 'image_size_names_choose', array(
			'thumbnail' => esc_html( Wp::GetLocString( 'Thumbnail' ) ),
			'medium'    => esc_html( Wp::GetLocString( 'Medium' ) ),
			'large'     => esc_html( Wp::GetLocString( 'Large' ) ),
			'full'      => esc_html( Wp::GetLocString( 'Full Size' ) ),
		) );

		foreach( get_intermediate_image_sizes() as $id )
		{
			$name = isset( $sizeNames[ $id ] ) ? $sizeNames[ $id ] : null;
			if( empty( $name ) )
			{
				$name = $id;
				$name = str_replace( array( '-', '_' ), ' ', $name );
				$name = strtoupper( substr( $name, 0, 1 ) ) . substr( $name, 1 );
			}

			$thumbnail_sizes[ $id ][ 'name' ] = $name;

			if( in_array( $id, array( 'thumbnail', 'medium', 'medium_large', 'large' ) ) )
			{
				$thumbnail_sizes[ $id ][ 'width' ]  = ( int )get_option( $id . '_size_w' );
				$thumbnail_sizes[ $id ][ 'height' ] = ( int )get_option( $id . '_size_h' );
				$thumbnail_sizes[ $id ][ 'crop' ]   = ( 'thumbnail' == $id ) ? ( bool )get_option( 'thumbnail_crop' ) : false;
			}
			else if( !empty( $_wp_additional_image_sizes ) && !empty( $_wp_additional_image_sizes[ $id ] ) )
			{
				$thumbnail_sizes[ $id ][ 'width' ]  = ( int )$_wp_additional_image_sizes[ $id ][ 'width' ];
				$thumbnail_sizes[ $id ][ 'height' ] = ( int )$_wp_additional_image_sizes[ $id ][ 'height' ];
				$thumbnail_sizes[ $id ][ 'crop' ]   = ( bool )$_wp_additional_image_sizes[ $id ][ 'crop' ];
			}
		}

		return( $thumbnail_sizes );
	}

	static function GetMediaUploadUrl( $postFrom, $siteUrlRelative = false )
	{
		if( is_int( $postFrom ) )
			$postFrom = get_post( $postFrom );

		$post_img_dir = null;
		if( $postFrom )
		{
			global $post, $post_id;

			$post_prev = $post;
			$post = null;
			$post_id_prev = $post_id;
			$post_id = $postFrom -> ID;

			$errorReportingPrevLevel = error_reporting( E_ERROR | E_PARSE );

			$file = apply_filters( 'wp_handle_upload_prefilter', array( 'name' => 'dummy.jpg', 'ext'  => 'jpg', 'type' => 'jpg' ) );

			$wp_upload_dir_res = wp_upload_dir( null, false );
			$post_img_dir = $wp_upload_dir_res[ 'url' ];

			$fileinfo = apply_filters( 'wp_handle_upload', array( 'file' => $file[ 'name' ], 'url'  => $post_img_dir . '/' . $file[ 'name' ], 'type' => $file[ 'type' ] ), 'upload' );

			error_reporting( $errorReportingPrevLevel );

			$post = $post_prev;
			$post_id = $post_id_prev;
		}
		else
		{
			$wp_upload_dir_res = wp_upload_dir( null, false );
			$post_img_dir = $wp_upload_dir_res[ 'baseurl' ];
		}

		return( Gen::SetFirstSlash( Net::Url2Uri( $post_img_dir, $siteUrlRelative ) ) );
	}

	static function GetAttachmentIdFromUrl( $attachment_url = '', $lang = null )
	{
		global $wpdb;

		if( empty( $attachment_url ) )
			return( false );

		{
			$siteAddr = Net::GetSiteAddrFromUrl( $attachment_url );
			if( !empty( $siteAddr ) && $siteAddr != Net::GetSiteAddrFromUrl( Wp::GetSiteWpRootUrl() ) )
				return( 0 );
		}

		$attachment_url = Net::Url2Uri( $attachment_url );

		$upload_dir_path = wp_upload_dir();
		$upload_dir_path = isset( $upload_dir_path[ 'baseurl' ] ) ? $upload_dir_path[ 'baseurl' ] : null;
		if( !$upload_dir_path )
			return( false );

		{
			$checkUris = array(
				Net::Url2Uri( $upload_dir_path, false ),
				Net::Url2Uri( $upload_dir_path, true )
			);

			$checkedIdx = 0;
			foreach( $checkUris as $checkUri )
			{
				if( strpos( $attachment_url, $checkUri ) === 0 )
					break;

				$checkedIdx ++;
			}

			if( $checkedIdx == count( $checkUris ) )
				return( 0 );

			$upload_dir_path = $checkUris[ $checkedIdx ];
		}

		$attachment_id = 0;

		$attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );

		$attachment_url = str_replace( $upload_dir_path . '/', '', $attachment_url );
		$attachment_url = rawurldecode( $attachment_url );

		$attachments = $wpdb -> get_results( $wpdb -> prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url ), ARRAY_A );
		foreach( $attachments as $attachment )
		{
			$aId = $attachment[ 'ID' ];

			$aLang = Wp::GetPostLang( $aId );
			if( $aLang == $lang )
			{
				$attachment_id = $aId;
				break;
			}
		}

		return( $attachment_id );
	}

	static function UpdateAttachment( $attachId, $data, $wp_error = false )
	{
		$data[ 'ID' ] = $attachId;

		$image_alt = isset( $data[ '_wp_attachment_image_alt' ] ) ? $data[ '_wp_attachment_image_alt' ] : null;
		if( $image_alt )
			update_post_meta( $attachId, '_wp_attachment_image_alt', $image_alt );

		return( wp_update_post( $data, $wp_error ) );
	}

	static function GetAttachment( $attachId, $output = OBJECT )
	{
		$data = get_post( $attachId, ARRAY_A );
		if( !$data )
			return( null );

		$image_alt = get_post_meta( $attachId, '_wp_attachment_image_alt', true );
		if( $image_alt )
			$data[ '_wp_attachment_image_alt' ] = $image_alt;

		if( $output == OBJECT )
			$data = ( object )$data;
		return( $data );
	}

	static function AddInlineScript( $handle, $data, $position = 'after' )
	{

		if( Gen::DoesFuncExist( '\\wp_add_inline_script' ) )
			return( \wp_add_inline_script( $handle, $data, $position ) );

		echo( wp_kses( Ui::ScriptInlineContent( $data ), Wp::GetKsesSanitizeCtx( 'script' ) ) );
		return( true );
	}

	const REMOVEFILTER_PRIORITY_ALL							= null;
	const REMOVEFILTER_FUNCNAME_ALL							= null;
	const REMOVEFILTER_TAG_ALL								= null;

	static private function _RemoveFilter_IsEqual( $fn, $fnRem )
	{
		if( $fnRem === null )
			return( true );

		if( is_string( $fn ) )
		{
			$sepClass = strpos( $fn, '::' );
			if( $sepClass !== false )
				$fn = array( substr( $fn, 0, $sepClass ), substr( $fn, $sepClass + strlen( '::' ) ) );
		}

		if( $fn === $fnRem )
			return( true );

		if( is_array( $fn ) && count( $fn ) == 2 )
		{
			$fnObj = $fn[ 0 ];
			$fnName = $fn[ 1 ];

			if( is_array( $fnRem ) && count( $fnRem ) == 2 )
			{
				$fnRemClass = $fnRem[ 0 ];
				$fnRemName = $fnRem[ 1 ];

				if( $fnRemName === Wp::REMOVEFILTER_FUNCNAME_ALL || $fnRemName === $fnName )
					if( is_string( $fnRemClass ) )
					{
						if( is_object( $fnObj ) && $fnRemClass == get_class( $fnObj ) )
							return( true );
						if( is_string( $fnObj ) && $fnRemClass == $fnObj )
							return( true );
					}
			}
		}

		return( false );
	}

	static function GetFilters( $tag, $fn = null, $priority = Wp::REMOVEFILTER_PRIORITY_ALL )
	{
		$filters = array(); self::_GetRemoveFilters( false, $tag, $fn, $priority, $filters );
		return( $filters );
	}

	static function RemoveFilters( $tag, $fnRem = null, $priority = Wp::REMOVEFILTER_PRIORITY_ALL, &$filters = array() )
	{
		return( self::_GetRemoveFilters( true, $tag, $fnRem, $priority, $filters ) );
	}

	static private function _GetRemoveFilters( $remove, $tag, $fnRem, $priority, &$filters )
	{
		global $wp_filter;

		$items = array();
		{
			$flts = $wp_filter;
			if( $tag !== Wp::REMOVEFILTER_TAG_ALL )
			{
				$fltPriors = isset( $wp_filter[ $tag ] ) ? $wp_filter[ $tag ] : null;
				if( !$fltPriors )
					return( false );

				$flts = array( $tag => $fltPriors );
			}

			foreach( $flts as $fltTag => $fltPriors )
			{

				if( is_object( $fltPriors )  )
				{
					if( property_exists( $fltPriors, 'callbacks' ) )
						$fltPriors = $fltPriors -> callbacks;
				}

				if( is_array( $fltPriors ) )
					foreach( $fltPriors as $fltPrior => $cbs )
					{
						if( $priority !== self::REMOVEFILTER_PRIORITY_ALL && $fltPrior != $priority )
							continue;

						foreach( $cbs as $cbKey => $cb )
						{
							$fn = $cb[ 'function' ];
							if( self::_RemoveFilter_IsEqual( $fn, $fnRem ) )
								$items[] = array( 't' => $fltTag, 'k' => $cbKey, 'f' => $fn, 'p' => $fltPrior, 'a' => $cb[ 'accepted_args' ] );
						}
					}
			}
		}

		$res = false;

		foreach( $items as &$item )
		{
			if( $remove ? remove_filter( $item[ 't' ], $item[ 'k' ], $item[ 'p' ] ) : true )
			{
				$res = true;

				if( $remove )
					unset( $item[ 'k' ] );
				$filters[] = $item;
			}
		}

		return( $res );
	}

	static function AddFilters( $filters )
	{
		$res = false;

		if( is_array( $filters ) )
			foreach( $filters as $filter )
				if( add_filter( $filter[ 't' ], $filter[ 'f' ], $filter[ 'p' ], $filter[ 'a' ] ) )
					$res = true;

		return( $res );
	}

	static function InitScreenOption( string $plugin_page, string $option )
	{
		add_filter( 'set_screen_option_toplevel_page_' . $plugin_page . '_' . $option,
			function( $screen_option, $option, $value )
			{
				return( $value );
			}
		, 10, 3 );
	}

	static function AddScreenOption( string $option )
	{
		add_screen_option( $option );
	}

	static function GetScreenOptionId( string $option )
	{
		$current_screen = get_current_screen();
		if( !$current_screen )
			return;

		return( $current_screen -> id . '_' . $option );
	}

	static function GetGmtOffset()
	{
		return( ( int )( ( float )get_option( 'gmt_offset' ) * 60 * 60 ) );
	}

	static function GetISOFirstWeekDay()
	{
		$v = ( int )get_option( 'start_of_week' ) % 7;
		return( $v ? $v : 7 );
	}

	const SETPOSTLANG_IDORIG_UNSET			= null;
	const SETPOSTLANG_IDORIG_DONTCHANGE		= -1;

	static function IsLangsActive()
	{
		if( self::_Wpml_IsActive() )	return( true );
		if( self::_Pll_IsActive() )		return( true );
		return( false );
	}

	static function GetDefLang()
	{
		if( self::_Wpml_IsActive() )	return( self::_Wpml_GetDefLang() );
		if( self::_Pll_IsActive() )		return( self::_Pll_GetDefLang() );
		return( null );
	}

	static function GetCurLang( $noLangVal = null )
	{
		if( self::_Wpml_IsActive() )	return( self::_Wpml_GetCurLang() );
		if( self::_Pll_IsActive() )		return( self::_Pll_GetCurLang() );
		return( $noLangVal );
	}

	static function SetCurLang( $lang )
	{
		if( self::_Wpml_IsActive() )	return( self::_Wpml_SetCurLang( $lang ) );
		if( self::_Pll_IsActive() )		return( self::_Pll_SetCurLang( $lang ) );
	}

	static function GetLangs()
	{
		if( self::_Wpml_IsActive() )	return( self::_Wpml_GetLangs() );
		if( self::_Pll_IsActive() )		return( self::_Pll_GetLangs() );
		return( null );
	}

	static function GetPostLang( $id, $postType = null )
	{
		$lang = null;

		if( empty( $postType ) )
			$postType = get_post_type( $id );

		if( self::_Wpml_IsActive() )	$lang = self::_Wpml_GetElemLang( $id, $postType );
		if( self::_Pll_IsActive() )		$lang = self::_Pll_GetElemLang( $id, $postType );

		return( $lang );
	}

	static function GetElemLang( $id, $type )
	{
		$lang = null;

		if( self::_Wpml_IsActive() )	$lang = self::_Wpml_GetElemLang( $id, $type );
		if( self::_Pll_IsActive() )		$lang = self::_Pll_GetElemLang( $id, $type );

		return( $lang );
	}

	static function SetPostLang( $id, $lang, $idOrig = self::SETPOSTLANG_IDORIG_DONTCHANGE )
	{
		$type = get_post_type( $id );
		$typeOrig = ( $idOrig !== self::SETPOSTLANG_IDORIG_DONTCHANGE ) ? get_post_type( $idOrig ) : null;

		if( self::_Wpml_IsActive() )	return( self::_Wpml_SetElemLang( $id, $type, $lang, $idOrig, $typeOrig ) );
		if( self::_Pll_IsActive() )		return( self::_Pll_SetElemLang( $id, $type, $lang, $idOrig, $typeOrig ) );
		return( Gen::E_NOTIMPL );
	}

	static function SetElemLang( $id, $lang, $type, $idOrig = self::SETPOSTLANG_IDORIG_DONTCHANGE, $typeOrig = null )
	{
		if( self::_Wpml_IsActive() )	return( self::_Wpml_SetElemLang( $id, $type, $lang, $idOrig, $typeOrig ) );
		if( self::_Pll_IsActive() )		return( self::_Pll_SetElemLang( $id, $type, $lang, $idOrig, $typeOrig ) );
		return( Gen::E_NOTIMPL );
	}

	static function RemoveLangFilters()
	{
		if( self::_Wpml_IsActive() )	return( self::_Wpml_RemoveLangFilters() );
		if( self::_Pll_IsActive() )		return( self::_Pll_RemoveLangFilters() );
		return( null );
	}

	static function RemoveLangAttachmentFilters()
	{
		if( self::_Wpml_IsActive() )	return( self::_Wpml_RemoveLangAttachmentFilters() );
		if( self::_Pll_IsActive() )		return( self::_Pll_RemoveLangAttachmentFilters() );
		return( null );
	}

	static private function _Wpml_IsActive()
	{
		global $sitepress;
		return( !!$sitepress );

	}

	static private function _Wpml_GetDefLang()
	{
		global $sitepress;
		return( $sitepress -> get_default_language() );
	}

	static private function _Wpml_GetCurLang()
	{
		global $sitepress;
		return( $sitepress -> get_current_language() );
	}

	static function _Wpml_OnHomeUrl_GetLangFromUrl( $language, $url )
	{
		return( self::_Wpml_GetCurLang() );
	}

	static private function _Wpml_SetCurLang( $lang )
	{
		global $sitepress;
		$sitepress -> switch_lang( $lang );

		static $bFltSet = false;

		if( $bFltSet )
			return;

		$bFltSet = true;

		add_filter( 'home_url',
			function( $url )
			{

				$_SERVER[ 'REQUEST_URI' ] = add_query_arg( array( '_seraph_accel_home_url_tmp_lang' => self::_Wpml_GetCurLang() ), $_SERVER[ 'REQUEST_URI' ] );
				add_filter( 'wpml_get_language_from_url', __CLASS__ . '::_Wpml_OnHomeUrl_GetLangFromUrl', 99999, 2 );
				return( $url );
			}
		, -99999 );

		add_filter( 'home_url',
			function( $url )
			{
				remove_filter( 'wpml_get_language_from_url', __CLASS__ . '::_Wpml_OnHomeUrl_GetLangFromUrl', 99999 );
				$_SERVER[ 'REQUEST_URI' ] = remove_query_arg( '_seraph_accel_home_url_tmp_lang', $_SERVER[ 'REQUEST_URI' ] );
				return( $url );
			}
		, 99999 );
	}

	static private function _Wpml_GetLangs()
	{
		$res = array();

		$langCurPrev = self::_Wpml_GetCurLang();
		$langDef = self::_Wpml_GetDefLang();

		if( $langCurPrev != $langDef )
			self::_Wpml_SetCurLang( $langDef );

		$ls = icl_get_languages( 'skip_missing=0' );
		foreach( $ls as $l )
			$res[ $l[ 'language_code' ] ] = $l[ 'translated_name' ];

		if( $langCurPrev != $langDef )
			self::_Wpml_SetCurLang( $langCurPrev );

		return( $res );
	}

	static private function _Wpml_GetElemLang( $id, $type )
	{
		global $sitepress;

		if( empty( $type ) )
			return( null );

		$typeWpml = apply_filters( 'wpml_element_type', $type );
		if( empty( $typeWpml ) )
			return( null );

		$langInfoPost = $sitepress -> get_element_language_details( $id, $typeWpml );
		if( !$langInfoPost )
			return( null );

		return( $langInfoPost -> language_code );
	}

	static private function _Wpml_SetElemLang( $id, $type, $lang, $idOrig, $typeOrig )
	{
		global $sitepress;

		if( empty( $type ) )
			return( Gen::E_INVALIDARG );

		$typeWpml = apply_filters( 'wpml_element_type', $type );
		if( empty( $typeWpml ) )
			return( Gen::E_INTERNAL );

		$langTrId = null;
		$sourceLangCode = null;

		if( !empty( $idOrig ) )
		{
			if( $idOrig === self::SETPOSTLANG_IDORIG_DONTCHANGE )
			{
				$langInfoPost = $sitepress -> get_element_language_details( $id, $typeWpml );

				if( $langInfoPost )
				{
					$langTrId = $langInfoPost -> trid;
					$sourceLangCode = $langInfoPost -> source_language_code;
				}
			}
			else
			{
				if( empty( $typeOrig ) || $typeOrig != $type )
					return( Gen::E_INVALIDARG );

				$langInfoPostOrig = $sitepress -> get_element_language_details( $idOrig, $typeWpml );

				if( $langInfoPostOrig )
				{
					$langTrId = $langInfoPostOrig -> trid;
					$sourceLangCode = $langInfoPostOrig -> language_code;
				}
			}
		}

		$sitepress -> set_element_language_details( $id, $typeWpml, $langTrId, $lang, $sourceLangCode, true );

		return( Gen::S_OK );
	}

	static private function _Wpml_RemoveLangAttachmentFilters()
	{

		$filters = array();
		Wp::RemoveFilters( 'add_attachment',				array( 'WPML_Media_Attachments_Duplication', Wp::REMOVEFILTER_FUNCNAME_ALL ), Wp::REMOVEFILTER_PRIORITY_ALL, $filters );
		Wp::RemoveFilters( 'edit_attachment',				array( 'WPML_Media_Attachments_Duplication', Wp::REMOVEFILTER_FUNCNAME_ALL ), Wp::REMOVEFILTER_PRIORITY_ALL, $filters );
		Wp::RemoveFilters( 'save_post',						array( 'WPML_Media_Attachments_Duplication', Wp::REMOVEFILTER_FUNCNAME_ALL ), Wp::REMOVEFILTER_PRIORITY_ALL, $filters );
		return( $filters );
	}

	static private function _Wpml_RemoveLangFilters()
	{

		$filters = array();
		Wp::RemoveFilters( Wp::REMOVEFILTER_TAG_ALL,		array( 'SitePress', Wp::REMOVEFILTER_FUNCNAME_ALL ), Wp::REMOVEFILTER_PRIORITY_ALL, $filters );

		return( $filters );
	}

	static private function _Pll_IsActive()
	{
		return( Gen::DoesFuncExist( 'PLL' ) );
	}

	static private function _Pll_GetDefLang()
	{
		return( pll_default_language() );
	}

	static private function _Pll_GetCurLang()
	{
		return( pll_current_language() );
	}

	static private function _Pll_SetCurLang( $lang )
	{

	}

	static private function _Pll_GetLangs()
	{
		$res = array();

		$lsN = pll_languages_list( array( 'fields' => 'name' ) );
		$ls = pll_languages_list();
		foreach( $ls as $li => $l )
			$res[ $l ] = $lsN[ $li ];

		return( $res );
	}

	static private function _Pll_GetElemLang( $id, $type )
	{
		if( empty( $type ) )
			return( null );
		return( taxonomy_exists( $type ) ? pll_get_term_language( $id ) : pll_get_post_language( $id ) );
	}

	static private function _Pll_SetElemLang( $id, $type, $lang, $idOrig, $typeOrig )
	{
		if( empty( $type ) )
			return( Gen::E_INVALIDARG );

		$isTax =  taxonomy_exists( $type );

		if( !empty( $idOrig ) )
		{
			if( $idOrig !== self::SETPOSTLANG_IDORIG_DONTCHANGE )
			{
				if( empty( $typeOrig ) || $typeOrig != $type )
					return( Gen::E_INVALIDARG );

				$langDef = pll_default_language();
				$trans = $isTax ? pll_get_term_translations( $idOrig ) : pll_get_post_translations( $idOrig );
				$transNew = array( $langDef => $idOrig );
				foreach( $trans as $transLang => $transId )
					if( $transLang != $langDef )
						$transNew[ $transLang ] = $transId;
				$transNew[ $lang ] = $id;

				$isTax ? pll_save_term_translations( $transNew ) : pll_save_post_translations( $transNew );
			}
		}
		else
		{
			$transNew = array( $lang => $id );
			$isTax ? pll_save_term_translations( $transNew ) : pll_save_post_translations( $transNew );
		}

		if( $isTax )
			pll_set_term_language( $id, $lang );
		else
			pll_set_post_language( $id, $lang );

		return( Gen::S_OK );
	}

	static private function _Pll_RemoveLangAttachmentFilters()
	{
		$filters = array();
		Wp::RemoveFilters( Wp::REMOVEFILTER_TAG_ALL,		array( 'PLL_Admin_Filters_Media', Wp::REMOVEFILTER_FUNCNAME_ALL ), Wp::REMOVEFILTER_PRIORITY_ALL, $filters );
		return( $filters );
	}

	static private function _Pll_RemoveLangFilters()
	{

		$filters = array();
		Wp::RemoveFilters( Wp::REMOVEFILTER_TAG_ALL,		array( 'PLL_Admin_Filters', Wp::REMOVEFILTER_FUNCNAME_ALL ), Wp::REMOVEFILTER_PRIORITY_ALL, $filters );
		Wp::RemoveFilters( Wp::REMOVEFILTER_TAG_ALL,		array( 'PLL_Admin_Filters_Post', Wp::REMOVEFILTER_FUNCNAME_ALL ), Wp::REMOVEFILTER_PRIORITY_ALL, $filters );
		Wp::RemoveFilters( Wp::REMOVEFILTER_TAG_ALL,		array( 'PLL_Admin_Filters_Term', Wp::REMOVEFILTER_FUNCNAME_ALL ), Wp::REMOVEFILTER_PRIORITY_ALL, $filters );
		Wp::RemoveFilters( Wp::REMOVEFILTER_TAG_ALL,		array( 'PLL_Admin_Filters_Media', Wp::REMOVEFILTER_FUNCNAME_ALL ), Wp::REMOVEFILTER_PRIORITY_ALL, $filters );
		return( $filters );
	}

	const LOC_DEF			= 'en_US';

	static private function _Loc_DomainExpand( &$domain, &$domainFile )
	{
		$domainFile = $domain;
		if( is_array( $domain ) && count( $domain ) )
		{
			if( count( $domain ) > 1 )
				$domainFile = $domain[ 1 ];
			$domain = $domain[ 0 ];
		}
	}

	static function Loc_ScriptLoadEx( $handle, $domain, $ns, $pathAbsRoot )
	{
		self::_Loc_DomainExpand( $domain, $domainFile );

		if( Gen::DoesFuncExist( '\\wp_set_script_translations' ) )
			\wp_set_script_translations( $handle, $domain, $pathAbsRoot );

		$localeCur = Wp::GetLocale();
		$locDataFilePrefix = $pathAbsRoot . '/' . $domainFile . '-' . substr( $handle, strlen( $ns ) + 1 ) . '-';

		$locDataFile = null;
		{
			$locales = array( $localeCur );
			if( $localeCur != self::LOC_DEF )
				$locales[] = self::LOC_DEF;

			foreach( $locales as $locale )
			{
				$locDataFileTry = $locDataFilePrefix . $locale . '.json';
				if( !@file_exists( $locDataFileTry ) )
					continue;

				$locDataFile = $locDataFileTry;
				break;
			}
		}

		if( empty( $locDataFile ) )
			return( false );

		$translations = self::_LoadScriptTranslations( $locDataFile, $handle, $domain );
		if( empty( $translations ) )
			return( false );

		return( Wp::AddInlineScript( $handle, '(function(data){seraph_accel.Wp.Loc.SetData(data.locale_data.messages,"' . $domain . '");})(' . $translations . ')' ) );

	}

	static function Loc_ScriptLoad( $handle, $domain, $domainLocDir, $locSubPath )
	{
		if( !$domainLocDir )
			$domainLocDir = $domain;

		$pathAbsRoot = WP_PLUGIN_DIR;

		return( Wp::Loc_ScriptLoadEx( $handle, $domain, 'seraph_accel', $pathAbsRoot . '/' . $domainLocDir . '/' . $locSubPath ) );
	}

	static private function _LoadScriptTranslations( $file, $handle, $domain )
	{
		if( Gen::DoesFuncExist( '\\load_script_translations' ) )
			return( load_script_translations( $file, $handle, $domain ) );

		$file = apply_filters( 'load_script_translation_file', $file, $handle, $domain );
		if( !$file || !is_readable( $file ) )
			return( false );

		return( apply_filters( 'load_script_translations', @file_get_contents( $file ), $file, $handle, $domain ) );
	}

	static function GetLocale()
	{
		return( Gen::DoesFuncExist( 'determine_locale' ) ? determine_locale() : ( is_admin() ? get_user_locale() : get_locale() ) );
	}

	static private function _Loc_LoadPrepareSubSystemIds( $subSystemIds )
	{
		if( $subSystemIds === null )
			$subSystemIds = array( '', 'admin' );

		if( !is_array( $subSystemIds ) )
			$subSystemIds = array( $subSystemIds );

		return( $subSystemIds );
	}

	static function Loc_LoadEx( $subSystemIds = null, $domain = null, $pathAbsRoot = null )
	{
		self::_Loc_DomainExpand( $domain, $domainFile );

		$subSystemIds = self::_Loc_LoadPrepareSubSystemIds( $subSystemIds );
		if( !count( $subSystemIds ) )
			return( false );

		$localeCur = Wp::GetLocale();

		$localeRes = false;

		$locales = array( $localeCur );
		if( $localeCur != self::LOC_DEF )
			$locales[] = self::LOC_DEF;

		foreach( $subSystemIds as $subSystemId )
		{
			$name = $domainFile;
			if( !empty( $subSystemId ) )
				$name .= '-' . $subSystemId;

			foreach( $locales as $locale )
			{
				if( !load_textdomain( $domain, $pathAbsRoot . '/' . $name . '-' . $locale . '.mo' ) )
					continue;

				$localeRes = $locale;
				break;
			}
		}

		return( $localeRes );
	}

	static private $_locLoadCtx = null;

	static private function _Loc_LoadTextDomain( $domain, $pathRel )
	{

		if( !function_exists( 'has_translation' )  )
			return( load_plugin_textdomain( $domain, false, $pathRel ) );

		$locale = apply_filters( 'plugin_locale', determine_locale(), $domain );

		$mofile = $domain . '-' . $locale . '.mo';

		if ( load_textdomain( $domain, WP_LANG_DIR . '/plugins/' . $mofile ) )
			return true;

		if( false !== $pathRel )
			$path = WP_PLUGIN_DIR . '/' . trim( $pathRel, '/' );
		else
			$path = WP_PLUGIN_DIR;

		return load_textdomain( $domain, $path . '/' . $mofile );

	}

	static function Loc_Load( $subSystemIds = null, $domain = null, $domainLocDir = null, $locSubPath = null, array $addFiles = array() )
	{
		if( !$domainLocDir )
			$domainLocDir = $domain;

		$subSystemIds = self::_Loc_LoadPrepareSubSystemIds( $subSystemIds );
		if( !count( $subSystemIds ) )
			return( false );

		$aFlt = array();
		Wp::RemoveFilters( 'override_load_textdomain', array( 'Performant_Translations', Wp::REMOVEFILTER_FUNCNAME_ALL ), Wp::REMOVEFILTER_PRIORITY_ALL, $aFlt );
		Wp::RemoveFilters( Wp::REMOVEFILTER_TAG_ALL, array( 'Loco_hooks_LoadHelper', Wp::REMOVEFILTER_FUNCNAME_ALL ), Wp::REMOVEFILTER_PRIORITY_ALL, $aFlt );
		Wp::RemoveFilters( Wp::REMOVEFILTER_TAG_ALL, array( 'Loco_hooks_LegacyLoadHelper', Wp::REMOVEFILTER_FUNCNAME_ALL ), Wp::REMOVEFILTER_PRIORITY_ALL, $aFlt );

		$pathAbsRoot = WP_PLUGIN_DIR . '/' . $domainLocDir;
		$pathRel = $domainLocDir . '/' . $locSubPath;

		add_filter( 'load_textdomain_mofile', __CLASS__ . '::_on_load_textdomain_mofile', 0, 2 );

		$localeCur = Wp::GetLocale();

		$locales = array( null );
		if( $localeCur != self::LOC_DEF )
			$locales[] = self::LOC_DEF;

		$localeRes = false;

		self::$_locLoadCtx = array( 'pathAbsRoot' => $pathAbsRoot, 'forceLoadOwn' => false );

		self::$_locLoadCtx[ 'localeLoading' ] = $localeCur;
		self::$_locLoadCtx[ 'pathRel' ] = $pathRel;

		foreach( $subSystemIds as $subSystemId )
		{
			self::$_locLoadCtx[ 'subSystemIdCur' ] = $subSystemId;

			$subSystemLoaded = false;
			$pathAbsRootTouched = false;

			$addFilesLoaded = array();

			foreach( $locales as $locale )
			{
				self::$_locLoadCtx[ 'localeCur' ] = $locale;
				self::$_locLoadCtx[ 'pathAbsRootTouched' ] = false;

				if( !$subSystemLoaded )
				{
					if( self::_Loc_LoadTextDomain( $domain, $pathRel ) )
						$subSystemLoaded = true;

					$pathAbsRootTouched = self::$_locLoadCtx[ 'pathAbsRootTouched' ];
					if( $subSystemLoaded && !$pathAbsRootTouched )
					{
						self::$_locLoadCtx[ 'forceLoadOwn' ] = true;
						$pathAbsRootTouched = self::_Loc_LoadTextDomain( $domain, $pathRel );
						self::$_locLoadCtx[ 'forceLoadOwn' ] = false;
					}
				}

				if( !empty( $addFiles ) )
				{
					$localeFilePart = apply_filters( 'plugin_locale', $localeCur, $domain );

					foreach( $addFiles as $addFileIdx => $addFile )
					{
						$mofile = $pathAbsRoot . '/' . $addFile . '-' . $localeFilePart . '.mo';
						if( !( isset( $addFilesLoaded[ $addFileIdx ] ) ? $addFilesLoaded[ $addFileIdx ] : null ) && load_textdomain( $domain, $mofile ) )
							$addFilesLoaded[ $addFileIdx ] = true;
					}
				}

				if( !$localeRes && ( $subSystemLoaded || count( $addFilesLoaded ) == count( $addFiles ) ) )
				{
					$localeRes = self::$_locLoadCtx[ 'localeCur' ];
					if( !$localeRes )
						$localeRes = $localeCur;
				}

				if( $subSystemLoaded && !$pathAbsRootTouched )
					$subSystemLoaded = false;
			}
		}

		self::$_locLoadCtx = null;

		remove_filter( 'load_textdomain_mofile', __CLASS__ . '::_on_load_textdomain_mofile', 0 );

		Wp::AddFilters( $aFlt );

		return( $localeRes );
	}

	static function _on_load_textdomain_mofile( $mofile, $domain )
	{
		if( strpos( $mofile, self::$_locLoadCtx[ 'pathRel' ] . '/' ) === false )
			return( $mofile . '.SKIP' );

		if( !empty( self::$_locLoadCtx[ 'subSystemIdCur' ] ) )
			$mofile = Gen::GetFileDir( $mofile ) . '/' . str_replace( self::$_locLoadCtx[ 'localeLoading' ] . '.', self::$_locLoadCtx[ 'subSystemIdCur' ] . '-' . self::$_locLoadCtx[ 'localeLoading' ] . '.', Gen::GetFileName( $mofile ) );

		if( !empty( self::$_locLoadCtx[ 'localeCur' ] ) )
			$mofile = Gen::GetFileDir( $mofile ) . '/' . str_replace( self::$_locLoadCtx[ 'localeLoading' ] . '.', self::$_locLoadCtx[ 'localeCur' ] . '.', Gen::GetFileName( $mofile ) );

		$pathAbsRoot = self::$_locLoadCtx[ 'pathAbsRoot' ];
		$pathAbsRootTouched = substr( $mofile, 0, strlen( $pathAbsRoot ) ) == $pathAbsRoot;

		if( $pathAbsRootTouched )
			self::$_locLoadCtx[ 'pathAbsRootTouched' ] = true;
		else if( self::$_locLoadCtx[ 'forceLoadOwn' ] )
			return( null );

		return( $mofile );
	}

	static function GetMultisiteAdminModes()
	{
		$res = array( 'global' => true, 'local' => true );
		if( !is_multisite() )
			return( $res );

		$res[ 'global' ] = defined( 'WP_NETWORK_ADMIN' ) && WP_NETWORK_ADMIN == true;
		$res[ 'local' ] = !$res[ 'global' ];
		return( $res );
	}

	static function IsMultisiteMain()
	{
		if( !is_multisite() || !defined( 'BLOG_ID_CURRENT_SITE' ) )
			return( true );
		return( get_current_blog_id() == BLOG_ID_CURRENT_SITE );
	}

	static function IsMultisiteGlobalAdmin()
	{
		if( !is_multisite() )
			return( false );
		return( defined( 'WP_NETWORK_ADMIN' ) && WP_NETWORK_ADMIN == true );
	}

	static function GetPagenumUrl( $url, $pagenum = 1, $escape = true )
	{
		$uriRequestOld = $_SERVER[ 'REQUEST_URI' ];
		$_SERVER[ 'REQUEST_URI' ] = Net::Url2Uri( $url );
		$url = get_pagenum_link( $pagenum, $escape );
		$_SERVER[ 'REQUEST_URI' ] = $uriRequestOld;
		return( $url );
	}

	static function GetCommentPagenumUrl( $url, $pagenum = 1 )
	{
		global $post;

		$postCont = Wp::CreateFakePostContainer();

		$postOld = $post;
		$post = $postCont -> post;

		$ctx = new AnyObj();
		$ctx -> post = $post;
		$ctx -> url = $url;
		$ctx -> cb = function( $ctx, $permalink, $post )
		{
			if( $post -> ID === $ctx -> post -> ID )
				return( $ctx -> url );
			return( $permalink );
		};

		add_filter( 'post_link', array( $ctx, 'cb' ), 99999, 2 );
		$url = get_comments_pagenum_link( $pagenum );
		remove_filter( 'post_link', array( $ctx, 'cb' ), 99999 );

		$post = $postOld;
		return( $url );
	}

	static function GetCronUrl( $args = array() )
	{
		return( Net::UrlAddArgs( Wp::GetSiteWpRootUrl( 'wp-cron.php' ), $args ) );
	}

	static function IsInRunningCron()
	{
		return( defined( 'DOING_CRON' ) && DOING_CRON );
	}

	static function GetHomePath()
	{
		if( !function_exists( 'get_home_path' ) )
			require_once( ABSPATH . 'wp-admin/includes/file.php' );

		return( get_home_path() );
	}
}

