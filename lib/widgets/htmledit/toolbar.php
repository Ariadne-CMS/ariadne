<?php
	include($this->store->get_config('code').'nls/ieedit.'.$this->nls);
	if (!$wgHTMLEditSaveTemplate) {
		$wgHTMLEditSaveTemplate="user.edit.save.html";
	}
?><!-- Copyright 2000 Microsoft Corporation. All rights reserved. -->
<!-- Author: Steve Isaac, Microsoft Corporation -->
<!--This demo shows how to host the DHTML Editing component on a Web page. -->
<!-- 2002, Muze: extensive changes to use the toolbar on contentEditable content --> 
<html>
<head>
<META content="text/html; charset=UTF-8" http-equiv=Content-Type>
<title>Edit <?php echo $path.$file; ?></title>

<!-- Styles -->
<link REL="stylesheet" TYPE="text/css" HREF="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/Toolbars/toolbars.css">

<!-- Script Functions and Event Handlers -->
<script LANGUAGE="JavaScript" SRC="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/Inc/dhtmled.js">
</script>

<script LANGUAGE="JavaScript" SRC="<?php echo $AR->dir->www; ?>widgets/compose/compose.js">
</script>

<script LANGUAGE="JavaScript" SRC="<?php echo $AR->dir->www; ?>widgets/htmledit/tableEditor.js">
</script>

<script LANGUAGE="JavaScript" SRC="<?php echo $AR->dir->www; ?>widgets/htmledit/contextMenu.js">
</script>

<script ID="editorSettings" LANGUAGE="JavaScript">
	var buttons_disabled=new Array();
	var tbContentEditOptions=new Array();
	var wgSaveTmpl='<?php echo $wgHTMLEditSaveTemplate; ?>';
<?php
	// load editor.ini, in case the editor is started directly, not through the 
	// js.html file
	$options=$this->call("editor.ini");

	function make_ini_options($name, $option) {
		if (is_array($option)) {
			reset($option);
			echo "	$name = new Array();\n";
			while (list($key, $value)=each($option)) {
				make_ini_options($name."[\"$key\"]", $value);
			}
		} else if (is_string($option)) {
			echo "	$name = \"".AddCSlashes($option, ARESCAPE)."\";\n";
		} else {
			echo "	$name = ".(int)$option.";\n";
		}
	}

	echo "var ";
	make_ini_options("tbContentEditOptions", $options);
?>
	if (tbContentEditOptions["disabled"]) {
		var temp=tbContentEditOptions["disabled"].split(":");
		for (i=0; i<temp.length; i++) {
			if (temp[i]) {
				buttons_disabled[temp[i]]=1;
			}
		}
	}
</script>

<script ID="clientEventHandlersJS" LANGUAGE="javascript">
<!--
//
// Constants
//
var MENU_SEPARATOR = ""; // Context menu separator

//
// Globals
//

var docComplete = false;
var initialDocComplete = false;

var QueryStatusToolbarButtons = new Array();
var CommandCrossReference = new Array();
var tbContentElement;
var tEdit=null;

//
// Utility functions
//

// Constructor for custom object that represents a QueryStatus command and 
// corresponding toolbar element.
function QueryStatusItem(command, element) {
	this.command = command;
	this.element = element;
}

function setSelection(dir) {
	var tr=document.body.createTextRange();
	tr.collapse(dir);
	tr.select();
	KeepState.SaveSelection();
}

function setFormat(command, value) {
	var sel=KeepState.GetSelection();
	var type=sel.type;
	var target = (type == "None" ? tbContentElement.contentWindow.document : sel)
	target.execCommand(command, false, value);
	KeepState.RestoreSelection();
	return true;
}

function getBlock(el) {
	var BlockElements="|H1|H2|H3|H4|H5|H6|P|PRE|LI|TD|DIV|BLOCKQUOTE|DT|DD|TABLE|HR|IMG|";
	while ((el!=null) && (BlockElements.indexOf("|"+el.tagName+"|")==-1)) {
		el=el.parentElement;
	}
	return el;
}


//
// KeepState object

function StateObject() {
	this.name='KeepState';
	this.selection=null;
	this.GetSelection=state_getSelection;
	this.SaveSelection=state_saveSelection;
	this.RestoreSelection=state_restoreSelection;
}


function state_getSelection() {
	var sel=this.selection;
	if (!sel) {
		sel=tbContentElement.contentWindow.document.selection.createRange();
		sel.type=tbContentElement.contentWindow.document.selection.type;
	}
	return sel;
}

function state_saveSelection() {
	this.selection=tbContentElement.contentWindow.document.selection.createRange();
	if (!this.selection || (this.selection.parentElement && this.selection.parentElement() && 
				 !(this.selection.parentElement() == tbContentElement.contentWindow.document.body || tbContentElement.contentWindow.document.body.contains(this.selection.parentElement() ) ) ) ) {
		this.selection=tbContentElement.contentWindow.document.body.createTextRange();
		this.selection.collapse(false);
		this.selection.type="None";
	} else {
		this.selection.type=tbContentElement.contentWindow.document.selection.type;
	}

}

function state_restoreSelection() {
	if (this.selection) {
		this.selection.select();
	}
}


function init_cssStyle() {
	var inline = tbContentEditOptions['css']['inline'];
	var i=0;
	for (var istyle in inline) {
		cssStyle.options[i] = new Option(inline[istyle], istyle);
		i++;
	}
}

function init_ParagraphStyle() {
	var block = tbContentEditOptions['css']['block'];
	var i=0;
	for (var istyle in block) {
		ParagraphStyle.options[i] = new Option(block[istyle], istyle);
		i++;
	}
}


var KeepState;
//
// Event handlers
//
function tableEdit_onMouseMove() {
  // fixme: add even handlers to table edit class instead of this
  if (tEdit) {
    tEdit.changePos();
    tEdit.resizeCell();
  }
}

function tableEdit_onKeyUp() {
  var startInTable=showTableOptions;
  tbContentElement_DisplayChanged();
  if (tEdit && (showTableOptions || startInTable)) {
    tEdit.setTableElements();
    tEdit.repositionArrows();
  }
}

function tableEdit_onMouseUp() {
  var startInTable=showTableOptions;
  tbContentElement_DisplayChanged();
  if (tEdit && (showTableOptions || startInTable)) {
    tEdit.setTableElements();
    tEdit.stopCellResize(false);
  }
}

function tableEdit_onScroll() {
  if (tEdit) {
    tEdit.repositionArrows();
  }
}

