<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {

		$fstore = $this->store->get_filestore_svn("templates");
        $svn = $fstore->connect($this->id, $repository, $username, $password);

		$linebased = explode("\n", $diff);

		// Index: /home/wouter/svn/ariadne/site/files/templates/72/7/_psite.test.5.html.any.pinp
		// ===================================================================
		// --- /home/wouter/svn/ariadne/site/files/templates/72/7/_psite.test.5.html.any.pinp	(revision 5960)
		// +++ /home/wouter/svn/ariadne/site/files/templates/72/7/_psite.test.5.html.any.pinp	(working copy)
		// @@ -8,7 +8,7 @@
		// -<body>
		// +<body class="body">
		//  <h1>Muze Lib Test 5</h1>

		$status = "";
		$i = 0;
		while( isset($linebased[$i]) ) {
			$line = $linebased[$i];
			$firstchar = substr($line,0,1);
			switch( $firstchar ) {
				case "I": // Index:
					$template = substr($line, strpos($line, " ")+1);
					$props = $fstore->svn_get_ariadne_props($svn, $template);
					if( !ar_error::isError($props) && count($props) ) {
						$line = str_replace($template, $this->path.$props["ar:function"]." (".$props["ar:type"].") [".$props["ar:language"]."] ".($props["ar:default"] == '1' ? $ARnls["default"] : ""), $line );
					}
					$status .= "\n<span class='svndiff_indexline'>".htmlspecialchars($line)."</span>\n";
					$line = $linebased[++$i];
					$status .= "<span class='svndiff_headline'>".htmlspecialchars($line)."</span>\n";
					$line = $linebased[++$i];
					$firstspace = strpos($line, " ")+1;
					$nextspace = strpos($line, "\t", $firstspace);
					$template = substr($line, $firstspace, $nextspace-$firstspace);
					$props = $fstore->svn_get_ariadne_props($svn, $template);
					if( !ar_error::isError($props) && count($props) ) {
						$line = str_replace($template, $this->path.$props["ar:function"]." (".$props["ar:type"].") [".$props["ar:language"]."] ".($props["ar:default"] == '1' ? $ARnls["default"] : ""), $line );
					}
					$status .= "<span class='svndiff_headline'>".htmlspecialchars($line)."</span>\n";
					$line = $linebased[++$i];
					$firstspace = strpos($line, " ")+1;
					$nextspace = strpos($line, "\t", $firstspace);
					$template = substr($line, $firstspace, $nextspace-$firstspace);
					$props = $fstore->svn_get_ariadne_props($svn, $template);
					if( !ar_error::isError($props) && count($props) ) {
						$line = str_replace($template, $this->path.$props["ar:function"]." (".$props["ar:type"].") [".$props["ar:language"]."] ".($props["ar:default"] == '1' ? $ARnls["default"] : ""), $line );
					}
					$status .= "<span class='svndiff_headline'>".htmlspecialchars($line)."</span>\n";
				break;
				case "@": // @@
					$status .= "<span class='svndiff_offsetline'>".htmlspecialchars($line)."</span>\n";
				break;
				case "-": // --- file -diff
					$status .= "<span class='svndiff_removeline'>".htmlspecialchars($line)."</span>\n";
				break;
				case "+": // +++ file +diff
					$status .= "<span class='svndiff_addline'>".htmlspecialchars($line)."</span>\n";
				break;
				default:
					if( strlen($line) ) {
						$status .= "<span class='svndiff_normalline'>".htmlspecialchars($line)."</span>\n";
					}
				break;
			}
			$i++;
		}
		if( $status != "" ) {
			if( $nowrap ) {
				$arResult = $status;
			} else {
				$arResult = "<pre class='svnresult'>".$status."</pre>\n";
			}
		} else {
			$arResult = "";
		}
}
?>
