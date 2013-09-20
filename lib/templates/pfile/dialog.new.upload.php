<?php
	$ARCurrent->nolangcheck = true;
	if( $this->CheckSilent("read") && $this->CheckConfig() ) {	
		$arLanguage=$this->getdata("arLanguage","none");
		if (!$arLanguage) {
			$arLanguage=$ARConfig->nls->default;
		}
		$selectednls=$arLanguage;
		$selectedlanguage=$AR->nls->list[$arLanguage];
		
		$flagurl = $AR->dir->images."nls/small/$selectednls.gif";
?>
<style>
	#busy {
		position: absolute;
		top: 0px;
		left: 0px;
		width: 300px;
		height: 32px;
		background-color: #eeeeee;
		padding-top: 20px;
		border: 1px solid #cccccc;
		display: none;
		text-align: center;
	}
	#data {
		position: relative;
	}
</style>
<script type="text/javascript">
	document.getElementById("wgWizForm").wgWizSubmitHandler = function() {
		document.getElementById("busy").style.display = 'block';
		return true;
	}
</script>
<fieldset id="data">
	<legend><?php echo $ARnls["file"]; ?></legend>
	<div class="field">
		<label for="file" class="required"><?php echo $ARnls["file"]; ?></label>
		<img class="flag" src="<?php echo $flagurl; ?>" alt="<?php echo $selectedlanguage; ?>">
		<input id="file" type="file" value="<?php echo $this->getvar($selectednls . "[file]"); ?>" name="<?php echo $selectednls."[file]"; ?>" class="inputline wgWizAutoFocus" onchange="this.form.submit();">
		<!-- input type="submit" name="upload" value="Upload" -->
	</div>
	<div id="busy"><?php echo $ARnls["ariadne:uploading"]; ?>...</div>
</fieldset>
<?php
	}
?>