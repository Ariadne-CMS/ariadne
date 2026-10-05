<?php
// Run with: php tests/system-templates/export-download.php
// Exercise the real web loader helpers and download template without a database.
define('AriadneBasePath', dirname(__DIR__, 2) . '/lib');
class baseObject {}
function debug($message, $level = null) {}
$store_config = array('code' => AriadneBasePath . '/');
$ARCurrent = new stdClass();
require AriadneBasePath . '/includes/loader.web.php';

class ExportDownloadDouble {
	public $path = '/projects/export/';
	public $parent = '/projects/';
	public $store;
	public $allowed = true;
	public $configOutput = '';

	public function CheckLogin($grant) {
		if (!$this->allowed) {
			echo 'Authorization required';
		}
		return $this->allowed;
	}

	public function CheckConfig() {
		echo $this->configOutput;
		return true;
	}

	public function download() {
		global $ARCurrent, $ARnls;
		include AriadneBasePath . '/templates/pobject/object.export.ax';
	}
}

function checkExportDownload($condition, $message) {
	if (!$condition) {
		throw new RuntimeException($message);
	}
}

$directory = sys_get_temp_dir() . '/ariadne-export-test-' . bin2hex(random_bytes(8));
mkdir($directory . '/temp', 0700, true);
$archive = file_get_contents(dirname(__DIR__, 2) . '/www/install/packages/demo.ax');
$object = new ExportDownloadDouble();
$object->store = new class($directory) {
	private $directory;
	public function __construct($directory) { $this->directory = $directory; }
	public function get_config($key) { return $this->directory . '/'; }
};
$session = new class {
	public function get($key) { return '/original/location/export.ax'; }
};
$ARnls = array('err:noexportfile' => 'Missing export: %s%s');
$ldXSSProtectionActive = false;
$initialLevel = ob_get_level();

try {
	foreach (array('clean', 'whitespace', 'missing', 'denied') as $case) {
		$tempfile = $directory . '/temp/export.ax';
		if ($case !== 'missing') {
			file_put_contents($tempfile, $archive);
		}
		$ARCurrent = (object) array('session' => $session);
		$object->allowed = $case !== 'denied';
		$object->configOutput = $case === 'whitespace' ? ' ' : '';

		ob_start(); // Capture the HTTP response.
		ob_start(); // The buffer started by ldProcessRequest before calling templates.
		$ldOutputBufferActive = true;
		if ($case === 'whitespace') {
			echo ' '; // Stray output from a template called before the download template.
		}
		$object->download();
		if ($ldOutputBufferActive) {
			ob_end_flush();
		}
		$response = ob_get_clean();

		if ($case === 'clean' || $case === 'whitespace') {
			checkExportDownload($response === $archive, "$case: download differs from the archive");
			checkExportDownload(substr($response, 0, 2) === "\x1f\x8b", "$case: gzip header is not at byte zero");
			checkExportDownload($ARCurrent->ldHeaders['content-length'] === 'Content-Length: ' . strlen($response), "$case: wrong content length");
			checkExportDownload(!$ldOutputBufferActive && $ARCurrent->arDontCache, "$case: streaming/cache state changed");
			checkExportDownload(!file_exists($tempfile), "$case: temporary archive was not removed");
		} elseif ($case === 'missing') {
			checkExportDownload(strpos($response, 'Missing export: ') === 0, 'Missing-file error was discarded');
		} else {
			checkExportDownload($response === 'Authorization required', 'Authorization response was discarded');
			checkExportDownload(file_exists($tempfile), 'Unauthorized request removed the archive');
		}
		checkExportDownload(ob_get_level() === $initialLevel, "$case: output buffer leaked");
	}
} finally {
	while (ob_get_level() > $initialLevel) {
		ob_end_clean();
	}
	if (file_exists($directory . '/temp/export.ax')) {
		unlink($directory . '/temp/export.ax');
	}
	rmdir($directory . '/temp');
	rmdir($directory);
}
echo "Export download checks passed (clean, whitespace, missing file, authorization).\n";
