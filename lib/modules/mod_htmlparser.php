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

	define(T_EOF, 0);
	define(T_TEXT, -1);
	define(T_ERROR, -2);

	define(STATE_TEXT, 1);
	define(STATE_OPEN_TAG, 2);
	define(STATE_CLOSE_TAG, 3);
	define(STATE_ATTRIB, 4);

	class htmlparser {

		function scanner($buffer) {
			$scanner['YYBUFFER'] = $buffer."\000";
			$scanner['YYLINE'] = 0;
			$scanner['YYCURSOR'] = 0;
			$scanner['YYSTATE'] = STATE_TEXT;


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
			$YYLINE = &$scanner["YYLINE"];
			$YYBUFFER = &$scanner["YYBUFFER"];
			$yych = $YYBUFFER[$YYCURSOR];
			$YYSTATE = &$scanner["YYSTATE"];

			do {
				switch (true) {
					case $yych === '"' && ($YYSTATE == STATE_ATTRIB):
					case $yych === "'" && ($YYSTATE == STATE_ATTRIB):
						$YYSTATE = STATE_OPEN_TAG;

						$quote = $yych;
						$yych = $yych = $YYBUFFER[++$YYCURSOR];
						while ($yych !== "\000" && $yych !== $quote) {
							if ($yych == "\\") {
								$yych = $yych = $YYBUFFER[++$YYCURSOR];
							}
							$value .= $yych;
							$yych = $yych = $YYBUFFER[++$YYCURSOR];
						}
						$value = $quote.$value.$quote;
						$yych = $YYBUFFER[++$YYCURSOR];
						return T_ATTRIB_VAL;
					break;
					case $yych === '=' && ($YYSTATE == STATE_OPEN_TAG):
						while ($scanner['class_whitespace'][$yych = $YYBUFFER[++$YYCURSOR]] == $yych);
						$YYSTATE = STATE_ATTRIB;
						contine;
					break;
					case $yych === $scanner['class_whitespace'][$yych] && (($YYSTATE == STATE_OPEN_TAG) || ($YYSTATE == STATE_CLOSE_TAG)):
						$yych = $YYBUFFER[++$YYCURSOR]; continue;
					break;
					case $yych === '<' && ($YYSTATE == STATE_TEXT):
						$value = $yych;
						$yych = $YYBUFFER[++$YYCURSOR];
						if ($yych == '/') {
							$next_state = STATE_CLOSE_TAG;
							$tag = T_CLOSE_TAG;
							$yych = $YYBUFFER[++$YYCURSOR];
						} else {
							while ($scanner['class_whitespace'][$yych] == $yych) {
								$yych = $YYBUFFER[++$YYCURSOR];
							}
							$next_state = STATE_OPEN_TAG;
							$tag = T_OPEN_TAG;
						}
						if ($scanner['class_ident_start'][$yych] == $yych) {
							$value = $yych;
							while ($scanner['class_ident_next'][$yych = $YYBUFFER[++$YYCURSOR]] == $yych) {
								$value .= $yych;
							}
							$YYSTATE = $next_state;
							return $tag;
						}
						return T_TEXT;
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
					case $yych === '>' && (($YYSTATE == STATE_OPEN_TAG) || ($YYSTATE == STATE_CLOSE_TAG)):
						$YYSTATE = STATE_TEXT;
						$yych = $YYBUFFER[++$YYCURSOR]; continue;
					break;
					case $yych === "\000":
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
			$value = "";
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
				'input'
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

			$tagName = strtolower(end($stack));
			$result = Array();
			while ($parser["token_ahead"] != T_EOF) {
				switch ($parser["token_ahead"]) {
					case T_TEXT:
						$node = Array('type' => 'text');
						$node['html'] = htmlparser::parse_Text($parser);
						$result[] = $node;
					break;
					case T_OPEN_TAG:
						$nextTag = strtolower($parser["token_ahead_value"]);
						if ($nextTag == $tagName && in_array($tagName, $siblings)) {
							return $result;
						}

						$node = htmlparser::parse_Tag_Open($parser);
						if ($node) {
							if ($node['type'] !== 'tagSingle') {
								array_push($stack, $node['tagName']);
									$node['children'] = htmlparser::parse_Node($parser, $stack);
								array_pop($stack);
								$current = end($stack);
							}
							$result[] = $node;
						}
					break;
					case T_CLOSE_TAG:
						htmlparser::nextToken($parser);
						return $result;
					default:
						htmlparser::nextToken($parser);
				}
			}
			return $result;
		}

		function compile($nodes) {
			$result = "";
			if (is_array($nodes)) {
				foreach ($nodes as $node) {
					if ($node['type'] == 'tag' || $node['type'] == 'tagSingle') {
						if ($node['tagName']) {
							$result .= "<".$node['tagName'];
							if (is_array($node['attribs'])) {
								foreach ($node['attribs'] as $key => $value) {
									$result .= " $key";
									if ($value !== false) {
										$result .= "=$value";
									}
								}
							}
							if ($node['type'] == 'tagSingle') {
								$result .= "/";
							}
							$result .= ">";
						}
						if ($node['type'] !== 'tagSingle') {
							$result .= htmlparser::compile($node['children']);
							$result .= "</".$node['tagName'].">";
						}
					} else {
						$result .= $node['html'];
					}
				}
			}
			return $result;
		}

		function parse($document) {
			$parser = Array();
			$scanner = htmlparser::scanner($document);
			$parser['scanner'] = &$scanner;
			$stack = Array();
			htmlparser::nextToken($parser);
			$result = htmlparser::parse_Node($parser, $stack);
			return $result;
		}
	}
?>
