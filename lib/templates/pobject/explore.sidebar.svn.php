<?php
	$ARCurrent->nolangcheck=true;
	require_once($this->store->get_config("code")."modules/mod_yui.php");

	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		$fstore	= $this->store->get_filestore_svn("templates");
		$svn	= $fstore->connect($this->id);
		// FIXME eror checking
		$svn_info = $fstore->svn_info($svn);

		if (count($svn_info)) {
			$tasks = array(
				array(
					'href' => $this->make_ariadne_url() . 'dialog.svn.tree.info.php',
					'onclick' => "muze.ariadne.explore.arshow('dialog.svn.tree.info',this.href); return false;",
					'icon' => $AR->dir->images . 'icons/small/svninfo.png',
					'nlslabel' => $ARnls['ariadne:svn:info']
				),
				array(
					'href' => $this->make_ariadne_url() . 'dialog.svn.tree.diff.php',
					'onclick' => "muze.ariadne.explore.arshow('dialog.svn.tree.diff',this.href); return false;",
					'icon' => $AR->dir->images . 'icons/small/svndiff.png',
					'nlslabel' => $ARnls["ariadne:svn:diff"]
				),
				array(
					'href' => $this->make_ariadne_url() . 'dialog.svn.tree.commit.php',
					'onclick' => "muze.ariadne.explore.arshow('dialog.svn.tree.commit',this.href); return false;",
					'icon' => $AR->dir->images . 'icons/small/svncommit.png',
					'nlslabel' => $ARnls['ariadne:svn:commit']
				),
				array(
					'href' => $this->make_ariadne_url() . 'dialog.svn.tree.revert.php',
					'onclick' => "muze.ariadne.explore.arshow('dialog.svn.tree.revert',this.href); return false;",
					'icon' => $AR->dir->images . 'icons/small/svnrevert.png',
					'nlslabel' => $ARnls['ariadne:svn:revert']
				),
				array(
					'href' => $this->make_ariadne_url() . 'dialog.svn.tree.update.php',
					'onclick' => "muze.ariadne.explore.arshow('dialog.svn.tree.update',this.href); return false;",
					'icon' => $AR->dir->images . 'icons/small/svnupdate.png',
					'nlslabel' => $ARnls['ariadne:svn:update']
				),
				array(
					'href' => $this->make_ariadne_url() . 'dialog.svn.tree.unsvn.php',
					'onclick' => "muze.ariadne.explore.arshow('dialog.svn.tree.unsvn',this.href); return false;",
					'icon' => $AR->dir->images . 'icons/small/unsvn.png',
					'nlslabel' => $ARnls['ariadne:svn:unsvn']
				)
			);
		} else {
			$tasks = array(
				array(
					'href' => $this->make_ariadne_url() . 'dialog.svn.tree.checkout.php',
					'onclick' => "muze.ariadne.explore.arshow('dialog.svn.tree.checkout',this.href); return false;",
					'icon' => $AR->dir->images . 'icons/small/svncheckout.png',
					'nlslabel' => $ARnls['ariadne:svn:checkout']
				),
				array(
					'href' => $this->make_ariadne_url() . 'dialog.svn.tree.import.php',
					'onclick' => "muze.ariadne.explore.arshow('dialog.svn.tree.import',this.href); return false;",
					'icon' => $AR->dir->images . 'icons/small/svnimport.png',
					'nlslabel' => $ARnls['ariadne:svn:import']
				)
			);
		}

		$svn_icon = false;
		$label = $ARnls['ariadne:svn:settings'];
		if ($svn_revision) {
			$svn_icon = $AR->dir->images. 'svn/InSubVersionIcon.png';
			$svn_status = $fstore->svn_status($svn);
			if ($svn_status) {
				foreach ($svn_status as $key => $value) {
					if (substr($key, -5) == ".pinp") {
						$svn_icon = $AR->dir->images . 'svn/ModifiedIcon.png';
						break;
					}
				}
			}
			$label = $ARnls['ariadne:svn:revision'] . ": " . $svn_revision;
		}

		$section = array(
			'id' => 'svn',
			'label' => $label,
			'inline_icon' => $svn_icon,
			'tasks' => $tasks
		);

		echo yui::getSection($section);
	}
?>
