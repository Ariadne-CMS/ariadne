// this script is a modified version of the script found at 
// http://www.dhtml-now.de/dhtml/menue/menubar.asp
// by Andreas Zierhut Andreas.Zierhut@t-online.de

document.captureEvents(Event.KEYPRESS);
document.onkeypress = KeyHandler;

function KeyHandler(evt)
{

  if (!evt.which && evt.modifiers == 4) {
    if (activeMenu)
      HideOpenMenues();
    else if (hoverMenu)
      hoverMenu.NormalMenu(hoverMenuidx);
    else
      ARMenu.HoverMenu(0);
  }

  else if (evt.which == 32) {
    if (hoverMenu)
      hoverMenu.ClickMenu(hoverMenuidx);
  }

  else if (evt.which == 45) {

    if (activeMenu) {

      if (lastHover) {

        var idxn = lastHoveridx+1;
        if (idxn == lastHover.itemList.length)
          idxn = 0;
        lastHover.HoverMenu(idxn);
      }

    }

  }

  else if (evt.which == 46) {

    if (activeMenu) {

      if (lastHover) {

        var idxn = lastHoveridx-1;
        if (idxn == -1)
          idxn = lastHover.itemList.length-1;
        lastHover.HoverMenu(idxn);
      }

    }

  }

  else if (evt.which == 60) {

    if (hoverMenu && hoverMenuidx)
      hoverMenu.HoverMenu(hoverMenuidx-1);
    else if (hoverMenu && !hoverMenuidx)
      hoverMenu.HoverMenu(hoverMenu.itemList.length-1);

    if (activeMenu && activeMenuidx)
      activeMenu.HoverMenu(activeMenuidx-1);
    else if (activeMenu && !activeMenuidx)
      activeMenu.HoverMenu(activeMenu.itemList.length-1);

  }

  else if (evt.which == 121) {

    if (hoverMenu && hoverMenuidx+1 != hoverMenu.itemList.length)
      hoverMenu.HoverMenu(hoverMenuidx+1);
    else if (hoverMenu && hoverMenuidx+1 == hoverMenu.itemList.length)
      hoverMenu.HoverMenu(0);

    if (activeMenu && activeMenuidx+1 != activeMenu.itemList.length)
      activeMenu.HoverMenu(activeMenuidx+1);
    else if (activeMenu && activeMenuidx+1 == activeMenu.itemList.length)
      activeMenu.HoverMenu(0);

  }

  else {

    var chr = String.fromCharCode(evt.which).toLowerCase();

    if (activeMenu) {
      var Menu = window[activeMenu.itemList[activeMenuidx].SubMenu];
      for (var i=0; i < Menu.itemList.length; i++)
        if (Menu.itemList[i].ShortCut.toLowerCase() == chr)
          self.location.href = Menu.itemList[i].Link;
    }
    else {
      for (var i=0; i < ARMenu.itemList.length; i++)
        if (ARMenu.itemList[i].ShortCut.toLowerCase() == chr)
          ARMenu.ClickMenu(i);
    }

  }

}

function appMenu()
{
  this.itemList = new Array();
  this.Sep = new Array();
  this.type = 'appMenu';

  this.Color = '#000000';
  this.bgColor = '#C6C3C6';

  this.hoverColor = '#FFFFFF';
  this.hoverbgColor = '#000084';
}

function subMenu(Name)
{
  this.itemList = new Array();
  this.Sep = new Array();
  this.Name = Name;
  this.type = 'subMenu';

  this.Color = '#000000';
  this.bgColor = '#C6C3C6';

  this.fadeColor = '#999999';

  this.hoverColor = '#FFFFFF';
  this.hoverbgColor = '#000084';
}

