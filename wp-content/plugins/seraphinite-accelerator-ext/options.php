<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

function GetSupportedEncodingTypes()
{
	return( array(
		'br'		=> esc_html_x( 'Brotli', 'admin.Settings_EncTypes', 'seraphinite-accelerator' ),
		'gzip'		=> esc_html_x( 'GZip', 'admin.Settings_EncTypes', 'seraphinite-accelerator' ),
		'deflate'	=> esc_html_x( 'Deflate', 'admin.Settings_EncTypes', 'seraphinite-accelerator' ),
		'compress'	=> esc_html_x( 'Compress', 'admin.Settings_EncTypes', 'seraphinite-accelerator' ),
		''			=> esc_html_x( 'None', 'admin.Settings_EncTypes', 'seraphinite-accelerator' ),
	) );
}

function GetSupportedCompressionTypes()
{
	return( array(
		'brotli'	=> esc_html_x( 'Brotli', 'admin.Settings_ComprTypes', 'seraphinite-accelerator' ),
		'deflate'	=> esc_html_x( 'Deflate', 'admin.Settings_ComprTypes', 'seraphinite-accelerator' ),
		''			=> esc_html_x( 'None', 'admin.Settings_ComprTypes', 'seraphinite-accelerator' ),
	) );
}

function SettTimeoutEditor_GetMins( $v )
{
	return( intval( $v % 60 ) );
}

function SettTimeoutEditor_GetHours( $v )
{
	return( intval( ( $v / ( 60 ) ) % ( 24 ) ) );
}

function SettTimeoutEditor_GetDays( $v, $dayStartAt1 = false )
{
	return( intval( ( $v / ( 24 * 60 ) ) ) + ( $dayStartAt1 ? 1 : 0 ) );
}

function _SettTimeoutEditor( $fldId, $v, $txt, $tag = 'label', $fldsAdd = array(), $dayStartAt1 = false )
{
	return( Ui::Tag( $tag, vsprintf( $txt, array_merge( array(
		Ui::NumberBox( $fldId . 'Mins', SettTimeoutEditor_GetMins( $v ), array( 'class' => 'inline', 'min' => 0, 'placeholder' => _x( 'TimeoutMinsPlchldr', 'admin.Settings_Cache_Common', 'seraphinite-accelerator' ), 'style' => array( 'width' => '4em' ) ), true ),
		Ui::NumberBox( $fldId . 'Hours', SettTimeoutEditor_GetHours( $v ), array( 'class' => 'inline', 'min' => 0, 'placeholder' => _x( 'TimeoutHoursPlchldr', 'admin.Settings_Cache_Common', 'seraphinite-accelerator' ), 'style' => array( 'width' => '4em' ) ), true ),
		Ui::NumberBox( $fldId . 'Days', SettTimeoutEditor_GetDays( $v, $dayStartAt1 ), array( 'class' => 'inline', 'min' => 0, 'placeholder' => _x( 'TimeoutDaysPlchldr', 'admin.Settings_Cache_Common', 'seraphinite-accelerator' ), 'style' => array( 'width' => '4em' ) ), true ),
	), $fldsAdd ) ) ) );
}

function _SettOutputCompressionTypes( $types, $fldId, $v, $disableNone = false )
{
	$o = '';

	$i = 0;
	foreach( $types as $comprType => $comprTypeLbl )
	{
		if( $i && !( $i % 3 ) )
			$o .= ( Ui::TagClose( 'tr' ) . Ui::TagOpen( 'tr' ) );

		$o .= ( Ui::TagOpen( 'td' ) );
		{
			$o .= ( Ui::CheckBox( $comprTypeLbl, 'seraph_accel/' . $fldId . '/' . $comprType, in_array( $comprType, $v ), true, array( 'disabled' => ( $disableNone && $comprType === '' ) ? true : null ) ) );
		}
		$o .= ( Ui::TagClose( 'td' ) );

		$i++;
	}

	return( $o );
}

function _SettOutputCookiesEditor( $fldId, $v, $ns )
{
	return( _SettOutputTokensEditor( $fldId, $v, _x( 'CookiesPhlr', 'admin.Settings', 'seraphinite-accelerator' ), $ns, "\n", 5, true ) );
}

function _SettOutputArgsEditor( $fldId, $v, $ns )
{
	return( _SettOutputTokensEditor( $fldId, $v, _x( 'ArgsPhlr', 'admin.Settings_Exclusions_Args', 'seraphinite-accelerator' ), $ns, ',' ) );
}

function _SettOutputArgs2Editor( $fldId, $v, $ns )
{
	return( _SettOutputTokensEditor( $fldId, $v, _x( 'Args2Phlr', 'admin.Settings_Exclusions_Args', 'seraphinite-accelerator' ), $ns, "\n", 5, true ) );
}

function _SettOutputTblCondEditor( $fldId, $v, $ns )
{
	return( _SettOutputTokensEditor( $fldId, $v, _x( 'TblCondPhlr', 'admin.Settings_Tbls', 'seraphinite-accelerator' ), $ns, ';' ) );
}

function _SettOutputAgentsEditor( $fldId, $v, $placeholder, $ns )
{
	return( _SettOutputTokensEditor( $fldId, $v, $placeholder, $ns, "\n", 5, true ) );
}

function _SettOutputScriptsEditor( $fldId, $v, $placeholder, $ns, $sep = "\n", $height = 5 )
{
	return( _SettOutputTokensEditor( $fldId, $v, $placeholder, $ns, $sep, $height, true ) );
}

function _SettOutputStylesEditor( $fldId, $v, $ns )
{
	return( _SettOutputTokensEditor( $fldId, $v, _x( 'StylesPhlr', 'admin.Settings_Styles_Editor', 'seraphinite-accelerator' ), $ns, "\n", 5, true ) );
}

function _SettOutputCdnTypesEditor( $fldId, $v, $ns )
{
	return( _SettOutputTokensEditor( $fldId, $v, _x( 'TypesPhlr', 'admin.Settings_Cdns', 'seraphinite-accelerator' ), $ns, ',' ) );
}

function _SettOutputCdnExlsEditor( $fldId, $v, $ns )
{
	return( _SettOutputTokensEditor( $fldId, $v, _x( 'ExlsPhlr', 'admin.Settings_Cdns', 'seraphinite-accelerator' ), $ns, ',' ) );
}

function _SettOutputTokensEditor( $fldId, $v, $placeholder, $ns, $sep = "\n", $height = 5, $masked = false )
{
	$o = '';

	$o .= ( Ui::TokensList( $v, $ns . '/' . $fldId, array( 'masked' => $masked, 'class' => 'vals ctlSpaceVAfter', 'style' => array( 'min-height' => '3em', 'height' => '' . $height . 'em', 'max-height' => '20em' ), 'data-oninit' => 'seraph_accel.Ui.TokensList.InitItems( this, true )' ), true ) );

	$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'class' => 'std', 'style' => array( 'width' => '100%' ) ) ) . Ui::TagOpen( 'tr' ) );
	{
		if( $sep === "\n" )
			$o .= ( Ui::Tag( 'td', Ui::TextArea( null, null, array( 'class' => 'val', 'wrap' => 'off', 'placeholder' => $placeholder, 'style' => array( 'width' => '100%', 'height' => '1em', 'max-height' => '15em', 'line-height' => '1.5' ) ) ), array( 'class' => 'wp-pwd'  ) ) );
		else
			$o .= ( Ui::Tag( 'td', Ui::TextBox( null, '', array( 'class' => 'val', 'placeholder' => $placeholder, 'style' => array( 'width' => '100%' ) ) ) ) );
		$o .= ( Ui::Tag( 'td',
			Ui::Button( esc_html( Wp::GetLocString( array( 'AddItemBtn', 'admin.Common_ItemsList' ), null, 'seraphinite-accelerator' ) ), false, null, array( 'ctlSpaceAfterSm' ), 'button', array( 'onclick' => 'seraph_accel.Settings._int.StrItem_OnAdd( this, ' . @json_encode( $sep ) . ' ); return false;' ) ) .
			Ui::Button( Ui::Tag( 'span', null, array( 'class' => 'dashicons dashicons-admin-page', 'style' => array( 'display' => 'table-cell' ) ) ), false, null, array( 'ctlSpaceAfterSm' ), 'button', array( 'onclick' => 'seraph_accel.Settings._int.StrItem_OnCopyAll( this ); return false;' ) ) .
			Ui::Button( Ui::Tag( 'span', null, array( 'class' => 'dashicons dashicons-trash', 'style' => array( 'display' => 'table-cell' ) ) ), false, null, null, 'button', array( 'onclick' => 'seraph_accel.Settings._int.StrItem_OnDelAll( this ); return false;' ) )
		, array( 'style' => array( 'width' => '1px', 'text-wrap' => 'nowrap' ) ) ) );
	}
	$o .= ( Ui::TagClose( 'tr' ) . Ui::SettBlock_ItemSubTbl_End() );

	return( $o );
}

function _SettCdnAddrEditor( $id, $v = '' )
{
	$siteCdnUrl = str_replace( '//', '//cdn.', Wp::GetSiteRootUrl() );
	return( Ui::TextBox( $id, $v, array( 'style' => array( 'width' => '100%' ), 'placeholder' => sprintf( _x( 'AddrPhlr_%1$s', 'admin.Settings_Cdn_Item', 'seraphinite-accelerator' ), $siteCdnUrl ) ), true ) );
}

function _SettCacheOps( $sett, $fldIdEx, $ns, $bSrv = false )
{
	$aOps = array( 2 => esc_html_x( 'OpDelCmbItem', 'admin.Settings_Cache_Revalidate', 'seraphinite-accelerator' ), 0 => esc_html_x( 'OpRevalidateCmbItem', 'admin.Settings_Cache_Revalidate', 'seraphinite-accelerator' ) );
	if( $bSrv )
		$aOps[ 10 ] = esc_html_x( 'OpSrvDelCmbItem', 'admin.Settings_Cache_Revalidate', 'seraphinite-accelerator' );
	return( Ui::ComboBox( $ns . '/' . $fldIdEx, $aOps, Gen::GetArrField( $sett, $fldIdEx, 0, '/' ), true, array( 'class' => 'inline' ) ) );
}

