<?php

	function rm_dir($path) {
		$path=($path[strlen($path)-1]=="/") ? $path : $path."/";
		if (file_exists($path)) {
			$dir=dir($path);
			while ($entry=$dir->read()) {
				if ($entry!="." && $entry!="..") {
					if (is_dir($path.$entry)) {
						rm_dir($path.$entry);
					} else {
						unlink($path.$entry);
					}
				}
			}
			rmdir($path);
		}
	}

	function untar($tarfile, $file, $dstdir) {
		global $error, $config;
		if ($config["tar_error_handler"]) {
			$errfile="./tar.errors";
			$handler=sprintf($config["tar_error_handler"], $errfile);
		}

		$command=sprintf($config["tar_untar"], $dstdir, $file, $tarfile, $handler );
		echo " exec($command)\n";
		system($command,$retVar);
		if ($retVar!=0) {
			$error="Error: Can't untar $tarfile, untar failed in $dstdir with errorcode ($retVar)\n";
			if ($errfile && file_exists($errfile)) {
				$error.=implode("",file($errfile));						
				unlink($errfile);
				$error.="\n";
			}
			$result=0;
		} else {
			$result=1; // ok
		}
		return $result;
	}

	function tar($tarfile, $file, $dstdir) {
		global $error, $config;
		if ($config["tar_error_handler"]) {
			$errfile="./tar.errors";
			$handler=sprintf($config["tar_error_handler"], $errfile);
		}

		$command=sprintf($config["tar_tar"], $dstdir, $file, $tarfile, $handler );
		echo " exec($command)\n";
		system($command,$retVar);
		if ($retVar!=0) {
			$error="Error: ($command): Can't tar to $tarfile, tar failed in $dstdir with errorcode ($retVar)\n";
			if ($errfile && file_exists($errfile)) {
				$error.=implode("",file($errfile));						
				unlink($errfile);
				$error.="\n";
			}
			$result=0;
		} else {
			$result=1; // ok
		}
		return $result;
	}
