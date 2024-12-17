<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

function _GetInlineContentSignature( $type, array $prefsuff, $id )
{
	return( $prefsuff[ 0 ] . '{{{{' . $type . '-' . $id . '}}}}' . $prefsuff[ 1 ] );
}

function _AddInline( array &$inlines, $content, $type, array $prefsuff, &$idNext, $encodeUrl = false )
{
	$inlineTypeItemsBlock = &$inlines[ $type ];
	if( !$inlineTypeItemsBlock )
		$inlineTypeItemsBlock = array( 'prefsuff' => $prefsuff, 'items' => array(), 'encodeUrl' => $encodeUrl );

	$inlineTypeItems = &$inlineTypeItemsBlock[ 'items' ];

	$res = _GetInlineContentSignature( $type, $prefsuff, $idNext );
	$inlineTypeItems[ $idNext ] = $content;

	$idNext ++;
	return( $res );
}

function _ApplyInlinesBack( &$data, array &$inlines )
{
	foreach( array_reverse( $inlines, true ) as $inlineType => $inlineTypeItems )
	{
		$prefsuff = $inlineTypeItems[ 'prefsuff' ];
		$encodeUrl = $inlineTypeItems[ 'encodeUrl' ];
		$inlineTypeItems = $inlineTypeItems[ 'items' ];

		foreach( $inlineTypeItems as $inlineTypeItemId => $inlineTypeItem )
		{

			$itemSignature = _GetInlineContentSignature( $inlineType, $prefsuff, $inlineTypeItemId );
			$data = str_replace( $encodeUrl ? array( $itemSignature, rawurlencode( $itemSignature ) ) : $itemSignature, $inlineTypeItem, $data );
		}
	}

	$inlines = array();
}

function _UnMaskTag( $tag )
{
	if( strpos( $tag, 'm_' ) !== 0 )
		return( null );
	return( substr( $tag, 2 ) );
}

function _MaskTag( array &$list, $tag, $type, $accurate = false )
{
	if( is_array( $tag ) )
	{
		foreach( $tag as $tagE )
			_MaskTag( $list, $tagE, $type, $accurate );
		return;
	}

	$tagR = 'm_' . $tag;

	$listR = &$list[ 'replace' ];

	$listR[ '<' . $tag . '>' ] = '<' . $tagR . '>';
	$listR[ '<' . $tag . ' ' ] = '<' . $tagR . ' ';
	$listR[ '<' . $tag . "\n" ] = '<' . $tagR . "\n";

	if( $accurate )
	{
		$listR[ '<' . $tag . "\t" ] = '<' . $tagR . "\t";
		$listR[ '<' . $tag . "\r" ] = '<' . $tagR . "\r";
		$listR[ '<' . $tag . "\v" ] = '<' . $tagR . "\v";
		$listR[ '<' . $tag . "\f" ] = '<' . $tagR . "\f";
	}

	$listR[ '</' . $tag . '>' ] = '</' . $tagR . '>';
	$listR[ '<\\/' . $tag . '>' ] = '<\\/' . $tagR . '>';

	$list[ 'types' ][ $type ] .= $tagR . ',';
}

function _NormalizeScriptData( $tag, $data, $encoding, $correctEncoding = true, $correctCdata = false )
{

	if( $correctEncoding )
	{
		$scriptCharset = function_exists( 'mb_detect_encoding' ) ? strtoupper( mb_detect_encoding( $data, 'UTF-8,ISO-8859-1,WINDOWS-1252,ISO-8859-15,AUTO' ) ) : null;
		if( $scriptCharset && $scriptCharset != $encoding && function_exists( 'mb_convert_encoding' ) )
		{
			$data = mb_convert_encoding( $data, $encoding, $scriptCharset );

		}
	}

	if( $correctCdata )
		$data = preg_replace( array( '/^\\s*<!\\[CDATA\\[\\s*$/m', '/^\\s*\\]\\]>\\s*$/m' ), array( '/*<![CDATA[*/', '/*]]>*/' ), $data );

	if( $tag == 'script' )
		return( str_replace( '</', '{{{{ESC_TAG}}}}<\\/', $data ) );
	return( str_replace( '</', '<\\/', $data ) );
}

function _GetTagMaxRange( $tag, $data, $offset = 0 )
{
	$posBody = Ui::TagBeginGetPos( $tag, $data, $offset );
	if( $posBody === false )
		return( false );

	$posBody = $posBody[ 1 ];

	$posBodyEndLast = false;
	for( $offset = $posBody; ; )
	{
		$posBodyEnd = Ui::TagEndGetPos( $tag, $data, $offset );
		if( !$posBodyEnd )
			break;

		$posBodyEndLast = $posBodyEnd[ 0 ];
		$offset = $posBodyEnd[ 1 ];
	}

	if( $posBodyEndLast === false )
		return( false );

	return( array( $posBody, $posBodyEndLast ) );
}

function _HtmlProcess_TagsNormalize( $data, $tagParent, $min = false, &$contNotAllowed = null )
{
	$ctx = new TagsNormalizer( array( 'mask_unk_tag' => 'seraph-accel-masked', 'minimize' => $min ), $tagParent );
	return( $ctx -> process( $data, $contNotAllowed ) );
}

function _AddInline_StripScripts( &$inlines, &$data, &$idNext, $cb )
{
	$tag = array( 'script', 'SCRIPT' );

	$pos = 0;
	for( ;; )
	{
		$pos = Ui::TagBeginGetPos( $tag, $data, $pos, true );
		if( !$pos )
			break;

		$attrs = TagsNormalizer::ihjjquihqf( substr( $data, $pos[ 0 ] + 7, $pos[ 1 ] - ( $pos[ 0 ] + 7 + 1 ) ) );

		$pos = $pos[ 1 ];

		$posEnd = Ui::TagEndGetPos( $tag, $data, $pos, true );
		$posEnd = $posEnd ? $posEnd[ 0 ] : strlen( $data );

		$cont = substr( $data, $pos, $posEnd - $pos );

		$addEnding = false;
		if( $posInsideSecond = Ui::TagBeginGetPos( $tag, $cont, 0, true ) )
		{
			$posInsideSecond = $posInsideSecond[ 0 ];

			if( !preg_match( '@(?:/\*|\*/|\'|"|`)@', substr( $cont, 0, $posInsideSecond ) ) )
			{
				$addEnding = true;

				$posEnd = $pos + $posInsideSecond;
				$cont = substr( $data, $pos, $posEnd - $pos );
			}
		}

		$contNew = _AddInline( $inlines, call_user_func_array( $cb, array( $cont, $attrs ) ), 'SCRIPT', array( '/*', '*/' ), $idNext );
		$data = substr( $data, 0, $pos ) . $contNew . ( $addEnding ? '</script>' : '' ) . substr( $data, $posEnd );
		$pos += strlen( $contNew ) + 9;
	}
}

function _AddInline_StripHtmlComments( &$inlines, &$data, &$idNext, $irregular = true )
{
	$data = str_replace( '--!>', '-->', $data );

	$pos = 0;
	for( ;; )
	{
		$pos = strpos( $data, '<!--', $pos );
		if( $pos === false )
			break;

		$pos += 4;

		$posEnd = strpos( $data, '-->', $pos );
		if( $posEnd === false )
			$posEnd = strlen( $data );

		$cont = substr( $data, $pos, $posEnd - $pos );

		$contNew = _AddInline( $inlines, $cont, 'COMMENT', array( '', '' ), $idNext );

		$data = substr( $data, 0, $pos ) . $contNew . substr( $data, $posEnd );
		$pos += strlen( $contNew ) + 3;
	}

	if( $irregular )
	{
		$pos = 0;
		for( ;; )
		{
			$pos = strpos( $data, "<!\xE2\x80\x94", $pos );
			if( $pos === false )
				break;

			$posEnd = strpos( $data, "\xE2\x80\x94>", $pos + 5 );
			if( $posEnd === false )
				$posEnd = strlen( $data );
			else
				$posEnd += 4;

			$cont = substr( $data, $pos + 5, $posEnd - 4 - ( $pos + 5 ) );

			$contNew = '<!--' . _AddInline( $inlines, $cont, 'COMMENT-I', array( '', '' ), $idNext ) . '-->';

			$data = substr( $data, 0, $pos ) . $contNew . substr( $data, $posEnd );
			$pos += strlen( $contNew );
		}
	}
}

