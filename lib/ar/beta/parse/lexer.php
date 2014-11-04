<?php
	ar_pinp::allow( 'ar_beta_parse_lexer' );
	ar_pinp::allow( 'ar_beta_parse_lexer_node' );

	class ar_beta_parse_lexer extends arBase {

		public function createRule($string, $target) {
			$node = ar_beta_parse_lexer_parser::parse($string);
			if (!is_object($target)) {
				$target = new ar_beta_parse_lexer_node_fin($target);
			}
			return $node->compile($target);
		}

	}


	class ar_beta_parse_lexer_node extends arBase {
		static $_STATE_COUNT = 0;
		var $state;
		var $fin;

		function setFin($fin) {
			$this->fin = $fin;
		}

		function copyRep($rep = null, $newRep = null) {
			$result = clone($this);
			$result->state = ++ar_beta_parse_lexer_node::$_STATE_COUNT;
			return $result;
		}

		function cleanRep($seen = Array(), $prevState = 0) {
			return $this;
		}

		function compile($target) {
			return $this;
		}

		function merge($other, $stack = Array()) {
			if (!isset($other)) {
				return $this;
			}
			return $this;
		}

		function copy() {
			$result = clone($this);
			$result->state = ++ar_beta_parse_lexer_node::$_STATE_COUNT;
			return $result;
		}
	}

	class ar_beta_parse_lexer_node_char extends ar_beta_parse_lexer_node {
		var $transitions = Array();

		function __construct($transitions = Array()) {
			$this->transitions = $transitions;
			$this->state = ++ar_beta_parse_lexer_node::$_STATE_COUNT;
		}

		function match($input, $offset = 0) {
			$result   = null;
			$nextNode = $this->transitions[$input[$offset]];
			if (isset($nextNode)) {
				$result = $nextNode->match($input, $offset + 1);
			}
			if (!isset($result) && $this->fin) {
				$result = $this->fin->match($input, $offset + 1);
			}
			return $result;
		}

		function inverse() {
			for ($i = 1; $i <= 128; $i++) {
				$char = chr($i);
				if (isset($this->transitions[$char])) {
					unset($this->transitions[$char]);
				} else {
					$this->transitions[$char] = false;
				}
			}
		}

		function getGroupedAndSorted() {
			$grouped = Array();
			ksort($this->transitions, SORT_STRING);
			foreach ($this->transitions as $char => $stateNode) {
				if (!$grouped[$stateNode->state]) {
					$grouped[$stateNode->state] = Array($char => $stateNode);
				} else {
					$group = &$grouped[$stateNode->state];
					foreach ($group as $index => $frop);
					$index = (string)$index;
					if (strlen($index) < 2) {
						if (ord($index[0]) + 1 != ord($char)) {
							$group[$char] = $stateNode;
						} else {
							$group[$index.'-'.$char] = $stateNode;
							unset($group[$index]);
						}
					} else {
						if (ord($index[2]) + 1 != ord($char)) {
							$group[$char] = $stateNode;
						} else {
							$group[$index[0].'-'.$char] = $stateNode;
							unset($group[$index]);
						}
					}
				}
			}
			return $grouped;
		}

		function merge($other, $stack = Array()) {
			if (!isset($other)) {
				return $this;
			}
			$result = clone($this);
			$result->state = ++ar_beta_parse_lexer_node::$_STATE_COUNT;
			if (get_class($other) == get_class()) {
				if ($this->fin !== $other->fin) {
					if (!isset($this->fin)) {
						$result->fin = $other->fin;
					}
				}
				$mergedList = Array();
				foreach ($this->transitions as $char => $target) {
					if (isset($other->transitions[$char]) && $other->transitions[$char] !== $target) {
						$tState = $this->transitions[$char]->state;
						$oState = $other->transitions[$char]->state;
						$merged = $mergedList[$tState][$oState];
						if (!isset($merged)) {
							$merged = $this->transitions[$char]->merge($other->transitions[$char], &$stack);
							$mergedList[$tState][$oState] = $merged;
						}
						$result->transitions[$char] = $merged;
					}
				}
				foreach ($other->transitions as $char => $target) {
					if (!isset($result->transitions[$char])) {
						$result->transitions[$char] = $target;
					}
				}
			} else if (get_class($other) === 'ar_beta_parse_lexer_node_fin') {
				$result->setFin($other);
			} else if (get_class($other) === 'ar_beta_parse_lexer_node_rep') {
				$result = $this->merge($other->unrollRep(), &$stack);
			}
			return $result;
		}

		function compile($target) {
			foreach (array_keys($this->transitions) as $char) {
				$this->transitions[$char] = $target;
			}
			return $this;
		}

		function copyRep($rep = null, $newRep = null) {
			$result = clone($this);
			$result->state = ++ar_beta_parse_lexer_node::$_STATE_COUNT;

			$done = Array();
			foreach ($result->transitions as $transition => $nState) {
				if ($nState === $rep) {
					$result->transitions[$transition] = $newRep;
				} else {
					$newState = $done[$this->state][$nState->state];
					if (!$newState) {
						$newState = $nState->copyRep($rep, $newRep);
						$done[$this->state][$nState->state] = $newState;
					}
					$result->transitions[$transition] = $newState;
				}
			}
			return $result;
		}

		function cleanRep($seen = Array(), $prevState = 0) {
			if (is_array($this->transitions)) {
				foreach ($this->transitions as $char => $nState) {
					$this->transitions[$char] = $nState->cleanRep(&$seen, $this->state);
				}
			}
			return $this;
		}

		function copy() {
			$result = parent::copy();
			if (is_array($this->transitions)) {
				foreach ($this->transitions as $char => $nState) {
					if (is_object($nState)) {
						$result->transitions[$char] = $nState->copy();
					}
				}
			}
			return $result;
		}
	}


	class ar_beta_parse_lexer_node_rep extends ar_beta_parse_lexer_node {
		var $nodes;
		var $groupId;

		function __construct($nodes = null) {
			$this->nodes = $nodes;
			$this->state = ++ar_beta_parse_lexer_node::$_STATE_COUNT;
		}

		function match($input, $offset = 0) {
			$result = $this->nodes->match($input, $offset);
			if (!isset($result) && $this->fin) {
				$result = $this->fin->match($input, $offset + 1);
			}
			return $result;
		}

		function merge($other, $stack = Array()) {
			if (!isset($other)) {
				return $this;
			}
			if (get_class($other) == get_class()) {
				$groupNode = $stack['groups'][$this->id][$other->id];
				if (!isset($groupNode)) {
					$groupNode = new ar_beta_parse_lexer_node_rep();
					$groupNode->id = $groupNode->state;
					$stack['groups'][$this->id][$other->id] = $groupNode;
					if ($stack['complement']) {
						$result = $this->copyRep();
						$groupNode->nodes = $result->merge($other->unrollRep(), &$stack);
					} else {
						$groupNode->nodes = $this->unrollRep()->merge($other->unrollRep(), &$stack);
					}
					$result = $groupNode->cleanRep();
				} else {
					$result = $groupNode;
				}
			} else if ($stack['complement']) {
				$result = $this->copyRep();
				$result->nodes = $result->nodes->merge($other, &$stack);
			} else {
				$result = $this->unrollRep()->merge($other, &$stack);
			}
			return $result;
		}

		function copyRep($rep = null, $newRep = null) {
			$result = clone($this);
			$result->state = ++ar_beta_parse_lexer_node::$_STATE_COUNT;
			if (!isset($newRep)) {
				$result->nodes = $this->nodes->copyRep($this, $result);
			}
			return $result;
		}

		function unrollRep() {
			return $this->nodes;
		}

		function cleanRep($seen = Array(), $prevState = 0) {
			$result = $this;
			$seen[$this->state] += 1;
			if ($seen[$this->state] == 1) {
				$nodes = $this->nodes->cleanRep(&$seen, $this->state);
				if ($seen[$this->state] > 1) {
					$result = $this;
					$result->nodes = $nodes;
				} else {
					$result = $nodes;
				}
			}
			return $result;
		}

		function compile($target) {
			$this->id = $this->state;
			$this->nodes = $this->nodes->compile($this);
			$result = $this->merge($target, Array('complement' => true))->cleanRep();
			return $result;
		}


		function copy() {
			$result = parent::copy();
			$result->nodes = $result->nodes->copy();
			return $result;
		}

	}

	class ar_beta_parse_lexer_node_fin extends ar_beta_parse_lexer_node {
		var $value = '';

		function __construct($value) {
			$this->state = ++ar_beta_parse_lexer_node::$_STATE_COUNT;
			$this->value = $value;
		}

		function match($input, $offset = 0) {
			return Array(
				'matched' => $this,
				'offset'  => $offset
			);
		}

		function setFin($node) {
			// SAY WHAT?!
		}

		function merge($other, $stack = Array()) {
			$result = clone($other);
			$result->state = ++ar_beta_parse_lexer_node::$_STATE_COUNT;
			$result->setFin($this);
			return $result;
		}

		function copy() {
			return $this;
		}
	}

	class ar_beta_parse_lexer_node_and extends ar_beta_parse_lexer_node {
		var $left, $right;

		function __construct($left, $right) {
			$this->left = $left;
			$this->right = $right;
		}

		function compile($target) {
			return $this->left->compile($this->right->compile($target));
		}

		function copy() {
			$result = parent::copy();
			$result->left = $result->left->copy();
			$result->right = $result->right->copy();
			return $result;
		}
	}

	class ar_beta_parse_lexer_node_or extends ar_beta_parse_lexer_node_and {

		function merge($other, $stack = Array()) {
			return $other;
		}

		function compile($target) {
			$this->right = $this->right->compile($target);
			$this->left = $this->left->compile($target);
			return $this->left->merge($this->right);
		}

	}

	class ar_beta_parse_lexer_node_try extends ar_beta_parse_lexer_node {
		var $nodes;

		function __construct($nodes) {
			$this->nodes = $nodes;
		}

		function compile($target) {
			return $this->nodes->compile($target)->merge($target, Array('complement' => true));
		}

		function copy() {
			$result = parent::copy();
			$result->nodes = $result->nodes->copy();
			return $result;
		}
	}


	class ar_beta_parse_lexer_parser {
		static $_INITED = false;
		static $_CHAR = Array();
		static $_CHAR_LIST = Array();
		static $_CHAR_ANY = Array();

		protected function init() {
			ar_beta_parse_lexer_parser::$_INITED = true;

			for ($c = 1; $c <= 128; $c++) {
				$char = chr($c);
				if (!in_array($char, Array('[', '(', ')', '|', '.', '+', '*', '?'))) {
					ar_beta_parse_lexer_parser::$_CHAR[$char] = $char;
				}
				if (!in_array($char, Array(']'))) {
					ar_beta_parse_lexer_parser::$_CHAR_LIST[$char] = $char;
				}
				ar_beta_parse_lexer_parser::$_CHAR_ANY[$char] = false;
			}
		}

		protected function parseElement($input, &$offset) {
			$char = ar_beta_parse_lexer_parser::parseChar(ar_beta_parse_lexer_parser::$_CHAR, $input, $offset);
			if (isset($char)) {
				$result = new ar_beta_parse_lexer_node_char(Array($char => false));
			} else if ($input[$offset] === '.') {
				$offset++;
				$result = new ar_beta_parse_lexer_node_char(ar_beta_parse_lexer_parser::$_CHAR_ANY);
			} else if ($input[$offset] === '[') {
				$offset++;
				if ($input[$offset] === '^') {
					$offset++;
					$inv = true;
				}
				$result = new ar_beta_parse_lexer_node_char(ar_beta_parse_lexer_parser::parseCharList($input, $offset));
				if ($input[$offset++] != ']') {
					throw new Exception("Expected ']' at offset $offset");
				}
				if ($inv) {
					$result->inverse();
				}
			} else if ($input[$offset] === '(') {
				$offset++;
				$result = ar_beta_parse_lexer_parser::parseExpression($input, $offset);
				if ($input[$offset++] != ')') {
					throw new Exception("Expected ')' instead of '".$input[$offset-1]."' at offset $offset");
				}
			}
			return $result;
		}

		protected function parseChar($list, $input, &$offset) {
			$char = $input[$offset];
			if (isset($list[$char])) {
				$offset++;
				if ($char === '\\') {
					$char = $input[$offset++];
					if ($char === 'n') {
						$char = "\n";
					} else if ($char === 'r') {
						$char = "\r";
					} else if ($char === 't') {
						$char = "\t";
					}
				}
				return $char;
			}
			return null;
		}

		protected function parseCharList($input, &$offset) {
			$result = Array();
			$char   = ar_beta_parse_lexer_parser::parseChar(ar_beta_parse_lexer_parser::$_CHAR_LIST, $input, $offset);
			if (isset($char)) {
				if ($input[$offset] === '-') {
					$offset++;
					$charEnd = ar_beta_parse_lexer_parser::parseChar(ar_beta_parse_lexer_parser::$_CHAR_LIST, $input, $offset);
					if (isset($charEnd)) {
						for ($i = ord($char); $i <= ord($charEnd); $i++) {
							$result[chr($i)] = false;
						}
						$result = $result + ar_beta_parse_lexer_parser::parseCharList($input, $offset);
					} else {
						$result[$char] = false;
						$result['-'] = false;
					}
				} else {
					$result[$char] = false;
					$result = $result + ar_beta_parse_lexer_parser::parseCharList($input, $offset);
				}
			}
			return $result;
		}

		protected function parseElements($input, &$offset) {
			$element = ar_beta_parse_lexer_parser::parseElement($input, $offset);
			if ($element) {
				$char = $input[$offset];
				switch ($char) {
					case '*':
						$offset++;
						$element = new ar_beta_parse_lexer_node_rep($element);
					break;
					case '?':
						$offset++;
						$element = new ar_beta_parse_lexer_node_try($element);
					break;
					case '+':
						$offset++;
						$copy    = $element->copy();
						$element = new ar_beta_parse_lexer_node_and($copy, new ar_beta_parse_lexer_node_rep($element, true));
					break;
				}
				$element2 = ar_beta_parse_lexer_parser::parseElements($input, $offset);
				if ($element2) {
					$result = new ar_beta_parse_lexer_node_and($element, $element2);
				} else {
					$result = $element;
				}
			}
			return $result;
		}

		protected function parseExpression($input, &$offset) {
			$elements = ar_beta_parse_lexer_parser::parseElements($input, $offset);
			if (!$elements) {
				throw new Exception("No element found at offset $offset");
			}
			$char = $input[$offset];
			if ($char == '|') {
				$offset++;
				$result = new ar_beta_parse_lexer_node_or($elements, ar_beta_parse_lexer_parser::parseExpression($input, $offset));
			} else {
				$result = $elements;
			}
			return $result;
		}

		public function parse($input) {
			ar_beta_parse_lexer_parser::init();
			$result = ar_beta_parse_lexer_parser::parseExpression($input, $offset = 0);
			return $result;
		}

	}
