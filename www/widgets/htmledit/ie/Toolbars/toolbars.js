// Copyright 2000 Microsoft Corporation. All rights reserved. 
// Author: Steve Isaac, Microsoft Corporation
//
// DHTML Toolbar Package
//
// This file (along with the companion toolbars.css file) implements full featured
// toolbars completely in DHTML.
//
// See tutorial.htm in the Doc directory for full info on how to use this package.
//
//
//=================================================================================================
//

// Public Style Classes
// --------------------
// tbToolbar:    Toolbar
// tbButton:     Toolbar button
// tbIcon:       Toolbar or menu icon
// tbSeparator:  Toolbar or menu separator
// tbMenu:       Pulldown menu
// tbMenuItem:   Menu item
// tbSubmenu:    Submenu
// tbGeneral:    Arbitrary HTML element in a toolbar.
// tbContentElement: Identifies an HTML element as the page body. One and only one 
//               element on the page must have this class. The element must also have 
//               its ID set to "tbContentElement".
//
// Public Attributes
// -----------------
// TBTYPE: Special type of element. Possible values are:
//   Elements: toggle
//             radio
//             <not specified> - Simple button
//
//   Toolbars: noMouseOver
//             <not specified> - Mouseover supported
//
// TBSTATE: State of the element. Possible values are:
//   Elements: gray (Disabled)
//             checked
//             unchecked
//             
//   Toolbars: dockedTop
//             dockedBottom
//             hidden
//
// tbOnMenuShow: Event handler that is called immediately prior to showing a menu or 
//   submenu. Hosts use this to set the state of menu items. This attribute can either
//   be set on individual menus and submenus, or on a toolbar in which case it is
//   fired for every menu and submenu within that toolbar. The menu that is about
//   to be shown is given in tbEventSrcElement (see below).
//
// Public Functions
// ----------------
// TBSetState(element, newState)
//   Sets the state of an element.
//     element: element to set. This is an object.
//     newState: state to set. This is a string, same values as TBSTATE.
//
// TBRebuildToolbar(toolbar, rebuildMenus)
//   Use this routine to change the contents of a toolbar on the fly. Make all changes
//   (adding, removing and modifying toolbar elements), then call this routine once.
//	 This routine can also be used to add an entirely new toolbar.
//     toolbar: toolbar to rebuild. This is an object.
//     rebuildMenus: Should the menus in this toolbar also be rebuilt? Only set this
//       to true if there have been changes; its an expensive operation.
//
// TBRebuildMenu(menu, rebuildSubmenus)
//   Use this routine to change the contents of a menu or a submenu on the fly. Make all changes
//   (adding, removing and modifying menu items), then call this routine once.
//     menu: menu to rebuild. This is an object.
//     rebuildSubmenus: Should the submenus also be rebuilt? Only set this
//       to true if there have been changes; its expensive.
//
// Public Globals
// --------------
// tbEventSrcElement: Contains the element that an event was fired on. The toolbar
// package doesn't support the event object; this object performs a similar function.
var tbEventSrcElement;

//
// Public Error Return Values
// --------------------------
TB_STS_OK = "OK" // Success return
TB_E_INVALID_CLASS = "Invalid class value" // An element has an unrecognized class attribute (probably a misspelling)
TB_E_INVALID_TYPE = "Invalid TBTYPE value"
TB_E_INVALID_STATE = "Invalid TBSTATE value"
TB_E_NO_ID = "Element does not have an ID"

//
//=================================================================================================
// 
// Private Attributes
// ------------------
// TBTOOLBARWIDTH: Width of the toolbar (in px)
// TBUSERONCLICK: Temporary storage of an element's original onclick handler
//
// Private Constants. These can be used along with toolbar.css to change the look of the toolbar package.
// -----------------
TB_DISABLED_OPACITY_FILTER = "alpha(opacity=25)"
TB_HANDLE_WIDTH = 10
TB_HANDLE = '<DIV class=tbHandleDiv style="LEFT: 3"> </DIV>' +
            '<DIV class=tbHandleDiv style="LEFT: 6"> </DIV>'