function _AddInline_StripXmps( &$inlines, &$data, &$idNext, $cb )
{
	$tag = array( 'xmp', 'XMP' );

	$pos = 0;
	for( ;; )
	{
		$pos = Ui::TagBeginGetPos( $tag, $data, $pos );
		if( !$pos )
			break;

		$pos = $pos[ 1 ];

		$posEnd = Ui::TagEndGetPos( $tag, $data, $pos );
		$posEnd = $posEnd ? $posEnd[ 0 ] : strlen( $data );

		$cont = substr( $data, $pos, $posEnd - $pos );
		$contNew = _AddInline( $inlines, call_user_func_array( $cb, array( $cont ) ), 'XMP', array( '', '' ), $idNext );

		$data = substr_replace( $data, $contNew, $pos, $posEnd - $pos );
		$pos += strlen( $contNew ) + 6;
	}
}

function _AddInline_StripStyles( &$inlines, &$data, &$idNext, $cb )
{
	$tag = array( 'style', 'STYLE' );

	$pos = 0;
	for( ;; )
	{
		$pos = Ui::TagBeginGetPos( $tag, $data, $pos );
		if( !$pos )
			break;

		$pos = $pos[ 1 ];

		$posEnd = Ui::TagEndGetPos( $tag, $data, $pos );
		$posEnd = $posEnd ? $posEnd[ 0 ] : strlen( $data );

		$cont = substr( $data, $pos, $posEnd - $pos );

		$contNew = _AddInline( $inlines, call_user_func_array( $cb, array( $cont ) ), 'STYLE', array( '/*', '*/' ), $idNext );
		$data = substr( $data, 0, $pos ) . $contNew . substr( $data, $posEnd );
		$pos += strlen( $contNew ) + 8;
	}
}

