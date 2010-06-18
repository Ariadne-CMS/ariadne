<?php
	$ARCurrent->nolangcheck=true;
  	include_once($this->store->get_config("code")."nls/ariadne.".$this->reqnls);

	if ($this->CheckLogin("read") && $this->CheckConfig()) {
		if (!$arLanguage) {
			$arLanguage=$nls;
		}
		if (isset($data->$arLanguage)) {
			$nlsdata=$data->$arLanguage;
		}

		$myName = $nlsdata->name;
		
		$fstore	= $this->store->get_filestore_svn("templates");
		$svnstack = &PEAR_ErrorStack::singleton('VersionControl_SVN');
		$svn	= $fstore->connect($this->id);
		$svn_info = $fstore->svn_info($svn);

		$svn_revision = $svn_info['Revision'];

		$tasks = array();
		if ($this->CheckSilent("layout")) {
			if ($svn_revision) {
				
				$tasks[] = array(
					'href' => $this->make_ariadne_url() . 'dialog.svn.tree.info.php',
					'onclick' => "muze.ariadne.explore.arshow('svn_object_info',this.href); return false;",
					'icon' => $AR->dir->images . 'icons/small/svninfo.png',
					'nlslabel' => $ARnls['ariadne:svn:info']
				);
				$tasks[] = array(
					'href' => $this->make_ariadne_url() . 'dialog.svn.tree.diff.php',
					'onclick' => "muze.ariadne.explore.arshow('svn_object_diff',this.href); return false;",
					'icon' => $AR->dir->images . 'icons/small/svndiff.png',
					'nlslabel' => $ARnls["ariadne:svn:diff"]
				);
				$tasks[] = array(
					'href' => $this->make_ariadne_url() . 'dialog.svn.tree.commit.php',
					'onclick' => "muze.ariadne.explore.arshow('svn_object_commit',this.href); return false;",
					'icon' => $AR->dir->images . 'icons/small/svncommit.png',
					'nlslabel' => $ARnls['ariadne:svn:commit']
				);
				$tasks[] = array(
					'href' => $this->make_ariadne_url() . 'dialog.svn.tree.revert.php',
					'onclick' => "muze.ariadne.explore.arshow('svn_object_revert',this.href); return false;",
					'icon' => $AR->dir->images . 'icons/small/svnrevert.png',
					'nlslabel' => $ARnls['ariadne:svn:revert']
				);
				$tasks[] = array(
					'href' => $this->make_ariadne_url() . 'dialog.svn.tree.update.php',
					'onclick' => "muze.ariadne.explore.arshow('svn_object_update',this.href); return false;",
					'icon' => $AR->dir->images . 'icons/small/svnupdate.png',
					'nlslabel' => $ARnls['ariadne:svn:update']
				);
				$tasks[] = array(
					'href' => $this->make_ariadne_url() . 'dialog.svn.tree.unsvn.php',
					'onclick' => "muze.ariadne.explore.arshow('svn_object_unsvn',this.href); return false;",
					'icon' => $AR->dir->images . 'icons/small/unsvn.png',
					'nlslabel' => $ARnls['ariadne:svn:unsvn']
				);
			} else {
				$tasks[] = array(
					'href' => $this->make_ariadne_url() . 'dialog.svn.tree.checkout.php',
					'onclick' => "muze.ariadne.explore.arshow('svn_object_checkout',this.href); return false;",
					'icon' => $AR->dir->images . 'icons/small/svncheckout.png',
					'nlslabel' => $ARnls['ariadne:svn:checkout']
				);
				$tasks[] = array(
					'href' => $this->make_ariadne_url() . 'dialog.svn.tree.import.php',
					'onclick' => "muze.ariadne.explore.arshow('svn_object_import',this.href); return false;",
					'icon' => $AR->dir->images . 'icons/small/svnimport.png',
					'nlslabel' => $ARnls['ariadne:svn:import']
				);
			}
		}

		if ($tasks) {
			$svn_icon = false;
			$label = $ARnls['ariadne:svn:settings'];
			if ($svn_revision) {
				ob_start();
				$svn_status = $fstore->svn_status($svn);
				ob_end_clean();
				if ($svn_status) {
					foreach ($svn_status as $key => $value) {
						if (substr($key, -5) == ".pinp") {
						} else {
						unset($svn_status[$key]);
						}
					} 
					if (sizeof($svn_status)) {
						$svn_icon = $AR->dir->images . 'svn/ModifiedIcon.png';
					} else {
						$svn_icon = $AR->dir->images . 'svn/InSubVersionIcon.png';
					}
				} else if ($svn_info) {
						$svn_icon = $AR->dir->images . 'svn/InSubVersionIcon.png';
				}
				$label = $ARnls['ariadne:svn:revision'] . ": " . $svn_revision;
			}

			$section = array(
				'id' => 'svn',
				'label' => $label,
				'inline_icon' => $svn_icon,
				'tasks' => $tasks
			);

			echo(showSection($section));
		}
	}
?>