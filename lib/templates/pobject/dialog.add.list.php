<?php
	global $AR;
	$ARCurrent->nolangcheck = true;
	if ( $this->CheckLogin("add", ARANYTYPE) && $this->CheckConfig() ) {
		require_once( $this->store->get_config("code")."modules/mod_yui.php" );
		if ( !$arReturnTemplate ) {
			$arReturnTemplate = "dialog.new.php";
		}

		$server_name = preg_replace( "|^[htps:/]*/|i", "", $AR->host );

		if ( $_SERVER["HTTP_HOST"] == $server_name ) {
			$currentpath = $this->store->get_config("root") . $this->path;
	  	} else {
			$currentpath = $this->make_ariadne_url();
		}

		if ( !($showall && $this->CheckSilent("layout")) ) {
			$showall = 0;
		}

		$typeslist = yui::getTypes($this, $showall);
		$itemlist = array();
		if ( $typeslist && is_array($typeslist) && count($typeslist) ) {
			$itemlist = yui::getItems($this, $typeslist, $currentpath, $arReturnTemplate);
		} else {
			error( $ARnls["ariadne:no_adding_found"] );
		}
		echo '<div class="listcontainer">';
		foreach ( $itemlist as $item ) {
			echo '<a class="item '.$item['class'].'" href="'.$item['href'].'" title="'.$item['type'].'">';
			echo '<img class="icon" src="'.$item['icon'].'" alt="'.$item['type'].'" title="'.$item['type'].'">';
			echo '<span class="name">'.htmlspecialchars($item['name']).'</span>';
			echo '</a>';
		}
		echo '</div>';
	}
?>
