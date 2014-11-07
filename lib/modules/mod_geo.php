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
		$url="http://maps.google.com/maps/geo?q=".$address."&output=".$send_output."&key=".$this->api_key;
		$res = file_get_contents($url);
		if ($output=='php') {
			return JSON::decode($res);
		} else {
			return $res;
		}
	}

	function getLatLong($address) {
		$data=$this->getRawData($address, 'php');
		if ($data->Status->code!=200) {
			return error::raiseError('MOD_GEO: connection to Google Maps failed: '.$data->Status->code, 'geo_4');
		} else {
			$coordinates = $data->Placemark[0]->Point->coordinates;
			return array('lat' => $coordinates[1], 'long' => $coordinates[0], 'alt' => $coordinates[2]);
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
			$this->getter->api_key = $this->api_key;
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

	/**
	*   returns an array with a lat and a long as calculated from the exif GPS info.
	*
	* @param	array	$exif	An exif block as returned by exif_read_data() and pphoto::getExif(). Make sure the GPS part is included.
	*/

	function parseNumber($number) {
		$parts = explode("/", $number);
		if ($parts[1] != 0) {
			return $parts[0] / $parts[1];
		}
		return false;
	}

	function exifToLatLong($exif) {
		if ($exif['GPS'] && is_array($exif['GPS']['GPSLatitude']) && is_array($exif['GPS']['GPSLongitude']) ) {
			$lat_degree = (float)self::parseNumber($exif["GPS"]["GPSLatitude"][0]);
			$lat_mins   = (float)self::parseNumber($exif["GPS"]["GPSLatitude"][1]);
			$lat_secs   = (float)self::parseNumber($exif["GPS"]["GPSLatitude"][2]);
			$result = array();
			$result['lat'] = $lat_degree + ( $lat_mins / 60 ) + ( $lat_secs / 3600 );
			if( $exif['GPS']['GPSLatitudeRef'] == 'S' ) {
				  $result['lat'] = (-1) * $result['lat'];
			}

			$lng_degree = (float)self::parseNumber($exif["GPS"]["GPSLongitude"][0]);
			$lng_mins   = (float)self::parseNumber($exif["GPS"]["GPSLongitude"][1]);
			$lng_secs   = (float)self::parseNumber($exif["GPS"]["GPSLongitude"][2]);
			$result['lng'] = $lng_degree + ( $lng_mins / 60 ) + ( $lng_secs / 3600 );
			if( $exif['GPS']['GPSLongitudeRef'] == 'W' ) {
				$result['lng'] = (-1) * $result['lng'];
			}
			return $result;
		} else {
			return error::raiseError('MOD_GEO: No EXIF GPS block given', 'geo_6');
		}
	}

	//
	// Convert Rijksdriehoekscodinaten to latitude/longitude
	// http://nl.wikipedia.org/wiki/Rijksdriehoeksco%C3%B6rdinaten
	//
	function rd2wgs ($x, $y) {
	    // Calculate WGS84 coördinates
	    $dX = ($x - 155000) * pow(10, - 5);
	    $dY = ($y - 463000) * pow(10, - 5);
	    $SomN = (3235.65389 * $dY) + (- 32.58297 * pow($dX, 2)) + (- 0.2475 *
	         pow($dY, 2)) + (- 0.84978 * pow($dX, 2) *
	         $dY) + (- 0.0655 * pow($dY, 3)) + (- 0.01709 *
	         pow($dX, 2) * pow($dY, 2)) + (- 0.00738 *
	         $dX) + (0.0053 * pow($dX, 4)) + (- 0.00039 *
	         $dX ^ 2 * pow($dY, 3)) + (0.00033 * pow(
	            $dX, 4) * $dY) + (- 0.00012 *
	         $dX * $dY);
	    $SomE = (5260.52916 * $dX) + (105.94684 * $dX * $dY) + (2.45656 *
	         $dX * pow($dY, 2)) + (- 0.81885 * pow(
	            $dX, 3)) + (0.05594 *
	         $dX * pow($dY, 3)) + (- 0.05607 * pow(
	            $dX, 3) * $dY) + (0.01199 *
	         $dY) + (- 0.00256 * pow($dX, 3) * pow(
	            $dY, 2)) + (0.00128 *
	         $dX * pow($dY, 4)) + (0.00022 * pow($dY,
	            2)) + (- 0.00022 * pow(
	            $dX, 2)) + (0.00026 *
	         pow($dX, 5));

	    $Latitude = 52.15517 + ($SomN / 3600);
	    $Longitude = 5.387206 + ($SomE / 3600);

	    return array(
	        'latitude' => $Latitude ,
	        'longitude' => $Longitude);
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

	function _exifToLatLong($exif) {
		return parent::exifToLatLong($exif);
	}

	function _rd2wgs($x,$y) { return parent::rd2wgs($x,$y); }

}
