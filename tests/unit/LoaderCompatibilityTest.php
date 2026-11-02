<?php

use PHPUnit\Framework\TestCase;

class LoaderRequestParsed extends RuntimeException {}

class LoaderCookieCanary
{
	public static $wakeups = 0;
	public function __wakeup() { self::$wakeups++; }
}

class LoaderCompatibilityTest extends TestCase
{
	public static $headers = array();
	public static $cookies = array();
	public static $sessionLookups = array();
	public static $cookieWriteResult = true;
	public static $decryptedCookie = null;
	public static $decryptCalls = 0;
	private $savedGlobals = array();
	private const GLOBALS_USED = array('AR', 'ARCurrent', '_COOKIE', '_SERVER', '_GET', '_POST', 'ARnls',
		'store_config', 'auth_config', 'cache_config', 'store', 'context', 'DB', 'path', 'function', 'nls', 'LD_NO_SESSION_SUPPORT');

	public static function setUpBeforeClass(): void
	{
		// Load the real function bodies in separate namespaces: loaders share names,
		// and the suite already has the command-line loader installed.
		$parser = (new PhpParser\ParserFactory())->create(PhpParser\ParserFactory::PREFER_PHP7);
		$printer = new PhpParser\PrettyPrinter\Standard();
		foreach (array('web', 'itf', 'webdav') as $loader) {
			$namespace = 'LoaderCompatibility\\'.$loader;
			eval('namespace '.$namespace.';'. <<<'PHP'
function debug(...$args) {}
function ldStartSession(...$args) {}
function headers_sent() { return false; }
function header($header, $replace = true) {
	\LoaderCompatibilityTest::$headers[] = array($header, $replace);
}
function setcookie($name, $value = '', array|int $expires = 0, ...$options) {
	\LoaderCompatibilityTest::$cookies[$name] = array($value, $expires);
	return \LoaderCompatibilityTest::$cookieWriteResult;
}
const MCRYPT_RIJNDAEL_256 = 'rijndael-256';
function function_exists($name) { return $name === 'mcrypt_encrypt' || \function_exists($name); }
class ar_crypt {
	public function __construct(...$args) {}
	public function decrypt($cookie) {
		\LoaderCompatibilityTest::$decryptCalls++;
		return \LoaderCompatibilityTest::$decryptedCookie;
	}
}
function array_key_exists($key, $array) {
	\LoaderCompatibilityTest::$sessionLookups[] = $key;
	return \array_key_exists($key, $array);
}
function strrpos($string, $needle) {
	// Stop request processing after session selection, before routing or I/O.
	throw new \LoaderRequestParsed();
}
PHP
			);
			$auth = $loader === 'itf' ? 'web' : $loader;
			foreach (array('loader.'.$loader.'.php', 'loader.'.$auth.'.auth.php') as $file) {
				$nodes = $parser->parse(file_get_contents(dirname(__DIR__, 2).'/lib/includes/'.$file));
				foreach ($nodes as $node) {
					if ($node instanceof PhpParser\Node\Stmt\Function_
						&& str_starts_with(strtolower((string)$node->name), 'ld')
						&& strtolower((string)$node->name) !== 'ldstartsession') {
						eval('namespace '.$namespace.';'.$printer->prettyPrint(array($node)));
					}
				}
			}
		}
		// Use the actual consent-gated embedded-content method with each loader.
		$nodes = $parser->parse(file_get_contents(dirname(__DIR__, 2).'/lib/modules/mod_edit.php'));
		foreach ($nodes as $node) {
			if ($node instanceof PhpParser\Node\Stmt\Class_ && (string)$node->name === 'edit') {
				foreach ($node->getMethods() as $method) {
					if ((string)$method->name === 'fixSource') {
						foreach (array('web', 'itf') as $loader) {
							eval('namespace LoaderCompatibility\\'.$loader.'; class edit {'
								.$printer->prettyPrint(array($method)).'}');
						}
					}
				}
			}
		}
		// Exercise the real WebDAV entry-point cookie selection without starting
		// the server, bootstrapping a database, or authenticating a request.
		$nodes = $parser->parse(file_get_contents(dirname(__DIR__, 2).'/webdav/loader.php'));
		$selection = array();
		$collect = false;
		foreach ($nodes as $node) {
			if ($node instanceof PhpParser\Node\Stmt\Expression
				&& $node->expr instanceof PhpParser\Node\Expr\Assign
				&& $node->expr->var instanceof PhpParser\Node\Expr\Variable) {
				$name = $node->expr->var->name;
				if ($name === 'session_id') $collect = true;
				if ($collect && $name === 'args') break;
			}
			if ($collect) $selection[] = $node;
		}
		if (!$selection) throw new RuntimeException('WebDAV cookie selection not found');
		eval('namespace LoaderCompatibility\\webdav; function selectCookieSession() {'
			.$printer->prettyPrint($selection).' return $session_id; }');
	}

