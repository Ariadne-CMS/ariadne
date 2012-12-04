<?php

	class pinp_ZipArchive {

		function _create() {
			$context = pobject::getContext();
			$me      = $context['arCurrentObject'];

			$zip = new ZipArchiveWrapper();
			return $zip->create( tempnam( $me->store->get_config("files")."temp/", 'zip' ) );
		}

	}


	class ZipArchiveWrapper extends ZipArchive {
		protected $_filename;

		function __construct() {
		}

		function create( $filename ) {
			$this->_filename = $filename;

			$resCreate = $this->open( $this->_filename, ZipArchive::CREATE );
			if ($resCreate !== true) {
				return error::raiseError( "System-error creating temporary zip archive '".$this->filename."'", $resCreate );
			}

			return $this;
		}


		function _addFromString( $filename, $content ) {
			return $this->addFromString( $filename, $content );
		}


		function _close() {
			$this->close();
		}


		function _DownloadFile( $filename = "", $cacheSeconds = 1800, $contentType = "application/zip" ) {
		global $AR;
			$context = pobject::getContext();
			$me      = $context['arCurrentObject'];

			$size = filesize( $this->_filename );
			ldSetContent( $contentType, $size );

			$expires = time() + $cacheSeconds;
			if ($AR->user->data->login=="public" || $me->CheckPublic("read")) {
				ldSetClientCache(true, $expires);
			}
			if ( $filename ) {
				ldHeader("Content-Disposition: inline; filename=$filename");
				ldHeader("Content-length:".(string)$size);
			}
			readfile( $this->_filename );
		}


	}

?>