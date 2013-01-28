<?php
	$ARCurrent->nolangcheck=true;    
	$ARCurrent->allnls=true;

	if ($this->CheckLogin("read") && $this->CheckConfig()) {
		require_once($this->store->get_config("code")."modules/mod_yui.php");

		if ($AR->user->data->language) {
			ldSetNls($AR->user->data->language);
		}

		$wwwroot = $AR->dir->www;

		$menuitems = array(
			array(
				'label' => $ARnls['ariadne:logoff'],
				'iconalt' => $ARnls['ariadne:logoff'],
				'icon' => $AR->dir->images . 'icons/medium/logout.png',
				'href' => $this->make_ariadne_url() .'logoff.php'
			),
			array(
				'label' => $ARnls['ariadne:search'],
				'iconalt' => $ARnls['ariadne:search'],
				'icon' => $AR->dir->images . 'icons/small/search.png',
				'onclick' => "muze.ariadne.explore.toolbar.searchwindow(); return false;",
				'href' => $this->make_ariadne_url(). 'dialog.search.php'
			),
			array(
				'label' => $ARnls['ariadne:folders'],
				'iconalt' => $ARnls['ariadne:folders'],
				'icon' => $AR->dir->images . 'icons/small/view_tree.png',
				'href' => "#",
				'onclick' => 'muze.ariadne.explore.tree.toggle(); return false;'
			),
			array(
				'label' => $ARnls['ariadne:preferences'],
				'iconalt' => $ARnls['ariadne:preferences'],
				'icon' => $AR->dir->images . 'icons/small/preferences.png',
				'onclick' => "muze.ariadne.explore.arshow('dialog.preferences','" . $this->store->get_config('root').$AR->user->path . "dialog.preferences.php'); return false;",
				'href' => $this->make_ariadne_url($AR->user->path) . "dialog.preferences.php"
			),
			array(
				'iconalt' => $ARnls['ariadne:iconview'],
				'icon' => $AR->dir->images . 'icons/small/view_icon.png',
				'onclick' => 'return false;',
				'submenu' => array(
					array(
						'href' => "javascript:muze.ariadne.explore.viewpane.setviewmode('list');",
						'label' => $ARnls['ariadne:small'],
					),
					array(
						'href' => "javascript:muze.ariadne.explore.viewpane.setviewmode('icons');",
						'label' => $ARnls['ariadne:large'],
					),
					array(
						'href' => "javascript:muze.ariadne.explore.viewpane.setviewmode('details');",
						'label' => $ARnls['ariadne:details'],
					)
				)
			),
			array(
				'iconalt' => $ARnls['ariadne:help'],
				'icon' => $AR->dir->images . 'icons/small/help.png',
				'onclick' => 'return false;',
				'submenu' => array(
					array(
						'href' => "#",
						'onclick' => "muze.ariadne.explore.arshow('help', 'http://www.ariadne-cms.org/docs/'); return false;",
						'label' => $ARnls['ariadne:help']
					),
					array(
						'href' => "#",
						'onclick' => "muze.ariadne.explore.arshow('help.about','help.about.php'); return false;" ,
						'label' => $ARnls['ariadne:about']
					)
				)
			),
			array(
				'iconalt' => $ARnls['ariadne:up'],
				'icon' => $AR->dir->images . 'icons/small/up.png',
				//'href' => "javascript:muze.ariadne.explore.view('" . $this->parent . "');"
				'href' => $this->make_ariadne_url($this->parent) . "explore.html",
				'onclick' => "muze.ariadne.explore.toolbar.viewparent(); return false;",
				'id' => "viewparent"
			)
		);


	?>
		<div class="logo">
			<a href="http://www.ariadne-cms.org">
				<img src="<?php echo $wwwroot;?>images/tree/logo2.gif" alt="Ariadne Web Application Server">
				<span class="ariadne">Ariadne</span>
				<span class="ariadne_sub">Web Application Server</span>
			</a>
		</div>
		<?php
			echo yui::yui_menuitems($menuitems, "yuimenubar", "explore_menubar")."\n";
		?>
		<div class="searchdiv">
			<form action="explore.html" onsubmit="muze.ariadne.explore.toolbar.searchsubmit(this.arPath.value); return false;">
				<div>
					<input size="30" id="searchpath" class="text" type="text" name="arPath" value="<?php echo $this->path; ?>">
					<input type="image" src="<?php echo $AR->dir->www; ?>images/icons/small/go.png" title="<?php echo htmlspecialchars($ARnls['ariadne:search']); ?>" id="searchbutton" name="searchsubmit" value="<?php echo $ARnls["ariadne:search"]; ?>">
				</div>
				<div id="resultscontainer"></div>
			</form>
		</div>
<?php	}	?>