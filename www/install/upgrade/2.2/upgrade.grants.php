<pre>
<?php
		echo "== importing grants names\n\n";
		$ARCurrent->nolangcheck = true;
		$ARCurrent->options["verbose"]=true;

		$ax_config["writeable"]=false;
		$ax_config["database"]="2.2/system.ariadne.grants.ax";
		echo "ax file (".$ax_config["database"].")\n";
		set_time_limit(0);
		$inst_store = $ax_config["dbms"]."store";
		$axstore=new $inst_store("", $ax_config);
		if (!$axstore->error) {
			$ARCurrent->importStore=&$store;
			$args="srcpath=/system/ariadne/grants/&destpath=/system/ariadne/grants/";
			$axstore->call("system.export.phtml", $args,
				$axstore->get("/"));
			$error=$axstore->error;
			$axstore->close();
		} else {
			$error=$axstore->error;
		}
?>
</pre>
