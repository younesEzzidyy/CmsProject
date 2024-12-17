<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

class ImgSrc
{
	public $src;
	public $srcInfo;
	public $mimeType;

	private $cont;
	private $info;

	function __construct( $src, $srcInfo = null )
	{
		$this -> src = $src;
		$this -> srcInfo = $srcInfo;

	}

	function dispose()
	{
		$this -> cont = null;
		$this -> info = null;
		$this -> srcInfo = null;
		$this -> src = null;
	}

	function Init( $ctxProcess, $requestDomainUrl = null, $requestUriPath = null )
	{
		if( !isset( $this -> srcInfo ) )
			$this -> srcInfo = Ui::IsSrcAttrData( $this -> src ) ? false : GetSrcAttrInfo( $ctxProcess, $requestDomainUrl, $requestUriPath, $this -> src );
	}

	function GetSize()
	{
		if( !isset( $this -> cont ) && $this -> srcInfo )
		{
			$file = (isset($this -> srcInfo[ 'filePath' ])?$this -> srcInfo[ 'filePath' ]:null);
			if( $file )
				return( Gen::FileSize( $file ) );
		}

		$this -> GetCont();
		return( $this -> cont !== false ? strlen( $this -> cont ) : false );
	}

	function GetCont()
	{
		if( !isset( $this -> cont ) )
		{
			if( $this -> srcInfo )
			{
				$file = (isset($this -> srcInfo[ 'filePath' ])?$this -> srcInfo[ 'filePath' ]:null);
				if( $file )
				{
					$this -> cont = @file_get_contents( $file );

				}
				if( !(isset($this -> cont)?$this -> cont:null) )
				{
					$this -> cont = GetExtContents( $this -> srcInfo[ 'url' ], $this -> mimeType, true, 10 );

				}
			}
			else
			{
				$this -> cont = Ui::GetSrcAttrData( $this -> src, $this -> mimeType );
				if( $this -> mimeType == 'image/jpg' )
					$this -> mimeType = 'image/jpeg';
			}

			if( !$this -> mimeType )
			{
				$this -> GetInfo();
				if( $this -> info )
					$this -> mimeType = $this -> info[ 'mime' ];
			}
		}

		return( $this -> cont );
	}

	function GetInfo()
	{
		$this -> GetCont();

		if( !isset( $this -> info ) )
		{
			$this -> info = Img::GetInfoFromData( $this -> cont );
			if( $this -> info === null )
				$this -> info = false;
		}

		return( $this -> info );
	}

	function GetDisplayFile()
	{
		if( $this -> srcInfo )
		{
			if( isset( $this -> srcInfo[ 'filePath' ] ) )
				return( $this -> srcInfo[ 'filePath' ] );
			return( $this -> srcInfo[ 'url' ] );
		}

		$this -> GetInfo();

		if( $this -> info )
			return( substr( _Images_ProcessSrc_InlineEx( $this -> info[ 'mime' ], ( string )$this -> GetCont() ), 0, 200 ) );

		return( '' );
	}

}

class ImgSzAlternatives
{
	public $a;
	public $isImportant;
	public $info;

	function __construct()
	{
		$this -> a = array();
	}

	function isEmpty()
	{
		return( count( $this -> a ) <= 1 );
	}
}

function _Images_ProcessSrc_CopyImageToHost( &$ctxProcess, $imgSrc, $imgCont, $settCache, $settImg )
{
	$type = Fs::GetFileTypeFromMimeContentType( $imgSrc -> mimeType );
	if( !$type )
		return( null );

	if( !UpdSc( $ctxProcess, $settCache, array( 'img', $type ), $imgCont, $imgSrc -> src, $file ) )
		return( false );

	if( !is_array( $imgSrc -> srcInfo ) )
		$imgSrc -> srcInfo = array();

	if( Gen::GetArrField( $settImg, array( 'redirOwn' ), false ) && in_array( $type, array( 'jpe','jpg','jpeg','png','gif','bmp', 'webp','avif' ) ) )
	{
		$imgSrc -> srcInfo[ 'srcWoArgs' ] = $ctxProcess[ 'siteRootUri' ] . '/';
		$imgSrc -> srcInfo[ 'args' ] = Image_MakeOwnRedirUrlArgs( substr( $file, strlen( $ctxProcess[ 'siteRootPath' ] ) + 1 ) );
		$imgSrc -> src = Net::UrlDeParse( array( 'path' => $imgSrc -> srcInfo[ 'srcWoArgs' ], 'query' => $imgSrc -> srcInfo[ 'args' ] ), Net::URLPARSE_F_PRESERVEEMPTIES );
	}
	else
	{
		$imgSrc -> srcInfo[ 'srcWoArgs' ] = $imgSrc -> src;
		$imgSrc -> srcInfo[ 'args' ] = array();
	}

	$imgSrc -> srcInfo[ 'srcUrlFullness' ] = 2;
	$imgSrc -> srcInfo[ 'url' ] = $imgSrc -> src;
	$imgSrc -> srcInfo[ 'ext' ] = false;
	$imgSrc -> srcInfo[ '#' ] = null;

	_Images_ProcessSrc_ConvertAll( $settImg, $imgCont, $file, _Images_ProcessSrcEx_FileMTime( $file ) );
	return( true );
}

function _Images_ProcessSrc_DeInlineLarge( &$ctxProcess, $imgSrc, $settCache, $settImg )
{
	if( !Gen::GetArrField( $settImg, array( 'deinlLrg' ), false ) )
		return( null );

	$imgCont = $imgSrc -> GetCont();
	if( $imgCont === false )
		return( null );

	if( strlen( $imgCont ) < Gen::GetArrField( $settImg, array( 'deinlLrgSize' ), 0 ) )
		return( null );
	return( _Images_ProcessSrc_CopyImageToHost( $ctxProcess, $imgSrc, $imgCont, $settCache, $settImg ) );
}

function _Images_ProcessSrc_InlineEx( $mimeType, $imgCont )
{

	return( Ui::SetSrcAttrData( $imgCont, $mimeType ) );
}

function _Images_ProcessSrc_InlineSmall( $imgSrc, $settImg )
{
	if( !Gen::GetArrField( $settImg, array( 'inlSml' ), false ) )
		return( false );

	$fileSize = $imgSrc -> GetSize();
	if( $fileSize === false )
		return( false );

	if( !$fileSize || $fileSize > Gen::GetArrField( $settImg, array( 'inlSmlSize' ), 0 ) )
		return( false );

	$imgCont = $imgSrc -> GetCont();
	if( $imgCont === false )
		return( false );

	if( !$imgSrc -> mimeType )
		return( false );

	$imgSrc -> src = _Images_ProcessSrc_InlineEx( $imgSrc -> mimeType, $imgCont );
	return( true );
}

function _FileWriteTmpAndReplace( $file, $fileTime, $data = null, $fileTmp = null )
{

	if( $fileTmp === null )
		$fileTmp = $file . '.tmp';

	$lock = new Lock( $fileTmp . '.l', false, true );
	if( $lock -> Acquire() )
	{
		if( $data === null || @file_put_contents( $fileTmp, $data ) )
		{
			if( @touch( $fileTmp, $fileTime ) )
			{

				if( @rename( $fileTmp, $file ) )
				{
					$lock -> Release();
					return( true );
				}
				else
					Gen::LastErrDsc_Set( LocId::Pack( 'FileRenameErr_%1$s%2$s', 'Common', array( $fileTmp, $file ) ) );
			}
			else
				Gen::LastErrDsc_Set( LocId::Pack( 'FileWriteErr_%1$s', 'Common', array( $fileTmp ) ) );
		}
		else
			Gen::LastErrDsc_Set( LocId::Pack( 'FileWriteErr_%1$s', 'Common', array( $fileTmp ) ) );

		$lock -> Release();
	}
	else
		Gen::LastErrDsc_Set( $lock -> GetErrDescr() );

	@unlink( $fileTmp );
	@unlink( $file );

	return( false );
}

