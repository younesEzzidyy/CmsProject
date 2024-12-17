<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

require_once( __DIR__ . '/Cmn/Gen.php' );
require_once( __DIR__ . '/Cmn/Ui.php' );
require_once( __DIR__ . '/Cmn/Fs.php' );
require_once( __DIR__ . '/Cmn/Db.php' );
require_once( __DIR__ . '/Cmn/Img.php' );
require_once( __DIR__ . '/Cmn/Plugin.php' );

spl_autoload_register(
	function( $class )
	{
		if( strpos( $class, 'MaxMind\\Db\\Reader' ) === 0 )
			@include_once( dirname( __DIR__ ) . '/woocommerce/vendor/maxmind-db/reader/src/' . str_replace( '\\', '/', $class ) . '.php' );
	}
);

const PLUGIN_SETT_VER								= 139;
const PLUGIN_DATA_VER								= 1;
const PLUGIN_EULA_VER								= 1;
const QUEUE_DB_VER									= 4;
const PLUGIN_STAT_VER								= 1;
const PLUGIN_EXTTOOLS_VER							= 1;

function OnOptRead_Sett( $sett, $verFrom )
{
	if( $verFrom == 5 )
	{
		$verFrom = 6;

		if( Gen::GetArrField( $sett, array( 'cache', 'updPostDeps' ), array() ) === array( '/' ) )
			unset( $sett[ 'cache' ][ 'updPostDeps' ] );
	}

	if( $verFrom == 7 )
	{
		$verFrom = 8;

		if( Gen::GetArrField( $sett, array( 'cache', 'lazyInvTmp' ), false ) )
			Gen::SetArrField( $sett, array( 'cache', 'lazyInvForcedTmp' ), true );
	}

	if( $verFrom && $verFrom < 9 )
		Gen::SetArrField( $sett, array( 'contPr', 'css', 'groupFont' ), Gen::GetArrField( $sett, array( 'contPr', 'css', 'groupNonCrit' ), false ) );

	if( $verFrom && $verFrom < 10 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'normUrl' ), true );
		Gen::SetArrField( $sett, array( 'contPr', 'normUrlMode' ), 2 );
	}

	if( $verFrom && $verFrom < 11 )
		Gen::SetArrField( $sett, array( 'contPr', 'lazy', 'bjs' ), false );

	if( $verFrom && $verFrom < 12 )
		Gen::SetArrField( $sett, array( 'contPr', 'css', 'sepImp' ), false );

	if( $verFrom && $verFrom < 17 )
		Gen::SetArrField( $sett, array( 'cache', 'updPostOp' ), 0 );

	if( $verFrom && $verFrom < 20 )
		Gen::SetArrField( $sett, array( 'contPr', 'frm', 'lazy', 'elmntrBg' ), false );

	if( $verFrom && $verFrom < 21 )
	{
		Gen::SetArrField( $sett, array( 'cache', 'updPostDeps' ), Op_DepItems_MigrateFromOld( Gen::GetArrField( $sett, array( 'cache', 'updPostDeps' ), array() ) ) );
		Gen::SetArrField( $sett, array( 'cache', 'updAllDeps' ), Op_DepItems_MigrateFromOld( Gen::GetArrField( $sett, array( 'cache', 'updAllDeps' ), array() ) ) );
	}

	if( $verFrom && $verFrom < 22 )
		Gen::SetArrField( $sett, array( 'contPr', 'frm', 'lazy', 'youTubeFeed' ), false );

	if( $verFrom && $verFrom < 24 )
		Gen::SetArrField( $sett, array( 'contPr', 'img', 'szAdaptBg' ), false );

	if( $verFrom && $verFrom < 26 )
		Gen::SetArrField( $sett, array( 'cache', 'chunks', 'js' ), false );

	if( $verFrom && $verFrom < 27 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'img', 'webp', 'redir' ), Gen::GetArrField( $sett, array( 'contPr', 'img', 'redirWebp' ) ) );
		unset( $sett[ 'contPr' ][ 'img' ][ 'redirWebp' ] );

		Gen::SetArrField( $sett, array( 'contPr', 'img', 'webp', 'enable' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'img', 'avif', 'enable' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'img', 'avif', 'redir' ), false );
	}

	if( $verFrom && $verFrom < 28 )
		Gen::SetArrField( $sett, array( 'contPr', 'img', 'lazy', 'del3rd' ), false );

	if( $verFrom && $verFrom < 29 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'sldBdt' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrBgSldshw' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'css', 'corrErr' ), true );
	}

	if( $verFrom && $verFrom < 30 )
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'prtThSkel' ), false );

	if( $verFrom && $verFrom < 31 )
		Gen::SetArrField( $sett, array( 'contPr', 'img', 'lazy', 'smoothAppear' ), true );

	if( $verFrom && $verFrom < 32 )
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'fltsmThBgFill' ), false );

	if( $verFrom && $verFrom < 33 )
		Gen::SetArrField( $sett, array( 'contPr', 'grps', 'items' ), array() );

	if( $verFrom && $verFrom < 34 )
	{
		$grps = Gen::GetArrField( $sett, array( 'contPr', 'grps', 'items' ), array() );
		$grps[ '@' ] = Gen::GetArrField( OnOptGetDef_Sett(), array( 'contPr', 'grps', 'items', '@' ), array() );
		Gen::SetArrField( $grps, array( '@', 'enable' ), 0 );

		Gen::SetArrField( $sett, array( 'contPr', 'grps', 'items' ), $grps );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'ukSldshw' ), false );
	}

	if( $verFrom && $verFrom < 35 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'sldN2Ss' ), false );

		if( $verFrom >= 34 )
		{
			$sklExclDef = array(
				'matchAll(.//*[@class]/@class, "@(?:post|page|postid|pageid|-id|image|term|item|avatar|user|link_wishlist)[\\-_]([\\da-f]+)@i")',
				'matchAll(.//*[@id]/@id, "@[\\-_]([\\da-f]+)@i")',
			);

			foreach( Gen::GetArrField( $sett, array( 'contPr', 'grps', 'items' ), array() ) as $grpId => $grp )
			{
				Gen::SetArrField( $sett, array( 'contPr', 'grps', 'items', $grpId, 'sklExcl', '+' ), $sklExclDef[ 0 ] );
				Gen::SetArrField( $sett, array( 'contPr', 'grps', 'items', $grpId, 'sklExcl', '+' ), $sklExclDef[ 1 ] );
			}
		}
	}

	if( $verFrom && $verFrom < 36 )
		Gen::SetArrField( $sett, array( 'cache', 'srvClr' ), false );

	if( $verFrom && $verFrom < 37 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'swBdt' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'tdThumbCss' ), false );
	}

	if( $verFrom && $verFrom < 38 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmsKitImgCmp' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'haCrsl' ), false );
	}

	if( $verFrom && $verFrom < 39 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrTabs' ), false );
	}

	if( $verFrom && $verFrom < 40 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'phtncThmb' ), true );
	}

	if( $verFrom && $verFrom < 42 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'normalize' ), Gen::GetArrField( $sett, array( 'contPr', 'normalize' ), 0 ) | 512 );
	}

	if( $verFrom && $verFrom < 43 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrVids' ), false );
	}

	if( $verFrom && $verFrom < 44 )
	{
		Gen::SetArrField( $sett, array( 'cache', 'procIntervalShort' ), Gen::GetArrField( $sett, array( 'cache', 'procInterval' ), 0 ) );
	}

	if( $verFrom && $verFrom < 45 )
	{
		$sklExclPrevDef = array(
			'matchAll(.//*[@class]/@class, "@(?:post|page|postid|pageid|-id|image|term|item|avatar|user|link_wishlist)[\\-_]([\\da-f]+)@i")',
			'matchAll(.//*[@id]/@id, "@[\\-_]([\\da-f]+)@i")',
			'.//svg[@width="0"][@height="0"]',
			'.//body[contains(concat(" ",normalize-space(@class)," ")," woocommerce ")][contains(concat(" ",normalize-space(@class)," ")," single-product ")]//*[match(concat(" ",normalize-space(@class)," "),"@[\\s\\-]products\\s@")]/*',
			'matchAll(.//*[contains(concat(" ",normalize-space(@class)," ")," woocommerce ")]//*[contains(concat(" ",normalize-space(@class)," ")," product ")]/@class, "@\\sproduct_cat-[^\\s]+@", "@\\sproduct_tag-[^\\s]+@", "@\\spa_[^\\s]+@")',
			'matchAll(.//*[contains(concat(" ",normalize-space(@class)," ")," woocommerce-product-attributes-item ")]/@class, "@\\swoocommerce-product-attributes-item--attribute_[^\\s]+@")',
			'matchAll(.//*[contains(concat(" ",normalize-space(@class)," ")," woocommerce ")]//*[contains(concat(" ",normalize-space(@class)," ")," comment-author-")]/@class, "@\\scomment-author-[^\\s]+@")',
		);

		$sklExclPrev2Def = array(
			'.//svg[@width="0"][@height="0"]',
			'.//body[contains(concat(" ",normalize-space(@class)," ")," woocommerce ")][contains(concat(" ",normalize-space(@class)," ")," single-product ")]//*[contains(concat(" ",normalize-space(@class)," ")," products ")]/*',
			'matchAll(.//*[contains(concat(" ",normalize-space(@class)," ")," woocommerce ")]//*[contains(concat(" ",normalize-space(@class)," ")," product ")]/@class, "@\\sproduct_cat-[^\\s]+@", "@\\sproduct_tag-[^\\s]+@", "@\\spa_[^\\s]+@")',
			'matchAll(.//*[contains(concat(" ",normalize-space(@class)," ")," woocommerce-product-attributes-item ")]/@class, "@\\swoocommerce-product-attributes-item--attribute_[^\\s]+@")',
			'matchAll(.//*[contains(concat(" ",normalize-space(@class)," ")," woocommerce ")]//*[contains(concat(" ",normalize-space(@class)," ")," comment-author-")]/@class, "@\\scomment-author-[^\\s]+@")',
			'matchAll(.//*[@class]/@class, "@(?:post|page|postid|pageid|-id|image|term|item|avatar|user|link_wishlist)[\\-_]([\\da-f]+)@i")',
			'matchAll(.//*[@id]/@id, "@[\\-_]([\\da-f]+)@i")',
		);

		$sklExclPrev3Def = array(
			'.//svg[@width="0"][@height="0"]',
			'.//body[contains(concat(" ",normalize-space(@class)," ")," woocommerce ")][contains(concat(" ",normalize-space(@class)," ")," single-product ")]//*[contains(concat(" ",normalize-space(@class)," ")," products ")]/*',
			'matchAll(.//*[contains(concat(" ",normalize-space(@class)," ")," woocommerce ")]//*[contains(concat(" ",normalize-space(@class)," ")," product ")]/@class, "@\\sproduct_cat-[^\\s]+@", "@\\sproduct_tag-[^\\s]+@", "@\\spa_[^\\s]+@")',
			'matchAll(.//*[contains(concat(" ",normalize-space(@class)," ")," woocommerce-product-attributes-item ")]/@class, "@\\swoocommerce-product-attributes-item--attribute_[^\\s]+@")',
			'matchAll(.//*[contains(concat(" ",normalize-space(@class)," ")," woocommerce ")]//*[contains(concat(" ",normalize-space(@class)," ")," comment-author-")]/@class, "@\\scomment-author-[^\\s]+@")',
			'matchAll(.//*[@class]/@class, "@\\s(?:post|postid|menu-item|avatar|user|elementor-repeater-item)[\\-_]([\\da-f]+)@i")',
			'matchAll(.//*[@id]/@id, "@[\\-_]([\\da-f]+)(?:[\\s\\-_]|$)@i")',
		);

		$sklExclPrev4Def = array(
			'.//body[contains(concat(" ",normalize-space(@class)," ")," woocommerce ")][contains(concat(" ",normalize-space(@class)," ")," single-product ")]//*[contains(concat(" ",normalize-space(@class)," ")," products ")]/*',
			'matchAll(.//*[contains(concat(" ",normalize-space(@class)," ")," woocommerce ")]//*[contains(concat(" ",normalize-space(@class)," ")," product ")]/@class, "@\\sproduct_cat-[^\\s]+@", "@\\sproduct_tag-[^\\s]+@", "@\\spa_[^\\s]+@")',
			'.//svg[@width="0"][@height="0"]',
			'matchAll(.//*[contains(concat(" ",normalize-space(@class)," ")," woocommerce ")]//*[contains(concat(" ",normalize-space(@class)," ")," comment-author-")]/@class, "@\\scomment-author-[^\\s]+@")',
			'matchAll(.//*[contains(concat(" ",normalize-space(@class)," ")," woocommerce-product-attributes-item ")]/@class, "@\\swoocommerce-product-attributes-item--attribute_[^\\s]+@")',
			'matchAll(.//*[@id]/@id, "@[\\-_]([\\da-f]+)(?:[\\s\\-_]|$)@i")',
			'matchAll(.//*[@class]/@class, "@(?:post|page|postid|pageid|-id|image|term|item|avatar|user|link_wishlist)[\\-_]([\\da-f]+)@i")',
		);

		$sklExclDef = array(
			'.//br', './/script', './/style', './/link', './/head',
			'.//svg[@width="0"][@height="0"]',
		);

		$sklCssSelExclDef = array(
			'@[\\.#][\\w\\-]*[\\-_]([\\da-f]+)[\\W_]@i',
			'@\\.(?:product_cat|product_tag|pa|woocommerce-product-attributes-item--attribute|comment-author)[\\-_]([\\w\\-]+)@i',
		);

		foreach( Gen::GetArrField( $sett, array( 'contPr', 'grps', 'items' ), array() ) as $grpId => $grp )
		{
			$sklExclCur = Gen::GetArrField( $grp, array( 'sklExcl' ), array() );
			if( Gen::ArrEqual( $sklExclCur, $sklExclPrevDef ) || Gen::ArrEqual( $sklExclCur, $sklExclPrev2Def ) || Gen::ArrEqual( $sklExclCur, $sklExclPrev3Def ) || Gen::ArrEqual( $sklExclCur, $sklExclPrev4Def ) )
			{
				Gen::SetArrField( $sett, array( 'contPr', 'grps', 'items', $grpId, 'sklSrch' ), true );
				Gen::SetArrField( $sett, array( 'contPr', 'grps', 'items', $grpId, 'sklCssSelExcl' ), $sklCssSelExclDef );
				Gen::SetArrField( $sett, array( 'contPr', 'grps', 'items', $grpId, 'sklExcl' ), $sklExclDef );
			}
			else
			{
				Gen::SetArrField( $sett, array( 'contPr', 'grps', 'items', $grpId, 'sklSrch' ), false );
				Gen::SetArrField( $sett, array( 'contPr', 'grps', 'items', $grpId, 'sklCssSelExcl' ), array() );
				foreach( array( './/br', './/script', './/style', './/link', './/head' ) as $sklExclItem )
					Gen::SetArrField( $sett, array( 'contPr', 'grps', 'items', $grpId, 'sklExcl', '+' ), $sklExclItem );
			}
		}
	}

	if( $verFrom && $verFrom < 46 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'jetMobMenu' ), false );
	}

	if( $verFrom && $verFrom < 48 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrNavMenu' ), false );
	}

	if( $verFrom && $verFrom < 49 )
		Gen::SetArrField( $sett, array( 'cache', 'srv' ), false );

	if( $verFrom && $verFrom < 51 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'diviMvImg' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'diviMvText' ), false );
	}

	if( $verFrom && $verFrom < 52 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrAdvTabs' ), false );
	}

	if( $verFrom && $verFrom < 54 )
	{
		Gen::SetArrField( $sett, array( 'cache', 'useTimeoutClnForWpNonce' ), false );
	}

	if( $verFrom && $verFrom < 55 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'sldRev' ), false );
	}

	if( $verFrom && $verFrom < 57 )
	{
		$v = Gen::GetArrField( $sett, array( 'contPr', 'js', 'clickDelay' ) );
		if( $v !== null )
		{
			Gen::SetArrField( $sett, array( 'contPr', 'js', 'clk', 'delay' ), $v );
			unset( $sett[ 'contPr' ][ 'js' ][ 'clickDelay' ] );
		}

		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrWdgtGal' ), false );
	}

	if( $verFrom && $verFrom < 58 )
	{
		foreach( Gen::GetArrField( $sett, array( 'cache', 'updPostDeps' ), array() ) as $i => $dep )
		{
			if( Gen::StrStartsWith( $dep, '@pageNums' ) || Gen::StrStartsWith( $dep, '@commentPageNums' ) )
			{
				$dep = '@post@{ID}:' . $dep;
				Gen::SetArrField( $sett, array( 'cache', 'updPostDeps', $i ), $dep );
			}
		}
	}

	if( $verFrom && $verFrom < 59 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'diviVidBox' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'js', 'clk', 'exclDef', '+' ), './/a[contains(concat(" ",normalize-space(@class)," ")," et_pb_video_play ")]' );
	}

	if( $verFrom && $verFrom < 60 )
	{
		$aExcl = Gen::GetArrField( $sett, array( 'contPr', 'js', 'clk', 'exclDef' ), array() );
		if( !in_array( './/*[starts-with(@href,"#elementor-action")]', $aExcl ) )
			Gen::SetArrField( $sett, array( 'contPr', 'js', 'clk', 'exclDef', '+' ), './/*[starts-with(@href,"#elementor-action")]' );
		if( !in_array( './/a[@e-action-hash]', $aExcl ) )
			Gen::SetArrField( $sett, array( 'contPr', 'js', 'clk', 'exclDef', '+' ), './/a[@e-action-hash]' );
	}

	if( $verFrom && $verFrom < 62 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'scrlSeq' ), false );
	}

	if( $verFrom && $verFrom < 63 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'fusionBgVid' ), false );
	}

	if( $verFrom && $verFrom < 64 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'js', 'groupExcls' ), array() );
	}

	if( $verFrom && $verFrom < 65 )
	{
		Gen::SetArrField( $sett, array( 'cache', 'normAgent' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'js', 'groupExclMdls' ), false );
	}

	if( $verFrom && $verFrom < 66 )
	{
		Gen::SetArrField( $sett, array( 'cache', 'procWorkInt' ), 0.0 );
		Gen::SetArrField( $sett, array( 'cache', 'procPauseInt' ), 0.0 );
	}

	if( $verFrom && $verFrom < 67 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cln', 'cmtsExcl' ), Gen::GetArrField( $sett, array( 'contPr', 'minCmtsExcl' ), array() ) );
		Gen::SetArrField( $sett, array( 'contPr', 'cln', 'cmts' ), Gen::GetArrField( $sett, array( 'contPr', 'min' ), false ) );
		unset( $sett[ 'contPr' ][ 'minCmtsExcl' ] );
	}

	if( $verFrom && $verFrom < 68 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'mkImgSrcSet' ), false );
	}

	if( $verFrom && $verFrom < 69 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'woodmartPrcFlt' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'txpTagGrps' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'diviMvSld' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'diviMvFwHdr' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'diviDsmGal' ), false );
	}

	if( $verFrom && $verFrom < 70 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'eaelSmpMnu' ), false );
	}

	if( $verFrom && $verFrom < 74 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'suTabs' ), false );
	}

	if( $verFrom && $verFrom < 75 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'upbBgImg' ), false );
	}

	if( $verFrom && $verFrom < 76 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrAni' ), false );
	}

	if( $verFrom && $verFrom < 77 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'the7MblHdr' ), false );
	}

	if( $verFrom && $verFrom < 79 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrAccrdn' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'wbwPrdFlt' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'wpStrs' ), false );
	}

	if( $verFrom && $verFrom < 81 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'ukBgImg' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'ukAni' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'tmHdr' ), false );
	}

	if( $verFrom && $verFrom < 82 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'qodefApprAni' ), false );
	}

	if( $verFrom && $verFrom < 83 )
	{
		{
			$autoExcls = Gen::GetArrField( $sett, array( 'contPr', 'css', 'nonCrit', 'autoExcls' ), array() );
			foreach( array( '@\\.animated@', '@\\.qodef-qi--appeared@', '@\\.uk-animation-@', '@\\.show-mobile-header@' ) as $autoExclsExpr )
				if( !in_array( $autoExclsExpr, $autoExcls ) )
					$autoExcls[] = $autoExclsExpr;
			Gen::SetArrField( $sett, array( 'contPr', 'css', 'nonCrit', 'autoExcls' ), $autoExcls );
		}

		{
			$contCss = Gen::GetArrField( $sett, array( 'contPr', 'css', 'custom', 'elementor-vis', 'data' ) );
			if( is_string( $contCss ) )
			{
				$contCss = str_replace( 'body.seraph-accel-js-lzl-ing-ani .elementor-invisible', 'body.seraph-accel-js-lzl-ing-ani .elementor-invisible:not([data-lzl-an])', $contCss );
				Gen::SetArrField( $sett, array( 'contPr', 'css', 'custom', 'elementor-vis', 'data' ), $contCss );
			}
		}
	}

	if( $verFrom && $verFrom < 84 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'fsnEqHghtCols' ), false );
	}

	if( $verFrom && $verFrom < 86 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrWdgtImgCrsl' ), false );
	}

	if( $verFrom && $verFrom < 87 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'ukGrid' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'ukModal' ), false );
		Gen::SetArrField( $sett, array( 'cache', 'chkNotMdfSince' ), false );
	}

	if( $verFrom && $verFrom < 88 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'ukHghtVwp' ), Gen::GetArrField( $sett, array( 'contPr', 'cp', 'tmHdr' ), false ) );

		{
			$autoExcls = Gen::GetArrField( $sett, array( 'contPr', 'css', 'nonCrit', 'autoExcls' ), array() );
			foreach( array( '@\\.uk-modal@', '@\\.uk-first-column@', '@\\.uk-grid-margin@', '@\\.uk-grid-stack@', ) as $autoExclsExpr )
				if( !in_array( $autoExclsExpr, $autoExcls ) )
					$autoExcls[] = $autoExclsExpr;
			Gen::SetArrField( $sett, array( 'contPr', 'css', 'nonCrit', 'autoExcls' ), $autoExcls );
		}

		{
			$contCss = Gen::GetArrField( $sett, array( 'contPr', 'css', 'custom', 'tm', 'data' ) );
			if( is_string( $contCss ) )
			{
				$contCss = str_replace( array( ".uk-flex[uk-height-viewport*=\"offset-top: true\"],\r\n.uk-flex[uk-height-viewport*=\"offset-top:true\"] {\r\n\tmin-height: calc(100vh - 1px*var(--tm-header-placeholder-cy));\r\n}\r\n\r\n", "--tm-header-placeholder-cy" ), array( "", "--uk-header-placeholder-cy" ), $contCss );
				Gen::SetArrField( $sett, array( 'contPr', 'css', 'custom', 'tm', 'data' ), $contCss );
			}
		}

		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'ukNavBar' ), false );
	}

	if( $verFrom && $verFrom < 89 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'diviVidBg' ), false );
	}

	if( $verFrom && $verFrom < 91 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'merimagBgImg' ), false );
	}

	if( $verFrom && $verFrom < 92 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrSpltAni' ), false );
	}

	if( $verFrom && $verFrom < 93 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'fsnAni' ), false );
	}

	if( $verFrom && $verFrom < 94 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'astrRsp' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'thrvAni' ), false );
		Gen::SetArrField( $sett, array( 'cache', 'forceAdvCache' ), false );
	}

	if( $verFrom && $verFrom < 95 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'mdcrLdng' ), false );
		Gen::SetArrField( $sett, array( 'asyncUseRe' ), false );
	}

	if( $verFrom && $verFrom < 96 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'prmmprssLzStls' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'diviLzStls' ), false );
	}

	if( $verFrom && $verFrom < 97 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrPremNavMenu' ), false );
	}

	if( $verFrom && $verFrom < 98 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'img', 'deinlLrg' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'vidJs' ), false );
	}

	if( $verFrom && $verFrom < 99 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'upbAni' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'the7Ani' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'ultRspnsv' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'ultVcHd' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'mnmgImg' ), true );
	}

	if( $verFrom && $verFrom < 100 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'tldBgImg' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'jqVide' ), false );
	}

	if( $verFrom && $verFrom < 101 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'diviVidFr' ), false );
	}

	if( $verFrom && $verFrom < 102 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'wprAniTxt' ), false );
	}

	if( $verFrom && $verFrom < 104 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'fltsmThAni' ), false );
	}

	if( $verFrom && $verFrom < 105 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrWdgtAvoShcs' ), false );
	}

	if( $verFrom && $verFrom < 106 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'diviAni' ), false );
	}

	if( $verFrom && $verFrom < 107 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrHdr' ), false );
	}

	if( $verFrom && $verFrom < 108 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'ntBlueThRspnsv' ), false );
	}

	if( $verFrom && $verFrom < 109 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'phloxThRspnsv' ), false );
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'phloxThAni' ), false );
	}

	if( $verFrom && $verFrom < 110 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrPremScrl' ), false );
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'diviHdr' ), false );
	}

	if( $verFrom && $verFrom < 111 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'brcksAni' ), false );
	}

	if( $verFrom && $verFrom < 112 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrTrxAni' ), false );
	}

	if( $verFrom && $verFrom < 113 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'wprTabs' ), false );
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'wooSctrCntDwnTmr' ), false );
	}

	if( $verFrom && $verFrom < 115 )
	{
		Gen::SetArrField( $sett, array( 'cache', 'lazyInvInitTmp' ), true );
	}

	if( $verFrom && $verFrom < 116 )
	{
		Gen::SetArrField( $sett, array( 'cache', 'ctxSkip' ), false );
	}

	if( $verFrom && $verFrom < 116 )
	{
		Gen::SetArrField( $sett, array( 'cache', 'ctxSkip' ), false );
	}

	if( $verFrom && $verFrom < 117 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrPremTabs' ), false );
	}

	if( $verFrom && $verFrom < 118 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'wooJs' ), false );
	    Gen::SetArrField( $sett, array( 'contPr', 'cp', 'sbThAni' ), false );
	}

	if( $verFrom && $verFrom < 119 )
	{
		Gen::SetArrField( $sett, array( 'asyncMode' ), Gen::GetArrField( $sett, array( 'asyncUseRe' ), false ) ? 're' : '' );
		unset( $sett[ 'asyncUseRe' ] );
	}

	if( $verFrom && $verFrom < 120 )
	{
		{
			$aCacheExt = Gen::GetArrField( $sett, array( 'contPr', 'img', 'cacheExt' ), array() );
			foreach( $aCacheExt as &$eCacheExt )
				if( Gen::StrPosArr( $eCacheExt, array( 'cdninstagram', 'googleusercontent', ) ) !== false )
					$eCacheExt = 'crit:' . $eCacheExt;
			unset( $eCacheExt );
			Gen::SetArrField( $sett, array( 'contPr', 'img', 'cacheExt' ), $aCacheExt );
		}

		Gen::SetArrField( $sett, array( 'contPr', 'js', 'groupCritSpec' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'js', 'critSpec' ), array( 'timeout' => array( 'enable' => true, 'v' => 0, ), 'items' => array(), ) );

		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'lottGen' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmsKitLott' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrShe' ), false );
	}

	if( $verFrom && $verFrom < 121 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'kdncThAni' ), false );
	}

	if( $verFrom && $verFrom < 122 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'jetLott' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'diviPrld' ), false );
	}

	if( $verFrom && $verFrom < 123 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'diviStck' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrStck' ), Gen::GetArrField( $sett, array( 'contPr', 'cp', 'elmntrHdr' ), false ) );
		Gen::UnsetArrField( $sett, array( 'contPr', 'cp', 'elmntrHdr' ) );

		Gen::SetArrField( $sett, array( 'contPr', 'js', 'scrlDelay' ), 0 );
	}

	if( $verFrom && $verFrom < 124 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'mdknThRspnsv' ), false );
	}

	if( $verFrom && $verFrom < 125 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'img', 'szAdaptImg' ), false );
	}

	if( $verFrom && $verFrom < 126 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'jetCrsl' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'jetCrslPst' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrPremCrsl' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'sprflMenu' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'sldRev_SmthLd' ), false );
	}

	if( $verFrom && $verFrom < 127 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'jqJpPlr' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'prstPlr' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'ultAni' ), false );
	}

	if( $verFrom && $verFrom < 128 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'css', 'bfrJs' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'img', 'excl' ), array() );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrWdgtLott' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrWdgtWooPrdImgs' ), false );

		{
			$contCss = Gen::GetArrField( $sett, array( 'contPr', 'css', 'custom', 'rev-slider', 'data' ) );
			if( is_string( $contCss ) )
			{
				$contCss = preg_replace( '@height:\\s*100vh\\s*!important@', 'height: calc(100vh - var(--lzl-rs-offs-y)) !important', $contCss );
				$contCss = preg_replace( '@margin-top:\\s*-100vh\\s*!important@', 'margin-top: calc(-100vh + var(--lzl-rs-offs-y)) !important', $contCss );
				Gen::SetArrField( $sett, array( 'contPr', 'css', 'custom', 'rev-slider', 'data' ), $contCss );
			}
		}

		{
			$contCss = Gen::GetArrField( $sett, array( 'contPr', 'css', 'custom', 'n2-ss-slider', 'data' ) );
			if( is_string( $contCss ) )
			{
				$contCss = preg_replace( '@\\.n2-ss-slider:not\\(\\.n2-ss-loaded\\):not\\(\\[data-ss-carousel\\]\\)\\s+\\[data-slide-public-id="1"\\]\\s+\\.n2-ss-layers-container,\\s*\\.n2-ss-slider:not\\(\\.n2-ss-loaded\\)\\s+\\.n2-ss-slider-controls-advanced\\s*{\\s*opacity:@', ".n2-ss-slider:not(.n2-ss-loaded):not([data-ss-carousel]) [data-slide-public-id=\"1\"] .n2-ss-layers-container,\n.n2-ss-slider:not(.n2-ss-loaded):not([data-ss-carousel]) .n2-ss-slide-backgrounds [data-public-id=\"1\"],\n.n2-ss-slider:not(.n2-ss-loaded) .n2-ss-slider-controls-advanced {\n\topacity:", $contCss );
				Gen::SetArrField( $sett, array( 'contPr', 'css', 'custom', 'n2-ss-slider', 'data' ), $contCss );
			}
		}
	}

	if( $verFrom && $verFrom < 129 )
	{
	    Gen::SetArrField( $sett, array( 'contPr', 'img', 'szAdaptDpr' ), false );

		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'esntlsThAni' ), false );

		{
			$contCss = Gen::GetArrField( $sett, array( 'contPr', 'css', 'custom', 'rev-slider', 'data' ) );
			if( is_string( $contCss ) )
			{
				$contCss = preg_replace( '@height:\\s*calc\\(100vh\\s*@', 'height: calc(var(--seraph-accel-dvh) ', $contCss );
				$contCss = preg_replace( '@margin-top:\\s*calc\\(-100vh\\s*@', 'margin-top: calc(-1*var(--seraph-accel-dvh) ', $contCss );
				Gen::SetArrField( $sett, array( 'contPr', 'css', 'custom', 'rev-slider', 'data' ), $contCss );
			}
		}
	}

	if( $verFrom && $verFrom < 130 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrStrtch' ), false );
	}

	if( $verFrom && $verFrom < 131 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'lazy', 'p' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'beThAni' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'xooelTabs' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrWdgtCntr' ), false );

		Gen::SetArrField( $sett, array( 'cache', 'cntLen' ), true );
	}

	if( $verFrom && $verFrom < 133 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'jqSldNivo' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'upbCntVid' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'wooPrcFlt' ), false );

		{
			$contCss = Gen::GetArrField( $sett, array( 'contPr', 'css', 'custom', 'n2-ss-slider', 'data' ) );
			if( is_string( $contCss ) )
			{
				$contCss = str_replace( '[data-slide-public-id="1"]', '[data-slide-public-id][data-lzl-first="1"]', $contCss );
				$contCss = str_replace( '[data-public-id="1"]', '[data-public-id][data-lzl-first="1"]', $contCss );
				Gen::SetArrField( $sett, array( 'contPr', 'css', 'custom', 'n2-ss-slider', 'data' ), $contCss );
			}
		}
	}

	if( $verFrom && $verFrom < 134 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'sldRev7' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'css', 'font', 'deinlLrg' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'css', 'font', 'deinlLrgSize' ), 512 );
	}

	if( $verFrom && $verFrom < 135 )
	{
		Gen::SetArrField( $sett, array( 'cache', 'ctxContPr' ), true );
		Gen::SetArrField( $sett, array( 'contPr', 'css', 'font', 'optLoadNameExpr' ), '' );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'elmntrWdgtPrmLott' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'diviDataAni' ), false );
	}

	if( $verFrom && $verFrom < 136 )
	{
		Gen::SetArrField( $sett, array( 'cache', 'opAgentPostpone' ), false );
	}

	if( $verFrom && $verFrom < 137 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'img', 'lazy', 'own' ), true );
	}

	if( $verFrom && $verFrom < 138 )
	{
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'nktrLott' ), false );
		Gen::SetArrField( $sett, array( 'contPr', 'cp', 'grnshftPbAosOnceAni' ), false );
	}

	if( $verFrom && $verFrom < 139 )
	{
		foreach( Gen::GetArrField( $sett, array( 'contPr', 'cdn', 'items' ), array() ) as $i => $settCdnItem )
			Gen::SetArrField( $sett, array( 'contPr', 'cdn', 'items', $i, 'sa' ), false );
	}

	return( $sett );
}

