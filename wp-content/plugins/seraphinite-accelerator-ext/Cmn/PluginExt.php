<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

require( __DIR__ . '/LicCli.php' );

class PluginLic
{
	static function GetStateId()
	{
		$data = PluginOptions::Get( self::STG_VER, 'Lic', __CLASS__ . '::' );

		$licInfo = $data[ 'data' ];
		if( !$licInfo )
			return( 'not_active' );

		switch( $licInfo[ 'hr' ] )
		{
		case Lic::S_LICMGR_LIC_EXPIRED:		return( 'expired' );
		case Lic::S_LICMGR_LIC_INACTIVE:	return( 'suspended' );
		case Lic::E_LICMGR_LIC_BLOCKED:		return( 'blocked' );
		case Lic::E_LICMGR_EP_NOT_ACTIVE:	return( 'not_active' );
		}

		if( !Gen::HrSucc( $licInfo[ 'hr' ] ) )
			return( 'error' );

		return( 'active' );
	}

	static function GetStateErrorCode()
	{
		$data = PluginOptions::Get( self::STG_VER, 'Lic', __CLASS__ . '::' );

		$licInfo = $data[ 'data' ];
		if( !$licInfo )
			return( Gen::S_OK );
		return( $licInfo[ 'hr' ] );
	}

	static function GetKey()
	{
		$data = PluginOptions::Get( self::STG_VER, 'Lic', __CLASS__ . '::' );

		$licInfo = $data[ 'data' ];
		if( !$licInfo )
			return( NULL );
		return( $licInfo[ 'key' ] );
	}

	static function CheckFeature( $feature = Lic::DefFeature )
	{
		$data = PluginOptions::Get( self::STG_VER, 'Lic', __CLASS__ . '::' );

		$licInfo = $data[ 'data' ];
		if( !$licInfo )
			return( Lic::E_LICMGR_LIC_INVALID );

		$features = $licInfo[ 'features' ];
		if( !is_array( $features ) || !count( $features ) )
			return( $licInfo[ 'hr' ] );

		return( in_array( $feature, $features ) ? Gen::S_OK : Gen::S_FALSE );
	}

	static function Activate( $key )
	{

		$key = Lic::GetKeyIdFromText( $key );
		if( !$key )
			return( Gen::E_INVALIDARG );

		$data = PluginOptions::Get( self::STG_VER, 'Lic', __CLASS__ . '::' );

		$licInfo = $data[ 'data' ];
		if( $licInfo && Gen::HrSucc( $licInfo[ 'hr' ] ) )
			return( Gen::S_FALSE );

		PluginRmtCfg::Update( true );

		$srvUrl = Gen::GetArrField( PluginRmtCfg::Get(), 'Prms.UrlLicMgr' );
		if( !$srvUrl )
			return( Gen::E_INVALID_STATE );

		$licRes = LicCli::Action( $srvUrl, '594EDF52DE173808712397', LicCli::Act_Activate, $key, 'wordpress-accelerator', '2.22.16', Wp::GetSiteId(), Wp::GetSiteDisplayName() );
		$licSrvRes = $licRes[ 'response' ];
		if( !$licSrvRes )
			return( $licRes[ 'hr' ] );

		$hr = $licSrvRes[ 'hr' ];
		if( !Gen::HrSucc( $hr ) )
			return( $hr );

		$licInfo = array( 'hr' => $hr, 'key' => $key, 'features' => $licSrvRes[ 'features' ], 'sid' => 1 );
		$data[ 'data' ] = $licInfo;

		$hrOp = PluginOptions::Set( self::STG_VER, 'Lic', $data, __CLASS__ . '::' );
		if( Gen::HrFail( $hrOp ) )
			return( $hrOp );

		return( $hr );
	}

	static function Deactivate()
	{
		$data = PluginOptions::Get( self::STG_VER, 'Lic', __CLASS__ . '::' );

		$licInfo = $data[ 'data' ];
		if( !$licInfo )
			return( Gen::S_FALSE );

		PluginRmtCfg::Update( true );

		$srvUrl = Gen::GetArrField( PluginRmtCfg::Get(), 'Prms.UrlLicMgr' );
		if( !$srvUrl )
			return( Gen::E_INVALID_STATE );

		$licRes = LicCli::Action( $srvUrl, '594EDF52DE173808712397', LicCli::Act_Deactivate, $licInfo[ 'key' ], 'wordpress-accelerator', '2.22.16', Wp::GetSiteId( isset( $licInfo[ 'sid' ] ) ? null : 'OLD' ) );
		$licSrvRes = $licRes[ 'response' ];
		if( !$licSrvRes )
			$hr = $licRes[ 'hr' ];
		else
			$hr = $licSrvRes[ 'hr' ];

		$licInfo = NULL;
		$data[ 'data' ] = $licInfo;

		$hrOp = PluginOptions::Set( self::STG_VER, 'Lic', $data, __CLASS__ . '::' );
		if( Gen::HrFail( $hrOp ) )
			return( $hrOp );

		return( $hr );
	}

