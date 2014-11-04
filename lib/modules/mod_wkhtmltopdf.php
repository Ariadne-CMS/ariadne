<?php
	class wkhtmltopdf {

		function __construct( $config = array() ) {
			if (!$config['cmd']) {
				$config['cmd'] = '/usr/bin/xvfb-run -a /usr/bin/wkhtmltopdf --disallow-local-file-access ';
			}

			if (!$config['temp']) {
				$context = pobject::getContext();
				$me = $context["arCurrentObject"];
				$config['temp'] = $me->store->get_config( "files" ) . "temp/";
			}

			$this->config = $config;
			$this->options = array();
			$this->cookies = array();
			$this->headers = array();
		}


		function generateFromURL( $url ) {
			if ( !preg_match( '|^https?://|', $url ) ) {
				return ar_error::raiseError( "wkhtmltopdf: '$url' is not a valid URL", 201 );
			}

			$url = escapeshellarg( $url );
			$tempFile = tempnam( $this->config['temp'], 'pdf' );
			if ( !$tempFile ) {
				return ar_error::raiseError( "wkhtmltopdf: could not create a temporary file", 202 );
			}

			$execString = $this->config['cmd'];
			foreach ($this->options as $name => $value) {
				if ( is_bool( $name ) ) {
					$execString .= " --$name";
				} else {
					$execString .= " --$name " . escapeshellarg( $value );
				}
			}

			foreach ($this->cookies as $name => $value) {
				$execString .= " --cookie " . escapeshellarg( $name ) . " " . escapeshellarg( $value );
			}

			foreach ($this->headers as $name => $value) {
				$execString .= " --custom-header " . escapeshellarg( $name ) . " " . escapeshellarg( $value );
			}

			$execString .= " $url $tempFile";
			$execOutput = array();
			$execResult = 0;
			exec( $execString, $execOutput, $execResult );
			if ( $execResult != 0 && $execResult != 2 ) { // code 2 is for 404's encountered
				@unlink( $tempFile );
				return ar_error::raiseError( "wkhtmltopdf: error ($execResult) while trying to generate PDF: " . implode( "\n", (array) $execOutput ), 203 );
			}

			readfile( $tempFile );
			unlink( $tempFile );
		}

		function setCookieList( $cookieList = array() ) {
			if ( is_array($cookieList) ) {
				foreach( $cookieList as $name => $value) {
					$this->setOption( $name, $value );
				}
			}
		}

		function setCookie($name, $value = null) {
			$this->cookies[ $name ] = $value;
		}

		function setHeaderList( $headerList = array() ) {
			if ( is_array($headerList) ) {
				foreach( $headerList as $name => $value) {
					$this->setHeader( $name, $value );
				}
			}
		}


		function setHeader($name, $value = null) {
			$this->headers[ $name ] = $value;
		}


		function setOptionList( $optionList = array() ) {
			if ( is_array($optionList) ) {
				foreach( $optionList as $name => $value) {
					$this->setOption( $name, $value );
				}
			}
		}

		function setOption($name, $value = null) {
			if ($value === null) {
				unset( $this->options[ $name ] );
				return true;
			}
			switch ($name) {
				case 'collate':
				case 'grayscale':
				case 'ignore-load-errors':
				case 'lowquality':
				case 'no-background':
					$this->options[ $name ] = true;
				break;
				case 'copies':
				case 'dpi':
				case 'minimum-font-size':
					$this->options[ $name ] = (int) $value;
				break;
				case 'margin-bottom':
				case 'margin-top':
				case 'margin-left':
				case 'margin-right':
				case 'footer-center':
				case 'footer-font-name':
				case 'footer-font-size':
				case 'footer-html':
				case 'footer-line':
				case 'footer-right':
				case 'footer-left':
				case 'footer-spacing':
				case 'header-center':
				case 'header-font-name':
				case 'header-font-size':
				case 'header-html':
				case 'header-line':
				case 'header-right':
				case 'header-left':
				case 'header-spacing':

				case 'encoding':
				case 'orientation':
				case 'page-height':
				case 'page-size':
				case 'page-width':
				case 'username':
				case 'password':
				case 'title':
					$this->options[ $name ] = (string) $value;
				break;
				case 'zoom':
					$this->options[ $name ] = (float) $value;
				break;
				default:
					return false;
			}
			return true;
		}

	}


	class pinp_wkhtmltopdf {
		private $instance;

		function __construct( $options = array() ) {
			$this->instance = new wkhtmltopdf();
			$this->instance->setOptionList( $options );
		}

		function _generateFromURL( $url ) {
			return $this->instance->generateFromURL( $url );
		}

		static function _get( $options = array() ) {
			return new pinp_wkhtmltopdf( $options );
		}



	}