function Op_DepItems_MigrateFromOld( $dependItems )
{
	foreach( $dependItems as &$dependItem )
	{
		$dependItem = preg_replace( '`(^|\\W)IF:([^:]+):([^:]+)`',							'${1}@IF@${2}@${3}',					$dependItem );
		$dependItem = preg_replace( '`(^|\\W)terms:`',										'${1}@terms@',							$dependItem );
		$dependItem = preg_replace( '`(^|\\W)termsOfClass:([^:]+):([^:]+):([^:]+)`',		'${1}@termsOfClass@${2}@${3}@${4}',		$dependItem );
		$dependItem = preg_replace( '`(^|\\W)home($|\\W)`',									'${1}@home${2}',						$dependItem );
		$dependItem = preg_replace( '`(^|\\W)postsBase:`',									'${1}@postsBase@',						$dependItem );
		$dependItem = preg_replace( '`(^|\\W)posts:`',										'${1}@posts@',							$dependItem );
		$dependItem = preg_replace( '`(^|\\W)sitemapItems:`',								'${1}@sitemapItems@',					$dependItem );
	}

	return( $dependItems );
}

function OnFileValuesGetRootDir( $var = null )
{
	return( array( GetCacheDir() . '/s', $var !== null ? ( string )$var : GetSiteId() ) );
}

function OnAsyncTasksGetFile()
{
	return( GetCacheDir() . '/at' );
}

function OnAsyncTasksGetPushUrlFile()
{
	return( Gen::GetArrField( Plugin::SettGetGlobal(), array( 'asyncUseCron' ), true ) ? 'wp-cron.php' : 'index.php' );
}

function OnAsyncTasksPushGetMode()
{
	return( Gen::GetArrField( Plugin::SettGetGlobal(), array( 'asyncMode' ), '' ) );
}

function OnAsyncTasksPushReGetLauncher()
{
	return( Gen::GetArrField( Plugin::SettGetGlobal(), array( 'reLnch' ), '' ) );
}

function OnAsyncTasksPushReGetTmpDir()
{
	return( GetCacheDir() . '/tmp' );
}

function OnExtToolsGetDir()
{
	return( GetCacheDir() . '/b' );
}

