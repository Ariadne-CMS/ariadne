<?php
    /******************************************************************
     loader.web.php                                        Muze Ariadne
     ------------------------------------------------------------------
     Author: Muze (info@muze.nl)
     Date: 11 december 2002

     Copyright 2002 Muze

     This file is part of Ariadne.

     Ariadne is free software; you can redistribute it and/or modify
     it under the terms of the GNU General Public License as published 
     by the Free Software Foundation; either version 2 of the License, 
     or (at your option) any later version.
 
     Ariadne is distributed in the hope that it will be useful,
     but WITHOUT ANY WARRANTY; without even the implied warranty of
     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     GNU General Public License for more details.

     You should have received a copy of the GNU General Public License
     along with Ariadne; if not, write to the Free Software 
     Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  
     02111-1307  USA

    -------------------------------------------------------------------

     Description:

       This is loader that contains all functions for the Ariadne web
       interface.

    ******************************************************************/

	$ERRMODE="htmljs"; // alternative: "text"/"html"/"js"

	define('LD_ERR_ACCESS', -1);
	define('LD_ERR_SESSION', -2);

	include_once($store_config['code']."modules/mod_debug.php");
	
	function debug_print( $text ) {
		echo "<b>".$text."</b><br>";
		flush();
	}

	function error($text) {
		global $ERRMODE;
		switch ($ERRMODE) {
			case "html" :
				echo "<b><font color='red'>Error: $text</font></b><BR>\n";
				break;
			case "js" :
				echo "\nalert('Error: $text');\n";
				break;
			case "text" :
				echo "\nERROR: $text\n";
				break;
			case "htmljs" :
			default: 
				echo "// <b><font color='red'>Error: $text</font></b><BR>\n<!--\nalert('Error: $text');\n// -->\n";
				break;
		}
	}

	function ldAuthUser($login, $password) {
	global $ARLogin, $ARPassword, $store, $AR;
		$criteria["object"]["implements"]["="]="'puser'";
		$criteria["login"]["value"]["="]="'".AddSlashes($login)."'";
		$user = current(
					$store->call(
						"system.authenticate.phtml",
						Array(
							"ARPassword" => $password
						),
						$store->find("/system/users/", $criteria)
					));
		if ($user) {
			if ($login !== "public") {
				/* welcome to Ariadne :) */
				ldSetCredentials($login, $password);
			}
			$ARLogin = $user->data->login;
			$ARPassword = 0;
			$AR->user = $user;
			$result = true;
		} else {
			debug("ldAuthUser: user('$user') could not authenticate", "all");
			$result = LD_ERR_ACCESS;
		}
		return $result;
	}

	function ldCheckLogin($login, $password) {
	global $ARCurrent, $AR;
		debug("ldCheckLogin($login, [password])", "all");
		if ($login) {
			debug("ldCheckLogin: initiating new login ($login)", "all");
			if ($ARCurrent->session) {
				if (!$ARCurrent->session->get("ARLogin") ||
						$ARCurrent->session->get("ARLogin") == "public") {
					debug("ldCheckLogin: logging into a public session (".$ARCurrent->session->id.")", "all");
					$result = ldAuthUser($login, $password);
				} else {
					if (ldCheckCredentials($login, $password)) {
						debug("ldCheckLogin: succesfully logged into private session (".$ARCurrent->session->id.")", "all");
						$result = ldAuthUser($login, $password);
					} else {
						if ($ARCurrent->session->get("ARLogin") == $login) {
							debug("ldCheckLogin: user ($login) tries to login to his session without a cookie set", "all");
							if ($ARCurrent->session->get("ARPassword", true) == $password) {
								debug("ldCheckLogin: user ($login) succesfully aquired session (".$ARCurrent->session->id."); logging in user", "all");
								$result = ldAuthUser($login, $password);
							} else {
								debug("ldCheckLogin: wrong password in session (".$ARCurrent->session->id."); try again", "all");
								$result = LD_ERR_ACCESS;
							}
						} else
						if (ldCheckCredentials($ARCurrent->session->get("ARLogin"), $ARCurrent->session->get("ARPassword", 1)))  {
							debug("ldCheckLogin: user tries to login as another user", "all");
							$result = ldAuthUser($login, $password);
						} else {
							debug("ldCheckLogin: could not login to private session (".$ARCurrent->session->id."): creating a new one", "all");
							ldStartSession();
							$result = ldAuthUser($login, $password);
						}
					}
				}
			} else {
				debug("ldCheckLogin: starting new session", "all");
				ldStartSession();
				$result = ldAuthUser($login, $password);
			}
		} else {
			if ($ARCurrent->session) {
				if (!$ARCurrent->session->get("ARLogin")) {
					if ($ARCurrent->session->get("ARSessionTimedout", 1)) {
						$ARCurrent->session->put("ARSessionTimedout", 0, 1);
					}
					debug("ldCheckLogin: logging in with public session (".$ARCurrent->session->id.")", "all");
					$result = ldCheckLogin("public", "none");
				} else
				if ($ARCurrent->session->get("ARSessionTimedout", 1)) {
					debug("ldCheckLogin: session has been timedout, forcing login", "all");
					$result = LD_ERR_SESSION;
				} else {
					$cookie = ldGetCredentials();
					$cookie_login = $cookie[$ARCurrent->session->id]['login'];
					if ($cookie_login) {
						$login = $ARCurrent->session->get("ARLogin");
						$password = $ARCurrent->session->get("ARPassword", true);
						if (ldCheckCredentials($login, $password)) {
							debug("ldCheckLogin: logging ($login) into a private session (".$ARCurrent->session->id.") with credentials from cookie", "all");
							$result = ldAuthUser($login, $password);
						} else {
							debug("ldCheckLogin: could not login ($login) on private session (".$ARCurrent->session->id.") with credentials from cookie: removing cookie", "all");
							unset($cookie[$ARCurrent->session->id]);
							setcookie("ARCookie", serialize($cookie), 0, '/');
							$result = LD_ERR_ACCESS;
						}
					} else {
						debug("ldCheckLogin: user tried to hijack a session (".$ARCurrent->session->id.") ", "all");
						$result = LD_ERR_ACCESS;
					}
				}
			} else {
				if ($AR->arSessionRespawn) {
					debug("ldCheckLogin: trying to respawn a session", "all");
					$cookie = ldGetCredentials();
					if (is_array($cookie)) {
						reset($cookie);
						while (!$result && (list($sid, $sval)=each($cookie))) {
							ldStartSession($sid);
							$login = $ARCurrent->session->get("ARLogin");
							$password = $ARCurrent->session->get("ARPassword", 1);
							debug("ldCheckLogin: trying to respawn session ($sid) for user ($login)", "all");
							if (ldCheckCredentials($login, $password)) {
								debug("ldCheckLogin: credentials matched, trying to login user", "all");
								$result = ldAuthUser($login, $password);
							} else {
								debug("ldCheckLogin: credentials didn't match", "all");
							}
						}
					}
				} else {
					debug("ldCheckLogin: normal public login", "all");
					$result = ldAuthUser("public", "none");
				}
			}
		}
		return $result;
	}

	function ldRegisterFile($field = "file", &$error) {
	global $ARnls, $store, $HTTP_POST_FILES, $HTTP_POST_VARS;

		require_once($store->code."modules/mod_mimemagic.php");

		$result = Array();

		$file_temp=$HTTP_POST_FILES[$field]['tmp_name'];
		$file=$HTTP_POST_FILES[$field]['name'];
		if ($file && is_uploaded_file($file_temp)) {
			list($inf, $inftp) = virusscan($file_temp);
			if($inf) {
				virusclean($file_temp);
				// This is duplicate in some cases. Should be a bit cleaned up.
				$error = sprintf($ARnls["err:fileuploadvirus"], $inftp);
			} else {
				// new file uploaded -> save it before PHP deletes it
				$file_artemp=tempnam($store->files."temp","upload");
				if (move_uploaded_file($file_temp, $file_artemp)) {
					// now make the new values available to wgWizKeepVars()
					$result[$field]=$file;
					$result[$field."_temp"]=substr($file_artemp,strlen($store->files."temp"));
					$result[$field."_size"]=$HTTP_POST_FILES[$field]['size'];
					$type = get_mime_type($file_artemp);
					if (!$type) {
						$type = get_mime_type($file, MIME_EXT);
					}
					$result[$field."_type"]=$type;
				}
			}
		}
		return $result;
	}

	function ldOnFinish() {
	global $ARCurrent, $store;

		if ($ARCurrent->session) {
			$ARCurrent->session->save();
		}
		if ($store) {
			$store->close();
		}
	}

	function ldObjectNotFound($requestedpath, $requestedtemplate) {
	global $store, $AR;

		$path=$requestedpath;
		while ($path!=$prevPath && !$store->exists($path)) {
			$prevPath=$path;
			$path=$store->make_path($path, "..");
		}
		if ($prevPath==$path) {
			error("Database is not initialised, please run <a href=\"".$AR->dir->www."install/install.php\">the installer</a>");
		} else {
			// no results: page couldn't be found, show user definable 404 message
			$store->call("user.notfound.html",
				 Array(	"arRequestedPath" => $requestedpath,
				 		"arRequestedTemplate" => $requestedtemplate ),
				 $store->get($path));
		}

	}


	function ldAccessDenied($path, $message) {
	global $ARCurrent, $store;
		/* 
			since there is no 'peek' function, we need to pop and push
			the arCallArgs variable.
		*/

		$arCallArgs = array_pop($ARCurrent->arCallStack);
		array_push($ARCurrent->arCallStack, $arCallArgs);

		if (!$arCallArgs || is_array($arCallArgs)) {
			$arCallArgs["arLoginMessage"] = $message;
		} else {
			$arCallArgs.="&arLoginMessage=".urlencode($message);
		}
		if (!$ARCurrent->arLoginSilent) {
			$ARCurrent->arLoginSilent = true;
			$store->call("user.login.html", 
								$arCallArgs,
								$store->get($path) );
		}

	}

	function ldSetRoot($session='', $nls='') {
	global $store, $AR, $ARCurrent, $root, $rootoptions;

		$root=$AR->root;
		$rootoptions="";
		if ($session && !$AR->hideSessionIDfromURL) {
			$rootoptions.="/-".$session."-";
			$ARCurrent->session->id=$session;
		}
		if ($nls) {
			$rootoptions.="/$nls";
			$ARCurrent->nls=$nls;
		}
		$root.=$rootoptions;
		if ($store) { // loader.php uses this function before the store is initialized.
			$store->root=$root;
			$store->rootoptions=$rootoptions;
		}	
	}

	function ldSetNls($nls) {
	global $ARCurrent;

		$session=$ARCurrent->session->id;
		ldSetRoot($session, $nls);
	}

	function ldSetSession($session='') {
	global $ARCookie, $AR, $ARCurrent;

		$nls=$ARCurrent->nls;
		if ($AR->hideSessionIDfromURL) {
			$cookie = Array();
			$cookie[$ARCurrent->session->id]['timestamp']=time();
			$ARCookie=serialize($cookie);
			debug("setting cookie ($ARCookie)");
			setcookie("ARCookie",$ARCookie, 0, '/');			
		}
		ldSetRoot($session, $nls);
	}
 
	function ldStartSession($sessionid='') {
	global $ARCurrent, $AR, $ariadne;

		require($ariadne."/configs/sessions.phtml");
		$ARCurrent->session=new session($session_config,$sessionid);
		ldSetSession($ARCurrent->session->id);
	}

	function ldSetCache($file, $time, $image, $headers) {
	global $store;

		debug("ldSetCache($file, $time, [image], [headers])","object");
		if ($time==-2) {
			$time=0;
		} else {
			$time=time()+($time*3600);
		}
		if (!ereg("\.\.",$file)) {
			if ($image) {
				$path=substr($file, 1, strrpos($file, "/")-1);
				if (!file_exists($store->files."cache/".$path)) {
					ldMkDir("cache/".$path);
					ldMkDir("cacheheaders/".$path);
				}
				$fp=fopen($store->files."cache".$file, "wb");
				fwrite($fp, $image);
				fclose($fp);
				$fp=fopen($store->files."cacheheaders".$file, "wb");
				fwrite($fp, $headers);
				fclose($fp);
				if (!touch($store->files."cache".$file, $time)) {
					debug("ldSetCache: ERROR: couldn't touch image","object");
				}
			}
		}
	}

	function ldMkDir($dir) {
	global $store;

		debug("ldMkDir($dir)","object");
		$dir=strtok($dir, "/");
		$curr=$store->files;
		while ($dir) {
			$curr.=$dir."/";
			debug("ldMkDir: $curr","all");
			@mkdir($curr, 0755);
			$dir=strtok("/");
		}
	}

	function ldSetCredentials($login, $password) {
	global $ARCurrent, $AR, $HTTP_COOKIE_VARS;

		// Make sure the login is lower case. Because of the
		// numerous checks on "admin".
		$login = strtolower( $login );

		$ARCookie = stripslashes($HTTP_COOKIE_VARS["ARCookie"]);

		debug("ldSetCredentials($login, [password])","object");

		if (!$ARCurrent->session) {
			ldStartSession();
		} else {
			/* use the same sessionid if the user didn't login before */
			ldStartSession($ARCurrent->session->id);
		}
		$ARCurrent->session->put("ARLogin",$login);
		$ARCurrent->session->put("ARPassword",$password,1);

		/* now save our session */
		$ARCurrent->session->save();

		if (!$AR->hideSessionIDfromURL) {
			$cookie=unserialize($ARCookie);
		} else {
			// If we are hiding the session id from the URL,
			// there can only be one user per cookie, so we
			// throw the old stuff away
			$cookie=array();
		}

		// FIXME: now clean up the cookie, remove old sessions
		@reset($cookie);
		while (list($sessionid, $data)=@each($cookie)) {
			if (!$ARCurrent->session->sessionstore->exists("/$sessionid/")) {
				// don't just kill it, it may be from another ariadne installation
				if ($data['timestamp']<(time()-86400)) {
					// but do kill it if it's older than one day
					unset($cookie[$sessionid]);
				}
			} 
		}

		$cookie[$ARCurrent->session->id]['login']=$login;
		$cookie[$ARCurrent->session->id]['timestamp']=time();
		$cookie[$ARCurrent->session->id]['check']="{".ARCrypt($password.$ARCurrent->session->id)."}";
		$ARCookie=serialize($cookie);
		debug("setting cookie ($ARCookie)");
		setcookie("ARCookie",$ARCookie, 0, '/');
	}

	function ldGetCredentials() {
	global $HTTP_COOKIE_VARS;
		/* 
			FIXME:
			this is a hack: php 4.0.3pl1 (and up?) runs 'magic_quotes' on
			cookies put in $HTTP_COOKIE_VARS which will cause unserialize
			to not function correctly.
		*/
		$ARCookie = stripslashes($HTTP_COOKIE_VARS["ARCookie"]);
		debug("ldGetCredentials()","object");
		$cookie=unserialize($ARCookie);
		return $cookie;
	}

	function ldCheckCredentials($login, $password) {
	global $ARCurrent, $AR;
		debug("ldCheckCredentials()","object");
		$result=false;
		$cookie=ldGetCredentials();
		if ($login==$cookie[$ARCurrent->session->id]['login']
			&& ($saved=$cookie[$ARCurrent->session->id]['check'])) {
			$check="{".ARCrypt($password.$ARCurrent->session->id)."}";
			if ($check==$saved) {
				$result=true;
			} else {
				debug("login check failed","all");
			}
		} else {
			debug("wrong login or corrupted cookie","all");
		}			
		return $result;
	}

	function ldGetUserCookie($cookiename="ARUserCookie") {
	global $HTTP_COOKIE_VARS;
	
		$cookie = false;
	
		if( $HTTP_COOKIE_VARS[$cookiename] && !($cookiename == "ARCookie")) {
			
			/* 
				FIXME:
				this is a hack: php 4.0.3pl1 (and up?) runs 'magic_quotes' on
				cookies put in $HTTP_COOKIE_VARS which will cause unserialize
				to not function correctly.
			*/
			$ARUserCookie = stripslashes($HTTP_COOKIE_VARS[$cookiename]);
			debug("ldGetUserCookie() = $ARUserCookie","object");
			$cookie=unserialize($ARUserCookie);
		}
		return $cookie;
	}

	function ldSetUserCookie($cookie, $cookiename="ARUserCookie", $expire=null, $path="/", $domain="", $secure=0) {
	global $HTTP_COOKIE_VARS;
		
		$result = false;

		if( $cookiename != "ARCookie") {
			debug("ldSetUserCookie(".serialize($cookie).")","object");
			$ARUserCookie=serialize($cookie);
			$result = setcookie($cookiename,$ARUserCookie, $expire, $path, $domain, $secure);
		}
		
		return $result;
	}

	function ldRedirect($uri) {
		return ldHeader("Location: $uri");
	}

	function ldHeader($header) {
	global $ARCurrent;

		$result=false;
		if (!Headers_sent()) {
			$result=true;
			if (is_array($header)) {
				$header=implode('\n',$header);
			}
			Header($header);
			$ARCurrent->ldHeaders[strtolower($header)]=$header;
		} else {
			debug("Headers already sent, couldn't send $header","all");
		}
		return $result;
	}

	function ldSetClientCache($cache_on, $expires=0, $modified=0) {
		global $ARCurrent;
		$now=time();
		if ($cache_on) {
			if (!$expires) {
				$expires=$now+1800;
			}
			if (!$modified) {
				$modified=$now;
			}
			ldHeader("Pragma: cache");
			ldHeader("Cache-control: cache");
			ldHeader("Expires: ".gmstrftime("%a, %d %b %Y %H:%M:%S GMT",$expires));
			$result=ldHeader("Last-Modified: ".gmstrftime("%a, %d %b %Y %H:%M:%S GMT",$modified));
		} else {
			if (!$modified) {
				$modified=time();
			}
			ldHeader("Pragma: no-cache");
			ldHeader("Cache-control: must-revalidate, max-age=0, private");
			ldHeader("Expires: ".gmstrftime("%a, %d %b %Y %H:%M:%S GMT",$expires));
			$result=ldHeader("Last-Modified: ".gmstrftime("%a, %d %b %Y %H:%M:%S GMT",$modified));
		}
		return $result;
	}

	function ldSetContent($mimetype, $size=0) {
		$result=ldHeader("Content-Type: ".$mimetype);
		if ($size) {
			$result=ldHeader("Content-Length: ".$size);
		}
		return $result;
	}

	function ldGetServerVar($server_var) {
		global $HTTP_SERVER_VARS;

		return $HTTP_SERVER_VARS[$server_var];
	}

	function ldGetClientVar($client_var) {
		// not all environment variables should be disclosed
		switch($client_var) {
			case "REMOTE_ADDR": $result = getenv("REMOTE_ADDR"); break;
			case "HTTP_USER_AGENT": $result = getenv("HTTP_USER_AGENT"); break;
			case "HTTP_ACCEPT": $result = getenv("HTTP_ACCEPT"); break;
			case "HTTP_ACCEPT_LANGUAGE": $result = getenv("HTTP_ACCEPT_LANGUAGE"); break;
			default: $result = false; break;
		}
		return $result;
	}

?>