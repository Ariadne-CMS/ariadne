<?php

require_once(dirname(__DIR__, 3).'/lib/modules/mod_filestore.phtml');

class failingFilestoreForTest extends filestore
{
	public $failAt = 0;
	private $replaceCount = 0;

	protected function replaceFile($source, $destination)
	{
		$this->replaceCount++;
		if ($this->replaceCount === $this->failAt) {
			return false;
		}
		return parent::replaceFile($source, $destination);
	}
}

class throwingFilestoreForTest extends filestore
{
	protected function stageFile($contents, $directory, $prefix)
	{
		throw new RuntimeException('Staging failed');
	}
}

class shortLockFilestoreForTest extends filestore
{
	protected $lockTimeout = 0.05;
}

class filestoreTest extends PHPUnit\Framework\TestCase
{
	private $root;

	protected function setUp(): void
	{
		$this->root = sys_get_temp_dir().'/ariadne-filestore-'.bin2hex(random_bytes(8)).'/';
		mkdir($this->root, 0770, true);
	}

	protected function tearDown(): void
	{
		$this->removeDirectory($this->root);
	}

	public function testWriteReportsReplacementFailure()
	{
		$filestore = new failingFilestoreForTest('templates', $this->root);
		$filestore->failAt = 1;

		$this->assertFalse($filestore->write('new contents', 123, 'test.txt'));
		$this->assertFalse($filestore->exists(123, 'test.txt'));
	}

	public function testBatchWriteUpdatesAllFiles()
	{
		$filestore = new filestore('templates', $this->root);

		$this->assertTrue($filestore->writeBatch(array(
			'test.inc' => 'compiled',
			'test.pinp' => 'source'
		), 123));
		$this->assertSame('compiled', $filestore->read(123, 'test.inc'));
		$this->assertSame('source', $filestore->read(123, 'test.pinp'));
	}

	public function testBatchWriteRollsBackAllFiles()
	{
		$filestore = new filestore('templates', $this->root);
		$filestore->write('old compiled', 123, 'test.inc');
		$filestore->write('old source', 123, 'test.pinp');

		$failingFilestore = new failingFilestoreForTest('templates', $this->root);
		$failingFilestore->failAt = 2;

		$this->assertFalse($failingFilestore->writeBatch(array(
			'test.inc' => 'new compiled',
			'test.pinp' => 'new source'
		), 123));
		$this->assertSame('old compiled', $filestore->read(123, 'test.inc'));
		$this->assertSame('old source', $filestore->read(123, 'test.pinp'));
	}

	public function testPersistentLockFileCanBeReused()
	{
		$filestore = new filestore('templates', $this->root);

		$this->assertTrue($filestore->writeBatch(array('test.pinp' => 'first'), 123));
		$this->assertTrue($filestore->writeBatch(array('test.pinp' => 'second'), 123));
		$this->assertSame('second', $filestore->read(123, 'test.pinp'));
	}

	public function testBatchWriteReleasesLockAfterException()
	{
		$throwingFilestore = new throwingFilestoreForTest('templates', $this->root);
		try {
			$throwingFilestore->writeBatch(array('test.pinp' => 'first'), 123);
			$this->fail('Expected staging to throw');
		} catch (RuntimeException $e) {
			$this->assertSame('Staging failed', $e->getMessage());
		}

		$filestore = new filestore('templates', $this->root);
		$this->assertTrue($filestore->writeBatch(array('test.pinp' => 'second'), 123));
		$this->assertSame('second', $filestore->read(123, 'test.pinp'));
	}

	public function testBatchWriteTimesOutWhenLockIsHeld()
	{
		$filestore = new filestore('templates', $this->root);
		$this->assertTrue($filestore->writeBatch(array('test.pinp' => 'first'), 123));

		$lockFiles = glob($this->root.'templates.locks/*.lock');
		$this->assertCount(1, $lockFiles);
		$heldLock = fopen($lockFiles[0], 'c');
		$this->assertTrue(flock($heldLock, LOCK_EX));

		$waitingFilestore = new shortLockFilestoreForTest('templates', $this->root);
		$this->assertFalse($waitingFilestore->writeBatch(array('test.pinp' => 'blocked'), 123));

		flock($heldLock, LOCK_UN);
		fclose($heldLock);
		$this->assertTrue($waitingFilestore->writeBatch(array('test.pinp' => 'second'), 123));
		$this->assertSame('second', $filestore->read(123, 'test.pinp'));
	}

	private function removeDirectory($path)
	{
		if (!is_dir($path)) {
			return;
		}
		foreach (scandir($path) as $entry) {
			if ($entry === '.' || $entry === '..') {
				continue;
			}
			$child = $path.$entry;
			if (is_dir($child)) {
				$this->removeDirectory($child.'/');
			} else {
				unlink($child);
			}
		}
		rmdir($path);
	}
}
?>
