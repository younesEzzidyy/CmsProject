<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

function GetYouTubeVideoAttrs( $id )
{
	$res = array( 'width' => 16, 'height' => 9 );

	$data = GetExtContents( 'https://www.youtube.com/watch?v=' . $id, $contMimeType, false );
	if( !$data )
		return( false );

	$metas = GetContentsMetaProps( $data );

	$w = (isset($metas[ 'og:video:width' ])?$metas[ 'og:video:width' ]:null);
	$h = (isset($metas[ 'og:video:height' ])?$metas[ 'og:video:height' ]:null);
	if( $w && $h )
	{
		$res[ 'width' ] = $w;
		$res[ 'height' ] = $h;
	}

	$res[ 'title' ] = (isset($metas[ 'title' ])?$metas[ 'title' ]:null);

	return( $res );
}

function GetYouTubeVideoThumbUrl( $ctxProcess, $id, $args = null )
{
	if( $id == 'videoseries' || $id == 'live_stream' )
	{
		$data = GetExtContents( Net::UrlAddArgs( 'https://www.youtube.com/embed/' . $id, $args ), $contMimeType, false );
		if( !$data )
			return( '' );

		$data = GetContentsRawHead( $data );
		if( !$data )
			return( '' );

		$id = null;
		if( @preg_match( '@<link\\srel=["\']canonical["\']\\shref=["\']([^"\']*)@', $data, $m ) )
			$id = GetVideoThumbIdFromUrl( $ctxProcess, $m[ 1 ] );
		else if( @preg_match( '@\\Wytcfg\\s*.\\s*set\\s*\\(\\s*{@', $data, $m, PREG_OFFSET_CAPTURE ) )
		{
			$posStart = $m[ 0 ][ 1 ] + strlen( $m[ 0 ][ 0 ] ) - 1;
			$pos = Gen::JsonGetEndPos( $posStart, $data );
			$m = @json_decode( Gen::JsObjDecl2Json( substr( $data, $posStart, $pos - $posStart ) ), true );
			$m = @json_decode( Gen::GetArrField( $m, array( 'PLAYER_VARS', 'embedded_player_response' ), '' ), true );
			$m = Gen::GetArrField( $m, array( 'previewPlayabilityStatus', 'errorScreen', 'playerErrorMessageRenderer', 'proceedButton', 'buttonRenderer', 'navigationEndpoint', 'urlEndpoint', 'url' ), '' );
			if( $m )
				return( GetVideoThumbUrlFromUrl( $ctxProcess, $m ) );
		}
	}

	if( !$id )
		return( '' );

	$res = 'https://i.ytimg.com/vi/' . $id . '/sddefault.jpg';

	$data = GetExtContents( 'https://www.youtube.com/watch?v=' . $id, $contMimeType, false );
	if( $data )
	{
		$metas = GetContentsMetaProps( $data );
		if( (isset($metas[ 'og:image' ])?$metas[ 'og:image' ]:null) )
			$res = $metas[ 'og:image' ];
		else if( @preg_match( '@<div\\s+id=[\'"]?player(?:&#45;|-)placeholder[\'"]?\\s+style=[\'"]background-image:\\s*url\\([\'"]([^\';]+)[\'"]@', $data, $m ) )
		{

			$res = $m[ 1 ];
		}
	}

	return( $res );
}

function GetYouTubeVideoCtlContent()
{
	return( Ui::Tag( 'span', '<svg height="100%" version="1.1" viewBox="0 0 68 48" width="100%"><path class="ytp-large-play-button-bg" d="M66.52,7.74c-0.78-2.93-2.49-5.41-5.42-6.19C55.79,.13,34,0,34,0S12.21,.13,6.9,1.55 C3.97,2.33,2.27,4.81,1.48,7.74C0.06,13.05,0,24,0,24s0.06,10.95,1.48,16.26c0.78,2.93,2.49,5.41,5.42,6.19 C12.21,47.87,34,48,34,48s21.79-0.13,27.1-1.55c2.93-0.78,4.64-3.26,5.42-6.19C67.94,34.95,68,24,68,24S67.94,13.05,66.52,7.74z" fill="#f00"></path><path d="M 45,24 27,14 27,34" fill="#fff"></path></svg>', array( 'style' => array( 'position' => 'absolute', 'left' => '50%', 'top' => '50%', 'width' => '68px', 'height' => '48px', 'margin-left' => '-34px', 'margin-top' => '-24px', 'pointer-events' => 'none' ) ) ) );
}

function ApplyYouTubeVideoPlaceholder( $item, &$src, $lazyVideoCurId, $urlThumb, $sz = null )
{
	$data = '<!DOCTYPE html>' . Ui::Tag( 'html', Ui::Tag( 'body',
			Ui::Tag( 'a', null, array( 'href' => '#', 'onclick' => 'window.parent.postMessage(\'seraph-accel-lzl-v:' . $lazyVideoCurId . '\',\'*\');', 'style' => array( 'position' => 'absolute', 'width' => '100%', 'height' => '100%', 'background' => 'center / cover no-repeat url(' . $urlThumb . ')' ) ) ) .
			GetYouTubeVideoCtlContent()
			, array( 'style' => array( 'margin' => 0 ) )
		) );

	$item -> setAttribute( 'lzl-v', '' );
	$item -> setAttribute( 'data-id', $lazyVideoCurId );
	$item -> setAttribute( 'data-lzl-v-src', add_query_arg( array( 'autoplay' => 1, 'enablejsapi' => 1 ), $src ) );
	$item -> setAttribute( 'data-lzl-v-svc', 'youtube' );
	$item -> setAttribute( 'allow', 'autoplay' );
	$item -> setAttribute( 'src', $src = 'data:text/html,' . rawurlencode( $data ) );
}

function ApplyVimeoVideoPlaceholder( $item, &$src, $lazyVideoCurId, $urlThumb, $sz = null )
{
	$thumbStyles = $urlThumb ?
		array( 'background' => 'center / cover no-repeat url(\'' . ( $sz ? add_query_arg( array( 'mw' => $sz[ 0 ], 'mh' => $sz[ 1 ] ), $urlThumb ) : $urlThumb ) . '\')' ) :
		array( 'background-color' => '#000' );

	$data = '<!DOCTYPE html>' . Ui::Tag( 'html', Ui::Tag( 'body',
			Ui::Tag( 'a', null, array( 'href' => '#', 'onclick' => 'window.parent.postMessage(\'seraph-accel-lzl-v:' . $lazyVideoCurId . '\',\'*\');', 'style' => array_merge( array( 'position' => 'absolute', 'width' => '100%', 'height' => '100%' ), $thumbStyles ) ) ) .
			Ui::Tag( 'span', '<svg height="100%" version="1.1" viewBox="0 0 66 40" width="100%"><path d="M 45,21 27,11 27,31" fill="#fff"></path></svg>', array( 'style' => array( 'background' => 'rgb(0,173,239)', 'border-radius' => '5px;', 'position' => 'absolute', 'left' => '50%', 'top' => '50%', 'width' => '66px', 'height' => '40px', 'margin-left' => '-33px', 'margin-top' => '-20px', 'pointer-events' => 'none' ) ) )
			, array( 'style' => array( 'margin' => 0 ) )
		) );

	$item -> setAttribute( 'lzl-v', '' );
	$item -> setAttribute( 'data-id', $lazyVideoCurId );
	$item -> setAttribute( 'data-lzl-v-src', add_query_arg( array( 'autoplay' => 1 ), $src ) );
	$item -> setAttribute( 'allow', 'autoplay' );
	$item -> setAttribute( 'src', $src = 'data:text/html,' . rawurlencode( $data ) );
}

function GetVimeoVideoThumbUrl( $id )
{

	if( !$id )
		return( null );

	if( $data = GetExtContents( 'https://player.vimeo.com/video/' . $id, $contMimeType ) )
	{

		$nPos = strpos( $data, '"base":"https://i.vimeocdn.com/video/' );
		if( $nPos !== false )
		{
			$nPos += 37;

			$nPosEnd = strpos( $data, '"', $nPos );
			if( $nPosEnd === false )
				return( null );

			return( 'https://i.vimeocdn.com/video/' . substr( $data, $nPos, $nPosEnd - $nPos ) . '.jpg' );
		}
	}

	if( $data = GetExtContents( 'https://vimeo.com/' . $id, $contMimeType ) )
	{

		$metas = GetContentsMetaProps( $data );
		if( $urlComps = Net::UrlParse( (isset($metas[ 'og:image' ])?$metas[ 'og:image' ]:null), Net::URLPARSE_F_QUERY ) )
		{
			$url = Gen::GetArrField( $urlComps, array( 'query', 'src0' ) );
			if( $url )
				return( $url );

			if( Gen::GetArrField( $urlComps, array( 'host' ) ) == 'i.vimeocdn.com' )
			{
				unset( $urlComps[ 'query' ][ 'f' ] );
				return( Net::UrlDeParse( $urlComps ) );
			}
		}
	}

	return( 'https://vumbnail.com/' . $id . '.jpg' );
}

function _GetVideoThumbIdFromUrl_Cleanup( $id )
{
	$n = strpos( $id, '&' );
	return( $n === false ? $id : substr( $id, 0, $n ) );
}

function GetVideoThumbIdFromUrl( $ctxProcess, $url, &$svc = null )
{
	$srcInfo = GetSrcAttrInfo( $ctxProcess, null, null, $url );

	if( ( $nPos = stripos( $srcInfo[ 'srcWoArgs' ], '.youtube.com/embed/' ) ) !== false )
	{
		$svc = 'youtube';
		return( _GetVideoThumbIdFromUrl_Cleanup( ( substr( $srcInfo[ 'srcWoArgs' ], $nPos + 19 ) ) ) );
	}
	if( ( $nPos = stripos( $srcInfo[ 'srcWoArgs' ], '.youtube-nocookie.com/embed/' ) ) !== false )
	{
		$svc = 'youtube';
		return( _GetVideoThumbIdFromUrl_Cleanup( substr( $srcInfo[ 'srcWoArgs' ], $nPos + 28 ) ) );
	}
	if( ( $nPos = stripos( $srcInfo[ 'srcWoArgs' ], '/youtu.be/' ) ) !== false )
	{
		$svc = 'youtube';
		return( _GetVideoThumbIdFromUrl_Cleanup( substr( $srcInfo[ 'srcWoArgs' ], $nPos + 10 ) ) );
	}
	if( ( $nPos = stripos( $srcInfo[ 'srcWoArgs' ], 'youtube.com/watch' ) ) !== false )
	{
		$svc = 'youtube';
		return( _GetVideoThumbIdFromUrl_Cleanup( (isset($srcInfo[ 'args' ][ 'v' ])?$srcInfo[ 'args' ][ 'v' ]:null) ) );
	}
	if( ( $nPos = stripos( $srcInfo[ 'srcWoArgs' ], 'youtube-nocookie.com/watch' ) ) !== false )
	{
		$svc = 'youtube';
		return( _GetVideoThumbIdFromUrl_Cleanup( (isset($srcInfo[ 'args' ][ 'v' ])?$srcInfo[ 'args' ][ 'v' ]:null) ) );
	}

	if( ( $nPos = stripos( $srcInfo[ 'srcWoArgs' ], 'player.vimeo.com/video/' ) ) !== false )
	{
		$svc = 'vimeo';
		return( _GetVideoThumbIdFromUrl_Cleanup( substr( $srcInfo[ 'srcWoArgs' ], $nPos + 23 ) ) );
	}
	if( ( $nPos = stripos( $srcInfo[ 'srcWoArgs' ], '/vimeo.com/' ) ) !== false )
	{
		$svc = 'vimeo';
		return( _GetVideoThumbIdFromUrl_Cleanup( substr( $srcInfo[ 'srcWoArgs' ], $nPos + 11 ) ) );
	}

	return( null );
}

function GetVideoThumbUrlFromUrl( $ctxProcess, $url, &$id = null )
{
	$id = GetVideoThumbIdFromUrl( $ctxProcess, $url, $svc );

	switch( $svc )
	{
	case 'youtube':			return( GetYouTubeVideoThumbUrl( $ctxProcess, $id ) );
	case 'vimeo':			return( GetVimeoVideoThumbUrl( $id ) );
	}

	return( null );
}

function Frames_AdjustThumbUrl( $urlThumb, &$ctxProcess, $settCache, $settImg, $settCdn )
{
	$imgSrc = new ImgSrc( $urlThumb );
	$r = Images_ProcessSrc( $ctxProcess, $imgSrc, $settCache, $settImg, $settCdn );
	if( $r === false )
		return( false );
	if( !$r )
		return( $urlThumb );

	if( ulyjqbuhdyqcetbhkiy( $imgSrc -> src ) )
		$urlThumb = $ctxProcess[ 'siteDomainUrl' ] . $imgSrc -> src;
	else if( (isset($imgSrc -> src[ 0 ])?$imgSrc -> src[ 0 ]:null) == '?' )
		$urlThumb = $ctxProcess[ 'siteDomainUrl' ] . $ctxProcess[ 'siteRootUri' ] . $imgSrc -> src;
	else
		$urlThumb = $imgSrc -> src;
	return( $urlThumb );
}

function Frames_Process( &$ctxProcess, $doc, $settCache, $settFrm, $settImg, $settCdn, $settJs )
{
	if( !( Gen::GetArrField( $settFrm, array( 'lazy', 'enable' ), false ) ) )
	    return( true );

	$yt = Gen::GetArrField( $settFrm, array( 'lazy', 'yt' ), false );
	$vm = Gen::GetArrField( $settFrm, array( 'lazy', 'vm' ), false );

	$body = $ctxProcess[ 'ndBody' ];

	$isImgLazy = Gen::GetArrField( $settImg, array( 'lazy', 'load' ), false );

	$lazyVideoCurId = 0;

	foreach( HtmlNd::ChildrenAsArr( $doc -> getElementsByTagName( 'iframe' ) ) as $item )
	{
		if( ContentProcess_IsAborted( $settCache ) ) return( true );

		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		if( HtmlNd::FindUpByTag( $item, 'noscript' ) )
			continue;

		if( Scripts_IsElemAs( $ctxProcess, $doc, $settJs, $item ) )
			continue;

		$src = $item -> getAttribute( 'src' );
		if( !$src || $src == 'about:blank' )
			continue;

		ContentMarkSeparate( $item, false );

		if( (isset($ctxProcess[ 'compatView' ])?$ctxProcess[ 'compatView' ]:null) )
			continue;

		$srcInfo = GetSrcAttrInfo( $ctxProcess, null, null, $src );
		Fullness_AdjustUrl( $ctxProcess, $src, (isset($srcInfo[ 'srcUrlFullness' ])?$srcInfo[ 'srcUrlFullness' ]:null) );
		$item -> setAttribute( 'src', $src );

		$sz = array( $item -> getAttribute( 'width' ), $item -> getAttribute( 'height' ) );
		if( !is_numeric( substr( $sz[ 0 ], -1 ) ) || !is_numeric( substr( $sz[ 1 ], -1 ) ) )
			$sz = null;

		$isVideo = false;
		$id = GetVideoThumbIdFromUrl( $ctxProcess, $srcInfo[ 'url' ], $svc );
		if( $svc == 'youtube' && $yt )
		{
			if( !$id )
				continue;

			$isVideo = true;
			$lazyVideoCurId++;

			$urlThumb = Frames_AdjustThumbUrl( GetYouTubeVideoThumbUrl( $ctxProcess, $id, $srcInfo[ 'args' ] ), $ctxProcess, $settCache, $settImg, $settCdn );
			if( $urlThumb === false )
				return( false );

			ApplyYouTubeVideoPlaceholder( $item, $src, $lazyVideoCurId, $urlThumb, $sz );

		}
		else if( $svc == 'vimeo' && $vm )
		{
			if( !$id )
				continue;

			$isVideo = true;
			$lazyVideoCurId++;

			$urlThumb = Frames_AdjustThumbUrl( GetVimeoVideoThumbUrl( $id ), $ctxProcess, $settCache, $settImg, $settCdn );
			if( $urlThumb === false )
				return( false );

			ApplyVimeoVideoPlaceholder( $item, $src, $lazyVideoCurId, $urlThumb, $sz );
		}

		if( $isImgLazy && !Images_CheckLazyExcl( $ctxProcess, $doc, $settImg, $item ) )
		{
			$ctxProcess[ 'lazyload' ] = true;
			HtmlNd::AddRemoveAttrClass( $item, 'lzl' );
			$item -> setAttribute( 'data-lzl-src', $src );
			$item -> setAttribute( 'src', LazyLoad_SrcSubst( $ctxProcess, array( 'cx' => $sz ? $sz[ 0 ] : null, 'cy' => $sz ? $sz[ 1 ] : null ), true ) );
		}

		if( $isVideo )
		{
			$item -> setAttribute( 'allowtransparency', 'true' );
			$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ) ) ) );
		}
	}

	$xpath = null;

	if( Gen::GetArrField( $settFrm, array( 'lazy', 'elmntrBg' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-element ")][@data-settings]', $ctxProcess[ 'ndHtml' ] ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			$dataSett = @json_decode( $item -> getAttribute( 'data-settings' ), true );
			if( Gen::GetArrField( $dataSett, array( 'background_background' ) ) == 'video' && ( $urlVideo = Gen::GetArrField( $dataSett, array( 'background_video_link' ) ) ) )
			{
				$container = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(@class,"elementor-background-video-container")][1]', $item ) );
				if( !$container )
					continue;

				if( $urlVideoThumb = GetVideoThumbUrlFromUrl( $ctxProcess, $urlVideo ) )
				{
					$container -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $container -> getAttribute( 'style' ) ), array( 'background' => 'center / cover no-repeat url(' . $urlVideoThumb . ')!important' ) ) ) );

					if( $isImgLazy && !Images_CheckLazyExcl( $ctxProcess, $doc, $settImg, $container ) )
					{
						$ctxProcess[ 'lazyload' ] = true;
						HtmlNd::AddRemoveAttrClass( $container, array( 'lzl' ) );
					}
				}
				else if( $itemVideo = HtmlNd::FirstOfChildren( $xpath -> query( './video[1]', $container ) ) )
				{
					$tmStart = ( int )Gen::GetArrField( $dataSett, array( 'background_video_start' ) );
					$tmEnd = ( int )Gen::GetArrField( $dataSett, array( 'background_video_end' ) );
					if( $tmStart || $tmEnd )
					{
						$urlComps = ( array )Net::UrlParse( $urlVideo );
						$urlComps[ 'fragment' ] = 't=' . $tmStart . ( $tmEnd ? ( ',' . $tmEnd ) : '' );
						$urlVideo = Net::UrlDeParse( $urlComps );
						unset( $urlComps );
					}

					$itemVideo -> setAttribute( 'src', $urlVideo );
					$itemVideo -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemVideo -> getAttribute( 'style' ) ), array( 'height' => '100%' ) ) ) );

					Gen::SetArrField( $dataSett, array( 'background_video_link' ), 'https://player.vimeo.com/video/DUMMY' );
					$item -> setAttribute( 'data-settings', @json_encode( $dataSett ) );
				}
			}
		}
	}

	$bYouTubeFeedPlay = false;
	if( Gen::GetArrField( $settFrm, array( 'lazy', 'youTubeFeed' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/a[contains(concat(" ",normalize-space(@class)," ")," sby_video_thumbnail ")]', $ctxProcess[ 'ndHtml' ] ) as $item )
		{
			$id = $item -> getAttribute( 'data-video-id' );
			if( !$id )
				continue;

			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			$urlVideoThumbnail = $item -> getAttribute( 'data-full-res' );

			if( !$urlVideoThumbnail )
				continue;

			ContentMarkSeparate( $item -> parentNode, false );
			if( (isset($ctxProcess[ 'compatView' ])?$ctxProcess[ 'compatView' ]:null) )
				continue;

			$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( 'background-image' => null, 'background' => 'center / cover no-repeat url(' . $urlVideoThumbnail . ')' ) ) ) );

			if( !HtmlNd::FirstOfChildren( $xpath -> query( './*[contains(concat(" ",normalize-space(@class)," ")," sby_play_btn ")]', $item ) ) )
			{
				$itemCtl = HtmlNd::Parse( GetYouTubeVideoCtlContent() );
				if( $itemCtl && $itemCtl -> firstChild )
					if( $itemCtl = $doc -> importNode( $itemCtl -> firstChild, true ) )
					{
						$item -> appendChild( $itemCtl );
						$item -> setAttribute( 'href', '#' );
						$item -> removeAttribute( 'target' );
						$item -> setAttribute( 'onclick', 'seraph_accel_youTubeFeedPlayVideo(this);return false' );
						$item -> setAttribute( 'data-lzl-clk-no', '1' );
						$bYouTubeFeedPlay = true;
					}
			}

			if( $itemPlayerContainer = HtmlNd::FirstOfChildren( $xpath -> query( './*[contains(concat(" ",normalize-space(@class)," ")," sby_player_wrap ")]', $item -> parentNode ) ) )
			{

			}
		}
	}

	if( $ctxProcess[ 'mode' ] == 'full' && ( $lazyVideoCurId || $bYouTubeFeedPlay ) )
	{

		$item = $doc -> createElement( 'script' );
		if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
			$item -> setAttribute( 'type', 'text/javascript' );
		$item -> setAttribute( 'seraph-accel-crit', '1' );
		HtmlNd::SetValFromContent( $item, "(function(d,f){function g(a,b){(function(c){if(d.YT)c();else{var e=f.createElement(\"script\");e.type=\"text/javascript\";e.src=\"https://www.youtube.com/iframe_api\";e.onload=c;f.head.appendChild(e)}})(function(){d.YT.ready(function(){new d.YT.Player(a,{events:{onReady:function(c){c.target.playVideo()}}});b&&\"string\"==typeof a.src&&(a.src=a.src.replace(\"autoplay=0\",\"autoplay=1\"))})})}d.addEventListener(\"message\",function(a){if(\"string\"==typeof a.data){a=a.data.split(\":\");var b=a[1];a=a[0];if(\"seraph-accel-lzl-v\"==\na&&(a=f.querySelectorAll('iframe[lzl-v][data-id=\"'+b+'\"]'),a.length)){b=0;if(1<a.length){for(;b<a.length;b++){var c=a[b];if(c.offsetWidth||c.offsetHeight||c.getClientRects().length)break}if(b==a.length)return}a=a[b];a.src=a.getAttribute(\"data-lzl-v-src\");\"youtube\"==a.getAttribute(\"data-lzl-v-svc\")&&g(a)}}},!1);d.seraph_accel_youTubeFeedPlayVideo=function(a){a.setAttribute(\"onclick\",\"return false\")}})(window,document)" );
		$body -> appendChild( $item );

		ContentMarkSeparate( $item, false );
	}

	return( true );
}

function ContParts_Process( &$ctxProcess, $doc, $settCache, $settCp, $settImg, $settCdn, $jsNotCritsDelayTimeout )
{

	$xpath = null;

	if( !$jsNotCritsDelayTimeout || (isset($ctxProcess[ 'compatView' ])?$ctxProcess[ 'compatView' ]:null) || $ctxProcess[ 'isAMP' ] )
	    return( true );

	$ctx = new AnyObj();
	$ctx -> aAniAppear = array();
	$ctx -> bBjs = false;
	$ctx -> cfgElmntrFrontend = null;
	$ctx -> cnvCssSel2Xpath = null;
	$cfgUiKit = null;

	if( Gen::GetArrField( $settCp, array( 'wooJs' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		if( $item = HtmlNd::FirstOfChildren( $xpath -> query( './/body[contains(concat(" ",normalize-space(@class)," ")," woocommerce-no-js ")]' ) ) )
			HtmlNd::AddRemoveAttrClass( $item, array( 'woocommerce-js' ), array( 'woocommerce-no-js' ) );
	}

	if( Gen::GetArrField( $settCp, array( 'sldBdt' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/*[@data-bdt-slideshow]|.//*[@bdt-slideshow]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			$dataSett = $item -> getAttribute( 'data-bdt-slideshow' );
			if( !$dataSett )
				$dataSett = $item -> getAttribute( 'bdt-slideshow' );

			$dataSett = @json_decode( $dataSett, true );
			$minHeight = Gen::GetArrField( $dataSett, array( 'min-height' ) );
			HtmlNd::AddRemoveAttrClass( $item, array( 'bdt-slideshow' ) );

			if( $itemSlides = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," bdt-slideshow-items ")][1]', $item ) ) )
			{
				if( $minHeight )
					$itemSlides -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemSlides -> getAttribute( 'style' ) ), array( 'min-height' => '' . $minHeight . 'px' ) ) ) );

				if( $itemFirstSlide = HtmlNd::FirstOfChildren( $xpath -> query( './*[contains(concat(" ",normalize-space(@class)," ")," bdt-slideshow-item ")][1]', $itemSlides ) ) )
				{
					HtmlNd::AddRemoveAttrClass( $itemFirstSlide, array( 'bdt-active' ) );
					if( $itemSlideCoverBgCont = HtmlNd::FirstOfChildren( $xpath -> query( './*[contains(concat(" ",normalize-space(@class)," ")," bdt-position-cover ")][1]', $itemFirstSlide ) ) )
					{
						if( $itemSlideCoverBg = HtmlNd::FirstOfChildren( $xpath -> query( './img[@bdt-cover][1]', $itemSlideCoverBgCont ) ) )
						{
							$itemSlideCoverBgCont -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemSlideCoverBg -> getAttribute( 'style' ) ), array( 'background' => 'center / cover no-repeat url(' . $itemSlideCoverBg -> getAttribute( 'src' ) . ')' ) ) ) );
							$itemSlideCoverBg -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemSlideCoverBg -> getAttribute( 'style' ) ), array( 'visibility' => 'hidden' ) ) ) );
						}
					}
				}
			}
		}
	}

	if( Gen::GetArrField( $settCp, array( 'swBdt' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," bdt-switcher-item-content-inner ")][not(preceding-sibling::*)]' ) as $item )
			HtmlNd::AddRemoveAttrClass( $item, array( 'bdt-active' ) );
	}

	if( Gen::GetArrField( $settCp, array( 'vidJs' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		$adjusted = false;
		foreach( $xpath -> query( './/video[contains(concat(" ",normalize-space(@class)," ")," video-js ")]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			if( $attrPoster = HtmlNd::GetAttrNode( $item, 'poster' ) )
				$ctxProcess[ 'aAttrImg' ][] = $attrPoster;

			$item -> parentNode -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => array_merge( HtmlNd::GetAttrClass( $item ), array( 'vjs-controls-enabled', 'vjs-workinghover', 'vjs-user-active', 'js-lzl-ing' ) ), 'tabindex' => '-1', 'role' => 'region' ), array(
				HtmlNd::CreateTag( $doc, 'div', array( 'class' => array( 'vjs-poster' ), 'aria-disabled' => 'false', 'style'=> array( 'background-image' => 'url("' . ( $attrPoster ? ( string )$attrPoster -> nodeValue : '' ) . '");' ) ) ),
				HtmlNd::CreateTag( $doc, 'button', array( 'class' => array( 'vjs-big-play-button' ), 'type' => 'button', 'aria-disabled' => 'false' ), array( HtmlNd::CreateTag( $doc, 'span', array( 'aria-hidden' => 'true', 'class' => array( 'vjs-icon-placeholder' ) ) ) ) ),
			) ) );

			$adjusted = true;
		}

		if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, 'video.video-js {
	display: none;
}

div.video-js:nth-of-type(n+2) {
	display: none;
}' );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}
	}

	if( $ctxProcess[ 'mode' ] == 'full' && Gen::GetArrField( $settCp, array( 'qodefApprAni' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		if( HtmlNd::FirstOfChildren( $xpath -> query( '(.//*[contains(concat(" ",normalize-space(@class)," ")," qodef-qi--has-appear ")])[1]' ) ) )
		{
			$ctxProcess[ 'aCssCrit' ][ '@\\.qodef-qi--appeared@' ] = true;

			{

				$ctx -> aAniAppear[ '.qodef-qi--has-appear:not(.qodef-qi--appeared)' ] = 'function(b){function c(){b.classList.add("qodef-qi--appeared")}var a=b.getAttribute("data-appear-delay");a?(a="random"===a?Math.floor(390*Math.random()+10):parseInt(a,10),setTimeout(c,a)):c()}';
			}
		}
	}

	if( Gen::GetArrField( $settCp, array( 'elmntrTrxAni' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_elmntrTrxAni( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'grnshftPbAosOnceAni' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_grnshftPbAosOnceAni( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'elmntrAni' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_elmntrAni( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'elmntrSpltAni' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		$adjusted = false;
		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-element ")][contains(@data-settings,\'"ui_animate_split":\')]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			$dataSett = ( array )@json_decode( $item -> getAttribute( 'data-settings' ), true );
			if( Gen::GetArrField( $dataSett, array( 'ui_animate_split' ), '' ) != 'ui-split-animate' )
				continue;

			$itemContainer = HtmlNd::FirstOfChildren( $xpath -> query( '(.//*[contains(concat(" ",normalize-space(@class)," ")," elementor-widget-container ")])[1]', $item ) );
			if( !$itemContainer )
				continue;

			$sAniName = Gen::GetArrField( $dataSett, array( 'ui_animate_split_style' ), '' );
			$sAniMode = Gen::GetArrField( $dataSett, array( 'ui_animate_split_by' ), '' );
			if( $sAniMode != 'chars' && $sAniMode != 'words' && $sAniMode != 'lines' )
				continue;

			$ctxProcess[ 'aCssCrit' ][ '@\\.' . $sAniName . '@' ] = true;

			for( $itemContainerChild = HtmlNd::GetFirstElement( $itemContainer ); $itemContainerChild; $itemContainerChild = HtmlNd::GetNextElementSibling( $itemContainerChild ) )
			{
				$itemContainerChildCopy = $itemContainerChild -> cloneNode( true );

				$nWord = 0;
				$nLines = 0;
				$nChar = 0;

				$aItemTxt = array();
				for( $itemTxt = null; $itemTxt = HtmlNd::GetNextTreeChild( $itemContainerChildCopy, $itemTxt );  )
					if( $itemTxt -> nodeType == XML_TEXT_NODE )
						$aItemTxt[] = $itemTxt;

				foreach( $aItemTxt as $itemTxt )
				{
					$txt = trim( $itemTxt -> textContent );
					if( !strlen( $txt ) )
						continue;

					foreach( explode( ' ', $txt ) as $txtWord )
					{
						$txtWord = trim( $txtWord );
						if( !strlen( $txtWord ) )
							continue;

						if( $nWord )
							$itemTxt -> parentNode -> insertBefore( HtmlNd::CreateTag( $doc, 'span', array( 'class' => 'whitespace' ), array( $doc -> createTextNode( ' ' ) ) ), $itemTxt );

						$itemWord = HtmlNd::CreateTag( $doc, 'span', array( 'class' => array( 'word', $sAniMode != 'chars' ? 'ui-e-animated' : null ), 'style' => array( '---ui-word-index' => $nWord, '---ui-line-index' => ( $sAniMode == 'lines' ? 0 : null ) ) ) );
						$itemTxt -> parentNode -> insertBefore( $itemWord, $itemTxt );
						$nWord++;

						if( $sAniMode != 'chars' )
						{
							$itemWord -> appendChild( $doc -> createTextNode( $txtWord ) );
							continue;
						}

						foreach( function_exists( 'mb_str_split' ) ? mb_str_split( $txtWord ) : str_split( $txtWord ) as $txtChar )
						{
							$itemWord -> appendChild( HtmlNd::CreateTag( $doc, 'span', array( 'class' => array( 'char', 'ui-e-animated' ), 'style' => array( '---ui-char-index' => $nChar ) ), array( $doc -> createTextNode( $txtChar ) ) ) );
							$nChar++;
						}
					}

					$itemTxt -> parentNode -> removeChild( $itemTxt );
				}

				unset( $aItemTxt );

				if( $sAniMode == 'lines' )
					$nLines = 1;

				$itemContainerChildCopy -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemContainerChildCopy -> getAttribute( 'style' ) ), array( '---ui-word-total' => $nWord, '---ui-char-total' => $nChar ? $nChar : null, '---ui-line-total' => $nLines ? $nLines : null ) ) ) );
				HtmlNd::AddRemoveAttrClass( $itemContainerChildCopy, array( 'splitting', 'words', $sAniMode != 'words' ? $sAniMode : null ) );

				$itemContainerChild -> setAttribute( 'data-lzl-spl-c', HtmlNd::DeParse( $itemContainerChildCopy ) );
				unset( $itemContainerChildCopy );
			}

			$item -> setAttribute( 'data-lzl-spl-an', $sAniName );
			$item -> setAttribute( 'data-lzl-spl-as', $sAniMode == 'chars' ? '.char' : '.word' );

			$adjusted = true;
		}

		if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
		{
			$ctxProcess[ 'aCssCrit' ][ '@\\.ui-e-animated@' ] = true;

			$ctx -> aAniAppear[ '.elementor-element[data-lzl-spl-an]' ] = 'function(a){function d(){e.forEach(function(b){b.classList.add(f)});a.style.setProperty("opacity","1")}a.querySelectorAll("[data-lzl-spl-c]").forEach(function(b){b.outerHTML=b.getAttribute("data-lzl-spl-c")});var f=a.getAttribute("data-lzl-spl-an"),e=a.querySelectorAll(a.getAttribute("data-lzl-spl-as"));a.removeAttribute("data-lzl-spl-an");var c=JSON.parse(a.getAttribute("data-settings"));delete c.ui_animate_split;a.setAttribute("data-settings",JSON.stringify(c));(c=a.getAttribute("data-lzl-ad"))?
setTimeout(d,parseInt(c,10)):d()}';
		}
	}

	if( Gen::GetArrField( $settCp, array( 'elmntrBgSldshw' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-element ")][contains(@data-settings,"background_slideshow_gallery")]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			$urlFirstImg = Gen::GetArrField( @json_decode( $item -> getAttribute( 'data-settings' ), true ), array( 'background_slideshow_gallery', 0, 'url' ) );
			if( !$urlFirstImg )
				continue;

			$dataId = $item -> getAttribute( 'data-id' );
			if( !$dataId )
				continue;

			$cssSel = '.elementor-element-' . $dataId;
			if( in_array( 'elementor-invisible', HtmlNd::GetAttrClass( $item ) ) )
				$cssSel .= '.elementor-invisible';

			$itemStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemStyle, $cssSel . '{background: center / cover no-repeat url(' . $urlFirstImg . ')!important;}' );
			$item -> parentNode -> insertBefore( $itemStyle, $item );
		}
	}

	if( Gen::GetArrField( $settCp, array( 'prtThSkel' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/body[contains(concat(" ",normalize-space(@class)," ")," theme-porto ")]//*[contains(concat(" ",normalize-space(@class)," ")," skeleton-loading ")]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			if( $itemTmp = HtmlNd::FirstOfChildren( $xpath -> query( './' . $item -> nodeName . '[contains(concat(" ",normalize-space(@class)," ")," skeleton-body ")][1]', $item -> parentNode ) ) )
				$item -> parentNode -> removeChild( $itemTmp );

			if( $itemTpl = HtmlNd::FirstOfChildren( $xpath -> query( './script[@type="text/template"][1]', $item ) ) )
			{
				if( $itemTmp = HtmlNd::Parse( @json_decode( trim( $itemTpl -> nodeValue ) ), LIBXML_NONET ) )
				{
					$item -> removeChild( $itemTpl );
					if( $itemTmp = $doc -> importNode( $itemTmp, true ) )
						HtmlNd::MoveChildren( $item, $itemTmp );
				}

				HtmlNd::AddRemoveAttrClass( $item, array(), array( 'skeleton-loading' ) );
			}
		}
	}

	if( $ctxProcess[ 'mode' ] == 'full' && Gen::GetArrField( $settCp, array( 'astrRsp' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		if( $itemThemeCfgScript = HtmlNd::FirstOfChildren( $xpath -> query( './/body//script[@id="astra-theme-js-js-extra"]' ) ) )
		{
			$ctxProcess[ 'aCssCrit' ][ '@\\.ast-desktop@' ] = true;
			$ctxProcess[ 'aCssCrit' ][ '@\\.ast-header-break-point@' ] = true;

			{

				$itemCmnScript = $doc -> createElement( 'script' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemCmnScript -> setAttribute( 'type', 'text/javascript' );
				$itemCmnScript -> setAttribute( 'seraph-accel-crit', '1' );
				HtmlNd::SetValFromContent( $itemCmnScript, '(function(a,e){function b(){var c=a.body.classList,d=e.innerWidth>astra.break_point;c.toggle("ast-header-break-point",!d);c.toggle("ast-desktop",d)}b();a.addEventListener("seraph_accel_calcSizes",b,{capture:!0,passive:!0});seraph_accel_izrbpb.add(function(){a.removeEventListener("seraph_accel_calcSizes",b,{capture:!0,passive:!0})})})(document,window);
' );
				$ctxProcess[ 'ndBody' ] -> insertBefore( $itemCmnScript, $ctxProcess[ 'ndBody' ] -> firstChild );
			}

			$itemThemeCfgScript -> setAttribute( 'seraph-accel-crit', '1' );
			$ctxProcess[ 'ndBody' ] -> insertBefore( $itemThemeCfgScript, $itemCmnScript );
		}
	}

	if( Gen::GetArrField( $settCp, array( 'ntBlueThRspnsv' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_ntBlueThRspnsv( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'mdknThRspnsv' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_mdknThRspnsv( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'fltsmThBgFill' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_fltsmThBgFill( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'fltsmThAni' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_fltsmThAni( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'the7Ani' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_the7Ani( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'the7MblHdr' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_the7MblHdr( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'sbThAni' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_sbThAni( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'esntlsThAni' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_esntlsThAni( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'beThAni' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_beThAni( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'ukSldshw' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," uk-slideshow-items ")][@uk-height-viewport]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			$props = Ui::ParseStyleAttr( $item -> getAttribute( 'uk-height-viewport' ) );
			if( !$props )
				continue;

			if( isset( $props[ 'minHeight' ] ) )
				$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( 'min-height' => $props[ 'minHeight' ] . 'px' ) ) ) );

			if( $itemFirstSlide = HtmlNd::FirstOfChildren( $xpath -> query( './*[contains(concat(" ",normalize-space(@class)," ")," el-item ")][1]', $item ) ) )
				HtmlNd::AddRemoveAttrClass( $itemFirstSlide, array( 'uk-active' ) );
		}
	}

	if( Gen::GetArrField( $settCp, array( 'ukBgImg' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/*[@uk-img][@data-src]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			$src = $item -> getAttribute( 'data-src' );
			if( !$src )
				continue;

			$item -> removeAttribute( 'data-src' );
			$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( 'background-image' => 'url(' . $src . ')' ) ) ) );
		}
	}

	if( Gen::GetArrField( $settCp, array( 'ukAni' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		$adjusted = false;
		foreach( $xpath -> query( './/*[@uk-scrollspy]' ) as $itemBlock )
		{
			$cfgAniBlock = _UiKit_ParseProps( ( string )$itemBlock -> getAttribute( 'uk-scrollspy' ) );
			if( !isset( $cfgAniBlock[ 'cls' ] ) )
				continue;

			foreach( $xpath -> query( './/*[@uk-scrollspy-class]', $itemBlock ) as $item )
			{
				if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
					continue;

				$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( 'opacity' => '0' ) ) ) );
				if( !strlen( ( string )$item -> getAttribute( 'uk-scrollspy-class' ) ) )
					$item -> setAttribute( 'uk-scrollspy-class', $cfgAniBlock[ 'cls' ] );
				if( isset( $cfgAniBlock[ 'delay' ] ) )
					$item -> setAttribute( 'data-lzl-ad', ( string )$cfgAniBlock[ 'delay' ] );
				$adjusted = true;
			}
		}

		if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
		{
			$ctxProcess[ 'aCssCrit' ][ '@\\.uk-animation-@' ] = true;

			$ctx -> aAniAppear[ '[uk-scrollspy-class]:not(.uk-scrollspy-inview)' ] = '
				function( e )
				{
					var delay = e.getAttribute( "data-lzl-ad" );
					if( delay )
						delay = parseInt( delay, 10 );

					function _apply()
					{
						e.classList.add( e.getAttribute( "uk-scrollspy-class" ) );
						e.style.removeProperty( "opacity" );
					}

					e.classList.add( "uk-scrollspy-inview" );
					delay ? setTimeout( _apply, delay ) : _apply();
					return( delay );
				}
			';
		}
	}

	if( Gen::GetArrField( $settCp, array( 'ukGrid' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		$adjusted = false;
		foreach( $xpath -> query( './/*[@uk-grid]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			HtmlNd::AddRemoveAttrClass( $item, array( 'uk-grid' ) );

			{
				$itemScript = $doc -> createElement( 'script' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemScript -> setAttribute( 'type', 'text/javascript' );
				$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
				HtmlNd::SetValFromContent( $itemScript, 'seraph_accel_cp_ukGrid_calcSizes(document.currentScript.parentNode);' );
				$item -> appendChild( $itemScript );
			}

			$adjusted = true;
		}

		if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
		{
			$ctxProcess[ 'aCssCrit' ][ '@\\.uk-first-column@' ] = true;
			$ctxProcess[ 'aCssCrit' ][ '@\\.uk-grid-margin@' ] = true;
			$ctxProcess[ 'aCssCrit' ][ '@\\.uk-grid-stack@' ] = true;

			{
				$itemScript = $doc -> createElement( 'script' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemScript -> setAttribute( 'type', 'text/javascript' );
				$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
				HtmlNd::SetValFromContent( $itemScript, '
					function seraph_accel_cp_ukGrid_calcSizes( e )
					{
						var nWidth = e.getBoundingClientRect().width;

						var aChild = e.children;
						var iCol = 0, iRow = 0, nCell = 0;
						var nCurRowWidth = 0;
						for( var i = 0; i < aChild.length; i++, iCol++ )
						{
							var eChild = aChild[ i ];
							if( eChild.tagName == "SCRIPT" )
								continue;

							var eChildWidth = eChild.getBoundingClientRect().width;
							nCell++;

							if( nCurRowWidth + eChildWidth > nWidth )
							{
								nCurRowWidth = 0;
								iCol = 0;
								iRow++;
							}

							nCurRowWidth += eChildWidth;

							eChild.classList.toggle( "uk-first-column", !iCol );
							eChild.classList.toggle( "uk-grid-margin", iRow );
						}

						e.classList.toggle( "uk-grid-stack", nCell == ( iRow + 1 ) );
					}

					(
						function( d )
						{
							function OnEvt( evt )
							{
								d.querySelectorAll( "[uk-grid]" ).forEach( seraph_accel_cp_ukGrid_calcSizes );
							}

							d.addEventListener( "seraph_accel_calcSizes", OnEvt, { capture: true, passive: true } );
							seraph_accel_izrbpb.add( function() { d.removeEventListener( "seraph_accel_calcSizes", OnEvt, { capture: true, passive: true } ); } );
						}
					)( document );
				' );
				$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
			}

			{
				$itemsCmnStyle = $doc -> createElement( 'style' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
				HtmlNd::SetValFromContent( $itemsCmnStyle, '
					[uk-grid] > script {
						display: none !important;
					}
				' );
				$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
			}
		}
	}

	if( Gen::GetArrField( $settCp, array( 'ukModal' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/*[@uk-modal]' ) as $item )
			HtmlNd::AddRemoveAttrClass( $item, array( 'uk-modal' ) );
	}

	if( Gen::GetArrField( $settCp, array( 'ukHghtVwp' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/*[@uk-height-viewport]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			$cfgViewport = _UiKit_ParseProps( ( string )$item -> getAttribute( 'uk-height-viewport' ) );
			if( Gen::GetArrField( $cfgViewport, array( 'offset-top' ), '' ) === 'true' )
				$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( 'min-height' => 'calc((100vh - 1px*var(--uk-header-placeholder-cy)) - ' . ( int )Gen::GetArrField( $cfgViewport, array( 'offset-bottom' ), '' ) . 'vh)' ) ) ) );
		}
	}

	if( Gen::GetArrField( $settCp, array( 'ukNavBar' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		$itemIconTpl = null;
		$adjusted = false;
		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," uk-navbar ")]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			if( $cfgUiKit === null )
				$cfgUiKit = _UiKit_GetSett( $ctxProcess, $xpath );

			if( !$cfgUiKit )
				continue;

			foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," uk-navbar-nav ")]', $item ) as $itemNav )
			{
				foreach( $xpath -> query( './/*[@uk-navbar-parent-icon]', $itemNav ) as $itemNavParIcon )
				{

					if( $itemIconTpl === null )
					{
						$itemIconTpl = HtmlNd::ParseAndImport( $doc, Gen::GetArrField( $cfgUiKit, array( 'js', 'navbar-parent-icon' ), '' ) );
						if( !$itemIconTpl )
							$itemIconTpl = false;
					}

					if( $itemIconTpl )
					{
						HtmlNd::AddRemoveAttrClass( $itemNavParIcon, array( 'uk-icon', 'uk-navbar-parent-icon' ) );
						$itemNavParIcon -> appendChild( $itemIconTpl -> cloneNode( true ) );

						$adjusted = true;
					}
				}
			}
		}

		unset( $itemIconTpl );

		if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, '
				[uk-navbar-parent-icon] > *:nth-child(n + 2) {
					display: none;
				}
			' );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}
	}

	if( Gen::GetArrField( $settCp, array( 'tmHdr' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		$adjusted = false;
		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," tm-page ")]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			if( $cfgUiKit === null )
				$cfgUiKit = _UiKit_GetSett( $ctxProcess, $xpath );

			if( !$cfgUiKit )
				continue;

			$bDynHdr = false;
			if( $itemMainSectAttr = HtmlNd::FirstOfChildren( $xpath -> query( '(.//*[contains(concat(" ",normalize-space(@class)," ")," uk-section-secondary ")][@tm-header-transparent])[1]', $item ) ) )
			{
				$transpMode = ( string )$itemMainSectAttr -> getAttribute( 'tm-header-transparent' );
				if( strlen( $transpMode ) )
					$bDynHdr = true;
			}

			$itemHdrLast = null;
			foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," tm-header ")]|.//*[contains(concat(" ",normalize-space(@class)," ")," tm-header-mobile ")]|.//*[contains(concat(" ",normalize-space(@class)," ")," tm-toolbar ")]', $item ) as $itemHdr )
			{
				$itemHdrLast = $itemHdr;
				if( strpos( ( string )$itemHdr -> getAttribute( 'class' ), 'tm-header' ) === false )
					continue;

				foreach( $xpath -> query( './/*[@uk-navbar-toggle-icon]', $itemHdr ) as $itemHdrTglIcon )
				{

					if( $itemIcon = HtmlNd::ParseAndImport( $doc, str_replace( '<style', '<style seraph-accel-noadjust', Gen::GetArrField( $cfgUiKit, array( 'js', 'navbar-toggle-icon' ), '' ) ) ) )
					{
						HtmlNd::AddRemoveAttrClass( $itemHdrTglIcon, array( 'uk-icon', 'uk-navbar-toggle-icon' ) );
						$itemHdrTglIcon -> appendChild( $itemIcon );
						unset( $itemIcon );
					}
				}

				if( $bDynHdr )
				{
					HtmlNd::AddRemoveAttrClass( $itemHdr, array( 'tm-header-overlay' ) );
					if( $itemHdrNavBarCont = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," uk-navbar-container ")]', $itemHdr ) ) )
						HtmlNd::AddRemoveAttrClass( $itemHdrNavBarCont, array( 'uk-navbar-transparent', 'uk-' . $transpMode ) );
				}
			}

			$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( '--uk-header-placeholder-cy' => $bDynHdr ? '0' : 'var(--uk-header-cy)' ) ) ) );
			foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," tm-header-placeholder ")]', $item ) as $itemHdrPlchdr )
				$itemHdrPlchdr -> parentNode -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemHdrPlchdr -> parentNode -> getAttribute( 'style' ) ), array( '--uk-header-placeholder-cy' => $bDynHdr ? 'var(--uk-header-cy)' : '0' ) ) ) );

			if( $itemHdrLast )
			{
				$itemScript = $doc -> createElement( 'script' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemScript -> setAttribute( 'type', 'text/javascript' );
				$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
				HtmlNd::SetValFromContent( $itemScript, 'seraph_accel_cp_tmHdr_calcSizes(document.currentScript.parentNode);' );
				HtmlNd::InsertAfter( $itemHdrLast -> parentNode, $itemScript, $itemHdrLast );

				$adjusted = true;
			}
		}

		if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
		{
			{
				$itemScript = $doc -> createElement( 'script' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemScript -> setAttribute( 'type', 'text/javascript' );
				$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
				HtmlNd::SetValFromContent( $itemScript, '
					function seraph_accel_cp_tmHdr_calcSizes( e )
					{
						var nHdrCy = 0;
						e.querySelectorAll( ".tm-header,.tm-header-mobile,.tm-toolbar" ).forEach(
							function( eHdr )
							{
								nHdrCy += eHdr.clientHeight;
							}
						);
						e.style.setProperty( "--uk-header-cy", nHdrCy );
					}

					(
						function( d )
						{
							function OnEvt( evt )
							{
								d.querySelectorAll( ".tm-page" ).forEach( seraph_accel_cp_tmHdr_calcSizes );
							}

							d.addEventListener( "seraph_accel_calcSizes", OnEvt, { capture: true, passive: true } );
							seraph_accel_izrbpb.add( function() { d.removeEventListener( "seraph_accel_calcSizes", OnEvt, { capture: true, passive: true } ); } );
						}
					)( document );
				' );
				$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
			}

			{
				$itemsCmnStyle = $doc -> createElement( 'style' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
				HtmlNd::SetValFromContent( $itemsCmnStyle, '
					[uk-navbar-toggle-icon] > *:nth-child(n + 2) {
						display: none;
					}
				' );
				$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
			}
		}
	}

	if( Gen::GetArrField( $settCp, array( 'fusionBgVid' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		$adjusted = false;
		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," fusion-background-video-wrapper ")]/iframe' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			$itemWrapper = $item -> parentNode;

			$urlThumb = GetVimeoVideoThumbUrl( $itemWrapper -> getAttribute( 'data-vimeo-video-id' ) );
			$itemWrapper -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemWrapper -> getAttribute( 'style' ) ), array( 'opacity' => null, 'width' => '100%', 'background' => 'center / cover no-repeat url(' . $urlThumb . ')' ) ) ) );

			HtmlNd::RenameAttr( $item, 'src', 'data-lzl-src' );

			$adjusted = true;
		}

		if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
		{
			{
				$itemsCmnStyle = $doc -> createElement( 'style' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				    $itemsCmnStyle -> setAttribute( 'type', 'text/css' );
				HtmlNd::SetValFromContent( $itemsCmnStyle, '
				    .fusion-background-video-wrapper:not([style*="opacity:"]) > iframe
				    {
				        opacity: 0;
				    }
				' );
				$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
			}

			{
				$itemScript = $doc -> createElement( 'script' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemScript -> setAttribute( 'type', 'text/javascript' );
				$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
				HtmlNd::SetValFromContent( $itemScript, '
					seraph_accel_izrbpb.add(
						function()
						{
							document.querySelectorAll( ".fusion-background-video-wrapper>iframe" ).forEach( function( i ){ i.src = i.getAttribute( "data-lzl-src" ) } );
						}
					);
				' );
				$ctxProcess[ 'ndBody' ] -> appendChild( $itemScript );
			}
		}
	}

	if( Gen::GetArrField( $settCp, array( 'fsnEqHghtCols' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		$adjusted = false;
		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," fusion-fullwidth ")][contains(concat(" ",normalize-space(@class)," ")," fusion-equal-height-columns ")]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," fusion-layout-column ")][not(self::node()[contains(concat(" ",normalize-space(@class)," ")," fusion-column-no-min-height ")])]/*[contains(concat(" ",normalize-space(@class)," ")," fusion-column-wrapper ")]', $item ) as $itemWrp )
			{

				if( $itemWrpCont = HtmlNd::GetFirstElement( $itemWrp ) )
					$itemWrpCont -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemWrpCont -> getAttribute( 'style' ) ), array( 'height' => '100%' ) ) ) );
			}

			{
				$itemScript = $doc -> createElement( 'script' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemScript -> setAttribute( 'type', 'text/javascript' );
				$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
				HtmlNd::SetValFromContent( $itemScript, 'seraph_accel_cp_fsnEqHghtCols_calcSizes(document.currentScript.parentNode);' );
				$item -> appendChild( $itemScript );
			}

			$adjusted = true;
		}

		if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
		{
			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, '
				function seraph_accel_cp_fsnEqHghtCols_calcSizes( e )
				{
					var a = e.querySelectorAll( ".fusion-layout-column:not(.fusion-column-no-min-height) > .fusion-column-wrapper" );

					a.forEach(
						function( eWrp )
						{
							eWrp.style.removeProperty( "height" );
						}
					);

					var nCy = 1;
					var nY;
					a.forEach(
						function( eWrp )
						{
							var y = eWrp.getBoundingClientRect().top;
							if( nY === undefined )
								nY = y;

							if( nY === y )
							{
								nCy = Math.max( nCy, eWrp.clientHeight );
								eWrp.style.setProperty( "height", "calc(1px * var(--cols-min-h))" );
							}
						}
					);

					e.style.setProperty( "--cols-min-h", nCy );
				}

				(
					function( d )
					{
						function OnEvt( evt )
						{
							d.querySelectorAll( ".fusion-fullwidth.fusion-equal-height-columns" ).forEach( seraph_accel_cp_fsnEqHghtCols_calcSizes );
						}

						d.addEventListener( "seraph_accel_calcSizes", OnEvt, { capture: true, passive: true } );
						seraph_accel_izrbpb.add( function() { d.removeEventListener( "seraph_accel_calcSizes", OnEvt, { capture: true, passive: true } ); } );
					}
				)( document );
			' );
			$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
		}
	}

	if( Gen::GetArrField( $settCp, array( 'fsnAni' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_fsnAni( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( $ctxProcess[ 'mode' ] == 'full' && Gen::GetArrField( $settCp, array( 'thrvAni' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		if( HtmlNd::FirstOfChildren( $xpath -> query( '(.//*[contains(concat(" ",normalize-space(@class)," ")," tve_ea_thrive_animation ")])[1]' ) ) )
		{
			$ctxProcess[ 'aCssCrit' ][ '@\\.tve-viewport-triggered@' ] = true;
			$ctxProcess[ 'aCssCrit' ][ '@\\.tve_anim_start@' ] = true;

			{

				$ctx -> aAniAppear[ '.tve_ea_thrive_animation:not(.tve-viewport-triggered)' ] = 'function(a){a=a.classList;a.add("tve-viewport-triggered");a.add("tve_anim_start")}';
			}
		}
	}

	if( Gen::GetArrField( $settCp, array( 'phloxThRspnsv' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_phloxThRspnsv( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'phloxThAni' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_phloxThAni( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'sldN2Ss' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_sldN2Ss( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'tdThumbCss' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," td-thumb-css ")][@data-type="css_image"]|.//*[contains(concat(" ",normalize-space(@class)," ")," td_single_image_bg ")][@data-type="css_image"]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			$imgSrc = $item -> getAttribute( 'data-img-url' );
			if( !$imgSrc )
				continue;

			$item -> removeAttribute( 'data-img-url' );
			$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( 'background-image' => 'url("' . $imgSrc . '")' ) ) ) );
		}
	}

	if( Gen::GetArrField( $settCp, array( 'upbAni' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_upbAni( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'upbBgImg' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_upbBgImg( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'upbCntVid' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_upbCntVid( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'ultRspnsv' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_ultRspnsv( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'ultVcHd' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_ultVcHd( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'ultAni' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_ultAni( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'jqJpPlr' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," jp-jplayer ")]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			$itemCont = HtmlNd::GetNextElementSibling( $item );
			if( !$itemCont )
				continue;

			if( $itemCtl = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," jp-controls ")]//*[contains(concat(" ",normalize-space(@class)," ")," jp-pause ")]', $itemCont ) ) )
				$itemCtl -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemCtl -> getAttribute( 'style' ) ), array( 'display' => 'none' ) ) ) );
			if( $itemCtl = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," jp-controls ")]//*[contains(concat(" ",normalize-space(@class)," ")," jp-unmute ")]', $itemCont ) ) )
				$itemCtl -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemCtl -> getAttribute( 'style' ) ), array( 'display' => 'none' ) ) ) );

			if( $itemCtl = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," jp-controls ")]//*[contains(concat(" ",normalize-space(@class)," ")," jp-seek-bar ")]', $itemCont ) ) )
				$itemCtl -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemCtl -> getAttribute( 'style' ) ), array( 'width' => '100%' ) ) ) );

			if( $itemCtl = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," jp-controls ")]//*[contains(concat(" ",normalize-space(@class)," ")," jp-current-time ")]', $itemCont ) ) )
				$itemCtl -> appendChild( $doc -> createTextNode( '00:00' ) );
		}
	}

	if( Gen::GetArrField( $settCp, array( 'prstPlr' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_prstPlr( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'elmsKitImgCmp' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		$itemsCmnStyle = null;
		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementskit-image-comparison ")]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			$offs = $item -> getAttribute( 'data-offset' );
			if( !$offs )
				continue;

			if( $ctxProcess[ 'mode' ] == 'full' && !$itemsCmnStyle )
			{
				$itemsCmnStyle = $doc -> createElement( 'style' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
				HtmlNd::SetValFromContent( $itemsCmnStyle, '
					.image-comparison-container:not(.twentytwenty-container) {
						overflow: hidden;
						position: relative;
					}

					.image-comparison-container:not(.twentytwenty-container) > img:first-child {
						position: absolute;
						object-fit: cover;
						object-position: 0 0;
					}

					.twentytwenty-horizontal .image-comparison-container:not(.twentytwenty-container) > img:first-child {
						width: calc(var(--data-offset) * 100%);
						height: 100%;
						border-top-right-radius: 0;
						border-bottom-right-radius: 0;
					}

					.twentytwenty-vertical .image-comparison-container:not(.twentytwenty-container) > img:first-child {
						width: 100%;
						height: calc(var(--data-offset) * 100%);
						border-bottom-left-radius: 0;
						border-bottom-right-radius: 0;
					}

					.image-comparison-container.twentytwenty-container > .twentytwenty-handle.js-lzl-ing {
						display: none;
					}

					.image-comparison-container .twentytwenty-handle.js-lzl-ing {
						box-sizing: content-box;
					}

					.twentytwenty-horizontal .image-comparison-container:not(.twentytwenty-container) .twentytwenty-handle.js-lzl-ing {
						left: calc(var(--data-offset) * 100%);
					}

					.twentytwenty-vertical .image-comparison-container:not(.twentytwenty-container) .twentytwenty-handle.js-lzl-ing {
						top: calc(var(--data-offset) * 100%);
					}
				' );
				$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
			}

			$isVert = in_array( 'image-comparison-container-vertical', HtmlNd::GetAttrClass( $item ) );

			HtmlNd::AddRemoveAttrClass( $item -> parentNode, 'twentytwenty-' . ( $isVert ? 'vertical' : 'horizontal' ) );
			$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( '--data-offset' => $offs ) ) ) );

			$itemCtl = HtmlNd::Parse( '<div class="twentytwenty-handle js-lzl-ing"><span class="twentytwenty-' . ( $isVert ? 'down' : 'left' ) . '-arrow"></span><span class="twentytwenty-' . ( $isVert ? 'up' : 'right' ) . '-arrow"></span></div>' );
			if( $itemCtl && $itemCtl -> firstChild )
				if( $itemCtl = $doc -> importNode( $itemCtl -> firstChild, true ) )
					$item -> appendChild( $itemCtl );
		}
	}

	if( Gen::GetArrField( $settCp, array( 'haCrsl' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		$itemsCmnStyle = null;
		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," ha-carousel ")][@data-settings]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			$dataSett = @json_decode( $item -> getAttribute( 'data-settings' ), true );
			if( !$dataSett )
				continue;

			$dataId = $item -> getAttribute( 'data-id' );
			if( !$dataId )
				continue;

			if( !( $itemSlides = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," ha-slick--carousel ")][1]', $item ) ) ) )
				continue;

			$aSlides = HtmlNd::ChildrenAsArr( $xpath -> query( './*[contains(concat(" ",normalize-space(@class)," ")," slick-slide ")]', $itemSlides ) );
			if( !$aSlides )
				continue;

			if( $ctx -> cfgElmntrFrontend === null )
				$ctx -> cfgElmntrFrontend = _Elmntr_GetFrontendCfg( $xpath );

			if( $ctxProcess[ 'mode' ] == 'full' && !$itemsCmnStyle )
			{
				$itemsCmnStyle = $doc -> createElement( 'style' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
				HtmlNd::SetValFromContent( $itemsCmnStyle, '
					.ha-slick--carousel.js-lzl-ing {
						width: 400%;
						text-align: center;
						margin-left: -150%;
					}

					.ha-slick--carousel.js-lzl-ing.slick-initialized,
					.ha-slick--carousel:not(.js-lzl-ing):not(.slick-initialized) {
						display: none!important;
					}

					.ha-slick--carousel.js-lzl-ing:not(.slick-initialized),
					.ha-slick--carousel:not(.js-lzl-ing).slick-initialized {
						display: block!important;
					}

					.ha-slick--carousel.js-lzl-ing .slick-slide {
						display: inline-block;
						float: none;
					}
				' );
				$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
			}

			$aViews = Gen::GetArrField( $ctx -> cfgElmntrFrontend, array( 'views' ), array() );

			$nShowMax = 0;
			foreach( $aViews as $viewId => $view )
			{
				$nShow = ( int )Gen::GetArrField( $dataSett, array( 'slides_to_show' . ( $viewId == 'desktop' ? '' : ( '_' . $viewId ) ) ) );
				if( !$nShow )
					continue;

				$nShow = $nShow + 2;
				if( $nShowMax < $nShow )
					$nShowMax = $nShow;
			}

			$itemStyleCont = '';
			$maxWidthPrev = null;
			foreach( $aViews as $viewId => $view )
			{
				$nShow = ( int )Gen::GetArrField( $dataSett, array( 'slides_to_show' . ( $viewId == 'desktop' ? '' : ( '_' . $viewId ) ) ) );
				if( !$nShow )
					continue;

				$maxWidth = $view[ 'cxMax' ];
				if( $maxWidth == 2147483647 )
					$maxWidth = null;

				$nShow = $nShow + 2;

				if( $maxWidthPrev || $maxWidth )
					$itemStyleCont .= '@media ' . ( $maxWidthPrev ? ( '(min-width: ' . ( $maxWidthPrev + 1 ) . 'px)' ) : '' ) . ( $maxWidthPrev && $maxWidth ? ' and ' : '' ) . ( $maxWidth ? ( '(max-width: ' . $maxWidth . 'px)' ) : '' ) . ' {' . "\n";

				$itemStyleCont .= '.ha-carousel.elementor-element-' . $dataId . ' .ha-slick--carousel.js-lzl-ing .slick-slide {width: calc((100% / 4 - 100px) / ' . ( $nShow - 2 ) . ');}' . "\n";
				for( $i = 0; $i < ( int )( ( $nShowMax - $nShow ) / 2 ); $i++ )
				{
					$itemStyleCont .= '.ha-carousel.elementor-element-' . $dataId . ' .ha-slick--carousel.js-lzl-ing .slick-slide:nth-child(' . ( $i + 1 ) . '),';
					$itemStyleCont .= '.ha-carousel.elementor-element-' . $dataId . ' .ha-slick--carousel.js-lzl-ing .slick-slide:nth-child(' . ( $nShowMax - $i ) . '),';
				}
				if( ( !( $nShow % 2 ) && ( $nShowMax % 2 ) ) || ( ( $nShow % 2 ) && !( $nShowMax % 2 ) ) )
					$itemStyleCont .= '.ha-carousel.elementor-element-' . $dataId . ' .ha-slick--carousel.js-lzl-ing .slick-slide:nth-child(' . ( $nShowMax - ( int )( ( $nShowMax - $nShow ) / 2 ) ) . '),';

				$itemStyleCont = rtrim( $itemStyleCont, ',' );
				$itemStyleCont .= ' {display:none;}' . "\n";

				if( $maxWidthPrev || $maxWidth )
					$itemStyleCont .= '}' . "\n";

				if( $maxWidth )
					$maxWidthPrev = $maxWidth;
			}

			{
				$itemStyle = $doc -> createElement( 'style' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemStyle -> setAttribute( 'type', 'text/css' );
				HtmlNd::SetValFromContent( $itemStyle, $itemStyleCont );
				$item -> parentNode -> insertBefore( $itemStyle, $item );
			}

			$itemSlidesTmp = $itemSlides -> cloneNode( false );
			$itemSlides -> parentNode -> appendChild( $itemSlidesTmp );

			for( $i = 0; $i < ( int )( $nShowMax / 2 ); $i++ )
			{
				$idx = count( $aSlides ) - ( int )( $nShowMax / 2 ) + $i;
				if( $idx >= 0 )
					$slide = $aSlides[ $idx ] -> cloneNode( true );
				else
					$slide = $aSlides[ 0 ] -> cloneNode( true );
				$itemSlidesTmp -> appendChild( $slide );
			}

			$slide = $aSlides[ 0 ] -> cloneNode( true );

			$itemSlidesTmp -> appendChild( $slide );

			for( $i = 0; $i < ( int )( $nShowMax / 2 ); $i++ )
			{
				$idx = $i + 1;
				if( $idx < count( $aSlides ) )
					$slide = $aSlides[ $idx ] -> cloneNode( true );
				else
					$slide = $aSlides[ 0 ] -> cloneNode( true );
				$itemSlidesTmp -> appendChild( $slide );
			}

			HtmlNd::AddRemoveAttrClass( $itemSlidesTmp, array( 'slick-slider', 'js-lzl-ing' ) );

			$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( 'overflow' => 'hidden' ) ) ) );

			if( (isset($dataSett[ 'navigation' ])?$dataSett[ 'navigation' ]:null) == 'dots' )
				_SlickSld_AddDots( $doc, $itemSlidesTmp, 'slick-dots', count( $aSlides ), function( $sld, $i ) { return( '<li class="" role="presentation"><button type="button" role="tab"></button></li>' ); } );

			{
				$itemNoScript = $doc -> createElement( 'noscript' );
				$itemNoScript -> setAttribute( 'data-lzl-bjs', '' );
				$itemSlides -> parentNode -> insertBefore( $itemNoScript, $itemSlides );
				$itemNoScript -> appendChild( $itemSlides );
				ContNoScriptItemClear( $itemNoScript );

				$ctx -> bBjs = true;
			}
		}
	}

	if( Gen::GetArrField( $settCp, array( 'elmntrTabs' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-tabs ")]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			if( $itemFirstTabTitle = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-tabs-wrapper ")]//*[contains(concat(" ",normalize-space(@class)," ")," elementor-tab-title ")][@data-tab="1"]', $item ) ) )
			{
				HtmlNd::AddRemoveAttrClass( $itemFirstTabTitle, array( 'elementor-active' ) );
			}

			if( $itemFirstTabBody = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-tabs-content-wrapper ")]//*[contains(concat(" ",normalize-space(@class)," ")," elementor-tab-content ")][@data-tab="1"]', $item ) ) )
			{
				HtmlNd::AddRemoveAttrClass( $itemFirstTabBody, array( 'elementor-active' ) );
				$itemFirstTabBody -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemFirstTabBody -> getAttribute( 'style' ) ), array( 'display' => 'block' ) ) ) );
				$itemFirstTabBody -> removeAttribute( 'hidden' );
			}
		}
	}

	if( Gen::GetArrField( $settCp, array( 'elmntrAccrdn' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-accordion ")][@role="tablist"]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			if( $itemFirstTabBody = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-accordion-item ")]//*[contains(concat(" ",normalize-space(@class)," ")," elementor-tab-content ")][@data-tab="1"]', $item ) ) )
			{
				HtmlNd::AddRemoveAttrClass( $itemFirstTabBody, array( 'elementor-active' ) );
				$itemFirstTabBody -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemFirstTabBody -> getAttribute( 'style' ) ), array( 'display' => 'block' ) ) ) );
			}
		}
	}

	if( Gen::GetArrField( $settCp, array( 'elmntrAdvTabs' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," eael-advance-tabs ")]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			{
				$itemFirstTabTitle = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," eael-tabs-nav ")]//*[contains(concat(" ",normalize-space(@class)," ")," eael-tab-item-trigger ")][contains(concat(" ",normalize-space(@class)," ")," active-default ")]', $item ) );
				if( !$itemFirstTabTitle )
					$itemFirstTabTitle = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," eael-tabs-nav ")]//*[contains(concat(" ",normalize-space(@class)," ")," eael-tab-item-trigger ")][@data-tab="1"]', $item ) );
				if( $itemFirstTabTitle )
					HtmlNd::AddRemoveAttrClass( $itemFirstTabTitle, 'active' );
			}

			{
				$itemFirstTabBody = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," eael-tabs-content ")]//*[contains(concat(" ",normalize-space(@class)," ")," eael-tab-content-item ")][contains(concat(" ",normalize-space(@class)," ")," active-default ")]', $item ) );
				if( !$itemFirstTabBody )
					$itemFirstTabBody = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," eael-tabs-content ")]//*[contains(concat(" ",normalize-space(@class)," ")," eael-tab-content-item ")][1]', $item ) );
				if( $itemFirstTabBody )
					HtmlNd::AddRemoveAttrClass( $itemFirstTabBody, 'active' );
			}
		}
	}

	if( Gen::GetArrField( $settCp, array( 'elmntrPremTabs' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," premium-tabs ")]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			{
				$itemFirstTabTitle = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," premium-tabs-nav ")]//*[contains(concat(" ",normalize-space(@class)," ")," premium-tabs-nav-list-item ")][contains(concat(" ",normalize-space(@class)," ")," active-default ")]', $item ) );
				if( !$itemFirstTabTitle )
					$itemFirstTabTitle = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," premium-tabs-nav ")]//*[contains(concat(" ",normalize-space(@class)," ")," premium-tabs-nav-list-item ")][@data-list-index="0"]', $item ) );
				if( $itemFirstTabTitle )
					HtmlNd::AddRemoveAttrClass( $itemFirstTabTitle, 'tab-current' );
			}

			{
				$itemFirstTabBody = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," premium-content-wrap ")]//*[contains(concat(" ",normalize-space(@class)," ")," premium-tabs-content-section ")][contains(concat(" ",normalize-space(@class)," ")," active-default ")]', $item ) );
				if( !$itemFirstTabBody )
					$itemFirstTabBody = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," premium-content-wrap ")]//*[contains(concat(" ",normalize-space(@class)," ")," premium-tabs-content-section ")][1]', $item ) );
				if( $itemFirstTabBody )
					HtmlNd::AddRemoveAttrClass( $itemFirstTabBody, 'content-current' );
			}
		}
	}

	if( Gen::GetArrField( $settCp, array( 'xooelTabs' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," xoo-el-form-container ")][count(.//*[contains(concat(" ",normalize-space(@class)," ")," xoo-el-tabs ")]) > 0]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			$curTab = $item -> getAttribute( 'data-active' );
			if( $itemCurTab = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," xoo-el-tabs ")]//*[@data-tab="' . $curTab . '"]', $item ) ) )
				HtmlNd::AddRemoveAttrClass( $itemCurTab, array( 'xoo-el-active' ) );
			if( $itemCurTabBody = HtmlNd::FirstOfChildren( $xpath -> query( './/*[@data-section="' . $curTab . '"][contains(concat(" ",normalize-space(@class)," ")," xoo-el-section ")]', $item ) ) )
				HtmlNd::AddRemoveAttrClass( $itemCurTabBody, array( 'xoo-el-active' ) );
		}
	}

	if( Gen::GetArrField( $settCp, array( 'phtncThmb' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/a[contains(concat(" ",normalize-space(@class)," ")," photonic-lb ")]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			foreach( array( 'href', 'data-download-url' ) as $attr )
			{
				if( $src = $item -> getAttribute( $attr ) )
				{
					$imgSrc = new ImgSrc( html_entity_decode( $src ) );

					$r = Images_ProcessSrc( $ctxProcess, $imgSrc, $settCache, $settImg, $settCdn );
					if( $r === false )
						return( false );

					if( $r )
						$item -> setAttribute( $attr, $imgSrc -> src );

					unset( $imgSrc );
				}
			}
		}
	}

	if( Gen::GetArrField( $settCp, array( 'elmntrVids' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		$widgetId = 0;

		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-widget-video ")]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			$itemVideoPlaceholder = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-video ")]', $item ) );
			if( !$itemVideoPlaceholder )
				continue;

			$dataSett = @json_decode( $item -> getAttribute( 'data-settings' ), true );
			if( !$dataSett )
				$dataSett = @json_decode( $item -> getAttribute( 'data-cmplz-elementor-settings' ), true );
			if( !$dataSett )
			{
				if( $urlVideoThumb = $item -> getAttribute( 'data-placeholder-image' ) )
					$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( 'background' => 'center / cover no-repeat url(' . $urlVideoThumb . ')!important' ) ) ) );
				continue;
			}

			switch( Gen::GetArrField( $dataSett, array( 'video_type' ) ) )
			{
			case 'youtube':
				if( $id = GetVideoThumbIdFromUrl( $ctxProcess, Gen::GetArrField( $dataSett, array( 'youtube_url' ), '' ) ) )
				{
					$metas = GetYouTubeVideoAttrs( $id );

					$autoplay = Gen::GetArrField( $dataSett, array( 'autoplay' ) ) == 'yes';
					$mute = Gen::GetArrField( $dataSett, array( 'mute' ) ) == 'yes';
					$loop = Gen::GetArrField( $dataSett, array( 'loop' ) ) == 'yes';
					$controls = Gen::GetArrField( $dataSett, array( 'controls' ) ) == 'yes';
					$start = Gen::GetArrField( $dataSett, array( 'start' ) );

					$itemVideoPlaceholder = HtmlNd::SetTag( $itemVideoPlaceholder, 'iframe' );
					$itemVideoPlaceholder -> setAttribute( 'frameborder', '0' );
					$itemVideoPlaceholder -> setAttribute( 'allowfullscreen', '1' );
					$itemVideoPlaceholder -> setAttribute( 'allow', 'accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture' . ( $autoplay ? ' autoplay;' : '' ) );
					$itemVideoPlaceholder -> setAttribute( 'src', Net::UrlAddArgs( 'https://www.youtube.com/embed/' . $id, array( 'start' => $start ? $start : null, 'autoplay' => $autoplay ? '1' : null, 'controls' => $controls ? '1' : null, 'mute' => $mute ? '1' : null, 'loop' => $loop ? '1' : null, 'rel' => '0', 'playsinline' => '0', 'modestbranding' => '0', 'enablejsapi' => '1', 'origin' => Wp::GetSiteRootUrl() ) ) );
					if( (isset($metas[ 'title' ])?$metas[ 'title' ]:null) )
						$itemVideoPlaceholder -> setAttribute( 'title', $metas[ 'title' ] );

					switch( Gen::GetArrField( $dataSett, array( 'aspect_ratio' ) ) )
					{
					case '169':
						$itemVideoPlaceholder -> setAttribute( 'width', '640' );
						$itemVideoPlaceholder -> setAttribute( 'height', '360' );
						break;

					default:
						$itemVideoPlaceholder -> setAttribute( 'width', '640' );
						$itemVideoPlaceholder -> setAttribute( 'height', '360' );
						break;
					}
				}

				break;
			}
		}
	}

	if( Gen::GetArrField( $settCp, array( 'suTabs' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," su-tabs ")]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			$iActiveDef = ( int )$item -> getAttribute( 'data-active' );
			if( !$iActiveDef )
				$iActiveDef = 1;

			{
				$itemFirstTabTitle = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," su-tabs-nav ")]/*[' . $iActiveDef . ']', $item ) );
				if( $itemFirstTabTitle )
					HtmlNd::AddRemoveAttrClass( $itemFirstTabTitle, 'su-tabs-current' );
			}

			if( $itemFirstTabBody = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," su-tabs-pane ")][' . $iActiveDef . ']', $item ) ) )
			{
				HtmlNd::AddRemoveAttrClass( $itemFirstTabBody, array( 'su-tabs-pane-open' ) );
			}
		}
	}

	if( Gen::GetArrField( $settCp, array( 'jetMobMenu' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_jetMobMenu( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'jetCrsl' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_jetCrsl( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'jetCrslPst' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_jetCrslPst( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'jetLott' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_jetLott( $ctx, $ctxProcess, $settCache, $settImg, $settCdn, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'elmntrNavMenu' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		$adjusted = false;
		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-widget-nav-menu ")][@data-settings]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			$itemSubMenuIconTpl = null;
			foreach( $xpath -> query( './/nav[contains(concat(" ",normalize-space(@class)," ")," elementor-nav-menu--main ")]/*[contains(concat(" ",normalize-space(@class)," ")," elementor-nav-menu ")]/li[contains(concat(" ",normalize-space(@class)," ")," menu-item-has-children ")]/*[contains(concat(" ",normalize-space(@class)," ")," elementor-item ")]', $item ) as $itemMenu )
			{
				if( !$itemSubMenuIconTpl )
				{
					$dataSett = @json_decode( $item -> getAttribute( 'data-settings' ), true );

					$itemSubMenuIconTpl = Gen::GetArrField( $dataSett, array( 'submenu_icon', 'value' ) );
					if( strpos( $itemSubMenuIconTpl, '<' ) === false )
						$itemSubMenuIconTpl = '<i class="' . $itemSubMenuIconTpl . '"></i>';
					$itemSubMenuIconTpl = HtmlNd::Parse( '<span class="sub-arrow js-lzl-ing">' . $itemSubMenuIconTpl . '</span>' );
					if( $itemSubMenuIconTpl && $itemSubMenuIconTpl -> firstChild )
						$itemSubMenuIconTpl = $doc -> importNode( $itemSubMenuIconTpl -> firstChild, true );
				}

				$itemMenu -> appendChild( $itemSubMenuIconTpl -> cloneNode( true ) );
				$adjusted = true;
			}
		}

		if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, '.elementor-widget-nav-menu ul[data-smartmenus-id] .sub-arrow.js-lzl-ing {display:none!important;}' );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}
	}

	if( Gen::GetArrField( $settCp, array( 'elmntrPremNavMenu' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		$adjusted = false;
		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-widget-premium-nav-menu ")][@data-settings]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( 'display' => 'none' ) ) ) );

			if( $itemNavDef = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," premium-nav-default ")]', $item ) ) )
				HtmlNd::AddRemoveAttrClass( $itemNavDef, array(), array( 'premium-nav-default' ) );

			{
				$itemScript = $doc -> createElement( 'script' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemScript -> setAttribute( 'type', 'text/javascript' );
				$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
				HtmlNd::SetValFromContent( $itemScript, 'seraph_accel_cp_elmntrPremNavMenu_calcSizes(document.currentScript.parentNode,!0);' );
				$item -> appendChild( $itemScript );
			}

			$adjusted = true;
		}

		if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
		{
			$ctxProcess[ 'aCssCrit' ][ '@\\.premium-ver-hamburger-menu@' ] = true;
			$ctxProcess[ 'aCssCrit' ][ '@\\.premium-hamburger-menu@' ] = true;

			{

				$itemCmnScript = $doc -> createElement( 'script' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemCmnScript -> setAttribute( 'type', 'text/javascript' );
				$itemCmnScript -> setAttribute( 'seraph-accel-crit', '1' );
				HtmlNd::SetValFromContent( $itemCmnScript, "function seraph_accel_cp_elmntrPremNavMenu_calcSizes(a,c){var b=a.querySelector(\".premium-nav-widget-container\");if(b){try{var d=JSON.parse(b.getAttribute(\"data-settings\"))}catch(f){}if(d)var e=d.breakpoint;a.classList.add(\"premium-ver-hamburger-menu\");a.classList.toggle(\"premium-hamburger-menu\",window.innerWidth<=e);c&&(b.style.removeProperty(\"visibility\"),b.style.removeProperty(\"opacity\"),a.style.removeProperty(\"display\"))}}\n(function(a){function c(b){a.querySelectorAll(\".elementor-widget-premium-nav-menu[data-settings]\").forEach(seraph_accel_cp_elmntrPremNavMenu_calcSizes)}a.addEventListener(\"seraph_accel_calcSizes\",c,{capture:!0,passive:!0});seraph_accel_izrbpb.add(function(){a.removeEventListener(\"seraph_accel_calcSizes\",c,{capture:!0,passive:!0})})})(document)" );
				$ctxProcess[ 'ndBody' ] -> insertBefore( $itemCmnScript, $ctxProcess[ 'ndBody' ] -> firstChild );
			}
		}
	}

	if( Gen::GetArrField( $settCp, array( 'elmntrPremScrl' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		if( $ctxProcess[ 'mode' ] == 'full' && ( HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," premium-mscroll-yes ")]' ) ) || HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-widget-premium-hscroll ")]' ) ) ) )
		{
			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );

			HtmlNd::SetValFromContent( $itemScript, "(function(b,a){seraph_accel_izrbpb.add(function(){a.scrollTo_js_lzl_ing=window.scrollTo;a.scrollTo=function(){};b.addEventListener(\"seraph_accel_jsFinish\",function(){a.scrollTo=window.scrollTo_js_lzl_ing;delete a.scrollTo_js_lzl_ing},{capture:!0,passive:!0})},99)})(document,window)" );
			$ctxProcess[ 'ndBody' ] -> appendChild( $itemScript );
		}
	}

	if( Gen::GetArrField( $settCp, array( 'elmntrPremCrsl' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_elmntrPremCrsl( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'woodmartPrcFlt' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		$wooPriceSliderPrms = null;

		foreach( $xpath -> query( './/body[contains(concat(" ",normalize-space(@class)," ")," theme-woodmart ")]//*[contains(concat(" ",normalize-space(@class)," ")," woocommerce ")][contains(concat(" ",normalize-space(@class)," ")," widget_price_filter ")]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			_Woo_PriceSlider( $doc, $item, $xpath, $wooPriceSliderPrms );
		}

		unset( $wooPriceSliderPrms );
	}

	if( Gen::GetArrField( $settCp, array( 'wooPrcFlt' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		$wooPriceSliderPrms = null;
		$adjusted = false;

		foreach( $xpath -> query( './/body[not(self::node()[contains(concat(" ",normalize-space(@class)," ")," theme-woodmart ")])]//*[contains(concat(" ",normalize-space(@class)," ")," woocommerce ")][contains(concat(" ",normalize-space(@class)," ")," widget_price_filter ")]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			_Woo_PriceSlider( $doc, $item, $xpath, $wooPriceSliderPrms, false );

			$adjusted = true;
		}

		unset( $wooPriceSliderPrms );

		if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
		{

			{

				$itemScript = $doc -> createElement( 'script' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemScript -> setAttribute( 'type', 'text/javascript' );
				$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
				HtmlNd::SetValFromContent( $itemScript, "(function(e,a){var b=a.seraph_accel_js_lzl_initScrCustom;a.seraph_accel_js_lzl_initScrCustom=function(){b&&b();a.jQuery&&a.jQuery.fn.slider&&!a.jQuery.fn.seraph_accel_slider&&(a.jQuery.fn.seraph_accel_slider=a.jQuery.fn.slider,a.jQuery.fn.slider=function(c){this.each(function(){this.querySelectorAll(\"*\").forEach(function(d){d.remove()})});return a.jQuery.fn.seraph_accel_slider.call(this,c)},a.jQuery.fn.slider.defaults=a.jQuery.fn.seraph_accel_slider.defaults)}})(document,window)" );
				$ctxProcess[ 'ndBody' ] -> appendChild( $itemScript );
			}
		}
	}

	if( Gen::GetArrField( $settCp, array( 'wooPrdGallSld' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_wooPrdGallSld( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'wbwPrdFlt' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		$adjusted = false;
		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," wpfMainWrapper ")]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			if( $itemSlider = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," wpfPriceFilterRange ")]', $item ) ) )
				_PriceSliderAdd( $doc, $itemSlider );

			foreach( $xpath -> query( './*[contains(concat(" ",normalize-space(@class)," ")," wpfFilterWrapper ")]', $item ) as $itemWrp )
				$itemWrp -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemWrp -> getAttribute( 'style' ) ), array( 'visibility' => 'inherit' ) ) ) );

			if( $itemLoader = HtmlNd::FirstOfChildren( $xpath -> query( './*[contains(concat(" ",normalize-space(@class)," ")," wpfLoaderLayout ")]', $item ) ) )
				$itemLoader -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemLoader -> getAttribute( 'style' ) ), array( 'display' => 'none' ) ) ) );

			$adjusted = true;
		}

		if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, '
				.wpfFilterWrapper[style*=visibility] .wpfPriceFilterRange .ui-slider-range.js-lzl-ing {
					display: none;
				}
			' );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}
	}

	if( Gen::GetArrField( $settCp, array( 'wpStrs' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		$adjusted = false;
		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," js-wpstories-serialized ")]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			if( !( $itemCont = HtmlNd::ParseAndImport( $doc, '<div class="js-wpstories-group-wrap js-lzl">' . @rawurldecode( @base64_decode( $item -> getAttribute( 'data-content' ) ) ) . '</div>' ) ) )
				continue;

			HtmlNd::InsertAfter( $item -> parentNode, $itemCont, $item );
			$item -> setAttribute( 'data-content', @base64_encode( '' ) );
			unset( $itemCont );

			$adjusted = true;
		}

		if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, '
				.js-wpstories-group-wrap:not(.js-lzl) {
					display: none !important;
				}
			' );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}
	}

	if( Gen::GetArrField( $settCp, array( 'txpTagGrps' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," tag-groups-cloud ")][starts-with(@id,"tag-groups-cloud-tabs-")]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			HtmlNd::AddRemoveAttrClass( $item, 'ui-tabs ui-corner-all ui-widget ui-widget-content', 'tag-groups-cloud-hidden' );

			if( $itemTabs = HtmlNd::FirstOfChildren( $xpath -> query( './/ul', $item ) ) )
			{
				HtmlNd::AddRemoveAttrClass( $itemTabs, 'ui-tabs-nav ui-corner-all ui-helper-reset ui-helper-clearfix ui-widget-header' );

				$bFirst = true;
				foreach( $xpath -> query( './li', $itemTabs ) as $itemTab )
				{
					HtmlNd::AddRemoveAttrClass( $itemTab, 'ui-tabs-tab ui-corner-top ui-state-default ui-tab' );
					if( $bFirst )
					{
						$bFirst = false;
						HtmlNd::AddRemoveAttrClass( $itemTab, 'ui-tabs-active ui-state-active' );
					}
				}
			}

			$bFirst = true;
			foreach( $xpath -> query( './div', $item ) as $itemTabBody )
			{
				HtmlNd::AddRemoveAttrClass( $itemTabBody, 'ui-tabs-panel ui-corner-bottom ui-widget-content' );
				if( $bFirst )
					$bFirst = false;
				else
					$itemTabBody -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemTabBody -> getAttribute( 'style' ) ), array( 'display' => 'none' ) ) ) );
			}
		}
	}

	{
		$adjusted = false;
		$bDynSize = false;

		if( Gen::GetArrField( $settCp, array( 'diviMvImg' ), false ) )
		{
			if( !$xpath )
				$xpath = new \DOMXPath( $doc );
			_ProcessCont_Cp_diviMvImg( $ctx, $ctxProcess, $doc, $xpath, $adjusted, $bDynSize );
		}

		if( Gen::GetArrField( $settCp, array( 'diviMvText' ), false ) )
		{
			if( !$xpath )
				$xpath = new \DOMXPath( $doc );
			_ProcessCont_Cp_diviMvText( $ctx, $ctxProcess, $doc, $xpath, $adjusted, $bDynSize );
		}

		if( Gen::GetArrField( $settCp, array( 'diviMvSld' ), false ) )
		{
			if( !$xpath )
				$xpath = new \DOMXPath( $doc );
			_ProcessCont_Cp_diviMvSld( $ctx, $ctxProcess, $doc, $xpath, $adjusted, $bDynSize );
		}

		if( Gen::GetArrField( $settCp, array( 'diviMvFwHdr' ), false ) )
		{
			if( !$xpath )
				$xpath = new \DOMXPath( $doc );
			_ProcessCont_Cp_diviMvFwHdr( $ctx, $ctxProcess, $doc, $xpath, $adjusted, $bDynSize );
		}

		if( Gen::GetArrField( $settCp, array( 'diviDsmGal' ), false ) )
		{
			if( !$xpath )
				$xpath = new \DOMXPath( $doc );
			_ProcessCont_Cp_diviDsmGal( $ctx, $ctxProcess, $doc, $xpath, $adjusted, $bDynSize );
		}

		_ProcessCont_Cp_diviMv_Finalize( $ctx, $ctxProcess, $doc, $xpath, $adjusted, $bDynSize );
	}

	if( Gen::GetArrField( $settCp, array( 'diviVidBox' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_diviVidBox( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'diviVidBg' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_diviVidBg( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'diviVidFr' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_diviVidFr( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( $ctxProcess[ 'mode' ] == 'full' && Gen::GetArrField( $settCp, array( 'diviLzStls' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_diviLzStls( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'diviPrld' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_diviPrld( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'diviAni' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_diviAni( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'diviDataAni' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_diviDataAni( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'diviStck' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_diviStck( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'diviHdr' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_diviHdr( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'brcksAni' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_brcksAni( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'kdncThAni' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_kdncThAni( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'mkImgSrcSet' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/*[@data-mk-image-src-set]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			if( $dataSett = @json_decode( $item -> getAttribute( 'data-mk-image-src-set' ), true ) )
				$item -> setAttribute( 'src', Gen::GetArrField( $dataSett, array( 'default' ), '' ) );
		}
	}

	if( Gen::GetArrField( $settCp, array( 'merimagBgImg' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," merimag-lazy-image ")]' ) as $item )
		{
			$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( 'background-image' => 'url(' . $item -> getAttribute( 'data-src' ) . ')' ) ) ) );

			$item -> removeAttribute( 'data-src' );
			HtmlNd::AddRemoveAttrClass( $item, array(), array( 'merimag-lazy-image' ) );
		}
	}

	if( Gen::GetArrField( $settCp, array( 'mdcrLdng' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		$adjusted = false;
		foreach( $xpath -> query( './/body[contains(concat(" ",normalize-space(@class)," ")," theme-mydecor ")]//*[contains(concat(" ",normalize-space(@class)," ")," images ")][contains(concat(" ",normalize-space(@class)," ")," loading ")]|.//body[contains(concat(" ",normalize-space(@class)," ")," theme-mydecor ")]//*[contains(concat(" ",normalize-space(@class)," ")," ts-product ")]//*[contains(concat(" ",normalize-space(@class)," ")," content-wrapper ")][contains(concat(" ",normalize-space(@class)," ")," loading ")]|.//body[contains(concat(" ",normalize-space(@class)," ")," theme-mydecor ")]//*[contains(concat(" ",normalize-space(@class)," ")," ts-logo-slider-wrapper ")][contains(concat(" ",normalize-space(@class)," ")," loading ")]|.//body[contains(concat(" ",normalize-space(@class)," ")," theme-mydecor ")]//*[contains(concat(" ",normalize-space(@class)," ")," related-posts ")][contains(concat(" ",normalize-space(@class)," ")," loading ")]|.//*[contains(concat(" ",normalize-space(@class)," ")," woocommerce ")]//*[contains(concat(" ",normalize-space(@class)," ")," product ")]//figure[contains(concat(" ",normalize-space(@class)," ")," loading ")]|.//body[contains(concat(" ",normalize-space(@class)," ")," theme-mydecor ")]//*[contains(concat(" ",normalize-space(@class)," ")," blogs ")]//article//a[contains(concat(" ",normalize-space(@class)," ")," gallery ")][contains(concat(" ",normalize-space(@class)," ")," loading ")]|.//body[contains(concat(" ",normalize-space(@class)," ")," theme-mydecor ")]//*[contains(concat(" ",normalize-space(@class)," ")," ts-blogs-wrapper ")][contains(concat(" ",normalize-space(@class)," ")," loading ")]|.//body[contains(concat(" ",normalize-space(@class)," ")," theme-mydecor ")]//*[contains(concat(" ",normalize-space(@class)," ")," ts-testimonial-wrapper ")]//*[contains(concat(" ",normalize-space(@class)," ")," items ")][contains(concat(" ",normalize-space(@class)," ")," loading ")]|.//body[contains(concat(" ",normalize-space(@class)," ")," theme-mydecor ")]//*[contains(concat(" ",normalize-space(@class)," ")," ts-twitter-slider ")]//*[contains(concat(" ",normalize-space(@class)," ")," items ")][contains(concat(" ",normalize-space(@class)," ")," loading ")]|.//body[contains(concat(" ",normalize-space(@class)," ")," theme-mydecor ")]//*[contains(concat(" ",normalize-space(@class)," ")," ts-portfolio-wrapper ")][contains(concat(" ",normalize-space(@class)," ")," loading ")]|.//body[contains(concat(" ",normalize-space(@class)," ")," theme-mydecor ")]//*[contains(concat(" ",normalize-space(@class)," ")," ts-product-category-wrapper ")]//*[contains(concat(" ",normalize-space(@class)," ")," content-wrapper ")][contains(concat(" ",normalize-space(@class)," ")," loading ")]|.//body[contains(concat(" ",normalize-space(@class)," ")," theme-mydecor ")]//*[contains(concat(" ",normalize-space(@class)," ")," thumbnails-container ")][contains(concat(" ",normalize-space(@class)," ")," loading ")]|.//body[contains(concat(" ",normalize-space(@class)," ")," theme-mydecor ")]//*[contains(concat(" ",normalize-space(@class)," ")," column-products ")][contains(concat(" ",normalize-space(@class)," ")," loading ")]|.//body[contains(concat(" ",normalize-space(@class)," ")," theme-mydecor ")]//*[contains(concat(" ",normalize-space(@class)," ")," ts-team-members ")]//*[contains(concat(" ",normalize-space(@class)," ")," loading ")]|.//body[contains(concat(" ",normalize-space(@class)," ")," theme-mydecor ")]//*[contains(concat(" ",normalize-space(@class)," ")," ts-instagram-wrapper ")][contains(concat(" ",normalize-space(@class)," ")," loading ")]|.//body[contains(concat(" ",normalize-space(@class)," ")," theme-mydecor ")]//*[contains(concat(" ",normalize-space(@class)," ")," elementor-section ")][contains(concat(" ",normalize-space(@class)," ")," loading ")]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			HtmlNd::AddRemoveAttrClass( $item, array(), array( 'loading' ) );
		}
	}

	if( Gen::GetArrField( $settCp, array( 'eaelSmpMnu' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		$adjusted = false;

		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-widget-eael-simple-menu ")]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			$itemContainer = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," eael-simple-menu-container ")]', $item ) );
			if( !$itemContainer )
				continue;

			HtmlNd::AddRemoveAttrClass( $item, 'eael-hamburger--responsive' );

			$strToggleTxt = '';
			if( $item1stMenu = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," eael-simple-menu ")]/*[contains(concat(" ",normalize-space(@class)," ")," menu-item ")]/*[contains(concat(" ",normalize-space(@class)," ")," menu-link ")]', $itemContainer ) ) )
				$strToggleTxt = $item1stMenu -> textContent;

			{
				$widthBrk = array();
				if( @preg_match( '@\\(>\\s*(\\d+)px\\)$@', Gen::GetArrField( @json_decode( $itemContainer -> getAttribute( 'data-hamburger-breakpoints' ), true ), array( $itemContainer -> getAttribute( 'data-hamburger-device' ) ), '' ), $widthBrk ) )
					$widthBrk = $widthBrk[ 1 ];
				else
					$widthBrk = '0';

				$item -> setAttribute( 'data-lzl-width-brk', $widthBrk );
				unset( $widthBrk );
			}

			$itemWrapper = HtmlNd::CreateTag( $doc, 'nav', array( 'class' => array( 'eael-nav-menu-wrapper' ), 'style' => array( 'position' => 'inherit' ) ) );
			foreach( $itemContainer -> childNodes as $itemChild )
			{
				$itemWrapper -> appendChild( $itemChild );
				foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," menu-item-has-children ")]/*[contains(concat(" ",normalize-space(@class)," ")," menu-link ")]', $itemChild ) as $itemMnu )
					$itemMnu -> appendChild( HtmlNd::CreateTag( $doc, 'span', array( 'class' => ( string )$itemContainer -> getAttribute( 'data-indicator-class' ) ) ) );
			}

			$itemContainer -> appendChild( HtmlNd::CreateTag( $doc, 'span', array( 'class' => array( 'eael-simple-menu-toggle-text' ), 'style' => array(  ) ), array( $doc -> createTextNode( $strToggleTxt ) ) ) );
			$itemContainer -> appendChild( $itemWrapper );
			$itemContainer -> appendChild( HtmlNd::CreateTag( $doc, 'button', array( 'class' => array( 'eael-simple-menu-toggle' ), 'style' => array(  ) ), array( HtmlNd::ParseAndImport( $doc, ( string )$itemContainer -> getAttribute( 'data-hamburger-icon' ) ), HtmlNd::CreateTag( $doc, 'span', array( 'class' => array( 'eael-simple-menu-toggle-text' ) ), array( $doc -> createTextNode( $strToggleTxt ) ) ) ) ) );

			{
				$itemScript = $doc -> createElement( 'script' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemScript -> setAttribute( 'type', 'text/javascript' );
				$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
				HtmlNd::SetValFromContent( $itemScript, 'seraph_accel_cp_eaelSmpMnu_calcSizes(document.currentScript.parentNode);' );
				$item -> insertBefore( $itemScript, $item -> firstChild );
			}

			$adjusted = true;
		}

		if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
		{
            $ctxProcess[ 'aCssCrit' ][ '@\\.eael-hamburger--responsive@' ] = true;
            $ctxProcess[ 'aCssCrit' ][ '@\\.eael-hamburger--not-responsive@' ] = true;

			{
				$itemsCmnStyle = $doc -> createElement( 'style' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
				HtmlNd::SetValFromContent( $itemsCmnStyle, '
					.eael-nav-menu-wrapper > .eael-simple-menu-toggle-text, .eael-nav-menu-wrapper > .eael-simple-menu-toggle, .eael-simple-menu .menu-link > span:not(:first-child) {
						display: none !important;
					}
				' );
				$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
			}

			{
				$itemScript = $doc -> createElement( 'script' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemScript -> setAttribute( 'type', 'text/javascript' );
				$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
				HtmlNd::SetValFromContent( $itemScript, '
					function seraph_accel_cp_eaelSmpMnu_calcSizes( e )
					{
						if( window.innerWidth > parseInt( e.getAttribute( "data-lzl-width-brk" ), 10 ) )
						{
							e.classList.remove( "eael-hamburger--responsive" );
							e.classList.add( "eael-hamburger--not-responsive" );
						}
						else
						{
							e.classList.add( "eael-hamburger--responsive" );
							e.classList.remove( "eael-hamburger--not-responsive" );
						}
					}

					(
						function( d )
						{
							function OnEvt( evt )
							{
								d.querySelectorAll( ".elementor-widget-eael-simple-menu" ).forEach( seraph_accel_cp_eaelSmpMnu_calcSizes );
							}

							d.addEventListener( "seraph_accel_calcSizes", OnEvt, { capture: true, passive: true } );
							seraph_accel_izrbpb.add( function() { d.removeEventListener( "seraph_accel_calcSizes", OnEvt, { capture: true, passive: true } ); } );
						}
					)( document );
				' );
				$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
			}
		}
	}

	if( Gen::GetArrField( $settCp, array( 'wprAniTxt' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_wprAniTxt( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'wprTabs' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_wprTabs( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'scrlSeq' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/scrollsequence' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			$itemContainer = HtmlNd::FirstOfChildren( $xpath -> query( './/section[contains(concat(" ",normalize-space(@class)," ")," scrollsequence-wrap ")]', $item ) );
			if( !$itemContainer )
				continue;

			$id = $itemContainer -> getAttribute( 'id' );
			if( !$id )
				continue;

			@preg_match( '@ssq-uid-\\d+-\\d+-(\\d+)@', $id, $idCfg );
			if( !$idCfg )
				continue;
			$idCfg = $idCfg[ 1 ];

			$cfg = _Scrollsequence_GetFrontendCfg( $idCfg, HtmlNd::FirstOfChildren( $xpath -> query( './/script[contains(concat(" ",normalize-space(@class)," ")," scrollsequence-input-script ")]', $item ) ) );
			if( !$cfg )
				continue;

			$itemStyleCont = '';

			$itemStyleCont .= '
				scrollsequence #' . $id . '.scrollsequence-wrap:not([style*="visibility:"]) .scrollsequence-page:first-child {
					display: block !important;
					background: center / cover no-repeat url(' . Gen::GetArrField( $cfg, array( 'page', 0, 'imagesFull', 0 ), '' ) . ');
				}
			';

			if( $ctxProcess[ 'mode' ] == 'full' && $itemStyleCont )
			{
				$itemStyle = $doc -> createElement( 'style' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemStyle -> setAttribute( 'type', 'text/css' );
				HtmlNd::SetValFromContent( $itemStyle, $itemStyleCont );
				$itemContainer -> parentNode -> insertBefore( $itemStyle, $itemContainer );
			}
		}
	}

	if( Gen::GetArrField( $settCp, array( 'elmntrWdgtGal' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-widget-gallery ")][@data-settings]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			if( $ctx -> cfgElmntrFrontend === null )
				$ctx -> cfgElmntrFrontend = _Elmntr_GetFrontendCfg( $xpath );

			$dataSett = @json_decode( $item -> getAttribute( 'data-settings' ), true );

			$itemContainer = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-gallery__container ")]', $item ) );
			if( !$itemContainer )
				continue;

			HtmlNd::AddRemoveAttrClass( $itemContainer, 'e-gallery-container' );

			$content_hover_animation = Gen::GetArrField( $dataSett, array( 'content_hover_animation' ), '' );

			$aImage = array();
			$itemImgContainerIdx = -1;
			foreach( $itemContainer -> childNodes as $itemImgContainer )
			{
				if( $itemImgContainer -> nodeType != XML_ELEMENT_NODE )
					continue;

				$itemImgContainerIdx++;

				$itemImg = HtmlNd::FirstOfChildren( $xpath -> query( './*[contains(concat(" ",normalize-space(@class)," ")," elementor-gallery-item__image ")]', $itemImgContainer ) );
				if( !$itemImg )
					continue;

				$itemImg -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemImg -> getAttribute( 'style' ) ), array( 'background-image' => 'url(' . $itemImg -> getAttribute( 'data-thumbnail' ) . ')' ) ) ) );
				HtmlNd::AddRemoveAttrClass( $itemImg, 'e-gallery-image-loaded' );

				$aImage[] = ( object )array( 'nd' => $itemImgContainer, 'sz' => ( object )array( 'cx' => ( int )$itemImg -> getAttribute( 'data-width' ), 'cy' => ( int )$itemImg -> getAttribute( 'data-height' ) ), 'cssChildIdx' => $itemImgContainerIdx + 1 );

				$itemCont = HtmlNd::FirstOfChildren( $xpath -> query( './*[contains(concat(" ",normalize-space(@class)," ")," elementor-gallery-item__content ")]', $itemImgContainer ) );
				if( $itemCont )
				{
					foreach( $itemCont -> childNodes as $itemContChild )
					{
						if( $itemContChild -> nodeType != XML_ELEMENT_NODE )
							continue;
						HtmlNd::AddRemoveAttrClass( $itemContChild, 'elementor-animated-item--' . $content_hover_animation );
					}

					$itemOverlay = HtmlNd::FirstOfChildren( $xpath -> query( './*[contains(concat(" ",normalize-space(@class)," ")," elementor-gallery-item__overlay ")]', $itemImgContainer ) );
					if( $itemOverlay )
						HtmlNd::AddRemoveAttrClass( $itemOverlay, 'elementor-animated-item--' . $content_hover_animation );
				}
			}

			if( !$aImage )
				continue;

			$itemCssSel = '.elementor-element-' . $item -> getAttribute( 'data-id' );

			$itemStyleCont = '';

			$layout = Gen::GetArrField( $dataSett, array( 'gallery_layout' ), '' );
			HtmlNd::AddRemoveAttrClass( $itemContainer, array( 'e-gallery--ltr', 'e-gallery-' . $layout ) );

			if( $layout == 'justified' )
			{
				foreach( array( array( 'type' => '_mobile', 'widthAlign' => 766, 'cxMax' => Gen::GetArrField( $ctx -> cfgElmntrFrontend, array( 'views', 'mobile', 'cxMax' ), 0 ) ), array( 'type' => '_tablet', 'widthAlign' => 767, 'cxMin' => Gen::GetArrField( $ctx -> cfgElmntrFrontend, array( 'views', 'tablet', 'cxMin' ), 0 ), 'cxMax' => Gen::GetArrField( $ctx -> cfgElmntrFrontend, array( 'views', 'tablet', 'cxMax' ), 0 ) ), array( 'type' => '', 'widthAlign' => 767, 'cxMin' => Gen::GetArrField( $ctx -> cfgElmntrFrontend, array( 'views', 'desktop', 'cxMin' ), 0 ) ) ) as $view )
				{
					$viewGap = Gen::GetArrField( $dataSett, array( 'gap' . $view[ 'type' ] ), array() );
					$viewIdealRowHeight = Gen::GetArrField( $dataSett, array( 'ideal_row_height' . $view[ 'type' ] ), array() );
					if( Gen::GetArrField( $viewIdealRowHeight, array( 'unit' ), '' ) != 'px' || Gen::GetArrField( $viewGap, array( 'unit' ), '' ) != 'px' )
						continue;

					$aRow = array();
					$iCurRow = -1;
					$nCurAvailWidth = 0;
					$cyTotal = 0;
					foreach( $aImage as $image )
					{

						if( !$nCurAvailWidth )
						{
							$nCurAvailWidth = $view[ 'widthAlign' ];
							$iCurRow ++;
							$aRow[ $iCurRow ] = array( 'a' => array(), 'cxAdapted' => 0, 'cy' => 0 );
						}

						$cxAdapted = ( int )round( $image -> sz -> cx * ( ( float )Gen::GetArrField( $viewIdealRowHeight, array( 'size' ), 0 ) / $image -> sz -> cy ) );
						$aRow[ $iCurRow ][ 'a' ][] = array( 'image' => $image, 'cxAdapted' => $cxAdapted );
						$aRow[ $iCurRow ][ 'cxAdapted' ] += $cxAdapted;

						if( $nCurAvailWidth < $cxAdapted )
							$nCurAvailWidth = 0;
						else
						{
							$nCurAvailWidth -= $cxAdapted;
							if( $nCurAvailWidth / $view[ 'widthAlign' ] < 0.2 )
								$nCurAvailWidth = 0;
						}

						if( !$nCurAvailWidth )
						{
							$aRow[ $iCurRow ][ 'cy' ] = ( int )round( ( float )Gen::GetArrField( $viewIdealRowHeight, array( 'size' ), 0 ) * ( $view[ 'widthAlign' ] / ( $aRow[ $iCurRow ][ 'cxAdapted' ] + ( count( $aRow[ $iCurRow ][ 'a' ] ) - 1 ) * Gen::GetArrField( $viewGap, array( 'size' ), 0 ) ) ) );
							$cyTotal += $aRow[ $iCurRow ][ 'cy' ];
						}
					}

					$itemStyleCont .= "\n" . '@media (' . ( isset( $view[ 'cxMin' ] ) ? ( 'min-width: ' . $view[ 'cxMin' ] . 'px' ) : '' ) . ( isset( $view[ 'cxMin' ] ) && isset( $view[ 'cxMax' ] ) ? ') and (' : '' ) . ( isset( $view[ 'cxMax' ] ) ? ( 'max-width: ' . $view[ 'cxMax' ] . 'px' ) : '' ) . ') {';

					$cyCur = 0;
					foreach( $aRow as $iCurRow => $row )
					{
						$cxAdaptedCur = 0;
						foreach( $row[ 'a' ] as $iCurCol => $col )
						{
							$itemStyleCont .= "\n" . $itemCssSel . ' .elementor-gallery__container:not([style*=container-aspect-ratio]) .e-gallery-item:nth-child(' . $col[ 'image' ] -> cssChildIdx . ') {
								--item-width: ' . ( ( float )$col[ 'cxAdapted' ] / $row[ 'cxAdapted' ] ) . ';
								--gap-count: ' . ( count( $row[ 'a' ] ) - 1 ) . ';
								--item-height: ' . ( ( float )$row[ 'cy' ] / ( $cyTotal ? $cyTotal : 1 ) ) . ';
								--item-start: ' . ( ( float )$cxAdaptedCur / $row[ 'cxAdapted' ] ) . ';
								--item-row-index: ' . $iCurCol . ';
								--item-top: ' . ( ( float )$cyCur / ( $cyTotal ? $cyTotal : 1 ) ) . ';
								--row: ' . $iCurRow . ';
							}';

							$cxAdaptedCur += $col[ 'cxAdapted' ];
						}

						$cyCur += $row[ 'cy' ];
					}

					$itemStyleCont .= "\n" . $itemCssSel . ' .elementor-gallery__container:not([style*=container-aspect-ratio]) {
						--container-aspect-ratio: ' . ( ( float )( $cyTotal  ) / $view[ 'widthAlign' ] ) . ';
						--hgap: ' . Gen::GetArrField( $viewGap, array( 'size' ), 0 ) . 'px;
						--vgap: ' . Gen::GetArrField( $viewGap, array( 'size' ), 0 ) . 'px;
						--rows: ' . count( $aRow ) . ';
					}';

					$itemStyleCont .= "\n" . '}';
				}

				$itemStyleCont .= "\n" . $itemCssSel . ' .e-gallery-justified:not([style*=container-aspect-ratio]) .e-gallery-item {
						height: calc(var(--item-height) * (100% - var(--vgap) * var(--rows)));
						top: calc(var(--item-top) * (100% - var(--vgap) * var(--rows)) + (var(--row) * var(--vgap)));
				}';

				$itemStyleCont .= "\n" . $itemCssSel . ' .e-gallery-justified:not([style*=container-aspect-ratio]) {
						padding-bottom: calc(var(--container-aspect-ratio) * 100% + var(--vgap) * var(--rows));
				}';
			}
			else if( $layout == 'grid' )
			{
				$aspect_ratio = explode( ':', Gen::GetArrField( $dataSett, array( 'aspect_ratio' ), '' ) );
				if( count( $aspect_ratio ) == 2 )
					$aspect_ratio = ( float )$aspect_ratio[ 1 ] / ( float )$aspect_ratio[ 0 ];

				foreach( array( array( 'type' => '_mobile', 'cxMax' => Gen::GetArrField( $ctx -> cfgElmntrFrontend, array( 'views', 'mobile', 'cxMax' ), 0 ) ), array( 'type' => '_tablet', 'cxMin' => Gen::GetArrField( $ctx -> cfgElmntrFrontend, array( 'views', 'tablet', 'cxMin' ), 0 ), 'cxMax' => Gen::GetArrField( $ctx -> cfgElmntrFrontend, array( 'views', 'tablet', 'cxMax' ), 0 ) ), array( 'type' => '', 'cxMin' => Gen::GetArrField( $ctx -> cfgElmntrFrontend, array( 'views', 'desktop', 'cxMin' ), 0 ) ) ) as $view )
				{
					$viewGap = Gen::GetArrField( $dataSett, array( 'gap' . $view[ 'type' ] ), array() );
					if( Gen::GetArrField( $viewGap, array( 'unit' ), '' ) != 'px' )
						continue;

					$nCols = Gen::GetArrField( $dataSett, array( 'columns' . $view[ 'type' ] ), 0 );

					$itemStyleCont .= "\n" . '@media (' . ( isset( $view[ 'cxMin' ] ) ? ( 'min-width: ' . $view[ 'cxMin' ] . 'px' ) : '' ) . ( isset( $view[ 'cxMin' ] ) && isset( $view[ 'cxMax' ] ) ? ') and (' : '' ) . ( isset( $view[ 'cxMax' ] ) ? ( 'max-width: ' . $view[ 'cxMax' ] . 'px' ) : '' ) . ') {';

					$itemStyleCont .= "\n" . $itemCssSel . ' .elementor-gallery__container:not([style*=container-aspect-ratio]) {
						--container-aspect-ratio: 100%;
						--aspect-ratio: ' . ( $aspect_ratio * 100 ) . '%;
						--hgap: ' . Gen::GetArrField( $viewGap, array( 'size' ), 0 ) . 'px;
						--vgap: ' . Gen::GetArrField( $viewGap, array( 'size' ), 0 ) . 'px;
						--columns: ' . $nCols . ';
						--rows: ' . ( int )ceil( ( float )count( $aImage ) / $nCols ) . ';
					}';

					$itemStyleCont .= "\n" . '}';
				}

				$itemStyleCont .= "\n" . $itemCssSel . ' .e-gallery-grid:not([style*=container-aspect-ratio]).e-gallery--animated .e-gallery-item {
					width: unset;
					height: unset;
					left: unset;
					top: unset;
					position: unset;
				}';

				$itemStyleCont .= "\n" . $itemCssSel . ' .e-gallery-grid:not([style*=container-aspect-ratio]).e-gallery--animated {
					display: grid;
					grid-gap: var(--vgap) var(--hgap);
					grid-template-columns: repeat(var(--columns), 1fr);
				}';
			}
			else if( $layout == 'masonry' )
			{
				foreach( array( array( 'type' => '_mobile', 'cxMax' => Gen::GetArrField( $ctx -> cfgElmntrFrontend, array( 'views', 'mobile', 'cxMax' ), 0 ) ), array( 'type' => '_tablet', 'cxMin' => Gen::GetArrField( $ctx -> cfgElmntrFrontend, array( 'views', 'tablet', 'cxMin' ), 0 ), 'cxMax' => Gen::GetArrField( $ctx -> cfgElmntrFrontend, array( 'views', 'tablet', 'cxMax' ), 0 ) ), array( 'type' => '', 'cxMin' => Gen::GetArrField( $ctx -> cfgElmntrFrontend, array( 'views', 'desktop', 'cxMin' ), 0 ) ) ) as $view )
				{
					$viewGap = Gen::GetArrField( $dataSett, array( 'gap' . $view[ 'type' ] ), array() );
					if( Gen::GetArrField( $viewGap, array( 'unit' ), '' ) != 'px' )
						continue;

					$nCols = Gen::GetArrField( $dataSett, array( 'columns' . $view[ 'type' ] ), 0 );

					$itemStyleCont .= "\n" . '@media (' . ( isset( $view[ 'cxMin' ] ) ? ( 'min-width: ' . $view[ 'cxMin' ] . 'px' ) : '' ) . ( isset( $view[ 'cxMin' ] ) && isset( $view[ 'cxMax' ] ) ? ') and (' : '' ) . ( isset( $view[ 'cxMax' ] ) ? ( 'max-width: ' . $view[ 'cxMax' ] . 'px' ) : '' ) . ') {';

					$aCol = array();
					for( $iCol = 0; $iCol < $nCols; $iCol++ )
						$aCol[ $iCol ] = array( 'a' => array(), 'cy' => 0 );

					$colDefWidth = 100;
					$iCol = 0;
					foreach( $aImage as $image )
					{

						$cy = ( int )round( $image -> sz -> cy * ( ( float )$colDefWidth / $image -> sz -> cx ) );
						$aCol[ $iCol ][ 'a' ][] = array( 'image' => $image, 'cy' => $cy, 'y' => $aCol[ $iCol ][ 'cy' ] );
						$aCol[ $iCol ][ 'cy' ] += $cy;

						$iCol++;
						if( $iCol == $nCols )
							$iCol = 0;
					}

					$cyTotal = 0;
					$nMaxGaps = 0;
					foreach( $aCol as $col )
					{
						if( $col[ 'cy' ] > $cyTotal )
						{
							$cyTotal = $col[ 'cy' ];
							$nMaxGaps = count( $col[ 'a' ] ) - 1;
						}
					}

					foreach( $aCol as $iCol => $col )
					{
						foreach( $col[ 'a' ] as $iRow => $row )
						{
							$itemStyleCont .= "\n" . $itemCssSel . ' .elementor-gallery__container:not([style*=highest-column-gap-count]) .e-gallery-item:nth-child(' . $row[ 'image' ] -> cssChildIdx . ') {
								--item-height: ' . ( ( float )$row[ 'image' ] -> sz -> cy / $row[ 'image' ] -> sz -> cx * 100 ) . '%;
								--item-height-ex: ' . ( ( float )$row[ 'cy' ] / ( $cyTotal ? $cyTotal : 1 ) ) . ';
								--column: ' . $iCol . ';
								--items-in-column:  ' . $iRow . ';
								--percent-height: ' . ( ( float )$row[ 'y' ] / ( $cyTotal ? $cyTotal : 1 ) * 100 ) . '%;
								--item-top: ' . ( ( float )$row[ 'y' ] / ( $cyTotal ? $cyTotal : 1 ) ) . ';
							}';
						}
					}

					$itemStyleCont .= "\n" . $itemCssSel . ' .elementor-gallery__container:not([style*=highest-column-gap-count]) {
						--hgap: ' . Gen::GetArrField( $viewGap, array( 'size' ), 0 ) . 'px;
						--vgap: ' . Gen::GetArrField( $viewGap, array( 'size' ), 0 ) . 'px;
						--columns: ' . $nCols . ';
						--highest-column-gap-count: ' . $nMaxGaps . ';
						padding-bottom: ' . ( ( float )$cyTotal / ( $nCols * $colDefWidth + ( $nCols - 1 ) * Gen::GetArrField( $viewGap, array( 'size' ), 0 ) ) * 100 ) . '%;
					}';

					$itemStyleCont .= "\n" . '}';
				}

				$itemStyleCont .= "\n" . $itemCssSel . ' .e-gallery-masonry:not([style*=highest-column-gap-count]) .e-gallery-item {
						height: calc(var(--item-height-ex) * (100% - var(--vgap) * var(--highest-column-gap-count)));
						top: calc(var(--item-top) * (100% - var(--vgap) * var(--highest-column-gap-count)) + (var(--items-in-column) * var(--vgap)));
				}';
			}

			if( $ctxProcess[ 'mode' ] == 'full' && $itemStyleCont )
			{
				$itemStyle = $doc -> createElement( 'style' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemStyle -> setAttribute( 'type', 'text/css' );
				HtmlNd::SetValFromContent( $itemStyle, $itemStyleCont );
				$item -> parentNode -> insertBefore( $itemStyle, $item );
			}
		}
	}

	if( Gen::GetArrField( $settCp, array( 'elmntrWdgtImgCrsl' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_elmntrWdgtImgCrsl( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'elmntrWdgtWooPrdImgs' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_elmntrWdgtWooPrdImgs( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'elmntrWdgtCntr' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_elmntrWdgtCntr( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'wooSctrCntDwnTmr' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		$adjusted = false;
		foreach( $xpath -> query( './/*[@class="woo-sctr-countdown-timer-wrap"]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			$adjusted = true;

			{
				$itemScript = $doc -> createElement( 'script' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemScript -> setAttribute( 'type', 'text/javascript' );
				$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
				HtmlNd::SetValFromContent( $itemScript, 'seraph_accel_cp_wooSctrCntDwnTmr_Init(document.currentScript.parentNode);' );
				$item -> appendChild( $itemScript );
			}
		}

		if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
		{

			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, "function seraph_accel_cp_wooSctrCntDwnTmr_Init(f){function g(){k=parseInt(h/60,10);l=parseInt(h%60,10);m.innerText=10>k?\"0\"+k:k;n.innerText=10>l?\"0\"+l:l}let h=function(b){var c=b.querySelector(\".woo-sctr-countdown-end-time\");b=Date.now();let e=c.dataset.countdown_time_end;if(!e)return 0;e=new Date(e.replace(\" \",\"T\")+\"Z\");e=e.valueOf();if(e>b)return Math.round((e-b)/1E3);var d=c.dataset.countdown_time_from;var a=c.dataset.countdown_time_to;c=c.dataset.countdown_time_reset;if(!d||!a)return 0;d=new Date(d.replace(\" \",\n\"T\")+\"Z\");d=d.valueOf();a=new Date(a.replace(\" \",\"T\")+\"Z\");a=a.valueOf();if(d===e&&a>b)return Math.round((a-b)/1E3);if(a===e&&0<=parseInt(c)){d=a-d;c=1E3*parseInt(c);if(a<b)return a=a+Math.floor((b-a)/(d+c))*(d+c)+c,a>b?b-a:Math.round((a+d-b)/1E3);a=b-e-c;return 0>a?b-a:Math.round((d-a)/1E3)}return 0}(f),k,l,m=f.querySelector(\".woo-sctr-countdown-minute-value\"),n=f.querySelector(\".woo-sctr-countdown-second-value\");g();let p=setInterval(()=>{g();0>--h&&clearInterval(p)},1E3)}\n(function(f){function g(){f.querySelectorAll(\".woo-sctr-countdown-timer-wrap\").forEach(function(h){seraph_accel_cp_wooSctrCntDwnTmr_Init(h)})}f.addEventListener(\"DOMContentLoaded\",g,{capture:!0,passive:!0});f.addEventListener(\"seraph_accel_freshPartsDone\",g,{capture:!0,passive:!0});seraph_accel_izrbpb.add(function(){f.removeEventListener(\"DOMContentLoaded\",g,{capture:!0,passive:!0})})})(document)" );
			$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
		}
	}

	if( Gen::GetArrField( $settCp, array( 'elmntrWdgtAvoShcs' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_elmntrWdgtAvoShcs( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'elmntrWdgtLott' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_elmntrWdgtLott( $ctx, $ctxProcess, $settCache, $settImg, $settCdn, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'elmntrWdgtPrmLott' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_elmntrWdgtPrmLott( $ctx, $ctxProcess, $settCache, $settImg, $settCdn, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'nktrLott' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_nktrLott( $ctx, $ctxProcess, $settCache, $settImg, $settCdn, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'elmntrStck' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_elmntrStck( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'elmntrShe' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_elmntrShe( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'elmntrStrtch' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_elmntrStrtch( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'elmsKitLott' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_elmsKitLott( $ctx, $ctxProcess, $settCache, $settImg, $settCdn, $doc, $xpath );
	}

	if( $ctxProcess[ 'mode' ] == 'full' && Gen::GetArrField( $settCp, array( 'prmmprssLzStls' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		if( $itemNoScr = HtmlNd::FirstOfChildren( $xpath -> query( './/body//script[@id="premiumpress-js"]/following-sibling::noscript[@id="deferred-styles"]' ) ) )
		{
			foreach( HtmlNd::ChildrenAsArr( $itemNoScr -> childNodes ) as $itemNoScrChild )
				$itemNoScr -> parentNode -> insertBefore( $itemNoScrChild, $itemNoScr );

			if( $itemScr = HtmlNd::FirstOfChildren( $xpath -> query( '(./following-sibling::script)[1][contains(text(),\'"deferred-styles"\')]', $itemNoScr ) ) )
				$itemScr -> parentNode -> removeChild( $itemScr );
			$itemNoScr -> parentNode -> removeChild( $itemNoScr );
		}
	}

	if( Gen::GetArrField( $settCp, array( 'mnmgImg' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," minimog-lazy-image ")]/img' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			HtmlNd::AddRemoveAttrClass( $item, array(), array( 'll-image' ) );
			if( HtmlNd::GetAttr( $item, 'data-src-retina' ) !== null )
			{
				$item -> removeAttribute( 'data-src' );
				HtmlNd::RenameAttr( $item, 'data-src-retina', 'src' );
			}
			else
				HtmlNd::RenameAttr( $item, 'data-src', 'src' );

			$itemCont = $item -> parentNode;
			$itemCont -> parentNode -> insertBefore( $item, $itemCont );
			$itemCont -> parentNode -> removeChild( $itemCont );
		}
	}

	if( Gen::GetArrField( $settCp, array( 'tldBgImg' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," t-bgimg ")][@data-original]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			HtmlNd::AddRemoveAttrClass( $item, array(), array( 't-bgimg' ) );
			$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( 'background-image' => 'url("' . $item -> getAttribute( 'data-original' ) . '")' ) ) ) );
			$item -> removeAttribute( 'data-original' );
		}
	}

	if( Gen::GetArrField( $settCp, array( 'sprflMenu' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		if( $itemScr = HtmlNd::FirstOfChildren( $xpath -> query( './/script[contains(text(),"var SFM_template")]' ) ) )
		{
			$aItem = array();
			if( preg_match( '@var\\s+SFM_template\\s*=\\s*(.+)@', $itemScr -> nodeValue, $aItem ) )
			{
				if( $aItem = @json_decode( $aItem[ 1 ], true ) )
				{
					if( $aItem = HtmlNd::ParseAndImportAll( $doc, $aItem ) )
					{
						HtmlNd::InsertBefore( $ctxProcess[ 'ndBody' ], $aItem, $ctxProcess[ 'ndBody' ] -> firstChild );

						$itemScr -> nodeValue = 'var SFM_template = ""';

						$ctxProcess[ 'aJsCrit' ][ 'body:@\\.SFM_is_mobile\\s*=@' ] = true;
						HtmlNd::AddRemoveAttrClass( $ctxProcess[ 'ndBody' ], array( 'superfly-on' ) );

						$ctxProcess[ 'aCssCrit' ][ '@\\.sfm-@' ] = true;
					}
				}
			}
		}
	}

	if( Gen::GetArrField( $settCp, array( 'jqVide' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_jqVide( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'jqSldNivo' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_jqSldNivo( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'sldWndr3dCrsl' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_sldWndr3dCrsl( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'sldRoyal' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_sldWndr3dCrsl( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'sldRev' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_sldRev( $ctx, $ctxProcess, $doc, $xpath, Gen::GetArrField( $settCp, array( 'sldRev_SmthLd' ), false ) );
	}

	if( Gen::GetArrField( $settCp, array( 'sldRev7' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_sldRev7( $ctx, $ctxProcess, $doc, $xpath );
	}

	if( Gen::GetArrField( $settCp, array( 'lottGen' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		_ProcessCont_Cp_lottGen( $ctx, $ctxProcess, $settCache, $settImg, $settCdn, $doc, $xpath );
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $ctx -> bBjs )
	{
		$itemScript = $doc -> createElement( 'script' );
		if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
			$itemScript -> setAttribute( 'type', 'text/javascript' );
		$itemScript -> setAttribute( 'seraph-accel-crit', '1' );

		HtmlNd::SetValFromContent( $itemScript, "seraph_accel_izrbpb.add(function(){for(var b=document.querySelectorAll(\"noscript[data-lzl-bjs]\"),a=0;a<b.length;a++){var c=b[a];c.outerHTML=c.textContent}},99)" );
		$ctxProcess[ 'ndBody' ] -> appendChild( $itemScript );
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $ctx -> aAniAppear )
	{
		$itemScript = $doc -> createElement( 'script' );
		if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
			$itemScript -> setAttribute( 'type', 'text/javascript' );
		$itemScript -> setAttribute( 'seraph-accel-crit', '1' );

		$itemScriptContSelectors = '{';
		foreach( $ctx -> aAniAppear as $selector => $func )
			$itemScriptContSelectors .= '"' . $selector . '":' . $func . ',';
		$itemScriptContSelectors .= '}';

		HtmlNd::SetValFromContent( $itemScript, str_replace( 'COMPILE_FAKE_SELECTORS_OBJECT', $itemScriptContSelectors, "(function(k,q){function r(b,f=!1){const {top:e,left:c,bottom:a,right:d}=b,{innerHeight:g,innerWidth:h}=q;return c==d||e==a?!1:f?0<=e&&0<=c&&a<=g&&d<=h:!(e>g||0>a)&&!(c>h||0>d)}function m(b){function f(d,g){function h(){for(;n<g.length;){var p=g[n++],l;r(p.rc)&&(l=d(p.e,t));if(l){setTimeout(h,l);break}}}var n=0;h()}b=COMPILE_FAKE_SELECTORS_OBJECT;var e=[],c;for(c in b){var a={cbElem:b[c],items:[]};k.querySelectorAll(c).forEach(function(d){a.items.push({e:d,rc:d.getBoundingClientRect()})});e.push(a)}e.forEach(function(d){f(d.cbElem,\nd.items)})}var t={GetDurationTime:function(b,f){\"string\"!==typeof b&&(b=\"\");for(var e=b.split(\",\"),c=b=0;c<e.length;c++){var a=e[c];a=-1!==a.lastIndexOf(\"ms\")?parseFloat(a):-1!==a.lastIndexOf(\"s\")?1E3*parseFloat(a):parseFloat(a);\"max\"==f&&b<a&&(b=a)}return b}};k.addEventListener(\"seraph_accel_calcSizes\",m,{capture:!0,passive:!0});seraph_accel_izrbpb.add(function(){k.removeEventListener(\"seraph_accel_calcSizes\",m,{capture:!0,passive:!0})})})(document,window)" ) );
		$ctxProcess[ 'ndBody' ] -> appendChild( $itemScript );
	}

	return( true );
}

function _Scrollsequence_GetFrontendCfg( $id, $itemInitScr )
{
	if( !$itemInitScr )
		return( null );

	$m = array();
	if( !preg_match( '@{\\s*"ssqId"\\s*:\\s*"' . $id . '"@m', $itemInitScr -> nodeValue, $m, PREG_OFFSET_CAPTURE ) )
		return( null );

	$posStart = $m[ 0 ][ 1 ];
	$pos = Gen::JsonGetEndPos( $posStart, $itemInitScr -> nodeValue );
	if( $pos === null )
		return( null );

	$prms = @json_decode( Gen::JsObjDecl2Json( substr( $itemInitScr -> nodeValue, $posStart, $pos - $posStart ) ), true );
	if( !$prms )
		return( null );

	return( $prms );
}

function _WoodmartPrcFlt_Price( $v, $wooPriceSliderPrms )
{
	if( !$wooPriceSliderPrms )
		return( $v );

	$v = number_format( ( float )$v, Gen::GetArrField( $wooPriceSliderPrms, array( 'currency_format_num_decimals' ), '' ), Gen::GetArrField( $wooPriceSliderPrms, array( 'currency_format_decimal_sep' ), '' ), Gen::GetArrField( $wooPriceSliderPrms, array( 'currency_format_thousand_sep' ), '' ) );
	return( sprintf( str_replace( array( '%s', '%v' ), array( '%1$s', '%2$s' ), Gen::GetArrField( $wooPriceSliderPrms, array( 'currency_format' ), '' ) ), Gen::GetArrField( $wooPriceSliderPrms, array( 'currency_format_symbol' ), '' ), $v ) );
}

function _Woo_PriceSlider( $doc, $item, $xpath, &$wooPriceSliderPrms, $bMainClass = true )
{
	if( $itemSlider = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," price_slider ")]', $item ) ) )
	{
		$itemSlider -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemSlider -> getAttribute( 'style' ) ), array( 'display' => null ) ) ) );
		_PriceSliderAdd( $doc, $itemSlider, $bMainClass );
	}

	if( $itemLabel = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," price_label ")]', $item ) ) )
	{
		if( $wooPriceSliderPrms === null )
		{
			$itemScr = HtmlNd::FirstOfChildren( $xpath -> query( './/script[@id="wc-price-slider-js-extra"]' ) );
			$wooPriceSliderPrms = array();
			if( preg_match( '@\\swoocommerce_price_slider_params\\s*=\\s*({[^{}]*})@', $itemScr -> nodeValue, $wooPriceSliderPrms ) )
				$wooPriceSliderPrms = @json_decode( Gen::JsObjDecl2Json( $wooPriceSliderPrms[ 1 ] ) );
			else
				$wooPriceSliderPrms = false;
		}

		$itemLabel -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemLabel -> getAttribute( 'style' ) ), array( 'display' => null ) ) ) );

		$itemPriceMin = HtmlNd::FirstOfChildren( $xpath -> query( './/*[@name="min_price"]', $item ) );
		if( $itemPriceMin && ( $itemLabelFrom = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," from ")]', $itemLabel ) ) ) )
		{
			$itemPriceMin -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemPriceMin -> getAttribute( 'style' ) ), array( 'display' => 'none' ) ) ) );
			$itemLabelFrom -> nodeValue = _WoodmartPrcFlt_Price( $itemPriceMin -> getAttribute( 'data-min' ), $wooPriceSliderPrms );
		}

		$itemPriceMax = HtmlNd::FirstOfChildren( $xpath -> query( './/*[@name="max_price"]', $item ) );
		if( $itemPriceMax && ( $itemLabelTo = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," to ")]', $itemLabel ) ) ) )
		{
			$itemPriceMax -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemPriceMax -> getAttribute( 'style' ) ), array( 'display' => 'none' ) ) ) );
			$itemLabelTo -> nodeValue = _WoodmartPrcFlt_Price( $itemPriceMax -> getAttribute( 'data-max' ), $wooPriceSliderPrms );
		}
	}
}

function _PriceSliderAdd( $doc, $itemSlider, $bMainClass = true )
{
	HtmlNd::AddRemoveAttrClass( $itemSlider, array( $bMainClass ? 'ui-slider' : null, 'ui-corner-all ui-slider-horizontal ui-widget ui-widget-content' ) );

	$itemSlider -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => 'ui-slider-range ui-corner-all ui-widget-header js-lzl-ing', 'style' => array( 'left' => '0%', 'width' => '100%' ) ), array() ) );
	$itemSlider -> appendChild( HtmlNd::CreateTag( $doc, 'span', array( 'class' => 'ui-slider-handle ui-corner-all ui-state-default', 'style' => array( 'left' => '0%' ), 'tabindex' => '0' ), array() ) );
	$itemSlider -> appendChild( HtmlNd::CreateTag( $doc, 'span', array( 'class' => 'ui-slider-handle ui-corner-all ui-state-default', 'style' => array( 'left' => '100%' ), 'tabindex' => '0' ), array() ) );
}

function _UiKit_GetSett( $ctxProcess, $xpath )
{
	$cont = _Cp_GetScriptContent( $ctxProcess, $xpath, './/script[contains(@src,"/assets/uikit/dist/js/uikit.")]' );
	if( !$cont )
		return( false );

	$contIcons = _Cp_GetScriptContent( $ctxProcess, $xpath, './/script[contains(@src,"/assets/uikit/dist/js/uikit-icons")]' );

	$cfgUiKit = array();

	foreach( array( 'navbar-toggle-icon', 'navbar-parent-icon' ) as $prm )
	{
		if( preg_match( '@\\"' . $prm . '\\"\\s*:\\s*([\\w]+)\\s*,@', $cont, $m ) )
			if( preg_match( '@\\s*,\\s*' . $m[ 1 ] . '\\s*=\\s*\'([^\']+)\'@', $cont, $m2 ) )
				$cfgUiKit[ 'js' ][ $prm ] = $m2[ 1 ];

		if( $contIcons )
			if( preg_match( '@\\"' . $prm . '\\"\\s*:\\s*\'([^\']+)\'\\s*,@', $contIcons, $m ) )
				$cfgUiKit[ 'js' ][ $prm ] = $m[ 1 ];
	}

	return( $cfgUiKit );
}

function _UiKit_ParseProps( string $props )
{
	return( Gen::ParseProps( $props, ';', ':' ) );
}

function _Cp_GetScrCont( $ctxProcess, $src )
{
	$srcInfo = GetSrcAttrInfo( $ctxProcess, null, null, $src );

	$cont = null;
	if( (isset($srcInfo[ 'filePath' ])?$srcInfo[ 'filePath' ]:null) )
		$cont = @file_get_contents( $srcInfo[ 'filePath' ] );
	if( !$cont )
		$cont = GetExtContents( (isset($srcInfo[ 'url' ])?$srcInfo[ 'url' ]:null), $contMimeType );
	return( $cont );
}

function _Cp_GetScriptContent( $ctxProcess, $xpath, $query )
{
	$itemScr = HtmlNd::FirstOfChildren( $xpath -> query( $query ) );
	if( !$itemScr )
		return( false );

	$src = $itemScr -> getAttribute( 'src' );
	$srcInfo = GetSrcAttrInfo( $ctxProcess, null, null, $src );
	if( !(isset($srcInfo[ 'filePath' ])?$srcInfo[ 'filePath' ]:null) )
		return( false );

	$cont = @file_get_contents( (isset($srcInfo[ 'filePath' ])?$srcInfo[ 'filePath' ]:null) );
	if( !$cont )
		return( false );

	return( $cont );
}

function _ProcessCont_Cp_diviMvImg( $ctx, &$ctxProcess, $doc, $xpath, &$adjusted, &$bDynSize )
{
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," et_pb_module ")]' ) as $itemContainer )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $itemContainer ) )
			continue;

		$itemClassId = _Divi_GetClassId( $itemContainer, array( 'et_pb_image', 'et_pb_menu' ) );
		if( $itemClassId === null )
			continue;

		$item = HtmlNd::FirstOfChildren( $xpath -> query( './/img[@data-et-multi-view]', $itemContainer ) );
		if( !$item )
			continue;

		$dataSett = @json_decode( $item -> getAttribute( 'data-et-multi-view' ), true );
		$views = Gen::GetArrField( $dataSett, array( 'schema', 'attrs' ), array() );
		if( !$views )
			continue;

		HtmlNd::AddRemoveAttrClass( $item, array(), array( 'et_multi_view_hidden_image' ) );

		foreach( $views as $viewId => $attrs )
		{
			if( !is_array( $attrs ) )
				continue;

			$itemContView = $viewId === 'desktop' ? $item : $item -> cloneNode( true );
			$itemContView -> setAttribute( 'data-et-multi-view-id', $viewId );

			foreach( $attrs as $attrKey => $attrVal )
				$itemContView -> setAttribute( $attrKey, $attrVal );

			$dataSettCopy = Gen::ArrCopy( $dataSett );
			Gen::SetArrField( $dataSettCopy, array( 'schema', 'attrs' ), array( $viewId => array() ) );
			$itemContView -> setAttribute( 'data-et-multi-view', @json_encode( $dataSettCopy ) );
			unset( $dataSettCopy );

			if( $item !== $itemContView )
				$item -> parentNode -> appendChild( $itemContView );
		}

		if( $itemStyleCont = _Divi_GetMultiViewStyle( $views, $itemClassId, false ) )
		{

			$itemStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemStyle, $itemStyleCont );
			$item -> parentNode -> insertBefore( $itemStyle, $item );
		}

		$adjusted = true;
	}
}

function _ProcessCont_Cp_diviMvText( $ctx, &$ctxProcess, $doc, $xpath, &$adjusted, &$bDynSize )
{
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," et_pb_module ")][contains(concat(" ",normalize-space(@class)," ")," et_pb_text ")]' ) as $itemContainer )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $itemContainer ) )
			continue;

		$itemClassId = _Divi_GetClassId( $itemContainer, 'et_pb_text' );
		if( $itemClassId === null )
			continue;

		$item = HtmlNd::FirstOfChildren( $xpath -> query( './/*[@data-et-multi-view]', $itemContainer ) );
		if( !$item )
			continue;

		$dataSett = @json_decode( $item -> getAttribute( 'data-et-multi-view' ), true );
		$views = Gen::GetArrField( $dataSett, array( 'schema', 'content' ), array() );
		if( !$views )
			continue;

		HtmlNd::CleanChildren( $item );

		foreach( $views as $viewId => $cont )
		{
			if( !is_string( $cont ) )
				continue;

			if( !( $itemContView = HtmlNd::ParseAndImport( $doc, Ui::Tag( 'div', $cont ) ) ) )
				continue;

			$itemContView -> setAttribute( 'data-et-multi-view-id', $viewId );
			$itemContView -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemContView -> getAttribute( 'style' ) ), array( 'display' => 'none' ) ) ) );
			$item -> appendChild( $itemContView );
		}

		if( $itemStyleCont = _Divi_GetMultiViewStyle( $views, $itemClassId, true ) )
		{
			$itemStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemStyle, $itemStyleCont );
			$item -> parentNode -> insertBefore( $itemStyle, $item );
		}

		$adjusted = true;
	}
}

function _ProcessCont_Cp_diviMvSld( $ctx, &$ctxProcess, $doc, $xpath, &$adjusted, &$bDynSize )
{
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," et_pb_module ")][contains(concat(" ",normalize-space(@class)," ")," et_pb_slider ")]' ) as $itemContainer )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $itemContainer ) )
			continue;

		$itemClassId = _Divi_GetClassId( $itemContainer, 'et_pb_slider' );
		if( $itemClassId === null )
			continue;

		$item = HtmlNd::FirstOfChildren( $xpath -> query( './/*[@data-et-multi-view]', $itemContainer ) );
		if( !$item )
			continue;

		$dataSett = @json_decode( $item -> getAttribute( 'data-et-multi-view' ), true );
		$views = Gen::GetArrField( $dataSett, array( 'schema', 'content' ), array() );
		if( !$views )
			continue;

		HtmlNd::CleanChildren( $item );

		foreach( $views as $viewId => $cont )
		{
			if( !is_string( $cont ) )
				continue;

			if( !( $itemContView = HtmlNd::ParseAndImport( $doc, Ui::Tag( 'div', $cont ) ) ) )
				continue;

			$itemContView -> setAttribute( 'data-et-multi-view-id', $viewId );
			$itemContView -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemContView -> getAttribute( 'style' ) ), array( 'display' => 'none' ) ) ) );
			$item -> appendChild( $itemContView );
		}

		if( $itemStyleCont = _Divi_GetMultiViewStyle( $views, $itemClassId, true ) )
		{
			$itemStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemStyle, $itemStyleCont );
			$item -> parentNode -> insertBefore( $itemStyle, $item );
		}

		$adjusted = true;
	}
}

function _ProcessCont_Cp_diviMvFwHdr( $ctx, &$ctxProcess, $doc, $xpath, &$adjusted, &$bDynSize )
{
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," et_pb_module ")][contains(concat(" ",normalize-space(@class)," ")," et_pb_fullwidth_header ")]' ) as $itemContainer )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $itemContainer ) )
			continue;

		$itemClassId = _Divi_GetClassId( $itemContainer, 'et_pb_fullwidth_header' );
		if( $itemClassId === null )
			continue;

		HtmlNd::AddRemoveAttrClass( $itemContainer, 'lzl_cs' );

		{
			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, 'seraph_accel_cp_divi_calcSizes(document.currentScript.parentNode);' );
			$itemContainer -> insertBefore( $itemScript, $itemContainer -> firstChild );
		}

		$adjusted = true;
		$bDynSize = true;
	}
}

function _ProcessCont_Cp_diviMv_Finalize( $ctx, &$ctxProcess, $doc, $xpath, $adjusted, $bDynSize )
{
	if( $adjusted )
	{
		if( stripos( $ctxProcess[ 'userAgent' ], 'mobile' ) !== false )
		{
			HtmlNd::AddRemoveAttrClass( $ctxProcess[ 'ndBody' ], array( 'et_mobile_device' ) );

		}
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $bDynSize )
	{
		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, '
					/* Full Width Header */
					.et_pb_module.et_pb_fullwidth_header.et_pb_fullscreen:not(.et_multi_view_swapped),
					.et_pb_module.et_pb_fullwidth_header.et_pb_fullscreen:not(.et_multi_view_swapped) .et_pb_fullwidth_header_container {
						min-height: calc(100vh - 1px*var(--lzl-corr-y));
					}
				' );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}

		{

			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, "function seraph_accel_cp_divi_calcSizes(a){try{var b=JSON.parse(a.getAttribute(\"data-et-multi-view\"))}catch(c){}b&&b.schema&&b.schema.classes&&(b=b.schema.classes[980<a.clientWidth?\"desktop\":767<a.clientWidth?\"tablet\":\"phone\"])&&(b.remove&&a.classList.remove.apply(a.classList,b.remove),b.add&&a.classList.add.apply(a.classList,b.add));a.style.setProperty(\"--lzl-corr-y\",a.getBoundingClientRect().y-a.ownerDocument.body.getBoundingClientRect().y)}\n(function(a){function b(c){a.querySelectorAll(\".et_pb_module.lzl_cs\").forEach(seraph_accel_cp_divi_calcSizes)}a.addEventListener(\"seraph_accel_calcSizes\",b,{capture:!0,passive:!0});seraph_accel_izrbpb.add(function(){a.removeEventListener(\"seraph_accel_calcSizes\",b,{capture:!0,passive:!0})})})(document)" );
			$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
		}
	}
}

function _ProcessCont_Cp_diviDsmGal( $ctx, &$ctxProcess, $doc, $xpath, &$adjusted, &$bDynSize )
{
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," dsm-gallery ")]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$aImage = array();
		$itemImgContainerIdx = -1;
		foreach( $xpath -> query( './*[contains(concat(" ",normalize-space(@class)," ")," grid-item ")]', $item ) as $itemImgContainer )
		{
			$itemImgContainerIdx++;

			$itemImg = HtmlNd::FirstOfChildren( $xpath -> query( './/img', $itemImgContainer ) );
			if( !$itemImg )
				continue;

			$aImage[] = ( object )array( 'nd' => $itemImgContainer, 'sz' => ( object )array( 'cx' => ( int )$itemImg -> getAttribute( 'width' ), 'cy' => ( int )$itemImg -> getAttribute( 'height' ) ), 'cssChildIdx' => $itemImgContainerIdx + 1 );
		}

		if( !$aImage )
			continue;

		$layout = 'masonry';

		if( $layout == 'masonry' )
		{
			$nCols = 3;
			$margin = 12;

			$aCol = array();
			for( $iCol = 0; $iCol < $nCols; $iCol++ )
				$aCol[ $iCol ] = array( 'a' => array(), 'cy' => 0 );

			$colDefWidth = 100;
			$iCol = 0;
			foreach( $aImage as $image )
			{

				$cy = $image -> sz -> cx ? ( int )round( ( $image -> sz -> cy ) * ( ( float )$colDefWidth / $image -> sz -> cx ) ) : 0;
				$aCol[ $iCol ][ 'a' ][] = array( 'image' => $image, 'y' => $aCol[ $iCol ][ 'cy' ] );
				$aCol[ $iCol ][ 'cy' ] += $cy;

				$iCol++;
				if( $iCol == $nCols )
					$iCol = 0;
			}

			$cyTotal = 0;
			foreach( $aCol as $col )
			{
				if( $col[ 'cy' ] > $cyTotal )
				{
					$cyTotal = $col[ 'cy' ];
				}
			}

			foreach( $aCol as $iCol => $col )
			{
				foreach( $col[ 'a' ] as $iRow => $row )
				{
					$row[ 'image' ] -> nd -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $row[ 'image' ] -> nd -> getAttribute( 'style' ) ), array( 'position' => 'absolute', 'left' => 'calc(' . ( ( float )$iCol * 100 / $nCols ) . '% + ' . ( $margin * $iCol / $nCols ) . 'px)', 'top' => ( ( float )$row[ 'y' ] * 100 / ( $cyTotal ? $cyTotal : 1 ) ) . '%' ) ) ) );
				}
			}

			$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( 'position' => 'relative', 'padding-bottom' => ( float )$cyTotal * 100 / ( $colDefWidth * $nCols ) . '%' ) ) ) );
		}
	}
}

function _ProcessCont_Cp_diviVidBox( $ctx, &$ctxProcess, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," et_pb_video_box ")]/iframe' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( '--width' => $item -> getAttribute( 'width' ), '--height' => $item -> getAttribute( 'height' ) ) ) ) );
		HtmlNd::RenameAttr( $item, 'src', 'data-lzl-src' );
		$adjusted = true;
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
	{
		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, '
					.et_pb_video_box > iframe
					{
						height: 0;
						padding-top: calc(var(--height) / var(--width) * 100%);
					}
				' );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}

		{
			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, '
					seraph_accel_izrbpb.add(
						function()
						{
							document.querySelectorAll( ".et_pb_video_box>iframe" ).forEach( function( i ){ i.src = i.getAttribute( "data-lzl-src" ) } );
						}
					);
				' );
			$ctxProcess[ 'ndBody' ] -> appendChild( $itemScript );
		}
	}
}

function _ProcessCont_Cp_diviVidBg( $ctx, &$ctxProcess, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," et_pb_section_video_bg ")]/video' ) as $item )
	{
		HtmlNd::AddRemoveAttrClass( $item -> parentNode, array( 'et_pb_section_video_bg_js_lzl' ), array( 'et_pb_section_video_bg' ) );
		$adjusted = true;
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
	{
		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, '
					.et_pb_section_video_bg_js_lzl video {
						width: 100%;
						object-fit: cover;
						height: 100%;
					}

					.et_pb_section_video_bg_js_lzl {
						position: absolute;
						top: 0;
						left: 0;
						width: 100%;
						height: 100%;
						overflow: hidden;
						display: block;
						pointer-events: none;
					}

					.iphone .et_pb_section_video_bg_js_lzl video::-webkit-media-controls-start-playback-button {
						display: none !important;
						-webkit-appearance: none;
					}

					.et_pb_column > .et_pb_section_video_bg_js_lzl {
						z-index: -1;
					}

					.et_pb_section_video_bg_js_lzl.et_pb_section_video_bg_hover, .et_pb_section_video_bg_js_lzl.et_pb_section_video_bg_phone, .et_pb_section_video_bg_js_lzl.et_pb_section_video_bg_tablet, .et_pb_section_video_bg_js_lzl.et_pb_section_video_bg_tablet_only {
						display: none;
					}

					.et_pb_section_video_on_hover:hover > .et_pb_section_video_bg_js_lzl {
						display: none;
					}

					@media (min-width: ' . ( 980 + 1 ) . 'px) {
						.et_pb_section_video_bg_js_lzl.et_pb_section_video_bg_desktop_only {
							display: block;
						}
					}

					@media (max-width: ' . ( 980 ) . 'px) {
						.et_pb_section_video_bg_js_lzl.et_pb_section_video_bg_tablet {
							display: block;
						}

						.et_pb_section_video_bg_js_lzl.et_pb_section_video_bg_desktop_only {
							display: none;
						}
					}

					@media (min-width: ' . ( 767 + 1 ) . 'px) {
						.et_pb_section_video_bg_js_lzl.et_pb_section_video_bg_desktop_tablet {
							display: block;
						}
					}

					@media (min-width: ' . ( 767 + 1 ) . 'px) and (max-width:' . ( 980 ) . 'px) {
						.et_pb_section_video_bg_js_lzl.et_pb_section_video_bg_tablet_only {
							display: block;
						}
					}

					@media (max-width: ' . ( 767 ) . 'px) {
						.et_pb_section_video_bg_js_lzl.et_pb_section_video_bg_phone {
							display: block;
						}

						.et_pb_section_video_bg_js_lzl.et_pb_section_video_bg_desktop_tablet {
							display: none;
						}
					}
				' );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}
	}
}

function _ProcessCont_Cp_diviVidFr( $ctx, &$ctxProcess, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," et_pb_module ")]//*[not(self::node()[contains(concat(" ",normalize-space(@class)," ")," et_pb_video_box ")])]//iframe' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$size = ( int )$item -> getAttribute( 'width' );
		if( !$size )
			continue;

		if( in_array( 'et_pb_video_box', HtmlNd::GetAttrClass( $item -> parentNode ) ) )
			continue;

		$size = ( int )$item -> getAttribute( 'height' ) / $size;

		$itemWrapper = HtmlNd::CreateTag( $doc, 'div', array( 'class' => array( 'fluid-width-video-wrapper' ), 'style' => array( 'padding-top' => ( string )( $size * 100 ) . '%' ) ) );
		$item -> parentNode -> insertBefore( $itemWrapper, $item );
		$itemWrapper -> appendChild( $item );
	}
}

function _ProcessCont_Cp_diviLzStls( $ctx, &$ctxProcess, $doc, $xpath )
{
	if( $itemScr = HtmlNd::FirstOfChildren( $xpath -> query( './/body//script[contains(text(),"/et-divi-dynamic-")]' ) ) )
	{
		$styleInsertAfterId = '';
		if( preg_match( '@\\Wdocument\\s*\\.\\s*getElementById\\s*\\(\\s*[\'"]([^\'"]+)[\'"]@', $itemScr -> nodeValue, $m ) )
			$styleInsertAfterId = $m[ 1 ];
		$styleLazyId = '';
		if( preg_match( '@\\Wlink\\s*\\.\\s*id\\s*=\\s*[\'"]([^\'"]+)[\'"]@', $itemScr -> nodeValue, $m ) )
			$styleLazyId = $m[ 1 ];
		$styleLazyHref = '';
		if( preg_match( '@\\Wvar\\s*file\\s*=\\s*\\[\\s*[\'"]([^\'"]+)[\'"]@', $itemScr -> nodeValue, $m ) )
			$styleLazyHref = str_replace( '\\/', '/', $m[ 1 ] );

		if( $itemStyleInsertAfter = HtmlNd::FirstOfChildren( $xpath -> query( './/style[@id="' . $styleInsertAfterId . '"]' ) ) )
		{
			$itemStyleLazy = $doc -> createElement( 'link' );
			$itemStyleLazy -> setAttribute( 'rel', 'stylesheet' );
			$itemStyleLazy -> setAttribute( 'id', $styleLazyId );
			$itemStyleLazy -> setAttribute( 'href', $styleLazyHref );
			HtmlNd::InsertAfter( $itemStyleInsertAfter -> parentNode, $itemStyleLazy, $itemStyleInsertAfter );
			$itemScr -> parentNode -> removeChild( $itemScr );
		}

		unset( $itemScr );
	}
}

function _ProcessCont_Cp_diviAni( $ctx, &$ctxProcess, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," et_pb_animation")]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		HtmlNd::AddRemoveAttrClass( $item, array( 'et_pb_animation' ) );
		$adjusted = true;
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.et-animated@' ] = true;

		{

			$ctx -> aAniAppear[ '.et_pb_animation:not(.et-animated)' ] = 'function(a){a.classList.add("et-animated")}';
		}
	}
}

function _ProcessCont_Cp_diviDataAni( $ctx, &$ctxProcess, $doc, $xpath )
{
	$itemScrCfg = HtmlNd::FirstOfChildren( $xpath -> query( './/body//script[contains(text(),"et_animation_data")]' ) );
	if( !$itemScrCfg )
		return;

	@preg_match( '@var\\s+et_animation_data\\s+=\\s+(\\[.*?\\])@', $itemScrCfg -> nodeValue, $m );
	if( !$m )
		return;

	$cfg = @json_decode( $m[ 1 ], true );
	if( !$cfg )
		return;

	$contStyle = '';

	$adjusted = false;
	foreach( $cfg as $cfgI )
	{
		if( empty( $cfgI[ 'class' ] ) || empty( $cfgI[ 'style' ] ) || empty( $cfgI[ 'repeat' ] ) || empty( $cfgI[ 'duration'] ) || empty( $cfgI[ 'delay' ] ) || empty( $cfgI[ 'intensity' ] ) || empty( $cfgI[ 'starting_opacity' ] ) || empty( $cfgI[ 'speed_curve' ] ) )
			continue;

		$cfgI[ 'starting_opacity' ] = intval( $cfgI[ 'starting_opacity' ] ) / 100;
		$delay = intval( $cfgI[ 'duration' ] ) + intval( $cfgI[ 'delay' ] );

		$adjustedI = false;
		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," ' . $cfgI[ 'class' ] . ' ")]' ) as $item )
		{
			if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
				continue;

			HtmlNd::AddRemoveAttrClass( $item, array( 'dani-lzl' ) );
			$item -> setAttribute( 'data-dani-lzl-dur', $delay );
			$adjustedI = true;
		}

		if( !$adjustedI )
			continue;

		$adjusted = true;

    	$contStyle .= '.' . $cfgI[ 'class' ] . '.ing { animation-name: ' . $cfgI[ 'class' ] . '-dani-lzl; animation-duration:  ' . $cfgI[ 'duration' ] . '; animation-delay: ' . $cfgI[ 'delay' ] . '; animation-timing-function: ' . $cfgI[ 'speed_curve' ] . '; }
    	@keyframes ' . $cfgI[ 'class' ] . '-dani-lzl { 0% { transform: ';

		$i = 'none';
		$n = intval( $cfgI[ 'intensity' ] );
		preg_match( '@(slide|zoom|flip|fold|roll|fade|bounce)(top|bottom|right|left|)@i', strtolower( $cfgI[ 'style' ] ), $style );

		switch( @$style[ 1 ] )
		{
		case "slide":
		    switch( @$style[ 2 ] )
		    {
		    case "top":
		        $i = "translate3d(0, " . ( -2 * $n ) . "%, 0)";
		        break;
		    case "right":
		        $i = "translate3d(" . ( 2 * $n ) . "%, 0, 0)";
		        break;
		    case "bottom":
		        $i = "translate3d(0, " . ( 2 * $n ) . "%, 0)";
		        break;
		    case "left":
		        $i = "translate3d(" . ( -2 * $n ) . "%, 0, 0)";
		        break;
		    default:
		        $a = .01 * ( 100 - $n );
		        $i = "scale3d(" . $a . ", " . $a . ", " . $a . ")";
		        break;
		    }
		    break;

		case "zoom":
		    $a = .01 * ( 100 - $n );
		    $i = "scale3d(" . $a . ", " . $a . ", " . $a . ")";
		    break;

		case "flip":
		    switch ( @$style[ 2 ] )
		    {
		    case "right":
		        $o = ceil( .9 * $n );
		        $i = "perspective(2000px) rotateY(" . $o . "deg)";
		        break;
		    case "left":
		        $o = -1 * ceil(.9 * $n);
		        $i = "perspective(2000px) rotateY(" . $o . "deg)";
		        break;
		    case "bottom":
		        $o = -1 * ceil(.9 * $n);
		        $i = "perspective(2000px) rotateX(" . $o . "deg)";
		        break;
		    case "top":
		    default:
		        $o = ceil(.9 * $n);
		        $i = "perspective(2000px) rotateX(" . $o . "deg)";
		        break;
		    }
		    break;

		case "fold":
		    switch ( @$style[ 2 ] )
			{
		    case "top":
		        $o = -1 * ceil( .9 * $n );
		        $i = "perspective(2000px) rotateX(" . $o . "deg)";
		        break;
		    case "bottom":
		        $o = ceil(.9 * $n);
		        $i = "perspective(2000px) rotateX(" . $o . "deg)";
		        break;
		    case "left":
		        $o = ceil(.9 * $n);
		        $i = "perspective(2000px) rotateY(" . $o . "deg)";
		        break;
		    default:
		        $o = -1 * ceil(.9 * $n);
		        $i = "perspective(2000px) rotateY(" . $o . "deg)";
		        break;
		    }
		    break;

		case "roll":
		    switch ( @$style[ 2 ] )
			{
		    case "right":
		    case "bottom":
		        $o = -1 * ceil( 3.6 * $n );
		        $i = "rotateZ(" . $o . "deg)";
		        break;
		    case "top":
		    case "left":
		    default:
		        $o = ceil( 3.6 * $n );
		        $i = "rotateZ(" . $o . "deg)";
		        break;
		    }

		default:
			$i = "none";
			break;
		}

    	$contStyle .= $i . '; opacity: ' . $cfgI[ 'starting_opacity' ] . ';} 100% { transform: none; opacity: 1;} }';
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.ing(?:\\W|$)@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.ed(?:\\W|$)@' ] = true;

		if( $contStyle )
		{
			$contStyle .= '.dani-lzl.ed, .lzl-sticky .dani-lzl { animation: none !important; transform: none !important; opacity: 1 !important; }';

			$itemStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemStyle, $contStyle );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemStyle );
		}

		{

			$ctx -> aAniAppear[ '.dani-lzl:not(.ing,.ed)' ] = 'function(a){a.classList.add("ing");setInterval(function(){a.classList.add("ed");a.classList.remove("ing")},parseInt(a.getAttribute("data-dani-lzl-dur"),10))}';
		}
	}
}

function _ProcessCont_Cp_diviStck( $ctx, &$ctxProcess, $doc, $xpath )
{
	if( !HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," et_pb_sticky_module ")]' ) ) )
		return;

	$itemScrCfg = HtmlNd::FirstOfChildren( $xpath -> query( './/body//script[contains(text(),"et_pb_sticky_elements")]' ) );
	if( !$itemScrCfg )
		return;

	$posStart = array();
	if( !preg_match( '@var\\s+et_pb_sticky_elements\\s*=\\s*{@', $itemScrCfg -> nodeValue, $posStart, PREG_OFFSET_CAPTURE ) )
		return;

	$posStart = $posStart[ 0 ][ 1 ] + strlen( $posStart[ 0 ][ 0 ] ) - 1;
	$pos = Gen::JsonGetEndPos( $posStart, $itemScrCfg -> nodeValue );
	if( $pos === null )
		return;

	$cfg = @json_decode( Gen::JsObjDecl2Json( substr( $itemScrCfg -> nodeValue, $posStart, $pos - $posStart ) ), true );
	if( $cfg === null )
		return;

	$adjusted = false;
	foreach( Gen::GetArrField( $cfg, array( '' ), array() ) as $id => $cfgItem )
	{
		if( !$ctx -> cnvCssSel2Xpath )
			$ctx -> cnvCssSel2Xpath = StyleProcessor::createCnvCssSel2Xpath();

		$selItem = StyleProcessor::cssSelToXPathEx( $ctx -> cnvCssSel2Xpath, Gen::GetArrField( $cfgItem, array( 'selector' ), '' ) );
		if( !$selItem )
			continue;

		$item = HtmlNd::FirstOfChildren( $xpath -> query( $selItem ) );
		if( !$item )
			continue;

		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$item -> setAttribute( 'data-lzl-stck', $id );

		$itemSticky = $item -> cloneNode( true );
		for( $itemStickyChild = null; $itemStickyChild = HtmlNd::GetNextTreeChild( $itemSticky, $itemStickyChild, true ); )
		{
			if( $itemStickyChild -> nodeType != XML_ELEMENT_NODE )
				continue;

			$itemStickyChild -> removeAttribute( 'data-order_class' );
			if( $itemStickyChild -> hasAttribute( 'id' ) )
				$itemStickyChild -> setAttribute( 'id', $itemStickyChild -> getAttribute( 'id' ) . '-lzl' );
		}
		HtmlNd::AddRemoveAttrClass( $itemSticky, array( 'js-lzl-ing' ) );
		HtmlNd::InsertBefore( $item -> parentNode, $itemSticky, $item );

		Gen::SetArrField( $cfg, array( $id, 'selector' ), Gen::GetArrField( $cfgItem, array( 'selector' ), '' ) . ':not(.js-lzl-ing)' );

		$adjusted = true;
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
	{
		$itemScrCfg -> nodeValue = substr_replace( $itemScrCfg -> nodeValue, @json_encode( $cfg ), $posStart, $pos - $posStart );

		$ctxProcess[ 'aCssCrit' ][ '@\\.et_pb_sticky@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.lzl-sticky@' ] = true;

		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, '[data-lzl-stck].js-lzl-ing.lzl-sticky {
	position: fixed;
	width: 100%;
	margin-top: 0px;
	margin-bottom: 0px;
	top: 0px;
	z-index: 99;
}

[data-lzl-stck].js-lzl-ing:not(.lzl-sticky),
body:not(.seraph-accel-js-lzl-ing) [data-lzl-stck].js-lzl-ing {
	display: none !important;
}

body.seraph-accel-js-lzl-ing [data-lzl-stck]:not(.js-lzl-ing).lzl-sticky {
	visibility: hidden !important;
}' );

			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}

		{

			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, "function seraph_accel_cp_diviStck_calcSizes(a){a.classList.contains(\"et_pb_sticky_placeholder\")||function(b,e=!0){var d=et_pb_sticky_elements[b.getAttribute(\"data-lzl-stck\")];if(d){var c=b.previousElementSibling;c&&c.classList.contains(\"js-lzl-ing\")&&(e?b.classList.contains(\"lzl-sticky\")||(b.classList.add(\"lzl-sticky\"),c.classList.add(\"lzl-sticky\"),c.classList.add(\"et_pb_sticky\"),c.classList.add(\"et_pb_sticky--\"+String(d.position))):b.classList.contains(\"lzl-sticky\")&&(b.classList.remove(\"lzl-sticky\"),\nc.classList.remove(\"lzl-sticky\"),c.classList.remove(\"et_pb_sticky\"),c.classList.remove(\"et_pb_sticky--\"+String(d.position))))}}(a,0>a.getBoundingClientRect().top)}\n(function(a){function b(e){a.querySelectorAll(\"[data-lzl-stck]:not(.js-lzl-ing)\").forEach(function(d){seraph_accel_cp_diviStck_calcSizes(d)})}a.addEventListener(\"seraph_accel_calcSizes\",b,{capture:!0,passive:!0});a.addEventListener(\"scroll\",b,{capture:!0,passive:!0});a.addEventListener(\"seraph_accel_jsFinish\",function(e){a.querySelectorAll(\"[data-lzl-stck].js-lzl-ing\").forEach(function(d){d.remove()});a.removeEventListener(\"seraph_accel_calcSizes\",b,{capture:!0,passive:!0});a.removeEventListener(\"scroll\",\nb,{capture:!0,passive:!0})},{capture:!0,passive:!0})})(document)" );
			$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
		}

		{
			$itemScrCfg -> setAttribute( 'seraph-accel-crit', '1' );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemScrCfg );
		}
	}
}

function _ProcessCont_Cp_diviPrld( $ctx, &$ctxProcess, $doc, $xpath )
{

	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," et_pb_preload")]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		HtmlNd::AddRemoveAttrClass( $item, array(), array( 'et_pb_preload' ) );
	}
}

function _ProcessCont_Cp_diviHdr( $ctx, &$ctxProcess, $doc, $xpath )
{
	if( $ctxProcess[ 'mode' ] == 'full' && ( $item = HtmlNd::FirstOfChildren( $xpath -> query( './/body[contains(concat(" ",normalize-space(@class)," ")," et_divi_theme ")][contains(concat(" ",normalize-space(@class)," ")," et_fixed_nav ")]//*[@id="main-header"]' ) ) ) )
	{
		{
			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, 'seraph_accel_cp_diviHdr_calcSizes(document);' );
			$item -> appendChild( $itemScript );
		}

		{

		}

		{

			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, "function seraph_accel_cp_diviHdr_calcSizes(a){var b=a.querySelector(\"#main-header\"),c=a.querySelector(\"#top-header\");a=a.querySelector(\"#page-container\");b.style.setProperty(\"top\",(c?c.clientHeight:0)+\"px\");a&&a.style.setProperty(\"padding-top\",b.clientHeight-b.clientHeight+(c?c.clientHeight:0)+b.clientHeight+\"px\")}\n(function(a){function b(c){seraph_accel_cp_diviHdr_calcSizes(a)}a.addEventListener(\"seraph_accel_calcSizes\",b,{capture:!0,passive:!0});seraph_accel_izrbpb.add(function(){a.removeEventListener(\"seraph_accel_calcSizes\",b,{capture:!0,passive:!0})})})(document)" );
			$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
		}
	}
}

function _Divi_GetClassId( $item, $aClassType )
{
	$classes = $item -> getAttribute( 'class' );
	if( !is_string( $classes ) )
		return( null );

	$classes = ' ' . $classes . ' ';

	$found = null;
	foreach( ( array )$aClassType as $classType )
	{
		$m = array();
		if( !@preg_match( '@\\s(' . $classType . '_\\d+[^\\s]*)\\s@', $classes, $m ) )
			continue;

		$found = $m[ 1 ];
		break;
	}

	return( $found );
}

function _Divi_GetMultiViewStyle( $views, $itemClassId, $full )
{
	$ctx = new AnyObj();
	$ctx -> itemClassId = $itemClassId;
	$ctx -> full = $full;
	$ctx -> cb =
		function( $ctx, $views, $viewId )
		{
			$res = '.et_pb_module.' . $ctx -> itemClassId;
			if( $ctx -> full )
				return( $res . ' [data-et-multi-view]:not(.et_multi_view_swapped), .et_pb_module.' . $ctx -> itemClassId . ' [data-et-multi-view]:not(.et_multi_view_swapped) > [data-et-multi-view-id="' . $viewId . '"]{ display:unset!important; }' );
			return( $res . ' [data-et-multi-view-id]:not([data-et-multi-view-id="' . $viewId . '"]){ display:none!important; }' );
		};

	return( _Divi_GetMultiViewStyleEx( $views, array( $ctx, 'cb' ) ) );
}

function _Divi_GetMultiViewStyleEx( $views, $cbStyle )
{
	static $g_aEtPbMaxSizes = array( 'phone' => 767, 'tablet' => 980 );

	$itemStyleCont = '';
	if( isset( $views[ 'phone' ] ) && isset( $views[ 'tablet' ] ) && isset( $views[ 'desktop' ] ) )
	{
		$itemStyleCont = '
			@media (max-width: ' . $g_aEtPbMaxSizes[ 'phone' ] . 'px)
			{
				' . call_user_func( $cbStyle, $views, 'phone' ) . '
			}

			@media (min-width: ' . ( $g_aEtPbMaxSizes[ 'phone' ] + 1 ) . 'px) and (max-width: ' . $g_aEtPbMaxSizes[ 'tablet' ] . 'px)
			{
				' . call_user_func( $cbStyle, $views, 'tablet' ) . '
			}

			@media (min-width: ' . ( $g_aEtPbMaxSizes[ 'tablet' ] + 1 ) . 'px)
			{
				' . call_user_func( $cbStyle, $views, 'desktop' ) . '
			}
		';
	}
	else if( isset( $views[ 'phone' ] ) && isset( $views[ 'desktop' ] ) )
	{
		$itemStyleCont = '
			@media (max-width: ' . $g_aEtPbMaxSizes[ 'phone' ] . 'px)
			{
				' . call_user_func( $cbStyle, $views, 'phone' ) . '
			}

			@media (min-width: ' . ( $g_aEtPbMaxSizes[ 'phone' ] + 1 ) . 'px)
			{
				' . call_user_func( $cbStyle, $views, 'desktop' ) . '
			}
		';
	}
	else if( isset( $views[ 'tablet' ] ) && isset( $views[ 'desktop' ] ) )
	{
		$itemStyleCont = '
			@media (max-width: ' . $g_aEtPbMaxSizes[ 'tablet' ] . 'px)
			{
				' . call_user_func( $cbStyle, $views, 'tablet' ) . '
			}

			@media (min-width: ' . ( $g_aEtPbMaxSizes[ 'tablet' ] + 1 ) . 'px)
			{
				' . call_user_func( $cbStyle, $views, 'desktop' ) . '
			}
		';
	}

	return( $itemStyleCont );
}

function _ProcessCont_Cp_sldN2Ss( $ctx, &$ctxProcess, $doc, $xpath )
{
	$itemInitCmnScr = null;

	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," n2-section-smartslider ")]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$tplApplied = false;
		foreach( $xpath -> query( './/template[@data-loading-type]', $item ) as $itemTpl )
		{
			HtmlNd::MoveChildren( $itemTpl -> parentNode, $itemTpl );
			$itemTpl -> parentNode -> removeChild( $itemTpl );
			$tplApplied = true;
		}

		if( $tplApplied )
			$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( 'height' => null ) ) ) );
	}

	$bRtScript = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," n2-ss-slider ")]' ) as $itemSld )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $itemSld ) )
			continue;

		if( !$itemInitCmnScr )
			$itemInitCmnScr = HtmlNd::FirstOfChildren( $xpath -> query( './/script[contains(text(),"_N2.r(")]' ) );

		$cfg = _ProcessCont_Cp_sldN2Ss_GetMeta( $itemSld -> getAttribute( 'id' ), $itemInitCmnScr );

		if( $itemBulletTpl = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," n2-bullet ")][1]', $itemSld ) ) )
		{
			$itemBulletTpl -> removeAttribute( 'style' );

			$i = 0;
			foreach( $xpath -> query( './/*[@data-slide-public-id]', $itemSld ) as $item )
			{
				$itemBullet = $itemBulletTpl -> cloneNode( true );
				$itemBulletCont = $doc -> createElement( 'div' );
				$itemBulletCont -> appendChild( $itemBullet );
				$itemBulletTpl -> parentNode -> appendChild( $itemBulletCont );

				if( $i === 0 )
					HtmlNd::AddRemoveAttrClass( $itemBullet, array( 'n2-active' ) );

				$i++;
			}

			$itemBulletTpl -> parentNode -> removeChild( $itemBulletTpl );
		}

		$idFirstSlide = '1';
		{
			$itemFirstSlide = HtmlNd::FirstOfChildren( $xpath -> query( './/*[@data-slide-public-id][@data-first="1"]', $itemSld ) );
			if( $itemFirstSlide )
			{
				$idFirstSlide = $itemFirstSlide -> getAttribute( 'data-slide-public-id' );
				$itemFirstSlide -> setAttribute( 'data-lzl-first', '1' );
			}
			else if( $itemFirstSlide = HtmlNd::FirstOfChildren( $xpath -> query( './/*[@data-slide-public-id="1"]', $itemSld ) ) )
				$itemFirstSlide -> setAttribute( 'data-lzl-first', '1' );
		}

		if( $itemShowcaseCont = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," n2-ss-showcase-slides ")]', $itemSld ) ) )
			HtmlNd::AddRemoveAttrClass( $itemShowcaseCont, array( 'n2-ss-showcase-slides--ready' ) );

		if( $itemFirstBg = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," n2-ss-slide-backgrounds ")]//*[@data-public-id="' . $idFirstSlide . '"]', $itemSld ) ) )
		{
			$itemFirstBg -> setAttribute( 'data-lzl-first', '1' );
			if( $itemFirstBgVideo = HtmlNd::FirstOfChildren( $xpath -> query( './/video[contains(concat(" ",normalize-space(@class)," ")," n2-ss-slide-background-video ")]', $itemFirstBg ) ) )
			{
				$itemFirstBgVideo -> setAttribute( 'preload', '1' );
				$itemFirstBgVideo -> setAttribute( 'autoplay', '1' );
			}
		}

		$bResponsive = false;
		$items = HtmlNd::ChildrenAsArr( $xpath -> query( './/*[@data-slide-public-id="' . $idFirstSlide . '"]//*[contains(concat(" ",normalize-space(@class)," ")," n2-ss-layer ")][contains(concat(" ",normalize-space(@class)," ")," n-uc-")]', $itemSld ) );

		$itemsNeedClone = array();
		foreach( $items as $item )
		{
			$idParent = $item -> getAttribute( 'data-parentid' );
			if( !$idParent )
				continue;

			$itemParent = HtmlNd::FirstOfChildren( $xpath -> query( './/*[@id="' . $idParent . '"]', $itemSld ) );
			if( !$itemParent || $itemParent -> parentNode !== $item -> parentNode )
				continue;

			$itemsNeedClone[] = $itemParent;
			$itemsNeedClone[] = $item;
		}

		$fnGetClone = function( $fnGetClone, $xpath, $itemSld, $item )
		{
			$idParent = $item -> getAttribute( 'data-parentid' );
			if( $idParent )
			{
				$itemParent = HtmlNd::FirstOfChildren( $xpath -> query( './/*[@data-id-ex="' . $idParent . '"]', $itemSld ) );
				if( !$itemParent )
					if( $itemParent = HtmlNd::FirstOfChildren( $xpath -> query( './/*[@id="' . $idParent . '"]', $itemSld ) ) )
						$itemParent = $fnGetClone( $fnGetClone, $xpath, $itemSld, $itemParent );
					else
						$itemParent = $item -> parentNode;
			}
			else
				$itemParent = $item -> parentNode;

			$id = $item -> getAttribute( 'id' );
			if( $id )
				if( $itemClone = HtmlNd::FirstOfChildren( $xpath -> query( './/*[@data-id-ex="' . $id . '"]', $itemSld ) ) )
					return( $itemClone );

			HtmlNd::AddRemoveAttrClass( $item, 'js-lzl-n-ing' );
			$itemClone = $item -> cloneNode( true );
			$itemParent -> appendChild( $itemClone );
			HtmlNd::AddRemoveAttrClass( $itemClone, 'js-lzl-ing', 'js-lzl-n-ing' );
			HtmlNd::RenameAttr( $itemClone, 'id', 'data-id-ex' );
			HtmlNd::RenameAttr( $itemClone, 'data-parentid', 'data-parentid-ex' );
			return( $itemClone );
		};

		foreach( $items as $item )
		{
			$layerSelectorEx = '';
			if( in_array( $item, $itemsNeedClone, true ) )
			{
				$item = $fnGetClone( $fnGetClone, $xpath, $itemSld, $item );
				$layerSelectorEx = '.js-lzl-ing';
			}

			$layerSelectorUnique = '';
			foreach( Ui::ParseClassAttr( $item -> getAttribute( 'class' ) ) as $class )
				if( Gen::StrStartsWith( $class, 'n-uc-' ) )
				{
					$layerSelectorUnique = '.' . $class;
					break;
				}

			$rotation = $item -> getAttribute( 'data-rotation' );
			$responsiveposition = $item -> getAttribute( 'data-responsiveposition' );
			$responsivesize = $item -> getAttribute( 'data-responsivesize' );
			$bHasParent = !!$item -> getAttribute( 'data-parentid-ex' );

			if( $responsiveposition || $responsivesize )
				$bResponsive = true;

			{
				$style = Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) );

				if( $itemSld -> getAttribute( 'data-ss-legacy-font-scale' ) && $item -> getAttribute( 'data-sstype' ) == 'layer' )
				{
					$style[ 'font-size' ] = $bHasParent ? '100%' : 'calc(100%*var(--ss-responsive-scale)*var(--ssfont-scale))';
				}

				if( $style )
					$item -> setAttribute( 'style', Ui::GetStyleAttr( $style ) );
			}

			$stylesSeparated = array( 'desktop' => array(), 'tablet' => array(), 'mobile' => array() );

			foreach( $stylesSeparated as $view => &$styleSeparated )
			{
				if( ( $v = $item -> getAttribute( 'data-' . $view . 'portraitwidth' ) ) !== null )
					$styleSeparated[ 'width' ] = is_numeric( $v ) ? ( 'calc(' . $v . 'px' . ( $responsivesize ? ' * var(--ss-responsive-scale))' : '' ) ) : ( $v == 'auto' ? '100%' : $v );
				if( ( $v = $item -> getAttribute( 'data-' . $view . 'portraitheight' ) ) !== null )
					$styleSeparated[ 'height' ] = is_numeric( $v ) ? ( 'calc(' . $v . 'px' . ( $responsivesize ? ' * var(--ss-responsive-scale))' : '' ) ) : $v;

				$left = $item -> getAttribute( 'data-' . $view . 'portraitleft' );
				$top = $item -> getAttribute( 'data-' . $view . 'portraittop' );
				$translate = array( 0, 0 );

				switch( $item -> getAttribute( 'data-' . $view . 'portraitalign' ) )
				{
					case 'center':
						$translate[ 0 ] = '-50%';
						break;

					case 'right':
						$translate[ 0 ] = '-100%';
						break;

					default:
						break;
				}
				switch( $item -> getAttribute( $bHasParent ? 'data-' . $view . 'portraitparentalign' : 'data-' . $view . 'portraitalign' ) )
				{
					case 'center':
						$styleSeparated[ 'left' ] = 'calc(50%' . ( $left !== null ? ( ' + ' . $left . 'px' . ( $responsiveposition ? ' * var(--ss-responsive-scale)' : '' ) ) : '' ) . ')';
						break;

					case 'right':
						$styleSeparated[ 'left' ] = 'calc(100%' . ( $left !== null ? ( ' + ' . $left . 'px' . ( $responsiveposition ? ' * var(--ss-responsive-scale)' : '' ) ) : '' ) . ')';
						break;

					default:
						if( $left )
							$styleSeparated[ 'left' ] = 'calc(' . $left . 'px' . ( $responsiveposition ? ' * var(--ss-responsive-scale)' : '' ) . ')';
						break;
				}

				switch( $item -> getAttribute( 'data-' . $view . 'portraitvalign' ) )
				{
					case 'middle':
						$translate[ 1 ] = '-50%';
						break;

					case 'bottom':
						$translate[ 1 ] = '-100%';
						break;

					default:
						break;
				}
				switch( $item -> getAttribute( $bHasParent ? 'data-' . $view . 'portraitparentvalign' : 'data-' . $view . 'portraitvalign' ) )
				{
					case 'middle':
						$styleSeparated[ 'top' ] = 'calc(50%' . ( $top !== null ? ( ' + ' . $top . 'px' . ( $responsiveposition ? ' * var(--ss-responsive-scale)' : '' ) ) : '' ) . ')';
						break;

					case 'bottom':
						$styleSeparated[ 'top' ] = 'calc(100%' . ( $top !== null ? ( ' + ' . $top . 'px' . ( $responsiveposition ? ' * var(--ss-responsive-scale)' : '' ) ) : '' ) . ')';
						break;

					default:
						if( $top )
							$styleSeparated[ 'top' ] = 'calc(' . $top . 'px' . ( $responsiveposition ? ' * var(--ss-responsive-scale)' : '' ) . ')';
						break;
				}

				if( $translate[ 0 ] || $translate[ 1 ] )
				{
					$styleSeparated[ 'transform' ] = 'translate(' . $translate[ 0 ] . ', ' . $translate[ 1 ] . ')';
					if( $rotation )
						$styleSeparated[ 'transform' ] .= ' rotate(' . $rotation . 'deg)';
					$styleSeparated[ 'transform' ] .= '!important';
				}
			}
			unset( $styleSeparated );

			{
				$cont = '';
				foreach( $stylesSeparated as $view => $styleSeparated )
				{
					if( !$styleSeparated )
						continue;

					if( $view == 'tablet' )
						$cont .= '@media (orientation: landscape) and (max-width: 1199px) and (min-width: 901px), (orientation: portrait) and (max-width: 1199px) and (min-width: 701px) {' . "\n";
					else if( $view == 'mobile' )
						$cont .= '@media (orientation: landscape) and (max-width: 900px), (orientation: portrait) and (max-width: 700px) {' . "\n";

					$cont .= '.n2-ss-slider:not(.n2-ss-loaded) .n2-ss-layer' . $layerSelectorEx . $layerSelectorUnique . '{' . Ui::GetStyleAttr( $styleSeparated ) . '}' . "\n";

					if( $view != 'desktop' )
						$cont .= '}' . "\n";
				}

				if( $cont )
				{
					$itemStyle = $doc -> createElement( 'style' );
					if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
						$itemStyle -> setAttribute( 'type', 'text/css' );
					HtmlNd::SetValFromContent( $itemStyle, $cont );
					$item -> parentNode -> insertBefore( $itemStyle, $item );
				}
			}
		}

		if( $bResponsive )
		{
			$maxWidth = Gen::GetArrField( $cfg, array( 'responsive', 'base', 'slideOuterWidth' ) );
			if( !$maxWidth )
				$maxWidth = '1200';

			if( !$itemSld -> hasAttribute( 'data-ss-max-width' ) )
				$itemSld -> setAttribute( 'data-ss-max-width', $maxWidth );

			$bRtScript = true;
		}

		if( (isset($cfg[ 'initType' ])?$cfg[ 'initType' ]:null) == 'SmartSliderCarousel' )
		{
			foreach( HtmlNd::ChildrenAsArr( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," n2-ss-slide ")]', $itemSld ) ) as $itemIdx => $itemSlide )
				$itemSlide -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemSlide -> getAttribute( 'style' ) ), array( '--slide-group-index' => ( string )$itemIdx ) ) ) );

			foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," nextend-bullet-bar ")]/*', $itemSld ) as $itemBullet )
				$itemBullet -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemBullet -> getAttribute( 'style' ) ), array( 'display' => 'none' ) ) ) );

			if( $itemPane = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," n2-ss-slider-pane ")]', $itemSld ) ) )
				$itemPane -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemPane -> getAttribute( 'style' ) ), array( 'width' => '100%' ) ) ) );

			$itemSld -> setAttribute( 'data-ss-carousel', @json_encode( array( 'slideOuterWidth' => Gen::GetArrField( $cfg, array( 'responsive', 'base', 'slideOuterWidth' ) ), 'minSlideGap' => Gen::GetArrField( $cfg, array( 'responsive', 'minimumSlideGap' ) ) ) ) );
			$bRtScript = true;
		}
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $bRtScript )
	{

		$itemScript = $doc -> createElement( 'script' );
		if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
			$itemScript -> setAttribute( 'type', 'text/javascript' );
		$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
		HtmlNd::SetValFromContent( $itemScript, "function seraph_accel_cp_sldN2Ss_calcSizes(b){var a=parseInt(b.getAttribute(\"data-ss-max-width\"),10);if(a){var e=void 0;b.querySelectorAll(\".n2-ss-slide-limiter\").forEach(function(f){e||\"none\"==getComputedStyle(f).getPropertyValue(\"display\")||(e=f)});e||(e=b);a=e.clientWidth/a;var d=parseInt(b.getAttribute(\"data-ss-legacy-font-scale\"),10)?1+1/6:1;b.style.setProperty(\"--ss-responsive-scale\",a>d?d:a)}try{var c=JSON.parse(b.getAttribute(\"data-ss-carousel\"))}catch(f){}if(c){d=b.querySelector(\".n2-ss-slider-pane\");\nvar g=d.getBoundingClientRect().width;a=Math.max(1,Math.floor(g/(c.slideOuterWidth+c.minSlideGap)));c=Math.floor((g-a*c.slideOuterWidth)/a/2);0>c&&(c=0);d.style.setProperty(\"--slide-margin-side\",\"\"+c+\"px\");d.style.setProperty(\"--slide-transform-offset\",\"0!important\");d.style.setProperty(\"--self-side-margin\",\"none!important\");b=Array.from(b.querySelectorAll(\".nextend-bullet-bar>*\"));c=a?a!=b.length?Math.ceil(b.length/a):0:b.length;for(a=0;a<b.length;a++)a+1>c?b[a].style.setProperty(\"display\",\"none\"):\nb[a].style.removeProperty(\"display\")}}(function(b){function a(e){b.querySelectorAll(\".n2-ss-slider:not(.n2-ss-loaded)\").forEach(seraph_accel_cp_sldN2Ss_calcSizes)}b.addEventListener(\"seraph_accel_calcSizes\",a,{capture:!0,passive:!0});seraph_accel_izrbpb.add(function(){b.removeEventListener(\"seraph_accel_calcSizes\",a,{capture:!0,passive:!0})})})(document)" );
		$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
	}
}

function _ProcessCont_Cp_sldN2Ss_GetMeta( $id, $itemInitCmnScr )
{
	$prms = array();

	if( !$itemInitCmnScr )
		return( $prms );

	if( !preg_match( '@\\Wnew\\s+_N2\\s*\\.\\s*(\\w+)\\(\\s*\'' . $id . '\'\\s*,\\s*@', $itemInitCmnScr -> nodeValue, $m, PREG_OFFSET_CAPTURE ) )
		return( $prms );

	$prms[ 'initType' ] = $m[ 1 ][ 0 ];

	$posStart = $m[ 0 ][ 1 ] + strlen( $m[ 0 ][ 0 ] );
	$posEnd = Gen::JsonGetEndPos( $posStart, $itemInitCmnScr -> nodeValue );
	if( $posEnd === null )
		return( $prms );

	$data = substr( $itemInitCmnScr -> nodeValue, $posStart, $posEnd - $posStart );

	while( preg_match( '@\\"(\\w+)\\"\\s*:\\s*function\\(\\)\\s*@', $data, $m, PREG_OFFSET_CAPTURE ) )
	{
		$posStart = $m[ 0 ][ 1 ] + strlen( $m[ 0 ][ 0 ] );
		$posEnd = Gen::JsonGetEndPos( $posStart, $data );

		$dataSub = '';

		switch( $m[ 1 ][ 0 ] )
		{
		case 'initCallbacks':
			if( preg_match( '@\\Wnew\\s+_N2\\s*.\\s*SmartSliderWidgetBulletTransition\\(\\s*this\\s*,\\s*@', $data, $mSub, PREG_OFFSET_CAPTURE, $posStart ) )
			{
				if( strlen( $dataSub ) )
					$dataSub .= ',';

				$posStartSub = $mSub[ 0 ][ 1 ] + strlen( $mSub[ 0 ][ 0 ] );
				$posEndSub = Gen::JsonGetEndPos( $posStartSub, $data );

				$dataSub .= '"SmartSliderWidgetBulletTransition":' . substr( $data, $posStartSub, $posEndSub - $posStartSub );
			}
			break;
		}

		$data = substr_replace( $data, '"' . $m[ 1 ][ 0 ] . '":{' . $dataSub . '}', $m[ 0 ][ 1 ], $posEnd - $m[ 0 ][ 1 ] );
	}

	$prms = array_merge_recursive( Gen::GetArrField( @json_decode( Gen::JsObjDecl2Json( $data ), true ), array( '' ), array() ), $prms );
	return( $prms );
}

function _ProcessCont_Cp_sldRev( $ctx, &$ctxProcess, $doc, $xpath, $bDblLoadFix )
{
	$itemInitCmnScr = null;
	$engineVer = null;

	$adjusted = false;
	$adjustedBubbles = false;
	foreach( HtmlNd::ChildrenAsArr( $xpath -> query( './/rs-module' ) ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		if( !$engineVer )
			$engineVer = _RevSld_GetEngineVer( $ctxProcess, $xpath );
		if( !$itemInitCmnScr )
			$itemInitCmnScr = HtmlNd::FirstOfChildren( $xpath -> query( './/script[contains(text(),".revolutionInit(")]' ) );

		$itemId = $item -> getAttribute( 'id' );
		if( $bDblLoadFix )
		{
			$itemIdOrig = $itemId;

		}

		$prms = _RevSld_GetPrmsFromScr( $item, $itemInitCmnScr, $bDblLoadFix ? $itemId : null );
		if( !$prms )
			continue;

		$aItemSlide = HtmlNd::ChildrenAsArr( $xpath -> query( './rs-slides/rs-slide', $item ) );
		if( !$aItemSlide )
			continue;

		$nSlides = count( $aItemSlide );
		$itemFirstSlide = $aItemSlide[ 0 ];
		$nSwitchingLoadingTimeout = 0;
		$nSwitchingLoadingTimeoutMax = ( int )$item -> getAttribute( 'data-lzl-ing-tm' );
		if( !$nSwitchingLoadingTimeoutMax )
			$nSwitchingLoadingTimeoutMax = 4500;

		$aItemStyle = array( array(), array(), array(), array() );

		$aGridWidth = Gen::GetArrField( $prms, array( 'start', 'gw' ), array() );
		if( count( $aGridWidth ) == 1 )
			$aGridWidth = array_fill( 0, count( $aItemStyle ), $aGridWidth[ 0 ] );

		$aGridHeight = Gen::GetArrField( $prms, array( 'start', 'gh' ), array() );
		if( count( $aGridHeight ) == 1 )
			$aGridHeight = array_fill( 0, count( $aItemStyle ), $aGridHeight[ 0 ] );

		$aWidth = array_reverse( Gen::GetArrField( $prms, array( 'start', 'rl' ), array() ) );
		if( count( $aWidth ) == 1 )
			$aWidth = array_fill( 0, count( $aItemStyle ), $aWidth[ 0 ] );

		$aElWidth = array_reverse( Gen::GetArrField( $prms, array( 'start', 'el' ), array() ) );
		if( count( $aElWidth ) == 1 )
			$aElWidth = array_fill( 0, count( $aItemStyle ), $aElWidth[ 0 ] );

		if( count( $aWidth ) != count( $aItemStyle ) )
			continue;

		HtmlNd::AddRemoveAttrClass( $item, array( 'js-lzl-nid' ) );

		$keepBPHeight = Gen::GetArrField( $prms, array( 'init', 'keepBPHeight' ) );
		$layout = Gen::GetArrField( $prms, array( 'init', 'sliderLayout' ), '' );
		$item -> setAttribute( 'data-lzl-widths', @json_encode( $aWidth ) );
		$item -> setAttribute( 'data-lzl-widths-g', @json_encode( array_reverse( $aGridWidth ) ) );
		$item -> setAttribute( 'data-lzl-heights-g', @json_encode( array_reverse( $aGridHeight ) ) );
		$item -> setAttribute( 'data-lzl-g-s', @version_compare( $engineVer, '6.6', '>=' ) ? ( $layout != 'fullscreen' ? false : !$keepBPHeight ) : ( $layout != 'fullscreen' ? $keepBPHeight : !$keepBPHeight ) );
		$item -> setAttribute( 'data-lzl-layout', $layout );

		$itemStyleCont = '';

		if( $layout != 'fullscreen' )
		{
			$heightProp = true ? 'height' : 'min-height';
			for( $i = 0; $i < count( $aItemStyle ); $i++ )
			{
				$h = (isset($aGridHeight[ $i ])?$aGridHeight[ $i ]:'0') . 'px';
				if( !$keepBPHeight )
					$h = 'calc(' . $h . '*var(--lzl-rs-scale))';
				$aItemStyle[ $i ][ '#' . $itemId . ':not(.revslider-initialised)' ][ $heightProp ] = $h . '!important';

				if( $bDblLoadFix )
				{
					if( $itemIdOrig != $itemId )
						$aItemStyle[ $i ][ '#' . $itemIdOrig . ':not(.revslider-initialised)' ][ $heightProp ] = $h . '!important';
				}
			}

			if( $bDblLoadFix )
				$itemStyleCont .= 'rs-module-wrap:has( > #' . $itemId . '.js-lzl-ing) { margin-top: calc(-1px * var(--lzl-rs-cy))!important; }';
		}

		{
			$v = Gen::GetArrField( $prms, array( 'start', 'offset' ) );
			if( !is_string( $v ) )
				$v = ( string )$v . 'px';
			else if( !strlen( $v ) )
				$v = '0px';
			else if( Gen::StrEndsWith( $v, '%' ) )
				$v = ( string ) ( ( float )$v / 100 ) . ' * var(--seraph-accel-dvh)';
			$item -> setAttribute( 'style', Ui::GetStyleAttr( Ui::MergeStyleAttr( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( '--lzl-rs-offs-y' => $v ) ) ) );
		}

		$aItemTop = array();

		{
			$itemSlidesTmp = $doc -> createElement( 'rs-slides-lzl' );
			HtmlNd::AddRemoveAttrClass( $itemSlidesTmp, array( 'rs-lzl-cont', 'js-lzl-ing' ) );
			HtmlNd::InsertAfter( $item, $itemSlidesTmp, $itemFirstSlide -> parentNode );
			$itemSlidesTmp -> setAttribute( 'style', Ui::GetStyleAttr( Ui::MergeStyleAttr( Ui::ParseStyleAttr( $itemSlidesTmp -> getAttribute( 'style' ) ), array( 'width' => '100%', 'height' => '100%' ) ) ) );

			$itemFirstSlideTmp = $itemFirstSlide -> cloneNode( true );
			$itemSlidesTmp -> appendChild( $itemFirstSlideTmp );
			$itemFirstSlideTmp -> setAttribute( 'style', Ui::GetStyleAttr( Ui::MergeStyleAttr( Ui::ParseStyleAttr( $itemFirstSlideTmp -> getAttribute( 'style' ) ), array( 'width' => '100%', 'height' => '100%' ) ) ) );

			$aItemTop[] = $itemFirstSlideTmp;

			if( $itemCurSlideIndex = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(text(),"{{current_slide_index}}")]', $itemFirstSlideTmp ) ) )
				if( $itemCurSlideIndex -> firstChild && $itemCurSlideIndex -> firstChild -> nodeType == XML_TEXT_NODE )
					$itemCurSlideIndex -> firstChild -> nodeValue = str_replace( '{{current_slide_index}}', '1', ( string )$itemCurSlideIndex -> firstChild -> nodeValue );

			if( $itemCurSlideIndex = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(text(),"{{total_slide_count}}")]', $itemFirstSlideTmp ) ) )
				if( $itemCurSlideIndex -> firstChild && $itemCurSlideIndex -> firstChild -> nodeType == XML_TEXT_NODE )
					$itemCurSlideIndex -> firstChild -> nodeValue = str_replace( '{{total_slide_count}}', ( string )$nSlides, ( string )$itemCurSlideIndex -> firstChild -> nodeValue );
		}

		if( $itemStaticLayers = HtmlNd::FirstOfChildren( $xpath -> query( './rs-static-layers', $item ) ) )
		{
			$itemStaticLayersTmp = HtmlNd::SetTag( $itemStaticLayers -> cloneNode( true ), 'rs-static-layers-lzl' );
			HtmlNd::AddRemoveAttrClass( $itemStaticLayersTmp, array( 'rs-lzl-cont', 'js-lzl-ing' ) );
			HtmlNd::InsertAfter( $item, $itemStaticLayersTmp, $itemStaticLayers );

			$aItemTop[] = $itemStaticLayersTmp;

			if( $itemCountTotal = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," count_total ")]', $itemStaticLayersTmp ) ) )
				if( $itemCountTotal -> firstChild && $itemCountTotal -> firstChild -> nodeType == XML_TEXT_NODE )
					$itemCountTotal -> firstChild -> nodeValue = sprintf( '%0' . strlen( trim( ( string )$itemCountTotal -> firstChild -> nodeValue ) ) . 'u', $nSlides );
		}

		foreach( $aItemTop as $itemFirstSlideTmp )
		{
			$iCurBubblesRand = 0;
			$slideMediaFilter = $itemFirstSlideTmp -> getAttribute( 'data-mediafilter' );
			$itemSlideChild = null;
			$itemSlideChildNext = null;
			$itemSlideBgContainer = null;
			$bBlendModeLighten = false;
			$bInColumn = false;
			$bInGroup = false;

			$attrAnim = _RevSld_GetAttrs( $itemFirstSlideTmp -> getAttribute( 'data-anim' ) );
			_RevSld_AdjustTimeoutByVal( $nSwitchingLoadingTimeout, $nSwitchingLoadingTimeoutMax, (isset($attrAnim[ 'ms' ])?$attrAnim[ 'ms' ]:null) );

			while( $itemSlideChild = ( $itemSlideChildNext ? $itemSlideChildNext : HtmlNd::GetNextTreeChild( $itemFirstSlideTmp, $itemSlideChild ) ) )
			{
				$itemSlideChildNext = null;
				if( $itemSlideChild -> nodeType != XML_ELEMENT_NODE )
					continue;

				{
					$id = ( string )$itemSlideChild -> getAttribute( 'id' );
					if( strlen( $id ) && strpos( $id, '-lzl' ) === false )
						$itemSlideChild -> setAttribute( 'id', $id . '-lzl' );
					unset( $id );
				}

				$aClass = HtmlNd::GetAttrClass( $itemSlideChild );

				$bResponsiveSizes = $itemSlideChild -> getAttribute( 'data-rsp_bd' ) !== 'off';
				$bResponsiveOffsets = $itemSlideChild -> getAttribute( 'data-rsp_o' ) !== 'off';
				$bResponsiveChildren = $itemSlideChild -> getAttribute( 'data-rsp_ch' ) === 'on';

				$baseAlign = $itemSlideChild -> getAttribute( 'data-basealign' );

				$isLayer = $itemSlideChild -> nodeName == 'rs-layer' || in_array( 'rs-layer', $aClass );
				$isContainer = $itemSlideChild -> nodeName == 'rs-row' || $itemSlideChild -> nodeName == 'rs-column' || $itemSlideChild -> nodeName == 'rs-group';

				$itemParent = $itemSlideChild -> parentNode;
				$itemInsertBefore = $itemSlideChild -> nextSibling;

				{

					if( $itemParent === $itemFirstSlideTmp )
					{
						$bInColumn = false;
						$bInGroup = false;
					}

					if( $itemSlideChild -> nodeName == 'rs-column' )
						$bInColumn = true;
					if( $itemSlideChild -> nodeName == 'rs-group' )
						$bInGroup = true;
				}

				if( $itemSlideChild -> nodeName == 'img' && in_array( 'rev-slidebg', $aClass ) )
				{
					$itemChildSelector = '#' . $itemId . ' ' . ( $itemFirstSlideTmp -> hasAttribute( 'data-key' ) ? ( '.js-lzl-ing [data-key="' . $itemFirstSlideTmp -> getAttribute( 'data-key' ) . '"]' ) : ( $itemFirstSlideTmp -> nodeName . '.js-lzl-ing' ) ) . ' rs-sbg:nth-child(' . ( ( $itemSlideBgContainer ? $itemSlideBgContainer -> childNodes -> length : 0 ) + 1 ) . ')';

					$itemSlideChildNext = HtmlNd::GetNextTreeChild( $itemFirstSlideTmp, $itemSlideChild );
					$attrPanZoom = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-panzoom' ) );

					$srcImg = $itemSlideChild -> getAttribute( 'data-lazyload' );
					if( !$srcImg )
						$srcImg = $itemSlideChild -> getAttribute( 'src' );
					$attrBg = Ui::MergeStyleAttr( array( 'p' => 'center' ), _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-bg' ) ) );

					$attrPanZoomDuration = ( int )Gen::GetArrField( $attrPanZoom, array( 'd' ), '0' );
					$attrPanZoomOffsetXY = explode( '/', Gen::GetArrField( $attrPanZoom, array( 'os' ), '0px/0px' ) );
					$attrPanZoomOffsetEndXY = explode( '/', Gen::GetArrField( $attrPanZoom, array( 'oe' ), '0px/0px' ) );
					$attrPanZoomScale = ( float )Gen::GetArrField( $attrPanZoom, array( 'ss' ), '100%' ) / 100;
					$attrPanZoomScaleEnd = ( float )Gen::GetArrField( $attrPanZoom, array( 'se' ), '100%' ) / 100;

					$attrBgPos = explode( ' ', Gen::GetArrField( $attrBg, array( 'p' ), '' ) );
					if( count( $attrBgPos ) < 2 )
						$attrBgPos[ 1 ] = $attrBgPos[ 0 ];

					switch( $attrBgPos[ 0 ] )
					{
					case 'left':					$attrBgPos[ 0 ] = '0%'; break;
					case 'middle':
					case 'center':					$attrBgPos[ 0 ] = '50%'; break;
					case 'right':					$attrBgPos[ 0 ] = '100%'; break;
					}

					switch( $attrBgPos[ 1 ] )
					{
					case 'top':						$attrBgPos[ 1 ] = '0%'; break;
					case 'middle':
					case 'center':					$attrBgPos[ 1 ] = '50%'; break;
					case 'bottom':					$attrBgPos[ 1 ] = '100%'; break;
					}

					$attrBgPosEnd[ 0 ] = 'calc(' . $attrBgPos[ 0 ] . ' + ' . _RevSld_GetSize( false, Gen::GetArrField( $attrPanZoomOffsetEndXY, array( 0 ), '0' ) ) . ' / ' . $attrPanZoomScaleEnd . ')';
					$attrBgPosEnd[ 1 ] = 'calc(' . $attrBgPos[ 1 ] . ' + ' . _RevSld_GetSize( false, Gen::GetArrField( $attrPanZoomOffsetEndXY, array( 1 ), '0' ) ) . ' / ' . $attrPanZoomScaleEnd . ')';
					$attrBgPos[ 0 ] = 'calc(' . $attrBgPos[ 0 ] . ' + ' . _RevSld_GetSize( false, Gen::GetArrField( $attrPanZoomOffsetXY, array( 0 ), '0' ) ) . ' / ' . $attrPanZoomScale . ')';
					$attrBgPos[ 1 ] = 'calc(' . $attrBgPos[ 1 ] . ' + ' . _RevSld_GetSize( false, Gen::GetArrField( $attrPanZoomOffsetXY, array( 1 ), '0' ) ) . ' / ' . $attrPanZoomScale . ')';

					$attrsStyle = array( 'width' => '100%', 'height' => '100%', 'background' => ( isset( $attrBg[ 'c' ] ) ? ( ( string )$attrBg[ 'c' ] . ( Gen::StrStartsWith( ( string )$attrBg[ 'c' ], array( '#', 'rgb', 'hsl' ) ) ? '' : ',' ) . ' ' ) : '' ) . implode( ' ', $attrBgPos ) . ' / cover no-repeat url(' . $srcImg . ')', 'transform' => 'scale(' . $attrPanZoomScale . ') rotate(' . Gen::GetArrField( $attrPanZoom, array( 'rs' ), '0deg' ) . ')' );
					if( $attrPanZoomDuration )
					{
						$attrsStyle[ 'transition-property' ] = 'transform, background-position !important';
						$attrsStyle[ 'transition-duration' ] = '' . $attrPanZoomDuration . 'ms !important';
						$attrsStyle[ 'transition-timing-function' ] = 'linear';
					}
					$itemSlideChildTmp = HtmlNd::CreateTag( $doc, 'div', array( 'style' => $attrsStyle ) );
					$itemParent -> replaceChild( $itemSlideChildTmp, $itemSlideChild );
					$itemSlideChild = $itemSlideChildTmp;

					$itemSlideBgItem = HtmlNd::CreateTag( $doc, 'rs-sbg', array( 'class' => array( $slideMediaFilter ), 'style' => array( 'width' => '100%', 'height' => '100%' ) ), array( $itemSlideChild ) );

					if( $itemSlideBgContainer )
					{
						$itemSlideBgContainer -> appendChild( $itemSlideBgItem );
					}
					else
					{
						$itemSlideBgContainer = HtmlNd::CreateTag( $doc, 'rs-sbg-wrap', null, array( $itemSlideBgItem ) );
						$itemParent -> insertBefore( HtmlNd::CreateTag( $doc, 'rs-sbg-px', null, array( $itemSlideBgContainer ) ), $itemInsertBefore );
					}

					if( $attrPanZoomDuration )
					{
						$itemStyleCont .= '
								rs-module:not(.js-lzl-nid)' . $itemChildSelector . ' > div {
									transform: scale(' . $attrPanZoomScaleEnd . ') rotate(' . Gen::GetArrField( $attrPanZoom, array( 're' ), '0deg' ) . ') !important;
									background-position: ' . implode( ' ', $attrBgPosEnd ) . ' !important;
								}
							';
					}
				}
				else if( $itemSlideChild -> nodeName == 'rs-bgvideo' )
				{
					$itemChildSelector = '#' . $itemId . ' ' . ( $itemFirstSlideTmp -> hasAttribute( 'data-key' ) ? ( '.js-lzl-ing [data-key="' . $itemFirstSlideTmp -> getAttribute( 'data-key' ) . '"]' ) : ( $itemFirstSlideTmp -> nodeName . '.js-lzl-ing' ) ) . ' rs-bgvideo:nth-child(' . ( ( $itemSlideBgContainer ? $itemSlideBgContainer -> childNodes -> length : 0 ) + 1 ) . ')';

					HtmlNd::AddRemoveAttrClass( $itemSlideChild, array( $slideMediaFilter ) );

					$itemSlideChildNext = HtmlNd::GetNextTreeChild( $itemFirstSlideTmp, $itemSlideChild );

					$itemSlideBgItem = $itemSlideChild;
					$itemSlideBgItem -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => array( 'rs-fullvideo-cover' ) ) ) );
					$itemSlideBgItem -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => array( 'html5vid', 'rs_html5vidbasicstyles', 'fullcoveredvideo' ) ), array( HtmlNd::CreateTag( $doc, 'video', array( 'autoplay' => '', 'muted' => '', 'loop' => '', 'preload' => 'auto', 'style' => array( 'object-fit' => 'cover', 'background-size' => 'cover', 'opacity' => '0', 'width' => '100%', 'height' => '100%', 'position' => 'absolute', 'left' => '0px', 'top' => '0px' ) ), array( HtmlNd::CreateTag( $doc, 'source', array( 'src' => $itemSlideChild -> getAttribute( 'data-mp4' ), 'type' => array( 'video/mp4' ) ) ) ) ) ) ) );
					$itemSlideBgItem -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => array( 'tp-video-play-button' ) ), array( HtmlNd::CreateTag( $doc, 'i', array( 'class' => array( 'revicon-right-dir' ) ) ), HtmlNd::CreateTag( $doc, 'span', array( 'class' => array( 'tp-revstop' ) ), array( $doc -> createTextNode( ' ' ) ) ) ) ) );

					if( $itemSlideBgContainer )
					{
						$itemSlideBgContainer -> appendChild( $itemSlideBgItem );
					}
					else
					{
						$itemSlideBgContainer = HtmlNd::CreateTag( $doc, 'rs-sbg-wrap', null, array( $itemSlideBgItem ) );
						$itemParent -> insertBefore( HtmlNd::CreateTag( $doc, 'rs-sbg-px', null, array( $itemSlideBgContainer ) ), $itemInsertBefore );
					}
				}
				else if( $isLayer || $isContainer )
				{
					$id = $itemSlideChild -> getAttribute( 'id' );
					$itemIdWrap = $id . '-wrap';

					$itemChildSelector = '.js-lzl-ing #' . $id;
					$itemChildSelectorWrap = '.js-lzl-ing #' . $itemIdWrap;

					$attrXy = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-xy' ), count( $aItemStyle ) );
					$attrDim = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-dim' ) );
					$attrText = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-text' ) );
					$attrPadding = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-padding' ) );
					$attrMargin = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-margin' ) );
					$attrBorder = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-border' ) );
					$attrBTrans = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-btrans' ), count( $aItemStyle ) );
					$attrTextStroke = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-tst' ) );
					$attrType = $itemSlideChild -> getAttribute( 'data-type' );
					$attrWrapperClass = $itemSlideChild -> getAttribute( 'data-wrpcls' );
					$attrVisibility = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-vbility' ) );
					$attrColumn = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-column' ) );
					$attrLoop = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-tloop' ) );

					$attrFrame0 = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-frame_0' ) );
					$attrFrame0Mask = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-frame_0_mask' ) );
					$attrFrame1 = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-frame_1' ) );

					$bBaseAlignLayerArea = ( $attrType != 'row' ) ? ( $baseAlign !== 'slide' ) : false;

					$attrColor = trim( ( string )$itemSlideChild -> getAttribute( 'data-color' ) );
					if( strlen( $attrColor ) )
						$attrColor = explode( '||', ( string )$attrColor );
					else
						$attrColor = array();

					$attrDisplay = $itemSlideChild -> getAttribute( 'data-disp' );
					if( !$attrDisplay )
						$attrDisplay = null;

					$attrPos = $itemSlideChild -> getAttribute( 'data-pos' );

					if( !isset( $attrText[ 'ls' ] ) )
						$attrText[ 'ls' ] = '0';

					if( !isset( $attrText[ 'l' ] ) && ( $attrType == 'text' || $attrType == 'column' ) )
						$attrText[ 'l' ] = '25px';

					$styleSeparated = array( 'color' => $attrColor ? null : '#fff', 'position' => ( $itemParent === $itemFirstSlideTmp || $itemParent -> nodeName == 'rs-group' || $attrPos == 'a' ) ? 'absolute' : 'relative', 'display' => $attrDisplay );
					$styleSeparatedWrap = array( 'position' => $styleSeparated[ 'position' ], 'display' => $attrDisplay, 'pointer-events' => 'auto' );

					if( (isset($attrLoop[ 'u' ])?$attrLoop[ 'u' ]:null) != 'true' )
						if( !_RevSld_AdjustTimeoutByVal( $nSwitchingLoadingTimeout, $nSwitchingLoadingTimeoutMax, (isset($attrFrame1[ 'st' ])?$attrFrame1[ 'st' ]:null), (isset($attrFrame1[ 'sp' ])?$attrFrame1[ 'sp' ]:null) ) && !( (isset($attrFrame0[ 'o' ])?$attrFrame0[ 'o' ]:null) == '1' && ( !$attrFrame0Mask || (isset($attrFrame0Mask[ 'u' ])?$attrFrame0Mask[ 'u' ]:null) != 't' ) ) )
							$styleSeparated[ 'opacity' ] = '0!important';

					if( $attrType != 'column' && ( $attrPos == 'a' || !HtmlNd::FindUpBy( $itemSlideChild, function( $nd, $data ) { return( $nd -> nodeName == 'rs-column' ); } ) ) )
					{
						$bExtraX = ( $baseAlign !== 'slide' && ( $itemParent === $itemFirstSlideTmp || ( $itemParent -> nodeName == 'rs-zone' && $itemParent -> parentNode === $itemFirstSlideTmp ) ) );

						$a = array_fill( 0, count( $aItemStyle ), array() );
						$aW = array_fill( 0, count( $aItemStyle ), array() );
						for( $i = 0; $i < count( $aItemStyle ); $i++ )
						{
							$translate = array( 0, 0 );
							$offset = $attrType != 'row' ? array( Gen::GetArrField( $attrXy, array( 'xo', $i ), '0' ), Gen::GetArrField( $attrXy, array( 'yo', $i ), '0' ) ) : array( '0', '0' );
							$bBaseAlignLayerAreaI = $bBaseAlignLayerArea;

							{
								$prefix = null;
								$prefixSize = null;
								switch( $alignX = Gen::GetArrField( $attrXy, array( 'x', $i ), '' ) )
								{
								case 'c':
								case 'm':
									$translate[ 0 ] = '-50%';
									$prefix = '50% + ';
									break;

								case 'r':
									$translate[ 0 ] = '-100%';
									$prefix = '100% - ';
									if( $bExtraX )
									{
										$prefix = '-1px * var(--lzl-rs-extra-x) + ' . $prefix;
										$prefixSize = '-2px * var(--lzl-rs-extra-x) + ';
									}
									break;

								default:
									if( Gen::StrEndsWith( $alignX, 'px' ) )
										$offset[ 0 ] = $alignX;

									$prefix = '';
									if( $bExtraX )
									{
										$prefix = '1px * var(--lzl-rs-extra-x) + ' . $prefix;
										$prefixSize = '-2px * var(--lzl-rs-extra-x) + ';
									}
								}

								$aW[ $i ][ 'left' ] = _RevSld_GetSize( $bResponsiveOffsets, $offset[ 0 ], $prefix );
								$aW[ $i ][ 'width' ] = _RevSld_GetSize( false, '100%', $prefixSize );
							}

							{
								$prefix = null;
								switch( $alignY = Gen::GetArrField( $attrXy, array( 'y', $i ), '' ) )
								{
								case 'c':
								case 'm':
									if( $attrType != 'row' )
										$translate[ 1 ] = '-50%';
									$prefix = '50% + ';

									if( @version_compare( $engineVer, '6.6', '>=' ) && $bBaseAlignLayerAreaI )
										$bBaseAlignLayerAreaI = false;
									break;

								case 'b':
									if( $attrType != 'row' )
										$translate[ 1 ] = '-100%';
									$prefix = '100% - ';
									break;

								default:
									if( Gen::StrEndsWith( $alignY, 'px' ) )
										$offset[ 1 ] = $alignY;
								}

								$offsSuffix = null;
								if( $bBaseAlignLayerAreaI )
									$offsSuffix = ' + 1px * var(--lzl-rs-diff-y)';

								$aW[ $i ][ 'top' ] = _RevSld_GetSize( $bResponsiveOffsets, $offset[ 1 ], $prefix, $offsSuffix );
							}

							if( $translate[ 0 ] || $translate[ 1 ] )
								$a[ $i ][ 'transform' ] = 'translate(' . $translate[ 0 ] . ', ' . $translate[ 1 ] . ')!important';
						}
						_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
						_RevSld_SetStyleAttr( $styleSeparatedWrap, $aItemStyle, $itemChildSelectorWrap, $aW );
					}

					$aSizeChild = array();
					$aSizeWrap = array();
					foreach( array( 'w' => 'width', 'maxw' => 'max-width', 'h' => 'height' ) as $f => $t )
					{
						$a = array();
						foreach( ( array )(isset($attrDim[ $f ])?$attrDim[ $f ]:'auto') as $i => $v )
						{
							$v = $a[ $i ][ $t ] = _RevSld_GetSize( $bResponsiveSizes, $v . ( is_numeric( $v ) ? 'px' : '' ) );
							$aSizeChild[ $i ][ $t ] = $v == 'auto' ? 'auto' : null;
							$aSizeWrap[ $i ][ $t ] = Gen::StrEndsWith( ( string )$v, '%' ) ? '100%' : null;
						}
						_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
					}

					if( isset( $attrColumn[ 'w' ] ) )
					{
						foreach( $aSizeWrap as $i => $v ) unset( $aSizeWrap[ $i ][ 'width' ] );
						$a = array(); foreach( ( array )$attrColumn[ 'w' ] as $i => $v ) $a[ $i ][ 'width' ] = _RevSld_GetSize( $bResponsiveOffsets, $v );
						_RevSld_SetStyleAttr( $styleSeparatedWrap, $aItemStyle, $itemChildSelectorWrap, $a );
					}

					if( $attrType != 'column' )
					{

						{
							$a = array();
							foreach( ( array )(isset($attrDim[ 'w' ])?$attrDim[ 'w' ]:'auto') as $i => $vDim )
							{

								$v = isset( $attrText[ 'w' ] ) ? ( is_array( $attrText[ 'w' ] ) ? $attrText[ 'w' ][ $i ] : $attrText[ 'w' ] ) : 'nowrap';
								if( $v == 'normal' && $vDim == 'auto' && ( !$bInColumn && !$bInGroup || $styleSeparated[ 'position' ] !== 'relative' ) )
									$v = 'nowrap';
								$a[ $i ][ 'white-space' ] = $v;
							}
							_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
						}

						if( $attrColor )
						{
							$a = array(); foreach( $attrColor as $i => $v ) $a[ $i ][ 'color' ] = $attrColor[ $i ];
							_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
						}

						foreach( array( 'fw' => 'font-weight' ) as $f => $t )
						{
							$a = array(); foreach( ( array )(isset($attrText[ $f ])?$attrText[ $f ]:null) as $i => $v ) $a[ $i ][ $t ] = $v;
							_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
						}

						foreach( array( 's' => 'font-size', 'ls' => 'letter-spacing' ) as $f => $t )
						{
							$a = array(); foreach( ( array )(isset($attrText[ $f ])?$attrText[ $f ]:null) as $i => $v ) if( $v !== null ) $a[ $i ][ $t ] = _RevSld_GetSize( $bResponsiveSizes, $v . ( Gen::StrEndsWith( $v, 'px' ) ? '' : 'px' ) );
							_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
						}
					}

					foreach( array( 'l' => 'line-height' ) as $f => $t )
					{
						$a = array(); foreach( ( array )(isset($attrText[ $f ])?$attrText[ $f ]:null) as $i => $v ) if( $v !== null ) $a[ $i ][ $t ] = _RevSld_GetSize( $bResponsiveSizes, $v . ( Gen::StrEndsWith( $v, 'px' ) ? '' : 'px' ) );
						_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
					}

					foreach( array( 'a' => 'text-align' ) as $f => $t )
					{
						$a = array(); foreach( ( array )(isset($attrText[ $f ])?$attrText[ $f ]:null) as $i => $v ) $a[ $i ][ $t ] = $v;
						_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
					}

					foreach( array( 'f' => 'float' ) as $f => $t )
					{
						$a = array(); foreach( ( array )(isset($attrText[ $f ])?$attrText[ $f ]:null) as $i => $v ) $a[ $i ][ $t ] = $v;
						_RevSld_SetStyleAttr( $styleSeparatedWrap, $aItemStyle, $itemChildSelectorWrap, $a );
					}

					foreach( array( 'l' => 'padding-left', 'r' => 'padding-right', 't' => 'padding-top', 'b' => 'padding-bottom' ) as $f => $t )
					{
						$a = array(); foreach( ( array )(isset($attrPadding[ $f ])?$attrPadding[ $f ]:null) as $i => $v ) if( $v !== null ) $a[ $i ][ $t ] = _RevSld_GetSize( $bResponsiveSizes, $v . 'px' );
						_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
					}

					foreach( array( 'l' => 'margin-left', 'r' => 'margin-right', 't' => 'margin-top', 'b' => 'margin-bottom' ) as $f => $t )
					{
						if( $itemSlideChild -> nodeName == 'rs-row' )
							$t = str_replace( 'margin-', 'padding-', $t );
						$a = array(); foreach( ( array )(isset($attrMargin[ $f ])?$attrMargin[ $f ]:null) as $i => $v ) if( $v !== null ) $a[ $i ][ $t ] = _RevSld_GetSize( $bResponsiveSizes, $v . 'px' );
						_RevSld_SetStyleAttr( $styleSeparatedWrap, $aItemStyle, $itemChildSelectorWrap, $a );
					}

					foreach( array( 'bos' => 'border-style', 'boc' => 'border-color', 'bow' => 'border-width', 'bor' => 'border-radius' ) as $f => $t )
					{
						$a = array(); foreach( ( array )(isset($attrBorder[ $f ])?$attrBorder[ $f ]:null) as $i => $v ) $a[ $i ][ $t ] = ( $f == 'bow' ) ? _RevSld_GetSize( false, $v ) : $v;
						_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
					}

					foreach( array( 'w' => '-webkit-text-stroke-width', 'c' => '-webkit-text-stroke-color' ) as $f => $t )
					{
						$a = array(); foreach( ( array )(isset($attrTextStroke[ $f ])?$attrTextStroke[ $f ]:null) as $i => $v ) $a[ $i ][ $t ] = $v;
						_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
					}

					if( $attrVisibility )
					{

						$a = array(); foreach( $attrVisibility[ '' ] as $i => $v ) if( $v === 'f' ) $a[ $i ][ 'display' ] = 'none'; else $a[ $i ][ '' ] = '';
						_RevSld_SetStyleAttr( $styleSeparatedWrap, $aItemStyle, $itemChildSelectorWrap, $a );
					}

					if( isset( $attrFrame0[ 'rZ' ] ) )
					{
						if( !is_array( $attrFrame0[ 'rZ' ] ) || isset( $attrFrame0[ 'rZ' ][ 'cyc' ] ) )
							$attrFrame0[ 'rZ' ] = array_fill( 0, count( $aItemStyle ), $attrFrame0[ 'rZ' ] );
						$attrBTrans[ 'rZ' ] = $attrFrame0[ 'rZ' ];
					}

					if( isset( $attrBTrans[ 'rZ' ] ) )
					{
						$a = array(); foreach( ( array )$attrBTrans[ 'rZ' ] as $i => $v ) $a[ $i ][ 'transform' ] = 'rotate(' . ( isset( $v[ 'cyc' ] ) ? _RevSld_GetIdxPropVal( $attrBTrans, array( 'cyc' ), 0, '0' ) : ( string )( int )$v  ). 'deg)!important';
						_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
					}

					if( $attrType == 'image' && ( $itemImg = HtmlNd::FirstOfChildren( $xpath -> query( './/img', $itemSlideChild ) ) ) )
					{
						HtmlNd::RenameAttr( $itemImg, 'data-lazyload', 'src' );

						$styleSeparatedImg = array();
						_RevSld_SetStyleAttr( $styleSeparatedImg, $aItemStyle, $itemChildSelector . ' > img', $aSizeChild );
						$itemImg -> setAttribute( 'style', Ui::GetStyleAttr( Ui::MergeStyleAttr( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), $styleSeparatedImg ) ) );
						unset( $styleSeparatedImg );

					}

					if( $attrType == 'video' )
					{

						$mp4Url = $itemSlideChild -> getAttribute( 'data-mp4' );

						$itemSlideChild -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => array( 'html5vid', 'rs_html5vidbasicstyles' ), 'style' => array( 'box-sizing' => 'content-box', 'border-color' => 'transparent', 'border-style' => 'none', 'left' => '0px', 'top' => '0px' ) ), array(
							HtmlNd::CreateTag( $doc, 'video', array( 'preload' => 'auto', 'style' => array( 'opacity' => '1', 'width' => '100%', 'height' => '100%', 'display' => 'block' ) ), array(
								HtmlNd::CreateTag( $doc, 'source', array( 'type' => 'video/mp4', 'src' => $mp4Url ) )
							) )
						) ) );
					}

					if( $posterUrl = $itemSlideChild -> getAttribute( 'data-poster' ) )
					{

						$itemSlideChild -> appendChild( HtmlNd::CreateTag( $doc, 'rs-poster', array( 'class' => 'noSwipe', 'style' => array( 'background-image' => 'url(' . $posterUrl . ')' ) ) ) );
					}

					if( $blendMode = $itemSlideChild -> getAttribute( 'data-blendmode' ) )
					{
						$styleSeparatedWrap[ 'mix-blend-mode' ] = $blendMode;
						if( $blendMode == 'lighten' && !$bBlendModeLighten )
						{
							$styleSeparated[ 'opacity' ] = '0!important';
							$bBlendModeLighten = true;
						}
					}

					if( $srcSvg = $itemSlideChild -> getAttribute( 'data-svg_src' ) )
					{
						HtmlNd::AddRemoveAttrClass( $itemSlideChild, 'rs-layer' );

						$imgSrc = new ImgSrc( $srcSvg );
						$imgSrc -> Init( $ctxProcess );

						if( $itemSvg = HtmlNd::LoadXML( $imgSrc -> GetCont() ) )
							if( $itemSvg = $doc -> importNode( $itemSvg, true ) )
								$itemSlideChild -> appendChild( $itemSvg );

						unset( $imgSrc, $itemSvg );

						$attrSvgI = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-svgi' ), count( $aItemStyle ), '||' );
						if( isset( $attrSvgI[ 'c' ] ) )
						{
							$a = array(); foreach( $attrSvgI[ 'c' ] as $i => $v ) $a[ $i ][ 'fill' ] = $attrSvgI[ 'c' ][ $i ];
							$styleSeparatedSvg = array();
							_RevSld_SetStyleAttr( $styleSeparatedSvg, $aItemStyle, $itemChildSelector . ' > svg', $a );
						}
					}

					$actions = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-actions' ) );
					if( $actions )
					{
						HtmlNd::AddRemoveAttrClass( $itemSlideChild, array( 'rs-waction', 'rs-layer' ) );

						if( Gen::GetArrField( $actions, array( 'a' ) ) == 'startlayer' )
						{
							$idLayer = Gen::GetArrField( $actions, array( 'layer' ) );
							if( $idLayer && ( $itemLayerToHide = HtmlNd::FirstOfChildren( $xpath -> query( './/*[@id="' . $idLayer . '"]', $itemSlidesTmp ) ) ) )
								HtmlNd::AddRemoveAttrClass( $itemLayerToHide, 'js-lzl-ing-disp-none' );
						}

						if( Gen::GetArrField( $actions, array( 'o' ) ) == 'click' )
						{
							HtmlNd::AddRemoveAttrClass( $itemSlideChild, 'rs-wclickaction' );
						}
					}

					$frameChars = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-frame_0_chars' ) );
					if( $frameChars )
					{
						$aSizeWrap = array( array( 'width' => '100%' ) );

						$frameChars1 = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-frame_1_chars' ) );
						if( $frameChars1 && ( ( int )Gen::GetArrField( $frameChars1, array( 'd' ) ) > ( int )Gen::GetArrField( $frameChars, array( 'd' ) ) ) )
							$frameChars = $frameChars1;
						unset( $frameChars1 );

						$aLines = array( '' );
						for( $item111 = $itemSlideChild -> firstChild; $item111; $item111 = $item111 -> nextSibling )
						{
							if( $item111 -> nodeType == XML_ELEMENT_NODE && $item111 -> nodeName == 'br' )
							{
								$aLines[] = "\n";
								$aLines[] = '';
								continue;
							}

							$aLines[ count( $aLines ) - 1 ] .= $item111 -> textContent;
						}

						HtmlNd::CleanChildren( $itemSlideChild );

						foreach( $aLines as $aChars )
						{
							if( $aChars === "\n" )
								$aItemWord[] = $doc -> createTextNode( "\xC2\xA0" );
							else
							{
								$aChars = trim( $aChars );
								$aChars = function_exists( 'mb_str_split' ) ? mb_str_split( $aChars ) : str_split( $aChars );

								$aItemWord = array();
								foreach( $aChars as $i => $char )
								{
									if( !$aItemWord || $char === ' ' )
									{
										if( $char === ' ' )
											$aItemWord[] = $doc -> createTextNode( "\n" );
										$aItemWord[] = HtmlNd::CreateTag( $doc, 'div', array( 'class' => 'rs_splitted_words', 'style' => array( 'display' => 'inline-block' ) ) );
										if( $char === ' ' )
											continue;
									}

									$itemChar = HtmlNd::CreateTag( $doc, 'div', array( 'class' => 'rs_splitted_chars', 'style' => array(
										'display' => 'inline-block',
										'transform-origin' => '50% 50%',
										'transform' => 'translate3d(' . _RevSld_GetIdxPropVal( $frameChars, array( 'x', 'cyc' ), $i, '0' ) . 'px, ' . _RevSld_GetIdxPropVal( $frameChars, array( 'y', 'cyc' ), $i, '0' ) . 'px, ' . _RevSld_GetIdxPropVal( $frameChars, array( 'z', 'cyc' ), $i, '0' ) . 'px) rotate(' . _RevSld_GetIdxPropVal( $frameChars, array( 'rZ', 'cyc' ), $i, '0' ) . 'deg)'
									) ), array( $doc -> createTextNode( $char ) ) );
									$aItemWord[ count( $aItemWord ) - 1 ] -> appendChild( $itemChar );
								}
							}

							$itemSlideChild -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => 'rs_splitted_lines', 'style' => array( 'white-space' => null, 'text-align' => 'inherit' ) ), $aItemWord ) );
						}
					}

					$bubbleMorph = @json_decode( $itemSlideChild -> getAttribute( 'data-bubblemorph' ), true );
					if( $bubbleMorph )
					{

						static $g_aBubblePosRand = array(0=>array(0=>array(0=>82,1=>82,),1=>array(0=>92,1=>68,),2=>array(0=>66,1=>69,),3=>array(0=>30,1=>100,),),1=>array(0=>array(0=>86,1=>19,),1=>array(0=>73,1=>86,),2=>array(0=>16,1=>9,),3=>array(0=>12,1=>87,),),2=>array(0=>array(0=>37,1=>78,),1=>array(0=>27,1=>5,),2=>array(0=>55,1=>92,),3=>array(0=>40,1=>7,),),3=>array(0=>array(0=>87,1=>83,),1=>array(0=>44,1=>81,),2=>array(0=>46,1=>69,),3=>array(0=>69,1=>67,),),4=>array(0=>array(0=>75,1=>93,),1=>array(0=>67,1=>84,),2=>array(0=>42,1=>77,),3=>array(0=>14,1=>34,),),5=>array(0=>array(0=>8,1=>17,),1=>array(0=>4,1=>19,),2=>array(0=>29,1=>51,),3=>array(0=>60,1=>8,),),6=>array(0=>array(0=>87,1=>98,),1=>array(0=>49,1=>15,),2=>array(0=>89,1=>52,),3=>array(0=>21,1=>27,),),7=>array(0=>array(0=>38,1=>5,),1=>array(0=>27,1=>19,),2=>array(0=>7,1=>40,),3=>array(0=>7,1=>98,),),8=>array(0=>array(0=>43,1=>93,),1=>array(0=>24,1=>73,),2=>array(0=>66,1=>75,),3=>array(0=>14,1=>75,),),9=>array(0=>array(0=>99,1=>91,),1=>array(0=>38,1=>4,),2=>array(0=>64,1=>61,),3=>array(0=>78,1=>28,),),10=>array(0=>array(0=>1,1=>20,),1=>array(0=>46,1=>28,),2=>array(0=>42,1=>71,),3=>array(0=>23,1=>45,),),11=>array(0=>array(0=>54,1=>41,),1=>array(0=>39,1=>34,),2=>array(0=>21,1=>4,),3=>array(0=>85,1=>84,),),12=>array(0=>array(0=>1,1=>66,),1=>array(0=>61,1=>38,),2=>array(0=>82,1=>32,),3=>array(0=>12,1=>25,),),13=>array(0=>array(0=>29,1=>89,),1=>array(0=>79,1=>47,),2=>array(0=>63,1=>95,),3=>array(0=>78,1=>80,),),14=>array(0=>array(0=>48,1=>28,),1=>array(0=>82,1=>62,),2=>array(0=>56,1=>23,),3=>array(0=>74,1=>68,),),15=>array(0=>array(0=>22,1=>23,),1=>array(0=>20,1=>56,),2=>array(0=>87,1=>66,),3=>array(0=>93,1=>85,),),16=>array(0=>array(0=>40,1=>4,),1=>array(0=>97,1=>14,),2=>array(0=>76,1=>35,),3=>array(0=>97,1=>11,),),17=>array(0=>array(0=>42,1=>86,),1=>array(0=>87,1=>57,),2=>array(0=>16,1=>56,),3=>array(0=>73,1=>14,),),18=>array(0=>array(0=>7,1=>19,),1=>array(0=>43,1=>71,),2=>array(0=>16,1=>82,),3=>array(0=>62,1=>41,),),19=>array(0=>array(0=>95,1=>93,),1=>array(0=>29,1=>78,),2=>array(0=>45,1=>88,),3=>array(0=>10,1=>7,),),20=>array(0=>array(0=>40,1=>0,),1=>array(0=>14,1=>76,),2=>array(0=>40,1=>72,),3=>array(0=>53,1=>91,),),21=>array(0=>array(0=>19,1=>65,),1=>array(0=>58,1=>56,),2=>array(0=>85,1=>86,),3=>array(0=>1,1=>27,),),22=>array(0=>array(0=>14,1=>34,),1=>array(0=>91,1=>57,),2=>array(0=>49,1=>65,),3=>array(0=>60,1=>65,),),23=>array(0=>array(0=>95,1=>66,),1=>array(0=>100,1=>96,),2=>array(0=>46,1=>2,),3=>array(0=>55,1=>42,),),24=>array(0=>array(0=>19,1=>79,),1=>array(0=>60,1=>85,),2=>array(0=>99,1=>54,),3=>array(0=>79,1=>26,),),25=>array(0=>array(0=>66,1=>28,),1=>array(0=>62,1=>45,),2=>array(0=>81,1=>23,),3=>array(0=>52,1=>97,),),26=>array(0=>array(0=>76,1=>75,),1=>array(0=>95,1=>11,),2=>array(0=>3,1=>78,),3=>array(0=>61,1=>39,),),27=>array(0=>array(0=>53,1=>64,),1=>array(0=>19,1=>15,),2=>array(0=>78,1=>14,),3=>array(0=>67,1=>73,),),28=>array(0=>array(0=>1,1=>10,),1=>array(0=>58,1=>92,),2=>array(0=>54,1=>92,),3=>array(0=>82,1=>68,),),29=>array(0=>array(0=>48,1=>51,),1=>array(0=>8,1=>49,),2=>array(0=>48,1=>44,),3=>array(0=>34,1=>93,),),30=>array(0=>array(0=>94,1=>12,),1=>array(0=>65,1=>58,),2=>array(0=>52,1=>24,),3=>array(0=>67,1=>16,),),31=>array(0=>array(0=>18,1=>42,),1=>array(0=>77,1=>32,),2=>array(0=>97,1=>66,),3=>array(0=>33,1=>12,),),32=>array(0=>array(0=>37,1=>91,),1=>array(0=>44,1=>47,),2=>array(0=>89,1=>84,),3=>array(0=>20,1=>57,),),33=>array(0=>array(0=>5,1=>17,),1=>array(0=>71,1=>8,),2=>array(0=>75,1=>48,),3=>array(0=>29,1=>20,),),34=>array(0=>array(0=>25,1=>24,),1=>array(0=>11,1=>99,),2=>array(0=>98,1=>87,),3=>array(0=>76,1=>18,),),35=>array(0=>array(0=>10,1=>72,),1=>array(0=>48,1=>30,),2=>array(0=>49,1=>99,),3=>array(0=>47,1=>62,),),36=>array(0=>array(0=>30,1=>33,),1=>array(0=>67,1=>38,),2=>array(0=>61,1=>75,),3=>array(0=>40,1=>96,),),37=>array(0=>array(0=>81,1=>85,),1=>array(0=>30,1=>86,),2=>array(0=>14,1=>54,),3=>array(0=>9,1=>49,),),38=>array(0=>array(0=>94,1=>29,),1=>array(0=>34,1=>33,),2=>array(0=>45,1=>32,),3=>array(0=>38,1=>82,),),39=>array(0=>array(0=>98,1=>17,),1=>array(0=>40,1=>11,),2=>array(0=>5,1=>12,),3=>array(0=>26,1=>77,),),40=>array(0=>array(0=>81,1=>37,),1=>array(0=>58,1=>86,),2=>array(0=>40,1=>60,),3=>array(0=>10,1=>63,),),41=>array(0=>array(0=>0,1=>54,),1=>array(0=>90,1=>7,),2=>array(0=>22,1=>78,),3=>array(0=>3,1=>70,),),42=>array(0=>array(0=>87,1=>97,),1=>array(0=>50,1=>54,),2=>array(0=>85,1=>20,),3=>array(0=>82,1=>10,),),43=>array(0=>array(0=>56,1=>33,),1=>array(0=>92,1=>92,),2=>array(0=>23,1=>53,),3=>array(0=>82,1=>61,),),44=>array(0=>array(0=>83,1=>81,),1=>array(0=>78,1=>47,),2=>array(0=>29,1=>46,),3=>array(0=>3,1=>49,),),45=>array(0=>array(0=>53,1=>100,),1=>array(0=>59,1=>25,),2=>array(0=>47,1=>78,),3=>array(0=>83,1=>14,),),46=>array(0=>array(0=>30,1=>100,),1=>array(0=>34,1=>86,),2=>array(0=>22,1=>87,),3=>array(0=>69,1=>7,),),47=>array(0=>array(0=>97,1=>9,),1=>array(0=>61,1=>29,),2=>array(0=>50,1=>89,),3=>array(0=>83,1=>30,),),48=>array(0=>array(0=>75,1=>44,),1=>array(0=>71,1=>58,),2=>array(0=>62,1=>55,),3=>array(0=>88,1=>92,),),49=>array(0=>array(0=>77,1=>82,),1=>array(0=>68,1=>17,),2=>array(0=>86,1=>62,),3=>array(0=>28,1=>8,),),50=>array(0=>array(0=>70,1=>97,),1=>array(0=>5,1=>63,),2=>array(0=>65,1=>39,),3=>array(0=>52,1=>47,),),51=>array(0=>array(0=>37,1=>50,),1=>array(0=>36,1=>87,),2=>array(0=>44,1=>14,),3=>array(0=>79,1=>49,),),52=>array(0=>array(0=>32,1=>77,),1=>array(0=>95,1=>13,),2=>array(0=>100,1=>55,),3=>array(0=>85,1=>31,),),53=>array(0=>array(0=>45,1=>17,),1=>array(0=>91,1=>73,),2=>array(0=>84,1=>81,),3=>array(0=>28,1=>14,),),54=>array(0=>array(0=>71,1=>9,),1=>array(0=>60,1=>38,),2=>array(0=>50,1=>59,),3=>array(0=>61,1=>75,),),55=>array(0=>array(0=>66,1=>10,),1=>array(0=>71,1=>27,),2=>array(0=>47,1=>10,),3=>array(0=>78,1=>10,),),56=>array(0=>array(0=>50,1=>75,),1=>array(0=>38,1=>61,),2=>array(0=>11,1=>15,),3=>array(0=>100,1=>8,),),57=>array(0=>array(0=>13,1=>42,),1=>array(0=>55,1=>61,),2=>array(0=>97,1=>26,),3=>array(0=>89,1=>21,),),58=>array(0=>array(0=>50,1=>37,),1=>array(0=>0,1=>90,),2=>array(0=>48,1=>74,),3=>array(0=>95,1=>74,),),59=>array(0=>array(0=>50,1=>8,),1=>array(0=>76,1=>28,),2=>array(0=>54,1=>91,),3=>array(0=>53,1=>62,),),60=>array(0=>array(0=>77,1=>82,),1=>array(0=>30,1=>70,),2=>array(0=>53,1=>0,),3=>array(0=>35,1=>11,),),61=>array(0=>array(0=>80,1=>25,),1=>array(0=>13,1=>13,),2=>array(0=>80,1=>70,),3=>array(0=>34,1=>72,),),62=>array(0=>array(0=>39,1=>80,),1=>array(0=>62,1=>28,),2=>array(0=>83,1=>85,),3=>array(0=>8,1=>2,),),63=>array(0=>array(0=>12,1=>10,),1=>array(0=>60,1=>38,),2=>array(0=>61,1=>70,),3=>array(0=>90,1=>10,),),64=>array(0=>array(0=>81,1=>69,),1=>array(0=>93,1=>94,),2=>array(0=>94,1=>7,),3=>array(0=>35,1=>57,),),65=>array(0=>array(0=>78,1=>29,),1=>array(0=>47,1=>55,),2=>array(0=>40,1=>88,),3=>array(0=>54,1=>53,),),66=>array(0=>array(0=>38,1=>53,),1=>array(0=>47,1=>30,),2=>array(0=>25,1=>100,),3=>array(0=>21,1=>72,),),67=>array(0=>array(0=>31,1=>58,),1=>array(0=>53,1=>21,),2=>array(0=>56,1=>29,),3=>array(0=>92,1=>17,),),68=>array(0=>array(0=>34,1=>88,),1=>array(0=>17,1=>61,),2=>array(0=>28,1=>61,),3=>array(0=>52,1=>53,),),69=>array(0=>array(0=>73,1=>60,),1=>array(0=>19,1=>79,),2=>array(0=>90,1=>49,),3=>array(0=>20,1=>93,),),70=>array(0=>array(0=>21,1=>46,),1=>array(0=>47,1=>99,),2=>array(0=>31,1=>70,),3=>array(0=>84,1=>92,),),71=>array(0=>array(0=>4,1=>32,),1=>array(0=>25,1=>36,),2=>array(0=>91,1=>55,),3=>array(0=>31,1=>30,),),72=>array(0=>array(0=>38,1=>40,),1=>array(0=>52,1=>92,),2=>array(0=>47,1=>92,),3=>array(0=>7,1=>68,),),73=>array(0=>array(0=>77,1=>87,),1=>array(0=>9,1=>10,),2=>array(0=>80,1=>47,),3=>array(0=>16,1=>60,),),74=>array(0=>array(0=>11,1=>100,),1=>array(0=>96,1=>67,),2=>array(0=>4,1=>1,),3=>array(0=>68,1=>57,),),75=>array(0=>array(0=>47,1=>7,),1=>array(0=>19,1=>93,),2=>array(0=>88,1=>71,),3=>array(0=>29,1=>68,),),76=>array(0=>array(0=>20,1=>4,),1=>array(0=>21,1=>94,),2=>array(0=>59,1=>80,),3=>array(0=>77,1=>8,),),77=>array(0=>array(0=>18,1=>65,),1=>array(0=>35,1=>24,),2=>array(0=>65,1=>68,),3=>array(0=>37,1=>85,),),78=>array(0=>array(0=>50,1=>16,),1=>array(0=>80,1=>34,),2=>array(0=>16,1=>72,),3=>array(0=>98,1=>33,),),79=>array(0=>array(0=>64,1=>40,),1=>array(0=>74,1=>65,),2=>array(0=>35,1=>29,),3=>array(0=>70,1=>75,),),80=>array(0=>array(0=>53,1=>59,),1=>array(0=>49,1=>56,),2=>array(0=>88,1=>20,),3=>array(0=>35,1=>49,),),81=>array(0=>array(0=>51,1=>58,),1=>array(0=>67,1=>75,),2=>array(0=>70,1=>61,),3=>array(0=>37,1=>35,),),82=>array(0=>array(0=>30,1=>54,),1=>array(0=>46,1=>93,),2=>array(0=>97,1=>33,),3=>array(0=>92,1=>46,),),83=>array(0=>array(0=>53,1=>28,),1=>array(0=>46,1=>43,),2=>array(0=>12,1=>32,),3=>array(0=>8,1=>58,),),84=>array(0=>array(0=>14,1=>28,),1=>array(0=>23,1=>69,),2=>array(0=>52,1=>36,),3=>array(0=>59,1=>66,),),85=>array(0=>array(0=>17,1=>44,),1=>array(0=>46,1=>16,),2=>array(0=>27,1=>26,),3=>array(0=>90,1=>63,),),86=>array(0=>array(0=>23,1=>25,),1=>array(0=>17,1=>64,),2=>array(0=>76,1=>87,),3=>array(0=>7,1=>100,),),87=>array(0=>array(0=>50,1=>30,),1=>array(0=>41,1=>34,),2=>array(0=>25,1=>32,),3=>array(0=>86,1=>34,),),88=>array(0=>array(0=>93,1=>62,),1=>array(0=>74,1=>41,),2=>array(0=>51,1=>2,),3=>array(0=>86,1=>32,),),89=>array(0=>array(0=>7,1=>67,),1=>array(0=>58,1=>0,),2=>array(0=>19,1=>57,),3=>array(0=>92,1=>92,),),90=>array(0=>array(0=>17,1=>13,),1=>array(0=>87,1=>73,),2=>array(0=>91,1=>14,),3=>array(0=>64,1=>18,),),91=>array(0=>array(0=>70,1=>30,),1=>array(0=>78,1=>71,),2=>array(0=>87,1=>17,),3=>array(0=>76,1=>78,),),92=>array(0=>array(0=>18,1=>85,),1=>array(0=>29,1=>49,),2=>array(0=>94,1=>76,),3=>array(0=>85,1=>42,),),93=>array(0=>array(0=>2,1=>22,),1=>array(0=>51,1=>12,),2=>array(0=>13,1=>65,),3=>array(0=>14,1=>66,),),94=>array(0=>array(0=>94,1=>63,),1=>array(0=>87,1=>82,),2=>array(0=>17,1=>56,),3=>array(0=>3,1=>68,),),95=>array(0=>array(0=>75,1=>51,),1=>array(0=>98,1=>96,),2=>array(0=>18,1=>51,),3=>array(0=>7,1=>35,),),96=>array(0=>array(0=>32,1=>96,),1=>array(0=>65,1=>14,),2=>array(0=>5,1=>41,),3=>array(0=>31,1=>32,),),97=>array(0=>array(0=>26,1=>61,),1=>array(0=>27,1=>74,),2=>array(0=>78,1=>47,),3=>array(0=>10,1=>83,),),98=>array(0=>array(0=>64,1=>46,),1=>array(0=>12,1=>89,),2=>array(0=>0,1=>7,),3=>array(0=>69,1=>25,),),99=>array(0=>array(0=>65,1=>27,),1=>array(0=>91,1=>39,),2=>array(0=>87,1=>10,),3=>array(0=>57,1=>17,),),100=>array(0=>array(0=>38,1=>65,),1=>array(0=>5,1=>40,),2=>array(0=>64,1=>43,),3=>array(0=>34,1=>97,),),101=>array(0=>array(0=>12,1=>33,),1=>array(0=>23,1=>33,),2=>array(0=>15,1=>41,),3=>array(0=>94,1=>28,),),102=>array(0=>array(0=>2,1=>37,),1=>array(0=>42,1=>8,),2=>array(0=>40,1=>27,),3=>array(0=>97,1=>54,),),103=>array(0=>array(0=>45,1=>99,),1=>array(0=>24,1=>76,),2=>array(0=>18,1=>26,),3=>array(0=>37,1=>44,),),104=>array(0=>array(0=>69,1=>5,),1=>array(0=>47,1=>75,),2=>array(0=>79,1=>31,),3=>array(0=>96,1=>36,),),105=>array(0=>array(0=>30,1=>75,),1=>array(0=>66,1=>51,),2=>array(0=>92,1=>49,),3=>array(0=>52,1=>18,),),106=>array(0=>array(0=>54,1=>32,),1=>array(0=>32,1=>12,),2=>array(0=>33,1=>29,),3=>array(0=>7,1=>40,),),107=>array(0=>array(0=>25,1=>52,),1=>array(0=>96,1=>87,),2=>array(0=>57,1=>60,),3=>array(0=>64,1=>6,),),108=>array(0=>array(0=>77,1=>98,),1=>array(0=>93,1=>1,),2=>array(0=>61,1=>76,),3=>array(0=>8,1=>58,),),109=>array(0=>array(0=>75,1=>37,),1=>array(0=>85,1=>10,),2=>array(0=>27,1=>27,),3=>array(0=>39,1=>92,),),110=>array(0=>array(0=>5,1=>85,),1=>array(0=>91,1=>33,),2=>array(0=>98,1=>6,),3=>array(0=>60,1=>33,),),111=>array(0=>array(0=>38,1=>64,),1=>array(0=>31,1=>49,),2=>array(0=>48,1=>69,),3=>array(0=>57,1=>7,),),112=>array(0=>array(0=>64,1=>28,),1=>array(0=>24,1=>2,),2=>array(0=>36,1=>19,),3=>array(0=>42,1=>63,),),113=>array(0=>array(0=>1,1=>1,),1=>array(0=>72,1=>95,),2=>array(0=>70,1=>3,),3=>array(0=>83,1=>71,),),114=>array(0=>array(0=>33,1=>11,),1=>array(0=>35,1=>99,),2=>array(0=>31,1=>62,),3=>array(0=>69,1=>58,),),115=>array(0=>array(0=>95,1=>9,),1=>array(0=>40,1=>36,),2=>array(0=>49,1=>99,),3=>array(0=>0,1=>69,),),116=>array(0=>array(0=>24,1=>70,),1=>array(0=>11,1=>68,),2=>array(0=>41,1=>8,),3=>array(0=>83,1=>45,),),117=>array(0=>array(0=>71,1=>94,),1=>array(0=>97,1=>90,),2=>array(0=>38,1=>87,),3=>array(0=>100,1=>51,),),118=>array(0=>array(0=>17,1=>57,),1=>array(0=>20,1=>88,),2=>array(0=>28,1=>41,),3=>array(0=>36,1=>95,),),119=>array(0=>array(0=>94,1=>33,),1=>array(0=>58,1=>73,),2=>array(0=>75,1=>64,),3=>array(0=>24,1=>10,),),120=>array(0=>array(0=>54,1=>12,),1=>array(0=>59,1=>56,),2=>array(0=>98,1=>61,),3=>array(0=>39,1=>6,),),121=>array(0=>array(0=>50,1=>36,),1=>array(0=>9,1=>87,),2=>array(0=>74,1=>34,),3=>array(0=>75,1=>40,),),122=>array(0=>array(0=>3,1=>71,),1=>array(0=>92,1=>3,),2=>array(0=>47,1=>73,),3=>array(0=>48,1=>80,),),123=>array(0=>array(0=>64,1=>8,),1=>array(0=>58,1=>90,),2=>array(0=>85,1=>81,),3=>array(0=>72,1=>22,),),124=>array(0=>array(0=>48,1=>72,),1=>array(0=>69,1=>11,),2=>array(0=>5,1=>69,),3=>array(0=>82,1=>16,),),125=>array(0=>array(0=>99,1=>49,),1=>array(0=>47,1=>17,),2=>array(0=>74,1=>98,),3=>array(0=>56,1=>41,),),126=>array(0=>array(0=>89,1=>9,),1=>array(0=>91,1=>0,),2=>array(0=>53,1=>90,),3=>array(0=>12,1=>30,),),127=>array(0=>array(0=>98,1=>22,),1=>array(0=>2,1=>27,),2=>array(0=>84,1=>10,),3=>array(0=>73,1=>90,),),128=>array(0=>array(0=>17,1=>66,),1=>array(0=>6,1=>15,),2=>array(0=>23,1=>91,),3=>array(0=>58,1=>44,),),129=>array(0=>array(0=>79,1=>24,),1=>array(0=>7,1=>87,),2=>array(0=>41,1=>90,),3=>array(0=>33,1=>96,),),130=>array(0=>array(0=>89,1=>10,),1=>array(0=>32,1=>99,),2=>array(0=>35,1=>7,),3=>array(0=>72,1=>51,),),131=>array(0=>array(0=>44,1=>43,),1=>array(0=>32,1=>34,),2=>array(0=>10,1=>5,),3=>array(0=>49,1=>40,),),132=>array(0=>array(0=>63,1=>18,),1=>array(0=>79,1=>77,),2=>array(0=>78,1=>12,),3=>array(0=>61,1=>23,),),133=>array(0=>array(0=>39,1=>21,),1=>array(0=>5,1=>8,),2=>array(0=>41,1=>89,),3=>array(0=>63,1=>19,),),134=>array(0=>array(0=>5,1=>73,),1=>array(0=>67,1=>32,),2=>array(0=>7,1=>91,),3=>array(0=>44,1=>5,),),135=>array(0=>array(0=>5,1=>44,),1=>array(0=>87,1=>62,),2=>array(0=>38,1=>79,),3=>array(0=>63,1=>54,),),136=>array(0=>array(0=>56,1=>5,),1=>array(0=>81,1=>68,),2=>array(0=>10,1=>29,),3=>array(0=>100,1=>36,),),137=>array(0=>array(0=>92,1=>71,),1=>array(0=>90,1=>9,),2=>array(0=>65,1=>76,),3=>array(0=>26,1=>87,),),138=>array(0=>array(0=>11,1=>48,),1=>array(0=>56,1=>91,),2=>array(0=>93,1=>64,),3=>array(0=>99,1=>2,),),139=>array(0=>array(0=>7,1=>26,),1=>array(0=>60,1=>74,),2=>array(0=>65,1=>89,),3=>array(0=>76,1=>26,),),140=>array(0=>array(0=>3,1=>31,),1=>array(0=>48,1=>41,),2=>array(0=>64,1=>64,),3=>array(0=>63,1=>7,),),141=>array(0=>array(0=>54,1=>15,),1=>array(0=>94,1=>58,),2=>array(0=>61,1=>22,),3=>array(0=>33,1=>81,),),142=>array(0=>array(0=>86,1=>46,),1=>array(0=>76,1=>8,),2=>array(0=>15,1=>20,),3=>array(0=>65,1=>66,),),143=>array(0=>array(0=>80,1=>84,),1=>array(0=>56,1=>29,),2=>array(0=>75,1=>36,),3=>array(0=>73,1=>86,),),144=>array(0=>array(0=>71,1=>16,),1=>array(0=>13,1=>36,),2=>array(0=>4,1=>16,),3=>array(0=>72,1=>9,),),145=>array(0=>array(0=>55,1=>88,),1=>array(0=>4,1=>58,),2=>array(0=>19,1=>84,),3=>array(0=>62,1=>25,),),146=>array(0=>array(0=>73,1=>38,),1=>array(0=>43,1=>13,),2=>array(0=>30,1=>4,),3=>array(0=>73,1=>79,),),147=>array(0=>array(0=>17,1=>54,),1=>array(0=>33,1=>78,),2=>array(0=>14,1=>13,),3=>array(0=>97,1=>65,),),148=>array(0=>array(0=>27,1=>5,),1=>array(0=>15,1=>39,),2=>array(0=>38,1=>72,),3=>array(0=>18,1=>11,),),149=>array(0=>array(0=>78,1=>99,),1=>array(0=>54,1=>20,),2=>array(0=>71,1=>8,),3=>array(0=>4,1=>64,),),150=>array(0=>array(0=>58,1=>51,),1=>array(0=>69,1=>44,),2=>array(0=>33,1=>19,),3=>array(0=>67,1=>88,),),151=>array(0=>array(0=>69,1=>33,),1=>array(0=>22,1=>64,),2=>array(0=>30,1=>61,),3=>array(0=>75,1=>96,),),152=>array(0=>array(0=>38,1=>89,),1=>array(0=>96,1=>25,),2=>array(0=>43,1=>83,),3=>array(0=>20,1=>30,),),153=>array(0=>array(0=>87,1=>44,),1=>array(0=>84,1=>51,),2=>array(0=>1,1=>94,),3=>array(0=>92,1=>88,),),154=>array(0=>array(0=>43,1=>46,),1=>array(0=>37,1=>90,),2=>array(0=>5,1=>13,),3=>array(0=>58,1=>85,),),155=>array(0=>array(0=>37,1=>57,),1=>array(0=>98,1=>75,),2=>array(0=>90,1=>62,),3=>array(0=>3,1=>61,),),156=>array(0=>array(0=>25,1=>68,),1=>array(0=>30,1=>36,),2=>array(0=>10,1=>48,),3=>array(0=>44,1=>15,),),157=>array(0=>array(0=>8,1=>22,),1=>array(0=>91,1=>46,),2=>array(0=>80,1=>64,),3=>array(0=>72,1=>62,),),158=>array(0=>array(0=>96,1=>60,),1=>array(0=>89,1=>53,),2=>array(0=>78,1=>73,),3=>array(0=>70,1=>27,),),159=>array(0=>array(0=>42,1=>65,),1=>array(0=>51,1=>77,),2=>array(0=>98,1=>36,),3=>array(0=>53,1=>67,),),160=>array(0=>array(0=>19,1=>2,),1=>array(0=>70,1=>54,),2=>array(0=>45,1=>2,),3=>array(0=>1,1=>0,),),161=>array(0=>array(0=>3,1=>99,),1=>array(0=>58,1=>5,),2=>array(0=>26,1=>45,),3=>array(0=>15,1=>33,),),162=>array(0=>array(0=>88,1=>9,),1=>array(0=>50,1=>97,),2=>array(0=>46,1=>27,),3=>array(0=>50,1=>45,),),163=>array(0=>array(0=>94,1=>24,),1=>array(0=>62,1=>40,),2=>array(0=>52,1=>72,),3=>array(0=>10,1=>13,),),164=>array(0=>array(0=>33,1=>14,),1=>array(0=>6,1=>31,),2=>array(0=>16,1=>36,),3=>array(0=>20,1=>72,),),165=>array(0=>array(0=>43,1=>78,),1=>array(0=>76,1=>67,),2=>array(0=>49,1=>26,),3=>array(0=>94,1=>15,),),166=>array(0=>array(0=>5,1=>65,),1=>array(0=>11,1=>82,),2=>array(0=>20,1=>37,),3=>array(0=>12,1=>15,),),167=>array(0=>array(0=>47,1=>26,),1=>array(0=>97,1=>70,),2=>array(0=>22,1=>62,),3=>array(0=>60,1=>66,),),168=>array(0=>array(0=>39,1=>21,),1=>array(0=>23,1=>55,),2=>array(0=>76,1=>4,),3=>array(0=>76,1=>66,),),169=>array(0=>array(0=>77,1=>85,),1=>array(0=>77,1=>5,),2=>array(0=>82,1=>61,),3=>array(0=>7,1=>82,),),170=>array(0=>array(0=>16,1=>29,),1=>array(0=>54,1=>24,),2=>array(0=>60,1=>0,),3=>array(0=>12,1=>72,),),171=>array(0=>array(0=>81,1=>29,),1=>array(0=>62,1=>30,),2=>array(0=>11,1=>17,),3=>array(0=>69,1=>53,),),172=>array(0=>array(0=>92,1=>95,),1=>array(0=>2,1=>58,),2=>array(0=>1,1=>82,),3=>array(0=>73,1=>13,),),173=>array(0=>array(0=>33,1=>19,),1=>array(0=>90,1=>42,),2=>array(0=>32,1=>72,),3=>array(0=>25,1=>72,),),174=>array(0=>array(0=>19,1=>96,),1=>array(0=>60,1=>31,),2=>array(0=>7,1=>96,),3=>array(0=>11,1=>69,),),175=>array(0=>array(0=>51,1=>41,),1=>array(0=>27,1=>97,),2=>array(0=>39,1=>24,),3=>array(0=>85,1=>41,),),176=>array(0=>array(0=>48,1=>28,),1=>array(0=>71,1=>62,),2=>array(0=>22,1=>14,),3=>array(0=>69,1=>92,),),177=>array(0=>array(0=>5,1=>25,),1=>array(0=>18,1=>48,),2=>array(0=>2,1=>95,),3=>array(0=>3,1=>59,),),178=>array(0=>array(0=>96,1=>37,),1=>array(0=>50,1=>90,),2=>array(0=>27,1=>49,),3=>array(0=>3,1=>71,),),179=>array(0=>array(0=>74,1=>9,),1=>array(0=>55,1=>12,),2=>array(0=>19,1=>5,),3=>array(0=>97,1=>27,),),180=>array(0=>array(0=>33,1=>73,),1=>array(0=>15,1=>43,),2=>array(0=>88,1=>81,),3=>array(0=>21,1=>82,),),181=>array(0=>array(0=>39,1=>49,),1=>array(0=>73,1=>10,),2=>array(0=>47,1=>96,),3=>array(0=>37,1=>54,),),182=>array(0=>array(0=>21,1=>16,),1=>array(0=>54,1=>99,),2=>array(0=>84,1=>33,),3=>array(0=>97,1=>13,),),183=>array(0=>array(0=>34,1=>13,),1=>array(0=>78,1=>88,),2=>array(0=>42,1=>19,),3=>array(0=>57,1=>44,),),184=>array(0=>array(0=>18,1=>82,),1=>array(0=>12,1=>100,),2=>array(0=>73,1=>26,),3=>array(0=>60,1=>43,),),185=>array(0=>array(0=>66,1=>71,),1=>array(0=>71,1=>26,),2=>array(0=>15,1=>100,),3=>array(0=>24,1=>93,),),186=>array(0=>array(0=>95,1=>73,),1=>array(0=>74,1=>79,),2=>array(0=>22,1=>26,),3=>array(0=>58,1=>64,),),187=>array(0=>array(0=>94,1=>22,),1=>array(0=>80,1=>98,),2=>array(0=>48,1=>62,),3=>array(0=>92,1=>2,),),188=>array(0=>array(0=>63,1=>8,),1=>array(0=>40,1=>81,),2=>array(0=>83,1=>43,),3=>array(0=>29,1=>53,),),189=>array(0=>array(0=>18,1=>66,),1=>array(0=>26,1=>82,),2=>array(0=>93,1=>70,),3=>array(0=>29,1=>66,),),190=>array(0=>array(0=>61,1=>0,),1=>array(0=>24,1=>57,),2=>array(0=>31,1=>94,),3=>array(0=>34,1=>83,),),191=>array(0=>array(0=>31,1=>66,),1=>array(0=>31,1=>87,),2=>array(0=>62,1=>92,),3=>array(0=>2,1=>66,),),192=>array(0=>array(0=>28,1=>54,),1=>array(0=>65,1=>36,),2=>array(0=>90,1=>36,),3=>array(0=>76,1=>6,),),193=>array(0=>array(0=>16,1=>74,),1=>array(0=>69,1=>24,),2=>array(0=>34,1=>39,),3=>array(0=>32,1=>76,),),194=>array(0=>array(0=>89,1=>100,),1=>array(0=>49,1=>37,),2=>array(0=>40,1=>10,),3=>array(0=>67,1=>98,),),195=>array(0=>array(0=>59,1=>63,),1=>array(0=>71,1=>46,),2=>array(0=>1,1=>18,),3=>array(0=>53,1=>33,),),196=>array(0=>array(0=>12,1=>2,),1=>array(0=>81,1=>8,),2=>array(0=>36,1=>30,),3=>array(0=>62,1=>14,),),197=>array(0=>array(0=>73,1=>55,),1=>array(0=>30,1=>8,),2=>array(0=>59,1=>16,),3=>array(0=>54,1=>91,),),198=>array(0=>array(0=>34,1=>28,),1=>array(0=>90,1=>49,),2=>array(0=>100,1=>40,),3=>array(0=>80,1=>61,),),199=>array(0=>array(0=>25,1=>13,),1=>array(0=>69,1=>38,),2=>array(0=>99,1=>96,),3=>array(0=>31,1=>62,),),200=>array(0=>array(0=>16,1=>84,),1=>array(0=>0,1=>95,),2=>array(0=>58,1=>63,),3=>array(0=>59,1=>7,),),201=>array(0=>array(0=>51,1=>11,),1=>array(0=>74,1=>45,),2=>array(0=>39,1=>32,),3=>array(0=>24,1=>37,),),202=>array(0=>array(0=>34,1=>39,),1=>array(0=>83,1=>28,),2=>array(0=>52,1=>32,),3=>array(0=>46,1=>40,),),203=>array(0=>array(0=>45,1=>80,),1=>array(0=>99,1=>96,),2=>array(0=>51,1=>74,),3=>array(0=>8,1=>65,),),204=>array(0=>array(0=>3,1=>42,),1=>array(0=>78,1=>65,),2=>array(0=>84,1=>20,),3=>array(0=>62,1=>99,),),205=>array(0=>array(0=>32,1=>62,),1=>array(0=>56,1=>50,),2=>array(0=>60,1=>69,),3=>array(0=>10,1=>27,),),206=>array(0=>array(0=>40,1=>94,),1=>array(0=>49,1=>81,),2=>array(0=>94,1=>30,),3=>array(0=>54,1=>56,),),207=>array(0=>array(0=>40,1=>24,),1=>array(0=>48,1=>71,),2=>array(0=>62,1=>39,),3=>array(0=>44,1=>60,),),208=>array(0=>array(0=>18,1=>60,),1=>array(0=>78,1=>99,),2=>array(0=>9,1=>59,),3=>array(0=>74,1=>55,),),209=>array(0=>array(0=>83,1=>92,),1=>array(0=>83,1=>1,),2=>array(0=>42,1=>33,),3=>array(0=>10,1=>56,),),210=>array(0=>array(0=>86,1=>82,),1=>array(0=>70,1=>29,),2=>array(0=>89,1=>49,),3=>array(0=>47,1=>81,),),211=>array(0=>array(0=>0,1=>75,),1=>array(0=>58,1=>85,),2=>array(0=>66,1=>43,),3=>array(0=>86,1=>18,),),212=>array(0=>array(0=>85,1=>42,),1=>array(0=>6,1=>26,),2=>array(0=>58,1=>42,),3=>array(0=>0,1=>81,),),213=>array(0=>array(0=>76,1=>4,),1=>array(0=>94,1=>94,),2=>array(0=>85,1=>29,),3=>array(0=>97,1=>3,),),214=>array(0=>array(0=>67,1=>78,),1=>array(0=>94,1=>67,),2=>array(0=>13,1=>46,),3=>array(0=>64,1=>43,),),215=>array(0=>array(0=>96,1=>1,),1=>array(0=>63,1=>58,),2=>array(0=>50,1=>67,),3=>array(0=>88,1=>33,),),216=>array(0=>array(0=>43,1=>49,),1=>array(0=>55,1=>17,),2=>array(0=>92,1=>65,),3=>array(0=>0,1=>89,),),217=>array(0=>array(0=>3,1=>48,),1=>array(0=>45,1=>40,),2=>array(0=>3,1=>65,),3=>array(0=>97,1=>35,),),218=>array(0=>array(0=>51,1=>61,),1=>array(0=>82,1=>27,),2=>array(0=>93,1=>60,),3=>array(0=>0,1=>80,),),219=>array(0=>array(0=>44,1=>63,),1=>array(0=>51,1=>48,),2=>array(0=>98,1=>71,),3=>array(0=>17,1=>32,),),220=>array(0=>array(0=>20,1=>39,),1=>array(0=>49,1=>11,),2=>array(0=>56,1=>72,),3=>array(0=>18,1=>26,),),221=>array(0=>array(0=>74,1=>11,),1=>array(0=>19,1=>87,),2=>array(0=>79,1=>16,),3=>array(0=>80,1=>72,),),222=>array(0=>array(0=>31,1=>98,),1=>array(0=>32,1=>58,),2=>array(0=>99,1=>86,),3=>array(0=>27,1=>95,),),223=>array(0=>array(0=>20,1=>16,),1=>array(0=>68,1=>16,),2=>array(0=>81,1=>23,),3=>array(0=>83,1=>24,),),224=>array(0=>array(0=>79,1=>38,),1=>array(0=>45,1=>10,),2=>array(0=>4,1=>70,),3=>array(0=>36,1=>42,),),225=>array(0=>array(0=>82,1=>33,),1=>array(0=>76,1=>86,),2=>array(0=>64,1=>74,),3=>array(0=>13,1=>52,),),226=>array(0=>array(0=>9,1=>49,),1=>array(0=>78,1=>78,),2=>array(0=>71,1=>93,),3=>array(0=>27,1=>8,),),227=>array(0=>array(0=>14,1=>66,),1=>array(0=>84,1=>54,),2=>array(0=>22,1=>51,),3=>array(0=>9,1=>63,),),228=>array(0=>array(0=>75,1=>15,),1=>array(0=>92,1=>88,),2=>array(0=>29,1=>7,),3=>array(0=>68,1=>41,),),229=>array(0=>array(0=>75,1=>26,),1=>array(0=>74,1=>24,),2=>array(0=>25,1=>92,),3=>array(0=>75,1=>68,),),230=>array(0=>array(0=>78,1=>82,),1=>array(0=>89,1=>45,),2=>array(0=>76,1=>70,),3=>array(0=>45,1=>27,),),231=>array(0=>array(0=>62,1=>22,),1=>array(0=>88,1=>20,),2=>array(0=>15,1=>6,),3=>array(0=>71,1=>69,),),232=>array(0=>array(0=>69,1=>63,),1=>array(0=>77,1=>70,),2=>array(0=>8,1=>74,),3=>array(0=>41,1=>99,),),233=>array(0=>array(0=>52,1=>76,),1=>array(0=>57,1=>0,),2=>array(0=>55,1=>55,),3=>array(0=>15,1=>36,),),234=>array(0=>array(0=>41,1=>5,),1=>array(0=>5,1=>7,),2=>array(0=>79,1=>4,),3=>array(0=>24,1=>7,),),235=>array(0=>array(0=>52,1=>16,),1=>array(0=>19,1=>65,),2=>array(0=>26,1=>43,),3=>array(0=>80,1=>60,),),236=>array(0=>array(0=>25,1=>56,),1=>array(0=>97,1=>47,),2=>array(0=>44,1=>17,),3=>array(0=>90,1=>80,),),237=>array(0=>array(0=>60,1=>96,),1=>array(0=>79,1=>28,),2=>array(0=>72,1=>62,),3=>array(0=>86,1=>73,),),238=>array(0=>array(0=>72,1=>65,),1=>array(0=>63,1=>21,),2=>array(0=>86,1=>57,),3=>array(0=>37,1=>86,),),239=>array(0=>array(0=>75,1=>58,),1=>array(0=>65,1=>66,),2=>array(0=>33,1=>69,),3=>array(0=>82,1=>7,),),240=>array(0=>array(0=>1,1=>29,),1=>array(0=>44,1=>30,),2=>array(0=>36,1=>64,),3=>array(0=>60,1=>83,),),241=>array(0=>array(0=>87,1=>36,),1=>array(0=>86,1=>84,),2=>array(0=>24,1=>84,),3=>array(0=>50,1=>37,),),242=>array(0=>array(0=>84,1=>39,),1=>array(0=>67,1=>14,),2=>array(0=>84,1=>32,),3=>array(0=>33,1=>0,),),243=>array(0=>array(0=>27,1=>22,),1=>array(0=>21,1=>46,),2=>array(0=>26,1=>85,),3=>array(0=>83,1=>19,),),244=>array(0=>array(0=>72,1=>36,),1=>array(0=>80,1=>78,),2=>array(0=>56,1=>25,),3=>array(0=>38,1=>67,),),245=>array(0=>array(0=>92,1=>53,),1=>array(0=>5,1=>31,),2=>array(0=>77,1=>74,),3=>array(0=>91,1=>46,),),246=>array(0=>array(0=>84,1=>78,),1=>array(0=>18,1=>45,),2=>array(0=>56,1=>89,),3=>array(0=>99,1=>21,),),247=>array(0=>array(0=>37,1=>67,),1=>array(0=>52,1=>30,),2=>array(0=>3,1=>15,),3=>array(0=>55,1=>82,),),248=>array(0=>array(0=>97,1=>31,),1=>array(0=>44,1=>60,),2=>array(0=>17,1=>86,),3=>array(0=>56,1=>95,),),249=>array(0=>array(0=>13,1=>52,),1=>array(0=>33,1=>56,),2=>array(0=>44,1=>24,),3=>array(0=>55,1=>1,),),250=>array(0=>array(0=>4,1=>87,),1=>array(0=>83,1=>39,),2=>array(0=>78,1=>32,),3=>array(0=>29,1=>92,),),251=>array(0=>array(0=>4,1=>85,),1=>array(0=>95,1=>42,),2=>array(0=>90,1=>64,),3=>array(0=>7,1=>37,),),252=>array(0=>array(0=>12,1=>57,),1=>array(0=>48,1=>0,),2=>array(0=>95,1=>9,),3=>array(0=>34,1=>53,),),253=>array(0=>array(0=>16,1=>94,),1=>array(0=>44,1=>35,),2=>array(0=>66,1=>63,),3=>array(0=>43,1=>72,),),254=>array(0=>array(0=>32,1=>65,),1=>array(0=>30,1=>76,),2=>array(0=>38,1=>61,),3=>array(0=>8,1=>29,),),255=>array(0=>array(0=>58,1=>84,),1=>array(0=>18,1=>77,),2=>array(0=>95,1=>27,),3=>array(0=>12,1=>62,),),256=>array(0=>array(0=>25,1=>78,),1=>array(0=>55,1=>92,),2=>array(0=>93,1=>43,),3=>array(0=>47,1=>49,),),257=>array(0=>array(0=>1,1=>48,),1=>array(0=>93,1=>59,),2=>array(0=>20,1=>94,),3=>array(0=>81,1=>44,),),258=>array(0=>array(0=>64,1=>42,),1=>array(0=>11,1=>38,),2=>array(0=>17,1=>76,),3=>array(0=>100,1=>43,),),259=>array(0=>array(0=>64,1=>21,),1=>array(0=>34,1=>88,),2=>array(0=>98,1=>15,),3=>array(0=>16,1=>2,),),260=>array(0=>array(0=>2,1=>54,),1=>array(0=>38,1=>49,),2=>array(0=>40,1=>4,),3=>array(0=>6,1=>80,),),261=>array(0=>array(0=>2,1=>19,),1=>array(0=>48,1=>100,),2=>array(0=>26,1=>93,),3=>array(0=>1,1=>91,),),262=>array(0=>array(0=>88,1=>36,),1=>array(0=>98,1=>30,),2=>array(0=>78,1=>26,),3=>array(0=>78,1=>94,),),263=>array(0=>array(0=>26,1=>17,),1=>array(0=>36,1=>39,),2=>array(0=>6,1=>94,),3=>array(0=>58,1=>41,),),264=>array(0=>array(0=>63,1=>38,),1=>array(0=>81,1=>73,),2=>array(0=>89,1=>38,),3=>array(0=>98,1=>34,),),265=>array(0=>array(0=>11,1=>48,),1=>array(0=>1,1=>5,),2=>array(0=>25,1=>1,),3=>array(0=>20,1=>62,),),266=>array(0=>array(0=>92,1=>91,),1=>array(0=>34,1=>93,),2=>array(0=>7,1=>35,),3=>array(0=>88,1=>62,),),267=>array(0=>array(0=>97,1=>9,),1=>array(0=>17,1=>65,),2=>array(0=>36,1=>100,),3=>array(0=>60,1=>24,),),268=>array(0=>array(0=>70,1=>18,),1=>array(0=>31,1=>49,),2=>array(0=>70,1=>58,),3=>array(0=>98,1=>99,),),269=>array(0=>array(0=>95,1=>91,),1=>array(0=>25,1=>80,),2=>array(0=>69,1=>40,),3=>array(0=>48,1=>65,),),270=>array(0=>array(0=>56,1=>33,),1=>array(0=>1,1=>86,),2=>array(0=>41,1=>23,),3=>array(0=>93,1=>78,),),271=>array(0=>array(0=>78,1=>89,),1=>array(0=>13,1=>69,),2=>array(0=>77,1=>81,),3=>array(0=>21,1=>77,),),272=>array(0=>array(0=>82,1=>33,),1=>array(0=>22,1=>67,),2=>array(0=>79,1=>16,),3=>array(0=>62,1=>60,),),273=>array(0=>array(0=>64,1=>29,),1=>array(0=>42,1=>37,),2=>array(0=>12,1=>4,),3=>array(0=>27,1=>54,),),274=>array(0=>array(0=>100,1=>95,),1=>array(0=>91,1=>81,),2=>array(0=>66,1=>6,),3=>array(0=>27,1=>21,),),275=>array(0=>array(0=>63,1=>45,),1=>array(0=>37,1=>89,),2=>array(0=>54,1=>48,),3=>array(0=>13,1=>15,),),276=>array(0=>array(0=>87,1=>77,),1=>array(0=>7,1=>71,),2=>array(0=>73,1=>17,),3=>array(0=>84,1=>8,),),277=>array(0=>array(0=>47,1=>58,),1=>array(0=>23,1=>11,),2=>array(0=>32,1=>14,),3=>array(0=>70,1=>36,),),278=>array(0=>array(0=>27,1=>86,),1=>array(0=>52,1=>91,),2=>array(0=>31,1=>34,),3=>array(0=>42,1=>42,),),279=>array(0=>array(0=>2,1=>16,),1=>array(0=>25,1=>17,),2=>array(0=>26,1=>78,),3=>array(0=>12,1=>62,),),280=>array(0=>array(0=>13,1=>28,),1=>array(0=>3,1=>35,),2=>array(0=>79,1=>15,),3=>array(0=>95,1=>34,),),281=>array(0=>array(0=>48,1=>35,),1=>array(0=>5,1=>51,),2=>array(0=>85,1=>42,),3=>array(0=>36,1=>18,),),282=>array(0=>array(0=>21,1=>16,),1=>array(0=>20,1=>59,),2=>array(0=>77,1=>1,),3=>array(0=>85,1=>95,),),283=>array(0=>array(0=>0,1=>78,),1=>array(0=>98,1=>46,),2=>array(0=>37,1=>73,),3=>array(0=>3,1=>44,),),284=>array(0=>array(0=>5,1=>96,),1=>array(0=>48,1=>11,),2=>array(0=>43,1=>24,),3=>array(0=>42,1=>96,),),285=>array(0=>array(0=>99,1=>63,),1=>array(0=>62,1=>74,),2=>array(0=>57,1=>45,),3=>array(0=>5,1=>65,),),286=>array(0=>array(0=>9,1=>2,),1=>array(0=>28,1=>15,),2=>array(0=>52,1=>64,),3=>array(0=>47,1=>9,),),287=>array(0=>array(0=>40,1=>2,),1=>array(0=>22,1=>69,),2=>array(0=>41,1=>97,),3=>array(0=>6,1=>40,),),288=>array(0=>array(0=>65,1=>98,),1=>array(0=>90,1=>1,),2=>array(0=>67,1=>34,),3=>array(0=>30,1=>41,),),289=>array(0=>array(0=>47,1=>21,),1=>array(0=>63,1=>12,),2=>array(0=>61,1=>96,),3=>array(0=>12,1=>43,),),290=>array(0=>array(0=>26,1=>90,),1=>array(0=>73,1=>85,),2=>array(0=>32,1=>36,),3=>array(0=>0,1=>37,),),291=>array(0=>array(0=>41,1=>50,),1=>array(0=>40,1=>92,),2=>array(0=>44,1=>34,),3=>array(0=>39,1=>55,),),292=>array(0=>array(0=>20,1=>92,),1=>array(0=>63,1=>9,),2=>array(0=>8,1=>25,),3=>array(0=>41,1=>96,),),293=>array(0=>array(0=>33,1=>48,),1=>array(0=>33,1=>14,),2=>array(0=>70,1=>98,),3=>array(0=>22,1=>70,),),294=>array(0=>array(0=>80,1=>66,),1=>array(0=>22,1=>92,),2=>array(0=>51,1=>88,),3=>array(0=>38,1=>60,),),295=>array(0=>array(0=>79,1=>28,),1=>array(0=>53,1=>73,),2=>array(0=>3,1=>87,),3=>array(0=>28,1=>79,),),296=>array(0=>array(0=>71,1=>4,),1=>array(0=>89,1=>18,),2=>array(0=>21,1=>40,),3=>array(0=>28,1=>54,),),297=>array(0=>array(0=>24,1=>4,),1=>array(0=>86,1=>94,),2=>array(0=>95,1=>2,),3=>array(0=>71,1=>100,),),298=>array(0=>array(0=>99,1=>40,),1=>array(0=>97,1=>10,),2=>array(0=>87,1=>25,),3=>array(0=>46,1=>54,),),299=>array(0=>array(0=>49,1=>77,),1=>array(0=>66,1=>3,),2=>array(0=>39,1=>45,),3=>array(0=>2,1=>95,),),300=>array(0=>array(0=>54,1=>8,),1=>array(0=>33,1=>72,),2=>array(0=>7,1=>44,),3=>array(0=>79,1=>24,),),301=>array(0=>array(0=>89,1=>14,),1=>array(0=>0,1=>79,),2=>array(0=>69,1=>23,),3=>array(0=>82,1=>8,),),302=>array(0=>array(0=>55,1=>38,),1=>array(0=>63,1=>87,),2=>array(0=>12,1=>48,),3=>array(0=>56,1=>28,),),303=>array(0=>array(0=>60,1=>63,),1=>array(0=>72,1=>43,),2=>array(0=>27,1=>3,),3=>array(0=>79,1=>75,),),304=>array(0=>array(0=>76,1=>38,),1=>array(0=>47,1=>96,),2=>array(0=>97,1=>24,),3=>array(0=>70,1=>25,),),305=>array(0=>array(0=>4,1=>11,),1=>array(0=>10,1=>76,),2=>array(0=>25,1=>91,),3=>array(0=>56,1=>20,),),306=>array(0=>array(0=>41,1=>28,),1=>array(0=>66,1=>63,),2=>array(0=>50,1=>31,),3=>array(0=>21,1=>97,),),307=>array(0=>array(0=>9,1=>13,),1=>array(0=>21,1=>15,),2=>array(0=>62,1=>21,),3=>array(0=>43,1=>50,),),308=>array(0=>array(0=>85,1=>22,),1=>array(0=>45,1=>94,),2=>array(0=>7,1=>51,),3=>array(0=>46,1=>24,),),309=>array(0=>array(0=>85,1=>5,),1=>array(0=>27,1=>63,),2=>array(0=>49,1=>82,),3=>array(0=>44,1=>45,),),310=>array(0=>array(0=>54,1=>100,),1=>array(0=>9,1=>1,),2=>array(0=>45,1=>2,),3=>array(0=>99,1=>40,),),311=>array(0=>array(0=>36,1=>0,),1=>array(0=>24,1=>34,),2=>array(0=>55,1=>65,),3=>array(0=>39,1=>6,),),312=>array(0=>array(0=>27,1=>14,),1=>array(0=>18,1=>50,),2=>array(0=>9,1=>9,),3=>array(0=>56,1=>99,),),313=>array(0=>array(0=>83,1=>100,),1=>array(0=>95,1=>94,),2=>array(0=>81,1=>17,),3=>array(0=>88,1=>2,),),314=>array(0=>array(0=>30,1=>90,),1=>array(0=>28,1=>14,),2=>array(0=>44,1=>99,),3=>array(0=>50,1=>47,),),315=>array(0=>array(0=>50,1=>76,),1=>array(0=>41,1=>64,),2=>array(0=>17,1=>38,),3=>array(0=>40,1=>57,),),316=>array(0=>array(0=>10,1=>98,),1=>array(0=>78,1=>16,),2=>array(0=>42,1=>58,),3=>array(0=>53,1=>78,),),317=>array(0=>array(0=>5,1=>65,),1=>array(0=>90,1=>72,),2=>array(0=>12,1=>28,),3=>array(0=>30,1=>95,),),318=>array(0=>array(0=>28,1=>72,),1=>array(0=>55,1=>93,),2=>array(0=>21,1=>33,),3=>array(0=>100,1=>44,),),319=>array(0=>array(0=>18,1=>84,),1=>array(0=>21,1=>75,),2=>array(0=>44,1=>11,),3=>array(0=>6,1=>48,),),320=>array(0=>array(0=>44,1=>21,),1=>array(0=>91,1=>34,),2=>array(0=>57,1=>8,),3=>array(0=>34,1=>59,),),321=>array(0=>array(0=>44,1=>82,),1=>array(0=>3,1=>41,),2=>array(0=>6,1=>52,),3=>array(0=>22,1=>36,),),322=>array(0=>array(0=>6,1=>81,),1=>array(0=>97,1=>31,),2=>array(0=>31,1=>63,),3=>array(0=>53,1=>54,),),323=>array(0=>array(0=>34,1=>61,),1=>array(0=>23,1=>8,),2=>array(0=>59,1=>82,),3=>array(0=>100,1=>11,),),324=>array(0=>array(0=>5,1=>48,),1=>array(0=>99,1=>91,),2=>array(0=>13,1=>92,),3=>array(0=>9,1=>76,),),325=>array(0=>array(0=>40,1=>84,),1=>array(0=>85,1=>15,),2=>array(0=>54,1=>91,),3=>array(0=>75,1=>57,),),326=>array(0=>array(0=>39,1=>11,),1=>array(0=>36,1=>66,),2=>array(0=>44,1=>5,),3=>array(0=>11,1=>83,),),327=>array(0=>array(0=>62,1=>73,),1=>array(0=>86,1=>92,),2=>array(0=>40,1=>43,),3=>array(0=>92,1=>30,),),328=>array(0=>array(0=>61,1=>32,),1=>array(0=>82,1=>79,),2=>array(0=>49,1=>11,),3=>array(0=>42,1=>21,),),329=>array(0=>array(0=>97,1=>30,),1=>array(0=>96,1=>19,),2=>array(0=>73,1=>60,),3=>array(0=>56,1=>75,),),330=>array(0=>array(0=>58,1=>2,),1=>array(0=>68,1=>33,),2=>array(0=>27,1=>79,),3=>array(0=>45,1=>59,),),331=>array(0=>array(0=>46,1=>3,),1=>array(0=>67,1=>86,),2=>array(0=>63,1=>47,),3=>array(0=>45,1=>21,),),332=>array(0=>array(0=>65,1=>84,),1=>array(0=>4,1=>2,),2=>array(0=>9,1=>65,),3=>array(0=>58,1=>63,),),333=>array(0=>array(0=>64,1=>38,),1=>array(0=>51,1=>2,),2=>array(0=>83,1=>44,),3=>array(0=>80,1=>46,),),334=>array(0=>array(0=>98,1=>83,),1=>array(0=>41,1=>3,),2=>array(0=>69,1=>11,),3=>array(0=>72,1=>22,),),335=>array(0=>array(0=>81,1=>86,),1=>array(0=>88,1=>52,),2=>array(0=>91,1=>12,),3=>array(0=>71,1=>79,),),336=>array(0=>array(0=>65,1=>10,),1=>array(0=>19,1=>11,),2=>array(0=>14,1=>39,),3=>array(0=>0,1=>7,),),337=>array(0=>array(0=>10,1=>49,),1=>array(0=>94,1=>18,),2=>array(0=>71,1=>23,),3=>array(0=>59,1=>54,),),338=>array(0=>array(0=>81,1=>85,),1=>array(0=>100,1=>93,),2=>array(0=>26,1=>93,),3=>array(0=>22,1=>46,),),339=>array(0=>array(0=>78,1=>11,),1=>array(0=>48,1=>81,),2=>array(0=>38,1=>5,),3=>array(0=>33,1=>39,),),340=>array(0=>array(0=>88,1=>63,),1=>array(0=>42,1=>56,),2=>array(0=>15,1=>63,),3=>array(0=>20,1=>46,),),341=>array(0=>array(0=>86,1=>64,),1=>array(0=>42,1=>78,),2=>array(0=>9,1=>62,),3=>array(0=>36,1=>44,),),342=>array(0=>array(0=>0,1=>91,),1=>array(0=>8,1=>87,),2=>array(0=>90,1=>4,),3=>array(0=>6,1=>53,),),343=>array(0=>array(0=>2,1=>95,),1=>array(0=>94,1=>87,),2=>array(0=>53,1=>53,),3=>array(0=>36,1=>74,),),344=>array(0=>array(0=>44,1=>18,),1=>array(0=>53,1=>2,),2=>array(0=>33,1=>73,),3=>array(0=>65,1=>14,),),345=>array(0=>array(0=>69,1=>96,),1=>array(0=>43,1=>18,),2=>array(0=>71,1=>30,),3=>array(0=>78,1=>73,),),346=>array(0=>array(0=>3,1=>78,),1=>array(0=>0,1=>29,),2=>array(0=>3,1=>43,),3=>array(0=>49,1=>87,),),347=>array(0=>array(0=>51,1=>97,),1=>array(0=>51,1=>55,),2=>array(0=>7,1=>24,),3=>array(0=>64,1=>12,),),348=>array(0=>array(0=>80,1=>79,),1=>array(0=>1,1=>57,),2=>array(0=>18,1=>53,),3=>array(0=>15,1=>33,),),349=>array(0=>array(0=>31,1=>34,),1=>array(0=>6,1=>70,),2=>array(0=>35,1=>11,),3=>array(0=>71,1=>63,),),350=>array(0=>array(0=>37,1=>0,),1=>array(0=>92,1=>0,),2=>array(0=>44,1=>95,),3=>array(0=>19,1=>83,),),351=>array(0=>array(0=>30,1=>68,),1=>array(0=>39,1=>20,),2=>array(0=>97,1=>80,),3=>array(0=>69,1=>76,),),352=>array(0=>array(0=>37,1=>7,),1=>array(0=>13,1=>32,),2=>array(0=>39,1=>51,),3=>array(0=>97,1=>66,),),353=>array(0=>array(0=>53,1=>79,),1=>array(0=>48,1=>81,),2=>array(0=>53,1=>99,),3=>array(0=>70,1=>92,),),354=>array(0=>array(0=>81,1=>36,),1=>array(0=>36,1=>87,),2=>array(0=>14,1=>94,),3=>array(0=>93,1=>55,),),355=>array(0=>array(0=>44,1=>76,),1=>array(0=>21,1=>87,),2=>array(0=>5,1=>31,),3=>array(0=>51,1=>77,),),356=>array(0=>array(0=>26,1=>29,),1=>array(0=>59,1=>37,),2=>array(0=>85,1=>2,),3=>array(0=>22,1=>82,),),357=>array(0=>array(0=>9,1=>61,),1=>array(0=>12,1=>99,),2=>array(0=>84,1=>31,),3=>array(0=>26,1=>19,),),358=>array(0=>array(0=>85,1=>76,),1=>array(0=>63,1=>19,),2=>array(0=>99,1=>25,),3=>array(0=>93,1=>53,),),359=>array(0=>array(0=>11,1=>0,),1=>array(0=>80,1=>97,),2=>array(0=>60,1=>76,),3=>array(0=>87,1=>70,),),360=>array(0=>array(0=>13,1=>9,),1=>array(0=>7,1=>2,),2=>array(0=>58,1=>30,),3=>array(0=>47,1=>16,),),361=>array(0=>array(0=>40,1=>27,),1=>array(0=>12,1=>77,),2=>array(0=>5,1=>97,),3=>array(0=>36,1=>34,),),362=>array(0=>array(0=>76,1=>21,),1=>array(0=>41,1=>23,),2=>array(0=>99,1=>26,),3=>array(0=>75,1=>90,),),363=>array(0=>array(0=>66,1=>67,),1=>array(0=>12,1=>31,),2=>array(0=>14,1=>63,),3=>array(0=>33,1=>17,),),364=>array(0=>array(0=>19,1=>18,),1=>array(0=>85,1=>8,),2=>array(0=>37,1=>69,),3=>array(0=>35,1=>70,),),365=>array(0=>array(0=>58,1=>19,),1=>array(0=>57,1=>71,),2=>array(0=>31,1=>84,),3=>array(0=>7,1=>64,),),366=>array(0=>array(0=>17,1=>41,),1=>array(0=>36,1=>11,),2=>array(0=>69,1=>68,),3=>array(0=>40,1=>52,),),367=>array(0=>array(0=>64,1=>55,),1=>array(0=>23,1=>75,),2=>array(0=>64,1=>76,),3=>array(0=>36,1=>68,),),368=>array(0=>array(0=>75,1=>53,),1=>array(0=>2,1=>73,),2=>array(0=>60,1=>76,),3=>array(0=>73,1=>69,),),369=>array(0=>array(0=>21,1=>23,),1=>array(0=>61,1=>19,),2=>array(0=>0,1=>16,),3=>array(0=>51,1=>79,),),370=>array(0=>array(0=>98,1=>17,),1=>array(0=>44,1=>80,),2=>array(0=>21,1=>66,),3=>array(0=>86,1=>73,),),371=>array(0=>array(0=>36,1=>66,),1=>array(0=>68,1=>55,),2=>array(0=>11,1=>62,),3=>array(0=>53,1=>5,),),372=>array(0=>array(0=>73,1=>83,),1=>array(0=>96,1=>41,),2=>array(0=>87,1=>40,),3=>array(0=>69,1=>77,),),373=>array(0=>array(0=>61,1=>77,),1=>array(0=>90,1=>79,),2=>array(0=>99,1=>42,),3=>array(0=>62,1=>81,),),374=>array(0=>array(0=>54,1=>81,),1=>array(0=>9,1=>64,),2=>array(0=>100,1=>99,),3=>array(0=>7,1=>100,),),375=>array(0=>array(0=>33,1=>50,),1=>array(0=>75,1=>35,),2=>array(0=>3,1=>80,),3=>array(0=>30,1=>43,),),376=>array(0=>array(0=>39,1=>9,),1=>array(0=>10,1=>54,),2=>array(0=>99,1=>63,),3=>array(0=>33,1=>15,),),377=>array(0=>array(0=>58,1=>13,),1=>array(0=>10,1=>77,),2=>array(0=>75,1=>17,),3=>array(0=>42,1=>44,),),378=>array(0=>array(0=>51,1=>89,),1=>array(0=>46,1=>92,),2=>array(0=>6,1=>71,),3=>array(0=>43,1=>54,),),379=>array(0=>array(0=>62,1=>21,),1=>array(0=>80,1=>53,),2=>array(0=>50,1=>54,),3=>array(0=>59,1=>33,),),380=>array(0=>array(0=>21,1=>96,),1=>array(0=>90,1=>64,),2=>array(0=>32,1=>92,),3=>array(0=>23,1=>83,),),381=>array(0=>array(0=>64,1=>81,),1=>array(0=>72,1=>17,),2=>array(0=>55,1=>86,),3=>array(0=>2,1=>6,),),382=>array(0=>array(0=>53,1=>30,),1=>array(0=>60,1=>58,),2=>array(0=>14,1=>53,),3=>array(0=>89,1=>98,),),383=>array(0=>array(0=>39,1=>29,),1=>array(0=>21,1=>29,),2=>array(0=>47,1=>99,),3=>array(0=>3,1=>55,),),384=>array(0=>array(0=>91,1=>90,),1=>array(0=>20,1=>24,),2=>array(0=>44,1=>91,),3=>array(0=>69,1=>65,),),385=>array(0=>array(0=>19,1=>87,),1=>array(0=>0,1=>44,),2=>array(0=>19,1=>100,),3=>array(0=>15,1=>82,),),386=>array(0=>array(0=>85,1=>82,),1=>array(0=>93,1=>75,),2=>array(0=>13,1=>44,),3=>array(0=>96,1=>11,),),387=>array(0=>array(0=>33,1=>66,),1=>array(0=>37,1=>41,),2=>array(0=>36,1=>1,),3=>array(0=>69,1=>83,),),388=>array(0=>array(0=>96,1=>63,),1=>array(0=>19,1=>33,),2=>array(0=>77,1=>21,),3=>array(0=>67,1=>63,),),389=>array(0=>array(0=>53,1=>82,),1=>array(0=>34,1=>59,),2=>array(0=>96,1=>20,),3=>array(0=>85,1=>74,),),390=>array(0=>array(0=>30,1=>47,),1=>array(0=>9,1=>97,),2=>array(0=>76,1=>78,),3=>array(0=>88,1=>94,),),391=>array(0=>array(0=>29,1=>70,),1=>array(0=>20,1=>58,),2=>array(0=>59,1=>91,),3=>array(0=>43,1=>13,),),392=>array(0=>array(0=>85,1=>60,),1=>array(0=>34,1=>40,),2=>array(0=>18,1=>75,),3=>array(0=>82,1=>2,),),393=>array(0=>array(0=>99,1=>31,),1=>array(0=>68,1=>95,),2=>array(0=>48,1=>5,),3=>array(0=>64,1=>42,),),394=>array(0=>array(0=>60,1=>14,),1=>array(0=>86,1=>34,),2=>array(0=>77,1=>63,),3=>array(0=>20,1=>54,),),395=>array(0=>array(0=>3,1=>65,),1=>array(0=>91,1=>30,),2=>array(0=>37,1=>47,),3=>array(0=>100,1=>54,),),396=>array(0=>array(0=>60,1=>39,),1=>array(0=>60,1=>50,),2=>array(0=>98,1=>64,),3=>array(0=>43,1=>5,),),397=>array(0=>array(0=>97,1=>66,),1=>array(0=>87,1=>81,),2=>array(0=>22,1=>68,),3=>array(0=>81,1=>83,),),398=>array(0=>array(0=>1,1=>81,),1=>array(0=>69,1=>64,),2=>array(0=>28,1=>31,),3=>array(0=>36,1=>16,),),399=>array(0=>array(0=>78,1=>23,),1=>array(0=>26,1=>92,),2=>array(0=>49,1=>85,),3=>array(0=>3,1=>73,),),);

						$itemSvgDefs = HtmlNd::CreateTag( $doc, 'defs', array(), array(
							HtmlNd::CreateTag( $doc, 'filter', array( 'id' => $id . '-f-blur-sm', 'x' => '-100%', 'y' => '-100%', 'width' => '400%', 'height' => '400%' ), array(
								HtmlNd::CreateTag( $doc, 'feGaussianBlur', array( 'result' => 'blur', 'stdDeviation' => '2' ), array(
								) ),
								HtmlNd::CreateTag( $doc, 'feComponentTransfer', array(), array(
									HtmlNd::CreateTag( $doc, 'feFuncA', array( 'type' => 'linear', 'slope' => '180', 'intercept' => '-70' ) ),
								) ),
							) ),

							HtmlNd::CreateTag( $doc, 'filter', array( 'id' => $id . '-f-blur', 'x' => '-100%', 'y' => '-100%', 'width' => '400%', 'height' => '400%' ), array(
								HtmlNd::CreateTag( $doc, 'feGaussianBlur', array( 'result' => 'blur', 'stdDeviation' => '10' ), array(
								) ),
								HtmlNd::CreateTag( $doc, 'feComponentTransfer', array(), array(
									HtmlNd::CreateTag( $doc, 'feFuncA', array( 'type' => 'linear', 'slope' => '180', 'intercept' => '-70' ) ),
								) ),
							) ),
						) );

						$bg = Gen::GetArrField( $bubbleMorph, array( 'bg' ) );
						if( is_string( $bg ) && preg_match( '@^rgba\\(\\s*\\d+\\s*,\\s*\\d+\\s*,\\s*\\d+\\s*,\\s*0\\s*\\)$@', $bg ) )
							$bg = null;

						if( $bg )
						{
							if( is_array( $bg ) )
							{

								$type = Gen::GetArrField( $bg, array( 'type' ), '' );
								if( $type )
								{
									$attrs = array( 'id' => $id . '-bubbles-bg' );
									$angle = ( float )Gen::GetArrField( $bg, array( 'angle' ) );
									if( $angle )
										$attrs[ 'gradientTransform' ] = 'rotate(' . ( $angle - 90 ) . ')';

									$itemSvgDefs -> appendChild( $itemBg = HtmlNd::CreateTag( $doc, $type . 'Gradient', $attrs ) );
									foreach( Gen::GetArrField( $bg, array( 'colors' ), array() ) as $color )
									{
										$itemBg -> appendChild( HtmlNd::CreateTag( $doc, 'stop', array( 'offset' => ( string )Gen::GetArrField( $color, array( 'position' ), 0 ) . '%', 'stop-color' => Gen::GetArrField( $color, array( 'a' ), 1.0 ) !== 1.0 ? sprintf( 'rgba(%d,%d,%d,%d)', Gen::GetArrField( $color, array( 'r' ), 0 ), Gen::GetArrField( $color, array( 'g' ), 0 ), Gen::GetArrField( $color, array( 'b' ), 0 ), Gen::GetArrField( $color, array( 'a' ), 0.0 ) ) : sprintf( 'rgb(%d,%d,%d)', Gen::GetArrField( $color, array( 'r' ), 0 ), Gen::GetArrField( $color, array( 'g' ), 0 ), Gen::GetArrField( $color, array( 'b' ), 0 ) ) ) ) );
									}

									$bg = 'url(#' . $id . '-bubbles-bg)';
								}
							}
						}

						$itemSlideChild -> appendChild( $itemSvg = HtmlNd::CreateTag( $doc, 'svg', array( 'version' => '1.1', 'xmlns' => 'http://www.w3.org/2000/svg', 'overflow' => 'visible' ), array( $itemSvgDefs,  ) ) );

						$aSpeedX = array_map( function( $v ) { return( ( float )$v ); }, explode( '|', Gen::GetArrField( $bubbleMorph, array( 'speedx' ), '' ) ) );
						$aSpeedY = array_map( function( $v ) { return( ( float )$v ); }, explode( '|', Gen::GetArrField( $bubbleMorph, array( 'speedy' ), '' ) ) );
						$aBorderColor = explode( '|', Gen::GetArrField( $bubbleMorph, array( 'bordercolor' ), '' ) );
						$aBorderSize = explode( '|', Gen::GetArrField( $bubbleMorph, array( 'bordersize' ), '' ) );
						$nBubblesMax = 0;
						foreach( explode( '|', Gen::GetArrField( $bubbleMorph, array( 'num' ), '' ) ) as $i => $nBubbles )
						{
							$nBubbles = min( count( $g_aBubblePosRand ) - $iCurBubblesRand, $nBubbles );
							if( $nBubblesMax < $nBubbles )
								$nBubblesMax = $nBubbles;

							if( ( int )(isset($aBorderSize[ $i ])?$aBorderSize[ $i ]:'') )
							{
								$itemSvgBorderSub1 = HtmlNd::CreateTag( $doc, 'g', array( 'class' => 'bubbles b-ext' ) );
								$itemSvgBorderSub2 = HtmlNd::CreateTag( $doc, 'g', array( 'class' => 'bubbles b-int' ) );
								$itemSvg -> appendChild( HtmlNd::CreateTag( $doc, 'mask', array( 'class' => 'v' . $i, 'id' => $id . '-bubbles-v' . $i . '-border', 'style' => array( 'display' => 'none' ) ), array( $itemSvgBorderSub1, $itemSvgBorderSub2 ) ) );
							}
							else
							{
								$itemSvgBorderSub1 = null;
								$itemSvgBorderSub2 = null;
							}

							if( $bg )
							{
								$itemSvgBody = HtmlNd::CreateTag( $doc, 'g', array( 'class' => 'bubbles body' ) );
								$itemSvg -> appendChild( HtmlNd::CreateTag( $doc, 'mask', array( 'class' => 'v' . $i, 'id' => $id . '-bubbles-v' . $i . '-body', 'style' => array( 'display' => 'none' ) ), array( $itemSvgBody ) ) );
							}
							else
								$itemSvgBody = null;

							for( $iBubble = 0; $iBubble < $nBubbles; $iBubble++ )
							{
								$dur = ( (isset($aSpeedX[ $i ])?$aSpeedX[ $i ]:0.0) + (isset($aSpeedY[ $i ])?$aSpeedY[ $i ]:0.0) ) / 2;
								$dur = $dur ? ( 2.5 / $dur ) : 50;

								{
									$durShift = 0.3 * $dur * ( ( $iBubble + 1 ) / ( float )$nBubbles );
									if( $iBubble % 2 )
										$durShift *= -1;
									$dur += $durShift;
								}

								$keyTimes = ''; $valuesX = ''; $valuesY = '';
								$jn = count( $g_aBubblePosRand[ $iCurBubblesRand + $iBubble ] );
								for( $j = 0; $j < $jn; $j++ )
								{
									$keyTimes .= ( string )( ( float )$j / $jn ) . ';';
									$valuesX .= ( string )$g_aBubblePosRand[ $iCurBubblesRand + $iBubble ][ $j ][ 0 ] . '%;';
									$valuesY .= ( string )$g_aBubblePosRand[ $iCurBubblesRand + $iBubble ][ $j ][ 1 ] . '%;';
								}
								$keyTimes .= '1';
								$valuesX .= ( string )$g_aBubblePosRand[ $iCurBubblesRand + $iBubble ][ 0 ][ 0 ] . '%;';
								$valuesY .= ( string )$g_aBubblePosRand[ $iCurBubblesRand + $iBubble ][ 0 ][ 1 ] . '%;';

								$itemSvgBubble = HtmlNd::CreateTag( $doc, 'circle', array( 'class' => 'b' . $iBubble ), array(
									HtmlNd::CreateTag( $doc, 'animate', array( 'attributeName' => 'cx', 'keyTimes' => $keyTimes, 'values' => $valuesX, 'dur' => ( string )$dur . 's', 'repeatCount' => 'indefinite' ) ),
									HtmlNd::CreateTag( $doc, 'animate', array( 'attributeName' => 'cy', 'keyTimes' => $keyTimes, 'values' => $valuesY, 'dur' => ( string )$dur . 's', 'repeatCount' => 'indefinite' ) ),
								) );

								$bItemSvgBubbleNeedClone = false;
								foreach( array( $itemSvgBorderSub1, $itemSvgBorderSub2, $itemSvgBody ) as $itemSvgBubbleContainer )
								{
									if( !$itemSvgBubbleContainer )
										continue;

									if( $bItemSvgBubbleNeedClone )
										$itemSvgBubble = $itemSvgBubble -> cloneNode( true );
									else
										$bItemSvgBubbleNeedClone = true;

									$itemSvgBubbleContainer -> appendChild( $itemSvgBubble );
								}
							}

							if( $itemSvgBorderSub1 )
								$itemSvg -> appendChild( HtmlNd::CreateTag( $doc, 'rect', array( 'class' => 'v' . $i, 'mask' => 'url(#' . $id . '-bubbles-v' . $i . '-border)', 'fill' => (isset($aBorderColor[ $i ])?$aBorderColor[ $i ]:''), 'style' => array( 'display' => 'none' ) ), array() ) );
							if( $itemSvgBody )
								$itemSvg -> appendChild( HtmlNd::CreateTag( $doc, 'rect', array( 'class' => 'v' . $i, 'mask' => 'url(#' . $id . '-bubbles-v' . $i . '-body)', 'fill' => $bg, 'style' => array( 'display' => 'none' ) ), array() ) );

							_RevSld_SetStyleAttrEx( $aItemStyle, '#' . $id . ' .v' . $i, $i, array( 'display' => 'initial!important' ) );
						}

						$iCurBubblesRand += $nBubblesMax;

						{
							$a = array();
							foreach( explode( '|', Gen::GetArrField( $bubbleMorph, array( $f ), '' ) ) as $i => $v )
								$a[ $i ][ $t ] = $v;
							_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
						}

						foreach( array( 'bufferx' => '--buffer-x', 'buffery' => '--buffer-y', 'bordersize' => '--border-size' ) as $f => $t )
						{
							$a = array(); foreach( explode( '|', Gen::GetArrField( $bubbleMorph, array( $f ), '0' ) ) as $i => $v ) $a[ $i ][ $t ] = _RevSld_GetSize( false, $v );
							_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
						}

						{
							$itemScript = $doc -> createElement( 'script' );
							if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
								$itemScript -> setAttribute( 'type', 'text/javascript' );
							$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
							HtmlNd::SetValFromContent( $itemScript, 'seraph_accel_cp_sldRev_bubblemorph_calcSizes(document.currentScript.parentNode);' );
							$itemSlideChild -> insertBefore( $itemScript, $itemSlideChild -> firstChild );
						}

						$adjustedBubbles = true;
					}

					$styleSeparated = Ui::MergeStyleAttr( Ui::ParseStyleAttr( $itemSlideChild -> getAttribute( 'style' ) ), $styleSeparated );
					$styleSeparatedWrap[ 'z-index' ] = (isset($styleSeparated[ 'z-index' ])?$styleSeparated[ 'z-index' ]:null);

					$itemSlideChild -> setAttribute( 'style', Ui::GetStyleAttr( $styleSeparated ) );

					_RevSld_SetStyleAttr( $styleSeparatedWrap, $aItemStyle, $itemChildSelectorWrap, $aSizeWrap );

					$styleSeparatedLoopWrap = array( 'position' => $styleSeparated[ 'position' ], 'display' => $attrDisplay );
					_RevSld_SetStyleAttr( $styleSeparatedLoopWrap, $aItemStyle, $itemChildSelectorWrap . '>rs-loop-wrap', $aSizeWrap );

					$styleSeparatedMaskWrap = array( 'position' => $styleSeparated[ 'position' ], 'overflow' => 'visible', 'display' => $attrDisplay );
					_RevSld_SetStyleAttr( $styleSeparatedMaskWrap, $aItemStyle, $itemChildSelectorWrap . '>rs-loop-wrap>rs-mask-wrap', $aSizeWrap );

					$itemParent -> insertBefore( HtmlNd::CreateTag( $doc, $isLayer ? 'rs-layer-wrap' : ( $itemSlideChild -> nodeName . '-wrap' ), array( 'id' => $itemIdWrap, 'class' => array( 'rs-parallax-wrap', $attrWrapperClass, $itemSlideChild -> nodeName == 'rs-row' ? 'slider-row-wrap' : null ), 'style' => $styleSeparatedWrap ), array( HtmlNd::CreateTag( $doc, 'rs-loop-wrap', array( 'style' => $styleSeparatedLoopWrap ), array( HtmlNd::CreateTag( $doc, 'rs-mask-wrap', array( 'style' => $styleSeparatedMaskWrap ), array( $itemSlideChild ) ) ) ) ) ), $itemInsertBefore );
				}
			}
		}

		if( Gen::GetArrField( $prms, array( 'init', 'navigation', 'bullets', 'enable' ) ) && $nSlides )
		{
			$direction = Gen::GetArrField( $prms, array( 'init', 'navigation', 'bullets', 'direction' ), 'horizontal' );
			$alignHor = Gen::GetArrField( $prms, array( 'init', 'navigation', 'bullets', 'h_align' ), 'center' );
			$alignVer = Gen::GetArrField( $prms, array( 'init', 'navigation', 'bullets', 'v_align' ), 'bottom' );
			$space = Gen::GetArrField( $prms, array( 'init', 'navigation', 'bullets', 'space' ), 5 );

			$obj = new AnyObj();
			$obj -> cb =
				function( $obj, $m )
				{
					return( $obj -> itemSlide -> getAttribute( 'data-' . $m[ 1 ] ) );
				};

			$itemBulletsTmp = '';
			for( $i = 0; $i < $nSlides; $i++ )
			{
				$obj -> itemSlide = $aItemSlide[ $i ];

				$attrs = array( 'class' => 'tp-bullet ' . ( $i === 0 ? 'selected' : '' ), 'style' => array( 'position' => 'relative!important' ) );
				if( $direction == 'horizontal' )
				{
					if( $i )
						$attrs[ 'style' ][ 'margin-left' ] = ( string )$space . 'px';
					$attrs[ 'style' ][ 'display' ] = 'inline-block!important';
				}
				else
				{
					if( $i )
						$attrs[ 'style' ][ 'margin-top' ] = ( string )$space . 'px';
				}

				$itemBulletsTmp .= Ui::Tag( 'rs-bullet', preg_replace_callback( '@{{([^{}]+)}}@', array( $obj, 'cb' ), Gen::GetArrField( $prms, array( 'init', 'navigation', 'bullets', 'tmp' ), '' ) ), $attrs );
			}

			unset( $obj );

			$attrs = array( 'class' => array( 'tp-bullets', 'js-lzl-ing', Gen::GetArrField( $prms, array( 'init', 'navigation', 'bullets', 'style' ) ), $direction, 'nav-dir-' . $direction, 'nav-pos-hor-' . $alignHor, 'nav-pos-ver-' . $alignVer ), 'style' => array( 'display' => 'flex', 'flex-wrap' => 'wrap', 'z-index' => 1000, 'position' => 'absolute', 'counter-reset' => 'section' ) );
			if( $direction != 'horizontal' )
				$attrs[ 'style' ][ 'flex-direction' ] = 'column';

			{
				$translate = array( '0% + ', '0% + ' );

				{
					switch( $alignHor )
					{
					case 'center':
					case 'middle':					$translate[ 0 ] = '-50% + ';	$pos = '50%'; break;
					case 'right':					$translate[ 0 ] = '-100% - ';	$pos = '100%'; break;
					default:						$pos = '0%';
					}

					$attrs[ 'style' ][ 'left' ] = $pos;
				}

				{
					switch( $alignVer )
					{
					case 'center':
					case 'middle':					$translate[ 1 ] = '-50% + ';	$pos = '50%'; break;
					case 'bottom':					$translate[ 1 ] = '-100% - ';	$pos = '100%'; break;
					default:						$pos = '0%';
					}

					$attrs[ 'style' ][ 'top' ] = $pos;
				}

				$attrs[ 'style' ][ 'transform' ] = 'translate(' . _RevSld_GetSize( false, Gen::GetArrField( $prms, array( 'init', 'navigation', 'bullets', 'h_offset' ), 0 ), $translate[ 0 ] ) . ', ' . _RevSld_GetSize( false, Gen::GetArrField( $prms, array( 'init', 'navigation', 'bullets', 'v_offset' ), 20 ), $translate[ 1 ] ) . ')!important';
			}

			$itemBulletsTmp = HtmlNd::ParseAndImport( $doc, Ui::Tag( 'rs-bullets', $itemBulletsTmp, $attrs ) );
			$item -> appendChild( $itemBulletsTmp );

			_RevSld_HavHideMode( $itemStyleCont, $itemId, $prms, 'bullets', 'rs-bullets' );
			_RevSld_AdjustTimeoutByVal( $nSwitchingLoadingTimeout, $nSwitchingLoadingTimeoutMax, Gen::GetArrField( $prms, array( 'init', 'navigation', 'bullets', 'animDelay' ) ) );
		}

		if( Gen::GetArrField( $prms, array( 'init', 'navigation', 'arrows', 'enable' ) ) && $nSlides )
		{
			foreach( array( 'left', 'right' ) as $type )
			{
				$attrs = array();
				$attrs[ 'class' ] = array( 'tp-' . $type . 'arrow', 'tparrows', 'js-lzl-ing', Gen::GetArrField( $prms, array( 'init', 'navigation', 'arrows', 'style' ), '' ) );

				$translate = array( 0, 0 );

				$prefix = null;
				{
					switch( Gen::GetArrField( $prms, array( 'init', 'navigation', 'arrows', $type, 'h_align' ), $type ) )
					{
					case 'center':
					case 'middle':					$translate[ 0 ] = '-50%';	$prefix = '50% + '; break;
					case 'right':					$translate[ 0 ] = '-100%';	$prefix = '100% - '; break;
					}

					$attrs[ 'style' ][ 'left' ] = _RevSld_GetSize( false, Gen::GetArrField( $prms, array( 'init', 'navigation', 'arrows', $type, 'h_offset' ), 20 ), $prefix );
				}

				$prefix = null;
				{
					switch( Gen::GetArrField( $prms, array( 'init', 'navigation', 'arrows', $type, 'v_align' ), 'middle' ) )
					{
					case 'center':
					case 'middle':					$translate[ 1 ] = '-50%';		$prefix = '50% + '; break;
					case 'bottom':					$translate[ 1 ] = '-100%';		$prefix = '100% - '; break;
					}

					$attrs[ 'style' ][ 'top' ] = _RevSld_GetSize( false, Gen::GetArrField( $prms, array( 'init', 'navigation', 'arrows', $type, 'v_offset' ), 0 ), $prefix );
				}

				if( $translate[ 0 ] || $translate[ 1 ] )
					$attrs[ 'style' ][ 'transform' ] = 'translate(' . $translate[ 0 ] . ', ' . $translate[ 1 ] . ')!important';

				$itemStyleCont .= '#' . $itemId . ' .tp-' . $type . 'arrow.js-lzl-ing{' . Ui::GetStyleAttr( $attrs[ 'style' ], false ) . '}';
				unset( $attrs[ 'style' ] );

				$item -> appendChild( HtmlNd::ParseAndImport( $doc, Ui::Tag( 'rs-arrow', Gen::GetArrField( $prms, array( 'init', 'navigation', 'arrows', 'tmp' ), '' ), $attrs ) ) );
			}

			_RevSld_HavHideMode( $itemStyleCont, $itemId, $prms, 'arrows', 'rs-arrow' );
			_RevSld_AdjustTimeoutByVal( $nSwitchingLoadingTimeout, $nSwitchingLoadingTimeoutMax, Gen::GetArrField( $prms, array( 'init', 'navigation', 'arrows', 'animDelay' ) ) );
		}

		foreach( array( 'tabs' => array( 'sel' => 'tab', 'defs' => array( 'wrapper_padding' => 10, 'space' => 0 ) ), 'thumbnails' => array( 'sel' => 'thumb', 'defs' => array( 'wrapper_padding' => 2, 'space' => 0 ) ) ) as $type => $typeMeta )
		{
			if( !Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'enable' ) ) )
				continue;

			$contTabs = '';

			$obj = new AnyObj();
			$obj -> cb =
				function( $obj, $m )
				{
					if( count( $m ) == 3 && $m[ 1 ] == 'param' )
						return( $obj -> itemSlide -> getAttribute( 'data-p' . $m[ 2 ] ) );
					if( count( $m ) == 2 )
						return( $obj -> itemSlide -> getAttribute( 'data-' . $m[ 1 ] ) );

					if( $m[ 0 ] == 'class="tp-thumb-image"' )
						return( 'class="tp-thumb-image" style="background-image: url(&quot;' . $obj -> itemSlide -> getAttribute( 'data-thumb' ) . '&quot;);"' );

					return( $m[ 0 ] );
				};

			$visibleAmount = Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'visibleAmount' ), 5 );
			if( $visibleAmount > $nSlides )
				$visibleAmount = $nSlides;
			foreach( $aItemSlide as $i => $obj -> itemSlide )
			{
				if( $i == $visibleAmount )
					break;

				$contTab = Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'tmp' ) );
				$contTab = preg_replace_callback( '@{{([a-z\\-]+)(\\d+)}}@i', array( $obj, 'cb' ), $contTab );
				$contTab = preg_replace_callback( '@{{([\\w\\-]+)}}@', array( $obj, 'cb' ), $contTab );
				$contTab = preg_replace_callback( '@class="[\\w\\-]+"@', array( $obj, 'cb' ), $contTab );

				$contTabs .= Ui::Tag( 'rs-' . $typeMeta[ 'sel' ], $contTab
					, array(
						'data-liindex' => $i,
						'data-key' => $obj -> itemSlide -> getAttribute( 'data-key' ),
						'class' => array( 'tp-' . $typeMeta[ 'sel' ], $i === 0 ? 'selected' : '' ),
						'style' => array(
							'display' => 'inline-block!important',
							'flex-shrink' => '0',
							'position' => 'relative',
							'width' => '' . Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'width' ), 0 ) . 'px !important',
							'height' => '100%',
							'margin-right' => ( $i + 1 == $visibleAmount ) ? null : ( '' . Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'space' ), $typeMeta[ 'defs' ][ 'space' ] ) . 'px' ),
						),
					) );
			}

			unset( $obj );

			if( !$contTabs )
				continue;

			$widthTotal = $visibleAmount * Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'width' ), 0 ) + ( $visibleAmount - 1 ) * Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'space' ), $typeMeta[ 'defs' ][ 'space' ] ) + 2 * Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'mhoff' ), 0 );
			$height = Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'height' ), 0 );

			$padding = array_fill( 0, 4, '' . ( int )Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'wrapper_padding' ), $typeMeta[ 'defs' ][ 'wrapper_padding' ], null, false, false ) . 'px' );
			$translate = array( 0, 0 ); $prefix = array( null, null );
			{
				switch( Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'h_align' ), 'center' ) )
				{
				case 'center':
				case 'middle':													$prefix[ 0 ] = $padding[ 3 ] . ' + '; $padding[ 3 ] = 'calc(50% - (' . $widthTotal . 'px / 2) - ' . $padding[ 3 ] . ')'; break;
				case 'right':					$translate[ 0 ] = '-100%';		$prefix[ 0 ] = '100% - '; break;
				}

				switch( Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'v_align' ), 'bottom' ) )
				{
				case 'center':
				case 'middle':					$translate[ 1 ] = '-50%';		$prefix[ 1 ] = '50% + '; break;
				case 'bottom':					$translate[ 1 ] = '-100%';		$prefix[ 1 ] = '100% - '; break;
				}
			}

			$itemStyleCont .= '#' . $itemId . '_wrapper rs-' . $typeMeta[ 'sel' ] . 's.js-lzl-ing rs-' . $typeMeta[ 'sel' ] . 's-wrap{' . Ui::GetStyleAttr(
				array(
					'display' => 'flex',
					'max-height' => '' . $height . 'px!important',
					'height' => '' . $height . 'px!important',
				)
			, false ) . '}';

			$itemStyleCont .= '#' . $itemId . '_wrapper rs-' . $typeMeta[ 'sel' ] . 's.js-lzl-ing rs-navmask{' . Ui::GetStyleAttr(
				array(
					'padding' => '' . Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'mvoff' ), 0 ) . 'px ' . Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'mhoff' ), 0 ) . 'px!important',
					'max-width' => 'unset !important;',
					'max-height' => 'unset !important;',
				)
			, false ) . '}';

			$itemStyleCont .= '#' . $itemId . '_wrapper rs-' . $typeMeta[ 'sel' ] . 's.js-lzl-ing{' . Ui::GetStyleAttr(
				array(
					'background' => Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'wrapper_color' ) ),
					'transform' => ( ( $translate[ 0 ] || $translate[ 1 ] ) && Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'position' ) ) != 'outer-horizontal' ) ? ( 'translate(' . $translate[ 0 ] . ', ' . $translate[ 1 ] . ')!important' ) : null,
					'padding' => implode( ' ', $padding ) . '!important',
					'left' => _RevSld_GetSize( false, Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'h_offset' ), 0 ), $prefix[ 0 ] ) . '!important',
					'top' => _RevSld_GetSize( false, Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'v_offset' ), 20 ), $prefix[ 1 ] ) . '!important',
					'max-width' => 'unset !important;',
					'max-height' => 'unset !important;',
					'position' => ( Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'position' ) ) == 'outer-horizontal' ) ? 'relative' : null,
				)
			, false ) . '}';

			$contTabs = Ui::Tag( 'rs-' . $typeMeta[ 'sel' ] . 's',
				Ui::Tag( 'rs-navmask',
					Ui::Tag( 'rs-' . $typeMeta[ 'sel' ] . 's-wrap',
						$contTabs
					, array(
						'class' => array( 'tp-' . $typeMeta[ 'sel' ] . 's-inner-wrapper' ),
					) )
				, array(
					'class' => array( 'tp-' . $typeMeta[ 'sel' ] . '-mask' ),
				) )
			, array(
				'class' => array( 'js-lzl-ing', 'nav-dir-horizontal', 'nav-pos-ver-bottom', 'nav-pos-hor-center', 'rs-nav-element', 'tp-' . $typeMeta[ 'sel' ] . 's', 'tp-span-wrapper', 'inner', Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'style' ), '' ) ),
			) );

			if( Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'position' ) ) == 'outer-horizontal' )
				$item -> parentNode -> appendChild( HtmlNd::ParseAndImport( $doc, $contTabs ) );
			else
				$item -> appendChild( HtmlNd::ParseAndImport( $doc, $contTabs ) );

			_RevSld_HavHideMode( $itemStyleCont, $itemId, $prms, $type, 'rs-' . $typeMeta[ 'sel' ] . 's' );

			_RevSld_AdjustTimeoutByVal( $nSwitchingLoadingTimeout, $nSwitchingLoadingTimeoutMax, Gen::GetArrField( $prms, array( 'init', 'navigation', $type, 'animDelay' ) ) );
		}

		$aWidthUnique = array();
		for( $iDevice = 0; $iDevice < count( $aWidth ); $iDevice++ )
		{
			$width = $aWidth[ count( $aWidth ) - 1 - $iDevice ];
			if( !isset( $aWidthUnique[ $width ] ) )
				$aWidthUnique[ $width ] = $iDevice;
		}
		$aWidthUnique = array_reverse( $aWidthUnique, true );

		$iWidth = 0;
		$widthPrev = 0;
		foreach( $aWidthUnique as $width => $iDevice )
		{
			if( $aItemStyle[ $iDevice ] )
			{
				$itemStyleCont .= '@media';
				if( $iWidth > 0 )
					$itemStyleCont .= ' (min-width: ' . ( $widthPrev ) . 'px)';
				if( $iWidth > 0 && $iWidth < count( $aWidthUnique ) - 1 )
					$itemStyleCont .= ' and';
				if( $iWidth < count( $aWidthUnique ) - 1 )
					$itemStyleCont .= ' (max-width: ' . ( $width - 1 ) . 'px)';

				$itemStyleCont .= '{' . Ui::GetStyleSels( $aItemStyle[ $iDevice ] ) . '}';
			}

			$iWidth++;
			$widthPrev = $width;
		}

		if( $itemStyleCont )
		{
			$itemStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemStyle, $itemStyleCont );
			$item -> parentNode -> insertBefore( $itemStyle, $item );
		}

		$item -> setAttribute( 'style', Ui::GetStyleAttr( Ui::MergeStyleAttr( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( '--lzl-rs-scale' => '1' ) ) ) );

		$itemOrig = null;
		if( $bDblLoadFix )
		{
			$itemWrap = $item -> parentNode;
			$itemWrapOrig = $itemWrap -> cloneNode( true );
			HtmlNd::InsertBefore( $itemWrap -> parentNode, $itemWrapOrig, $itemWrap );
			$itemOrig = HtmlNd::FirstOfChildren( $xpath -> query( './rs-module', $itemWrapOrig ) );

		    HtmlNd::Remove( HtmlNd::ChildrenAsArr( $xpath -> query( './script', $itemWrap ) ) );
		}

		{
			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, 'seraph_accel_cp_sldRev_calcSizes_init(document,false)' );
			$item -> insertBefore( $itemScript, $item -> firstChild );
		}

		{
			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, 'seraph_accel_cp_sldRev_calcSizes_init(document,true)' );
			$item -> appendChild( $itemScript );
		}

		if( $bDblLoadFix )
		{
			$item -> setAttribute( 'id', $itemId );
			HtmlNd::AddRemoveAttrClass( $item -> parentNode, array( 'js-lzl-ing' ) );
			HtmlNd::AddRemoveAttrClass( $itemOrig -> parentNode, array( 'js-lzl-ing' ) );

			HtmlNd::AddRemoveAttrClass( $item, array( 'js-lzl-ing' ) );
			$itemOrig -> setAttribute( 'data-lzl-ing-t', ( string )$nSwitchingLoadingTimeout );

			HtmlNd::AddRemoveAttrClass( $itemOrig, '', array( 'js-lzl-nid' ) );

			foreach( array( './/rs-slides-lzl', './/rs-static-layers-lzl', './/rs-bullets', './/rs-arrow', './/rs-progress', './/rs-tabs', './/rs-thumbs' ) as $selItem )
			    HtmlNd::Remove( HtmlNd::ChildrenAsArr( $xpath -> query( $selItem, $itemOrig -> parentNode ) ) );

			foreach( array( './rs-slides', './rs-static-layers' ) as $selItem )
			    HtmlNd::Remove( HtmlNd::ChildrenAsArr( $xpath -> query( $selItem, $item ) ) );

			{
				$itemNoScript = $doc -> createElement( 'noscript' );
				$itemNoScript -> setAttribute( 'data-lzl-bjs', '' );
				HtmlNd::MoveChildren( $itemNoScript, $itemOrig );
				ContNoScriptItemClear( $itemNoScript );
				$itemOrig -> appendChild( $itemNoScript );

				$ctx -> bBjs = true;
			}

			{
				$itemScript = $doc -> createElement( 'script' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemScript -> setAttribute( 'type', 'text/javascript' );
				$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
				HtmlNd::SetValFromContent( $itemScript, 'seraph_accel_cp_sldRev_calcSizes_init(document,false)' );
				$itemOrig -> insertBefore( $itemScript, $itemOrig -> firstChild );
			}
		}
		else
		{
			foreach( $xpath -> query( './rs-slides//img', $item ) as $itemImg )
				HtmlNd::RenameAttr( $itemImg, 'src', 'data-lzl-src' );
		}

		$adjusted = true;
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.rev_break_columns@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@rs-fullwidth-wrap@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@rs-fw-forcer@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.js-lzl-nid@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@not\\(\\.js-lzl-ing@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@not\\(\\.seraph-accel-js-lzl-ing@' ] = true;

		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle,
				".rs-lzl-cont.js-lzl-ing > rs-slide,\r\n.rs-lzl-cont.js-lzl-ing *:not(.tp-video-play-button),\r\n.rs-lzl-cont.js-lzl-ing > rs-slide *:not(.tp-video-play-button) {\r\n\tvisibility: visible !important;\r\n\topacity: 1 !important;\r\n}\r\n\r\nrs-module.revslider-initialised > rs-tabs.js-lzl-ing,\r\nrs-module:not([style*=lzl-rs-scale]) .rs-lzl-cont.js-lzl-ing {\r\n\tvisibility: hidden !important;\r\n}\r\n\r\nrs-module-wrap {\r\n\tvisibility: visible !important;\r\n\theight: unset !important;\r\n}\r\n\r\nrs-module.revslider-initialised > .rs-lzl-cont.js-lzl-ing,\r\nrs-module:not(.revslider-initialised) > rs-static-layers:not(.js-lzl-ing),\r\nrs-module.revslider-initialised > tp-bullets.js-lzl-ing,\r\nrs-module.revslider-initialised > rs-arrow.js-lzl-ing,\r\n.rs-lzl-cont.js-lzl-ing .html5vid:not(:has(>video)),\r\n.js-lzl-ing-disp-none,\r\nrs-module.js-lzl-nid rs-slides-lzl [data-cbreak] {\r\n\tdisplay: none !important;\r\n}\r\n\r\n.js-lzl-ing .rev_row_zone_middle {\r\n\ttransform: translate(0,-50%);\r\n\ttop: calc(50%);\r\n}\r\n\r\n.rs-lzl-cont.js-lzl-ing rs-layer[data-type=\"image\"] img,\r\n.rs-lzl-cont.js-lzl-ing .rs-layer[data-type=\"image\"] img {\r\n\tobject-fit: fill;\r\n\twidth: 100%;\r\n\theight: 100%;\r\n}\r\n\r\n.rs-lzl-cont.js-lzl-ing [data-bubblemorph] svg {\r\n\tposition: absolute;\r\n\tleft: calc(var(--sz) / 2 + var(--buffer-x));\r\n\ttop: calc(var(--sz) / 2 + var(--buffer-y));\r\n\twidth: calc(100% - var(--sz) - 2 * var(--buffer-x));\r\n\theight: calc(100% - var(--sz) - 2 * var(--buffer-y));\r\n}\r\n\r\n.rs-lzl-cont.js-lzl-ing [data-bubblemorph] .bubbles.b-ext > circle {\r\n\tr: calc(0.97 * var(--sz) / 2);\r\n\tfill: white;\r\n}\r\n\r\n.rs-lzl-cont.js-lzl-ing [data-bubblemorph] .bubbles.b-int > circle {\r\n\tr: calc(0.97 * var(--sz) / 2 - var(--border-size));\r\n\tfill: black;\r\n}\r\n\r\n.rs-lzl-cont.js-lzl-ing [data-bubblemorph] .bubbles.body > circle {\r\n\tr: calc(0.97 * var(--sz) / 2 - var(--border-size));\r\n\tfill: white;\r\n}\r\n\r\n.rs-lzl-cont.js-lzl-ing [data-bubblemorph] .bubbles {\r\n\t-webkit-filter: var(--flt);\r\n\tfilter: var(--flt);\r\n}\r\n\r\n.rs-lzl-cont.js-lzl-ing [data-bubblemorph] rect[mask] {\r\n\tx: calc(-1 * var(--sz) / 2);\r\n\ty: calc(-1 * var(--sz) / 2);\r\n\twidth: calc(100% + var(--sz));\r\n\theight: calc(100% + var(--sz));\r\n}"
				. ( $bDblLoadFix ?
					"rs-module-wrap.js-lzl-ing:has(rs-module:not(.js-lzl-ing)),\r\nrs-module-wrap:has(rs-module.js-lzl-ing-fin) {\r\n\topacity: 0 !important;\r\n}\r\n\r\nrs-module-wrap.js-lzl-ing:has(rs-module:not(.js-lzl-ing):not([data-lzl-layout=\"fullscreen\"])) {\r\n\theight: calc(1px * var(--lzl-rs-cy)) !important;\r\n}\r\n\r\nrs-module-wrap:not(.js-lzl-ing) {\r\n\ttransition: opacity 1000ms ease-in-out;\r\n}\r\n\r\nbody:not(.seraph-accel-js-lzl-ing) rs-module-wrap:has(rs-module:not(.js-lzl-ing)) {\r\n\tz-index: 10 !important;\r\n}\r\n\r\nbody:not(.seraph-accel-js-lzl-ing) rs-module-wrap:has(rs-module.js-lzl-ing) {\r\n\tz-index: 9 !important;\r\n}"
				: "" )
			);
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}

		{

			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, str_replace( array( '_PRM__ADJUSTED_BUBBLES_', '_PRM_RESTORE_IMGSRC_' ), array( $adjustedBubbles ? '1' : '0', $bDblLoadFix ? '0' : '1' ), "function seraph_accel_cp_sldRev_calcSizes_init(a,d){seraph_accel_cmn_calcSizes(a.documentElement);seraph_accel_cp_sldRev_calcSizes(a.currentScript.parentNode,d)}\nfunction seraph_accel_cp_sldRev_calcSizes(a,d){for(var b=JSON.parse(a.getAttribute(\"data-lzl-widths\")),c=JSON.parse(a.getAttribute(\"data-lzl-widths-g\")),f=JSON.parse(a.getAttribute(\"data-lzl-heights-g\")),h=!!a.getAttribute(\"data-lzl-g-s\"),e=0;e<b.length&&!(window.innerWidth<b[e]);e++);e==b.length&&(e=b.length-1);var k=b.length-1-e;b=a.clientWidth/c[e];1<b&&(b=1);f=(a.clientHeight-f[e]*(h?b:1))/2;0>f&&(f=0);c=(a.clientWidth-c[e])/2;0>c&&(c=0);a.style.setProperty(\"--lzl-rs-scale\",b);a.style.setProperty(\"--lzl-rs-diff-y\",\nf);a.style.setProperty(\"--lzl-rs-extra-x\",c);a.classList.contains(\"js-lzl-ing\")&&(a.parentNode.style.setProperty(\"--lzl-rs-cy\",a.parentNode.clientHeight),(c=a.parentNode.parentNode.querySelector(\"#\"+a.parentNode.getAttribute(\"id\")+\":has(rs-module:not(.js-lzl-ing))\"))&&c.style.setProperty(\"--lzl-rs-cy\",a.parentNode.clientHeight));!1!==d&&(a.querySelectorAll(\"rs-slides-lzl [data-cbreak]\").forEach(function(g){parseInt(g.getAttribute(\"data-cbreak\"),10)<=k?g.classList.add(\"rev_break_columns\"):g.classList.remove(\"rev_break_columns\")}),\n!0===d&&a.classList.remove(\"js-lzl-nid\"))}function seraph_accel_cp_sldRev_bubblemorph_calcSizes(a){var d=Math.max(a.clientWidth,a.clientHeight)/5;a.style.setProperty(\"--sz\",\"\"+d+\"px\");a.style.setProperty(\"--flt\",'url(\"#'+a.id+\"-f-blur\"+(30<=d?\"\":\"-sm\")+'\")')}\nfunction seraph_accel_cp_sldRev_loadFinish(a,d,b=!0){if(a.hasAttribute(\"data-lzl-ing-t\")){b=b?parseInt(a.getAttribute(\"data-lzl-ing-t\"),10):0;a.removeAttribute(\"data-lzl-ing-t\");var c=document.querySelector(\"#\"+d+\".js-lzl-ing\");c&&setTimeout(function(){a.parentNode.classList.remove(\"js-lzl-ing\");c.parentNode.classList.remove(\"js-lzl-ing\");setTimeout(function(){c.classList.add(\"js-lzl-ing-fin\");setTimeout(function(){setTimeout(function(){c.parentNode.remove()},0)},1E3)},1E3)},b)}}\n(function(a){function d(b){a.querySelectorAll(\"rs-module:not(.revslider-initialised)[data-lzl-widths]\").forEach(seraph_accel_cp_sldRev_calcSizes);_PRM__ADJUSTED_BUBBLES_&&a.querySelectorAll(\"rs-module:not(.revslider-initialised) .rs-lzl-cont.js-lzl-ing [data-bubblemorph]\").forEach(seraph_accel_cp_sldRev_bubblemorph_calcSizes)}a.addEventListener(\"seraph_accel_calcSizes\",d,{capture:!0,passive:!0});seraph_accel_izrbpb.add(function(){a.removeEventListener(\"seraph_accel_calcSizes\",d,{capture:!0,passive:!0});\n_PRM_RESTORE_IMGSRC_&&a.querySelectorAll(\"rs-slides img\").forEach(function(b){b.hasAttribute(\"data-lzl-src\")&&b.setAttribute(\"src\",b.getAttribute(\"data-lzl-src\"))})})})(document,_PRM__ADJUSTED_BUBBLES_,_PRM_RESTORE_IMGSRC_)" ) );
			$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
		}
	}
}

function _ProcessCont_Cp_sldRev7( $ctx, &$ctxProcess, $doc, $xpath )
{
	$adjusted = false;
	foreach( HtmlNd::ChildrenAsArr( $xpath -> query( './/sr7-module' ) ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$adjusted = true;
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@(?:^|\\W|\\.)sr7-@' ] = true;

		$ctxProcess[ 'aJsCrit' ][ 'src:@/revslider/public/js@' ] = true;
		$ctxProcess[ 'aJsCrit' ][ 'body:@(?:^|\\W)SR7\\.\\w@' ] = true;
	}
}

function _RevSld_GetEngineVer( &$ctxProcess, $xpath )
{
	$engineVer = '9999.9999';

	$itemEngineScr = HtmlNd::FirstOfChildren( $xpath -> query( './/script[@id="revmin-js"]' ) );
	if( !$itemEngineScr )
		return( $engineVer );

	$src = $itemEngineScr -> getAttribute( 'src' );
	$srcInfo = GetSrcAttrInfo( $ctxProcess, null, null, $src );

	$cont = null;
	if( (isset($srcInfo[ 'filePath' ])?$srcInfo[ 'filePath' ]:null) )
		$cont = @file_get_contents( $srcInfo[ 'filePath' ] );
	if( !$cont )
		$cont = GetExtContents( (isset($srcInfo[ 'url' ])?$srcInfo[ 'url' ]:null), $contMimeType );

	if( !preg_match( '@"Slider\\sRevolution\\s([\\d\\.]+)"@', $cont, $m ) )
		return( $engineVer );

	$engineVer = $m[ 1 ];
	return( $engineVer );
}

function _RevSld_GetPrmsFromScr( $item, $itemInitCmnScr, $itemIdTmp )
{
	if( !$itemInitCmnScr )
		return( null );

	$prms = array();

	for( $itemInitScr = $item -> nextSibling; $itemInitScr; $itemInitScr = $itemInitScr -> nextSibling )
	{
		if( $itemInitScr -> nodeName != 'script' )
			continue;

		$m = array();
		if( !preg_match( '@^\\s*setREVStartSize\\(\\s*({[^}]*})@', $itemInitScr -> nodeValue, $m ) )
			continue;

		$m = @json_decode( Gen::JsObjDecl2Json( $m[ 1 ] ), true );
		if( !$m )
			return( null );

		$prms[ 'start' ] = $m;
		break;
	}

	if( !$itemInitScr )
		return( null );

	$cmdScrId = array();
	if( !preg_match( '@\\.\\s*RS_MODULES\\s*.\\s*modules\\s*\\[\\s*["\']([\\w\\-]+)["\']\\s*\\]@', $itemInitScr -> nodeValue, $cmdScrId ) )
		return( null );

	$cmdScrId = $cmdScrId[ 1 ];

	$posStart = array();
	if( !preg_match( '@\\WRS_MODULES\\s*.\\s*modules\\s*\\[\\s*["\']' . $cmdScrId . '["\']\\s*\\]\\s*=\\s*{@', $itemInitCmnScr -> nodeValue, $posStart, PREG_OFFSET_CAPTURE ) )
		return( null );

	$posStart = $posStart[ 0 ][ 1 ] + strlen( $posStart[ 0 ][ 0 ] );

	if( !preg_match( '@\\W(\\w+)\\.revolutionInit\\s*\\(\\s*@', $itemInitCmnScr -> nodeValue, $posStartInit, PREG_OFFSET_CAPTURE, $posStart ) )
		return( null );

	$posStart = $posStartInit[ 0 ][ 1 ] + strlen( $posStartInit[ 0 ][ 0 ] );
	$pos = Gen::JsonGetEndPos( $posStart, $itemInitCmnScr -> nodeValue );
	if( $pos === null )
		return( null );

	$prms[ 'init' ] = @json_decode( Gen::JsObjDecl2Json( substr( $itemInitCmnScr -> nodeValue, $posStart, $pos - $posStart ) ), true );

	$aCssCleanSelLate = array( '.rs-lzl-cont.js-lzl-ing' );
	$aCssCleanSel = array(  );
	if( Gen::GetArrField( $prms, array( 'init', 'navigation', 'bullets', 'enable' ) ) )
		$aCssCleanSel[] = 'rs-bullets.js-lzl-ing';
	if( Gen::GetArrField( $prms, array( 'init', 'navigation', 'tabs', 'enable' ) ) )
		$aCssCleanSel[] = 'rs-tabs.js-lzl-ing';
	if( Gen::GetArrField( $prms, array( 'init', 'navigation', 'thumbnails', 'enable' ) ) )
		$aCssCleanSel[] = 'rs-thumbs.js-lzl-ing';
	if( $aCssCleanSelLate || $aCssCleanSel || $itemIdTmp )
		$itemInitCmnScr -> nodeValue = substr_replace( $itemInitCmnScr -> nodeValue,
			( $aCssCleanSelLate && !$itemIdTmp ? ( $posStartInit[ 1 ][ 0 ] . '.on( "revolution.slide.onloaded", function(){jQuery(this).children("' . implode( ',', $aCssCleanSelLate ) . '").remove();});' ) : '' ) .
			( $aCssCleanSel && !$itemIdTmp ? ( $posStartInit[ 1 ][ 0 ] . '.on( "revolution.slide.afterdraw", function(){jQuery(this.parentNode).find("' . implode( ',', $aCssCleanSel ) . '").remove();});' ) : '' ) .
			( $itemIdTmp ? ( $posStartInit[ 1 ][ 0 ] . '.on( "revolution.slide.onchange", function(){seraph_accel_cp_sldRev_loadFinish(this,"' . $itemIdTmp . '");});' ) : '' ) .
			''
		, $posStartInit[ 1 ][ 1 ], 0 );

	return( $prms );
}

function _RevSld_GetAttrs( $data, $nValsForce = false, $valSep = ',' )
{
	$res = array();
	foreach( explode( ';', $data ) as $e )
	{
		if( !strlen( $e ) )
			continue;

		$e = explode( ':', $e );
		if( count( $e ) > 2 )
			continue;

		if( count( $e ) < 2 )
			array_splice( $e, 0, 0, array( '' ) );

		$iBracket = 0;
		for( $i = 0; $i < strlen( $e[ 1 ] ); $i++ )
		{
			$c = $e[ 1 ][ $i ];
			if( $c == '(' )
				$iBracket++;
			else if( $c == ')' )
				$iBracket--;
			else if( $iBracket > 0 && $c == ',' )
				$e[ 1 ][ $i ] = "\xFF";
		}

		if( strpos( $e[ 1 ], $valSep ) !== false )
		{
			$e[ 1 ] = array_map(
				function( $e )
				{
					$e = trim( $e, " \t\n\r\0\x0B[]'" );
					return( $e );
				}
			, explode( $valSep, $e[ 1 ] ) );
		}
		else if( Gen::StrStartsWith( $e[ 1 ], 'cyc(' ) )
			$e[ 1 ] = array( 'cyc' => array_map( 'trim', explode( '|', substr( $e[ 1 ], 4, -1 ) ) ) );
		else if( $nValsForce )
			$e[ 1 ] = array_fill( 0, $nValsForce, $e[ 1 ] );

		$e[ 1 ] = Gen::StrReplace( "\xFF", ',', $e[ 1 ] );

		$res[ $e[ 0 ] ] = $e[ 1 ];
	}

	return( $res );
}

function _RevSld_GetSize( $scaleInit, $sz, $prefix = '', $suffix = '' )
{
	if( $sz === null )
		return( null );

	$res = '';

	$szSuffix = array();
	if( preg_match( '@\\D+$@', $sz, $szSuffix ) )
	{
		$szSuffix = $szSuffix[ 0 ];
		$sz = substr( $sz, 0, -strlen( $szSuffix ) );
	}
	else
		$szSuffix = '';

	$scale = false;
	if( !$szSuffix )
		$szSuffix = 'px';

	if( $szSuffix == 'px' && ( float )$sz )
		$scale = $scaleInit;

	$calc = false;
	if( $scale || $prefix || $suffix )
		$calc = true;

	if( $calc )
		$res .= 'calc(';
	if( $prefix )
		$res .= $prefix;
	$res .= $sz . $szSuffix;
	if( $scale )
		$res .= ' * var(--lzl-rs-scale)';
	if( $suffix )
		$res .= $suffix;
	if( $calc )
		$res .= ')';

	return( $res );
}

function _RevSld_SetStyleAttrEx( &$aItemStyle, $itemChildSelector, $i, $styles )
{
	$aDst = &$aItemStyle[ $i ][ $itemChildSelector ];

	if( !is_array( $aDst ) )
	{
		$aDst = $styles;
		return;
	}

	if( isset( $styles[ 'transform' ] ) && isset( $aDst[ 'transform' ] ) )
	{
		$aDst[ 'transform' ] = ( Gen::StrEndsWith( $aDst[ 'transform' ], '!important' ) ? substr( $aDst[ 'transform' ], 0, strlen( $aDst[ 'transform' ] ) - 10 ) : $aDst[ 'transform' ] ) . ' ' . $styles[ 'transform' ];
		unset( $styles[ 'transform' ] );
	}

	$aDst = array_merge( $aDst, $styles );
}

function _RevSld_SetStyleAttr( &$styleSeparated, &$aItemStyle, $itemChildSelector, $a )
{
	if( count( $a ) == 1 )
	{
		$styleSeparated = array_merge( $styleSeparated, $a[ 0 ] );
		return;
	}

	foreach( $a as $i => $styles )
		_RevSld_SetStyleAttrEx( $aItemStyle, $itemChildSelector, $i, $styles );
}

function _RevSld_GetIdxPropVal( $props, $path, $i, $vDef = null )
{
	$props = ( array )Gen::GetArrField( $props, $path );
	$v = Gen::GetArrField( $props, array( $i ) );
	if( $v === null && $i !== 0 )
		$v = Gen::GetArrField( $props, array( 0 ) );
	return( $v !== null ? $v : $vDef );
}

function _RevSld_HavHideMode( &$itemStyleCont, $itemId, $prms, $type, $sel )
{
	foreach( array( 'hide_under' => array( 'l' => 'max', 'o' => -1 ), 'hide_over' => array( 'l' => 'min', 'o' => 0 ) ) as $hideMode => $hideLim )
		if( $v = ( int )Gen::GetArrField( $prms, array( 'init', 'navigation', $type, $hideMode ) ) )
			$itemStyleCont .= '@media (' . $hideLim[ 'l' ] . '-width: ' . ( $v + $hideLim[ 'o' ] ) . 'px){#' . $itemId . ' ' . $sel . '.js-lzl-ing{display:none!important;}}';
}

function _RevSld_AdjustTimeoutByVal( &$nTimeout, $nTimeoutMax, $v, $vAdd = 0 )
{
	if( !is_int( $v ) )
	{
		if( is_array( $v ) )
		{
			$bApply = true;
			foreach( $v as $vI )
				if( !_RevSld_AdjustTimeoutByVal( $nTimeout, $nTimeoutMax, $vI ) )
					$bApply = false;
			return( $bApply );
		}
		else if( is_string( $v ) )
		{
			if( Gen::StrEndsWith( $v, 'ms' ) )
				$v = ( int )$v;
			else if( Gen::StrEndsWith( $v, 's' ) )
				$v = ( int )$v * 1000;
			else
				$v = ( int )$v;
		}
		else
			$v = 0;
	}

	if( $vAdd )
	{
		$vAdd2 = 0; _RevSld_AdjustTimeoutByVal( $vAdd2, null, $vAdd );
		$v = $v + $vAdd2;
	}

	if( $nTimeoutMax !== null && $v > $nTimeoutMax )
		return( false );

	if( $nTimeout < $v )
		$nTimeout = $v;

	return( true );
}

function _ProcessCont_Cp_sldWndr3dCrsl( $ctx, &$ctxProcess, $doc, $xpath )
{
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," wonderplugin3dcarousel ")]' ) as $item )
	{
	    if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
	        continue;
	}

}

function _ProcessCont_Cp_upbAni( $ctx, &$ctxProcess, $doc, $xpath )
{
	if( $ctxProcess[ 'mode' ] != 'full' )
		return;

	if( HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," wpb_animate_when_almost_visible ")]' ) ) )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.wpb_start_animation@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.animated@' ] = true;

		$ctx -> aAniAppear[ '.wpb_animate_when_almost_visible:not(.wpb_start_animation)' ] = "function(a){a.classList.add(\"wpb_start_animation\");a.classList.add(\"animated\")}";
	}
}

function _ProcessCont_Cp_upbBgImg( $ctx, &$ctxProcess, $doc, $xpath )
{
	$nSepId = 1;

	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," upb_bg_img ")][@data-ultimate-bg]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$dataSett = $item -> getAttribute( 'data-ultimate-bg' );
		if( !$dataSett )
			continue;

		if( !( $itemRow = _Upb_GetNearestRow( $item ) ) )
			continue;

		$bgOverride = HtmlNd::GetAttr( $item, 'data-bg-override' );
		$themeSupport = HtmlNd::GetAttr( $item, 'data-theme-support' );

		HtmlNd::AddRemoveAttrClass( $item, array( 'upb_row_bg', HtmlNd::GetAttr( $item, 'data-ultimate-bg-style' ) ), array( 'upb_bg_img' ) );
		if( $item -> getAttribute( 'data-overlay' ) == 'true' )
			$item -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => 'upb_bg_overlay', 'style' => array( 'background-color' => HtmlNd::GetAttr( $item, 'data-overlay-color' ) ) ) ) );
		if( $item -> getAttribute( 'data-theme-support' ) === '' )
			$item -> removeAttribute( 'data-theme-support' );
		$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( 'background-size' => HtmlNd::GetAttr( $item, 'data-bg-img-size' ), 'background-repeat' => HtmlNd::GetAttr( $item, 'data-bg-img-repeat' ), 'background-position' => HtmlNd::GetAttr( $item, 'data-bg-img-position' ), 'background-image' => HtmlNd::GetAttr( $item, 'data-ultimate-bg' ), 'background-color' => 'rgba(0, 0, 0, 0)', 'background-attachment' => HtmlNd::GetAttr( $item, 'data-bg_img_attach' ) ) ) ) );

		if( $bgOverride == 'browser_size' )
			$itemRow -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => array( 'upb-background-text-wrapper', 'full-browser-size' ), 'style' => array( 'height' => '100vh' ) ), array( HtmlNd::CreateTag( $doc, 'div', array( 'class' => 'upb-background-text vc_row wpb_row vc_row-fluid vc_row-o-equal-height vc_row-o-content-middle vc_row-flex' ), HtmlNd::ChildrenAsArr( $itemRow -> childNodes ) ) ) ) );

		$itemRow -> insertBefore( $item, $itemRow -> firstChild );
		HtmlNd::AddRemoveAttrClass( $itemRow, Ui::ParseClassAttr( $item -> getAttribute( 'data-hide-row' ) ) );
		$itemRow -> setAttribute( 'data-rtl', $item -> getAttribute( 'data-rtl' ) );
		$itemRow -> setAttribute( 'data-row-effect-mobile-disable', $item -> getAttribute( 'data-row-effect-mobile-disable' ) );
		$itemRow -> setAttribute( 'data-img-parallax-mobile-disable', $item -> getAttribute( 'data-img-parallax-mobile-disable' ) );

		if( $themeSupport !== null && $themeSupport !== 'enable' )
			$itemContainer = null;
		else
			$itemContainer = $item -> parentNode;
		HtmlNd::AddRemoveAttrClass( $itemContainer, array( 'vc_row-has-fill' ) );

		if( $item -> getAttribute( 'data-seperator' ) == 'true' )
		{

			$o = $item->getAttribute("data-seperator-type");
			$s = (int)$item->getAttribute("data-seperator-shape-size");
			$i = $item->getAttribute("data-seperator-background-color");
			$l = $item->getAttribute("data-seperator-border");
			$d = $item->getAttribute("data-seperator-border-color");
			$n = $item->getAttribute("data-seperator-border-width");
			$p = $item->getAttribute("data-seperator-svg-height");
			$c = $item->getAttribute("data-seperator-full-width");
			$u = HtmlNd::GetAttr($item,"data-seperator-position");
			if($u===null)
				$u = "top_seperator";
			$v = HtmlNd::GetAttr($item,"data-icon");
			$v = null === $v ? "" : '<div class="separator-icon">' . $v . "</div>";
			$h = $seperator_class = $seperator_border_css = $seperator_border_line_css = $seperator_css = "";

			$_ = $shape_css = $svg = $inner_html = $seperator_css = "";
			$t = !1;
			$b = "uvc-seperator-" . $nSepId++;
			$g;
			$m = $s / 2;
			$e = 0;
			if ("triangle_seperator" == $o)
				$seperator_class = "ult-trinalge-seperator";
			else if ("circle_seperator" == $o)
				$seperator_class = "ult-circle-seperator";
			else if ("diagonal_seperator" == $o)
				$seperator_class = "ult-double-diagonal";
			else if ("triangle_svg_seperator" == $o)
			{
				$seperator_class = "ult-svg-triangle";
				$svg = '<svg class="uvc-svg-triangle" xmlns="http://www.w3.org/2000/svg" version="1.1" fill="' . $i . '" width="100%" height="' . $p . '" viewBox="0 0 0.156661 0.1"><polygon points="0.156661,3.93701e-006 0.156661,0.000429134 0.117665,0.05 0.0783307,0.0999961 0.0389961,0.05 -0,0.000429134 -0,3.93701e-006 0.0783307,3.93701e-006 "/></svg>';
				$t = !0;
			}
			else if ("circle_svg_seperator" == $o)
			{
				$seperator_class = "ult-svg-circle";
				$svg = '<svg class="uvc-svg-circle" xmlns="http://www.w3.org/2000/svg" version="1.1" fill="' . $i . '" width="100%" height="' . $p . '" viewBox="0 0 0.2 0.1"><path d="M0.200004 0c-3.93701e-006,0.0552205 -0.0447795,0.1 -0.100004,0.1 -0.0552126,0 -0.0999921,-0.0447795 -0.1,-0.1l0.200004 0z"/></svg>';
				$t = !0;
			}
			else if ("xlarge_triangle_seperator" == $o)
			{
				$seperator_class = "ult-xlarge-triangle";
				$svg = '<svg class="uvc-x-large-triangle" xmlns="http://www.w3.org/2000/svg" version="1.1" fill="' . $i . '" width="100%" height="' . $p . '" viewBox="0 0 4.66666 0.333331" preserveAspectRatio="none"><path class="fil0" d="M-0 0.333331l4.66666 0 0 -3.93701e-006 -2.33333 0 -2.33333 0 0 3.93701e-006zm0 -0.333331l4.66666 0 0 0.166661 -4.66666 0 0 -0.166661zm4.66666 0.332618l0 -0.165953 -4.66666 0 0 0.165953 1.16162 -0.0826181 1.17171 -0.0833228 1.17171 0.0833228 1.16162 0.0826181z"/></svg>';
				$t = !0;
			}
			else if ("xlarge_triangle_left_seperator" == $o)
			{
				$seperator_class = "ult-xlarge-triangle-left";
				$svg = '<svg class="uvc-x-large-triangle-left" xmlns="http://www.w3.org/2000/svg" version="1.1" fill="' . $i . '" width="100%" height="' . $p . '" viewBox="0 0 2000 90" preserveAspectRatio="none"><polygon xmlns="http://www.w3.org/2000/svg" points="535.084,64.886 0,0 0,90 2000,90 2000,0 "></polygon></svg>';
				$t = !0;
			}
			else if ("xlarge_triangle_right_seperator" == $o)
			{
				$seperator_class = "ult-xlarge-triangle-right";
				$svg = '<svg class="uvc-x-large-triangle-right" xmlns="http://www.w3.org/2000/svg" version="1.1" fill="' . $i . '" width="100%" height="' . $p . '" viewBox="0 0 2000 90" preserveAspectRatio="none"><polygon xmlns="http://www.w3.org/2000/svg" points="535.084,64.886 0,0 0,90 2000,90 2000,0 "></polygon></svg>';
				$t = !0;
			}
			else if ("xlarge_circle_seperator" == $o)
			{
				$seperator_class = "ult-xlarge-circle";
				$svg = '<svg class="uvc-x-large-circle" xmlns="http://www.w3.org/2000/svg" version="1.1" fill="' . $i . '" width="100%" height="' . $p . '" viewBox="0 0 4.66666 0.333331" preserveAspectRatio="none"><path class="fil1" d="M4.66666 0l0 7.87402e-006 -3.93701e-006 0c0,0.0920315 -1.04489,0.166665 -2.33333,0.166665 -1.28844,0 -2.33333,-0.0746339 -2.33333,-0.166665l-3.93701e-006 0 0 -7.87402e-006 4.66666 0z"/></svg>';
				$t = !0;
			}
			else if ("curve_up_seperator" == $o)
			{
				$seperator_class = "ult-curve-up-seperator";
				$svg = '<svg class="curve-up-inner-seperator uvc-curve-up-seperator" xmlns="http://www.w3.org/2000/svg" version="1.1" fill="' . $i . '" width="100%" height="' . $p . '" viewBox="0 0 4.66666 0.333331" preserveAspectRatio="none"><path class="fil0" d="M-7.87402e-006 0.0148858l0.00234646 0c0.052689,0.0154094 0.554437,0.154539 1.51807,0.166524l0.267925 0c0.0227165,-0.00026378 0.0456102,-0.000582677 0.0687992,-0.001 1.1559,-0.0208465 2.34191,-0.147224 2.79148,-0.165524l0.0180591 0 0 0.166661 -7.87402e-006 0 0 0.151783 -4.66666 0 0 -0.151783 -7.87402e-006 0 0 -0.166661z"/></svg>';
				$t = !0;
			}
			else if ("curve_down_seperator" == $o)
			{
				$seperator_class = "ult-curve-down-seperator";
				$svg = '<svg class="curve-down-inner-seperator uvc-curve-down-seperator" xmlns="http://www.w3.org/2000/svg" version="1.1" fill="' . $i . '" width="100%" height="' . $p . '" viewBox="0 0 4.66666 0.333331" preserveAspectRatio="none"><path class="fil0" d="M-7.87402e-006 0.0148858l0.00234646 0c0.052689,0.0154094 0.554437,0.154539 1.51807,0.166524l0.267925 0c0.0227165,-0.00026378 0.0456102,-0.000582677 0.0687992,-0.001 1.1559,-0.0208465 2.34191,-0.147224 2.79148,-0.165524l0.0180591 0 0 0.166661 -7.87402e-006 0 0 0.151783 -4.66666 0 0 -0.151783 -7.87402e-006 0 0 -0.166661z"/></svg>';
				$t = !0;
			}
			else if ("tilt_left_seperator" == $o)
			{
				$seperator_class = "ult-tilt-left-seperator";
				$svg = '<svg class="uvc-tilt-left-seperator" xmlns="http://www.w3.org/2000/svg" version="1.1" fill="' . $i . '" width="100%" height="' . $p . '" viewBox="0 0 4 0.266661" preserveAspectRatio="none"><polygon class="fil0" points="4,0 4,0.266661 -0,0.266661 "/></svg>';
				$t = !0;
			}
			else if ("tilt_right_seperator" == $o)
			{
				$seperator_class = "ult-tilt-right-seperator";
				$svg = '<svg class="uvc-tilt-right-seperator" xmlns="http://www.w3.org/2000/svg" version="1.1" fill="' . $i . '" width="100%" height="' . $p . '" viewBox="0 0 4 0.266661" preserveAspectRatio="none"><polygon class="fil0" points="4,0 4,0.266661 -0,0.266661 "/></svg>';
				$t = !0;
			}
			else if ("waves_seperator" == $o)
			{
				$seperator_class = "ult-wave-seperator";
				$svg = '<svg class="wave-inner-seperator uvc-wave-seperator" xmlns="http://www.w3.org/2000/svg" version="1.1" fill="' . $i . '" width="100%" height="' . $p . '" viewBox="0 0 6 0.1" preserveAspectRatio="none"><path d="M0.199945 0c3.93701e-006,0.0552205 0.0447795,0.1 0.100004,0.1l-0.200008 0c-0.0541102,0 -0.0981929,-0.0430079 -0.0999409,-0.0967008l0 0.0967008 0.0999409 0c0.0552244,0 0.1,-0.0447795 0.100004,-0.1zm0.200004 0c7.87402e-006,0.0552205 0.0447874,0.1 0.1,0.1l-0.2 0c0.0552126,0 0.0999921,-0.0447795 0.1,-0.1zm0.200004 0c3.93701e-006,0.0552205 0.0447795,0.1 0.100004,0.1l-0.200008 0c0.0552244,0 0.1,-0.0447795 0.100004,-0.1zm0.200004 0c7.87402e-006,0.0552205 0.0447874,0.1 0.1,0.1l-0.2 0c0.0552126,0 0.0999921,-0.0447795 0.1,-0.1zm0.200004 0c3.93701e-006,0.0552205 0.0447795,0.1 0.100004,0.1l-0.200008 0c0.0552244,0 0.1,-0.0447795 0.100004,-0.1zm0.200004 0c7.87402e-006,0.0552205 0.0447874,0.1 0.1,0.1l-0.2 0c0.0552126,0 0.0999921,-0.0447795 0.1,-0.1zm0.200004 0c3.93701e-006,0.0552205 0.0447795,0.1 0.100004,0.1l-0.200008 0c0.0552244,0 0.1,-0.0447795 0.100004,-0.1zm0.200004 0c7.87402e-006,0.0552205 0.0447874,0.1 0.1,0.1l-0.2 0c0.0552126,0 0.0999921,-0.0447795 0.1,-0.1zm0.200004 0c3.93701e-006,0.0552205 0.0447795,0.1 0.100004,0.1l-0.200008 0c0.0552244,0 0.1,-0.0447795 0.100004,-0.1zm0.200004 0c7.87402e-006,0.0552205 0.0447874,0.1 0.1,0.1l-0.2 0c0.0552126,0 0.0999921,-0.0447795 0.1,-0.1zm2.00004 0c7.87402e-006,0.0552205 0.0447874,0.1 0.1,0.1l-0.2 0c0.0552126,0 0.0999921,-0.0447795 0.1,-0.1zm-0.1 0.1l-0.200008 0c-0.0552126,0 -0.0999921,-0.0447795 -0.1,-0.1 -7.87402e-006,0.0552205 -0.0447874,0.1 -0.1,0.1l0.2 0c0.0552244,0 0.1,-0.0447795 0.100004,-0.1 3.93701e-006,0.0552205 0.0447795,0.1 0.100004,0.1zm-0.400008 0l-0.200008 0c-0.0552126,0 -0.0999921,-0.0447795 -0.1,-0.1 -7.87402e-006,0.0552205 -0.0447874,0.1 -0.1,0.1l0.2 0c0.0552244,0 0.1,-0.0447795 0.100004,-0.1 3.93701e-006,0.0552205 0.0447795,0.1 0.100004,0.1zm-0.400008 0l-0.200008 0c-0.0552126,0 -0.0999921,-0.0447795 -0.1,-0.1 -7.87402e-006,0.0552205 -0.0447874,0.1 -0.1,0.1l0.2 0c0.0552244,0 0.1,-0.0447795 0.100004,-0.1 3.93701e-006,0.0552205 0.0447795,0.1 0.100004,0.1zm-0.400008 0l-0.200008 0c-0.0552126,0 -0.0999921,-0.0447795 -0.1,-0.1 -7.87402e-006,0.0552205 -0.0447874,0.1 -0.1,0.1l0.2 0c0.0552244,0 0.1,-0.0447795 0.100004,-0.1 3.93701e-006,0.0552205 0.0447795,0.1 0.100004,0.1zm-0.400008 0l-0.200008 0c0.0552244,0 0.1,-0.0447795 0.100004,-0.1 3.93701e-006,0.0552205 0.0447795,0.1 0.100004,0.1zm1.90004 -0.1c3.93701e-006,0.0552205 0.0447795,0.1 0.100004,0.1l-0.200008 0c0.0552244,0 0.1,-0.0447795 0.100004,-0.1zm0.200004 0c7.87402e-006,0.0552205 0.0447874,0.1 0.1,0.1l-0.2 0c0.0552126,0 0.0999921,-0.0447795 0.1,-0.1zm0.200004 0c3.93701e-006,0.0552205 0.0447795,0.1 0.100004,0.1l-0.200008 0c0.0552244,0 0.1,-0.0447795 0.100004,-0.1zm0.200004 0c7.87402e-006,0.0552205 0.0447874,0.1 0.1,0.1l-0.2 0c0.0552126,0 0.0999921,-0.0447795 0.1,-0.1zm0.200004 0c3.93701e-006,0.0552205 0.0447795,0.1 0.100004,0.1l-0.200008 0c0.0552244,0 0.1,-0.0447795 0.100004,-0.1zm0.200004 0c7.87402e-006,0.0552205 0.0447874,0.1 0.1,0.1l-0.2 0c0.0552126,0 0.0999921,-0.0447795 0.1,-0.1zm0.200004 0c3.93701e-006,0.0552205 0.0447795,0.1 0.100004,0.1l-0.200008 0c0.0552244,0 0.1,-0.0447795 0.100004,-0.1zm0.200004 0c7.87402e-006,0.0552205 0.0447874,0.1 0.1,0.1l-0.2 0c0.0552126,0 0.0999921,-0.0447795 0.1,-0.1zm0.200004 0c3.93701e-006,0.0552205 0.0447795,0.1 0.100004,0.1l-0.200008 0c0.0552244,0 0.1,-0.0447795 0.100004,-0.1zm0.199945 0.00329921l0 0.0967008 -0.0999409 0c0.0541102,0 0.0981929,-0.0430079 0.0999409,-0.0967008z"/></svg>';
				$t = !0;
			}
			else if ("clouds_seperator" == $o)
			{
				$seperator_class = "ult-cloud-seperator";
				$svg = '<svg class="cloud-inner-seperator uvc-cloud-seperator" xmlns="http://www.w3.org/2000/svg" version="1.1" fill="' . $i . '" width="100%" height="' . $p . '" viewBox="0 0 2.23333 0.1" preserveAspectRatio="none"><path class="fil0" d="M2.23281 0.0372047c0,0 -0.0261929,-0.000389764 -0.0423307,-0.00584252 0,0 -0.0356181,0.0278268 -0.0865354,0.0212205 0,0 -0.0347835,-0.00524803 -0.0579094,-0.0283701 0,0 -0.0334252,0.0112677 -0.0773425,-0.00116929 0,0 -0.0590787,0.0524724 -0.141472,0.000779528 0,0 -0.0288189,0.0189291 -0.0762362,0.0111535 -0.00458268,0.0141024 -0.0150945,0.040122 -0.0656811,0.0432598 -0.0505866,0.0031378 -0.076126,-0.0226614 -0.0808425,-0.0308228 -0.00806299,0.000854331 -0.0819961,0.0186969 -0.111488,-0.022815 -0.0076378,0.0114843 -0.059185,0.0252598 -0.083563,-0.000385827 -0.0295945,0.0508661 -0.111996,0.0664843 -0.153752,0.019 -0.0179843,0.00227559 -0.0571181,0.00573622 -0.0732795,-0.0152953 -0.027748,0.0419646 -0.110602,0.0366654 -0.138701,0.00688189 0,0 -0.0771732,0.0395709 -0.116598,-0.0147677 0,0 -0.0497598,0.02 -0.0773346,-0.00166929 0,0 -0.0479646,0.0302756 -0.0998937,0.00944094 0,0 -0.0252638,0.0107874 -0.0839488,0.00884646 0,0 -0.046252,0.000775591 -0.0734567,-0.0237087 0,0 -0.046252,0.0101024 -0.0769567,-0.00116929 0,0 -0.0450827,0.0314843 -0.118543,0.0108858 0,0 -0.0715118,0.0609803 -0.144579,0.00423228 0,0 -0.0385787,0.00770079 -0.0646299,0.000102362 0,0 -0.0387559,0.0432205 -0.125039,0.0206811 0,0 -0.0324409,0.0181024 -0.0621457,0.0111063l-3.93701e-005 0.0412205 2.2323 0 0 -0.0627953z"/></svg>';
				$t = !0;
			}
			else if ("multi_triangle_seperator" == $o)
			{
				$seperator_class = "ult-multi-trianle";
				$f = preg_replace_callback( '/^#?([a-f\\d])([a-f\\d])([a-f\\d])$/i', function($m) { return $m[ 1 ] . $m[ 1 ] . $m[ 2 ] . $m[ 2 ] . $m[ 3 ] . $m[ 3 ]; }, $i );
				if(preg_match( '/^#?([a-f\\d]{2})([a-f\\d]{2})([a-f\\d]{2})$/i', $f, $match ))
					$f = array( 'r' => hex2bin( $match[ 1 ] ), 'g' => hex2bin( $match[ 2 ] ), 'b' => hex2bin( $match[ 3 ] ) );
				else
					$f = null;
				$svg = '<svg class="uvc-multi-triangle-svg" xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 100 100" preserveAspectRatio="none" width="100%" height="' . $p . '">\t\t\t\t            <path class="large left" d="M0 0 L50 50 L0 100" fill="rgba(' . $f['r'] . "," . $f['g'] . "," . $f['b'] . ', .1)"></path>\t\t\t\t            <path class="large right" d="M100 0 L50 50 L100 100" fill="rgba(' . $f['r'] . "," . $f['g'] . "," . $f['b'] . ', .1)"></path>\t\t\t\t            <path class="medium left" d="M0 100 L50 50 L0 33.3" fill="rgba(' . $f['r'] . "," . $f['g'] . "," . $f['b'] . ', .3)"></path>\t\t\t\t            <path class="medium right" d="M100 100 L50 50 L100 33.3" fill="rgba(' . $f['r'] . "," . $f['g'] . "," . $f['b'] . ', .3)"></path>\t\t\t\t            <path class="small left" d="M0 100 L50 50 L0 66.6" fill="rgba(' . $f['r'] . "," . $f['g'] . "," . $f['b'] . ', .5)"></path>\t\t\t\t            <path class="small right" d="M100 100 L50 50 L100 66.6" fill="rgba(' . $f['r'] . "," . $f['g'] . "," . $f['b'] . ', .5)"></path>\t\t\t\t            <path d="M0 99.9 L50 49.9 L100 99.9 L0 99.9" fill="rgba(' . $f['r'] . "," . $f['g'] . "," . $f['b'] . ', 1)"></path>\t\t\t\t            <path d="M48 52 L50 49 L52 52 L48 52" fill="rgba(' . $f['r'] . "," . $f['g'] . "," . $f['b'] . ', 1)"></path>\t\t\t\t        </svg>';
				$t = !0;
			}
			else if ("round_split_seperator" == $o)
			{

			} else
				$seperator_class = "ult-no-shape-seperator";

			if(null !== $n && "" != $n && 0 != $n)
				$e = (int)$n;
			$shape_css = 'content: "";width:' . $s . "px; height:" . $s . "px; bottom: -" . ($m + $e) . "px;";
			if("" != $i)
				$shape_css .= "background-color:" . $i . ";";
			if("none" != $l && "ult-rounded-split-seperator-wrapper" != $seperator_class && 0 == $t)
			{
				$seperator_border_line_css = $n . "px " . $l . " " . $d;
				$shape_css .= "border-bottom:" . $seperator_border_line_css . "; border-right:" . $seperator_border_line_css . ";";
				$seperator_css .= "border-bottom:" . $seperator_border_line_css . ";";
				$h = "bottom:" . $n . "px !important";
			}

			if("ult-no-shape-seperator" != $seperator_class && "ult-rounded-split-seperator-wrapper" != $seperator_class && 0 == $t)
				$_ = "." . $b . " .ult-main-seperator-inner:after { " . $shape_css . " }";
			else
				$_ = '';

			if(1 == $t)
				$inner_html = $svg;

			if("top_bottom_seperator" == $u)
			{
				$g = '<div class="ult-vc-seperator top_seperator ' . $seperator_class . " " . $b . '" data-full-width="' . $c . '" data-border="' . $l . '" data-border-width="' . $n . '"><div class="ult-main-seperator-inner">' . $inner_html . "</div>" . $v . "</div>";
				$g .= '<div class="ult-vc-seperator bottom_seperator ' . $seperator_class . " " . $b . '" data-full-width="' . $c . '" data-border="' . $l . '" data-border-width="' . $n . '"><div class="ult-main-seperator-inner">' . $inner_html . "</div>" . $v . "</div>";
			}
			else
			{
				$g = '<div class="ult-vc-seperator ' . $u . " " . $seperator_class . " " . $b . '" data-full-width="' . $c . '" data-border="' . $l . '" data-border-width="' . $n . '"><div class="ult-main-seperator-inner">' . $inner_html . "</div>" . $v . "</div>";
			}

			$g = HtmlNd::ParseAndImportAll( $doc, $g );
			foreach( $g as $g1 )
				$itemRow -> insertBefore( $g1, $itemRow -> firstChild );

			$seperator_css = "." . $b . " .ult-main-seperator-inner { " . $seperator_css . " }";
			if("" != $h)
			{
				$h = "." . $b . " .ult-main-seperator-inner { " . $h . " }";
				$seperator_css .= $h;
			}
			if("" != $v)
			{
				$p2 = $p / 2;
				if("none_seperator" == $o || "circle_svg_seperator" == $o || "triangle_svg_seperator" == $o)
					$seperator_css .= "." . $b . " .separator-icon { -webkit-transform: translate(-50%, -50%); -moz-transform: translate(-50%, -50%); -ms-transform: translate(-50%, -50%); -o-transform: translate(-50%, -50%); transform: translate(-50%, -50%); }";
				else
					$seperator_css .= "." . $b . ".top_seperator .separator-icon { -webkit-transform: translate(-50%, calc(-50% . " . $p2 . "px)); -moz-transform: translate(-50%, calc(-50% . " . $p2 . "px)); -ms-transform: translate(-50%, calc(-50% . " . $p2 . "px)); -o-transform: translate(-50%, calc(-50% . " . $p2 . "px)); transform: translate(-50%, calc(-50% . " . $p2 . "px)); } ." . $b . ".bottom_seperator .separator-icon { -webkit-transform: translate(-50%, calc(-50% - " . $p2 . "px)); -moz-transform: translate(-50%, calc(-50% - " . $p2 . "px)); -ms-transform: translate(-50%, calc(-50% - " . $p2 . "px)); -o-transform: translate(-50%, calc(-50% - " . $p2 . "px)); transform: translate(-50%, calc(-50% - " . $p2 . "px)); }";
			}

			if(1 == $t)
			{
				foreach( $g as $g1 )
					foreach( $xpath -> query( './/svg', $g1 ) as $itemSvg )
						$itemSvg -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemSvg -> getAttribute( 'style' ) ), array( 'height' => $p . 'px' ) ) ) );

			}

			if( $ctxProcess[ 'mode' ] == 'full' )
			{
				$itemStyle = $doc -> createElement( 'style' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemStyle -> setAttribute( 'type', 'text/css' );
				HtmlNd::SetValFromContent( $itemStyle, $_ . $seperator_css );
				$itemRow -> parentNode -> insertBefore( $itemStyle, $itemRow );
			}
		}
	}

	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," upb_color ")]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

	}
}

function _ProcessCont_Cp_upbCntVid( $ctx, &$ctxProcess, $doc, $xpath )
{
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," upb_content_video ")][@data-ultimate-video]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$urlVid = $item -> getAttribute( 'data-ultimate-video' );
		if( !$urlVid )
			continue;

		if( !( $itemRow = _Upb_GetNearestRow( $item ) ) )
			continue;

		$themeSupport = HtmlNd::GetAttr( $item, 'data-theme-support' );

		$itemCont = HtmlNd::CreateTag( $doc, 'div', array( 'class' => 'upb_video-wrapper' ) );
		$itemCont -> appendChild( $item );
		$itemRow -> insertBefore( $itemCont, $itemRow -> firstChild );

		HtmlNd::AddRemoveAttrClass( $item, array( 'upb_video-bg' ), array( 'upb_content_video' ) );
		$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( 'background-image' => 'url(' . HtmlNd::GetAttr( $item, 'data-ultimate-video-poster' ) . ')' ) ) ) );
		$item -> appendChild( HtmlNd::CreateTag( $doc, 'video', array( 'class' => array( 'upb_video-src' ), 'muted' => HtmlNd::GetAttr( $item, 'data-ultimate-video-muted' ), 'loop' => HtmlNd::GetAttr( $item, 'data-ultimate-video-loop' ), 'preload' => 'auto', 'autoplay' => HtmlNd::GetAttr( $item, 'data-ultimate-video-autoplay' ) ), array( HtmlNd::CreateTag( $doc, 'source', array( 'type' => 'video/mp4', 'src' => HtmlNd::GetAttr( $item, 'data-ultimate-video' ) ) ) ) ) );
		if( $item -> getAttribute( 'data-overlay' ) == 'true' )
		{
		    $item -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => 'upb_bg_overlay', 'style' => array( 'background-color' => HtmlNd::GetAttr( $item, 'data-overlay-color' ) ) ) ) );
			if( $overlayPattern = $item -> getAttribute( 'data-overlay-pattern' ) )
			{
				$item -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => 'upb_bg_overlay_pattern', 'style' => array( 'background-image' => 'url(' . $overlayPattern . ')', 'opacity' => HtmlNd::GetAttr( $item, 'data-overlay-pattern-opacity' ), 'background-attachment' => HtmlNd::GetAttr( $item, 'data-overlay-pattern-attachment' ) ) ) ) );
			}
		}

		HtmlNd::AddRemoveAttrClass( $itemRow, Ui::ParseClassAttr( $item -> getAttribute( 'data-hide-row' ) ) );
		$itemRow -> setAttribute( 'data-rtl', $item -> getAttribute( 'data-rtl' ) );
		$itemRow -> setAttribute( 'data-row-effect-mobile-disable', $item -> getAttribute( 'data-row-effect-mobile-disable' ) );
		$itemRow -> setAttribute( 'data-img-parallax-mobile-disable', $item -> getAttribute( 'data-img-parallax-mobile-disable' ) );

		if( $themeSupport !== null && $themeSupport !== 'enable' )
			$itemContainer = null;
		else
			$itemContainer = $itemCont -> parentNode;
		HtmlNd::AddRemoveAttrClass( $itemContainer, array( 'vc_row-has-fill' ) );
	}
}

function _Upb_GetNearestRow( $item )
{
	for( $itemRow = $item; $itemRow = HtmlNd::GetPreviousElementSibling( $itemRow );  )
		if( in_array( 'wpb_row', HtmlNd::GetAttrClass( $itemRow ) ) )
			break;
	return( $itemRow );
}

function _ProcessCont_Cp_the7_AddGlob( $ctx, &$ctxProcess, $doc, $xpath )
{
	if( isset( $ctx -> the7Glob ) )
		return;

	$itemCmnScript = $doc -> createElement( 'script' );
	if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
		$itemCmnScript -> setAttribute( 'type', 'text/javascript' );
	$itemCmnScript -> setAttribute( 'seraph-accel-crit', '1' );
	HtmlNd::SetValFromContent( $itemCmnScript, "var dtGlobalsLzl={};\n(function(b,a){a.isMobile=/(Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|windows phone)/.test(navigator.userAgent);a.isAndroid=/(Android)/.test(navigator.userAgent);a.isiOS=/(iPhone|iPod|iPad)/.test(navigator.userAgent);a.isiPhone=/(iPhone|iPod)/.test(navigator.userAgent);a.isiPad=/(iPad)/.test(navigator.userAgent);a.isWindowsPhone=navigator.userAgent.match(/IEMobile/i);var c=b.documentElement.classList;c.add(\"mobile-\"+a.isMobile);c.add(a.isiOS?\"is-iOS\":\"not-iOS\");b=b.body.classList;\n-1!=navigator.userAgent.indexOf(\"Safari\")&&-1==navigator.userAgent.indexOf(\"Chrome\")&&b.add(\"is-safari\");a.isWindowsPhone&&(b.add(\"ie-mobile\"),b.add(\"windows-phone\"));a.isMobile||b.add(\"no-mobile\");a.isiPhone&&(b.add(\"is-iphone\"),b.add(\"windows-phone\"))})(document,dtGlobalsLzl)" );
	$ctxProcess[ 'ndBody' ] -> insertBefore( $itemCmnScript, $ctxProcess[ 'ndBody' ] -> firstChild );

	$ctx -> the7Glob = true;
}

function _ProcessCont_Cp_the7MblHdr( $ctx, &$ctxProcess, $doc, $xpath )
{
	$adjusted = false;
	$settTheme = null;
	foreach( $xpath -> query( './/body[contains(concat(" ",normalize-space(@class))," the7-ver-")][not(self::node()[contains(concat(" ",normalize-space(@class)," ")," responsive-off ")])]//*[contains(concat(" ",normalize-space(@class)," ")," masthead ")]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		if( $settTheme === null )
		{
			$settTheme = array();
			if( $itemScrCfg = HtmlNd::FirstOfChildren( $xpath -> query( './/script[contains(text(),"dtLocal")][contains(text(),"dtShare")]' ) ) )
			{
				$posBegin = array();
				if( preg_match( '@\\svar\\s+dtLocal\\s+=\\s+{@', $itemScrCfg -> nodeValue, $posBegin, PREG_OFFSET_CAPTURE ) )
				{
					$posBegin = $posBegin[ 0 ][ 1 ] + strlen( $posBegin[ 0 ][ 0 ] ) - 1;
					$posEnd = Gen::JsonGetEndPos( $posBegin, $itemScrCfg -> nodeValue );
					if( $posEnd !== null )
						$settTheme[ 'dtLocal' ] = @json_decode( Gen::JsObjDecl2Json( substr( $itemScrCfg -> nodeValue, $posBegin, $posEnd - $posBegin ) ), true );
				}
			}
		}

		if( !$settTheme )
			continue;

		$desktopHeaderHeight = Gen::GetArrField( $settTheme, array( 'dtLocal', 'themeSettings', 'desktopHeader', 'height' ) );
		if( $desktopHeaderHeight && ( $itemStdHdr = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," header-bar ")]', $item ) ) ) )
			$itemStdHdr -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemStdHdr -> getAttribute( 'style' ) ), array( 'height' => ( string )$desktopHeaderHeight . 'px' ) ) ) );

		HtmlNd::AddRemoveAttrClass( $item, array( 'sticky-off' ) );

		$contMiniWidgets = '';
		{
			$a = array();
			foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," near-logo-first-switch ")]', $item ) as $itemCloneFrom )
			{
				$itemCloneFrom = $itemCloneFrom -> cloneNode( true );
				HtmlNd::AddRemoveAttrClass( $itemCloneFrom, array( 'show-on-first-switch', 'js-lzl' ), array( 'near-logo-first-switch', 'near-logo-second-switch' ) );
				$a[] = $itemCloneFrom;
			}

			foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," near-logo-second-switch ")]', $item ) as $itemCloneFrom )
			{
				$itemCloneFrom = $itemCloneFrom -> cloneNode( true );
				HtmlNd::AddRemoveAttrClass( $itemCloneFrom, array( 'show-on-second-switch', 'js-lzl' ), array( 'near-logo-first-switch', 'near-logo-second-switch' ) );
				$a[] = $itemCloneFrom;
			}

			foreach( $a as $itemCloneFrom )
				$contMiniWidgets .= HtmlNd::DeParse( $itemCloneFrom );
			unset( $a );
		}

		$contImgLogo = '';
		{
			if( !( $itemMixedHdr = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," mixed-header ")]' ) ) ) )
				$itemMixedHdr = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," masthead ")][not(self::node()[contains(concat(" ",normalize-space(@class)," ")," mixed-header ")])]' ) );
			if( $itemMixedHdr )
				foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," branding ")]/a|.//*[contains(concat(" ",normalize-space(@class)," ")," branding ")]/img', $itemMixedHdr ) as $itemMixedHdrSub )
				{
					$itemMixedHdrSub = $itemMixedHdrSub -> cloneNode( true );
					HtmlNd::AddRemoveAttrClass( $itemMixedHdrSub, array( 'js-lzl' ) );
					$contImgLogo .= HtmlNd::DeParse( $itemMixedHdrSub );
				}
		}

		$contMobileToggleCaption = Gen::GetArrField( $settTheme, array( 'dtLocal', 'themeSettings', 'mobileHeader', 'mobileToggleCaptionEnabled' ) ) != 'disabled' ? ( '<span class="menu-toggle-caption">' . Gen::GetArrField( $settTheme, array( 'dtLocal', 'themeSettings', 'mobileHeader', 'mobileToggleCaption' ) ) . '</span>' ) : '';

		if( !( $itemMblBar = HtmlNd::ParseAndImport( $doc, '<div class="mobile-header-bar js-lzl"><div class="mobile-navigation"><a href="#" class="dt-mobile-menu-icon js-lzl" aria-label="Mobile menu icon">' . $contMobileToggleCaption . '<div class="lines-button "><span class="menu-line"></span><span class="menu-line"></span><span class="menu-line"></span></div></a></div><div class="mobile-mini-widgets">' . $contMiniWidgets . '</div><div class="mobile-branding">' . $contImgLogo . '</div></div>' ) ) )
			continue;

		$item -> appendChild( $itemMblBar );

		$aLeft = array();
		$aRight = array();

		if( $itemLeftWidget = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," left-widgets ")]', $item ) ) )
		{
			foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," in-top-bar ")]', $item ) as $itemCloneFrom )
			{
				$itemCloneFrom = $itemCloneFrom -> cloneNode( true );
				HtmlNd::AddRemoveAttrClass( $itemCloneFrom, array( 'hide-on-desktop', 'hide-on-first-switch', 'show-on-second-switch', 'js-lzl-ing' ) );
				$aLeft[] = $itemCloneFrom;
			}

			foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," in-top-bar-left ")]', $item ) as $itemCloneFrom )
			{
				$itemCloneFrom = $itemCloneFrom -> cloneNode( true );
				HtmlNd::AddRemoveAttrClass( $itemCloneFrom, array( 'hide-on-desktop', 'show-on-first-switch', 'js-lzl-ing' ) );
				$aLeft[] = $itemCloneFrom;
			}
		}

		if( $itemRightWidget = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," right-widgets ")]', $item ) ) )
		{
			foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," in-top-bar-right ")]', $item ) as $itemCloneFrom )
			{
				$itemCloneFrom = $itemCloneFrom -> cloneNode( true );
				HtmlNd::AddRemoveAttrClass( $itemCloneFrom, array( 'hide-on-desktop', 'show-on-first-switch', 'js-lzl-ing' ), array( 'select-type-menu', 'list-type-menu', 'select-type-menu-second-switch', 'list-type-menu-second-switch' ) );
				$aRight[] = $itemCloneFrom;
			}

		}

		foreach( $aLeft as $itemCloneFrom )
			$itemLeftWidget -> appendChild( $itemCloneFrom );
		foreach( $aRight as $itemCloneFrom )
			$itemRightWidget -> appendChild( $itemCloneFrom );
		unset( $aLeft, $aRight );

		{
			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, 'seraph_accel_cp_the7MblHdr_calcSizes(document.currentScript.parentNode);' );
			$item -> appendChild( $itemScript );
		}

		$adjusted = true;
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.top-bar-empty@' ] = true;

		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, '.masthead .mobile-header-bar:not(.js-lzl),
.masthead .mobile-header-bar.js-lzl > * > *:not(.js-lzl),
.masthead.fixed-masthead .js-lzl-ing,
.dt-mobile-header .js-lzl-ing,
.dt-mobile-header .js-lzl,
.masthead.masthead-mobile .mini-widgets > .js-lzl-ing,
body:not(.seraph-accel-js-lzl-ing) .masthead .mini-widgets > .js-lzl-ing {
	display: none !important;
}

/*@media screen and (max-width: ' . Gen::GetArrField( $settTheme, array( 'dtLocal', 'themeSettings', 'mobileHeader', 'secondSwitchPoint' ), 0 ) . 'px) {
	.masthead .mobile-header-bar .mobile-branding .js-lzl {
		display: inline-block;
	}
}*/' );

			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}

		{

			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, "function seraph_accel_cp_the7MblHdr_calcSizes(b){function c(a){if(!a)return!1;var e;for(a=a.firstElementChild;a;a=a.nextElementSibling){a.classList.remove(\"first\");a.classList.remove(\"last\");var g=a.offsetParent?\"visible\"!=getComputedStyle(a).visibility?!1:!0:!1;if(g){e||(e=a);var h=a}}if(!e)return!1;e.classList.add(\"first\");h.classList.add(\"last\");return!0}var d=b.querySelector(\".top-bar\");if(d){d.classList.remove(\"top-bar-empty\");var f=!1;d.querySelectorAll(\".mini-widgets\").forEach(function(a){c(a)&&\n(f=!0)});f||d.classList.add(\"top-bar-empty\")}b.querySelectorAll(\".header-bar .mini-widgets\").forEach(c);b.querySelectorAll(\".mobile-mini-widgets\").forEach(c)}(function(b){function c(d){b.querySelectorAll(\".masthead\").forEach(seraph_accel_cp_the7MblHdr_calcSizes)}b.addEventListener(\"seraph_accel_calcSizes\",c,{capture:!0,passive:!0});seraph_accel_izrbpb.add(function(){b.removeEventListener(\"seraph_accel_calcSizes\",c,{capture:!0,passive:!0})})})(document)" );
			$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
		}
	}
}

function _ProcessCont_Cp_the7Ani( $ctx, &$ctxProcess, $doc, $xpath )
{
	if( $ctxProcess[ 'mode' ] != 'full' )
		return;

	if( HtmlNd::FirstOfChildren( $xpath -> query( './/body[contains(concat(" ",normalize-space(@class)," ")," the7-ver-")]//*[contains(concat(" ",normalize-space(@class)," ")," animate-element ")]' ) ) )
	{
		_ProcessCont_Cp_the7_AddGlob( $ctx, $ctxProcess, $doc, $xpath );

		$ctxProcess[ 'aCssCrit' ][ '@\\.mobile-false@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.mobile-true@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.start-animation@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.animation-triggered@' ] = true;

		$ctx -> aAniAppear[ '.skills:not(.js-lzl-start-ani)' ] = "function(a){dtGlobalsLzl.isMobile&&(a.classList.add(\"js-lzl-start-ani\"),seraph_accel_cp_the7Ani_skills(a))}";

		$ctx -> aAniAppear[ '.animation-at-the-same-time:not(.js-lzl-start-ani)' ] = "function(a){dtGlobalsLzl.isMobile||(a.classList.add(\"js-lzl-start-ani\"),a.querySelectorAll(\".animate-element:not(.start-animation)\").forEach(function(b){b.classList.add(\"start-animation\");b.classList.add(\"animation-triggered\")}))}";

		$ctx -> aAniAppear[ '.animate-element:not(.start-animation)' ] = "function(b){if(!dtGlobalsLzl.isMobile){var a=b.classList;a.add(\"start-animation\");a.add(\"animation-triggered\");a.contains(\"skills\")&&seraph_accel_cp_the7Ani_skills(b);return 200}}";

		$itemCmnScript = $doc -> createElement( 'script' );
		if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
			$itemCmnScript -> setAttribute( 'type', 'text/javascript' );
		$itemCmnScript -> setAttribute( 'seraph-accel-crit', '1' );
		HtmlNd::SetValFromContent( $itemCmnScript, "function seraph_accel_cp_the7Ani_skills(b){b.querySelectorAll(\".skill-value\").forEach(function(a){a.style.setProperty(\"width\",a.getAttribute(\"data-width\")+\"%\")})}" );
		$ctxProcess[ 'ndBody' ] -> appendChild( $itemCmnScript );
	}
}

function _ProcessCont_Cp_jqVide( $ctx, &$ctxProcess, $doc, $xpath )
{
	foreach( $xpath -> query( './/*[@data-vide-bg]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$bg = Gen::ParseProps( $item -> getAttribute( 'data-vide-bg' ), ',', ':' );
		$options = array_merge( array( 'volume' => '1', 'playbackRate' => '1', 'muted' => 'true', 'loop' => 'true', 'autoplay' => 'true', 'posterType' => 'detect', 'position' => '50% 50%', 'resizing' => 'true', 'bgColor' => 'transparent' ), Gen::ParseProps( $item -> getAttribute( 'data-vide-options' ), ',', ':' ) );

		$item -> removeAttribute( 'data-vide-bg' );
		$item -> removeAttribute( 'data-vide-options' );

		$aStyle = array(
			'position'				=> 'absolute',
			'z-index'				=> -1,
			'left'					=> 0,
			'right'					=> 0,
			'top'					=> 0,
			'bottom'				=> 0,
			'overflow'				=> 'hidden',
			'background-size'		=> 'cover',
			'background-position'	=> $options[ 'position' ],
			'background-color'		=> $options[ 'bgColor' ],
			'background-repeat'		=> 'no-repeat',
		);

		$urlPoster = null;
		if( $options[ 'posterType' ] == 'detect' )
		{
			foreach( array( 'gif', 'jpg', 'jpeg', 'png' ) as $posterProbe )
			{
				$posterProbe = (isset($bg[ 'poster' ])?$bg[ 'poster' ]:'') . '.' . $posterProbe;
				$imgSrc = new ImgSrc( $posterProbe );
				if( $imgSrc -> GetCont() === false )
					continue;

				unset( $imgSrc );
				$urlPoster = $posterProbe;
				break;
			}
		}
		else if( $options[ 'posterType' ] != 'none' )
			$urlPoster = (isset($bg[ 'poster' ])?$bg[ 'poster' ]:'') . '.' . $options[ 'posterType' ];

		$aStyle[ 'background-image' ] = ( $urlPoster !== null ) ? ( 'url("' . (isset($bg[ 'poster' ])?$bg[ 'poster' ]:'') . '.' . $options[ 'posterType' ] . '")' ) : 'none';

		$aAttrVid = array(
			'autoplay'				=> $options[ 'autoplay' ],
			'loop'					=> $options[ 'loop' ],
			'volume'				=> $options[ 'volume' ],
			'muted'					=> $options[ 'muted' ],
			'defaultMuted'			=> $options[ 'muted' ],
			'playbackRate'			=> $options[ 'playbackRate' ],
			'defaultPlaybackRate'	=> $options[ 'playbackRate' ],

			'style' => array(
				'position'			=> 'absolute',
				'z-index'			=> -1,
				'object-fit'		=> 'cover',
				'object-position'	=> $options[ 'position' ],
				'width'				=> '100%',
				'height'			=> '100%',
			),
		);

		$aVidChild = array();
		foreach( array( 'mp4', 'webm', 'ogv' ) as $vidType )
			if( (isset($bg[ $vidType ])?$bg[ $vidType ]:null) )
				$aVidChild[] = HtmlNd::CreateTag( $doc, 'source', array( 'src' => $bg[ $vidType ] . '.' . $vidType, 'type' => 'video/' . $vidType ) );

		$item -> insertBefore( HtmlNd::CreateTag( $doc, 'div', array( 'class' => (isset($options[ 'className' ])?$options[ 'className' ]:null), 'style' => $aStyle ), array( HtmlNd::CreateTag( $doc, 'video', $aAttrVid, $aVidChild ) ) ), $item -> firstChild );
	}
}

function _ProcessCont_Cp_jqSldNivo( $ctx, &$ctxProcess, $doc, $xpath )
{
	$bScrFound = null;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," nivoSlider ")]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		if( !$item -> parentNode || !$item -> parentNode -> parentNode )
			continue;

		if( $bScrFound === null )
			$bScrFound = false;

		$prms = array();
		for( $itemProbe = null; $itemProbe = HtmlNd::GetNextTreeChild( $item -> parentNode -> parentNode, $itemProbe ); )
		{
			if( $itemProbe -> nodeType != XML_ELEMENT_NODE || $itemProbe -> nodeName != 'script' || HtmlNd::DoesContain( $item, $itemProbe ) )
				continue;

			$m = array();
			if( !@preg_match( '@(jQuery\\([\'"]#' . $item -> getAttribute( 'id' ) . '[\'"]\\))\\.nivoSlider\\(\\s*@', $itemProbe -> nodeValue, $m, PREG_OFFSET_CAPTURE ) )
				continue;

			{
				$posStart = $m[ 0 ][ 1 ] + strlen( $m[ 0 ][ 0 ] );
				$posEnd = Gen::JsonGetEndPos( $posStart, $itemProbe -> nodeValue );

				if( $posEnd !== null )
					$prms = @json_decode( Gen::JsObjDecl2Json( substr( $itemProbe -> nodeValue, $posStart, $posEnd - $posStart ) ), true );
				if( !$prms )
					$prms = array();
			}

			$itemProbe -> nodeValue = substr_replace( $itemProbe -> nodeValue, 'var c=' . $m[ 1 ][ 0 ] . ';c.parent().find( ".js-lzl-ing" ).remove();c', $m[ 1 ][ 1 ], strlen( $m[ 1 ][ 0 ] ) );
			$bScrFound = true;
			break;
		}

		{
			$aNav = array();
			for( $itemSlide = HtmlNd::GetFirstElement( $item ), $i = 0; $itemSlide; $itemSlide = HtmlNd::GetNextElementSibling( $itemSlide ), $i++ )
				$aNav[] = HtmlNd::CreateTag( $doc, 'a', array( 'class' => array( 'nivo-control', Gen::GetArrField( $prms, array( 'startSlide' ), 0 ) == $i ? 'active' : null ) ), array( $doc -> createTextNode( ( string )( $i + 1 ) ) ) );
			$item -> parentNode -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => array( 'nivo-controlNav', 'js-lzl-ing' ), 'style' => array() ), $aNav ) );
		}

		$sldImgSrc = null;
		$sldCaption = null;
		if( $itemFirtSlide = HtmlNd::FirstOfChildren( $xpath -> query( '(.//img)[' . ( string )( Gen::GetArrField( $prms, array( 'startSlide' ), 0 ) + 1 ) . ']', $item ) ) )
		{
			$sldImgSrc = $itemFirtSlide -> getAttribute( 'src' );
			$sldCaption = ( string )$itemFirtSlide -> getAttribute( 'title' );
			if( Gen::StrStartsWith( $sldCaption, '#' ) )
			{
				if( $itemFirtSlideCaption = HtmlNd::FirstOfChildren( $xpath -> query( './/*[@id="' . substr( $sldCaption, 1 ) . '"]' ) ) )
				{
					$sldCaption = array();
					for( $itemFirtSlideCaptionChild = HtmlNd::GetFirstElement( $itemFirtSlideCaption ); $itemFirtSlideCaptionChild; $itemFirtSlideCaptionChild = HtmlNd::GetNextElementSibling( $itemFirtSlideCaptionChild ) )
						$sldCaption[] = $itemFirtSlideCaptionChild -> cloneNode( true );
				}
				else
					$sldCaption = null;
			}
			else
				$sldCaption = HtmlNd::ParseAndImportAll( $doc, ( string )$sldCaption );
		}

		if( $sldImgSrc )
		{
			{
				$itemNoScript = $doc -> createElement( 'noscript' );
				$itemNoScript -> setAttribute( 'data-lzl-bjs', '' );
				HtmlNd::MoveChildren( $itemNoScript, $item );
				$item -> appendChild( $itemNoScript );
				ContNoScriptItemClear( $itemNoScript );

				$ctx -> bBjs = true;
			}

			$item -> appendChild( HtmlNd::CreateTag( $doc, 'img', array( 'class' => array( 'nivo-main-image', 'js-lzl-ing' ), 'style' => array( 'display' => 'inline' ), 'src' => $sldImgSrc ) ) );
			$item -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => array( 'nivo-caption', 'js-lzl-ing' ), 'style' => array( 'display' => 'block' ) ), $sldCaption ) );
		}

		$item -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => array( 'nivo-directionNav', 'js-lzl-ing' ), 'style' => array() ), array( HtmlNd::CreateTag( $doc, 'a', array( 'class' => array( 'nivo-prevNav' ) ), array( $doc -> createTextNode( Gen::GetArrField( $prms, array( 'prevText' ), '' ) ) ) ), HtmlNd::CreateTag( $doc, 'a', array( 'class' => array( 'nivo-nextNav' ) ), array( $doc -> createTextNode( Gen::GetArrField( $prms, array( 'nextText' ), '' ) ) ) ) ) ) );
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $bScrFound === false )
	{
		{

			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, "(function(e,a){var b=a.seraph_accel_js_lzl_initScrCustom;a.seraph_accel_js_lzl_initScrCustom=function(){b&&b();a.jQuery&&a.jQuery.fn.nivoSlider&&!a.jQuery.fn.seraph_accel_nivoSlider&&(a.jQuery.fn.seraph_accel_nivoSlider=a.jQuery.fn.nivoSlider,a.jQuery.fn.nivoSlider=function(c){this.each(function(){this.parentNode.querySelectorAll(\".js-lzl-ing\").forEach(function(d){d.remove()})});return a.jQuery.fn.seraph_accel_nivoSlider.call(this,c)},a.jQuery.fn.nivoSlider.defaults=a.jQuery.fn.seraph_accel_nivoSlider.defaults)}})(document,\nwindow)" );
			$ctxProcess[ 'ndBody' ] -> appendChild( $itemScript );
		}
	}
}

function _ProcessCont_Cp_wprAniTxt( $ctx, &$ctxProcess, $doc, $xpath )
{

	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," wpr-anim-text ")]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$aClass = HtmlNd::GetAttrClass( $item );
		if( in_array( 'wpr-anim-text-type-typing', $aClass ) )
		{
		}
		else if( in_array( 'wpr-anim-text-letters', $aClass ) )
		{
		}
		else if( in_array( 'wpr-anim-text-type-clip', $aClass ) )
		{
		}
		else
		{
			if( $itemFirstChild = HtmlNd::FirstOfChildren( $xpath -> query( './/b', $item ) ) )
				HtmlNd::AddRemoveAttrClass( $itemFirstChild, array( 'wpr-anim-text-visible' ) );
		}
	}
}

function _ProcessCont_Cp_wprTabs( $ctx, &$ctxProcess, $doc, $xpath )
{
	$adjusted = false;

	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," wpr-tabs ")]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$dataSett = @json_decode( $item -> getAttribute( 'data-options' ), true );
		$idActiveTab = Gen::GetArrField( $dataSett, array( 'activeTab' ), 1 );

		if( $itemFirstTabTitle = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," wpr-tabs-wrap ")]//*[contains(concat(" ",normalize-space(@class)," ")," wpr-tab ")][@data-tab="' . $idActiveTab . '"]', $item ) ) )
		{
			HtmlNd::AddRemoveAttrClass( $itemFirstTabTitle, array( 'wpr-tab-active' ) );
		}

		if( $itemFirstTabBody = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," wpr-tabs-content-wrap ")]//*[contains(concat(" ",normalize-space(@class)," ")," wpr-tab-content ")][@data-tab="' . $idActiveTab . '"]', $item ) ) )
		{
			HtmlNd::AddRemoveAttrClass( $itemFirstTabBody, array( 'wpr-tab-content-active', 'wpr-animation-enter' ) );
		}

		$adjusted = true;
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.wpr-tab-active@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.wpr-tab-content-active@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.wpr-animation-enter@' ] = true;
	}
}

function _ProcessCont_Cp_elmntrAni( $ctx, &$ctxProcess, $doc, $xpath )
{
	$adjusted = false;
	$cmnStyles = '';
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-element ")][not(self::node()[@data-lzl-trx])][contains(@data-settings,"animation")]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$dataSett = ( array )@json_decode( $item -> getAttribute( 'data-settings' ), true );

		foreach( array( '', '_' ) as $attrPrefix )
		{
			if( $ctx -> cfgElmntrFrontend === null )
				$ctx -> cfgElmntrFrontend = _Elmntr_GetFrontendCfg( $xpath );

			foreach( array_merge( array( '' => null ), Gen::GetArrField( $ctx -> cfgElmntrFrontend, array( 'views' ), array() ) ) as $viewId => $view )
			{
				$attrSrch = array( 'an' => $attrPrefix . 'animation' . ( $viewId ? '_' . $viewId : '' ), 'ad' => $attrPrefix . 'animation_delay' . ( $viewId ? '_' . $viewId : '' ) );

				$sAniName = Gen::GetArrField( $dataSett, array( $attrSrch[ 'an' ] ), '' );
				if( !$sAniName )
					continue;

				if( $viewId )
				{
					$dataId = $item -> getAttribute( 'data-id' );
					if( !$dataId )
						continue;

					$cmnStyles .= '@media ' . ( $view[ 'cxMin' ] != 0 ? ( '(min-width: ' . $view[ 'cxMin' ] . 'px)' ) : '' ) . ( $view[ 'cxMin' ] != 0 && $view[ 'cxMax' ] != 2147483647 ? ' and ' : '' ) . ( $view[ 'cxMax' ] != 2147483647 ? ( '(max-width: ' . $view[ 'cxMax' ] . 'px)' ) : '' ) . ' {' . "\n";
					$cmnStyles .= '.elementor-element-' . $dataId . ' {';
					$cmnStyles .= '--lzl-an: ' . $sAniName . ';';
					if( isset( $dataSett[ $attrSrch[ 'ad' ] ] ) )
						$cmnStyles .= '--lzl-ad: ' . ( string )Gen::GetArrField( $dataSett, array( $attrSrch[ 'ad' ] ) ) . ';';
					$cmnStyles .= '}' . "\n";
					$cmnStyles .= '}' . "\n";

					$item -> setAttribute( 'data-lzl-an', '' );
				}
				else
				{
					$item -> setAttribute( 'data-lzl-an', $sAniName );
					if( isset( $dataSett[ $attrSrch[ 'ad' ] ] ) )
						$item -> setAttribute( 'data-lzl-ad', ( string )Gen::GetArrField( $dataSett, array( $attrSrch[ 'ad' ] ) ) );
				}

				$ctxProcess[ 'aCssCrit' ][ '@\\.' . $sAniName . '@' ] = true;

				$adjusted = true;

				if( !$viewId )
					break;
			}
		}
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.animated@' ] = true;

		$ctx -> aAniAppear[ '.elementor-element[data-lzl-an]:not(.animated)' ] = 'function(a){function d(){a.classList.add(c);a.style.setProperty("animation-name",getComputedStyle(a).getPropertyValue("animation-name"));a.classList.remove("elementor-invisible")}var c=a.getAttribute("data-lzl-an"),b=a.getAttribute("data-lzl-ad");c||(b=getComputedStyle(a),c=b.getPropertyValue("--lzl-an"),b=b.getPropertyValue("--lzl-ad"));c&&(a.classList.add("animated"),b?setTimeout(d,parseInt(b,10)):d())}';

		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, $cmnStyles );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}
	}
}

function _ProcessCont_Cp_elmntrTrxAni( $ctx, &$ctxProcess, $doc, $xpath )
{
	$adjusted = false;
	$bTrxScr = null;
	foreach( array( array( 'animation', 'animation_delay' ), array( '_animation', '_animation_delay' ) ) as $attrSrch )
	{
		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-element ")][contains(concat(" ",normalize-space(@class)," ")," elementor-widget-trx_")][contains(@data-settings,\'"' . $attrSrch[ 0 ] . '":\')]' ) as $itemContainer )
		{
			if( $bTrxScr === null )
				$bTrxScr = !!HtmlNd::FirstOfChildren( $xpath -> query( './/script[@id="trx_addons-js"][contains(@src,"trx_addons/js/__scripts.js")]' ) );
			if( !$bTrxScr )
				continue;

			if( !ContentProcess_IsItemInFragments( $ctxProcess, $itemContainer ) )
				continue;

			$widgetClass = ( string )$itemContainer -> getAttribute( 'data-widget_type' );
			if( !Gen::StrStartsWith( $widgetClass, 'trx_' ) )
				continue;

			$widgetClass = substr( $widgetClass, 4 );
			$widgetClass = explode( '.', $widgetClass )[ 0 ] . '_item';

			$dataSett = ( array )@json_decode( $itemContainer -> getAttribute( 'data-settings' ), true );

			$sAniName = Gen::GetArrField( $dataSett, array( $attrSrch[ 0 ] ), '' );

			$ctxProcess[ 'aCssCrit' ][ '@\\.' . $sAniName . '@' ] = true;

			$itemContainer -> setAttribute( 'data-lzl-trx', '1' );
			$aItem = HtmlNd::ChildrenAsArr( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," ' . $widgetClass . ' ")]', $itemContainer ) );
			if( !$aItem )
				$aItem[] = $itemContainer;
			foreach( $aItem as $item )
			{
				$item -> setAttribute( 'data-lzl-trxan', $sAniName );
				if( isset( $dataSett[ $attrSrch[ 1 ] ] ) )
					$item -> setAttribute( 'data-lzl-trxad', ( string )Gen::GetArrField( $dataSett, array( $attrSrch[ 1 ] ) ) );
				$adjusted = true;
			}
		}
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.animated@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.animated-item@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.trx_addons_invisible@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.elementor-invisible@' ] = true;

		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, '.trx_addons_invisible.animated {
	visibility: visible;
	opacity: 1;
}' );

			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}

		$ctx -> aAniAppear[ '[data-lzl-trxan]:not(.animated)' ] = 'function(a){function c(){a.classList.add(a.getAttribute("data-lzl-trxan"));a.classList.add("animated");a.classList.add("animated-item");a.style.setProperty("animation-name",getComputedStyle(a).getPropertyValue("animation-name"));for(var b=a;b;b=b.parentNode)if(b.getAttribute&&"1"==b.getAttribute("data-lzl-trx")){b.classList.remove("elementor-invisible");break}}var d=a.getAttribute("data-lzl-trxad");d?setTimeout(c,parseInt(d,10)):c()}';
	}
}

function _ProcessCont_Cp_elmntrStck( $ctx, &$ctxProcess, $doc, $xpath )
{
	$adjusted = false;
	foreach( HtmlNd::ChildrenAsArr( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-element ")][contains(@data-settings,"sticky")]' ) ) as $item )
	{
		$dataSett = @json_decode( $item -> getAttribute( 'data-settings' ), true );
		if( !Gen::GetArrField( $dataSett, array( 'sticky' ), '' ) && !Gen::GetArrField( $dataSett, array( 'ekit_sticky' ), '' ) )
			continue;

		if( $ctx -> cfgElmntrFrontend === null )
			$ctx -> cfgElmntrFrontend = _Elmntr_GetFrontendCfg( $xpath );

		$aStickyOn = array();
		foreach( array_merge( Gen::GetArrField( $dataSett, array( 'sticky_on' ), array() ), explode( '_', Gen::GetArrField( $dataSett, array( 'ekit_sticky_on' ), '' ) ) ) as $stickyOnViewId )
		{
			if( !$stickyOnViewId )
				continue;

			if( $view = Gen::GetArrField( $ctx -> cfgElmntrFrontend, array( 'views', $stickyOnViewId ) ) )
				$aStickyOn[] = array( $view[ 'cxMin' ], $view[ 'cxMax' ] );
		}

		$item -> setAttribute( 'data-lzl-sticky-widths', @json_encode( $aStickyOn ) );
		HtmlNd::AddRemoveAttrClass( $item, array( 'js-lzl-ing' ) );

		$itemStickySpacer = HtmlNd::CreateTag( $doc, $item -> nodeName, array( 'class' => array( 'lzl-sticky-spacer' ), 'style' => array( 'display' => 'none' ) ) );
		HtmlNd::InsertAfter( $item -> parentNode, $itemStickySpacer, $item );

		{
			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, 'seraph_accel_cp_elmntrStck_calcSizes(document.currentScript.parentNode);' );
			$item -> appendChild( $itemScript );
		}

		$adjusted = true;
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.jet-sticky-transition-in@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.jet-sticky-section--stuck@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.ekit-sticky@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.lzl-sticky@' ] = true;

		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, '[data-lzl-sticky-widths].js-lzl-ing.elementor-element.lzl-sticky {
	position: fixed;
	width: 100%;
	margin-top: 0px;
	margin-bottom: 0px;
	top: 0px;
	z-index: 99;
}

[data-lzl-sticky-widths].js-lzl-ing:is(.elementor-sticky__spacer,.the7-e-sticky-spacer) {
	display: none!important;
}

[data-lzl-sticky-widths].js-lzl-ing.elementor-element.lzl-sticky + .lzl-sticky-spacer {
	display: block!important;
	width: 100%;
}' );

			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}

		{

			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, "function seraph_accel_cp_elmntrStck_calcSizes(a){if(a.classList.contains(\"elementor-sticky__spacer\")||a.classList.contains(\"the7-e-sticky-spacer\"))return!1;var c=a.nextElementSibling;c&&c.classList.contains(\"lzl-sticky-spacer\")&&(c.style.setProperty(\"height\",\"\"+a.getBoundingClientRect().height+\"px\"),0<c.getBoundingClientRect().top&&a.classList.remove(\"lzl-sticky\"));try{var b=JSON.parse(a.getAttribute(\"data-settings\"))}catch(f){}b||(b={});if(\"top\"==b.sticky||\"top\"==b.ekit_sticky){try{var d=JSON.parse(a.getAttribute(\"data-lzl-sticky-widths\"))}catch(f){}d||\n(d=[]);c=!1;for(var e in d)if(window.innerWidth>=d[e][0]&&window.innerWidth<=d[e][1]){c=!0;break}c&&(void 0===b.sticky_offset||b.sticky_offset?(\"yes\"==b.jet_sticky_section&&(window.scrollY?(a.classList.add(\"jet-sticky-section--stuck\"),a.classList.add(\"jet-sticky-transition-in\")):(a.classList.remove(\"jet-sticky-section--stuck\"),a.classList.remove(\"jet-sticky-transition-in\"))),b.ekit_sticky_offset&&\"px\"==b.ekit_sticky_offset.unit&&(window.scrollY>=b.ekit_sticky_offset.size?(a.style.setProperty(\"position\",\n\"fixed\"),a.style.setProperty(\"top\",\"0\"),a.style.setProperty(\"width\",\"100%\"),a.classList.add(\"ekit-sticky\"),a.classList.add(\"ekit-sticky--active\")):(a.style.removeProperty(\"position\",\"fixed\"),a.style.removeProperty(\"top\",\"0\"),a.classList.remove(\"ekit-sticky\"),a.classList.remove(\"ekit-sticky--active\"))),b.ekit_sticky_effect_offset&&\"px\"==b.ekit_sticky_effect_offset.unit&&(window.scrollY>=b.ekit_sticky_effect_offset.size?a.classList.add(\"ekit-sticky--effects\"):a.classList.remove(\"ekit-sticky--effects\"))):\n0>=a.getBoundingClientRect().top&&a.classList.add(\"lzl-sticky\"))}return!0}\n(function(a){function c(b){var d=!0;a.querySelectorAll(\"[data-lzl-sticky-widths]\").forEach(function(e){seraph_accel_cp_elmntrStck_calcSizes(e)||(d=!1)});d||(a.querySelectorAll(\"[data-lzl-sticky-widths]\").forEach(function(e){e.classList.remove(\"js-lzl-ing\");e.classList.remove(\"lzl-sticky\")}),a.removeEventListener(\"seraph_accel_calcSizes\",c,{capture:!0,passive:!0}),a.removeEventListener(\"scroll\",c,{capture:!0,passive:!0}))}a.addEventListener(\"seraph_accel_calcSizes\",c,{capture:!0,passive:!0});a.addEventListener(\"scroll\",\nc,{capture:!0,passive:!0})})(document)" );
			$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
		}
	}
}

function _ProcessCont_Cp_elmntrWdgtLott( $ctx, &$ctxProcess, $settCache, $settImg, $settCdn, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-widget-lottie ")][@data-settings]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$dataSett = @json_decode( $item -> getAttribute( 'data-settings' ), true );
		if( !$dataSett )
			continue;

		$itemPlacehldr = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," e-lottie__animation ")]', $item ) );
		if( !$itemPlacehldr )
			continue;

		$renderer = (isset($dataSett[ 'renderer' ])?$dataSett[ 'renderer' ]:null);
		$dataFile = (isset($dataSett[ 'source_json' ][ 'url' ])?$dataSett[ 'source_json' ][ 'url' ]:null);
		if( !$dataFile )
			continue;

		$r = _ProcessCont_Cp_lottGen_AdjustItem( $ctx, $ctxProcess, $settCache, $settImg, $settCdn, $doc, $xpath, $itemPlacehldr, $renderer, $dataFile );
		if( $r === false )
			return( false );

		$dataSett[ 'source_json' ][ 'url' ] = $dataFile;

		if( !$r )
			continue;

		if( 0 )
		{

			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			HtmlNd::SetValFromContent( $itemScript, str_replace( array( 'PRM_PATH', 'PRM_RENDERER', 'PRM_LOOP', 'PRM_AUTOPLAY' ), array( $dataFile, $renderer, 'true', 'true' ), "bodymovin.loadAnimation({container:document.currentScript.parentNode,path:\"PRM_PATH\",renderer:\"PRM_RENDERER\",loop:PRM_LOOP,autoplay:PRM_AUTOPLAY})" ) );
			$itemPlacehldr -> insertBefore( $itemScript, $itemPlacehldr -> firstChild );
		}

		$item -> setAttribute( 'data-settings', @json_encode( $dataSett ) );

		$adjusted = true;
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@svg\\.lottgen@' ] = true;
		$ctxProcess[ 'aJsCritSpec' ][ 'id:@^lottie-js$@' ] = true;

		if( $itemScr = HtmlNd::FirstOfChildren( $xpath -> query( './/script[@id="lottie-js"]' ) ) )
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemScr );

		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, "svg.lottgen.js-lzl-ing:has(+ svg), .e-lottie__animation > svg:not(.lottgen) ~ * {\r\n\tdisplay: none !important;\r\n}" );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}

		{

			$ctx -> aAniAppear[ '.elementor-widget-lottie:not(.js-lzl-ed)' ] = 'function(b){if(window.bodymovin){var c=b.querySelector(".e-lottie__animation");if(c){b.classList.add("js-lzl-ed");try{var a=JSON.parse(b.getAttribute("data-settings"))}catch(d){}a||(a={});bodymovin.loadAnimation({container:c,path:a.source_json.url,renderer:a.renderer,loop:!0,autoplay:!0});delete a.source_json;b.setAttribute("data-settings",JSON.stringify(a))}}}';
		}
	}
}

function _ProcessCont_Cp_elmntrWdgtPrmLott( $ctx, &$ctxProcess, $settCache, $settImg, $settCdn, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-widget-premium-lottie ")][@data-settings]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$dataSett = @json_decode( $item -> getAttribute( 'data-settings' ), true );
		if( !$dataSett )
			continue;

		$itemPlacehldr = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," premium-lottie-animation ")]', $item ) );
		if( !$itemPlacehldr )
			continue;

		$renderer = $itemPlacehldr-> getAttribute( 'data-lottie-render' );
		$dataFile = $itemPlacehldr-> getAttribute( 'data-lottie-url' );
		if( !$dataFile )
			continue;

		$r = _ProcessCont_Cp_lottGen_AdjustItem( $ctx, $ctxProcess, $settCache, $settImg, $settCdn, $doc, $xpath, $itemPlacehldr, $renderer, $dataFile );
		if( $r === false )
			return( false );

		if( !$r )
			continue;

		$dataSett[ 'lottie_file' ][ 'url' ] = $dataFile;
		$itemPlacehldr-> setAttribute( 'data-lottie-url', $dataFile );

		$item -> setAttribute( 'data-settings', @json_encode( $dataSett ) );

		$adjusted = true;
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@svg\\.lottgen@' ] = true;
		$ctxProcess[ 'aJsCritSpec' ][ 'id:@^lottie-js-lzl$@' ] = true;

		{
			$itemScr = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScr -> setAttribute( 'type', 'application/js' );
			$itemScr -> setAttribute( 'src', 'https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js' );
			$itemScr -> setAttribute( 'id', 'lottie-js-lzl' );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemScr );
		}

		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, "svg.lottgen.js-lzl-ing:has(+ svg), .e-lottie__animation > svg:not(.lottgen) ~ * {\r\n\tdisplay: none !important;\r\n}" );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}

		{

			$ctx -> aAniAppear[ '.elementor-widget-premium-lottie:not(.js-lzl-ed)' ] = 'function(b){if(window.bodymovin){var a=b.querySelector(".premium-lottie-animation");a&&(b.classList.add("js-lzl-ed"),bodymovin.loadAnimation({container:a,path:a.getAttribute("data-lottie-url"),renderer:a.getAttribute("data-lottie-render"),loop:"true"===a.getAttribute("data-lottie-loop"),autoplay:!0}),a.removeAttribute("data-lottie-url"))}}';
		}
	}
}

function _ProcessCont_Cp_nktrLott( $ctx, &$ctxProcess, $settCache, $settImg, $settCdn, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," nectar-lottie ")][@data-lottie-settings]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$dataSett = @json_decode( $item -> getAttribute( 'data-lottie-settings' ), true );
		if( !$dataSett )
			continue;

		$dataFile = (isset($dataSett[ 'json_url' ])?$dataSett[ 'json_url' ]:null);
		if( !$dataFile )
			continue;

		$r = _ProcessCont_Cp_lottGen_AdjustItem( $ctx, $ctxProcess, $settCache, $settImg, $settCdn, $doc, $xpath, $item, 'svg', $dataFile );
		if( $r === false )
			return( false );

		if( !$r )
			continue;

		$dataSett[ 'json_url' ] = $dataFile;

		$item -> setAttribute( 'data-lottie-settings', @json_encode( $dataSett ) );

		$adjusted = true;
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@svg\\.lottgen@' ] = true;
		$ctxProcess[ 'aJsCritSpec' ][ 'id:@^lottie-js-lzl$@' ] = true;

		{
			$itemScr = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScr -> setAttribute( 'type', 'application/js' );
			$itemScr -> setAttribute( 'src', 'https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js' );
			$itemScr -> setAttribute( 'id', 'lottie-js-lzl' );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemScr );
		}

		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, "svg.lottgen.js-lzl-ing:has(+ svg), .e-lottie__animation > svg:not(.lottgen) ~ * {\r\n\tdisplay: none !important;\r\n}" );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}

		{

			$ctx -> aAniAppear[ '.nectar-lottie:not(.js-lzl-ed)' ] = 'function(b){if(window.bodymovin){b.classList.add("js-lzl-ed");try{var a=JSON.parse(b.getAttribute("data-lottie-settings"))}catch(c){}a||(a={});bodymovin.loadAnimation({container:b,path:a.json_url,renderer:"svg",loop:!0,autoplay:!0});delete a.json_url;b.setAttribute("data-lottie-settings",JSON.stringify(a))}}';
		}
	}
}

function _ProcessCont_Cp_elmsKitLott( $ctx, &$ctxProcess, $settCache, $settImg, $settCdn, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," ekit_lottie ")][@data-renderer]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$renderer = $item -> getAttribute( 'data-renderer' );
		$dataFile = $item -> getAttribute( 'data-path' );
		if( !$dataFile )
			continue;

		$r = _ProcessCont_Cp_lottGen_AdjustItem( $ctx, $ctxProcess, $settCache, $settImg, $settCdn, $doc, $xpath, $item, $renderer, $dataFile );
		if( $r === false )
			return( false );

		if( !$r )
			continue;

		{

			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			HtmlNd::SetValFromContent( $itemScript, str_replace( array( 'PRM_PATH', 'PRM_RENDERER', 'PRM_LOOP', 'PRM_AUTOPLAY' ), array( $dataFile, $renderer, $item -> getAttribute( 'data-loop' ), $item -> getAttribute( 'data-autoplay' ) ), "bodymovin.loadAnimation({container:document.currentScript.parentNode,path:\"PRM_PATH\",renderer:\"PRM_RENDERER\",loop:PRM_LOOP,autoplay:PRM_AUTOPLAY})" ) );
			HtmlNd::InsertAfter( $item, $itemScript, null, true );
		}

		$item -> removeAttribute( 'data-path' );

		$adjusted = true;
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@svg\\.lottgen@' ] = true;

		$ctxProcess[ 'aJsCritSpec' ][ 'id:@^lottie-js$@' ] = true;
		$ctxProcess[ 'aJsCritSpec' ][ 'body:@bodymovin\\.loadAnimation\\(\\s*{\\s*container\\s*:\\s*document\\.currentScript\\.parentNode\\W@' ] = true;

		if( $itemScr = HtmlNd::FirstOfChildren( $xpath -> query( './/script[@id="lottie-js"]' ) ) )
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemScr );

		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, 'svg.lottgen.js-lzl-ing:has(+ svg) {
	display: none!important;
}' );

			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}
	}
}

function _ProcessCont_Cp_elmntrWdgtAvoShcs( $ctx, &$ctxProcess, $doc, $xpath )
{
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-widget-avo-showcase ")]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," bg-img ")][@data-background]', $item ) as $itemBg )
		{
			$itemBg -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemBg -> getAttribute( 'style' ) ), array( 'background-image' => 'url("' . $itemBg -> getAttribute( 'data-background' ) . '")' ) ) ) );
			$itemBg -> removeAttribute( 'data-background' );
		}
	}
}

function _ProcessCont_Cp_elmntrShe( $ctx, &$ctxProcess, $doc, $xpath )
{
	$bDynamic = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-element ")][contains(concat(" ",normalize-space(@class)," ")," she-header-yes ")]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$dataSett = @json_decode( $item -> getAttribute( 'data-settings' ), true );

		if( Gen::GetArrField( $dataSett, array( 'transparent_header_show' ), '' ) != 'yes' )
			continue;

		$aTransparentOn = Gen::GetArrField( $dataSett, array( 'transparent_on' ), array() );
		if( count( $aTransparentOn ) == 3  )
		{
			HtmlNd::AddRemoveAttrClass( $item, array( 'she-header-transparent-yes' ) );
			continue;
		}

		static $g_aTransparentOnWidth = array( 'desktop' => array( 1025, 2147483647 ), 'tablet' => array( 768, 1024 ), 'mobile' => array( 0, 767 ) );

		$aStickyOn = array();
		foreach( $aTransparentOn as $stickyOnViewId )
			if( isset( $g_aTransparentOnWidth[ $stickyOnViewId ] ) )
				$aStickyOn[] = $g_aTransparentOnWidth[ $stickyOnViewId ];

		$item -> setAttribute( 'data-lzl-trnsp-widths', @json_encode( $aStickyOn ) );

		{
			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, 'seraph_accel_cp_elmntrShe_calcSizes(document.currentScript.parentNode);' );
			$item -> appendChild( $itemScript );
		}

		$bDynamic = true;
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $bDynamic )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.she-header-transparent-yes@' ] = true;

		{

			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, "function seraph_accel_cp_elmntrShe_calcSizes(a){var c=!1;try{var b=JSON.parse(a.getAttribute(\"data-lzl-trnsp-widths\"))}catch(e){}b||(b=[]);for(var d in b)if(window.innerWidth>=b[d][0]&&window.innerWidth<=b[d][1]){c=!0;break}c?a.classList.add(\"she-header-transparent-yes\"):a.classList.remove(\"she-header-transparent-yes\")}\n(function(a){function c(b){a.querySelectorAll(\".elementor-element.she-header-yes[data-lzl-trnsp-widths]\").forEach(function(d){seraph_accel_cp_elmntrShe_calcSizes(d)})}a.addEventListener(\"seraph_accel_calcSizes\",c,{capture:!0,passive:!0});seraph_accel_izrbpb.add(function(){a.removeEventListener(\"seraph_accel_calcSizes\",c,{capture:!0,passive:!0})})})(document)" );
			$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
		}
	}
}

function _ProcessCont_Cp_elmntrPremCrsl( $ctx, &$ctxProcess, $doc, $xpath )
{
	$adjusted = false;
	$idNext = 0;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," premium-carousel-wrapper ")][@data-settings]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$dataSett = @json_decode( $item -> getAttribute( 'data-settings' ), true );
		if( !$dataSett )
			continue;

		$sld = _SlickSld_PrepareCont( $ctx, $doc, $xpath, HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," premium-carousel-inner ")]', $item ) ), 'premium-carousel-template', (isset($dataSett[ 'dots' ])?$dataSett[ 'dots' ]:null) || (isset($dataSett[ 'arrows' ])?$dataSett[ 'arrows' ]:null) );
		if( !$sld )
			continue;

		HtmlNd::AddRemoveAttrClass( $item, '', array( 'premium-carousel-hidden' ) );

		$selId = $item -> getAttribute( 'id' );
		if( $selId )
			$selId = '#' . $selId;
		else
		{
			$selId = 'lzl-' . $idNext++;
			HtmlNd::AddRemoveAttrClass( $item, array( $selId ) );
			$selId = '.' . $selId;
		}

		$aViews = array( 'slidesMob' => ( int )(isset($dataSett[ 'mobileBreak' ])?$dataSett[ 'mobileBreak' ]:null) - 1, 'slidesTab' => ( int )(isset($dataSett[ 'tabletBreak' ])?$dataSett[ 'tabletBreak' ]:null) - 1, isset( $dataSett[ 'slidesDesk' ] ) ? 'slidesDesk' : 'slidesToShow' => null );

		$itemStyleCont = '';
		$maxWidthPrev = null;
		foreach( $aViews as $optId => $maxWidth )
		{
			$nShow = ( int )Gen::GetArrField( $dataSett, array( $optId ) );
			if( !$nShow )
				continue;

			if( $maxWidth <= 0 )
				$maxWidth = null;

			if( $maxWidthPrev || $maxWidth )
				$itemStyleCont .= '@media ' . ( $maxWidthPrev ? ( '(min-width: ' . ( $maxWidthPrev + 1 ) . 'px)' ) : '' ) . ( $maxWidthPrev && $maxWidth ? ' and ' : '' ) . ( $maxWidth ? ( '(max-width: ' . $maxWidth . 'px)' ) : '' ) . ' {' . "\n";

			$itemStyleCont .= '.premium-carousel-wrapper' . $selId . ' .premium-carousel-inner:not(.slick-initialized)' . ( $sld -> bSimpleCont ? '' : ' ' ) . '.lzl-c > * {width: calc(100% / ' . $nShow . ');}' . "\n";
			$itemStyleCont .= '.premium-carousel-wrapper' . $selId . ' .premium-carousel-inner:not(.slick-initialized)' . ( $sld -> bSimpleCont ? '' : ' ' ) . '.lzl-c > *:nth-child(n+' . ( $nShow + 1 ) . ') {visibility:hidden!important;}' . "\n";

			{
				$nDots = _SlickSld_GetDotsCount( array( 'slideCount' => $sld -> nSlides, 'slidesToShow' => $nShow, 'slidesToScroll' => ( int )(isset($dataSett[ 'slidesToScroll' ])?$dataSett[ 'slidesToScroll' ]:null), 'infinite' => ( bool )(isset($dataSett[ 'infinite' ])?$dataSett[ 'infinite' ]:null), 'centerMode' => ( bool )(isset($dataSett[ 'centerMode' ])?$dataSett[ 'centerMode' ]:null), 'asNavFor' => false ) );
				$itemStyleCont .= '.premium-carousel-wrapper' . $selId . ' .premium-carousel-inner:not(.slick-initialized) .slick-dots' . ( $nDots ? ' > *:nth-child(n+' . ( $nDots + 1 ) . ')' : '' ) . ' {display:none;}' . "\n";
			}

			if( $maxWidthPrev || $maxWidth )
				$itemStyleCont .= '}' . "\n";

			if( $maxWidth )
				$maxWidthPrev = $maxWidth;
		}

		{
			$itemStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemStyle, $itemStyleCont );
			$item -> parentNode -> insertBefore( $itemStyle, $item );
		}

		if( (isset($dataSett[ 'arrows' ])?$dataSett[ 'arrows' ]:null) )
		{
			if( $itemPrev = HtmlNd::FirstOfChildren( $xpath -> query( './*[contains(concat(" ",normalize-space(@class)," ")," premium-carousel-nav-arrow-prev ")]/*[1]', $item ) ) )
			{
				$itemPrev = $itemPrev -> cloneNode( true );
				HtmlNd::AddRemoveAttrClass( $itemPrev, array( 'slick-arrow' ) );
				$sld -> itemSlides -> insertBefore( $itemPrev, $sld -> itemSlides -> firstChild );
			}

			if( $itemNext = HtmlNd::FirstOfChildren( $xpath -> query( './*[contains(concat(" ",normalize-space(@class)," ")," premium-carousel-nav-arrow-next ")]/*[1]', $item ) ) )
			{
				$itemNext = $itemNext -> cloneNode( true );
				HtmlNd::AddRemoveAttrClass( $itemNext, array( 'slick-arrow' ) );
				$sld -> itemSlides -> appendChild( $itemNext );
			}
		}

		if( (isset($dataSett[ 'dots' ])?$dataSett[ 'dots' ]:null) )
		{
			if( (isset($dataSett[ 'carouselNavigation' ])?$dataSett[ 'carouselNavigation' ]:null) == 'dots' )
				$sld -> dotTpl = ( string )HtmlNd::DeParse( HtmlNd::FirstOfChildren( $xpath -> query( './*[contains(concat(" ",normalize-space(@class)," ")," premium-carousel-nav-dot ")]', $item ) ), false );

			if( !$sld -> dotTpl )
				$sld -> dotTpl = 'X';

			_SlickSld_AddDots( $doc, $sld -> itemSlides, 'slick-dots', $sld -> nSlides, function( $sld, $i ) { return( '<li role="presentation">' . $sld -> dotTpl . '</li>' ); }, $sld );
		}

		$adjusted = true;
	}

	if( $adjusted && $ctxProcess[ 'mode' ] == 'full' )
	{
		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, _SlickSld_GetGlobStyle( '.premium-carousel-inner', 'premium-carousel-template' ) . '' );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}

		_SlickSld_InitGlob( $ctx, $ctxProcess, $doc, '.premium-carousel-inner' );
	}
}

function _ProcessCont_Cp_elmntrWdgtImgCrsl( $ctx, &$ctxProcess, $doc, $xpath )
{
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-widget-image-carousel ")][@data-settings]|.//*[contains(concat(" ",normalize-space(@class)," ")," elementor-widget-n-carousel ")][@data-settings]|.//*[contains(concat(" ",normalize-space(@class)," ")," elementor-widget-loop-carousel ")][@data-settings]|.//*[contains(concat(" ",normalize-space(@class)," ")," elementor-widget-media-carousel ")][@data-settings]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		if( $ctx -> cfgElmntrFrontend === null )
			$ctx -> cfgElmntrFrontend = _Elmntr_GetFrontendCfg( $xpath );

		$itemCssSel = '.elementor-element-' . $item -> getAttribute( 'data-id' );

		$dataSett = @json_decode( $item -> getAttribute( 'data-settings' ), true );
		$itemStyleCont = '';

		foreach( array( array( 'type' => '', 'widthAlign' => 767, 'cxMin' => Gen::GetArrField( $ctx -> cfgElmntrFrontend, array( 'views', 'desktop', 'cxMin' ), 0 ) ), array( 'type' => '_tablet', 'widthAlign' => 767, 'cxMin' => Gen::GetArrField( $ctx -> cfgElmntrFrontend, array( 'views', 'tablet', 'cxMin' ), 0 ), 'cxMax' => Gen::GetArrField( $ctx -> cfgElmntrFrontend, array( 'views', 'tablet', 'cxMax' ), 0 ) ), array( 'type' => '_mobile', 'widthAlign' => 766, 'cxMax' => Gen::GetArrField( $ctx -> cfgElmntrFrontend, array( 'views', 'mobile', 'cxMax' ), 0 ) ) ) as $view )
		{
			$nSlidesShow = ( int )Gen::GetArrField( $dataSett, array( 'slides_to_show' . $view[ 'type' ] ) );
			if( !$nSlidesShow )
				$nSlidesShow = ( int )Gen::GetArrField( $dataSett, array( 'slides_to_show' ) );

			$sImageSpacingCustom = ( string )Gen::GetArrField( $dataSett, array( 'image_spacing_custom' . $view[ 'type' ], 'size' ) );
			if( strlen( $sImageSpacingCustom ) )
				$sImageSpacingCustom .= ( string )Gen::GetArrField( $dataSett, array( 'image_spacing_custom' . $view[ 'type' ], 'unit' ) );
			else
				$sImageSpacingCustom = ( string )Gen::GetArrField( $dataSett, array( 'image_spacing_custom', 'size' ) ) . ( string )Gen::GetArrField( $dataSett, array( 'image_spacing_custom', 'unit' ) );

			if( !strlen( $sImageSpacingCustom ) )
				$sImageSpacingCustom = '0';

			if( isset( $view[ 'cxMax' ] ) )
				$itemStyleCont .= '@media (max-width: ' . $view[ 'cxMax' ] . 'px) {';
			$itemStyleCont .= '
					' . $itemCssSel . ' .swiper:not(.swiper-initialized) .swiper-slide, ' . $itemCssSel . ' .swiper-container:not(.swiper-container-initialized) .swiper-slide
					{
						width: calc((100% - (' . ( $nSlidesShow - 1 ) . ')*' . $sImageSpacingCustom . ')/' . $nSlidesShow . ');
						margin-right: ' . $sImageSpacingCustom . ';
					}
				';

			if( isset( $view[ 'cxMax' ] ) )
				$itemStyleCont .= '}';
		}

		$itemStyleCont .= '
				' . $itemCssSel . ' .swiper:not(.swiper-initialized) > .swiper-wrapper, ' . $itemCssSel . ' .swiper-container:not(.swiper-container-initialized) > .swiper-wrapper
				{
					gap: 0;
				}
			';

		if( $ctxProcess[ 'mode' ] == 'full' && $itemStyleCont )
		{
			$itemStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemStyle, $itemStyleCont );
			$item -> parentNode -> insertBefore( $itemStyle, $item );
		}
	}
}

function _ProcessCont_Cp_elmntrWdgtWooPrdImgs( $ctx, &$ctxProcess, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-widget-woocommerce-product-images ")]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$itemWrp = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," woocommerce-product-gallery__wrapper ")]', $item ) );
		if( !$itemWrp )
			continue;

		$aThumbs = array();
		for( $itemWrpChild = HtmlNd::GetFirstElement( $itemWrp ); $itemWrpChild; $itemWrpChild = HtmlNd::GetNextElementSibling( $itemWrpChild ) )
			$aThumbs[] = HtmlNd::CreateTag( $doc, 'li', null, array( HtmlNd::CreateTag( $doc, 'img', array( 'src' => $itemWrpChild -> getAttribute( 'data-thumb' ) ) ) ) );

		if( !$aThumbs )
			continue;

		HtmlNd::AddRemoveAttrClass( HtmlNd::GetFirstElement( $aThumbs[ 0 ] ), array( 'flex-active' ) );

		$itemWrp -> parentNode -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => array( 'flex-viewport', 'js-lzl-ing' ) ) ) );
		$itemWrp -> parentNode -> appendChild( HtmlNd::CreateTag( $doc, 'ol', array( 'class' => array( 'flex-control-thumbs', 'js-lzl-ing' ) ), $aThumbs ) );

		$adjusted = true;
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
	{
		$cmnStyle =
			"body:is(.seraph-accel-js-lzl-ing, .seraph-accel-js-lzl-ing-ani) .elementor-widget-woocommerce-product-images .woocommerce-product-gallery__wrapper {\r\n\twidth: 100% !important;\r\n}\r\n\r\n.elementor-widget-woocommerce-product-images .woocommerce-product-gallery:has(.flex-viewport:not(.js-lzl-ing)) > .flex-control-thumbs.js-lzl-ing,\r\n.elementor-widget-woocommerce-product-images .woocommerce-product-gallery:has(.flex-viewport:not(.js-lzl-ing)) > .flex-viewport.js-lzl-ing {\r\n\tdisplay: none !important;\r\n}\r\n\r\nbody:is(.seraph-accel-js-lzl-ing, .seraph-accel-js-lzl-ing-ani) .elementor-widget-woocommerce-product-images .woocommerce-product-gallery__wrapper {\r\n\tdisplay: flex !important;\r\n\toverflow: hidden !important;\r\n}\r\n\r\nbody:is(.seraph-accel-js-lzl-ing, .seraph-accel-js-lzl-ing-ani) .elementor-widget-woocommerce-product-images .woocommerce-product-gallery__wrapper > * {\r\n\tflex-shrink: 0 !important;\r\n\twidth:100% !important;\r\n}";

		if( $contFlexyStyles = HtmlNd::FirstOfChildren( $xpath -> query( './/link[@id="ct-flexy-styles-css"][@rel="stylesheet"][@href]' ) ) )
		{
			if( $contFlexyStyles = _Cp_GetScrCont( $ctxProcess, $contFlexyStyles -> getAttribute( 'href' ) ) )
			{
				foreach( array( '@\\.flex-control-nav\\s*{[^}]+}@m', '@\\.flex-control-nav\\s+li\\s*{[^}]+}@' ) as $e )
					if( @preg_match( $e, $contFlexyStyles, $m ) )
						$cmnStyle .= str_replace( '.flex-control-nav', '.flex-control-thumbs.js-lzl-ing', $m[ 0 ] );
			}
		}

		$itemsCmnStyle = $doc -> createElement( 'style' );
		if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
			$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
		HtmlNd::SetValFromContent( $itemsCmnStyle, $cmnStyle );
		$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
	}
}

function _ProcessCont_Cp_elmntrWdgtCntr( $ctx, &$ctxProcess, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-widget-counter ")]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$adjusted = true;
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
	{

		$ctx -> aAniAppear[ '.elementor-widget-counter:not(.lzl-cntr-ad)' ] = 'function(c){var a=c.querySelector(".elementor-counter-number");if(a){c.classList.add("lzl-cntr-ad");c=parseInt(a.getAttribute("data-duration"),10);var b=parseInt(a.getAttribute("data-from-value"),10),d=parseInt(a.getAttribute("data-to-value"),10),f=a.getAttribute("data-delimiter");a.setAttribute("data-from-value",d);a.setAttribute("data-duration",0);var g=(d-b)/(c/10),h=setInterval(function(){b+=g;b>=d&&(b=d,clearInterval(h));var e=""+Math.round(b);f&&(e=e.replace(/\B(?=(\d{3})+(?!\d))/g,
f));a.textContent=e},10)}}';
	}
}

function _ProcessCont_Cp_elmntrStrtch( $ctx, &$ctxProcess, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-section ")][contains(concat(" ",normalize-space(@class)," ")," elementor-section-stretched ")]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		HtmlNd::AddRemoveAttrClass( $item -> parentNode, array( 'lzl-strtch-owner' ) );

		$adjusted = true;
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
	{
		{

			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, "(function(a){a.addEventListener(\"seraph_accel_calcSizes\",function(e){var d=\"rtl\"==a.documentElement.getAttribute(\"dir\");a.querySelectorAll(\".lzl-strtch-owner\").forEach(function(b){var c=b.getBoundingClientRect();b.style.setProperty(\"--lzl-strtch-offs-x\",\"\"+(d?a.documentElement.clientWidth-c.right:c.left)+\"px\")})},{capture:!0,passive:!0})})(document)" );
			$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
		}
	}
}

function _SlickSld_PrepareCont( $ctx, $doc, $xpath, $itemSlides, $classSlide, $bInsideCtls = false )
{
	if( !$itemSlides )
		return( null );

	$sld = new AnyObj();
	$sld -> itemSlides = $itemSlides;

		$sld -> bSimpleCont = false;

	if( !$sld -> bSimpleCont )
	{
		$sld -> itemSlides = $itemSlides -> cloneNode( false );
		HtmlNd::AddRemoveAttrClass( $sld -> itemSlides, array( 'js-lzl-ing' ) );
		$itemSlidesContTmp = HtmlNd::CreateTag( $doc, 'div', array( 'class' => array( 'slick-track', 'lzl-c' ) ) );
	}
	else
		HtmlNd::AddRemoveAttrClass( $itemSlides, array( 'lzl-c' ) );

	$sld -> nSlides = 0;
	foreach( $xpath -> query( './*', $itemSlides ) as $itemSlide )
	{
		if( !in_array( $classSlide, HtmlNd::GetAttrClass( $itemSlide ) ) )
		{
			if( !$sld -> bSimpleCont )
				$sld -> itemSlides -> appendChild( $itemSlide -> cloneNode( true ) );
			continue;
		}

		$sld -> nSlides++;

		if( !$sld -> bSimpleCont )
		{
			$itemSlide = $itemSlide -> cloneNode( true );
			$itemSlidesContTmp -> appendChild( $itemSlide );
		}

		if( $sld -> nSlides == 1 )
			HtmlNd::AddRemoveAttrClass( $itemSlide, array( 'slick-current' ) );
	}

	if( !$sld -> nSlides )
		return( null );

	if( !$sld -> bSimpleCont )
	{

		$sld -> itemSlides -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => array( 'slick-list' ) ), array( $itemSlidesContTmp ) ) );
		$itemSlides -> parentNode -> appendChild( $sld -> itemSlides );

		{
			$itemNoScript = $doc -> createElement( 'noscript' );
			$itemNoScript -> setAttribute( 'data-lzl-bjs', '' );
			$itemSlides -> parentNode -> insertBefore( $itemNoScript, $itemSlides );
			$itemNoScript -> appendChild( $itemSlides );
			ContNoScriptItemClear( $itemSlides );

			$ctx -> bBjs = true;
		}
	}

	HtmlNd::AddRemoveAttrClass( $sld -> itemSlides, array( 'slick-slider' ) );

	return( $sld );
}

function _SlickSld_GetGlobStyle( $selSlides, $classSlide )
{
	return( '' . $selSlides . ':not(.slick-initialized).lzl-c, ' . $selSlides . ':not(.slick-initialized) .lzl-c {
	flex-wrap: nowrap;
	display: flex;
}
' . $selSlides . ':not(.slick-initialized).lzl-c > *, ' . $selSlides . ':not(.slick-initialized) .lzl-c > * {
	flex-shrink: 0;
}
' . $selSlides . ':not(.slick-initialized):not(.lzl-c):not(.js-lzl-ing),
' . $selSlides . '.slick-initialized + ' . $selSlides . '.js-lzl-ing,
' . $selSlides . '.slick-initialized.js-lzl-ing {
	display: none !important;
}' );
}

function _SlickSld_InitGlob( $ctx, &$ctxProcess, $doc, $selSlides )
{

	$itemScript = $doc -> createElement( 'script' );
	if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
		$itemScript -> setAttribute( 'type', 'text/javascript' );
	$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
	HtmlNd::SetValFromContent( $itemScript, str_replace( '_PRM_SELSLIDES_', $selSlides, "(function(c,b){seraph_accel_izrbpb.add(function(){c.querySelectorAll(\"_PRM_SELSLIDES_:not(.slick-initialized):not(.lzl-c):not(.js-lzl-ing)\").forEach(function(a){b.MutationObserver&&(a.obsedgvsd=new b.MutationObserver(function(){a.obsedgvsd&&a.classList.contains(\"slick-initialized\")&&(a.obsedgvsd.disconnect(),delete a.obsedgvsd,a.slick&&a.slick.refresh())}),a.obsedgvsd.observe(a,{attributes:!0,attributeFilter:[\"class\"]}))})},110)})(document,window)" ) );
	$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
}

function _SlickSld_AddDots( $doc, $itemSlides, $class, $n, $cbItemTpl, $cbCtx = null )
{
	HtmlNd::AddRemoveAttrClass( $itemSlides, array( 'slick-dotted' ) );

	$itemCtl = HtmlNd::ParseAndImport( $doc, '<ul class="' . $class . '" role="tablist"></ul>' );
	if( !$itemCtl )
		return;

	for( $i = 0; $i < $n; $i++ )
	{
		$itemDot = HtmlNd::ParseAndImport( $doc, ( string )call_user_func( $cbItemTpl, $cbCtx, $i ) );
		if( !$itemDot )
			return;

		if( !$i )
			HtmlNd::AddRemoveAttrClass( $itemDot, array( 'slick-active' ) );
		$itemCtl -> appendChild( $itemDot );
	}

	$itemSlides -> appendChild( $itemCtl );
}

function _SlickSld_GetDotsCount( $aPrm = array( 'slideCount' => 1, 'slidesToShow' => 1, 'slidesToScroll' => 1, 'infinite' => false, 'centerMode' => false, 'asNavFor' => false ) )
{
	$e = 0;
	$t = 0;
	$o = 0;

	if( !$aPrm[ 'slidesToScroll' ] )
		$aPrm[ 'slidesToScroll' ] = 1;
	if( $aPrm[ 'slidesToScroll' ] > $aPrm[ 'slidesToShow' ] )
		$aPrm[ 'slidesToScroll' ] = $aPrm[ 'slidesToShow' ];

    if( $aPrm[ 'infinite' ] )
	{
        if( $aPrm[ 'slideCount' ] <= $aPrm[ 'slidesToShow' ] )
            ++$o;
        else
		{
            for( ; $e < $aPrm[ 'slideCount' ]; )
			{
                ++$o;
                $e = $t + $aPrm[ 'slidesToScroll' ];
                $t += $aPrm[ 'slidesToScroll' ] <= $aPrm[ 'slidesToShow' ] ? $aPrm[ 'slidesToScroll' ] : $aPrm[ 'slidesToShow' ];
			}
		}
	}
    else if( $aPrm[ 'centerMode' ] )
	{
        $o = $aPrm[ 'slideCount' ];
	}
    else if( $aPrm[ 'asNavFor' ] )
	{
        for( ; $e < $aPrm[ 'slideCount' ]; )
		{
            ++$o;
            $e = $t + $aPrm[ 'slidesToScroll' ];
            $t += $aPrm[ 'slidesToScroll' ] <= $aPrm[ 'slidesToShow' ] ? $aPrm[ 'slidesToScroll' ] : $aPrm[ 'slidesToShow' ];
		}
	}
    else
	{
        $o = 1 + ceil( ( $aPrm[ 'slideCount' ] - $aPrm[ 'slidesToShow' ] ) / $aPrm[ 'slidesToScroll' ] );
	}

	return( $o > 1 ? $o : 0 );
}

function _Elmntr_GetFrontendCfg( $xpath )
{
	$raw = _Elmntr_GetFrontendCfgEx( HtmlNd::FirstOfChildren( $xpath -> query( './/script[@id="elementor-frontend-js-before"]' ) ) );

	$prms = array(
		'views' => array(
			'mobile' => array(
				'cxMin' => 0,
				'cxMax' => ( Gen::GetArrField( $raw, array( 'views', 'mobile' ), 0 ) - 1 ),
			),

			'tablet' => array(
				'cxMin' => Gen::GetArrField( $raw, array( 'views', 'mobile' ), 0 ),
				'cxMax' => ( Gen::GetArrField( $raw, array( 'views', 'tablet' ), 0 ) - 1 ),
			),

			'desktop' => array(
				'cxMin' => Gen::GetArrField( $raw, array( 'views', 'tablet' ), 0 ),
				'cxMax' => 2147483647,
			)
		)
	);

	return( $prms );
}

function _Elmntr_GetFrontendCfgEx( $itemInitCmnScr )
{
	if( !$itemInitCmnScr )
		return( null );

	$m = array();
	if( !preg_match( '@\\WelementorFrontendConfig\\s*\\=\\s*@', $itemInitCmnScr -> nodeValue, $m, PREG_OFFSET_CAPTURE ) )
		return( null );

	$posStart = $m[ 0 ][ 1 ] + strlen( $m[ 0 ][ 0 ] );
	$pos = Gen::JsonGetEndPos( $posStart, $itemInitCmnScr -> nodeValue );
	if( $pos === null )
		return;

	$prms = @json_decode( Gen::JsObjDecl2Json( substr( $itemInitCmnScr -> nodeValue, $posStart, $pos - $posStart ) ), true );
	if( !$prms )
		return( null );

	foreach( array( 'mobile' => 767, 'tablet' => 1024 ) as $k => $def )
	{
		$nMax = Gen::GetArrField( $prms, array( 'responsive', 'breakpoints', $k, 'value' ), 0 );
		if( !$nMax )
			$nMax = Gen::GetArrField( $prms, array( 'responsive', 'breakpoints', $k, 'default_value' ), 0 );
		if( !$nMax )
			$nMax = $def;
		$prms[ 'views' ][ $k ] = $nMax;
	}

	return( $prms );
}

function _ProcessCont_Cp_fltsmThBgFill( $ctx, &$ctxProcess, $doc, $xpath )
{
	foreach( $xpath -> query( './/body[contains(concat(normalize-space(@class)," "),"flatsome ")]//*[contains(concat(" ",normalize-space(@class)," ")," bg ")][contains(concat(" ",normalize-space(@class)," ")," fill ")][contains(concat(" ",normalize-space(@class)," ")," bg-fill ")]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		HtmlNd::AddRemoveAttrClass( $item, array( 'bg-loaded' ) );
	}
}

function _ProcessCont_Cp_fltsmThAni( $ctx, &$ctxProcess, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/body[contains(concat(normalize-space(@class)," "),"flatsome ")]//*[@data-animate]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$adjusted = true;
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\[data-animate-transform@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\[data-animate-transition@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\[data-animated@' ] = true;

		$ctx -> aAniAppear[ '[data-animate]:not([data-animated])' ] = "function(a,c){a.setAttribute(\"data-animate-transform\",\"true\");a.setAttribute(\"data-animate-transition\",\"true\");a.setAttribute(\"data-animated\",\"true\");var b=getComputedStyle(a);setTimeout(function(){a.removeAttribute(\"data-animate\")},c.GetDurationTime(b.getPropertyValue(\"animation-delay\")+\",\"+b.getPropertyValue(\"transition-delay\"),\"max\")+c.GetDurationTime(b.getPropertyValue(\"transition-duration\"),\"max\"))}";
	}
}

function _ProcessCont_Cp_ntBlueThRspnsv( $ctx, &$ctxProcess, $doc, $xpath )
{
	if( !HtmlNd::FirstOfChildren( $xpath -> query( './/body[contains(concat(" ",normalize-space(@class)," ")," ninetheme-theme-name-NT ")]' ) ) )
		return;

	{

		if( HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," welcome_default ")]' ) ) )
			HtmlNd::AddRemoveAttrClass( $ctxProcess[ 'ndBody' ], array( 'default-version' ) );
		else
			HtmlNd::AddRemoveAttrClass( $ctxProcess[ 'ndBody' ], array(), array( 'default-version' ) );

		if( HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," welcome_rtl ")]' ) ) )
			HtmlNd::AddRemoveAttrClass( $ctxProcess[ 'ndBody' ], array( 'rtl_version' ) );
		else
			HtmlNd::AddRemoveAttrClass( $ctxProcess[ 'ndBody' ], array(), array( 'rtl_version' ) );
	}

	if( $itemMobIcon = HtmlNd::ParseAndImport( $doc, '<div class="mean-bar js-lzl-ing"><a href="#nav" class="meanmenu-reveal" style="right:0;left:auto;"><span><span><span></span></span></span></a></div>' ) )
		$ctxProcess[ 'ndBody' ] -> insertBefore( $itemMobIcon, $ctxProcess[ 'ndBody' ] -> firstChild );

	if( $ctxProcess[ 'mode' ] == 'full' )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.mean-container@' ] = true;

		{
			$itemsCmnStyle = $doc->createElement('style');
			if (apply_filters('seraph_accel_jscss_addtype', false))
				$itemsCmnStyle->setAttribute('type', 'text/css');
			HtmlNd::SetValFromContent($itemsCmnStyle, 'body:not(.seraph-accel-js-lzl-ing) .mean-bar.js-lzl-ing,
body.mean-container.seraph-accel-js-lzl-ing .mainmenu nav,
body:not(.mean-container).seraph-accel-js-lzl-ing .mean-bar.js-lzl-ing {
{
	display: none;
}' );

			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}

		{

			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, "function seraph_accel_cp_ntBlueThRspnsv_calcSizes(){767>=(window.innerWidth||document.documentElement.clientWidth)?document.body.classList.add(\"mean-container\"):document.body.classList.remove(\"mean-container\")}seraph_accel_cp_ntBlueThRspnsv_calcSizes();(function(a){function b(){seraph_accel_cp_ntBlueThRspnsv_calcSizes()}a.addEventListener(\"seraph_accel_calcSizes\",b,{capture:!0,passive:!0});seraph_accel_izrbpb.add(function(){a.removeEventListener(\"seraph_accel_calcSizes\",b,{capture:!0,passive:!0})})})(document)" );
			$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
		}
	}
}

function _ProcessCont_Cp_mdknThRspnsv( $ctx, &$ctxProcess, $doc, $xpath )
{
	if( !HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," mediken-header-top ")]' ) ) )
		return;

	$itemHdr = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," menu_area ")][contains(concat(" ",normalize-space(@class)," ")," mobile-menu ")]' ) );
	if( !$itemHdr )
		return;

	HtmlNd::AddRemoveAttrClass( $itemHdr, array( 'mean-container' ) );

	if( $itemMobHdr = HtmlNd::ParseAndImport( $doc, '<div class="mean-bar js-lzl-ing"><a href="#nav" class="meanmenu-reveal" style="background:;color:;right:0;left:auto;"><span></span><span></span><span></span></a></div>' ) )
		HtmlNd::InsertBefore( $itemHdr, $itemMobHdr, $itemHdr -> firstChild );

	if( $ctxProcess[ 'mode' ] == 'full' )
	{
		{
			$itemsCmnStyle = $doc->createElement('style');
			if (apply_filters('seraph_accel_jscss_addtype', false))
				$itemsCmnStyle->setAttribute('type', 'text/css');
			HtmlNd::SetValFromContent($itemsCmnStyle, 'body:not(.seraph-accel-js-lzl-ing) .mean-bar.js-lzl-ing,
body.seraph-accel-js-lzl-ing .menu_area.mobile-menu > nav,
body.seraph-accel-js-lzl-ing .mean-bar:not(.js-lzl-ing) {
{
	display: none;
}' );

			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}
	}
}

function _ProcessCont_Cp_phloxThRspnsv( $ctx, &$ctxProcess, $doc, $xpath )
{
	if( !HtmlNd::FirstOfChildren( $xpath -> query( './/body[contains(concat(" ",normalize-space(@class)," ")," theme-phlox")]' ) ) )
		return;

	HtmlNd::AddRemoveAttrClass( $ctxProcess[ 'ndBody' ], array( 'aux-dom-ready' ), array( 'aux-dom-unready' ) );

	if( $ctxProcess[ 'mode' ] == 'full' )
	{
		$aSwitch = array();
		foreach( $xpath -> query( './/*[@data-switch-width]' ) as $item )
			$aSwitch[ $item -> getAttribute( 'data-switch-width' ) ][] = 'body.seraph-accel-js-lzl-ing #' . $item -> getAttribute( 'id' );

		if( $aSwitch )
		{
			$cont = '';
			foreach( $aSwitch as $width => $aSw )
				$cont .= '@media (max-width: ' . $width . 'px){' . implode( ',', $aSw ) . '{display:none}}';

			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, $cont );

			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}
	}
}

function _ProcessCont_Cp_phloxThAni( $ctx, &$ctxProcess, $doc, $xpath )
{

	if( $ctxProcess[ 'mode' ] == 'full' && HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," aux-appear-watch-animation ")]' ) ) )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.aux-animated@' ] = true;
		$ctxProcess[ 'aCssCrit' ][ '@\\.aux-animated-once@' ] = true;

		{

			$ctx -> aAniAppear[ '.aux-appear-watch-animation:not(.aux-animated)' ] = 'function(a){a.classList.add("aux-animated");a.classList.add("aux-animated-once")}';
		}
	}
}

function _ProcessCont_Cp_brcksAni( $ctx, &$ctxProcess, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/body[contains(@class,"bricks")]//*[contains(@data-interactions,"animationType")]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$dataSett = ( array )@json_decode( $item -> getAttribute( 'data-interactions' ), true );
		foreach( $dataSett as $dataSettI )
		{
			if( Gen::GetArrField( $dataSettI, array( 'trigger' ), '' ) != 'enterView' )
				continue;

			$sAniName = Gen::GetArrField( $dataSettI, array( 'animationType' ), '' );

			$item -> setAttribute( 'data-lzl-an', $sAniName );
			$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( 'animation-duration' => Gen::GetArrField( $dataSettI, array( 'animationDuration' ) ), 'animation-delay' => Gen::GetArrField( $dataSettI, array( 'animationDelay' ) ) ) ) ) );

			if( $ctxProcess[ 'mode' ] == 'full' )
				$ctxProcess[ 'aCssCrit' ][ '@\\.brx-animate-' . $sAniName . '@' ] = true;

			$adjusted = true;
			break;
		}
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.brx-animated@' ] = true;

		{

			$ctx -> aAniAppear[ '[data-lzl-an]:not(.brx-animated)' ] = 'function(a){setTimeout(function(){a.classList.add("brx-animate-"+a.getAttribute("data-lzl-an"));a.classList.add("brx-animated")})}';
		}
	}
}

function _ProcessCont_Cp_sbThAni( $ctx, &$ctxProcess, $doc, $xpath )
{

	$adjusted = false;
	foreach( $xpath -> query( './/body[contains(concat(" ",normalize-space(@class)," ")," sandbox-theme")]//*[@data-cue]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$ctxProcess[ 'aCssCrit' ][ '@' . $item -> getAttribute( 'data-cue' ) . '@' ] = true;

		$adjusted = true;
	}

	if( $adjusted && $ctxProcess[ 'mode' ] == 'full' )
	{
		{

			$ctx -> aAniAppear[ '[data-cue]:not([data-show=true])' ] = 'function(a){a.style.setProperty("animation-name",a.getAttribute("data-cue"));a.style.setProperty("animation-duration",a.getAttribute("data-duration")+"ms");a.style.setProperty("animation-delay",a.getAttribute("data-delay")+"ms");a.style.setProperty("animation-timing-function","ease");a.style.setProperty("animation-direction","normal");a.style.setProperty("animation-fill-mode","both");a.setAttribute("data-show","true")}';
		}
	}
}

function _ProcessCont_Cp_lottGen( $ctx, &$ctxProcess, $settCache, $settImg, $settCdn, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/script[contains(text(),"bodymovin.loadAnimation(")]' ) as $itemScr )
	{
		$item = $itemScr -> parentNode;

		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		if( !preg_match( '@bodymovin\\.loadAnimation\\(\\s*{\\s*container\\s*:\\s*document\\.getElementById\\W@', $itemScr -> nodeValue ) )
			continue;

		$dataFile = array();
		if( preg_match( '@\\Wpath\\s*:\\s*[\'"]([\\w\\/\\.-]+)[\'"]@', $itemScr -> nodeValue, $dataFile, PREG_OFFSET_CAPTURE ) )
			$dataFile = $dataFile[ 1 ];

		if( !$dataFile )
			continue;

		$renderer = array();
		if( preg_match( '@\\Wrenderer\\s*:\\s*[\'"](\\w+)[\'"]@', $itemScr -> nodeValue, $renderer ) )
			$renderer = $renderer[ 1 ];

		$dataFileNew = $dataFile[ 0 ];
		$r = _ProcessCont_Cp_lottGen_AdjustItem( $ctx, $ctxProcess, $settCache, $settImg, $settCdn, $doc, $xpath, $item, $renderer, $dataFileNew );
		if( $r === false )
			return( false );

		if( !$r )
			continue;

		if( $dataFileNew != $dataFile[ 0 ] )
			$itemScr -> nodeValue = substr_replace( $itemScr -> nodeValue, $dataFileNew, $dataFile[ 1 ], strlen( $dataFile[ 0 ] ) );

		$adjusted = true;
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@svg\\.lottgen@' ] = true;

		$ctxProcess[ 'aJsCritSpec' ][ 'body:@\\Wbodymovin\\W@' ] = true;

		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, 'svg.lottgen.js-lzl-ing:has(+ svg) {
	display: none!important;
}' );

			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}
	}
}

function _ProcessCont_Cp_lottGen_AdjustItem( $ctx, &$ctxProcess, $settCache, $settImg, $settCdn, $doc, $xpath, $item, $renderer, &$srcData )
{
	if( $renderer != 'svg' )
		return( null );

	$src = $srcData;
	$cont = _Cp_GetScrCont( $ctxProcess, $src );

	$cont = @json_decode( ( string )$cont, true );
	if( !$cont )
		return( null );

	$item -> appendChild( HtmlNd::CreateTag( $doc, 'svg', array( 'xmlns' => 'http://www.w3.org/2000/svg', 'viewBox' => '0 0 ' . Gen::GetArrField( $cont, array( 'w' ), 0 ) . ' ' . Gen::GetArrField( $cont, array( 'h' ), 0 ) . '', 'width' => Gen::GetArrField( $cont, array( 'w' ), 0 ), 'height' => Gen::GetArrField( $cont, array( 'h' ), 0 ), 'class' => array( 'lottgen', 'js-lzl-ing' ), 'style' => 'width:100%;height:100%;transform:translate3d(0px,0px,0px);content-visibility:visible;' ), Gen::GetArrField( $settImg, array( 'lazy', 'load' ), false ) ? array( HtmlNd::CreateTag( $doc, 'image', array( 'href' => LazyLoad_SrcSubst( $ctxProcess, array( 'cx' => Gen::GetArrField( $cont, array( 'w' ), 0 ), 'cy' => Gen::GetArrField( $cont, array( 'h' ), 0 ) ), true ), 'width' => ( string )Gen::GetArrField( $cont, array( 'w' ), 0 ) . 'px', 'height' => ( string )Gen::GetArrField( $cont, array( 'h' ), 0 ) . 'px' ) ) ) : array() ) );

	$contAdjusted = false;
	foreach( Gen::GetArrField( $cont, array( 'assets' ), array() ) as $assetIdx => $asset )
	{
		$srcImg = Gen::GetArrField( $asset, array( 'p' ), '' );
		if( !$srcImg )
			continue;

		$imgSrc = new ImgSrc( $srcImg );

		$r = Images_ProcessSrc( $ctxProcess, $imgSrc, $settCache, $settImg, $settCdn );
		if( $r === false )
			return( false );

		if( !$r )
			continue;

		Gen::SetArrField( $cont, array( 'assets', $assetIdx, 'p' ), $imgSrc -> src );
		$contAdjusted = true;
	}

	if( $contAdjusted && ( $cont = @json_encode( $cont ) ) )
	{
		if( !UpdSc( $ctxProcess, $settCache, 'json', $cont, $src ) )
			return( false );

		$srcData = $src;
	}

	return( true );
}

function _ProcessCont_Cp_kdncThAni( $ctx, &$ctxProcess, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/body[contains(concat(" ",normalize-space(@class)," ")," theme-kadence ")]//*[@data-aos]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;
		$adjusted = true;
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.aos-animate@' ] = true;

		{

			$ctx -> aAniAppear[ '[data-aos]:not(.aos-animate)' ] = 'function(a){a.classList.add("aos-animate")}';
		}
	}
}

function _ProcessCont_Cp_fsnAni( $ctx, &$ctxProcess, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," fusion-animated ")][@data-animationtype]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$ctxProcess[ 'aCssCrit' ][ '@\\.' . $item -> getAttribute( 'data-animationtype' ) . '@' ] = true;

		$adjusted = true;
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.do-animate@' ] = true;

		{

			$ctx -> aAniAppear[ '.fusion-animated[data-animationtype]:not([style*=visibility])' ] = 'function(a){function b(){a.ownerDocument.body.classList.add("do-animate");a.classList.add(a.getAttribute("data-animationtype"));a.style.setProperty("animation-duration",a.getAttribute("data-animationduration")+"s");a.style.setProperty("visibility","visible")}a.classList.add("animated");var c=a.getAttribute("data-animationdelay");c?setTimeout(b,parseInt(c,10)):b()}';
		}
	}
}

function _ProcessCont_Cp_jetMobMenu( $ctx, &$ctxProcess, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," jet-mobile-menu ")][@data-menu-options]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$dataSett = @json_decode( $item -> getAttribute( 'data-menu-options' ), true );
		$itemToggleClosedIcon = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," jet-mobile-menu__refs ")]/*[@ref="toggleClosedIcon"]', $item ) );
		if( !$itemToggleClosedIcon )
			continue;

		$toggleText = Gen::GetArrField( $dataSett, array( 'toggleText' ), '' );

		$itemToggle = HtmlNd::Parse( '<div class="jet-mobile-menu__instance jet-mobile-menu__instance--' . Gen::GetArrField( $dataSett, array( 'menuLayout' ), '' ) . '-layout ' . Gen::GetArrField( $dataSett, array( 'menuPosition' ), '' ) . '-container-position ' . Gen::GetArrField( $dataSett, array( 'togglePosition' ), '' ) . '-toggle-position js-lzl-ing"><div tabindex="1" class="jet-mobile-menu__toggle"><div class="jet-mobile-menu__toggle-icon">' . HtmlNd::DeParse( $itemToggleClosedIcon, false ) . '</div>' . ( $toggleText ? '<span class="jet-mobile-menu__toggle-text">' . $toggleText . '</span>' : '' ) . '</div></div>' );
		if( $itemToggle && $itemToggle -> firstChild )
			if( $itemToggle = $doc -> importNode( $itemToggle -> firstChild, true ) )
			{
				$item -> insertBefore( $itemToggle, $item -> firstChild );
				$adjusted = true;
			}
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
	{
		$itemsCmnStyle = $doc -> createElement( 'style' );
		if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
			$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
		HtmlNd::SetValFromContent( $itemsCmnStyle, 'body:not(.seraph-accel-js-lzl-ing) .jet-mobile-menu__instance.js-lzl-ing{display:none!important;}body.seraph-accel-js-lzl-ing .jet-mobile-menu__instance:not(.js-lzl-ing){display:none!important;}' );
		$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
	}
}

function _ProcessCont_Cp_jetCrsl( $ctx, &$ctxProcess, $doc, $xpath )
{
	_ProcessCont_Cp__jetCrsl( $ctx, $ctxProcess, $doc, $xpath, array( 'item' => 'elementor-widget-jet-carousel', 'itemSlk' => 'elementor-slick-slider', 'itemSlkChld' => 'jet-carousel__item', 'settViewPrefix' => 'slides_to_show' ) );
}

function _ProcessCont_Cp_jetCrslPst( $ctx, &$ctxProcess, $doc, $xpath )
{
	_ProcessCont_Cp__jetCrsl( $ctx, $ctxProcess, $doc, $xpath, array( 'item' => 'elementor-widget-jet-posts', 'itemSlk' => 'jet-posts', 'itemSlkChld' => 'jet-posts__item', 'settViewPrefix' => 'columns' ) );
}

function _ProcessCont_Cp__jetCrsl( $ctx, &$ctxProcess, $doc, $xpath, array $aPrm )
{
	$adjusted = false;
	$idNext = 0;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," ' . $aPrm[ 'item' ] . ' ")][@data-settings]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$dataSett = @json_decode( $item -> getAttribute( 'data-settings' ), true );
		if( !$dataSett )
			continue;

		if( !( $itemJet = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," jet-carousel ")][@data-slider_options]', $item ) ) ) )
			continue;

		$dataSettJet = @json_decode( $itemJet -> getAttribute( 'data-slider_options' ), true );
		if( !$dataSettJet )
			continue;

		$sld = _SlickSld_PrepareCont( $ctx, $doc, $xpath, HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," ' . $aPrm[ 'itemSlk' ] . ' ")]', $itemJet ) ), $aPrm[ 'itemSlkChld' ], (isset($dataSettJet[ 'dots' ])?$dataSettJet[ 'dots' ]:null) );
		if( !$sld )
			continue;

		if( $ctx -> cfgElmntrFrontend === null )
			$ctx -> cfgElmntrFrontend = _Elmntr_GetFrontendCfg( $xpath );

		$classId = 'lzl-' . $idNext++;

		HtmlNd::AddRemoveAttrClass( $itemJet, array( $classId ) );

		$aViews = Gen::GetArrField( $ctx -> cfgElmntrFrontend, array( 'views' ), array() );

		$itemStyleCont = '';
		$maxWidthPrev = null;
		foreach( $aViews as $viewId => $view )
		{
			$nShow = ( int )Gen::GetArrField( $dataSett, array( $aPrm[ 'settViewPrefix' ] . ( $viewId == 'desktop' ? '' : ( '_' . $viewId ) ) ) );
			if( !$nShow )
				continue;

			$maxWidth = $view[ 'cxMax' ];
			if( $maxWidth == 2147483647 )
				$maxWidth = null;

			if( $maxWidthPrev || $maxWidth )
				$itemStyleCont .= '@media ' . ( $maxWidthPrev ? ( '(min-width: ' . ( $maxWidthPrev + 1 ) . 'px)' ) : '' ) . ( $maxWidthPrev && $maxWidth ? ' and ' : '' ) . ( $maxWidth ? ( '(max-width: ' . $maxWidth . 'px)' ) : '' ) . ' {' . "\n";

			$itemStyleCont .= '.jet-carousel.' . $classId . ' .' . $aPrm[ 'itemSlk' ] . ':not(.slick-initialized)' . ( $sld -> bSimpleCont ? '' : ' ' ) . '.lzl-c > * {width: calc(100% / ' . $nShow . ');}' . "\n";
			$itemStyleCont .= '.jet-carousel.' . $classId . ' .' . $aPrm[ 'itemSlk' ] . ':not(.slick-initialized)' . ( $sld -> bSimpleCont ? '' : ' ' ) . '.lzl-c > *:nth-child(n+' . ( $nShow + 1 ) . ') {visibility:hidden!important;}' . "\n";

			{
				$nDots = _SlickSld_GetDotsCount( array( 'slideCount' => $sld -> nSlides, 'slidesToShow' => $nShow, 'slidesToScroll' => ( int )(isset($dataSettJet[ 'slidesToScroll' ])?$dataSettJet[ 'slidesToScroll' ]:null), 'infinite' => ( bool )(isset($dataSettJet[ 'infinite' ])?$dataSettJet[ 'infinite' ]:null), 'centerMode' => false, 'asNavFor' => false ) );
				$itemStyleCont .= '.jet-carousel.' . $classId . ' .' . $aPrm[ 'itemSlk' ] . ':not(.slick-initialized) .jet-slick-dots' . ( $nDots ? ' > *:nth-child(n+' . ( $nDots + 1 ) . ')' : '' ) . ' {display:none;}' . "\n";
			}

			if( $maxWidthPrev || $maxWidth )
				$itemStyleCont .= '}' . "\n";

			if( $maxWidth )
				$maxWidthPrev = $maxWidth;
		}

		{
			$itemStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemStyle, $itemStyleCont );
			$item -> parentNode -> insertBefore( $itemStyle, $item );
		}

		if( (isset($dataSettJet[ 'dots' ])?$dataSettJet[ 'dots' ]:null) )
			_SlickSld_AddDots( $doc, $sld -> itemSlides, 'jet-slick-dots', $sld -> nSlides, function( $sld, $i ) { return( '<li tabindex="0"><span>' . ( $i + 1 ) . '</span></li>' ); } );

		$adjusted = true;
	}

	if( $adjusted && $ctxProcess[ 'mode' ] == 'full' )
	{
		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, _SlickSld_GetGlobStyle( '.jet-carousel .' . $aPrm[ 'itemSlk' ], $aPrm[ 'itemSlkChld' ] ) . '.jet-carousel .' . $aPrm[ 'itemSlk' ] . ':not(.slick-initialized) > *,
.jet-carousel .' . $aPrm[ 'itemSlk' ] . ':not(.slick-initialized) ~ .jet-arrow {
	visibility: visible !important;
}
.jet-carousel .' . $aPrm[ 'itemSlk' ] . ':not(.slick-initialized) > .jet-slick-dots {
	width: 100%;
}' );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}

		_SlickSld_InitGlob( $ctx, $ctxProcess, $doc, '.jet-carousel .' . $aPrm[ 'itemSlk' ] );
	}
}

function _ProcessCont_Cp_jetLott( $ctx, &$ctxProcess, $settCache, $settImg, $settCdn, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," jet-lottie ")][@data-settings]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$dataSett = @json_decode( $item -> getAttribute( 'data-settings' ), true );
		if( !$dataSett )
			continue;

		$dataFile = Gen::GetArrField( $dataSett, array( 'path' ), '' );
		if( !$dataFile )
			continue;

		$itemCont = HtmlNd::FirstOfChildren( $xpath -> query( './*[contains(concat(" ",normalize-space(@class)," ")," jet-lottie__elem ")]', $item ) );
		if( !$itemCont )
			continue;

		$r = _ProcessCont_Cp_lottGen_AdjustItem( $ctx, $ctxProcess, $settCache, $settImg, $settCdn, $doc, $xpath, $itemCont, Gen::GetArrField( $dataSett, array( 'renderer' ), '' ), $dataFile );
		if( $r === false )
			return( false );

		if( !$r )
			continue;

		{

			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			HtmlNd::SetValFromContent( $itemScript, str_replace( array( 'PRM_PATH', 'PRM_RENDERER', 'PRM_LOOP', 'PRM_AUTOPLAY' ), array( $dataFile, Gen::GetArrField( $dataSett, array( 'renderer' ), '' ), Gen::GetArrField( $dataSett, array( 'loop' ) ) ? 'true' : 'false', Gen::GetArrField( $dataSett, array( 'action_start' ), '' ) == 'autoplay' ? 'true' : 'false' ), "bodymovin.loadAnimation({container:document.currentScript.parentNode,path:\"PRM_PATH\",renderer:\"PRM_RENDERER\",loop:PRM_LOOP,autoplay:PRM_AUTOPLAY})" ) );
			HtmlNd::InsertAfter( $itemCont, $itemScript, null, true );
		}

		Gen::UnsetArrField( $dataSett, array( 'path' ) );
		$item -> setAttribute( 'data-settings', @json_encode( $dataSett ) );

		$adjusted = true;
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@svg\\.lottgen@' ] = true;

		$ctxProcess[ 'aJsCritSpec' ][ 'id:@^jet-lottie-js$@' ] = true;
		$ctxProcess[ 'aJsCritSpec' ][ 'body:@bodymovin\\.loadAnimation\\(\\s*{\\s*container\\s*:\\s*document\\.currentScript\\.parentNode\\W@' ] = true;

		if( $itemScr = HtmlNd::FirstOfChildren( $xpath -> query( './/script[@id="jet-lottie-js"]' ) ) )
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemScr );

		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, 'svg.lottgen.js-lzl-ing:has(+ svg) {
	display: none!important;
}' );

			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}
	}
}

function _ProcessCont_Cp_ultRspnsv( $ctx, &$ctxProcess, $doc, $xpath )
{

	$aView = array( 'large_screen' => 'min-width:1824px', 'tablet' => 'max-width:1199px', 'tablet_portrait' => 'max-width:991px', 'mobile_landscape' => 'max-width:767px', 'mobile' => 'max-width:479px' );
	$aSpacerToViewId = array( 'mobile' => 'mobile', 'mobile-landscape' => 'mobile_landscape', 'tab' => 'tablet', 'tab-portrait' => 'tablet_portrait' );

	$aCss = array( '' => array() );
	foreach( $aView as $viewId => $spec )
		$aCss[ $viewId ] = array();

	$adjusted = false;

	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," ult-responsive ")]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$data = $item -> getAttribute( 'data-responsive-json-new' );
		$cssSelTarget = $item -> getAttribute( 'data-ultimate-target' );
		if( !$data || !$cssSelTarget )
			continue;

		$adjusted = true;
		HtmlNd::AddRemoveAttrClass( $item, array(), array( 'ult-responsive' ) );
		$item -> removeAttribute( 'data-responsive-json-new' );
		$item -> removeAttribute( 'data-ultimate-target' );

		if( $ctxProcess[ 'mode' ] == 'full' )
		{
			foreach( ( array )@json_decode( $data, true ) as $ruleName => $ruleData )
				foreach( Gen::ParseProps( $ruleData, ';', ':' ) as $viewId => $ruleVal )
				{
					if( !isset( $aView[ $viewId ] ) )
						$viewId = '';
					$aCss[ $viewId ][ $cssSelTarget ][ $ruleName ] = $ruleVal;
				}
		}
	}

	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," ult-spacer ")]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$adjusted = true;

		$cssSelTarget = '.spacer-' . ( string )$item -> getAttribute( 'data-id' );
		HtmlNd::AddRemoveAttrClass( $item, array(), array( 'ult-spacer' ) );

		$aAttrDel = array();
		if( $item -> attributes )
			foreach( $item -> attributes as $attr )
			{
				if( Gen::StrStartsWith( $attr -> nodeName, 'data-height' ) )
				{
					if( $ctxProcess[ 'mode' ] == 'full' )
					{
						$viewId = ltrim( substr( $attr -> nodeName, 11 ), '-' );
						$viewId = isset( $aSpacerToViewId[ $viewId ] ) ? $aSpacerToViewId[ $viewId ] : '';
						$aCss[ $viewId ][ $cssSelTarget ][ 'height' ] = ( string )$attr -> nodeValue . 'px';
					}

					$aAttrDel[] = $attr -> nodeName;
				}
			}

		foreach( $aAttrDel as $attrDel )
			$item -> removeAttribute( $attrDel );
	}

	if( $ctxProcess[ 'mode' ] != 'full' )
		return;

	if( !$adjusted )
		return;

	$cont = '';
	foreach( $aCss as $viewId => $aCssSel )
	{
		if( isset( $aView[ $viewId ] ) )
			$cont .= '@media (' . $aView[ $viewId ] . ') {';
		$cont .= Ui::GetStyleSels( $aCssSel );
		if( isset( $aView[ $viewId ] ) )
			$cont .= '}';
	}

	$itemCmnStyle = $doc -> createElement( 'style' );
	if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
		$itemCmnStyle -> setAttribute( 'type', 'text/css' );
	HtmlNd::SetValFromContent( $itemCmnStyle, $cont );
	$ctxProcess[ 'ndHead' ] -> appendChild( $itemCmnStyle );
}

function _ProcessCont_Cp_ultVcHd( $ctx, &$ctxProcess, $doc, $xpath )
{

	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," uvc-heading ")]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$ctxProcess[ 'isRtl' ];

		$spacer = $item -> getAttribute( 'data-hspacer' );
		$line_width = $item -> getAttribute( 'data-hline_width' );
		$icon_type = $item -> getAttribute( 'data-hicon_type' );
		$align = $item -> getAttribute( 'data-halign' );

		if( $spacer == 'line_with_icon' )
		{

		}
		else if( $spacer == 'line_only' )
		{
			if( $itemSub = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," uvc-heading-spacer ")]//*[contains(concat(" ",normalize-space(@class)," ")," uvc-headings-line ")]', $item ) ) )
			{
				if( $align == 'left' || $align == 'right' )
					$itemSub -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemSub -> getAttribute( 'style' ) ), array( 'float' => $align ) ) ) );
				else
					$itemSub -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemSub -> getAttribute( 'style' ) ), array( 'margin' => '0 auto' ) ) ) );
			}
		}
	}
}

function _ProcessCont_Cp_ultAni( $ctx, &$ctxProcess, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," ult-animation ")][contains(concat(" ",normalize-space(@class)," ")," ult-animate-viewport ")]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$ctxProcess[ 'aCssCrit' ][ '@\\.' . $item -> getAttribute( 'data-animate' ) . '@' ] = true;

		if( $item -> hasAttribute( 'data-animation-delay' ) )
			$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( 'transition-delay' => $item -> getAttribute( 'data-animation-delay' ) . 's' ) ) ) );

		for( $itemChild = HtmlNd::GetFirstElement( $item ); $itemChild; $itemChild = HtmlNd::GetNextElementSibling( $itemChild ) )
		{
			$aStyle = Ui::ParseStyleAttr( $itemChild -> getAttribute( 'style' ) );

			if( $item -> hasAttribute( 'data-animation-delay' ) )
				$aStyle[ 'animation-delay' ] = $item -> getAttribute( 'data-animation-delay' ) . 's';
			if( $item -> hasAttribute( 'data-animation-duration' ) )
				$aStyle[ 'animation-duration' ] = $item -> getAttribute( 'data-animation-duration' ) . 's';
			if( $item -> hasAttribute( 'data-animation-iteration' ) )
				$aStyle[ 'animation-iteration-count' ] = $item -> getAttribute( 'data-animation-iteration' );

			$itemChild -> setAttribute( 'style', Ui::GetStyleAttr( $aStyle ) );
		}

		$adjusted = true;
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
	{
		{
			$ctxProcess[ 'aCssCrit' ][ '@\\.animated@' ] = true;
			$ctxProcess[ 'aCssCrit' ][ '@\\.char@' ] = true;
		}

		$ctx -> aAniAppear[ '.ult-animation.ult-animate-viewport>*:not(.animated)' ] = "function(a){var b=a.parentNode,c=b.getAttribute(\"data-animate\");-1===c.indexOf(\" \")&&a.classList.add(c);a.classList.add(\"animated\");b.style.setProperty(\"opacity\",\"1\")}";
	}
}

function _ProcessCont_Cp_esntlsThAni( $ctx, &$ctxProcess, $doc, $xpath )
{
	if( $ctxProcess[ 'mode' ] != 'full' )
		return;

	if( !HtmlNd::FirstOfChildren( $xpath -> query( './/script[@id="pix-main-essentials-js"]' ) ) )
		return;

	{
		$adjusted = false;
		foreach( $xpath -> query( './/*[@data-anim-type][contains(concat(" ",normalize-space(@class)," ")," animate-in ")]' ) as $item )
		{
			$ctxProcess[ 'aCssCrit' ][ '@\\.' . $item -> getAttribute( 'data-anim-type' ) . '@' ] = true;
			$adjusted = true;
		}

		if( $adjusted )
		{
			$ctxProcess[ 'aCssCrit' ][ '@\\.animating@' ] = true;
			$ctxProcess[ 'aCssCrit' ][ '@\\.animated@' ] = true;
			$ctxProcess[ 'aCssCrit' ][ '@\\.pix-animate@' ] = true;

			$ctx -> aAniAppear[ '[data-anim-type].animate-in' ] = "function(a,c){a.classList.remove(\"animate-in\");setTimeout(function(){a.classList.add(a.getAttribute(\"data-anim-type\"));a.classList.add(\"animating\");a.classList.add(\"pix-animate\");var b=getComputedStyle(a);setTimeout(function(){a.classList.remove(\"animating\");a.classList.add(\"animated\")},c.GetDurationTime(b.getPropertyValue(\"animation-delay\")+\",\"+b.getPropertyValue(\"transition-delay\"),\"max\")+c.GetDurationTime(b.getPropertyValue(\"animation-duration\")+\",\"+b.getPropertyValue(\"transition-duration\"),\n\"max\"))},parseInt(a.getAttribute(\"data-anim-delay\"),10))}";
		}
	}

	if( HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," pix-intro-img ")]' ) ) )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.animated@' ] = true;

		$ctx -> aAniAppear[ '.pix-intro-img:not(.animated)' ] = "function(a){a.classList.add(\"animated\")}";
	}
}

function _ProcessCont_Cp_beThAni( $ctx, &$ctxProcess, $doc, $xpath )
{
	if( $ctxProcess[ 'mode' ] != 'full' )
		return;

	if( !HtmlNd::FirstOfChildren( $xpath -> query( './/script[@id="mfn-animations-js"]' ) ) )
		return;

	{
		$adjusted = false;
		foreach( $xpath -> query( './/*[@data-anim-type][contains(concat(" ",normalize-space(@class)," ")," animate ")]' ) as $item )
		{
			$ctxProcess[ 'aCssCrit' ][ '@\\.' . $item -> getAttribute( 'data-anim-type' ) . '@' ] = true;
			$adjusted = true;
		}

		if( $adjusted )
		{

			$ctx -> aAniAppear[ '[data-anim-type].animate:not(.lzl-ad)' ] = "function(a,b){a.classList.add(\"lzl-ad\");setTimeout(function(){a.classList.add(a.getAttribute(\"data-anim-type\"))},parseInt(a.getAttribute(\"data-anim-delay\"),10))}";
		}
	}
}

function _ProcessCont_Cp_prstPlr( $ctx, &$ctxProcess, $doc, $xpath )
{
	$adjusted = false;
	foreach( $xpath -> query( './/presto-player' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$aCfg = _PrstPlr_GetPrmsFromScr( HtmlNd::FirstOfChildren( $xpath -> query( './/script[contains(text(),".querySelector")]', $item -> parentNode -> parentNode ) ) );

		$itemOverlay = HtmlNd::ParseAndImport( $doc, '<div class="presto-player__muted-overlay js-lzl-ing" style="display:none">' . ( Gen::GetArrField( $aCfg, array( 'blockAttributes', 'mutedPreview', 'enabled' ), false ) ? '' : '<div class="plyr__controls"><button class="plyr__controls__item plyr__control" type="button" data-plyr="play" aria-pressed="false"><svg class="icon--not-pressed" aria-hidden="true" focusable="false"><use xlink:href="wp-content/plugins/presto-player/img/default.svg#plyr-play"></use></svg></button><div class="plyr__controls__item plyr__progress__container"><div class="plyr__progress"><input data-plyr="seek" type="range" min="0" max="100" step="0.01" value="0" autocomplete="off" role="slider" aria-label="Seek" aria-valuemin="0" aria-valuemax="281" aria-valuenow="0" aria-valuetext="00:00 of 00:00" seek-value="00.0" style="--value: 0%;"></div></div><div class="plyr__controls__item plyr__time--current plyr__time" aria-label="Current time">00:00</div><div class="plyr__controls__item plyr__volume"><button type="button" class="plyr__control" data-plyr="mute" aria-pressed="false"><svg class="icon--not-pressed" aria-hidden="true" focusable="false"><use xlink:href="wp-content/plugins/presto-player/img/default.svg#plyr-volume"></use></svg></button><input data-plyr="volume" type="range" min="0" max="1" step="0.05" value="1" autocomplete="off" role="slider" aria-label="Volume" aria-valuemin="0" aria-valuemax="100" aria-valuenow="100" aria-valuetext="100.0%" style="--value: 100%;"></div></div>' ) . '<div class="plyr__control plyr__control--overlaid"><svg id="plyr-play" viewBox="0 0 18 18"><path d="M15.562 8.1L3.87.225c-.818-.562-1.87 0-1.87.9v15.75c0 .9 1.052 1.462 1.87.9L15.563 9.9c.584-.45.584-1.35 0-1.8z"></path></svg></div></div>' );
		$item -> insertBefore( $itemOverlay, $item -> firstChild );

		if( $urlVideoThumb = GetVideoThumbUrlFromUrl( $ctxProcess, $item -> getAttribute( 'src' ) ) )
			$itemOverlay -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemOverlay -> getAttribute( 'style' ) ), array( 'background' => 'center / cover no-repeat url(' . $urlVideoThumb . ')!important' ) ) ) );

		$adjusted = true;
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
	{
		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, "body.seraph-accel-js-lzl-ing presto-player .presto-player__muted-overlay.js-lzl-ing {\r\n\tdisplay: block !important;\r\n}\r\n\r\npresto-player .presto-player__muted-overlay.js-lzl-ing {\r\n\tposition: absolute;\r\n\twidth: 100%;\r\n\theight: 100%;\r\n\tcursor: pointer;\r\n\tz-index: 2;\r\n\tvisibility: visible;\r\n}\r\n\r\npresto-player .presto-player__muted-overlay.js-lzl-ing .plyr__control--overlaid {\r\n\tdisplay: block;\r\n\topacity: 1;\r\n\tvisibility: visible;\r\n\tleft: 50%;\r\n\ttransform: translate(-50%, -50%);\r\n\ttransition: all 0.3s ease 0s;\r\n\tposition: absolute !important;\r\n\ttop: 50% !important;\r\n}\r\n\r\npresto-player .presto-player__muted-overlay.js-lzl-ing .plyr__control--overlaid svg {\r\n\tposition: relative;\r\n\tdisplay: block;\r\n\tpointer-events: none;\r\n}\r\n\r\npresto-player[skin=\"modern\"] .presto-player__muted-overlay.js-lzl-ing .plyr__control--overlaid {\r\n\tbackground: var(--plyr-video-control-background-hover, var(--plyr-color-main, var(--plyr-color-main, #00b3ff)));\r\n\tborder: 0px;\r\n\tcolor: var(--plyr-video-control-color, #fff);\r\n\tpadding: calc(var(--plyr-control-spacing, 10px) * 1.5);\r\n\tborder-radius: 2px;\r\n\tpadding-left: 26px;\r\n\tpadding-right: 26px;\r\n}\r\n\r\npresto-player[skin=\"modern\"] .presto-player__muted-overlay.js-lzl-ing .plyr__control--overlaid svg {\r\n\theight: 40px;\r\n\twidth: 50px;\r\n\tleft: 2px;\r\n\tfill: currentcolor;\r\n}\r\n\r\npresto-player[skin=\"default\"] .presto-player__muted-overlay.js-lzl-ing .plyr__control--overlaid {\r\n\tbackground: #d92929;\r\n\tborder: 0px;\r\n\tcolor: var(--plyr-video-control-color, #fff);\r\n\tpadding: calc(var(--plyr-control-spacing, 10px) * 1.5);\r\n\tborder-radius: 6px;\r\n\tpadding-left: 26px;\r\n\tpadding-right: 26px;\r\n}\r\n\r\npresto-player[skin=\"default\"] .presto-player__muted-overlay.js-lzl-ing .plyr__control--overlaid svg {\r\n\theight: 18px;\r\n\twidth: 18px;\r\n\tleft: 2px;\r\n\tfill: currentcolor;\r\n}\r\n\r\n/* Play controls */\r\n.presto-player__muted-overlay.js-lzl-ing {\r\n\t-moz-osx-font-smoothing: grayscale;\r\n\t-webkit-font-smoothing: antialiased;\r\n\talign-items: center;\r\n\tdirection: ltr;\r\n\tdisplay: flex;\r\n\tflex-direction: column;\r\n\tfont-family: var(--plyr-font-family, inherit);\r\n\tfont-variant-numeric: tabular-nums;\r\n\tfont-weight: var(--plyr-font-weight-regular, 400);\r\n\tline-height: var(--plyr-line-height, 1.7);\r\n\tmax-width: 100%;\r\n\tmin-width: 200px;\r\n\tposition: relative;\r\n\ttext-shadow: none;\r\n\ttransition: box-shadow 0.3s ease;\r\n\tz-index: 0;\r\n}\r\n\r\n.presto-player__muted-overlay.js-lzl-ing {\r\n\tflex: 1;\r\n\tjustify-content: center;\r\n\tfont-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif, \"Apple Color Emoji\", \"Segoe UI Emoji\", \"Segoe UI Symbol\";\r\n\twidth: 100%;\r\n}\r\n\r\n.presto-player__muted-overlay.js-lzl-ing .plyr__controls {\r\n\tbackground: var(--plyr-video-controls-background, linear-gradient(rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.75)));\r\n\tborder-bottom-left-radius: inherit;\r\n\tborder-bottom-right-radius: inherit;\r\n\tbottom: 0;\r\n\tcolor: var(--plyr-video-control-color, #fff);\r\n\tleft: 0;\r\n\tpadding: calc(var(--plyr-control-spacing, 10px) / 2);\r\n\tpadding-top: calc(var(--plyr-control-spacing, 10px)* 2);\r\n\tposition: absolute;\r\n\tright: 0;\r\n\ttransition: opacity 0.4s ease-in-out, transform 0.4s ease-in-out;\r\n\tz-index: 3;\r\n}\r\n\r\n.presto-player__muted-overlay.js-lzl-ing .plyr__controls {\r\n\tbackground: var(--plyr-video-controls-background, linear-gradient(rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.15), rgba(0, 0, 0, 0.25), rgba(0, 0, 0, 0.75)));\r\n}\r\n\r\n@media (min-width: 480px) {\r\n\t.presto-player__muted-overlay.js-lzl-ing .plyr__controls {\r\n\t\tpadding: var(--plyr-control-spacing, 10px);\r\n\t\tpadding-top: calc(var(--plyr-control-spacing, 10px)* 3.5);\r\n\t}\r\n}\r\n\r\n.presto-player__muted-overlay.js-lzl-ing .plyr__controls {\r\n\talign-items: center;\r\n\tdisplay: flex;\r\n\tjustify-content: flex-end;\r\n\ttext-align: center;\r\n}\r\n\r\n.presto-player__muted-overlay.js-lzl-ing .plyr__controls .plyr__controls__item:first-child {\r\n\tmargin-left: 0;\r\n\tmargin-right: auto;\r\n}\r\n\r\n.presto-player__muted-overlay.js-lzl-ing .plyr__controls .plyr__controls__item {\r\n\tmargin-left: calc(var(--plyr-control-spacing, 10px) / 4);\r\n}\r\n\r\n.presto-player__muted-overlay.js-lzl-ing .plyr__controls button {\r\n\tfont: inherit;\r\n\tline-height: inherit;\r\n\twidth: auto;\r\n}\r\n\r\n.presto-player__muted-overlay.js-lzl-ing .plyr__control {\r\n\tbackground: transparent;\r\n\tborder: 0;\r\n\tborder-radius: var(--plyr-control-radius, 3px);\r\n\tcolor: inherit;\r\n\tcursor: pointer;\r\n\tflex-shrink: 0;\r\n\toverflow: visible;\r\n\tpadding: calc(var(--plyr-control-spacing, 10px)* 0.7);\r\n\tposition: relative;\r\n\ttransition: all 0.3s ease;\r\n}\r\n\r\n.presto-player__muted-overlay.js-lzl-ing .plyr__control svg {\r\n\tdisplay: block;\r\n\tfill: currentColor;\r\n\theight: var(--plyr-control-icon-size, 18px);\r\n\tpointer-events: none;\r\n\twidth: var(--plyr-control-icon-size, 18px);\r\n}\r\n\r\n.presto-player__muted-overlay.js-lzl-ing .plyr__controls__item.plyr__progress__container {\r\n\tposition: relative;\r\n}\r\n\r\n.presto-player__muted-overlay.js-lzl-ing .plyr__controls .plyr__controls__item.plyr__progress__container {\r\n\tpadding-left: calc(var(--plyr-control-spacing, 10px) / 4);\r\n}\r\n\r\n.presto-player__muted-overlay.js-lzl-ing .plyr__controls .plyr__controls__item {\r\n\tmargin-left: calc(var(--plyr-control-spacing, 10px) / 4);\r\n}\r\n\r\n.presto-player__muted-overlay.js-lzl-ing .plyr__controls .plyr__progress__container {\r\n\tflex: 1;\r\n\tmin-width: 0;\r\n}\r\n\r\n.presto-player__muted-overlay.js-lzl-ing .plyr__progress {\r\n\tleft: calc(var(--plyr-range-thumb-height, 13px)* 0.5);\r\n\tmargin-right: var(--plyr-range-thumb-height, 13px);\r\n\tposition: relative;\r\n}\r\n\r\n.presto-player__muted-overlay.js-lzl-ing .plyr__progress input[type=range] {\r\n\tposition: relative;\r\n\tz-index: 2;\r\n}\r\n\r\n.presto-player__muted-overlay.js-lzl-ing .plyr__controls input[type=range] {\r\n\tappearance: none;\r\n\tbackground: transparent;\r\n\tborder: 0;\r\n\tborder-radius: calc(var(--plyr-range-thumb-height, 13px)* 2);\r\n\tcolor: var(--plyr-range-fill-background, var(--plyr-color-main, var(--plyr-color-main, #00b3ff)));\r\n\tdisplay: block;\r\n\theight: calc((var(--plyr-range-thumb-active-shadow-width, 3px)* 2) + var(--plyr-range-thumb-height, 13px));\r\n\tmargin: 0;\r\n\tmin-width: 0;\r\n\tpadding: 0;\r\n\ttransition: box-shadow 0.3s ease;\r\n\twidth: 100%;\r\n}\r\n\r\n.presto-player__muted-overlay.js-lzl-ing .plyr__progress input[type=range] {\r\n\tmargin-left: calc(var(--plyr-range-thumb-height, 13px)* -0.5);\r\n\tmargin-right: calc(var(--plyr-range-thumb-height, 13px)* -0.5);\r\n\twidth: calc(100% + var(--plyr-range-thumb-height, 13px));\r\n}\r\n\r\n.presto-player__muted-overlay.js-lzl-ing .plyr__progress input[type=\"range\"]::-webkit-slider-runnable-track, .presto-player__muted-overlay.js-lzl-ing .plyr__volume input[type=\"range\"]::-webkit-slider-runnable-track {\r\n\theight: var(--plyr-range-track-height, 5px);\r\n\tbackground: var(--plyr-video-range-track-background, var(--plyr-video-progress-buffered-background, rgba(255, 255, 255, 0.25)));\r\n}\r\n\r\n.presto-player__muted-overlay.js-lzl-ing .plyr__controls input[type=\"range\"]::-webkit-slider-thumb {\r\n\tbackground: var(--plyr-range-thumb-background, #fff);\r\n\tborder: 0;\r\n\tborder-radius: 100%;\r\n\tbox-shadow: var(--plyr-range-thumb-shadow, 0 1px 1px rgba(35, 40, 47, 0.15), 0 0 0 1px rgba(35, 40, 47, 0.2));\r\n\theight: var(--plyr-range-thumb-height, 13px);\r\n\tposition: relative;\r\n\ttransition: all 0.2s ease;\r\n\twidth: var(--plyr-range-thumb-height, 13px);\r\n\tappearance: none;\r\n\tmargin-top: calc((var(--plyr-range-thumb-height, 13px) - var(--plyr-range-track-height, 5px)) / 2 * -1);\r\n}\r\n\r\n.presto-player__muted-overlay.js-lzl-ing .plyr__controls .plyr__controls__item.plyr__time {\r\n\tpadding: 0 calc(var(--plyr-control-spacing, 10px) / 2);\r\n}\r\n\r\n.presto-player__muted-overlay.js-lzl-ing .plyr__controls .plyr__controls__item {\r\n\tmargin-left: calc(var(--plyr-control-spacing, 10px) / 4);\r\n}\r\n\r\n.presto-player__muted-overlay.js-lzl-ing .plyr__controls .plyr__time {\r\n\tfont-size: var(--plyr-font-size-time, var(--plyr-font-size-small, 13px));\r\n}\r\n\r\n.presto-player__muted-overlay.js-lzl-ing .plyr__volume {\r\n\talign-items: center;\r\n\tdisplay: flex;\r\n\tmax-width: 110px;\r\n\tmin-width: 80px;\r\n\tposition: relative;\r\n\twidth: 20%;\r\n}\r\n\r\n.presto-player__muted-overlay.js-lzl-ing .plyr__volume input[type=range] {\r\n\tmargin-left: calc(var(--plyr-control-spacing, 10px) / 2);\r\n\tmargin-right: calc(var(--plyr-control-spacing, 10px) / 2);\r\n\tposition: relative;\r\n\tz-index: 2;\r\n}" );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}
	}
}

function _PrstPlr_GetPrmsFromScr( $itemInitScr )
{
	if( !$itemInitScr )
		return( array() );

	$prms = array();

	if( preg_match( '@player\\s*\\.\\s*blockAttributes\\s*=\\s*@s', $itemInitScr -> nodeValue, $posStart, PREG_OFFSET_CAPTURE ) )
	{
		$posStart = $posStart[ 0 ][ 1 ] + strlen( $posStart[ 0 ][ 0 ] );
		$pos = Gen::JsonGetEndPos( $posStart, $itemInitScr -> nodeValue );
		if( $pos === null )
			return( null );

		$prms[ 'blockAttributes' ] = @json_decode( Gen::JsObjDecl2Json( substr( $itemInitScr -> nodeValue, $posStart, $pos - $posStart ) ), true );
	}

	return( $prms );
}

function _ProcessCont_Cp_wooPrdGallSld( $ctx, &$ctxProcess, $doc, $xpath )
{
	foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," woocommerce-product-gallery ")]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

	}
}

function _ProcessCont_Cp_grnshftPbAosOnceAni( $ctx, &$ctxProcess, $doc, $xpath )
{
	$adjusted = false;

	foreach( $xpath -> query( './/*[@data-aos-once]' ) as $item )
	{
		if( !ContentProcess_IsItemInFragments( $ctxProcess, $item ) )
			continue;

		$adjusted = true;
	}

	if( $ctxProcess[ 'mode' ] == 'full' && $adjusted )
	{
		$ctxProcess[ 'aCssCrit' ][ '@\\.aos-animate@' ] = true;

		$ctx -> aAniAppear[ '[data-aos-once]:not(.aos-animate)' ] = 'function(a){a.classList.add("aos-animate")}';
	}
}