TB_TOOLBAR_PADDING = 4
TB_SEPARATOR_PADDING = 5
TB_CLIENT_AREA_GAP = 0

//
// Private Globals
// ---------------
var TBInitialized = false; // Set to true when the package has initialized.
var tbToolbars = new Array();  // Array of all toolbars.
var tbContentElementObject = null; // Content element.
var tbContentElementTop = 0;  // Y pixel coordinate of the top of the content element.
var tbContentElementBottom = 0; // Y pixel coordinate of the bottom of the content element.
var tbLastHeight = 0; // Previous client window height (before resize in process).
var tbLastWidth = 0; // Previous client window width. 
var tbRaisedElement = null; // Current toolbar button that is being shown raised.
var tbOnClickInProcess;	// Executing user's onClick event.
var tbMouseOutWhileInOnClick;  // An onmouseout event occurred while executing the user's onClick event.

//
// Functions
//

// Public function for changing an element's state. 
function TBSetState(element, newState) {

  newState = newState.toLowerCase();

  switch (element.className) {
    case "tbToolbar" :
      if ((newState != "dockedtop") && (newState != "dockedbottom") && (newState != "hidden")) {
        return TB_E_INVALID_STATE;    
      }
      element.TBSTATE = newState;
      if (newState == "hidden") {
        element.style.visibility = "hidden";
      } else {
        element.style.visibility = "visible";
      }
      TBLayoutToolbars();
      TBLayoutBodyElement();      
    break;
    
    case "tbButton" :
    case "tbButtonDown" :
    case "tbButtonMouseOverUp" :
    case "tbButtonMouseOverDown" :
    case "tbMenuItem" :
      if ((newState != "gray") && (newState != "checked") && (newState != "unchecked")) {
        return TB_E_INVALID_STATE;
      }
        
      currentState = element.TBSTATE;
      if (currentState == "") {
        currentState = "checked";
      }
      
      if (newState == currentState) {
        return;
      }

      if (element.className != "tbMenuItem") {
        image = element.children.tags("IMG")[0];
     
        // Going into disabled state  
        if (newState == "gray") {
          element.className = "tbButton";  
          image.className = "tbIcon";  
          element.style.filter = TB_DISABLED_OPACITY_FILTER;
        }
      
        // Coming out of disabled state. Remove disabled look.
        if (currentState == "gray") {
          element.style.filter = "";
        }
      
        if (newState == "checked") {
           element.className = "tbButtonDown";  
           image.className = "tbIconDown";
        } else { //unchecked
           element.className = "tbButton";  
           image.className = "tbIcon";
        }  
      }

      if ((element.TBTYPE == "radio") && (newState == "checked")) {
        radioButtons = element.parentElement.children;
        for (i=0; i<radioButtons.length; i++) {
          if ((radioButtons[i].NAME == element.NAME) && (radioButtons[i] != element)) {
            radioButtons[i].TBSTATE = "unchecked";
            
            if (element.className != "tbMenuItem") {
              radioButtons[i].className = "tbButton";
              radioButtons[i].children.tags("IMG")[0].className = "tbIcon";
            }
          }
        }
      }
      
      element.TBSTATE = newState;
      break;
      
    default :
      return TB_E_INVALID_CLASS;
  }
  return TB_STS_OK;
} //TBSetState


// Event handler for tbContentElementObject onmouseover events.
function TBContentElementMouseOver() {
  if (tbRaisedElement) {
    switch (tbRaisedElement.className) {
    case "tbMenu" :
      // Note: TBShowNormal is in tbmenus.js.
      TBShowNormal(tbRaisedElement);
      break;
    case "tbButtonMouseOverUp" :
      tbRaisedElement.className = "tbButton";
      break;
    case "tbButtonMouseOverDown" :
      tbRaisedElement.className = "tbButtonDown";
      break;
    }
    tbRaisedElement = null;
  }
}


// Global onmouseup handler.
function TBGlobalMouseUp() {
}


