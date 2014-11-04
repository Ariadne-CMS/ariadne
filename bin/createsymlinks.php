#!/usr/bin/env php
<?php



// EDIT CREATESYMLINKS.INI TO ADD EXTENSIONS

	$OK = "\x1B[1m\x1B[32m[OK]\x1B[0m";
	$FAILED = "\x1B[1m\x1B[31m[FAILED]\x1B[0m";
	$COLS = 50;
	$silent = false;

	if (($argv[1] == '--silent') || ($argv[1] == '-s')) {
		$silent = true;
	} else if (($argv[1] == '--verbose') || ($argv[1] == '-v')) {
		$verbose = true;
	} else if (($argv[1] == '--help') || ($argv[1] == '-h')) {
		echo "Usage:\n" . ltrim($argv[0], "./") . " [options]\n\n";
		echo str_pad("-h, --help", 25) . "This message\n";
		echo str_pad("-s, --silent", 25) . "Don't produce any output on console\n";
		echo str_pad("-v, --verbose", 25) . "Produce verbose output\n";
		exit();
	}

	function makedir($path) {
		global $silent;
		global $verbose;
		global $OK;
		global $FAILED;
		global $COLS;

		if ($path[strlen($path)-1] !== '/') {
			$path .= '/';
		}

		if (preg_match('|(/?.*/)?(.+/)|i', $path, $regs)) {
			$parent = $regs[1];

			if ($parent && !file_exists($parent)) {
				makedir($parent);
			}
		}

		if (!file_exists($path)) {
			if(!$silent) {
			}
			if(mkdir($path, 0775)) {
				if($verbose) {
					echo str_pad("Creating path: " . basename($path), $COLS);
					echo $OK."\n";
				}
			} else {
				if(!$silent) {
					echo str_pad("Creating path: " . basename($path), $COLS);
					echo $FAILED." (permission denied)\n";
				}
			}
		}
	}

	function createsyms($srcdir, $dstdir){
		global $OK;
		global $FAILED;
		global $COLS;
		global $silent;
		if( !file_exists($srcdir) || !file_exists($dstdir) ) {
			if( !$silent ) {
				echo str_pad("Linking: " . str_replace(getcwd()."/", "", $srcdir)." to ". str_replace(getcwd()."/", "", $dstdir), $COLS);
				echo $FAILED;
				echo " (";
				echo !file_exists($srcdir) ? "source does not exist" : "destination does not exist";
				echo ")\n";
			}
			return;
		}
		if( !$silent ) {
			echo str_pad("Linking: " . str_replace(getcwd()."/", "", $srcdir)." to ". str_replace(getcwd()."/", "", $dstdir), $COLS);
			echo $OK."\n";
		}
		createsyms_rec($srcdir, $dstdir, $srcdir);
	}

	function createsyms_rec(&$srcdir, &$dstdir, $path) {
		global $silent;
		global $verbose;
		global $OK;
		global $FAILED;
		global $COLS;

		$dh = @opendir($path);

		while ($file = @readdir($dh)) {
			if ($file != "." && $file != "..") {
				$f = $path . $file;
				if (is_file($f)) {
					/* do not link backupfiles */
					if (!preg_match('/^.*~$/i', $f)) {
						$targetpath = $dstdir . substr($path, strlen($srcdir));
						$target = $targetpath . $file;

						if (!file_exists($targetpath)) {
							makedir($targetpath);
						}

						if (@is_link($target)) {
							unlink($target);
						}

						$symError = "";
						ob_start();
							$symResult = symlink($f, $target);
							if (!$symResult) {
								$symError = str_replace("\n", "", ob_get_contents());
							}
						ob_end_clean();

						if($symResult) {
							if($verbose) {
								echo str_pad("Creating link: " . basename($target), $COLS) . $OK."\n";
							}
						} else {
							if(!$silent) {
								echo str_pad("Creating link: " . substr( $path, strlen($srcdir) ).basename($target), $COLS) . $FAILED;
								echo " ($symError)\n";
							}
						}
					}
				} else if (is_dir($f)) {
					/* skip CVS directories */
					if ($file != "CVS" && $file != ".svn" && $file != ".git") {
						$targetpath = $dstdir.substr("$path$file/", strlen($srcdir));
						makedir($targetpath);
						createsyms_rec($srcdir,$dstdir,"$f/");
					}
				}
			}
		}
		@closedir($dh);
	}
	if(!$silent) {
		echo "\nStarting link procedure, fasten seatbelts please!\n\n";
	}


	$settings = parse_ini_file("createsymlinks.ini", true);

	$root = "../../";
	chdir($root);

	$destination = getcwd()."/".$settings["destination"];
	$local = getcwd()."/".$settings["local"];
	$ariadne = getcwd()."/".$settings["ariadne"];
	$extensions = $settings["extensions"];

	createsyms($ariadne, $destination);

	if( is_array( $extensions ) ) {
		foreach( $extensions as $extension ) {
			createsyms(getcwd()."/".$extension, $destination);
		}
	}

	createsyms($local, $destination);
?>
