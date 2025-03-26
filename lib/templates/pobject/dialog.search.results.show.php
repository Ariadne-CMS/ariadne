<?php
	$ARCurrent->nolangcheck = true;
	$this->resetloopcheck();
	if( $this->CheckConfig() ) {
		$oddeven = $this->getvar("oddeven");
		$oddeven = ( $oddeven == "odd" ? "even" : "odd" );
		$first = $this->getvar("first");
		$this->putvar("first", false);
?>
<tr class="<?php
	if( $first ) {
		echo " yui-dt-first";
	}
	echo " yui-dt-".$oddeven;
?>">
<td class="yui-dt-sortable yui-dt-first yui-dt-col-name"><?php $this->call("dialog.search.results.show.link.php"); ?></td>
<td class="yui-dt-sortable yui-dt-col-location"><?php $this->get($this->parent, "dialog.search.results.show.link.php"); ?></td>
<td class="yui-dt-sortable yui-dt-col-size" align="right">
<div style="display:none;"><?php printf("%010d", $this->size); ?></div>
<?php
		 	if ($this->size>1024) {
				echo round(intval($this->size)/1024)."&nbsp;KB&nbsp;";
			} else {
				echo intval($this->size)."&nbsp;B&nbsp;";
			}
?></td>
<td class="yui-dt-sortable yui-dt-col-modified"><?php
  $date=getdate($this->lastchanged);
  $now=getdate(time());
  if ($now["year"]!=$date["year"]) {
    echo DateTimeImmutable::createFromFormat('U', $this->lastchanged)->format(' m/Y');
  } else if ($this->lastchanged<(time()-86400)) {
    echo DateTimeImmutable::createFromFormat('U', $mtime)->format(' d/m');
  } else {
    echo DateTimeImmutable::createFromFormat('U', $this->lastchanged)->format(' H:i');
  }
?></td><td "yui-dt-sortable yui-dt-last yui-dt-col-language"><?php
  if (is_array($this->data->nls->list??null)) {
	asort($this->data->nls->list);
    reset($this->data->nls->list);
    foreach( $this->data->nls->list as $key => $value ) {
      echo "<img src=\"".$AR->dir->images."nls/small/".$key.".gif\" alt=\"".htmlentities($value)."\"> ";
    }
  }
?></td>
</tr>
<?php
	}
?>
