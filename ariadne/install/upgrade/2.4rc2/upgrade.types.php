<pre>
<?php
	if (!$store->exists("/system/ariadne/types/pbookmark/")) {
		echo "Installing pbookmark\n";

		$store->add_type("pbookmark", "pobject");
		$store->add_type("pbookmark", "pbookmark");
	}

	if (!$store->exists("/system/ariadne/types/pldapconnection/")) {
		echo "Installing pldapconnection\n";
		// install pconnector type

		$store->add_type("pconnector", "pobject");
		$store->add_type("pconnector", "ppage");
		$store->add_type("pconnector", "pdir");
		$store->add_type("pconnector", "pconnector");

		$store->add_type("pldapconnection", "pobject");
		$store->add_type("pldapconnection", "ppage");
		$store->add_type("pldapconnection", "pdir");
		$store->add_type("pldapconnection", "pconnector");
		$store->add_type("pldapconnection", "pldapconnection");
	}

	if (!$store->exists("/system/ariadne/types/psearch/")) {
		echo "Installing psearch\n";

		$store->add_type("psearch","pobject");
		$store->add_type("psearch","ppage");
		$store->add_type("psearch","pdir");
		$store->add_type("psearch","psearch");
	}

	if (!$store->exists("/system/ariadne/types/psection/")) {
		echo "Installing psection\n";

		$store->add_type("psection", "pobject");
		$store->add_type("psection", "ppage");
		$store->add_type("psection", "pdir");
		$store->add_type("psection", "psection");
	}

		echo "== importing types.ax file\n\n";
		$ARCurrent->nolangcheck = true;
		$ARCurrent->options["verbose"]=true;
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
			$args="srcpath=/system/ariadne/types/&destpath=/system/ariadne/types/";
			$axstore->call("system.export.phtml", $args,
				$axstore->get("/"));
			$error=$axstore->error;
			$axstore->close();
		} else {
			$error=$axstore->error;
		}

?></pre>
