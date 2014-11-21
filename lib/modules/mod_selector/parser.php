<?php
	require_once("scanner.php");

	class selectorParser {

		public function __construct($string) {
			$this->scanner = new selectorScanner($string);
			$this->scanner->next();
		}

		public function parse($greedyMatching = '!', $stack = Array()) {
			$node = $this->parseOr($greedyMatching, $stack);
			return $node;
		}

		public function parseOr($greedyMatching = '!', $stack) {
			$node = $this->parseAnd($greedyMatching, $stack);
			while ($this->scanner->token_ahead == T_OR) {
				$this->scanner->next();
				$node = nodeFactory::createNode(
							N_OR,
							Array(
								'greedy'	=> $greedyMatching,
								'nodeLeft'	=> $node,
								'nodeRight'	=> $this->parseAnd($greedyMatching, $stack)
							)
				);
			}
			return $node;
		}

		public function parseAnd($greedyMatching = '!', $stack) {
			$prevNode = null;
			while ($node = $this->parseIdent($greedyMatching, $stack)) {
					$node = $this->parseRepeat($node, $stack);
					if ($prevNode) {
						$node = nodeFactory::createNode(
									N_AND,
									Array(
										'greedy'	=> $greedyMatching,
										'nodeLeft'	=> $node,
										'nodeRight'	=> $prevNode
									)
						);
					}
					$prevNode = $node;
			}
			return $prevNode;
		}

		public function parseIdent($greedyMatching = '!', $stack) {
				$greedyMatching = $this->parseGreedyness($greedyMatching);
				switch ($this->scanner->token_ahead) {
					case T_RECURSE_DEF:
						$this->scanner->next();
						$recurseNode = nodeFactory::createNode(N_RECURSE);
						array_push($stack, $recurseNode);
							$node = $this->parse($greedyMatching, $stack);
						array_pop($stack);
						$recurseNode->setNode($node);
						$node = $recurseNode;
						if ($this->scanner->token_ahead == T_PAR_CLOSE) {
							$this->scanner->next();
						}
					break;
					case T_RECURSE_IDENT:
						$this->scanner->next();
						$node = $stack[sizeof($stack)-1];
					break;
					case T_IDENT:
						$this->scanner->next();
						$node = nodeFactory::createNode(
									N_IDENT,
									Array(
										'greedy'	=> $greedyMatching,
										'value'		=> $this->scanner->token_value
									)
						);
					break;
					case T_PAR_OPEN:
						$this->scanner->next();
						$node = $this->parse($greedyMatching, $stack);
						if ($this->scanner->token_ahead == T_PAR_CLOSE) {
							$this->scanner->next();
						}
					break;
				}

				return $node;
		}

		public function parseGreedyness($greedyMatching) {
			if ($this->scanner->token_ahead == T_NON_GREEDY) {
				$this->scanner->next();
				$greedyMatching = '?';
			} else if ($this->scanner->token_ahead == T_GREEDY) {
				$this->scanner->next();
				$greedyMatching = '!';
			} else if ($this->scanner->token_ahead == T_EQUAL_GREEDY) {
				$this->scanner->next();
				$greedyMatching = '=';
			}
			return $greedyMatching;
		}

		public function parseRepeat(&$prevNode, $stack) {
			switch($this->scanner->token_ahead) {
				case T_REP_ZERO_ONE:
					$this->scanner->next();
					$prevNode->req = false;
					$node = $this->parseRepeat($prevNode, $stack);
				break;
				case T_REP_ZERO_MORE:
					$this->scanner->next();
					$node = nodeFactory::createNode(
								N_REPEAT,
								Array(
									"greedy"	=> $prevNode->greedy,
									"minRep"	=> 1,
									"maxRep"	=> 0,
									"req"		=> false,
									"min"		=> $prevNode->min,
									"max"		=> 0,
									"size"		=> $prevNode->size,
									"nodeLeft"	=> $prevNode
								)
					);
					$node = $this->parseRepeat($node, $stack);
				break;
				case T_REP_ONE_MORE:
					$this->scanner->next();
					$node = nodeFactory::createNode(
								N_REPEAT,
								Array(
									"greedy"	=> $prevNode->greedy,
									"minRep"	=> 1,
									"maxRep"	=> 0,
									"req"		=> $prevNode->req,
									"min"		=> $prevNode->min,
									"max"		=> 0,
									"size"		=> $prevNode->size,
									"nodeLeft"	=> $prevNode
								)
					);
					$node = $this->parseRepeat($node, $stack);
				break;
				case T_REP_OPEN:
					$this->scanner->next();
					$min		= 0;
					$max		= 0;
					if ($this->scanner->token_ahead == T_NUMBER) {
						$this->scanner->next();
						$min = (int)$this->scanner->token_value;
						$max = $min;
					}
					if ($this->scanner->token_ahead == T_COMMA) {
						$this->scanner->next();
						if ($this->scanner->token_ahead == T_NUMBER) {
							$this->scanner->next();
							$max = (int)$this->scanner->token_value;
						}
					}
					if ($this->scanner->token_ahead == T_REP_CLOSE) {
						$this->scanner->next();
					}
					$node = nodeFactory::createNode(
								N_REPEAT,
								Array(
									"greedy"	=> $prevNode->greedy,
									"minRep"	=> ($min > 0) ? $min : 1,
									"maxRep"	=> $max,
									"req"		=> ($min > 0) ? $prevNode->req : false,
									"min"		=> ($min > 0) ? $min * $prevNode->min : $prevNode->min,
									"max"		=> $max * $prevNode->max,
									"size"		=> ($min) ? $min * $prevNode->size : $prevNode->size,
									"nodeLeft"	=> $prevNode
								)
					);
					$node = $this->parseRepeat($node, $stack);
				break;
				default:
					$node = $prevNode;
				break;
			}
			return $node;
		}

	}
