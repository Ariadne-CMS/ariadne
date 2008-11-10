<?php

define("DIRMODE", 0770);

class cacherss {
	function cacherss( $httphelper ) {
		$this->httphelper = $httphelper;
	}
	
	function load( $url ) {
		$data = $this->httphelper->load( $url, "", time() - (30*60) );
		$this->xmldata = $data["data"];

		$rssfeed = new cacherssfeed(); // load from string
		$result = $rssfeed->parseString( $this->xmldata );

		return $result;
	}

	function titlelink($url) {
		$data = $this->httphelper->load( $url, "", time() - (30*60) );
		$this->xmldata = $data["data"];

		$rssfeed = new cacherssfeed(); // load from string

		$rssfeed->parseString( $this->xmldata);
		$rss_channel = $rssfeed->info();
		return Array("title" => $rss_channel['title'], "link" => $rss_channel['link']);
	}	
}


class pinp_cacherss {
	function _load( $url ) {
		global $AR;
		$cachelocation = $AR->dir->install . "/files/cache/rss/";
		$cache = new cache( $cachelocation );
		$httphelper = new httphelper( $cache);
		$rss = new cacherss( $httphelper );
		return $rss->load( $url );
	}

	function _titlelink( $url ) {
		global $AR;
		$cachelocation = $AR->dir->install . "/files/cache/rss/";
		$cache = new cache( $cachelocation );
		$httphelper = new httphelper( $cache);
		$rss = new cacherss( $httphelper );
		return $rss->titlelink( $url );
	}
}


class cacherssfeed {
	function parseString( $xmldata ) {
		// reset namestack
		$this->xmldata = $xmldata;
		$this->ns = Array();
		$this->elements = Array();
		$this->rss_items = Array();
		$this->rss_channel = Array();

		// Finding the RSS feed source encoding - thanks to the pointers on
		// http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
		//
		// Read the first part of the RSS feed to find the encoding.


		// Prepare a regexp to find the source encoding, and match it. If we find it, use that - otherwise assume UTF-8 as the default XML encoding.
		$encoding_regexp = '/<?xml.*encoding=[\'"](.*?)[\'"].*?>/m';

		// $encoding_regexp = '/.*encoding=[\'"]/m';

		if (preg_match($encoding_regexp, $this->xmldata, $matches)) {
			$this->encoding = strtoupper($matches[1]);
		} else {
			$this->encoding = "UTF-8";
		}

		// The RSS library only understands UTF-8, US-ASCII and ISO-8859-1, so only give it this. For other encodings we'll default to UTF-8.

		if($this->encoding == "UTF-8" || $this->encoding == "US-ASCII" || $this->encoding == "ISO-8859-1") {
			$this->parser = xml_parser_create($this->encoding);
		} else {
			$this->parser = xml_parser_create("UTF-8");
			$this->encoding = "UTF-8";
		}

		// Check if we have valid xml
		$parsetest = xml_parse(xml_parser_create($this->encoding), $xmldata);
		if (!$parsetest) {
			//echo "XML doesn't parse\n";
			return false;
		}

		//$this->parser = xml_parser_create();
		xml_set_object($this->parser, $this);
		xml_set_element_handler($this->parser, "startElement", "endElement");
		xml_set_character_data_handler($this->parser, "characterData");
		xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
		xml_parser_set_option($this->parser, XML_OPTION_TARGET_ENCODING, "UTF-8");

		// return the array
		return $this->getArray();

	}

	function startElement($parser, $name, $attribs) {
		$newElement = Array();
		$element = &$this->elements;
		foreach ($this->ns as $n) {
			$parentElement = $element;
			if (is_array($element) && isset($element[$n])) {
				$element = &$element[$n];
			}
		}
		$this->ns[] = $name;
		switch ($name) {
			default:
				if (is_array($attribs)) {
					foreach ($attribs as $key => $value) {
						$element[$name . ":" . $key] = $value;
					}
				}
				$element[$name] = $newElement;
		}
	}

	function endElement($parser, $name) {
		$element = &$this->elements;
		foreach ($this->ns as $n) {
			$parentElement = $element;
			if (is_array($element) && isset($element[$n])) {
				$element = &$element[$n];
			}
		}
		switch ($name) {
			case 'item':
				$this->rss_items[] = $element;
				unset($parentElement[$name]);
			break;
			case 'channel':
				$this->rss_channel = $element;
			break;
		}
		array_pop($this->ns);
	}

	function characterData($parser, $data) {
		$element = &$this->elements;
		foreach ($this->ns as $n) {
			if (is_array($element) && isset($element[$n])) {
				$element = &$element[$n];
			}
		}
		switch ($n) {
			case 'textinput':
			case 'item':
			case 'rss':
			case 'channel':
			case 'image':
			case 'rdf:RDF':
			case 'rdf:Seq':
			case 'rdf:li':
			case 'items':
				break;
			default:
				if (!$element) {
					$element = "";
				}
				$element .= $data;
			break;
		}
	}

