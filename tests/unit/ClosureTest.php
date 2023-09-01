<?php

class ClosureTest extends AriadneBaseTest
{

	public function setUp(): void
	{
		$this->initAriadne();
	}

	public function testClosure() {
		$closure = current( ar::get(TESTBASE.'/closure/')->call('closure.html') );
		$this->assertInstanceof( '\Closure', $closure );
		$this->assertEquals( $closure(), 'closure' );
	}

	public function testClosureCall() {
		$result = current( ar::get(TESTBASE.'/closure/')->call('closure.call.html') );
		$this->assertIsArray( $result );
		$this->assertCount( 3, $result );
		if ( is_array($result) ) {
			$values = array_values($result);
			$this->assertEquals( [ 'closure', 'closure child 1', 'closure child 2' ], $values );
		}
	}

}
?>
