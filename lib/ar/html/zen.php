<?php

	ar_pinp::allow( 'ar_html_zen' );

	class ar_html_zen extends ar_htmlNodes {

		const T_EOF                 = 0;
		const T_IDENT               = 1;
		const T_NUMBER              = 2;
		const T_PLACEHOLDER         = 3;
		const T_EXPRESSION_OPEN     = 4;
		const T_EXPRESSION_CLOSE    = 5;

		const T_OP_ATTRIBUTES_OPEN  = 6;
		const T_OP_ATTRIBUTES_CLOSE = 7;
		const T_OP_FILTER           = 8;
		const T_OP_MULTIPLIER       = 9;
		const T_OP_ASSIGN           = 11;
		const T_OP_ID               = 12;
		const T_OP_CLASS            = 13;
		const T_OP_CHILDREN         = 14;
		const T_OP_SIBLINGS         = 15;
		const T_OP_SETTING          = 16;

		public function __construct( $string ) {
			$parser = new ar_html_zenParser($string);
			$nodes = $parser->run();
			parent::__construct( (array)$this->compileNodes($nodes) );
		}

		private function compileNodes($nodes, $childNodes = null) {
			if( !isset($childNodes) ) {
				$childNodes = ar_html::nodes();
			}
			if( isset($nodes["children"]) ) {
				$childNodes = $this->compileNodes($nodes["children"], $childNodes);
			}
			unset($nodes["children"]);
			$result = array();
			$mult = 1;
			if( isset($nodes["multiplier"]) ) {
				$mult = (int)$nodes["multiplier"];
				unset($nodes["multiplier"]);
			}
			for($i=0;$i<$mult;$i++) {
				foreach( $nodes as $key => $value ) {
					if( $value["tagName"] ) {
						$tmult = 1;
						if( isset($value["multiplier"]) ) {
							$tmult = (int)$value["multiplier"];
						}
						for($j=0;$j<$tmult;$j++) {
							$result[] = ar_html::tag( $value["tagName"], $value["attributes"], $childNodes->cloneNode(true));
						}
					} else {
						$result = array_merge($result, (array)$this->compileNodes($value, $childNodes));
					}
				}
			}
			return ar_html::nodes($result);
		}
	}

	class ar_html_zenScanner {
		protected $YYLINE;
		protected $YYBUFFER;
		protected $YYCURSOR;
		protected $YYSTATE;

		protected $class_ident = array();
		protected $class_ident_next = array();
		protected $class_number = array();
		protected $class_whitespace = array();

		protected $tokens = array();

		public $token;
		public $token_value;
		public $token_ahead;
		public $token_ahead_value;


		function __construct($buffer) {
			$this->YYBUFFER = $buffer."\000";
			$this->YYLINE = 0;
			$this->YYCURSOR = 0;
			$this->YYSTATE = STATE_TEXT;


			// Identifiers [a-zA-Z]
			$class_ident_start = array();
			$start = ord('a');
			$end   = ord('z');
			for ($i = $start; $i <= $end; $i++) {
				$class_ident_start[chr($i)] = chr($i);
				$class_ident_start[strtoupper(chr($i))] = strtoupper(chr($i));
			}
			$this->class_ident = array_merge(array('-' => '-', '_' => '_'), $class_ident_start);
			$this->class_ident_start = $class_ident_start;
			// Numbers [0-9]
			$class_number = array();
			$start = ord('0');
			$end   = ord('9');
			for ($i = $start; $i <= $end; $i++) {
				$class_ident_next[chr($i)] = chr($i);
				$class_number[chr($i)] = chr($i);
			}
			$this->class_number = $class_number;
			$this->class_ident = array_merge($this->class_ident, $class_ident_next);
			// Whitespace
			$class_whitespace = array(" " => " ", "\t" => "\t", "\r" => "\r", "\n" => "\n");
			$this->class_whitespace = $class_whitespace;
		}

		function next() {
			if (count($this->tokens) == 0) {
				$new_token = $this->scan($new_value);
			} else {
				$entry = array_shift($this->tokens);
				list($new_token, $new_value) = each($entry);
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
			$YYBUFFER = &$this->YYBUFFER;
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
						++$YYCURSOR;
						return ar_html_zen::T_IDENT;
					break;
					case '|' === $yych: ($token || $token = ar_html_zen::T_OP_FILTER);
					case '(' === $yych: ($token || $token = ar_html_zen::T_EXPRESSION_OPEN);
					case ')' === $yych: ($token || $token = ar_html_zen::T_EXPRESSION_CLOSE);
					case '*' === $yych: ($token || $token = ar_html_zen::T_OP_MULTIPLIER);
					case '+' === $yych: ($token || $token = ar_html_zen::T_OP_SIBLINGS);
					case '[' === $yych: ($token || $token = ar_html_zen::T_OP_ATTRIBUTES_OPEN);
					case ']' === $yych: ($token || $token = ar_html_zen::T_OP_ATTRIBUTES_CLOSE);
					case '=' === $yych: ($token || $token = ar_html_zen::T_OP_ASSIGN);
					case '$' === $yych: ($token || $token = ar_html_zen::T_PLACEHOLDER);
					case '>' === $yych: ($token || $token = ar_html_zen::T_OP_CHILDREN);
					case '.' === $yych: ($token || $token = ar_html_zen::T_OP_CLASS);
					case '#' === $yych: ($token || $token = ar_html_zen::T_OP_ID);
					case ':' === $yych: ($token || $token = ar_html_zen::T_OP_SETTING);
						$value = $yych;
						++$YYCURSOR;
						return $token;
					break;
					case $this->class_whitespace[$yych] === $yych:
						$yych = $YYBUFFER[++$YYCURSOR]; continue;
					break;
					case $this->class_number[$yych] === $yych:
						$value = "";
						while ($this->class_number[$yych] == $yych && ($yych != "\000")) {
							$value .= $yych;
							$yych = $YYBUFFER[++$YYCURSOR];
						}
						return ar_html_zen::T_NUMBER;
					break;
					case $this->class_ident_start[$yych] === $yych:
						$value = $yych;
						$yych = $YYBUFFER[++$YYCURSOR];
						while ($this->class_ident[$yych] == $yych && ($yych != "\000")) {
							$value .= $yych;
							$yych = $YYBUFFER[++$YYCURSOR];
						}
						return ar_html_zen::T_IDENT;
					break;
					case "\000" === $yych:
						$value = $yych;
						return ar_html_zen::T_EOF;
					break;
					default:
						$value = $yych;
						++$YYCURSOR;
						return $value;
					break;
				}
			} while(1);
		}
	}

	class ar_html_zenParser {
		protected $scanner;

		public function __construct($string) {
			$this->scanner = new ar_html_zenScanner($string);
			$this->scanner->next();
		}

		public function run() {
			$nodelist = $this->parse();
			return $nodelist;
		}

		private function parse() {
			$result = array();
			$result[] = $this->parseExpression();
			$bye = false;
			do {
				$token = $this->scanner->token_ahead;
				switch($token) {
					case ar_html_zen::T_OP_CHILDREN:
						$this->scanner->next();
						$result["children"] = $this->parse();
					break;
					case ar_html_zen::T_OP_SIBLINGS:
						$this->scanner->next();
						$result[] = $this->parseExpression();
					break;
					default:
						$bye = true;
					break;
				}
			} while( !$bye );
			return $result;
		}

		private function parseExpression() {
			$token = $this->scanner->token_ahead;
			switch( $token ) {
				case ar_html_zen::T_EXPRESSION_OPEN:
					$this->scanner->next();
					$result = $this->parse();
					if( $this->scanner->token_ahead != ar_html_zen::T_EXPRESSION_CLOSE ) {
						die("No closing ')' found for expression.");
					}
					$this->scanner->next();
				break;
				case ar_html_zen::T_IDENT:
					$tagName = $this->scanner->token_ahead_value;
					$result["tagName"] = $tagName;
					$this->scanner->next();
					$result["attributes"] = $this->parseAttributes();
				break;
			}
			$mult = $this->parseMultiplier();
			if( $mult ) {
				$result["multiplier"] = $mult;
			}
			return $result;
		}

		private function parseAttributes() {
			$bye = false;
			$result = array();
			do {
				$token = $this->scanner->token_ahead;
				$key = false;
				switch( $token ) {
					case ar_html_zen::T_OP_ID:
						$key = "id";
					case ar_html_zen::T_OP_CLASS:
						if( !$key ) { $key = "class"; }
						$this->scanner->next();
						if( $this->scanner->token_ahead == ar_html_zen::T_IDENT ) {
							$result[$key][] = $this->scanner->token_ahead_value;
							$this->scanner->next();
						} else {
							die('no ident found for attribute: '.$key);
						}
					break;
					case ar_html_zen::T_OP_ATTRIBUTES_OPEN:
						$this->scanner->next();
						$result = array_merge($result, $this->parseAttributeList() ); // FIXME: deep merge
						if( $this->scanner->token_ahead != ar_html_zen::T_OP_ATTRIBUTES_CLOSE ) {
							die("No attribute closing tag ']' found.");
						}
						$this->scanner->next();
					break;
					default:
						$bye = true;
					break;
				}
			} while( !$bye );
			return $result;
		}

		private function parseAttributeList() {
			$bye = false;
			$result = array();
			do {
				$token = $this->scanner->token_ahead;
				switch( $token ) {
					case ar_html_zen::T_IDENT:
						$attr = $this->scanner->token_ahead_value;
						$value = "";
						$this->scanner->next();
						if( $this->scanner->token_ahead == ar_html_zen::T_OP_ASSIGN ) {
							$this->scanner->next();
							if( $this->scanner->token_ahead != ar_html_zen::T_IDENT ) {
								die('Trying to assign empty attribute '.$this->scanner->token_ahead_value );
							}
							$value = $this->scanner->token_ahead_value;
							$this->scanner->next();
						}
						$result[$attr] = $value;
					break;
					default:
						$bye = true;
					break;
				}
			} while( !$bye );
			return $result;
		}

		private function parseMultiplier() {
			$token = $this->scanner->token_ahead;
			if( $token == ar_html_zen::T_OP_MULTIPLIER ) {
				$this->scanner->next();
				if( $this->scanner->token_ahead == ar_html_zen::T_NUMBER ) {
					$value = $this->scanner->token_ahead_value;
					$this->scanner->next();
					return $value;
				} else {
					die('Invalid multiplier found: '.$this->scanner->token_ahead_value);
				}
			}
			return false;
		}
	}
