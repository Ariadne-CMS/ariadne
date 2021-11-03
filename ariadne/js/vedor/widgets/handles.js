/*
	Vedor Resize and Drag Library
	Copyright Vedor 2005, All rights reserved

	usage
		<script src="muze.js">
		<script src="muze/event.js">
		<script src="vedor/widgets/handles.js">
		<script>
			function init() {
				var handles=vedor.widgets.init(window, document.body, 'images/');
				handles.attachTags(relResize.getHandles(), 'TD', 'TH', 'TABLE');
				handles.attach(absResize.getHandles(), document.getElementById('absoluteDiv'));
				handles.attach({e:myResizeMethod}, document.getElementById('myElement'));
			}
		</script>

	public methods

		attach(handlesList, element)

			attaches the handles and antlines to the given element.
			handlesList is an object with one or more of the following function pointers:
				nw, n, ne, w, e, sw, s, se, drag
			if set, each of these will make a handle appear in the corresponding corner,
			except for 'drag', which will make the element draggable. 

		attachTags(handlesList, tag1, tag2, ...)

			see above, except for all tags with the given tagNames

	The methods below should not be needed often, except for remove and insert, when
	saving the html source.

		insert()

			inserts the needed handle divs in the source document

		remove()

			removes the handle divs from the source document. Do this before saving the
			source html.

		show()

			show the handles on the current element, if any is selected.

		hide()

			hide the handles

		select(element, handlesList)

			select an element, showing the handles and antlines, using the given handlesList
			to see which handles must be shown and which callback functions to use.
			Only use this if for some reason attach isn't good enough.

		mark(handleId)

			marks the current element for resizing or dragging

		stop(hideFlag)

			stops resizing or dragging the current element, if hideFlag is true, the handles
			are hidden.

		resize(handleId)

			called on mousemove, if the current element is marked for resizing or dragging,
			it will call the corresponding callback function in the handlesList.
*/


muze.namespace("vedor.widgets.handles");

muze.require("muze.event");

