<!--TOOLBAR_START--><!--TOOLBAR_EXEMPT--><!--TOOLBAR_END-->
<!-- Copyright 2000 Microsoft Corporation. All rights reserved. -->
<!-- Author: Steve Isaac, Microsoft Corporation -->
<!-- Changes by Auke van Slooten, Muze V.O.F. to implement source view and add images from Ariadne. -->
<!-- Changes by Matt Finn, NetDesign Inc which make it work with non-english internet explorers (ParagraphStyles) -->
<!--This demo shows how to host the DHTML Editing component on a Web page. -->
<html>
<head>
<meta NAME="GENERATOR" CONTENT="Microsoft Visual Studio 6.0">
<title>Edit <?php echo $path.$file; ?></title>

<!-- Styles -->
<link REL="stylesheet" TYPE="text/css" HREF="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/Toolbars/toolbars.css">


<!-- Script Functions and Event Handlers -->
<script LANGUAGE="JavaScript" SRC="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/Inc/dhtmled.js">
</script>

<script ID="clientEventHandlersJS" LANGUAGE="javascript">
<!--
  window.exists=true; // do not reload editing environment if window still exists.
  tbContentRoot="<?php echo $root; ?>";
  tbContentPath="<?php echo $path; ?>";
  tbContentFile="<?php echo $file; ?>";
  tbContentName='<?php echo $name; ?>';  
  tbContentLanguage='<?php echo $language; ?>';
  tbContentType='<?php echo $type; ?>';
//
// Constants
//
var MENU_SEPARATOR = ""; // Context menu separator
var sHeader="<BODY STYLE=\"font:12pt times new roman,times,serif\">";

//
// Globals
//

var docComplete = false;
var initialDocComplete = false;

var QueryStatusToolbarButtons = new Array();
var QueryStatusEditMenu = new Array();
var QueryStatusFormatMenu = new Array();
var QueryStatusHTMLMenu = new Array();
var QueryStatusTableMenu = new Array();
var QueryStatusZOrderMenu = new Array();
var ContextMenu = new Array();
var GeneralContextMenu = new Array();
var TableContextMenu = new Array();
var AbsPosContextMenu = new Array();

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


