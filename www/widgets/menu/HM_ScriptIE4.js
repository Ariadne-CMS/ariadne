/*HM_ScriptIE4.js
* by Peter Belesis. v4.0.14 010810
* Copyright (c) 2001 Peter Belesis. All Rights Reserved.
* Originally published and documented at http://www.dhtmlab.com/
* You may use this code only if this entire
* copyright notice appears unchanged and you publicly display
* a link to http://www.dhtmlab.com/.
*
* Contact peterbelesis@yahoo.co.uk for all other uses.
*/

function HM_f_AssignParameters(paramarray){
	var ParamName = paramarray[0];
	var DefaultValue = paramarray[1];
	var FullParamName = "HM_" + ParamName;

	if (typeof eval("window.HM_PG_" + ParamName) == "undefined") {
		if (typeof eval("window.HM_GL_" + ParamName) == "undefined") {
			eval(FullParamName + "= DefaultValue");
		}
		else {
			eval(FullParamName + "= HM_GL_" + ParamName);
		}
	}
	else {
		eval(FullParamName + "= HM_PG_" + ParamName);
	}

	paramarray[0] = FullParamName;
	paramarray[1] = eval(FullParamName);
}

function HM_f_EvalParameters(valuenew,valueold,valuetype){
	var TestString, ParPosition;

	if(typeof valuenew == "undefined" || valuenew == null || (valuenew.isString && valuenew.length == 0)){
		return valueold;
	}

	if(valuetype != "delayed"){
		while(valuenew.isString) {
			ParPosition = valuenew.indexOf("(");
			if(ParPosition !=-1) {
				TestString = "window." + valuenew.substr(0,ParPosition);
				if (typeof eval(TestString) != "undefined" && eval(TestString).isFunction) {
					valuenew = eval(valuenew);
				}
			}
			else break
		}
	}

	while(valuenew.isFunction) {valuenew = valuenew()}

	switch(valuetype){	
		case "number":
			while (valuenew.isString) {valuenew = eval(valuenew)}
			break;
		case "boolean":
			while (!valuenew.isBoolean) {
				valuenew = (valuenew.isNumber) ? valuenew ? true : false : eval(valuenew);
			}
			break;
	}

	return valuenew;
}

for (i=0;i<HM_a_Parameters.length;i++) {
	HM_f_AssignParameters(HM_a_Parameters[i]);
	eval(HM_a_Parameters[i][0] + "= HM_f_EvalParameters("+ HM_a_Parameters[i][0] +",null,HM_a_Parameters[i][2])")
}

HM_ChildPerCentOver = (isNaN(parseFloat(HM_ChildPerCentOver))) ? null : parseFloat(HM_ChildPerCentOver)/100;

HM_ChildMilliSecondsVisible = HM_ChildSecondsVisible * 1000;

function HM_f_ValidateArray(arrayname){
	var MenuArrayIsValid = false;
	var MenuArrayIsObject = (typeof eval("window." + arrayname) == "object");
	if(MenuArrayIsObject) { 
		var TheMenuArray = eval(arrayname);
		if(TheMenuArray.isArray && TheMenuArray.length > 1) {
			MenuArrayIsValid = true;
			if(!TheMenuArray.itemValidation) {
				while((typeof TheMenuArray[TheMenuArray.length-1] != "object") || (!TheMenuArray[TheMenuArray.length-1].isArray)) {
					TheMenuArray.length--;
				}
				TheMenuArray.itemValidation = true;
			}
		}
	}
	return MenuArrayIsValid;
}

if(!window.HM_a_TreesToBuild) {
	HM_a_TreesToBuild = [];
	for(i=1; i<100; i++){
		if(HM_f_ValidateArray(HM_ArrayIDPrefix + i)) HM_a_TreesToBuild[HM_a_TreesToBuild.length] = i;
	}
}

HM_CurrentArray = null;
HM_CurrentTree  = null;
HM_CurrentMenu  = null;
HM_CurrentItem  = null;
HM_a_TopMenus = [];
HM_AreLoaded = false;
HM_AreCreated = false;
HM_BeingCreated = false;
HM_UserOverMenu = false;
HM_HideAllTimer = null;
HM_TotalTrees = 0; 
HM_ZIndex = 5000;

function propertyTransfer(){
	this.obj = eval(this.id + "Obj");
	for (temp in this.obj) {this[temp] = this.obj[temp]}
}

function HM_f_StartIt() {
	if((typeof(document.body) == "undefined") || (document.body == null)) return;
	if(HM_AreCreated) return;
	HM_AreLoaded = true;
	if (HM_ClickKill) {
		HM_f_OtherMouseDown = (document.onmousedown) ? document.onmousedown : new Function;
    	document.onmousedown = function(){HM_f_PageClick();HM_f_OtherMouseDown()}
    }
	else {
		HM_TopMilliSecondsVisible = HM_TopSecondsVisible * 1000;
	}
    HM_f_MakeTrees();
	HM_f_OtherOnLoad();
}

