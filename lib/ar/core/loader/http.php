<?php
	require_once(ARBaseDir.'core/loader.php');

	class ar_core_loader_http implements ar_core_loaderInterface {
		private $options = null;
		private $ariadne = null;
		private $session = null;
		private $request = null;
		private $nls     = null;
		private $hideSessionIDFromURL = false;

		public function __construct($options = null, $ariadne = null, $session = null, $nls = null) {
			$this->ariadne = $ariadne;
			$this->options = $options;
			$this->session = $session;
			$this->nls     = $nls;

			if (!isset($_SERVER['PHP_SELF'])) { // needed for IIS: it doesn't set the PHP_SELF variable.
				$_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'].$_SERVER['PATH_INFO'];
			}
		}

		protected function parseSession( $pathInfo, $session ) {
			$re = '|^/-(.{4})-/|';
			$matches = null;
			$sessionID = null;
			if (preg_match($re, $pathInfo, $matches)) {
				$sessionID = $matches[1];
				$pathInfo = substr($pathInfo, strlen($matches[0])-1);
				$this->hideSessionIDFromURL = false;
			} else if ($this->hideSessionIDFromURL) {
				$cookie = $this->getCookie();
				if (isset($cookie) && is_array($cookie)) {
					$sessionID = current(array_keys($cookie));
				}
			}
			if ( $sessionID ) {
				$session->start($sessionID);
			}
			return $pathInfo;
		}

		/*fixme -- */
		protected function parseNLS( $pathInfo, $nls ) {
			$re      = '|^/-(.{2})-/|';
			$matches = null;
			$nls->nls = $nls->default;
			if (preg_match($re, $pathInfo, $matches)) {
				$nls->requested = $matches[1];
				if ( isset($nls->available[$matches[1]]) ) {
					$nls->nls = $matches[1];
					$pathInfo = substr($pathInfo, strlen($matches[0])-1);
				}
			}
			$this->parseAcceptLanguage($_SERVER['HTTP_ACCEPT_LANGUAGE'], $nls);
			if ( !isset( $nls->available[$nls] ) ) {
				$nls->nls = $nls->default;
			}
			$nls->current = $nls->nls;
			return $pathInfo;
		}

		protected function parseAcceptLanguage($acceptLanguage, $nls) {
			$re      = '%([a-zA-Z]{2}|\\*)[a-zA-Z-]*(?:;q=([0-9.]+))?%';
			$matches = null;
			if (preg_match_all($re, $acceptLanguage, $matches)) {
				$otherlangs=array();
				$otherq=false;
				foreach ($matches as $match) {
					if (!isset($match[2])) {
						$match[2]=1;
					}
					if ($match[1]=="*") {
						$otherq=$match[2];
					} else if ($nls->available[$match[1]]) {
						$otherlangs[]=$match[1];
						$nls->accepted[$match[1]]=$match[2];
					}
				}
				if ($otherq !== false) {
					$otherlangs=array_diff(array_keys($nls->available), $otherlangs);
					foreach ($otherlangs as $lang) {
						$nls->accepted[$lang]=$otherq;
					}
				}
				arsort($nls->accepted);
			}
		}
		/* -- fixme */

		public function getRequest() {
			$pathInfo = $_SERVER['PATH_INFO'];
			if (!$pathInfo) {
				throw new ar_exception_illegalRequest('No PATH_INFO found', ar_exceptions::NO_PATH_INFO);
			}
			if (isset($this->session)) {
				$pathInfo = $this->parseSession($pathInfo, $this->session);
			}
			if (isset($this->nls)) {
				$pathInfo = $this->parseNLS($pathInfo, $this->nls);
			}
			$path = dirname($pathInfo);
			$template = basename($pathInfo);
			if (!$template) {
				$template = 'view.html';
			}
			$params = $this->getParams();
			if ( isset($params['ARLogin']) ) {
				$user     = $params['ARLogin'];
				$password = $params['ARPassword'];
				unset($params['ARPassword']);
			} else {
				$user     = 'public';
				$password = null;
			}
			$this->request = array(
				'path'     => $path,
				'template' => $template,
				'params'   => $params,
				'session'  => $this->session,
				'nls'      => $this->nls,
				'cache'    => $this->getCacheControl(),
				'loader'   => $this,
				'user'     => $user,
				'password' => $password
			);
			return $this->request;
		}

		public function handleRequest($request = null) {
			if (!isset($request)) {
				$request = $this->request ? $this->request : $this->getRequest();
			}
			return $this->ariadne->handleRequest($request);
		}

		public function handleException($exception) {
			$code = $exception->getCode();
			switch ($code) {
				case ar_exceptions::NO_PATH_INFO :
					$this->redirect($_SERVER['PHP_SELF'].'/');
				break;
				case ar_exceptions::HEADERS_SENT :
					echo "The loader has detected that PHP has already sent the HTTP Headers. This error is usually caused by trailing white space or newlines in the configuration files. See the following error message for the exact file that is causing this:";
					header("Misc: this is a test header");
				break;
				case ar_exceptions::ACCESS_DENIED :
					$this->request['params']['arLoginMessage'] = $exception->getMessage();
					$this->request['params']['arOriginalFunction'] = $this->request['template'];
					$this->request['user'] = 'public';
					$this->request['template'] = 'user.login.html';
					$this->handleRequest($request);
				break;
				case ar_exceptions::SESSION_TIMEOUT :
					$this->request['params']['arLoginMessage'] = $exception->getMessage();
					$this->request['params']['arOriginalFunction'] = $this->request['template'];
					$this->request['template'] = 'user.session.timeout.html';
					$this->handleRequest($request);
				break;
				case ar_exceptions::PASSWORD_EXPIRED :
					$this->request['params']['arLoginMessage'] = $exception->getMessage();
					$this->request['params']['arOriginalFunction'] = $this->request['template'];
					$this->request['template'] = 'user.password.expired.html';
					$this->handleRequest($request);
				break;
				case ar_exceptions::OBJECT_NOT_FOUND :
					$this->request['params']['arLoginMessage'] = $exception->getMessage();
					$this->request['params']['arRequestedPath'] = $this->request['path'];
					$this->request['params']['arRequestedTemplate'] = $this->request['template'];
					$this->request['template'] = 'user.notfound.html';
					try {
						$this->request['path'] = $this->ariadne->findNearestPath($this->request['path']);
						$this->handleRequest($request);
					} catch ( ar_exception $e ) {
						if ($e->getCode()!=ar_exceptions::OBJECT_NOT_FOUND) {
							$this->handleExceptions($e);
						} else {
							throw $e;
						}
					}
				break;
				case ar_exceptions::DATABASE_EMPTY :
					// give installation tips.
				break;
				default :
					throw new ar_exception_unknownError('Unknown error exception.', ar_exceptions::UNKNOWN_ERROR, $exception);
				break;
			}
		}

		public function run() {
			try {
				$this->handleRequest();
			} catch( ar_exception $e ) {
				$this->handleException($e);
			}
		}

		public function getCacheControl() {
			// COOKIEs...
			$isCacheable =
				( !isset($_SERVER['HTTP_CACHE_CONTROL'])
					|| ( strpos( $_SERVER['HTTP_CACHE_CONTROL'], 'no-cache' ) === false
						&& strpos( $_SERVER['HTTP_CACHE_CONTROL'], 'no-store' ) === false  )
				)
				&& ( !isset($_SERVER['HTTP_PRAGMA'])
					|| ( strpos( $_SERVER['HTTP_PRAGMA'], 'no-cache' ) === false )
				)
				&& ( $_SERVER['REQUEST_METHOD'] == 'GET' );
			if ( $isCacheable ) {
				$result = array( 'cacheable' => true );
				if ( isset($_SERVER['HTTP_CACHE_CONTROL']) ) {
					$cacheControl = explode(',', $_SERVER['HTTP_CACHE_CONTROL']);
					foreach ( $cacheControl as $cache ) {
						$cache = explode( '=', $cache );
						switch ( $cache[0] ) {
							case 'max-age' :
							case 'max-stale' :
							case 'min-fresh' :
								$result[$cache[0]] = $cache[1];
							break;
							case 'only-if-cached' :
								$result[$cache[1]] = true;
							break;
						}
					}
				}
				if ( isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ) {
					$result['if-modified-since'] = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
				}
				return $result;
			} else {
				return false;
			}
		}

		public function redirect( $url ) {
			return ar_http_headers::redirect( $url );
		}

		public function header( $header ) {
			return ar_http_headers::header( $header );
		}

		public function getvar( $name = null, $method = null ) {
			return ar_http::getvar( $name, $method );
		}

		public function cache( $expires = 0, $modified = false ) {
			return ar_http_headers::cache( $expires, $modified);
		}

		public function disableCache() {
			return ar_http_headers::disableCache();
		}

		public function content( $contentType, $size = 0 ) {
			return ar_http_headers::content( $contentType, $size );
		}

		public function isCacheable() {
		}

		public function makeURL( $path = '', $nls = '', $session = true, $https = null, $keephost = null ) {
			$context = ar::context();
			$me = $context->getObject();
			return $me->make_url( $path, $nls, $session, $https, $keephost );
		}
	}
