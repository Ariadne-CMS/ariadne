<?php

/*

NuSOAP - Web Services Toolkit for PHP

Copyright (c) 2002 NuSphere Corporation

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

If you have any questions or comments, please email:

Dietrich Ayala
dietrich@ganx4.com
http://dietrich.ganx4.com/nusoap

NuSphere Corporation
http://www.nusphere.com

 */

/* load classes

// necessary classes
require_once('class.soapclient.php');
require_once('class.soap_val.php');
require_once('class.soap_parser.php');
require_once('class.soap_fault.php');

// transport classes
require_once('class.soap_transport_http.php');

// optional add-on classes
require_once('class.xmlschema.php');
require_once('class.wsdl.php');

// server class
require_once('class.soap_server.php');*/


// make errors handle properly in windows (thx, thong@xmethods.com)
error_reporting(2039);

/**
 *  set schema version
 *
 * @var      XMLSchemaVersion
 * @access   public
 */
$XMLSchemaVersion = 'http://www.w3.org/2001/XMLSchema';

/**
 *  load namespace uris into an array of uri => prefix
 *
 * @var      namespaces
 * @access   public
 */
$namespaces = array(
	'SOAP-ENV' => 'http://schemas.xmlsoap.org/soap/envelope/',
	'xsd' => $XMLSchemaVersion,
	'xsi' => $XMLSchemaVersion.'-instance',
	'SOAP-ENC' => 'http://schemas.xmlsoap.org/soap/encoding/',
	'si' => 'http://soapinterop.org/xsd');

/**
 *
 * nusoap_base
 *
 * @author   Dietrich Ayala <dietricha@ganx4.com>
 * @version  v 0.6
 * @access   public
 */

class nusoap_base {

	var $title = 'NuSOAP';
	var $version = '0.6';
	var $error_str = false;
	// toggles automatic encoding of special characters
	var $charencoding = true;

	/**
	 *  set default encoding
	 *
	 * @var      soap_defencoding
	 * @access   public
	 */
	var $soap_defencoding = 'UTF-8';

	/**
	 *  load namespace uris into an array of uri => prefix
	 *
	 * @var      namespaces
	 * @access   public
	 */
	var $namespaces = array(
		'SOAP-ENV' => 'http://schemas.xmlsoap.org/soap/envelope/',
		'xsd' => 'http://www.w3.org/2001/XMLSchema',
		'xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
		'SOAP-ENC' => 'http://schemas.xmlsoap.org/soap/encoding/',
		'si' => 'http://soapinterop.org/xsd');
	/**
	 * load types into typemap array
	 * is this legacy yet?
	 * @var      typemap
	 * @access   public
	 */
	var $typemap = array(
		'http://www.w3.org/2001/XMLSchema' => array(
			'string'=>'string','boolean'=>'boolean','float'=>'double','double'=>'double','decimal'=>'double',
			'duration'=>'','dateTime'=>'string','time'=>'string','date'=>'string','gYearMonth'=>'',
			'gYear'=>'','gMonthDay'=>'','gDay'=>'','gMonth'=>'','hexBinary'=>'string','base64Binary'=>'string',
			// derived datatypes
			'normalizedString'=>'string','token'=>'string','language'=>'','NMTOKEN'=>'','NMTOKENS'=>'','Name'=>'','NCName'=>'','ID'=>'',
			'IDREF'=>'','IDREFS'=>'','ENTITY'=>'','ENTITIES'=>'','integer'=>'integer','nonPositiveInteger'=>'integer',
			'negativeInteger'=>'integer','long'=>'','int'=>'integer','short'=>'','byte'=>'','nonNegativeInteger'=>'integer',
			'unsignedLong'=>'','unsignedInt'=>'','unsignedShort'=>'','unsignedByte'=>'','positiveInteger'=>''),
		'http://www.w3.org/1999/XMLSchema' => array(
			'i4'=>'','int'=>'integer','boolean'=>'boolean','string'=>'string','double'=>'double',
			'float'=>'double','dateTime'=>'string',
			'timeInstant'=>'string','base64Binary'=>'string','base64'=>'string','ur-type'=>'array'),
		'http://soapinterop.org/xsd' => array('SOAPStruct'=>'struct'),
		'http://schemas.xmlsoap.org/soap/encoding/' => array('base64'=>'string','array'=>'array','Array'=>'array')
	);

	/**
	 *  entities to convert
	 *
	 * @var      xmlEntities
	 * @access   public
	 */
	var $xmlEntities = array('quot' => '"','amp' => '&',
		'lt' => '<','gt' => '>','apos' => "'");

	/**
	 * constructor: loads schema version
	 */
	function __construct(){
		global $XMLSchemaVersion;
		$this->XMLSchemaVersion = $XMLSchemaVersion;
	}

	/**
	 * adds debug data to the class level debug string
	 *
	 * @param    string $string debug data
	 * @access   private
	 */
	function debug($string){
		$this->debug_str .= get_class($this).": $string\n";
	}

	/**
	 * returns error string if present
	 *
	 * @return   boolean $string error string
	 * @access   public
	 */
	function getError(){
		if($this->error_str != ""){
			return $this->error_str;
		}
		return false;
	}

	/**
	 * sets error string
	 *
	 * @return   boolean $string error string
	 * @access   private
	 */
	function setError($str){
		$this->error_str = $str;
	}

	/**
	 * serializes PHP values in accordance w/ section 5
	 * @return        string
	 * @access        public
	 */
	function serialize_val($val,$name=false,$type=false,$name_ns=false,$type_ns=false,$attributes=false){
		//print "in serialize_val: $val, $name, $type, $name_ns, $type_ns<br>";
		// if no name, use item
		$name = (!$name|| is_numeric($name)) ? 'noname' : $name;
		// if name has ns, add ns prefix to name
		if($name_ns){
			$prefix = 'nu'.rand(1000,9999);
			$name = $prefix.':'.$name;
			$xmlns .= " xmlns:$prefix=\"$name_ns\"";
		}
		// if type is prefixed, create type prefix
		if($type_ns == $this->namespaces['xsd'] || $type_ns == ''){
			// need to fix this. shouldn't default to if no ns specified
			// w/o checking against typemap
			$type_prefix = 'xsd';
		} elseif($type_ns){
			$type_prefix = 'ns'.rand(1000,9999);
			$xmlns .= " xmlns:$type_prefix=\"$type_ns\"";
		}
		// serialize attributes if present
		if($attributes){
			foreach($attributes as $k => $v){
				$atts .= " $k=\"$v\"";
			}
		}
		// detect type and serialize
		switch(true) {
			case is_null($val):
				$xml .= "<$name$xmlns xsi:type=\"xsd:nil\"/>\n";
				break;
			case (is_bool($val) || $type == 'boolean'):
				if(!$val){
					$val = 0;
				}
				$xml .= "<$name$xmlns xsi:type=\"".$type_prefix.":boolean\"$atts>$val</$name>\n";
				break;
			case (is_int($val) || is_long($val) || $type == 'int'):
				$xml .= "<$name$xmlns xsi:type=\"".$type_prefix.":int\"$atts>$val</$name>\n";
				break;
			case (is_float($val)|| is_double($val) || $type == 'float'):
				$xml .= "<$name$xmlns xsi:type=\"".$type_prefix.":float\"$atts>$val</$name>\n";
				break;
			case (is_string($val) || $type == 'string'):
				if($this->charencoding){
					$val = htmlspecialchars($val);
				}
				$xml .= "<$name$xmlns xsi:type=\"".$type_prefix.":string\"$atts>$val</$name>\n";
				break;
			case is_object($val):
				break;
				break;
			case (is_array($val) || $type):
				// detect if struct or array
				if(preg_match("/^[0-9]+$/",key($val)) || preg_match('/^ArrayOf/',$type)){
					foreach($val as $v){
						$tt = gettype($v);
						$array_types[$tt] = 1;
						$xml .= $this->serialize_val($v,'item');
						if(is_array($v) && is_numeric(key($v))){
							$i += sizeof($v);
						} else {
							$i += 1;
							unset($array_types['array']);
						}
					}
					if(count($array_types) > 1){
						$array_typename = "xsd:ur-type";
					} else {
						$array_typename = "xsd:".$tt;
					}
					if($array_types['array']){
						$array_type = $i.",".$i;
					} else {
						$array_type = $i;
					}
					$xml = "<$name xsi:type=\"SOAP-ENC:Array\" SOAP-ENC:arrayType=\"".$array_typename."[$array_type]\"$atts>\n".$xml."</$name>\n";
				} else {
					// got a struct
					if($type && $type_prefix){
						$type_str = " xsi:type=\"$type_prefix:$type\"";
					}
					$xml .= "<$name$xmlns$type_str$atts>\n";
					foreach($val as $k => $v){
						$xml .= $this->serialize_val($v,$k);
					}
					$xml .= "</$name>\n";
				}
				break;
			default:
				$xml .= "not detected, got ".gettype($val)." for $val\n";
				break;
		}
		return $xml;
	}

	/**
	 * serialize message
	 *
	 * @param string body
	 * @param string headers
	 * @param array namespaces
	 * @return string message
	 * @access public
	 */
	function serializeEnvelope($body,$headers=false,$namespaces=array()){
		// serialize namespaces
		foreach(array_merge($this->namespaces,$namespaces) as $k => $v){
			$ns_string .= "\n  xmlns:$k=\"$v\"";
		}
		// serialize headers
		if($headers){
			$headers = "<SOAP-ENV:Header>\n".$headers."</SOAP-ENV:Header>\n";
		}
		// serialize envelope
		return
			"<?xml version=\"1.0\"?".">\n".
			"<SOAP-ENV:Envelope SOAP-ENV:encodingStyle=\"http://schemas.xmlsoap.org/soap/encoding/\"$ns_string>\n".
			$headers.
			"<SOAP-ENV:Body>\n".
			$body.
			"</SOAP-ENV:Body>\n".
			"</SOAP-ENV:Envelope>\n";
	}

	function formatDump($str){
		$str = htmlspecialchars($str);
		return nl2br($str);
	}
}

// XML Schema Datatype Helper Functions

//xsd:dateTime helpers

/**
 * convert unix timestamp to ISO 8601 compliant date string
 *
 * @param    string $timestamp Unix time stamp
 * @access   public
 */
function timestamp_to_iso8601($timestamp,$utc=true){
	$datestr = date("Y-m-d\TH:i:sO",$timestamp);
	if($utc){
		$pregStr =
			"/([0-9]{4})-".        // centuries & years CCYY-
			"([0-9]{2})-".        // months MM-
			"([0-9]{2})".        // days DD
			"T".                        // separator T
			"([0-9]{2}):".        // hours hh:
			"([0-9]{2}):".        // minutes mm:
			"([0-9]{2})(\.[0-9]*)?". // seconds ss.ss...
			"(Z|[+\-][0-9]{2}:?[0-9]{2})?/"; // Z to indicate UTC, -/+HH:MM:SS.SS... for local tz's

		if(preg_match($pregStr,$datestr,$regs)){
			return sprintf("%04d-%02d-%02dT%02d:%02d:%02dZ",$regs[1],$regs[2],$regs[3],$regs[4],$regs[5],$regs[6]);
		}
		return false;
	} else {
		return $datestr;
	}
}

/**
 * convert ISO 8601 compliant date string to unix timestamp
 *
 * @param    string $datestr ISO 8601 compliant date string
 * @access   public
 */
function iso8601_to_timestamp($datestr){
	$pregStr =
		"/([0-9]{4})-".        // centuries & years CCYY-
		"([0-9]{2})-".        // months MM-
		"([0-9]{2})".        // days DD
		"T".                        // separator T
		"([0-9]{2}):".        // hours hh:
		"([0-9]{2}):".        // minutes mm:
		"([0-9]{2})(\.[0-9]+)?". // seconds ss.ss...
		"(Z|[+\-][0-9]{2}:?[0-9]{2})?/"; // Z to indicate UTC, -/+HH:MM:SS.SS... for local tz's
	if(preg_match($pregStr,$datestr,$regs)){
		// not utc
		if($regs[8] != "Z"){
			$op = substr($regs[8],0,1);
			$h = substr($regs[8],1,2);
			$m = substr($regs[8],strlen($regs[8])-2,2);
			if($op == "-"){
				$regs[4] = $regs[4] + $h;
				$regs[5] = $regs[5] + $m;
			} elseif($op == "+"){
				$regs[4] = $regs[4] - $h;
				$regs[5] = $regs[5] - $m;
			}
		}
		return strtotime("$regs[1]-$regs[2]-$regs[3] $regs[4]:$regs[5]:$regs[6]Z");
	} else {
		return false;
	}
}


