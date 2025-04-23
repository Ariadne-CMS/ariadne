<?php
	if (!$this->validateFormSecret()) {
		error($ARnls['ariadne:err:invalidsession']);
		exit;
	}
	if (!isset($srcpath)) {
		$srcpath = '';
	}
	ldDisablePostProcessing();
	if ($this->CheckLogin("config") && $this->CheckConfig()) {
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
			echo "line.innerHTML = '" . str_replace("\n", "<br>", AddSlashes(htmlspecialchars($str??''))) . "';\n";
			echo "document.getElementById('progress_verbose').appendChild(line);\n";
			echo "</script>";
			flush();
		}
?>
<div id="progressbars">
        <div class="sourcepath">
                <?php echo $ARnls['importobject'] . " $srcpath"; ?>
		<div id="progressbar">
			<div id="progress"></div>
			<div id="progress_text">0/<?php echo ($total ?? 0); ?></div>
		</div>
	</div>
</div>
<div id="progress_verbose"></div>
<?php
			$file = ldRegisterFile("source", $this->error);
			if ($file && !$this->error) {
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
				if ($force=$this->getdata("force","none")) {
					$ARCurrent->options["force"]=true;
				}
				$source = $this->store->get_config("files")."temp/".$file["source_temp"];
				$ax_config["writeable"]=false;
				$ax_config["database"]=$source;
				set_time_limit(0);

				$store=new axstore("", $ax_config);
				if (!$store->error) {

					$ARCurrent->importStore=&$this->store;
					// srcpath and destpath may be empty
					$callArgs=array("srcpath" => $srcpath,
									"destpath" => $this->path);

					$store->call("system.export.phtml", $callArgs,
					$store->get("/"));
					$error=$store->error;

					$store->close();

				} else {
					$error="import error: ".$store->error;
				}
				unlink($source);
			} else {
				$error="import error: '$source' could not find uploaded file. (does the filesize exceed the maximum upload size?)";
			}

			if ($error) {
				echo $error."\n";
			}
		?>
	<?php
	}
?>