function window_onload() {
	tbContentElement=document.getElementById('tbContentElement');
	if (navigator.appName.indexOf("Microsoft")!=-1) {
		// Initialze QueryStatus tables. These tables associate a command id with the
		// corresponding button object. Must be done on window load, 'cause the buttons must exist.
		QueryStatusToolbarButtons[0] = new QueryStatusItem("Bold", document.body.all["DECMD_BOLD"]);
		QueryStatusToolbarButtons[1] = new QueryStatusItem("Copy", document.body.all["DECMD_COPY"]);
		QueryStatusToolbarButtons[2] = new QueryStatusItem("Cut", document.body.all["DECMD_CUT"]);
		QueryStatusToolbarButtons[3] = new QueryStatusItem("CreateLink", document.body.all["DECMD_HYPERLINK"]);
		QueryStatusToolbarButtons[4] = new QueryStatusItem("Indent", document.body.all["DECMD_INDENT"]);
		QueryStatusToolbarButtons[5] = new QueryStatusItem("Italic", document.body.all["DECMD_ITALIC"]);
		QueryStatusToolbarButtons[6] = new QueryStatusItem("JustifyLeft", document.body.all["DECMD_JUSTIFYLEFT"]);
		QueryStatusToolbarButtons[7] = new QueryStatusItem("JustifyCenter", document.body.all["DECMD_JUSTIFYCENTER"]);
		QueryStatusToolbarButtons[8] = new QueryStatusItem("JustifyRight", document.body.all["DECMD_JUSTIFYRIGHT"]);
		QueryStatusToolbarButtons[9] = new QueryStatusItem("InsertOrderedList", document.body.all["DECMD_ORDERLIST"]);
		QueryStatusToolbarButtons[10] = new QueryStatusItem("Outdent", document.body.all["DECMD_OUTDENT"]);
		QueryStatusToolbarButtons[11] = new QueryStatusItem("Paste", document.body.all["DECMD_PASTE"]);
		QueryStatusToolbarButtons[12] = new QueryStatusItem("Redo", document.body.all["DECMD_REDO"]);
		QueryStatusToolbarButtons[13] = new QueryStatusItem("Underline", document.body.all["DECMD_UNDERLINE"]);
		QueryStatusToolbarButtons[14] = new QueryStatusItem("Undo", document.body.all["DECMD_UNDO"]);
		QueryStatusToolbarButtons[15] = new QueryStatusItem("InsertUnorderedList", document.body.all["DECMD_UNORDERLIST"]);
		// Initialize cross reference
		CommandCrossReference["Bold"]					= DECMD_BOLD;
		CommandCrossReference["Copy"] 				= DECMD_COPY;
		CommandCrossReference["Cut"] 					= DECMD_CUT;
		CommandCrossReference["CreateLink"]	 		= DECMD_HYPERLINK;
		CommandCrossReference["Indent"] 				= DECMD_INDENT;
		CommandCrossReference["Italic"] 				= DECMD_ITALIC;
		CommandCrossReference["JustifyLeft"] 			= DECMD_JUSTIFYLEFT;
		CommandCrossReference["JustifyCenter"] 		= DECMD_JUSTIFYCENTER;
		CommandCrossReference["JustifyRight"] 		= DECMD_JUSTIFYRIGHT;
		CommandCrossReference["InsetOrderedList"] 	= DECMD_ORDERLIST;
		CommandCrossReference["Outdent"] 				= DECMD_OUTDENT;
		CommandCrossReference["Redo"] 				= DECMD_REDO;
		CommandCrossReference["Underline"] 			= DECMD_UNDERLINE;
		CommandCrossReference["Undo"] 				= DECMD_UNDO;
		CommandCrossReference["InsertUnorderedList"] 	= DECMD_UNORDERLIST;

		// Initialize the context menu arrays.
		GeneralContextMenu[0] = new ContextMenuItem("<?php echo $ARnls["e_cut"]; ?>", DECMD_CUT);
		GeneralContextMenu[1] = new ContextMenuItem("<?php echo $ARnls["e_copy"]; ?>", DECMD_COPY);
		GeneralContextMenu[2] = new ContextMenuItem("<?php echo $ARnls["e_paste"]; ?>", DECMD_PASTE);

		TableContextMenu[0] = new ContextMenuItem(MENU_SEPARATOR, 0);
		TableContextMenu[1] = new ContextMenuItem("<?php echo $ARnls["e_insertrow"]; ?>", DECMD_INSROW_onclick);
		TableContextMenu[2] = new ContextMenuItem("<?php echo $ARnls["e_deleterows"]; ?>", DECMD_DELROW_onclick);
		TableContextMenu[3] = new ContextMenuItem(MENU_SEPARATOR, 0);
		TableContextMenu[4] = new ContextMenuItem("<?php echo $ARnls["e_insertcol"]; ?>", DECMD_INSCOL_onclick);
		TableContextMenu[5] = new ContextMenuItem("<?php echo $ARnls["e_deletecols"]; ?>", DECMD_DELCOL_onclick);
		TableContextMenu[6] = new ContextMenuItem(MENU_SEPARATOR, 0);
		TableContextMenu[7] = new ContextMenuItem("<?php echo $ARnls["e_insertcell"]; ?>", DECMD_INSCELL_onclick);
		TableContextMenu[8] = new ContextMenuItem("<?php echo $ARnls["e_deletecells"]; ?>", DECMD_DELCELL_onclick);
		TableContextMenu[9] = new ContextMenuItem("<?php echo $ARnls["e_mergecells"]; ?>", DECMD_MRGCELL_onclick);
		TableContextMenu[10] = new ContextMenuItem("<?php echo $ARnls["e_splitcell"]; ?>", DECMD_SPLTCELL_onclick);

		docComplete = false;
		KeepState=new StateObject();

		tEdit = new tableEditor(tbContentElement.contentWindow);

		// init the cssStyle select box
		init_cssStyle();
		init_ParagraphStyle();

		tbContentElement.contentWindow.document.body.onBlur=KeepState.SaveSelection;
		tbContentElement.contentWindow.document.body.onkeyup=tableEdit_onKeyUp;
		tbContentElement.contentWindow.document.body.onmouseup=tableEdit_onMouseUp;
		tbContentElement.contentWindow.document.body.onmousemove=tableEdit_onMouseMove;
		tbContentElement.contentWindow.document.body.DocumentComplete=tbContentElement_DocumentComplete;
		tbContentElement.contentWindow.document.body.onkeypress=tbContentElement_Compose_press;
		tbContentElement.contentWindow.document.body.onkeydown=tbContentElement_Compose_down;
		tbContentElement.contentWindow.document.body.oncontextmenu=showContextMenu;
		tbContentElement.onscroll = tableEdit_onScroll;

		if (tbContentElement.contentWindow.onLoadHandler) {
			tbContentElement.contentWindow.onLoadHandler();
		}

		document.getElementById("StandardToolbar").style.visibility = "visible";
		document.getElementById("StyleToolbar").style.visibility = "visible";
		document.getElementById("FormatToolbar").style.visibility = "visible";
	} else {
		// non microsoft browser, so try to at least show the content
		tbContentElement.style.border='0px';
		tbContentElement.style.backgroundColor='white';
		tbContentElement.style.height='100%';
		tbContentElement.style.width='100%';
		tbContentElement.style.overflow='auto';
	}
	document.getElementById("loadingdiv").style.visibility = "hidden";
}

