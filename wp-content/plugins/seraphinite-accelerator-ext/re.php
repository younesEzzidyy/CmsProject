<?php

class _seraph_accel_re_Rtn
{
	public function __destruct()
	{
		_seraph_accel_re_Rtn::IncludeGen();
		_seraph_accel_re_Rtn::End( \seraph_accel\Net::GetHrFromResponseCode( http_response_code() ), false );
	}

	static function RunInit()
	{
		http_response_code( 200 );
		self::$g_oInst = new _seraph_accel_re_Rtn();
	}

	static function IncludeGen()
	{
		if( method_exists( '\\seraph_accel\\Gen', 'HrSucc' ) )
			return;

		define( 'ABSPATH', __DIR__ );
		require( __DIR__ . '/Cmn/Gen.php' );
	}

	static function End( $hr, $exit = true )
	{
		echo( "\x01" . json_encode( array( 'hr' => $hr ) ) . "\x01" );
		if( $exit )
			exit( 0 );
	}

	static function ExecUnMaskUrlArg( $v )
	{

		return( str_replace( '^', '%', $v ) );
	}

	private static $g_oInst;
}

$aOpts = getopt( '', array( 'root:', 'soft:', 'url:', 'method:', 'hdrs:', 't:', 'tmp:' ) );

if( isset( $aOpts[ 't' ] ) )
{

	$reFile = defined( 'SERAPH_ACCEL_REFILE' ) ? SERAPH_ACCEL_REFILE : __FILE__;

	_seraph_accel_re_Rtn::IncludeGen();

	if( !function_exists( 'proc_open' ) || !function_exists( 'proc_close' ) )
		_seraph_accel_re_Rtn::End( \seraph_accel\Gen::E_ACCESS_DENIED );

	$nTimeout = ( int )$aOpts[ 't' ] * 1000;

	$sCmd = \seraph_accel\Gen::ExecEscArg( PHP_BINARY ) . ' -c ' . \seraph_accel\Gen::ExecEscArg( php_ini_loaded_file() ) . ' -d opcache.file_cache=' . \seraph_accel\Gen::ExecEscArg( ini_get( 'opcache.file_cache' ) ) . ' -d opcache.enable_cli=' . ini_get( 'opcache.enable_cli' ) . ' -d opcache.file_cache_only=' . ini_get( 'opcache.file_cache_only' ) . ' -d opcache.validate_timestamps=' . ini_get( 'opcache.validate_timestamps' ) . ' -d opcache.optimization_level=' . ini_get( 'opcache.optimization_level' ) . ' -d display_errors=' . ini_get( 'display_errors' ) . ' -d output_buffering=' . ini_get( 'output_buffering' ) . ' -d memory_limit=' . ini_get( 'memory_limit' ) . ' -d max_execution_time=' . ini_get( 'max_execution_time' ) . ' -d register_argc_argv=' . ini_get( 'register_argc_argv' ) . ' ' . \seraph_accel\Gen::ExecEscArg( $reFile ) . ' --root=' . \seraph_accel\Gen::ExecEscArg( (isset($aOpts[ 'root' ])?$aOpts[ 'root' ]:'') ) . ' --soft=' . \seraph_accel\Gen::ExecEscArg( (isset($aOpts[ 'soft' ])?$aOpts[ 'soft' ]:'') ) . ' --method=' . \seraph_accel\Gen::ExecEscArg( (isset($aOpts[ 'method' ])?$aOpts[ 'method' ]:'') ) . ' --url=' . \seraph_accel\Gen::ExecEscArg( (isset($aOpts[ 'url' ])?$aOpts[ 'url' ]:'') ) . ' --hdrs=' . \seraph_accel\Gen::ExecEscArg( (isset($aOpts[ 'hdrs' ])?$aOpts[ 'hdrs' ]:'') );

	if( isset( $aOpts[ 'tmp' ] ) )
		$sFileOut = ( string )$aOpts[ 'tmp' ];
	else
		$sFileOut = strstr( strtolower( PHP_OS ), 'win' ) ? 'NUL' : '/dev/null';

	$hProc = @proc_open( $sCmd, array( 1 => array( 'file', $sFileOut, 'w' ) ), $aPipe, null, null, array( 'bypass_shell' => true ) );
	if( !$hProc )
		_seraph_accel_re_Rtn::End( \seraph_accel\Gen::E_FAIL );

	if( !$nTimeout )
		_seraph_accel_re_Rtn::End( \seraph_accel\Gen::S_OK );

	$sBody = '';
	while( $nTimeout )
	{
		usleep( 250 * 1000 );

		$stat = proc_get_status( $hProc );
		if( !$stat[ 'running' ] )
			break;

		$nTimeout -= 250;
	}

	if( !$nTimeout )
		_seraph_accel_re_Rtn::End( \seraph_accel\Gen::E_TIMEOUT );

	if( !isset( $aOpts[ 'tmp' ] ) )
		_seraph_accel_re_Rtn::End( \seraph_accel\Gen::S_OK );

	print( ( string )file_get_contents( $aOpts[ 'tmp' ] ) );
	exit( 0 );
}