function _Images_ProcessSrc_ConvertEx( $type, $typeIdx, $settImg, $data, $file, $fileCnv, $fileTime, $fileTimeCnv, $aTypeFrom, &$sizeCheck )
{
	global $seraph_accel_g_prepPrms;

	$fileCnvStat = $fileCnv . '.json';
	$fileTimeCnvStat = @filemtime( $fileCnvStat );

	$fileExt = Gen::GetFileExt( $file );

	if( !Gen::GetArrField( $settImg, array( $type, 'enable' ), false ) || !in_array( $fileExt, $aTypeFrom ) )
		return;

	if( $fileTimeCnvStat !== false && $fileTime <= $fileTimeCnvStat )
		return;

	if( $fileTimeCnv !== false && $fileTime <= $fileTimeCnv )
	{
		$sizeCheck = Gen::FileSize( $fileCnv );
		if( $sizeCheck === false )
			LastWarnDscs_Add( LocId::Pack( 'ImgConvertFileErr_%1$s%2$s%3$s', null, array( $file, $type, Gen::GetLocPackFileReadErr( $fileCnv ) ) ) );
		return;
	}

	if( $data === null )
		$data = @file_get_contents( $file );
	else if( @is_a( $data, 'seraph_accel\\ImgSrc' ) )
		$data = $data -> GetCont();

	$lock = new Lock( $fileCnv . '.l', false, true );
	if( !$lock -> Acquire() )
	{
		LastWarnDscs_Add( LocId::Pack( 'ImgConvertFileErr_%1$s%2$s%3$s', null, array( $file, $type, $lock -> GetErrDescr() ) ) );
		return;
	}

	$status = null;
	if( ( $fileExt == 'png' && Img::IsDataPngAnimated( $data ) ) || ( $fileExt == 'gif' && Img::IsDataGifAnimated( $data ) ) )
		$status = 'aniNotSupp';

	$hr = Gen::S_FALSE;
	$fileCnvTmp = $fileCnv . '.tmp';
	if( !$status )
	{
		@unlink( $fileCnvTmp );

		if( $seraph_accel_g_prepPrms )
			ProcessCtlData_Update( (isset($seraph_accel_g_prepPrms[ 'pc' ])?$seraph_accel_g_prepPrms[ 'pc' ]:null), array( 'stageDsc' => LocId::Pack( 'ImgConvertFile_%1$s%2$s', null, array( $file, $type ) ) ) );

		$hr = Img::ConvertDataEx( $dataCnvRes, $data, 'image/' . $type, Gen::GetArrField( $settImg, array( $type, 'prms' ), array() ), $fileCnvTmp );

		if( $seraph_accel_g_prepPrms )
			ProcessCtlData_Update( (isset($seraph_accel_g_prepPrms[ 'pc' ])?$seraph_accel_g_prepPrms[ 'pc' ]:null), array( 'stageDsc' => null ) );
	}

	$fileTime += $typeIdx + 1;

	if( $hr == Gen::S_OK )
	{
		if( $sizeCheck === false )
			$sizeCheck = strlen( $data );

		$sizeCnv = Gen::FileSize( $fileCnvTmp );
		if( $sizeCnv !== false )
		{
			if( $sizeCnv < $sizeCheck )
			{
				if( _FileWriteTmpAndReplace( $fileCnv, $fileTime, null, $fileCnvTmp ) )
					$sizeCheck = $sizeCnv;
				@unlink( $fileCnvStat );
			}
			else
				$status = array( 'larger' => $sizeCnv );
		}
		else
			Gen::LastErrDsc_Set( Gen::GetLocPackFileReadErr( $fileCnvTmp ) );
	}
	else if( $hr == Gen::E_UNSUPPORTED )
		Gen::LastErrDsc_Set( LocId::Pack( 'ImgConvertUnsupp' ) );

	if( $hr != Gen::S_OK || $status )
	{
		@unlink( $fileCnv );
		if( $status )
			_FileWriteTmpAndReplace( $fileCnvStat, $fileTime, @json_encode( $status ) );
		else
			@unlink( $fileCnvStat );
	}

	@unlink( $fileCnvTmp );

	$lock -> Release();

	if( !Gen::LastErrDsc_Is() )
		return;

	LastWarnDscs_Add( LocId::Pack( 'ImgConvertFileErr_%1$s%2$s%3$s', null, array( $file, $type, Gen::LastErrDsc_Get() ) ) );
	Gen::LastErrDsc_Set( null );
}

function _Images_ProcessSrc_ConvertAll( $settImg, $imgSrcOrCont, $file, $fileTime )
{
	$sizeCheck = false;
	$aTypeFrom = array( 'jpe','jpg','jpeg','png','gif','bmp' );
	foreach( array( 'webp','avif' ) as $typeIdx => $type )
	{
		$fileCnv = $file . '.' . $type;

		$fileTimeCnv = _Images_ProcessSrcEx_FileMTime( $fileCnv );
		_Images_ProcessSrc_ConvertEx( $type, $typeIdx, $settImg, $imgSrcOrCont, $file, $fileCnv, $fileTime, $fileTimeCnv, $aTypeFrom, $sizeCheck );

		$aTypeFrom[] = $type;
	}
}

function _Images_ProcessSrcEx_FileMTime( $file )
{
	return( Gen::FileSize( $file ) ? @filemtime( $file ) : false );
}

