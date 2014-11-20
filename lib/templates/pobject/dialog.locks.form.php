<?php
	$locks=$this->store->mod_lock->get_locks($this->data->login);
	$arReturnPage = $this->getdata('arReturnPage');

	require_once($this->store->get_config("code")."modules/mod_yui.php");

	$fields = array(
		"path",
		"expires",
		"unlock"
	);

	$colDefs = yui::colDefs($fields);
	$data = array();
	if (is_array($locks)) {
		foreach ($locks as $path => $lock) {
			$datarow = array(
				"path" => '<label for="unlock_' . $path . '">' . $path . '</label>',
				"expires" => '<label for="unlock_' . $path . '">' . date("d-m-Y H:i:s",$lock["release"]) . '</label>'
			);
			$datarow["unlock"] = '<input class="checkbox" type="checkbox" id="unlock_' . $path . '" name="unlock[' . $path . ']">';
			array_push($data, $datarow);
		}
?>
<script type="text/javascript">
	function initresults() {
		if (YAHOO.util.Dom.get("resultsTable")) {
			var myDataSource = new YAHOO.util.DataSource(YAHOO.util.Dom.get("resultsTable"));
			myDataSource.responseType = YAHOO.util.DataSource.TYPE_HTMLTABLE;
			myDataSource.responseSchema = {
				fields: [{key:"path"},
					{key:"expires"},
					{key:"unlock"}
					]
			};
			var myColumnDefs = [
				{key:"path", label:"<?php echo $ARnls['path'];?>", sortable:true},
				{key:"expires", label:"<?php echo $ARnls['expires'];?>", sortable:true},
				{key:"unlock", label:"<?php echo $ARnls['unlock'];?>", sortable:true}
			];
			var myDataTable = new YAHOO.widget.DataTable("resultSet", myColumnDefs, myDataSource, {});
			myDataTable.subscribe("rowMouseoverEvent", function(event) { YAHOO.util.Dom.addClass(event.target, "highlight"); });
			myDataTable.subscribe("rowMouseoutEvent", function(event) { YAHOO.util.Dom.removeClass(event.target, "highlight"); });
		}
	}

	YAHOO.util.Event.onDOMReady(initresults);
</script>
<script type="text/javascript">
	function toggleAll() {
		var boxes = YAHOO.util.Dom.getElementsByClassName('checkbox');
		if (boxes.length) {
			var check = !boxes[0].checked;
			for (var i = 0; i < boxes.length; i++) {
				boxes[i].checked = check;
			}
		}
	}
</script>

	<input type="hidden" name="arReturnPage" value="<?php echo htmlspecialchars($arReturnPage); ?>">
	<div id="resultSet" class="yui-dt">
		<table id="resultsTable">
		<thead>
			<tr class="yui-dt-first yui-dt-last">
				<th class="yui-dt-first yui-dt-col-path yui-dt-sortable"><div class="yui-dt-liner"><span class="yui-dt-label"><a class="yui-dt-sortable"><?php echo $ARnls["path"]; ?></div></a></span></th>
				<th class="yui-dt-col-expires yui-dt-sortable"><div class="yui-dt-liner"><span class="yui-dt-label"><a class="yui-dt-sortable"><?php echo $ARnls["expires"]; ?></div></a></span></th>
				<th class="yui-dt-last yui-dt-col-unlock yui-dt-sortable"><div class="yui-dt-liner"><span class="yui-dt-label"><a class="yui-dt-sortable"><?php echo $ARnls["unlock"]; ?></div></a></span></th>
			</tr>
		</thead>
		<tbody class="yui-dt-body yui-dt-data">
			<?php
				$oddeven = "odd";
				$first = true;
				foreach ($data as $datarow) {
			?>
				<tr class="<?php
				if( $first ) {
					echo " yui-dt-first";
				}
				echo " yui-dt-".$oddeven;
				?>">
					<td class="yui-dt-sortable yui-dt-first yui-dt-col-path"><?php echo $datarow['path']; ?></td>
					<td class="yui-dt-sortable yui-dt-col-expires"><?php echo $datarow['expires']; ?></td>
					<td "yui-dt-sortable yui-dt-last yui-dt-col-unlock"><?php echo $datarow['unlock']; ?></td>
				</tr>
			<?php
					$oddeven = ($oddeven == "odd") ? "even" : "odd";
					$first = false;
				}
			?>
		</tbody>
		</table>
	</div>
	<input class="select_all button" type="button" value="<?php echo $ARnls['ariadne:lock:toggle_all']; ?>" onclick="toggleAll()">
<?php
	}
?>
