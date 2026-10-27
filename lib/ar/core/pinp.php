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

	public function mail($to, $subject, $message, $additional_headers = array(), $additional_params = '') {
		// Never pass template-controlled command-line options to the mail transport.
		if ($additional_params !== '' && $additional_params !== null) { return false; }
		return $this->sendMail($to, $subject, $message, $additional_headers);
	}

	protected function sendMail($to, $subject, $message, $additional_headers) {
		return mail($to, $subject, $message, $additional_headers);
	}

	public function preg_replace_callback($pattern, $callback, $subject, $limit = -1, &$count = null, $flags = 0) {
		if (!$callback instanceof \Closure) { return false; }
		return preg_replace_callback($pattern, $callback, $subject, $limit, $count, $flags);
	}

	public function preg_replace_callback_array($patterns, $subject, $limit = -1, &$count = null, $flags = 0) {
		foreach ($patterns as $callback) {
			if (!$callback instanceof \Closure) { return false; }
		}
		return preg_replace_callback_array($patterns, $subject, $limit, $count, $flags);
	}

	private function isSafeFilter($filter, $options) {
		return (int)$filter !== FILTER_CALLBACK
			|| (is_array($options) && ($options['options'] ?? null) instanceof \Closure);
	}

	private function areSafeFilters($options) {
		if (!is_array($options)) { return $this->isSafeFilter($options, null); }
		foreach ($options as $specification) {
			$filter = is_array($specification) ? ($specification['filter'] ?? FILTER_DEFAULT) : $specification;
			if (!$this->isSafeFilter($filter, $specification)) { return false; }
		}
		return true;
	}

	public function filter_var($value, $filter = FILTER_DEFAULT, $options = 0) {
		if (!$this->isSafeFilter($filter, $options)) { return false; }
		return filter_var($value, $filter, $options);
	}

	public function filter_var_array($array, $options = FILTER_DEFAULT, $add_empty = true) {
		if (!$this->areSafeFilters($options)) { return false; }
		return filter_var_array($array, $options, $add_empty);
	}

	public function filter_input($type, $name, $filter = FILTER_DEFAULT, $options = 0) {
		// Server/environment input can disclose credentials outside template scope.
		if (!in_array((int)$type, array(INPUT_GET, INPUT_POST, INPUT_COOKIE), true)
			|| !$this->isSafeFilter($filter, $options)) { return false; }
		return filter_input($type, $name, $filter, $options);
	}

	public function filter_input_array($type, $options = FILTER_DEFAULT, $add_empty = true) {
		if (!in_array((int)$type, array(INPUT_GET, INPUT_POST, INPUT_COOKIE), true)
			|| !$this->areSafeFilters($options)) { return false; }
		return filter_input_array($type, $options, $add_empty);
	}

	public function stream_context_create($options = null, $params = null) {
		if (isset($params['notification']) && !$params['notification'] instanceof \Closure) { return false; }
		return stream_context_create($options, $params);
	}

	public function stream_context_set_params($context, $params) {
		if (isset($params['notification']) && !$params['notification'] instanceof \Closure) { return false; }
		return stream_context_set_params($context, $params);
	}

	public function is_callable($value, $syntax_only = false, &$callable_name = null) {
		if (!$value instanceof \Closure) { $callable_name = ''; return false; }
		return is_callable($value, $syntax_only, $callable_name);
	}

	private function isLoadedClass($class) {
		return is_string($class) && (class_exists($class, false) || interface_exists($class, false)
			|| trait_exists($class, false));
	}

	public function class_exists($class, $autoload = false) { return class_exists($class, false); }
	public function interface_exists($class, $autoload = false) { return interface_exists($class, false); }
	public function trait_exists($class, $autoload = false) { return trait_exists($class, false); }
	public function enum_exists($class, $autoload = false) { return enum_exists($class, false); }

	public function get_class_methods($object_or_class) {
		return is_object($object_or_class) || $this->isLoadedClass($object_or_class)
			? get_class_methods($object_or_class) : false;
	}

	public function get_parent_class($object_or_class) {
		return is_object($object_or_class) || $this->isLoadedClass($object_or_class)
			? get_parent_class($object_or_class) : false;
	}

	public function method_exists($object_or_class, $method) {
		return (is_object($object_or_class) || $this->isLoadedClass($object_or_class))
			&& method_exists($object_or_class, $method);
	}

	public function property_exists($object_or_class, $property) {
		return (is_object($object_or_class) || $this->isLoadedClass($object_or_class))
			&& property_exists($object_or_class, $property);
	}

	public function is_a($object_or_class, $class, $allow_string = false) {
		if (is_string($object_or_class) && (!$allow_string || !$this->isLoadedClass($object_or_class))) { return false; }
		return is_a($object_or_class, $class, $allow_string);
	}

	public function is_subclass_of($object_or_class, $class, $allow_string = true) {
		if (is_string($object_or_class) && (!$allow_string || !$this->isLoadedClass($object_or_class))) { return false; }
		return is_subclass_of($object_or_class, $class, $allow_string);
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
			// PHP function names are case-insensitive; closure variable names are not.
			$function = strtolower($function);
			//function not in whitelist
			switch($function) {
				// first arg must be a closure
				case 'array_map':
					if ( $this->isSafeCallable($args[0])) {
						return call_user_func_array( $function, $args );
					}
				return false;
				// second arg must be a closure
				case 'array_filter':
				case 'array_reduce':
				case 'array_find':
				case 'array_find_key':
				case 'array_any':
				case 'array_all':
				case 'mb_ereg_replace_callback':
					if ( $this->isSafeCallable($args[1] ?? null)) {
						return call_user_func_array( $function, $args );
					}
				return false;
				// last arg must be a closure
				case 'array_diff_uassoc':
				case 'array_diff_ukey':
				case 'array_intersect_uassoc':
				case 'array_intersect_ukey':
				case 'array_udiff':
				case 'array_udiff_assoc':
				case 'array_uintersect':
				case 'array_uintersect_assoc':
					$l = count($args);
					if ( $this->isSafeCallable($args[$l-1] ?? null) ) {
						return call_user_func_array( $function, $args );
					}
				return false;
				// last two args must be a closure
				case 'array_udiff_uassoc':
				case 'array_uintersect_uassoc':
					$l = count($args);
					if ( $this->isSafeCallable($args[$l-1] ?? null) && $this->isSafeCallable( $args[$l-2] ?? null ) ) {
						return call_user_func_array( $function, $args );
					}
				return false;
			}

			$function = '_'.$function;
			if ( is_callable( [ $this->this, $function ] ) ) {
				return call_user_func_array( [ $this->this, $function ], $args );
			}
 
			return ar_error::raiseError("Function is not a method",500);
		}
	}

}