function HM_f_MakeTrees(){
    HM_BeingCreated = true;
	var TreeParams = null;
	var TreeHasChildren = false;
	var ItemArray = null;

	for(var t=0; t<HM_a_TreesToBuild.length; t++) {
		if(!HM_f_ValidateArray(HM_ArrayIDPrefix + HM_a_TreesToBuild[t])) continue;
		HM_CurrentArray = eval(HM_ArrayIDPrefix + HM_a_TreesToBuild[t]);

		TreeParams = HM_CurrentArray[0];
		TreeHasChildren = false;

		for(var i=1; i<HM_CurrentArray.length; i++) {
			ItemArray = HM_CurrentArray[i];
			if(ItemArray[ItemArray.length-1]) {TreeHasChildren = true; break}
		}

		HM_CurrentTree = {
			MenuWidth        : MenuWidth = HM_f_EvalParameters(TreeParams[0],HM_MenuWidth,"number"),
			MenuLeft         : MenuLeft = HM_f_EvalParameters(TreeParams[1],null,"delayed"),
			MenuTop          : MenuTop = HM_f_EvalParameters(TreeParams[2],null,"delayed"),
			ItemWidth        : ItemWidth = MenuWidth - (HM_BorderWidth*2),
			ItemTextWidth    : TreeHasChildren ? (ItemWidth - (HM_ImageSize + HM_ImageHorizSpace + HM_ItemPadding)) : ItemWidth,
			HorizOffsetRight : HorizOffsetRight = (parseInt((HM_ChildPerCentOver != null) ? (HM_ChildPerCentOver  * ItemWidth) : HM_ChildOverlap)),
			HorizOffsetLeft  : (MenuWidth - HorizOffsetRight),
			FontColor        : HM_f_EvalParameters(TreeParams[3],HM_FontColor),
			FontColorOver    : HM_f_EvalParameters(TreeParams[4],HM_FontColorOver),
			BGColor          : HM_f_EvalParameters(TreeParams[5],HM_BGColor),
			BGColorOver      : HM_f_EvalParameters(TreeParams[6],HM_BGColorOver),
			BorderColor      : HM_f_EvalParameters(TreeParams[7],HM_BorderColor),
			SeparatorColor   : HM_f_EvalParameters(TreeParams[8],HM_SeparatorColor),
			TopIsPermanent   : ((MenuLeft == null) || (MenuTop == null)) ? false : HM_f_EvalParameters(TreeParams[9],false,"boolean"),
			TopIsHorizontal  : TopIsHorizontal = HM_f_EvalParameters(TreeParams[10],false,"boolean"),
			TreeIsHorizontal : TreeHasChildren ? HM_f_EvalParameters(TreeParams[11],false,"boolean") : false,
			PositionUnder    : (!TopIsHorizontal || !TreeHasChildren) ? false : HM_f_EvalParameters(TreeParams[12],false,"boolean"),
			TopImageShow     : TreeHasChildren ? HM_f_EvalParameters(TreeParams[13],true,"boolean")  : false,
			TreeImageShow    : TreeHasChildren ? HM_f_EvalParameters(TreeParams[14],true,"boolean")  : false,
			UponDisplay      : HM_f_EvalParameters(TreeParams[15],HM_UponDisplay,"delayed"),
			UponHide         : HM_f_EvalParameters(TreeParams[16],HM_UponHide,"delayed"),
			RightToLeft      : HM_f_EvalParameters(TreeParams[17],HM_RightToLeft,"boolean"),
			ClickStart		 : HM_f_EvalParameters(TreeParams[18],HM_ClickStart,"boolean")
		}

		HM_CurrentMenu = null;
		HM_f_MakeMenu(HM_a_TreesToBuild[t]);
		HM_a_TopMenus[HM_TotalTrees] = HM_CurrentTree.treeParent;
		HM_TotalTrees++;
		if(HM_CurrentTree.TopIsPermanent){
			with(HM_CurrentTree.treeParent) {
				HM_CurrentTree.treeParent.xPos = eval(HM_CurrentTree.MenuLeft);
				HM_CurrentTree.treeParent.yPos = eval(HM_CurrentTree.MenuTop);
				moveTo(HM_CurrentTree.treeParent.xPos,HM_CurrentTree.treeParent.yPos);
				style.zIndex = HM_ZIndex;
				setTimeout(HM_CurrentTree.treeParent.id + ".fixSize(true)",10);
			}
		}
    }

	if(HM_StatusDisplayBuild) status = HM_TotalTrees + " Hierarchical Menu Trees Created";
    HM_AreCreated = true;
    HM_BeingCreated = false;
}

