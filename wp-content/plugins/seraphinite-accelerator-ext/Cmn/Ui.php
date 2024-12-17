<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

class Ui
{

	static function Link( $content, $href, $newWnd = false, $prms = null, $attrs = null )
	{
		if( $attrs === null )
			$attrs = array();

		$showLink = !empty( $href ) || (isset($prms[ 'showIfNoHref' ])?$prms[ 'showIfNoHref' ]:null);

		if( $showLink )
		{
			$resPart = '';

			$linkPreContent = (isset($prms[ 'linkPreContent' ])?$prms[ 'linkPreContent' ]:null);
			if( $linkPreContent )
				$resPart .= $linkPreContent;

			{
				$attrs[ 'href' ] = $href !== null ? $href : '';

				if( $newWnd && strpos( $attrs[ 'href' ], 'mailto:' ) !== 0 )
					$attrs[ 'target' ] = '_blank';
			}

			$resPart .= Ui::TagOpen( 'a', $attrs );

			if( is_array( $content ) )
				$content[ 0 ] = $resPart . $content[ 0 ];
			else
				$content = $resPart . $content;
		}
		else if( (isset($prms[ 'noTextIfNoHref' ])?$prms[ 'noTextIfNoHref' ]:null) )
		{
			if( !is_array( $content ) )
				return( '' );

			$content[ 0 ] = '';
			$content[ count( $content ) - 1 ] = '';
			if( count( $content ) == 3 )
				$content[ 1 ] = '';

			return( $content );
		}

		if( $showLink )
		{
			$resPart = Ui::TagClose( 'a' );

			$linkAfterContent = (isset($prms[ 'linkAfterContent' ])?$prms[ 'linkAfterContent' ]:null);
			if( $linkAfterContent )
				$resPart .= $linkAfterContent;

			if( is_array( $content ) )
				$content[ count( $content ) - 1 ] .= $resPart;
			else
				$content .= $resPart;
		}

		return( $content );
	}

	static function Label( $text, $addNames = false, $attrs = null )
	{
		if( !is_array( $attrs ) )
			$attrs = array();

		if( is_array( $text ) && count( $text ) == 2 )
		{
			$txtItems = $text[ 1 ];
			$txtArgs = array();

			foreach( $txtItems as $txtItem )
			{
				if( !is_array( $txtItem ) )
				{
					$txtArgs[] = $txtItem;
					continue;
				}

				$switchOptions = array();

				$switchDefVal = null;
				if( isset( $txtItem[ 2 ] ) )
					$switchDefVal = $txtItem[ 2 ];

				$attrsForCombo = (isset($txtItem[ 3 ])?$txtItem[ 3 ]:null);
				if( !is_array( $attrsForCombo ) )
					$attrsForCombo = array();
				Gen::SetArrField( $attrsForCombo, 'class.+', 'inline' );
				Gen::SetArrField( $attrsForCombo, 'disabled', (isset($attrs[ 'disabled' ])?$attrs[ 'disabled' ]:null) );

				foreach( $txtItem[ 1 ] as $txtItemVal )
				{
					if( isset( $txtItemVal[ 2 ] ) && $txtItemVal[ 2 ] )
						$switchDefVal = $txtItemVal[ 0 ];

					$itemAttrs = (isset($txtItemVal[ 3 ])?$txtItemVal[ 3 ]:null);
					if( !is_array( $itemAttrs ) )
						$itemAttrs = array( 'disabled' => $itemAttrs );
					$switchOptions[ $txtItemVal[ 0 ] ] = array( $txtItemVal[ 1 ], $itemAttrs );
				}

				$txtArgs[] = self::ComboBox( $txtItem[ 0 ], $switchOptions, $switchDefVal, $addNames, $attrsForCombo );
			}

			$text = vsprintf( $text[ 0 ], $txtArgs );
		}

		return( self::Tag( 'label', $text, $attrs ) );
	}

	static function CheckBox( $text, $id, $checked = false, $addNames = false, $attrs = null, $title = null, $checkAttrs = null )
	{
		if( !is_array( $checkAttrs ) )
			$checkAttrs = array();

		if( $id )
			$checkAttrs[ 'id' ] = $id;

		return( self::_CheckRadBox( 'checkbox', $text, $checkAttrs, null, $checked, $addNames, $attrs, $title ) );
	}

	static function RadioBox( $text, $idGroup, $value, $def = false, $attrs = null, $title = null, $radioAttrs = null )
	{
		if( !is_array( $radioAttrs ) )
			$radioAttrs = array();

		if( $idGroup )
			$radioAttrs[ 'name' ] = $idGroup;

		return( self::_CheckRadBox( 'radio', $text, $radioAttrs, $value, $def, false, $attrs, $title ) );
	}

	static private function _CheckRadBox( $type, $text, $attrs, $value = null, $checked = false, $addNames = false, $attrsForLabel = null, $title = null )
	{
		if( !is_array( $attrs ) )
			$attrs = array();
		if( !is_array( $attrsForLabel ) )
			$attrsForLabel = array();

		$attrs[ 'disabled' ] = (isset($attrsForLabel[ 'disabled' ])?$attrsForLabel[ 'disabled' ]:null);

		if( !empty( $title ) )
			$attrs[ 'title' ] = $attrsForLabel[ 'title' ] = $title;

		if( !empty( $checked ) )
			$attrs[ 'checked' ] = 'checked';

		$res = self::InputBox( $type, null, $value, $attrs, $addNames );

		if( is_array( $text ) && count( $text ) == 2 )
			$text[ 0 ] = $res . $text[ 0 ];
		else
			$text = $res . $text;

		return( self::Label( $text, $addNames, $attrsForLabel ) );
	}

	static function ComboBox( $id, $items, $value, $addNames = false, $attrs = null )
	{
		if( !is_array( $attrs ) )
			$attrs = array();
		if( !is_array( $items ) )
			$items = array();

		self::_AddIdName( $attrs, $id, $addNames );

		$res = '';

		foreach( $items as $itemVal => $itemText )
		{
			$itemAttrs = null;
			if( is_array( $itemText ) )
			{
				$itemAttrs = (isset($itemText[ 1 ])?$itemText[ 1 ]:null);
				$itemText = (isset($itemText[ 0 ])?$itemText[ 0 ]:null);
			}

			if( !is_array( $itemAttrs ) )
				$itemAttrs = array();

			$itemAttrs[ 'value' ] = $itemVal;
			if( $itemVal == $value )
				$itemAttrs[ 'selected' ] = '';

			$res .= self::Tag( 'option', $itemText, $itemAttrs );
		}

		return( self::Tag( 'select', $res, $attrs ) );
	}

