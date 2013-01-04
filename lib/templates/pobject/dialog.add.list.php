<?php
	global $AR;
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("add", ARANYTYPE) && $this->CheckConfig()) {
		require_once($this->store->get_config("code")."modules/mod_yui.php");
		if (!$arReturnTemplate) {
			$arReturnTemplate="dialog.new.php";
		}

		$server_name=preg_replace("|^[htps:/]*/|i","",$AR->host);

		if ($_SERVER["HTTP_HOST"]==$server_name) {
			$currentpath=$this->store->get_config("root").$this->path;
	  	} else {
			$currentpath=$this->make_ariadne_url();
		}
		
		if (!($showall && $this->CheckSilent("layout"))) {
			$showall = 0;
		}
		
		$typeslist = yui::getTypes($this, $showall);
		$itemlist = Array();
		if($typeslist && is_array($typeslist) && count($typeslist)) {
			$itemlist = yui::getItems($this, $typeslist, $currentpath, $arReturnTemplate);
		} else {
			error($ARnls["ariadne:no_adding_found"]);
		}
?>
<div class='listcontainer'>
<?php
	foreach ($itemlist as $item) {
?>
		<a class="item <?php echo $item['class']; ?>" href="<?php echo $item['href']; ?>" title="<?php echo $item['type']; ?>">
			<img class="icon" src="<?php echo $item['icon']; ?>" alt="<?php echo $item['type']; ?>" title="<?php echo $item['type']; ?>">
			<span class="name"><?php echo $item['name']; ?></span>
		</a>
<?php
	}
?>
</div>
<?php
	}
?>