	function current() {
		return $this->rss_items[0];
	}

	function info() {
		return $this->rss_channel;
	}

	function next() {
		// this is needed 
		if (!$this->parser) {
			return false;
		}

		xml_set_object($this->parser, $this);

		/* remove the last item from the queue */
		if (count($this->rss_items)) {
			array_shift($this->rss_items);
		}
		if (!count($this->rss_items) && $this->xmldata) {
			$rss_data = $this->xmldata;
			// $this->xmldata = false;

			if (!xml_parse($this->parser, $rss_data, $eof)) {
				$this->error = sprintf("XML error: %s at line %d",
					xml_error_string(xml_get_error_code($this->parser)),
					xml_get_current_line_number($this->parser));
			}
		}
		return $this->rss_items[0];
	}
	
	function getArray() {
		$result=Array();
		do {
			$result[]=$this->current();
		} while ($this->next());
		return $result;
	}
}

class httphelper {

	function httphelper( $cache ) {
		$this->cache = $cache;
	}

	function load( $url, $meta="", $maxage=0, $user="" ) {
		$result = $this->cache->load($url, $maxage, $user);
		if( !$result && $maxage >= 0 ) {
			$data = $this->HTTPRequest("GET", $url);
			if( $data != "" ) {
				$result = $this->cache->save($url, $data, $meta, $user);
			}
		} 
		return $result;
	}

	function HTTPRequest($method, $url, $postdata = "", $port=80 ) { 
		$maxtries = 5;
		$tries = 0;
		$redirecting = true;

		if(is_array($postdata)) { 
			foreach($postdata as $key=>$val) { 
				if(!is_integer($key)) {
					$data .= "$key=".urlencode($val)."&"; 
				}
			} 
		} else { 
			$data = $postdata; 
		}

		while ($redirecting && $tries < $maxtries) {
			$tries++; 
			// get host name and URI from URL, URI not needed though 
			preg_match("/^([htps]*:\/\/)?([^\/]+)(.*)/i", $url, $matches); 
			$host = $matches[2]; 
			$uri = $matches[3]; 
			if (!$matches[1]) {
				$url="http://".$url;
			}
			$connection = @fsockopen( $host, $port, $errno, $errstr, 120); 
			if( $connection ) { 
				if( strtoupper($method) == "GET" ) { 
					if ($data) {
						$url .= "?" . $data; 
					}
					fputs( $connection, "GET $uri HTTP/1.0\r\n"); 
				} else if( strtoupper($method) == "POST" ) { 
					fputs( $connection, "POST $uri HTTP/1.0\r\n"); 
				} else {
					fputs( $connection, "$method $uri HTTP/1.0\r\n");
				}

				fputs( $connection, "Host: $host\r\n");
				fputs( $connection, "Accept: */*\r\n"); 
				fputs( $connection, "Accept: image/gif\r\n"); 
				fputs( $connection, "Accept: image/x-xbitmap\r\n"); 
				fputs( $connection, "Accept: image/jpeg\r\n"); 

				if( strtoupper($method) == "POST" ) { 
					$strlength = strlen( $data); 
					fputs( $connection, "Content-type: application/x-www-form-urlencoded\r\n" ); 
					fputs( $connection, "Content-length: ".$strlength."\r\n\r\n"); 
					fputs( $connection, $data."\r\n"); 
				} 

				fputs( $connection, "\r\n" , 2); 
				$output = ""; 

				$headerContents = '';
				$headerStart = 0; 
				$headerEnd = 0; 
				$redirecting = false; 

				while (!feof($connection)) { 
					$currentLine = fgets ($connection, 1024); 
					if ($headerEnd && $redirecting) { 
						break; 
					} else if ($headerEnd && !$redirecting) { 
						//this is the html from the page 
						$contents = $contents . $currentLine; 
					} else if ( ereg("^HTTP", $currentLine) ) { 
						//came to the start of the header 
						$headerStart = 1; 
						$headerContents = $currentLine;
					} else if ( $headerStart && preg_match('/^[\n\r\t ]*$/', $currentLine) ) { 
						//came to the end of the header 
						$headerEnd = 1; 
					} else { 
						//this is the header, if you want it... 
						if (preg_match("/^Location: (.+?)\n/is",$currentLine,$matches) ) { 
							$headerContents .= $currentLine;
							//redirects are sometimes relative 
							$newurl = $matches[1]; 
							if (!preg_match("/http:\/\//i", $newurl, $matches) ) { 
								$url .= $newurl; 
							} else { 
								$url = $newurl; 
							} 
							//extra \r's get picked up sometimes 
							//i think only with relative redirects 
							//this is a quick fix. 
							$url = preg_replace("/\r/s","",$url); 
							$redirecting = true; 
						} else {
							$headerContents.=$currentLine;
						}
					} 
				} 
			} else {
				$this->error="$errstr ($errno)";
				$contents=false;
				$url = "";
			}
			@fclose($connection); 
		}
		if (($method!="GET") && ($method!="POST")) {
			$contents=$headerContents."\n".$contents;
		}
		return $contents; 
	} 


}

