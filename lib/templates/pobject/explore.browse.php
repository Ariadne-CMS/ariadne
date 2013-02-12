<?php
	$ARCurrent->allnls = true;
	if ($this->CheckLogin("read") && $this->CheckConfig()) {

		if ($AR->user->data->language && is_object($ARnls) ) {
			$ARnls->setLanguage($AR->user->data->language);
		}

		require_once($this->store->get_config("code")."modules/mod_yui.php");
		if (!$ARCurrent->arTypeTree) {
			$this->call("typetree.ini");
		}		
		if (!$items_per_page) {
			$items_per_page = 60;
		}
		$current_page = $page;
		if (!$current_page) {
			$current_page = 1;
		}
		if (!$order=$this->getvar('order')) {
			$order = "name";
		}
		if (!$direction=$this->getvar('direction')) {
			$direction = "ASC";
		}

		$offset = ($current_page-1) * $items_per_page;

		$colDefs = array(
			array( 'key' => 'svn', 'label' => $ARnls['svn'], 'sortable' => true ),
			array( 'key' => 'type', 'label' => $ARnls['type'], 'sortable' => true ),
			array( 'key' => 'name', 'label' => $ARnls['name'], 'sortable' => true ),
			array( 'key' => 'path', 'label' => $ARnls['path'], 'sortable' => true ),
			array( 'key' => 'filename', 'label' => $ARnls['filename'], 'sortable' => true ),
			array( 'key' => 'size', 'label' => $ARnls['size'], 'sortable' => true, 'parser' => 'number' ),
			array( 'key' => 'owner', 'label' => $ARnls['owner'], 'sortable' => true ),
			array( 'key' => 'modified', 'label' => $ARnls['modified'], 'sortable' => true, 'formatterfunc' => 'muze.ariadne.explore.dateFormatter', 'parserfunc' => 'muze.ariadne.explore.dateParser' ),
			array( 'key' => 'language', 'label' => $ARnls['language'], 'sortable' => true ),
			array( 'key' => 'priority', 'label' => $ARnls['priority'], 'sortable' => true, 'parser' => 'number' ),
		);
		if (!$AR->SVN->enabled) {
			// No SVN if SVN is not enabled;
			array_shift($colDefs);
		}

		$listargs = array(
			"limit" => $items_per_page,
			"offset" => $offset,
			"sanity" => true,
			"order" => $order,
			"direction" => $direction
		);
		
		$object_list = $this->call('system.list.objects.php', $listargs);

		if (!is_array($object_list)) {
			$object_list = array();
		}
		if (!is_array($object_list['objects'])) {
			$object_list['objects'] = array();
		}
		$divId = "resultsDiv";
		$tableId = "resultsTable";

		if( $viewtype == "details" ) {
			$datalist = array();
			foreach($object_list['objects'] as $item) {
				$datarow = array();
				if ($AR->SVN->enabled) {
					$datarow['svn'] = yui::getSvnIcon($item['svn']['status']);
				}
				$datarow['type'] = yui::getTypeIcon($item, $viewtype);
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
				array_push($datalist, $datarow);
			}
		}

?>

<script type="text/javascript">
	muze.ariadne.explore.viewpane.path = '<?php echo $this->path ?>';	
</script>

<script type="text/javascript">
<?php 
		yui::showTableJs($divId, $tableId, $colDefs); 
?>
</script>
<script type="text/javascript">
	YAHOO.util.Event.onDOMReady(
		function() {
			YAHOO.util.Event.addListener('archildren', 'click', muze.ariadne.explore.viewpane.onClick);
		}
	);
</script>
<?php
		$eventData = new object();
		$eventData = ar_events::fire( 'ariadne:onbeforeexplore', $eventData );
		if ( $eventData ) {
?>
	<div class="browse viewpane <?php echo $viewtype; ?>" id="viewpane">
<?php 
	yui::showPaging($object_list['total'], $items_per_page, $current_page, "top");
	if ($viewtype == "details") {
		yui::showTable($divId, $tableId, $colDefs, $datalist);
		?>
		<script type="text/javascript">
			YAHOO.util.Event.onDOMReady(muze.ariadne.explore.viewpane.load_handler);
		</script>
		<?php
	} else {
		yui::showList($object_list, $viewtype);
	}
?>
	<div class="viewpane_footer"></div>
<?php
	yui::showPaging($object_list['total'], $items_per_page, $current_page, "bottom");
?>
	</div>
<?php
			ar_events::fire('ariadne:onexplore');
		}
	}
?>