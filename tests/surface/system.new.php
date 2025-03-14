#!/usr/bin/env php
<?php
	$targetPath = "/system/temp/";
	$uid = time();
	$tests = [
		"system.new.phtml" => [
			[
				"arNewType" => "paddressbook",
				"arNewFilename" => "paddressbook-" . $uid,
				"en" => [
					"name" => "paddressbook-" . $uid,
				],
			],
			[
				"arNewType" => "particle",
				"arNewFilename" => "particle-" . $uid,
				"en" => [
					"name" => "particle-" . $uid,
				],
			],
			[
				"arNewType" => "pbookmark",
				"arNewFilename" => "pbookmark-" . $uid,
				"en" => [
					"name" => "pbookmark-" . $uid,
				],
				"value" => "https://www.muze.nl"
			],
			[
				"arNewType" => "pcalendar",
				"arNewFilename" => "pcalendar-" . $uid,
				"en" => [
					"name" => "pcalendar-" . $uid,
				],
			],
			[
				"arNewType" => "pcalitem",
				"arNewFilename" => "pcalitem-" . $uid,
				"en" => [
					"name" => "pcalitem-" . $uid,
				],
			],
			[
				"arNewType" => "pdir",
				"arNewFilename" => "pdir-" . $uid,
				"en" => [
					"name" => "pdir-" . $uid,
					"summary" => "This is the summary for pdir-$uid",
					"page" => "This is the page. so much test wow"
				],
			],
			[
				"arNewType" => "pdir.groups",
				"arNewFilename" => "pdir.groups-" . $uid,
				"en" => [
					"name" => "pdir.groups-" . $uid,
				],
			],
			[
				"arNewType" => "pdir.libs",
				"arNewFilename" => "pdir.libs-" . $uid,
				"en" => [
					"name" => "pdir.libs-" . $uid,
				],
			],
			[
				"arNewType" => "pdir.profiles",
				"arNewFilename" => "pdir.profiles-" . $uid,
				"en" => [
					"name" => "pdir.profiles-" . $uid,
				],
			],
			[
				"arNewType" => "pdir.users",
				"arNewFilename" => "pdir.users-" . $uid,
				"en" => [
					"name" => "pdir.users-" . $uid,
				],
			],
			[
				"arNewType" => "pdir.system",
				"arNewFilename" => "pdir.system-" . $uid,
				"en" => [
					"name" => "pdir.system-" . $uid,
				],
			],
			[
				"arNewType" => "pfile",
				"arNewFilename" => "pfile-" . $uid,
				"en" => [
					"name" => "pfile-" . $uid,
				],
			],
			[
				"arNewType" => "pgroup",
				"arNewFilename" => "pgroup-" . $uid,
				"name" => "pgroup-" . $uid,
				"email" => "group@example.com"
			],
			[
				"arNewType" => "pnewspaper",
				"arNewFilename" => "pnewspaper-" . $uid,
				"en" => [
					"name" => "pnewspaper-" . $uid,
				],
			],
			[
				"arNewType" => "pobject",
				"arNewFilename" => "pobject-" . $uid,
				"en" => [
					"name" => "pobject-" . $uid,
				],
				"value" => $uid
			],
			[
				"arNewType" => "porganization",
				"arNewFilename" => "porganization-" . $uid,
				"en" => [
					"name" => "porganization-" . $uid
				],
			],
			[
				"arNewType" => "ppage",
				"arNewFilename" => "ppage-" . $uid,
				"en" => [
					"name" => "ppage-" . $uid,
					"summary" => "This is the summary for ppage-$uid",
					"page" => "This is the page. so much test wow"
				],
			],
			[
				"arNewType" => "pperson",
				"arNewFilename" => "pperson-" . $uid,
				"lastname" => "Tester",
				"en" => [
					"name" => "pperson-" . $uid
				],
			],
			[
				"arNewType" => "pproject",
				"arNewFilename" => "pproject-" . $uid,
				"en" => [
					"name" => "pproject-" . $uid,
				],
				"scaffold" => "/system/scaffolds/muze/pproject/default/"
			],
			[
				"arNewType" => "psection",
				"arNewFilename" => "psection-" . $uid,
				"en" => [
					"name" => "psection-" . $uid,
				],
			],
			[
				"arNewType" => "psection.lib",
				"arNewFilename" => "psection.lib-" . $uid,
				"en" => [
					"name" => "psection.lib-" . $uid,
				],
			],
			[
				"arNewType" => "psite",
				"arNewFilename" => "psite-" . $uid,
				"en" => [
					"name" => "psite-" . $uid,
					"url" => "https://www.example.com"
				],
			],
			[
				"arNewType" => "puser",
				"arNewFilename" => "puser-" . $uid,
				"name" => "puser-$uid",
				"newpass1" => $uid,
				"newpass2" => $uid
			],
			[
				"arNewType" => "pphotobook",
				"arNewFilename" => "pphotobook-" . $uid,
				"en" => [
					"name" => "pphotobook-" . $uid,
				],
			],
			[
				"arNewType" => "pphoto",
				"arNewFilename" => "pphoto-" . $uid,
				"en" => [
					"name" => "pphoto-" . $uid,
				],
			],
			[
				"arNewType" => "pprofile",
				"arNewFilename" => "pprofile-" . $uid,
				"name" => "pprofile-$uid",
			],
			[
				"arNewType" => "pscenario",
				"arNewFilename" => "pscenario-" . $uid,
				"en" => [
					"name" => "pscenario-$uid",
				]
			],
			[
				"arNewType" => "pshortcut",
				"arNewFilename" => "pshortcut-" . $uid,
				"en" => [
					"name" => "pshortcut-$uid",
				]
			],
			[
				"arNewType" => "psearch",
				"arNewFilename" => "psearch-" . $uid,
				"en" => [
					"name" => "psearch-$uid",
				]
			],
/*
			// FIXME: Skipped, should be removed from Ariadne
			["arNewType" => "pldapconnection"],
			// FIXME: Skipped, requires PHPUnit_Framework_Test
			// ["arNewType" => "punittest"],
*/
		]
	];
	
	include_once("testRunner.php");
