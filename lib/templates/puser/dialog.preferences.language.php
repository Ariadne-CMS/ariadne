<?php
	/******************************************************************

	 no result.

	******************************************************************/
	$ARCurrent->nolangcheck=true;
	if ($this->CheckSilent("read")) {
?>
	<fieldset id="language">
	<legend><?php echo $ARnls["language"]; ?></legend>
		<div class="field">
			<label for="language"><?php echo $ARnls["default"]; ?></label>
			<select name="language">
			<?php
				if (!$newtype) {
					$language=$data->language;
				} else {
					$language=$ARCurrent->default;
				}
				reset($AR->nls->list);
				while (list($key, $value)=each($AR->nls->list)) {
					if ($key==$language) {
						echo "<option value=\"$key\" selected>$value</option>\n";
					} else {
						echo "<option value=\"$key\">$value</option>\n";
					}
				}
			?>
			</select>
		</div>
		<?php echo $ARnls["show"]; ?>
		<input type="hidden" name="languagelist[none]" value="None">
		<?php
			$languagelist=$this->getdata("languagelist","none");
			reset($AR->nls->list);
			asort($AR->nls->list);
			while (list($arnls, $value)=each($AR->nls->list)) {
				if ($languagelist[$arnls]) {
					$image=$AR->dir->images.'nls/small/'.$arnls.'.gif';
					$selected=" checked";
				} else {
					$image=$AR->dir->images.'nls/small/faded/'.$arnls.'.gif';
					$selected="";
				}
				?>
					<div class="field checkbox flag left">
						<a href="#" onClick="selectnls('<?php echo $arnls; ?>');">
							<img id="flag_<?php echo $arnls; ?>" alt="<?php echo $value; ?>" src="<?php echo $image; ?>"></a>
						<input type="checkbox" id="checkbox_<?php echo $arnls; ?>"
							name="languagelist[<?php echo $arnls; ?>]" onClick="setImage('<?php echo $arnls; ?>');"
							value="<?php echo $value; ?>"<?php echo $selected; ?>>
						<label for="checkbox_<?php echo $arnls; ?>"><?php echo $value; ?></label>
					</div>
				<?php
			}
		?>
	</fieldset>
	<script type="text/javascript">
		function selectnls(nls) {
			var checkbox=document.getElementById('checkbox_'+nls);
			if (checkbox.checked) {
				checkbox.checked=false;
			} else {
				checkbox.checked=true;
			}
			setImage(nls);
			return false;
		}
		function setImage(nls) {
			var checkbox=document.getElementById('checkbox_'+nls);
			if (checkbox.checked) {
				document.images['flag_'+nls].src='<?php echo $AR->dir->images.'nls/small/'; ?>'+nls+'.gif';
			} else {
				document.images['flag_'+nls].src='<?php echo $AR->dir->images.'nls/small/faded/'; ?>'+nls+'.gif';
			}
			return false;
		}
	</script>
<?php
	}
?>