appMenu.prototype.init                   = pinit;
appMenu.prototype.addMenuItem            = paddMenuItem;
subMenu.prototype.addMenuItem            = paddMenuItem;
appMenu.prototype.build                  = pbuild;
subMenu.prototype.build                  = pbuild;
appMenu.prototype.createSeparator        = pcreateSeparator;
appMenu.prototype.createMenuBottom       = pcreateMenuBottom;
subMenu.prototype.addSeparator           = paddSeparator;
subMenu.prototype.createSeparator        = pcreateSeparatorSub;

appMenu.prototype.createMenuCell         = pcreateMenuCell;
subMenu.prototype.MenuCell               = pSubMenuCell;
subMenu.prototype.createMenuCellHover    = pcreateMenuCellHover;
subMenu.prototype.createRahmen           = pcreateRahmen;
appMenu.prototype.createLeisteNachRechts = pcreateLeisteNachRechts;

appMenu.prototype.NormalMenu             = pNormalMenu;
appMenu.prototype.HoverMenu              = pHoverMenu;
subMenu.prototype.HoverMenu              = pHoverSubMenu;
appMenu.prototype.ClickMenu              = pClickMenu;
appMenu.prototype.HideSubMenues          = pHideSubMenues;
subMenu.prototype.HideSubMenues          = pHideSubMenues;

function pinit()
{
  this.createLeisteNachRechts();
  this.createMenuBottom();
  document.onmousedown = new Function('if (activeMenu) { ARMenu.HideSubMenues(); HideOpenMenues(); }');
}

function paddMenuItem(Caption, Link, SubMenu, Faded)
{
  var hotKey = Caption.indexOf('<');

  this.itemList[this.itemList.length] = new Object();
  this.itemList[this.itemList.length-1].Caption = Caption.substring(0, hotKey) + '<u>' + Caption.charAt(hotKey+1) + '</u>' + Caption.substring(hotKey+2);
  this.itemList[this.itemList.length-1].Name = 'mni' + Caption.replace(/</g, 'a').replace(/&/g, 'a').replace(/;/, 'a');
  this.itemList[this.itemList.length-1].ShortCut = Caption.charAt(hotKey+1);
  this.itemList[this.itemList.length-1].Link = Link;
  this.itemList[this.itemList.length-1].SubMenu = SubMenu;
  this.itemList[this.itemList.length-1].Parent = this;
  this.itemList[this.itemList.length-1].Index = this.itemList.length-1;
  this.itemList[this.itemList.length-1].Ebene = (this.Parent ? this.Parent.Ebene+1 : 0);
  this.itemList[this.itemList.length-1].Faded=Faded;

  if (SubMenu) {
    window[SubMenu] = new subMenu(SubMenu);
    window[SubMenu].Parent = this.itemList[this.itemList.length-1];
  }
}

function paddSeparator()
{
  this.Sep[this.itemList.length] = true;
}

function pbuild()
{

  if (this.type == 'subMenu') {

    var nm = (this.Parent.Parent.Parent ? this.Parent.Parent.Name + 'Index' + this.Parent.Index : '');
    var lft = (!this.Parent.Parent.Parent ? document[this.Parent.Name].pageX : document[nm].pageX + document[nm].document.width);
    var top = (!this.Parent.Parent.Parent ? document[this.Parent.Name].pageY + document[this.Parent.Name].document.height + 3 : document[nm].pageY);

    document.write ('<style type="text/css"> #' + this.Name + ' { position: absolute; left: ' + lft + '; top: ' + top + '; width: 1; height: 1; visibility: hidden; z-Index: ' + (this.itemList[0].Ebene * 5 + 5) + '; } </style>');

    this.Dok = '<span id="' + this.Name + '">\n' +
               '  <table border=0 cellspacing=0 cellpadding=0>\n';

  }

  for (var i=0; i < this.itemList.length; i++) {

    if (this.type == 'appMenu')
      this.createMenuCell(i);
    else {
      if (this.Sep[i])
        this.createSeparator();
      this.MenuCell(i);
    }

  }

  if (this.type == 'subMenu') {

    this.Dok += '  </table>\n' +
                '</span>\n';

    document.write (this.Dok);

    for (var i=0; i < this.itemList.length; i++)
      this.Dok += this.createMenuCellHover(i);

    this.createRahmen();
  }

  for (var i=0; i < this.itemList.length; i++)
    if (this.itemList[i].SubMenu)
      window[this.itemList[i].SubMenu].build();

}