function Images_ProcessSrcEx( &$ctxProcess, $imgSrc, $settCache, $settImg )
{
	$args = $imgSrc -> srcInfo[ 'args' ];

	$file = (isset($imgSrc -> srcInfo[ 'filePath' ])?$imgSrc -> srcInfo[ 'filePath' ]:null);

	if( !$file )
	{

		$cache = false;
		$cacheCrit = false;
		foreach( Gen::GetArrField( $settImg, array( 'cacheExt' ), array() ) as $srcPattern )
		{
			$cacheCritTmp = false;
			if( Gen::StrStartsWith( $srcPattern, 'crit:' ) )
			{
				$srcPattern = substr( $srcPattern, 5 );
				$cacheCritTmp = true;
			}

			if( !@preg_match( $srcPattern, $imgSrc -> src ) )
				continue;

			$cache = true;
			$cacheCrit = $cacheCritTmp;
			break;
		}

		if( !$cache )
			return( null );

		$imgCont = $imgSrc -> GetCont();

		if( $imgCont === false )
		{
			$sErrTxt = LocId::Pack( 'CacheExtImgErr_%1$s', null, array( LocId::Pack( 'NetDownloadErr_%1$s', 'Common', array( $imgSrc -> src ) ) ) );
			if( $cacheCrit )
			{
				Gen::LastErrDsc_Set( $sErrTxt );
				return( false );
			}

			if( (isset($ctxProcess[ 'debugM' ])?$ctxProcess[ 'debugM' ]:null) )
				LastWarnDscs_Add( $sErrTxt );
			return( null );
		}

		if( !$imgSrc -> mimeType )
		{
			$sErrTxt = LocId::Pack( 'CacheExtImgErr_%1$s', null, array( LocId::Pack( 'NetMimeErr_%1$s', 'Common', array( $imgSrc -> src ) ) ) );
			if( $cacheCrit )
			{
				Gen::LastErrDsc_Set( $sErrTxt );
				return( false );
			}

			if( (isset($ctxProcess[ 'debugM' ])?$ctxProcess[ 'debugM' ]:null) )
				LastWarnDscs_Add( $sErrTxt );
			return( null );
		}

		if( Gen::GetArrField( $settImg, array( 'inlSml' ), false ) && strlen( $imgCont ) <= Gen::GetArrField( $settImg, array( 'inlSmlSize' ), 0 ) )
			$imgSrc -> src = _Images_ProcessSrc_InlineEx( $imgSrc -> mimeType, $imgCont );
		else
		{
			$r = _Images_ProcessSrc_CopyImageToHost( $ctxProcess, $imgSrc, $imgCont, $settCache, $settImg );
			if( !$r )
				return( $r );
		}

		return( true );
	}

	$fileTime = _Images_ProcessSrcEx_FileMTime( $file );
	if( !$fileTime )
	{
		if( (isset($ctxProcess[ 'debugM' ])?$ctxProcess[ 'debugM' ]:null) )
			LastWarnDscs_Add( Gen::GetLocPackFileReadErr( $file ) );
		return( null );
	}

	if( ( (isset($ctxProcess[ 'compatView' ])?$ctxProcess[ 'compatView' ]:null) !== 'cm' ) && _Images_ProcessSrc_InlineSmall( $imgSrc, $settImg ) )
		return( true );

	_Images_ProcessSrc_ConvertAll( $settImg, $imgSrc, $file, $fileTime );

	foreach( array( 'webp','avif' ) as $typeCnv )
	{
		if( !( Gen::GetArrField( $settImg, array( $typeCnv, 'redir' ), false ) ) )
			continue;

		$srcRealCnvFile = $file . '.' . $typeCnv;
		$fileTimeCnv = _Images_ProcessSrcEx_FileMTime( $srcRealCnvFile );
		if( $fileTimeCnv !== false && $fileTimeCnv > $fileTime )
			$fileTime = $fileTimeCnv;
	}

	$argsAdjusted = false;

	if( Gen::GetArrField( $settImg, array( 'redirOwn' ), false ) && in_array( strtolower( Gen::GetFileExt( $file ) ), array( 'jpe','jpg','jpeg','png','gif','bmp', 'webp','avif' ) ) )
	{
		$imgSrc -> srcInfo[ 'srcWoArgs' ] = $ctxProcess[ 'siteRootUri' ] . '/';
		$imgSrc -> srcInfo[ 'srcUrlFullness' ] = 2;
		$args = Image_MakeOwnRedirUrlArgs( substr( $file, strlen( $ctxProcess[ 'siteRootPath' ] ) + 1 ) );
		$argsAdjusted = true;
	}

	if( Gen::GetArrField( $settImg, array( 'srcAddLm' ), false ) )
	{
		$args[ 'lm' ] = sprintf( '%X', $fileTime );
		$argsAdjusted = true;
	}

	if( $argsAdjusted )
	{
		$imgSrc -> src = Net::UrlDeParse( array( 'path' => $imgSrc -> srcInfo[ 'srcWoArgs' ], 'query' => $args, 'fragment' => (isset($imgSrc -> srcInfo[ '#' ])?$imgSrc -> srcInfo[ '#' ]:null) ), Net::URLPARSE_F_PRESERVEEMPTIES );

	}

	return( true );
}

function Images_ProcessSrcSizeAlternatives( $imgSzAlternatives, &$ctxProcess, $imgSrc, $settCache, $settImg, $settCdn, $bCrop = false, $isImportant = false )
{
	global $seraph_accel_g_prepPrms;

	$imgSrc -> Init( $ctxProcess );

	$info = $imgSrc -> GetInfo();

	if( !$info || !Img::IsMimeRaster( $info[ 'mime' ] ) )
		return( null );

	$fileType = Gen::GetFileName( $info[ 'mime' ] );

	$idCache = md5( ( string )$imgSrc -> GetCont() . ( Gen::GetArrField( $settImg, array( 'inlSml' ), false ) ? ( string )Gen::GetArrField( $settImg, array( 'inlSmlSize' ), 0 ) : '' ), true );
	$infoCache = Images_ProcessSrcSizeAlternatives_Cache_Get( $ctxProcess[ 'dataPath' ], $idCache );

	if( $infoCache === false )
		return( null );

	$img = null;
	$infoCache = ( array )$infoCache;

	static $g_aAdaptDimsScale = array( 1.0, 	1.01,	1.01,	1.03,	1.05,	1.0,	1.0,	1.0	 );

	$szAdaptBgCxMin = Gen::GetArrField( $settImg, array( 'szAdaptBgCxMin' ), 0 );
	foreach( array( $info[ 'cx' ], 				1920,	1366,	992,	768,	480,	360,	120	 ) as $i => $cx )
	{
		if( $infoCache === false )
			break;

		$scale = ( float )$info[ 'cx' ] / $cx;
		if( $scale < $g_aAdaptDimsScale[ $i ] || !( $szAdaptBgCxMin <= $cx ) )
			continue;

		$cy = ( int )round( ( float )$info[ 'cy' ] * ( $cx / $info[ 'cx' ] ) );

		$imgScaled = null;
		foreach( ( $bCrop ? array( $cx, 	1920,	1366,	992,	768,	480,	360				 ) : array( $cx ) ) as $cxCrop )
		{
			if( $cxCrop > $cx || $szAdaptBgCxMin > $cx )
				continue;

			if( $info[ 'cx' ] == $cxCrop )
				continue;

			$idAi = ( $info[ 'cx' ] != $cx ? ( string )$cx : 'O' ) . ( $cxCrop != $cx ? ( '@' . $cxCrop ) : '' );
			$imgSrcAlter = null;
			$file = null;

			if( $infoCacheVal = (isset($infoCache[ $idAi ])?$infoCache[ $idAi ]:null) )
			{
				if( is_array( $infoCacheVal ) )
					$imgSrcAlter = _Images_ProcessSrc_InlineEx( ( string )(isset($infoCacheVal[ 't' ])?$infoCacheVal[ 't' ]:null), ( string )(isset($infoCacheVal[ 'd' ])?$infoCacheVal[ 'd' ]:null) );
				else if( !CheckSc( $ctxProcess, $settCache, array( 'img', $fileType ), ( string )$infoCacheVal, $imgSrcAlter, $file ) )
					$infoCacheVal = null;
			}

			if( !$infoCacheVal )
			{
				if( $img === null )
				{
					if( $seraph_accel_g_prepPrms )
						ProcessCtlData_Update( (isset($seraph_accel_g_prepPrms[ 'pc' ])?$seraph_accel_g_prepPrms[ 'pc' ]:null), array( 'stageDsc' => LocId::Pack( 'ImgAdaptFile_%1$s', null, array( $imgSrc -> GetDisplayFile() ) ) ) );

					$data = $imgSrc -> GetCont();
					if( ( $info[ 'mime' ] == 'image/png' && Img::IsDataPngAnimated( $data ) ) || ( $info[ 'mime' ] == 'image/gif' && Img::IsDataGifAnimated( $data ) ) )
					{
						$infoCache = false;
						break;
					}

					$img = Img::CreateFromData( $data );
					unset( $data );

					if( !$img )
					{

						return( null );
					}

					$imgScaled = $img;
				}
				else if( !$imgScaled )
					$imgScaled = $img;

				if( $cx === $cxCrop )
				{

					$imgNew = Img::CreateCopyResample( $img,
						array( 'cx' => $cx, 'cy' => $cy ),
						array( 'x' => 0, 'y' => 0, 'cx' => $info[ 'cx' ], 'cy' => $info[ 'cy' ] ) );
					if( !$imgNew )
					{

						continue;
					}

					$imgScaled = $imgNew;
				}
				else
				{

					$imgNew = Img::CreateCopyResample( $imgScaled,
						array( 'cx' => $cxCrop, 'cy' => $cy ),
						array( 'x' => ( $cx - $cxCrop ) / 2, 'y' => 0, 'cx' => $cxCrop, 'cy' => $cy ) );
					if( !$imgNew )
					{

						continue;
					}
				}

				$imgNewCont = Img::GetData( $imgNew, $info[ 'mime' ] );
				if( $imgNew !== $imgScaled )
					imagedestroy( $imgNew );
				if( !$imgNewCont )
				{

					continue;
				}

				$infoCacheVal = array();
				{
					if( Gen::GetArrField( $settImg, array( 'inlSml' ), false ) && $info[ 'mime' ] && strlen( $imgNewCont ) <= Gen::GetArrField( $settImg, array( 'inlSmlSize' ), 0 ) )
					{
						$imgSrcAlter = _Images_ProcessSrc_InlineEx( $info[ 'mime' ], $imgNewCont );

						$infoCacheVal = array( 't' => $info[ 'mime' ], 'd' => $imgNewCont );
					}
					else
					{
						$oiCi = UpdSc( $ctxProcess, $settCache, array( 'img', $fileType ), $imgNewCont, $imgSrcAlter, $file );
						if( !$oiCi )
							return( false );

						$infoCacheVal = $oiCi;
					}
				}

				$infoCache[ $idAi ] = $infoCacheVal;
			}

			unset( $infoCacheVal );

			if( $file )
			{
				_Images_ProcessSrc_ConvertAll( $settImg, null, $file, _Images_ProcessSrcEx_FileMTime( $file ) );

				if( Gen::GetArrField( $settImg, array( 'redirOwn' ), false ) && in_array( $fileType, array( 'jpe','jpg','jpeg','png','gif','bmp', 'webp','avif' ) ) )
					$imgSrcAlter = Net::UrlDeParse( array( 'path' => $ctxProcess[ 'siteRootUri' ] . '/', 'query' => Image_MakeOwnRedirUrlArgs( substr( $file, strlen( $ctxProcess[ 'siteRootPath' ] ) + 1 ) ) ), Net::URLPARSE_F_PRESERVEEMPTIES );

				Cdn_AdjustUrl( $ctxProcess, $settCdn, $imgSrcAlter, $fileType );
				Fullness_AdjustUrl( $ctxProcess, $imgSrcAlter );
			}

			$imgSzAlternatives -> a[ $idAi ] = array( 'img' => $imgSrcAlter, 'sz' => array( $cx, $cy ) );
		}

		if( $imgScaled && $imgScaled !== $img )
			imagedestroy( $imgScaled );
	}

	if( $img )
	{
		imagedestroy( $img );

		if( $seraph_accel_g_prepPrms )
			ProcessCtlData_Update( (isset($seraph_accel_g_prepPrms[ 'pc' ])?$seraph_accel_g_prepPrms[ 'pc' ]:null), array( 'stageDsc' => null ) );
	}

	$imgSzAlternatives -> a[ '0' ] = array( 'img' => null );
	$imgSzAlternatives -> isImportant = $isImportant;
	$imgSzAlternatives -> info = $info;

	Images_ProcessSrcSizeAlternatives_Cache_Set( $ctxProcess[ 'dataPath' ], $idCache, $infoCache );
	return( $infoCache === false ? null : true );
}