function OnOptGetDef_Sett()
{
	return( array(

		'full' => true,

		'cache' => array(
			'enable' => true,

			'normAgent' => true,
			'chkNotMdfSince' => true,
			'cntLen' => true,
			'opAgentPostpone' => true,

			'srv' => true,
			'srvClr' => true,
			'nginx' => array(
				'fastCgiDir' => '',
				'fastCgiLevels' => '1:2',
			),
			'sucuri' => array(
				'apiKey' => '',
				'apiSecret' => '',
			),

			'cron' => true,
			'forceAdvCache' => false,

			'lazyInv' => true,
			'lazyInvInitTmp' => true,
			'lazyInvForcedTmp' => false,
			'lazyInvTmp' => false,

			'updPost' => true,
			'updPostDelay' => 0,
			'updPostOp' => 0,
			'updPostDeps' => array(
				'@home',
				'@post@{ID}:@pageNums',
				'@post@{ID}:@commentPageNums',
				'@postsBase@{post_type}:<|@pageNums|@commentPageNums>',
				'@termsOfClass@categories@{post_type}@{ID}:<|@pageNums|@commentPageNums>',
			),
			'updPostMeta' => false,
			'updPostMetaExcl' => array(
				'@^\\d+$@',
				'@^_edit_lock$@',
				'@^classic-editor-remember$@',
				'@post_views_@',
				'@^import_started_at@',
			),

			'updTerms' => false,
			'updTermsOp' => 2,
			'updTermsDeps' => array( 'category', 'product_cat', 'course_cat' ),

			'updAllDeps' => array(
				'@home',
				'@postsViewable:<|@pageNums|@commentPageNums>',
			),

			'updSche' => array(
				'def' => array(
					'enable' => false,
					'op' => 0,
					'prior' => 7,
					'period' => 24,
					'periodN' => 1,
					'times' => array(
						array(
							'm' => 0,
							'tm' => 0,
						)
					),
					'deps' => array(
						'@home',
					),
				),
			),

			'updByTimeout' => true,

			'maxProc' => 1,
			'procInterval' => 5,
			'procIntervalShort' => 1,
			'procMemLim' => 2048,
			'procTmLim' => 570,
			'procWorkInt' => 0.5,
			'procPauseInt' => 0.5,

			'autoProc' => true,

			'timeout' => 7 * 24 * 60,
			'timeoutFr' => 60,
			'timeoutCln' => 182 * 24 * 60,
			'ctxTimeoutCln' => 15 * 24 * 60,
			'autoClnPeriod' => 24 * 60,
			'useTimeoutClnForWpNonce' => true,

			'encs' => array( '', 'gzip', 'deflate', 'compress' ),
			'dataCompr' => array( 'deflate' ),
			'dataLvl' => array(),
			'useDataComprAssets' => true,

			'chunks' => array(
				'enable' => true,
				'js' => true,
				'css' => true,
				'seps' => array(

					array( 'enable' => true, 'sel' => './/header[1]',	'side' => 1 | 2 ),
					array( 'enable' => true, 'sel' => './/footer[last()]',	'side' => 1 | 2 ),

					array( 'enable' => true, 'sel' => './/div[contains(concat(" ", normalize-space(@class), " "), " header ")][1]',	'side' => 1 | 2 ),
					array( 'enable' => true, 'sel' => './/div[contains(concat(" ", normalize-space(@class), " "), " footer ")][last()]',	'side' => 1 | 2 ),

					array( 'enable' => true, 'sel' => './/div[@id="comments" and contains(concat(" ", normalize-space(@class), " "), " comments-area ")]',	'side' => 1 | 2 ),
					array( 'enable' => true, 'sel' => './/section[@id="comment-wrap"]',	'side' => 1 | 2 ),

					array( 'enable' => true, 'sel' => './/div[@data-elementor-type="header"]',	'side' => 1 | 2 ),
					array( 'enable' => true, 'sel' => './/div[@data-elementor-type="footer"]',	'side' => 1 | 2 ),

					array( 'enable' => true, 'sel' => './/div[contains(concat(" ", normalize-space(@class), " "), " tdc-header-wrap ")]',	'side' => 1 | 2 ),
					array( 'enable' => true, 'sel' => './/div[contains(concat(" ", normalize-space(@class), " "), " tdc-footer-wrap ")]',	'side' => 1 | 2 ),
					array( 'enable' => true, 'sel' => './/div[contains(concat(" ", normalize-space(@class), " "), " td-header-template-wrap ")]',	'side' => 1 | 2 ),
					array( 'enable' => true, 'sel' => './/div[contains(concat(" ", normalize-space(@class), " "), " td-footer-template-wrap ")]',	'side' => 1 | 2 ),

					array( 'enable' => true, 'sel' => './/div[contains(concat(" ", normalize-space(@class), " "), " fusion-tb-header ")]',	'side' => 1 | 2 ),
					array( 'enable' => true, 'sel' => './/div[contains(concat(" ", normalize-space(@class), " "), " fusion-tb-footer ")]',	'side' => 1 | 2 ),

					array( 'enable' => true, 'sel' => './/*[contains(concat(" ",normalize-space(@class)," ")," et_pb_comments_module ")]',	'side' => 1 | 2 ),

				),
			),

			'urisExcl' => array(
				'/checkout/',
				'@.*sitemap\.xsl$@',
				'@page/@',
			),
			'exclAgents' => array(
				'printfriendly',
			),
			'exclCookies' => array(),
			'exclArgsAll' => true,

			'exclArgs' => array(
				'aiosp_sitemap_path',
				'aiosp_sitemap_page',
				'xml_sitemap',
				'seopress_sitemap',
				'seopress_news',
				'seopress_video',
				'seopress_cpt',
				'seopress_paged',
				'sitemap',
				'sitemap_n',
			),

			'skipArgsAll' => false,
			'skipArgs' => array( 'redirect_to', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'fbclid', 'story_fbid', 'mibextid', 'gclid', 'wbraid', 'gbraid', '_ga', 'yclid', 'srsltid' ),

			'exclConts' => array(
			),

			'hdrs' => array(
				'@^Set-Cookie\\s*:\\s*wordpress_test_cookie\\s*=@i',
				'@^X-XSS-Protection\\s*:@i',
				'@^X-Frame-Options\\s*:@i',
				'@^Content-Security-Policy\\s*:@i',
				'@^Referrer-Policy\\s*:@i',
				'@^Feature-Policy\\s*:@i',
				'@^Permissions-Policy\\s*:@i',
			),

			'views' => true,
			'viewsDeviceGrps' => array(

				array(
					'enable' => false,
					'name' => '',
					'id' => 'mobilehighres',
					'agents' => array( 'android', 'bada', 'incognito',  'maemo', 'mobi', 'opera mini', 's8000', 'series60', 'ucbrowser', 'ucweb', 'webmate', 'webos', ),
				),

				array(
					'enable' => false,
					'name' => '',
					'id' => 'mobilelowres',
					'agents' => array( '240x320', '2.0 mmp', '\bppc\b', 'alcatel', 'amoi', 'asus', 'au-mic', 'audiovox', 'avantgo', 'benq', 'bird', 'blackberry', 'blazer', 'cdm', 'cellphone', 'danger', 'ddipocket', 'docomo', 'dopod', 'elaine/3.0', 'ericsson', 'eudoraweb', 'fly', 'haier', 'hiptop', 'hp.ipaq', 'htc', 'huawei', 'i-mobile', 'iemobile', 'iemobile/7', 'iemobile/9', 'j-phone', 'kddi', 'konka', 'kwc', 'kyocera/wx310k', 'lenovo', 'lg', 'lg/u990', 'lge vx', 'midp', 'midp-2.0', 'mmef20', 'mmp', 'mobilephone', 'mot-v', 'motorola', 'msie 10.0', 'netfront', 'newgen', 'newt', 'nintendo ds', 'nintendo wii', 'nitro', 'nokia', 'novarra', 'o2', 'openweb', 'opera mobi', 'opera.mobi', 'p160u', 'palm', 'panasonic', 'pantech', 'pdxgw', 'pg', 'philips', 'phone', 'playbook', 'playstation portable', 'portalmmm', 'proxinet', 'psp', 'qtek', 'sagem', 'samsung', 'sanyo', 'sch', 'sch-i800', 'sec', 'sendo', 'sgh', 'sharp', 'sharp-tq-gx10', 'small', 'smartphone', 'softbank', 'sonyericsson', 'sph', 'symbian', 'symbian os', 'symbianos', 'toshiba', 'treo', 'ts21i-10', 'up.browser', 'up.link', 'uts', 'vertu', 'vodafone', 'wap', 'willcome', 'windows ce', 'windows.ce', 'winwap', 'xda', 'xoom', 'zte', ),
				),

				array(
					'enable' => true,
					'name' => '',
					'id' => 'mobile',
					'agents' => array( 'mobile', 'android', 'silk/',  'blackberry', 'opera mini', 'opera mobi', ),
				),
			),

			'viewsGeo' => array(
				'enable' => false,
			),

			'viewsCompatGrps' => array(
				array(
					'enable' => true,
					'id' => 'c',
					'agents' => array(
						'@\\Wmsie \\d+\\.\\d+\\W@',
						'@\\Wtrident/\\d+\\.\\d+\\W@',
						'@\\Wyandexmetrika/\\d+\\.\\d+\\W@',
						'@\\Wgoogleadsenseinfeed\\W@',
						'!@\\Wchrome/\\d+\\W@ & @(?:\\W|^)safari/([\\d\\.]+)(?:\\W|$)@ < 603.3.8',
						'@\\sMac\\sOS\\sX\\s([\\d\\_]+)@i < 10.12.6',
					),
				),

				array(
					'enable' => false,
					'id' => 'cm',
					'agents' => array(
						'@\\Wbingbot/\\d+\\.\\d+\\W@',
					),
				),
			),

			'viewsGrps' => array(
				array(
					'enable' => true,
					'name' => 'AMP',
					'cookies' => array(),
					'args' => array( 'amp', ),
				),

				array(
					'enable' => true,
					'name' => 'WPML',
					'cookies' => array( 'wp_wcml_currency', 'wcml_client_currency', ),
					'args' => array( 'lang', ),
				),

				array(
					'enable' => true,
					'name' => 'WPtouch',
					'cookies' => array( 'wptouch-pro-cache-state', 'wptouch-pro-view', ),
					'args' => array(),
				),

				array(
					'enable' => true,
					'name' => 'VillaTheme WooCommerce Multi Currency',
					'cookies' => array( 'wmc_current_currency' ),
				),

				array(
					'enable' => true,
					'name' => 'YITH Multi Currency Switcher for WooCommerce',
					'cookies' => array( 'yith_wcmcs_currency' ),
				),

				array(
					'enable' => true,
					'name' => 'GDPR Cookie Consent',
					'cookies' => array( 'viewed_cookie_policy', 'cli_user_preference' ),
				),

				array(
					'enable' => true,
					'name' => 'Pixelmate Cookie Banner',
					'cookies' => array( 'pixelmate' ),
				),

				array(
					'enable' => true,
					'name' => 'OneCom Cookie Banner',
					'cookies' => array( 'onecom_cookie_consent' ),
				),

				array(
					'enable' => true,
					'name' => 'us_cookie_notice',
					'cookies' => array( 'us_cookie_notice_accepted' ),
				),

				array(
					'enable' => true,
					'name' => 'Transcy',
					'cookies' => array( 'transcy_' ),
				),

				array(
					'enable' => true,
					'name' => 'WP Legal Pages',
					'cookies' => array( 'wplegalpages-' ),
				),
			),

			'ctx' => false,
			'ctxSkip' => false,
			'ctxSessSep' => true,
			'ctxContPr' => true,
			'ctxCliRefresh' => true,
			'ctxGrps' => array(
				'common' => array(
					'enable' => true,
					'name' => 'Common',
					'cookies' => array(
						'wp-postpass_',
						'comment_author_',
						'sc_commented_posts',
					),
					'args' => array(
						'key',
					),

					'tables' => array(

						array(
							'name' => '%PREFIX%users',
							'col' => 'ID',
						),

						array(
							'name' => '%PREFIX%usermeta',
							'col' => 'user_id',
						),

					),
				),

				'wordpress-social-login' => array(
					'enable' => true,
					'name' => 'Social Login',
					'args' => array(
						'action',
					),
				),

				'theme_woodmart' => array(
					'enable' => true,
					'name' => 'WoodMart Theme',
					'cookies' => array(
						'woodmart_wishlist_products',
					),
				),

				'jet-cw' => array(
					'enable' => true,
					'name' => 'Jet',
					'cookies' => array(
						'jet-wish-list',
						'jet-compare-list',
					),
				),

				'woocommerce' => array(
					'enable' => true,
					'name' => 'WooCommerce',
					'cookies' => array(
						'woocommerce_cart_hash',
						'DISABLED-wp_woocommerce_session_',
						'yith_wcwl_session_',
					),

					'args' => array(
						'add-to-cart',
						'remove_item',
						'removed_item',
						'undo_item',
						'update_cart',
						'proceed',
						'order_again',
						'apply_coupon',
						'remove_coupon',
					),

					'tables' => array(

						array(
							'name' => '%PREFIX%woocommerce_sessions',
							'col' => 'session_key',
						),

						array(
							'name' => '%PREFIX%posts',
							'col' => 'ID',
							'nameRel' => '%PREFIX%postmeta',
							'colRel' => 'meta_value',
							'colRelLink' => 'post_id',
							'condRel' => array( 'meta_key' => array( '_customer_user' ) ),
						),

						array(
							'name' => '%PREFIX%postmeta',
							'col' => 'post_id',
							'nameRel' => '%PREFIX%postmeta',
							'colRel' => 'meta_value',
							'colRelLink' => 'post_id',
							'condRel' => array( 'meta_key' => array( '_customer_user' ) ),
						),

					),
				),

				'easy-digital-downloads' => array(
					'enable' => true,
					'name' => 'Easy Digital Downloads',
					'cookies' => array(
						'@^edd_items_in_cart$@ > 0 & @^PHPSESSID$@i',
					),
				),

				'lifterlms' => array(
					'enable' => true,
					'name' => 'LMS by LifterLMS',

					'tables' => array(

						array(
							'name' => '%PREFIX%lifterlms_sessions',
							'col' => 'session_key',
						),

						array(
							'name' => '%PREFIX%posts',
							'col' => 'ID',
							'nameRel' => '%PREFIX%lifterlms_user_postmeta',
							'colRel' => 'user_id',
							'colRelLink' => 'post_id',
						),

						array(
							'name' => '%PREFIX%postmeta',
							'col' => 'post_id',
							'nameRel' => '%PREFIX%lifterlms_user_postmeta',
							'colRel' => 'user_id',
							'colRelLink' => 'post_id',
						),

					),
				),

				'wp-recall' => array(
					'enable' => true,
					'name' => 'WP-Recall',

					'tables' => array(

						array(
							'name' => '%PREFIX%rcl_bookmarks',
							'col' => 'user_id',
						),

						array(
							'name' => '%PREFIX%rcl_bookmarks_gr',
							'col' => 'user_gr',
						),

						array(
							'name' => '%PREFIX%rcl_chat_messages',
							'col' => 'user_id',
						),

						array(
							'name' => '%PREFIX%rcl_chat_messagemeta',
							'col' => 'message_id',
							'nameRel' => '%PREFIX%rcl_chat_messages',
							'colRel' => 'user_id',
							'colRelLink' => 'message_id',
						),

						array(
							'name' => '%PREFIX%rcl_chat_users',
							'col' => 'user_id',
						),

						array(
							'name' => '%PREFIX%rcl_chats',
							'col' => 'chat_id',
							'nameRel' => '%PREFIX%rcl_chat_users',
							'colRel' => 'user_id',
							'colRelLink' => 'chat_id',
						),

						array(
							'name' => '%PREFIX%rcl_feeds',
							'col' => 'user_id',
						),

						array(
							'name' => '%PREFIX%rcl_groups_users',
							'col' => 'user_id',
						),

						array(
							'name' => '%PREFIX%rcl_groups_options',
							'col' => 'group_id',
							'nameRel' => '%PREFIX%rcl_groups_users',
							'colRel' => 'user_id',
							'colRelLink' => 'group_id',
						),

						array(
							'name' => '%PREFIX%rcl_groups',
							'col' => 'ID',
							'nameRel' => '%PREFIX%rcl_groups_users',
							'colRel' => 'user_id',
							'colRelLink' => 'group_id',
						),

						array(
							'name' => '%PREFIX%rcl_notifications',
							'col' => 'user_id',
						),

						array(
							'name' => '%PREFIX%rcl_orders',
							'col' => 'user_id',
						),

						array(
							'name' => '%PREFIX%rcl_order_items',
							'col' => 'order_id',
							'nameRel' => '%PREFIX%rcl_orders',
							'colRel' => 'user_id',
							'colRelLink' => 'order_id',
						),

						array(
							'name' => '%PREFIX%rcl_rating_values',
							'col' => 'user_id',
						),

						array(
							'name' => '%PREFIX%rcl_rating_totals',
							'col' => 'object_id',
							'nameRel' => '%PREFIX%rcl_rating_values',
							'colRel' => 'user_id',
							'colRelLink' => 'object_id',
						),

						array(
							'name' => '%PREFIX%rcl_rating_users',
							'col' => 'user_id',
						),

						array(
							'name' => '%PREFIX%rcl_temp_media',
							'col' => 'user_id',
						),

						array(
							'name' => '%PREFIX%rcl_user_action',
							'col' => 'user',
						),

						array(
							'name' => '%PREFIX%rcl_uw_shares',
							'col' => 'author_id',
						),

					),
				),
			),
		),

		'cacheBr' => array(
			'enable' => true,
			'timeout' => 30 * 24 * 60,
		),

		'contPr' => array(
			'enable' => true,
			'normalize' => 1|2,

			'normUrl' => false,
			'normUrlMode' => 2,

			'min' => true,
			'cln' => array(
				'cmts' => true,
				'cmtsExcl' => array(
					'@^\\s*/?noindex\\s*$@i',
					'@\\[et-ajax\\]@i',
				),
				'items' => array(
				),
			),
			'rpl' => array(
				'items' => array(
				),
			),

			'lazy' => array(
				'items' => array(
				),
				'bjs' => true,
				'p' => false,
			),

			'fresh' => array(
				'smoothAppear' => true,
				'items' => array(
					'sa:.//*[contains(concat(" ",normalize-space(@class)," ")," wpforms-container ")]',
					'sa:.//*[contains(concat(" ",normalize-space(@class)," ")," wfacp_checkout_form ")]',
				),
			),

			'earlyPaint' => true,

			'img' => array(
				'srcAddLm' => false,
				'sysFlt' => false,
				'inlSml' => true,
				'inlSmlSize' => 1024,
				'deinlLrg' => true,
				'deinlLrgSize' => 2048,
				'redirOwn' => false,
				'redirCacheAdapt' => false,
				'webp' => array(
					'enable' => true,
					'redir' => true,
					'prms' => array(
						'q' => Img::WEBP_QUALITY_DEF,
					),
				),
				'avif' => array(
					'enable' => false,
					'redir' => false,
					'prms' => array(
						'q' => Img::AVIF_QUALITY_DEF,
						's' => Img::AVIF_SPEED_DEF,
					),
				),
				'szAdaptImg' => false,
				'szAdaptBg' => false,
				'szAdaptExcl' => array(

				),
				'szAdaptBgCxMin' => 0,
				'szAdaptDpr' => true,
				'excl' => array(
					'.//svg[contains(concat(" ",normalize-space(@class)," ")," lottgen ")][contains(concat(" ",normalize-space(@class)," ")," js-lzl-ing ")]/image',
				),
				'lazy' => array(
					'setSize' => false,
					'load' => true,
					'own' => true,
					'smoothAppear' => true,
					'del3rd' => true,
					'excl' => array(

						'.//img[contains(concat(\' \',normalize-space(@class),\' \'),\' jetpack-lazy-image \')]',
						'.//img[@data-opt-src]',
						'.//img[contains(concat(" ",normalize-space(@class)," ")," rev-slidebg ")]',
						'.//sr7-module//img',
						'.//img[contains(concat(" ",normalize-space(@class)," ")," a3-notlazy ")]',
						'.//*[contains(concat(" ",normalize-space(@class)," ")," t-bgimg ")]',
						'.//*[contains(concat(" ",normalize-space(@class)," ")," posts-container ")][@data-load-animation]//*[contains(concat(" ",normalize-space(@class)," ")," post-featured-img ")]//img',
						'.//*[contains(concat(" ",normalize-space(@class)," ")," dani-lzl ")]//img',

					),
				),

				'cacheExt' => array(
					'crit:@\\.cdninstagram\\.com/@',
					'crit:@\\.googleusercontent\\.com/@',
					'@\\.ytimg\\.com/@',
					'@\\.vimeocdn\\.com/@',
				),
			),

			'frm' => array(
				'lazy' => array(
					'enable' => true,
					'yt' => true,
					'vm' => true,
					'elmntrBg' => true,
					'youTubeFeed' => true,

				),
			),

			'cp' => array(
				'sldBdt' => true,
				'swBdt' => true,
				'vidJs' => true,
				'elmntrAni' => true,
				'elmntrSpltAni' => true,
				'elmntrTrxAni' => true,
				'elmntrBgSldshw' => true,
				'elmntrVids' => true,
				'qodefApprAni' => true,
				'prtThSkel' => true,
				'astrRsp' => true,
				'ntBlueThRspnsv' => true,
				'mdknThRspnsv' => true,
				'fltsmThBgFill' => true,
				'fltsmThAni' => true,
				'ukSldshw' => true,
				'ukBgImg' => true,
				'ukAni' => true,
				'ukGrid' => true,
				'ukModal' => true,
				'ukHghtVwp' => true,
				'ukNavBar' => true,
				'tmHdr' => true,
				'fusionBgVid' => true,
				'fsnEqHghtCols' => true,
				'fsnAni' => true,
				'thrvAni' => true,
				'phloxThRspnsv' => true,
				'phloxThAni' => true,
				'sldN2Ss' => true,
				'sldRev' => true,
				'sldRev_SmthLd' => true,
				'sldRev7' => true,
				'tdThumbCss' => true,
				'elmsKitImgCmp' => true,
				'elmsKitLott' => true,
				'haCrsl' => true,
				'jetCrsl' => true,
				'jetCrslPst' => true,
				'elmntrTabs' => true,
				'elmntrAccrdn' => true,
				'elmntrAdvTabs' => true,
				'elmntrNavMenu' => true,
				'elmntrPremNavMenu' => true,
				'elmntrPremScrl' => true,
				'elmntrPremTabs' => true,
				'elmntrPremCrsl' => true,
				'elmntrWdgtGal' => true,
				'elmntrWdgtImgCrsl' => true,
				'elmntrWdgtWooPrdImgs' => true,
				'elmntrWdgtCntr' => true,
				'elmntrWdgtAvoShcs' => true,
				'elmntrWdgtLott' => true,
				'elmntrWdgtPrmLott' => true,
				'nktrLott' => true,
				'elmntrStck' => false,
				'elmntrShe' => false,
				'elmntrStrtch' => true,
				'xooelTabs' => true,
				'phtncThmb' => true,
				'jetMobMenu' => true,
				'jetLott' => true,
				'diviMvImg' => true,
				'diviMvText' => true,
				'diviMvSld' => true,
				'diviMvFwHdr' => true,
				'diviVidBox' => true,
				'diviVidBg' => true,
				'diviVidFr' => true,
				'diviDsmGal' => true,
				'diviLzStls' => true,
				'diviPrld' => true,
				'diviStck' => true,
				'diviAni' => true,
				'diviDataAni' => true,
				'diviHdr' => true,
				'brcksAni' => true,
				'kdncThAni' => true,
				'scrlSeq' => true,
				'mkImgSrcSet' => true,
				'woodmartPrcFlt' => true,
				'wooPrcFlt' => true,
				'wbwPrdFlt' => true,
				'wooJs' => true,
				'wpStrs' => true,
				'txpTagGrps' => true,
				'eaelSmpMnu' => true,
				'wprAniTxt' => true,
				'wprTabs' => true,
				'suTabs' => true,
				'upbAni' => true,
				'upbBgImg' => true,
				'upbCntVid' => true,
				'ultRspnsv' => true,
				'ultVcHd' => true,
				'ultAni' => true,
				'the7Ani' => true,
				'the7MblHdr' => true,
				'sbThAni' => true,
				'esntlsThAni' => true,
				'beThAni' => true,
				'merimagBgImg' => true,
				'mdcrLdng' => true,
				'prmmprssLzStls' => true,
				'mnmgImg' => true,
				'tldBgImg' => true,
				'jqVide' => true,
				'jqSldNivo' => true,
				'wooSctrCntDwnTmr' => true,
				'lottGen' => true,
				'sprflMenu' => true,
				'jqJpPlr' => true,
				'prstPlr' => true,
				'grnshftPbAosOnceAni' => true,

			),

			'js' => array(

				'groupCritSpec' => false,
				'groupNonCrit' => false,

				'groupExclMdls' => true,
				'groupExcls' => array(
					'src:@stripe@',
					'src:@\\.hsforms\\.net\\W@',
					'src:@//cdnjs\\.cloudflare\\.com/ajax/libs/bodymovin/[\\d\\.]+/lottie\\.@'
				),

				'min' => false,
				'minExcls' => array(
				),
				'other' => array(
					'incl' => array(

						'.//iframe[@id=\'likes-master\' and contains(@src,\'//widgets.wp.com/likes/master.html?\')]',

					),
				),
				'cprRem' => false,
				'optLoad' => true,
				'cplxDelay' => false,
				'preLoadEarly' => false,
				'loadFast' => false,
				'aniDelay' => 1000,
				'scrlDelay' => 500,

				'clk' => array(
					'delay' => 250,
					'excl' => array(
						'.//*[contains(concat(" ",normalize-space(@class)," ")," cpel-switcher__lang ")]',
						'.//*[contains(concat(" ",normalize-space(@class)," ")," cpel-switcher__lang ")]//a',
					),

					'exclDef' => array(
						'.//a[@href="#"]',
						'.//a[@href="#link-popup"]',

						'.//*[starts-with(@href,"#elementor-action")]',
						'.//a[contains(concat(" ",normalize-space(@class)," ")," mobile-menu ")]',
						'.//a[contains(concat(" ",normalize-space(@class)," ")," elementor-button ")][not(self::node()[contains(concat(" ",normalize-space(@class)," ")," elementor-button-link ")])]',
						'.//a[@e-action-hash]',
						'.//a[contains(concat(" ",normalize-space(@class)," ")," elementor-toggle-title ")]',
						'.//a[contains(concat(" ",normalize-space(@class)," ")," sby_video_thumbnail ")]',
						'.//a[contains(concat(" ",normalize-space(@class)," ")," ui-tabs-anchor ")]',
						'.//a[contains(concat(" ",normalize-space(@class)," ")," elementor-icon ")]',
						'.//a[contains(concat(" ",normalize-space(@class)," ")," wd-open-popup ")]',
						'.//a[starts-with(@href,"#grve-")]',
						'.//button[contains(concat(" ",normalize-space(@class)," ")," elementskit-menu-toggler ")]',
						'.//a[starts-with(@href,"#")][contains(concat(" ",normalize-space(@class)," ")," infinite-mm-menu-button ")]',
						'.//*[contains(concat(" ",normalize-space(@class)," ")," elementor-swiper-button ")]',

						'.//a[contains(concat(" ",normalize-space(@class)," ")," jet-button__instance ")]',
						'.//*[contains(concat(" ",normalize-space(@class)," ")," jet-menu-item ")]/a[contains(concat(" ",normalize-space(@class)," ")," menu-link ")]',

						'.//a[contains(concat(" ",normalize-space(@class)," ")," ajax_add_to_cart ")]',
						'.//button[contains(concat(" ",normalize-space(@class)," ")," single_add_to_cart_button ")]',

						'.//a[contains(concat(" ",normalize-space(@class)," ")," dt-mobile-menu-icon ")]',
						'.//a[contains(concat(" ",normalize-space(@class)," ")," submit ")]',

						'.//a[@uk-toggle]',

						'.//a[contains(concat(" ",normalize-space(@class)," ")," woodmart-nav-link ")]',

						'.//a[contains(concat(" ",normalize-space(@class)," ")," et_pb_video_play ")]',
						'.//*[contains(concat(" ",normalize-space(@class)," ")," et-menu ")]/li/a[starts-with(@href,"#")]',
						'.//a[contains(concat(" ",normalize-space(@class)," ")," et_pb_button ")]',

						'.//a[contains(concat(" ",normalize-space(@class)," ")," meanmenu-reveal ")]',

						'.//*[contains(concat(" ",normalize-space(@class)," ")," wpforms-icon-choices-item ")]',

						'.//a[contains(concat(" ",normalize-space(@class)," ")," wd-el-video-link ")]',

						'.//*[contains(concat(" ",normalize-space(@class)," ")," product-video-button ")]/a',

						'.//button[contains(concat(" ",normalize-space(@class)," ")," menu-toggle ")]',

						'.//a[@data-fslightbox="gallery"]',

						'.//a[contains(concat(" ",normalize-space(@class)," ")," dvmm_button ")]',

						'.//div[@data-thumb]//a',

						'.//a[contains(concat(" ",normalize-space(@class)," ")," searchOpen ")]',

						'.//button[contains(concat(" ",normalize-space(@class)," ")," uicore-toggle ")]',

						'.//a[contains(concat(" ",normalize-space(@class)," ")," bricks-button ")]',

						'.//button[contains(concat(" ",normalize-space(@class)," ")," e-n-menu-toggle ")]',

						'.//img[contains(concat(" ",normalize-space(@class)," ")," swiper-slide-image ")]',
					),
				),

				'nonCrit' => array(
					'inl' => true,
					'int' => true,
					'ext' => true,
					'excl' => true,
					'items' => array(

						'body:@\\Wfunction\\s+et_core_page_resource_fallback\\W@',

						'body:@\\WTRINITY_TTS_WP_CONFIG\\W@',

						'id:@^spai_js$@',

						'src:@/depicter/@', 'body:@\\WDepicter\\W@',

						'src:@\\.github\\.com@',

						'body:@window\\.jetMenuMobileWidgetRenderData@',

						'src:@\\.typekit\\.net@',
						'body:@\\WTypekit\\.load\\(@',

					),

					'timeout' => array(
						'enable' => true,
						'v' => 7500,
					),
				),

				'critSpec' => array(
					'timeout' => array(
						'enable' => true,
						'v' => 0,
					),

					'items' => array(

						'src:@\\.cookiebot\\.com@',

						'id:@^cookieyes$@',
					),
				),

				'spec' => array(
					'timeout' => array(
						'enable' => false,
						'v' => 500,
					),

					'items' => array(

						'@googletagmanager\\.com@i',
						'@(?:^|\\W)gtag\\s*\\(\\s*[\'"]@',

						'@google-analytics\\.com@i',
						'@(?:^|\\W)ga\\s*\\(\\s*[\'"]create\\W@',
						'@(?:^|\\W)ga\\s*\\(\\s*[\'"][^\'"]*\\.?send\\W@',

						'@\\Wgoogleadservices\\.com\\W@i',
						'@\\Wgooglesyndication\\.com/pagead/js/adsbygoogle\\.js(?:$|\\W)@',
						'@(?:^|\\W)window\\s*.\\s*adsbygoogle(?:$|\\W)@',

						'@\\Wgstatic\\.com\\W@',

						'id:@^wpforms-recaptcha-js-after@',
						'src:@/plugins/contact-form-7/modules/recaptcha/@',

						'@//apis\\.google\\.com/js/plusone\\.js$@',
						'@//apis\\.google\\.com/js/platform\\.js$@',

						'!body:@elementor@i & src,body:@connect\\.facebook\\.net@i',
						'@(?:^|\\W)fbq\\s*\\(\\s*[\'"]@',

						'@static\\.hotjar\\.com@i',

						'src:@\\Wtrinitymedia\\.ai/player\\W@',

						'@mc.yandex.ru/metrika@i',
						'@(?:^|\\W)ym\\s*\\(\\s*\\d@',
						'@\\Wyastatic\\.net\\W@i',
						'@\\Wcdn\\.jsdelivr\\.net/npm/yandex-metrica-watch/[\\w\\-]+\\.js@',

						'@\\Wsite\\.yandex\\.net/v[\\d\\.]+/js/all\\.js\\W@s',

						'@\\Wyandex\\.st/share/share\\.js@',

						'@\\Wapi-maps\\.yandex\\.ru/services/constructor\\W@i',
						'body:@\\Wymaps\\W@',

						'@top-fwz1\\.mail\\.ru@i',
						'@(?:^|\\W)_tmr\\s*\\.@',

						'@\\Wconnect\\.ok\\.ru/connect\\.js\\W@',

						'@\\Wlc2ads\\.ru/js/adv_out\\.js@',

						'@counter\\.yadro\\.ru/hit\\?@i',

						'@\\Wadvertur\\.ru\\W@',

						'@\\Wapi\\.content-ad\\.net\\W@',

						'@\\Wuptolike\\.com\\W@i',

						'@\\Wshare\\.pluso\\.ru\\W@',

						'@\\Wsendpulse\\.com/js/push/@',

						'@\\Wmediametrics\\.ru/partner/inject/inject\\.js@',

						'@code[\\w-]*\\.jivosite\\.com@i',

						'@\\Wtawk\\.to\\W@',

						'@\\Wgetbutton\\.io\\W@',

						'@bigreal\\.org/@i',
						'@\\.realbig\\.media/@i',

						'@\\Wtruenat\\.bid\\W@i',
						'@\\Wnewup\\.bid\\W@i',
						'@\\Wnewrrb\\.bid\\W@i',
						'@\\Wrb_ajaxurl\\W@',
						'@\\Wcontent_rb\\W@',
						'@gi\\[\'ads\'\\]\\[\'siteAdBlock\'\\]&&gi\\[\'ads\'\\]\\[\'blockAdBlock\'\\]&&gh\\[\'setAttribute\'\\]\\(\'data-ad-block\'@',

						'@\\Wgismeteo\\.ru/api/informer/getinformer/@',

						'@\\Wvk\\.com/js/api/openapi\\.js\\W@',
						'@(?:^|\\W)VK\\s*\\.\\s*Widgets\\W@s',
						'@(?:^|\\W)VK\\s*\\.\\s*init\\s*\\(@s',

						'@\\.addtoany\\.com\\W@i',

						'@\\Wtravelpayouts\\.com\\W@i',

						'src:@\\Wcalendly\\.com@',

						'@/woocommerce-gateway-stripe/assets/js/stripe(?:\\.|\\.min\\.)js\\W@',

						'@/amp\\-analytics\\-@i',

						'@\\w*\\.write\\s*\\(\\s*[\'"]<a\\s*[^>]*href\\s*=\\s*[\'"][^\'"]*www\\.liveinternet\\.ru\\W@',
						'@\\w*\\.write\\s*\\(\\s*[\'"]<img\\s*[^>]*src\\s*=\\s*[\'"][^\'"]*\\Wcounter\\.yadro\\.ru\\W@',

						'@\\Wadvertur\\.ru/v\\d+/code\\.js\\?id=\\d+@',

						'@\\.googlesyndication\\.com/pagead/show_ads\\.js$@',

						'@(?:^|\\W)amzn_assoc_placement\\s*=\\s*[\\\'"][\\w\\-]+[\\\'"]@',
						'@\\Wamazon-adsystem\\.com/widgets/onejs\\W@',

						'@\\.\\s*write\\s*\\(.+<div.+div>.+\\Wuptolike\\.com\\W@s',

						'@\\Wbeeketing\\.com\\W@',

						'@counter\\.rambler\\.ru/top100\\.jcn\\?@',

						'body:@function\\(c,h,i,m,p\\)@',

						'src:@\\.popt\\.in\\W@',

						'src:@/interactive-geo-maps/@',
						'src:@amcharts\\.com@',

						'@\\.chatbase\\.co@i',

						'src:@\\.hsforms\\.net\\W@',
						'body:@hbspt\\.forms\\.create\\(@',

						'src:@\\.visitiq\\.io@',
						'body:@vpixel\\.piximage@',

					),
				),
				'skips' => array(),
			),

			'css' => array(
				'corrErr' => true,
				'group' => true,
				'groupCombine' => false,
				'groupNonCrit' => true,
				'groupNonCritCombine' => false,

				'groupFont' => true,
				'groupFontCombine' => true,
				'font' => array(
					'deinlLrg' => false,
					'deinlLrgSize' => 512,
					'optLoadNameExpr' => '',
				),
				'fontPreload' => false,

				'sepImp' => true,
				'min' => true,
				'optLoad' => true,
				'inlAsSrc' => false,
				'inlCrit' => true,
				'inlNonCrit' => false,
				'delayNonCritWithJs' => true,
				'bfrJs' => false,
				'nonCrit' => array(
					'auto' => true,
					'autoExcls' => array(

						'@depicter@',

						'@\\.show-mobile-header@',

						'@\\.uk-modal@',

						'@#cr_floatingtrustbadge@',

						'@(?:^|\\s)br(?:$|[\\s\\.#\\[])@',
					),
					'inl' => true,
					'int' => true,
					'ext' => true,
					'excl' => false,
					'items' => array(),
				),

				'fontOptLoad' => true,
				'fontOptLoadDisp' => 'swap',
				'fontCrit' => false,

				'skips' => array(
					'id:@^reycore-critical-css$@',
				),

				'custom' => array(
					'0' => array( 'enable' => true, 'data' => '' ),
					'jet-menu'		=> array( 'enable' => false,	'descr' => 'Jet Menu',					'data' => ".seraph-accel-js-lzl-ing ul.jet-menu > li[id^=jet-menu-item-] {\n\tdisplay: none!important;\n}" ),
					'jet-testimonials'		=> array( 'enable' => true,	'descr' => 'Jet Testimonials',	'data' => ".jet-testimonials__instance:not(.slick-initialized) .jet-testimonials__item {\r\n\tmax-width: 100%;\r\n}\r\n\r\n.jet-testimonials__instance:not(.slick-initialized) .jet-testimonials__item:nth-child(n+4) {\r\n\tdisplay: none !important;\r\n}" ),
					'xo-slider'		=> array( 'enable' => true,		'descr' => 'XO Slider',					'data' => ".xo-slider .slide-content {\n\tdisplay: unset!important;\n}" ),

					'jqSldNivo'	=> array( 'enable' => true,		'descr' => 'Nivo Slider for jQuery',	'data' => "body:is(.seraph-accel-js-lzl-ing, .seraph-accel-js-lzl-ing-ani) .nivo-caption {\r\n\topacity: 1 !important;\r\n}\r\n\r\n.nivo-caption {\r\n\tdisplay: none;\r\n}" ),

					'owl-carousel'	=> array( 'enable' => true,		'descr' => 'OWL Carousel',				'data' => ".owl-carousel:not(.wd-owl):not(.owl-loaded) {\r\n\tdisplay: block !important;\r\n\tvisibility: visible !important;\r\n}\r\n\r\n.owl-carousel:not(.wd-owl):not(.owl-loaded) > *:not(:first-child) {\r\n\tdisplay: none;\r\n}\r\n\r\n.owl-carousel:not(.wd-owl) .container.full-screen {\r\n\theight: 100vh;\r\n}" ),

					'ult-carousel'	=> array( 'enable' => true,		'descr' => 'Ultimate Carousel',			'data' => ".seraph-accel-js-lzl-ing .ult-carousel-wrapper {\n\tvisibility:initial!important;\n}\n\n.seraph-accel-js-lzl-ing .ult-carousel-wrapper .ult-item-wrap:not(:first-child) {\n\tdisplay:none;\n}" ),

					'bdt-slideshow'	=> array( 'enable' => true,		'descr' => 'Airtech Plumber Slider',	'data' => ".seraph-accel-js-lzl-ing .bdt-prime-slider-previous, .seraph-accel-js-lzl-ing .bdt-prime-slider-next {\r\n\tdisplay: none !important;\r\n}\r\n\r\n.seraph-accel-js-lzl-ing .bdt-post-slider-item:first-child {\r\n\tdisplay: unset !important;\r\n}" ),

					'n2-ss-slider'	=> array( 'enable' => true,		'descr' => 'Smart Slider',				'data' => "ss3-force-full-width, ss3-fullpage {\r\n\ttransform: none !important;\r\n\topacity: 1 !important;\r\n\twidth: var(--seraph-accel-client-width) !important;\r\n\tmargin-left: calc((100% - var(--seraph-accel-client-width)) / 2);\r\n}\r\n\r\nss3-fullpage {\r\n\theight: 100vh !important;\r\n}\r\n\r\nbody.seraph-accel-js-lzl-ing .n2-ss-align {\r\n\toverflow: visible !important;\r\n}\r\n\r\n.n2-ss-slider:not(.n2-ss-loaded):not([data-ss-carousel]) .n2-ss-slide-backgrounds [data-public-id][data-lzl-first=\"1\"],\r\n.n2-ss-slider:not(.n2-ss-loaded):not([data-ss-carousel]) [data-slide-public-id][data-lzl-first=\"1\"] {\r\n\ttransform: translate3d(0px, 0px, 0px) !important;\r\n}\r\n\r\n.n2-ss-slider:not(.n2-ss-loaded):not([data-ss-carousel]) .n2-ss-slide:not([data-slide-public-id][data-lzl-first=\"1\"]),\r\n.n2-ss-slider:not(.n2-ss-loaded) .n2-ss-layer.js-lzl-n-ing,\r\n.n2-ss-slider:not(.n2-ss-loaded):not([style*=ss-responsive-scale]) [data-responsiveposition],\r\n.n2-ss-slider:not(.n2-ss-loaded):not([style*=ss-responsive-scale]) [data-responsivesize],\r\n.n2-ss-slider.n2-ss-loaded .n2-ss-layer.js-lzl-ing {\r\n\tvisibility: hidden !important;\r\n}\r\n\r\n.n2-ss-slider:not(.n2-ss-loaded):not([data-ss-carousel]) [data-slide-public-id][data-lzl-first=\"1\"] .n2-ss-layers-container,\r\n.n2-ss-slider:not(.n2-ss-loaded):not([data-ss-carousel]) .n2-ss-slide-backgrounds [data-public-id][data-lzl-first=\"1\"],\r\n.n2-ss-slider:not(.n2-ss-loaded) .n2-ss-slider-controls-advanced {\r\n\topacity: 1 !important;\r\n}\r\n\r\n.n2-ss-slider[data-ss-carousel]:not(.n2-ss-loaded) .n2-ss-layers-container {\r\n\topacity: 1 !important;\r\n\tvisibility: visible !important;\r\n}\r\n\r\n.n2-ss-slider-pane {\r\n\topacity: 1 !important;\r\n\tanimation-name: none !important;\r\n\t--self-side-margin: auto !important;\r\n\t--slide-width: 100% !important;\r\n}\r\n\r\n/*.n2-ss-showcase-slides:not(.n2-ss-showcase-slides--ready) {\r\n\topacity: 1 !important;\r\n\ttransform: none !important;\r\n}*/" ),

					'wp-block-ultimate-post-slider'	=> array( 'enable' => true,		'descr' => 'Block Ultimate Post Slider',	'data' => "[class*=wp-block-ultimate-post-post-slider] .ultp-block-items-wrap:not(.slick-initialized) > .ultp-block-item:not(:first-child)\n{\n\tdisplay: none!important;\n}" ),

					'preloaders'	=> array( 'enable' => true,		'descr' => 'Preloaders',				'data' => "#pre-load, #preloader, #page_preloader, #page-preloader, #loader-wrapper, #royal_preloader, #loftloader-wrapper, #page-loading, #the7-body > #load, #loader, #loaded, #loader-container,\r\n.rokka-loader, .page-preloader-cover, .apus-page-loading, .medizco-preloder, e-page-transition, .loadercontent, .shadepro-preloader-wrap, .tslg-screen, .page-preloader, .pre-loading, .preloader-outer, .page-loader, .martfury-preloader, body.theme-dotdigital > .preloader, .loader-wrap, .site-loader, .pix-page-loading-bg, .pix-loading-circ-path, .mesh-loader {\r\n\tdisplay: none !important;\r\n}\r\n\r\nbody.royal_preloader {\r\n\tvisibility: hidden !important;\r\n}" ),

					'elementor-vis'		=> array( 'enable' => false, 'descr' => 'Elementor (visibility and animation)', 'data' => "body.seraph-accel-js-lzl-ing-ani .elementor-invisible {\r\n\tvisibility: visible !important;\r\n}\r\n\r\n.elementor-element[data-settings*=\"animation\\\"\"] {\r\n\tanimation-name: none !important;\r\n}" ),

					'elementor'		=> array( 'enable' => true, 'descr' => 'Elementor', 'data' => ".vc_row[data-vc-full-width] {\r\n\tposition: relative;\r\n\twidth: var(--seraph-accel-client-width) !important;\r\n}\r\n\r\nhtml:not([dir=rtl]) .vc_row[data-vc-full-width] {\r\n\tleft: calc((100% - var(--seraph-accel-client-width)) / 2) !important;\r\n\tmargin-left: 0 !important;\r\n}\r\n\r\nhtml[dir=rtl] .vc_row[data-vc-full-width] {\r\n\tright: calc((100% - var(--seraph-accel-client-width)) / 2) !important;\r\n\tmargin-right: 0 !important;\r\n}\r\n\r\n.vc_row.wpb_row[data-vc-full-width]:not([data-vc-stretch-content=\"true\"]), .vc_row.mpc-row[data-vc-full-width]:not([data-vc-stretch-content=\"true\"]) {\r\n\t--pdd: calc((var(--seraph-accel-client-width) - (100% + 2*15px)) / 2);\r\n\tpadding-left: var(--pdd) !important;\r\n\tpadding-right: var(--pdd) !important;\r\n}\r\n\r\n.elementor-top-section.elementor-section-stretched[data-settings*=\"section-stretched\"] {\r\n\twidth: var(--seraph-accel-client-width) !important;\r\n}\r\n\r\nhtml:not([dir=rtl]) .elementor-top-section.elementor-section-stretched[data-settings*=\"section-stretched\"] {\r\n\tleft: calc(-1 * var(--lzl-strtch-offs-x)) !important;\r\n}\r\n\r\nhtml[dir=rtl] .elementor-top-section.elementor-section-stretched[data-settings*=\"section-stretched\"] {\r\n\tright: calc(-1 * var(--lzl-strtch-offs-x)) !important;\r\n}\r\n\r\nbody.seraph-accel-js-lzl-ing-ani .elementor-headline-dynamic-text.elementor-headline-text-active {\r\n\topacity: 1;\r\n}" ),

					'et'			=> array( 'enable' => true,		'descr' => 'Divi',						'data' => ".et_animated:not(.et_pb_sticky_placeholder,.dani-lzl) {\r\n\topacity: 1 !important;\r\n}\r\n\r\n.et_pb_section_video_bg > video {\r\n\theight: 100%;\r\n}\r\n\r\n.et_pb_preload .et_pb_section_video_bg, .et_pb_preload > div {\r\n\tvisibility: visible !important;\r\n}\r\n\r\nbody:is(.seraph-accel-js-lzl-ing, .seraph-accel-js-lzl-ing-ani) .et_pb_gallery_grid .et_pb_gallery_item {\r\n\tdisplay: block !important;\r\n}\r\n\r\n/* Slider */\r\n/*.et_pb_slider:not([data-active-slide]) {\r\n\theight: 1px;\r\n}*/\r\n\r\n.et_pb_slider:not([data-active-slide]) .et_pb_slides,\r\n.et_pb_slider:not([data-active-slide]) .et_pb_slide:first-child,\r\n.et_pb_slider:not([data-active-slide]) .et_pb_slide:first-child .et_pb_container {\r\n\theight: 100%;\r\n}" ),

					'tag-div'		=> array( 'enable' => true,		'descr' => 'tagDiv',					'data' => "body.td-animation-stack-type0 .td-animation-stack .entry-thumb,\nbody.td-animation-stack-type0 .post img:not(.woocommerce-product-gallery img):not(.rs-pzimg),\nbody.td-animation-stack-type0 .td-animation-stack .td-lazy-img,\n.tdb_header_menu .tdb-menu-items-pulldown.tdb-menu-items-pulldown-inactive {\n\topacity: 1!important;\n}" ),
					'photonic-thumb'	=> array( 'enable' => true,		'descr' => 'Photonic Photo Gallery',	'data' => ".photonic-thumb,\r\n.photonic-thumb a img {\r\n\tdisplay: unset !important;\r\n}\r\n\r\n.photonic-loading {\r\n\tdisplay: none !important;\r\n}\r\n\r\n.photonic-stream * {\r\n\tanimation-name: none !important;\r\n}" ),
					'avia-slideshow'	=> array( 'enable' => true,		'descr' => 'Avia Slideshow',		'data' => ".avia-slideshow.av-default-height-applied .avia-slideshow-inner > li:first-child {\r\n\topacity: 1 !important;\r\n\tvisibility: visible !important;\r\n}\r\n" ),

					'rev-slider'		=> array( 'enable' => true,		'descr' => 'Revolution Slider',		'data' => "rs-module-wrap:has(rs-module[data-lzl-layout=\"fullwidth\"]), rs-module-wrap:has(rs-module[data-lzl-layout=\"fullscreen\"]), rs-fullwidth-wrap, rs-fullwidth-wrap > rs-module-wrap {\r\n\twidth: var(--seraph-accel-client-width) !important;\r\n\tleft: calc((100% - var(--seraph-accel-client-width)) / 2) !important;\r\n}\r\n\r\nrs-module[data-lzl-layout=\"fullscreen\"] {\r\n\theight: calc(var(--seraph-accel-dvh) - var(--lzl-rs-offs-y)) !important;\r\n}\r\n\r\nrs-module[data-lzl-layout=\"fullscreen\"].js-lzl-ing {\r\n\tmargin-top: calc(-1*var(--seraph-accel-dvh) + var(--lzl-rs-offs-y)) !important;\r\n}" ),

					'fusion-vis'		=> array( 'enable' => false,	'descr' => 'Fusion (visibility and animation)',					'data' => ".fusion-animated {\n\tvisibility: visible;\n}\n" ),
					'fusion-menu'		=> array( 'enable' => true,		'descr' => 'Fusion Menu',					'data' => ".fusion-menu-element-wrapper.loading {\n\topacity: 1;\n}\n\n@media (max-width: 1024px) {\n\t.fusion-menu-element-wrapper.loading .fusion-menu {\n\t\tdisplay: none;\n\t}\n\n\t.fusion-menu-element-wrapper.loading button {\n\t\tdisplay: block !important;\n\t}\n\n\t.fusion-menu-element-wrapper.loading {\n\t\tdisplay: flex;\n\t}\n}" ),
					'jnews'			=> array( 'enable' => true,		'descr' => 'JNews Theme',					'data' => ".thumbnail-container.animate-lazy > img {\n\topacity: 1!important;\n}" ),
					'grve'			=> array( 'enable' => true,		'descr' => 'GROVE Theme',					'data' => ".grve-bg-image {\r\n\topacity: 1 !important;\r\n}\r\n\r\nbody.seraph-accel-js-lzl-ing-ani .grve-animated-item {\r\n\tanimation-fill-mode: both;\r\n\tanimation-duration: .8s;\r\n}\r\n\r\nbody.seraph-accel-js-lzl-ing-ani .grve-fade-in-left {\r\n\tanimation-name: grve_fade_in_left;\r\n}\r\n\r\nbody.seraph-accel-js-lzl-ing-ani .grve-fade-in {\r\n\tanimation-name: grve_fade_in;\r\n}\r\n\r\nbody.seraph-accel-js-lzl-ing-ani .grve-fade-in-up {\r\n\tanimation-name: grve_fade_in_up;\r\n}\r\n" ),

					'wpb'			=> array( 'enable' => true,		'descr' => 'WPBakery',					'data' => ".upb_row_bg[data-bg-override=\"browser_size\"],\r\n.upb_row_bg[data-bg-override*=\"full\"],\r\n.ult-vc-seperator[data-full-width=\"true\"] {\r\n\twidth: var(--seraph-accel-client-width) !important;\r\n}\r\n\r\n.ult-vc-seperator[data-full-width=\"true\"] .ult-main-seperator-inner {\r\n\twidth: 100% !important;\r\n\tmargin-left: 0 !important;\r\n\tmargin-right: 0 !important;\r\n}\r\n\r\nhtml:not([dir=rtl]) .upb_row_bg[data-bg-override=\"browser_size\"],\r\nhtml:not([dir=rtl]) .upb_row_bg[data-bg-override*=\"full\"],\r\nhtml:not([dir=rtl]) .ult-vc-seperator[data-full-width=\"true\"] {\r\n\tmargin-left: calc((100% - var(--seraph-accel-client-width)) / 2) !important;\r\n\tleft: 0 !important;\r\n}\r\n\r\nhtml[dir=rtl] .upb_row_bg[data-bg-override=\"browser_size\"],\r\nhtml[dir=rtl] .upb_row_bg[data-bg-override*=\"full\"],\r\nhtml[dir=rtl] .ult-vc-seperator[data-full-width=\"true\"] {\r\n\tmargin-right: calc((100% - var(--seraph-accel-client-width)) / 2) !important;\r\n\tright: 0 !important;\r\n}" ),

					'tm'			=> array( 'enable' => true,		'descr' => 'Yoo Theme',					'data' => ".tm-header-placeholder {\r\n\theight: calc(1px*var(--uk-header-placeholder-cy));\r\n}\r\n" ),

					'packery'		=> array( 'enable' => true,		'descr' => 'Packery',					'data' => "[data-packery-options].row.row-grid > .col:not([style*=\"position\"]),\r\n[data-packery-options].row.row-masonry > .col:not([style*=\"position\"]) {\r\n\tfloat: unset;\r\n\tdisplay: inline-block !important;\r\n\tvertical-align: top;\r\n}" ),

					'htmlGen'		=> array( 'enable' => true,		'descr' => 'Generic HTML',					'data' => "html, body {\r\n\tdisplay: block !important;\r\n\topacity: 1 !important;\r\n\tvisibility: unset !important;\r\n}" ),

					'cookie-law-info'		=> array( 'enable' => true,		'descr' => 'CookieYes',					'data' => ".cky-consent-container.cky-hide ~ .cky-consent-container {\r\n\tdisplay: none;\r\n}" ),
				),
			),

			'cdn' => array(

				'items' => array(
					array(
						'enable' => true,
						'sa' => true,
						'addr' => '',
						'types' => array( 'js', 'css', 'less', 'gif', 'jpeg', 'jpg', 'bmp', 'png', 'svg', 'webp', 'avif', 'eot', 'aac', 'mp3', 'mp4', 'ogg', 'pdf', 'docx', 'otf', 'ttf', 'woff', 'woff2' ),
						'uris' => array( 'wp-content', 'wp-includes' ),
						'urisExcl' => array(),
					),
				),
			),

			'grps' => array(

				'items' => array(

					'home' => array(
						'enable' => 0,
						'name' => 'Home',
						'urisIncl' => array(),
						'argsIncl' => array(),
						'patterns' => array( './/body[contains(concat(" ",normalize-space(@class)," ")," home ")]' ),
						'views' => array(),
						'sklExcl' => array(),
						'contPr' => array(
							'enable' => true,
							'jsOvr' => true,
							'js' => array(
								'optLoad' => true,
								'nonCrit' => array( 'timeout' => array( 'enable' => false, 'v' => 0 ), 'inl' => true, 'int' => true, 'ext' => true, 'excl' => true, 'items' => array(), ),
								'spec' => array( 'timeout' => array( 'enable' => false, 'v' => 7500, ) ),
							),
							'jsNonCritScopeOvr' => false,
							'cssOvr'=> false,
							'css'=> array( 'nonCrit'=> array( 'auto'=> false ) ),
						)
					),

					'@' => array(
						'enable' => 2,
						'name' => 'Common',
						'urisIncl' => array(),
						'argsIncl' => array(),
						'patterns' => array(),
						'views' => array(),

						'sklSrch' => true,

						'sklExcl' => array(

							'.//br', './/script', './/style', './/link', './/head',
							'.//svg[@width="0"][@height="0"]',
						),

						'sklCssSelExcl' => array(
							'@[\\.#][\\w\\-]*[\\-_]([\\da-f]+)[\\W_]@i',
							'@\\.(?:product_cat|product_tag|category|tag|pa|woocommerce-product-attributes-item--attribute|comment-author)[\\-_]([\\w\\-]+)@i',
						),
					),

					'desktop' => array(
						'enable' => 0,
						'name' => 'Desktop',
						'urisIncl' => array(),
						'argsIncl' => array(),
						'patterns' => array(),
						'views' => array( 'cmn' ),
						'sklSrch' => false,
						'sklExcl' => array(),
						'sklCssSelExcl' => array(),
						'contPr' => array(
							'enable' => true,
							'jsOvr' => true,
							'js' => array(
								'optLoad' => true,
								'nonCrit' => array( 'timeout' => array( 'enable' => true, 'v' => 7500 ), 'inl' => true, 'int' => true, 'ext' => true, 'excl' => true, 'items' => array(), ),
								'spec' => array( 'timeout' => array( 'enable' => false, 'v' => 7500, ) ),
							),
							'jsNonCritScopeOvr' => false,
							'cssOvr'=> false,
							'css'=> array( 'nonCrit'=> array( 'auto'=> false ) ),
						)
					),

				),
			),
		),

		'bots' => array(
			'agents' => array(
				'@\\Wcompatible\\W@i',
				'facebookexternalhit',
				'go-http-client',
				'google-adwords-instant',
				'adsbot-google',
				'googlebot',
				'googleyoutube',
				'ioncrawl',
				'chrome-lighthouse',
				'gtmetrix',
				'rankmathapi',
				'validator.w3.org',
				'zoominfobot',
				'freshpingbot',
				'wordpress/',
				'applebot/',
				'python-requests/',
				'slackbot',
				'uptimemonitor',
				'crawler_eb',
				'@\\s+web\\s+spider\\W@i',
				'dnbcrawler',
				'stormcrawler',
				'@df\\s+bot@',
				'webprosbot',
				'researchoftheweb',
				'siteanalyzerbot',
				'@2ip\\s+bot@',
				'ahrefs',
				'mj12bot',
				'bsbot',
				'okhttp',
				'phxbot',
				'sansanbot',
				'scrapy',
				'researchscan',
			),
		),

		'test' => array(
			'contDelay' => false,
			'contDelayTimeout' => 5000,
			'contExtra' => false,
			'contExtraSize' => 0x80000,
		),

		'hdrTrace' => false,
		'debugInfo' => false,
		'debug' => false,
		'emojiIcons' => false,

		'log' => false,
		'logScope' => array(
			'upd' => false,
			'srvClr' => false,
			'request' => false,
			'requestSkipped' => true,
			'requestSkippedAdmin' => true,
			'requestBots' => true,
		),

		'asyncUseCron' => true,
		'asyncMode' => '',
	) );
}

