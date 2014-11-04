<?php
	class wkhtmltoimage {

		function __construct( $config = array() ) {
			if (!$config['cmd']) {
				$config['cmd'] = '/usr/local/bin/wkhtmltoimage --disable-local-file-access ';
			}

			if (!$config['temp']) {
				$context = pobject::getContext();
				$me = $context["arCurrentObject"];
				$config['temp'] = $me->store->get_config( "files" ) . "temp/";
			}

			$this->config = $config;
			$this->options = array(
				'format' => 'png'
			);
			$this->cookies = array();
			$this->headers = array();
		}


		function generateFromURL( $url ) {
			if ( !preg_match( '|^https?://|', $url ) ) {
				return ar_error::raiseError( "wkhtmltoimage: '$url' is not a valid URL", 201 );
			}

			$url = escapeshellarg( $url );
			$tempFile = tempnam( $this->config['temp'], 'pdf' );
			if ( !$tempFile ) {
				return ar_error::raiseError( "wkhtmltoimage: could not create a temporary file", 202 );
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
				return ar_error::raiseError( "wkhtmltoimage: error ($execResult) while trying to generate image: " . implode( "\n", (array) $execOutput ), 203 );
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
				case 'ignore-load-errors':
					$this->options[ $name ] = true;
				break;
				case 'format':
				case 'encoding':
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


	class pinp_wkhtmltoimage {
		private $instance;

		function __construct( $options = array() ) {
			$this->instance = new wkhtmltoimage();
			$this->instance->setOptionList( $options );
		}

		function _generateFromURL( $url ) {
			return $this->instance->generateFromURL( $url );
		}

		static function _get( $options = array() ) {
			return new pinp_wkhtmltoimage( $options );
		}



	}