?>
<?php

/**
 * soap_fault class, allows for creation of faults
 * mainly used for returning faults from deployed functions
 * in a server instance.
 * @access public
 */
class soap_fault extends nusoap_base {

	var $faultcode;
	var $faultactor;
	var $faultstring;
	var $faultdetail;

	/**
	 * constructor
	 *
	 * @param string $faultcode
	 * @param string $faultactor (client | server)
	 * @param string $faultstring
	 * @param string $faultdetail
	 */
	function __construct($faultcode,$faultactor,$faultstring='',$faultdetail=''){
		$this->faultcode = $faultcode;
		$this->faultactor = $faultactor;
		$this->faultstring = $faultstring;
		$this->faultdetail = $faultdetail;
	}

	/**
	 * serialize a fault
	 *
	 * @access   public
	 */
	function serialize(){
		foreach($this->namespaces as $k => $v){
			$ns_string .= "\n  xmlns:$k=\"$v\"";
		}
		$return_msg =
			"<?xml version=\"1.0\"?".">\n".
			"<SOAP-ENV:Envelope SOAP-ENV:encodingStyle=\"http://schemas.xmlsoap.org/soap/encoding/\"$ns_string>\n".
			"<SOAP-ENV:Body>\n".
			"<SOAP-ENV:Fault>\n".
			"<faultcode>$this->faultcode</faultcode>\n".
			"<faultactor>$this->faultactor</faultactor>\n".
			"<faultstring>$this->faultstring</faultstring>\n".
			"<faultdetail>$this->faultdetail</faultdetail>\n".
			"</SOAP-ENV:Fault>\n".
			"</SOAP-ENV:Body>\n".
			"</SOAP-ENV:Envelope>\n";
		return $return_msg;
	}
}

?><?php
/*

NuSOAP - Web Services Toolkit for PHP

Copyright (c) 2002 NuSphere Corporation

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

 */

/**
 * parses an XML Schema, allows access to it's data, other utility methods
 * no validation... yet.
 * very experimental and limited. As is discussed on XML-DEV, I'm one of the people
 * that just doesn't have time to read the spec(s) thoroughly, and just have a couple of trusty
 * tutorials I refer to :)
 *
 * @author   Dietrich Ayala <dietricha@ganx4.com>
 * @access   public
 */
class XMLSchema extends nusoap_base  {

	/**
	 * constructor
	 *
	 * @param    string $schema schema document URI
	 * @param    string $xml xml document URI
	 * @access   public
	 */
	function __construct($schema="",$xml=""){

		$this->debug('xmlschema class instantiated, inside constructor');
		// files
		$this->schema = $schema;
		$this->xml = $xml;

		// define internal arrays of bindings, ports, operations, messages, etc.
		$this->complexTypes = array();

		// parser vars
		$this->parser;
		$this->position;
		$this->depth;
		$this->depth_array = array();

		// parse schema file
		if($schema != ""){
			$this->debug("initial schema file: $schema");
			$this->parseFile($schema);
		}

		// parse xml file
		if($xml != ""){
			$this->debug("initial xml file: $xml");
			$this->parseFile($xml);
		}

	}

	/**
	 * parse an XML file
	 *
	 * @param string $xml, path/URL to XML file
	 * @param string $type, (schema | xml)
	 * @return boolean
	 * @access public
	 */
	function parseFile($xml,$type){
		// parse xml file
		if($xml != ""){
			$this->debug("parsing $xml");
			$xmlStr = @join("",@file($xml));
			if($xmlStr == ""){
				$this->setError("No file at the specified URL: $xml.");
				return false;
			} else {
				$this->parseString($xmlStr,$type);
				return true;
			}
		}
		return false;
	}

