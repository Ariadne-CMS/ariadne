<pre>
<?php
	// cache store

	$cache_store = $cache_config["dbms"]."store_install";
	$cachestore=new $cache_store(".",$cache_config);

	require_once("../init_database_data.php");

	echo "== creating Ariadne Cache Store\n\n";
	if ($cachestore->initialize()) {

		foreach ($cacheproperties as $name => $property) {
			$cachestore->create_property($name, $property);
		}
		$cachestore->add_type("pcache","pobject");
		$cachestore->add_type("pcache","pcache");

		$cachestore->save( '/', 'pobject', new baseObject );
	} else {
		error("store not initialized.");
	}
	$cachestore->close();
?>
</pre>