	protected function setUp(): void
	{
		foreach (self::GLOBALS_USED as $name) {
			if (array_key_exists($name, $GLOBALS)) $this->savedGlobals[$name] = $GLOBALS[$name];
		}
		$GLOBALS['AR'] = (object) array('root' => '/', 'hideSessionIDfromURL' => true);
		$GLOBALS['ARCurrent'] = (object) array('arNoHeaders' => false);
		$_COOKIE = array();
		$_SERVER = array();
		$_GET = $_POST = array();
		self::$headers = self::$cookies = self::$sessionLookups = array();
		self::$cookieWriteResult = true;
		self::$decryptedCookie = null;
		self::$decryptCalls = 0;
		LoaderCookieCanary::$wakeups = 0;
	}

	protected function tearDown(): void
	{
		foreach (self::GLOBALS_USED as $name) {
			if (array_key_exists($name, $this->savedGlobals)) {
				$GLOBALS[$name] = $this->savedGlobals[$name];
			} else {
				unset($GLOBALS[$name]);
			}
		}
	}

	private function callLoader($loader, $function, ...$args)
	{
		return call_user_func_array('LoaderCompatibility\\'.$loader.'\\'.$function, $args);
	}

	public static function requestCookies()
	{
		foreach (array('web', 'itf') as $loader) {
			foreach (array(true, false) as $hidden) {
				foreach (array(false, true) as $hasCookie) {
					yield array($loader, $hidden, $hasCookie);
				}
			}
		}
	}

	/** @dataProvider requestCookies */
	public function testSessionSelectionHandlesMissingAndPresentCookies($loader, $hidden, $hasCookie)
	{
		$GLOBALS['AR']->hideSessionIDfromURL = $hidden;
		if (!$hidden) $_SERVER['PHP_AUTH_USER'] = 'test';
		if ($hasCookie) {
			$_COOKIE = array('ARCurrentSession' => 'abcd', 'ARSessionCookie' => array('abcd' => '{}'));
		}
		$this->assertSame($hasCookie ? 'abcd' : null, $this->callLoader($loader, 'ldGetCookieSession'));
		try {
			$this->callLoader($loader, 'ldProcessRequest', '/');
			$this->fail('Request did not reach routing');
		} catch (LoaderRequestParsed $expected) {
			$this->assertSame($hasCookie ? array('abcd') : array(), self::$sessionLookups);
		}
	}

	public static function webLoaders()
	{
		return array(array('web'), array('itf'));
	}

	public static function userCookieValues()
	{
		$values = array(
			array(null, false),
			array('', false),
			array('invalid JSON', false),
			array(array('nested' => 'cookie'), false),
			array(serialize(new LoaderCookieCanary()), false),
			array(serialize(array('nested' => new LoaderCookieCanary())), false),
			array(serialize(array('searchname' => 'legacy')), false),
			array('true', true),
			array('false', false),
			array('null', null),
			array('0', 0),
			array('"value"', 'value'),
			array('{"searchname":"test","advanced":true}', array('searchname' => 'test', 'advanced' => true)),
			array('["one","two"]', array('one', 'two')),
		);
		foreach (array('web', 'itf', 'webdav') as $loader) {
			foreach ($values as [$cookie, $expected]) yield array($loader, $cookie, $expected);
		}
	}

	/** @dataProvider userCookieValues */
	public function testUserCookiesAcceptOnlyJsonWithoutObjectHooks($loader, $cookie, $expected)
	{
		if ($cookie !== null) $_COOKIE['ARUserCookie'] = $cookie;
		$this->assertSame($expected, $this->callLoader($loader, 'ldGetUserCookie'));
		$this->assertSame(0, LoaderCookieCanary::$wakeups);
	}

