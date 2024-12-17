<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

function _Scripts_EncodeBodyAsSrc( $cont )
{

	$cont = str_replace( "%", '%25', $cont );

	$cont = str_replace( "\n", '%0A', $cont );
	$cont = str_replace( "#", '%23', $cont );
	$cont = str_replace( "\"", '%22', $cont );

	return( $cont );
}

function IsScriptTypeJs( $type )
{
	return( !$type || $type == 'application/javascript' || $type == 'text/javascript' || $type == 'module' );
}

function Script_SrcAddPreloading( $item, $src, $head, $doc )
{
	if( !$src )
		return;

	$itemPr = $doc -> createElement( 'link' );
	$itemPr -> setAttribute( 'rel', ( $item -> getAttribute( 'data-type' ) == 'module' || $item -> getAttribute( 'type' ) == 'module' ) ? 'modulepreload' : 'preload' );
	$itemPr -> setAttribute( 'as', $item -> tagName == 'IFRAME' ? 'document' : 'script' );
	$itemPr -> setAttribute( 'href', $src );
	$itemPr -> setAttribute( 'fetchpriority', 'low' );
	if( $item -> hasAttribute( 'integrity' ) )
		$itemPr -> setAttribute( "integrity", $item -> getAttribute( "integrity" ) );
	if( $item -> hasAttribute( "crossorigin" ) )
		$itemPr -> setAttribute( "crossorigin", $item -> getAttribute( "crossorigin" ) );
	$head -> appendChild( $itemPr );
}