	static function TextBox( $id, $value = null, $attrs = null, $addNames = false )
	{
		return( self::InputBox( 'text', $id, $value, $attrs, $addNames ) );
	}

	static function NumberBox( $id, $value = null, $attrs = null, $addNames = false )
	{
		return( self::InputBox( 'number', $id, $value, $attrs, $addNames ) );
	}

	static function LogItem( $severity, $text, $normalizeText = true )
	{
		if( gettype( $text ) !== 'string' )
			return;

		$icon = array( 'name' => 'dashicons-info', 'color' => 'clrWpNone' );
		switch( $severity )
		{
		case "info":		$icon = array( 'name' => "dashicons-info",			'color' => "clrWpInfo" ); break;
		case "normal":		$icon = array( 'name' => "dashicons-info",			'color' => "clrWpNormal" ); break;
		case "success":		$icon = array( 'name' => "dashicons-info",			'color' => "clrWpSucc" ); break;
		case "warning":		$icon = array( 'name' => "dashicons-warning",		'color' => "clrWpWarn" ); break;
		case "error":		$icon = array( 'name' => "dashicons-warning",		'color' => "clrWpErr" ); break;
		}

		if( $normalizeText )
		{
			$lc = substr( $text, 0, -1 );
			if( $lc != "." && $lc != "?" )
				$text .= ".";
		}

		return( Ui::Tag( "div", Ui::Tag( "div", null, array( "class" => "icon dashicons " . $icon[ 'name' ] . " " . $icon[ 'color' ] ) ) . Ui::Tag( "div", $text, array( "class" => "text" ) ), array( "class" => "logItem" ) ) );
	}

	static function EscHtml( $value, $spaces = false )
	{
		$value = htmlspecialchars( $value, ENT_COMPAT | ENT_HTML401 | ENT_QUOTES );
		if( $spaces )
			$value = str_replace( ' ', '&nbsp;', $value );
		return( $value );
	}

	private static function _AddIdName( &$attrs, $id, $addNames )
	{
		if( !empty( $id ) )
			$attrs[ 'id' ] = $id;
		else
			$id = (isset($attrs[ 'id' ])?$attrs[ 'id' ]:null);

		if( !empty( $id ) && $addNames )
		{
			$attrs[ 'name' ] = $id;
			if( $addNames == 'n' )
				unset( $attrs[ 'id' ] );
		}
	}

	static function GetStyleAttr( $attrs, $bLastSemicolon = true )
	{
		$res = '';
		foreach( $attrs as $attrKey => $attrVal )
			if( strlen( $attrKey ) && $attrVal !== null )
			{

				$res .= $attrKey . ':' . $attrVal . ';';
			}
		if( !$bLastSemicolon )
			$res = rtrim( $res, ';' );
		return( $res );
	}

	static function GetStyleSels( $sels )
	{
		$res = '';
		foreach( $sels as $sel => $attrs )
		{
			$resPart = Ui::GetStyleAttr( $attrs );
			if( $resPart )
				$res .= $sel . '{' . $resPart . '}';
		}

		return( $res );
	}

	static function ParseStyleAttr( $attrs )
	{
		$res = array();
		foreach( explode( ';', $attrs ) as $attr )
		{
			$sep = strpos( $attr, ':' );
			if( $sep === false )
				$sep = strlen( $attr );

			if( $sep !== false )
			{
				$attrKey = trim( substr( $attr, 0, $sep ) );
				$attrVal = trim( substr( $attr, $sep + 1 ) );
			}
			else
			{
				$attrKey = trim( $attr );
				$attrVal = '';
			}

			if( $attrKey )
				$res[ $attrKey ] = $attrVal;
		}

		return( $res );
	}

	static function MergeStyleAttr( $attrs, $attrsNew )
	{

		foreach( $attrsNew as $attrKey => $attrVal )
		{
			if( isset( $attrs[ $attrKey ] ) )
				$attrs[ $attrKey ] = $attrVal;
			else
				Gen::ArrSplice( $attrs, 0, 0, array( $attrKey => $attrVal ) );
		}

		return( $attrs );
	}

	static function SpacyClassAttr( $v )
	{
		return( str_replace( array( "\t", "\n", "\r", "\0", "\x0B", "\v" ), ' ', ( string )$v ) );
	}

	static function ParseClassAttr( $v )
	{
		$v = trim( Gen::StrReplaceWhileChanging( '  ', ' ', Ui::SpacyClassAttr( $v ) ), ' ' );
		return( strlen( $v ) ? explode( ' ', $v ) : array() );
	}

	static function IsSrcAttrData( $v )
	{
		return( strtolower( substr( $v, 0, 5 ) ) === 'data:' );
	}

	static function GetSrcAttrData( $v, &$mimeType = null, &$encoding = null )
	{

		$data = strpos( $v, ',' );
		if( $data === false )
			return( false );

		$prms = explode( ';', substr( $v, 5, $data - 5 ) );
		$data = trim( substr( $v, $data + 1 ) );
		$mimeType = (isset($prms[ 0 ])?$prms[ 0 ]:null);
		$encoding = (isset($prms[ count( $prms ) - 1 ])?$prms[ count( $prms ) - 1 ]:null);
		return( $encoding == 'base64' ? base64_decode( $data ) : false );
	}

	static function SetSrcAttrData( $data, $mimeType )
	{

		return( 'data:' . $mimeType . ';base64,' . base64_encode( $data ) );
	}

	static function GetSrcSetAttr( $attrs, $beauty = true )
	{
		return( implode( ', ', array_map( function( $e ) { return( implode( ' ', $e ) ); }, $attrs ) ) );

	}

	static function ParseSrcSetAttr( $v )
	{

		$res = array();
		$lastSrcData = null;

		foreach( explode( ',', $v ) as $args )
		{
			$args = array_map( 'trim', explode( ' ', Gen::StrReplaceWhileChanging( '  ', ' ', str_replace( array( "\t", "\r", "\n", "\0", "\x0B" ), ' ', trim( $args ) ) ) ) );

			if( Ui::IsSrcAttrData( $args[ 0 ] ) )
			{
				$lastSrcData = $args[ 0 ];
				continue;
			}

			if( $lastSrcData )
			{
				$args[ 0 ] = $lastSrcData . ',' . $args[ 0 ];
				$lastSrcData = null;
			}

			$res[] = $args;
		}

		return( $res );
	}