function tbContentElement_Compose_press() {
	myevent=tbContentElement.contentWindow.event;
	if (!wgCompose_keypress(myevent)) {
		tbContentElement.contentWindow.event.cancelBubble=true; 
		tbContentElement.contentWindow.event.returnValue=false; 
	}
	return true;
}

function tbContentElement_Compose_down() {
	myevent=tbContentElement.contentWindow.event;
	if (!wgCompose_keydown(myevent)) {
		tbContentElement.contentWindow.event.cancelBubble=true; 
		tbContentElement.contentWindow.event.returnValue=false; 
	}
	return true;
}

var showTableOptions=false;
var previousParent=null;

/*
	if (showTableOptions) {
		for (i=0; i<TableContextMenu.length; i++) {
			ContextMenu[idx++] = TableContextMenu[i];
		}
	}
*/

// DisplayChanged handler. Very time-critical routine; this is called
// every time a character is typed. QueryStatus those toolbar buttons that need
// to be in synch with the current state of the document and update. 
function tbContentElement_DisplayChanged() {
	for ( var i=0; i<QueryStatusToolbarButtons.length; i++) {
		if (buttons_disabled[CommandCrossReference[QueryStatusToolbarButtons[i].command]]) {
			TBSetState(QueryStatusToolbarButtons[i].element, "gray"); 
		} else if(!tbContentElement.contentWindow.document.queryCommandState(QueryStatusToolbarButtons[i].command)) {
			if (!tbContentElement.contentWindow.document.queryCommandSupported(QueryStatusToolbarButtons[i].command) ||
					!tbContentElement.contentWindow.document.queryCommandEnabled(QueryStatusToolbarButtons[i].command)) {
			TBSetState(QueryStatusToolbarButtons[i].element, "gray"); 
			} else {
				TBSetState(QueryStatusToolbarButtons[i].element, "unchecked"); 
			} 
		} else { // DECMDF_LATCHED
			 TBSetState(QueryStatusToolbarButtons[i].element, "checked");
		}
	}

	var parent=false;
	var sel=KeepState.GetSelection();
	var tableOptions=false;
	if (sel) {
		if (sel.type=="Control") {
			parent=sel.item(0);
		} else {
			parent=sel.parentElement();
		}
		if (parent==previousParent) {
			return true;
		} else {
			previousParent=parent;
		}
		while (parent && parent.className!='editable' && parent.parentElement) {
			if (!tableOptions && parent.tagName=='TABLE') {
				tableOptions=true;
			}
			parent=parent.parentElement;
		}
	}
	showTableOptions=tableOptions;
	return true;
}

function showTableBorders(element) {
	// Now show borders for tables as well.	
    var aTables = element.getElementsByTagName("TABLE");
    for (i=0;i<aTables.length;i++){
		var tds = aTables[i].getElementsByTagName("td");
		for (var j = 0; j < tds.length; ++j) {
			if (showBorders && (aTables[i].border == 0)) {
				tds[j].runtimeStyle.border = '1 dotted #BCBCBC';
			} else {
				tds[j].runtimeStyle.cssText = '';
			}
//            aTables[i].runtimeStyle.borderCollapse = "collapse";
        }
    }
	if (tEdit) {
		tEdit.repositionArrows();
	}
}



function SAVE_onclick(newurl) {
	if (tbContentElement.contentWindow.SAVE_onclick) {
		tbContentElement.contentWindow.SAVE_onclick(newurl);
	} else {
		savewindow=window.open(wgSaveTmpl+'?arReturnPage='+escape(newurl), 'savewindow', 'directories=no,height=100,width=300,location=no,status=yes,toolbar=no,resizable=no');
		top.wgTBIsDirty=false;
	}
}

function NEW_onclick() {
	if (tbContentElement.contentWindow.NEW_onclick) {
		tbContentElement.contentWindow.NEW_onclick();
	} else {
	    addwindow=window.open( "<?php echo $this->make_url(); ?>object.new.select.phtml", 'addwindow',
	        'directories=no,height=400,width=550,location=no,status=yes,toolbar=no,resizable=no');
		addwindow.focus();
	}
}

function DELETE_onclick() {
	if (tbContentElement.contentWindow.DELETE_onclick) {
		tbContentElement.contentWindow.DELETE_onclick();
	} else {
	    delwindow=window.open('<?php echo $this->make_url(); ?>object.delete.phtml', 'delwindow',
	        'directories=no,height=100,width=300,location=no,status=yes,toolbar=no,resizable=no');
		delwindow.focus();
	}
}

function wgRecurseDone(action) {
	if (tbContentElement.contentWindow.wgRecurseDone) {
		tbContentElement.contentWindow.wgRecurseDone(action);
	} else {
	    window.location='<?php echo $this->make_url('..'); ?>user.edit.html';
	}
}

function objectadded(type, name, path) {
	if (tbContentElement.contentWindow.objectadded) {
		tbContentElement.contentWindow.objectadded(type, name, path);
    } else {
		window.location='<?php echo $this->make_url(); ?>user.edit.html';
	}
}

var exitClicked=false;

function EXIT_onclick() {
	exitClicked=true;
	if (tbContentElement.contentWindow.EXIT_onclick) {
		tbContentElement.contentWindow.EXIT_onclick();
	} else {
		var temp=isDirty();
		if (temp && doConfirmSave()) {
			SAVE_onclick('<?php echo $this->make_url(); ?>');
		} else if (window.opener) {
			window.close();
		} else {
		    window.location='<?php echo $this->make_url(); ?>';
		}
	}
}

function doConfirmSave() {
	return confirm("You have made changes to this page, do you wish to save these?");
}

function handleBeforeUnload() {
	if (!exitClicked && isDirty()) {
		event.returnValue="You have made changes to this page, if you leave these changes will not be saved.";
	}
}

window.onbeforeunload=handleBeforeUnload;

function DECMD_UNORDERLIST_onclick() {
	setFormat("InsertUnorderedList");
}

function DECMD_UNDO_onclick() {
	setFormat("Undo");
	
}

function DECMD_UNDERLINE_onclick() {
	setFormat("Underline");
	
}

function DECMD_SELECTALL_onclick() {
	setFormat("SelectAll");
	
}

function DECMD_REDO_onclick() {
	setFormat("Redo");
	
}

function DECMD_PASTE_onclick() {
	setFormat("Paste");
	
}

function DECMD_OUTDENT_onclick() {
	setFormat("Outdent");
	
}

function DECMD_ORDERLIST_onclick() {
	setFormat("InsertOrderedList");
	
}

function DECMD_JUSTIFYRIGHT_onclick() {
	setFormat("JustifyRight");
	
}

function DECMD_JUSTIFYLEFT_onclick() {
	setFormat("JustifyLeft");
	
}

