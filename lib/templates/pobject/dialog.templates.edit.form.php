<?php

	$type = $this->getvar("type");
	$function = $this->getvar("function");
	$language = $this->getvar("language");

	if( $newtype = $this->getvar("newtype") ) {
		$type = $newtype;
	}
	
	if( $newfunction = $this->getvar("newfunction") ) {
		$function = $newfunction;
	}
	
	if( $newlanguage = $this->getvar("newlanguage") ) {
		$language = $newlanguage;
	}


	$svn_enabled = $AR->SVN->enabled;
	if ($svn_enabled) {
		$filestore = $this->store->get_filestore_svn("templates");
		$svnstack = &PEAR_ErrorStack::singleton('VersionControl_SVN');
		$svn = $filestore->connect($this->id);
		$svn_status = $filestore->svn_status($svn);
	} else {
		$filestore = $this->store->get_filestore("templates");
	}

	$template = $this->getvar("template");
	if( !isset($template) ) {
		$file = "";
		if ($this->data->config->pinp[$type][$function][$language]) {
			$template=$type.".".$function.".".$language.".pinp";
			$templates=$this->store->get_filestore("templates");
			if ($templates->exists($this->id, $template)) {
				$file=$templates->read($this->id, $template);
			}
		}
	} else {
		$file = $template;
	}
	$file=ereg_replace("&","&amp;",$file);
	$file=ereg_replace("<","&lt;", ereg_replace(">","&gt;",$file));

?>
	<script type="text/javascript">
		function objectadded() {
			if (window.opener && window.opener.objectadded) {
				window.opener.objectadded();
			}
			window.location.href = window.location.href;
		}
	</script>
	<div id="basicmenu" class="yuimenubar">
		 <div class="bd">
			  <ul class="first-of-type">
<?php 
	if ($svn_enabled && $svn['info']['Revision']) {
	
		$filename = $type.".".$function.".".$language.".pinp";
		switch($svn_status[$filename]) {
			// Fixme: find out the codes for "locked", "read only" and add them.
			case "C":
				$svn_img = "ConflictIcon.png";
				$svn_alt = $ARnls['ariadne:svn:conflict'];
				break;
			case "M":
				$svn_img = "ModifiedIcon.png";
				$svn_alt = $ARnls['ariadne:svn:modified'];
				break;
			case "?":
				break;
			case "A":
				$svn_img = "AddedIcon.png";
				$svn_alt = $ARnls['ariadne:svn:added'];
				break;
			case "D":
				$svn_img = "DeletedIcon.png";
				$svn_alt = $ARnls['ariadne:svn:deleted'];
			case "!":
				$svn_style = "filter: alpha(opacity=30); opacity: 0.3;";
				$svn_style_hide = "filter: alpha(opacity=0); opacity: 0;";
				break;
			default:
				$svn_img = "InSubVersionIcon.png";
				$svn_alt = $ARnls['ariadne:svn:insubversion'];
				break;
		}	
		$svn_img_src = $AR->dir->images . "/svn/$svn_img";
?>
					<li class="yuimenubaritem">
						<a class="yuimenubaritemlabel" href="#"><?php 
							if ($svn_img) { 
								?><img class="svn_icon" alt="<?php echo $svn_alt; ?>" src="<?php echo $svn_img_src; ?>">
							<?php } ?><?php echo $ARnls["ariadne:svn"]; ?></a>
						<div id="svn" class="yuimenu">
							<div class="bd">
                                <ul class="first-of-type">
                                    <li class="yuimenuitem"><a class="yuimenuitemlabel" href="dialog.svn.templates.commit.php?type=<?php echo rawurlencode($type); ?>&function=<?php echo rawurlencode($function); ?>&language=<?php echo rawurlencode($language);?>" onclick="muze.ariadne.explore.arshow('edit_object_data', this.href); return false;"><?php echo $ARnls["ariadne:svn:commit"]; ?></a></li>
                                    <li class="yuimenuitem"><a class="yuimenuitemlabel" href="dialog.svn.templates.update.php?type=<?php echo rawurlencode($type); ?>&function=<?php echo rawurlencode($function); ?>&language=<?php echo rawurlencode($language);?>" onclick="muze.ariadne.explore.arshow('edit_object_data', this.href); return false;"><?php echo $ARnls["ariadne:svn:update"]; ?></a></li>
                                    <li class="yuimenuitem"><a class="yuimenuitemlabel" href="dialog.svn.templates.diff.php?type=<?php echo rawurlencode($type); ?>&function=<?php echo rawurlencode($function); ?>&language=<?php echo rawurlencode($language);?>" onclick="muze.ariadne.explore.arshow('edit_object_data', this.href); return false;"><?php echo $ARnls["ariadne:svn:diff"]; ?></a></li>
                                    <li class="yuimenuitem"><a class="yuimenuitemlabel" href="dialog.svn.templates.revert.php?type=<?php echo rawurlencode($type); ?>&function=<?php echo rawurlencode($function); ?>&language=<?php echo rawurlencode($language);?>" onclick="muze.ariadne.explore.arshow('edit_object_data', this.href); return false;"><?php echo $ARnls["ariadne:svn:revert"]; ?></a></li>
                                    <li class="yuimenuitem"><a class="yuimenuitemlabel" href="dialog.svn.templates.delete.php?type=<?php echo rawurlencode($type); ?>&function=<?php echo rawurlencode($function); ?>&language=<?php echo rawurlencode($language);?>" onclick="muze.ariadne.explorer.arshow('edit_object_data', this.href); return false;"><?php echo $ARnls["ariadne:svn:delete"]; ?></a></li>
							<?php
									if( $svn_status[$filename] ) {
							?>
                                    <li class="yuimenuitem"><a class="yuimenuitemlabel" href="dialog.svn.templates.resolved.php?type=<?php echo rawurlencode($type); ?>&function=<?php echo rawurlencode($function); ?>&language=<?php echo rawurlencode($language);?>" onclick="muze.ariadne.explorer.arshow('edit_object_data', this.href); return false;"><?php echo $ARnls["ariadne:svn:resolved"]; ?></a></li>
							<?php
									}
							?>    
                                </ul>            
							</div>
						</div>                    
					</li>
<?php
	}
	if( $this->CheckSilent("config") ) {
?>
					<li class="yuimenubaritem">
						 <a class="yuimenubaritemlabel" href="dialog.grantkey.php" onclick="muze.ariadne.explore.arshow('edit_object_grantkey', this.href); return false;">
							<?php echo $ARnls['ariadne:grantkey']; ?>
						 </a>
					</li>
<?php
	}
