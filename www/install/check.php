<?php
	$DEBUGON = true;

	cdebug("check.php START");

	check_file( "../ariadne.inc" );
	cdebug("reading ../ariadne.inc");
	include_once("../ariadne.inc");

	check_file( $ariadne );

	$ariadne_configs = $ariadne."/configs";
	check_file( $ariadne_configs );

	check_file( $ariadne."/configs/ariadne.phtml" );
	cdebug("reading ".$ariadne."/configs/ariadne.phtml");
	require_once($ariadne."/configs/ariadne.phtml");

	check_file( $ariadne."/configs/store.phtml" );
	cdebug("reading ".$ariadne."/configs/store.phtml");
	require_once($ariadne."/configs/store.phtml");

	check_file( $ariadne."/configs/axstore.phtml" );
	cdebug("reading ".$ariadne."/configs/axstore.phtml");
	require_once($ariadne."/configs/axstore.phtml");

	check_file( $store_config['code'] );
	check_file( $store_config['code']."includes/" );

	check_file( $store_config['code']."modules/mod_debug.php" );
	cdebug( "reading ".$store_config['code']."modules/mod_debug.php" );
	include_once( $store_config['code']."modules/mod_debug.php" );

	check_file( $store_config['code']."includes/loader.web.php" );
	cdebug( "reading ".$store_config['code']."includes/loader.web.php" );
	include_once( $store_config['code']."includes/loader.web.php" );

	check_file( $store_config['code']."stores/" );
	check_file( $store_config['code']."stores/".$ax_config["dbms"]."store.phtml" );
	cdebug( "reading ".$store_config['code']."stores/".$ax_config["dbms"]."store.phtml" );
	include_once($store_config['code']."stores/".$ax_config["dbms"]."store.phtml");

	check_file( $store_config['code']."stores/".$store_config["dbms"]."store_install.phtml" );
	cdebug( "reading ".$store_config['code']."stores/".$store_config["dbms"]."store_install.phtml" );
	include_once($store_config['code']."stores/".$store_config["dbms"]."store_install.phtml");

	check_file( $store_config['code']."nls/" );
	check_file( $store_config['code']."nls/".$AR->nls->default );
	cdebug( "reading ".$store_config['code']."nls/".$AR->nls->default );
	include_once( $store_config['code']."nls/".$AR->nls->default );

	$ariadne_files = substr($ariadne, 0, strlen($ariadne)-3)."files";
	check_file( $ariadne_files );
	if( ! is_writable( $ariadne_files ) ) {
		cdebug( "$ariadne_files is not writable" );
		echo "[FATAL] can't write to $ariadne_files even though it exists, check permissions: ";
		display_perms( fileperms($ariadne_files) );
	} else {
		cdebug( "$ariadne_files is writable" );
	}

	cdebug( "check.php END\n\n");

	function check_file( $file ) {

		if( ! file_exists( $file ) ) {
			echo "[FATAL] $file can not be found.\n";
			exit();
		}

		cdebug( "$file exists" );

		if( ! is_readable( $file ) ) {
			echo "[FATAL] can't read $file even though it exists, check permissions: ";
			display_perms( fileperms($file) );
			exit();
		}

		cdebug( "$file is readable" );
		return true;
	}

	function display_perms( $mode ) {
		/* Determine Type */
		if( $mode & 0x1000 ) {
			$type='p'; /* FIFO pipe */
		} else if( $mode & 0x2000 ) {
			$type='c'; /* Character special */
		} else if( $mode & 0x4000 ) {
			$type='d'; /* Directory */
		} else if( $mode & 0x6000 ) {
			$type='b'; /* Block special */
		} else if( $mode & 0x8000 ) {
			$type='-'; /* Regular */
		} else if( $mode & 0xA000 ) {
			$type='l'; /* Symbolic Link */
		} else if( $mode & 0xC000 ) {
			$type='s'; /* Socket */
		} else {
			$type='u'; /* UNKNOWN */
		}

		/* Determine permissions */
		$owner["read"]	 = ($mode & 00400) ? 'r' : '-';
		$owner["write"]	= ($mode & 00200) ? 'w' : '-';
		$owner["execute"] = ($mode & 00100) ? 'x' : '-';
		$group["read"]	 = ($mode & 00040) ? 'r' : '-';
		$group["write"]	= ($mode & 00020) ? 'w' : '-';
		$group["execute"] = ($mode & 00010) ? 'x' : '-';
		$world["read"]	 = ($mode & 00004) ? 'r' : '-';
		$world["write"]	= ($mode & 00002) ? 'w' : '-';
		$world["execute"] = ($mode & 00001) ? 'x' : '-';

		/* Adjust for SUID, SGID and sticky bit */
		if( $mode & 0x800 ) {
			$owner["execute"] = ($owner['execute']=='x') ? 's' : 'S';
		}
		if( $mode & 0x400 ) {
			$group["execute"] = ($group['execute']=='x') ? 's' : 'S';
		}
		if( $mode & 0x200 ) {
			$world["execute"] = ($world['execute']=='x') ? 't' : 'T';
		}

		printf("%1s", $type);
		printf("%1s%1s%1s", $owner['read'], $owner['write'], $owner['execute']);
		printf("%1s%1s%1s", $group['read'], $group['write'], $group['execute']);
		printf("%1s%1s%1s\n", $world['read'], $world['write'], $world['execute']);
	}

	function cdebug( $text ) {
		global $DEBUGON;

		if( $DEBUGON ) {
			echo "[CHECK] ".$text."\n";
		}
	}

?>
