<?php
	include_once("getvars.php");

	$sections = array(
		"step1" => array(
			"title" => $ARnls['install:language'],
			"icon" 	=> "../images/icons/large/language.png"
		),
		"step2" => array(
			"title" => $ARnls['install:pre_install_checks'],
			"icon" 	=> "../images/icons/large/pobject.png"
		),
		"step3" => array(
			"title" => $ARnls['install:license'],
			"icon"	=> "../images/icons/large/ppage.png"
		),
		"step4" => array(
			"title" => $ARnls['install:database'],
			"icon" 	=> "../images/icons/large/svndiff.png"
		),
		"step5" => array(
			"title" => $ARnls['install:configuration'],
			"icon"	=> "../images/icons/large/edit.png",
		),
		"step6" => array(
			"title" => $ARnls['install:install'],
			"icon"	=> "../images/icons/large/priority.png"
		)
	);
?>
				<div id="sections">
					<?php
						foreach ($sections as $key => $info) {
							if ($key == $step) {
								$current = " current";
							} else {
								$current = '';
							}
					?>
					<label class="section<?php echo $current; ?>" for="<?php echo $key; ?>">
						<img onclick="this.parentNode.click();" alt="<?php echo $info['title']; ?>" src="<?php echo $info['icon']; ?>">
						<span class="title"><?php echo $info['title']; ?></span>
					</label>
					<input type="submit" class="hidden" name="step" value="<?php echo $key; ?>" id="<?php echo $key; ?>">
					<?php
						}
					?>
				</div>
