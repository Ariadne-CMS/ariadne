<?php
	$ARCurrent->nolangcheck=true;
	if( $this->CheckLogin("layout") && $this->CheckConfig()) {
			$svn = $this->call("system.svn.info.php");
			if( is_array($svn) ) {
				echo "<table class=\"svninfo\">";
				$i = 0;
				foreach( $svn as $key => $value ) {
					$id = str_replace(" ", "_", $key);
					echo "<tr class=\"".(++$i % 2 == 1 ? "odd" : "even")."\"><td class=\"svninfokey\">".$key."</td><td class=\"svninfovalue\" id=\"".$id."\">".$value."</td>\n";
				}
				echo "</table>";
			}
	}
?>