function _NormalizeHtmlData( $norm, &$data, $encoding = null, $min = false )
{

	if( !$norm )
		return( true );

	$inlines = array();
	$idNext = 1;

	if( $norm & 1 )
	{

		for( $pos = 0; ; )
		{
			$posPhpTag = strpos( $data, '<?php', $pos );
			if( $posPhpTag === false )
				break;

			$posPhpTagEnd = strpos( $data, '?>', $posPhpTag + 5 );
			if( $posPhpTagEnd === false )
				break;

			$posPhpTagEnd += 2;

			$data = substr_replace( $data, '', $posPhpTag, $posPhpTagEnd - $posPhpTag );
			$pos = $posPhpTag;
		}

		$data = str_replace( array( '<![if ', '<![endif]>', '<!===', '===>' ), array( '<!--[if ', '<![endif]-->', '<!--===', '===-->' ), $data );

		{
			$obj = new AnyObj();
			$obj -> encoding = $encoding;
			$obj -> encodingCorr = !!( $norm & 512 );
			$obj -> cb = function( $obj, $cont, $attrs )
			{
				return( IsScriptTypeJs( (isset($attrs[ 'type' ])?$attrs[ 'type' ]:null) ) ? _NormalizeScriptData( 'script', $cont, $obj -> encoding, $obj -> encodingCorr ) : str_replace( '</', '{{{{ESC_TAG}}}}<\\/', $cont ) );
			};

			_AddInline_StripScripts( $inlines, $data, $idNext, array( $obj, 'cb' ) );
			unset( $obj );
		}

		if( $norm & 2 )
		{
			$tag = array( 'noscript', 'NOSCRIPT' );

			$pos = 0;
			for( ;; )
			{
				$pos = Ui::TagBeginGetPos( $tag, $data, $pos );
				if( !$pos )
					break;

				$pos = $pos[ 1 ];

				$posEnd = Ui::TagEndGetPos( $tag, $data, $pos );
				$posEnd = $posEnd ? $posEnd[ 0 ] : strlen( $data );

				$cont = substr( $data, $pos, $posEnd - $pos );

				$contNew = _AddInline( $inlines, _HtmlProcess_TagsNormalize( $cont, 'noscript', $min ), 'NOSCRIPT', array( '', '' ), $idNext );
				$data = substr( $data, 0, $pos ) . $contNew . substr( $data, $posEnd );
				$pos += strlen( $contNew ) + 11;
			}
		}

		_AddInline_StripHtmlComments( $inlines, $data, $idNext, !!( $norm & 2 ) );

		{
			$obj = new AnyObj();
			$obj -> encoding = $encoding;
			$obj -> cb = function( $obj, $cont )
			{
				return( htmlentities( $cont, ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE, $obj -> encoding ) );
			};

			_AddInline_StripXmps( $inlines, $data, $idNext, array( $obj, 'cb' ) );
			unset( $obj );
		}

		{
			$obj = new AnyObj();
			$obj -> encoding = $encoding;
			$obj -> encodingCorr = !!( $norm & 512 );
			$obj -> cb = function( $obj, $cont )
			{
				return( str_replace( array( '<', '>' ), array( '&lt;', '&gt;' ), _NormalizeScriptData( 'style', $cont, $obj -> encoding, $obj -> encodingCorr, true ) ) );
			};

			_AddInline_StripStyles( $inlines, $data, $idNext, array( $obj, 'cb' ) );
			unset( $obj );
		}

		{
			$data = str_replace( "\r\r", "\r", $data );

			$data = str_replace( array( '</br>', '</BR>', '<br/>', '<BR/>' ), '<br>', $data );

			$data = preg_replace_callback( '/&nbsp[^;]/',
				function( $m )
				{
					return( '&nbsp;' . substr( $m[ 0 ], 5 ) );
				}
			, $data );
		}

		{
			$tagHeadPos = Ui::TagGetPos( array( 'head', 'HEAD' ), $data );
			if( $tagHeadPos[ 0 ] && $tagHeadPos[ 1 ] )
			{
				$tagBodyPos = Ui::TagGetPos( array( 'body', 'BODY' ), $data );
				if( $tagBodyPos[ 1 ] && $tagBodyPos[ 1 ][ 0 ] < $tagHeadPos[ 1 ][ 0 ] )
					$data = substr_replace( $data, '', $tagBodyPos[ 1 ][ 0 ], $tagBodyPos[ 1 ][ 2 ] );
				if( $tagBodyPos[ 0 ] && $tagBodyPos[ 0 ][ 0 ] < $tagHeadPos[ 1 ][ 0 ] )
					$data = substr_replace( $data, '', $tagBodyPos[ 0 ][ 0 ], $tagBodyPos[ 0 ][ 2 ] );
			}
		}

		if( $bodyRange = _GetTagMaxRange( array( 'body', 'BODY' ), $data ) )
		{
			$tags = array(
				array( 'tag' => array( '?xml', '?XML' ) ),
				array( 'tag' => array( '!doctype', '!DOCTYPE' ) ),
				array( 'tag' => array( 'html', 'HTML' ) ),
				array( 'tag' => array( 'head', 'HEAD' ) ),
				array( 'tag' => array( 'body', 'BODY' ) ),
				array( 'tag' => array( 'title', 'TITLE' ), 'content' => true ),
			);

			foreach( $tags as $tag )
			{
				if( (isset($tag[ 'content' ])?$tag[ 'content' ]:null) )
				{
					$tag = $tag[ 'tag' ];

					$pos = $bodyRange[ 0 ];
					for( ;; )
					{
						$posBegin = Ui::TagBeginGetPos( $tag, $data, $pos );
						if( !$posBegin || $posBegin[ 0 ] >= $bodyRange[ 1 ] )
							break;

						$posEnd = Ui::TagEndGetPos( $tag, $data, $posBegin[ 1 ] );
						if( !$posEnd || $posEnd[ 0 ] >= $bodyRange[ 1 ] )
							break;

						$data = substr( $data, 0, $posBegin[ 0 ] ) . substr( $data, $posEnd[ 1 ] );
						$bodyRange[ 1 ] -= $posEnd[ 1 ] - $posBegin[ 0 ];
						$pos = $posBegin[ 0 ];
					}

					continue;
				}

				$tag = $tag[ 'tag' ];

				for( $i = 1; $i <= 2; $i++ )
				{
					$pos = $bodyRange[ 0 ];
					for( ;; )
					{
						$pos = ( $i == 1 ) ? Ui::TagBeginGetPos( $tag, $data, $pos ) : Ui::TagEndGetPos( $tag, $data, $pos );
						if( !$pos || $pos[ 0 ] >= $bodyRange[ 1 ] )
							break;

						$data = substr_replace( $data, '', $pos[ 0 ], $pos[ 2 ] );
						$bodyRange[ 1 ] -= $pos[ 2 ];
						$pos = $pos[ 0 ];
					}
				}
			}
		}

		{
			$tag = array( 'html', 'HTML' );

			if( !Ui::TagBeginGetPos( $tag, $data ) )
				if( $posTagBegin = Ui::TagBeginGetPos( array( 'head', 'HEAD' ), $data ) )
					$data = substr_replace( $data, Ui::TagOpen( $tag[ 0 ] ), $posTagBegin[ 0 ], 0 );
		}

		{
			$tag = array( 'head', 'HEAD' );

			if( !Ui::TagBeginGetPos( $tag, $data ) )
				if( $posTagBegin = Ui::TagBeginGetPos( array( 'html', 'HTML' ), $data ) )
					$data = substr_replace( $data, Ui::TagOpen( $tag[ 0 ] ), $posTagBegin[ 1 ], 0 );

			if( !Ui::TagEndGetPos( $tag, $data ) )
				if( $posTagEnd = Ui::TagBeginGetPos( array( 'body', 'BODY' ), $data ) )
					$data = substr_replace( $data, Ui::TagClose( $tag[ 0 ] ), $posTagEnd[ 0 ], 0 );
		}

		{
		    $tagHead = array( 'head', 'HEAD' );
			$tagBody = array( 'body', 'BODY' );

			if( $posTagEnd = Ui::TagEndGetPos( $tagHead, $data ) )
			{
				if( $posTagBegin = Ui::TagBeginGetPos( $tagBody, $data, $posTagEnd[ 1 ] ) )
				{
					if( strlen( trim( substr( $data, $posTagEnd[ 1 ], $posTagBegin[ 0 ] - $posTagEnd[ 1 ] ) ) ) )
					{
						$data = substr_replace( $data, Ui::TagClose( $tagHead[ 0 ] ), $posTagBegin[ 0 ], 0 );
						$data = substr_replace( $data, '', $posTagEnd[ 0 ], $posTagEnd[ 2 ] );
					}
				}
			}
		}
	}

	if( $norm & 2 )
	{

		$contBodyFromHead = '';
		if( $headRange = _GetTagMaxRange( array( 'head', 'HEAD' ), $data ) )
		    $data = substr_replace( $data, _HtmlProcess_TagsNormalize( substr( $data, $headRange[ 0 ], $headRange[ 1 ] - $headRange[ 0 ] ), 'head', $min, $contBodyFromHead ), $headRange[ 0 ], $headRange[ 1 ] - $headRange[ 0 ] );

		if( $bodyRange = _GetTagMaxRange( array( 'body', 'BODY' ), $data ) )
			$data = substr_replace( $data, $contBodyFromHead . _HtmlProcess_TagsNormalize( substr( $data, $bodyRange[ 0 ], $bodyRange[ 1 ] - $bodyRange[ 0 ] ), 'body', $min ), $bodyRange[ 0 ], $bodyRange[ 1 ] - $bodyRange[ 0 ] );

	}

	$aMaskedTagsLite = array( 'replace' => array(), 'types' => array( 'block' => '', 'inline' => '', 'empty' => '' ) );

	if( $norm & 1 )
	{

		{
			_MaskTag( $aMaskedTagsLite, array( 'p', 'address', 'ol', 'ul', 'table', 'noscript'  ), 'block', true );
			$data = str_ireplace( array_keys( $aMaskedTagsLite[ 'replace' ] ), $aMaskedTagsLite[ 'replace' ], $data );
		}
	}

	if( $inlines )
		_ApplyInlinesBack( $data, $inlines );

	if( !( $norm & 524288 ) )
		return( true );

	if( Gen::DoesFuncExist( 'tidy_parse_string' ) )
	{
		$dataCopy = $data;

		$aMaskedTags = array( 'replace' => array(), 'types' => array(
			'block' => 'script,section,article,main,aside,header,footer,nav,figure,figcaption,template,video,track,canvas,details,dialog,hgroup,menu,summary,',
			'inline' => '',
			'empty' => 'command,embed,keygen,source,track,wbr,' )
		);
		_MaskTag( $aMaskedTags, array( 'a', 'audio', 'command', 'datalist', 'embed', 'keygen', 'mark', 'menuitem', 'meter', 'output', 'progress', 'source', 'time', 'wbr', 'ruby', 'rt', 'rp', 'bdi' ), 'inline' );
		_MaskTag( $aMaskedTags, array( 'menu' ), 'block' );

		foreach( $aMaskedTagsLite[ 'types' ] as $maskedTagsLiteTypeId => $maskedTagsLiteType )
			$aMaskedTags[ 'types' ][ $maskedTagsLiteTypeId ] .= $maskedTagsLiteType;

		$dataCopy = str_replace( "\t", ' ', $dataCopy );
		$dataCopy = str_replace( "\v", ' ', $dataCopy );
		$dataCopy = str_replace( "\f", ' ', $dataCopy );

		$dataCopy = str_replace( "\r", '', $dataCopy );

		$dataCopy = str_ireplace( array_keys( $aMaskedTags[ 'replace' ] ), $aMaskedTags[ 'replace' ], $dataCopy );

		$tidy = new \tidy();
		if( $tidy -> parseString( $dataCopy, array(
			'wrap-sections'				=> false,
			'wrap'						=> 0,
			'wrap-asp'					=> false,
			'wrap-jste'					=> false,
			'wrap-php'					=> false,
			'drop-empty-elements'		=> false,
			'drop-empty-paras'			=> false,
			'merge-divs'				=> false,
			'merge-spans'				=> false,
			'merge-emphasis'			=> false,
			'join-styles'				=> false,
			'tidy-mark'					=> false,
			'fix-style-tags'			=> false,

			'preserve-entities'			=> true,
			'quote-ampersand'			=> true,
			'quote-marks'				=> true,
			'quote-nbsp'				=> true,

			'new-blocklevel-tags'		=> $aMaskedTags[ 'types' ][ 'block' ],
			'new-inline-tags'			=> $aMaskedTags[ 'types' ][ 'inline' ],
			'new-empty-tags'			=> $aMaskedTags[ 'types' ][ 'empty' ],

			'clean'						=> false,
			'output-html'				=> true,
		) ) )
		{
			if( $tidy -> cleanRepair() )
			{
				$data = str_ireplace( $aMaskedTags[ 'replace' ], array_keys( $aMaskedTags[ 'replace' ] ), $tidy -> value );
				return( true );
			}
		}
	}

	return( false );
}

function _ContentTypeToArray( $contentType )
{
	$matches = explode( ';', trim( strtolower( $contentType ) ) );
	if( isset( $matches[ 1 ] ) )
	{
		$matches[ 1 ] = explode( '=', $matches[ 1 ] );

		$matches[ 1 ] = isset( $matches[ 1 ][ 1 ] ) && $matches[ 1 ][ 1 ] ? trim( $matches[ 1 ][ 1 ] ) : null;
	}
	else
		$matches[ 1 ] = null;

	return( $matches );
}

function _ContentTypeFromHtml( $markup )
{
	$matches = array();

	preg_match( '@<meta[^>]+http-equiv\\s*=\\s*(["|\'])Content-Type\\1([^>]+?)>@i', $markup, $matches );
	if( !isset( $matches[ 0 ] ) )
		return( array( null, null ) );

	preg_match( '@content\\s*=\\s*(["|\'])(.+?)\\1@', $matches[ 0 ], $matches );
	if( !isset( $matches[ 0 ] ) )
		return( array( null, null ) );

	return( _ContentTypeToArray( $matches[ 2 ] ) );
}