function Images_ProcessSrc( &$ctxProcess, $imgSrc, $settCache, $settImg, $settCdn )
{
	if( !$imgSrc -> src )
		return( null );

	$adjusted = null;

	$imgSrc -> Init( $ctxProcess );
	if( !$imgSrc -> srcInfo )
	{
		$r = _Images_ProcessSrc_DeInlineLarge( $ctxProcess, $imgSrc, $settCache, $settImg );
		if( !$r )
			return( $r );

		$adjusted = true;
	}

	$fileType = strtolower( Gen::GetFileExt( (isset($imgSrc -> srcInfo[ 'srcWoArgs' ])?$imgSrc -> srcInfo[ 'srcWoArgs' ]:null) ) );

	if( $adjusted === null )
	{
		$adjusted = Images_ProcessSrcEx( $ctxProcess, $imgSrc, $settCache, $settImg );
		if( $adjusted === false )
			return( false );
	}

	if( Cdn_AdjustUrl( $ctxProcess, $settCdn, $imgSrc -> src, $fileType ) )
		$adjusted = true;
	if( Fullness_AdjustUrl( $ctxProcess, $imgSrc -> src, (isset($imgSrc -> srcInfo[ 'srcUrlFullness' ])?$imgSrc -> srcInfo[ 'srcUrlFullness' ]:null) ) )
		$adjusted = true;

	return( $adjusted );
}

function Images_ProcessSrcSet( &$ctxProcess, &$srcset, $settCache, $settImg, $settCdn )
{
	$apply = false;

	$srcItems = Ui::ParseSrcSetAttr( $srcset );
	foreach( $srcItems as &$srcItem )
	{
		$imgSrc = new ImgSrc( html_entity_decode( $srcItem[ 0 ] ) );

		$r = Images_ProcessSrc( $ctxProcess, $imgSrc, $settCache, $settImg, $settCdn );
		if( $r === false )
			return( false );

		if( $r )
		{
			$srcItem[ 0 ] = $imgSrc -> src;
			$apply = true;
		}
	}

	if( !$apply )
		return( null );

	$srcset = Ui::GetSrcSetAttr( $srcItems, false );
	return( true );
}

function LazyLoad_SvgSubst( $width, $height, $exact = false, $fill = null )
{
	return( Ui::Tag( 'svg', $fill ? Ui::TagOpen( 'rect', array( 'width' => '100%', 'height' => '100%', 'fill' => is_array( $fill ) && count( $fill ) >= 3 ? ( count( $fill ) > 3 ? sprintf( '#%02X%02X%02X%02X', $fill[ 0 ], $fill[ 1 ], $fill[ 2 ], $fill[ 3 ] ) : sprintf( '#%02X%02X%02X', $fill[ 0 ], $fill[ 1 ], $fill[ 2 ] ) ) : null ), true ) : null
		, array_merge( array( 'xmlns' => 'http://www.w3.org/2000/svg', 'viewBox' => '0 0 ' . $width . ' ' . $height ), $exact ? array( 'width' => $width, 'height' => $height ) : array() ) ) );
}

