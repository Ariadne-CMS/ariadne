<?php
#[\AllowDynamicProperties]
class ar_core_pinpSandbox extends arBase {
	public $this;
	public $data;
	public $customdata;
	public $nlsdata;
	public $customnlsdata;
	public $path;
	public $parent;
	public $type;
	public $vtype;
	public $priority;
	public $id;
	public $lastchanged;
	public $size;
	public $nls;
	public $reqnls;
	public $arIsNewObject;
	public $ARnls;
	public $arConfig;

	public function __construct($scope) {
		$properties = array(
			"data",
			"customdata",
			"nlsdata",
			"customnlsdata",
			"path",
			"parent",
			"type",
			"vtype",
			"priority",
			"id",
			"lastchanged",
			"size",
			"nls",
			"reqnls",
			"arIsNewObject",
			"ARnls"
		);
		
		$this->this          = $scope;
		foreach ($properties as $property) {
			$this->{$property} = $scope->{$property} ?? null;
		}
	}

	private function isSafeCallable( $callable ) {
		return ( !isset($callable) || $callable instanceof \Closure );
	}

	public function usort(array &$arr, callable $f) {
		if ( $this->isSafeCallable($f) ) {
			return usort($arr, $f);
		} else {
			return false;
		}
	}

	public function uasort(array &$arr, callable $f) {
		if ( $this->isSafeCallable($f) ) {
			return uasort($arr, $f);
		} else {
			return false;
		}
	}

	public function uksort(array &$arr, callable $f) {
		if ( $this->isSafeCallable($f) ) {
			return uksort($arr, $f);
		} else {
			return false;
		}
	}

	public function array_walk(array &$arr, callable $f) {
		if ( $this->isSafeCallable($f) ) {
			return array_walk($arr, $f);
		} else {
			return false;
		}
	}

	public function array_walk_recursive(array &$arr, callable $f) {
		if ( $this->isSafeCallable($f) ) {
			return array_walk_recursive($arr, $f);
		} else {
			return false;
		}
	}

	public function __call($function, $args){
		if ( $function[0] === '_' ) {
			// variable called as a function
			$function = substr($function,1);
			if( isset($this->{$function}) && $this->{$function} instanceof \Closure ) {
				return call_user_func_array($this->{$function}, $args);
			} else {
				return ar_error::raiseError("Function is not a closure",500);
			}
		} else {
			//function not in whitelist
			switch($function) {
				// first arg must be a closure
				case 'array_map':
					if ( $this->isSafeCallable($args[0])) {
						return call_user_func_array( $function, $args );
					}
				break;
				// second arg must be a closure
				case 'array_filter':
				case 'array_reduce':
				case 'preg_replace_callback':
					if ( $this->isSafeCallable($args[1] ?? null)) {
						return call_user_func_array( $function, $args );
					}
				break;
				// last arg must be a closure
				case 'array_diff_uassoc':
				case 'array_diff_ukey':
				case 'array_intersect_uassoc':
				case 'array_intersect_ukey':
				case 'array_udiff':
				case 'array_udiff_assoc':
					$l = count($args);
					if ( $this->isSafeCallable($args[$l-1] ?? null) ) {
						return call_user_func_array( $function, $args );
					}
				break;
				// last two args must be a closure
				case 'array_udiff_uassoc':
				case 'array_uintersect_uassoc':
					$l = count($args);
					if ( $this->isSafeCallable($args[$l-1] ?? null) && $this->isSafeCallable( $args[$l-2] ?? null ) ) {
						return call_user_func_array( $function, $args );
					}
				break;
			}

			$function = '_'.$function;
			if ( is_callable( [ $this->this, $function ] ) ) {
				return call_user_func_array( [ $this->this, $function ], $args );
			}
 
			return ar_error::raiseError("Function is not a method",500);
		}
	}

}
