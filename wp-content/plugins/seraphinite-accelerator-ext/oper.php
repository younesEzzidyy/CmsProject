<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

function _CacheDirWalk_User( $path, $item, &$ctx )
{
	$path = $path . '/' . $item . '/s';
	if( !@is_dir( $path ) )
		return( true );

	return( Gen::DirEnum( $path, $ctx,
		function( $path, $item, &$ctx )
		{
			$path = $path . '/' . $item . '/s';
			if( !@is_dir( $path ) )
				return( true );

			$ctx[ 'isDefSess' ] = ( $item == '@' );

			return( Gen::DirEnum( $path, $ctx,
				function( $path, $item, &$ctx )
				{
					$path = $path . '/' . $item . '/o';
					if( !@is_dir( $path ) )
						return( true );

					$ctx[ 'isUserCtx' ] = !( $ctx[ 'isDefSess' ] && ( $item == '@' ) );
					$ctx[ 'dirRootLen' ] = strlen( $path );

					$recurse = true;
					$objPathSpec = Gen::GetArrField( $ctx[ 'spec' ], array( 'objPath' ) );
					if( $objPathSpec !== null )
					{
						if( $objPathSpec )
							$path .= '/' . $objPathSpec;
						$recurse = Gen::GetArrField( $ctx[ 'spec' ], array( 'objPathRecurse' ), false );
					}

					foreach( Gen::GetArrField( $ctx[ 'spec' ], array( 'objPathMask' ), false ) ? @glob( $path, GLOB_NOSORT | GLOB_ONLYDIR ) : array( $path ) as $pathReal )
					{
						if( @is_dir( $pathReal ) && $ctx[ 'cbPath' ] && call_user_func_array( $ctx[ 'cbPath' ], array( &$ctx[ 'ctxWalk' ], $ctx[ 'isUserCtx' ], str_replace( '\\', '/', substr( $pathReal, $ctx[ 'dirRootLen' ] ) ) ) ) === false )
							return( false );

						if( Gen::DirEnum( $pathReal, $ctx,
							function( $path, $item, &$ctx )
							{
								$item = $path . '/' . $item;
								if( @is_dir( $item ) )
									return( $ctx[ 'cbPath' ] ? call_user_func_array( $ctx[ 'cbPath' ], array( &$ctx[ 'ctxWalk' ], $ctx[ 'isUserCtx' ], str_replace( '\\', '/', substr( $item, $ctx[ 'dirRootLen' ] ) ) ) ) : true );
								return( $ctx[ 'cbObj' ] ? call_user_func_array( $ctx[ 'cbObj' ], array( &$ctx[ 'ctxWalk' ], $ctx[ 'isUserCtx' ], $item ) ) : true );
							}
							, $recurse
						) === false )
						{
							return( false );
						}
					}
				}
			) );
		}
	) );
}

function _CacheDirWalk_View( $path, $viewId, &$ctx2 )
{
	$path .= '/' . $viewId;
	if( !@is_dir( $path ) )
		return( true );

	$ctx = &$ctx2 -> ctx;

	if( $ctx2 -> aViewId !== null )
	{
		$f = false;
		foreach( $ctx2 -> aViewId as $viewIdFilter )
		{
			if( Gen::StrEndsWith( $viewIdFilter, '*' ) )
			{
				$viewIdFilter = substr( $viewIdFilter, 0, -1 );
				if( $viewId != $viewIdFilter && !Gen::StrStartsWith( $viewId, $viewIdFilter . '-' ) )
					continue;
			}
			else if( $viewId != $viewIdFilter )
				continue;

			$f = true;
			break;
		}

		if( !$f )
			return( true );
	}

	$viewDir = $path;

	if( $cbView = $ctx[ 'cbView' ] )
		call_user_func_array( $cbView, array( &$ctx[ 'ctxWalk' ], $viewId, $viewDir, true ) );

	$path .= '/c';
	if( !@is_dir( $path ) )
		return( true );

	$userIdSpec = Gen::GetArrField( $ctx[ 'spec' ], array( 'userId' ) );
	$res = $userIdSpec ? _CacheDirWalk_User( $path, '' . $userIdSpec, $ctx ) : Gen::DirEnum( $path, $ctx, 'seraph_accel\\_CacheDirWalk_User' );

	if( $cbView = $ctx[ 'cbView' ] )
		call_user_func_array( $cbView, array( &$ctx[ 'ctxWalk' ], $viewId, $viewDir, false ) );

	return( $res );
}

function _CacheDirWalk( $siteId, $siteSubId, $aViewId, &$ctxWalk, $spec = null, $cbObj = null, $cbData = null, $cbPath = null, $cbView = null, $cbSite = null )
{
	$siteCacheRootPath = GetCacheDir() . '/s/' . $siteId;

	$ctx = array( 'ctxWalk' => &$ctxWalk, 'spec' => $spec, 'cbObj' => $cbObj, 'cbPath' => $cbPath, 'cbData' => $cbData, 'cbView' => $cbView );

	if( $cbSite )
		if( call_user_func_array( $cbSite, array( &$ctx[ 'ctxWalk' ], $siteCacheRootPath, true ) ) === false )
			return( false );

	if( $cbObj || $cbPath )
	{
		foreach( ( $siteSubId === null ) ? @glob( GetCacheViewsDir( $siteCacheRootPath ) . '*' ) : array( GetCacheViewsDir( $siteCacheRootPath ) . '-' . $siteSubId ) as $viewDir )
		{
			$ctx2 = new AnyObj();
			$ctx2 -> ctx = &$ctx;
			$ctx2 -> aViewId = $aViewId;
			if( Gen::DirEnum( $viewDir, $ctx2, 'seraph_accel\\_CacheDirWalk_View' ) === false )
				return( false );

		}
	}

	if( $cbData )
	{
		$cacheDataDir = GetCacheDataDir( $siteCacheRootPath );
		$ctx[ 'cacheDataDirLen' ] = strlen( $cacheDataDir );

		if( Gen::DirEnum( $cacheDataDir, $ctx,
			function( $path, $item, &$ctx )
			{
				$itemId = $item;

				$item = $path . '/' . $item;
				if( @is_dir( $item ) )
					return( true );

				$itemIdPrefix = explode( '/', substr( $path, $ctx[ 'cacheDataDirLen' ] + 1 ) );
				if( strlen( $itemIdPrefix[ 0 ] ) > 1 )
				{
					$itemType = $itemIdPrefix[ 0 ];
					array_splice( $itemIdPrefix, 0, 1 );
				}
				else
					$itemType = 'html';

				$itemId = explode( '.', $itemId );
				$itemId = implode( '', $itemIdPrefix ) . (isset($itemId[ 0 ])?$itemId[ 0 ]:null) . '.' . (isset($itemId[ 1 ])?$itemId[ 1 ]:null);

				return( call_user_func_array( $ctx[ 'cbData' ], array( &$ctx[ 'ctxWalk' ], $itemType, $itemId, $item ) ) );
			}
		, true ) === false )
		{
			return( false );
		}
	}

	if( $cbSite )
		if( call_user_func_array( $cbSite, array( &$ctx[ 'ctxWalk' ], $siteCacheRootPath, false ) ) === false )
			return( false );

	return( true );
}

function CacheGetInfo( $siteId, $cbCancel )
{
	$info = array( 'cbCancel' => $cbCancel, 'nObj' => 0, 'nJs' => 0, 'nCss' => 0, 'nImg' => 0, 'nLrn' => 0, 'size' => 0, 'sizeUncompr' => 0, 'sizeLrn' => 0, 'sizeObjFrag' => 0, 'sizeObj' => 0 );

	if( _CacheDirWalk( $siteId, null, null, $info, null,
		function( &$info, $isUserCtx, $objFile )
		{
			if( $info[ 'cbCancel' ]() )
				return( false );

			$info[ 'nObj' ] += 1;
			$sz = @filesize( $objFile );
			$info[ 'size' ] += $sz;
			$info[ 'sizeUncompr' ] += $sz;

			foreach( Gen::GetArrField( CacheReadDsc( $objFile ), array( 'p' ), array() ) as $oiCi )
			{
				if( $info[ 'cbCancel' ]() )
					return( false );

				$sz = GetCacheCos( $oiCi );

				$info[ 'sizeObj' ] += $sz;

			}
		}

		,
		function( &$info, $dataType, $dataId, $dataFile )
		{
			if( $info[ 'cbCancel' ]() )
				return( false );

			$sz = @filesize( $dataFile );
			$info[ 'size' ] += $sz;

			if( $dataType == 'img' )
			{
				$info[ 'nImg' ] += 1;
				$info[ 'sizeUncompr' ] += $sz;
			}
			else if( $dataType == Gen::GetFileExt( $dataFile ) )
			{
				switch( $dataType )
				{
				case 'js':		$info[ 'nJs' ] += 1; break;
				case 'css':		$info[ 'nCss' ] += 1; break;
				}

				$info[ 'sizeUncompr' ] += $sz;
			}
			else
				$info[ 'sizeUncompr' ] += GetCacheCos( Gen::GetFileName( Gen::GetFileName( $dataFile, true ), true ) );

			if( $dataType == 'html' )
			{
				$fileNameCount = null;
				if( $dataType == Gen::GetFileExt( $dataFile ) )
					$fileNameCount = $dataFile;
				else
				{
					$dataFileUncompr = Gen::GetFileName( $dataFile, true, true );
					if( $dataType == Gen::GetFileExt( $dataFileUncompr ) && !@file_exists( $dataFileUncompr ) )
						$fileNameCount = $dataFileUncompr;
				}

				if( $fileNameCount )
					$info[ 'sizeObjFrag' ] += GetCacheCos( Gen::GetFileName( $fileNameCount, true ) );
			}
		}

		,
		null

		,
		function( &$info, $viewId, $viewDir, $begin )
		{
			if( !$begin )
				return;

			if( Gen::DirEnum( $viewDir . '/l', $info,
				function( $path, $item, &$info )
				{
					if( $info[ 'cbCancel' ]() )
						return( false );

					$path = $path . '/' . $item;
					if( @is_dir( $path ) )
						return;

					$sz = @filesize( $path );
					$info[ 'size' ] += $sz;
					$info[ 'sizeUncompr' ] += $sz;
					$info[ 'sizeLrn' ] += $sz;

					$info[ 'nLrn' ] += 1;
				}
			, true ) === false )
			{
				return( false );
			}
		}

		,
		function( &$info, $siteDir, $begin )
		{
			if( !$begin )
				return;

			if( Gen::DirEnum( $siteDir . '/l', $info,
				function( $path, $item, &$info )
				{
					if( $info[ 'cbCancel' ]() )
						return( false );

					$path = $path . '/' . $item;
					if( @is_dir( $path ) )
						return;

					$sz = @filesize( $path );
					$info[ 'size' ] += $sz;
					$info[ 'sizeUncompr' ] += GetCacheCos( Gen::GetFileName( Gen::GetFileName( $path, true ), true ) );
					$info[ 'sizeLrn' ] += $sz;
				}
			, true ) === false )
			{
				return( false );
			}
		}
	) === false )
	{
		return( null );
	}

	unset( $info[ 'cbCancel' ] );
	return( $info );
}

class DscLockUpdater
{
	private $timeout;

	private $lock;
	private $tmLastUpdate = 0.0;

	function __construct( $timeout = 0.0 )
	{
		$this -> timeout = $timeout;
		$this -> lock = new Lock( 'dl', GetCacheDir() );
	}

	function __destruct()
	{
		$this -> Release();
	}

	function Acquire()
	{
		return( $this -> lock -> Acquire() );
	}