	/**
	 * parse an XML string
	 *
	 * @param    string $xml path or URL
	 * @param string $type, (schema|xml)
	 * @access   private
	 */
	function parseString($xml,$type){
		// parse xml string
		if($xml != ""){

			// Create an XML parser.
			$this->parser = xml_parser_create();
			// Set the options for parsing the XML data.
			xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);

			// Set the object for the parser.
			xml_set_object($this->parser, $this);

			// Set the element handlers for the parser.
			if($type == "schema"){
				xml_set_element_handler($this->parser, "schemaStartElement","schemaEndElement");
				xml_set_character_data_handler($this->parser,"schemaCharacterData");
			} elseif($type == "xml"){
				xml_set_element_handler($this->parser, "xmlStartElement","xmlEndElement");
				xml_set_character_data_handler($this->parser,"xmlCharacterData");
			}

			// Parse the XML file.
			if(!xml_parse($this->parser,$xml,true)){
				// Display an error message.
				$errstr = sprintf("XML error on line %d: %s",
					xml_get_current_line_number($this->parser),
					xml_error_string(xml_get_error_code($this->parser))
				);
				$this->debug("XML parse error: $errstr");
				$this->setError("Parser error: $errstr");
			}
			xml_parser_free($this->parser);
		} else{
			$this->debug("no xml passed to parseString()!!");
			$this->setError("no xml passed to parseString()!!");
		}
	}

	/**
	 * start-element handler
	 *
	 * @param    string $parser XML parser object
	 * @param    string $name element name
	 * @param    string $attrs associative array of attributes
	 * @access   private
	 */
	function schemaStartElement($parser, $name, $attrs) {

		// position in the total number of elements, starting from 0
		$pos = $this->position++;
		$depth = $this->depth++;
		// set self as current value for this depth
		$this->depth_array[$depth] = $pos;

		// loop through atts, logging ns declarations
		foreach($attrs as $key => $value){
			// if ns declarations, add to class level array of valid namespaces
			if(preg_match("/^xmlns/",$key)){
				if($ns_prefix = substr(strrchr($key,":"),1)){
					$this->namespaces[$ns_prefix] = $value;
				} else {
					$this->namespaces['ns'.(count($this->namespaces)+1)] = $value;
				}
				if($value == 'http://www.w3.org/2001/XMLSchema'){
					$this->XMLSchemaVersion = $value;
					$this->namespaces['xsi'] = $value.'-instance';
				} elseif($value == 'http://www.w3.org/1999/XMLSchema'){
					$this->XMLSchemaVersion = $value;
					$this->namespaces['xsi'] = $value.'-instance';
				}
			}
		}

		// get element prefix
		if(preg_match("/:/",$name)){
			// get ns prefix
			$prefix = substr($name,0,strpos($name,":"));
			// get unqualified name
			$name = substr(strstr($name,":"),1);
		}
		//$this->debug("name: $name, prefix: $prefix");

		// find status, register data
		switch($name){
			case "all":
				$this->complexTypes[$this->currentComplexType]["compositor"] = "all";
				$this->complexTypes[$this->currentComplexType]["phpType"] = "struct";
				break;
			case "attribute":
				if($attrs["name"]){
					$this->attributes[$attrs["name"]] = $attrs;
					$aname = $attrs["name"];
				} elseif($attrs["ref"]){
					$aname = $this->expandQName($attrs["ref"]);
				}

				if($this->currentComplexType){
					$this->complexTypes[$this->currentComplexType]["attrs"][$aname] = $attrs;
				} elseif($this->currentElement){
					$this->elements[$this->currentElement]['attrs'][$aname] = $attrs;
				}

				if($aname == 'http://schemas.xmlsoap.org/soap/encoding/:arrayType'){
					foreach($attrs as $k => $v){
						if(strstr($k,':') == ':arrayType'){
							if(strpos($v,'[,]')){
								$this->complexTypes[$this->currentComplexType]["multidimensional"] = true;
							}
							$v = substr($v,0,strpos($v,'[')); // clip the []
							if(strpos($v,':')){
								$v = $this->expandQName($v);
							} else {
								$v = $this->XMLSchemaVersion.':'.$v;
							}
							$this->complexTypes[$this->currentComplexType]["arrayType"] = $v;
							break;
						}
					}
				}
				break;
			case "complexContent":

				break;
			case 'complexType':
				if($attrs['name']){
					$this->currentElement = false;
					$this->currentComplexType = $attrs['name'];
					$this->complexTypes[$this->currentComplexType] = $attrs;
					$this->complexTypes[$this->currentComplexType]['typeClass'] = 'complexType';
					if(preg_match('/:Array$/',$attrs['base'])){
						$this->complexTypes[$this->currentComplexType]['phpType'] = 'array';
					} else {
						$this->complexTypes[$this->currentComplexType]['phpType'] = 'struct';
					}
					$this->xdebug("processing complexType $attrs[name]");
				}
				break;
			case 'element':
				if(isset($attrs['type'])){
					$this->xdebug("processing element ".$attrs['name']);
					$this->currentElement = $attrs['name'];
					$this->elements[ $attrs['name'] ] = $attrs;
					$this->elements[ $attrs['name'] ]['typeClass'] = 'element';
					$ename = $attrs['name'];
				} elseif(isset($attrs['ref'])){
					$ename = $attrs['ref'];
				} else {
					$this->xdebug("adding complexType $attrs[name]");
					$this->currentComplexType = $attrs['name'];
					$this->complexTypes[ $attrs['name'] ] = $attrs;
					$this->complexTypes[ $attrs['name'] ]['element'] = 1;
					$this->complexTypes[$this->currentComplexType]['phpType'] = 'struct';
				}
				if($ename && $this->currentComplexType){
					$this->complexTypes[$this->currentComplexType]['elements'][$ename] = $attrs;
				}
				break;
			case 'restriction':
				$this->xdebug("in restriction for ct: $this->currentComplexType and ce: $this->currentElement");
				if($this->currentElement){
					$this->elements[$this->currentElement]['type'] = $attrs['base'];
				} elseif($this->currentComplexType){
					$this->complexTypes[$this->currentComplexType]['restrictionBase'] = $attrs['base'];
					if(strstr($attrs['base'],':') == ':Array'){
						$this->complexTypes[$this->currentComplexType]['phpType'] = "array";
					}
				}
				break;
			case 'schema':
				$this->schema = $attrs;
				$this->schema['schemaVersion'] = $this->getNamespaceFromPrefix($prefix);
				break;
			case 'sequence':
				$this->complexTypes[$this->currentComplexType]['compositor'] = 'sequence';
				break;
			case 'simpleType':
				$this->currentElement = $attrs['name'];
				$this->elements[ $attrs['name'] ] = $attrs;
				$this->elements[ $attrs['name'] ]['typeClass'] = 'element';
				break;
		}
	}

	/**
	 * end-element handler
	 *
	 * @param    string $parser XML parser object
	 * @param    string $name element name
	 * @access   private
	 */
	function schemaEndElement($parser, $name) {
		// position of current element is equal to the last value left in depth_array for my depth
		$pos = $this->depth_array[$this->depth];
		// bring depth down a notch
		$this->depth--;
		// move on...
		if($name == 'complexType'){
			$this->currentComplexType = false;
			$this->currentElement = false;
		}
		if($name == 'element'){
			$this->currentElement = false;
		}
	}

	/**
	 * element content handler
	 *
	 * @param    string $parser XML parser object
	 * @param    string $data element content
	 * @access   private
	 */
	function schemaCharacterData($parser, $data){
		$pos = $this->depth_array[$this->depth];
		$this->message[$pos]["cdata"] .= $data;
	}

	/**
	 * serialize the schema
	 *
	 * @access   public
	 */
	function serializeSchema(){

		$schemaPrefix = $this->getPrefixFromNamespace($this->schema['schemaVersion']);
		// complex types
		foreach($this->complexTypes as $typeName => $attrs){
			$contentStr = "";
			// serialize child elements
			if(count($attrs["elements"]) > 0){
				foreach($attrs["elements"] as $element => $eParts){
					$contentStr .= "<element ref=\"$element\"/>\n";
				}
			}
			// serialize attributes
			if(count($attrs["attrs"]) > 0){
				foreach($attrs["attrs"] as $attr => $aParts){
					$contentStr .= "<attribute ref=\"$attr\"/>\n";
				}
			}

			// if restriction
			if($attrs["restrictionBase"]){
				$contentStr = "<$schemaPrefix:restriction base=\"".$attrs["restrictionBase"]."\">\n".
					$contentStr."</$schemaPrefix:restriction>\n";
			}
			if($attrs["complexContent"]){
				$contentStr = "<$schemaPrefix:complexContent>\n".
					$contentStr."</$schemaPrefix:complexContent>\n";
			} elseif($attrs["sequence"]){
				$contentStr = "<$schemaPrefix:sequence>\n".
					$contentStr."</$schemaPrefix:sequence>\n";
			} elseif($attrs["all"]){
				$contentStr = "<$schemaPrefix:all>\n".
					$contentStr."</$schemaPrefix:all>\n";
			}
			if($attrs['element']){
				if($contentStr != ""){
					$contentStr = "<$schemaPrefix:element name=\"$typeName\">\n"."<$schemaPrefix:complexType>\n".
						$contentStr."</$schemaPrefix:complexType>\n"."</$schemaPrefix:element>\n";
				} else {
					$contentStr = "<$schemaPrefix:element name=\"$typeName\">\n"."<$schemaPrefix:complexType/>\n".
						"</$schemaPrefix:element>\n";
				}
			} else {
				if($contentStr != ""){
					$contentStr = "<$schemaPrefix:complexType name=\"$typeName\">\n".
						$contentStr."</$schemaPrefix:complexType>\n";
				} else {
					$contentStr = "<$schemaPrefix:complexType name=\"$typeName\"/>\n";
				}
			}
			$xml .= $contentStr;
		}
		// elements
		if(count($this->elements) > 0){
			foreach($this->elements as $element => $eParts){
				$xml .= "<$schemaPrefix:element name=\"$element\" type=\"".$eParts['type']."\"/>\n";
			}
		}
		// attributes
		if(count($this->attributes) > 0){
			foreach($this->attributes as $attr => $aParts){
				$xml .= "<$schemaPrefix:attribute name=\"$attr\" type=\"".$aParts['type']."\"/>\n";
			}
		}
		$xml = "<$schemaPrefix:schema targetNamespace=\"".$this->schema["targetNamespace"]."\">\n".
			$xml."</$schemaPrefix:schema>\n";

		return $xml;
	}

	/**
	 * expands a qualified name
	 *
	 * @param    string $string qname
	 * @return        string expanded qname
	 * @access   private
	 */
	function expandQname($qname){
		// get element prefix
		if(preg_match("/:/",$qname)){
			// get unqualified name
			$name = substr(strstr($qname,":"),1);
			// get ns prefix
			$prefix = substr($qname,0,strpos($qname,":"));
			if(isset($this->namespaces[$prefix])){
				return $this->namespaces[$prefix].":".$name;
			} else {
				return false;
			}
		} else {
			return $qname;
		}
	}

	/**
	 * adds debug data to the clas level debug string
	 *
	 * @param    string $string debug data
	 * @access   private
	 */
	function xdebug($string){
		$this->debug(" xmlschema: $string");
	}

	/**
	 * get the PHP type of a user defined type in the schema
	 * PHP type is kind of a misnomer since it actually returns 'struct' for assoc. arrays
	 * returns false if no type exists, or not w/ the given namespace
	 * else returns a string that is either a native php type, or 'struct'
	 *
	 * @param string $type, name of defined type
	 * @param string $ns, namespace of type
	 * @return mixed
	 * @access public
	 */
	function getPHPType($type,$ns){
		global $typemap;
		if(isset($typemap[$ns][$type])){
			//print "found type '$type' and ns $ns in typemap<br>";
			return $typemap[$ns][$type];
		} elseif(isset($this->complexTypes[$type])){
			//print "getting type '$type' and ns $ns from complexTypes array<br>";
			return $this->complexTypes[$type]["phpType"];
		}
		return false;
	}

	/**
	 * returns the local part of a prefixed string
	 * returns the original string, if not prefixed
	 *
	 * @param string
	 * @return string
	 * @access public
	 */
	function getLocalPart($str){
		if($sstr = strrchr($str,':')){
			// get unqualified name
			return substr( $sstr, 1 );
		} else {
			return $str;
		}
	}

	/**
	 * returns the prefix part of a prefixed string
	 * returns false, if not prefixed
	 *
	 * @param string
	 * @return mixed
	 * @access public
	 */
	function getPrefix($str){
		if($pos = strrpos($str,':')){
			// get prefix
			return substr($str,0,$pos);
		}
		return false;
	}

	/**
	 * pass it a prefix, it returns a namespace
	 * or false if no prefixes registered for the given namespace
	 *
	 * @param string
	 * @return mixed
	 * @access public
	 */
	function getNamespaceFromPrefix($prefix){
		if(isset($this->namespaces[$prefix])){
			return $this->namespaces[$prefix];
		}
		//$this->setError("No namespace registered for prefix '$prefix'");
		return false;
	}

	/**
	 * returns the prefix for a given namespace
	 * returns false if no namespace registered with the given prefix
	 *
	 * @param string
	 * @return mixed
	 * @access public
	 */
	function getPrefixFromNamespace($ns){
		foreach($this->namespaces as $p => $n){
			if($ns == $n){
				$this->usedNamespaces[$p] = $ns;
				return $p;
			}
		}
		return false;
	}

	/**
	 * returns an array of information about a given type
	 * returns false if no type exists by the given name
	 *
	 *         typeDef = array(
	 *         'elements' => array(), // refs to elements array
	 *        'restrictionBase' => '',
	 *        'phpType' => '',
	 *        'order' => '(sequence|all)',
	 *        'attrs' => array() // refs to attributes array
	 *        )
	 *
	 * @param string
	 * @return mixed
	 * @access public
	 */
	function getTypeDef($type){
		if(isset($this->complexTypes[$type])){
			return $this->complexTypes[$type];
		} elseif(isset($this->elements[$type])){
			return $this->elements[$type];
		} elseif(isset($this->attributes[$type])){
			return $this->attributes[$type];
		}
		return false;
	}

	/**
	 * returns a sample serialization of a given type, or false if no type by the given name
	 *
	 * @param string $type, name of type
	 * @return mixed
	 * @access public
	 */
	function serializeTypeDef($type){
		//print "in sTD() for type $type<br>";
		if($typeDef = $this->getTypeDef($type)){
			$str .= "<$type";
			if(is_array($typeDef['attrs'])){
				foreach($attrs as $attName => $data){
					$str .= " $attName=\"{type = ".$data['type']."}\"";
				}
			}
			$str .= " xmlns=\"".$this->schema['targetNamespace']."\"";
			if(count($typeDef['elements']) > 0){
				$str .= ">\n";
				foreach($typeDef['elements'] as $element => $eData){
					$str .= $this->serializeTypeDef($element);
				}
				$str .= "</$type>\n";
			} elseif($typeDef['typeClass'] == 'element') {
				$str .= "></$type>\n";
			} else {
				$str .= "/>\n";
			}
			return $str;
		}
		return false;
	}

	/**
	 * returns HTML form elements that allow a user
	 * to enter values for creating an instance of the given type.
	 *
	 * @param string $name, name for type instance
	 * @param string $type, name of type
	 * @return string
	 * @access public
	 */
	function typeToForm($name,$type){
		// get typedef
		if($typeDef = $this->getTypeDef($type)){
			// if struct
			if($typeDef['phpType'] == 'struct'){
				$buffer .= '<table>';
				foreach($typeDef['elements'] as $child => $childDef){
					$buffer .= "
						<tr><td align='right'>$childDef[name] (type: ".$this->getLocalPart($childDef['type'])."):</td>
						<td><input type='text' name='parameters[".$name."][$childDef[name]]'></td></tr>";
				}
				$buffer .= '</table>';
				// if array
			} elseif($typeDef['phpType'] == 'array'){
				$buffer .= '<table>';
				for($i=0;$i < 3; $i++){
					$buffer .= "
						<tr><td align='right'>array item (type: $typeDef[arrayType]):</td>
						<td><input type='text' name='parameters[".$name."][]'></td></tr>";
				}
				$buffer .= '</table>';
				// if scalar
			} else {
				$buffer .= "<input type='text' name='parameters[$name]'>";
			}
		} else {
			$buffer .= "<input type='text' name='parameters[$name]'>";
		}
		return $buffer;
	}
}

?>
<?php

/*

NuSOAP - Web Services Toolkit for PHP

Copyright (c) 2002 NuSphere Corporation

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

 */

/**
 * for creating serializable abstractions of native PHP types
 * NOTE: this is only really used when WSDL is not available.
 *
 * @author   Dietrich Ayala <dietricha@ganx4.com>
 * @version  v 0.6
 * @access   public
 */
class soapval extends nusoap_base {
	/**
	 * constructor
	 *
	 * @param    string $name optional value name
	 * @param    string $type optional type name
	 * @param	mixed $value optional content of value
	 * @param	string $namespace optional namespace of value
	 * @param	string $type_namespace optional namespace of type
	 * @param	array $attributes associative array of attributes to add to element serialization
	 * @access   public
	 */
	function __construct($name="noname",$type=false,$value=-1,$element_ns=false,$type_ns=false,$attributes=false) {
		$this->name = $name;
		$this->value = $value;
		$this->type = $type;
		$this->element_ns = $element_ns;
		$this->type_ns = $type_ns;
		$this->attributes = $attributes;
	}

	/**
	 * return serialized value
	 *
	 * @return	string XML data
	 * @access   private
	 */
	function serialize() {
		return $this->serialize_val($this->value,$this->name,$this->type,$this->element_ns,$this->type_ns,$this->attributes);
	}

	/**
	 * decodes a soapval object into a PHP native type
	 *
	 * @param	object $soapval optional SOAPx4 soapval object, else uses self
	 * @return	mixed
	 * @access   public
	 */
	function decode(){
		return $this->value;
	}
}

?>
<?php
/*

NuSOAP - Web Services Toolkit for PHP

Copyright (c) 2002 NuSphere Corporation

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

 */

/**
 * transport class for sending/receiving data via HTTP and HTTPS
 * NOTE: PHP must be compiled with the CURL extension for HTTPS support
 * HTTPS support is experimental!
 *
 * @access public
 */
class soap_transport_http extends nusoap_base {

	var $username;
	var $password;
	var $url;
	/**
	 * constructor
	 */
	function __construct($url){
		$this->url = $url;
		$u = parse_url($url);
		foreach($u as $k => $v){
			$this->debug("$k = $v");
			$this->$k = $v;
		}
		if($u['query'] != ''){
			$this->path .= $u['query'];
		}
		if(!isset($u['port']) && $u['scheme'] == 'http'){
			$this->port = 80;
		}
	}

	/**
	 * if authenticating, set user credentials here
	 *
	 * @param    string $user
	 * @param    string $pass
	 * @access   public
	 */
	function setCredentials($user, $pass) {
		$this->user = $username;
		$this->pass = $pword;
	}

	/**
	 * set the soapaction value
	 *
	 * @param    string $soapaction
	 * @access   public
	 */
	function setSOAPAction($soapaction) {
		$this->soapaction = $soapaction;
	}

	/**
	 * set proxy info here
	 *
	 * @param    string $proxyhost
	 * @param    string $proxyport
	 * @access   public
	 */
	function setProxy($proxyhost, $proxyport) {
		$this->proxyhost = $proxyhost;
		$this->proxyport = $proxyport;
	}

