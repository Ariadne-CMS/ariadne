<?php
/*
 *    changed : 10. oct. 03
 *    author  : troels@kyberfabrikken.dk
 *    additional : Martin B. Vestergaard, Adrian Cope
 *    download: http://www.phpclasses.org/browse.html/package/1020.html
 *
 *    description :
 *        a script aimed at cleaning up after mshtml. use it in your wysiwyg html-editor,
 *        to strip messy code resulting from a copy-paste from word.
 *        this script doesnt come anything near htmltidy, but its pure php. if you have
 *        access to install binaries on your server, you might want to try using htmltidy.
 *    note :
 *        you might want to allow fonttags or even style tags. in that case, modify the
 *        function htmlcleaner::cleanup()
 *    usage :
 *        $body = htmlcleaner::cleanup($_POST['htmlCode']);
 *
 *    disclaimer :
 *        this piece of code is freely usable by anyone. if it makes your life better,
 *        remember me in your eveningprayer. if it makes your life worse, try doing it any
 *        better yourself.
 *
 *    todo/bugs :
 *        the script seems to remove textnodes in the root area. (eg. with no enclosing tags)
 */
define ('HTML_CLEANER_NODE_CLOSINGSTYLE_NORMAL',0);
define ('HTML_CLEANER_NODE_CLOSINGSTYLE_NONE',1);
define ('HTML_CLEANER_NODE_CLOSINGSTYLE_XHTMLSINGLE',2);
define ('HTML_CLEANER_NODE_CLOSINGSTYLE_HTMLSINGLE',3);
define ('HTML_CLEANER_NODE_NODETYPE_NODE',0);
define ('HTML_CLEANER_NODE_NODETYPE_CLOSINGNODE',1);
define ('HTML_CLEANER_NODE_NODETYPE_TEXT',2);
define ('HTML_CLEANER_NODE_NODETYPE_SPECIAL',3);
class htmlcleanertag {
	public $nodeType;
	public $nodeName;
	public $nodeValue;
	public $attributes;
	public $closingStyle;

	public function __construct($str)
	{
		if ($str[0]=='<') {
			$this->nodeType = HTML_CLEANER_NODE_NODETYPE_NODE;
		} else {
			$this->nodeType = HTML_CLEANER_NODE_NODETYPE_TEXT;
		}

		if ((strlen($str)>1) && ($str[1]=='?' || $str[1]=='!')) {
			$this->nodeType = HTML_CLEANER_NODE_NODETYPE_SPECIAL;
		}

		if ($this->nodeType==HTML_CLEANER_NODE_NODETYPE_NODE) {
			$this->parseFromString($str);
		} else if ($this->nodeType==HTML_CLEANER_NODE_NODETYPE_TEXT || $this->nodeType==HTML_CLEANER_NODE_NODETYPE_SPECIAL) {
			$this->nodeValue = $str;
		}
	}

	function parseFromString($str)
	{
		$str = str_replace("\n"," ", $str);
		$offset=1;
		$endset=strlen($str)-2;
		if ($str[0]!='<' || $str[strlen($str)-1]!='>'){
			trigger_error('tag syntax error', E_USER_ERROR);
		}
		if ($str[strlen($str)-2]=='/') {
			$endset = $endset-1;
			$this->closingStyle = HTML_CLEANER_NODE_CLOSINGSTYLE_XHTMLSINGLE;
		}
		if ($str[1]=='/') {
			$offset=2;
			$this->nodeType = HTML_CLEANER_NODE_NODETYPE_CLOSINGNODE;
		}
		for ($tagname = '';preg_match("/([a-zA-Z0-9:]{1})/",$str[$offset]);$offset++) {
			$tagname .= $str[$offset];
		}
		for ($tagattr = '';$offset<=$endset;$offset++){
			$tagattr .= $str[$offset];
		}
		$this->nodeName = strtolower($tagname);
		$this->attributes = $this->parseAttributes($tagattr);
	}

