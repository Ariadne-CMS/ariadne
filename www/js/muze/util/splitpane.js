muze.namespace("muze.util.splitpane", function() {
	return { 
		getHorizSplitPane : function(sBGElId, sHandleEId, iLeft, iRight, leftContainer, rightContainer) {
			var slider = YAHOO.widget.Slider.getHorizSlider(sBGElId, sHandleEId, iLeft, iRight);
			var leftContainer = document.getElementById(leftContainer);
			var rightContainer = document.getElementById(rightContainer);
			var oriLeftWidth = parseInt(YAHOO.util.Dom.getStyle(leftContainer, "width"));
			var oriRightWidth = parseInt(YAHOO.util.Dom.getStyle(rightContainer, "width"));

			var oriRightLeft = parseInt(YAHOO.util.Dom.getStyle(rightContainer, "left"));

			var handleChange = function(x) {
				var x = this.getXValue();
					
				var leftWidth = oriLeftWidth + x;
				YAHOO.util.Dom.setStyle(leftContainer, "width", leftWidth + "px");
				YAHOO.util.Dom.setStyle(document.getElementById("treeDiv"), "width", leftWidth-24 + "px");
				
				//var rightWidth = oriRightWidth + (x * -1);
				//YAHOO.util.Dom.setStyle(rightContainer, "width", rightWidth + "px");

				var rightLeft = oriRightLeft + x;
				YAHOO.util.Dom.setStyle(rightContainer, "left", rightLeft + "px");
			} 
			slider.subscribe("change", handleChange);
			return slider;
		},
		getVertSplitPane : function(sBGElId, sHandleEId, iUp, iDown, topContainer, bottomContainer) {
			var slider = YAHOO.widget.Slider.getVertSlider(sBGElId, sHandleEId, iUp, iDown);
			var topContainer = document.getElementById(topContainer);
			var bottomContainer = document.getElementById(bottomContainer);
			var oriTopHeight = parseInt(YAHOO.util.Dom.getStyle(topContainer, "height"));
			var oriBottomHeight = parseInt(YAHOO.util.Dom.getStyle(bottomContainer, "height"));
				
			var handleChange = function(offsetFromStart) {
				var y = this.getYValue();
				var topHeight = oriTopHeight + y;

				YAHOO.util.Dom.setStyle(topContainer, "height", topHeight + "px");
				var bottomHeight = oriBottomHeight + (y * -1);

				YAHOO.util.Dom.setStyle(bottomContainer, "height", bottomHeight + "px");
			} 
			slider.subscribe("change", handleChange);
			return slider;
		}
	}
});