	private static function _GetTagAttrs( $attrs )
	{
		$res = '';

		if( is_array( $attrs ) )
		{
			foreach( $attrs as $attr => $attrVal )
			{
				if( $attr === 'disabled' )
				{
					if( $attrVal !== true && $attrVal !== '' )
						continue;
					$attrVal = '';
				}

				$res .= ' ' . $attr;

				if( $attrVal === '' )
					continue;

				$res .= '="';

				if( is_array( $attrVal ) )
				{
					if( $attr == "style" )
					{
						$res .= Ui::GetStyleAttr( $attrVal );
					}
					else
					{
						$first = true;
						foreach( $attrVal as $attrValItem )
						{
							if( empty( $attrValItem ) )
								continue;

							if( !$first )
								$res .= ' ';
							$res .= Ui::EscHtml( $attrValItem );

							$first = false;
						}
					}
				}
				else
					$res .= Ui::EscHtml( $attrVal );

				$res .= '"';
			}
		}
		else if( is_string( $attrs ) )
			$res .= ' ' . $attrs;

		return( $res );
	}

	static function InputBox( $type, $id, $value = null, $attrs = null, $addNames = false )
	{
		if( !is_array( $attrs ) )
			$attrs = array();

		$attrs[ 'type' ] = $type;

		self::_AddIdName( $attrs, $id, $addNames );

		if( $value !== null )
			$attrs[ 'value' ] = $value;

		$res = '';

		$masked = (isset($attrs[ 'masked' ])?$attrs[ 'masked' ]:null);
		if( $masked )
		{
			unset( $attrs[ 'id' ] );
			unset( $attrs[ 'name' ] );

			$attrs[ 'onchange' ] = 'jQuery(this.parentNode).find("input[type=\\"hidden\\"]").val(seraph_accel.Ui._MaskEncode(this.value))';

			$res .= self::TagOpen( 'span' );
			$res .= Ui::InputBox( 'hidden', $id, self::_maskEncode( @rawurlencode( $value ) ), null, $addNames );
		}

		$res .= self::Tag( 'input', null, $attrs, true );

		if( $masked )
			$res .= self::TagClose( 'span' );

		return( $res );
	}

	static function TextArea( $id, $value = null, $attrs = null, $addNames = false )
	{
		if( !is_array( $attrs ) )
			$attrs = array();

		$attrs[ 'type' ] = 'text';
		self::_AddIdName( $attrs, $id, $addNames );

		$res = '';

		$masked = (isset($attrs[ 'masked' ])?$attrs[ 'masked' ]:null);
		if( $masked )
		{
			unset( $attrs[ 'id' ] );
			unset( $attrs[ 'name' ] );

			$attrs[ 'onchange' ] = 'jQuery(this.parentNode).find("input[type=\\"hidden\\"]").val(seraph_accel.Ui._MaskEncode(this.value))';

			$res .= self::TagOpen( 'span' );
			$res .= Ui::InputBox( 'hidden', $id, self::_maskEncode( @rawurlencode( $value ) ), null, $addNames );
		}

		$res .= self::Tag( 'textarea', ( string )$value, $attrs );

		if( $masked )
			$res .= self::TagClose( 'span' );

		return( $res );
	}

	static function Button( $content, $primary = false, $nameId = null, $classesEx = null, $type = 'submit', $attrs = null )
	{
		$res = '';

		$isBtnEx = ( $type == 'button' ) && ( strpos( $content, '<' ) !== false );

		if( !$attrs )
			$attrs = array();

		if( $nameId )
			Gen::SetArrField( $attrs, 'name', $nameId );

		Gen::SetArrField( $attrs, 'type', $type );
		if( !$isBtnEx )
		{
			Gen::SetArrField( $attrs, 'value', $content );
			$content = null;
		}

		Gen::SetArrField( $attrs, 'class.+', 'button' );
		if( $primary )
			Gen::SetArrField( $attrs, 'class.+', 'button-primary' );

		if( $classesEx )
		{
			if( is_array( $classesEx ) )
			{
				foreach( $classesEx as $c )
					Gen::SetArrField( $attrs, 'class.+', $c );
			}
			else
				Gen::SetArrField( $attrs, 'class.+', $classesEx );
		}

		return( Ui::Tag( $isBtnEx ? 'button' : 'input', $content, $attrs ) );
	}

	static function Comment( $content = null )
	{
		return( '<!-- ' . $content . ' -->' );
	}

	static function Spinner( $big = false, array $attrs = null )
	{
		if( !$attrs )
			$attrs = array();
		Gen::SetArrField( $attrs, 'class.+', 'seraph_accel_spinner' . ( $big ? ' big' : '' ) );
		return( Ui::Tag( 'span', null, $attrs ) );
	}

	static function ToggleButton( $cssSelectorItemToToggle, $attrsBtn = null, $attrs = null )
	{
		if( !$attrsBtn )
			$attrsBtn = array();
		if( !$attrs )
			$attrs = array();

		Gen::SetArrField( $attrsBtn, 'style.line-height', '0' );
		Gen::SetArrField( $attrsBtn, 'style.vertical-align', 'middle' );
		Gen::SetArrField( $attrsBtn, 'onclick', 'seraph_accel.Ui._cb.ToggleButton_OnClick("' . Ui::EscHtml( $cssSelectorItemToToggle ) . '",this);return(false);' );

		return( Ui::Tag( 'div', Ui::Button( Ui::Tag( 'span', null, array( 'class' => 'dashicons dashicons-arrow-down', 'style' => array( 'margin-left' => '-0.1em' ) ) ), false, null, null, 'button', $attrsBtn ) . Ui::Spinner( false, array( 'class' => array( 'ctlSpaceBefore' ), 'style' => array( 'display' => 'none', 'vertical-align' => 'middle' ) ) ), $attrs ) );
	}

	static function Tag( $name, $content = null, $attrs = null, $selfClose = false, $prms = null )
	{
		if( $content === null )
			$content = '';

		if( (isset($prms[ 'noTagsIfNoContent' ])?$prms[ 'noTagsIfNoContent' ]:null) && empty( $content ) )
			return( $content );

		$resPart = self::TagOpen( $name, $attrs, $selfClose );
		if( $selfClose )
			return( $resPart );

		if( is_array( $content ) )
			$content[ 0 ] = $resPart . $content[ 0 ];
		else
			$content = $resPart . $content;

		$resPart = self::TagClose( $name );

		$afterContent = (isset($prms[ 'afterContent' ])?$prms[ 'afterContent' ]:null);
		if( $afterContent )
			$resPart .= $afterContent;

		if( is_array( $content ) )
			$content[ count( $content ) - 1 ] .= $resPart;
		else
			$content .= $resPart;

		return( $content );
	}

