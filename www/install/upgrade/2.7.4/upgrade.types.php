<pre>
<?php
	if (!$store->exists("/system/ariadne/types/pproject/")) {
		echo "Installing pproject\n";

		// install pproject type

		$store->add_type("pproject", "pobject");
		$store->add_type("pproject", "ppage");
		$store->add_type("pproject", "pdir");
		$store->add_type("pproject", "psection");
		$store->add_type("pproject", "pproject");

	}

?></pre>
