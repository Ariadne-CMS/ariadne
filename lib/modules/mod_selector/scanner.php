<?php
	class selectorScanner {

		function __construct($buffer) {
			$this->YYBUFFER = $buffer."\000";
			$this->YYLINE = 0;
			$this->YYCURSOR = 0;
			$this->YYSTATE = STATE_TEXT;


			// Identifiers [a-zA-Z]
			$class_ident_start = Array('_' => '_');
			for ($i = ord('a'); $i <= ord('z'); $i++) {
				$class_ident_start[chr($i)] = chr($i);
				$class_ident_start[strtoupper(chr($i))] = strtoupper(chr($i));
			}
			$this->class_ident = array_merge(Array('.' => '.'), $class_ident_start);
			// Numbers [0-9] 
			for ($i = ord('0'); $i <= ord('9'); $i++) {
				$class_number[chr($i)] = chr($i);
			}
			$this->class_number = $class_number;

			// Whitespace
			$class_whitespace = Array(" " => " ", "\t" => "\t", "\r" => "\r", "\n" => "\n");
			$this->class_whitespace = $class_whitespace;
		}

		function next() {
			if (count($this->tokens) == 0) {
				$new_token = $this->scan($new_value);
			} else {
				list($new_token, $new_value) = each(array_shift($this->tokens));
			}
			if (isset($this->token_ahead)) {
				$this->token = $this->token_ahead;
				$this->token_value = $this->token_ahead_value;
			}
			$this->token_ahead = $new_token;
			$this->token_ahead_value = $new_value;
			return $this->token;

		}


		function scan(&$value) {
			$YYCURSOR = &$this->YYCURSOR;
			//$YYLINE = &$this->YYLINE;
			$YYBUFFER = &$this->YYBUFFER;
			//$YYSTATE = &$this->YYSTATE;
			$yych = $YYBUFFER[$YYCURSOR];
			$token = "";

			do {
				switch (true) {
					case '"' === $yych: 
					case "'" === $yych:
						$quote = $yych;
						$yych = $YYBUFFER[++$YYCURSOR];
						while ($yych !== "\000" && $yych !== $quote) {
							if ($yych == "\\") {
								$yych = $YYBUFFER[++$YYCURSOR];
								if ($yych !== $quote && $yych != "\\") {
									$value .= "\\";
								}
							}
							$value .= $yych;
							$yych = $YYBUFFER[++$YYCURSOR];
						}
						$yych = $YYBUFFER[++$YYCURSOR];
						return T_IDENT;
					break;
					case '|' === $yych: ($token || $token = T_OR);
					case '{' === $yych: ($token || $token = T_REP_OPEN);
					case '}' === $yych: ($token || $token = T_REP_CLOSE);
					case ',' === $yych: ($token || $token = T_COMMA);
					case '(' === $yych: ($token || $token = T_PAR_OPEN);
					case ')' === $yych: ($token || $token = T_PAR_CLOSE);
					case '*' === $yych: ($token || $token = T_REP_ZERO_MORE);
					case '+' === $yych: ($token || $token = T_REP_ONE_MORE);
						$value = $yych; $yych = $YYBUFFER[++$YYCURSOR];
						return $token;
					break;
					case $this->class_whitespace[$yych] === $yych:
						$yych = $YYBUFFER[++$YYCURSOR]; continue;
					break;
					case '%' === $yych:
						$value = $yych;
						$yych = $YYBUFFER[++$YYCURSOR];						
						if ($yych == '(') {
							$value .= $yych;
							++$YYCURSOR;
							return T_RECURSE_DEF;
						}
						return T_RECURSE_IDENT;
					break;
					case '?' === $yych:
						$value = $yych;
						$yych = $YYBUFFER[++$YYCURSOR];						
						if ($yych == ':') {
							$value .= $yych;
							++$YYCURSOR;
							return T_NON_GREEDY;
						}
						return T_REP_ZERO_ONE;
					break;
					case '!' === $yych:
						$value = $yych;
						$yych = $YYBUFFER[++$YYCURSOR];						
						if ($yych == ':') {
							$value .= $yych;
							++$YYCURSOR;
							return T_GREEDY;
						}
						return $value;
					break;
					case '=' === $yych:
						$value = $yych;
						$yych = $YYBUFFER[++$YYCURSOR];						
						if ($yych == ':') {
							$value .= $yych;
							++$YYCURSOR;
							return T_EQUAL_GREEDY;
						}
						return $value;
					break;
					case $this->class_number[$yych] === $yych:
						$value = "";
						while ($this->class_number[$yych] == $yych && ($yych != "\000")) {
							$value .= $yych;
							$yych = $YYBUFFER[++$YYCURSOR];
						}
						return T_NUMBER;
					break;
					case $this->class_ident[$yych] === $yych:
						$value = "";
						while ($this->class_ident[$yych] == $yych && ($yych != "\000")) {
							$value .= $yych;
							$yych = $YYBUFFER[++$YYCURSOR];
						}
						return T_IDENT;
					break;
					case "\000" === $yych:
						$value = $yych;
						return T_EOF;
					break;
					default:
						$value = $yych; $yych = $YYBUFFER[++$YYCURSOR];
						return $value;
					break;
				}
			} while(1);
		}

	}

?>