function HM_f_GetItemDivStr(itemid,disptext,hasmore){
	var WidthValue = HM_CurrentMenu.isHorizontal ? (ItemElement.isLastItem) ? (HM_CurrentTree.MenuWidth - HM_BorderWidth - HM_SeparatorSize) : (HM_CurrentTree.MenuWidth - HM_BorderWidth) : HM_CurrentTree.ItemWidth;
	var TempString = "<DIV ID=" + itemid + " STYLE='position:absolute;width:" + WidthValue + "px'>";
	if(HM_CurrentMenu.showImage) {
		var FullPadding  = (HM_ItemPadding*2) + HM_ImageSize + HM_ImageHorizSpace;
	}
    if(hasmore && HM_CurrentMenu.showImage) {
		var ImgPosition = HM_CurrentTree.RightToLeft ? "absolute;" : "relative;";
		var ImgSrc      = HM_CurrentTree.RightToLeft ? HM_ImageSrcLeft : HM_ImageSrc;
		var ImgHSpace   = (HM_CurrentTree.RightToLeft) ? 0 : HM_ItemPadding;
		var ImgStyle    = HM_CurrentTree.RightToLeft ? ("left:"+ (HM_ItemPadding + HM_ImageHorizSpace) + "px;top:"+ (HM_ItemPadding + HM_ImageVertSpace) + "px;") : ("float:right;margin-right:"+ (-FullPadding) +"px;margin-top:"+ HM_ImageVertSpace + "px;width:"+ HM_ImageSize + "px;");
		var ImgString   = "<IMG ID='HM_ImMore' STYLE='position:"+ ImgPosition + ImgStyle +"' SRC='" + ImgSrc + "' HSPACE="+ ImgHSpace +" VSPACE=0 BORDER=0>";
		TempString += ImgString;
	}
	TempString += disptext + "</DIV>";
	return TempString;
}

function HM_f_SetItemProperties(itemid,itemidsuffix) {
	this.tree        = HM_CurrentTree;
	this.itemsetup   = HM_f_ItemSetup;
	this.index       = HM_CurrentMenu.itemCount - 1;
	this.isLastItem  = (HM_CurrentMenu.itemCount == HM_CurrentMenu.maxItems);
	this.array		 = HM_CurrentMenu.array[HM_CurrentMenu.itemCount];
	this.dispText    = this.array[0];
	this.linkText    = this.array[1];
	this.permHilite  = HM_f_EvalParameters(this.array[3],false,"boolean");
	this.hasRollover = (!this.permHilite && HM_f_EvalParameters(this.array[2],true,"boolean"));
	this.hasMore	 = HM_f_EvalParameters(this.array[4],false,"boolean") && HM_f_ValidateArray(HM_ArrayIDPrefix + itemidsuffix);
	this.childID	 = this.hasMore ? (HM_MenuIDPrefix + itemidsuffix) : null;
	this.child		 = null;
    this.onmouseover = HM_f_ItemOver;
    this.onmouseout  = HM_f_ItemOut;
	this.setItemStyle = HM_f_SetItemStyle;
	this.itemStr	 = HM_f_GetItemDivStr(itemid,this.dispText,this.hasMore);
	this.showChild   = HM_f_ShowChild;
}

function HM_f_Make4ItemElement(menucount) {
	var ItemIDSuffix = menucount + "_" + HM_CurrentMenu.itemCount;
	var LayerID  = HM_ItemIDPrefix + ItemIDSuffix;
	var ObjectID = LayerID + "Obj";
	eval(ObjectID + " = new Object()");
	ItemElement = eval(ObjectID);
	ItemElement.setItemProperties = HM_f_SetItemProperties;
	ItemElement.setItemProperties(LayerID,ItemIDSuffix);
	return ItemElement;
}

function HM_f_MakeElement(menuid) {
	var MenuObject;
	var LayerID  = menuid;
	var ObjectID = LayerID + "Obj";
	eval(ObjectID + " = new Object()"); 
	MenuObject = eval(ObjectID);
	return MenuObject;
}

