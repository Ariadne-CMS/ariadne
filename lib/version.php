<?php
	$ARversion = array();
	$ARversion['version'] = '12.1';
	$ARversion['date'] = strtotime('2026-10-26');
	$ARversion['year'] = DateTimeImmutable::createFromFormat('U', $ARversion['date'])->format('Y');