	/** @dataProvider webLoaders */
	public function testWritingCookiesDoesNotDeserializeConsentCookie($loader)
	{
		$_COOKIE['ARCookieConsent'] = serialize(new LoaderCookieCanary());
		$this->assertFalse($this->callLoader($loader, 'ldSetUserCookie', 'value', 'test', 0, '/', '', false, true));
		$this->assertSame(array(), self::$cookies);
		$this->assertSame(0, LoaderCookieCanary::$wakeups);
		$this->assertTrue($this->callLoader($loader, 'ldSetUserCookie', 'value', 'test'));
		$this->assertSame(0, LoaderCookieCanary::$wakeups);
	}

	public static function webdavSessionCookies()
	{
		foreach (array(null, '', 'invalid JSON', true, array('abcd' => 'value'),
			serialize(new LoaderCookieCanary()),
			serialize(array('abcd' => new LoaderCookieCanary())),
			serialize(array('abcd' => array('timestamp' => 123))),
			'null', 'true', '42', '"abcd"', '[]', '{}') as $cookie) {
			yield array($cookie, 0);
		}
		yield array(json_encode(array('abcd' => array('timestamp' => 123))), 'abcd');
		yield array(json_encode(array('abcd' => array('note' => 'quoted "value" and backslash \\'))), 'abcd');
	}

	/** @dataProvider webdavSessionCookies */
	public function testWebdavEntryPointAcceptsOnlyJsonSessionCookies($cookie, $expected)
	{
		if ($cookie !== null) $_COOKIE['ARCookie'] = $cookie;
		$this->assertSame($expected, $this->callLoader('webdav', 'selectCookieSession'));
		$this->assertSame(0, LoaderCookieCanary::$wakeups);
	}

	/** @dataProvider webLoaders */
	public function testHeadersCanBeAppendedBeforeAnyHeaderIsRecorded($loader)
	{
		$this->assertTrue($this->callLoader($loader, 'ldHeader', 'X-Test: first', false));
		$this->assertTrue($this->callLoader($loader, 'ldHeader', 'X-Test: second', false));
		$this->assertSame('X-Test: firstX-Test: second', $GLOBALS['ARCurrent']->ldHeaders['x-test']);
		$this->assertSame(array(array('X-Test: first', false), array('X-Test: second', false)), self::$headers);
	}

	/** @dataProvider webLoaders */
	public function testRequiredConsentPreventsCookieWrites($loader)
	{
		$this->assertFalse($this->callLoader($loader, 'ldSetUserCookie', 'value', 'test', 0, '/', '', false, true));
		$this->assertSame(array(), self::$cookies);
		$_COOKIE['ARCookieConsent'] = 'true';
		$this->assertTrue($this->callLoader($loader, 'ldSetUserCookie', 'value', 'test', 0, '/', '', false, true));
		$this->assertSame(array('"value"', 0), self::$cookies['test']);
	}

	public function testSessionCookieCleanupRemovesOldestCookiesBySessionId()
	{
		$GLOBALS['AR']->hideSessionIDfromURL = false;
		$GLOBALS['ARCurrent']->session = new class {
			public $id = 'current';
			public $sessionstore;
			private $values = array();
			public function __construct() {
				$this->sessionstore = new class { public function exists($path) { return false; } };
			}
			public function put($key, $value, ...$args) { $this->values[$key] = $value; }
			public function get($key, ...$args) { return $this->values[$key]; }
			public function save() {}
		};
		$now = time();
		foreach (array_merge(range(10, 17), range(0, 9)) as $index) {
			$_COOKIE['ARSessionCookie']['session'.$index] = json_encode(array('timestamp' => $now - $index));
		}
		$this->callLoader('web', 'ldSetCredentials', 'test');
		$deleted = array_keys(array_filter(self::$cookies, fn($cookie) => $cookie[0] === false));
		$this->assertSame(array('ARSessionCookie[session15]', 'ARSessionCookie[session16]', 'ARSessionCookie[session17]'), $deleted);
		$this->assertArrayHasKey('ARSessionCookie[current]', self::$cookies);
	}

