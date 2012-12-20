/*
	FIXME: event add/remove via array index laten werken, geen directe functie pointers, alleen indexes in handles array
	op die manier krijg je geen circulaire referenties via closures
	
	javascript events library for muze modules
	----------------------------------------

	object get(object evt)
		This method returns the event object cross browser. You only need this if you don't
		use muze.event.attach() to attach your event handler, since it already does this for
		you.

		examples:
			function myEventHandler(evt) {
				evt = muze.event.get(evt);
				....
			}

	bool cancel(object evt)
		This method cancels the event, stops propagation, prevents default, in short it kills
		the event dead. Cross browser. It also returns false, so you may assign it directly
		to events you want killed.

		examples:
			function myEventHandler(evt) {
				...
				if (killEvent == true) {
					return muze.event.cancel(evt);
				}
				...
			}

			document.body.onMouseDown = muze.event.cancel;


	bool pass(object evt)
		This method returns true. So you may use it to make explicit that you don't cancel an event.

	mixed attach(object obj, string event, object handler, bool useCapture)
		This method attaches an event handler to an event on an object. It makes sure the event
		gets cleaned on unload, so you won't get memory leaks. It makes sure that 'this' points
		to the object the event is defined on. Important: Returns the handler required for detaching
		the event. This is not the same handler as passed to the attach function!
		arguments:
			obj		DOM object on which to catch the event
			event		name of the event to catch, e.g. 'load', 'click', etc.
			handler		function that handles the event.
			useCapture	Mozilla's useCapture option to addEventListener
		examples:

			...
			var detachHandler = muze.event.attach(document.body, 'load', function() { alert(this.innerHTML); });
			...

	bool detach(object obj, string event, object, handler, bool useCapture)
		This method detaches an event handler from an event on an object.
		arguments:
			obj		DOM objeect on which the event handler was attached
			event		name of the event to remove, e.g. 'load', 'click', etc.
			handler		handler to detach.
			useCapture	Mozilla's useCapture option to addEventListener
		examples:
		
			...
			var detachHandler = muze.event.attach(document.body, 'click', function() { alert('we have a click'); });
			...
			muze.event.detach(document.body, 'click', detachHandler);
			...


	void clean() 
		This method cleans/removes all attached event handlers. It is automatically run on unload of document, if needed.

	TODO:
		custom events met trigger en bind achtige functie, misschien in eigen namespace
		
*/

