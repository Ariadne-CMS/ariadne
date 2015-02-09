<?php
class ar_listExressionTest extends AriadneBaseTest {
	public function testFTPGetNonExisting() {
		$prep = [
			0 => 'a - odd,first',
			1 => 'b - even',
			2 => 'c - odd',
			3 => 'd - even,last'
		];

		$list = array( 'a', 'b', 'c', 'd' );
		$le = ar::listExpression( $list )->pattern( '(odd even?)*', " single | first .* last " );
		foreach ($list as $position => $value) {
			$line = $value." - ".implode(',',$le->item( $position));
			$this->assertEquals($prep[$position], $line);
		}
	}
}
