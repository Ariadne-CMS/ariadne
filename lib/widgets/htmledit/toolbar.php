<!-- Copyright 2000 Microsoft Corporation. All rights reserved. -->
<!-- Author: Steve Isaac, Microsoft Corporation -->
<!--This demo shows how to host the DHTML Editing component on a Web page. -->
<!-- 2002, Muze: extensive changes to use the toolbar on contentEditable content --> 
<html>
<head>
<META content="text/html; charset=UTF-8" http-equiv=Content-Type>
<title>Edit <?php echo $path.$file; ?></title>

<!-- Styles -->
<link REL="stylesheet" TYPE="text/css" HREF="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/Toolbars/toolbars.css">

<!-- Script Functions and Event Handlers -->
<script LANGUAGE="JavaScript" SRC="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/Inc/dhtmled.js">
</script>

<script LANGUAGE="JavaScript" SRC="<?php echo $AR->host.$AR->dir->www; ?>widgets/compose/compose.js">
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
var ContextMenu = new Array();
var GeneralContextMenu = new Array();

//
// Utility functions
//

// Constructor for custom object that represents an item on the context menu
function ContextMenuItem(string, cmdId) {
  this.string = string;
  this.cmdId = cmdId;
}

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
  var target = (type == "None" ? tbContentElement.document : sel)
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
    sel=tbContentElement.document.selection.createRange();
    sel.type=tbContentElement.document.selection.type;
  }
  return sel;
}

function state_saveSelection() {
  this.selection=tbContentElement.document.selection.createRange();
  if (!this.selection || (this.selection.parentElement && this.selection.parentElement() && 
         !(this.selection.parentElement() == tbContentElement.document.body || tbContentElement.document.body.contains(this.selection.parentElement() ) ) ) ) {
    this.selection=tbContentElement.document.body.createTextRange();
    this.selection.collapse(false);
    this.selection.type="None";
  } else {
    this.selection.type=tbContentElement.document.selection.type;
  }

}

function state_restoreSelection() {
  if (this.selection) {
    this.selection.select();
  }
}

var KeepState
//
// Event handlers
//
function window_onload() {
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
  // Initialize the context menu arrays.
  GeneralContextMenu[0] = new ContextMenuItem("Cut", DECMD_CUT);
  GeneralContextMenu[1] = new ContextMenuItem("Copy", DECMD_COPY);
  GeneralContextMenu[2] = new ContextMenuItem("Paste", DECMD_PASTE);
  docComplete = false;
  KeepState=new StateObject();
  tbContentElement.document.body.onBlur=KeepState.SaveSelection;
  tbContentElement.document.body.onkeyup=tbContentElement_DisplayChanged;
  tbContentElement.document.body.onmouseup=tbContentElement_DisplayChanged;
  tbContentElement.document.body.DocumentComplete=tbContentElement_DocumentComplete;
  tbContentElement.document.body.onkeypress=tbContentElement_Compose_press;
  tbContentElement.document.body.onkeydown=tbContentElement_Compose_down;
}

function tbContentElement_Compose_press() {
  myevent=tbContentElement.event;
  if (!wgCompose_keypress(myevent)) {
    tbContentElement.event.cancelBubble=true; 
    tbContentElement.event.returnValue=false; 
  }
  return true;
}

function tbContentElement_Compose_down() {
  myevent=tbContentElement.event;
  if (!wgCompose_keydown(myevent)) {
    tbContentElement.event.cancelBubble=true; 
    tbContentElement.event.returnValue=false; 
  }
  return true;
}