//
// Event handlers
//
function window_onload() {

  // Initialze QueryStatus tables. These tables associate a command id with the
  // corresponding button object. Must be done on window load, 'cause the buttons must exist.
  QueryStatusToolbarButtons[0] = new QueryStatusItem(DECMD_BOLD, document.body.all["DECMD_BOLD"]);
  QueryStatusToolbarButtons[1] = new QueryStatusItem(DECMD_COPY, document.body.all["DECMD_COPY"]);
  QueryStatusToolbarButtons[2] = new QueryStatusItem(DECMD_CUT, document.body.all["DECMD_CUT"]);
  QueryStatusToolbarButtons[3] = new QueryStatusItem(DECMD_HYPERLINK, document.body.all["DECMD_HYPERLINK"]);
  QueryStatusToolbarButtons[4] = new QueryStatusItem(DECMD_INDENT, document.body.all["DECMD_INDENT"]);
  QueryStatusToolbarButtons[5] = new QueryStatusItem(DECMD_ITALIC, document.body.all["DECMD_ITALIC"]);
  QueryStatusToolbarButtons[6] = new QueryStatusItem(DECMD_JUSTIFYLEFT, document.body.all["DECMD_JUSTIFYLEFT"]);
  QueryStatusToolbarButtons[7] = new QueryStatusItem(DECMD_JUSTIFYCENTER, document.body.all["DECMD_JUSTIFYCENTER"]);
  QueryStatusToolbarButtons[8] = new QueryStatusItem(DECMD_JUSTIFYRIGHT, document.body.all["DECMD_JUSTIFYRIGHT"]);
  QueryStatusToolbarButtons[9] = new QueryStatusItem(DECMD_LOCK_ELEMENT, document.body.all["DECMD_LOCK_ELEMENT"]);
  QueryStatusToolbarButtons[10] = new QueryStatusItem(DECMD_MAKE_ABSOLUTE, document.body.all["DECMD_MAKE_ABSOLUTE"]);
  QueryStatusToolbarButtons[11] = new QueryStatusItem(DECMD_ORDERLIST, document.body.all["DECMD_ORDERLIST"]);
  QueryStatusToolbarButtons[12] = new QueryStatusItem(DECMD_OUTDENT, document.body.all["DECMD_OUTDENT"]);
  QueryStatusToolbarButtons[13] = new QueryStatusItem(DECMD_PASTE, document.body.all["DECMD_PASTE"]);
  QueryStatusToolbarButtons[14] = new QueryStatusItem(DECMD_REDO, document.body.all["DECMD_REDO"]);
  QueryStatusToolbarButtons[15] = new QueryStatusItem(DECMD_UNDERLINE, document.body.all["DECMD_UNDERLINE"]);
  QueryStatusToolbarButtons[16] = new QueryStatusItem(DECMD_UNDO, document.body.all["DECMD_UNDO"]);
  QueryStatusToolbarButtons[17] = new QueryStatusItem(DECMD_UNORDERLIST, document.body.all["DECMD_UNORDERLIST"]);
  QueryStatusToolbarButtons[18] = new QueryStatusItem(DECMD_INSERTTABLE, document.body.all["DECMD_INSERTTABLE"]);
  QueryStatusToolbarButtons[19] = new QueryStatusItem(DECMD_INSERTROW, document.body.all["DECMD_INSERTROW"]);
  QueryStatusToolbarButtons[20] = new QueryStatusItem(DECMD_DELETEROWS, document.body.all["DECMD_DELETEROWS"]);
  QueryStatusToolbarButtons[21] = new QueryStatusItem(DECMD_INSERTCOL, document.body.all["DECMD_INSERTCOL"]);
  QueryStatusToolbarButtons[22] = new QueryStatusItem(DECMD_DELETECOLS, document.body.all["DECMD_DELETECOLS"]);
  QueryStatusToolbarButtons[23] = new QueryStatusItem(DECMD_INSERTCELL, document.body.all["DECMD_INSERTCELL"]);
  QueryStatusToolbarButtons[24] = new QueryStatusItem(DECMD_DELETECELLS, document.body.all["DECMD_DELETECELLS"]);
  QueryStatusToolbarButtons[25] = new QueryStatusItem(DECMD_MERGECELLS, document.body.all["DECMD_MERGECELLS"]);
  QueryStatusToolbarButtons[26] = new QueryStatusItem(DECMD_SPLITCELL, document.body.all["DECMD_SPLITCELL"]);
  QueryStatusToolbarButtons[27] = new QueryStatusItem(DECMD_SETFORECOLOR, document.body.all["DECMD_SETFORECOLOR"]);
  QueryStatusToolbarButtons[28] = new QueryStatusItem(DECMD_SETBACKCOLOR, document.body.all["DECMD_SETBACKCOLOR"]);
  QueryStatusEditMenu[0] = new QueryStatusItem(DECMD_UNDO, document.body.all["EDIT_UNDO"]);
  QueryStatusEditMenu[1] = new QueryStatusItem(DECMD_REDO, document.body.all["EDIT_REDO"]);
  QueryStatusEditMenu[2] = new QueryStatusItem(DECMD_CUT, document.body.all["EDIT_CUT"]);
  QueryStatusEditMenu[3] = new QueryStatusItem(DECMD_COPY, document.body.all["EDIT_COPY"]);
  QueryStatusEditMenu[4] = new QueryStatusItem(DECMD_PASTE, document.body.all["EDIT_PASTE"]);
  QueryStatusEditMenu[5] = new QueryStatusItem(DECMD_DELETE, document.body.all["EDIT_DELETE"]);
  QueryStatusHTMLMenu[0] = new QueryStatusItem(DECMD_HYPERLINK, document.body.all["HTML_HYPERLINK"]);
  QueryStatusHTMLMenu[1] = new QueryStatusItem(DECMD_IMAGE, document.body.all["HTML_IMAGE"]);
  QueryStatusFormatMenu[0] = new QueryStatusItem(DECMD_FONT, document.body.all["FORMAT_FONT"]);
  QueryStatusFormatMenu[1] = new QueryStatusItem(DECMD_BOLD, document.body.all["FORMAT_BOLD"]);
  QueryStatusFormatMenu[2] = new QueryStatusItem(DECMD_ITALIC, document.body.all["FORMAT_ITALIC"]);
  QueryStatusFormatMenu[3] = new QueryStatusItem(DECMD_UNDERLINE, document.body.all["FORMAT_UNDERLINE"]);
  QueryStatusFormatMenu[4] = new QueryStatusItem(DECMD_JUSTIFYLEFT, document.body.all["FORMAT_JUSTIFYLEFT"]);
  QueryStatusFormatMenu[5] = new QueryStatusItem(DECMD_JUSTIFYCENTER, document.body.all["FORMAT_JUSTIFYCENTER"]);
  QueryStatusFormatMenu[6] = new QueryStatusItem(DECMD_JUSTIFYRIGHT, document.body.all["FORMAT_JUSTIFYRIGHT"]);
  QueryStatusFormatMenu[7] = new QueryStatusItem(DECMD_SETFORECOLOR, document.body.all["FORMAT_SETFORECOLOR"]);
  QueryStatusFormatMenu[8] = new QueryStatusItem(DECMD_SETBACKCOLOR, document.body.all["FORMAT_SETBACKCOLOR"]);
  QueryStatusTableMenu[0] = new QueryStatusItem(DECMD_INSERTTABLE, document.body.all["TABLE_INSERTTABLE"]);
  QueryStatusTableMenu[1] = new QueryStatusItem(DECMD_INSERTROW, document.body.all["TABLE_INSERTROW"]);
  QueryStatusTableMenu[2] = new QueryStatusItem(DECMD_DELETEROWS, document.body.all["TABLE_DELETEROW"]);
  QueryStatusTableMenu[3] = new QueryStatusItem(DECMD_INSERTCOL, document.body.all["TABLE_INSERTCOL"]);
  QueryStatusTableMenu[4] = new QueryStatusItem(DECMD_DELETECOLS, document.body.all["TABLE_DELETECOL"]);
  QueryStatusTableMenu[5] = new QueryStatusItem(DECMD_INSERTCELL, document.body.all["TABLE_INSERTCELL"]);
  QueryStatusTableMenu[6] = new QueryStatusItem(DECMD_DELETECELLS, document.body.all["TABLE_DELETECELL"]);
  QueryStatusTableMenu[7] = new QueryStatusItem(DECMD_MERGECELLS, document.body.all["TABLE_MERGECELL"]);
  QueryStatusTableMenu[8] = new QueryStatusItem(DECMD_SPLITCELL, document.body.all["TABLE_SPLITCELL"]);
  QueryStatusZOrderMenu[0] = new QueryStatusItem(DECMD_SEND_TO_BACK, document.body.all["ZORDER_SENDBACK"]);
  QueryStatusZOrderMenu[1] = new QueryStatusItem(DECMD_BRING_TO_FRONT, document.body.all["ZORDER_BRINGFRONT"]);
  QueryStatusZOrderMenu[2] = new QueryStatusItem(DECMD_SEND_BACKWARD, document.body.all["ZORDER_SENDBACKWARD"]);
  QueryStatusZOrderMenu[3] = new QueryStatusItem(DECMD_BRING_FORWARD, document.body.all["ZORDER_BRINGFORWARD"]);
  QueryStatusZOrderMenu[4] = new QueryStatusItem(DECMD_SEND_BELOW_TEXT, document.body.all["ZORDER_BELOWTEXT"]);
  QueryStatusZOrderMenu[5] = new QueryStatusItem(DECMD_BRING_ABOVE_TEXT, document.body.all["ZORDER_ABOVETEXT"]);
  
  // Initialize the context menu arrays.
  GeneralContextMenu[0] = new ContextMenuItem("Cut", DECMD_CUT);
  GeneralContextMenu[1] = new ContextMenuItem("Copy", DECMD_COPY);
  GeneralContextMenu[2] = new ContextMenuItem("Paste", DECMD_PASTE);
  TableContextMenu[0] = new ContextMenuItem(MENU_SEPARATOR, 0);
  TableContextMenu[1] = new ContextMenuItem("Insert Row", DECMD_INSERTROW);
  TableContextMenu[2] = new ContextMenuItem("Delete Rows", DECMD_DELETEROWS);
  TableContextMenu[3] = new ContextMenuItem(MENU_SEPARATOR, 0);
  TableContextMenu[4] = new ContextMenuItem("Insert Column", DECMD_INSERTCOL);
  TableContextMenu[5] = new ContextMenuItem("Delete Columns", DECMD_DELETECOLS);
  TableContextMenu[6] = new ContextMenuItem(MENU_SEPARATOR, 0);
  TableContextMenu[7] = new ContextMenuItem("Insert Cell", DECMD_INSERTCELL);
  TableContextMenu[8] = new ContextMenuItem("Delete Cells", DECMD_DELETECELLS);
  TableContextMenu[9] = new ContextMenuItem("Merge Cells", DECMD_MERGECELLS);
  TableContextMenu[10] = new ContextMenuItem("Split Cell", DECMD_SPLITCELL);
  AbsPosContextMenu[0] = new ContextMenuItem(MENU_SEPARATOR, 0);
  AbsPosContextMenu[1] = new ContextMenuItem("Send To Back", DECMD_SEND_TO_BACK);
  AbsPosContextMenu[2] = new ContextMenuItem("Bring To Front", DECMD_BRING_TO_FRONT);
  AbsPosContextMenu[3] = new ContextMenuItem(MENU_SEPARATOR, 0);
  AbsPosContextMenu[4] = new ContextMenuItem("Send Backward", DECMD_SEND_BACKWARD);
  AbsPosContextMenu[5] = new ContextMenuItem("Bring Forward", DECMD_BRING_FORWARD);
  AbsPosContextMenu[6] = new ContextMenuItem(MENU_SEPARATOR, 0);
  AbsPosContextMenu[7] = new ContextMenuItem("Send Below Text", DECMD_SEND_BELOW_TEXT);
  AbsPosContextMenu[8] = new ContextMenuItem("Bring Above Text", DECMD_BRING_ABOVE_TEXT);
  docComplete = false;

  var f=new ActiveXObject("DEGetBlockFmtNamesParam.DEGetBlockFmtNamesParam");

  tbContentElement.ExecCommand(DECMD_GETBLOCKFMTNAMES,OLECMDEXECOPT_DODEFAULT,f);

  vbarr = new VBArray(f.Names);
  arr = vbarr.toArray();

  for (var i=0;i<arr.length;i++) {
    ParagraphStyle.options[ParagraphStyle.options.length]=new Option(arr[i], arr[i]);
  }

  loadpage(tbContentRoot, tbContentPath, tbContentFile, tbContentName, tbContentLanguage, tbContentType);
}