?>
					<li class="yuimenubaritem">
						 <a class="yuimenubaritemlabel" href="http://www.ariadne-cms.org/docs/reference/" onclick="muze.ariadne.explore.arshow('_new', this.href); return false;">
							<?php echo $ARnls['help']; ?>
						 </a>
					</li>
			  </ul>
		 </div>
	</div>
<style>
	#tabsdata #template_row_counter, #tabsdata #template_col_counter, #tabsdata #template_char_counter {
		border: 0px;
		background-color: transparent;
		width: 30px;
		display: inline-block;
	}
</style>
<div id="template_options">
	<div class="template_option">
		<label for="newtype" class="ontop"><?php echo $ARnls["type"]; ?></label>
		<select name="newtype" id="newtype">
			<?php
				$this->call('dialog.types.optionlist.php', array("selected" => $type));
			?>
		</select>
	</div>
	<div class="template_option wide_template_option">
		<label for="newfunction" class="ontop"><?php echo $ARnls["template"]; ?></label>
		<input type="text" name="newfunction" class="inputline" value="<?php echo $function; ?>">
	</div>
	<div class="template_option">
		<label for="newlanguage" class="ontop"><?php echo $ARnls["language"]; ?></label>
		<select name="newlanguage" id="newlanguage">
			<option value="any"><?php echo $ARnls["any"]; ?></option>
			<?php
				foreach( $AR->nls->list as $key => $value ) {
					if ($language==$key) {
						echo "<option value=\"$key\" selected>$value</option>\n";
					} else {
						echo "<option value=\"$key\">$value</option>\n";
					}
				}
			?>
		</select>
	</div>
	<div class="template_option">
	<?php 
		if ($data->config->templates[$type][$function][$language] || !$function) {
			$default=1;
		}
	?>
		<label for="default" class="ontop"><?php echo $ARnls["default"]; ?></label>
		<input type="checkbox" id="default" name="default" value="1" <?php if ($default) { echo " checked"; } ?>>
	</div>
	<input type="hidden" id="cursorOffset" name="cursorOffset">
<?php
	// reset lineOffset always
?>
	<input type="hidden" id="lineOffset" name="lineOffset" value="-1">
</div>
<div id="template_editor">
	<textarea name="template" id="template" wrap="off"><?php echo $file; ?></textarea>
