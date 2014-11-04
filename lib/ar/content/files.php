<?php

	ar_pinp::allow( 'ar_content_files');
	ar_pinp::allow( 'ar_content_filesFile' );

	class ar_content_files extends arBase {
	}

	interface ar_content_filesFileInterface {
		public function read( $length );
		public function readfile();
		public function write( $string, $length = null );
		public function eof();
		public function size();
		public function getc();
		public function gets($length = null);
		public function getContents( $offset = -1, $maxLen = null );
		public function seek( $offset, $whence = SEEK_SET );
		public function tell();
		public function truncate( $size );
		public function rewind();
	}

	class ar_content_filesFile extends arBase implements ar_content_filesFileInterface {

		private $resource = null;

		public function __construct( $resource ) {
			$this->resource = $resource;
		}

		public function __destruct() {
			if ($this->resource) {
				fclose( $this->resource );
			}
		}

		public function read( $length ) {
			return fread( $this->resource, $length );
		}

		public function readfile() {
			return fpassthru( $this->resource );
		}

		public function getContents( $length = -1, $offset = -1 ) {
			return stream_get_contents( $this->resource, $length, $offset );
		}

		public function write( $string, $length = null ) {
			return fwrite( $this->resource, (string) $string, $length );
		}

		public function eof() {
			return feof( $this->resource );
		}

		public function size() {
			$curpos = ftell( $this->resource );
			fseek( $this->resource, 0, SEEK_END );
			$size = ftell( $this->resource );
			fseek( $this->resource, $curpos );
			return $size;
		}

		public function getc() {
			return fgetc( $this->resource );
		}

		public function getcsv( $length=0, $delimiter=',', $enclosure='"', $escape='\\' ) {
			return fgetcsv( $this->resource, $length, $delimiter, $enclosure, $escape );
		}

		public function gets( $length = null ) {
			return fgets( $this->resource, $length );
		}

		public function seek( $offset, $whence = SEEK_SET ) {
			return fseek( $this->resource, $offset, $whence );
		}

		public function tell() {
			return ftell( $this->resource );
		}

		public function truncate( $size ) {
			return ftruncate( $this->resource, $size );
		}

		public function rewind() {
			return rewind( $this->resource );
		}

		protected function getResource() {
			return $this->resource;
		}

		public function getMetaData() {
			return stream_get_meta_data($this->resource);
		}

	}
?>