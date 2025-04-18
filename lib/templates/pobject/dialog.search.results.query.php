<?php
	$ARCurrent->nolangcheck=true;
	$arResult = "";
	if ($this->CheckSilent("read") && $this->CheckConfig()) {
		$arPath = $this->getvar("arPath", "none");
		if( !($arPath && $this->exists($arPath))) {
			$arPath = $this->path;
		}

		$query = "";
		if( ($context??null) == 1 || !($query = $this->getvar("query"))) {
			$searchname = $this->getvar("searchname");
			if( $searchname ) {
				$query .= "name.value ~= '%".AddSlashes($searchname)."%'";
			}
			$searchtext = $this->getvar("searchtext");
			if( $searchtext ) {
				if( $query != "" ) {
					$query .= " and ";
				}
				$query .=  " text.value ~= '%".AddSlashes($text)."%'";
			}
			$arimplements = $this->getvar("arimplements");
			if( $arimplements ) {
				if( $query != "" ) {
					$query .= " and ";
				}
				$query .= " object.type='".AddSlashes($arimplements)."'";
			}
		}
		$arResult = $query;
	}
?>