function GetSiteId( $site = null )
{
	if( !is_multisite() )
		return( 'm' );

	if( !$site )
	{
		$site = get_current_site();
		$site = new AnyObj( array( 'blog_id' => get_current_blog_id(), 'site_id' => $site -> site_id ) );
	}

	if( defined( 'SITE_ID_CURRENT_SITE' ) && defined( 'BLOG_ID_CURRENT_SITE' ) && $site -> blog_id == BLOG_ID_CURRENT_SITE && $site -> site_id == SITE_ID_CURRENT_SITE )
		return( 'm' );

	return( '' . $site -> site_id . '_' . $site -> blog_id );
}

function GetBlogIdFromSiteId( $siteId )
{
	if( $siteId === 'm' )
		return( defined( 'BLOG_ID_CURRENT_SITE' ) ? BLOG_ID_CURRENT_SITE : 0 );

	$nPos = strpos( $siteId, '_' );
	if( $nPos === false )
		return( false );

	return( ( int )substr( $siteId, $nPos + 1 ) );
}

function GetCacheDir()
{
	return( defined( 'SERAPH_ACCEL_CACHE_DIR' ) ? SERAPH_ACCEL_CACHE_DIR : ( WP_CONTENT_DIR . '/cache/seraphinite-accelerator' ) );
}

function GetCacheDataDir( $siteCacheRootPath )
{
	return( $siteCacheRootPath . '/d' );
}

function GetCacheViewsDir( $siteCacheRootPath, $siteSubId = null )
{
	$siteCacheRootPath .= '/v';
	if( $siteSubId )
		$siteCacheRootPath .= '-' . $siteSubId;
	return( $siteCacheRootPath );
}

function GetSalt()
{
	if( defined( 'SERAPH_ACCEL_SALT' ) )
		return( SERAPH_ACCEL_SALT );
	if( defined( 'NONCE_SALT' ) )
		return( NONCE_SALT );
	return( '' );
}

function _GetCacheCurUserSessionHash( $sessionId, $userSessionId, $userId, $expiration )
{
	return( hash_hmac( function_exists( 'hash' ) ? 'sha256' : 'sha1', $sessionId . $userSessionId . $userId . $expiration, AUTH_KEY ) );
}

