<?php
	ldDisablePostProcessing();
	if ($this->CheckLogin("config") && $this->CheckConfig()) {

		require($this->store->get_config("code")."/configs/axstore.phtml");
		include($this->store->get_config("code")."/stores/axstore.phtml");

		$ARCurrent->options["verbose"]=true;
		if ($without_grants=$this->getdata("without_grants","none")) {
			$ARCurrent->options["without_grants"]=true;
		}
		if ($without_data=$this->getdata("without_data","none")) {
			$ARCurrent->options["without_data"]=true;
		}
		if ($without_templates=$this->getdata("without_templates","none")) {
			$ARCurrent->options["without_templates"]=true;
		}
		if ($without_files=$this->getdata("without_files","none")) {
			$ARCurrent->options["without_files"]=true;
		}

		function progress($current, $total) {
			if ($total > 0) {
				$progress = (int)(100*($current)/$total);

				echo "<script type='text/javascript'>\n";
				echo "document.getElementById('progress').style.width = '" . $progress . "%';\n";
				echo "document.getElementById('progress_text').innerHTML = '" . $current . "/" . $total . "';\n";
				echo "</script>";
				flush();
			}
		}

		function display($str) {
			echo "<script type='text/javascript'>\n";
			echo "var line = document.createElement('SPAN');";
			echo "line.innerHTML = '" . str_replace("\n", "<br>", htmlspecialchars($str)) . "';\n";
			echo "document.getElementById('progress_verbose').appendChild(line);\n";
			echo "</script>";
			flush();
		}

		set_time_limit(0);

		$ax_config["database"]=tempnam($this->store->get_config("files")."temp/","ax");
		@unlink($ax_config["database"]);
		$srcpath=$this->path;
		$ax_config["writeable"]=true;
		$ARCurrent->session->put("tempname",$ax_config["database"]);
		if ($full_path) {
			$destpath="";
		} else {
			if ($this->parent=="..") {
				$destpath="/";
			} else {
				$destpath="/".substr($this->path, strlen($this->parent));
			}
		}
?>
<script type="text/javascript">
	error=false;
	window.onload=startdownload;
	
	function startdownload() {
		if (!error) {
			location.href='object.export.ax';
		}
	}
	
</script>
<?php echo $ARnls['ariadne:export'] . " $srcpath"; ?>
<div id="progressbar">
	<div id="progress"></div>
	<div id="progress_text">0/<?php echo $total; ?></div>
</div>
<div id="progress_verbose"></div>
<?php
			$importStore=new axstore("", $ax_config);
			if (!$importStore->error) {
				set_time_limit(0);
				$ARCurrent->importStore=&$importStore;
				$callArgs=Array("srcpath" => $srcpath,
								"destpath" => $destpath);

				$error = $this->call("system.export.phtml", $callArgs);
				$importStore->close();

			} else {
				$error="ax error: ".$importStore->error;
			}

			if ($error) {
				echo $error."\n";
			}
		?>
<?php
		if ($error) {
			// prevent js download code from running 
			echo "<script type='text/javascript'> error=true; </script>";
		}
	}
?>