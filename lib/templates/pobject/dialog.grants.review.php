<?php
//	include_once($this->store->get_config("code")."modules/mod_grant.php");
	include_once("dialog.grants.logic.php");

	$data = $this->getdata('data');
	echo "<p>The grants specified below will be set. If you are satisfied with the changes, please press 'Apply' once more to set the grants.</p>";

	echo "<table width=\"100%\">";
	echo "<tr><th>".$ARnls["path"]."</th><th>User/group</th><th>".$ARnls["grants"]."</th></tr>";
	foreach( $data as $gpath => $gusers ) {
		foreach( $gusers as $upath => $value ) {
			echo "<tr><td>".htmlspecialchars($gpath)."</td><td>".htmlspecialchars($upath)."</td>";
			if (is_array($value['grants']['array'])) {
				echo "<td>".htmlentities(grantsArrayToString($value["grants"]["array"]), ENT_QUOTES, 'UTF-8')."</td>";
			} else {
				echo "<td>".htmlentities($value['grants']['grantsstring'], ENT_QUOTES, 'UTF-8')."</td>";
			}
			echo "</tr>";
		}
	}
	echo "</table>";
?>
