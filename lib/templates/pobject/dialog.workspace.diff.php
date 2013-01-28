<?php
        global $AR;
        require_once($AR->dir->install."/lib/ar/beta/diff.php");

	$ARCurrent->nolangcheck=true;
	function objectDiff($object1, $object2, $prefix="") {
		foreach ($object1 as $key => $value) {
			if (is_string($value)) {
				if (is_array($object1)) {
					$diff = (string)ar_beta_diff::diff(htmlentities($object2[$key]), htmlentities($value));

					if ($diff) {
						$result .= "<h2>" . $prefix . "[" . $key . "]</h2>";
						$result .= $diff;
						// $result .= " = [" . $value . "]:[" . $object2[$key] . "]<br>";
						unset($diff);
					}

				} elseif (is_object($object1)) {
					$diff = (string)ar_beta_diff::diff(htmlentities($object2->{$key}), htmlentities($value));

					if ($diff) {
						$result .= "<h2>" . $prefix . "->" . $key . "</h2>";
						$result .= $diff;
						unset($diff);
					}
					//	$result .= " = [" . $value . "]:[" . $object2->{$key} . "]<br>";
				}
			} elseif (is_array($value)) {
				if (is_array($object1)) {
					$result .= objectDiff($object1[$key], $object2[$key], $prefix . "[" . $key . "]");
				} elseif (is_object($object1)) {
					$result .= objectDiff($object1->{$key}, $object2->{$key}, $prefix . "[" . $key . "]");
				}
			} elseif (is_object($value)) {
				if (is_array($object1)) {
					$result .= objectDiff($value, $object2[$key], $prefix . "->" . $key);
				} elseif (is_object($object1)) {
					$result .= objectDiff($value, $object2->{$key}, $prefix . "->" . $key);
				}
			}
 		}

		return $result;
	}

	if ($this->CheckLogin("edit") && $this->CheckConfig()) {

		$workspace = $this->getdata('workspacepath');

		if (is_array($workspace)) {
			echo ar_beta_diff::style();

			foreach ($workspace as $path => $layer) {
				$this->store->setLayer($layer, $this->path);
				$ob1 = current($this->get($path, "system.get.phtml"));
				$this->store->setLayer(0, $this->path);
				$ob2 = current($this->get($path, "system.get.phtml"));

				echo "<h1>" . $ob1->nlsdata->name . "</h1>";
				echo objectDiff($ob1->data, $ob2->data, "data");
			}
		}
		// $this->call("window.close.objectadded.js");
	}
?>