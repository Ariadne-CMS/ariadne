<?php
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		// FIXME: load Ariadne NLS;
		require_once($this->store->get_config("code")."modules/mod_yui.php");

		$editor="dialog.templates.edit.php";

		if( !$ARCurrent->arTypeTree ) {
			$this->call('typetree.ini');
		}
		$icons = $ARCurrent->arTypeIcons;
		$names = $ARCurrent->arTypeNames;

		$svn_enabled = $AR->SVN->enabled;
		$ARnls['svn'] = "SVN";

		if ($svn_enabled) {
			$filestore = $this->store->get_filestore_svn("templates");
			$svn = $filestore->connect($this->id);
			// FIXME eror checking
			$svn_info = $filestore->svn_info($svn);

			$svn_status = $filestore->svn_status($svn);
		} else {
			$filestore = $this->store->get_filestore("templates");
		}

		$wwwroot = $AR->dir->www;
		$yui_base = $wwwroot . "js/yui/";

		$search = $this->getdata("search");

	if ($svn_enabled) {
?>
<script type="text/javascript">
	muze.ariadne.svn = function() {};
</script>
<style type="text/css">
	.svn {
		display: none;
	}
</style>
<?php
	}
?>
<script type="text/javascript">
	function objectadded() {
		if (window.opener && window.opener.objectadded) {
			window.opener.objectadded();
		}
		window.location.href = window.location.href;
	}

	function templatestableinit() {

		var myColumnDefs = [
			{key:"type", label:"<?php echo $ARnls['type'];?>", sortable:true},
			{key:"template", label:"<?php echo $ARnls['template'];?>", sortable:true},
			{key:"language", label:"<?php echo $ARnls['language'];?>", sortable:true},
			{key:"size", label:"<?php echo $ARnls['size'];?>", sortable:true},
			{key:"modified", label:"<?php echo $ARnls['modified'];?>", sortable:true}<?php if( $search != "" ) { echo ",\n";
			echo "{key:\"search\", label:\"".$ARnls['search']."\", sortable:true}\n";
			} ?>
		];

		if (muze.ariadne.svn) {
			myColumnDefs = [
				{key:"svn", label:"<?php echo $ARnls['svn'];?>", sortable:true},
				{key:"type", label:"<?php echo $ARnls['type'];?>", sortable:true},
				{key:"template", label:"<?php echo $ARnls['template'];?>", sortable:true},
				{key:"language", label:"<?php echo $ARnls['language'];?>", sortable:true},
				{key:"size", label:"<?php echo $ARnls['size'];?>", sortable:true},
				{key:"modified", label:"<?php echo $ARnls['modified'];?>", sortable:true}<?php if( $search != "" ) { echo ",\n";
				echo "{key:\"search\", label:\"".$ARnls['search']."\", sortable:true}\n";
			} ?>
			];
		}
		muze.ariadne.templates.init(myColumnDefs);

	}
	YAHOO.util.Event.onDOMReady(templatestableinit);
</script>
	<div id="basicmenu" class="yuimenubar">
		 <div class="bd">
			  <ul class="first-of-type">
					<li class="yuimenubaritem">
						 <a class="yuimenubaritemlabel" href="javascript:muze.ariadne.templates.control.newTemplate();">
							  <?php echo $ARnls['new']; ?>
						 </a>
					</li>
