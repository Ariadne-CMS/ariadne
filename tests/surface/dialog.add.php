#!/usr/bin/env php
<?php

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
		$targetOb->call($templateName, $args);
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}

	$tests = [
		"dialog.add.php" => [
			[],
			["arNewType" => "paddressbook"],
			["arNewType" => "particle"],
			["arNewType" => "pbookmark"],
			["arNewType" => "pcalendar"],
			["arNewType" => "pcalitem"],
			["arNewType" => "pdir"],
			["arNewType" => "pfile"],
			["arNewType" => "pgroup"],
			["arNewType" => "pdir.groups"],
			["arNewType" => "pldapconnection"],
			["arNewType" => "pdir.libs"],
			["arNewType" => "psection.lib"],
			["arNewType" => "pnewspaper"],
			["arNewType" => "pobject"],
			["arNewType" => "porganization"],
			["arNewType" => "ppage"],
			["arNewType" => "pperson"],
			["arNewType" => "pphoto"],
			["arNewType" => "pphotobook"],
			["arNewType" => "pprofile"],
			["arNewType" => "pdir.profiles"],
			["arNewType" => "pproject"],
			["arNewType" => "pscenario"],
			["arNewType" => "psearch"],
			["arNewType" => "psection"],
			["arNewType" => "pshortcut"],
			["arNewType" => "psite"],
			["arNewType" => "pdir.system"],
			// FIXME: Skipped, requires PHPUnit_Framework_Test
			// ["arNewType" => "punittest"],
			["arNewType" => "puser"],
			["arNewType" => "pdir.users"]
		],
		"dialog.add.form.php" => [
			[],
			["arNewType" => "paddressbook", "showall" => 1],
			["arNewType" => "particle", "showall" => 1],
			["arNewType" => "pbookmark", "showall" => 1],
			["arNewType" => "pcalendar", "showall" => 1],
			["arNewType" => "pcalitem", "showall" => 1],
			["arNewType" => "pdir", "showall" => 1],
			["arNewType" => "pfile", "showall" => 1],
			["arNewType" => "pgroup", "showall" => 1],
			["arNewType" => "pdir.groups", "showall" => 1],
			["arNewType" => "pldapconnection", "showall" => 1],
			["arNewType" => "pdir.libs", "showall" => 1],
			["arNewType" => "psection.lib", "showall" => 1],
			["arNewType" => "pnewspaper", "showall" => 1],
			["arNewType" => "pobject", "showall" => 1],
			["arNewType" => "porganization", "showall" => 1],
			["arNewType" => "ppage", "showall" => 1],
			["arNewType" => "pperson", "showall" => 1],
			["arNewType" => "pphoto", "showall" => 1],
			["arNewType" => "pphotobook", "showall" => 1],
			["arNewType" => "pprofile", "showall" => 1],
			["arNewType" => "pdir.profiles", "showall" => 1],
			["arNewType" => "pproject", "showall" => 1],
			["arNewType" => "pscenario", "showall" => 1],
			["arNewType" => "psearch", "showall" => 1],
			["arNewType" => "psection", "showall" => 1],
			["arNewType" => "pshortcut", "showall" => 1],
			["arNewType" => "psite", "showall" => 1],
			["arNewType" => "pdir.system", "showall" => 1],
			// FIXME: Skipped, requires PHPUnit_Framework_Test
			// ["arNewType" => "punittest", "showall" => 1],
			["arNewType" => "puser", "showall" => 1],
			["arNewType" => "pdir.users", "showall" => 1]
		],
	];

	$foundFailures = false;
	$targetPath = "/";
	foreach ($tests as $templateName => $argVariants) {
		foreach ($argVariants as $args) {
			$testFailed = false;
			$result = runTemplate($targetPath, $templateName, $args);
			if (preg_match_all("/(Deprecated|Fatal|Warning).*/", $result, $matches)) {
				$foundFailues = true;
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
		exit(1);
	} else {
		echo "All clear, no deprecations, warning or fatal messages found." . PHP_EOL;
		exit(0);
	}
