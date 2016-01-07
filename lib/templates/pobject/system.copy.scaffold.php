<?php
	if ( $scaffold && ar::exists($scaffold) ) {
		$scaffoldOb = current(ar::get($scaffold)->call('system.get.phtml'));
		if ( $scaffold ) {
			// copy config from scaffold
			foreach ($scaffoldOb->data->config->pinp as $ttype => $tdata ) {
				foreach ( $tdata as $tfunction => $tlanguages ) {
					foreach( $tlanguages as $tlanguage => $tid ) {
						if ( $scaffoldOb->data->config->privatetemplates[$ttype][$tfunction]) {
							$private = true;
						} else {
							$private = false;
						}
						if ( $scaffoldOb->data->config->templates[$ttype][$tfunction][$tlanguage] ) {
							$default = true;
						} else {
							$default = false;
						}
						$template = $scaffoldOb->call('system.get.layout.phtml', array(
							'type'     => $ttype,
							'function' => $tfunction,
							'language' => $tlanguage
						));
						$this->call('system.save.layout.phtml', array(
							'type'     => $ttype,
							'function' => $tfunction,
							'language' => $tlanguage,
							'default'  => $default,
							'private'  => $private,
							'template' => $template
						));
					}
				}
			}
		}

		// copy all children from scaffold
		$objects = ar::get($scaffold)->find('')->limit(0)->order('object.path ASC')->call('system.get.name.phtml');
		foreach( $objects as $objectpath => $objectname ) {
			$subpath = substr($objectpath, strlen($scaffold));
			ar::get($objectpath)->call('system.copyto.phtml', array(
				'target' => $this->path.$subpath,
				'search' => $scaffold,
				'replace' => $this->path,
				'defaultnls' => $this->data->nls->default
			));
		}
	}
?>