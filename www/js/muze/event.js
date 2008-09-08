/*
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
		to the object the event is defined on.
		arguments:
			obj		DOM object on which to catch the event
			event		name of the event to catch, e.g. 'load', 'click', etc.
			handler		function that handles the event.
			useCapture	Mozilla's useCapture option to addEventListener
		examples:

			...
			muze.event.attach(document.body, 'load', function() { alert(this.innerHTML); });
			...

	void clean() 
		This method cleans/removes all attached event handlers. It is automatically run on unload of document

*/


muze.namespace('muze.event');

muze.event = function() {

	/* private methods */

	/* private variables */

	var cache=[];

	var events = {

		get:function(evt) {
			if (!evt) {
				evt=window.event;
			}
			if (!evt.target) {
				evt.target=evt.srcElement;
			}
			return evt;
		},

		cancel:function(evt) {
			evt = muze.event.get(evt);
			if (evt.returnValue) {
				evt.returnValue=false;
			} 
			if (evt.preventDefault) {
				evt.preventDefault();
			}
			evt.cancelBubble=true;
			if (evt.stopPropagation) {
				evt.stopPropagation();
			}
			return false;
		},

		pass:function(evt) {
			return true;
		},

		attach:function(ob, event, fp, useCapture) {
			if (ob) {
				function createHandlerFunction(obj, fn){
					var o = new Object;
					o.myObj = obj;
					o.calledFunc = fn;
					o.myFunc = function(e){ 
						var e = muze.event.get(e);
						return o.calledFunc.call(o.myObj, e);
					}
					return o.myFunc;
				}
				var handler=createHandlerFunction(ob, fp);
				cache[cache.length]={ event:event, object:ob, handler:handler };
				if (ob.addEventListener){
					ob.addEventListener(event, handler, useCapture);
					return true;
				} else if (ob.attachEvent){
					return ob.attachEvent("on"+event, handler);
				} else {
					//FIXME: don't do alerts like this
					alert("Handler could not be attached");
				}
			} else {
				//FIXME: don't do alerts like this
				alert('Object not found');
			}
		},

		clean:function() {
			var item=null;
			for (var i=cache.length-1; i>=0; i--) {
				item=cache[i];
				item.object['on'+item.event]=null;
				if (item.object.removeEventListener) {
					item.object.removeEventListener(item.event, item.handler, item.useCapture);
				} else if (item.object.detachEvent) {
					item.object.detachEvent("on" + item.event, item.handler);
				}
				cache[i]=null;
			}
			item=null;
		}

	}

	events.attach(window, 'unload', events.clean);

	return events;
}();