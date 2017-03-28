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
	public $attributes = array();
	public $closingStyle;

	public function __construct($str)
	{
		if ($str[0]=='<') {
			$this->nodeType = HTML_CLEANER_NODE_NODETYPE_NODE;
			if (isset($str[1]) && ($str[1]=='?' || $str[1]=='!')) {
				$this->nodeType = HTML_CLEANER_NODE_NODETYPE_SPECIAL;
				$this->nodeValue = $str;
			} else {
				$this->parseFromString($str);
			}
		} else {
			$this->nodeType = HTML_CLEANER_NODE_NODETYPE_TEXT;
			$this->nodeValue = $str;
		}

	}

	function parseFromString($str)
	{
		$str = str_replace("\n"," ", $str);
		$offset=1;
		$endset=strlen($str)-2;
		if ($str[0] != '<' || $str[$endset+1] !== '>'){
			trigger_error('tag syntax error', E_USER_ERROR);
		}
		if ($str[$endset]=='/') {
			$endset--;
			$this->closingStyle = HTML_CLEANER_NODE_CLOSINGSTYLE_XHTMLSINGLE;
		}
		if ($str[1]=='/') {
			$offset=2;
			$this->nodeType = HTML_CLEANER_NODE_NODETYPE_CLOSINGNODE;
		}

		preg_match("|</?([a-zA-Z0-9:-]+)|",$str,$matches);
		$tagname = $matches[1];
		$offset += strlen($tagname);

		$tagattr = substr($str,$offset,$endset-$offset+1);

		$this->nodeName = strtolower($tagname);
		$this->attributes = $this->parseAttributes($tagattr);
	}

	function parseAttributes($str)
	{
		$str = trim($str);
		if(strlen($str) == 0) {
			return array();
		}

		//echo "{{".$str."}}\n";
		$i=0;
		$return = array();
		$_state = -1;
		$_name = '';
		$_quote = '';
		$_value = '';
		$strlen = strlen($str);

		while ($i<$strlen) {
			$chr = $str[$i];

			if ($_state == -1) {		// reset buffers
				$_name = '';
				$_quote = '';
				$_value = '';
				$_state = 0;		// parse from here
			}
			if ($_state == 0) {		// state 0 : looking for name
				if (ctype_space($chr)) { // whitespace, NEXT
					$i++;
					continue;
				}
				preg_match("/([a-zA-Z][a-zA-Z0-9_:.-]*)/",$str,$matches,0,$i);

				$_name = $matches[1];
				$i += strlen($_name);
				$chr = $str[$i];

				if ($chr == '=') {
					$_state = 3;
				} else {
					$_state = 2;
				}
			} else if ($_state == 2) { // state 2: looking for equal
				if (!ctype_space($chr)) {
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
				if ($chr == '"' || $chr == "'" ) {
					// fastforward til next quot
					$regexp = '|^'.$chr.'(.*?)'.$chr.'|';
					$skip = 1;
				} else if (!ctype_space($chr)) {
					// fastforward til next space
					$regexp = '|^(.*?) ?|';
					$skip = 0;
				}

				preg_match($regexp,substr($str,$i),$matches);
				$_value = $matches[1];
				$i += strlen($_value) + $skip ;

				$return[strtolower($_name)] = $_value;
				$_state = -1;

			}
			$i++;
		}
		if($_state != -1 ) {
			if ($_value!='') {
				$return[strtolower($_name)] = $_value;
			} else if ($_name!='') {
				$return[] = $_name;
			}
		}

		return $return;
	}

	public function _toString() {
		return $this->toString();
	}

	public function toString()
	{
		$src = '';
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
				$str .= ' '.$attkey.'="'.str_replace('"','&quot;',$attvalue).'"';
			}
		}
		if ($this->closingStyle == HTML_CLEANER_NODE_CLOSINGSTYLE_XHTMLSINGLE) {
			$str .= ' />';
		} else {
			$str .= '>';
		}
		return $str;
	}

}

class htmlcleaner
{
	public static function version()
	{
		return 'mshtml cleanup v.0.9.2 by troels@kyberfabrikken.dk';
	}

	public static function dessicate($str)
	{
		$i=0;
		$parts = array();
		$_state = 0;
		$_buffer = '';
		$_quote = '';
		$str_len = strlen($str);
		while ($i<$str_len) {
			$chr = $str[$i];
			if ($_state == -1) {	// reset buffers
				$_buffer = '';
				$_quote = '';
				$_state = 0;
			}
			if ($_state == 0) {	// state 0 : looking for <
				$pos = strpos($str,'<',$i);
				if( $pos === false) {
					// no more
					$_buffer = substr($str,$i);
					$i = $str_len;
				} else if($str[$pos] === '<') {
					$chr = '<';
					$_buffer = substr($str,$i,$pos-$i);
					if ($_buffer!='') {
						// store part
						array_push($parts,new htmlcleanertag($_buffer));
					}
					$_buffer = '<';
					$i = $pos;
					if (($i+3 < $str_len) && $str[$i+1] == '!' && $str[$i+2] == '-' && $str[$i+3] == '-') {

						// cheating, fast forward to end of comment
						$end = strpos($str,'-->',$i+3); // start looking 3 steps ahead
						if($end !== false) {
							$comment = substr($str,$i,$end-$i+3);
							array_push($parts,new htmlcleanertag($comment)); // Remove this line to make the cleaner leave out HTML comments from the parts.
							$_state = -1;
							$i = $end+2;
						} else {
							$_buffer = substr($str,$i);
							$i = $str_len;
						}
					} else {
						$_state = 1;
					}
				}
			} else if ($_state == 1) {	// state 1 : in tag looking for >
				$_buffer .= $chr;
				if ($chr == '"' || $chr == "'") {

					$regexp = '|'.$chr.'(.*?)'.$chr.'|sm';
					preg_match($regexp,$str,$matches,0,$i);

					$_buffer .= $matches[1] . $chr;
					$i += strlen($matches[1]) + 1 ;
				} else if ($chr == '>') {
					array_push($parts,new htmlcleanertag($_buffer));
					$_state = -1;
				}
			}
			$i++;
		}
		return $parts;
	}


