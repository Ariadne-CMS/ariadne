<?php

class URL {

	function arguments($args, $prefix='') {
		if (!is_array($args)) return '';
		$str = '';
		foreach ($args as $key => $value) {
			if ($str !== '') $str.='&';
			$fullkey = ($prefix === '') ? $key : $prefix.'['.$key.']';
			$str .= is_array($value) ? $this->_query_str($value, $fullkey) : $fullkey.'='.rawurlencode($value);
		}
		if ($prefix == '' && $str !== '') $str = '?' . $str;
		return $str;
	}

}

class pinp_URL {

	function _arguments($args) {
		return URL::arguments($args);
	}

}

?>