// Copyright 2000 Microsoft Corporation. All rights reserved. 
// Author: Steve Isaac, Microsoft Corporation
//
// DHTML Toolbar Package: Pulldown Menus
//
// Include this file if use pulldown menus in your toolbars. See tutorial.htm in
// the Doc directory for detailed instructions on how to do so.
//
// Public Constants
// ----------------
tbMenu = true; // Let the rest of the toolbar package know that menus are enabled.

//
// Private Constants. These can be used along with toolbar.css to change the look of 
// the menus.
// -----------------
TB_MENU_BORDER_WIDTH = 4
TB_MENU_BORDER_HEIGHT = 4
TB_MENU_POPUP_X_OFFSET = 1
TB_MENU_POPUP_Y_OFFSET = 5
TB_MENU_BUTTON_PADDING = 5

// Private Attributes
// ------------------
// TBLEVEL: Index into the scriptlet array. Which scriptlet is this element in?
// TBMESTINGLEVEL: Attribute on scriptlets only. Index into the scriptlet array for this scriptlet.
// TBMENUBODY: Menu body string. On menus and submenus only.

//
// Private Globals
// ---------------
var tbMenuShowing = null;  // The menu that is current being displayed. Null means no menu is up.
var tbMenuCancel = null; // Cancelling a menu is occurring. This is the menu being cancelled.
var tbNumScriptlets = 0; // Number of scriptlets allocated for menu bodies.
var tbScriptlets = new Array();	// Scriptlet array.
var tbNextShowingMenu; // Menu about to be shown. 

//
// Functions
//

// Public function for rebuilding a menu or submenu
function TBRebuildMenu(menu, rebuildSubmenus) {
var s, nestingDepth;

  TBHideMenus();
  
  // Calculate the initial nesting depth of this menu.
  if (menu.parentElement.className == "tbMenu" || menu.parentElement.className == "tbSubmenu") {
    nestingDepth = menu.parentElement.TBLEVEL + 1;
  } else {
    nestingDepth = 0;
  } 

  // Rebuild the menu.
  if ((s = TBBuildMenu(menu, nestingDepth, rebuildSubmenus)) != TB_STS_OK) {
    alert("TBRebuildMenu: Error rebuilding menu: " + menu.id  + ". Status: " + s);          
    return s;
  }

}


// Make an element's borders appear raised
function TBShowRaised(element) {
  element.style.borderTopColor ="buttonhighlight";
  element.style.borderLeftColor ="buttonhighlight";
  element.style.borderBottomColor ="buttonshadow";
  element.style.borderRightColor ="buttonshadow";
}


// Make an element's borders appear depressed
function TBShowDepressed(element) {
  element.style.borderTopColor ="buttonshadow";
  element.style.borderLeftColor ="buttonshadow";
  element.style.borderBottomColor ="buttonhighlight";
  element.style.borderRightColor ="buttonhighlight";  
}


// Make an element's borders appear normal
function TBShowNormal(element) {
  element.style.borderTopColor ="buttonface";
  element.style.borderLeftColor ="buttonface";
  element.style.borderBottomColor ="buttonface";
  element.style.borderRightColor ="buttonface";  
}


// Menu onmouseover handler
function TBMenuMouseOver() {
  var element;

  element = event.srcElement; 
  while (element.className != "tbMenu") {
    element = element.parentElement;
  }
   
  if (element.TBSTATE == "gray") {
    event.cancelBubble=true;
    return;
  }
  
  if (tbMenuShowing == element) {
    event.cancelBubble=true;
    return;
  }
  
    
  if (tbMenuShowing) {
    TBShowNormal(tbMenuShowing);
    TBHideMenuBodies(); 
    tbNextShowingMenu = element;
    // Hack! This avoids IE thinking that portions of the menu body don't need to be painted.
    window.setTimeout("TBPopUpMenu(tbNextShowingMenu, 0)", 0);
  } else {
    TBShowRaised(element);
    tbRaisedElement = element;    
  }
  
  event.cancelBubble=true;
  
} // TBMenuMouseOver


