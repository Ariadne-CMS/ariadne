/*HM_Loader.js
* by Peter Belesis. v4.0.14 010810
* Copyright (c) 2001 Peter Belesis. All Rights Reserved.
*
* Changed file to config.php
*	arguments:
*		arDirWWW	the Ariadne www directory
*
*/

   HM_DOM = (document.getElementById) ? true : false;
   HM_NS4 = (document.layers) ? true : false;
    HM_IE = (document.all) ? true : false;
   HM_IE4 = HM_IE && !HM_DOM;
   HM_Mac = (navigator.appVersion.indexOf("Mac") != -1);
  HM_IE4M = HM_IE4 && HM_Mac;
HM_IsMenu = (HM_DOM || HM_NS4 || (HM_IE4 && !HM_IE4M));

HM_BrowserString = HM_NS4 ? "NS4" : HM_DOM ? "DOM" : "IE4";

<?php
	$arDirWWW = $HTTP_GET_VARS["arDirWWW"];
?>

if(window.event + "" == "undefined") event = null;

function HM_f_PopUp(){return false};
function HM_f_PopDown(){return false};
popUp = HM_f_PopUp;
popDown = HM_f_PopDown;

HM_GL_MenuWidth          = 200;
HM_GL_FontFamily         = "Helvetica,Arial,sans-serif";
HM_GL_FontSize           = 9;
HM_GL_FontBold           = false;
HM_GL_FontItalic         = false;
HM_GL_FontColor          = "black";
HM_GL_FontColorOver      = "white";
if (HM_NS4) {
	HM_GL_BGColor            = "#bfbfbf";
} else {
	HM_GL_BGColor            = "buttonface";
}
HM_GL_BGColorOver        = "#003F82";
HM_GL_ItemPadding        = 2;

HM_GL_BorderWidth        = 2;
HM_GL_BorderColor        = "#DDDDDD";
HM_GL_BorderStyle        = "outset";
HM_GL_SeparatorSize      = 0;
HM_GL_SeparatorColor     = "#D3D3D3";

HM_GL_ImageSrc = "<?php echo $arDirWWW; ?>widgets/menu/HM_More_black_right.gif";
HM_GL_ImageSrcLeft = "<?php echo $arDirWWW; ?>widgets/menu/HM_More_black_left.gif";

HM_GL_ImageSrcOver = "<?php echo $arDirWWW; ?>widgets/menu/HM_More_white_right.gif";
HM_GL_ImageSrcLeftOver = "<?php echo $arDirWWW; ?>widgets/menu/HM_More_white_left.gif";

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


HM_MenuIDPrefix = "HM_Menu";
HM_ItemIDPrefix = "HM_Item";
HM_ArrayIDPrefix = "HM_Array";

HM_a_Parameters = [
	["MenuWidth",          200,		"number"],
	["FontFamily",         "Arial,sans-serif"],
	["FontSize",           9,		"number"],
	["FontBold",           false,	"boolean"],
	["FontItalic",         false,	"boolean"],
	["FontColor",          "black"],
	["FontColorOver",      "white"],
	["BGColor",            "white"],
	["BGColorOver",        "black"],
	["ItemPadding",        3,		"number"],
	["BorderWidth",        2,		"number"],
	["BorderColor",        "red"],
	["BorderStyle",        "solid"],
	["SeparatorSize",      1,		"number"],
	["SeparatorColor",     "yellow"],
	["ImageSrc",           "HM_More_black_right.gif"],
	["ImageSrcOver",       null],
	["ImageSrcLeft",       "HM_More_black_left.gif"],
	["ImageSrcLeftOver",   null],
	["ImageSize",          5,		"number"],
	["ImageHorizSpace",    0,		"number"],
	["ImageVertSpace",     0,		"number"],
	["KeepHilite",         false,	"boolean"],
	["ClickStart",         false,	"boolean"],
	["ClickKill",          true,	"boolean"],
	["ChildOverlap",       20,		"number"],
	["ChildOffset",        10,		"number"],
	["ChildPerCentOver",   null,	"number"],
	["TopSecondsVisible",  .5,		"number"],
	["ChildSecondsVisible",.3,		"number"],
	["StatusDisplayBuild", 1,		"boolean"],
	["StatusDisplayLink",  1,		"boolean"],
	["UponDisplay",        null,	"delayed"],
	["UponHide",           null,	"delayed"],
	["RightToLeft",        false,	"boolean"],
	["CreateTopOnly",      0,		"boolean"],
	["ShowLinkCursor",     false,	"boolean"],
	["NSFontOver",         true,    "boolean"]
]

Function.prototype.isFunction = true;
Function.prototype.isString = false;
String.prototype.isFunction = false;
String.prototype.isString = true;
String.prototype.isBoolean = false;
String.prototype.isNumber = false;
Number.prototype.isString = false;
Number.prototype.isFunction = false;
Number.prototype.isBoolean = false;
Number.prototype.isNumber = true;
Boolean.prototype.isString = false;
Boolean.prototype.isFunction = false;
Boolean.prototype.isBoolean = true;
Boolean.prototype.isNumber = false;
Array.prototype.itemValidation = false;
Array.prototype.isArray = true;

