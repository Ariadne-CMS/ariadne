<?php
    /******************************************************************
     mod_xmlrpc.php                                        Muze Ariadne
     ------------------------------------------------------------------
     Author: Wouter Commandeur (Muze) (woutet@muze.nl)
     Date: 05 februari 2003

     Copyright 2003 Muze

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

	Wrapper module to the Pear XMLRPC library.

    ******************************************************************/

	include_once("XML/RPC.php");

	class XMLRPC {

		/* parse the given array and return a valid xmlrpc encoded array */

		function array_encode( $arguments=array(), $in_struct = 0 ) {

			$result = array();

			if( is_array( $arguments ) && (sizeof($arguments) > 0) ) {
				while( list($key,$value) = each( $arguments ) ) {
					if( is_int($key) ) {
						switch (gettype($value)) {
							case ("array"):
								$nest_array = XMLRPC::array_encode($value);
								array_push($result, new XML_RPC_Value($nest_array,"array"));
								break;
							case ("integer"):
								array_push($result, new XML_RPC_Value($value,"int"));
								break;
							case ("string"):
								array_push($result, new XML_RPC_Value($value,"string"));
								break;
							default:
								array_push($result, new XML_RPC_Value($value));
								break;
						}
					} else {
						$pieces = explode(":",$key);
						if( is_array($pieces) && ( sizeof($pieces) == 1) ) { // name
							switch( gettype($value) ) {
							case ("array"):
								$nest_array = XMLRPC::array_encode($value);
								array_push($result, new XML_RPC_Value($nest_array,"array"));
								break;
							case ("integer"):
								array_push($result, new XML_RPC_Value($value,"int"));
								break;
							case ("string"):
								array_push($result, new XML_RPC_Value($value,"string"));
								break;
							default:
								array_push($result, new XML_RPC_Value($value));
								break;
							}
						} elseif( is_array($pieces) ) { // type:name
							$type = $pieces[0];
							$name = $pieces[1];
							switch( $type ) {
								case ("struct"):
									$nest = XMLRPC::array_encode($value,1);
									if( $name == "" || $in_struct == 0 ) {
										array_push($result, new XML_RPC_Value($nest,$type));
									} else {
										$result[$name] = new XML_RPC_Value($nest,$type);
									}
									break;
								case ("array"):
									$nest = XMLRPC::array_encode($value);
									if( $name == "" || $in_struct == 0 ) {
										array_push($result, new XML_RPC_Value($nest,$type));
									} else {
										$result[$name] = new XML_RPC_Value($nest,$type);
									}
									break;
								case ("datetime"):
									if( is_int($value) ) {
										$datetime = XML_RPC_iso8601_encode($value);
									} else {
										$datetime = $value;
									}
									if( $name == "" || $in_struct == 0 ) {
										array_push($result, new XML_RPC_Value($datetime,"dateTime.iso8601"));
									} else {
										$result[$name] = new XML_RPC_Value($datetime,$type);
									}
									break;
								case ("i4"):
								case ("int"):
								case ("double"):
								case ("base64"):
								case ("string"):
								case ("boolean"):
								default:
									if( $name == "" || $in_struct == 0) {
										array_push($result, new XML_RPC_Value($value, $type));
									} else {
										$result[$name] = new XML_RPC_Value($value, $type);
									}
									break;
								break;
							}
						}
					}
				}
			}
			return $result;
		}

		function call( $url="",$function="",$arguments=array() ) {
		$arguments = XMLRPC::array_encode($arguments);

			$myResult = XMLRPC::call_raw($url,$function,$arguments);

			if( !$myResult ) {
				$result = "XMLRPC::Error ErrNo: ".$myClient->errno." ErrStr: ".$myClient->errstr;
			} else {
				if( $myResult->faultCode() ) {
					$result = "XMLRPC::ResultError Code: ".$myResult->faultCode()." Reason: ".$myResult->faultString();
				} else {
					// We have a valid response
					$result = array( XML_RPC_Decode( $myResult->Value() ) );
				}
			}
			return $result;
		}

		function call_raw( $url="", $function="", $arguments=array() ) {

			// parse the given url find: server, path, port
			// http://host:port/path/

			preg_match("/^([htps]*:\/\/)?([^\/:]+)(:[^\/]+)?(.*)/i", $url, $matches);

			$myHost = $matches[2];
			$myPort = substr($matches[3],1);
			if( !$matches[3] ) {
			  $myPort = 80;
			}
			$myPath = $matches[4];

			$myFunction = $function;

			$myArguments = $arguments;

			$myClient = new XML_RPC_Client($myPath, $myHost, $myPort);

			$myMessage = new XML_RPC_Message($myFunction, $myArguments);

			$myResult = $myClient->send($myMessage);

			return $myResult;
		}
	}

class pinp_XMLRPC extends XMLRPC {

	function _call( $url="", $function="", $arguments=array() ) {
		return $this->call( $url, $function, $arguments );
	}
}
