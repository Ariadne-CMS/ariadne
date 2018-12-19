<?php
	$ARCurrent->nolangcheck=true;

//	if ($this->CheckLogin("delete") && $this->CheckConfig()) {
		$root = $this->getvar("root") ? $this->getvar("root") : $this->currentsite();
		if (substr($root, -1) != "/") {
			$root .= "/";
		}

		if ($this->getvar('targets')) {
			$targets = $this->getvar("targets");
		} else {
			$targets = array($this->path);
		}
?>

        <fieldset id="data" class="delete">
            <legend><?php echo $ARnls["ariadne:delete"]; ?></legend>

            <?php

		foreach ($targets as $target) {


			$targetob = current($this->get($target, "system.get.phtml"));

                        if (!$targetob->CheckSilent("delete")) {
                            $checkfailed = true;
                        } else {
                            $checkfailed = false;
                        }

			$query = "object.path =~ '" . $target . "%' order by path DESC";
			$total = $targetob->count_find($target, $query);

			$total--; // children of current path - don't include yourself.
			$crumbs = '';
			$path = '';
			$parents = $targetob->parents($target, 'system.get.name.phtml', '', $root);
			$parentpaths = $targetob->parents($target, 'system.get.path.phtml', '', $root);
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
			$crumbs = htmlspecialchars( $crumbs . "/ " . $targetob->nlsdata->name );
			$oldcrumbs = htmlspecialchars( $oldcrumbs . "/ " . $targetob->nlsdata->name );

			if( !$ARCurrent->arTypeTree ) {
				$targetob->call('typetree.ini');
			}
			$icons = $ARCurrent->arTypeIcons;
			$names = $ARCurrent->arTypeNames;

			$icon = $ARCurrent->arTypeIcons[$targetob->type]['medium'] ? $ARCurrent->arTypeIcons[$targetob->type]['medium'] : $targetob->call("system.get.icon.php", array('size' => 'medium'));

			$iconalt = $targetob->type;
			if ( $targetob->implements("pshortcut") ) {
				$overlay_icon = $icon;
				$overlay_alt = $targetob->type;
				if ( $ARCurrent->arTypeIcons[$targetob->vtype]['medium'] ) {
					$icon = $ARCurrent->arTypeIcons[$targetob->vtype]['medium'];
				} else {
					$icon = current($targetob->get($targetob->data->path, "system.get.icon.php", array('size' => 'medium')));
				}
				$iconalt = $targetob->vtype;
			}

                //This lists the files selected for the action
			if($checkfailed){
                            echo '<div class="checkfailed">';
                            echo '<span class="error">' . $ARnls["err:nogrants"] .'</span>';
                        } else {
                            echo '<div>';
                        }
                        echo '<img src="' . $icon . '" alt="' . htmlspecialchars($iconalt) . '" title="' . htmlspecialchars($iconalt) . '" class="typeicon">';

			if ( $overlay_icon ) {
				echo '<img src="' . $overlay_icon . '" alt="' . htmlspecialchars($overlay_alt) . '" title="' . htmlspecialchars($overlay_alt) . '" class="overlay_typeicon">';
			}
			echo '<div class="name">' . $targetob->nlsdata->name . ' ';
			echo '( <span class="crumbs" title="' . $oldcrumbs . '">' . $crumbs . '</span> )';
			echo '</div>';
			echo '<div class="path">' . $path . '</div>';
			if ($total > 0 && !$targetob->checkfailed) {
				echo sprintf( $ARnls['q:removeall'], $targetob->nlsdata->name, $total);
			}
                        echo '</div>';
		}

		if (($total == 0) && (sizeof($targets) == 1)) {
			echo '<p>' . $ARnls["q:removeobject"] . '</p>';
		} else if (($total == 0) && sizeof($targets) > 1) {
			echo '<p>' . $ARnls["q:removeobjects"] . '</p>';
		}

		echo '<div class="field checkbox">';
		echo '  <input type="checkbox" id="childrenonly" name="childrenonly" value="true">';
		echo '  <label for="childrenonly">' . $ARnls['ariadne:childrenonly'] . '</label>';
		echo '</div>';
//	} else {
//		echo $ARnls['e:nodeletegrants'];
//	}
?>
</fieldset>