	function Release( $force = false )
	{
		if( $this -> timeout && !$force )
		{
			$tmCur = microtime( true );
			if( $tmCur - $this -> tmLastUpdate < $this -> timeout )
				return;

			$this -> tmLastUpdate = $tmCur;
		}

		$this -> lock -> Release();
	}
}

class CacheUrlProcessor
{
	private $viewsHeaders;
	private $priority;
	private $cbIsAbort;
	private $siteId;
	private $sitePathLen;
	private $settCache;

	public function __construct( $cbIsAbort, $siteId, $settCache, $priority, $viewId = null )
	{
		$this -> cbIsAbort = $cbIsAbort;
		$this -> siteId = $siteId;
		$this -> settCache = $settCache;
		$this -> viewsHeaders = CacheOpGetViewsHeaders( $settCache, $viewId );
		$this -> priority = $priority;
	}

	function __destruct()
	{
	}

	function getViewIds()
	{
		return( array_keys( $this -> viewsHeaders ) );
	}

	function isAbort()
	{
		return( $this -> cbIsAbort ? @call_user_func_array( $this -> cbIsAbort, array() ) : false );
	}

	function op( $siteAddr, $path, $query, $viewId = null )
	{

		if( IsUriByPartsExcluded( $this -> settCache, $path, $query ) )
			return( null );

		$url = CacheOpUrl_DeParseUrl( $siteAddr, $path, $query );

		foreach( $this -> viewsHeaders as $id => $headers )
		{
			if( $this -> isAbort() )
				return( false );

			if( $viewId !== null && $viewId != $id )
				continue;

			if( CachePostPreparePageEx( $url, $this -> siteId, $this -> priority, null, $headers ) )
			{

			}
			usleep( 1 );
		}

		return( true );
	}
}

function _CacheOp_Clear_Dsc_MarkExistedParts( &$datasDel, $dsc, $aTypes )
{

	foreach( Gen::GetArrField( $dsc, array( 'p' ), array() ) as $oiCi )
		foreach( $aTypes as $type )
			unset( $datasDel[ $type ][ $oiCi ] );

	foreach( Gen::GetArrField( $dsc, array( 's' ), array() ) as $childType => $children )
		foreach( $children as $childId )
			unset( $datasDel[ $childType ][ $childId ] );
}

function CacheOp( $op, $priority = 0, $cbIsAborted = true )
{
	$ctx = new AnyObj();
	$ctx -> op = $op;
	$ctx -> pluginFileValueName = ( $ctx -> op === 1 ) ? 'cln' : 'o';

	if( $cbIsAborted === true && PluginFileValues::Get( $ctx -> pluginFileValueName ) !== null )
		return( false );

	if( $op == 10 )
	{
		CacheExt_Clear();
		return( null );
	}

	if( $cbIsAborted === true )
		PluginFileValues::Set( $ctx -> pluginFileValueName, $op );

	$settCacheGlobal = Gen::GetArrField( Plugin::SettGetGlobal(), array( 'cache' ), array() );

	$sett = Plugin::SettGet();
	$curSiteId = GetSiteId();

	$ctx -> lock = new DscLockUpdater(  );
	$ctx -> datasDel = array();
	$ctx -> procWorkInt = (isset($settCacheGlobal[ 'procWorkInt' ])?$settCacheGlobal[ 'procWorkInt' ]:null);
	$ctx -> procPauseInt = (isset($settCacheGlobal[ 'procPauseInt' ])?$settCacheGlobal[ 'procPauseInt' ]:null);
	$ctx -> cbIsAborted = $cbIsAborted;
	$ctx -> _isAborted =
		function( $ctx )
		{

			if( $ctx -> cbIsAborted === true )
				return( PluginFileValues::Get( $ctx -> pluginFileValueName ) === null );
			return( call_user_func( $ctx -> cbIsAborted ) );
		};
	$ctx -> isAborted = function( $ctx ) { return( !Gen::SliceExecTime( $ctx -> procWorkInt, $ctx -> procPauseInt, 5, array( $ctx, '_isAborted' ) ) ); };

	unset( $settCacheGlobal );

	if( $op == 1 )
	{
		$ctx -> timeout = Gen::GetArrField( $sett, array( 'cache', 'timeoutCln' ), 0 ) * 60;
		$ctx -> timeoutCtx = Gen::GetArrField( $sett, array( 'cache', 'ctxTimeoutCln' ), 0 ) * 60;
		$ctx -> tmCur = Gen::GetCurRequestTime();
		unset( $sett );

		if( Gen::DirEnum( ProcessCtlData_GetFullPath(), $ctx,
			function( $path, $item, &$ctx )
			{
				if( $ctx -> isAborted() )
					return( false );

				$item = $path . '/' . $item;
				if( @is_dir( $item ) )
					return;

				$tmFile = @filemtime( $item );
				if( $tmFile !== false && $ctx -> tmCur - $tmFile > 43200 )
					@unlink( $item );
			}
		) === false )
		{
			return( false );
		}

		if( _CacheDirWalk( $curSiteId, null, null, $ctx, null,
			null,

			function( &$ctx, $dataType, $dataId, $dataFile )
			{
				if( $ctx -> isAborted() )
					return( false );

				$ctx -> datasDel[ $dataType ][ $dataId ] = true;
			}
		) === false )
		{
			return( false );
		}
	}

	if( $op != 3 )
	{
		if( _CacheDirWalk( $curSiteId, null, null, $ctx, null,
			function( &$ctx, $isUserCtx, $objFile )
			{
				if( $ctx -> isAborted() )
					return( false );

				if( $ctx -> op != 1 )
				{
					_CacheObjFileOp( $ctx -> lock, $objFile, $ctx -> op );
					return;
				}

				{
					$dscFileTm = @filemtime( $objFile );
					if( $dscFileTm >= 60 )
					{
						$dscFileTmAge = $ctx -> tmCur - $dscFileTm;
						$timeout = $isUserCtx ? $ctx -> timeoutCtx : $ctx -> timeout;

						if( $timeout > 0 && $dscFileTmAge > $timeout )
						{
							@unlink( $objFile );
							return;
						}
					}
				}

				$dsc = CacheReadDsc( $objFile );
				if( !$dsc )
				{

					return;
				}

				if( isset( $dsc[ 'l' ] ) )
				{
					unset( $ctx -> lrnsDel[ $dsc[ 'l' ] ] );

					$lrnDsc = Learn_ReadDsc( $ctx -> curViewDir . '/l/' . Learn_Id2File( $dsc[ 'l' ] ) );
					Learn_KeepNeededData( $ctx -> datasDel, $ctx -> lrnsGlobDel, $lrnDsc, $ctx -> lrnDataPath );
				}

				_CacheOp_Clear_Dsc_MarkExistedParts( $ctx -> datasDel, $dsc, array( 'html' ) );
				foreach( Gen::GetArrField( $dsc, array( 'b' ), array() ) as $idSubPart => $dscPart )
					_CacheOp_Clear_Dsc_MarkExistedParts( $ctx -> datasDel, $dscPart, array( 'html', 'js', 'css' ) );
			}

			, $op == 2 ? function( &$ctx, $dataType, $dataId, $dataFile )
			{
				if( $ctx -> isAborted() )
					return( false );

				@unlink( $dataFile );
			} : null

			,
			null

			,
			function( &$ctx, $viewId, $viewDir, $begin )
			{
				if( $begin )
				{
					$ctx -> curViewDir = $viewDir;
					if( $ctx -> op == 2 )
						Gen::DelDir( $viewDir . '/l' );
				}

				if( $ctx -> op != 1 )
					return;

				if( $begin )
				{
					$ctx -> lrnsDel = array();

					if( Gen::DirEnum( $viewDir . '/l', $ctx,
						function( $path, $item, &$ctx )
						{
							if( $ctx -> isAborted() )
								return( false );

							if( @is_dir( $path . '/' . $item ) )
								return;

							while( strpos( $item, '.' ) !== false )
								$item = Gen::GetFileName( $item, true );
							$ctx -> lrnsDel[ Gen::GetFileName( $path ) . '/' . @hex2bin( $item ) ] = true;
						}
					, true ) === false )
					{
						return( false );
					}
				}
				else
				{
					foreach( $ctx -> lrnsDel as $learnId => $del )
						Learn_Clear( $viewDir . '/l/' . Learn_Id2File( $learnId ) );
				}
			}

			,
			function( &$ctx, $siteDir, $begin )
			{
				if( $begin )
				{
					$ctx -> lrnDataPath = $siteDir . '/l';
					$ctx -> lrnsGlobDel = array();

					if( $ctx -> op == 2 )
						Gen::DelDir( $ctx -> lrnDataPath );

					if( $ctx -> op != 1 )
						return;

					if( Gen::DirEnum( $ctx -> lrnDataPath, $ctx,
						function( $path, $item, &$ctx )
						{
							if( $ctx -> isAborted() )
								return( false );

							$path .= '/' . $item;
							if( !@is_dir( $path ) )
								$ctx -> lrnsGlobDel[ str_replace( '\\', '/', substr( $path, strlen( $ctx -> lrnDataPath ) + 1 ) ) ] = true;
						}
					, true ) === false )
					{
						return( false );
					}
				}
				else
				{
					foreach( $ctx -> lrnsGlobDel as $file => $del )
						@unlink( $siteDir . '/l/' . $file );

					if( $ctx -> op == 1 )
						if( Images_ProcessSrcSizeAlternatives_Cache_Cleanup( $siteDir . '/d', $ctx -> tmCur - $ctx -> timeout, array( $ctx, 'isAborted' ) ) === false )
							return( false );
				}
			}
		) === false )
		{
			return( false );
		}
	}

	if( ( $op == 2 || $op == 0 ) && Gen::GetArrField( $sett, array( 'cache', 'srvClr' ), false ) )
		CacheExt_Clear();

	if( $op == 1 )
	{
		foreach( $ctx -> datasDel as $datasDelType => $datasDel )
			if( empty( $datasDel ) )
				unset( $ctx -> datasDel[ $datasDelType ] );

		if( $ctx -> datasDel )
		{
			if( _CacheDirWalk( $curSiteId, null, null, $ctx, null,
				null,
				function( &$ctx, $dataType, $dataId, $dataFile )
				{
					if( $ctx -> isAborted() )
						return( false );

					if( (isset($ctx -> datasDel[ $dataType ][ $dataId ])?$ctx -> datasDel[ $dataType ][ $dataId ]:null) )
					{
						$tmFile = @filemtime( $dataFile );
						if( $tmFile !== false && $ctx -> tmCur - $tmFile > min( $ctx -> timeout, ( 12 * 60 * 60 ) ) )
							@unlink( $dataFile );
					}
				}
			) === false )
			{
				return( false );
			}
		}
	}

	if( $op == 1 || $op == 2 )
	{
		$siteCacheRootPath = GetCacheDir() . '/s/' . $curSiteId;

		foreach( glob( GetCacheViewsDir( $siteCacheRootPath ) . '*' ) as $viewDir )
		{
			if( Gen::DirEnum( $viewDir, $ctx,
				function( $path, $item, &$ctx )
				{
					if( $ctx -> isAborted() )
						return( false );

					$path = $path . '/' . $item;
					if( @is_dir( $path ) )
						@rmdir( $path );
				}
				, true
			) === false )
			{
				return( false );
			}
		}

		if( Gen::DirEnum( GetCacheDataDir( $siteCacheRootPath ), $ctx,
			function( $path, $item, &$ctx )
			{
				if( $ctx -> isAborted() )
					return( false );

				$path = $path . '/' . $item;
				if( @is_dir( $path ) )
					@rmdir( $path );
			}
		, true ) === false )
		{
			return( false );
		}
	}

	$settCache = Gen::GetArrField( Plugin::SettGet(), array( 'cache' ), array() );
	if( ( $op == 0 || $op == 3 || $op == 2 ) && (isset($settCache[ 'autoProc' ])?$settCache[ 'autoProc' ]:null) )
	{
		PostUpdCancel( is_multisite() ? $curSiteId : null );
		CacheQueueDelete( is_multisite() ? $curSiteId : null );

		$ctx -> curSiteId = $curSiteId;
		$ctx -> viewId = null;
		$ctx -> proc = new CacheUrlProcessor( array( $ctx, 'isAborted' ), $ctx -> curSiteId, $settCache, $priority, $ctx -> viewId );
		$ctx -> cb = function( $ctx, $url )
		{
			CacheOpUrl_ParseUrl( $url, $ctx -> curSiteAddr, $siteSubId, $path, $ctx -> curQuery );

			$recurse = false;
			$mask = false;
			if( Gen::StrEndsWith( $path, '/*' ) )
			{
				$path = substr( $path, 0, -1 );
				$recurse = true;
			}

			if( strpos( $path, '*' ) !== false )
				$mask = true;

			if( !$recurse && !$mask )
			{
				if( $ctx -> proc -> op( $ctx -> curSiteAddr, $path, $ctx -> curQuery ) === false )
					return( false );
				return;
			}

			if( _CacheDirWalk( $ctx -> curSiteId, $siteSubId, $ctx -> proc -> getViewIds(), $ctx, array( 'objPath' => strtolower( trim( $path, '/' ) ), 'objPathRecurse' => $recurse, 'objPathMask' => $mask )
				, null
				, null

				,
				function( &$ctx, $isUserCtx, $siteRelPath )
				{

					if( $ctx -> isAborted() )
						return( false );

					if( $ctx -> proc -> op( $ctx -> curSiteAddr, $siteRelPath, $ctx -> curQuery, $ctx -> curViewId ) === false )
						return( false );

				}
				,
				function( &$ctx, $viewId, $viewDir, $begin )
				{
					if( !$begin )
						return;

					$ctx -> curViewId = $viewId;

				}
			) === false )
			{
				return( false );
			}
		};

		if( Op_DepItems_Process( Gen::GetArrField( $settCache, array( 'updAllDeps' ), array() ), array( $ctx, 'cb' ) ) === false )
			return( false );

		CachePushQueueProcessor();
	}

	if( $cbIsAborted === true )
		PluginFileValues::Del( $ctx -> pluginFileValueName );
	return( true );
}