muze.require('muze.env', function() {

muze.namespace('muze.event', function() {

	/* private methods */

	/* private variables */
	var event = this;

	

	if (muze.env.isHostMethod(document, 'createEvent')) {
		event.create = function( name, maskEvt, win ) {
			if (!win) {
				win = muze.global;
			}
			var type = 'HTMLEvents';
			var init = function(evt, mask) {
				evt.initEvent(name, mask ? mask.bubbles : true, mask ? mask.cancelable : true);
			}
			switch (name) {
				case 'click' :
				case 'dblclick':
				case 'mousedown':
				case 'mousemove':
				case 'mouseout':
				case 'mouseover':
				case 'mouseup':
				case 'mousewheel':
				case 'contextmenu':
				case 'DOMMouseScroll':
				case 'drag':
				case 'dragdrop':
				case 'dragend':
				case 'dragenter':
				case 'dragover':
				case 'dragexit':
				case 'dragleave':
				case 'dragstart':
				case 'drop':				
					type = 'MouseEvents';
					init = function(evt, mask) {
						if (mask) {
							evt.initMouseEvent(name, mask.bubbles, mask.cancelable, mask.view, mask.detail, mask.screenX, mask.screenY, mask.clientX, mask.clientY, mask.ctrlKey, mask.altKey, mask.shiftKey, mask.metaKey, mask.button, mask.relatedTarget);
						} else {
							evt.initMouseEvent(name, true, true, win, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
						}						
					}
				break;
				case 'DOMFocusIn':
				case 'DOMFocusOut':
				case 'DOMActivate':
					type = 'UIEvents';
					init = function(evt, mask) {
						if (mask) {
							evt.initUIEvent(name, mask.bubbles, mask.cancelable, mask.view, mask.detail);
						} else {
							evt.initUIEvent(name, true, true, win, 1);
						}
					}
				break;
				case 'keypress':
				case 'keydown':
				case 'keyup':
					type = 'KeyboardEvents';
					var evt = win.document.createEvent( type );
					if (muze.env.isHostMethod(evt, 'initKeyboardEvent')) {
						init = function(evt, mask) {
							if (mask) {
								var modifiers = '';
								if (mask.altKey) {
									modifiers += 'Alt ';
								}
								if (mask.ctrlKey) {
									modifiers += 'Control ';
								}
								if (mask.shiftKey) {
									modifiers += 'Shift ';
								}
								if (mask.metaKey) {
									modifiers += 'Meta ';
								}
								evt.initKeyboardEvent(name, !!mask.bubbles, !!mask.cancelable, mask.view, mask.keyIdentifier, mask.keyLocation, modifiers);
							} else {
								evt.initKeyboardEvent(name, true, true, win, '', 0, '');
							}
						}
					} else if (muze.env.isHostMethod(evt, 'initKeyEvent')) {
						init = function(evt, mask) {
							if (mask) {
								evt.initKeyEvent(name, !!mask.bubbles, !!mask.cancelable, mask.view, mask.ctrlKey, mask.altKey, mask.shiftKey, mask.metaKey, mask.keyCode, mask.charCode);
							} else {
								evt.initKeyEvent(name, true, true, win, false, false, false, false, 0, 0);
							}
						}
					}
				break;
				case 'message':
					type = 'MessageEvent';
					init = function(evt, mask) {
						if (mask) {
							evt.initMessageEvent(name, mask.bubbles, mask.cancelable, mask.data, mask.origin, mask.lastEventId, mask.source, mask.ports);
						} else {
							evt.initMessageEvent(name, true, true, '', '', '', '', null);
						}
					}
				break;
			}
			var evt =  win.document.createEvent(type);
			init(evt, maskEvt);
			return evt;
		}
	} else if (muze.env.isHostMethod(document, 'createEventObject') ) {
		event.create = function( name, evt, win ) {
			if (!win) {
				win = muze.global;
			}
			var evt = win.document.createEventObject(name, evt);
			return evt;
		};
	} else {
		event.create = false;
	}

	if (muze.env.isHostMethod(document, 'dispatchEvent')) {
		event.fire = function(el, name, evt) {
			var win = muze.global;
			if (el.ownerDocument && el.ownerDocument.defaultView) {
				win = el.ownerDocument.defaultView;
			} else if (el.ownerDocument && el.ownerDocument.parentWindow) {
				win = el.ownerDocument.parentWindow;
			}
			evt = muze.event.create(name, evt, win);
			el.dispatchEvent(evt);
		}
	} else if (muze.env.isHostMethod(document, 'fireEvent')) {
		event.fire = function(el, name, evt) {
			if (name.substr(0,3)!=='DOM') {
				name = 'on'+name;
			}
			el.fireEvent(name, evt);
		}
	} else {
		event.fire = false;
	}
	
	event.get = function(evt, win) {
		if ( !win ) {
			win = muze.global;
		}
		if ( !evt ) {
			evt = win.event;
		}
		return evt;
	};

	event.cancel = function(evt) {
		event.preventDefault(evt);
		event.stopPropagation(evt);
		return false;
	};
	
	event.stopPropagation = function(evt) {
		evt = event.get(evt);
		if (muze.env.isHostMethod(evt, 'stopPropagation')) {
			evt.stopPropagation();
		} else {
			evt.cancelBubble = true;
		}
	};

	event.preventDefault = function(evt) {
		evt = event.get(evt);
		if (muze.env.isHostMethod(evt, 'preventDefault')) {
			evt.preventDefault();
		} else {
			evt.returnValue=false;
		} 
	};
	
	event.pass = function(evt) {
		return true;
	};

	event.target = function(evt) {
		evt = event.get(evt);
		if (muze.env.isHostObject(evt, 'target') ) {
			return evt.target;
		} else if (muze.env.isHostObject(evt, 'srcElement') ) {
			return evt.srcElement;
		} else {
			return null;
		}
	}

	event.getCharCode = function(evt) {
		evt = event.get(evt);
		if (evt.type=='keypress' || evt.type=='onkeypress') {
			return (evt.charCode ? evt.charCode : ((evt.keyCode) ? evt.keyCode : evt.which));
		} else {
			return false;
		}
	}

	var docEl = document.documentElement;
	var listeners = [];

	var getWrapper = function( id ) {
		return function(evt) {
			var o = listeners[id].el;
			if (o.ownerDocument) {
				var win = o.ownerDocument.defaultView ? o.ownerDocument.defaultView : o.ownerDocument.parentWindow;
			} else if (o.defaultView) {
				var win = o.defaultView;
			} else if (o.parentWindow) {
				var win = o.parentWindow;
			} else if (o.document) {
				var win = o;
			} else {
				var win = muze.global;
			}
			evt = event.get(evt, win);
			var f = listeners[id].listener;
			f.call(o, evt);
		}
	}

	if (muze.env.isHostMethod(docEl, 'addEventListener')) {
		event.attach = function(o, sEvent, fListener, useCapture) {
			if ( !muze.env.isFunction(fListener) ) {
				throw {
					el : o,
					message : 'listener is not a method',
					event : sEvent
				};
			}
			var listenerID = listeners.push( {
				el : o,
				listener : fListener
			} ) - 1;
			var wrapped = getWrapper(listenerID);
			o.addEventListener(sEvent, wrapped, !!useCapture);
			return wrapped;
		};
	} else if (muze.env.isHostMethod(docEl, 'attachEvent')) {
		event.attach = function(o, sEvent, fListener, useCapture) {
			if (!muze.env.isFunction(fListener)) {
				throw {
					el : o,
					message : 'listener is not a method',
					event : sEvent
				};
			}
			var listenerID = listeners.push( {
				el : o,
				listener : fListener
			} ) - 1;
			if (sEvent.substr(0,3)!='DOM') {
				sEvent = 'on' + sEvent;
			}
			var wrapped = getWrapper(listenerID);
			o.attachEvent(sEvent, wrapped);
			return wrapped;
		};
	} else {
		event.attach = false;
	}


	if (muze.env.isHostMethod(docEl, 'removeEventListener') ) {
		event.detach = function(o, sEvent, handle, useCapture) {	
			if (o && sEvent) {
				var result = o.removeEventListener(sEvent, handle, !!useCapture);
				return result;
			} else {
				return false;
			}
		};
	} else if (muze.env.isHostMethod(docEl, 'detachEvent') ) {
		event.detach = function(o, sEvent, handle, useCapture) {	
			if (o && sEvent) {
				var result = o.detachEvent('on'+sEvent, handle);
				return result;
			} else {
				return false;
			}
		}
	} else {
		event.detach = false;
	}
	
	event.clean = function() { }

});

});
