<?php
	$ARversion = array();
	$ARversion['version'] = '12rc1';
	$ARversion['date'] = strtotime('2025-03-01');
	$ARversion['year'] = DateTimeImmutable::createFromFormat('U', $ARversion['date'])->format('Y');

