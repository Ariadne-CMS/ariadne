<?php
	$ARCurrent->allnls = true;

	if ($this->CheckLogin("read") && $this->CheckConfig()) {

	  	require_once($this->store->get_config("code")."modules/mod_yui.php");
	  	require_once($this->store->get_config("code")."modules/mod_workspace.php");

		if (workspace::enabled($this->path) && getenv("ARIADNE_WORKSPACE")) {
			$imagesdir = $AR->dir->images;
			$status = workspace::status($this->path);

			if ($status['hardlinks']) {
				$icon = $imagesdir . "svn/ConflictIcon.png";
				$details = "<strong>" . $ARnls['ariadne:workspace:warning'] . ": </strong>" . $ARnls['ariadne:workspace:hardlinks_found'] . "<br><br>";

				$section = array(
					'id' => 'workspace',
					'label' => "Workspace",
					'inline_icon' => $icon,
					'details' => $details
				);
			} else {
				$combined_status = false;
				foreach ($status as $key => $value) {
					$combined_status = $combined_status || $value;
				}

				if ($combined_status) {
					$icon = $imagesdir . "svn/ModifiedIcon.png";
				} else {
					$icon = $imagesdir . "svn/InSubVersionIcon.png";
				}

				$tasks = array();
				$tasks[] = array(
					'href' => $this->make_local_url() . "dialog.workspace.php",
					'onclick' => "muze.ariadne.explore.arshow('dialog.workspace', this.href); return false;",
					'icon' => $imagesdir . 'icons/small/go.png',
					'nlslabel' => $ARnls['ariadne:workspace:manage_workspace']
				);

				$tasks[] = array(
					'href' => $this->make_local_url() . "view.html",
					'onclick' => "muze.ariadne.explore.arshow('_new', this.href); return false;",
					'icon' => $imagesdir . 'icons/small/viewweb.png',
					'nlslabel' => $ARnls['ariadne:workspace:view_workspace']
				);

				$siteob = current($this->get($this->currentsite(), "system.get.phtml"));

				$tasks[] = array(
					'href' => str_replace( $siteob->data->workspaceurl, $siteob->data->url, $this->make_local_url($path, false, false) ),
					'onclick' => "muze.ariadne.explore.arshow('_new', this.href); return false;",
					'icon' => $imagesdir . 'icons/small/viewweb.png',
					'nlslabel' => $ARnls['ariadne:workspace:view_live']
				);
			
				$section = array(
					'id' => 'workspace',
					'label' => $ARnls['ariadne:workspace:workspace'],
					'inline_icon' => $icon,
					'tasks' => $tasks,
					'details' => $details
				);
			}

			echo yui::getSection($section);
		}
	}
?>