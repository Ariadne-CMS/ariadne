<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckSilent("read") && $this->CheckConfig()) {
		$emails = $this->getdata('emails', 'none');
?>
<fieldset id="contact">
	<legend><?php echo $ARnls["contactinformation"]; ?></legend>
	<div class="field">
		<label for="telephone" class="required"><?php echo $ARnls["telephone"]; ?></label>
		<input id="telephone" type="text" name="telephone" value="<?php $this->showdata("telephone", "none"); ?>" class="inputline wgWizAutoFocus">
	</div>
	<div class="field">
		<label for="fax" class="required"><?php echo $ARnls["fax"]; ?></label>
		<input id="fax" type="text" name="fax" value="<?php $this->showdata("fax", "none"); ?>" class="inputline">
	</div>
	<div class="field">
		<label for="emails" class="required"><?php echo $ARnls["email"]; ?></label>
		<?php
			$snippetDef = array(
				'emails' => array(
						'name' => 'emails',
						'type' => 'fieldlist',
						'label' => false,
						'class' => 'inputline',
						'value' => $emails
				)
			);
			$formSnippet = ar('html')->parse( (string)ar('html')->form( $snippetDef, null, null ) );
			$snippet = $formSnippet->getElementsByTagName('fieldset');

			echo (string)$snippet;

		?>
	</div>
</fieldset>
<?php } ?>
