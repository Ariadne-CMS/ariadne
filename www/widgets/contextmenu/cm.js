/*
Original script by Toh Zhiqiang
Homepage: http://www.tohzhiqiang.f2s.com/leftclick/index.php
Email: webmaster@tohzhiqiang.com

New script
By Chris van de Steeg (alley@ilikeu2.com)
what's new:
	* mozilla compatible (althoug Toh claimed it to be, it absolutely didn't work in moz...
		needed a lot of work before it did work... event really doesn't exist in moz, Toh)
	* ('much') better look (only thing I can't seem to fix, is the seperator look in moz).
	* removed the unnecessary backGroundLayer.
	* allowed to add 'contextmenu' attribute to any tag, so it can show it's own contextmenu
	* allowed to disable menuitems
	* made it a right-click menu (contextmenu) instead of a left-click menu
	* made it possible to use javascript in the url's
	* fixed numerous bugs
*/

var cm_widthMax;
var cm_heightMax;
var cm_xCoor;
var cm_yCoor;
var cm_sWidth;
var cm_sHeight;
var cm_xScroll;
var cm_widthScroll;
var BR_IE = false;
var BR_NS = false;

var cm_menu = new Array;
var cm_divId = new Array;

function cm_menuItem() {
  this.item = arguments[0];
  this.hasSubMenu = arguments[1];
  this.level = arguments[2];
  this.subMenuFront = arguments[3];
  this.subMenuOffset = arguments[4];
}

function cm_findMenuLevel(parentId) {
  for (i = 0; i < cm_divId.length; i = i + 2) {
      if (cm_divId[i] == (parentId + "Front")) {
          return cm_divId[i + 1];
          break;
      }
  }
}

function cm_createMenu(menuId, menuWidth, level) {
  var frontLayer = document.createElement("DIV");
  frontLayer.id = menuId + "Front";
  frontLayer.className = "cm_menuFront";
  frontLayer.style.width = menuWidth;
  frontLayer.style.height = 3;
  frontLayer.style.visibility = "hidden";
  document.body.appendChild(frontLayer);
  cm_divId[cm_divId.length] = menuId + "Front";
  cm_divId[cm_divId.length] = level;
}

function cm_createItem(itemId, displayText, parentId, pageSrc, pageTarget, hasSubMenu)
{
  var menuFront = document.getElementById(parentId + "Front");
  var level = cm_findMenuLevel(parentId);
  var subMenuFront = "";
  var subMenuOffset = -1;
  var itemLayer = document.createElement("DIV");  
  itemLayer.onmouseover = cm_over;
  itemLayer.onmouseout = cm_out;
  itemLayer.onclick = cm_goToLink;
  itemLayer.id = itemId;
  itemLayer.className = "cm_item";
  itemLayer.isContextItem=true;
  if (hasSubMenu) {
      itemLayer.pageSrc = "";
      itemLayer.innerHTML = displayText;
      var sepLayer = document.createElement("DIV");
      sepLayer.className = "cm_arrow";
      sepLayer.innerHTML = "4";
      sepLayer.style.left = parseInt(menuFront.style.width.replace("px", "")) - 20;
      itemLayer.appendChild(sepLayer);
  } else {
      itemLayer.innerHTML = displayText;        
  }
  itemLayer.style.top = parseInt(menuFront.style.height.replace("px", "")) - 3;
  itemLayer.style.width = parseInt(menuFront.style.width.replace("px", "")) - 4;
  //itemLayer.style.fontFamily = itemFont;
  //itemLayer.style.color = outCr;
  if (hasSubMenu)
  	itemLayer.subMenuOffset =  menuFront.style.height - 3;
  menuFront.appendChild(itemLayer);
  menuFront.style.height = itemLayer.offsetHeight + parseInt(menuFront.style.height.replace("px", ""));
  itemLayer.url = pageSrc;  
  if (!pageTarget){
      pageTarget = "_top"
  }
  itemLayer.target = pageTarget;
  cm_menu[cm_menu.length] = new cm_menuItem(itemId, hasSubMenu, level, subMenuFront, subMenuOffset);  
}

