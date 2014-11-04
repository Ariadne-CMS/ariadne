<?php
/*
	Workspaces for object data within Ariadne.

	(c) Muze 2011;
*/
	class workspace {
		function getLayer($workspace) {
			$layers = array( // FIXME: should only define this once, not every time function is called.
				"live" => 0,
				"workspace" => 1
			);
			return $layers[$workspace];
		}

		function enabled($path, $workspace="workspace") {
			// Check if the current path has an active workspace. Useful to warn the user that changes will not be directly visible.

			if ($this->store->getLayer($path) == workspace::getLayer($workspace)) {
				return true;
			}
			return false;
		}

		function status($path, $recursive=false, $workspace="workspace") {
			// Get the status of changes for the current path. This checks only the changes for this object, recursion to child objects is handled seperately.
			// If $recursive is true, it will also check for
			// changes in child objects. The resulting array
			// will be the combined result of a logical "OR" (so
			// if any object is moved, moved will be true. If
			// any object has been created, created will be
			// true).

			$context = pobject::getContext();
			$me = $context['arCurrentObject'];

			// FIXME: layerstatus wordt nu van de huidige active layer opgehaald in plaats van $workspace

			$hardlinks = $me->store->checkHardLinks($me->path);

			if ($hardlinks) {
				return array(
					"hardlinks" => true
				);
			}

			$layerstatus = $me->store->getLayerstatus($me->path, false);

			$result = array(
					"update" => false,
					"move" => false,
					"delete" => false,
					"create" => false,
					"overwrite" => false
			);

			if (is_array($layerstatus)) {
				if (!$recursive) {
					if ($layerstatus[$me->path] && is_array($layerstatus[$me->path]['operation'])) {
						foreach ($layerstatus[$me->path]['operation'] as $operation) {
							$result[$operation] = true;
						}
					}
				} else {
					foreach ($layerstatus as $path) {
						if (is_array($layerstatus[$path]['operation'])) {
							foreach ($layerstatus[$me->path]['operation'] as $operation) {
								$result[$operation] = true;
							}
						}
					}
				}
			}

			return $result;
		}

		function diff($path, $recursive=false, $workspace="workspace") {
			// Get the changes for the current path against the current live. If recursive is true, it also returns the changes for the child objects.

			return array(
				"new" => array(
					"path" => "/projects/ariadne-cms/ariadne-cms/images/icons/pgroup.gif/",
					"data" => array(
						"nl" => array(
							"page" => "frop"
						)
					)
				),
				"old" => array(
					"path" => "/projects/ariadne-cms/ariadne-cms/images/ikonen/pgroup.gif/",
					"data" => array(
						"nl" => array(
							"page" => "frup"
						)
					)
				)
			);
		}

		function commit($paths, $workspace="workspace") {
			// Commit the changes that have been made in the active workspace to the actual store.
			// $paths is an array containing the paths that will be commited.
			return true;
		}

		function revert($paths, $workspace="workspace") {
			// Revert/discard the changes in the active workspace.
			// $paths is an array containing the paths that will be reverted.
			return true;
		}

		function activate($path, $workspace="workspace") {
			// Activate the workspace for the current path. All
			// calls on objects will return the workspaced
			// version when this has been called. This should be
			// called from within the workspace loader.
			$context = pobject::getContext();
			$me = $context['arCurrentObject'];

			// FIXME: Checken of de gevraagde workspace wel geconfigged is voor dit pad.
			$layer = workspace::getLayer($workspace);
			if (!isset($layer)) {
				return false;
			} else {
				$me->store->setLayer($layer);
				return true;
			}
		}
	}

	class pinp_workspace {
		function _enabled($path, $workspace="workspace") {
			return workspace::enabled($path, $workspace);
		}
		function _status($path, $recursive=false, $workspace="workspace") {
			return workspace::status($path, $recursive, $workspace);
		}
		function _diff($path, $recursive=false, $workspace="workspace") {
			return workspace::diff($path, $recursive, $workspace);
		}
		function _commit($paths, $workspace="workspace") {
			return workspace::commit($paths, $workspace);
		}
		function _revert($paths, $workspace="workspace") {
			return workspace::revert($paths, $workspace);
		}
		function _activate($path, $workspace="workspace") {
			return workspace::activate($path, $workspace);
		}
	}

?>