function DECMD_JUSTIFYCENTER_onclick() {
	setFormat("JustifyCenter");
	
}

function DECMD_ITALIC_onclick() {
	setFormat("Italic");
	
}

function DECMD_INDENT_onclick() {
	setFormat("Indent");
	
}

function getEl(sTag,start) {
	while ((start!=null) && (start.tagName!=sTag))
		start = start.parentElement
	return start
}


function DECMD_IMAGE_onclick() {
	var args = new Array();
	var elIMG = false;
	var el = false;
	var rg = false;

	window.el=false;
	window.elIMG=false;
	window.rg=false;
	el=tbContentElement.contentWindow.document.selection;
	window.el=el;
	if (el.type=="Control") {
		elIMG=el.createRange().item(0);
		window.elIMG=elIMG;
		if (elIMG) {
			src=new String(elIMG.src);
			root=new String('<?php echo $this->store->get_config("root"); ?>');
			if (src.substring(0,root.length)==root) {
				src=src.substring(root.length);
			} else { // htmledit component automatically adds http://
				temp=new String('<?php echo $this->store->get_config("root"); ?>');
				if (src.substring(0,temp.length)==temp) {
					src=src.substring(temp.length);
				} else {
					temp=new String('http:///');
					if (src.substring(0,temp.length)==temp) {
						src=src.substring(temp.length-1);
					}
				}
			}
			args['src'] = src;
			args['border'] = elIMG.border;
			args['hspace'] = elIMG.hspace;
			args['vspace'] = elIMG.vspace;
			args['align'] = elIMG.align;
			args['alt'] = elIMG.alt;
			args['ar:type'] = elIMG['ar:type'];
			args['ar:path'] = elIMG['ar:path'];
		}
	} else {
		elIMG=false;
		window.rg=el.createRange();
		src = '<?php echo $this->path; ?>';
		args['src'] = "";
		args['ar:path'] = src;
		args['hspace'] = "";
		args['vspace'] = "";
		args['align'] = ""; 
		args['alt'] = "";
		args['border'] = "";
	}
	args['editOptions']=tbContentEditOptions;
	args['stylesheet']=tbContentEditOptions['css']['stylesheet'];
	arr = showModalDialog( "edit.object.html.image.phtml", args,	"font-family:Verdana; font-size:12; dialogWidth:600px; dialogHeight:400px; status: no; resizable: yes;");
	if (arr != null){
	IMAGE_set(arr);
	}
}

function IMAGE_set(arr) {
	window.setfocusto=false;
	var el=window.el;
	if (arr != null) {
		// register change in the editable field, since the focus was already lost through the dialog
		var editField=getEditableField();
		if (editField) {
			editField.onfocus();
		}

		src=new String(arr['src']);
		temp=new String('http://');
		if (src.substring(0,temp.length)!=temp) {
			src='<?php echo $this->store->get_config("root"); ?>'+src;
		}
		if (window.elIMG) { // insert a new img
			elIMG=window.elIMG;
			elIMG.src=src;
			elIMG.border=arr['border'];
			elIMG.hspace=arr['hspace'];
			elIMG.vspace=arr['vspace'];
			if (arr['align']=='none') {
				elIMG.align='';
			} else {
				elIMG.align=arr['align'];
			}
			elIMG.alt=arr['alt'];
			elIMG['ar:type'] = arr['ar:type'];
			elIMG['class'] = arr['class'];
			if (arr['ar:path']) {
				elIMG['ar:path']=arr['ar:path'];
			}
		} else {
			el=window.el;
			if ((el.type=="None") || (el.type=="Text"))	{
				temp='<IMG SRC="'+src+'"';
				if (arr['border']!='') {
					temp+=' BORDER='+arr['border'];
				}
				if (arr['hspace']!='') {
					temp+=' HSPACE='+arr['hspace'];
				}
				if (arr['vspace']!='') {
					temp+=' VSPACE='+arr['vspace'];
				}
				if (arr['align']!='') {
					temp+=' ALIGN='+arr['align'];
				}
				if (arr['alt']!='') {
					temp+=' ALT="'+arr['alt']+'"';
				}
				if (arr['class']!='') {
					temp+=' CLASS="'+arr['class']+'"';
				}
				if (arr['ar:type']!='') {
					temp+=' ar:type="'+arr['ar:type']+'"';
				}
				if (arr['ar:path']!='') {
					temp+=' ar:path="'+arr['ar:path']+'"';
				}
				temp+='>';
				rg.pasteHTML(temp);
				rg.select();
			}
		}
		// register change in the editable field, since the focus was already lost through the dialog
		if (editField) {
			editField.onblur();
		}
	}
}	

function cssStyle_onChange(command)
{
	/*
		following code is inspired if not copied from the very 
		nicely done FCK editor: http://www.fredck.com/FCKeditor/
	*/	

	var oSelection = tbContentElement.contentWindow.document.selection ;
	var oTextRange = oSelection.createRange() ;

	var sTag = new String(command.value);
	if (sTag=='0' || !command.value) {
		var clear=true;
	} else {
		var clear=false;
	}
	var aTagAndClass = sTag.split('.');
	if (aTagAndClass[0]) {
		sTag = aTagAndClass[0];
	} else {
		sTag = "";
	}
	if (aTagAndClass.length==2) {
		var sClass = aTagAndClass[1]; 
	} else {
		var sClass = "";
	}

	if (oSelection.type == "Text")
	{
		var oSpan = document.createElement("SPAN") ;
		oSpan.innerHTML = oTextRange.htmlText ;

		var oParent = oTextRange.parentElement() ;
		var oFirstChild = oSpan.firstChild ;

		if (sTag=='' && oFirstChild.nodeType == 1 && oFirstChild.outerHTML == oSpan.innerHTML && 
				(oFirstChild.tagName == "SPAN"
				|| oFirstChild.tagName == "FONT"
				|| oFirstChild.tagName == "P"
				|| oFirstChild.tagName == "DIV"))
		{
			if (clear) // clear span/class
			{
				if (oFirstChild.tagName=="SPAN") 
				{
					oParent.outerHTML = oParent.innerHTML;
				} 
				else 
				{
					oParent.className = null;
				}
			} else {
				oParent.className = sClass ;
			}
		}
		else
		{
			if (clear) 
			{
				var text = oSpan.innerText;
				oTextRange.pasteHTML(text);
			} 
			else 
			{
				var text = oTextRange.htmlText;
				if (sTag=='') 
				{
					sTag='span';
				}
				if (sClass) 
				{
					oTextRange.pasteHTML('<'+sTag+' class="' + sClass + '">' + text + '</'+sTag+'>');
				}
				else 
				{
					oTextRange.pasteHTML('<'+sTag+'>' + text + '</'+sTag+'>');
				}
			}
		}
	}
	else if (oSelection.type == "Control" && oTextRange.length == 1)
	{
		var oControl = oTextRange.item(0) ;
		if (sTag=='' || oControl.tagName==sTag) {
			oControl.className = sClass ;
		}
	}
	var editField=getEditableField();
	if (editField) {
		registerChange(editField.id);
	}
	command.selectedIndex = 0 ;	
	tbContentElement.focus();
}