function HM_f_MakeMenu(menucount) {
	if(!HM_f_ValidateArray(HM_ArrayIDPrefix + menucount)) return false;
	HM_CurrentArray = eval(HM_ArrayIDPrefix + menucount);
	NewMenu = document.all(HM_MenuIDPrefix + menucount);
	if(!NewMenu) {
		NewMenu = HM_f_MakeElement(HM_MenuIDPrefix + menucount);
		NewMenu.array = HM_CurrentArray;
		NewMenu.tree  = HM_CurrentTree;

		if(HM_CurrentMenu) {
			NewMenu.parentMenu = HM_CurrentMenu;
			NewMenu.parentItem = HM_CurrentMenu.itemElement;
			NewMenu.parentItem.child = NewMenu;
			NewMenu.hasParent = true;
			NewMenu.isHorizontal = HM_CurrentTree.TreeIsHorizontal;
			NewMenu.showImage = HM_CurrentTree.TreeImageShow;
		}
		else {
			NewMenu.isHorizontal = HM_CurrentTree.TopIsHorizontal;
			NewMenu.showImage = HM_CurrentTree.TopImageShow;
		}
	
		HM_CurrentMenu = NewMenu;
		HM_CurrentMenu.itemCount = 0;
		HM_CurrentMenu.maxItems = HM_CurrentMenu.array.length - 1;
		HM_CurrentMenu.showIt = HM_f_ShowIt;
		HM_CurrentMenu.keepInWindow = HM_f_KeepInWindow;
	    HM_CurrentMenu.onmouseover = HM_f_MenuOver;
	    HM_CurrentMenu.onmouseout = HM_f_MenuOut;
	    HM_CurrentMenu.hideTree = HM_f_HideTree
	    HM_CurrentMenu.hideParents = HM_f_HideParents;
	    HM_CurrentMenu.hideChildren = HM_f_HideChildren;
	    HM_CurrentMenu.hideTop = HM_f_HideTop;
	    HM_CurrentMenu.hideSelf = HM_f_HideSelf;
		HM_CurrentMenu.count = menucount;
	    HM_CurrentMenu.hasChildVisible = false;
	    HM_CurrentMenu.isOn = false;
	    HM_CurrentMenu.hideTimer = null;
	    HM_CurrentMenu.currentItem = null;
		HM_CurrentMenu.setMenuStyle = HM_f_SetMenuStyle;
		HM_CurrentMenu.sizeFixed = false;
		HM_CurrentMenu.fixSize = HM_f_FixSize;
		HM_CurrentMenu.onselectstart = HM_f_CancelSelect;
    	HM_CurrentMenu.moveTo = HM_f_MoveTo;
		HM_CurrentMenu.htmlString = "<DIV ID='" + HM_MenuIDPrefix + menucount +"' STYLE='position:absolute;visibility:hidden;width:"+ HM_CurrentTree.MenuWidth +"'>";
	}

	while (HM_CurrentMenu.itemCount < HM_CurrentMenu.maxItems) {
		HM_CurrentMenu.itemCount++;

		HM_CurrentItem = document.all(HM_ItemIDPrefix + menucount + "_" + HM_CurrentMenu.itemCount);
		if(!HM_CurrentItem) {
			if(HM_StatusDisplayBuild) status = "Creating Hierarchical Menus: " + menucount + " / " + HM_CurrentMenu.itemCount;
			HM_CurrentMenu.itemElement = HM_f_Make4ItemElement(menucount);
			HM_CurrentMenu.htmlString += HM_CurrentMenu.itemElement.itemStr;
		}
		if(HM_CurrentMenu.itemElement.hasMore && (!HM_CreateTopOnly || HM_AreCreated && HM_CreateTopOnly)) {
	        MenuCreated = HM_f_MakeMenu(menucount + "_" + HM_CurrentMenu.itemCount);
            if(MenuCreated) {
				HM_CurrentMenu = HM_CurrentMenu.parentMenu;
			}
        }
    }

	document.body.insertAdjacentHTML("BeforeEnd",HM_CurrentMenu.htmlString + "</DIV>");
	menuLyr = document.all(HM_MenuIDPrefix + menucount);
	menuLyr.propertyTransfer = propertyTransfer;
	menuLyr.propertyTransfer();
	HM_CurrentMenu = menuLyr;
	if(!HM_CurrentMenu.hasParent)HM_CurrentTree.treeParent = HM_CurrentTree.startChild = HM_CurrentMenu;
	HM_CurrentMenu.setMenuStyle();
    HM_CurrentMenu.childNodes = HM_CurrentMenu.children;
	HM_CurrentMenu.lastItem = HM_CurrentMenu.childNodes[HM_CurrentMenu.childNodes.length-1];
    for(var i=0; i<HM_CurrentMenu.childNodes.length; i++) {
        it = HM_CurrentMenu.childNodes[i];
		it.siblingBelow = i>0 ? HM_CurrentMenu.childNodes[i-1] : null;
		it.propertyTransfer = propertyTransfer;
		it.propertyTransfer();
		it.itemsetup(i+1);
	}
	HM_CurrentMenu.moveTo(0,0);
	return HM_CurrentMenu;
}

