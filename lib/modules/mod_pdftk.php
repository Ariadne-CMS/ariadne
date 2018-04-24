<?php
	class pdftk {
		protected $config;

		public function __construct( $config = array() ) {
			if (!$config['cmd']) {
				$config['cmd'] = '/usr/bin/pdftk ';
			}

			if (!$config['temp']) {
				$context = pobject::getContext();
				$me = $context["arCurrentObject"];
				$config['temp'] = $me->store->get_config( "files" ) . "temp/";
			}

			$this->config = $config;
		}

		public function concat( $files = array() ) {
			if (!sizeof($files)) {
				return false;
			}

			$inputs = array();
			$i = 1;
			foreach ($files as $file) {
				$tempFile = tempnam( $this->config['temp'], 'pdftk-input-' );
				if ( !$tempFile ) {
					return ar_error::raiseError( "pdftk: could not create a temporary input file", 202 );
				}
				$inputs[] = $tempFile;
				file_put_contents($tempFile, $file);
			}
			unset($files);

			$outputFile = tempnam( $this->config['temp'], 'pdftk-output-' );
			if ( !$outputFile ) {
				return ar_error::raiseError( "pdftk: could not create a temporary output file", 204 );
			}
			
			// pdftk in1.pdf in2.pdf cat output out1.pdf
			$execString = $this->config['cmd'];
			$execString .= " " . implode(" ", $inputs);
			$execString .= " cat output $outputFile";

			$execOutput = array();
			$execResult = 0;

			exec( $execString, $execOutput, $execResult );

			if ( $execResult != 0 ) {
				foreach ($inputs as $file) { 
					@unlink($file);
				}
				@unlink($outputFile);
				return ar_error::raiseError( "pdftk: error ($execResult) [$execString] while trying to generate PDF: " . implode( "\n", (array) $execOutput ), 203 );
			}

			$result = file_get_contents( $outputFile );

			foreach ($inputs as $file) { 
				@unlink($file);
			}
			@unlink($outputFile);
			return $result;
		}

		public function background( $files = array() ) {
			/*
				Call this with arguments:
					$files = array(
						"pdf" => $pdfData,
						"background" => $backgroundPdf
					);
			*/						
			if (!sizeof($files)) {
				return false;
			}

			$inputs = array();
			$i = 1;
			foreach ($files as $key => $file) {
				$tempFile = tempnam( $this->config['temp'], 'pdftk-input-' );
				if ( !$tempFile ) {
					return ar_error::raiseError( "pdftk: could not create a temporary input file", 202 );
				}
				$inputs[$key] = $tempFile;
				file_put_contents($tempFile, $file);
			}
			unset($files);

			$outputFile = tempnam( $this->config['temp'], 'pdftk-output-' );
			if ( !$outputFile ) {
				return ar_error::raiseError( "pdftk: could not create a temporary output file", 204 );
			}
			
			// pdftk in1.pdf in2.pdf cat output out1.pdf
			// system("pdftk.exe \"$frontpage\" background $frontpage_file output \"$wm_frontpage\"");
			$execString = $this->config['cmd'];
			$execString .= " " . $inputs["pdf"];
			$execString .= " background " . $inputs["background"] . " output $outputFile";

			$execOutput = array();
			$execResult = 0;

			exec( $execString, $execOutput, $execResult );

			if ( $execResult != 0 ) {
				foreach ($inputs as $file) { 
					@unlink($file);
				}
				@unlink($outputFile);
				return ar_error::raiseError( "pdftk: error ($execResult) while trying to generate PDF: " . implode( "\n", (array) $execOutput ), 203 );
			}

			$result = file_get_contents( $outputFile );

			foreach ($inputs as $file) { 
				@unlink($file);
			}
			@unlink($outputFile);
			return $result;
		}

		public function pages( $args = array() ) {
			/*
				Call this with arguments:
					$args = array(
						"pdf" => $pdfData,
						"pages" => "1-r2" // removes the last page;
					);
			*/						
			if (!sizeof($args)) {
				return false;
			}

			$inputs = array();

			$tempFile = tempnam( $this->config['temp'], 'pdftk-input-' );
			if ( !$tempFile ) {
				return ar_error::raiseError( "pdftk: could not create a temporary input file", 202 );
			}
			$inputs['pdf'] = $tempFile;
			file_put_contents($tempFile, $args['pdf']);

			$outputFile = tempnam( $this->config['temp'], 'pdftk-output-' );
			if ( !$outputFile ) {
				return ar_error::raiseError( "pdftk: could not create a temporary output file", 204 );
			}
			
			// pdftk in1.pdf cat 1-r2 output out1.pdf
			// system("pdftk.exe \"$frontpage\" background $frontpage_file output \"$wm_frontpage\"");
			$execString = $this->config['cmd'];
			$execString .= " " . $inputs["pdf"];
			$execString .= " cat " . escapeshellcmd($args["pages"]) . " output $outputFile";

			$execOutput = array();
			$execResult = 0;

			exec( $execString, $execOutput, $execResult );

			if ( $execResult != 0 ) {
				foreach ($inputs as $file) { 
					@unlink($file);
				}
				@unlink($outputFile);
				return ar_error::raiseError( "pdftk: error ($execResult) while trying to generate PDF: " . implode( "\n", (array) $execOutput ), 203 );
			}

			$result = file_get_contents( $outputFile );

			foreach ($inputs as $file) { 
				@unlink($file);
			}
			@unlink($outputFile);
			return $result;
		}
	}

	class pinp_pdftk {
		private $instance;

		public function __construct() {
			$this->instance = new pdftk();
		}

		public function _concat( $files = array() ) {
			return $this->instance->concat( $files );
		}
		
		public function _background( $args = array() ) {
			return $this->instance->background( $args );
		}

		public function _pages( $args = array() ) {
			return $this->instance->pages( $args );
		}

		public static function _get() {
			return new pinp_pdftk();
		}
	}
