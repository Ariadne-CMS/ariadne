<?php
/**
 * mod_geo, a helper module for Ariadne to get geotag addresses in pinp/php.
 * 
 * mod_geo needs a Google Maps key to function. Set it once using init()
 * 
 * @author Gerhard Hoogterp <gerhard@frappe.xs4all.nl>
 * @author Auke van Slooten <auke@muze.nl>
*/

require_once($this->store->get_config('code')."/modules/mod_json.php");
require_once($this->store->get_config('code')."/modules/mod_error.php");

/**
 * This is the Google Maps geo helper/getter class
 */
class geo_gmap {
	function getRawData($address, $output='php') {
		$address	= urlencode($address);
		if ($output=='php') {
			$send_output = 'json';
		} else {
			$send_output = $output;
		}
		$url="http://maps.google.com/maps/geo?q=".$address."&output=".$send_output."&key=".$key;
		$res = file_get_contents($url);
		if ($output=='php') {
			return JSON::decode($res);
		} else {
			return $res;
		}
	}
	
	function getLatLong($address) {
		$data=$this->getRawData($address, 'json');
		if ($data->Status->code!=200) {
			return error::raiseError('MOD_GEO: connection to Google Maps failed: '.$data->Status->code, 'geo_4');
		} else {
			$coordinates = $data->Placemark[0]->Point->coordinates;
			return array('lat' => $coordinates[0], 'long' => $coordinates[1], 'alt' => $coordinates[2]);
		}
	}
}

/**
 * This is the main mod_geo class
 */
class geo {

	/**
	*  init configures the geo module to use the given API and any necessary extra parameters for that API
	* 
	* @param 	hash	$config	hash containing at least 'API', default is 'GMap' for Google Maps.
	*/
	function init($config) {
		if (!$config['API'] || $config['API']=='GMap') {
			$this->api = 'GMap';
			$this->api_key = $config['KEY'];
			if (!$this->api_key) {
				return error::raiseError('MOD_GEO: Google Maps needs an API KEY, please provide one','geo_3');
			}
			$this->output = $config['OUTPUT'];
			if (!$this->output) {
				$this->output = 'php';
			}
			$this->getter = new geo_gmap();
			return true;
		} else {
			return error::raiseError('MOD_GEO: API "'.$config['API'].'" not supported', 'geo_0');
		}
	}

	/**
	*  getRawData returns full address data for a given address, as returned by Google Maps
	*	
	* returns the data for the address $address in the format specified by $output.
	* default is php. Other options area xml, kml, json and cvs.
	* 
	* @param	string	$address	street address, format is 'streetname  housenumber, zipcode, state,  country'. You may skip values if unknown or not applicable. 
	* @param	string	$output	type of output to return, possible values are 'xml','kml','cvs' and 'json'. Default is 'json'. In the case of 'json' the result will automatically be converted to PHP arrays
	*/
	function getRawData($address, $output = null) {
		if (!$output) {
			$output = $this->output;
		}
		if (!$address) {
			return error::raiseError('MOD_GEO: No address given to getRawData', 'geo_1');
		} else if ($this->getter) {
			return $this->getter->getRawData($address, $output);
		} else {
			return error::raiseError('MOD_GEO: GEO module not initialized, call init method first', 'geo_2');
		}
	}

	/**
	*   returns a string with Lat,Lng,Alt for the given address.
	*
	* @param	string	$address	street address, format is 'streetname  housenumber, zipcode, state,  country'. You may skip values if unknown or not applicable. 
	*/
	
	function getLatLong($address) {
		if (!$address) {
			return error::raiseError('MOD_GEO: No address given to getLatLong', 'geo_5');
		} else if ($this->getter) {
			return $this->getter->getLatLong($address);
		} else {
			return error::raiseError('MOD_GEO: GEO module not initialized, call init method first', 'geo_2');
		}
	}
}

/**
 * This is the PINP mod_geo wrapper class.
 */
class pinp_geo extends geo {

	function _init($config) {
		$geo = new pinp_geo();
		$result = $geo->init($config);
		if (!error::isError($result)) {
			return $geo;
		} else {
			return $result;
		}
	}

	function _getRawData($address, $output = null) {
		return parent::getRawData($address, $output);
	}

	function _getLatLong($address) {
		return parent::getLatLong($address);
	}
	
}


?>