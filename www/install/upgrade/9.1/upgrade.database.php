<pre>
<?php
	require_once("../init_database_data.php");
	require_once($store_config['code']."stores/".$store_config["dbms"]."store_install.phtml");

	$cache_store = $cache_config["dbms"]."store_install";
	$cachestore=new $cache_store(".",$cache_config);

	function updateDB($store, $properties) {
		foreach ($properties as $name => $property) {
			echo "\tAltering store_prop_$name\n";
			if ( $store->has_property($name) ) {
				$store->alter_property($name, $property);
			} else {
				$store->create_property($name, $property);
			}
		}
	}

	updateDB($store, $properties);
	updateDB($cachestore, $cacheproperties);

?>
</pre>
