#!/usr/bin/env php
<?php
	if (!isset($tests)) {
		echo "OK" . PHP_EOL;
		exit(0);
	}
	
	$ARLoader = 'cmd';
	$currentDir = getcwd();
	$ariadne = dirname($currentDir,2).'/lib/';

	if (!@include_once($ariadne."/bootstrap.php")) {
		chdir(substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/')));
		$ariadne = dirname(getcwd()).'/lib/';

		if(!include_once($ariadne."/bootstrap.php")){
			echo "could not find Ariadne";
			exit(1);
		}

		chdir($currentDir);
	}

	$inst_store = $store_config["dbms"]."store";
	$store=new $inst_store($root??null,$store_config);

	/* now load a user (admin in this case)*/
	$login = "admin";
	$query = "object.implements = 'puser' and login.value='$login'";
	$AR->user = current($store->call('system.get.phtml', '', $store->find('/system/users/', $query)));
	
	function runTemplate($targetPath, $templateName, $args) {
		global $store;
		$targetOb = current($store->call("system.get.phtml", '', $store->get($targetPath)));
		ob_start();
		$callResult = $targetOb->call($templateName, $args);
		$result = ob_get_contents();
		ob_end_clean();
		if ($targetOb->error) {
			throw $targetOb->error;
		}
		return $result;
	}

	$foundFailures = false;

	if (!isset($targetPath)) {
		$targetPath = "/";
	}
	foreach ($tests as $templateName => $argVariants) {
		foreach ($argVariants as $args) {
			$testFailed = false;
			$result = runTemplate($targetPath, $templateName, $args);
			if (preg_match_all("/(Deprecated|Fatal|Warning):.*/", $result, $matches)) {
				if (!$foundFailures) {
					echo "FAILED" . PHP_EOL;
				}
				$foundFailures = true;
				$testFailed = true;
				echo "-----------------------------------" . PHP_EOL;
				echo "Failures in test $templateName with args " . json_encode($args) . PHP_EOL;
				echo "-----------------------------------" . PHP_EOL;
				foreach ($matches[0] as $match) {
					echo $match . PHP_EOL;
				}
				echo "-----------------------------------" . PHP_EOL;
			}
		}
	}

	$store->close();

	if ($foundFailures) {
		echo "Found failures!" . PHP_EOL;
		exit(1);
	} else {
		echo "OK" . PHP_EOL;
		exit(0);
	}