	static function Update()
	{
		$data = PluginOptions::Get( self::STG_VER, 'Lic', __CLASS__ . '::' );

		$licInfo = $data[ 'data' ];
		if( !$licInfo )
			return( Gen::S_FALSE );

		$srvUrl = Gen::GetArrField( PluginRmtCfg::Get(), 'Prms.UrlLicMgr' );
		if( !$srvUrl )
			return( Gen::E_INVALID_STATE );

		$licRes = LicCli::Action( $srvUrl, '594EDF52DE173808712397', LicCli::Act_Check, $licInfo[ 'key' ], 'wordpress-accelerator', '2.22.16', Wp::GetSiteId( isset( $licInfo[ 'sid' ] ) ? null : 'OLD' ) );
		$licSrvRes = $licRes[ 'response' ];
		if( !$licSrvRes )
			return( $licRes[ 'hr' ] );

		$hr = $licSrvRes[ 'hr' ];

		$licInfo[ 'features' ] = (isset($licSrvRes[ 'features' ])?$licSrvRes[ 'features' ]:null);
		$licInfo[ 'hr' ] = $hr;

		$data[ 'data' ] = $licInfo;

		$hrOp = PluginOptions::Set( self::STG_VER, 'Lic', $data, __CLASS__ . '::' );
		if( Gen::HrFail( $hrOp ) )
			return( $hrOp );

		return( Gen::S_OK );
	}

	static function GetDataUrl( $item )
	{
		$data = PluginOptions::Get( self::STG_VER, 'Lic', __CLASS__ . '::' );

		$licInfo = $data[ 'data' ];
		if( !$licInfo )
			return( NULL );

		$srvUrl = Gen::GetArrField( PluginRmtCfg::Get(), 'Prms.UrlLicMgr' );
		if( !$srvUrl )
			return( Gen::E_INVALID_STATE );

		$features = (isset($licInfo[ 'features' ])?$licInfo[ 'features' ]:null);
		if( !is_array( $features ) || !count( $features ) )
			return( NULL );

		return( LicCli::GetDataUrl( $srvUrl, '594EDF52DE173808712397', $licInfo[ 'key' ], 'wordpress-accelerator', '2.22.16', Wp::GetSiteId( isset( $licInfo[ 'sid' ] ) ? null : 'OLD' ), $features[ 0 ], $item ) );
	}

	const STG_VER = 1;

	static function OnOptRead_Lic( $data, $verFrom )
	{
		if( is_string( (isset($data[ 'data' ])?$data[ 'data' ]:null) ) )
			$data[ 'data' ] = Gen::Unserialize( Gen::StrDecode( $data[ 'data' ] ) );
		else
			$data[ 'data' ] = NULL;

		return( $data );
	}

	static function OnOptWrite_Lic( $data )
	{
		if( is_array( $data[ 'data' ] ) && count( $data[ 'data' ] ) )
			$data[ 'data' ] = Gen::StrEncode( Gen::Serialize( $data[ 'data' ] ) );
		else
			$data[ 'data' ] = NULL;

		return( $data );
	}
}

class PluginExt
{
	static function SwitchToFull()
	{

		{
			if( PluginLic::CheckFeature() != Gen::S_OK )
				return( null );

			if( Plugin::_IsSwitchingActive() )
				return( null );

			$res = PluginExt::GetSwitchToFullContent( Plugin::DisplayContent_Block, Ui::Tag( 'strong', esc_html_x( 'PluginTitleFull', 'admin.Common', 'seraphinite-accelerator' ) ) );
			if( !$res )
				return( null );

			Plugin::_admin_printscriptsstyles();
			return( Ui::BannerMsg( Ui::MsgSucc, $res, 0 ) );
		}

	}