function wgCompose_show(buffer) {
	var sel=KeepState.GetSelection();
	sel.pasteHTML(buffer);
}

function GetElement(oElement,sTag) 
{
	/*Utility function; Goes up the DOM from the element oElement, till
	a parent element with the tag that matches sTag
	is found. Returns that parent element.*/
	while (oElement!=null && oElement.tagName!=sTag){
		oElement = oElement.parentElement;
	}
	return oElement;
}

function DECMD_HYPERLINK_onclick() {
	var arr,args,oSel, oParent, sType;
	var oATag=false;

	oSel = tbContentElement.contentWindow.document.selection;
	oRange = oSel.createRange();
	sType=oSel.type;
	if (sType=="Control") {
		oElement=oRange.item(0);
		oParent=oRange.item(0).parentElement;
	} else {
		if (oRange.htmlText.substr(oRange.htmlText.length-4, 4)=='<BR>') {
			// BR included in selection as last element, remove it, it has
			// dangerous effects on the hyperlink command in IE
			oRange.moveEnd('character',-1);
			oRange.select();
		}
		if (oRange.htmlText.substr(0,4)=='<BR>') {
			// idem when its the first character
			oRange.moveStart('character',1);
			oRange.select();
		}
		oParent=oRange.parentElement();
	}
	arr=null;
	args=new Array();
	//set a default value for your link button
	args["URL"] = "http:/"+"/";
	args["anchors"] = HYPERLINK_getAnchors();
	while (oParent.tagName && !oParent.className.match(/\beditable\b/) && oParent.tagName != 'A') {
		oParent = oParent.parentNode;
	}
	if (oParent.tagName=="A") {
		oATag=oParent;
		args["URL"] = oParent.href;
		for (var i=0; i<oParent.attributes.length; i++) {
			oAttr=oParent.attributes.item(i);
			if (oAttr.specified) {
				args[oAttr.nodeName]=oAttr.nodeValue;
			}
		}
	}
	/* 
	here popup your own dialog, pass the arg array to that, get what the user
	entered there and come back here
	*/ 
	arr = showModalDialog( "edit.object.html.link.phtml", args,	"font-family:Verdana; font-size:12; dialogWidth:32em; dialogHeight:16em; status: no; resizable: yes;");
	if (arr != null){
		// register change in the editable field, since the focus was already lost through the dialog
		var editField=getEditableField();
		if (editField) {
			editField.onfocus();
		}
		var newLink="<a";
		if (arr['URL']) {
			newLink+=" href=\""+arr['URL']+"\"";
		}
		if (arr['name']) {
			newLink+=' name="'+arr['name']+'"';
		}
		if (arr['attributes']) {
			for (var i in arr['attributes']) {
				var arAttribute=arr['attributes'][i];
				newLink=newLink+" "+arAttribute.name+"=\""+arAttribute.value+"\"";
			}
		}
		newLink=newLink+">";
			if (!oATag && (arr['URL'] || arr['name'])) {
			if (sType=='Control') {
				oElement.outerHTML=newLink+oElement.outerHTML+"</A>";
			} else {

				// first let the dhtmledit component set the link, since it is better in it.
				// but to find it back, we need a unique identifier
				var linkIdentifier=Math.floor(Math.random()*10000);
				setFormat("CreateLink", '#'+linkIdentifier);
				// now collapse the range, so even if the range overlaps a link partly, the parent
				// element will become the link. trust me.... 
				oRange.collapse();
				// now set the ATag object, so it can be 'fixed' with the extra attributes later
				oATag=oRange.parentElement();
				if (oATag.tagName!='A' || oATag.href!='#'+linkIdentifier) {
					// ok, the link doesn't line up with the range, apparantly, so try to find the link
					oATag=null;
					var allATags=tbContentElement.DOM.getElementsByTagName('A');
					for (var i=0; i<allATags.length; i++) {
						if (allATags[i].href=='#'+linkIdentifier) {
							oATag=allATags[i];
							break;
						}
					}
				}
			}
		}
		if (oATag && (arr['URL'] || arr['name'])) {
			oATag.outerHTML=newLink+oATag.innerHTML+'</a>';
		}
		if (oATag && !arr['URL'] && !arr['name']) {
			oATag.outerHTML=oATag.innerHTML;
//			unlink seems broken, when removing a link on an image, it also removes the image...
//			setFormat("UnLink");
		}
		// register change in the editable field, since the focus was already lost through the dialog
		if (editField) {
			editField.onblur();
		}
	}
	tbContentElement.focus();
}

function HYPERLINK_getAnchors() {
	var aATags = tbContentElement.contentWindow.document.getElementsByTagName('A');
	var result = new Array();
	var i=0;
	var ii=0;
	for (ii=0; ii<aATags.length; ii++) {
		var oATag=aATags[ii];
		if (oATag.name) {
			result[i]='#'+oATag.name;
			i++;
		}
	}
	return result;
}

function DECMD_DELETE_onclick() {
	setFormat("Delete");
	
}

function DECMD_CUT_onclick() {
	setFormat("Cut");
	
}

function DECMD_COPY_onclick() {
	setFormat("Copy");
	
}

function DECMD_BOLD_onclick() {
	setFormat("Bold");
}

function ParagraphStyle_onchange() {
	setFormat("FormatBlock", "<"+ParagraphStyle.value+">"); 
	ParagraphStyle.selectedIndex=0;
}

function tbContentElement_DocumentComplete() {

		if (initialDocComplete) {
			if (tbContentElement.CurrentDocumentPath == "") {
				URL_VALUE = "http://";
		}
		else {
			URL_VALUE = tbContentElement.CurrentDocumentPath;
		}
	}

	initialDocComplete = true;
	docComplete = true;
}


/*
function loadStyleSheet() {
	tbContentElement=document.getElementById('tbContentElement');
	if (navigator.appName.indexOf("Microsoft")!=-1) {
		var e = tbContentElement.contentWindow.document.createElement( '<link href="' + tbContentEditOptions['css']['stylesheet'] + '" rel="stylesheet" type="text/css">' );
		tbContentElement.contentWindow.document.body.appendChild( e );
	}
}
*/

function getContents(data_id) {
	var data="";
	if (data=tbContentElement.contentWindow.document.getElementById(data_id)) {
		// it seems that the editor logic in MSIE insists on adding
		// full paths to hyperlinks, even if you just enter #something.
		// so removing it again here.
		var temp=new String(data.innerHTML);
		var replaceregexp=new RegExp(tbContentElement.location, 'g');
		temp=temp.replace(replaceregexp, '');
		return temp;
	} else {
		return '';
	}
}

