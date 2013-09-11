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
			$filter = $this->getvar('filter');

			$filterqueries = array(
				"ctime-year" => "time.ctime > " . (strtotime("today") - 365*24*60*60),
				"ctime-month" => "time.ctime > " . (strtotime("today") - 30*24*60*60),
				"ctime-day" => "time.ctime > " . (strtotime("today") - 24*60*60),
				"mtime-year" => "time.mtime > " . (strtotime("today") - 365*24*60*60),
				"mtime-month" => "time.mtime > " . (strtotime("today") - 30*24*60*60),
				"mtime-day" => "time.mtime > " . (strtotime("today") - 24*60*60)
			);
			
			$orderqueries = array(
				"name" => array(
					"name.$nls.value",
					"name.none.value"
				),
				"ctime" => array(
					"time.ctime",
					"name.$nls.value",
					"name.none.value"
				),
				"path" => array(
					"path",
					"name.$nls.value",
					"name.none.value"
				),
				"mtime" => array(
					"time.mtime",
					"name.$nls.value",
					"name.none.value"
				),
				"priority" => array(
					"priority",
					"name.$nls.value",
					"name.none.value"
				)
			);

			// Aliases
			$orderqueries['modified'] = $orderqueries['mtime'];
			$orderqueries['filename'] = $orderqueries['path'];
			
			if ($filterqueries[$filter]) {
				$query .= " and " . $filterqueries[$filter];
			}

			if (!$orderqueries[$order]) {
				$order = 'name';
			}

			if ($orderqueries[$order]) {
				$directionpart = (strtolower($direction) == 'desc' ? " DESC" : " ASC");
				$querypart = implode($directionpart . ", ", (array)$orderqueries[$order]);
				$query .= " order by " . $querypart . $directionpart;
			}
		}

		// echo $query;

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

		$countQuery = "object.parent='".AddSlashes($this->path)."'";
		if ($filterqueries[$filter]) {
			$countQuery .= " and " . $filterqueries[$filter];
		}

		$foldertotal = $this->count_find(".", $countQuery);

		// If the total is more than 1000 and sanity is set, don't get the list.
		if (!($foldertotal > 1000 && $sanity)) {
			$objects = $this->find(".", $query, "system.list.entry.php", "", $limit, $offset);
		}

		if ($name || $type) {
			$total=$this->count_find(".", $query);
		} else {
			$total = $foldertotal;
		}

		$arResult = array(
			'objects' => $objects,
			'total' => $total
		);
	}
?>
