<?php
	$ARCurrent->nolangcheck=true;
	include_once($this->store->get_config("code")."nls/ariadne.".$this->reqnls);
	require_once($this->store->get_config("code")."modules/mod_yui.php");

	if ($this->CheckLogin("read") && $this->CheckConfig()) {

		$exifdata = $this->getExif();
		if (!$exifdata['ERROR']) {
			$exif['height'] = $exifdata['COMPUTED']['Height'] . "px";
			$exif['width'] = $exifdata['COMPUTED']['Width'] . "px";

			$details = "<img src='" . $this->make_ariadne_url() . "view.thumb.html' alt='" . htmlspecialchars($this->nlsdata->name) . "'>";

			$section = array(
				'id' => 'details',
				'label' => $ARnls['ariadne:details'],
				'details' => $details . yui::section_table($exif)
			);
			echo yui::getSection($section);
		}
	}
?>