function _CacheObjFileOp( $lock, $objFile, $op )
{
	switch( $op )
	{
	case 0:
		$lock -> Acquire();

		if( Gen::StrEndsWith( $objFile, '.p' ) )
			@unlink( $objFile );
		else
			@touch( $objFile, 0 );

		$lock -> Release();
		break;

	case 2:
		$lock -> Acquire();

		@unlink( $objFile );
		$lock -> Release();
		break;
	}

}

function CacheOpUser( $userId, $op )
{
	$curSiteId = GetSiteId();

	$ctx = array( 'op' => $op, 'lock' => new DscLockUpdater() );

	_CacheDirWalk( $curSiteId, null, null, $ctx, array( 'userId' => $userId ),
		function( &$ctx, $isUserCtx, $objFile )
		{
			_CacheObjFileOp( $ctx[ 'lock' ], $objFile, $ctx[ 'op' ] );
		}
	);

}

function _Op_DepItem_Process_GetFldFromObj( $obj, $fld )
{
	if( is_array( $obj ) )
	{
		foreach( $obj as $objSub )
			if( ( $v = Gen::GetArrField( $objSub, $fld ) ) !== null )
				return( $v );

		return( null );
	}

	return( Gen::GetArrField( $obj, $fld ) );
}

function _Op_DepItem_Process_GetValFromObj( $obj, $v )
{
	if( $v === null || (isset($v[ 0 ])?$v[ 0 ]:null) != '{' )
		return( $v );
	$v = _Op_DepItem_Process_GetFldFromObj( $obj, substr( $v, 1, -1 ) );
	return( is_string( $v ) ? str_replace( array( ':', '<', '|', '>', '@', ',' ), array( '%3A', '%3C', '%7C', '%3E', '%40', '%2C' ), $v ) : $v );
}

function _Op_DepItem_Process_GetValsFromObj( $obj, $vals )
{
	if( !is_string( $vals ) || !strlen( $vals ) )
		return( array() );

	$vals = explode( ',', $vals );
	foreach( $vals as &$val )
		$val = _Op_DepItem_Process_GetValFromObj( $obj, $val );
	return( $vals );
}

function _Op_DepItem_UrlAddPath( $url, $path )
{
	if( !$path )
		return( $url );
	return( rtrim( $url, '/' ) . '/' . ltrim( $path, '/' ) );
}

function _Op_DepItem_UrlAddArgs( $url, $args )
{
	$urlComps = Net::UrlParse( $url, Net::URLPARSE_F_QUERY );
	if( !$urlComps )
		return( null );

	$urlComps[ 'query' ] = array_merge( $urlComps[ 'query' ], $args );
	return( Net::UrlDeParse( $urlComps ) );
}

function _Op_DepItems_ParseEx( $dependItem )
{
	$dependItem = '<' . str_replace( array( '@>', '@<', '://' ), array( '@%3E', '@%3C', '%3A//' ), $dependItem ) . '>';

	for( $i = 0; $i < strlen( $dependItem ); $i++ )
	{
		if( $dependItem[ $i ] == '<' )
		{
			if( $i && $dependItem[ $i - 1 ] != '<' )
			{
				$repl = $dependItem[ $i - 1 ] == '>' ? ',' : '",';
				$dependItem = substr_replace( $dependItem, $repl, $i, 0 );
				$i += strlen( $repl );
			}
		}
		else if( $dependItem[ $i ] == '>' )
		{
			if( $dependItem[ $i - 1 ] != '<' && $dependItem[ $i - 1 ] != '>' )
			{
				$repl = $dependItem[ $i - 1 ] == '|' ? '""' : '"';
				$dependItem = substr_replace( $dependItem, $repl, $i, 0 );
				$i += strlen( $repl );
			}
		}
		else if( $dependItem[ $i ] == '|' )
		{
			if( $dependItem[ $i - 1 ] != '>' )
			{
				$repl = ( $dependItem[ $i - 1 ] == '<' || $dependItem[ $i - 1 ] == '|' ) ? '""' : '"';
				$dependItem = substr_replace( $dependItem, $repl, $i, 0 );
				$i += strlen( $repl );
			}
		}
		else
		{
			$repl = '';
			if( $dependItem[ $i - 1 ] == '<' || $dependItem[ $i - 1 ] == '|' )
				$repl = '"';
			else if( $dependItem[ $i - 1 ] == '>' )
				$repl = ',"';

			if( $repl )
			{
				$dependItem = substr_replace( $dependItem, $repl, $i, 0 );
				$i += strlen( $repl );
			}
		}
	}

	$dependItem = substr( str_replace( array( '<', '|', '>' ), array( '[[', '],[', ']]' ), $dependItem ), 1, -1 );
	return( $dependItem );
}

function _Op_DepItems_PostParse( &$dependItem )
{
	for( $i = 0; $i < count( $dependItem ); $i++ )
	{
		if( is_array( $dependItem[ $i ] ) )
		{
			foreach( $dependItem[ $i ] as &$dependItemSub )
				_Op_DepItems_PostParse( $dependItemSub );
			continue;
		}

		$d = explode( ':', trim( $dependItem[ $i ], ':' ) );
		foreach( $d as &$di )
		{
			$di = new AnyObj( array( 'args' => explode( '@', $di ) ) );
			if( count( $di -> args ) > 1 && $di -> args[ 0 ] === '' )
			{
				array_shift( $di -> args );
				$di -> name = array_shift( $di -> args );
				$di -> args = array_map( function( $v ) { return( str_replace( array( '%3A//' ), array( '://' ), $v ) ); }, $di -> args );
			}
		}

		array_splice( $dependItem, $i, 1, $d );
		$i += count( $d ) - 1;
	}
}

function _Op_DepItems_Parse( $dependItem )
{
	$dependItem = @json_decode( _Op_DepItems_ParseEx( $dependItem ), true );
	_Op_DepItems_PostParse( $dependItem );
	return( $dependItem );
}