function getTextContents(data_id) {
	var data="";
	if (data=tbContentElement.contentWindow.document.getElementById(data_id)) {
		return data.innerText;
	} else {
		return '';
	}
}

function getValue(data_name) {
	var data="";
	var value='';
	if (data=tbContentElement.contentWindow.document.getElementById(data_name)) {
		switch (data.type) {
			case 'checkbox' :
				if (data.checked) {
					value=data.value;
				}
				break;
			case 'radio' :
				var radio=tbContentElement.contentWindow.document.all[data_name];
				if (radio) { 
					for (var i=0; i<radio.length; i++) {
						if (radio[i].checked) {
							value=radio[i].value;
							break;
						}
					}
				}
				break;
			case 'hidden' :
			case 'password' :
			case 'text' :
				value=data.value;
				break;
			case 'select-one' :
				value=data.options[data.selectedIndex].value;
				break;
			case 'select-multiple' :
				value=new Array();
				for (var i=0; i<data.length; i++) {
					if (data.options[i].selected) {
						value[value.length]=data.options[i].value;
					}
				}
				break;
			default :
				value=data.innerHTML;
				break;
		}
		return value;
	} else {
		return '';
	}
}

function setValue(data_name, value) {
	var data="";
	if (data=tbContentElement.contentWindow.document.getElementById(data_name)) {
		switch (data.type) {
			case 'checkbox' :
				if (data.checked) {
					value=data.value;
				}
				break;
			case 'radio' :
				var radio=tbContentElement.contentWindow.document.all[data_name];
				if (radio) { 
					for (var i=0; i<radio.length; i++) {
						if (radio[i].checked) {
							value=radio[i].value;
							break;
						}
					}
				}
				break;
			case 'hidden' :
			case 'password' :
			case 'text' :
				data.value = value;
				break;
			case 'select-one' :
				value=data.options[data.selectedIndex].value;
				break;
			case 'select-multiple' :
				value=new Array();
				for (var i=0; i<data.length; i++) {
					if (data.options[i].selected) {
						value[value.length]=data.options[i].value;
					}
				}
				break;
			default :
				data.innerHTML = value;
				break;
		}
	}
}

var arFieldRegistry=new Array();
var arFieldList=new Array(); // simple array to keep all editable fields
var arObjectRegistry=new Array();
var arChangeRegistry=new Array();
var arGroupRegistry=new Array();
var currentEditableField=false;
var arRequiredFieldRegistry=new Array();

function registerDataField(fieldId, fieldName, objectPath, objectId) {
	arFieldRegistry[fieldId]=new dataField(fieldId, fieldName, objectPath, objectId);
	if (!arObjectRegistry[objectId]) {
		arObjectRegistry[objectId]=new Array();
	}
	if (!arObjectRegistry[objectId][fieldName]) {
		arObjectRegistry[objectId][fieldName]=new Array();
	}
	arObjectRegistry[objectId][fieldName][arObjectRegistry[objectId][fieldName].length]=arFieldRegistry[fieldId];
	arFieldList.push(arFieldRegistry[fieldId]); // add to full list of editable fields
}

function requiredField(title, fieldId) {
	this.title=title;
	this.fieldId=fieldId;
}

function requireDataField(fieldName, objectId, title) {
	var fieldId=arObjectRegistry[objectId][fieldName][0].fieldId;
	arRequiredFieldRegistry.push(new requiredField(title, fieldId));
}

function getRequiredFields() {
	return arRequiredFieldRegistry;
}

function getField(fieldId) {
	return arFieldRegistry[fieldId];
}

function dataField(fieldid, name, path, id) {
	this.fieldId=fieldid;
	this.name=name;
	this.path=path; //FIXME: an object may have multiple paths, not all of which the user may have edit grants on
	this.id=id;
}

function registerGroup(group, fieldId) {
	if (!arGroupRegistry[group]) {
		arGroupRegistry[group]=new Array();
	}
	arGroupRegistry[group].push(fieldId);
	arFieldRegistry[fieldId].group=group;
}

function registerChange(fieldId, stoprecurse) {
	if (arFieldRegistry[fieldId]) {
		var objectId=arFieldRegistry[fieldId].id;
		var fieldName=arFieldRegistry[fieldId].name;
		if (!arChangeRegistry[fieldName+objectId]) {
			var index=arChangeRegistry.length;
			arChangeRegistry[index]=arFieldRegistry[fieldId];
			arChangeRegistry[new String(fieldName+objectId)]=index;
		}
	}
	if (!stoprecurse && arFieldRegistry[fieldId].group) {
		groupList=arGroupRegistry[arFieldRegistry[fieldId].group];
		if (groupList) {
			for (var i=0; i<groupList.length; i++) {
				registerChange(groupList[i], true);
			}
		}
	}
}

function initEditable() {
	var editable;
	var editWindow=document.getElementById('tbContentElement').contentWindow;
	for (i=0; i<editWindow.document.all.length; i++) {
		if (editWindow.document.all[i].className == "editable") {
			editable=editWindow.document.all[i];
			editable.onfocus=checkChangeStart;
			editable.onblur=checkChangeEnd;
			editable.contentEditable=true;
			editable.style.backgroundImage="url('<?php echo $this->store->get_config("root"); ?>images/dot.gif')";
		}
	}
}

function checkChangeStart() {
	this.startContent=getValue(this.id);
	currentEditableField=this;
}

function checkChangeEnd() {
	var newValue = getValue(this.id);
	if (arFieldRegistry[this.id]) {
		if (this.startContent!=newValue) {
			registerChange(this.id);

			var objectId = arFieldRegistry[this.id].id;
			for (var i in arObjectRegistry[objectId][arFieldRegistry[this.id].name]) {
				var fieldId = arObjectRegistry[objectId][arFieldRegistry[this.id].name][i].fieldId;
				arFieldRegistry[fieldId].value = newValue;
				if (fieldId!=this.id) {
					// don't update the content of the current field, since that breaks
					// selections.
					setValue(fieldId, newValue);
				}
			}
			this.startContent=newValue;
		}
	}
}

function isDirty() {
	if (currentEditableField) {
		currentEditableField.onblur();
	}
	return arChangeRegistry.length;
}

function clearDirty() {
	arChangeRegistry=new Array();
}

function getEditableField() {
	var parent=false;
	var sel=KeepState.GetSelection();
	if (sel) {
		if (sel.type=="Control") {
			parent=sel.item(0);
		} else {
			parent=sel.parentElement();
		}
		while (parent && parent.className!='editable' && parent.parentElement) {
			parent=parent.parentElement;
		}
	}
	if (parent && parent.className=='editable') {
		return parent;
	} else {
		return false;
	}
}

function getDirtyField() {
	return arChangeRegistry.pop();
}