function LazyLoad_SrcSubst( $ctxProcess, $info, $exact = false, $fill = null )
{
	if( !$info )
		$info = array();
	if( !(isset($info[ 'cx' ])?$info[ 'cx' ]:null) )
		$info[ 'cx' ] = 225;
	if( !(isset($info[ 'cy' ])?$info[ 'cy' ]:null) )
		$info[ 'cy' ] = $info[ 'cx' ] / 3 * 2;

	if( !$exact )
		return( 'data:image/svg+xml,' . rawurlencode( LazyLoad_SvgSubst( $info[ 'cx' ], $info[ 'cy' ], $exact, $fill ) ) );

	$wh = $info[ 'cx' ] + $info[ 'cy' ];

	if( !Img::IsMimeRaster( (isset($info[ 'mime' ])?$info[ 'mime' ]:null) ) || $wh > 10000 || !@function_exists( 'imagecreatetruecolor' ) )
	{

		{
			if( !is_array( $fill ) || count( $fill ) < 3 )
				$fill = array( 0, 0, 0, 0 );
			else if( count( $fill ) == 3 )
				$fill[] = 0;

			foreach( $fill as $i => $c )
			{
				if( $c < 0 )
					$fill[ $i ] = 0;
				else if( $c > 0xFF )
					$fill[ $i ] = 0xFF;
			}

		}

		return( 'data:image/svg+xml,' . rawurlencode( Ui::Tag( 'svg', ( $fill ? Ui::TagOpen( 'rect', array( 'width' => '100%', 'height' => '100%', 'fill' => is_array( $fill ) && count( $fill ) >= 3 ? ( count( $fill ) > 3 ? sprintf( '#%02X%02X%02X%02X', $fill[ 0 ], $fill[ 1 ], $fill[ 2 ], $fill[ 3 ] ) : sprintf( '#%02X%02X%02X', $fill[ 0 ], $fill[ 1 ], $fill[ 2 ] ) ) : null ), true ) : '' )
			, array_merge( array( 'xmlns' => 'http://www.w3.org/2000/svg', 'viewBox' => isset( $info[ 'viewBox' ] ) ? $info[ 'viewBox' ] : ( '0 0 ' . $info[ 'cx' ] . ' ' . $info[ 'cy' ] ) ), array( 'width' => isset( $info[ 'width' ] ) ? $info[ 'width' ] : $info[ 'cx' ], 'height' => isset( $info[ 'height' ] ) ? $info[ 'height' ] : $info[ 'cy' ] ) ) ) ) );
	}

	if( !is_array( $fill ) || count( $fill ) < 3 )
		$fill = array( 0, 0, 0, 0 );
	else if( count( $fill ) == 3 )
		$fill[] = 0;

	foreach( $fill as $i => $c )
	{
		if( $c < 0 )
			$fill[ $i ] = 0;
		else if( $c > 0xFF )
			$fill[ $i ] = 0xFF;
	}

	$idCache = md5( 'LazyLoad_SrcSubst:' . sprintf( '%ux%u#%02X%02X%02X%02X', $info[ 'cx' ], $info[ 'cy' ], ( int )$fill[ 0 ], ( int )$fill[ 1 ], ( int )$fill[ 2 ], ( int )$fill[ 3 ] ), true );
	$imgNewCont = Images_ProcessSrcSizeAlternatives_Cache_Get( $ctxProcess[ 'dataPath' ], $idCache );

	if( !$imgNewCont )
	{
		$hNew = @imagecreatetruecolor( $info[ 'cx' ], $info[ 'cy' ] );

		$hClr = @imagecolorallocatealpha( $hNew, ( int )$fill[ 0 ], ( int )$fill[ 1 ], ( int )$fill[ 2 ], ( int )( 127 - $fill[ 3 ] / 2 ) );
		@imagefill( $hNew, 0, 0, $hClr );
		@imagecolordeallocate( $hNew, $hClr );
		if( $fill[ 3 ] !== 0xFF )
			@imagesavealpha( $hNew, true );

		$imgNewCont = Img::GetData( $hNew, 'image/png', array( 'c' => $wh > 1000 ? Img::PNG_COMPRESSION_HIGH : Img::PNG_COMPRESSION_LOW, 'q' => $wh > 4000 ? 1 : 100, 's' => $wh > 8000 ? Img::PNG_SPEED_LOW : Img::PNG_SPEED_HIGH ) );

		@imagedestroy( $hNew );
	}

	Images_ProcessSrcSizeAlternatives_Cache_Set( $ctxProcess[ 'dataPath' ], $idCache, $imgNewCont );

	return( _Images_ProcessSrc_InlineEx( 'image/png', $imgNewCont ) );

}

function _Images_ProcessItemLazy_Start( &$ctxProcess, $doc, $settImg, $item )
{
	if( $ctxProcess[ 'isAMP' ] || !Gen::GetArrField( $settImg, array( 'lazy', 'load' ), false ) )
		return( null );

	if( HtmlNd::FindUpByTag( $item, 'noscript' ) )
		return( null );

	if( Images_CheckLazyExcl( $ctxProcess, $doc, $settImg, $item ) )
		return( null );

	if( Gen::GetArrField( $settImg, array( 'lazy', 'del3rd' ), false ) )
	{

		HtmlNd::AddRemoveAttrClass( $item, array(), array( 'lazyload', 'blog-thumb-lazy-load', 'lazy-load', 'lazy', 'mfn-lazy', 'iso-lazy-load', 'll-image', 'wd-lazy-load', 'wd-lazy-fade', 'houzez-lazyload', 'pk-lazyload' ) );

		if( $item -> hasAttribute( 'data-src' ) )
		{
			if( strlen( ( string )$item -> getAttribute( 'data-src' ) ) )
				HtmlNd::RenameAttr( $item, 'data-src', 'src' );
			else
				$item -> removeAttribute( 'data-src' );
		}
		if( $item -> hasAttribute( 'data-srcset' ) )
		{
			if( strlen( ( string )$item -> getAttribute( 'data-srcset' ) ) )
				HtmlNd::RenameAttr( $item, 'data-srcset', 'srcset' );
			else
				$item -> removeAttribute( 'data-srcset' );
		}

		HtmlNd::RenameAttr( $item, 'data-orig-src', 'src' );
		HtmlNd::RenameAttr( $item, 'data-orig-srcset', 'srcset' );

		HtmlNd::RenameAttr( $item, 'data-lazy-src', 'src' );
		HtmlNd::RenameAttr( $item, 'data-lazy-srcset', 'srcset' );

		HtmlNd::RenameAttr( $item, 'data-wood-src', 'src' );

		HtmlNd::RenameAttr( $item, 'data-pk-src', 'src' );
		HtmlNd::RenameAttr( $item, 'data-pk-srcset', 'srcset' );
		HtmlNd::RenameAttr( $item, 'data-ls-sizes', 'sizes' );
		$item -> removeAttribute( 'data-pk-sizes' );

		if( HtmlNd::GetAttr( $item, 'srcset' ) === null )
			$item -> removeAttribute( 'srcset' );
	}

	return( true );
}

function _Images_ProcessItemLazy_Finish( &$ctxProcess, $doc, $settImg, $item, $imgSrc )
{
	$src = $item -> getAttribute( 'src' );
	if( !$src )
		return( null );
	if( !$item -> getAttribute( 'srcset' ) && Ui::IsSrcAttrData( $src ) )
		return( null );

	if( Gen::GetArrField( $settImg, array( 'lazy', 'own' ), false ) )
	{

		{
			$itemCopy = $item -> cloneNode( true );
			if( !$itemCopy )
				return( false );

			$itemNoScript = $doc -> createElement( 'noscript' );
			if( !$itemNoScript )
				return( false );

			$itemNoScript -> setAttribute( 'lzl', '' );
			$itemNoScript -> appendChild( $itemCopy );
			HtmlNd::InsertAfter( $item -> parentNode, $itemNoScript, $item );
		}

		$ctxProcess[ 'lazyload' ] = true;
		HtmlNd::AddRemoveAttrClass( $item, array( 'lzl' ) );

		HtmlNd::RenameAttr( $item, 'srcset', 'data-lzl-srcset' );
		HtmlNd::RenameAttr( $item, 'sizes', 'data-lzl-sizes' );

		$item -> setAttribute( 'data-lzl-src', $src );

		$item -> setAttribute( 'src', LazyLoad_SrcSubst( $ctxProcess, $imgSrc ? $imgSrc -> GetInfo() : null, true ) );

	}
	else
	{
		$item -> setAttribute( 'loading', 'lazy' );
	}

	{
		for( $p = $item -> parentNode; $p && $p -> nodeType == XML_ELEMENT_NODE; $p = $p -> parentNode )
		{
			if( !in_array( 'woocommerce-product-gallery', Ui::ParseClassAttr( $p -> getAttribute( 'class' ) ) ) )
				continue;

			$styles = Ui::ParseStyleAttr( $p -> getAttribute( 'style' ) );
			$styles[ 'opacity' ] = 1;

			$p -> setAttribute( 'style', Ui::GetStyleAttr( $styles ) );
			break;
		}
	}

	return( true );
}

function Images_ProcessItemLazyBg( &$ctxProcess, $doc, $settImg, $item, $imgSrc )
{
	if( HtmlNd::FindUpByTag( $item, 'noscript' ) )
		return( false );

	if( $item -> hasAttribute( 'data-bg' ) )
		return( false );

	if( Images_CheckLazyExcl( $ctxProcess, $doc, $settImg, $item ) )
		return( false );

	$ctxProcess[ 'lazyload' ] = true;
	HtmlNd::AddRemoveAttrClass( $item, array( 'lzl' ) );

	$item -> setAttribute( 'data-lzl-bg', $imgSrc -> src );

	$imgSrc -> src = LazyLoad_SrcSubst( $ctxProcess, $imgSrc -> GetInfo(), true );

	return( true );
}

