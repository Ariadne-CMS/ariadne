<?php
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

<script ID="editorSettings" LANGUAGE="JavaScript">
  var buttons_disabled=new Array();
  var tbContentEditOptions=new Array();
  var wgSaveTmpl='<?php echo $wgHTMLEditSaveTemplate; ?>';
<?php
	// load editor.ini, in case the editor is started directly, not through the 
	// js.html file
	$options=$this->call("editor.ini");
	if (is_array($options)) {
		while (list($key, $value)=each($options)) {
			if (is_string($key)) {
				$skey='"'.$key.'"';
			} else {
				$skey=$key;
			}
			if (is_array($value)) {
				echo "  tbContentEditOptions[$skey]=new Array();\n";
				while (list($key2, $value2)=each($value)) {
					if (is_string($key2)) {
						$skey2='"'.$key2.'"';
					} else {
						$skey2=$key2;
					}
					echo "  tbContentEditOptions[$skey][$skey2]='".AddCSlashes($value2, ARESCAPE)."';\n";
				}
			} else {
				echo "  tbContentEditOptions[$skey]='".AddCSlashes($value, ARESCAPE)."';\n";
			}
		}
	}
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
  if (tbContentElement.onLoadHandler) {
	tbContentElement.onLoadHandler();
  }
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
  for ( var i=0; i<QueryStatusToolbarButtons.length; i++) {
    if (buttons_disabled[CommandCrossReference[QueryStatusToolbarButtons[i].command]]) {
      TBSetState(QueryStatusToolbarButtons[i].element, "gray"); 
    } else if(!tbContentElement.document.queryCommandState(QueryStatusToolbarButtons[i].command)) {
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


function SAVE_onclick(newurl) {
  savewindow=window.open(wgSaveTmpl+'?arReturnPage='+escape(newurl), 'savewindow', 'directories=no,height=100,width=300,location=no,status=no,toolbar=no,resizable=no');
  top.wgTBIsDirty=false;
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
  var args = new Array();
  var elIMG = false;
  var el = false;
  var rg = false;

  window.el=false;
  window.elIMG=false;
  window.rg=false;
  el=tbContentElement.document.selection;
  window.el=el;
  if (el.type=="Control") {
    elIMG=el.createRange().item(0);
    window.elIMG=elIMG;
    if (elIMG) {
      src=new String(elIMG.src);
      root=new String('<?php echo $this->store->root; ?>');
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
      args['src'] = src;
      args['border'] = elIMG.border;
      args['hspace'] = elIMG.hspace;
      args['vspace'] = elIMG.vspace;
      args['align'] = elIMG.align;
      args['alt'] = elIMG.alt;
    }
  } else {
    elIMG=false;
    window.rg=el.createRange();
    src = '<?php echo $this->path; ?>';
    args['src'] = src;
    args['hspace'] = "";
    args['vspace'] = "";
    args['align'] = ""; 
    args['alt'] = "";
    args['border'] = "";
  }
  args['editOptions']=tbContentEditOptions;
  arr = showModalDialog( "<?php echo $this->store->root.$AR->user->path; 
		?>edit.object.html.image.phtml", args,  "font-family:Verdana; font-size:12; dialogWidth:600px; dialogHeight:400px; status: no; resizable: yes;");
  if (arr != null){
	IMAGE_set(arr);
  }
}

function IMAGE_set(arr) {
  window.setfocusto=false;
  var el=window.el;
  if (arr != null) {
    src=new String(arr['src']);
    temp=new String('http://');
    if (src.substring(0,temp.length)!=temp) {
      src='<?php echo $this->store->root; ?>'+src;
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
    } else {
      el=window.el;
      if ((el.type=="None") || (el.type=="Text"))  {
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
        rg.pasteHTML(temp);
        rg.select();
      }
    }
  }
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

	oSel = tbContentElement.document.selection;
	oRange = oSel.createRange();
	sType=oSel.type;
    if (sType=="Control") {
		oElement=oRange.item(0);
		oParent=oRange.item(0).parentElement;
	} else {
		oParent=oRange.parentElement();
	}
	arr=null;
	args=new Array();
	//set a default value for your link button
	args["URL"] = "http:/"+"/";
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
	arr = showModalDialog( "edit.object.html.link.phtml", args,  "font-family:Verdana; font-size:12; dialogWidth:32em; dialogHeight:12em; status: no; resizable: yes;");
	if (arr != null){
	    if (!oATag && arr['URL']) {
			var newLink="<a href=\""+arr['URL']+"\"";
			if (arr['attributes']) {
				for (var i in arr['attributes']) {
					var arAttribute=arr['attributes'][i];
					newLink=newLink+" "+arAttribute.name+"=\""+arAttribute.value+"\"";
				}
			}
			newLink=newLink+">";
			if (sType=='Control') {
				oElement.outerHTML=newLink+oElement.outerHTML+"</A>";
			} else {
				
				// first let the msie set the link, since it is better in it.
				setFormat("CreateLink", arr['URL']);
				// now collapse the range, so even if the range overlaps a link partly, the parent
				// element will become the link. trust me.... 
				oRange.collapse();
				// now set the ATag object, so it can be 'fixed' with the extra attributes later
				oATag=oRange.parentElement();
			}
		}
		if (oATag && arr['URL']) {
			for (i=0; i<oATag.attributes.length; i++) {
				oldAttribute=oATag.attributes.item(i);
				var dummy=new String(oldAttribute.name);
				if ((dummy.substring(0,3)=='ar_') || (dummy.substring(0,3)=="ar:")) {
					oATag.removeAttribute(oldAttribute.name);
				}
			}
			oATag.href=arr['URL'];
			if (arr['attributes']) {
				for (var i in arr['attributes']) {
					var arAttribute=arr['attributes'][i];
					oATag.setAttribute(arAttribute.name, arAttribute.value);
				}
			}
		}
		if (oATag && !arr['URL']) {
			oATag.outerHTML=oATag.innerHTML;
//			unlink seems broken, when removing a link on an image, it also removes the image...
//			setFormat("UnLink");
		}
	}
	tbContentElement.focus();
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

function getContents(data_id) {
	var data="";
	if (data=tbContentElement.document.getElementById(data_id)) {
		return data.innerHTML;
	} else {
		return '';
	}
}

function getTextContents(data_id) {
	var data="";
	if (data=tbContentElement.document.getElementById(data_id)) {
		return data.innerText;
	} else {
		return '';
	}
}

function getValue(data_name) {
	var data="";
	var value='';
	if (data=tbContentElement.document.getElementById(data_name)) {
		switch (data.type) {
			case 'checkbox' :
				if (data.checked) {
					value=data.value;
				}
				break;
			case 'radio' :
				var radio=tbContentElement.document.all[data_name];
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
				value='';
				break;
		}
		return value;
	} else {
		return '';
	}
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
<body onload="return window_onload()" unselectable='on' tabIndex='-1'>

<!-- Toolbars -->
<div class="tbToolbar" ID="StandardToolbar" unselectable='on'>
  <div class="tbButton" ID="SAVE" unselectable='on' TITLE="Save File" LANGUAGE="javascript" onclick="return SAVE_onclick(0)">
    <img class="tbIcon" unselectable='on' src="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/images/save.gif" WIDTH="23" HEIGHT="22">
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

</div>

<div class="tbToolbar" unselectable='on' ID="FormatToolbar">
  <select ID="ParagraphStyle" class="tbGeneral" style="width:90" TITLE="Paragraph Format" LANGUAGE="javascript" onchange="return ParagraphStyle_onchange()">
    <option value="P">Normal (P)</option>
    <option value="H1">Heading 1 (H1)</option>
    <option value="H2">Heading 2 (H2)</option>
    <option value="H3">Heading 3 (H3)</option>
    <option value="H4">Heading 4 (H4)</option>
    <option value="H5">Heading 5 (H5)</option>
    <option value="H6">Heading 6 (H6)</option>
    <option value="H7">Heading 7 (H7)</option>
    <option value="PRE">Preformatted (PRE)</option>
  </select>

  <div class="tbSeparator" unselectable='on'></div>

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
</div>

<IFRAME ID="tbContentElement" CLASS="tbContentElement" unselectable='on' oldstyle="border: 1px inset buttonhighlight; background-color: white; padding: 8px; overflow: scroll;" <?php
  if (!$wgHTMLEditTemplate) {
    $wgHTMLEditTemplate="user.edit.page.html";
  }
  if (ldGetServerVar("QUERY_STRING") && (strpos($wgHTMLEditTemplate,"?") === false) ) {
    $wgHTMLEditTemplate.="?".ldGetServerVar("QUERY_STRING");
  }
  echo "SRC=\"$wgHTMLEditTemplate\"";
?>></IFRAME>

<!-- Toolbar Code File. Note: This must always be the last thing on the page -->
<script LANGUAGE="Javascript" SRC="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/Toolbars/toolbars.js">
</script>
<script LANGUAGE="Javascript">
  tbScriptletDefinitionFile = "<?php echo $AR->dir->www; ?>widgets/htmledit/ie/Toolbars/menubody.htm";
</script>
<script LANGUAGE="Javascript" SRC="<?php echo $AR->dir->www; ?>widgets/htmledit/ie/Toolbars/tbmenus.js">
</script>

</body>
</html>