function tbContentElement_ShowContextMenu() {
  var menuStrings = new Array();
  var menuStates = new Array();
  var state;
  var i
  var idx = 0;

  // Rebuild the context menu. 
  ContextMenu.length = 0;

  // Always show general menu
  for (i=0; i<GeneralContextMenu.length; i++) {
    ContextMenu[idx++] = GeneralContextMenu[i];
  }

  // Set up the actual arrays that get passed to SetContextMenu
  for (i=0; i<ContextMenu.length; i++) {
    menuStrings[i] = ContextMenu[i].string;
    if (menuStrings[i] != MENU_SEPARATOR) {
      state = tbContentElement.document.queryCommandState(ContextMenu[i].cmdId);
    } else {
      state = DECMDF_ENABLED;
    }
    if (state == DECMDF_DISABLED || state == DECMDF_NOTSUPPORTED) {
      menuStates[i] = OLE_TRISTATE_GRAY;
    } else if (state == DECMDF_ENABLED || state == DECMDF_NINCHED) {
      menuStates[i] = OLE_TRISTATE_UNCHECKED;
    } else { // DECMDF_LATCHED
      menuStates[i] = OLE_TRISTATE_CHECKED;
    }
  }
  
  // Set the context menu
  tbContentElement.SetContextMenu(menuStrings, menuStates);
}

function tbContentElement_ContextMenuAction(itemIndex) {
  
  if (ContextMenu[itemIndex].cmdId == DECMD_INSERTTABLE) {
    InsertTable();
  } else {
    setFormat(ContextMenu[itemIndex].cmdId);
  }
}

// DisplayChanged handler. Very time-critical routine; this is called
// every time a character is typed. QueryStatus those toolbar buttons that need
// to be in synch with the current state of the document and update. 
function tbContentElement_DisplayChanged() {
  var i, s;
  for (i=0; i<QueryStatusToolbarButtons.length; i++) {
    if (!tbContentElement.document.queryCommandState(QueryStatusToolbarButtons[i].command)) {
      if (!tbContentElement.document.queryCommandSupported(QueryStatusToolbarButtons[i].command) ||
          !tbContentElement.document.queryCommandEnabled(QueryStatusToolbarButtons[i].command)) {
	    TBSetState(QueryStatusToolbarButtons[i].element, "gray"); 
      } else {
        TBSetState(QueryStatusToolbarButtons[i].element, "unchecked"); 
      } 
    } else { // DECMDF_LATCHED
       TBSetState(QueryStatusToolbarButtons[i].element, "checked");
    }
  }
  return true;
}


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
  var sel=KeepState.GetSelection();
  var type=sel.type;
  var args = new Array();

  if ((type=="Control") && (KeepState.IMG=sel.item(0))) {
    var src=new String(KeepState.IMG.src);
    var root=new String('<?php echo $this->store->root; ?>');
    var temp='';
    if (src.substring(0,root.length)==root) {
      src=src.substring(root.length);
    } else { // htmledit component automatically adds http://
      temp=new String('<?php echo $this->store->root; ?>');
      if (src.substring(0,temp.length)==temp) {
        src=src.substring(temp.length);
      } else {
        temp=new String('http:///');
        if (src.substring(0,temp.length)==temp) {
          src=src.substring(temp.length-1);
        }
      }
    }
    args['src']    = src;
    args['border'] = KeepState.IMG.border;
    args['hspace'] = KeepState.IMG.hspace;
    args['vspace'] = KeepState.IMG.vspace;
    args['align']  = KeepState.IMG.align;
    args['alt']    = KeepState.IMG.alt;
  } else {
    sel.collapse(true);
    KeepState.IMG=false;
    args['src']    = '<?php if ($wgHTMLEditImageDir) { echo $wgHTMLEditImageDir; } else { echo $this->path; } ?>';
    args['hspace'] = "";
    args['vspace'] = "";
    args['align']  = ""; 
    args['alt']    = "";
    args['border'] = "";
  }

  imgwindow=window.open("edit.object.html.image.phtml?src="+escape(args['src'])+
    "&border="+escape(args['border'])+"&hspace="+escape(args['hspace'])+
    "&vspace="+escape(args['vspace'])+"&align="+escape(args['align'])+
    "&alt="+escape(args['alt']),"imgwindow","directories=no,height=160,width=425,location=no,menubar=no,status=no,toolbar=no,resizable=yes");
  imgwindow.focus();
  KeepState.RestoreSelection();
  return true;

}

