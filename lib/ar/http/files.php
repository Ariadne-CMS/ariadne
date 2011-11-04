<?php

	ar_pinp::allow( 'ar_http_files');
	ar_pinp::allow( 'ar_http_filesRegistry');
	
	class ar_http_files extends arBase {
	
		protected static $store = null;
	
		protected static function getStore( $store = null ) {
			if ( !$store ) {
				$store = self::$store;
			}
			if ( !$store ) {
				self::$store =  ar('session')->get();
				if ( !self::$store ) {
					self::$store = ar('session')->start();
				}
				$store = self::$store;
			}
			return $store;
		}
	
		public static function ls() {
			return $_FILES;
		}
		
		public static function getfile( $filename ) {
			// return opened file if exists, null otherwise
			$file = null;
			$info = $_FILES[$filename];
			if ( $info && is_uploaded_file( $info['tmp_name'] ) ) {
				$fp = fopen( $info['tmp_name'], 'r+b' );
				$file = new ar_content_filesFile( $fp );
			}
			return $file;
		}		

		public static function registry( $store = null ) {
			$store = self::getStore( $store );
			return new ar_http_filesRegistry( $store );
		}
		
	}
	
	class ar_http_filesRegistry extends arBase {

		protected $store = null;
		
		public function __construct( $store ) { 
			$this->store = $store;
		}
		
		public function ls() {
			return $this->store->getvar('registeredFiles');
		}
		
		public function getfile( $filename, $nls = 'none' ) {
			$registeredFiles = $this->ls();
			$info = $registeredFiles[$filename][$nls];
			if ($info && $info[$filename.'_temp']) {
				// check before removing
				$tempfile = preg_replace( "|[\\\/]|", "", $info[$filename.'_temp'] );
				$tempOb = ar::context()->getObject();
				$tempfile = $tempOb->store->get_config('files').'temp/'.$tempfile;
				$resource = fopen( $tempfile, 'r+b' );
				return new ar_content_filesFile( $resource );
			} else {
				return null;
			}
		}
		
		public function putfile( $filename, $nls = 'none') {
			$registeredFiles = (array) $this->store->getvar('registeredFiles');
			$error = '';
			$fileInfo = ldRegisterFile( $filename, $error );
			if (!$error) {
				$registeredFiles[ $filename ][ $nls ] = $fileInfo;
				$this->store->putvar('registeredFiles', $registeredFiles );
				$result = $fileInfo;
			} else {
				$result = ar('error')->raiseError( $error, 501 );
			}		
			return $result;
		}
		
		public function remove( $filename, $nls = 'none') {
			$registeredFiles = $this->ls();
			$info = $registeredFile[$filename][$nls];
			unset($registeredFiles[$filename][$nls]);
			if (!$registeredFiles[$filename]) {
				unset($registeredFiles[$filename]);
			}
			$this->store->putvar('registeredFiles', $registeredFiles );
			if ($info && $info[$filename.'_temp']) {
				// check before removing
				$tempfile = preg_replace( "|[\\\/]|", "", $info[$filename.'_temp'] );
				$tempOb = ar::context()->getObject();
				$tempfile = $tempOb->store->get_config('files').'temp/'.$tempfile;
				if ($tempfile && file_exists($tempfile) ) {
					unlink($tempfile);
				}
			}
		}
		
	}
?>