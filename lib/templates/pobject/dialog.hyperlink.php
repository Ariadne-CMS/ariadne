<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("read") && $this->CheckConfig()) {

		include($this->store->get_config("code")."widgets/wizard/code.php");

		$arEditorSettings=$this->call('editor.ini');
		$behaviours=$arEditorSettings['link']['behaviours'];
		$linktypes=$arEditorSettings['link']['types'];

		$wgWizButtons = array(
			"delete" => array(
				"value" => $ARnls["delete"],
				"location" => "left"
			),
			"cancel" => array(
				"value" => $ARnls["cancel"]
			),
			"save" => array(
				"value" => $ARnls["save"]
			),
		);

		$wgWizFlow = array();
		$wgWizFlow[] = array(
			"current" => $this->getdata("wgWizCurrent","none"),
			"cancel" => "window.close.js",
			"save" => "dialog.hyperlink.save.php",
			"delete" => "dialog.hyperlink.delete.php"
		);
		$counter = 1;
		foreach ($linktypes as $linktype => $settings) {
			$wgWizFlow[0][$linktype] = "dialog.hyperlink." . $linktype . ".form.php";
			$wgWizFlow[] = array(
				"title" => $settings['name'],
				"image" => $AR->dir->images . 'wizard/hyperlink.' . $linktype. '.png', // FIXME: give this a decent icon or no icon
				"template" => "dialog.hyperlink." . $linktype . ".form.php"
			);
			$revLookup[$linktype] = $counter;
			$counter++;
		}

		$this->call("typetree.ini");
		$name=$ARCurrent->arTypeNames[$this->type];

		// spawn wizard
		$wgWizHeaderIcon = $AR->dir->images . 'wizard/hyperlink.png';
		$wgWizTitle=$ARnls["ariadne:editor:hyperlinkedit"];
		$wgWizHeader=$wgWizTitle;
		$wgWizNextStep = $this->getdata('wgWizNextStep');
		if (!$wgWizNextStep && $this->getdata('artype')) {
			$wgWizNextStep = $revLookup[$this->getdata('artype')];
		}
		include($this->store->get_config("code")."widgets/wizard/yui.wizard.html");
	}
?>