	/**
	 * send the SOAP message via HTTP 1.0
	 *
	 * @param    string $msg message data
	 * @param    integer $timeout set timeout in seconds
	 * @return        string data
	 * @access   public
	 */
	function send($data, $timeout=0) {
		flush();
		$this->debug('entered send() with data of length: '.strlen($data));

		if($this->proxyhost && $this->proxyport){
			$host = $this->proxyhost;
			$port = $this->proxyport;
		} else {
			$host = $this->host;
			$port = $this->port;
		}
		if($timeout > 0){
			$fp = fsockopen($host, $port, $this->errno, $this->error_str, $timeout);
		} else {
			$fp = fsockopen($host, $port, $this->errno, $this->error_str);
		}
		//socket_set_blocking($fp,0);
		if (!$fp) {
			$this->debug("Couldn't open socket connection to server: $server!");
			$this->setError("Couldn't open socket connection to server: $server.");
			return false;
		}

		$credentials = '';
		if($this->user != '') {
			$credentials = 'Authorization: Basic '.base64_encode('$this->user:$this->pass').'\r\n';
		}

		if($this->proxyhost && $this->proxyport){
			$this-> outgoing_payload = "POST $this->url HTTP/1.0\r\n";
		} else {
			$this->outgoing_payload = "POST $this->path HTTP/1.0\r\n";
		}

		$this->outgoing_payload .=
			"User-Agent: $this->title v$this->version\r\n".
			"Host: ".$this->host."\r\n".
			$credentials.
			"Content-Type: text/xml\r\nContent-Length: ".strlen($data)."\r\n".
			"SOAPAction: \"$this->soapaction\""."\r\n\r\n".
			$data;

		// send
		if(!fputs($fp, $this->outgoing_payload, strlen($this->outgoing_payload))) {
			$this->setError("couldn't write message data to socket");
			$this->debug("Write error");
		}

		// get response
		$this->incoming_payload = "";
		while ($data = fread($fp, 32768)) {
			$this->incoming_payload .= $data;
		}

		// close filepointer
		fclose($fp);
		$data = $this->incoming_payload;
		//print "data: <xmp>$data</xmp>";
		// separate content from HTTP headers
		if(preg_match("/([^<]*?)\r?\n\r?\n(<.*>)/s",$data,$result)) {
			$this->debug("found proper separation of headers and document");
			$this->debug("getting rid of headers, stringlen: ".strlen($data));
			$clean_data = $result[2];
			$this->debug("cleaned data, stringlen: ".strlen($clean_data));
					 /*
					 if(preg_match("/^(.*)\r?\n\r?\n/",$data)) {
								$this->debug("found proper separation of headers and document");
								$this->debug("getting rid of headers, stringlen: ".strlen($data));
								$clean_data = preg_replace("/^[^<]*\r\n\r\n/","", $data);
								$this->debug("cleaned data, stringlen: ".strlen($clean_data));
					  */
		} else {
			$this->setError('no proper separation of headers and document.');
			return false;
		}
		if(strlen($clean_data) == 0){
			$this->debug("no data after headers!");
			$this->setError("no data present after HTTP headers.");
			return false;
		}

		return $clean_data;
	}


	/**
	 * send the SOAP message via HTTPS 1.0 using CURL
	 *
	 * @param    string $msg message data
	 * @param    integer $timeout set timeout in seconds
	 * @return        string data
	 * @access   public
	 */
	function sendHTTPS($data, $timeout=0) {
		flush();
		$this->debug('entered sendHTTPS() with data of length: '.strlen($data));
		// init CURL
		$ch = curl_init();

		// set proxy
		if($this->proxyhost && $this->proxyport){
			$host = $this->proxyhost;
			$port = $this->proxyport;
		} else {
			$host = $this->host;
			$port = $this->port;
		}
		// set url
		$hostURL = ($port != '') ? "https://$host:$port" : "https://$host";
		// add path
		$hostURL .= $this->path;

		curl_setopt($ch, CURLOPT_URL, $hostURL);
		// set other options
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// set timeout
		if($timeout != 0){
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		}

		$credentials = '';
		if($this->user != '') {
			$credentials = 'Authorization: Basic '.base64_encode('$this->user:$this->pass').'\r\n';
		}

		if($this->proxyhost && $this->proxyport){
			$this-> outgoing_payload = "POST $this->url HTTP/1.0\r\n";
		} else {
			$this->outgoing_payload = "POST $this->path HTTP/1.0\r\n";
		}

		$this->outgoing_payload .=
			"User-Agent: $this->title v$this->version\r\n".
			"Host: ".$this->host."\r\n".
			$credentials.
			"Content-Type: text/xml\r\nContent-Length: ".strlen($data)."\r\n".
			"SOAPAction: \"$this->soapaction\""."\r\n\r\n".
			$data;

		// set payload
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->outgoing_payload);

		// send and receive
		$this->incoming_payload = curl_exec($ch);
		$data = $this->incoming_payload;

		$err = "cURL ERROR: ".curl_errno($ch).": ".curl_error($ch)."<br>";

		if($err != ''){
			foreach(curl_getinfo($ch) as $k => $v){
				$err .= "$k: $v<br>";
			}
			$this->setError($err);
			curl_close($ch);
			return false;
		}

		curl_close($ch);

		// separate content from HTTP headers
		if(preg_match("/^(.*)\r?\n\r?\n/",$data)) {
			$this->debug("found proper separation of headers and document");
			$this->debug("getting rid of headers, stringlen: ".strlen($data));
			$clean_data = preg_replace("/^[^<]*\r\n\r\n/","", $data);
			$this->debug("cleaned data, stringlen: ".strlen($clean_data));
		} else {
			$this->setError('no proper separation of headers and document.');
			return false;
		}
		if(strlen($clean_data) == 0){
			$this->debug("no data after headers!");
			$this->setError("no data present after HTTP headers.");
			return false;
		}

		return $clean_data;
	}
}

?>
<?php
/*

NuSOAP - Web Services Toolkit for PHP

Copyright (c) 2002 NuSphere Corporation

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

 */

/**
 *
 * soap_server allows the user to create a SOAP server
 * that is capable of receiving messages and returning responses
 *
 * NOTE: WSDL functionality is experimental
 *
 * @author   Dietrich Ayala <dietricha@ganx4.com>
 * @version  v 0.6
 * @access   public
 */
class soap_server extends nusoap_base {

	// assoc array of operations => opData
	var $operations = array();
	var $responseHeaders = false;
	var $headers = "";
	var $request = "";
	var $charset_encoding = "UTF-8";
	var $fault = false;
	var $result = "successful";

	/**
	 * constructor
	 *
	 * @param string $wsdl path or URL to a WSDL file
	 * @access   public
	 */
	function __construct($wsdl=false){

		// turn on debugging?
		global $debug;
		if(isset($debug)){
			$this->debug_flag = true;
		}

		$this->wsdl = false;

		// wsdl
		if($wsdl){
			$this->wsdl = new wsdl($wsdl);
			if($err = $this->wsdl->getError()){
				die("WSDL ERROR: $err");
			}
		}
	}

	/**
	 * processes request and returns response
	 *
	 * @param    string $data usually is the value of $HTTP_RAW_POST_DATA
	 * @access   public
	 */
	function service($data){
		// print wsdl
		if(preg_match('/^wsdl/',$GLOBALS['QUERY_STRING'])){
			header("Content-Type: text/xml\r\n");
			print $this->wsdl->serialize();
			// print web interface
		} elseif($data == '' && $this->wsdl){
			print $this->webDescription();
		} else {
			// $response is the serialized response message
			$response = $this->parse_request($data);
			$this->debug("server sending...");
			$payload = $response;
			//$payload .= "<!--\n$this->debug_str\n-->";
			// print headers
			if($this->fault){
				$header[] = "Status: 500 Internal Server Error\r\n";
			} else {
				$header[] = "Status: 200 OK\r\n";
			}
			$header[] = "Server: $this->title Server v$this->version\r\n";
			$header[] = "Connection: Close\r\n";
			$header[] = "Content-Type: text/xml; charset=$this->charset_encoding\r\n";
			$header[] = "Content-Length: ".strlen($payload)."\r\n\r\n";
			reset($header);
			foreach($header as $hdr){
				header($hdr);
			}
			$this->response = join("\n",$header).$payload;
			print $payload;
		}
	}





	/**
	 * parses request and returns
	 *
	 * @param    string $data XML string
	 * @return        object SOAPx4 soapmsg object
	 * @access   private
	 */
	function get_request($data="") {
		$this->debug("entering parseRequest() on ".date("H:i Y-m-d"));
		// get headers
		if(function_exists("getallheaders")){
			$this->headers = getallheaders();
			foreach($this->headers as $k=>$v){
				$dump .= "$k: $v\r\n";
				$this->debug("$k: $v");
			}
			// get SOAPAction header
			if($this->headers['SOAPAction']){
				$this->SOAPAction = str_replace('"','',$this->headers['SOAPAction']);
			}
			// get the character encoding of the incoming request
			if(strpos($headers_array['Content-Type'],"=")){
				$enc = str_replace("\"","",substr(strstr($headers_array["Content-Type"],"="),1));
				if(preg_match("/^(ISO-8859-1|US-ASCII|UTF-8)$/i",$enc)){
					$this->xml_encoding = $enc;
				} else {
					$this->xml_encoding = 'us-ascii';
				}
			}
			$this->debug("got encoding: $this->xml_encoding");
		} elseif(is_array($_SERVER)){
			$this->headers['User-Agent'] = $_SERVER['HTTP_USER_AGENT'];
			$this->SOAPAction = $_SERVER['SOAPAction'];
		}
		$this->request = $dump."\r\n\r\n".$data;
		// parse response, get soap parser obj
		$parser = new soap_parser($data,$this->xml_encoding);
		// if fault occurred during message parsing
		if($err = $parser->getError()){
			// parser debug
			$this->debug("parser debug: \n".$parser->debug_str);
			$this->result = "fault: error in msg parsing or eval: $err";
			$this->fault("Server","error in msg parsing or eval:\n".$err);
			// return soapresp
			return $this->fault->serialize();
			// else successfully parsed request into soapval object
		} else {
			// get/set methodname
			$this->methodname = $parser->root_struct_name;
			$this->debug("method name: $this->methodname");

			// evaluate message, getting back parameters
			$this->debug("calling parser->get_response()");
			$request_data = $parser->get_response();
			$this->debug('Parsed response dump: $request_data');

			// parser debug
			$this->debug("parser debug: \n".$parser->debug_str);
			return $request_data;
		}
	}




	function send_returnvalue($method_response) {
		// if we got nothing back. this might be ok (echoVoid)
		if(isset($method_response) && $method_response != "" || is_bool($method_response)) {
			// if fault
			if(get_class($method_response) == 'soap_fault'){
				debug('soapserver::send_returnvalue got a fault object from method', 'loader');
				$this->fault = $method_response;
				$return_val =  $method_response->serialize();
				// if return val is soapval object
			} elseif(get_class($method_response) == 'soapval'){
				$this->debug('got a soapval object from method');
				$return_val = $method_response->serialize();
				// returned other
			} else {
				$this->debug("got a ".gettype($method_response)." from method");
				$this->debug("serializing return value");
				if($this->wsdl){
					if(sizeof($this->opData['output']['parts']) > 1){
						$opParams = $method_response;
					} else {
						$opParams = array($method_response);
					}
					$return_val = $this->wsdl->serializeRPCParameters($this->methodname,'output',$opParams);
				} else {
					$return_val = $this->serialize_val($method_response);
				}
			}
		}
		if (!$this->fault) {
			$this->debug("serializing response");
			$payload = "<".$this->methodname."Response>\n".$return_val."</".$this->methodname."Response>\n";
			$this->result = "successful";
			if($this->wsdl){
				//$this->debug("WSDL debug data:\n".$this->wsdl->debug_str);
			}
			// $response is the serialized response message
			$response =  $this->serializeEnvelope($payload,$this->responseHeaders);
		} else {
			$response = $return_val;
		}

		$this->debug("server sending...");
		$payload = $response;
		//$payload .= "<!--\n$this->debug_str\n-->";
		// print headers
		if($this->fault){
			$header[] = "Status: 500 Internal Server Error\r\n";
		} else {
			$header[] = "Status: 200 OK\r\n";
		}
		$header[] = "Server: $this->title Server v$this->version\r\n";
		$header[] = "Connection: Close\r\n";
		$header[] = "Content-Type: text/xml; charset=$this->charset_encoding\r\n";
		$header[] = "Content-Length: ".strlen($payload)."\r\n\r\n";
		reset($header);
		foreach($header as $hdr){
			header($hdr);
		}
		$this->response = join("\n",$header).$payload;
		debug("soapserver::sending ($payload)", "loader");
		print $payload;
	}