function _CharsetFromHtml( $markup, &$documentCharset )
{
	$contentType = _ContentTypeFromHtml( $markup );
	if( $contentType[ 1 ] )
	{
		$documentCharset = strtoupper( $contentType[ 1 ] );
		return;
	}

	$matches = array();
	preg_match( '@<meta[^>]+charset\\s*=[\\s"\']*([\\w-]+)@i', $markup, $matches );
	if( !isset( $matches[ 0 ] ) )
		return;

	$documentCharset = strtoupper( trim( (isset($matches[ 1 ])?$matches[ 1 ]:null) ) );
}

function _HtmlParseCharset( $data, &$documentCharset )
{
	$htmlHeadEnd = Gen::StrPosArr( $data, array( '</head>', '</HEAD>' ) );
	if( $htmlHeadEnd !== false )
		$htmlHead = substr( $data, 0, $htmlHeadEnd );
	else
		$htmlHead = $data;

	_CharsetFromHtml( $htmlHead, $documentCharset );

	if( !$documentCharset )
		$documentCharset = function_exists( 'mb_detect_encoding' ) ? strtoupper( mb_detect_encoding( $data, 'ASCII,UTF-8,ISO-8859-1,WINDOWS-1252,ISO-8859-15,AUTO' ) ) : 'UTF-8';
}

function _CharsetSetToHtml( &$html, $charset, $xhtml = false )
{

	$html = preg_replace( '@\\s*<meta[^>]+http-equiv\\s*=\\s*(["\'])Content-Type\\1[^>]+/?>@i', '<meta seraph-accel-delunneededmeta>', $html );

	$html = preg_replace( '@\\s*<meta[^>]+charset\\s*=\\s*(["\'])([^>]+)\\1\\s*/?>@i', '<meta seraph-accel-delunneededmeta>', $html );

	$htmlHeadBegin = Ui::TagBeginGetPos( array( 'head', 'HEAD' ), $html );
	if( !$htmlHeadBegin )
		return;

	$htmlHeadBegin = $htmlHeadBegin[ 1 ];
	$html = substr( $html, 0, $htmlHeadBegin ) . '<meta http-equiv="Content-Type" content="text/html;charset=' . strtoupper( $charset ) . '"' . ( $xhtml ? ' /' : '' ) . '>' . substr( $html, $htmlHeadBegin );
}

function _HtmlAdjustCharset( &$data, $documentCharset )
{
	_CharsetSetToHtml( $data, $documentCharset );

}

function GetHtmlDoc( $data, $norm, $min = false, $commentsPreserve = true )
{

	$isAmp = false;
	{
		$posHtml = Ui::TagBeginGetPos( array( 'html', 'HTML' ), $data );
		if( $posHtml )
			$isAmp = isset( TagsNormalizer::ihjjquihqf( substr( $data, $posHtml[ 0 ] + 5, $posHtml[ 1 ] - ( $posHtml[ 0 ] + 5 + 1 ) ) )[ 'amp' ] );
	}

	if( !$isAmp )
	{
		$documentCharset = RemoveZeroSpace( $data );
		_HtmlParseCharset( $data, $documentCharset );

		if( !_NormalizeHtmlData( $norm, $data, $documentCharset, $min ) )
			return( null );

		if( $documentCharset )
		{
			if( function_exists( 'mb_convert_encoding' ) )
				$data = mb_convert_encoding( $data, $documentCharset, $documentCharset );
			if( substr( $data, 0, 3 ) === "\xEF\xBB\xBF" )
				$content = substr( $content, 3 );
		}

		_HtmlAdjustCharset( $data, $documentCharset );
	}

	$doc = _ParseHtmlData( $data, $norm );
	if( !$doc )
		return( null );

	unset( $data );

	foreach( HtmlNd::ChildrenAsArr( $doc -> getElementsByTagName( 'meta' ) ) as $item )
		if( $item -> hasAttribute( 'seraph-accel-delunneededmeta' ) )
			$item -> parentNode -> removeChild( $item );

	if( $norm & 1 )
	{
		$items = HtmlNd::ChildrenAsArr( $doc -> getElementsByTagName( 'style' ) );
		foreach( $items as $item )
		    HtmlNd::SetValFromContent( $item, str_replace( array( '&lt;', '&gt;' ), array( '<', '>' ), $item -> nodeValue ) );

		$items = HtmlNd::ChildrenAsArr( $doc -> getElementsByTagName( 'script' ) );
		foreach( $items as $item )
		{

			$item -> nodeValue = htmlspecialchars( str_replace( '{{{{ESC_TAG}}}}<\\/', '</', $item -> nodeValue ) );
		}

		for( $item = null; $item = HtmlNd::GetNextTreeChild( $doc, $item );  )
		{
			if( $item -> nodeType != XML_ELEMENT_NODE )
				continue;

			if( $tagMasked = _UnMaskTag( $item -> nodeName ) )
				$item = HtmlNd::SetTag( $item, $tagMasked );
		}

		{
			$ndHtml = HtmlNd::FindByTag( $doc, 'html', false );
			$ndBody = HtmlNd::FindByTag( $ndHtml, 'body', false );
			if( $ndHtml && $ndBody )
			{
				foreach( array( $ndBody, $ndHtml ) as $ndMoveFromAfter )
					for( $item = $ndMoveFromAfter -> nextSibling; $item;  )
					{
						$itemNext = $item -> nextSibling;
						if( !$itemNext && $item -> nodeType == XML_TEXT_NODE && !strlen( trim( $item -> textContent ) ) )
							break;
						$ndBody -> appendChild( $item );
						$item = $itemNext;
					}
			}
		}
	}

	if( $norm & 2 )
	{
		for( $item = null; $item = HtmlNd::GetNextTreeChild( $doc, $item );  )
		{
			if( $item -> nodeType != XML_ELEMENT_NODE )
				continue;

			$tagMasked = $item -> getAttribute( 'seraph-accel-masked' );
			if( !$tagMasked )
				continue;

			$item -> removeAttribute( 'seraph-accel-masked' );
			$item = HtmlNd::SetTag( $item, $tagMasked );
		}
	}

	if( $min || $commentsPreserve !== true )
	{
		HtmlNd::CleanChildren( $doc,
			function( $nd, $data )
			{
				if( $nd -> nodeType == XML_COMMENT_NODE )
				{
					if( $data[ 'commentsPreserve' ] !== true && !IsObjInRegexpList( $data[ 'commentsPreserve' ], array( 'body' => $nd -> textContent ) ) )
						return( true );
					return( false );
				}

				if( $data[ 'min' ] && HtmlNd::IsNodeEmpty( $nd ) )
				{
					if( $nd -> textContent == '' )
						return( true );

					$ndPrev = $nd -> previousSibling;
					if( $ndPrev && $ndPrev -> nodeType != XML_ELEMENT_NODE )
						return( true );

					$nd -> textContent = ' ';
					return( false );
				}

				return( false );
			}
			, array( 'min' => $min, 'commentsPreserve' => $commentsPreserve )
		);
	}

	return( $doc );
}

function _ParseHtmlData( $data, $norm = 0 )
{

	$doc = new \DOMDocument();
	$doc -> registerNodeClass( 'DOMElement', 'seraph_accel\\DomElementEx' );

	$doc -> strictErrorChecking = false;

	$doc -> recover = !!( $norm & 1 );

	$data = preg_replace_callback( '@\\s(?:v-on):[^\\s]+\\s*=\\s*["\']@',
		function( $m )
		{
			return( str_replace( array( '[', ']' ), array( '_sq67547865_o_', '_sq67547865_c_' ), $m[ 0 ] ) );
		}
	, $data );

	if( !@$doc -> loadHTML( $data, LIBXML_COMPACT | LIBXML_NONET | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_PARSEHUGE ) )
		return( null );

	return( $doc );
}

