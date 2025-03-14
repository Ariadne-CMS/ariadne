<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("edit") && $this->CheckConfig()) {
		$arEditorSettings = $this->call("editor.ini");
		$arpath = $this->getdata('arpath');
		if (!$arpath) {
			$arpath = $this->path;
		}
		$align = $this->getvar("align");
		$alttext = $this->getvar("alttext");
		$arstyle = $this->getvar("style");
?>
<script type="text/javascript">
	function callback(path) {
		document.getElementById("target").value = path;
		loadPreview();
	}

	function loadPreview() {
		var target      = document.getElementById("target").value;
		var style       = document.getElementById("style").value;
		var alttext     = document.getElementById("alttext").value;
		var align	= document.getElementById("align").value;

		muze.load('dialog.image.preview.html?target=' + escape(target) + '&style=' + escape(style) + '&alttext=' + escape(alttext) + '&align=' + escape(align)).onload(function(response) {
			document.getElementById("preview_span").innerHTML = response;
		});
	}
</script>
<style type="text/css">
	#data {
		margin-right: 350px;
	}
	.preview {
		position: absolute;
		right: 10px;
		width: 290px;
		top: 10px;
		padding: 0px;
		margin: 0px;
	}
	.preview img {
		max-height: 300px;
		max-width: 300px;
	}
</style>
<fieldset id="data" class="browse">
		<legend><?php echo $ARnls["ariadne:editor:image_settings"]; ?></legend>
		<div class="field">
			<input type="hidden" name="type" value="internal">
			<label for="target" class="required"><?php echo $ARnls["path"]; ?></label>
			<input onchange="loadPreview();" type="text" id="target" name="target" value="<?php echo htmlspecialchars($arpath??''); ?>" class="inputline wgWizAutoFocus">
			<input class="button" type="button" value="<?php echo $ARnls['browse']; ?>" title="<?php echo $ARnls['browse']; ?>" onclick='callbacktarget="extrauser"; window.open("<?php echo $this->make_ariadne_url($wgBrowsePath??''); ?>" + "dialog.browse.php", "browse", "height=480,width=920"); return false;'>
		</div>
		<div class="field">
			<label for="alttext"><?php echo $ARnls["ariadne:editor:image_alttext"]; ?></label>
			<input onchange="loadPreview();" type="text" id="alttext" name="alttext" value="<?php echo htmlspecialchars($alttext??''); ?>" class="inputline">
		</div>
		<div class="field">
			<label for="style"><?php echo $ARnls["ariadne:editor:style"]; ?></label>
			<select name="style" id="style" onchange="loadPreview();">
				<?php foreach ($arEditorSettings['image']['styles'] as $key => $style) { ?>
					<?php $selected = '';
					if ($key == $style) {
						$selected = " selected";
						$extra_attributes = '';
						foreach ($arEditorSettings['image']['styles'][$key]['attributes'] as $attr => $value) {
							$extra_attributes = $attr . "='$value' ";
						}
						$imgclass = $arEditorSettings['image']['styles'][$key]['class'];
					} ?>
					<option <?php echo $selected; ?> value="<?php echo $key; ?>"><?php echo $key; ?></option>
				<?php	} ?>
			</select>
		</div>
		<div class="field">
			<label for="align"><?php echo $ARnls["ariadne:editor:alignment"]; ?></label>
			<select name="align" id="align" onchange="loadPreview();">
				<?php
					$alignoptions = array(
						"none" => $ARnls["ariadne:editor:align_not_set"],
						"left" => $ARnls["ariadne:editor:align_left"],
						"right" => $ARnls["ariadne:editor:align_right"],
						"textTop" => $ARnls["ariadne:editor:align_texttop"],
						"absMiddle" => $ARnls["ariadne:editor:align_absmiddle"],
						"baseline" => $ARnls["ariadne:editor:align_baseline"],
						"absBottom" => $ARnls["ariadne:editor:align_absbottom"],
						"bottom" => $ARnls["ariadne:editor:align_bottom"],
						"middle" => $ARnls["ariadne:editor:align_middle"],
						"top" => $ARnls["ariadne:editor:align_top"]
					);

					foreach ($alignoptions as $key => $value) {
						$selected = '';
						if ($key == $align) {
							$selected = " selected";
						} ?>
						<option <?php echo $selected; ?> value="<?php echo $key; ?>"><?php echo $value; ?></option>
				<?php	} ?>
			</select>
		</div>
</fieldset>
<fieldset class="preview">
	<legend><?php echo $ARnls['ariadne:editor:preview']; ?></legend>
	<div>
	Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat
	<span id="preview_span">
		<?php $this->call("dialog.image.preview.html", array("target" => $arpath, "style" => $arstyle)); ?>
	</span>
	volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation
	ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat.
	Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse
	molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero
	eros et accumsan et iusto odio dignissim qui blandit praesent luptatum
	zzril delenit augue duis dolore te feugait nulla facilisi.
	</div>
</fieldset>
<?php
	}
?>