function GetCacheCurUserSession( $siteId, $defForce = false )
{
	$sessInfoDef = array( 'userId' => '0' );

	if( $defForce )
		return( $sessInfoDef );

	global $seraph_accel_g_sessInfo;

	if( is_array( $seraph_accel_g_sessInfo ) )
		return( $seraph_accel_g_sessInfo );

	$secure = is_ssl();
	$cookie = Gen::SanitizeTextData( (isset($_COOKIE[ ( $secure ? '__Secure-' : '' ) . 'wp_seraph_accel_sess_' . $siteId ])?$_COOKIE[ ( $secure ? '__Secure-' : '' ) . 'wp_seraph_accel_sess_' . $siteId ]:null) );
	if( empty( $cookie ) && $secure )
		$cookie = Gen::SanitizeTextData( (isset($_COOKIE[ 'wp_seraph_accel_sess_' . $siteId ])?$_COOKIE[ 'wp_seraph_accel_sess_' . $siteId ]:null) );
	if( empty( $cookie ) )
		return( $seraph_accel_g_sessInfo = $sessInfoDef );

	$cookie_elements = explode( '|', $cookie );
	if( count( $cookie_elements ) !== 5 )
		return( $seraph_accel_g_sessInfo = $sessInfoDef );

	list( $userSessionId, $sessionId, $userId, $expiration, $hmac ) = $cookie_elements;

	if( $expiration && time() > $expiration )
		return( $seraph_accel_g_sessInfo = $sessInfoDef );

	$hmacCheck = _GetCacheCurUserSessionHash( $sessionId, $userSessionId, $userId, $expiration );
	if( $hmac !== $hmacCheck )
		return( $seraph_accel_g_sessInfo = $sessInfoDef );

	return( $seraph_accel_g_sessInfo = array( 'sessId' => $sessionId, 'userSessId' => $userSessionId, 'userId' => $userId, 'expiration' => $expiration ) );
}

function ShouldCurUserSessionExist()
{
	foreach( $_COOKIE as $cookKey => $cookVal )
	{
		if( strpos( $cookKey, 'wordpress_logged_in_' ) === 0 )
			return( true );

	}

	return( false );
}

function SetCacheCurUserSession( $siteId, $sessionId, $userSessionId, $userId, $expiration )
{
	if( headers_sent() )
		return;

	global $seraph_accel_g_sessInfo;

	$hmac = _GetCacheCurUserSessionHash( $sessionId, $userSessionId, $userId, $expiration );
	$secure = is_ssl();
	Net::SetCookie( ( $secure ? '__Secure-' : '' ) . 'wp_seraph_accel_sess_' . $siteId, $userSessionId . '|' . $sessionId . '|' . $userId . '|' . $expiration . '|' . $hmac, array( 'expires' => Gen::GetCurRequestTime() + YEAR_IN_SECONDS, 'path' => COOKIEPATH, 'domain' => COOKIE_DOMAIN, 'secure' => $secure, 'httponly' => true, 'samesite' => 'Strict' ) );

	$seraph_accel_g_sessInfo = array( 'sessId' => $sessionId, 'userSessId' => $userSessionId, 'userId' => $userId, 'expiration' => $expiration );
}

function _GetDataFileComprExt( $compr, $composite = true )
{

	switch( $compr )
	{
	case 'deflate':
		return( $composite ? '.deflu' : '.gz' );

	case 'brotli':

		return( '.br' );
	}

	return( '' );
}

function _GetDataFileEncExt( $encoding, $composite = true )
{

	switch( $encoding )
	{
	case 'gzip':
	case 'deflate':
	case 'compress':
		return( $composite ? '.deflu' : '.gz' );

	case 'br':

		return( '.br' );
	}

	return( '' );
}

function GetCacheCh( $oiCi, $binary = false )
{
	$res = Gen::GetFileName( $oiCi, true );
	return( $binary ? hex2bin( $res ) : $res );
}

function GetCacheCos( $oiCi )
{
	return( @intval( Gen::GetFileExt( $oiCi ), 16 ) );
}

function _CacheCompDf( $data, $level = -1 )
{

	$ctx = deflate_init( ZLIB_ENCODING_RAW, array( 'level' => $level ) );
	if( $ctx === false )
		return( false );

	$data = deflate_add( $ctx, $data, ZLIB_FULL_FLUSH );
	if( $data === false )
		return( false );

	$blockLast = deflate_add( $ctx, '', ZLIB_FINISH );
	if( $blockLast !== "\x03\0" )
		return( false );

	return( $data );
}

function _CacheCompBr( $data, $level = 11 )
{

	$ctx = Gen::CallFunc( 'brotli_compress_init', array( $level, BROTLI_GENERIC ), false );
	if( !$ctx )
		return( false );

	$blockFirst = brotli_compress_add( $ctx, '', 1 );
	if( $blockFirst !== "\x6b\x00" )
		return( false );

	$data = brotli_compress_add( $ctx, $data, 1 );
	if( $data === false )
		return( false );

	$blockLast = brotli_compress_add( $ctx, '', BROTLI_FINISH );
	if( $blockLast !== "\x03" )
		return( false );

	return( $data );
}

function IsBrotliAvailable()
{
	return( function_exists( 'brotli_compress_init' ) );
}

function CacheCvs( $sz, $szOrig )
{
	return( $sz !== false && ( ( $sz === 0 && $szOrig === 0 ) || ( $sz !== 0 && $szOrig !== 0 ) ) );
}

function IsSrvNotSupportGzAssets()
{

	return( preg_match( '@litespeed@i', (isset($_SERVER[ 'SERVER_SOFTWARE' ])?$_SERVER[ 'SERVER_SOFTWARE' ]:'') ) );
}

function CacheCgf( $settCache, $dataPath, $oiCi, $fileExt, $dataFileExt = '' )
{
	return( $dataPath . '/' . CacheCgif( $settCache, $oiCi ) . '.' . $fileExt . $dataFileExt );
}

function UseGzAssets( $settCache )
{
	return( Gen::GetArrField( $settCache, array( 'useDataComprAssets' ), false ) && !IsSrvNotSupportGzAssets() );
}

function CacheCgif( $settCache, $oiCi )
{
	$oiCif = '';

	$nLvlTotal = 0;
	foreach( Gen::GetArrField( $settCache, array( 'dataLvl' ), array() ) as $nLvl )
	{
		$oiCif .= substr( $oiCi, $nLvlTotal, $nLvl ) . '/';
		$nLvlTotal += $nLvl;
	}

	$oiCif .= substr( $oiCi, $nLvlTotal );
	return( $oiCif );
}

function CacheCw( $settCache, $siteRootPath, $dataPath, $composite, $content, $type, $fileExt = null )
{
	if( !$fileExt )
		$fileExt = $type;

	$oiCi = @md5( $content ) . '.' . sprintf( '%x', strlen( $content ) );
	$oiCif = CacheCgif( $settCache, $oiCi );

	$dataComprs = Gen::GetArrField( $settCache, array( 'dataCompr' ), array() );
	if( empty( $dataComprs ) )
		$dataComprs[] = '';

	if( $type != 'html' )
	{
		$dataPath .= '/' . $type;
		if( !$composite )
		{
			if( ( $type != 'css' && $type != 'js' ) || !UseGzAssets( $settCache ) )
				$dataComprs = array( '' );
			else if( !in_array( '', $dataComprs, true ) )
				$dataComprs[] = '';
		}
	}

	$writeOk = true;
	$writeFailDsc = null;

	foreach( $dataComprs as $dataCompr )
	{
		$dataFileExt = _GetDataFileComprExt( $dataCompr, $composite );
		if( $dataFileExt === null )
		{
			$writeOk = false;
			Gen::LastErrDsc_Set( LocId::Pack( 'DataComprUnsupp_%1$s', null, array( $dataCompr ) ) );
			break;
		}

		$oiCf = $dataPath . '/' . $oiCif . '.' . $fileExt . $dataFileExt;

		$lock = new Lock( $oiCf . '.l', false, true );
		if( !$lock -> Acquire() )
		{
			$writeOk = false;
			Gen::LastErrDsc_Set( $lock -> GetErrDescr() );
			break;
		}

		if( CacheCvs( Gen::FileSize( $oiCf ), strlen( $content ) ) )
		{
			$lock -> Release();
			continue;
		}

		$oiCfTmp = $oiCf . '.tmp';
		Gen::FileOpenWithMakeDir( $chunkFileStm, $oiCfTmp, 'wb' );
		if( $chunkFileStm )
		{
			{
				$contentWrite = false;
				switch( $dataCompr )
				{
				case '':
					$contentWrite = $content;
					break;

				case 'deflate':
					$contentWrite = ( $dataFileExt == '.gz' ) ? @gzencode( $content, 9 ) : _CacheCompDf( $content, 9 );
					break;

				case 'brotli':
					$contentWrite = ( $dataFileExt == '.br' ) ? Gen::CallFunc( 'brotli_compress', array( $content, 11 ), false ) : _CacheCompBr( $content, 11 );
					break;
				}

				if( $contentWrite === false || $contentWrite === null )
				{
					$writeOk = false;
					Gen::LastErrDsc_Set( LocId::Pack( 'DataComprErr_%1$s', null, array( $dataCompr ) ) );
				}
				else if( @fwrite( $chunkFileStm, $contentWrite ) === false )
				{
					$writeOk = false;
					Gen::LastErrDsc_Set( LocId::Pack( 'FileWriteErr_%1$s', 'Common', array( $oiCfTmp ) ) );
				}

				unset( $contentWrite );
			}

			@fclose( $chunkFileStm );

			if( $writeOk && !@rename( $oiCfTmp, $oiCf ) )
			{
				$writeOk = false;
				Gen::LastErrDsc_Set( LocId::Pack( 'FileRenameErr_%1$s%2$s', 'Common', array( $oiCfTmp, $oiCf ) ) );
			}
		}
		else
		{
			$writeOk = false;
			Gen::LastErrDsc_Set( LocId::Pack( 'FileWriteErr_%1$s', 'Common', array( $oiCfTmp ) ) );
		}

		if( !$writeOk )
		{
			@unlink( $oiCfTmp );
			@unlink( $oiCf );
			$lock -> Release();
			break;
		}

		$lock -> Release();
	}

	if( !$writeOk )
		return( null );

	if( $siteRootPath !== null && strpos( $dataPath, $siteRootPath . '/' ) === 0 )
		$relFilePath = substr( $dataPath, strlen( $siteRootPath ) + 1 ) . '/';
	else
		$relFilePath = '';
	$relFilePath .= $oiCif . '.' . $fileExt;

	return( array( 'id' => $oiCi, 'relFilePath' => $relFilePath ) );
}

function CacheCc( $settCache, $siteRootPath, $dataPath, $oiCi, $type, $fileExt = null )
{
	if( !$fileExt )
		$fileExt = $type;

	$oiCif = CacheCgif( $settCache, $oiCi );

	if( $type != 'html' )
		$dataPath .= '/' . $type;

	$oiCf = $dataPath . '/' . $oiCif . '.' . $fileExt;

	$lock = new Lock( $oiCf . '.l', false, true );
	if( !$lock -> Acquire() )
	{
		Gen::LastErrDsc_Set( $lock -> GetErrDescr() );
		return( null );
	}

	$readOk = @file_exists( $oiCf );

	$lock -> Release();

	if( !$readOk )
		return( null );

	if( $siteRootPath !== null && strpos( $dataPath, $siteRootPath . '/' ) === 0 )
		$relFilePath = substr( $dataPath, strlen( $siteRootPath ) + 1 ) . '/';
	else
		$relFilePath = '';
	$relFilePath .= $oiCif . '.' . $fileExt;

	return( array( 'id' => $oiCi, 'relFilePath' => $relFilePath ) );
}

function _ContentCw( &$dsc, $data, $type, $settCache, $dataPath )
{
	if( !$data )
		return( true );

	$oiC = CacheCw( $settCache, null, $dataPath, true, $data, $type );
	if( !$oiC )
		return( false );

	$dsc[ 'p' ][] = $oiC[ 'id' ];
	return( true );
}

function iadkxsejfuijdujdes_( &$content, $type, &$dsc, $contSep, $contSepLen, $settCache, $dataPath )
{
	$writeOk = true;

	$contentChunkOffset = 0;
	$contentChunkSearchOffset = 0;
	$contentChunkWrite = '';

	for( ;; )
	{
		$contentChunkOffsetNext = strpos( $content, $contSep, $contentChunkSearchOffset );

		if( $contentChunkOffsetNext === false )
		{
			if( !$contentChunkOffset )
				$contentChunk = $content;
			else
				$contentChunk = substr( $content, $contentChunkOffset );
		}
		else
		{
			$content = substr( $content, 0, $contentChunkOffsetNext ) . substr( $content, $contentChunkOffsetNext + $contSepLen );

			$contentChunk = substr( $content, $contentChunkOffset, $contentChunkOffsetNext - $contentChunkOffset );
			$contentChunkSearchOffset = $contentChunkOffsetNext;

			$contentChunkOffset = $contentChunkOffsetNext;
		}

		if( strlen( $contentChunkWrite ) >= 2048 || strlen( $contentChunk ) >= 2048 )
		{
			$writeOk = _ContentCw( $dsc, $contentChunkWrite, $type, $settCache, $dataPath );
			$contentChunkWrite = '';
		}

		$contentChunkWrite .= $contentChunk;
		$contentChunk = null;

		if( $contentChunkOffsetNext === false || !$writeOk )
			break;
	}

	if( $writeOk )
		$writeOk = _ContentCw( $dsc, $contentChunkWrite, $type, $settCache, $dataPath );

	return( $writeOk );
}

function CacheReadDsc( $filePath )
{
	return( @unserialize( @file_get_contents( $filePath ) ) );
}

function CacheDscUpdate( $lock, $settCache, $content, $deps, $subParts, $dataPath, $tmp = false, $origContHash = null, $learnId = null )
{
	global $seraph_accel_g_dscFile;
	global $seraph_accel_g_dscFilePending;

	$dsc = array( 'p' => array() );

	$writeOk = true;

	{
		$contTmpTimeStampBegin = '<!-- seraph-accel-tmpTimeStamp: ';
		if( $tmp )
			$content .= $contTmpTimeStampBegin . Gen::GetCurRequestTime() . ' -->';
		else if( $contTmpTimeStampBeginPos = strrpos( $content, $contTmpTimeStampBegin ) )
			$content = substr( $content, 0, $contTmpTimeStampBeginPos );
		unset( $contTmpTimeStampBegin, $contTmpTimeStampBeginPos );
	}

	if( !$tmp )
	{
		$contSep = ContentMarkGetSep();
		$contSepLen = strlen( $contSep );

		$contentPartOffset = 0;
		$contentPartSearchOffset = 0;
		$contentPartWrite = '';

		if( $subParts )
			foreach( $subParts as $idSubPart => $contentPart )
			{
				if( !$contentPart )
					continue;

				$subPartType = Gen::GetFileExt( $idSubPart );
				$idSubPart = Gen::GetFileName( $idSubPart, true );

				$dscPart = array( 'p' => array() );

				$writeOk = Gen::GetArrField( $settCache, array( 'chunks', 'enable' ) ) ? iadkxsejfuijdujdes_( $contentPart, $subPartType, $dscPart, $contSep, $contSepLen, $settCache, $dataPath ) : _ContentCw( $dscPart, $contentPart, $subPartType, $settCache, $dataPath );
				if( !$writeOk )
					break;

				$dscPart[ 'c' ] = pack( 'V', crc32( $contentPart ) );
				$dscPart[ 'a' ] = hash( 'adler32', $contentPart, true );

				if( !isset( $dsc[ 'b' ] ) )
					$dsc[ 'b' ] = array();
				$dsc[ 'b' ][ $idSubPart ] = $dscPart;
			}

		if( $writeOk )
			$writeOk = Gen::GetArrField( $settCache, array( 'chunks', 'enable' ) ) ? iadkxsejfuijdujdes_( $content, 'html', $dsc, $contSep, $contSepLen, $settCache, $dataPath ) : _ContentCw( $dsc, $content, 'html', $settCache, $dataPath );
	}
	else

		$writeOk = _ContentCw( $dsc, $content, 'html', $settCache, $dataPath );

	$dsc[ 'c' ] = pack( 'V', crc32( $content ) );
	$dsc[ 'a' ] = hash( 'adler32', $content, true );

	if( $deps )
		$dsc[ 's' ] = $deps;

	if( $tmp )
		$dsc[ 't' ] = true;
	else if( $learnId )
		$dsc[ 'l' ] = $learnId;
	if( $origContHash )
		$dsc[ 'h' ] = $origContHash;

	if( !$lock -> Acquire() )
		return( null );

	if( $writeOk )
	{
		$dscOld = CacheReadDsc( $seraph_accel_g_dscFile );

		if( $tmp && $dscOld && isset( $dscOld[ 'b' ] ) )
			$dsc[ 'b' ] = $dscOld[ 'b' ];

		{
			if( !$tmp && $dscOld && (isset($dscOld[ 't' ])?$dscOld[ 't' ]:null) )
				$hdrs = (isset($dscOld[ 'hd' ])?$dscOld[ 'hd' ]:null);
			else
				$hdrs = GetCurHdrsToStoreInCache( $settCache );

			if( $hdrs )
				$dsc[ 'hd' ] = $hdrs;
		}

		$dscFileNew = $seraph_accel_g_dscFile . '.new';

		if( @file_put_contents( $dscFileNew, @serialize( $dsc ) ) === false )
		{
			Gen::LastErrDsc_Set( LocId::Pack( 'FileWriteErr_%1$s', 'Common', array( $dscFileNew ) ) );
			$writeOk = false;
		}

		if( $writeOk )
		{
			$preservedFileTime = @filemtime( $seraph_accel_g_dscFile );
			if( $preservedFileTime === false )
			{
				if( $tmp )
					$preservedFileTime = 5;
			}
			else if( $preservedFileTime > 0 && !$tmp )
				$preservedFileTime = false;

			if( $preservedFileTime !== false && !@touch( $dscFileNew, $preservedFileTime ) )
			{
				Gen::LastErrDsc_Set( LocId::Pack( 'FileModifyErr_%1$s', 'Common', array( $dscFileNew ) ) );
				$writeOk = false;
			}

			if( $writeOk && !@rename( $dscFileNew, $seraph_accel_g_dscFile ) )
			{
				Gen::LastErrDsc_Set( LocId::Pack( 'FileRenameErr_%1$s%2$s', 'Common', array( $dscFileNew, $seraph_accel_g_dscFile ) ) );
				$writeOk = false;
			}
			else if( !$tmp && $dscOld && (isset($dscOld[ 't' ])?$dscOld[ 't' ]:null) )
				if( $oiCi = Gen::GetArrField( $dscOld, array( 'p', 0 ) ) )
					foreach( glob( $dataPath . '/' . $oiCi . '.html*', GLOB_NOSORT ) as $file )
						@unlink( $file );
		}

		if( !$writeOk )
		{

			@unlink( $dscFileNew );
		}
	}

	if( $tmp !== 'u' )
		$ulr = @unlink( $seraph_accel_g_dscFilePending );
	if( !$tmp && Gen::StrEndsWith( $seraph_accel_g_dscFilePending, '.pp' ) )
		@unlink( substr( $seraph_accel_g_dscFilePending, 0, -1 ) );

	$lock -> Release();

	return( $writeOk ? $dsc : null );
}

function IsCronEnabled()
{
	return( !( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) );
}

function CacheDoesCronDelayPageLoad()
{
	if( !IsCronEnabled() )
		return( false );
	if( Gen::IsRequestSessionsCanBeClosedForContinueBgWork() )
		return( false );
	return( true );
}

function CacheDoCronAndEndRequest()
{
	global $seraph_accel_g_prepPrms;

	if( $seraph_accel_g_prepPrms !== null || !IsCronEnabled() || !Gen::GetArrField( Plugin::SettGet(), array( 'cache', 'cron' ), false ) )
		return( null );

	if( !Gen::CloseCurRequestSessionForContinueBgWork() )
		return( false );

	add_action( 'wp_loaded', function() { if( Wp::GetFilters( 'init', 'wp_cron' ) ) wp_cron(); exit(); }, -999999 );
	return( true );
}

function GetContentProcessorForce( $sett )
{
	if( !(isset($sett[ 'debug' ])?$sett[ 'debug' ]:null) )
		return( null );

	if( isset( $_REQUEST[ 'seraph_accel_proc' ] ) )
		return( Gen::SanitizeTextData( $_REQUEST[ 'seraph_accel_proc' ] ) );

	if( isset( $_COOKIE[ 'seraph_accel_proc' ] ) )
		return( Gen::SanitizeTextData( $_COOKIE[ 'seraph_accel_proc' ] ) );

	return( null );
}

function ApplyContentProcessorForceSett( &$sett, $settContPrOverride )
{
	if( !$settContPrOverride )
		return;

	if( $settContPrOverride === 'LRNSTR' )
	{
		$sett[ 'contPr' ] = array( 'enable' => true, 'grps' => Gen::GetArrField( $sett, array( 'contPr', 'grps' ), array() ) );
		return;
	}

	if( $settContPrOverride === '1' )
		return;

	$settContPrOverride = @json_decode( @base64_decode( $settContPrOverride ), true );
	if( is_array( $settContPrOverride ) )
	{
		$settContPrOverride[ 'enable' ] = true;
		$sett[ 'contPr' ] = $settContPrOverride;
	}
}

function GetCacheSessionSrvState( $dirSesss )
{
	$ctx = array( 'tmLast' => 0, 'stateId' => '' );

	Gen::DirEnum( $dirSesss . '/st_s', $ctx,
		function( $path, $file, &$ctx )
		{
			if( @filemtime( $path . '/' . $file ) > $ctx[ 'tmLast' ] )
				$ctx[ 'stateId' ] = $file;
		}
	);

	return( $ctx[ 'stateId' ] );
}

function SetCacheSessionSrvState( $dirSesss, $stateId )
{
	$dirSesss = $dirSesss . '/st_s';

	$hr = Gen::MakeDir( $dirSesss );
	if( Gen::HrFail( $hr ) )
		return( $hr );

	@touch( $dirSesss . '/' . $stateId );

	$ctx = array( 'stateId' => $stateId );

	if( !Gen::DirEnum( $dirSesss, $ctx,
		function( $path, $file, &$ctx )
		{
			if( $file != $ctx[ 'stateId' ] )
				@unlink( $path . '/' . $file );
		}
	) )
	{
		return( Gen::E_FAIL );
	}

	return( Gen::S_OK );
}

const TOF_COMPR_MAX	= 9;

function Tof_GetFileData( $dir, $id, $ver = null, $compressed = false, $oiCi = null )
{
	if( $oiCi !== null )
		$id = $oiCi . '.' . $id;

	$data = @file_get_contents( $dir . '/' . $id );
	if( $compressed )
		$data = @gzdecode( $data );
	$data = @unserialize( $data );

	if( !is_array( $data ) )
		$data = array();

	if( $ver )
	{
		$vFrom = (isset($data[ 'v' ])?$data[ 'v' ]:null);
		if( is_array( $ver ) )
		{
			if( $ver[ 0 ] !== $vFrom )
				$data = call_user_func( $ver[ 1 ], $data, $vFrom );
		}
		else if( $ver !== $vFrom )
			$data = array();
	}

	return( $data );
}

function Tof_SetFileData( $dir, $id, $data, $ver = null, $tmp = false, $compressed = false, &$oiCi = null )
{
	$hr = Gen::MakeDir( $dir, true );
	if( Gen::HrFail( $hr ) )
		return( $hr );

	if( $ver )
		$data[ 'v' ] = $ver;

	$data = @serialize( $data );

	if( $oiCi !== null )
	{
		$oiCi = @md5( $data ) . '.' . sprintf( '%x', strlen( $data ) );
		$id = $oiCi . '.' . $id;

		if( @file_exists( $dir . '/' . $id ) )
			return( Gen::S_FALSE );
	}

	$fileTmp = tempnam( $dir, '' );
	if( !$fileTmp )
		return( Gen::E_FAIL );

	if( $compressed !== false )
		$data = @gzencode( $data, is_int( $compressed ) ? $compressed : -1 );

	if( @file_put_contents( $fileTmp, $data ) === false )
	{
		@unlink( $fileTmp );
		return( Gen::E_FAIL );
	}

	if( !@rename( $fileTmp, $tmp ? ( $fileTmp . '.' . $id ) : ( $dir . '/' . $id ) ) )
	{
		@unlink( $fileTmp );
		return( Gen::E_FAIL );
	}

	return( Gen::S_OK );
}

function _ContentMarkSeparate_CreateSepElem( $doc )
{

	$item = $doc -> createElement( 'script' );
	$item -> setAttribute( 'type', 'text/seraph-accel-cont-sep' );

	return( $item );
}

