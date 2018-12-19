<pre>
<?php

function importMissingData($path) {
	global $ARCurrent, $AR, $ax_config, $store;
	$importpath = false;
	echo "== testing for $path\n\n";
	$res = current(ar::get($path)->call('system.get.phtml'));
	if(!$res) {
		print "$path does not exists\n";
		$importpath = $path;
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
}

$paths = array (
		'/system/newspaper/scenarios/fixed/',
		'/system/newspaper/displays/fixed/',
		'/system/profiles/administrators/',
		'/system/scaffolds/',
		'/projects/'
	);

foreach($paths as $path){
	importMissingData($path);
}

ar::get('/system/newspaper/scenarios/')->ls()->call('system.save.data.phtml');

?>
</pre>