function pcreateMenuCell(Index)
{

  Item = this.itemList[Index];

  var lft = 2;
  var top = 2;
  var nm = (Index ? this.itemList[Index-1].Name : '');

  if (Index)
    lft += document[nm].pageX + document[nm].document.width;

  document.write ('<style type="text/css"> #' + Item.Name + ' { position: absolute; left: ' + lft + '; top: ' + top + '; width: 1; height: 1; layer-background-color: #C6C3C6; z-Index: 4; } </style>');

  document.write ('<span id="' + Item.Name + '">\n' +
                  '  <table border=0 cellspacing=0 cellpadding=0>\n' +
                  '    <tr>\n' +
                  '      <td>\n' +
                  '        <img src="'+imagedir+'dot.gif" width=1 height=2 alt=""><br>\n' +
                  '      </td>\n' +
                  '    </tr>\n' +
                  '    <tr>\n' +
                  '      <td>\n' +
                  '        <a href="javascript:void(0)" class="MenuePunkt">&nbsp;' + Item.Caption + '&nbsp;</a><br>\n' +
                  '      </td>\n' +
                  '    </tr>\n' +
                  '    <tr>\n' +
                  '      <td>\n' +
                  '        <img src="'+imagedir+'dot.gif" width=1 height=2 alt=""><br>\n' +
                  '      </td>\n' +
                  '    </tr>\n' +
                  '  </table>\n' +
                  '</span>\n');

  nm = Item.Name;
  var wth = document[nm].document.width;
  var hht = document[nm].document.height;

  document.write ('<style type="text/css">\n' +
                  '  #O' + nm + ' { position: absolute; left: ' + lft + '; top: ' + top + '; width: 1; height: 1; z-Index: 1000; }\n' +
                  '  #' + nm + 'Links { position: absolute; left: ' + (lft-1) + '; top: ' + (top-1) + '; width: 1; height: 1; layer-background-color: #C6C3C6; z-Index: 3; }\n' +
                  '  #' + nm + 'Rechts { position: absolute; left: ' + (lft-1) + '; top: ' + (top-1) + '; width: 1; height: 1; layer-background-color: #C6C3C6; z-Index: 2; }\n' +
                  '</style>');

  document.write ('<span id="O' + nm + '">\n' +
                  '  <table border=0 cellspacing=0 cellpadding=0 width=' + wth + ' height=' + hht + '>\n' +
                  '    <tr>\n' +
                  '      <td><img src="'+imagedir+'dot.gif" width="1" height="1" alt=""></td>\n' +
                  '    </tr>\n' +
                  '  </table>\n' +
                  '</span>\n');

  document.write ('<span id="' + nm + 'Links">\n' +
                  '  <table border=0 cellspacing=0 cellpadding=0 width=' + (wth+1) + ' height=' + (hht+1) + '>\n' +
                  '    <tr>\n' +
                  '      <td>\n' +
                  '        <img src="'+imagedir+'dot.gif" width=1 height=1 alt=""><br>\n' +
                  '      </td>\n' +
                  '    </tr>\n' +
                  '  </table>\n' +
                  '</span>\n');

  document.write ('<span id="' + nm + 'Rechts">\n' +
                  '  <table border=0 cellspacing=0 cellpadding=0 width=' + (wth+2) + ' height=' + (hht+2) + '>\n' +
                  '    <tr>\n' +
                  '      <td>\n' +
                  '        <img src="'+imagedir+'dot.gif" width=1 height=1 alt=""><br>\n' +
                  '      </td>\n' +
                  '    </tr>\n' +
                  '  </table>\n' +
                  '</span>\n');

  document['O'+nm].captureEvents(Event.MOUSEDOWN);
  document['O'+nm].onmouseover = new Function ('ARMenu.HoverMenu(' + Index + ')');
  document['O'+nm].onmouseout  = new Function ('ARMenu.NormalMenu(' + Index + ')');
  document['O'+nm].onmousedown = new Function('ARMenu.ClickMenu(' + Index + ');return false;');

}