function Scripts_Process( &$ctxProcess, $sett, $settCache, $settContPr, $settJs, $settCdn, $doc )
{
	if( (isset($ctxProcess[ 'isAMP' ])?$ctxProcess[ 'isAMP' ]:null) )
	    return( true );

	$optLoad = Gen::GetArrField( $settJs, array( 'optLoad' ), false );
	$skips = Gen::GetArrField( $settJs, array( 'skips' ), array() );

	if( !( $optLoad || Gen::GetArrField( $settJs, array( 'groupNonCrit' ), false ) || Gen::GetArrField( $settJs, array( 'min' ), false ) || Gen::GetArrField( $settCdn, array( 'enable' ), false ) || $skips ) )
		return( true );

	if( (isset($ctxProcess[ 'compatView' ])?$ctxProcess[ 'compatView' ]:null) )
		$optLoad = false;

	$head = $ctxProcess[ 'ndHead' ];
	$body = $ctxProcess[ 'ndBody' ];

	$aGrpExcl = Gen::GetArrField( $settJs, array( 'groupExcls' ), array() );
	$notCritsDelayTimeout = Gen::GetArrField( $settJs, array( 'nonCrit', 'timeout', 'enable' ), false ) ? Gen::GetArrField( $settJs, array( 'nonCrit', 'timeout', 'v' ), 0 ) : null;

	$critSpecsDelayTimeout = Gen::GetArrField( $settJs, array( 'critSpec', 'timeout', 'enable' ), false ) ? Gen::GetArrField( $settJs, array( 'critSpec', 'timeout', 'v' ), 0 ) : null;
	$critSpec = array();
	if( $critSpecsDelayTimeout !== null )
	{
		$critSpec = Gen::GetArrField( $settJs, array( 'critSpec', 'items' ), array() );
		if( isset( $ctxProcess[ 'aJsCritSpec' ] ) )
		{
			foreach( array_keys( $ctxProcess[ 'aJsCritSpec' ] ) as $expr )
				if( !in_array( $expr, $critSpec ) )
					$critSpec[] = $expr;
		}

		$critSpec = array_map( function( $v ) { return( $v . 'S' ); }, $critSpec );
	}

	$specsDelayTimeout = Gen::GetArrField( $settJs, array( 'spec', 'timeout', 'enable' ), false ) ? Gen::GetArrField( $settJs, array( 'spec', 'timeout', 'v' ), 0 ) : null;
	$specs = ( ( $notCritsDelayTimeout !== null && $specsDelayTimeout ) || ( $notCritsDelayTimeout === null && $specsDelayTimeout !== null ) ) ? Gen::GetArrField( $settJs, array( 'spec', 'items' ), array() ) : array();
	{
		$specs = array_map( function( $v ) { return( $v . 'S' ); }, $specs );
	}

	$settNonCrit = Gen::GetArrField( $settJs, array( 'nonCrit' ), array() );
	{
		$aItems = Gen::GetArrField( $settNonCrit, array( 'items' ), array() );

		if( isset( $ctxProcess[ 'aJsCrit' ] ) )
		{
			foreach( array_keys( $ctxProcess[ 'aJsCrit' ] ) as $expr )
				if( !in_array( $expr, $aItems ) )
					$aItems[] = $expr;
		}

		$aItems = array_map( function( $v ) { return( $v . 'S' ); }, $aItems );

		Gen::SetArrField( $settNonCrit, array( 'items' ), $aItems );
		unset( $aItems );
	}

	$delayNotCritNeeded = false;
	$delaySpecNeeded = false;

	$items = HtmlNd::ChildrenAsArr( $doc -> getElementsByTagName( 'script' ) );

	$contGroups = array( 'crit' => array( array( 0, 0 ), array( '' ) ), 'critSpec' => array( array( 0, 0 ), array( '' ) ), '' => array( array( 0, 0 ), array( '' ) ), 'spec' => array( array( 0, 0 ), array( '' ) ) );

	foreach( $items as $item )
	{
		if( ContentProcess_IsAborted( $settCache ) ) return( true );

		$type = HtmlNd::GetAttrVal( $item, 'type' );
		if( !IsScriptTypeJs( $type ) )
			continue;

		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
		{
			if( !$type )
				$item -> setAttribute( 'type', $type = 'text/javascript' );
		}
		else if( $type && (isset($settContPr[ 'min' ])?$settContPr[ 'min' ]:null) && $type != 'module' )
		{
			$item -> removeAttribute( 'type' );
			$type = null;
		}

		$src = HtmlNd::GetAttrVal( $item, 'src' );
		$id = HtmlNd::GetAttrVal( $item, 'id' );
		$cont = $item -> nodeValue;

		{

		}

		$detectedPattern = null;
		if( IsObjInRegexpList( $skips, array( 'src' => $src, 'id' => $id, 'body' => $cont ), $detectedPattern ) )
		{
			if( (isset($ctxProcess[ 'debug' ])?$ctxProcess[ 'debug' ]:null) )
			{
				$item -> setAttribute( 'type', 'o/js-inactive' );
				$item -> setAttribute( 'seraph-accel-debug', 'status=skipped;' . ( $detectedPattern ? ' detectedPattern="' . $detectedPattern . '"' : '' ) );
			}
			else
				$item -> parentNode -> removeChild( $item );
			continue;
		}

		$detectedPattern = null;
		if( $src )
		{
			$srcInfo = GetSrcAttrInfo( $ctxProcess, null, null, $src );

			if( (isset($srcInfo[ 'filePath' ])?$srcInfo[ 'filePath' ]:null) && Gen::GetFileExt( $srcInfo[ 'filePath' ] ) == 'js' )
				$cont = @file_get_contents( $srcInfo[ 'filePath' ] );
			if( !$cont )
			{
				$cont = GetExtContents( (isset($srcInfo[ 'url' ])?$srcInfo[ 'url' ]:null), $contMimeType );
				if( $cont !== false && !in_array( $contMimeType, array( 'text/javascript', 'application/x-javascript', 'application/javascript' ) ) )
				{
					$cont = false;
					if( (isset($sett[ 'debug' ])?$sett[ 'debug' ]:null) )
						LastWarnDscs_Add( LocId::Pack( 'JsUrlWrongType_%1$s%2$s', null, array( $srcInfo[ 'url' ], $contMimeType ) ) );
				}
			}

			$isCrit = $item -> hasAttribute( 'seraph-accel-crit' ) ? true : GetObjSrcCritStatus( $settNonCrit, $critSpec, $specs, $srcInfo, $src, $id, $cont, $detectedPattern );

			if( Script_AdjustCont( $ctxProcess, $settCache, $settJs, $srcInfo, $src, $id, $cont ) )
			{
				if( (isset($ctxProcess[ 'debug' ])?$ctxProcess[ 'debug' ]:null) )
					$cont = '// ################################################################################################################################################' . "\r\n" . '// DEBUG: seraph-accel JS src="' . $src . '"' . "\r\n\r\n" . $cont;

				if( !UpdSc( $ctxProcess, $settCache, 'js', $cont, $src ) )
					return( false );
			}

			Cdn_AdjustUrl( $ctxProcess, $settCdn, $src, 'js' );
			Fullness_AdjustUrl( $ctxProcess, $src, (isset($srcInfo[ 'srcUrlFullness' ])?$srcInfo[ 'srcUrlFullness' ]:null) );

			$item -> setAttribute( 'src', $src );
		}
		else
		{
			if( !$cont )
				continue;

			$isCrit = $item -> hasAttribute( 'seraph-accel-crit' ) ? true : GetObjSrcCritStatus( $settNonCrit, $critSpec, $specs, null, null, $id, $cont, $detectedPattern );

			if( Script_AdjustCont( $ctxProcess, $settCache, $settJs, null, null, $id, $cont ) )
			{
				if( (isset($ctxProcess[ 'debug' ])?$ctxProcess[ 'debug' ]:null) )
					$cont = '// ################################################################################################################################################' . "\r\n" . '// DEBUG: seraph-accel JS src="inline:' . (isset($ctxProcess[ 'serverArgs' ][ 'REQUEST_SCHEME' ])?$ctxProcess[ 'serverArgs' ][ 'REQUEST_SCHEME' ]:null) . '://' . $ctxProcess[ 'host' ] . ':' . (isset($ctxProcess[ 'serverArgs' ][ 'SERVER_PORT' ])?$ctxProcess[ 'serverArgs' ][ 'SERVER_PORT' ]:null) . (isset($ctxProcess[ 'serverArgs' ][ 'REQUEST_URI' ])?$ctxProcess[ 'serverArgs' ][ 'REQUEST_URI' ]:null) . ':' . $item -> getLineNo() . '"' . "\r\n\r\n" . $cont;

				HtmlNd::SetValFromContent( $item, $cont );
			}
		}

		ContUpdateItemIntegrity( $item, $cont );

		if( (isset($ctxProcess[ 'debug' ])?$ctxProcess[ 'debug' ]:null) )
			$item -> setAttribute( 'seraph-accel-debug', 'status=' . ( $isCrit === true ? 'critical' : ( $isCrit === 'critSpec' ? 'criticalSpecial' : ( $isCrit === null ? 'special' : 'nonCritical' ) ) ) . ';' . ( $detectedPattern ? ' detectedPattern="' . $detectedPattern . '"' : '' ) );

		$delay = 0;
		if( $optLoad )
		{
			if( !$isCrit )
			{
				$parentNode = $item -> parentNode;
				$async = $item -> hasAttribute( 'async' );

				$delay = ( $isCrit === null ) ? $specsDelayTimeout : $notCritsDelayTimeout;

				if( $delay === 0 && ( !$async || ( $parentNode === $head || $parentNode === $body ) ) )
					$body -> appendChild( $item );
			}
			else if( $isCrit === 'critSpec' && !$item -> hasAttribute( 'async' ) )
			{
				$item -> setAttribute( 'defer', '' );
				if( !$src )
				{
					$src = 'data:text/javascript,' . _Scripts_EncodeBodyAsSrc( $cont );
					$item -> nodeValue = '';
					$item -> setAttribute( 'src', $src );
				}
			}

		}

		if( (isset($ctxProcess[ 'chunksEnabled' ])?$ctxProcess[ 'chunksEnabled' ]:null) )
			ContentMarkSeparate( $item, false );

		if( $delay )
		{
			if( $type )
				$item -> setAttribute( 'data-type', $type );

			if( $isCrit === null )
			{

				$item -> setAttribute( 'type', 'o/js-lzls' );
				$delaySpecNeeded = true;
			}
			else
			{

				$item -> setAttribute( 'type', 'o/js-lzl' );
				$delayNotCritNeeded = true;
			}
		}

		if( !(isset($ctxProcess[ 'compatView' ])?$ctxProcess[ 'compatView' ]:null) && (isset($settJs[ $isCrit === true ? 'group' : ( $isCrit === 'critSpec' ? 'groupCritSpec' : ( $isCrit === null ? 'groupSpec' : 'groupNonCrit' ) ) ])?$settJs[ $isCrit === true ? 'group' : ( $isCrit === 'critSpec' ? 'groupCritSpec' : ( $isCrit === null ? 'groupSpec' : 'groupNonCrit' ) ) ]:null) )
		{
			if( $ctxProcess[ 'mode' ] == 'full' )
			{
				if( (isset($ctxProcess[ 'debug' ])?$ctxProcess[ 'debug' ]:null) && is_string( $cont ) )
					$cont = '/* ################################################################################################################################################ */' . "\r\n" . '/* DEBUG: seraph-accel JS src="' . $src . '" */' . "\r\n\r\n" . $cont;

				$bGrpExcl = ( Gen::GetArrField( $settJs, array( 'groupExclMdls' ) ) && $type == 'module' ) || IsObjInRegexpList( $aGrpExcl, array( 'src' => $src, 'id' => $id, 'body' => $cont ) );

				if( $cont === false || $bGrpExcl )
					$cont = '';

				if( strlen( $cont ) )
				{

					if( substr( $cont, -1, 1 ) == ';' )
						$cont .= "\r\n";
					else
						$cont .= ";\r\n";

					if( (isset($ctxProcess[ 'chunksEnabled' ])?$ctxProcess[ 'chunksEnabled' ]:null) && Gen::GetArrField( $settCache, array( 'chunks', 'js' ) ) )
						$cont .= ContentMarkGetSep();

					if( $optLoad && $isCrit === false && $delayNotCritNeeded )
						$cont .= 'seraph_accel_gzjydy();';

				}

				$contGroup = &$contGroups[ $isCrit === true ? 'crit' : ( $isCrit === 'critSpec' ? 'critSpec' : ( $isCrit === null ? 'spec' : '' ) ) ];

				if( ( $item -> hasAttribute( 'defer' ) && $item -> getAttribute( 'defer' ) !== false ) && !( $item -> hasAttribute( 'async' ) && $item -> getAttribute( 'async' ) !== false ) && $src )
				{
					if( $bGrpExcl )
						array_splice( $contGroup[ 1 ], count( $contGroup[ 1 ] ), 0, array( $item, '' ) );

					$contGroup[ 1 ][ count( $contGroup[ 1 ] ) - 1 ] .= $cont;
				}
				else
				{
					if( $bGrpExcl )
					{
						array_splice( $contGroup[ 1 ], $contGroup[ 0 ][ 0 ], 1, array( substr( $contGroup[ 1 ][ $contGroup[ 0 ][ 0 ] ], 0, $contGroup[ 0 ][ 1 ] ), $item, substr( $contGroup[ 1 ][ $contGroup[ 0 ][ 0 ] ], $contGroup[ 0 ][ 1 ] ) ) );
						$contGroup[ 0 ][ 0 ] += 2;
						$contGroup[ 0 ][ 1 ] = 0;
					}

					$contGroup[ 1 ][ $contGroup[ 0 ][ 0 ] ] = substr_replace( $contGroup[ 1 ][ $contGroup[ 0 ][ 0 ] ], $cont, $contGroup[ 0 ][ 1 ], 0 );
					$contGroup[ 0 ][ 1 ] += strlen( $cont );
				}

				unset( $contGroup );
			}

			$item -> parentNode -> removeChild( $item );
		}
		else if( $delay && $isCrit === false && (isset($settJs[ 'preLoadEarly' ])?$settJs[ 'preLoadEarly' ]:null) )
			Script_SrcAddPreloading( $item, $src, $head, $doc );
	}

	if( $optLoad )
	{
		foreach( HtmlNd::ChildrenAsArr( $doc -> getElementsByTagName( 'iframe' ) ) as $item )
		{
			if( ContentProcess_IsAborted( $settCache ) ) return( true );

			if( HtmlNd::FindUpByTag( $item, 'noscript' ) )
				continue;

			if( !Scripts_IsElemAs( $ctxProcess, $doc, $settJs, $item ) )
				continue;

			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			$src = HtmlNd::GetAttrVal( $item, 'src' );
			$id = HtmlNd::GetAttrVal( $item, 'id' );
			$srcInfo = GetSrcAttrInfo( $ctxProcess, null, null, $src );

			$detectedPattern = null;
			$isCrit = GetObjSrcCritStatus( $settNonCrit, $critSpec, $specs, $srcInfo, $src, $id, null, $detectedPattern );

			Fullness_AdjustUrl( $ctxProcess, $src, (isset($srcInfo[ 'srcUrlFullness' ])?$srcInfo[ 'srcUrlFullness' ]:null) );
			$item -> setAttribute( 'src', $src );
			$item -> setAttribute( 'async', '' );

			if( (isset($ctxProcess[ 'debug' ])?$ctxProcess[ 'debug' ]:null) )
				$item -> setAttribute( 'seraph-accel-debug', 'status=' . ( $isCrit === true ? 'critical' : ( $isCrit === 'critSpec' ? 'criticalSpecial' : ( $isCrit === null ? 'special' : 'nonCritical' ) ) ) . ';' . ( $detectedPattern ? ' detectedPattern="' . $detectedPattern . '"' : '' ) );

			if( $isCrit )
				continue;

			$delay = ( $isCrit === null ) ? $specsDelayTimeout : $notCritsDelayTimeout;
			if( !$delay )
				continue;

			HtmlNd::RenameAttr( $item, 'src', 'data-src' );
			if( $isCrit === null )
			{
				$item -> setAttribute( 'type', 'o/js-lzls' );
				$delaySpecNeeded = true;
			}
			else
			{
				$item -> setAttribute( 'type', 'o/js-lzl' );
				$delayNotCritNeeded = true;
			}
		}
	}

	if( $ctxProcess[ 'mode' ] != 'full' )
		return( true );

	$itemGrpCritLast = null;
	foreach( $contGroups as $contGroupId => $contGroup )
	{
		foreach( $contGroup[ 1 ] as $cont )
		{
			if( !$cont )
				continue;

			if( is_string( $cont ) )
			{
				$item = $doc -> createElement( 'script' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$item -> setAttribute( $item, 'type', 'text/javascript' );

				if( !GetContentProcessorForce( $sett ) && (isset($ctxProcess[ 'chunksEnabled' ])?$ctxProcess[ 'chunksEnabled' ]:null) && Gen::GetArrField( $settCache, array( 'chunks', 'js' ) ) )
				{
					$idSub = ( string )( $ctxProcess[ 'subCurIdx' ]++ ) . '.js';
					$ctxProcess[ 'subs' ][ $idSub ] = $cont;
					$src = ContentProcess_GetGetPartUri( $ctxProcess, $idSub );
				}
				else
				{
					$cont = str_replace( ContentMarkGetSep(), '', $cont );
					if( !UpdSc( $ctxProcess, $settCache, 'js', $cont, $src ) )
						return( false );
				}

				Cdn_AdjustUrl( $ctxProcess, $settCdn, $src, 'js' );
				Fullness_AdjustUrl( $ctxProcess, $src );
				$item -> setAttribute( 'src', $src );
			}
			else
				$item = $cont;

			if( $contGroupId === 'crit' || $contGroupId === 'critSpec' )
			{
				HtmlNd::InsertAfter( $head, $item, $itemGrpCritLast, true );
				$itemGrpCritLast = $item;

				if( $contGroupId === 'critSpec' )
					$item -> setAttribute( 'defer', '' );

				continue;
			}

			if( is_string( $cont ) && $optLoad )
			{
				$delay = ( $contGroupId === 'spec' ) ? $specsDelayTimeout : $notCritsDelayTimeout;
				if( $delay )
				{

					if( $contGroupId === 'spec' )
					{
						$item -> setAttribute( 'type', 'o/js-lzls' );
						$delaySpecNeeded = true;

						$delay = $specsDelayTimeout;
					}
					else
					{
						$item -> setAttribute( 'type', 'o/js-lzl' );
						$delayNotCritNeeded = true;

						$delay = $notCritsDelayTimeout;
					}

					if( $contGroupId === '' && (isset($settJs[ 'preLoadEarly' ])?$settJs[ 'preLoadEarly' ]:null) )
						Script_SrcAddPreloading( $item, $src, $head, $doc );
				}
			}

			$body -> appendChild( $item );
		}
	}

	if( $delayNotCritNeeded || $delaySpecNeeded )
	{

		{

			$item = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$item -> setAttribute( 'type', 'text/javascript' );

			HtmlNd::SetValFromContent( $item, "function seraph_accel_cmn_calcSizes(a){a.style.setProperty(\"--seraph-accel-client-width\",\"\"+a.clientWidth+\"px\");a.style.setProperty(\"--seraph-accel-client-width-px\",\"\"+a.clientWidth);a.style.setProperty(\"--seraph-accel-client-height\",\"\"+a.clientHeight+\"px\");a.style.setProperty(\"--seraph-accel-dvh\",\"\"+window.innerHeight+\"px\")}(function(a){a.addEventListener(\"seraph_accel_calcSizes\",function(b){seraph_accel_cmn_calcSizes(a.documentElement)},{capture:!0,passive:!0});seraph_accel_cmn_calcSizes(a.documentElement)})(document)" );
			$body -> insertBefore( $item, $body -> firstChild );
		}

		$ctxProcess[ 'jsDelay' ] = array( 'a' => array( '_E_A1_', '_E_A2_', '_E_TM1_', '_E_TM2_', '_E_CJSD_', '_E_AD_', '_E_FSCRLD_', '_E_FCD_', '_E_PRL_', '_E_LF_' ), 'v' => array( '"o/js-lzl"', '"o/js-lzls"', $notCritsDelayTimeout ? $notCritsDelayTimeout : 0, $specsDelayTimeout ? $specsDelayTimeout : 0, (isset($settJs[ 'cplxDelay' ])?$settJs[ 'cplxDelay' ]:null) ? 1 : 0, Gen::GetArrField( $settJs, array( 'aniDelay' ), 250 ), $notCritsDelayTimeout ? Gen::GetArrField( $settJs, array( 'scrlDelay' ), 0 ) : 0, Gen::GetArrField( $settJs, array( 'clk', 'delay' ), 250 ), (isset($settJs[ 'preLoadEarly' ])?$settJs[ 'preLoadEarly' ]:null) ? 0 : 1, (isset($settJs[ 'loadFast' ])?$settJs[ 'loadFast' ]:null) ? 1 : 0 ) );

	}

	return( true );
}

function Scripts_ProcessAddRtn( &$ctxProcess, $sett, $settCache, $settContPr, $settJs, $settCdn, $doc, $prms )
{

	$cont = str_replace( $prms[ 'a' ], $prms[ 'v' ], "(function(n,l,p,N,G,q,E,T,U,O,V,W,X){function P(){n.seraph_accel_js_lzl_initScrCustom&&n.seraph_accel_js_lzl_initScrCustom();if(r){var a=n[function(b){var c=\"\";b.forEach(function(d){c+=String.fromCharCode(d+3)});return c}([103,78,114,98,111,118])];!r.dkhjihyvjed&&a?r=void 0:(r.dkhjihyvjed=!0,r.jydy(a))}}function C(a,b=0,c){function d(){if(!a)return[];for(var e=[].slice.call(l.querySelectorAll('[type=\"'+a+'\"]')),g=0,f=e.length;g<f;g++){var h=e[g];if(h.hasAttribute(\"defer\")&&!1!==h.defer&&(!h.hasAttribute(\"async\")||\n!1===h.async)&&h.hasAttribute(\"src\")||\"module\"==h.getAttribute(\"data-type\"))e.splice(g,1),e.push(h),g--,f--}return e}function k(e=!1){P();X||e?w():p(w,b)}function D(e){e=e.ownerDocument;var g=e.seraph_accel_njsujyhmaeex={hujvqjdes:\"\",wyheujyhm:e[function(f){var h=\"\";f.forEach(function(m){h+=String.fromCharCode(m+3)});return h}([116,111,102,113,98])],wyhedbujyhm:e[function(f){var h=\"\";f.forEach(function(m){h+=String.fromCharCode(m+3)});return h}([116,111,102,113,98,105,107])],ujyhm:function(f){this.seraph_accel_njsujyhmaeex.hujvqjdes+=\nf},dbujyhm:function(f){this.write(f+\"\\n\")}};e[function(f){var h=\"\";f.forEach(function(m){h+=String.fromCharCode(m+3)});return h}([116,111,102,113,98])]=g.ujyhm;e[function(f){var h=\"\";f.forEach(function(m){h+=String.fromCharCode(m+3)});return h}([116,111,102,113,98,105,107])]=g.dbujyhm}function x(e){var g=e.ownerDocument,f=g.seraph_accel_njsujyhmaeex;if(f){if(f.hujvqjdes){var h=g.createElement(\"span\");e.parentNode.insertBefore(h,e.nextSibling);h.outerHTML=f.hujvqjdes}g[function(m){var t=\"\";m.forEach(function(H){t+=\nString.fromCharCode(H+3)});return t}([116,111,102,113,98])]=f.wyheujyhm;g[function(m){var t=\"\";m.forEach(function(H){t+=String.fromCharCode(H+3)});return t}([116,111,102,113,98,105,107])]=f.wyhedbujyhm;delete g.seraph_accel_njsujyhmaeex}}function w(){var e=I.shift();if(e)if(e.parentNode){var g=l.seraph_accel_usbpb(e.tagName),f=e.attributes;if(f)for(var h=0;h<f.length;h++){var m=f[h],t=m.value;m=m.name;\"type\"!=m&&(\"data-type\"==m&&(m=\"type\"),\"data-src\"==m&&(m=\"src\"),g.setAttribute(m,t))}g.textContent=\ne.textContent;f=!g.hasAttribute(\"async\");h=g.hasAttribute(\"src\");m=g.hasAttribute(\"nomodule\");f&&D(g);if(h=f&&h&&!m)g.onload=g.onerror=function(){g._seraph_accel_loaded||(g._seraph_accel_loaded=!0,x(g),k())};e.parentNode.replaceChild(g,e);h||(f&&x(g),k(!f))}else I=d(),w();else c&&c()}var I=d();if(W){var Q=l.createDocumentFragment();I.forEach(function(e){var g=e?e.getAttribute(\"src\"):void 0;if(g){var f=l.createElement(\"link\");f.setAttribute(\"rel\",\"module\"==e.getAttribute(\"data-type\")?\"modulepreload\":\n\"preload\");f.setAttribute(\"as\",\"IFRAME\"==e.tagName?\"document\":\"script\");f.setAttribute(\"href\",g);e.hasAttribute(\"integrity\")&&f.setAttribute(\"integrity\",e.getAttribute(\"integrity\"));e.hasAttribute(\"crossorigin\")&&f.setAttribute(\"crossorigin\",e.getAttribute(\"crossorigin\"));Q.appendChild(f)}});l.head.appendChild(Q)}k()}function u(a,b,c){var d=l.createEvent(\"Events\");d.initEvent(b,!0,!1);if(c)for(var k in c)d[k]=c[k];a.dispatchEvent(d)}function F(a,b){function c(k){try{Object.defineProperty(l,\"readyState\",\n{configurable:!0,enumerable:!0,value:k})}catch(D){}}function d(k){q?(r&&(r.jydyut(),r=void 0),c(\"interactive\"),u(l,\"readystatechange\"),u(l,\"DOMContentLoaded\"),delete l.readyState,u(l,\"readystatechange\"),p(function(){u(n,\"load\");u(n,\"scroll\");b&&b();k()})):k()}if(v){if(3==v){function k(){q&&c(\"loading\");!0===a?C(q?N:0,10,function(){d(function(){2==v?(v=1,1E6!=E&&p(function(){F(!0)},E)):C(G)})}):C(q?N:0,0,function(){d(function(){C(G)})})}function D(){for(var x,w;void 0!==(x=Object.keys(seraph_accel_izrbpb.a)[0]);){for(;w=\nseraph_accel_izrbpb.a[x].shift();)if(w(D))return;delete seraph_accel_izrbpb.a[x]}\"scrl\"===a&&O?p(k,O):k()}D()}else 1==v&&C(G);!0===a?v--:v=0}}function J(a){return\"click\"==a||\"mouseover\"==a||\"touchstart\"==a||\"touchmove\"==a||\"touchend\"==a||\"pointerdown\"==a||\"pointermove\"==a||\"pointerup\"==a}function K(a){var b=!1;\"touchstart\"==a.type?y=!1:\"pointerdown\"==a.type?z=!1:!1===y&&\"touchmove\"==a.type?y=!0:!1===z&&\"pointermove\"==a.type&&(z=!0);if(J(a.type)){if(void 0!==A){b=!0;var c=!1,d=!1,k=!0;\"click\"==a.type?\nc=d=!0:\"mouseover\"==a.type?(c=!0,k=!1):\"touchmove\"==a.type?(b=!1,y&&(d=!0)):\"touchend\"==a.type?y&&(d=!0):\"pointerdown\"==a.type?d=!0:\"pointermove\"==a.type?(b=!1,z&&(d=!0)):\"pointerup\"==a.type&&z&&(d=!0);if(k)for(k=a.target;k;k=k.parentNode)if(k.getAttribute&&(k.getAttribute(\"data-lzl-clk-no\")&&(b=!1),k.getAttribute(\"data-lzl-clk-nodef\"))){b=!0;d&&(a.preventDefault(),a.stopImmediatePropagation());break}if(b){d=!1;if(c)for(c=0;c<A.length;c++)if(A[c].type==a.type){d=!0;break}d||A.push(a)}}}else l.removeEventListener(a.type,\nK,{passive:!0});\"touchend\"==a.type?y=void 0:\"pointerup\"==a.type&&(z=void 0);void 0===B?B=!0:!1===B&&\"touchstart\"!=a.type&&\"pointerdown\"!=a.type&&F(b||\"scroll\"!=a.type&&\"wheel\"!=a.type&&\"touchmove\"!=a.type&&\"pointermove\"!=a.type?!1:\"scrl\",L)}function L(){p(function(){R.forEach(function(a){l.removeEventListener(a,K,J(a)?{capture:!0,passive:!1}:{passive:!0})});l.body.classList.remove(\"seraph-accel-js-lzl-ing\");u(l,\"seraph_accel_jsFinish\");A.forEach(function(a){if(\"click\"==a.type||\"mouseover\"==a.type){var b=\nl.elementFromPoint(a.clientX,a.clientY);b&&b.dispatchEvent(new MouseEvent(a.type,{view:a.view,bubbles:!0,cancelable:!0,clientX:a.clientX,clientY:a.clientY}))}else\"touchstart\"==a.type||\"touchmove\"==a.type||\"touchend\"==a.type?(b=(b=a.changedTouches&&a.changedTouches.length?a.changedTouches[0]:void 0)?l.elementFromPoint(b.clientX,b.clientY):void 0)&&b.dispatchEvent(a):(\"pointerdown\"==a.type||\"pointermove\"==a.type||\"pointerup\"==a.type)&&(b=l.elementFromPoint(a.clientX,a.clientY))&&b.dispatchEvent(a)});\nA=void 0},V);p(function(){l.body.classList.remove(\"seraph-accel-js-lzl-ing-ani\")},U)}function S(a){a.currentTarget&&a.currentTarget.removeEventListener(a.type,S);!0===B?(B=!1,F(!1,L)):(B=!1,1E6!=q&&p(function(){F(!0,L)},q))}function M(){p(function(){u(l,\"seraph_accel_calcSizes\")},0)}n.location.hash.length&&(q&&(q=1),E&&(E=1));q&&p(function(){l.body.classList.add(\"seraph-accel-js-lzl-ing-ani\")});var R=\"scroll wheel mousemove pointermove keydown click touchstart touchmove touchend pointerdown pointerup\".split(\" \"),\nB,y,z,r=T?{a:[],jydy:function(a){if(a&&a.fn&&!a.seraph_accel_bpb){this.a.push(a);a.seraph_accel_bpb={otquhdv:a.fn[function(b){var c=\"\";b.forEach(function(d){c+=String.fromCharCode(d+3)});return c}([111,98,94,97,118])]};if(a[function(b){var c=\"\";b.forEach(function(d){c+=String.fromCharCode(d+3)});return c}([101,108,105,97,79,98,94,97,118])])a[function(b){var c=\"\";b.forEach(function(d){c+=String.fromCharCode(d+3)});return c}([101,108,105,97,79,98,94,97,118])](!0);a.fn[function(b){var c=\"\";b.forEach(function(d){c+=\nString.fromCharCode(d+3)});return c}([111,98,94,97,118])]=function(b){l.addEventListener(\"DOMContentLoaded\",function(c){b.bind(l)(a,c)});return this}}},jydyut:function(){for(var a=0;a<this.a.length;a++){var b=this.a[a];b.fn[function(c){var d=\"\";c.forEach(function(k){d+=String.fromCharCode(k+3)});return d}([111,98,94,97,118])]=b.seraph_accel_bpb.otquhdv;delete b.seraph_accel_bpb;if(b[function(c){var d=\"\";c.forEach(function(k){d+=String.fromCharCode(k+3)});return d}([101,108,105,97,79,98,94,97,118])])b[function(c){var d=\n\"\";c.forEach(function(k){d+=String.fromCharCode(k+3)});return d}([101,108,105,97,79,98,94,97,118])](!1)}}}:void 0;n.seraph_accel_gzjydy=P;var v=3,A=[];R.forEach(function(a){l.addEventListener(a,K,J(a)?{capture:!0,passive:!1}:{passive:!0})});n.addEventListener(\"load\",S);n.addEventListener(\"resize\",M,!1);l.addEventListener(\"DOMContentLoaded\",M,!1);n.addEventListener(\"load\",M)})(window,document,setTimeout,_E_A1_,_E_A2_,_E_TM1_,_E_TM2_,_E_CJSD_,_E_AD_,_E_FSCRLD_,_E_FCD_,_E_PRL_,_E_LF_)" );

	$item = $doc -> createElement( 'script' );
	if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
		$item -> setAttribute( 'type', 'text/javascript' );

	$item -> setAttribute( 'id', 'seraph-accel-js-lzl' );

	HtmlNd::SetValFromContent( $item, $cont );

	$ctxProcess[ 'ndBody' ] -> appendChild( $item );

	ContentMarkSeparate( $item );

}

function Scripts_IsElemAs( &$ctxProcess, $doc, $settJs, $item )
{
	$items = &$ctxProcess[ 'scriptsInclItems' ];
	if( $items === null )
	{
		$items = array();

		$incls = Gen::GetArrField( $settJs, array( 'other', 'incl' ), array() );
		if( $incls )
		{
			$xpath = new \DOMXPath( $doc );

			foreach( $incls as $inclItemPath )
				foreach( HtmlNd::ChildrenAsArr( $xpath -> query( $inclItemPath, $ctxProcess[ 'ndHtml' ] ) ) as $itemIncl )
					$items[] = $itemIncl;
		}
	}

	return( in_array( $item, $items, true ) );
}

function JsMinify( $cont, $method, $removeFlaggedComments = false )
{
	try
	{
		switch( $method )
		{
		case 'jshrink':		$contNew = JShrink\Minifier::minify( $cont, array( 'flaggedComments' => !$removeFlaggedComments ) ); break;
		default:			$contNew = JSMin\JSMin::minify( $cont, array( 'removeFlaggedComments' => $removeFlaggedComments ) ); break;
		}
	}
	catch( \Exception $e )
	{
		return( $cont );
	}

	if( !$contNew )
		return( $cont );

	$cont = $contNew;

	if( (isset($ctxProcess[ 'debug' ])?$ctxProcess[ 'debug' ]:null) )
		$cont = '/* DEBUG: MINIFIED by seraph-accel */' . $cont;

	return( $cont );
}

function Script_AdjustCont( $ctxProcess, $settCache, $settJs, $srcInfo, $src, $id, &$cont )
{
	if( !$cont )
		return( false );

	$adjusted = false;
	if( ( !$srcInfo || !(isset($srcInfo[ 'ext' ])?$srcInfo[ 'ext' ]:null) ) && Gen::GetArrField( $settJs, array( 'min' ), false ) && !IsObjInRegexpList( Gen::GetArrField( $settJs, array( 'minExcls' ), array() ), array( 'src' => $src, 'id' => $id, 'body' => $cont ) ) )
	{
		$contNew = trim( JsMinify( $cont, (isset($settJs[ 'minMthd' ])?$settJs[ 'minMthd' ]:null), (isset($settJs[ 'cprRem' ])?$settJs[ 'cprRem' ]:null) ) );
		if( $cont != $contNew )
		{
			$cont = $contNew;
			$adjusted = true;
		}
	}

	return( $adjusted );
}

