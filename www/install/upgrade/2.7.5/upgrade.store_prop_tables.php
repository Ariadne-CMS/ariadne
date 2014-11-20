<pre>
<?php
	require_once("../init_database_data.php");
	require_once($store_config['code']."stores/".$store_config["dbms"]."store_install.phtml");

//	$inst_store = $store_config["dbms"]."store_install";
//	$upgradeStore = new $inst_store(".",$store_config);
	foreach ($properties as $name => $property) {
		echo "\tAltering store_prop_$name\n";
		$store->alter_property($name, $property);
	}

?>
</pre>
