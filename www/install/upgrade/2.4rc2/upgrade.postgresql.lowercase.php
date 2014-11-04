<?php
	$seen = array();
	$query = "select a.path as apath ,b.path as bpath from ".$store->tbl_prefix."nodes as a, ".$store->tbl_prefix."nodes as b where lower(a.path) = lower(b.path) and a.path  != b.path;";
	$testresult =  $store->store_run_query($query);
	if(@pg_numrows($testresult) > 0){
		$error ="There are conflicting objects in the store, upgrade failt";
		while($row = pg_fetch_assoc($testresult)){
			if(!$seen[$row['bpath']]) {
				$error .= "</br>\n ".$row['apath']." conflicts with ".$row['bpath'];
				$seen[$row['apath']] = $row['bpath'];
			}
		}
	} else {
		// addapting the store
		$query = "CREATE UNIQUE INDEX ".$store->tbl_prefix."nodes_path_lower ON ".$store->tbl_prefix."nodes (lower(path));";
		$store->store_run_query($query);
		$query = "CREATE INDEX ".$store->tbl_prefix."nodes_parent_lower ON ".$store->tbl_prefix."nodes (lower(parent));";
		$store->store_run_query($query);
	}
?>