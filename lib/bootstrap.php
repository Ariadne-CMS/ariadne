<?php

	global $auth_config,$store_config,$cache_config,$session_config,$AR,$ariadne,$ax_config,$ARCurrent,$ARConfig,$ARLoader,$ARnls;

	// declare default object,
	if (!class_exists('baseObject',false)) {
		#[\AllowDynamicProperties]
		class baseObject { }           // do not change
	}

	if(!defined('AriadneBasePath') ) {
		define('AriadneBasePath', $ariadne);
	}

	// Polyfill for 'each' that was removed in php 8
	if (!function_exists('each')) {
	    function each(array &$array) {
		$value = current($array);
		$key = key($array);

		if (is_null($key)) {
		    return false;
		}

		// Move pointer.
		next($array);

		return array(1 => $value, 'value' => $value, 0 => $key, 'key' => $key);
	    }
	}
	// Polyfill for 'create_function' that was removed in php 8
	if (!function_exists('create_function')) {
	    function create_function($args, $code) {
		static $i;

		$namespace = __NAMESPACE__;

		do {
		    $i++;
		    $name = "__{$namespace}_lambda_{$i}";
		} while (\function_exists($name));

		eval("function {$name}({$args}) { {$code} }");

		return $name;
	    }
	}    	

	$loaderType = ($ARLoader)?$ARLoader:'web';

	require_once(AriadneBasePath."/../vendor/autoload.php");
	require_once(AriadneBasePath.'/configs/ariadne.phtml');
	require_once(AriadneBasePath.'/configs/ariadne-default.phtml');
	require_once(AriadneBasePath."/configs/sessions.phtml");
	require_once(AriadneBasePath."/configs/cache.phtml");
	require_once(AriadneBasePath."/configs/authentication.phtml");
	require_once(AriadneBasePath."/configs/store.phtml");
	require_once(AriadneBasePath."/ar.php");
	require_once(AriadneBasePath."/modules/mod_debug.php");
	require_once(AriadneBasePath."/modules/mod_cache.php");

	$AR->context = array();

	require_once(AriadneBasePath."/includes/loader.".$loaderType.".php");
	require_once(AriadneBasePath."/stores/".$store_config["dbms"]."store.phtml");

	require_once(AriadneBasePath."/modules/mod_session.phtml");
	require_once(AriadneBasePath."/modules/mod_auth/".$auth_config['method'].".php");

	$prevARnls = $ARnls;


	$ARnls = ar('nls')->dict($AR->nls->default,null,'ARnls',AriadneBasePath.'/nls/');

	if (is_array($prevARnls)) {
		foreach($prevARnls as $key => $value) {
			$ARnls[$key] = $value;
		}
	}
