<?php
	$ARCurrent->nolangcheck=true;
	require_once($this->store->get_config("code")."modules/mod_yui.php");

	if ($this->CheckLogin("read") && $this->CheckConfig()) {
		$settings = array();
		if ($this->CheckSilent("edit")) {
			$task = array(
				'href' => $this->make_ariadne_url() . "dialog.cache.php",
				'onclick' => "muze.ariadne.explore.arshow('dialog.cache', this.href); return false;",
				'icon' => $AR->dir->images . 'icons/small/cache.png',
				'nlslabel' => $ARnls['caching']
			);
			if (isset($this->data->config->cacheconfig) && $this->data->config->cacheconfig) {
				$task['class'] = 'sethere';
			}
			$settings[] = $task;
		}
		if ($this->CheckSilent("layout")) {
			$task = array(
				'href' => $this->make_ariadne_url() . "dialog.templates.php",
				'onclick' => "muze.ariadne.explore.arshow('dialog.templates', this.href); return false;",
				'icon' => $AR->dir->images . 'icons/small/templates.png',
				'nlslabel' => $ARnls['templates']
			);
			if (isset($this->data->config->pinp) && $this->data->config->pinp) {
				$task['class'] = 'sethere';
			}
			$settings[] = $task;

			$task = array(
				'href' => $this->make_ariadne_url() . "dialog.custom.php",
				'onclick' => "muze.ariadne.explore.arshow('dialog.custom',this.href); return false;",
				'icon' => $AR->dir->images . 'icons/small/customfields.png',
				'nlslabel' => $ARnls["ariadne:customfields"]
			);
			if (isset($this->data->config->customconfig) && $this->data->config->customconfig) {
				$task['class'] = 'sethere';
			}
			$settings[] = $task;

			$task = array(
				'href' => $this->make_ariadne_url() . "dialog.language.php",
				'onclick' => "muze.ariadne.explore.arshow('dialog.language',this.href); return false;",
				'icon' => $AR->dir->images . 'icons/small/language.png',
				'nlslabel' => $ARnls['language']
			);
			if (isset($this->data->config->nlsconfig) && $this->data->config->nlsconfig) {
				$task['class'] = 'sethere';
			}
			$settings[] = $task;
		}

		if ($this->CheckSilent("config")) {
			$task = array(
				'href' => $this->make_ariadne_url() . "dialog.grants.php",
				'onclick' => "muze.ariadne.explore.arshow('dialog.grants',this.href); return false;",
				'icon' => $AR->dir->images . 'icons/small/grants.png',
				'nlslabel' => $ARnls['grants']
			);
			if(isset($this->data->config->grants) && $this->data->config->grants) {
				$hasGrants = false;
				foreach ($this->data->config->grants as $value) {
					if (sizeof($value)) {
						$hasGrants = true;
						break;
					}
				}
				if ($hasGrants) {
					$task['class'] = 'sethere';
				}
			}
			$settings[] = $task;
			if( !isset($shortcutSidebar) || !$shortcutSidebar ) {
				$task = array(
					'href' => $this->make_ariadne_url() . "dialog.owner.php",
					'onclick' => "muze.ariadne.explore.arshow('dialog.owner', this.href); return false;",
					'icon' => $AR->dir->images . 'icons/small/owner.png',
					'nlslabel' => $ARnls['owner']
				);
				$settings[] = $task;
			}
		}

		if ($this->CheckSilent("edit") && (!isset($shortcutSidebar) || !$shortcutSidebar)) {
			$task = array(
				'href' => $this->make_ariadne_url() . "dialog.priority.php",
				'onclick' => "muze.ariadne.explore.arshow('dialog.priority',this.href); return false;",
				'icon' => $AR->dir->images . 'icons/small/priority.png',
				'nlslabel' => $ARnls['priority']
			);
			$settings[] = $task;
		}

		$section = array(
			'id' => 'settings',
			'label' => $ARnls['ariadne:settings'],
			'tasks' => $settings
		);
		$section = $this->call('explore.sidebar.settings.extra.html', array("section" => $section, "images" => $AR->dir->images));
		if (count($section['tasks'])) {
			if( isset($shortcutSidebar) && $shortcutSidebar ) {
				if (!isset($ARCurrent->arTypeTree)) {
					$this->call('typetree.ini');
				}
				$section['inline_icon'] = $ARCurrent->arTypeIcons[$this->type]['small'] ? $ARCurrent->arTypeIcons[$this->type]['small'] : $this->call('system.get.icon.php', array('size' => 'small'));
				$section['inline_iconalt'] = $this->type;
			}

            echo yui::getSection($section);
		}
	}
?>