// Menu onmouseout handler
function TBMenuMouseOut() {
  var element;

  element = event.srcElement; 
  while (element.className != "tbMenu") {
    element = element.parentElement;
  }
     
  if (element.TBSTATE == "gray") {
    event.cancelBubble=true;
    return;
  }

  // Make sure we got this event because the user really moused out, not just because we went into
  // a child element. 
  if (element.contains(event.toElement)) {
    event.cancelBubble=true;
    return;
  }
    
  if (tbMenuShowing == element) {
    event.cancelBubble=true;
    return;
  }
  
  tbRaisedElement = null;
      
  TBShowNormal(element);
  event.cancelBubble=true;

} // TBMenuMouseOut


// Menu onmousedown handler
function TBMenuMouseDown() {
  var element;
  
  element = event.srcElement; 
  while (element.className != "tbMenu") {
    element = element.parentElement;
  }
  
  if (element.TBSTATE == "gray") {
    event.cancelBubble=true;
    return false;
  }
  
  if (tbMenuShowing == element) {
    tbMenuCancel = element;
    event.cancelBubble=true;
    return false;  
  }
  
  TBPopUpMenu(element, 0);
  
  event.cancelBubble=true;
  return false;  

} // TBMenuMouseDown


// Menu onmouseup handler
function TBMenuMouseUp() {
  var element;
  
  element = event.srcElement; 
  while (element.className != "tbMenu") {
    element = element.parentElement;
  }
  
  if (tbMenuCancel == element) {
    TBHideMenuBodies();
    TBShowRaised(element);
    tbMenuShowing = null;
  }
  
  tbMenuCancel = null;
  
  event.cancelBubble=true;
  return false;

} // TBMenuMouseUp


// Hide all scriptlets
function TBHideMenuBodies() {
  var i;
  
  for (i=0; i<tbNumScriptlets; i++) {
    tbScriptlets[i].style.posLeft = -2000;
    tbScriptlets[i].style.posTop = -2000;
    tbScriptlets[i].HideMenu();
  }  
}


// Bring up a menu or subMenu
function TBPopUpMenu(menu, nestingDepth) {
  var scriptlet = tbScriptlets[nestingDepth];
  var width = 0, height = 0;
  var tb, s;
  
  // Call the user's tbOnMenuShow handler, if there is one.
  tbEventSrcElement = menu;
  if (!menu.tbOnMenuShow) {
    tb = menu;
    while (tb.className != "tbToolbar") {
      tb = tb.parentElement;
    }
    s = tb.tbOnMenuShow;
  } else {
    s = menu.tbOnMenuShow;
  }
  if (s) {
    eval("function anonymous() { "+ s + " } anonymous()");
  }
  
  // If this is a menu button on the toolbar, make it look pressed
  if (menu.className == "tbMenu") {
    TBShowDepressed(menu); 
  }
 
  // Position the scriptlet 
  if (menu.className == "tbMenu") {
    scriptlet.style.posLeft = menu.offsetLeft + menu.parentElement.offsetLeft + TB_MENU_POPUP_X_OFFSET;
    scriptlet.style.posTop = menu.offsetTop + menu.parentElement.offsetTop + menu.parentElement.offsetHeight - TB_MENU_POPUP_Y_OFFSET; 
  } else {
    scriptlet.style.posLeft = tbScriptlets[nestingDepth - 1].offsetLeft + tbScriptlets[nestingDepth - 1].subMenuX; 
    scriptlet.style.posTop = tbScriptlets[nestingDepth - 1].offsetTop + tbScriptlets[nestingDepth - 1].subMenuY; 
  }
    
  // Tell the scriptlet to display the menu
  scriptlet.ShowMenu(menu.id, menu.TBMENUBODY);
  
  // If we haven't cached the size, set the scriplet size and cache it for next time
  width = scriptlet.menuWidth + TB_MENU_BORDER_WIDTH; 
  scriptlet.style.posWidth = width; 

  height = scriptlet.menuHeight + TB_MENU_BORDER_HEIGHT;
  scriptlet.style.posHeight = height.toString(); 
 
   // Adjust menu position if we are outside of the browser window
  if (menu.className == "tbMenu") {
    if ((scriptlet.offsetTop + scriptlet.offsetHeight) > document.body.clientHeight) {
      scriptlet.style.posTop = (scriptlet.offsetTop - scriptlet.offsetHeight + menu.parentElement.offsetHeight - TB_MENU_POPUP_Y_OFFSET*2);
    }
  } else {
    if ((scriptlet.offsetTop + scriptlet.offsetHeight) > document.body.clientHeight) {
      scriptlet.style.posTop = (scriptlet.offsetTop - scriptlet.offsetTop + scriptlet.offsetHeight - document.body.clientHeight);
    }
    if ((scriptlet.offsetLeft + scriptlet.offsetWidth) > document.body.clientWidth) {
      scriptlet.style.posLeft = (scriptlet.offsetLeft - scriptlet.offsetWidth + menu.parentElement.offsetWidth - TB_MENU_BORDER_HEIGHT*2);
      if (scriptlet.offsetLeft < 0) {
        scriptlet.style.posLeft = 0;
      }
    }
  }
  
  // Force the scriptlet to draw on top of any windowed controls
  scriptlet.style.visibility = "visible";
  
  // Remember this menu
  if (menu.className == "tbMenu") {
    tbMenuShowing = menu;
  }
  
} // TBPopUpMenu