function IMAGE_set(arr) {
  var sel=KeepState.GetSelection();
  var type=sel.type;
  var src='';
  var temp='';

  if (arr != null) {
    src=new String(arr['src']);
    temp=new String('http://');
    if (src.substring(0,temp.length)!=temp) {
      src='<?php echo $this->store->root; ?>'+src;
    }
    if (KeepState.IMG) { // insert a new img
      KeepState.IMG.src=src;
      if (arr['border']!='') {
        KeepState.IMG.border=arr['border'];
      }
      if (arr['hspace']!='') {
        KeepState.IMG.hspace=arr['hspace'];
      }
      if (arr['vspace']!='') {
        KeepState.IMG.vspace=arr['vspace'];
      }
      if (arr['align']!='') {
        KeepState.IMG.align=arr['align'];
      }
      if (arr['alt']!='') {
        KeepState.IMG.alt=arr['alt'];
      }
    } else {
      if ((type=="None") || (type=="Text"))  {
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
        temp+='>';
        sel.pasteHTML(temp);
        sel.select();
      }
    }
  }
}  

function wgCompose_show(buffer) {
  var sel=KeepState.GetSelection();
  sel.pasteHTML(buffer);
}

	function DECMD_HYPERLINK_onclick() {
		var arr,args,oSel, oParent, sType;

		oSel = document.selection;
		sType=oSel.type;
		arr=null;
		args=new Array();
		//set a default value for your link button
		args["URL"] = "http://";
		/*
		The logic is similar if there is a selection
		of text or image. You get the nearest parent and
		then go up the DOM to see the nearest parent A element
		*/
		if(sType=="Text" || sType=="None"){
			oParent = GetElement(oSel.createRange().parentElement(),"A");
		} else { 
			oParent = GetElement(oSel.createRange().item(0),"A");
		}
		/* 
		So, if you get a parent A (anchor) element, you use the href property
		of that. Now, there is an obvious caveat here, because A can
		be a link or an anchor. So, you need to see if it has an href.
		*/
		if(oParent && oParent.href) {
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
		arr = showModalDialog( "<?php echo $this->store->root.$AR->user->path; 
			?>edit.object.html.link.phtml", args,  "font-family:Verdana; font-size:12; dialogWidth:32em; dialogHeight:12em");
		if (arr != null){
			if (oParent) {
				for (i=0; i<oParent.attributes.length; i++) {
					oldAttribute=oParent.attributes.item(i);
					var dummy=new String(oldAttribute.name);
					if (dummy.substring(0,3)=='ar_') {
						oParent.removeAttribute(oldAttribute.name);
					}
				}
				oParent.href=arr['URL'];
				if (arr['attributes']) {
					for (var i in arr['attributes']) {
						var arAttribute=arr['attributes'][i];
						oParent.setAttribute(arAttribute.name, arAttribute.value);
					}
				}
			} else {
				var newHTML="<a href=\""+arr['URL']+"\"";
				if (arr['attributes']) {
					for (var i in arr['attributes']) {
						var arAttribute=arr['attributes'][i];
						newHTML=newHTML+" "+arAttribute.name+"=\""+arAttribute.value+"\"";
					}
				}
				oRange=oSel.createRange();
				if (sType=="Control") {
					var myimg=oRange.item(0);
					newHTML=newHTML+">" + myimg.outerHTML + "</a>";
					myimg.outerHTML=newHTML;
				} else {
					newHTML=newHTML+">" + oRange.htmlText + "</a>";
					oRange.pasteHTML(newHTML);
				}
			}
		}
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

//-->
</script>

<script LANGUAGE="javascript" FOR="tbContentElement" EVENT="ShowContextMenu">
<!--
return tbContentElement_ShowContextMenu()
//-->
</script>

<script LANGUAGE="javascript" FOR="tbContentElement" EVENT="ContextMenuAction(itemIndex)">
<!--
return tbContentElement_ContextMenuAction(itemIndex)
//-->
</script>

<SCRIPT LANGUAGE=javascript FOR=tbContentElement EVENT=DocumentComplete>
<!--
 tbContentElement_DocumentComplete()
//-->
</SCRIPT>

</head>
<body onload="return window_onload()" unselectable='on'>

<!-- Toolbars -->
<div class="tbToolbar" ID="StandardToolbar" unselectable='on'>
  <!-- div class="tbButton" ID="MENU_FILE_SAVE" unselectable='on' TITLE="Save File" LANGUAGE="javascript" onclick="return MENU_FILE_SAVE_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/save.gif" WIDTH="23" HEIGHT="22">
  </div>
  
  <div class="tbSeparator"></div -->

  <div class="tbButton" unselectable='on' ID="DECMD_CUT" TITLE="Cut" LANGUAGE="javascript" onclick="return DECMD_CUT_onclick();">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/cut.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div class="tbButton" unselectable='on' ID="DECMD_COPY" TITLE="Copy" LANGUAGE="javascript" onclick="return DECMD_COPY_onclick();">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/copy.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div class="tbButton" unselectable='on' ID="DECMD_PASTE" TITLE="Paste" LANGUAGE="javascript" onclick="return DECMD_PASTE_onclick();">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/paste.gif" WIDTH="23" HEIGHT="22">
  </div>

  <div class="tbSeparator"></div>

  <div class="tbButton" unselectable='on' ID="DECMD_UNDO" TITLE="Undo" LANGUAGE="javascript" onclick="return DECMD_UNDO_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/undo.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div class="tbButton" unselectable='on' ID="DECMD_REDO" TITLE="Redo" LANGUAGE="javascript" onclick="return DECMD_REDO_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/redo.gif" WIDTH="23" HEIGHT="22">
  </div>

</div>

<div class="tbToolbar" unselectable='on' ID="FormatToolbar">
  <select ID="ParagraphStyle" class="tbGeneral" style="width:90" TITLE="Paragraph Format" LANGUAGE="javascript" onchange="return ParagraphStyle_onchange()">
    <option value="P">Normal (P)</option>
    <option value="H1">Heading 1 (H1)</option>
    <option value="H2">Heading 1 (H1)</option>
    <option value="H3">Heading 2 (H1)</option>
    <option value="H4">Heading 3 (H1)</option>
    <option value="H5">Heading 4 (H1)</option>
    <option value="H6">Heading 5 (H1)</option>
    <option value="H7">Heading 6 (H1)</option>
    <option value="H1">Heading 7 (H1)</option>
    <option value="PRE">Preformatted (PRE)</option>
  </select>

  <div class="tbSeparator" unselectable='on'></div>

  <div class="tbButton" unselectable='on' ID="DECMD_BOLD" TITLE="Bold" TBTYPE="toggle" LANGUAGE="javascript" onclick="return DECMD_BOLD_onclick();">
    <img class="tbIcon" unselectable='on' src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/bold.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div class="tbButton" unselectable='on' ID="DECMD_ITALIC" TITLE="Italic" TBTYPE="toggle" LANGUAGE="javascript" onclick="return DECMD_ITALIC_onclick()">
    <img class="tbIcon" unselectable='on' src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/italic.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div class="tbButton" unselectable='on' ID="DECMD_UNDERLINE" TITLE="Underline" TBTYPE="toggle" LANGUAGE="javascript" onclick="return DECMD_UNDERLINE_onclick()">
    <img class="tbIcon" unselectable='on' src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/under.gif" WIDTH="23" HEIGHT="22">
  </div>
  
  <div class="tbSeparator" unselectable='on'></div>

  <div class="tbButton" unselectable='on' ID="DECMD_JUSTIFYLEFT" TITLE="Align Left" TBTYPE="toggle" NAME="Justify" LANGUAGE="javascript" onclick="return DECMD_JUSTIFYLEFT_onclick()">
    <img class="tbIcon" unselectable='on' src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/left.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div class="tbButton" unselectable='on' ID="DECMD_JUSTIFYCENTER" TITLE="Center" TBTYPE="toggle" NAME="Justify" LANGUAGE="javascript" onclick="return DECMD_JUSTIFYCENTER_onclick()">
    <img class="tbIcon" unselectable='on' src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/center.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div class="tbButton" unselectable='on' ID="DECMD_JUSTIFYRIGHT" TITLE="Align Right" TBTYPE="toggle" NAME="Justify" LANGUAGE="javascript" onclick="return DECMD_JUSTIFYRIGHT_onclick()">
    <img class="tbIcon" unselectable='on' src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/right.gif" WIDTH="23" HEIGHT="22">
  </div>

  <div class="tbSeparator" unselectable='on'></div>

  <div class="tbButton" unselectable='on' ID="DECMD_ORDERLIST" TITLE="Numbered List" TBTYPE="toggle" LANGUAGE="javascript" onclick="return DECMD_ORDERLIST_onclick()">
    <img class="tbIcon" unselectable='on' src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/numlist.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div class="tbButton" unselectable='on' ID="DECMD_UNORDERLIST" TITLE="Bulletted List" TBTYPE="toggle" LANGUAGE="javascript" onclick="return DECMD_UNORDERLIST_onclick()">
    <img class="tbIcon" unselectable='on' src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/bullist.gif" WIDTH="23" HEIGHT="22">
  </div>
  
  <div class="tbSeparator" unselectable='on'></div>

  <div class="tbButton" unselectable='on' ID="DECMD_OUTDENT" TITLE="Decrease Indent" LANGUAGE="javascript" onclick="return DECMD_OUTDENT_onclick()">
    <img class="tbIcon" unselectable='on' src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/deindent.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div class="tbButton" unselectable='on' ID="DECMD_INDENT" TITLE="Increase Indent" LANGUAGE="javascript" onclick="return DECMD_INDENT_onclick()">
    <img class="tbIcon" unselectable='on' src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/inindent.gif" WIDTH="23" HEIGHT="22">
  </div>

  <div class="tbSeparator" unselectable='on'></div>

  <div class="tbButton" unselectable='on' ID="DECMD_HYPERLINK" TITLE="Link" LANGUAGE="javascript" onclick="return DECMD_HYPERLINK_onclick()">
    <img class="tbIcon" unselectable='on' src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/link.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div class="tbButton" unselectable='on' ID="DECMD_IMAGE" TITLE="Insert Image" LANGUAGE="javascript" onclick="return DECMD_IMAGE_onclick()">
    <img class="tbIcon" unselectable='on' src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/image.gif" WIDTH="23" HEIGHT="22">
  </div>
</div>

<IFRAME ID="tbContentElement" CLASS="tbContentElement" unselectable='on' oldstyle="border: 1px inset buttonhighlight; background-color: white; padding: 8px; overflow: scroll;" <?php
  if (!$wgHTMLEditTemplate) {
    $wgHTMLEditTemplate="user.edit.page.html";
  }
  global $QUERY_STRING;
  if ($QUERY_STRING && (strpos($wgHTMLEditTemplate,"?") === false) ) {
    $wgHTMLEditTemplate.="?".$QUERY_STRING;
  }
  echo "SRC=\"$wgHTMLEditTemplate\"";
?>></IFRAME>

<!-- Toolbar Code File. Note: This must always be the last thing on the page -->
<script LANGUAGE="Javascript" SRC="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/Toolbars/toolbars.js">
</script>
<script LANGUAGE="Javascript">
  tbScriptletDefinitionFile = "<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/Toolbars/menubody.htm";
</script>
<script LANGUAGE="Javascript" SRC="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/Toolbars/tbmenus.js">
</script>

</body>
</html>
