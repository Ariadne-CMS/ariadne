<?php
	$ARCurrent->nolangcheck=true;

	if ($this->CheckLogin("delete") && $this->CheckConfig()) {
		$root = $this->getvar("root") ? $this->getvar("root") : $this->currentsite();
		if (substr($root, -1) != "/") {
			$root .= "/";
		}

		$query = "object.path =~ '" . $target . "%' order by path DESC";
		$total = $this->count_find($this->path, $query);
		$total--; // children of current path - don't include yourself.
		$crumbs = '';
		$path = '';
		$parents = $this->parents($this->path, 'system.get.name.phtml', '', $root);
		$parentpaths = $this->parents($this->path, 'system.get.path.phtml', '', $root);
		$path = array_pop($parentpaths);

		if ( strpos( $path, $root ) === 0 ) {
			$path = substr( $path, strlen($root)-1 );
		}

		array_pop($parents); //remove current item from the list.
		array_shift( $parents ); // remove site root from the list.
		foreach ($parents as $name) {
			$crumbs .= "/ $name";
		}

		$oldcrumbs = $crumbs;
		if (strlen($crumbs) > 18) {
			$crumbs = mb_substr($crumbs, 0, 12, "utf-8") . "..." . mb_substr($crumbs, strlen($crumbs)-5, strlen($crumbs), "utf-8");
			if (strlen($crumbs) >= strlen($oldcrumbs)) {
				$crumbs = $oldcrumbs;
			}
		}
		$crumbs = htmlspecialchars( $crumbs . "/ " . $nlsdata->name );
		$oldcrumbs = htmlspecialchars( $oldcrumbs . "/ " . $nlsdata->name );

		if( !$ARCurrent->arTypeTree ) {
			$this->call('typetree.ini');
		}
		$icons = $ARCurrent->arTypeIcons;
		$names = $ARCurrent->arTypeNames;

		$icon = $ARCurrent->arTypeIcons[$this->type]['medium'] ? $ARCurrent->arTypeIcons[$this->type]['medium'] : $this->call("system.get.icon.php", array('size' => 'medium'));

		$iconalt = $this->type;
		if ( $this->implements("pshortcut") ) {
			$overlay_icon = $icon;
			$overlay_alt = $this->type;
			if ( $ARCurrent->arTypeIcons[$this->vtype]['medium'] ) {
				$icon = $ARCurrent->arTypeIcons[$this->vtype]['medium'];
			} else {
				$icon = current($this->get($this->data->path, "system.get.icon.php", array('size' => 'medium')));
			}
			$iconalt = $this->vtype;
		}
		echo '<fieldset id="data" class="delete">';
		
		echo '<legend>';
		echo $ARnls["delete"] . ' ' . $names[$this->type];
		echo '</legend>';
		
		echo '<img src="' . $icon . '" alt="' . htmlspecialchars($iconalt) . '" title="' . htmlspecialchars($iconalt) . '" class="typeicon">';
		
		if ( $overlay_icon ) {
			echo '<img src="' . $overlay_icon . '" alt="' . htmlspecialchars($overlay_alt) . '" title="' . htmlspecialchars($overlay_alt) . '" class="overlay_typeicon">';
		}
		echo '<div class="name">' . $nlsdata->name . ' ';
		echo '( <span class="crumbs" title="' . $oldcrumbs . '">' . $crumbs . '</span> )';
		echo '</div>';
		echo '<div class="path">' . $path . '</div>';
		
		if ($total == 0) {
			echo $ARnls["q:removeobject"];
		} else {
			echo sprintf( $ARnls['q:removeall'], $nlsdata->name, $total);
			echo '<div class="field checkbox">';
			echo '  <input type="checkbox" id="childrenonly" name="childrenonly" value="true">';
			echo '  <label for="childrenonly">' . $ARnls['ariadne:childrenonly'] . '</label>';
			echo '</div>';
		}
		echo '</fieldset>';
	} else {
		echo $ARnls['e:nodeletegrants'];
	}
?>