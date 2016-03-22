<?php
    if ( $this->data->path && ar::exists($this->data->path) ) {
    	ar::get($this->data->path)->call("explore.browse.php", $arCallArgs);
    }
?>
