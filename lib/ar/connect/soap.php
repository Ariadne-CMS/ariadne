<?php
	ar_pinp::allow( 'ar_connect_soap' );
	ar_pinp::allow( 'ar_connect_soapClient' );
	ar_pinp::allow( 'ar_connect_soapServer' );
	ar_pinp::allow( 'ar_connect_soapHeader' );
	ar_pinp::allow( 'ar_connect_soapParam' );
	ar_pinp::allow( 'ar_connect_soapVar' );

	class ar_connect_soap extends arBase {

		public static function client( $wsdl, $options = array() ) {
			return new ar_connect_soapClient( $wsdl, $options );
		}

		public static function server( $wsdl, $options = array() ) {
			return new ar_connect_soapServer( $wsdl, $options );
		}

		public static function header( $namespace, $name, $data = null, $mustUnderstand = false, $actor = null ) {
			if (isset($actor)) {
				$soapHeader = new SoapHeader( $namespace, $name, $data, $mustUnderstand, $actor);
			} else {
				$soapHeader = new SoapHeader( $namespace, $name, $data, $mustUnderstand);
			}
			return $soapHeader;
		}

		public static function param( $data, $name ) {
			return new SoapParam( $data, $name );
		}

		public static function variable( $data, $encoding, $type_name = '', $type_namespace = '', $node_name = '', $node_namespace = '') {
			return new SoapVar( $data, $encoding, $type_name, $type_namespace, $node_name, $node_namespace );
		}

	}


	class ar_connect_soapClient extends arWrapper {

		public function __construct( $wsdl, $options = array() ) {
			$soapClient = new SoapClient( $wsdl, $options );
			parent::__construct( $soapClient );
		}

		public function _soapCall( $name, $arguments, $options = array(), $inputHeaders = array(), &$outputHeaders = array() ) {
			try {
				$result = $this->wrapped->__soapCall( $name, $arguments, $options, $inputHeaders, $outputHeaders );
			} catch( Exception $e ) {
				$result = ar::error( $e->getMessage(), $e->getCode() );
			}
			return $result;
		}

		public function _setSoapHeaders($soapHeaders = null) {
			$this->wrapped->__setSoapHeaders($soapHeaders);
		}

		public function _setLocation($location) {
			$this->wrapped->__setLocation($location);
		}

		public function _getFunctions() {
			return $this->wrapped->__getFunctions();
		}

		public function _getTypes() {
			return $this->wrapped->__getTypes();
		}

		public function _getLastResponse() {
			return $this->wrapped->__getLastResponse();
		}

		public function _getLastResponseHeaders() {
			return $this->wrapped->__getLastResponseHeaders();
		}

		public function _getLastRequest() {
			return $this->wrapped->__getLastRequest();
		}

		public function _getLastRequestHeaders() {
			return $this->wrapped->__getLastRequestHeaders();
		}

		public function _setCookie( $name, $value = null ) {
			return $this->wrapped->__setCookie( $name, $value );
		}
	}

	class ar_connect_soapServer extends arWrapper {

		public function __construct( $wsdl, $options = array() ) {
			$soapServer = new SoapServer( $wsdl, $options );
			parent::__construct( $soapServer );
		}

	}

	class ar_connect_soapHeader extends arWrapper {

		public function __construct( $namespace, $name, $data = null, $mustUnderstand = false, $actor = null ) {
			if (isset($actor)) {
				$soapHeader = new SoapHeader( $namespace, $name, $data, $mustUnderstand, $actor);
			} else {
				$soapHeader = new SoapHeader( $namespace, $name, $data, $mustUnderstand);
			}
			parent::__construct( $soapHeader );
		}

	}

	class ar_connect_soapParam extends arWrapper {

		public function __construct( $data, $name ) {
			return  new SoapParam( $data, $name );

		}

	}

	class ar_connect_soapVar extends arWrapper {

		public function __construct( $data, $encoding, $type_name = '', $type_namespace = '', $node_name = '', $node_namespace = '' ) {
			$soapVar = new SoapVar( $data, $encoding, $type_name, $type_namespace, $node_name, $node_namespace );
			parent::__construct( $soapVar );
		}

	}
