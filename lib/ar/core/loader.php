<?php

	class ar_core_loader {

		public static function http($options, $ariadne = null, $session = null, $nls = null) {
			ar_error::$throwExceptions = true;
			if ( !isset($ariadne) ) {
				$ariadne = ar_core_ariadne::create( $options['AR'] );
			}
			if (!isset($session)) {
				if (!isset($options['session']['hideSessionIDFromURL'])) {
					$options['session']['hideSessionIDFromURL'] = $options['AR']->hideSessionIDFromURL;
				}
				$session = new ar_core_session($options['session']);
			}
			if (!isset($nls)) {
				$nlsOptions       = $options['AR']->nls;
				$nlsOptions->root = $options['AR']->dir->install.'lib/nls/';
				$nls = new ar_core_nls( $nlsOptions );
			}
			return new ar_core_loader_http($options, $ariadne, $session, $nls);
		}

	}

	interface ar_core_loaderInterface {
		public function __construct($options = null, $ariadne = null, $session = null, $nls = null);
		public function getRequest();
		public function handleRequest( $request=null );
		public function run();
		public function handleException( $exception );
		public function isCacheable();
	}