function _Op_DepItem_Process( $dependItem, $cb, $obj = null, $url = '' )
{
	if( !$dependItem )
		return( @call_user_func( $cb, $url ) );

	$p = array_shift( $dependItem );
	if( is_array( $p ) )
	{
		foreach( $p as $pi )
			if( _Op_DepItem_Process( array_merge( $pi, $dependItem ), $cb, $obj, $url ) === false )
				return( false );
		return( null );
	}

	if( (isset($p -> name)?$p -> name:null) === null )
		return( _Op_DepItem_Process( $dependItem, $cb, $obj, $url . (isset($p -> args[ 0 ])?$p -> args[ 0 ]:null) ) );

	switch( $p -> name )
	{
	case 'IF':
		$val = _Op_DepItem_Process_GetValFromObj( $obj, (isset($p -> args[ 0 ])?$p -> args[ 0 ]:null) );
		if( count( $p -> args ) > 2 )
		{
			$vOp = (isset($p -> args[ 1 ])?$p -> args[ 1 ]:null);
			$valsCmp = _Op_DepItem_Process_GetValsFromObj( $obj, (isset($p -> args[ 2 ])?$p -> args[ 2 ]:null) );
		}
		else
		{
			$vOp = '=';
			$valsCmp = _Op_DepItem_Process_GetValsFromObj( $obj, (isset($p -> args[ 1 ])?$p -> args[ 1 ]:null) );
		}

		switch( $vOp )
		{
		case '=':				$isTrue = in_array( $val, $valsCmp ); break;
		case '!=':				$isTrue = !in_array( $val, $valsCmp ); break;
		case '%3C':	$isTrue = $val <  (isset($valsCmp[ 0 ])?$valsCmp[ 0 ]:null); break;
		case '%3C=':	$isTrue = $val <= (isset($valsCmp[ 0 ])?$valsCmp[ 0 ]:null); break;
		case '%3E':	$isTrue = $val >  (isset($valsCmp[ 0 ])?$valsCmp[ 0 ]:null); break;
		case '%3E=':	$isTrue = $val >= (isset($valsCmp[ 0 ])?$valsCmp[ 0 ]:null); break;
		}

		if( !is_array( (isset($dependItem[ 0 ])?$dependItem[ 0 ]:null) ) )
			return( $isTrue ? _Op_DepItem_Process( $dependItem, $cb, $obj, $url ) : null );

		$pi = array_shift( $dependItem );
		$pi = (isset($pi[ $isTrue ? 0 : 1 ])?$pi[ $isTrue ? 0 : 1 ]:null);
		if( !is_array( $pi ) )
			$pi = array();
		return( _Op_DepItem_Process( array_merge( $pi, $dependItem ), $cb, $obj, $url ) );

	case 'home':
		return( _Op_DepItem_Process( $dependItem, $cb, $obj, Wp::GetSiteRootUrl( '', false ) ) );

	case 'path':
		$val = _Op_DepItem_Process_GetValFromObj( $obj, (isset($p -> args[ 0 ])?$p -> args[ 0 ]:null) );
		$urlComps = Net::UrlParse( $url );
		if( !$urlComps )
			return( null );

		$urlComps[ 'path' ] = _Op_DepItem_UrlAddPath( $urlComps[ 'path' ], $val );
		return( _Op_DepItem_Process( $dependItem, $cb, $obj, Net::UrlDeParse( $urlComps ) ) );

	case 'arg':
		return( _Op_DepItem_Process( $dependItem, $cb, $obj, _Op_DepItem_UrlAddArgs( $url, array( rawurldecode( _Op_DepItem_Process_GetValFromObj( $obj, (isset($p -> args[ 0 ])?$p -> args[ 0 ]:null) ) ) => rawurldecode( _Op_DepItem_Process_GetValFromObj( $obj, (isset($p -> args[ 1 ])?$p -> args[ 1 ]:null) ) ) ) ) ) );

	case 'pageNums':
		if( !$url )
			return( null );

		$urlComps = Net::UrlParse( Wp::GetPagenumUrl( $url, 99999, false ) );
		if( !$urlComps )
			return( null );

		$url = Net::UrlDeParse( $urlComps, 0, array( PHP_URL_QUERY, PHP_URL_FRAGMENT ) );
		if( strpos( $url, '99999' ) === false )
			return( null );

		return( _Op_DepItem_Process( $dependItem, $cb, $obj, str_replace( '99999', '*', $url ) ) );

	case 'commentPageNums':
		if( !$url )
			return( null );

		$urlComps = Net::UrlParse( Wp::GetCommentPagenumUrl( $url, 99999 ) );
		if( !$urlComps )
			return( null );

		$url = Net::UrlDeParse( $urlComps, 0, array( PHP_URL_QUERY, PHP_URL_FRAGMENT ) );
		if( strpos( $url, '99999' ) === false )
			return( null );

		return( _Op_DepItem_Process( $dependItem, $cb, $obj, str_replace( '99999', '*', $url ) ) );

	case 'post':
		$postCur = is_a( $obj, 'WP_Post' ) ? $obj : null;
		foreach( _Op_DepItem_Process_GetValsFromObj( $obj, (isset($p -> args[ 0 ])?$p -> args[ 0 ]:null) ) as $postId )
		{
			$postId = intval( $postId );
			$post = ( $postCur && $postCur -> ID == $postId ) ? $postCur : get_post( $postId );

			$lang = Wp::GetCurLang();
			$langPost = Wp::GetPostLang( $postId, $post -> post_type );

			if( $lang != $langPost )
				Wp::SetCurLang( $langPost );

			$res = _Op_DepItem_Process( $dependItem, $cb, $post, get_permalink( $post ) );

			if( $lang != $langPost )
				Wp::SetCurLang( $lang );

			if( $res === false )
				return( false );
		}

		return( null );

	case 'posts':
		return( _Op_DepItem_Process_Posts( _Op_DepItem_Process_GetValsFromObj( $obj, (isset($p -> args[ 0 ])?$p -> args[ 0 ]:null) ), $dependItem, $cb, $obj, (isset($p -> args[ 1 ])?$p -> args[ 1 ]:null) ) );

	case 'postsViewable':
		$postTypes = array();

		foreach( get_post_types( array(), 'objects' ) as $postType )
			if( is_post_type_viewable( $postType ) )
				$postTypes[] = $postType -> name;

		return( _Op_DepItem_Process_Posts( $postTypes, $dependItem, $cb, $obj, (isset($p -> args[ 0 ])?$p -> args[ 0 ]:null) ) );

	case 'postsWithTerms':
		$postTypes = _Op_DepItem_Process_GetValsFromObj( $obj, (isset($p -> args[ 0 ])?$p -> args[ 0 ]:null) );
		if( !$postTypes )
			return( null );

		$termIds = array_map( 'intval', _Op_DepItem_Process_GetValsFromObj( $obj, (isset($p -> args[ 1 ])?$p -> args[ 1 ]:null) ) );

		$nMaxItems = _Op_DepItem_Process_GetValFromObj( $obj, (isset($p -> args[ 2 ])?$p -> args[ 2 ]:null) );
		if( $nMaxItems !== null )
		{
			$nMaxItems = intval( $nMaxItems );
			if( $nMaxItems <= 0 )
				$nMaxItems = 1;
		}

		$tblPosts = Db::GetSysTbl( 'posts' );
		$tblTermRels = Db::GetSysTbl( 'term_relationships' );

		for( $i = 0; ; $i++ )
		{
			$items = DbTbl::GetRowsEx( DbTbl::QueryId( $tblPosts ) . ' LEFT JOIN ' . DbTbl::QueryId( $tblTermRels ) . ' ON (' . DbTbl::QueryId( $tblPosts . '.ID' ) . ' = ' . DbTbl::QueryId( $tblTermRels . '.object_id' ) . ')', array( 'ID', 'post_type' ), array( $i * 1000, 1000 ), array( $tblTermRels . '.term_taxonomy_id' => $termIds, $tblPosts . '.post_type' => $postTypes, $tblPosts . '.post_status' => 'publish' ), array( 'ID' => 'ASC' ), OBJECT, array( 'group' => array( $tblPosts . '.ID' ) ) );
			if( !$items )
				break;

			foreach( $items as $item )
			{
				if( _Op_DepItem_Process( $dependItem, $cb, $item, get_permalink( $item -> ID ) ) === false )
					return( false );

				if( $nMaxItems !== null )
				{
					$nMaxItems--;
					if( !$nMaxItems )
						break;
				}
			}
		}

		return( null );

	case 'terms':
		$taxonomies = _Op_DepItem_Process_GetValsFromObj( $obj, (isset($p -> args[ 0 ])?$p -> args[ 0 ]:null) );
		$postId = intval( _Op_DepItem_Process_GetValFromObj( $obj, (isset($p -> args[ 1 ])?$p -> args[ 1 ]:null) ) );
		$nMaxItems = _Op_DepItem_Process_GetValFromObj( $obj, (isset($p -> args[ 2 ])?$p -> args[ 2 ]:null) );
		$flags = _Op_DepItem_Process_GetValsFromObj( $obj, (isset($p -> args[ 3 ])?$p -> args[ 3 ]:null) );

		if( $nMaxItems !== null )
		{
			$nMaxItems = intval( $nMaxItems );
			if( $nMaxItems <= 0 )
				$nMaxItems = 1;
		}

		if( $postId )
		{
			$terms = Gen::GetArrField( wp_get_object_terms( $postId, $taxonomies, array( 'fields' => 'all' ) ), array( '' ), array() );
			foreach( $terms as $term )
				if( _Op_DepItem_Process( $dependItem, $cb, array( $term, $obj ), get_term_link( $term ) ) === false )
					return( false );
			if( _Op_DepItem_EnumTermsParents( $dependItem, $cb, $obj, $taxonomies, $terms ) === false )
				return( false );
		}
		else
		{
			$extraWhere = array();
			if( in_array( 'hideNonEmpty', $flags ) )
				$extraWhere[] = '`count` = 0';
			else if( in_array( 'hideEmpty', $flags ) )
				$extraWhere[] = '`count` > 0';

			for( $i = 0; ; $i++ )
			{
				$nMaxItemsCur = 1000;
				if( $nMaxItems !== null && $nMaxItemsCur > $nMaxItems )
					$nMaxItemsCur = $nMaxItems;
				$terms = DbTbl::GetRows( Db::GetSysTbl( 'term_taxonomy' ), array( 'term_id', 'taxonomy' ), array( $i * 1000, $nMaxItemsCur ), array( 'taxonomy' => $taxonomies ), array( 'term_id' => 'ASC' ), OBJECT, array( 'extraWhere' => $extraWhere ) );
				if( !$terms )
					break;

				foreach( $terms as $term )
				{
					$term -> term_id = intval( $term -> term_id );
					if( _Op_DepItem_Process( $dependItem, $cb, $term, get_term_link( $term -> term_id ) ) === false )
						return( false );
				}

				if( $nMaxItems !== null )
				{
					$nMaxItems -= count( $terms );
					if( $nMaxItems <= 0 )
						break;
				}
			}

		}

		return( null );

	case 'termsOfClass':
		$taxonomyClasses = _Op_DepItem_Process_GetValsFromObj( $obj, (isset($p -> args[ 0 ])?$p -> args[ 0 ]:null) );
		$postType = _Op_DepItem_Process_GetValFromObj( $obj, (isset($p -> args[ 1 ])?$p -> args[ 1 ]:null) );
		$postId = intval( _Op_DepItem_Process_GetValFromObj( $obj, (isset($p -> args[ 2 ])?$p -> args[ 2 ]:null) ) );

		if( $postType )
		{
			$postTypeObject = get_post_type_object( $postType );
			if( (isset($postTypeObject -> capability_type)?$postTypeObject -> capability_type:null) )
				$postType = $postTypeObject -> capability_type;
			unset( $postTypeObject );
		}

		foreach( $taxonomyClasses as $taxonomyClass )
		{
			if( $postType && $postId )
				foreach( Gen::GetArrField( Wp::GetPostsTaxonomiesByClass( $taxonomyClass, array( 'public' => true, 'hasRewriteSlug' => true, 'postType' => $postType ) ), array( $postType ), array() ) as $taxonomy )
				{

					$terms = Gen::GetArrField( wp_get_object_terms( $postId, $taxonomy, array( 'fields' => 'all' ) ), array( '' ), array() );
					foreach( $terms as $term )
						if( _Op_DepItem_Process( $dependItem, $cb, array( $term, $obj ), get_term_link( $term ) ) === false )
							return( false );
					if( _Op_DepItem_EnumTermsParents( $dependItem, $cb, $obj, $taxonomy, $terms ) === false )
						return( false );
				}
		}

		return( null );

	case 'postsBase':
		$postTypes = _Op_DepItem_Process_GetValsFromObj( $obj, (isset($p -> args[ 0 ])?$p -> args[ 0 ]:null) );

		foreach( $postTypes as $postType )
		{
			if( function_exists( 'get_post_type_archive_link' ) )
			{
				$link = get_post_type_archive_link( $postType );
				if( $link && _Op_DepItem_Process( $dependItem, $cb, $obj, $link ) === false )
					return( false );
			}
			else if( $postType == 'post' )
			{
				if( _Op_DepItem_Process( $dependItem, $cb, $obj, get_permalink( get_option( 'page_for_posts' ) ) ) === false )
					return( false );
			}
		}

		return( null );

	case 'sitemapItems':
		$sitemapUri = _Op_DepItem_Process_GetValFromObj( $obj, (isset($p -> args[ 0 ])?$p -> args[ 0 ]:null) );
		if( !$sitemapUri )
			$sitemapUri = 'sitemap.xml';

		if( _Op_DepItem_EnumSitemapUrls( $dependItem, $cb, ( strpos( $sitemapUri, '://' ) === false ) ? _Op_DepItem_UrlAddPath( Wp::GetSiteRootUrl(), $sitemapUri ) : $sitemapUri, $sitemapUri ) === false )
			return( false );

		return( null );

	case 'wooProdVars':
		$product = function_exists( 'wc_get_product' ) ? wc_get_product( intval( _Op_DepItem_Process_GetValFromObj( $obj, (isset($p -> args[ 0 ])?$p -> args[ 0 ]:null) ) ) ) : null;
		if( !$product || !is_a( $product, 'WC_Product_Variable' ) )
			return( null );

		$attrIdsIncl = array_map( 'strtolower', _Op_DepItem_Process_GetValsFromObj( $obj, (isset($p -> args[ 1 ])?$p -> args[ 1 ]:null) ) );
		$attrIdsExcl = array_map( 'strtolower', _Op_DepItem_Process_GetValsFromObj( $obj, (isset($p -> args[ 2 ])?$p -> args[ 2 ]:null) ) );

		$variationsUnique = array();
		$variations = $product -> get_available_variations();
		foreach( $variations as $variation )
		{
			$attrs = Gen::GetArrField( $variation, array( 'attributes' ), array() );
			$variationUniqueKey = '';
			foreach( $attrs as $attrArg => $attrVal )
			{
				$attrId = substr( $attrArg, 10 );
				if( ( $attrIdsIncl && !in_array( $attrId, $attrIdsIncl ) ) || ( $attrIdsExcl && in_array( $attrId, $attrIdsExcl ) ) )
					unset( $attrs[ $attrArg ] );
				else
					$variationUniqueKey .= $attrId . $attrVal;
			}

			if( !(isset($variationsUnique[ $variationUniqueKey ])?$variationsUnique[ $variationUniqueKey ]:null) )
			{
				if( $attrs && _Op_DepItem_Process( $dependItem, $cb, $item, _Op_DepItem_UrlAddArgs( $url, $attrs ) ) === false )
					return( false );
				$variationsUnique[ $variationUniqueKey ] = true;
			}
		}

		unset( $attrIdsIncl );
		unset( $attrIdsExcl );

		return( null );

	case 'iter':
		$n = ( int )_Op_DepItem_Process_GetValFromObj( $obj, (isset($p -> args[ 0 ])?$p -> args[ 0 ]:null) );
		$nStep = ( int )_Op_DepItem_Process_GetValFromObj( $obj, (isset($p -> args[ 2 ])?$p -> args[ 2 ]:null) );
		if( !$nStep )
			$nStep = 1;

		for( $i = ( int )_Op_DepItem_Process_GetValFromObj( $obj, (isset($p -> args[ 1 ])?$p -> args[ 1 ]:null) ); $i < $n; $i += $nStep )
			if( _Op_DepItem_Process( $dependItem, $cb, $obj, $url . $i ) === false )
				return( false );
		return( null );
	}

	return( null );
}