// Global onmousedown handler.
function TBGlobalMouseDown() {
  // Always bring down any menus that are being displayed.
  if (typeof(tbMenu) != "undefined") {
    TBHideMenus();
  }
} 


//Global ondragstart and onselectstart handler.
function TBGlobalStartEvents() {
}

//Global mouse move handler.
function TBGlobalMouseMove() {
}


// Hander that simply cancels an event
function TBCancelEvent() {
  event.returnValue=false;
  event.cancelBubble=true;
}


// Toolbar button onmouseover handler
function TBButtonMouseOver() {
  var element, image;

  image = event.srcElement;
  element = image.parentElement;
  
  if (element.TBSTATE == "gray") {
    event.cancelBubble=true;
    return;
  }
  // Change button look based on current state of image.
  if (image.className == "tbIcon") {
    element.className = "tbButtonMouseOverUp";
    tbRaisedElement = element;
  } else if (image.className == "tbIconDown") {
    element.className = "tbButtonMouseOverDown";
  }

  event.cancelBubble=true;
} // TBButtonMouseOver


// Toolbar button onmouseout handler
function TBButtonMouseOut() {
  var element, image;
  
  image = event.srcElement;
  element = image.parentElement;
  if (element.TBSTATE == "gray") {
    event.cancelBubble=true;
    return;
  }
  
  tbRaisedElement = null;
  
  // Are we in the middle of an onClick event? Set a flag for the onclick handler and return if so.
  if (tbOnClickInProcess) {
    tbMouseOutWhileInOnClick = true;
    return;
  }

  switch (image.className) {
    case "tbIcon" :
      // Is the user cancelling unchecking a toggle/radio button by moving out?
      if (((element.TBTYPE == "toggle") || (element.TBTYPE == "radio")) && (element.TBSTATE == "checked")) {
        element.className = "tbButtonDown";
        image.className = "tbIconDown";
      } else {
        element.className = "tbButton";
      }
    break;
      
    case "tbIconDown" :
      // Is the user cancelling checking a toggle/radio button by moving out?
      if (((element.TBTYPE == "toggle") || (element.TBTYPE == "radio")) && (element.TBSTATE == "unchecked")) {
        element.className = "tbButton";
        image.className = "tbIcon";
      } else {
        element.className = "tbButtonDown"
      }
    break;
      
    case "tbIconDownPressed" :
      // The only time we'll see this is if the user is cancelling unchecking a checked toggle/radio
      element.className = "tbButtonDown";
      image.className = "tbIconDown";
    break;  
  }
  event.cancelBubble=true;
} // TBButtonMouseOut


// Toolbar button onmousedown handler
function TBButtonMouseDown() {
  var element, image;
  
  if (typeof(tbMenu) != "undefined") {
    TBHideMenus();
  }
  
  if (event.srcElement.tagName == "IMG") {
    image = event.srcElement;
    element = image.parentElement;
  } else {
    element = event.srcElement;
    image = element.children.tags("IMG")[0];
  }
  if (element.TBSTATE == "gray") {
    event.cancelBubble=true;
    return;
  }
  switch (image.className) {
    case "tbIcon" :
      element.className = "tbButtonMouseOverDown";
      image.className = "tbIconDown";
    break;
      
    case "tbIconDown" :
      if ((element.TBTYPE == "toggle") || (element.TBTYPE == "radio")) {
        image.className = "tbIconDownPressed";
      } else {
        element.className = "tbButton";
        image.className = "tbIcon";
      }
    break;
  }   
  
  event.cancelBubble=true;
  return false;
   
} //TBButtonMouseDown