function HM_f_SetMenuStyle(){
	with(this.style) {
		borderWidth = HM_BorderWidth + "px";
		borderColor = HM_CurrentTree.BorderColor;
		borderStyle = HM_BorderStyle;
		overflow    = "hidden";
		cursor      = "default";
	}
}

function HM_f_SetItemStyle() {
	with(this.style){
		backgroundColor = (this.permHilite) ? HM_CurrentTree.BGColorOver : HM_CurrentTree.BGColor;
		color		= (this.permHilite) ? HM_CurrentTree.FontColorOver : HM_CurrentTree.FontColor;
		font		= ((HM_FontBold) ? "bold " : "normal ") + HM_FontSize + "pt " + HM_FontFamily;
		padding		= HM_ItemPadding +"px";
		fontStyle	= (HM_FontItalic) ? "italic" : "normal";
		overflow	= "hidden";
		pixelWidth	= HM_CurrentTree.ItemWidth;

		if(HM_CurrentMenu.showImage)	{
			var FullPadding  = (HM_ItemPadding*2) + HM_ImageSize + HM_ImageHorizSpace;
			if (this.tree.RightToLeft) paddingLeft = FullPadding + "px";
			else paddingRight = FullPadding + "px";
		}
		if(!this.isLastItem) {
			var SeparatorString = HM_SeparatorSize + "px solid " + this.tree.SeparatorColor;
			if (this.menu.isHorizontal) borderRight = SeparatorString;
			else borderBottom = SeparatorString;
		}

		if(this.menu.isHorizontal){
			if(this.isLastItem) pixelWidth = (HM_CurrentTree.MenuWidth - HM_BorderWidth - HM_SeparatorSize);
			else pixelWidth = (HM_CurrentTree.MenuWidth - HM_BorderWidth);
			pixelTop = 0;
			pixelLeft = (this.index * (HM_CurrentTree.MenuWidth - HM_BorderWidth));
			var LeftAndWidth = pixelLeft + pixelWidth;
			this.menu.style.pixelWidth = LeftAndWidth + (HM_BorderWidth * 2);
		}
		else {
			pixelLeft = 0;
		}
	}
}

function HM_f_FixSize(makevis){
	if(this.isHorizontal) {
		var MaxItemHeight = 0;
	    for(i=0; i<this.childNodes.length; i++) {
	        var TempItem = this.childNodes[i];
		    if (TempItem.index) {
				var SiblingHeight = TempItem.siblingBelow.scrollHeight;
				MaxItemHeight = Math.max(MaxItemHeight,SiblingHeight);
			}
	       	else{
				MaxItemHeight = TempItem.scrollHeight;
			}
		}
	    for(i=0; i<this.childNodes.length; i++) {
			this.childNodes[i].style.pixelHeight = MaxItemHeight;
		}
		this.style.pixelHeight = MaxItemHeight+(HM_BorderWidth * 2);
	}
	else {
	    for(i=0; i<this.childNodes.length; i++) {
	        var TempItem = this.childNodes[i];
		    if (TempItem.index) {
				var SiblingHeight =(TempItem.siblingBelow.scrollHeight + HM_SeparatorSize);
				TempItem.style.pixelTop = TempItem.siblingBelow.style.pixelTop + SiblingHeight;
			}
			else TempItem.style.pixelTop = 0;
		}
		this.style.pixelHeight = TempItem.style.pixelTop + TempItem.scrollHeight + (HM_BorderWidth * 2);
	}
	this.sizeFixed = true;
	if(makevis)this.style.visibility = "visible";
}

function HM_f_ItemSetup(whichItem) {
    this.menu = this.parentElement;
	this.ClickStart = this.hasMore && this.tree.ClickStart && (this.tree.TopIsPermanent && (this.tree.treeParent==this.menu));
	if(this.ClickStart) {
		this.linkText = "";
		this.onclick = this.showChild;
	}

    if (this.hasMore) {
		if(this.menu.showImage){
			this.imgLyr = this.children("HM_ImMore");
			this.hasImageRollover = ((!this.tree.RightToLeft && HM_ImageSrcOver) || (this.tree.RightToLeft && HM_ImageSrcLeftOver));
			if(this.hasImageRollover) {
				this.imageSrc = this.tree.RightToLeft ? HM_ImageSrcLeft : HM_ImageSrc;
				this.imageSrcOver = this.tree.RightToLeft ? HM_ImageSrcLeftOver : HM_ImageSrcOver;
				if(this.permHilite) this.imgLyr.src = this.imageSrcOver;
			}
		}

        this.child = document.all(this.childID);
        if(this.child) {
			this.child.parentMenu = this.menu;
        	this.child.parentItem = this;
		}
    }
	if(this.linkText && !this.ClickStart) {
		this.onclick = HM_f_LinkIt;
		if(HM_ShowLinkCursor)this.style.cursor = "hand";
	}

	this.setItemStyle();
}