function _Op_DepItem_Process_Posts( $postTypes, $dependItem, $cb, $obj, $argMaxItems )
{
	if( !$postTypes )
		return( null );

	$nMaxItems = _Op_DepItem_Process_GetValFromObj( $obj, $argMaxItems );
	if( $nMaxItems !== null )
	{
		$nMaxItems = intval( $nMaxItems );
		if( $nMaxItems <= 0 )
			$nMaxItems = 1;
	}

	$lang = Wp::GetCurLang();
	$res = null;

	for( $i = 0; ; $i++ )
	{
		$items = DbTbl::GetRows( Db::GetSysTbl( 'posts' ), array( 'ID', 'post_type' ), array( $i * 1000, 1000 ), array( 'post_type' => $postTypes, 'post_status' => 'publish' ), array( 'ID' => 'ASC' ), OBJECT );
		if( !$items )
			break;

		foreach( $items as $item )
		{
			$langPost = Wp::GetPostLang( $item -> ID, $item -> post_type );
			Wp::SetCurLang( $langPost );

			if( _Op_DepItem_Process( $dependItem, $cb, $item, get_permalink( $item -> ID ) ) === false )
			{
				$res = false;
				break;
			}

			if( $nMaxItems !== null )
			{
				$nMaxItems--;
				if( !$nMaxItems )
					break;
			}
		}
	}

	Wp::SetCurLang( $lang );
	return( $res );
}

function _Op_DepItem_EnumSitemapUrls( $dependItem, $cb, $urlSitemap, $tryLocalUri = null )
{

	$data = @GetExtContents( $urlSitemap, $contMimeType, true, 30, false );

	if( !$data && $tryLocalUri )
		$data = @file_get_contents( _Op_DepItem_UrlAddPath( ABSPATH, $tryLocalUri ) );

	if( !$data )
		return( null );

	$doc = new \DOMDocument();
	$doc -> strictErrorChecking = false;

	if( !@$doc -> loadXML( $data, LIBXML_BIGLINES | LIBXML_NONET | LIBXML_PARSEHUGE ) )
		return( null );

	foreach( $doc -> getElementsByTagName( 'sitemap' ) as $child )
		foreach( $child -> getElementsByTagName( 'loc' ) as $loc )
			if( _Op_DepItem_EnumSitemapUrls( $dependItem, $cb, $loc -> nodeValue ) === false )
				return( false );

	foreach( $doc -> getElementsByTagName( 'url' ) as $child )
		foreach( $child -> getElementsByTagName( 'loc' ) as $loc )
			if( $loc -> tagName == 'loc' && _Op_DepItem_Process( $dependItem, $cb, null, $loc -> nodeValue ) === false )
				return( false );

	return( null );
}

function _Op_DepItem_EnumTermsParents( $dependItem, $cb, $obj, $taxonomies , $terms, array &$alreadyProcessed = array() )
{
	$termIds = array();
	foreach( $terms as $term )
	{
		if( !$term -> parent || isset( $alreadyProcessed[ $term -> parent ] ) )
			continue;

		$alreadyProcessed[ $term -> parent ] = true;
		$termIds[] = $term -> parent;
	}

	if( !$termIds )
		return( null );

	$terms = get_terms( array( 'taxonomy' => $taxonomies, 'include' => $termIds, 'hide_empty' => false, 'fields' => 'all' ) );
	foreach( $terms as $term )
		if( _Op_DepItem_Process( $dependItem, $cb, array( $term, $obj ), get_term_link( $term ) ) === false )
			return( false );

	if( _Op_DepItem_EnumTermsParents( $dependItem, $cb, $obj, $taxonomies, $terms, $alreadyProcessed ) === false )
		return( false );

	return( null );
}

function Op_DepItem_Process( $dependItem, $cb, $obj = null )
{
	$url = '';

	$posProto = strpos( $dependItem, '://' );
	if( ( (isset($dependItem[ 0 ])?$dependItem[ 0 ]:null) === '/' && (isset($dependItem[ 1 ])?$dependItem[ 1 ]:null) === '/' ) || ( $posProto !== false && $posProto <= 5 ) )
	{
		$url = $dependItem;
		$dependItem = '';
	}

	return( _Op_DepItem_Process( _Op_DepItems_Parse( $dependItem ), $cb, $obj, $url ) );
}

function Op_DepItems_Process( $dependItems, $cb, $obj = null )
{
	foreach( $dependItems as $dependItem )
		if( Op_DepItem_Process( $dependItem, $cb, $obj ) === false )
			return( false );
}

function CacheOp_IsPostVisible( $post )
{
	return( in_array( $post -> post_status, array( 'publish' ) ) );
}

function CacheOpPost( $postId, $del, $priority = 0, $proc = null, $cbIsAborted = false, $immediatelyPushQueue = true )
{
    $post = get_post( $postId );
	if( !$post )
		return;

	$sett = Plugin::SettGet();
	$op = Gen::GetArrField( $sett, array( 'cache', 'updPostOp' ), 0 );

	$lang = Wp::GetCurLang();
	$langPost = Wp::GetPostLang( $postId, $post -> post_type );

	Wp::SetCurLang( $langPost );

	$ctx = new AnyObj();
	$ctx -> cbIsAborted = $cbIsAborted;
	$ctx -> urls = array( get_permalink( $post ) );
	$ctx -> cb =
		function( $ctx, $url )
		{
			if( !is_bool( $ctx -> cbIsAborted ) && call_user_func( $ctx -> cbIsAborted ) )
				return( false );
			$ctx -> urls[] = $url;
		};

	if( Gen::GetArrField( Plugin::SettGet(), array( 'log' ), false ) && Gen::GetArrField( Plugin::SettGet(), array( 'logScope', 'upd' ), false ) )
	{
		$txt = '';
		switch( $op )
		{
		case 0:		$txt .= 'Automatic revalidation'; break;
		case 3:	$txt .= 'Automatic revalidation if needed'; break;
		case 2:				$txt .= 'Automatic deleting'; break;
		}

		$txt .= ' due to post with ID ' . $postId . ' ' . ( $del ? 'deleted' : 'changed' );
		$txt .= '; scope: URL(s): ' . implode( ', ', array_merge( $ctx -> urls, Gen::GetArrField( $sett, array( 'cache', 'updPostDeps' ), array() ) ) );

		LogWrite( $txt, Ui::MsgInfo, 'Cache update' );
	}

	$bAborted = false;

	if( Op_DepItems_Process( Gen::GetArrField( $sett, array( 'cache', 'updPostDeps' ), array() ), array( $ctx, 'cb' ), $post ) === false )
		$bAborted = true;

	Wp::SetCurLang( $lang );

	if( $bAborted )
		return( false );

	if( $del && $op !== 2 )
	{
		if( CacheOpUrls( false, $ctx -> urls[ 0 ], 2, $priority, $cbIsAborted, $proc ) === false )
			return( false );
		array_splice( $ctx -> urls, 0, 1 );
	}

	return( CacheOpUrls( false, $ctx -> urls, $op, $priority, $cbIsAborted, $proc, null, $immediatelyPushQueue ) );
}

function CacheOpCancel( $op )
{
	return( PluginFileValues::Del( ( $op === 1 ) ? 'cln' : 'o' ) );
}

function CacheGetCurOp( $op )
{
	return( PluginFileValues::Get( ( $op === 1 ) ? 'cln' : 'o' ) );
}

function CacheOpUrl_ParseUrl( $url, &$siteAddr, &$siteSubId, &$path, &$query )
{
	global $seraph_accel_sites;

	if( (isset($url[ 0 ])?$url[ 0 ]:null) === '/' && (isset($url[ 1 ])?$url[ 1 ]:null) === '/' )
	{
	}
	else if( strpos( $url, '://' ) === false )
	{
		if( $url && $url[ 0 ] != '/' )
			$url = '/' . $url;
		$url = rtrim( Wp::GetSiteRootUrl(), '/' ) . $url;
	}

	$urlComps = Net::UrlParse( $url, Net::URLPARSE_F_PATH_FIXFIRSTSLASH | Net::URLPARSE_F_PRESERVEEMPTIES );
	if( !(isset($urlComps[ 'scheme' ])?$urlComps[ 'scheme' ]:null) )
		Net::GetUrlWithoutProtoEx( Wp::GetSiteRootUrl(), $urlComps[ 'scheme' ] );

	$host = Net::GetSiteAddrFromUrl( Net::UrlDeParse( $urlComps, 0, array(), array( PHP_URL_SCHEME, PHP_URL_HOST, PHP_URL_PORT ) ) );
	$path = CachePathNormalize( $urlComps[ 'path' ], $pathIsDir, false );
	$query = (isset($urlComps[ 'query' ])?$urlComps[ 'query' ]:null);
	$siteId = GetCacheSiteIdAdjustPath( $seraph_accel_sites, $host, $siteSubId, $path );
	$siteAddr = $urlComps[ 'scheme' ] . '://' . $host;

	if( $pathIsDir )
		$path .= '/';
	if( $path && $path[ 0 ] != '/' )
		$path = '/' . $path;

	return( $siteId );
}

function CacheOpUrl_DeParseUrl( $siteAddr, $path, $query = null )
{
	$url = $siteAddr . $path;
	if( $query !== null )
		$url .= '?' . $query;
	return( $url );
}