class cache {

	function cache( $path ) {
		// FIXME: forceer $path eindigen op een / en security.

		if (!is_dir($path) && !file_exists($path)) {
			mkdir($path, DIRMODE);
		}

		$this->path = $path;
	}


	function load( $tag, $maxage = 0, $user = "" ) {
	
		if( !$tag ) {
			return false;
		}
	
		$tag = $this->escape( $tag );
	
		$result = false;


		if( $user != "" ) {
			$user = $user."/";
		}

		$path = $this->path. $user. $tag;
		if (file_exists($path) && filectime($path) >= $maxage) {
			$fp = fopen( $path, "rb" );
			$data=fread($fp,filesize($path) );
			$timestamp = filectime($path);
			fclose($fp);
			$metapath = $path.".meta";
			$meta = "";
			if( file_exists($metapath)) {
				$fp = fopen( $metapath, "rb" );
				$meta = fread($fp, filesize($metapath) );
				fclose($fp);
			}
			$result = array( "data" => unserialize($data), "meta" => unserialize($meta), "timestamp" => $timestamp );
		}

		return $result;
	}

	function save( $tag, $data, $meta = "", $user = "" ) {
	
		if( !$tag ) {
			return false;
		}
	
		$result = false;

		$user = $this->escape( $user );
	
		if( $user != "" ) {
			$user = $user."/";
		}
	
		$tag = $this->escape( $tag );
		$data = serialize( $data );
		$meta = serialize( $meta );

		$path = $this->path.$user;
		if( $path != $this->path ) {
			// user folder maken
			if( !file_exists($path) ) {
				if( !@mkdir($path, 0770) ) {
					// abort abort abort!
					return false;
				}
			}
		}
		$path = $this->path.$user.$tag;

		$fp = fopen( $path, "wb");
		$result=fwrite($fp, $data);
		fclose($fp);
		if( $result ) { 
			$metapath = $path.".meta";     
			$fp = fopen( $metapath, "wb" );
			$result = fwrite($fp, $meta);
			fclose($fp);
		}
		if( $result ) {
			$result = array( "data" => unserialize($data), "meta" => unserialize($meta), "timestamp" => filectime($path) );
		}	
		return $result;
	}
	
	function clear( $tag, $user = "" ) {

		if( !$tag ) {
			return false;
		}
		$tag = $this->escape( $tag );

		$user = $this->escape( $user );
	
		if( $user != "" ) {
			$user = $user."/";
		}

		$path = $this->path.$user.$tag;
		if( file_exists($path) ) {
			$result = unlink($path);
			$metapath = $path.".meta";
			if( file_exists($metapath) ) {
				unlink($metapath);
			}
		}

		return $result;
	}

	function escape($path) {
		// This function will return an escaped path. All the characters not supported by Ariadne will be encoded.
		// See also path_escape_callback

		// Returns an empty string if no path, or an empty path was given.
		$result = "";
		if ($path) {
			$result = preg_replace_callback(
				'/[^A-Za-z0-9-]/', 
				create_function(
					// Replaces characters in the path with their number. 
					// Quite similar to " " -> "%20" for HTML escape, but we use _ instead of %
					// This function is to be used as a callback for preg_replace_callback
					'$char',
					'if ($char[0]) {'.
					'	if ($char[0]=="_") {'.
					'		return "__"; '.
					'	} else {'.
					'		return "_".dechex(ord($char[0]));'.
					'	}'.
					'}'
				),
				$path
			);
		}
		return $result;
	}

	function unescape($path) {
		$result = "";
		if ($path) {
			$result = preg_replace_callback(
				'/(_[0-9a-fA-F][0-9a-fA-F]|__)/', 
				create_function(
					'$matches', 
					// Two types of escaped characters can be here, the
					// underscore or other characters. Check for the
					// underscore first.

					'$char = $matches[0];'.
					'if ($char[1] == "_") {'.
					// It is the underscore, return it as a character.
					'	return "_";'.
					'}'.

					// Assume it is an escaped character here. Find the
					// numbers in hex, turn them back to decimal, get
					// the corresponding character and return it.

					'return chr(hexdec(substr($char, 1, 2)));'
				),
				$path
			);
		}
		return $result;
	}
}