	// removes the worst mess from word.
	public static function cleanup($body, $config)
	{

		$scriptParts = array();

		do {
			$prefix = md5(rand());
		} while (strpos($body, $prefix) !== false);

		$callback = function($matches) use ($prefix, &$scriptParts) {
			$scriptPartKey = '----'.$prefix . '-' . count($scriptParts).'----';
			$scriptParts[$scriptPartKey] = $matches[0];
			return $scriptPartKey;
		};

		$newbody = preg_replace_callback('!<script[^>]*>(.|[\r\n])*?</[^>]*script[^>]*>!i', $callback, $body);

		if($newbody) {
			$body = $newbody;
		}

		$body = "<htmlcleaner>$body</htmlcleaner>";
		$rewrite_rules = $config["rewrite"];
		$return = '';
		$parts = htmlcleaner::dessicate($body);

		// flip emtied rules so we can use it as indexes
		if (is_array($config["delete_emptied"])) {
			$config["delete_emptied"] = array_flip($config["delete_emptied"]);
		}
		if (isset($config["delete_empty_containers"]) && is_array($config["delete_empty_containers"])) {
			$config["delete_empty_containers"] = array_flip($config["delete_empty_containers"]);
		}
		$delete_stack = Array();
		$skipNodes = 0;
		if(is_array($rewrite_rules)) {
			foreach ($rewrite_rules as $tag_rule=> $attrib_rules) {
				$escaped_rule = str_replace('/','\/',$tag_rule);
				if($tag_rule !== $escaped_rule) {
					$rewrite_rules[$escaped_rule] = $attrib_rules;
					unset($rewrite_rules[$tag_rule]);
					$tag_rule = $escaped_rule;
				}

				if (is_array($attrib_rules)) {
					foreach ($attrib_rules as $attrib_rule=> $value_rules) {
						$escaped_rule = str_replace('/','\/',$attrib_rule);
						if ($attrib_rule !== $escaped_rule) {
							$rewrite_rules[$tag_rule][$escaped_rule] = $value_rules;
							unset($rewrite_rules[$tag_rule][$attrib_rule]);
							$attrib_rule = $escaped_rule;
						}

						if (is_array($value_rules)) {
							foreach ($value_rules as $value_rule=>$value) {
								$escaped_rule = str_replace('/','\/',$value_rule);
								if ($value_rule !== $escaped_rule) {
									$rewrite_rules[$tag_rule][$attrib_rule][$escaped_rule] = $value;
									unset($rewrite_rules[$tag_rule][$attrib_rule][$value_rule]);
								}
							}
						} 
					}
				}
			}
		}

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
						if (isset($parts[$i+1]) && $parts[$i+1]->nodeName == $part->nodeName && $parts[$i+1]->nodeType == HTML_CLEANER_NODE_NODETYPE_CLOSINGNODE) {
							$skipNodes = 1;
							continue;
						}
					}
				}
			}


			if ($part && is_array($rewrite_rules)) {
				foreach ($rewrite_rules as $tag_rule=>$attrib_rules) {
					if (preg_match('/'.$tag_rule.'/is', $part->nodeName)) {
						if (is_array($attrib_rules)) {
							foreach ($attrib_rules as $attrib_rule=>$value_rules) {
								foreach ($part->attributes as $attrib_key=>$attrib_val) {
									if (preg_match('/'.$attrib_rule.'/is', $attrib_key)) {
										if (is_array($value_rules)) {
											foreach ($value_rules as $value_rule=>$value) {
												if (preg_match('/'.$value_rule.'/is', $attrib_val)) {
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
														$part->attributes[$attrib_key] = preg_replace('/^'.$value_rule.'$/is', $value, $part->attributes[$attrib_key]);
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
											$part->attributes[preg_replace('/^'.$attrib_rule.'$/is', $value_rules, $attrib_key)] = $part->attributes[$attrib_key];
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

		$return = str_replace(array_keys($scriptParts), array_values($scriptParts), $return);

		//FIXME: htmlcleaner removes the '<' in '</htmlcleaner>' if the html code is broken
		// ie: if the last tag in the input isn't properly closed... it should instead
		// close any broken tag properly (add quotes and a '>')

		return str_replace('<htmlcleaner>', '', str_replace('</htmlcleaner>', '', $return));
	}
}

class pinp_htmlcleaner extends htmlcleaner {

	public static function _dessicate($str) {
		return parent::dessicate($str);
	}
	public static function _cleanup($str,$config) {
		return parent::cleanup($str,$config);
	}

}