function CacheOpUrls( $isExpr, $urls, $op, $priority = 0, $cbIsAborted = true, $proc = null, $viewId = null, $immediatelyPushQueue = true )
{
	if( $cbIsAborted === true && PluginFileValues::Get( 'o' ) !== null )
		return( false );

	if( !is_array( $urls ) )
		$urls = array( $urls );

	if( $cbIsAborted === true )
		PluginFileValues::Set( 'o', $op );

	$settCacheGlobal = Gen::GetArrField( Plugin::SettGetGlobal(), array( 'cache' ), array() );

	$ctx = new AnyObj();
	$ctx -> op = $op;
	$ctx -> curSiteId = GetSiteId();
	$ctx -> curSiteAddr = null;
	$ctx -> curQuery = null;
	$ctx -> curPathIsDir = false;
	$ctx -> cbIsAborted = $cbIsAborted;
	$ctx -> priority = $priority;
	$ctx -> viewId = $viewId;
	$ctx -> lock = new DscLockUpdater();
	$ctx -> procWorkInt = (isset($settCacheGlobal[ 'procWorkInt' ])?$settCacheGlobal[ 'procWorkInt' ]:null);
	$ctx -> procPauseInt = (isset($settCacheGlobal[ 'procPauseInt' ])?$settCacheGlobal[ 'procPauseInt' ]:null);
	$ctx -> _isAborted =
		function( $ctx )
		{
			if( $ctx -> cbIsAborted === true )
				return( PluginFileValues::Get( 'o' ) === null );
			if( $ctx -> cbIsAborted === false )
				return( false );
			return( call_user_func( $ctx -> cbIsAborted ) );
		};
	$ctx -> isAborted = function( $ctx ) { return( !Gen::SliceExecTime( $ctx -> procWorkInt, $ctx -> procPauseInt, 5, array( $ctx, '_isAborted' ) ) ); };

	unset( $settCacheGlobal );

	$settCache = Gen::GetArrField( Plugin::SettGet(), array( 'cache' ), array() );

	if( $op !== 1 && ( $proc !== null ? $proc : (isset($settCache[ 'autoProc' ])?$settCache[ 'autoProc' ]:null) ) )
		$ctx -> proc = new CacheUrlProcessor( array( $ctx, 'isAborted' ), $ctx -> curSiteId, $settCache, $priority, $ctx -> viewId );

	$ctx -> cbUrlOp =
		function( $ctx, $url )
		{
			if( CacheOpUrl_ParseUrl( $url, $ctx -> curSiteAddr, $siteSubId, $path, $ctx -> curQuery ) !== $ctx -> curSiteId )
				return;

			$ctx -> curPathIsDir = false;

			$recurse = false;
			$mask = false;
			if( Gen::StrEndsWith( $path, '/*' ) )
			{
				$path = substr( $path, 0, -1 );
				$recurse = true;
			}

			if( strpos( $path, '*' ) !== false )
			{
				$mask = true;
				$ctx -> curPathIsDir = Gen::StrEndsWith( $path, '/' );
			}

			if( _CacheDirWalk( $ctx -> curSiteId, $siteSubId, $ctx -> viewId !== null ? array( $ctx -> viewId . '*' ) : null, $ctx, array( 'objPath' => strtolower( trim( $path, '/' ) ), 'objPathRecurse' => $recurse, 'objPathMask' => $mask ),
				function( &$ctx, $isUserCtx, $objFile )
				{
					if( $ctx -> isAborted() )
						return( false );

					if( $ctx -> op == 10 || $ctx -> op == 3 )
						return;

					if( $ctx -> op == 2 && $ctx -> priority != -480 )
						if( $dsc = CacheReadDsc( $objFile ) )
							if( isset( $dsc[ 'l' ] ) )
								Learn_Clear( $ctx -> curViewDir . '/l/' . Learn_Id2File( $dsc[ 'l' ] ) );

					_CacheObjFileOp( $ctx -> lock, $objFile, $ctx -> op );
				}

				, null

				, ( $recurse || $mask ) ? function( &$ctx, $isUserCtx, $siteRelPath )
				{

					if( $ctx -> op == 10 )
						CacheExt_Clear( CacheOpUrl_DeParseUrl( $ctx -> curSiteAddr, $siteRelPath, $ctx -> curQuery ) );

					else if( (isset($ctx -> proc)?$ctx -> proc:null) && $ctx -> proc -> op( $ctx -> curSiteAddr, $siteRelPath . ( $ctx -> curPathIsDir ? '/' : '' ), $ctx -> curQuery, $ctx -> curViewId ) === false )
						return( false );

				} : null

				,
				function( &$ctx, $viewId, $viewDir, $begin )
				{
					if( !$begin )
						return;

					$ctx -> curViewId = $viewId;
					$ctx -> curViewDir = $viewDir;
				}
			) === false )
			{
				return( false );
			}

			if( $recurse || $mask )
				return;

			if( $ctx -> op == 10 )
				CacheExt_Clear( CacheOpUrl_DeParseUrl( $ctx -> curSiteAddr, $path, $ctx -> curQuery ) );

			else if( (isset($ctx -> proc)?$ctx -> proc:null) && $ctx -> proc -> op( $ctx -> curSiteAddr, $path, $ctx -> curQuery ) === false )
				return( false );

		};

	$bAborted = false;
	foreach( $urls as $url )
		if( ( $isExpr ? Op_DepItem_Process( $url, array( $ctx, 'cbUrlOp' ) ) : @call_user_func( array( $ctx, 'cbUrlOp' ), $url ) ) === false )
		{
			$bAborted = true;
			break;
		}

	$ctx -> lock -> Release( true );

	if( !$bAborted && $ctx -> op != 10 && (isset($ctx -> proc)?$ctx -> proc:null) )
		CachePushQueueProcessor( false, $immediatelyPushQueue );

	if( $cbIsAborted === true )
		PluginFileValues::Del( 'o' );

	return( !$bAborted );
}

function CacheOpGetViewsHeaders( $settCache, $viewId = null )
{
	$res = array();

	if( $viewId === null || $viewId === 'cmn' )
		$res[ 'cmn' ] = array( 'User-Agent' => 'Mozilla/99999.9 AppleWebKit/9999999.99 (KHTML, like Gecko) Chrome/999999.0.9999.99 Safari/9999999.99 seraph-accel-Agent/2.22.16' );

	if( (isset($settCache[ 'views' ])?$settCache[ 'views' ]:null) )
	{
		$viewsDeviceGrps = Gen::GetArrField( $settCache, array( 'viewsDeviceGrps' ), array() );
		foreach( $viewsDeviceGrps as $viewsDeviceGrp )
		{
			if( !(isset($viewsDeviceGrp[ 'enable' ])?$viewsDeviceGrp[ 'enable' ]:null) )
				continue;

			$id = (isset($viewsDeviceGrp[ 'id' ])?$viewsDeviceGrp[ 'id' ]:null);
			if( $viewId !== null && $viewId !== $id )
				continue;

			$res[ $id ] = array( 'User-Agent' => GetViewTypeUserAgent( $viewsDeviceGrp ) );

		}

		if( Gen::GetArrField( $settCache, array( 'viewsGeo', 'enable' ) ) )
		{
			$ip = gethostbyname( Gen::GetArrField( Net::UrlParse( Wp::GetSiteRootUrl() ), array( 'host' ), '' ) );
			$viewGeoId = GetViewGeoIdByIp( $settCache, $ip );

			foreach( $res as $id => &$aHdr )
			{
				$aHdr[ 'X-Seraph-Accel-Geoid' ] = $viewGeoId;
				$aHdr[ 'X-Seraph-Accel-Geo-Remote-Addr' ] = $ip;
			}
			unset( $aHdr );
		}
	}

	if( (isset($settCache[ 'opAgentPostpone' ])?$settCache[ 'opAgentPostpone' ]:null) )
	{
		foreach( $res as $id => &$aHdr )
		{
			$aHdr[ 'X-Seraph-Accel-Postpone-User-Agent' ] = $aHdr[ 'User-Agent' ];
			unset( $aHdr[ 'User-Agent' ] );
		}
		unset( $aHdr );
	}

	return( $res );
}

function OnOptDel_Sett()
{
	return( CacheInitEnv( Plugin::SettGet() ) );
}

function CacheVerifyEnvDropin( $sett, $verifyEnvDropin = null )
{
	if( $verifyEnvDropin === null )
		$verifyEnvDropin = new AnyObj();

	$verifyEnvDropin -> needed = str_replace( '.0,', ',', ( string )GetAdvCacheFileContent( $sett ) );
	$verifyEnvDropin -> actual = str_replace( '.0,', ',', ( string )@file_get_contents( WP_CONTENT_DIR . '/advanced-cache.php' ) );

	if( $verifyEnvDropin -> actual == $verifyEnvDropin -> needed )
		return( true );

}

function CacheVerifyEnvReRoot( $sett, $verifyEnvDropin = null )
{
	if( $verifyEnvDropin === null )
		$verifyEnvDropin = new AnyObj();

	$verifyEnvDropin -> needed = ( string )PluginRe::GetRootFileContent();
	$verifyEnvDropin -> actual = ( string )@file_get_contents( PluginRe::GetRootFileName() );

	if( $verifyEnvDropin -> actual == $verifyEnvDropin -> needed )
		return( true );

}

function CacheVerifyEnvNginxConf( $sett )
{
	return( @file_get_contents( Wp::GetHomePath() . 'seraph-accel-img-compr-redir.conf' ) == CacheGetEnvNginxConf( $sett ) );
}

function CacheGetEnvNginxConf( $sett )
{
	$imgTypesCnvFrom_RegExpEnum = implode( '|', array( 'jpe','jpg','jpeg','png','gif','bmp', 'webp','avif' ) );
	$confComprRedirBlock = '';

	if( !Gen::GetArrField( $sett, array( 'contPr', 'img', 'redirOwn' ), false ) )
	{
		$redirCacheAdapt = Gen::GetArrField( $sett, array( 'contPr', 'img', 'redirCacheAdapt' ), false );

		$redir = false;
		foreach( array_reverse( array( 'webp','avif' ) ) as $comprType )
		{
			if( !Gen::GetArrField( $sett, array( 'contPr', 'img', $comprType, 'redir' ), false ) )
				continue;

			$redir = true;

			if( $confComprRedirBlock )
				$confComprRedirBlock .= "\n";

			$confComprRedirBlock .=
				"\t" . '# ' . $comprType . '' . "\n\t" . 'types { image/' . $comprType . ' ' . $comprType . '; }' . "\n\t" . 'set $' . $comprType . '_redir "";' . "\n\t" . 'if ($http_accept ~* "image\\/' . $comprType . '") { set $' . $comprType . '_redir "${' . $comprType . '_redir}A"; }' . "\n\t" . 'if (-f $request_filename.' . $comprType . ') { set $' . $comprType . '_redir "${' . $comprType . '_redir}F"; set $any_redir "R"; }' . "\n\t" . 'if ($' . $comprType . '_redir = "AF") { add_header Vary Accept;' . ( Gen::GetArrField( $sett, array( 'hdrTrace' ), false ) ? ' add_header X-Seraph-Accel-Cache "state=preoptimized; redir=conf;";' : '' ) . ' rewrite (.*) $1.' . $comprType . ( $redirCacheAdapt ? ' redirect;' : '' ) .' break; }' . "\n" .
				'';
		}

		if( $redir )
		{
			$confComprRedirBlock =
				"\t" . 'set $any_redir "";' . "\n\n" .
				$confComprRedirBlock .
				'';

			$confComprRedirBlock .=
				"\n\t" . 'if ($any_redir = "") { add_header Vary Accept;' . ( Gen::GetArrField( $sett, array( 'hdrTrace' ), false ) ? ' add_header X-Seraph-Accel-Cache "state=original; redir=conf;";' : '' ) . ' }' . "\n" .
				'';
		}
	}

	if( $confComprRedirBlock )
	{
		$confTypesFromBlock = '';
		foreach( array( 'jpe','jpg','jpeg','png','gif','bmp' ) as $type )
			$confTypesFromBlock .= "\t\t" . Fs::GetMimeContentType( '.' . $type ) . "\t" . $type . ';' . "\n";

		$confComprRedirBlock =
			'location ~ .*\.(' . $imgTypesCnvFrom_RegExpEnum . ')$' . "\n" .
			'{' . "\n\t" . 'types' . "\n\t" . '{' . "\n" .
			$confTypesFromBlock .
			"\t" . '}' . "\n\n" .
			$confComprRedirBlock .
			'}' . "\n" .
			'';
	}
	else
		$confComprRedirBlock =
			'# Empty' . "\n";

	$confComprRedirBlock = '# seraphinite-accelerator - Automatic redirection to Avif and WebP versions if they exist' . "\n" . $confComprRedirBlock;
	return( $confComprRedirBlock );
}

