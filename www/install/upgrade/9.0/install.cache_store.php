<pre>
<?php
	// cache store

	$inst_store = $cache_config["dbms"]."store_install";
	$cachestore=new $inst_store(".",$cache_config);

	require_once("../init_database_data.php");
	require_once($store_config['code']."stores/".$store_config["dbms"]."store_install.phtml");

	echo "== creating Ariadne Cache Store\n\n";
	if ($cachestore->initialize()) {

		foreach ($cacheproperties as $name => $property) {
			$cachestore->create_property($name, $property);
		}
		$cachestore->add_type("pcache","pobject");
		$cachestore->add_type("pcache","pcache");

		$cachestore->save( '/', 'pobject', new object );
	} else {
		error("store not initialized.");
	}
	$cachestore->close();
?>
</pre>
