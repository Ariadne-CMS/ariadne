<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3).'/lib/ar.php';
require_once dirname(__DIR__, 3).'/lib/modules/mod_pinp.phtml';

class PinpFunctionAllowlistWakeupProbe {
	public static $called = false;
	public function __wakeup() { self::$called = true; }
}

class PinpFunctionAllowlistTest extends TestCase {
	private function runTemplate($source, $variables = array()) {
		$object = new ar_core_pinpSandbox(new stdClass());
		foreach ($variables as $name => $value) { $object->$name = $value; }
		$compiler = new pinp('', 'object->', '$object->_');
		$compiled = $compiler->compile('<pinp>'.$source.'</pinp>');
		$this->assertNull($compiler->error);
		eval('?>'.$compiled);
		return $object;
	}

	public function testRecentPureFunctionsExecuteNatively() {
		$object = $this->runTemplate(
			'$keys = array(array_key_first($items), array_key_last($items));'
			.'$list = array_is_list(array_values($items));'
			.'$text = str_contains("hello world", "world") && str_starts_with("hello", "he") && str_ends_with("hello", "lo");'
			.'$next = str_increment("AZ"); $previous = str_decrement("BA");'
			.'$valid = json_validate("{\"x\":1}"); $division = intdiv(17, 3);'
			.'$hash = hash_hmac("sha256", "message", "key");'
			.'$password = password_hash("secret", PASSWORD_DEFAULT); $verified = password_verify("secret", $password);',
			array('items' => array('a' => 10, 'b' => 20))
		);
		$this->assertSame(array('a', 'b'), $object->keys);
		$this->assertTrue($object->list && $object->text && $object->valid && $object->verified);
		$this->assertSame('BA', $object->next);
		$this->assertSame('AZ', $object->previous);
		$this->assertSame(5, $object->division);
		$this->assertSame(hash_hmac('sha256', 'message', 'key'), $object->hash);
	}

	public function testCallbackFunctionNamesAndClosureVariablesKeepPhpCaseRules() {
		$object = $this->runTemplate('$value = $MixedCase("value"); $mapped = ARRAY_MAP($MixedCase, array("other"));', array(
			'MixedCase' => function ($value) { return strtoupper($value); }
		));
		$this->assertSame('VALUE', $object->value);
		$this->assertSame(array('OTHER'), $object->mapped);
	}

	public function testPhp85ArrayEndpoints() {
		if (!function_exists('array_first')) { $this->markTestSkipped('Requires PHP 8.5'); }
		$object = $this->runTemplate('$first = array_first($items); $last = array_last($items);', array('items' => array(12, 34)));
		$this->assertSame(12, $object->first);
		$this->assertSame(34, $object->last);
	}

	public function testNewArrayCallbacksStayInsideSandbox() {
		if (!function_exists('array_find')) { $this->markTestSkipped('Requires PHP 8.4'); }
		foreach (array('array_find', 'array_find_key', 'array_any', 'array_all') as $function) {
			$object = $this->runTemplate('$result = '.$function.'($items, $callback);', array(
				'items' => array(1, 2, 3), 'callback' => function ($value) { return $value > 1; }
			));
			$expected = array('array_find' => 2, 'array_find_key' => 1, 'array_any' => true, 'array_all' => false);
			$this->assertSame($expected[$function], $object->result);
			$object = $this->runTemplate('$result = '.$function.'($items, "strlen");', array('items' => array('text')));
			$this->assertFalse($object->result);
		}
	}

	public function testRegexCallbackCountsAreWrittenBack() {
		$object = $this->runTemplate(
			'$text = preg_replace_callback("/a/", $callback, "aab", -1, $count);'
			.'$other = preg_replace_callback_array($patterns, "aba", -1, $otherCount);'
			.'$plain = preg_replace("/a/", "b", "aaa", -1, $plainCount);',
			array('callback' => function ($match) { return strtoupper($match[0]); },
				'patterns' => array('/a/' => function () { return 'x'; }))
		);
		$this->assertSame(array('AAb', 2, 'xbx', 2, 'bbb', 3), array(
			$object->text, $object->count, $object->other, $object->otherCount, $object->plain, $object->plainCount
		));
		$object = $this->runTemplate('$result = preg_replace_callback_array($patterns, "aaa");', array('patterns' => array('/a/' => 'strlen')));
		$this->assertFalse($object->result);
	}

