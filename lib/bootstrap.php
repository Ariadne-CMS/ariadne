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

	if (!file_exists(AriadneBasePath."/../vendor/autoload.php")) {
		echo "Vendor autoload.php is missing, please run 'composer install' to fix this.";
		exit();
	}
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

	if ($workspace = getenv("ARIADNE_WORKSPACE")) {
		include_once($store_config['code']."modules/mod_workspace.php");
		$layer = workspace::getLayer($workspace);
		if (!$layer) {
			$layer = 1;
		}

		if ($wspaths = getenv("ARIADNE_WORKSPACE_PATHS")) {
			$wspaths = explode(";", $wspaths);
			foreach ($wspaths as $wspath) {
				if ($wspath != '') {
					$store_config['layer'][$wspath] = $layer;
				}
			}
		} else {
			$store_config['layer'] = array('/' => $layer );
		}
	}

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

	foreach ($AR->PINP_AllowedClasses??[] as $class) {
		ar_pinp::allow($class);
	}
