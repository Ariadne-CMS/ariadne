<?php

	global $AR;
	require_once($AR->dir->install."/lib/ar/html.php");

	class yui {

		function getSectionContent($settings) {
			$result = '';

			if(is_array($settings)) {
				foreach ($settings as $item) {
					$result .= '<a class="sidebar_task';
					if ($item['class']) {
						$result .= ' ' . $item['class'];
					}
					$result .= '"';

					if ($item['href']) {
						$result .= ' href="' . $item['href'] . '"';
					}
					if ($item['onclick']) {
						$result .= ' onclick="' . $item['onclick'] . '"';
					}
					if ($item['target']) {
						$result .= ' target="' . $item['target'] . '"';
					} else {
						// FIXME: This should not be used in strict doctype, but we don't want it in our
						// window either.

						// $result .= ' target="_blank"'; 
					}

					$result .= '>';

					if ($item['icon']) {
						$result .= '<img class="task_icon" src="' . $item['icon'] . '" alt="' . $item['iconalt'] . '" title="' . $item['iconalt'] . '">&nbsp;&nbsp;';
					}

					$itemlabel = $item['nlslabel'];
					$maxlabellength = 25;
					if (mb_strlen($itemlabel, "utf-8") > $maxlabellength) {
						$origName = $itemlabel;
						$itemlabel = "<span title=\"$origName\">" . mb_substr($itemlabel, 0, $maxlabellength-3, "utf-8")."..."."</span>";
					}
					$result .= $itemlabel;
					$result .= "</a>";
				}
			}
			return $result;
		}


		function getSection($section) {
			$invisibleSections= $_COOKIE['invisibleSections'];

			$maxheadlength = 18;
			if ($section['icon']) {
				$maxheadlength = 14;
			}

			$sectionDisplayName = $section['label'];
			$sectionName = $section['id'];
			$icon = $section['icon'];

			if (mb_strlen($sectionDisplayName, "utf-8") > $maxheadlength) {
				$origName = htmlspecialchars($sectionDisplayName);
				$sectionDisplayName = "<span title=\"$origName\">".htmlspecialchars(mb_substr($sectionDisplayName, 0, $maxheadlength-3, "utf-8")."...")."</span>";
			} else {
				$sectionDisplayName = htmlspecialchars($sectionDisplayName);
			}
			$icontag = "";
			if ($icon) {
				$icontag .= '<img src="' . $icon . '" class="icon" alt="' . $section['iconalt'] . '" title="' . $section['iconalt'] . '">';
				if( $section['overlay_icon']  ) {
					$icontag .= '<img src="' . $section['overlay_icon'] . '" class="overlay_icon" alt="' . $section['overlay_iconalt'] . '" title="' . $section['overlay_iconalt'] . '">';
				}
			}

			if (strstr(strtolower($invisibleSections), $sectionName . ";")) {
				$section_class = " collapsed";
			} else {
				$section_class = " expanded";
			}

			$sectionhead_class = "";
			if ($icon) {
				$sectionhead_class .= " iconsection";
			}

			if( $section['inline_icon'] ) {
				$sectionhead_class .= " iconinlinesection";
				$icontag .= '<img src="' . $section['inline_icon'] . '" class="inline_icon" alt="' . $section['inline_iconalt'] . '" title="' . $section['inline_iconalt'] . '">';
			}

			$togglehref = "javascript:muze.ariadne.explore.sidebar.section.toggle('" . $sectionName . "');";

			$result = '';

			$result .= '<div class="section' . $section_class . '">';
			$result .= $icontag;

			$result .= '<div class="sectionhead yuimenubar' . $sectionhead_class . '">';
			$result .= '<a href="' . $togglehref . '">' . $sectionDisplayName . '</a>';
			$result .= '<a class="toggle" href="' . $togglehref . '">&nbsp;</a>';
			$result .= '</div>';

			$result .= '<div class="sectionbody" id="' . $sectionName . '_body">';

			$result .= '<div class="section_content">';

			if ($section['details']) {
				$result .= '<div class="details">';
				$result .= $section['details'];
				$result .= '</div>';
			}

			$result .= self::getSectionContent($section['tasks']);
			$result .= '</div>';

			$result .= '</div>';
			$result .= '</div>';
			return $result;
		}		
		

	
		/* explore.sidebar.info.php */
		function section_table($info) {
			global $ARnls;
			$result = '';
			if (is_array($info)) {
				$rows = ar_html::nodes();
				foreach( $info as $key => $value ) {
					$rows[] = ar_html::tag("tr", ar_html::tag("td", $ARnls[$key].":"), ar_html::tag("td", array("class" => "data"), $value) );
				}
				$result = ar_html::tag("table", array("class" => "infotable"), $rows);
			}
			return $result;
		}
		
		function labelspan($label, $maxlabellength=16) {
			// Reduce length of a label if they are too long.
			if (mb_strlen($label, "utf-8") > $maxlabellength) {
				$label = ar_html::tag("span", array("title" => $label),htmlspecialchars(mb_substr($label, 0, $maxlabellength-3,"utf-8")."...")); 
			} else {
				$label = htmlspecialchars($label);
			}
			return $label;
		}
	
	
	/* dialog.templates.list.php */
		function layout_sortfunc($a, $b) {
			if ($a == $b) {
				 return 0;
			}
			return ($a < $b) ? -1 : 1;
		}
	
	/* explore.php */
		function yui_menuitems($menuitems, $menuname, $menuid='') {
			$result = '';
			if (is_array($menuitems)) {
				$nodes = ar_html::nodes();
				
				foreach ($menuitems as $item) {
					if (!$item['href']) {
						$item['href'] = "#";
					}

					$link = array(
						"class" => $menuname. 'itemlabel',
						"href" => $item["href"],
					);
					
					if( $item["onclick"] ) {
						$link["onclick"] = $item["onclick"];
					}
					if( $item["icon"] ) {
						$icon = ar_html::tag("img", array("src" => $item["icon"], "alt" => $item["iconalt"]));
					} else {
						$icon = false;
					}
					if( $item["label"] ) {
						$content = ar_html::tag("span", array("class" => "menulabel"), $item['label']);
					} else {
						$content = false;
					}

					$a = ar_html::tag("a", $link, $icon, $content);
					
					if( is_array($item['submenu']) ) {
						$submenu = self::yui_menuitems($item['submenu'], "yuimenu");
					} else {
						$submenu = false;
					}
					$nodes[] = ar_html::tag("li", array("class" => $menuname."item"), $a, $submenu);
				}
				
				$div = array( "class" => $menuname );
				if( $menuid ) {
					$div["id"] = $menuid;
				}
				$result = ar_html::tag("div", $div,
								ar_html::tag("div", array("class"=>"bd"),
										ar_html::tag("ul", array("class" => "first-of-type"), $nodes)
								)
							);

			}
			return $result;
		}



	/* Explore.browse.php */
		function showTable($divId, $tableId, $columnDefs, $data) {
		
			if (is_array($columnDefs)) {

				$colnum = count($columnDefs);
		
				$headcols = ar_html::nodes();
			
				for ($num = 0; $num < $colnum; $num++) {
				
					$class = array();
				
					if ($num == 0) {
						$class[] = "yui-dt-first";
					}
					if ($num == ($colnum-1)) {
						$class[] = "yui-dt-last";
					}
					$class[] = 'yui-dt-col'.$columnDefs[$num]['key'];
					$class[] = 'yui-dt-sortable';
					$headcols[] = ar_html::tag('th', array('class' => $class ),
							ar_html::tag('div', array('class' => 'yui-dt-header'), 
								ar_html::tag('span', array('class' => 'yui-dt-label'),
									 ar_html::tag('a', array('class' => 'yui-dt-sortable'), htmlspecialchars($columnDefs[$num]['label']) )
								)
							)
						);
				}
				$head = ar_html::tag('thead', ar_html::tag('tr', array('class' => array('yui-dt-first', 'yui-dt-last')), $headcols) );
			
				if (is_array($data)) {
					$oddeven = 'even';

					$rownums = count($data);
					
					$bodyrows = ar_html::nodes();
					
					for ($rownum = 0; $rownum < $rownums; $rownum++) {
						$rowclass = array();
						if ($rownum == 0) {
							$rowclass[] = 'yui-dt-first';
						}
						if ($rownum == $rownums-1) {
							$rowclass[] = 'yui-dt-last';
						}
						$rowclass[] = 'explore_item';
						$rowclass[] = $oddeven;
						
						$bodycols = ar_html::nodes();
						
						if ($data[$rownum]['name']) {
							$data[$rownum]['name'] = self::labelspan($data[$rownum]['name'], 24);
						}
						if ($data[$rownum]['filename']) {
							$data[$rownum]['filename'] = self::labelspan($data[$rownum]['filename'], 24);
						}
						for ($num = 0; $num < $colnum; $num++) {

							$colclass = array();
							if ($num == 0) {
								$colclass[] = 'yui-dt-first';
							}
							if ($num == ($colnum-1)) {
								$colclass[] = 'yui-dt-first';
							}
							$colclass[] = 'yui-dt-col-'.$columnDefs[$num]['key'];
							$bodycols[] = ar_html::tag('td', array('class' => $colclass), $data[$rownum][$columnDefs[$num]['key']] );
						}
						$bodyrows[] = ar_html::tag('tr', array('class' => $rowclass, 'path' => $data[$rownum]['path']), $bodycols);
						$oddeven = ($oddeven == 'even' ? 'odd' : 'even');
					}
				}
				$body = ar_html::tag('tbody', array('class' => 'yui-dt-body'), $bodyrows);
				
				$table = ar_html::tag('table', array('id' => $tableId), $head, $body);
			}
			echo ar_html::tag('div', array('id' => $divId, 'class' => 'yui-dt-'), $table)."\n";
		}

		function showTableJs($divId, $tableId, $columnDefs) {
			$jsColDefs = '';
			$jsFields = '';
			foreach ($columnDefs as $colDef) {
				$jsColDefs .= "\t\t\t".'{key:"' . $colDef['key'] . '"';
				if ($colDef['label']) {
					$jsColDefs .= ',label:"' . $colDef['label'] . '"';
				}
				if ($colDef['sortable']) {
					$jsColDefs .= ',sortable:true';
					}
				$jsColDefs .= "},\n";

				$jsFields .= "\t\t\t\t".'{key:"' . $colDef['key'] . '"';
				if ($colDef['parser']) {
					$jsFields .= ',parser:"' . $colDef['parser'] . '"';
				}
				$jsFields .= '},'."\n";
			}

			// Strip last comma and \n;
			$jsColDefs = substr($jsColDefs, 0, strlen($jsColDefs)-2);
			$jsFields = substr($jsFields, 0, strlen($jsFields)-2);

			echo '		muze.ariadne.explore.viewpane.myColumnDefs = [' . "\n";
			echo $jsColDefs."\n";
			echo '	        ];' . "\n";
			echo '		muze.ariadne.explore.viewpane.load_handler = function() {' . "\n";
			echo '		        muze.ariadne.explore.viewpane.myDataSource = new YAHOO.util.DataSource(YAHOO.util.Dom.get("' . $tableId . '"));' . "\n";
			echo '		        muze.ariadne.explore.viewpane.myDataSource.responseType = YAHOO.util.DataSource.TYPE_HTMLTABLE;' . "\n";
			echo '		        muze.ariadne.explore.viewpane.myDataSource.responseSchema = {' . "\n";
			echo ' 		            fields: [' . "\n";
			echo $jsFields."\n";
			echo '		            ]' . "\n";
			echo '		        };' . "\n";
			echo '	        	muze.ariadne.explore.viewpane.dataTable = new YAHOO.widget.DataTable("' . $divId . '", muze.ariadne.explore.viewpane.myColumnDefs, muze.ariadne.explore.viewpane.myDataSource,{});' . "\n";
			echo '			muze.ariadne.explore.viewpane.dataTable.subscribe("rowClickEvent", muze.ariadne.explore.viewpane.rowClick);' . "\n";
			echo '			muze.ariadne.explore.viewpane.dataTable.subscribe("rowDblclickEvent", muze.ariadne.explore.viewpane.rowDoubleClick);' . "\n";
			echo '			muze.ariadne.explore.viewpane.dataTable.subscribe("rowMouseoverEvent", muze.ariadne.explore.viewpane.onEventHighlightRow);' . "\n";
			echo '			muze.ariadne.explore.viewpane.dataTable.subscribe("rowMouseoutEvent", muze.ariadne.explore.viewpane.onEventUnhighlightRow);' . "\n";
			echo '		}' . "\n";

		}

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

		function getSvnIcon($status) {
			global $AR;
			if ($status == 'insubversion') {
				$iconsrc =  $AR->dir->images . 'svn/InSubVersionIcon.png';
				$alt = $ARnls['ariadne:svn:insubversion'];
			} elseif ($status == 'modified') {
				$iconsrc = $AR->dir->images . 'svn/ModifiedIcon.png';
				$alt = $ARnls['ariadne:svn:modified'];
			}

			if ($iconsrc) {
				return ar_html::tag('img', array('class' => 'explore_svnicon', 'src' => $iconsrc, 'alt' => $alt ));
			} else {
				return null;
			}
		}

		function getTypeIcon($item, $viewtype) {
		
		
			$type = $item["type"];
			$vtype = $item["vtype"];
			
			$iconsize = 'small';
			if ($viewtype == 'icons') {
				$iconsize = 'large';
			} elseif($viewtype == 'list') {
				$iconsize = 'medium';
			}

			$result = ar_html::nodes();
			if( $type == "pshortcut" ) {
				$result[] = ar_html::tag('img', array('title' => $item['vtype'], 'alt' => $item['vtype'], 'class' => 'explore_icon', 'src' => $item['icons'][$iconsize]) );
				$result[] = ar_html::tag('img', array('title' => $item['type'], 'alt' => $item['type'], 'class' => 'explore_icon_shortcut_'.$viewtype, 'src' => $item['overlay_icons'][$iconsize]) );
			} else {
				$result[] = ar_html::tag('img', array('title' => $item['type'], 'alt' => $item['type'], 'class' => 'explore_icon', 'src' => $item['icons'][$iconsize]) );
			}
			return $result;
		}

		function showList($objects, $viewtype='list') {
			global $AR, $ARnls;
			
			if (is_array($objects) && count($objects) > 0) {
				$nodes = ar_html::nodes();
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
				foreach ($objects as $node) {
					$content = self::getTypeIcon($node, $viewtype);
					if( is_array($node['svn']) ) {
						$content[] = self::getSvnIcon($node['svn']['status']);
					}
					$content[]= ar_html::tag('span', array('class' => 'explore_name'), self::labelspan($node['name'], $maxlen ));
					$nodes[] = ar_html::tag( 'li', array('class' => 'explore_item'), ar_html::tag( 'a', array('href' => $node['local_url'].'explore.html', 'onDblClick' => "top.muze.ariadne.explore.view('" . htmlspecialchars($node['path']) . "'); return false;", 'title' => $node['name']), $content) );
				}
				$result = ar_html::tag('ul', array('class' => array('explore_list', $viewtype) ), $nodes);
			} else {
				$result = ar_html::tag('div', array('class'=>'noobjects'), htmlspecialchars($ARnls['ariadne:no_objects_found']) );
			}
			echo $result."\n";
		}
		
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
			echo $result;
		}
		
		
		/* dialog.add.list.php */
		function getTypes($arObject, $showall) {
			$result = Array();
			if (!$showall) {
				$typetree = $arObject->call('typetree.ini');
				$thistypetree = $typetree[$arObject->type];

				if (is_array($thistypetree)) {
					foreach( $thistypetree as $type => $name ) {
						$result[$type] = $name;
					}
				}
			} else {
				$systemtypes = $arObject->ls("/system/ariadne/types/", "system.get.phtml");
				foreach ($systemtypes as $object) {
					$type=$object->data->value;
					$name=$object->nlsdata->name;

					$result[$type] = $name;
				}

				$arObject->call('typetree.ini');
				$arTypeNames = $arObject->getvar('arTypeNames');
				if (is_array($arTypeNames)) {
					$result = array_merge($result, $arTypeNames);
				}
			}
			asort($result);
			return $result;
		}
		
		function checkType($arObject, $type, $name, $currentpath, $arReturnTemplate) {
			global $AR;
			global $ARCurrent;
			if (!$arObject->CheckSilent("add", $type)) {
				$class .= "greyed";
			}
			$dotPos=strpos($type, '.');
			if (false!==$dotPos) {
				$realtype=substr($type, 0, $dotPos);
			} else {
				$realtype=$type;
			}

			$icon = $arObject->call("system.get.icon.php", array("type" => $type));

			$itemurl = $currentpath . $arReturnTemplate . "?arNewType=" . RawUrlEncode($type) . "&amp;" . ldGetServerVar("QUERY_STRING");
			$result = array(
				"type" => $type,
				"class" => $class,
				"icon" => $icon,
				"realtype" => $realtype,
				"href" => $itemurl,
				"name" => $name
			);
			return $result;
		}
		
		function getItems($arObject, $typeslist, $currentpath, $arReturnTemplate) {
			$result = array();
			foreach( $typeslist as $type => $name ) {
				$result[] = self::checkType($arObject, $type, $name, $currentpath, $arReturnTemplate);
			}
			return $result;
		}
	}
?>