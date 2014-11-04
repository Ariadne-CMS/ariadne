<?php

	interface ar_core_nlsInterface {
		public function __construct($options = null);
		public function parse($pathInfo);
	}

	class ar_core_nls implements ar_core_nlsInterface {
		private $default   = 'en';
		private $available = array( 'en' => 'English' );
		private $current   = 'en';
		private $accepted  = array();
		private $requested = '';
		private $root      = './';

		public function __construct($options = null) {
			if (isset($options)) {
				if (isset($options->default) ) {
					$this->default = $options->default;
				}
				if (isset($options->list)) {
					$this->list = $options->list;
				}
				if (isset($options->root)) {
					$this->root = $options->root;
				}
			}
		}

		public function __get($name) {
			switch ($name) {
				case 'current' :
				case 'requested' :
				case 'default' :
				case 'available' :
				case 'accepted' :
					return $this->{$name};
				break;
			}
		}

		public function loadtext( $nls, $section = '' ) {
			$nls      = preg_replace('/[^a-z]*/i', '', $nls);
			$section  = preg_replace('[^a-z\._-]*/i', '', $section);
			$fileName = $nls;
			if ($section) {
				$fileName = $section.'.'.$fileName;
			}
			global $ARnls;
			include($this->root.$fileName);
		}

		public function gettext( $key ) {
			global $ARnls;
			return $ARnls[$key];
		}
	}
?>