function ContentMarkSeparate( $item, $checkSize = true, $side = 0xFF )
{
	if( $checkSize && strlen( $item -> nodeValue ) < 2048 )
		return;

	$doc = $item -> ownerDocument;
	$parentNode = $item -> parentNode;

	if( $side & 1 )
		HtmlNd::InsertBefore( $parentNode, _ContentMarkSeparate_CreateSepElem( $doc ), $item );
	if( $side & 2 )
		HtmlNd::InsertAfter( $parentNode, _ContentMarkSeparate_CreateSepElem( $doc ), $item );
}

function ContentMarkGetSep()
{
	$doc = new \DOMDocument();
	$sep = _ContentMarkSeparate_CreateSepElem( $doc );
	return( $doc -> saveHTML( $sep ) );
}

function ContentMarkSeparate_CreateLazyPartSepElem( $doc, $id )
{
	$item = $doc -> createElement( 'script' );
	$item -> setAttribute( 'type', 'text/seraph-accel-lazypart-sep' );
	$item -> setAttribute( 'id', $id );
	return( $item );
}

function ContentMarkGetPartSep( $id )
{
	$doc = new \DOMDocument();
	$sep = ContentMarkSeparate_CreateLazyPartSepElem( $doc, $id );
	return( $doc -> saveHTML( $sep ) );
}

function ContentMarkSeparateSofter( &$content, $hide = true )
{
	$content = str_replace( ContentMarkGetSep(), $hide ? '' : '<!-- seraph-accel-cont-sep -->', $content );
}

function GetCacheSiteIdAdjustPath( $sites, &$addr, &$siteSubId, &$path )
{
	if( !is_array( $sites ) )
		return( null );

	if( $path )
		$addr .= '/' . $path;

	$addrSite = $addr;
	for( ;; )
	{
		$id = (isset($sites[ $addrSite ])?$sites[ $addrSite ]:null);
		if( $id )
		{
			$path = ltrim( substr( $addr, strlen( $addrSite ) ), '/' );

			$posSubSite = strpos( $id, '-' );
			if( $posSubSite !== false )
			{
				$siteSubId = substr( $id, $posSubSite + 1 );
				$id = substr( $id, 0, $posSubSite );
			}

			$addr = $addrSite;
			return( $id );
		}

		$addrSiteNext = dirname( $addrSite );
		if( $addrSiteNext === $addrSite )
			break;
		$addrSite = $addrSiteNext;
		unset( $addrSiteNext );
	}

	return( null );
}

function GetSiteIds()
{
	static $aIds;

	if( $aIds !== null )
		return( $aIds );

	global $seraph_accel_sites;

	$aIds = array();
	foreach( ( array )$seraph_accel_sites as $id )
	{
		$posSubSite = strpos( $id, '-' );
		if( $posSubSite !== false )
			$id = substr( $id, 0, $posSubSite );
		$aIds[ $id ] = true;
	}

	return( $aIds = array_keys( $aIds ) );
}

function CachePathNormalize( $path, &$pathIsDir, $bLwr = true )
{
	if( $path == '/' )
	{
		$pathIsDir = true;
		$path = '';
	}
	else
	{
		$path = ltrim( $path, '/' );
		if( $bLwr )
			$path = strtolower( $path );

		if( substr( $path, -1 ) == '/' )
		{
			$pathIsDir = true;
			$path = rtrim( $path, '/' );
		}
	}

	return( Gen::GetNormalizedPath( $path ) );
}

function ParseContCachePathArgs( $serverArgs, &$args )
{

	$path = (isset($serverArgs[ 'REQUEST_URI' ])?$serverArgs[ 'REQUEST_URI' ]:null);
	$posQuery = strpos( $path, '?' );

	if( $posQuery !== false )
	{
		$queryOrig = substr( $path, $posQuery + 1 );
		if( $args === null || $queryOrig != (isset($serverArgs[ 'QUERY_STRING' ])?$serverArgs[ 'QUERY_STRING' ]:null) )
		{
			$args = array();
			@parse_str( $queryOrig, $args );
		}

		$path = substr( $path, 0, $posQuery );
	}
	else
		$args = array();

	return( $path );
}

function GetContCacheEarlySkipData( &$pathOrig = null , &$path = null , &$pathIsDir = null , &$args = null  )
{
	global $seraph_accel_g_cacheSkipData;

	if( $seraph_accel_g_cacheSkipData !== null )
		return( $seraph_accel_g_cacheSkipData );

	$seraph_accel_g_cacheSkipData = false;

	if( defined( 'SID' ) && SID != '' )
		$seraph_accel_g_cacheSkipData = array( 'skipped', array( 'reason' => 'sid' ) );
	else if( is_admin() )
		$seraph_accel_g_cacheSkipData = array( 'skipped', array( 'reason' => 'admin' ) );
	else if( defined( 'DOING_CRON' ) || isset( $_REQUEST[ 'doing_wp_cron' ] ) )
		$seraph_accel_g_cacheSkipData = array( 'skipped', array( 'reason' => 'cron' ) );
	else if( defined( 'XMLRPC_REQUEST' ) )
		$seraph_accel_g_cacheSkipData = array( 'skipped', array( 'reason' => 'xmlrpc' ) );
	else
	{
		$pathOrig = ParseContCachePathArgs( $_SERVER, $args );
		$path = CachePathNormalize( $pathOrig, $pathIsDir );

		if( strpos( $path, 'robots.txt' ) !== false )
			$seraph_accel_g_cacheSkipData = array( 'skipped', array( 'reason' => 'robots' ) );
		else if( strpos( $path, '.htaccess' ) !== false )
			$seraph_accel_g_cacheSkipData = array( 'skipped', array( 'reason' => 'htaccess' ) );
		else if( strpos( '/' . $path, '/wp-' ) !== false )
			$seraph_accel_g_cacheSkipData = array( 'skipped', array( 'reason' => 'wpUrl' ) );
		else if( isset( $_SERVER[ 'HTTP_LINGUISE_ORIGINAL_LANGUAGE' ] ) )
			$seraph_accel_g_cacheSkipData = array( 'skipped', array( 'reason' => 'linguiseGetOrig' ) );
	}

	return( $seraph_accel_g_cacheSkipData );
}

function lfjikztqjqji( $siteId, $tmCur, $inc = true )
{
	if( Plugin::IsApiRequest() )
		return( null );

	$statPath = GetCacheDir() . '/s/' . $siteId . '/st';
	$imuyluwqfjqji = Tof_GetFileData( $statPath, 'pv' );

	if( !(isset($imuyluwqfjqji[ 'ts' ])?$imuyluwqfjqji[ 'ts' ]:null) )
		$imuyluwqfjqji[ 'ts' ] = $tmCur;
	if( !(isset($imuyluwqfjqji[ 'n' ])?$imuyluwqfjqji[ 'n' ]:null) )
		$imuyluwqfjqji[ 'n' ] = 0;

	if( $tmCur - $imuyluwqfjqji[ 'ts' ] > 60 * 60 * 24 * 30 )
	{
		$imuyluwqfjqji[ 'ts' ] = $tmCur;
		$imuyluwqfjqji[ 'n' ] = 0;
	}

	if( $inc )
	{

		$imuyluwqfjqji[ 'n' ]++;

		if( Gen::HrFail( Tof_SetFileData( $statPath, 'pv', $imuyluwqfjqji ) ) )
			return( 'errUpdateStat' );
	}

	if( $imuyluwqfjqji[ 'n' ] > 5000 )
		return( 'outOfLimits' );

	return( null );
}

function _NormalizeExclPath( &$path )
{
	if( strlen( $path ) > 1 && $path[ 0 ] === '/' )
		$path = substr( $path, 1 );
}

function IsUriByPartsExcluded( $settCache, $path, $args )
{
	if( is_string( $args ) )
		$args = Net::UrlParseQuery( $args );

	_NormalizeExclPath( $path );
	return( !!_ContProcGetExclStatus( $settCache, Gen::GetArrField( $settCache, array( 'ctxGrps' ), array() ), null, null, $path, $path, $args ) );
}

function CheckPathInUriList( $a, $path, $pathOrig = null )
{
	if( $pathOrig === null )
		$pathOrig = $path;

	foreach( $a as $aa )
	{
		_NormalizeExclPath( $aa );

		$matched = false;
		foreach( ExprConditionsSet_Parse( $aa ) as $e )
		{
			$isRegExp = IsStrRegExp( $e[ 'expr' ] );

			if( $isRegExp )
			{
				if( @preg_match( $e[ 'expr' ], $pathOrig, $valFound ) )
					$valFound = count( $valFound ) > 1 ? $valFound[ 1 ] : $valFound[ 0 ];
				else
					$valFound = '';
			}
			else if( strpos( $path, $e[ 'expr' ] ) === 0 )
				$valFound = $path;
			else
				$valFound = '';

			$matched = ExprConditionsSet_ItemOp( $e, $valFound );
			if( !$matched )
				break;
		}

		if( $matched )
			return( $aa );
	}

	return( null );
}

function _ContProcGetExclStatus( $settCache, $ctxGrps, $userAgent, $cookies, $path, $pathOrig, &$args, $adjustArgs = false )
{

	if( !empty( $args ) )
	{
		if( Gen::GetArrField( $settCache, array( 'exclArgsAll' ), true ) )
			return( 'exclArgsAll' );

		if( Gen::GetArrField( $settCache, array( 'skipArgsAll' ), false ) )
			$args = array();

		$exclArgs = Gen::GetArrField( $settCache, array( 'exclArgs' ), array() );
		$skipArgs = Gen::GetArrField( $settCache, array( 'skipArgs' ), array() );
		foreach( $args as $argKey => $argVal )
		{
			$argKeyCmp = strtolower( $argKey );

			foreach( $exclArgs as $a )
				if( _ContProcGetExclStatus_KeyValMatch( $a, $argKeyCmp, $argVal ) )
					return( 'exclArgs:' . $a );

			foreach( $ctxGrps as $ctxGrp )
			{
				if( !(isset($ctxGrp[ 'enable' ])?$ctxGrp[ 'enable' ]:null) )
					continue;

				$ctxArgs = Gen::GetArrField( $ctxGrp, array( 'args' ), array() );
				foreach( $ctxArgs as $a )
					if( strpos( $argKeyCmp, $a ) === 0 )
						return( 'userCtxArgs:' . $a );
			}

			if( $adjustArgs )
				foreach( $skipArgs as $a )
					if( _ContProcGetExclStatus_KeyValMatch( $a, $argKeyCmp, $argVal ) )
						unset( $args[ $argKey ] );
		}
	}

	if( $uriExcl = CheckPathInUriList( Gen::GetArrField( $settCache, array( 'urisExcl' ), array() ), $path, $pathOrig ) )
		return( 'urisExcl:' . $uriExcl );

	if( $userAgent )
		foreach( Gen::GetArrField( $settCache, array( 'exclAgents' ), array() ) as $e )
			if( strpos( $userAgent, $e ) !== false )
				return( 'exclAgents:' . $e );

	$exclCookies = Gen::GetArrField( $settCache, array( 'exclCookies' ), array() );
	if( $exclCookies && $cookies )
		foreach( $cookies as $cookKey => $cookVal )
			foreach( $exclCookies as $e )
				if( strpos( $cookKey, $e ) === 0 )
					return( 'exclCookies:' . $e );

	return( false );
}

function _ContProcGetExclStatus_KeyValMatch( $expr, $k, $v, $sep = '=' )
{
	$found = false;
	foreach( ExprConditionsSet_Parse( $expr ) as $e )
	{
		$val = false;
		if( IsStrRegExp( $e[ 'expr' ] ) )
		{
			if( @preg_match( $e[ 'expr' ] . 'S', $k . $sep . $v, $m ) )
				$val = count( $m ) > 1 ? $m[ 1 ] : $m[ 0 ];
		}
		else if( Gen::StrStartsWith( $k, $e[ 'expr' ] ) )
			$val = true;

		if( !ExprConditionsSet_ItemOp( $e, $val ) )
			return( false );

		$found = true;
	}

	return( $found );
}

function ContProcGetExclStatus( $siteId, $settCache, $path, $pathOrig, $pathIsDir, &$args, &$varsOut, $adjustArgs = true, $adjustStat = true )
{
	global $seraph_accel_g_contProcGetExclStatus;
	global $seraph_accel_g_prepContIsUserCtx;
	global $seraph_accel_g_cacheCtxSkip;

	if( $seraph_accel_g_contProcGetExclStatus !== null )
		return( $seraph_accel_g_contProcGetExclStatus );

	$tmCur = Gen::GetCurRequestTime();

	$varsOut = array();

	$userAgent = strtolower( (isset($_SERVER[ 'HTTP_USER_AGENT' ])?$_SERVER[ 'HTTP_USER_AGENT' ]:'') );

	$varsOut[ 'tmCur' ] = $tmCur;
	$varsOut[ 'userAgent' ] = $userAgent;

	$ctxGrps = Gen::GetArrField( $settCache, array( 'ctxGrps' ), array() );

	if( $pathIsDir )
		$path .= '/';
	$seraph_accel_g_contProcGetExclStatus = _ContProcGetExclStatus( $settCache, $ctxGrps, $userAgent, $_COOKIE, $path, $pathOrig, $args, $adjustArgs );
	if( $seraph_accel_g_contProcGetExclStatus )
		return( $seraph_accel_g_contProcGetExclStatus );

	$sessInfo = GetCacheCurUserSession( $siteId, $seraph_accel_g_cacheCtxSkip );

	$userId = (isset($sessInfo[ 'userId' ])?$sessInfo[ 'userId' ]:null);

	$varsOut[ 'sessInfo' ] = $sessInfo;
	$varsOut[ 'userId' ] = $userId;

	if( !$seraph_accel_g_cacheCtxSkip )
	{
		$stateCookId = GetCookiesState( $ctxGrps, $_COOKIE );

		if( $stateCookId || $userId )
		{

			$seraph_accel_g_prepContIsUserCtx = true;
			if( !Gen::GetArrField( $settCache, array( 'ctx' ), false ) )
				$seraph_accel_g_contProcGetExclStatus = 'userCtx';
		}
	}
	else
	{
		$stateCookId = '';

		$viewsGrps = Gen::GetArrField( $settCache, array( 'viewsGrps' ), array() );

		foreach( array_keys( $_COOKIE ) as $cookKey )
		{
			$viewStateIdProbe = '';
			if( (isset($settCache[ 'views' ])?$settCache[ 'views' ]:null) )
				foreach( $viewsGrps as $viewsGrp )
					if( (isset($viewsGrp[ 'enable' ])?$viewsGrp[ 'enable' ]:null) )
						AccomulateCookiesState( $viewStateIdProbe, array( $cookKey => $_COOKIE[ $cookKey ] ), Gen::GetArrField( $viewsGrp, array( 'cookies' ), array() ) );

			if( !strlen( $viewStateIdProbe ) )
				unset( $_COOKIE[ $cookKey ] );
		}
	}

	$varsOut[ 'stateCookId' ] = $stateCookId;

	$shouldCurUserSessionExist = ShouldCurUserSessionExist();
	if( ( !(isset($sessInfo[ 'sessId' ])?$sessInfo[ 'sessId' ]:null) && ( $shouldCurUserSessionExist || $stateCookId ) ) || ( $shouldCurUserSessionExist && !$userId ) )
		$seraph_accel_g_contProcGetExclStatus = Gen::GetArrField( $settCache, array( 'ctx' ), false ) ? 'noCacheSession' : 'userCtx';

	return( $seraph_accel_g_contProcGetExclStatus );
}

function IsStrRegExp( $s )
{
	return( strpos( '/~@;%`#', (isset($s[ 0 ])?$s[ 0 ]:null) ) !== false );
}

function ExprConditionsSet_Parse( $expr )
{
	$a = explode( ' & ', $expr );
	foreach( $a as &$e )
	{
		$e = trim( $e );

		if( ( $posVal = strpos( $e, ' != ' ) ) !== false )
			$e = array( 'expr' => trim( substr( $e, 0, $posVal ) ), 'op' => 'ne', 'v' => trim( substr( $e, $posVal + 4 ) ) );
		else if( ( $posVal = strpos( $e, ' = ' ) ) !== false )
			$e = array( 'expr' => trim( substr( $e, 0, $posVal ) ), 'op' => 'e', 'v' => trim( substr( $e, $posVal + 3 ) ) );
		else if( ( $posVal = strpos( $e, ' < ' ) ) !== false )
			$e = array( 'expr' => trim( substr( $e, 0, $posVal ) ), 'op' => '<', 'v' => trim( substr( $e, $posVal + 3 ) ) );
		else if( ( $posVal = strpos( $e, ' > ' ) ) !== false )
			$e = array( 'expr' => trim( substr( $e, 0, $posVal ) ), 'op' => '>', 'v' => trim( substr( $e, $posVal + 3 ) ) );
		else if( ( $posVal = strpos( $e, ' >= ' ) ) !== false )
			$e = array( 'expr' => trim( substr( $e, 0, $posVal ) ), 'op' => '>=', 'v' => trim( substr( $e, $posVal + 4 ) ) );
		else if( ( $posVal = strpos( $e, ' <= ' ) ) !== false )
			$e = array( 'expr' => trim( substr( $e, 0, $posVal ) ), 'op' => '<=', 'v' => trim( substr( $e, $posVal + 4 ) ) );
		else if( Gen::StrStartsWith( $e, '!' ) )
			$e = array( 'expr' => trim( substr( $e, 1 ) ), 'op' => 'v' );
		else
			$e = array( 'expr' => $e, 'op' => '' );
	}

	return( $a );
}

function ExprConditionsSet_ItemOp( $e, $v )
{
	switch( $e[ 'op' ] )
	{
	case 'ne':		return( $v !== $e[ 'v' ] );
	case 'e':		return( $v === $e[ 'v' ] );
	case 'v':		return( !strlen( $v ) );

	case '<':
	case '>':
	case '>=':
	case '<=':		return( strlen( $v ) ? @version_compare( $v, $e[ 'v' ], $e[ 'op' ] ) : false );
	}

	return( !!strlen( $v ) );
}

function ExprConditionsSet_IsItemOpFullSearch( $e )
{
	switch( $e[ 'op' ] )
	{
	case 'ne':
	case 'e':		return( true );

	case '<':
	case '>':
	case '>=':
	case '<=':		return( true );
	}

	return( false );
}

function ExprConditionsSet_IsTrivial( $ee )
{
	return( count( $ee ) == 1 && $ee[ 0 ][ 'op' ] === '' );
}

function AccomulateCookiesState( &$state, $cookies, $elems )
{
	foreach( $elems as $ee )
	{
		$statePart = '';
		foreach( ExprConditionsSet_Parse( $ee ) as $e )
		{
			$isRegExp = IsStrRegExp( $e[ 'expr' ] );

			$cookieVals = array();
			foreach( $cookies as $cookKey => $cookVal )
				if( $isRegExp ? @preg_match( $e[ 'expr' ], $cookKey ) : Gen::StrStartsWith( $cookKey, $e[ 'expr' ] ) )
					$cookieVals[] = $cookVal;
			if( !$cookieVals )
				$cookieVals = array( '' );

			$found = false;
			foreach( $cookieVals as $cookVal )
			{
				if( ExprConditionsSet_ItemOp( $e, $cookVal ) )
				{
					$statePart .= strlen( $cookVal ) ? $cookVal : '!';
					$found = true;
				}
			}

			if( !$found )
			{
				$statePart = '';
				break;
			}
		}

		$state .= $statePart;
	}
}

function AccomulateHdrsState( &$state, &$aCurHdr, $elems )
{
	foreach( $elems as $ee )
	{
		$statePart = '';
		foreach( ExprConditionsSet_Parse( $ee ) as $e )
		{
			if( $aCurHdr === null )
				$aCurHdr = Net::GetRequestHeaders( null, false, true );

			$isRegExp = IsStrRegExp( $e[ 'expr' ] );

			$vals = array();
			foreach( $aCurHdr as $hdr )
			{
				if( $isRegExp )
				{
					if( @preg_match( $e[ 'expr' ], $hdr, $m ) )
						$vals[] = count( $m ) > 1 ? $m[ 1 ] : $m[ 0 ];
				}
				else if( Gen::StrStartsWith( $hdr, $e[ 'expr' ] ) )
					$vals[] = $hdr;
			}
			if( !$vals )
				$vals = array( '' );

			$found = false;
			foreach( $vals as $val )
			{
				if( ExprConditionsSet_ItemOp( $e, $val ) )
				{
					$statePart .= strlen( $val ) ? $val : '!';
					$found = true;
				}
			}

			if( !$found )
			{
				$statePart = '';
				break;
			}
		}

		$state .= $statePart;
	}
}

function GetCookiesState( $ctxGrps, $cookies )
{
	$stateCookId = '';

	foreach( $ctxGrps as $ctxGrp )
		if( (isset($ctxGrp[ 'enable' ])?$ctxGrp[ 'enable' ]:null) )
			AccomulateCookiesState( $stateCookId, $cookies, Gen::GetArrField( $ctxGrp, array( 'cookies' ), array() ) );

	return( $stateCookId );
}

function ContProcGetSkipStatus( $content )
{
	global $seraph_accel_g_contProcGetSkipStatus;
	global $seraph_accel_g_sRedirLocation;

	if( $seraph_accel_g_contProcGetSkipStatus !== null )
		return( $seraph_accel_g_contProcGetSkipStatus );

	if( defined( 'REST_REQUEST' ) && REST_REQUEST )
		return( $seraph_accel_g_contProcGetSkipStatus = 'restapi' );

	if( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST )
		return( $seraph_accel_g_contProcGetSkipStatus = 'xmlrpc' );

	$errLast = error_get_last();
	if( Gen::GetArrField( $errLast, array( 'type' ), 0 ) & ( E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR ) )
	{
		$msg = $errLast[ 'message' ];
		if( strpos( $msg, $errLast[ 'file' ] ) === false )
			$msg .= "\n" . $errLast[ 'file' ] . ':' . $errLast[ 'line' ];
		return( $seraph_accel_g_contProcGetSkipStatus = 'err:php:' . rawurlencode( $msg ) );
	}

	$http_response_code = http_response_code();
	if( $http_response_code !== 200 )
	{
		$skipStatus = 'httpCode:' . $http_response_code;
		if( in_array( $http_response_code, array( 301, 302, 307, 308 ) ) && $seraph_accel_g_sRedirLocation )
			$skipStatus .= ':' . rawurlencode( $seraph_accel_g_sRedirLocation );
		return( $seraph_accel_g_contProcGetSkipStatus = $skipStatus );
	}

	if( is_404() )
		return( $seraph_accel_g_contProcGetSkipStatus = 'httpCode:404' );

	if( is_search() )
		return( $seraph_accel_g_contProcGetSkipStatus = 'search' );
	if( is_feed() )
		return( $seraph_accel_g_contProcGetSkipStatus = 'feed' );

	if( Gen::StrPosArr( $content, array( '</body>', '</BODY>' ) ) === false && Gen::StrPosArr( $content, array( '</head>', '</HEAD>' ) ) === false )
		return( $seraph_accel_g_contProcGetSkipStatus = 'noHdrOrBody' );

	return( $seraph_accel_g_contProcGetSkipStatus = false );
}

function MatchUserAgentExpressions( $userAgent, $aAgent, $aAgentPS = array() )
{
	foreach( $aAgent as $aa )
	{
		$bPS = false;
		foreach( $aAgentPS as $agentPS )
		{
			if( !@preg_match( $agentPS, $aa ) )
				continue;

			$bPS = true;
			break;
		}

		if( $bPS )
			continue;

		$matched = false;
		foreach( ExprConditionsSet_Parse( $aa ) as $a )
		{
			$isRegExp = IsStrRegExp( $a[ 'expr' ] );

			if( $isRegExp )
			{
				if( @preg_match( $a[ 'expr' ], $userAgent, $userAgentFoundVal ) )
					$userAgentFoundVal = count( $userAgentFoundVal ) > 1 ? $userAgentFoundVal[ 1 ] : $userAgentFoundVal[ 0 ];
				else
					$userAgentFoundVal = '';
			}
			else if( strpos( $userAgent, $a[ 'expr' ] ) !== false )
				$userAgentFoundVal = $userAgent;
			else
				$userAgentFoundVal = '';

			$matched = ExprConditionsSet_ItemOp( $a, $userAgentFoundVal );
			if( !$matched )
				break;
		}

		if( $matched )
			return( true );
	}

	return( false );
}