	function parseAttributes($str)
	{
		$i=0;
		$return = array();
		$_state = -1;
		$_value = '';

		while ($i<strlen($str)) {
			$chr = $str[$i];

			if ($_state == -1) {		// reset buffers
				$_name = '';
				$_quote = '';
				$_value = '';
				$_state = 0;		// parse from here
			}
			if ($_state == 0) {		// state 0 : looking for name
				if (preg_match("/([a-zA-Z]{1})/",$chr)) {
					$_name = $chr;
					$_state = 1;
				}
			} else if ($_state == 1) {	// state 1 : looking for equal
				if (preg_match("/([a-zA-Z0-9_:.-]{1})/",$chr)) {
					$_name .= $chr;
				} else if ($chr == '=') {
					$_state = 3;
				} else {
					$_state = 2;
				}
			} else if ($_state == 2) { // state 2: looking for equal
				if ($chr != ' ' && $chr != "\t" && $chr != "\n") {
					if ($chr == '=') {
						$_state = 3;
					} else {
						// end of attribute
						$return[] = $_name;
						$_state = -1;
						continue; // Don't up the counter, this char is the first char for the next attribute.
					}
				}
			} else if ($_state == 3) {	// state 3 : looking for quote
				if (preg_match("/([\'\"]{1})/",$chr)) {
					$_quote = $chr;
					$_value = '';
					$_state = 4;
				} else if (preg_match("/\\s{1}/",$chr)) {
					$_state = 3;
				} else {
					$_quote = '';
					$_value = $chr;
					$_state = 4;
				}
			} else if ($_state == 4) {	// state 4 : looking for endquote
				if ($_quote != "") {
					if ($chr == $_quote) {
						// end of attribute
						$return[strtolower($_name)] = $_value;
						$_state = -1;
					} else {
						$_value .= $chr;
					}
				} else {
					// Unquoted attributes end when there is a space char.
					if (preg_match("/\\s{1}/", $chr)) {
						$return[strtolower($_name)] = $_value;
						$_state = -1;
					} else {
						$_value .= $chr;
					}

/*
					if (preg_match("/([a-zA-Z0-9\.\,\_\-\/\#\@\%]{1})/",$chr)) {
						$_value .= $chr;
					} else {
						// end of attribute
						$return[strtolower($_name)] = $_value;
						$_state = -1;
					}
*/
				}
			}
			$i++;
		}
		if ($_value!='') {
			$return[strtolower($_name)] = $_value;
		} else if ($_name!='') {
			$return[] = $_name;
		}

		return $return;
	}

	public function _toString() {
		return $this->toString();
	}

	public function toString()
	{
		if ( ($this->nodeName == 'link' ||
			$this->nodeName == 'img' ||
			$this->nodeName == 'br' ||
			$this->nodeName == 'hr')
			&& $this->closingStyle != HTML_CLEANER_NODE_CLOSINGSTYLE_XHTMLSINGLE
		) {
			$this->closingStyle = HTML_CLEANER_NODE_CLOSINGSTYLE_HTMLSINGLE;
		}
		if ($this->nodeType == HTML_CLEANER_NODE_NODETYPE_TEXT || $this->nodeType == HTML_CLEANER_NODE_NODETYPE_SPECIAL) {
			return $this->nodeValue;
		}
		if ($this->nodeType == HTML_CLEANER_NODE_NODETYPE_NODE) {
			$str = '<'.$this->nodeName;
		} else if ($this->nodeType == HTML_CLEANER_NODE_NODETYPE_CLOSINGNODE) {
			return '</'.$this->nodeName.">";
		}
		foreach ($this->attributes as $attkey => $attvalue) {
			if (is_numeric($attkey)) {
				$str .= ' '.$attvalue;
			} else {
				$str .= ' '.$attkey."=\"".$attvalue."\"";
			}
		}
		if ($this->closingStyle == HTML_CLEANER_NODE_CLOSINGSTYLE_XHTMLSINGLE) {
			$str .= ' />';
		} else {
			$str .= '>';
		}
//		if ($this->nodeName != "td")
//			$str .= "\n";
		return $str;
	}

}

