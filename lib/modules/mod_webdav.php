<?php
	require_once("HTTP/WebDAV/Server.php");

	class Ariadne_WebDAV_Server extends HTTP_WebDAV_Server {

		function Ariadne_WebDAV_Server( &$store ) {
		global $ariadne;
			debug("webdav: initting server");
			$this->HTTP_WebDAV_Server();
			$this->store = $store;
			debug("webdav: loading modules");

			$this->modules = Array();
			include_once($ariadne."/modules/mod_webdav/files.php");
			$this->modules['files'] = new WebDAV_files($this);

			debug("webdav: init done");
		}

		function check_auth($type, $user, $pass) {
		global $AR, $ARCurrent, $auth_config;
			debug("webdav:check_auth  $type:$user:$pass;");
			$auth_class = "mod_auth_".$auth_config['method'];

			debug("webdav:check_auth using $auth_class module");

			$mod_auth = new $auth_class($auth_config);

			/* FIXME: make this configurable */
			if (eregi('^Microsoft Data Access Internet', $_SERVER['HTTP_USER_AGENT'])) {
				debug("webdav:check_auth using sessions");
				$this->http_auth_realm = "Ariadne WebDAV: ".$ARCurrent->session->id;
				if (!$ARCurrent->session || !$ARCurrent->session->id) {
					ldStartSession();
				}
				if ($ARCurrent->session->get('ARSessionTimedout', 1)) {
					/* find a better solution than to just kill the session */
					$ARCurrent->session->kill();
					ldStartSession();
				} elseif ($user) {
					$result = $mod_auth->checkLogin($user, $pass);
				}
			} else {
				debug("webdav:check_auth no session support");
				$this->http_auth_realm = "Ariadne WebDAV";
				// do HTTP Basic Auth only, so no session stuff
				global $LD_NO_SESSION_SUPPORT;
				$LD_NO_SESSION_SUPPORT = true;
				if ($user) {
					$result = $mod_auth->checkLogin($user, $pass);
				}
			}
			if ($result === true) {
				debug("webdav:check_auth success");
				debug("webdav:check_auth user loaded: ".$AR->user->data->login);
				return true;
			} else {
				debug("webdav:check_auth failed");
				return false;
			}
		}

		function get_info($list) {
			$result = Array();
			$props = $list['props'];
			if (is_array($props)) {
				foreach ($props as $name => $val) {
					$result['props'][] = $this->mkprop($name, $val);
				}
			}
			$result['path'] = $list['path'];
			return $result;
		}

		function propfind(&$options, &$files) {
			debug("webdav:propfind [end]");
			return $this->modules['files']->propfind($options, $files);
		}

		function head($options) {
			debug("webdav:head [$status]");
			return $this->modules['files']->head($options);
		}

		function delete($options) {
			debug("webdav:delete [$status]");
			return $this->modules['files']->delete($options);
		}

		function lock( &$options ) {
			debug("method lock called");
		}

		function checkLock($path) {
			debug("method check lock");
		}

		function mkcol($options) {
			debug("webdav:mkcol [$status]");
			return $this->modules['files']->mkcol($options);
		}

		function get(&$options) {
			debug("webdav:get [$status]");
			return $this->modules['files']->get($options);
		}


		function move($options) {
			debug("webdav:move [$status]");
			return $this->modules['files']->move($options);
		}

		function put(&$params) {
		global $ARCurrent;
			debug("webdav:put [$status]");
			return $this->modules['files']->put($params);
		}



	} // end class definition
?>