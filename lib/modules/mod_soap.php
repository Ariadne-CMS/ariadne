<?php
    /******************************************************************
     mod_soap.php                                          Muze Ariadne
     ------------------------------------------------------------------
     Author: Muze (info@muze.nl)
     Date: 25 november 2004

     Copyright 2004 Muze

     This file is part of Ariadne.

     Ariadne is free software; you can redistribute it and/or modify
     it under the terms of the GNU General Public License as published 
     by the Free Software Foundation; either version 2 of the License, 
     or (at your option) any later version.
 
     Ariadne is distributed in the hope that it will be useful,
     but WITHOUT ANY WARRANTY; without even the implied warranty of
     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     GNU General Public License for more details.

     You should have received a copy of the GNU General Public License
     along with Ariadne; if not, write to the Free Software 
     Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  
     02111-1307  USA

    -------------------------------------------------------------------

     Description:

		PINP wrapper to the pear SOAP library.

    ******************************************************************/

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