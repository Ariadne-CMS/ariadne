	var viewmode=parent.Get('viewmode');
	var b_offset=0;
	var b_limit=5;
	var b_total=0;
	var b_page=0;
	if (!viewmode) {
		viewmode='list';
	}
	function b_setView(type) {
		viewmode=type;
		top.Set('viewmode',viewmode);
		b_draw();
	}
	function b_setTotal(total) {
		b_total=total;
		if (b_total>b_limit) {
			var pages=(b_total/b_limit);
			pages_select=document.b_pages.b_goto_select;
			for (i=0; i<pages; i++) {
				pages_select.options[i]=new Option(i+1, i+1);
			}
			pages_select.options[b_page].selected=true;
			document.b_pages.style.visibility='inherit';
		}
	}
	function b_show(button) {
		if (document.all) {
			if (document.all[button]) {
				document.all[button].style.visibility='inherit';
			}
		} else if (document.getElementById) {
			if (b_object=document.getElementById(button)) {
				b_object.style.visibility='inherit';
			}
		}
	}
	function b_hide(button) {
		if (document.all) {
			if (document.all[button]) {
				document.all[button].style.visibility='hidden';
			}
		} else if (document.getElementById) {
			if (b_object=document.getElementById(button)) {
				b_object.style.visibility='hidden';
			}
		}
	}
	function b_prev() {
		b_offset-=b_limit;
		if (b_offset<0) {
			b_offset=0;
		}
		b_page-=1;
		b_draw();
	}
	function b_next() {
		b_offset+=b_limit;
		b_page+=1;
		b_draw();
	}
	function b_first() {
		b_offset=0;
		b_page=0;
		b_draw();
	}
	function b_last() {
		b_offset=b_total-(b_total%b_limit);
		if (b_offset<0) {
			b_offset=0;
		}
		b_page=(b_total/b_limit)-1;
		if (b_total%b_limit) {
			b_page+-1;
		}
		b_draw();
	} 
	function b_goto(page) {
		b_page=page;
		b_offset=b_limit*page;
		b_draw();
		return false;
	}
	function b_draw() {
		var newsrc='browse.nav.'+viewmode+'.phtml?'+query+'&limit='+b_limit+'&offset='+b_offset;
		if (!window.archildren) {
			archildren=document.getElementById("archildren");
		} else {
			archildren=window.archildren;
		}
		if (archildren.src) {
			archildren.src=newsrc;
		} else {
			archildren.location=newsrc;
		}
		document.b_pages.b_goto_select.options[document.b_page].selected=true;
	}
