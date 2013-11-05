<?php
	if (!$this->validateFormSecret()) {
		error($ARnls['ariadne:err:invalidsession']);      
		exit;
	}
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
				echo "document.getElementById('progress' + currentProgressBar).style.width = '" . $progress . "%';\n";
				echo "document.getElementById('progress_text' + currentProgressBar).innerHTML = '" . $current . "/" . $total . "';\n";
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

		$sources = $this->getvar("sources");
		if (!$sources) {
			$sources = array($this->path);
		}

		$ax_config["writeable"]=true;
		$ARCurrent->session->put("tempname",$ax_config["database"]);

		$importStore=new axstore("", $ax_config);
		if (!$importStore->error) {
			set_time_limit(0);
			$ARCurrent->importStore=&$importStore;
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
<div id="progressbars">
<?php foreach ($sources as $key => $srcpath) { ?>
	<div class="sourcepath">
		<?php echo $ARnls['ariadne:export'] . " $srcpath"; ?>
		<div class="progressbar">
			<div class="progress" id="progress<?php echo $key; ?>"></div>
			<div class="progress_text" id="progress_text<?php echo $key; ?>">0/<?php echo $total; ?></div>
		</div>
	</div>
<?php } ?>
</div>
<div id="progress_verbose"></div>
<?php
		foreach ($sources as $key => $srcpath) {
			echo "<script type='text/javascript'>currentProgressBar = '$key';</script>";

			$sourceob = current($this->get($srcpath, "system.get.phtml"));

			if ($full_path) {
				$destpath="";
			} else {
				if ($sourceob->parent=="..") {
					$destpath="/";
				} else {
					$destpath="/".substr($sourceob->path, strlen($sourceob->parent));
				}
			}

			if (!$importStore->error) {
				$callArgs=Array("srcpath" => $srcpath, "destpath" => $destpath);
				$error = $this->call("system.export.phtml", $callArgs);
			} else {
				$error="ax error: ".$importStore->error;
			}

			if ($error) {
				echo $error."\n";
			}

			if ($error) {
				// prevent js download code from running 
				echo "<script type='text/javascript'> error=true; </script>";
			}
		}
		$importStore->close();
	}
?>