function HM_f_PopUp(menuname){
    if (!HM_AreLoaded) return;
	menuname = menuname.replace("elMenu",HM_MenuIDPrefix);
	var TempMenu = document.all(menuname);
	if(!TempMenu) return;
	HM_CurrentMenu = TempMenu;
	if (HM_CurrentMenu.tree.ClickStart) {
		var ClickElement = event.srcElement;
		ClickElement.onclick = HM_f_PopMenu;
    }
	else HM_f_PopMenu();
}

function HM_f_PopMenu(){
    if (!HM_AreLoaded || !HM_AreCreated) return true;
    if (HM_CurrentMenu.tree.ClickStart && event.type != "click") return true;
	var mouse_x_position, mouse_y_position;
    HM_f_HideAll();
    HM_CurrentMenu.hasParent = false;
	HM_CurrentMenu.tree.startChild = HM_CurrentMenu;
	HM_CurrentMenu.mouseX = mouse_x_position = (event.clientX + document.body.scrollLeft);
	HM_CurrentMenu.mouseY = mouse_y_position = (event.clientY + document.body.scrollTop);
	HM_CurrentMenu.xIntended = HM_CurrentMenu.xPos = (HM_CurrentMenu.tree.MenuLeft!=null) ? eval(HM_CurrentMenu.tree.MenuLeft) : mouse_x_position;
	HM_CurrentMenu.yIntended = HM_CurrentMenu.yPos = (HM_CurrentMenu.tree.MenuTop!=null)  ? eval(HM_CurrentMenu.tree.MenuTop)  : mouse_y_position;
	if(!HM_CurrentMenu.sizeFixed) HM_CurrentMenu.fixSize(false);
    HM_CurrentMenu.keepInWindow();
    HM_CurrentMenu.moveTo(HM_CurrentMenu.xPos,HM_CurrentMenu.yPos);
    HM_CurrentMenu.isOn = true;
    HM_CurrentMenu.showIt(true);
    return false;
}

function HM_f_MenuOver() {
	if(!this.tree.startChild){this.tree.startChild = this}
	if(this.tree.startChild == this) HM_f_HideAll(this)
    this.isOn = true;
    HM_UserOverMenu = true;
    HM_CurrentMenu = this;
    if (this.hideTimer) clearTimeout(this.hideTimer);
}

function HM_f_MenuOut() {
	if(event.srcElement.contains(event.toElement)) return;
    this.isOn = false;
    HM_UserOverMenu = false;
    if(HM_StatusDisplayLink) status = "";
	if(!HM_ClickKill) {
		clearTimeout(HM_HideAllTimer);
		HM_HideAllTimer = null;
		HM_HideAllTimer = setTimeout("HM_CurrentMenu.hideTree()",HM_ChildMilliSecondsVisible);
	}
}

function HM_f_ShowChild(){
	if(!this.child) {
		HM_CurrentTree = this.tree;
		HM_CurrentMenu = this.menu;
		HM_CurrentItem = this;
		this.child = HM_f_MakeMenu(this.menu.count + "_"+(this.index+1));
		this.tree.treeParent = this.menu;
		this.tree.startChild = this.menu;
       	this.child.parentItem = this;
	}
	if (this.tree.PositionUnder && (this.menu == this.tree.treeParent)) {
			this.child.xPos = this.menu.style.pixelLeft + this.style.pixelLeft;
			this.child.yPos = this.menu.style.pixelTop + this.menu.offsetHeight - (HM_BorderWidth);
	}
	else {
		this.oL = this.menu.style.pixelLeft + this.offsetLeft;
		this.oT = this.menu.style.pixelTop  + this.offsetTop;
		if(this.tree.RightToLeft) {
			this.child.xPos = this.oL + (this.tree.HorizOffsetRight - this.child.offsetWidth);
		}
		else {		
			this.child.xPos = this.oL + this.tree.HorizOffsetLeft;
		}
		this.child.yPos = this.oT + HM_ChildOffset + HM_BorderWidth;
	}
	this.child.xDiff = this.child.xPos - this.menu.style.pixelLeft;
	this.child.yDiff = this.child.yPos - this.menu.style.pixelTop;
	if(!this.child.sizeFixed) this.child.fixSize(false);
	if(!this.tree.PositionUnder || this.menu!=this.tree.treeParent) this.child.keepInWindow();
	this.child.moveTo(this.child.xPos,this.child.yPos);
	this.menu.hasChildVisible = true;
	this.menu.visibleChild = this.child;
	this.child.showIt(true);
}

