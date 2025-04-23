<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		$this->resetloopcheck();

		$username = $this->getdata('username');
		$password = $this->getdata('password');
		$message = $this->getdata('message');

		$fstore	= $this->store->get_filestore_svn("templates");
		$svn	= $fstore->connect($this->id, $username, $password);

		$type = $this->getdata('type');
		$language = $this->getdata('language');
		$function = $this->getdata('function');

		$single = false;
		if( $type && $language && $function ) { // single commit
			$single = true;
			$pinp_filename = $type . "." . $function . "." . $language . ".pinp";
			if ( $this->data->config->templates[$type][$function] ?? null ) {
				$default = 1;
			} else {
				$default = 0;
			}
			if ( $this->data->config->privatetemplates[$type][$function] ?? null ) {
				$private = 1;
			} else {
				$private = 0;
			}

			$fileinfo = array();
			$fileinfo[$pinp_filename] = array();
			$fileinfo[$pinp_filename]['ar:type'] = $type;
			$fileinfo[$pinp_filename]['ar:function'] = $function;
			$fileinfo[$pinp_filename]['ar:language'] = $language;
			$fileinfo[$pinp_filename]['ar:default'] = $default;
			$fileinfo[$pinp_filename]['ar:private'] = $private;
			$fstore->svn_add($svn, $pinp_filename);
		} else { // whole list
			$fileinfo = array();
			$fileinfo[''] = array();
			$fileinfo['']['ar:type'] = $this->type;
			$fileinfo['']['ar:name'] = $this->nlsdata->name;

			$pinp = $this->data->config->pinp;
			if($pinp){
				foreach( $pinp as $type => $values ) {
					foreach( $values as $function => $templatelist ) {
						foreach($templatelist as $language => $node) {
							$pinp_filename = $type . "." . $function . "." . $language . ".pinp";
							if ($this->data->config->templates[$type][$function] ?? null) {
								$default = 1;
							} else {
								$default = 0;
							}

							if ($this->data->config->privatetemplates[$type][$function] ?? null) {
								$private = 1;
							} else {
								$private = 0;
							}

							$fileinfo[$pinp_filename] = array();
							$fileinfo[$pinp_filename]['ar:function'] = $function;
							$fileinfo[$pinp_filename]['ar:type'] = $type;
							$fileinfo[$pinp_filename]['ar:language'] = $language;
							$fileinfo[$pinp_filename]['ar:default'] = $default;
							$fileinfo[$pinp_filename]['ar:private'] = $private;
							$fstore->svn_add($svn, $pinp_filename);
						}
					}
				}
			}

			$svn_status = (array)$fstore->svn_status($svn);
			$deleted_templates = $this->data->config->deleted_templates ?? null;
			if ($deleted_templates) {
				foreach ( $deleted_templates as $type => $values) {
					foreach ($values as $function => $templatelist) {
						foreach ($templatelist as $language => $node) {
							$pinp_filename = $type . "." . $function . "." . $language . ".pinp";
							if ($svn_status[$pinp_filename] == 'D') {
								$fileinfo[$pinp_filename] = array();
								if (isset($this->data->config->deleted_templates[$type][$function][$language])) {
									unset($this->data->config->deleted_templates[$type][$function][$language]);
									if (count($this->data->config->deleted_templates[$type][$function])==0) {
										unset($this->data->config->deleted_templates[$type][$function]);
										if (count($this->data->config->deleted_templates[$type])==0) {
											unset($this->data->config->deleted_templates[$type]);
										}
									}
								}
								if (isset($this->data->config->deleted_privatetemplates[$type][$function])) {
									unset($this->data->config->deleted_privatetemplates[$type][$function]);
									if (count($this->data->config->deleted_privatetemplates[$type])==0) {
										unset($this->data->config->deleted_privatetemplates[$type]);
									}
								}
							}
						}
					}
				}
			}
		}

		$result = $fstore->svn_commit($svn, $message, $fileinfo);
		if ($result === false) {
			echo "Commit failed.<br>\n";
			if (count($errs = $fstore->svnstack->getErrors())) {
				foreach ($errs as $err) {
					echo htmlspecialchars($err['message']??'', ENT_QUOTES, 'UTF-8')."<br>\n";
				}
			}
		} elseif( ( $result ?? null ) && ( $pinp ?? null ) && !( $single ?? null ) ) {
			foreach( $pinp as $type => $values ) {
				foreach( $values as $function => $templatelist ) {
					foreach( $templatelist as $language => $node ) {
						$pinp_filename = $type . "." . $function . "." . $language . ".pinp";
						if( !$fstore->exists($this->id, $pinp_filename ) ) {
							$this->resetloopcheck();
							$this->call( "system.delete.layout.phtml", array( "type" => $type, "language" => $language, "function" => $function ) );
						}
					}
				}
			}
		} elseif( $result && $single ) {
			if( !$fstore->exists($this->id, $pinp_filename ) ) {
				$this->call( "system.delete.layout.phtml", array( "type" => $type, "language" => $language, "function" => $function ) );
			}
		} else {
			echo "No changes to commit.<br>\n";
		}
		if( $result ) {
			$this->save();
			echo $result;
		}
		flush();
	}
?>