{
	if( !isset( $aOpts[ 'root' ] ) )
	{
		echo( 'E_INVALIDARG: root' );

		_seraph_accel_re_Rtn::IncludeGen();
		_seraph_accel_re_Rtn::End( \seraph_accel\Gen::E_INVALIDARG );
	}

	$_SERVER[ 'DOCUMENT_ROOT' ] = str_replace( '//', '/', $aOpts[ 'root' ] );
}

$dirPrev = str_replace( '\\', '/', __FILE__ );

if( strpos( $dirPrev, '/public_html' ) !== false && strpos( $_SERVER[ 'DOCUMENT_ROOT' ], '/private_html' ) !== false )
	$_SERVER[ 'DOCUMENT_ROOT' ] = str_replace( '/private_html', '/public_html', $_SERVER[ 'DOCUMENT_ROOT' ] );

{
	$dirDocRootPrefix = '';
	$dirDocRootProbe = rtrim( $_SERVER[ 'DOCUMENT_ROOT' ], '/' );

	for( ;; )
	{
		if( strpos( $dirPrev, $dirDocRootProbe ) === 0 )
			break;

		$dirDocRootProbeNext = ltrim( $dirDocRootProbe, '/' );
		$posSlash = strpos( $dirDocRootProbeNext, '/' );
		if( $posSlash === false )
		{
			$dirDocRootPrefix = null;
			break;
		}

		$dirDocRootProbeNext = substr( $dirDocRootProbeNext, $posSlash );
		$dirDocRootPrefix .= substr( $dirDocRootProbe, 0, strlen( $dirDocRootProbe ) - strlen( $dirDocRootProbeNext ) );
		$dirDocRootProbe = $dirDocRootProbeNext;
	}

	if( $dirDocRootPrefix )
		$dirPrev = $dirDocRootPrefix . $dirPrev;
}

if( defined( 'SERAPH_ACCEL_REFILE' ) )
	$dirSiteRootCurScript = dirname( SERAPH_ACCEL_REFILE );
else
{
	$aDirPrev = array( $dirPrev );
	while( $dirSiteRootCurScript = @dirname( $dirPrev ) )
	{
		if( $dirSiteRootCurScript === $dirPrev )
		{
			$dirSiteRootCurScript = null;
			break;
		}

		$aDirPrev[] = $dirSiteRootCurScript;
		if( @file_exists( $dirSiteRootCurScript . '/wp-load.php' ) )
			break;

		$dirPrev = $dirSiteRootCurScript;
	}

	unset( $dirPrev );

	if( !$dirSiteRootCurScript )
	{
		echo( 'E_INVALID_STATE: reFile: ' . json_encode( $aDirPrev ) );

		_seraph_accel_re_Rtn::IncludeGen();
		_seraph_accel_re_Rtn::End( \seraph_accel\Gen::E_INVALID_STATE );
	}
}

