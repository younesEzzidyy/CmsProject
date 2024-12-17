<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

function _SettingsWizardPage()
{
	Plugin::CmnScripts( array( 'Cmn', 'Gen', 'Ui', 'Net', 'AdminUi' ) );
	wp_register_script( Plugin::ScriptId( 'Admin' ), add_query_arg( Plugin::GetFileUrlPackageParams(), Plugin::FileUrl( 'Admin.js', __FILE__ ) ), array_merge( array( 'jquery' ), Plugin::CmnScriptId( array( 'Cmn', 'Gen', 'Ui', 'Net' ) ) ), '2.22.16' );
	Plugin::Loc_ScriptLoad( Plugin::ScriptId( 'Admin' ) );
	wp_enqueue_script( Plugin::ScriptId( 'Admin' ) );

	Plugin::DisplayAdminFooterRateItContent();

	$rmtCfg = PluginRmtCfg::Get();
	$isMultisiteMain = Wp::IsMultisiteMain();

	Ui::PostBoxes_MetaboxAdd( 'wizard', Wp::GetLocString( array( 'TitleWiz', 'admin.Common_Settings' ), null, 'seraphinite-accelerator' ) . Ui::Tag( 'span', Ui::AdminBtnsBlock( array( array( 'type' => Ui::AdminBtn_Help, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.SettingsWiz' ) ) ), Ui::AdminHelpBtnModeBlockHeader ) ), false,
		function( $callbacks_args, $box )
		{
			extract( $box[ 'args' ] );

			$vDefDontChange = !Gen::GetArrField( Plugin::StateGet(), array( 'settWiz' ) );

			echo( Ui::TagOpen( 'div', array( 'class' => 'wizPages' ) ) );
			{

				echo( Ui::TagOpen( 'div' ) );
				{
					$fldId = 'fullOrSimple';

					echo( Ui::Tag( 'p', Wp::safe_html_x( 'Info', 'admin.SetupWizard_Mode', 'seraphinite-accelerator' ) ) );

					echo( Ui::TagOpen( 'p' ) );
					{
						echo( Ui::RadioBox( Wp::safe_html_x( 'FullRad', 'admin.SetupWizard_Mode', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, 'full', true ) );
						echo( Ui::Tag( 'span', Wp::safe_html_x( 'FullInfo', 'admin.SetupWizard_Mode', 'seraphinite-accelerator' ), array( 'class' => 'chkradSubBlock' ) ) );
					}
					echo( Ui::TagClose( 'p' ) );

					echo( Ui::TagOpen( 'p' ) );
					{
						echo( Ui::RadioBox( Wp::safe_html_x( 'SimpleRad', 'admin.SetupWizard_Mode', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, 'simple' ) );
						echo( Ui::Tag( 'span', Wp::safe_html_x( 'SimpleInfo', 'admin.SetupWizard_Mode', 'seraphinite-accelerator' ), array( 'class' => 'chkradSubBlock' ) ) );
					}
					echo( Ui::TagClose( 'p' ) );
				}
				echo( Ui::TagClose( 'div' ) );

				echo( Ui::TagOpen( 'div' ) );
				{
					echo( Ui::Tag( 'p', Wp::safe_html_x( 'Info', 'admin.SetupWizard_SelfDiag', 'seraphinite-accelerator' ) ) );

					echo( Ui::Tag( 'p', _SelfDiagContent() ) );
				}
				echo( Ui::TagClose( 'div' ) );

				echo( Ui::TagOpen( 'div' ) );
				{
					$fldId = 'optPrior';
					$vDef = $vDefDontChange ? '' : 'score';

					echo( Ui::Tag( 'p', sprintf( Wp::safe_html_x( 'Info_%1$s', 'admin.SetupWizard_OptPrior', 'seraphinite-accelerator' ), $isMultisiteMain ? Wp::safe_html_x( 'Info_1', 'admin.SetupWizard_OptPrior', 'seraphinite-accelerator' ) : '' ) ) );

					echo( Ui::TagOpen( 'p' ) );
					{
						echo( Ui::RadioBox( Wp::safe_html_x( 'ScoreRad', 'admin.SetupWizard_OptPrior', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, 'score', $vDef == 'score' ) );
						echo( Ui::Tag( 'span', Wp::safe_html_x( 'ScoreInfo', 'admin.SetupWizard_OptPrior', 'seraphinite-accelerator' ), array( 'class' => 'chkradSubBlock' ) ) );
					}
					echo( Ui::TagClose( 'p' ) );

					echo( Ui::TagOpen( 'p' ) );
					{
						echo( Ui::RadioBox( Wp::safe_html_x( 'FreshRad', 'admin.SetupWizard_OptPrior', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, 'fresh', $vDef == 'fresh' ) );
						echo( Ui::Tag( 'span', Wp::safe_html_x( 'FreshInfo', 'admin.SetupWizard_OptPrior', 'seraphinite-accelerator' ), array( 'class' => 'chkradSubBlock' ) ) );
					}
					echo( Ui::TagClose( 'p' ) );

					if( $vDefDontChange )
					{
						echo( Ui::TagOpen( 'p' ) );
						{
							echo( Ui::RadioBox( Wp::safe_html_x( 'DontModifyRad', 'admin.SetupWizard', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, '', $vDef == '' ) );
							echo( Ui::Tag( 'span', Wp::safe_html_x( 'DontModifyInfo', 'admin.SetupWizard', 'seraphinite-accelerator' ), array( 'class' => 'chkradSubBlock' ) ) );
						}
						echo( Ui::TagClose( 'p' ) );
					}
				}
				echo( Ui::TagClose( 'div' ) );

				if( $isMultisiteMain )
				{
					echo( Ui::TagOpen( 'div' ) );
					{
						$fldId = 'hostPerf';
						$vDef = $vDefDontChange ? '' : 'low';

						echo( Ui::Tag( 'p', Wp::safe_html_x( 'Info', 'admin.SetupWizard_Host', 'seraphinite-accelerator' ) ) );

						echo( Ui::TagOpen( 'p' ) );
						{
							echo( Ui::RadioBox( Wp::safe_html_x( 'HighRad', 'admin.SetupWizard_Host', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, 'high', $vDef == 'high' ) );
							echo( Ui::Tag( 'span', Wp::safe_html_x( 'HighInfo', 'admin.SetupWizard_Host', 'seraphinite-accelerator' ), array( 'class' => 'chkradSubBlock' ) ) );
						}
						echo( Ui::TagClose( 'p' ) );

						echo( Ui::TagOpen( 'p' ) );
						{
							echo( Ui::RadioBox( Wp::safe_html_x( 'MedRad', 'admin.SetupWizard_Host', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, 'med', $vDef == 'med' ) );
							echo( Ui::Tag( 'span', Wp::safe_html_x( 'MedInfo', 'admin.SetupWizard_Host', 'seraphinite-accelerator' ), array( 'class' => 'chkradSubBlock' ) ) );
						}
						echo( Ui::TagClose( 'p' ) );

						echo( Ui::TagOpen( 'p' ) );
						{
							echo( Ui::RadioBox( Wp::safe_html_x( 'LowRad', 'admin.SetupWizard_Host', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, 'low', $vDef == 'low' ) );
							echo( Ui::Tag( 'span', Wp::safe_html_x( 'LowInfo', 'admin.SetupWizard_Host', 'seraphinite-accelerator' ), array( 'class' => 'chkradSubBlock' ) ) );
						}
						echo( Ui::TagClose( 'p' ) );

						if( $vDefDontChange )
						{
							echo( Ui::TagOpen( 'p' ) );
							{
								echo( Ui::RadioBox( Wp::safe_html_x( 'DontModifyRad', 'admin.SetupWizard', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, '', $vDef == '' ) );
								echo( Ui::Tag( 'span', Wp::safe_html_x( 'DontModifyInfo', 'admin.SetupWizard', 'seraphinite-accelerator' ), array( 'class' => 'chkradSubBlock' ) ) );
							}
							echo( Ui::TagClose( 'p' ) );
						}
					}
					echo( Ui::TagClose( 'div' ) );
				}

				echo( Ui::TagOpen( 'div' ) );
				{
					$fldId = 'mobileDep';
					$vDef = $vDefDontChange ? '' : 'dep';

					echo( Ui::Tag( 'p', Wp::safe_html_x( 'Info', 'admin.SetupWizard_Mobiles', 'seraphinite-accelerator' ) ) );

					echo( Ui::TagOpen( 'p' ) );
					{
						echo( Ui::RadioBox( Wp::safe_html_x( 'DepRad', 'admin.SetupWizard_Mobiles', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, 'dep', $vDef == 'dep' ) );
						echo( Ui::Tag( 'span', Wp::safe_html_x( 'DepInfo', 'admin.SetupWizard_Mobiles', 'seraphinite-accelerator' ), array( 'class' => 'chkradSubBlock' ) ) );
					}
					echo( Ui::TagClose( 'p' ) );

					echo( Ui::TagOpen( 'p' ) );
					{
						echo( Ui::RadioBox( Wp::safe_html_x( 'IndepRad', 'admin.SetupWizard_Mobiles', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, 'indep', $vDef == 'indep' ) );
						echo( Ui::Tag( 'span', Wp::safe_html_x( 'IndepInfo', 'admin.SetupWizard_Mobiles', 'seraphinite-accelerator' ), array( 'class' => 'chkradSubBlock' ) ) );
					}
					echo( Ui::TagClose( 'p' ) );

					if( $vDefDontChange )
					{
						echo( Ui::TagOpen( 'p' ) );
						{
							echo( Ui::RadioBox( Wp::safe_html_x( 'DontModifyRad', 'admin.SetupWizard', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, '', $vDef == '' ) );
							echo( Ui::Tag( 'span', Wp::safe_html_x( 'DontModifyInfo', 'admin.SetupWizard', 'seraphinite-accelerator' ), array( 'class' => 'chkradSubBlock' ) ) );
						}
						echo( Ui::TagClose( 'p' ) );
					}
				}
				echo( Ui::TagClose( 'div' ) );

				echo( Ui::TagOpen( 'div' ) );
				{
					$fldId = 'runOpt';

					echo( Ui::Tag( 'p', Wp::safe_html_x( 'FinalInfo', 'admin.SetupWizard', 'seraphinite-accelerator' ) ) );

					echo( Ui::TagOpen( 'p' ) );
					{
						echo( Ui::CheckBox( Wp::safe_html_x( 'RunOptChk', 'admin.SetupWizard', 'seraphinite-accelerator' ), 'seraph_accel/' . $fldId, true, true ) );
					}
					echo( Ui::TagClose( 'p' ) );
				}
				echo( Ui::TagClose( 'div' ) );
			}
			echo( Ui::TagClose( 'div' ) );
		},
		get_defined_vars()
	);

	{
		$htmlContent = Plugin::GetLockedFeatureLicenseContent( Plugin::DisplayContent_SmallBlock );
		if( !empty( $htmlContent ) )
			Ui::PostBoxes_MetaboxAdd( 'switchToFull', Plugin::GetSwitchToFullTitle(), false, function( $callbacks_args, $box ) { echo( $box[ 'args' ][ 'c' ] ); }, array( 'c' => $htmlContent ), 'side' );

		Ui::PostBoxes_MetaboxAdd( 'about', Plugin::GetAboutPluginTitle(), false, function( $callbacks_args, $box ) { echo( Plugin::GetAboutPluginContent() ); }, null, 'side' );
		Ui::PostBoxes_MetaboxAdd( 'aboutVendor', Plugin::GetAboutVendorTitle(), false, function( $callbacks_args, $box ) { echo( Plugin::GetAboutVendorContent() ); }, null, 'side' );
	}

	Ui::PostBoxes( Plugin::GetSubjectTitle( Wp::GetLocString( array( 'TitleWiz', 'admin.Common_Settings' ), null, 'seraphinite-accelerator' ) ), array( 'body' => array( 'nosort' => true ), 'side' => array( 'nosort' => true ) ),
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
						echo( Plugin::Sett_WizardBtns( 'seraph_accel_saveWizSettings' ) );
					}
				);

				echo( Ui::TagClose( 'form' ) );
			}
		),
		get_defined_vars()
	);
}

function _OnFinishSettingsWizard( $args )
{
	$sett = Plugin::SettGet();
	$settDef = OnOptGetDef_Sett();

	{
		$fldId = array( 'contPr', 'enable' );
		Gen::SetArrField( $sett, $fldId, $args[ 'seraph_accel/fullOrSimple' ] == 'full' );
	}

	{
		$v = null;
		switch( $args[ 'seraph_accel/mobileDep' ] )
		{
			case 'dep':			$v = true; break;
			case 'indep':		$v = false; break;
		}

		if( $v !== null )
		{
			$fldId = array( 'cache', 'viewsDeviceGrps' );

			$settItemDef = array();
			foreach( Gen::GetArrField( $settDef, $fldId, array() ) as $i => $item )
			{
				if( (isset($item[ 'id' ])?$item[ 'id' ]:null) == 'mobile' )
				{
					$settItemDef = $item;
					break;
				}
			}

			$settItems = Gen::GetArrField( $sett, $fldId, array() );
			$settItemIdx = count( $settItems );
			foreach( $settItems as $i => $item )
			{
				if( (isset($item[ 'id' ])?$item[ 'id' ]:null) == 'mobile' )
					$settItemIdx = $i;
				Gen::SetArrField( $sett, array_merge( $fldId, array( $i, 'enable' ) ), false );
			}

			Gen::SetArrField( $sett, array_merge( $fldId, array( $settItemIdx ) ), $settItemDef );
			Gen::SetArrField( $sett, array_merge( $fldId, array( $settItemIdx, 'enable' ) ), $v );
		}
	}

	{
		$flds = array(
			array( 'cache', 'lazyInvForcedTmp' ),
			array( 'cache', 'updPostOp' ),
		);

		$vals = array();
		switch( $args[ 'seraph_accel/optPrior' ] )
		{
			case 'score':
				$vals = array(
					false,
					0,
				);
				break;

			case 'fresh':
				$vals = array(
					true,
					2,
				);
				break;
		}

		foreach( $vals as $i => $v )
			Gen::SetArrField( $sett, $flds[ $i ], $v );
	}

	if( Wp::IsMultisiteMain() )
	{
		$fldId1 = array( 'cache', 'maxProc' );
		$fldId2 = array( 'cache', 'procInterval' );
		$fldId3 = array( 'cache', 'procIntervalShort' );
		$fldId4 = array( 'cache', 'timeout' );
		$fldId5 = array( 'cache', 'procWorkInt' );
		$fldId6 = array( 'cache', 'procPauseInt' );

		$vals = null;
		switch( $args[ 'seraph_accel/hostPerf' ] )
		{
			case 'high':
				$vals = array(
					1 => 4,
					2 => 0,
					3 => 0,
					4 => 24 * 60,
					5 => 0.0,
					6 => 0.0,
				); break;

			case 'med':
				$vals = array(
					1 => 2,
					2 => 0,
					3 => 0,
					4 => 24 * 60,
					5 => 0.0,
					6 => 0.0,
				); break;

			case 'low':
				$vals = array(
					1 => Gen::GetArrField( $settDef, $fldId1, 0 ),
					2 => Gen::GetArrField( $settDef, $fldId2, 0 ),
					3 => Gen::GetArrField( $settDef, $fldId3, 0 ),
					4 => Gen::GetArrField( $settDef, $fldId4, 0 ),
					5 => Gen::GetArrField( $settDef, $fldId5, 0 ),
					6 => Gen::GetArrField( $settDef, $fldId6, 0 ),
				); break;
		}

		if( $vals )
		{
			Gen::SetArrField( $sett, $fldId1, $vals[ 1 ] );
			Gen::SetArrField( $sett, $fldId2, $vals[ 2 ] );
			Gen::SetArrField( $sett, $fldId3, $vals[ 3 ] );
			Gen::SetArrField( $sett, $fldId4, $vals[ 4 ] );
			Gen::SetArrField( $sett, $fldId5, $vals[ 5 ] );
			Gen::SetArrField( $sett, $fldId6, $vals[ 6 ] );
		}
	}

	$hr = ApplySettings( $sett, !Gen::GetArrField( Plugin::StateGet(), array( 'settWiz' ) ) );
	if( Gen::HrFail( $hr ) )
		return( $hr );

	Plugin::StateUpdateFlds( array( 'settWiz' => null ) );

	if( isset( $args[ 'seraph_accel/runOpt' ] ) )
	{
		if( Gen::GetArrField( $sett, array( 'log' ), false ) && Gen::GetArrField( Plugin::SettGet(), array( 'logScope', 'upd' ), false ) )
		{
			$txt = '';
			$txt .= 'Revalidation after setup wizard';

			LogWrite( $txt, Ui::MsgInfo, 'Cache update' );
		}

		RunOpt();
	}

	return( $hr );
}

function _SelfDiagContent()
{
	$res = '';
	$res .= Ui::TagOpen( 'div', array( 'class' => 'blck' ) );
	{
		$res .= Ui::Tag( 'div', null, array( 'class' => 'seraph_accel_textarea ctlSpaceVAfter log', 'style' => array( 'overflow' => 'scroll', 'min-height' => '15em', 'max-height' => '50em', 'resize' => 'vertical' ) ) );
		$res .= Ui::Tag( 'div',
			Ui::Button( esc_html_x( 'Start', 'admin.SelfDiag', 'seraphinite-accelerator' ), true, null, null, 'button', array( 'class' => array( 'ctlSpaceAfter', 'ctlVaMiddle' ), 'style' => array( 'min-width' => '7em' ), 'onclick' => 'seraph_accel.SelfDiag._int.OnStart(this);return false;', 'data-oninit' => 'seraph_accel.SelfDiag._int.OnStart(this,true)' ) ) .
			Ui::Button( Wp::GetLocString( 'Cancel' ), false, null, null, 'button', array( 'class' => array( 'ctlSpaceAfter', 'ctlVaMiddle', 'cancel' ), 'style' => array( 'min-width' => '7em' ), 'disabled' => true, 'onclick' => 'seraph_accel.SelfDiag._int.OnCancel(this);return false;' ) ) .
			Ui::Spinner( false, array( 'class' => 'ctlSpaceAfter ctlVaMiddle', 'style' => array( 'display' => 'none' ) ) )
		);
	}
	$res .= Ui::TagClose( 'div' );
	return( $res );
}

add_action( 'seraph_accel_selfDiag_cronTestTask',
	function()
	{
		PluginFileValues::Del( 'selfDiag_test' );

	}
);

function _SelfDiag_GetResponseResString( $url, $requestRes, $body = false )
{
	if( is_wp_error( $requestRes ) )
	{
		if( $requestRes -> get_error_data( 'url' ) )
			$url = $requestRes -> get_error_data( 'url' );
		return( sprintf( Wp::safe_html_x( 'RequestWpErr_%1$s%2$s', 'admin.SelfDiag', 'seraphinite-accelerator' ), $url, MsgUnpackLocIds( $requestRes -> get_error_message() ) ) );
	}

	if( (isset($requestRes[ 'url' ])?$requestRes[ 'url' ]:null) )
		$url = $requestRes[ 'url' ];

	$res = 'HTTP ' . ( isset( $requestRes[ 'method' ] ) ? ( $requestRes[ 'method' ] . ' ' ) : '' ) . wp_remote_retrieve_response_code( $requestRes ) . ' ' . $url;
	$res .= '<br>' . GetHeadersResString( array( 'Server Address' => gethostbyname( Gen::GetArrField( Net::UrlParse( $url ), array( 'host' ), '' ) ) ) );

	if( isset( $requestRes[ 'headers_sent' ] ) )
		$res .= '<br>' . Ui::Tag( 'u', Wp::safe_html_x( 'RequestHeaders', 'admin.SelfDiag', 'seraphinite-accelerator' ) ) . '<br>' . GetHeadersResString( $requestRes[ 'headers_sent' ] );
	$res .= '<br>' . Ui::Tag( 'u', Wp::safe_html_x( 'ResponseHeaders', 'admin.SelfDiag', 'seraphinite-accelerator' ) ) . '<br>' . GetHeadersResString( Net::GetHeadersFromWpRemoteGet( $requestRes ) );

	if( $body )
	{
		$bodyC = wp_remote_retrieve_body( $requestRes );
		if( is_int( $body ) && strlen( $bodyC ) > $body )
			$bodyC = substr( $bodyC, 0, $body ) . '...';
		$res .= '<br>' . Ui::Tag( 'u', Wp::safe_html_x( 'Body', 'admin.SelfDiag', 'seraphinite-accelerator' ) ) . '<br>' . htmlspecialchars( $bodyC );
	}
	return( Ui::Tag( 'span', $res, array( 'style' => array( 'white-space' => 'nowrap' ) ) ) );
}

function OnAsyncTask_SelfDiag_AsyncRequest( $args )
{
	PluginFileValues::Del( 'selfDiag_test' );
}

function OnAdminApi_SelfDiag_AsyncRequest( $args )
{
	$res = new AnyObj();
	$res -> hr = Gen::S_OK;

	$res -> hr = PluginFileValues::Set( 'selfDiag_test', true );
	if( $res -> hr !== Gen::S_OK )
	{
		$res -> descr = sprintf( Wp::safe_html_x( 'FileWriteFail_%1$s%2$08X', 'admin.SelfDiag', 'seraphinite-accelerator' ), PluginFileValues::GetDir(), $res -> hr );
		return( $res );
	}

	$timeout = 25;
	$timeoutNet = 25;

	$res -> hr = Plugin::AsyncFastTaskPost( 'SelfDiag_AsyncRequest', null, $timeout, false, true );
	if( $res -> hr !== Gen::S_OK )
	{
		$res -> descr = sprintf( Wp::safe_html_x( 'FileWriteFail_%1$s%2$08X', 'admin.SelfDiag', 'seraphinite-accelerator' ), Plugin::AsyncTaskGetFileName(), $res -> hr );
	}
	else
	{
		$url = Plugin::AsyncTaskPushGetUrl( 'M' );
		$requestRes = Plugin::AsyncTaskPushEx( $url, 0 );

		$res -> hr = Net::GetHrFromWpRemoteGet( $requestRes );
		if( $res -> hr !== Gen::S_OK )
		{
			$res -> descr = _SelfDiag_GetResponseResString( $url, $requestRes );
		}
		else
		{
			$step = 1;
			while( $timeout )
			{
				if( !PluginFileValues::Get( 'selfDiag_test', 1 ) )
					break;

				$timeout -= $step;
				sleep( $step );
			}

			if( !$timeout )
			{
				$res -> hr = Gen::S_FAIL;
				$res -> descr = Wp::safe_html_x( 'TaskExecTimeoutExpired', 'admin.SelfDiag', 'seraphinite-accelerator' );
			}
		}

		if( $res -> hr !== Gen::S_OK && Gen::GetArrField( Plugin::SettGetGlobal(), array( 'asyncUseCron' ), true ) )
			$res -> descr = sprintf( Wp::safe_html_x( 'TaskExecCronProbablyBlocked_%1$s%2$s%3$s', 'admin.SelfDiag', 'seraphinite-accelerator' ), esc_html_x( 'Title', 'admin.Settings_Advanced', 'seraphinite-accelerator' ), esc_html_x( 'Lbl', 'admin.Settings_Advanced_Cron', 'seraphinite-accelerator' ), esc_html_x( 'AsyncUseCronChk', 'admin.Settings_Advanced_Cron', 'seraphinite-accelerator' ) ) . '<br>' . $res -> descr;
	}

	PluginFileValues::Del( 'selfDiag_test' );

	if( $res -> hr == Gen::S_OK )
		return( $res );

	$url = Plugin::AsyncTaskPushGetUrl( 'M_TEST' );
	$testData = 'M_TEST_' . Gen::GetArrField( Net::UrlParse( $url, Net::URLPARSE_F_QUERY ), array( 'query', 'rt' ), '' );

	$tm = time();
	$requestRes = Plugin::AsyncTaskPushEx( $url, $timeoutNet );
	$tm = time() - $tm;

	$hr = Net::GetHrFromWpRemoteGet( $requestRes );
	$descr = '';
	if( $hr !== Gen::S_OK )
	{
		$descr = _SelfDiag_GetResponseResString( $url, $requestRes );
	}
	else if( $tm > Plugin::ASYNCTASK_PUSH_TIMEOUT )
	{
		$hr = Gen::S_FALSE;
		$descr = sprintf( Wp::safe_html_x( 'RequestTimeTooLong_%1$s%2$s', 'admin.SelfDiag', 'seraphinite-accelerator' ), ( string )Plugin::ASYNCTASK_PUSH_TIMEOUT, ( string )$tm );
	}
	else if( !Gen::StrStartsWith( wp_remote_retrieve_body( $requestRes ), $testData ) )
	{
		$hr = Gen::S_FALSE;
		$descr = sprintf( Wp::safe_html_x( 'BdyMismatchCanSkip_%1$s', 'admin.SelfDiag', 'seraphinite-accelerator' ), $testData ) . '<br>' . _SelfDiag_GetResponseResString( $url, $requestRes, 1000 );
	}

	if( $res -> hr === Gen::S_OK )
		$res -> hr = $hr;
	if( $descr )
	{
		if( $res -> descr )
			$res -> descr .= '<br>';
		$res -> descr .= $descr;
	}

	return( $res );
}

function OnAdminApi_SelfDiag_SetMaxExecTime( $args )
{
	$res = new AnyObj();
	$res -> hr = Gen::S_OK;
	$res -> descr = '';

	$procTmLim = Gen::GetArrField( Plugin::SettGet(), array( 'cache', 'procTmLim' ), 570 );

	if( !Gen::SetTimeLimit( $procTmLim + 30 ) )
	{
		$tmCur = @ini_get( 'max_execution_time' );

		if( $tmCur < $procTmLim + 30 )
		{
			$res -> hr = Gen::HrSuccFromFail( Gen::E_FAIL );
			$res -> descr = sprintf( Wp::safe_html_x( 'WarnExecTime_%1$s%2$s', 'admin.SelfDiag', 'seraphinite-accelerator' ), $tmCur, $procTmLim + 30 );
		}
	}

	$memLim = Gen::GetArrField( Plugin::SettGet(), array( 'cache', 'procMemLim' ), 0 );
	$memLimCur = wp_convert_hr_to_bytes( @ini_get( 'memory_limit' ) ) / 1024 / 1024;
	if( $memLim > $memLimCur )
	{
		if( $res -> hr == Gen::S_OK )
			$res -> hr = Gen::S_FALSE;
		if( $res -> descr )
			$res -> descr .= '<br>';
		$res -> descr .= sprintf( Wp::safe_html_x( 'WarnMemLim_%1$s%2$s%3$s', 'admin.SelfDiag', 'seraphinite-accelerator' ), size_format( $memLimCur * 1024 * 1024, 1 ), size_format( $memLim * 1024 * 1024, 1 ), sprintf( Plugin::GetPluginString( 'NameToDetails_%1$s%2$s' ), esc_html_x( 'Title', 'admin.Settings_Cache_Revalidate', 'seraphinite-accelerator' ), esc_html_x( 'Lbl', 'admin.Settings_Cache_Revalidate_Proc', 'seraphinite-accelerator' ) ) );
	}

	return( $res );
}

function OnAdminApi_SelfDiag_PageOptimize( $args )
{
	$res = new AnyObj();
	$res -> hr = Gen::S_OK;

	if( !Gen::GetCurRequestTime() )
	{
		$res -> hr = Gen::E_FAIL;
		$res -> descr = Wp::safe_html_x( 'NoCurRequestTime', 'admin.SelfDiag', 'seraphinite-accelerator' );
		return( $res );
	}

	$selfTestSuffix = Gen::MicroTimeStamp();
	$tmBegin = Gen::MicroTimeStamp();
	$url = add_query_arg( array( 'seraph_accel_prep' => @rawurlencode( @base64_encode( @json_encode( array( 'nonce' => hash_hmac( 'md5', $tmBegin, GetSalt() ), 'selfTest' => $selfTestSuffix, '_tm' => $tmBegin ) ) ) ) ), Wp::GetSiteRootUrl() );

	$viewsHeaders = CacheOpGetViewsHeaders( Gen::GetArrField( Plugin::SettGet(), array( 'cache' ), array() ) );

	$asyncMode = OnAsyncTasksPushGetMode();
	if( $asyncMode == 're' || $asyncMode == 're_r' )
		$requestRes = PluginRe::MakeRequest( 'GET', $url, array(), 45 );
	else

		$requestRes = Wp::RemoteGet( $url, array( 'local' => $asyncMode == 'loc', 'timeout' => 45, 'sslverify' => false, 'headers' => $viewsHeaders[ 'cmn' ] ) );

	$res -> hr = Net::GetHrFromWpRemoteGet( $requestRes );
	if( $res -> hr !== Gen::S_OK )
	{
		$res -> descr = _SelfDiag_GetResponseResString( $url, $requestRes );
	}
	else if( strpos( wp_remote_retrieve_body( $requestRes ), 'selfTest-' . $selfTestSuffix ) === false )
	{
		$res -> hr = Gen::E_DATACORRUPTED;
		$res -> descr = sprintf( Wp::safe_html_x( 'BdyMismatch_%1$s', 'admin.SelfDiag', 'seraphinite-accelerator' ), 'selfTest-' . $selfTestSuffix ) . '<br>' . _SelfDiag_GetResponseResString( $url, $requestRes, 500 );
	}

	return( $res );
}

function OnAdminApi_SelfDiag_VendorSrv( $args )
{
	$res = new AnyObj();
	$res -> hr = Gen::S_OK;

	$requestRes = PluginRmtCfg::UpdateTestRequest();

	$res -> hr = Net::GetHrFromWpRemoteGet( $requestRes );
	if( $res -> hr !== Gen::S_OK )
	{
		$res -> descr = _SelfDiag_GetResponseResString( $url, $requestRes );
	}

	return( $res );
}

function OnAdminApi_SelfDiag_ExtCache( $args )
{
	$sett = Plugin::SettGet();
	$isExtCacheAllowed = Gen::GetArrField( $sett, array( 'cache', 'srv' ), false ) || Gen::GetArrField( $sett, array( 'cache', 'srvClr' ), false );

	$res = new AnyObj();
	$res -> hr = Gen::S_OK;

	$url = Wp::GetSiteRootUrl();

	$requestRes = Wp::RemoteGet( $url, array( 'timeout' => 45, 'sslverify' => false ) );

	$res -> hr = Net::GetHrFromWpRemoteGet( $requestRes );
	if( $res -> hr !== Gen::S_OK )
	{
		$res -> descr = _SelfDiag_GetResponseResString( $url, $requestRes );
		return( $res );
	}

	$tmStamp = Gen::MicroTimeStamp();
	$requestRes = Wp::RemoteGet( $url, array( 'timeout' => 45, 'sslverify' => false, 'headers' => array( 'X-Seraph-Accel-test' => $tmStamp ) ) );

	$res -> hr = Net::GetHrFromWpRemoteGet( $requestRes );
	if( $res -> hr !== Gen::S_OK )
	{
		$res -> descr = _SelfDiag_GetResponseResString( $url, $requestRes );
	}
	else if( wp_remote_retrieve_header( $requestRes, 'x-seraph-accel-test' ) !== $tmStamp )
	{
		if( !$isExtCacheAllowed )
		{
			$res -> hr = Gen::HrSuccFromFail( Gen::E_DATACORRUPTED );

			$types = array();
			if( strpos( wp_remote_retrieve_header( $requestRes, 'server' ), 'nginx' ) === 0 && ( wp_remote_retrieve_header( $requestRes, 'x-cache' ) || wp_remote_retrieve_header( $requestRes, 'x-cache-status' ) ) )
				$types[] = 'Nginx';
			if( wp_remote_retrieve_header( $requestRes, 'x-litespeed-cache' ) )
				$types[] = 'LiteSpeed';
			if( wp_remote_retrieve_header( $requestRes, 'x-fastcgi-cache' ) )
				$types[] = 'FastCGI';
			if( wp_remote_retrieve_header( $requestRes, 'cf-cache-status' ) && wp_remote_retrieve_header( $requestRes, 'cf-ray' ) )
				$types[] = 'CloudFlare';
			if( wp_remote_retrieve_header( $requestRes, 'x-ezoic-cdn' ) )
				$types[] = 'Ezoic CDN';

			$linkHlp = Ui::Link( array( '', '' ), Plugin::RmtCfgFld_GetLoc( PluginRmtCfg::Get(), 'Help.Settings_Cache_Srv' ), true );
			if( $types )
				$res -> descr = vsprintf( Wp::safe_html_x( 'HdrMismatchExtCacheType_%1$s%2$s%3$s', 'admin.SelfDiag', 'seraphinite-accelerator' ), array_merge( $linkHlp, array( implode( ', ', array_map( function( $v ) { return( Ui::Tag( 'strong', $v ) ); }, $types ) ) ) ) );
			else
				$res -> descr = vsprintf( Wp::safe_html_x( 'HdrMismatchExtCache_%1$s%2$s', 'admin.SelfDiag', 'seraphinite-accelerator' ), $linkHlp );

			$res -> descr .= '<br>' . sprintf( Wp::safe_html_x( 'HdrMismatch_%1$s', 'admin.SelfDiag', 'seraphinite-accelerator' ), 'X-Seraph-Accel-test: ' . $tmStamp ) . '<br>' . _SelfDiag_GetResponseResString( $url, $requestRes );
		}
	}

	return( $res );
}

function OnAdminApi_SelfDiag_3rdPartySettCompat( $args )
{
	$res = new AnyObj();
	$res -> hr = Gen::S_OK;
	$res -> descr = '';
	$res -> cb = function( $res, $sev, $text )
	{
		if( $sev === Gen::SevErr )
			$res -> hr = Gen::E_FAIL;
		else if( $sev === Gen::SevInfo )
		{
			if( $res -> hr == Gen::S_OK )
				$res -> hr = Gen::S_FALSE;
		}
		else if( !Gen::HrFail( $res -> hr ) )
			$res -> hr = Gen::S_FAIL;

		if( $res -> descr )
			$res -> descr .= '<br>';
		$res -> descr .= $text;
	};

	SelfDiag_DetectStateAnd3rdPartySettConflicts( array( $res, 'cb' ), true );

	return( $res );
}

function OnAdminApi_SelfDiag_DataBase( $args )
{
	$res = new AnyObj();
	$res -> hr = Gen::S_OK;
	$res -> descr = '';

	CacheInitQueueTable( true );

	global $wpdb;

	if( $wpdb -> last_error )
	{
		$res -> hr = Gen::E_FAIL;
		$res -> descr = vsprintf( Wp::safe_html_x( 'DbTblCreateUpdateFailed_%1$s', 'admin.SelfDiag', 'seraphinite-accelerator' ), array( $wpdb -> last_error ) ) . '<br>' . Ui::Tag( 'em', htmlspecialchars( $wpdb -> last_query ), array( 'style' => array( 'white-space' => 'nowrap' ) ) );
	}

	return( $res );
}

function GetCodeViewHtmlBlock( $cont )
{
	return( Ui::Tag( 'div', str_replace( array( "\r", "\n", "\t", " " ), array( '', '<br>', '&#9;', '&nbsp;' ), htmlspecialchars( $cont ) ), array( 'class' => array( 'seraph_accel_textarea' ), 'style' => array( 'overflow' => 'scroll', 'height' => '5em', 'min-height' => '3em', 'max-height' => '20em', 'resize' => 'vertical' ) ) ) );
}

function SelfDiag_DetectStateAnd3rdPartySettConflicts( $cb, $ext = false )
{
	$sett = Plugin::SettGet();
	$rmtCfg = PluginRmtCfg::Get();

	$contRemindRefreshCache = vsprintf( Wp::safe_html_x( 'RemindRefreshCache_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), Ui::Link( array( '', '' ), menu_page_url( 'seraph_accel_manage', false ) . '#operate' ) );

	$isCacheEnabled = Gen::GetArrField( $sett, 'cache/enable', false, '/' );
	$isContProcEnabled = Gen::GetArrField( $sett, 'contPr/enable', false, '/' );
	$isScriptsDelayLoadEnabled = $isContProcEnabled && Gen::GetArrField( $sett, 'contPr/js/optLoad', false, '/' ) && Gen::GetArrField( $sett, 'contPr/js/nonCrit/timeout/enable', false, '/' ) && Gen::GetArrField( $sett, 'contPr/js/nonCrit/timeout/v', 0, '/' );
	$isExtCacheAllowed = Gen::GetArrField( $sett, array( 'cache', 'srv' ), false ) || Gen::GetArrField( $sett, array( 'cache', 'srvClr' ), false );

	if( $isCacheEnabled )
	{
		{
			$dir = GetCacheDir();
			if( ( !@is_dir( $dir ) && Gen::HrFail( Gen::MakeDir( $dir, true ) ) ) || !@is_writable( $dir ) )
				call_user_func_array( $cb, array( Ui::MsgErr, sprintf( Wp::safe_html_x( 'CacheDirNotWrittable_%1$s', 'admin.Notice', 'seraphinite-accelerator' ), $dir ) ) );
		}

		if( IsWpCacheActive() )
		{
			$verifyEnvDropin = new AnyObj();
			if( !isset( $sett[ PluginOptions::VERPREV ] ) && ( (isset($_SERVER[ 'REQUEST_METHOD' ])?$_SERVER[ 'REQUEST_METHOD' ]:null) == 'GET' ) && !CacheVerifyEnvDropin( $sett, $verifyEnvDropin ) )
			{
				if( !@file_exists( WP_CONTENT_DIR . '/advanced-cache.php' ) && !@is_writable( WP_CONTENT_DIR ) )
					call_user_func_array( $cb, array( Ui::MsgErr, sprintf( Wp::safe_html_x( 'ContentDirNotWrittable_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), Gen::GetFileName( WP_CONTENT_DIR ), 'advanced-cache.php' ) ) );
				else if( !@is_writable( WP_CONTENT_DIR . '/advanced-cache.php' ) )
					call_user_func_array( $cb, array( Ui::MsgErr, sprintf( Wp::safe_html_x( 'ContentDropinNotWrittable_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), Gen::GetFileName( WP_CONTENT_DIR ), 'advanced-cache.php' ) ) );
				else
					call_user_func_array( $cb, array( Ui::MsgErr, sprintf( Wp::safe_html_x( 'ContentDropinNotMatch_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), Gen::GetFileName( WP_CONTENT_DIR ), 'advanced-cache.php' ) . ( $ext ? '' : sprintf( Wp::safe_html_x( 'ContentDropinNotMatchEx_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), GetCodeViewHtmlBlock( $verifyEnvDropin -> needed ), GetCodeViewHtmlBlock( $verifyEnvDropin -> actual ) ) ) ) );
			}
			else if( !Gen::DoesFuncExist( 'seraph_accel_siteSettInlineDetach' ) )
				call_user_func_array( $cb, array( Ui::MsgErr, sprintf( Wp::safe_html_x( 'ContentDropinNotLoaded_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), Gen::GetFileName( WP_CONTENT_DIR ), 'advanced-cache.php' ) ) );
		}
		else
		{
			$cfgFile = Wp::GetConfigFilePath();
			if( !@is_writable( $cfgFile ) )
				call_user_func_array( $cb, array( Ui::MsgErr, sprintf( Wp::safe_html_x( 'ConfigFileNotWrittable_%1$s', 'admin.Notice', 'seraphinite-accelerator' ), $cfgFile ) ) );
			else
				call_user_func_array( $cb, array( Ui::MsgErr, Wp::safe_html_x( 'WpCacheNotActive', 'admin.Notice', 'seraphinite-accelerator' ) ) );
		}

		if( ( in_array( 'br', Gen::GetArrField( $sett, 'cache/encs', array(), '/' ) ) || in_array( 'brotli', Gen::GetArrField( $sett, 'cache/dataCompr', array(), '/' ) ) ) && @version_compare( @phpversion( 'brotli' ), '0.1.0' ) === -1 )
			call_user_func_array( $cb, array( Ui::MsgErr, sprintf( Wp::safe_html_x( 'PhpBrotliNotActive_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), 'BROTLI', '0.1.0' ) ) );

		$aLocksInfo = array( array( 'pl', GetCacheDir() ), array( 'dl', GetCacheDir() ) );
		foreach( GetSiteIds() as $siteId )
			$aLocksInfo[] = array( 'l', GetCacheDir() . '/q/' . $siteId );
		foreach( $aLocksInfo as $d )
		{
			$lock = new Lock( $d[ 0 ], $d[ 1 ] );
			if( $lock -> Acquire() )
			{
				$lock -> Release();
				unset( $lock );
				continue;
			}

			call_user_func_array( $cb, array( Ui::MsgErr, sprintf( Wp::safe_html_x( 'TmpFileNotWrittable_%1$s', 'admin.Notice', 'seraphinite-accelerator' ), $lock -> GetFileName() ) ) );
			unset( $lock );
			break;
		}

		if( Gen::GetArrField( $sett, 'asyncMode', '', '/' ) === 're_r' )
		{
			$reFile = PluginRe::GetRootFileName();
			$verifyEnvDropin = new AnyObj();
			if( !CacheVerifyEnvReRoot( $sett, $verifyEnvDropin ) )
			{
				if( !@file_exists( $reFile ) && !@is_writable( dirname( $reFile ) ) )
					call_user_func_array( $cb, array( Ui::MsgErr, sprintf( Wp::safe_html_x( 'ContentDirNotWrittable_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), dirname( $reFile ), Gen::GetFileName( $reFile ) ) ) );
				else if( !@is_writable( $reFile ) )
					call_user_func_array( $cb, array( Ui::MsgErr, sprintf( Wp::safe_html_x( 'ContentDropinNotWrittable_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), dirname( $reFile ), Gen::GetFileName( $reFile ) ) ) );
				else
					call_user_func_array( $cb, array( Ui::MsgErr, sprintf( Wp::safe_html_x( 'ContentDropinNotMatch_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), dirname( $reFile ), Gen::GetFileName( $reFile ) ) . ( $ext ? '' : sprintf( Wp::safe_html_x( 'ContentDropinNotMatchEx_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), GetCodeViewHtmlBlock( $verifyEnvDropin -> needed ), GetCodeViewHtmlBlock( $verifyEnvDropin -> actual ) ) ) ) );
			}
		}

	}

	if( !isset( $sett[ PluginOptions::VERPREV ] ) && ( (isset($_SERVER[ 'REQUEST_METHOD' ])?$_SERVER[ 'REQUEST_METHOD' ]:null) == 'GET' ) && !CacheVerifyEnvNginxConf( $sett ) )
	{
		call_user_func_array( $cb, array( Ui::MsgErr, sprintf( Wp::safe_html_x( 'ContentNginxConfNotMatch_%1$s', 'admin.Notice', 'seraphinite-accelerator' ), Wp::GetHomePath() . 'seraph-accel-img-compr-redir.conf' ) ) );
	}

	if( !Gen::DoesFuncExist( 'fsockopen' ) && !Gen::DoesFuncExist( 'curl_exec' ) )
		call_user_func_array( $cb, array( Ui::MsgWarn, sprintf( Wp::safe_html_x( 'PhpExtNotActive_%1$s', 'admin.Notice', 'seraphinite-accelerator' ), 'CURL' ) ) );

	if( $isContProcEnabled )
	{
		if( ( Gen::GetArrField( $sett, 'contPr/normalize', 0, '/' ) & 524288 ) && !Gen::DoesFuncExist( 'tidy_parse_string' ) )
			call_user_func_array( $cb, array( Ui::MsgWarn, sprintf( Wp::safe_html_x( 'PhpTidyNotActive_%1$s%2$s%3$s', 'admin.Notice', 'seraphinite-accelerator' ), 'TIDY', esc_html_x( 'TidyChk', 'admin.Settings_Html_Fix', 'seraphinite-accelerator' ), Ui::Link( esc_html_x( 'Title', 'admin.Settings_Html', 'seraphinite-accelerator' ), menu_page_url( 'seraph_accel_settings', false ) . '#html' ) ) ) );

		if( !Gen::DoesFuncExist( 'iconv' ) )
			call_user_func_array( $cb, array( Ui::MsgWarn, sprintf( Wp::safe_html_x( 'PhpExtNotActive_%1$s', 'admin.Notice', 'seraphinite-accelerator' ), 'ICONV' ) ) );

		if( !Gen::DoesFuncExist( 'mb_detect_encoding' ) || !Gen::DoesFuncExist( 'mb_convert_encoding' ) )
			call_user_func_array( $cb, array( Ui::MsgWarn, sprintf( Wp::safe_html_x( 'PhpExtNotActive_%1$s', 'admin.Notice', 'seraphinite-accelerator' ), 'MBSTRING' ) ) );

		if( !Gen::DoesFuncExist( '\\DOMElement::getAttribute' ) )
			call_user_func_array( $cb, array( Ui::MsgErr, sprintf( Wp::safe_html_x( 'PhpExtNotActive_%1$s', 'admin.Notice', 'seraphinite-accelerator' ), 'LIBXML' ) ) );

		if( !Gen::DoesFuncExist( 'imagecreatefromstring' ) )
		{
			foreach( array( 'webp','avif' ) as $comprType )
				if( Gen::GetArrField( $sett, array( 'contPr', 'img', $comprType, 'enable' ), false, '/' ) )
					call_user_func_array( $cb, array( Ui::MsgWarn, sprintf( Wp::safe_html_x( 'PhpGdNotActive_%1$s%2$s%3$s', 'admin.Notice', 'seraphinite-accelerator' ), 'GD',
						sprintf( Plugin::GetPluginString( 'SubjectTitle_%1$s%2$s' ), esc_html_x( 'Lbl', 'admin.Settings_Images_Compr', 'seraphinite-accelerator' ), Gen::GetArrField( array( 'avif' => sprintf( esc_html_x( 'AvifChk_%1$s%2$s', 'admin.Settings_Images_Compr', 'seraphinite-accelerator' ), '', '' ), 'webp' => sprintf( esc_html_x( 'WebpChk_%1$s', 'admin.Settings_Images_Compr', 'seraphinite-accelerator' ), '' ) ), $comprType, '' ) ),
						Ui::Link( esc_html_x( 'Title', 'admin.Settings_Images', 'seraphinite-accelerator' ), menu_page_url( 'seraph_accel_settings', false ) . '#images' ) ) ) );

			if( Gen::GetArrField( $sett, 'contPr/img/szAdaptImg', false, '/' ) || Gen::GetArrField( $sett, 'contPr/img/szAdaptBg', false, '/' ) )
				call_user_func_array( $cb, array( Ui::MsgWarn, sprintf( Wp::safe_html_x( 'PhpGdNotActive_%1$s%2$s%3$s', 'admin.Notice', 'seraphinite-accelerator' ), 'GD',
					sprintf( Plugin::GetPluginString( 'SubjectTitle_%1$s%2$s' ), esc_html_x( 'Title', 'admin.Settings_Images', 'seraphinite-accelerator' ), esc_html_x( 'Lbl', 'admin.Settings_Images_Adapt', 'seraphinite-accelerator' ) ),
					Ui::Link( esc_html_x( 'Title', 'admin.Settings_Images', 'seraphinite-accelerator' ), menu_page_url( 'seraph_accel_settings', false ) . '#images' ) ) ) );
		}
	}

	$themeCh = wp_get_theme();
	for( $theme = $themeCh; $theme && $theme -> parent();  )
		$theme = $theme -> parent();

	if( $theme )
	{
		switch( $theme -> template )
		{
			case 'woostroid2':

				if( $isScriptsDelayLoadEnabled && get_theme_mod( 'page_preloader' ) )
					call_user_func_array( $cb, array( Gen::SevWarn, sprintf( Wp::safe_html_x( '3rdSett_DisplayConflict_%1$s%2$s%3$s%4$s', 'admin.Notice', 'seraphinite-accelerator' ), $contRemindRefreshCache, $theme -> name, Wp::GetLocString( 'Show page preloader', null, 'woostroid2' ), esc_html_x( 'OffOptionValue', 'admin.Notice', 'seraphinite-accelerator' ) ) ) );

				break;
			case 'woodmart':
				$themeOpts = get_option( 'xts-woodmart-options' );

				if( $isScriptsDelayLoadEnabled && Gen::GetArrField( $themeOpts, 'lazy_loading', null, '/' ) )
					call_user_func_array( $cb, array( Gen::SevWarn, sprintf( Wp::safe_html_x( '3rdSett_DisplayConflict_%1$s%2$s%3$s%4$s', 'admin.Notice', 'seraphinite-accelerator' ), $contRemindRefreshCache, $theme -> name, Wp::GetLocString( 'Lazy loading for images', null, 'woodmart' ), esc_html_x( 'OffOptionValue', 'admin.Notice', 'seraphinite-accelerator' ) ) ) );
				if( $isScriptsDelayLoadEnabled && Gen::GetArrField( $themeOpts, 'preloader', null, '/' ) )
					call_user_func_array( $cb, array( Gen::SevWarn, sprintf( Wp::safe_html_x( '3rdSett_DisplayConflict_%1$s%2$s%3$s%4$s', 'admin.Notice', 'seraphinite-accelerator' ), $contRemindRefreshCache, $theme -> name, Wp::GetLocString( 'Preloader', null, 'woodmart' ), esc_html_x( 'OffOptionValue', 'admin.Notice', 'seraphinite-accelerator' ) ) ) );

				break;

			case 'dt-the7':
				$themeOpts = get_option( $themeCh -> stylesheet == 'dt-the7-child' ? 'the7dtchild' : 'the7' );

				if( $isScriptsDelayLoadEnabled && _3rdParty_to_boolean( Gen::GetArrField( $themeOpts, 'general-beautiful_loading', null, '/' ) ) )
					call_user_func_array( $cb, array( Gen::SevWarn, sprintf( Wp::safe_html_x( '3rdSett_DisplayConflict_%1$s%2$s%3$s%4$s', 'admin.Notice', 'seraphinite-accelerator' ), $contRemindRefreshCache, $theme -> name, 'Beautiful loading', esc_html_x( 'OffOptionValue', 'admin.Notice', 'seraphinite-accelerator' ) ) ) );

				break;

			case 'themify-ultra':
				$themeOpts = get_option( 'themify_data' );

				if( $isCacheEnabled )
					if( _3rdParty_to_boolean( Gen::GetArrField( $themeOpts, 'setting-cache-html', null, '/' ) ) )
						call_user_func_array( $cb, array( Gen::SevWarn, sprintf( Wp::safe_html_x( '3rdSett_ConflictSoft_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), $theme -> name, Wp::GetLocString( 'Themify Cache', null, 'themify' ) ) ) );
				if( $isScriptsDelayLoadEnabled )
				{
					if( !_3rdParty_to_boolean( Gen::GetArrField( $themeOpts, 'setting-disable-lazy', null, '/' ) ) )
						call_user_func_array( $cb, array( Gen::SevWarn, sprintf( Wp::safe_html_x( '3rdSett_DisplayConflict_%1$s%2$s%3$s%4$s', 'admin.Notice', 'seraphinite-accelerator' ), $contRemindRefreshCache, $theme -> name, Wp::GetLocString( 'Themify Lazy Load', null, 'themify' ), esc_html_x( 'OffOptionValue', 'admin.Notice', 'seraphinite-accelerator' ) ) ) );
					if( !_3rdParty_to_boolean( Gen::GetArrField( $themeOpts, 'setting-script_minification-min', null, '/' ) ) )
						call_user_func_array( $cb, array( Gen::SevWarn, sprintf( Wp::safe_html_x( '3rdSett_ConflictSoft_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), $theme -> name, Wp::GetLocString( 'Minified Scripts', null, 'themify' ) ) ) );
					if( !_3rdParty_to_boolean( Gen::GetArrField( $themeOpts, 'setting-optimize-wc', null, '/' ) ) )
						call_user_func_array( $cb, array( Gen::SevWarn, sprintf( Wp::safe_html_x( '3rdSett_ConflictSoft_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), $theme -> name, Wp::GetLocString( 'WooCommerce Script Optimization', null, 'themify' ) ) ) );
					if( _3rdParty_to_boolean( Gen::GetArrField( $themeOpts, 'setting-jquery', null, '/' ) ) )
						call_user_func_array( $cb, array( Gen::SevWarn, sprintf( Wp::safe_html_x( '3rdSett_ConflictSoft_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), $theme -> name, Wp::GetLocString( 'Defer jQuery script loading', null, 'themify' ) ) ) );
				}

				break;

			case 'thegem':
				$themeOpts = get_option( 'thegem_theme_options' );

				if( $isScriptsDelayLoadEnabled )
				{
					if( _3rdParty_to_boolean( Gen::GetArrField( $themeOpts, 'pagespeed_lazy_images_desktop_enable', null, '/' ) ) || _3rdParty_to_boolean( Gen::GetArrField( $themeOpts, 'pagespeed_lazy_images_mobile_enable', null, '/' ) ) )
						call_user_func_array( $cb, array( Gen::SevWarn, sprintf( Wp::safe_html_x( '3rdSett_DisplayConflict_%1$s%2$s%3$s%4$s', 'admin.Notice', 'seraphinite-accelerator' ), $contRemindRefreshCache, $theme -> name, Wp::GetLocString( 'Image Loading Optimizations', null, 'thegem' ), esc_html_x( 'OffOptionValue', 'admin.Notice', 'seraphinite-accelerator' ) ) ) );
				}

				break;

			case 'xstore':

				if( $isScriptsDelayLoadEnabled )
				{
					if( get_theme_mod( 'images_loading_type_et-desktop', 'lazy' ) != 'default' )
						call_user_func_array( $cb, array( Gen::SevWarn, sprintf( Wp::safe_html_x( '3rdSett_DisplayConflict_%1$s%2$s%3$s%4$s', 'admin.Notice', 'seraphinite-accelerator' ), $contRemindRefreshCache, $theme -> name, Wp::GetLocString( 'Image Loading Type', null, 'xstore' ), esc_html_x( 'OffOptionValue', 'admin.Notice', 'seraphinite-accelerator' ) ) ) );
				}

				break;

			case 'superio':
				$themeOpts = get_option( 'superio_theme_options' );

				if( $isScriptsDelayLoadEnabled )
				{
					if( _3rdParty_to_boolean( Gen::GetArrField( $themeOpts, 'image_lazy_loading', null, '/' ) ) )
						call_user_func_array( $cb, array( Gen::SevWarn, sprintf( Wp::safe_html_x( '3rdSett_DisplayConflict_%1$s%2$s%3$s%4$s', 'admin.Notice', 'seraphinite-accelerator' ), $contRemindRefreshCache, $theme -> name, Wp::GetLocString( 'Image Lazy Loading', null, 'superio' ), esc_html_x( 'OffOptionValue', 'admin.Notice', 'seraphinite-accelerator' ) ) ) );
				}

				break;
		}
	}

	$availablePlugins = Plugin::GetAvailablePluginsEx();

	$plg = (isset($availablePlugins[ 'wp-smushit' ])?$availablePlugins[ 'wp-smushit' ]:null);
	if( !$plg || !(isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
		$plg = (isset($availablePlugins[ 'wp-smush-pro' ])?$availablePlugins[ 'wp-smush-pro' ]:null);
	if( $plg && (isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
	{
		$plgOpts = get_option( 'wp-smush-settings' );

		if( $isScriptsDelayLoadEnabled && Gen::GetArrField( $plgOpts, 'lazy_load', null, '/' ) )
			call_user_func_array( $cb, array( Gen::SevWarn, sprintf( Wp::safe_html_x( '3rdSett_DisplayConflict_%1$s%2$s%3$s%4$s', 'admin.Notice', 'seraphinite-accelerator' ), $contRemindRefreshCache, $plg[ 'Name' ], Wp::GetLocString( 'Lazy Load', null, 'wp-smushit' ), Wp::GetLocString( 'Deactivate', null, 'wp-smushit' ) ) ) );

	}

	$plg = (isset($availablePlugins[ 'elementor' ])?$availablePlugins[ 'elementor' ]:null);
	if( !$plg || !(isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
		$plg = (isset($availablePlugins[ 'elementor-pro' ])?$availablePlugins[ 'elementor-pro' ]:null);
	if( $plg && (isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
	{

		if( $isScriptsDelayLoadEnabled && ( get_option( 'elementor_lazy_load_background_images' ) == '1' || ( !strlen( ( string )get_option( 'elementor_lazy_load_background_images' ) ) && get_option( 'elementor_experiment-e_lazyload' ) == 'active' ) ) )
		    call_user_func_array( $cb, array( Gen::SevWarn, sprintf( Wp::safe_html_x( '3rdSett_DisplayConflict_%1$s%2$s%3$s%4$s', 'admin.Notice', 'seraphinite-accelerator' ), $contRemindRefreshCache, $plg[ 'Name' ], Wp::GetLocString( 'Lazy Load Background Images', null, 'elementor' ), Wp::GetLocString( 'Inactive', null, 'elementor' ) ) ) );

	}

	$plg = (isset($availablePlugins[ 'ewww-image-optimizer' ])?$availablePlugins[ 'ewww-image-optimizer' ]:null);
	if( $plg && (isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
	{

		if( $isScriptsDelayLoadEnabled && get_option( 'ewww_image_optimizer_lazy_load' ) )
			call_user_func_array( $cb, array( Gen::SevWarn, sprintf( Wp::safe_html_x( '3rdSett_DisplayConflict_%1$s%2$s%3$s%4$s', 'admin.Notice', 'seraphinite-accelerator' ), $contRemindRefreshCache, $plg[ 'Name' ], Wp::GetLocString( 'Lazy Load', null, 'ewww-image-optimizer' ), esc_html_x( 'OffOptionValue', 'admin.Notice', 'seraphinite-accelerator' ) ) ) );
		if( $isScriptsDelayLoadEnabled && get_option( 'ewww_image_optimizer_webp_for_cdn' ) )
			call_user_func_array( $cb, array( Gen::SevWarn, sprintf( Wp::safe_html_x( '3rdSett_DisplayConflict_%1$s%2$s%3$s%4$s', 'admin.Notice', 'seraphinite-accelerator' ), $contRemindRefreshCache, $plg[ 'Name' ], Wp::GetLocString( 'JS WebP Rewriting', null, 'ewww-image-optimizer' ), esc_html_x( 'OffOptionValue', 'admin.Notice', 'seraphinite-accelerator' ) ) ) );

	}

	$plg = (isset($availablePlugins[ 'webp-express' ])?$availablePlugins[ 'webp-express' ]:null);
	if( $plg && (isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
	{

		if( $isScriptsDelayLoadEnabled && _3rdParty_to_boolean( get_option( 'webp-express-alter-html' ) ) )
		{
			$plgAlterHtmlOpts = @json_decode( get_option( 'webp-express-alter-html-options' ), true );
			switch( get_option( 'webp-express-alter-html-replacement' ) )
			{
				case 'picture':
					if( Gen::GetArrField( $plgAlterHtmlOpts, array( 'alter-html-add-picturefill-js' ) ) )
						call_user_func_array( $cb, array( Gen::SevWarn, sprintf( Wp::safe_html_x( '3rdSett_DisplayConflict_%1$s%2$s%3$s%4$s', 'admin.Notice', 'seraphinite-accelerator' ), $contRemindRefreshCache, $plg[ 'Name' ], Wp::GetLocString( 'Dynamically load picturefill.js on older browsers', null, 'webp-express' ), esc_html_x( 'OffOptionValue', 'admin.Notice', 'seraphinite-accelerator' ) ) ) );
					break;
				case 'url':
					if( Gen::GetArrField( $plgAlterHtmlOpts, array( 'only-for-webp-enabled-browsers' ) ) )
						call_user_func_array( $cb, array( Gen::SevWarn, sprintf( Wp::safe_html_x( '3rdSett_DisplayConflict_%1$s%2$s%3$s%4$s', 'admin.Notice', 'seraphinite-accelerator' ), $contRemindRefreshCache, $plg[ 'Name' ], Wp::GetLocString( 'Only do the replacements in webp enabled browsers', null, 'webp-express' ), esc_html_x( 'OffOptionValue', 'admin.Notice', 'seraphinite-accelerator' ) ) ) );
					break;
			}
		}

	}

	$plg = (isset($availablePlugins[ 'rocket-lazy-load' ])?$availablePlugins[ 'rocket-lazy-load' ]:null);
	if( $plg && (isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
	{

		if( $isScriptsDelayLoadEnabled )
			call_user_func_array( $cb, array( Gen::SevWarn, sprintf( Wp::safe_html_x( '3rdSett_DisplayConflict_%1$s%2$s%3$s%4$s', 'admin.Notice', 'seraphinite-accelerator' ), $contRemindRefreshCache, $plg[ 'Name' ], $plg[ 'Name' ], esc_html_x( 'OffOptionValue', 'admin.Notice', 'seraphinite-accelerator' ) ) ) );

	}

	$plg = (isset($availablePlugins[ 'a3-lazy-load' ])?$availablePlugins[ 'a3-lazy-load' ]:null);
	if( $plg && (isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
	{
		$plgOpts = get_option( 'a3_lazy_load_global_settings' );

		if( $isScriptsDelayLoadEnabled && Gen::GetArrField( $plgOpts, 'a3l_apply_lazyloadxt' ) )
			call_user_func_array( $cb, array( Gen::SevWarn, sprintf( Wp::safe_html_x( '3rdSett_DisplayConflict_%1$s%2$s%3$s%4$s', 'admin.Notice', 'seraphinite-accelerator' ), $contRemindRefreshCache, $plg[ 'Name' ], Wp::GetLocString( 'Enable Lazy Load', null, 'a3-lazy-load' ), esc_html_x( 'OffOptionValue', 'admin.Notice', 'seraphinite-accelerator' ) ) ) );

	}

	$plg = (isset($availablePlugins[ 'optimole-wp' ])?$availablePlugins[ 'optimole-wp' ]:null);
	if( $plg && (isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
	{
		$plgOpts = wp_parse_args( get_option( 'optml_settings' ) );

		if( $isScriptsDelayLoadEnabled && _3rdParty_to_boolean( Gen::GetArrField( $plgOpts, 'lazyload', null, '/' ) ) )
			call_user_func_array( $cb, array( Gen::SevWarn, sprintf( Wp::safe_html_x( '3rdSett_DisplayConflict_%1$s%2$s%3$s%4$s', 'admin.Notice', 'seraphinite-accelerator' ), $contRemindRefreshCache, $plg[ 'Name' ], Wp::GetLocString( 'Lazyload', null, 'optimole-wp' ), esc_html_x( 'OffOptionValue', 'admin.Notice', 'seraphinite-accelerator' ) ) ) );

	}

	$plg = (isset($availablePlugins[ 'shortpixel-adaptive-images' ])?$availablePlugins[ 'shortpixel-adaptive-images' ]:null);
	if( $plg && (isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
	{

		if( $isScriptsDelayLoadEnabled )
			call_user_func_array( $cb, array( Gen::SevWarn, sprintf( Wp::safe_html_x( '3rdMdl_Conflict_%1$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ] ) ) );

	}

	$plg = (isset($availablePlugins[ 'async-javascript' ])?$availablePlugins[ 'async-javascript' ]:null);
	if( $plg && (isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
	{

		if( $isContProcEnabled )
			call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdMdl_Conflict_%1$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ] ) ) );

	}

	$plg = (isset($availablePlugins[ 'revslider' ])?$availablePlugins[ 'revslider' ]:null);
	if( $plg && (isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
	{
		$plgOpts = get_option( 'revslider-global-settings' );
		if( !is_array( $plgOpts ) )
			$plgOpts = @json_decode( $plgOpts, true );

		if( $isScriptsDelayLoadEnabled )
		{
			if( _3rdParty_to_boolean( Gen::GetArrField( $plgOpts, 'lazyonbg', 'false' ) ) )
				call_user_func_array( $cb, array( Gen::SevWarn, sprintf( Wp::safe_html_x( '3rdSett_DisplayConflict_%1$s%2$s%3$s%4$s', 'admin.Notice', 'seraphinite-accelerator' ), $contRemindRefreshCache, $plg[ 'Name' ], Wp::GetLocString( 'Lazy Load on BG Images', null, 'revslider' ), esc_html_x( 'OffOptionValue', 'admin.Notice', 'seraphinite-accelerator' ) ) ) );
			if( defined( 'RS_REVISION' ) && version_compare( RS_REVISION, '6.5' ) >= 0 && !in_array( Gen::GetArrField( $plgOpts, 'forceLazyLoading', 'smart' ), array( '', 'none' ), true ) )
				call_user_func_array( $cb, array( Gen::SevWarn, sprintf( Wp::safe_html_x( '3rdSett_DisplayConflict_%1$s%2$s%3$s%4$s', 'admin.Notice', 'seraphinite-accelerator' ), $contRemindRefreshCache, $plg[ 'Name' ], Wp::GetLocString( 'Default lazy loading in modules', null, 'revslider' ), Wp::GetLocString( 'No Change', null, 'revslider' ) ) ) );

		}

	}

	$plg = (isset($availablePlugins[ 'wp-optimize' ])?$availablePlugins[ 'wp-optimize' ]:null);
	if( $plg && (isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
	{

		$plgOpts = get_option( 'wpo_cache_config' );
		if( $isCacheEnabled && Gen::GetArrField( $plgOpts, 'enable_page_caching' ) )
		    call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdSett_Conflict_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ], Wp::GetLocString( 'Enable page caching', null, 'wp-optimize' ) ) ) );

		$plgOpts = get_option( 'wpo_minify_config' );
		if( $isContProcEnabled )
		{
			if( Gen::GetArrField( $plgOpts, 'enabled' ) )
				call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdSett_Conflict_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ], Wp::GetLocString( 'Enable Minify', null, 'wp-optimize' ) ) ) );
		}

	}

	$plg = (isset($availablePlugins[ 'wp-rocket' ])?$availablePlugins[ 'wp-rocket' ]:null);
	if( $plg && (isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
	{
		call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdMdl_Conflict_%1$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ] ) ) );

	}

	$plg = (isset($availablePlugins[ 'w3-total-cache' ])?$availablePlugins[ 'w3-total-cache' ]:null);
	if( $plg && (isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
	{
		call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdMdl_Conflict_%1$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ] ) ) );

	}

	$plg = (isset($availablePlugins[ 'nitropack' ])?$availablePlugins[ 'nitropack' ]:null);
	if( $plg && (isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
	{
		call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdMdl_Conflict_%1$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ] ) ) );
	}

	$plg = (isset($availablePlugins[ 'wp-fastest-cache' ])?$availablePlugins[ 'wp-fastest-cache' ]:null);
	if( $plg && (isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
	{
		call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdMdl_Conflict_%1$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ] ) ) );

	}

	$plg = (isset($availablePlugins[ 'hummingbird-performance' ])?$availablePlugins[ 'hummingbird-performance' ]:null);
	if( $plg && (isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
	{
		call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdMdl_Conflict_%1$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ] ) ) );

	}

	$plg = (isset($availablePlugins[ 'wp-hummingbird' ])?$availablePlugins[ 'wp-hummingbird' ]:null);
	if( $plg && (isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
	{
		call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdMdl_Conflict_%1$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ] ) ) );

	}

	$plg = (isset($availablePlugins[ 'jetpack' ])?$availablePlugins[ 'jetpack' ]:null);
	if( $plg && (isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
	{
		$plgOpts = Gen::GetArrField( get_option( 'jetpack_active_modules' ), array( '' ), array() );
		if( $isCacheEnabled && in_array( 'photon', $plgOpts ) )
			call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdSett_Conflict_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ], Wp::GetLocString( 'Enable site accelerator', null, 'jetpack' ) ) ) );

		if( $isScriptsDelayLoadEnabled && in_array( 'lazy-images', $plgOpts ) )
			call_user_func_array( $cb, array( Gen::SevWarn, sprintf( Wp::safe_html_x( '3rdSett_DisplayConflict_%1$s%2$s%3$s%4$s', 'admin.Notice', 'seraphinite-accelerator' ), $contRemindRefreshCache, $plg[ 'Name' ], Wp::GetLocString( 'Enable Lazy Loading for images', null, 'jetpack' ), esc_html_x( 'OffOptionValue', 'admin.Notice', 'seraphinite-accelerator' ) ) ) );

	}

	$plg = (isset($availablePlugins[ 'autoptimize' ])?$availablePlugins[ 'autoptimize' ]:null);
	if( $plg && (isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
	{

		if( $isContProcEnabled )
			call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdMdl_Conflict_%1$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ] ) ) );

	}

	$plg = (isset($availablePlugins[ 'litespeed-cache' ])?$availablePlugins[ 'litespeed-cache' ]:null);
	if( $plg && (isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
	{

		call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdMdl_Conflict_%1$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ] ) ) );
	}

	$plg = (isset($availablePlugins[ 'sg-cachepress' ])?$availablePlugins[ 'sg-cachepress' ]:null);
	if( $plg )
	{
		if( (isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
		{
			if( $isCacheEnabled && get_option( 'siteground_optimizer_enable_cache' ) && !$isExtCacheAllowed )
				call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdSett_ConflictSoft_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ], Wp::GetLocString( 'Dynamic Caching', null, 'sg-cachepress' ) ) ) );

			if( $isContProcEnabled )
			{
				if( get_option( 'siteground_optimizer_optimize_html' ) )
					call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdSett_ConflictSoft_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ], Wp::GetLocString( 'Minify the HTML Output', null, 'sg-cachepress' ) ) ) );
				if( get_option( 'siteground_optimizer_optimize_javascript' ) )
					call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdSett_ConflictSoft_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ], Wp::GetLocString( 'Minify JavaScript Files', null, 'sg-cachepress' ) ) ) );
				if( get_option( 'siteground_optimizer_combine_javascript' ) )
					call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdSett_ConflictSoft_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ], Wp::GetLocString( 'Combine JavaScript Files', null, 'sg-cachepress' ) ) ) );
				if( get_option( 'siteground_optimizer_optimize_javascript_async' ) )
					call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdSett_ConflictSoft_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ], Wp::GetLocString( 'Defer Render-blocking JavaScript', null, 'sg-cachepress' ) ) ) );
				if( get_option( 'siteground_optimizer_optimize_css' ) )
					call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdSett_ConflictSoft_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ], Wp::GetLocString( 'Minify CSS Files', null, 'sg-cachepress' ) ) ) );
				if( get_option( 'siteground_optimizer_combine_css' ) )
					call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdSett_ConflictSoft_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ], Wp::GetLocString( 'Combine CSS Files', null, 'sg-cachepress' ) ) ) );
				if( get_option( 'siteground_optimizer_optimize_web_fonts' ) )
					call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdSett_ConflictSoft_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ], Wp::GetLocString( 'Web Fonts Optimization', null, 'sg-cachepress' ) ) ) );
			}
			if( $isScriptsDelayLoadEnabled && get_option( 'siteground_optimizer_lazyload_images' ) )
				call_user_func_array( $cb, array( Gen::SevWarn, sprintf( Wp::safe_html_x( '3rdSett_DisplayConflict_%1$s%2$s%3$s%4$s', 'admin.Notice', 'seraphinite-accelerator' ), $contRemindRefreshCache, $plg[ 'Name' ], Wp::GetLocString( 'Lazy Load Media', null, 'sg-cachepress' ), esc_html_x( 'OffOptionValue', 'admin.Notice', 'seraphinite-accelerator' ) ) ) );

		}
		else
		{
			if( $isCacheEnabled )
				call_user_func_array( $cb, array( Gen::SevWarn, sprintf( Wp::safe_html_x( '3rdMdl_ConflictOff_%1$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ] ) ) );
		}
	}

	$plg = (isset($availablePlugins[ 'fast-velocity-minify' ])?$availablePlugins[ 'fast-velocity-minify' ]:null);
	if( $plg && (isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
	{
		if( $isCacheEnabled )
			call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdMdl_Conflict_%1$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ] ) ) );
	}

	$plg = (isset($availablePlugins[ 'breeze' ])?$availablePlugins[ 'breeze' ]:null);
	if( $plg && (isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
	{
		call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdMdl_Conflict_%1$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ] ) ) );
	}

	$plg = (isset($availablePlugins[ 'wp-meteor' ])?$availablePlugins[ 'wp-meteor' ]:null);
	if( $plg && (isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
	{
		call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdMdl_Conflict_%1$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ] ) ) );
	}

	$plg = (isset($availablePlugins[ 'wp-cloudflare-page-cache' ])?$availablePlugins[ 'wp-cloudflare-page-cache' ]:null);
	if( $plg && (isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
	{
		call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdMdl_Conflict_%1$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ] ) ) );
	}

	$plg = (isset($availablePlugins[ 'wp-super-cache' ])?$availablePlugins[ 'wp-super-cache' ]:null);
	if( $plg && (isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
	{
		call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdMdl_Conflict_%1$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ] ) ) );
	}

	$plg = (isset($availablePlugins[ 'a2-optimized-wp' ])?$availablePlugins[ 'a2-optimized-wp' ]:null);
	if( $plg && (isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
	{
		call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdMdl_Conflict_%1$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ] ) ) );
	}

	$plg = (isset($availablePlugins[ 'wp-asset-clean-up' ])?$availablePlugins[ 'wp-asset-clean-up' ]:null);
	if( $plg && (isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
	{
		call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdMdl_Conflict_%1$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ] ) ) );
	}

	$plg = (isset($availablePlugins[ 'perfmatters' ])?$availablePlugins[ 'perfmatters' ]:null);
	if( $plg && (isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
	{
		$plgOpts = get_option( 'perfmatters_options' );

		if( $isContProcEnabled )
		{
			if( _3rdParty_to_boolean( Gen::GetArrField( $plgOpts, 'assets.remove_unused_css' ) ) )
				call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdSett_Conflict_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ], Wp::GetLocString( 'Remove Unused CSS', null, 'perfmatters' ) ) ) );
			if( _3rdParty_to_boolean( Gen::GetArrField( $plgOpts, 'assets.minify_css' ) ) )
				call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdSett_Conflict_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ], Wp::GetLocString( 'Minify CSS', null, 'perfmatters' ) ) ) );
			if( _3rdParty_to_boolean( Gen::GetArrField( $plgOpts, 'assets.minify_js' ) ) )
				call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdSett_Conflict_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ], Wp::GetLocString( 'Minify JavaScript', null, 'perfmatters' ) ) ) );
		}
		if( $isScriptsDelayLoadEnabled )
		{
			if( _3rdParty_to_boolean( Gen::GetArrField( $plgOpts, 'assets.defer_js' ) ) )
				call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdSett_Conflict_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ], Wp::GetLocString( 'Defer Javascript', null, 'perfmatters' ) ) ) );
			if( _3rdParty_to_boolean( Gen::GetArrField( $plgOpts, 'assets.delay_js' ) ) )
				call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdSett_Conflict_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ], Wp::GetLocString( 'Delay JavaScript', null, 'perfmatters' ) ) ) );
			if( _3rdParty_to_boolean( Gen::GetArrField( $plgOpts, 'lazyload.lazy_loading' ) ) )
				call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdSett_Conflict_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ], Wp::GetLocString( 'Images', null, 'perfmatters' ) ) ) );
			if( _3rdParty_to_boolean( Gen::GetArrField( $plgOpts, 'lazyload.lazy_loading_iframes' ) ) )
				call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdSett_Conflict_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ], Wp::GetLocString( 'iFrames and Videos', null, 'perfmatters' ) ) ) );
			if( _3rdParty_to_boolean( Gen::GetArrField( $plgOpts, 'lazyload.css_background_images' ) ) )
				call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdSett_Conflict_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ], Wp::GetLocString( 'CSS Background Images', null, 'perfmatters' ) ) ) );
		}

	}

	$plg = (isset($availablePlugins[ 'flying-pages' ])?$availablePlugins[ 'flying-pages' ]:null);
	if( $plg && (isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
	{
		call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdMdl_Conflict_%1$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ] ) ) );
	}

	$plg = (isset($availablePlugins[ 'flying-scripts' ])?$availablePlugins[ 'flying-scripts' ]:null);
	if( $plg && (isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
	{
		call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdMdl_Conflict_%1$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ] ) ) );
	}

	$plg = (isset($availablePlugins[ 'debloat' ])?$availablePlugins[ 'debloat' ]:null);
	if( $plg && (isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
	{
		call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdMdl_Conflict_%1$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ] ) ) );
	}

	$plg = (isset($availablePlugins[ 'clearfy' ])?$availablePlugins[ 'clearfy' ]:null);
	if( $plg && (isset($plg[ 'IsActive' ])?$plg[ 'IsActive' ]:null) )
	{

		$deactive_preinstall_components = Gen::GetArrField( get_option( 'wbcr_clearfy_deactive_preinstall_components' ), array( '' ), array() );

		if( $isCacheEnabled && get_option( 'wbcr_clearfy_enable_cache' ) && !in_array( 'cache', $deactive_preinstall_components ) )
			call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdSett_Conflict_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ], Wp::GetLocString( 'Enable cache', null, 'clearfy' ) ) ) );

		if( $isContProcEnabled && !in_array( 'minify_and_combine', $deactive_preinstall_components ) )
		{
			if( get_option( 'wbcr_clearfy_css_optimize' ) )
				call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdSett_Conflict_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ], Wp::GetLocString( 'Optimize CSS Code?', null, 'minify-and-combine' ) ) ) );
			if( get_option( 'wbcr_clearfy_css_aggregate' ) )
				call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdSett_Conflict_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ], Wp::GetLocString( 'Aggregate CSS-files?', null, 'minify-and-combine' ) ) ) );
			if( get_option( 'wbcr_clearfy_css_include_inline' ) )
				call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdSett_Conflict_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ], Wp::GetLocString( 'Also aggregate inline CSS?', null, 'minify-and-combine' ) ) ) );
			if( get_option( 'wbcr_clearfy_css_defer' ) )
				call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdSett_Conflict_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ], Wp::GetLocString( 'Inline and Defer CSS?', null, 'minify-and-combine' ) ) ) );
			if( get_option( 'wbcr_clearfy_css_inline' ) )
				call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdSett_Conflict_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ], Wp::GetLocString( 'Inline all CSS?', null, 'minify-and-combine' ) ) ) );

			if( get_option( 'wbcr_clearfy_js_optimize' ) )
				call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdSett_Conflict_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ], Wp::GetLocString( 'Optimize JavaScript Code?', null, 'minify-and-combine' ) ) ) );
			if( get_option( 'wbcr_clearfy_js_aggregate' ) )
				call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdSett_Conflict_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ], Wp::GetLocString( 'Aggregate JS-files?', null, 'minify-and-combine' ) ) ) );
			if( get_option( 'wbcr_clearfy_js_include_inline' ) )
				call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdSett_Conflict_%1$s%2$s', 'admin.Notice', 'seraphinite-accelerator' ), $plg[ 'Name' ], Wp::GetLocString( 'Also aggregate inline JS?', null, 'minify-and-combine' ) ) ) );
		}
	}

	if( !$ext )
		return;

	if( $isContProcEnabled )
	{
		$requestRes = Wp::RemoteGet( Wp::GetSiteRootUrl(), array( 'timeout' => 30, 'sslverify' => false ) );
		if( Net::GetHrFromWpRemoteGet( $requestRes ) === Gen::S_OK )
		{
			$data = wp_remote_retrieve_body( $requestRes );

			if( $isContProcEnabled )
			{
				if( preg_match( '@<script[^>]+type\\s*=\\s*text/ez-screx\\W@', $data ) )
					call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdExtSett_Conflict_%1$s%2$s', 'admin.SelfDiag', 'seraphinite-accelerator' ), 'Ezoic', 'Leap' ) ) );

				if( preg_match( '@<script[^>]+src\\s*=[\'"][^\'"]+cloudflare-static/rocket-loader\\.@', $data ) )
					call_user_func_array( $cb, array( Gen::SevErr, sprintf( Wp::safe_html_x( '3rdExtSett_Conflict_%1$s%2$s', 'admin.SelfDiag', 'seraphinite-accelerator' ), 'CloudFlare', 'Rocket Loader' ) ) );
			}

			if( preg_match( '@<script[^>]+src\\s*=[\'"][^\'"]+/challenge-platform/[^\'"]+/scripts/invisible\\.@', $data ) )
				call_user_func_array( $cb, array( Gen::SevInfo, sprintf( Wp::safe_html_x( '3rdExtSett_SpeedDecr_%1$s%2$s', 'admin.SelfDiag', 'seraphinite-accelerator' ), 'CloudFlare', 'Bot Fight Mode' ) ) );
		}
	}

	if( $isContProcEnabled )
	{

		$bRedirOwn = Gen::GetArrField( $sett, array( 'contPr', 'img', 'redirOwn' ), false );
		foreach( array( 'webp','avif' ) as $comprType )
		{
			if( !Gen::GetArrField( $sett, array( 'contPr', 'img', $comprType, 'enable' ), false ) )
				continue;

			$contMimeTypeTest = 'image/' . $comprType;

			$optionText = sprintf( Plugin::GetPluginString( 'SubjectTitle_%1$s%2$s' ), esc_html_x( 'Lbl', 'admin.Settings_Images_Compr', 'seraphinite-accelerator' ), Gen::GetArrField( array( 'avif' => sprintf( esc_html_x( 'AvifChk_%1$s%2$s', 'admin.Settings_Images_Compr', 'seraphinite-accelerator' ), '', '' ), 'webp' => sprintf( esc_html_x( 'WebpChk_%1$s', 'admin.Settings_Images_Compr', 'seraphinite-accelerator' ), '' ) ), $comprType, '' ) );

			if( $bRedirOwn )
				$testUrl = add_query_arg( array_merge( Image_MakeOwnRedirUrlArgs( ltrim( Net::Url2Uri( plugins_url( '/Images/Test.png', __FILE__ ), true ), '/' ) ), array( '_' => Gen::MicroTimeStamp() ) ), Wp::GetSiteWpRootUrl() );
			else
				$testUrl = add_query_arg( array( '_' => Gen::MicroTimeStamp() ), plugins_url( '/Images/Test.png', __FILE__ ) );

			$requestRes = Wp::RemoteGet( $testUrl, array( 'timeout' => 30, 'sslverify' => false, 'headers' => array( 'Accept' => $contMimeTypeTest . ',image/*,*/*;q=0.8' ) ) );
			if( Net::GetHrFromWpRemoteGet( $requestRes ) !== Gen::S_OK )
			{
				if( $bRedirOwn )
					call_user_func_array( $cb, array( Gen::SevWarn, _SelfDiag_GetResponseResString( $testUrl, $requestRes ) ) );
			}
			else
			{
				$contMimeType = ( string )wp_remote_retrieve_header( $requestRes, 'content-type' );

				if( strpos( $contMimeType, $contMimeTypeTest ) === false )
					call_user_func_array( $cb, array( Gen::SevWarn, vsprintf( Wp::safe_html_x( 'ImgConvRedir_NotActive_%1$s%2$s%3$s%4$s%5$s', 'admin.Notice', 'seraphinite-accelerator' ), array_merge( Ui::Link( array( '', '' ), Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Images_Compr' ), true, array(  ) ), array( $comprType, $optionText, esc_html_x( 'RedirOwnChk', 'admin.Settings_Images_Compr', 'seraphinite-accelerator' ) ) ) ) ) );
			}

			$hr = Img::ConvertDataEx( $dataCnv, @file_get_contents( __DIR__ . '/Images/Test.png' ), $contMimeTypeTest );
			if( Gen::HrFail( $hr ) )
				if( $hr == Gen::E_UNSUPPORTED )
					call_user_func_array( $cb, array( Gen::SevWarn, vsprintf( Wp::safe_html_x( 'ImgConv_NotSupp_%1$s%2$s%3$s%4$s', 'admin.Notice', 'seraphinite-accelerator' ), array_merge( Ui::Link( array( '', '' ), Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Images_Compr' ), true, array(  ) ), array( $comprType, $optionText ) ) ) ) );
				else
					call_user_func_array( $cb, array( Gen::SevWarn, vsprintf( Wp::safe_html_x( 'ImgConv_NotWork_%1$s%2$s%3$s%4$s%5$s', 'admin.Notice', 'seraphinite-accelerator' ), array_merge( Ui::Link( array( '', '' ), Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Help.Settings_Images_Compr' ), true, array(  ) ), array( $comprType, $optionText, Gen::LastErrDsc_Is() ? MsgUnpackLocIds( Gen::LastErrDsc_Get() ) : sprintf( '0x%08X', $hr ) ) ) ) ) );
		}
	}

	if( UseGzAssets( Gen::GetArrField( $sett, array( 'cache' ), array() ) ) )
	{
		foreach( array( 'js', 'css' ) as $assetType )
		{
			$dataTest = @file_get_contents( __DIR__ . '/Images/Test.' . $assetType );

			$testUrl = add_query_arg( array( '_' => Gen::MicroTimeStamp() ), plugins_url( '/Images/Test.' . $assetType, __FILE__ ) );
			$requestRes = Wp::RemoteGet( $testUrl, array( 'timeout' => 30, 'sslverify' => false, 'headers' => array( 'Accept' => 'text/' . $assetType . ',*/*;q=0.1', 'Accept-Encoding' => 'gzip, deflate, br' ) ) );
			if( Net::GetHrFromWpRemoteGet( $requestRes ) !== Gen::S_OK )
			{

			}
			else
			{
				$data = wp_remote_retrieve_body( $requestRes );
				if( $data )
				{
					if( $data !== $dataTest )
						call_user_func_array( $cb, array( Gen::SevWarn, sprintf( Wp::safe_html_x( 'AssetCompr_Bad_%1$s', 'admin.Notice', 'seraphinite-accelerator' ), $assetType ) ) );
				}
			}
		}
	}
}

function _3rdParty_to_boolean( $value )
{
	if( in_array( $value, [ 'yes', 'enabled', 'true', '1', 'on' ], true ) )
		return( true );
	if( in_array( $value, [ 'no', 'disabled', 'false', '0', 'off' ], true ) )
		return( false );
	return( boolval( $value ) );
}

