<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {

		if (is_array($templates)) {
			foreach ($templates as $filename) {

				$exists = $fstore->exists($this->id,$filename);
				$pinp = substr($filename,-5);
				if (!$exists || $pinp != ".pinp") {
					continue; // not a template
				}

				$template = $fstore->read($this->id,$filename);

				$meta = array();
				$meta['ar:default']  = $fstore->svn_propget($svn, "ar:default", $filename);
				$meta['ar:type']     = $fstore->svn_propget($svn, "ar:type", $filename);
				$meta['ar:function'] = $fstore->svn_propget($svn, "ar:function", $filename);
				$meta['ar:language'] = $fstore->svn_propget($svn, "ar:language", $filename);
				$meta['ar:private']  = $fstore->svn_propget($svn, "ar:private", $filename);

				if($meta['default'] == '1') {
					$meta['default'] = 1;
				}
				if($meta['private'] == '1') {
					$meta['private'] = 1;
				}

				//echo "Meta information:\n";
				//echo "ar:default [" . $meta['ar:default'] . "]\n";
				//echo "ar:type [" . $meta['ar:type'] . "]\n";
				//echo "ar:function [" . $meta['ar:function'] . "]\n";
				//echo "ar:language [" . $meta['ar:language'] . "]\n";

				$this->error = null;

				$this->call("system.save.layout.phtml", array(
							"template" => $template,
							"default"  => $meta['ar:default'],
							"type"     => $meta['ar:type'],
							"function" => $meta['ar:function'],
							"language" => $meta['ar:language'],
							"private"  => $meta['ar:private']
							));

				if ($this->error) {
					echo "\nError compiling ".$this->path.$meta['ar:function']." (".$meta['ar:type'].") [".$meta['ar:language']."] ".($meta['ar:default'] == '1' ? $ARnls['default'] : "")."\n";
					echo $this->error."\n\n";
				}
			}
		}
	}
?>