function HtmlDocDeParse( $doc, $norm = 0, $itemContainerOnly = null )
{
	$inlines = array();
	$idNext = 1;

	foreach( ( $itemContainerOnly ? $itemContainerOnly : $doc ) -> getElementsByTagName( 'a' ) as $item )
	{
		$href = $item -> getAttribute( 'href' );
		if( $href !== null && $href != '' )
			$item -> setAttribute( 'href', _AddInline( $inlines, $href, 'A-HREF', array( '', '' ), $idNext, true ) );
	}

	for( $item = null; $item = HtmlNd::GetNextTreeChild( $itemContainerOnly ? $itemContainerOnly : $doc, $item );  )
	{

		if( $item -> nodeType == XML_COMMENT_NODE )
			$item -> nodeValue = _AddInline( $inlines, $item -> nodeValue, 'COMMENT', array( '', '' ), $idNext );
		else if( $item -> nodeType == XML_ELEMENT_NODE )
		{
			if( ( $norm & 1 ) && $item -> nodeName == 'xmp' )
				$item -> nodeValue = _AddInline( $inlines, html_entity_decode( $item -> nodeValue ), 'XMP', array( '', '' ), $idNext );
		}
	}

	if( $itemContainerOnly )
		$data = HtmlNd::DeParse( $itemContainerOnly, false );
	else if( (isset($doc -> documentElement)?$doc -> documentElement:null) )
	{
		$data = $doc -> saveHTML( $doc -> documentElement );
		$doc -> replaceChild( $doc -> createTextNode( '%{%{%{HTML}%}%}%' ), $doc -> documentElement );
		$data = str_replace( '%{%{%{HTML}%}%}%', $data, $doc -> saveHTML() );
	}
	else
		$data = $doc -> saveHTML();

	if( $inlines )
		_ApplyInlinesBack( $data, $inlines );

	$data = str_replace( array( 'seraph-accel-delunneededmeta', '_seraph_accel_attr_temp_symb_40_', '_seraph_accel_attr_temp_symb_23_', '_seraph_accel_attr_temp_symb_void_' ), array( '', '@', '#', '' ), $data );
	$data = preg_replace( '@{{{MASKED_ENT_SYM:([\\d]+)}}}@', '&#${1};', $data );

	foreach( array( '"', '\'' ) as $q )
		$data = preg_replace_callback( '@\\s(?:v-for|v-if|v-else|v-else-if|v-show|v-text|v-html|selectable)\\s*=\\s*' . $q . '[^' . $q . ']+' . $q . '@',
			function( $m )
			{
				return( str_replace( array( '&amp;', '&lt;', '&gt;' ), array( '&', '<', '>' ), $m[ 0 ] ) );
			}
		, $data );

	$data = preg_replace_callback( '@\\s(?:v-on):[^\\s]+\\s*=\\s*["\']@',
		function( $m )
		{
			return( str_replace( array( '_sq67547865_o_', '_sq67547865_c_' ), array( '[', ']' ), $m[ 0 ] ) );
		}
	, $data );

	return( $data );
}

function IsHtml( $buffer )
{
	if( Ui::TagBeginGetPos( array( '!doctype', '!DOCTYPE' ), $buffer ) )
		return( true );

	foreach( array(
		array( 'html', 'HTML' ),
		array( 'head', 'HEAD' ),
		array( 'body', 'BODY' ),
	) as $tag )
	{
		if( Ui::TagBeginGetPos( $tag, $buffer ) )
			return( true );
		if( Ui::TagEndGetPos( $tag, $buffer ) )
			return( true );
	}

	return( false );
}

function RemoveZeroSpace( &$content, $charset = null )
{

	if( substr( $content, 0, 3 ) === "\xEF\xBB\xBF" )
	{
		$content = substr( $content, 3 );
		$charset = 'UTF-8';
	}

	return( $charset );
}

class TagsNormalizer
{
	private $parent;
	private $parents = array();
	private $parentsAttrs = array();
	private $parentSubInlinePos;
	private $parentNotAllowedPos;

	private $opts;

	static private $udybdyqw = array(
		'#pcdata' => 1,
		'a' => 1, 'abbr' => 1, 'acronym' => 1, 'audio' => 2, 'applet' => 1, 'b' => 1, 'bdi' => 1, 'bdo' => 1, 'big' => 1, 'br' => 1,
		'button' => 1, 'canvas' => 1, 'cite' => 1, 'code' => 1, 'command' => 1, 'data' => 1, 'datalist' => 2, 'del' => 1,
		'dfn' => 1, 'em' => 1, 'embed' => 1, 'figcaption' => 1, 'font' => 1, 'i' => 1, 'iframe' => 1, 'img' => 1, 'input' => 1,
		'ins' => 1, 'kbd' => 1, 'label' => 1, 'link' => 1, 'map' => 1, 'mark' => 1, 'meta' => 1, 'meter' => 1, 'noscript' => 1, 'object' => 1,
		'output' => 1, 'picture' => 2, 'progress' => 1, 'q' => 1, 'ruby' => 2, 's' => 1, 'samp' => 1, 'script' => 1, 'select' => 2, 'slot' => 1, 'small' => 1,
		'span' => 1, 'strike' => 1, 'strong' => 1, 'sub' => 1, 'summary' => 1, 'sup' => 1, 'svg' => 2, 'template' => 1, 'text' => 1, 'textarea' => 1, 'time' => 1,
		'u' => 1, 'tt' => 1, 'var' => 1, 'video' => 2, 'wbr' => 1,
		'noindex' => 1,
	);

	static private $ojfcuqw = array( '!doctype' => 1, 'area' => 1, 'base' => 1, 'basefont' => 1, 'br' => 1, 'col' => 1,
		'command' => 1, 'embed' => 1, 'frame' => 1, 'hr' => 1, 'img' => 1, 'input' => 1, 'isindex' => 1, 'keygen' => 1, 'link' => 1,
		'meta' => 1, 'param' => 1, 'source' => 1, 'track' => 1, 'wbr' => 1 );

	static private $tbyxsjduhqfqw = array(
		'datalist' => array( 'option' => 1, ),
		'optgroup' => array( 'option' => 1, ),
		'select' => array( 'option' => 1, 'optgroup' => 1, ),

		'ol' => array( 'li' => 1, ),
		'ul' => array( 'li' => 1, ),
		'menu' => array( 'li' => 1, ),
		'dir' => array( 'li' => 1, ),
		'dl' => array( 'dd' => 1, 'dt' => 1, ),
		'hgroup' => array( 'h1' => 1, 'h2' => 1, 'h3' => 1, 'h4' => 1, 'h5' => 1, 'h6' => 1, ),

		'option' => array( '#pcdata' => 1, ),
		'rp' => array( '#pcdata' => 1, ),
		'script' => array( '#pcdata' => 1, ),
		'textarea' => array( '#pcdata' => 1, ),

		'rbc' => array( 'rb' => 1, 'rp' => 1, 'rt' => 1, ),
		'rtc' => array( 'rt' => 1, 'rp' => 1, ),
		'ruby' => array( 'rbc' => 1, 'rtc' => 1, 'rb' => 1, 'rp' => 1, 'rt' => 1, ),

		'table' => array( 'caption' => 1, 'col' => 1, 'colgroup' => 1, 'tfoot' => 1, 'tbody' => 1, 'tr' => 1, 'thead' => 1, ),
		'tbody' => array( 'tr' => 1, ),
		'tfoot' => array( 'tr' => 1, ),
		'thead' => array( 'tr' => 1, ),
		'tr' => array( 'td' => 1, 'th' => 1, ),
		'colgroup' => array( 'col' => 1, ),

		'picture' => array( 'source' => 1, ),
		'audio' => array( 'source' => 1, ),
		'video' => array( 'source' => 1, ),
	);

	static private $ubrqaquhrdkqw = array(
		'html' => 1, 'head' => 1, 'body' => 1, 'header' => 1, 'footer' => 1, 'table' => 1, 'section' => array( 'form' => 1 ),
	);

	static private $tumebbqtbyxsjduhqfqw = array(

		'head' => array(
			'head' => 1,
			'title' => 1, 'base' => 1, 'link' => 1, 'style' => 1, 'meta' => 1,
			'script' => 1, 'noscript' => 1, 'template' => 1,
		)
	);

	static private $htxqw = array(
		'h1' => 1, 'h2' => 1, 'h3' => 1, 'h4' => 1, 'h5' => 1, 'h6' => 1,
	);

