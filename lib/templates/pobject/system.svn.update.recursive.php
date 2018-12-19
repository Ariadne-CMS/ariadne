<?php
	$ARCurrent->nolangcheck = true;

	if ($this->CheckLogin("layout") && $this->CheckConfig()) {
		set_time_limit(0);
		$this->resetloopcheck();

		$fstore = $this->store->get_filestore_svn("templates");
		$svn    = $fstore->connect($this->id, $this->getdata("username"), $this->getdata("password"));

		$svn_info = $fstore->svn_info($svn);
		$stored_repository = rtrim($svn_info['url'], "/") . "/";
		$revision = $this->getdata('revision');
		$repository = $this->getdata('repository');

		$repoPath = $this->getdata("repoPath");

		if ($repoPath) {
			$repo_subpath = substr($this->path, strlen($repoPath));
			$repository = rtrim($repository, "/") . "/" . $repo_subpath;
		} else {
			$repository = $stored_repository;
		}

		if (!$svn_info) {
			echo "\n<span class='svn_error'>" . $this->path . ": is not in SVN.</span>\n";
			flush();
		} else if (($repository != $stored_repository) && $revision) {
			echo "Checked repo: [$repository] [$stored_repository] rev $revision";
			echo "\n<span class='svn_error'>" . $this->path . ": " . $ARnls['err:svn:leaving_recurse_tree'] . "</span>\n";
			flush();
		} else {
			// we really need to update this, call the update template
			$result = $this->call('system.svn.update.php', $arCallArgs);

			// Run update on the existing subdirs.
			$arCallArgs['repoPath'] = $this->path;
			$arCallArgs['repository'] = $repository;
			$arCallArgs['revision'] = $revision;

			$this->ls($this->path, "system.svn.update.recursive.php", $arCallArgs);

			// Create the dirs, restore them if needed.
			$dirlist = $fstore->svn_list($svn, $revision);
			if ($dirlist) {
				$arCallArgs['dirlist'] = $dirlist;
				$arCallArgs['svn'] = $svn;
				$arCallArgs['fstore'] = $fstore;
				$arCallArgs['repository'] = $repository;
				$this->call("system.svn.checkout.dirs.php", $arCallArgs);
			}
			flush();
		}
	}
?>
