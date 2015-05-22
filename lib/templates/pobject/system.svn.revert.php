<?php
	$ARCurrent->nolangcheck = true;
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		$this->resetloopcheck();

		$type = $this->getvar("type");
		$function = $this->getvar("function");
		$language = $this->getvar("language");

		$fstore	= $this->store->get_filestore_svn("templates");
		$svn	= $fstore->connect($this->id, $username, $password);
		// FIXME: error checking
		$status = $fstore->svn_status($svn);

		if ($status) {
			$templates = array();
			if( $type && $function && $language ) {
				$filename = $type . "." . $function . "." . $language . ".pinp";
				if( $status[$filename]['wc-status']['item'] != 'unversioned' ) {
					$props = $fstore->svn_get_ariadne_props($svn, $filename);
					echo "<span class='svn_addtemplateline'>Reverting ".$this->path.$props["ar:function"]." (".$props["ar:type"].") [".$props["ar:language"]."] ".( $props["ar:default"] == '1' ? $ARnls["default"] : "")."</span>\n";
					$fstore->svn_revert($svn, $filename);
					$templates[] = $fstore->get_path($svn, $filename);
				}
			} else {
				foreach($status as $filename => $svn_status) {
					if ($svn_status['wc-status']['item'] == 'unversioned') {
						unset($status[$filename]);
					} else {
						$props = $fstore->svn_get_ariadne_props($svn, $filename);
						echo "<span class='svn_addtemplateline'>Reverting ".$this->path.$props["ar:function"]." (".$props["ar:type"].") [".$props["ar:language"]."] ".( $props["ar:default"] == '1' ? $ARnls["default"] : "")."</span>\n";
						$fstore->svn_revert($svn, $filename);
						$templates[] = $fstore->get_path($svn, $filename);
					}
				}

			}

			$this->call(
				"system.svn.compile.templates.php",
				array(
					'templates' 	=> $templates,
					'fstore'	=> $fstore,
					'svn'		=> $svn
				)
			);
		}
	}
?>