	static private $kojyjduqw = array( 'quot' => 1, 'amp' => 1, 'lt' => 1, 'gt' => 1 );
	static private $dojyjduqw = array( 'fnof' => '402', 'Alpha' => '913', 'Beta' => '914', 'Gamma' => '915', 'Delta' => '916', 'Epsilon' => '917', 'Zeta' => '918', 'Eta' => '919', 'Theta' => '920', 'Iota' => '921', 'Kappa' => '922', 'Lambda' => '923', 'Mu' => '924', 'Nu' => '925', 'Xi' => '926', 'Omicron' => '927', 'Pi' => '928', 'Rho' => '929', 'Sigma' => '931', 'Tau' => '932', 'Upsilon' => '933', 'Phi' => '934', 'Chi' => '935', 'Psi' => '936', 'Omega' => '937', 'alpha' => '945', 'beta' => '946', 'gamma' => '947', 'delta' => '948', 'epsilon' => '949', 'zeta' => '950', 'eta' => '951', 'theta' => '952', 'iota' => '953', 'kappa' => '954', 'lambda' => '955', 'mu' => '956', 'nu' => '957', 'xi' => '958', 'omicron' => '959', 'pi' => '960', 'rho' => '961', 'sigmaf' => '962', 'sigma' => '963', 'tau' => '964', 'upsilon' => '965', 'phi' => '966', 'chi' => '967', 'psi' => '968', 'omega' => '969', 'thetasym' => '977', 'upsih' => '978', 'piv' => '982', 'bull' => '8226', 'hellip' => '8230', 'prime' => '8242', 'Prime' => '8243', 'oline' => '8254', 'frasl' => '8260', 'weierp' => '8472', 'image' => '8465', 'real' => '8476', 'trade' => '8482', 'alefsym' => '8501', 'larr' => '8592', 'uarr' => '8593', 'rarr' => '8594', 'darr' => '8595', 'harr' => '8596', 'crarr' => '8629', 'lArr' => '8656', 'uArr' => '8657', 'rArr' => '8658', 'dArr' => '8659', 'hArr' => '8660', 'forall' => '8704', 'part' => '8706', 'exist' => '8707', 'empty' => '8709', 'nabla' => '8711', 'isin' => '8712', 'notin' => '8713', 'ni' => '8715', 'prod' => '8719', 'sum' => '8721', 'minus' => '8722', 'lowast' => '8727', 'radic' => '8730', 'prop' => '8733', 'infin' => '8734', 'ang' => '8736', 'and' => '8743', 'or' => '8744', 'cap' => '8745', 'cup' => '8746', 'int' => '8747', 'there4' => '8756', 'sim' => '8764', 'cong' => '8773', 'asymp' => '8776', 'ne' => '8800', 'equiv' => '8801', 'le' => '8804', 'ge' => '8805', 'sub' => '8834', 'sup' => '8835', 'nsub' => '8836', 'sube' => '8838', 'supe' => '8839', 'oplus' => '8853', 'otimes' => '8855', 'perp' => '8869', 'sdot' => '8901', 'lceil' => '8968', 'rceil' => '8969', 'lfloor' => '8970', 'rfloor' => '8971', 'lang' => '9001', 'rang' => '9002', 'loz' => '9674', 'spades' => '9824', 'clubs' => '9827', 'hearts' => '9829', 'diams' => '9830', 'apos' => '39',  'OElig' => '338', 'oelig' => '339', 'Scaron' => '352', 'scaron' => '353', 'Yuml' => '376', 'circ' => '710', 'tilde' => '732', 'ensp' => '8194', 'emsp' => '8195', 'thinsp' => '8201', 'zwnj' => '8204', 'zwj' => '8205', 'lrm' => '8206', 'rlm' => '8207', 'ndash' => '8211', 'mdash' => '8212', 'lsquo' => '8216', 'rsquo' => '8217', 'sbquo' => '8218', 'ldquo' => '8220', 'rdquo' => '8221', 'bdquo' => '8222', 'dagger' => '8224', 'Dagger' => '8225', 'permil' => '8240', 'lsaquo' => '8249', 'rsaquo' => '8250', 'euro' => '8364', 'nbsp' => '160', 'iexcl' => '161', 'cent' => '162', 'pound' => '163', 'curren' => '164', 'yen' => '165', 'brvbar' => '166', 'sect' => '167', 'uml' => '168', 'copy' => '169', 'ordf' => '170', 'laquo' => '171', 'not' => '172', 'shy' => '173', 'reg' => '174', 'macr' => '175', 'deg' => '176', 'plusmn' => '177', 'sup2' => '178', 'sup3' => '179', 'acute' => '180', 'micro' => '181', 'para' => '182', 'middot' => '183', 'cedil' => '184', 'sup1' => '185', 'ordm' => '186', 'raquo' => '187', 'frac14' => '188', 'frac12' => '189', 'frac34' => '190', 'iquest' => '191', 'Agrave' => '192', 'Aacute' => '193', 'Acirc' => '194', 'Atilde' => '195', 'Auml' => '196', 'Aring' => '197', 'AElig' => '198', 'Ccedil' => '199', 'Egrave' => '200', 'Eacute' => '201', 'Ecirc' => '202', 'Euml' => '203', 'Igrave' => '204', 'Iacute' => '205', 'Icirc' => '206', 'Iuml' => '207', 'ETH' => '208', 'Ntilde' => '209', 'Ograve' => '210', 'Oacute' => '211', 'Ocirc' => '212', 'Otilde' => '213', 'Ouml' => '214', 'times' => '215', 'Oslash' => '216', 'Ugrave' => '217', 'Uacute' => '218', 'Ucirc' => '219', 'Uuml' => '220', 'Yacute' => '221', 'THORN' => '222', 'szlig' => '223', 'agrave' => '224', 'aacute' => '225', 'acirc' => '226', 'atilde' => '227', 'auml' => '228', 'aring' => '229', 'aelig' => '230', 'ccedil' => '231', 'egrave' => '232', 'eacute' => '233', 'ecirc' => '234', 'euml' => '235', 'igrave' => '236', 'iacute' => '237', 'icirc' => '238', 'iuml' => '239', 'eth' => '240', 'ntilde' => '241', 'ograve' => '242', 'oacute' => '243', 'ocirc' => '244', 'otilde' => '245', 'ouml' => '246', 'divide' => '247', 'oslash' => '248', 'ugrave' => '249', 'uacute' => '250', 'ucirc' => '251', 'uuml' => '252', 'yacute' => '253', 'thorn' => '254', 'yuml' => '255' );

	static private $vbhshqubshjjqqw = array(
		'class' => 1, 'style' => 1,
	);

	function __construct( $opts = array(), $parent = null )
	{
		$this -> opts = $opts;
		$this -> parent = $parent;
	}

	function process( $t, &$contNotAllowed = null )
	{
		$t = preg_replace( '@[\x00-\x08\x0b-\x0c\x0e-\x1f]@', '', $t );
		$t = preg_replace_callback( '@<!(?:(?:--.*?--)|(?:\[CDATA\[.*?\]\]))>@sm', __CLASS__ . '::tsjcs_', $t );
		$t = preg_replace_callback( '@&amp;([a-zA-Z][a-zA-Z0-9]{1,30}|#(?:[0-9]{1,8}|[Xx][0-9A-Fa-f]{1,7}))[;<]@', __CLASS__ . '::jdu_', str_replace( '&', '&amp;', $t ) );

		for( $tOffs = 0; ; )
		{
			$tPartPos = strpos( $t, '<', $tOffs );

			if( $this -> parentNotAllowedPos !== null )
			{
				$n = ( $tPartPos === false ? strlen( $t ) : $tPartPos ) - $tOffs;
				$contNotAllowed .= substr( $t, $tOffs, $n );
				$t = substr_replace( $t, '', $tOffs, $n );

				if( $tPartPos !== false )
					$tPartPos = $tOffs;
			}

			if( $tPartPos === false )
				break;

			if( !preg_match( '@[a-zA-Z/>]@', (isset($t[ $tPartPos + 1 ])?$t[ $tPartPos + 1 ]:null) ) )
			{
				$t = substr_replace( $t, '&lt;', $tPartPos, 1 );
				$tOffs = $tPartPos + 4;
				continue;
			}

			for( $tPartTryPos = $tPartPos + 1; ; )
			{
				$tPartClosePos = strpos( $t, '>', $tPartTryPos );
				if( $tPartClosePos === false )
					break;

				$tPart = substr( $t, $tPartPos, $tPartClosePos + 1 - $tPartPos );

				$resNotAllowed = '';
				$tPartNew = $this -> wqj_( $tPart, $resNotAllowed );
				if( is_string( $tPartNew ) )
					break;

				$tPartTryPos = strpos( $t, $tPartNew[ 0 ], $tPartClosePos + 1 );
				if( $tPartTryPos === false )
				{
					$tPartClosePos = false;
					break;
				}

				$nnnnnn1 = substr( $t, $tPartClosePos, $tPartTryPos - $tPartClosePos );
				$nnnnnn2 = str_replace( '>', '&gt;', $nnnnnn1 );
				$t = substr_replace( $t, $nnnnnn2, $tPartClosePos, strlen( $nnnnnn1 ) );
				$tPartTryPos += strlen( $nnnnnn2 ) - strlen( $nnnnnn1 );
			}

			if( $tPartClosePos === false )
				break;

			$contNotAllowed .= $resNotAllowed;

			if( $tPartNew !== $tPart )
				$t = substr_replace( $t, $tPartNew, $tPartPos, strlen( $tPart ) );
			$tOffs = $tPartPos + strlen( $tPartNew );
		}

		$this -> byjdkfef_( $t, $contNotAllowed );

		if( strpos( $t, "\x01" ) !== false )
			$t = str_replace( array( "\x01", "\x02", "\x03", "\x04", "\x05" ), array( '', '', '&', '<', '>' ), $t );
		if( strpos( $contNotAllowed, "\x01" ) !== false )
			$contNotAllowed = str_replace( array( "\x01", "\x02", "\x03", "\x04", "\x05" ), array( '', '', '&', '<', '>' ), $contNotAllowed );

		return( $t );
	}

