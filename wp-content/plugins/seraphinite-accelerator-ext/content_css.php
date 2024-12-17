<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

spl_autoload_register(
	function( $class )
	{
		if( strpos( $class, 'PhpCss' ) === 0 )
			@include_once( 'D:/Data/Temp/PhpCss/' . str_replace( '\\', '/', $class ) . '.php' );
	}
);

function _CssExtractImports_GetPosRange( &$aCommentRange, $pos )
{
	foreach( $aCommentRange as &$commentRange )
		if( $commentRange[ 0 ] <= $pos && $pos < $commentRange[ 1 ] )
			return( $commentRange );

	return( false );
}

function _CssExtractImports( &$cont )
{

	$res = array();

	$m = array(); preg_match_all( '#@import(?:\\s+url\\([^()]*\\)|\\s*"[^"]*"|\\s*\'[^\']*\')?[^@{}\\r\\n;]*(;\\s*\\n?|\\s*$)#S', $cont, $m, PREG_OFFSET_CAPTURE );
	if( !$m )
		return( $res );

	$aCommentRange = array();
	for( $offs = 0; ; )
	{
		$posCommentBegin = strpos( $cont, '/*', $offs );
		if( $posCommentBegin === false )
			break;

		$posCommentEnd = strpos( $cont, '*/', $posCommentBegin + 2 );
		if( $posCommentEnd === false )
			$posCommentEnd = strlen( $cont );
		else
			$posCommentEnd += 2;

		$aCommentRange[] = array( $posCommentBegin, $posCommentEnd );
		$offs = $posCommentEnd;
	}
	unset( $posCommentBegin, $posCommentEnd );

	for( $offs = 0; ; )
	{
		$posFirstBlock = strpos( $cont, '{', $offs );
		if( $posFirstBlock === false )
		{
			$posFirstBlock = strlen( $cont );
			break;
		}

		$range = _CssExtractImports_GetPosRange( $aCommentRange, $posFirstBlock );
		if( !$range )
			break;

		$offs = $range[ 1 ];
	}

	for( $i = count( $m[ 0 ] ); $i > 0; $i-- )
	{
		$mi = $m[ 0 ][ $i - 1 ];

		$offs = $mi[ 1 ];
		$len = strlen( $mi[ 0 ] );

		if( $offs > $posFirstBlock )
			continue;

		if( _CssExtractImports_GetPosRange( $aCommentRange, $offs ) )
			continue;

		$s = substr( $cont, $offs, $len );
		$suffix = $m[ 1 ][ $i - 1 ][ 0 ];
		if( !Gen::StrStartsWith( $suffix, ';' ) )
			$s = substr_replace( $s, ';' . ( Gen::StrStartsWith( $suffix, "\n" ) ? "\n" : "\r\n" ), $len - strlen( $suffix ) );

		array_splice( $res, 0, 0, array( $s ) );
		$cont = substr_replace( $cont, '', $offs, $len );
	}

	return( $res );
}

function _CssInsertImports( &$cont, $imports )
{
	$contHead = implode( '', array_merge( _CssExtractImports( $cont ), $imports ) );
	if( $contHead )
		$cont = $contHead . $cont;

}

function Style_ProcessCont( &$ctxProcess, $sett, $settCache, $settContPr, $settCss, $settImg, $settCdn, $head, &$item, $srcInfo, $src, $id,  $cont, $contAdjusted, $isInlined, $status, $isNoScript, &$contGroups )
{
	$contPrefix = RemoveZeroSpace( $cont, '' );
	if( $contPrefix )
		$contPrefix = '@charset "' . $contPrefix . '";';

	$m = array();
	if( substr( $cont, 0, 8 ) == '@charset' && preg_match( '/^@charset\\s+"([\\w-]+)"\\s*;/iS', $cont, $m, PREG_OFFSET_CAPTURE ) )
	{
		if( $m[ 0 ][ 1 ] == 0 )
		{
			$contPrefix = '@charset "' . strtoupper( $m[ 1 ][ 0 ] ) . '";';
			$cont = substr( $cont, strlen( $m[ 0 ][ 0 ] ) );
		}
	}

	$group = null;
	if( !$isNoScript )
	{
		if( $status == 'crit' )
		{
			if( (isset($settCss[ 'group' ])?$settCss[ 'group' ]:null) )
				$group = !!(isset($settCss[ 'groupCombine' ])?$settCss[ 'groupCombine' ]:null);
		}
		else if( $status == 'fonts' )
		{
			if( (isset($settCss[ 'groupFont' ])?$settCss[ 'groupFont' ]:null) )
				$group = !!(isset($settCss[ 'groupFontCombine' ])?$settCss[ 'groupFontCombine' ]:null);
		}
		else
		{
			if( (isset($settCss[ 'groupNonCrit' ])?$settCss[ 'groupNonCrit' ]:null) )
				$group = !!(isset($settCss[ 'groupNonCritCombine' ])?$settCss[ 'groupNonCritCombine' ]:null);
		}
	}

	$contImports = array();
	if( $group )
	{
		$contImports = _CssExtractImports( $cont );

		$media = HtmlNd::GetAttrVal( $item, 'media' );
		if( $media && $media != 'all' )
		{
			if( $onload = $item -> getAttribute( 'onload' ) )
			{
				if( preg_match( '@^\\s*this\\s*\\.\\s*media\\s*=\\s*this\\s*\\.\\s*dataset\\s*\\.\\s*media\\s*;\\s*delete\\s+this\\s*\\.\\s*dataset\\s*\\.\\s*media\\s*;\\s*this\\s*\\.\\s*removeAttribute\\s*\\(\\s*[\'"]onload[\'"]\\s*\\)\\s*;?\\s*$@S', $onload ) )
					$media = $item -> getAttribute( 'data-media' );
				else
					$media = '';
			}

			$media = trim( $media );
			if( $media && $media != 'all' )
				$cont = '@media ' . $media . "{\r\n" . $cont . "\r\n}";
		}
	}

	if( ( $contAdjusted || $group ) && (isset($ctxProcess[ 'debug' ])?$ctxProcess[ 'debug' ]:null) )
		$cont = '/* ################################################################################################################################################ */' . "\r\n" . '/* DEBUG: seraph-accel CSS src="' . $src . '" */' . "\r\n\r\n" . $cont;

	if( $group )
	{
		$contGroup = &$contGroups[ $status ];
		_CssInsertImports( $contGroup, $contImports );
		$contGroup .= $cont . "\r\n";

		if( (isset($ctxProcess[ 'chunksEnabled' ])?$ctxProcess[ 'chunksEnabled' ]:null) && Gen::GetArrField( $settCache, array( 'chunks', 'css' ) ) )
			$contGroup .= ContentMarkGetSep();

		if( (isset($ctxProcess[ 'debug' ])?$ctxProcess[ 'debug' ]:null) )
			$contGroup .= "\r\n";

		if( $item -> parentNode )
			$item -> parentNode -> removeChild( $item );
	}
	else
	{
		$cont = $contPrefix . $cont;

		if( !Style_ProcessCont_ItemApply( $ctxProcess, $sett, $settCache, $settCss, $settCdn, $head, $item, $srcInfo, $src, $id,  $cont, $contAdjusted, $isInlined, $status, $isNoScript, $group !== null, false ) )
			return( false );
	}

	return( true );
}

function _Style_ProcessCont_ItemApply_EscapeInline( $cont )
{
	return( str_replace( array( '<style', '</style' ), array( '&lt;style', '&lt;/style' ), $cont ) );
}

function Style_ProcessCont_ItemApply( &$ctxProcess, $sett, $settCache, $settCss, $settCdn, $head, &$item, $srcInfo, $src, $id,  $cont, $contAdjusted, $isInlined, $status, $isNoScript, $repos, $composite = false )
{
	$itemsAfter = array();
	$optLoad = (isset($settCss[ 'optLoad' ])?$settCss[ 'optLoad' ]:null);
	$inlineAsSrc = (isset($settCss[ 'inlAsSrc' ])?$settCss[ 'inlAsSrc' ]:null);

	if( (isset($ctxProcess[ 'compatView' ])?$ctxProcess[ 'compatView' ]:null) )
		$inlineAsSrc = false;

	$inline = $isInlined;
	if( $optLoad && !$isNoScript )
	{
		$inline = ( (isset($ctxProcess[ 'compatView' ])?$ctxProcess[ 'compatView' ]:null) !== 'cm' ) && !!(isset($settCss[ $status == 'crit' ? 'inlCrit' : 'inlNonCrit' ])?$settCss[ $status == 'crit' ? 'inlCrit' : 'inlNonCrit' ]:null);
		if( HtmlNd::FindUpByTag( $item, 'svg' ) && $isInlined )
			$inline = true;
	}

	$media = $item -> getAttribute( 'media' );

	$cont = str_replace( '::bhkdyqcetujyi::', (

		( $inlineAsSrc && $inline && !$isInlined  ) ) ? $ctxProcess[ 'siteDomainUrl' ] : '', $cont );

	ContUpdateItemIntegrity( $item, $cont );

	if( $inline )
	{

		if( $composite )
		    $cont = str_replace( ContentMarkGetSep(), '', $cont );
		$cont = apply_filters( 'seraph_accel_css_content', $cont, false );

		if( !$isInlined )
		{
			if( $inlineAsSrc )
			    $item -> setAttribute( 'href', 'data:text/css,' . _Scripts_EncodeBodyAsSrc( $cont ) );
			else
			{
				$item = HtmlNd::SetTag( $item, 'style', array( 'rel', 'as', 'href' ) );
				HtmlNd::SetValFromContent( $item, _Style_ProcessCont_ItemApply_EscapeInline( $cont ) );
			}
		}
		else if( $contAdjusted )
			HtmlNd::SetValFromContent( $item, _Style_ProcessCont_ItemApply_EscapeInline( $cont ) );
	}
	else
	{
		if( $isInlined )
		{
			$item = HtmlNd::SetTag( $item, 'link', true );
			$item -> setAttribute( 'rel', 'stylesheet' );

		}

		if( $contAdjusted || $isInlined )
		{
			if( $composite && !GetContentProcessorForce( $sett ) && (isset($ctxProcess[ 'chunksEnabled' ])?$ctxProcess[ 'chunksEnabled' ]:null) && Gen::GetArrField( $settCache, array( 'chunks', 'css' ) ) )
			{
				$cont = apply_filters( 'seraph_accel_css_content', $cont, true );

				$idSub = ( string )( $ctxProcess[ 'subCurIdx' ]++ ) . '.css';
				$ctxProcess[ 'subs' ][ $idSub ] = $cont;
				$src = ContentProcess_GetGetPartUri( $ctxProcess, $idSub );
			}
			else
			{
				if( $composite )
					$cont = str_replace( ContentMarkGetSep(), '', $cont );
				$cont = apply_filters( 'seraph_accel_css_content', $cont, true );

				if( !UpdSc( $ctxProcess, $settCache, 'css', $cont, $src ) )
					return( false );
			}
		}

		Cdn_AdjustUrl( $ctxProcess, $settCdn, $src, 'css' );
		Fullness_AdjustUrl( $ctxProcess, $src, $srcInfo ? (isset($srcInfo[ 'srcUrlFullness' ])?$srcInfo[ 'srcUrlFullness' ]:null) : null );

		$item -> nodeValue = '';
		$item -> setAttribute( 'href', $src );

		if( !(isset($ctxProcess[ 'compatView' ])?$ctxProcess[ 'compatView' ]:null) && $optLoad && !$isNoScript && $status != 'crit' )
		{

			{
				$itemCopy = $item -> cloneNode( true );
				$itemNoScript = $item -> ownerDocument -> createElement( 'noscript' );
				if( !$itemCopy || !$itemNoScript )
					return( false );

				$itemNoScript -> setAttribute( 'lzl', '' );
				$itemNoScript -> appendChild( $itemCopy );

				$itemsAfter[] = $itemNoScript;
			}

			if( $status == 'fonts' )
			{
				$itemPreLoad = $item -> cloneNode( true );
				$itemPreLoad -> setAttribute( 'rel', 'preload' );
				$itemPreLoad -> setAttribute( 'as', 'style' );
				$itemsAfter[] = $itemPreLoad;
			}

			$item -> setAttribute( 'rel', 'stylesheet/lzl' . ( $status == 'nonCrit' ? '-nc' : '' ) );
			$ctxProcess[ 'lazyloadStyles' ][ $status ] = ( $status != 'fonts' ) && (isset($settCss[ 'delayNonCritWithJs' ])?$settCss[ 'delayNonCritWithJs' ]:null) ? 'withScripts' : '';

		}
	}

	if( $repos )
	{
		if( $item -> parentNode )
			$item -> parentNode -> removeChild( $item );

		if( $status == 'crit' )
		{
			$head -> appendChild( $item );
		}
		else
		{
			if( $item -> nodeName != 'style' )
				$head -> appendChild( $item );
			else
				$ctxProcess[ 'ndBody' ] -> appendChild( $item );
		}
	}

	$itemInsertAfter = $item;
	foreach( $itemsAfter as $itemAfter )
	{
		HtmlNd::InsertAfter( $item -> parentNode, $itemAfter, $itemInsertAfter );
		$itemInsertAfter = $itemAfter;
	}

	if( (isset($ctxProcess[ 'chunksEnabled' ])?$ctxProcess[ 'chunksEnabled' ]:null) )
	{
		ContentMarkSeparate( $item, false, 1 );
		ContentMarkSeparate( $itemsAfter ? $itemsAfter[ count( $itemsAfter ) - 1 ] : $item, false, 2 );
	}

	return( true );
}

