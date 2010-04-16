<?php

	ar_pinp::allow('ar_listExpression', array('pattern', 'item') );
	
	class ar_listExpression_Pattern {
		public $patterns = array();
		
		public function __construct( $patterns ) {
			$this->patterns = $patterns;
		}		
	}
	
	class ar_listExpression extends arBase implements Iterator, Countable, ArrayAccess {
		
		private $list      = null;
		private $current   = 0;
		private $patterns  = array();
		private $nodeLists = array();
		private $length    = 0;
		
		const T_IDENT          = 3;
		const T_OR             = 4;
		const T_REP_OPEN       = 5;
		const T_REP_CLOSE      = 6;
		const T_COMMA          = 7;
		const T_PAR_OPEN       = 8;
		const T_PAR_CLOSE      = 9;
		const T_REP_ZERO_MORE  = 10;
		const T_REP_ONE_MORE   = 11;
		const T_REP_ZERO_ONE   = 12;
		const T_NUMER          = 13;
		const T_EOF            = 14;
		
		const N_OR             = 100;
		const N_AND            = 101;
		const N_IDENT          = 102;
		const N_REPEAT         = 103;
		
		public function __construct( $list ) {
			if (is_array($list)) {
				$this->list   = $list;
			} else if ( is_numeric( $list ) ) {
				$this->length = $list;
			}
		}
		
		public function pattern() {
			$params = func_get_args();
			foreach( $params as $pattern ) {
				if ( is_array( $pattern ) ) {
					call_user_func_array( array( $this, 'pattern' ), $pattern );
				} else {
					$this->patterns[] = $pattern;
					$parser = new ar_listExpressionParser( $pattern );
					$this->nodeLists[] = $parser->run();
				}
			}
			return $this;
		}
		
		public function item( $position ) {
			$result = array();
			if ( isset($this->list) ) {
				$length = count($this->list);
			} else {
				$length = $this->length;
			}
			if (!$length) {
				$length = $position;
			}
			foreach ( $this->nodeLists as $i => $nodeList ) {
				$item = $nodeList->run($length, $position);
				if ($item !== '.') {
					$result[$i] = $item;
				}
			}
			return $result;
		}
		
		function offsetExists($offset) {
			return (exists($this->list[$offset]));
		}
		
		function offsetGet($offset) {
			$position = array_search( $offset, array_keys($this->list) );
			if (isset($position)) {
				return $this->item( $position );
			} else {
				return null;
			}
		}
		
		function offsetSet($offset, $value) {
			return false;
		}
		
		function offsetUnset($offset) {
			return false;
		}
		
		function current() {
			return $this->item($this->current);
		}
		
		function key() {
			return $this->current;
		}
		
		function next() {
			++$this->current;
		}
		
		function rewind() {
			$this->current = 0;
		}
		
		function valid() {
			return ( $this->current < count($this->list) );
		}
		
		function count() {
			return (isset($this->list) ? count($this->list): $this->length);
		}
		
		public static function createNode($type, $data = array()) {
			switch ($type) {
				case ar_listExpression::N_OR:
					return new ar_listExpressionNodeOr($data);
				break;
				case ar_listExpression::N_AND:
					return new ar_listExpressionNodeAnd($data);
				break;
				case ar_listExpression::N_IDENT:
					return new ar_listExpressionNodeIdent($data);
				break;
				case ar_listExpression::N_REPEAT:
					return new ar_listExpressionNodeRepeat($data);
				break;
			}
		}


	}
	
	class ar_listExpressionScanner {

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
				$class_ident_next[chr($i)] = chr($i);
				$class_number[chr($i)] = chr($i);
			}
			$this->class_number = $class_number;

			// Whitespace
			$class_whitespace = Array(" " => " ", "\t" => "\t", "\r" => "\r", "\n" => "\n");
			$this->class_whitespace = $class_whitespace;

			return $scanner;
		}

		function next() {
			if (count($this->tokens) == 0) {
				$new_token = $this->scan($new_value);
			} else {
				list($new_token, $new_value) = each(array_shift($tokens));
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
			$YYLINE = &$this->YYLINE;
			$YYBUFFER = &$this->YYBUFFER;
			$YYSTATE = &$this->YYSTATE;
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
						return ar_listExpression::T_IDENT;
					break;
					case '|' === $yych: ($token || $token = ar_listExpression::T_OR);
					case '{' === $yych: ($token || $token = ar_listExpression::T_REP_OPEN);
					case '}' === $yych: ($token || $token = ar_listExpression::T_REP_CLOSE);
					case ',' === $yych: ($token || $token = ar_listExpression::T_COMMA);
					case '(' === $yych: ($token || $token = ar_listExpression::T_PAR_OPEN);
					case ')' === $yych: ($token || $token = ar_listExpression::T_PAR_CLOSE);
					case '*' === $yych: ($token || $token = ar_listExpression::T_REP_ZERO_MORE);
					case '+' === $yych: ($token || $token = ar_listExpression::T_REP_ONE_MORE);
						$value = $yych; $yych = $YYBUFFER[++$YYCURSOR];
						return $token;
					break;
					case $this->class_whitespace[$yych] === $yych:
						$yych = $YYBUFFER[++$YYCURSOR]; continue;
					break;
					case '?' === $yych:
						$value = $yych;
						$yych = $YYBUFFER[++$YYCURSOR];						
						return ar_listExpression::T_REP_ZERO_ONE;
					break;
					case $this->class_number[$yych] === $yych:
						$value = "";
						while ($this->class_number[$yych] == $yych && ($yych != "\000")) {
							$value .= $yych;
							$yych = $YYBUFFER[++$YYCURSOR];
						}
						return ar_listExpression::T_NUMBER;
					break;
					case $this->class_ident[$yych] === $yych:
						$value = "";
						while ($this->class_ident[$yych] == $yych && ($yych != "\000")) {
							$value .= $yych;
							$yych = $YYBUFFER[++$YYCURSOR];
						}
						return ar_listExpression::T_IDENT;
					break;
					case "\000" === $yych:
						$value = $yych;
						return ar_listExpression::T_EOF;
					break;
					default:
						$value = $yych; $yych = $YYBUFFER[++$YYCURSOR];
						return $value;
					break;
				}
			} while(1);
		}

	}

	class ar_listExpressionParser {

		public function __construct($string) {
			$this->scanner = new ar_listExpressionScanner($string);
			$this->scanner->next();
		}

		public function run() {
			$node = $this->parseOr();
			return $node;
		}

		private function parseOr() {
			$node = $this->parseAnd();
			while ($this->scanner->token_ahead == ar_listExpression::T_OR) {
				$this->scanner->next();
				$node = ar_listExpression::createNode(
							ar_listExpression::N_OR,
							Array(
								'nodeLeft'	=> $node,
								'nodeRight'	=> $this->parseAnd()
							)
				);
			}
			return $node;
		}

		private function parseAnd() {
			$prevNode = null;
			while ($node = $this->parseIdent()) {
					$node = $this->parseRepeat($node);
					if ($prevNode) {
						$node = ar_listExpression::createNode(
									ar_listExpression::N_AND,
									Array(
										'nodeLeft'	=> $node,
										'nodeRight'	=> $prevNode
									)
						);
					}
					$prevNode = $node;
			}
			return $prevNode;
		}

		private function parseIdent() {
				switch ($this->scanner->token_ahead) {
					case ar_listExpression::T_IDENT:
						$this->scanner->next();
						$node = ar_listExpression::createNode(
									ar_listExpression::N_IDENT,
									Array(
										'value'		=> $this->scanner->token_value
									)
						);
					break;
					case ar_listExpression::T_PAR_OPEN:
						$this->scanner->next();
						$node = $this->parseOr();
						if ($this->scanner->token_ahead == ar_listExpression::T_PAR_CLOSE) {
							$this->scanner->next();
						}
					break;
				}

				return $node;
		}

		private function parseRepeat(&$prevNode) {
			switch($this->scanner->token_ahead) {
				case ar_listExpression::T_REP_ZERO_ONE:
					$this->scanner->next();
					$prevNode->req = false;
					$node = $this->parseRepeat($prevNode);
				break;
				case ar_listExpression::T_REP_ZERO_MORE:
					$this->scanner->next();
					$node = ar_listExpression::createNode(
								ar_listExpression::N_REPEAT,
								Array(
									"minRep"	=> 1,
									"maxRep"	=> 0,
									"req"		=> false,
									"min"		=> $prevNode->min,
									"max"		=> 0,
									"nodeLeft"	=> $prevNode
								)
					);
					$node = $this->parseRepeat($node);
				break;
				case ar_listExpression::T_REP_ONE_MORE:
					$this->scanner->next();
					$node = ar_listExpression::createNode(
								ar_listExpression::N_REPEAT,
								Array(
									"minRep"	=> 1,
									"maxRep"	=> 0,
									"req"		=> $prevNode->req,
									"min"		=> $prevNode->min,
									"max"		=> 0,
									"nodeLeft"	=> $prevNode
								)
					);
					$node = $this->parseRepeat($node);
				break;
				case ar_listExpression::T_REP_OPEN:
					$this->scanner->next();
					$min		= 0;
					$max		= 0;
					if ($this->scanner->token_ahead == ar_listExpression::T_NUMBER) {
						$this->scanner->next();
						$min = (int)$this->scanner->token_value;
						$max = $min;
					}
					if ($this->scanner->token_ahead == ar_listExpression::T_COMMA) {
						$this->scanner->next();
						if ($this->scanner->token_ahead == ar_listExpression::T_NUMBER) {
							$this->scanner->next();
							$max = (int)$this->scanner->token_value;
						}
					}
					if ($this->scanner->token_ahead == ar_listExpression::T_REP_CLOSE) {
						$this->scanner->next();
					}
					$node = ar_listExpression::createNode(
								ar_listExpression::N_REPEAT,
								Array(
									"minRep"	=> ($min > 0) ? $min : 1,
									"maxRep"	=> $max,
									"req"		=> ($min > 0) ? $prevNode->req : false,
									"min"		=> ($min > 0) ? $min * $prevNode->min : $prevNode->min,
									"max"		=> $max * $prevNode->max,
									"nodeLeft"	=> $prevNode
								)
					);
					$node = $this->parseRepeat($node);
				break;
				default:
					$node = $prevNode;
				break;
			}
			return $node;
		}

	}

	interface ar_listExpressionNode {

		function run($count, $offset); 

	}

	class ar_listExpressionNodeOr implements ar_listExpressionNode {

		function __construct($data) {
			$nodeLeft = $data['nodeLeft']; $nodeRight = $data['nodeRight'];
			if ($nodeLeft || $nodeRight) {
				if ($nodeRight && $nodeRight->type == ar_listExpression::N_OR) {
					if (!$nodeLeft || $nodeRight->left && $nodeRight->left->min > $nodeLeft->min) {
						$newNodeLeft	= $nodeRight->left;
						$nodeRight		= ar_listExpression::createNode(ar_listExpression::N_OR, Array('nodeLeft' => $nodeLeft, 'nodeRight' => $nodeRight->right));
						$nodeLeft		= $newNodeLeft;
					}
				}
				$this->req		= $nodeLeft->req & $nodeRight->req;
				if (!$nodeLeft || ($nodeRight && $nodeRight->min > $nodeLeft->min)) {
					$this->min		= ($nodeLeft) ? $nodeLeft->min : 0;
					$this->max		= (!$nodeRight->max || $nodeRight->max > $nodeLeft->max) ? $nodeRight->max : $nodeLeft->max;
					$this->size		= ($nodeRight->size > $nodeLeft->size) ? $nodeRight->size : $nodeLeft->size;
					$this->left		= $nodeRight;
					$this->right	= $nodeLeft;
				} else if (!$nodeRight || ($nodeLeft && $nodeLeft->min >= $nodeRight->min)) {
					$this->min		= ($nodeRight) ? $nodeRight->min : 0;
					$this->max		= (!$nodeLeft->max || $nodeLeft->max > $nodeRight->max) ? $nodeLeft->max : $nodeRight->max;
					$this->size		= ($nodeLeft->size > $nodeRight->size) ? $nodeLeft->size : $nodeRight->size;
					$this->left		= $nodeLeft;
					$this->right	= $nodeRight;
				}
			}

		}

		function run($count, $offset) {
			if ($this->left->min <= $count) {
				return $this->left->run($count, $offset);
			} else {
				return $this->right->run($count, $offset);
			}
		}

	}

	class ar_listExpressionNodeAnd implements ar_listExpressionNode {

		function __construct($data) {
			$nodeLeft = $data['nodeLeft']; $nodeRight = $data['nodeRight'];
			$this->size			= $nodeLeft->size + $nodeRight->size;
			$this->req			= $nodeLeft->req | $nodeRight->req;
			$this->min			= 0;
			if ($nodeLeft->req) {
				$this->min		= $nodeLeft->min;
			}
			if ($nodeRight->req) {
				$this->min		+= $nodeRight->min;
			}
			$this->max			= (!$nodeLeft->max || !$nodeRight->max) ? 0 : $nodeLeft->max + $nodeRight->max;
			$this->left			= $nodeLeft;
			$this->right		= $nodeRight;
		}

		function run($count, $offset) {
			$require = (($this->right->req) ? $this->right->min : 0) + (($this->left->req) ? $this->left->min : 0);
			if ($count < $require) {
				return $count;
			}

			$rightCount = $count;
			$leftCount	= 0;
			if ($this->left->req) {
				$rightCount -= $this->left->min;
				$leftCount	+= $this->left->min;
			}
			if ($rightCount >= $this->right->min) {
				$rightResult = $this->right->run($rightCount, $offset);
				if (is_string($rightResult)) {
					return $rightResult;
				}
				if ($rightResult > 0) {
					$rightCount	-= $rightResult;
					$leftCount	+= $rightResult;
				}
			} else {
				$leftCount	+= $rightCount;
				$rightCount	= 0;
			}
			if ($leftCount >= $this->left->min) {
				return $this->left->run($leftCount, $offset - $rightCount);
			} else {
				return $leftCount;
			}
		}

	}

	class ar_listExpressionNodeIdent implements ar_listExpressionNode {

		function __construct($data) {
			$this->value		= $data['value'];
			$this->req			= true;
			$this->min			= 1;
			$this->max			= 1;
			$this->size			= 1;
		}

		function run($count, $offset) {
			if ($offset == 0) {
				return $this->value;
			} else {
				return $count - 1;
			}
		}

	}

	class ar_listExpressionNodeRepeat implements ar_listExpressionNode {

		function __construct($data) {
			$nodeLeft			= $data['nodeLeft'];
			$this->req			= $data['req'];
			$this->min			= $data['min'];
			$this->max			= $data['max'];
			$this->size			= $data['size'];
			$this->minRep		= $data['minRep'];
			$this->maxRep		= $data['maxRep'];
			$this->left			= $nodeLeft;
		}

		function run($count, $offset) {
			if ($count < $this->minRep * $this->left->min) {
				return $count;
			}
			if ($this->max && $count > $this->max && $offset >= $this->max) {
				return $count - $this->max;
			}

			$rightCount		= $count;
			$minRep			= $this->minRep;
			do {
				$minRepSize		= ($minRep > 0) ? (($minRep - 1) * $this->left->min) : 0;

				$rightResult	= $this->left->run($rightCount - $minRepSize, $offset);
				if (is_string($rightResult)) {
					return $rightResult;
				}
				$matchSize		= ($rightCount - $minRepSize) - $rightResult;
				$rightRep		= (int)($rightCount / $matchSize);
				$rightRest		= $rightCount % $matchSize;
				$minRep			= $minRep - $rightRep;
				if ($offset < $rightCount - $rightRest) {
					return $this->left->run($matchSize, $offset % $matchSize);
				} else {
					$offset		= $offset - ($rightCount - $rightRest);
					$rightCount	= $rightRest;
				}
			} while ($rightRest && $rightRest >= $this->left->min);

			return $rightRest;
		}

	}
	
?>