<?php

use PHPUnit\Framework\TestCase;

class ShortcutDefaultStoreStub
{
	public function make_path($parent, $path)
	{
		return $path;
	}

	public function exists($path)
	{
		return true;
	}
}

class ShortcutDefaultObjectStub
{
	public $data;
	public $parent = '/';
	public $path = '/shortcut/';
	public $store;

	public function __construct()
	{
		$this->data = (object) array(
			'path' => '/target/',
			'keepurl' => false
		);
		$this->store = new ShortcutDefaultStoreStub();
	}

	public function CheckConfig($function, $args)
	{
		return true;
	}

	public function get($path, $function, $args)
	{
		return array('called');
	}
}

class ShortcutDefaultTemplateTest extends TestCase
{
	public function testSequentialCallsDoNotTriggerTheRecursionGuard()
	{
		global $ARBeenHere, $ARConfig, $ARCurrent;

		$ARBeenHere = array();
		$ARConfig = (object) array(
			'cache' => array('/target/' => (object) array('templates_path' => '/target/'))
		);
		$ARCurrent = (object) array('shortcut_redirect' => array());
		$shortcut = new ShortcutDefaultObjectStub();
		$template = dirname(__DIR__, 2).'/lib/templates/pshortcut/default.phtml';

		$call = function() use ($template) {
			global $ARBeenHere, $ARConfig, $ARCurrent;
			$data = $this->data;
			$arCallFunction = 'helene.get.templates';
			$arCallArgs = array();
			include $template;
			return $arResult;
		};

		for ($index = 0; $index < 12; $index++) {
			$this->assertSame('called', $call->call($shortcut));
			$this->assertSame(0, $ARBeenHere['helene.get.templates']['/target/']);
		}
	}
}
