<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckSilent("read") && $this->CheckConfig()) {
		ldSetUserCookie(array(
			"query" => $this->getdata("query"),
			"searchname" => $this->getdata("searchname"),
			"searchtext" => $this->getdata("searchtext"),
			"arimplements" => $this->getdata("arimplements"),
			"context" => $context ?? null,
			"advanced" => $advanced ?? null
		), "ariadneDialogSearch");

		$query = $this->call("dialog.search.results.query.php", array("context" => ($context ?? null), "advanced" => ($advanced ?? null)));

		if ($query || $this->getvar("wgWizAction") === "0" ) {
?>
<style type="text/css">
	#resultSet {
		margin-top: 10px;
		margin-left: 3px;
		margin-right: 3px;
		position: relative;
	}
	#resultSet table {
		width: 100%;
	}
	.yui-dt table th.yui-dt-last,
	.yui-dt table td.yui-dt-last,
	.yui-dt table thead {
		border-right: 0px;
	}
	#resultSet img {
		border: 0px;
	}
	#resultSet img.icon {
		height: 16px;
		width: 16px;
	}
	#resultSet A {
		text-decoration: none;
		color: black;
	}
</style>
<script type="text/javascript">
	// Backwards compatibility hooks;
	if (
		window.opener &&
		window.opener.muze &&
		window.opener.muze.ariadne &&
		window.opener.muze.ariadne.explore &&
		window.opener.muze.ariadne.explore.view
	) {
		View = window.opener.muze.ariadne.explore.view;
	}
	function initresults() {
		if (YAHOO.util.Dom.get("resultsTable")) {
			var myDataSource = new YAHOO.util.DataSource(YAHOO.util.Dom.get("resultsTable"));
			myDataSource.responseType = YAHOO.util.DataSource.TYPE_HTMLTABLE;
			myDataSource.responseSchema = {
				fields: [{key:"name"},
					{key:"location"},
					{key:"size"},
					{key:"modified"},
					{key:"language"}
					]
			};
			var myColumnDefs = [
				{key:"name", label:"<?php echo $ARnls['name'];?>", sortable:true},
				{key:"location", label:"<?php echo $ARnls['location'];?>", sortable:true},
				{key:"size", label:"<?php echo $ARnls['size'];?>", sortable:true},
				{key:"modified", label:"<?php echo $ARnls['modified'];?>", sortable:true},
				{key:"language", label:"<?php echo $ARnls['language'];?>", sortable:true}
			];
			var myDataTable = new YAHOO.widget.DataTable("resultSet", myColumnDefs, myDataSource, {});
		}
	}
	YAHOO.util.Event.onDOMReady(initresults);

</script>

	<div id="resultSet" class="yui-dt">
		<table id="resultsTable">
		<thead>
			<tr class="yui-dt-first yui-dt-last">
				<th class="yui-dt-first yui-dt-col-name yui-dt-sortable"><div class="yui-dt-liner"><span class="yui-dt-label"><a class="yui-dt-sortable"><?php echo $ARnls["name"]; ?></div></a></span></th>
				<th class="yui-dt-col-location yui-dt-sortable"><div class="yui-dt-liner"><span class="yui-dt-label"><a class="yui-dt-sortable"><?php echo $ARnls["location"]; ?></div></a></span></th>
				<th class="yui-dt-col-size yui-dt-sortable"><div class="yui-dt-liner"><span class="yui-dt-label"><a class="yui-dt-sortable"><?php echo $ARnls["size"]; ?></div></a></span></th>
				<th class="yui-dt-col-modified yui-dt-sortable"><div class="yui-dt-liner"><span class="yui-dt-label"><a class="yui-dt-sortable"><?php echo $ARnls["modified"]; ?></div></a></span></th>
				<th class="yui-dt-last yui-dt-col-language yui-dt-sortable"><div class="yui-dt-liner"><span class="yui-dt-label"><a class="yui-dt-sortable">&nbsp;</div></a></span></th>
			</tr>
		</thead>
		<tbody class="yui-dt-body yui-dt-data">
<?php
			$this->putvar("oddeven", "odd");
			$this->putvar("first", "true");
			if( !$this->find($arPath, $query, "dialog.search.results.show.php", "", 0, 0) ){
				if( $this->error) {
					echo "<tr><td colspan=5>";
					error($this->error);
					echo "</td></tr>";
				}
			}
?>
		</tbody>
		</table>
	</div>
<?php
		}
	}
?>