function HM_f_ItemOver(){
    if (HM_KeepHilite) {
        if (this.menu.currentItem && this.menu.currentItem != this && this.menu.currentItem.hasRollover) {
            with(this.menu.currentItem.style){
				backgroundColor = this.tree.BGColor;
            	color = this.tree.FontColor
			}
			if(this.menu.currentItem.hasImageRollover)this.menu.currentItem.imgLyr.src = this.menu.currentItem.imageSrc;
		}
	}
	if(event.srcElement.id == "HM_ImMore") return;
	if(this.hasRollover) {
		this.style.backgroundColor = this.tree.BGColorOver;
		this.style.color = this.tree.FontColorOver;
		if(this.hasImageRollover)this.imgLyr.src = this.imageSrcOver;
	}
    if(HM_StatusDisplayLink) status = this.linkText;
    this.menu.currentItem = this;
	if (this.menu.hasChildVisible) {
		if(this.menu.visibleChild == this.child && this.menu.visibleChild.hasChildVisible) this.menu.visibleChild.hideChildren(this);
		else this.menu.hideChildren(this);
    }
    if (this.hasMore && !this.ClickStart) this.showChild();
}

function HM_f_ItemOut() {
	if (event.srcElement.contains(event.toElement)
	  || (event.fromElement.tagName=="IMG" && (event.toElement && event.toElement.contains(event.fromElement))))
		  return;
    if ( (!HM_KeepHilite || ((this.tree.TopIsPermanent && (this.tree.treeParent==this)) && !this.menu.hasChildVisible)) && this.hasRollover) {
		with(this.style) {
			backgroundColor = this.tree.BGColor;
			color = this.tree.FontColor
		}
		if(this.hasImageRollover)this.imgLyr.src = this.imageSrc;

	}
}

function HM_f_MoveTo(xPos,yPos) {
	this.style.pixelLeft = xPos;
	this.style.pixelTop = yPos;
}

function HM_f_ShowIt(on) {
	if (!(this.tree.TopIsPermanent && (this.tree.treeParent==this))) {
		if(!this.hasParent || (this.hasParent && this.tree.TopIsPermanent && (this.tree.treeParent==this.parentMenu))) {
			var IsVisible = (this.style.visibility == "visible");
			if ((on && !IsVisible) || (!on && IsVisible))
				eval(on ? this.tree.UponDisplay : this.tree.UponHide)
		}
		if(on) this.style.zIndex = ++HM_ZIndex;
		this.style.visibility = (on) ? "visible" : "hidden";
	}
    if (HM_KeepHilite && this.currentItem && this.currentItem.hasRollover) {
		with(this.currentItem.style){
			backgroundColor = this.tree.BGColor;
			color = this.tree.FontColor;
		}
		if(this.currentItem.hasImageRollover)this.currentItem.imgLyr.src = this.currentItem.imageSrc;
	}
	this.currentItem = null;
}



function HM_f_KeepInWindow() {
    var ExtraSpace     = 10;
	var WindowLeftEdge = document.body.scrollLeft;
	var WindowTopEdge  = document.body.scrollTop;
	var WindowWidth    = document.body.clientWidth;
	var WindowHeight   = document.body.clientHeight;
	var WindowRightEdge  = (WindowLeftEdge + WindowWidth) - ExtraSpace;
	var WindowBottomEdge = (WindowTopEdge + WindowHeight) - ExtraSpace;

	var MenuLeftEdge = this.xPos;
	var MenuRightEdge = MenuLeftEdge + this.style.pixelWidth;
	var MenuBottomEdge = this.yPos + this.style.pixelHeight;

	if (this.hasParent) {
		var ParentLeftEdge = this.parentItem.oL;
	}
	if (MenuRightEdge > WindowRightEdge) {
		if (this.hasParent) {
			this.xPos = ParentLeftEdge + this.tree.HorizOffsetRight - this.offsetWidth;	
		}
		else {
			dif = MenuRightEdge - WindowRightEdge;
			this.xPos -= dif;
		}
		this.xPos = Math.max(5,this.xPos);
	}

	if (MenuBottomEdge > WindowBottomEdge) {
		dif = MenuBottomEdge - WindowBottomEdge;
		this.yPos -= dif;
	}

	if (MenuLeftEdge < WindowLeftEdge) {
		if (this.hasParent) {
			this.xPos = ParentLeftEdge + this.tree.HorizOffsetLeft;
			MenuRightEdge = this.xPos + this.style.pixelWidth;
			if(MenuRightEdge > WindowRightEdge) this.xPos -= (MenuRightEdge - WindowRightEdge);
		}
		else {this.xPos = 5}
	}
}