function _SettingsPage()
{

	if( isset( $_REQUEST[ 'wizard' ] ) )
	{
		_SettingsWizardPage();
		return;
	}

	Plugin::CmnScripts( array( 'Cmn', 'Gen', 'Ui', 'Net', 'AdminUi' ) );
	wp_register_script( Plugin::ScriptId( 'Admin' ), add_query_arg( Plugin::GetFileUrlPackageParams(), Plugin::FileUrl( 'Admin.js', __FILE__ ) ), array_merge( array( 'jquery' ), Plugin::CmnScriptId( array( 'Cmn', 'Gen', 'Ui', 'Net' ) ) ), '2.22.16' );
	Plugin::Loc_ScriptLoad( Plugin::ScriptId( 'Admin' ) );
	wp_enqueue_script( Plugin::ScriptId( 'Admin' ) );

	Plugin::DisplayAdminFooterRateItContent();

	$adminMsModes = Wp::GetMultisiteAdminModes();

	$isPaidLockedContent = false;

	$rmtCfg = PluginRmtCfg::Get();
	$sett = Plugin::SettGet();
	$dtCurLoc = new \DateTime( 'now', DateTimeZone::FromOffset( Wp::GetGmtOffset() ) );

	{
		Ui::PostBoxes_MetaboxAdd( 'navigator', esc_html_x( 'Title', 'admin.Settings_Nav', 'seraphinite-accelerator' ) . Ui::Tag( 'span', Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Navigator' ) ) ), Ui::AdminHelpBtnModeBlockHeader ) ), false,
			function( $callbacks_args, $box )
			{
				extract( $box[ 'args' ] );

				$o = '';

				$o .= ( Ui::Tag( 'p', Plugin::SwitchToExt(), null, false, array( 'noTagsIfNoContent' => true, 'afterContent' => Ui::SepLine( 'p' ) ) ) );

				$o .= ( Ui::TagOpen( 'p' ) );
				{
					$fldId = 'settScopeView';

					$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'data-oninit' => 'jQuery(this).find("input[type=\"radio\"]").on("change",function(){var ctlEnum=jQuery(this).closest("table");seraph_accel.Ui.ComboShowDependedItems(ctlEnum.get(0),ctlEnum.closest("#navigator").parent().get(0),"ns-nav")})' ) ) );
					{
						$o .= ( Ui::TagOpen( 'tr' ) );
						{
							$o .= ( Ui::Tag( 'td', Ui::RadioBox( Wp::safe_html_x( 'SimpleRad', 'admin.Settings_Nav', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, 'simple', true ) ) );
							$o .= ( Ui::Tag( 'td', Ui::RadioBox( Wp::safe_html_x( 'CacheRad', 'admin.Settings_Nav', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, 'cache' ) ) );
							$o .= ( Ui::Tag( 'td', Ui::RadioBox( Wp::safe_html_x( 'ScriptsRad', 'admin.Settings_Nav', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, 'scripts' ) ) );
							$o .= ( Ui::Tag( 'td', Ui::RadioBox( Wp::safe_html_x( 'OtherRad', 'admin.Settings_Nav', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, 'other' ) ) );
						}
						$o .= ( Ui::TagClose( 'tr' ) );

						$o .= ( Ui::TagOpen( 'tr' ) );
						{
							$o .= ( Ui::Tag( 'td' ) );
							$o .= ( Ui::Tag( 'td', Ui::RadioBox( Wp::safe_html_x( 'VarsRad', 'admin.Settings_Nav', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, 'vars' ) ) );
							$o .= ( Ui::Tag( 'td', Ui::RadioBox( Wp::safe_html_x( 'ContentRad', 'admin.Settings_Nav', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, 'content' ) ) );
							$o .= ( Ui::Tag( 'td' ) );
						}
						$o .= ( Ui::TagClose( 'tr' ) );
					}
					$o .= ( Ui::SettBlock_ItemSubTbl_End() );
				}
				$o .= ( Ui::TagClose( 'p' ) );

				if( $adminMsModes[ 'local' ] )
					$o .= ( Ui::Button( Wp::GetLocString( array( 'WizStartBtn', 'admin.Common_Settings' ), null, 'seraphinite-accelerator' ), true, null, 'ctlSpaceAfter ctlVaMiddle', 'button', array( 'onclick' => 'window.location.href="' . add_query_arg( array( 'wizard' => 1 ), menu_page_url( 'seraph_accel_settings', false ) ) . '";return false;' ) ) );

				echo( $o );
			},
			get_defined_vars()
		);

		Ui::PostBoxes_MetaboxAdd( 'cache', esc_html_x( 'Title', 'admin.Settings_Cache', 'seraphinite-accelerator' ) . Ui::Tag( 'span', Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Cache' ) ) ), Ui::AdminHelpBtnModeBlockHeader ) ), true,
			function( $callbacks_args, $box )
			{
				extract( $box[ 'args' ] );

				$o = '';

				$o .= ( Ui::SettBlock_Begin() );
				{
					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Cache_Common', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Cache_Gen' ) ) ), Ui::AdminHelpBtnModeText ), array( 'style' => array( 'display' => $adminMsModes[ 'local' ] ? null : 'none' ) ) ) );
					{
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin() );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/enable';
									$o .= ( Ui::CheckBox( esc_html_x( 'EnableChk', 'admin.Settings_Cache_Common', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, true, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr', array( 'style' => array( 'display' => $adminMsModes[ 'local' ] ? null : 'none' ) ) ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/autoProc';
									$o .= ( Ui::CheckBox( esc_html_x( 'AutoProcChk', 'admin.Settings_Cache_Revalidate', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/normAgent';
									$o .= ( Ui::CheckBox( esc_html_x( 'NormAgentChk', 'admin.Settings_Cache_Revalidate', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Cache_AcceptEncodings', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Cache_Encodings' ) ) ), Ui::AdminHelpBtnModeText ), array( 'style' => array( 'display' => $adminMsModes[ 'local' ] ? null : 'none' ) ) ) );
					{
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin() );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$fldId = 'cache/encs';
								$v = Gen::GetArrField( $sett, $fldId, array(), '/' );
								$o .= ( _SettOutputCompressionTypes( GetSupportedEncodingTypes(), $fldId, $v, true ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Cache_DataCompression', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Cache_Compression' ) ) ), Ui::AdminHelpBtnModeText ), array( 'style' => array( 'display' => $adminMsModes[ 'local' ] ? null : 'none' ) ) ) );
					{
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin() );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$fldId = 'cache/dataCompr';
								$v = Gen::GetArrField( $sett, $fldId, array(), '/' );
								$o .= ( _SettOutputCompressionTypes( GetSupportedCompressionTypes(), $fldId, $v ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );

						$o .= ( Ui::SettBlock_ItemSubTbl_Begin() );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/useDataComprAssets';
									$o .= ( Ui::CheckBox( esc_html_x( 'UseDataComprAssetsChk', 'admin.Settings_Cache_Common', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, true, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/dataLvl';
									$o .= ( Ui::Label( sprintf( esc_html_x( 'DataDirLevels_%1$s', 'admin.Settings_Cache_Common', 'seraphinite-accelerator' ), Ui::TextBox( 'seraph_accel/' . $fldId, implode( ':', Gen::GetArrField( $sett, $fldId, array(), '/' ) ), array( 'style' => array( 'width' => '7em' ) ), true ) ) ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Cache_SamePartsOpt', 'seraphinite-accelerator' ) . Ui::Tag( 'span', Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Cache_Sep' ) ), Plugin::AdminBtnsBlock_GetPaidContent( $isPaidLockedContent ) ), Ui::AdminHelpBtnModeText ) ), array( 'style' => array( 'display' => $adminMsModes[ 'local' ] ? null : 'none' ) ) ) );
					{
						$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'class' => 'blck ctlMaxSizeX', 'style' => array( 'display' => 'none' ) ) ) );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$fldId = 'cache/chunks/enable';
								$o .= ( Ui::Tag( 'td', Ui::CheckBox( esc_html_x( 'EnableChk', 'admin.Settings_Cache_SamePartsOpt', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$fldId = 'cache/chunks/js';
								$o .= ( Ui::Tag( 'td', Ui::CheckBox( esc_html_x( 'EnableJsChk', 'admin.Settings_Cache_SamePartsOpt', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ), array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$fldId = 'cache/chunks/css';
								$o .= ( Ui::Tag( 'td', Ui::CheckBox( esc_html_x( 'EnableCssChk', 'admin.Settings_Cache_SamePartsOpt', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ), array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) . Ui::TagOpen( 'td' ) );
							{
								$fldId = 'cache/chunks/seps';
								$itemsListPrms = array( 'editorAreaCssPath' => '.blck', 'sortable' => true );

								$o .= ( Ui::Tag( 'p', Ui::ItemsList( $itemsListPrms, Gen::GetArrField( $sett, $fldId, array(), '/' ), 'seraph_accel/' . $fldId,
									function( $cbArgs, $idItems, $vals, $itemKey, $item )
									{
										extract( $cbArgs );

										$o = '';

										$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'class' => array( 'std', 'ctlMaxSizeX ctlSpaceVAfter' ) ) ) . Ui::TagOpen( 'tr' ) );
										{
											{
												$fldId = 'enable';
												$o .= ( Ui::Tag( 'td', Ui::CheckBox( null, $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, true, '/' ), true ), array( 'class' => 'ctlMinSizeX' ) ) );
											}

											{
												$fldId = 'sel';
												$o .= ( Ui::Tag( 'td', Ui::TextBox( $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, '', '/' ), array( 'masked' => true, 'class' => 'ctlMaxSizeX', 'placeholder' => _x( 'SelectorPhlr', 'admin.Settings_Cache_SamePartsOpt', 'seraphinite-accelerator' ) ), true ) ) );
											}

											{
												$fldIdVal = Gen::GetArrField( $item, 'side', 1 | 2, '/' );
												$o .= ( Ui::Tag( 'td', Ui::CheckBox( esc_html_x( 'BeforeChk', 'admin.Settings_Cache_SamePartsOpt', 'seraphinite-accelerator' ), $idItems . '/' . $itemKey . '/before', $fldIdVal & 1, true ), array( 'class' => 'ctlMinSizeX ctlNoWrap' ) ) );
												$o .= ( Ui::Tag( 'td', Ui::CheckBox( esc_html_x( 'AfterChk', 'admin.Settings_Cache_SamePartsOpt', 'seraphinite-accelerator' ), $idItems . '/' . $itemKey . '/after', $fldIdVal & 2, true ), array( 'class' => 'ctlMinSizeX ctlNoWrap' ) ) );
											}

											$o .= ( Ui::Tag( 'td', Ui::ItemsList_ItemOperateBtns( $itemsListPrms, array( 'class' => 'ctlSpaceAfterSm' ) ), array( 'class' => 'ctlMinSizeX ctlNoWrap' ) ) );
										}
										$o .= ( Ui::TagClose( 'tr' ) . Ui::SettBlock_ItemSubTbl_End() );

										return( $o );
									},

									function( $cbArgs, $attrs )
									{
										return( Ui::ItemsList_NoItemsContent( $attrs ) );
									},

									get_defined_vars(), array( 'class' => 'ctlMaxSizeX' )
								) ) );

								$o .= ( Ui::Tag( 'p', Ui::ItemsList_OperateBtns( $itemsListPrms, array( 'class' => 'ctlSpaceAfter' ) ) ) );
							}
							$o .= ( Ui::TagClose( 'td' ) . Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );

					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Cache_Headers', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Cache_Headers' ) ) ), Ui::AdminHelpBtnModeText ), array( 'style' => array( 'display' => $adminMsModes[ 'local' ] ? null : 'none' ) ) ) );
					{
						$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck', 'style' => array( 'display' => 'none' ) ) ) );
						{
							$fldId = 'cache/hdrs';
							$o .= ( _SettOutputTokensEditor( $fldId, Gen::GetArrField( $sett, $fldId, array(), '/' ), _x( 'HdrsPhlr', 'admin.Settings_Cache_Headers', 'seraphinite-accelerator' ), 'seraph_accel', "\n", 5, true ) );
						}
						$o .= ( Ui::TagClose( 'div' ) );
					}
					$o .= ( Ui::SettBlock_Item_End() );
				}
				$o .= ( Ui::SettBlock_End() );

				echo( $o );
			},
			get_defined_vars(), 'body', 'ctlInitHidden ns-nav-cache', null, $adminMsModes[ 'local' ]
		);

		Ui::PostBoxes_MetaboxAdd( 'revalidate', esc_html_x( 'Title', 'admin.Settings_Cache_Revalidate', 'seraphinite-accelerator' ) . Ui::Tag( 'span', Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Revalidate' ) ) ), Ui::AdminHelpBtnModeBlockHeader ) ), true,
			function( $callbacks_args, $box )
			{
				extract( $box[ 'args' ] );

				$o = '';

				$o .= ( Ui::SettBlock_Begin() );
				{
					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Cache_Revalidate_Cmn', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Cache_Revalidate_Cmn' ) ) ), Ui::AdminHelpBtnModeText ), array( 'style' => array( 'display' => $adminMsModes[ 'local' ] ? null : 'none' ) ) ) );
					{
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'class' => 'blck ctlMaxSizeX' ) ) );
						{
							$o .= ( Ui::TagOpen( 'tr', array( 'style' => array( 'display' => null ) ) ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/lazyInv';
									$o .= ( Ui::CheckBox( esc_html_x( 'LazyInvChk', 'admin.Settings_Cache_Common', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( Plugin::AdminBtnsBlock_GetPaidContent( $isPaidLockedContent ) ), Ui::AdminHelpBtnModeChkRad ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, true, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr', array( 'style' => array( 'display' => null ) ) ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
								{
									$fldId = 'cache/lazyInvInitTmp';
									$o .= ( Ui::CheckBox( esc_html_x( 'LazyInvInitTmpChk', 'admin.Settings_Cache_Common', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( Plugin::AdminBtnsBlock_GetPaidContent( $isPaidLockedContent ) ), Ui::AdminHelpBtnModeChkRad ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, true, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr', array( 'style' => array( 'display' => null ) ) ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
								{
									$fldId = 'cache/lazyInvForcedTmp';
									$o .= ( Ui::CheckBox( esc_html_x( 'LazyInvForcedTmpChk', 'admin.Settings_Cache_Common', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( Plugin::AdminBtnsBlock_GetPaidContent( $isPaidLockedContent ) ), Ui::AdminHelpBtnModeChkRad ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, true, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr', array( 'style' => array( 'display' => null ) ) ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
								{
									$fldId = 'cache/lazyInvTmp';
									$o .= ( Ui::CheckBox( esc_html_x( 'LazyInvTmpChk', 'admin.Settings_Cache_Common', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( Plugin::AdminBtnsBlock_GetPaidContent( $isPaidLockedContent ) ), Ui::AdminHelpBtnModeChkRad ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, true, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Cache_Revalidate_UpdPost', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Cache_Revalidate_UpdPost' ) ) ), Ui::AdminHelpBtnModeText ), array( 'style' => array( 'display' => $adminMsModes[ 'local' ] ? null : 'none' ) ) ) );
					{
						$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'class' => 'blck ctlMaxSizeX', 'style' => array( 'display' => 'none' ) ) ) );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'class' => 'blck' ) ) );
								{
									$fldId = 'cache/updPost';
									$fldIdEx = 'cache/updPostOp';
									$o .= ( Ui::CheckBox( sprintf( esc_html_x( 'UpdPostChk_%1$s', 'admin.Settings_Cache_Revalidate', 'seraphinite-accelerator' ), _SettCacheOps( $sett, $fldIdEx, 'seraph_accel' ) ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );

									$fldId = 'cache/updPostDeps';
									$o .= ( _SettOutputTokensEditor( $fldId, Gen::GetArrField( $sett, $fldId, array(), '/' ), _x( 'UpdPostDepsPhlr', 'admin.Settings_Cache_Revalidate', 'seraphinite-accelerator' ), 'seraph_accel', "\n", 5, true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'class' => 'blck' ) ) );
								{
									$fldId = 'cache/updPostMeta';
									$o .= ( Ui::CheckBox( esc_html_x( 'UpdPostMetaChk', 'admin.Settings_Cache_Revalidate', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );

									$fldId = 'cache/updPostMetaExcl';
									$o .= ( _SettOutputTokensEditor( $fldId, Gen::GetArrField( $sett, $fldId, array(), '/' ), _x( 'UpdPostMetaExclsPhlr', 'admin.Settings_Cache_Revalidate', 'seraphinite-accelerator' ), 'seraph_accel', "\n", 5, true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/updPostDelay';
									$o .= ( Ui::Label( sprintf( esc_html_x( 'UpdPostDelay_%1$s', 'admin.Settings_Cache_Revalidate', 'seraphinite-accelerator' ), Ui::NumberBox( 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, 0, '/' ), array( 'min' => 0, 'placeholder' => '0', 'style' => array( 'width' => '5em' ) ), true ) ) ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Cache_Revalidate_UpdTerms', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Cache_Revalidate_UpdTerms' ) ) ), Ui::AdminHelpBtnModeText ), array( 'style' => array( 'display' => $adminMsModes[ 'local' ] ? null : 'none' ) ) ) );
					{
						$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'class' => 'blck ctlMaxSizeX', 'style' => array( 'display' => 'none' ) ) ) );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/updTerms';
									$fldIdEx = 'cache/updTermsOp';
									$o .= ( Ui::CheckBox( sprintf( esc_html_x( 'UpdTermsChk_%1$s', 'admin.Settings_Cache_Revalidate', 'seraphinite-accelerator' ), _SettCacheOps( $sett, $fldIdEx, 'seraph_accel' ) ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, null, '/' ), true ) );

									$fldId = 'cache/updTermsDeps';
									$o .= ( _SettOutputTokensEditor( $fldId, Gen::GetArrField( $sett, $fldId, array(), '/' ), _x( 'UpdTermsDepsPhlr', 'admin.Settings_Cache_Revalidate', 'seraphinite-accelerator' ), 'seraph_accel', ",", 5, true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Cache_Revalidate_UpdAll', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Cache_Revalidate_UpdAll' ) ) ), Ui::AdminHelpBtnModeText ), array( 'style' => array( 'display' => $adminMsModes[ 'local' ] ? null : 'none' ) ) ) );
					{
						$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'class' => 'blck ctlMaxSizeX', 'style' => array( 'display' => 'none' ) ) ) );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/updAllDeps';
									$o .= ( _SettOutputTokensEditor( $fldId, Gen::GetArrField( $sett, $fldId, array(), '/' ), _x( 'UpdAllDepsPhlr', 'admin.Settings_Cache_Revalidate', 'seraphinite-accelerator' ), 'seraph_accel', "\n", 5, true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Cache_Revalidate_Proc', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Cache_Revalidate_Proc' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'class' => 'blck ctlMaxSizeX', 'style' => array( 'display' => 'none' ) ) ) );
						{
							$o .= ( Ui::TagOpen( 'tr', array( 'style' => array( 'display' => $adminMsModes[ 'global' ] ? null : 'none' ) ) ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/maxProc';
									$o .= ( Ui::Label( sprintf( esc_html_x( 'MaxProc_%1$s', 'admin.Settings_Cache_Revalidate', 'seraphinite-accelerator' ), Ui::NumberBox( 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, 1, '/' ), array( 'min' => 1, 'placeholder' => '1', 'style' => array( 'width' => '4em' ) ), true ) ) ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr', array( 'style' => array( 'display' => $adminMsModes[ 'global' ] ? null : 'none' ) ) ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/procInterval';
									$o .= ( Ui::Label( sprintf( esc_html_x( 'ProcInterval_%1$s', 'admin.Settings_Cache_Revalidate', 'seraphinite-accelerator' ), Ui::NumberBox( 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, 1, '/' ), array( 'min' => 0, 'placeholder' => '0', 'style' => array( 'width' => '4em' ) ), true ) ) ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr', array( 'style' => array( 'display' => $adminMsModes[ 'global' ] ? null : 'none' ) ) ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/procIntervalShort';
									$o .= ( Ui::Label( sprintf( esc_html_x( 'ProcIntervalShort_%1$s', 'admin.Settings_Cache_Revalidate', 'seraphinite-accelerator' ), Ui::NumberBox( 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, 1, '/' ), array( 'min' => 0, 'placeholder' => '0', 'style' => array( 'width' => '4em' ) ), true ) ) ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr', array( 'style' => array( 'display' => $adminMsModes[ 'global' ] ? null : 'none' ) ) ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId1 = 'cache/procWorkInt';
									$fldId2 = 'cache/procPauseInt';
									$o .= ( Ui::Label( sprintf( esc_html_x( 'ProcWorkPauseInts_%1$s%2$s', 'admin.Settings_Cache_Revalidate', 'seraphinite-accelerator' ), Ui::NumberBox( 'seraph_accel/' . $fldId1, Gen::GetArrField( $sett, $fldId1, 0.0, '/' ), array( 'min' => 0, 'step' => '0.01', 'placeholder' => '0.0', 'style' => array( 'width' => '5em' ) ), true ), Ui::NumberBox( 'seraph_accel/' . $fldId2, Gen::GetArrField( $sett, $fldId2, 0.0, '/' ), array( 'min' => 0, 'step' => '0.01', 'placeholder' => '0.0', 'style' => array( 'width' => '5em' ) ), true ) ) ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr', array( 'style' => array( 'display' => $adminMsModes[ 'local' ] ? null : 'none' ) ) ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/procMemLim';
									$o .= ( Ui::Label( sprintf( esc_html_x( 'MemLim_%1$s', 'admin.Settings_Cache_Revalidate', 'seraphinite-accelerator' ), Ui::NumberBox( 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, 0, '/' ), array( 'min' => '1', 'style' => array( 'width' => '7em' ) ), true ) ) ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr', array( 'style' => array( 'display' => $adminMsModes[ 'local' ] ? null : 'none' ) ) ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/procTmLim';
									$o .= ( Ui::Label( sprintf( esc_html_x( 'TimeLim_%1$s', 'admin.Settings_Cache_Revalidate', 'seraphinite-accelerator' ), Ui::NumberBox( 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, 1, '/' ), array( 'min' => 1, 'placeholder' => '1', 'style' => array( 'width' => '5em' ) ), true ) ) ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Cache_Revalidate_Intervals', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Cache_Revalidate_Intervals' ) ) ), Ui::AdminHelpBtnModeText ), array( 'style' => array( 'display' => $adminMsModes[ 'local' ] ? null : 'none' ) ) ) );
					{
						$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'class' => 'blck ctlMaxSizeX', 'style' => array( 'display' => 'none' ) ) ) );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/updByTimeout';
									$o .= ( Ui::CheckBox( esc_html_x( 'UpdByTimeoutChk', 'admin.Settings_Cache_Common', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
								{
									$fldId = 'cache/timeout';
									$o .= ( _SettTimeoutEditor( 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, 0, '/' ), esc_html_x( 'Timeout_%1$s%2$s%3$s', 'admin.Settings_Cache_Common', 'seraphinite-accelerator' ) ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/timeoutFr';
									$o .= ( Ui::Label( sprintf( esc_html_x( 'TimeoutFragments_%1$s', 'admin.Settings_Cache_Common', 'seraphinite-accelerator' ), Ui::NumberBox( 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, 0, '/' ), array( 'min' => 0, 'placeholder' => '0', 'style' => array( 'width' => '5em' ) ), true ) ) ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/timeoutCln';
									$o .= ( _SettTimeoutEditor( 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, 0, '/' ), esc_html_x( 'TimeoutCln_%1$s%2$s%3$s', 'admin.Settings_Cache_Common', 'seraphinite-accelerator' ) ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
								{
									$fldId = 'cache/useTimeoutClnForWpNonce';
									$o .= ( Ui::CheckBox( esc_html_x( 'TimeoutClnForWpNonceChk', 'admin.Settings_Cache_Common', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/autoClnPeriod';
									$o .= ( _SettTimeoutEditor( 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, 0, '/' ), esc_html_x( 'AutoClnPeriod_%1$s%2$s%3$s', 'admin.Settings_Cache_Common', 'seraphinite-accelerator' ) ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Cache_Revalidate_Sche', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Cache_Revalidate_Sche' ) ) ), Ui::AdminHelpBtnModeText ), array( 'style' => array( 'display' => $adminMsModes[ 'local' ] ? null : 'none' ) ) ) );
					{
						$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );

						$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck', 'style' => array( 'display' => 'none' ) ) ) );
						{
							$fldId = 'cache/updSche';
							$itemsListPrms = array( 'editorAreaCssPath' => '.blck', 'sortable' => true );
							$items = Gen::GetArrField( $sett, $fldId, array(), '/' );

							$o .= ( Ui::ItemsList( $itemsListPrms, $items, 'seraph_accel/' . $fldId,
								function( $cbArgs, $idItems, $items, $itemKey, $item )
								{
									extract( $cbArgs );

									if( $item === null )
										$item = Gen::GetArrField( OnOptGetDef_Sett(), 'cache/updSche/def', array(), '/' );

									$o = '';

									$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck' ) ) );
									{
										{
											$fldId = 'enable';
											$fldIdEx = 'period';
											$fldIdEx1 = 'periodN';

											$bEnabled = Gen::GetArrField( $item, $fldId, false, '/' );

											$o .= ( Ui::CheckBox( sprintf( esc_html_x( 'UpdChk_%1$s%2$s', 'admin.Settings_Cache_Revalidate_Sche_Item', 'seraphinite-accelerator' ),
												Ui::ComboBox( $idItems . '/' . $itemKey . '/' . $fldIdEx, array( 0 => esc_html_x( 'PerMinute', 'admin.Settings_Cache_Revalidate_Sche_Item', 'seraphinite-accelerator' ), 1 => esc_html_x( 'PerHour', 'admin.Settings_Cache_Revalidate_Sche_Item', 'seraphinite-accelerator' ), 24 => esc_html_x( 'PerDay', 'admin.Settings_Cache_Revalidate_Sche_Item', 'seraphinite-accelerator' ), 168 => esc_html_x( 'PerWeek', 'admin.Settings_Cache_Revalidate_Sche_Item', 'seraphinite-accelerator' ), 720 => esc_html_x( 'PerMonth', 'admin.Settings_Cache_Revalidate_Sche_Item', 'seraphinite-accelerator' ), 8760 => esc_html_x( 'PerYear', 'admin.Settings_Cache_Revalidate_Sche_Item', 'seraphinite-accelerator' ) ), Gen::GetArrField( $item, $fldIdEx, 0, '/' ), true, array( 'class' => 'inline period', 'data-oninit' => 'jQuery(this).change()', 'onchange' => 'seraph_accel.Ui.ComboShowDependedItems( this, jQuery( this.parentNode ).closest( ".blck" ).first().get( 0 ) )' ) ),
												Ui::NumberBox( $idItems . '/' . $itemKey . '/' . $fldIdEx1, Gen::GetArrField( $item, $fldIdEx1, 1, '/' ), array( 'class' => 'inline', 'min' => 1, 'placeholder' => '1', 'style' => array( 'width' => '4em' ) ), true )
												), $idItems . '/' . $itemKey . '/' . $fldId, $bEnabled, true ) );
										}

										$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck ctlSpaceVBefore ctlSpaceVAfter' ) ) );
										{
											$fldId = 'times';
											$itemsList2Prms = array( 'editorAreaCssPath' => '.blck', 'sortable' => true );

											$o .= ( Ui::ItemsList( $itemsList2Prms, Gen::GetArrField( $item, $fldId, array(), '/' ), $idItems . '/' . $itemKey . '/' . $fldId,
												function( $cbArgs, $idItems, $vals, $itemKey, $item )
												{
													extract( $cbArgs );

													$o = '';

													$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'class' => array( 'std', 'ctlMaxSizeX' ), 'data-oninitnew' => 'jQuery( this.parentNode ).closest( ".blck" ).parent().closest( ".blck" ).find( ".period" ).first().change()' ) ) . Ui::TagOpen( 'tr' ) );
													{

														$o .= ( Ui::TagOpen( 'td' ) );
														{
															$fldId = 'tm';
															$fldIdEx = 'm';
															$fldIdEx2 = 's';
															$o .= ( _SettTimeoutEditor( $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, 0, '/' ), esc_html_x( 'Time_%1$s%2$s%3$s%4$s%5$s%6$s%7$s%8$s%9$s%10$s%11$s%12$s%13$s', 'admin.Settings_Cache_Revalidate_Sche_Item', 'seraphinite-accelerator' ), 'span',
																array_merge(
																	array( Ui::ComboBox( $idItems . '/' . $itemKey . '/' . $fldIdEx, array( 1 => esc_html( Wp::GetLocString( 'January' ) ), 2 => esc_html( Wp::GetLocString( 'February' ) ), 3 => esc_html( Wp::GetLocString( 'March' ) ), 4 => esc_html( Wp::GetLocString( 'April' ) ), 5 => esc_html( Wp::GetLocString( 'May' ) ), 6 => esc_html( Wp::GetLocString( 'June' ) ), 7 => esc_html( Wp::GetLocString( 'July' ) ), 8 => esc_html( Wp::GetLocString( 'August' ) ), 9 => esc_html( Wp::GetLocString( 'September' ) ), 10 => esc_html( Wp::GetLocString( 'October' ) ), 11 => esc_html( Wp::GetLocString( 'November' ) ), 12 => esc_html( Wp::GetLocString( 'December' ) ) ), Gen::GetArrField( $item, $fldIdEx, 0, '/' ) + 1, true, array( 'class' => 'inline' ) ) ),
																	Ui::Tag( 'span', array( '', '' ), array( 'class' => 'ns-8760' ) ),
																	Ui::Tag( 'span', array( '', '' ), array( 'class' => 'ns-8760 ns-720 ns-168' ) ),
																	Ui::Tag( 'span', array( '', '' ), array( 'class' => 'ns-8760 ns-720 ns-168 ns-24' ) ),
																	Ui::Tag( 'span', array( '', '' ), array( 'class' => 'ns-8760 ns-720 ns-168 ns-24 ns-1' ) ),
																	array( Ui::NumberBox( $idItems . '/' . $itemKey . '/' . $fldIdEx2, Gen::GetArrField( $item, $fldIdEx2, 0, '/' ), array( 'class' => 'inline', 'min' => 0, 'placeholder' => '0', 'style' => array( 'width' => '4em' ) ), true ) )
																), true
															) );
														}
														$o .= ( Ui::TagClose( 'td' ) );

														$o .= ( Ui::Tag( 'td', Ui::ItemsList_ItemOperateBtns( $itemsList2Prms, array( 'class' => 'ctlSpaceBeforeSm' ) ), array( 'class' => 'ctlMinSizeX ctlNoWrap' ) ) );
													}
													$o .= ( Ui::TagClose( 'tr' ) . Ui::SettBlock_ItemSubTbl_End() );

													if( Gen::GetArrField( $itemParent, 'enable', false ) && $vals && count( $vals ) > 1 )
													{
														$tmRun = CacheOperScheduler_ItemTime_GetNextRunTime( $itemParent, $item, $dtCurLoc );
														if( $tmRun )
															$o .= ( Ui::Tag( 'p', sprintf( esc_html_x( 'NextRunDescr_%1$s', 'admin.Settings_Cache_Revalidate_Sche_Item', 'seraphinite-accelerator' ), date_i18n( DateTime::RFC2822, $tmRun + $dtCurLoc -> getOffset() ) ), array( 'class' => 'description' ) ) );
													}

													$o .= ( Ui::Tag( 'div', null, array( 'class' => 'ctlSpaceVAfter' ) ) );

													return( $o );
												},

												function( $cbArgs, $attrs )
												{
													return( Ui::ItemsList_NoItemsContent( $attrs ) );
												},

												array( 'isPaidLockedContent' => $isPaidLockedContent, 'rmtCfg' => $rmtCfg, 'itemsList2Prms' => $itemsList2Prms, 'itemParent' => $item, 'dtCurLoc' => $dtCurLoc ), array( 'class' => 'ctlMaxSizeX' ), 1
											) );

											$o .= ( Ui::Tag( 'div', Ui::ItemsList_OperateBtns( $itemsList2Prms, array( 'class' => 'ctlSpaceBefore' ) ), array( 'style' => array( 'text-align' => 'right' ) ) ) );
										}
										$o .= ( Ui::TagClose( 'div' ) );

										{
											$fldId = 'op';
											$fldIdEx = 'prior';
											$o .= ( Ui::Label( sprintf( esc_html_x( 'UpdLbl_%1$s%2$s', 'admin.Settings_Cache_Revalidate_Sche_Item', 'seraphinite-accelerator' ),
												_SettCacheOps( $item, $fldId, $idItems . '/' . $itemKey, true ),
												Ui::ComboBox(
													$idItems . '/' . $itemKey . '/' . $fldIdEx,
													array(
														'7'					=> esc_html_x( 'PriorNormal', 'admin.Settings_Cache_Revalidate_Sche_Item', 'seraphinite-accelerator' ),
														'3'		=> esc_html_x( 'PriorHigh', 'admin.Settings_Cache_Revalidate_Sche_Item', 'seraphinite-accelerator' ),
													),
													Gen::GetArrField( $item, $fldIdEx, 7, '/' ), true, array( 'class' => array( 'inline' ) ) )
											) ) );
										}

										{
											$fldId = 'deps';
											$o .= ( _SettOutputTokensEditor( $fldId, Gen::GetArrField( $item, $fldId, array(), '/' ), _x( 'UpdPostDepsPhlr', 'admin.Settings_Cache_Revalidate', 'seraphinite-accelerator' ), $idItems . '/' . $itemKey, "\n", 5, true ) );
										}

										$o .= ( Ui::Tag( 'div', Ui::ItemsList_ItemOperateBtns( $itemsListPrms, array( 'class' => 'ctlSpaceAfterSm' ) ), array( 'class' => 'ctlSpaceVBefore ctlSpaceVAfter' ) ) );

										if( $bEnabled && $items && count( $items ) > 1 )
										{
											$tmRun = CacheOperScheduler_Item_GetNextRunTime( $item, $dtCurLoc );
											if( $tmRun )
												$o .= ( Ui::Tag( 'p', sprintf( esc_html_x( 'NextRunDescr_%1$s', 'admin.Settings_Cache_Revalidate_Sche_Item', 'seraphinite-accelerator' ), date_i18n( DateTime::RFC2822, $tmRun + $dtCurLoc -> getOffset() ) ), array( 'class' => 'description ctlSpaceVAfter' ) ) );
										}
									}
									$o .= ( Ui::TagClose( 'div' ) );

									$o .= ( Ui::SepLine( 'div', array( 'style' => array( 'margin-bottom' => '2em' ) ) ) );

									return( $o );
								},

								function( $cbArgs, $attrs )
								{
									Gen::SetArrField( $attrs, 'class.+', 'ctlSpaceVAfter' );
									return( Ui::Tag( 'div', Ui::Tag( 'label', Ui::ItemsList_NoItemsContent() ), $attrs ) );
								},

								get_defined_vars(), array( 'class' => 'ctlMaxSizeX' )
							) );

							$o .= ( Ui::Tag( 'div', Ui::ItemsList_OperateBtns( $itemsListPrms, array( 'class' => 'ctlSpaceAfter', 'style' => array( 'margin-left' => 0 ) ) ), array( 'class' => 'ctlSpaceVBefore' ) ) );

							if( array_search( true, array_column( $items, 'enable' ), true ) !== false )
								$o .= ( Ui::Tag( 'p', Ui::Tag( 'strong', sprintf( esc_html_x( 'NextRunDescr_%1$s', 'admin.Settings_Cache_Revalidate_Sche_Item', 'seraphinite-accelerator' ), date_i18n( DateTime::RFC2822, Plugin::AsyncTaskGetTime( 'CacheNextScheduledOp', null, true ) + $dtCurLoc -> getOffset() ) ) ), array( 'class' => 'description ctlSpaceVBefore' ) ) );
						}
						$o .= ( Ui::TagClose( 'div' ) );
					}
					$o .= ( Ui::SettBlock_Item_End() );

				}
				$o .= ( Ui::SettBlock_End() );

				echo( $o );
			},
			get_defined_vars(), 'body', 'ctlInitHidden ns-nav-cache'
		);

		Ui::PostBoxes_MetaboxAdd( 'cacheBr', esc_html_x( 'Title', 'admin.Settings_CacheBrowser', 'seraphinite-accelerator' ) . Ui::Tag( 'span', Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_CacheBrowser' ) ) ), Ui::AdminHelpBtnModeBlockHeader ) ), true,
			function( $callbacks_args, $box )
			{
				extract( $box[ 'args' ] );

				$o = '';

				$o .= ( Ui::SettBlock_Begin() );
				{
					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_CacheBrowser_Common', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_CacheBrowser_Gen' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin() );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cacheBr/enable';
									$o .= ( Ui::CheckBox( esc_html_x( 'EnableChk', 'admin.Settings_Cache_Common', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, true, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cacheBr/timeout';
									$o .= ( _SettTimeoutEditor( 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, 0, '/' ), esc_html_x( 'Timeout_%1$s%2$s%3$s', 'admin.Settings_CacheBrowser_Common', 'seraphinite-accelerator' ) ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );
				}
				$o .= ( Ui::SettBlock_End() );

				echo( $o );
			},
			get_defined_vars(), 'body', 'ctlInitHidden ns-nav-cache', null, $adminMsModes[ 'local' ] && Gen::HtAccess_IsSupported()
		);

		Ui::PostBoxes_MetaboxAdd( 'server', esc_html_x( 'Title', 'admin.Settings_Cache_Srv', 'seraphinite-accelerator' ) . Ui::Tag( 'span', Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Cache_Srv' ) ) ), Ui::AdminHelpBtnModeBlockHeader ) ), true,
			function( $callbacks_args, $box )
			{
				extract( $box[ 'args' ] );

				$o = '';

				$o .= ( Ui::SettBlock_Begin() );
				{
					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Cache_Srv_Gen', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Cache_Srv_Gen' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin() );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/srv';
									$o .= ( Ui::CheckBox( esc_html_x( 'SrvChk', 'admin.Settings_Cache_Srv', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/srvClr';
									$o .= ( Ui::CheckBox( esc_html_x( 'SrvUpdChk', 'admin.Settings_Cache_Srv', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Cache_Srv_Nginx', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Cache_Srv_Nginx' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'class' => 'std ctlMaxSizeX' ) ) );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/nginx/fastCgiDir';
									$o .= ( Ui::Label( sprintf( esc_html_x( 'FastCgiCacheDir_%1$s', 'admin.Settings_Cache_Srv_Nginx', 'seraphinite-accelerator' ), Ui::TextBox( 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, '', '/' ), array( 'style' => array( 'width' => '100%' ) ), true ) ), false, array( 'class' => 'ctlMaxSizeX' ) ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/nginx/fastCgiLevels';
									$o .= ( Ui::Label( sprintf( esc_html_x( 'FastCgiCacheLevels_%1$s', 'admin.Settings_Cache_Srv_Nginx', 'seraphinite-accelerator' ), Ui::TextBox( 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, '', '/' ), array( 'style' => array( 'width' => '7em' ) ), true ) ) ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Cache_Srv_Sucuri', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Cache_Srv_Sucuri' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'class' => 'std ctlMaxSizeX' ) ) );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/sucuri/apiKey';
									$o .= ( Ui::Label( sprintf( esc_html_x( 'ApiKey_%1$s', 'admin.Settings_Cache_Srv_Sucuri', 'seraphinite-accelerator' ), Ui::TextBox( 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, '', '/' ), array( 'style' => array( 'width' => '100%' ) ), true ) ), false, array( 'class' => 'ctlMaxSizeX' ) ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/sucuri/apiSecret';
									$o .= ( Ui::Label( sprintf( esc_html_x( 'ApiSecret_%1$s', 'admin.Settings_Cache_Srv_Sucuri', 'seraphinite-accelerator' ), Ui::TextBox( 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, '', '/' ), array( 'style' => array( 'width' => '100%' ) ), true ) ), false, array( 'class' => 'ctlMaxSizeX' ) ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );
				}
				$o .= ( Ui::SettBlock_End() );

				echo( $o );
			},
			get_defined_vars(), 'body', 'ctlInitHidden ns-nav-cache', null, $adminMsModes[ 'local' ]
		);

		Ui::PostBoxes_MetaboxAdd( 'exclusions', esc_html_x( 'Title', 'admin.Settings_Exclusions', 'seraphinite-accelerator' ) . Ui::Tag( 'span', Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Exclusions' ) ) ), Ui::AdminHelpBtnModeBlockHeader ) ), true,
			function( $callbacks_args, $box )
			{
				extract( $box[ 'args' ] );

				$o = '';

				$o .= ( Ui::SettBlock_Begin() );
				{
					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Exclusions_Uris', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Exclusions_Paths' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{

						$o .= ( Ui::ToggleButton( '#exclusionsUris', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::TagOpen( 'div', array( 'id' => 'exclusionsUris', 'class' => 'blck', 'style' => array( 'display' => 'none' ) ) ) );
						{
							$fldId = 'cache/urisExcl';
							$o .= ( _SettOutputTokensEditor( $fldId, Gen::GetArrField( $sett, $fldId, array(), '/' ), _x( 'UrisPhlr', 'admin.Settings_Exclusions_Args', 'seraphinite-accelerator' ), 'seraph_accel', "\n", 5, true ) );
						}
						$o .= ( Ui::TagClose( 'div' ) );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Exclusions_Args', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Exclusions_Args' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{

						$o .= ( Ui::ToggleButton( '#exclusionsArgs', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'id' => 'exclusionsArgs', 'class' => 'std', 'style' => array( 'width' => '100%', 'display' => 'none' ) ) ) );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'class' => 'blck' ) ) );
								{
									{
										$fldId = 'cache/exclArgsAll';
										$o .= ( Ui::CheckBox( esc_html_x( 'AllExclChk', 'admin.Settings_Exclusions_Args', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, true, '/' ), true ) );
									}

									{
										$fldId = 'cache/exclArgs';
										$o .= ( _SettOutputArgs2Editor( $fldId, Gen::GetArrField( $sett, $fldId, array(), '/' ), 'seraph_accel' ) );
									}
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'class' => 'blck' ) ) );
								{
									{
										$fldId = 'cache/skipArgsAll';
										$o .= ( Ui::CheckBox( esc_html_x( 'AllSkipChk', 'admin.Settings_Exclusions_Args', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
									}

									{
										$fldId = 'cache/skipArgs';
										$o .= ( _SettOutputArgs2Editor( $fldId, Gen::GetArrField( $sett, $fldId, array(), '/' ), 'seraph_accel' ) );
									}
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Exclusions_Agents', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Exclusions_Agents' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{

						$o .= ( Ui::ToggleButton( '#exclusionsAgents', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'id' => 'exclusionsAgents', 'class' => 'std', 'style' => array( 'width' => '100%', 'display' => 'none' ) ) ) );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'class' => 'blck' ) ) );
								{
									$fldId = 'cache/exclAgents';
									$o .= ( _SettOutputTokensEditor( $fldId, Gen::GetArrField( $sett, $fldId, array(), '/' ), _x( 'AgentsPhlr', 'admin.Settings_Exclusions_Args', 'seraphinite-accelerator' ), 'seraph_accel', ",", 5 ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Exclusions_Cookies', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Exclusions_Cookies' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{

						$o .= ( Ui::ToggleButton( '#exclusionsCookies', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'id' => 'exclusionsCookies', 'class' => 'std', 'style' => array( 'width' => '100%', 'display' => 'none' ) ) ) );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'class' => 'blck' ) ) );
								{
									$fldId = 'cache/exclCookies';
									$o .= ( _SettOutputTokensEditor( $fldId, Gen::GetArrField( $sett, $fldId, array(), '/' ), _x( 'CookiesPhlr', 'admin.Settings_Exclusions_Args', 'seraphinite-accelerator' ), 'seraph_accel', ',' ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Exclusions_Conts', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Exclusions_Conts' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'class' => 'std blck', 'style' => array( 'width' => '100%', 'display' => 'none' ) ) ) );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'class' => 'blck' ) ) );
								{
									$fldId = 'cache/exclConts';
									$o .= ( _SettOutputTokensEditor( $fldId, Gen::GetArrField( $sett, $fldId, array(), '/' ), _x( 'PatternsPhlr', 'admin.Settings_Exclusions_Conts', 'seraphinite-accelerator' ), 'seraph_accel', "\n", 5, true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );
				}
				$o .= ( Ui::SettBlock_End() );

				echo( $o );
			},
			get_defined_vars(), 'body', 'ctlInitHidden ns-nav-vars', null, $adminMsModes[ 'local' ]
		);

		Ui::PostBoxes_MetaboxAdd( 'views', esc_html_x( 'Title', 'admin.Settings_Views', 'seraphinite-accelerator' ) . Ui::Tag( 'span', Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Views' ) ) ), Ui::AdminHelpBtnModeBlockHeader ) ), true,
			function( $callbacks_args, $box )
			{
				extract( $box[ 'args' ] );

				$o = '';

				$o .= ( Ui::SettBlock_Begin() );
				{
					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Views_Common', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Views_Gen' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'class' => 'std', 'style' => array( 'width' => '100%' ) ) ) );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/views';
									$o .= ( Ui::CheckBox( esc_html_x( 'ViewsChk', 'admin.Settings_Views_Common', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, true, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Views_Devices', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Views_Devices' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{

						$o .= ( Ui::ToggleButton( '#viewsDeviceGrps', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::TagOpen( 'div', array( 'id' => 'viewsDeviceGrps', 'style' => array( 'display' => 'none' ) ) ) );
						{
							$fldId = 'cache/viewsDeviceGrps';
							$items = Gen::GetArrField( $sett, $fldId, array(), '/' );

							$itemsListPrms = array( 'editorAreaCssPath' => '#viewsDeviceGrps', 'sortable' => true );
							$o .= ( Ui::ItemsList( $itemsListPrms, $items, 'seraph_accel/' . $fldId,
								function( $cbArgs, $idItems, $items, $itemKey, $item )
								{
									extract( $cbArgs );

									$o = '';

									$o .= ( Ui::SepLine() );

									$o .= ( Ui::SettBlock_Begin( array( 'class' => 'compact', 'style' => array( 'margin-top' => 0 ) ) ) );
									{
										{
											$fldId = 'id';
											$id = (isset($item[ $fldId ])?$item[ $fldId ]:null);
											$o .= ( Ui::InputBox( 'hidden', $idItems . '/' . $itemKey . '/' . $fldId, $id, null, true ) );
										}

										$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'EnabledLbl', 'admin.Settings_Views_Devices_Item', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Views_Devices_Item_Enabled' ) ) ), Ui::AdminHelpBtnModeText ) ) );
										{
											$fldId = 'enable';
											$o .= ( Ui::CheckBox( null, $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, true, '/' ), true ) );
										}
										$o .= ( Ui::SettBlock_Item_End() );

										$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'NameLbl', 'admin.Settings_Views_Devices_Item', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Views_Devices_Item_Name' ) ) ), Ui::AdminHelpBtnModeText ) ) );
										{
											$fldId = 'name';
											$name = (isset($item[ $fldId ])?$item[ $fldId ]:null);

											$plchldr = GetViewDisplayNameById( $id );
											if( !$plchldr )
												$plchldr = _x( 'NamePhlr', 'admin.Settings_Views_Devices_Item', 'seraphinite-accelerator' );

											$o .= ( Ui::TextBox( $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, '', '/' ), array( 'style' => array( 'width' => '100%' ), 'placeholder' => $plchldr ), true ) );
											if( $id )
												$o .= ( Ui::Tag( 'p', sprintf( esc_html_x( 'NameIdDescr_%1$s', 'admin.Settings_Views_Devices_Item', 'seraphinite-accelerator' ), $id ), array( 'class' => 'description' ) ) );
										}
										$o .= ( Ui::SettBlock_Item_End() );

										$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'AgentsLbl', 'admin.Settings_Views_Devices_Item', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Views_Devices_Item_Agents' ) ) ), Ui::AdminHelpBtnModeText ), array( 'class' => 'blck' ) ) );
										{
											$fldId = 'agents';
											$o .= ( _SettOutputAgentsEditor( $fldId, Gen::GetArrField( $item, $fldId, array(), '/' ), _x( 'AgentsPhlr', 'admin.Settings_Cache_Devices', 'seraphinite-accelerator' ), $idItems . '/' . $itemKey ) );
										}
										$o .= ( Ui::SettBlock_Item_End() );
									}
									$o .= ( Ui::SettBlock_End() );

									$o .= ( Ui::Tag( 'div', Ui::ItemsList_ItemOperateBtns( $itemsListPrms, array( 'class' => 'ctlSpaceAfterSm' ) ), array( 'class' => 'ctlSpaceVBefore ctlSpaceVAfter' ) ) );

									return( $o );
								},

								function( $cbArgs, $attrs )
								{
									Gen::SetArrField( $attrs, 'class.+', 'ctlSpaceVAfter' );
									return( Ui::Tag( 'div', Ui::Tag( 'label', Ui::ItemsList_NoItemsContent() ), $attrs ) );
								},

								get_defined_vars(), array( 'class' => 'ctlMaxSizeX' )
							) );

							$o .= ( Ui::SepLine() );
							$o .= ( Ui::Tag( 'div', Ui::ItemsList_OperateBtns( $itemsListPrms, array( 'class' => 'ctlSpaceAfter', 'style' => array( 'margin-left' => 0 ) ) ), array( 'class' => 'ctlSpaceVBefore' ) ) );
						}
						$o .= ( Ui::TagClose( 'div' ) );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Views_Geo', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Views_Geo' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck', 'style' => array( 'display' => 'none' ) ) ) );
						{
							{
								$fldId = 'cache/viewsGeo/enable';
								$o .= ( Ui::CheckBox( esc_html_x( 'EnableChk', 'admin.Settings_Views_Geo', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
							}

							{
								$fldId = 'cache/viewsGeo/grps';
								$items = Gen::GetArrField( $sett, $fldId, array(), '/' );

								$itemsListPrms = array( 'editorAreaCssPath' => '.blck', 'sortable' => true );
								$o .= ( Ui::ItemsList( $itemsListPrms, $items, 'seraph_accel/' . $fldId,
									function( $cbArgs, $idItems, $items, $itemKey, $item )
									{
										extract( $cbArgs );

										$o = '';

										$o .= ( Ui::SepLine() );

										$o .= ( Ui::SettBlock_Begin( array( 'class' => 'compact', 'style' => array( 'margin-top' => 0 ) ) ) );
										{
											$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'EnabledLbl', 'admin.Settings_Views_Geo', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Views_Geo_Enabled' ) ) ), Ui::AdminHelpBtnModeText ) ) );
											{
												$fldId = 'enable';
												$o .= ( Ui::CheckBox( null, $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, true, '/' ), true ) );
											}
											$o .= ( Ui::SettBlock_Item_End() );

											$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'NameLbl', 'admin.Settings_Views_Geo', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Views_Geo_Name' ) ) ), Ui::AdminHelpBtnModeText ), array( 'class' => 'blck' ) ) );
											{
												$fldId = 'name';
												$o .= ( Ui::TextBox( $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, '', '/' ), array( 'style' => array( 'width' => '100%' ), 'placeholder' => _x( 'NamePhlr', 'admin.Settings_Views_Geo', 'seraphinite-accelerator' ) ), true ) );
												if( $itemKey != Ui::ItemsList_GetNewKeyTpl() )
													$o .= ( Ui::Tag( 'p', sprintf( esc_html_x( 'NameIdDescr_%1$s', 'admin.Settings_Views_Geo', 'seraphinite-accelerator' ), $itemKey ), array( 'class' => 'description' ) ) );
											}
											$o .= ( Ui::SettBlock_Item_End() );

											$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'CountryCodesLbl', 'admin.Settings_Views_Geo', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Views_Geo_CountryCodes' ) ) ), Ui::AdminHelpBtnModeText ), array( 'class' => 'blck' ) ) );
											{
												$fldId = 'items';
												$o .= ( _SettOutputTokensEditor( $fldId, Gen::GetArrField( $item, $fldId, array(), '/' ), _x( 'CountryCodePhlr', 'admin.Settings_Views_Geo', 'seraphinite-accelerator' ), $idItems . '/' . $itemKey, "\n", 5, true ) );
											}
											$o .= ( Ui::SettBlock_Item_End() );
										}
										$o .= ( Ui::SettBlock_End() );

										$o .= ( Ui::Tag( 'div', Ui::ItemsList_ItemOperateBtns( $itemsListPrms, array( 'class' => 'ctlSpaceAfterSm' ) ), array( 'class' => 'ctlSpaceVBefore ctlSpaceVAfter' ) ) );

										return( $o );
									},

									function( $cbArgs, $attrs )
									{
										Gen::SetArrField( $attrs, 'class.+', 'ctlSpaceVAfter' );
										return( Ui::Tag( 'div', Ui::Tag( 'label', Ui::ItemsList_NoItemsContent() ), $attrs ) );
									},

									get_defined_vars(), array( 'class' => 'ctlMaxSizeX' )
								) );

								$o .= ( Ui::SepLine() );
								$o .= ( Ui::Tag( 'div', Ui::ItemsList_OperateBtns( $itemsListPrms, array( 'class' => 'ctlSpaceAfter', 'style' => array( 'margin-left' => 0 ) ) ), array( 'class' => 'ctlSpaceVBefore' ) ) );
							}
						}
						$o .= ( Ui::TagClose( 'div' ) );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Views_Compat', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Views_Compat' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck', 'style' => array( 'display' => 'none' ) ) ) );
						{
							$fldId = 'cache/viewsCompatGrps';
							$items = Gen::GetArrField( $sett, $fldId, array(), '/' );

							$itemsListPrms = array( 'editorAreaCssPath' => '.blck', 'sortable' => true );
							$o .= ( Ui::ItemsList( $itemsListPrms, $items, 'seraph_accel/' . $fldId,
								function( $cbArgs, $idItems, $items, $itemKey, $item )
								{
									extract( $cbArgs );

									$o = '';

									$o .= ( Ui::SepLine() );

									$o .= ( Ui::SettBlock_Begin( array( 'class' => 'compact', 'style' => array( 'margin-top' => 0 ) ) ) );
									{
										{
											$fldId = 'id';
											$id = (isset($item[ $fldId ])?$item[ $fldId ]:null);
											$o .= ( Ui::InputBox( 'hidden', $idItems . '/' . $itemKey . '/' . $fldId, $id, null, true ) );
										}

										$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'EnabledLbl', 'admin.Settings_Views_Devices_Item', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Views_Compat_Item_Enabled' ) ) ), Ui::AdminHelpBtnModeText ) ) );
										{
											$fldIdText = null;
											switch( $id )
											{
											case 'c':		$fldIdText = _x( 'CompatChk', 'admin.Settings_Views_Compat_Item_Name', 'seraphinite-accelerator' ); break;
											case 'cm':	$fldIdText = _x( 'CompatMinChk', 'admin.Settings_Views_Compat_Item_Name', 'seraphinite-accelerator' ); break;
											}

											$fldId = 'enable';
											$o .= ( Ui::CheckBox( $fldIdText, $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, true, '/' ), true ) );
										}
										$o .= ( Ui::SettBlock_Item_End() );

										$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'AgentsLbl', 'admin.Settings_Views_Devices_Item', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Views_Compat_Item_Agents' ) ) ), Ui::AdminHelpBtnModeText ), array( 'class' => 'blck' ) ) );
										{
											$fldId = 'agents';
											$o .= ( _SettOutputAgentsEditor( $fldId, Gen::GetArrField( $item, $fldId, array(), '/' ), _x( 'AgentsCompatPhlr', 'admin.Settings_Cache_Devices', 'seraphinite-accelerator' ), $idItems . '/' . $itemKey ) );
										}
										$o .= ( Ui::SettBlock_Item_End() );
									}
									$o .= ( Ui::SettBlock_End() );

									return( $o );
								},

								function( $cbArgs, $attrs )
								{
									Gen::SetArrField( $attrs, 'class.+', 'ctlSpaceVAfter' );
									return( Ui::Tag( 'div', Ui::Tag( 'label', Ui::ItemsList_NoItemsContent() ), $attrs ) );
								},

								get_defined_vars(), array( 'class' => 'ctlMaxSizeX' )
							) );

						}
						$o .= ( Ui::TagClose( 'div' ) );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Views', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Views_Grp' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{

						$o .= ( Ui::ToggleButton( '#viewsGrps', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::TagOpen( 'div', array( 'id' => 'viewsGrps', 'style' => array( 'display' => 'none' ) ) ) );
						{
							$fldId = 'cache/viewsGrps';
							$items = Gen::GetArrField( $sett, $fldId, array(), '/' );

							$itemsListPrms = array( 'editorAreaCssPath' => '#viewsGrps', 'sortable' => true );
							$o .= ( Ui::ItemsList( $itemsListPrms, $items, 'seraph_accel/' . $fldId,
								function( $cbArgs, $idItems, $items, $itemKey, $item )
								{
									extract( $cbArgs );

									$o = '';

									$o .= ( Ui::SepLine() );

									$o .= ( Ui::SettBlock_Begin( array( 'class' => 'compact', 'style' => array( 'margin-top' => 0 ) ) ) );
									{
										$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'EnabledLbl', 'admin.Settings_Views_Item', 'seraphinite-accelerator' ) ) );
										{
											$fldId = 'enable';
											$o .= ( Ui::CheckBox( null, $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, true, '/' ), true ) );
										}
										$o .= ( Ui::SettBlock_Item_End() );

										$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'NameLbl', 'admin.Settings_Views_Item', 'seraphinite-accelerator' ) ) );
										{
											$fldId = 'name';
											$o .= ( Ui::TextBox( $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, '', '/' ), array( 'style' => array( 'width' => '100%' ), 'placeholder' => _x( 'NamePhlr', 'admin.Settings_Views_Devices_Item', 'seraphinite-accelerator' ) ), true ) );
										}
										$o .= ( Ui::SettBlock_Item_End() );

										$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'UriExclsLbl', 'admin.Settings_Views_Item', 'seraphinite-accelerator' ), array( 'class' => 'blck' ) ) );
										{
											$fldId = 'urisExcl';
											$o .= ( _SettOutputTokensEditor( $fldId, Gen::GetArrField( $item, $fldId, array(), '/' ), _x( 'UrisPhlr', 'admin.Settings_Exclusions_Args', 'seraphinite-accelerator' ),  $idItems . '/' . $itemKey, "\n", 5, true ) );
										}
										$o .= ( Ui::SettBlock_Item_End() );

										$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'CookiesLbl', 'admin.Settings_Views_Item', 'seraphinite-accelerator' ), array( 'class' => 'blck' ) ) );
										{
											$fldId = 'cookies';
											$o .= ( _SettOutputCookiesEditor( $fldId, Gen::GetArrField( $item, $fldId, array(), '/' ), $idItems . '/' . $itemKey ) );
										}
										$o .= ( Ui::SettBlock_Item_End() );

										$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Cache_Headers', 'seraphinite-accelerator' ), array( 'class' => 'blck' ) ) );
										{
											$fldId = 'hdrs';
											$o .= ( _SettOutputTokensEditor( $fldId, Gen::GetArrField( $item, $fldId, array(), '/' ), _x( 'HdrsPhlr', 'admin.Settings_Cache_Headers', 'seraphinite-accelerator' ), $idItems . '/' . $itemKey, "\n", 5, true ) );
										}
										$o .= ( Ui::SettBlock_Item_End() );

										$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'ArgsLbl', 'admin.Settings_Views_Item', 'seraphinite-accelerator' ), array( 'class' => 'blck' ) ) );
										{
											$fldId = 'args';
											$o .= ( _SettOutputArgsEditor( $fldId, Gen::GetArrField( $item, $fldId, array(), '/' ), $idItems . '/' . $itemKey ) );
										}
										$o .= ( Ui::SettBlock_Item_End() );
									}
									$o .= ( Ui::SettBlock_End() );

									$o .= ( Ui::Tag( 'div', Ui::ItemsList_ItemOperateBtns( $itemsListPrms, array( 'class' => 'ctlSpaceAfterSm' ) ), array( 'class' => 'ctlSpaceVBefore ctlSpaceVAfter' ) ) );

									return( $o );
								},

								function( $cbArgs, $attrs )
								{
									Gen::SetArrField( $attrs, 'class.+', 'ctlSpaceVAfter' );
									return( Ui::Tag( 'div', Ui::Tag( 'label', Ui::ItemsList_NoItemsContent() ), $attrs ) );
								},

								get_defined_vars(), array( 'class' => 'ctlMaxSizeX' )
							) );

							$o .= ( Ui::SepLine() );
							$o .= ( Ui::Tag( 'div', Ui::ItemsList_OperateBtns( $itemsListPrms, array( 'class' => 'ctlSpaceAfter', 'style' => array( 'margin-left' => 0 ) ) ), array( 'class' => 'ctlSpaceVBefore' ) ) );
						}
						$o .= ( Ui::TagClose( 'div' ) );
					}
					$o .= ( Ui::SettBlock_Item_End() );
				}
				$o .= ( Ui::SettBlock_End() );

				echo( $o );
			},
			get_defined_vars(), 'body', 'ctlInitHidden ns-nav-vars', null, $adminMsModes[ 'local' ]
		);

		Ui::PostBoxes_MetaboxAdd( 'ctxs', esc_html_x( 'Title', 'admin.Settings_Ctx', 'seraphinite-accelerator' ) . Ui::Tag( 'span', Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Ctx' ) ) ), Ui::AdminHelpBtnModeBlockHeader ) ), true,
			function( $callbacks_args, $box )
			{
				extract( $box[ 'args' ] );

				$o = '';

				$o .= ( Ui::SettBlock_Begin() );
				{
					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Ctx_Common', 'seraphinite-accelerator' ) . Ui::Tag( 'span', Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Ctx_Gen' ) ), Plugin::AdminBtnsBlock_GetPaidContent( $isPaidLockedContent ) ), Ui::AdminHelpBtnModeText ) ), array( 'style' => array( 'display' => null ) ) ) );
					{
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin() );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/ctx';
									$o .= ( Ui::CheckBox( esc_html_x( 'CtxChk', 'admin.Settings_Ctx_Common', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, true, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/ctxSkip';
									$o .= ( Ui::CheckBox( esc_html_x( 'CtxSkipChk', 'admin.Settings_Ctx_Common', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, true, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/ctxSessSep';
									$o .= ( Ui::CheckBox( esc_html_x( 'CtxSessSepChk', 'admin.Settings_Ctx_Common', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, true, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/ctxContPr';
									$o .= ( Ui::CheckBox( esc_html_x( 'ContentProcessEnableChk', 'admin.Settings_Advanced_Common', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, true, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Ctx_Revalidate', 'seraphinite-accelerator' ) . Ui::Tag( 'span', Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Ctx_Revalidate' ) ), Plugin::AdminBtnsBlock_GetPaidContent( $isPaidLockedContent ) ), Ui::AdminHelpBtnModeText ) ), array( 'style' => array( 'display' => null ) ) ) );
					{
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin() );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/ctxCliRefresh';
									$o .= ( Ui::CheckBox( esc_html_x( 'CtxClientRevalidateChk', 'admin.Settings_Ctx_Revalidate', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, true, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/ctxTimeoutCln';
									$o .= ( _SettTimeoutEditor( 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, 0, '/' ), esc_html_x( 'TimeoutCln_%1$s%2$s%3$s', 'admin.Settings_Cache_Common', 'seraphinite-accelerator' ) ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Ctx_Grps', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Ctx_Grp' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::ToggleButton( '#ctxGrps', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
					}
					$o .= ( Ui::SettBlock_Item_End() );
				}
				$o .= ( Ui::SettBlock_End() );

				$o .= ( Ui::TagOpen( 'div', array( 'id' => 'ctxGrps', 'style' => array( 'display' => 'none' ) ) ) );
				{
					$fldId = 'cache/ctxGrps';
					$items = Gen::GetArrField( $sett, $fldId, array(), '/' );

					$itemsListPrms = array( 'editorAreaCssPath' => '#ctxGrps', 'sortable' => true );

					$o .= ( Ui::ItemsList( $itemsListPrms, $items, 'seraph_accel/' . $fldId,
						function( $cbArgs, $idItems, $items, $itemKey, $item )
						{
							extract( $cbArgs );

							$o = '';

							$o .= ( Ui::SepLine( 'p' ) );

							$o .= ( Ui::SettBlock_Begin() );
							{
								$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'EnabledLbl', 'admin.Settings_Ctx_Grp', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Ctx_Grp_Enable' ) ) ), Ui::AdminHelpBtnModeText ) ) );
								{
									$fldId = 'enable';
									$o .= ( Ui::CheckBox( null, $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, true, '/' ), true ) );
								}
								$o .= ( Ui::SettBlock_Item_End() );

								$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'NameLbl', 'admin.Settings_Ctx_Grp', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Ctx_Grp_Name' ) ) ), Ui::AdminHelpBtnModeText ) ) );
								{
									$fldId = 'name';
									$o .= ( Ui::TextBox( $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, '', '/' ), array( 'style' => array( 'width' => '100%' ), 'placeholder' => _x( 'NamePhlr', 'admin.Settings_Ctx_Grp', 'seraphinite-accelerator' ) ), true ) );
								}
								$o .= ( Ui::SettBlock_Item_End() );

								$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Ctx_Grp_Cookies', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Ctx_Grp_Cookies' ) ) ), Ui::AdminHelpBtnModeText ) ) );
								{

									$o .= ( Ui::ToggleButton( '.blck.cookies', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
									$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck cookies', 'style' => array( 'display' => 'none' ) ) ) );
									{
										$fldId = 'cookies';
										$o .= ( _SettOutputCookiesEditor( $fldId, Gen::GetArrField( $item, $fldId, array(), '/' ), $idItems . '/' . $itemKey ) );
									}
									$o .= ( Ui::TagClose( 'div' ) );
								}
								$o .= ( Ui::SettBlock_Item_End() );

								$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Ctx_Grp_Args', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Ctx_Grp_Args' ) ) ), Ui::AdminHelpBtnModeText ) ) );
								{

									$o .= ( Ui::ToggleButton( '.blck.args', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
									$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck args', 'style' => array( 'display' => 'none' ) ) ) );
									{
										$fldId = 'args';
										$o .= ( _SettOutputArgsEditor( $fldId, Gen::GetArrField( $item, $fldId, array(), '/' ), $idItems . '/' . $itemKey ) );
									}
									$o .= ( Ui::TagClose( 'div' ) );
								}
								$o .= ( Ui::SettBlock_Item_End() );

								$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Ctx_Grp_Tbls', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Ctx_Grp_Tbl' ) ), Plugin::AdminBtnsBlock_GetPaidContent( $isPaidLockedContent ) ), Ui::AdminHelpBtnModeText ), array( 'style' => array( 'display' => null ) ) ) );
								{

									$o .= ( Ui::ToggleButton( '.blck.tables', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
									$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck tables', 'style' => array( 'display' => 'none' ) ) ) );
									{
										$fldId = 'tables';
										$items = Gen::GetArrField( $item, $fldId, array(), '/' );

										$itemsList2Prms = array( 'editorAreaCssPath' => '.blck.tables', 'sortable' => true );
										$o .= ( Ui::ItemsList( $itemsList2Prms, $items, $idItems . '/' . $itemKey . '/' . $fldId,
											function( $cbArgs, $idItems, $items, $itemKey, $item )
											{
												extract( $cbArgs );

												$o = '';

												$o .= ( Ui::SepLine( 'div', array( 'style' => array( 'margin-bottom' => '2em' ) ) ) );

												$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck' ) ) );
												{
													$o .= ( Ui::Label( Ui::Tag( '', esc_html_x( 'NameLbl', 'admin.Settings_Ctx_Grp_Tbls', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Ctx_Grp_Tbl_Name' ) ) ), Ui::AdminHelpBtnModeText ) ) ) );

													$fldId = 'name';
													$o .= ( Ui::TextBox( $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, '', '/' ), array( 'style' => array( 'width' => '100%' ) ), true ) );
												}
												$o .= ( Ui::TagClose( 'div' ) );

												$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck' ) ) );
												{
													$o .= ( Ui::Label( Ui::Tag( '', esc_html_x( 'ColLbl', 'admin.Settings_Ctx_Grp_Tbls', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Ctx_Grp_Tbl_Col' ) ) ), Ui::AdminHelpBtnModeText ) ) ) );

													$fldId = 'col';
													$o .= ( Ui::TextBox( $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, '', '/' ), array( 'style' => array( 'width' => '100%' ) ), true ) );
												}
												$o .= ( Ui::TagClose( 'div' ) );

												$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck' ) ) );
												{
													$o .= ( Ui::Label( Ui::Tag( '', esc_html_x( 'NameRelLbl', 'admin.Settings_Ctx_Grp_Tbls', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Ctx_Grp_Tbl_NameRel' ) ) ), Ui::AdminHelpBtnModeText ) ) ) );

													$fldId = 'nameRel';
													$o .= ( Ui::TextBox( $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, '', '/' ), array( 'style' => array( 'width' => '100%' ) ), true ) );
												}
												$o .= ( Ui::TagClose( 'div' ) );

												$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck' ) ) );
												{
													$o .= ( Ui::Label( Ui::Tag( '', esc_html_x( 'ColRelLbl', 'admin.Settings_Ctx_Grp_Tbls', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Ctx_Grp_Tbl_ColRel' ) ) ), Ui::AdminHelpBtnModeText ) ) ) );

													$fldId = 'colRel';
													$o .= ( Ui::TextBox( $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, '', '/' ), array( 'style' => array( 'width' => '100%' ) ), true ) );
												}
												$o .= ( Ui::TagClose( 'div' ) );

												$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck' ) ) );
												{
													$o .= ( Ui::Label( Ui::Tag( '', esc_html_x( 'ColRelLinkLbl', 'admin.Settings_Ctx_Grp_Tbls', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Ctx_Grp_Tbl_ColRelLink' ) ) ), Ui::AdminHelpBtnModeText ) ) ) );

													$fldId = 'colRelLink';
													$o .= ( Ui::TextBox( $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, '', '/' ), array( 'style' => array( 'width' => '100%' ) ), true ) );
												}
												$o .= ( Ui::TagClose( 'div' ) );

												$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck' ) ) );
												{
													$o .= ( Ui::Label( Ui::Tag( '', esc_html_x( 'CondLbl', 'admin.Settings_Ctx_Grp_Tbls', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Ctx_Grp_Tbl_Cond' ) ) ), Ui::AdminHelpBtnModeText ) ) ) );

													$fldId = 'condRel';
													$o .= ( _SettOutputTblCondEditor( $fldId, _Sett_GetCond( Gen::GetArrField( $item, $fldId, array(), '/' ) ), $idItems . '/' . $itemKey ) );
												}
												$o .= ( Ui::TagClose( 'div' ) );

												$o .= ( Ui::Tag( 'div', Ui::ItemsList_ItemOperateBtns( $itemsList2Prms, array( 'class' => 'ctlSpaceAfterSm' ) ), array( 'class' => 'ctlSpaceVBefore ctlSpaceVAfter' ) ) );

												return( $o );
											},

											function( $cbArgs, $attrs )
											{
												Gen::SetArrField( $attrs, 'class.+', 'ctlSpaceVAfter' );
												return( Ui::Tag( 'div', Ui::Tag( 'label', Ui::ItemsList_NoItemsContent() ), $attrs ) );
											},

											array( 'isPaidLockedContent' => $isPaidLockedContent, 'rmtCfg' => $rmtCfg, 'itemsList2Prms' => $itemsList2Prms ), array( 'class' => 'ctlMaxSizeX' ), 1
										) );

										$o .= ( Ui::SepLine() );
										$o .= ( Ui::Tag( 'div', Ui::ItemsList_OperateBtns( $itemsList2Prms, array( 'class' => 'ctlSpaceAfter', 'style' => array( 'margin-left' => 0 ) ) ), array( 'class' => 'ctlSpaceVBefore' ) ) );
									}
									$o .= ( Ui::TagClose( 'div' ) );
								}
								$o .= ( Ui::SettBlock_Item_End() );
							}
							$o .= ( Ui::SettBlock_End() );

							$o .= ( Ui::Tag( 'div', Ui::ItemsList_ItemOperateBtns( $itemsListPrms, array( 'class' => 'ctlSpaceAfterSm' ) ), array( 'class' => 'ctlSpaceVBefore ctlSpaceVAfter' ) ) );

							return( $o );
						},

						function( $cbArgs, $attrs )
						{
							Gen::SetArrField( $attrs, 'class.+', 'ctlSpaceVAfter' );
							return( Ui::Tag( 'div', Ui::Tag( 'label', Ui::ItemsList_NoItemsContent() ), $attrs ) );
						},

						get_defined_vars(), array( 'class' => 'ctlMaxSizeX' )
					) );

					$o .= ( Ui::SepLine() );
					$o .= ( Ui::Tag( 'div', Ui::ItemsList_OperateBtns( $itemsListPrms, array( 'class' => 'ctlSpaceAfter', 'style' => array( 'margin-left' => 0 ) ) ), array( 'class' => 'ctlSpaceVBefore' ) ) );
				}
				$o .= ( Ui::TagClose( 'div' ) );

				echo( $o );
			},
			get_defined_vars(), 'body', 'ctlInitHidden ns-nav-vars', null, $adminMsModes[ 'local' ]
		);

		Ui::PostBoxes_MetaboxAdd( 'html', esc_html_x( 'Title', 'admin.Settings_Html', 'seraphinite-accelerator' ) . Ui::Tag( 'span', Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Html' ) ), Plugin::AdminBtnsBlock_GetPaidContent( $isPaidLockedContent ) ), Ui::AdminHelpBtnModeBlockHeader ) ), true,
			function( $callbacks_args, $box )
			{
				extract( $box[ 'args' ] );

				$o = '';

				$o .= ( Ui::SettBlock_Begin() );
				{
					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Html_Common', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Html_Gen' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin() );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/min';
									$o .= ( Ui::CheckBox( esc_html_x( 'MinChk', 'admin.Settings_Html_Common', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/earlyPaint';
									$o .= ( Ui::CheckBox( esc_html_x( 'EarlyPaintChk', 'admin.Settings_Html_Common', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Html_Fix', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Html_Fix' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin() );
						{
							$fldId = 'contPr/normalize';

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$o .= ( Ui::CheckBox( esc_html_x( 'LiteChk', 'admin.Settings_Html_Fix', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId . 'Lite', !!( Gen::GetArrField( $sett, $fldId, 0, '/' ) & 1 ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
								{
									$o .= ( Ui::CheckBox( esc_html_x( 'LiteScrEncCorrChk', 'admin.Settings_Html_Fix', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId . 'LiteScrEncCorr', !!( Gen::GetArrField( $sett, $fldId, 0, '/' ) & 512 ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$o .= ( Ui::CheckBox( esc_html_x( 'MedChk', 'admin.Settings_Html_Fix', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId . 'Med', !!( Gen::GetArrField( $sett, $fldId, 0, '/' ) & 2 ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$o .= ( Ui::CheckBox( esc_html_x( 'TidyChk', 'admin.Settings_Html_Fix', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId . 'Tidy', !!( Gen::GetArrField( $sett, $fldId, 0, '/' ) & 524288 ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Html_Cln', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Html_Cln' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck', 'style' => array( 'display' => 'none' ) ) ) );
						{
							$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck' ) ) );
							{
								$fldId = 'contPr/cln/cmts';
								$o .= ( Ui::CheckBox( esc_html_x( 'CmtsChk', 'admin.Settings_Html_Cln', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );

								$fldId = 'contPr/cln/cmtsExcl';
								$o .= ( _SettOutputTokensEditor( $fldId, Gen::GetArrField( $sett, $fldId, array(), '/' ), _x( 'CommentsExclPhlr', 'admin.Settings_Html_Cln', 'seraphinite-accelerator' ), 'seraph_accel', "\n", 5, true ) );
							}
							$o .= ( Ui::TagClose( 'div' ) );

							$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck', 'style' => array( 'margin-top' => '1em' ) ) ) );
							{
								$o .= ( Ui::Tag( 'label', esc_html_x( 'ItemsLbl', 'admin.Settings_Html_Cln', 'seraphinite-accelerator' ) ) );

								$fldId = 'contPr/cln/items';
								$o .= ( _SettOutputTokensEditor( $fldId, Gen::GetArrField( $sett, $fldId, array(), '/' ), _x( 'SklExclPhlr', 'admin.Settings_Group_Item', 'seraphinite-accelerator' ), 'seraph_accel', "\n", 5, true ) );
							}
							$o .= ( Ui::TagClose( 'div' ) );
						}
						$o .= ( Ui::TagClose( 'div' ) );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Html_Rpl', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Html_Rpl' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck', 'style' => array( 'display' => 'none' ) ) ) );
						{
							$fldId = 'contPr/rpl/items';
							$itemsListPrms = array( 'editorAreaCssPath' => '.blck', 'sortable' => true );

							$o .= ( Ui::Tag( 'p', Ui::ItemsList( $itemsListPrms, Gen::GetArrField( $sett, $fldId, array(), '/' ), 'seraph_accel/' . $fldId,
								function( $cbArgs, $idItems, $vals, $itemKey, $item )
								{
									extract( $cbArgs );

									$o = '';

									$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'class' => array( 'std', 'ctlMaxSizeX ctlSpaceVAfter' ) ) ) );
									{
										{
											$fldId = 'enable';
											$fldIdEx = 'expr';
											$o .= ( Ui::Tag( 'tr',
												Ui::Tag( 'td',Ui::CheckBox( null, $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, true, '/' ), true ), array( 'class' => 'ctlMinSizeX' ) ) .
												Ui::Tag( 'td', Ui::TextBox( $idItems . '/' . $itemKey . '/' . $fldIdEx, Gen::GetArrField( $item, $fldIdEx, '', '/' ), array( 'masked' => true, 'placeholder' => _x( 'FindExprPhlr', 'admin.Settings_Html_Rpl', 'seraphinite-accelerator' ), 'style' => array( 'width' => '100%' ) ), true ) )
											) );
										}

										{
											$fldId = 'data';
											$o .= ( Ui::Tag( 'tr', Ui::Tag( 'td', Ui::TextArea( $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, '', '/' ), array( 'masked' => true, 'wrap' => 'off', 'class' => 'code', 'style' => array( 'width' => '100%', 'min-height' => '7em', 'max-height' => '30em' ) ), true ), array( 'colspan' => 2 ) ) ) );
										}

									}
									$o .= ( Ui::SettBlock_ItemSubTbl_End() );

									$o .= ( Ui::ItemsList_ItemOperateBtns( $itemsListPrms, array( 'class' => 'ctlSpaceAfterSm ctlSpaceVAfter' ) ) );
									$o .= ( Ui::SepLine( 'div', array( 'class' => array( 'ctlSpaceVAfter' ) ) ) );

									return( $o );
								},

								function( $cbArgs, $attrs )
								{
									return( Ui::ItemsList_NoItemsContent( $attrs ) );
								},

								get_defined_vars(), array( 'class' => 'ctlMaxSizeX' )
							) ) );

							$o .= ( Ui::Tag( 'p', Ui::ItemsList_OperateBtns( $itemsListPrms, array( 'class' => 'ctlSpaceAfter ctlSpaceVBefore' ) ) ) );
						}
						$o .= ( Ui::TagClose( 'div' ) );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Html_Fresh', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Html_Fresh' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'class' => 'blck ctlMaxSizeX', 'style' => array( 'display' => 'none' ) ) ) );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/fresh/smoothAppear';
									$o .= ( Ui::CheckBox( esc_html_x( 'SmoothAppearChk', 'admin.Settings_Images_Lazy', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/fresh/items';
									$o .= ( _SettOutputTokensEditor( $fldId, Gen::GetArrField( $sett, $fldId, array(), '/' ), _x( 'ItemsPhlr', 'admin.Settings_Html_Lazy', 'seraphinite-accelerator' ), 'seraph_accel', "\n", 5, true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Html_Lazy', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Html_Lazy' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'class' => 'blck ctlMaxSizeX', 'style' => array( 'display' => 'none' ) ) ) );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/lazy/bjs';
									$o .= ( Ui::CheckBox( esc_html_x( 'LoadBeforeJsChk', 'admin.Settings_Html_Lazy', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/lazy/p';
									$o .= ( Ui::CheckBox( esc_html_x( 'AsPropChk', 'admin.Settings_Html_Lazy', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/lazy/items';
									$o .= ( _SettOutputTokensEditor( $fldId, Gen::GetArrField( $sett, $fldId, array(), '/' ), _x( 'ItemsPhlr', 'admin.Settings_Html_Lazy', 'seraphinite-accelerator' ), 'seraph_accel', "\n", 5, true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );
				}
				$o .= ( Ui::SettBlock_End() );

				echo( $o );
			},
			get_defined_vars(), 'body', 'ctlInitHidden ns-nav-content', null, $adminMsModes[ 'local' ]
		);

		Ui::PostBoxes_MetaboxAdd( 'images', esc_html_x( 'Title', 'admin.Settings_Images', 'seraphinite-accelerator' ) . Ui::Tag( 'span', Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Images' ) ), Plugin::AdminBtnsBlock_GetPaidContent( $isPaidLockedContent ) ), Ui::AdminHelpBtnModeBlockHeader ) ), true,
			function( $callbacks_args, $box )
			{
				extract( $box[ 'args' ] );

				$o = '';

				$o .= ( Ui::SettBlock_Begin() );
				{
					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Images_Common', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Images_Gen' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'class' => 'blck ctlMaxSizeX' ) ) );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/img/srcAddLm';
									$o .= ( Ui::CheckBox( esc_html_x( 'SrcAddLmChk', 'admin.Settings_Images_Common', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/img/sysFlt';
									$o .= ( Ui::CheckBox( esc_html_x( 'SysFltChk', 'admin.Settings_Images_Common', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/img/inlSml';
									$fldIdEx = 'contPr/img/inlSmlSize';
									$o .= ( Ui::CheckBox( sprintf( esc_html_x( 'InlineSmallChk_%1$s', 'admin.Settings_Images_Common', 'seraphinite-accelerator' ),
										Ui::NumberBox( 'seraph_accel/' . $fldIdEx, round( ( float )Gen::GetArrField( $sett, $fldIdEx, 0, '/' ) / 1024, 1 ), array( 'min' => 0, 'step' => '0.1', 'placeholder' => '0', 'style' => array( 'width' => '7em' ), 'class' => 'inline' ), true )
									), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/img/deinlLrg';
									$fldIdEx = 'contPr/img/deinlLrgSize';
									$o .= ( Ui::CheckBox( sprintf( esc_html_x( 'DeInlineLargeChk_%1$s', 'admin.Settings_Images_Common', 'seraphinite-accelerator' ),
										Ui::NumberBox( 'seraph_accel/' . $fldIdEx, round( ( float )Gen::GetArrField( $sett, $fldIdEx, 0, '/' ) / 1024, 1 ), array( 'min' => 0, 'step' => '0.1', 'placeholder' => '0', 'style' => array( 'width' => '7em' ), 'class' => 'inline' ), true )
									), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$o .= ( Ui::Label( esc_html_x( 'ExclsLbl', 'admin.Settings_Common', 'seraphinite-accelerator' ) ) );

									$fldId = 'contPr/img/excl';
									$o .= ( _SettOutputTokensEditor( $fldId, Gen::GetArrField( $sett, $fldId, array(), '/' ), _x( 'ExclsPhlr', 'admin.Settings_Images_Lazy', 'seraphinite-accelerator' ), 'seraph_accel', "\n", 5, true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Images_Compr', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Images_Compr' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'class' => 'blck ctlMaxSizeX' ) ) );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/img/webp/enable';
									$fldIdEx = 'contPr/img/webp/prms/q';
									$o .= ( Ui::CheckBox( sprintf( esc_html_x( 'WebpChk_%1$s', 'admin.Settings_Images_Compr', 'seraphinite-accelerator' ), Ui::NumberBox( 'seraph_accel/' . $fldIdEx, Gen::GetArrField( $sett, $fldIdEx, 0, '/' ), array( 'min' => 0, 'max' => 100, 'style' => array( 'width' => '4em' ), 'class' => 'inline' ), true ) . '% ' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/img/webp/redir';
									$o .= ( Ui::CheckBox( esc_html_x( 'RedirWebpChk', 'admin.Settings_Images_Compr', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/img/avif/enable';
									$fldIdEx = 'contPr/img/avif/prms/q';
									$fldIdEx2 = 'contPr/img/avif/prms/s';
									$o .= ( Ui::CheckBox( sprintf( esc_html_x( 'AvifChk_%1$s%2$s', 'admin.Settings_Images_Compr', 'seraphinite-accelerator' ), Ui::NumberBox( 'seraph_accel/' . $fldIdEx, Gen::GetArrField( $sett, $fldIdEx, 0, '/' ), array( 'min' => 0, 'max' => 100, 'style' => array( 'width' => '4em' ), 'class' => 'inline' ), true ) . '% ', Ui::NumberBox( 'seraph_accel/' . $fldIdEx2, Gen::GetArrField( $sett, $fldIdEx2, 0, '/' ), array( 'min' => 0, 'max' => 10, 'style' => array( 'width' => '3.5em' ), 'class' => 'inline' ), true ) . ' ' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/img/avif/redir';
									$o .= ( Ui::CheckBox( esc_html_x( 'RedirAvifChk', 'admin.Settings_Images_Compr', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/img/redirCacheAdapt';
									$o .= ( Ui::CheckBox( esc_html_x( 'RedirCacheAdaptChk', 'admin.Settings_Images_Compr', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/img/redirOwn';
									$o .= ( Ui::CheckBox( esc_html_x( 'RedirOwnChk', 'admin.Settings_Images_Compr', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Images_Adapt', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Images_Adapt' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'class' => 'blck ctlMaxSizeX', 'style' => array( 'display' => 'none' ) ) ) );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/img/szAdaptImg';
									$o .= ( Ui::CheckBox( esc_html_x( 'ImgChk', 'admin.Settings_Images_Adapt', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/img/szAdaptBg';
									$o .= ( Ui::CheckBox( esc_html_x( 'BgChk', 'admin.Settings_Images_Adapt', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldIdEx = 'contPr/img/szAdaptBgCxMin';
									$o .= ( Ui::Label( sprintf( esc_html_x( 'SzLbl_%1$s', 'admin.Settings_Images_Adapt', 'seraphinite-accelerator' ),
										Ui::NumberBox( 'seraph_accel/' . $fldIdEx, Gen::GetArrField( $sett, $fldIdEx, 0, '/' ), array( 'min' => 0, 'style' => array( 'width' => '5em' ) ), true )
									) ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/img/szAdaptDpr';
									$o .= ( Ui::CheckBox( esc_html_x( 'DprChk', 'admin.Settings_Images_Adapt', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$o .= ( Ui::Label( esc_html_x( 'ExclsLbl', 'admin.Settings_Common', 'seraphinite-accelerator' ) ) );

									$fldId = 'contPr/img/szAdaptExcl';
									$o .= ( _SettOutputTokensEditor( $fldId, Gen::GetArrField( $sett, $fldId, array(), '/' ), _x( 'ExclsPhlr', 'admin.Settings_Images_Adapt', 'seraphinite-accelerator' ), 'seraph_accel', "\n", 5, true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Images_Lazy', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Images_Lazy' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'class' => 'blck ctlMaxSizeX', 'style' => array( 'display' => 'none' ) ) ) );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/img/lazy/setSize';
									$o .= ( Ui::CheckBox( esc_html_x( 'SetSizeChk', 'admin.Settings_Images_Lazy', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, true, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/img/lazy/load';
									$o .= ( Ui::CheckBox( esc_html_x( 'LoadChk', 'admin.Settings_Images_Lazy', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, true, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
								{
									$fldId = 'contPr/img/lazy/own';
									$o .= ( Ui::CheckBox( esc_html_x( 'OwnChk', 'admin.Settings_Images_Lazy', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '3em' ) ) ) );
								{
									$fldId = 'contPr/img/lazy/smoothAppear';
									$o .= ( Ui::CheckBox( esc_html_x( 'SmoothAppearChk', 'admin.Settings_Images_Lazy', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
								{
									$fldId = 'contPr/img/lazy/del3rd';
									$o .= ( Ui::CheckBox( esc_html_x( 'Del3rdChk', 'admin.Settings_Images_Lazy', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$o .= ( Ui::Label( esc_html_x( 'ExclsLbl', 'admin.Settings_Common', 'seraphinite-accelerator' ) ) );

									$fldId = 'contPr/img/lazy/excl';
									$o .= ( _SettOutputTokensEditor( $fldId, Gen::GetArrField( $sett, $fldId, array(), '/' ), _x( 'ExclsPhlr', 'admin.Settings_Images_Lazy', 'seraphinite-accelerator' ), 'seraph_accel', "\n", 5, true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Images_CacheExt', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Images_CacheExt' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'class' => 'blck ctlMaxSizeX', 'style' => array( 'display' => 'none' ) ) ) );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$o .= ( Ui::Label( esc_html_x( 'DescrLbl', 'admin.Settings_Images_CacheExt', 'seraphinite-accelerator' ) ) );

									$fldId = 'contPr/img/cacheExt';
									$o .= ( _SettOutputTokensEditor( $fldId, Gen::GetArrField( $sett, $fldId, array(), '/' ), _x( 'ItemsPhlr', 'admin.Settings_Images_CacheExt', 'seraphinite-accelerator' ), 'seraph_accel', "\n", 5, true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );
				}
				$o .= ( Ui::SettBlock_End() );

				echo( $o );
			},
			get_defined_vars(), 'body', 'ctlInitHidden ns-nav-content', null, $adminMsModes[ 'local' ]
		);

		Ui::PostBoxes_MetaboxAdd( 'frames', esc_html_x( 'Title', 'admin.Settings_Frames', 'seraphinite-accelerator' ) . Ui::Tag( 'span', Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Frames' ) ), Plugin::AdminBtnsBlock_GetPaidContent( $isPaidLockedContent ) ), Ui::AdminHelpBtnModeBlockHeader ) ), true,
			function( $callbacks_args, $box )
			{
				extract( $box[ 'args' ] );

				$o = '';

				$o .= ( Ui::SettBlock_Begin() );
				{
					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Frames_Lazy', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Frames_Lazy' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin() );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/frm/lazy/enable';
									$o .= ( Ui::CheckBox( esc_html_x( 'EnableChk', 'admin.Settings_Frames_Lazy', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
								{
									$fldId = 'contPr/frm/lazy/yt';
									$o .= ( Ui::CheckBox( esc_html_x( 'YouTubeChk', 'admin.Settings_Frames_Lazy', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
								{
									$fldId = 'contPr/frm/lazy/vm';
									$o .= ( Ui::CheckBox( esc_html_x( 'VimeoChk', 'admin.Settings_Frames_Lazy', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
								{
									$fldId = 'contPr/frm/lazy/elmntrBg';
									$o .= ( Ui::CheckBox( esc_html_x( 'ElmntrBgChk', 'admin.Settings_Frames_Lazy', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
								{
									$fldId = 'contPr/frm/lazy/youTubeFeed';
									$o .= ( Ui::CheckBox( esc_html_x( 'YouTubeFeedChk', 'admin.Settings_Frames_Lazy', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Frames_ContParts' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin() );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/sldBdt';
									$o .= ( Ui::CheckBox( esc_html_x( 'SldBdtChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/swBdt';
									$o .= ( Ui::CheckBox( esc_html_x( 'SwBdtChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/vidJs';
									$o .= ( Ui::CheckBox( esc_html_x( 'VidJsChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/elmntrAni';
									$o .= ( Ui::CheckBox( esc_html_x( 'ElmntrAniChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/elmntrSpltAni';
									$o .= ( Ui::CheckBox( esc_html_x( 'ElmntrSpltAniChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/elmntrTrxAni';
									$o .= ( Ui::CheckBox( esc_html_x( 'ElmntrTrxAniChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/elmntrBgSldshw';
									$o .= ( Ui::CheckBox( esc_html_x( 'ElmntrBgSldshwChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/elmntrVids';
									$o .= ( Ui::CheckBox( esc_html_x( 'ElmntrVidsChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/elmntrTabs';
									$o .= ( Ui::CheckBox( esc_html_x( 'ElmntrTabsChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/elmntrAccrdn';
									$o .= ( Ui::CheckBox( esc_html_x( 'ElmntrAccrdnChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/elmntrAdvTabs';
									$o .= ( Ui::CheckBox( esc_html_x( 'ElmntrAdvTabsChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/elmntrPremTabs';
									$o .= ( Ui::CheckBox( esc_html_x( 'ElmntrPremTabsChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/elmntrPremCrsl';
									$o .= ( Ui::CheckBox( esc_html_x( 'ElmntrPremCrslChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/elmntrNavMenu';
									$o .= ( Ui::CheckBox( esc_html_x( 'ElmntrNavMenuChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/elmntrPremNavMenu';
									$o .= ( Ui::CheckBox( esc_html_x( 'ElmntrPremNavMenuChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/elmntrPremScrl';
									$o .= ( Ui::CheckBox( esc_html_x( 'ElmntrPremScrlChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/elmntrWdgtGal';
									$o .= ( Ui::CheckBox( esc_html_x( 'ElmntrWdgtGalChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/elmntrWdgtImgCrsl';
									$o .= ( Ui::CheckBox( esc_html_x( 'ElmntrWdgtImgCrslChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/elmntrWdgtWooPrdImgs';
									$o .= ( Ui::CheckBox( esc_html_x( 'ElmntrWdgtWooPrdImgsChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/elmntrWdgtCntr';
									$o .= ( Ui::CheckBox( esc_html_x( 'ElmntrWdgtCntrChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/elmntrWdgtAvoShcs';
									$o .= ( Ui::CheckBox( esc_html_x( 'ElmntrWdgtAvoShcsChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/elmntrWdgtLott';
									$o .= ( Ui::CheckBox( esc_html_x( 'ElmntrWdgtLottChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/elmntrWdgtPrmLott';
									$o .= ( Ui::CheckBox( esc_html_x( 'ElmntrWdgtPrmLottChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/nktrLott';
									$o .= ( Ui::CheckBox( esc_html_x( 'NktrLottChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/elmntrStck';
									$o .= ( Ui::CheckBox( esc_html_x( 'ElmntrStckChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/elmntrShe';
									$o .= ( Ui::CheckBox( esc_html_x( 'ElmntrSheChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/elmntrStrtch';
									$o .= ( Ui::CheckBox( esc_html_x( 'ElmntrStrtchSecChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/qodefApprAni';
									$o .= ( Ui::CheckBox( esc_html_x( 'QodefApprAniChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/prtThSkel';
									$o .= ( Ui::CheckBox( esc_html_x( 'PrtThSkelChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/astrRsp';
									$o .= ( Ui::CheckBox( esc_html_x( 'AstrRspChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/ntBlueThRspnsv';
									$o .= ( Ui::CheckBox( esc_html_x( 'NtBlueThRspnsvChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/mdknThRspnsv';
									$o .= ( Ui::CheckBox( esc_html_x( 'MdknThRspnsvChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/fltsmThBgFill';
									$o .= ( Ui::CheckBox( esc_html_x( 'FltsmThBgFillChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/fltsmThAni';
									$o .= ( Ui::CheckBox( esc_html_x( 'FltsmThAniChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/ukSldshw';
									$o .= ( Ui::CheckBox( esc_html_x( 'UkSldshwChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/ukBgImg';
									$o .= ( Ui::CheckBox( esc_html_x( 'UkBgImgChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/ukAni';
									$o .= ( Ui::CheckBox( esc_html_x( 'UkAniChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/ukGrid';
									$o .= ( Ui::CheckBox( esc_html_x( 'UkGridChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/ukModal';
									$o .= ( Ui::CheckBox( esc_html_x( 'UkModalChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/ukHghtVwp';
									$o .= ( Ui::CheckBox( esc_html_x( 'UkHghtVwpChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/ukNavBar';
									$o .= ( Ui::CheckBox( esc_html_x( 'UkNavBarChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/tmHdr';
									$o .= ( Ui::CheckBox( esc_html_x( 'TmHdrChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/sldN2Ss';
									$o .= ( Ui::CheckBox( esc_html_x( 'SldN2SsChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/sldRev';
									$o .= ( Ui::CheckBox( esc_html_x( 'SldRevChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
								{
									$fldId = 'contPr/cp/sldRev_SmthLd';
									$o .= ( Ui::CheckBox( esc_html_x( 'SldRevSmthLdChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/sldRev7';
									$o .= ( Ui::CheckBox( esc_html_x( 'SldRev7Chk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/tdThumbCss';
									$o .= ( Ui::CheckBox( esc_html_x( 'TdThumbCssChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/elmsKitImgCmp';
									$o .= ( Ui::CheckBox( esc_html_x( 'ElmsKitImgCmpChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/elmsKitLott';
									$o .= ( Ui::CheckBox( esc_html_x( 'ElmsKitLottChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/haCrsl';
									$o .= ( Ui::CheckBox( esc_html_x( 'HaCrslChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/xooelTabs';
									$o .= ( Ui::CheckBox( esc_html_x( 'XooelTabsChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/phtncThmb';
									$o .= ( Ui::CheckBox( esc_html_x( 'PhotonicThumbChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/jetMobMenu';
									$o .= ( Ui::CheckBox( esc_html_x( 'JetMobMenuChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/jetLott';
									$o .= ( Ui::CheckBox( esc_html_x( 'JetLottChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/jetCrsl';
									$o .= ( Ui::CheckBox( esc_html_x( 'JetCrslChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/jetCrslPst';
									$o .= ( Ui::CheckBox( esc_html_x( 'JetCrslPstChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/diviMvImg';
									$o .= ( Ui::CheckBox( esc_html_x( 'DiviMvImgChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/diviMvText';
									$o .= ( Ui::CheckBox( esc_html_x( 'DiviMvTextChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/diviMvSld';
									$o .= ( Ui::CheckBox( esc_html_x( 'DiviMvSldChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/diviMvFwHdr';
									$o .= ( Ui::CheckBox( esc_html_x( 'DiviMvFwHdrChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/diviVidBox';
									$o .= ( Ui::CheckBox( esc_html_x( 'DiviVidBoxChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/diviVidBg';
									$o .= ( Ui::CheckBox( esc_html_x( 'DiviVidBgChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/diviVidFr';
									$o .= ( Ui::CheckBox( esc_html_x( 'DiviVidFrChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/diviDsmGal';
									$o .= ( Ui::CheckBox( esc_html_x( 'DiviDsmGalChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/diviLzStls';
									$o .= ( Ui::CheckBox( esc_html_x( 'DiviLzStlsChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/diviPrld';
									$o .= ( Ui::CheckBox( esc_html_x( 'DiviPrldChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/diviStck';
									$o .= ( Ui::CheckBox( esc_html_x( 'DiviStckChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/diviAni';
									$o .= ( Ui::CheckBox( esc_html_x( 'DiviAniChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/diviDataAni';
									$o .= ( Ui::CheckBox( esc_html_x( 'DiviDataAniChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/diviHdr';
									$o .= ( Ui::CheckBox( esc_html_x( 'DiviHdrChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/brcksAni';
									$o .= ( Ui::CheckBox( esc_html_x( 'BrcksAniChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/kdncThAni';
									$o .= ( Ui::CheckBox( esc_html_x( 'KdncThAniChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/scrlSeq';
									$o .= ( Ui::CheckBox( esc_html_x( 'ScrlSeqChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/fusionBgVid';
									$o .= ( Ui::CheckBox( esc_html_x( 'FusionBgVidChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/fsnEqHghtCols';
									$o .= ( Ui::CheckBox( esc_html_x( 'FsnEqHghtColsChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/fsnAni';
									$o .= ( Ui::CheckBox( esc_html_x( 'FsnAniChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/thrvAni';
									$o .= ( Ui::CheckBox( esc_html_x( 'ThrvAniChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/phloxThRspnsv';
									$o .= ( Ui::CheckBox( esc_html_x( 'PhloxThRspnsvChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/phloxThAni';
									$o .= ( Ui::CheckBox( esc_html_x( 'PhloxThAniChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/mkImgSrcSet';
									$o .= ( Ui::CheckBox( esc_html_x( 'MkImgSrcSetChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/woodmartPrcFlt';
									$o .= ( Ui::CheckBox( esc_html_x( 'WoodmartPrcFltChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/wooPrcFlt';
									$o .= ( Ui::CheckBox( esc_html_x( 'WooPrcFltChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/wbwPrdFlt';
									$o .= ( Ui::CheckBox( esc_html_x( 'WbwPrdFltChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/wooJs';
									$o .= ( Ui::CheckBox( esc_html_x( 'WooJsChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/wpStrs';
									$o .= ( Ui::CheckBox( esc_html_x( 'WpStrsChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/txpTagGrps';
									$o .= ( Ui::CheckBox( esc_html_x( 'TxpTagGrpsChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/eaelSmpMnu';
									$o .= ( Ui::CheckBox( esc_html_x( 'EaelSmpMnuChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/wprAniTxt';
									$o .= ( Ui::CheckBox( esc_html_x( 'WprAniTxtChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/wprTabs';
									$o .= ( Ui::CheckBox( esc_html_x( 'WprTabsChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/suTabs';
									$o .= ( Ui::CheckBox( esc_html_x( 'SuTabsChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/upbAni';
									$o .= ( Ui::CheckBox( esc_html_x( 'UpbAniChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/upbBgImg';
									$o .= ( Ui::CheckBox( esc_html_x( 'UpbBgImgChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/upbCntVid';
									$o .= ( Ui::CheckBox( esc_html_x( 'UpbCntVidChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/ultRspnsv';
									$o .= ( Ui::CheckBox( esc_html_x( 'UltRspnsvChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/ultVcHd';
									$o .= ( Ui::CheckBox( esc_html_x( 'UltVcHdChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/ultAni';
									$o .= ( Ui::CheckBox( esc_html_x( 'UltAniChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/the7Ani';
									$o .= ( Ui::CheckBox( esc_html_x( 'The7AniChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/the7MblHdr';
									$o .= ( Ui::CheckBox( esc_html_x( 'The7MblHdrChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/sbThAni';
									$o .= ( Ui::CheckBox( esc_html_x( 'SbThAniChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/esntlsThAni';
									$o .= ( Ui::CheckBox( esc_html_x( 'EsntlsThAniChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/beThAni';
									$o .= ( Ui::CheckBox( esc_html_x( 'BeThAniChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/merimagBgImg';
									$o .= ( Ui::CheckBox( esc_html_x( 'MerimagBgImgChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/mdcrLdng';
									$o .= ( Ui::CheckBox( esc_html_x( 'MdcrLdngChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/prmmprssLzStls';
									$o .= ( Ui::CheckBox( esc_html_x( 'PrmmprssLzStlsChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/mnmgImg';
									$o .= ( Ui::CheckBox( esc_html_x( 'MnmgImgChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/tldBgImg';
									$o .= ( Ui::CheckBox( esc_html_x( 'TldBgImgChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/jqVide';
									$o .= ( Ui::CheckBox( esc_html_x( 'JqVideChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/jqSldNivo';
									$o .= ( Ui::CheckBox( esc_html_x( 'JqSldNivoChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/wooSctrCntDwnTmr';
									$o .= ( Ui::CheckBox( esc_html_x( 'WooSctrCntDwnTmrChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/lottGen';
									$o .= ( Ui::CheckBox( esc_html_x( 'LottGenChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/sprflMenu';
									$o .= ( Ui::CheckBox( esc_html_x( 'SprflMenuChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/jqJpPlr';
									$o .= ( Ui::CheckBox( esc_html_x( 'JqJpPlrChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/prstPlr';
									$o .= ( Ui::CheckBox( esc_html_x( 'PrstPlrChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/cp/grnshftPbAosOnceAni';
									$o .= ( Ui::CheckBox( esc_html_x( 'GrnshftPbAosOnceAniChk', 'admin.Settings_Frames_ContParts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );
				}
				$o .= ( Ui::SettBlock_End() );

				echo( $o );
			},
			get_defined_vars(), 'body', 'ctlInitHidden ns-nav-content', null, $adminMsModes[ 'local' ]
		);

		Ui::PostBoxes_MetaboxAdd( 'scripts', esc_html_x( 'Title', 'admin.Settings_Scripts', 'seraphinite-accelerator' ) . Ui::Tag( 'span', Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Scripts' ) ), Plugin::AdminBtnsBlock_GetPaidContent( $isPaidLockedContent ) ), Ui::AdminHelpBtnModeBlockHeader ) ), true,
			function( $callbacks_args, $box )
			{
				extract( $box[ 'args' ] );

				$o = '';

				$o .= ( Ui::SettBlock_Begin() );
				{
					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Scripts_Common', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Scripts_Gen' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin() );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/js/optLoad';
									$o .= ( Ui::CheckBox( esc_html_x( 'OptLoadChk', 'admin.Settings_Scripts_Common', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
								{
									$fldId = 'contPr/js/critSpec/timeout/enable';
									$o .= ( Ui::CheckBox(
										esc_html_x( 'TimeoutChk', 'admin.Settings_Scripts_CritSpecial', 'seraphinite-accelerator' ),
										'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true
									) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
								{
									$fldId = 'contPr/js/nonCrit/timeout/enable';
									$fldIdEx = 'contPr/js/nonCrit/timeout/v';
									$o .= ( Ui::CheckBox(
										sprintf( esc_html_x( 'TimeoutChk_%1$s', 'admin.Settings_Scripts_NotCrit', 'seraphinite-accelerator' ), Ui::NumberBox( 'seraph_accel/' . $fldIdEx, round( ( float )Gen::GetArrField( $sett, $fldIdEx, 0, '/' ) / 1000, 1 ), array( 'min' => 0, 'step' => 'any', 'placeholder' => '0', 'style' => array( 'width' => '5em' ) ), true ) ),
										'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true
									) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
								{
									$fldId = 'contPr/js/spec/timeout/enable';
									$fldIdEx = 'contPr/js/spec/timeout/v';
									$o .= ( Ui::CheckBox(
										sprintf( esc_html_x( 'TimeoutChk_%1$s', 'admin.Settings_Scripts_Special', 'seraphinite-accelerator' ), Ui::NumberBox( 'seraph_accel/' . $fldIdEx, round( ( float )Gen::GetArrField( $sett, $fldIdEx, 0, '/' ) / 1000, 1 ), array( 'min' => 0, 'step' => 'any', 'placeholder' => '0', 'style' => array( 'width' => '5em' ) ), true ) ),
										'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true
									) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
								{
									$fldId = 'contPr/js/cplxDelay';
									$o .= ( Ui::CheckBox( esc_html_x( 'CplxDelayChk', 'admin.Settings_Scripts_Common', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
								{
									$fldId = 'contPr/js/preLoadEarly';
									$o .= ( Ui::CheckBox( esc_html_x( 'PreLoadEarlyChk', 'admin.Settings_Scripts_Common', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
								{
									$fldId = 'contPr/js/loadFast';
									$o .= ( Ui::CheckBox( esc_html_x( 'LoadFastChk', 'admin.Settings_Scripts_Common', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}

						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Scripts_Group', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Scripts_Group' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'class' => 'blck ctlMaxSizeX', 'style' => array( 'display' => 'none' ) ) ) );
						{

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/js/groupCritSpec';
									$o .= ( Ui::CheckBox( esc_html_x( 'GroupCritSpecChk', 'admin.Settings_Scripts_Common', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/js/groupNonCrit';
									$o .= ( Ui::CheckBox( esc_html_x( 'GroupNonCritChk', 'admin.Settings_Scripts_Common', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$o .= ( Ui::Tag( 'div', Ui::Label( esc_html_x( 'ExclsLbl', 'admin.Settings_Common', 'seraphinite-accelerator' ) ) ) );

									{
										$fldId = 'contPr/js/groupExclMdls';
										$o .= ( Ui::CheckBox( esc_html_x( 'GroupExclMdlsChk', 'admin.Settings_Scripts_Common', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
									}

									{
										$fldId = 'contPr/js/groupExcls';
										$o .= ( _SettOutputScriptsEditor( $fldId, Gen::GetArrField( $sett, $fldId, array(), '/' ), _x( 'ScriptsPhlr', 'admin.Settings_Scripts_Editor', 'seraphinite-accelerator' ), 'seraph_accel' ) );
									}
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Scripts_Interact', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Scripts_Interact' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'class' => 'blck ctlMaxSizeX', 'style' => array( 'display' => 'none' ) ) ) );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/js/aniDelay';
									$o .= ( Ui::Label( sprintf( esc_html_x( 'AniDelayDelay_%1$s', 'admin.Settings_Scripts_Common', 'seraphinite-accelerator' ), Ui::NumberBox( 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, 0, '/' ), array( 'min' => 0, 'placeholder' => '250', 'style' => array( 'width' => '5em' ) ), true ) ) ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/js/scrlDelay';
									$o .= ( Ui::Label( sprintf( esc_html_x( 'ScrlDelay_%1$s', 'admin.Settings_Scripts_Common', 'seraphinite-accelerator' ), Ui::NumberBox( 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, 0, '/' ), array( 'min' => 0, 'placeholder' => '0', 'style' => array( 'width' => '5em' ) ), true ) ) ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/js/clk/delay';
									$o .= ( Ui::Label( sprintf( esc_html_x( 'FirstClickDelay_%1$s', 'admin.Settings_Scripts_Common', 'seraphinite-accelerator' ), Ui::NumberBox( 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, 0, '/' ), array( 'min' => 50, 'placeholder' => '250', 'style' => array( 'width' => '5em' ) ), true ) ) ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}

							$o .= ( Ui::TagOpen( 'tr', array( 'class' => 'blck' ) ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$o .= ( Ui::Label( esc_html_x( 'ExclsDefLbl', 'admin.Settings_Scripts_Interact', 'seraphinite-accelerator' ) ) );

									$fldId = 'contPr/js/clk/exclDef';
									$o .= ( _SettOutputTokensEditor( $fldId, Gen::GetArrField( $sett, $fldId, array(), '/' ), _x( 'InclsPhlr', 'admin.Settings_Scripts_Other', 'seraphinite-accelerator' ), 'seraph_accel', "\n", 5, true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr', array( 'class' => 'blck' ) ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$o .= ( Ui::Label( esc_html_x( 'ExclsLbl', 'admin.Settings_Scripts_Interact', 'seraphinite-accelerator' ) ) );

									$fldId = 'contPr/js/clk/excl';
									$o .= ( _SettOutputTokensEditor( $fldId, Gen::GetArrField( $sett, $fldId, array(), '/' ), _x( 'InclsPhlr', 'admin.Settings_Scripts_Other', 'seraphinite-accelerator' ), 'seraph_accel', "\n", 5, true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Scripts_Min', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Scripts_Min' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'class' => 'blck ctlMaxSizeX', 'style' => array( 'display' => 'none' ) ) ) );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/js/min';
									$o .= ( Ui::CheckBox( esc_html_x( 'MinChk', 'admin.Settings_Scripts_Common', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, true, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
								{
									$fldId = 'contPr/js/cprRem';
									$o .= ( Ui::CheckBox( esc_html_x( 'CopyrightRemChk', 'admin.Settings_Scripts_Common', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$o .= ( Ui::Label( esc_html_x( 'ExclsLbl', 'admin.Settings_Common', 'seraphinite-accelerator' ) ) );

									$fldId = 'contPr/js/minExcls';
									$o .= ( _SettOutputScriptsEditor( $fldId, Gen::GetArrField( $sett, $fldId, array(), '/' ), _x( 'ScriptsPhlr', 'admin.Settings_Scripts_Editor', 'seraphinite-accelerator' ), 'seraph_accel' ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Scripts_CritSpecial', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Scripts_CritSpecial' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck', 'style' => array( 'display' => 'none' ) ) ) );
						{
							{
								$fldId = 'contPr/js/critSpec/items';
								$o .= ( _SettOutputScriptsEditor( $fldId, Gen::GetArrField( $sett, $fldId, array(), '/' ), _x( 'ScriptsPhlr', 'admin.Settings_Scripts_Special', 'seraphinite-accelerator' ), 'seraph_accel', "\n", 8 ) );
							}
						}
						$o .= ( Ui::TagClose( 'div' ) );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Scripts_NotCrit', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Scripts_NotCrit' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'class' => 'blck ctlMaxSizeX', 'style' => array( 'display' => 'none' ) ) ) );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									{
										$fldId = 'contPr/js/nonCrit/inl';
										$o .= ( Ui::CheckBox( esc_html_x( 'InlChk', 'admin.Settings_NonCritScope', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true, array( 'class' => 'ctlSpaceAfter' ) ) );
									}

									{
										$fldId = 'contPr/js/nonCrit/int';
										$o .= ( Ui::CheckBox( esc_html_x( 'IntChk', 'admin.Settings_NonCritScope', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true, array( 'class' => 'ctlSpaceAfter' ) ) );
									}

									{
										$fldId = 'contPr/js/nonCrit/ext';
										$o .= ( Ui::CheckBox( esc_html_x( 'ExtChk', 'admin.Settings_NonCritScope', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true, array( 'class' => 'ctlSpaceAfter' ) ) );
									}
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									{
										$fldId = 'contPr/js/nonCrit/excl';
										$o .= ( Ui::ComboBox(
											'seraph_accel/' . $fldId,
											array(
												'1'		=> esc_html_x( 'Excl', 'admin.Settings_NonCritScope', 'seraphinite-accelerator' ),
												''		=> esc_html_x( 'Incl', 'admin.Settings_NonCritScope', 'seraphinite-accelerator' ),
											),
											Gen::GetArrField( $sett, $fldId, false, '/' ) ? '1' : '', true, array( 'class' => 'ctlSpaceVAfter' ) ) );
									}

									{
										$fldId = 'contPr/js/nonCrit/items';
										$o .= ( _SettOutputScriptsEditor( $fldId, Gen::GetArrField( $sett, $fldId, array(), '/' ), _x( 'ScriptsPhlr', 'admin.Settings_Scripts_Editor', 'seraphinite-accelerator' ), 'seraph_accel' ) );
									}
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Scripts_Special', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Scripts_Special' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck', 'style' => array( 'display' => 'none' ) ) ) );
						{
							{
								$fldId = 'contPr/js/spec/items';
								$o .= ( _SettOutputScriptsEditor( $fldId, Gen::GetArrField( $sett, $fldId, array(), '/' ), _x( 'ScriptsPhlr', 'admin.Settings_Scripts_Special', 'seraphinite-accelerator' ), 'seraph_accel', "\n", 8 ) );
							}
						}
						$o .= ( Ui::TagClose( 'div' ) );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Scripts_Skip', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Scripts_Skip' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck', 'style' => array( 'display' => 'none' ) ) ) );
						{
							$fldId = 'contPr/js/skips';
							$o .= ( _SettOutputScriptsEditor( $fldId, Gen::GetArrField( $sett, $fldId, array(), '/' ), _x( 'ScriptsPhlr', 'admin.Settings_Scripts_Editor', 'seraphinite-accelerator' ), 'seraph_accel' ) );
						}
						$o .= ( Ui::TagClose( 'div' ) );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Scripts_Other', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Scripts_Other' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'class' => 'blck ctlMaxSizeX', 'style' => array( 'display' => 'none' ) ) ) );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/js/other/incl';
									$o .= ( _SettOutputTokensEditor( $fldId, Gen::GetArrField( $sett, $fldId, array(), '/' ), _x( 'InclsPhlr', 'admin.Settings_Scripts_Other', 'seraphinite-accelerator' ), 'seraph_accel', "\n", 5, true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );
				}
				$o .= ( Ui::SettBlock_End() );

				echo( $o );
			},
			get_defined_vars(), 'body', 'ctlInitHidden ns-nav-scripts', null, $adminMsModes[ 'local' ]
		);

		Ui::PostBoxes_MetaboxAdd( 'styles', esc_html_x( 'Title', 'admin.Settings_Styles', 'seraphinite-accelerator' ) . Ui::Tag( 'span', Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Styles' ) ), Plugin::AdminBtnsBlock_GetPaidContent( $isPaidLockedContent ) ), Ui::AdminHelpBtnModeBlockHeader ) ), true,
			function( $callbacks_args, $box )
			{
				extract( $box[ 'args' ] );

				$o = '';

				$o .= ( Ui::SettBlock_Begin() );
				{
					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Styles_Common', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Styles_Gen' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin() );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/css/optLoad';
									$o .= ( Ui::CheckBox( esc_html_x( 'OptLoadChk', 'admin.Settings_Styles_Common', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
								{
									$fldId = 'contPr/css/inlAsSrc';
									$o .= ( Ui::CheckBox( esc_html_x( 'InlAsSrcChk', 'admin.Settings_Styles_Common', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
								{
									$fldId = 'contPr/css/inlCrit';
									$o .= ( Ui::CheckBox( esc_html_x( 'InlCritChk', 'admin.Settings_Styles_Common', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
								{
									$fldId = 'contPr/css/inlNonCrit';
									$o .= ( Ui::CheckBox( esc_html_x( 'InlNonCritChk', 'admin.Settings_Styles_Common', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
								{
									$fldId = 'contPr/css/delayNonCritWithJs';
									$o .= ( Ui::CheckBox( esc_html_x( 'DelayNonCritWithJsChk', 'admin.Settings_Styles_Common', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
								{
									$fldId = 'contPr/css/bfrJs';
									$o .= ( Ui::CheckBox( esc_html_x( 'BfrJsChk', 'admin.Settings_Styles_Common', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/css/group';
									$fldIdEx = 'contPr/css/groupCombine';
									$o .= ( Ui::CheckBox( sprintf( esc_html_x( 'GroupChk_%1$s', 'admin.Settings_Styles_Common', 'seraphinite-accelerator' ), Ui::ComboBox( 'seraph_accel/' . $fldIdEx, array( '1' => esc_html_x( 'CombineCmbItem', 'admin.Settings_Styles_Common', 'seraphinite-accelerator' ), '' => esc_html_x( 'NotCombineCmbItem', 'admin.Settings_Styles_Common', 'seraphinite-accelerator' ) ), Gen::GetArrField( $sett, $fldIdEx, false, '/' ) ? '1' : '', true, array( 'class' => 'inline' ) ) ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/css/groupNonCrit';
									$fldIdEx = 'contPr/css/groupNonCritCombine';
									$o .= ( Ui::CheckBox( sprintf( esc_html_x( 'GroupNonCritChk_%1$s', 'admin.Settings_Styles_Common', 'seraphinite-accelerator' ), Ui::ComboBox( 'seraph_accel/' . $fldIdEx, array( '1' => esc_html_x( 'CombineCmbItem', 'admin.Settings_Styles_Common', 'seraphinite-accelerator' ), '' => esc_html_x( 'NotCombineCmbItem', 'admin.Settings_Styles_Common', 'seraphinite-accelerator' ) ), Gen::GetArrField( $sett, $fldIdEx, false, '/' ) ? '1' : '', true, array( 'class' => 'inline' ) ) ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/css/sepImp';
									$o .= ( Ui::CheckBox( esc_html_x( 'SepImpChk', 'admin.Settings_Styles_Common', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/css/min';
									$o .= ( Ui::CheckBox( esc_html_x( 'MinChk', 'admin.Settings_Styles_Common', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/css/corrErr';
									$o .= ( Ui::CheckBox( esc_html_x( 'CorrErrChk', 'admin.Settings_Styles_Common', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Styles_Fonts', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Styles_Fonts' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'class' => 'ctlMaxSizeX' ) ) );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{

									$fldId = 'contPr/css/fontOptLoad';
									$fldIdEx = 'contPr/css/fontOptLoadDisp';
									$o .= ( Ui::CheckBox( sprintf( esc_html_x( 'OptChk_%1$s', 'admin.Settings_Styles_Fonts', 'seraphinite-accelerator' ), Ui::ComboBox( 'seraph_accel/' . $fldIdEx,
										array(
											'auto' => 'auto',
											'block' => 'block',
											'swap' => 'swap',
											'fallback' => 'fallback',
											'optional' => 'optional',
										)
										, Gen::GetArrField( $sett, $fldIdEx, '', '/' ), true, array( 'class' => 'inline' ) ) ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
								{
									$fldId = 'contPr/css/font/optLoadNameExpr';
									$o .= ( Ui::TextBox( 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, '', '/' ), array( 'masked' => true, 'placeholder' => esc_html_x( 'OptLoadNameExprPlchldr', 'admin.Settings_Styles_Fonts', 'seraphinite-accelerator' ), 'style' => array( 'width' => '100%' ) ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/css/fontCrit';
									$o .= ( Ui::CheckBox( esc_html_x( 'FontNonCritChk', 'admin.Settings_Styles_Fonts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, !Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
								{
									$fldId = 'contPr/css/groupFont';
									$fldIdEx = 'contPr/css/groupFontCombine';
									$o .= ( Ui::CheckBox( sprintf( esc_html_x( 'GroupFontChk_%1$s', 'admin.Settings_Styles_Common', 'seraphinite-accelerator' ), Ui::ComboBox( 'seraph_accel/' . $fldIdEx, array( '1' => esc_html_x( 'CombineCmbItem', 'admin.Settings_Styles_Common', 'seraphinite-accelerator' ), '' => esc_html_x( 'NotCombineCmbItem', 'admin.Settings_Styles_Common', 'seraphinite-accelerator' ) ), Gen::GetArrField( $sett, $fldIdEx, false, '/' ) ? '1' : '', true, array( 'class' => 'inline' ) ) ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/css/font/deinlLrg';
									$fldIdEx = 'contPr/css/font/deinlLrgSize';
									$o .= ( Ui::CheckBox( sprintf( esc_html_x( 'DeInlineLargeChk_%1$s', 'admin.Settings_Styles_Fonts', 'seraphinite-accelerator' ),
										Ui::NumberBox( 'seraph_accel/' . $fldIdEx, round( ( float )Gen::GetArrField( $sett, $fldIdEx, 0, '/' ) / 1024, 1 ), array( 'min' => 0, 'step' => '0.1', 'placeholder' => '0', 'style' => array( 'width' => '7em' ), 'class' => 'inline' ), true )
									), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/css/fontPreload';
									$o .= ( Ui::CheckBox( esc_html_x( 'FontPreloadChk', 'admin.Settings_Styles_Fonts', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Styles_NotCrit', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_NotCrit' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'class' => 'blck ctlMaxSizeX', 'style' => array( 'display' => 'none' ) ) ) );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									{
										$fldId = 'contPr/css/nonCrit/auto';

										$o .= ( Ui::ComboBox(
											'seraph_accel/' . $fldId,
											array(
												'auto'		=> esc_html_x( 'Auto', 'admin.Settings_Styles_NotCrit', 'seraphinite-accelerator' ),
												'manual'	=> esc_html_x( 'Manual', 'admin.Settings_Styles_NotCrit', 'seraphinite-accelerator' ),
											),
											Gen::GetArrField( $sett, $fldId, false, '/' ) ? 'auto' : 'manual', true, array( 'class' => 'ctlSpaceVAfter', 'data-oninit' => 'seraph_accel.Settings._int.OnUpdateCssAuto(this)', 'onchange' => 'seraph_accel.Settings._int.OnUpdateCssAuto(this)' ) ) );
									}
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr', array( 'class' => 'ns-manual' ) ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									{
										$fldId = 'contPr/css/nonCrit/inl';
										$o .= ( Ui::CheckBox( esc_html_x( 'InlChk', 'admin.Settings_NonCritScope', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true, array( 'class' => 'ctlSpaceAfter' ) ) );
									}

									{
										$fldId = 'contPr/css/nonCrit/int';
										$o .= ( Ui::CheckBox( esc_html_x( 'IntChk', 'admin.Settings_NonCritScope', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true, array( 'class' => 'ctlSpaceAfter' ) ) );
									}

									{
										$fldId = 'contPr/css/nonCrit/ext';
										$o .= ( Ui::CheckBox( esc_html_x( 'ExtChk', 'admin.Settings_NonCritScope', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true, array( 'class' => 'ctlSpaceAfter' ) ) );
									}
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr', array( 'class' => 'ns-manual blck' ) ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									{
										$fldId = 'contPr/css/nonCrit/excl';
										$o .= ( Ui::ComboBox(
											'seraph_accel/' . $fldId,
											array(
												'1'		=> esc_html_x( 'Excl', 'admin.Settings_NonCritScope', 'seraphinite-accelerator' ),
												''		=> esc_html_x( 'Incl', 'admin.Settings_NonCritScope', 'seraphinite-accelerator' ),
											),
											Gen::GetArrField( $sett, $fldId, false, '/' ) ? '1' : '', true, array( 'class' => 'ctlSpaceVAfter' ) ) );
									}

									{
										$fldId = 'contPr/css/nonCrit/items';
										$o .= ( _SettOutputStylesEditor( $fldId, Gen::GetArrField( $sett, $fldId, array(), '/' ), 'seraph_accel' ) );
									}
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr', array( 'class' => 'ns-auto blck' ) ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/css/nonCrit/autoExcls';
									$o .= ( _SettOutputTokensEditor( $fldId, Gen::GetArrField( $sett, $fldId, array(), '/' ), _x( 'AutoExclPhlr', 'admin.Settings_Styles_NotCrit', 'seraphinite-accelerator' ), 'seraph_accel', "\n", 5, true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Styles_Skip', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Styles_Skip' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck', 'style' => array( 'display' => 'none' ) ) ) );
						{
							$fldId = 'contPr/css/skips';
							$o .= ( _SettOutputStylesEditor( $fldId, Gen::GetArrField( $sett, $fldId, array(), '/' ), 'seraph_accel' ) );
						}
						$o .= ( Ui::TagClose( 'div' ) );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Styles_Custom', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Styles_Custom' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck', 'style' => array( 'display' => 'none' ) ) ) );
						{
							$fldId = 'contPr/css/custom';
							$itemsListPrms = array( 'editorAreaCssPath' => '.blck', 'sortable' => true );

							$o .= ( Ui::Tag( 'p', Ui::ItemsList( $itemsListPrms, Gen::GetArrField( $sett, $fldId, array(), '/' ), 'seraph_accel/' . $fldId,
								function( $cbArgs, $idItems, $vals, $itemKey, $item )
								{
									extract( $cbArgs );

									$o = '';

									$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'class' => array( 'std', 'ctlMaxSizeX ctlSpaceVAfter' ) ) ) );
									{
										{
											$fldId = 'enable';
											$fldIdEx = 'descr';
											$o .= ( Ui::Tag( 'tr',
												Ui::Tag( 'td',Ui::CheckBox( null, $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, true, '/' ), true ), array( 'class' => 'ctlMinSizeX' ) ) .
												Ui::Tag( 'td', Ui::TextBox( $idItems . '/' . $itemKey . '/' . $fldIdEx, Gen::GetArrField( $item, $fldIdEx, '', '/' ), array( 'placeholder' => _x( 'DescrPhlr', 'admin.Settings_Styles_Custom', 'seraphinite-accelerator' ), 'style' => array( 'width' => '100%' ) ), true ) )
											) );
										}

										{
											$fldId = 'noJsDl';
											$o .= ( Ui::Tag( 'tr', Ui::Tag( 'td', Ui::CheckBox( esc_html_x( 'NoJsDlChk', 'admin.Settings_Styles_Custom', 'seraphinite-accelerator' ), $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, false, '/' ), true ), array( 'colspan' => 2 ) ) ) );
										}

										{
											$fldId = 'data';
											$o .= ( Ui::Tag( 'tr', Ui::Tag( 'td', Ui::TextArea( $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, '', '/' ), array( 'wrap' => 'off', 'class' => 'code', 'style' => array( 'width' => '100%', 'min-height' => '7em', 'max-height' => '30em' ) ), true ), array( 'colspan' => 2 ) ) ) );
										}

									}
									$o .= ( Ui::SettBlock_ItemSubTbl_End() );

									$o .= ( Ui::ItemsList_ItemOperateBtns( $itemsListPrms, array( 'class' => 'ctlSpaceAfterSm ctlSpaceVAfter' ) ) );
									$o .= ( Ui::SepLine( 'div', array( 'class' => array( 'ctlSpaceVAfter' ) ) ) );

									return( $o );
								},

								function( $cbArgs, $attrs )
								{
									return( Ui::ItemsList_NoItemsContent( $attrs ) );
								},

								get_defined_vars(), array( 'class' => 'ctlMaxSizeX' )
							) ) );

							$o .= ( Ui::Tag( 'p', Ui::ItemsList_OperateBtns( $itemsListPrms, array( 'class' => 'ctlSpaceAfter ctlSpaceVBefore' ) ) ) );
						}
						$o .= ( Ui::TagClose( 'div' ) );
					}
					$o .= ( Ui::SettBlock_Item_End() );
				}
				$o .= ( Ui::SettBlock_End() );

				echo( $o );
			},
			get_defined_vars(), 'body', 'ctlInitHidden ns-nav-scripts', null, $adminMsModes[ 'local' ]
		);

		Ui::PostBoxes_MetaboxAdd( 'cdns', esc_html_x( 'Title', 'admin.Settings_Cdns', 'seraphinite-accelerator' ) . Ui::Tag( 'span', Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Cdn' ) ), Plugin::AdminBtnsBlock_GetPaidContent( $isPaidLockedContent ) ), Ui::AdminHelpBtnModeBlockHeader ) ), true,
			function( $callbacks_args, $box )
			{
				extract( $box[ 'args' ] );

				$o = '';

				{
					$fldId = 'contPr/cdn/items';
					$items = Gen::GetArrField( $sett, $fldId, array(), '/' );

					$itemsListPrms = array( 'editorAreaCssPath' => '#cdns', 'sortable' => true );

					$o .= ( Ui::ItemsList( $itemsListPrms, $items, 'seraph_accel/' . $fldId,
						function( $cbArgs, $idItems, $items, $itemKey, $item )
						{
							extract( $cbArgs );

							$o = '';

							$o .= ( Ui::SettBlock_Begin() );
							{
								$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'EnabledLbl', 'admin.Settings_Cdn_Item', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Cdn_Enabled' ) ) ), Ui::AdminHelpBtnModeText ) ) );
								{
									$fldId = 'enable';
									$o .= ( Ui::CheckBox( null, $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, true, '/' ), true ) );

									{
										$fldId = 'sa';
										$o .= Ui::Tag( 'p', Ui::CheckBox( esc_html_x( 'ScrApplyChk', 'admin.Settings_Cdn_Item', 'seraphinite-accelerator' ), $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, true, '/' ), true ) );
									}
								}
								$o .= ( Ui::SettBlock_Item_End() );

								$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'AddrLbl', 'admin.Settings_Cdn_Item', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Cdn_Addr' ) ) ), Ui::AdminHelpBtnModeText ) ) );
								{
									$fldId = 'addr';
									$o .= ( _SettCdnAddrEditor( $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, '', '/' ) ) );
								}
								$o .= ( Ui::SettBlock_Item_End() );

								$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'TypesLbl', 'admin.Settings_Cdn_Item', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Cdn_Types' ) ) ), Ui::AdminHelpBtnModeText ) ) );
								{

									$o .= ( Ui::ToggleButton( '.blck.types', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
									$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck types', 'style' => array( 'display' => 'none' ) ) ) );
									{
										$fldId = 'types';
										$o .= ( _SettOutputCdnTypesEditor( $fldId, Gen::GetArrField( $item, $fldId, array(), '/' ), $idItems . '/' . $itemKey ) );
									}
									$o .= ( Ui::TagClose( 'div' ) );
								}
								$o .= ( Ui::SettBlock_Item_End() );

								$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'InclsLbl', 'admin.Settings_Cdn_Item', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Cdn_Incls' ) ) ), Ui::AdminHelpBtnModeText ) ) );
								{

									$o .= ( Ui::ToggleButton( '.blck.incls', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
									$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck incls', 'style' => array( 'display' => 'none' ) ) ) );
									{
										$fldId = 'uris';
										$o .= ( _SettOutputCdnExlsEditor( $fldId, Gen::GetArrField( $item, $fldId, array(), '/' ), $idItems . '/' . $itemKey ) );
									}
									$o .= ( Ui::TagClose( 'div' ) );
								}
								$o .= ( Ui::SettBlock_Item_End() );

								$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'ExclsLbl', 'admin.Settings_Cdn_Item', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Cdn_Excls' ) ) ), Ui::AdminHelpBtnModeText ) ) );
								{

									$o .= ( Ui::ToggleButton( '.blck.excls', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
									$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck excls', 'style' => array( 'display' => 'none' ) ) ) );
									{
										$fldId = 'urisExcl';
										$o .= ( _SettOutputCdnExlsEditor( $fldId, Gen::GetArrField( $item, $fldId, array(), '/' ), $idItems . '/' . $itemKey ) );
									}
									$o .= ( Ui::TagClose( 'div' ) );
								}
								$o .= ( Ui::SettBlock_Item_End() );
							}
							$o .= ( Ui::SettBlock_End() );

							$o .= ( Ui::Tag( 'div', Ui::ItemsList_ItemOperateBtns( $itemsListPrms, array( 'class' => 'ctlSpaceAfterSm' ) ), array( 'class' => 'ctlSpaceVBefore ctlSpaceVAfter' ) ) );

							$o .= ( Ui::SepLine() );

							return( $o );
						},

						function( $cbArgs, $attrs )
						{
							Gen::SetArrField( $attrs, 'class.+', 'ctlSpaceVAfter' );
							return( Ui::Tag( 'div', Ui::Tag( 'label', Ui::ItemsList_NoItemsContent() ), $attrs ) );
						},

						get_defined_vars(), array( 'class' => 'ctlMaxSizeX' )
					) );

					$o .= ( Ui::Tag( 'div', Ui::ItemsList_OperateBtns( $itemsListPrms, array( 'class' => 'ctlSpaceAfter', 'style' => array( 'margin-left' => 0 ) ) ), array( 'class' => 'ctlSpaceVBefore' ) ) );
				}

				echo( $o );
			},
			get_defined_vars(), 'body', 'ctlInitHidden ns-nav-other', null, $adminMsModes[ 'local' ]
		);

		Ui::PostBoxes_MetaboxAdd( 'groups', esc_html_x( 'Title', 'admin.Settings_Groups', 'seraphinite-accelerator' ) . Ui::Tag( 'span', Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Group' ) ), Plugin::AdminBtnsBlock_GetPaidContent( $isPaidLockedContent ) ), Ui::AdminHelpBtnModeBlockHeader ) ), true,
			function( $callbacks_args, $box )
			{
				extract( $box[ 'args' ] );

				$o = '';

				$aViews = GetViewsList( $sett );

				{
					$fldId = 'contPr/grps/items';
					$items = Gen::GetArrField( $sett, $fldId, array(), '/' );

					$itemsListPrms = array( 'editorAreaCssPath' => '#groups', 'sortable' => true, 'sortDrag' => false );

					$o .= ( Ui::ItemsList( $itemsListPrms, $items, 'seraph_accel/' . $fldId,
						function( $cbArgs, $idItems, $items, $itemKey, $item )
						{
							extract( $cbArgs );

							$o = '';

							$o .= ( Ui::SettBlock_Begin() );
							{
								$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'EnabledLbl', 'admin.Settings_Group_Item', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Group_Enabled' ) ) ), Ui::AdminHelpBtnModeText ) ) );
								{
									$o .= ( Ui::SettBlock_ItemSubTbl_Begin() );
									{
										$o .= ( Ui::TagOpen( 'tr' ) );
										{
											$fldIdVal = Gen::GetArrField( $item, 'enable', 1, '/' );
											$o .= ( Ui::Tag( 'td', Ui::CheckBox( esc_html_x( 'SettOvrChk', 'admin.Settings_Group_Item', 'seraphinite-accelerator' ), $idItems . '/' . $itemKey . '/settOvr', $fldIdVal & 1, true ), array( 'class' => 'ctlMinSizeX ctlNoWrap' ) ) );
											$o .= ( Ui::Tag( 'td', Ui::CheckBox( esc_html_x( 'LearnChk', 'admin.Settings_Group_Item', 'seraphinite-accelerator' ), $idItems . '/' . $itemKey . '/lrn', $fldIdVal & 2, true ), array( 'class' => 'ctlMinSizeX ctlNoWrap' ) ) );
										}
										$o .= ( Ui::TagClose( 'tr' ) );
									}
									$o .= ( Ui::SettBlock_ItemSubTbl_End() );
								}
								$o .= ( Ui::SettBlock_Item_End() );

								$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'NameLbl', 'admin.Settings_Group_Item', 'seraphinite-accelerator' ) ) );
								{
									$fldId = 'name';
									$o .= ( Ui::TextBox( $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, '', '/' ), array( 'style' => array( 'width' => '100%' ), 'placeholder' => _x( 'NamePhlr', 'admin.Settings_Group_Item', 'seraphinite-accelerator' ) ), true ) );
								}
								$o .= ( Ui::SettBlock_Item_End() );

								$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'ScopeLbl', 'admin.Settings_Group_Item', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Group_Scope' ) ) ), Ui::AdminHelpBtnModeText ) ) );
								{
									$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
									$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck', 'style' => array( 'display' => 'none' ) ) ) );
									{
										$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck', 'style' => array( 'margin-bottom' => '2em' ) ) ) );
										{
											$o .= ( Ui::Tag( 'label', Ui::Tag( 'strong', esc_html_x( 'UrisInclLbl', 'admin.Settings_Group_Item', 'seraphinite-accelerator' ) ) .  Ui::AdminHelpBtn( Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Group_UrisIncl' ) ) ) );

											$fldId = 'urisIncl';
											$o .= ( _SettOutputTokensEditor( $fldId, Gen::GetArrField( $item, $fldId, array(), '/' ), _x( 'UriPhlr', 'admin.Settings_Group_Item', 'seraphinite-accelerator' ), $idItems . '/' . $itemKey, "\n", 5, true ) );
										}
										$o .= ( Ui::TagClose( 'div' ) );

										$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck', 'style' => array( 'margin-bottom' => '2em' ) ) ) );
										{
											$o .= ( Ui::Tag( 'label', Ui::Tag( 'strong', esc_html_x( 'ArgsInclLbl', 'admin.Settings_Group_Item', 'seraphinite-accelerator' ) ) .  Ui::AdminHelpBtn( Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Group_ArgsIncl' ) ) ) );

											$fldId = 'argsIncl';
											$o .= ( _SettOutputArgs2Editor( $fldId, Gen::GetArrField( $item, $fldId, array(), '/' ), $idItems . '/' . $itemKey ) );
										}
										$o .= ( Ui::TagClose( 'div' ) );

										$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck', 'style' => array( 'margin-bottom' => '2em' ) ) ) );
										{
											$o .= ( Ui::Tag( 'label', Ui::Tag( 'strong', esc_html_x( 'PatternsLbl', 'admin.Settings_Group_Item', 'seraphinite-accelerator' ) ) .  Ui::AdminHelpBtn( Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Group_Patterns' ) ) ) );

											$fldId = 'patterns';
											$o .= ( _SettOutputTokensEditor( $fldId, Gen::GetArrField( $item, $fldId, array(), '/' ), _x( 'PatternsPhlr', 'admin.Settings_Group_Item', 'seraphinite-accelerator' ), $idItems . '/' . $itemKey, "\n", 5, true ) );
										}
										$o .= ( Ui::TagClose( 'div' ) );

										$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck', 'style' => array( 'margin-bottom' => '2em' ) ) ) );
										{
											$o .= ( Ui::Tag( 'label', Ui::Tag( 'strong', esc_html_x( 'ViewsLbl', 'admin.Settings_Group_Item', 'seraphinite-accelerator' ) ) .  Ui::AdminHelpBtn( Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Group_Views' ) ) ) );

											$fldId = 'views';
											$o .= ( Ui::TokensList( Gen::GetArrField( $item, $fldId, array(), '/' ), $idItems . '/' . $itemKey . '/' . $fldId, array( 'style' => array( 'min-height' => '3em', 'height' => '9em', 'max-height' => '15em' ), 'data-onexpand' => 'seraph_accel.Ui.TokensMetaTree.Expand(this,seraph_accel.Settings._int.views,isExpanding)', 'data-onapply' => 'seraph_accel.Ui.TokensMetaTree.Apply(this)' ), true ) );
										}
										$o .= ( Ui::TagClose( 'div' ) );
									}
									$o .= ( Ui::TagClose( 'div' ) );
								}
								$o .= ( Ui::SettBlock_Item_End() );

								$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'SettingsLbl', 'admin.Settings_Group_Item', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Group_Settings' ) ) ), Ui::AdminHelpBtnModeText ) ) );
								{
									$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
									$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'class' => 'blck', 'style' => array( 'display' => 'none', 'width' => '100%' ) ) ) );
									{
										$o .= ( Ui::TagOpen( 'tr' ) );
										{
											$o .= ( Ui::TagOpen( 'td' ) );
											{
												$fldId = 'contPr/enable';
												$o .= ( Ui::CheckBox( esc_html_x( 'ContentProcessEnableChk', 'admin.Settings_Advanced_Common', 'seraphinite-accelerator' ), $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, true, '/' ), true ) );
											}
											$o .= ( Ui::TagClose( 'td' ) );
										}
										$o .= ( Ui::TagClose( 'tr' ) );

										$o .= ( Ui::TagOpen( 'tr' ) );
										{
											$o .= ( Ui::TagOpen( 'td' ) );
											{
												$fldId = 'contPr/jsOvr';
												$o .= ( Ui::CheckBox( esc_html_x( 'JsOverChk', 'admin.Settings_Group_Item_Settings', 'seraphinite-accelerator' ), $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, false, '/' ), true ) );
											}
											$o .= ( Ui::TagClose( 'td' ) );
										}
										$o .= ( Ui::TagClose( 'tr' ) );

										$o .= ( Ui::TagOpen( 'tr' ) );
										{
											$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
											{
												$fldId = 'contPr/js/optLoad';
												$o .= ( Ui::CheckBox( esc_html_x( 'OptLoadChk', 'admin.Settings_Scripts_Common', 'seraphinite-accelerator' ), $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), '/' ), true ) );
											}
											$o .= ( Ui::TagClose( 'td' ) );
										}
										$o .= ( Ui::TagClose( 'tr' ) );

										$o .= ( Ui::TagOpen( 'tr' ) );
										{
											$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '3em' ) ) ) );
											{
												$fldId = 'contPr/js/nonCrit/timeout/enable';
												$fldIdEx = 'contPr/js/nonCrit/timeout/v';
												$o .= ( Ui::CheckBox(
													sprintf( esc_html_x( 'TimeoutChk_%1$s', 'admin.Settings_Scripts_NotCrit', 'seraphinite-accelerator' ), Ui::NumberBox( $idItems . '/' . $itemKey . '/' . $fldIdEx, round( ( float )Gen::GetArrField( $item, $fldIdEx, Gen::GetArrField( $sett, $fldIdEx, 0, '/' ), '/' ) / 1000, 1 ), array( 'min' => 0, 'step' => 'any', 'placeholder' => '0', 'style' => array( 'width' => '5em' ) ), true ) ),
													$idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), '/' ), true
												) );
											}
											$o .= ( Ui::TagClose( 'td' ) );
										}

										$o .= ( Ui::TagOpen( 'tr' ) );
										{
											$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '3em' ) ) ) );
											{
												$fldId = 'contPr/js/spec/timeout/enable';
												$fldIdEx = 'contPr/js/spec/timeout/v';
												$o .= ( Ui::CheckBox(
													sprintf( esc_html_x( 'TimeoutChk_%1$s', 'admin.Settings_Scripts_Special', 'seraphinite-accelerator' ), Ui::NumberBox( $idItems . '/' . $itemKey . '/' . $fldIdEx, round( ( float )Gen::GetArrField( $item, $fldIdEx, Gen::GetArrField( $sett, $fldIdEx, 0, '/' ), '/' ) / 1000, 1 ), array( 'min' => 0, 'step' => 'any', 'placeholder' => '0', 'style' => array( 'width' => '5em' ) ), true ) ),
													$idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), '/' ), true
												) );
											}
											$o .= ( Ui::TagClose( 'td' ) );
										}

										$o .= ( Ui::TagOpen( 'tr' ) );
										{
											$o .= ( Ui::TagOpen( 'td' ) );
											{
												$fldId = 'contPr/jsNonCritScopeOvr';
												$o .= ( Ui::CheckBox( esc_html_x( 'JsNonCritScopeOvrChk', 'admin.Settings_Group_Item_Settings', 'seraphinite-accelerator' ), $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, false, '/' ), true ) );
											}
											$o .= ( Ui::TagClose( 'td' ) );
										}
										$o .= ( Ui::TagClose( 'tr' ) );

										$o .= ( Ui::TagOpen( 'tr' ) );
										{
											$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
											{
												{
													$fldId = 'contPr/js/nonCrit/inl';
													$o .= ( Ui::CheckBox( esc_html_x( 'InlChk', 'admin.Settings_NonCritScope', 'seraphinite-accelerator' ), $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), '/' ), true, array( 'class' => 'ctlSpaceAfter' ) ) );
												}

												{
													$fldId = 'contPr/js/nonCrit/int';
													$o .= ( Ui::CheckBox( esc_html_x( 'IntChk', 'admin.Settings_NonCritScope', 'seraphinite-accelerator' ), $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), '/' ), true, array( 'class' => 'ctlSpaceAfter' ) ) );
												}

												{
													$fldId = 'contPr/js/nonCrit/ext';
													$o .= ( Ui::CheckBox( esc_html_x( 'ExtChk', 'admin.Settings_NonCritScope', 'seraphinite-accelerator' ), $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), '/' ), true, array( 'class' => 'ctlSpaceAfter' ) ) );
												}
											}
											$o .= ( Ui::TagClose( 'td' ) );
										}
										$o .= ( Ui::TagClose( 'tr' ) );

										$o .= ( Ui::TagOpen( 'tr' ) );
										{
											$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
											{
												{
													$fldId = 'contPr/js/nonCrit/excl';
													$o .= ( Ui::ComboBox(
														$idItems . '/' . $itemKey . '/' . $fldId,
														array(
															'1'		=> esc_html_x( 'Excl', 'admin.Settings_NonCritScope', 'seraphinite-accelerator' ),
															''		=> esc_html_x( 'Incl', 'admin.Settings_NonCritScope', 'seraphinite-accelerator' ),
														),
														Gen::GetArrField( $item, $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), '/' ) ? '1' : '', true, array( 'class' => 'ctlSpaceVAfter' ) ) );
												}

												{
													$fldId = 'contPr/js/nonCrit/items';
													$o .= ( _SettOutputScriptsEditor( $fldId, Gen::GetArrField( $item, $fldId, Gen::GetArrField( $sett, $fldId, array(), '/' ), '/' ), _x( 'ScriptsPhlr', 'admin.Settings_Scripts_Editor', 'seraphinite-accelerator' ), $idItems . '/' . $itemKey ) );
												}
											}
											$o .= ( Ui::TagClose( 'td' ) );
										}
										$o .= ( Ui::TagClose( 'tr' ) );

										$o .= ( Ui::TagOpen( 'tr' ) );
										{
											$o .= ( Ui::TagOpen( 'td' ) );
											{
												$fldId = 'contPr/cssOvr';
												$o .= ( Ui::CheckBox( esc_html_x( 'CssOverChk', 'admin.Settings_Group_Item_Settings', 'seraphinite-accelerator' ), $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, false, '/' ), true ) );
											}
											$o .= ( Ui::TagClose( 'td' ) );
										}
										$o .= ( Ui::TagClose( 'tr' ) );

										$o .= ( Ui::TagOpen( 'tr' ) );
										{
											$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
											{
												$o .= ( Ui::Tag( 'label', esc_html_x( 'Lbl', 'admin.Settings_Styles_NotCrit', 'seraphinite-accelerator' ) ) );

												{
													$fldId = 'contPr/css/nonCrit/auto';
													$o .= ( Ui::Tag( 'div', Ui::ComboBox(
														$idItems . '/' . $itemKey . '/' . $fldId,
														array(
															'auto'		=> esc_html_x( 'Auto', 'admin.Settings_Styles_NotCrit', 'seraphinite-accelerator' ),
															'manual'	=> esc_html_x( 'Manual', 'admin.Settings_Styles_NotCrit', 'seraphinite-accelerator' ),
														),
														Gen::GetArrField( $item, $fldId, false, '/' ) ? 'auto' : 'manual', true, array( 'class' => 'ctlSpaceVAfter' ) ) ) );
												}
											}
											$o .= ( Ui::TagClose( 'td' ) );
										}
										$o .= ( Ui::TagClose( 'tr' ) );
									}
									$o .= ( Ui::SettBlock_ItemSubTbl_End() );
								}
								$o .= ( Ui::SettBlock_Item_End() );

								$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'LearnLbl', 'admin.Settings_Group_Item', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Group_Learn' ) ) ), Ui::AdminHelpBtnModeText ) ) );
								{
									$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
									$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck', 'style' => array( 'display' => 'none' ) ) ) );
									{
										{
											$fldId = 'sklSrch';
											$o .= ( Ui::CheckBox( esc_html_x( 'SklSrchChk', 'admin.Settings_Group_Item_Settings', 'seraphinite-accelerator' ), $idItems . '/' . $itemKey . '/' . $fldId, Gen::GetArrField( $item, $fldId, false, '/' ), true ) );
										}

										$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck', 'style' => array( 'margin-top' => '1em' ) ) ) );
										{
											$o .= ( Ui::Tag( 'label', esc_html_x( 'SklExclLbl', 'admin.Settings_Group_Item', 'seraphinite-accelerator' ) ) );

											$fldId = 'sklExcl';
											$o .= ( _SettOutputTokensEditor( $fldId, Gen::GetArrField( $item, $fldId, array(), '/' ), _x( 'SklExclPhlr', 'admin.Settings_Group_Item', 'seraphinite-accelerator' ), $idItems . '/' . $itemKey, "\n", 5, true ) );
										}
										$o .= ( Ui::TagClose( 'div' ) );

										$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck', 'style' => array( 'margin-top' => '1em' ) ) ) );
										{
											$o .= ( Ui::Tag( 'label', esc_html_x( 'SklCssSelExclLbl', 'admin.Settings_Group_Item', 'seraphinite-accelerator' ) ) );

											$fldId = 'sklCssSelExcl';
											$o .= ( _SettOutputTokensEditor( $fldId, Gen::GetArrField( $item, $fldId, array(), '/' ), _x( 'SklCssSelExclPhlr', 'admin.Settings_Group_Item', 'seraphinite-accelerator' ), $idItems . '/' . $itemKey, "\n", 5, true ) );
										}
										$o .= ( Ui::TagClose( 'div' ) );
									}
									$o .= ( Ui::TagClose( 'div' ) );
								}
								$o .= ( Ui::SettBlock_Item_End() );
							}
							$o .= ( Ui::SettBlock_End() );

							$o .= ( Ui::Tag( 'div', Ui::ItemsList_ItemOperateBtns( $itemsListPrms, array( 'class' => 'ctlSpaceAfterSm' ) ), array( 'class' => 'ctlSpaceVBefore ctlSpaceVAfter' ) ) );

							$o .= ( Ui::SepLine() );

							return( $o );
						},

						function( $cbArgs, $attrs )
						{
							Gen::SetArrField( $attrs, 'class.+', 'ctlSpaceVAfter' );
							return( Ui::Tag( 'div', Ui::Tag( 'label', Ui::ItemsList_NoItemsContent() ), $attrs ) );
						},

						get_defined_vars(), array( 'class' => 'ctlMaxSizeX' )
					) );

					$o .= ( Ui::Tag( 'div', Ui::ItemsList_OperateBtns( $itemsListPrms, array( 'class' => 'ctlSpaceAfter', 'style' => array( 'margin-left' => 0 ) ) ), array( 'class' => 'ctlSpaceVBefore', 'data-oninit' => 'seraph_accel.Settings._int.views = ' . @json_encode( $aViews ) ) ) );
				}

				echo( $o );
			},
			get_defined_vars(), 'body', 'ctlInitHidden ns-nav-other', null, $adminMsModes[ 'local' ]
		);

		Ui::PostBoxes_MetaboxAdd( 'advanced', esc_html_x( 'Title', 'admin.Settings_Advanced', 'seraphinite-accelerator' ) . Ui::Tag( 'span', Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Advanced' ) ) ), Ui::AdminHelpBtnModeBlockHeader ) ), true,
			function( $callbacks_args, $box )
			{
				extract( $box[ 'args' ] );

				$o = '';

				$o .= ( Ui::SettBlock_Begin() );
				{
					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Advanced_Common', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Advanced_Gen' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin(  array( 'style' => array( 'width' => '100%' ) ) ) );
						{
							$o .= ( Ui::TagOpen( 'tr', array( 'style' => array( 'display' => $adminMsModes[ 'local' ] ? null : 'none' ) ) ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'emojiIcons';
									$o .= ( Ui::CheckBox( esc_html_x( 'EmojiIconsEnableChk', 'admin.Settings_Advanced_Common', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, true, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr', array( 'style' => array( 'display' => $adminMsModes[ 'local' ] ? null : 'none' ) ) ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/enable';
									$o .= ( Ui::CheckBox( esc_html_x( 'ContentProcessEnableChk', 'admin.Settings_Advanced_Common', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( Plugin::AdminBtnsBlock_GetPaidContent( $isPaidLockedContent ) ), Ui::AdminHelpBtnModeChkRad ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, true, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr', array( 'style' => array( 'display' => $adminMsModes[ 'global' ] ? null : 'none' ) ) ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/forceAdvCache';
									$o .= ( Ui::CheckBox( sprintf( esc_html_x( 'ForceAdvCacheChk_%1$s', 'admin.Settings_Advanced_Common', 'seraphinite-accelerator' ), 'advanced-cache.php' ) . Ui::AdminBtnsBlock( array( Plugin::AdminBtnsBlock_GetPaidContent( $isPaidLockedContent ) ), Ui::AdminHelpBtnModeChkRad ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr', array( 'style' => array( 'display' => $adminMsModes[ 'local' ] ? null : 'none' ) ) ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'contPr/normUrl';
									$fldIdEx = 'contPr/normUrlMode';
									$o .= ( Ui::CheckBox( sprintf( esc_html_x( 'NormUrlChk_%1$s', 'admin.Settings_Advanced_Common', 'seraphinite-accelerator' ), Ui::ComboBox( 'seraph_accel/' . $fldIdEx, array( 4 => esc_html_x( 'ProtoHostPathCmbItem', 'admin.Settings_Advanced_Common_NormUrl', 'seraphinite-accelerator' ), 3 => esc_html_x( 'HostPathCmbItem', 'admin.Settings_Advanced_Common_NormUrl', 'seraphinite-accelerator' ), 2 => esc_html_x( 'PathCmbItem', 'admin.Settings_Advanced_Common_NormUrl', 'seraphinite-accelerator' ) ), Gen::GetArrField( $sett, $fldIdEx, 2, '/' ), true, array( 'class' => 'inline' ) ) ) . Ui::AdminBtnsBlock( array( Plugin::AdminBtnsBlock_GetPaidContent( $isPaidLockedContent ) ), Ui::AdminHelpBtnModeChkRad ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr', array( 'style' => array( 'display' => $adminMsModes[ 'local' ] ? null : 'none' ) ) ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/chkNotMdfSince';
									$o .= ( Ui::CheckBox( esc_html_x( 'ChkNotMdfSinceChk', 'admin.Settings_Advanced_Common', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( Plugin::AdminBtnsBlock_GetPaidContent( $isPaidLockedContent ) ), Ui::AdminHelpBtnModeChkRad ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, true, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr', array( 'style' => array( 'display' => $adminMsModes[ 'local' ] ? null : 'none' ) ) ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/cntLen';
									$o .= ( Ui::CheckBox( esc_html_x( 'CntLenChk', 'admin.Settings_Advanced_Common', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( Plugin::AdminBtnsBlock_GetPaidContent( $isPaidLockedContent ) ), Ui::AdminHelpBtnModeChkRad ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr', array( 'style' => array( 'display' => $adminMsModes[ 'global' ] ? null : 'none' ) ) ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'cache/opAgentPostpone';
									$o .= ( Ui::CheckBox( esc_html_x( 'OpAgentPostponeChk', 'admin.Settings_Advanced_Common', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( Plugin::AdminBtnsBlock_GetPaidContent( $isPaidLockedContent ) ), Ui::AdminHelpBtnModeChkRad ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr', array( 'style' => array( 'display' => $adminMsModes[ 'global' ] ? null : 'none' ) ) ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'asyncMode';
									$o .= ( Ui::Label( sprintf( esc_html_x( 'AsyncMode_%1$s', 'admin.Settings_Advanced_Common', 'seraphinite-accelerator' ),
										Ui::ComboBox(
											'seraph_accel/' . $fldId,
											array(
												''		=> esc_html_x( 'Normal', 'admin.Settings_Advanced_AsyncMode', 'seraphinite-accelerator' ),
												'loc'		=> esc_html_x( 'Local', 'admin.Settings_Advanced_AsyncMode', 'seraphinite-accelerator' ),
												're_r'	=> esc_html_x( 'ReRoot', 'admin.Settings_Advanced_AsyncMode', 'seraphinite-accelerator' ),
												're'			=> esc_html_x( 'Re', 'admin.Settings_Advanced_AsyncMode', 'seraphinite-accelerator' ),
											),
											Gen::GetArrField( $sett, $fldId, '', '/' ), true, array( 'class' => 'inline' ) )
									) ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr', array( 'style' => array( 'display' => 'none' ) ) ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
								{
									$fldId = 'reLnch';
									$o .= ( Ui::TextBox( 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, '', '/' ), array( 'style' => array( 'width' => '100%' ) ), true ) );
									$o .= ( Ui::Tag( 'p', sprintf( Wp::GetLocString( array( 'PhpExtDirSuffix_%1$s%2$s', 'admin.Common_Msg' ), null, 'seraphinite-accelerator' ), '', ini_get( 'extension_dir' ) ), array( 'class' => 'description' ) ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Advanced_Cron', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Advanced_Cron' ) ) ), Ui::AdminHelpBtnModeText ) ) );
					{
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin() );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$o .= ( Ui::CheckBox( esc_html_x( 'CronEnableChk', 'admin.Settings_Advanced_Cron', 'seraphinite-accelerator' ), 'seraph_accel/cronEnable', IsCronEnabled(), true, array( 'disabled' => $adminMsModes[ 'global' ] ? null : true ) ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr', array( 'style' => array( 'display' => $adminMsModes[ 'local' ] ? null : 'none' ) ) ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
								{
									$fldId = 'cache/cron';
									$o .= ( Ui::CheckBox( esc_html_x( 'CronCompensatorChk', 'admin.Settings_Advanced_Cron', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr', array( 'style' => array( 'display' => $adminMsModes[ 'global' ] ? null : 'none' ) ) ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'asyncUseCron';
									$o .= ( Ui::CheckBox( esc_html_x( 'AsyncUseCronChk', 'admin.Settings_Advanced_Cron', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Advanced_Debug', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Advanced_Debug' ) ) ), Ui::AdminHelpBtnModeText ), array( 'style' => array( 'display' => $adminMsModes[ 'local' ] ? null : 'none' ) ) ) );
					{
						$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'class' => 'blck', 'style' => array( 'display' => 'none', 'width' => '100%' ) ) ) );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'debug';
									$o .= ( Ui::CheckBox( esc_html_x( 'EnableChk', 'admin.Settings_Advanced_Debug', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'hdrTrace';
									$o .= ( Ui::CheckBox( esc_html_x( 'HdrTraceEnableChk', 'admin.Settings_Advanced_Debug', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'debugInfo';
									$o .= ( Ui::CheckBox( esc_html_x( 'EnableInfoChk', 'admin.Settings_Advanced_Debug', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'log';
									$o .= ( Ui::CheckBox( vsprintf( esc_html_x( 'LogChk_%1$s%2$s%3$s%4$s', 'admin.Settings_Advanced_Debug', 'seraphinite-accelerator' ), array_merge( Ui::Link( array( '', '' ), Wp::GetSiteWpRootUrl( ltrim( substr( GetCacheDir(), strlen( ABSPATH ) ), "/\\" ) . LogGetRelativeFile() ), true ), Ui::Link( array( '', '' ), '#', false, null, array( 'onclick' => 'if(confirm("?"))seraph_accel.Settings._int.OnLogClear(this);return false' ) ) ) ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
								{
									$fldId = 'logScope/upd';
									$o .= ( Ui::CheckBox( esc_html_x( 'LogUpdChk', 'admin.Settings_Advanced_Debug', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
								{
									$fldId = 'logScope/srvClr';
									$o .= ( Ui::CheckBox( esc_html_x( 'LogSrvClrChk', 'admin.Settings_Advanced_Debug', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '1.5em' ) ) ) );
								{
									$fldId = 'logScope/request';
									$o .= ( Ui::CheckBox( esc_html_x( 'LogRequestChk', 'admin.Settings_Advanced_Debug', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '3.0em' ) ) ) );
								{
									$fldId = 'logScope/requestSkipped';
									$o .= ( Ui::CheckBox( esc_html_x( 'RequestSkippedChk', 'admin.Settings_Advanced_Debug', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '4.5em' ) ) ) );
								{
									$fldId = 'logScope/requestSkippedAdmin';
									$o .= ( Ui::CheckBox( esc_html_x( 'RequestSkippedAdminChk', 'admin.Settings_Advanced_Debug', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td', array( 'style' => array( 'padding-left' => '3.0em' ) ) ) );
								{
									$fldId = 'logScope/requestBots';
									$o .= ( Ui::CheckBox( esc_html_x( 'RequestBotsChk', 'admin.Settings_Advanced_Debug', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Advanced_Bots', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Advanced_Bots' ) ) ), Ui::AdminHelpBtnModeText ), array( 'style' => array( 'display' => $adminMsModes[ 'local' ] ? null : 'none' ) ) ) );
					{
						$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::TagOpen( 'div', array( 'class' => 'blck', 'style' => array( 'display' => 'none' ) ) ) );
						{
							$o .= ( Ui::Label( esc_html_x( 'AgentsLbl', 'admin.Settings_Views_Devices_Item', 'seraphinite-accelerator' ) ) );

							$fldId = 'bots/agents';
							$o .= ( _SettOutputAgentsEditor( $fldId, Gen::GetArrField( $sett, $fldId, array(), '/' ), _x( 'AgentsPhlr', 'admin.Settings_Cache_Devices', 'seraphinite-accelerator' ), 'seraph_accel' ) );
						}
						$o .= ( Ui::TagClose( 'div' ) );
					}
					$o .= ( Ui::SettBlock_Item_End() );

					$o .= ( Ui::SettBlock_Item_Begin( esc_html_x( 'Lbl', 'admin.Settings_Advanced_Test', 'seraphinite-accelerator' ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Advanced_Test' ) ) ), Ui::AdminHelpBtnModeText ), array( 'style' => array( 'display' => $adminMsModes[ 'local' ] ? null : 'none' ) ) ) );
					{
						$o .= ( Ui::ToggleButton( '.blck', array( 'style' => array( 'min-width' => '7em' ) ), array( 'class' => 'ctlSpaceVAfter' ) ) );
						$o .= ( Ui::SettBlock_ItemSubTbl_Begin( array( 'class' => 'blck', 'style' => array( 'display' => 'none', 'width' => '100%' ) ) ) );
						{
							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'test/contDelay';
									$fldIdEx = 'test/contDelayTimeout';
									$o .= ( Ui::CheckBox( sprintf( esc_html_x( 'TestContDelayChk_%1$s', 'admin.Settings_Advanced_Test', 'seraphinite-accelerator' ),
										Ui::NumberBox( 'seraph_accel/' . $fldIdEx, Gen::GetArrField( $sett, $fldIdEx, 0, '/' ) / 1000, array( 'min' => 0, 'placeholder' => '0', 'style' => array( 'width' => '4em' ) ), true )
									), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );

							$o .= ( Ui::TagOpen( 'tr' ) );
							{
								$o .= ( Ui::TagOpen( 'td' ) );
								{
									$fldId = 'test/contExtra';
									$fldIdEx = 'test/contExtraSize';
									$o .= ( Ui::CheckBox( sprintf( esc_html_x( 'TestContExtraChk_%1$s', 'admin.Settings_Advanced_Test', 'seraphinite-accelerator' ),
										Ui::NumberBox( 'seraph_accel/' . $fldIdEx, Gen::GetArrField( $sett, $fldIdEx, 0, '/' ) / 1024, array( 'min' => 0, 'placeholder' => '0', 'style' => array( 'width' => '7em' ) ), true )
									), 'seraph_accel/' . $fldId, Gen::GetArrField( $sett, $fldId, false, '/' ), true ) );
								}
								$o .= ( Ui::TagClose( 'td' ) );
							}
							$o .= ( Ui::TagClose( 'tr' ) );
						}
						$o .= ( Ui::SettBlock_ItemSubTbl_End() );
					}
					$o .= ( Ui::SettBlock_Item_End() );
				}
				$o .= ( Ui::SettBlock_End() );

				echo( $o );
			},
			get_defined_vars(), 'body', 'ctlInitHidden ns-nav-other', null
		);
	}

	{
		$htmlContent = Plugin::GetSettingsLicenseContent();
		if( !empty( $htmlContent ) )
			Ui::PostBoxes_MetaboxAdd( 'license', Plugin::GetSettingsLicenseTitle(), true, function( $callbacks_args, $box ) { echo( $box[ 'args' ][ 'c' ] ); }, array( 'c' => $htmlContent ), 'normal' );

		$htmlContent = Plugin::GetAdvertProductsContent( 'advertProducts' );
		if( !empty( $htmlContent ) )
			Ui::PostBoxes_MetaboxAdd( 'advertProducts', Plugin::GetAdvertProductsTitle(), false, function( $callbacks_args, $box ) { echo( $box[ 'args' ][ 'c' ] ); }, array( 'c' => $htmlContent ), 'normal' );
	}

	{
		$htmlContent = Plugin::GetRateItContent( 'rateIt', Plugin::DisplayContent_SmallBlock );
		if( !empty( $htmlContent ) )
			Ui::PostBoxes_MetaboxAdd( 'rateIt', Plugin::GetRateItTitle(), false, function( $callbacks_args, $box ) { echo( $box[ 'args' ][ 'c' ] ); }, array( 'c' => $htmlContent ), 'side' );

		$htmlContent = Plugin::GetLockedFeatureLicenseContent( Plugin::DisplayContent_SmallBlock );
		if( !empty( $htmlContent ) )
			Ui::PostBoxes_MetaboxAdd( 'switchToFull', Plugin::GetSwitchToFullTitle(), false, function( $callbacks_args, $box ) { echo( $box[ 'args' ][ 'c' ] ); }, array( 'c' => $htmlContent ), 'side' );

		Ui::PostBoxes_MetaboxAdd( 'about', Plugin::GetAboutPluginTitle(), false, function( $callbacks_args, $box ) { echo( Plugin::GetAboutPluginContent() ); }, null, 'side' );
		Ui::PostBoxes_MetaboxAdd( 'aboutVendor', Plugin::GetAboutVendorTitle(), false, function( $callbacks_args, $box ) { echo( Plugin::GetAboutVendorContent() ); }, null, 'side' );

		if( Gen::GetArrField( PluginRmtCfg::Get(), 'Prms.HostingBannerShow-Ext' ) )
			Ui::PostBoxes_MetaboxAdd( 'hostingBanner', esc_html_x( 'Title', 'admin.HostingBanner', 'seraphinite-accelerator' ), true, function( $callbacks_args, $box ) { echo( GetHostingBannerContent() ); }, null, 'side' );
	}

	Ui::PostBoxes( Plugin::GetSettingsTitle(), array( 'body' => array( 'nosort' => true ), 'normal' => array(), 'side' => array( 'nosort' => true ) ),
		array(
			'bodyContentBegin' => function( $callbacks_args )
			{
				extract( $callbacks_args );

				echo( Ui::TagOpen( 'form', array( 'id' => 'seraph-accel-form', 'method' => 'post', 'onsubmit' => 'return seraph_accel.Ui.Apply(this);' ) ) );

			},

			'bodyContentEnd' => function( $callbacks_args )
			{
				extract( $callbacks_args );

				Ui::PostBoxes_BottomGroupPanel(
					function( $callbacks_args )
					{
						echo( Plugin::Sett_SaveBtn( 'seraph_accel_saveSettings' ) );
					}
				);

				echo( Ui::TagClose( 'form' ) );
			}
		),
		get_defined_vars()
	);
}

function _GetTimeoutVal( $fldId, $args, $dayStartAt1 = false )
{
	$v = 0;

	$v += intval( $args[ $fldId . 'Mins' ] );
	$v += intval( $args[ $fldId . 'Hours' ] ) * ( 60 );

	$d = intval( $args[ $fldId . 'Days' ] );
	if( $dayStartAt1 && $d )
		$d -= 1;

	$v += $d * ( 24 * 60 );
	return( $v );
}

function _OnSaveSettings( $args )
{
	$adminMsModes = Wp::GetMultisiteAdminModes();
	$tmCur = time();

	$sett = Plugin::SettGet();

	if( $adminMsModes[ 'global' ] )
	{
		{ $fldId = 'cache/maxProc';							Gen::SetArrField( $sett, $fldId, @intval( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'cache/procInterval';					Gen::SetArrField( $sett, $fldId, @intval( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'cache/procIntervalShort';				Gen::SetArrField( $sett, $fldId, @intval( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'cache/procWorkInt';						Gen::SetArrField( $sett, $fldId, @floatval( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'cache/procPauseInt';					Gen::SetArrField( $sett, $fldId, @floatval( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'cache/forceAdvCache';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'cache/opAgentPostpone';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }

		{ $fldId = 'asyncUseCron';							Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'asyncMode';								Gen::SetArrField( $sett, $fldId, Gen::SanitizeId( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }

		{
			$fldId = 'reLnch';

			$v = Gen::SanitizeTextData( trim( (isset($args[ 'seraph_accel/' . $fldId ])?$args[ 'seraph_accel/' . $fldId ]:'') ) );
			if( $v )
				Gen::SetArrField( $sett, $fldId, $v, '/' );
			else
				Gen::UnsetArrField( $sett, $fldId, '/' );
		}
	}

	if( $adminMsModes[ 'local' ] )
	{
		$cacheDisabling = false;
		{
			$fldId = 'cache/enable';

			$v = isset( $args[ 'seraph_accel/' . $fldId ] );
			if( Gen::GetArrField( $sett, 'cache/enable', true, '/' ) && !$v )
				$cacheDisabling = true;

			Gen::SetArrField( $sett, $fldId, $v, '/' );
		}

		{ $fldId = 'cache/procMemLim';						Gen::SetArrField( $sett, $fldId, @intval( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{
			$fldId = 'cache/procTmLim';
			$v = @intval( $args[ 'seraph_accel/' . $fldId ] );
			Gen::SetArrField( $sett, $fldId, $v ? $v : 1, '/' );
		}

		{ $fldId = 'cache/srv';								Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'cache/srvClr';							Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'cache/nginx/fastCgiDir';				Gen::SetArrField( $sett, $fldId, Gen::SanitizeTextData( trim( $args[ 'seraph_accel/' . $fldId ] ) ), '/' ); }
		{ $fldId = 'cache/nginx/fastCgiLevels';				Gen::SetArrField( $sett, $fldId, Gen::SanitizeTextData( trim( $args[ 'seraph_accel/' . $fldId ] ) ), '/' ); }
		{ $fldId = 'cache/sucuri/apiKey';					Gen::SetArrField( $sett, $fldId, Gen::SanitizeTextData( trim( $args[ 'seraph_accel/' . $fldId ] ) ), '/' ); }
		{ $fldId = 'cache/sucuri/apiSecret';				Gen::SetArrField( $sett, $fldId, Gen::SanitizeTextData( trim( $args[ 'seraph_accel/' . $fldId ] ) ), '/' ); }

		{ $fldId = 'cache/cron';							Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }

		{ $fldId = 'cache/lazyInv';							Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'cache/lazyInvInitTmp';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'cache/lazyInvForcedTmp';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'cache/lazyInvTmp';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'cache/updPost';							Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'cache/updPostDelay';					Gen::SetArrField( $sett, $fldId, @intval( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'cache/updPostOp';						Gen::SetArrField( $sett, $fldId, @intval( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'cache/updPostDeps';						Gen::SetArrField( $sett, $fldId, Ui::TokensList_GetVal( (isset($args[ 'seraph_accel/' . $fldId ])?$args[ 'seraph_accel/' . $fldId ]:null), null, true ), '/' ); }
		{ $fldId = 'cache/updPostMeta';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'cache/updPostMetaExcl';					Gen::SetArrField( $sett, $fldId, Ui::TokensList_GetVal( (isset($args[ 'seraph_accel/' . $fldId ])?$args[ 'seraph_accel/' . $fldId ]:null), null, true ), '/' ); }
		{ $fldId = 'cache/updTerms';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'cache/updTermsOp';						Gen::SetArrField( $sett, $fldId, @intval( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'cache/updTermsDeps';					Gen::SetArrField( $sett, $fldId, Ui::TokensList_GetVal( (isset($args[ 'seraph_accel/' . $fldId ])?$args[ 'seraph_accel/' . $fldId ]:null), null, true ), '/' ); }
		{ $fldId = 'cache/updAllDeps';						Gen::SetArrField( $sett, $fldId, Ui::TokensList_GetVal( (isset($args[ 'seraph_accel/' . $fldId ])?$args[ 'seraph_accel/' . $fldId ]:null), null, true ), '/' ); }

		{
			$fldId = 'cache/updSche';
			Gen::SetArrField( $sett, $fldId, Ui::ItemsList_GetSaveItems( 'seraph_accel/' . $fldId, '/', $args,
				function( $cbArgs, $idItems, $itemKey, $item, $args )
				{
					$item = array();

					{ $fldId = 'enable';					Gen::SetArrField( $item, $fldId, isset( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ), '/' ); }
					{ $fldId = 'op';						Gen::SetArrField( $item, $fldId, @intval( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ), '/' ); }
					{ $fldId = 'prior';						Gen::SetArrField( $item, $fldId, @intval( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ), '/' ); }
					{ $fldId = 'period';					Gen::SetArrField( $item, $fldId, @intval( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ), '/' ); }
					{ $fldId = 'periodN';					Gen::SetArrField( $item, $fldId, @intval( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ), '/' ); }
					{ $fldId = 'deps';						Gen::SetArrField( $item, $fldId, Ui::TokensList_GetVal( (isset($args[ $idItems . '/' . $itemKey . '/' . $fldId ])?$args[ $idItems . '/' . $itemKey . '/' . $fldId ]:null), null, true ), '/' ); }

					{
						$fldId = 'times';
						Gen::SetArrField( $item, $fldId, Ui::ItemsList_GetSaveItems( $idItems . '/' . $itemKey . '/' . $fldId, '/', $args,
							function( $cbArgs, $idItems, $itemKey, $item, $args )
							{
								$item = array();

								{ $fldId = 'tm';			Gen::SetArrField( $item, $fldId, _GetTimeoutVal( $idItems . '/' . $itemKey . '/' . $fldId, $args, true ), '/' ); }
								{ $fldId = 'm';				Gen::SetArrField( $item, $fldId, @intval( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ) - 1, '/' ); }
								{ $fldId = 's';				Gen::SetArrField( $item, $fldId, @intval( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ), '/' ); }

								return( $item );
							}
						), '/' );
					}

					return( $item );
				}
			, null, 'c-' ), '/' );
		}

		{ $fldId = 'cache/autoProc';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'cache/normAgent';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'cache/cntLen';							Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }

		{
			$fldId = 'cache/chkNotMdfSince';

			$vPrev = Gen::GetArrField( $sett, $fldId, null, '/' );
			$v = isset( $args[ 'seraph_accel/' . $fldId ] );
			Gen::SetArrField( $sett, $fldId, $v, '/' );

			if( $vPrev != $v )
				Gen::SetArrField( $sett, '_LM/' . $fldId, $tmCur, '/' );
		}

		{ $fldId = 'cache/updByTimeout';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'cache/timeout';							Gen::SetArrField( $sett, $fldId, _GetTimeoutVal( 'seraph_accel/' . $fldId, $args ), '/' ); }
		{ $fldId = 'cache/timeoutFr';						Gen::SetArrField( $sett, $fldId, ( int )$args[ 'seraph_accel/' . $fldId ], '/' ); }
		{ $fldId = 'cache/timeoutCln';						Gen::SetArrField( $sett, $fldId, _GetTimeoutVal( 'seraph_accel/' . $fldId, $args ), '/' ); }
		{ $fldId = 'cache/ctxTimeoutCln';					Gen::SetArrField( $sett, $fldId, _GetTimeoutVal( 'seraph_accel/' . $fldId, $args ), '/' ); }
		{ $fldId = 'cache/autoClnPeriod';					Gen::SetArrField( $sett, $fldId, _GetTimeoutVal( 'seraph_accel/' . $fldId, $args ), '/' ); }
		{ $fldId = 'cache/useTimeoutClnForWpNonce';			Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'cache/hdrs';							Gen::SetArrField( $sett, $fldId, Ui::TokensList_GetVal( (isset($args[ 'seraph_accel/' . $fldId ])?$args[ 'seraph_accel/' . $fldId ]:null), null, true ), '/' ); }

		{ $fldId = 'cache/chunks/enable';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'cache/chunks/js';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'cache/chunks/css';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }

		{
			$fldId = 'cache/chunks/seps';
			Gen::SetArrField( $sett, $fldId, Ui::ItemsList_GetSaveItems( 'seraph_accel/' . $fldId, '/', $args,
				function( $cbArgs, $idItems, $itemKey, $item, $args )
				{
					$item = array();

					$side = 0;
					if( isset( $args[ $idItems . '/' . $itemKey . '/before' ] ) )
						$side |= 1;
					if( isset( $args[ $idItems . '/' . $itemKey . '/after' ] ) )
						$side |= 2;

					{ $fldId = 'enable';						Gen::SetArrField( $item, $fldId, isset( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ), '/' ); }
					{ $fldId = 'sel';							Gen::SetArrField( $item, $fldId, @trim( Wp::SanitizeXPath( Ui::UnmaskValue( @stripslashes( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ) ) ) ), '/' ); }
					{ $fldId = 'side';							Gen::SetArrField( $item, $fldId, $side, '/' ); }

					return( $item );
				}
			), '/' );
		}

		{
			$fldId = 'cache/encs';
			$v = array();
			$bNone = false;
			foreach( GetSupportedEncodingTypes() as $comprType => $comprTypeLbl )
			{
				if( isset( $args[ 'seraph_accel/' . $fldId . '/' . $comprType ] ) )
				{
					if( $comprType === '' )
						$bNone = true;
					$v[] = $comprType;
				}
			}

			if( !$bNone )
				$v[] = '';
			Gen::SetArrField( $sett, $fldId, $v, '/' );
		}

		{
			$fldId = 'cache/dataCompr';
			$v = array();
			foreach( GetSupportedCompressionTypes() as $comprType => $comprTypeLbl )
			{
				if( isset( $args[ 'seraph_accel/' . $fldId . '/' . $comprType ] ) )
					$v[] = $comprType;
			}

			if( empty( $v ) )
				$v[] = '';
			Gen::SetArrField( $sett, $fldId, $v, '/' );
		}
		{
			$fldId = 'cache/dataLvl';
			$v = array_map( function( $v ) { return( ( int )$v ); }, explode( ':', Wp::SanitizeText( $args[ 'seraph_accel/' . $fldId ] ) ) );
			for( $i = 0; $i < count( $v ); $i++ )
				if( !$v[ $i ] )
					array_splice( $v, $i--, 1 );
			if( count( $v ) )
				$v[ 0 ] = 1;
			Gen::SetArrField( $sett, $fldId, $v, '/' );
		}
		{ $fldId = 'cache/useDataComprAssets';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }

		{ $fldId = 'cache/views';							Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{
			$fldId = 'cache/viewsDeviceGrps';
			Gen::SetArrField( $sett, $fldId, Ui::ItemsList_GetSaveItems( 'seraph_accel/' . $fldId, '/', $args,
				function( $cbArgs, $idItems, $itemKey, $item, $args )
				{
					$item = array();

					$name = @trim( Wp::SanitizeText( (isset($args[ $idItems . '/' . $itemKey . '/name' ])?$args[ $idItems . '/' . $itemKey . '/name' ]:null) ) );

					$id = @trim( Wp::SanitizeId( (isset($args[ $idItems . '/' . $itemKey . '/id' ])?$args[ $idItems . '/' . $itemKey . '/id' ]:null) ) );
					if( !$id || !GetViewDisplayNameById( $id ) )
						$id = @str_replace( '-', '', @sanitize_title( $name ) );
					if( !$id )
						$id = '' . time();

					{ $fldId = 'enable';						Gen::SetArrField( $item, $fldId, isset( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ), '/' ); }
					{ $fldId = 'name';							Gen::SetArrField( $item, $fldId, $name, '/' ); }
					{ $fldId = 'id';							Gen::SetArrField( $item, $fldId, $id, '/' ); }
					{ $fldId = 'agents';						Gen::SetArrField( $item, $fldId, Ui::TokensList_GetVal( (isset($args[ $idItems . '/' . $itemKey . '/' . $fldId ])?$args[ $idItems . '/' . $itemKey . '/' . $fldId ]:null), null, true ), '/' ); }

					return( $item );
				}
			), '/' );
		}
		{
			$fldId = 'cache/viewsCompatGrps';
			Gen::SetArrField( $sett, $fldId, Ui::ItemsList_GetSaveItems( 'seraph_accel/' . $fldId, '/', $args,
				function( $cbArgs, $idItems, $itemKey, $item, $args )
				{
					$item = array();

					$name = @trim( Wp::SanitizeText( (isset($args[ $idItems . '/' . $itemKey . '/name' ])?$args[ $idItems . '/' . $itemKey . '/name' ]:null) ) );
					$id = @trim( Wp::SanitizeId( (isset($args[ $idItems . '/' . $itemKey . '/id' ])?$args[ $idItems . '/' . $itemKey . '/id' ]:null) ) );
					if( !$id )
						$id = @str_replace( '-', '', @sanitize_title( $name ) );
					if( !$id )
						$id = '' . time();

					{ $fldId = 'enable';						Gen::SetArrField( $item, $fldId, isset( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ), '/' ); }
					{ $fldId = 'name';							Gen::SetArrField( $item, $fldId, $name, '/' ); }
					{ $fldId = 'id';							Gen::SetArrField( $item, $fldId, $id, '/' ); }
					{ $fldId = 'agents';						Gen::SetArrField( $item, $fldId, Ui::TokensList_GetVal( (isset($args[ $idItems . '/' . $itemKey . '/' . $fldId ])?$args[ $idItems . '/' . $itemKey . '/' . $fldId ]:null), null, true ), '/' ); }

					return( $item );
				}
			), '/' );
		}
		{
			$fldId = 'cache/viewsGrps';
			Gen::SetArrField( $sett, $fldId, Ui::ItemsList_GetSaveItems( 'seraph_accel/' . $fldId, '/', $args,
				function( $cbArgs, $idItems, $itemKey, $item, $args )
				{
					$item = array();

					{ $fldId = 'enable';					Gen::SetArrField( $item, $fldId, isset( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ), '/' ); }
					{ $fldId = 'name';						Gen::SetArrField( $item, $fldId, Wp::SanitizeText( (isset($args[ $idItems . '/' . $itemKey . '/' . $fldId ])?$args[ $idItems . '/' . $itemKey . '/' . $fldId ]:null) ), '/' ); }
					{ $fldId = 'urisExcl';					Gen::SetArrField( $item, $fldId, _ArrToLwr( Ui::TokensList_GetVal( (isset($args[ $idItems . '/' . $itemKey . '/' . $fldId ])?$args[ $idItems . '/' . $itemKey . '/' . $fldId ]:null), null, true ), true ), '/' ); }
					{ $fldId = 'cookies';					Gen::SetArrField( $item, $fldId, _CookiesToLwr( Ui::TokensList_GetVal( (isset($args[ $idItems . '/' . $itemKey . '/' . $fldId ])?$args[ $idItems . '/' . $itemKey . '/' . $fldId ]:null), null, true ) ), '/' ); }
					{ $fldId = 'hdrs';						Gen::SetArrField( $item, $fldId, Ui::TokensList_GetVal( (isset($args[ $idItems . '/' . $itemKey . '/' . $fldId ])?$args[ $idItems . '/' . $itemKey . '/' . $fldId ]:null), null, true ), '/' ); }
					{ $fldId = 'args';						Gen::SetArrField( $item, $fldId, _ArrToLwr( Ui::TokensList_GetVal( (isset($args[ $idItems . '/' . $itemKey . '/' . $fldId ])?$args[ $idItems . '/' . $itemKey . '/' . $fldId ]:null) ) ), '/' ); }

					return( $item );
				}
			), '/' );
		}

		{ $fldId = 'cache/viewsGeo/enable';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{
			$fldId = 'cache/viewsGeo/grps';
			Gen::SetArrField( $sett, $fldId, Ui::ItemsList_GetSaveItems( 'seraph_accel/' . $fldId, '/', $args,
				function( $cbArgs, $idItems, $itemKey, $item, $args )
				{
					$item = array();

					{ $fldId = 'enable';					Gen::SetArrField( $item, $fldId, isset( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ), '/' ); }
					{ $fldId = 'name';						Gen::SetArrField( $item, $fldId, Wp::SanitizeText( (isset($args[ $idItems . '/' . $itemKey . '/' . $fldId ])?$args[ $idItems . '/' . $itemKey . '/' . $fldId ]:null) ), '/' ); }
					{ $fldId = 'items';						Gen::SetArrField( $item, $fldId, Ui::TokensList_GetVal( (isset($args[ $idItems . '/' . $itemKey . '/' . $fldId ])?$args[ $idItems . '/' . $itemKey . '/' . $fldId ]:null), function( $v ) { return( IsStrRegExp( $v ) ? $v : strtoupper( $v ) ); }, true ), '/' ); }

					return( $item );
				}
			, null, 'G^' ), '/' );
		}

		{ $fldId = 'cache/urisExcl';						Gen::SetArrField( $sett, $fldId, _ArrToLwr( Ui::TokensList_GetVal( (isset($args[ 'seraph_accel/' . $fldId ])?$args[ 'seraph_accel/' . $fldId ]:null), null, true ), true ), '/' ); }
		{ $fldId = 'cache/exclAgents';						Gen::SetArrField( $sett, $fldId, _ArrToLwr( Ui::TokensList_GetVal( (isset($args[ 'seraph_accel/' . $fldId ])?$args[ 'seraph_accel/' . $fldId ]:null) ) ), '/' ); }
		{ $fldId = 'cache/exclCookies';						Gen::SetArrField( $sett, $fldId, _CookiesToLwr( Ui::TokensList_GetVal( (isset($args[ 'seraph_accel/' . $fldId ])?$args[ 'seraph_accel/' . $fldId ]:null) ) ), '/' ); }
		{ $fldId = 'cache/exclArgsAll';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'cache/exclArgs';						Gen::SetArrField( $sett, $fldId, _ArrToLwr( Ui::TokensList_GetVal( (isset($args[ 'seraph_accel/' . $fldId ])?$args[ 'seraph_accel/' . $fldId ]:null), null, true ), true ), '/' ); }
		{ $fldId = 'cache/skipArgsAll';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'cache/skipArgs';						Gen::SetArrField( $sett, $fldId, _ArrToLwr( Ui::TokensList_GetVal( (isset($args[ 'seraph_accel/' . $fldId ])?$args[ 'seraph_accel/' . $fldId ]:null), null, true ), true ), '/' ); }
		{ $fldId = 'cache/exclConts';						Gen::SetArrField( $sett, $fldId, Ui::TokensList_GetVal( (isset($args[ 'seraph_accel/' . $fldId ])?$args[ 'seraph_accel/' . $fldId ]:null), 'seraph_accel\\Wp::SanitizeXPath', true ), '/' ); }

		{ $fldId = 'cache/ctx';								Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'cache/ctxSkip';							Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'cache/ctxSessSep';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'cache/ctxContPr';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'cache/ctxCliRefresh';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{
			$fldId = 'cache/ctxGrps';
			Gen::SetArrField( $sett, $fldId, Ui::ItemsList_GetSaveItems( 'seraph_accel/' . $fldId, '/', $args,
				function( $cbArgs, $idItems, $itemKey, $item, $args )
				{
					$item = array();

					{ $fldId = 'enable';						Gen::SetArrField( $item, $fldId, isset( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ), '/' ); }
					{ $fldId = 'name';							Gen::SetArrField( $item, $fldId, Wp::SanitizeText( (isset($args[ $idItems . '/' . $itemKey . '/' . $fldId ])?$args[ $idItems . '/' . $itemKey . '/' . $fldId ]:null) ), '/' ); }
					{ $fldId = 'cookies';						Gen::SetArrField( $item, $fldId, _CookiesToLwr( Ui::TokensList_GetVal( (isset($args[ $idItems . '/' . $itemKey . '/' . $fldId ])?$args[ $idItems . '/' . $itemKey . '/' . $fldId ]:null), null, true ) ), '/' ); }
					{ $fldId = 'args';							Gen::SetArrField( $item, $fldId, _ArrToLwr( Ui::TokensList_GetVal( (isset($args[ $idItems . '/' . $itemKey . '/' . $fldId ])?$args[ $idItems . '/' . $itemKey . '/' . $fldId ]:null) ) ), '/' ); }

					{
						$fldId = 'tables';
						Gen::SetArrField( $item, $fldId, Ui::ItemsList_GetSaveItems( $idItems . '/' . $itemKey . '/' . $fldId, '/', $args,
							function( $cbArgs, $idItems, $itemKey, $item, $args )
							{
								$item = array();

								{ $fldId = 'name';					Gen::SetArrField( $item, $fldId, Wp::SanitizeText( (isset($args[ $idItems . '/' . $itemKey . '/' . $fldId ])?$args[ $idItems . '/' . $itemKey . '/' . $fldId ]:null) ), '/' ); }
								{ $fldId = 'col';					Gen::SetArrField( $item, $fldId, Wp::SanitizeId( (isset($args[ $idItems . '/' . $itemKey . '/' . $fldId ])?$args[ $idItems . '/' . $itemKey . '/' . $fldId ]:null) ), '/' ); }
								{ $fldId = 'nameRel';				Gen::SetArrField( $item, $fldId, Wp::SanitizeId( (isset($args[ $idItems . '/' . $itemKey . '/' . $fldId ])?$args[ $idItems . '/' . $itemKey . '/' . $fldId ]:null) ), '/' ); }
								{ $fldId = 'colRel';				Gen::SetArrField( $item, $fldId, Wp::SanitizeId( (isset($args[ $idItems . '/' . $itemKey . '/' . $fldId ])?$args[ $idItems . '/' . $itemKey . '/' . $fldId ]:null) ), '/' ); }
								{ $fldId = 'colRelLink';			Gen::SetArrField( $item, $fldId, Wp::SanitizeId( (isset($args[ $idItems . '/' . $itemKey . '/' . $fldId ])?$args[ $idItems . '/' . $itemKey . '/' . $fldId ]:null) ), '/' ); }
								{ $fldId = 'condRel';				Gen::SetArrField( $item, $fldId, _Sett_SetCond( Ui::TokensList_GetVal( (isset($args[ $idItems . '/' . $itemKey . '/' . $fldId ])?$args[ $idItems . '/' . $itemKey . '/' . $fldId ]:null) ) ), '/' ); }

								return( $item );
							}
						), '/' );
					}

					return( $item );
				}
			), '/' );
		}

		{ $fldId = 'contPr/enable';							Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }

		{
			$fldId = 'contPr/normalize';
			$v = 0;

			if( isset( $args[ 'seraph_accel/' . $fldId . 'Lite' ] ) )
				$v |= 1;
			if( isset( $args[ 'seraph_accel/' . $fldId . 'LiteScrEncCorr' ] ) )
				$v |= 512;
			if( isset( $args[ 'seraph_accel/' . $fldId . 'Med' ] ) )
				$v |= 2;
			if( isset( $args[ 'seraph_accel/' . $fldId . 'Tidy' ] ) )
				$v |= 524288;

			Gen::SetArrField( $sett, $fldId, $v, '/' );
		}

		{ $fldId = 'contPr/normUrl';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/normUrlMode';					Gen::SetArrField( $sett, $fldId, @intval( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }

		{ $fldId = 'contPr/min';							Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cln/cmts';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cln/cmtsExcl';					Gen::SetArrField( $sett, $fldId, Ui::TokensList_GetVal( $args[ 'seraph_accel/' . $fldId ], null, true ), '/' ); }
		{ $fldId = 'contPr/cln/items';						Gen::SetArrField( $sett, $fldId, Ui::TokensList_GetVal( $args[ 'seraph_accel/' . $fldId ], 'seraph_accel\\Wp::SanitizeXPath', true ), '/' ); }
		{ $fldId = 'contPr/earlyPaint';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }

		{ $fldId = 'contPr/lazy/bjs';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/lazy/p';							Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/lazy/items';						Gen::SetArrField( $sett, $fldId, Ui::TokensList_GetVal( $args[ 'seraph_accel/' . $fldId ], 'seraph_accel\\Wp::SanitizeXPath', true ), '/' ); }

		{ $fldId = 'contPr/fresh/smoothAppear';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/fresh/items';					Gen::SetArrField( $sett, $fldId, Ui::TokensList_GetVal( $args[ 'seraph_accel/' . $fldId ], 'seraph_accel\\Wp::SanitizeXPath', true ), '/' ); }

		{ $fldId = 'contPr/img/srcAddLm';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/img/sysFlt';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/img/inlSml';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/img/inlSmlSize';					Gen::SetArrField( $sett, $fldId, @intval( round( @floatval( $args[ 'seraph_accel/' . $fldId ] ) * 1024 ) ), '/' ); }
		{ $fldId = 'contPr/img/deinlLrg';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/img/deinlLrgSize';				Gen::SetArrField( $sett, $fldId, @intval( round( @floatval( $args[ 'seraph_accel/' . $fldId ] ) * 1024 ) ), '/' ); }
		{ $fldId = 'contPr/img/excl';						Gen::SetArrField( $sett, $fldId, Ui::TokensList_GetVal( $args[ 'seraph_accel/' . $fldId ], 'seraph_accel\\Wp::SanitizeXPath', true ), '/' ); }
		{ $fldId = 'contPr/img/webp/enable';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/img/webp/redir';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/img/webp/prms/q';				Gen::SetArrField( $sett, $fldId, @intval( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/img/avif/enable';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/img/avif/redir';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/img/avif/prms/q';				Gen::SetArrField( $sett, $fldId, @intval( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/img/avif/prms/s';				Gen::SetArrField( $sett, $fldId, @intval( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/img/redirCacheAdapt';			Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/img/redirOwn';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/img/szAdaptImg';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/img/szAdaptBg';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/img/szAdaptExcl';				Gen::SetArrField( $sett, $fldId, Ui::TokensList_GetVal( $args[ 'seraph_accel/' . $fldId ], 'seraph_accel\\Wp::SanitizeXPath', true ), '/' ); }
		{ $fldId = 'contPr/img/szAdaptBgCxMin';				Gen::SetArrField( $sett, $fldId, @intval( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/img/szAdaptDpr';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/img/lazy/setSize';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/img/lazy/load';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/img/lazy/own';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/img/lazy/smoothAppear';			Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/img/lazy/del3rd';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/img/lazy/excl';					Gen::SetArrField( $sett, $fldId, Ui::TokensList_GetVal( $args[ 'seraph_accel/' . $fldId ], 'seraph_accel\\Wp::SanitizeXPath', true ), '/' ); }
		{ $fldId = 'contPr/img/cacheExt';					Gen::SetArrField( $sett, $fldId, Ui::TokensList_GetVal( $args[ 'seraph_accel/' . $fldId ], null, true ), '/' ); }

		{ $fldId = 'contPr/frm/lazy/enable';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/frm/lazy/yt';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/frm/lazy/vm';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/frm/lazy/elmntrBg';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/frm/lazy/youTubeFeed';			Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }

		{ $fldId = 'contPr/cp/sldBdt';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/swBdt';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/vidJs';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/elmntrAni';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/elmntrSpltAni';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/elmntrTrxAni';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/elmntrBgSldshw';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/elmntrVids';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/elmntrTabs';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/elmntrAccrdn';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/elmntrAdvTabs';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/elmntrPremTabs';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/elmntrNavMenu';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/elmntrPremNavMenu';			Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/elmntrPremScrl';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/elmntrPremCrsl';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/elmntrWdgtGal';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/elmntrWdgtImgCrsl';			Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/elmntrWdgtWooPrdImgs';		Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/elmntrWdgtCntr';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/elmntrWdgtAvoShcs';			Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/elmntrWdgtLott';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/elmntrWdgtPrmLott';			Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/nktrLott';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/elmntrStck';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/elmntrShe';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/elmntrStrtch';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/qodefApprAni';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/prtThSkel';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/astrRsp';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/ntBlueThRspnsv';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/mdknThRspnsv';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/fltsmThBgFill';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/fltsmThAni';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/ukSldshw';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/ukBgImg';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/ukAni';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/ukGrid';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/ukModal';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/ukHghtVwp';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/ukNavBar';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/tmHdr';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/sldN2Ss';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/sldRev';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/sldRev_SmthLd';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/sldRev7';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }

		{ $fldId = 'contPr/cp/tdThumbCss';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/elmsKitImgCmp';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/elmsKitLott';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/haCrsl';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/xooelTabs';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/phtncThmb';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/jetMobMenu';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/jetLott';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/jetCrsl';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/jetCrslPst';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/diviMvImg';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/diviMvText';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/diviMvSld';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/diviMvFwHdr';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/diviVidBox';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/diviVidBg';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/diviVidFr';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/diviDsmGal';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/diviLzStls';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/diviPrld';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/diviStck';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/diviAni';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/diviDataAni';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/diviHdr';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/brcksAni';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/kdncThAni';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/scrlSeq';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/fusionBgVid';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/fsnEqHghtCols';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/fsnAni';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/thrvAni';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/phloxThRspnsv';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/phloxThAni';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/mkImgSrcSet';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/woodmartPrcFlt';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/wooPrcFlt';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/wbwPrdFlt';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/wooJs';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/wpStrs';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/txpTagGrps';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/eaelSmpMnu';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/wprAniTxt';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/wprTabs';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/suTabs';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/upbAni';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/upbBgImg';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/upbCntVid';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/ultRspnsv';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/ultVcHd';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/ultAni';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/the7Ani';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/the7MblHdr';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/sbThAni';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/esntlsThAni';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/beThAni';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/merimagBgImg';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/mdcrLdng';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/prmmprssLzStls';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/mnmgImg';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/tldBgImg';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/jqVide';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/jqSldNivo';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/wooSctrCntDwnTmr';			Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/lottGen';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/sprflMenu';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/jqJpPlr';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/prstPlr';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/cp/grnshftPbAosOnceAni';			Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }

		{ $fldId = 'contPr/js/optLoad';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }

		{ $fldId = 'contPr/js/cplxDelay';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/js/preLoadEarly';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/js/loadFast';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/js/aniDelay';					Gen::SetArrField( $sett, $fldId, @intval( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/js/scrlDelay';					Gen::SetArrField( $sett, $fldId, @intval( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/js/clk/delay';					Gen::SetArrField( $sett, $fldId, @intval( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/js/clk/excl';					Gen::SetArrField( $sett, $fldId, Ui::TokensList_GetVal( $args[ 'seraph_accel/' . $fldId ], 'seraph_accel\\Wp::SanitizeXPath', true ), '/' ); }
		{ $fldId = 'contPr/js/clk/exclDef';					Gen::SetArrField( $sett, $fldId, Ui::TokensList_GetVal( $args[ 'seraph_accel/' . $fldId ], 'seraph_accel\\Wp::SanitizeXPath', true ), '/' ); }

		{ $fldId = 'contPr/js/groupCritSpec';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/js/groupNonCrit';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/js/groupExclMdls';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/js/groupExcls';					Gen::SetArrField( $sett, $fldId, Ui::TokensList_GetVal( $args[ 'seraph_accel/' . $fldId ], null, true ), '/' ); }
		{ $fldId = 'contPr/js/min';							Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/js/minExcls';					Gen::SetArrField( $sett, $fldId, Ui::TokensList_GetVal( $args[ 'seraph_accel/' . $fldId ], null, true ), '/' ); }
		{ $fldId = 'contPr/js/cprRem';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/js/nonCrit/inl';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/js/nonCrit/int';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/js/nonCrit/ext';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/js/nonCrit/excl';				Gen::SetArrField( $sett, $fldId, !empty( @$args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/js/nonCrit/items';				Gen::SetArrField( $sett, $fldId, Ui::TokensList_GetVal( $args[ 'seraph_accel/' . $fldId ], null, true ), '/' ); }
		{ $fldId = 'contPr/js/nonCrit/timeout/enable';		Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/js/nonCrit/timeout/v';			Gen::SetArrField( $sett, $fldId, @intval( round( @floatval( $args[ 'seraph_accel/' . $fldId ] ) * 1000 ) ), '/' ); }
		{ $fldId = 'contPr/js/critSpec/timeout/enable';		Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/js/critSpec/timeout/v';			Gen::SetArrField( $sett, $fldId, @intval( round( @floatval( $args[ 'seraph_accel/' . $fldId ] ) * 1000 ) ), '/' ); }
		{ $fldId = 'contPr/js/critSpec/items';				Gen::SetArrField( $sett, $fldId, Ui::TokensList_GetVal( $args[ 'seraph_accel/' . $fldId ], null, true ), '/' ); }
		{ $fldId = 'contPr/js/spec/timeout/enable';			Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/js/spec/timeout/v';				Gen::SetArrField( $sett, $fldId, @intval( round( @floatval( $args[ 'seraph_accel/' . $fldId ] ) * 1000 ) ), '/' ); }
		{ $fldId = 'contPr/js/spec/items';					Gen::SetArrField( $sett, $fldId, Ui::TokensList_GetVal( $args[ 'seraph_accel/' . $fldId ], null, true ), '/' ); }
		{ $fldId = 'contPr/js/skips';						Gen::SetArrField( $sett, $fldId, Ui::TokensList_GetVal( $args[ 'seraph_accel/' . $fldId ], null, true ), '/' ); }
		{ $fldId = 'contPr/js/other/incl';					Gen::SetArrField( $sett, $fldId, Ui::TokensList_GetVal( $args[ 'seraph_accel/' . $fldId ], 'seraph_accel\\Wp::SanitizeXPath', true ), '/' ); }

		{ $fldId = 'contPr/css/optLoad';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/css/inlAsSrc';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/css/inlCrit';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/css/inlNonCrit';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/css/delayNonCritWithJs';			Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/css/bfrJs';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/css/group';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/css/groupCombine';				Gen::SetArrField( $sett, $fldId, $args[ 'seraph_accel/' . $fldId ] === '1', '/' ); }
		{ $fldId = 'contPr/css/groupNonCrit';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/css/groupNonCritCombine';		Gen::SetArrField( $sett, $fldId, $args[ 'seraph_accel/' . $fldId ] === '1', '/' ); }
		{ $fldId = 'contPr/css/groupFont';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/css/groupFontCombine';			Gen::SetArrField( $sett, $fldId, $args[ 'seraph_accel/' . $fldId ] === '1', '/' ); }
		{ $fldId = 'contPr/css/fontPreload';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/css/sepImp';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/css/min';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/css/corrErr';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/css/nonCrit/auto';				Gen::SetArrField( $sett, $fldId, $args[ 'seraph_accel/' . $fldId ] == 'auto', '/' ); }
		{ $fldId = 'contPr/css/nonCrit/autoExcls';			Gen::SetArrField( $sett, $fldId, Ui::TokensList_GetVal( $args[ 'seraph_accel/' . $fldId ], null, true ), '/' ); }
		{ $fldId = 'contPr/css/nonCrit/inl';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/css/nonCrit/int';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/css/nonCrit/ext';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/css/nonCrit/excl';				Gen::SetArrField( $sett, $fldId, !empty( (isset($args[ 'seraph_accel/' . $fldId ])?$args[ 'seraph_accel/' . $fldId ]:null) ), '/' ); }
		{ $fldId = 'contPr/css/nonCrit/items';				Gen::SetArrField( $sett, $fldId, Ui::TokensList_GetVal( $args[ 'seraph_accel/' . $fldId ], null, true ), '/' ); }
		{ $fldId = 'contPr/css/skips';						Gen::SetArrField( $sett, $fldId, Ui::TokensList_GetVal( $args[ 'seraph_accel/' . $fldId ], null, true ), '/' ); }
		{ $fldId = 'contPr/css/fontOptLoad';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/css/fontOptLoadDisp';			Gen::SetArrField( $sett, $fldId, Wp::SanitizeId( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/css/font/optLoadNameExpr';		Gen::SetArrField( $sett, $fldId, Wp::SanitizeText( Ui::UnmaskValue( @stripslashes( $args[ 'seraph_accel/' . $fldId ] ) ) ), '/' ); }
		{ $fldId = 'contPr/css/fontCrit';					Gen::SetArrField( $sett, $fldId, !isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/css/font/deinlLrg';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'contPr/css/font/deinlLrgSize';			Gen::SetArrField( $sett, $fldId, @intval( round( @floatval( $args[ 'seraph_accel/' . $fldId ] ) * 1024 ) ), '/' ); }

		{
			$fldId = 'contPr/rpl/items';
			Gen::SetArrField( $sett, $fldId, Ui::ItemsList_GetSaveItems( 'seraph_accel/' . $fldId, '/', $args,
				function( $cbArgs, $idItems, $itemKey, $item, $args )
				{
					$item = array();

					{ $fldId = 'enable';						Gen::SetArrField( $item, $fldId, isset( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ), '/' ); }
					{ $fldId = 'expr';							Gen::SetArrField( $item, $fldId, @trim( Ui::UnmaskValue( @stripslashes( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ) ) ), '/' ); }
					{ $fldId = 'data';							Gen::SetArrField( $item, $fldId, Ui::UnmaskValue( @stripslashes( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ) ), '/' ); }

					return( $item );
				}
			), '/' );
		}

		{
			$fldId = 'contPr/css/custom';
			Gen::SetArrField( $sett, $fldId, Ui::ItemsList_GetSaveItems( 'seraph_accel/' . $fldId, '/', $args,
				function( $cbArgs, $idItems, $itemKey, $item, $args )
				{
					$item = array();

					{ $fldId = 'enable';						Gen::SetArrField( $item, $fldId, isset( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ), '/' ); }
					{ $fldId = 'noJsDl';						Gen::SetArrField( $item, $fldId, isset( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ), '/' ); }
					{ $fldId = 'descr';							Gen::SetArrField( $item, $fldId, Wp::SanitizeText( @stripslashes( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ) ), '/' ); }
					{ $fldId = 'data';							Gen::SetArrField( $item, $fldId, Wp::SanitizeCss( @stripslashes( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ) ), '/' ); }

					return( $item );
				}
			), '/' );
		}

		{
			$fldId = 'contPr/cdn/items';
			Gen::SetArrField( $sett, $fldId, Ui::ItemsList_GetSaveItems( 'seraph_accel/' . $fldId, '/', $args,
				function( $cbArgs, $idItems, $itemKey, $item, $args )
				{
					$item = array();

					{ $fldId = 'enable';						Gen::SetArrField( $item, $fldId, isset( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ), '/' ); }
					{ $fldId = 'sa';							Gen::SetArrField( $item, $fldId, isset( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ), '/' ); }
					{ $fldId = 'addr';							Gen::SetArrField( $item, $fldId, @rtrim( @trim( Wp::SanitizeUrl( (isset($args[ $idItems . '/' . $itemKey . '/' . $fldId ])?$args[ $idItems . '/' . $itemKey . '/' . $fldId ]:null) ) ), '/' ), '/' ); }
					{ $fldId = 'types';							Gen::SetArrField( $item, $fldId, _ArrToLwr( Ui::TokensList_GetVal( (isset($args[ $idItems . '/' . $itemKey . '/' . $fldId ])?$args[ $idItems . '/' . $itemKey . '/' . $fldId ]:null) ) ), '/' ); }
					{ $fldId = 'uris';							Gen::SetArrField( $item, $fldId, _ArrToLwr( Ui::TokensList_GetVal( (isset($args[ $idItems . '/' . $itemKey . '/' . $fldId ])?$args[ $idItems . '/' . $itemKey . '/' . $fldId ]:null) ) ), '/' ); }
					{ $fldId = 'urisExcl';						Gen::SetArrField( $item, $fldId, _ArrToLwr( Ui::TokensList_GetVal( (isset($args[ $idItems . '/' . $itemKey . '/' . $fldId ])?$args[ $idItems . '/' . $itemKey . '/' . $fldId ]:null) ) ), '/' ); }

					return( $item );
				}
			), '/' );
		}
		{ $fldId = 'contPr/cdn/enable';						Gen::SetArrField( $sett, $fldId, _Cdn_IsEnabled( Gen::GetArrField( $sett, 'contPr/cdn', array(), '/' ) ), '/' ); }

		{
			$fldId = 'contPr/grps/items';
			Gen::SetArrField( $sett, $fldId, Ui::ItemsList_GetSaveItems( 'seraph_accel/' . $fldId, '/', $args,
				function( $cbArgs, $idItems, $itemKey, $item, $args )
				{
					$item = array();

					{
						$v = 0;
						if( isset( $args[ $idItems . '/' . $itemKey . '/settOvr' ] ) )
							$v |= 1;
						if( isset( $args[ $idItems . '/' . $itemKey . '/lrn' ] ) )
							$v |= 2;
						$fldId = 'enable';								Gen::SetArrField( $item, $fldId, $v, '/' );
					}

					{ $fldId = 'name';									Gen::SetArrField( $item, $fldId, @stripslashes( (isset($args[ $idItems . '/' . $itemKey . '/' . $fldId ])?$args[ $idItems . '/' . $itemKey . '/' . $fldId ]:'') ), '/' ); }
					{ $fldId = 'patterns';								Gen::SetArrField( $item, $fldId, Ui::TokensList_GetVal( (isset($args[ $idItems . '/' . $itemKey . '/' . $fldId ])?$args[ $idItems . '/' . $itemKey . '/' . $fldId ]:null), 'seraph_accel\\Wp::SanitizeXPath', true ), '/' ); }
					{ $fldId = 'urisIncl';								Gen::SetArrField( $item, $fldId, Ui::TokensList_GetVal( (isset($args[ $idItems . '/' . $itemKey . '/' . $fldId ])?$args[ $idItems . '/' . $itemKey . '/' . $fldId ]:null), null, true ), '/' ); }
					{ $fldId = 'argsIncl';								Gen::SetArrField( $item, $fldId, _ArrToLwr( Ui::TokensList_GetVal( (isset($args[ $idItems . '/' . $itemKey . '/' . $fldId ])?$args[ $idItems . '/' . $itemKey . '/' . $fldId ]:null), null, true ), true ), '/' ); }
					{ $fldId = 'views';									Gen::SetArrField( $item, $fldId, Ui::TokensList_GetVal( (isset($args[ $idItems . '/' . $itemKey . '/' . $fldId ])?$args[ $idItems . '/' . $itemKey . '/' . $fldId ]:null) ), '/' ); }

					{ $fldId = 'sklSrch';								Gen::SetArrField( $item, $fldId, isset( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ), '/' ); }
					{ $fldId = 'sklExcl';								Gen::SetArrField( $item, $fldId, Ui::TokensList_GetVal( (isset($args[ $idItems . '/' . $itemKey . '/' . $fldId ])?$args[ $idItems . '/' . $itemKey . '/' . $fldId ]:null), 'seraph_accel\\Wp::SanitizeXPath', true ), '/' ); }
					{ $fldId = 'sklCssSelExcl';							Gen::SetArrField( $item, $fldId, Ui::TokensList_GetVal( (isset($args[ $idItems . '/' . $itemKey . '/' . $fldId ])?$args[ $idItems . '/' . $itemKey . '/' . $fldId ]:null), null, true ), '/' ); }

					{ $fldId = 'contPr/enable';							Gen::SetArrField( $item, $fldId, isset( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ), '/' ); }
					{ $fldId = 'contPr/jsOvr';							Gen::SetArrField( $item, $fldId, isset( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ), '/' ); }
					{ $fldId = 'contPr/js/optLoad';						Gen::SetArrField( $item, $fldId, isset( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ), '/' ); }
					{ $fldId = 'contPr/js/nonCrit/timeout/enable';		Gen::SetArrField( $item, $fldId, isset( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ), '/' ); }
					{ $fldId = 'contPr/js/nonCrit/timeout/v';			Gen::SetArrField( $item, $fldId, @intval( round( @floatval( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ) * 1000 ) ), '/' ); }
					{ $fldId = 'contPr/js/spec/timeout/enable';			Gen::SetArrField( $item, $fldId, isset( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ), '/' ); }
					{ $fldId = 'contPr/js/spec/timeout/v';				Gen::SetArrField( $item, $fldId, @intval( round( @floatval( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ) * 1000 ) ), '/' ); }
					{ $fldId = 'contPr/jsNonCritScopeOvr';				Gen::SetArrField( $item, $fldId, isset( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ), '/' ); }
					{ $fldId = 'contPr/js/nonCrit/inl';					Gen::SetArrField( $item, $fldId, isset( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ), '/' ); }
					{ $fldId = 'contPr/js/nonCrit/int';					Gen::SetArrField( $item, $fldId, isset( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ), '/' ); }
					{ $fldId = 'contPr/js/nonCrit/ext';					Gen::SetArrField( $item, $fldId, isset( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ), '/' ); }
					{ $fldId = 'contPr/js/nonCrit/excl';				Gen::SetArrField( $item, $fldId, !empty( @$args[ $idItems . '/' . $itemKey . '/' . $fldId ] ), '/' ); }
					{ $fldId = 'contPr/js/nonCrit/items';				Gen::SetArrField( $item, $fldId, Ui::TokensList_GetVal( $args[ $idItems . '/' . $itemKey . '/' . $fldId ], null, true ), '/' ); }
					{ $fldId = 'contPr/cssOvr';							Gen::SetArrField( $item, $fldId, isset( $args[ $idItems . '/' . $itemKey . '/' . $fldId ] ), '/' ); }
					{ $fldId = 'contPr/css/nonCrit/auto';				Gen::SetArrField( $item, $fldId, $args[ $idItems . '/' . $itemKey . '/' . $fldId ] == 'auto', '/' ); }

					return( $item );
				}
			, null, 'c-' ), '/' );
		}

		{ $fldId = 'cacheBr/enable';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'cacheBr/timeout';						Gen::SetArrField( $sett, $fldId, _GetTimeoutVal( 'seraph_accel/' . $fldId, $args ), '/' ); }

		{ $fldId = 'test/contDelay';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'test/contDelayTimeout';					Gen::SetArrField( $sett, $fldId, @intval( $args[ 'seraph_accel/' . $fldId ] ) * 1000, '/' ); }
		{ $fldId = 'test/contExtra';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'test/contExtraSize';					Gen::SetArrField( $sett, $fldId, @intval( $args[ 'seraph_accel/' . $fldId ] ) * 1024, '/' ); }

		{ $fldId = 'bots/agents';							Gen::SetArrField( $sett, $fldId, Ui::TokensList_GetVal( (isset($args[ 'seraph_accel/' . $fldId ])?$args[ 'seraph_accel/' . $fldId ]:null), null, true ), '/' ); }

		{ $fldId = 'debug';									Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'hdrTrace';								Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'debugInfo';								Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'emojiIcons';							Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }

		{ $fldId = 'log';									Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'logScope/upd';							Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'logScope/srvClr';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'logScope/request';						Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'logScope/requestSkipped';				Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'logScope/requestSkippedAdmin';			Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
		{ $fldId = 'logScope/requestBots';					Gen::SetArrField( $sett, $fldId, isset( $args[ 'seraph_accel/' . $fldId ] ), '/' ); }
	}

	{
		$svc = Gen::GetArrField( Wp::GetFilters( 'woocommerce_get_geolocation', array( 'WC_Integration_MaxMind_Geolocation', 'get_geolocation' ) ), array( 0, 'f', 0 ) );
		$dbFile = $svc ? $svc -> get_database_service() -> get_database_path() : null;
		Gen::SetArrField( $sett, 'cache/viewsGeo/fileMmDb', $dbFile, '/' );
	}

	$hr = ApplySettings( $sett );

	{
		$cronEnable = isset( $args[ 'seraph_accel/cronEnable' ] );
		if( IsCronEnabled() != $cronEnable )
			$hr = Gen::HrAccom( $hr, Php::File_SetDefineVal( Wp::GetConfigFilePath(), 'DISABLE_WP_CRON', !$cronEnable ) );
	}

	if( Plugin::AsyncTaskGetTime( 'CheckUpdatePostProcessAddPostponed' ) )
		Plugin::AsyncTaskPost( 'CheckUpdatePostProcessAddPostponed', null, array( time() + Gen::GetArrField( $sett, array( 'cache', 'updPostDelay' ), 0 ), 60 * 60 ), true, true );

	CacheInitClearProcessor( true );
	CacheInitOperScheduler( true );

	return( $hr );
}

function ApplySettings( $sett, $changedUpdateCache = true )
{
	$hr = Gen::S_OK;
	$hr = Gen::HrAccom( $hr, Plugin::SettSet( $sett ) );
	$hr = Gen::HrAccom( $hr, CacheInitEnv( $sett ) );

	if( $changedUpdateCache )
		Plugin::StateUpdateFlds( array( 'settChangedUpdateCache' => true ) );

	return( $hr );
}

function _Cdn_IsEnabled( $settCdn )
{
	foreach( Gen::GetArrField( $settCdn, array( 'items' ), array() ) as $item )
		if( $item[ 'enable' ] && $item[ 'addr' ] )
			return( true );

	return( false );
}

function _ArrToLwr( $arr, $bExpr = false )
{
	if( !$bExpr )
		return( array_map( 'strtolower', $arr ) );

	return( array_map(
		function( $expr )
		{
			$aExpr = ExprConditionsSet_Parse( $expr );
			if( !count( $aExpr ) || ( count( $aExpr ) == 1 && !IsStrRegExp( $aExpr[ 0 ][ 'expr' ] ) ) )
				$expr = strtolower( $expr );
			return( $expr );
		}
	, $arr ) );
}

function _CookiesToLwr( $arr )
{
	return( array_map(
		function( $e )
		{
			$posVal = strpos( $e, '=' );
			if( $posVal === false )
				return( strtolower( $e ) );
			return( strtolower( substr( $e, 0, $posVal ) ) . substr( $e, $posVal ) );
		}
	, $arr ) );
}

function _Sett_GetCond( $arr )
{
	$items = array();

	foreach( $arr as $col => $vals )
		$items[] = $col . ' = ' . implode( ', ', $vals );

	return( $items );
}

function _Sett_SetCond( $items )
{
	$arr = array();

	foreach( $items as $item )
	{
		list( $col, $vals ) = explode( '=', $item );
		$arr[ trim( $col ) ] = array_map( 'trim', explode( ',', $vals ) );
	}

	return( $arr );
}

