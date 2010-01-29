<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("add", ARANYTYPE) && $this->CheckConfig()) {
		if (!$arReturnTemplate) {
			$arReturnTemplate="dialog.new.php";
		}

		$server_name=eregi_replace("^[htps:/]*/","",$AR->host);

		if ($_SERVER["HTTP_HOST"]==$server_name) {
			$currentpath=$this->store->get_config("root").$this->path;
	  	} else {
			$currentpath=$this->make_local_url();
		}

		if( !function_exists("getTypes") ) {
			function getTypes($arObject, $showall) {
				if (!$showall) {
					$configcache = $ARConfig->cache[$arObject->path];
					$typetree = $arObject->call('typetree.ini');
					$thistypetree = $typetree[$arObject->type];

					if (is_array($thistypetree)) {
						foreach( $thistypetree as $type => $name ) {
							$result[$type] = $name;
						}
					}
				} else {
					$systemtypes = $arObject->ls("/system/ariadne/types/", "system.get.phtml");
					foreach ($systemtypes as $object) {
						$type=$object->data->value;
						$name=$object->nlsdata->name;

						$result[$type] = $name;
					}

					$typetree = $arObject->call('typetree.ini');
					foreach ($typetree as $value) {
						foreach( $value as $type => $name ) {
							$result[$type] = $name;
						}
					}
				}
				return $result;
			}
		}

		if( !function_exists("checkType") ) {		
			function checkType($arObject, $type, $name, $currentpath, $arReturnTemplate) {
				global $AR;
				global $ARCurrent;
				if (!$arObject->CheckSilent("add", $type)) {
					$class .= "greyed";
				}
				$dotPos=strpos($type, '.');
				if (false!==$dotPos) {
					$realtype=substr($type, 0, $dotPos);
				} else {
					$realtype=$type;
				}

				$icon = $arObject->call("system.get.icon.php", array("type" => $type));

				$itemurl = $currentpath . $arReturnTemplate . "?arNewType=" . RawUrlEncode($type) . "&amp;" . ldGetServerVar("QUERY_STRING");
				$result = array(
					"type" => $type,
					"class" => $class,
					"icon" => $icon,
					"realtype" => $realtype,
					"href" => $itemurl,
					"name" => $name
				);
				return $result;
			}
		}
		
		if( !function_exists("getItems") ) {			
			function getItems($arObject, $typeslist, $currentpath, $arReturnTemplate) {
				$result = array();
				foreach( $typeslist as $type => $name ) {
					$result[] = checkType($arObject, $type, $name, $currentpath, $arReturnTemplate);
				}
				return $result;
			}
		}
		
		if (!($showall && $this->CheckSilent("layout"))) {
			$showall = 0;
		}
		
		$typeslist = getTypes($this, $showall);
		$itemlist = Array();
		if($typeslist && is_array($typeslist) && count($typeslist)) {
			$itemlist = getItems($this, $typeslist, $currentpath, $arReturnTemplate);
		} else {
			error($ARnls["ariadne:no_adding_found"]);
		}
?>
	<style type="text/css">
		html,body {
			height: 100%;
			width: 100%;
			padding: 0px;
			margin: 0px;
			font-family: verdana;
			font-size: 11px;
			background-color: #C1CAE2;
		}
		.listcontainer {
			background-color: #EFF3FF;
			height: 100%;
			width: 100%;
		}
		.listcontainer a.item {
			display: block;
			height: 70px;
			width: 88px;
			border: 1px solid #EFF3FF;
			background-color: #EFF3FF;
			text-align: center;
			padding-top: 7px;
		}
		.listcontainer a img {
			border: 0px;
		}
		.listcontainer a.item .icon {
		}
		.listcontainer a.item {
			float: left;
		}
		.listcontainer a {
			text-decoration: none;
			color: black;
		}
		.listcontainer a:hover {
			border: 1px solid #4A6799;
			background-color: #D0DCF7;
		}
		.listcontainer a.item .name {
			display: block;
			height: 32px;
		}
		div.showall {
			text-align: center;
			margin-bottom: 10px;
			width: 100%;
			height: 32px;
		}
		a.showall {
			line-height: 24px;
			padding-left: 10px;
			padding-right: 10px;
			padding-top: 2px;
			padding-bottom: 2px;
			border: 1px solid #EFF3FF;
		}
	</style>
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
