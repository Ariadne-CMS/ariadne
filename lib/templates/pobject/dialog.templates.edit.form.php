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
		$svn_info = $filestore->svn_info($svn);
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
	$file = htmlentities($file, ENT_QUOTES, 'UTF-8');
?>
	<script type="text/javascript">
		function objectadded() {
			if (window.opener && window.opener.objectadded) {
				window.opener.objectadded();
			}
			window.location.href = window.location.href;
		}
	</script>
	<?php
		if ($AR->user->data->template_editor == 'ace') {
	?>
	<style type="text/css">
		#tabsdata #template_editor #template, #editor {
			position: absolute;
			width: 100%;
			height: 100%;
			border: 0px;
			padding: 0px;
			margin: 0px;
		}
		#editor {
			background-color: white;
			cursor: text;
		}
		#editor, #editor div {
			font: 12px/normal 'Monaco', 'Menlo', 'Ubuntu Mono', 'Consolas', 'source-code-pro', monospace;
		}
		#tabsdata #template_editor {
			left: 22px;
		}
	</style>

	<script type="text/javascript" src="<?php echo $AR->dir->www; ?>js/ace/ace.js" charset="utf-8"></script>
	<script type="text/javascript" src="<?php echo $AR->dir->www; ?>js/ace/theme-eclipse.js" charset="utf-8"></script>
	<script type="text/javascript" src="<?php echo $AR->dir->www; ?>js/ace/mode-php.js" charset="utf-8"></script>
	<script type="text/javascript">
		var editor = null;
		window.onload = function() {
			var template = document.getElementById('template');
			template.style.display = 'none';
			var editorDiv = document.getElementById('editor');
			editorDiv.style.display = 'block';
			editor = ace.edit('editor');
			editor.setTheme('ace/theme/eclipse');
			var phpMode = ace.require('ace/mode/php').Mode;

			editor.getSession().setMode( new phpMode() );
			editor.getSession().setUseSoftTabs(false);
			editor.setShowPrintMargin(false);
			editor.setBehavioursEnabled(false);

			editor.getSession().setValue( template.value );
			<?php
				$error = $this->getvar("error");
				if( $error ) {
					echo "alert('".AddCSlashes($error, ARESCAPE)."');\n";
				}
				// set the cursor pos if needed
				$col = $this->getvar("cursorOffset");
				if( !isset($col) || $col == '') {
					$col = 1;
				}
				$line = $this->getvar("lineOffset");
				if( !isset($line) || $line == -1 ) {
					$line = 1;
				}
			?>
			window.setTimeout( function() {
				editor.resize(true);
				editor.gotoLine( <?php echo $line+1; ?>, <?php echo $col; ?> );
				editor.scrollToLine( <?php echo $line; ?>, true, true);
			}, 10);
			var wgWizForm = document.getElementById("wgWizForm");
			wgWizForm.wgWizSubmitHandler = function() {
				document.getElementById('cursorOffset').value = editor.selection.selectionLead.column;
				document.getElementById('lineOffset').value = editor.selection.selectionLead.row;
				return true;
			}
		}
	</script>
	<?php
	}
	?>
	<div id="basicmenu" class="yuimenubar">
		 <div class="bd">
			  <ul class="first-of-type">