	public function testWebdavCookiesHandleMissingValuesAndNullExpiry()
	{
		$this->assertFalse($this->callLoader('webdav', 'ldGetUserCookie'));
		$GLOBALS['AR']->sessionCryptoKey = 'unused';
		$this->assertNull($this->callLoader('webdav', 'ldDecodeCookie', null));
		$this->assertNull($this->callLoader('webdav', 'ldDecodeCookie', ''));
		$this->assertSame(array('login' => 'test'), $this->callLoader('webdav', 'ldDecodeCookie', '{"login":"test"}'));
		$this->assertTrue($this->callLoader('webdav', 'ldSetUserCookie', 'value'));
		$this->assertSame(array('"value"', 0), self::$cookies['ARUserCookie']);
		$this->assertTrue($this->callLoader('webdav', 'ldSetUserCookie', 'value', 'explicit', null));
		$this->assertSame(array('"value"', 0), self::$cookies['explicit']);
	}

	public static function consentValues()
	{
		foreach (array('web', 'itf') as $loader) {
			foreach (array('true', 'false', '1', '"true"', '"yes"', '[true]', '{"accepted":true}', 'null') as $cookie) {
				yield array($loader, $cookie, $cookie === 'true');
			}
		}
	}

	/** @dataProvider consentValues */
	public function testOnlyBooleanTrueGrantsConsent($loader, $cookie, $consented)
	{
		$_COOKIE['ARCookieConsent'] = $cookie;
		$this->assertSame($consented, $this->callLoader($loader, 'ldSetConsentedCookie', 'value', 'test'));
		$this->assertSame($consented, isset(self::$cookies['test']));
		$GLOBALS['ARnls'] = array('vd_cookie_consent_required' => 'Consent required');
		$source = '<span vd:cookieconsentrequired="true" vd:source="'.base64_encode('Embedded content')
			.'">placeholder<span vd:endsource="true"></span></span>';
		$this->assertSame($consented ? 'Embedded content' : '[Consent required]',
			call_user_func('LoaderCompatibility\\'.$loader.'\\edit::fixSource', $source));
	}

	public static function protectedCookieNames()
	{
		foreach (array('web', 'itf', 'webdav') as $loader) {
			foreach (array('ARSessionCookie', 'ARSessionCookie[abcd]', 'ARSessionCookieSuffix',
				'ARCurrentSession', 'ARCurrentSession[abcd]', 'ARCookie', 'ARCookie[abcd]', '', null, array('name')) as $name) {
				yield array($loader, $name);
			}
		}
	}

	/** @dataProvider protectedCookieNames */
	public function testGenericCookieAccessCannotReadOrWriteProtectedNames($loader, $name)
	{
		if (is_string($name)) $_COOKIE[$name] = '{"secret":"value"}';
		$this->assertFalse($this->callLoader($loader, 'ldGetUserCookie', $name));
		$this->assertFalse($this->callLoader($loader, 'ldSetUserCookie', 'value', $name));
		$this->assertSame(array(), self::$cookies);
	}

	public static function allLoaders()
	{
		return array(array('web'), array('itf'), array('webdav'));
	}

	/** @dataProvider allLoaders */
	public function testCookieWritersRejectEncodingFailuresAndReturnWriteFailures($loader)
	{
		$this->assertFalse($this->callLoader($loader, 'ldSetUserCookie', array('bad' => "\xFF")));
		$this->assertSame(array(), self::$cookies);
		$this->assertSame(array(), self::$headers);
		self::$cookieWriteResult = false;
		$this->assertFalse($this->callLoader($loader, 'ldSetUserCookie', 'valid'));
	}

	/** @dataProvider allLoaders */
	public function testScalarCookieWritesRoundTrip($loader)
	{
		foreach (array(true, false, null, 0, 'value', array('nested' => array('value'))) as $value) {
			$this->assertTrue($this->callLoader($loader, 'ldSetUserCookie', $value, 'ordinary', null));
			$_COOKIE['ordinary'] = self::$cookies['ordinary'][0];
			$this->assertSame($value, $this->callLoader($loader, 'ldGetUserCookie', 'ordinary'));
		}
	}

	public static function malformedAuthCookies()
	{
		foreach (array('web', 'itf', 'webdav') as $loader) {
			foreach (array(null, '', array('nested' => 'cookie'), true, 123, 'invalid JSON',
				'null', 'true', '42', '"value"') as $cookie) yield array($loader, $cookie);
		}
	}