function _EmbedStyles_Process( $processor, &$ctxProcess, $sett, $settCache, $settContPr, $settCss, $settImg, $settCdn, $doc, &$aCritFonts, &$aImgSzAlternativesBlocksGlobal = null )
{
	$itemClassIdx = 0;
	for( $item = null; $item = HtmlNd::GetNextTreeChild( $ctxProcess[ 'ndBody' ], $item );  )
	{
		if( ContentProcess_IsAborted( $settCache ) ) return;

		if( $item -> nodeType != XML_ELEMENT_NODE )
			continue;

		$skip = false;
		switch( $item -> nodeName )
		{
		case 'script':
		case 'noscript':
		case 'style':
		case 'img':
		case 'picture':
		case 'source':
			$skip = true;
			break;
		}

		if( $skip )
			continue;

		$style = $item -> getAttribute( 'style' );
		if( !$style )
			continue;

		if( $processor -> IsTraceEnabled() )
			$processor -> SetCurObjectId( GetSourceItemTracePath( $ctxProcess, $item -> getNodePath() . '[@style]' ) );

		$ruleSet = $processor -> ParseRuleSet( $style );
		if( !$ruleSet )
			continue;

		$imgSzAlternatives = null;
		if( $aImgSzAlternativesBlocksGlobal !== null && !Images_CheckSzAdaptExcl( $ctxProcess, $doc, $settImg, $item ) )
			$imgSzAlternatives = new ImgSzAlternatives();

		$ctxItems = new AnyObj( array( 'item' => $item ) );

		$r = StyleProcessor::AdjustRuleSet( $ruleSet, $aCritFonts, $ctxItems, $doc, $ctxProcess, $settCache, $settCss, $settImg, $settCdn, null, null, true, $imgSzAlternatives );
		if( $r === false )
			return( false );

		if( $r )
		{
			$style = $ruleSet -> renderWhole( $processor -> GetRenderFormatMin() );
			$style = str_replace( '::bhkdyqcetujyi::', '', $style );
			$item -> setAttribute( 'style', $style );
		}

		if( (isset($ctxItems -> lazyBg)?$ctxItems -> lazyBg:null) )
			StyleProcessor::AdjustItemLazyBg( $ctxProcess, $settImg, $doc, $item );

		if( $imgSzAlternatives && !$imgSzAlternatives -> isEmpty() )
		{
			$itemCssClass = 'seraph-accel-bg-' . $itemClassIdx++;
			StyleProcessor::AdjustItemAdaptImg( $ctxProcess, $settImg, $doc, $item, $itemCssClass );
			$aImgSzAlternativesBlocksGlobal[] = array( 'sels' => array( '.' . $itemCssClass ), 'alts' => $imgSzAlternatives, 'isLazyBg' => (isset($ctxItems -> lazyBg)?$ctxItems -> lazyBg:null) || (isset($ctxItems -> lazyBgItem)?$ctxItems -> lazyBgItem:null) );
		}
	}

	return( true );
}