class htmlcleaner
{
	public function version()
	{
		return 'mshtml cleanup v.0.9.2 by troels@kyberfabrikken.dk';
	}

	public function dessicate($str)
	{
		$i=0;
		$parts = array();
		$_state = -1;
		$str_len = strlen($str);
		while ($i<$str_len) {
			$chr = $str[$i];
			if ($_state == -1) {	// reset buffers
				$_buffer = '';
				$_state = 0;
			}
			if ($_state == 0) {	// state 0 : looking for <
				if ($chr == '<') {
					// start buffering
					if ($_buffer!='') {
						// store part
						array_push($parts,new htmlcleanertag($_buffer));
					}
					$_buffer = '<';
					if (($i+3 < $str_len) && $str[$i+1] == '!' && $str[$i+2] == '-' && $str[$i+3] == '-') {
						// comment
						$_state = 2;
					} else {
						$_state = 1;
					}
				} else {
					$_buffer .= $chr;
				}
			} else if ($_state == 1) {	// state 1 : in tag looking for >
				$_buffer .= $chr;
				if ($chr == '"' || $chr == "'") {
					$_quote = $chr;
					$_state = 3;
				} else if ($chr == '>') {
					array_push($parts,new htmlcleanertag($_buffer));
					$_state = -1;
				}
			} else if ($_state == 2) {	// state 2 : in comment looking for -->
				$_buffer .= $chr;
				if ($str[$i-2] == '-' && $str[$i-1] == '-' && $str[$i] == '>') {
					array_push($parts,new htmlcleanertag($_buffer)); // Remove this line to make the cleaner leave out HTML comments from the parts.
					$_state = -1;
				}
			} else if ($_state == 3) {
				$_buffer .= $chr;
				if ($chr == $_quote || $chr == '') {
					$_state = 1;
				}
			}
			$i++;
		}
		return $parts;
	}


