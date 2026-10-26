<?php

use PHPUnit\Framework\TestCase;

class SoapTraceTest extends TestCase
{
	public function testTracePropertiesDelegateToSoapGetters()
	{
		if (!extension_loaded('soap')) {
			$this->markTestSkipped('The SOAP extension is required.');
		}

		$client = new ar_connect_soapClient(null, array(
			'location' => 'http://localhost/',
			'uri' => 'urn:soap-trace-test',
			'trace' => true
		));
		$soap = $client->__unwrap();
		$properties = array(
			'__last_response' => '_getLastResponse',
			'__last_response_headers' => '_getLastResponseHeaders',
			'__last_request' => '_getLastRequest',
			'__last_request_headers' => '_getLastRequestHeaders'
		);

		foreach ($properties as $name => $getter) {
			$this->assertNull($client->$name);
			// Seed trace data without a network request; the native properties
			// are private on newer PHP versions.
			$property = new ReflectionProperty(SoapClient::class, $name);
			$property->setValue($soap, 'First trace for '.$name);
			$this->assertSame('First trace for '.$name, $client->$name);
			$this->assertSame($client->$getter(), $client->$name);

			$property->setValue($soap, 'Next trace for '.$name);
			$this->assertSame('Next trace for '.$name, $client->$name);
		}
	}

	public function testOtherPropertiesUseTheParentWrapper()
	{
		// Inject a simple wrapped object to exercise the inherited fallback.
		$client = (new ReflectionClass(ar_connect_soapClient::class))->newInstanceWithoutConstructor();
		$property = new ReflectionProperty(arWrapper::class, 'wrapped');
		$property->setValue($client, (object) array('custom' => 'ordinary property'));
		$this->assertSame('ordinary property', $client->custom);
	}
}