function _OpCache_Invalidate( $file )
{

	switch( OnAsyncTasksPushGetMode() )
	{
	case 're':
	case 're_r':
		PluginRe::OpCacheReset();
		break;
	}

	if( function_exists( 'opcache_invalidate' ) )
		@opcache_invalidate( $file, true );
}

function CacheInitEnvDropin( $sett, $init = true )
{
	$file = WP_CONTENT_DIR . '/advanced-cache.php';
	$cont = @file_get_contents( $file );

	if( !$init )
	{
		if( $cont && strpos( $cont, '/* seraphinite-accelerator */' ) !== false )
		{
			@file_put_contents( $file, '<?php /* Disabled by seraphinite-accelerator */' );
			_OpCache_Invalidate( $file );
		}

		return( Gen::S_OK );
	}

	$contNew = GetAdvCacheFileContent( $sett );

	$hr = Gen::S_OK;
	if( $cont != $contNew )
	{
		$hr = Gen::HrAccom( $hr, @file_put_contents( $file, $contNew ) !== false ? Gen::S_OK : Gen::E_FAIL );
		_OpCache_Invalidate( $file );
	}

	return( $hr );
}

function IsWpCacheActive()
{
	return( defined( 'WP_CACHE' ) && WP_CACHE );
}

function CacheInitEnv( $sett, $init = true )
{
	$cacheEnable = Gen::GetArrField( $sett, 'cache/enable', true, '/' );

	$reFile = PluginRe::GetRootFileName();

	if( !$cacheEnable || !$init )
	{
		CacheInitEnvDropin( $sett, false );

		CacheInitClearProcessor( true, false );
		CacheInitOperScheduler( true, false );
	}

	if( !$init )
	{
		if( Gen::HtAccess_IsSupported() )
			Gen::HtAccess_SetBlock( 'seraphinite-accelerator', '' );

		{
			$confComprRedirBlock = CacheGetEnvNginxConf( array() );

			$fileConfComprRedir = Wp::GetHomePath() . 'seraph-accel-img-compr-redir.conf';
			if( @file_get_contents( $fileConfComprRedir ) !== $confComprRedirBlock )
				@file_put_contents( $fileConfComprRedir, $confComprRedirBlock );
		}

		@unlink( $reFile );

		return( Gen::S_OK );
	}

	$hr = Gen::S_OK;

	if( $cacheEnable )
	{
		$hr = Gen::HrAccom( $hr, CacheInitEnvDropin( $sett ) );
		if( !IsWpCacheActive() )
		{
			$hr = Gen::HrAccom( $hr, Php::File_SetDefineVal( Wp::GetConfigFilePath(), 'WP_CACHE', true ) );
			if( Gen::HrSucc( $hr ) )
			    @define( 'WP_CACHE', true );
		}

		$hr = Gen::HrAccom( $hr, Gen::MakeDir( GetCacheDir(), true ) );
	}

	$imgTypesCnvFrom_RegExpEnum = implode( '|', array( 'jpe','jpg','jpeg','png','gif','bmp', 'webp','avif' ) );
	if( Gen::HtAccess_IsSupported() )
	{
		$htaccessBlock = '';

		if( Gen::GetArrField( $sett, 'cacheBr/enable', false, '/' ) )
		{
			$tmStr = '"access plus ' . Gen::GetArrField( $sett, 'cacheBr/timeout', 0, '/' ) . ' minutes"';

			$htaccessBlock .=
				'<IfModule mod_mime.c>' . "\n\t" . 'AddType image/avif .avif' . "\n\t" . 'AddType image/webp .webp' . "\n\t" . 'AddType application/font-woff2 .woff2' . "\n\t" . 'AddType application/x-font-opentype .otf' . "\n" .
				'</IfModule>' . "\n" .
				'<IfModule mod_expires.c>' . "\n\t" . 'ExpiresActive on' . "\n\t" . 'ExpiresByType text/css                      ' . $tmStr . "\n\t" . 'ExpiresByType text/javascript               ' . $tmStr . "\n\t" . 'ExpiresByType application/javascript        ' . $tmStr . "\n\t" . 'ExpiresByType application/x-javascript      ' . $tmStr . "\n\t" . 'ExpiresByType font/eot                      ' . $tmStr . "\n\t" . 'ExpiresByType font/opentype                 ' . $tmStr . "\n\t" . 'ExpiresByType font/woff                     ' . $tmStr . "\n\t" . 'ExpiresByType application/vnd.ms-fontobject ' . $tmStr . "\n\t" . 'ExpiresByType application/font-woff         ' . $tmStr . "\n\t" . 'ExpiresByType application/font-woff2        ' . $tmStr . "\n\t" . 'ExpiresByType application/x-font-ttf        ' . $tmStr . "\n\t" . 'ExpiresByType application/x-font-woff       ' . $tmStr . "\n\t" . 'ExpiresByType image/vnd.microsoft.icon      ' . $tmStr . "\n\t" . 'ExpiresByType image/x-icon                  ' . $tmStr . "\n\t" . 'ExpiresByType image/bmp                     ' . $tmStr . "\n\t" . 'ExpiresByType image/gif                     ' . $tmStr . "\n\t" . 'ExpiresByType image/jpeg                    ' . $tmStr . "\n\t" . 'ExpiresByType image/png                     ' . $tmStr . "\n\t" . 'ExpiresByType image/svg+xml                 ' . $tmStr . "\n\t" . 'ExpiresByType image/avif                    ' . $tmStr . "\n\t" . 'ExpiresByType image/webp                    ' . $tmStr . "\n\t" . 'ExpiresByType audio/ogg                     ' . $tmStr . "\n\t" . 'ExpiresByType video/mp4                     ' . $tmStr . "\n\t" . 'ExpiresByType video/ogg                     ' . $tmStr . "\n\t" . 'ExpiresByType video/webm                    ' . $tmStr . "\n" .
				'</IfModule>' . "\n" .
				'';

			if( !Gen::GetArrField( $sett, array( 'cache', 'chkNotMdfSince' ), false ) )
			{

				$htaccessBlock .=
					'<IfModule mod_headers.c>' . "\n\t" . 'Header unset ETag' . "\n" .
					'</IfModule>' . "\n" .
					'FileETag None' . "\n" .
					'';
			}
		}

		if( !Gen::GetArrField( $sett, array( 'contPr', 'img', 'redirOwn' ), false ) )
		{
			$redirCacheAdapt = Gen::GetArrField( $sett, array( 'contPr', 'img', 'redirCacheAdapt' ), false );

			$htaccessBlockRedir = '';
			foreach( array_reverse( array( 'webp','avif' ) ) as $comprType )
			{
				if( !Gen::GetArrField( $sett, array( 'contPr', 'img', $comprType, 'redir' ), false ) )
					continue;

				$htaccessBlockRedir .=
					'<IfModule mod_rewrite.c>' . "\n\t" . 'RewriteEngine On' . "\n\t" . 'RewriteCond %{HTTP_ACCEPT} image\\/' . $comprType . "\n\t" . 'RewriteCond %{REQUEST_FILENAME} \\.(' . $imgTypesCnvFrom_RegExpEnum . ')$' . "\n\t" . 'RewriteCond %{REQUEST_FILENAME}.' . $comprType . ' -f' . "\n\t" . 'RewriteRule ^(.*)\\.(' . $imgTypesCnvFrom_RegExpEnum . ')$ $1\\.$2\\.' . $comprType . ' [QSA' . ( $redirCacheAdapt ? ',R' : '' ) . ']' . "\n" .
					'</IfModule>' . "\n" .
					'<IfModule mod_headers.c>' . "\n\t" . '<FilesMatch \\.(' . $imgTypesCnvFrom_RegExpEnum . ')\\.' . $comprType . '$>' . "\n\t\t" . 'Header merge Vary Accept' . "\n" .
					( Gen::GetArrField( $sett, array( 'hdrTrace' ), false ) && !( @preg_match( '@IdeaWebServer@i', (isset($_SERVER[ 'SERVER_SOFTWARE' ])?$_SERVER[ 'SERVER_SOFTWARE' ]:'') ) ) ? "\t\t" . 'Header set X-Seraph-Accel-Cache "state=preoptimized; redir=htaccess;"' . "\n" : '' ) .
					"\t" . '</FilesMatch>' . "\n" .
					'</IfModule>' . "\n" .
					'';
			}

			if( $htaccessBlockRedir )
			{
				$htaccessBlock .=
					'<IfModule mod_headers.c>' . "\n\t" . '<FilesMatch \\.(' . $imgTypesCnvFrom_RegExpEnum . ')$>' . "\n\t\t" . 'Header merge Vary Accept' . "\n" .
					( Gen::GetArrField( $sett, array( 'hdrTrace' ), false ) && !( @preg_match( '@IdeaWebServer@i', (isset($_SERVER[ 'SERVER_SOFTWARE' ])?$_SERVER[ 'SERVER_SOFTWARE' ]:'') ) ) ? "\t\t" . 'Header set X-Seraph-Accel-Cache "state=original; redir=htaccess;"' . "\n" : '' ) .
					"\t" . '</FilesMatch>' . "\n" .
					'</IfModule>' . "\n" .
					$htaccessBlockRedir;
			}
		}

		{
			$encs = Gen::GetArrField( $sett, array( 'cache', 'encs' ), array() );
			$mimeTypes = array(
				'text/plain',

				'text/css',

				'text/javascript',
				'application/javascript',
				'application/x-javascript',
				'application/json',

				'text/html',
				'text/xml',
				'application/atom+xml',
				'application/rss+xml',
				'application/xhtml+xml',
				'application/xml',
				'text/x-component',

				'application/vnd.ms-fontobject',
				'application/x-font-ttf',
				'font/eot',
				'font/opentype',

				'image/bmp',
				'image/svg+xml',
				'image/vnd.microsoft.icon',
				'image/x-icon',
			);

			if( in_array( 'br', $encs ) )
			{
				$htaccessBlock .=
					'<IfModule mod_brotli.c>' . "\n\t" . '<IfModule mod_filter.c>' . "\n\t\t" . 'AddOutputFilterByType BROTLI_COMPRESS';

				foreach( $mimeTypes as $mimeType )
					$htaccessBlock .= ' ' . $mimeType;

				$htaccessBlock .=
					"\n\t" . '</IfModule>' . "\n" .
					'</IfModule>' . "\n" .
					'';
			}

			if( in_array( 'gzip', $encs ) || in_array( 'deflate', $encs ) || in_array( 'compress', $encs ) )
			{
				$htaccessBlock .=
					'<IfModule mod_deflate.c>' . "\n\t" . '<IfModule mod_filter.c>' . "\n\t\t" . 'AddOutputFilterByType DEFLATE';

				foreach( $mimeTypes as $mimeType )
					$htaccessBlock .= ' ' . $mimeType;

				$htaccessBlock .=
					"\n\t" . '</IfModule>' . "\n" .
					'</IfModule>' . "\n" .
					'';
			}
		}

		if( UseGzAssets( Gen::GetArrField( $sett, array( 'cache' ), array() ) ) )
		{
			$dataComprs = Gen::GetArrField( $sett, array( 'cache', 'dataCompr' ), array() );

			if( in_array( 'brotli', $dataComprs ) )
			{
				$htaccessBlock .=
					'<IfModule mod_headers.c>' . "\n\t" . '<IfModule mod_rewrite.c>' . "\n\t\t" . 'RewriteEngine On' . "\n\t\t" . 'RewriteCond %{HTTP:Accept-Encoding} (^|\\W)br(\\W|$)' . "\n\t\t" . 'RewriteCond %{REQUEST_FILENAME} \\.(css|js)$' . "\n\t\t" . 'RewriteCond %{REQUEST_FILENAME}.br -f' . "\n\t\t" . 'RewriteRule ^(.*)\\.(css|js)$ $1\\.$2\\.br [QSA]' . "\n\t\t" . 'RewriteRule \\.css\\.br$ - [T=text/css,E=no-gzip:1,E=no-brotli:1]' . "\n\t\t" . 'RewriteRule \\.js\\.br$ - [T=application/javascript,E=no-gzip:1,E=no-brotli:1]' . "\n\t" . '</IfModule>' . "\n\t" . '<FilesMatch \\.(js|css)\\.br$>' . "\n\t\t" . 'Header set Content-Encoding br' . "\n\t\t" . 'Header merge Vary Accept-Encoding' . "\n" .
					( Gen::GetArrField( $sett, array( 'hdrTrace' ), false ) && !( @preg_match( '@IdeaWebServer@i', (isset($_SERVER[ 'SERVER_SOFTWARE' ])?$_SERVER[ 'SERVER_SOFTWARE' ]:'') ) ) ? "\t\t" . 'Header set X-Seraph-Accel-Cache "state=precompressed; redir=htaccess;"' . "\n" : '' ) .
					"\t" . '</FilesMatch>' . "\n" .
					'</IfModule>' . "\n" .
					'';
			}

			if( in_array( 'deflate', $dataComprs ) && !( @preg_match( '@IdeaWebServer@i', (isset($_SERVER[ 'SERVER_SOFTWARE' ])?$_SERVER[ 'SERVER_SOFTWARE' ]:'') ) ) )
			{
				$htaccessBlock .=
					'<IfModule mod_headers.c>' . "\n\t" . '<IfModule mod_rewrite.c>' . "\n\t\t" . 'RewriteEngine On' . "\n\t\t" . 'RewriteCond %{HTTP:Accept-Encoding} (^|\\W)gzip(\\W|$)' . "\n\t\t" . 'RewriteCond %{REQUEST_FILENAME} \\.(css|js)$' . "\n\t\t" . 'RewriteCond %{REQUEST_FILENAME}.gz -f' . "\n\t\t" . 'RewriteRule ^(.*)\\.(css|js)$ $1\\.$2\\.gz [QSA]' . "\n\t\t" . 'RewriteRule \\.css\\.gz$ - [T=text/css,E=no-gzip:1,E=no-brotli:1]' . "\n\t\t" . 'RewriteRule \\.js\\.gz$ - [T=application/javascript,E=no-gzip:1,E=no-brotli:1]' . "\n\t" . '</IfModule>' . "\n\t" . '<FilesMatch \\.(js|css)\\.gz$>' . "\n\t\t" . 'Header set Content-Encoding gzip' . "\n\t\t" . 'Header merge Vary Accept-Encoding' . "\n" .
					( Gen::GetArrField( $sett, array( 'hdrTrace' ), false ) && !( @preg_match( '@IdeaWebServer@i', (isset($_SERVER[ 'SERVER_SOFTWARE' ])?$_SERVER[ 'SERVER_SOFTWARE' ]:'') ) ) ? "\t\t" . 'Header set X-Seraph-Accel-Cache "state=precompressed; redir=htaccess;"' . "\n" : '' ) .
					"\t" . '</FilesMatch>' . "\n" .
					'</IfModule>' . "\n" .
					'';
			}
		}

		$htaccessBlock = trim( $htaccessBlock );

		if( Gen::HtAccess_GetBlock( 'seraphinite-accelerator' ) != $htaccessBlock )
			$hr = Gen::HrAccom( $hr, Gen::HtAccess_SetBlock( 'seraphinite-accelerator', $htaccessBlock, 5 ) );
	}

	{
		$confComprRedirBlock = CacheGetEnvNginxConf( $sett );

		$fileConfComprRedir = Wp::GetHomePath() . 'seraph-accel-img-compr-redir.conf';
		if( @file_get_contents( $fileConfComprRedir ) !== $confComprRedirBlock )
			@file_put_contents( $fileConfComprRedir, $confComprRedirBlock );
	}

	if( Gen::GetArrField( $sett, 'asyncMode', '', '/' ) === 're_r' )
	{
		$reFileCont = PluginRe::GetRootFileContent();

		if( @file_get_contents( $reFile ) !== $reFileCont )
			@file_put_contents( $reFile, $reFileCont );
	}
	else
		@unlink( $reFile );

	return( $hr );
}

