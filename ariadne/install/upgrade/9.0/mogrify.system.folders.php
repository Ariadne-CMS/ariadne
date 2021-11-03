<pre>
<?php
$importpath = false;

function mogrifyDir($path,$type) {
	echo "== mogrify $path to $type\n";
	$res = current(ar::get($path)->call('system.get.phtml'));
	if($res && $res->type == "pdir") {
		print "Oldtype  is pdir, mogrify to new type $type\n";
		$res = ar::get($path)->call('system.mogrify.phtml',array('type' => $type));
	}
}

mogrifyDir('/system/profiles/','pdir.profiles');
mogrifyDir('/system/users/',   'pdir.users');
mogrifyDir('/system/groups/',  'pdir.groups');
mogrifyDir('/system',  'pdir.system');
?>

</pre>

