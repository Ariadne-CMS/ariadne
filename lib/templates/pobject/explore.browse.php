<?php
	$ARCurrent->allnls = true;
	if (!($this->CheckLogin("read") && $this->CheckConfig())) {
		return;
	}
	if ($AR->user->data->language && is_object($ARnls) ) {
		$ARnls->setLanguage($AR->user->data->language);
	}

	require_once($this->store->get_config("code")."modules/mod_yui.php");
	if (!isset($ARCurrent->arTypeTree)) {
		$this->call("typetree.ini");
	}
	if (!isset($items_per_page) || !$items_per_page) {
		$items_per_page = 60;
	}
	$current_page = (isset($page) ? $page : null);
	if (!$current_page) {
		$current_page = 1;
	}
	$direction = $this->getvar('direction');
	$direction = ($direction=="DESC" ? 'DESC' : 'ASC');
	$orderqueries = array(
		"name" => array(
			"name.$nls.value",
			"name.none.value"
		),
		"ctime" => array(
			"time.ctime",
			"name.$nls.value",
			"name.none.value"
		),
		"path" => array(
			"path",
			"name.$nls.value",
			"name.none.value"
		),
		"mtime" => array(
			"time.mtime",
			"name.$nls.value",
			"name.none.value"
		),
		"priority" => array(
			"priority",
			"name.$nls.value",
			"name.none.value"
		)
	);

	// Aliases
	$orderqueries['modified'] = $orderqueries['mtime'];
	$orderqueries['filename'] = $orderqueries['path'];

	$order = $this->getvar('order');
	if (!$order || !($orderqueries[$order]??null)) {
		$orderQuery = "name.$nls.value $direction, name.none.value $direction";
	} else {
		$orderQuery = [];
		foreach( $orderqueries[$order] as $orderpart ) {
			$orderQuery[] = $orderpart.' '.$direction;
		}
		$orderQuery = implode(',',$orderQuery);
	}

	$offset = ($current_page-1) * $items_per_page;

	$colDefs = [
		'svn'      => [ 'label' => $ARnls['svn'], 'sortable' => true ],
		'type'     => [ 'label' => $ARnls['type'], 'sortable' => true ],
		'name'     => [ 'label' => $ARnls['name'], 'sortable' => true ],
		'path'     => [ 'label' => $ARnls['path'], 'sortable' => true ],
		'filename' => [ 'label' => $ARnls['filename'], 'sortable' => true ],
		'size'     => [ 'label' => $ARnls['size'], 'sortable' => true, 'parser' => 'number' ],
		'owner'    => [ 'label' => $ARnls['owner'], 'sortable' => true ],
		'modified' => [ 'label' => $ARnls['modified'], 'sortable' => true, 'formatterfunc' => 'muze.ariadne.explore.dateFormatter', 'parserfunc' => 'muze.ariadne.explore.dateParser' ],
		'language' => [ 'label' => $ARnls['language'], 'sortable' => true ],
		'priority' => [ 'label' => $ARnls['priority'], 'sortable' => true, 'parser' => 'number' ],
		'icons'    => [ 'label' => '', 'sortable' => false, 'hide' => true ],
		'icon'     => [ 'label' => '', 'sortable' => false, 'hide' => true ]
	];
	if (!$AR->SVN->enabled || !$this->CheckSilent('layout')) {
		// No SVN if SVN is not enabled;
		array_shift($colDefs);
	}
	$args = array(
		"path"      => ($this instanceof \pshortcut && $this->data->path && ar::exists($this->data->path)) ? $this->data->path : $this->path,
		"query"     => '',
		"limit"     => $items_per_page,
		"offset"    => $offset,
		"sanity"    => true,
		"order"     => $orderQuery,
		"view"      => $viewtype,
		"total"     => ar::ls()->count(),
		"template"  => 'system.list.entry.php',
		"args"      => array(
			"columns" => $colDefs
		),
		"filters"   => array(),
		"method"    => "post"
	);
	$args['parent'] = $args['path'];

	$eventData = ar_events::fire( 'ariadne:onFilterGather', $args );
	$eventData = ar_events::fire( 'ariadne:onFilter', $eventData );
	if ( $eventData ) {
                $query = $eventData['query'];
                if ( $eventData['parent'] && $query ) {
                        $query = sprintf('object.parent="%s" and ( %s )', $eventData['parent'], $query );
                } else if ( !$query ) {
                        $query = sprintf('object.parent="%s"', $eventData['parent'] ? $eventData['parent'] : $eventData['path']);
                }

		$object_list = ar::get($eventData['path'])
		->find($query)
		->limit($eventData['limit'])
		->offset($eventData['offset'])
		->order($eventData['order'])
		->call($eventData['template'], $eventData['args']);

		$object_count = ar::get($eventData['path'])
		->find($query)
		->limit($eventData['limit'])
		->offset($eventData['offset'])
		->order($eventData['order'])
		->count();

		$divId = "resultsDiv";
		$tableId = "resultsTable";

		$colDefs = $eventData['args']['columns'];
		foreach($colDefs as $colKey => $colDef ) {
			if ( isset($colDef['hide']) && $colDef['hide'] ) {
				unset($colDefs[$colKey]);
			} else {
				$colDefs[$colKey] = array_merge($colDef, [ 'key' => $colKey ]);
			}
		}
		$colDefs        = array_values($colDefs);
		$viewtype       = $eventData['view'];
		$items_per_page = $eventData['limit'];
		$current_page   = 1 + floor($eventData['offset'] / $eventData['limit']);

		if( $viewtype == "details" ) {
			$datalist = array();
			foreach($object_list as $item) {
				$datarow = $item;
				if ($AR->SVN->enabled) {
					$datarow['svn'] = yui::getSvnIcon($item['svn']['status']);
				}
				$datarow['type']     = yui::getTypeIcon($item, $args['view']);

				if (is_array($item['language'])) {
					$datarow['language'] = '';
					foreach( $item['language'] as $key => $value ) {
						$datarow['language'] .= "<img class=\"flag\" src=\"".$AR->dir->images."nls/small/".$key.".gif\" alt=\"".htmlspecialchars($value??'')."\"> ";
					}
				}
				array_push($datalist, $datarow);
			}
		}
		$colDefsJson = json_encode($colDefs);
		echo <<< EOF
	<script type="text/javascript">
		muze.ariadne.explore.viewpane.path = '{$this->path}';
	</script>

	<script type="text/json" id='yuiTableDefinition'>
		{$colDefsJson}
	</script>
	<script type="text/javascript">
                muze.ariadne.explore.viewpane.load_handler = function() {
                        var myColumnDefs = JSON.parse(document.getElementById('yuiTableDefinition').innerHTML);
                        var myFieldDefs  = myColumnDefs;
                        var myDataSource = new YAHOO.util.DataSource(YAHOO.util.Dom.get("resultsTable"));
                        myDataSource.responseType = YAHOO.util.DataSource.TYPE_HTMLTABLE;
                        myDataSource.responseSchema = {
                                fields: myFieldDefs
                        };
                        muze.ariadne.explore.viewpane.dataTable = new YAHOO.widget.DataTable("resultsDiv",
                                myColumnDefs,
                                myDataSource,
                                {}
                        );
                        muze.ariadne.explore.viewpane.dataTable.subscribe("rowClickEvent", muze.ariadne.explore.viewpane.rowClick);
                        muze.ariadne.explore.viewpane.dataTable.subscribe("rowDblclickEvent", muze.ariadne.explore.viewpane.rowDoubleClick);
                        muze.ariadne.explore.viewpane.dataTable.subscribe("rowMouseoverEvent", muze.ariadne.explore.viewpane.onEventHighlightRow);
                        muze.ariadne.explore.viewpane.dataTable.subscribe("rowMouseoutEvent", muze.ariadne.explore.viewpane.onEventUnhighlightRow);
                        muze.ariadne.explore.viewpane.dataTable.subscribe("columnSortEvent", muze.ariadne.explore.viewpane.onEventSortColumn);
                        muze.ariadne.explore.viewpane.hideRows();
                };
		YAHOO.util.Event.onDOMReady(function() {
			muze.event.attach( document.body, 'click', function(evt) {
				evt = muze.event.get(evt);
				muze.ariadne.explore.viewpane.onClick(evt);
			});
			YAHOO.util.Event.addListener('archildren', 'selected', muze.ariadne.explore.viewpane.onSelected);
			YAHOO.util.Event.addListener('archildren', 'clearselection', muze.ariadne.explore.viewpane.unselectItem);
		});
	</script>
	<div class="browse viewpane {$viewtype}" id="viewpane">
EOF;
		yui::showPaging($object_count, $items_per_page, $current_page, "top");
		if ($viewtype == "details") {
			yui::showTable($divId, $tableId, $colDefs, $datalist);
			echo <<<EOF
			<script type="text/javascript">
				YAHOO.util.Event.onDOMReady(muze.ariadne.explore.viewpane.load_handler);
			</script>
EOF;
		} else {
			yui::showList(['objects' => $object_list, 'total' => $object_count], $viewtype);
		}
		echo '<div class="viewpane_footer"></div>';
		yui::showPaging($object_count, $items_per_page, $current_page, "bottom");
		echo <<<EOF
	</div>
<article class="dropzoneInfo">
	<progress id="uploadprogress" class="uploadprogress" min="0" max="100" value="0">0</progress>
</article>
EOF;
		ar_events::fire('ariadne:onexplore', $eventData);
	}
?>