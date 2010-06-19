<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("read") && $this->CheckConfig()) {
		include_once($this->store->get_config("code")."nls/ariadne.".$this->reqnls);
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

		$colDefs = yui::colDefs($fields);

		$listargs = array(
			"limit" => $items_per_page,
			"offset" => $offset
		);
		
	        if ($viewtype == "details") {
			$order = $_COOKIE["sortorder"];
			$direction = $_COOKIE["sortdirection"];
			if ($order) {
				$listargs["order"] = $order;
			}
			if ($direction) {
				$listargs["direction"] = $direction;
			}
		}

		$object_list = $this->call('system.list.objects.php', $listargs);

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
	yui::showTableJs($divId, $tableId, $fields, $colDefs); 
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
	yui::showPaging($object_list['total'], $items_per_page, $current_page, "top");
	if ($viewtype == "details") {
		yui::showTable($divId, $tableId, $colDefs, $data);
		?>
		<script type="text/javascript">
			YAHOO.util.Event.onDOMReady(muze.ariadne.explore.viewpane.load_handler);
		</script>
		<?php
	} else {
		yui::showList($object_list['objects'], $viewtype);
	}
	if( $viewtype != "details" ) {
?>
		<div class="viewpane_footer">
		</div>
<?php
	}
	yui::showPaging($object_list['total'], $items_per_page, $current_page, "bottom");
?>
	</div>
<?php
	}
?>