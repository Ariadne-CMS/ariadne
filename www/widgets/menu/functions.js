// default values for opening windows
	windowprops=new Array();
	windowprops['common']='resizable';
	windowprops['full']='directories,location,menubar,status,toolbar,resizable,scrollbars';
	windowprops['object_fs']=windowprops['common']+',height=100,width=400';
	windowprops['object_new']=windowprops['common']+',height=275,width=450';
	windowprops['edit_find']=windowprops['common']+',height=400,width=500';
	windowprops['edit_preferences']=windowprops['common']+',height=400,width=500';
	windowprops['edit_object_data']=windowprops['common']+',height=275,width=450';
	windowprops['edit_object_cache']=windowprops['common']+',height=250,width=250';
	windowprops['edit_object_layout']=windowprops['common']+',height=400,width=700';
	windowprops['edit_object_custom']=windowprops['common']+',height=210,width=400';
	windowprops['edit_object_shortcut']=windowprops['common']+',height=250,width=450';
	windowprops['edit_object_grants']=windowprops['common']+',height=300,width=550';
	windowprops['edit_object_types']=windowprops['common']+',height=150,width=250';
	windowprops['edit_object_nls']=windowprops['common']+',height=250,width=400';
	windowprops['edit_priority']=windowprops['common']+',height=150,width=250';
	windowprops['view_fonts']=windowprops['common']+',height=300,width=450';
	windowprops['help']=windowprops['common']+',height=350,width=450';
	windowprops['help_about']=windowprops['common']+',height=275,width=500';
	windowprops['_new']=windowprops['full'];

	function viewpath(path) {
		test=new String(path);
		if (test.substr(test.length-1)!='/') {
			test+='/';
		}
		re=/\/+/g
		test=test.replace(re,'/');
		parent.View(test);
		return false;
	}

	function arshow(windowname, link) {
		properties=windowprops[windowname];

		/* FIXME: doesn't work without frames on mozilla
		windowsize=parent.Get(windowname);
		if (windowsize) {
			alert('windowsize='+windowsize);
			properties=properties+','+windowsize;
		}
		*/
		workwindow=window.open(link, windowname, properties);
		workwindow.focus();
	}

	function artoggleexplorerbar() {
		if (document.all) {
			expl_icon=document.all['explorerbar_icon'];
			treestatus=parent.toggletree();
			if (treestatus=='hidden') {
				expl_icon.className='unselectedOption';
			} else {
				expl_icon.className='selectedOption';
			}
		}
	}

	function setView(type) {
		parent.Set('viewmode',type);
		if (!window.archildren) {
			archildren=document.getElementById("archildren");
		} else {
			archildren=window.archildren;
		}
		if (archildren.src) {
			archildren.src='browse.nav.'+type+'.phtml';
		} else {
			archildren.location.href='browse.nav.'+type+'.phtml';
		}
	}

	function editobject() {
		newwindow=window.open('edit.object.data.phtml', 'newwindow', windowproperties);
		newwindow.focus();
		return false;
	}