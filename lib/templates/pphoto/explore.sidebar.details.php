<?php
	$ARCurrent->nolangcheck=true;
	include_once($this->store->get_config("code")."nls/ariadne.".$this->reqnls);
	require_once($this->store->get_config("code")."modules/mod_yui.php");

	if ($this->CheckLogin("read") && $this->CheckConfig()) {

		$imagedata = $this->getdatacache("explore-sidebar-details-image");
		if (!$imagedata){
			$imagedata = $this->getimageinfo();
			if (is_array($imagedata)) {
				$this->savedatacache("explore-sidebar-details-exif",$imagedata,8760);
			}
		}

		if (is_array($imagedata)){
			$info['width'] = $imagedata[0];
			$info['height'] = $imagedata[1];

			$details = "<img src='" . $this->make_ariadne_url() . "view.thumb.html' alt='" . htmlspecialchars($this->nlsdata->name??'') . "'>";

			$section = array(
				'id' => 'details',
				'label' => $ARnls['ariadne:details'],
				'details' => $details . yui::section_table($info)
			);
			echo yui::getSection($section);
		}
	}
?>
