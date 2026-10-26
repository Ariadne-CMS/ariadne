<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3).'/lib/modules/mod_pinp.phtml';

class PinpSoapReferenceClient
{
	public function _soapCall($name, $arguments, $options, $inputHeaders, &$outputHeaders)
	{
		$outputHeaders = array('status' => 'received');
		return array($name, $arguments, $options, $inputHeaders);
	}

	public function _otherCall($name, $arguments, $options, $inputHeaders, $resultHeaders)
	{
		return $resultHeaders;
	}
}

class PinpSoapCallTest extends TestCase
{
	/** @dataProvider soapCalls */
	public function testOutputHeadersAreWrittenBack($method, $target, $initial)
	{
		$compiler = new pinp('', 'object->', '$scope->_');
		$source = '<pinp>'.$initial.'$result = $client->'.$method.'($name, $arguments, $options, $inputHeaders, '.$target.');</pinp>';
		$compiled = $compiler->compile($source);
		$this->assertNull($compiler->error);
		$object = (object) array('client' => new PinpSoapReferenceClient());
		eval('?>'.$compiled);
		$this->assertSame(array(null, null, null, null), $object->result);
		if (strpos($target, '[') !== false) {
			$this->assertSame(array('status' => 'received'), $object->headers['response']);
		} else if (strpos($target, '->') !== false) {
			$this->assertSame(array('status' => 'received'), $object->holder->headers);
		} else {
			$this->assertSame(array('status' => 'received'), $object->headers);
		}
	}

	public static function soapCalls()
	{
		return array(
			'uninitialized output' => array('soapCall', '$headers', ''),
			'initialized output' => array('soapCall', '$headers', '$headers = array();'),
			'array output' => array('soapCall', '$headers["response"]', '$headers = array();'),
			'variable array index' => array('soapCall', '$headers[$key]', '$headers = array(); $key = "response";'),
			'object output' => array('soapCall', '$holder->headers', '$holder = json_decode("{}");'),
			'prefixed method' => array('_soapCall', '$headers', ''),
			'native spelling' => array('__soapCall', '$headers', ''),
			'case insensitive' => array('SOAPCALL', '$headers', ''),
		);
	}

	public function testOtherMethodsKeepTheFifthArgumentNullGuard()
	{
		$compiler = new pinp('', 'object->', '$scope->_');
		$compiled = $compiler->compile('<pinp>$result = $client->otherCall(null, array(), array(), array(), $resultHeaders);</pinp>');
		$this->assertNull($compiler->error);
		$this->assertStringContainsString('($object->resultHeaders ?? null)', $compiled);
		$object = (object) array('client' => new PinpSoapReferenceClient());
		eval('?>'.$compiled);
		$this->assertNull($object->result);
	}
}