	public function testFiltersAllowClosuresAndRejectOtherCallbacks() {
		$object = $this->runTemplate(
			'$valid = filter_var("12", FILTER_VALIDATE_INT);'
			.'$text = filter_var("hello", FILTER_CALLBACK, array("options" => $callback));'
			.'$blocked = filter_var("hello", FILTER_CALLBACK, array("options" => "strtoupper"));'
			.'$blockedArray = filter_var_array(array("value" => "hello"), $options);'
			.'$env = filter_input(INPUT_ENV, "PATH"); $server = filter_input_array(INPUT_SERVER);',
			array('callback' => function ($text) { return strtoupper($text); },
				'options' => array('value' => array('filter' => (string)FILTER_CALLBACK, 'options' => 'strtoupper')))
		);
		$this->assertSame(12, $object->valid);
		$this->assertSame('HELLO', $object->text);
		$this->assertFalse($object->blocked);
		$this->assertFalse($object->blockedArray);
		$this->assertFalse($object->env);
		$this->assertFalse($object->server);
	}

	public function testUnserializeCannotInstantiateExecutableClasses() {
		PinpFunctionAllowlistWakeupProbe::$called = false;
		$object = $this->runTemplate('$value = unserialize($serialized, array("allowed_classes" => true));', array(
			'serialized' => serialize(new PinpFunctionAllowlistWakeupProbe())
		));
		$this->assertFalse(PinpFunctionAllowlistWakeupProbe::$called);
		$this->assertInstanceOf(ar_error::class, $object->value);

	}

	public function testClassInspectionDoesNotAutoload() {
		$loaded = array();
		$loader = function ($class) use (&$loaded) { $loaded[] = $class; };
		spl_autoload_register($loader);
		try {
			$this->runTemplate(
				'$a = class_exists("PinpUntrustedClass", true); $b = interface_exists("PinpUntrustedInterface", true);'
				.'$c = trait_exists("PinpUntrustedTrait", true); $d = enum_exists("PinpUntrustedEnum", true);'
				.'$e = method_exists("PinpUntrustedMethod", "read"); $f = property_exists("PinpUntrustedProperty", "secret");'
				.'$g = is_a("PinpUntrustedA", "stdClass", true); $h = is_subclass_of("PinpUntrustedSubclass", "stdClass");'
				.'$i = get_class_vars("PinpUntrustedVars"); $j = get_parent_class("PinpUntrustedParent");'
				.'$k = is_callable(array("PinpUntrustedCallable", "read"));'
				.'$l = unserialize("O:20:\"PinpUntrustedRestore\":0:{}");'
			);
		} finally { spl_autoload_unregister($loader); }
		$this->assertSame(array(), $loaded);
	}

	public function testStreamNotificationsRequireClosures() {
		$object = $this->runTemplate(
			'$blocked = stream_context_create(null, array("notification" => "strlen"));'
			.'$context = stream_context_create(null, array("notification" => $callback));'
			.'$changed = stream_context_set_params($context, array("notification" => "strlen"));',
			array('callback' => function () {})
		);
		$this->assertFalse($object->blocked);
		$this->assertFalse($object->changed);
		$this->assertInstanceOf(Closure::class, stream_context_get_params($object->context)['notification']);
	}

	public function testCryptographicOutputReferences() {
		if (!function_exists('openssl_encrypt') || !function_exists('sodium_crypto_generichash_init')) {
			$this->markTestSkipped('Requires OpenSSL and sodium');
		}
		$object = $this->runTemplate(
			'$cipher = openssl_encrypt("message", "aes-128-gcm", $key, OPENSSL_RAW_DATA, $iv, $tag);'
			.'$plain = openssl_decrypt($cipher, "aes-128-gcm", $key, OPENSSL_RAW_DATA, $iv, $tag);'
			.'$state = sodium_crypto_generichash_init(); sodium_crypto_generichash_update($state, "message");'
			.'$hash = sodium_crypto_generichash_final($state);',
			array('key' => str_repeat('k', 16), 'iv' => str_repeat('i', 12))
		);
		$this->assertSame('message', $object->plain);
		$this->assertSame(16, strlen($object->tag));
		$this->assertSame(sodium_crypto_generichash('message'), $object->hash);
	}

