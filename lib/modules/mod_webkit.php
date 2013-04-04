<?php
	/*
	
	*/

	class pinp_webkit {

		function _toPng($url) {
			return webkit::toPng($url);
		}

	}

	class webkit {

		function toPng( $url ) {
			global $AR;
			$image = false;
			if ( $AR->Webkit2png->path ) {
				$url = ar::url($url);
				if ( $url->host ) { // only allow remote fetches
					$cmd = $AR->Webkit2png->xvfbPath . ' ' . $AR->Webkit2png->xvfbOptions 
					. ' ' . $AR->Webkit2png->path . ' ' . $AR->Webkit2png->options . ' ' . escapeshellarg( (string) $url ); 
					//echo $cmd;
					$image = shell_exec( $cmd );
				}
			}
			return $image;
		}

	}

?>