	/**
	 * parses request and posts response
	 *
	 * @param    string $data XML string
	 * @return        object SOAPx4 soapmsg object
	 * @access   private
	 */
	function parse_request($data="") {
		$this->debug("entering parseRequest() on ".date("H:i Y-m-d"));
		// get headers
		if(function_exists("getallheaders")){
			$this->headers = getallheaders();
			foreach($this->headers as $k=>$v){
				$dump .= "$k: $v\r\n";
				$this->debug("$k: $v");
			}
			// get SOAPAction header
			if($this->headers['SOAPAction']){
				$this->SOAPAction = str_replace('"','',$this->headers['SOAPAction']);
			}
			// get the character encoding of the incoming request
			if(strpos($headers_array['Content-Type'],"=")){
				$enc = str_replace("\"","",substr(strstr($headers_array["Content-Type"],"="),1));
				if(preg_match("/^(ISO-8859-1|US-ASCII|UTF-8)$/i",$enc)){
					$this->xml_encoding = $enc;
				} else {
					$this->xml_encoding = 'us-ascii';
				}
			}
			$this->debug("got encoding: $this->xml_encoding");
		} elseif(is_array($_SERVER)){
			$this->headers['User-Agent'] = $_SERVER['HTTP_USER_AGENT'];
			$this->SOAPAction = $_SERVER['SOAPAction'];
		}
		$this->request = $dump."\r\n\r\n".$data;
		// parse response, get soap parser obj
		$parser = new soap_parser($data,$this->xml_encoding);
		// if fault occurred during message parsing
		if($err = $parser->getError()){
			// parser debug
			$this->debug("parser debug: \n".$parser->debug_str);
			$this->result = "fault: error in msg parsing or eval: $err";
			$this->fault("Server","error in msg parsing or eval:\n".$err);
			// return soapresp
			return $this->fault->serialize();
			// else successfully parsed request into soapval object
		} else {
			// get/set methodname
			$this->methodname = $parser->root_struct_name;
			$this->debug("method name: $this->methodname");
			// does method exist?
			if(!function_exists($this->methodname)){
				// "method not found" fault here
				$this->debug("method '$this->methodname' not found!");
				$this->debug("parser debug: \n".$parser->debug_str);
				$this->result = "fault: method not found";
				$this->fault("Server","method '$this->methodname' not defined in service '$this->service'");
				return $this->fault->serialize();
			}
			if($this->wsdl){
				if(!$this->opData = $this->wsdl->getOperationData($this->methodname)){
					$this->fault('Server',"Operation '$this->methodname' is not defined in the WSDL for this service");
					return $this->fault->serialize();
				}
			}
			$this->debug("method '$this->methodname' exists");
			// evaluate message, getting back parameters
			$this->debug("calling parser->get_response()");
			$request_data = $parser->get_response();
			$this->debug('Parsed response dump: $request_data');
			// parser debug
			$this->debug("parser debug: \n".$parser->debug_str);
			// verify that request parameters match the method's signature
			if($this->verify_method($this->methodname,$request_data)){
				// if there are parameters to pass
				if($request_data){
					$this->debug("calling '$this->methodname' with params");
					if (! function_exists('call_user_func_array')) {
						$this->debug("calling method using eval()");
						$funcCall = $this->methodname."(";
						foreach($request_data as $param) {
							$funcCall .= "\"$param\",";
						}
						$funcCall = substr($funcCall, 0, -1).')';
						$this->debug("function call:<br>$funcCall");
						eval("\$method_response = $funcCall;");
					} else {
						$this->debug("calling method using call_user_func_array()");
						$method_response = call_user_func_array("$this->methodname",$request_data);
					}
				} else {
					// call method w/ no parameters
					$this->debug("calling $this->methodname w/ no params");
					//$method_response = call_user_func($this->methodname);
					$m = $this->methodname;
					$method_response = $m();
				}
				$this->debug("done calling method: $this->methodname, received $method_response of type".gettype($method_response));
				// if we got nothing back. this might be ok (echoVoid)
				if(isset($method_response) && $method_response != "" || is_bool($method_response)) {
					// if fault
					if(get_class($method_response) == 'soap_fault'){
						$this->debug('got a fault object from method');
						$this->fault = $method_response;
						return $method_response->serialize();
						// if return val is soapval object
					} elseif(get_class($method_response) == 'soapval'){
						$this->debug('got a soapval object from method');
						$return_val = $method_response->serialize();
						// returned other
					} else {
						$this->debug("got a ".gettype($method_response)." from method");
						$this->debug("serializing return value");
						if($this->wsdl){
							if(sizeof($this->opData['output']['parts']) > 1){
								$opParams = $method_response;
							} else {
								$opParams = array($method_response);
							}
							$return_val = $this->wsdl->serializeRPCParameters($this->methodname,'output',$opParams);
						} else {
							$return_val = $this->serialize_val($method_response);
						}
					}
				}
				$this->debug("serializing response");
				$payload = "<".$this->methodname."Response>\n".$return_val."</".$this->methodname."Response>\n";
				$this->result = "successful";
				if($this->wsdl){
					//$this->debug("WSDL debug data:\n".$this->wsdl->debug_str);
				}
				return $this->serializeEnvelope($payload,$this->responseHeaders);
			} else {
				// debug
				$this->debug("ERROR: request not verified against method signature");
				$this->result = "fault: request failed validation against method signature";
				// return fault
				$this->fault("Server","Sorry, operation '$this->methodname' not defined in service.");
				return $this->fault->serialize();
			}
		}
	}

	/**
	 * takes the soapval object that was created by parsing the request
	 * and compares to the method's signature, if available.
	 *
	 * @param        object SOAPx4 soapval object
	 * @return        boolean
	 * @access   private
	 */
	function verify_method($operation,$request){
		if(isset($this->operations[$operation])){
			return true;
		}
		return false;
	}

	/**
	 * add a method to the dispatch map
	 *
	 * @param    string $methodname
	 * @param    string $in array of input values
	 * @param    string $out array of output values
	 * @access   public
	 */
	function add_to_map($methodname,$in,$out){
		$this->operations[$methodname] = array('name' => $methodname,'in' => $in,'out' => $out);
	}

	/**
	 * register a service with the server
	 *
	 * @param    string $methodname
	 * @param    string $in array of input values
	 * @param    string $out array of output values
	 * @param        string $namespace
	 * @param        string $soapaction
	 * @param        string $style (rpc|literal)
	 * @access   public
	 */
	function register($name,$in=false,$out=false,$namespace=false,$soapaction=false,$style=false){
		$this->operations[$name] = array(
			'name' => $name,
			'in' => $in,
			'out' => $out,
			'namespace' => $namespage,
			'soapaction' => $soapaction,
			'style' => $style);
		return true;
	}

	/**
	 * create a fault. this also acts as a flag to the server that a fault has occured.
	 *
	 * @param        string faultcode
	 * @param        string faultactor
	 * @param        string faultstring
	 * @param        string faultdetail
	 * @access   public
	 */
	function fault($faultcode,$faultactor,$faultstring='',$faultdetail=''){
		$this->fault = new soap_fault($faultcode,$faultactor,$faultstring,$faultdetail);
	}