function DECMD_DETAILS_onclick() {
	var mydoc=tbContentElement.contentWindow.document;
	var foundit=false;
	if (mydoc.styleSheets[0]) {
		var myRules=mydoc.styleSheets[0].rules;
		for (var i=0; i<myRules.length; i++) {
			if (myRules[i].selectorText.match(/.*\.editable.*/)) {
				if (myRules[i].style.borderWidth=="1px") {
					myRules[i].style.borderWidth='0px';
					myRules[i].style.borderColor='';
					myRules[i].style.borderStyle='';
				} else {
					myRules[i].style.borderWidth='1px';
					myRules[i].style.borderColor='black';
					myRules[i].style.borderStyle='dotted';
				}	
				foundit=true;
			}
		}
		if (!foundit) {
			// append a style
			alert('no .editable');
		}
	}
}

function getRequired() {
    var labels=tbContentElement.contentWindow.document.getElementsByTagName('LABEL');
	if (labels) {
	    var required=new Array();
		var i=labels.length-1;
		do {
			if (labels[i].className=='required') {
				required[labels[i].htmlFor]=labels[i].innerText;
			}
		} while (i--);
	    return required;
	} else {

	}
}

function DECMD_TABLE_onclick() {
  var args = new Array();
  var arr = null;
  var tablestr='';

  // Display table information dialog
  args["NumRows"] = 3;
  args["NumCols"] = 3;
  args["TableAttrs"] = '';
  args["CellAttrs"] = '';
  args["Caption"] = '';
  
  arr = null;

  arr = showModalDialog( "<?php echo $AR->dir->www; ?>widgets/htmledit/ie/Inc/instable.htm",
                             args,
                             "font-family:Verdana; font-size:12; dialogWidth:36em; dialogHeight:25em; status: no; resizable: yes;");
  if (arr != null) {
    // Initialize table object
    var tablestr="<table";
    if (arr["TableAttrs"]) {
      tablestr+=" "+arr["TableAttrs"];
    }
    tablestr+=">";
    if (arr["Caption"]) {
      tablestr+="<caption>"+arr["Caption"]+"</caption>";
    }
    for (var i=0; i<arr["NumRows"]; i++) {
      tablestr+="<tr>";
      for (var ii=0; ii<arr["NumCols"]; ii++) {
        tablestr+="<td";
        if (arr["CellAttrs"]) {
          tablestr+=" "+arr["CellAttrs"];
        }
        tablestr+="></td>";
      }
      tablestr+="</tr>";
    }
    tablestr+="</table>";
	el=tbContentElement.contentWindow.document.selection;
	if ((el.type=="None") || (el.type=="Text"))	{
		var rg=el.createRange();
		rg.pasteHTML(tablestr);
		rg.select();
	}
  }
}

function DECMD_SPLTCELL_onclick() {
	return tEdit.splitCell();
}

function DECMD_MRGCELL_onclick() {
	return tEdit.mergeRight();
}

function DECMD_INSCELL_onclick() {
	return tEdit.addCell();
}

function DECMD_INSROW_onclick() {
	return tEdit.processRow('add');
}

function DECMD_INSCOL_onclick() {
	return tEdit.processColumn('add');
}

function DECMD_DELCELL_onclick() {
	return tEdit.removeCell();
}

function DECMD_DELROW_onclick() {
	return tEdit.processRow('remove');
}

function DECMD_DELCOL_onclick() {
	return tEdit.processColumn('remove');
}

//-->
</script>

<SCRIPT LANGUAGE=javascript FOR=tbContentElement EVENT=DocumentComplete>
<!--
 tbContentElement_DocumentComplete()
//-->
</SCRIPT>

</head>
<body onload="return window_onload()" unselectable='on' tabIndex='-1'>
<div id="loadingdiv" style="position: absolute; top: 0px; left: 0px; width: 100%; height: 100% background-color: #999999; color: black;">
<?php echo $ARnls["loading"]; ?>
</div>
<!-- Toolbars -->
<div class="tbToolbar" ID="StandardToolbar" unselectable='on' style="visibility: hidden;">
	<div class="tbButton" ID="SAVE" unselectable='on' TITLE="Save Page" LANGUAGE="javascript" onclick="return SAVE_onclick(0)">
		<img class="tbIcon" unselectable='on' src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/save.gif" WIDTH="23" HEIGHT="22">
	</div>
	
	<div class="tbButton" ID="NEW" unselectable='on' TITLE="New Page" LANGUAGE="javascript" onclick="return NEW_onclick(0)">
		<img class="tbIcon" unselectable='on' src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/newdoc.gif" WIDTH="23" HEIGHT="22">
	</div>
	
	<div class="tbButton" ID="DELETE" unselectable='on' TITLE="Delete Page" LANGUAGE="javascript" onclick="return DELETE_onclick(0)">
		<img class="tbIcon" unselectable='on' src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/delete.gif" WIDTH="23" HEIGHT="22">
	</div>
	
	<div class="tbSeparator"></div -->

	<div class="tbButton" unselectable='on' ID="DECMD_CUT" TITLE="Cut" LANGUAGE="javascript" onclick="return DECMD_CUT_onclick();">
		<img class="tbIcon" unselectable='on' src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/cut.gif" WIDTH="23" HEIGHT="22">
	</div>
	<div class="tbButton" unselectable='on' ID="DECMD_COPY" TITLE="Copy" LANGUAGE="javascript" onclick="return DECMD_COPY_onclick();">
		<img class="tbIcon" unselectable='on' src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/copy.gif" WIDTH="23" HEIGHT="22">
	</div>
	<div class="tbButton" unselectable='on' ID="DECMD_PASTE" TITLE="Paste" LANGUAGE="javascript" onclick="return DECMD_PASTE_onclick();">
		<img class="tbIcon" unselectable='on' src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/paste.gif" WIDTH="23" HEIGHT="22">
	</div>

	<div class="tbSeparator" unselectable='on'></div>

	<div class="tbButton" unselectable='on' ID="DECMD_UNDO" TITLE="Undo" LANGUAGE="javascript" onclick="return DECMD_UNDO_onclick()">
		<img class="tbIcon" unselectable='on' src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/undo.gif" WIDTH="23" HEIGHT="22">
	</div>

	<div class="tbButton" unselectable='on' ID="DECMD_REDO" TITLE="Redo" LANGUAGE="javascript" onclick="return DECMD_REDO_onclick()">
		<img class="tbIcon" unselectable='on' src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/redo.gif" WIDTH="23" HEIGHT="22">
	</div>

	<div class="tbSeparator" unselectable='on'></div>

	<div class="tbButton" unselectable='on' ID="DECMD_DETAILS" TITLE="Details" LANGUAGE="javascript" onclick="return DECMD_DETAILS_onclick()">
		<img class="tbIcon" unselectable='on' src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/details.gif" WIDTH="23" HEIGHT="22">
	</div>
</div>