// Toolbar button onmouseup handler
function TBButtonMouseUp() {
  var element, image, userOnClick, radioButtons, i;
 
  if (event.srcElement.tagName == "IMG") {
    image = event.srcElement;
    element = image.parentElement;
  } else {
    element = event.srcElement;
    image = element.children.tags("IMG")[0];
  }
  
  if (element.TBSTATE == "gray") {
    event.cancelBubble=true;
    return;
  }

  // Make sure this is one of our events
  if ((image.className != "tbIcon") && (image.className != "tbIconDown") && (image.className != "tbIconDownPressed")) {
    return;
  }

  // Initialize tbEventSrcElement so that the user's onClick handler can find out where the
  // event is coming from
  tbEventSrcElement = element;
  
  // Execute the  onclick handler that was on the event originally (user's onclick handler).
  // This is a little tricky; we have to call the anonymous function wrapper that was put around
  // the event by IE. Also, we set a global flag so that we can find out if a mouseout event occurs
  // while processing the user's onclick handler. mouseout and onclick behavior have to change
  // if this happens.
  tbOnClickInProcess = true;
  tbMouseOutWhileInOnClick = false;
  if (element.TBUSERONCLICK) {
    eval(element.TBUSERONCLICK + "anonymous()");
  }
  tbOnClickInProcess = false;
  
  // Is the nomouseover flag set on the toolbar?
  if (element.parentElement.TBTYPE == "nomouseover") {
    tbMouseOutWhileInOnClick = true;
  }

  //Update state and appearance based on type of button
  switch (element.TBTYPE) {
    case "toggle" :
      if (element.TBSTATE == "checked") {
        element.TBSTATE = "unchecked";
        if (tbMouseOutWhileInOnClick) {
          element.className = "tbButton";
        } else {
          element.className = "tbButtonMouseOverUp";
        }
        image.className = "tbIcon";
      } else {
        element.TBSTATE = "checked";
        if (tbMouseOutWhileInOnClick) {
          element.className = "tbButtonDown";
        } else {
          element.className = "tbButtonMouseOverDown";
        }
        image.className = "tbIconDown";
      }
    break;
      
    case "radio" :
      // Turn this element on if its not already on
      if (element.TBSTATE == "checked"){
        image.className = "tbIconDown";
        break;
      }
      element.TBSTATE = "checked";
      if (tbMouseOutWhileInOnClick) {
        element.className = "tbButtonDown";
      } else {
        element.className = "tbButtonMouseOverDown";
      }
      image.className = "tbIconDown";
    
      // Turn off every other radio button in this group by going through everything in the parent container
      radioButtons = element.parentElement.children;
      for (i=0; i<radioButtons.length; i++) {
        if ((radioButtons[i].NAME == element.NAME) && (radioButtons[i] != element)) {
          radioButtons[i].TBSTATE = "unchecked";
          radioButtons[i].className = "tbButton";
          radioButtons[i].children.tags("IMG")[0].className = "tbIcon";
        }
      }
    break;
    
    default : // Regular button
      if (tbMouseOutWhileInOnClick) {
        element.className = "tbButton";
      } else {
        element.className = "tbButtonMouseOverUp";
      }
      image.className = "tbIcon";
  }
  
  event.cancelBubble=true;
  return false;
  
} // TBButtonMouseUp


// Initialize a toolbar button
function TBInitButton(element, mouseOver) {
  var image;
 
  // Make user-settable properties all lowercase and do a validity check
  if (element.TBTYPE) {
    element.TBTYPE = element.TBTYPE.toLowerCase();
    if ((element.TBTYPE != "toggle") && (element.TBTYPE != "radio")) {
      return TB_E_INVALID_TYPE;
    }
  }
  if (element.TBSTATE) {
    element.TBSTATE = element.TBSTATE.toLowerCase();
    if ((element.TBSTATE != "gray") && (element.TBSTATE != "checked") && (element.TBSTATE != "unchecked")) {
      return TB_E_INVALID_STATE;
    }
  }
 
  image = element.children.tags("IMG")[0]; 

  // Set up all our event handlers
  if (mouseOver) {
    element.onmouseover = TBButtonMouseOver;
    element.onmouseout = TBButtonMouseOut;
  }
  element.onmousedown = TBButtonMouseDown; 
  element.onmouseup = TBButtonMouseUp; 
  element.ondragstart = TBCancelEvent;
  element.onselectstart = TBCancelEvent;
  element.onselect = TBCancelEvent;
  element.TBUSERONCLICK = element.onclick; // Save away the original onclick event
  element.onclick = TBCancelEvent;
   
  // Set up initial button state
  if (element.TBSTATE == "gray") {
    element.style.filter = TB_DISABLED_OPACITY_FILTER;
    return TB_STS_OK;
  }
  if (element.TBTYPE == "toggle" || element.TBTYPE == "radio") {
    if (element.TBSTATE == "checked") {
      element.className = "tbButtonDown";
      image.className = "tbIconDown";
    } else {
      element.TBSTATE = "unchecked";
    }
  }
  element.TBINITIALIZED = true;
  return TB_STS_OK;
} // TBInitButton


