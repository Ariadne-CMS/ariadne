<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("edit") && $this->CheckConfig()) {
		// Get the differences in the workspace for the children.
//		echo "<pre>";
//		print_r($this->store->getLayerstatus($this->path, true));
//		echo "</pre>";

	?>
<script type="text/javascript">
	function tableinit() {
		var myColumnDefs = [
			{key:"name", label:"<?php echo $ARnls['name']; ?>", sortable:true},
			{key:"path", label:"<?php echo $ARnls['path']; ?>", sortable:true},
			{key:"changes", label:"<?php echo $ARnls['ariadne:workspace:status']; ?>", sortable:true},
			{key:"select", label:"Select", sortable:false},
//, formatter: "checkbox"},
		];

        // Called from within the view template!
		var myDataSource = new YAHOO.util.DataSource(YAHOO.util.Dom.get("workspaceTable"));
		myDataSource.responseType = YAHOO.util.DataSource.TYPE_HTMLTABLE;
		myDataSource.responseSchema = {
			fields: [{key:"name"},
				{key:"path"},
				{key:"changes"},
				{key:"select"}
			]
		};


		var myDataTable = new YAHOO.widget.DataTable("workspaceDiv", myColumnDefs, myDataSource, {});

		myDataTable.subscribe("checkboxClickEvent", function(oArgs) {
				var elCheckbox = oArgs.target;
				var newValue = elCheckbox;

				newValue = "<input type='checkbox' name='" + elCheckbox.name + "' value='" + elCheckbox.value + "'";
				if (elCheckbox.checked) {
					newValue += "checked='checked' ";
				}
				newValue += ">";
				var record = this.getRecord(elCheckbox);
				var column = this.getColumn(elCheckbox);
				record.setData(column.key,newValue);
		});

	}
 	YAHOO.util.Event.onDOMReady(tableinit);
</script>

<div id="workspaceDiv">
	<table id="workspaceTable">
		<thead>
			<th><?php echo $ARnls['name']; ?></th>
			<th><?php echo $ARnls['path']; ?></th>
			<th><?php echo $ARnls['ariadne:workspace:status']; ?></th>
			<th></th>
		</thead>
		<?php
			foreach ($this->store->getLayerstatus($this->path, true) as $entry) {
				$defaultnls = $entry['new']['data']->nls->default;
				$name = $entry['new']['data']->$defaultnls->name;

				$olddefaultnls = $entry['old']['data']->nls->default;
				$oldname = $entry['old']['data']->$olddefaultnls->name;

				if ($oldname != $name) {
					$name .= "<br><em>(Was: $oldname)</em>";
				}

				$path = $entry['new']['path'];
				if ($entry['old']['path'] != $path && $entry['old']['path']) {
					$path .= "<br><em>(Was: " . $entry['old']['path'] . ")</em>";
				}

				$changes = '';
				foreach ($entry['operation'] as $change) {
					$changes .= $ARnls["ariadne:workspace:" . $change] . "<br>";
				}
		?>
			<tr>
				<td><?php echo $name; ?></td>
				<td><?php echo $path; ?></td>
				<td><?php echo $changes; ?></td>
				<td><input type="checkbox" name="workspacepath[<?php echo $entry['new']['path'];?>]" value="1" checked></td>
			</tr>
		<?php	}	?>
	</table>
</div>
<?php
	}
?>
