<?php
	$ARCurrent->nolangcheck=true;
	$target = $this->store->make_path( $this->parent, $this->data->path );
	if ( $this->exists( $target ) ) {
		$arResult = current( $this->get( $target, 'system.list.objects.php', $arCallArgs ) );
	}
?>
