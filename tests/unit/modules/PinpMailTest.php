<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3).'/lib/ar.php';
require_once dirname(__DIR__, 3).'/lib/modules/mod_pinp.phtml';

class PinpMailTest extends TestCase {
	private function compileMail($arguments) {
		$compiler = new pinp('', 'object->', '$object->_');
		$compiled = $compiler->compile('<pinp>$result = mail($to, $subject, $message'.$arguments.');</pinp>');
		$this->assertNull($compiler->error);
		$this->assertStringContainsString('$object->mail(', $compiled);
		return $compiled;
	}

	private function sandbox() {
		$object = $this->getMockBuilder(ar_core_pinpSandbox::class)
			->setConstructorArgs(array(new stdClass()))
			->onlyMethods(array('sendMail'))
			->getMock();
		$object->to = 'recipient@example.invalid';
		$object->subject = 'Test subject';
		$object->message = 'Test body';
		return $object;
	}

	/** @dataProvider ordinaryCalls */
	public function testOrdinaryCallsUseOnlyFourTransportArguments($arguments, $headers, $params, $transportResult) {
		$object = $this->sandbox();
		$object->headers = $headers;
		$object->params = $params;
		$object->expects($this->once())->method('sendMail')
			->willReturnCallback(function (...$args) use ($object, $headers, $transportResult) {
				$this->assertSame(array($object->to, $object->subject, $object->message, $headers), $args);
				return $transportResult;
			});
		eval('?>'.$this->compileMail($arguments));
		$this->assertSame($transportResult, $object->result);
	}

	public static function ordinaryCalls() {
		return array(
			'no headers' => array('', array(), '', true),
			'string headers' => array(', $headers', "From: sender@example.invalid\r\nReply-To: reply@example.invalid", '', true),
			'array headers' => array(', $headers', array('From' => 'sender@example.invalid'), '', true),
			'empty fifth argument' => array(', $headers, $params', array(), '', true),
			'null fifth argument' => array(', $headers, $params', array(), null, true),
			'transport failure' => array('', array(), '', false)
		);
	}

	/** @dataProvider rejectedParameters */
	public function testCommandLineOptionsNeverReachTransport($params) {
		$object = $this->sandbox();
		$object->headers = array();
		$object->params = $params;
		$object->expects($this->never())->method('sendMail');
		eval('?>'.$this->compileMail(', $headers, $params'));
		$this->assertFalse($object->result);
	}

	public static function rejectedParameters() {
		return array(
			'envelope sender option' => array('-fsender@example.invalid'),
			'mailer options' => array('-X/tmp/pinp-mail-log'),
			'whitespace' => array(' '),
			'false is not an empty string' => array(false),
			'zero is not an empty string' => array(0),
			'array' => array(array())
		);
	}
}
