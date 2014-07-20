<?php
/******************************************************************
  upgrades filestore data of file 
 ******************************************************************/

if ($this->CheckLogin("admin") && $this->CheckConfig())
{

	$filestore=$this->store->get_filestore("files");

	// list files
	$files=$filestore->ls($this->id);

	if( $files === false ) {
		$files = array();
	}

	// sort files on path length
	usort($files, function($a,$b) {
			$alen = strlen($a);
			$blen = strlen($b);
			return $alen > $blen;
			});

	$filedata = array();
	$defaultnls = $data->nls->default;

	while ( $file = array_shift($files) )
	{
		if ( ! preg_match ('/^[a-z]{2}_.+$/',$file )) {
			$filedata[$file] = array();
			foreach ($files as $key => $nlsfile)
			{
				if ( preg_match ('/^.._'.preg_quote($file).'$/',$nlsfile ))
				{
					$filedata[$file][] = $nlsfile;
					unset($files[$key]);
				}
			}
		}
	}

	if ( count($filedata) == 0 ) {
		$ARResult = "0";
	} else {
		$ARResult = "0";
		foreach ($filedata as $base => $data)
		{
			$ARResult++;
			// test if default nls is available
			if ( in_array($defaultnls.'_'.$base, $data) )
			{
				$filestore->remove($this->id,$base);
			} else {
				$filestore->move($this->id,$base,$this->id,$defaultnls.'_'.$base);
			}
		}
	}
}

?>