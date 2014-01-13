<?php
	define(T_OPEN_TAG, 252);
	define(T_CLOSE_TAG, 251);
	define(T_TAG_END, 250);
	define(T_ASSIGN, 248);
	define(T_IDENT, 247);
	define(T_STRING, 246);
	define(T_ATTRIB, 245);
	define(T_NUMBER, 244);
	define(T_ATTRIB_VAL, 243);
	define(T_DOCTYPE, 242);

	define(T_EOF, 0);
	define(T_TEXT, -1);
	define(T_ERROR, -2);

	define(STATE_TEXT, 1);
	define(STATE_OPEN_TAG, 2);
	define(STATE_CLOSE_TAG, 3);
	define(STATE_ATTRIB, 4);
	define(STATE_COMMENT, 5);
	define(STATE_SCRIPT, 6);
	define(STATE_DOCTYPE, 7);
 
	define(CONTEXT_NORMAL, 1);
	define(CONTEXT_SCRIPT, 2);

	class htmlparser {

		function scanner($buffer) {
			$scanner['YYBUFFER'] = $buffer."\000";
			$scanner['YYLINE'] = 0;
			$scanner['YYCURSOR'] = 0;
			$scanner['YYSTATE'] = STATE_TEXT;
			$scanner['YYCONTEXT'] = CONTEXT_NORMAL;

			$class_ident_start = Array();
			for ($i = ord('a'); $i <= ord('z'); $i++) {
				$class_ident_start[chr($i)] = chr($i);
				$class_ident_start[strtoupper(chr($i))] = strtoupper(chr($i));
			}
			$scanner['class_ident_start'] = $class_ident_start;

			$class_attrib_start = $class_ident_start;
			$scanner['class_attrib_start'] = $class_attrib_start;


			$class_ident_next = $class_ident_start;
			for ($i = ord('0'); $i <= ord('9'); $i++) {
				$class_ident_next[chr($i)] = chr($i);
				$class_number[chr($i)] = chr($i);
			}
			$scanner['class_ident_next'] = $class_ident_next;
			$scanner['class_number'] = $class_number;

			$class_attrib_next = $class_ident_next;
			$class_attrib_next[':'] = ':';
			$scanner['class_attrib_next'] = $class_attrib_next;


			$class_whitespace = Array(" " => " ", "\t" => "\t", "\r" => "\r", "\n" => "\n");
			$scanner['class_whitespace'] = $class_whitespace;

			return $scanner;
		}

		function scan(&$scanner, &$value) {
			$YYCURSOR = &$scanner["YYCURSOR"];
			//$YYLINE = &$scanner["YYLINE"];
			$YYBUFFER = &$scanner["YYBUFFER"];
			$yych = $YYBUFFER[$YYCURSOR];
			$YYSTATE = &$scanner["YYSTATE"];
			$YYCONTEXT = &$scanner["YYCONTEXT"];

			do {
				switch (true) {
					case $yych === $scanner['class_attrib_start'][$yych] && ($YYSTATE == STATE_DOCTYPE):
						$value = $yych;
						while ($scanner['class_attrib_next'][$yych = $YYBUFFER[++$YYCURSOR]] == $yych) {
							$value .= $yych;
						}
						return T_ATTRIB_VAL;
					break;
					case $yych === '"' && ($YYSTATE == STATE_DOCTYPE):
						$yych = $yych = $YYBUFFER[++$YYCURSOR];
						while ($yych !== "\000" && $yych !== '"') {
							$value .= $yych;
							$yych = $yych = $YYBUFFER[++$YYCURSOR];
						}
						$value = '"'.$value.'"';
						$yych = $YYBUFFER[++$YYCURSOR];
						return T_ATTRIB_VAL;
					break;
					case $yych === '"' && ($YYSTATE == STATE_ATTRIB):
					case $yych === "'" && ($YYSTATE == STATE_ATTRIB):
						$YYSTATE = STATE_OPEN_TAG;

						$quote = $yych;
						$yych = $yych = $YYBUFFER[++$YYCURSOR];
						while ($yych !== "\000" && $yych !== $quote) {
							$value .= $yych;
							$yych = $yych = $YYBUFFER[++$YYCURSOR];
						}
						$yych = $YYBUFFER[++$YYCURSOR];
						return T_ATTRIB_VAL;
					break;
					case $yych === '=' && ($YYSTATE == STATE_OPEN_TAG):
						while ($scanner['class_whitespace'][$yych = $YYBUFFER[++$YYCURSOR]] == $yych);
						$YYSTATE = STATE_ATTRIB;
						contine;
					break;
					case $yych === $scanner['class_whitespace'][$yych] && (($YYSTATE == STATE_OPEN_TAG) || ($YYSTATE == STATE_CLOSE_TAG) || ($YYSTATE == STATE_DOCTYPE)):
						$yych = $YYBUFFER[++$YYCURSOR]; continue;
					break;
					case $yych === '-' && ($YYSTATE == STATE_COMMENT):
						if (substr($YYBUFFER, $YYCURSOR, 3) == '-->') {
							$YYSTATE = STATE_TEXT;
							$value = "-->"; $YYCURSOR+=3;
							return T_TEXT;
						}
						$value = '-';
						$YYBUFFER[++$YYCURSOR];
						return T_TEXT;
					break;
					case (strtolower(substr($YYBUFFER, $YYCURSOR, strlen('<!--'))) == '<!--') && ($YYSTATE == STATE_TEXT):
							$value		= "<!--"; $YYCURSOR+=3;
							$YYSTATE	= STATE_COMMENT;
							return	T_TEXT;
					break;
					case strtolower(substr($YYBUFFER, $YYCURSOR, strlen('</script>'))) == '</script>' && ($YYSTATE == STATE_SCRIPT):
						$YYCONTEXT = CONTEXT_NORMAL;
						// fallthrough
					case substr($YYBUFFER, $YYCURSOR, 2) == '</' && ($YYSTATE == STATE_TEXT):
						$YYSTATE	= STATE_CLOSE_TAG;
						$YYCURSOR	+= 1;
						$value		= "";
						while ($scanner['class_ident_next'][$yych = $YYBUFFER[++$YYCURSOR]] == $yych) {
							$value .= $yych;
						}
						return T_CLOSE_TAG;
					break;
					case strtolower(substr($YYBUFFER, $YYCURSOR, strlen('<!doctype'))) == '<!doctype' && ($YYSTATE == STATE_TEXT):
						$YYSTATE	= STATE_DOCTYPE;
						$value		= substr($YYBUFFER, $YYCURSOR, strlen('<!doctype'));
						$YYCURSOR	+= strlen('<!doctype');
						return T_DOCTYPE;
					break;
					case $yych == '<' && ($YYSTATE == STATE_TEXT):
						$YYSTATE	= STATE_OPEN_TAG;
						$value		= "";
						while ($scanner['class_ident_next'][$yych = $YYBUFFER[++$YYCURSOR]] == $yych) {
							$value .= $yych;
						}
						if (strtolower($value) == 'script') {
							$YYCONTEXT = CONTEXT_SCRIPT;
						}
						return T_OPEN_TAG;
					break;
					case $yych === $scanner['class_attrib_start'][$yych] && ($YYSTATE == STATE_OPEN_TAG):
						$value = $yych;
						while ($scanner['class_attrib_next'][$yych = $YYBUFFER[++$YYCURSOR]] == $yych) {
							$value .= $yych;
						}
						return T_ATTRIB;
					break;
					case $yych === $scanner['class_number'][$yych] && ($YYSTATE == STATE_OPEN_TAG):
						$value = $yych;
						while ($scanner['class_number'][$yych = $YYBUFFER[++$YYCURSOR]] == $yych) {
							$value .= $yych;
						}
						return T_NUMBER;
					break;
					case $yych === '>' && (($YYSTATE == STATE_OPEN_TAG) || ($YYSTATE == STATE_CLOSE_TAG) || ($YYSTATE == STATE_DOCTYPE)):
						if ($YYCONTEXT == CONTEXT_SCRIPT) {
							$YYSTATE = STATE_SCRIPT;
						} else {
							$YYSTATE = STATE_TEXT;
						}
						$yych = $YYBUFFER[++$YYCURSOR]; continue;
					break;
					case ord($yych) === 0:
						$value = $yych;
						return T_EOF;
					break;
					case $yych === $yych && ($YYSTATE == STATE_ATTRIB):
						$YYSTATE = STATE_OPEN_TAG;
						$value = "";
						while ($scanner['class_whitespace'][$yych] !== $yych && ($yych != "\000" && $yych != ">")) {
							$value .= $yych;
							$yych = $YYBUFFER[++$YYCURSOR];
						}
						return T_ATTRIB_VAL;
					break;
					case $yych === $yych && ($YYSTATE == STATE_OPEN_TAG):
						$yych = $YYBUFFER[++$YYCURSOR]; continue;
					break;
					default:
						$value = $yych;
						$yych = $YYBUFFER[++$YYCURSOR]; return T_TEXT;
				}
			} while (1);

		}

		function nextToken(&$parser) {
			$tokens = &$parser['tokens'];
			$scanner = &$parser['scanner'];
			if (count($tokens) == 0) {
				$new_token = htmlparser::scan($scanner, $new_value);
			} else {
				list($new_token, $new_value) = each(array_shift($tokens));
			}
			if (isset($parser["token_ahead"])) {
				$parser["token"] = $parser["token_ahead"];
				$parser["token_value"] = $parser["token_ahead_value"];
			}
			$parser["token_ahead"] = $new_token;
			$parser["token_ahead_value"] = $new_value;
		}


		function parse_Text(&$parser) {
			$result = "";
			while ($parser["token_ahead"] == T_TEXT) {
				htmlparser::nextToken($parser);
				$result .= $parser["token_value"];
			}
			return $result;
		}

		function parse_Tag_Open(&$parser) {
			$singles = Array(
				'br', 'img', 'area', 'link', 'param', 'hr', 'base', 'meta',
				'input', 'col'
			);

			$result = Array('type' => 'tag');
			$tagName = $parser["token_ahead_value"];
			if (in_array(strtolower($tagName), $singles)) {
				$result['type'] = 'tagSingle';
			}
			$result['tagName'] = $tagName;
			htmlparser::nextToken($parser);
			while ($parser["token_ahead"] == T_ATTRIB) {
				htmlparser::nextToken($parser);
				$attrib = $parser["token_value"];
				$attrib_value = false;
				if ($parser["token_ahead"] == T_ATTRIB_VAL) {
					htmlparser::nextToken($parser);
					$attrib_value = $parser["token_value"];
				}
				$result['attribs'][$attrib] = $attrib_value;
			}

			return $result;
		}

		function parse_Node(&$parser, &$stack) {
			$siblings = Array('table', 'tr', 'td', 'li', 'ul');

			if (count($stack)) {
				$parentNode	= &$stack[count($stack)-1];
				$tagName	= strtolower($parentNode['tagName']);
			}
			$result = Array();
			while ($parser["token_ahead"] != T_EOF) {
				switch ($parser["token_ahead"]) {
					case T_TEXT:
						$node = Array('type' => 'text');
						$node['html'] = htmlparser::parse_Text($parser);
						$result[] = $node;
					break;
					case T_DOCTYPE:
						$node = Array('type' => 'doctype');
						htmlparser::nextToken($parser);
						while ($parser["token_ahead"] == T_ATTRIB_VAL) {
							htmlparser::nextToken($parser);
							$attrib_value = $parser["token_value"];
							$node['attribs'][] = $attrib_value;
						}
						$result[] = $node;
					break;
					case T_OPEN_TAG:
						$nextTag = strtolower($parser["token_ahead_value"]);
						if ($nextTag == $tagName && in_array($tagName, $siblings)) {
							if ($parser['options']['noTagResolving']) {
								$parentNode['htmlTagClose'] = "";
							}
							return $result;
						}

						$node = htmlparser::parse_Tag_Open($parser);
						if ($node) {
							if ($node['type'] !== 'tagSingle') {
								$stack[] = &$node;
								$node['children'] = htmlparser::parse_Node($parser, $stack);
								array_pop($stack);
								$current = end($stack);
							}
							$result[] = $node;
						}
					break;
					case T_CLOSE_TAG:
						$closeTag = $parser["token_ahead_value"];
						if ($tagName != strtolower($closeTag)) {
							// continue parsing because closing tag does not match current tag
							// FIXME: create a better check
							htmlparser::nextToken($parser);

							// if we do not resolve tags, we have to add this one as text
							if ($parser['options']['noTagResolving']) {
								$node = Array('type' => 'text');
								$node['html'] = "</".$parser["token_value"].">";
								$result[] = $node;
							}
							continue;
						}

						// if we do not resolve tags, we have to add this one as text
						if ($parser['options']['noTagResolving']) {
							$parentNode['htmlTagClose'] = "</".$parser['token_ahead_value'].">";
						}

						htmlparser::nextToken($parser);
						return $result;
					default:
						htmlparser::nextToken($parser);
				}
			}
			if ($parser['options']['noTagResolving']) {
				$parentNode['htmlTagClose'] = "";
			}
			return $result;
		}

		function compile_Attribs(&$node) {
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

		function compile($node) {
			$result = "";
			switch ($node['type']) {
				case 'tag':
				case 'tagSingle':
					if ($node['tagName']) {
						$result .= "<".$node['tagName'];
						$result .= htmlparser::compile_Attribs($node);
						$result .= ">";
					}
				case 'root':
					if (is_array($node['children'])) {
						foreach ($node['children'] as $child) {
							$result .= htmlparser::compile($child);
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

		function parse($document, $options = false) {
			if (!$options) {
				$options = Array();
			}
			$parser = Array('options' => $options);
			$scanner = htmlparser::scanner($document);
			$parser['scanner'] = &$scanner;
			$stack = Array();
			htmlparser::nextToken($parser);
			$result = Array(
				'type' => 'root',
				'children' => htmlparser::parse_Node($parser, $stack)
			);
			return $result;
		}
	}
?>