// Populate a toolbar with the elements within it
function TBPopulateToolbar(tb) {
  var i, elements, s;

  // Iterate through all the top-level elements in the toolbar
  elements = tb.children;
  for (i=0; i<elements.length; i++) {
    if (elements[i].tagName == "SCRIPT" || elements[i].tagName == "!") {
      continue;
    }
    switch (elements[i].className) {
      case "tbButton" :			
        if (elements[i].TBINITIALIZED == null) {
          if ((s = TBInitButton(elements[i], tb.TBTYPE != "nomouseover")) != TB_STS_OK) {
            alert("Problem initializing:" + elements[i].id + " Status:" + s);
            return s;
          }
        }
        elements[i].style.posLeft = tb.TBTOOLBARWIDTH;
        tb.TBTOOLBARWIDTH += elements[i].offsetWidth + 1; 
      break;
       
      case "tbMenu" :
        if (typeof(tbMenu) == "undefined") {
          alert("You need to include tbmenus.js if you want to use menus. See tutorial for details. Element: " + elements[i].id + " has class=tbMenu");
        } else {
          if (elements[i].TBINITIALIZED == null) {
            if ((s = TBInitToolbarMenu(elements[i], tb.TBTYPE != "nomouseover")) != TB_STS_OK) {
               alert("Problem initializing:" + elements[i].id + " Status:" + s);
             return s;
            }
          }
          elements[i].style.posLeft = tb.TBTOOLBARWIDTH;
          tb.TBTOOLBARWIDTH += elements[i].offsetWidth + TB_MENU_BUTTON_PADDING; 
        }
      break;
        
      case "tbGeneral" :
        elements[i].style.posLeft = tb.TBTOOLBARWIDTH;
        tb.TBTOOLBARWIDTH += elements[i].offsetWidth + 1; 
      break;
                
      case "tbSeparator" :
        elements[i].style.posLeft = tb.TBTOOLBARWIDTH + 2;
        tb.TBTOOLBARWIDTH += TB_SEPARATOR_PADDING;
      break;
      
      case "tbHandleDiv":
      break;
 
      default :
        alert("Invalid class: " + elements[i].className + " on Element: " + elements[i].id + " <" + elements[i].tagName + ">");
        return TB_E_INVALID_CLASS;
    }
  }
  return TB_STS_OK;
} // TBPopulateToolbar


// Initialize a toolbar. 
function TBInitToolbar(tb) {
  var s1, tr; 

  // Set up toolbar attributes
  if (tb.TBSTATE) {
    tb.TBSTATE = tb.TBSTATE.toLowerCase();
    if ((tb.TBSTATE != "dockedtop") && (tb.TBSTATE != "dockedbottom") && (tb.TBSTATE != "hidden")) {
      return TB_E_INVALID_STATE;    
    }
  } else {
    tb.TBSTATE = "dockedtop";
  }
  
  if (tb.TBSTATE == "hidden") {
    tb.style.visibility = "hidden";
  }
  
  if (tb.TBTYPE) {
    tb.TBTYPE = tb.TBTYPE.toLowerCase();
    if (tb.TBTYPE != "nomouseover") {
      return TB_E_INVALID_TYPE;    
    }
  }
  
  // Set initial size of toolbar to that of the handle
  tb.TBTOOLBARWIDTH = TB_HANDLE_WIDTH;
    
  // Populate the toolbar with its contents
  if ((s = TBPopulateToolbar(tb)) != TB_STS_OK) {
    return s;
  }
  
  // Set the toolbar width and put in the handle
  tb.style.posWidth = tb.TBTOOLBARWIDTH + TB_TOOLBAR_PADDING;
  tb.insertAdjacentHTML("AfterBegin", TB_HANDLE);
  
  return TB_STS_OK;
} // TBInitToolbar