	// removes the worst mess from word.
	public function cleanup($body, $config)
	{

		$scriptParts = array();
		$scriptPart  = false;
		do {
			$scriptPartKey = "";
			if (preg_match('!<script[^>]*>(.|[\r\n])*?</[^>]*script[^>]*>!i', $body, $matches)) {
				do {
					$scriptPartKey = '----'.md5(rand()).'----';
				} while (strpos($body, $scriptPartKey) !== false);
				$body = str_replace($matches[0], $scriptPartKey, $body);
				$scriptParts[$scriptPartKey] = $matches[0];
			}
		} while($scriptPartKey);

		$body = "<htmlcleaner>$body</htmlcleaner>";
		$rewrite_rules = $config["rewrite"];
		$return = '';
		$parts = htmlcleaner::dessicate($body);

		// flip emtied rules so we can use it as indexes
		if (is_array($config["delete_emptied"])) {
			$config["delete_emptied"] = array_flip($config["delete_emptied"]);
		}
		if (is_array($config["delete_empty_containers"])) {
			$config["delete_empty_containers"] = array_flip($config["delete_empty_containers"]);
		}
		$delete_stack = Array();
		$skipNodes = 0;
		foreach ($parts as $i => $part) {
			if ($skipNodes > 0) {
				$skipNodes--;
				continue;
			}
			if ($part->nodeType == HTML_CLEANER_NODE_CLOSINGSTYLE_NONE) {
				if (isset($config["delete_emptied"][$part->nodeName])
						&& count($delete_stack)) {
					do {
						$closed = array_pop($delete_stack);
					} while ($closed["tag"] && $closed["tag"] != $part->nodeName);
					if ($closed["delete"]) {
						unset($part);
					}
				}
			} else
			if ($part->nodeType == HTML_CLEANER_NODE_NODETYPE_NODE) {
				if (isset($config["delete_emptied"][$part->nodeName])
					&& count($delete_stack)) {
						array_push($delete_stack, Array("tag" => $part->nodeName));
				} else if (isset($config["delete_empty_containers"][$part->nodeName])) {
					if ($part->nodeName != 'a' || !$part->attributes['name']) {	// named anchor objects are not containers
						if ($parts[$i+1] && $parts[$i+1]->nodeName == $part->nodeName && $parts[$i+1]->nodeType == HTML_CLEANER_NODE_NODETYPE_CLOSINGNODE) {
							$skipNodes = 1;
							continue;
						}
					}
				}
			}

			if ($part && is_array($rewrite_rules)) {
				foreach ($rewrite_rules as $tag_rule=>$attrib_rules) {
					$escaped_rule = str_replace('/','\/',$tag_rule);
					if (preg_match('/'.$escaped_rule.'/is', $part->nodeName)) {
						if (is_array($attrib_rules) && is_array($part->attributes)) {
							foreach ($attrib_rules as $attrib_rule=>$value_rules) {
								foreach ($part->attributes as $attrib_key=>$attrib_val) {
									$escaped_rule = str_replace('/','\/',$attrib_rule);
									if (preg_match('/'.$escaped_rule.'/is', $attrib_key)) {
										if (is_array($value_rules)) {
											foreach ($value_rules as $value_rule=>$value) {
												$escaped_rule = str_replace('/','\/',$value_rule);
												if (preg_match('/'.$escaped_rule.'/is', $attrib_val)) {
													if ($value === false) {
														unset($part->attributes[$attrib_key]);
														if (!count($part->attributes)) {
															if (isset($config["delete_emptied"][$part->nodeName])) {
																// remove previous config
																@array_pop($delete_stack);
																array_push($delete_stack, Array("tag" => $part->nodeName, "delete" => true));
																unset($part);
															}
															break 3;
														}
													} else {
														$escaped_rule = str_replace('/','\/',$value_rule);
														$part->attributes[$attrib_key] = preg_replace('/^'.$escaped_rule.'$/is', $value, $part->attributes[$attrib_key]);
													}
												}
											}
										} else
										if ($value_rules === false) {
											unset($part->attributes[$attrib_key]);
											if (!count($part->attributes)) {
												if (isset($config["delete_emptied"][$part->nodeName])) {
													// remove previous config
													@array_pop($delete_stack);
													array_push($delete_stack, Array("tag" => $part->nodeName, "delete" => true));
													unset($part);
												}
												break 2;
											}
										} else {
											$escaped_rule = str_replace('/','\/',$attrib_rule);
											$part->attributes[preg_replace('/^'.$escaped_rule.'$/is', $value_rules, $attrib_key)] = $part->attributes[$attrib_key];
											unset($part->attributes[$attrib_key]);
										}
									}
								}
							}
						} else if ($attrib_rules === false) {
							unset($part);
						} else {
							$part->nodeName = $attrib_rules;
						}
						break; // tag matched, so skip next rules.
					}
				}
			}
			if ($part && strstr($part->nodeValue,'<?xml:namespace')===false) {
				$return .= $part->toString();
			}
		}

		foreach ($scriptParts as $key => $value) {
			$return = str_replace($key, $value, $return);
		}
		//FIXME: htmlcleaner removes the '<' in '</htmlcleaner>' if the html code is broken
		// ie: if the last tag in the input isn't properly closed... it should instead
		// close any broken tag properly (add quotes and a '>')

		return str_replace('<htmlcleaner>', '', str_replace('</htmlcleaner>', '', $return));
	}
}

class pinp_htmlcleaner extends htmlcleaner {

	function _dessicate($str) {
		return parent::dessicate($str);
	}
	function _cleanup($str,$config) {
		return parent::cleanup($str,$config);
	}

}