$dirSiteRootCurScript .= '/';
{
	$pos = strpos( $dirSiteRootCurScript, $_SERVER[ 'DOCUMENT_ROOT' ] );
	if( $pos === false )
	{
		$_SERVER[ 'DOCUMENT_ROOT' ] = $dirSiteRootCurScript;
	}

	else if( $pos > 0 )
		$dirSiteRootCurScript = substr( $dirSiteRootCurScript, $pos );
}

{
	$aOpts[ 'hdrs' ] = ( array )@json_decode( rawurldecode( isset( $aOpts[ 'hdrs' ] ) ? _seraph_accel_re_Rtn::ExecUnMaskUrlArg( $aOpts[ 'hdrs' ] ) : '' ), true );

	foreach( $aOpts[ 'hdrs' ] as $hdrKey => $hdrVal )
	{
		$hdrKey = strtoupper( str_replace( array( '-' ), '_', $hdrKey ) );
		$_SERVER[ 'HTTP_' . $hdrKey ] = $hdrVal;

		if( $hdrKey == 'COOKIE' )
		{
			foreach( explode( ';', trim( $hdrVal, " \t\n\r\0\x0B;" ) ) as $cookieKeyVal )
			{
				$cookieKeyVal = explode( '=', trim( $cookieKeyVal ) );
				if( count( $cookieKeyVal ) == 2 )
					$_COOKIE[ trim( $cookieKeyVal[ 0 ] ) ] = trim( $cookieKeyVal[ 1 ] );
			}
		}
	}
	unset( $hdrKey, $hdrVal );
}

unset( $_SERVER[ 'argv' ], $_SERVER[ 'argc' ], $_SERVER[ 'PATH_TRANSLATED' ] );

$aUrlParts = isset( $aOpts[ 'url' ] ) ? parse_url( _seraph_accel_re_Rtn::ExecUnMaskUrlArg( $aOpts[ 'url' ] ) ) : null;
if( !$aUrlParts || !isset( $aUrlParts[ 'scheme' ] ) || !isset( $aUrlParts[ 'host' ] ) )
{
	echo( 'E_INVALIDARG: url: "' . (isset($aOpts[ 'url' ])?$aOpts[ 'url' ]:'') . '"' );

	_seraph_accel_re_Rtn::IncludeGen();
	_seraph_accel_re_Rtn::End( \seraph_accel\Gen::E_INVALIDARG );
}

if( !isset( $aUrlParts[ 'path' ] ) )
	$aUrlParts[ 'path' ] = '';

$_SERVER[ 'REQUEST_TIME_FLOAT' ] = microtime( true );
$_SERVER[ 'REQUEST_TIME' ] = ( int )$_SERVER[ 'REQUEST_TIME_FLOAT' ];
$_SERVER[ 'REQUEST_METHOD' ] = isset( $aOpts[ 'method' ] ) ? $aOpts[ 'method' ] : 'GET';
$_SERVER[ 'REQUEST_SCHEME' ] = $aUrlParts[ 'scheme' ];
$_SERVER[ 'HTTPS' ] = $aUrlParts[ 'scheme' ] === 'https' ? 'on' : '';

{
	if( !isset( $aOpts[ 'soft' ] ) )
	{
		echo( 'E_INVALIDARG: soft' );

		_seraph_accel_re_Rtn::IncludeGen();
		_seraph_accel_re_Rtn::End( \seraph_accel\Gen::E_INVALIDARG );
	}

	$_SERVER[ 'SERVER_SOFTWARE' ] = ( strlen( $aOpts[ 'soft' ] ) ? rawurldecode( _seraph_accel_re_Rtn::ExecUnMaskUrlArg( $aOpts[ 'soft' ] ) ) : 'Generic' ) . ' (Seraph Accel Request Emulator 2.22.16)';
	$_SERVER[ 'SERVER_NAME' ] = $aUrlParts[ 'host' ];
	$_SERVER[ 'SERVER_ADDR' ] = '127.0.0.1';
	$_SERVER[ 'SERVER_PORT' ] = isset( $aUrlParts[ 'port' ] ) ? $aUrlParts[ 'port' ] : ( $_SERVER[ 'HTTPS' ] === 'on' ? 443 : 80 );
	$_SERVER[ 'SERVER_PROTOCOL' ] = 'HTTP/1.1';
}