<?php
	if ($svn_enabled) {
?>
					<li class="yuimenubaritem">
						<a class="yuimenubaritemlabel" href="#">SVN</a>
						<div id="svn" class="yuimenu">
							<div class="bd">
                                                                <ul class="first-of-type">
<?php
		if (count($svn_info)) {
?>
                                                                    <li class="yuimenuitem"><a class="yuimenuitemlabel" href="dialog.svn.tree.info.php" onclick="muze.ariadne.explore.arshow('dialog.svn.tree.info', this.href); return false;"><?php echo $ARnls["ariadne:svn:info"]; ?></a></li>
                                                                    <li class="yuimenuitem"><a class="yuimenuitemlabel" href="dialog.svn.templates.diff.php" onclick="muze.ariadne.explore.arshow('dialog.svn.templates.diff', this.href); return false;"><?php echo $ARnls["ariadne:svn:diff"]; ?></a></li>
                                                                    <li class="yuimenuitem"><a class="yuimenuitemlabel" href="dialog.svn.templates.serverdiff.php" onclick="muze.ariadne.explore.arshow('dialog.svn.templates.serverdiff', this.href); return false;"><?php echo $ARnls["ariadne:svn:serverdiff"]; ?></a></li>
                                                                    <li class="yuimenuitem"><a class="yuimenuitemlabel" href="dialog.svn.templates.commit.php" onclick="muze.ariadne.explore.arshow('dialog.svn.templates.commit', this.href); return false;"><?php echo $ARnls["ariadne:svn:commit"]; ?></a></li>
                                                                    <li class="yuimenuitem"><a class="yuimenuitemlabel" href="dialog.svn.templates.revert.php" onclick="muze.ariadne.explore.arshow('dialog.svn.templates.revert', this.href); return false;"><?php echo $ARnls["ariadne:svn:revert"]; ?></a></li>
                                                                    <li class="yuimenuitem"><a class="yuimenuitemlabel" href="dialog.svn.templates.update.php" onclick="muze.ariadne.explore.arshow('dialog.svn.templates.update', this.href); return false;"><?php echo $ARnls["ariadne:svn:update"]; ?></a></li>
                                                                    <li class="yuimenuitem"><a class="yuimenuitemlabel" href="dialog.svn.templates.unsvn.php" onclick="muze.ariadne.explore.arshow('dialog.svn.templates.unsvn', this.href); return false;"><?php echo $ARnls["ariadne:svn:unsvn"]; ?></a></li>
<?php
		} else {
?>
                                                                    <li class="yuimenuitem"><a class="yuimenuitemlabel" href="dialog.svn.templates.checkout.php" onclick="muze.ariadne.explore.arshow('dialog.svn.templates.checkout', this.href); return false;"><?php echo $ARnls["ariadne:svn:checkout"]; ?></a></li>
                                                                    <li class="yuimenuitem"><a class="yuimenuitemlabel" href="dialog.svn.templates.import.php" onclick="muze.ariadne.explore.arshow('dialog.svn.templates.import', this.href); return false;"><?php echo $ARnls["ariadne:svn:import"]; ?></a></li>
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
						 <a class="yuimenubaritemlabel" href="http://www.ariadne-cms.org/docs/" onclick="muze.ariadne.explore.arshow('_new', this.href); return false;">
							<?php echo $ARnls['help']; ?>
						 </a>
					</li>
			  </ul>
		 </div>
	</div>
	<input type="hidden" name="type" value="<?php echo $this->type; ?>">
<?php
	if ($AR->Grep->path) {
?>
	<div class="searchdiv">
		<input class="text" type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>">
		<input type="submit" id="searchbutton" class="wgWizControl" name="wgWizControl" onclick="document.wgWizForm.wgWizAction.value='grep'" value="<?php echo $ARnls["search"]; ?>">
	</div>
<?php
		$grepresults = false;
		if( $search != "" ) {
			$templates_path = $filestore->make_path($this->id);
			$esc_search = escapeshellarg($search);
			$greps = array();

			$result = exec($AR->Grep->path." ".$AR->Grep->options." $esc_search $templates_path*.pinp", $greps);
			$grepresults = array();
			foreach ($greps as $grep) {
				list($file, $linenr, $line)=explode(":", $grep, 3);
				$file=substr($file, strrpos($file, '/'));
				$file=substr($file, 2);
				$file = substr($file, 0, strrpos($file, '.'));
				if( !is_array($grepresults[$file])) {
					$grepresults[$file] = array();
				}
				$grepresults[$file][] = $linenr.": ".$line;
			}
		}


	}
?>
	<div id='templatesDiv' class='topdiv'>
		<table id='templatesTable' border="0" cellspacing="0" cellpadding="0">
		<thead style="cursor:hand;">
		<tr>
			<td valign="top" class="required">
				<?php echo $ARnls["type"]; ?>
			</td><td valign="top" class="required">
				<?php echo $ARnls["template"]; ?>
			</td><td valign="top" class="required">
				<?php echo $ARnls["language"]; ?>
			</td><td valign="top" class="required">
				<?php echo $ARnls["size"]; ?>
			</td><td valign="top" class="required">
				<?php echo $ARnls["modified"]; ?>
			</td>
		</tr></thead><tbody>
		<?php
			$pinp = $data->config->pinp;
			$templates = $data->config->templates;
			$privatetemplates = $data->config->privatetemplates;

			if ($svn_enabled && $svn_status ) {
				$deleted_templates = $data->config->deleted_templates;
				$deleted_privatetemplates = $data->config->deleted_privatetemplates;

				foreach ($svn_status as $filename=>$file_status) {
					if ($file_status['wc-status']['item'] == "missing") {
						// Template is deleted here, but not in the SVN.
						$file_meta = array();
						$file_meta['ar:default'] = $filestore->svn_propget($svn, "ar:default", $filename);
						$file_meta['ar:type'] = $filestore->svn_propget($svn, "ar:type", $filename);
						$file_meta['ar:function'] = $filestore->svn_propget($svn, "ar:function", $filename);
						$file_meta['ar:language'] = $filestore->svn_propget($svn, "ar:language", $filename);
						$file_meta['ar:private'] = $filestore->svn_propget($svn, "ar:private", $filename);

						$pinp[$file_meta['ar:type']][$file_meta['ar:function']][$file_meta['ar:language']] = $this->id;
						$templates[$file_meta['ar:type']][$file_meta['ar:function']] = $file_meta['ar:default'];
						$privatetemplates[$file_meta['ar:type']][$file_meta['ar:function']] = $file_meta['ar:private'];

					} else if ($file_status['wc-status']['item'] == "deleted") {
						foreach ($deleted_templates as $type => $functions) {
							foreach ($functions as $function => $languages) {
								foreach ($languages as $language => $default) {
									if ($filename == "$type.$function.$language.pinp") {

										$file_meta = array();
										$file_meta['ar:default'] = $default;
										$file_meta['ar:type'] = $type;
										$file_meta['ar:function'] = $function;
										$file_meta['ar:language'] = $language;
										$file_meta['ar:private'] = $deleted_privatetemplates[$type][$function] ? 1 : 0;

										$privatetemplates[$type][$function] = $file_meta['ar:private'];

										$pinp[$file_meta['ar:type']][$file_meta['ar:function']][$file_meta['ar:language']] = $this->id;
										$templates[$file_meta['ar:type']][$file_meta['ar:function']] = $file_meta['ar:default'];
									}
								}
							}
						}
					}
				}
			}
			if (isset($pinp) && count($pinp)) {
				foreach( $pinp as $type => $values ) {
					uksort($values, array('yui', 'layout_sortfunc'));
					foreach( $values as $function => $templatelist ) {
						ksort($templatelist);
						$flagbuttons = '';
						$flag_svn = '';
						$grep_results = '';
						foreach ($templatelist as $language => $template) {
							$filename = $type . "." . $function . "." . $language . ".pinp";
							$filename_short = $type . "." . $function . "." . $language;
							if ($svn_enabled && $svn_status ) {
								$svn_style = "";
								$svn_style_hide = "";
								$svn_img = "";
								$itemstatus = $svn_status[$filename]['wc-status']['item'];
								switch($itemstatus) {
									// Fixme: find out the codes for "locked", "read only" and add them.

									case "conflicted":
										$svn_img = "ConflictIcon.png";
										$svn_alt = $ARnls['ariadne:svn:conflict'];
										break;
									case "modified":
										$svn_img = "ModifiedIcon.png";
										$svn_alt = $ARnls['ariadne:svn:modified'];
										break;
									case "unversioned":
										break;
									case "added":
										$svn_img = "AddedIcon.png";
										$svn_alt = $ARnls['ariadne:svn:added'];
										break;
									case "deleted":
										$svn_img = "DeletedIcon.png";
										$svn_alt = $ARnls['ariadne:svn:deleted'];
										if ($this->data->config->deleted_templates[$type][$function][$language]) {
											$svn_style = "blurred";
											$svn_style_hide = "hidden";
										}
										break;
									case "missing":
										$svn_style = "blurred";
										$svn_style_hide = "hidden";
										break;
									case 'normal':
										$svn_img = "InSubVersionIcon.png";
										$svn_alt = $ARnls['ariadne:svn:insubversion'];
										break;
									default:
										// No status, this is an error
										break;
								}
							}

							$flag = "<img src=\"".$AR->dir->images."nls/small/$language.gif\" alt=\"".$AR->nls->list[$language]."\">";
							if ($svn_enabled && (count($templatelist) > 1) && $svn_img ) {
								$svn_img_src = $AR->dir->images . "/svn/$svn_img";
								$flag_svn = '<img class="flag_svn_icon" alt="' . $svn_alt . '" src="' . $svn_img_src . '">';
							}

							$flagbuttons .= "<a class=\"button\" href=\"".$this->make_ariadne_url().$editor."?type=".$type."&amp;function=".RawUrlEncode($function).
								"&amp;language=".$language;
							if ($search) {
								$flagbuttons .= "&amp;search=".RawUrlEncode($search);
							}
							$flagbuttons .= "\">" . $flag . $flag_svn . "</a> ";

							if( is_array( $grepresults) && is_array($grepresults[$filename_short]) ) {
								foreach( $grepresults[$filename_short] as $r ) {
									list( $ln, $tx ) = explode(":", $r, 2);
									if (count($templatelist) > 1) {
										$grep_results .= $flag . "&nbsp;";
									}
									$grep_results .= "<a href=\"".$this->make_ariadne_url().$editor."?type=".$type."&amp;function=".RawUrlEncode($function).
									"&amp;lineOffset=".rawurlencode($ln)."&amp;language=".rawurlencode($language)."\">".htmlspecialchars($r)."</a><br>";
								}
							}
						}

						$icon_src = $ARCurrent->arTypeIcons[$type]["small"] ? $ARCurrent->arTypeIcons[$type]["small"] : $this->call("system.get.icon.php", array("type" => $type, "size" => "small"));
						$icon_alt = $type;
						?><tr valign="middle">
							<td class="svn">
								<?php
									if ($svn_enabled) {
										if ($svn_img) {
											$svn_img_src = $AR->dir->images . "/svn/$svn_img";
											?><img class="svn_icon" alt="<?php echo $svn_alt; ?>" src="<?php echo $svn_img_src;?>"><?php
										}
									}
								?>
							</td>
						       <td align="left" valign="middle" style="height:23px;">
								<div class="<?php echo $svn_style; ?>">
									<img class="type_icon" alt="<?php echo $icon_alt; ?>" src="<?php echo $icon_src; ?>">
								<?php echo $type; ?>&nbsp;</div></td>
							<td align="left"><div style="display:none;"><?php echo $function; ?></div><div class="<?php echo $svn_style; ?>"><?php
								if (!$templates[$type][$function]) {
									echo "<img class='local' src='{$AR->dir->images}local.gif' alt='local'>&nbsp;";
								}
								if ($privatetemplates[$type][$function]) {
									echo "<img class='private' src='{$AR->dir->images}private.png' alt='" . $ARnls['ariadne:template:private'] . "' title='" . $ARnls['ariadne:template:private'] . "'>";
								}
							?>
							<?php echo $function; ?></div>
							</td>
							<td><div class="<?php echo $svn_style; ?>"><?php
								echo $flagbuttons;
							?></div></td>
								<?php
									$tbasename = "$type.$function";
									$mtime = 0;
									$msize = 0;
									foreach( $templatelist as $language => $template ) {
										$time = $filestore->mtime($this->id, "$tbasename.$language.pinp");
										if ($time > $mtime) {
											$mtime = $time;
										}
										$size = $filestore->size($this->id, "$tbasename.$language.pinp");
										if( $size > $msize) {
											$msize = $size;
										}

									}
								?>
							<td>
								<div style="display:none;"><?php printf("%010d", $msize); ?></div>
								<div class="<?php echo $svn_style_hide;?>">
									<?php echo $this->make_filesize($msize); ?>&nbsp;
								</div>
							</td>
							<td>
								<div style="display:none;"><?php print $mtime; ?></div>
								<div class="<?php echo $svn_style_hide;?>">
									<?php echo strftime("%H:%M", $mtime); ?>&nbsp;&nbsp;
									<?php echo strftime("%d %b %Y", $mtime); ?>&nbsp;
								</div>
							</td>
							<td>
								<div class="<?php echo $svn_style_hide;?>">
									<?php echo $grep_results; ?>
								</div>
							</td>
						</tr><?php
					}
				}
			}
		?></tbody>
		</table>
	</div>
<?php
	}
?>
