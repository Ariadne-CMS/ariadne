<?php
	$ARCurrent->nolangcheck=true;
	// FIXME: make strict
	if ($this->CheckSilent("read") && $this->CheckConfig()) {
?>
	<fieldset id="data">
	<legend><?php printf($ARnls["err:cannotlockobject"],$this->path); ?></legend><img src="<?php echo $AR->dir->images; ?>dot.gif" alt="" width="1" height="1"><br>
	<table border="0" align="center" width="90%">
	<tr>
		<th align="left" width="400">
			<?php echo $ARnls["path"]; ?>
		</th><th align="left">
			<?php echo $ARnls["login"]; ?>
		</th><th align="left">
			<?php echo $ARnls["expires"]; ?>
		</th>
	</tr>
	<?php
		if ($locklist=$this->store->mod_lock->locklist) {
			while ((list($key, $lock)=each($locklist))) {
				echo "<tr><td>".$lock["path"]."</td>";
				echo "<td>".$lock["identity"]."</td>";
				echo "<td><nobr>".strftime("%d-%m %H:%M:%S",$lock["release"])."</nobr></td></tr>\n";
			}
		}
	?>
	</table>
	</fieldset>
<?php
	}
?>