$_SERVER[ 'HTTP_HOST' ] = $aUrlParts[ 'host' ];
if( $_SERVER[ 'HTTPS' ] === 'on' )
{
	if( $_SERVER[ 'SERVER_PORT' ] != 443 )
		$_SERVER[ 'HTTP_HOST' ] .= ':' . $_SERVER[ 'SERVER_PORT' ];
}
else
{
	if( $_SERVER[ 'SERVER_PORT' ] != 80 )
		$_SERVER[ 'HTTP_HOST' ] .= ':' . $_SERVER[ 'SERVER_PORT' ];
}

if( !isset( $_SERVER[ 'HTTP_USER_AGENT' ] ) )
	$_SERVER[ 'HTTP_USER_AGENT' ] = 'Seraph Accel Request Emulator 2.22.16';
if( !isset( $_SERVER[ 'HTTP_ACCEPT' ] ) )
	$_SERVER[ 'HTTP_ACCEPT' ] = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8;v=b3;q=0.7';

$_SERVER[ 'HTTP_ACCEPT_ENCODING' ] = 'identity';

$_SERVER[ 'REQUEST_URI' ] = $aUrlParts[ 'path' ] . ( isset( $aUrlParts[ 'query' ] ) ? ( '?' . $aUrlParts[ 'query' ] ) : '' );
$_SERVER[ 'QUERY_STRING' ] = isset( $aUrlParts[ 'query' ] ) ? $aUrlParts[ 'query' ] : '';

$_REQUEST = array(); parse_str( $_SERVER[ 'QUERY_STRING' ], $_REQUEST );
if( $_SERVER[ 'REQUEST_METHOD' ] == 'GET' )
{
	$_GET = $_REQUEST;
	$_POST = array();
}
else if( $_SERVER[ 'REQUEST_METHOD' ] == 'POST' )
{
	$_GET = array();
	$_POST = $_REQUEST;
}

$_SERVER[ 'REMOTE_ADDR' ] = '127.0.0.1';
$_SERVER[ 'REMOTE_PORT' ] = 65535;

$file = null;
foreach( array( $aUrlParts[ 'path' ], substr( $dirSiteRootCurScript, strlen( $_SERVER[ 'DOCUMENT_ROOT' ] ) ) ) as $pathSrch )
{
	$fileProbe = $_SERVER[ 'DOCUMENT_ROOT' ] . rtrim( $pathSrch, '/' );
	if( substr_compare( $fileProbe, '.php', -strlen( '.php' ), strlen( '.php' ), true ) !== 0 )
		$fileProbe .= '/index.php';

	if( strpos( $fileProbe, $dirSiteRootCurScript ) !== 0 )
	{
		echo( 'E_ACCESS_DENIED: ' . json_encode( array( 'fileProbe' => $fileProbe, 'dirSiteRootCurScript' => $dirSiteRootCurScript ) ) );

		_seraph_accel_re_Rtn::IncludeGen();
		_seraph_accel_re_Rtn::End( \seraph_accel\Gen::E_ACCESS_DENIED );
	}

	if( @file_exists( $fileProbe ) )
	{
		$file = $fileProbe;
		break;
	}
}

unset( $aOpts, $aUrlParts, $pathSrch );

if( $file === null )
{
	echo( 'E_NOT_FOUND' );

	_seraph_accel_re_Rtn::IncludeGen();
	_seraph_accel_re_Rtn::End( \seraph_accel\Gen::E_NOT_FOUND );
}

$_SERVER[ 'SCRIPT_FILENAME' ] = $file;
$_SERVER[ 'PHP_SELF' ] = $_SERVER[ 'SCRIPT_NAME' ] = substr( $file, strlen( $_SERVER[ 'DOCUMENT_ROOT' ] ) );

_seraph_accel_re_Rtn::RunInit();

include( $file );

