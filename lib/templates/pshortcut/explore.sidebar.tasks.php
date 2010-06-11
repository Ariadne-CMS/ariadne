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

		$tasks = array();

/*		
		if ($this->CheckSilent("add",ARANYTYPE) && !$hideAdd) {
			$tasks[] = array(
				'href' => $this->make_local_url() ."dialog.add.php",
				'onclick' => "muze.ariadne.explore.arshow('edit_object_data',this.href); return false;",
				'icon' => $AR->dir->images . 'icons/small/add.png',
				'nlslabel' => $ARnls['ariadne:new']
			);
		}
*/
		if($this->CheckSilent("edit")) {
			$tasks[] = array(
				'href' => $this->make_local_url() . "dialog.edit.shortcut.php",
				'onclick' => "muze.ariadne.explore.arshow('edit_object_shortcut',this.href); return false;",
				'icon' => $AR->dir->images . 'icons/small/edit.png',
				'nlslabel' => $ARnls['ariadne:edit']
			);
		}
		if ($this->CheckSilent("delete")) {
			$tasks[] = array(
				'href' => $this->make_local_url() . "dialog.rename.php",
				'onclick' => "muze.ariadne.explore.arshow('object_fs',this.href); return false;",
				'icon' => $AR->dir->images . 'icons/small/rename.png',
				'nlslabel' => $ARnls['ariadne:rename']
			);
		}
		$tasks[] = array(
			'href' => $this->make_local_url() . "dialog.copy.php",
			'onclick' => "muze.ariadne.explore.arshow('object_fs',this.href); return false;",
			'icon' => $AR->dir->images . 'icons/small/copy.png',
			'nlslabel' => $ARnls['ariadne:copy']
		);

		if ($this->CheckSilent("delete")) {
			$tasks[] = array(
				'href' => $this->make_local_url() . "dialog.delete.php",
				'onclick' => "muze.ariadne.explore.arshow('object_fs',this.href); return false;",
				'icon' => $AR->dir->images . 'icons/small/delete.png',
				'nlslabel' => $ARnls['ariadne:delete']
			);		
		}

		$tasks[] = array(
			'href' => $this->make_local_url()."view.html",
			'onclick' => "muze.ariadne.explore.arshow('_new', this.href); return false;",
			'icon' => $AR->dir->images . 'icons/small/viewweb.png',
			'nlslabel' => $ARnls['ariadne:viewweb']
		);		

		if ($this->CheckSilent("edit")) {
			$tasks[] = array(
				'href' => $this->make_local_url()."user.edit.html",
				'onclick' => "muze.ariadne.explore.arshow('_new', this.href); return false;",
				'icon' => $AR->dir->images . 'icons/small/wysiwyg.png',
				'nlslabel' => $ARnls['ariadne:wysiwyg_editor']
			);		
		}
/*
		$this->call("typetree.ini");
		$icon=$this->call('system.get.icon.php');
		$iconalt = $this->type;
		
		if( $this->implements("pshortcut") ) {
			$overlay_icon = $icon;
			$overlay_alt = $this->type;
			$icon = current($this->get($this->data->path, 'system.get.icon.php'));
			$iconalt = $this->vtype;
		} 
		$loadicon = $AR->dir->images . 'ajax-loading.gif';
		$loadiconalt = "Loading...";

		$arCallArgs["sectionName"] = "shortcuttasks";
		$arCallArgs["sectionDisplayName"] = $ARnls["ariadne:options"];
		$arCallArgs["icon"] = $icon;
		$arCallArgs["loadicon"] = $loadicon;
*/		
		$section = array(
			'id' => 'shortcuttasks',
			'label' => $ARnls["ariadne:options"],
//			'icon' => $icon,
//			'iconalt' => $iconalt,
//			'overlay_icon' => $overlay_icon,
//			'overlay_iconalt' => $overlay_alt,
			'loadicon' => $loadicon,
			'loadiconalt' => $loadiconalt,
			'tasks' => $tasks,
			'inline_icon' => $this->call('system.get.icon.php', array('size' => 'small')),
			'inline_iconalt' => $this->type
		);

		$section = $this->call('explore.sidebar.tasks.extra.html', array("section" => $section, "images" => $AR->dir->images));
		echo(showSection($section));
	}
?>