<pre>
<?php
	require_once("../init_database_data.php");
	require_once($store_config['code']."stores/".$store_config["dbms"]."store_install.phtml");

	$store->upgradeNodes();
	foreach ($properties as $name => $property) {
		echo "\tAltering store_prop_$name\n";
		$store->alter_property($name, $property);
	}

	$session_store = $session_config["dbms"]."store_install";
	$sessionstore=new $session_store(".",$session_config);
	$sessionstore->upgradeNodes();


	$cache_store = $cache_config["dbms"]."store_install";
	$cachestore=new $cache_store(".",$cache_config);

	$cachestore->upgradeNodes();
	foreach ($cacheproperties as $name => $property) {
		echo "\tAltering cache_prop_$name\n";
		$cachestore->alter_property($name, $property);
	}

?>
</pre>
