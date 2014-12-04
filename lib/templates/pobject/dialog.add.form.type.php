<?php
	if ($this->CheckLogin("read") && $this->CheckConfig()) {
		echo '<fieldset id="type">';
		if (!$location) {
			$location = $this->path;
		}
		$locationOb = current($this->get($location, "system.get.phtml"));
		$typeslist = yui::getTypes($locationOb, $showall);
		$itemlist = array();
		if($typeslist && is_array($typeslist) && count($typeslist)) {
			$itemlist = yui::getItems($locationOb, $typeslist, $location, "dialog.add.php");
			foreach ($itemlist as $item) {
				if ($this->CheckLogin('add', $item['type'])) {
					$typeslist[$item['type']] = ar('html')->el(
						"div",
						ar('html')->el(
							"img",
							array("src" => $item['icon'], "alt" => $item['name'], "title" => $item['name'])
						) . $item['name']);
				} else {
					unset($typeslist[$item['type']]);
				}
			}

		} else {
			// error($ARnls["ariadne:no_adding_found"]);
		}

		if ($arNewType) {
			echo "<legend>" . $ARnls['ariadne:new:create_object'] . "</legend>";
			$typeslist = array(
				$arNewType => $typeslist[$arNewType],
//						'' => ar('html')->el("div", array("class" => "otherType"), $ARnls['ariadne:new:change_type'])
			);

			$fields = array(
				"arNewType" => array(
					"type" => "radio",
					"value" => $arNewType,
					"options" => $typeslist,
					"label" => false,
					"class" => "type_selected"
				)
			);
		} else {
			echo '<legend>' . $ARnls['ariadne:new:select_type'] . '</legend>';

			if ($this->CheckSilent("layout")) {
				$fields = array(
					"showall" => array(
						"type" => "checkbox",
						"value" => $showall,
						"label" => $ARnls['ariadne:new:show_all_types'],
						"checkedValue" => 1,
						"uncheckedValue" => 0
					)
				);
				$snippit = ar('html')->form($fields, false)->getHTML()->childNodes;
				foreach ($snippit->getElementsByTagName("input") as $checkOption) {
					$checkOption->setAttribute("onclick", "this.form.submit();");
				}
				echo $snippit;
			}


			$fields = array(
				"arNewType" => array(
					"type" => "radio",
					"value" => $arNewType,
					"options" => $typeslist,
					"label" => false,
					"class" => "type"
				)
			);
		}
		$snippit = ar('html')->form($fields, false)->getHTML()->childNodes;
		foreach ($snippit->getElementsByTagName("input") as $radioOption) {
			$radioOption->setAttribute("onclick", "this.form.submit();");
		}
		foreach ($snippit->getElementsByTagName("label") as $radioLabel) {
			$radioLabel->setAttribute("onclick", "var f=this.form; document.getElementById(this.getAttribute('for')).checked=true; f.submit();");
		}
		echo $snippit;

		if ($arNewType) {
			echo '<input tabindex="-1" style="position: absolute; left: -1000px; width: 0px; height: 0px;" unselectable="on" type="submit" name="wgWizControl" class="wgWizControl" value="current">';
			echo '<button class="othertype" type="submit" name="arNewType" value="">' . $ARnls['ariadne:new:change_type'] . '</button>';
		}
		echo '</fieldset>';
	}
?>
