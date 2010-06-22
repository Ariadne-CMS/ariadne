<?php
	$ARCurrent->nolangcheck=true;
	include_once($this->store->get_config("code")."nls/".$this->reqnls);
  	include_once($this->store->get_config("code")."nls/ariadne.".$this->reqnls);
 	include_once($this->store->get_config("code")."nls/menu.".$this->reqnls);
	require_once($this->store->get_config("code")."modules/mod_yui.php");

	if ($this->CheckLogin("read") && $this->CheckConfig()) {
		$settings = array();
		if ($this->CheckSilent("edit")) {
			$task = array(
				'href' => $this->make_ariadne_url() . "dialog.cache.php",
				'onclick' => "muze.ariadne.explore.arshow('edit_object_cache', this.href); return false;",
				'icon' => $AR->dir->images . 'icons/small/cache.png',
				'nlslabel' => $ARnls['caching']
			);
			if ($this->data->config->cacheconfig) {
				$task['class'] = 'sethere';
			}
			$settings[] = $task;
		}
		if ($this->CheckSilent("layout")) {
			$task = array(
				'href' => $this->make_ariadne_url() . "dialog.templates.php",
				'onclick' => "muze.ariadne.explore.arshow('edit_object_layout', this.href); return false;",
				'icon' => $AR->dir->images . 'icons/small/templates.png',
				'nlslabel' => $ARnls['templates']
			);
			if ($this->data->config->pinp) {
				$task['class'] = 'sethere';
			}
			$settings[] = $task;

			$task = array(
				'href' => $this->make_ariadne_url() . "dialog.custom.php",
				'onclick' => "muze.ariadne.explore.arshow('edit_object_custom',this.href); return false;",
				'icon' => $AR->dir->images . 'icons/small/customfields.png',
				'nlslabel' => $ARnls["m_custom"]
			);
			if ($this->data->config->customconfig) {
				$task['class'] = 'sethere';
			}
			$settings[] = $task;

			$task = array(
				'href' => $this->make_ariadne_url() . "dialog.language.php",
				'onclick' => "muze.ariadne.explore.arshow('edit_object_nls',this.href); return false;",
				'icon' => $AR->dir->images . 'icons/small/language.png',
				'nlslabel' => $ARnls['language']
			);
			if ($this->data->config->nlsconfig) {
				$task['class'] = 'sethere';
			}
			$settings[] = $task;
		}

		if ($this->CheckSilent("config")) {
			$task = array(
				'href' => $this->make_ariadne_url() . "dialog.grants.php",
				'onclick' => "muze.ariadne.explore.arshow('edit_object_grants',this.href); return false;",
				'icon' => $AR->dir->images . 'icons/small/grants.png',
				'nlslabel' => $ARnls['grants']
			);
			if($this->data->config->grants) {
				$task['class'] = 'sethere';
			}
			$settings[] = $task;
			if( !$shortcutSidebar ) {
				$task = array(
					'href' => $this->make_ariadne_url() . "dialog.owner.php",
					'onclick' => "muze.ariadne.explore.arshow('edit_object_owner', this.href); return false;",
					'icon' => $AR->dir->images . 'icons/small/owner.png',
					'nlslabel' => $ARnls['owner']
				);
				$settings[] = $task;
			}
		}

		if ($this->CheckSilent("edit") && !$shortcutSidebar) {
			$task = array(
				'href' => $this->make_ariadne_url() . "dialog.priority.php",
				'onclick' => "muze.ariadne.explore.arshow('edit_priority',this.href); return false;",
				'icon' => $AR->dir->images . 'icons/small/priority.png',
				'nlslabel' => $ARnls['priority']
			);
			$settings[] = $task;
		}

		if (count($settings)) {
			$section = array(
				'id' => 'settings',
				'label' => $ARnls['ariadne:settings'],
				'tasks' => $settings
			);
			if( $shortcutSidebar ) {
				if (!$ARCurrent->arTypeTree) {
					$this->call('typetree.ini');
				}
				$section['inline_icon'] = $ARCurrent->arTypeIcons[$this->type]['small'] ? $ARCurrent->arTypeIcons[$this->type]['small'] : $this->call('system.get.icon.php', array('size' => 'small'));
				$section['inline_iconalt'] = $this->type;
			}
			
            echo yui::getSection($section);
		}
	}
?>