function Images_CheckExclEx( &$ctxProcess, $doc, $settImg, $item, $id1, $settPath )
{
	$exclItems = &$ctxProcess[ $id1 ];
	if( $exclItems === null )
	{
		$exclItems = array();

		$excls = Gen::GetArrField( $settImg, $settPath, array() );
		if( $excls )
		{
			$xpath = new \DOMXPath( $doc );

			foreach( $excls as $exclItemPath )
				foreach( HtmlNd::ChildrenAsArr( @$xpath -> query( $exclItemPath, $ctxProcess[ 'ndHtml' ] ) ) as $itemExcl )
					$exclItems[] = $itemExcl;
		}
	}

	return( in_array( $item, $exclItems, true ) );
}

function Images_CheckExcl( &$ctxProcess, $doc, $settImg, $item )
{
	return( Images_CheckExclEx( $ctxProcess, $doc, $settImg, $item, 'imgExclItems', array( 'excl' ) ) );
}

function Images_CheckLazyExcl( &$ctxProcess, $doc, $settImg, $item )
{
	return( Images_CheckExclEx( $ctxProcess, $doc, $settImg, $item, 'lazyExclItems', array( 'lazy', 'excl' ) ) );
}

function Images_CheckSzAdaptExcl( &$ctxProcess, $doc, $settImg, $item )
{
	$excls = Gen::GetArrField( $settImg, array( 'szAdaptExcl' ), array() );
	if( !$excls )
		return( false );

	$ctxSzAdaptExcl = (isset($ctxProcess[ 'ctxSzAdaptExcl' ])?$ctxProcess[ 'ctxSzAdaptExcl' ]:null);
	if( !$ctxSzAdaptExcl )
		$ctxProcess[ 'ctxSzAdaptExcl' ] = $ctxSzAdaptExcl = new AnyObj();

	$itemRoot = $ctxProcess[ 'ndHtml' ];
	if( is_string( $item ) )
	{
		if( !(isset($ctxSzAdaptExcl -> itemTmp)?$ctxSzAdaptExcl -> itemTmp:null) )
		{
			$ctxSzAdaptExcl -> itemTmpCont = $doc -> createElement( 'root' );
			$ctxSzAdaptExcl -> itemTmpCont -> appendChild( $ctxSzAdaptExcl -> itemTmp = $doc -> createElement( 'style' ) );
		}

		HtmlNd::SetValFromContent( $ctxSzAdaptExcl -> itemTmp, $item );
		$item = $ctxSzAdaptExcl -> itemTmp;
		$itemRoot = $ctxSzAdaptExcl -> itemTmpCont;
	}

	$xpath = new \DOMXPath( $doc );

	$found = false;
	foreach( $excls as $exclItem )
	{
		$items = HtmlNd::ChildrenAsArr( @$xpath -> query( $exclItem, $itemRoot ) );
		if( in_array( $item, $items, true ) )
		{
			$found = true;
			break;
		}
	}

	HtmlNd::SetValFromContent( (isset($ctxSzAdaptExcl -> itemTmp)?$ctxSzAdaptExcl -> itemTmp:null), '' );
	return( $found );
}