//Global onmousedown handler. Bring down any menus that are up.
function TBHideMenus() {
  if (tbMenuShowing) {
    TBHideMenuBodies();
    TBShowNormal(tbMenuShowing);
    tbMenuShowing = null;
  }
  
} // TBHideMenus


// Check for a menu icon. Extract it into an appropriate string if found. Return
// placeholder HTML if not found.
function TBCheckMenuIcon(element, disabled) {
  var i, s;

  // See if one of the element's immediate children is an icon (class == tbIcon).
  for (i=0; i<element.children.length; i++) {
    if (element.children[i].className == "tbIcon") {
      // Found it. Put the icon's outerHTML in our return string. 
      if (element.TBSTATE == "checked") {
        element.children[i].className = "tbMenuIconChecked";
      }	else {
        element.children[i].className = "tbMenuIcon";
      }
      s = element.children[i].outerHTML;
      element.children[i].className = "tbIcon";
      return '<td ' + disabled + '>' + s + '</td>';
    }
  }
  
  // Is this element a non-icon toggle or radio button?
  if (element.TBTYPE == "toggle" || element.TBTYPE == "radio") {
    if (element.TBSTATE == "checked") {
      return  '<td ' + disabled + '><span class="tbMenuItemChecked">a</span></td>';
    } else {
      return  '<td ' + disabled + '><span class="tbMenuBlankSpace">&nbsp;</span></td>';
    }
  }
  
  // Didn't find an icon or toggle/radio button, so return some blank space
  return '<td ' + disabled + '><span class="tbMenuBlankSpace">&nbsp;</span></td>';
}


// Walk the menu and submenu structures. Save onclick handlers. Create menu and 
// subMenu bodies and put them into the appropriate scriptlets. 
function TBBuildMenu(menu, nestingDepth, buildSubmenus) {
  var i, s, s1, items, disabled, element, sts;
  
  // Note which scriptlet this menu is going into
  menu.TBLEVEL = nestingDepth;
  
  // We need to wrap each menu body in a couple of divs and a table. Here's the outer string for this.
  s = '<div id=' + menu.id + ' class="tbMenuBodyOuterDiv"><div class="tbMenuBodyInnerDiv"><table id="TBMENUBODYTABLE" cellpadding=0 cellspacing=0 class="tbMenuBodyTable">';
  
  // Go through all the children in the menu, looking for menu body elements.
  items = menu.children.tags("DIV");
  for (i=0; i<items.length; i++) {
    element = items[i];
    
    if (element.TBSTATE == "gray"){
      disabled = 'style = "filter:' + TB_DISABLED_OPACITY_FILTER + '"';
    } else {
      disabled = "";
    }
    
    switch (element.className) {
      case "tbMenuItem" :
        if (element.id == "") {
          return TB_E_NO_ID;
        }
        
        // Save away the user's onclick event handler
        if (element.TBINITIALIZED == null) {
          element.TBUSERONCLICK = element.onclick; 
          element.onclick = TBCancelEvent;
          element.TBINITIALIZED = true;
        }

        // Note which scriptlet this menu item is going into
        element.TBLEVEL = nestingDepth;
        
        // Wrap a table row around the menu item and put in its icon if it exists. 
        s1 = TBCheckMenuIcon(element, disabled); 
        s += '<tr TBELEMENTID="' + element.id + '" >' + s1 + '<td noWrap ' + disabled +'>' + element.outerHTML + '</td><td ' + disabled + '><span class="tbMenuBlankSpace">&nbsp</span></td></tr>';
      break;
        
      case "tbSubmenu" :
        if (!buildSubmenus) {
          return;
        }
        
        if (element.id == "") {
          return TB_E_NO_ID;
        }
                
        // Call ourselves recursively to populate the subMenu.
        if ((sts = TBBuildMenu(element, nestingDepth + 1, true)) != TB_STS_OK) {
          return sts;
        }
        
        // Wrap a table row around the menu item, put the icon in if it exists and 
        // put the subMenu arrow in.
        s1 = TBCheckMenuIcon(element, disabled);
        s += '<tr TBELEMENTID="' + element.id + '" >' + s1 + '<td noWrap ' + disabled +'>' + element.outerHTML + '</td><td ' + disabled +'><span class="tbSubmenuGlyph">4</span></td></tr>';
      break;
        
      case "tbSeparator" :
        // Change the class to tbMenuSeparator. That way the separators aren't
        // visible on the main page.
        element.className = "tbMenuSeparator";
        
        // Save a separator as a couple of divs that span the entire width of the menu.
        s += '<tr class="tbMenuSeparator"><td align=center colspan=3><div class="tbMenuSeparatorTop"></div><div class="tbMenuSeparatorBottom"></div></td></tr>';
      break;
        
    }
  }  

  // We need to turn any relative URLs in the menu body into absolute URLs, because
  // relative URLs will not work in the scriptlet (IE makes them absolute relative
  // to the scriptlet when we paste them into the scriptlet). Do this by inserting 
  // the menu body into the document and extracting the outerHTML. This is a rather
  // expensive way of doing this, but its the only way to guarantee that all 
  // user-supplied URLs also gets updated...
  s += "</table></div></div>";

  document.body.insertAdjacentHTML("AfterBegin", s);
  s = document.body.all[0].outerHTML;
  document.body.all[0].outerHTML = "";
  
  // Save the menu body string on the menu object.
  menu.TBMENUBODY = s;
  return TB_STS_OK;
    
} // TBBuildMenu