	/**
	 * prints html description of services
	 *
	 * @access private
	 */
	function webDescription(){
		$b .= "
			<html><head><title>NuSOAP: ".$this->wsdl->serviceName."</title>
			<style type=\"text/css\">
			body    { font-family: arial; color: #000000; background-color: #ffffff; margin: 0px 0px 0px 0px; }
			p       { font-family: arial; color: #000000; margin-top: 0px; margin-bottom: 12px; }
			pre { background-color: silver; padding: 5px; font-family: Courier New; font-size: x-small; color: #000000;}
			ul      { margin-top: 10px; margin-left: 20px; }
			li      { list-style-type: none; margin-top: 10px; color: #000000; }
			.content{
				margin-left: 0px; padding-bottom: 2em; }
					.nav {
						padding-top: 10px; padding-bottom: 10px; padding-left: 15px; font-size: .70em;
						margin-top: 10px; margin-left: 0px; color: #000000;
						background-color: #ccccff; width: 20%; margin-left: 20px; margin-top: 20px; }
						.title {
							font-family: arial; font-size: 26px; color: #ffffff;
							background-color: #999999; width: 105%; margin-left: 0px;
							padding-top: 10px; padding-bottom: 10px; padding-left: 15px;}
							.hidden {
								position: absolute; visibility: hidden; z-index: 200; left: 250px; top: 100px;
								font-family: arial; overflow: hidden; width: 600;
								padding: 20px; font-size: 10px; background-color: #999999;
								layer-background-color:#FFFFFF; }
								a,a:active  { color: charcoal; font-weight: bold; }
								a:visited   { color: #666666; font-weight: bold; }
								a:hover     { color: cc3300; font-weight: bold; }
								</style>
								<script language=\"JavaScript\" type=\"text/javascript\">
<!--
	// POP-UP CAPTIONS...
	function lib_bwcheck(){ //Browsercheck (needed)
		this.ver=navigator.appVersion
			this.agent=navigator.userAgent
			this.dom=document.getElementById?1:0
			this.opera5=this.agent.indexOf(\"Opera 5\")>-1
			this.ie5=(this.ver.indexOf(\"MSIE 5\")>-1 && this.dom && !this.opera5)?1:0;
		this.ie6=(this.ver.indexOf(\"MSIE 6\")>-1 && this.dom && !this.opera5)?1:0;
		this.ie4=(document.all && !this.dom && !this.opera5)?1:0;
		this.ie=this.ie4||this.ie5||this.ie6
			this.mac=this.agent.indexOf(\"Mac\")>-1
			this.ns6=(this.dom && parseInt(this.ver) >= 5) ?1:0;
		this.ns4=(document.layers && !this.dom)?1:0;
		this.bw=(this.ie6 || this.ie5 || this.ie4 || this.ns4 || this.ns6 || this.opera5)
			return this
	}
	var bw = new lib_bwcheck()
		//Makes crossbrowser object.
		function makeObj(obj){
			this.evnt=bw.dom? document.getElementById(obj):bw.ie4?document.all[obj]:bw.ns4?document.layers[obj]:0;
			if(!this.evnt) return false
				this.css=bw.dom||bw.ie4?this.evnt.style:bw.ns4?this.evnt:0;
			this.wref=bw.dom||bw.ie4?this.evnt:bw.ns4?this.css.document:0;
			this.writeIt=b_writeIt;
			return this
	}
	// A unit of measure that will be added when setting the position of a layer.
	//var px = bw.ns4||window.opera?\"\":\"px\";
	function b_writeIt(text){
		if (bw.ns4){this.wref.write(text);this.wref.close()}
		else this.wref.innerHTML = text
	}
	//Shows the messages
	var oDesc;
	function popup(divid){
		if(oDesc = new makeObj(divid)){
			oDesc.css.visibility = \"visible\"
	}
	}
	function popout(){ // Hides message
		if(oDesc) oDesc.css.visibility = \"hidden\"
	}
	//-->
		  </script>
		  </head>
		  <body>
		  <div class='content'>
		  <br><br>
		  <div class='title'>".$this->wsdl->serviceName."</div>
		  <div class='nav'>
		  <p>View the <a href='".$_SERVER['PHP_SELF']."?wsdl'>WSDL</a> for the service.
		  Click on an operation name to view it's details.</p>
		  <ul>
		  ";
		  foreach($this->wsdl->getOperations() as $op => $data){
				$b .= "<li><a href='#' onclick=\"popup('$op')\">$op</a></li>";
				// create hidden div
				$b .= "<div id='$op' class='hidden'>
				<a href='#' onclick='popout()'><font color='#ffffff'>Close</font></a><br><br>";
				foreach($data as $donnie => $marie){
					 if($donnie == 'input' || $donnie == 'output'){
						  $b .= "<font color='white'>".ucfirst($donnie).":</font><br>";
						  foreach($marie as $captain => $tenille){
								if($captain == 'parts'){
									 $b .= "&nbsp;&nbsp;$captain:<br>";
									 foreach($tenille as $joanie => $chachi){
										  $b .= "&nbsp;&nbsp;&nbsp;&nbsp;$joanie: $chachi<br>";
									 }
								} else {
									 $b .= "&nbsp;&nbsp;$captain: $tenille<br>";
								}
						  }
					 } else {
						  $b .= "<font color='white'>".ucfirst($donnie).":</font> $marie<br>";
					 }
				}
				/*$b .= "<pre>".$this->formatDump(
					 $this->wsdl->serializeEnvelope(
						  $this->wsdl->serializeRPCParameters($op,array())))."</pre>";
				$b .= "</div>";*/
		  }
		  $b .= "
		  <ul>
		  </div>
		  </div>
		  </body></html>";
		  return $b;
	 }

	 /**
	 * sets up wsdl object
	 * this acts as a flag to enable internal WSDL generation
	 * NOTE: NOT FUNCTIONAL
	 *
	 * @param string $serviceName, name of the service
	 * @param string $namespace, tns namespace
	 */
	 function configureWSDL($serviceName,$namespace){
		  $this->wsdl = new wsdl;
		  $this->wsdl->serviceName = $serviceName;
		  $this->wsdl->namespaces['tns'] = $namespace;
		  $this->wsdl->namespaces['soap'] = "http://schemas.xmlsoap.org/wsdl/soap/";
		  $this->wsdl->namespaces['wsdl'] = "http://schemas.xmlsoap.org/wsdl/";
	 }
}

?>
<?php
/*

NuSOAP - Web Services Toolkit for PHP

Copyright (c) 2002 NuSphere Corporation

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

 */

/**
 * parses a WSDL file, allows access to it's data, other utility methods
 *
 * @author   Dietrich Ayala <dietricha@ganx4.com>
 * @access   public
 */
class wsdl extends XMLSchema {

	/**
	 * constructor
	 *
	 * @param    string $wsdl WSDL document URL
	 * @access   public
	 */
	function __construct($wsdl=""){
		$this->wsdl = $wsdl;
		// define internal arrays of bindings, ports, operations, messages, etc.
		//$this->namespaces = array();
		$this->complexTypes = array();
		$this->messages = array();
		$this->currentMessage;
		$this->currentOperation;
		$this->portTypes = array();
		$this->currentPortType;
		$this->bindings = array();
		$this->currentBinding;
		$this->ports = array();
		$this->currentPort;
		$this->opData = array();
		$this->status = "";
		$this->documentation = false;
		// array of wsdl docs to import
		$this->import = array();
		// parser vars
		$this->parser;
		$this->position;
		$this->depth;
		$this->depth_array = array();

		// parse wsdl file
		if($wsdl != ""){
			$this->debug("initial wsdl file: $wsdl");
			$this->parseWSDL($wsdl);
		}

		// imports
		if(sizeof($this->import) > 0){
			foreach($this->import as $ns => $url){
				$this->debug("importing wsdl from $url");
				$this->parseWSDL($url);
			}
		}

	}

	/**
	 * parses the wsdl document
	 *
	 * @param    string $wsdl path or URL
	 * @access   private
	 */
	function parseWSDL($wsdl=""){
		// parse wsdl file
		if($wsdl != ""){
			$this->debug("getting $wsdl");
			if ($fp = @fopen($wsdl,"r")) {
				while($data = fread($fp, 32768)) {
					$wsdl_string .= $data;
				}
				fclose($fp);
			} else {
				$this->setError("bad path to WSDL file.");
				return false;
			}
			// Create an XML parser.
			$this->parser = xml_parser_create();
			// Set the options for parsing the XML data.
			//xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
			xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);
			// Set the object for the parser.
			xml_set_object($this->parser, $this);
			// Set the element handlers for the parser.
			xml_set_element_handler($this->parser, "start_element","end_element");
			xml_set_character_data_handler($this->parser,"character_data");
			//xml_set_default_handler($this->parser, "default_handler");

			// Parse the XML file.
			if(!xml_parse($this->parser,$wsdl_string,true)){
				// Display an error message.
				$errstr = sprintf("XML error on line %d: %s",
					xml_get_current_line_number($this->parser),
					xml_error_string(xml_get_error_code($this->parser))
				);
				$this->debug("XML parse error: $errstr");
				$this->setError("Parser error: $errstr");
				return false;
			}
			xml_parser_free($this->parser);
		} else{
			$this->debug("no wsdl passed to parseWSDL()!!");
			$this->setError("no wsdl passed to parseWSDL()!!");
			return false;
		}

		// add new data to operation data
		foreach($this->bindings as $binding => $bindingData){
			if(is_array($bindingData['operations'])){
				foreach($bindingData['operations'] as $operation => $data){
					$this->debug("post-parse data gathering for $operation");
					$this->bindings[$binding]['operations'][$operation]['input'] = array_merge($this->bindings[$binding]['operations'][$operation]['input'],$this->portTypes[ $bindingData['portType'] ][$operation]['input']);
					$this->bindings[$binding]['operations'][$operation]['output'] = array_merge($this->bindings[$binding]['operations'][$operation]['output'],$this->portTypes[ $bindingData['portType'] ][$operation]['output']);
					$this->bindings[$binding]['operations'][$operation]['input']['parts'] = $this->messages[ $this->bindings[$binding]['operations'][$operation]['input']['message'] ];
					$this->bindings[$binding]['operations'][$operation]['output']['parts'] = $this->messages[ $this->bindings[$binding]['operations'][$operation]['output']['message'] ];
					if($this->bindings[$binding]['operations'][$operation]['style'] == ''){
						$this->bindings[$binding]['operations'][$operation]['style'] = $bindingData['style'];
					}
					$this->bindings[$binding]['operations'][$operation]['transport'] = $bindingData['transport'];
					$this->bindings[$binding]['operations'][$operation]['documentation'] = $this->portTypes[ $bindingData['portType'] ][$operation]['documentation'];
					$this->bindings[$binding]['operations'][$operation]['endpoint'] = $bindingData['endpoint'];
				}
			}
		}
		return true;
	}

	/**
	 * start-element handler
	 *
	 * @param    string $parser XML parser object
	 * @param    string $name element name
	 * @param    string $attrs associative array of attributes
	 * @access   private
	 */
	function start_element($parser, $name, $attrs) {

		if($this->status == "schema" || preg_match("/schema$/",$name)){
			//$this->debug("startElement for $name ($attrs[name]). status = $this->status (".$this->getLocalPart($name).")");
			$this->status = "schema";
			$this->schemaStartElement($parser,$name,$attrs);
		} else {
			// position in the total number of elements, starting from 0
			$pos = $this->position++;
			$depth = $this->depth++;
			// set self as current value for this depth
			$this->depth_array[$depth] = $pos;

			// get element prefix
			if(preg_match("/:/",$name)){
				// get ns prefix
				$prefix = substr($name,0,strpos($name,":"));
				// get unqualified name
				$name = substr(strstr($name,":"),1);
			}
			//$this->debug("name: $name, prefix: $prefix");

			// loop through atts, logging ns declarations
			foreach($attrs as $key => $value){
				// if ns declarations, add to class level array of valid namespaces
				if(preg_match("/^xmlns/",$key)){
					if($ns_prefix = substr(strrchr($key,":"),1)){
						$this->namespaces[$ns_prefix] = $value;
					} else {
						$this->namespaces['ns'.(count($this->namespaces)+1)] = $value;
					}
					if($value == 'http://www.w3.org/2001/XMLSchema'){
						$this->XMLSchemaVersion = $value;
						$this->namespaces['xsi'] = $value.'-instance';
					} elseif($value == 'http://www.w3.org/1999/XMLSchema'){
						$this->XMLSchemaVersion = $value;
						$this->namespaces['xsi'] = $value.'-instance';
					}
				}
			}

			// find status, register data
			switch($this->status){
				case 'message':
					if($name == 'part'){
						if($attrs['type']){
							//print "msg ".$this->currentMessage.": found part $attrs[name]: ".implode(',',$attrs)."<br>";
							$this->messages[$this->currentMessage][$attrs['name']] = $this->expandQname($attrs['type']);
							//print "i've stored it as: ".$this->messages[$this->currentMessage][$attrs['name']]."<br>";
						}
						if($attrs['element']){
							$this->messages[$this->currentMessage][$attrs['name']] = $this->expandQname($attrs['element']);
						}
					}
					break;
				case 'portType':
					switch($name){
						case 'operation':
							$this->currentPortOperation = $attrs["name"];
							$this->debug("portType $this->currentPortType operation: $this->currentPortOperation");
							$this->portTypes[$this->currentPortType][$attrs["name"]]["parameterOrder"] = $attrs["parameterOrder"];
							break;
						case 'documentation':
							$this->documentation = true;
							break;
							// merge input/output data
						default:
							$this->portTypes[$this->currentPortType][$this->currentPortOperation][$name]['message'] = $this->getLocalPart($attrs['message']);
							break;
					}
					break;
						case 'binding':
							switch($name){
								case 'binding':
									// get ns prefix
									if(isset($attrs['style'])){
										$this->bindings[$this->currentBinding]['prefix'] = $prefix;
									}
									$this->bindings[$this->currentBinding] = array_merge($this->bindings[$this->currentBinding],$attrs);
									break;
								case 'header':
									$this->bindings[$this->currentBinding]['operations'][$this->currentOperation][$this->opStatus]['headers'][] = $attrs;
									break;
								case 'operation':
									if($attrs['soapAction'] || $attrs['style']){
										$this->bindings[$this->currentBinding]['operations'][$this->currentOperation]['soapAction'] = $attrs['soapAction'];
										$this->bindings[$this->currentBinding]['operations'][$this->currentOperation]['style'] = $attrs['style'];
									} elseif($attrs['name']) {
										$this->currentOperation = $attrs['name'];
										$this->debug("current binding operation: $this->currentOperation");
										$this->bindings[$this->currentBinding]['operations'][$this->currentOperation]['name'] = $attrs['name'];
										$this->bindings[$this->currentBinding]['operations'][$this->currentOperation]['binding'] = $this->currentBinding;
										$this->bindings[$this->currentBinding]['operations'][$this->currentOperation]['endpoint'] = $this->bindings[$this->currentBinding]['endpoint'];
									}
									break;
								case 'input':
									$this->opStatus = 'input';
									break;
								case 'output':
									$this->opStatus = 'output';
									break;
								case 'body':
									$this->bindings[$this->currentBinding]['operations'][$this->currentOperation][$this->opStatus] = array_merge($this->bindings[$this->currentBinding]['operations'][$this->currentOperation][$this->opStatus],$attrs);
									break;
							}
							break;
								case "service":
									switch($name){
										case "port":
											$this->currentPort = $attrs['name'];
											$this->debug("current port: $this->currentPort");
											$this->ports[$this->currentPort]['binding'] = substr(strstr($attrs['binding'],":"),1);

											break;
										case "address":
											$this->ports[$this->currentPort]['location'] = $attrs['location'];
											$this->ports[$this->currentPort]['bindingType'] = $this->getNamespaceFromPrefix($prefix);
											$this->bindings[ $this->ports[$this->currentPort]['binding'] ]['bindingType'] = $this->getNamespaceFromPrefix($prefix);
											$this->bindings[ $this->ports[$this->currentPort]['binding'] ]['endpoint'] = $attrs['location'];
											//echo "port $this->currentPort, has binding ".$this->ports[$this->currentPort]['binding']." and endpoint ".$attrs['location']."<br>";
											break;
									}
									break;
			}
			// set status
			switch($name){
				case "import":
					if(isset($attrs['location'])){
						$this->import[$attrs['namespace']] = $attrs['location'];
					}
					break;
				case "types":
					$this->status = "schema";
					break;
				case "message":
					$this->status = "message";
					$this->messages[$attrs["name"]] = array();
					$this->currentMessage = $attrs["name"];
					break;
				case "portType":
					$this->status = "portType";
					$this->portTypes[$attrs["name"]] = array();
					$this->currentPortType = $attrs["name"];
					break;
				case "binding":
					if(isset($attrs['name'])){
						// get binding name
						if(preg_match("/:/",$attrs['name'])){
							$this->currentBinding = substr(strstr($attrs['name'],":"),1);
							$prefix = substr($name,0,strpos($attrs['name'],":"));
						} else {
							$this->currentBinding = $attrs['name'];
						}
						$this->status = "binding";
						$this->bindings[$this->currentBinding]['portType'] = substr(strstr($attrs['type'],":"),1);
						$this->debug("current binding: $this->currentBinding of portType: ".$attrs['type']);
					}
					break;
				case "service":
					$this->serviceName = $attrs["name"];
					$this->status = "service";
					break;
				case "definitions":
					foreach ($attrs as $name=>$value) {
						$this->wsdl_info[$name]=$value;
					}
					break;
			}
		}
	}

	/**
	 * end-element handler
	 *
	 * @param    string $parser XML parser object
	 * @param    string $name element name
	 * @access   private
	 */
	function end_element($parser, $name) {
		// unset schema status
		if(preg_match('/types$/',$name) || preg_match('/schema$/',$name)){
			$this->status = "";
		}
		if($this->status == 'schema'){
			$this->schemaEndElement($parser, $name);
		} else {
			// position of current element is equal to the last value left in depth_array for my depth
			$pos = $this->depth_array[$this->depth];
			// bring depth down a notch
			$this->depth--;
		}
		// end documentation
		if($this->documentation){
			$this->portTypes[$this->currentPortType][$this->currentPortOperation]['documentation'] = $this->documentation;
			$this->documentation = false;
		}
	}

	/**
	 * element content handler
	 *
	 * @param    string $parser XML parser object
	 * @param    string $data element content
	 * @access   private
	 */
	function character_data($parser, $data){
		$pos = $this->depth_array[$this->depth];
		$this->message[$pos]["cdata"] .= $data;
		if($this->documentation){
			$this->documentation .= $data;
		}
	}


	function getBindingData($binding){
		if(is_array($this->bindings[$binding])){
			return $this->bindings[$binding];
		}
	}

	function getMessageData($operation,$portType,$msgType){
		$name = $this->opData[$operation][$msgType]['message'];
		$this->debug( "getting msgData for $name, using $operation,$portType,$msgType<br>" );
		return $this->messages[$name];
	}

	/**
	 * returns an assoc array of operation names => operation data
	 * NOTE: currently only supports multiple services of differing binding types
	 * This method needs some work
	 *
	 * @param string $bindingType eg: soap, smtp, dime (only soap is currently supported)
	 * @return array
	 * @access public
	 */
	function getOperations($bindingType = "soap"){
		if($bindingType == "soap"){
			$bindingType = "http://schemas.xmlsoap.org/wsdl/soap/";
		}
		// loop thru ports
		foreach($this->ports as $port => $portData){
			// binding type of port matches parameter
			if($portData['bindingType'] == $bindingType){
				// get binding
				return $this->bindings[ $portData['binding'] ]['operations'];
			}
		}
		return array();
	}

	/**
	 * returns an associative array of data necessary for calling an operation
	 *
	 * @param string $operation, name of operation
	 * @param string $bindingType, type of binding eg: soap
	 * @return array
	 * @access public
	 */
	function getOperationData($operation,$bindingType="soap"){
		if($bindingType == "soap"){
			$bindingType = "http://schemas.xmlsoap.org/wsdl/soap/";
		}
		// loop thru ports
		foreach($this->ports as $port => $portData){
			// binding type of port matches parameter
			if($portData['bindingType'] == $bindingType){
				// get binding
				foreach($this->bindings[ $portData['binding'] ]['operations'] as $bOperation => $opData){
					if($operation == $bOperation){
						return $opData;
					}
				}
			}
		}
	}

	/**
	 * serialize the parsed wsdl
	 *
	 * @return string, serialization of WSDL
	 * @access   public
	 */
	function serialize(){
		$xml = "<?xml version=\"1.0\"?><definitions";
		foreach($this->namespaces as $k => $v){
			$xml .= " xmlns:$k=\"$v\"";
		}
		$xml .= ">";

		// imports
		if(sizeof($this->import) > 0){
			foreach($this->import as $ns => $url){
				$xml .= "<import location=\"$url\" namespace=\"$ns\" />\n";
			}
		}

		// types
		if($this->schema){
			$xml .= "<types>";
			//$xml .= $this->serializeSchema();
			$xml .= "</types>";
		}

		// messages
		if(count($this->messages) >= 1){
			foreach($this->messages as $msgName => $msgParts){
				$xml .= "<message name=\"$msgName\">";
				foreach($msgParts as $partName => $partType){
					$xml .= "<part name=\"$partName\" type=\"$partType\" />";
				}
				$xml .= "</message>";
			}
		}
		// portTypes
		if(count($this->portTypes) >= 1){
			foreach($this->portTypes as $portTypeName => $portOperations){
				$xml .= "<portType name=\"$portTypeName\">";
				foreach($portOperations as $portOperation => $parameterOrder){
					$xml .= "<operation name=\"$portOperation\" parameterOrder=\"$parameterOrder\">";
					foreach($this->portTypes[$portTypeName][$portOperation] as $name => $attrs){
						$xml .= "<$name";
						if(is_array($attrs)){
							foreach($attrs as $k => $v){
								$xml .= " $k=\"$v\"";
							}
						}
						$xml .= "/>";
					}
					$xml .= "</operation>";
				}
				$xml .= "</portType>";
			}
		}
		// bindings
		if(count($this->bindings) >= 1){
			foreach($this->bindings as $bindingName => $attrs){
				$xml .= "<binding name=\"$msgName\" type=\"".$attrs["type"]."\">";
				$xml .= "<soap:binding style=\"".$attrs["style"]."\" transport=\"".$attrs["transport"]."\"/>";
				foreach($attrs["operations"] as $opName => $opParts){
					$xml .= "<operation name=\"$opName\">";
					$xml .= "<soap:operation soapAction=\"".$opParts["soapAction"]."\"/>";
					$xml .= "<input>";
					$xml .= "<soap:body use=\"".$opParts["input"]["use"]."\" namespace=\"".$opParts["input"]["namespace"]."\" encodingStyle=\"".$opParts["input"]["encodingStyle"]."\"/>";
					$xml .= "</input>";
					$xml .= "<output>";
					$xml .= "<soap:body use=\"".$opParts["output"]["use"]."\" namespace=\"".$opParts["output"]["namespace"]."\" encodingStyle=\"".$opParts["output"]["encodingStyle"]."\"/>";
					$xml .= "</output>";
					$xml .= "</operation>";
				}
				$xml .= "</message>";
			}
		}
		// services
		$xml .= "<service name=\"$this->serviceName\">";
		if(count($this->ports) >= 1){
			foreach($this->ports as $pName => $attrs){
				$xml .= "<port name=\"$pName\" binding=\"".$attrs["binding"]."\">";
				$xml .= "soap:address location=\"".$attrs["location"]."\"/>";
				$xml .= "</port>";
			}
		}
		$xml .= "</service>";
		return $xml."</definitions>";
	}

	/**
	 * serialize a PHP value according to a WSDL message definition
	 *
	 * TODO
	 * - only serialize namespaces used in the message
	 * - multi-ref serialization
	 * - validate PHP values against type definitions, return errors if invalid
	 * - probably more stuff :)
	 * - implement 'out' functionality or write new function for 'out' parameters
	 *
	 * @param        string type name
	 * @param        mixed param value
	 * @return        mixed new param or false if initial value didn't validate
	 */
	function serializeRPCParameters($operation,$direction,$parameters){
		if($direction != 'input' && $direction != 'output'){
			$this->setError('The value of the \$direction argument needs to be either "input" or "output"');
			return false;
		}
		if(!$opData = $this->getOperationData($operation)){
			return false;
		}
		$this->debug( "in serializeRPCParameters with xml schema version $this->XMLSchemaVersion");
		// set input params
		if(sizeof($opData[$direction]['parts']) > 0){
			foreach($opData[$direction]['parts'] as $name => $type){
				$xml .= $this->serializeType($name,$type,array_shift($parameters));
			}
		}
		return $xml;
	}

	/**
	 * serializes a PHP value according a given type definition
	 *
	 * @param string $name, name of type
	 * @param string $type, type of type, heh
	 * @param mixed $value, a native PHP value
	 * @return string serialization
	 * @access public
	 */
	function serializeType($name,$type,$value){
		$this->debug("in serializeType: $name, $type, $value");
		if(strpos($type,':')){
			$uqType = substr($type,strrpos($type,":")+1);
			$ns = substr($type,0,strrpos($type,":"));
			$this->debug("got a prefixed type: $uqType, $ns");
			if($ns == $this->XMLSchemaVersion){
				if($uqType == 'boolean' && !$value){
					$value = 0;
				} elseif($uqType == 'boolean'){
					$value = 1;
				}
				if($uqType == 'string' && $this->charencoding){
					$value = htmlspecialchars($value);
				}
				// it's a scalar
				return "<$name xsi:type=\"".$this->getPrefixFromNamespace($this->XMLSchemaVersion).":$uqType\">$value</$name>\n";
			}
		} else {
			$uqType = $type;
		}
		$typeDef = $this->getTypeDef($uqType);
		$phpType = $typeDef['phpType'];
		$this->debug("serializeType: uqType: $uqType, ns: $ns, phptype: $phpType, arrayType: ".$typeDef['arrayType']);
		// if php type == struct, map value to the <all> element names
		if($phpType == "struct"){
			$xml = "<$name xsi:type=\"".$this->getPrefixFromNamespace($ns).":$uqType\">\n";
			if(is_array($this->complexTypes[$uqType]["elements"])){
				foreach($this->complexTypes[$uqType]["elements"] as $eName => $attrs){
					// get value
					if(isset($value[$eName])){
						$v = $value[$eName];
					} elseif(is_array($value)) {
						$v = array_shift($value);
					}
					if(!isset($attrs['type'])){
						$xml .= $this->serializeType($eName,$attrs['name'],$v);
					} else {
						$this->debug("calling serialize_val() for $eName, $v, ".$this->getLocalPart($attrs['type']));
						$xml .= $this->serialize_val($v,$eName,$this->getLocalPart($attrs['type']),null,$this->getNamespaceFromPrefix($this->getPrefix($attrs['type'])));
					}
				}
			}
			$xml .= "</$name>\n";
		} elseif($phpType == "array"){
			$rows = sizeof($value);
			if($typeDef['multidimensional']){
				$nv = array();
				foreach($value as $v){
					$cols = ','.sizeof($v);
					$nv = array_merge($nv,$v);
				}
				$value = $nv;
			}
			if(is_array($value) && sizeof($value) >= 1){
				foreach($value as $k => $v){
					if(strpos($typeDef['arrayType'],':')){
						$contents .= $this->serializeType('item',$typeDef['arrayType'],$v);
					} else {
						$contents .= $this->serialize_val($v,'item',$typeDef['arrayType'],null,$this->XMLSchemaVersion);
					}
				}
			}
			$xml = "<$name xsi:type=\"".$this->getPrefixFromNamespace('http://schemas.xmlsoap.org/soap/encoding/').":Array\" ".
				$this->getPrefixFromNamespace('http://schemas.xmlsoap.org/soap/encoding/')
				.":arrayType=\""
				.$this->getPrefixFromNamespace($this->getPrefix($typeDef['arrayType']))
				.":".$this->getLocalPart($typeDef['arrayType'])."[$rows$cols]\">\n"
				.$contents
				."</$name>\n";
		}
		return $xml;
	}
}

?>
<?php
/*

NuSOAP - Web Services Toolkit for PHP

Copyright (c) 2002 NuSphere Corporation

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

 */

/**
 *
 * soap_parser class parses SOAP XML messages
 *
 * @author   Dietrich Ayala <dietricha@ganx4.com>
 * @version  v 0.051
 * @access   public
 */
class soap_parser extends nusoap_base {
	/**
	 * constructor
	 *
	 * @param    string $xml SOAP message
	 * @param    string $encoding character encoding scheme of message
	 * @access   public
	 */
	function __construct($xml,$encoding="UTF-8",$method=""){
		$this->xml = $xml;
		$this->xml_encoding = $encoding;
		$this->method = $method;
		$this->root_struct = "";
		$this->root_struct_name = "";
		$this->root_header = "";
		// determines where in the message we are (envelope,header,body,method)
		$this->status = "";
		$this->position = 0;
		$this->depth = 0;
		$this->default_namespace = "";
		$this->namespaces = array();
		$this->message = array();
		$this->fault = false;
		$this->fault_code = "";
		$this->fault_str = "";
		$this->fault_detail = "";
		$this->errstr = "";
		$this->depth_array = array();
		$this->debug_flag = true;
		$this->debug_str = "";
		$this->soapresponse = NULL;
		$this->responseHeaders = "";
		// for multiref parsing:
		// array of id => pos
		$this->ids = array();
		// array of id => hrefs => pos
		$this->multirefs = array();

		$this->entities = array ( "&" => "&amp;", "<" => "&lt;", ">" => "&gt;",
			"'" => "&apos;", '"' => "&quot;" );

		// Check whether content has been read.
		if(!empty($xml)){
			$this->debug("Entering soap_parser()");
			// Create an XML parser.
			$this->parser = xml_parser_create($this->xml_encoding);
			// Set the options for parsing the XML data.
			//xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
			xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);
			// Set the object for the parser.
			xml_set_object($this->parser, $this);
			// Set the element handlers for the parser.
			xml_set_element_handler($this->parser, "start_element","end_element");
			xml_set_character_data_handler($this->parser,"character_data");
			//xml_set_default_handler($this->parser, "default_handler");

			// Parse the XML file.
			if(!xml_parse($this->parser,$xml,true)){
				// Display an error message.
				$err = sprintf("XML error on line %d: %s",
					xml_get_current_line_number($this->parser),
					xml_error_string(xml_get_error_code($this->parser)));
				$this->debug("parse error: $err");
				$this->errstr = $err;
			} else {
				$this->debug("parsed successfully, found root struct: $this->root_struct of name $this->root_struct_name");
				// get final value
				$this->soapresponse = $this->message[$this->root_struct]['result'];
				// get header value
				if($this->root_header != ""){
					$this->responseHeaders = $this->message[$this->root_header]['result'];
				}
			}
			xml_parser_free($this->parser);
		} else {
			$this->debug("xml was empty, didn't parse!");
			$this->errstr = "xml was empty, didn't parse!";
		}
	}

	/**
	 * start-element handler
	 *
	 * @param    string $parser XML parser object
	 * @param    string $name element name
	 * @param    string $attrs associative array of attributes
	 * @access   private
	 */
	function start_element($parser, $name, $attrs) {
		// position in a total number of elements, starting from 0
		// update class level pos
		$pos = $this->position++;
		// and set mine
		$this->message[$pos]["pos"] = $pos;
		// depth = how many levels removed from root?
		// set mine as current global depth and increment global depth value
		$this->message[$pos]["depth"] = $this->depth++;

		// else add self as child to whoever the current parent is
		if($pos != 0){
			$this->message[$this->parent]["children"] .= "|$pos";
		}
		// set my parent
		$this->message[$pos]["parent"] = $this->parent;
		// set self as current parent
		$this->parent = $pos;
		// set self as current value for this depth
		$this->depth_array[$this->depth] = $pos;
		// get element prefix
		if(strpos($name,":")){
			// get ns prefix
			$prefix = substr($name,0,strpos($name,":"));
			// get unqualified name
			$name = substr(strstr($name,":"),1);
		}
		// set status
		if($name == "Envelope"){
			$this->status = "envelope";
		} elseif($name == "Header"){
			$this->root_header = $pos;
			$this->status = "header";
		} elseif($name == "Body"){
			$this->status = "body";
			$this->body_position = $pos;
			// set method
		} elseif($this->status == "body" && $pos == ($this->body_position+1)){
			//if($name == $this->method."Response" || $name == $this->method || $name == "Fault"){
			$this->status = "method";
			$this->root_struct_name = $name;
			$this->root_struct = $pos;
			$this->message[$pos]["type"] = "struct";
			$this->debug("found root struct $this->root_struct_name, pos $pos");
			//}
		}
		// set my status
		$this->message[$pos]["status"] = $this->status;
		// set name
		$this->message[$pos]["name"] = htmlspecialchars($name);
		// set attrs
		$this->message[$pos]["attrs"] = $attrs;
		// get namespace
		if($prefix){
			$this->message[$pos]["namespace"] = $this->namespaces[$prefix];
			$this->default_namespace = $this->namespaces[$prefix];
		} else {
			$this->message[$pos]["namespace"] = $this->default_namespace;
		}
		// loop through atts, logging ns and type declarations
		foreach($attrs as $key => $value){

			// if ns declarations, add to class level array of valid namespaces
			if(strpos($key,'xmlns:')){
				$prefix = substr(strrchr($key,":"),1);
				if(preg_match('|^http://www.w3.org/[0-9]{4}/XMLSchema$|',$value)){
					global $XMLSchemaVersion,$namespaces;
					$XMLSchemaVersion = $value;
					$namespaces["xsd"] = $XMLSchemaVersion;
					$namespaces["xsi"] = $XMLSchemaVersion."-instance";
				}
				$this->namespaces[substr(strrchr($key,":"),1)] = $value;
				// set method namespace
				if($name == $this->root_struct_name){
					$this->methodNamespace = $value;
				}
				// if it's a type declaration, set type
			} elseif(strpos($key,":type")){
				$this->message[$pos]["type"] = substr(strrchr($value,":"),1);
				$this->message[$pos]["typePrefix"] = substr($value,0,strpos($key,":")-1);
				// should do something here with the namespace of specified type?
			} elseif(strpos($key,":arrayType")){
				$this->message[$pos]['type'] = 'array';
										  /* do arrayType preg here
										  [1]    arrayTypeValue    ::=    atype asize
										  [2]    atype    ::=    QName rank*
										  [3]    rank    ::=    '[' (',')* ']'
										  [4]    asize    ::=    '[' length~ ']'
										  [5]    length    ::=    nextDimension* Digit+
										  [6]    nextDimension    ::=    Digit+ ','
											*/
				$expr = "/([A-Za-z0-9_]+):([A-Za-z]+[A-Za-z0-9_]+)\[([0-9]+),?([0-9]*)\]/";
				if(preg_match($expr,$value,$regs)){
					$this->message[$pos]['typePrefix'] = $regs[1];
					$this->message[$pos]['arraySize'] = $regs[3];
					$this->message[$pos]['arrayCols'] = $regs[4];
				}
			}
			// log id
			if($key == "id"){
				$this->ids[$value] = $pos;
			}
			// root
			if(strpos($key,":root") && $value == 1){
				$this->status = "method";
				$this->root_struct_name = $name;
				$this->root_struct = $pos;
				$this->debug("found root struct $this->root_struct_name, pos $pos");
			}
		}
	}

	/**
	 * end-element handler
	 *
	 * @param    string $parser XML parser object
	 * @param    string $name element name
	 * @access   private
	 */
	function end_element($parser, $name) {
		// position of current element is equal to the last value left in depth_array for my depth
		$pos = $this->depth_array[$this->depth];
		// bring depth down a notch
		$this->depth--;

		// build to native type
		if($pos > $this->body_position){
			// deal w/ multirefs
			if(isset($this->message[$pos]['attrs']['href'])){
				// get id
				$id = substr($this->message[$pos]['attrs']['href'],1);
				// add placeholder to href array
				$this->multirefs[$id][$pos] = "placeholder";
				// add set a reference to it as the result value
				$this->message[$pos]['result'] =& $this->multirefs[$id][$pos];
			} elseif($this->message[$pos]['children'] != ""){
				$this->message[$pos]['result'] = $this->buildVal($pos);
			} else {
				$this->message[$pos]['result'] = $this->message[$pos]['cdata'];
			}
		}

		// switch status
		if($pos == $this->root_struct){
			$this->status = "body";
		} elseif(preg_match("/:Body/i",$name)){
			$this->status = "header";
		} elseif(preg_match("/:Header/i",$name)){
			$this->status = "envelope";
		} elseif(preg_match("/:Envelope/i",$name)){
			// resolve hrefs/ids
			if(sizeof($this->multirefs) > 0){
				foreach($this->multirefs as $id => $hrefs){
					$this->debug("resolving multirefs for id: $id");
					foreach($hrefs as $refPos => $ref){
						$this->debug("resolving href at pos $refPos");
						$this->multirefs[$id][$refPos] = $this->buildval($this->ids[$id]);
					}
				}
			}
		}
		// set parent back to my parent
		$this->parent = $this->message[$pos]["parent"];
	}

	/**
	 * element content handler
	 *
	 * @param    string $parser XML parser object
	 * @param    string $data element content
	 * @access   private
	 */
	function character_data($parser, $data){
		$pos = $this->depth_array[$this->depth];
		$this->message[$pos]["cdata"] .= $data;
	}

	/**
	 * get the parsed message
	 *
	 * @return        object SOAPx4 soap_val object
	 * @access   public
	 */
	function get_response(){
		return $this->soapresponse;
	}

	/**
	 * get the parsed headers
	 *
	 * @return        mixed object SOAPx4 soapval object or empty if no headers
	 * @access   public
	 */
	function getHeaders(){
		return $this->responseHeaders;
	}

	/**
	 * decodes entities
	 *
	 * @param    string $text string to translate
	 * @access   private
	 */
	function decode_entities($text){
		foreach($this->entities as $entity => $encoded){
			$text = str_replace($encoded,$entity,$text);
		}
		return $text;
	}

	/**
	 * builds response structures for compound values (arrays/structs)
	 *
	 * @param    string $pos position in node tree
	 * @access   private
	 */
	function buildVal($pos){
		// build self
		$this->debug("inside buildVal() for ".$this->message[$pos]['name']."(pos $pos) of type ".$this->message[$pos]["type"]);
		// if there are children...
		if($this->message[$pos]["children"] != ""){
			$children = explode("|",$this->message[$pos]["children"]);
			array_shift($children); // knock off empty
			// loop thru them, getting params
			foreach($children as $child_pos){
				// md array
				if($this->message[$pos]['arrayCols']){
					$this->debug("got an MD array element: $r, $c");
					$params[$r][] = $this->message[$child_pos]['result'];
					$c++;
					if($c == $this->message[$pos]['arrayCols']){
						$c = 0;
						$r++;
					}
				} elseif($this->message[$pos]['type'] == 'array'){
					$params[] =& $this->message[$child_pos]['result'];
				} else {
					$params[$this->message[$child_pos]["name"]] =& $this->message[$child_pos]['result'];
				}
			}
			return is_array($params) ? $params : array();
		} else {
			//return $this->message[$pos]['cdata'];
			return  strtr($this->message[$pos]['cdata'],array_flip($this->entities));
		}
	}

	/**
	 * for building SOAP header values
	 *
	 * @param    string $pos position in node tree
	 * @access   private
	 */
	function buildSoapVal($pos){
		// if there are children...
		if($this->message[$pos]["children"] != ""){
			$children = explode("|",$this->message[$pos]["children"]);
			// loop thru them, getting params
			foreach($children as $c => $child_pos){
				if($this->message[$child_pos]["type"] != NULL) {
					$this->debug("adding ".$this->message[$child_pos]["name"].", pos: $child_pos");
					$params[] = $this->message[$child_pos]['result'];
				}
			}
		}
		// build self
		$this->debug("building ".$this->message[$pos]['name']."(pos $pos) of type ".$this->message[$pos]["type"]);
		if($params){
			return new soapval($this->message[$pos]["name"], $this->message[$pos]["type"] , $params);
		} else {
			return new soapval($this->message[$pos]["name"], $this->message[$pos]["type"] , $this->message[$pos]["cdata"]);
		}
	}
}