<div class="tbToolbar" unselectable='on' ID="StyleToolbar" style="visibility: hidden">
	<select ID="ParagraphStyle" class="tbGeneral" style="width:90" TITLE="Paragraph Format" LANGUAGE="javascript" onchange="return ParagraphStyle_onchange()">
	</select>

	<select ID="cssStyle" class="tbGeneral" style="width:90" TITLE="Style" LANGUAGE="javascript" onchange="return cssStyle_onChange(this)">
	</select>

</div>
<div class="tbToolbar" unselectable='on' ID="FormatToolbar" style="visibility: hidden">

	<div class="tbButton" unselectable='on' ID="DECMD_BOLD" TITLE="Bold" TBTYPE="toggle" LANGUAGE="javascript" onclick="return DECMD_BOLD_onclick();">
		<img class="tbIcon" unselectable='on' src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/bold.gif" WIDTH="23" HEIGHT="22">
	</div>
	<div class="tbButton" unselectable='on' ID="DECMD_ITALIC" TITLE="Italic" TBTYPE="toggle" LANGUAGE="javascript" onclick="return DECMD_ITALIC_onclick()">
		<img class="tbIcon" unselectable='on' src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/italic.gif" WIDTH="23" HEIGHT="22">
	</div>
	<div class="tbButton" unselectable='on' ID="DECMD_UNDERLINE" TITLE="Underline" TBTYPE="toggle" LANGUAGE="javascript" onclick="return DECMD_UNDERLINE_onclick()">
		<img class="tbIcon" unselectable='on' src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/under.gif" WIDTH="23" HEIGHT="22">
	</div>
	
	<div class="tbSeparator" unselectable='on'></div>

	<div class="tbButton" unselectable='on' ID="DECMD_JUSTIFYLEFT" TITLE="Align Left" TBTYPE="toggle" NAME="Justify" LANGUAGE="javascript" onclick="return DECMD_JUSTIFYLEFT_onclick()">
		<img class="tbIcon" unselectable='on' src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/left.gif" WIDTH="23" HEIGHT="22">
	</div>
	<div class="tbButton" unselectable='on' ID="DECMD_JUSTIFYCENTER" TITLE="Center" TBTYPE="toggle" NAME="Justify" LANGUAGE="javascript" onclick="return DECMD_JUSTIFYCENTER_onclick()">
		<img class="tbIcon" unselectable='on' src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/center.gif" WIDTH="23" HEIGHT="22">
	</div>
	<div class="tbButton" unselectable='on' ID="DECMD_JUSTIFYRIGHT" TITLE="Align Right" TBTYPE="toggle" NAME="Justify" LANGUAGE="javascript" onclick="return DECMD_JUSTIFYRIGHT_onclick()">
		<img class="tbIcon" unselectable='on' src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/right.gif" WIDTH="23" HEIGHT="22">
	</div>

	<div class="tbSeparator" unselectable='on'></div>

	<div class="tbButton" unselectable='on' ID="DECMD_ORDERLIST" TITLE="Numbered List" TBTYPE="toggle" LANGUAGE="javascript" onclick="return DECMD_ORDERLIST_onclick()">
		<img class="tbIcon" unselectable='on' src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/numlist.gif" WIDTH="23" HEIGHT="22">
	</div>
	<div class="tbButton" unselectable='on' ID="DECMD_UNORDERLIST" TITLE="Bulletted List" TBTYPE="toggle" LANGUAGE="javascript" onclick="return DECMD_UNORDERLIST_onclick()">
		<img class="tbIcon" unselectable='on' src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/bullist.gif" WIDTH="23" HEIGHT="22">
	</div>
	
	<div class="tbSeparator" unselectable='on'></div>

	<div class="tbButton" unselectable='on' ID="DECMD_OUTDENT" TITLE="Decrease Indent" LANGUAGE="javascript" onclick="return DECMD_OUTDENT_onclick()">
		<img class="tbIcon" unselectable='on' src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/deindent.gif" WIDTH="23" HEIGHT="22">
	</div>
	<div class="tbButton" unselectable='on' ID="DECMD_INDENT" TITLE="Increase Indent" LANGUAGE="javascript" onclick="return DECMD_INDENT_onclick()">
		<img class="tbIcon" unselectable='on' src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/inindent.gif" WIDTH="23" HEIGHT="22">
	</div>

	<div class="tbSeparator" unselectable='on'></div>

	<div class="tbButton" unselectable='on' ID="DECMD_HYPERLINK" TITLE="Link" LANGUAGE="javascript" onclick="return DECMD_HYPERLINK_onclick()">
		<img class="tbIcon" unselectable='on' src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/link.gif" WIDTH="23" HEIGHT="22">
	</div>
	<div class="tbButton" unselectable='on' ID="DECMD_IMAGE" TITLE="Insert Image" LANGUAGE="javascript" onclick="return DECMD_IMAGE_onclick()">
		<img class="tbIcon" unselectable='on' src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/image.gif" WIDTH="23" HEIGHT="22">
	</div>
	<div class="tbButton" unselectable='on' ID="DECMD_TABLE" TITLE="Insert Table" LANGUAGE="javascript" onclick="return DECMD_TABLE_onclick()">
		<img class="tbIcon" unselectable='on' src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/instable.gif" WIDTH="23" HEIGHT="22">
	</div>

</div>
<div class="tbButton" unselectable='on' ID="DECMD_EXIT" TITLE="Close Editor" LANGUAGE="javascript" onclick="return EXIT_onclick()" style="position: absolute; right: 5px; top: 2px; cursor: hand;">
	<img class="tbIcon" unselectable='on' src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/exit.gif" WIDTH="22" HEIGHT="22">
</div>
<IFRAME ID="tbContentElement" CLASS="tbContentElement" onLoad="initEditable()" unselectable='on' oldstyle="border: 0px; background-color: white; height: 100%; width: 100%; overflow: scroll;" <?php
	if (!$wgHTMLEditTemplate) {
		$wgHTMLEditTemplate="user.edit.page.html";
	}
	if (ldGetServerVar("QUERY_STRING") && (strpos($wgHTMLEditTemplate,"?") === false) ) {
		$wgHTMLEditTemplate.="?".ldGetServerVar("QUERY_STRING");
	}
	echo "SRC=\"$wgHTMLEditTemplate\"";
?>></IFRAME>

<!-- Toolbar Code File. Note: This must always be the last thing on the page -->
<script>
	if (navigator.appName.indexOf("Microsoft")!=-1) {
		document.write('<script LANGUAGE="Javascript" SRC="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/Toolbars/toolbars.js"></scrip'+'t>');
		document.write('<script LANGUAGE="Javascript">');
		document.write('	tbScriptletDefinitionFile = "<?php echo $AR->dir->www; ?>widgets/htmledit/ie/Toolbars/menubody.htm";');
		document.write('</scrip'+'t>');
		document.write('<script LANGUAGE="Javascript" SRC="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/Toolbars/tbmenus.js"></scrip'+'t>');
	}
</script>
</body>
</html>