// Initialize a toolbar menu element
function TBInitToolbarMenu(toolbarMenu, mouseOver) {
  var i, element, s, menuMeasureSpan = document.all["TBMenuMeasureSpan"];
 
  if (toolbarMenu.ID == "") {
    return TB_E_NO_ID;
  }

  toolbarMenu.align = "center";
  
  // Set up all our event handlers
  if (mouseOver) {
    toolbarMenu.onmouseover = TBMenuMouseOver;
    toolbarMenu.onmouseout = TBMenuMouseOut;
  }
  toolbarMenu.onmousedown = TBMenuMouseDown; 
  toolbarMenu.onmouseup = TBMenuMouseUp;
  toolbarMenu.ondragstart = TBCancelEvent;
  toolbarMenu.onselectstart = TBCancelEvent;
  toolbarMenu.onselect = TBCancelEvent;
  
  // Calculate width of the menu button. Put whatever is in the menu's innerHTML into
  // a span, get rid of the menu body elements,  then use the offsetWidth of the span to 
  // give us an accurate pixel count
  menuMeasureSpan.innerHTML = toolbarMenu.innerHTML;
  i = 0;
  while (i<menuMeasureSpan.children.length) {
    element = menuMeasureSpan.children[i];
    switch (element.className) {
      case "tbMenuItem" :
      case "tbSubmenu" :
      case "tbMenuSeparator" :
        element.outerHTML = "";
      break;
      
      default :
        i++;   
    }
  }  
  toolbarMenu.style.posWidth = menuMeasureSpan.offsetWidth;
  
  //Build the menus and subMenus.
  if ((s = TBBuildMenu(toolbarMenu, 0, true)) != TB_STS_OK) {
    alert("Problem with menu item or subMenu in menu: " + toolbarMenu.id + ". Status: " + s);          
    return s;
  }
  toolbarMenu.TBINITIALIZED = true;
    
  return TB_STS_OK;
  
} // TBInitToolbarMenu


// A scriptlet has fired ReadyStateChanged, and is therefore ready to be used.
function TBScriptletReadyState(scriptlet) {
  var title;
  
  // Put the document's title into the scriptlet. This keeps the IE title bar from changing when 
  // mouse is in the scriptlet
  title = document.title;
  if (title == "") {
    title = document.location; 
  }
  scriptlet.titleProp = title;
  
  // Put the opacity filter for disabled elements into the scriptlet.
  scriptlet.opacityFilter = TB_DISABLED_OPACITY_FILTER;
}


