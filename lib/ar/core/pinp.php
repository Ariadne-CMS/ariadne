<?php
class ar_core_pinpSandbox extends arBase {

	public function __construct($scope) {
		$this->this = $scope;
		$this->data = $scope->data;
		$this->customdata = $scope->customdata;
		$this->nlsdata = $scope->nlsdata;
		$this->customnlsdata = $scope->customnlsdata;
		$this->path = $scope->path;
		$this->parent = $scope->parent;
		$this->type = $scope->type;
		$this->vtype = $scope->vtype;
		$this->priority = $scope->priority;
		$this->id = $scope->id;
		$this->lastchanged = $scope->lastchanged;
		$this->size = $scope->size;
		$this->nls = $scope->nls;
		$this->reqnls = $scope->reqnls;
		$this->arIsNewObject = $scope->arIsNewObject;
		$this->ARnls = $scope->ARnls;
	}

	private function isSafeCallable( $callable ) {
		return ( !isset($callable) || $callable instanceof \Closure );
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
				case 'array_walk':
				case 'array_walk_recursive':
				case 'usort':
				case 'uasort':
				case 'uksort':
				case 'preg_replace_callback':
					if ( $this->isSafeCallable($args[1])) {
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
					if ( $this->isSafeCallable($args[$l-1]) ) {
						return call_user_func_array( $function, $args );
					}
				break;
				// last two args must be a closure
				case 'array_udiff_uassoc':
				case 'array_uintersect_uassoc':
					$l = count($args);
					if ( $this->isSafeCallable($args[$l-1]) && $this->isSafeCallable( $args[$l-2] ) ) {
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