/*---------------------------------------------------------------------

Copyright 2013 Muze
Written by Yvo Brevoort

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

--------------------------------------------------------------------*/

var selectable = function() {
	var helperDiv;
	var selecting = false;
	var containerDiv;
	var filterClass = "selectable";
	
	var selectPositions = {
		x1 : 0,
		x2 : 0,
		y1 : 0,
		y2 : 0
	}
	var handleMouseDown = function(event) {
		// console.log("mouse down");
		selecting = true;
		containerDiv = this;

		if (!event.ctrlKey) {
			clearSelection();
		}
		
		selectPositions.x1 = event.pageX ? event.pageX : event.clientX;
		selectPositions.y1 = event.pageY ? event.pageY : event.clientY;
		selectPositions.x2 = selectPositions.x1 + 1;
		selectPositions.y2 = selectPositions.y1 + 1;
		
		updateHelper();

		updateSelection();

		document.body.onselectstart = function() {return false;} // FIXME: should do this with event attach and preventDefault
		containerDiv.style.MozUserSelect = "none";
		document.body.ondragstart = function() {return false;}
	}
	
	var handleMouseMove = function(event) {
		if (!selecting) {
			return true;
		}

		// console.log("mouse move");
			
		selectPositions.x2 = event.pageX ? event.pageX : event.clientX;
		selectPositions.y2 = event.pageY ? event.pageY : event.clientY;
			
		updateHelper();
		updateSelection();
	}
	
	var checkClass = function(elm) {
		if (typeof(filterClass) == "string") {
			if (elm.className && elm.className.match(new RegExp("\\b" + filterClass + "\\b"))) {
				return true;
			}
		}
		
		if (typeof(filterClass) == "object") {
			if (elm.className) {
				for(var i in filterClass) {
					if (elm.className.match(new RegExp("\\b" + filterClass[i] + "\\b"))) {
						return true;
					}
				}
			}
		}
		return false;
	}

	var updateSelection = function() {
		if (!containerDiv) {
			return;
		}
		var divs = containerDiv.getElementsByTagName("*");

		for (var i=0; i<divs.length; i++) {
			if (checkClass(divs[i])) {
				// console.log("found selectable");						
				if (
					(divs[i].getBoundingClientRect().right > ((selectPositions.x1 < selectPositions.x2) ? selectPositions.x1 : selectPositions.x2)) &&
					(divs[i].getBoundingClientRect().bottom > ((selectPositions.y1 < selectPositions.y2) ? selectPositions.y1 : selectPositions.y2)) &&
					(divs[i].getBoundingClientRect().left < ((selectPositions.x1 > selectPositions.x2) ? selectPositions.x1 : selectPositions.x2)) && 
					(divs[i].getBoundingClientRect().top < ((selectPositions.y1 > selectPositions.y2) ? selectPositions.y1 : selectPositions.y2))
				) {
					// Select
					if (!(divs[i].className.match(/\bselecting\b/))) {
						divs[i].className = divs[i].className + " selecting";
					}
				} else {
					// Unselect
					if (divs[i].className.match(/\bselecting\b/)) {
						divs[i].className = divs[i].className.replace(/\bselecting\b/, '');
					}
				}
				
			}
		}
	}
	
	var clearSelection = function() {
		if (!containerDiv) {
			return false;
		}
		var divs = containerDiv.getElementsByTagName("*");
		for (var i=0; i<divs.length; i++) {
			if (checkClass(divs[i])) {
				if (divs[i].className.match(/\bselectable-selected\b/)) {
					divs[i].className = divs[i].className.replace(/\bselectable-selected\b/, '');
				}
			}
		}
		fireEvent("clearselection", containerDiv);
	}
	
	var handleMouseUp = function(event) {
		// console.log("mouse up");
		if (!containerDiv) {
			return true;
		}
		var divs = containerDiv.getElementsByTagName("*");
		for (var i=0; i<divs.length; i++) {
			if (checkClass(divs[i])) {
				if (divs[i].className.match(/\bselecting\b/)) {
					divs[i].className = divs[i].className.replace(/\bselecting\b/, '');
					if (divs[i].className.match(/\bselectable-selected\b/)) {
						divs[i].className = divs[i].className.replace(/\bselectable-selected\b/, '');
					} else {
						divs[i].className = divs[i].className + " selectable-selected";
					}
				}
			}
		}
		
		selecting = false;
		updateHelper();

		fireEvent("selected", containerDiv);
		containerDiv = false;
		return true;
	}
	
	var updateHelper = function() {
		// console.log("Update helper");
		if (!selecting) {
			helperDiv.style.display = "none";
			return;
		}

		helperDiv.style.display = "block";
		
		var newleft = (selectPositions.x1 > selectPositions.x2) ? selectPositions.x2 : selectPositions.x1;
		var newwidth = Math.abs(selectPositions.x1 - selectPositions.x2);
		var newtop = (selectPositions.y1 > selectPositions.y2) ? selectPositions.y2 : selectPositions.y1;
		var newheight = Math.abs(selectPositions.y1 - selectPositions.y2);
		
		offsetParent = helperDiv.offsetParent;
		while(offsetParent) {
			newleft = offsetParent.scrollLeft + newleft;
			newtop = offsetParent.scrollTop + newtop;
			offsetParent = offsetParent.offsetParent;
		}

		// Reduce size by 1 pixel to allow normal events on target to fire.
		helperDiv.style.left = newleft + 1 + "px";
		helperDiv.style.width = newwidth - 1 + "px";
		
		helperDiv.style.top = newtop + 1 + "px";
		helperDiv.style.height = newheight - 1 + "px";
		
		// console.log("L:" + newleft +"; "+"W:" + newwidth +"; "+"T:" + newtop +"; "+"H:" + newheight +"; "+"D:" + helperDiv.style.display +";");
	}

	var fireEvent = function(eventName, eventTarget) {
		var event;
		if (document.createEvent) {
			event = document.createEvent("HTMLEvents");
			event.initEvent(eventName, true, true);
		} else {
			event = document.createEventObject();
			event.eventType = eventName;
		}

		event.eventName = eventName;

		if (document.createEvent) {
			eventTarget.dispatchEvent(event);
		} else {
			eventTarget.fireEvent("on" + event.eventType, event);
		}
	}

	return {
		init : function(container, options) {
			if (typeof(container) == "string") {
				container = document.getElementById(container);
			}

			if (window.addEventListener) {
				container.addEventListener("mousedown", handleMouseDown, false);
			} else if (window.attachEvent) {
				container.attachEvent("mousedown", handleMouseDown);
			}

			if (window.addEventListener) {
				document.body.addEventListener("mousemove", handleMouseMove, false);
				document.body.addEventListener("mouseup", handleMouseUp, false);
			} else if (window.attachEvent) {
				document.body.attachEvent("mousemove", handleMouseMove);
				document.body.attachEvent("mouseup", handleMouseUp);
			}
			
			if (options && options.filterClass) {
				filterClass = options.filterClass;
			}

			// Add helpder div to body;
			helperDiv = document.createElement("DIV");
			helperDiv.className = "selectableHelper";
			document.body.appendChild(helperDiv);
		}
	}
}();
