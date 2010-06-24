<?php
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		// FIXME: load Ariadne NLS;
		require_once($this->store->get_config("code")."modules/mod_yui.php");
		
		$editor="dialog.templates.edit.php";
		
		$this->call('typetree.ini');
		$icons = $ARCurrent->arTypeIcons;
		$names = $ARCurrent->arTypeNames;

		$svn_enabled = $AR->SVN->enabled;
		$ARnls['svn'] = "SVN";

		if ($svn_enabled) {
			$filestore = $this->store->get_filestore_svn("templates");
			$svn = $filestore->connect($this->id);
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
		if ($svn_info['Revision']) {
?>
                                                                    <li class="yuimenuitem"><a class="yuimenuitemlabel" href="dialog.svn.tree.info.php" onclick="muze.ariadne.explore.arshow('edit_object_data', this.href); return false;"><?php echo $ARnls["ariadne:svn:info"]; ?></a></li>
                                                                    <li class="yuimenuitem"><a class="yuimenuitemlabel" href="dialog.svn.templates.diff.php" onclick="muze.ariadne.explore.arshow('edit_object_data', this.href); return false;"><?php echo $ARnls["ariadne:svn:diff"]; ?></a></li>
                                                                    <li class="yuimenuitem"><a class="yuimenuitemlabel" href="dialog.svn.templates.commit.php" onclick="muze.ariadne.explore.arshow('edit_object_data', this.href); return false;"><?php echo $ARnls["ariadne:svn:commit"]; ?></a></li>
                                                                    <li class="yuimenuitem"><a class="yuimenuitemlabel" href="dialog.svn.templates.revert.php" onclick="muze.ariadne.explore.arshow('edit_object_data', this.href); return false;"><?php echo $ARnls["ariadne:svn:revert"]; ?></a></li>
                                                                    <li class="yuimenuitem"><a class="yuimenuitemlabel" href="dialog.svn.templates.update.php" onclick="muze.ariadne.explore.arshow('edit_object_data', this.href); return false;"><?php echo $ARnls["ariadne:svn:update"]; ?></a></li>
                                                                    <li class="yuimenuitem"><a class="yuimenuitemlabel" href="dialog.svn.templates.unsvn.php" onclick="muze.ariadne.explore.arshow('edit_object_data', this.href); return false;"><?php echo $ARnls["ariadne:svn:unsvn"]; ?></a></li>
<?php
		} else {
?>
                                                                    <li class="yuimenuitem"><a class="yuimenuitemlabel" href="dialog.svn.templates.checkout.php" onclick="muze.ariadne.explore.arshow('edit_object_data', this.href); return false;"><?php echo $ARnls["ariadne:svn:checkout"]; ?></a></li>
                                                                    <li class="yuimenuitem"><a class="yuimenuitemlabel" href="dialog.svn.templates.import.php" onclick="muze.ariadne.explore.arshow('edit_object_data', this.href); return false;"><?php echo $ARnls["ariadne:svn:import"]; ?></a></li>
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
		<input type="submit" id="searchbutton" class="wgWizControl" name="wgWizControl" onclick="document.wgWizForm.wgWizAction.value='grep'"; value="<?php echo $ARnls["search"]; ?>">
	</div>
<?php
		$grepresults = false;
		if( $search != "" ) {
			$templates_path = $filestore->make_path($this->id);
			$esc_search = escapeshellarg($search);
			$greps = Array();

			$result = exec($AR->Grep->path." ".$AR->Grep->options." $esc_search $templates_path*.pinp", $greps);
			$grepresults = array();
			foreach ($greps as $grep) {
				list($file, $linenr, $line)=split(":", $grep, 3);
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

			if ($svn_enabled && $svn_status ) {
				foreach ($svn_status as $filename=>$file_status) {
					if ($file_status == "!") {
						// Template is deleted here, but not in the SVN.
						$file_meta = array();
						$file_meta['ar:default'] = $filestore->svn_propget($svn, "ar:default", $filename);
						$file_meta['ar:type'] = $filestore->svn_propget($svn, "ar:type", $filename);
						$file_meta['ar:function'] = $filestore->svn_propget($svn, "ar:function", $filename);
						$file_meta['ar:language'] = $filestore->svn_propget($svn, "ar:language", $filename);

						$pinp[$file_meta['ar:type']][$file_meta['ar:function']][$file_meta['ar:language']] = $this->id;
						$templates[$file_meta['ar:type']][$file_meta['ar:function']] = $file_meta['ar:default'];
					}
				}
			}

			if (($pinp) && is_array($pinp)) {
				foreach( $pinp as $type => $values ) {
					uksort($values, array('yui', 'layout_sortfunc'));
					foreach( $values as $function => $templatelist ) {
						ksort($templatelist);
						reset($templatelist);
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
							}

							$flag = "<img src=\"".$AR->dir->images."nls/small/$language.gif\" alt=\"".$AR->nls->list[$language]."\">";
							if ($svn_enabled) {
								if (sizeof($templatelist) > 1) {
									if ($svn_img) {
										$svn_img_src = $AR->dir->images . "/svn/$svn_img";
										$flag_svn = '<img class="flag_svn_icon" alt="' . $svn_alt . '" src="' . $svn_img_src . '">';
									}
								}
							}
									
							$flagbuttons .= "<a class=\"button\" href=\"".$this->store->get_config("root").$this->path.$editor."?type=".$type."&amp;function=".RawUrlEncode($function).
								"&amp;language=".$language."\">" . $flag . $flag_svn . "</a> ";

							if( is_array( $grepresults) && is_array($grepresults[$filename_short]) ) {
								foreach( $grepresults[$filename_short] as $r ) {
									list( $ln, $tx ) = split(":", $r, 2);
									if (sizeof($templatelist) > 1) {
										$grep_results .= $flag . "&nbsp;";
									}
									$grep_results .= "<a href=\"".$this->store->get_config("root").$this->path.$editor."?type=".$type."&amp;function=".RawUrlEncode($function).
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
						       <td align="left" valign="middle" style="height:23px; <?php echo $svn_style;?>">
								
								<img class="type_icon" alt="<?php echo $icon_alt; ?>" src="<?php echo $icon_src; ?>">

								<?php echo $type; ?>&nbsp;</td>
							<td style="<?php echo $svn_style;?>" align="left"><div style="display:none;"><?php echo $function; ?></div><?php  
								if (!$templates[$type][$function]) {
									echo "<img class='local' src='{$AR->dir->images}local.gif' alt='local'>";	
								}
							?>
							<?php echo $function; ?></td>
							<td style="<?php echo $svn_style;?>"><?php
								echo $flagbuttons;
							?></td>
								<?php
									$tbasename = "$type.$function";
									reset($templatelist);
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
							<td style="<?php echo $svn_style_hide;?>">
								<div style="display:none;"><?php printf("%010d", $msize); ?></div><?php echo $this->make_filesize($msize); ?>&nbsp;
							</td>
							<td style="<?php echo $svn_style_hide;?>">
								<div style="display:none;"><?php print $mtime; ?></div>
								<?php echo strftime("%H:%M", $mtime); ?>&nbsp;&nbsp;
								<?php echo strftime("%d %b %Y", $mtime); ?>&nbsp;
							</td>
							<td style="<?php echo $svn_style_hide;?>">
								<?php echo $grep_results; ?>
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
