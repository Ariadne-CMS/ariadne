/*HM_Loader.js
* by Peter Belesis. v4.0.14 010810
* Copyright (c) 2001 Peter Belesis. All Rights Reserved.
*/

   HM_DOM = (document.getElementById) ? true : false;
   HM_NS4 = (document.layers) ? true : false;
    HM_IE = (document.all) ? true : false;
   HM_IE4 = HM_IE && !HM_DOM;
   HM_Mac = (navigator.appVersion.indexOf("Mac") != -1);
  HM_IE4M = HM_IE4 && HM_Mac;
HM_IsMenu = (HM_DOM || HM_NS4 || (HM_IE4 && !HM_IE4M));

HM_BrowserString = HM_NS4 ? "NS4" : HM_DOM ? "DOM" : "IE4";

if(window.event + "" == "undefined") event = null;
function HM_f_PopUp(){return false};
function HM_f_PopDown(){return false};
popUp = HM_f_PopUp;
popDown = HM_f_PopDown;

HM_GL_MenuWidth          = 150;
HM_GL_FontFamily         = "Verdana,Helvetica,Arial,sans-serif";
HM_GL_FontSize           = 10;
HM_GL_FontBold           = false;
HM_GL_FontItalic         = false;
HM_GL_FontColor          = "black";
HM_GL_FontColorOver      = "white";
HM_GL_BGColor            = "#dfdfdf";
HM_GL_BGColorOver        = "#003F82";
HM_GL_ItemPadding        = 2;

HM_GL_BorderWidth        = 2;
HM_GL_BorderColor        = "#DDDDDD";
HM_GL_BorderStyle        = "outset";
HM_GL_SeparatorSize      = 0;
HM_GL_SeparatorColor     = "#D3D3D3";

HM_GL_ImageSrc = "/widgets/menu/HM_More_blue_right.gif";
HM_GL_ImageSrcLeft = "/widgets/menu/HM_More_blue_left.gif";

HM_GL_ImageSrcOver = "/widgets/menu/HM_More_white_right.gif";
HM_GL_ImageSrcLeftOver = "/widgets/menu/HM_More_white_left.gif";

HM_GL_ImageSize          = 5;
HM_GL_ImageHorizSpace    = 0;
HM_GL_ImageVertSpace     = 5;

HM_GL_KeepHilite         = false;
HM_GL_ClickStart         = false;
HM_GL_ClickKill          = false;
HM_GL_ChildOverlap       = 7;
HM_GL_ChildOffset        = 7;
HM_GL_ChildPerCentOver   = null;
HM_GL_TopSecondsVisible  = .5;
HM_GL_ChildSecondsVisible = .3;
HM_GL_StatusDisplayBuild = 1;
HM_GL_StatusDisplayLink  = 1;
HM_GL_UponDisplay        = null;
HM_GL_UponHide           = null;

//HM_GL_RightToLeft      = true;
HM_GL_CreateTopOnly      = HM_NS4 ? true : false;
HM_GL_ShowLinkCursor     = true;
HM_GL_NSFontOver = true;
//HM_a_TreesToBuild = [1,2,3,4];
