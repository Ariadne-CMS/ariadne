<?php
	define('AR_HTMLPARSER_T_OPEN_TAG', 252);
	define('AR_HTMLPARSER_T_CLOSE_TAG', 251);
	define('AR_HTMLPARSER_T_TAG_END', 250);
	define('AR_HTMLPARSER_T_ASSIGN', 248);
	define('AR_HTMLPARSER_T_IDENT', 247);
	define('AR_HTMLPARSER_T_STRING', 246);
	define('AR_HTMLPARSER_T_ATTRIB', 245);
	define('AR_HTMLPARSER_T_NUMBER', 244);
	define('AR_HTMLPARSER_T_ATTRIB_VAL', 243);
	define('AR_HTMLPARSER_T_DOCTYPE', 242);

	define('AR_HTMLPARSER_T_EOF', 0);
	define('AR_HTMLPARSER_T_TEXT', -1);
	define('AR_HTMLPARSER_T_ERROR', -2);

	define('AR_HTMLPARSER_STATE_TEXT', 1);
	define('AR_HTMLPARSER_STATE_OPEN_TAG', 2);
	define('AR_HTMLPARSER_STATE_CLOSE_TAG', 3);
	define('AR_HTMLPARSER_STATE_ATTRIB', 4);
	define('AR_HTMLPARSER_STATE_COMMENT', 5);
	define('AR_HTMLPARSER_STATE_SCRIPT', 6);
	define('AR_HTMLPARSER_STATE_DOCTYPE', 7);

	define('CONTEXT_NORMAL', 1);
	define('CONTEXT_SCRIPT', 2);

	class htmlparser {

		protected static function scanner($buffer) {
			$scanner = array();
			$scanner['YYBUFFER'] = $buffer."\000";
			$scanner['YYCURSOR'] = 0;
			$scanner['YYSTATE'] = AR_HTMLPARSER_STATE_TEXT;
			$scanner['YYCONTEXT'] = CONTEXT_NORMAL;

			$class_ident_start = array();
			$start = ord('a');
			$end   = ord('z');
			for ($i = $start; $i <= $end; $i++) {
				$class_ident_start[chr($i)] = chr($i);
				$class_ident_start[strtoupper(chr($i))] = strtoupper(chr($i));
			}
			$scanner['class_ident_start'] = $class_ident_start;

			$class_attrib_start = $class_ident_start;
			$scanner['class_attrib_start'] = $class_attrib_start;


			$class_ident_next = $class_ident_start;
			$class_number = array();
			$start = ord('0');
			$end   = ord('9');
			for ($i = $start; $i <= $end; $i++) {
				$class_ident_next[chr($i)] = chr($i);
				$class_number[chr($i)] = chr($i);
			}
			$scanner['class_ident_next'] = $class_ident_next;
			$scanner['class_number'] = $class_number;

			// List of allowed characters for attribute names;
			$class_attrib_next = $class_ident_next;
			$class_attrib_next[':'] = ':';
			$class_attrib_next['-'] = '-';
			$class_attrib_next['_'] = '_';
			$class_attrib_next['.'] = '.';

			$scanner['class_attrib_next'] = $class_attrib_next;


			$class_whitespace = array(" " => " ", "\t" => "\t", "\r" => "\r", "\n" => "\n");
			$scanner['class_whitespace'] = $class_whitespace;

			return $scanner;
		}

		protected static function scan(&$scanner, &$value) {
			$YYCURSOR = &$scanner["YYCURSOR"];
			$YYBUFFER = &$scanner["YYBUFFER"];
			$yych = $YYBUFFER[$YYCURSOR];
			$YYSTATE = &$scanner["YYSTATE"];
			$YYCONTEXT = &$scanner["YYCONTEXT"];

			do {
				switch (true) {
					case $yych === $scanner['class_attrib_start'][$yych] && ($YYSTATE == AR_HTMLPARSER_STATE_DOCTYPE):
						$value = $yych;
						while ($scanner['class_attrib_next'][$yych = $YYBUFFER[++$YYCURSOR]] == $yych) {
							$value .= $yych;
						}
						return AR_HTMLPARSER_T_ATTRIB_VAL;
					break;
					case $yych === '"' && ($YYSTATE == AR_HTMLPARSER_STATE_DOCTYPE):
						$yych = $yych = $YYBUFFER[++$YYCURSOR];
						while ($yych !== "\000" && $yych !== '"') {
							$value .= $yych;
							$yych = $yych = $YYBUFFER[++$YYCURSOR];
						}
						$value = '"'.$value.'"';
						$yych = $YYBUFFER[++$YYCURSOR];
						return AR_HTMLPARSER_T_ATTRIB_VAL;
					break;
					case $yych === '"' && ($YYSTATE == AR_HTMLPARSER_STATE_ATTRIB):
					case $yych === "'" && ($YYSTATE == AR_HTMLPARSER_STATE_ATTRIB):
						$YYSTATE = AR_HTMLPARSER_STATE_OPEN_TAG;

						$quote = $yych;
						$yych = $yych = $YYBUFFER[++$YYCURSOR];
						while ($yych !== "\000" && $yych !== $quote) {
							$value .= $yych;
							$yych = $yych = $YYBUFFER[++$YYCURSOR];
						}
						$yych = $YYBUFFER[++$YYCURSOR];
						return AR_HTMLPARSER_T_ATTRIB_VAL;
					break;
					case $yych === '=' && ($YYSTATE == AR_HTMLPARSER_STATE_OPEN_TAG):
						while ($scanner['class_whitespace'][$yych = $YYBUFFER[++$YYCURSOR]] == $yych);
						$YYSTATE = AR_HTMLPARSER_STATE_ATTRIB;
						contine;
					break;
					case $yych === $scanner['class_whitespace'][$yych] && (($YYSTATE == AR_HTMLPARSER_STATE_OPEN_TAG) || ($YYSTATE == AR_HTMLPARSER_STATE_CLOSE_TAG) || ($YYSTATE == AR_HTMLPARSER_STATE_DOCTYPE)):
						$yych = $YYBUFFER[++$YYCURSOR]; continue;
					break;
					case $yych === '-' && ($YYSTATE == AR_HTMLPARSER_STATE_COMMENT):
						if (substr($YYBUFFER, $YYCURSOR, 3) == '-->') {
							$YYSTATE = AR_HTMLPARSER_STATE_TEXT;
							$value = "-->"; $YYCURSOR+=3;
							return AR_HTMLPARSER_T_TEXT;
						}
						$value = '-';
						$YYBUFFER[++$YYCURSOR];
						return AR_HTMLPARSER_T_TEXT;
					break;
					case ($YYSTATE == AR_HTMLPARSER_STATE_TEXT) && (substr_compare($YYBUFFER, '<!--', $YYCURSOR, 4) == 0 ):
							$value		= "<!--"; $YYCURSOR+=4;
							$YYSTATE	= AR_HTMLPARSER_STATE_COMMENT;
							return AR_HTMLPARSER_T_TEXT;
					break;
					case ($YYSTATE == AR_HTMLPARSER_STATE_SCRIPT) && ( substr_compare($YYBUFFER, '</script>', $YYCURSOR, 9, true) == 0 ):
						$YYCONTEXT = CONTEXT_NORMAL;
						// fallthrough
					case ($YYSTATE == AR_HTMLPARSER_STATE_TEXT) && substr($YYBUFFER, $YYCURSOR, 2) == '</':
						$YYSTATE	= AR_HTMLPARSER_STATE_CLOSE_TAG;
						$YYCURSOR	+= 1;
						$value		= "";
						while ($scanner['class_ident_next'][$yych = $YYBUFFER[++$YYCURSOR]] == $yych) {
							$value .= $yych;
						}
						return AR_HTMLPARSER_T_CLOSE_TAG;
					break;
					case ($YYSTATE == AR_HTMLPARSER_STATE_TEXT) && ( substr_compare($YYBUFFER, '<!doctype', $YYCURSOR, 9 , true) == 0 ):
						$YYSTATE	= AR_HTMLPARSER_STATE_DOCTYPE;
						$value		= substr($YYBUFFER, $YYCURSOR, 9/* strlen('<!doctype')*/);
						$YYCURSOR	+= 9 /*strlen('<!doctype')*/;
						return AR_HTMLPARSER_T_DOCTYPE;
					break;
					case $yych == '<' && ($YYSTATE == AR_HTMLPARSER_STATE_TEXT):
						$YYSTATE	= AR_HTMLPARSER_STATE_OPEN_TAG;
						$value		= "";
						while ($scanner['class_ident_next'][$yych = $YYBUFFER[++$YYCURSOR]] == $yych) {
							$value .= $yych;
						}
						if (strtolower($value) == 'script') {
							$YYCONTEXT = CONTEXT_SCRIPT;
						}
						return AR_HTMLPARSER_T_OPEN_TAG;
					break;
					case $yych === $scanner['class_attrib_start'][$yych] && ($YYSTATE == AR_HTMLPARSER_STATE_OPEN_TAG):
						$value = $yych;
						while ($scanner['class_attrib_next'][$yych = $YYBUFFER[++$YYCURSOR]] == $yych) {
							$value .= $yych;
						}
						return AR_HTMLPARSER_T_ATTRIB;
					break;
					case $yych === $scanner['class_number'][$yych] && ($YYSTATE == AR_HTMLPARSER_STATE_OPEN_TAG):
						$value = $yych;
						while ($scanner['class_number'][$yych = $YYBUFFER[++$YYCURSOR]] == $yych) {
							$value .= $yych;
						}
						return AR_HTMLPARSER_T_NUMBER;
					break;
					case $yych === '>' && (($YYSTATE == AR_HTMLPARSER_STATE_OPEN_TAG) || ($YYSTATE == AR_HTMLPARSER_STATE_CLOSE_TAG) || ($YYSTATE == AR_HTMLPARSER_STATE_DOCTYPE)):
						if ($YYCONTEXT == CONTEXT_SCRIPT) {
							$YYSTATE = AR_HTMLPARSER_STATE_SCRIPT;
						} else {
							$YYSTATE = AR_HTMLPARSER_STATE_TEXT;
						}
						$yych = $YYBUFFER[++$YYCURSOR]; continue;
					break;
					case ord($yych) === 0:
						$value = $yych;
						return AR_HTMLPARSER_T_EOF;
					break;
					case $yych === $yych && ($YYSTATE == AR_HTMLPARSER_STATE_ATTRIB):
						$YYSTATE = AR_HTMLPARSER_STATE_OPEN_TAG;
						$value = "";
						while ($scanner['class_whitespace'][$yych] !== $yych && ($yych != "\000" && $yych != ">")) {
							$value .= $yych;
							$yych = $YYBUFFER[++$YYCURSOR];
						}
						return AR_HTMLPARSER_T_ATTRIB_VAL;
					break;
					case $yych === $yych && ($YYSTATE == AR_HTMLPARSER_STATE_OPEN_TAG):
						$yych = $YYBUFFER[++$YYCURSOR]; continue;
					break;
					case ($YYSTATE == AR_HTMLPARSER_STATE_TEXT):
						$value = "";
						while ( $yych != '<'  && $yych != "\000" ) {
							$value .= $yych;
							$yych = $YYBUFFER[++$YYCURSOR];
						}
						return AR_HTMLPARSER_T_TEXT;
					break;
					default:
						$value = $yych;
						$YYCURSOR++;
						return AR_HTMLPARSER_T_TEXT;
				}
			} while (1);

		}

		protected static function nextToken(&$parser) {
			$scanner = &$parser['scanner'];

			$new_token = static::scan($scanner, $new_value);

			if (isset($parser["token_ahead"])) {
				$parser["token"] = $parser["token_ahead"];
				$parser["token_value"] = $parser["token_ahead_value"];
			}
			$parser["token_ahead"] = $new_token;
			$parser["token_ahead_value"] = $new_value;
		}


		protected static function parse_Text(&$parser) {
			$result = "";
			while ($parser["token_ahead"] == AR_HTMLPARSER_T_TEXT) {
				static::nextToken($parser);
				$result .= $parser["token_value"];
			}
			return $result;
		}

		protected static function parse_Tag_Open(&$parser) {
			$singles = array(
				'br', 'img', 'area', 'link', 'param', 'hr', 'base', 'meta',
				'input', 'col'
			);

			$result = array('type' => 'tag');
			$tagName = $parser["token_ahead_value"];
			if (in_array(strtolower($tagName), $singles)) {
				$result['type'] = 'tagSingle';
			}
			$result['tagName'] = $tagName;
			static::nextToken($parser);
			while ($parser["token_ahead"] == AR_HTMLPARSER_T_ATTRIB) {
				static::nextToken($parser);
				$attrib = $parser["token_value"];
				$attrib_value = false;
				if ($parser["token_ahead"] == AR_HTMLPARSER_T_ATTRIB_VAL) {
					static::nextToken($parser);
					$attrib_value = $parser["token_value"];
				}
				$result['attribs'][$attrib] = $attrib_value;
			}

			return $result;
		}

		protected static function parse_Node(&$parser, &$stack) {
			$siblings = array('table', 'tr', 'td', 'li', 'ul');

			if (count($stack)) {
				$parentNode	= &$stack[count($stack)-1];
				$tagName	= strtolower($parentNode['tagName']);
			}
			$result = array();
			while ($parser["token_ahead"] != AR_HTMLPARSER_T_EOF) {
				switch ($parser["token_ahead"]) {
					case AR_HTMLPARSER_T_TEXT:
						$node = array('type' => 'text');
						$node['html'] = static::parse_Text($parser);
						$result[] = $node;
					break;
					case AR_HTMLPARSER_T_DOCTYPE:
						$node = array('type' => 'doctype');
						static::nextToken($parser);
						while ($parser["token_ahead"] == AR_HTMLPARSER_T_ATTRIB_VAL) {
							static::nextToken($parser);
							$attrib_value = $parser["token_value"];
							$node['attribs'][] = $attrib_value;
						}
						$result[] = $node;
					break;
					case AR_HTMLPARSER_T_OPEN_TAG:
						$nextTag = strtolower($parser["token_ahead_value"]);
						if ($nextTag == $tagName && in_array($tagName, $siblings)) {
							if ($parser['options']['noTagResolving']) {
								$parentNode['htmlTagClose'] = "";
							}
							return $result;
						}

						$node = static::parse_Tag_Open($parser);
						if ($node) {
							if ($node['type'] !== 'tagSingle') {
								$stack[] = &$node;
								$node['children'] = static::parse_Node($parser, $stack);
								array_pop($stack);
								end($stack);
							}
							$result[] = $node;
						}
					break;
					case AR_HTMLPARSER_T_CLOSE_TAG:
						$closeTag = $parser["token_ahead_value"];
						if ($tagName != strtolower($closeTag)) {
							// continue parsing because closing tag does not match current tag
							// FIXME: create a better check
							static::nextToken($parser);

							// if we do not resolve tags, we have to add this one as text
							if ($parser['options']['noTagResolving']) {
								$node = array('type' => 'text');
								$node['html'] = "</".$parser["token_value"].">";
								$result[] = $node;
							}
							continue;
						}

						// if we do not resolve tags, we have to add this one as text
						if ($parser['options']['noTagResolving']) {
							$parentNode['htmlTagClose'] = "</".$parser['token_ahead_value'].">";
						}

						static::nextToken($parser);
						return $result;
					default:
						static::nextToken($parser);
				}
			}
			if ($parser['options']['noTagResolving']) {
				$parentNode['htmlTagClose'] = "";
			}
			return $result;
		}

		protected static function compile_Attribs(&$node) {
			$result = "";
			if (is_array($node['attribs'])) {
				foreach ($node['attribs'] as $key => $value) {
					$result .= " $key";
					if ($value !== false) {
						$result .= "=\"".str_replace('"', '\"', $value)."\"";
					}
				}
			}
			return $result;
		}

		public static function compile($node) {
			$result = "";
			switch ($node['type']) {
				case 'tag':
				case 'tagSingle':
					if ($node['tagName']) {
						$result .= "<".$node['tagName'];
						$result .= static::compile_Attribs($node);
						$result .= ">";
					}
				case 'root':
					if (is_array($node['children'])) {
						foreach ($node['children'] as $child) {
							$result .= static::compile($child);
						}
					}
					if ($node['type'] == 'tag') {
						if (isset($node['htmlTagClose'])) {
							$result .= $node['htmlTagClose'];
						} else {
							$result .= "</".$node['tagName'].">";
						}
					}
				break;
				case 'doctype':
					$result .= "<!DOCTYPE";
					foreach ($node['attribs'] as $attrib) {
						$result .= " $attrib";
					}
					$result .= ">";
				break;
				default:
					$result .= $node['html'];
				break;
			}
			return $result;
		}

		public static function parse($document, $options = false) {
			if (!$options) {
				$options = array();
			}
			$parser = array('options' => $options);
			$scanner = static::scanner($document);
			$parser['scanner'] = &$scanner;
			$stack = array();
			static::nextToken($parser);
			$result = array(
				'type' => 'root',
				'children' => static::parse_Node($parser, $stack)
			);
			return $result;
		}
	}