function ContProcIsCompatView( $settCache, $userAgent  )
{

	global $seraph_accel_g_contProcCompatView;

	if( $seraph_accel_g_contProcCompatView !== null )
		return( $seraph_accel_g_contProcCompatView );

	$compatView = false;

	$viewsCompatGrps = Gen::GetArrField( $settCache, array( 'viewsCompatGrps' ), array() );

	foreach( $viewsCompatGrps as $viewsCompatGrp )
	{
		if( !(isset($viewsCompatGrp[ 'enable' ])?$viewsCompatGrp[ 'enable' ]:null) )
			continue;

		if( MatchUserAgentExpressions( $userAgent, Gen::GetArrField( $viewsCompatGrp, array( 'agents' ), array() ) ) )
		{
			$compatView = (isset($viewsCompatGrp[ 'id' ])?$viewsCompatGrp[ 'id' ]:null);
			break;
		}
	}

	return( $seraph_accel_g_contProcCompatView = $compatView );
}

function GetViewTypeUserAgent( $viewsDeviceGrp )
{
	return( 'Mozilla/99999.9 AppleWebKit/9999999.99 (KHTML, like Gecko) Chrome/999999.0.9999.99 Safari/9999999.99 seraph-accel-Agent/2.22.16 ' . ucwords( implode( ' ', Gen::GetArrField( $viewsDeviceGrp, array( 'agents' ), array() ) ) ) );
}

function CorrectRequestScheme( &$serverArgs, $target = null )
{

	if( strtolower( (isset($serverArgs[ 'HTTPS' ])?$serverArgs[ 'HTTPS' ]:'') ) == 'on' || ( $target == 'client' && strtolower( (isset($serverArgs[ 'HTTP_X_FORWARDED_PROTO' ])?$serverArgs[ 'HTTP_X_FORWARDED_PROTO' ]:'') ) == 'https' ) )
	{
		if( (isset($serverArgs[ 'REQUEST_SCHEME' ])?$serverArgs[ 'REQUEST_SCHEME' ]:null) == 'http' && (isset($serverArgs[ 'SERVER_PORT' ])?$serverArgs[ 'SERVER_PORT' ]:null) == 80 )
			$serverArgs[ 'SERVER_PORT' ] = 443;
		$serverArgs[ 'REQUEST_SCHEME' ] = 'https';
	}
	else if( !(isset($serverArgs[ 'REQUEST_SCHEME' ])?$serverArgs[ 'REQUEST_SCHEME' ]:null) )
		$serverArgs[ 'REQUEST_SCHEME' ] = 'http';
}

function GetRequestHost( $serverArgs )
{
	return( strtolower( Net::GetRequestHost( $serverArgs ) ) );
}

function GetCurRequestUrl()
{
	$serverArgsTmp = Gen::ArrCopy( $_SERVER ); CorrectRequestScheme( $serverArgsTmp, 'client' );
	return( $serverArgsTmp[ 'REQUEST_SCHEME' ] . '://' . GetRequestHost( $serverArgsTmp ) . $serverArgsTmp[ 'REQUEST_URI' ] );
}

function Queue_GetStgPrms( $dirQueue, $state )
{
	return( array( 'dirFilesPattern' => $dirQueue . '/' . $state . '/*.dat.gz', 'options' => array( 'countPerFirstChunk' => 100, 'cbSort' =>
		function( $item1, $item2 )
		{
			$iCmp = Gen::VarCmp( (isset($item1[ 'p' ])?$item1[ 'p' ]:null), (isset($item2[ 'p' ])?$item2[ 'p' ]:null) );
			if( $iCmp !== 0 )
				return( $iCmp );

			$iCmp = Gen::VarCmp( (isset($item1[ 't' ])?$item1[ 't' ]:null), (isset($item2[ 't' ])?$item2[ 't' ]:null) );
			if( $iCmp !== 0 )
				return( $iCmp );

			return( 0 );
		}
	) ) );
}

function Queue_IsPriorFirst( $siteId, $priority )
{
	$dirQueue = GetCacheDir() . '/q/' . $siteId;

	$lock = new Lock( 'l', $dirQueue );
	if( !$lock -> Acquire() )
		return( false );

	$res = false;

	foreach( array( 1, 0 ) as $state )
	{
		$a = new ArrayOnFiles( Queue_GetStgPrms( $dirQueue, $state ) );
		$b = Gen::GetArrField( current( $a -> slice( 0, 1 ) ), array( 'p' ), 0 ) == $priority;
		$a -> dispose();

		if( $b )
		{
			$res = true;
			break;
		}
	}

	$lock -> Release();
	return( $res );
}

function OnAsyncTask_QueueProcessItems( $args )
{
	$settGlobal = Plugin::SettGetGlobal();
	$settCacheGlobal = Gen::GetArrField( $settGlobal, array( 'cache' ), array() );

	$nMaxItems = (isset($settCacheGlobal[ 'maxProc' ])?$settCacheGlobal[ 'maxProc' ]:null);
	if( !$nMaxItems )
		$nMaxItems = 1;
	$nMaxItemsTotal = $nMaxItems;

	$procTmLim = Gen::GetArrField( Plugin::SettGet(), array( 'cache', 'procTmLim' ), 570 );

	$dirFileValues = PluginFileValues::GetDirVar( '' );

	$tmCur = microtime( true );

	foreach( glob( GetCacheDir() . '/qt/*.dat' ) as $fileTempQueue )
	{
		$data = @unserialize( @file_get_contents( $fileTempQueue ) );
		@unlink( $fileTempQueue );

		if( $data )
			CachePostPreparePageEx( Gen::GetArrField( $data, array( 'u' ), '' ), Gen::GetArrField( $data, array( 's' ), '' ), Gen::GetArrField( $data, array( 'p' ), 10 ), null, Gen::GetArrField( $data, array( 'h' ), array() ), Gen::GetArrField( $data, array( 't' ), 0.0 ) );
	}

	$aCurItemsPrior = array();
	foreach( GetSiteIds() as $siteId )
	{
		$dirQueue = GetCacheDir() . '/q/' . $siteId;

		$lock = new Lock( 'l', $dirQueue );
		if( !$lock -> Acquire() )
			continue;

		$aProgress = new ArrayOnFiles( Queue_GetStgPrms( $dirQueue, 1 ) );
		$aDone = new ArrayOnFiles( Queue_GetStgPrms( $dirQueue, 2 ) );
		foreach( $aProgress -> slice( 0 ) as $id => $item )
		{
			$data = Gen::GetArrField( Gen::Unserialize( (isset($item[ 'd' ])?$item[ 'd' ]:null) ), array( '' ), array() );

			$hrItemForce = Gen::S_OK;
			$tmDur = $tmCur - ( float )(isset($item[ 't' ])?$item[ 't' ]:null);
			$fileCtl = ProcessCtlData_GetFullPath( (isset($data[ 'pc' ])?$data[ 'pc' ]:null) );
			if( $tmDur <= $procTmLim + 30 )
			{
				if( $tmDur > ( 30 ) && $fileCtl )
				{
					$ctlRes = ProcessCtlData_Get( $fileCtl, $isLive );
					if( $ctlRes === null )
						$hrItemForce = Gen::S_ABORTED;
					else if( Gen::GetArrField( $ctlRes, array( 'stage' ) ) && !Gen::GetArrField( $ctlRes, array( 'finish' ) ) && !$isLive )
						$hrItemForce = Gen::E_INVALID_STATE;
				}
			}
			else
				$hrItemForce = Gen::E_TIMEOUT;

			if( $hrItemForce == Gen::S_OK )
			{
				if( $nMaxItems > 0 )
					$nMaxItems --;
				$aCurItemsPrior[ ( int )(isset($item[ 'p' ])?$item[ 'p' ]:null) ] = true;
				continue;
			}

			if( $fileCtl )
				ProcessCtlData_Del( $fileCtl );

			$data[ 'td' ] = $tmDur;
			$data[ 'hr' ] = $hrItemForce;

			$item[ 'd' ] = Gen::Serialize( $data );
			$item[ 'p' ] = -1000;
			$item[ 't' ] = $tmCur;
			unset( $aProgress[ $id ] );
			$aDone[ $id ] = $item;
		}

		{
			$n = $aDone -> count();

			if( Gen::GetArrField( $settGlobal, array( 'debug' ), false ) )
			{
				if( $n > 2 * 10 )
				{
					$aDoneNew = array();
					$nDone = 0;
					$nDoneErr = 0;

					for( $aDone -> end(); $aDone -> valid(); $aDone -> prev() )
					{
						$item = $aDone -> current();

						$data = Gen::GetArrField( Gen::Unserialize( (isset($item[ 'd' ])?$item[ 'd' ]:null) ), array( '' ), array() );
						list( $iconClr, $state, $stateDsc, $duration ) = GetQueueItem_Done_Attrs( $data );
						unset( $state, $stateDsc, $duration );

						$bPut = false;
						if( $iconClr == 'error' )
						{
							if( $nDoneErr < 10 )
							{
								$bPut = true;
								$nDoneErr++;
							}
						}
						else
						{
							if( $nDone < 10 )
							{
								$bPut = true;
								$nDone++;
							}
						}

						if( $bPut )
							Gen::ArrSplice( $aDoneNew, 0, 0, array( $aDone -> key() => $item ) );

						if( $nDone == 10 && $nDoneErr == 10 )
							break;
					}

					$aDone -> clear();
					$aDone -> setItems( $aDoneNew );
				}
			}
			else
			{
				if( $n > 10 )
					$aDone -> splice( 0, $n - 10 );
			}
		}

		$aDone -> dispose(); $aProgress -> dispose(); $lock -> Release();
		unset( $aDone, $aProgress, $lock );
	}

	if( !$nMaxItems )
	{

		return;
	}

	$procInterval = (isset($settCacheGlobal[ 'procInterval' ])?$settCacheGlobal[ 'procInterval' ]:null);
	if( $procInterval )
	{
		if( $nMaxItems < $nMaxItemsTotal )
			return;

		$procEndLastTime = intval( PluginFileValues::GetEx( $dirFileValues, 'pelt' ) );
		if( $tmCur - $procEndLastTime < $procInterval )
			return;
	}

	$items = array();
	foreach( GetSiteIds() as $siteId )
	{
		$dirQueue = GetCacheDir() . '/q/' . $siteId;

		$lock = new Lock( 'l', $dirQueue );
		if( !$lock -> Acquire() )
			continue;

		$aInitial = new ArrayOnFiles( Queue_GetStgPrms( $dirQueue, 0 ) );
		foreach( $aInitial -> slice( 0, $nMaxItems ) as $id => $item )
		{
			$item[ 's' ] = $siteId;
			$items[ $id ] = $item;
		}
		unset( $item );

		$aInitial -> dispose(); $lock -> Release();
		unset( $aInitial, $lock );
	}

	if( !$items )
	{

		return;
	}

	uasort( $items, Gen::GetArrField( Queue_GetStgPrms( '', 0 ), array( 'options', 'cbSort' ) ) );
	$items = array_slice( $items, 0, $nMaxItems, true );

	foreach( $items as $id => $item )
	{
		$prior = ( int )(isset($item[ 'p' ])?$item[ 'p' ]:null);

		if( isset( $aCurItemsPrior[ -480 ] ) && $prior > -480 )
		    continue;

		$aCurItemsPrior[ $prior ] = true;

		Plugin::AsyncTaskPost( 'CacheProcessItem', array( 'id' => $id, 'siteId' => $item[ 's' ] ) );

	}

}

function CacheInitQueueProcessor()
{
	$tmCur = time();

	{
		$time = $tmCur + ( 30 );
		$time -= $time % ( 30 );
		Plugin::AsyncFastTaskPost( 'QueueProcessItems', null, array( $time, 30 ), false, function( $args, $argsPrev ) { return( false ); } );
	}

	{
		$time = $tmCur + ( 120 );
		$time -= $time % ( 120 );
		Plugin::AsyncFastTaskPost( 'CheckPostProcess', null, array( $time, 24 * 60 * 60 ), false, function( $args, $argsPrev ) { return( false ); } );
	}
}

function CachePushQueueProcessor( $next = false, $immediately = false, $shortInterval = false )
{

	$procInterval = 0;
	if( $next && $immediately === false )
	{
		$settCacheGlobal = Gen::GetArrField( Plugin::SettGetGlobal(), array( 'cache' ), array() );
		$procInterval = $shortInterval ? (isset($settCacheGlobal[ 'procIntervalShort' ])?$settCacheGlobal[ 'procIntervalShort' ]:null) : (isset($settCacheGlobal[ 'procInterval' ])?$settCacheGlobal[ 'procInterval' ]:null);
	}

	$dirFileValues = PluginFileValues::GetDirVar( '' );
	$time = time() + $procInterval;

	if( !is_bool( $immediately ) )
	{
		$timeLast = PluginFileValues::GetEx( $dirFileValues, 'qpin' );
		if( $timeLast && ( $time - $timeLast < $immediately ) )
			$procInterval = 99999;
	}
	else if( $next && ( $immediately || $shortInterval ) )
		PluginFileValues::SetEx( $dirFileValues, 'pelt', 0 );

	PluginFileValues::SetEx( $dirFileValues, 'qpin', $time );

	Plugin::AsyncFastTaskPost( 'QueueProcessItems', null, array( $time ), false, true );

	if( !$next && !IsCronEnabled()  )
		return;

	if( $procInterval <= 5 )
	{
		if( $procInterval )
			sleep( $procInterval );

			Plugin::AsyncTaskPush( 0 );
	}

}

function ContentProcess_IsAborted( $settCache = null )
{
	global $seraph_accel_g_prepPrms;

	if( $seraph_accel_g_prepPrms === null )
		return;

	return( !Gen::SliceExecTime( (isset($settCache[ 'procWorkInt' ])?$settCache[ 'procWorkInt' ]:null), (isset($settCache[ 'procPauseInt' ])?$settCache[ 'procPauseInt' ]:null), 2,
		function()
		{
			global $seraph_accel_g_prepPrms;
			return( ProcessCtlData_IsAborted( (isset($seraph_accel_g_prepPrms[ 'pc' ])?$seraph_accel_g_prepPrms[ 'pc' ]:null) ) );
		}
	) );
}

function ProcessCtlData_GetFullPath( $file = '' )
{
	if( $file === null )
		return( null );
	return( GetCacheDir() . '/pc' . ( $file ? ( '/' . Gen::GetFileName( $file ) ) : '' ) );
}

function ProcessCtlData_IsAborted( $fileCtl )
{
	if( !$fileCtl )
		return;

	$lock = new Lock( 'pl', GetCacheDir() );
	if( !$lock -> Acquire() )
		return;

	$res = !@file_exists( $fileCtl . '.dat' );
	$lock -> Release();
	return( $res );
}

function ProcessCtlData_Init( $fileCtlDir, $data )
{
	if( Gen::HrFail( Gen::MakeDir( $fileCtlDir, true ) ) )
		return( null );

	$fileCtl = @tempnam( $fileCtlDir, '' );
	if( !$fileCtl )
		return( null );

	if( !@file_put_contents( $fileCtl . '.dat', @serialize( $data ) ) )
	{
		@unlink( $fileCtl );
		return( null );
	}

	return( $fileCtl );
}

function ProcessCtlData_Del( $fileCtl )
{
	$lock = new Lock( 'pl', GetCacheDir() );
	if( !$lock -> Acquire() )
		return;

	@unlink( $fileCtl . '.dat' );
	@unlink( $fileCtl );
	$lock -> Release();
}

function ProcessCtlData_Get( $fileCtl, &$isLive = null )
{
	$lock = new Lock( 'pl', GetCacheDir() );
	if( !$lock -> Acquire() )
		return( Gen::E_FAIL );

	if( Gen::FileContentExclusive_Open( $hFileCtl, $fileCtl ) !== Gen::E_BUSY )
	{
		if( !$hFileCtl )
		{
			$lock -> Release();
			return( null );
		}

		Gen::FileContentExclusive_Close( $hFileCtl );
		$isLive = false;
	}
	else
		$isLive = true;

	$data = @file_get_contents( $fileCtl . '.dat' );
	$data = is_string( $data ) ? @unserialize( $data ) : null;
	$lock -> Release();
	return( $data );
}

function ProcessCtlData_Update( $fileCtl, $data, $clearPrev = false, $lifeCtlOpenClose = null )
{
	if( !$fileCtl )
		return( true );

	$lock = new Lock( 'pl', GetCacheDir() );
	if( !$lock -> Acquire() )
	{

		return( false );
	}

	global $seraph_accel_g_hFileCtl;

	$dataPrev = @file_get_contents( $fileCtl . '.dat' );
	if( $dataPrev === false )
	{
		$lock -> Release();

		return( null );
	}

	if( $lifeCtlOpenClose === true )
	{
		Gen::FileContentExclusive_Open( $seraph_accel_g_hFileCtl, $fileCtl, false );
		if( !$seraph_accel_g_hFileCtl )
		{
			$lock -> Release();

			return( false );
		}
	}
	else if( $lifeCtlOpenClose === false )
		Gen::FileContentExclusive_Close( $seraph_accel_g_hFileCtl );

	if( !$dataPrev || $clearPrev )
		$dataPrev = array();
	else
		$dataPrev = @unserialize( $dataPrev );

	$res = @file_put_contents( $fileCtl . '.dat', @serialize( array_merge( $dataPrev, $data ) ) ) !== false;
	$lock -> Release();

	return( $res );
}

function OnAsyncTask_CacheProcessItem( $args )
{

	$id = (isset($args[ 'id' ])?$args[ 'id' ]:null);
	$siteId = Gen::SanitizeId( (isset($args[ 'siteId' ])?$args[ 'siteId' ]:null) );
	if( !$id || !$siteId )
		return;

	$dirFileValues = PluginFileValues::GetDirVar( '' );
	$dirQueue = GetCacheDir() . '/q/' . $siteId;

	$item = null;
	{
		$lock = new Lock( 'l', $dirQueue );
		if( !$lock -> Acquire() )
			return;

		$aInitial = new ArrayOnFiles( Queue_GetStgPrms( $dirQueue, 0 ) );
		$item = $aInitial[ $id ];

		$aInitial -> dispose(); $lock -> Release();
		unset( $aInitial, $lock );
	}

	if( !$item )
		return;

	$data = Gen::GetArrField( Gen::Unserialize( (isset($item[ 'd' ])?$item[ 'd' ]:null) ), array( '' ), array() );
	if( !$data )
		return;

	{ if( !isset( $data[ 'p' ] ) ) $data[ 'p' ] = ( int )(isset($item[ 'p' ])?$item[ 'p' ]:null); }

	$procTmLim = Gen::GetArrField( Plugin::SettGet(), array( 'cache', 'procTmLim' ), 570 );

	Gen::SetTimeLimit( $procTmLim + 30 );

	$fileCtlDir = ProcessCtlData_GetFullPath();
	if( !( $fileCtl = ProcessCtlData_Init( $fileCtlDir, array() ) ) )
	{
		$data[ 'td' ] = 0;
		$data[ 'hr' ] = Gen::E_FAIL;
		$data[ 'r' ] = 'init:Can\'t modify files in \'' . $fileCtlDir . '\'';

		$item[ 'd' ] = Gen::Serialize( $data );

		$lock = new Lock( 'l', $dirQueue );
		if( $lock -> Acquire() )
		{
			$aInitial = new ArrayOnFiles( Queue_GetStgPrms( $dirQueue, 0 ) );
			$aDone = new ArrayOnFiles( Queue_GetStgPrms( $dirQueue, 2 ) );

			unset( $aInitial[ $id ] );
			$aDone[ $id ] = $item;

			$aInitial -> dispose(); $aDone -> dispose();
		}
		$lock -> Release();
		unset( $aInitial, $aDone, $lock );

		return;
	}
	unset( $fileCtlDir );

	$data[ 'pc' ] = Gen::GetFileName( $fileCtl );

	$tmBegin = microtime( true );

	$tmOrig = ( float )(isset($item[ 't' ])?$item[ 't' ]:null);
	$priorOrig = ( int )(isset($item[ 'p' ])?$item[ 'p' ]:null);
	$item[ 't' ] = $tmBegin;
	$item[ 'd' ] = Gen::Serialize( $data );

	{
		$lock = new Lock( 'l', $dirQueue );
		if( !$lock -> Acquire() )
		{

			return;
		}

		$aInitial = new ArrayOnFiles( Queue_GetStgPrms( $dirQueue, 0 ) );
		$aProgress = new ArrayOnFiles( Queue_GetStgPrms( $dirQueue, 1 ) );

		$ok = true;
		if( $aInitial -> unsetItem( $id ) === false )
			$ok = false;
		if( $ok && $aProgress -> setItem( $id, $item ) === false )
			$ok = false;

		$aInitial -> dispose(); $aProgress -> dispose();

		if( !$ok )
		{
			ProcessCtlData_Del( $fileCtl );
			$lock -> Release();
			return;
		}

		$lock -> Release();
		unset( $aInitial, $aProgress, $lock );
	}

	$prepArgs = array( 'nonce' => hash_hmac( 'md5', '' . $tmBegin, GetSalt() ), '_tm' => '' . $tmBegin, 'pc' => $data[ 'pc' ] );
	if( $priorOrig == -480 )
		$prepArgs[ 'lrn' ] = (isset($data[ 'l' ])?$data[ 'l' ]:null);

	$url = add_query_arg( array( 'seraph_accel_prep' => @rawurlencode( @base64_encode( @json_encode( $prepArgs ) ) ) ), (isset($data[ 'u' ])?$data[ 'u' ]:null) );

	$hdrs = (isset($data[ 'h' ])?$data[ 'h' ]:null);
	if( !is_array( $hdrs ) )
		$hdrs = array();

	{
		$hdrsForRequest = $hdrs;
		Net::GetUrlWithoutProtoEx( $url, $proto );
		if( strtolower( $proto ) == 'https' )
		{
			Net::RemoveHeader( $hdrsForRequest, 'Upgrade-Insecure-Requests' );
			Net::RemoveHeader( $hdrsForRequest, 'Ssl' );

		}

		Net::RemoveHeader( $hdrsForRequest, 'Accept-Encoding' );
		Net::RemoveHeader( $hdrsForRequest, 'Cloud-Protector-Client-Ip' );

		unset( $proto );

		$asyncMode = OnAsyncTasksPushGetMode();
		if( $asyncMode == 're' || $asyncMode == 're_r' )
			$requestRes = PluginRe::MakeRequest( 'GET', $url, $hdrsForRequest, 30, false );
		else

			$requestRes = Wp::RemoteGet( $url, array( 'local' => $asyncMode == 'loc', 'redirection' => 0, 'timeout' => 30, 'sslverify' => false, 'headers' => $hdrsForRequest ) );

		$tmFinish = microtime( true );
		$hr = Net::GetHrFromWpRemoteGet( $requestRes, true );
		$httpCode = Net::GetResponseCodeFromHr( $hr );

	}

	$needRepeat = false;
	$needLrn = false;
	$repeatIdx = null;
	$skipStatus = null;
	$warns = null;

	$ctlRes = ProcessCtlData_Get( $fileCtl, $isLive );
	if( Gen::GetArrField( $ctlRes, array( 'stage' ) ) )
	{

		for( ;; )
		{

			$tmFinish = microtime( true );
			if( $tmFinish - $tmBegin > $procTmLim )
			{
				$hr = Gen::E_TIMEOUT;
				$requestRes = null;
				break;
			}

			if( is_int( $ctlRes ) )
			{
				$hr = $ctlRes;
				$requestRes = null;
				break;
			}

			if( $ctlRes === null )
			{
				$hr = Gen::S_ABORTED;
				$requestRes = null;
				break;
			}

			if( Gen::GetArrField( $ctlRes, array( 'finish' ) ) )
			{
				$skipStatus = Gen::GetArrField( $ctlRes, array( 'skip' ) );
				$hr = $skipStatus ? ( Gen::StrStartsWith( $skipStatus, 'err:' ) ? Gen::E_FAIL : Gen::S_FALSE ) : Gen::S_OK;
				$warns = Gen::GetArrField( $ctlRes, array( 'warns' ), array() );
				break;
			}

			if( !$isLive )
			{
				$hr = Gen::E_INVALID_STATE;
				$requestRes = null;
				break;
			}

			sleep( 1 );

			$ctlRes = ProcessCtlData_Get( $fileCtl, $isLive );
		}
	}
	else
	{
		if( ProcessCtlData_IsAborted( $fileCtl ) )
		{
			$hr = Gen::S_ABORTED;
			$requestRes = null;
		}
		else if( $httpCode && $httpCode != 500 )
		{
			if( $httpCode == 524 || $httpCode == 522 || $httpCode == 504 || $httpCode == 503 )
				if( ( $repeatIdx = (isset($data[ 'rdr' ])?$data[ 'rdr' ]:0) ) <= 3 )
					$needRepeat = true;
			$hr = Gen::HrSuccFromFail( $hr );
		}
	}

	$immediatelyPushQueue = false;

	$urlRedir = trim( wp_remote_retrieve_header( $requestRes, 'location' ) );
	if( !$urlRedir && $skipStatus && preg_match( '@^httpCode\\:(?:301|302|307|308)\\:@', $skipStatus ) )
		$urlRedir = rawurldecode( substr( $skipStatus, 13 ) );

	if( $urlRedir && $urlRedir != (isset($data[ 'u' ])?$data[ 'u' ]:null) )
	{
		$urlRedir = remove_query_arg( array( 'seraph_accel_prep' ), $urlRedir );
		if( Gen::StrStartsWith( $urlRedir, '//' ) )
		{
			GetUrlWithoutProtoEx( $url, $proto );
			$urlRedir = $proto . ':' . $urlRedir;
			unset( $proto );
		}
		else if( strpos( $urlRedir, '://' ) === false )
			$urlRedir = Net::GetSiteAddrFromUrl( $url, true ) . $urlRedir;

		if( $priorOrig !== 10 )
			if( ( $redirIdx = (isset($data[ 'rdr' ])?$data[ 'rdr' ]:0) ) <= 4 )
				if( CachePostPreparePageEx( $urlRedir, $siteId, $priorOrig, (isset($data[ 'p' ])?$data[ 'p' ]:null), $hdrs, $tmOrig, $redirIdx + 1, (isset($data[ 'l' ])?$data[ 'l' ]:null) ) )
					$immediatelyPushQueue = true;
	}

	$data[ 'td' ] = $tmFinish - $tmBegin;

	if( $hr != Gen::S_OK && !$skipStatus && $httpCode )
		$skipStatus = 'httpCode:' . $httpCode;

	if( $skipStatus )
	{
		if( Gen::StrStartsWith( $skipStatus, 'httpCode:' ) && $urlRedir && strlen( $skipStatus ) === 12 )
			$skipStatus .= ':' . rawurlencode( $urlRedir );

		$data[ 'hr' ] = ( $hr = Gen::StrStartsWith( $skipStatus, 'err:' ) ? Gen::E_FAIL : Gen::S_FALSE );
		$data[ 'r' ] = $skipStatus;

		if( $skipStatus == 'alreadyProcessing' || $skipStatus == 'lrnNeed' )
			$needRepeat = true;
		else if( Gen::StrStartsWith( $skipStatus, 'lrnNeed:' ) )
			$needLrn = substr( $skipStatus, 8 );
	}
	else if( $hr != Gen::S_OK )
	{
		$data[ 'hr' ] = $hr;
		if( is_wp_error( $requestRes ) )
			$data[ 'r' ] = rawurlencode( $requestRes -> get_error_message() ) . ':' . rawurlencode( LocId::Pack( 'RequestHeadersTrace_%1$s', null, array( strip_tags( str_replace( array( '<br>' ), array( "\n" ), GetHeadersResString( $hdrsForRequest ) ) ) ) ) );
		if( $skipStatus )
			$data[ 'r' ] = $skipStatus;
	}

	if( $needLrn )
	{
		{
			$lock = new Lock( 'l', $dirQueue );
			if( !$lock -> Acquire() )
			{

				return;
			}

			$aProgress = new ArrayOnFiles( Queue_GetStgPrms( $dirQueue, 1 ) );
			unset( $aProgress[ $id ] );
			$aProgress -> dispose();

			$lock -> Release();
			unset( $aProgress, $lock );
		}

		CachePostPreparePageEx( (isset($data[ 'u' ])?$data[ 'u' ]:null), $siteId, -480, (isset($data[ 'p' ])?$data[ 'p' ]:null), $hdrs, $tmOrig, null, $needLrn );
		$immediatelyPushQueue = true;
	}
	else
	{
		if( $hr == Gen::S_OK && $warns )
			$data[ 'w' ] = $warns;

		if( isset( $data[ 'hr' ] ) && $data[ 'hr' ] != Gen::S_OK && $urlRedir && $urlRedir == (isset($data[ 'u' ])?$data[ 'u' ]:null) )
		{
			$data[ 'hr' ] = ( $hr = Gen::E_FAIL );
			$data[ 'r' ] = 'redirectToItself';
		}

		{
			$lock = new Lock( 'l', $dirQueue );
			if( !$lock -> Acquire() )
			{

				return;
			}

			$aProgress = new ArrayOnFiles( Queue_GetStgPrms( $dirQueue, 1 ) );
			$aDone = new ArrayOnFiles( Queue_GetStgPrms( $dirQueue, 2 ) );
			{
				$dataExtUpdated = $aProgress[ $id ];
				if( $dataExtUpdated )
				{
					if( $dataExtUpdated = Gen::GetArrField( Gen::Unserialize( (isset($dataExtUpdated[ 'd' ])?$dataExtUpdated[ 'd' ]:null) ), array( '' ), array() ) )
						if( (isset($dataExtUpdated[ 'rpt' ])?$dataExtUpdated[ 'rpt' ]:null) )
							$needRepeat = true;

					unset( $dataExtUpdated );
				}
			}

			$item[ 't' ] = $tmFinish;
			$item[ 'p' ] = -1000;
			$item[ 'd' ] = Gen::Serialize( $data );

			unset( $aProgress[ $id ] );
			$aDone[ $id ] = $item;

			$aProgress -> dispose(); $aDone -> dispose(); $lock -> Release();
			unset( $aProgress, $aDone, $lock );
		}
	}

	ProcessCtlData_Del( $fileCtl );

	PluginFileValues::SetEx( $dirFileValues, 'pelt', ( int )$tmFinish );

	if( $needRepeat && CachePostPreparePageEx( (isset($data[ 'u' ])?$data[ 'u' ]:null), $siteId, $priorOrig, (isset($data[ 'p' ])?$data[ 'p' ]:null), $hdrs, $tmOrig, $repeatIdx !== null ? ( $repeatIdx + 1 ) : null, (isset($data[ 'l' ])?$data[ 'l' ]:null) ) )
	    $immediatelyPushQueue = true;

	CachePushQueueProcessor( true, $immediatelyPushQueue, $hr != Gen::S_OK && Gen::HrSucc( $hr ) );

}