	/** @dataProvider keyLoadingFunctions */
	public function testOpenSslKeyLoadingAndSigning($privateLoader, $publicLoader, $fromFile) {
		if (!extension_loaded('openssl')) { $this->markTestSkipped('Requires OpenSSL'); }
		$key = openssl_pkey_new(array('private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA));
		$this->assertNotFalse($key);
		$this->assertTrue(openssl_pkey_export($key, $privatePem, 'test passphrase'));
		$publicPem = openssl_pkey_get_details($key)['key'];
		$files = array();
		try {
			if ($fromFile) {
				foreach (array($privatePem, $publicPem) as $pem) {
					$file = tempnam(sys_get_temp_dir(), 'pinp-key-');
					$files[] = $file;
					file_put_contents($file, $pem);
				}
				$privatePem = 'file://'.$files[0];
				$publicPem = 'file://'.$files[1];
			}
			$object = $this->runTemplate(
				'$privateKey = '.$privateLoader.'($privatePem, "test passphrase");'
				.'$publicKey = '.$publicLoader.'($publicPem);'
				.'$signed = openssl_sign("message", $signature, $privateKey, OPENSSL_ALGO_SHA256);'
				.'$verified = openssl_verify("message", $signature, $publicKey, OPENSSL_ALGO_SHA256);'
				.'$tampered = openssl_verify("changed message", $signature, $publicKey, OPENSSL_ALGO_SHA256);',
				array('privatePem' => $privatePem, 'publicPem' => $publicPem)
			);
			$this->assertTrue($object->signed);
			$this->assertNotEmpty($object->signature);
			$this->assertSame(1, $object->verified);
			$this->assertSame(0, $object->tampered);
		} finally {
			foreach ($files as $file) { unlink($file); }
		}
	}

	public static function keyLoadingFunctions() {
		return array(
			'pem keys' => array('openssl_pkey_get_private', 'openssl_pkey_get_public', false),
			'pem aliases' => array('openssl_get_privatekey', 'openssl_get_publickey', false),
			'file keys' => array('openssl_pkey_get_private', 'openssl_pkey_get_public', true),
			'file aliases' => array('openssl_get_privatekey', 'openssl_get_publickey', true)
		);
	}

	public function testExecutionTimeLimitCanBeChanged() {
		$previous = (int)ini_get('max_execution_time');
		try {
			$object = $this->runTemplate('$limited = set_time_limit(30);');
			$this->assertTrue($object->limited);
			$this->assertSame('30', ini_get('max_execution_time'));
			$object = $this->runTemplate('$unlimited = set_time_limit(0);');
			$this->assertTrue($object->unlimited);
			$this->assertSame('0', ini_get('max_execution_time'));
		} finally { set_time_limit($previous); }
	}

	public function testDangerousFunctionsAreNotNativeCalls() {
		$compiler = new pinp('', 'object->', '$object->_');
		$blocked = array(
			'file_get_contents', 'fopen', 'fwrite', 'unlink', 'glob', 'stat', 'mysqli_query', 'pg_query',
			'exec', 'system', 'shell_exec', 'proc_open', 'dl', 'ini_set', 'putenv', 'getenv',
			'call_user_func', 'call_user_func_array', 'register_shutdown_function', 'set_error_handler',
			'get_error_handler', 'get_exception_handler', 'get_defined_vars', 'get_resources',
			'extract', 'compact', 'func_get_args', 'debug_backtrace', 'class_alias', 'spl_autoload_call',
			'get_headers', 'curl_exec', 'mail', 'mb_send_mail', 'imap_open', 'imap_savebody',
			'openssl_pkey_export_to_file', 'openssl_csr_sign', 'openssl_x509_read',
			'hash_file', 'hash_update_file', 'hash_update_stream', 'parse_ini_string', 'parse_ini_file',
			'simplexml_load_string', 'simplexml_load_file', 'libxml_set_external_entity_loader',
			'xml_set_element_handler', 'resourcebundle_create', 'resourcebundle_locales',
			'imagepng', 'imagecreatefrompng', 'imagettftext', 'finfo_open', 'pspell_save_wordlist',
			'filter_var', 'filter_var_array', 'unserialize', 'array_find', 'preg_replace_callback_array',
			'get_class_vars', 'stream_context_create', 'stream_context_set_params'
		);
		foreach ($blocked as $name) {
			$compiled = $compiler->compile('<pinp>'.$name.'($value);</pinp>');
			$this->assertNull($compiler->error, $name);
			$this->assertStringContainsString('$object->'.$name.'(', $compiled, $name);
		}
	}
}