function Images_Process( &$ctxProcess, $doc, $settCache, $settImg, $settCdn )
{
	if( !( Gen::GetArrField( $settImg, array( 'srcAddLm' ), false ) || Gen::GetArrField( $settImg, array( 'inlSml' ), false ) || Gen::GetArrField( $settImg, array( 'deinlLrg' ), false ) || Gen::GetArrField( $settImg, array( 'lazy', 'setSize' ), false ) || Gen::GetArrField( $settImg, array( 'lazy', 'load' ), false ) || Gen::GetArrField( $settCdn, array( 'enable' ), false ) || (isset($settImg[ 'szAdaptImg' ])?$settImg[ 'szAdaptImg' ]:null) ) )
		return( true );

	$items = HtmlNd::ChildrenAsArr( $doc -> getElementsByTagName( 'img' ) );
	if( $ctxProcess[ 'isAMP' ] )
		$items = array_merge( $items, HtmlNd::ChildrenAsArr( $doc -> getElementsByTagName( 'amp-img' ) ) );
	foreach( $items as $item )
	{
		if( ContentProcess_IsAborted( $settCache ) ) return( true );

		if( !$item -> attributes || HtmlNd::FindUpByTag( $item, 'noscript' ) || Images_CheckExcl( $ctxProcess, $doc, $settImg, $item ) )
			continue;

		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$inlinedSize = 0;
		$imgSrc = null;
		$srcAiSmallest = null;

		$bLazy = _Images_ProcessItemLazy_Start( $ctxProcess, $doc, $settImg, $item );
		if( $bLazy === false )
			return( false );

		$attr = $item -> attributes -> getNamedItem( 'src' );
		if( $attr )
		{
			$imgSrc = new ImgSrc( html_entity_decode( $attr -> nodeValue ) );
			$dataAi = null;

			if( (isset($settImg[ 'szAdaptImg' ])?$settImg[ 'szAdaptImg' ]:null) && !Images_CheckSzAdaptExcl( $ctxProcess, $doc, $settImg, $item ) )
			{
				$imgSzAlternatives = new ImgSzAlternatives();
				if( Images_ProcessSrcSizeAlternatives( $imgSzAlternatives, $ctxProcess, $imgSrc, $settCache, $settImg, $settCdn ) === false )
					return( false );

				if( !$imgSzAlternatives -> isEmpty() )
				{
					$dataAi = array( 's' => array( $imgSzAlternatives -> info[ 'cx' ], $imgSzAlternatives -> info[ 'cy' ] ), 'd' => array() );
					foreach( $imgSzAlternatives -> a as $dim => $imgSzAlternative )
					{
						if( !$imgSzAlternative[ 'img' ] )
							continue;

						$srcAiSmallest = $imgSzAlternative[ 'img' ];
						$dataAi[ 'd' ][ $dim ] = $srcAiSmallest;
					}

					$ctxProcess[ 'imgAdaptive' ] = true;
				}
			}

			$r = Images_ProcessSrc( $ctxProcess, $imgSrc, $settCache, $settImg, $settCdn );
			if( $r === false )
				return( false );

			if( $r )
				$attr -> nodeValue = htmlspecialchars( $imgSrc -> src );

			if( $dataAi )
			{
				$dataAi[ 'o' ] = $imgSrc -> src;

				HtmlNd::AddRemoveAttrClass( $item, array( 'ai-img' ) );
				$item -> setAttribute( 'data-ai-img', @json_encode( $dataAi ) );
				if( (isset($settImg[ 'szAdaptDpr' ])?$settImg[ 'szAdaptDpr' ]:null) )
					$item -> setAttribute( 'data-ai-dpr', 'y' );
				$item -> removeAttribute( 'srcset' );
				$item -> removeAttribute( 'sizes' );
			}

			if( Gen::GetArrField( $settImg, array( 'lazy', 'setSize' ), false ) && !$item -> hasAttribute( 'width' ) && !$item -> hasAttribute( 'height' ) && ( $srcImgDim = $imgSrc -> GetInfo() ) )
			{
				if( $srcImgDim[ 'cx' ] !== null && $srcImgDim[ 'cy' ] !== null )
				{
					$item -> setAttribute( 'width', ( int )round( ( float )$srcImgDim[ 'cx' ] ) );
					$item -> setAttribute( 'height', ( int )round( ( float )$srcImgDim[ 'cy' ] ) );
				}
			}

			if( Ui::IsSrcAttrData( $imgSrc -> src ) )
				$inlinedSize = strlen( $imgSrc -> src );
		}

		if( $attrSrcSet = $item -> attributes -> getNamedItem( 'srcset' ) )
		{
			$srcset = $attrSrcSet -> nodeValue;

			$r = Images_ProcessSrcSet( $ctxProcess, $srcset, $settCache, $settImg, $settCdn );
			if( $r === false )
				return( false );

			if( $r )
				$attrSrcSet -> nodeValue = htmlspecialchars( $srcset );

			if( stripos( $srcset, 'data:' ) !== false )
				$inlinedSize = strlen( $srcset );
		}

		if( $inlinedSize >= 2048 && (isset($ctxProcess[ 'chunksEnabled' ])?$ctxProcess[ 'chunksEnabled' ]:null) )
			ContentMarkSeparate( $item );

		if( $bLazy )
		{
			if( _Images_ProcessItemLazy_Finish( $ctxProcess, $doc, $settImg, $item, $imgSrc ) === false )
				return( false );
		}
		else if( $srcAiSmallest )
			$item -> setAttribute( 'src', $srcAiSmallest );
	}

	foreach( HtmlNd::ChildrenAsArr( $doc -> getElementsByTagName( 'picture' ) ) as $itemPict )
	{
		if( ContentProcess_IsAborted( $settCache ) ) return( true );

		if( HtmlNd::FindUpByTag( $itemPict, 'noscript' ) || Images_CheckExcl( $ctxProcess, $doc, $settImg, $itemPict ) )
			continue;

		foreach( $itemPict -> childNodes as $item )
		{
			if( $item -> nodeName != 'source' )
				continue;

			if( !$item -> attributes )
				continue;

			$attrSrcSet = $item -> attributes -> getNamedItem( 'srcset' );
			if( $attrSrcSet )
			{
				$srcset = $attrSrcSet -> nodeValue;

				$r = Images_ProcessSrcSet( $ctxProcess, $srcset, $settCache, $settImg, $settCdn );
				if( $r === false )
					return( false );

				if( $r )
					$attrSrcSet -> nodeValue = htmlspecialchars( $srcset );
			}

			if( Gen::GetArrField( $settImg, array( 'lazy', 'load' ), false ) )
			{

				{
					$itemCopy = $item -> cloneNode( true );
					if( !$itemCopy )
						return( false );

					$itemNoScript = $doc -> createElement( 'noscript' );
					if( !$itemNoScript )
						return( false );

					$itemNoScript -> setAttribute( 'lzl', '' );
					$itemNoScript -> appendChild( $itemCopy );
					HtmlNd::InsertAfter( $item -> parentNode, $itemNoScript, $item );
				}

				$ctxProcess[ 'lazyload' ] = true;
				HtmlNd::RenameAttr( $item, 'srcset', 'data-lzl-srcset' );
			}
		}
	}

	foreach( HtmlNd::ChildrenAsArr( $doc -> getElementsByTagName( 'image' ) ) as $itemSvgImg )
	{
		if( !HtmlNd::FindUpByTag( $itemSvgImg, 'svg' ) || HtmlNd::FindUpByTag( $itemSvgImg, 'noscript' ) || Images_CheckExcl( $ctxProcess, $doc, $settImg, $itemSvgImg ) )
			continue;

		$href = $itemSvgImg -> getAttribute( 'href' );
		if( !$href )
			continue;

		$imgSrc = new ImgSrc( $href );

		$r = Images_ProcessSrc( $ctxProcess, $imgSrc, $settCache, $settImg, $settCdn );
		if( $r === false )
			return( false );

		if( $r )
			$itemSvgImg -> setAttribute( 'href', $imgSrc -> src );
	}

	foreach( $ctxProcess[ 'aAttrImg' ] as $attrImg )
	{
		$imgSrc = new ImgSrc( $attrImg -> nodeValue );

		$r = Images_ProcessSrc( $ctxProcess, $imgSrc, $settCache, $settImg, $settCdn );
		if( $r === false )
			return( false );

		if( $r )
			$attrImg -> nodeValue = $imgSrc -> src;
	}

	if( Gen::GetArrField( $settImg, array( 'srcAddLm' ), false ) || Gen::GetArrField( $settCdn, array( 'enable' ), false ) )
	{
		$srcImgDim = null;

		$settImgForMeta = Gen::ArrCopy( $settImg );
		Gen::SetArrField( $settImgForMeta, array( 'inlSml' ), false );
		Gen::SetArrField( $settImgForMeta, array( 'deinlLrg' ), false );

		foreach( $ctxProcess[ 'ndHead' ] -> childNodes as $item )
		{
			if( HtmlNd::FindUpByTag( $item, 'noscript' ) || Images_CheckExcl( $ctxProcess, $doc, $settImg, $item ) )
				continue;

			if( ContentProcess_IsAborted( $settCache ) ) return( true );

			$srcAttrName = null; $src = null;
			$bImg = true;

			if( $item -> nodeName == 'meta' )
			{
				$id = $item -> getAttribute( 'property' );
				if( !$id )
					$id = $item -> getAttribute( 'name' );

				if( $id && in_array( $id, array( 'og:image', 'og:image:secure_url', 'twitter:image', 'vk:image' ) ) )
					$srcAttrName = 'content';
			}
			else if( $item -> nodeName == 'link' )
			{
				switch( $item -> getAttribute( 'rel' ) )
				{
				case 'icon':
					$srcAttrName = 'href';
					break;

				case 'preload':
					switch( $item -> getAttribute( 'as' ) )
					{
					case 'image':
						$srcAttrName = 'href';
						break;

					case 'font':
						$srcAttrName = 'href';
						$bImg = false;
						break;
					}
					break;
				}
			}

			if( !$srcAttrName )
				continue;

			$src = $item -> getAttribute( $srcAttrName );
			if( !$src )
				continue;

			if( $bImg )
			{
				$src = new ImgSrc( $src );

				$r = Images_ProcessSrc( $ctxProcess, $src, $settCache, $settImgForMeta, $settCdn );
				if( $r === false )
					return( false );
			}
			else
			{
				$r = false;
				if( $srcInfo = Ui::IsSrcAttrData( $src ) ? false : GetSrcAttrInfo( $ctxProcess, null, null, $src ) )
				{
					if( Cdn_AdjustUrl( $ctxProcess, $settCdn, $src, Gen::GetFileExt( $srcInfo[ 'srcWoArgs' ] ) ) )
						$r = true;
					if( Fullness_AdjustUrl( $ctxProcess, $src, (isset($srcInfo[ 'srcUrlFullness' ])?$srcInfo[ 'srcUrlFullness' ]:null) ) )
						$r = true;
				}
			}

			if( $r )
				$item -> setAttribute( $srcAttrName, is_string( $src ) ? $src : $src -> src );
		}
	}

	foreach( HtmlNd::ChildrenAsArr( $doc -> getElementsByTagName( 'video' ) ) as $item )
	{
		if( HtmlNd::FindUpByTag( $item, 'noscript' ) || Images_CheckExcl( $ctxProcess, $doc, $settImg, $item ) )
			continue;

		foreach( array( 'src', 'data-src' ) as $attr )
		{
			$src = $item -> getAttribute( $attr );
			if( !$src )
				continue;

			$r = false;
			if( $srcInfo = Ui::IsSrcAttrData( $src ) ? false : GetSrcAttrInfo( $ctxProcess, null, null, $src ) )
			{
				if( Cdn_AdjustUrl( $ctxProcess, $settCdn, $src, Gen::GetFileExt( $srcInfo[ 'srcWoArgs' ] ) ) )
					$r = true;
				if( Fullness_AdjustUrl( $ctxProcess, $src, (isset($srcInfo[ 'srcUrlFullness' ])?$srcInfo[ 'srcUrlFullness' ]:null) ) )
					$r = true;
			}

			if( $r )
				$item -> setAttribute( $attr, $src );
		}

		foreach( $item -> childNodes as $itemSrc )
		{
			if( $itemSrc -> nodeName != 'source' )
				continue;

			$src = $itemSrc -> getAttribute( 'src' );

			$r = false;
			if( $srcInfo = Ui::IsSrcAttrData( $src ) ? false : GetSrcAttrInfo( $ctxProcess, null, null, $src ) )
			{
				if( Cdn_AdjustUrl( $ctxProcess, $settCdn, $src, Gen::GetFileExt( $srcInfo[ 'srcWoArgs' ] ) ) )
					$r = true;
				if( Fullness_AdjustUrl( $ctxProcess, $src, (isset($srcInfo[ 'srcUrlFullness' ])?$srcInfo[ 'srcUrlFullness' ]:null) ) )
					$r = true;
			}

			if( $r )
				$itemSrc -> setAttribute( 'src', $src );
		}
	}

	if( Gen::GetArrField( $settCdn, array( 'enable' ), false ) )
	{
        $ctxRpl = new AnyObj();
		$ctxRpl -> ctxProcess = $ctxProcess;
		$ctxRpl -> settCdn = $settCdn;

		$ctxRpl -> cb =
			function( $ctxRpl, $srcOrig )
			{
				$src = $srcOrig;

				$r = false;
				if( $srcInfo = Ui::IsSrcAttrData( $src ) ? false : GetSrcAttrInfo( $ctxRpl -> ctxProcess, null, null, $src ) )
				{
					if( Cdn_AdjustUrl( $ctxRpl -> ctxProcess, $ctxRpl -> settCdn, $src, Gen::GetFileExt( $srcInfo[ 'srcWoArgs' ] ) ) )
						$r = true;
				}

				if( !$r )
					return( $srcOrig );

				$ctxRpl -> r = true;
				return( $src );
			}
		;
		$ctxRpl -> cbEscaped =
			function( $ctxRpl, $m )
			{
				$srcOrig = str_replace( '\\/', '/', $m[ 0 ] );
				if( $srcOrigLastSlash = Gen::StrEndsWith( $srcOrig, '\\' ) )
					$srcOrig = substr( $srcOrig, 0, -1 );
				$src = $ctxRpl -> cb( $srcOrig );
				if( $srcOrig === $src )
					return( $m[ 0 ] );
				return( str_replace( '/', '\\/', $src ) . ( $srcOrigLastSlash ? '\\' : '' ) );
			}
		;

		$ctxRpl -> cbSimple =
			function( $ctxRpl, $m )
			{
				return( $ctxRpl -> cb( $m[ 0 ] ) );
			}
		;

		$originalUrl = $ctxProcess[ 'siteDomainUrl' ] . $ctxProcess[ 'siteRootUri' ];
        $directories = array();
		$bApply = false;
		{
			foreach( Gen::GetArrField( $settCdn, array( 'items' ), array() ) as $settCdnItem )
				if( $settCdnItem[ 'enable' ] && $settCdnItem[ 'addr' ] && (isset($settCdnItem[ 'sa' ])?$settCdnItem[ 'sa' ]:null) )
				{
					$bApply = true;
					$directories = array_merge( Gen::GetArrField( $settCdnItem, array( 'uris' ), array() ) );
				}
			$directories = array_unique( $directories );
		}

		if( $bApply )
		{
			$regexOriginalUrl = preg_quote( $originalUrl, '@' );
			$directories = implode( '|', array_map( function( $v ) { return( preg_quote( Gen::SetLastSlash( $v ), '@' ) ); }, $directories ) );
			$escapedOriginalUrl = str_replace( '/', '(?:\\\\/)', $regexOriginalUrl );
			$escapedIncludedDirs = str_replace( '/', '(?:\\\\/)', $directories );
			$regexSimple = '@(?<=[(\\"\'])(?:' . $regexOriginalUrl . ')?/(?:((?:' . $directories . ')[^\\"\')]+)|([^/\\"\']+\\.[^/\\"\')]+))(?=[\\"\')])@S';
			$regexEscaped = '@(?<=[(\\"\'])(?:' . $escapedOriginalUrl . ')?(?:\\\\/)(?:((?:' . $escapedIncludedDirs . ')[^\\"\')]+)|([^/\\"\']+\\.[^/\\"\')]+))(?=[\\"\')])@S';

			foreach( HtmlNd::ChildrenAsArr( $doc -> getElementsByTagName( 'script' ) ) as $item )
			{
				if( strlen( ( string )$item -> getAttribute( 'src' ) ) > 0 )
					continue;

				$cont = $item -> nodeValue;

				$ctxRpl -> r = false;

				{
					$result = @preg_replace_callback( $regexEscaped, array( $ctxRpl, 'cbEscaped' ), $cont );
					if( $result !== null )
						$cont = $result;
					unset( $result );
				}

				{
					$result = @preg_replace_callback( $regexSimple, array( $ctxRpl, 'cbSimple' ), $cont );
					if( $result !== null )
						$cont = $result;
					unset( $result );
				}

				if( $ctxRpl -> r )
					HtmlNd::SetValFromContent( $item, $cont );
			}
		}
	}

	return( true );
}