	/** @dataProvider malformedAuthCookies */
	public function testMalformedAuthenticationCookiesDoNotCrashOrAuthenticate($loader, $cookie)
	{
		$this->assertNull($this->callLoader($loader, 'ldDecodeCookie', $cookie));
		$GLOBALS['ARCurrent']->session = new class {
			public $id = 'abcd';
		};
		$_COOKIE['ARSessionCookie'] = array('abcd' => $cookie);
		$this->assertFalse($this->callLoader($loader, 'ldCheckCredentials', 'test'));
		$_COOKIE['ARSessionCookie'] = $cookie;
		$this->assertSame(is_array($cookie) ? $cookie : array(), $this->callLoader($loader, 'ldGetCredentials'));
		$this->assertFalse($this->callLoader($loader, 'ldCheckCredentials', 'test'));
	}

	/** @dataProvider allLoaders */
	public function testJsonAndEncryptedAuthenticationCookiesStillValidate($loader)
	{
		$GLOBALS['ARCurrent']->session = new class {
			public $id = 'abcd';
			public function get($key, ...$args) {
				return array('ARSessionKey' => 'secret', 'ARUserDir' => '/system/users/',
					'ARLogin' => 'test', 'ARSessionTimedout' => false)[$key];
			}
		};
		$data = array('login' => 'test', 'check' => $this->callLoader($loader, 'ldGenerateSessionKeyCheck'));
		$_COOKIE['ARSessionCookie']['abcd'] = json_encode($data);
		$this->assertTrue($this->callLoader($loader, 'ldCheckCredentials', 'test'));
		$GLOBALS['AR']->sessionCryptoKey = base64_encode('test key');
		$this->assertSame($data, $this->callLoader($loader, 'ldDecodeCookie', json_encode($data)));
		$this->assertSame(0, self::$decryptCalls);
		self::$decryptedCookie = json_encode($data);
		$_COOKIE['ARSessionCookie']['abcd'] = 'encrypted-cookie';
		$this->assertTrue($this->callLoader($loader, 'ldCheckCredentials', 'test'));
		$this->assertSame(1, self::$decryptCalls);
		foreach (array(false, array('unexpected'), '"scalar"', 'invalid JSON') as $invalid) {
			self::$decryptedCookie = $invalid;
			$this->assertFalse($this->callLoader($loader, 'ldCheckCredentials', 'test'));
		}
	}

	/** @dataProvider webLoaders */
	public function testArrayShapedSessionSelectorIsIgnored($loader)
	{
		$_COOKIE['ARCurrentSession'] = array('abcd');
		$_COOKIE['ARSessionCookie'] = array('abcd' => '{}');
		$this->assertNull($this->callLoader($loader, 'ldGetCookieSession'));
		try {
			$this->callLoader($loader, 'ldProcessRequest', '/');
			$this->fail('Request did not reach routing');
		} catch (LoaderRequestParsed $expected) {
			$this->assertSame(array(), self::$sessionLookups);
		}
	}

	/** @dataProvider allLoaders */
	public function testLoginCleanupIgnoresMalformedCookiesAndExpiresOldValidCookies($loader)
	{
		$GLOBALS['AR']->hideSessionIDfromURL = false;
		$GLOBALS['LD_NO_SESSION_SUPPORT'] = false;
		$GLOBALS['ARCurrent']->session = new class {
			public $id = 'current';
			public $sessionstore;
			private $values = array();
			public function __construct() {
				$this->sessionstore = new class { public function exists($path) { return false; } };
			}
			public function put($key, $value, ...$args) { $this->values[$key] = $value; }
			public function get($key, ...$args) { return $this->values[$key]; }
			public function save() {}
		};
		$_COOKIE['ARSessionCookie'] = array(
			'missing' => '{}', 'badTimestamp' => '{"timestamp":[]}',
			'nested' => array('cookie'), 'scalar' => 'true',
			'old' => json_encode(array('timestamp' => time() - 172800))
		);
		$this->callLoader($loader, 'ldSetCredentials', 'test');
		$this->assertArrayHasKey('ARSessionCookie[old]', self::$cookies);
		$this->assertArrayNotHasKey('ARSessionCookie[badTimestamp]', self::$cookies);
		$this->assertArrayNotHasKey('ARSessionCookie[missing]', self::$cookies);
		$this->assertArrayHasKey('ARSessionCookie[current]', self::$cookies);
		unset($_COOKIE['ARSessionCookie']);
		$this->callLoader($loader, 'ldSetCredentials', 'test');
	}
}
