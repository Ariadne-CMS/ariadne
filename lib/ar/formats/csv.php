<?php
	ar_pinp::allow( 'ar_formats_csv');
	ar_pinp::allow( 'ar_formats_csvData');
	ar_pinp::allow( 'ar_formats_csvStream');

	class ar_formats_csv extends arBase {

		static protected $configuration = array(
			"delimiter" => ",",
			"enclosure" => '"',
			"escape"    => '"',
			"encoding"  => "ISO-8859-1",
			"lineEnd"   => "\n",
			"headers"   => true
		);

		public static function configure( $option, $value ) {
			if ( isset( self::$configuration[$option] ) ) {
				self::$configuration[$option] = $value;
			}
			return $this;
		}

		public static function parse( $text, $configuration = array() ) {
			$configuration = $configuration + self::$configuration;
			return new ar_formats_csvData( $text, $configuration );
		}

		public static function load( $file, $configuration = array() ) {
			$configuration = $configuration + self::$configuration;
			$file->rewind();
			return new ar_formats_csvData( $file->getContents(), $configuration );
		}

		public static function stream( $file, $configuration = array() ) {
			$configuration = $configuration + self::$configuration;
			return new ar_formats_csvStream( $file, $configuration );
		}

		public static function compile( $data, $configuration = array() ) {
			$data = self::prepareData( $data, $configuration );
			return (string) $data;
		}

		public static function save( $file, $data, $configuration = null ) {
			$data = self::prepareData( $data, $configuration );
			return $file->write( (string) $data );
		}

		protected static function prepareData( $data, $configuration ) {
			$configuration = $configuration + self::$configuration;
			if ( ! ( $data instanceof ar_formats_csvFeedInterface ) ) {
				$data = new ar_formats_csvData( (array) $data, $configuration );
			}
			return $data;
		}

		public static function convertToArray( $csvdata ) {
			if ( $csvdata instanceof ar_formats_csvFeedInterface ) {
				$result = array();
				$headers = $csvdata->getHeaders();
				if ( is_array( $headers ) && count( $headers ) ) {
					$result[] = $headers;
				}
				foreach ( (array) $csvdata as $csvLine ) {
					$result[] = (array) $csvLine;
				}
			} else {
				$result = (array) $csvdata;
			}
			return $result;
		}

	}

	interface ar_formats_csvFeedInterface {}

	class ar_formats_csvData extends ArrayObject implements ar_formats_csvFeedInterface {

		protected $configuration = array(
			"delimiter"      => ",",
			"enclosure"      => '"',
			"escape"         => '"',
			"encoding"       => "ISO-8859-1",
			"lineEnd"        => "\n",
			"forceEnclosure" => false,
			"headers"        => true
		);

		protected $current = 0;

		public function __construct( $text, $configuration = array() ) {
			$this->configuration = $configuration + $this->configuration;
			if ( !is_array($text) ) {
				$text = $this->convertToUTF8( $text );
				$maxBuffer = 10 * 1024 * 1024;
				$file = fopen( "php://temp/maxmemory:$maxBuffer", 'r+' );
				fputs( $file, $text );
				rewind( $file );
				do {
					$line = fgetcsv( $file, 0, $this->configuration['delimiter'], $this->configuration['enclosure'], $this->configuration['escape'] );
					$lines[] = $line;

				} while ( !feof( $file ) && $line !== false );
				fclose($file);
				unset($file);
			} else {
				$lines = $text;
			}
			if ( $this->configuration['headers'] === true ) { // first line contains the header information
				$this->configuration['headers'] = (array) new ar_formats_csvLine( array_shift( $lines ), $this->configuration );
			} else if ( is_array( $this->configuration['headers'] ) ) {
				$this->configuration['headers'] = (array) new ar_formats_csvLine( $this->configuration['headers'], $this->configuration );
			}
			$parsedLines = array();
			foreach ( $lines as $key => $line ) {
				$parsedLines[ $key ] = new ar_formats_csvLine( $line, $this->configuration );
			}
			parent::__construct( $parsedLines );
		}

		public function current() {
			return $this[$this->current];
		}

		public function key() {
			return $this->current;
		}

		public function next() {
			$this->current++;
		}

		public function rewind() {
			$this->current = 0;
		}

		public function valid() {
			return isset( $this[$this->current] );
		}

		public function __toString() {
			$result = '';
			if ( is_array( $this->configuration['headers'] ) && count( $this->configuration['headers'] ) ) {
				$headers = new ar_formats_csvLine( $this->configuration['headers'], $this->configuration );
				$result .= (string) $headers . $this->configuration['lineEnd'];
			}
			foreach ( (array) $this as $csvLine ) {
				$csvLine->configure( $this->configuration );
				$result .= (string) $csvLine . $this->configuration['lineEnd'];
			}
			$result = substr( $result, 0, strlen( $result ) - strlen( $this->configuration['lineEnd'] ) );
			return $result;
		}

		public function convertToArray() {
			return ar_formats_csv::convertToArray( $this );
		}

		public function __call( $name, $arguments ) {
			if (($name[0]==='_')) {
				$realName = substr($name, 1);
				if (ar_pinp::isAllowed($this, $realName)) {
					return call_user_func_array(array($this, $realName), $arguments);
				} else {
					trigger_error("Method $realName not found in class ".get_class($this), E_USER_WARNING);
				}
			} else {
				trigger_error("Method $name not found in class ".get_class($this), E_USER_WARNING);
			}
		}

		public function configure( $name, $value ) {
			$this->configuration[$name] = $value;
			return $this;
		}

		public function getHeaders() {
			return $this->configuration['headers'];
		}

		protected function convertToUTF8( $text ) {
			if ( strtolower( $this->configuration['encoding'] ) != 'utf-8' ) {
				$text = iconv( $this->configuration['encoding'], 'UTF-8', $text );
			}
			return $text;
		}

	}

	class ar_formats_csvLine extends ArrayObject implements arKeyValueStoreInterface {

		protected $configuration = array(
			"delimiter"     => ",",
			"enclosure"     => '"',
			"escape"        => '"',
			"forceEnclosure"=> false,
			"headers"       => array()
		);

		protected static function str_getcsv( $input, $delimiter=',', $enclosure='"', $escape='\\' ) {
			if ( function_exists('str_getcsv') ) {
				$elements = str_getcsv( $input, $delimiter, $enclosure, $escape );
			} else {
				$maxBuffer = 10 * 1024 * 1024;
				$file = fopen( "php://temp/maxmemory:$maxBuffer", 'r+' );
				fputs( $file, $input );
				rewind( $file );
				$elements = fgetcsv( $file, 0, $delimiter, $enclosure, $escape );
				fclose($file);
				unset($file);
			}
			return $elements;
		}

		public function __construct( $line, $configuration = array() ) {
			$this->configuration = $configuration + $this->configuration;
			if ( is_array( $line ) ) {
				$elements = $line;
			} else {
				$elements = self::str_getcsv( $line, $this->configuration['delimiter'], $this->configuration['enclosure'], $this->configuration['escape'] );
			}
			if ( is_array( $this->configuration['headers'] ) && count( $this->configuration['headers'] ) ) {
				$keyedElements = array();
				foreach ( $elements as $key => $value ) {
					$keyedElements[ $this->configuration['headers'][ $key ] ] = $value;
				}
			} else {
				$keyedElements = $elements;
			}
			parent::__construct( $keyedElements );
		}

		public function configure( $name, $value = null ) {
			if ( is_array( $name ) ) {
				$this->configuration = $name + $this->configuration;
			} else {
				$this->configuration[$name] = $value;
			}
		}

		public function __toString() {
			$elements = (array) $this;
			foreach( $elements as $element ) {
				$result .= $this->escape( $element );
				$result .= $this->configuration['delimiter'];
			}
			$result = substr( $result, 0, strlen( $result ) - 1 );
			return $result;
		}

		protected function escape( $value ) {
			if ( strpos( $value, $this->configuration['delimiter'] ) !== false
				|| strpos( $value, $this->configuration['enclosure'] ) !== false
				|| strpos( $value, $this->configuration['lineEnd'] ) !== false
				// below is to make sure the gerenated csv is compatible with standard implementations
				|| strpos( $value, ',' ) !== false
				|| strpos( $value, '\n' ) !== false
				|| strpos( $value, '"' ) !== false
				|| $this->configuration['forceEnclosure']
			) {
				return $this->configuration['enclosure']
				. str_replace( $this->configuration['enclosure'],
					$this->configuration['escape'] . $this->configuration['enclosure'], $value )
				. $this->configuration['enclosure'];
			} else {
				return $value;
			}
		}

		protected function getOffset( $offset ) {
			if ( is_numeric($offset) && count( $this->configuration['headers'] ) ) {
				$offset = $this->configuration['headers'][$offset];
			}
			return (string) $offset;
		}
		public function offsetGet( $offset ) {
			return parent::offsetGet( $this->getOffset( $offset ) );
		}

		public function offsetSet( $offset, $value ) {
			return parent::offsetSet( $this->getOffset( $offset ), $value );
		}

		public function offsetExists( $offset ) {
			return parent::offsetExists( $this->getOffset( $offset ) );
		}

		public function offsetUnset( $offset ) {
			return parent::offsetUnset( $this->getOffset( $offset ) );
		}

		public function getvar( $name ) {
			return $this->offsetGet($name);
		}

		public function putvar( $name, $value ) {
			$this->offsetSet($name, $value);
		}
	}

	class ar_formats_csvStream extends arBase implements Iterator, ar_formats_csvFeedInterface {

		protected $configuration = array(
			"seperator"      => ",",
			"quotation"      => '"',
			"escape"         => '"',
			"encoding"       => "ISO-8859-1",
			"lineEnd"        => "\n",
			"forceEnclosure" => false,
			"headers"        => true
		);

		protected $file = null;
		protected $fileContainsHeaders = true;

		public function __construct( $file, $configuration = array() ) {
			$this->configuration = $configuration + $this->configuration;
			$this->file = $file;

			if ( $this->configuration['headers'] === true ) { // first line contains the header information
				$this->configuration['headers'] = (array) $this->getLine();
				$this->fileContainsHeaders = true;
			} else if ( is_array( $this->configuration['headers'] ) ) {
				$this->configuration['headers'] = (array) new ar_formats_csvLine( $this->configuration['headers'], $this->configuration );
				$this->fileContainsHeaders = false;
			}
		}

		public function configure( $name, $value ) {
			$this->configuration[$name] = $value;
			return $this;
		}

		public function getHeaders() {
			return $this->configuration['headers'];
		}

		public function __toString() {
			$result = '';
			if ( is_array( $this->configuration['headers'] ) && count($this->configuration['headers']) ) {
				$headers = new ar_formats_csvLine( $this->configuration['headers'], $this->configuration );
				$result .= (string) $headers . $this->configuration['lineEnd'];
			}
			$this->file->rewind();
			if ( $this->fileContainsHeaders ) {
				$this->getLine();
			}
			while ( $line = $this->getLine() ) {
				$result .= (string) $line . $this->configuration['lineEnd'];
			}
			$result = substr( $result, 0, strlen( $result ) - strlen( $this->configuration['lineEnd'] ) );
			return $result;
		}

		public function convertToArray() {
			return ar_formats_csv::convertToArray( $this );
		}

		protected function convertToUTF8( $text ) {
			if ( strtolower( $this->configuration['encoding'] ) != 'utf-8' ) {
				$text = iconv( $this->configuration['encoding'], 'UTF-8', $text );
			}
			return $text;
		}

		protected function getLine() {
			$line = null;
			$linedata = $this->file->getcsv( 0, $this->configuration['delimiter'], $this->configuration['enclosure'], $this->configuration['escape'] );
			if ( $linedata ) {
				foreach( $linedata as $key => $element ) {
					$linedata[$key] = $this->convertToUTF8( $element );
				}
				$line = new ar_formats_csvLine( $linedata, $this->configuration );
			}
			return $line;
		}

		public function current() {
			if ( !$this->currentLine ) {
				$this->currentLine = $this->getLine( $this->file );
			}
			return $this->currentLine;
		}

		public function key() {
			return $this->currentIndex;
		}

		public function next() {
			$this->currentIndex++;
			$this->currentLine = $this->getLine( $this->file );
		}

		public function rewind() {
			$this->currentIndex = 0;
			$this->file->rewind();
			$this->currentLine = $this->getLine( $this->file );
		}

		public function valid() {
			return isset( $this->currentLine );
		}

	}
