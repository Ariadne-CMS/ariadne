<?php

	require_once("SOAP/Client.php");

	class pinp_SOAP {

		function _Client($endpoint, $wsdl = false , $portName = false, $proxy_params=array() ) {
			return new pinp_SOAP_Client( $endpoint, $wsdl, $portName, $proxy_params);
		}
		
		function _Value($name, $type, $value, $namespaces=false) {
			if( $namespaces===false ) {
				return new SOAP_Value( $name, $type, $value);
			} else {
				return new SOAP_Value( $name, $type, $value, $namespaces );
			}
		}

		function _Header($name, $type, $value=NULL, $mustunderstand=0, $actor = 'http://schemas.xmlsoap.org/soap/actor/next') {
			return new SOAP_Header( $name, $type, $value, $mustunderstand, $actor );
		}

	}
	
	class pinp_SOAP_Client extends SOAP_Client {
	
		function pinp_SOAP_Client($endpoint, $wsdl = false, $portName = false, $proxy_params=array()) {
			parent::SOAP_Client( $endpoint, $wsdl, $portName, $proxy_params );
        }
                     		
		function _call( $function, $arguments=false, $namespace=false, $soapAction=false ) {
			return $this->call( $function, $arguments, $namespace, $soapAction );
		}

		function _addHeader($soap_value) {
			return $this->addHeader($soap_value);
		}

		function _isError($value) {
			return $this->isError($value);
		}

		function _isWarning($value) {
			return $this->isWarning($value);
		}

		function _errorMessage($error) {
			if (is_object($error) && get_class($error) == "soap_fault") {
				$result = $error->message;
			} else {
				$result = false;
			}
			return $result;
		}

	}

	
?>