window.onfocus=checkfocus;

function checkfocus() {
  if (window.setfocusto) {
    window.setfocusto.focus();
  }
  window.onfocus=checkfocus;
}

function loadpage(root, path, file, name, language, type) {
  // FIXME check isDirty and ask for save first.
  if (ViewHTML.TBSTATE=="unchecked") {
    VIEW_HTML_onclick();
  }
  // window.document.title='Edit '+path+file+' ( '+name+': '+language+')';
  tbContentRoot=root;
  tbContentPath=path;
  tbContentFile=file;
  tbContentName=name;
  tbContentLanguage=language;
  tbContentType=type;
  if (file) {
    file+='/';
  }
  tbContentElement.LoadURL(root+path+file+'show.'+name+'.phtml?language='+escape(language));
  tbContentElement.BaseURL=root+path;
  tbContentElement.focus();
}

function InsertTable() {
  var pVar = ObjTableInfo;
  var args = new Array();
  var arr = null;
   
  // Display table information dialog
  args["NumRows"] = ObjTableInfo.NumRows;
  args["NumCols"] = ObjTableInfo.NumCols;
  args["TableAttrs"] = ObjTableInfo.TableAttrs;
  args["CellAttrs"] = ObjTableInfo.CellAttrs;
  args["Caption"] = ObjTableInfo.Caption;
  
  arr = null;
  
  arr = showModalDialog( "<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/Inc/instable.htm",
                             args,
                             "font-family:Verdana; font-size:12; dialogWidth:36em; dialogHeight:25em");
  if (arr != null) {
  
    // Initialize table object
    for ( elem in arr ) {
      if ("NumRows" == elem && arr["NumRows"] != null) {
        ObjTableInfo.NumRows = arr["NumRows"];
      } else if ("NumCols" == elem && arr["NumCols"] != null) {
        ObjTableInfo.NumCols = arr["NumCols"];
      } else if ("TableAttrs" == elem) {
        ObjTableInfo.TableAttrs = arr["TableAttrs"];
      } else if ("CellAttrs" == elem) {
        ObjTableInfo.CellAttrs = arr["CellAttrs"];
      } else if ("Caption" == elem) {
        ObjTableInfo.Caption = arr["Caption"];
      }
    }
    tbContentElement.ExecCommand(DECMD_INSERTTABLE,OLECMDEXECOPT_DODEFAULT, pVar);  
  }
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

  // Is the selection inside a table? Add table menu if so
  if (tbContentElement.QueryStatus(DECMD_INSERTROW) != DECMDF_DISABLED) {
    for (i=0; i<TableContextMenu.length; i++) {
      ContextMenu[idx++] = TableContextMenu[i];
    }
  }

  // Is the selection on an absolutely positioned element? Add z-index commands if so
  if (tbContentElement.QueryStatus(DECMD_LOCK_ELEMENT) != DECMDF_DISABLED) {
    for (i=0; i<AbsPosContextMenu.length; i++) {
      ContextMenu[idx++] = AbsPosContextMenu[i];
    }
  }

  // Set up the actual arrays that get passed to SetContextMenu
  for (i=0; i<ContextMenu.length; i++) {
    menuStrings[i] = ContextMenu[i].string;
    if (menuStrings[i] != MENU_SEPARATOR) {
      state = tbContentElement.QueryStatus(ContextMenu[i].cmdId);
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
    tbContentElement.ExecCommand(ContextMenu[itemIndex].cmdId, OLECMDEXECOPT_DODEFAULT);
  }
}

// DisplayChanged handler. Very time-critical routine; this is called
// every time a character is typed. QueryStatus those toolbar buttons that need
// to be in synch with the current state of the document and update. 
function tbContentElement_DisplayChanged() {
  var i, s;

  for (i=0; i<QueryStatusToolbarButtons.length; i++) {
    s = tbContentElement.QueryStatus(QueryStatusToolbarButtons[i].command);
    if (s == DECMDF_DISABLED || s == DECMDF_NOTSUPPORTED) {
      TBSetState(QueryStatusToolbarButtons[i].element, "gray"); 
    } else if (s == DECMDF_ENABLED  || s == DECMDF_NINCHED) {
       TBSetState(QueryStatusToolbarButtons[i].element, "unchecked"); 
    } else { // DECMDF_LATCHED
       TBSetState(QueryStatusToolbarButtons[i].element, "checked");
    }
  }

  s = tbContentElement.QueryStatus(DECMD_GETBLOCKFMT);
  if (s == DECMDF_DISABLED || s == DECMDF_NOTSUPPORTED) {
    ParagraphStyle.disabled = true;
  } else {
    ParagraphStyle.disabled = false;
    ParagraphStyle.value = tbContentElement.ExecCommand(DECMD_GETBLOCKFMT, OLECMDEXECOPT_DODEFAULT);
  }
  s = tbContentElement.QueryStatus(DECMD_GETFONTNAME);
  if (s == DECMDF_DISABLED || s == DECMDF_NOTSUPPORTED) {
    FontName.disabled = true;
  } else {
    FontName.disabled = false;
    FontName.value = tbContentElement.ExecCommand(DECMD_GETFONTNAME, OLECMDEXECOPT_DODEFAULT);
  }
  
  if (s == DECMDF_DISABLED || s == DECMDF_NOTSUPPORTED) {
    FontSize.disabled = true;
  } else {
    FontSize.disabled = false;
    FontSize.value = tbContentElement.ExecCommand(DECMD_GETFONTSIZE, OLECMDEXECOPT_DODEFAULT);
  }
}

function MENU_FILE_SAVE_onclick() {
  if (ViewHTML.TBSTATE=="checked") {
    var sContents=tbContentElement.DocumentHTML;
  } else {
    var sContents=tbContentElement.DOM.body.innerText;
  }
  if (tbContentFile) {
    file=tbContentFile+'/';
  } else {
    file='';
  }
  savewindow=window.open('','savewindow','directories=no,height=100,width=300,location=no,status=no,toolbar=no,resizable=no');
  savewindow.document.open();
  savewindow.document.write("<html><body bgcolor=#CCCCCC><font face='Arial,helvetica,sans-serif'>");
  savewindow.document.write("<form method='POST' action='"+tbContentRoot+tbContentPath+file+"edit."+tbContentName+".save.phtml'>");
  savewindow.document.write("<input type='hidden' name='"+tbContentName+"'>");
  savewindow.document.write("<input type='hidden' name='ContentLanguage'>");
  savewindow.document.write("</form><br>Saving "+tbContentName+"</font></body></html>");
  savewindow.document.close();
  savewindow.document.forms[0][tbContentName].value=sContents;
  savewindow.document.forms[0].ContentLanguage.value=tbContentLanguage;
  savewindow.document.forms[0].submit();
}

function VIEW_HTML_onclick() {
  if (ViewHTML.TBSTATE=="checked") {

    TBSetState(ViewHTML, "unchecked");

//    var sContents=tbContentElement.DocumentHTML;
    if (tbContentElement.DOM.body.innerHTML) {
      var sContents=tbContentElement.FilterSourceCode(tbContentElement.DOM.body.innerHTML);
    } else {
      var sContents=new String();
    }
	// don't even think about changing the next few lines... 
	// the htmlediting component is extremely picky
    sContents=sContents.replace(/&/g,"&amp;");
    sContents=sContents.replace(/</g,"&lt;");
    sContents=sContents.replace(/>/g,"&gt;");  
	sContents=sContents.replace(/\r/g,""); // KILL KILL KILL
    sContents=new String("<html><head><style> p { margin: 0pt; } </style></head><BODY style=\"font:10pt courier, monospace\"><PRE>"+sContents+"</PRE></BODY></html>");
	// now you can edit anything you want...
    tbContentElement.DocumentHTML=sContents;
/*
    if (tbContentElement.DOM.styleSheets.length==0) {
      tbContentElement.DOM.createStyleSheet(tbContentRoot+tbContentPath+tbContentFile+'edit.css');
    } else {
      tbContentElement.DOM.styleSheets(0).href=tbContentRoot+tbContentPath+tbContentFile+'edit.css';
    }
*/
    ToolbarFormatState=FormatToolbar.TBSTATE;
    TBSetState(FormatToolbar, "hidden");
    TBSetState(ToolbarMenuFmt, "gray");    
    ToolbarAbsState=AbsolutePositioningToolbar.TBSTATE;
    TBSetState(AbsolutePositioningToolbar, "hidden");
    TBSetState(ToolbarMenuAbs, "gray");
    ToolbarTableState=TableToolbar.TBSTATE;
    TBSetState(TableToolbar, "hidden");
    TBSetState(ToolbarMenuTable, "gray");
  } else {
    TBSetState(FormatToolbar, ToolbarFormatState);
    if (ToolbarFormatState=="hidden") {
      TBSetState(ToolbarMenuFmt, "unchecked");
    } else {
      TBSetState(ToolbarMenuFmt, "checked");
    }
    TBSetState(AbsolutePositioningToolbar, ToolbarAbsState);
    if (ToolbarAbsState=="hidden") {
      TBSetState(ToolbarMenuAbs, "unchecked");
    } else {
      TBSetState(ToolbarMenuAbs, "checked");
    }
    TBSetState(TableToolbar, ToolbarTableState);
    if (ToolbarTableState=="hidden") {
      TBSetState(ToolbarMenuTable, "unchecked");
    } else {
      TBSetState(ToolbarMenuTable, "checked");
    }
    TBSetState(ViewHTML, "checked");

/*
    if (tbContentElement.DOM.styleSheets.length==0) {
      tbContentElement.DOM.createStyleSheet(tbContentRoot+tbContentPath+tbContentFile+'style.css');
    } else {
      tbContentElement.DOM.styleSheets(0).href=tbContentRoot+tbContentPath+tbContentFile+'style.css';
    }
*/
	// it's impossible to get the source back _with_ all 
    // the original indentation, except by:
    if (tbContentElement.DOM.body.innerText) {
      var sContents=tbContentElement.FilterSourceCode(tbContentElement.DOM.body.innerText);
    } else {
      var sContents=new String();
    }
    // sContents=sContents.replace(/\r/g,""); // and again! get rid of those pesky returns
    tbContentElement.DocumentHTML=sContents
    tbContentElement.BaseURL=tbContentRoot+tbContentPath;
  }
  TBRebuildMenu(ViewHTML.parentElement, true);
  tbContentElement.focus();
}

function DECMD_VISIBLEBORDERS_onclick() {
  tbContentElement.ShowBorders = !tbContentElement.ShowBorders;
  tbContentElement.focus();
}

function DECMD_UNORDERLIST_onclick() {
  tbContentElement.ExecCommand(DECMD_UNORDERLIST,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function DECMD_UNDO_onclick() {
  tbContentElement.ExecCommand(DECMD_UNDO,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function DECMD_UNDERLINE_onclick() {
  tbContentElement.ExecCommand(DECMD_UNDERLINE,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function DECMD_SNAPTOGRID_onclick() {
  tbContentElement.SnapToGrid = !tbContentElement.SnapToGrid;
  tbContentElement.focus();
}

function DECMD_SHOWDETAILS_onclick() {
  tbContentElement.ShowDetails = !tbContentElement.ShowDetails;
  tbContentElement.focus();
}

function DECMD_SETFORECOLOR_onclick() {
  var arr = showModalDialog( "<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/Inc/selcolor.htm",
                             "",
                             "font-family:Verdana; font-size:12; dialogWidth:30em; dialogHeight:34em" );

  if (arr != null) {
    tbContentElement.ExecCommand(DECMD_SETFORECOLOR,OLECMDEXECOPT_DODEFAULT, arr);
  }
}

function DECMD_SETBACKCOLOR_onclick() {
  var arr = showModalDialog( "<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/Inc/selcolor.htm",
                             "",
                             "font-family:Verdana; font-size:12; dialogWidth:30em; dialogHeight:34em" );

  if (arr != null) {
    tbContentElement.ExecCommand(DECMD_SETBACKCOLOR,OLECMDEXECOPT_DODEFAULT, arr);
  }
  tbContentElement.focus();
}

function DECMD_SELECTALL_onclick() {
  tbContentElement.ExecCommand(DECMD_SELECTALL,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function DECMD_REDO_onclick() {
  tbContentElement.ExecCommand(DECMD_REDO,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function DECMD_PASTE_onclick() {
  tbContentElement.ExecCommand(DECMD_PASTE,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function DECMD_OUTDENT_onclick() {
  tbContentElement.ExecCommand(DECMD_OUTDENT,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function DECMD_ORDERLIST_onclick() {
  tbContentElement.ExecCommand(DECMD_ORDERLIST,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function DECMD_MAKE_ABSOLUTE_onclick() {
  tbContentElement.ExecCommand(DECMD_MAKE_ABSOLUTE,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function DECMD_LOCK_ELEMENT_onclick() {
  tbContentElement.ExecCommand(DECMD_LOCK_ELEMENT,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function DECMD_JUSTIFYRIGHT_onclick() {
  tbContentElement.ExecCommand(DECMD_JUSTIFYRIGHT,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function DECMD_JUSTIFYLEFT_onclick() {
  tbContentElement.ExecCommand(DECMD_JUSTIFYLEFT,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function DECMD_JUSTIFYCENTER_onclick() {
  tbContentElement.ExecCommand(DECMD_JUSTIFYCENTER,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function DECMD_ITALIC_onclick() {
  tbContentElement.ExecCommand(DECMD_ITALIC,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function DECMD_INDENT_onclick() {
  tbContentElement.ExecCommand(DECMD_INDENT,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
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
  el=tbContentElement.DOM.selection;
  window.el=el;
  if (el.type=="Control") {
    elIMG=el.createRange().item(0);
    window.elIMG=elIMG;
    if (elIMG) {
      // alert(elIMG.outerHTML);
      src=new String(elIMG.src);
      root=new String('<?php echo $AR->host.$this->store->root; ?>');
      if (src.substring(0,root.length)==root) {
        src=src.substring(root.length);
      } else { // htmledit component automatically adds http://
        temp=new String('http://<?php echo $this->store->root; ?>');
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

  imgwindow=window.open("<?php echo $this->store->root.$this->path; 
    ?>edit.object.html.image.phtml?src="+escape(args['src'])+
    "&border="+escape(args['border'])+"&hspace="+escape(args['hspace'])+
    "&vspace="+escape(args['vspace'])+"&align="+escape(args['align'])+
    "&alt="+escape(args['alt']),"imgwindow","directories=no,height=160,width=425,location=no,menubar=no,status=no,toolbar=no,resizable=yes");
  imgwindow.focus();
  window.setfocusto=imgwindow;
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
      if (arr['border']!='') {
        elIMG.border=arr['border'];
      }
      if (arr['hspace']!='') {
        elIMG.hspace=arr['hspace'];
      }
      if (arr['vspace']!='') {
        elIMG.vspace=arr['vspace'];
      }
      if (arr['align']!='') {
        elIMG.align=arr['align'];
      }
      if (arr['alt']!='') {
        elIMG.alt=arr['alt'];
      }
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

function DECMD_HYPERLINK_onclick() {
  tbContentElement.ExecCommand(DECMD_HYPERLINK,OLECMDEXECOPT_PROMPTUSER);
  tbContentElement.focus();
}

function DECMD_FINDTEXT_onclick() {
  tbContentElement.ExecCommand(DECMD_FINDTEXT,OLECMDEXECOPT_PROMPTUSER);
  tbContentElement.focus();
}

function DECMD_DELETE_onclick() {
  tbContentElement.ExecCommand(DECMD_DELETE,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function DECMD_CUT_onclick() {
  tbContentElement.ExecCommand(DECMD_CUT,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function DECMD_COPY_onclick() {
  tbContentElement.ExecCommand(DECMD_COPY,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function DECMD_BOLD_onclick() {
  tbContentElement.ExecCommand(DECMD_BOLD,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function OnMenuShow(QueryStatusArray, menu) {
  var i, s;
 
  for (i=0; i<QueryStatusArray.length; i++) {
    s = tbContentElement.QueryStatus(QueryStatusArray[i].command);
    if (s == DECMDF_DISABLED || s == DECMDF_NOTSUPPORTED) {
      TBSetState(QueryStatusArray[i].element, "gray"); 
    } else if (s == DECMDF_ENABLED  || s == DECMDF_NINCHED) {
       TBSetState(QueryStatusArray[i].element, "unchecked"); 
    } else { // DECMDF_LATCHED
       TBSetState(QueryStatusArray[i].element, "checked");
    }
  }

  // If the menu is the HTML menu, then
  // check if the selection type is "Control", if so,
  // set menu item state of the Intrinsics submenu and rebuild the menu.
  if (QueryStatusArray[0].command == DECMD_HYPERLINK) { 
    for (i=0; i < HTML_INTRINSICS.all.length; i++) {
      if (HTML_INTRINSICS.all[i].className == "tbMenuItem") {    
        if (tbContentElement.DOM.selection.type == "Control") {
            TBSetState(HTML_INTRINSICS.all[i], "gray");  
        } else {
            TBSetState(HTML_INTRINSICS.all[i], "unchecked");  
        }
      }
    }
  }

  // rebuild the menu so that menu item states will be reflected
  TBRebuildMenu(menu, true);
  
  tbContentElement.focus();
}

function INTRINSICS_onclick(html) {
  var selection;
  
  selection = tbContentElement.DOM.selection.createRange();
  selection.pasteHTML(html);
  tbContentElement.focus();
}

function TABLE_DELETECELL_onclick() {
  tbContentElement.ExecCommand(DECMD_DELETECELLS,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function TABLE_DELETECOL_onclick() {
  tbContentElement.ExecCommand(DECMD_DELETECOLS,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function TABLE_DELETEROW_onclick() {
  tbContentElement.ExecCommand(DECMD_DELETEROWS,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function TABLE_INSERTCELL_onclick() {
  tbContentElement.ExecCommand(DECMD_INSERTCELL,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function TABLE_INSERTCOL_onclick() {
  tbContentElement.ExecCommand(DECMD_INSERTCOL,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function TABLE_INSERTROW_onclick() {
  tbContentElement.ExecCommand(DECMD_INSERTROW,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function TABLE_INSERTTABLE_onclick() {
  InsertTable();
  tbContentElement.focus();
}

function TABLE_MERGECELL_onclick() {
  tbContentElement.ExecCommand(DECMD_MERGECELLS,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function TABLE_SPLITCELL_onclick() {
  tbContentElement.ExecCommand(DECMD_SPLITCELL,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function FORMAT_FONT_onclick() {
  tbContentElement.ExecCommand(DECMD_FONT,OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function ZORDER_ABOVETEXT_onclick() {
  tbContentElement.ExecCommand(DECMD_BRING_ABOVE_TEXT, OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function ZORDER_BELOWTEXT_onclick() {
  tbContentElement.ExecCommand(DECMD_SEND_BELOW_TEXT, OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function ZORDER_BRINGFORWARD_onclick() {
  tbContentElement.ExecCommand(DECMD_BRING_FORWARD, OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function ZORDER_BRINGFRONT_onclick() {
  tbContentElement.ExecCommand(DECMD_BRING_TO_FRONT, OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function ZORDER_SENDBACK_onclick() {
  tbContentElement.ExecCommand(DECMD_SEND_TO_BACK, OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function ZORDER_SENDBACKWARD_onclick() {
  tbContentElement.ExecCommand(DECMD_SEND_BACKWARD, OLECMDEXECOPT_DODEFAULT);
  tbContentElement.focus();
}

function TOOLBARS_onclick(toolbar, menuItem) {
  if (toolbar.TBSTATE == "hidden") {
    TBSetState(toolbar, "dockedTop");
    TBSetState(menuItem, "checked");
  } else {
    TBSetState(toolbar, "hidden");
    TBSetState(menuItem, "unchecked");
  }
  
  TBRebuildMenu(menuItem.parentElement, false);

  tbContentElement.focus();
}

function ParagraphStyle_onchange() {	 
  tbContentElement.ExecCommand(DECMD_SETBLOCKFMT, OLECMDEXECOPT_DODEFAULT, ParagraphStyle.value);
  tbContentElement.focus();
}

function FontName_onchange() {
  tbContentElement.ExecCommand(DECMD_SETFONTNAME, OLECMDEXECOPT_DODEFAULT, FontName.value);
  tbContentElement.focus();
}

function FontSize_onchange() {
  tbContentElement.ExecCommand(DECMD_SETFONTSIZE, OLECMDEXECOPT_DODEFAULT, parseInt(FontSize.value));
  tbContentElement.focus();
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

<script LANGUAGE="javascript" FOR="tbContentElement" EVENT="DisplayChanged">
<!--
return tbContentElement_DisplayChanged()
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
<body LANGUAGE="javascript" onload="return window_onload()">

<!-- Toolbars -->
<div class="tbToolbar" ID="MenuBar">
  <div class="tbMenu" ID="FILE">
    File
    <div class="tbMenuItem" ID="FILE_SAVE" LANGUAGE="javascript" onclick="return MENU_FILE_SAVE_onclick()">
      Save File
      <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/save.gif" WIDTH="23" HEIGHT="22">
    </div>
  </div> 
  
  <div class="tbMenu" ID="EDIT" LANGUAGE="javascript" tbOnMenuShow="return OnMenuShow(QueryStatusEditMenu, EDIT)">
    Edit
    <div class="tbMenuItem" ID="EDIT_UNDO" LANGUAGE="javascript" onclick="return DECMD_UNDO_onclick()">
      Undo
      <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/undo.gif" WIDTH="23" HEIGHT="22">
    </div>
    <div class="tbMenuItem" ID="EDIT_REDO" LANGUAGE="javascript" onclick="return DECMD_REDO_onclick()">
      Redo
      <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/redo.gif" WIDTH="23" HEIGHT="22">
    </div>

    <div class="tbSeparator"></div>

    <div class="tbMenuItem" ID="EDIT_CUT" LANGUAGE="javascript" onclick="return DECMD_CUT_onclick()">
      Cut
      <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/cut.gif" WIDTH="23" HEIGHT="22">
    </div>
    <div class="tbMenuItem" ID="EDIT_COPY" LANGUAGE="javascript" onclick="return DECMD_COPY_onclick()">
      Copy
      <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/copy.gif" WIDTH="23" HEIGHT="22">
    </div>
    <div class="tbMenuItem" ID="EDIT_PASTE" LANGUAGE="javascript" onclick="return DECMD_PASTE_onclick()">
      Paste
      <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/paste.gif" WIDTH="23" HEIGHT="22">
    </div>
    <div class="tbMenuItem" ID="EDIT_DELETE" LANGUAGE="javascript" onclick="return DECMD_DELETE_onclick()">
      Delete
      <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/delete.gif" WIDTH="23" HEIGHT="22">
    </div>

    <div class="tbSeparator"></div>
    
    <div class="tbMenuItem" ID="EDIT_SELECTALL" LANGUAGE="javascript" onclick="return DECMD_SELECTALL_onclick()">
      Select All
    </div>

    <div class="tbSeparator"></div>

    <div class="tbMenuItem" ID="EDIT_FINDTEXT" TITLE="Find" LANGUAGE="javascript" onclick="return DECMD_FINDTEXT_onclick()">
      Find...
      <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/find.gif" WIDTH="23" HEIGHT="22">
    </div>
  </div>
  
  <div class="tbMenu" ID="VIEW">
    View
    <div class="tbSubmenu" TBTYPE="toggle" ID="VIEW_TOOLBARS">
      Toolbars
      <div class="tbMenuItem" id="ToolbarMenuStd" TBTYPE="toggle" TBSTATE="checked" ID="TOOLBARS_STANDARD" TBTYPE="toggle" LANGUAGE="javascript" onclick="return TOOLBARS_onclick(StandardToolbar, ToolbarMenuStd)">
        Standard
      </div>
      <div class="tbMenuItem" id="ToolbarMenuFmt" TBTYPE="toggle" TBSTATE="checked" ID="TOOLBARS_FORMAT" TBTYPE="toggle" LANGUAGE="javascript" onclick="return TOOLBARS_onclick(FormatToolbar, ToolbarMenuFmt)">
        Formatting
      </div>
      <div class="tbMenuItem" id="ToolbarMenuAbs" TBTYPE="toggle" TBSTATE="unchecked" ID="TOOLBARS_ZORDER" TBTYPE="toggle" LANGUAGE="javascript" onclick="return TOOLBARS_onclick(AbsolutePositioningToolbar, ToolbarMenuAbs)">
        Absolute Positioning
      </div>
      <div class="tbMenuItem" id="ToolbarMenuTable" TBTYPE="toggle" TBSTATE="unchecked" ID="TOOLBARS_TABLE" TBTYPE="toggle" LANGUAGE="javascript" onclick="return TOOLBARS_onclick(TableToolbar, ToolbarMenuTable)">
        Table
      </div>
    </div>

    <div class="tbSeparator"></div>

    <div class="tbMenuItem" id="ViewHTML" TBTYPE="toggle" TBSTATE="checked" ID="VIEW_HTML" TBTYPE="toggle" LANGUAGE="javascript" onclick="return VIEW_HTML_onclick()">
      WYSIWYG
    </div>
  </div> 
  
  <div class="tbMenu" ID="FORMAT" LANGUAGE="javascript" tbOnMenuShow="return OnMenuShow(QueryStatusFormatMenu, FORMAT)">
    Format
    <div class="tbMenuItem" ID="FORMAT_FONT" LANGUAGE="javascript" onclick="return FORMAT_FONT_onclick()">
      Font...
    </div>
  
    <div class="tbSeparator"></div>

    <div class="tbMenuItem" ID="FORMAT_BOLD" TBTYPE="toggle" LANGUAGE="javascript" onclick="return DECMD_BOLD_onclick()">
      Bold 
      <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/bold.gif" WIDTH="23" HEIGHT="22">
    </div>
    <div class="tbMenuItem" ID="FORMAT_ITALIC" TBTYPE="toggle" LANGUAGE="javascript" onclick="return DECMD_ITALIC_onclick()">
      Italic
      <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/italic.gif" WIDTH="23" HEIGHT="22">
    </div>
    <div class="tbMenuItem" ID="FORMAT_UNDERLINE" TBTYPE="toggle" LANGUAGE="javascript" onclick="return DECMD_UNDERLINE_onclick()">
      Underline
      <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/under.gif" WIDTH="23" HEIGHT="22">
    </div>
  
    <div class="tbSeparator"></div>

    <div class="tbMenuItem" ID="FORMAT_SETFORECOLOR" LANGUAGE="javascript" onclick="return DECMD_SETFORECOLOR_onclick()">
      Set Foreground Color...
      <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/fgcolor.gif" WIDTH="23" HEIGHT="22">
    </div>
    <div class="tbMenuItem" ID="FORMAT_SETBACKCOLOR" LANGUAGE="javascript" onclick="return DECMD_SETBACKCOLOR_onclick()">
      Set Background Color...
      <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/bgcolor.gif" WIDTH="23" HEIGHT="22">
    </div>
  
    <div class="tbSeparator"></div>

    <div class="tbMenuItem" ID="FORMAT_JUSTIFYLEFT" TBTYPE="radio" NAME="Justify" LANGUAGE="javascript" onclick="return DECMD_JUSTIFYLEFT_onclick()">
      Align Left
      <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/left.gif" WIDTH="23" HEIGHT="22">
    </div>
    <div class="tbMenuItem" ID="FORMAT_JUSTIFYCENTER" TBTYPE="radio" NAME="Justify" LANGUAGE="javascript" onclick="return DECMD_JUSTIFYCENTER_onclick()">
      Center
      <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/center.gif" WIDTH="23" HEIGHT="22">
    </div>
    <div class="tbMenuItem" ID="FORMAT_JUSTIFYRIGHT" TBTYPE="radio" NAME="Justify" LANGUAGE="javascript" onclick="return DECMD_JUSTIFYRIGHT_onclick()">
      Align Right
      <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/right.gif" WIDTH="23" HEIGHT="22">
    </div> 
  </div>   
  
  <div class="tbMenu" ID="HTML" LANGUAGE="javascript" tbOnMenuShow="return OnMenuShow(QueryStatusHTMLMenu, HTML)">
    HTML
    <div class="tbMenuItem" ID="HTML_HYPERLINK" LANGUAGE="javascript" onclick="return DECMD_HYPERLINK_onclick()">
      Link...
      <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/link.gif" WIDTH="23" HEIGHT="22">
    </div>
    <div class="tbMenuItem" ID="HTML_IMAGE" LANGUAGE="javascript" onclick="return DECMD_IMAGE_onclick()">
      Image...
      <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/image.gif" WIDTH="23" HEIGHT="22">
    </div>

    <div class="tbSeparator"></div>

    <div class="tbSubmenu" ID="HTML_INTRINSICS">
      Intrinsics
      <div class="tbMenuItem" ID="INTRINSICS_TEXTBOX" LANGUAGE="javascript" onclick="return INTRINSICS_onclick('&lt;INPUT type=text&gt;')">
        Textbox
      </div>
      <div class="tbMenuItem" ID="INTRINSICS_PASSWRD" LANGUAGE="javascript" onclick="return INTRINSICS_onclick('&lt;INPUT type=password&gt;')">
        Password
      </div>
      <div class="tbMenuItem" ID="INTRINSICS_FILE" LANGUAGE="javascript" onclick="return INTRINSICS_onclick('&lt;INPUT type=file&gt;')">
        File Field
      </div>
      <div class="tbMenuItem" ID="INTRINSICS_TEXTAREA" LANGUAGE="javascript" onclick="return INTRINSICS_onclick('&lt;TEXTAREA rows=2 cols=20&gt;&lt;/TEXTAREA&gt;')">
        Text Area
      </div>

      <div class="tbSeparator"></div>

      <div class="tbMenuItem" ID="INTRINSICS_CHECKBOX" LANGUAGE="javascript" onclick="return INTRINSICS_onclick('&lt;INPUT type=checkbox&gt;')">
        Checkbox
      </div>
      <div class="tbMenuItem" ID="INTRINSICS_RADIO" LANGUAGE="javascript" onclick="return INTRINSICS_onclick('&lt;INPUT type=radio&gt;')">
        Radio Button
      </div>
 
      <div class="tbSeparator"></div>

      <div class="tbMenuItem" ID="INTRINSICS_DROPDOWN" LANGUAGE="javascript" onclick="return INTRINSICS_onclick('&lt;SELECT&gt;&lt;/SELECT&gt;')">
        Dropdown
      </div>
      <div class="tbMenuItem" ID="INTRINSICS_LISTBOX" LANGUAGE="javascript" onclick="return INTRINSICS_onclick('&lt;SELECT size=2&gt;&lt;/SELECT&gt;')">
        Listbox
      </div>
 
      <div class="tbSeparator"></div>

      <div class="tbMenuItem" ID="INTRINSICS_BUTTON" LANGUAGE="javascript" onclick="return INTRINSICS_onclick('&lt;INPUT type=button value=Button&gt;')">
        Button
      </div>
      <div class="tbMenuItem" ID="INTRINSICS_SUBMIT" LANGUAGE="javascript" onclick="return INTRINSICS_onclick('&lt;INPUT type=submit value=Submit&gt;')">
        Submit Button
      </div>
      <div class="tbMenuItem" ID="INTRINSICS_RESET" LANGUAGE="javascript" onclick="return INTRINSICS_onclick('&lt;INPUT type=reset value=Reset&gt;')">
        Reset Button
      </div>
    </div>
  </div>

</div>

<div class="tbToolbar" ID="StandardToolbar">
  <div class="tbButton" ID="MENU_FILE_SAVE" TITLE="Save File" LANGUAGE="javascript" onclick="return MENU_FILE_SAVE_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/save.gif" WIDTH="23" HEIGHT="22">
  </div>
  
  <div class="tbSeparator"></div>

  <div class="tbButton" ID="DECMD_CUT" TITLE="Cut" LANGUAGE="javascript" onclick="return DECMD_CUT_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/cut.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div class="tbButton" ID="DECMD_COPY" TITLE="Copy" LANGUAGE="javascript" onclick="return DECMD_COPY_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/copy.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div class="tbButton" ID="DECMD_PASTE" TITLE="Paste" LANGUAGE="javascript" onclick="return DECMD_PASTE_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/paste.gif" WIDTH="23" HEIGHT="22">
  </div>

  <div class="tbSeparator"></div>

  <div class="tbButton" ID="DECMD_UNDO" TITLE="Undo" LANGUAGE="javascript" onclick="return DECMD_UNDO_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/undo.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div class="tbButton" ID="DECMD_REDO" TITLE="Redo" LANGUAGE="javascript" onclick="return DECMD_REDO_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/redo.gif" WIDTH="23" HEIGHT="22">
  </div>

  <div class="tbSeparator"></div>

  <div class="tbButton" ID="DECMD_FINDTEXT" TITLE="Find" LANGUAGE="javascript" onclick="return DECMD_FINDTEXT_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/find.gif" WIDTH="23" HEIGHT="22">
  </div>
</div>

<div class="tbToolbar" ID="FormatToolbar">
  <select ID="ParagraphStyle" class="tbGeneral" style="width:90" TITLE="Paragraph Format" LANGUAGE="javascript" onchange="return ParagraphStyle_onchange()">
  </select>
  <select ID="FontName" class="tbGeneral" style="width:140" TITLE="Font Name" LANGUAGE="javascript" onchange="return FontName_onchange()">
    <option value="Arial">Arial
    <option value="Tahoma">Tahoma
    <option value="Courier New">Courier New
    <option value="Times New Roman">Times New Roman
    <option value="Wingdings">Wingdings
  </select>
  <select ID="FontSize" class="tbGeneral" style="width:40" TITLE="Font Size" LANGUAGE="javascript" onchange="return FontSize_onchange()">
    <option value="1">1
    <option value="2">2
    <option value="3">3
    <option value="4">4
    <option value="5">5
    <option value="6">6
    <option value="7">7
  </select>
  
  <div class="tbSeparator"></div>

  <div class="tbButton" ID="DECMD_BOLD" TITLE="Bold" TBTYPE="toggle" LANGUAGE="javascript" onclick="return DECMD_BOLD_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/bold.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div class="tbButton" ID="DECMD_ITALIC" TITLE="Italic" TBTYPE="toggle" LANGUAGE="javascript" onclick="return DECMD_ITALIC_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/italic.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div class="tbButton" ID="DECMD_UNDERLINE" TITLE="Underline" TBTYPE="toggle" LANGUAGE="javascript" onclick="return DECMD_UNDERLINE_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/under.gif" WIDTH="23" HEIGHT="22">
  </div>
  
  <div class="tbSeparator"></div>

  <div class="tbButton" ID="DECMD_SETFORECOLOR" TITLE="Foreground Color" LANGUAGE="javascript" onclick="return DECMD_SETFORECOLOR_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/fgcolor.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div class="tbButton" ID="DECMD_SETBACKCOLOR" TITLE="Background Color" LANGUAGE="javascript" onclick="return DECMD_SETBACKCOLOR_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/bgcolor.gif" WIDTH="23" HEIGHT="22">
  </div>
  
  <div class="tbSeparator"></div>

  <div class="tbButton" ID="DECMD_JUSTIFYLEFT" TITLE="Align Left" TBTYPE="toggle" NAME="Justify" LANGUAGE="javascript" onclick="return DECMD_JUSTIFYLEFT_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/left.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div class="tbButton" ID="DECMD_JUSTIFYCENTER" TITLE="Center" TBTYPE="toggle" NAME="Justify" LANGUAGE="javascript" onclick="return DECMD_JUSTIFYCENTER_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/center.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div class="tbButton" ID="DECMD_JUSTIFYRIGHT" TITLE="Align Right" TBTYPE="toggle" NAME="Justify" LANGUAGE="javascript" onclick="return DECMD_JUSTIFYRIGHT_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/right.gif" WIDTH="23" HEIGHT="22">
  </div>

  <div class="tbSeparator"></div>

  <div class="tbButton" ID="DECMD_ORDERLIST" TITLE="Numbered List" TBTYPE="toggle" LANGUAGE="javascript" onclick="return DECMD_ORDERLIST_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/numlist.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div class="tbButton" ID="DECMD_UNORDERLIST" TITLE="Bulletted List" TBTYPE="toggle" LANGUAGE="javascript" onclick="return DECMD_UNORDERLIST_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/bullist.gif" WIDTH="23" HEIGHT="22">
  </div>
  
  <div class="tbSeparator"></div>

  <div class="tbButton" ID="DECMD_OUTDENT" TITLE="Decrease Indent" LANGUAGE="javascript" onclick="return DECMD_OUTDENT_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/deindent.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div class="tbButton" ID="DECMD_INDENT" TITLE="Increase Indent" LANGUAGE="javascript" onclick="return DECMD_INDENT_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/inindent.gif" WIDTH="23" HEIGHT="22">
  </div>

  <div class="tbSeparator"></div>

  <div class="tbButton" ID="DECMD_HYPERLINK" TITLE="Link" LANGUAGE="javascript" onclick="return DECMD_HYPERLINK_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/link.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div class="tbButton" ID="DECMD_IMAGE" TITLE="Insert Image" LANGUAGE="javascript" onclick="return DECMD_IMAGE_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/image.gif" WIDTH="23" HEIGHT="22">
  </div>
</div>


<div class="tbToolbar" ID="AbsolutePositioningToolbar" TBSTATE="hidden">
  <div class="tbButton" ID="DECMD_VISIBLEBORDERS" TITLE="Visible Borders" TBTYPE="toggle" LANGUAGE="javascript" onclick="return DECMD_VISIBLEBORDERS_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/borders.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div class="tbButton" ID="DECMD_SHOWDETAILS" TITLE="Show Details" TBTYPE="toggle" LANGUAGE="javascript" onclick="return DECMD_SHOWDETAILS_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/details.gif" WIDTH="23" HEIGHT="22">
  </div>
  
  <div class="tbSeparator"></div>

  <div class="tbButton" ID="DECMD_MAKE_ABSOLUTE" TBTYPE="toggle" LANGUAGE="javascript" TITLE="Make Absolute" onclick="return DECMD_MAKE_ABSOLUTE_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/abspos.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div class="tbButton" ID="DECMD_LOCK_ELEMENT" TBTYPE="toggle" LANGUAGE="javascript" TITLE="Lock" onclick="return DECMD_LOCK_ELEMENT_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/lock.gif" WIDTH="23" HEIGHT="22">
  </div>
  
  <div class="tbSeparator"></div>
  
  <div class="tbMenu" ID="ZORDER" LANGUAGE="javascript" tbOnMenuShow="return OnMenuShow(QueryStatusZOrderMenu, ZORDER)">
  Z Order
    <div class="tbMenuItem" ID="ZORDER_BRINGFRONT" LANGUAGE="javascript" onclick="return ZORDER_BRINGFRONT_onclick()">
      Bring to Front
    </div>
    <div class="tbMenuItem" ID="ZORDER_SENDBACK" LANGUAGE="javascript" onclick="return ZORDER_SENDBACK_onclick()">
      Send to Back
    </div>
 
    <div class="tbSeparator"></div>

    <div class="tbMenuItem" ID="ZORDER_BRINGFORWARD" LANGUAGE="javascript" onclick="return ZORDER_BRINGFORWARD_onclick()">
      Bring Forward
    </div>
    <div class="tbMenuItem" ID="ZORDER_SENDBACKWARD" LANGUAGE="javascript" onclick="return ZORDER_SENDBACKWARD_onclick()">
      Send Backward
    </div>
 
    <div class="tbSeparator"></div>

    <div class="tbMenuItem" ID="ZORDER_BELOWTEXT" LANGUAGE="javascript" onclick="return ZORDER_BELOWTEXT_onclick()">
      Below Text
    </div>
    <div class="tbMenuItem" ID="ZORDER_ABOVETEXT" LANGUAGE="javascript" onclick="return ZORDER_ABOVETEXT_onclick()">
      Above Text
    </div>
  </div>
  
  <div class="tbSeparator"></div>
  
  <div class="tbButton" ID="DECMD_SNAPTOGRID" TITLE="Snap to Grid" TBTYPE="toggle" LANGUAGE="javascript" onclick="return DECMD_SNAPTOGRID_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/snapgrid.gif" WIDTH="23" HEIGHT="22">
  </div>
</div>

<div class="tbToolbar" ID="TableToolbar" TBSTATE="hidden">
  <div class="tbButton" ID="DECMD_INSERTTABLE" TITLE="Insert Table" LANGUAGE="javascript" onclick="return TABLE_INSERTTABLE_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/instable.gif" WIDTH="23" HEIGHT="22">
  </div>

  <div class="tbSeparator"></div>

  <div class="tbButton" ID="DECMD_INSERTROW" TITLE="Insert Row" LANGUAGE="javascript" onclick="return TABLE_INSERTROW_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/insrow.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div class="tbButton" ID="DECMD_DELETEROWS" TITLE="Delete Rows" LANGUAGE="javascript" onclick="return TABLE_DELETEROW_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/delrow.gif" WIDTH="23" HEIGHT="22">
  </div>
 
  <div class="tbSeparator"></div>

  <div class="tbButton" ID="DECMD_INSERTCOL" TITLE="Insert Column" LANGUAGE="javascript" onclick="return TABLE_INSERTCOL_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/inscol.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div class="tbButton" ID="DECMD_DELETECOLS" TITLE="Delete Columns" LANGUAGE="javascript" onclick="return TABLE_DELETECOL_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/delcol.gif" WIDTH="23" HEIGHT="22">
  </div>
  
  <div class="tbSeparator"></div>

  <div class="tbButton" ID="DECMD_INSERTCELL" TITLE="Insert Cell" LANGUAGE="javascript" onclick="return TABLE_INSERTCELL_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/inscell.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div class="tbButton" ID="DECMD_DELETECELLS" TITLE="Delete Cells" LANGUAGE="javascript" onclick="return TABLE_DELETECELL_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/delcell.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div class="tbButton" ID="DECMD_MERGECELLS" TITLE="Merge Cells" LANGUAGE="javascript" onclick="return TABLE_MERGECELL_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/mrgcell.gif" WIDTH="23" HEIGHT="22">
  </div>
  <div class="tbButton" ID="DECMD_SPLITCELL" TITLE="Split Cells" LANGUAGE="javascript" onclick="return TABLE_SPLITCELL_onclick()">
    <img class="tbIcon" src="<?php echo $AR->host.$AR->dir->www; ?>widgets/htmledit/ie/images/spltcell.gif" WIDTH="23" HEIGHT="22">
  </div>
</div>

<!-- DHTML Editing control Object. This will be the body object for the toolbars. -->
<object ID="tbContentElement" CLASS="tbContentElement" 
  CLASSID="clsid:2D360201-FFF5-11D1-8D03-00A0C959BC0A" VIEWASTEXT>
  <param name="Scrollbars" value=true>
  <param name="SourceCodePreservation" value="1">
  <param name="UseDivOnCarriageReturn" value="1">
</object>
<!-- unsafe CLASSID="clsid:2D360200-FFF5-11D1-8D03-00A0C959BC0A" -->
<!-- DEInsertTableParam Object -->
<object ID="ObjTableInfo" CLASSID="clsid:47B0DFC7-B7A3-11D1-ADC5-006008A5848C" VIEWASTEXT>
</object>

<!-- DEGetBlockFmtNamesParam Object -->
<object ID="ObjBlockFormatInfo" CLASSID="clsid:8D91090E-B955-11D1-ADC5-006008A5848C" VIEWASTEXT>
</object>

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
