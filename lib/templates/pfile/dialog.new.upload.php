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
		width: 100%;
		height: 100%;
		background-color: #eeeeee;
		border: 1px solid #cccccc;
		display: none;
		text-align: center;
	}
	#busy span {
		display: inline-block;
		height: 20px;
		position: absolute;
		top: 50%;
		margin-top: -10px;
		text-align: center;
		left: 0px;
		width: 100%;
	}
	#tabsdata label.fileinfo {
		margin-top: 0px;
	}
	#tabsdata #data {
		position: relative;
		margin-bottom: 10px;
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
	<div class="field fileinfo">
		<?php   if ($this->getdata('file_temp', $selectednls)) { ?>
			<div class="left">
				<label for="file" class="fileinfo">
					<?php echo $this->getdata('file', $selectednls); ?><br>
					<?php echo $this->make_filesize($this->getdata('file_size', $selectednls)); ?>
					<br><br>
				</label>
			</div>
			<div class="right">
				<img class="flag" src="<?php echo $flagurl; ?>" alt="<?php echo $selectedlanguage; ?>">
				<input id="file" type="file" value="<?php echo $this->getvar($selectednls . "[file]"); ?>" name="<?php echo $selectednls."[file]"; ?>" class="inputline wgWizAutoFocus" onchange="document.getElementById('busy').style.display = 'block'; this.form.submit();">
			</div>
		<?php	} else { ?>
			<img class="flag" src="<?php echo $flagurl; ?>" alt="<?php echo $selectedlanguage; ?>">
			<input id="file" type="file" value="<?php echo $this->getvar($selectednls . "[file]"); ?>" name="<?php echo $selectednls."[file]"; ?>" class="inputline wgWizAutoFocus" onchange="document.getElementById('busy').style.display = 'block'; this.form.submit();">
		<?php	} ?>
		<!-- input type="submit" name="upload" value="Upload" -->
	</div>
	<div id="busy"><span><?php echo $ARnls["ariadne:uploading"]; ?>...</span></div>
</fieldset>
<?php
	}
?>