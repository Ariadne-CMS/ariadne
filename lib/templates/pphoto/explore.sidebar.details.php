<?php
	$ARCurrent->nolangcheck=true;
  	include_once($this->store->get_config("code")."nls/ariadne.".$this->reqnls);

	if ($this->CheckLogin("read") && $this->CheckConfig()) {
		if (!$arLanguage) {
			$arLanguage=$nls;
			if (is_array($arCallArgs)) {
				$arCallArgs["arLanguage"]=$nls;
			} else {
				$arCallArgs.="&arLanguage=$nls";
			}
		}

		// FIXME: should take this information from sys.object.list.entry.html

		$myName = $nlsdata->name;
		$myType = $this->get("/system/ariadne/types/".$this->type, "system.get.name.phtml");
		if (!$myType) {
			$myType = array($this->type);
		}
		$myFilename = substr($this->path, strlen($this->parent));
		$myFilename = substr($myFilename, 0, strlen($myFilename) -1 );
		$myOwner = $this->data->config->owner_name;
		if (!$myOwner) {
			$myOwner = $this->data->owner_name;
		}

		$details = "<img src='" . $this->make_local_url() . "view.thumb.html' alt='" . $myName . "'>";

		$exifdata = $this->getExif();
		if (!$exifdata['ERROR']) {
	//		$exif['filesize'] = $exifdata['FILE']['FileSize'];
	//		$exif['mimetype'] = $exifdata['FILE']['MimeType'];
			$exif['height'] = $exifdata['COMPUTED']['Height'] . "px";
			$exif['width'] = $exifdata['COMPUTED']['Width'] . "px";

			$section = array(
				'id' => 'details',
				'label' => $ARnls['ariadne:details'],
				'details' => $details . section_table($exif)
			);
			echo showSection($section);
		}
	}
?>