<?php
	if ($svn_enabled && $svn_info['revision']) {

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
                                    <li class="yuimenuitem"><a class="yuimenuitemlabel" href="dialog.svn.templates.commit.php?type=<?php echo rawurlencode($type); ?>&function=<?php echo rawurlencode($function); ?>&language=<?php echo rawurlencode($language);?>" onclick="muze.ariadne.explore.arshow('dialog.svn.templates.commit', this.href); return false;"><?php echo $ARnls["ariadne:svn:commit"]; ?></a></li>
                                    <li class="yuimenuitem"><a class="yuimenuitemlabel" href="dialog.svn.templates.update.php?type=<?php echo rawurlencode($type); ?>&function=<?php echo rawurlencode($function); ?>&language=<?php echo rawurlencode($language);?>" onclick="muze.ariadne.explore.arshow('dialog.svn.templates.update', this.href); return false;"><?php echo $ARnls["ariadne:svn:update"]; ?></a></li>
                                    <li class="yuimenuitem"><a class="yuimenuitemlabel" href="dialog.svn.templates.diff.php?type=<?php echo rawurlencode($type); ?>&function=<?php echo rawurlencode($function); ?>&language=<?php echo rawurlencode($language);?>" onclick="muze.ariadne.explore.arshow('dialog.svn.templates.diff', this.href); return false;"><?php echo $ARnls["ariadne:svn:diff"]; ?></a></li>
                                    <li class="yuimenuitem"><a class="yuimenuitemlabel" href="dialog.svn.templates.serverdiff.php?type=<?php echo rawurlencode($type); ?>&function=<?php echo rawurlencode($function); ?>&language=<?php echo rawurlencode($language);?>" onclick="muze.ariadne.explore.arshow('dialog.svn.templates.serverdiff', this.href); return false;"><?php echo $ARnls["ariadne:svn:serverdiff"]; ?></a></li>
                                    <li class="yuimenuitem"><a class="yuimenuitemlabel" href="dialog.svn.templates.revert.php?type=<?php echo rawurlencode($type); ?>&function=<?php echo rawurlencode($function); ?>&language=<?php echo rawurlencode($language);?>" onclick="muze.ariadne.explore.arshow('dialog.svn.templates.revert', this.href); return false;"><?php echo $ARnls["ariadne:svn:revert"]; ?></a></li>
                                    <li class="yuimenuitem"><a class="yuimenuitemlabel" href="dialog.svn.templates.delete.php?type=<?php echo rawurlencode($type); ?>&function=<?php echo rawurlencode($function); ?>&language=<?php echo rawurlencode($language);?>" onclick="muze.ariadne.explore.arshow('dialog.svn.templates.delete', this.href); return false;"><?php echo $ARnls["ariadne:svn:delete"]; ?></a></li>
							<?php
									if( $svn_status[$filename] ) {
							?>
                                    <li class="yuimenuitem"><a class="yuimenuitemlabel" href="dialog.svn.templates.resolved.php?type=<?php echo rawurlencode($type); ?>&function=<?php echo rawurlencode($function); ?>&language=<?php echo rawurlencode($language);?>" onclick="muze.ariadne.explore.arshow('dialog.svn.templates.resolved', this.href); return false;"><?php echo $ARnls["ariadne:svn:resolved"]; ?></a></li>
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
						 <a class="yuimenubaritemlabel" href="dialog.grantkey.php" onclick="muze.ariadne.explore.arshow('dialog.grantkey', this.href); return false;">
							<?php echo $ARnls['ariadne:grantkey']; ?>
						 </a>
					</li>
<?php
	}
?>
					<li class="yuimenubaritem">
						<a class="yuimenubaritemlabel" href="#"><?php echo $ARnls["ariadne:help"]; ?></a>
						<div id="help" class="yuimenu">
							<div class="bd">
                                <ul class="first-of-type">
									<li class="yuimenuitem">
										 <a class="yuimenuitemlabel" href="http://www.ariadne-cms.org/docs/reference/" onclick="muze.ariadne.explore.arshow('_new', this.href); return false;">
											<?php echo $ARnls['ariadne:programmers_reference']; ?>
										 </a>
									</li>
<?php
	if ($AR->user->data->template_editor == 'ace') {
?>
									<li class="yuimenuitem">
										 <a class="yuimenuitemlabel" href="http://www.ariadne-cms.org/docs/manual/ace/" onclick="muze.ariadne.explore.arshow('_new', this.href); return false;">
											<?php echo $ARnls['ariadne:ace_editor']; ?>
										 </a>
									</li>
<?php
	}
?>
								</ul>
							</div>
						</div>
					</li>
			  </ul>
		 </div>
	</div>
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
		if ($data->config->privatetemplates[$type][$function]) {
			$private=1;
		}
	?>
		<label for="private" class="ontop"><?php echo $ARnls["ariadne:template:private"]; ?></label>
		<input type="hidden" name="private" value="0">
		<input type="checkbox" id="private" name="private" value="1" <?php if ($private) { echo " checked"; } ?>>
	</div>
	<div class="template_option">
	<?php
		if ($data->config->templates[$type][$function][$language] || !$function) {
			$default=1;
		}
	?>
		<label for="default" class="ontop"><?php echo $ARnls["default"]; ?></label>
		<input type="hidden" name="default" value="0">
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
	<div id="editor" style="display: none"></div>
</div>
<?php
	if ( $AR->user->data->template_editor != 'ace' ) {
		echo "<div id=\"template_linenumbers\">\n";
?>
	<textarea name="linenumbers" id="linenumbers" wrap="off" readonly class="linenumbers" tabindex="-1" unselectable="on"><?php
	$linetotal = substr_count($file, "\n");
	$linetotal = $linetotal + 1000;
for($i=1;$i<$linetotal;$i++) { echo $i."\n"; }
	?></textarea>
<?php
		echo "</div>\n";
	}
?>
<?php
	if ( $AR->user->data->template_editor != 'ace' ) {
?>
<script type="text/javascript">

	var currentPos;

	function posHandler() {
		currentPos = muze.util.textarea.getCursorPosition(document.getElementById("template"));
		return true;
	}

	function scrollHandler(event,obj) {
		var scrollpos = obj.scrollTop;
		document.getElementById("linenumbers").scrollTop = scrollpos;
	}

	function saveCurrentPos() {
		if (currentPos && currentPos.offset) {
			document.getElementById('cursorOffset').value = currentPos.offset;
		}
		return true;
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
					var r = document.selection.createRange();
					r.text="\t";
					r.collapse(false);
					r.select();
				} else {
					// unsupported browsers
				}
			}
			result = false;
		} else if (keycode == escapeKeyCode) {
			result = false; // should work in all browsers
		}

		return result ? muze.event.pass(event) : muze.event.cancel(event);
	}

	function initHandlers() {

		var wgWizForm = document.getElementById("wgWizForm");
		wgWizForm.wgWizSubmitHandler = function() {
			var lines = document.getElementById("linenumbers")
			lines.parentNode.removeChild(lines);
			return true;
		}

<?php
		$error = $this->getvar("error");
		if( $error ) {
			echo "alert(\"".AddCSlashes($error, ARESCAPE)."\");\n";
		}
?>
		var area = document.getElementById("template");
		muze.event.attach(area, 'click', posHandler, false);
		muze.event.attach(document.getElementById('wgWizForm'), 'submit', saveCurrentPos, false);
		muze.event.attach(area, 'keydown', function(evt) { posHandler(evt); return keyHandler(evt, area); }, false);
		muze.event.attach(area, 'keyup', function(evt) { return keyHandler(evt, area); }, false);
		muze.event.attach(area, 'keypress', function(evt) { return keyHandler(evt, area); }, false);
		muze.event.attach(area, 'scroll', function(evt) { return scrollHandler(evt, area); }, false);

		area.focus();

<?php
		// set the cursor pos if needed
		$col = 0;
		$pos = $this->getvar("cursorOffset");
		if( !isset($pos) || $pos == '') {
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
	}

	YAHOO.util.Event.onDOMReady(initHandlers);
</script>
<?php
	}
	if ($AR->user->data->template_editor == 'ace') {
?>
<script type="text/javascript">
	muze.event.attach( document.getElementById('wgWizForm'), 'submit', function() {
		document.getElementById('template').value = editor.getSession().getValue();
	} );
</script>
<?php
	}
?>
