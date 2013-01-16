<?php
	$ARCurrent->nolangcheck=true;
        if ($this->CheckLogin("add") && $this->CheckConfig()) {
		require_once($this->store->get_config("code")."modules/mod_yui.php");

		$showall = $this->getvar('showall');
		if (!($showall && $this->CheckSilent("layout"))) {
			$showall = 0;
		}

		$location = $this->getvar('location');
		if (!$location) {
			$location = $this->path;
		}
		$locationOb = current($this->get($location, "system.get.phtml"));

		$arNewType = $this->getvar('arNewType');
		$arNewFilename = $this->getvar('arNewFilename');
		if (!$arNewFilename) {
			$arNewFilename = "{5:id}";
		}

		$arLanguages = $this->getvar('arLanguages');

		if (!$arLanguages) {
			$arLanguages = array(
				$ARConfig->nls->default
			);
		} else {
			$arLanguages[] = $ARConfig->nls->default;
		}

		$addLanguage = $this->getvar('addLanguage');
		$extraLanguage = $this->getvar('extraLanguage');
		if ($addLanguage && $extraLanguage) {
			$arLanguages[] = $extraLanguage;			
		}
		$arLanguages = array_unique($arLanguages);

		$arLanguage=$this->getdata("arLanguage","none");
		if (!$arLanguage) { // language select hasn't been done yet, so start with default language
			$arLanguage=$ARConfig->nls->default;
			$ARCurrent->arLanguage=$arLanguage;
		}

		$ARnls['ariadne:add:location'] = 'Locatie';
		$ARnls['ariadne:add:below'] = 'Onder het huidige object, een niveau dieper';
		$ARnls['ariadne:add:beside'] = 'Naast het huidige object, op hetzelfde niveau';
		$ARnls['ariadne:add:select_type'] = 'Kies een object type om aan te maken:';
		$ARnls['ariadne:add:show_all_types'] = 'Toon alle types';
		$ARnls['ariadne:add:extralanguages'] = 'Extra taalgegevens toevoegen';
		$ARnls['ariadne:add:default_language_data'] = 'Gegevens standaardtaal';
		$ARnls['ariadne:add:add_language'] = 'Taal toevoegen';
		$ARnls['ariadne:add:change_type'] = 'Wijzig type';

?>
		<style type="text/css">
			#tabsdata label {
				display: inline-block;
				width: 120px;
			}

			#location div.radioButton input,
			#location div.radioButton label {
				display: inline;
			}

			#location div.radioButton {
				margin-bottom: 3px;
			}

			#type label {
				width: 72px;
			}

			#type div.formCheckbox input,
			#type div.formCheckbox label {
				display: inline;
				vertical-align: middle;
			}
 
			#type div.formRadio.type input,
			#type div.formRadio.type_selected input {
				visibility: hidden;
				height: 0px;
			}

			#type div.formRadio.type input + label,
			#type div.formRadio.type_selected input + label {
				border: 1px solid transparent;
				padding: 3px;
				margin-top: 0px;
			}

			#type div.formRadio.type input:hover + label {
				border: 1px solid #4A6799;
				background-color: #D0DCF7;
			}

			#type div.formRadio.type input:checked + label {
				border: 1px solid #4A6799;
				background-color: #316AC5;
				color: white;
			}
			#type div.formRadio.type div.radioButton,
			#type div.formRadio.type_selected div.radioButton {
				display: inline-block;
				text-align: center;
				margin-right: 5px;
			}
			#type div.formRadio.type div.radioButton img,
			#type div.formRadio.type_selected div.radioButton img {
				display: block;
				margin: 0px auto;
			}
			#type {
				position: relative;
			}
			#type .othertype {
				position: absolute;
				top: 10px;
				right: 10px;
			}

			fieldset.editdata fieldset legend {
				display: block;
				margin-left: 20px;
				padding-left: 5px;
				padding-right: 5px;
			}
			fieldset.editdata table {
				border-collapse: collapse;
				margin: 0px;
				padding: 0px;
			}

			#tabsdata fieldset.editdata fieldset {
				border: 0px;
				margin: 0px;
				padding: 0px;
				border-top: 1px solid #888888;
			}
			img.flag {
				display: none;
			}
			#languages div.formField {
				display: inline;
			}
			button.addLanguage {
				display: inline;
			}
			#tabsdata div.right {
				float: right;
			}
		</style>
		<fieldset id="location">
			<legend><?php echo $ARnls['ariadne:add:location']; ?></legend>
			<?php
				$fields = array(
					"location" => array(
						"type" => "radio",
						"options" => array(
							$this->path => $ARnls['ariadne:add:below'],
							$this->parent => $ARnls['ariadne:add:beside']
						),
						'class' => 'field',
						'value'=> $location,
						'label' => false
					)
				);
				$snippit = ar('html')->form($fields, false)->getHTML()->childNodes;
				foreach ($snippit->getElementsByTagName("input") as $radioOption) {
					$radioOption->setAttribute("onclick", "this.form.submit();");
				}
				echo $snippit;
			?>
		</fieldset>

		<fieldset id="type">
			<?php
				$typeslist = yui::getTypes($locationOb, $showall);
				$itemlist = Array();
				if($typeslist && is_array($typeslist) && count($typeslist)) {
					$itemlist = yui::getItems($locationOb, $typeslist, $location, "dialog.add.php");
					foreach ($itemlist as $item) {
						$typeslist[$item['type']] = ar('html')->el(
							"div",
							ar('html')->el(
								"img",
								array("src" => $item['icon'], "alt" => $item['name'], "title" => $item['name'])
							) . $item['name']);
					}

				} else {
					error($ARnls["ariadne:no_adding_found"]);
				}

				if ($arNewType) {
					echo "<legend>Object aanmaken: " . $itemlist[$arNewType]['name'] . "</legend>";
					$typeslist = array(
						$arNewType => $typeslist[$arNewType],
//						'' => ar('html')->el("div", array("class" => "otherType"), $ARnls['ariadne:add:change_type'])
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
			?>
				<legend><?php echo $ARnls['ariadne:add:select_type']; ?></legend>
				<?php
					
					if ($this->CheckSilent("layout")) {
						$fields = array(
							"showall" => array(
								"type" => "checkbox",
								"value" => $showall,
								"label" => $ARnls['ariadne:add:show_all_types'],
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
				echo $snippit;

				if ($arNewType) { ?>
					<button class="othertype" type="submit" name="arNewType" value="">Wijzig type</button>
				<?php }
			?>			
		</fieldset>
		<?php if ($arNewType) { ?>
			<fieldset class="editdata">
				<?php if (sizeof($arLanguages) > 1) { ?>
					<legend><?php echo $ARnls['ariadne:add:default_language_data']; ?> : <?php echo $ARConfig->nls->list[$arLanguage]; ?></legend>
				<?php } else { ?>
					<legend><?php echo $ARnls['data']; ?></legend>
				<?php } ?>
			<?php 
				$arNewData=new object;
				$arNewPath=$this->make_path($arNewFilename);
				$wgWizCallObject=$this->store->newobject($arNewPath, $this->path, $arNewType, $arNewData);
				$wgWizCallObject->arIsNewObject=true;

				$configcache=$ARConfig->cache[$this->path]; // use parent path
				if ($configcache->custom) {
					foreach( $configcache->custom as $key => $definition ) {
						if (($definition["type"]==$arNewType) || // use new type
							($definition["inherit"] && ($wgWizCallObject->AR_implements($definition["type"])))) { // check new object
							$hascustomdata=true;
							break;
						}
					}
				}

				include($this->store->get_config("code")."widgets/wizard/code.php");

				$wgWizFlow = array();
				$wgWizFlow[] = array(
					"current" => $this->getdata("wgWizCurrent","none"),
					"cancel" => "dialog.edit.cancel.php",
					"save" => "dialog.edit.save.php"
				);
				// inject filename step
				$wgWizFlow[] = array(
					"title" => $ARnls["filename"],
					"image" => $AR->dir->images.'wizard/info.png',
					"template" => "dialog.new.filename.php",
					"nolang" => true
				);
				
				$wgWizFlow[] = array(
					"title" => $ARnls["data"],
					"image" => $AR->dir->images.'wizard/data.png',
					"template" => "dialog.edit.form.php"
				);


				// Call edit flow which will add the remaining flow to the wizard if appropriate
				$wgWizFlow = $wgWizCallObject->call("dialog.edit.flow.php", array( "wgWizFlow" => $wgWizFlow ));

				// Call new flow which will can override the edit flow
				$wgWizFlow = $wgWizCallObject->call("dialog.new.flow.php", array( "wgWizFlow" => $wgWizFlow ));

				// Custom data and locking gets added last.
				if( false && $hascustomdata ) { // Skip custom data for now, it doesn't play nice with the rest.
					$wgWizFlow[] = array(
						"title" => $ARnls["customdata"],
						"image" => $AR->dir->images.'wizard/customdata.png',
						"template" => "dialog.edit.custom.php" 
					);
				}

				// call user overridable new flow
				$wgWizFlow = $wgWizCallObject->call("user.wizard.new.html", Array("wgWizFlow" => $wgWizFlow));


				$showLanguageSelect = false;
				foreach ($wgWizFlow as $step) {
					if ($step['template']) {
						$wgWizCallObject->call($step['template'], array("arNewType" => $arNewType, "arLanguage" => $arLanguage));
						if (!$step['nolang']) {
							$showLanguageSelect = true;
						}
					}
				}
			?>

			</fieldset>

			<?php 
				$languageList = $ARConfig->nls->list;
				$usedLanguages = $arLanguages;
				foreach ($usedLanguages as $language) {
					unset($languageList[$language]);
				}

				foreach ($usedLanguages as $extraLanguage) {
					echo '<input type="hidden" name="arLanguages[]" value="' . htmlspecialchars($extraLanguage) . '">';

					if ($extraLanguage != $arLanguage) {

				?>
				<fieldset class="editdata">
					<legend><?php echo $ARnls['data']; ?> : <?php echo $ARConfig->nls->list[$extraLanguage]; ?></legend>
						<?php	
							foreach ($wgWizFlow as $step) {
								if ($step['template'] && !$step['nolang']) {
									$wgWizCallObject->call($step['template'], array("arNewType" => $arNewType, "arLanguage" => $extraLanguage));
								}
							}
						?>
				</fieldset>
				<?php
					}
				}

				if ($showLanguageSelect && sizeof($languageList) > 0) {
					$fields = array(
						"arLanguages" => array(
							"type" => "select",
							"options" => $languageList,
							"label" => false,
							"name" => "extraLanguage",
						),
						"addLanguage" => array(
							"type" => "button",
							"value" =>
							$ARnls['ariadne:add:add_language'],
							"label" => false,
							"class" => "addLanguage"
						)
					);
			 ?>
				<fieldset id="languages">
					<legend><?php echo $ARnls['ariadne:add:extralanguages']; ?></legend>
					<?php echo ar('html')->form($fields, false)->getHTML()->childNodes; ?>
				</fieldset>
			<?php } ?>
		<?php } ?>
<?php
	}
?>