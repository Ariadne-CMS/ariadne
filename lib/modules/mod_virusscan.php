<?php 

	$VSsupportedscanners = "f-prot";

	function virusscan($filename) {
		global $AR, $VSsupportedscanners;

		$scanner = strtolower($AR->VS->scannername);		
		// Do they want us to scan ?
		if($AR->VS->performscan && (stristr($VSsupportedscanners, $scanner)!=false)) {
			// Yes they do.		
			
			$infected = true;	// We're paranoid; always assume it's infected unless proven otherwise.
			$infectiontype = "an unknown virus infection";
			$path =  $AR->VS->path;
			
			switch($scanner) {
				case "f-prot": $cmd = $path."f-prot";
						exec("$cmd $filename", $output, $ret);
						// process output
						switch($ret) {
							case 0:
							case 1:
							case 2:
							case 5: 
							case 7: // No virusses found or cleaned, however, something MIGHT be wrong with the setup
								$infected = false;
								break;
							case 3:
							case 6:
							case 8: // A virus or something else suspicious was found, perhaps cleaned.
								$infected = true;
								// Find the virus ID
								break;
						} // Switch
						if($infected) {
							// Find the virus ID
							while(list($h, $i) = each($output)) {
								if($j = strstr($i, "Infection: ")) {
									$infectiontype = substr($j, 11);
								}
							}
						}
						break;
			} // Switch
		} else {
			// No they dont
			$infected = false;
			$infectiontype = "Virusscanning is disabled";
		}
		return array($infected, $infectiontype);
	}

	function virusclean($filename) {
		global $AR, $VSsupportedscanners;

		$scanner = strtolower($AR->VS->scannername);	
		// Do they want us to scan ?
		if($AR->VS->performscan && (stristr($VSsupportedscanners, $scanner)!=false)) {
			// Yes they do
			
			$cleaned = false;	// We're paranoid; always assume it failed unless proven otherwise.
			$path =  $AR->VS->path;
			
			switch($scanner) {
				case "f-prot": $cmd = $path."f-prot -disinf -auto";
						exec("$cmd $filename", $output, $ret);
						// process output
						switch($ret) {
							case 0:
							case 1:
							case 2:
							case 3:
							case 5: 
							case 7: 
							case 8: // No virusses found or cleaned, however, something MIGHT be wrong with the setup
								$cleaned = false;
								break;
							case 6: // At least one file was cleaned.
								$cleaned = true;
								break;
						} // Switch
						break;
			} // Switch
		} else {
			// No they dont
			$cleaned = true;
		}
		return $cleaned;
	}

?>