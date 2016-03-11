<?php
class ar_urlTest extends AriadneBaseTest {

	function testparseUrl()
	{
		$starturl = 'http://www.ariadne-cms.org/?frop=1';
		$url = ar::url( $starturl );
		$this->assertInstanceOf( 'ar_url', $url );
		$this->assertEquals( $starturl, ''.$url );

		$starturl = 'http://www.ariadne-cms.org/?frop=1&frml=2';
		$url = ar::url( $starturl );
		$url->fragment = 'test123';
		$this->assertEquals( $starturl .'#test123', ''.$url);

		$starturl = 'http://www.ariadne-cms.org/view.html?foo=some+thing';
		$url = ar::url( $starturl );
		$this->assertInstanceOf( 'ar_url', $url );
		$this->assertInstanceOf( 'ar_urlQuery', $url->query );
		$this->assertEquals( $starturl, ''.$url );
		$this->assertEquals( $url->query['foo'], 'some thing' );
	}

	function testparseUrlQueryMultipleElements()
	{
		$starturl = 'http://www.ariadne-cms.org/?test=test&test=frop';
		$url = ar::url( $starturl );
		$this->assertInstanceOf( 'ar_url', $url );
		$this->assertInstanceOf( 'ar_urlQuery', $url->query );
		$this->assertEquals( 'frop', ''.$url->query['test'], "PHP url parser, the second instance has precedence");
		$this->assertNotEquals( $starturl, ''.$url );
	}

	function testparseUrlQueryUnnumberedElements()
	{
		$starturl = 'http://www.ariadne-cms.org/?test[]=test&test[]=frop';
		$url = ar::url( $starturl );
		$this->assertInstanceOf( 'ar_url', $url );
		$this->assertInstanceOf( 'ar_urlQuery', $url->query );
		$this->assertEquals( ['test', 'frop'], $url->query['test'], "Auto indexed array from query");
		$this->assertEquals( (string)$url, (string)ar::url($url) );
	}

	function testparseUrlQueryNumberedElements()
	{
		$starturl = 'http://www.ariadne-cms.org/?test[1]=test&test[0]=frop';
		$url = ar::url( $starturl );
		$this->assertInstanceOf( 'ar_url', $url );
		$this->assertInstanceOf( 'ar_urlQuery', $url->query );
		$this->assertEquals( ['frop', 'test'], $url->query['test'], "manual index array from query");
		$this->assertEquals( (string)$url, (string)ar::url($url) );
	}

	function testModQuery()
	{
		$base = 'http://host/path/to?test=';
		$url = ar::url($base .'1');
		$url->query['test'] = "3";
		$this->assertEquals( $base .'3', (string)$url );

		$url->query['test'] = ['foo', 'bar'];
		$this->assertEquals( 'http://host/path/to?test%5B0%5D=foo&test%5B1%5D=bar', (string)$url );
	}

	function testParseAuthority()
	{
		$starturl = 'http://foo:bar@www.ariadne-cms.org:80/';
		$url = ar::url( $starturl );
		$this->assertInstanceOf( 'ar_url', $url );
		$this->assertEquals( $starturl, $url.'' );
	}

	function testCopyQuery()
	{
		$url1 = ar::url('http://host/path/to?test=1');
		$url2 = ar::url('http://host/path/to?test=2');
		$this->assertNotEquals( $url1, $url2);

		$url2->query = $url1->query;
		$this->assertEquals( $url1, $url2);
	}

	function testCopyAndModQuery()
	{
		$url1 = ar::url('http://host/path/to?test=1');
		$url2 = ar::url('http://host/path/to?test=2');
		$this->assertNotEquals( $url1, $url2);

		$url2->query = $url1->query;
		$this->assertEquals( $url1, $url2);

		$url2->query['test'] = '3';
		$this->assertEquals( '3', $url2->query['test']);
		$this->assertEquals( '1', $url1->query['test']);

	}


	function testParseCommonURLS()
	{
		$commonUrls = [
			'ftp://ftp.is.co.za/rfc/rfc1808.txt',
			'http://www.ietf.org/rfc/rfc2396.txt',
			'ldap://[2001:db8::7]/c=GB?objectClass?one',
			'mailto:John.Doe@example.com',
			'news:comp.infosystems.www.servers.unix',
			'tel:+1-816-555-1212',
			'telnet://192.0.2.16:80/',
			'urn:oasis:names:specification:docbook:dtd:xml:4.1.2',
			'//google.com',
			'../../relative/',
			'file:///C:/'
		];
		foreach ($commonUrls as $sourceUrl) {
			$url = ar::url( $sourceUrl );
			$this->assertEquals( $sourceUrl, ''.$url );
		}
	}
}