function pSubMenuCell(Index)
{
  if (this.itemList[Index].Faded==true) {
    aclass="MenuePunktFaded";
  } else {
    aclass="MenuePunkt";
  }
  this.Dok += '    <tr>\n' +
              '      <td bgColor="#C6C3C6" valign="top">\n' +
              '        <a name="Vorne' + this.Name + 'Index' + Index + '"><img src="'+imagedir+'dot.gif" width="20" height="20" alt=""></a><br>\n' +
              '      </td>\n' +
              '      <td bgColor="#C6C3C6">\n' +
              '        <a href="' + this.itemList[Index].Link + '" class="'+aclass+'">' + this.itemList[Index].Caption + '&nbsp;</a><br>\n' +
              '      </td>\n' +
              '      <td bgColor="#C6C3C6">\n' +
              '        <img src="'+imagedir+'dot.gif" width="5" height="1" alt=""><br>\n' +
              '      </td>\n' +
              '      <td bgColor="#C6C3C6" valign="bottom">\n' +
              '        <img src="' + (this.itemList[Index].SubMenu ? ''+imagedir+'menu/arrow.gif' : ''+imagedir+'dot.gif') + '" width="12" height="19" alt=""><a name="Hinten' + this.Name + 'Index' + Index + '"><img src="'+imagedir+'dot.gif" width="1" height="1" alt=""></a><br>\n' +
              '      </td>\n' +
              '    </tr>\n';
}

function pcreateSeparatorSub()
{
  this.Dok += '    <tr>\n' +
              '      <td colspan=4 bgColor="#C3C6C3"><img src="'+imagedir+'dot.gif" width="1" height="2" alt=""></td>\n' +
              '    </tr>\n' +
              '    <tr>\n' +
              '      <td colspan=4 bgColor="#828482"><img src="'+imagedir+'dot.gif" width="1" height="1" alt=""></td>\n' +
              '    </tr>\n' +
              '    <tr>\n' +
              '      <td colspan=4 bgColor="#FFFFFF"><img src="'+imagedir+'dot.gif" width="1" height="1" alt=""></td>\n' +
              '    </tr>\n' +
              '    <tr>\n' +
              '      <td colspan=4 bgColor="#C3C6C3"><img src="'+imagedir+'dot.gif" width="1" height="2" alt=""></td>\n' +
              '    </tr>\n';
}

