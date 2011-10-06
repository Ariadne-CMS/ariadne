<?php
/*
	Workspaces for object data within Ariadne.	

	(c) Muze 2011;
*/
	class workspace {
		function enabled($path, $workspace="workspace") {
			// Check if the current path has an active workspace. Useful to warn the user that changes will not be directly visible.
			return true;
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
				"modified" => false,
				"moved" => true,
				"deleted" => false,
				"created" => false 
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
?>