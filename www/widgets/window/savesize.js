  window.onunload=arSaveSize;
  function arSaveSize() {
    dprops='';
    if (document.all) {
      temp=window.document.body;
      dheight=temp.clientHeight-4;
      dwidth=temp.clientWidth-4;
      dprops='height='+dheight+',width='+dwidth;
    } else {
      dheight=window.outerHeight;
      dwidth=window.outerWidth;
      dleft=window.screenX;
      dtop=window.screenY;
      dprops='height='+dheight+',width='+dwidth+',top='+dtop+',left='+dleft;
    }
    if (top.window.opener && !top.window.opener.closed) {
      if (top.window.opener.top.Set) {
        window.opener.top.Set(window.name, dprops);
      } else if (top.window.opener.top.window.opener && 
                !top.window.opener.top.window.opener.closed &&
                 top.window.opener.top.window.opener.top.Set) {
        top.window.opener.top.window.opener.top.Set(window.name, dprops);
      }
	}
    return true;
  }