function Image_MakeOwnRedirUrlArgs( $path )
{
	return( array( 'seraph_accel_gci' => $path, 'n' => Gen::GetNonce( $path, GetSalt() ) ) );
}

function _Images_ProcessSrcSizeAlternatives_Cache_ArrayOnFiles( $fileTpl )
{
	return( array( 'dirFilesPattern' => $fileTpl . '_*.dat.gz', 'options' => array( 'comprLev' => 9 ) ) );
}

function Images_ProcessSrcSizeAlternatives_Cache_Get( $dataPath, $imgStgId )
{
	$fileTpl = Gen::GetFileDir( $dataPath ) . '/ai/c';

	$lock = new Lock( $fileTpl . 'l', false );
	if( !$lock -> Acquire() )
		return( null );

	$aCache = new ArrayOnFiles( _Images_ProcessSrcSizeAlternatives_Cache_ArrayOnFiles( $fileTpl ) );
	$a = ( array )$aCache -> offsetGet( $imgStgId );
	$aCache -> dispose(); $lock -> Release();

	return( (isset($a[ 'v' ])?$a[ 'v' ]:null) );
}

function Images_ProcessSrcSizeAlternatives_Cache_Set( $dataPath, $imgStgId, $v )
{
	$fileTpl = Gen::GetFileDir( $dataPath ) . '/ai/c';

	$lock = new Lock( $fileTpl . 'l', false );
	if( !$lock -> Acquire() )
		return;

	$aCache = new ArrayOnFiles( _Images_ProcessSrcSizeAlternatives_Cache_ArrayOnFiles( $fileTpl ) );
	$aCache[ $imgStgId ] = array( 't' => time(), 'v' => $v );
	$aCache -> dispose(); $lock -> Release();
}

function Images_ProcessSrcSizeAlternatives_Cache_Cleanup( $dataPath, $tm, $cbIsAborted )
{
	$fileTpl = Gen::GetFileDir( $dataPath ) . '/ai/c';

	$lock = new Lock( $fileTpl . 'l', false );
	if( !$lock -> Acquire() )
		return;

	$aCache = new ArrayOnFiles( _Images_ProcessSrcSizeAlternatives_Cache_ArrayOnFiles( $fileTpl ) );

	$aDel = array();

	$bAborted = false;
	foreach( $aCache as $imgStgId => $a )
	{
		if( @call_user_func( $cbIsAborted ) )
		{
			$bAborted = true;
			break;
		}

		if( ( int )(isset($a[ 't' ])?$a[ 't' ]:null) < $tm )
			$aDel[] = $imgStgId;
	}

	foreach( $aDel as $imgStgId )
	{
		if( @call_user_func( $cbIsAborted ) )
		{
			$bAborted = true;
			break;
		}

		unset( $aCache[ $imgStgId ] );
	}

	$aCache -> dispose(); $lock -> Release();
	return( !$bAborted );
}