function pcreateMenuCellHover(i)
{
  var nm = this.Name + 'Index' + i;
  var lft = document[this.Name].pageX + document[this.Name].document.anchors['Vorne' + this.Name + 'Index' + i].x;
  var top = document[this.Name].pageY + document[this.Name].document.anchors['Vorne' + this.Name + 'Index' + i].y;

  var wth = document[this.Name].document.anchors['Hinten' + this.Name + 'Index' + i].x - document[this.Name].document.anchors['Vorne' + this.Name + 'Index' + i].x + 1;
  var hht = document[this.Name].document.anchors['Hinten' + this.Name + 'Index' + i].y - document[this.Name].document.anchors['Vorne' + this.Name + 'Index' + i].y + 19;

  if (this.itemList[i].Faded==true) {
    aclass="MenuePunktHoverFaded";
  } else {
    aclass="MenuePunktHover";
  }

  document.write ('<style type="text/css">\n' +
                  '  #' + nm + ' { position: absolute; left: ' + lft + '; top: ' + top + '; visibility: hidden; z-Index: ' + (this.itemList[i].Ebene * 5 + 5) + '; }\n' +
                  '  #O' + nm + ' { position: absolute; left: ' + lft + '; top: ' + top + '; visibility: hidden; z-Index: 1000; }\n' +
                  '</style>');

  document.write ('<span id="' + nm + '">\n' +
                  '  <table border=0 cellspacing=0 cellpadding=0 width="' + wth + '" height="' + hht + '">\n' +
                  '    <tr>\n' +
                  '      <td bgColor="#000084" width="20" valign="top">\n' +
                  '        <img src="'+imagedir+'dot.gif" width="20" height="20" alt=""><br>\n' +
                  '      </td>\n' +
                  '      <td bgColor="#000084" width="' + (wth - 37) + '">\n' +
                  '        <a href="' + this.itemList[i].Link + '" class="' + aclass + '">' + this.itemList[i].Caption + '&nbsp;</a><br>\n' +
                  '      </td>\n' +
                  '      <td width="5" bgColor="#000084">\n' +
                  '        <img src="'+imagedir+'dot.gif" width="5" height="1" alt=""><br>\n' +
                  '      </td>\n' +
                  '      <td bgColor="#000084" width="12" valign="bottom">\n' +
                  '        <img src="' + (this.itemList[i].SubMenu ? ''+imagedir+'menu/arrowhigh.gif' : ''+imagedir+'dot.gif') + '" width="12" height="19" alt=""><img src="'+imagedir+'dot.gif" width="1" height="1" alt=""><br>\n' +
                  '      </td>\n' +
                  '    </tr>\n' +
                  '  </table>\n' +
                  '</span>\n');

  document.write ('<span id="O' + nm + '">\n' +
                  '  <table border=0 cellspacing=0 cellpadding=0 width=' + wth + ' height=' + hht + '>\n' +
                  '    <tr>\n' +
                  '      <td><img src="'+imagedir+'dot.gif" width="1" height="1" alt=""></td>\n' +
                  '    </tr>\n' +
                  '  </table>\n' +
                  '</span>\n');

  document['O'+nm].captureEvents(Event.MOUSEDOWN);
  document['O'+nm].onmouseover = new Function('evt', this.Parent.SubMenu + '.HoverMenu(' + i + ');');
  document['O'+nm].onmouseout = HideHoverSubMenu;
  document['O'+nm].onmousedown = new Function ('self.location.href = "' + document[this.Name + 'Index' + i].document.links[0].href + '"; return false;');

}

