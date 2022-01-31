<?php
	$ARCurrent->nolangcheck=true;
	$ARCurrent->allnls = true;
	if( $this->CheckLogin("read") && $this->CheckConfig() ) {

		if ($AR->user->data->language && is_object($ARnls) ) {
			$ARnls->setLanguage($AR->user->data->language);
		}


		if( !isset($arLanguage) || !$arLanguage ) {
			$arLanguage = $nls;
		}

		$owner = $this->data->config->owner_name;
		if( !$owner ) {
			$owner = $this->data->owner_name;
		}
		$date = strftime("%m-%d-%Y",(isset($this->data->ctime) ? $this->data->ctime : null));
		$modified = strftime("%m-%d-%Y",(isset($this->data->mtime) ? $this->data->mtime : null));

		$userConfig = $this->loadUserConfig();
		$authconfig = $userConfig['authentication'];

		$mod_user = $this->data->muser;

		foreach ($authconfig['userdirs'] as $userdir) {
			$muser = current($this->find($userdir, "login.value='".AddCSlashes($this->data->muser, ARESCAPE)."'", "system.get.phtml"));
			if ($muser) {
				$mod_user = $muser->nlsdata->name;
				break;
			}
		}

		$name = $this->nlsdata->name;

		if (!isset($ARCurrent->arTypeTree)) {
			$this->call("typetree.ini");
		}
		$icon = isset($ARCurrent->arTypeIcons[$this->type]['default']) ? $ARCurrent->arTypeIcons[$this->type]['default'] : $this->call("system.get.icon.php");
		$iconalt = $this->type;
		if( $this->implements("pshortcut") ) {
			$overlay_icon = $icon;
			$overlay_alt = $this->type;
			$icon = isset($ARCurrent->arTypeIcons[$this->vtype]['default']) ? $ARCurrent->arTypeIcons[$this->vtype]['default'] : current($this->get($this->data->path, "system.get.icon.php"));
			$iconalt = $this->vtype;
		}
?>
<img src="<?php echo $icon; ?>" alt="<?php echo htmlspecialchars($iconalt); ?>" title="<?php echo htmlspecialchars($iconalt); ?>" class="icon">
<?php
	if( isset($overlay_icon) && $overlay_icon ) {
?>
<img src="<?php echo $overlay_icon; ?>" alt="<?php echo htmlspecialchars($overlay_alt); ?>" title="<?php echo htmlspecialchars($overlay_alt); ?>" class="overlay_icon">
<?php
	}
?>
<div class="sectionhead yuimenubar iconsection"><?php echo htmlspecialchars($name); ?></div>
<div id="browseheaderbody" class="sectionbody">
	<div id="browseheaderleft">
		<ul>
				<li><?php echo $ARnls["ariadne:created"]." ".htmlspecialchars($date).", ".strtolower($ARnls["ariadne:currentowner"])." ".htmlspecialchars($owner); ?></li>
				<li><?php echo $ARnls["ariadne:lastmodified"]." ".htmlspecialchars($modified)." ".strtolower($ARnls["ariadne:by"])." ".htmlspecialchars($mod_user); ?></li>
		</ul>
	</div>
	<div id="browseheaderright">
<?php
	if( !isset($this->data->name) || !$this->data->name ) {
		$config = $this->loadConfig();
		foreach( $config->nls->list as $key => $value ) {
			if( $arLanguage == $key ) {
				$class = "selected";
			} else {
				$class = "unselected";
			}
			if( isset($this->data->{$key}->name) ) {
				echo "<a class='$class' href='#' onClick=\"muze.ariadne.explore.setnls('" . $key . "'); return false;\" title=\"".htmlspecialchars($value)."\"><img class=\"flag\" src=\"".$AR->dir->images."nls/small/".$key.".gif\" alt=\"".htmlspecialchars($value)."\"></a> ";
			} else {
				echo "<a class='$class' href='#' onClick=\"muze.ariadne.explore.setnls('" . $key . "'); return false;\" title=\"".htmlspecialchars($value)."\"><img class=\"flag\" src=\"".$AR->dir->images."nls/small/faded/".$key.".gif\" alt=\"".htmlspecialchars($value)."\"></a> ";
			}
		}
	}
?>
	</div>
	<div class="clear">
	</div>
</div>
<?php
	}
?>