// A scriptlet has fired a custom event.
function TBScriptletEvent(scriptlet, eventName, eventObject) {

  var i;
  
  switch (eventName) {
    case "TBMenuClick" :
      var element = document.body.all[eventObject];
      
      TBHideMenus();
      
      // Initialize tbEventSrcElement so that the user's onClick handler can find out where the
      // event is coming from
      tbEventSrcElement = element;
      
      // Execute the  onclick handler that was on the event originally (user's onclick handler).
      // This is a little tricky; we have to call the anonymous function wrapper that was put around
      // the event by IE. 
      if (element.TBUSERONCLICK) {
        eval(element.TBUSERONCLICK + "anonymous()");
      }
    break;
      
      
    case "TBDisplaySubmenu" :
      TBPopUpMenu(document.body.all[eventObject], parseInt(scriptlet.TBNESTINGLEVEL) + 1);
    break;
      
    case "TBHideSubmenu" :
      // hide all submenus
      for (i=tbNumScriptlets-1; i>=0; i--) {
        if (i > scriptlet.TBNESTINGLEVEL) {
          tbScriptlets[i].style.posLeft = -2000;
          tbScriptlets[i].style.posTop = -2000;
          tbScriptlets[i].HideMenu();
        }
      }
    break;
  }
  
} // TBScriptletEvent


// Recursively check the nesting depth of subMenus
function TBCheckNestingDepth(menu, nestingLevel) {
  var i;
  
    for (i=0; i<menu.children.length; i++) {
      if (menu.children[i].className == "tbSubmenu") {
        nestingLevel = TBCheckNestingDepth(menu.children[i], nestingLevel + 1);
      }
    }
    return nestingLevel;
}


//
// Immediately executed code
//
{ 
  var i, element;
  
  // Compute the maximum subMenu nesting depth of all the menus. This is the number
  // of scriptlets we need to allocate. 
  // Stick an onmousedown handler in for all objects and applets on the page. 
  // This is needed in order to bring down a menu if the user hapens to click in an 
  // object. 
  // See if there is an element that has a tbBodyObject class.
  for (i=0; i<document.body.all.length; i++) {
    element = document.body.all[i];
    if (element.className == "tbMenu") {
      if ((thisMenuNestingDepth = TBCheckNestingDepth(element, 1)) > tbNumScriptlets) {
        tbNumScriptlets = thisMenuNestingDepth;
      } 
    }
    if ((element.tagName == "OBJECT" || element.tagName == "APPLET")) {
      document.write('<SCRIPT LANGUAGE="JavaScript" FOR="' + element.id + '" EVENT="onmousedown"> TBGlobalMouseDown(); </scrip' +'t>');    
    }
  }
  
  // Insert scriptlets into the body
  if (typeof(tbScriptletDefinitionFile) == "undefined") {
    alert('tbScripletDefinitionFile not defined!. You must include the following immediately before <script SRC=toolbars.js>: <script LANGUAGE="Javascript"> tbScriptletDefinitionFile = "location of menubody.htm file"; </scrip' + 't>');

  }
  
  for (i=0; i<tbNumScriptlets; i++) {
    document.write('<OBJECT data=' + tbScriptletDefinitionFile + ' class=tbScriptlet id=TBSCRIPTLET style="POSITION: absolute; TOP: -2000; LEFT: -2000" type=text/x-scriptlet></OBJECT>'); 
  }
  
  // Set up the tbScriptlets array
  if (tbNumScriptlets > 0) {
    if (tbNumScriptlets == 1) {
      tbScriptlets[0] = document.all["TBSCRIPTLET"];
    } else {
      tbScriptlets = document.all["TBSCRIPTLET"];
    }
  }
  
  // Mark each scriptlet with its nesting level.
  for (i=0; i<tbNumScriptlets; i++) {
   tbScriptlets[i].TBNESTINGLEVEL = i;
  }
  
  // Insert the scriptlet's event handlers into the body (this is the only way to get
  // events from dynamically created scriptlets. 
  document.write('<SCRIPT LANGUAGE="JavaScript" FOR=TBSCRIPTLET EVENT="onreadystatechange"> TBScriptletReadyState(this); </scrip' +'t>');
  document.write('<SCRIPT LANGUAGE="JavaScript" FOR=TBSCRIPTLET EVENT="onscriptletevent(eventName, eventObject)"> TBScriptletEvent(this, eventName, eventObject); </scrip' +'t>');
}


