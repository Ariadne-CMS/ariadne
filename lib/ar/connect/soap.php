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
			//return new ar_connect_soapHeader( $namespace, $name, $data, $mustUnderstand, $actor );
		}

		public static function param( $data, $name ) {
			return new ar_connect_soapParam( $data, $name );
		}
		
		public static function variable( $data, $encoding, $type_name = '', $type_namespace = '', $node_name = '', $node_namespace = '') {
			return new SoapVar( $data, $encoding, $type_name, $type_namespace, $node_name, $node_namespace );
		}
		
	}

	
	class ar_connect_soapClient extends arWrapper {
		
		function __construct( $wsdl, $options = array() ) {
			$soapClient = new SoapClient( $wsdl, $options );
			parent::__construct( $soapClient );
		}	

		function _soapCall( $name, $arguments, $options = Array(), $inputHeaders = Array(), &$outputHeaders = Array() ) {
			try {
				//$result = new arWrapper( $this->wrapped->__soapCall( $name, $arguments, $options, $inputHeaders, $outputHeaders ) );
				$result = $this->wrapped->__soapCall( $name, $arguments, $options, $inputHeaders, $outputHeaders );
			} catch( Exception $e ) {
				$result = ar::error( $e->getMessage(), $e->getCode() );
			}
			return $result;
		}

		function _setSoapHeaders($soapHeaders = null) {
			$this->wrapped->__setSoapHeaders($soapHeaders);
		}

		function _setLocation($location) {
			$this->wrapped->__setLocation($location);
		}

		function _getFunctions() {
			return $this->wrapped->__getFunctions();
		}

		function _getLastResponse() {
			return $this->wrapped->__getLastResponse();
		}

		function _getLastRequest() {
			return $this->wrapped->__getLastRequest();
		}

	}

	class ar_connect_soapServer extends arWrapper {
		
		function __construct( $wsdl, $options = array() ) {
			$soapServer = new SoapServer( $wsdl, $options );
			parent::__construct( $soapServer );
		}
		
	}
	
	class ar_connect_soapHeader extends arWrapper {
		
		function __construct( $namespace, $name, $data = null, $mustUnderstand = false, $actor = null ) {
			if (isset($actor)) {
				$soapHeader = new SoapHeader( $namespace, $name, $data, $mustUnderstand, $actor);
			} else {
				$soapHeader = new SoapHeader( $namespace, $name, $data, $mustUnderstand);
			}
			parent::__construct( $soapHeader );
		}
	
	}
	
	class ar_connect_soapParam extends arWrapper {
		
		function __construct( $data, $name ) {
			return  new SoapParam( $data, $name );
			
		}
	
	}
	
	class ar_connect_soapVar extends arWrapper {
		
		function __construct( $data, $encoding, $type_name = '', $type_namespace = '', $node_name = '', $node_namespace = '' ) {
			$soapVar = new SoapVar( $data, $encoding, $type_name, $type_namespace, $node_name, $node_namespace ); 
			parent::__construct( $soapVar );
		}
		
	}
	
?>