function cm_createSep(parentId)
{
  var menuFront = document.getElementById(parentId + "Front");
  var sepLayer = document.createElement("DIV");
  sepLayer.className = "cm_itemSep";
  sepLayer.innerHTML = "&nbsp;";
  sepLayer.style.top = menuFront.style.height;
  sepLayer.style.width = parseInt(menuFront.style.width.replace("px", "")) - 4;
  menuFront.appendChild(sepLayer);
  menuFront.style.height = sepLayer.offsetHeight + parseInt(menuFront.style.height.replace("px", ""))+4;
}

function cm_linkSubMenu(itemId, subMenuId)
{
  for (i = 0; i < cm_menu.length; i++)
    {
      if (cm_menu[i].item == itemId)
        {
          cm_menu[i].subMenuFront = subMenuId + "Front";
          break;
        }
    }
}

function cm_hideMenus(e){
  for (i = 0; i < cm_divId.length; i = i + 2) {
      var menuLayer = document.getElementById(cm_divId[i]);
      menuLayer.style.visibility = "hidden";
    }
}

function cm_popUpPos(parentId, e)
{
  var menuFront = document.getElementById(parentId + "Front");
  var disabledItems = menuFront.disabledItems;
  if (!disabledItems)
  	disabledItems = "";
  for (var i=0;i<menuFront.childNodes.length;i++){
  	if (menuFront.childNodes[i].className != "cm_itemSep"
		&& menuFront.childNodes[i].className != "cm_sep"
		&& menuFront.childNodes[i].className != "cm_arrow"){
			if (disabledItems.toLowerCase().indexOf(menuFront.childNodes[i].id.toLowerCase() + ";") >= 0){
				menuFront.childNodes[i].className = "cm_itemDisabled";
				menuFront.childNodes[i].onclick = cm_noClickEvent;
				menuFront.childNodes[i].inactive=true;
			}else{
				menuFront.childNodes[i].className = "cm_item";
				menuFront.childNodes[i].onclick = cm_goToLink;
				menuFront.childNodes[i].inactive=false;
			}
	}
  }
  cm_widthMax = BR_IE?document.body.clientWidth:window.innerWidth;
  cm_heightMax = BR_IE?document.body.clientHeight:window.innerHeight;
  cm_xCoor = e.clientX;
  cm_yCoor = e.clientY;
  cm_sWidth = parseInt(menuFront.style.width.replace("px", ""));
  cm_sHeight = parseInt(menuFront.style.height.replace("px", ""));
  cm_yScroll = BR_IE?document.body.scrollTop:window.pageYOffset;
  cm_widthScroll = BR_IE?document.body.offsetWidth - cm_widthMax:window.pageXOffset - cm_widthMax;
  cm_xWidth = cm_xCoor + cm_sWidth;
  cm_yHeight = cm_yCoor + cm_sHeight;

  if (menuFront.style.visibility == "hidden") {
      if (cm_yHeight < (cm_heightMax - 1))
          menuFront.style.top = cm_yCoor + cm_yScroll;
      else {
          if (cm_yCoor < cm_sHeight)
              menuFront.style.top = cm_yScroll;
          else {
              menuFront.style.top = cm_yCoor - cm_sHeight + cm_yScroll - 2;
              if ((parseInt(menuFront.style.top.replace("px", "")) + parseInt(menuFront.style.height.replace("px", "")) - cm_yScroll + 2) > cm_heightMax)
                  menuFront.style.top = cm_heightMax - parseInt(menuFront.style.height.replace("px", "")) + cm_yScroll - 2;
          }
      }

      if (cm_xWidth < cm_widthMax - 1)
          menuFront.style.left = cm_xCoor;
      else {
          if (cm_xCoor == (cm_widthMax + 1))
              menuFront.style.left = cm_xCoor - cm_sWidth - 3;
          else {
              if (cm_widthMax < cm_xCoor)
                  menuFront.style.left = cm_xCoor - cm_sWidth - cm_widthScroll - 1;
              else
                  menuFront.style.left = cm_xCoor - cm_sWidth - 2;
          }
      }
  }
}

