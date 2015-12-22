<?php
	ldDisablePostProcessing();
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		set_time_limit(0);
		$arCallArgs["colorize"] = true;
		$arCallArgs["nowrap"] = true;
		$result = $this->find($this->path, '', "system.svn.diff.php", $arCallArgs, 0, 0);
		$modifications = "";
		foreach( $result as $diff ) {
			if ( !ar('error')->isError($diff)){
				$modifications .= $diff;
			}
		}
		if( !$modifications ) {
			echo "<pre class='svnresult'>".$ARnls["ariadne:svn:nomod"]."</pre>";
		} else {
			echo "<pre class='svnresult'>".$modifications."</pre>";
		}
	}
?>
