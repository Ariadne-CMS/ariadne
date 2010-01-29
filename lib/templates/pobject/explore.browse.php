<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("read") && $this->CheckConfig()) {
		include_once($this->store->get_config("code")."nls/ariadne.".$this->reqnls);
		$this->call("typetree.ini");

		if( !function_exists("showTable") ) {
			function showTable($divId, $tableId, $columnDefs, $data) {
				echo '<div id="' . $divId . '" class="yui-dt">';

				if (is_array($columnDefs)) {
					// First, output the table header
					echo '<table id="' . $tableId. '">' . "\n";
					echo '<thead><tr class="yui-dt-first yui-dt-last">' . "\n";

					$colnum = sizeof($columnDefs);
				
					for ($num = 0; $num < $colnum; $num++) {
						$extraclass = '';
						if ($num == 0) {
							$extraclass .= " yui-dt-first";
						}
						if ($num == ($colnum-1)) {
							$extraclass .= " yui-dt-last";
						}
						echo '<th class="yui-dt-col-' . $columnDefs[$num]['key'] . $extraclass . ' yui-dt-sortable">';
						echo '<div class="yui-dt-header">';
						echo '<span class="yui-dt-label">';
						// echo '<a class="yui-dt-sortable" href="?key=' . $columnDefs[$num]['key'] . '">';
						echo '<a class="yui-dt-sortable">';
						echo $columnDefs[$num]['label'];
						echo '</a>';
						echo '</span>';
						echo '</div>';
						echo '</th>';
					}

					echo "	</tr></thead>\n";
				
					// Now the table data;
					if (is_array($data)) {
						echo '<tbody class="yui-dt-body">';
						$oddeven = 'even';

						$rownums = sizeof($data);
						for ($rownum = 0; $rownum < $rownums; $rownum++) {
							if ($rownum == 0) {
								$row_extraclass .= ' yui-dt-first';
							}
							if ($rownum == $rownums-1) {
								$row_extraclass .= ' yui-dt-last';
							}

							// FIXME: Er moet iets bedacht worden om de path informatie door te spelen naar javascript - deze methode werkt wel, maar wordt overschreven door YUI;
							echo '<tr path="' . $data[$rownum]['path'] . '" class="explore_item yui-dt-' . $oddeven . $row_extraclass . '">';
	//						echo '<tr class="explore_item yui-dt-' . $oddeven . $row_extraclass . '">';
							for ($num = 0; $num < $colnum; $num++) {
								$extraclass = '';
								if ($num == 0) {
									$extraclass .= " yui-dt-first";
								}
								if ($num == ($colnum-1)) {
									$extraclass .= " yui-dt-last";
								}
								echo '<td class="yui-dt-col-' . $columnDefs[$num]['key'] . $extraclass .'">';
								echo $data[$rownum][$columnDefs[$num]['key']];
								echo "</td>\n";
							}

							echo "</tr>\n";

							if ($oddeven == 'even') {
								$oddeven = 'odd';
							} else {
								$oddeven = 'even';
							}
						}
					}
				
					// Close her up, we're done!
					echo "</tbody></table>";
				}
				echo "</div>";
			}
		}
		
		if( !function_exists("showTableJs") ) {
			function showTableJs($divId, $tableId, $fields, $columnDefs) {
				$jsColDefs = '';
				foreach ($columnDefs as $colDef) {
					$jsColDefs .= '{key:"' . $colDef['key'] . '"';
					if ($colDef['label']) {
						$jsColDefs .= ',label:"' . $colDef['label'] . '"';
					}
					if ($colDef['sortable']) {
						$jsColDefs .= ',sortable:true';
						}
					$jsColDefs .= '},';
				}

				// Strip last comma;
				$jsColDefs = substr($jsColDefs, 0, strlen($jsColDefs)-1);

				$jsFields = '';
				$jsFields = '';
				foreach ($fields as $key) {
					$jsFields .= '{key:"' . $key . '"},';
				}
				$jsFields = substr($jsFields, 0, strlen($jsFields)-1);

				// Start output - this should be changed around to be better readable!
				echo '		muze.ariadne.explore.viewpane.myColumnDefs = [' . "\n";
				echo $jsColDefs;
				echo '	        ];' . "\n";

	/*			echo '	        muze.ariadne.explore.viewpane.myDataSource = new YAHOO.util.DataSource(YAHOO.util.Dom.get("' . $tableId . '"));' . "\n";
				echo '	        muze.ariadne.explore.viewpane.myDataSource.responseType = YAHOO.util.DataSource.TYPE_HTMLTABLE;' . "\n";
				echo '	        muze.ariadne.explore.viewpane.myDataSource.responseSchema = {' . "\n";
				echo ' 	            fields: [' . "\n";
				echo $jsFields;
				echo '	            ]' . "\n";
				echo '	        };' . "\n";

				echo '	        muze.ariadne.explore.viewpane.dataTable = new YAHOO.widget.DataTable("' . $divId . '", muze.ariadne.explore.viewpane.myColumnDefs, muze.ariadne.explore.viewpane.myDataSource,{});' . "\n";
				echo '		muze.ariadne.explore.viewpane.dataTable.subscribe("rowClickEvent", muze.ariadne.explore.viewpane.rowClick);' . "\n";
				echo '		muze.ariadne.explore.viewpane.dataTable.subscribe("rowDblclickEvent", muze.ariadne.explore.viewpane.rowDoubleClick);' . "\n";
	*/

				echo '		muze.ariadne.explore.viewpane.load_handler = function() {' . "\n";
				echo '		        muze.ariadne.explore.viewpane.myDataSource = new YAHOO.util.DataSource(YAHOO.util.Dom.get("' . $tableId . '"));' . "\n";
				echo '		        muze.ariadne.explore.viewpane.myDataSource.responseType = YAHOO.util.DataSource.TYPE_HTMLTABLE;' . "\n";
				echo '		        muze.ariadne.explore.viewpane.myDataSource.responseSchema = {' . "\n";
				echo ' 		            fields: [' . "\n";
				echo $jsFields;
				echo '		            ]' . "\n";
				echo '		        };' . "\n";
				echo '	        	muze.ariadne.explore.viewpane.dataTable = new YAHOO.widget.DataTable("' . $divId . '", muze.ariadne.explore.viewpane.myColumnDefs, muze.ariadne.explore.viewpane.myDataSource,{});' . "\n";
				echo '			muze.ariadne.explore.viewpane.dataTable.subscribe("rowClickEvent", muze.ariadne.explore.viewpane.rowClick);' . "\n";
				echo '			muze.ariadne.explore.viewpane.dataTable.subscribe("rowDblclickEvent", muze.ariadne.explore.viewpane.rowDoubleClick);' . "\n";
				echo '			muze.ariadne.explore.viewpane.dataTable.subscribe("rowMouseoverEvent", muze.ariadne.explore.viewpane.onEventHighlightRow);' . "\n";
				echo '			muze.ariadne.explore.viewpane.dataTable.subscribe("rowMouseoutEvent", muze.ariadne.explore.viewpane.onEventUnhighlightRow);' . "\n";
				echo '		}' . "\n";

			}
		}
		
		if( !function_exists("colDefs") ) {
			function colDefs($fields) {
				global $ARnls;
			
				// Create a default set of column definitions given a set of field keys.
				// This uses the keyname for the index, and ARnls key as the label.
				// All columns are defined as sortable;
				if (is_array($fields)) {
					$columnDefs = array();
					foreach ($fields as $key) {
						if (!$ARnls[$key]) {
							$ARnls[$key] = $key;
						}

						$colDef = array(
							'key' 		=> $key,
							'label' 	=> $ARnls[$key],
							'sortable' 	=> true
						);
					
						array_push($columnDefs, $colDef);
					}
				}
				return $columnDefs;
			}
		}
		
		if( !function_exists("getSvnIcon") ) {
			function getSvnIcon($status) {
				global $AR;
				if ($status == 'insubversion') {
					$iconsrc =  $AR->dir->images . 'svn/InSubVersionIcon.png';
				}
				if ($status == 'modified') {
					$iconsrc = $AR->dir->images . 'svn/ModifiedIcon.png';
				}

				if ($iconsrc) {
					return '<img class="explore_svnicon" src="' . $iconsrc . '">';
				} else {
					return "&nbsp;";
				}
			}
		}
		
		if( !function_exists("getTypeIcon") ) {
			function getTypeIcon($item, $viewtype) {
				$type = $item["type"];
				$vtype = $item["vtype"];
				
				$iconsize = 'small';
				if ($viewtype == 'icons') {
					$iconsize = 'large';
				}
				if ($viewtype == 'list') {
					$iconsize = 'medium';
				}

				$iconsrc = "";
				if( $type == "pshortcut" ) {
					$iconsrc = '<img title="' . $item['vtype'] . '" alt="' . $item['vtype'] . '" class="explore_icon" src="' . $item['icons'][$iconsize] . '">';
					$iconsrc .= '<img title="' . $item['type'] . '" alt="' . $item['type'] . '" class="explore_icon_shortcut_' . $viewtype . '" src="' . $item['overlay_icons'][$iconsize] . '">';
				} else {
					$iconsrc = '<img title="' . $item['type'] . '" alt="' . $item['type'] . '" class="explore_icon" src="' . $item['icons'][$iconsize] . '">';
				}
				if( $iconsrc == "" ) {
					$iconsrc = "&nbsp;";
				}
				return $iconsrc;
			}
		}
		
		if( !function_exists("showList") ) {
			function showList($objects, $viewtype='list') {
				global $AR, $ARnls;
				$result = '';
				if (is_array($objects) && sizeof($objects) > 0) {
					$result .= '<ul class="explore_list ' . $viewtype . '">';
					foreach ($objects as $node) {
						$item = array(
							// FIXME: should we use $this->store->root?
							'href' => $AR->user->store->root . $node['path'] . 'explore.html', 
							'ondoubleclick' => "top.muze.ariadne.explore.view('" . htmlspecialchars($node['path']) . "'); return false;",
							// 'onclick' => "muze.ariadne.explore.viewpane.selectItem(this); return false;",
						);

						$name = $node['name']; 

						switch ($viewtype) {
							case "details" : 
								$maxlen = 32;
							break;
							case "icons" :
								$maxlen = 7;
							break;
							default :
								$maxlen = 11;
							break;
						}

						if ($maxlen) {
							if (mb_strlen($name, 'utf-8') > $maxlen) {
								$name = mb_substr($name, 0, $maxlen, 'utf-8') . '...';
							}
						}

	//					$item['label'] = $node['pre'] . $name;
						$item['label'] = $name;

						if (is_array($node['svn'])) {
							$item['svnicon'] = getSvnIcon($node['svn']['status']);
						}

						$item['icon'] = getTypeIcon($node, $viewtype);
						

	//					$result .= '<li class="explore_item" path="' . $node['path'] . '">';
						$result .= '<li class="explore_item">';

						$result .= '<a href="' . $item['href'] . '"';
						// $result .= ' onclick="' . $item['onclick'] . '"';
						$result .= ' onDblClick="' . $item['ondoubleclick'] . '"';
						// $result .= ' path="' . $node['path'] . '"';
						$result .= ' title="' . htmlspecialchars($node['name']) . '"';
						$result .= '>';

						$result .= $item['icon'];

						if ($item['svnicon']) {
							$result .= $item['svnicon'];
						}

						if ($viewtype == 'icons') {
							$result .= '<br>';
						}

						$result .= htmlspecialchars($item['label']);
						$result .= '</a>';
					}
					$result .= "</ul>";
				} else {
					$result = "<div class='noobjects'>" . $ARnls['ariadne:no_objects_found'] . "</div>";
				}
				return $result;
			}
		}
		
		if( !function_exists("showPaging") ) {
			function showPaging($total, $items_per_page, $current_page, $pagingclass="") {
				global $AR;
				if ($total > $items_per_page) {
					$total_pages = (int) ($total/$items_per_page);
					if ($total % $items_per_page > 0) {
						$total_pages++;
					}

					$pagingentries = array();
					if ($current_page > 1) {
						$pagingentries[] = array(
							"class" => "prev",
							"image" => $AR->dir->www . "images/icons/small/prev.png",
							"label" => $ARnls['ariadne:prev'],
							"onclick" => "muze.ariadne.explore.viewpane.view(muze.ariadne.explore.viewpane.path, " . ($current_page - 1) .  "); return false; "
						);
					}

					$entry = array(
						"class" => "first",
						"label" => 1,
						"onclick" => "muze.ariadne.explore.viewpane.view(muze.ariadne.explore.viewpane.path, 1); return false;"
					);
					if (1 == $current_page) {
						$entry["class"] .= " current";
					}
					$pagingentries[] = $entry;

					if ($current_page >= 5) {
						$start = $current_page - 2;
						$end = $current_page + 2;
					} else  {
						$start = 2;
						$end = 5;
					}

					if ($end > $total_pages - 2) {
						$start = $total_pages - 4;
						$end = $total_pages -1;
					}

					if ($start < 2) {
						$start = 2;
					}
					if (($end > $total_pages - 2) && $total_pages > 5) {
						$end = $total_pages - 1;
					}
					if ($start > 2) {
						$pagingentries[] = array(
							"label" => '...',
							"class" => "ellipsis"
						);			
					}

					for ($i=$start; $i <= $end; $i++) {
						if ($total_pages >= $i) {
							$entry = array(
								"label" => $i,
								"onclick" => "muze.ariadne.explore.viewpane.view(muze.ariadne.explore.viewpane.path, " . $i . "); return false;"
							);
							if ($i == $current_page) {
								$entry["class"] = "current";
							}
							$pagingentries[] = $entry;
						}
					}

					if (($total_pages > 5) && ($total_pages > ($end + 1))) {
						$pagingentries[] = array(
							"label" => '...',
							"class" => "ellipsis"
						);
					}

					$entry = array(
						"class" => "last",
						"label" => $total_pages,
						"onclick" => "muze.ariadne.explore.viewpane.view(muze.ariadne.explore.viewpane.path, " . $total_pages .  "); return false;"
					);
					if ($total_pages == $current_page) {
						$entry["class"] .= " current";
					}
					$pagingentries[] = $entry;

					if ($current_page < $total_pages) {
						$pagingentries[] = array(
							"class" => "next",
							"image" => $AR->dir->www . "images/icons/small/next.png",
							"label" => $ARnls['ariadne:next'],
							"onclick" => "muze.ariadne.explore.viewpane.view(muze.ariadne.explore.viewpane.path, " . ($current_page + 1) .  "); return false;"
						);
					}
					$result = "<ul class='paging";
					if ($pagingclass) {
						$result .= " $pagingclass";
					}
					$result .= "'>";
					foreach ($pagingentries as $entry) {
						$result .= "<li";
						if ($entry['class']) {
							$result .= ' class="' . $entry['class'] . '"';
						}
						$result .= ">";
						$result .= "<a";

						if ($entry['onclick']) {
							$result .= ' onclick="' . $entry['onclick'] . '"';
						}
						if ($entry['href']) {
							$result .= ' href="' . $entry['href'] . '"';
						} else {
							$result .= ' href="#"';
						}
						$result .= ">";

						if ($entry['image']) {
							$result .= '<img src="'. $entry['image'] . '"';
							if ($entry['label']) {
								$result .= ' alt="' . htmlspecialchars($entry['label']) . '"';
								$result .= ' title="' . htmlspecialchars($entry['label']) . '"';
							}
							$result .= '>';
						} else {
							$result .= htmlspecialchars($entry['label']);
						}
						$result .= "</a>";
						$result .= "</li>";
					}
					$result .= "</ul>";
			//		$result .= "Total items: " . $total . "<br>";
			//		$result .= "Total pages: $total_pages";
				}
				return $result;
			}
		}
?>

<?php
	if (!$items_per_page) {
		$items_per_page = 60;
	}
	$current_page = $page;
	if (!$current_page) {
		$current_page = 1;
	}
	$offset = ($current_page-1) * $items_per_page;

	$fields = array(
		'svn',
		'type',
		'name',
		'path',
		'filename',
		'size',
		'owner',
		'modified',
		'language',
		'priority'
	);
	if (!$AR->SVN->enabled) {
		// No SVN if SVN is not enabled;
		unset($fields[0]);
	}

	$colDefs = colDefs($fields);

	$object_list = $this->call('system.list.objects.php', array(
		"limit" => $items_per_page,
		"offset" => $offset
	));

	$data = array();
	if (!is_array($object_list)) {
		$object_list = array();
	}
	if (!is_array($object_list['objects'])) {
		$object_list['objects'] = array();
	}
	foreach($object_list['objects'] as $item) {
		$datarow = array();
		if ($AR->SVN->enabled) {
			$datarow['svn'] = getSvnIcon($item['svn']['status']);
		}
		$datarow['type'] = getTypeIcon($item, $viewtype);
		$datarow['name'] = $item['name'];
		$datarow['filename'] = $item['filename'];
		$datarow['path'] = $item['path'];
		$datarow['size'] = $item['size'];
		$datarow['owner']  = $item['owner'];
		$datarow['modified'] = strftime("%d-%m-%Y",$item['lastchanged']);
		$datarow['priority']  = $item['priority'];

		if (is_array($item['language'])) {
			foreach( $item['language'] as $key => $value ) {
				$datarow['language'] .= "<a href='#' onClick=\"muze.ariadne.registry.set('store_root', muze.ariadne.registry.get('root') + '/-' + muze.ariadne.registry.get('SessionID') + '-/" . $key . "'); muze.ariadne.explore.objectadded(); return false;\" title=\"".htmlspecialchars($value)."\"><img class=\"flag\" src=\"".$AR->dir->images."nls/small/".$key.".gif\" alt=\"".htmlspecialchars($value)."\"></a> ";
			}
		}
		array_push($data, $datarow);
	}
	$divId = "resultsDiv";
	$tableId = "resultsTable";
?>

<script type="text/javascript">
	muze.ariadne.explore.viewpane.path = '<?php echo $this->path ?>';	
</script>

<script type="text/javascript">
        <?php 
		echo showTableJs($divId, $tableId, $fields, $colDefs); 
	?>
</script>

<script type="text/javascript">
	YAHOO.util.Event.onDOMReady(
		function() {
			YAHOO.util.Event.addListener('archildren', 'click', muze.ariadne.explore.viewpane.onClick);
		}
	);
</script>
	<div class="viewpane" id="viewpane">
<?php 
	echo showPaging($object_list['total'], $items_per_page, $current_page, "top");
	if ($viewtype == "details") {
		echo showTable($divId, $tableId, $colDefs, $data);
		?>
		<script type="text/javascript">
			YAHOO.util.Event.onDOMReady(muze.ariadne.explore.viewpane.load_handler);
		</script>
		<?php
	} else {
		echo showList($object_list['objects'], $viewtype);
	}
	if( $viewtype != "details" ) {
?>
		<div class="viewpane_footer">
		</div>
<?php
	}
	echo showPaging($object_list['total'], $items_per_page, $current_page, "bottom");
?>
	</div>
<?php
	}
?>