function pcreateRahmen()
{
  var lft = document[this.Name].pageX;
  var top = document[this.Name].pageY;
  var wth = document[this.Name].document.width;
  var hht = document[this.Name].document.height;
  var eb  = this.itemList[0].Ebene;

  document.write ('<style type="text/css">\n' +
                  '  #Rahmen' + this.Name + ' { position: absolute; left: ' + (lft-2) + '; top: ' + (top-2) + '; width: 1; height: 1; visibility: hidden; z-Index: ' + (5 * eb + 2) + '; }\n' +
                  '  #Rahmen2' + this.Name + ' { position: absolute; left: ' + (lft-1) + '; top: ' + (top-1) + '; width: 1; height: 1; visibility: hidden; z-Index: ' + (5 * eb + 4) + '; }\n' +
                  '  #Rahmen3' + this.Name + ' { position: absolute; left: ' + (lft-1) + '; top: ' + (top-1) + '; width: 1; height: 1; visibility: hidden; z-Index: ' + (5 * eb + 3) + '; }\n' +
                  '  #Rahmen4' + this.Name + ' { position: absolute; left: ' + (lft-2) + '; top: ' + top + '; width: 1; height: 1; visibility: hidden; z-Index: ' + (5 * eb + 1) + '; }\n' +
                  '</style>');

  document.write ('<span id="Rahmen' + this.Name + '">\n' +
                  '  <table border=0 cellspacing=0 cellpadding=0 width="' + (wth+3) + '" height="' + (hht+3) + '">\n' +
                  '    <tr>\n' +
                  '      <td bgColor="#C6C3C6"><img src="'+imagedir+'dot.gif" width="1" height="1" alt=""></td>\n' +
                  '    </tr>\n' +
                  '  </table>\n' +
                  '</span>\n');

  document.write ('<span id="Rahmen2' + this.Name + '">\n' +
                  '  <table border=0 cellspacing=0 cellpadding=0 width="' + (wth+1) + '" height="' + (hht+1) + '">\n' +
                  '    <tr>\n' +
                  '      <td bgColor="#FFFFFF"><img src="'+imagedir+'dot.gif" width="1" height="1" alt=""></td>\n' +
                  '    </tr>\n' +
                  '  </table>\n' +
                  '</span>\n');

  document.write ('<span id="Rahmen3' + this.Name + '">\n' +
                  '  <table border=0 cellspacing=0 cellpadding=0 width="' + (wth+2) + '" height="' + (hht+2) + '">\n' +
                  '    <tr>\n' +
                  '      <td bgColor="#828482"><img src="'+imagedir+'dot.gif" width="1" height="1" alt=""></td>\n' +
                  '    </tr>\n' +
                  '  </table>\n' +
                  '</span>\n');

  document.write ('<span id="Rahmen4' + this.Name + '">\n' +
                  '  <table border=0 cellspacing=0 cellpadding=0 width="' + (wth+4) + '" height="' + (hht+2) + '">\n' +
                  '    <tr>\n' +
                  '      <td bgColor="#000000"><img src="'+imagedir+'dot.gif" width="1" height="1" alt=""></td>\n' +
                  '    </tr>\n' +
                  '  </table>\n' +
                  '</span>\n');

}

var lastHover = null;
var lastHoveridx = -1;
var openMenues = '';

function pHoverSubMenu(i)
{
  this.HideSubMenues();

  var l = document[this.Name];
  var nm = this.Name + 'Index' + i;
  document[nm].visibility = 'show';
  if (lastHover)
    document[lastHover.Name + 'Index' + lastHoveridx].visibility = 'hide';
  lastHover = this;
  lastHoveridx = i;

  if (this.itemList[i].SubMenu) {
    openMenues += this.itemList[i].SubMenu + ',';
    setVisible(this.itemList[i].SubMenu, true);
  }
}

function pHideSubMenues()
{
  if (openMenues) {

    var mns = openMenues.substring(0, openMenues.length-1).split(',');

    var i = mns.length-1;
    for (; i >= 0; i--) {
      if (mns[i] != this.Name)
        setVisible(mns[i], 'hidden');
      else
        break;
    }

    openMenues = '';
    for (var j=0; j <= i; j++)
      openMenues += mns[j] + ',';
  }

}

function HideHoverSubMenu()
{
  document[lastHover.Name + 'Index' + lastHoveridx].visibility = 'hide';
  lastHover = null;
}

var activeMenu = null;
var activeMenuidx = 0;

var hoverMenu = null;
var hoverMenuidx = 0;

function pHoverMenu(Index)
{

  this.HideSubMenues();

  if (!activeMenu && (!hoverMenu || hoverMenu.itemList[hoverMenuidx] != this)) {

    if (hoverMenu)
      hoverMenu.NormalMenu(hoverMenuidx);

    var nm = this.itemList[Index].Name;
    document[nm + 'Links'].bgColor = '#FFFFFF';
    document[nm + 'Rechts'].bgColor = '#848284';

    hoverMenu = this;
    hoverMenuidx = Index;
  }
  else if (activeMenu)
    this.ClickMenu(Index);

}

