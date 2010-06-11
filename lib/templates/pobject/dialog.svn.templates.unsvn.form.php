<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		echo "<strong>Are you sure you want to remove Version Control?</strong>";
	}
?>
