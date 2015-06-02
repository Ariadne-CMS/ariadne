<?php
	$ARCurrent->nolangcheck=true;
	if( $this->CheckLogin("layout") && $this->CheckConfig()) {
			$svn = $this->call("system.svn.info.php");
			if( is_array($svn) ) {
				echo "<table class=\"svninfo\">";
				$i = 0;
				$func = function($value,$key) use (&$i) {
					$id = str_replace(" ", "_", $key);
					echo "<tr class=\"".(++$i % 2 == 1 ? "odd" : "even")."\"><td class=\"svninfokey\">".ucfirst($key)."</td><td class=\"svninfovalue\" id=\"".$id."\">".$value."</td>\n";
				};
				array_walk_recursive($svn,$func);
				echo "</table>";
			}
	}
?>
