  window.onunload=arSaveSize;
  function arSaveSize() {
    dprops='';
    if (document.all) {
      temp=top.window.document.body;
      dheight=temp.clientHeight-4;
      dwidth=temp.clientWidth-4;
      dprops='height='+dheight+',width='+dwidth;
    } else {
      dheight=top.window.outerHeight;
      dwidth=top.window.outerWidth;
      dleft=top.window.screenX;
      dtop=top.window.screenY;
      dprops='height='+dheight+',width='+dwidth+',top='+dtop+',left='+dleft;
    }
	try {

    if (top.window.opener && !top.window.opener.closed) {
      if (top.window.opener.top.Set) {
        top.window.opener.top.Set(top.window.name, dprops);
      /*
        FIXME: this somehow breaks in IE5.0, maybe others too 
		} else if (top.window.opener.top.window.opener && 
                !top.window.opener.top.window.opener.closed &&
                 top.window.opener.top.window.opener.top.Set) {
        top.window.opener.top.window.opener.top.Set(window.name, dprops);
      */
      }
	}

	} catch(e) {
	}
    return true;
  }
