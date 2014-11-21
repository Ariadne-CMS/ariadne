<?php
	class nodeFactory {

		public static function createNode($type, $data = array()) {
			switch ($type) {
				case N_OR:
					return new nodeOr($data);
				break;
				case N_AND:
					return new nodeAnd($data);
				break;
				case N_IDENT:
					return new nodeIdent($data);
				break;
				case N_REPEAT:
					return new nodeRepeat($data);
				break;
				case N_RECURSE:
					return new nodeRecurse($data);
				break;
			}
		}

	}

	abstract class node {
		protected $min;
		protected $max;
		protected $type;
		protected $req;
		protected $size;
		protected $greedy;

		abstract public function __construct($data);
		abstract public function run($count, $offset);

	}

	class nodeOr extends node {

		public function __construct($data) {
			$nodeLeft = $data['nodeLeft']; $nodeRight = $data['nodeRight'];
			if ($nodeLeft || $nodeRight) {
				if ($nodeRight && $nodeRight->type == N_OR) {
					if (!$nodeLeft || $nodeRight->left && $nodeRight->left->min > $nodeLeft->min) {
						$newNodeLeft	= $nodeRight->left;
						$nodeRight		= nodeFactory::createNode(N_OR, Array('nodeLeft' => $nodeLeft, 'nodeRight' => $nodeRight->right));
						$nodeLeft		= $newNodeLeft;
					}
				}
				$this->greedy	= $data['greedy'];
				$this->req		= $nodeLeft->req & $nodeRight->req;
				if (!$nodeLeft || ($nodeRight && $nodeRight->min > $nodeLeft->min)) {
					$this->min		= ($nodeLeft) ? $nodeLeft->min : 0;
					$this->max		= (!$nodeRight->max ) ? $nodeRight->max : max($nodeRight->max, $nodeLeft->max);
					$this->size		= max($nodeRight->size, $nodeLeft->size);
					$this->left		= $nodeRight;
					$this->right	= $nodeLeft;
				} else if (!$nodeRight || ($nodeLeft && $nodeLeft->min >= $nodeRight->min)) {
					$this->min		= ($nodeRight) ? $nodeRight->min : 0;
					$this->max		= (!$nodeLeft->max ) ? $nodeLeft->max : max($nodeRight->max, $nodeLeft->max);
					$this->size		= max($nodeRight->size, $nodeLeft->size);
					$this->left		= $nodeLeft;
					$this->right	= $nodeRight;
				}
			}

		}

		public function run($count, $offset) {
//			echo "OR(count: $count; offset: $offset;)\n";
			if ($this->left->min <= $count) {
				return $this->left->run($count, $offset);
			} else {
				return $this->right->run($count, $offset);
			}
		}

	}

	class nodeAnd extends node {

		public function __construct($data) {
			$nodeLeft = $data['nodeLeft']; $nodeRight = $data['nodeRight'];
			$this->greedy		= $data['greedy'];
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

		public function run($count, $offset) {
//			echo "AND(count: $count; offset: $offset;)\n";
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

	class nodeIdent extends node {

		public function __construct($data) {
			$this->greedy		= $data['greedy'];
			$this->value		= $data['value'];
			$this->req			= true;
			$this->min			= 1;
			$this->max			= 1;
			$this->size			= 1;
		}

		public function run($count, $offset) {
//		echo "(<b>".$this->value."</b>: count: $count; offset: $offset;)\n";
			if ($offset == 0) {
				return $this->value;
			} else {
				return $count - 1;
			}
		}

	}

	class nodeRepeat extends node {

		public function __construct($data) {
			$nodeLeft			= $data['nodeLeft'];
			$this->greedy		= $data['greedy'];
			$this->req			= $data['req'];
			$this->min			= $data['min'];
			$this->max			= $data['max'];
			$this->size			= $data['size'];
			$this->minRep		= $data['minRep'];
			$this->maxRep		= $data['maxRep'];
			$this->left			= $nodeLeft;
		}

		public function run($count, $offset) {
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

	class nodeRecurse extends node {

		public function __construct($data) {
			$this->size		= 1;
			$this->min		= 1;
			$this->req		= 1;
		}

		public function setNode(&$node) {
			$this->left		= $node;
			$this->min		= $this->left->min;
			$this->max		= $this->left->max;
			$this->size 	= $this->left->size;
			$this->greedy	= $this->left->greedy;
			$this->req		= $this->left->req;
		}

		public function run($count, $offset) {
			static $frop;
			if (!$frop) {
				$frop = 0;
			}
			$frop++;
			if ($frop > 1000) {
				return "?";
			}
			//echo "count: $count; offset: $offset;\n";
			$result = $this->left->run($count, $offset);
			$frop--;
			return $result;
		}

	}
