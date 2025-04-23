<?php
	$ARCurrent->nolangcheck=true;
	if (!$this->validateFormSecret()) {
		error($ARnls['ariadne:err:invalidsession']);
		exit;
	}
//	if ($this->CheckLogin('config') && $this->CheckConfig() && $this->can_mogrify() ) {
            if ($this->getvar('targets')) {
                    $targets = $this->getvar("targets");
            } else {
                   $targets = array($this->path);
            }
?>

	<div id="mogrify"><?php echo $ARnls["mogrifying"] . " " . ( $current_object_path ?? "" ); ?></div> <!--hier iets aan aanpassen voor current_object_path?-->
<?php
            foreach ($targets as $target) {
                $targetob = current($this->get($target, "system.get.phtml"));

                if ($targetob->CheckSilent('config') && $targetob->CheckConfig() && $targetob->can_mogrify() ) {
                        $targetob->mogrify( $targetob->id, $this->getdata('type'));
                }
		if ($this->error) {
			echo '<div class="error">'.$this->error.'</div>';
		}
            }
?>	<div class="buttons">
	<div class="left">
	<input unselectable="on" type="submit" name="wgWizControl" class="wgWizControl" onClick="document.wgWizForm.wgWizAction.value='cancel';" value="<?php echo $ARnls['cancel']; ?>">
	</div>
	</div>
	<?php if (!$this->error) { ?>
		<script type="text/javascript">
			if ( window.opener && window.opener.muze && window.opener.muze.dialog ) {
				window.opener.muze.dialog.callback( window.name, 'mogrified', {
					'path': '<?php echo $this->path; ?>'
				});
			} else  {
				// backward compatibility with pre muze.dialog openers
				if ( window.opener && window.opener.muze && window.opener.muze.ariadne ) {
					window.opener.muze.ariadne.explore.tree.refresh('<?php echo $this->path; ?>');
					window.opener.muze.ariadne.explore.view('<?php echo $this->path; ?>');
				}
				window.close();
			}
		</script>
	<?php } ?>
<?php
//	}
?>