function hideHover()
{
  if (menuHover)
    document[menuHover].visibility = 'hide';
}

function pClickMenu(Index)
{

  if (!activeMenu || (activeMenu != this || activeMenuidx != Index)) {

    var nm = this.itemList[Index].Name;
    var sb = this.itemList[Index].SubMenu;

    if (hoverMenu && (hoverMenu != this || Index != hoverMenuidx))
      hoverMenu.NormalMenu(hoverMenuidx);

    hoverMenu = null;
    hoverMenuidx = 0;

    document[nm + 'Links'].bgColor = '#848284';
    document[nm + 'Rechts'].bgColor = '#FFFFFF';

    if (!activeMenu || activeMenu.itemList[activeMenuidx].Name != sb) {

      setVisible(sb, 'show');

      if (activeMenu)
        HideOpenMenues();

      activeMenu = this;
      activeMenuidx = Index;

    }
  }
}

function HideOpenMenues()
{
  setVisible(activeMenu.itemList[activeMenuidx].SubMenu, 'hide');
  var opOld = activeMenu;
  activeMenu = null;
  opOld.NormalMenu(activeMenuidx);
}

function setVisible(mn, wert)
{
  document['Rahmen' + mn].visibility = wert;
  document['Rahmen2' + mn].visibility = wert;
  document['Rahmen3' + mn].visibility = wert;
  document['Rahmen4' + mn].visibility = wert;
  document[mn].visibility = wert;
  for (var i=0; i < window[mn].itemList.length; i++)
    document['O'+mn+'Index'+i].visibility = wert;
}

function pNormalMenu(Index)
{
  if (!activeMenu) {
    hoverMenu = null;
    var nm = this.itemList[Index].Name;
    document[nm + 'Links'].bgColor = '#C6C3C6';
    document[nm + 'Rechts'].bgColor = '#C6C3C6';
  }
}

function pcreateSeparator()
{
  var obj = document[ARMenu.itemList[0].Name];
  var top = obj.pageY+1;
  var hht = obj.document.height;
  var wthges = window.innerWidth;

  document.write ('<style type="text/css"> #GesSeparator { position: absolute; left: 0; top: ' + (top+hht) + '; width: 1; height: 1; z-Index: 2; } </style>');

  document.write('<span id="GesSeparator">\n' +
                 '  <table border=0 cellspacing=0 cellpadding=0 width=' + wthges + ' height=5>\n' +
                 '    <tr><td bgColor="#C3C6C3"><img src="'+imagedir+'dot.gif" width="1" height="2" alt=""></td></tr>\n' +
                 '    <tr><td bgColor="#828482"><img src="'+imagedir+'dot.gif" width="1" height="1" alt=""></td></tr>\n' +
                 '    <tr><td bgColor="#FFFFFF"><img src="'+imagedir+'dot.gif" width="1" height="1" alt=""></td></tr>\n' +
                 '    <tr><td bgColor="#C3C6C3"><img src="'+imagedir+'dot.gif" width="1" height="2" alt=""></td></tr>\n' +
                 '  </table>\n' +
                 '</span>\n');
}

function pcreateMenuBottom()
{
  var obj = document[ARMenu.itemList[0].Name];
  var top = obj.pageY+1;
  var hht = obj.document.height;
  var wthges = window.innerWidth;

  document.write ('<style type="text/css"> #GesSeparator { position: absolute; left: 0; top: ' + (top+hht) + '; width: 1; height: 1; z-Index: 2; } </style>');

  document.write('<span id="GesSeparator">\n' +
                 '  <table border=0 cellspacing=0 cellpadding=0 width=' + wthges + ' height=3>\n' +
                 '    <tr><td bgColor="#C3C6C3"><img src="'+imagedir+'dot.gif" width="1" height="1" alt=""></td></tr>\n' +
                 '    <tr><td bgColor="#828482"><img src="'+imagedir+'dot.gif" width="1" height="1" alt=""></td></tr>\n' +
                 '    <tr><td bgColor="#000000"><img src="'+imagedir+'dot.gif" width="1" height="1" alt=""></td></tr>\n' +
                 '  </table>\n' +
                 '</span>\n');
}

