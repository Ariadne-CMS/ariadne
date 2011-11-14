<?php
/*
	Workspaces for object data within Ariadne.	

	(c) Muze 2011;
*/
	class workspace {
		var $layers = array(
			"live" => 0,
			"workspace" => 1
		);

		function enabled($path, $workspace="workspace") {
			// Check if the current path has an active workspace. Useful to warn the user that changes will not be directly visible.

			$context = pobject::getContext();
			$me = $context['arCurrentObject'];
			$config = $me->loadUserConfig();
			if ($config['workspace'] == $workspace) {
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

			return array(
				"modified" => false, // combination of 'update' and 'overwrite'
				"moved" => false,
				"deleted" => false,
				"created" => true
			);
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