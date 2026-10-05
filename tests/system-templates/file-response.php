<?php
// Run with: php tests/system-templates/file-response.php
// Exercise the real file response methods, stream and web headers without a database.
define('AriadneBasePath', dirname(__DIR__, 2) . '/lib');
class baseObject {}
class arBase {}
class ar_pinp {
	public static function allow($class) {}
}
class ar_error {
	public static function isError($value) { return $value instanceof Exception; }
}
class ar_store_files {
	public static $file;
	public static function exists($file, $nls) { return true; }
	public static function get($file, $nls) { return self::$file; }
}
function debug($message, $level = null) {}
$store_config = array('code' => AriadneBasePath . '/');
$ARCurrent = new stdClass();
require AriadneBasePath . '/includes/loader.web.php';
require AriadneBasePath . '/objects/pfile.phtml';
require AriadneBasePath . '/ar/content/files.php';

class FileResponseDouble extends pfile {
	public $allowed = true;
	public $configOutput = '';
	public $contextDepth = 0;

	public function pushContext($context) { $this->contextDepth++; }
	public function popContext() { $this->contextDepth--; }
	public function getvar($var) { return 'view.html'; }
	public function CheckPublic($grant, $modifier = null) { return true; }
	public function CheckLogin($grant, $modifier = null) {
		if (!$this->allowed) {
			echo 'Authorization required';
		}
		return $this->allowed;
	}
	public function CheckConfig($arCallFunction = '', $arCallArgs = '') {
		echo $this->configOutput;
		return true;
	}
	public function ParseString($contents) {
		return str_replace('{arRoot}', '/root/', $contents);
	}
	public function view() {
		global $ARCurrent;
		$arCallFunction = 'view.html';
		$arCallArgs = array();
		include AriadneBasePath . '/templates/pfile/view.html';
	}
}

function checkFileResponse($condition, $message) {
	if (!$condition) {
		throw new RuntimeException($message);
	}
}

$object = new FileResponseDouble();
$object->path = '/files/example/';
$object->parent = '/files/';
$object->id = 1;
$object->nls = 'en';
$object->data = (object) array('nls' => (object) array('default' => 'en'));
$AR = (object) array('user' => (object) array('data' => (object) array('login' => 'public')));
$initialLevel = ob_get_level();
$checks = 0;

try {
	foreach (array('ShowFile', 'DownloadFile', 'view') as $method) {
		foreach (array(
			'application/octet-stream' => "PK\x03\x04\x00\xffbinary\r\n",
			'text/plain' => "  legitimate leading spaces\r\n",
			'text/html' => '  <a href="{arRoot}">Home</a>',
		) as $mimetype => $contents) {
			foreach (array('', '  ', "\nstray template output\t") as $prefix) {
				foreach (array(false, true) as $private) {
					$ARCurrent = (object) array('arDontCache' => $private, 'cachetime' => 0);
					$ldOutputBufferActive = true;
					$object->data->en = (object) array('mimetype' => $mimetype);
					$object->configOutput = $prefix;
					$stream = fopen('php://temp', 'w+b');
					fwrite($stream, $contents);
					rewind($stream);
					ar_store_files::$file = new ar_content_filesFile($stream);
					ob_start(); // HTTP response capture, outside the loader's buffer.
					ob_start(); // Buffer started by the web loader before calling templates.
					echo $prefix;
					$object->$method();
					checkFileResponse(ob_get_level() === $initialLevel + 2, "$method closed a buffer");
					ob_end_flush();
					$response = ob_get_clean();
					$expected = $mimetype === 'text/html' && $method !== 'DownloadFile'
						? str_replace('{arRoot}', '/root/', $contents) : $contents;
					$case = "$method ($mimetype, prefix " . strlen($prefix) . ", private $private)";
					checkFileResponse($response === $expected, "$case: response differs from the file");
					$headers = $ARCurrent->ldHeaders;
					if ($method === 'DownloadFile' || $mimetype === 'application/octet-stream') {
						checkFileResponse((int) explode(':', $headers['content-length'], 2)[1] === strlen($response), "$case: wrong content length");
					}
					$disposition = $method === 'DownloadFile' ? 'attachment; filename="example"' : 'inline; filename=example';
					checkFileResponse($headers['content-disposition'] === 'Content-Disposition: ' . $disposition, "$case: wrong disposition");
					checkFileResponse($ARCurrent->arDontCache === $private && $ldOutputBufferActive, "$case: changed buffering/cache state");
					checkFileResponse($object->contextDepth === 0, "$case: context leaked");
					$checks++;
				}
			}
		}
	}

	foreach (array('ShowFile', 'DownloadFile') as $method) {
		ar_store_files::$file = new RuntimeException('Missing file');
		ob_start();
		echo 'Earlier diagnostic';
		$result = $object->$method();
		$response = ob_get_clean();
		checkFileResponse($result === ar_store_files::$file, "$method discarded the missing-file error");
		checkFileResponse($response === 'Earlier diagnostic', "$method discarded diagnostics for a missing file");
		checkFileResponse($object->contextDepth === 0, "$method leaked context for a missing file");
	}
	$object->allowed = false;
	ob_start();
	$object->view();
	$response = ob_get_clean();
	checkFileResponse($response === 'Authorization required', 'Authorization response was discarded');
} finally {
	while (ob_get_level() > $initialLevel) {
		ob_end_clean();
	}
}
echo "File response checks passed ($checks content cases, missing files, authorization).\n";