function HM_f_LinkIt() {
    if (this.linkText.indexOf("javascript:")!=-1) eval(this.linkText)
    else {
		HM_f_HideAll();
		location.href = this.linkText;
	}
}

function HM_f_PopDown(menuname){
    if (!HM_AreLoaded || !HM_AreCreated) return;
	menuname = menuname.replace("elMenu",HM_MenuIDPrefix);
    var MenuToHide = document.all(menuname);
	if(!MenuToHide)return;
    MenuToHide.isOn = false;
    if (!HM_ClickKill) MenuToHide.hideTop();
}

function HM_f_HideAll(callingmenu) {
	for(var i=0; i<HM_TotalTrees; i++) {
        var TopMenu = HM_a_TopMenus[i].tree.startChild;
		if(TopMenu == callingmenu)continue
        TopMenu.isOn = false;
        if (TopMenu.hasChildVisible) TopMenu.hideChildren();
        TopMenu.showIt(false);
    }    
}

function HM_f_HideTree() { 
    HM_HideAllTimer = null;
    if (HM_UserOverMenu) return;
    if (this.hasChildVisible) this.hideChildren();
    this.hideParents();
}

function HM_f_HideTop() {
	TopMenuToHide = this;
    (HM_ClickKill) ? TopMenuToHide.hideSelf() : (this.hideTimer = setTimeout("TopMenuToHide.hideSelf()",HM_TopMilliSecondsVisible));
}

function HM_f_HideSelf() {
    this.hideTimer = null;
    if (!this.isOn && !HM_UserOverMenu) this.showIt(false);
}

function HM_f_HideParents() {
    var TempMenu = this;
    while(TempMenu.hasParent) {
        TempMenu.showIt(false);
        TempMenu.parentMenu.isOn = false;        
        TempMenu = TempMenu.parentMenu;
    }
    TempMenu.hideTop();
}

function HM_f_HideChildren(callingitem) {
    var TempMenu = this.visibleChild;
    while(TempMenu.hasChildVisible) {
        TempMenu.visibleChild.showIt(false);
        TempMenu.hasChildVisible = false;
        TempMenu = TempMenu.visibleChild;
    }
	if((callingitem && (!callingitem.hasMore || this.visibleChild != callingitem.child)) || (!callingitem && !this.isOn)) {
		this.visibleChild.showIt(false);
		this.hasChildVisible = false;
	}
}

function HM_f_CancelSelect(){return false}

function HM_f_PageClick() {
    if (!HM_UserOverMenu && HM_CurrentMenu!=null && !HM_CurrentMenu.isOn) HM_f_HideAll();
}

popUp = HM_f_PopUp;
popDown = HM_f_PopDown;

function HM_f_ResizeHandler(){
	var mouse_x_position, mouse_y_position;
	for(var i=0; i<HM_TotalTrees; i++) {
        var TopMenu = HM_a_TopMenus[i].tree.startChild;
		if(TopMenu.style.visibility == "visible") {
			TopMenu.oldLeft = TopMenu.xPos;
			TopMenu.oldTop = TopMenu.yPos;
			mouse_x_position = TopMenu.mouseX;
			mouse_y_position = TopMenu.mouseY;
			TopMenu.xPos = eval(TopMenu.tree.MenuLeft);
			TopMenu.yPos = eval(TopMenu.tree.MenuTop);
			if(TopMenu.xPos == null) TopMenu.xPos = TopMenu.xIntended;
			if(TopMenu.yPos == null) TopMenu.yPos = TopMenu.yIntended;
			if(!TopMenu.tree.TopIsPermanent) TopMenu.keepInWindow();
			TopMenu.moveTo(TopMenu.xPos,TopMenu.yPos);
			var TempMenu = TopMenu;
		    while(TempMenu.hasChildVisible) {
				TempParent = TempMenu;
				TempMenu = TempMenu.visibleChild;
				TempMenu.xPos = TempParent.xPos + TempMenu.xDiff;
				TempMenu.yPos = TempParent.yPos + TempMenu.yDiff;
				if(!TopMenu.tree.TopIsPermanent || (TopMenu.tree.TopIsPermanent && !TopMenu.tree.PositionUnder)) TempMenu.keepInWindow();
				TempMenu.moveTo(TempMenu.xPos,TempMenu.yPos);
		    }
		}
    }
	HM_f_OtherResize();
}

HM_f_OtherResize = (window.onresize) ? window.onresize :  new Function;
window.onresize = HM_f_ResizeHandler;

HM_f_OtherOnLoad = (window.onload) ? window.onload :  new Function;
window.onload = function(){setTimeout("HM_f_StartIt()",10)};

//end