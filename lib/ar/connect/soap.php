<?php
	ar_pinp::allow( 'ar_connect_soap' );
	ar_pinp::allow( 'ar_connect_soapClient' );
	ar_pinp::allow( 'ar_connect_soapServer' );
	ar_pinp::allow( 'ar_connect_soapHeader' );
	ar_pinp::allow( 'ar_connect_soapParam' );
	ar_pinp::allow( 'ar_connect_soapVar' );
	ar_pinp::allow( 'ar_connect_soapClient' );

	class ar_connect_soap extends arBase {
	
		public static function client( $wsdl, $options = array() ) {
			return new ar_connect_soapClient( $wsdl, $options );
		}

		public static function server( $wsdl, $options = array() ) {
			return new ar_connect_soapServer( $wsdl, $options );
		}
	
		public static function header( $namespace, $name, $data = null, $mustUnderstand = false, $actor = '' ) {
			return new ar_connect_soapHeader( $namespace, $name, $data, $mustUnderstand, $actor );
		}

		public static function param( $data, $name ) {
			return new ar_connect_soapParam( $data, $name );
		}
		
		public static function variable( $data, $encoding, $type_name = '', $type_namespace = '', $node_name = '', $node_namespace = '') {
			return new ar_connect_soapVar( $data, $encoding, $type_name, $type_namespace, $node_name, $node_namespace );
		}
		
	}

	
	class ar_connect_soapClient extends arWrapper {
		
		function __construct( $wsdl, $options = array() ) {
			$soapClient = new SoapClient( $wsdl, $options );
			parent::__construct( $soapClient );
		}
	
	}

	class ar_connect_soapServer extends arWrapper {
		
		function __construct( $wsdl, $options = array() ) {
			$soapServer = new SoapServer( $wsdl, $options );
			parent::__construct( $soapServer );
		}
		
	}
	
	class ar_connect_soapHeader extends arWrapper {
		
		function __construct( $namespace, $name, $data = null, $mustUnderstand = false, $actor = '' ) {
			$soapHeader = new SoapHeader( $namespace, $name, $data, $mustUnderstand, $actor );
			parent::__construct( $soapHeader );
		}
	
	}
	
	class ar_connect_soapParam extends arWrapper {
		
		function __construct( $data, $name ) {
			$soapParam = new SoapParam( $data, $name );
			parent::__construct( $soapParam );
		}
	
	}
	
	class ar_connect_soapVar extends arWrapper {
		
		function __construct( $data, $encoding, $type_name = '', $type_namespace = '', $node_name = '', $node_namespace = '' ) {
			$soapVar = new SoapVar( $data, $encoding, $type_name, $type_namespace, $node_name, $node_namespace ); 
			parent::__construct( $soapVar );
		}
		
	}
	
?>