	static function TagOpen( $name, $attrs = null, $selfClose = false )
	{
		if( empty( $name ) )
			return( '' );
		return( '<' . $name . self::_GetTagAttrs( $attrs ) . ( $selfClose ? ' /' : '' ) . '>' );
	}

	static function TagClose( $name )
	{
		if( empty( $name ) )
			return( '' );
		return( '</' . $name . '>' );
	}

	static private function _maskEncode( $v )
	{
		$vOut = '';

		for( $i = 0; $i < ( function_exists( 'mb_strlen' ) ? mb_strlen( $v ) : strlen( $v ) ); $i++ )
		{
			if( $vOut )
				$vOut .= ',';
			$vOut .= function_exists( 'mb_ord' ) ? mb_ord( $v[ $i ] ) : ord( $v[ $i ] );
		}

		return( $vOut );
	}

	static private function _maskDecode( $v )
	{
		$v = strlen( $v ) ? explode( ',', $v ) : array();
		for( $i = 0; $i < count( $v ); $i++ )
			$v[ $i ] = function_exists( 'mb_chr' ) ? mb_chr( intval( $v[ $i ] ) ) : chr( intval( $v[ $i ] ) );
		return( implode( '', $v ) );
	}

	static function UnmaskValue( $value )
	{
		return( @rawurldecode( self::_maskDecode( $value ) ) );
	}

	static function TokensList( $value, $id = null, $attrs = null, $addNames = false )
	{
		$res = '';

		if( $attrs === null )
			$attrs = array();

		$masked = (isset($attrs[ 'masked' ])?$attrs[ 'masked' ]:null);

		$value = @rawurlencode( @wp_json_encode( $value ) );
		if( $masked )
			$value = self::_maskEncode( $value );
		$attrsVal = array( 'type' => 'hidden', 'value' => $value );

		self::_AddIdName( $attrs, $id, $addNames );
		if( (isset($attrs[ 'name' ])?$attrs[ 'name' ]:null) )
		{
			$attrsVal[ 'name' ] = $attrs[ 'name' ];
			unset( $attrs[ 'name' ] );
		}

		Gen::SetArrField( $attrs, 'class.+', 'seraph_accel_TokensList seraph_accel_textarea rs' );
		Gen::SetArrField( $attrs, 'style.overflow', 'scroll' );
		Gen::SetArrField( $attrs, 'style.resize', 'vertical' );

		return( Ui::Tag( 'div', Ui::TagOpen( 'input', $attrsVal, true ), $attrs ) );
	}

	static function TokensList_GetVal( $value, callable $cbItem = null, $unmask = false )
	{
		$value = @stripslashes( $value );
		if( $unmask )
			$value = self::_maskDecode( $value );
		$value = @rawurldecode( $value );

		$a = @json_decode( $value, true );
		if( !is_array( $a ) )
			return( array() );
		return( $cbItem ? @array_map( $cbItem, $a ) : $a );
	}

	const ITEMSLIST_NEWKEY	= '{{itemKey}}';

	static function ItemsList_GetNewKeyTpl( $level = 0 )
	{
		return( '{{' . $level . 'itemId}}' );
	}

	static function ItemsList( array $prms, array $items, $idItems, $cbItem, $cbEmpty, $cbArgs = null, $attrs = null, $level = 0 )
	{
		$res = '';

		$onDelItemJsCb = (isset($prms[ 'onDelItemJsCb' ])?$prms[ 'onDelItemJsCb' ]:null);
		$sortable = (isset($prms[ 'sortable' ])?$prms[ 'sortable' ]:null);
		$sortableDrag = (isset($prms[ 'sortDrag' ])?$prms[ 'sortDrag' ]:null);
		if( $sortableDrag === null )
			$sortableDrag = true;

		if( $cbEmpty )
			$res .= call_user_func( $cbEmpty, $cbArgs, array( 'class' => 'items-list-empty-content', 'style' => empty( $items ) ? array() : array( 'display' => 'none' ) ) );

		if( !$attrs )
			$attrs = array();

		Gen::SetArrField( $attrs, 'class.+', 'items-list' );

		Gen::SetArrField( $attrs, 'style.list-style-type', 'none' );
		Gen::SetArrField( $attrs, 'style.margin', 0 );
		Gen::SetArrField( $attrs, 'style.padding', 0 );
		if( empty( $items ) )
			Gen::SetArrField( $attrs, 'style.display', 'none' );

		$contentItemBegin = explode( '{{itemKey}}', Ui::TagOpen( 'li', array( 'class' => 'item {{itemKey}}' . ( $sortable ? ' ui-sortable-handle' : '' ), 'style' => array( 'margin' => 0, 'padding' => 0 ) ) ) );
		$contentItemEnd = Ui::TagClose( 'li' );

		$initCount = 0;
		$itemsLimit = (isset($prms[ 'itemsLimit' ])?$prms[ 'itemsLimit' ]:null);
		foreach( $items as $itemKey => $item )
		{
			if( $itemsLimit !== null && $initCount > $itemsLimit )
				break;

			$res .= $contentItemBegin[ 0 ] . $itemKey . $contentItemBegin[ 1 ] . call_user_func( $cbItem, $cbArgs, $idItems, $items, $itemKey, $item ) . $contentItemEnd;
			if( is_numeric( $itemKey ) )
				$initCount++;
		}

		$itemIdTpl = Ui::ItemsList_GetNewKeyTpl( $level );
		Gen::SetArrField( $attrs, 'data-oninit', 'seraph_accel.Ui.ItemsList._Init(this,"' . esc_attr( $prms[ 'editorAreaCssPath' ] ) . '",' . ( empty( $onDelItemJsCb ) ? 'null' : esc_attr( $onDelItemJsCb ) ) . ',' . esc_attr( $initCount ) . ',"' . Gen::GetJsHtmlContent( $contentItemBegin[ 0 ] . $itemIdTpl . $contentItemBegin[ 1 ] . call_user_func( $cbItem, $cbArgs, $idItems, null, $itemIdTpl, null ) . $contentItemEnd ) . '",' . ( $sortable && $sortableDrag ? 'true' : 'false' ) . ',' . esc_attr( $level ) . ')' );
		return( Ui::Tag( 'ul', $res, $attrs ) );
	}

	static function ItemsList_GetItemCssPath( $itemKey )
	{
		return( '.items-list .item.' . $itemKey );
	}