// Lay out the docked toolbars
function TBLayoutToolbars() {
  var x,y,i;
  
  x = 0; y = 0;
  
  // If no toolbars we're outta here
  if (tbToolbars.length == 0) {
    return;
  }
  
  // Lay out any dockedTop toolbars
  for (i=0; i<tbToolbars.length; i++) {
    if (tbToolbars[i].TBSTATE == "dockedtop") {
      if ((x > 0) && (x + parseInt(tbToolbars[i].TBTOOLBARWIDTH) > document.body.offsetWidth)) {
        x=0; y += tbToolbars[i].offsetHeight;
      }
      tbToolbars[i].style.left = x;
      x += parseInt(tbToolbars[i].TBTOOLBARWIDTH) + TB_TOOLBAR_PADDING;
      tbToolbars[i].style.posTop = y;
    }
  } 

  // Adjust the top of the bodyElement if there were dockedTop toolbars
  if ((x != 0) || (y !=0)) {
    tbContentElementTop = y + tbToolbars[0].offsetHeight + TB_CLIENT_AREA_GAP;
  }
    
  // Lay out any dockedBottom toolbars
  x = 0; y = document.body.clientHeight - tbToolbars[0].offsetHeight;
  for (i=tbToolbars.length - 1; i>=0; i--) {
    if (tbToolbars[i].TBSTATE == "dockedbottom") {
      if ((x > 0) && (x + parseInt(tbToolbars[i].TBTOOLBARWIDTH) > document.body.offsetWidth)) {
        x=0; y -= tbToolbars[i].offsetHeight;
      }
      tbToolbars[i].style.left = x;
      x += parseInt(tbToolbars[i].TBTOOLBARWIDTH) + TB_TOOLBAR_PADDING;
      tbToolbars[i].style.posTop = y;
    }
  }
  
  // Adjust the bottom of the bodyElement if there were dockedBottom toolbars
  if ((x != 0) || (y != (document.body.offsetHeight - tbToolbars[0].offsetHeight))) {
    tbContentElementBottom = document.body.offsetHeight - y + TB_CLIENT_AREA_GAP;
  }
  
  tbLastHeight = 0;
  tbLastWidth = 0;
  
} // TBLayoutToolbars


// Adjust the position and size of the body element and the bottom and right docked toolbars.
function TBLayoutBodyElement() {
  
  tbContentElementObject.style.posTop = tbContentElementTop;
  tbContentElementObject.style.left = 0; 
  tbContentElementObject.style.posHeight = document.body.offsetHeight - tbContentElementBottom - tbContentElementTop;
  tbContentElementObject.style.width = document.body.offsetWidth;
  
  // Update y position of any dockedBottom toolbars
  if (tbLastHeight != 0) {
    for (i=tbToolbars.length - 1; i>=0; i--) {
      if (tbToolbars[i].TBSTATE == "dockedbottom" && tbToolbars[i].style.visibility != "hidden") {
        tbToolbars[i].style.posTop += document.body.offsetHeight - tbLastHeight;
      }
    }
  }
  
  tbLastHeight = document.body.offsetHeight;
  tbLastWidth = document.body.offsetWidth;
  
} // TBLayoutBodyElement


