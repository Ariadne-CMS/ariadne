<?php
class ar_core_pinpSandbox extends arBase {
	private $this;

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
		$this->store = $scope->store;
		$this->ARnls = $scope->ARnls;
	}

	public function __get($name) {
		return $this->{$name};
	}

	public function __set($name, $value) {
		if( $name === 'this' ) {
			throw new \InvalidArgumentException("can't assign value to \$this");
		}
		if( $value instanceof \Closure ) {
			$value = \Closure::bind($value, $this);
		}

		$this->{$name} = $value;
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
			$function = '_'.$function;
			if(is_callable( [ $this->this,$function ])) {
				return call_user_func_array( [ $this->this, $function], $args);
			} else {
				return ar_error::raiseError("Function is not a method",500);
			}
		}
	}

	public function array_map($function, $a) {
		$args = func_get_args();
		if( isset($function) && $function instanceof \Closure ) {
			return call_user_func_array('array_map', $args);
		} else {
			return ar_error::raiseError("Function is not a closure",500);
		}
	}

}