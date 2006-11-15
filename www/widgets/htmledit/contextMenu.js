/*
	Copyright (c) 2002 Jonathan Snook
	http://www.snook.ca/

	This code was developed for people who wish to use a context menu
	with MSHTML. I've matched the method names to be pretty close to
	what they were under the DEC samples from Microsoft.

	You are free to use this code in your application. I simply ask
	that you include this copyright info and send me an e-mail to let
	me know that you're using it. There's no warranty or support
	included with this code.
*/

	/* initalize contextmenu */
		var MENU_SEPARATOR = ""; // Context menu separator
		var ContextMenu = new Array();
		var GeneralContextMenu = new Array();
		var TableContextMenu = new Array();

		var oPopup;
		var oPopupBody;
		var contextCount = 0;
	/* end initalize */
	/* initalize contextmenu */

	/* end initalize */

	/*
		ContextMenuItem() is an object function that stores the string
		to appear in the context menu and the cmdid of the item.
	*/
	function ContextMenuItem(title, cmd){
		this.string = title;
		this.cmd = cmd;
	}

	/*
		contextHilite and contextDelite perform the highlighting in
		the context menu
	*/
	function contextHilite(event){
		event.srcElement.runtimeStyle.backgroundColor = "Highlight";
		if (event.srcElement.state){
			event.srcElement.runtimeStyle.color = "GrayText";
		} else {
			event.srcElement.runtimeStyle.color = "HighlightText";
		}
	}

	function contextDelite(event){
		event.srcElement.runtimeStyle.backgroundColor = "";
		event.srcElement.runtimeStyle.color = "";
	}

	/*
		addContextItem() does the actual work of adding the elements
		to the contextmenu
	*/
	function addContextItem(text, state){
		// reset the popup if redisplaying
		if(contextCount == 0) {oPopupBody.innerHTML = ''}

		var el = oPopup.document.createElement("<div>")
		el.style.cursor = 'default';
		el.style.width = '100%';
		el.style.align = 'center';
		if(text == "") {
			el.innerHTML = '<hr>';
			el.style.padding = '2px';
			el.style.margin = '0px';
			el.style.height = '17px';
			el.style.overflow = 'hidden';
		} else {
			el.innerHTML = text;
			el.style.margin = '0px 1px';
			el.style.padding = '2px 20px';
			el.attachEvent('onmouseover',contextHilite);
			el.attachEvent('onmouseout',contextDelite);
			el.attachEvent('onclick',contextOnclick);
		}
		oPopupBody.appendChild(el);

		if (state || text == "") {
			el.state = true;
			el.style.color="GrayText";
		} else {
			el.style.color="MenuText";
		}

		el.item = contextCount;
		contextCount++;
	}

	/*
		contextOnclick() processes which element in the contextmenu
		was clicked on.
	*/
	function contextOnclick(event){
		if(event.srcElement.state){
			return false; // do nothing
		} else {
			/* process the info:
			   decide what you want to do based on the
			   id passed from event.srcElement.item */
			oPopup.hide();
//			ContextMenu[event.srcElement.item].cmd();
			switch (ContextMenu[event.srcElement.item].cmd) {
				case DECMD_PASTE:
					DECMD_PASTE_onclick();
				break;
				case DECMD_CUT:
					DECMD_CUT_onclick();
				break;
				case DECMD_COPY:
					DECMD_COPY_onclick();
				break;
			}
/*
			switch (ContextMenu[event.srcElement.item].cmdId) {
				case "addImage":
					IMAGE_onclick();
					break;
				case "addLink":
					HYPERLINK_onclick();
					break;
				default:
					document.execCommand(ContextMenu[event.srcElement.item].cmdId);
			}
*/
		}
	}

	/*
		showContextMenu() builds an array of contextmenu items. Like the DEC sample,
		you could have multiple context menus based on (surprise) the context. For
		example, if you clicked in a table, additional context items become visible.
	*/
	function showContextMenu(){
		oPopup = window.createPopup();
		oPopupBody = oPopup.document.body;
		oPopupBody.style.backgroundColor = "threedface";
		oPopupBody.style.border = "outset 2px";
		oPopupBody.style.fontFamily = "Tahoma";
		oPopupBody.style.fontSize = "11px";

		var menuStrings = new Array();
		var menuStates = new Array();

		var idx=0;
		ContextMenu.length = 0;
		contextCount = 0;
		for (i=0; i<GeneralContextMenu.length; i++) {
			ContextMenu[idx++] = GeneralContextMenu[i];
		}
		if (showTableOptions) {
			for (i=0; i<TableContextMenu.length; i++) {
				ContextMenu[idx++] = TableContextMenu[i];
			}
		}

		for (i=0; i<ContextMenu.length; i++) {
			addContextItem(ContextMenu[i].string, false);
		}

		/* display the context menu */

		var iHeight = (contextCount * 17) + 5;
		oPopup.show(tbContentElement.contentWindow.event.clientX+2, tbContentElement.contentWindow.event.clientY+2, 150, iHeight, document.body);
		return false;
	}