// Initialize everything when the document is ready
function document.onreadystatechange() {
  var i, s;
  
  if (TBInitialized) {
    return;
  }
  
  TBInitialized = true;
  
  document.body.scroll = "no";
  
  // Add a <span> that we will use this to measure the contents of menus
  if (typeof(tbMenu) != "undefined") {
    document.body.insertAdjacentHTML("BeforeEnd", "<span ID=TBMenuMeasureSpan></span>");
  }
  
  // Find all the toolbars and initialize them. 
  for (i=0; i<document.body.all.length; i++) {
    if (document.body.all[i].className == "tbToolbar") {
      if ((s = TBInitToolbar(document.body.all[i])) != TB_STS_OK) {
        alert("Toolbar: " + document.body.all[i].id + " failed to initialize. Status: " + s);
      }
      tbToolbars[tbToolbars.length] = document.body.all[i];
    }
  }
  
  // Get rid of the menu measuring span
  if (typeof(tbMenu) != "undefined") {
    document.all["TBMenuMeasureSpan"].outerHTML = "";
  }
  
  // Lay out the page
  TBLayoutToolbars();
  TBLayoutBodyElement();
  
  // Handle all resize events
  window.onresize = TBLayoutBodyElement;
    
  // Grab global mouse events.
  document.onmousedown = TBGlobalMouseDown;
  document.onmousemove = TBGlobalMouseMove;
  document.onmouseup = TBGlobalMouseUp;
  document.ondragstart = TBGlobalStartEvents;
  document.onselectstart = TBGlobalStartEvents;
}


//
// Immediately executed code
//
{
  tbContentElementObject = document.body.all["tbContentElement"];
   
  if (typeof(tbContentElementObject) == "undefined") {
    alert("Error: There must be one element on the page with an ID of tbContentElement");
  }

  if (tbContentElementObject.className != "tbContentElement") {
    alert('Error: tbContentElement must have its class set to "tbContentElement"');
  }
  
  // Add an onmouseover handler to the tbContentElement. We need this for when the DHTML Edting
  // control is the content element. IE doesn't give the toolbars onmouseout events in that case. 
  document.write('<SCRIPT LANGUAGE="JavaScript" FOR="tbContentElement" EVENT="onmouseover"> TBContentElementMouseOver(); </scrip' +'t>');  
  
}

// Rebuild toolbar; call after inserting or deleting items on toolbar.
function TBRebuildToolbar(toolbar, rebuildMenus)
{
  var toolbarFound = false;

  // Add a <span> that we will use this to measure the contents of menus
  if (typeof(tbMenu) != "undefined") {
    document.body.insertAdjacentHTML("BeforeEnd", "<span ID=TBMenuMeasureSpan></span>");
  }

  // Look through existing toolbars and see if we get a match
  for (i=0; i<tbToolbars.length; i++) {
    if (tbToolbars[i].id == toolbar.id) {
      toolbarFound = true;
      break;
    }
  }

  // is this is a new toolbar?
  if (false == toolbarFound) { 	
      // new toolbar, initialize it and add it to toolbar array
    if ((s = TBInitToolbar(toolbar)) != TB_STS_OK) {
      alert("Toolbar: " + toolbar.id + " failed to initialize. Status: " + s);
    }

    // add the new toolbar to the internal toolbar array
    tbToolbars[tbToolbars.length] = toolbar;

  }
  else {

    // Set initial size of toolbar to that of the handle
    toolbar.TBTOOLBARWIDTH = TB_HANDLE_WIDTH;

    for (i=0; i<document.body.all.length; i++) {
      if (document.body.all[i].className == "tbMenu") {
        TBRebuildMenu(document.body.all[i], rebuildMenus);
      }
    }

    // Populate the toolbar with its contents
    if ((s = TBPopulateToolbar(toolbar)) != TB_STS_OK) {
      alert("Toolbar: " + document.body.all[i].id + " failed to populate. Status: " + s);
    }

    // Set the toolbar width and put in the handle
    toolbar.style.posWidth = toolbar.TBTOOLBARWIDTH + TB_TOOLBAR_PADDING;

    if (false == toolbarFound) // new toolbar, add handle
      tb.insertAdjacentHTML("AfterBegin", TB_HANDLE);
  }
 
  // Get rid of the menu measuring span
  if (typeof(tbMenu) != "undefined") {
    document.all["TBMenuMeasureSpan"].outerHTML = "";
  }
   
  // Lay out the page
  TBLayoutToolbars();
  TBLayoutBodyElement();
}
