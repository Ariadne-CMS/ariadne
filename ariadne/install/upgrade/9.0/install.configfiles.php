<pre>
<?php
	$ARCurrent->nolangcheck = true;
	$ARCurrent->options["verbose"]=true;
	$ARCurrent->AXAction == "import";

	// become admin
	$AR->user=new baseObject;
	$AR->user->data=new baseObject;
	$AR->user->data->login=$ARLogin="admin";

	$ax_config["writeable"]=false;
	$ax_config["database"]="../packages/base.ax";
	echo "ax file (".$ax_config["database"].")\n";

	$inst_store = $ax_config["dbms"]."store";
	$axstore=new $inst_store("", $ax_config);

	// test if the base lib is available, die if not
	if(!ar::exists('/system/lib/muze/cms/base/')) {
		echo "muze libs are not yet available, refusing upgrade\n";
		echo "Please manual import  /system/lib/muze/ from www/install/packages/base.ax and continue upgrade after that\n";
		die("could not complete upgrade");
	}

	function importTemplate($cpath,$args){
		global $store,$axstore;
		$templatesrc = current($axstore->call('system.get.template.php',$args, $axstore->get($cpath)));
		$templatedst = current(  $store->call('system.get.template.php',$args,   $store->get($cpath)));

		if(!$store->exists($cpath)) {
			// cannot install config on non-existing path
			print "$cpath doesn't exists, skipping\n";
		}

		if(!ar_error::isError($templatesrc)) {
			if(ar_error::isError($templatedst)) { // error means no template found
				// save template
				$args['template'] = $templatesrc;
				ar::get($cpath)->call('system.save.layout.phtml',$args);
			} else {
				print "Already a configfile defined on $cpath, please merge the following in your configfile ".$args['function']."\n";
				print $templatesrc."\n";
			}
		} else {
			print "Error src template not found on $cpath \n";
		}
	}

	$args = array ('type' => 'pdir',     'function' => 'config.ini','language' => 'any', 'default' => false);
	$cpath = '/';
	importTemplate($cpath,$args);

	$args = array ('type' => 'pobject',  'function' => 'typetree.ini','language' => 'any', 'default' => true);
	$cpath = '/';
	importTemplate($cpath,$args);

	$args = array ('type' => 'pproject', 'function' => 'config.ini','language' => 'any', 'default' => false);
	$cpath = '/projects/';
	importTemplate($cpath,$args);

?>
</pre>
