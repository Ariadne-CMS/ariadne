<?php
	$ARCurrent->nolangcheck=true;
	if ($this->CheckLogin('read') && $this->CheckConfig()) {
		if (isset($query)) {
			// Master override, allows any query to be run.
		} else {
			$query="object.parent='".AddSlashes($this->path)."'";

			if ($type) {
				$query.=" and object.implements='".AddSlashes($type)."'";
			}
			if ($name) {
				$query.=" and name.value ~= '%".AddSlashes($name)."%'";
			}

			$query.=" order by name.$nls.value, name.none.value";
		}

		if (!$limit) {
			$limit=0;
		}

		if (!$offset) {
			$offset = 0;
		}

		// FIXME: recurse.phtml constructie om niet teveel geheugen
		// te gebruiken kan ook hier worden neergezet, zodat je
		// vanuit willekeurige plekken gewoon sys.objects.list.phtml
		// kan oproepen, ook als het om veel objecten gaat.

		if (!$ARCurrent->arTypeIcons) {
			$this->call('typetree.ini');
		}
		$total=$this->count_find(".", $query);
		$objects = $this->find(".", $query, "system.list.entry.php", "", $limit, $offset);

		$arResult = array(
			'objects' => $objects,
			'total' => $total
		);
	}
?>
