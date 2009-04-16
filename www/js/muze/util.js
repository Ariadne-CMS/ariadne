muze.namespace('muze.util');

muze.util = function() {

	return {
		isString : function(str) {
			if (typeof str == 'string') {
				return true;
			}
			if (typeof str == 'object') {
				var criterion = str.constructor.toString().match(/string/i); 
				return (criterion != null);  
			}
			return false;
		},
		isArray : function(arr) {
			if (typeof arr == 'array') {
				return true;
			}
			if (typeof arr == 'object') {
				var criterion = arr.constructor.toString().match(/array/i); 
				return (criterion != null);  
			}
			return false;
		},
		setOpacity : function(object, opacity) {
			if (opacity >= 1) {
				opacity = 1;
			}
			if (opacity < 0) {
				opacity = 0;
			}

			object.style.filter = "alpha(opacity=" + (opacity*100) + ")";
			object.style.MozOpacity = opacity;
			object.style.KHTMLOpacity = opacity;
			object.style.opacity = opacity;
		},
		getSize : function(object) {
			return { 
				height : parseInt(object.offsetHeight),
				width : parseInt(object.offsetWidth)
			}
		},
		getScrollSize : function() {
			var xScroll, yScroll;
	
			if (window.innerHeight && window.scrollMaxY) {	
				xScroll = document.body.scrollWidth;
				yScroll = window.innerHeight + window.scrollMaxY;
			} else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
				xScroll = document.body.scrollWidth;
				yScroll = document.body.scrollHeight;
			} else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
				xScroll = document.body.offsetWidth;
				yScroll = document.body.offsetHeight;
			}
			return {
				width : xScroll,
				height : yScroll
			}
		},
		getScrollOffset : function(object) {
			if (!object) {
				var iebody = (document.compatMode && document.compatMode!='BackCompat') ? document.documentElement : document.body;
				return {
					x : document.all? iebody.scrollLeft : window.pageXOffset,
					y : document.all? iebody.scrollTop: window.pageYOffset
				}
			} else {
				return {
					x : object.scrollLeft,
					y : object.scrollTop
				}
			}
		},
		getPageSize : function() {
			var scrollSize = muze.util.getScrollSize();
			var windowSize = muze.util.getWindowSize();
			
			// for small pages with total height less then height of the viewport
			if(scrollSize.height < windowSize.height){
				pageHeight = windowSize.height;
			} else { 
				pageHeight = scrollSize.height;
			}

			// for small pages with total width less then width of the viewport
			if(scrollSize.width < windowSize.width){	
				pageWidth = windowSize.width;
			} else {
				pageWidth = scrollSize.width;
			}

			return {
				width : pageWidth,
				height : pageHeight
			}
		},
		getWindowSize : function() {
			var windowWidth, windowHeight;
			if (self.innerHeight) {	// all except Explorer
				windowWidth = self.innerWidth;
				windowHeight = self.innerHeight;
			} else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
				windowWidth = document.documentElement.clientWidth;
				windowHeight = document.documentElement.clientHeight;
			} else if (document.body) { // other Explorers
				windowWidth = document.body.clientWidth;
				windowHeight = document.body.clientHeight;
			}
			return {
				width : windowWidth,
				height : windowHeight
			}
		}
	}
}();
