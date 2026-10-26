<?php

use PHPUnit\Framework\TestCase;

class LoaderRequestParsed extends RuntimeException {}

class LoaderCompatibilityTest extends TestCase
{
	public static $headers = array();
	public static $cookies = array();
	public static $sessionLookups = array();
	private $savedGlobals = array();
	private const GLOBALS_USED = array('AR', 'ARCurrent', '_COOKIE', '_SERVER', 'ARnls',
		'store_config', 'auth_config', 'cache_config', 'store', 'context', 'DB', 'path', 'function', 'nls');

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
	return true;
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
		self::$headers = self::$cookies = self::$sessionLookups = array();
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
}
