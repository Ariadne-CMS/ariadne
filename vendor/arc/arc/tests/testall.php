<?php
require_once( __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php');

$path = __DIR__ . '/../vendor/arc/';
$dirs = scandir ( $path );
foreach ( $dirs as $dir ) {
	if ( $dir != '.' && $dir != '..' && is_dir($path.$dir) && is_dir($path.$dir.'/tests/')) {
		$files = scandir( $path.$dir.'/tests/' );
		foreach ( $files as $file ) {
			if ( is_file( $path.$dir.'/tests/'  . $file ) && preg_match( '/^test\..*\.php$/i', $file ) ) {
				include_once( $path.$dir.'/tests/' . $file );
			}
		}
	}
}
?>