	private function juhiuh_( &$resNotAllowed, $res, $isNotAllowed = false )
	{
		if( $this -> parentNotAllowedPos === null && !$isNotAllowed )
			return( $res );

		$resNotAllowed .= $res;
		return( '' );
	}

	private function byjdkfef_( &$res, &$resNotAllowed, $e = null )
	{
		$ap = array();
		while( $p = $this -> fef_( $res, $resNotAllowed ) )
		{
			if( $p[ 0 ] == $e )
				break;
			array_splice( $ap, 0, 0, array( $p ) );
		}

		return( $ap );
	}

	private function fef_( &$res, &$resNotAllowed )
	{
		$p = array_pop( $this -> parents );
		if( $p === null )
			return( null );

		$res .= $this -> juhiuh_( $resNotAllowed, '</' . $p . '>' );
		$attrs = array_pop( $this -> parentsAttrs );

		if( $this -> parentSubInlinePos === count( $this -> parents ) )
			$this -> parentSubInlinePos = null;
		if( $this -> parentNotAllowedPos === count( $this -> parents ) )
			$this -> parentNotAllowedPos = null;

		return( array( $p, $attrs ) );
	}

	private function xikf_( &$res, &$resNotAllowed, $tag, $attrs = '', $selfClosed = false )
	{
		$isNotAllowed = false;
		if( $this -> parentNotAllowedPos === null )
		{
			$p = $this -> parents ? $this -> parents[ count( $this -> parents ) - 1 ] : $this -> parent;
			$pAllowedChildren = (isset(self::$tumebbqtbyxsjduhqfqw[ $p ])?self::$tumebbqtbyxsjduhqfqw[ $p ]:null);
			if( $pAllowedChildren && !(isset($pAllowedChildren[ $tag ])?$pAllowedChildren[ $tag ]:null) )
				$isNotAllowed = true;
		}

		$cont = '<' . $tag . $attrs;

		if( $selfClosed )
			$cont .= ' /';
		else
		{
			$this -> parents[] = $tag;
			$this -> parentsAttrs[] = $attrs;

			if( $this -> parentSubInlinePos === null )
				if( $inlTagInfo = (isset(self::$udybdyqw[ $tag ])?self::$udybdyqw[ $tag ]:null) )
					if( $inlTagInfo === 2 )
						$this -> parentSubInlinePos = count( $this -> parents ) - 1;

			if( $isNotAllowed )
				$this -> parentNotAllowedPos = count( $this -> parents ) - 1;
		}

		$cont .= '>';

		$res .= $this -> juhiuh_( $resNotAllowed, $cont, $isNotAllowed );
	}

	private function udybdywqjiy_( $tag, $parentPos = null )
	{
		if( $parentPos === null )
			$parentPos = count( $this -> parents ) - 1;

		if( $this -> parentSubInlinePos !== null && $parentPos >= $this -> parentSubInlinePos )
			return( true );
		return( !!(isset(self::$udybdyqw[ $tag ])?self::$udybdyqw[ $tag ]:null) );
	}

	private function iefudybdyjedjiqbjduhqfjuw_()
	{
		for( $i = count( $this -> parents ); $i > 0; $i-- )
			if( !$this -> udybdywqjiy_( $this -> parents[ $i - 1 ], $i - 1 ) )
				return( $i - 1 );

		return( false );
	}

	static private function tybqlucqdhjjqiy_( $name )
	{
		return( preg_match( '/^[\\w\\-\\.:@\\[\\]\\#]+$/', $name ) );
	}

	static function ihjjquihqf( $a, $vDef = '', $valClearCrlf = true )
	{
		$aA = array();

		if( strpos( $a, "\x01" ) !== false )
			$a = preg_replace( '`\x01[^\x01]*\x01`', '', $a );

		$mode = 0;
		while( strlen( $a ) )
		{
			$w = 0;
			switch( $mode )
			{
			case 0:
				if( preg_match( '`^[^\s=/"\']+`', $a, $m ) )
				{
					$nm = strtolower( $m[ 0 ] );
					$w = $mode = 1; $a = ltrim( substr_replace( $a, '', 0, strlen( $m[ 0 ] ) ), " \n\r\t\v\0\"'" );
				}
				else if( $a === '/' )
				{
					$aA[ '/' ] = '';
				}
				else if( Gen::StrStartsWith( $a, '/' ) )
				{
					$nm = '/';
					$w = $mode = 1; $a = substr( $a, 1 );
				}
				break;

			case 1:
				if( $a[ 0 ] == '=' )
				{
					$w = 1; $mode = 2;
					$a = ltrim( $a, '= ' );
				}
				else
				{
					$w = 1; $mode = 0; $a = ltrim( $a );
					if( !isset( $aA[ $nm ] ) && self::tybqlucqdhjjqiy_( $nm ) )
						$aA[ $nm ] = $vDef;
				}
				break;

			case 2:
				if( preg_match( '@^(?:"[^"]*")|(?:\'[^\']*\')|(?:\\s*[^\\s]+)@', $a, $m ) )
				{
					$m = $m[ 0 ];
					$a = ltrim( substr( $a, strlen( $m ) ), " \n\r\t\v\0\"'" ); $w = 1; $mode = 0;

					if( $m[ 0 ] == '"' || $m[ 0 ] == '\'' )
					{
						if( strlen( $m ) === 1 || substr( $m, -1 ) != $m[ 0 ] )
							return( $m[ 0 ] );
						$m = substr( $m, 1, -1 );
					}

					if( !isset( $aA[ $nm ] ) && self::tybqlucqdhjjqiy_( $nm ) )
					{
						if( $valClearCrlf === true || ( $valClearCrlf === null && (isset(self::$vbhshqubshjjqqw[ $nm ])?self::$vbhshqubshjjqqw[ $nm ]:null) ) )
							$m = trim( str_replace( array( "\n", "\r", "\t", "\v" ), ' ', $m ) );

						$aA[ $nm ] = str_replace( '<', '&lt;', $m );
					}
				}
				break;
			}

			if( $w == 0 )
			{
				$a = preg_replace( '`^(?:"[^"]*("|$)|\'[^\']*(\'|$)|\S)*\s*`', '', $a );
				$mode = 0;
			}
		}

		if( $mode == 1 )
			if( !isset( $aA[ $nm ] ) && self::tybqlucqdhjjqiy_( $nm ) )
				$aA[ $nm ] = $vDef;

		return( $aA );
	}

