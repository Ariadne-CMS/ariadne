<pre>
<?php
$importpath = '/system/lib/muze/';

if( $importpath) {
	print "importing $importpath\n";
	$ARCurrent->nolangcheck = true;
	$ARCurrent->options["verbose"]=true;
	$ARCurrent->options["force"]=true;
	$ARCurrent->AXAction == "import";

	// become admin
	$AR->user=new baseObject;
	$AR->user->data=new baseObject;
	$AR->user->data->login=$ARLogin="admin";

	$ax_config["writeable"]=false;
	$ax_config["database"]="../packages/lib.ax";
	echo "ax file (".$ax_config["database"].")\n";
	set_time_limit(0);
	$inst_store = $ax_config["dbms"]."store";
	$axstore=new $inst_store("", $ax_config);
	if (!$axstore->error) {
		$ARCurrent->importStore=&$store;
		$args['srcpath'] = $importpath;
		$args['destpath'] = $importpath;
		$axstore->call("system.export.phtml", $args, $axstore->get("/"));
		$error=$axstore->error;
		$axstore->close();
	} else {
		$error=$axstore->error;
	}
}

$importpath = '/system/lib/vedor/';

if($importpath) {
	print "importing $importpath\n";
	$ARCurrent->nolangcheck = true;
	$ARCurrent->options["verbose"]=true;
	$ARCurrent->options["force"]=true;
	$ARCurrent->AXAction == "import";

	// become admin
	$AR->user=new baseObject;
	$AR->user->data=new baseObject;
	$AR->user->data->login=$ARLogin="admin";

	$ax_config["writeable"]=false;
	$ax_config["database"]="../packages/lib.ax";
	echo "ax file (".$ax_config["database"].")\n";
	set_time_limit(0);
	$inst_store = $ax_config["dbms"]."store";
	$axstore=new $inst_store("", $ax_config);
	if (!$axstore->error) {
		$ARCurrent->importStore=&$store;
		$args['srcpath'] = $importpath;
		$args['destpath'] = $importpath;
		$axstore->call("system.export.phtml", $args, $axstore->get("/"));
		$error=$axstore->error;
		$axstore->close();
	} else {
		$error=$axstore->error;
	}
}

?>
</pre>
