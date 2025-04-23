<?php
	/******************************************************************

	******************************************************************/
	if ($this->CheckSilent("read")) {
		$arLanguage=$this->getdata("arLanguage","none");
		if (!$arLanguage) {
		 $arLanguage=$ARConfig->nls->default;
		}
		$selectednls=$arLanguage;
		$selectedlanguage=$ARConfig->nls->list[$arLanguage];

		$flagurl = $AR->dir->images."nls/small/$selectednls.gif";

		$userConfig = $this->loadUserConfig();
		if ( $userConfig['defaults']['publish']['publishScenarioDirs'] ?? null ) {
			$scenarioDir = end( $userConfig['defaults']['publish']['publishScenarioDirs'] );
		} else {
			$scenarioDir = "/system/newspaper/scenarios/";
		}
	?>
	<script type="text/javascript">
		summary=new Array();
		<?php
		$summaries = $this->ls($scenarioDir,"system.get.summary.phtml");

			foreach ($summaries as $key => $summary) {
				echo "summary['".key($summary)."']='".AddCSlashes(current($summary), ARESCAPE)."';\n";
			}
		?>
		function updatedescription(form) {
			if (form.scenario.selectedIndex!=-1) {
				document.getElementById("description_text").innerHTML = summary[form.scenario.options[form.scenario.selectedIndex].value];
			}
		}
	</script>
	<fieldset id="selectscenario">
		<legend><?php echo $ARnls["scenario"]; ?></legend>
		<div class="field">
			<select name="scenario" size="6" onChange="updatedescription(this.form)" class="inputline wgWizAutoFocus">
			<?php
				$this->ls($scenarioDir,"show.option.phtml", array("selected" => $this->getdata("scenario", "none")));
			?>
			</select>
		</div>
	</fieldset>
	<fieldset id="description">
		<legend><?php echo $ARnls['ariadne:info']; ?></legend>
		<div class="field" id="description_text">
		</div>
	</fieldset>
<script type="text/javascript">
	window.onload=init;
	function init() {
		document.wgWizForm["scenario"].focus();
		if (document.wgWizForm["scenario"].selectedIndex==-1) {
			document.wgWizForm["scenario"].options[0].selected=true;
		}
		updatedescription(document.wgWizForm);
	}
</script>
<?php
	}
?>