	private function wqj_( $t, &$resNotAllowed )
	{
		if( preg_match( '@^</\\s\\w@', $t ) )
			$t = substr_replace( $t, '</', 0, 3 );
		if( !preg_match( '@^<(/?)([a-zA-Z][a-zA-Z0-9:\\-_]*)([^>]*)>$@m', $t, $m ) )
			return( $this -> juhiuh_( $resNotAllowed, str_replace( array( '<', '>' ), array( '&lt;', '&gt;' ), $t ) ) );

		$closeTag = !empty( $m[ 1 ] );

		$e = strtolower( $m[ 2 ] );

		if( isset( $this -> opts[ 'mask_unk_tag' ] ) )
		{
			$eMasked = str_replace( array( ':' ), array( '_' ), $e );
			if( $eMasked != $e )
			{
				if( !$closeTag )
					$m[ 3 ] = $this -> opts[ 'mask_unk_tag' ] . '="' . $e . '" ' . $m[ 3 ];
				$m[ 2 ] = $e = $eMasked;
			}
			unset( $eMasked );
		}

		if( $closeTag )
		{
			if( isset( self::$ojfcuqw[ $e ] ) )
				return( '' );

			$res = '';

			{
				$isUnopen = false;
				$ePos = $this -> iefjiqbjduhqfjuw_( (isset(self::$htxqw[ $e ])?self::$htxqw[ $e ]:null) ? self::$htxqw : array( $e => 1 ) );
				if( $ePos === false )
					$isUnopen = true;
				else if( $e == 'p' )
				{
					$iefhrdkhqfhqud = $this -> iefudybdyjedjiqbjduhqfjuw_();
					if( $iefhrdkhqfhqud !== false && $iefhrdkhqfhqud > $ePos )
						$isUnopen = true;
				}
				else if( $ePos < count( $this -> parents ) - 1 && (isset(self::$tbyxsjduhqfqw[ $this -> parents[ count( $this -> parents ) - 1 ] ][ $e ])?self::$tbyxsjduhqfqw[ $this -> parents[ count( $this -> parents ) - 1 ] ][ $e ]:null) )
				{
					$isUnopen = true;
				}
				else
				{
					$e = $this -> parents[ $ePos ];

					$iefhrdkhqfhqud = $this -> iefjiqbjduhqfjuw_( self::$ubrqaquhrdkqw );
					if( $iefhrdkhqfhqud !== false && $iefhrdkhqfhqud > $ePos )
					{
						$orhrdkhqfhqudq = self::$ubrqaquhrdkqw[ $this -> parents[ $iefhrdkhqfhqud ] ];
						if( $orhrdkhqfhqudq === 1 || (isset($orhrdkhqfhqudq[ $e ])?$orhrdkhqfhqudq[ $e ]:null) )
							$isUnopen = true;
					}
				}

				if( $isUnopen )
				{
					if( $e == 'p' )
						return( $this -> juhiuh_( $resNotAllowed, '<' . $e . '></' . $e . '>' ) );
					return( '' );
				}
			}

			if( $ePos + 1 < count( $this -> parents ) && $this -> udybdywqjiy_( $e, $ePos ) )
			{
				$p = $this -> parents[ $ePos + 1 ];
				if( $p != 'p' && !$this -> udybdywqjiy_( $p, $ePos + 1 ) )
					return( '' );
			}

			$this -> byjdkfef_( $res, $resNotAllowed, $e );
			return( $res );
		}

		$selfClosed = false;

		{
			$aA = TagsNormalizer::ihjjquihqf( $m[ 3 ], null, null );
			if( !is_array( $aA ) )
				return( array( $aA ) );

			if( isset( $aA[ '/' ] ) )
			{
				unset( $aA[ '/' ] );
				$selfClosed = true;
			}

			$a = '';
			foreach( $aA as $k => $v )
			{
				if( $e == 'img' && $k == 'srcset' )
					$v = Ui::GetSrcSetAttr( Ui::ParseSrcSetAttr( $v ), !(isset($this -> opts[ 'minimize' ])?$this -> opts[ 'minimize' ]:null) );

				if( $e == 'v-select' && $k == 'disabled' )
					$k = '_seraph_accel_attr_temp_symb_void_disabled';
				$a .= ' ' . str_replace( array( '@', '#' ), array( '_seraph_accel_attr_temp_symb_40_', '_seraph_accel_attr_temp_symb_23_' ), $k );

				if( $v !== null )
					$a .= '="' . str_replace( '"', '&quot;', $v ) . '"';
			}

			unset( $aA );
		}

		$res = '';

		$tbyxshevijduhqfjsqnu = self::tbyxshevijduhqfjsqnujuw_( $e );
		if( $tbyxshevijduhqfjsqnu && ( $ePos = $this -> iefjiqbjduhqfjuw_( array( $e => 1 ) ) ) !== false )
		{
			$eParPos = $this -> iefjiqbjduhqfjuw_( $tbyxshevijduhqfjsqnu );
			if( $eParPos !== false && $ePos > $eParPos )
				$this -> byjdkfef_( $res, $resNotAllowed, $e );
		}

		$tagsRepeat = array();
		if( !$this -> udybdywqjiy_( $e ) && $this -> iefjiqbjduhqfjuw_( array( 'p' => 1 ) ) !== false )
			$tagsRepeat = $this -> byjdkfef_( $res, $resNotAllowed, 'p' );

		$this -> xikf_( $res, $resNotAllowed, $e, $a, (isset(self::$ojfcuqw[ $e ])?self::$ojfcuqw[ $e ]:null) || $selfClosed );

		foreach( $tagsRepeat as $tagRepeat )
			if( $tagRepeat[ 0 ] != 'span' )
				$this -> xikf_( $res, $resNotAllowed, $tagRepeat[ 0 ], $tagRepeat[ 1 ] );

		return( $res );
	}

	function iefjiqbjduhqfjuw_( $tagsIncl )
	{
		for( $i = count( $this -> parents ); $i > 0; $i-- )
		{
			$p = $this -> parents[ $i - 1 ];
			if( (isset($tagsIncl[ $p ])?$tagsIncl[ $p ]:null) )
				return( $i - 1 );
		}

		return( false );
	}

	static function tbyxshevijduhqfjsqnujuw_( $tag )
	{
		$res = array();
		foreach( self::$tbyxsjduhqfqw as $cpTag => $children )
			if( (isset($children[ $tag ])?$children[ $tag ]:null) )
				$res[ $cpTag ] = 1;
		return( $res );
	}

	static function tsjcs_( $t )
	{

		$t = $t[ 0 ];
		$isComment = $t[ 3 ] == '-';

		if( $isComment )
			$t = preg_replace( '`--+`', '-', substr( $t, 4, -3 ) );
		else
			$t = substr( $t, 1, -1 );

		return( str_replace( array( '&', '<', '>' ), array( "\x03", "\x04", "\x05" ), ( $isComment ? "\x01\x02\x04!--$t--\x05\x02\x01" : "\x01\x01\x04$t\x05\x01\x01" ) ) );
	}

	static private function is_digit( $s )
	{
		if( function_exists( 'ctype_digit' ) )
			return( ctype_digit( $s ) );

		for( $i = 0; $i < strlen( $s ); $i++ )
		{
			$c = ord( $s[ $i ] );
			if( $c < 0x30 || $c > 0x39 )
				return( false );
		}

		return( true );
	}

	static function jdu_( $t )
	{
		$tLast = substr( $t[ 0 ], -1 );
		if( $tLast === ';' )
			$tLast = '';

		$t = $t[ 1 ];
		if( $t[ 0 ] != '#' )
			return( '&'. ( (isset(self::$kojyjduqw[ $t ])?self::$kojyjduqw[ $t ]:null) ? $t : ( (isset(self::$dojyjduqw[ $t ])?self::$dojyjduqw[ $t ]:null) ? $t : 'amp;' . $t ) ) . ';' . $tLast );

		$t = substr( $t, 1 );
		$n = self::is_digit( $t ) ? intval( $t ) : hexdec( substr( $t, 1 ) );
		if( $n < 9 or ( $n > 13 && $n < 32 ) or $n == 11 or $n == 12 or ( $n > 126 && $n < 160 && $n != 133 ) or ( $n > 55295 && ( $n < 57344 or ( $n > 64975 && $n < 64992 ) or $n == 65534 or $n == 65535 or $n > 1114111 ) ) )
			return( '{{{MASKED_ENT_SYM:' . $t . '}}}' . $tLast );

		return( '&#'. ( self::is_digit( $t ) ? $n : 'x'. dechex( $n ) ) . ';' . $tLast );
	}
}