vedor.widgets.handles = ( function() {

function handles(vdWindow, vdElement, vdImageDir, vdUpdateFunc) {
	var antlines={		// pointers to antlines divs
		left:false,
		right:false,
		top:false,
		bottom:false
	}
	var handle={		// pointers to handle divs
		nw:false,
		n:false,
		ne:false,
		w:false,
		e:false,
		sw:false,
		s:false,
		se:false,
		drag:false
	}
	var handlesDiv=false;				// pointer to handles container div
	var startPos=false;					// x/y offset of vdElement
	var cellPos=false;					// x/y offset of current element, relative to startPos
	var dragStart=false;				// x/y offset of mousecursor on mousedown
	var hoverDiv=false;					// pointer to hover div, which aids selection
	var hoverHandles=false;				// pointer to handleslist for the current hover div
	var hoverObj=false;
	var resizeObj=false;				// current element
	var resizing=false;					// resize/drag on mousemove boolean flag
	var fnResizeHandler=null;			// resize function pointer for detachEvent
	var tempSelectStartFunction=null;	// temp function pointer to re attach after resizing/dragging

	var attachList=new Array();

	function getOffset(el, elTop, skipScrollbar) {

		function getBorderSize(b) {
			if (isNaN(b)) {
				switch(b) {
					case 'thin' : return 1;
					break;
					case 'thick' : return 4; // guess
					break;
					case 'medium' : return 2;
					break;
					default : result = parseInt(b);
				}
			} else {
				result = parseInt(b);
			}
			if (!result) {
				result = 0;
			}
			return result;
		}

		function isFrameBorderDisabled(el) {
			try {
				var f = el.ownerDocument.parentWindow.frameElement;
				if (f.frameBorder=='no' || f.frameBorder==="0") {
					return true;
				}
			} catch(e) {
			}
			return false;
		}			

		function getScrollOffset(el) {
			try {
				var xScroll = Math.max(el.ownerDocument.documentElement.scrollLeft, el.ownerDocument.body.scrollLeft);
				var yScroll = Math.max(el.ownerDocument.documentElement.scrollTop, el.ownerDocument.body.scrollTop);
			} catch(e) {
				var xScroll = el.ownerDocument.body.scrollLeft;
				var yScroll = el.ownerDocument.body.scrollTop;
			}
			return { x: xScroll, y: yScroll }
		}

		var offset			= { x:0, y:0 }
		var isIE 			= el.ownerDocument.compatMode; //
		var isNonIE			= el.ownerDocument.defaultView && el.ownerDocument.defaultView.getComputedStyle
		var isIE8 			= el.ownerDocument.documentMode;
		var isIE8Standard 	= (el.ownerDocument.documentMode==8);

		if (el && el.getBoundingClientRect) {
			var rect = el.getBoundingClientRect();
			offset.x = rect.left;
			offset.y = rect.top;
			var d = { x: 0, y: 0 }
			var docCheck = el.parentNode;
			while (docCheck && docCheck!=elTop) {
				if (docCheck.tagName=='BODY') {
					break;
				}
				docCheck = docCheck.parentNode;
			}
			if (docCheck && docCheck.tagName=='BODY') { // document body is part of the offset stack, so check scrollOffset and borderBug 
				if (!skipScrollbar) {
					d = getScrollOffset(el);
				}
				if (isIE && !isNonIE && !isIE8Standard && !isFrameBorderDisabled(el)) {
					d.x -= getBorderSize(el.ownerDocument.body.currentStyle.borderLeftWidth);
					d.y -= getBorderSize(el.ownerDocument.body.currentStyle.borderTopWidth);
				}
				if (!d.x) {
					d.x = 0;
				}
				if (!d.y) {
					d.y = 0;
				}
			}
			offset.x += d.x;
			offset.y += d.y;
		} else {
			while (el && el.offsetParent && el!=elTop) {
				if ( !isNaN( el.offsetLeft ) ) {
					offset.x += el.offsetLeft;
				}
				if ( !isNaN( el.offsetTop ) ) {
					offset.y += el.offsetTop;
				}
				el = el.offsetParent;
				if (el) {
					if (el!=elTop) {
						if (!isNaN( el.scrollTop)) {
							offset.y -= el.scrollTop;
						}
						if (!isNaN(el.scrollLeft)) {
							offset.x -= el.scrollLeft;
						}
					}
				}
			}
		}
		return offset;				
	}


	function initDragStart(evt) {
		var click=getClickPos(evt);
		var offset=getOffset(resizeObj, vdWindow.document.body, true);
		// difference between mouse cursor position and topleft corner
		var diffX=(click.x)-(offset.x);
		var diffY=(click.y)-(offset.y);
		dragStart={x:diffX, y:diffY}
	}


	function getClickPos(evt) {
		evt = getLocalEvent(evt);
		return { x:evt.clientX, y:evt.clientY };
	}

	function getLocalEvent(evt) {
		return (evt) ? evt : ((vdWindow.event) ? vdWindow.event : null);
	}

/*
    // define prototype methods only once
	if (typeof(_handles_prototype_called) == 'undefined') {
        _handles_prototype_called = true;
*/

		this.insert=function() {
			var tempHandles=this;

			handlesDiv = vdWindow.document.createElement("DIV");
			handlesDiv.setAttribute("unselectable", "on");
			handlesDiv.id = "vd_handles";
			handlesDiv.style.display = "none";
			handlesDiv.style.zIndex = 10;
			vdWindow.document.body.appendChild(handlesDiv);

			function createAntline(id, image) {
				var temp = vdWindow.document.createElement("DIV");
				temp.setAttribute("unselectable", "on");
				temp.className = "vd_antline";
				temp.id = id;
				temp.innerHTML = '<img style="display: block;" unselectable="on" src="'+vdImageDir+image+'">';
				handlesDiv.appendChild(temp);

				return vdWindow.document.getElementById(id);
			}

			antlines.left=createAntline('vd_antline_left','vertical.ants.gif');		
			antlines.right=createAntline('vd_antline_right','vertical.ants.gif');
			antlines.top=createAntline('vd_antline_top','horizontal.ants.gif');
			antlines.bottom=createAntline('vd_antline_bottom','horizontal.ants.gif');

			function createHandle(id, title, cursor) {
				var temp = vdWindow.document.createElement("DIV");
				temp.id = id;
				temp.setAttribute("unselectable", "on");
				temp.setAttribute("title", title);
				temp.style.top = "10px";
				temp.style.left = "10px";
				temp.style.width = "7px";
				temp.style.height = "7px";
				temp.style.overflow = "hidden";
				temp.style.backgroundColor = "white";
				temp.style.margin = "0px";
				temp.style.padding = "0px";
				temp.style.border = "1px solid black";
				temp.style.position = "absolute";
				temp.style.cursor = cursor;
				temp.style.zIndex = 1001;

				handlesDiv.appendChild(temp);
				var handle=vdWindow.document.getElementById(id);
				muze.event.attach(handle, 'mousedown', function(evt) { 
					evt = muze.event.get(evt);
					tempHandles.mark(handle.id, evt); 
					return muze.event.cancel(evt);
				} );
				return handle;
			}

			handle.nw=createHandle('vd_handle_topleft', '', 'se-resize');
			handle.n=createHandle('vd_handle_top', '', 's-resize');
			handle.ne=createHandle('vd_handle_topright', '', 'sw-resize');
			handle.w=createHandle('vd_handle_left', '', 'e-resize');
			handle.e=createHandle('vd_handle_right', '', 'w-resize');
			handle.sw=createHandle('vd_handle_bottomleft', '', 'ne-resize');
			handle.s=createHandle('vd_handle_bottom', '', 'n-resize');
			handle.se=createHandle('vd_handle_bottomright', '', 'nw-resize');

			handle.drag = vdWindow.document.createElement("DIV");
			handle.drag.setAttribute("unselectable", "on");
			handle.drag.id = "vd_handle_drag";
			handle.drag.style.position = "absolute";
			handle.drag.style.display = "none";
			handle.drag.style.cursor = "move";
			handle.drag.style.backgroundImage = "url(/vedor/widgets/vedor/images/transparant.gif)";
			handle.drag.style.zIndex = 3;

			handlesDiv.appendChild(handle.drag);
			muze.event.attach(handle.drag, 'mousedown', function(evt) { evt=muze.event.get(evt); tempHandles.mark(handle.drag.id, evt); return muze.event.cancel(evt); } );

			hoverDiv = vdWindow.document.createElement("DIV");
			hoverDiv.setAttribute("unselectable", "on");
			hoverDiv.id = "vd_hover";
			hoverDiv.style.position = "absolute";
			hoverDiv.style.zIndex = 2;
			hoverDiv.style.backgroundImage = "url(/vedor/widgets/vedor/images/transparant.gif)";

			vdWindow.document.body.appendChild(hoverDiv);
			hoverDiv.onclick=function(evt) { evt=getLocalEvent(evt); tempHandles.selectcurrent(); return vdCancelEvent(evt); }

			muze.event.attach(vdWindow.document.body, 'mouseup', function() { tempHandles.stop(false) } );
			muze.event.attach(vdWindow.document.body, 'mousedown', function(evt) { 
				if (resizeObj) {
					evt = muze.event.get(evt);
					// resizeObj moet parent zijn van evt.srcElement
					var el = muze.event.target(evt);
					while (el && el!=vdWindow.document.body) {
						if (el==resizeObj) {
							return;
						}
						if (el.id=='vd_handles' || el.id=='vd_hover') {
							return;
						}
						el = el.parentNode;
					}
					tempHandles.hide(); 
				}
			} );
			muze.event.attach(vdWindow.document.body, 'keydown', function() {
				if (resizeObj) {
					window.setTimeout(function() { tempHandles.show(); }, 10);
				}
			} );
			muze.event.attach(vdWindow.document, 'mousewheel', function() {
				if (resizeObj) {
					window.setTimeout(function() { tempHandles.show(); }, 10);
				}
			} );
		}

		this.hide=function() {
			var temp = resizeObj;
			resizeObj=null;
			handlesDiv.style.display='none';
			if( vdUpdateFunc ) {
				vdUpdateFunc(temp, 'hide');
			}
			temp = null;
			return true;
		}

		this.show=function(action) {
			if (vdElement!=vdWindow.document.body) {
				startPos = getOffset(vdElement, vdWindow.document.body); 
			} else {
				startPos = { x: 0, y:0 };
			}
			if (resizeObj) {
				cellPos = getOffset(resizeObj, vdElement);
				cellPos.w=resizeObj.offsetWidth;
				cellPos.h=resizeObj.offsetHeight;
				if (!cellPos.h || cellPos.h<=0) {
					cellPos.h=1;
				}
				if (!cellPos.w || cellPos.w<=0) {
					cellPos.w=1;
				}

				antlines.top.style.top=(startPos.y+cellPos.y)+'px';
				antlines.top.style.left=(startPos.x+cellPos.x)+'px';
				antlines.top.style.height='1px';
				antlines.top.style.width=cellPos.w+'px';
				antlines.top.style.overflow='hidden';
				antlines.top.style.position='absolute';
				antlines.top.style.zIndex=20;

				antlines.left.style.top=(startPos.y+cellPos.y)+'px';
				antlines.left.style.left=(startPos.x+cellPos.x)+'px';
				antlines.left.style.height=cellPos.h+'px';
				antlines.left.style.width='1px';
				antlines.left.style.overflow='hidden';
				antlines.left.style.position='absolute';
				antlines.left.style.zIndex=20;

				antlines.right.style.top=(startPos.y+cellPos.y)+'px';
				antlines.right.style.left=(startPos.x+cellPos.x+cellPos.w-1)+'px';	
				antlines.right.style.width='1px';
				antlines.right.style.height=cellPos.h+'px';
				antlines.right.style.overflow='hidden';
				antlines.right.style.position='absolute';
				antlines.right.style.zIndex=20;
				
				antlines.bottom.style.top=(startPos.y+cellPos.y+cellPos.h-1)+'px';
				antlines.bottom.style.left=(startPos.x+cellPos.x)+'px';
				antlines.bottom.style.width=cellPos.w+'px';
				antlines.bottom.style.height='1px';
				antlines.bottom.style.overflow='hidden';
				antlines.bottom.style.position='absolute';
				antlines.bottom.style.zIndex=20;

				if (this.handles) {
					if (this.handles.nw) {
						handle.nw.style.top=(startPos.y+cellPos.y-3)+'px';
						handle.nw.style.left=(startPos.x+cellPos.x-3)+'px';
						handle.nw.style.display='block';
					} else {
						handle.nw.style.display='none';
					}
					if (this.handles.n) {
						handle.n.style.top=(startPos.y+cellPos.y-3)+'px';
						handle.n.style.left=(startPos.x+cellPos.x+(Math.round(cellPos.w/2))-3)+'px';
						handle.n.style.display='block';
					} else {
						handle.n.style.display='none';
					}
					if (this.handles.ne) {
						handle.ne.style.top=(startPos.y+cellPos.y-3)+'px';
						handle.ne.style.left=(startPos.x+cellPos.x+cellPos.w-4)+'px';
						handle.ne.style.display='block';
					} else {
						handle.ne.style.display='none';
					}
					if (this.handles.w) {
						handle.w.style.top=(startPos.y+cellPos.y+(Math.round(cellPos.h/2))-3)+'px';
						handle.w.style.left=(startPos.x+cellPos.x-3)+'px';
						handle.w.style.display='block';
					} else {
						handle.w.style.display='none';
					}
					if (this.handles.e) {
						handle.e.style.top=(startPos.y+cellPos.y+(Math.round(cellPos.h/2))-3)+'px';
						handle.e.style.left=(startPos.x+cellPos.x+cellPos.w-4)+'px';
						handle.e.style.display='block';
					} else {
						handle.e.style.display='none';
					}
					if (this.handles.sw) {
						handle.sw.style.top=(startPos.y+cellPos.y+cellPos.h-4)+'px';
						handle.sw.style.left=(startPos.x+cellPos.x-3)+'px';
						handle.sw.style.display='block';
					} else {
						handle.sw.style.display='none';
					}
					if (this.handles.s) {
						handle.s.style.top=(startPos.y+cellPos.y+cellPos.h-4)+'px';
						handle.s.style.left=(startPos.x+cellPos.x+(Math.round(cellPos.w/2))-3)+'px';
						handle.s.style.display='block';
					} else {
						handle.s.style.display='none';
					}
					if (this.handles.se) {
						handle.se.style.top=(startPos.y+cellPos.y+cellPos.h-4)+'px';
						handle.se.style.left=(startPos.x+cellPos.x+cellPos.w-4)+'px';
						handle.se.style.display='block';
					} else {
						handle.se.style.display='none';
					}
					if (this.handles.drag) {
						handle.drag.style.top=(startPos.y+cellPos.y+1)+'px';
						handle.drag.style.left=(startPos.x+cellPos.x+1)+'px';
						handle.drag.style.height=(cellPos.h-1)+'px';
						handle.drag.style.width=(cellPos.w-1)+'px';
						handle.drag.style.display='block';
					} else {
						handle.drag.style.display='none';
						handle.drag.style.height='0px';
						handle.drag.style.width='0px';
					}
				} else {
					for (var i in handle) {
						handle[i].style.display='none';
					}
				}

				handlesDiv.style.display='block';
				// check for callback method
				if( vdUpdateFunc  ) {
					vdUpdateFunc(resizeObj, action);
				} 
			}
			return true;
		}

		this.remove=function() {
			vdWindow.document.body.removeChild(handlesDiv);
			vdWindow.document.body.removeChild(hoverDiv);
		}

		this.mark=function(handleId, evt) {
			resizing=true;
			tempHandles = this;
			if (tempSelectStartFunction==null) {
				// remember old onselectstart handler
				tempSelectStartFunction=vdWindow.document.onselectstart;
			}
			if (handleId=='vd_handle_drag') {
				initDragStart(evt);
			}
			// replace onselectstart handler with an empty one to
			// prevent selections while dragging
			vdWindow.document.onselectstart = function() { return false; }
			if (fnResizeHandler) {
				muze.event.detach(vdWindow.document.body, 'mousemove', this.fnResizeHandlerCancel);
			}
			fnResizeHandler=function(evt) { tempHandles.resize(handleId, evt); }
			this.fnResizeHandlerCancel = muze.event.attach(vdWindow.document.body, 'mousemove', fnResizeHandler);
		}

		this.stop=function(hide) {
			if (resizing) {
				resizing = false;
				// re-attach old onselectstart handler
				vdWindow.document.onselectstart=tempSelectStartFunction;
				muze.event.detach(vdWindow.document.body, 'mousemove', this.fnResizeHandlerCancel);
				fnResizeHandler = null;
				if (hide) {
					this.hide();
				}
				// check for callback method
				if( vdUpdateFunc ) {
					vdUpdateFunc(resizeObj, 'dragstop');
				}
			}
		}

		this.select=function(el, handlesList) {
			// select el, show antlines and optionally handles
			resizeObj=el;
			this.handles=handlesList;
			this.show('select');
			// this.mark('vd_handle_drag');
		}

		this.getElement=function() {
			if (resizeObj) {
				return resizeObj;
			}
		}

		this.resize=function(handleId, evt) {
			if (resizing && resizeObj) {
				var click = getClickPos(evt);
				var offset = getOffset(resizeObj, vdWindow.document.body, true); //ignore scrollbar
				// difference between mouse cursor position and topleft corner
				var diffX=(click.x)-(offset.x);
				var diffY=(click.y)-(offset.y);
				if (this.handles) {
					switch(handleId) {
						case 'vd_handle_topleft':
							if (this.handles.nw) {
								this.handles.nw(resizeObj, diffX, diffY);
							}
							break;
						case 'vd_handle_top':
							if (this.handles.n) {
								this.handles.n(resizeObj, diffX, diffY);
							}
							break;
						case 'vd_handle_topright':
							if (this.handles.ne) {
								this.handles.ne(resizeObj, diffX, diffY);
							}
							break;
						case 'vd_handle_left':
							if (this.handles.w) {
								this.handles.w(resizeObj, diffX, diffY);
							}
							break;
						case 'vd_handle_right':
							if (this.handles.e) {
								this.handles.e(resizeObj, diffX, diffY);
							}
							break;
						case 'vd_handle_bottomleft':
							if (this.handles.sw) {
								this.handles.sw(resizeObj, diffX, diffY);
							}
							break;
						case 'vd_handle_bottom':
							if (this.handles.s) {
								this.handles.s(resizeObj, diffX, diffY);
							}
							break;
						case 'vd_handle_bottomright':
							if (this.handles.se) {
								this.handles.se(resizeObj, diffX, diffY);
							}
							break;
						case 'vd_handle_drag':
							if (this.handles.drag) {
								this.handles.drag(resizeObj, diffX-dragStart.x, diffY-dragStart.y);
							}
							break;
					}
				}
				this.show('resize');
			}
		}

		this.hover=function(el, handles) {
			if (el) {
				hoverObj=el;
				hoverHandles=handles;
				startPos = getOffset(vdElement, vdWindow.document.body); 
				cellPos = getOffset(hoverObj, vdElement);
				if (cellPos) {
					cellPos.w=hoverObj.offsetWidth;
					cellPos.h=hoverObj.offsetHeight;

					hoverDiv.style.display='block';
//					window.status+='sp:'+startPos.y+' cp:'+cellPos.y;
					hoverDiv.style.top=(startPos.y+cellPos.y)+'px';
					hoverDiv.style.left=(startPos.x+cellPos.x)+'px';
					hoverDiv.style.width=cellPos.w+'px';
					hoverDiv.style.height=cellPos.h+'px';
					hoverDiv.style.zIndex=1;
				}
			}
		}

		this.selectcurrent=function() {
			this.select(hoverObj, hoverHandles);
		}

		this.attach=function(handlesList, el) 
		{
			//	handles={ nw:function, n:function, ...
			var tempHandles=this;
			if (el.vdHover) {
				muze.event.detach(el, 'mouseover',el.vdHoverCancel);
				if (hoverObj==el) {
					hoverHandles=handlesList;
				}
				if (resizeObj==el) {
					this.handles=handlesList;
					this.show('select');
				}
			}
			el.vdHover=function(evt) { evt=muze.event.get(evt); tempHandles.hover(el, handlesList); if (evt) { evt.cancelBubble=true; } }
			el.vdHoverCancel = muze.event.attach(el, 'mouseover', el.vdHover);
		}

		this.detach=function(el) 
		{
			if (el.vdHover) {
				muze.event.detach(el, 'mouseover',el.vdHoverCancel);
				el.vdHover=null;
			}
		}

		this.attachTags=function() 
		{
			var tempHandles=this;

			function attachTag(handlesList, tagName) 
			{
				var tagList=document.getElementsByTagName(tagName);
				for (var i=0; i<tagList.length; i++) 
				{
					if (!(tagList[i].unselectable=='on')) 
					{
						tempHandles.attach(handlesList, tagList[i]);
					}
				}
			}

			for (var i=1; i<arguments.length; i++) 
			{
				attachTag(arguments[0], arguments[i]);
			}
		}
//	}
	this.insert();
}


var absResize = {
	setWidth : function( el, width ) {
		if ( width < 0 ) {
			width = 0;
		}
		el.style.width = width + 'px';
	},
	setHeight : function( el, height ) {
		if ( height < 0 ) {
			height = 0;
		}
		el.style.height = height + 'px';
	},
	changeLeftPos : function( el, diffX ) {
		var start     = el.offsetLeft;
		el.style.left = ( start + diffX ) + 'px';
	},
	changeTopPos : function( el, diffY ) {
		var start    = el.offsetTop;
		el.style.top = ( start + diffY ) + 'px';
	},
	changeHeight : function( el, diffY ) {
		var start       = el.offsetHeight;
		el.style.height = Math.max( start + diffY, 0 ) + 'px';
	},
	changeWidth : function( el, diffX ) {
		var start      = el.offsetWidth;
		el.style.width = Math.max( start + diffX, 0 ) + 'px';
	},
	nw : function( el, diffX, diffY ) {
		absResize.changeLeftPos( el, diffX );
		absResize.changeTopPos( el, diffY );
		absResize.changeWidth( el, -diffX );
		absResize.changeHeight( el, -diffY );
	},
	n : function( el, diffX, diffY ) {
		absResize.changeTopPos( el, diffY );
		absResize.changeHeight( el, -diffY );
	},
	w : function( el, diffX, diffY ) {
		absResize.changeLeftPos( el, diffX );
		absResize.changeWidth( el, -diffX );
	},
	ne : function( el, diffX, diffY ) {
		absResize.changeTopPos( el, diffY );
		absResize.changeHeight( el, -diffY );
		absResize.setWidth( el, diffX );
	},
	e : function( el, diffX, diffY ) {
		absResize.setWidth( el, diffX );
	},
	sw : function( el, diffX, diffY ) {
		absResize.changeLeftPos( el, diffX );
		absResize.changeWidth( el, -diffX );
		absResize.setHeight( el, diffY );
	},
	s : function( el, diffX, diffY ) {
		absResize.setHeight( el, diffY );
	},
	se : function( el, diffX, diffY ) {
		absResize.setWidth( el, diffX );
		absResize.setHeight( el, diffY );
	},
	drag : function( el, diffX, diffY ) {
		absResize.changeLeftPos( el, diffX );
		absResize.changeTopPos( el, diffY );
	},
	getHandles : function() {
		return {
			nw   : absResize.nw,
			n    : absResize.n,
			ne   : absResize.ne,
			e    : absResize.e,
			se   : absResize.se,
			s    : absResize.s,
			sw   : absResize.sw,
			w    : absResize.w,
			drag : absResize.drag 
		}
	}
}

var relResize={
	setWidth:function(el, width) {
		if (width<0) {
			width=0;
		}
		el.style.width=width;
	},
	setHeight:function(el, height) {
		if (height<0) {
			height=0;
		}
		el.style.height=height;
	},
	e:function(el, diffX, diffY) {
		relResize.setWidth(el, diffX);
	},
	s:function(el, diffX, diffY) {
		relResize.setHeight(el, diffY);
	},
	se:function(el, diffX, diffY) {
		relResize.setWidth(el, diffX);
		relResize.setHeight(el, diffY);
	},
	getHandles:function() {
		return {
			e:relResize.e,
			se:relResize.se,
			s:relResize.s
		}
	}
}

var self = {

	init : function(vdWindow, vdElement, vdImageDir, vdUpdateFunc) {
		return new handles(vdWindow, vdElement, vdImageDir, vdUpdateFunc);
	}
}

return self;

})();