	static function ItemsList_ItemOperateBtnsTpl( array $prms, $attrs = null )
	{

		if( !$attrs )
			$attrs = array();

		$res = '';

		if( (isset($prms[ 'sortable' ])?$prms[ 'sortable' ]:null) )
		{
			$res .= Ui::Button( Ui::Tag( 'span', null, array( 'class' => 'dashicons dashicons-arrow-up', 'style' => array( 'display' => 'table-cell' ) ) ), false, null, null, 'button', array_merge( $attrs, Gen::GetArrField( $prms, 'btnsItemOperate.up.attrs', array() ) ) );
			$res .= Ui::Button( Ui::Tag( 'span', null, array( 'class' => 'dashicons dashicons-arrow-down', 'style' => array( 'display' => 'table-cell' ) ) ), false, null, null, 'button', array_merge( $attrs, Gen::GetArrField( $prms, 'btnsItemOperate.down.attrs', array() ) ) );
		}

		$res .= Ui::Button( Ui::Tag( 'span', null, array( 'class' => 'dashicons dashicons-trash', 'style' => array( 'display' => 'table-cell' ) ) ), false, null, null, 'button', array_merge( $attrs, Gen::GetArrField( $prms, 'btnsItemOperate.del.attrs', array() ) ) );

		return( $res );
	}

	static function ItemsList_ItemOperateBtns( array $prms, $attrs = null )
	{
		Gen::SetArrField( $prms, 'btnsItemOperate.up.attrs.onclick', 'seraph_accel.Ui.ItemsList.MoveItem(\'' . $prms[ 'editorAreaCssPath' ] . '\',this,-1);return false;' );
		Gen::SetArrField( $prms, 'btnsItemOperate.down.attrs.onclick', 'seraph_accel.Ui.ItemsList.MoveItem(\'' . $prms[ 'editorAreaCssPath' ] . '\',this,1);return false;' );
		Gen::SetArrField( $prms, 'btnsItemOperate.del.attrs.onclick', 'seraph_accel.Ui.ItemsList.DelItem(\'' . $prms[ 'editorAreaCssPath' ] . '\',this);return false;' );
		return( self::ItemsList_ItemOperateBtnsTpl( $prms, $attrs ) );
	}

	static function ItemsList_OperateBtnsTpl( array $prms, $attrs = null )
	{
		if( !$attrs )
			$attrs = array();

		$res = '';

		$res .= Ui::Button( esc_html_x( 'AddItemBtn', 'admin.Common_ItemsList', 'seraphinite-accelerator' ), false, null, null, 'button', array_merge( $attrs, Gen::GetArrField( $prms, 'btnsOperate.add.attrs', array() ) ) );
		$res .= Ui::Button( esc_html_x( 'DelAllItemsBtn', 'admin.Common_ItemsList', 'seraphinite-accelerator' ), false, null, null, 'button', array_merge( $attrs, Gen::GetArrField( $prms, 'btnsOperate.delAll.attrs', array() ) ) );

		return( $res );
	}

	static function ItemsList_OperateBtns( array $prms, $attrs = null )
	{
		Gen::SetArrField( $prms, 'btnsOperate.add.attrs.onclick', 'seraph_accel.Ui.ItemsList.AddItem(\'' . $prms[ 'editorAreaCssPath' ] . '\',this);return false;' );
		Gen::SetArrField( $prms, 'btnsOperate.delAll.attrs.onclick', 'seraph_accel.Ui.ItemsList.DelAllItems(\'' . $prms[ 'editorAreaCssPath' ] . '\',this);return false;' );
		return( self::ItemsList_OperateBtnsTpl( $prms, $attrs ) );
	}

	static function ItemsList_NoItemsContent( $attrs = null )
	{
		return( Ui::Tag( 'span', esc_html_x( 'NoItemsInfo', 'admin.Common_ItemsList', 'seraphinite-accelerator' ), $attrs ) );
	}

	static function ItemsList_GetSaveItems( $idItems, $sep, $request, $cbItem = null, $cbArgs = null, $rearrangeIdxs = true )
	{
		$keyItemsPrefix = $idItems . $sep;

		$resTmp = array();
		foreach( $request as $k => $v )
		{
			if( substr( $k, 0, strlen( $keyItemsPrefix ) ) !== $keyItemsPrefix )
				continue;

			$itemKey = substr( $k, strlen( $keyItemsPrefix ) );

			$posNextPath = strpos( $itemKey, $sep );
			if( $posNextPath !== false )
			{
				$itemKey = substr( $itemKey, 0, $posNextPath );
				$v = null;
			}

			if( !(isset($resTmp[ $itemKey ])?$resTmp[ $itemKey ]:null) )
				$resTmp[ $itemKey ] = $cbItem ? call_user_func( $cbItem, $cbArgs, $idItems, $itemKey, $v, $request ) : ( $v !== null ? $v : true );
		}

		if( !$rearrangeIdxs )
			return( $resTmp );

		$iCustomName = null;

		$res = array();
		foreach( $resTmp as $k => $v )
		{
			if( is_string( $rearrangeIdxs ) && is_numeric( $k ) )
			{
				$iCustomName = ( $iCustomName === null ) ? time() : ( $iCustomName + 1 );
				$k = $rearrangeIdxs . $iCustomName;
			}

			if( is_numeric( $k ) )
				$res[] = $v;
			else
				$res[ $k ] = $v;
		}

		return( $res );
	}

	static function MetaboxAdd( $id, $title, $callback, $callbacks_args = null, $screen = null, $context = 'advanced', $priority = 'default', $classesAdd = null, $classesRemove = null )
	{
		return( self::_MetaboxAdd( $id, $title, $callback, $callbacks_args, $screen, $context, $priority, array( 'seraph_accel', $classesAdd ), $classesRemove ) );
	}

	static private $g_aMetaBox_Classes = null;

