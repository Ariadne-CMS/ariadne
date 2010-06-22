<?php
	$ARCurrent->nolangcheck = true;
	$ARCurrent->allnls = true;

	require_once($this->store->get_config("code")."modules/mod_yui.php");
	
	if ($this->CheckLogin("read") && $this->CheckConfig()) {

	  	include_once($this->store->get_config("code")."nls/ariadne.".$this->reqnls);

		$tasks = array();

		if($this->CheckSilent("edit")) {
			$tasks[] = array(
				'href' => $this->make_ariadne_url() . "dialog.edit.shortcut.php",
				'onclick' => "muze.ariadne.explore.arshow('edit_object_shortcut',this.href); return false;",
				'icon' => $AR->dir->images . 'icons/small/edit.png',
				'nlslabel' => $ARnls['ariadne:edit']
			);
		}
		if ($this->CheckSilent("delete")) {
			$tasks[] = array(
				'href' => $this->make_ariadne_url() . "dialog.rename.php",
				'onclick' => "muze.ariadne.explore.arshow('object_fs',this.href); return false;",
				'icon' => $AR->dir->images . 'icons/small/rename.png',
				'nlslabel' => $ARnls['ariadne:rename']
			);
		}
		$tasks[] = array(
			'href' => $this->make_ariadne_url() . "dialog.copy.php",
			'onclick' => "muze.ariadne.explore.arshow('object_fs',this.href); return false;",
			'icon' => $AR->dir->images . 'icons/small/copy.png',
			'nlslabel' => $ARnls['ariadne:copy']
		);

		if ($this->CheckSilent("delete")) {
			$tasks[] = array(
				'href' => $this->make_ariadne_url() . "dialog.delete.php",
				'onclick' => "muze.ariadne.explore.arshow('object_fs',this.href); return false;",
				'icon' => $AR->dir->images . 'icons/small/delete.png',
				'nlslabel' => $ARnls['ariadne:delete']
			);		
		}

		$tasks[] = array( // we use make_local_url specifically
			'href' => $this->make_local_url()."view.html",
			'onclick' => "muze.ariadne.explore.arshow('_new', this.href); return false;",
			'icon' => $AR->dir->images . 'icons/small/viewweb.png',
			'nlslabel' => $ARnls['ariadne:viewweb']
		);		

		if ($this->CheckSilent("edit")) {
			$tasks[] = array(
				'href' => $this->make_ariadne_url()."user.edit.html",
				'onclick' => "muze.ariadne.explore.arshow('_new', this.href); return false;",
				'icon' => $AR->dir->images . 'icons/small/wysiwyg.png',
				'nlslabel' => $ARnls['ariadne:wysiwyg_editor']
			);		
		}
		$section = array(
			'id' => 'shortcuttasks',
			'label' => $ARnls["ariadne:options"],
			'tasks' => $tasks,
			'inline_icon' => $ARCurrent->arTypeIcons[$this->type]['small'] ? $ARCurrent->arTypeIcons[$this->type]['small'] : $this->call('system.get.icon.php', array('size' => 'small')),
			'inline_iconalt' => $this->type
		);

		$section = $this->call('explore.sidebar.tasks.extra.html', array("section" => $section, "images" => $AR->dir->images));
		echo yui::getSection($section);
	}
?>