function GetCacheStatusInfo( $siteId, $cbCancel )
{
	$info = CacheGetInfo( $siteId, $cbCancel );
	if( !$info )
		return( null );

	return( $info );
}

function _AddSiteIdSites( &$sitesIds, $addrSite, $siteId, $availablePlugins )
{
	$sitesIds[ $addrSite ] = $siteId;

	$aAddrSite = array();

	if( in_array( 'sitepress-multilingual-cms', $availablePlugins ) )
	{
		$sitePath = Gen::GetArrField( Net::UrlParse( 'http://' . $addrSite ), array( 'path' ) );
		foreach( Gen::GetArrField( get_option( 'icl_sitepress_settings' ), array( 'language_domains' ), array() ) as $lang => $domain )
			$aAddrSite[] = rtrim( $domain . $sitePath, '/' );
	}

	if( in_array( 'polylang', $availablePlugins ) || in_array( 'polylang-pro', $availablePlugins ) )
	{
		$plgOpts = Gen::GetArrField( get_option( 'polylang' ), array( '' ), array() );
		$forceLang = Gen::GetArrField( $plgOpts, array( 'force_lang' ) );
		if( $forceLang == 2 || $forceLang == 3 )
		{
			foreach( Gen::GetArrField( $plgOpts, array( 'domains' ), array() ) as $lang => $url )
			{
				if( $forceLang == 2 )
					$aAddrSite[] = $lang . '.' . $addrSite;
				else if( $urlComps = Net::UrlParse( $url ) )
					$aAddrSite[] = trim( Net::UrlDeParse( $urlComps, 0, array(), array( PHP_URL_HOST, PHP_URL_PORT, PHP_URL_PATH ) ), '/' );
			}
		}
	}

	if( in_array( 'multiple-domain', $availablePlugins ) )
	{
		foreach( Gen::GetArrField( get_option( 'multiple-domain-domains' ), array( '' ), array() ) as $domain => $opts )
		{
			$domain = rtrim( $domain, '/' );
			$path = trim( Gen::GetArrField( $opts, array( 'base' ), '' ), '/' );
			if( strlen( $path ) )
				$domain .= '/' . $path;
			$aAddrSite[] = $domain;
		}
	}

	if( defined( 'SERAPH_ACCEL_ALT_ROOTS' ) )
	{
		foreach( ( array )SERAPH_ACCEL_ALT_ROOTS as $url )
			if( $urlComps = Net::UrlParse( $url ) )
				$aAddrSite[] = trim( Net::UrlDeParse( $urlComps, 0, array(), array( PHP_URL_HOST, PHP_URL_PORT, PHP_URL_PATH ) ), '/' );
	}

	foreach( $aAddrSite as $addr )
		if( !isset( $sitesIds[ $addr ] ) )
			$sitesIds[ $addr ] = $siteId . '-' . md5( $addr );

}

function GetAdvCacheFileContent( $sett )
{
	$content = '<?php' . "\n";
	$content .= '/* seraphinite-accelerator */' . "\n";

	$availablePlugins = Plugin::GetAvailablePlugins();

	$sitesIds = array();
	if( Gen::DoesFuncExist( 'get_sites' ) && is_multisite() )
	{
		foreach( get_sites() as $site )
		{
			switch_to_blog( $site -> blog_id );

			$addrSite = strtolower( Net::GetUrlWithoutProto( Gen::SetLastSlash( Wp::GetSiteRootUrl(), false ) ) );
			$siteId = GetSiteId( $site );
			_AddSiteIdSites( $sitesIds, $addrSite, $siteId, $availablePlugins );

			Plugin::SettCacheClear();
			$settSite = Plugin::SettGet();
			$content .= 'function _seraph_accel_siteSettInlineDetach_' . $siteId . '(){ return ' . var_export( $settSite, true ) . '; }' . "\n";

			restore_current_blog();
		}

		$content .= 'function seraph_accel_siteSettInlineDetach($siteId){ $fn = \'_seraph_accel_siteSettInlineDetach_\' . $siteId; return function_exists($fn) ? call_user_func($fn) : null; }' . "\n";
	}
	else
	{
		$content .= 'function seraph_accel_siteSettInlineDetach($siteId){ return ' . var_export( $sett, true ) . '; }' . "\n";
		_AddSiteIdSites( $sitesIds, Net::GetUrlWithoutProto( Gen::SetLastSlash( Wp::GetSiteRootUrl(), false ) ), 'm', $availablePlugins );
	}

	$content .= '$seraph_accel_sites = ' . var_export( $sitesIds, true ) . ';' . "\n";

	$content .= '@include(WP_CONTENT_DIR . \'/plugins/' . Plugin::GetCurBaseName( false ) . '/cache.php\');' . "\n";
	$content .= '?>';
	return( $content );
}

function GetLoadAvg( $def = 0 )
{
	if( !function_exists( 'sys_getloadavg' ) )
		return( $def );

	$loadavg = sys_getloadavg();
	if( !is_array( $loadavg ) )
		return( $def );

	$loadavg = ( float )(isset($loadavg[ 0 ])?$loadavg[ 0 ]:null);
	if( $loadavg > 1 )
		$loadavg = 1;
	return( $loadavg !== null ? ( int )( round( 100 * $loadavg ) ) : $def );
}

function UpdateClientSessId( $curUserId, $token = null, $expirationNew = null )
{
	$siteId = GetSiteId();
	$tmCur = Gen::GetCurRequestTime();

	$sessInfo = GetCacheCurUserSession( $siteId );
	$sessId = (isset($sessInfo[ 'sessId' ])?$sessInfo[ 'sessId' ]:null);

	if( $curUserId )
	{
		if( (isset($sessInfo[ 'userSessId' ])?$sessInfo[ 'userSessId' ]:null) != $token || (isset($sessInfo[ 'expiration' ])?$sessInfo[ 'expiration' ]:null) != $expirationNew || (isset($sessInfo[ 'userId' ])?$sessInfo[ 'userId' ]:null) != $curUserId )
		{
			if( Gen::IsEmpty( $sessId ) )
				$sessId = wp_generate_password( 43, false, false );
			SetCacheCurUserSession( $siteId, $sessId, $token, $curUserId, $expirationNew );
		}
	}
	else if( Gen::IsEmpty( $sessId ) )
	{
		$set = false;
		{
			$cacheSkipData = GetContCacheEarlySkipData( $pathOrig, $path, $pathIsDir, $args );
			if( $cacheSkipData )
			{
				if( $cacheSkipData === array( 'skipped', array( 'reason' => 'noCacheSession' ) ) )
					$set = true;
			}
			else
			{
				$settCache = Gen::GetArrField( Plugin::SettGet(), array( 'cache' ), array() );
				if( ContProcGetExclStatus( $siteId, $settCache, $path, $pathOrig, $pathIsDir, $args, $varsOut, false, !(isset($settCache[ 'enable' ])?$settCache[ 'enable' ]:null) ) == 'noCacheSession' )
					$set = true;
			}
		}

		if( $set )
		{
			$sessId = wp_generate_password( 43, false, false );
			SetCacheCurUserSession( $siteId, $sessId, '0', 0, $tmCur + 12 * HOUR_IN_SECONDS );
		}
	}
	else if( (isset($sessInfo[ 'userId' ])?$sessInfo[ 'userId' ]:null) || (isset($sessInfo[ 'expiration' ])?$sessInfo[ 'expiration' ]:null) < $tmCur )
		SetCacheCurUserSession( $siteId, $sessId, '0', 0, $tmCur + 12 * HOUR_IN_SECONDS );
}