function cm_popUpSubPos(xCoor, yCoor, subMenuFront, layerId)
{ 
  var menuFront = document.getElementById(layerId + "Front");
  var disabledItems = menuFront.disabledItems;
  if (!disabledItems)
  	disabledItems = "";
  for (var i=0;i<subMenuFront.childNodes.length;i++){
  	if (subMenuFront.childNodes[i].className != "cm_itemSep"
		&& subMenuFront.childNodes[i].className != "cm_sep"
		&& subMenuFront.childNodes[i].className != "cm_arrow"){
			if (disabledItems.toLowerCase().indexOf(subMenuFront.childNodes[i].id.toLowerCase() + ";") >= 0){
				subMenuFront.childNodes[i].className = "cm_itemDisabled";
				subMenuFront.childNodes[i].onclick = cm_noClickEvent;
				subMenuFront.childNodes[i].inactive=true;
			}else{
				subMenuFront.childNodes[i].className = "cm_item";
				subMenuFront.childNodes[i].onclick = cm_goToLink;
				subMenuFront.childNodes[i].inactive=false;
			}
	}
  }
  cm_widthMax = BR_IE?document.body.clientWidth:window.innerWidth;
  cm_heightMax = BR_IE?document.body.clientHeight:window.innerHeight;
  cm_sWidth = parseInt(menuFront.style.width.replace("px", ""));
  cm_sHeight = parseInt(menuFront.style.height.replace("px", ""));
  cm_yScroll = BR_IE?document.body.scrollTop:window.pageYOffset;
  cm_widthScroll = BR_IE?document.body.offsetWidth - cm_widthMax:window.pageXOffset - cm_widthMax;
  cm_xWidth = xCoor + cm_sWidth;
  cm_yHeight = yCoor + cm_sHeight;

  if (subMenuFront.style.visibility == "hidden") {
      if ((cm_yHeight - cm_yScroll) < (cm_heightMax - 1))
          subMenuFront.style.top = yCoor;
      else
          subMenuFront.style.top = yCoor - cm_sHeight + 15;

      if (cm_xWidth < cm_widthMax - 1)
          subMenuFront.style.left = xCoor;
      else
          subMenuFront.style.left = xCoor - menuFront.style.width - parseInt(subMenuFront.style.width.replace("px", "")) + 5;
  }
}

function cm_show(e)
{
  cm_hideMenus();
  if (BR_IE) e=event;
  var source = BR_IE?e.srcElement:e.target;
  //for moz: set the source to the parentnode, since moz most times sends the contents of a 
  //tag as text object to an onclick
  if (!source.tagName)
  	source=source.parentNode;
  //for moz: set the source to the body, if it is the html tag.
  if (source.tagName == "HTML")
  	source=document.body;
  else if (source.tagName == "A" && source.getAttribute("path")) //make the a item selected
  	selectItem(source);
  else if (source.parentNode && source.parentNode.tagName == "A" && source.parentNode.getAttribute("path")) //make the image's a selected
  	selectItem(source.parentNode);
  if (source.getAttribute){
  	var menuId = source.getAttribute('contextmenu');
	var disabledItems = source.getAttribute('disableditems');
	//look up (until body) for a tag that has the attribute 'contextmenu'
	if (!menuId){
		var tmpItem = source;
		while (!menuId && tmpItem != document.body) {
			tmpItem = tmpItem.parentNode;
			disabledItems = source.getAttribute('disableditems');
			menuId=tmpItem.getAttribute('contextmenu');
		}
	}
	//look up (until body) for a tag that has the attribute 'disableditems', if it isn't found already
	if (!disabledItems){
		var tmpItem = source;
		while (!disabledItems && tmpItem != document.body) {
			tmpItem = tmpItem.parentNode;
			disabledItems=tmpItem.getAttribute('disabledItems');
		}
	}
	if (!disabledItems)
		disabledItems = "";
  }
  if (menuId){
	  var menuFront = document.getElementById(menuId + "Front");
	  menuFront.disabledItems = disabledItems;
      cm_popUpPos(menuId, e);
      menuFront.style.visibility = "visible";
      return false;
  }
}