</div>
<div id="template_linenumbers">
	<textarea name="linenumbers" id="linenumbers" wrap="off" readonly class="linenumbers" tabindex="-1" unselectable="on"><?php
	$linetotal = substr_count($file, "\n");
	$linetotal = $linetotal + 1000;
for($i=1;$i<$linetotal;$i++) { echo $i."\n"; }
	?></textarea>
</div>
<div id="template_status">
	<div id="template_row"><?php echo $ARnls['ariadne:template:row']; ?>: <input unselectable="on" disabled="true" id="template_row_counter" value="1"></div>
	<div id="template_col"><?php echo $ARnls['ariadne:template:col']; ?>: <input unselectable="on" disabled="true" id="template_col_counter" value="1"></div>
	<div id="template_char"><?php echo $ARnls['ariadne:template:char']; ?>: <input unselectable="on" disabled="true" id="template_char_counter" value="1"></div>
</div>
<script>

	function scrollHandler(event,obj) {
		var scrollpos = obj.scrollTop;
		document.getElementById("linenumbers").scrollTop = scrollpos;
	}

	function keyHandler(event,obj) {

		event = muze.event.get(event);
		var result = true;
		var tabKeyCode = 9;
		var escapeKeyCode = 27;
		if (event.which) { // mozilla
			var keycode = event.which;
		} else {// ie
			var keycode = event.keyCode;
		}
		if (keycode == tabKeyCode) {
			if (event.type == "keydown") {
				if (obj.setSelectionRange) {
					// mozilla - dom
					var scrollTop = obj.scrollTop;
					var s = obj.selectionStart;
					var e = obj.selectionEnd;
					obj.value = obj.value.substring(0, s) + 
						"\t" + obj.value.substr(e);
					obj.setSelectionRange(s + 1, s + 1);
					obj.focus();
					obj.scrollTop=scrollTop;
				} else if (obj.createTextRange) {
					// ie
					document.selection.createRange().text="\t"
					//var tempTabHandler = null;
					//tempTabHandler = muze.event.attach(obj, 'blur', 
					//	function() { obj.focus(); muze.event.detach(obj, 'blur', tempTabHandler, false); }, false);
				} else {
					// unsupported browsers
				}
			}
			result = false;
		} else if (keycode == escapeKeyCode) {
			result = false; // should work in all browsers
		}
		updateStatusBar();
		
		return result ? muze.event.pass(event) : muze.event.cancel(event);
	}

	function updateStatusBar() {
		function replaceContent(el, newChild) {
			if (el.firstChild) {
				el.replaceChild(newChild, el.firstChild);
			} else {
				el.appendChild(newChild);
			}
		}

		var pos = muze.util.textarea.getCursorPosition(document.getElementById('template'));
		document.getElementById('template_row_counter').value = pos.row;
		document.getElementById('template_col_counter').value = pos.col;
		document.getElementById('template_char_counter').value = pos.offset;
		document.getElementById('cursorOffset').value = pos.offset;
	}

	function initHandlers() {
	
		var wgWizForm = document.getElementById("wgWizForm");
		wgWizForm.wgWizSubmitHandler = function() {
			var lines = document.getElementById("linenumbers")
			lines.value = "";
			return true;
		}
	
<?php
		$error = $this->getvar("error");
		if( $error ) {
			echo "alert('".AddCSlashes($error, ARESCAPE)."');\n";
		}
?>
		var area = document.getElementById("template");
		muze.event.attach(area, 'click', updateStatusBar, false);
		muze.event.attach(area, 'keydown', function(evt) { return keyHandler(evt, area); }, false);
		muze.event.attach(area, 'keyup', function(evt) { return keyHandler(evt, area); }, false);
		muze.event.attach(area, 'keypress', function(evt) { return keyHandler(evt, area); }, false);
		muze.event.attach(area, 'scroll', function(evt) { return scrollHandler(evt, area); }, false);

		area.focus();
		
<?php		
		// set the cursor pos if needed
		$col = 0;
		$pos = $this->getvar("cursorOffset");
		if( !isset($pos) ) {
			$pos = 0;
		}
		$line = $this->getvar("lineOffset");
		if( isset($line) && $line != -1 ) {
			$pos = 'false';
			$col = 1;
		} else {
			$line = 0;
		}
?>
		var pos = new muze.util.textarea.Position( <?php echo $line; ?>, 0, 0, <?php echo $pos; ?>);
		muze.util.textarea.setCursorPosition(area, pos);
		updateStatusBar();

	}
	
	YAHOO.util.Event.onDOMReady(initHandlers);
</script>