	static private function _MetaboxAdd( $id, $title, $callback, $callbacks_args = null, $screen = null, $context = 'advanced', $priority = 'default', $classesAdd = null, $classesRemove = null )
	{
		if( is_string( $classesAdd ) )
			$classesAdd = array( $classesAdd );
		else if( !is_array( $classesAdd ) )
			$classesAdd = array();

		if( is_string( $classesRemove ) )
			$classesRemove = array( $classesRemove );
		else if( !is_array( $classesRemove ) )
			$classesRemove = array();

		$key = 'postbox_classes_' . get_current_screen() -> id . '_' . $id;

		self::$g_aMetaBox_Classes[ $key ] = array( 'a' => $classesAdd, 'r' => $classesRemove );

		add_meta_box( $id, Ui::Tag( 'span', $title ), $callback, $screen, $context, $priority, $callbacks_args );

		add_filter( $key,
			function( $classes )
			{
				$metaBox_Classes = self::$g_aMetaBox_Classes[ current_filter() ];

				foreach( $metaBox_Classes[ 'r' ] as $class )
					if( ( $classKey = array_search( $class, $classes ) ) !== false )
						unset( $classes[ $classKey ] );

				foreach( $metaBox_Classes[ 'a' ] as $class )
					if( array_search( $class, $classes ) === false )
						$classes[] = $class;

				return( $classes );
			}
		);
	}

	static function PostBoxes_BottomGroupPanel( $callback, $callbacks_args = null )
	{
		echo( wp_kses_post( Ui::TagOpen( 'div' ) ) );
		call_user_func( $callback, $callbacks_args );
		echo( wp_kses_post( Ui::TagClose( 'div' ) ) );
	}

	static function PostBoxes_MetaboxAdd( $id, $title, $expandable = true, $callback = null, $callbacks_args = null, $context = 'body', $classesAdd = null, $classesRemove = null, $visible = true )
	{
		if( is_string( $classesAdd ) )
			$classesAdd = array( $classesAdd );
		else if( !is_array( $classesAdd ) )
			$classesAdd = array();

		if( is_string( $classesRemove ) )
			$classesRemove = array( $classesRemove );
		else if( !is_array( $classesRemove ) )
			$classesRemove = array();

		if( !$expandable )
		{
			$classesAdd[] = 'nocollapse';
			$classesRemove[] = 'closed';
		}

		if( !$visible )
		{
			$classesAdd[] = 'ctlHidden';
			$id .= '_hidden_';
		}

		return( self::_MetaboxAdd( $id, $title, $callback, $callbacks_args, null, $context, 'default', $classesAdd, $classesRemove ) );
	}

	static function PostBoxes( $title, $metaBoxes = array( 'body' => null ), array $callbacks = null, $callbacks_args = null, $blocksAttrs = null )
	{
		wp_enqueue_script( 'postbox' );

		{
			$dropBoxes = array();
			foreach( $metaBoxes as $metaBoxId => $metaBox )
				if( $metaBox && (isset($metaBox[ 'nosort' ])?$metaBox[ 'nosort' ]:null) )
					$dropBoxes[] = $metaBoxId;

			if( count( $dropBoxes ) )
			{
				$userId = get_current_user_id();
				$userOptId = 'meta-box-order_' . get_current_screen() -> id;

				$sorted = get_user_option( $userOptId, $userId );
				$modified = false;
				foreach( $dropBoxes as $dropBoxId )
				{
					if( isset( $sorted[ $dropBoxId ] ) )
					{
						unset( $sorted[ $dropBoxId ] );
						$modified = true;
					}
				}

				if( $modified )
					update_user_option( $userId, $userOptId, $sorted );
			}
		}

		$modeClass = '';
		if( isset( $metaBoxes[ 'side' ] ) )
			$modeClass = ' columns-2';
		else if( isset( $metaBoxes[ 'normal' ] ) )
			$modeClass = ' columns-1';

		if( $blocksAttrs === null )
			$blocksAttrs = array();

		Gen::SetArrField( $blocksAttrs, 'wrap.class.+', 'wrap' );
		Gen::SetArrField( $blocksAttrs, 'wrap.class.+', 'seraph_accel' );

		?>

		<?php echo( wp_kses( Ui::TagOpen( 'div', $blocksAttrs[ 'wrap' ] ), Wp::GetKsesSanitizeCtx( 'admin' ) ) ); ?>
			<h1><?php echo( wp_kses_post( ( string )$title ) ); ?></h1>

			<?php

				$cbHeader = (isset($callbacks[ 'header' ])?$callbacks[ 'header' ]:null);
				if( $cbHeader )
					call_user_func( $cbHeader, $callbacks_args );

				wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
				wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
			?>

			<div id="poststuff">
				<div id="post-body" class="metabox-holder<?php echo( esc_attr( $modeClass ) ); ?>">
					<div id="post-body-content">
						<?php

						{
							$cb = (isset($callbacks[ 'bodyContentBegin' ])?$callbacks[ 'bodyContentBegin' ]:null);
							if( $cb )
								call_user_func( $cb, $callbacks_args );
						}

						{
							{
								$cb = (isset($callbacks[ 'body' ])?$callbacks[ 'body' ]:null);
								if( $cb )
									call_user_func( $cb, $callbacks_args );
							}

							{
								if( isset( $metaBoxes[ 'body' ] ) )
									do_meta_boxes( '', 'body', null );
							}
						}

						{
							$cb = (isset($callbacks[ 'bodyContentEnd' ])?$callbacks[ 'bodyContentEnd' ]:null);
							if( $cb )
								call_user_func( $cb, $callbacks_args );
						}

						?>
					</div>

					<?php if( isset( $metaBoxes[ 'side' ] ) ) { ?>
						<div id="postbox-container-1" class="postbox-container">
							<?php do_meta_boxes( '', 'side', null ); ?>
						</div>
					<?php } ?>

					<?php if( isset( $metaBoxes[ 'normal' ] ) ) { ?>
						<div id="postbox-container-2" class="postbox-container">
							<?php do_meta_boxes( '', 'normal', null ); ?>
						</div>
					<?php } ?>
				</div><!-- #post-body -->
			</div><!-- #poststuff -->

		</div><!-- .wrap -->

		<script>
			jQuery( document ).on( 'ready',
				function( $ )
				{
					postboxes.add_postbox_toggles( pagenow );

					var ctlMetaboxHolder = jQuery( "#post-body.metabox-holder" );
					<?php

					foreach( $metaBoxes as $metaBoxId => $metaBox )
					{
						if( !$metaBox || !(isset($metaBox[ 'nosort' ])?$metaBox[ 'nosort' ]:null) )
							continue;

					?>_MetaboxesBlock_DisableSortable( ctlMetaboxHolder, "<?php echo( esc_attr( $metaBoxId ) ); ?>" );
					<?php

					}

					?>

					jQuery( ".postbox.nocollapse" ).each(
						function()
						{
							var e = jQuery( this );
							e.find( ".hndle" ).unbind( "click" );
							e.find( ".handlediv" ).remove();
						}
					);

					function _MetaboxesBlock_DisableSortable( ctlMetaboxHolder, id )
					{
						var ctl = ctlMetaboxHolder.find( "#" + id + "-sortables" );
						ctl.sortable( "disable" );
						ctl.addClass( "nosort" );
					}
				}
			);
		</script>

		<?php

		echo( wp_kses( Ui::ViewInitContent( '.wrap.seraph_accel' ), Wp::GetKsesSanitizeCtx( 'script' ) ) );
	}

