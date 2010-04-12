<?php
	class ar_core_store {
	
		static public function create( $storeOptions ) {
			require_once($storeOptions['code'].'stores/'.$storeOptions['dbms'].'store.phtml');
			$storeName = $storeOptions['dbms'].'store';
			$store     = new $storeName($storeOptions['root'], $storeOptions);
			return $store;
		}

	}
?>