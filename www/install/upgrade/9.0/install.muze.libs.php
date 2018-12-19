<pre>
<?php
$importpath = false;
echo "== testing for lib folder\n\n";
$res = current(ar::get('/system/lib/muze/')->call('system.get.phtml'));
if(!$res) {
	print "No muze libs found\n";
	$importpath = '/system/lib/muze/';
	$res = current(ar::get('/system/lib/')->call('system.get.phtml'));
	if(!$res) {
		print "No lib folder found\n";
		$importpath = '/system/lib/';
	}
} else {
	print "Old muzelibs found, upgrade ?\n";
}

if($importpath) {
	print "importing $importpath\n";
	$ARCurrent->nolangcheck = true;
	$ARCurrent->options["verbose"]=true;
	$ARCurrent->AXAction == "import";

	// become admin
	$AR->user=new baseObject;
	$AR->user->data=new baseObject;
	$AR->user->data->login=$ARLogin="admin";

	$ax_config["writeable"]=false;
	$ax_config["database"]="../packages/base.ax";
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