	static function ViewInitContent( $viewCssSelector )
	{
		return( Ui::ScriptInlineContent( 'document.addEventListener("DOMContentLoaded",function(){seraph_accel.Ui.Init(jQuery("' . $viewCssSelector . '").get(0));})' ) );
	}

	static function PostBoxes_Popup( $id, $title, $callback = null, $callbacks_args = null )
	{
		$boxId = 'seraph_accel_popup_' . $id;
		$popupSide = $boxId . '_container';

		self::PostBoxes_MetaboxAdd( $boxId, $title, false, $callback, $callbacks_args, $popupSide );
		do_meta_boxes( '', $popupSide, $callbacks_args );

        ?>

		<script>
			(function()
			{
				var popupId = "#<?php echo( esc_attr( $boxId ) ); ?>";
				var block = jQuery( "#<?php echo( esc_attr( $boxId ) ); ?>" );
				
				var closeBtn = block.find( ".handlediv" );
				closeBtn.html( "" );
				closeBtn.addClass( "notice-dismiss" );
				closeBtn.removeClass( "handlediv" );
				closeBtn.css( "position", "relative" );

				closeBtn.on( "click", function(){ seraph_accel.Ui.PopupClose( "<?php echo( esc_attr( $id ) ); ?>" ); } );
			})();
		</script>
		
		<?php
	}

	static function SettBlock_Begin( $attrs = null )
	{
		if( $attrs === null )
			$attrs = array();

		Gen::SetArrField( $attrs, 'class.+', 'form-table' );
		Gen::SetArrField( $attrs, 'class.+', 'settings' );

		return( Ui::TagOpen( 'table', $attrs ) . Ui::TagOpen( 'tbody' ) );
	}

	static function SettBlock_End()
	{
		return( Ui::TagClose( 'tbody' ) . Ui::TagClose( 'table' ) );
	}

	static function SettBlock_Item_Begin( $label, $attrs = null )
	{
		if( $attrs === null )
			$attrs = array();

		$attrs[ 'valign' ] = 'top';

		$res = '';

		$res .= '<tr ' . self::_GetTagAttrs( $attrs ) . '>';
		$res .= '<th scope="row">' . $label . '</th>';
		$res .= '<td>';
		$res .= '<fieldset>';

		return( $res );
	}

	static function SettBlock_Item_End()
	{
		$res = '';

		$res .= '</fieldset></td></tr>';

		return( $res );
	}

	static function SettBlock_ItemSubTbl_Begin( $attrs = null )
	{
		if( !$attrs )
			$attrs = array();

		Gen::SetArrField( $attrs, 'class.+', 'sub' );
		Gen::SetArrField( $attrs, 'border', '0' );
		Gen::SetArrField( $attrs, 'cellpadding', '0' );
		Gen::SetArrField( $attrs, 'cellspacing', '0' );

		return( Ui::TagOpen( 'table', $attrs ) . Ui::TagOpen( 'tbody' ) );
	}

	static function SettBlock_ItemSubTbl_End()
	{
		return( Ui::TagClose( 'tbody' ) . Ui::TagClose( 'table' ) );
	}

	const MsgInfo					= 0;
	const MsgSucc					= 1;
	const MsgWarn					= 2;
	const MsgErr					= 3;

	const MsgOptDismissible			= 0x00000001;
	const MsgOptFade				= 0x00000002;

	static function BannerMsg( $severity, $text, $opts = 0, $attrs = NULL )
	{
		if( empty( $text ) )
			return( '' );

		if( !is_array( $attrs ) )
			$attrs = array();

		$class = '';
		switch( $severity )
		{
			case Ui::MsgSucc:		$class .= 'notice notice-success'; break;
			case Ui::MsgWarn:		$class .= 'notice notice-warning'; break;
			case Ui::MsgErr:		$class .= 'notice notice-error'; break;

			default:				$class .= 'notice notice-info'; break;
		}

		$class .= ' is-dismissible';
		if( !( $opts & Ui::MsgOptDismissible ) )
			$class .= ' seraph_accel_dismiss_hidden';
		if( $opts & Ui::MsgOptFade )
			$class .= ' fade';

		Gen::SetArrField( $attrs, 'class.+', $class );

		$res = Ui::TagOpen( 'div', $attrs );

		$res .= '<div class="seraph_accel"><p class="content">' . $text . '</p></div>';

		$res .= Ui::TagClose( 'div' );

		return( $res );
	}

	const AdminHelpBtnModeBlockHeader		= 'blkhdr';
	const AdminHelpBtnModeChkRad			= 'chkrad';
	const AdminHelpBtnModeText				= 'txt';
	const AdminHelpBtnModeBtn				= 'bttn';

	const AdminBtn_Help						= 'dashicons-editor-help';
	const AdminBtn_Paid						= 'dashicons-admin-network';

	static function AdminBtnsBlock( $items, $mode )
	{
		$res = '';

		foreach( $items as $item )
		{
			if( $item === null )
				continue;

			$newWnd = (isset($item[ 'newWnd' ])?$item[ 'newWnd' ]:null);
			if( $newWnd === null )
				$newWnd = true;

			$prms = array();
			$linkParams = \apply_filters( 'seraph_accel_Ui_AdminBtnsBlock_Link', array( 'content' => null, 'attrs' => array( 'class' => array( 'dashicons', $item[ 'type' ] ) ) ), $item[ 'type' ] );

			if( $item[ 'type' ] == Ui::AdminBtn_Paid )
				$prms[ 'showIfNoHref' ] = true;
			else
				$prms[ 'noTextIfNoHref' ] = true;

			$res .= Ui::Link( $linkParams[ 'content' ], (isset($item[ 'href' ])?$item[ 'href' ]:null), $newWnd, $prms, $linkParams[ 'attrs' ] );
		}

		return( Ui::Tag( 'span', Ui::Tag( 'span', $res, array( 'class' => array( $mode ) ) ), array( 'class' => array( 'mbtns' ) ) ) );
	}

