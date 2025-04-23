<?php
	$ARversion = array();
	$ARversion['version'] = '12-rc1';
	$ARversion['date'] = strtotime('2025-04-24');
	$ARversion['year'] = DateTimeImmutable::createFromFormat('U', $ARversion['date'])->format('Y');

