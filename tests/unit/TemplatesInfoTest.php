<?php

use PHPUnit\Framework\TestCase;

class TemplatesInfoFilestoreStub
{
	public $status = array();
	public $properties = array();

	public function connect($id)
	{
		return 'svn-'.$id;
	}

	public function svn_info($svn)
	{
		return array('revision' => 12);
	}

	public function svn_status($svn)
	{
		return $this->status;
	}

	public function svn_propget($svn, $property, $filename)
	{
		return $this->properties[$filename][$property] ?? null;
	}

	public function mtime($id, $filename)
	{
		return 1700000000;
	}

	public function size($id, $filename)
	{
		return strlen($filename);
	}
}

class TemplatesInfoStoreStub
{
	public $filestore;

	public function __construct($filestore)
	{
		$this->filestore = $filestore;
	}

	public function get_filestore($name)
	{
		return $this->filestore;
	}

	public function get_filestore_svn($name)
	{
		return $this->filestore;
	}
}

class TemplatesInfoObjectStub
{
	public $id = 42;
	public $data;
	public $store;

	public function CheckLogin($grant)
	{
		return true;
	}

	public function CheckConfig()
	{
		return true;
	}

	public function getInfo($svnEnabled)
	{
		$AR = (object) array('SVN' => (object) array('enabled' => $svnEnabled));
		include dirname(__DIR__, 2).'/lib/templates/pobject/system.get.templates.info.php';
		return $arResult;
	}
}

class TemplatesInfoTest extends TestCase
{
	private function getObject($filestore)
	{
		$object = new TemplatesInfoObjectStub();
		$object->store = new TemplatesInfoStoreStub($filestore);
		$object->data = (object) array(
			'config' => (object) array(
				'pinp' => array(
					'pobject' => array(
						'view.html' => array('any' => 42)
					)
				),
				'templates' => array(
					'pobject' => array('view.html' => true)
				),
				'privatetemplates' => array()
			)
		);
		return $object;
	}

	public function testReturnsSizeAndModificationTime()
	{
		$object = $this->getObject(new TemplatesInfoFilestoreStub());
		$result = $object->getInfo(false);

		$this->assertFalse($result['svn-enabled']);
		$this->assertSame(1700000000, $result['templates'][0]['mtime']);
		$this->assertSame(strlen('pobject.view.html.any.pinp'), $result['templates'][0]['size']);
		$this->assertFalse($result['templates'][0]['local']);
	}

	public function testReturnsSvnStateAndRestorableDeletedTemplates()
	{
		$filestore = new TemplatesInfoFilestoreStub();
		$filestore->status = array(
			'pobject.view.html.any.pinp' => array('wc-status' => array('item' => 'modified')),
			'pobject.missing.html.any.pinp' => array('wc-status' => array('item' => 'missing')),
			'pobject.deleted.html.any.pinp' => array('wc-status' => array('item' => 'deleted'))
		);
		$filestore->properties['pobject.missing.html.any.pinp'] = array(
			'ar:type' => 'pobject',
			'ar:function' => 'missing.html',
			'ar:language' => 'any',
			'ar:private' => 1
		);
		$object = $this->getObject($filestore);
		$object->data->config->deleted_templates = array(
			'pobject' => array('deleted.html' => array('any' => true))
		);
		$object->data->config->deleted_privatetemplates = array(
			'pobject' => array('deleted.html' => false)
		);

		$result = $object->getInfo(true);
		$byFunction = array();
		foreach ($result['templates'] as $template) {
			$byFunction[$template['function']] = $template;
		}

		$this->assertSame('modified', $byFunction['view.html']['svn-status']);
		$this->assertSame('modified', $result['svn-status']['pobject.view.html.any.pinp']['wc-status']['item']);
		$this->assertSame('missing', $byFunction['missing.html']['svn-status']);
		$this->assertTrue($byFunction['missing.html']['private']);
		$this->assertSame('deleted', $byFunction['deleted.html']['svn-status']);
	}
}
