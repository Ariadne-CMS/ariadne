<?php
/*
Author: G.Hoogterp
Email: Gerhard@frappe.xs4all.nl

Adapted by A. van Slooten
Made it more like the PEAR api
Last update: 24 Jun 2009

*/

require_once("Services/Twitter.php");

require_once(dirname(__FILE__).'/../../ar.php');

ar_pinp::allow('ar_connect_twitter', array('init'));
ar_pinp::allow('ar_connect_twitterService', array('sendRequest', 'setOption', 'setOptions'));
ar_pinp::allow('ar_connect_twitterWrapper', array());
ar_pinp::allow('ar_connect_twitterException', array('getCall', 'getResponse'));

class ar_connect_twitter extends arBase {
	private static $allowed_methods = array(
		''					=> array('setOption', 'setOptions'),
		'account' 			=> array('end_session', 'update_delivery_device', 'update_location', 'verify_credentials'),
		'direct_messages'	=> array('destroy', 'new'),
		'favorites'			=> array('create', 'destroy'),
		'friendships'		=> array('create', 'destroy'),
		'notifications'		=> array('follow', 'leave'),
		'search'			=> array('query', 'trends'),
		'statuses'			=> array('destroy', 'followers', 'friends', 'show', 'update', 'user_timeline'),
		'users'				=> array('show')
	);

	public static function init($username, $password) {
		return new ar_connect_twitterService($username, $password);
	}
	
	public static function isAllowed($section, $method) {
		return (in_array($method, self::$allowed_methods[$section]) || in_array($method, self::$allowed_methods['']));
	}
}

class ar_connect_twitterService extends arBase {
	private $object = null;
	private $service = null;
	
	private $methods = array(
		'account' 			=> null,
		'direct_messages'	=> null,
		'favorites'			=> null,
		'friendships'		=> null,
		'notifications'		=> null,
		'search'			=> null,
		'statuses'			=> null,
		'users'				=> null
	);

	
	public function __construct($username, $password) {
		$context = pobject::getContext();
		$this->object = $context['arCurrentObject'];
		$this->service = new Services_Twitter($username, $password);
	}

	public function __get($name) {
		if (!$this->methods[$name]) {
			$method = new ar_connect_twitterWrapper($this->service->$name, $name);
			if ($method) {
				$this->methods[$name] = $method;
				return $this->methods[$name];
			}
		}
	}
	
	public function __call($name, $arguments) {
		if ($name[0]=='_') {
			$name = substr($name, 1);
		}
		if (arPinp::isAllowed($this, $name)) {
			return call_user_func_array(array($this->service, $name), $arguments);
		}
	}
}

class ar_connect_twitterWrapper extends arBase {
	private $wrapped = null;
	private $section = '';
	
	public function __construct($wrapped, $section='') {
		$this->wrapped = $wrapped;
		$this->section = $section;
	}
	
	public function __call($name, $arguments) {
		if ($name[0]=='_') {
			$realName = substr($name, 1);
		} else {
			$realName = $name;
		}
		if (ar_connect_twitter::isAllowed($this->section, $realName)) {
			try {
				$result = call_user_func_array(array($this->wrapped, $realName), $arguments);
			} catch(Services_Twitter_Exception $e) {
				return new ar_error('Error in ar\connect\twitter calling '.$e->getCall(), 1);
			}
			if (is_object($result)) {
				return new ar_connect_twitterWrapper($result);
			}
		} else {
			return parent::__call($name, $arguments);
		}
	}
	
	public function __get($name) {
		if ($name[0]=='_') {
			$name = substr($name, 1);
		}
		return $this->wrapped->$name;
	}
	
	public function __set($name, $value) {
		if ($name[0]=='_') {
			$name = substr($name, 1);
		}
		$this->wrapped->$name =  $value;
	}
}

?>