function Styles_Process( &$ctxProcess, $sett, $settCache, $settContPr, $settCss, $settImg, $settCdn, $doc )
{
	if( (isset($ctxProcess[ 'isAMP' ])?$ctxProcess[ 'isAMP' ]:null) )
	    return( true );

	$adjustCont = (isset($settCss[ 'optLoad' ])?$settCss[ 'optLoad' ]:null)
		|| (isset($settCss[ 'min' ])?$settCss[ 'min' ]:null)
		|| (isset($settCss[ 'fontOptLoad' ])?$settCss[ 'fontOptLoad' ]:null)
		|| !(isset($settCss[ 'fontCrit' ])?$settCss[ 'fontCrit' ]:null)
		|| Gen::GetArrField( $settCss, array( 'font', 'deinlLrg' ), false )
		|| ( (isset($settCss[ 'group' ])?$settCss[ 'group' ]:null) && (isset($settCss[ 'groupCombine' ])?$settCss[ 'groupCombine' ]:null) )
		|| ( (isset($settCss[ 'groupNonCrit' ])?$settCss[ 'groupNonCrit' ]:null) && (isset($settCss[ 'groupNonCritCombine' ])?$settCss[ 'groupNonCritCombine' ]:null) )
		|| (isset($settImg[ 'szAdaptBg' ])?$settImg[ 'szAdaptBg' ]:null)
		|| Gen::GetArrField( $settCdn, array( 'enable' ), false );

	$skips = Gen::GetArrField( $settCss, array( 'skips' ), array() );
	if( !( $adjustCont || (isset($settCss[ 'group' ])?$settCss[ 'group' ]:null) || (isset($settCss[ 'groupNonCrit' ])?$settCss[ 'groupNonCrit' ]:null) || (isset($settCss[ 'sepImp' ])?$settCss[ 'sepImp' ]:null) || $skips ) )
		return( true );

	$head = $ctxProcess[ 'ndHead' ];

	$aCritFonts = (isset($settCss[ 'fontCritAuto' ])?$settCss[ 'fontCritAuto' ]:null) ? array() : null;

	$processor = new StyleProcessor( $doc, $ctxProcess[ 'ndHtml' ], (isset($ctxProcess[ 'lrnDsc' ])?$ctxProcess[ 'lrnDsc' ]:null), (isset($ctxProcess[ 'docSkeleton' ])?$ctxProcess[ 'docSkeleton' ]:null), (isset($ctxProcess[ 'sklCssSelExcl' ])?$ctxProcess[ 'sklCssSelExcl' ]:null), (isset($settCss[ 'corrErr' ])?$settCss[ 'corrErr' ]:null), (isset($sett[ 'debug' ])?$sett[ 'debug' ]:null), (isset($settCss[ 'min' ])?$settCss[ 'min' ]:null) === true );
	$processor -> Init( $ctxProcess );

	$aImgSzAlternativesBlocksGlobal = (isset($settImg[ 'szAdaptBg' ])?$settImg[ 'szAdaptBg' ]:null) ? array() : null;
	if( $adjustCont && !_EmbedStyles_Process( $processor, $ctxProcess, $sett, $settCache, $settContPr, $settCss, $settImg, $settCdn, $doc, $aCritFonts, $aImgSzAlternativesBlocksGlobal ) )
		return( false );

	if( ContentProcess_IsAborted( $settCache ) ) return( true );

	{
		$autoExcls = Gen::GetArrField( $settCss, array( 'nonCrit', 'autoExcls' ), array() );

		if( isset( $ctxProcess[ 'aCssCrit' ] ) )
		{
			foreach( array_keys( $ctxProcess[ 'aCssCrit' ] ) as $autoExclsExpr )
				if( !in_array( $autoExclsExpr, $autoExcls ) )
					$autoExcls[] = $autoExclsExpr;
		}

		$autoExcls = array_map( function( $v ) { return( $v . 'S' ); }, $autoExcls );

		Gen::SetArrField( $settCss, array( 'nonCrit', 'autoExcls' ), $autoExcls );
		unset( $autoExcls );
	}

	$processor -> InitFastDomSearch( $settCache, true );

	if( ContentProcess_IsAborted( $settCache ) ) return( true );

	if( !isset( $ctxProcess[ 'lrn' ] ) && isset( $ctxProcess[ 'lrnDsc' ] ) )
		$processor -> readLrnData( $ctxProcess, $ctxProcess[ 'lrnDsc' ], $ctxProcess[ 'lrnDataPath' ] );

	$settNonCrit = Gen::GetArrField( $settCss, array( 'nonCrit' ), array() );
	$contGroups = array( 'crit' => '', 'fonts' => '', 'nonCrit' => '' );

	$items = array();
	for( $item = null; $item = HtmlNd::GetNextTreeChild( $doc, $item );  )
	{
		if( $item -> nodeType != XML_ELEMENT_NODE )
			continue;

		switch( $item -> nodeName )
		{
		case 'link':
		case 'style':
			$items[] = array( 'item' => $item, 'nodePath' => $item -> getNodePath() );
			break;
		}
	}

	$hrefs = array();

	for( $i = 0; $i < count( $items ); $i++ )
	{
		$itemData = &$items[ $i ];
		$item = $itemData[ 'item' ];
		$cont = (isset($itemData[ 'cont' ])?$itemData[ 'cont' ]:null);

		if( ContentProcess_IsAborted( $settCache ) ) return( true );

		$isInlined = ( $item -> nodeName == 'style' );

		if( !$isInlined )
		{
			$rel = HtmlNd::GetAttrVal( $item, 'rel' );
			if( $cont === null )
				if( $rel != 'stylesheet' )
				{
					if( $rel == 'preload' && HtmlNd::GetAttrVal( $item, 'as' ) == 'style' )
					{
						if( !HtmlNd::GetAttrVal( $item, 'onload' ) )
						{
							if( (isset($settCss[ 'optLoad' ])?$settCss[ 'optLoad' ]:null) && $item -> parentNode )
								$item -> parentNode -> removeChild( $item );
							continue;
						}
					}
					else
						continue;
				}
		}

		$type = HtmlNd::GetAttrVal( $item, 'type' );
		if( $cont === null )
		{
			if( $type && $type != 'text/css' )
				continue;

			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
			{
				if( !$type )
					$item -> setAttribute( 'type', 'text/css' );
			}
			else if( $type && (isset($settContPr[ 'min' ])?$settContPr[ 'min' ]:null) )
				$item -> removeAttribute( 'type' );
		}
		else
			unset( $itemData[ 'cont' ] );

		$src = null;
		if( !$isInlined )
		{
			$src = HtmlNd::GetAttrVal( $item, 'href' );
			if( !$src )
				continue;
		}

		$id = HtmlNd::GetAttrVal( $item, 'id' );

		$detectedPattern = null;
		if( ( $cont === null ) && IsObjInRegexpList( $skips, array( 'src' => $src, 'id' => $id ), $detectedPattern ) )
		{
			if( (isset($ctxProcess[ 'debug' ])?$ctxProcess[ 'debug' ]:null) )
			{
				$item -> setAttribute( 'type', 'o/css-inactive' );
				$item -> setAttribute( 'seraph-accel-debug', 'status=skipped;' . ( $detectedPattern ? ' detectedPattern="' . $detectedPattern . '"' : '' ) );
			}
			else if( $item -> parentNode )
				$item -> parentNode -> removeChild( $item );

			continue;
		}

		$isNoScript = HtmlNd::FindUpByTag( $item, 'noscript' );
		if( $isNoScript && !$isInlined )
			continue;

		$srcInfo = null;
		if( !$isInlined )
		{
			if( $cont === null )
			{
				$itemPrevHref = (isset($hrefs[ $src ])?$hrefs[ $src ]:null);
				if( $itemPrevHref )
				{
					if( $rel == 'stylesheet' && $itemPrevHref -> getAttribute( 'rel' ) != $rel )
					{
						if( (isset($ctxProcess[ 'debug' ])?$ctxProcess[ 'debug' ]:null) )
						{
							$itemPrevHref -> setAttribute( 'href', $src );
							$itemPrevHref -> setAttribute( 'as', 'style-inactive' );
							$itemPrevHref -> setAttribute( 'seraph-accel-debug', 'status=skipped; reason=alreadyUsed;' );
						}
						else if( $itemPrevHref -> parentNode )
							$itemPrevHref -> parentNode -> removeChild( $itemPrevHref );
					}
					else
					{
						if( (isset($ctxProcess[ 'debug' ])?$ctxProcess[ 'debug' ]:null) )
						{
							if( $rel == 'stylesheet' )
								$item -> setAttribute( 'type', 'o/css-inactive' );
							else
								$item -> setAttribute( 'as', 'style-inactive' );
							$item -> setAttribute( 'seraph-accel-debug', 'status=skipped; reason=alreadyUsed;' );
						}
						else if( $item -> parentNode )
							$item -> parentNode -> removeChild( $item );

						continue;
					}
				}

				$hrefs[ $src ] = $item;
			}

			$srcInfo = GetSrcAttrInfo( $ctxProcess, null, null, $src );
		}

		if( $processor -> IsTraceEnabled() )
			$processor -> SetCurObjectId( GetSourceItemTracePath( $ctxProcess, (isset($itemData[ 'nodePath' ])?$itemData[ 'nodePath' ]:''), $srcInfo, $id ) );

		if( $cont === null )
		{
			$cont = false;
			if( !$isInlined )
			{
				if( (isset($srcInfo[ 'filePath' ])?$srcInfo[ 'filePath' ]:null) && Gen::GetFileExt( $srcInfo[ 'filePath' ] ) == 'css' )
					$cont = @file_get_contents( (isset($srcInfo[ 'filePath' ])?$srcInfo[ 'filePath' ]:null) );
				if( $cont === false )
				{
					$cont = GetExtContents( (isset($srcInfo[ 'url' ])?$srcInfo[ 'url' ]:null), $contMimeType );
					if( $cont !== false && !in_array( $contMimeType, array( 'text/css' ) ) )
					{
						$cont = false;
						if( (isset($sett[ 'debug' ])?$sett[ 'debug' ]:null) )
							LastWarnDscs_Add( LocId::Pack( 'CssUrlWrongType_%1$s%2$s', null, array( $srcInfo[ 'url' ], $contMimeType ) ) );
					}
				}
			}
			else
				$cont = $item -> nodeValue;

			if( $cont === false || ( !$cont && $isInlined ) )
			{

				continue;
			}

			if( (isset($settCss[ 'sepImp' ])?$settCss[ 'sepImp' ]:null) )
			{
				$contWoImports = $cont;
				$imports = _CssExtractImports( $contWoImports );
				if( $imports )
				{
					$media = $item -> getAttribute( 'media' );

					foreach( $imports as &$import )
					{
						$import = StyleProcessor::GetFirstImportSimpleAttrs( $ctxProcess, $import, $src );
						if( !$import || ( (isset($import[ 'media' ])?$import[ 'media' ]:null) && (isset($import[ 'media' ])?$import[ 'media' ]:null) != 'all' && $media && $media != 'all' && (isset($import[ 'media' ])?$import[ 'media' ]:null) != $media ) )
						{
							$imports = false;
							break;
						}
					}
					unset( $import );

					if( $imports )
					{
						$j = 0;
						foreach( $imports as $import )
						{

							$itemImp = $doc -> createElement( 'link' );
							HtmlNd::CopyAllAttrs( $item, $itemImp, array( 'id', 'type', 'rel' ) );
							$itemImp -> setAttribute( 'rel', 'stylesheet' );
							if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
								$itemImp -> setAttribute( 'type', 'text/css' );
							if( $id )
								$itemImp -> setAttribute( 'id', $id . '-i' . $j );

							$itemImp -> setAttribute( 'href', $import[ 'url' ] );
							if( (isset($import[ 'media' ])?$import[ 'media' ]:null) && ( !$media || $media == 'all' ) )
								$itemImp -> setAttribute( 'media', $import[ 'media' ] );

							$item -> parentNode -> insertBefore( $itemImp, $item );

							$itemDataImp = array( 'item' => $itemImp );

							array_splice( $items, $i + $j, 0, array( $itemDataImp ) );
							$j++;
						}

						$i--;
						$itemData[ 'cont' ] = $contWoImports;
						unset( $contWoImports );

						continue;
					}
				}

				unset( $contWoImports );
			}
		}

			if( $adjustCont )
			{
				$extract = !(isset($ctxProcess[ 'compatView' ])?$ctxProcess[ 'compatView' ]:null) && (isset($settNonCrit[ 'auto' ])?$settNonCrit[ 'auto' ]:null);
				$contsExtracted = $processor -> AdjustCont( $extract, $aCritFonts, $ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, $cont, $isInlined );
				if( $contsExtracted === false )
					return( false );
			}
			else
			{
				$extract = false;
				$contsExtracted = null;
			}

			if( ContentProcess_IsAborted( $settCache ) ) return( true );

		if( $item -> hasAttribute( 'seraph-accel-noadjust' ) )
		{
			$item -> removeAttribute( 'seraph-accel-noadjust' );
			continue;
		}

		$ps = array();

		if( $extract )
		{
			$contsExtracted[ 'nonCrit' ] = $cont;
			unset( $cont );

			$contExtractedIdDef = 'nonCrit';
			if( $isInlined && ( (isset($settCss[ 'optLoad' ])?$settCss[ 'optLoad' ]:null) && (isset($settCss[ 'inlCrit' ])?$settCss[ 'inlCrit' ]:null) ) && HtmlNd::HasAttrs( $item, array( 'type', 'media' ) ) )
				$contExtractedIdDef = 'crit';

			$itemInsertAfter = null;
			foreach( $contsExtracted as $contExtractedId => $contExtracted )
			{

				if( $contExtractedIdDef == $contExtractedId )
				{
					$itemExtracted = $item;
					$idExtracted = $id;
					$itemInsertAfter = $item;
				}
				else
				{
					if( !$contExtracted )
						continue;

					$itemExtracted = $doc -> createElement( $item -> nodeName );
					if( $itemInsertAfter )
					{
						HtmlNd::InsertAfter( $item -> parentNode, $itemExtracted, $itemInsertAfter );
						$itemInsertAfter = $itemExtracted;
					}
					else
						$item -> parentNode -> insertBefore( $itemExtracted, $item );

					if( $id )
					{
						$idExtracted = $id . '-' . $contExtractedId;
						$itemExtracted -> setAttribute( 'id', $idExtracted );
					}
					else
						$idExtracted = null;

					HtmlNd::CopyAllAttrs( $item, $itemExtracted, array( 'id' ) );
				}

				$ps[] = array( 'item' => $itemExtracted, 'id' => $idExtracted,  'cont' => $contExtracted, 'contAdjusted' => true, 'status' => $contExtractedId );
			}

			unset( $contExtracted );
			unset( $itemInsertAfter );
		}
		else
		{
			$detectedPattern = null;
			$isCrit = GetObjSrcCritStatus( $settNonCrit, null, null, $srcInfo, $src, $id, $cont, $detectedPattern );
			$ps[] = array( 'item' => $item, 'id' => $id,  'cont' => $cont, 'contAdjusted' => $contsExtracted !== null, 'status' => $isCrit ? 'crit' : 'nonCrit', 'detectedPattern' => $detectedPattern );
		}

		if( $isInlined )
		{
			if( (isset($ctxProcess[ 'debug' ])?$ctxProcess[ 'debug' ]:null) )
				$src = 'inline:' . (isset($ctxProcess[ 'serverArgs' ][ 'REQUEST_SCHEME' ])?$ctxProcess[ 'serverArgs' ][ 'REQUEST_SCHEME' ]:null) . '://' . $ctxProcess[ 'host' ] . ':' . (isset($ctxProcess[ 'serverArgs' ][ 'SERVER_PORT' ])?$ctxProcess[ 'serverArgs' ][ 'SERVER_PORT' ]:null) . (isset($ctxProcess[ 'serverArgs' ][ 'REQUEST_URI' ])?$ctxProcess[ 'serverArgs' ][ 'REQUEST_URI' ]:null) . ':' . $item -> getLineNo();
		}

		foreach( $ps as $psi )
		{
			if( (isset($ctxProcess[ 'debug' ])?$ctxProcess[ 'debug' ]:null) )
				$psi[ 'item' ] -> setAttribute( 'seraph-accel-debug', 'status=' . $psi[ 'status' ] . ';' . ( (isset($psi[ 'detectedPattern' ])?$psi[ 'detectedPattern' ]:null) ? ' detectedPattern="' . $psi[ 'detectedPattern' ] . '"' : '' ) );

			if( !Style_ProcessCont( $ctxProcess, $sett, $settCache, $settContPr, $settCss, $settImg, $settCdn, $head, $psi[ 'item' ], $srcInfo, $src, $psi[ 'id' ],  $psi[ 'cont' ], (isset($psi[ 'contAdjusted' ])?$psi[ 'contAdjusted' ]:null), $isInlined, $psi[ 'status' ], $isNoScript, $contGroups ) )
				return( false );
		}
	}

	if( $adjustCont )
		$processor -> ApplyItems( $ctxProcess, $settImg );

	if( $aImgSzAlternativesBlocksGlobal )
	{
		$cssCritDoc = new Sabberworm\CSS\CSSList\Document();

			foreach( $aImgSzAlternativesBlocksGlobal as $aImgSzAlternativesBlocks )
				$cssCritDoc -> append( StyleProcessor::ImgSzAlternativesGetStyleBlocks( $ctxProcess, $aImgSzAlternativesBlocks[ 'sels' ], $aImgSzAlternativesBlocks[ 'alts' ], true, $aImgSzAlternativesBlocks[ 'isLazyBg' ] ) );

		$cont = $processor -> RenderData( $cssCritDoc );
		unset( $cssCritDoc );

		$item = $doc -> createElement( 'style' );
		if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
			$item -> setAttribute( 'type', 'text/css' );
		HtmlNd::SetValFromContent( $item, $cont );
		$head -> appendChild( $item );

		if( !Style_ProcessCont( $ctxProcess, $sett, $settCache, $settContPr, $settCss, $settImg, $settCdn, $head, $item, null, null, null,  $cont, true, true, 'crit', false, $contGroups ) )
			return( false );

		unset( $cont );
		unset( $item );
	}

	unset( $itemData );
	unset( $hrefs );

	if( ContentProcess_IsAborted( $settCache ) ) return( true );

	foreach( $contGroups as $contGroupId => $contGroup )
	{
		if( !$contGroup )
			continue;

		if( $contGroupId == 'crit' )
		{
			$item = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$item -> setAttribute( 'type', 'text/css' );

			if( !Style_ProcessCont_ItemApply( $ctxProcess, $sett, $settCache, $settCss, $settCdn, $head, $item, null, null, null,  $contGroup, true, true, $contGroupId, false, true, true ) )
				return( false );
		}
		else
		{
			$item = $doc -> createElement( 'link' );
			$item -> setAttribute( 'rel', 'stylesheet' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$item -> setAttribute( 'type', 'text/css' );

			if( !Style_ProcessCont_ItemApply( $ctxProcess, $sett, $settCache, $settCss, $settCdn, $head, $item, null, null, null,  $contGroup, true, false, $contGroupId, false, true, true ) )
				return( false );
		}
	}

	$processor -> InitFastDomSearch( $settCache, false );

	if( (isset($settCss[ 'fontPreload' ])?$settCss[ 'fontPreload' ]:null) )
	{
		$itemInsBefore = $head -> firstChild;
		foreach( array_unique( $processor -> aFonts ) as $font )
		{
			$itemFont = $doc -> createElement( 'link' );
			$itemFont -> setAttribute( 'rel', 'preload' );
			$itemFont -> setAttribute( 'as', 'font' );

			$itemFont -> setAttribute( 'crossorigin', '' );
			$itemFont -> setAttribute( 'href', str_replace( '::bhkdyqcetujyi::', '', trim( $font -> getURL() -> getString() ) ) );
			$head -> insertBefore( $itemFont, $itemInsBefore );

			if( (isset($ctxProcess[ 'chunksEnabled' ])?$ctxProcess[ 'chunksEnabled' ]:null) )
				ContentMarkSeparate( $itemFont, false );
		}
	}

	if( isset( $ctxProcess[ 'lrn' ] ) )
	{
		if( !$processor -> writeLrnData( $ctxProcess, $ctxProcess[ 'lrnDsc' ], $ctxProcess[ 'lrnDataPath' ] ) )
			return( false );
	}
	else if( isset( $ctxProcess[ 'lrnDsc' ] ) )
		$processor -> readLrnDataFinish( $ctxProcess, $ctxProcess[ 'lrnDsc' ], $ctxProcess[ 'lrnDataPath' ] );

	unset( $processor );
	return( true );
}

class StyleProcessor
{
	protected $doc;
	protected $rootElem;
	protected $xpath;
	protected $xpathSkeleton;
	protected $sklCssSelExcls;
	protected $cnvCssSel2Xpath;

	public $cssSelFs;
	protected $docFs;

	protected $cssParser;
	protected $cssParserCurObjId;
	protected $curSelector;
	protected $cssFmt;
	protected $cssFmtSimple;
	protected $cssFmtMin;
	protected $minifier;

	protected $_aDepBegin;
	protected $_aCssSelIsCritCache;
	protected $_aAdjustContCache;
	protected $_aXpathSelCache;
	protected $_xpathCssSelCache;

	private $_aCssSelIsCritRtCache;

	public $aFonts;

	protected $aVoidSelector;

	function __construct( $doc, $rootElem, $lrnDsc = null, $docSkeleton = null, $sklCssSelExcls = null, $bCorrectErrors = true, $bTrace = false, $min = true )
	{
		$this -> doc = $doc;
		$this -> rootElem = $rootElem;
		$this -> xpath = new \DOMXPath( $doc );
		$this -> xpathSkeleton = $docSkeleton ? new \DOMXPath( $docSkeleton ) : null;
		$this -> minifier = new tubalmartin\CssMin\Minifier();

		$this -> sklCssSelExcls = array();
		if( is_array( $sklCssSelExcls ) )
			foreach( $sklCssSelExcls as $sklCssSelExcl )
				$this -> sklCssSelExcls[] = $sklCssSelExcl . 'S';

		$cssParserSett = Sabberworm\CSS\Settings::create() -> withKeepComments( false ) -> withMultibyteSupport( false ) -> withLenientParsing( Sabberworm\CSS\Settings::ParseErrMed | ( $bCorrectErrors ? Sabberworm\CSS\Settings::ParseErrHigh : 0 ) );

		if( $bTrace )
			$cssParserSett -> cbExceptionTracer = array( $this, '_trace' );

		$this -> cssParser = new Sabberworm\CSS\Parsing\ParserState( '', $cssParserSett );
		$this -> cssFmtSimple = new Sabberworm\CSS\OutputFormat();
		$this -> cssFmt = self::_GetRenderFormat( $min );
		$this -> cssFmtMin = self::_GetRenderFormat();

		$this -> aVoidSelector = array( new Sabberworm\CSS\Property\Selector( '&' ) );

		$this -> docFs = $docSkeleton ? $docSkeleton : $doc;
		$this -> cssSelFs = new CssSelFs( $this -> xpathSkeleton ? $this -> xpathSkeleton : $this -> xpath, $this -> cssParser -> getSettings(), CssSelFs::F_SUBSELS_SKIP_NAME | CssSelFs::F_PSEUDO_FORCE_TO_ANY | CssSelFs::F_FUNCTION_FORCE_TO_ANY | ( ( $lrnDsc || $this -> xpathSkeleton ) ? ( CssSelFs::F_ATTR_FORCE_TO_ANY | CssSelFs::F_COMB_ADJACENT_FORCE_TO_ANY ) : 0 ) );

		$this -> cnvCssSel2Xpath = new Symfony\Component\CssSelector\XPath\Translator();
		$this -> cnvCssSel2Xpath -> registerExtension( new CssToXPathHtmlExtension( $this -> cnvCssSel2Xpath ) );
		$this -> cnvCssSel2Xpath -> registerExtension( new CssToXPathNormalizedAttributeMatchingExtension() );
		$this -> cnvCssSel2Xpath -> registerParserShortcut( new Symfony\Component\CssSelector\Parser\Shortcut\EmptyStringParser() );
		$this -> cnvCssSel2Xpath -> registerParserShortcut( new Symfony\Component\CssSelector\Parser\Shortcut\ElementParser() );
		$this -> cnvCssSel2Xpath -> registerParserShortcut( new Symfony\Component\CssSelector\Parser\Shortcut\ClassParser() );
		$this -> cnvCssSel2Xpath -> registerParserShortcut( new Symfony\Component\CssSelector\Parser\Shortcut\HashParser() );

		$this -> _aDepBegin = array();
		$this -> _aCssSelIsCritCache = array();
		$this -> _aAdjustContCache = array();
		$this -> _aXpathSelCache = array();

		$this -> _aCssSelIsCritRtCache = array();
		$this -> aFonts = array();

	}

	function __destruct()
	{

	}

	static private function _EscapeNonStdParts( $cont, $escape )
	{
		if( $escape )
		{
			$cont = preg_replace_callback( '@{{(\\w+)}}@',
				function( array $matches )
				{
					return( 'TMPSYM293654_DBLSCOPEOPEN' . $matches[ 1 ] . 'TMPSYM293654_DBLSCOPECLOSE' );
				}
			, $cont );

			$cont = str_replace( '&gt;', 'TMPSYM293654_GT', $cont );

			return( $cont );
		}

		$cont = str_replace( array( 'TMPSYM293654_DBLSCOPEOPEN', 'TMPSYM293654_DBLSCOPECLOSE', 'TMPSYM293654_GT' ), array( '{{', '}}', '&gt;' ), $cont );
		return( $cont );
	}

	function Init( &$ctxProcess )
	{
		$this -> _aDepBegin = Gen::ArrCopy( $ctxProcess[ 'deps' ] );
	}

	function InitFastDomSearch( $settCache, $init = true )
	{
		for( $item = null; $item = HtmlNd::GetNextTreeChild( $this -> doc, $item );  )
		{
			if( ContentProcess_IsAborted( $settCache ) ) return;

			if( $item -> nodeType != XML_ELEMENT_NODE )
				continue;

			if( !$init )
			{
				if( $item -> hasAttribute( 'class' ) )
					$item -> setAttribute( 'class', str_replace( array( '| ', ' |', 'SEP535643564' ), array( '', '', '|' ), $item -> getAttribute( 'class' ) ) );

				$this -> cssSelFs -> _deinitItemData( $item );

				continue;
			}

			if( $item -> hasAttribute( 'class' ) )
			{
				$sClasses = Ui::SpacyClassAttr( $item -> getAttribute( 'class' ) );
				$item -> setAttribute( 'class', '| ' . str_replace( '|', 'SEP535643564', $sClasses ) . ' |' );
			}
			else
				$sClasses = null;

			if( $this -> docFs === $this -> doc )
				$this -> cssSelFs -> _initItemData( $item, $sClasses );

		}

		if( $this -> docFs === $this -> doc )
			return;

		for( $item = null; $item = HtmlNd::GetNextTreeChild( $this -> docFs, $item );  )
		{
			if( ContentProcess_IsAborted( $settCache ) ) return;

			if( $item -> nodeType != XML_ELEMENT_NODE )
				continue;

			if( !$init )
			{
				$this -> cssSelFs -> _deinitItemData( $item );
				continue;
			}

			if( $item -> hasAttribute( 'class' ) )
				$sClasses = $item -> getAttribute( 'class' );
			else
				$sClasses = null;

			$this -> cssSelFs -> _initItemData( $item, $sClasses );
		}

	}

	function AdjustCont( $extract, &$aCritFonts, &$ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, &$cont, $isInlined )
	{
		if( isset( $ctxProcess[ 'lrnDsc' ] ) )
		{
			$contHash = md5( $cont, true );

			$res = (isset($this -> _aAdjustContCache[ $contHash ])?$this -> _aAdjustContCache[ $contHash ]:null);
			if( is_array( $res ) )
			{
				$ok = true;
				foreach( $res as $contPartId => &$contPart )
				{
					if( $contPart === '' )
						continue;

					$contPart = ReadSc( $ctxProcess, $settCache, $contPart, 'css' );
					if( $contPart === null )
					{
						$ok = false;
						break;
					}
				}

				if( $ok )
				{
					$cont = $res[ 'nonCrit' ];
					unset( $res[ 'nonCrit' ] );
					return( $res );
				}
			}
			else if( $res === false )
			{
				return( null );
			}
			else if( $res === '' )
			{
				$cont = '';
				return( true );
			}
			else if( $res )
			{
				$contPart = ReadSc( $ctxProcess, $settCache, $res, 'css' );
				if( $contPart !== null )
				{
					$cont = $contPart;
					return( true );
				}
			}
		}

		$res = $this -> _AdjustCont( $extract, $aCritFonts, $ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, $cont, $isInlined );
		if( $res === false )
			return( false );

		if( isset( $ctxProcess[ 'lrn' ] ) )
		{
			if( is_array( $res ) )
			{
				$resLrn = array();
				foreach( array_merge( array( 'nonCrit' => $cont ), $res ) as $contPartId => $contPart )
				{
					$oiCi = ( $contPart !== '' ) ? UpdSc( $ctxProcess, $settCache, 'css', $contPart ) : '';
					if( $oiCi === false )
						return( false );

					$resLrn[ $contPartId ] = $oiCi;
				}

				$this -> _aAdjustContCache[ $contHash ] = $resLrn;
			}
			else if( $res === null )
				$this -> _aAdjustContCache[ $contHash ] = false;
			else
			{
				$oiCi = ( $cont !== '' ) ? UpdSc( $ctxProcess, $settCache, 'css', $cont ) : '';
				if( $oiCi === false )
					return( false );

				$this -> _aAdjustContCache[ $contHash ] = $oiCi;
			}
		}

		return( $res );
	}

	function _AdjustCont( $extract, &$aCritFonts, &$ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, &$cont, $isInlined )
	{
		RemoveZeroSpace( $cont );

		$cont = self::_EscapeNonStdParts( $cont, true );

		$this -> curSelector = null;
		$this -> cssParser -> setText( $cont );

		$cssDoc = new Sabberworm\CSS\CSSList\Document( $this -> cssParser -> currentPos() );

		try
		{
			$cssDoc -> parseEx( $this -> cssParser );
		}
		catch( \Exception $e )
		{
			$this -> cssParser -> traceException( $e );

			if( (isset($settCss[ 'corrErr' ])?$settCss[ 'corrErr' ]:null) )
			{
				if( !$extract )
					return( null );

				$contExtracted = self::_EscapeNonStdParts( $cont, false );
				$cont = '';
				return( array( 'crit' => $contExtracted ) );
			}
		}

		$cssCritDoc = new Sabberworm\CSS\CSSList\Document();
		$isCritDocAdjusted = false;
		$cssFontsDoc = new Sabberworm\CSS\CSSList\Document();
		$isFontsDocAdjusted = false;
		$isAdjusted = false;

		$blockParents = array( $cssDoc );
		$blockParentsCrit = array( $cssCritDoc );
		$blockParentsFonts = array( $cssFontsDoc );

		foreach( ( $aCritFonts !== null ? array( 'main', 'fonts' ) : array( '' ) ) as $stage )
		{
			foreach( $cssDoc -> getContents() as $i )
			{
				if( $i instanceof Sabberworm\CSS\Property\Charset )
				{
					if( !$stage || $stage == 'main' )
					{
						$cssCritDoc -> append( $i );
						$cssFontsDoc -> append( $i );
					}
				}
				else
				{
					$r = $this -> _AdjustContBlock( $stage, $aCritFonts, $i, '', $blockParents, $blockParentsCrit, $blockParentsFonts, $isCritDocAdjusted, $isFontsDocAdjusted, $extract, $ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, $isInlined );
					if( $r === false )
						return( false );
					if( $r )
						$isAdjusted = true;
				}

				if( ContentProcess_IsAborted( $settCache ) ) return( null );
			}
		}

		$min = (isset($settCss[ 'min' ])?$settCss[ 'min' ]:null);

		if( !$isAdjusted && !$isCritDocAdjusted && !$isFontsDocAdjusted && !$min )
		{
			if( !$extract )
				return( null );
			return( array( 'crit' => '' ) );
		}

		$cont = $this -> RenderData( $cssDoc );

		if( !$extract )
			return( ( $min || $isAdjusted ) ? true : null );

		$res = array();
		$res[ 'crit' ] = $isCritDocAdjusted ? $this -> RenderData( $cssCritDoc ) : '';
		if( $isFontsDocAdjusted )
			$res[ 'fonts' ] = $this -> RenderData( $cssFontsDoc );

		return( $res );
	}

	function GetRenderFormatMin()
	{
		return( $this -> cssFmtMin );
	}

	function RenderData( $renderable )
	{
		return( self::_EscapeNonStdParts( trim( $renderable -> renderWhole( $this -> cssFmt ) ), false ) );
	}

	static function AppendSelectors( $aSel, $add )
	{
		$aSelBlock = array();
		foreach( ( array )$add as $addA )
		{
			foreach( $aSel as $sel )
			{

				$sel .= $addA;
				$aSelBlock[] = $sel;
			}
		}

		return( $aSelBlock );
	}

	static function ImgLazyBgGetStyleBlocks( $ctxProcess, $aSel, $lazyBg, $important = false )
	{
		$ruleAdd = new Sabberworm\CSS\Rule\Rule( 'background-image' );
		$ruleAdd -> setValue( $lazyBg -> info ? new Sabberworm\CSS\Value\URL( new Sabberworm\CSS\Value\CSSString( LazyLoad_SrcSubst( $ctxProcess, $lazyBg -> info, true ) ) ) : 'none' );
		$ruleAdd -> setIsImportant( $important || $lazyBg -> isImportant );

		$block = new Sabberworm\CSS\RuleSet\DeclarationBlock();
		$block -> setSelectors( StyleProcessor::AppendSelectors( $aSel, array( '.lzl:not(.lzl-ed)', '.lzl-ing:not(.lzl-ed)' ) ) );
		$block -> addRule( $ruleAdd );

		return( array( $block ) );
	}

	static function ImgSzAlternativesGetStyleBlocks( $ctxProcess, $aSel, $imgSzAlternatives, $important = false, $bLazyBg = false )
	{
		$aBlock = array();

		if( $imgSzAlternatives -> isImportant )
			$important = true;

		foreach( $imgSzAlternatives -> a as $dim => $imgSzAlternative )
		{

			if( ulyjqbuhdyqcetbhkiy( $imgSzAlternative[ 'img' ] ) )
				$imgSzAlternative[ 'img' ] = '::bhkdyqcetujyi::' . $imgSzAlternative[ 'img' ];

			$aSelApply = StyleProcessor::AppendSelectors( $aSel, '[data-ai-bg*="-' . $dim . '-"]' );

			$block = new Sabberworm\CSS\RuleSet\DeclarationBlock();
			$block -> setSelectors( $aSelApply );

			$aBlock[] = $block;

			{
				$ruleAdd = new Sabberworm\CSS\Rule\Rule( 'background-image' );
				$ruleAdd -> setValue( $imgSzAlternative[ 'img' ] !== null ? new Sabberworm\CSS\Value\URL( new Sabberworm\CSS\Value\CSSString( $imgSzAlternative[ 'img' ] ) ) : 'none' );
				$ruleAdd -> setIsImportant( $important );
				$block -> addRule( $ruleAdd );
				unset( $ruleAdd );
			}

			if( $bLazyBg && $imgSzAlternative[ 'img' ] !== null )
			{
				$ruleAdd = new Sabberworm\CSS\Rule\Rule( '--lzl-bg-img' );
				$ruleAdd -> setValue( new Sabberworm\CSS\Value\CSSString( $imgSzAlternative[ 'img' ] ) );
				$block -> addRule( $ruleAdd );
				unset( $ruleAdd );

			}
		}

		if( $bLazyBg )
			Gen::ArrAdd( $aBlock, StyleProcessor::ImgLazyBgGetStyleBlocks( $ctxProcess, $aSel, new AnyObj( array( 'info' => $imgSzAlternatives -> info, 'isImportant' => $important ) ), $important ) );

		return( $aBlock );
	}

	private function _AdjustContBlock( $stage, &$aCritFonts, $block, $selParent, &$blockParents, &$blockParentsCrit, &$blockParentsFonts, &$isCritDocAdjusted, &$isFontsDocAdjusted, $extract, &$ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, $isInlined )
	{
		$isAdjusted = null;
		$canMoveTo = false;
		$aBlockAddAfter = array();

		if( $block instanceof Sabberworm\CSS\RuleSet\RuleSet )
		{
			$blockParents[] = $block;
			$blockParentsCrit[] = null;
			$blockParentsFonts[] = null;
		}

		if( $block instanceof Sabberworm\CSS\Property\Import )
		{
			if( !$stage || $stage == 'main' )
			{
				$r = self::_AdjustUrls( array( $block -> getLocation() ), false, $ctxProcess, $settCache, $settImg, $settCdn, $src, $isInlined );
				if( $r === false )
					return( false );
				if( $r )
					$isAdjusted = true;
				$canMoveTo = true;
			}
		}
		else if( $block instanceof Sabberworm\CSS\RuleSet\AtRuleSet && $block -> atRuleName() == 'font-face' )
		{
			if( !$stage || $stage == 'fonts' )
			{

				if( (isset($settCss[ 'fontOptLoad' ])?$settCss[ 'fontOptLoad' ]:null) )
				{
					$fontNameExpr = Gen::GetArrField( $settCss, array( 'font', 'optLoadNameExpr' ), '' );
					if( $fontName = $block -> getRule( 'font-family' ) )
						$fontName = $fontName -> getValue();
					if( !strlen( $fontNameExpr ) || ( IsStrRegExp( $fontNameExpr ) ? @preg_match( $fontNameExpr, $fontName ) : stripos( $fontName, $fontNameExpr ) !== false ) )
					{
						$rule = new Sabberworm\CSS\Rule\Rule( 'font-display' );
						$rule -> setValue( (isset($settCss[ 'fontOptLoadDisp' ])?$settCss[ 'fontOptLoadDisp' ]:null) ? $settCss[ 'fontOptLoadDisp' ] : 'swap' );
						$block -> removeRule( $rule -> getRule() );
						$block -> addRule( $rule );

						$isAdjusted = true;
					}
				}

				if( (isset($settCss[ 'fontPreload' ])?$settCss[ 'fontPreload' ]:null) )
				{
					foreach( $block -> getRules( 'src' ) as $rule )
					{
						self::_GetCssRuleValUrlObjs( $rule -> getValue(), $this -> aFonts );

					}
				}

				$aDepFonts = null;

				$r = self::AdjustRuleSet( $block, $aDepFonts, new AnyObj(), $this -> doc, $ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, $isInlined );
				if( $r === false )
					return( false );

				if( $r )
					$isAdjusted = true;

				if( (isset($settCss[ 'fontCrit' ])?$settCss[ 'fontCrit' ]:null) )
					$canMoveTo = true;
				else
				{
					if( (isset($settCss[ 'delayNonCritWithJs' ])?$settCss[ 'delayNonCritWithJs' ]:null) )
					{
						if( $aCritFonts !== null )
						{
							$isFontCrit = false;

							$names = array();
							foreach( $block -> getRules( 'font-family' ) as $rule )
								self::_GetCssRuleValFontNames( $rule -> getValue(), $names );

							foreach( $names as $name => $namesVal )
								if( (isset($aCritFonts[ $name ])?$aCritFonts[ $name ]:null) )
									$isFontCrit = true;

							if( $isFontCrit )
								$canMoveTo = 'fonts';
						}
						else
							$canMoveTo = 'fonts';
					}
				}
			}
		}
		else if( $block instanceof Sabberworm\CSS\RuleSet\RuleSet )
		{
			$selectors = null;

			if( !$stage || $stage == 'main' )
			{
				$isCrit = false;

				if( !$block -> isEmpty() )
				{
					$adjustes = new AnyObj();
					$adjustes -> lazyBg = null;
					$adjustes -> imgSzAlternatives = null;

					if( (isset($settImg[ 'szAdaptBg' ])?$settImg[ 'szAdaptBg' ]:null) && !Images_CheckSzAdaptExcl( $ctxProcess, $this -> doc, $settImg, ( string )$block ) )
						$adjustes -> imgSzAlternatives = new ImgSzAlternatives();

					if( $selectors === null )
						$selectors = $this -> _GetBlockSels( $selParent, $block, $settCss );

					if( $this -> _AdjustBlock( $selectors, $block, $extract, $isCrit, $aCritFonts, $ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, $isInlined, $adjustes ) )
						$isAdjusted = true;

					if( $adjustes -> imgSzAlternatives && !$adjustes -> imgSzAlternatives -> isEmpty() )
						Gen::ArrAdd( $aBlockAddAfter, StyleProcessor::ImgSzAlternativesGetStyleBlocks( $ctxProcess, $block -> getSelectors(), $adjustes -> imgSzAlternatives, false, !!$adjustes -> lazyBg ) );
					else if( $adjustes -> lazyBg )
						Gen::ArrAdd( $aBlockAddAfter, StyleProcessor::ImgLazyBgGetStyleBlocks( $ctxProcess, $block -> getSelectors(), $adjustes -> lazyBg ) );

					unset( $adjustes );
				}

				if( $isCrit )
					$canMoveTo = true;
			}

			{
				if( !$stage || $stage == 'main' )
				{
					if( (isset($settCss[ 'min' ])?$settCss[ 'min' ]:null) === true && $block instanceof Sabberworm\CSS\RuleSet\AtRuleSet )
						$block -> setAtRuleArgs( $this -> _SelectorMinify( $block -> atRuleArgs() ) );
				}

				if( $selectors === null )
					$selectors = $this -> _GetBlockSels( $selParent, $block, $settCss );

				$selFull = self::_getFullBlockSel( $selectors );

				foreach( $block -> getContents() as $i )
				{
					$r = $this -> _AdjustContBlock( $stage, $aCritFonts, $i, $selFull, $blockParents, $blockParentsCrit, $blockParentsFonts, $isCritDocAdjusted, $isFontsDocAdjusted, $extract, $ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, $isInlined );
					if( $r === false )
						return( false );
					if( $r )
						$isAdjusted = true;
					if( ContentProcess_IsAborted( $settCache ) ) return( null );
				}

				unset( $selFull );
			}
		}
		else
		{
			if( !$stage || $stage == 'main' )
			{
				$canMoveTo = true;
			}
		}

		$isCrit = $extract && $canMoveTo;
		if( $isCrit )
		{
			if( $canMoveTo === 'fonts' )
			{
				$blockParentsMoveTo = &$blockParentsFonts;
				$isFontsDocAdjusted = true;
			}
			else
			{
				$blockParentsMoveTo = &$blockParentsCrit;
				$isCritDocAdjusted = true;
			}

			for( $iParent = 0; $iParent < count( $blockParentsMoveTo ); $iParent++ )
			{
				if( $blockParentsMoveTo[ $iParent ] )
					continue;

				$oParent = $blockParents[ $iParent ];
				$oParentClone = null;
				if( $oParent instanceof Sabberworm\CSS\RuleSet\DeclarationBlock )
				{
					$oParentClone = new Sabberworm\CSS\RuleSet\DeclarationBlock();
					$oParentClone -> setSelectors( $oParent -> getSelectors() );
				}
				else if( $oParent instanceof Sabberworm\CSS\RuleSet\AtRuleBlockList )
					$oParentClone = new Sabberworm\CSS\CSSList\AtRuleBlockList( $oParent -> atRuleName(), $oParent -> atRuleArgs() );
				else if( $oParent instanceof Sabberworm\CSS\RuleSet\AtRuleSet )
					$oParentClone = new Sabberworm\CSS\RuleSet\AtRuleSet( $oParent -> atRuleName(), $oParent -> atRuleArgs() );
				else
					$oParentClone = new Sabberworm\CSS\RuleSet\AtRuleSet( 'media all' );

				$blockParentsMoveTo[ $iParent ] = $oParentClone;
				$blockParentsMoveTo[ $iParent - 1 ] -> append( $oParentClone );
			}

			if( $block instanceof Sabberworm\CSS\RuleSet\RuleSet )
			{
				$blockParentsMoveTo[ count( $blockParentsMoveTo ) - 1 ] -> moveRulesFrom( $block );
				if( $aBlockAddAfter )
					$blockParentsMoveTo[ count( $blockParentsMoveTo ) - 2 ] -> append( $aBlockAddAfter );

				if( $block -> isEmpty() && !$block -> getContents() )
				{
					$blockParents[ count( $blockParents ) - 2 ] -> remove( $block );
					$isAdjusted = true;
				}
			}
			else
			{
				$blockParents[ count( $blockParents ) - 1 ] -> remove( $block );
				$blockParentsMoveTo[ count( $blockParentsMoveTo ) - 1 ] -> append( $block );
			}
		}
		else
		{
			if( $block instanceof Sabberworm\CSS\RuleSet\RuleSet )
				if( $aBlockAddAfter )
					$blockParents[ count( $blockParents ) - 2 ] -> insert( $aBlockAddAfter, $block );
		}

		if( $block instanceof Sabberworm\CSS\RuleSet\RuleSet )
		{
			if( (isset($settCss[ 'min' ])?$settCss[ 'min' ]:null) === true && $block -> isEmpty() && !$block -> getContents() )
			{
				$blockParents[ count( $blockParents ) - 2 ] -> remove( $block );
				$isAdjusted = true;
			}

			array_pop( $blockParents );
			array_pop( $blockParentsCrit );
			array_pop( $blockParentsFonts );
		}

		return( $isAdjusted );
	}

	static private function _GetRenderFormat( $min = true )
	{
		if( $min )
		{
			$format = Sabberworm\CSS\OutputFormat::createCompact();
			$format -> setSemicolonAfterLastRule( false );
			$format -> setSpaceAfterRuleName( '' );
			$format -> setSpaceBeforeImportant( '' );
		}
		else
			$format = Sabberworm\CSS\OutputFormat::createPretty() -> set( 'Space*Rules', "\r\n" ) -> set( 'Space*Blocks', "\r\n" ) -> setSpaceBetweenBlocks( "\r\n\r\n" );

		return( $format );
	}

	private function _GetBlockSels( $selParent, $ruleSet, $settCss )
	{
		$selectors = null;
		if( $ruleSet instanceof Sabberworm\CSS\RuleSet\DeclarationBlock )
			$selectors = $ruleSet -> getSelectors();

		if( !$selectors )
			$selectors = $this -> aVoidSelector;

		foreach( $selectors as $i => $sel )
		{

			if( (isset($settCss[ 'min' ])?$settCss[ 'min' ]:null) === true )
				$sel -> setSelector( $this -> _SelectorMinify( $sel -> getSelector() ) );

			$selectors[ $i ] = array( self::_getFullSel( $sel -> getSelector(), $selParent ), $sel );
		}

		return( $selectors );
	}

	private function _AdjustBlock( $selectors, $ruleSet, $extract, &$isCrit, &$aCritFonts, &$ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, $isInlined, $adjustes )
	{
		if( !$extract )
			$isCrit = true;

		foreach( $selectors as $sel )
		{
			if( $isCrit )
				break;

			foreach( Gen::GetArrField( $settCss, array( 'nonCrit', 'autoExcls' ), array() ) as $excl )
			{
				if( @preg_match( $excl, $sel[ 1 ] -> getSelector() ) )
				{
					$isCrit = true;
					break;
				}
			}
		}

		$ctxItems = new AnyObj();
		$isAdjusted = self::AdjustRuleSet( $ruleSet, $aCritFonts, $ctxItems, $this -> doc, $ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, $isInlined, $adjustes -> imgSzAlternatives );

		if( (isset($ctxItems -> lazyBg)?$ctxItems -> lazyBg:null) )
			$adjustes -> lazyBg = $ctxItems -> lazyBg;

		if( (isset($ctxItems -> lazyBg)?$ctxItems -> lazyBg:null) || ( $adjustes -> imgSzAlternatives && !$adjustes -> imgSzAlternatives -> isEmpty() ) )
			foreach( $selectors as $sel )
				if( $sel = $this -> cssSelToXPath( $sel[ 0 ] ) )
				{
					$scope = 0;
					if( (isset($ctxItems -> lazyBg)?$ctxItems -> lazyBg:null) )
						$scope |= 1;
					if( $adjustes -> imgSzAlternatives && !$adjustes -> imgSzAlternatives -> isEmpty() )
						$scope |= 2;

					$this -> _aXpathSelCache[ $sel ] = $scope;
				}

		if( !$isCrit )
			foreach( $selectors as $sel )
			{
				$this -> curSelector = $sel[ 1 ];
				if( $this -> isCssSelCrit( $ctxProcess, $sel[ 0 ] ) )
				{
					$isCrit = true;
					break;
				}
			}

		return( $isAdjusted );
	}

	function ApplyItems( &$ctxProcess, $settImg )
	{
		foreach( $this -> _aXpathSelCache as $xpathSel => $scope )
			if( $items = $this -> xpathEvaluate( $xpathSel ) )
				foreach( $items as $item )
				{
					if( ( int )$scope & 1 )
						StyleProcessor::AdjustItemLazyBg( $ctxProcess, $settImg, $this -> doc, $item, true );

					if( ( int )$scope & 2 )
						StyleProcessor::AdjustItemAdaptImg( $ctxProcess, $settImg, $this -> doc, $item );
				}
	}

	static function AdjustItemLazyBg( &$ctxProcess, $settImg, $doc, $item, $bFromStyle = false )
	{
		if( Images_CheckLazyExcl( $ctxProcess, $doc, $settImg, $item ) )
			return;

		$ctxProcess[ 'lazyload' ] = true;
		HtmlNd::AddRemoveAttrClass( $item, array( 'lzl' ) );

		if( $bFromStyle && !$item -> hasAttribute( 'data-lzl-bg' ) )
			$item -> setAttribute( 'data-lzl-bg', '' );
	}

	static function AdjustItemAdaptImg( &$ctxProcess, $settImg, $doc, $item, $itemCssClass = null )
	{
		if( Images_CheckSzAdaptExcl( $ctxProcess, $doc, $settImg, $item ) )
		    return;

		static $g_defSizes = null;

		if( $g_defSizes === null )
		{
			$g_defSizes = '';

			foreach( array( 				1920,	1366,	992,	768,	480,	360,	120	 ) as $dim )
				$g_defSizes .= '-' . $dim;

			$g_defSizes .= '-0-';
		}

		$ctxProcess[ 'imgAdaptive' ] = true;
		$item -> setAttribute( 'data-ai-bg', $g_defSizes );
		if( (isset($settImg[ 'szAdaptDpr' ])?$settImg[ 'szAdaptDpr' ]:null) )
			$item -> setAttribute( 'data-ai-dpr', 'y' );
		HtmlNd::AddRemoveAttrClass( $item, array( 'ai-bg', $itemCssClass ) );
	}

	static function AdjustRuleSet( $ruleSet, &$aDepFonts, $ctxItems, $doc, &$ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, $isInlined, $imgSzAlternatives = null )
	{
		$isAdjusted = null;

		$urlDomainUrl = $isInlined ? null : Net::GetSiteAddrFromUrl( $src, true );
		$urlPath = $isInlined ? $ctxProcess[ 'requestUriPath' ] : Gen::GetFileDir( Net::Url2Uri( $src ) );

		{
			$urls = array();
			foreach( $ruleSet -> getRules() as $rule )
			{
				if( (isset($settCss[ 'min' ])?$settCss[ 'min' ]:null) === true )
					self::_RuleMinify( $rule );

				$skip = false;

				{
					switch( $rule -> getRule() )
					{
					case 'background-image':
					case 'background':
					case 'mask-image':
					case '-webkit-mask-image':
						$skip = true;
						break;
					}
				}

				if( $aDepFonts !== null )
				{
					switch( $rule -> getRule() )
					{

					case 'font-family':
					case 'font':
						self::_GetCssRuleValFontNames( $rule -> getValue(), $aDepFonts );
						break;
					}
				}

				if( !$skip )
					self::_GetCssRuleValUrlObjs( $rule -> getValue(), $urls );
			}

			if( $ruleSet instanceof Sabberworm\CSS\RuleSet\AtRuleSet && $ruleSet -> atRuleName() == 'font-face' && Gen::GetArrField( $settCss, array( 'font', 'deinlLrg' ), false ) )
			{
				foreach( $urls as $oUrl )
				{
					$url = trim( $oUrl -> getURL() -> getString() );
					if( !Ui::IsSrcAttrData( $url ) )
						continue;

					$data = Ui::GetSrcAttrData( $url, $type );
					if( !$data || strlen( $data ) < Gen::GetArrField( $settCss, array( 'font', 'deinlLrgSize' ), 0 ) )
						continue;

					$type = Fs::GetFileTypeFromMimeContentType( $type );
					if( !UpdSc( $ctxProcess, $settCache, array( 'font', $type ? $type : 'bin' ), $data, $url ) )
						return( false );

					$oUrl -> setURL( new Sabberworm\CSS\Value\CSSString( $url ) );
				}
			}

			$r = self::_AdjustUrls( $urls, $ruleSet instanceof Sabberworm\CSS\RuleSet\AtRuleSet && $ruleSet -> atRuleName() == 'font-face', $ctxProcess, $settCache, $settImg, $settCdn, $src, $isInlined );
			if( $r === false )
				return( false );
			if( $r )
				$isAdjusted = true;
		}

		$isLazy = Gen::GetArrField( $settImg, array( 'lazy', 'load' ), false );
		$itemsLazyAdjusted = false;
		$ctxItems -> lazyBg = null;
		$bgImgSzAlternativesProcessed = false;

		foreach( $ruleSet -> getRules( 'background-image' ) as $rule )
		{

			$urls = array(); self::_GetCssRuleValUrlObjs( $rule -> getValue(), $urls );
			foreach( $urls as $url )
			{
				$bgImgSrcOrig = html_entity_decode( trim( $url -> getURL() -> getString() ) );
				if( !$bgImgSrcOrig )
				{
					$url -> setReplacer( 'none' );
					continue;
				}

				$adjustedItem = false;

				$bgImgSrc = new ImgSrc( $bgImgSrcOrig );
				$bgImgSrc -> Init( $ctxProcess, $urlDomainUrl, $urlPath );
				if( $bgImgSrc -> src != $bgImgSrcOrig )
					$adjustedItem = true;

				if( !is_a( $rule -> getValue(), 'seraph_accel\\Sabberworm\\CSS\\Value\\RuleValueList' ) && $imgSzAlternatives && !$bgImgSzAlternativesProcessed )
				{
					if( Images_ProcessSrcSizeAlternatives( $imgSzAlternatives, $ctxProcess, $bgImgSrc, $settCache, $settImg, $settCdn, true, $rule -> getIsImportant() ) === false )
						return( false );

					if( $imgSzAlternatives && !$imgSzAlternatives -> isEmpty() )
					{
						$imgInfo = $bgImgSrc -> GetInfo();

						$ruleAdd = new Sabberworm\CSS\Rule\Rule( '--ai-bg-sz' );
						$ruleAdd -> setValue( ( string )Gen::GetArrField( $imgInfo, array( 'cx' ), 0 ) . ' ' . ( string )Gen::GetArrField( $imgInfo, array( 'cy' ), 0 ) );
						$ruleSet -> addRule( $ruleAdd );
						unset( $ruleAdd );

						$isAdjusted = true;
					}

					$bgImgSzAlternativesProcessed = true;
				}

				$r = Images_ProcessSrc( $ctxProcess, $bgImgSrc, $settCache, $settImg, $settCdn );
				if( $r === false )
					return( false );

				if( $r )
					$adjustedItem = true;

				if( $isLazy && !$itemsLazyAdjusted && !Ui::IsSrcAttrData( $bgImgSrc -> src ) )
				{
					if( isset( $ctxItems -> item ) )
					{
						if( Images_ProcessItemLazyBg( $ctxProcess, $doc, $settImg, $ctxItems -> item, $bgImgSrc ) )
						{
							$adjustedItem = true;
							$itemsLazyAdjusted = true;
							$ctxItems -> lazyBgItem = $bgImgSrc -> GetInfo();
						}
					}
					else
					{
						$ctxItems -> lazyBg = new AnyObj();
						$ctxItems -> lazyBg -> info = $bgImgSrc -> GetInfo();
						$ctxItems -> lazyBg -> isImportant = $rule -> getIsImportant();
						$itemsLazyAdjusted = true;

						$ruleAdd = new Sabberworm\CSS\Rule\Rule( '--lzl-bg-img' );
						$ruleAdd -> setValue( new Sabberworm\CSS\Value\CSSString( $bgImgSrc -> src ) );
						$ruleSet -> addRule( $ruleAdd );
						unset( $ruleAdd );

						$isAdjusted = true;
					}
				}

				if( ulyjqbuhdyqcetbhkiy( $bgImgSrc -> src ) )
				{
					$bgImgSrc -> src = '::bhkdyqcetujyi::' . $bgImgSrc -> src;
					$adjustedItem = true;
				}

				if( $adjustedItem )
				{
					$isAdjusted = true;
					$url -> setURL( new Sabberworm\CSS\Value\CSSString( $bgImgSrc -> src ) );
				}

				$bgImgSrc -> dispose();
				unset( $bgImgSrc );
			}
		}

		foreach( $ruleSet -> getRules( 'background' ) as $rule )
		{
			$urls = array(); self::_GetCssRuleValUrlObjs( $rule -> getValue(), $urls );
			foreach( $urls as $url )
			{
				$bgImgSrcOrig = html_entity_decode( trim( $url -> getURL() -> getString() ) );
				if( !$bgImgSrcOrig )
					continue;

				$adjustedItem = false;

				$bgImgSrc = new ImgSrc( $bgImgSrcOrig );
				$bgImgSrc -> Init( $ctxProcess, $urlDomainUrl, $urlPath );
				if( $bgImgSrc -> src != $bgImgSrcOrig )
					$adjustedItem = true;

				if( !self::_IsRuleValCompound( $rule -> getValue() ) && $imgSzAlternatives && !$bgImgSzAlternativesProcessed )
				{
					if( Images_ProcessSrcSizeAlternatives( $imgSzAlternatives, $ctxProcess, $bgImgSrc, $settCache, $settImg, $settCdn, true, $rule -> getIsImportant() ) === false )
						return( false );

					if( $imgSzAlternatives && !$imgSzAlternatives -> isEmpty() )
					{
						$imgInfo = $bgImgSrc -> GetInfo();

						$ruleAdd = new Sabberworm\CSS\Rule\Rule( '--ai-bg-sz' );
						$ruleAdd -> setValue( ( string )Gen::GetArrField( $imgInfo, array( 'cx' ), 0 ) . ' ' . ( string )Gen::GetArrField( $imgInfo, array( 'cy' ), 0 ) );
						$ruleSet -> addRule( $ruleAdd );
						unset( $ruleAdd );

						$isAdjusted = true;
					}

					$bgImgSzAlternativesProcessed = true;
				}

				$r = Images_ProcessSrc( $ctxProcess, $bgImgSrc, $settCache, $settImg, $settCdn );
				if( $r === false )
					return( false );

				if( $r )
					$adjustedItem = true;

				if( $isLazy && !$itemsLazyAdjusted && !Ui::IsSrcAttrData( $bgImgSrc -> src ) )
				{
					if( isset( $ctxItems -> item ) )
					{
						if( Images_ProcessItemLazyBg( $ctxProcess, $doc, $settImg, $ctxItems -> item, $bgImgSrc ) )
						{
							$adjustedItem = true;
							$itemsLazyAdjusted = true;
							$ctxItems -> lazyBgItem = $bgImgSrc -> GetInfo();
						}
					}
					else
					{
						$ctxItems -> lazyBg = new AnyObj();
						$ctxItems -> lazyBg -> info = $bgImgSrc -> GetInfo();
						$ctxItems -> lazyBg -> isImportant = $rule -> getIsImportant();
						$itemsLazyAdjusted = true;

						$ruleAdd = new Sabberworm\CSS\Rule\Rule( '--lzl-bg-img' );
						$ruleAdd -> setValue( new Sabberworm\CSS\Value\CSSString( $bgImgSrc -> src ) );
						$ruleSet -> addRule( $ruleAdd );
						unset( $ruleAdd );
					}
				}

				if( ulyjqbuhdyqcetbhkiy( $bgImgSrc -> src ) )
				{
					$bgImgSrc -> src = '::bhkdyqcetujyi::' . $bgImgSrc -> src;
					$adjustedItem = true;
				}

				if( $adjustedItem )
				{
					$isAdjusted = true;
					$url -> setURL( new Sabberworm\CSS\Value\CSSString( $bgImgSrc -> src ) );
				}

				$bgImgSrc -> dispose();
				unset( $bgImgSrc );
			}
		}

		foreach( array( 'mask-image', '-webkit-mask-image' ) as $ruleName )
		{
			foreach( $ruleSet -> getRules( $ruleName ) as $rule )
			{
				$urls = array(); self::_GetCssRuleValUrlObjs( $rule -> getValue(), $urls );
				foreach( $urls as $url )
				{
					$bgImgSrcOrig = html_entity_decode( trim( $url -> getURL() -> getString() ) );
					if( !$bgImgSrcOrig )
						continue;

					$adjustedItem = false;

					$bgImgSrc = new ImgSrc( $bgImgSrcOrig );
					$bgImgSrc -> Init( $ctxProcess, $urlDomainUrl, $urlPath );
					if( $bgImgSrc -> src != $bgImgSrcOrig )
						$adjustedItem = true;

					$r = Images_ProcessSrc( $ctxProcess, $bgImgSrc, $settCache, $settImg, $settCdn );
					if( $r === false )
						return( false );

					if( $r )
						$adjustedItem = true;

					if( ulyjqbuhdyqcetbhkiy( $bgImgSrc -> src ) )
					{
						$bgImgSrc -> src = '::bhkdyqcetujyi::' . $bgImgSrc -> src;
						$adjustedItem = true;
					}

					if( $adjustedItem )
					{
						$isAdjusted = true;
						$url -> setURL( new Sabberworm\CSS\Value\CSSString( $bgImgSrc -> src ) );
					}

					$bgImgSrc -> dispose();
					unset( $bgImgSrc );
				}
			}
		}

		return( $isAdjusted );
	}

	static function _IsRuleValCompound( $v )
	{
		if( !is_a( $v, 'seraph_accel\\Sabberworm\\CSS\\Value\\RuleValueList' ) )
			return( false );

		if( $v -> getListSeparator() == ',' )
			return( true );

		foreach( $v -> getListComponents() as $component )
			if( is_a( $component, 'seraph_accel\\Sabberworm\\CSS\\Value\\RuleValueList' ) && $component -> getListSeparator() == ',' )
				return( true );

		return( false );
	}

	static function _AdjustUrls( $urls, $isFont, &$ctxProcess, $settCache, $settImg, $settCdn, $src, $isInlined )
	{

		$isAdjusted = null;

		$urlDomainUrl = $isInlined ? null : Net::GetSiteAddrFromUrl( $src, true );
		$urlPath = $isInlined ? $ctxProcess[ 'requestUriPath' ] : Gen::GetFileDir( Net::Url2Uri( $src ) );

		foreach( $urls as $oUrl )
		{
			$url = trim( $oUrl -> getURL() -> getString() );

			if( !strlen( $url ) || Ui::IsSrcAttrData( $url ) || Gen::StrStartsWith( $url, '#' ) )
				continue;

			$urlNew = $url;
			$urlAdjusted = false;

			$srcInfo = GetSrcAttrInfo( $ctxProcess, $urlDomainUrl, $urlPath, $urlNew );
			if( $urlNew != $url )
				$urlAdjusted = true;

			$fileType = strtolower( Gen::GetFileExt( (isset($srcInfo[ 'srcWoArgs' ])?$srcInfo[ 'srcWoArgs' ]:null) ) );

			$isImg = false;
			switch( $fileType )
			{
			case 'jpeg':
			case 'jpg':
			case 'gif':
			case 'png':
			case 'webp':
			case 'bmp':
			case 'svg':
				$isImg = !$isFont;
				break;
			}

			if( $isImg )
			{

				$imgSrc = new ImgSrc( $urlNew, $srcInfo );

				$r = Images_ProcessSrcEx( $ctxProcess, $imgSrc, $settCache, $settImg );
				if( $r === false )
					return( false );

				if( $r )
					$urlAdjusted = true;

				$urlNew = $imgSrc -> src;
				unset( $imgSrc );
			}

			if( Cdn_AdjustUrl( $ctxProcess, $settCdn, $urlNew, $fileType ) )
				$urlAdjusted = true;
			if( Fullness_AdjustUrl( $ctxProcess, $urlNew, (isset($srcInfo[ 'srcUrlFullness' ])?$srcInfo[ 'srcUrlFullness' ]:null) ) )
				$urlAdjusted = true;

			if( $urlAdjusted )
				$isAdjusted = true;

			if( ulyjqbuhdyqcetbhkiy( $urlNew ) )
			{
				$urlNew = '::bhkdyqcetujyi::' . $urlNew;
				$urlAdjusted = true;
			}

			if( $urlAdjusted )
				$oUrl -> setURL( new Sabberworm\CSS\Value\CSSString( $urlNew ) );
		}

		return( $isAdjusted );
	}

	function _trace( $e )
	{
		$eS = ( $e instanceof Sabberworm\CSS\Parsing\SrcExcptn ) ? $e : null;
		$sev = $eS ? $eS -> getSeverity() : Sabberworm\CSS\Settings::ParseErrHigh;

		$sevLocId = LocId::Pack( $sev == Sabberworm\CSS\Settings::ParseErrHigh ? 'CssParseTrace_ErrHigh' : ( $sev == Sabberworm\CSS\Settings::ParseErrMed ? 'CssParseTrace_ErrMed' : 'CssParseTrace_ErrLow' ) );
		if( $this -> curSelector )
			$locId = LocId::Pack( 'CssParseSelTrace_%1$s%2$s%3$s%4$s%5$s%6$s', null, array( $sevLocId, $this -> cssParserCurObjId, ( string )$this -> cssParser -> currentLineCharNo( $this -> curSelector -> getPos() ), $e -> getMessage(), str_replace( array( "\t", "\n", "\r", "\0", "\x0B", "\v" ), array( '\\t', '\\n', '\\r', '\\0', '\\x0B', '\\v' ), $this -> cssSelFs -> parser -> getText() ), ( string )$this -> cssSelFs -> parser -> currentLineCharNo( $eS ? $eS -> getPos() : 0 ) ) );
		else
			$locId = LocId::Pack( 'CssParseTrace_%1$s%2$s%3$s%4$s', null, array( $sevLocId, $this -> cssParserCurObjId, ( string )$this -> cssParser -> currentLineCharNo( $eS ? $eS -> getPos() : 0 ), $e -> getMessage() ) );

		if( $sev !== Sabberworm\CSS\Settings::ParseErrLow )
			LastWarnDscs_Add( $locId );

	}

	function IsTraceEnabled()
	{
		return( $this -> cssParser -> isTraceEnabled() );
	}

	function SetCurObjectId( $id )
	{
		$this -> cssParserCurObjId = $id;
	}

	function ParseRuleSet( $data )
	{
		$this -> curSelector = null;
		$this -> cssParser -> setText( $data );

		$ruleSet = new Sabberworm\CSS\RuleSet\RuleSet();

		try
		{
			Sabberworm\CSS\RuleSet\RuleSet::parseRuleSet( $this -> cssParser, $ruleSet );
		}
		catch( \Exception $e )
		{
			$this -> cssParser -> traceException( $e );
			$ruleSet = null;
		}

		return( $ruleSet );
	}

	static function GetFirstImportSimpleAttrs( $ctxProcess, $import, $src )
	{
		if( preg_match( '@\\ssupports\\s*\\(@S', $import ) )
			return( null );

		try
		{
			$cssParser = new Sabberworm\CSS\Parser( $import, Sabberworm\CSS\Settings::create() -> withMultibyteSupport( false ) );
			$cssDoc = $cssParser -> parse();
			unset( $cssParser );
		}
		catch( \Exception $e )
		{
			return( null );
		}

		foreach( $cssDoc -> getContents() as $block )
		{
			if( $block instanceof Sabberworm\CSS\Property\Import )
			{
				$args = $block -> atRuleArgs();

				$url = $args[ 0 ];
				if( $url instanceof Sabberworm\CSS\Value\URL )
					$url = $url -> getURL();
				if( $url instanceof Sabberworm\CSS\Value\CSSString )
					$url = $url -> getString();

				if( gettype( $url ) !== 'string' )
					return( null );

				{
					$urlDomainUrl = $src ? Net::GetSiteAddrFromUrl( $src, true ) : null;
					$urlPath = $src ? Gen::GetFileDir( Net::Url2Uri( $src ) ) : $ctxProcess[ 'requestUriPath' ];
					$srcInfo = GetSrcAttrInfo( $ctxProcess, $urlDomainUrl, $urlPath, $url );
					Fullness_AdjustUrl( $ctxProcess, $url, (isset($srcInfo[ 'srcUrlFullness' ])?$srcInfo[ 'srcUrlFullness' ]:null) );
				}

				$res = array( 'url' => $url );
				if( count( $args ) > 1 )
					$res[ 'media' ] = ( string )$args[ 1 ];

				return( $res );
			}
		}

		return( null );
	}

	static function cssSelToXPathEx( $cnvCssSel2Xpath, string $sel )
	{

		$pos = strpos( $sel, '::' );
		if( $pos !== false )
			$sel = substr( $sel, 0, $pos );

		if( preg_match( '@[^\\s:](:(?:before|after))$@', $sel, $m ) )
			$sel = substr( $sel, 0, -strlen( $m[ 1 ] ) );

		$xpathQ = null; try { $xpathQ = $cnvCssSel2Xpath -> cssToXPath( $sel, 'descendant-or-self::' ); } catch( \Exception $e ) {}
		return( $xpathQ );
	}

	static function createCnvCssSel2Xpath()
	{
		$cnvCssSel2Xpath = new Symfony\Component\CssSelector\XPath\Translator();
		$cnvCssSel2Xpath -> registerExtension( new Symfony\Component\CssSelector\XPath\Extension\HtmlExtension( $cnvCssSel2Xpath ) );
		$cnvCssSel2Xpath -> registerParserShortcut( new Symfony\Component\CssSelector\Parser\Shortcut\EmptyStringParser() );
		$cnvCssSel2Xpath -> registerParserShortcut( new Symfony\Component\CssSelector\Parser\Shortcut\ElementParser() );
		$cnvCssSel2Xpath -> registerParserShortcut( new Symfony\Component\CssSelector\Parser\Shortcut\ClassParser() );
		$cnvCssSel2Xpath -> registerParserShortcut( new Symfony\Component\CssSelector\Parser\Shortcut\HashParser() );
		return( $cnvCssSel2Xpath );
	}

	function cssSelToXPath( string $sel )
	{
		return( StyleProcessor::cssSelToXPathEx( $this -> cnvCssSel2Xpath, $sel ) );
	}

	function xpathEvaluate( $query )
	{
		return( $this -> xpath -> evaluate( $query, $this -> rootElem ) );
	}

	function isCssSelCrit( $ctxProcess, $sel )
	{

		if( isset( $this -> _aCssSelIsCritCache[ $sel ] ) )
		{

			return( $this -> _aCssSelIsCritCache[ $sel ] );
		}

		$selFiltered = trim( ContSkeleton_FltName( $this -> sklCssSelExcls, $sel, true, true ) );

		$selector = $this -> cssSelFs -> parseSelector( $selFiltered );

		if( !$selector )
			$isCrit = true;
		else
		{
			$selDeparsed = $selector -> renderWhole( $this -> cssFmtSimple );
			if( isset( $this -> _aCssSelIsCritRtCache[ $selDeparsed ] ) )
				$isCrit = $this -> _aCssSelIsCritRtCache[ $selDeparsed ];
			else
			{

				$mr = $selector -> match( $this -> cssSelFs, $this -> docFs );

				$isCrit = $mr === false || !!$mr;
				$this -> _aCssSelIsCritRtCache[ $selDeparsed ] = $isCrit;
			}
		}

		$this -> _aCssSelIsCritCache[ $sel ] = $isCrit;

		return( $isCrit );

		$items = false;

		$xpathQ = $this -> cssSelToXPath( $selFiltered );

		if( $xpathQ )
		{
			$xpathQ = '(' . $xpathQ . ')[1]';

			$items = $this -> xpathSkeleton ? $this -> xpathSkeleton -> evaluate( $xpathQ ) : $this -> xpathEvaluate( $xpathQ );

		}

		return( $this -> _aCssSelIsCritCache[ $sel ] = ( $items === false || HtmlNd::FirstOfChildren( $items ) ) );

	}

	static private function _getFullBlockSel( $selectors )
	{
		$aSel = array();
		foreach( $selectors as $sel )
			$aSel[] = $sel[ 0 ];

		return( count( $aSel ) > 1 ? ':is(' . implode( ',', $aSel ) . ')' : $aSel[ 0 ] );
	}

	static private function _getFullSel( $sel, $selParent = '' )
	{
		if( $sel == '&' )
			return( $selParent );

		$bComb = strpos( '>+~', substr( $sel, 0, 1 ) ) !== false;

		$sel = ' ' . $sel;
		if( $bComb || !@preg_match( '@[^\\\\]&@', $sel ) )
			$sel = ' &' . $sel;

		$sel = preg_replace( '@([^\\\\])&@', '${1}' . $selParent, ' ' . $sel );
		return( trim( $sel ) );
	}

	static function keepLrnNeededData( &$datasDel, &$lrnsGlobDel, $dsc, $dataPath )
	{
		if( $id = Gen::GetArrField( $dsc, array( 'css', 'c' ) ) )
		{
			unset( $lrnsGlobDel[ 'css/c/' . $id . '.dat.gz' ] );

			$data = Tof_GetFileData( $dataPath . '/css/c', 'dat.gz', array( 2, function( $data, $vFrom ) { return( $data ); } ), true, $id );
			$v = Gen::GetArrField( $data, array( 'v' ), 1 );

			foreach( Gen::GetArrField( $data, array( 'ac' ), array() ) as $contHash => $contParts )
			{
				if( is_array( $contParts ) )
				{
					foreach( $contParts as $partId => $oiCi )
						if( is_string( $oiCi ) && strlen( $oiCi ) )
							unset( $datasDel[ 'css' ][ $oiCi ] );
				}
				else if( is_string( $contParts ) && strlen( $contParts ) )
					unset( $datasDel[ 'css' ][ $contParts ] );
			}

			if( $v < 2 )
			{

				unset( $datasDel[ 'img' ] );
			}
			else
			{
				foreach( Gen::GetArrField( $data, array( 'd' ), array() ) as $type => $aoiCi )
					foreach( $aoiCi as $oiCi )
						unset( $datasDel[ $type ][ $oiCi ] );
			}
		}

		if( $id = Gen::GetArrField( $dsc, array( 'css', 'xslb' ) ) )
		{
			unset( $lrnsGlobDel[ 'css/xslb/' . $id . '.dat.gz' ] );
		}
	}

	function readLrnData( &$ctxProcess, $dsc, $dataPath )
	{
		if( $id = Gen::GetArrField( $dsc, array( 'css', 'c' ) ) )
		{
			$data = Tof_GetFileData( $dataPath . '/css/c', 'dat.gz', array( 2, function( $data, $vFrom ) { return( $data ); } ), true, $id );

			$this -> _aAdjustContCache = Gen::GetArrField( $data, array( 'ac' ), array() );
		}

		if( $id = Gen::GetArrField( $dsc, array( 'css', 'xslb' ) ) )
		{
			$data = Tof_GetFileData( $dataPath . '/css/xslb', 'dat.gz', 1, true, $id );

			$this -> _aXpathSelCache = Gen::GetArrField( $data, array( 'd' ), array() );
		}
	}

	function readLrnDataFinish( &$ctxProcess, $dsc, $dataPath )
	{
		if( $id = Gen::GetArrField( $dsc, array( 'css', 'c' ) ) )
		{
			$data = Tof_GetFileData( $dataPath . '/css/c', 'dat.gz', array( 2, function( $data, $vFrom ) { return( $data ); } ), true, $id );

			DepsRemove( $ctxProcess[ 'deps' ], DepsExpand( Gen::GetArrField( $data, array( 'd' ), array() ) ) );
		}
	}

	function writeLrnData( &$ctxProcess, &$dsc, $dataPath )
	{
		if( $this -> _aAdjustContCache )
		{
			$data = array();

			$aDeps = DepsDiff( $this -> _aDepBegin, $ctxProcess[ 'deps' ] );
			unset( $aDeps[ 'css' ] );

			if( $this -> _aAdjustContCache )
			{
				$data[ 'ac' ] = $this -> _aAdjustContCache;

				foreach( $this -> _aAdjustContCache as $contHash => $contParts )
				{
					if( is_array( $contParts ) )
					{
						foreach( $contParts as $partId => $oiCi )
							if( is_string( $oiCi ) && strlen( $oiCi ) )
								DepsAdd( $aDeps, 'css', $oiCi );
					}
					else if( is_string( $contParts ) && strlen( $contParts ) )
						DepsAdd( $aDeps, 'css', $contParts );
				}
			}

			DepsRemove( $ctxProcess[ 'deps' ], $aDeps );
			unset( $aDeps[ 'css' ] );

			if( $aDeps )
				$data[ 'd' ] = DepsExpand( $aDeps, false );

			$dsc[ 'css' ][ 'c' ] = '';
			if( Gen::HrFail( @Tof_SetFileData( $dataPath . '/css/c', 'dat.gz', $data, 2, false, TOF_COMPR_MAX, $dsc[ 'css' ][ 'c' ] ) ) )
				return( false );
		}

		if( $this -> _aXpathSelCache )
		{
			$dsc[ 'css' ][ 'xslb' ] = '';
			if( Gen::HrFail( @Tof_SetFileData( $dataPath . '/css/xslb', 'dat.gz', array( 'd' => $this -> _aXpathSelCache ), 1, false, TOF_COMPR_MAX, $dsc[ 'css' ][ 'xslb' ] ) ) )
				return( false );
		}

		return( true );
	}

	private static function _GetCssRuleValUrlObjs( $v, &$urls )
	{
		if( $v instanceof Sabberworm\CSS\Value\URL )
			$urls[] = $v;
		else if( $v instanceof Sabberworm\CSS\Value\RuleValueList )
			foreach( $v -> getListComponents() as $vComp )
				self::_GetCssRuleValUrlObjs( $vComp, $urls );
	}

	private static function _GetCssRuleValFontNames( $v, &$names )
	{
		if( gettype( $v ) === 'string' )
		{
			if( !in_array( $v, array( 'normal', 'inherit', 'italic', 'oblique', 'small-caps', 'bold', 'bolder', 'lighter' ) ) )
				$names[ $v ] = true;
		}
		else if( $v instanceof Sabberworm\CSS\Value\CSSString )
			$names[ $v -> getString() ] = true;
		else if( $v instanceof Sabberworm\CSS\Value\RuleValueList )
			foreach( $v -> getListComponents() as $vComp )
				self::_GetCssRuleValFontNames( $vComp, $names );
	}

	private static function _DoesCSSRuleValContainFileURL( $v )
	{
		if( $v instanceof Sabberworm\CSS\Value\URL )
			return( !Ui::IsSrcAttrData( trim( $v -> getURL() -> getString() ) ) );

		if( !( $v instanceof Sabberworm\CSS\Value\RuleValueList ) )
			return( false );

		foreach( $v -> getListComponents() as $vComp )
			if( self::_DoesCSSRuleValContainFileURL( $vComp ) )
				return( true );

		return( false );
	}

	private static function _RuleMinify( $rule )
	{
		$aShorters = array(
			'font-weight'		=> array( 'normal' => 400, 'bold' => 700, ),
			'background'		=> array( 'transparent' => '0 0', 'none' => '0 0', 'black' => '#000', 'white' => '#fff', 'fuchsia' => '#f0f', 'magenta' => '#f0f', 'yellow' => '#ff0' ),

			'margin'			=> __CLASS__ . '::_RuleMinifySizes',
			'padding'			=> __CLASS__ . '::_RuleMinifySizes',
			'border-width'		=> __CLASS__ . '::_RuleMinifySizes',

			'left'				=> __CLASS__ . '::_RuleMinifySizes',
			'top'				=> __CLASS__ . '::_RuleMinifySizes',
			'right'				=> __CLASS__ . '::_RuleMinifySizes',
			'bottom'			=> __CLASS__ . '::_RuleMinifySizes',

			'margin-left'		=> __CLASS__ . '::_RuleMinifySizes',
			'margin-top'		=> __CLASS__ . '::_RuleMinifySizes',
			'margin-right'		=> __CLASS__ . '::_RuleMinifySizes',
			'margin-bottom'		=> __CLASS__ . '::_RuleMinifySizes',

			'padding-left'		=> __CLASS__ . '::_RuleMinifySizes',
			'padding-top'		=> __CLASS__ . '::_RuleMinifySizes',
			'padding-right'		=> __CLASS__ . '::_RuleMinifySizes',
			'padding-bottom'	=> __CLASS__ . '::_RuleMinifySizes',
		);

		$shorter = (isset($aShorters[ $rule -> getRule() ])?$aShorters[ $rule -> getRule() ]:null);
		if( !$shorter )
			return;

		if( is_array( $shorter ) )
		{
			$val = $rule -> getValue();
			if( !is_object( $val ) )
			{
				$valShort = (isset($shorter[ $val ])?$shorter[ $val ]:null);
				if( $valShort !== null )
					$rule -> setValue( $valShort );
			}
		}
		else
			@call_user_func( $shorter, $rule );
	}

	static function _SizeMin( $v )
	{
		if( $v instanceof Sabberworm\CSS\Value\Size && !$v -> getSize() )
			$v -> setUnit( null );
		return( $v );
	}

	static function _RuleMinifySizes( $rule )
	{

		$v = $rule -> getValue();
		if( $v instanceof Sabberworm\CSS\Value\RuleValueList )
		{
			$comps = $v -> getListComponents();
			foreach( $comps as &$vComp )
				$vComp = self::_SizeMin( $vComp );

			if( count( $comps ) == 4 && ( string )$comps[ 1 ] === ( string )$comps[ 3 ] )
				array_pop( $comps );
			if( count( $comps ) == 3 && ( string )$comps[ 0 ] === ( string )$comps[ 2 ] )
				array_pop( $comps );
			if( count( $comps ) == 2 && ( string )$comps[ 0 ] === ( string )$comps[ 1 ] )
				array_pop( $comps );

			$v -> setListComponents( $comps );
		}
		else
			$v = self::_SizeMin( $v );

		$rule -> setValue( $v );
	}

	private function _SelectorMinify( $sel )
	{
		$selWrongSuffix = '';

		{
			$posWrongSel = strpos( $sel, '{' );
			if( $posWrongSel !== false )
			{
				$selWrongSuffix = substr( $sel, $posWrongSel );
				$sel = substr( $sel, 0, $posWrongSel );
			}
		}

		if( $selNew = $this -> minifier -> run( $sel ) )
			$sel = $selNew;

		return( $sel . $selWrongSuffix );
	}
}

