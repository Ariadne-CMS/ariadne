<?php
	$ARCurrent->nolangcheck=true;
//	if ($this->CheckLogin("read") && $this->CheckConfig()) {
?>
	<!--fieldset>
		<legend><?php echo $ARnls["selectexporttype"]; ?></legend>
		<div class="field radio">
			<input id="ax" type="radio" name="exporttype" value="ax" checked>
			<label for="ax">AX</label>
		</div>
		<div class="field radio">
			<input id="wddx" type="radio" name="exporttype" value="wddx">
			<label for="wddx">WDDX</label>
		</div>
	</fieldset-->
        
    <?php
        if ($this->getvar('sources')) {
            $sources = $this->getvar("sources");
        } else {
            $sources = array($this->path);
        }
    ?>
    
    <fieldset id="data" class="export">
	<legend><?php echo $ARnls["ariadne:export"]; ?></legend>
        
        <?php
        
        foreach ($sources as $source) {
            
            $sourceob = current($this->get($source, "system.get.phtml"));
                            
            if (!$sourceob->CheckSilent("config")){  //config according to dialog.export.save, edit according to dialog.export.
                $checkfailed = true;
            } else {
                $checkfailed = false;
            }
            
            $type = $this->getvar("type");


            if (!$type) {
                $type = $sourceob->type;
            }
            
            $icon = $ARCurrent->arTypeIcons[$sourceob->type]['medium'] ? $ARCurrent->arTypeIcons[$sourceob->type]['medium'] : $sourceob->call("system.get.icon.php", array('size' => 'medium'));
            $iconalt = $sourceob->type;
            
            if ( $sourceob->implements("pshortcut") ) {
                $overlay_icon = $icon;
                $overlay_alt = $sourceob->type;
                if ( $ARCurrent->arTypeIcons[$sourceob->vtype]['medium'] ) {
                    $icon = $ARCurrent->arTypeIcons[$sourceob->vtype]['medium'];
                } else {
                    $icon = current($sourceob->get($sourceob->data->path, "system.get.icon.php", array('size' => 'medium')));
                }
            }

            if($checkfailed){
                echo '<div class="checkfailed">';
                echo '<span class="error">' . $ARnls["err:nogrants"] .'</span><br>';
            } else {
                echo '<div>';
            }
            echo '<img src="' . $icon . '" alt="' . htmlspecialchars($iconalt) . '" title="' . htmlspecialchars($iconalt) . '" class="typeicon">';
            if ( $overlay_icon ) {
                echo '<img src="' . $overlay_icon . '" alt="' . htmlspecialchars($overlay_alt) . '" title="' . htmlspecialchars($overlay_alt) . '" class="overlay_typeicon">';
            }
            echo '<div class="name">' . $sourceob->nlsdata->name . ' ';
            echo "( " . $ARCurrent->arTypeNames[$sourceob->type] . " / " . $sourceob->type . " )";
            echo '</div>';
            echo '<div class="path">' . $sourceob->path . '</div><br>';
            echo '</div>';
        }
    ?>
        
	<fieldset>
		<legend><?php echo $ARnls["options"]; ?></legend>
		<div class="field radio">
			<input id="without_grants" type="checkbox" name="without_grants" value="1" checked>
			<label for="without_grants"><?php echo $ARnls["withoutgrants"]; ?></label>
		</div>
		<div class="field radio">
			<input id="full_path" type="checkbox" name="full_path" value="1">
			<label for="full_path"><?php echo $ARnls["fullpath"]; ?></label>
		</div>
	</fieldset>
	<fieldset>
		<legend><?php echo $ARnls["advanced"]; ?></legend>
		<div class="field radio">
			<input id="without_data" type="checkbox" name="without_data" value="1">
			<label for="without_data"><?php echo $ARnls["withoutdata"]; ?></label>
		</div>
		<div class="field radio">
			<input id="without_files"  type="checkbox" name="without_files" value="1">
			<label for="without_files"><?php echo $ARnls["withoutfiles"]; ?></label>
		</div>
		<div class="field radio">
			<input id="without_templates" type="checkbox" name="without_templates" value="1">
			<label for="without_templates"><?php echo $ARnls["withouttemplates"]; ?></label>
		</div>
	</fieldset>
    </fieldset>
<?php
//	}
?>