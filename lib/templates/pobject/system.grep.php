<?php   
	if ( $this->CheckLogin("layout") && $this->CheckConfig() ) {
		$grepresults = [];
		if ( $search != "" ) {
			$filestore      = $this->store->get_filestore("templates");
			$templates_path = $filestore->make_path($this->id);
			$esc_search     = escapeshellarg($search);
			$greps          = [];


			$result         = exec($AR->Grep->path." ".$AR->Grep->options." $esc_search $templates_path*.pinp", $greps);
			$grepresults    = [];
			foreach ($greps as $grep) {
				list($file, $linenr, $line) = explode(":", $grep, 3);
				$file = substr($file, strrpos($file, '/'));
				$file = substr($file, 2);
				$file = substr($file, 0, strrpos($file, '.'));
				if( !is_array($grepresults[$file])) {
					$grepresults[$file] = [];
				}
				$grepresults[$file][] = [ 'line' => $linenr, 'match' => $line ];
			}
		}
		$arResult = $grepresults;
	}
?>