	static function GetSwitchToFullTitle()
	{
		return( esc_html_x( 'FullTitle', 'admin.Common_SwitchTo', 'seraphinite-accelerator' ) );
	}

	static function GetSwitchToFullContent( $type = Plugin::DisplayContent_Block, $contentBefore = '', $contentInside = '' )
	{
		$rmtCfg = PluginRmtCfg::Get();

		$dwnldUrl = PluginLic::GetDataUrl( Gen::GetArrField( $rmtCfg, 'Prms.FullProductDownloadPath' ) );
		if( !$dwnldUrl )
			return( null );

		$res = '';

		if( !empty( $contentInside ) )
			$res .= $contentInside . ' ';

		$res .= vsprintf( Wp::safe_html_x( 'FullInfo_%1$s%2$s%3$s%4$s', 'admin.Common_SwitchTo', 'seraphinite-accelerator' ), array_merge( Ui::Link( array( '', '' ), Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Links.UrlProductFeatures' ), true ), Ui::Link( array( '', '' ), $dwnldUrl, true ) ) );

		return( ( empty( $contentBefore ) ? '' : $contentBefore ) . Plugin::_GetSwitchToContent( $rmtCfg, $type == Plugin::DisplayContent_SmallBlock ? esc_html_x( 'FullSmallBtn', 'admin.Common_SwitchTo', 'seraphinite-accelerator' ) : esc_html_x( 'FullBtn', 'admin.Common_SwitchTo', 'seraphinite-accelerator' ), 'full', $res ) );
	}

	static function ActivateDeactivateResult( $res )
	{
		if( !is_array( $res ) )
			return( null );

		$res = (isset($res[ 'activate' ])?$res[ 'activate' ]:null);
		if( $res === null )
			return( null );

		$hr = $res[ 'hr' ];
		$msg = PluginExt::GetLicActionResultContent( $res[ 'action' ], $hr );

		$msgSeverity = ( $hr == Gen::S_OK ) ? Ui::MsgSucc : ( Gen::HrSucc( $hr ) ? Ui::MsgWarn : Ui::MsgErr );
		return( Ui::BannerMsg( (isset($msg[ 'severity' ])?$msg[ 'severity' ]:null), (isset($msg[ 'content' ])?$msg[ 'content' ]:null), Ui::MsgOptDismissible ) );
	}

	static function ActivateDeactivate( $args )
	{
		$actionArg = 'seraph_accel_activate';
		if( !isset( $args[ $actionArg ] ) )
			return;

		$hr = Gen::S_OK;

		switch( $args[ $actionArg ] )
		{
		case '1':
			$hr = PluginLic::Activate( $args[ 'seraph_accel_licKey' ] );
			$act = LicCli::Act_Activate;
			break;

		case '0':
			$hr = PluginLic::Deactivate();
			$act = LicCli::Act_Deactivate;
			break;
		}

		Plugin::ReloadWithPostOpRes( array( 'activate' => array( 'hr' => $hr, 'action' => $act ) ) );
		exit();
	}

	static function GetOrderInfoText( $rmtCfg )
	{
		return( vsprintf( esc_html_x( 'OrderInfo_%1$s%2$s%3$s%4$s', 'admin.Common_SwitchTo', 'seraphinite-accelerator' ), Gen::ArrFlatten( array(
			Ui::Link( array( '', '' ), Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Links.UrlUserPurchases' ), true ),
			Ui::Link( Ui::Tag( 'strong', array( '', '' ) ), Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Links.UrlProductFeatures' ), true ) )
		) ) );
	}

	static function GetOrderContent()
	{
		if( PluginLic::CheckFeature() != Lic::E_LICMGR_LIC_INVALID )
			return( NULL );

		$rmtCfg = PluginRmtCfg::Get();

		$res = '';
		$res .= Ui::Button( esc_html_x( 'OrderBtn', 'admin.Common_SwitchTo', 'seraphinite-accelerator' ), true, false, 'seraph_accel_btnwarn ctlSpaceAfter ctlVaMiddle', 'button', array( 'onclick' => 'window.open( \'' . Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Links.UrlProductBuy' ) .  '\', \'_blank\' );' ) );
		$res .= Ui::Tag( 'span', self::GetOrderInfoText( $rmtCfg ), array( 'class' => 'description ctlVaMiddle' ) );

		return( $res );
	}

	static function GetLicStateContent()
	{
		$statusText = NULL;
		$descrText = NULL;

		$rmtCfg = PluginRmtCfg::Get();

		$state = PluginLic::GetStateId();
		switch( $state )
		{
		case 'not_active':		$statusText = esc_html_x( 'State_NotActive', 'admin.Common_Lic', 'seraphinite-accelerator' ); break;
		case 'expired':			$statusText = esc_html_x( 'State_Expired', 'admin.Common_Lic', 'seraphinite-accelerator' ); $descrText = vsprintf( esc_html_x( 'State_Expired_Descr_%1$s%2$s', 'admin.Common_Lic', 'seraphinite-accelerator' ), Gen::ArrFlatten( array( Ui::Link( array( '', '' ), Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Links.UrlUserPurchases' ), true ) ) ) ); break;
		case 'suspended':		$statusText = esc_html_x( 'State_Suspended', 'admin.Common_Lic', 'seraphinite-accelerator' ); $descrText = vsprintf( esc_html_x( 'State_Suspended_Descr_%1$s%2$s', 'admin.Common_Lic', 'seraphinite-accelerator' ), Gen::ArrFlatten( array( Ui::Link( array( '', '' ), Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Links.UrlUserPurchases' ), true ) ) ) ); break;
		case 'blocked':			$statusText = esc_html_x( 'State_Blocked', 'admin.Common_Lic', 'seraphinite-accelerator' ); $descrText = vsprintf( esc_html_x( 'State_Blocked_Descr_%1$s%2$s', 'admin.Common_Lic', 'seraphinite-accelerator' ), Gen::ArrFlatten( array( Ui::Link( array( '', '' ), Gen::GetArrField( $rmtCfg, 'Links.UrlSupport' ), true ) ) )); break;
		case 'error':			$statusText = esc_html_x( 'State_Error', 'admin.Common_Lic', 'seraphinite-accelerator' ); $descrText = vsprintf( esc_html_x( 'State_Error_Descr_%1$s%2$s%3$s', 'admin.Common_Lic', 'seraphinite-accelerator' ), Gen::ArrFlatten( array( Plugin::GetErrorDescr( PluginLic::GetStateErrorCode() ), Ui::Link( array( '', '' ), Gen::GetArrField( $rmtCfg, 'Links.UrlSupport' ), true ) ) )); break;

		default:				$statusText = esc_html_x( 'State_Active', 'admin.Common_Lic', 'seraphinite-accelerator' ); break;
		}

		return( $statusText . Ui::Tag( 'p', Ui::Tag( 'span', $descrText, array( 'class' => 'description' ) ) ) );
	}

	static function GetLicActionResultContent( $action, $hr )
	{
		$res = NULL;

		$rmtCfg = PluginRmtCfg::Get();

		if( Gen::HrSucc( $hr ) )
		{
			$resType = ( $hr == Gen::S_OK ) ? Ui::MsgSucc : Ui::MsgWarn;

			$resAction = '';
			switch( $action )
			{
			case LicCli::Act_Activate:		$resAction = esc_html_x( 'ActivateOk', 'admin.Common_Lic_ActionResult', 'seraphinite-accelerator' ); break;
			case LicCli::Act_Deactivate:	$resAction = esc_html_x( 'DeactivateOk', 'admin.Common_Lic_ActionResult', 'seraphinite-accelerator' ); break;
			}

			switch( $hr )
			{
			case Lic::S_LICMGR_EP_ALREADY_ACTIVE:	$res = sprintf( esc_html_x( 'Ok_EpAlreadyActive_%1$s', 'admin.Common_Lic_ActionResult_Suffix', 'seraphinite-accelerator' ), $resAction ); $resType = Ui::MsgSucc; break;
			case Lic::S_LICMGR_EP_ALREADY_INACTIVE:	$res = sprintf( esc_html_x( 'Ok_EpAlreadyInactive_%1$s', 'admin.Common_Lic_ActionResult_Suffix', 'seraphinite-accelerator' ), $resAction ); $resType = Ui::MsgSucc; break;

			case Lic::S_LICMGR_LIC_INACTIVE:		$res = vsprintf( esc_html_x( 'Ok_LicInactive_%1$s%2$s%3$s', 'admin.Common_Lic_ActionResult_Suffix', 'seraphinite-accelerator' ), Gen::ArrFlatten( array( $resAction, Ui::Link( array( '', '' ), Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Links.UrlUserPurchases' ), true ) ) ) ); break;
			case Lic::S_LICMGR_LIC_EXPIRED:			$res = vsprintf( esc_html_x( 'Ok_LicExpired_%1$s%2$s%3$s', 'admin.Common_Lic_ActionResult_Suffix', 'seraphinite-accelerator' ), Gen::ArrFlatten( array( $resAction, Ui::Link( array( '', '' ), Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Links.UrlUserPurchases' ), true ) ) ) ); break;

			default:								$res = sprintf( esc_html_x( 'Ok_%1$s', 'admin.Common_Lic_ActionResult_Suffix', 'seraphinite-accelerator' ), $resAction ); break;
			}

			return( array( 'severity' => $resType, 'content' => $res ) );
		}

		$resType = Ui::MsgErr;

		$resAction = '';
		switch( $action )
		{
		case LicCli::Act_Activate:		$resAction = esc_html_x( 'ActivateErr', 'admin.Common_Lic_ActionResult', 'seraphinite-accelerator' ); break;
		case LicCli::Act_Deactivate:	$resAction = esc_html_x( 'DeactivateErr', 'admin.Common_Lic_ActionResult', 'seraphinite-accelerator' ); break;
		}

		switch( $hr )
		{
		case Gen::E_INVALIDARG:									$res = vsprintf( esc_html_x( 'Err_InvalidArg_%1$s%2$s%3$s', 'admin.Common_Lic_ActionResult_Suffix', 'seraphinite-accelerator' ), Gen::ArrFlatten( array( $resAction, Ui::Link( array( '', '' ), Gen::GetArrField( $rmtCfg, 'Links.UrlSupport' ), true ) ) ) ); break;
		case Gen::E_INTERNAL:									$res = vsprintf( esc_html_x( 'Err_Internal_%1$s%2$s%3$s', 'admin.Common_Lic_ActionResult_Suffix', 'seraphinite-accelerator' ), Gen::ArrFlatten( array( $resAction, Ui::Link( array( '', '' ), Gen::GetArrField( $rmtCfg, 'Links.UrlSupport' ), true ) ) ) ); break;

		case Lic::E_LICMGR_EP_ACTIVATION_LIMIT_REACHED:			$res = vsprintf( esc_html_x( 'Err_EpActivationLimitReached_%1$s%2$s%3$s', 'admin.Common_Lic_ActionResult_Suffix', 'seraphinite-accelerator' ), Gen::ArrFlatten( array( $resAction, Ui::Link( array( '', '' ), Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Links.UrlUserPurchases' ), true ) ) ) ); break;
		case Lic::E_LICMGR_EP_NOT_ACTIVE:						$res = vsprintf( esc_html_x( 'Err_EpNotActive_%1$s', 'admin.Common_Lic_ActionResult_Suffix', 'seraphinite-accelerator' ), Gen::ArrFlatten( array( $resAction ) ) ); break;
		case Lic::E_LICMGR_LIC_INVALID:							$res = vsprintf( esc_html_x( 'Err_LicInvalid_%1$s', 'admin.Common_Lic_ActionResult_Suffix', 'seraphinite-accelerator' ), Gen::ArrFlatten( array( $resAction ) ) ); break;
		case Lic::E_LICMGR_LIC_BLOCKED:							$res = vsprintf( esc_html_x( 'Err_LicBlocked_%1$s%2$s%3$s', 'admin.Common_Lic_ActionResult_Suffix', 'seraphinite-accelerator' ), Gen::ArrFlatten( array( $resAction, Ui::Link( array( '', '' ), Gen::GetArrField( $rmtCfg, 'Links.UrlSupport' ), true ) ) ) ); break;
		case Lic::E_LICMGR_UNIT_INVALID:						$res = vsprintf( esc_html_x( 'Err_UnitInvalid_%1$s', 'admin.Common_Lic_ActionResult_Suffix', 'seraphinite-accelerator' ), Gen::ArrFlatten( array( $resAction ) ) ); break;

		default:												$res = vsprintf( esc_html_x( 'Err_%1$s%2$s%3$s%4$s', 'admin.Common_Lic_ActionResult_Suffix', 'seraphinite-accelerator' ), Gen::ArrFlatten( array( $resAction, Plugin::GetErrorDescr( $hr ), Ui::Link( array( '', '' ), Gen::GetArrField( $rmtCfg, 'Links.UrlSupport' ), true ) ) ) ); break;
		}

		return( array( 'severity' => $resType, 'content' => $res ) );
	}

	static function GetSettingsLicenseContent()
	{
		$res = '';

		$rmtCfg = PluginRmtCfg::Get();

		$res .= Ui::Tag( 'p', Plugin::SwitchToExt(), null, false, array( 'noTagsIfNoContent' => true, 'afterContent' => Ui::SepLine( 'p' ) ) );

		$licKey = PluginLic::GetKey();

		$res .= Ui::TagOpen( 'form', array( 'method' => 'post' ) );
		$res .= Ui::TagOpen( 'input', array( 'type' => 'hidden', 'name' => 'seraph_accel_activate', 'value' => ( $licKey ? '0' : '1' ) ), true );
		$res .= Ui::SettBlock_Begin();

		{

			{
				$res .= Ui::SettBlock_Item_Begin( esc_html_x( 'StateLabel', 'admin.Common_Lic', 'seraphinite-accelerator' ) );
				{
					$res .= Ui::Tag( 'label', PluginExt::GetLicStateContent() );

				}
				$res .= Ui::SettBlock_Item_End();
			}

			{
				$res .= Ui::SettBlock_Item_Begin( esc_html_x( 'KeyLabel', 'admin.Common_Lic', 'seraphinite-accelerator' ) );
				{
					if( !$licKey )
						$res .= Ui::Tag( 'div', Ui::TextBox( 'seraph_accel_licKey', Lic::GetKeyDisplayText( $licKey ), array( 'placeholder' => 'Enter license key', 'style' => array( 'width' => '100%' ), 'class' => 'ctlSpaceVAfter' ), true ) );

					$res .= Ui::TagOpen( 'div' );
					{
						$res .= Ui::Button( $licKey ? esc_html_x( 'DeactivateBtn', 'admin.Common_Lic', 'seraphinite-accelerator' ) : esc_html_x( 'ActivateBtn', 'admin.Common_Lic', 'seraphinite-accelerator' ), true, false, 'ctlSpaceAfter ctlVaMiddle' );

						$htmlOrderContent = PluginExt::GetOrderContent();
						if( $htmlOrderContent )
							$res .= $htmlOrderContent;
					}
					$res .= Ui::TagClose( 'div' );

					if( $licKey )
						$res .= Ui::Tag( 'p', vsprintf( esc_html_x( 'ActivationNotice_%1$s%2$s', 'admin.Common_Lic', 'seraphinite-accelerator' ), Gen::ArrFlatten( array( Ui::Link( array( '', '' ), Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Links.UrlUserPurchases' ), true ) ) ) ), array( 'class' => 'description' ) );
				}
				$res .= Ui::SettBlock_Item_End();
			}
		}

		$res .= Ui::SettBlock_End();
		$res .= Ui::TagClose( 'form' );

		return( $res );
	}

	static function GetLockedFeatureLicenseContent( $type = Plugin::DisplayContent_Block, $contentBefore = '', $contentInside = '' )
	{
		$rmtCfg = PluginRmtCfg::Get();

		if( !Gen::GetArrField( $rmtCfg, 'Prms.FullProductDownloadPath' ) )
			return( null );

		$licOk = PluginLic::CheckFeature() == Gen::S_OK;

		$res = '';

		$txtSwitch = $licOk ? PluginExt::GetSwitchToFullContent( $type, $contentBefore, $contentInside ) : null;
		if( !$txtSwitch )
		{
			$res .= $contentBefore . Ui::TagOpen( 'p' );
			{
				$res .= $contentInside;
				if( !empty( $contentInside ) )
					$res .= ' ';
				$res .= self::GetOrderInfoText( $rmtCfg );
				$res .= Ui::TagClose( 'p' );
			}
			$res .= Ui::Tag( 'p', Ui::Button( $type == Plugin::DisplayContent_SmallBlock ? esc_html_x( 'OrderInLockedFeatureSmallBtn', 'admin.Common_SwitchTo', 'seraphinite-accelerator' ) : esc_html_x( 'OrderInLockedFeatureBtn', 'admin.Common_SwitchTo', 'seraphinite-accelerator' ), true, null, 'seraph_accel_btnwarn ctlVaMiddle', 'button', array( 'onclick' => 'window.open(\'' . Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Links.UrlProductBuy' ) .  '\', \'_blank\')' ) ) . Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Paid, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Links.UrlProductBuy' ) ) ), Ui::AdminHelpBtnModeBtn ) );
		}
		else
			$res .= $txtSwitch;

		return( $res );
	}
}