function cm_popupAtPos(menuId, left, top) {
	  cm_hideMenus();
	  var menuFront = document.getElementById(menuId + "Front");
	  //can not disable items when calling direct (for now...)
	  menuFront.disabledItems = "";
      menuFront.style.left = left;
	  menuFront.style.top = top;
      menuFront.style.visibility = "visible";
      return false;
}

function cm_checkElement(itemId)
{
  //just go up untill a div with attribute isContextItem=true is found;
  var tmpItem = itemId;
  while (tmpItem != document.body && !tmpItem.isContextItem)
  	tmpItem = tmpItem.parentNode;
  if (tmpItem != document.body)
  	return tmpItem;
  else 
  	return null;
 }

function cm_findLayerId(frontId)
{
  var index = frontId.indexOf("Front");
  return (frontId.substring(0, index));
}
 
function cm_over(e)
{
  if (BR_IE) e=event;
  var itemId = BR_IE?e.srcElement:e.target;
  itemId = cm_checkElement(itemId);
  if (itemId && itemId.tagName=="DIV"){
	  var menuFront = itemId.parentNode;
	  var layerId = cm_findLayerId(menuFront.id);
	  var cm_xCoor = parseInt(menuFront.style.left.replace("px", "")) + parseInt(itemId.style.width.replace("px", ""));
	  var cm_yCoor = parseInt(menuFront.style.top.replace("px", "")) + parseInt(itemId.style.top.replace("px", ""));
	  if (itemId.inactive)
	  	itemId.className = "cm_itemDisabledHover"
	  else {
	  	itemId.className = "cm_itemHover"
		  for (i = 0; i < cm_menu.length; i++) {      
		      if (itemId == document.getElementById(cm_menu[i].item)) {        
		          for (j = 0; j < cm_divId.length; j = j + 2) {             
					  if ((cm_menu[i].level) < cm_divId[j + 1]) {
		                  var subMenu = document.getElementById(cm_divId[j]);
		                  subMenu.style.visibility = "hidden";
		              }
		          }
		          if (cm_menu[i].hasSubMenu) {
		              var subMenuFront = document.getElementById(cm_menu[i].subMenuFront);
		              subMenuFront.style.zIndex = cm_menu[i].level + 12;
		              cm_popUpSubPos(cm_xCoor, cm_yCoor + cm_menu[i].subMenuOffset, subMenuFront, layerId);
		              subMenuFront.style.visibility = "visible";
		          }
		          break;
		      }        
		  }
	  }
  }
}

function cm_out(e)
{
  if (BR_IE) e=event;
  var itemId = BR_IE?e.srcElement:e.target;
  itemId = cm_checkElement(itemId);  
  if (itemId && itemId.tagName=="DIV"){
	if (itemId.inactive)
		itemId.className = "cm_itemDisabled";
	else
		itemId.className = "cm_item";
  }
}

function cm_goToLink(e)
{
  cm_hideMenus();
  if (BR_IE) e=event;
  var itemId = BR_IE?e.srcElement:e.target;
  itemId = cm_checkElement(itemId);  
  	
  var fullPathToObject=""
  var pathToObject="";
  if (selectedItem){
  	fullPathToObject=selectedItem.getAttribute("fullPath");
  	pathToObject=selectedItem.getAttribute("path");
  }
  if (itemId && itemId.url != ""){
    if (itemId.url.toLowerCase().indexOf("javascript:") >= 0){
    	eval(itemId.url.substr(11).replace('%fullObjectpath%', fullPathToObject).replace('%objectpath%', pathToObject));
	}else
		window.open(itemId.url.replace('%fullObjectpath%', fullPathToObject).replace('%objectpath%', pathToObject), itemId.target);
  }
}

function cm_noClickEvent(e) {
  if (BR_IE) e=event;
  e.cancelBubble = true;
  return false;
}

function cm_correctBrowser()
{
  BR_NS = (document.layers) ? true : false;
  BR_IE = (document.all) ? true : false;
  return (document.getElementById)
}

if (cm_correctBrowser()){
    document.onload = cm_hideMenus;
  	document.oncontextmenu = cm_show;
  	document.onclick = cm_hideMenus;
}