	static function AdminHelpBtn( $href, $mode = Ui::AdminHelpBtnModeText, $newWnd = true )
	{
		return( Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => $href, 'newWnd' => $newWnd ) ), $mode ) );
	}

	static function SepLine( $tag = 'div', $attrs = null )
	{
		if( !$attrs )
			$attrs = array();
		Gen::SetArrField( $attrs, 'class.+', 'hndle postbox-header' );
		return( Ui::Tag( $tag, null, $attrs ) );
	}

	static function Script( $src, $ver = null, $attrs = null )
	{
		if( !$attrs )
			$attrs = array();
		$attrs[ 'type' ] = 'text/javascript';
		$attrs[ 'src' ] = empty( $ver ) ? $src : add_query_arg( array( 'v' => $ver ), $src );
		return( Ui::Tag( 'script', null, $attrs ) );
	}

	static function ScriptInline( $src, $ver = null, $attrs = null )
	{
		return( Ui::ScriptInlineContent( @file_get_contents( $src ), $ver, $attrs ) );
	}

	static function ScriptInlineContent( $content, $ver = null, $attrs = null )
	{
		if( !$attrs )
			$attrs = array();
		if( !isset( $attrs[ 'type' ] ) )
			$attrs[ 'type' ] = 'text/javascript';
		return( Ui::Tag( 'script', $content, $attrs ) );
	}

	static function Style( $src, $ver = null, $attrs = null )
	{
		if( !$attrs )
			$attrs = array();
		$attrs[ 'href' ] = empty( $ver ) ? $src : add_query_arg( array( 'v' => $ver ), $src );
		$attrs[ 'rel' ] = 'stylesheet';
		return( Ui::Tag( 'link', null, $attrs, true ) );
	}

	static function StyleInline( $src, $ver = null, $attrs = null )
	{

		return( Ui::StyleInlineContent( @file_get_contents( $src ), $ver, $attrs ) );
	}

	static function StyleInlineContent( $content, $ver = null, $attrs = null )
	{
		return( Ui::Tag( 'style', $content, $attrs ) );
	}

	static function TagGetPos( $tag, $data, $offset = 0 )
	{
		$posBegin = Ui::TagBeginGetPos( $tag, $data, $offset );
		$posEnd = Ui::TagEndGetPos( $tag, $data, $posBegin ? $posBegin[ 1 ] : $offset );
		return( array( $posBegin, $posEnd ) );
	}

	static function TagBeginGetPos( $tag, $data, $offset = 0, $bSkipComments = false )
	{
		if( !is_array( $tag ) )
			$tag = array( $tag, strtoupper( $tag ) );
		$tag = array_map( function( $e ) { return( '<' . $e ); }, $tag );
		$tagS = '@' . implode( '|', array_map( function( $s ) { return( preg_quote( $s, '@' ) ); }, $bSkipComments ? array( '<!--', $tag[ 0 ], $tag[ 1 ] ) : $tag ) ) . '@';

		for( ;; )
		{
			$pos = array();
			if( !preg_match( $tagS, $data, $pos, PREG_OFFSET_CAPTURE, $offset ) )
				return( false );
			$pos = $pos[ 0 ][ 1 ];

			if( $bSkipComments && substr( $data, $pos + 1, 1 ) == '!' )
			{
				$offset = strpos( $data, '-->', $pos + 4 );
				if( $offset === false )
					return( false );

				$offset += 3;
				continue;
			}

			$posEnd = $pos + strlen( $tag[ 0 ] );
			$c = substr( $data, $posEnd, 1 );
			$posEnd ++;
			if( $c == '>' )
				return( array( $pos, $posEnd, $posEnd - $pos ) );

			if( $c == " " || $c == "\t" || $c == "\n" || $c == "\r" || $c == "\0" || $c == "\x0B" )
				break;

			$offset = $posEnd;
		}

		$posEnd = strpos( $data, '>', $posEnd );
		if( $posEnd === false )
			return( false );

		$posEnd ++;
		return( array( $pos, $posEnd, $posEnd - $pos ) );
	}

	static function TagEndGetPos( $tag, $data, $offset = 0, $bSkipComments = false )
	{
		if( !is_array( $tag ) )
			$tag = array( $tag, strtoupper( $tag ) );
		$tag = array_map( function( $e ) { return( '</' . $e . '>' ); }, $tag );
		$tagS = '@' . implode( '|', array_map( function( $s ) { return( preg_quote( $s, '@' ) ); }, $bSkipComments ? array( '<!--', $tag[ 0 ], $tag[ 1 ] ) : $tag ) ) . '@';

		for( ;; )
		{
			$pos = array();
			if( !preg_match( $tagS, $data, $pos, PREG_OFFSET_CAPTURE, $offset ) )
				return( false );
			$pos = $pos[ 0 ][ 1 ];

			if( $bSkipComments && substr( $data, $pos + 1, 1 ) == '!' )
			{
				$offset = strpos( $data, '-->', $pos + 4 );
				if( $offset === false )
					return( false );

				$offset += 3;
				continue;
			}

			break;
		}

		$n = strlen( $tag[ 0 ] );
		$posEnd = $pos + $n;
		return( array( $pos, $posEnd, $n ) );
	}
}

class UiPopups
{
	static private $items = NULL;

	static function Add( $id, $prms )
	{
		self::$items[ $id ] = $prms;
	}

	static function Draw()
	{
		if( empty( self::$items ) )
			return;

		$needModal = false;
		foreach( self::$items as $id => $prms )
		{
			if( $prms[ 'modal' ] )
				$needModal = true;

			if( $prms[ 'cbPre' ] )
				call_user_func( $prms[ 'cbPre' ], $prms );

			$attrs = $prms[ 'attrs' ];
			if( !$attrs )
				$attrs = array();

			ob_start();
			call_user_func( $prms[ 'cb' ], $id, $prms );
			$body = ob_get_clean();

			$attrs[ 'id' ] = 'seraph_accel_popup_' . $id;
			$attrs[ 'style' ][ 'display' ] = 'none';
			$attrs[ 'data-attr-modal' ][ 'display' ] = $prms[ 'modal' ];
			$attrs[ 'data-attr-body' ] = rawurlencode( $body );

			Gen::SetArrField( $attrs, 'class.+', 'seraph_accel popup' );

			echo( wp_kses( Ui::Tag( 'div', null, $attrs ), Wp::GetKsesSanitizeCtx( 'admin' ) ) );
		}

		if( $needModal )
			echo( wp_kses( Ui::Tag( 'div', null, array( 'class' => 'seraph_accel popup_modal_overlay', 'style' => array( 'display' => 'none' ) ) ), Wp::GetKsesSanitizeCtx( 'admin' ) ) );
	}
}