function GetHeadersResString( $hdrs )
{
	$res = '';
	foreach( $hdrs as $hdrId => $hdrVals )
	{
		if( !is_array( $hdrVals ) )
			$hdrVals = array( $hdrVals );

		foreach( $hdrVals as $hdrVal )
		{
			if( $res )
				$res .= '<br>';
			$res .= Ui::Tag( 'em', htmlspecialchars( $hdrId ) ) . ': ' . htmlspecialchars( $hdrVal );
		}
	}

	return( $res );
}

function CacheInitQueueTable( $force = false )
{
	$data = Plugin::DataGet();

	$dbVer = Gen::GetArrField( $data, 'queueDbVer', 0 );
	if( !$force && $dbVer == QUEUE_DB_VER )
		return;

	Gen::SetArrField( $data, 'queueDbVer', QUEUE_DB_VER );
	Plugin::DataSet( $data );

	if( $dbVer && $dbVer < 4 )
	{
		$nRowsChunk = 100;
		$dbtran = new Lock( 'ql', GetCacheDir() );
		if( $dbtran -> Acquire() )
		{
			$run = true;
			$tmStart = time();

			foreach( array( 2, 1, 0 ) as $state )
			{
				for( $i = 0; ; $i++ )
				{
					$items = DbTbl::GetRows( Db::GetTblPrefix( 'seraph_accel_queue' ), null, array( $i * $nRowsChunk, $nRowsChunk ), array( 'state' => $state ) );
					if( !$items )
						break;

					foreach( $items as $itemFromDb )
					{
						$id = @hex2bin( (isset($itemFromDb[ 'id' ])?$itemFromDb[ 'id' ]:null) );
						$siteId = (isset($itemFromDb[ 'site_id' ])?$itemFromDb[ 'site_id' ]:null);
						if( !$siteId )
							$siteId = 'm';

						$item = array( 'p' => ( int )(isset($itemFromDb[ 'prior' ])?$itemFromDb[ 'prior' ]:null), 't' => ( float )(isset($itemFromDb[ 'tm' ])?$itemFromDb[ 'tm' ]:null), 'd' => (isset($itemFromDb[ 'data' ])?$itemFromDb[ 'data' ]:null) );

						{
							$dirQueue = GetCacheDir() . '/q/' . $siteId;
							$lock = new Lock( 'l', $dirQueue );
							if( $lock -> Acquire() )
							{
								$a = new ArrayOnFiles( Queue_GetStgPrms( $dirQueue, $state ) );
								$a[ $id ] = $item;
								$a -> dispose(); unset( $a );

								$lock -> Release();
							}
							unset( $lock );
						}

						if( time() - $tmStart > 10 )
						{
							$run = false;
							break;
						}
					}

					if( !$run )
						break;
				}

				if( !$run )
					break;
			}

			DbTbl::DeleteRows( Db::GetTblPrefix( 'seraph_accel_queue' ) );
			$dbtran -> Release();
		}
	}
}

function GetViewDeviceGrpNameFromData( $viewsDeviceGrp )
{
	$viewName = (isset($viewsDeviceGrp[ 'name' ])?$viewsDeviceGrp[ 'name' ]:null);
	if( !$viewName )
		$viewName = 'id:' . (isset($viewsDeviceGrp[ 'id' ])?$viewsDeviceGrp[ 'id' ]:null);
	return( $viewName );
}

function _CachePostPreparePageEx_StopAndRepeat( $aProgress, $id, $data = null )
{
	$item = $aProgress[ $id ];
	if( !$item )
		return;

	$itemInProgressData = Gen::GetArrField( Gen::Unserialize( (isset($item[ 'd' ])?$item[ 'd' ]:null) ), array( '' ), array() );

	if( (isset($itemInProgressData[ 'rpt' ])?$itemInProgressData[ 'rpt' ]:null) || !( $fileCtl = ProcessCtlData_GetFullPath( (isset($itemInProgressData[ 'pc' ])?$itemInProgressData[ 'pc' ]:null) ) ) )
		return;

	ProcessCtlData_Del( $fileCtl );

	if( $data )
		$itemInProgressData = array_merge( $data, $itemInProgressData );
	$itemInProgressData[ 'rpt' ] = true;

	$item[ 'd' ] = Gen::Serialize( $itemInProgressData );
	$aProgress[ $id ] = $item;
}

function CachePostPreparePageEx( $url, $siteId, $priority, $priorityInitiator, $headers = null, $time = null, $redirIdx = null, $lrnId = null )
{

	if( !$url )
		return( false );

	if( $priority == -480 && !$lrnId )
		return( false );

	if( $priorityInitiator === null )
		$priorityInitiator = $priority;

	$settCache = Gen::GetArrField( Plugin::SettGet(), array( 'cache' ), array() );

	if( $time === null )
		$time = microtime( true );

	if( !is_array( $headers ) )
		$headers = array();

	$id = $url . $priority;
	$idLearn = $url . -480;
	foreach( $headers as $header => $headerVal )
	{
		$headerVal = $header . ( is_array( $headerVal ) ? implode( '', $headerVal ) : $headerVal );
		$id .= $headerVal;
		$idLearn .= $headerVal;
	}
	$id = md5( $id, true );
	$idLearn = md5( $idLearn, true );

	$viewName = null;
	if( $viewsDeviceGrp = GetCacheViewDeviceGrp( $settCache, strtolower( isset( $headers[ 'X-Seraph-Accel-Postpone-User-Agent' ] ) ? $headers[ 'X-Seraph-Accel-Postpone-User-Agent' ] : (isset($headers[ 'User-Agent' ])?$headers[ 'User-Agent' ]:'') ) ) )
		$viewName = GetViewDeviceGrpNameFromData( $viewsDeviceGrp );

	$dirQueue = GetCacheDir() . '/q/' . $siteId;

	if( $priority == 10 )
	{
		$count = 0;
		{
			$lock = new Lock( 'l', $dirQueue );
			if( $lock -> Acquire() )
			{
				$aInitial = new ArrayOnFiles( Queue_GetStgPrms( $dirQueue, 0 ) );
				$count = $aInitial -> count();
				$aInitial -> dispose();

				$lock -> Release();
			}

			unset( $aInitial, $lock );
		}

		if( $count > 1000 )
		{

			return( null );
		}
	}

	$data = array( 'p' => $priorityInitiator, 'u' => $url, 'h' => $headers, 'v' => $viewName );
	if( $redirIdx )
		$data[ 'rdr' ] = $redirIdx;
	if( $lrnId )
		$data[ 'l' ] = $lrnId;

	{
		$lock = new Lock( 'l', $dirQueue );
		if( !$lock -> Acquire() )
		{

			return( false );
		}

		$aInitial = new ArrayOnFiles( Queue_GetStgPrms( $dirQueue, 0 ) );
		$aProgress = new ArrayOnFiles( Queue_GetStgPrms( $dirQueue, 1 ) );
		$aDone = new ArrayOnFiles( Queue_GetStgPrms( $dirQueue, 2 ) );

		unset( $aDone[ $id ] );

		$res = false;
		if( !isset( $aProgress[ $id ] ) )
		{
			$aInitial[ $id ] = array( 'p' => $priority, 't' => $time, 'd' => Gen::Serialize( $data ) );
			$res = true;
		}

		if( $priority !== 10 )
		{
			_CachePostPreparePageEx_StopAndRepeat( $aProgress, $id, $data );
			if( $id != $idLearn )
				_CachePostPreparePageEx_StopAndRepeat( $aProgress, $idLearn );
		}

		$aInitial -> dispose(); $aProgress -> dispose(); $aDone -> dispose(); $lock -> Release();
		unset( $aInitial, $aProgress, $aDone, $lock );
	}

	return( $res );
}

function CacheQueueDelete( $siteId )
{
	$res = true;
	foreach( ( $siteId ? array( $siteId ) : GetSiteIds() ) as $siteIdEnum )
	{
		$dirQueue = GetCacheDir() . '/q/' . $siteIdEnum;

		$lock = new Lock( 'l', $dirQueue );
		if( !$lock -> Acquire() )
		{
			$res = false;
			continue;
		}

		$aInitial = new ArrayOnFiles( Queue_GetStgPrms( $dirQueue, 0 ) );
		$aDone = new ArrayOnFiles( Queue_GetStgPrms( $dirQueue, 2 ) );

		$aInitial -> clear();
		$aDone -> clear();

		$aInitial -> dispose(); $aDone -> dispose(); $lock -> Release();
		unset( $aInitial, $aDone, $lock );
	}

	return( $res );
}

function GetExtContents( $url, &$contMimeType = null, $userAgentCmn = true, $timeout = 30, $rememberServerState = true )
{

	$args = array( 'sslverify' => false, 'timeout' => $timeout );
	if( $userAgentCmn )
		$args[ 'user-agent' ] = 'Mozilla/99999.9 AppleWebKit/9999999.99 (KHTML, like Gecko) Chrome/999999.0.9999.99 Safari/9999999.99 seraph-accel-Agent/2.22.16';

	global $seraph_accel_g_aGetExtContentsFailedSrvs;

	if( $serverId = Net::UrlParse( $url ) )
		$serverId = Net::UrlDeParse( $serverId, 0, array( PHP_URL_USER, PHP_URL_PASS, PHP_URL_PATH, PHP_URL_QUERY, PHP_URL_FRAGMENT ) );

	if( $rememberServerState && $serverId && Gen::HrFail( (isset($seraph_accel_g_aGetExtContentsFailedSrvs[ $serverId ])?$seraph_accel_g_aGetExtContentsFailedSrvs[ $serverId ]:null) ) )
		return( false );

	$res = Wp::RemoteGet( $url, $args );
	$hr = Net::GetHrFromWpRemoteGet( $res );
	if( Gen::HrFail( $hr ) )
	{

		if( $rememberServerState && ( $hr == Gen::E_TIMEOUT || $hr == Net::E_TIMEOUT ) && $serverId )
			$seraph_accel_g_aGetExtContentsFailedSrvs[ $serverId ] = $hr;
		return( false );
	}

	$contMimeType = ( string )wp_remote_retrieve_header( $res, 'content-type' );

	if( ( $nPos = strpos( $contMimeType, ';' ) ) !== false )
		$contMimeType = substr( $contMimeType, 0, $nPos );
	$contMimeType = trim( $contMimeType );

	return( wp_remote_retrieve_body( $res ) );
}

function CacheExtractPreparePageParams( &$args )
{
	$prms = (isset($args[ 'seraph_accel_prep' ])?$args[ 'seraph_accel_prep' ]:null);
	if( !$prms )
		return( null );

	Net::CurRequestRemoveArgs( $args, array( 'seraph_accel_prep' ) );

	$prms = @json_decode( @base64_decode( Gen::SanitizeTextData( $prms ) ), true );
	if( hash_hmac( 'md5', '' . (isset($prms[ '_tm' ])?$prms[ '_tm' ]:null), GetSalt() ) != (isset($prms[ 'nonce' ])?$prms[ 'nonce' ]:null) )
		return( false );

	unset( $prms[ '_tm' ] );
	unset( $prms[ 'nonce' ] );

	if( isset( $prms[ 'pc' ] ) )
		$prms[ 'pc' ] = ProcessCtlData_GetFullPath( $prms[ 'pc' ] );

	if( isset( $_SERVER[ 'HTTP_X_SERAPH_ACCEL_GEO_REMOTE_ADDR' ] ) )
		$_SERVER[ 'REMOTE_ADDR' ] = $_SERVER[ 'HTTP_X_REAL_IP' ] = $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] = $_SERVER[ 'HTTP_X_SERAPH_ACCEL_GEO_REMOTE_ADDR' ];

	return( $prms );
}

function GetCacheViewDeviceGrp( $settCache, $userAgent )
{
	if( !(isset($settCache[ 'views' ])?$settCache[ 'views' ]:null) )
		return( null );

	$viewsDeviceGrps = Gen::GetArrField( $settCache, array( 'viewsDeviceGrps' ), array() );

	foreach( $viewsDeviceGrps as $viewsDeviceGrp )
	{
		if( !(isset($viewsDeviceGrp[ 'enable' ])?$viewsDeviceGrp[ 'enable' ]:null) )
			continue;

		$a = implode( ' ', Gen::GetArrField( $viewsDeviceGrp, array( 'agents' ), array() ) );
		if( $a && strpos( $userAgent, $a ) !== false )
			return( $viewsDeviceGrp );
	}

	foreach( $viewsDeviceGrps as $viewsDeviceGrp )
	{
		if( !(isset($viewsDeviceGrp[ 'enable' ])?$viewsDeviceGrp[ 'enable' ]:null) )
			continue;

		if( MatchUserAgentExpressions( $userAgent, Gen::GetArrField( $viewsDeviceGrp, array( 'agents' ), array() ) ) )
			return( $viewsDeviceGrp );
	}

	return( null );
}

function GetCurHdrsToStoreInCache( $settCache )
{
	$res = array();

	$hdrPatterns = (isset($settCache[ 'hdrs' ])?$settCache[ 'hdrs' ]:null);
	if( !$hdrPatterns )
		return( $res );

	foreach( headers_list() as $hdr )
	{
		foreach( $hdrPatterns as $hdrPattern )
		{
			if( @preg_match( $hdrPattern, $hdr ) )
			{
				$res[] = $hdr;
				break;
			}
		}
	}

	return( $res );
}

function LastWarnDscs_Add( $txt )
{
	global $seraph_accel_g_aLastWarnDsc;
	$seraph_accel_g_aLastWarnDsc[] = $txt;
}

function LastWarnDscs_Get()
{
	global $seraph_accel_g_aLastWarnDsc;
	return( $seraph_accel_g_aLastWarnDsc !== null ? $seraph_accel_g_aLastWarnDsc : array() );
}

function _SetExpirableOption( $option, $value, $timeout )
{
	update_option( $option, $value, false );
	update_option( $option . '_tmMax', time() + $timeout, false );
}

function _DelExpirableOption( $option )
{
	delete_option( $option );
	delete_option( $option . '_tmMax' );
}

function _DelExpiredOption( $option )
{
	$tmMax = get_option( $option . '_tmMax' );
	if( $tmMax && $tmMax < time() )
		_DelExpirableOption( $option );
}

function GetCountryCodeByIp( $settCache, &$ip_address )
{

	$country_code = '^';

	if( !class_exists( 'MaxMind\\Db\\Reader' ) )
	{

		return( $country_code );
	}

	try
	{
		$reader = new \MaxMind\Db\Reader( Gen::GetArrField( $settCache, array( 'viewsGeo', 'fileMmDb' ), '' ) );
		$data = $reader -> get( $ip_address );

		if( isset( $data[ 'country' ][ 'iso_code' ] ) )
			$country_code = strtoupper( $data[ 'country' ][ 'iso_code' ] );

		$reader -> close();
	}
	catch( \Exception $e )
	{
	}

	return( $country_code );
}

function GetViewGeoIdByIp( $settCache, &$ip )
{
	$countryCode = GetCountryCodeByIp( $settCache, $ip );

	$viewGeoId = null;
	$grpIsFirst = true;
	$countryCodeForce = null;
	foreach( Gen::GetArrField( $settCache, array( 'viewsGeo', 'grps' ), array() ) as $grpId => $grp )
	{
		if( !(isset($grp[ 'enable' ])?$grp[ 'enable' ]:null) )
			continue;

		$matched = false;
		$countryCodeFirstTmp = null;
		foreach( Gen::GetArrField( $grp, array( 'items' ), array() ) as $grpItem )
		{
			$aa = ExprConditionsSet_Parse( $grpItem );
			if( $countryCodeFirstTmp === null && ExprConditionsSet_IsTrivial( $aa ) )
				$countryCodeFirstTmp = $grpItem;

			foreach( $aa as $a )
			{
				$v = null;
				if( IsStrRegExp( $a[ 'expr' ] ) )
				{
					if( @preg_match( $a[ 'expr' ], $countryCode ) )
						$v = $countryCode;
				}
				else if( $countryCode === $a[ 'expr' ] )
					$v = $countryCode;

				$matched = ExprConditionsSet_ItemOp( $a, $v );
				if( !$matched )
					break;
			}

			if( $matched )
				break;
		}

		if( $matched )
		{
			$viewGeoId = $grpIsFirst ? '' : $grpId;
			$countryCodeForce = $countryCodeFirstTmp;
			break;
		}

		$grpIsFirst = false;
	}

	if( $viewGeoId === null )
		$viewGeoId = $countryCode;

	if( $countryCodeForce )
	{

	}

	return( $viewGeoId );
}

function DepsExpand( $a, $bExpand = true )
{
	$aRes = array();
	foreach( $a as $type => $aId )
		$aRes[ $type ] = $bExpand ? array_fill_keys( $aId, array() ) : array_keys( $aId );
	return( $aRes );
}

function DepsDiff( $a, $aNew )
{
	$aRes = array_diff_key( $aNew, $a );
	foreach( $a as $type => $aoiCi )
		$aRes[ $type ] = array_diff_key( Gen::GetArrField( $aNew, array( $type ), array() ), $aoiCi );
	return( $aRes );
}

function DepsAdd( &$a, $type, $oiCi )
{
	$a[ $type ][ $oiCi ] = array();
}

function DepsRemove( &$a, $aRem )
{
	foreach( $aRem as $type => $aoiCi )
	{
		foreach( $aoiCi as $oiCi => $v )
			unset( $a[ $type ][ $oiCi ] );

		if( isset( $a[ $type ] ) && !count( $a[ $type ] ) )
			unset( $a[ $type ] );
	}
}

function LogGetRelativeFile()
{
	static $g_fileRel;

	if( $g_fileRel === null )
		$g_fileRel = '/logs/log.' . Gen::GetNonce( 'logFileSfx', GetSalt() ) . '.txt';

	return( $g_fileRel );
}

function LogWrite( $text, $severity = Ui::MsgInfo, $category = 'DEBUG' )
{
	Gen::LogWrite( GetCacheDir() . LogGetRelativeFile(), $text, $severity, $category );
}