function pcreateLeisteNachRechts()
{
  var nm = this.itemList[this.itemList.length-1].Name;
  var lft = document[nm].pageX + 1;
  var top = document[nm].pageY - 1;
  var wth = document[nm].document.width;
  var hht = document[nm].document.height + 2;
  nm += 'LeisteNachRechts';
  var wthges = window.innerWidth - (lft + wth);

  document.write ('<style type="text/css"> #' + nm + ' {  position: absolute; left: ' + (lft+wth) + '; top: ' + top + '; width: 1; height: 1; layer-background-color: #C6C3C6; z-Index: 2; } </style>');

  document.write ('<span id="' + nm + '">\n' +
                  '  <table border=0 cellspacing=0 cellpadding=0 width=' + wthges + ' height=' + hht + '>\n' +
                  '    <form name="pathform" onSubmit="return viewpath(this.path.value);"><tr>\n' +
                  '      <td valign="middle" align="right">\n' +
                  '        <input style="font-size: 13px; height: 18px" type="text" name="path" size="40" value="'+path+'">\n' +
                  '      </td>\n' +
                  '    </tr></form>\n' +
                  '  </table>\n' +
                  '</span>\n');

  document[nm].captureEvents (Event.MOUSEDOWN);
  document[nm].onmousedown = new Function('if (activeMenu) { ARMenu.HideSubMenues(); HideOpenMenues(); }');

}

// default values for opening windows
windowprops=new Array();
windowprops['common']='directories=no,location=no,menubar=no,status=no,toolbar=no,resizable=yes';
windowprops['full']='directories=no,location=yes,menubar=yes,status=yes,toolbar=yes,resizable=yes';
windowprops['object_fs']=windowprops['common']+',height=250,width=450';
windowprops['object_new']=windowprops['common']+',height=275,width=500';
windowprops['edit_find']=windowprops['common']+',height=400,width=500';
windowprops['edit_preferences']=windowprops['common']+',height=400,width=500';
windowprops['edit_object_data']=windowprops['common']+',height=275,width=500';
windowprops['edit_object_cache']=windowprops['common']+',height=250,width=250';
windowprops['edit_object_layout']=windowprops['common']+',height=500,width=600';
windowprops['edit_object_shortcut']=windowprops['common']+',height=250,width=500';
windowprops['edit_object_grants']=windowprops['common']+',height=300,width=700';
windowprops['edit_object_types']=windowprops['common']+',height=150,width=250';
windowprops['edit_object_nls']=windowprops['common']+',height=250,width=400';
windowprops['edit_priority']=windowprops['common']+',height=150,width=250';
windowprops['view_fonts']=windowprops['common']+',height=300,width=450';
windowprops['_new']=windowprops['full'];
windowprops['help']=windowprops['common']+',height=350,width=450';
windowprops['help_about']=windowprops['common']+',height=250,width=450';

function viewpath(path) {
  top.View(path);
  return false;
}

function arshow(windowname, link) { 
  // FIXME: save windowproperties on close and reuse them, but remember,
  // there need not be a frameset around the current window
  // properties=top.Get('window.'+windowname);
  // if (!properties) {
    properties=windowprops[windowname];
  // }
  workwindow=window.open(link, windowname, properties);
  ARMenu.HideSubMenues(); 
  HideOpenMenues();
  // it seems that the above functions are still running some code when 
  // the next line is executed, and netscape just returns focus to the
  // current window instead of the workwindow. With a delay it works..
  window.setTimeout("workwindow.focus()",500);
}



function setview(type) {
  alert(type);
  ARMenu.HideSubMenues();
  HideOpenMenues();
}
